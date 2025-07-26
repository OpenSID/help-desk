<?php

namespace App\Services;

use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use League\HTMLToMarkdown\HtmlConverter;
use GuzzleHttp\Promise\Utils;

class GithubService
{
    protected $client;
    protected $token;
    protected $owner;
    protected $repo;
    protected $projectId;
    protected $statusFieldId;

    // Warna default jika tidak ada warna dari model
    protected const DEFAULT_COLOR = 'D3D3D3'; // Abu-abu

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
        try {
            $promises = [];
            $labels = $data['labels'] ?? [];
            $labelColors = $data['label_colors'] ?? [];

            foreach ($labels as $label) {
                $color = $labelColors[$label] ?? self::DEFAULT_COLOR;
                $description = "Label untuk $label";
                $this->createLabel($label, $description, $color);
            }

            $promises['createIssue'] = $this->client->postAsync("repos/{$this->owner}/{$this->repo}/issues", [
                'json' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'assignees' => $data['assignees'] ?? [],
                    'labels' => $labels,
                ],
            ]);

            $responses = Utils::settle($promises)->wait();
            $issueResponse = $responses['createIssue'];

            if ($issueResponse['state'] === 'fulfilled') {
                $issueData = json_decode($issueResponse['value']->getBody(), true);
                $issueData['node_id'] = $this->getIssueNodeId($issueData['number']);
                return $issueData;
            }

