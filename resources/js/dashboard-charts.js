import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    BarController,
    BarElement,
    DoughnutController,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler
} from 'chart.js';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    BarController,
    BarElement,
    DoughnutController,
    ArcElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
    Filler
);

function isDark() {
    return document.documentElement.classList.contains('dark');
}

function getThemeColors() {
    const dark = isDark();
    return {
        text: dark ? '#94A3B8' : '#64748B',
        grid: dark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.8)',
        tooltipBg: dark ? '#0F172A' : '#1E293B',
        tooltipBorder: dark ? '#334155' : '#475569',
    };
}

let trendChartInstance = null;
let purposeChartInstance = null;
let outcomeChartInstance = null;

export function initDashboardCharts(initialData) {
    const theme = getThemeColors();

    // 1. Trend Chart
    const trendCanvas = document.getElementById('dashboardTrendChart');
    if (trendCanvas && initialData?.trends) {
        if (trendChartInstance) {
            trendChartInstance.destroy();
        }

        trendChartInstance = new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: initialData.trends.labels,
                datasets: initialData.trends.datasets.map(ds => ({
                    ...ds,
                    borderWidth: 2.5,
                    pointRadius: 3.5,
                    pointHoverRadius: 6,
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 500,
                    easing: 'easeOutQuart'
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: theme.text,
                            boxWidth: 12,
                            padding: 16,
                            font: { family: 'Instrument Sans, sans-serif', size: 12, weight: 600 }
                        }
                    },
                    tooltip: {
                        backgroundColor: theme.tooltipBg,
                        borderColor: theme.tooltipBorder,
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { family: 'Instrument Sans, sans-serif', size: 13, weight: 700 },
                        bodyFont: { family: 'Instrument Sans, sans-serif', size: 12 },
                    }
                },
                scales: {
                    x: {
                        grid: { color: theme.grid },
                        ticks: { color: theme.text, font: { family: 'Instrument Sans, sans-serif', size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: theme.grid },
                        ticks: {
                            color: theme.text,
                            stepSize: 1,
                            precision: 0,
                            font: { family: 'Instrument Sans, sans-serif', size: 11 }
                        }
                    }
                }
            }
        });
    }

    // 2. Purpose Distribution (Doughnut)
    const purposeCanvas = document.getElementById('dashboardPurposeChart');
    if (purposeCanvas && initialData?.purposeDist) {
        if (purposeChartInstance) {
            purposeChartInstance.destroy();
        }

        purposeChartInstance = new Chart(purposeCanvas, {
            type: 'doughnut',
            data: {
                labels: initialData.purposeDist.labels,
                datasets: [{
                    data: initialData.purposeDist.data,
                    backgroundColor: initialData.purposeDist.colors,
                    borderWidth: 2,
                    borderColor: isDark() ? '#0D1322' : '#FFFFFF',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 500,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: theme.text,
                            boxWidth: 12,
                            padding: 14,
                            font: { family: 'Instrument Sans, sans-serif', size: 11, weight: 600 }
                        }
                    },
                    tooltip: {
                        backgroundColor: theme.tooltipBg,
                        borderColor: theme.tooltipBorder,
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                cutout: '68%',
            }
        });
    }

    // 3. Offer Outcomes (Bar Chart)
    const outcomeCanvas = document.getElementById('dashboardOutcomeChart');
    if (outcomeCanvas && initialData?.offerOutcomes) {
        if (outcomeChartInstance) {
            outcomeChartInstance.destroy();
        }

        outcomeChartInstance = new Chart(outcomeCanvas, {
            type: 'bar',
            data: {
                labels: initialData.offerOutcomes.labels,
                datasets: [{
                    label: 'Offers',
                    data: initialData.offerOutcomes.data,
                    backgroundColor: initialData.offerOutcomes.colors,
                    borderRadius: 6,
                    borderSkipped: false,
                    maxBarThickness: 32,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 500,
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: theme.tooltipBg,
                        borderColor: theme.tooltipBorder,
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: theme.text, font: { family: 'Instrument Sans, sans-serif', size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: theme.grid },
                        ticks: {
                            color: theme.text,
                            stepSize: 1,
                            precision: 0,
                            font: { family: 'Instrument Sans, sans-serif', size: 11 }
                        }
                    }
                }
            }
        });
    }
}

export function updateDashboardCharts(newData) {
    const isReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const animMode = isReduced ? 'none' : { duration: 350, easing: 'easeOutQuad' };

    if (trendChartInstance && newData?.trends) {
        trendChartInstance.data.labels = newData.trends.labels;
        newData.trends.datasets.forEach((ds, idx) => {
            if (trendChartInstance.data.datasets[idx]) {
                trendChartInstance.data.datasets[idx].data = ds.data;
            }
        });
        trendChartInstance.update(animMode);
    }

    if (purposeChartInstance && newData?.purposeDist) {
        purposeChartInstance.data.datasets[0].data = newData.purposeDist.data;
        purposeChartInstance.update(animMode);
    }

    if (outcomeChartInstance && newData?.offerOutcomes) {
        outcomeChartInstance.data.datasets[0].data = newData.offerOutcomes.data;
        outcomeChartInstance.update(animMode);
    }
}

// Global theme sync listener
window.addEventListener('theme-changed', () => {
    const theme = getThemeColors();
    [trendChartInstance, outcomeChartInstance].forEach(chart => {
        if (chart) {
            if (chart.options.scales?.x) {
                chart.options.scales.x.ticks.color = theme.text;
                chart.options.scales.x.grid.color = theme.grid;
            }
            if (chart.options.scales?.y) {
                chart.options.scales.y.ticks.color = theme.text;
                chart.options.scales.y.grid.color = theme.grid;
            }
            chart.update();
        }
    });

    if (purposeChartInstance) {
        purposeChartInstance.data.datasets[0].borderColor = isDark() ? '#0D1322' : '#FFFFFF';
        if (purposeChartInstance.options.plugins?.legend) {
            purposeChartInstance.options.plugins.legend.labels.color = theme.text;
        }
        purposeChartInstance.update();
    }
});

// Expose on window for Alpine.js
window.initDashboardCharts = initDashboardCharts;
window.updateDashboardCharts = updateDashboardCharts;
