<?php

namespace App\Jobs;

use App\Services\GitHubService;
use Illuminate\Bus\Queueable;
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
    public $action;
    public $data;

    public function __construct(string $action, array $data)
    {
        $this->action = $action;
        $this->data = $data;
        $this->onQueue('github');
        Log::info('ProcessGitHubTicket job initialized', [
            'action' => $this->action,
            'ticket_id' => $data['ticket_id'] ?? 'unknown',
        ]);
    }

    public function handle()
    {
        $github = app(GitHubService::class);
        $ticketId = $this->data['ticket_id'] ?? 'unknown';

        if ($this->action === 'create') {
            $response = $github->createIssue($this->data);

            if (!$response) {
                Log::error('Failed to create GitHub issue', ['ticket_id' => $ticketId]);
                return;
            }

            $ticket = \App\Models\Ticket::find($ticketId);
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

            if ($projectItemId) {
                $statusFieldId = $github->getProjectFieldId('Status');
                $ticketAuthorFieldId = $github->getProjectFieldId('Ticket Authors');
                $fieldValues = [];

                if ($statusFieldId) {
                    $statusOptionId = $github->getSingleSelectOptionId($statusFieldId, $ticket->status?->name ?? 'unknown');
                    if ($statusOptionId) {
                        $fieldValues[] = [
                            'fieldId' => $statusFieldId,
                            'value' => ['singleSelectOptionId' => $statusOptionId],
                        ];
                    }
                }

                if ($ticketAuthorFieldId) {
                    $authorName = $ticket->owner?->name ?? 'unknown';
                    $authorOptionId = $github->getSingleSelectOptionId($ticketAuthorFieldId, $authorName);
                    if ($authorOptionId) {
                        $fieldValues[] = [
                            'fieldId' => $ticketAuthorFieldId,
                            'value' => ['singleSelectOptionId' => $authorOptionId],
                        ];
                    } else {
                        Log::warning('Author option not found, please add to project', [
                            'author' => $authorName,
                            'ticket_id' => $ticketId,
                            'action' => 'Add this author to the "Ticket Authors" field in the GitHub project manually.',
                        ]);
                    }
                }

                if (!empty($fieldValues)) {
                    $github->updateProjectFields($projectItemId, $fieldValues);
                }
            }

            $ticket->save();
        }
    }
}
