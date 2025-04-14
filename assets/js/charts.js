// assets/js/charts.js
// javascript para los gráficos de la página principal

// Variable global para guardar referencias a los gráficos
var chartInstances = {};

// Inicializar gráficos cuando la página esté lista
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing charts');
    // Inicializar gráficos
    initCharts();
});

// Función principal para inicializar todos los gráficos
function initCharts() {
    console.log('Initializing all charts');
    
    // Inicializar gráficos de gauge (medidores circulares)
    initGaugeChart('gaugeAtencion', '#9933ff', '#3366ff');
    initGaugeChart('gaugeOportunidad', '#ffcc00', '#ff9900');
    initGaugeChart('gaugeAbandono', '#ff3366', '#ff0000');
    initGaugeChart('gaugeConversaciones', '#4338ca', '#6d28d9');
    
    // Inicializar gráfico de barras para métricas de tiempo
    initTimeMetricsChart();
    
    // Inicializar gráfico de línea para conversaciones por hora
    initHourlyChart();
}

// Función para inicializar gráficos de tipo gauge
function initGaugeChart(canvasId, color1, color2) {
    console.log('Initializing gauge chart: ' + canvasId);
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        console.warn('Canvas not found: ' + canvasId);
        return;
    }
    
    // Destruir el gráfico existente si existe
    if (chartInstances[canvasId]) {
        console.log('Destroying existing chart: ' + canvasId);
        chartInstances[canvasId].destroy();
        chartInstances[canvasId] = null;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Obtener el valor del gauge del elemento hermano con clase gauge-value
    const valueElement = canvas.parentElement.querySelector('.gauge-value');
    const percentage = valueElement ? parseFloat(valueElement.textContent) : 0;
    
    // Crear gradiente
    const gradientColors = ctx.createLinearGradient(0, 0, 0, 150);
    gradientColors.addColorStop(0, color1);
    gradientColors.addColorStop(1, color2);
    
    // Configurar y crear el gráfico
    chartInstances[canvasId] = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [percentage, 100 - percentage],
                backgroundColor: [
                    gradientColors,
                    '#1a1e2c' // Color de fondo oscuro
                ],
                borderWidth: 0,
                cutout: '75%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1500,
                easing: 'easeOutCubic'
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            }
        }
    });
}

// Función para inicializar el gráfico de barras de métricas de tiempo
function initTimeMetricsChart() {
    console.log('Initializing time metrics chart');
    const canvas = document.getElementById('timeMetrics');
    if (!canvas) {
        console.warn('Canvas not found: timeMetrics');
        return;
    }
    
    // Destruir el gráfico existente si existe
    if (chartInstances['timeMetrics']) {
        console.log('Destroying existing time metrics chart');
        chartInstances['timeMetrics'].destroy();
        chartInstances['timeMetrics'] = null;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Obtener los valores de los elementos hermanos
    const timeElements = canvas.parentElement.parentElement.querySelectorAll('.time-value');
    const values = Array.from(timeElements).map(el => {
        const text = el.textContent;
        return parseFloat(text.split(' ')[0]);
    });
    
    const labels = ['Tiempo de Espera', 'Tiempo de Respuesta', 'Duración Conversación'];
    
    // Crear gradientes
    const gradientColors1 = ctx.createLinearGradient(0, 0, 0, 150);
    gradientColors1.addColorStop(0, '#f97316');
    gradientColors1.addColorStop(1, '#fb923c');
    
    const gradientColors2 = ctx.createLinearGradient(0, 0, 0, 150);
    gradientColors2.addColorStop(0, '#ec4899');
    gradientColors2.addColorStop(1, '#f9a8d4');
    
    const gradientColors3 = ctx.createLinearGradient(0, 0, 0, 150);
    gradientColors3.addColorStop(0, '#8b5cf6');
    gradientColors3.addColorStop(1, '#a78bfa');
    
    // Configurar y crear el gráfico
    chartInstances['timeMetrics'] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    gradientColors1,
                    gradientColors2,
                    gradientColors3
                ],
                borderWidth: 0,
                borderRadius: 5,
                barPercentage: 0.5,
                categoryPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.7)',
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.7)',
                        font: {
                            size: 10
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    padding: 10,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    bodyFont: {
                        size: 12
                    },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' minutos';
                        }
                    }
                }
            }
        }
    });
}

// Función para inicializar el gráfico de conversaciones por hora
function initHourlyChart() {
    console.log('Initializing hourly chart');
    const canvas = document.getElementById('hourlyChats');
    if (!canvas) {
        console.warn('Canvas not found: hourlyChats');
        return;
    }
    
    // Destruir el gráfico existente si existe
    if (chartInstances['hourlyChats']) {
        console.log('Destroying existing hourly chart');
        chartInstances['hourlyChats'].destroy();
        chartInstances['hourlyChats'] = null;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Datos de muestra (serán reemplazados por datos reales)
    const data = {
        labels: ['4AM', '5AM', '6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM'],
        values: [2, 3, 5, 8, 10, 9, 7, 8, 10, 9, 7, 8, 9, 6, 5, 4, 3, 2, 1]
    };
    
    // Crear gradiente
    const gradientColors = ctx.createLinearGradient(0, 0, 0, 200);
    gradientColors.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
    gradientColors.addColorStop(1, 'rgba(59, 130, 246, 0)');
    
    // Configurar y crear el gráfico
    chartInstances['hourlyChats'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Conversaciones',
                data: data.values,
                borderColor: '#3b82f6',
                backgroundColor: gradientColors,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.7)',
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.7)',
                        font: {
                            size: 10
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.7)',
                    padding: 10,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    bodyFont: {
                        size: 12
                    },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return context.raw + ' conversaciones';
                        }
                    }
                }
            }
        }
    });
}

// Función para actualizar el gráfico de conversaciones por hora con datos dinámicos
function updateHourlyChart(labels, values) {
    console.log('Updating hourly chart with new data');
    if (!chartInstances['hourlyChats']) {
        console.warn('Hourly chart not initialized yet. Initializing now.');
        initHourlyChart();
    }
    
    const chart = chartInstances['hourlyChats'];
    if (chart) {
        chart.data.labels = labels;
        chart.data.datasets[0].data = values;
        chart.update();
    }
}