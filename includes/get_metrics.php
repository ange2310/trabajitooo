<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/conexion_api.php');

// Agregar router para manejar acciones desde el frontend
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    switch ($_GET['action']) {
        case 'dashboard_metrics':
            $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
            try {
                $metricas_datos = obtener_metricas_dashboard($fecha);

                // Formatear datos como se espera en charts.js
                $response = [
                    'total_conversations_received' => $metricas_datos['total_conversations'] ?? 0,
                    'total_conversations_attended' => $metricas_datos['attended_conversations'] ?? 0,
                    'total_abandoned' => $metricas_datos['abandoned_conversations'] ?? 0,
                    'average_first_response_minutes' => $metricas_datos['average_response_time'] ?? 0,
                    'average_duration_minutes' => $metricas_datos['average_conversation_duration'] ?? 0,
                    'average_wait_minutes' => $metricas_datos['average_wait_time'] ?? 0,
                    'goal_achieved_count' => $metricas_datos['goals_achieved'] ?? 0,
                    'attendance_rate' => $metricas_datos['attention_rate'] ?? 0,
                    'opportunity_rate' => $metricas_datos['opportunity_rate'] ?? 0,
                    'abandonment_rate' => $metricas_datos['abandonment_rate'] ?? 0,
                ];

                echo json_encode($response);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        // AÑADIR ESTE NUEVO CASO PARA DATOS POR HORA
        case 'hourly_stats':
            $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
            $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
            
            try {
                $datos = obtener_estadisticas_chat($start_date, $end_date, 'hour');
                $datos_procesados = procesar_datos_grafico_horas($datos);
                
                echo json_encode($datos_procesados);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }

    exit; // Detener ejecución si es una acción
}

/**
 * Obtiene estadísticas de chat por periodo
 */
function obtener_estadisticas_chat($start_date = null, $end_date = null, $group_by = 'day') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Solo iniciar la sesión si no está activa
    }

    if (!isset($_SESSION['token'])) {
        header('location: login.php'); // Redirigir a la página de inicio de sesión si no hay token
        exit;
    }

    // Valores por defecto si no se proporcionan fechas
    if ($start_date === null) {
        $start_date = date('Y-m-d', strtotime('-7 days')); // 7 días atrás
    }
    if ($end_date === null) {
        $end_date = date('Y-m-d'); //Fecha actual
    }

    // URL DE LA API CON DATOS OPCIONALES
    $url = "https://chatdev.tpsalud.com:6999/chat_statistics?start_date=$start_date&end_date=$end_date&group_by=$group_by";

    // Obtener el token de sesión
    $token = $_SESSION['token'];

    // Verificar si el token está vacío o no está disponible
    if (empty($token)) {
        echo 'Error: El token de autenticación no está disponible.';
        return null;
    }

    // Config de headers, incluye el token de sesión
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    // Iniciar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if ($response === false) {
        // Manejar el error si curl falló
        echo 'Error en la solicitud: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Si el código HTTP es 401, muestra la respuesta completa
    if ($httpCode === 401) {
        echo 'Error de autenticación: ' . $response;
        return null;
    }

    // Verificar si la solicitud fue exitosa
    if ($httpCode === 200) {
        // Intentar decodificar la respuesta JSON
        $data = json_decode($response, true);

        // Verificar si hubo un error al decodificar el JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'Error al decodificar el JSON: ' . json_last_error_msg();
            return null;
        }

        return $data;
    } else {
        // Manejar el error si la respuesta no es 200
        echo 'Error HTTP: ' . $httpCode . ' Respuesta: ' . $response;
        return null;
    }
    // Agregar depuración para ver formato exacto de respuesta
    error_log('Datos recibidos de API: ' . json_encode($data));
    
    return $data;
}


/**
 * Procesa datos para gráficos
 */
function procesar_datos_grafico_horas($datos) { 
    $labels = [];
    $values = [];

    // Si los datos están vacíos, devolver arrays vacíos
    if (empty($datos) || !is_array($datos)) {
        error_log("Datos vacíos o inválidos en procesar_datos_grafico_horas()");
        return ['labels' => [], 'values' => []];
    }
    
    // Verificar si hay estadísticas
    if (isset($datos['statistics']) && is_array($datos['statistics'])) {
        foreach ($datos['statistics'] as $item) {
            if (isset($item['period']) && isset($item['total_chats'])) {
                // Extraer solo la hora del timestamp
                $hora = date('H:i', strtotime($item['period']));
                $labels[] = $hora;
                $values[] = (int)$item['total_chats'];
            }
        }
    } else {
        error_log("No se encontraron estadísticas en el formato esperado: " . json_encode(array_keys($datos)));
    }
    
    // Si no hay datos, crear un array de 24 horas con valores en 0
    if (empty($labels)) {
        for ($hour = 0; $hour < 24; $hour++) {
            $formattedHour = sprintf("%d:00", $hour);
            $labels[] = $formattedHour;
            $values[] = 0;
        }
    }
    
    return [
        'labels' => $labels,
        'values' => $values
    ];
}

