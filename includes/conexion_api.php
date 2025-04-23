<?php
/**
 * permite que tu aplicación frontend se comunique con el servidor de la API, manejando toda la complejidad de autenticación, 
 * procesamiento de respuestas y gestión de errores.
 */
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../includes/get_metrics.php');


/**
 * 1. Procesa solicitudes AJAX entrantes identificadas por el parámetro 'action'.
 * Para la acción 'hourly_stats', detecta la fecha desde múltiples fuentes posibles.
 * Obtiene y procesa estadísticas de chat por hora mediante funciones especializadas.
 * Devuelve datos formateados en JSON para alimentar los gráficos del dashboard.
 */
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    switch ($_GET['action']) {
        case 'hourly_stats':
            // Obtener fecha del parámetro GET 
            $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
            
            // Registrar la fecha recibida para depuración
            error_log("hourly_stats: Fecha solicitada: " . $fecha);

            // Analizar el referer para ver qué fecha contiene
            if (!empty($_SERVER['HTTP_REFERER'])) {
                $referer_params = [];
                parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $referer_params);
                error_log("hourly_stats: Referer params: " . print_r($referer_params, true));
            }
            
            // Intentar obtener la fecha de múltiples fuentes
            $fecha_get = isset($_GET['fecha']) ? $_GET['fecha'] : 'no_fecha_get';
            $fecha_referer = isset($referer_params['fecha']) ? $referer_params['fecha'] : 'no_fecha_referer';
            $fecha_default = date('Y-m-d');
            
            // Usar la fecha más confiable disponible
            $fecha = !empty($fecha_get) && $fecha_get !== 'no_fecha_get' ? $fecha_get : 
                   (!empty($fecha_referer) && $fecha_referer !== 'no_fecha_referer' ? $fecha_referer : $fecha_default);
            
            error_log("hourly_stats: Fecha final utilizada: " . $fecha);
            
            try {
                // Pasar la misma fecha como start_date y end_date, y 'hour' como group_by
                $datos = obtener_estadisticas_chat($fecha, $fecha, 'hour');
                
                // Registrar los datos recibidos para depuración
                error_log("hourly_stats: Datos obtenidos: " . print_r($datos, true));
                
                // Procesar datos y devolver respuesta
                $datos_procesados = procesar_datos_grafico_horas($datos);
                
                // Si no hay datos, generar datos vacíos con estructura correcta
                if (empty($datos_procesados) || !isset($datos_procesados['labels']) || !isset($datos_procesados['values'])) {
                    $datos_procesados = [
                        'labels' => array_map(function($h) { return sprintf("%02d:00", $h); }, range(0, 23)),
                        'values' => array_fill(0, 24, 0)
                    ];
                }
                
                // Verificar que la respuesta JSON sea válida antes de enviarla
                $json_response = json_encode($datos_procesados);
                if ($json_response === false) {
                    // Si hay un error en la codificación JSON, registrarlo y devolver datos vacíos
                    error_log("hourly_stats: Error en json_encode: " . json_last_error_msg());
                    $json_response = json_encode([
                        'labels' => array_map(function($h) { return sprintf("%02d:00", $h); }, range(0, 23)),
                        'values' => array_fill(0, 24, 0)
                    ]);
                }
                
                echo $json_response;
                
            } catch (Exception $e) {
                error_log("hourly_stats: Excepción atrapada: " . $e->getMessage());
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

//2. Obtiene métricas generales del servidor
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

//3. Gestiona la autenticación con la API
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