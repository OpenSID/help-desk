<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use App\Models\Project;

class TicketByServiceChart extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.ticket-by-service-chart';

    public function getHeading(): string
    {
        return __('Trend by service');
    }

    public ?array $formData = [];

    public function mount(): void
    {
        $this->form->fill([
            'start_month'              => now()->subMonths(2)->month,
            'start_year'               => now()->subMonths(2)->year,
            'end_month'                => now()->month,
            'end_year'                 => now()->year,
            'project_id'               => Project::first()->id, // Default to 'Semua'
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(5)->schema([
                Select::make('start_month')
                    ->label(__('Start month'))
                    ->options($this->getMonths())
                    ->reactive(),

                Select::make('start_year')
                    ->label(__('Start year'))
                    ->options($this->getYears())
                    ->reactive(),

                Select::make('end_month')
                    ->label(__('End month'))
                    ->options($this->getMonths())
                    ->reactive(),

                Select::make('end_year')
                    ->label(__('End year'))
                    ->options($this->getYears())
                    ->reactive(),

                Select::make('project_id')
                    ->label(__('Project'))
                    ->options(function () {
                        return Project::all()->pluck('name', 'id')->toArray();
                    })
                    ->reactive(),
            ]),

        ];
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

    public function getChartData(): array
    {

        $state = $this->form->getState();
        // var_dump($state);

        if (!empty($state['start_month']) && !empty($state['start_year']) && !empty($state['end_month']) && !empty($state['end_year'])) {
            $start = Carbon::create($state['start_year'], $state['start_month'], 1)->startOfMonth();
            $end   = Carbon::create($state['end_year'], $state['end_month'], 1)->endOfMonth();
        } else {
            $start = now()->subMonths(2)->startOfMonth();
            $end   = now()->endOfMonth();
        }

        $tickets = DB::table('tickets')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total")
            ->whereBetween('created_at', [$start, $end])
            ->where('project_id', (int)$state['project_id'])
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();


        return [
            'labels' => $tickets->pluck('bulan'),
            'data'   => $tickets->pluck('total'),
        ];
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full'; // biar full 1 row
    }


    public function updated($name, $value): void
    {
        $chartData = $this->getChartData();

        $this->dispatchBrowserEvent('updateServiceChart', [
            'labels' => $chartData['labels']->toArray(),
            'data'   => $chartData['data']->toArray(),
        ]);
    }

    public function initChart()
    {
        $chartData = $this->getChartData();

        $this->dispatchBrowserEvent('renderServiceChart', [
            'labels' => $chartData['labels']->toArray(),
            'data'   => $chartData['data']->toArray(),
        ]);
    }
}
