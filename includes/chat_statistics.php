<?php
header('Content-Type: application/json');

// Incluir el archivo principal que contiene las funciones
require_once 'get_metrics.php';

// Obtener parámetros de la URL
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$group_by = isset($_GET['group_by']) ? $_GET['group_by'] : 'hour';

// Llamar a la función para obtener estadísticas
$stats = obtener_estadisticas_chat($start_date, $end_date, $group_by);

// Verificar si se obtuvieron datos
if ($stats && isset($stats['data'])) {
    // Devolver los datos en formato JSON
    echo json_encode($stats);
} else {
    // Devolver un objeto vacío en caso de error
    echo json_encode(['error' => 'No se pudieron obtener estadísticas de chat']);
}
?>