import Chart from 'chart.js/auto';

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
    renderTrendChart(event.detail[0].labels, event.detail[0].data);
});

window.addEventListener("renderTrendChart", event => {
    renderTrendChart(event.detail[0].labels, event.detail[0].data);
});

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

document.addEventListener('livewire:load', () => {
    console.log('Livewire loaded, setting up listeners for TicketOwnerChart');
    Livewire.on('renderOwnerChart', data => renderOwnerChart(data.labels, data.datasets));
});

window.addEventListener("updateOwnerChart", event => {
    console.log(event);
    renderOwnerChart(event.detail[0].labels, event.detail[0].datasets);
});
window.addEventListener("renderOwnerChart", event => {
    console.log(event);
    renderOwnerChart(event.detail[0].labels, event.detail[0].datasets);
});

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
    renderResponsibilityChart(event.detail[0].labels, event.detail[0].datasets);
});
window.addEventListener("renderResponsibilityChart", event => {
    renderResponsibilityChart(event.detail[0].labels, event.detail[0].datasets);
});

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
    renderServiceChart(event.detail[0].labels, event.detail[0].data);
});
window.addEventListener("renderServiceChart", event => {
    renderServiceChart(event.detail[0].labels, event.detail[0].data);
});

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
    renderApplicationChart(event.detail[0].labels, event.detail[0].data);
});
window.addEventListener("renderApplicationChart", event => {
    renderApplicationChart(event.detail[0].labels, event.detail[0].data);
});

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
    console.log(data.length)
    const ctx = document.getElementById('ticketDuplicateChart').getContext('2d');
    const message = document.getElementById('duplicateChartMessage');

    if (ticketDuplicateChart) {
        ticketDuplicateChart.destroy();
    }

    // Kalau kosong
    if (!data || data.length === 0 || data.every(v => v === 0)) {
        console.log('no data')
        message.style.display = 'block';
        // return;
    } else {
        console.log('has data')
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
    renderDuplicateChart(event.detail[0].labels, event.detail[0].data, event.detail[0].urls);
});

window.addEventListener("renderDuplicateChart", event => {
    renderDuplicateChart(event.detail[0].labels, event.detail[0].data, event.detail[0].urls);
});
