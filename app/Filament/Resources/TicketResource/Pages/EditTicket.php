<?php

namespace App\Filament\Resources\TicketResource\Pages;

use Filament\Pages\Actions;
use Illuminate\Support\Facades\Log;
use Filament\Resources\Pages\EditRecord;
use League\HTMLToMarkdown\HtmlConverter;
use App\Filament\Resources\TicketResource;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected array $categories = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['categories'] = $this->record->categories()->pluck('id')->toArray();
        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->categories()->sync($this->data['categories'] ?? []);

        try {
            // Ambil instance HtmlConverter dari container
            $converter = app(HtmlConverter::class);

            // Bersihkan HTML: ganti <br> berturut-turut dengan satu <br>
            $cleanedHtml = preg_replace('/<br\s*\/?>\s*<br\s*\/?>/i', '<br>', $this->record->content ?? '');

            $markdownContent = $converter->convert($cleanedHtml);

            // Hilangkan backslash dari URL (misal: \_ menjadi _)
            $markdownContent = str_replace(['\_', '\*', '\[', '\]'], ['_', '*', '[', ']'], $markdownContent);

            // Persiapkan data untuk pembaruan GitHub
            $labels = [
                'Helpdesk',
                $this->record->type?->name ?? 'default',
                $this->record->project?->name ?? 'unknown',
            ];

            $labelColors = [
                'Helpdesk' => '0000FF',
                $this->record->type?->name ?? 'default' => $this->record->type?->color ?? 'D3D3D3',
                $this->record->project?->name => 'D3D3D3',
            ];

            $githubData = [
                'title' => $this->record->name,
                'body' => $markdownContent ?? '',
                'assignees' => $this->record->responsible?->github_username ? [$this->record->responsible->github_username] : [],
                'labels' => $labels,
                'label_colors' => $labelColors,
                'ticket_id' => $this->record->id,
                'issue_number' => $this->record->github_issue_number,
                'project_item_id' => $this->record->github_project_item_id,
            ];

            // Log data untuk debugging
            Log::info('[EditTicket] Dispatching ProcessGitHubTicket job for update', [
                'ticket_id' => $this->record->id,
                'github_data' => $githubData,
            ]);

            // Dispatch job untuk memperbarui issue dan proyek GitHub
            \App\Jobs\ProcessGitHubTicket::dispatch('update', $githubData);
        } catch (\Exception $e) {
            Log::error('[EditTicket] Error queuing GitHub issue update: ' . $e->getMessage(), [
                'ticket_id' => $this->record->id ?? 'unknown',
            ]);
            throw $e;
        }
    }

}
