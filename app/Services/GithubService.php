<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GithubService
{
    protected string $baseUrl = 'https://api.github.com';

    public function createIssue(array $data)
    {
        $response = Http::withToken(config('services.github.token'))
            ->accept('application/vnd.github+json')
            ->post("{$this->baseUrl}/repos/" . config('services.github.owner') . "/" . config('services.github.repo') . "/issues", [
                'title' => $data['title'],
                'body' => $data['body'] ?? null,
                'assignees' => $data['assignees'] ?? [],
                'labels' => $data['labels'] ?? [],
            ]);

        if (!$response->successful()) {
            Log::error('GitHub API Error:', $response->json());
            return null;
        }

        return $response->json();
    }

    public function updateIssue(int $issueNumber, array $data)
    {
        $response = Http::withToken(config('services.github.token'))
            ->accept('application/vnd.github+json')
            ->patch("{$this->baseUrl}/repos/" . config('services.github.owner') . "/" . config('services.github.repo') . "/issues/{$issueNumber}", $data);

        return $response->successful();
    }

    public function closeIssue(int $issueNumber)
    {
        return $this->updateIssue($issueNumber, ['state' => 'closed']);
    }

    public function addComment(int $issueNumber, string $comment)
    {
        $response = Http::withToken(config('services.github.token'))
            ->post("{$this->baseUrl}/repos/" . config('services.github.owner') . "/" . config('services.github.repo') . "/issues/{$issueNumber}/comments", [
                'body' => $comment
            ]);

        return $response->successful();
    }

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

        $response = Http::withToken(config('services.github.token'))
            ->post("https://api.github.com/graphql", [
                'query' => $query,
                'variables' => [
                    'projectId' => config('services.github.project_id'),
                    'contentId' => $issueNodeId,
                ],
            ]);

        if (!$response->successful()) {
            Log::error("Failed to add to project", [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        }

        return $response->successful();
    }

    //     public function addToProject(int $issueId)
    //     {
    //         $query = <<<'GRAPHQL'
    // mutation($projectId: ID!, $contentId: ID!) {
    //   addProjectV2ItemById(input: {
    //     projectId: $projectId,
    //     contentId: $contentId
    //   }) {
    //     item {
    //       id
    //     }
    //   }
    // }
    // GRAPHQL;

    //         $response = Http::withToken(config('services.github.token'))
    //             ->post("https://api.github.com/graphql", [
    //                 'query' => $query,
    //                 'variables' => [
    //                     'projectId' => config('services.github.project_id'),
    //                     'contentId' => $issueId,
    //                 ],
    //             ]);

    //         return $response->successful();
    //     }
}
