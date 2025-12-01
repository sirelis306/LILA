document.addEventListener('DOMContentLoaded', function () {
    const data = window.__LILA_REPORTES__ || {};

    const ventasSemanales = data.ventasSemanales || [];
    const topProductos = data.topProductos || [];

    // Formatear fechas (YYYY-MM-DD -> DD/MM)
    const labelsVentas = ventasSemanales.map(v => {
        const fecha = v.fecha || v.FECHA;
        if (!fecha) return '';
        const [year, month, day] = fecha.split('-');
        return `${day}/${month}`;
    });
    const valoresVentas = ventasSemanales.map(v => parseFloat(v.total_usd || v.TOTAL_USD || 0));

    const ctxVentas = document.getElementById('chartVentasSemanales');
    if (ctxVentas && ventasSemanales.length > 0) {
        new Chart(ctxVentas, {
            type: 'line',
            data: {
                labels: labelsVentas,
                datasets: [{
                    label: 'Total USD',
                    data: valoresVentas,
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.15)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 4,
                    pointBackgroundColor: '#1d4ed8'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.parsed.y.toFixed(2)} USD`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' USD';
                            }
                        }
                    }
                }
            }
        });
    }

    const labelsProductos = topProductos.map(p => p.nombre_producto || p.NOMBRE_PRODUCTO);
    const cantidadesProductos = topProductos.map(p => parseInt(p.total_cantidad || p.TOTAL_CANTIDAD || 0, 10));

    const ctxProductos = document.getElementById('chartTopProductos');
    if (ctxProductos && topProductos.length > 0) {
        new Chart(ctxProductos, {
            type: 'bar',
            data: {
                labels: labelsProductos,
                datasets: [{
                    label: 'Cantidad vendida',
                    data: cantidadesProductos,
                    backgroundColor: [
                        '#4f46e5',
                        '#0ea5e9',
                        '#22c55e',
                        '#facc15',
                        '#f97316'
                    ]
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` ${context.parsed.x} unidades`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});


