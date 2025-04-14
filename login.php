<?php
session_start();
require_once 'config/config.php';

$error = '';
$success = '';
//verificar si ya hay una sesión activa
if(isset($_SESSION['token'])){
    header('location: index.php');
    exit;
}
// Si ya está logueado redirigir al dashboard
if (isset($_SESSION['token']) && !empty($_SESSION['token'])) {
    header('Location: index.php');
    exit;
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/conexion_api.php';
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $resultado = login_api($username, $password);
        
        if (isset($resultado['access_token'])) {
            // Guardar token en sesión
            $_SESSION['token'] = $resultado['access_token'];
            $_SESSION['username'] = $username;
            
            // Redirigir al dashboard
            header('Location: index.php');
            exit;
        } else {
            $error_msg = 'Credenciales incorrectas o error de conexión';
        }
    } else {
        $error_msg = 'Por favor ingrese usuario y contraseña';
    }
}

// Incluir el header
include_once 'includes/header.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard de Métricas</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        /* Estilos específicos para la página de login */
        .login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #1a1e2c;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            background-color: #262a38;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo svg {
            width: 60px;
            height: 60px;
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 30px;
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
        }
        
        .error-message, 
        .success-message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .error-message {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border-left: 4px solid #f44336;
        }
        
        .success-message {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4caf50;
            border-left: 4px solid #4caf50;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #ffffff;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #373d49;
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #4169E1;
            box-shadow: 0 0 0 3px rgba(65, 105, 225, 0.2);
        }
        
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 12px 15px;
            font-size: 16px;
            line-height: 1.5;
            border-radius: 6px;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }
        
        .btn-primary {
            color: #ffffff;
            background-color: #4169E1;
            border-color: #4169E1;
        }
        
        .btn-primary:hover {
            background-color: #3555b9;
            border-color: #3150ab;
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: #9ba3af;
            font-size: 14px;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-logo">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="#4169E1"/>
                <path d="M2 17L12 22L22 17" stroke="#4169E1" stroke-width="2"/>
                <path d="M2 12L12 17L22 12" stroke="#4169E1" stroke-width="2"/>
            </svg>
        </div>
        
        <h1 class="login-title">Acceso al Dashboard</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="username" class="form-label">Usuario</label>
                <input type="text" id="username" name="username" class="form-control" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                       placeholder="Ingrese su nombre de usuario" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Ingrese su contraseña" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </div>
        </form>
        
        <div class="form-footer">
            Sistema de Monitoreo y Análisis de Conversaciones
        </div>
    </div>
</body>
</html>