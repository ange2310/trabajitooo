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
    const canvas = document.getElementById('hourlyChats');
    if (!canvas) {
        console.warn('Canvas not found: hourlyChats');
        return;
    }
    
    // Destruir el gráfico existente si existe
    if (chartInstances['hourlyChats']) {
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
    console.log('Updating hourly chart with new data:', { labels, values });
    
    // Verificar si el canvas existe
    const canvas = document.getElementById('hourlyChats');
    if (!canvas) {
        console.warn('Canvas not found: hourlyChats');
        return;
    }
    
    // Si el gráfico ya existe, destruirlo completamente
    if (chartInstances['hourlyChats']) {
        console.log('Destroying existing hourly chart');
        chartInstances['hourlyChats'].destroy();
        chartInstances['hourlyChats'] = null;
    }
    
    // Obtener el contexto para dibujar el gráfico
    const ctx = canvas.getContext('2d');
    
    // Si no hay datos, mostrar un gráfico vacío con mensaje
    if (!labels || !values || !labels.length || !values.length) {
        // Crear un gráfico vacío con mensaje
        chartInstances['hourlyChats'] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                datasets: [{
                    label: 'Conversaciones',
                    data: [0, 0, 0, 0, 0, 0],
                    borderColor: 'rgba(59, 130, 246, 0.5)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderDash: [5, 5],
                    pointRadius: 0,
                    fill: true,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 5,
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
                        enabled: false
                    }
                }
            },
            plugins: [{
                id: 'noDataText',
                afterDraw: function(chart) {
                    if (chart.data.datasets[0].data.every(item => item === 0)) {
                        var ctx = chart.ctx;
                        var width = chart.width;
                        var height = chart.height;
                        
                        chart.clear();
                        
                        ctx.fillStyle = 'rgba(0, 0, 0, 0.1)';
                        ctx.fillRect(0, 0, width, height);
                        
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.font = '16px Arial';
                        ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                        ctx.fillText('No hay datos disponibles para esta fecha', width / 2, height / 2);
                    }
                }
            }]
        });
        
        return;
    }
    
    // Crear gradiente para el área bajo la línea
    const gradientColors = ctx.createLinearGradient(0, 0, 0, 200);
    gradientColors.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
    gradientColors.addColorStop(1, 'rgba(59, 130, 246, 0)');
    
    // Configurar y crear el gráfico con los datos proporcionados
    chartInstances['hourlyChats'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Conversaciones',
                data: values,
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
    
    console.log('Hourly chart updated successfully');
}

// Función para actualizar las estadísticas de conversaciones por hora
function updateHourlyConversationsChart(data) {
    console.log('Updating hourly conversations chart with data:', data);
    
    // Verificar si tenemos datos en el formato esperado
    if (data && data.hourly_data && Array.isArray(data.hourly_data.labels) && Array.isArray(data.hourly_data.values)) {
        updateHourlyChart(data.hourly_data.labels, data.hourly_data.values);
    } else if (data && Array.isArray(data.labels) && Array.isArray(data.values)) {
        // Formato alternativo
        updateHourlyChart(data.labels, data.values);
    } else {
        console.warn('No valid hourly data format found, displaying empty chart');
        updateHourlyChart([], []);
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
            updateGaugeValue('gaugeConversaciones', data.attendance_rate || 0);

            // Actualizar métricas de tiempo usando los nuevos campos
            updateTimeMetric('tiempo-espera', data.average_wait_minutes || 0);
            updateTimeMetric('tiempo-respuesta', data.average_first_response_minutes || 0);
            updateTimeMetric('tiempo-duracion', data.average_duration_minutes || 0);
            
            // Actualizar los contadores de conversaciones recibidas y atendidas
            updateConversationStats(data.total_conversations_received || 0, data.total_conversations_attended || 0);

            // Actualizar tabla de métricas de rendimiento
            updatePerformanceTable(
                data.goal_achieved_count || 0,
                50, // Suponiendo que el total de objetivos es 50
                data.total_abandoned || 0,
                data.total_conversations_received || 0,
                data.abandonment_rate || 0
            );
            
            // Inicializar los gráficos (ahora que los valores están en el DOM)
            initCharts();
        })
        .catch(error => {
            // Inicializar con valores predeterminados en caso de error
            initCharts();
        });
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
    const percentage = valueElement ? parseFloat(valueElement.textContent.replace('%', '').trim()) : 0;

    if (valueElement) {
        valueElement.textContent = percentage + '%';
    }

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
// Función para actualizar las estadísticas de conversaciones
function updateConversationStats(received, attended) {
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

    // Si el método 1 falla por algún motivo, 
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

// Función para modificar las métricas de tiempo
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

// Función para actualizar la tabla de rendimiento
function updatePerformanceTable(goalsAchieved, totalGoals, abandonedConversations, totalConversations, abandonmentRate) {
    // Actualizar el primer elemento de la tabla (Objetivos Alcanzados)
    const objectivesRow = document.querySelector('.performance-table tbody tr:first-child');
    if (objectivesRow) {
        const quantityCell = objectivesRow.querySelector('td:nth-child(2)');
        const progressBar = objectivesRow.querySelector('.progress-bar');
        const percentageSpan = objectivesRow.querySelector('.progress-container span');
        
        if (quantityCell) {
            quantityCell.textContent = `${goalsAchieved}/${totalGoals}`;
        }
        
        const percentage = totalGoals > 0 ? (goalsAchieved / totalGoals) * 100 : 0;
        
        if (progressBar) {
            progressBar.style.width = `${percentage}%`;
        }
        
        if (percentageSpan) {
            percentageSpan.textContent = `${percentage.toFixed(2)}%`;
        }
    }
    
    // Actualizar el segundo elemento de la tabla (Conversaciones Abandonadas)
    const abandonedRow = document.querySelector('.performance-table tbody tr:last-child');
    if (abandonedRow) {
        const quantityCell = abandonedRow.querySelector('td:nth-child(2)');
        const progressBar = abandonedRow.querySelector('.progress-bar');
        const percentageSpan = abandonedRow.querySelector('.progress-container span');
        
        if (quantityCell) {
            quantityCell.textContent = `${abandonedConversations}/${totalConversations}`;
        }
        
        if (progressBar) {
            progressBar.style.width = `${abandonmentRate}%`;
        }
        
        if (percentageSpan) {
            percentageSpan.textContent = `${abandonmentRate.toFixed(2)}%`;
        }
    }
}
