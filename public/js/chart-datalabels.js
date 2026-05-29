document.addEventListener('DOMContentLoaded', () => {
    // Wait for window.Chart to be initialized by Filament
    const checkChart = setInterval(() => {
        if (window.Chart) {
            clearInterval(checkChart);
            
            // Load the Chart.js Datalabels plugin from CDN
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js';
            script.onload = () => {
                // Register the plugin globally to all charts
                window.Chart.register(window.ChartDataLabels);
                
                // Configure global defaults for datalabels
                window.Chart.defaults.set('plugins.datalabels', {
                    color: '#1e293b',
                    font: {
                        weight: 'bold',
                        size: 11,
                        family: "'Inter', sans-serif"
                    },
                    formatter: function(value) {
                        if (value === 0) return '';
                        return value;
                    }
                });

                // Overrides for BAR charts (stacked or simple)
                window.Chart.defaults.set('datasets.bar.plugins.datalabels', {
                    color: function(ctx) {
                        let bg = ctx.dataset.backgroundColor;
                        if (Array.isArray(bg)) {
                            bg = bg[ctx.dataIndex];
                        }
                        // If background is yellow (#eab308) or platinum/chrome-like, use dark text
                        if (bg === '#eab308') {
                            return '#1e293b';
                        }
                        return '#ffffff'; // White text for other bar colors
                    },
                    font: {
                        size: 11,
                        weight: 'bold'
                    },
                    formatter: function(value) {
                        if (value === 0) return '';
                        return value;
                    }
                });

                // Overrides for LINE charts (e.g. IngresosMensualesChart)
                window.Chart.defaults.set('datasets.line.plugins.datalabels', {
                    color: '#334155', // slate-700
                    align: 'top',
                    anchor: 'end',
                    offset: 4,
                    font: {
                        size: 10,
                        weight: 'bold'
                    },
                    formatter: function(value) {
                        if (value === 0) return '';
                        return 'Bs. ' + Number(value).toLocaleString();
                    }
                });

                // Overrides for PIE and DOUGHNUT charts (e.g. MetodoPagoChart)
                window.Chart.defaults.set('datasets.pie.plugins.datalabels', {
                    color: '#ffffff',
                    align: 'center',
                    anchor: 'center',
                    font: {
                        size: 11,
                        weight: 'bold'
                    },
                    formatter: function(value, ctx) {
                        let sum = 0;
                        let dataArr = ctx.chart.data.datasets[0].data;
                        dataArr.map(data => {
                            sum += Number(data);
                        });
                        let percentage = (value * 100 / sum).toFixed(1) + "%";
                        if (value === 0) return '';
                        return 'Bs. ' + Number(value).toLocaleString() + '\n(' + percentage + ')';
                    }
                });

                window.Chart.defaults.set('datasets.doughnut.plugins.datalabels', {
                    color: '#ffffff',
                    align: 'center',
                    anchor: 'center',
                    font: {
                        size: 11,
                        weight: 'bold'
                    },
                    formatter: function(value, ctx) {
                        let sum = 0;
                        let dataArr = ctx.chart.data.datasets[0].data;
                        dataArr.map(data => {
                            sum += Number(data);
                        });
                        let percentage = (value * 100 / sum).toFixed(1) + "%";
                        if (value === 0) return '';
                        return 'Bs. ' + Number(value).toLocaleString() + '\n(' + percentage + ')';
                    }
                });
            };
            document.head.appendChild(script);
        }
    }, 100);
});
