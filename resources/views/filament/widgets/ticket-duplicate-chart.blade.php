<x-filament::widget>
    <x-filament::card>
        <div class="text-xl font-bold">{{ $this->getHeading() }}</div>
        {{-- Form filter --}}
        {{ $this->form }}

        {{-- Chart --}}
        <div class="mt-6 flex justify-center items-center" wire:ignore.self>
            {{-- Use wire:init to initialize the chart --}}
            <div wire:init="initChart" style="width: 500px; height: 500px;">
                <canvas id="ticketDuplicateChart" wire:ignore></canvas>
                <div id="duplicateChartMessage" style="text-align:center; font-weight:bold; margin-top:10px; opacity:0;">
                    {{ __('Data not found') }}
                </div>
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
