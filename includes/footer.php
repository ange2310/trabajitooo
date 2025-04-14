<!-- JavaScript para el dashboard -->
<script src="assets/js/charts.js"></script>
<script>
    // Script para el toggle del sidebar
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle del sidebar
        const toggleSidebar = document.getElementById('toggle-sidebar');
        const mobileToggle = document.getElementById('mobile-toggle');
        const sidebar = document.querySelector('.sidebar');
        const contentWrapper = document.querySelector('.content-wrapper');
        
        // Función para alternar el estado del sidebar
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
        }
        
        // Evento de click en el toggle del sidebar
        if (toggleSidebar) {
            toggleSidebar.addEventListener('click', toggleSidebarState);
        }
        
        // Toggle del sidebar en móviles
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }
        
        // NO inicializar gráficos aquí, ya se inicializan en charts.js
    });
</script>
</body>
</html>