/* JavaScript para el Toggle del Sidebar */

// Código JavaScript a implementar en un archivo dashboard.js
document.addEventListener('DOMContentLoaded', function() {
    // Elementos DOM
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
            contentWrapper.classList.add('expanded');
        }
    }
    
    // Función para alternar el estado del sidebar
    function toggleSidebarState() {
        sidebar.classList.toggle('collapsed');
        contentWrapper.classList.toggle('expanded');
        
        // Guardar estado en cookie
        const isCollapsed = sidebar.classList.contains('collapsed');
        document.cookie = `sidebar_collapsed=${isCollapsed}; path=/; max-age=31536000`;
    }
    
    // Asignar eventos
    if (toggleSidebar) {
        toggleSidebar.addEventListener('click', toggleSidebarState);
    }
    
    // Evento para móviles
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Cerrar sidebar en móviles al hacer clic en enlaces
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a, .logout-btn');
    if (window.innerWidth <= 992) {
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                sidebar.classList.remove('active');
            });
        });
    }
    
    // Añadir orden de animación a las cards
    const cards = document.querySelectorAll('.dashboard-card');
    cards.forEach((card, index) => {
        card.style.setProperty('--animation-order', index);
    });
});