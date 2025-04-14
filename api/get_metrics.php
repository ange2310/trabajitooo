<?php
require_once '../config/config.php';
require_once '../includes/data_processor.php';

// Verificar si es una petición AJAX y si hay un token válido
if (isset($_SESSION['token'])) {
    // Función para obtener datos desde la API externa
    function getMetricsFromAPI() {
        global $config;
        
        // Inicializar cURL
        $ch = curl_init($config['api_url'] . '/metrics');
        
        // Configurar opciones cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $_SESSION['token'],
            "Content-Type: application/json"
        ]);
        
        // Timeout para evitar bloqueo indefinido
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        // Ejecutar solicitud
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Verificar si hubo un error
        if (curl_errno($ch)) {
            error_log('Error de cURL al conectar con API: ' . curl_error($ch));
            curl_close($ch);
            return [
                'error' => true,
                'message' => 'Error de conexión con el servidor'
            ];
        }
        
        // Cerrar conexión cURL
        curl_close($ch);
        
        // Verificar respuesta HTTP
        if ($status !== 200) {
            error_log('Error HTTP al conectar con API. Código: ' . $status);
            return [
                'error' => true,
                'message' => 'Error del servidor: ' . $status
            ];
        }
        
        // Decodificar respuesta JSON
        $data = json_decode($response, true);
        
        // Verificar si la decodificación tuvo éxito
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('Error al decodificar JSON de la API: ' . json_last_error_msg());
            return [
                'error' => true,
                'message' => 'Error al procesar la respuesta del servidor'
            ];
        }
        
        // Devolver los datos
        return $data;
    }

    // Obtener datos frescos desde la API
    $api_data = getMetricsFromAPI();
    
    // Procesar los datos para el dashboard
    if (isset($api_data['error'])) {
        $dashboard_data = $api_data; // Devolver el error tal cual
    } else {
        // Datos procesados para el dashboard
        $dashboard_data = [
            'metrics' => [
                'attention' => [
                    'title' => 'Tasa de Atención',
                    'value' => isset($api_data['attention_rate']) ? floatval($api_data['attention_rate']) * 100 : 0,
                    'color1' => '#9933ff',
                    'color2' => '#3366ff'
                ],
                'opportunity' => [
                    'title' => 'Tasa de Oportunidad',
                    'value' => isset($api_data['opportunity_rate']) ? floatval($api_data['opportunity_rate']) * 100 : 0,
                    'color1' => '#ffcc00',
                    'color2' => '#ff9900'
                ],
                'abandonment' => [
                    'title' => 'Tasa de Abandono',
                    'value' => isset($api_data['abandonment_rate']) ? floatval($api_data['abandonment_rate']) * 100 : 0,
                    'color1' => '#ff3366',
                    'color2' => '#ff0000'
                ]
            ],
            'conversations' => [
                'total' => isset($api_data['total_conversations']) ? intval($api_data['total_conversations']) : 0,
                'attended' => isset($api_data['attended_conversations']) ? intval($api_data['attended_conversations']) : 0,
                'percentage' => isset($api_data['attendance_percentage']) ? floatval($api_data['attendance_percentage']) * 100 : 0
            ],
            'timeMetrics' => [
                'waitTime' => isset($api_data['average_wait_time']) ? floatval($api_data['average_wait_time']) : 0,
                'responseTime' => isset($api_data['average_response_time']) ? floatval($api_data['average_response_time']) : 0,
                'conversationDuration' => isset($api_data['average_conversation_duration']) ? floatval($api_data['average_conversation_duration']) : 0
            ],
            'performanceMetrics' => [
                'goalsAchieved' => [
                    'count' => isset($api_data['goals_achieved']) ? intval($api_data['goals_achieved']) : 0,
                    'total' => isset($api_data['total_goals']) ? intval($api_data['total_goals']) : 0,
                    'percentage' => isset($api_data['goals_achieved']) && isset($api_data['total_goals']) && $api_data['total_goals'] > 0 
                        ? ($api_data['goals_achieved'] / $api_data['total_goals']) * 100 
                        : 0
                ],
                'abandonedConversations' => [
                    'count' => isset($api_data['abandoned_conversations']) ? intval($api_data['abandoned_conversations']) : 0,
                    'total' => isset($api_data['total_conversations']) ? intval($api_data['total_conversations']) : 0,
                    'percentage' => isset($api_data['abandonment_rate']) ? floatval($api_data['abandonment_rate']) * 100 : 0
                ]
            ],
            'hourlyData' => processHourlyData($api_data)
        ];
    }
    
    // Función para procesar los datos por hora
    function processHourlyData($api_data) {
        // Si no hay datos por hora, devuelve datos de muestra
        if (!isset($api_data['hourly_conversations']) || !is_array($api_data['hourly_conversations'])) {
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
        foreach ($api_data['hourly_conversations'] as $hour => $count) {
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
    
    // Función para formatear la hora
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
    
    // Devolver como JSON
    header('Content-Type: application/json');
    echo json_encode($dashboard_data);
} else {
    // No autorizado
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
}
?>