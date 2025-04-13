<?php
session_start();

// Destruir la sesión actual
session_destroy();

// Redireccionar al login
header('Location: login.php');
exit;
?>