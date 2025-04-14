<?php
// profile.php - Página de perfil y configuración
session_start();

// Incluir archivos necesarios
require_once 'config/config.php';
require_once 'includes/get_metrics.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['token']) || empty($_SESSION['token'])) {
    header('Location: login.php');
    exit;
}

$message = '';
$error = '';

// Si es una petición POST, actualizar la configuración
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger los datos del formulario
    $config = [
        'conversation_validity_minutes' => isset($_POST['conversation_validity_minutes']) ? (int)$_POST['conversation_validity_minutes'] : null,
        'first_response_goal_minutes' => isset($_POST['first_response_goal_minutes']) ? (int)$_POST['first_response_goal_minutes'] : null,
        'inactivity_alert_minutes' => isset($_POST['inactivity_alert_minutes']) ? (int)$_POST['inactivity_alert_minutes'] : null
    ];
    
    // Eliminar valores nulos (opcionales)
    foreach ($config as $key => $value) {
        if ($value === null) {
            unset($config[$key]);
        }
    }
    
    // Actualizar configuración
    $result = actualizar_configuracion_dashboard($config);
    
    if (isset($result['error'])) {
        $error = $result['message'];
    } else {
        $message = 'Configuración actualizada correctamente';
    }
}

// Obtener la configuración actual
$config_data = obtener_configuracion_dashboard();

// Establecer valores predeterminados si hay error
if (isset($config_data['error'])) {
    $error = $config_data['message'];
    $config = [
        'conversation_validity_minutes' => 30,
        'first_response_goal_minutes' => 3,
        'inactivity_alert_minutes' => 10
    ];
} else {
    $config = $config_data['config'] ?? [];
}

// Incluir el header
include_once 'includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include_once 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="profile-container">
            <h1>Perfil y Configuración del Dashboard</h1>
            
            <?php if (!empty($message)): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="profile-card">
                <h2>Información del Usuario</h2>
                <div class="profile-info">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-details">
                        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Usuario'); ?></p>
                        <p><strong>Rol:</strong> Administrador</p>
                        <p><strong>Última sesión:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="config-card">
                <h2>Configuración del Dashboard</h2>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="config-form">
                    <div class="form-group">
                        <label for="conversation_validity_minutes">Validez de la conversación (minutos):</label>
                        <input type="number" id="conversation_validity_minutes" name="conversation_validity_minutes" 
                               value="<?php echo $config['conversation_validity_minutes'] ?? 30; ?>" min="1" max="120">
                        <small>Tiempo máximo para considerar una conversación como activa.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="first_response_goal_minutes">Meta de primera respuesta (minutos):</label>
                        <input type="number" id="first_response_goal_minutes" name="first_response_goal_minutes" 
                               value="<?php echo $config['first_response_goal_minutes'] ?? 3; ?>" min="1" max="30" step="0.5">
                        <small>Tiempo objetivo para la primera respuesta del agente.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="inactivity_alert_minutes">Alerta de inactividad (minutos):</label>
                        <input type="number" id="inactivity_alert_minutes" name="inactivity_alert_minutes" 
                               value="<?php echo $config['inactivity_alert_minutes'] ?? 10; ?>" min="1" max="60">
                        <small>Tiempo de inactividad antes de generar una alerta.</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Guardar Configuración</button>
                        <button type="reset" class="btn-secondary">Restablecer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-container {
        padding: 20px;
    }
    
    .profile-card, .config-card {
        background: #262a38;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        margin-bottom: 25px;
        overflow: hidden;
        padding: 25px;
    }
    
    .profile-info {
        display: flex;
        align-items: center;
    }
    
    .profile-avatar {
        font-size: 60px;
        color: #3b82f6;
        margin-right: 30px;
    }
    
    .profile-details p {
        margin: 10px 0;
        color: #e2e8f0;
    }
    
    .success-message, .error-message {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .success-message {
        background-color: rgba(16, 185, 129, 0.2);
        color: #6ee7b7;
        border-left: 4px solid #10b981;
    }
    
    .error-message {
        background-color: rgba(239, 68, 68, 0.2);
        color: #fca5a5;
        border-left: 4px solid #ef4444;
    }
    
    .config-form {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-group label {
        margin-bottom: 8px;
        font-weight: 500;
        color: #e2e8f0;
    }
    
    .form-group input {
        padding: 12px;
        border: 1px solid rgba(255, 255, 255, 0.1);
        background-color: rgba(30, 41, 59, 0.5);
        border-radius: 8px;
        color: #e2e8f0;
        font-size: 16px;
    }
    
    .form-group small {
        margin-top: 5px;
        color: #94a3b8;
        font-size: 12px;
    }
    
    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }
    
    .btn-primary, .btn-secondary {
        padding: 12px 20px;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        border: none;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
        color: white;
    }
    
    .btn-secondary {
        background: transparent;
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #94a3b8;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%);
    }
    
    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.05);
        color: #e2e8f0;
    }
    
    @media (max-width: 768px) {
        .profile-info {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-avatar {
            margin-right: 0;
            margin-bottom: 15px;
        }
    }
</style>

<script src="assets/js/dashboard.js"></script>
<!-- Incluir el footer -->
<?php include_once 'includes/footer.php'; ?>