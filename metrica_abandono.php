<?php
session_start();

// Si hay filtros específicos (inicio/fin), dar prioridad a estos
if (isset($_GET['inicio']) || isset($_GET['fin'])) {
    $inicio = isset($_GET['inicio']) ? $_GET['inicio'] : date('Y-m-01');
    $fin = isset($_GET['fin']) ? $_GET['fin'] : date('Y-m-t');
    
    // No guardar en sesión estos filtros específicos
} 
// Si viene con fecha desde el dashboard
else if (isset($_GET['fecha'])) {
    $fecha_dashboard = $_GET['fecha'];
    
    // Validar formato de fecha
    $date_parts = explode('-', $fecha_dashboard);
    if (count($date_parts) === 3 && checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
        // Si la fecha es válida:
        // 1. Guardarla en sesión para mantener consistencia
        $_SESSION['dashboard_fecha'] = $fecha_dashboard;
        // 2. Usar la misma fecha como inicio y fin
        $inicio = $fecha_dashboard;
        $fin = $fecha_dashboard;
    } else {
        // Si la fecha es inválida, usar valores predeterminados
        $inicio = date('Y-m-01');
        $fin = date('Y-m-t');
    }
}
// Si no hay parámetros pero hay fecha en sesión
else if (isset($_SESSION['dashboard_fecha'])) {
    // Usar la fecha guardada en sesión
    $fecha_dashboard = $_SESSION['dashboard_fecha'];
    $inicio = $fecha_dashboard;
    $fin = $fecha_dashboard;
    
    // Redirigir a la misma página pero con el parámetro fecha
    // para que sea explícito en la URL
    if (!isset($_SESSION['redirect_lock'])) {
        $_SESSION['redirect_lock'] = true;
        $current_page = basename($_SERVER['PHP_SELF']);
        header("Location: $current_page?fecha=$fecha_dashboard");
        exit;
    }
}
// Si no hay ni parámetros ni sesión
else {
    // Usar valores predeterminados
    $inicio = date('Y-m-01');
    $fin = date('Y-m-t');
}

// Limpiar bloqueo de redirección
$_SESSION['redirect_lock'] = false;
// Incluir archivos necesarios
require_once 'config/config.php';
require_once 'includes/conexion_api.php';
require_once 'includes/get_metrics.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['token']) || empty($_SESSION['token'])) {
    header('Location: login.php');
    exit;
}

// Obtener estadísticas por día
$estadisticas_diarias = obtener_estadisticas_chat($inicio, $fin, 'day');

// Preparar datos para la tabla de estadísticas
$estadisticas = [];
if (isset($estadisticas_diarias['statistics']) && is_array($estadisticas_diarias['statistics'])) {
    $estadisticas = $estadisticas_diarias['statistics'];
}

// Calcular métricas manualmente en caso de que la API no devuelva datos
$total_chats = 0;
$total_abandonados = 0;

// Contar desde estadísticas por día
if (is_array($estadisticas)) {
    foreach ($estadisticas as $dia) {
        $total_chats += intval($dia['total_chats'] ?? 0);
        $total_abandonados += intval($dia['abandoned_chats'] ?? 0);
    }
}

// Calcular porcentajes
$porcentaje_abandono = ($total_chats > 0) ? ($total_abandonados / $total_chats) * 100 : 0;

