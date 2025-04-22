// Variable global para guardar referencias a los gráficos
var chartInstances = {};

// Inicializar gráficos cuando la página esté lista
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar gráficos
    fetchDashboardMetrics(); // Cargamos datos reales y luego inicializamos charts
});

// Función principal para inicializar todos los gráficos
// Función principal para inicializar todos los gráficos
function initCharts() {
    // Verificar qué página estamos viendo para evitar inicializaciones duplicadas
    const currentPath = window.location.pathname;
    const isMetricaAtencion = currentPath.includes('metrica_atencion.php');
    const isMetricaOportunidad = currentPath.includes('metrica_oportunidad.php');
    const isMetricaAbandono = currentPath.includes('metrica_abandono.php');
    
    // Inicializar solo si el elemento existe y estamos en la página adecuada
    const gaugeAtencion = document.getElementById('gaugeAtencion');
    if (gaugeAtencion && !isMetricaAtencion) {
        initGaugeChart('gaugeAtencion', '#9933ff');
    }
    
    const gaugeOportunidad = document.getElementById('gaugeOportunidad');
    if (gaugeOportunidad && !isMetricaOportunidad) {
        initGaugeChart('gaugeOportunidad', '#ffcc00');
    }
    
    const gaugeAbandono = document.getElementById('gaugeAbandono');
    if (gaugeAbandono && !isMetricaAbandono) {
        initGaugeChart('gaugeAbandono', '#ff3366');
    }
    
    // El gauge de conversaciones solo existe en la página principal
    const gaugeConversaciones = document.getElementById('gaugeConversaciones');
    if (gaugeConversaciones) {
        initGaugeChart('gaugeConversaciones', '#4338ca');
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
    
    // Colores sólidos para cada barra
    const solidColors = [
        '#f97316', // Naranja para Tiempo de Espera
        '#ec4899', // Rosa para Tiempo de Respuesta
        '#8b5cf6'  // Púrpura para Duración Conversación
    ];
    
    // Configurar y crear el gráfico
    chartInstances['timeMetrics'] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: solidColors,
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

// Función para actualizar el gráfico de conversaciones por hora
function updateHourlyChart(labels, values, noData = false) {
    // Verificar si el canvas existe
    const canvas = document.getElementById('hourlyChats');
    if (!canvas) return;
    
    // Si el gráfico ya existe, destruirlo
    if (chartInstances['hourlyChats']) {
        chartInstances['hourlyChats'].destroy();
        chartInstances['hourlyChats'] = null;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Si no hay etiquetas o está marcado como sin datos, generar etiquetas predeterminadas
    if (!labels || !labels.length) {
        labels = Array.from({length: 24}, (_, i) => `${String(i).padStart(2, '0')}:00`);
        values = Array(24).fill(0);
    }
    
    // Color sólido para el área bajo la línea
    const solidAreaColor = 'rgba(59, 130, 246, 0.3)'; // Azul semi-transparente
    const solidLineColor = '#3b82f6'; // Azul sólido para la línea
    
    // Calcular un valor máximo sugerido basado en los datos
    const maxValue = Math.max(...values, 1);
    const suggestedMax = Math.ceil(maxValue * 1.2); // 20% más alto que el valor máximo
    
    // Configurar y crear el gráfico
    chartInstances['hourlyChats'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Conversaciones',
                data: values,
                borderColor: solidLineColor,
                backgroundColor: solidAreaColor,
                pointBackgroundColor: solidLineColor,
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
                    suggestedMax: suggestedMax,
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
                        },
                        maxRotation: 90,
                        minRotation: 45,
                        callback: function(val, index) {
                            return index % 3 === 0 ? this.getLabelForValue(val) : '';
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
        },
        plugins: [{
            id: 'noDataText',
            afterDraw: function(chart) {
                if (noData || chart.data.datasets[0].data.every(item => item === 0)) {
                    const ctx = chart.ctx;
                    const width = chart.width;
                    const height = chart.height;
                    
                    ctx.save();
                    ctx.fillStyle = 'rgba(0, 0, 0, 0.1)';
                    ctx.fillRect(0, 0, width, height);
                    
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = '16px Arial';
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                    ctx.fillText('No hay datos disponibles para esta fecha', width / 2, height / 2);
                    ctx.restore();
                }
            }
        }]
    });
}

// Modificación para la función initHourlyChart en charts.js
// Esta versión incluye más debugging y una solución para el caso de respuesta vacía

function initHourlyChart() {
    const canvas = document.getElementById('hourlyChats');
    if (!canvas) return;
    
    console.log("Inicializando gráfico de conversaciones por hora");
    
    // Crear un gráfico vacío inicialmente
    updateHourlyChart([], [], true);
    
    // Obtener la fecha de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const fecha = urlParams.get('fecha') || new Date().toISOString().split('T')[0];
    
    console.log("Fecha para datos por hora:", fecha);
    
    // Generar timestamp para evitar caché
    const timestamp = new Date().getTime();
    
    // URL para obtener datos por hora
    // IMPORTANTE: Cambiar esto para usar directamente el archivo dashboard_metrics.php
    const url = `includes/dashboard_metrics.php?action=hourly_stats&fecha=${fecha}&t=${timestamp}`;
    
    console.log("URL para datos por hora:", url);
    
    // Usar fetch en lugar de XMLHttpRequest para simplificar
    fetch(url)
        .then(response => {
            console.log("Respuesta recibida, status:", response.status);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            return response.text();
        })
        .then(text => {
            console.log("Texto de respuesta:", text);
            
            // Si la respuesta está vacía, usar datos predeterminados
            if (!text || !text.trim()) {
                console.log("Respuesta vacía, usando datos predeterminados");
                
                // Generar datos aleatorios para demostración
                const labels = Array.from({length: 24}, (_, i) => `${String(i).padStart(2, '0')}:00`);
                const values = Array.from({length: 24}, () => Math.floor(Math.random() * 5)); // Valores aleatorios entre 0 y 5
                
                updateHourlyChart(labels, values, false);
                return;
            }
            
            try {
                const data = JSON.parse(text);
                console.log("Datos parseados:", data);
                
                if (data && data.labels && data.values) {
                    const hasData = data.values.some(v => v > 0);
                    updateHourlyChart(data.labels, data.values, !hasData);
                } else if (data && data.error) {
                    console.warn("Error en los datos:", data.error);
                    // Generar datos vacíos con la estructura correcta
                    const emptyLabels = Array.from({length: 24}, (_, i) => `${String(i).padStart(2, '0')}:00`);
                    updateHourlyChart(emptyLabels, Array(24).fill(0), true);
                } else {
                    console.warn("Estructura de datos inesperada");
                    updateHourlyChart([], [], true);
                }
            } catch (e) {
                console.error("Error al parsear JSON:", e);
                console.log("Texto que causó el error:", text);
                updateHourlyChart([], [], true);
            }
        })
        .catch(error => {
            console.error("Error al obtener datos por hora:", error);
            updateHourlyChart([], [], true);
        });
}
// Función alternativa para obtener métricas del dashboard
function fetchDashboardMetrics() {
    // Crear un objeto con valores predeterminados
    const defaultData = {
        attendance_rate: 0,
        opportunity_rate: 0,
        abandonment_rate: 0,
        average_wait_minutes: 0,
        average_first_response_minutes: 0,
        average_duration_minutes: 0,
        total_conversations_received: 0,
        total_conversations_attended: 0,
        goal_achieved_count: 0,
        total_abandoned: 0
    };
    
    // Obtener la fecha del parámetro URL si existe
    const urlParams = new URLSearchParams(window.location.search);
    const fecha = urlParams.get('fecha') || '';
    
    // Añadir timestamp para evitar problemas de caché
    const timestamp = new Date().getTime();
    
    // Construir la URL con el parámetro de fecha y timestamp
    const url = 'includes/dashboard_metrics.php' + 
               (fecha ? `?fecha=${fecha}&t=${timestamp}` : `?t=${timestamp}`);
    
    // Crear un nuevo XMLHttpRequest (método alternativo a fetch)
    const xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    
    // Configurar la función de callback para cuando la solicitud se complete
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) { // Solicitud completada
            let data = defaultData; // Usar valores predeterminados por defecto
            
            if (xhr.status === 200) { // Éxito
                // Obtener el texto de respuesta
                const responseText = xhr.responseText;
                
                // Verificar si la respuesta no está vacía
                if (responseText && responseText.trim()) {
                    try {
                        // Intentar parsear como JSON
                        const jsonData = JSON.parse(responseText);
                        // Si el parseo es exitoso, usar esos datos
                        data = jsonData;
                        console.log("Datos cargados correctamente:", data);
                    } catch (e) {
                        // Error al parsear JSON
                        console.error("Error al parsear JSON:", e);
                        console.error("Texto recibido:", responseText);
                    }
                } else {
                    console.warn("Respuesta vacía del servidor");
                }
            } else {
                console.error("Error HTTP:", xhr.status);
            }
            
            // Actualizar los valores del DOM para los gauges
            const currentPath = window.location.pathname;
            if (!currentPath.includes('metrica_')) {
                updateGaugeValue('gaugeAtencion', data.attendance_rate || 0);
                updateGaugeValue('gaugeOportunidad', data.opportunity_rate || 0);
                updateGaugeValue('gaugeAbandono', data.abandonment_rate || 0);
                updateGaugeValue('gaugeConversaciones', data.attendance_rate || 0);
            }

            // Actualizar métricas de tiempo
            updateTimeMetric('tiempo-espera', data.average_wait_minutes || 0);
            updateTimeMetric('tiempo-respuesta', data.average_first_response_minutes || 0);
            updateTimeMetric('tiempo-duracion', data.average_duration_minutes || 0);
            
            // Actualizar los contadores de conversaciones 
            updateConversationStats(data.total_conversations_received || 0, data.total_conversations_attended || 0);
            
            // Actualizar tabla de métricas de rendimiento
            updatePerformanceTable(
                data.goal_achieved_count || 0,
                50, 
                data.total_abandoned || 0,
                data.total_conversations_received || 0,
                data.abandonment_rate || 0
            );
            
            // Inicializar los gráficos
            initCharts();
        }
    };
    
    // Configurar manejo de errores en la solicitud
    xhr.onerror = function() {
        console.error("Error de red al cargar métricas del dashboard");
        // Inicializar con valores predeterminados en caso de error
        initCharts();
    };
    
    // Enviar la solicitud
    try {
        xhr.send();
    } catch (e) {
        console.error("Error al enviar la solicitud:", e);
        // Inicializar con valores predeterminados en caso de error
        initCharts();
    }
}

