<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;

class TicketDuplicateChart extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.widgets.ticket-duplicate-chart';

    public function getHeading(): string
    {
        return __('Trend duplicates');
    }

    public ?array $formData = [];

    public function mount(): void
    {
        $this->form->fill([
            'month'   => now()->month,
            'year'    => now()->year,
        ]);
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
            ])
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

        $tickets = DB::table('ticket_relations')
            ->select('relation_id', DB::raw('COUNT(*) as total'))
            ->whereYear('created_at', $state['year'])
            ->whereMonth('created_at', $state['month'])
            ->groupBy('relation_id')
            ->orderByDesc('total')
            ->get();


        return [
            'labels' => $tickets->pluck('relation_id')->map(fn ($id) => 'ID Tiket : ' . $id)->values(),
            'data'   => $tickets->pluck('total'),
            'urls'   => $tickets->pluck('relation_id')->map(fn ($id) => route('filament.resources.tickets.view', $id)),
        ];
    }

    public function getColumnSpan(): int|string|array
    {
        return 'full'; // biar full 1 row
    }


    public function updated($name, $value): void
    {
        $chartData = $this->getChartData();

        $this->dispatchBrowserEvent('updateDuplicateChart', [
            'labels' => $chartData['labels']->toArray(),
            'data'   => $chartData['data']->toArray(),
            'urls'   => $chartData['urls']->toArray(),
        ]);
    }

    public function initChart()
    {
        $chartData = $this->getChartData();

        $this->dispatchBrowserEvent('renderDuplicateChart', [
            'labels' => $chartData['labels']->toArray(),
            'data'   => $chartData['data']->toArray(),
            'urls'   => $chartData['urls']->toArray(),
        ]);
    }
}
