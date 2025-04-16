// Variable global para guardar referencias a los gráficos
var chartInstances = {};

// Variable global para rastrear qué gauges ya se han inicializado
var initializedGauges = {};

// Inicializar gráficos cuando la página esté lista
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráficos
    fetchDashboardMetrics(); // Cargamos datos reales y luego inicializamos charts
});

 // Función principal para inicializar todos los gráficos
 function initCharts() {
    // Verificar qué página estamos viendo para evitar inicializaciones duplicadas
    const currentPath = window.location.pathname;
    const isMetricaAtencion = currentPath.includes('metrica_atencion.php');
    const isMetricaOportunidad = currentPath.includes('metrica_oportunidad.php');
    const isMetricaAbandono = currentPath.includes('metrica_abandono.php');
    
    // Eliminar la inicialización duplicada que está al inicio
    // y solo inicializar si el elemento existe y estamos en la página adecuada
    
    const gaugeAtencion = document.getElementById('gaugeAtencion');
    if (gaugeAtencion && !isMetricaAtencion) {
        initGaugeChart('gaugeAtencion', '#9933ff', '#3366ff');
    }
    
    const gaugeOportunidad = document.getElementById('gaugeOportunidad');
    if (gaugeOportunidad && !isMetricaOportunidad) {
        initGaugeChart('gaugeOportunidad', '#ffcc00', '#ff9900');
    }
    
    const gaugeAbandono = document.getElementById('gaugeAbandono');
    if (gaugeAbandono && !isMetricaAbandono) {
        initGaugeChart('gaugeAbandono', '#ff3366', '#ff0000');
    }
    
    // El gauge de conversaciones solo existe en la página principal
    const gaugeConversaciones = document.getElementById('gaugeConversaciones');
    if (gaugeConversaciones) {
        initGaugeChart('gaugeConversaciones', '#4338ca', '#6d28d9');
    }
    
    // Inicializar gráfico de barras para métricas de tiempo
    const timeMetrics = document.getElementById('timeMetrics');
    if (timeMetrics) {
        initTimeMetricsChart();
    }
    
    // Inicializar gráfico de línea para conversaciones por hora
    const hourlyChats = document.getElementById('hourlyChats');
    if (hourlyChats) {
        initHourlyChart();
    }
}

