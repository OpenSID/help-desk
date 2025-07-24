<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GitHubWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verifikasi signature webhook
        $signature = $request->header('X-Hub-Signature-256');
        $payload = $request->getContent();
        $secret = config('services.github.webhook_secret');

        if (!$this->verifyWebhookSignature($payload, $signature, $secret)) {
            Log::warning('[GitHubWebhookController] GitHub webhook signature verification failed', [
                'signature' => $signature,
            ]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Ambil data dari payload
        $data = $request->all();
        $event = $request->header('X-GitHub-Event');

        if ($event !== 'issues') {
            Log::info('[GitHubWebhookController] Ignoring non-issues webhook event', ['event' => $event]);
            return response()->json(['status' => 'ignored'], 200);
        }

        $action = $data['action'] ?? null;
        $issueNumber = $data['issue']['number'] ?? null;
        $issueState = $data['issue']['state'] ?? null;

        if ($action === 'closed' && $issueNumber && $issueState === 'closed') {
            // Dispatch job untuk memperbarui status tiket
            \App\Jobs\ProcessGitHubTicket::dispatch('close', ['issue_number' => $issueNumber])->onQueue('github');
            Log::info('[GitHubWebhookController] GitHub issue closed, dispatching job to update ticket', [
                'issue_number' => $issueNumber,
            ]);
        } else {
            Log::info('[GitHubWebhookController] Ignoring webhook event', [
                'action' => $action,
                'issue_number' => $issueNumber,
                'state' => $issueState,
            ]);
        }

        return response()->json(['status' => 'success'], 200);
    }

    protected function verifyWebhookSignature(string $payload, ?string $signature, string $secret): bool
    {
        if (!$signature) {
            return false;
        }

        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $secret);
        return hash_equals($expectedSignature, $signature);
    }
}
