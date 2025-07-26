<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Models\TicketStatus;
use Illuminate\Bus\Queueable;
use App\Services\GitHubService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ProcessGitHubTicket implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Coba ulang 3 kali jika gagal
    public $backoff = [1, 5, 10]; // Exponential backoff: 1 detik, 5 detik, 10 detik
    public $action;
    public $data;

    public function __construct(string $action, array $data)
    {
        $this->action = $action;
        $this->data = $data;

        Log::info('[ProcessGitHubTicket] job initialized', [
            'action' => $this->action,
            'ticket_id' => $data['ticket_id'] ?? 'unknown',
        ]);
    }

    public function handle()
    {
        $github = app(GitHubService::class);
        $ticketId = $this->data['ticket_id'] ?? 'unknown';

        if ($this->action === 'create') {
            try {
                $response = $github->createIssue($this->data);

                if (!$response) {
                    Log::error('Failed to create GitHub issue', ['ticket_id' => $ticketId]);
                    return;
                }

                $ticket = Ticket::find($ticketId);
                if (!$ticket) {
                    Log::error('Ticket not found in database', ['ticket_id' => $ticketId]);
                    return;
                }

                $ticket->github_issue_url = $response['html_url'] ?? null;
                $ticket->github_issue_number = $response['number'] ?? null;

                $projectItemId = null;
                if (!empty($response['node_id'])) {
                    $projectItemId = $github->addToProject($response['node_id']);
                    $ticket->github_project_item_id = $projectItemId;
                } else {
                    Log::error('Missing node_id for adding to project', ['ticket_id' => $ticketId]);
                }
                $ticket->save();

                if ($projectItemId) {
                    $fieldValues = $this->prepareProjectFieldValues($ticket, $github, $projectItemId);
                    if (!empty($fieldValues)) {
                        $github->updateProjectFields($projectItemId, $fieldValues);
                    }
                }

            } catch (\GuzzleHttp\Exception\RequestException $e) {
                Log::error('Exception during create action', [
                    'ticket_id' => $ticketId,
                    'error' => $e->getMessage(),
                    'status_code' => $e->getResponse()?->getStatusCode(),
                ]);
                throw $e; // Lempar ulang untuk retry
            }
        } elseif ($this->action === 'update') {
            try {
                if (empty($this->data['issue_number'])) {
                    Log::error('[ProcessGitHubTicket-update] Cannot update GitHub issue: Missing issue_number', [
                        'ticket_id' => $ticketId
                    ]);
                    return;
                }

                $response = $github->updateIssue($this->data);

                if (!$response) {
                    Log::error('[ProcessGitHubTicket-update] Failed to update GitHub issue', [
                        'ticket_id' => $ticketId,
                        'issue_number' => $this->data['issue_number']
                    ]);
                    return;
                }

                $ticket = Ticket::find($ticketId);
                if (!$ticket) {
                    Log::error('[ProcessGitHubTicket-update] Ticket not found in database', [
                        'ticket_id' => $ticketId
                    ]);
                    return;
                }

                if (!empty($this->data['project_item_id'])) {
                    $fieldValues = $this->prepareProjectFieldValues($ticket, $github, $this->data['project_item_id']);
                    if (!empty($fieldValues)) {
                        $github->updateProjectFields($this->data['project_item_id'], $fieldValues);
                    }
                } else {
                    Log::warning('[ProcessGitHubTicket-update] Missing project_item_id for updating project fields', [
                        'ticket_id' => $ticketId
                    ]);
                }

                $ticket->save();
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                Log::error('Exception during update action', [
                    'ticket_id' => $ticketId,
                    'error' => $e->getMessage(),
                    'status_code' => $e->getResponse()?->getStatusCode(),
                ]);
                throw $e; // Lempar ulang untuk retry
            }
        } elseif ($this->action === 'close') {
            try {
                $issueNumber = $this->data['issue_number'] ?? null;

                if (!$issueNumber) {
                    Log::error('[ProcessGitHubTicket-close] Cannot process close action: Missing issue_number', [
                        'issue_number' => $issueNumber
                    ]);
                    return;
                }

                $ticket = Ticket::with('status')->where('github_issue_number', $issueNumber)->first();

                if (!$ticket) {
                    Log::warning('[ProcessGitHubTicket-close] Ticket not found for GitHub issue', [
                        'issue_number' => $issueNumber,
                    ]);
                    return;
                }

                $doneStatus = TicketStatus::whereIn('name', [
                    TicketStatus::STATUS_DONE,
                    TicketStatus::STATUS_SELESAI,
                ])->first();

                if (!$doneStatus) {
                    Log::error('[ProcessGitHubTicket-close] Done status not found in database', [
                        'ticket_id' => $ticket->id,
                        'issue_number' => $issueNumber,
                    ]);
                    return;
                }

                $ticket->status_id = $doneStatus->id;
                $ticket->save();

                Log::info('[ProcessGitHubTicket-close] Ticket status updated to Done', [
                    'ticket_id' => $ticket->id,
                    'issue_number' => $issueNumber,
                    'status_id' => $doneStatus->id,
                ]);

                /**
                 * Gunakan kode ini jika ingin mengupdate status issue di GitHub
                 * Dengan catatan, jika pada project github tidak ada workflow yang mengubah status issue secara otomatis ketika ada trigger issue is closed
                 * Note: Status default issue github adalah Done ketika issue ditutup
                 */
                if (!empty($ticket->github_project_item_id)) {
                    if ($github->updateIssueStatus($ticket->github_project_item_id, 'done')) {
                        Log::info('[ProcessGitHubTicket-close] GitHub issue status updated via updateIssueStatus', [
                            'project_item_id' => $ticket->github_project_item_id,
                            'status' => 'done',
                        ]);
                    }
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                Log::error('[ProcessGitHubTicket-close] Exception during close action', [
                    'ticket_id' => $ticketId,
                    'error' => $e->getMessage(),
                    'status_code' => $e->getResponse()?->getStatusCode(),
                ]);
                throw $e; // Lempar ulang untuk retry
            }
        }
    }

    /**
     * Prepare field values for updating GitHub project fields.
     *
     * @param Ticket $ticket
     * @param GitHubService $github
     * @param string $projectItemId
     * @return array
     */
    protected function prepareProjectFieldValues(Ticket $ticket, GitHubService $github, string $projectItemId): array
    {
        $fieldValues = [];
        $ticketId = $ticket->id;

        $fields = [
            'Status' => [
                'fieldIdMethod' => 'getProjectFieldId',
                'valueMethod' => 'getSingleSelectOptionId',
                'value' => $ticket->status?->name ?? 'unknown',
            ],
            'Ticket Authors' => [
                'fieldIdMethod' => 'getProjectFieldId',
                'valueMethod' => 'getSingleSelectOptionId',
                'value' => $ticket->owner?->name ?? 'unknown',
                'warning' => 'Add this author to the "Ticket Authors" field in the GitHub project manually.',
            ],
            'Modul' => [
                'fieldIdMethod' => 'getProjectFieldId',
                'valueMethod' => 'getSingleSelectOptionId',
                'value' => 'Issue',
            ],
        ];


        foreach ($fields as $fieldName => $config) {
            $fieldIdMethod = $config['fieldIdMethod'];
            $valueMethod = $config['valueMethod'];
            $value = $config['value'];

            $fieldId = $github->$fieldIdMethod($fieldName);

            if ($fieldId) {
                $optionId = $github->$valueMethod($fieldId, $value);
                if ($optionId) {
                    $fieldValues[] = [
                        'fieldId' => $fieldId,
                        'value' => ['singleSelectOptionId' => $optionId],
                    ];
                } else {
                    Log::warning("ProcessGitHubTicket] Option not found for {$fieldName}", [
                        'value' => $value,
                        'ticket_id' => $ticketId,
                        'action' => $config['warning'] ?? null,
                    ]);
                }
            }
        }

        return $fieldValues;
    }

    /**
     * Handle a job failure.
     *
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::error('[ProcessGitHubTicket] job failed', [
            'action' => $this->action,
            'ticket_id' => $this->data['ticket_id'] ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