// Función para inicializar el gráfico de barras de métricas de tiempo
function initTimeMetricsChart() {
    const canvas = document.getElementById('timeMetrics');
    
    // Destruir el gráfico existente si existe
    if (chartInstances['timeMetrics']) {
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

// Función para cargar datos por hora (mejorada)
function cargarDatosPorHora() {
    
    const fecha = document.getElementById('fecha')?.value || new Date().toISOString().split('T')[0];
    
    // Calcular inicio y fin de mes para la fecha seleccionada
    const fechaObj = new Date(fecha);
    const inicioMes = new Date(fechaObj.getFullYear(), fechaObj.getMonth(), 1);
    const finMes = new Date(fechaObj.getFullYear(), fechaObj.getMonth() + 1, 0);
    
    const inicioFormatted = inicioMes.toISOString().split('T')[0];
    const finFormatted = finMes.toISOString().split('T')[0];
    
    fetch(`includes/get_metrics.php?action=hourly_stats&start_date=${inicioFormatted}&end_date=${finFormatted}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data && data.labels && data.values) {
                if (typeof updateHourlyChart === 'function') {
                    updateHourlyChart(data.labels, data.values);
                }
            } else {
                // Si no hay un formato reconocible, intentar verificar otras estructuras
                if (data && data.hourly_data) {
                    if (data.hourly_data.labels && data.hourly_data.values) {
                        updateHourlyChart(data.hourly_data.labels, data.hourly_data.values);
                    }
                } else if (data && data.statistics) {
                    // Intentar procesar datos de statistics
                    const labels = [];
                    const values = [];
                    
                    data.statistics.forEach(item => {
                        if (item.period) {
                            // Extraer la hora del timestamp
                            const hora = item.period.includes('T') ? 
                                item.period.split('T')[1].substring(0, 5) : 
                                '00:00';
                            labels.push(hora);
                            values.push(item.total_chats || 0);
                        }
                    });
                    
                    if (labels.length > 0 && values.length > 0) {
                        updateHourlyChart(labels, values);
                    } else {
                        updateHourlyChart([], []); // Sin datos
                    }
                } else {
                    updateHourlyChart([], []); // Sin datos
                }
            }
        })
        .catch(error => {
            updateHourlyChart([], []); // Mostrar gráfico vacío en caso de error
        });
}

// Función para actualizar el gráfico de conversaciones por hora con datos dinámicos
function updateHourlyChart(labels, values) {
    
    // Verificar si el canvas existe
    const canvas = document.getElementById('hourlyChats');
    
    // Si el gráfico ya existe, destruirlo completamente
    if (chartInstances['hourlyChats']) {
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
}

// Función para inicializar el gráfico de conversaciones por hora
function initHourlyChart() {
    const canvas = document.getElementById('hourlyChats');
    // Crear un gráfico vacío usando la misma lógica de actualización
    updateHourlyChart([], []);
    // Cargar datos de conversaciones por hora después de inicializar
    cargarDatosPorHora();
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
            // Actualizar los valores del DOM para los gauges
            // Solo si estamos en la página principal (no en las páginas de métricas)
            const currentPath = window.location.pathname;
            if (!currentPath.includes('metrica_')) {
                updateGaugeValue('gaugeAtencion', data.attendance_rate || 0);
                updateGaugeValue('gaugeOportunidad', data.opportunity_rate || 0);
                updateGaugeValue('gaugeAbandono', data.abandonment_rate || 0);
                updateGaugeValue('gaugeConversaciones', data.attendance_rate || 0);
            }

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
    const canvas = document.getElementById(canvasId);
    
    // Destruir el gráfico existente si existe
    if (chartInstances[canvasId]) {
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
    // Marcar este gauge como inicializado
    initializedGauges[canvasId] = true;
    
    // AÑADIR EL SÍMBOLO % AL VALOR DESPUÉS DE CREAR EL GRÁFICO
    if (valueElement && !valueElement.textContent.includes('%')) {
        valueElement.textContent = percentage + '%';
    }
    
    // Hacer el gauge clickeable
    canvas.style.cursor = 'pointer';
    canvas.addEventListener('click', function() {
        if (canvasId === 'gaugeAtencion') {
            window.location.href = 'metrica_atencion.php';
        } else if (canvasId === 'gaugeOportunidad') {
            window.location.href = 'metrica_oportunidad.php';
        } else if (canvasId === 'gaugeAbandono') {
            window.location.href = 'metrica_abandono.php';
        }
    });
    
    // También hacer clic en el valor para redirigir
    if (valueElement) {
        valueElement.style.cursor = 'pointer';
        valueElement.addEventListener('click', function() {
            if (canvasId === 'gaugeAtencion') {
                window.location.href = 'metrica_atencion.php';
            } else if (canvasId === 'gaugeOportunidad') {
                window.location.href = 'metrica_oportunidad.php';
            } else if (canvasId === 'gaugeAbandono') {
                window.location.href = 'metrica_abandono.php';
            }
        });
    }
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
    
    // Buscar el elemento span con clase time-value dentro del elemento
    const valueElement = element.querySelector('.time-value');
    if (valueElement) {
        // Asegurar que el valor sea numérico y no NaN
        const numValue = parseFloat(value);
        const displayValue = isNaN(numValue) ? 0 : numValue.toFixed(1);
        
        valueElement.textContent = `${displayValue} minutos`;
    }
}

// Función para actualizar metricas de rendimiento en el dashboard principal
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


// Funciones auxiliares para fechas
function getFirstDayOfMonth() {
    const now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
}

function getLastDayOfMonth() {
    const now = new Date();
    return new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
}



// Función para actualizar la tabla de rendimiento por agente
function updateAgentPerformanceTable(data) {
    const tableBody = document.querySelector('.agent-performance-table tbody');
    
    // Limpiar tabla
    tableBody.innerHTML = '';
    
    // Si no hay datos, mostrar mensaje
    if (!data || !Array.isArray(data) || data.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = '<td colspan="6" class="empty-message">No hay datos disponibles para mostrar</td>';
        tableBody.appendChild(row);
        return;
    }
    
    // Ordenar agentes por número de chats atendidos (descendente)
    data.sort((a, b) => {
        const aAttended = parseInt(a.chats_attended || 0);
        const bAttended = parseInt(b.chats_attended || 0);
        return bAttended - aAttended;
    });
    
    // Agregar filas a la tabla
    data.forEach(agent => {
        const row = document.createElement('tr');
        
        // Calcular tasa de atención
        const chatsReceived = parseInt(agent.chats_received || 0);
        const chatsAttended = parseInt(agent.chats_attended || 0);
        const attendanceRate = chatsReceived > 0 ? (chatsAttended / chatsReceived) * 100 : 0;
        
        // Formatear tiempos
        const responseTime = formatMinutes(agent.avg_response_time);
        const duration = formatMinutes(agent.avg_duration);
        
        // Crear HTML para la fila
        row.innerHTML = `
            <td data-label="Agente">${agent.agent_name || agent.agent_email || 'Sin nombre'}</td>
            <td data-label="Chats Recibidos">${chatsReceived}</td>
            <td data-label="Chats Atendidos">${chatsAttended}</td>
            <td data-label="Tasa de Atención">${attendanceRate.toFixed(2)}%</td>
            <td data-label="Tiempo de Respuesta">${responseTime} min</td>
            <td data-label="Duración Promedio">${duration} min</td>
        `;
        
        tableBody.appendChild(row);
    });
}

// Función para cargar datos de rendimiento de agentes
function loadAgentPerformanceData(startDate, endDate) {
    
    const url = `includes/get_metrics.php?action=agent_performance&start_date=${startDate}&end_date=${endDate}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            
            // Verificar estructura de datos
            const agentData = Array.isArray(data?.agents) ? data.agents : Array.isArray(data) ? data : [];

            // Actualizar tabla con los datos
            updateAgentPerformanceTable(agentData);
        })
        .catch(error => {
            updateAgentPerformanceTable([]); // Mostrar tabla vacía en caso de error
        });
}

// Función para inicializar la tabla de rendimiento de agentes
function initAgentPerformanceTable() {
    const tableContainer = document.querySelector('.agent-table-container');
    
    // Obtener fechas del filtro o usar valores predeterminados
    const startDateInput = document.getElementById('inicio');
    const endDateInput = document.getElementById('fin');
    
    const startDate = startDateInput ? startDateInput.value : getFirstDayOfMonth();
    const endDate = endDateInput ? endDateInput.value : getLastDayOfMonth();
    
    // Cargar datos con las fechas
    loadAgentPerformanceData(startDate, endDate);
}
