<!-- includes/sidebar.php - Menú lateral del dashboard -->
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
                <a href="index.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'tables.php' ? 'active' : ''; ?>">
                <a href="tables.php">
                    <i class="fas fa-table"></i>
                    <span>Tables</span>
                </a>
            </li>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'rtl.php' ? 'active' : ''; ?>">
                <a href="rtl.php">
                    <i class="fas fa-align-right"></i>
                    <span>RTL</span>
                </a>
            </li>
        </ul>
        
        <!-- Separador -->
        <div class="sidebar-divider">
            <span>ACCOUNTPAGES</span>
        </div>
        
        <!-- Menú de cuenta -->
        <ul class="sidebar-menu">
            <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <a href="profile.php">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Botón de logout -->
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>