<?php
require_once 'config/config.php';
require_once 'includes/conexion_api.php';
// includes/get_metrics.php
// Funciones para obtener y procesar métricas

/**
 * Obtiene las métricas para el dashboard
 * 
 * @param string $fecha Fecha para la cual obtener las métricas
 * @return array Datos procesados para el dashboard
 */
function obtener_metricas($fecha = null) {
    // Para fines de desarrollo y evitar el timeout, generamos datos de muestra
    // En producción, se conectaría con la API real
    
    // Datos de muestra para el dashboard
    return [
        'atencion' => 85.7,
        'oportunidad' => 92.3,
        'abandono' => 7.8,
        'tiempo_espera' => 2.5,
        'tiempo_respuesta' => 1.2,
        'duracion_conversacion' => 8.7,
        'conversaciones_recibidas' => 345,
        'conversaciones_atendidas' => 296,
        'objetivos_cantidad' => '42/50',
        'objetivos_porcentaje' => 84,
        'abandonadas_cantidad' => '27/345',
        'total_chats' => 345
    ];
}

/**
 * Procesa las métricas aplicando reglas de negocio
 * 
 * @param array $metricas Métricas crudas
 * @return array Métricas procesadas
 */
function procesar_metricas($metricas) {
    // Por simplicidad, devolvemos las mismas métricas sin cambios
    return $metricas;
}

/**
 * Obtiene la configuración del dashboard
 * 
 * @return array Configuración del dashboard
 */
function obtener_config_dashboard() {
    // Simulamos una configuración
    return [
        'refresh_rate' => 60, // segundos
        'goal_targets' => [
            'atencion' => 90,
            'oportunidad' => 85,
            'abandono' => 10
        ]
    ];
}

/**
 * Obtiene estadísticas de chat por periodo
 */
function obtener_estadisticas_chat($start_date = null, $end_date = null, $group_by = 'day') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start(); // Solo iniciar la sesión si no está activa
    }

    if (!isset($_SESSION['token'])) {
        ('location: login.php'); // Redirigir a la página de inicio de sesión si no hay token
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
}


/**
 * Procesa datos para gráficos
 * 
 * @param array $datos Datos crudos 
 * @return array Datos procesados para gráficos
 */
function procesar_datos_grafico_horas($datos) { 
    $labels = [];
    $values = [];

    // Acceder al array de estadísticas
    if (!isset($datos['statistics'])) {
        echo "Error: No se encontraron estadísticas.";
        return ['labels' => [], 'values' => []];
    }

    foreach ($datos['statistics'] as $item) {
        if (!isset($item['period']) || !isset($item['total_chats'])) {
            echo "Error: El item no tiene los índices \"period\" o \"total_chats\".";
            continue;
        }

        // Extraer solo la hora del campo 'period'
        $hora = date('H:i', strtotime($item['period']));

        $labels[] = $hora;
        $values[] = $item['total_chats'];
    }
    
    return [
        'labels' => $labels,
        'values' => $values
    ];
}


/**
 * Obtiene el rendimiento por agente
 * 
 * @param string $inicio Fecha inicio
 * @param string $fin Fecha fin
 * @param string $agent_email Email del agente (opcional)
 * @return array Datos de rendimiento
 */
function obtener_rendimiento_agente($inicio, $fin, $agent_email = null) {
    // Datos de muestra
    $agentes = [
        [
            'agent_name' => 'Ana García',
            'chats_received' => 345,
            'chats_attended' => 310,
            'avg_response_time' => 1.8,
            'avg_duration' => 10.5,
            'rating' => 4.7
        ],
        [
            'agent_name' => 'Carlos Martínez',
            'chats_received' => 298,
            'chats_attended' => 265,
            'avg_response_time' => 2.1,
            'avg_duration' => 8.9,
            'rating' => 4.5
        ],
        [
            'agent_name' => 'Laura Rodríguez',
            'chats_received' => 312,
            'chats_attended' => 290,
            'avg_response_time' => 1.5,
            'avg_duration' => 9.8,
            'rating' => 4.8
        ]
    ];
    
    // Si se especificó un agente, filtramos los resultados
    if ($agent_email !== null && $agent_email !== '') {
        return [$agentes[0]];
    }
    
    return $agentes;
}
?>