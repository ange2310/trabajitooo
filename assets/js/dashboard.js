
//Este archivo maneja interactividad del dashboard. Controla el toggle del sidebar y funciones para persistencia de fecha seleccionada
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

    // Configurar persistencia de fecha
    setupDatePersistence();
    
    // Configuración para mantener fechas en redirecciones
    mantenerFechaEnRedirecciones();

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


// Función para manejar la persistencia de la fecha seleccionada
function setupDatePersistence() {
    const fechaInput = document.getElementById('fecha');
    
    if (fechaInput) {
        //Al cargar la página, verificar si hay una fecha guardada
        const storedDate = sessionStorage.getItem('dashboardDate');
        
        // Si hay una fecha guardada y es diferente a la actual en el input
        if (storedDate && storedDate !== fechaInput.value) {
            // Actualizar el valor del input con la fecha guardada
            fechaInput.value = storedDate;
            
            // Comprobar si no estamos en una URL que ya tiene el parámetro fecha
            const urlParams = new URLSearchParams(window.location.search);
            const urlFecha = urlParams.get('fecha');
            
            // Si la URL no tiene fecha o es diferente a la guardada, redirigir con la fecha correcta
            if (!urlFecha || urlFecha !== storedDate) {
                // Evitar redirect loops añadiendo una marca de redirección
                if (!sessionStorage.getItem('redirecting')) {
                    sessionStorage.setItem('redirecting', 'true');
                    // Construir la nueva URL con la fecha guardada
                    let newUrl = window.location.pathname;
                    if (urlParams.toString()) {
                        // Reemplazar o agregar el parámetro fecha
                        urlParams.set('fecha', storedDate);
                        newUrl += '?' + urlParams.toString();
                    } else {
                        newUrl += '?fecha=' + storedDate;
                    }
                    window.location.href = newUrl;
                    return; // Detener la ejecución para evitar cambios durante la redirección
                }
            }
        }
        
        // Limpiar marca de redirección después de cargar
        sessionStorage.removeItem('redirecting');
        
        // Cuando el usuario cambia la fecha, guardarla
        fechaInput.addEventListener('change', function() {
            const newDate = this.value;
            // Guardar en sessionStorage
            sessionStorage.setItem('dashboardDate', newDate);
            
            // El resto del comportamiento (envío de formulario) se mantiene igual
            const fechaForm = this.closest('form');
            if (fechaForm) {
                fechaForm.submit();
            }
        });
    }
    
    // 3. Asegurar que los enlaces mantienen la fecha seleccionada
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const storedDate = sessionStorage.getItem('dashboardDate');
            if (storedDate) {
                // Si el enlace ya tiene parámetros
                if (this.href.includes('?')) {
                    // Verificar si ya tiene parámetro fecha
                    if (this.href.includes('fecha=')) {
                        // Reemplazar el valor de fecha en el enlace
                        this.href = this.href.replace(/fecha=[^&]+/, 'fecha=' + storedDate);
                    } else {
                        // Añadir parámetro fecha al final
                        this.href += '&fecha=' + storedDate;
                    }
                } else {
                    // Si el enlace no tiene parámetros, añadir fecha
                    this.href += '?fecha=' + storedDate;
                }
            }
        });
    });
}


// Función para mantener la fecha al cambiar entre métricas específicas y dashboard
function mantenerFechaEnRedirecciones() {
    // Links a las páginas de métricas desde el dashboard
    const gaugeLinks = [
        { id: 'gaugeAtencion', url: 'metrica_atencion.php' },
        { id: 'gaugeOportunidad', url: 'metrica_oportunidad.php' },
        { id: 'gaugeAbandono', url: 'metrica_abandono.php' }
    ];
    
    gaugeLinks.forEach(linkInfo => {
        const element = document.getElementById(linkInfo.id);
        if (element) {
            // Reemplazar la función existente por una que mantenga la fecha
            element.addEventListener('click', function(e) {
                e.preventDefault(); // Prevenir comportamiento por defecto
                
                // Obtener la fecha actual del selector o de sessionStorage
                const fechaInput = document.getElementById('fecha');
                let selectedDate = fechaInput ? fechaInput.value : null;
                
                if (!selectedDate) {
                    selectedDate = sessionStorage.getItem('dashboardDate');
                }
                
                if (!selectedDate) {
                    selectedDate = new Date().toISOString().split('T')[0]; // Fecha actual si no hay nada
                }
                
                // Construir URL con la fecha
                let targetUrl = linkInfo.url + '?fecha=' + selectedDate;
                
                // Redireccionar
                window.location.href = targetUrl;
            });
            
            // También aplicar al valor del gauge si está disponible
            const valueElement = element.parentElement.querySelector('.gauge-value');
            if (valueElement) {
                valueElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Obtener la fecha actual del selector o de sessionStorage
                    const fechaInput = document.getElementById('fecha');
                    let selectedDate = fechaInput ? fechaInput.value : null;
                    
                    if (!selectedDate) {
                        selectedDate = sessionStorage.getItem('dashboardDate');
                    }
                    
                    if (!selectedDate) {
                        selectedDate = new Date().toISOString().split('T')[0];
                    }
                    
                    // Redireccionar con la fecha
                    window.location.href = linkInfo.url + '?fecha=' + selectedDate;
                });
            }
        }
    });
    
    // Botón de retorno al dashboard desde páginas de métricas
    const backToDashboard = document.querySelector('.back-to-dashboard');
    if (backToDashboard) {
        backToDashboard.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Obtener la fecha desde sessionStorage o desde los filtros actuales
            let selectedDate = sessionStorage.getItem('dashboardDate');
            
            // Si no hay fecha en session, intentar obtenerla de los filtros de la página actual
            if (!selectedDate) {
                const inicioInput = document.getElementById('inicio');
                if (inicioInput && inicioInput.value) {
                    selectedDate = inicioInput.value;
                }
            }
            
            // Redireccionar al dashboard con la fecha
            if (selectedDate) {
                window.location.href = 'index.php?fecha=' + selectedDate;
            } else {
                window.location.href = 'index.php';
            }
        });
    }
}


