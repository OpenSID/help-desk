<x-filament::widget>
    <x-filament::card>
        <div class="text-xl font-bold">{{ $this->getHeading() }}</div>
        {{-- Form filter --}}
        {{ $this->form }}

        {{-- Chart --}}
        <div class="mt-6">
            <div wire:init="initChart">
                <canvas id="ticketApplicationChart" wire:ignore></canvas>
            </div>
        </div>

        @php
            $chartData = $this->getChartData();
        @endphp

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            let ticketApplicationChart = null;

            function renderApplicationChart(labels, data) {
                const ctx = document.getElementById('ticketApplicationChart').getContext('2d');

                if (ticketApplicationChart) {
                    ticketApplicationChart.destroy();
                }

                ticketApplicationChart = new Chart(ctx, {
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

            window.addEventListener("updateApplicationChart", event => {
                renderApplicationChart(event.detail.labels, event.detail.data);
            });
            window.addEventListener("renderApplicationChart", event => {
                renderApplicationChart(event.detail.labels, event.detail.data);
            }, { once: true });
        </script>

    </x-filament::card>
</x-filament::widget>
