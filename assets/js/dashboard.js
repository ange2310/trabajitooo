// Código para reemplazar o agregar a dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const contentWrapper = document.querySelector('.content-wrapper');
    const toggleSidebar = document.getElementById('toggle-sidebar');
    
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
            // Si existe la función para redimensionar gráficos, ejecutarla
            if (typeof window.resizeAllCharts === 'function') {
                window.resizeAllCharts();
            }
            
            // Forzar reflow para asegurar que los elementos se ajusten correctamente
            document.body.style.display = 'none';
            document.body.offsetHeight; // Trigger reflow
            document.body.style.display = '';
        }, 300);
    }
    
    // Asignar eventos
    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', toggleSidebarState);
    }
    
    // Para móviles también ajustamos el comportamiento
    const mobileToggle = document.getElementById('mobile-toggle');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Ajustar comportamiento responsive
    function handleResize() {
        const windowWidth = window.innerWidth;
        
        if (windowWidth <= 768) {
            // En móviles, ocultar sidebar y ajustar contenido
            sidebar.classList.remove('collapsed');
            contentWrapper.classList.add('full-width');
            contentWrapper.style.marginLeft = '0';
            contentWrapper.style.width = '100%';
        } else {
            // En desktop, restaurar estado según cookie
            if (cookieCollapsed) {
                const isCollapsed = cookieCollapsed.split('=')[1] === 'true';
                if (isCollapsed) {
                    sidebar.classList.add('collapsed');
                    contentWrapper.classList.add('full-width');
                } else {
                    sidebar.classList.remove('collapsed');
                    contentWrapper.classList.remove('full-width');
                }
            }
        }
    }
    
    // Inicializar según tamaño de pantalla
    handleResize();
    
    // Actualizar al cambiar tamaño de ventana
    window.addEventListener('resize', handleResize);
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
            if (data && data.labels && data.values) {
                if (typeof updateHourlyChart === 'function') {
                    updateHourlyChart(data.labels, data.values);
                }
            }
        })
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