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
if ($metrics && !isset($metrics['error']) && (isset($metrics['metrics']) || count($metrics) > 0)) {
    // Extraer los datos de métricas si vienen anidados
    $metricsData = isset($metrics['metrics']) ? $metrics['metrics'] : $metrics;
    
    // Añadir la fecha como parte de la respuesta
    $metricsData['date'] = $date;
    
    // Enviar datos como respuesta JSON
    echo json_encode($metricsData);
} else {
    // Si no hay datos, devolver valores predeterminados
    $defaultMetrics = [
        'date' => $date,
        'total_conversations_received' => 0,
        'total_conversations_attended' => 0,
        'total_abandoned' => 0,
        'average_wait_minutes' => 0,
        'average_first_response_minutes' => 0,
        'average_duration_minutes' => 0,
        'goal_achieved_count' => 0,
        'attendance_rate' => 0,
        'opportunity_rate' => 0,
        'abandonment_rate' => 0
    ];
    
    echo json_encode($defaultMetrics);
}
?>