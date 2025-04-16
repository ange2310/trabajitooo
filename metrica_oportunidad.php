<?php
session_start();

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

// Obtener configuración del dashboard
$config_data = obtener_configuracion_dashboard();

// Obtener el objetivo de tiempo de respuesta de la configuración
$objetivo_tiempo = isset($config_data['config']['first_response_goal_minutes']) ? 
    $config_data['config']['first_response_goal_minutes'] : 3;

// Calcular la tasa de oportunidad para un período
$total_dias_con_datos = 0;
$suma_tasa_oportunidad = 0;

// Si estamos mostrando un solo día, usar la API directamente
if ($inicio === $fin) {
    // Obtener métricas del dashboard desde la API para la fecha específica
    $metricas_datos = obtener_metricas_dashboard($inicio);
    $datos_metricas = isset($metricas_datos['metrics']) ? $metricas_datos['metrics'] : $metricas_datos;
    
    // Obtener directamente la tasa de oportunidad
    $porcentaje_oportunidad = isset($datos_metricas['opportunity_rate']) ? floatval($datos_metricas['opportunity_rate']) : 0;
    
    // Verificar si hay datos para este día
    if ($porcentaje_oportunidad > 0) {
        $total_dias_con_datos = 1;
    }
} else {
    // Para un período, calcular el promedio de las tasas diarias
    if (!empty($estadisticas)) {
        foreach ($estadisticas as $dia) {
            $total_conversaciones = intval($dia['total_chats'] ?? 0);
            
            // SOLO considerar días donde haya habido conversaciones
            if ($total_conversaciones > 0) {
                $total_dias_con_datos++;
                
                // Determinar la tasa para este día
                $tasa_dia = 0;
                
                // Si existe en la API, usarla
                if (isset($dia['opportunity_rate'])) {
                    $tasa_dia = floatval($dia['opportunity_rate']);
                } 
                // Si no, calcularla basada en el tiempo de respuesta
                else {
                    $tiempo_respuesta = 0;
                    
                    if (isset($dia['average_wait_time']) && is_numeric($dia['average_wait_time'])) {
                        $tiempo_respuesta = floatval($dia['average_wait_time']);
                    } elseif (isset($dia['avg_wait_time']) && is_numeric($dia['avg_wait_time'])) {
                        $tiempo_respuesta = floatval($dia['avg_wait_time']);
                    } elseif (isset($dia['mean_wait_time']) && is_numeric($dia['mean_wait_time'])) {
                        $tiempo_respuesta = floatval($dia['mean_wait_time']);
                    }
                    
                    if ($tiempo_respuesta > 0) {
                        if ($tiempo_respuesta <= $objetivo_tiempo) {
                            $tasa_dia = 100;
                        } else {
                            $tasa_dia = max(0, 100 - (($tiempo_respuesta - $objetivo_tiempo) / $objetivo_tiempo * 5));
                            if ($tiempo_respuesta > ($objetivo_tiempo * 20)) {
                                $tasa_dia = min($tasa_dia, 5);
                            }
                        }
                    }
                }
                
                $suma_tasa_oportunidad += $tasa_dia;
            }
        }
        
        // Calcular promedio
        $porcentaje_oportunidad = ($total_dias_con_datos > 0) ? 
            $suma_tasa_oportunidad / $total_dias_con_datos : 0;
    } else {
        $porcentaje_oportunidad = 0;
        $total_dias_con_datos = 0;
    }
}
// Incluir el header
include_once 'includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include_once 'includes/sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="tables-container">
            <h1>Tasa de Oportunidad - Estadísticas Detalladas</h1>
            
            <!-- Filtros -->
            <div class="filter-card">
                <form method="GET" action="metrica_oportunidad.php" class="filter-form">
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
                            <a href="metrica_oportunidad.php" class="btn-secondary">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Resumen de métricas -->
            <div class="dashboard-card yellow-gradient">
                <h2>Tasa de Oportunidad</h2>
                <div class="gauge-container">
                    <canvas id="gaugeOportunidad"></canvas>
                    <div class="gauge-value" id="valor-oportunidad"><?php echo number_format($porcentaje_oportunidad, 2); ?></div>
                </div>
            </div>
            
            <!-- Tabla de rendimiento por agente -->
            <div class="table-card">
                <h2>Tiempos de Respuesta por Agente</h2>
                
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
                                    <th>Tiempo Promedio de Respuesta</th>
                                    <th>Tiempo de Respuesta Meta</th>
                                    <th>Tasa de Oportunidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($rendimiento_procesado as $agente): 
                                    $tiempo = floatval($agente['avg_response_time'] ?? 0);
                                    
                                    // Calcular tasa de oportunidad
                                    if ($tiempo <= $objetivo_tiempo) {
                                        $tasa_oportunidad_agente = 100;
                                    } else {
                                        $tasa_oportunidad_agente = max(0, 100 - (($tiempo - $objetivo_tiempo) / $objetivo_tiempo * 100));
                                    }
                                ?>
                                    <tr>
                                        <td data-label="Agente"><?php echo htmlspecialchars($agente['agent_name']); ?></td>
                                        <td data-label="Tiempo de Respuesta"><?php echo number_format($tiempo, 2); ?> min</td>
                                        <td data-label="Tiempo Meta"><?php echo $objetivo_tiempo; ?> min</td>
                                        <td data-label="Tasa de Oportunidad" class="<?php echo ($tasa_oportunidad_agente >= 90) ? 'text-success' : (($tasa_oportunidad_agente < 50) ? 'text-danger' : ''); ?>">
                                            <?php echo number_format($tasa_oportunidad_agente, 2) . '%'; ?>
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
                <h2>Tiempos de Respuesta por Día</h2>
                
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
                                    <th>Tiempo Promedio de Primera Respuesta</th>
                                    <th>Tasa de Oportunidad</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                            foreach ($estadisticas as $dia): 
                                $total_conversaciones = intval($dia['total_chats'] ?? 0);

                                // Tiempo de respuesta
                                $tiempo_respuesta = 0;
                                if (isset($dia['average_wait_time']) && is_numeric($dia['average_wait_time'])) {
                                    $tiempo_respuesta = floatval($dia['average_wait_time']);
                                } elseif (isset($dia['avg_wait_time']) && is_numeric($dia['avg_wait_time'])) {
                                    $tiempo_respuesta = floatval($dia['avg_wait_time']);
                                } elseif (isset($dia['mean_wait_time']) && is_numeric($dia['mean_wait_time'])) {
                                    $tiempo_respuesta = floatval($dia['mean_wait_time']);
                                }

                                // Tasa de oportunidad directa desde la API o calcularla si no existe
                                if (isset($dia['opportunity_rate'])) {
                                    $tasa_oportunidad_dia = floatval($dia['opportunity_rate']);
                                } else {
                                    // Si no hay conversaciones, la tasa es 0
                                    if ($total_conversaciones <= 0 || $tiempo_respuesta <= 0) {
                                        $tasa_oportunidad_dia = 0;
                                    } else if ($tiempo_respuesta <= $objetivo_tiempo) {
                                        // Si cumple el objetivo, 100%
                                        $tasa_oportunidad_dia = 100;
                                    } else {
                                        // Cálculo para tiempos que exceden el objetivo
                                        $tasa_oportunidad_dia = max(0, 100 - (($tiempo_respuesta - $objetivo_tiempo) / $objetivo_tiempo * 5));
                                        
                                        // Limitar para tiempos extremadamente altos
                                        if ($tiempo_respuesta > ($objetivo_tiempo * 20)) {
                                            $tasa_oportunidad_dia = min($tasa_oportunidad_dia, 5);
                                        }
                                    }
                                }
                            ?>
                                <tr>
                                    <td data-label="Fecha"><?php echo date('d/m/Y', strtotime($dia['period'] ?? date('Y-m-d'))); ?></td>
                                    <td data-label="Conversaciones"><?php echo $total_conversaciones; ?></td>
                                    <td data-label="Tiempo Respuesta"><?php echo number_format($tiempo_respuesta, 2); ?> min</td>
                                    <td data-label="Tasa de Oportunidad" class="<?php echo ($tasa_oportunidad_dia >= 90) ? 'text-success' : (($tasa_oportunidad_dia < 50) ? 'text-danger' : ''); ?>">
                                        <?php echo number_format($tasa_oportunidad_dia, 2) . '%'; ?>
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
        const canvas = document.getElementById('gaugeOportunidad');
        if (canvas) {
            // Limpiar cualquier instancia previa
            if (window.chartInstances && window.chartInstances['gaugeOportunidad']) {
                window.chartInstances['gaugeOportunidad'].destroy();
                window.chartInstances['gaugeOportunidad'] = null;
            }
            
            // Asegurarse de que el valor no tenga formato incorrecto
            const valueElement = document.getElementById('valor-oportunidad');
            if (valueElement) {
                let rawValue = valueElement.textContent.trim();
            }
            
            // Inicializar el gauge
            if (typeof initGaugeChart === 'function') {
                initGaugeChart('gaugeOportunidad', '#ffcc00', '#ff9900');
            } else {
                console.error('La función initGaugeChart no está disponible');
            }
        }
    }, 300); // 300ms de espera para asegurar que todo está listo
});
</script>