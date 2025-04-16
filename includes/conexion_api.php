<?php

require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../includes/get_metrics.php');

if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    switch ($_GET['action']) {
        case 'hourly_stats':
            // Obtener fecha del parámetro GET explícitamente
            $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');


             // Analizar el referer para ver qué fecha contiene
            if (!empty($_SERVER['HTTP_REFERER'])) {
                $referer_params = [];
                parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $referer_params);
            }
            
            // Intentar obtener la fecha de múltiples fuentes
            $fecha_get = isset($_GET['fecha']) ? $_GET['fecha'] : 'no_fecha_get';
            $fecha_referer = isset($referer_params['fecha']) ? $referer_params['fecha'] : 'no_fecha_referer';
            $fecha_default = date('Y-m-d');
            
            // Usar la fecha más confiable disponible
            $fecha = !empty($fecha_get) && $fecha_get !== 'no_fecha_get' ? $fecha_get : 
                    (!empty($fecha_referer) && $fecha_referer !== 'no_fecha_referer' ? $fecha_referer : $fecha_default);
            
            try {
                // IMPORTANTE: Pasar la misma fecha como start_date y end_date, y 'hour' como group_by
                $datos = obtener_estadisticas_chat($fecha, $fecha, 'hour');
                
                // Procesar datos y devolver respuesta
                $datos_procesados = procesar_datos_grafico_horas($datos);
                
                echo json_encode($datos_procesados);
            } catch (Exception $e) {
                echo json_encode([
                    'error' => $e->getMessage(),
                    'labels' => array_map(function($h) { return sprintf("%02d:00", $h); }, range(0, 23)),
                    'values' => array_fill(0, 24, 0)
                ]);
            }
            break;
        
        default:
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
    
    exit;
}

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
        return [
            'error' => true,
            'message' => 'Error del servidor: ' . $status
        ];
    }
    
    // Decodificar respuesta JSON
    $data = json_decode($response, true);
    
    // Verificar si la decodificación tuvo éxito
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'error' => true,
            'message' => 'Error al procesar la respuesta del servidor'
        ];
    }
    
    // Devolver los datos
    return $data;
}

function login_api($username, $password) {
    global $config;
    // Inicializar cURL
    $ch = curl_init($config['api_url'] . '/login');
    
    // Configurar opciones cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    
    // Datos de login
    $postData = json_encode([
        'username' => $username,
        'password' => $password
    ]);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    
    // Timeout para evitar bloqueo indefinido
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    // Ejecutar solicitud
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Verificar si hubo un error
    if (curl_errno($ch)) {
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
        return [
            'error' => true,
            'message' => 'Error del servidor: ' . $status
        ];
    }
    
    // Decodificar respuesta JSON
    $data = json_decode($response, true);
    
    // Verificar si la decodificación tuvo éxito
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'error' => true,
            'message' => 'Error al procesar la respuesta del servidor'
        ];
    }
    
    // Devolver los datos
    return $data;
}

?>