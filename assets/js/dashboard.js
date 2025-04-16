document.addEventListener('DOMContentLoaded', function() {
    
    const sidebar = document.querySelector('.sidebar');
    const contentWrapper = document.querySelector('.content-wrapper');
    const toggleSidebar = document.getElementById('toggle-sidebar');
    const mobileToggle = document.getElementById('mobile-toggle');
    
    // Comprobar el estado inicial con cookie
    const cookieCollapsed = document.cookie.split('; ').find(row => row.startsWith('sidebar_collapsed='));
    if (cookieCollapsed) {
        const isCollapsed = cookieCollapsed.split('=')[1] === 'true';
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            contentWrapper.classList.add('full-width');
        }
    }
    
    // Función para alternar el estado del sidebar
    function toggleSidebarState() {
        sidebar.classList.toggle('collapsed');
        contentWrapper.classList.toggle('full-width');
        
        // Guardar estado en cookie
        const isCollapsed = sidebar.classList.contains('collapsed');
        document.cookie = `sidebar_collapsed=${isCollapsed}; path=/; max-age=31536000`;
        
        // Redimensionar gráficos después de la transición
        setTimeout(function() {
            if (typeof resizeAllCharts === 'function') {
                resizeAllCharts();
            }
        }, 300);
    }
    
    // Asignar eventos
    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', toggleSidebarState);
    }
    
    // Evento para móviles
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            
            // Redimensionar gráficos después de la transición
            setTimeout(function() {
                if (typeof resizeAllCharts === 'function') {
                    resizeAllCharts();
                }
            }, 300);
        });
    }
    
    /* ===== Manejo de los Gráficos y Actualización de Datos ===== */
    
    // Configurar listener para cambio de fecha manual (sin submit de formulario)
    setupDateListener();
    
    // Inicializar datos si están disponibles desde PHP
    if (typeof dashboard_data !== 'undefined') {
        
        // Esperar a que los gráficos estén inicializados antes de actualizar
        setTimeout(function() {
            // Actualizar el gráfico de conversaciones por hora si la función está disponible
            if (typeof updateHourlyChart === 'function' && 
                dashboard_data.hourlyData && 
                dashboard_data.hourlyData.labels && 
                dashboard_data.hourlyData.values) {
                updateHourlyChart(
                    dashboard_data.hourlyData.labels, 
                    dashboard_data.hourlyData.values
                );
            } else {
                // Si no hay datos por hora en el paso inicial, intentar cargarlos
                cargarDatosPorHora();
            }
        }, 5); // tiempo para que charts.js inicialice los gráficos en ms
    }
});

// Función para cargar datos por hora
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
            console.log('Datos por hora cargados:', data);
            if (data && data.labels && data.values) {
                if (typeof updateHourlyChart === 'function') {
                    updateHourlyChart(data.labels, data.values);
                }
            }
        })
        .catch(error => {
            console.error('Error al cargar datos por hora:', error);
        });
}

// Función para configurar el listener del selector de fecha
function setupDateListener() {
    const fechaInput = document.getElementById('fecha');
    
    if (fechaInput) {
        // Verificar si ya tiene el atributo onchange que hace submit
        const tieneOnchange = fechaInput.hasAttribute('onchange');
        
        // Si tiene onchange que hace submit automático, modificar el comportamiento
        if (tieneOnchange) {
            // Guardar el formulario al que pertenece
            const fechaForm = fechaInput.closest('form');
            
            // Remover el atributo onchange para evitar submit automático
            fechaInput.removeAttribute('onchange');
            
            // Añadir un nuevo event listener
            fechaInput.addEventListener('change', function() {
                // Obtener la fecha seleccionada
                const nuevaFecha = this.value;
                console.log('Nueva fecha seleccionada:', nuevaFecha);
                
                // Intentar actualizar gráficos con AJAX si la función está disponible
                if (typeof loadDashboardData === 'function') {
                    loadDashboardData(nuevaFecha);
                    
                    // Actualizar la URL sin recargar la página
                    const newUrl = window.location.pathname + '?fecha=' + nuevaFecha;
                    window.history.pushState({ fecha: nuevaFecha }, '', newUrl);
                } else {
                    // Si la función no está disponible, hacer submit del formulario
                    if (fechaForm) {
                        fechaForm.submit();
                    }
                }
            });
        }
    }
}

// Manejar eventos de navegación (botones atrás/adelante)
window.addEventListener('popstate', function(event) {
    if (event.state && event.state.fecha) {
        const fechaInput = document.getElementById('fecha');
        if (fechaInput) {
            fechaInput.value = event.state.fecha;
            
            // Cargar datos para la fecha restaurada
            if (typeof loadDashboardData === 'function') {
                loadDashboardData(event.state.fecha);
            } else {
                // Si no está disponible la función, hacer submit del formulario
                const fechaForm = fechaInput.closest('form');
                if (fechaForm) {
                    fechaForm.submit();
                }
            }
        }
    }
});