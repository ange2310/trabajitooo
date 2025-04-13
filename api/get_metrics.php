
<?php
require_once '../config/config.php';
require_once '../includes/data_processor.php';

// Verificar si es una petición AJAX y si hay un token válido
if (isset($_SESSION['token'])) {
    // Obtener datos frescos
    $dashboard_data = getDashboardData();
    
    // Devolver como JSON
    header('Content-Type: application/json');
    echo json_encode($dashboard_data);
} else {
    // No autorizado
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
}
?>