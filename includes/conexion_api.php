<?php
function getMetricsFromAPI() {
    global $config;
    
    // Código para conectarse a la API externa usando el token
    $ch = curl_init($config['api_url'] . '/metrics');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $_SESSION['token'],
        "Content-Type: application/json"
    ]);
    
    $response = curl_exec($ch);
    // Procesar respuesta...
    
    return json_decode($response, true);
}
?>