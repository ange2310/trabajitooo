// assets/js/charts.js
// javascript para los gráficos de la página principal

// Variable global para guardar referencias a los gráficos
var chartInstances = {};

// Inicializar gráficos cuando la página esté lista
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing charts');
    // Inicializar gráficos
    fetchDashboardMetrics(); // Cargamos datos reales y luego inicializamos charts
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
    
    // Usar arrays vacíos en lugar de datos de muestra
    const data = {
        labels: [],
        values: []
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
        // Si no hay datos, mostrar un gráfico vacío
        if (!labels.length || !values.length) {
            chart.data.labels = [];
            chart.data.datasets[0].data = [];
            chart.update();
            console.log('Chart updated with empty data');
            return;
        }
        
        chart.data.labels = labels;
        chart.data.datasets[0].data = values;
        chart.update();
        console.log('Chart updated with data:', {labels, values});
    }
}

function fetchDashboardMetrics() {
    // Obtener la fecha del parámetro URL si existe
    const urlParams = new URLSearchParams(window.location.search);
    const fecha = urlParams.get('fecha') || '';
    
    // Construir la URL con el parámetro de fecha
    const url = 'includes/dashboard_metrics.php' + (fecha ? `?fecha=${fecha}` : '');
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log('Datos recibidos del servidor:', data);
            
            // Actualizar los valores del DOM para los gauges
            updateGaugeValue('gaugeAtencion', data.attendance_rate || 0);
            updateGaugeValue('gaugeOportunidad', data.opportunity_rate || 0);
            updateGaugeValue('gaugeAbandono', data.abandonment_rate || 0);
            updateGaugeValue('gaugeConversaciones', data.total_conversations_received || 0);

            // Actualizar métricas de tiempo usando los nuevos campos
            updateTimeMetric('tiempo-espera', data.average_wait_minutes || 0);
            updateTimeMetric('tiempo-respuesta', data.average_first_response_minutes || 0);
            updateTimeMetric('tiempo-duracion', data.average_duration_minutes || 0);
            
            // Actualizar los contadores de conversaciones recibidas y atendidas
            updateConversationStats(data.total_conversations_received || 0, data.total_conversations_attended || 0);

            // Actualizar tabla de métricas de rendimiento
            updatePerformanceTable(
                data.goal_achieved_count || 0,
                50, // Total de objetivos (parece ser un valor fijo en tu interfaz)
                data.total_abandoned || 0,
                data.total_conversations_received || 0,
                data.abandonment_rate || 0
            );

            // Actualizar gráfico de conversaciones por hora
            // Si tienes datos de conversaciones por hora en la respuesta
            updateHourlyConversationsChart(data);
            
            // Inicializar los gráficos (ahora que los valores están en el DOM)
            initCharts();
        })
        .catch(error => {
            console.error('Error al obtener las métricas del dashboard:', error);
            // Inicializar con valores predeterminados en caso de error
            initCharts();
        });
}

// Función para actualizar las estadísticas de conversaciones
function updateConversationStats(received, attended) {
    // Método 1: Buscar por los elementos stat-box y actualizar según el título
    const statBoxes = document.querySelectorAll('.stat-box');
    
    statBoxes.forEach(box => {
        const title = box.querySelector('h3');
        const value = box.querySelector('.stat-value');
        
        if (title && value) {
            const titleText = title.textContent.trim();
            
            if (titleText === 'Recibidas') {
                value.textContent = received;
            } else if (titleText === 'Atendidas') {
                value.textContent = attended;
            }
        }
    });
    
    // Método 2 (alternativo): Si el método 1 falla por algún motivo, 
    // buscar directamente por el encabezado y actualizar el valor asociado
    if (!statBoxes.length) {
        document.querySelectorAll('h3').forEach(h3 => {
            const text = h3.textContent.trim();
            if (text === 'Recibidas' || text === 'Atendidas') {
                const parent = h3.parentElement;
                const valueElement = parent.querySelector('.stat-value');
                if (valueElement) {
                    valueElement.textContent = text === 'Recibidas' ? received : attended;
                }
            }
        });
    }
}

function updateGaugeValue(canvasId, value) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    
    // Asegurar que el valor sea numérico y no NaN
    const numValue = parseFloat(value);
    const displayValue = isNaN(numValue) ? 0 : numValue.toFixed(1);
    
    const valueElement = canvas.parentElement.querySelector('.gauge-value');
    if (valueElement) {
        valueElement.textContent = displayValue;
    }
}

// Función actualizada para modificar las métricas de tiempo
function updateTimeMetric(metricId, value) {
    const element = document.getElementById(metricId);
    if (element) {
        // Buscar el elemento span con clase time-value dentro del elemento
        const valueElement = element.querySelector('.time-value');
        if (valueElement) {
            // Asegurar que el valor sea numérico y no NaN
            const numValue = parseFloat(value);
            const displayValue = isNaN(numValue) ? 0 : numValue.toFixed(1);
            
            valueElement.textContent = `${displayValue} minutos`;
            console.log(`Actualizado ${metricId} a ${displayValue} minutos`);
        } else {
            console.warn(`No se encontró el elemento .time-value dentro de #${metricId}`);
        }
    } else {
        console.warn(`No se encontró el elemento con ID: ${metricId}`);
    }
}

