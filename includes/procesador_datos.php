<?php
require_once 'get_metrics.php';

/**
 * Obtiene y procesa los datos del dashboard desde la API
 *
 * @return array Datos formateados para el dashboard
 */
function getDashboardData() {
    // Obtener datos crudos de la API
    $raw_data = getMetricsFromAPI();
    
    // Verificar si hay errores
    if (isset($raw_data['error'])) {
        return $raw_data; // Devolver el error tal cual
    }
    
    // Procesar datos para el dashboard
    $dashboard_data = [
        'metrics' => [
            'attention' => [
                'title' => 'Tasa de Atención',
                'value' => isset($raw_data['attention_rate']) ? floatval($raw_data['attention_rate']) * 100 : 0,
                'color1' => '#9933ff',
                'color2' => '#3366ff'
            ],
            'opportunity' => [
                'title' => 'Tasa de Oportunidad',
                'value' => isset($raw_data['opportunity_rate']) ? floatval($raw_data['opportunity_rate']) * 100 : 0,
                'color1' => '#ffcc00',
                'color2' => '#ff9900'
            ],
            'abandonment' => [
                'title' => 'Tasa de Abandono',
                'value' => isset($raw_data['abandonment_rate']) ? floatval($raw_data['abandonment_rate']) * 100 : 0,
                'color1' => '#ff3366',
                'color2' => '#ff0000'
            ]
        ],
        'conversations' => [
            'total' => isset($raw_data['total_conversations']) ? intval($raw_data['total_conversations']) : 0,
            'attended' => isset($raw_data['attended_conversations']) ? intval($raw_data['attended_conversations']) : 0,
            'percentage' => isset($raw_data['attendance_percentage']) ? floatval($raw_data['attendance_percentage']) * 100 : 0
        ],
        'timeMetrics' => [
            'waitTime' => isset($raw_data['average_wait_time']) ? floatval($raw_data['average_wait_time']) : 0,
            'responseTime' => isset($raw_data['average_response_time']) ? floatval($raw_data['average_response_time']) : 0,
            'conversationDuration' => isset($raw_data['average_conversation_duration']) ? floatval($raw_data['average_conversation_duration']) : 0
        ],
        'performanceMetrics' => [
            'goalsAchieved' => [
                'count' => isset($raw_data['goals_achieved']) ? intval($raw_data['goals_achieved']) : 0,
                'total' => isset($raw_data['total_goals']) ? intval($raw_data['total_goals']) : 0,
                'percentage' => isset($raw_data['goals_achieved']) && isset($raw_data['total_goals']) && $raw_data['total_goals'] > 0 
                    ? ($raw_data['goals_achieved'] / $raw_data['total_goals']) * 100 
                    : 0
            ],
            'abandonedConversations' => [
                'count' => isset($raw_data['abandoned_conversations']) ? intval($raw_data['abandoned_conversations']) : 0,
                'total' => isset($raw_data['total_conversations']) ? intval($raw_data['total_conversations']) : 0,
                'percentage' => isset($raw_data['abandonment_rate']) ? floatval($raw_data['abandonment_rate']) * 100 : 0
            ]
        ],
        'hourlyData' => processHourlyData($raw_data)
    ];
    
    return $dashboard_data;
}

/**
 * Procesa los datos por hora de las conversaciones
 *
 * @param array $raw_data Datos crudos de la API
 * @return array Datos procesados por hora
 */
function processHourlyData($raw_data) {
    // Si no hay datos por hora, devuelve datos de muestra
    if (!isset($raw_data['hourly_conversations']) || !is_array($raw_data['hourly_conversations'])) {
        // Datos de muestra para desarrollo
        return [
            ['hour' => '4:00 AM', 'count' => 2],
            ['hour' => '5:00 AM', 'count' => 3],
            ['hour' => '6:00 AM', 'count' => 5],
            ['hour' => '7:00 AM', 'count' => 8],
            ['hour' => '8:00 AM', 'count' => 10],
            ['hour' => '9:00 AM', 'count' => 9],
            ['hour' => '10:00 AM', 'count' => 7],
            ['hour' => '11:00 AM', 'count' => 8],
            ['hour' => '12:00 PM', 'count' => 10],
            ['hour' => '1:00 PM', 'count' => 9],
            ['hour' => '2:00 PM', 'count' => 7],
            ['hour' => '3:00 PM', 'count' => 8],
            ['hour' => '4:00 PM', 'count' => 9],
            ['hour' => '5:00 PM', 'count' => 6],
            ['hour' => '6:00 PM', 'count' => 5],
            ['hour' => '7:00 PM', 'count' => 4],
            ['hour' => '8:00 PM', 'count' => 3],
            ['hour' => '9:00 PM', 'count' => 2],
            ['hour' => '10:00 PM', 'count' => 1]
        ];
    }
    
    $hourly_data = [];
    
    // Procesar datos por hora
    foreach ($raw_data['hourly_conversations'] as $hour => $count) {
        $hourly_data[] = [
            'hour' => formatHour($hour),
            'count' => intval($count)
        ];
    }
    
    // Ordenar por hora
    usort($hourly_data, function($a, $b) {
        return strtotime(substr($a['hour'], 0, -3)) <=> strtotime(substr($b['hour'], 0, -3));
    });
    
    return $hourly_data;
}

/**
 * Formatea una hora en formato 12 horas con AM/PM
 *
 * @param int $hour Hora en formato 24 horas
 * @return string Hora formateada
 */
function formatHour($hour) {
    // Asegurar que la hora sea un número
    $hour = intval($hour);
    
    // Formato 12 horas con AM/PM
    if ($hour == 0) {
        return "12:00 AM";
    } elseif ($hour < 12) {
        return $hour . ":00 AM";
    } elseif ($hour == 12) {
        return "12:00 PM";
    } else {
        return ($hour - 12) . ":00 PM";
    }
}
?>