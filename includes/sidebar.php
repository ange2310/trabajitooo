<div class="sidebar <?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true' ? 'collapsed' : ''; ?>">
    <div class="sidebar-header">
        <button id="toggle-sidebar" class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <div class="sidebar-content">
        <!-- Menú principal -->
        <ul class="sidebar-menu">
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php<?php echo isset($_SESSION['dashboard_fecha']) ? '?fecha='.htmlspecialchars($_SESSION['dashboard_fecha']) : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'tables.php' ? 'active' : ''; ?>">
                <a href="tables.php<?php echo isset($_SESSION['dashboard_fecha']) ? '?fecha='.htmlspecialchars($_SESSION['dashboard_fecha']) : ''; ?>">
                    <i class="fas fa-table"></i>
                    <span>Tables</span>
                </a>
            </li>
            <!-- Nuevos iconos para gauges -->
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'metrica_atencion.php' ? 'active' : ''; ?>">
                <a href="metrica_atencion.php<?php echo isset($_SESSION['dashboard_fecha']) ? '?fecha='.htmlspecialchars($_SESSION['dashboard_fecha']) : ''; ?>">
                    <i class="fas fa-headset"></i>
                    <span>Tasa de Atención</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'metrica_oportunidad.php' ? 'active' : ''; ?>">
                <a href="metrica_oportunidad.php<?php echo isset($_SESSION['dashboard_fecha']) ? '?fecha='.htmlspecialchars($_SESSION['dashboard_fecha']) : ''; ?>">
                    <i class="fas fa-clock"></i>
                    <span>Tasa de Oportunidad</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'metrica_abandono.php' ? 'active' : ''; ?>">
                <a href="metrica_abandono.php<?php echo isset($_SESSION['dashboard_fecha']) ? '?fecha='.htmlspecialchars($_SESSION['dashboard_fecha']) : ''; ?>">
                    <i class="fas fa-user-slash"></i>
                    <span>Tasa de Abandono</span>
                </a>
            </li>
        </ul>
        
        <!-- Separador -->
        <div class="sidebar-divider">
            <span>ACCOUNT PAGES</span>
        </div>
        
        <!-- Menú de cuenta -->
        <ul class="sidebar-menu">
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <a href="profile.php">
                    <i class="fas fa-user"></i>
                    <span>Perfil</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Botón de logout -->
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</div>