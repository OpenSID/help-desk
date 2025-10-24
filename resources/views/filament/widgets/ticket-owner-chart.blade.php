<x-filament::widget>
    <x-filament::card>
        <div class="text-xl font-bold">{{ $this->getHeading() }}</div>
        {{-- Form filter --}}
        {{ $this->form }}

        {{-- Chart --}}
        <div class="mt-6" wire:ignore.self>
            <div wire:init="initChart">
                <canvas id="ticketOwnerChart" wire:ignore></canvas>
            </div>
        </div>

        @php
            $chartData = $this->getChartData();
        @endphp

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>

        </script>

    </x-filament::card>
</x-filament::widget>