function obtener_rendimiento_agente($start_date = null, $end_date = null, $agent_id = null, $agent_email = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Solo iniciar la sesión si no está activa
    }

    if (!isset($_SESSION['token'])) {
        header('location: login.php'); // Redirigir a la página de inicio de sesión si no hay token
        exit;
    }

    // Valores por defecto si no se proporcionan fechas
    if ($start_date === null) {
        $start_date = date('Y-m-d', strtotime('-7 days')); // 7 días atrás
    }
    if ($end_date === null) {
        $end_date = date('Y-m-d'); //Fecha actual
    }

    // URL DE LA API CON DATOS OPCIONALES
    $url = "https://chatdev.tpsalud.com:6999/agent_performance?start_date=$start_date&end_date=$end_date";

    // Si se proporciona un ID de agente, agregarlo a la URL
    if ($agent_id !== null) {
        $url .= "&agent_id=$agent_id";
    } elseif ($agent_email !== null) {
        $url .= "&agent_email=$agent_email";
    }

    // Obtener el token de sesión
    $token = $_SESSION['token'];

    // Verificar si el token está vacío o no está disponible
    if (empty($token)) {
        echo 'Error: El token de autenticación no está disponible.';
        return null;
    }

    // Config de headers, incluye el token de sesión
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    // Iniciar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if ($response === false) {
        // Manejar el error si curl falló
        echo 'Error en la solicitud: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($httpCode === 401) {
        echo 'Error de autenticación: ' . $response;
        return null;
    }

    // Verificar si la solicitud fue exitosa
    if ($httpCode === 200) {
        // Intentar decodificar la respuesta JSON
        $data = json_decode($response, true);

        // Verificar si hubo un error al decodificar el JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'Error al decodificar el JSON: ' . json_last_error_msg();
            return null;
        }

        return $data;
    } else {
        // Manejar el error si la respuesta no es 200
        echo 'Error HTTP: ' . $httpCode . ' Respuesta: ' . $response;
        return null;
    }


}



function obtener_configuracion_dashboard() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['token'])) {
        header('location: login.php');
        exit;
    }

    // URL del endpoint
    $url = "https://chatdev.tpsalud.com:6999/dashboard_config";

    // Obtener el token de sesión
    $token = $_SESSION['token'];

    // Verificar si el token está disponible
    if (empty($token)) {
        return [
            'error' => true,
            'message' => 'El token de autenticación no está disponible'
        ];
    }

    // Configurar headers con el token
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    // Iniciar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        error_log('Error de cURL: ' . curl_error($ch));
        curl_close($ch);
        return [
            'error' => true,
            'message' => 'Error de conexión con el servidor'
        ];
    }
    
    curl_close($ch);

    // Verificar respuesta HTTP
    if ($httpCode !== 200) {
        error_log('Error HTTP: ' . $httpCode);
        return [
            'error' => true,
            'message' => 'Error del servidor: ' . $httpCode
        ];
    }

    // Decodificar JSON
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Error al decodificar JSON: ' . json_last_error_msg());
        return [
            'error' => true,
            'message' => 'Error al procesar la respuesta del servidor'
        ];
    }

    return $data;
}

function actualizar_configuracion_dashboard($config = []) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['token'])) {
        header('location: login.php');
        exit;
    }

    $url = "https://chatdev.tpsalud.com:6999/dashboard_config";
    $token = $_SESSION['token'];

    if (empty($token)) {
        return ['error' => true, 'message' => 'Token vacío'];
    }

    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    $payload = json_encode($config);


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        curl_close($ch);
        return ['error' => true, 'message' => 'Error de conexión'];
    }

    curl_close($ch);

    if ($httpCode !== 200) {
        return ['error' => true, 'message' => 'Error del servidor: ' . $httpCode];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => true, 'message' => 'Respuesta inválida'];
    }

    return $data;
}

function obtener_metricas_dashboard($date = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Solo iniciar la sesión si no está activa
    }

    if (!isset($_SESSION['token'])) {
        header('location: login.php'); // Redirigir a la página de inicio de sesión si no hay token
        exit;
    }

    if ($date === null) {
        $date = date('Y-m-d'); // Fecha actual
    }

    $url = "https://chatdev.tpsalud.com:6999/dashboard_metrics?date=$date";
    $token = $_SESSION['token'];

    if (empty($token)) {
        $msg = 'Error: El token de autenticación no está disponible.';
        echo $msg;
        error_log($msg);
        return null;
    }

    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if ($response === false) {
        $msg = 'Error en la solicitud: ' . curl_error($ch);
        echo $msg;
        error_log($msg);
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 401) {
        $msg = 'Error de autenticación: ' . $response;
        echo $msg;
        error_log($msg);
        return null;
    }

    if ($httpCode === 200) {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $msg = 'Error al decodificar el JSON: ' . json_last_error_msg();
            echo $msg;
            error_log($msg);
            return null;
        };

        return $data;
    } else {
        $msg = 'Error HTTP: ' . $httpCode . ' Respuesta: ' . $response;
        echo $msg;
        error_log($msg);
        return null;
    }
}

function procesar_metricas($metricas){
    return $metricas;
}

?>