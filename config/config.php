<?php
// No iniciar sesión aquí si ya se inició en otro lugar
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la API
$config = [
    'api_url' => 'https://chatdev.tpsalud.com:6999',
];

// Verificar autenticación
$current_page = basename($_SERVER['PHP_SELF']);
if ($current_page != 'login.php' && !isset($_SESSION['token'])) {
    header('Location: login.php');
    exit;
}
?>