<?php
require_once 'config/config.php';
require_once 'includes/data_processor.php';
require_once 'includes/dashboard_elements.php';

// Obtener datos procesados
$dashboard_data = getDashboardData();
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="dashboard-container">
    <div class="dashboard-grid">
        <?php 
        // Generar cada widget del dashboard usando los datos procesados
        echo createCircularWidget('Tasa de Atención', $dashboard_data['metrics']['attention']['value'], '#9933ff', '#3366ff');
        // Más widgets...
        ?>
    </div>
</main>

<!-- Pasar datos a JavaScript -->
<script>
    const dashboardData = <?php echo json_encode($dashboard_data); ?>;
</script>

<?php include 'includes/footer.php'; ?>