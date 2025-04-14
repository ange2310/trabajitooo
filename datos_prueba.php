<?php
// Este archivo es solo para fines de prueba durante el desarrollo
// ¡ELIMINAR EN PRODUCCIÓN!

// Simular respuesta de la API de autenticación
function login_api_dev($username, $password) {
    // Credenciales de prueba (para desarrollo)
    $valid_users = [
        'admin' => 'admin123',
        'usuario' => 'password'
    ];
    
    // Verificar credenciales
    if (array_key_exists($username, $valid_users) && $valid_users[$username] === $password) {
        // Simular respuesta exitosa de API
        return [
            'access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyLCJyb2xlIjoiYWRtaW4ifQ.SflKxwRJSMeKKF2QT4fwpMeJf36POk6yJV_adQssw5c',
            'token_type' => 'bearer',
            'user' => [
                'id' => 1,
                'username' => $username,
                'name' => 'Usuario Demo',
                'role' => 'admin'
            ]
        ];
    }
    
    // Simular error
    return [
        'error' => true,
        'message' => 'Credenciales incorrectas'
    ];
}

// Para usarlo, modificar temporalmente la función login_api en conexion_api.php
// reemplazando su contenido con:
/*
function login_api($username, $password) {
    // Llamar a la función de desarrollo durante las pruebas
    include_once 'datos_prueba.php';
    return login_api_dev($username, $password);
}
*/