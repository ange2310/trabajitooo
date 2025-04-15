<?php
// Establecer encabezado para JSON
header('Content-Type: application/json');

// Incluir el archivo necesario
require_once(__DIR__ . '/get_metrics.php');

// Capturar la salida para que no interfiera con la respuesta JSON
ob_start();
$date = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$metrics = obtener_metricas_dashboard($date);
ob_end_clean();

// Verificar que tenemos datos válidos
if ($metrics && isset($metrics['metrics'])) {
    // Mostrar en el log para depuración
    error_log("Enviando datos al frontend: " . print_r($metrics['metrics'], true));
    
    // Enviar directamente los datos de metrics como respuesta JSON
    echo json_encode($metrics['metrics']);
} else {
    // Si no hay datos, devolver valores predeterminados
    echo json_encode([
        'attendance_rate' => 0,
        'opportunity_rate' => 0,
        'abandonment_rate' => 0,
        'total_conversations_received' => 0,
        'total_conversations_attended' => 0,
        'total_abandoned' => 0,
        'average_wait_minutes' => 0,
        'average_first_response_minutes' => 0,
        'average_duration_minutes' => 0
    ]);
}
?>