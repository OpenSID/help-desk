<x-filament::widget>
    <x-filament::card>
        <div class="text-xl font-bold">{{ $this->getHeading() }}</div>
        {{-- Form filter --}}
        {{ $this->form }}

        {{-- Chart --}}
        <div class="mt-6">
            <div wire:init="initChart">
                <canvas id="ticketServiceChart" wire:ignore></canvas>
            </div>
        </div>

        @php
            $chartData = $this->getChartData();
        @endphp

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            let ticketServiceChart = null;

            function renderServiceChart(labels, data) {
                const ctx = document.getElementById('ticketServiceChart').getContext('2d');

                if (ticketServiceChart) {
                    ticketServiceChart.destroy();
                }

                ticketServiceChart = new Chart(ctx, {
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

            window.addEventListener("updateServiceChart", event => {
                renderServiceChart(event.detail.labels, event.detail.data);
            });
            window.addEventListener("renderServiceChart", event => {
                renderServiceChart(event.detail.labels, event.detail.data);
            }, { once: true });
        </script>

    </x-filament::card>
</x-filament::widget>
