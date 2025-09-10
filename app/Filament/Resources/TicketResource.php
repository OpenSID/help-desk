<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Models\Epic;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketRelation;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;
use App\Models\MasterApplication;
use App\Models\TicketCategory;
use App\Models\Milestone;
use Carbon\Carbon;
use App\Models\IssueSource;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 2;

    /**
     * Mendapatkan label navigasi untuk resource ini.
     *
     * @return string Label navigasi (diterjemahkan)
     */
    protected static function getNavigationLabel(): string
    {
        return __('Tickets');
    }

    /**
     * Mendapatkan label jamak (plural) untuk resource ini.
     *
     * @return string|null Label plural (diterjemahkan)
     */
    public static function getPluralLabel(): ?string
    {
        return static::getNavigationLabel();
    }

    /**
     * Mendapatkan grup navigasi untuk resource ini.
     *
     * @return string|null Nama grup navigasi (diterjemahkan)
     */
    protected static function getNavigationGroup(): ?string
    {
        return __('Management');
    }

    /**
     * Mendefinisikan form input untuk create/edit tiket.
     *
     * @param Form $form Instance form Filament
     * @return Form Form dengan skema input
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Card utama untuk form tiket
                Forms\Components\Card::make()
                    ->schema([
                        // Grid utama
                        Forms\Components\Grid::make()
                            ->schema([
                                // Pilihan project
                                Forms\Components\Select::make('project_id')
                                    ->label(__('Project'))
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($get, $set) {
                                        // Update status_id sesuai project
                                        $project = Project::where('id', $get('project_id'))->first();
                                        if ($project?->status_type === 'custom') {
                                            $set(
                                                'status_id',
                                                TicketStatus::where('project_id', $project->id)
                                                    ->where('is_default', true)
                                                    ->first()
                                                    ?->id
                                            );
                                        } else {
                                            $set(
                                                'status_id',
                                                TicketStatus::whereNull('project_id')
                                                    ->where('is_default', true)
                                                    ->first()
                                                    ?->id
                                            );
                                        }
                                    })
                                    ->options(fn() => Project::where('owner_id', auth()->user()->id)
                                        ->orWhereHas('users', function ($query) {
                                            return $query->where('users.id', auth()->user()->id);
                                        })->pluck('name', 'id')->toArray()
                                    )
                                    ->default(fn() => request()->get('project'))
                                    ->required(),
                                // Pilihan epic
                                Forms\Components\Select::make('epic_id')
                                    ->label(__('Epic'))
                                    ->searchable()
                                    ->reactive()
                                    ->options(function ($get, $set) {
                                        return Epic::where('project_id', $get('project_id'))->pluck('name', 'id')->toArray();
                                    }),
                                // Grid untuk kode dan nama tiket
                                Forms\Components\Grid::make()
                                    ->columns(12)
                                    ->columnSpan(2)
                                    ->schema([
                                        // Input kode tiket (hanya edit)
                                        Forms\Components\TextInput::make('code')
                                            ->label(__('Ticket code'))
                                            ->visible(fn($livewire) => !($livewire instanceof CreateRecord))
                                            ->columnSpan(2)
                                            ->disabled(),

                                        // Input nama tiket
                                        Forms\Components\TextInput::make('name')
                                            ->label(__('Ticket name'))
                                            ->required()
                                            ->columnSpan(
                                                fn($livewire) => !($livewire instanceof CreateRecord) ? 10 : 12
                                            )
                                            ->maxLength(255),
                                    ]),
                                // Pilihan owner tiket
                                Forms\Components\Select::make('owner_id')
                                    ->label(__('Ticket owner'))
                                    ->searchable()
                                    ->options(fn() => User::all()->pluck('name', 'id')->toArray())
                                    ->default(fn() => auth()->user()->id)
                                    ->required(),
                                // Pilihan penanggung jawab tiket
                                Forms\Components\Select::make('responsible_id')
                                    ->label(__('Ticket responsible'))
                                    ->searchable()
                                    ->options(fn() => User::all()->pluck('name', 'id')->toArray())
                                    ->required(),

                                Forms\Components\Select::make('master_application_id')
                                    ->label(__('Aplikasi'))
                                    ->searchable()
                                    ->options(fn() => MasterApplication::all()->pluck('name', 'id')->toArray()),

                                // Pilihan kategori solusi (bisa banyak)
                                Forms\Components\Select::make('categories')
                                    ->label(__('Solution Categories'))
                                    ->multiple()
                                    ->options(fn () => TicketCategory::pluck('name', 'id')->toArray())
                                    ->preload()
                                    ->searchable(),

                                Forms\Components\Select::make('issue_source_id')
                                    ->label(__('Issue Source'))
                                    ->searchable()
                                    ->options(fn() => IssueSource::all()->pluck('name', 'id')->toArray()),

                                // Grid untuk status, tipe, prioritas
                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->columnSpan(2)
                                    ->schema([
                                        // Pilihan status tiket
                                        Forms\Components\Select::make('status_id')
                                            ->label(__('Ticket status'))
                                            ->searchable()
                                            ->options(function ($get) {
                                                $project = Project::where('id', $get('project_id'))->first();
                                                if ($project?->status_type === 'custom') {
                                                    return TicketStatus::where('project_id', $project->id)
                                                        ->get()
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                } else {
                                                    return TicketStatus::whereNull('project_id')
                                                        ->get()
                                                        ->pluck('name', 'id')
                                                        ->toArray();
                                                }
                                            })
                                            ->default(function ($get) {
                                                $project = Project::where('id', $get('project_id'))->first();
                                                if ($project?->status_type === 'custom') {
                                                    return TicketStatus::where('project_id', $project->id)
                                                        ->where('is_default', true)
                                                        ->first()
                                                        ?->id;
                                                } else {
                                                    return TicketStatus::whereNull('project_id')
                                                        ->where('is_default', true)
                                                        ->first()
                                                        ?->id;
                                                }
                                            })
                                            ->required(),
                                        // Pilihan tipe tiket
                                        Forms\Components\Select::make('type_id')
                                            ->label(__('Ticket type'))
                                            ->searchable()
                                            ->options(fn() => TicketType::all()->pluck('name', 'id')->toArray())
                                            ->default(fn() => TicketType::where('is_default', true)->first()?->id)
                                            ->required(),
                                        // Pilihan prioritas tiket
                                        Forms\Components\Select::make('priority_id')
                                            ->label(__('Ticket priority'))
                                            ->searchable()
                                            ->options(fn() => TicketPriority::all()->pluck('name', 'id')->toArray())
                                            ->default(fn() => TicketPriority::where('is_default', true)->first()?->id)
                                            ->required(),
                                        // Pilihan milestone tiket
                                        Forms\Components\Select::make('milestone_id')
                                            ->label(__('Milestone'))
                                            ->searchable()
                                            ->options(fn () =>
                                                Milestone::where('access_status', 'Open') // ✅ hanya yang open
                                                    ->pluck('name', 'id')
                                                    ->toArray()
                                            )
                                            ->default(function () {
                                                $today = Carbon::today();

                                                $activeMilestone = Milestone::where('access_status', 'Open') // ✅ hanya milestone open
                                                    ->whereDate('start_date', '<=', $today)
                                                    ->whereDate('end_date', '>=', $today)
                                                    ->first();

                                                return $activeMilestone?->id;
                                            })
                                            ,
                                    ]),
                            ]),

                        // Editor konten tiket
                        Forms\Components\RichEditor::make('content')
                            ->label(__('Ticket content'))
                            ->required()
                            ->columnSpan(2),

                        // Grid estimasi waktu
                        Forms\Components\Grid::make()
                            ->columnSpan(2)
                            ->columns(12)
                            ->schema([
                                Forms\Components\TextInput::make('estimation')
                                    ->label(__('Estimation time'))
                                    ->numeric()
                                    ->columnSpan(2),
                            ]),

                        // Repeater relasi tiket
                        Forms\Components\Repeater::make('relations')
                            ->itemLabel(function (array $state) {
                                $ticketRelation = TicketRelation::find($state['id'] ?? 0);
                                if ($ticketRelation) {
                                    return __(config('system.tickets.relations.list.' . $ticketRelation->type))
                                        . ' '
                                        . $ticketRelation->relation->name
                                        . ' (' . $ticketRelation->relation->code . ')';
                                }
                                return null;
                            })
                            ->relationship()
                            ->collapsible()
                            ->collapsed()
                            ->orderable()
                            ->defaultItems(0)
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->columns(3)
                                    ->schema([
                                        // Pilihan tipe relasi
                                        Forms\Components\Select::make('type')
                                            ->label(__('Relation type'))
                                            ->required()
                                            ->searchable()
                                            ->options(config('system.tickets.relations.list'))
                                            ->default(fn() => config('system.tickets.relations.default')),


                                        // Pilihan tiket terkait
                                        Forms\Components\Select::make('relation_id')
                                            ->label(__('Related ticket'))
                                            ->required()
                                            ->searchable()
                                            ->columnSpan(2)
                                            ->options(function ($livewire) {
                                                $query = Ticket::query();
                                                // filter hanya bulan berjalan
                                                $query->whereMonth('created_at', now()->month)
                                                    ->whereYear('created_at', now()->year);
                                                if ($livewire instanceof EditRecord && $livewire->record) {
                                                    $query->where('id', '<>', $livewire->record->id);
                                                }
                                                return $query->get()->pluck('name', 'id')->toArray();
                                            }),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    /**
     * Mendefinisikan kolom tabel untuk daftar tiket.
     *
     * @param bool $withProject Apakah menampilkan kolom project
     * @return array Daftar kolom tabel
     */
    public static function tableColumns(bool $withProject = true): array
    {
        $columns = [];
        if ($withProject) {
            $columns[] = Tables\Columns\TextColumn::make('project.name')
                ->label(__('Project'))
                ->sortable()
                ->searchable();
        }
        $columns = array_merge($columns, [
            // Kolom nama tiket
            Tables\Columns\TextColumn::make('name')
                ->label(__('Ticket name'))
                ->sortable()
                ->searchable(),

            // Kolom owner
            Tables\Columns\TextColumn::make('owner.name')
                ->label(__('Owner'))
                ->sortable()
                ->formatStateUsing(fn($record) => view('components.user-avatar', ['user' => $record->owner]))
                ->searchable(),

            // Kolom penanggung jawab
            Tables\Columns\TextColumn::make('responsible.name')
                ->label(__('Responsible'))
                ->sortable()
                ->formatStateUsing(fn($record) => view('components.user-avatar', ['user' => $record->responsible]))
                ->searchable(),

            // Kolom status
            Tables\Columns\TextColumn::make('status.name')
                ->label(__('Status'))
                ->formatStateUsing(fn($record) => new HtmlString('
                            <div class="flex items-center gap-2 mt-1">
                                <span class="filament-tables-color-column relative flex h-6 w-6 rounded-md"
                                    style="background-color: ' . $record->status->color . '"></span>
                                <span>' . $record->status->name . '</span>
                            </div>
                        '))
                ->sortable()
                ->searchable(),

            // Kolom tipe
            Tables\Columns\TextColumn::make('type.name')
                ->label(__('Type'))
                ->formatStateUsing(
                    fn($record) => view('partials.filament.resources.ticket-type', ['state' => $record->type])
                )
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('masterApplication.name')
                ->label(__('Master Application'))
                ->formatStateUsing(function ($record) {
                    if (!$record->masterApplication) {
                        return '-'; // atau bisa diganti teks lain seperti "Tidak ada aplikasi"
                    }

                    return view('partials.filament.resources.master-application', ['state' => $record->masterApplication]);
                })
                ->sortable(false)
                ->searchable(),

            // Kolom kategori
            Tables\Columns\TextColumn::make('categories.name')
                ->label(__('Solution Categories'))
                ->formatStateUsing(
                    fn($record) => view('partials.filament.resources.ticket-category', ['state' => $record->categories])
                )
                ->sortable(false)
                ->searchable(),

            // kolom sumber masalah
            Tables\Columns\TextColumn::make('IssueSource.name')
                ->label(__('Issue Source'))
                ->formatStateUsing(function ($record) {
                    if (!$record->issueSource) {
                        return '-'; // atau bisa diganti teks lain seperti "Tidak ada aplikasi"
                    }

                    return view('partials.filament.resources.issue-source', ['state' => $record->issueSource]);
                })
                ->searchable(),

            // Kolom prioritas
            Tables\Columns\TextColumn::make('priority.name')
                ->label(__('Priority'))
                ->formatStateUsing(fn($record) => new HtmlString('
                            <div class="flex items-center gap-2 mt-1">
                                <span class="filament-tables-color-column relative flex h-6 w-6 rounded-md"
                                    style="background-color: ' . $record->priority->color . '"></span>
                                <span>' . $record->priority->name . '</span>
                            </div>
                        '))
                ->sortable()
                ->searchable(),

            // Kolom milestone
            Tables\Columns\TextColumn::make('milestone.name')
                ->label(__('Milestone'))
                ->formatStateUsing(
                    fn($record) => view('partials.filament.resources.milestone', ['state' => $record->milestone])
                )
                ->sortable()
                ->searchable(),


            // Kolom tanggal dibuat
            Tables\Columns\TextColumn::make('created_at')
                ->label(__('Created at'))
                ->dateTime()
                ->sortable()
                ->searchable(),
        ]);
        return $columns;
    }

    /**
     * Mendefinisikan tabel daftar tiket.
     *
     * @param Table $table Instance tabel Filament
     * @return Table Tabel dengan kolom dan aksi
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::tableColumns())
            ->filters([
                // Filter project
                Tables\Filters\SelectFilter::make('project_id')
                    ->label(__('Project'))
                    ->multiple()
                    ->options(fn() => Project::where('owner_id', auth()->user()->id)
                        ->orWhereHas('users', function ($query) {
                            return $query->where('users.id', auth()->user()->id);
                        })->pluck('name', 'id')->toArray()),

                // Filter owner
                Tables\Filters\SelectFilter::make('owner_id')
                    ->label(__('Owner'))
                    ->multiple()
                    ->options(fn() => User::all()->pluck('name', 'id')->toArray()),

                // Filter penanggung jawab
                Tables\Filters\SelectFilter::make('responsible_id')
                    ->label(__('Responsible'))
                    ->multiple()
                    ->options(fn() => User::all()->pluck('name', 'id')->toArray()),

                // Filter status
                Tables\Filters\SelectFilter::make('status_id')
                    ->label(__('Status'))
                    ->multiple()
                    ->options(fn() => TicketStatus::all()->pluck('name', 'id')->toArray()),

                // Filter tipe
                Tables\Filters\SelectFilter::make('type_id')
                    ->label(__('Type'))
                    ->multiple()
                    ->options(fn() => TicketType::all()->pluck('name', 'id')->toArray()),

                // Filter masteraplikasi
                Tables\Filters\SelectFilter::make('master_application_id')
                    ->label(__('Master Application'))
                    ->multiple()
                    ->options(fn() => MasterApplication::all()->pluck('name', 'id')->toArray()),

                // Filter kategori
                Tables\Filters\SelectFilter::make('categories')
                    ->label(__('Solution Categories'))
                    ->multiple()
                    ->options(fn () => \App\Models\TicketCategory::pluck('name', 'id')->toArray())
                    ->query(function ($query, $data) {
                        if (!empty($data['values'])) {
                            $query->whereHas('categories', function ($q) use ($data) {
                                $q->whereIn('ticket_categories.id', $data['values']);
                            });
                        }
                    }),

                // Filter prioritas
                Tables\Filters\SelectFilter::make('priority_id')
                    ->label(__('Priority'))
                    ->multiple()
                    ->options(fn() => TicketPriority::all()->pluck('name', 'id')->toArray()),

                // Filter milestone
                Tables\Filters\SelectFilter::make('milestone_id')
                    ->label(__('Milestone'))
                    ->multiple()
                    ->options(fn() => Milestone::all()->pluck('name', 'id')->toArray()),

                // Filter sumber masalah
                Tables\Filters\SelectFilter::make('issue_source_id')
                    ->label(__('Issue Source'))
                    ->multiple()
                    ->options(fn() => IssueSource::all()->pluck('name', 'id')->toArray()),
            ])
            ->actions([
                // Aksi lihat dan edit
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Aksi hapus massal
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /**
     * Mendefinisikan relasi yang tersedia untuk resource ini.
     *
     * @return array Daftar relasi
     */
    public static function getRelations(): array
    {
        return [
            // Tambahkan relasi jika ada
        ];
    }

    /**
     * Mendefinisikan halaman-halaman (routes) untuk resource ini.
     *
     * @return array Daftar halaman dan route-nya
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'view' => Pages\ViewTicket::route('/{record}'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
