<?php
session_start();
require_once 'config/config.php';

$error_msg = '';
$success = '';

//verificar si ya hay una sesión activa
if(isset($_SESSION['token'])){
    header('location: index.php');
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

// Incluir el header específico para login (sin sidebar)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard de Métricas</title>
    
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #f8fafc;
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 40px;
            background-color: rgba(15, 23, 42, 0.8);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo svg {
            width: 70px;
            height: 70px;
            filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.5));
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 30px;
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: -0.025em;
        }
        
        .error-message, 
        .success-message {
            margin-bottom: 25px;
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .error-message {
            background-color: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-left: 4px solid #ef4444;
        }
        
        .success-message {
            background-color: rgba(16, 185, 129, 0.2);
            color: #6ee7b7;
            border-left: 4px solid #10b981;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #e2e8f0;
        }
        
        .form-control {
            width: 100%;
            padding: 15px;
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 8px;
            background-color: rgba(30, 41, 59, 0.5);
            color: #f8fafc;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25), inset 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        
        .form-control::placeholder {
            color: #94a3b8;
        }
        
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 15px;
            font-size: 16px;
            line-height: 1.5;
            border-radius: 8px;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary {
            color: #ffffff;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            border-color: #4f46e5;
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.25);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 10px rgba(59, 130, 246, 0.3);
        }
        
        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 50%;
            transform: scale(0) translate(-50%, -50%);
            transform-origin: left top;
            opacity: 0;
        }
        
        .btn-primary:active::after {
            animation: ripple 0.6s ease-out;
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0) translate(-50%, -50%);
                opacity: 1;
            }
            100% {
                transform: scale(20) translate(-50%, -50%);
                opacity: 0;
            }
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 30px;
            color: #94a3b8;
            font-size: 14px;
            font-weight: 400;
        }
        
        /* Efecto de brillo en el borde del container */
        .login-container::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #4f46e5, #3b82f6, #60a5fa, #4f46e5);
            z-index: -1;
            filter: blur(10px);
            opacity: 0.5;
            border-radius: 18px;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .login-container {
                padding: 25px;
            }
            
            .login-title {
                font-size: 24px;
            }
            
            .form-control, .btn {
                padding: 12px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-logo">
            <svg width="70" height="70" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 7L12 12L22 7L12 2Z" fill="#60a5fa"/>
                <path d="M2 17L12 22L22 17" stroke="#60a5fa" stroke-width="2" stroke-linecap="round"/>
                <path d="M2 12L12 17L22 12" stroke="#60a5fa" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        
        <h1 class="login-title">Acceso al Dashboard</h1>
        
        <?php if (!empty($error_msg)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_msg); ?></div>
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
    
    <script>
        // Agregar pequeño efecto visual al formulario
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
        });
    </script>
</body>
</html>