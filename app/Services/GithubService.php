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

    // Cache untuk menyimpan field data
    protected static $fieldsCache = null;
    protected static $fieldOptionsCache = [];

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
     * Mendapatkan semua field data dari proyek dan cache hasilnya.
     *
     * @return array
     * @throws \Exception
     */
    protected function getAllProjectFields(): array
    {
        // Return cached data jika sudah ada
        if (static::$fieldsCache !== null) {
            return static::$fieldsCache;
        }

        $query = <<<'GRAPHQL'
        query($projectId: ID!) {
          node(id: $projectId) {
            ... on ProjectV2 {
              fields(first: 100) {
                nodes {
                  __typename
                  ... on ProjectV2FieldCommon {
                    id
                    name
                    dataType
                  }
                  ... on ProjectV2SingleSelectField {
                    id
                    name
                    dataType
                    options {
                      id
                      name
                    }
                  }
                  ... on ProjectV2IterationField {
                    id
                    name
                    dataType
                    configuration {
                      iterations {
                        id
                        title
                      }
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

                // Cache hasil untuk penggunaan selanjutnya
                static::$fieldsCache = $fields;

                // Cache options untuk single select fields
                foreach ($fields as $field) {
                    if (isset($field['options']) && is_array($field['options'])) {
                        static::$fieldOptionsCache[$field['id']] = $field['options'];
                    }
                }

                Log::info('Project fields cached successfully', [
                    'field_count' => count($fields),
                    'project_id' => $this->projectId,
                ]);

                return $fields;
            }

            // Jika tidak berhasil, log error dan lempar exception
            Log::error('Failed to fetch all project fields', [
                'status' => $response->status(),
                'body' => $response->json(),
                'project_id' => $this->projectId,
            ]);

            throw new \Exception("Failed to fetch project fields. Status: {$response->status()}, Details: " . json_encode($response->json()));
        } catch (\Exception $e) {
            Log::error('Exception in getAllProjectFields', [
                'error' => $e->getMessage(),
                'project_id' => $this->projectId,
            ]);
            throw $e;
        }
    }

    /**
     * Mendapatkan ID field proyek berdasarkan nama field (optimized with caching).
     *
     * @param string $fieldName
     * @return string|null
     * @throws \Exception
     */
    public function getProjectFieldId(string $fieldName): ?string
    {
        try {
            // Ambil semua fields (dari cache jika tersedia)
            $fields = $this->getAllProjectFields();

            foreach ($fields as $field) {
                if ($field['name'] === $fieldName) {
                    return $field['id'];
                }
            }

            // Field tidak ditemukan
            Log::warning('Project field not found', [
                'field_name' => $fieldName,
                'available_fields' => array_column($fields, 'name'),
                'project_id' => $this->projectId,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception in getProjectFieldId', [
                'field_name' => $fieldName,
                'error' => $e->getMessage(),
                'project_id' => $this->projectId,
            ]);
            throw $e;
        }
    }

    /**
     * Mendapatkan multiple field IDs sekaligus untuk batch operations.
     *
     * @param array $fieldNames
     * @return array Array dengan format ['fieldName' => 'fieldId']
     * @throws \Exception
     */
    public function getMultipleFieldIds(array $fieldNames): array
    {
        try {
            // Ambil semua fields sekali saja
            $fields = $this->getAllProjectFields();
            $result = [];

            // Map field names ke IDs
            foreach ($fields as $field) {
                if (in_array($field['name'], $fieldNames, true)) {
                    $result[$field['name']] = $field['id'];
                }
            }

            // Check jika ada field yang tidak ditemukan
            $notFound = array_diff($fieldNames, array_keys($result));
            if (!empty($notFound)) {
                Log::warning('Some project fields not found', [
                    'not_found_fields' => $notFound,
                    'available_fields' => array_column($fields, 'name'),
                    'project_id' => $this->projectId,
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Exception in getMultipleFieldIds', [
                'field_names' => $fieldNames,
                'error' => $e->getMessage(),
                'project_id' => $this->projectId,
            ]);
            throw $e;
        }
    }

    /**
     * Mendapatkan ID opsi untuk field SINGLE_SELECT berdasarkan nama opsi (optimized with caching).
     *
     * @param string $fieldId
     * @param string $optionName
     * @return string|null
     */
    public function getSingleSelectOptionId(string $fieldId, string $optionName): ?string
    {
        try {
            // Cek cache terlebih dahulu
            if (isset(static::$fieldOptionsCache[$fieldId])) {
                $options = static::$fieldOptionsCache[$fieldId];
                foreach ($options as $option) {
                    if (strtolower($option['name']) === strtolower($optionName)) {
                        return $option['id'];
                    }
                }

                Log::warning('Single select option not found in cached data', [
                    'field_id' => $fieldId,
                    'option_name' => $optionName,
                    'available_options' => array_column($options, 'name'),
                ]);
                return null;
            }

            // Jika tidak ada di cache, ambil semua fields (ini akan populate cache)
            $fields = $this->getAllProjectFields();

            // Cari field yang sesuai
            foreach ($fields as $field) {
                if ($field['id'] === $fieldId && isset($field['options'])) {
                    foreach ($field['options'] as $option) {
                        if (strtolower($option['name']) === strtolower($optionName)) {
                            return $option['id'];
                        }
                    }

                    Log::warning('Single select option not found', [
                        'field_id' => $fieldId,
                        'option_name' => $optionName,
                        'available_options' => array_column($field['options'], 'name'),
                    ]);
                    return null;
                }
            }

            Log::error('Single select field not found', [
                'field_id' => $fieldId,
                'option_name' => $optionName,
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Exception in getSingleSelectOptionId', [
                'field_id' => $fieldId,
                'option_name' => $optionName,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Helper method untuk membuat array field values untuk batch update (optimized).
     *
     * @param array $fieldMappings Array dengan format ['fieldName' => 'value']
     * @return array
     * @throws \Exception
     */
    public function prepareFieldValues(array $fieldMappings): array
    {
        $fieldValues = [];

        // Ambil semua field IDs yang diperlukan dalam satu call
        $fieldNames = array_keys($fieldMappings);
        $fieldIds = $this->getMultipleFieldIds($fieldNames);

        foreach ($fieldMappings as $fieldName => $value) {
            if (!isset($fieldIds[$fieldName])) {
                throw new \Exception("Field '{$fieldName}' not found in project");
            }

            $fieldId = $fieldIds[$fieldName];

            // Format value berdasarkan tipe field
            $formattedValue = $this->formatFieldValue($fieldName, $value, $fieldId);

            $fieldValues[] = [
                'fieldId' => $fieldId,
                'value' => $formattedValue,
            ];
        }

        return $fieldValues;
    }

    /**
     * Format value berdasarkan tipe field (optimized).
     *
     * @param string $fieldName
     * @param mixed $value
     * @param string $fieldId Optional - jika sudah diketahui field ID
     * @return array
     * @throws \Exception
     */
    protected function formatFieldValue(string $fieldName, $value, ?string $fieldId = null): array
    {
        // Untuk single select field, konversi nama opsi ke ID
        if ($this->isSingleSelectField($fieldName)) {
            if (!$fieldId) {
                $fieldId = $this->getProjectFieldId($fieldName);
            }

            $optionId = $this->getSingleSelectOptionId($fieldId, $value);

            if (!$optionId) {
                throw new \Exception("Option '{$value}' not found for field '{$fieldName}'");
            }

            return ['singleSelectOptionId' => $optionId];
        }

        // Untuk text field
        if (is_string($value)) {
            return ['text' => $value];
        }

        // Untuk number field
        if (is_numeric($value)) {
            return ['number' => (float) $value];
        }

        // Untuk date field (format ISO 8601)
        if ($value instanceof \DateTime) {
            return ['date' => $value->format('Y-m-d')];
        }

        // Default: text
        return ['text' => (string) $value];
    }

    /**
     * Clear cache untuk fields data.
     * Berguna jika terjadi perubahan struktur project.
     *
     * @return void
     */
    public static function clearFieldsCache(): void
    {
        static::$fieldsCache = null;
        static::$fieldOptionsCache = [];
        Log::info('Project fields cache cleared');
    }

    /**
     * Periksa apakah field adalah single select field.
     *
     * @param string $fieldName
     * @return bool
     */
    protected function isSingleSelectField(string $fieldName): bool
    {
        // Daftar field yang diketahui sebagai single select
        $singleSelectFields = ['Status', 'Priority', 'Modul', 'Category'];

        return in_array($fieldName, $singleSelectFields, true);
    }

    /**
     * Memperbarui beberapa field di proyek menggunakan batch mutation.
     *
     * @param string $itemId
     * @param array $fieldValues
     * @return bool
     */
    public function updateProjectFields(string $itemId, array $fieldValues)
    {
        try {
            if (empty($fieldValues)) {
                Log::warning('No field values provided for update', ['item_id' => $itemId]);
                return true;
            }

            // Buat mutation untuk multiple field updates dalam satu request
            $mutationParts = [];
            $variables = [
                'projectId' => $this->projectId,
                'itemId' => $itemId,
            ];


            foreach ($fieldValues as $index => $fieldValue) {
                $fieldVar = "field{$index}Id";
                $valueVar = "field{$index}Value";

                $variables[$fieldVar] = $fieldValue['fieldId'];
                $variables[$valueVar] = $fieldValue['value'];

                $mutationParts[] = "
                  update{$index}: updateProjectV2ItemFieldValue(input: {
                    projectId: \$projectId,
                    itemId: \$itemId,
                    fieldId: \${$fieldVar},
                    value: \${$valueVar}
                  }) {
                    projectV2Item {
                      id
                    }
                  }";
            }



            $mutationBody = implode("\n", $mutationParts);

            // Buat signature untuk semua variables
            $variableSignatures = ['$projectId: ID!', '$itemId: ID!'];
            foreach ($fieldValues as $index => $fieldValue) {
                $variableSignatures[] = "\$field{$index}Id: ID!";
                $variableSignatures[] = "\$field{$index}Value: ProjectV2FieldValue!";
            }

            $variableSignature = implode(', ', $variableSignatures);

            $query = "mutation({$variableSignature}) {{$mutationBody}}";



            Log::info('Executing batch field update', [
                'item_id' => $itemId,
                'field_count' => count($fieldValues),
                'field_ids' => array_column($fieldValues, 'fieldId'),
            ]);

            $response = Http::withToken($this->token)
                ->post('https://api.github.com/graphql', [
                    'query' => $query,
                    'variables' => $variables,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Periksa apakah ada error dalam response
                if (isset($responseData['errors'])) {
                    Log::error('GraphQL errors in batch field update', [
                        'item_id' => $itemId,
                        'errors' => $responseData['errors'],
                    ]);
                    return false;
                }

                Log::info('Batch field update successful', [
                    'item_id' => $itemId,
                    'updated_fields' => count($fieldValues),
                ]);
                return true;
            }

            Log::error('Failed to update project fields in batch', [
                'item_id' => $itemId,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Exception in batch field update', [
                'item_id' => $itemId,
                'error' => $e->getMessage(),
                'field_count' => count($fieldValues),
            ]);
            return false;
        }
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
     * @param array $additionalFields Optional - field tambahan untuk di-update bersamaan
     * @return bool
     * @throws \Exception
     */
    public function updateIssueStatus(string $projectItemId, string $status, array $additionalFields = []): bool
    {
        try {
            // Ambil nama opsi status dari konfigurasi
            $statusName = config('services.github.status_options.' . strtolower($status), null);
            if (!$statusName) {
                Log::warning('Status option not found in mapping', [
                    'status' => $status,
                    'project_item_id' => $projectItemId,
                ]);
                throw new \Exception('Status option not found in mapping for status ' . $status);
            }

            // Siapkan field mappings untuk batch update
            $fieldMappings = ['Status' => $statusName];

            // Tambahkan field tambahan jika ada
            $fieldMappings = array_merge($fieldMappings, $additionalFields);

            // Konversi ke format field values
            $fieldValues = $this->prepareFieldValues($fieldMappings);

            $success = $this->updateProjectFields($projectItemId, $fieldValues);

            if ($success) {
                Log::info('GitHub issue fields updated successfully', [
                    'project_item_id' => $projectItemId,
                    'status' => $status,
                    'status_name' => $statusName,
                    'additional_fields' => array_keys($additionalFields),
                ]);
            } else {
                Log::warning('Failed to update GitHub issue fields', [
                    'project_item_id' => $projectItemId,
                    'status' => $status,
                    'status_name' => $statusName,
                    'additional_fields' => array_keys($additionalFields),
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

    /**
     * Method utama untuk update batch multiple fields sekaligus.
     *
     * @param string $projectItemId
     * @param array $fieldMappings Array dengan format ['fieldName' => 'value']
     * @return bool
     * @throws \Exception
     *
     * Contoh penggunaan:
     * $github->updateMultipleFields($itemId, [
     *     'Status' => 'In Progress',
     *     'Modul' => 'Dashboard',
     *     'Priority' => 'High'
     * ]);
     */
    public function updateMultipleFields(string $projectItemId, array $fieldMappings): bool
    {
        try {
            if (empty($fieldMappings)) {
                Log::warning('No field mappings provided for batch update', ['item_id' => $projectItemId]);
                return true;
            }

            Log::info('Starting batch field update', [
                'project_item_id' => $projectItemId,
                'fields' => array_keys($fieldMappings),
            ]);

            // Konversi field mappings ke format yang dibutuhkan
            $fieldValues = $this->prepareFieldValues($fieldMappings);

            // Jalankan batch update
            $success = $this->updateProjectFields($projectItemId, $fieldValues);

            if ($success) {
                Log::info('Batch field update completed successfully', [
                    'project_item_id' => $projectItemId,
                    'updated_fields' => array_keys($fieldMappings),
                ]);
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Exception in batch field update', [
                'project_item_id' => $projectItemId,
                'field_mappings' => $fieldMappings,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