// Incluir el header
include_once 'includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include_once 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="tables-container">
            <h1>Tasa de Abandono - Estadísticas Detalladas</h1>

            <div class="dashboard-navigation">
                <a href="index.php<?php echo isset($_SESSION['dashboard_fecha']) ? '?fecha='.htmlspecialchars($_SESSION['dashboard_fecha']) : ''; ?>" class="back-to-dashboard">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>

            <style>
                .dashboard-navigation {
                    margin-bottom: 20px;
                }
                
                .back-to-dashboard {
                    display: inline-flex;
                    align-items: center;
                    padding: 8px 15px;
                    background-color: rgba(59, 130, 246, 0.1);
                    border-radius: 6px;
                    color: #3b82f6;
                    font-weight: 500;
                    transition: all 0.2s ease;
                }
                
                .back-to-dashboard:hover {
                    background-color: rgba(59, 130, 246, 0.2);
                    transform: translateX(-5px);
                }
                
                .back-to-dashboard i {
                    margin-right: 8px;
                }
            </style>
            
            <!-- Filtros -->
            <div class="filter-card">
                <form method="GET" action="metrica_abandono.php" class="filter-form">
                    <div class="filter-row">
                        <div class="form-group">
                            <label for="inicio">Fecha Inicio</label>
                            <input type="date" id="inicio" name="inicio" value="<?php echo htmlspecialchars($inicio); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="fin">Fecha Fin</label>
                            <input type="date" id="fin" name="fin" value="<?php echo htmlspecialchars($fin); ?>">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-primary">Filtrar</button>
                            <a href="metrica_abandono.php" class="btn-secondary">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Resumen de métricas -->
            <div class="dashboard-card">
                <h2>Tasa de Abandono</h2>
                <div class="gauge-container">
                    <canvas id="gaugeAbandono"></canvas>
                    <div class="gauge-value" id="valor-abandono"><?php echo number_format($porcentaje_abandono, 2); ?></div>
                </div>
                <p class="gauge-label">Basado en <?php echo $total_chats; ?> conversaciones durante el período seleccionado</p>
            </div>
            
            <!-- Estadísticas por día -->
            <div class="table-card">
                <h2>Tasa de Abandono por Día</h2>
                
                <?php if (empty($estadisticas)): ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p>No hay datos disponibles para los filtros seleccionados.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Conversaciones</th>
                                    <th>Abandonadas</th>
                                    <th>Tasa de Abandono</th>
                                    <th>Tiempo Promedio de Espera</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estadisticas as $dia): ?>
                                    <tr>
                                        <td data-label="Fecha"><?php echo date('d/m/Y', strtotime($dia['period'] ?? date('Y-m-d'))); ?></td>
                                        <td data-label="Conversaciones"><?php echo intval($dia['total_chats'] ?? 0); ?></td>
                                        <td data-label="Abandonadas"><?php echo intval($dia['abandoned_chats'] ?? 0); ?></td>
                                        <td data-label="Tasa de Abandono" class="<?php echo (isset($dia['abandoned_chats']) && $dia['abandoned_chats'] > 0) ? 'text-danger' : ''; ?>">
                                            <?php 
                                                $total = intval($dia['total_chats'] ?? 0);
                                                $abandonados = intval($dia['abandoned_chats'] ?? 0);
                                                $tasa = ($total > 0) ? ($abandonados / $total) * 100 : 0;
                                                echo number_format($tasa, 2) . '%';
                                            ?>
                                        </td>
                                        <td data-label="Tiempo de Espera">
                                            <?php 
                                                // Verificar si hay conversaciones este día
                                                $total_conversaciones = intval($dia['total_chats'] ?? 0);
                                                
                                                if ($total_conversaciones <= 0) {
                                                    // Si no hay conversaciones, mostrar 0 minutos
                                                    echo "0.00 min";
                                                } else {
                                                    // Si hay conversaciones, buscar el tiempo de espera en los datos
                                                    $tiempo_espera = 0;
                                                    
                                                    if (isset($dia['average_wait_time']) && is_numeric($dia['average_wait_time'])) {
                                                        $tiempo_espera = floatval($dia['average_wait_time']);
                                                    } elseif (isset($dia['avg_wait_time']) && is_numeric($dia['avg_wait_time'])) {
                                                        $tiempo_espera = floatval($dia['avg_wait_time']);
                                                    } elseif (isset($dia['mean_wait_time']) && is_numeric($dia['mean_wait_time'])) {
                                                        $tiempo_espera = floatval($dia['mean_wait_time']);
                                                    }
                                                    
                                                    // Mostrar el tiempo encontrado
                                                    echo number_format($tiempo_espera, 2) . ' min';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recomendaciones para reducir abandono -->
            <div class="table-card">
                <h2>Recomendaciones para Reducir el Abandono</h2>
                
                <div class="recommendation-list">
                    <div class="recommendation-item">
                        <i class="fas fa-clock"></i>
                        <div class="recommendation-content">
                            <h3>Optimizar Tiempos de Respuesta</h3>
                            <p>Reducir el tiempo de primera respuesta es crucial para evitar que los usuarios abandonen la conversación. Establecer un objetivo de respuesta inferior a 1 minuto.</p>
                        </div>
                    </div>
                    
                    <div class="recommendation-item">
                        <i class="fas fa-user-friends"></i>
                        <div class="recommendation-content">
                            <h3>Ajustar Número de Agentes</h3>
                            <p>Ajustar la cantidad de agentes disponibles según los horarios de mayor demanda para garantizar una atención oportuna.</p>
                        </div>
                    </div>
                    
                    <div class="recommendation-item">
                        <i class="fas fa-robot"></i>
                        <div class="recommendation-content">
                            <h3>Implementar Respuestas Automáticas</h3>
                            <p>Utilizar respuestas automáticas para reconocer la recepción del mensaje mientras un agente se conecta, reduciendo la sensación de espera.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .recommendation-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
        padding: 15px 0;
    }
    
    .recommendation-item {
        display: flex;
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 10px;
        padding: 20px;
        gap: 20px;
        transition: transform 0.3s ease;
    }
    
    .recommendation-item:hover {
        transform: translateY(-5px);
    }
    
    .recommendation-item i {
        font-size: 30px;
        color: #ef4444;
        min-width: 40px;
        text-align: center;
    }
    
    .recommendation-content h3 {
        margin-bottom: 10px;
        color: #ffffff;
    }
    
    .recommendation-content p {
        color: #94a3b8;
        font-size: 14px;
        line-height: 1.6;
    }
    
    .text-danger {
        color: #ef4444;
    }
    
    @media (max-width: 768px) {
        .recommendation-item {
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 15px;
        }
        
        .recommendation-item i {
            margin-bottom: 10px;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/charts.js"></script>
<script src="assets/js/dashboard.js"></script>
<script>
// SCRIPT ESPECÍFICO PARA ESTA PÁGINA
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que todo esté cargado antes de inicializar el gauge
    setTimeout(function() {
        const canvas = document.getElementById('gaugeAbandono');
        if (canvas) {
            
            // Limpiar cualquier instancia previa
            if (window.chartInstances && window.chartInstances['gaugeAbandono']) {
                window.chartInstances['gaugeAbandono'].destroy();
                window.chartInstances['gaugeAbandono'] = null;
            }
            
            // Asegurarse de que el valor no tenga formato incorrecto
            const valueElement = document.getElementById('valor-abandono');
            if (valueElement) {
                let rawValue = valueElement.textContent.trim();
                // Solo mantener el número (quitar el %)
                if (rawValue.includes('%')) {
                    rawValue = rawValue.replace('%', '').trim();
                    valueElement.textContent = rawValue;
                }
            }
            
            // Inicializar el gauge
            if (typeof initGaugeChart === 'function') {
                initGaugeChart('gaugeAbandono', '#ff3366', '#ff0000');
            }
        }
    }, 300); // 300ms de espera para asegurar que todo está listo
});
</script>