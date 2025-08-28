<x-filament::widget>
    <x-filament::card>
        <div class="text-xl font-bold">{{ $this->getHeading() }}</div>
        {{-- Form filter --}}
        {{ $this->form }}

        {{-- Chart --}}
        <div class="mt-6" wire:ignore.self>
            <div wire:init="initChart">
                <canvas id="ticketResponsibilityChart" wire:ignore></canvas>
            </div>
        </div>

        @php
            $chartData = $this->getChartData();
        @endphp

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            let ticketResponsibilityChart = null;

            function renderResponsibilityChart(labels, datasets) {
                const ctx = document.getElementById('ticketResponsibilityChart').getContext('2d');

                if (ticketResponsibilityChart) {
                    ticketResponsibilityChart.destroy();
                }

                ticketResponsibilityChart = new Chart(ctx, {
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

            window.addEventListener("updateResponsibilityChart", event => {
                renderResponsibilityChart(event.detail.labels, event.detail.datasets);
            });
            window.addEventListener("renderResponsibilityChart", event => {
                renderResponsibilityChart(event.detail.labels, event.detail.datasets);
            }, { once: true });
        </script>

    </x-filament::card>
</x-filament::widget>
