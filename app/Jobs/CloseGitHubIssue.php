<?php

namespace App\Jobs;

use App\Models\Ticket;
use App\Services\GitHubService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CloseGitHubIssue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3; // Coba ulang 3 kali jika gagal
    public $backoff = [1, 5, 10]; // Exponential backoff: 1 detik, 5 detik, 10 detik

    protected $ticketId;
    protected $issueNumber;

    /**
     * Create a new job instance.
     *
     * @param int $ticketId
     * @param int $issueNumber
     */
    public function __construct(int $ticketId, int $issueNumber)
    {
        $this->ticketId = $ticketId;
        $this->issueNumber = $issueNumber;

        Log::info('[CloseGitHubIssue] Job initialized', [
            'ticket_id' => $this->ticketId,
            'issue_number' => $this->issueNumber,
        ]);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $ticket = Ticket::find($this->ticketId);

            if (!$ticket) {
                Log::error('[CloseGitHubIssue] Ticket not found', [
                    'ticket_id' => $this->ticketId,
                ]);
                return;
            }

            if (!$this->issueNumber) {
                Log::warning('[CloseGitHubIssue] No GitHub issue number for ticket', [
                    'ticket_id' => $this->ticketId,
                ]);
                return;
            }

            $github = app(GitHubService::class);
            $success = $github->closeIssue($this->issueNumber);

            if ($success) {
                Log::info('[CloseGitHubIssue] Successfully closed GitHub issue', [
                    'ticket_id' => $this->ticketId,
                    'issue_number' => $this->issueNumber,
                ]);

                // Update status di GitHub Project jika ada project_item_id
                if ($ticket->github_project_item_id) {
                    try {
                        $github->updateIssueStatus($ticket->github_project_item_id, 'done');
                        Log::info('[CloseGitHubIssue] Updated GitHub project status to done', [
                            'ticket_id' => $this->ticketId,
                            'project_item_id' => $ticket->github_project_item_id,
                        ]);
                    } catch (\Exception $e) {
                        Log::warning('[CloseGitHubIssue] Failed to update GitHub project status', [
                            'ticket_id' => $this->ticketId,
                            'project_item_id' => $ticket->github_project_item_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } else {
                Log::error('[CloseGitHubIssue] Failed to close GitHub issue', [
                    'ticket_id' => $this->ticketId,
                    'issue_number' => $this->issueNumber,
                ]);
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            Log::error('[CloseGitHubIssue] Exception during close action', [
                'ticket_id' => $this->ticketId,
                'issue_number' => $this->issueNumber,
                'error' => $e->getMessage(),
                'status_code' => $e->getResponse()?->getStatusCode(),
            ]);
            throw $e; // Lempar ulang untuk retry
        } catch (\Exception $e) {
            Log::error('[CloseGitHubIssue] Unexpected exception', [
                'ticket_id' => $this->ticketId,
                'issue_number' => $this->issueNumber,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        Log::error('[CloseGitHubIssue] Job failed after all retries', [
            'ticket_id' => $this->ticketId,
            'issue_number' => $this->issueNumber,
            'error' => $exception->getMessage(),
        ]);
    }
}
