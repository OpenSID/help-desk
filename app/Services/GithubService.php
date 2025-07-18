<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GithubService
{
    protected $client;
    protected $token;
    protected $owner;
    protected $repo;
    protected $projectId;
    protected $statusFieldId;

    // Pemetaan status tiket ke statusOptionId
    protected const STATUS_OPTIONS = [
        'done' => 'f75ad846',
        'sedang_dikerjakan' => '47fc9ee4',
        'review' => '98236657',
        'target' => '88395b12',
    ];

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Accept' => 'application/vnd.github+json',
                'Authorization' => 'Bearer ' . config('services.github.token'),
            ],
        ]);
        $this->token = config('services.github.token');
        $this->owner = config('services.github.owner');
        $this->repo = config('services.github.repo');
        $this->projectId = config('services.github.project_id');
        $this->statusFieldId = config('services.github.status_field_id');
    }

    /**
     * Membuat issue baru di GitHub dan menambahkannya ke proyek.
     *
     * @param array $data
     * @return array|null
     */
    public function createIssue(array $data)
    {
        $promises = [];

        // Membuat issue menggunakan REST API
        $promises['createIssue'] = $this->client->postAsync("repos/{$this->owner}/{$this->repo}/issues", [
            'json' => [
                'title' => $data['title'],
                'body' => $data['body'],
                'assignees' => $data['assignees'] ?? [],
                'labels' => $data['labels'] ?? [],
            ],
        ]);

        try {
            $responses = Promise\Utils::settle($promises)->wait();
            $issueResponse = $responses['createIssue'];

            if ($issueResponse['state'] === 'fulfilled') {
                $issueData = json_decode($issueResponse['value']->getBody(), true);
                $issueData['node_id'] = $this->getIssueNodeId($issueData['number']);
                return $issueData;
            } else {
                Log::error('Failed to create issue', [
                    'reason' => $issueResponse['reason'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating issue', ['error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Mendapatkan node ID dari issue berdasarkan nomor issue.
     *
     * @param int $issueNumber
     * @return string|null
     */
    public function getIssueNodeId(int $issueNumber)
    {
        $query = <<<'GRAPHQL'
        query($owner: String!, $repo: String!, $issueNumber: Int!) {
          repository(owner: $owner, name: $repo) {
            issue(number: $issueNumber) {
              id
            }
          }
        }
        GRAPHQL;

        $response = Http::withToken($this->token)
            ->post('https://api.github.com/graphql', [
                'query' => $query,
                'variables' => [
                    'owner' => $this->owner,
                    'repo' => $this->repo,
                    'issueNumber' => $issueNumber,
                ],
            ]);

        if ($response->successful()) {
            return $response->json('data.repository.issue.id');
        }

        Log::error('Failed to fetch issue node ID', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return null;
    }

    /**
     * Menambahkan issue ke proyek.
     *
     * @param string $issueNodeId
     * @return string|null
     */
    public function addToProject(string $issueNodeId)
    {
        $query = <<<'GRAPHQL'
        mutation($projectId: ID!, $contentId: ID!) {
          addProjectV2ItemById(input: {
            projectId: $projectId,
            contentId: $contentId
          }) {
            item {
              id
            }
          }
        }
        GRAPHQL;

        $response = Http::withToken($this->token)
            ->post('https://api.github.com/graphql', [
                'query' => $query,
                'variables' => [
                    'projectId' => $this->projectId,
                    'contentId' => $issueNodeId,
                ],
            ]);

        if ($response->successful()) {
            return $response->json('data.addProjectV2ItemById.item.id');
        }

        Log::error('Failed to add issue to project', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return null;
    }

    /**
     * Mendapatkan ID field proyek berdasarkan nama field.
     *
     * @param string $fieldName
     * @return string|null
     */
    public function getProjectFieldId(string $fieldName)
    {
        $query = <<<'GRAPHQL'
        query($projectId: ID!) {
          node(id: $projectId) {
            ... on ProjectV2 {
              fields(first: 12) {
                nodes {
                  ... on ProjectV2FieldCommon {
                    id
                    name
                  }
                }
              }
            }
          }
        }
        GRAPHQL;

        $response = Http::withToken($this->token)
            ->post('https://api.github.com/graphql', [
                'query' => $query,
                'variables' => [
                    'projectId' => $this->projectId,
                ],
            ]);

        if ($response->successful()) {
            $fields = $response->json('data.node.fields.nodes');
            foreach ($fields as $field) {
                if ($field['name'] === $fieldName) {
                    return $field['id'];
                }
            }
        }

        Log::error('Failed to fetch project field ID', [
            'field_name' => $fieldName,
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return null;
    }

    /**
     * Mendapatkan ID opsi untuk field SINGLE_SELECT berdasarkan nama opsi.
     *
     * @param string $fieldId
     * @param string $optionName
     * @return string|null
     */
    public function getSingleSelectOptionId(string $fieldId, string $optionName)
    {
        $query = <<<'GRAPHQL'
        query($projectId: ID!) {
        node(id: $projectId) {
            ... on ProjectV2 {
            fields(first: 20) {
                nodes {
                ... on ProjectV2SingleSelectField {
                    id
                    name
                    options {
                    id
                    name
                    }
                }
                }
            }
            }
        }
        }
        GRAPHQL;

        $response = Http::withToken($this->token)
            ->post('https://api.github.com/graphql', [
                'query' => $query,
                'variables' => [
                    'projectId' => $this->projectId,
                ],
            ]);

        if ($response->successful()) {
            $fields = $response->json('data.node.fields.nodes') ?? [];
            foreach ($fields as $field) {
                // Pastikan field adalah SingleSelectField dan memiliki id serta options
                if (isset($field['id'], $field['options']) && $field['id'] === $fieldId) {
                    foreach ($field['options'] as $option) {
                        if (strtolower($option['name']) === strtolower($optionName)) {
                            return $option['id'];
                        }
                    }
                }
            }
        }

        Log::error('Failed to fetch single select option ID', [
            'field_id' => $fieldId,
            'option_name' => $optionName,
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return null;
    }

    /**
     * Memperbarui beberapa field di proyek.
     *
     * @param string $itemId
     * @param array $fieldValues
     * @return bool
     */
    public function updateProjectFields(string $itemId, array $fieldValues)
    {
        $success = true;

        foreach ($fieldValues as $fieldValue) {
            $query = <<<'GRAPHQL'
            mutation($projectId: ID!, $itemId: ID!, $fieldId: ID!, $value: ProjectV2FieldValue!) {
              updateProjectV2ItemFieldValue(input: {
                projectId: $projectId,
                itemId: $itemId,
                fieldId: $fieldId,
                value: $value
              }) {
                projectV2Item {
                  id
                }
              }
            }
            GRAPHQL;

            $response = Http::withToken($this->token)
                ->post('https://api.github.com/graphql', [
                    'query' => $query,
                    'variables' => [
                        'projectId' => $this->projectId,
                        'itemId' => $itemId,
                        'fieldId' => $fieldValue['fieldId'],
                        'value' => $fieldValue['value'],
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Failed to update project field', [
                    'field_id' => $fieldValue['fieldId'],
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Mengubah status issue di proyek.
     *
     * @param string $projectItemId
     * @param string $status
     * @return bool
     */
    public function updateIssueStatus(string $projectItemId, string $status)
    {
        $statusOptionId = self::STATUS_OPTIONS[strtolower($status)] ?? null;

        if (!$statusOptionId) {
            Log::error('Invalid status provided', ['status' => $status]);
            return false;
        }

        $query = <<<'GRAPHQL'
        mutation($projectId: ID!, $itemId: ID!, $statusFieldId: ID!, $statusOptionId: String!) {
          updateProjectV2ItemFieldValue(input: {
            projectId: $projectId,
            itemId: $itemId,
            fieldId: $statusFieldId,
            value: { singleSelectOptionId: $statusOptionId }
          }) {
            projectV2Item {
              id
            }
          }
        }
        GRAPHQL;

        $response = Http::withToken($this->token)
            ->post('https://api.github.com/graphql', [
                'query' => $query,
                'variables' => [
                    'projectId' => $this->projectId,
                    'itemId' => $projectItemId,
                    'statusFieldId' => $this->statusFieldId,
                    'statusOptionId' => $statusOptionId,
                ],
            ]);

        if ($response->successful()) {
            return true;
        }

        Log::error('Failed to update issue status', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        return false;
    }

    /**
     * Memperbarui issue di GitHub.
     *
     * @param int $issueNumber
     * @param array $data
     * @return bool
     */
    public function updateIssue(int $issueNumber, array $data)
    {
        $promise = $this->client->patchAsync("repos/{$this->owner}/{$this->repo}/issues/{$issueNumber}", [
            'json' => $data,
        ]);

        try {
            $response = Promise\Utils::unwrap([$promise])[$promise];
            if ($response->getStatusCode() === 200) {
                return true;
            }

            Log::error('Failed to update issue', [
                'issue_number' => $issueNumber,
                'status' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), true),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating issue', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * Menutup issue dengan komentar.
     *
     * @param int $issueNumber
     * @param string $comment
     * @return bool
     */
    public function closeIssue(int $issueNumber, string $comment)
    {
        $promises = [];

        // Tambahkan komentar
        $promises['comment'] = $this->client->postAsync("repos/{$this->owner}/{$this->repo}/issues/{$issueNumber}/comments", [
            'json' => ['body' => $comment],
        ]);

        // Tutup issue
        $promises['close'] = $this->client->patchAsync("repos/{$this->owner}/{$this->repo}/issues/{$issueNumber}", [
            'json' => ['state' => 'closed'],
        ]);

        try {
            $responses = Promise\Utils::settle($promises)->wait();
            $success = true;

            foreach ($responses as $key => $response) {
                if ($response['state'] !== 'fulfilled' || $response['value']->getStatusCode() !== 200) {
                    Log::error("Failed to {$key} issue", [
                        'issue_number' => $issueNumber,
                        'status' => $response['value']->getStatusCode(),
                        'body' => json_decode($response['value']->getBody(), true),
                    ]);
                    $success = false;
                }
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Error closing issue', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
