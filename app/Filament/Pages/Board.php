<?php

namespace App\Filament\Pages;

use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Board extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $slug = 'board';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.board';

    public ?int $project = null;

    public function getSubheading(): string|Htmlable|null
    {
        return __("In this section you can choose one of your projects to show its Scrum or Kanban board");
    }

    public static function getNavigationLabel(): string
    {
        return __('Board');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Management');
    }

    // ✅ gunakan Form method di Filament 3, bukan getFormSchema()
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns(1)
                            ->schema([
                                Forms\Components\Select::make('project')
                                    ->label(__('Project'))
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->helperText(__("Choose a project to show its board"))
                                    ->options(fn() => Project::query()
                                        ->where('owner_id', auth()->id())
                                        ->orWhereHas('users', fn($q) => $q->where('users.id', auth()->id()))
                                        ->pluck('name', 'id')
                                        ->toArray()),
                            ]),
                    ]),
            ]);
    }

    public function updatedProject($value): void
    {
        if (! $value) return;
        $this->redirectToProject($value);
    }

    protected function redirectToProject(int $projectId): void
    {
        $project = Project::find($projectId);

        if (! $project) {
            return;
        }

        if ($project->type === 'scrum') {
            $this->redirect("/scrum/{$project->id}");
        } else {
            $this->redirect("/kanban/{$project->id}");
        }
    }
}
