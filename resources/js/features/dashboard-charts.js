import Chart from 'chart.js/auto';

export function initDashboardCharts() {
    const trendEl = document.getElementById('chart-trend');
    const statusEl = document.getElementById('chart-status');
    const dusunEl = document.getElementById('chart-dusun');

    if (!trendEl && !statusEl && !dusunEl) {
        return;
    }

    Chart.defaults.font.family = "'Manrope', system-ui, -apple-system, sans-serif";
    Chart.defaults.color = '#52635b';

    if (trendEl) {
        try {
            const data = JSON.parse(trendEl.dataset.chart || '{}');
            new Chart(trendEl, {
                type: 'line',
                data: {
                    labels: data.labels || [],
                    datasets: [
                        {
                            label: 'Laporan Warga',
                            data: data.reports || [],
                            borderColor: '#176248',
                            backgroundColor: 'rgba(23, 98, 72, 0.08)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.35,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#176248',
                        },
                        {
                            label: 'Usulan Warga',
                            data: data.usulan || [],
                            borderColor: '#0284c7',
                            backgroundColor: 'rgba(2, 132, 199, 0.08)',
                            borderWidth: 2,
                            borderDash: [4, 4],
                            fill: false,
                            tension: 0.35,
                            pointRadius: 3,
                            pointHoverRadius: 5,
                            pointBackgroundColor: '#0284c7',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                usePointStyle: true,
                                font: { size: 12, weight: '600' },
                            },
                        },
                        tooltip: {
                            backgroundColor: '#17362d',
                            titleFont: { weight: 'bold', size: 12 },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, stepSize: 1 },
                            grid: { color: 'rgba(0, 0, 0, 0.06)' },
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } },
                        },
                    },
                },
            });
        } catch (e) {
            console.error('Error rendering trend chart:', e);
        }
    }

    if (statusEl) {
        try {
            const data = JSON.parse(statusEl.dataset.chart || '{}');
            new Chart(statusEl, {
                type: 'doughnut',
                data: {
                    labels: data.labels || ['Menunggu', 'Belum Ditangani', 'Diproses', 'Selesai'],
                    datasets: [{
                        data: data.values || [0, 0, 0, 0],
                        backgroundColor: ['#f59e0b', '#94a3b8', '#0284c7', '#16a34a'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '66%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                padding: 12,
                                usePointStyle: true,
                                font: { size: 11, weight: '500' },
                            },
                        },
                        tooltip: {
                            backgroundColor: '#17362d',
                            titleFont: { weight: 'bold', size: 12 },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                        },
                    },
                },
            });
        } catch (e) {
            console.error('Error rendering status chart:', e);
        }
    }

    if (dusunEl) {
        try {
            const data = JSON.parse(dusunEl.dataset.chart || '{}');
            new Chart(dusunEl, {
                type: 'bar',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'Jumlah Laporan',
                        data: data.values || [],
                        backgroundColor: '#176248',
                        borderRadius: 6,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#17362d',
                            titleFont: { weight: 'bold', size: 12 },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: { precision: 0, stepSize: 1 },
                            grid: { color: 'rgba(0, 0, 0, 0.06)' },
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { size: 11, weight: '500' } },
                        },
                    },
                },
            });
        } catch (e) {
            console.error('Error rendering dusun chart:', e);
        }
    }
}
