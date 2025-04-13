<div class="sidebar">
    <!-- Logo y título -->
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-brand">
            <div class="sidebar-logo">
                <!-- Puedes usar un SVG o imagen aquí -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="#4169E1"/>
                    <path d="M2 17L12 22L22 17" stroke="#4169E1" stroke-width="2"/>
                    <path d="M2 12L12 17L22 12" stroke="#4169E1" stroke-width="2"/>
                </svg>
            </div>
            <span>Dashboard</span>
        </a>
        <!-- Botón para móviles (opcional) -->
        <button class="sidebar-toggle" id="sidebarToggle">
            <span></span>
        </button>
    </div>

    <!-- Menú principal -->
    <nav class="sidebar-nav">
        <div class="sidebar-section">
            <h6 class="sidebar-heading">NAVEGACIÓN</h6>
            <ul class="sidebar-menu">
                <li class="sidebar-item active">
                    <a href="index.php" class="sidebar-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="tables.php" class="sidebar-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3h18v18H3zM3 9h18M9 21V9"></path>
                        </svg>
                        <span>Tablas</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="rtl.php" class="sidebar-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12h-8M3 8h8M3 16h8"></path>
                        </svg>
                        <span>RTL</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Sección de cuenta -->
        <div class="sidebar-section">
            <h6 class="sidebar-heading">CUENTA</h6>
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="profile.php" class="sidebar-link">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Perfil</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Información de usuario y logout -->
    <div class="sidebar-footer">
        <?php if (isset($_SESSION['user'])): ?>
            <div class="user-info">
                <div class="user-avatar">
                    <!-- Opcional: imagen de avatar del usuario -->
                    <div class="user-initial"><?php echo substr($_SESSION['user']['name'] ?? 'U', 0, 1); ?></div>
                </div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'Usuario'); ?></div>
                    <div class="user-role"><?php echo htmlspecialchars($_SESSION['user']['role'] ?? 'Administrador'); ?></div>
                </div>
                <a href="logout.php" class="logout-button" title="Cerrar sesión">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Script para toggle del sidebar en móviles (opcional) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('sidebarToggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
        });
    }
});
</script>