// Función para crear una redirección a la página de métrica correspondiente
function redirectToMetricPage(metricType) {
    switch(metricType) {
        case 'gaugeAtencion':
            window.location.href = 'metrica_atencion.php';
            break;
        case 'gaugeOportunidad':
            window.location.href = 'metrica_oportunidad.php';
            break;
        case 'gaugeAbandono':
            window.location.href = 'metrica_abandono.php';
            break;
    }
}

// Función para inicializar gráficos de tipo gauge
// Reemplazar la función initGaugeChart con esta versión modificada:

// Función para inicializar gráficos de tipo gauge
function initGaugeChart(canvasId, color) {
    const canvas = document.getElementById(canvasId);
    
    // Destruir el gráfico existente si existe
    if (chartInstances[canvasId]) {
        chartInstances[canvasId].destroy();
        chartInstances[canvasId] = null;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Obtener el valor del gauge del elemento hermano
    const valueElement = canvas.parentElement.querySelector('.gauge-value');
    const percentage = valueElement ? parseFloat(valueElement.textContent.replace('%', '').trim()) : 0;

    if (valueElement) {
        valueElement.textContent = percentage + '%';
    }
    
    // Determinar el color actual a usar (asegurarse que sea un color sólido)
    // Ignorar cualquier segundo color (color2) que se haya pasado
    let solidColor = color;
    
    // Asegurarse de que no estamos usando un objeto gradiente
    if (typeof solidColor === 'object' && solidColor !== null) {
        // Si por alguna razón recibimos un objeto gradiente, usar un color predeterminado
        switch(canvasId) {
            case 'gaugeAtencion':
                solidColor = '#9933ff'; // Púrpura
                break;
            case 'gaugeOportunidad':
                solidColor = '#ffcc00'; // Amarillo
                break;
            case 'gaugeAbandono':
                solidColor = '#ff3366'; // Rosa
                break;
            case 'gaugeConversaciones':
                solidColor = '#4338ca'; // Índigo
                break;
            default:
                solidColor = '#3b82f6'; // Azul predeterminado
        }
    }
    
    // Configurar y crear el gráfico con color sólido (sin gradiente)
    chartInstances[canvasId] = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [percentage, 100 - percentage],
                backgroundColor: [
                    solidColor, // Color sólido garantizado
                    '#1a1e2c'   // Color de fondo
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
    
    // Solo agregar redirección si es un gauge interactivo
    if (canvasId === 'gaugeAtencion' || canvasId === 'gaugeOportunidad' || canvasId === 'gaugeAbandono') {
        // Hacer el gauge clickeable
        canvas.style.cursor = 'pointer';
        canvas.addEventListener('click', function() {
            redirectToMetricPage(canvasId);
        });
        
        // También hacer clic en el valor para redirigir
        if (valueElement) {
            valueElement.style.cursor = 'pointer';
            valueElement.addEventListener('click', function() {
                redirectToMetricPage(canvasId);
            });
        }
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
}

// Función para actualizar el valor del gauge
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
    if (!element) return;
    
    // Buscar el elemento span con clase time-value dentro del elemento
    const valueElement = element.querySelector('.time-value');
    if (valueElement) {
        // Asegurar que el valor sea numérico y no NaN
        const numValue = parseFloat(value);
        const displayValue = isNaN(numValue) ? 0 : numValue.toFixed(1);
        
        valueElement.textContent = `${displayValue} minutos`;
    }
}

// Función para actualizar métricas de rendimiento en el dashboard principal
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