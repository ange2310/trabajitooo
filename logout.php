<?php
session_start();
session_unset();      // Limpiar todas las variables de sesión
session_destroy();    // Destruir la sesión
session_write_close(); // Asegurar que se escriben los cambios

// Eliminar la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirigir a login
header('Location: login.php');
exit;