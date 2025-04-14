// Reemplaza completamente el archivo assets/js/charts.js con este código

// Variable global para guardar referencias a los gráficos
var chartInstances = {};
var chartsInitialized = false;

// Inicializar gráficos cuando la página esté lista
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing charts');
    
    // Configurar evento para redimensionar gráficos sin recrearlos
    setupSidebarToggle();
    
    // Solo inicializar gráficos una vez
    if (!chartsInitialized) {
        initCharts();
        chartsInitialized = true;
    }
});
function fetchDashboardMetrics(fecha) {
    return fetch(`includes/get_metrics.php?action=dashboard_metrics&fecha=${fecha}`)
      .then(response => response.json())
      .then(data => {
        console.log('Dashboard Metrics:', data); // Útil para debug
        return data;
      })
      .catch(error => {
        console.error('Error al obtener métricas:', error);
        return null;
      });
  }
  

// Configurar el toggle del sidebar para evitar recreación de gráficos
function setupSidebarToggle() {
    const toggleSidebar = document.getElementById('toggle-sidebar');
    const mobileToggle = document.getElementById('mobile-toggle');
    const sidebar = document.querySelector('.sidebar');
    const contentWrapper = document.querySelector('.content-wrapper');
    
    function toggleSidebarState() {
        sidebar.classList.toggle('collapsed');
        
        // Guardar estado en cookie
        const isCollapsed = sidebar.classList.contains('collapsed');
        document.cookie = `sidebar_collapsed=${isCollapsed}; path=/; max-age=31536000`;
        
        // Ajustar margin del content wrapper
        if (window.innerWidth > 1200) {
            if (isCollapsed) {
                contentWrapper.style.marginLeft = '80px';
                contentWrapper.style.width = 'calc(100% - 80px)';
            } else {
                contentWrapper.style.marginLeft = '250px';
                contentWrapper.style.width = 'calc(100% - 250px)';
            }
        }
        
        // IMPORTANTE: En lugar de recrear los gráficos, solo los redimensionamos
        setTimeout(function() {
            resizeAllCharts();
        }, 300);
    }
    
    // Solo configurar el evento una vez
    if (toggleSidebar && !toggleSidebar._sidebarEventAttached) {
        toggleSidebar.addEventListener('click', toggleSidebarState);
        toggleSidebar._sidebarEventAttached = true;
    }
    
    // Toggle del sidebar en móviles
    if (mobileToggle && !mobileToggle._sidebarEventAttached) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            setTimeout(function() {
                resizeAllCharts();
            }, 300);
        });
        mobileToggle._sidebarEventAttached = true;
    }
}

// Función para redimensionar todos los gráficos existentes
function resizeAllCharts() {
    for (let id in chartInstances) {
        if (chartInstances[id] && typeof chartInstances[id].resize === 'function') {
            chartInstances[id].resize();
        }
    }
}
function initConversacionesGauge() {
    console.log('Inicializando gauge de conversaciones personalizado');
    const canvas = document.getElementById('gaugeConversaciones');
    if (!canvas) return;
    
    // Destruir gráfico existente si hay uno
    if (chartInstances['gaugeConversaciones']) {
        chartInstances['gaugeConversaciones'].destroy();
        chartInstances['gaugeConversaciones'] = null;
    }
    
    const ctx = canvas.getContext('2d');
    
    // Valor del gauge
    const valueElement = canvas.parentElement.querySelector('.gauge-value');
    const percentage = valueElement ? parseFloat(valueElement.textContent) : 0;
    
    // Gradiente personalizado
    const gradientColors = ctx.createLinearGradient(0, 0, 0, 200);
    gradientColors.addColorStop(0, '#5b45e0');  // Un poco más claro
    gradientColors.addColorStop(1, '#7c5bf2');  // Un poco más púrpura
    
    // Crear el gráfico con opciones personalizadas
    chartInstances['gaugeConversaciones'] = new Chart(ctx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [percentage, 100 - percentage],
                backgroundColor: [
                    gradientColors,
                    'rgba(30, 30, 60, 0.2)' // Fondo más transparente
                ],
                borderWidth: 0,
                cutout: '80%'  // Más delgado que el predeterminado
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 1500
            },
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false }
            }
        }
    });
}

// Función principal para inicializar todos los gráficos
function initCharts() {
    console.log('Initializing all charts');
    
    // Inicializar gráficos de gauge (medidores circulares)
    initGaugeChart('gaugeAtencion', '#9933ff', '#3366ff');
    initGaugeChart('gaugeOportunidad', '#ffcc00', '#ff9900');
    initGaugeChart('gaugeAbandono', '#ff3366', '#ff0000');
    initConversacionesGauge()

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
    
    // IMPORTANTE: Verificar si ya existe un gráfico y destruirlo correctamente
    if (chartInstances[canvasId]) {
        console.log('Destroying existing chart: ' + canvasId);
        chartInstances[canvasId].destroy();
        chartInstances[canvasId] = null;
    }
    
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.warn('Could not get context for: ' + canvasId);
        return;
    }
    
    // Obtener el valor del gauge del elemento hermano con clase gauge-value
    const valueElement = canvas.parentElement.querySelector('.gauge-value');
    const percentage = valueElement ? parseFloat(valueElement.textContent) : 0;
    
    // Crear gradiente
    const gradientColors = ctx.createLinearGradient(0, 0, 0, 150);
    gradientColors.addColorStop(0, color1);
    gradientColors.addColorStop(1, color2);
    
    // IMPORTANTE: Configurar el gráfico con responsive y maintainAspectRatio bien configurados
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
    
    // IMPORTANTE: Destruir el gráfico existente si existe
    if (chartInstances['timeMetrics']) {
        console.log('Destroying existing time metrics chart');
        chartInstances['timeMetrics'].destroy();
        chartInstances['timeMetrics'] = null;
    }
    
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.warn('Could not get context for: timeMetrics');
        return;
    }
    
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
    
    // IMPORTANTE: Destruir el gráfico existente si existe
    if (chartInstances['hourlyChats']) {
        console.log('Destroying existing hourly chart');
        chartInstances['hourlyChats'].destroy();
        chartInstances['hourlyChats'] = null;
    }
    
    const ctx = canvas.getContext('2d');
    if (!ctx) {
        console.warn('Could not get context for: hourlyChats');
        return;
    }
    
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

// Detectar cambios de tamaño de ventana para ajustar los gráficos
window.addEventListener('resize', function() {
    // Usar un temporizador para evitar actualizaciones demasiado frecuentes
    if (this.resizeTimer) clearTimeout(this.resizeTimer);
    this.resizeTimer = setTimeout(function() {
        resizeAllCharts();
    }, 300);
});