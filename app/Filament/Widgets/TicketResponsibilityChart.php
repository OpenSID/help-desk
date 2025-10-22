<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use App\Models\User;

class TicketResponsibilityChart extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.ticket-responsibility-chart';

    public function getHeading(): string
    {
        return __('Trend by responsible');
    }

    // ✅ deklarasi semua properti Livewire
    public int $start_month;
    public int $start_year;
    public int $end_month;
    public int $end_year;
    public int $responsibility_id;

    public function mount(): void
    {
        $this->start_month          = now()->subMonths(2)->month;
        $this->start_year           = now()->subMonths(2)->year;
        $this->end_month            = now()->month;
        $this->end_year             = now()->year;
        $this->resposibility_id     = 0; // Default to 'Semua'

        $this->form->fill([
            'start_month' => $this->start_month,
            'start_year'  => $this->start_year,
            'end_month'   => $this->end_month,
            'end_year'    => $this->end_year,
            'responsibility_id'    => $this->resposibility_id // Default to 'Semua'
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

                Select::make('responsibility_id')
                    ->label(__('Responsible'))
                    ->options(function () {
                        return [0 => 'Semua'] + User::where('role', 'devops')->pluck('name', 'id')->toArray();
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

        if((int)$state['responsibility_id'] === 0){

            $devopsIds = User::where('role', 'devops')->pluck('id');

            $tickets = DB::table('tickets')
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, responsible_id, COUNT(*) as total")
                ->whereIn('responsible_id', $devopsIds)
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('bulan','responsible_id')
                ->orderBy('bulan')
                ->get();

            $series = [];
            foreach ($devopsIds as $resposibleId) {
                $responsibleName = User::find($resposibleId)?->name ?? 'Responsible '.$resposibleId;
                $data = [];

                // Loop dari start sampai end untuk generate bulan langsung
                for($dt = $start->copy(); $dt <= $end; $dt->addMonth()){
                    $month = $dt->format('Y-m');
                    $found = $tickets->firstWhere(fn($t) => $t->bulan === $month && $t->responsible_id == $resposibleId);
                    $data[] = $found ? $found->total : 0;
                }

                $series[] = [
                    'name' => $responsibleName,
                    'data' => $data
                ];
            }

            return [
                'labels' => array_map(fn($d) => $d->format('Y-m'), iterator_to_array(\Carbon\CarbonPeriod::create($start, '1 month', $end))),
                'series' => $series,
            ];
        } else {
            $tickets = DB::table('tickets')
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bulan, COUNT(*) as total")
                ->whereBetween('created_at', [$start, $end])
                ->where('responsible_id', (int)$state['responsibility_id'])
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get();

            $responsibleName = User::find((int)$state['responsibility_id'])?->name ?? 'Responsible '.$state['responsibility_id'];
            $data = [];

            for($dt = $start->copy(); $dt <= $end; $dt->addMonth()){
                $month = $dt->format('Y-m');
                $found = $tickets->firstWhere('bulan', $month);
                $data[] = $found ? $found->total : 0;
            }

            return [
                'labels' => array_map(fn($d) => $d->format('Y-m'), iterator_to_array(\Carbon\CarbonPeriod::create($start, '1 month', $end))),
                'series' => [
                    [
                        'name' => $responsibleName,
                        'data' => $data
                    ]
                ],
            ];
        }
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full'; // biar full 1 row
    }


    public function updated($name, $value): void
    {
        $chartData = $this->getChartData();

        $this->dispatch('updateResponsibilityChart', [
            'labels' => $chartData['labels'],
            'datasets' => collect($chartData['series'])->map(function ($s, $i) {
                $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ffc0cb']; // biru, hijau, kuning, pink
                return [
                    'label' => $s['name'],
                    'data' => $s['data'],
                    'backgroundColor' => $colors[$i % count($colors)],
                ];
            })->toArray(),
        ]);
    }

    public function initChart()
    {
        $chartData = $this->getChartData();

        $this->dispatch('renderResponsibilityChart', [
            'labels' => $chartData['labels'],
            'datasets' => collect($chartData['series'])->map(function ($s, $i) {
                $colors = ['#3b82f6', '#10b981', '#f59e0b'];
                return [
                    'label' => $s['name'],
                    'data' => $s['data'],
                    'backgroundColor' => $colors[$i % count($colors)],
                ];
            })->toArray(),
        ]);
    }
}