            Log::error('Failed to create issue', [
                'reason' => $issueResponse['reason'],
                'ticket_id' => $data['ticket_id'] ?? 'unknown',
            ]);
            return null;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            if ($response && in_array($response->getStatusCode(), [429, 403])) {
                $statusCode = $response->getStatusCode();
                $message = $statusCode === 429 ? 'Rate limit exceeded' : 'Forbidden access';
                Log::warning("GitHub API error: {$message}", [
                    'ticket_id' => $data['ticket_id'] ?? 'unknown',
                    'status_code' => $statusCode,
                    'error' => $e->getMessage(),
                ]);
                throw $e; // Lempar ulang untuk ditangani oleh job
            }
            Log::error('Error creating issue', [
                'error' => $e->getMessage(),
                'ticket_id' => $data['ticket_id'] ?? 'unknown',
            ]);
            return null;
        }
    }

    /**
     * Membuat label baru di repositori jika belum ada.
     *
     * @param string $name
     * @param string $description
     * @param string $color
     * @return bool
     */
    public function createLabel(string $name, string $description, string $color)
    {
        try {
            $response = $this->client->post("repos/{$this->owner}/{$this->repo}/labels", [
                'json' => [
                    'name' => $name,
                    'description' => $description,
                    'color' => ltrim($color, '#'), // Hapus # dari kode hex
                ],
            ]);

            if ($response->getStatusCode() === 201) {
                return true;
            }

            Log::warning('Failed to create label', [
                'name' => $name,
                'status' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), true),
            ]);
            return false;
        } catch (\Exception $e) {
            // Label mungkin sudah ada
            if ($e->getCode() === 422) {
                // Perbarui warna label jika sudah ada
                $this->updateLabel($name, $description, $color);
                return true;
            }
            Log::error('Error creating label', ['name' => $name, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Memperbarui label yang sudah ada di repositori.
     *
     * @param string $name
     * @param string $description
     * @param string $color
     * @return bool
     */
    public function updateLabel(string $name, string $description, string $color)
    {
        try {
            $response = $this->client->patch("repos/{$this->owner}/{$this->repo}/labels/{$name}", [
                'json' => [
                    'name' => $name,
                    'description' => $description,
                    'color' => ltrim($color, '#'),
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                return true;
            }

            Log::warning('Failed to update label', [
                'name' => $name,
                'status' => $response->getStatusCode(),
                'body' => json_decode($response->getBody(), true),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Error updating label', ['name' => $name, 'error' => $e->getMessage()]);
            return false;
        }
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

        try {
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

            // Jika tidak berhasil atau field tidak ditemukan, log error dan lempar exception
            Log::error('Failed to fetch project field ID', [
                'field_name' => $fieldName,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new \Exception("Failed to fetch project field ID for '{$fieldName}'. Status: {$response->status()}, Details: " . json_encode($response->json()));
        } catch (\Exception $e) {
            Log::error('Exception in getProjectFieldId', [
                'field_name' => $fieldName,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Lempar ulang untuk ditangani oleh job
        }
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

        try {
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

            // Jika tidak berhasil atau opsi tidak ditemukan, log error dan lempar exception
            Log::error('Failed to fetch single select option ID', [
                'field_id' => $fieldId,
                'option_name' => $optionName,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            throw new \Exception("Failed to fetch single select option ID for field '{$fieldId}' and option '{$optionName}'. Status: {$response->status()}, Details: " . json_encode($response->json()));
        } catch (\Exception $e) {
            Log::error('Exception in getSingleSelectOptionId', [
                'field_id' => $fieldId,
                'option_name' => $optionName,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Lempar ulang untuk ditangani oleh job
        }
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

    public function updateIssue(array $data): ?array
    {
        $issueNumber = $data['issue_number'] ?? null;
        $ticketId = $data['ticket_id'] ?? 'unknown';

        try {
            // Perbarui label jika diperlukan
            $labels = $data['labels'] ?? [];
            $labelColors = $data['label_colors'] ?? [];
            foreach ($labels as $label) {
                $color = $labelColors[$label] ?? self::DEFAULT_COLOR;
                $description = "Label untuk $label";
                $this->createLabel($label, $description, $color);
            }

            // Kirim permintaan PATCH untuk memperbarui issue
            $response = $this->client->patch("repos/{$this->owner}/{$this->repo}/issues/{$issueNumber}", [
                'json' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                    'assignees' => $data['assignees'] ?? [],
                    'labels' => $labels,
                ],
            ]);

            $issueData = json_decode($response->getBody(), true);
            Log::info('[GithubService] GitHub issue updated successfully', [
                'ticket_id' => $ticketId,
                'issue_number' => $issueNumber,
            ]);
            return $issueData;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $response = $e->getResponse();
            if ($response && in_array($response->getStatusCode(), [429, 403])) {
                $statusCode = $response->getStatusCode();
                $message = $statusCode === 429 ? 'Rate limit exceeded' : 'Forbidden access';
                Log::warning("[GithubService] GitHub API error: {$message}", [
                    'ticket_id' => $ticketId,
                    'issue_number' => $issueNumber,
                    'status_code' => $statusCode,
                    'error' => $e->getMessage(),
                ]);
                throw $e; // Lempar ulang untuk ditangani oleh job
            }
            Log::error('[GithubService] Error updating GitHub issue', [
                'ticket_id' => $ticketId,
                'issue_number' => $issueNumber,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Memperbarui status issue di proyek.
     *
     * @param string $projectItemId
     * @param string $status
     * @return bool
     * @throws \Exception
     */
    public function updateIssueStatus(string $projectItemId, string $status): bool
    {
        try {
            $statusFieldId = $this->getProjectFieldId('Status');
            if (!$statusFieldId) {
                Log::error('Status field ID not found', ['project_item_id' => $projectItemId]);
                throw new \Exception('Status field ID not found for project item ' . $projectItemId);
            }

            // Ambil nama opsi status dari konfigurasi
            $statusName = config('services.github.status_options.' . strtolower($status), null);
            if (!$statusName) {
                Log::warning('Status option not found in mapping', [
                    'status' => $status,
                    'project_item_id' => $projectItemId,
                ]);
                throw new \Exception('Status option not found in mapping for status ' . $status);
            }

            // Konversi nama opsi status ke singleSelectOptionId
            $statusOptionId = $this->getSingleSelectOptionId($statusFieldId, $statusName);
            if (!$statusOptionId) {
                Log::error('Failed to fetch single select option ID', [
                    'field_id' => $statusFieldId,
                    'option_name' => $statusName,
                    'project_item_id' => $projectItemId,
                ]);
                throw new \Exception('Failed to fetch single select option ID for status ' . $statusName);
            }

            $fieldValues = [
                [
                    'fieldId' => $statusFieldId,
                    'value' => ['singleSelectOptionId' => $statusOptionId],
                ],
            ];

            $success = $this->updateProjectFields($projectItemId, $fieldValues);
            if ($success) {
                Log::info('GitHub issue status updated successfully', [
                    'project_item_id' => $projectItemId,
                    'status' => $status,
                    'status_option_id' => $statusOptionId,
                    'status_name' => $statusName,
                ]);
            } else {
                Log::warning('Failed to update GitHub issue status', [
                    'project_item_id' => $projectItemId,
                    'status' => $status,
                    'status_option_id' => $statusOptionId,
                    'status_name' => $statusName,
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Exception in updateIssueStatus', [
                'project_item_id' => $projectItemId,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Lempar ulang untuk ditangani oleh job
        }
    }
}
