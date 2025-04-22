<?php
session_start();

// Si hay filtros específicos (inicio/fin/agent), dar prioridad a estos
if (isset($_GET['inicio']) || isset($_GET['fin']) || isset($_GET['agent'])) {
    $inicio = isset($_GET['inicio']) ? $_GET['inicio'] : date('Y-m-01');
    $fin = isset($_GET['fin']) ? $_GET['fin'] : date('Y-m-t');
    $agent_email = isset($_GET['agent']) ? $_GET['agent'] : null;
    
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
        $agent_email = null; // No filtrar por agente en este caso
    } else {
        // Si la fecha es inválida, usar valores predeterminados
        $inicio = date('Y-m-01');
        $fin = date('Y-m-t');
        $agent_email = null;
    }
}
// Si no hay parámetros pero hay fecha en sesión
else if (isset($_SESSION['dashboard_fecha'])) {
    // Usar la fecha guardada en sesión
    $fecha_dashboard = $_SESSION['dashboard_fecha'];
    $inicio = $fecha_dashboard;
    $fin = $fecha_dashboard;
    $agent_email = null; // No filtrar por agente en este caso
    
    // Redirigir a la misma página pero con el parámetro fecha
    // para que sea explícito en la URL
    if (!isset($_SESSION['redirect_lock'])) {
        $_SESSION['redirect_lock'] = true;
        header("Location: tables.php?fecha=$fecha_dashboard");
        exit;
    }
}
// Si no hay ni parámetros ni sesión
else {
    // Usar valores predeterminados
    $inicio = date('Y-m-01');
    $fin = date('Y-m-t');
    $agent_email = null;
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

// Obtener parámetros de filtrado
$inicio = isset($_GET['inicio']) ? $_GET['inicio'] : date('Y-m-01');
$fin = isset($_GET['fin']) ? $_GET['fin'] : date('Y-m-t');
$agent_email = isset($_GET['agent']) ? $_GET['agent'] : null;

// Obtener datos de rendimiento de agentes
$rendimiento_agentes = obtener_rendimiento_agente($inicio, $fin, null, $agent_email);

// Procesar los datos para garantizar que tengan el formato esperado
$rendimiento_procesado = procesar_datos_rendimiento($rendimiento_agentes);

// Obtener estadísticas por día
$estadisticas_diarias = obtener_estadisticas_chat($inicio, $fin, 'day');

// Preparar datos para la tabla de estadísticas
$estadisticas = [];
if (isset($estadisticas_diarias['statistics']) && is_array($estadisticas_diarias['statistics'])) {
    $estadisticas = $estadisticas_diarias['statistics'];
}

// Incluir el header
include_once 'includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include_once 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="tables-container">
            <h1>Estadísticas Detalladas</h1>
            
            <!-- Filtros -->
            <div class="filter-card">
                <form method="GET" action="tables.php" class="filter-form">
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
                            <label for="agent">Agente (opcional)</label>
                            <input type="email" id="agent" name="agent" value="<?php echo htmlspecialchars($agent_email ?? ''); ?>" placeholder="correo@ejemplo.com">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-primary">Filtrar</button>
                            <a href="tables.php" class="btn-secondary">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Tabla de rendimiento por agente -->
            <div class="table-card">
                <h2>Rendimiento por Agente</h2>
                
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
                                    <th>Tiempo Promedio de Respuesta</th>
                                    <th>Duración Promedio</th>
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
                                        <td data-label="Tiempo de Respuesta"><?php echo number_format(floatval($agente['avg_response_time']), 2); ?> min</td>
                                        <td data-label="Duración Promedio">
                                            <?php 
                                                $duracion = floatval($agente['avg_duration']);
                                                echo number_format($duracion, 2) . ' min';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Estadísticas por periodo -->
            <div class="table-card">
                <h2>Estadísticas por Periodo</h2>
                
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
                                    <th>Atendidas</th>
                                    <th>Abandonadas</th>
                                    <th>Tasa de Atención</th>
                                    <th>Tiempo Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estadisticas as $dia): ?>
                                    <tr>
                                        <td data-label="Fecha"><?php echo date('d/m/Y', strtotime($dia['period'] ?? date('Y-m-d'))); ?></td>
                                        <td data-label="Conversaciones"><?php echo intval($dia['total_chats'] ?? 0); ?></td>
                                        <td data-label="Atendidas"><?php echo intval($dia['attended_chats'] ?? 0); ?></td>
                                        <td data-label="Abandonadas"><?php echo intval($dia['abandoned_chats'] ?? 0); ?></td>
                                        <td data-label="Tasa de Atención">
                                            <?php 
                                                $total = intval($dia['total_chats'] ?? 0);
                                                $tasa = ($total > 0) 
                                                    ? (intval($dia['attended_chats'] ?? 0) / $total) * 100 
                                                    : 0;
                                                echo number_format($tasa, 2) . '%';
                                            ?>
                                        </td>
                                        <td data-label="Tiempo Promedio">
                                            <?php 
                                                // Inicializar en 0.0 como predeterminado
                                                $tiempo_promedio = 0.0;

                                                // Verificar primero en las métricas globales
                                                if (!empty($metricas['duracion_conversacion'])) {
                                                    $tiempo_promedio = $metricas['duracion_conversacion'];
                                                } 
                                                // Luego buscar en posibles claves dentro del array $dia
                                                elseif (!empty($dia['average_duration_minutes'])) {
                                                    $tiempo_promedio = $dia['average_duration_minutes'];
                                                } 
                                                elseif (!empty($dia['avg_duration'])) {
                                                    $tiempo_promedio = $dia['avg_duration'];
                                                } 

                                                // Mostrar el valor con 2 decimales
                                                echo number_format(floatval($tiempo_promedio), 2) . ' min';
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

<script src="assets/js/dashboard.js"></script>