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
                <div id="duplicateChartMessage" style="text-align:center; font-weight:bold; margin-top:10px; display:none;">
                    {{ __('Data not found') }}
                </div>
            </div>
        </div>

        @php
            $chartData = $this->getChartData();
        @endphp

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            let ticketDuplicateChart = null;

            // 10 warna siap pakai
            const colors = [
                "#3b82f6", // biru
                "#ef4444", // merah
                "#10b981", // hijau
                "#f59e0b", // oranye
                "#8b5cf6", // ungu
                "#ec4899", // pink
                "#14b8a6", // teal
                "#f97316", // amber
                "#64748b", // abu
                "#84cc16", // lime
            ];

            function renderDuplicateChart(labels, data, urls) {
                const ctx = document.getElementById('ticketDuplicateChart').getContext('2d');
                const message = document.getElementById('duplicateChartMessage');

                if (ticketDuplicateChart) {
                    ticketDuplicateChart.destroy();
                }

                // Kalau kosong
                if (!data || data.length === 0 || data.every(v => v === 0)) {
                    message.style.display = 'block';
                    return;
                } else {
                    message.style.display = 'none';
                }

                const backgroundColors = data.map((_, i) => colors[i % colors.length]);

                ticketDuplicateChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Jumlah Tiket',
                            data: data,
                            backgroundColor: backgroundColors,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: true } },
                        onClick: (evt, activeEls) => {
                            if (activeEls.length > 0) {
                                const index = activeEls[0].index;
                                window.location.href = urls[index]; // 👈 langsung ke detail
                            }
                        }
                    }
                });
            }

            window.addEventListener("updateDuplicateChart", event => {
                console.log("Chart updated:", event.detail);
                renderDuplicateChart(event.detail.labels, event.detail.data, event.detail.urls);
            });

            window.addEventListener("renderDuplicateChart", event => {
                renderDuplicateChart(event.detail.labels, event.detail.data, event.detail.urls);
            }, { once: true });
        </script>

    </x-filament::card>
</x-filament::widget>
