<?php

// namespace App\Filament\Widgets;

// use Filament\Widgets\Widget;
// use Filament\Forms\Contracts\HasForms;
// use Filament\Forms\Concerns\InteractsWithForms;
// use Filament\Forms\Components\Select;
// use Illuminate\Support\Facades\DB;
// use Carbon\Carbon;
// use Filament\Forms\Components\Grid;

// class TicketDuplicateChart extends Widget implements HasForms
// {
//     use InteractsWithForms;

//     protected static string $view = 'filament.widgets.ticket-duplicate-chart';

//     public function getHeading(): string
//     {
//         return __('Trend duplicates');
//     }

//     // ✅ deklarasi semua properti Livewire
//     public int $month;
//     public int $year;

//     public function mount(): void
//     {
//         $this->month  = now()->month;
//         $this->year   = now()->year;

//         $this->form->fill([
//             'month'   => $this->month,
//             'year'    => $this->year,
//         ]);
//     }

//     protected function getFormSchema(): array
//     {
//         return [
//             Grid::make(4)->schema([
//                 Select::make('month')
//                     ->label(__('Month'))
//                     ->options($this->getMonths())
//                     ->reactive(),

//                 Select::make('year')
//                     ->label(__('Year'))
//                     ->options($this->getYears())
//                     ->reactive(),
//             ])
//         ];
//     }

//     protected function getMonths(): array
//     {
//         return collect(range(1, 12))
//             ->mapWithKeys(fn ($m) => [$m => Carbon::create()->month($m)->translatedFormat('F')])
//             ->toArray();
//     }

//     protected function getYears(): array
//     {
//         $now = now()->year;
//         return collect(range($now - 2, $now))
//             ->mapWithKeys(fn ($y) => [$y => $y])
//             ->toArray();
//     }

//     public function getChartData(): array
//     {

//         $state = $this->form->getState();

//         $tickets = DB::table('ticket_relations')
//             ->select('relation_id', DB::raw('COUNT(*) as total'))
//             ->whereYear('created_at', $state['year'])
//             ->whereMonth('created_at', $state['month'])
//             ->groupBy('relation_id')
//             ->orderByDesc('total')
//             ->get();


//         return [
//             'labels' => $tickets->pluck('relation_id')->map(fn ($id) => 'ID Tiket : ' . $id)->values(),
//             'data'   => $tickets->pluck('total'),
//             'urls'   => $tickets->pluck('relation_id')->map(fn ($id) => route('filament.admin.resources.tickets.view', ['record' => $id])),
//         ];
//     }

//     public function getColumnSpan(): int|string|array
//     {
//         return 'full'; // biar full 1 row
//     }


//     public function updated($name, $value): void
//     {
//         $chartData = $this->getChartData();

//         $this->dispatch('updateDuplicateChart', [
//             'labels' => $chartData['labels']->toArray(),
//             'data'   => $chartData['data']->toArray(),
//             'urls'   => $chartData['urls']->toArray(),
//         ]);
//     }

//     public function initChart()
//     {
//         $chartData = $this->getChartData();

//         $this->dispatch('renderDuplicateChart', [
//             'labels' => $chartData['labels']->toArray(),
//             'data'   => $chartData['data']->toArray(),
//             'urls'   => $chartData['urls']->toArray(),
//         ]);
//     }
// }


namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use App\Models\ProblemCategory;

class TicketDuplicateChart extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.ticket-duplicate-chart';

    public function getHeading(): string
    {
        return __('Trend duplicates');
    }

    // ✅ deklarasi semua properti Livewire
    public int $start_month;
    public int $start_year;
    public int $end_month;
    public int $end_year;
    public int $problem_category_id;

    public function mount(): void
    {
        $this->start_month              = now()->subMonths(2)->month;
        $this->start_year               = now()->subMonths(2)->year;
        $this->end_month                = now()->month;
        $this->end_year                 = now()->year;
        $this->problem_category_id      = ProblemCategory::first()->id; // Default to 'Semua'

        $this->form->fill([
            'start_month'               => $this->start_month,
            'start_year'                => $this->start_year,
            'end_month'                 => $this->end_month,
            'end_year'                  => $this->end_year,
            'problem_category_id'       => $this->problem_category_id // Default to 'Semua'
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

                Select::make('problem_category_id')
                    ->label(__('Problem Category'))
                    ->options(function () {
                        return ProblemCategory::all()->pluck('name', 'id')->toArray();
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
            ->where('problem_category_id', (int)$state['problem_category_id'])
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

        $this->dispatch('updateDuplicateChart', [
            'labels' => $chartData['labels']->toArray(),
            'data'   => $chartData['data']->toArray(),
        ]);
    }

    public function initChart()
    {
        $chartData = $this->getChartData();

        $this->dispatch('renderDuplicateChart', [
            'labels' => $chartData['labels']->toArray(),
            'data'   => $chartData['data']->toArray(),
        ]);
    }
}
