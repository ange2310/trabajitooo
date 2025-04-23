<?php
/**
 * Proporciona métricas de dashboard en formato JSON
 * Sirve como punto de acceso para obtener métricas del dashboard
 * con base en una fecha específica. Devuelve una respuesta JSON con los valores
 * de las métricas o valores predeterminados si no hay datos disponibles.
 */

// Asegurar que no se muestre ningún error en la salida
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Establecer encabezado para JSON
header('Content-Type: application/json');

try {
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
} catch (Exception $e) {
    // Registrar el error pero no mostrarlo
    error_log("Error en dashboard_metrics.php: " . $e->getMessage());
    
    // Devolver un error en formato JSON
    echo json_encode([
        'error' => true,
        'message' => 'Error interno al obtener métricas',
        'date' => isset($date) ? $date : date('Y-m-d')
    ]);
}

exit;
?>