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
            let ticketOwnerChart = null;

            function renderOwnerChart(labels, datasets) {
                const ctx = document.getElementById('ticketOwnerChart').getContext('2d');

                if (ticketOwnerChart) {
                    ticketOwnerChart.destroy();
                }

                ticketOwnerChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: true } }
                    }
                });
            }

            window.addEventListener("updateOwnerChart", event => {
                renderOwnerChart(event.detail.labels, event.detail.datasets);
            });
            window.addEventListener("renderOwnerChart", event => {
                renderOwnerChart(event.detail.labels, event.detail.datasets);
            }, { once: true });
        </script>

    </x-filament::card>
</x-filament::widget>
