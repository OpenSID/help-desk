<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;

class ExportReport extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.export-report';

    public static function getNavigationLabel(): string
    {
        return __('Export Report');
    }

    public function getTitle(): string
    {
        return __('Export Report');
    }

    protected static ?int $navigationSort = 2;

    public bool $saved = false;
    public ?string $completion_report = null;

    public function mount(): void
    {
        $month = now()->month;
        $year = now()->year;

        // cari data lama di tabel completion_report
        $data = DB::table('completion_report')
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $this->form->fill([
            'month'   => $month,
            'year'    => $year,
            'completion_report' => $data ? $data->completion_report : '',
        ]);

        $this->saved = $data ? true : false;
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(4)->schema([
                Select::make('month')
                    ->label(__('Month'))
                    ->options($this->getMonths())
                    ->reactive(),

                Select::make('year')
                    ->label(__('Year'))
                    ->options($this->getYears())
                    ->reactive(),
            ]),

            RichEditor::make('completion_report')
                ->label(__('Completion'))
                ->reactive()
                ->afterStateUpdated(fn ($state) => $this->completion_report = $state),
        ];
    }

    public function updatedMonth($value, $key): void
    {
        $state = $this->form->getState();
        $data = DB::table('completion_report')
            ->where('month', $state['month'])
            ->where('year', $state['year'])
            ->first();
        // dd($data);
        $this->form->fill([
            'month' => $state['month'],
            'year' => $state['year'],
            'completion_report' => $data ? $data->completion_report : '',
        ]);

        $this->saved = $data ? true : false;
    }

    public function updatedYear(): void
    {
        $state = $this->form->getState();
        $this->updatedMonth($state['month'] ?? null, 'month'); // biar logika sama
    }

    protected function getMonths(): array
    {
        return collect(range(1, 12))
            ->mapWithKeys(fn ($m) => [$m => Carbon::create()->month($m)->translatedFormat('F')])
            ->toArray();
    }

    protected function getYears(): array
    {
        $now = now()->year;
        return collect(range($now - 2, $now))
            ->mapWithKeys(fn ($y) => [$y => $y])
            ->toArray();
    }

    public function save(): void
    {
        $state = $this->form->getState();
        // dd($state);
        if (empty($state['month']) || empty($state['year']) || empty($state['completion_report'])) {
            Notification::make()
                ->title(__('Isi semua field terlebih dahulu'))
                ->danger()
                ->send();
            return;
        }

        DB::table('completion_report')->updateOrInsert(
            ['month' => $state['month'], 'year' => $state['year']],
            ['completion_report' => $state['completion_report']]
        );

        $this->saved = true;

        Notification::make()
            ->title(__('Data berhasil disimpan'))
            ->success()
            ->send();
    }

    public function export()
    {
        if (! $this->saved) {
            Notification::make()
                ->title(__('Isi & simpan data dulu sebelum export'))
                ->danger()
                ->send();
            return;
        }
        $state = $this->form->getState();

        $ticketTotal = DB::table('tickets')
            ->whereYear('created_at', $state['year'])
            ->whereMonth('created_at', $state['month'])
            ->get();

        $ticketByApplication = DB::table('tickets')
            ->select('master_applications.name', DB::raw('COUNT(*) as total'))
            ->join('master_applications', 'tickets.master_application_id', '=', 'master_applications.id')
            ->whereYear('tickets.created_at', $state['year'])
            ->whereMonth('tickets.created_at', $state['month'])
            ->groupBy('master_applications.name')
            ->get();

        $ticketByService = DB::table('tickets')
            ->select('projects.name', DB::raw('COUNT(*) as total'))
            ->join('projects', 'tickets.project_id', '=', 'projects.id')
            ->whereYear('tickets.created_at', $state['year'])
            ->whereMonth('tickets.created_at', $state['month'])
            ->groupBy('projects.name')
            ->get();

        $ticketDuplicate = DB::table('ticket_relations')
            ->join('tickets', 'ticket_relations.relation_id', '=', 'tickets.id')
            ->selectRaw("
                TRIM(
                    SUBSTRING_INDEX(tickets.name, '-', 1)
                ) as clean_name,
                COUNT(*) as total
            ")
            ->whereYear('ticket_relations.created_at', $state['year'])
            ->whereMonth('ticket_relations.created_at', $state['month'])
            ->groupBy('tickets.name')
            ->orderByDesc('total')
            ->get();

        $completionReport = DB::table('completion_report')
            ->where('month', $state['month'])
            ->where('year', $state['year'])
            ->value('completion_report');

        // dd($ticketDuplicate);
        return Excel::download(
            new ReportExport($state, $ticketTotal, $ticketByApplication, $ticketByService, $ticketDuplicate, $completionReport),
            'laporan-' . $state['year'] . '-' . str_pad($state['month'], 2, '0', STR_PAD_LEFT) . '.xlsx'
        );
    }
}
