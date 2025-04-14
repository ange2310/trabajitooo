<?php
// includes/conexion_api.php

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

function login_api($username, $password) {
    global $config;
    
    // Para desarrollo, usamos credenciales de prueba
    // En producción, descomentar este bloque para conectar con la API real
    /*
    // Inicializar cURL
    $ch = curl_init($config['api_url'] . '/auth/login');
    
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
        error_log('Error de cURL al conectar con API (login): ' . curl_error($ch));
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
        error_log('Error HTTP al conectar con API (login). Código: ' . $status);
        return [
            'error' => true,
            'message' => 'Error del servidor: ' . $status
        ];
    }
    
    // Decodificar respuesta JSON
    $data = json_decode($response, true);
    
    // Verificar si la decodificación tuvo éxito
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('Error al decodificar JSON de la API (login): ' . json_last_error_msg());
        return [
            'error' => true,
            'message' => 'Error al procesar la respuesta del servidor'
        ];
    }
    
    // Devolver los datos
    return $data;
    */
    

        // Llamar a la función de desarrollo durante las pruebas
        include_once 'datos_prueba.php';
        return login_api_dev($username, $password);
    
}
?>
