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


// Obtener datos de rendimiento de agentes
$rendimiento_agentes = obtener_rendimiento_agente($inicio, $fin);

// Procesar los datos para garantizar que tengan el formato esperado
$rendimiento_procesado = procesar_datos_rendimiento($rendimiento_agentes);

// Obtener estadísticas por día
$estadisticas_diarias = obtener_estadisticas_chat($inicio, $fin, 'day');

// Preparar datos para la tabla de estadísticas
$estadisticas = [];
if (isset($estadisticas_diarias['statistics']) && is_array($estadisticas_diarias['statistics'])) {
    $estadisticas = $estadisticas_diarias['statistics'];
}

// Calcular métricas manualmente en caso de que la API no devuelva datos
$total_chats = 0;
$total_atendidos = 0;

// Contar desde estadísticas por día
if (is_array($estadisticas)) {
    foreach ($estadisticas as $dia) {
        $total_chats += intval($dia['total_chats'] ?? 0);
        $total_atendidos += intval($dia['attended_chats'] ?? 0);
    }
}

// Si no hay datos en estadísticas, intentar sacarlos de rendimiento de agentes
if ($total_chats == 0 && is_array($rendimiento_procesado)) {
    foreach ($rendimiento_procesado as $agente) {
        $total_chats += intval($agente['chats_received'] ?? 0);
        $total_atendidos += intval($agente['chats_attended'] ?? 0);
    }
}

// Calcular porcentajes
$porcentaje_atencion = ($total_chats > 0) ? ($total_atendidos / $total_chats) * 100 : 0;

// Incluir el header
include_once 'includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include_once 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="tables-container">
            <h1>Tasa de Atención - Estadísticas Detalladas</h1>

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
                <form method="GET" action="metrica_atencion.php" class="filter-form">
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
                            <a href="metrica_atencion.php" class="btn-secondary">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Resumen de métricas -->
            <div class="dashboard-card">
                <h2>Tasa de Atención</h2>
                <div class="gauge-container">
                    <canvas id="gaugeAtencion"></canvas>
                    <div class="gauge-value" id="valor-atencion"><?php echo number_format($porcentaje_atencion, 2); ?></div>
                </div>
                <p class="gauge-label">Basado en <?php echo $total_chats; ?> chats durante el período seleccionado</p>
            </div>
            
            <!-- Tabla de rendimiento por agente -->
            <div class="table-card">
                <h2>Tasa de Atención por Agente</h2>
                
                <?php if (empty($rendimiento_procesado)): ?>
                    <div class="empty-state">
                        <i class="fas fa-info-circle"></i>
                        <p>No hay datos disponibles para los filtros seleccionados.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Agente</th>
                                    <th>Chats Recibidos</th>
                                    <th>Chats Atendidos</th>
                                    <th>Tasa de Atención</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rendimiento_procesado as $agente): ?>
                                    <tr>
                                        <td data-label="Agente"><?php echo htmlspecialchars($agente['agent_name']); ?></td>
                                        <td data-label="Chats Recibidos"><?php echo intval($agente['chats_received']); ?></td>
                                        <td data-label="Chats Atendidos"><?php echo intval($agente['chats_attended']); ?></td>
                                        <td data-label="Tasa de Atención" class="<?php echo ($agente['chats_received'] > 0 && $agente['chats_attended'] > 0) ? 'text-success' : ''; ?>">
                                            <?php 
                                                $tasa = ($agente['chats_received'] > 0) 
                                                    ? ($agente['chats_attended'] / $agente['chats_received']) * 100 
                                                    : 0;
                                                echo number_format($tasa, 2) . '%';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Estadísticas por día -->
            <div class="table-card">
                <h2>Tasa de Atención por Día</h2>
                
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
                                    <th>Conversaciones Totales</th>
                                    <th>Conversaciones Atendidas</th>
                                    <th>Tasa de Atención</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estadisticas as $dia): ?>
                                    <tr>
                                        <td data-label="Fecha"><?php echo date('d/m/Y', strtotime($dia['period'] ?? date('Y-m-d'))); ?></td>
                                        <td data-label="Conversaciones"><?php echo intval($dia['total_chats'] ?? 0); ?></td>
                                        <td data-label="Atendidas"><?php echo intval($dia['attended_chats'] ?? 0); ?></td>
                                        <td data-label="Tasa de Atención">
                                            <?php 
                                                $total = intval($dia['total_chats'] ?? 0);
                                                $tasa = ($total > 0) 
                                                    ? (intval($dia['attended_chats'] ?? 0) / $total) * 100 
                                                    : 0;
                                                echo number_format($tasa, 2) . '%';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/charts.js"></script>
<script src="assets/js/dashboard.js"></script>
<script>
// SCRIPT ESPECÍFICO PARA ESTA PÁGINA
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que todo esté cargado antes de inicializar el gauge
    setTimeout(function() {
        const canvas = document.getElementById('gaugeAtencion');
        if (canvas) {
            // Limpiar cualquier instancia previa
            if (window.chartInstances && window.chartInstances['gaugeAtencion']) {
                window.chartInstances['gaugeAtencion'].destroy();
                window.chartInstances['gaugeAtencion'] = null;
            }
            
            // Asegurarse de que el valor no tenga formato incorrecto
            const valueElement = document.getElementById('valor-atencion');
            if (valueElement) {
                let rawValue = valueElement.textContent.trim();
            }
            
            // Inicializar el gauge
            if (typeof initGaugeChart === 'function') {
                initGaugeChart('gaugeAtencion', '#9933ff', '#3366ff');
            }
        }
    }, 300); // 300ms de espera para asegurar que todo está listo
});
</script>