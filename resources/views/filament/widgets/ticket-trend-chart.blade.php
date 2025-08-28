<x-filament::widget>
    <x-filament::card>
        <div class="text-xl font-bold">{{ $this->getHeading() }}</div>
        {{-- Form filter --}}
        {{ $this->form }}

        {{-- Chart --}}
        <div class="mt-6" wire:ignore.self>
            <div wire:init="initChart">
                <canvas id="ticketTrendChart" wire:ignore></canvas>
            </div>
        </div>

        @php
            $chartData = $this->getChartData();
        @endphp

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            let ticketTrendChart = null;

            function renderTrendChart(labels, data) {
                const ctx = document.getElementById('ticketTrendChart').getContext('2d');

                if (ticketTrendChart) {
                    ticketTrendChart.destroy();
                }

                ticketTrendChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Tiket',
                            data: data,
                            backgroundColor: '#3b82f6',
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: true } }
                    }
                });
            }

            window.addEventListener("updateTrendChart", event => {
                console.log("Chart updated:", event.detail);
                renderTrendChart(event.detail.labels, event.detail.data);
            });

            window.addEventListener("renderTrendChart", event => {
                renderTrendChart(event.detail.labels, event.detail.data);
            }, { once: true });
        </script>

    </x-filament::card>
</x-filament::widget>
