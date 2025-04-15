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
        $response = [
            'access_token' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZXhwIjoxNzQ0NzM3MDk0fQ.8hIAJinj5PK8SShcyhScYI4HsuMUws2fqkP176AFcFk',
            'token_type' => 'bearer',
            'user' => [
                'id' => 1,
                'username' => $username,
                'name' => 'Usuario Demo',
                'role' => 'admin'
            ]
        ];

        // Iniciar la sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Guardar el token en la sesión
        $_SESSION['token'] = $response['access_token'];

        // Retornar la respuesta con éxito
        return $response;
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