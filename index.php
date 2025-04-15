<?php
// index.php - Archivo principal del dashboard
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

// Inicializar variables
$metricas = [];
$config_dashboard = [];
$conversaciones_por_hora = [];

// Obtener la fecha actual o la seleccionada por el usuario
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Fechas para el rango (por defecto el mes actual)
$inicio_mes = date('Y-m-01');
$fin_mes = date('Y-m-t');

try {
    // Obtener datos de la API
    $metricas_datos = obtener_metricas_dashboard($fecha);
    
    // Asegurarse de que todas las claves necesarias estén presentes
    $metricas = [
        'atencion' => $metricas_datos['attention_rate'] ?? 0,
        'oportunidad' => $metricas_datos['opportunity_rate'] ?? 0,
        'abandono' => $metricas_datos['abandonment_rate'] ?? 0,
        'tiempo_espera' => $metricas_datos['average_wait_time'] ?? 0,
        'tiempo_respuesta' => $metricas_datos['average_response_time'] ?? 0,
        'duracion_conversacion' => $metricas_datos['average_conversation_duration'] ?? 0,
        'conversaciones_recibidas' => $metricas_datos['total_conversations'] ?? 0,
        'conversaciones_atendidas' => $metricas_datos['attended_conversations'] ?? 0,
        'objetivos_cantidad' => isset($metricas_datos['goals_achieved']) && isset($metricas_datos['total_goals']) ? 
            $metricas_datos['goals_achieved'] . '/' . $metricas_datos['total_goals'] : '0/0',
        'objetivos_porcentaje' => isset($metricas_datos['goals_achieved']) && isset($metricas_datos['total_goals']) && $metricas_datos['total_goals'] > 0 ? 
            ($metricas_datos['goals_achieved'] / $metricas_datos['total_goals'] * 100) : 0,
        'abandonadas_cantidad' => isset($metricas_datos['abandoned_conversations']) && isset($metricas_datos['total_conversations']) ? 
            $metricas_datos['abandoned_conversations'] . '/' . $metricas_datos['total_conversations'] : '0/0',
        'total_chats' => $metricas_datos['total_conversations'] ?? 0
    ];
    
    $config_dashboard = obtener_configuracion_dashboard();
    $conversaciones_por_hora = obtener_estadisticas_chat($inicio_mes, $fin_mes, 'hour');
    
    // Procesar datos para usar en los gráficos
    $metricas = procesar_metricas($metricas);
    $datos_grafico = procesar_datos_grafico_horas($conversaciones_por_hora);

    // NUEVA LÍNEA: Verificar si tenemos datos reales para las conversaciones por hora
    if (empty($datos_grafico['labels'])) {
        // Registrar error si no hay datos
        error_log("No se obtuvieron datos para el gráfico de conversaciones por hora");
    }
} catch (Exception $e) {
    // Si hay un error con la API, mostrar mensaje
    $error_api = "Error al conectar con la API: " . $e->getMessage();
}

// Incluir el header
include_once 'includes/header.php';
?>

<div class="wrapper">
    <!-- Sidebar -->
    <?php include_once 'includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <div class="content-wrapper">
        <div class="dashboard-header">
            <h1>Dashboard de Estadísticas</h1>
            <div class="date-filter">
                <form method="GET" action="index.php">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" value="<?php echo $fecha; ?>" onchange="this.form.submit()">
                </form>
            </div>
        </div>
        
        <?php if (isset($error_api)): ?>
            <div class="error-message">
                <?php echo $error_api; ?>
            </div>
        <?php else: ?>
            <!-- Dashboard content -->
            <div class="dashboard-container">
            <?php $porcentajeAtencion = number_format($metricas['atencion'], 2);?>

                <!-- Fila superior -->
                <div class="dashboard-row">
                    <!-- Tasa de Atención -->
                    <div class="dashboard-card purple-gradient">
                        <h2>Tasa de Atención</h2>
                        <div class="gauge-container">
                            <canvas id="gaugeAtencion"></canvas>
                            <div class="gauge-value"><?php echo $porcentajeAtencion; ?>%</div>
                        </div>
                        <p class="gauge-label">Basado en <?php echo $metricas['conversaciones_recibidas']; ?> chats</p>
                    </div>
                    
                    <!-- Tasa de Oportunidad -->
                    <div class="dashboard-card yellow-gradient">
                        <h2>Tasa de Oportunidad</h2>
                        <div class="gauge-container">
                            <canvas id="gaugeOportunidad"></canvas>
                            <div class="gauge-value"><?php echo number_format($metricas['oportunidad'], 2); ?>%</div>
                        </div>
                        <p class="gauge-label">Basado en <?php echo $metricas['conversaciones_recibidas']; ?> chats</p>
                    </div>
                    
                    <!-- Métricas de Tiempo -->
                    <div class="dashboard-card wide-card brown-gradient">
                        <h2>Métricas de Tiempo (Minutos)</h2>
                        <div class="time-metrics-container">
                            <canvas id="timeMetrics"></canvas>
                        </div>
                        <div class="time-metrics-values">
                            <div class="time-metric" id="tiempo-espera">
                                <span class="time-label espera">Tiempo Promedio de Espera</span>
                                <span class="time-value"><?php echo number_format($metricas['tiempo_espera'] ?? 0, 1); ?> minutos</span>
                            </div>
                            <div class="time-metric" id="tiempo-respuesta">
                                <span class="time-label respuesta">Tiempo Promedio de Primera Respuesta</span>
                                <span class="time-value"><?php echo number_format($metricas['tiempo_respuesta'], 1); ?> minutos</span>
                            </div>
                            <div class="time-metric" id="tiempo-duracion">
                                <span class="time-label duracion">Duración Promedio de Conversación</span>
                                <span class="time-value"><?php echo number_format($metricas['duracion_conversacion'], 1); ?> minutos</span>
                            </div>
                        </div>

                    </div>
                </div>
                
                <!-- Fila media -->
                <div class="dashboard-row">
                    <!-- Tasa de Abandono -->
                    <div class="dashboard-card red-gradient">
                        <h2>Tasa de Abandono</h2>
                        <div class="gauge-container">
                            <canvas id="gaugeAbandono"></canvas>
                            <div class="gauge-value"><?php echo number_format($metricas['abandono'], 2); ?>%</div>
                        </div>
                        <p class="gauge-label">Basado en <?php echo $metricas['conversaciones_recibidas']; ?> chats</p>
                    </div>
                    
                    <!-- Conversaciones -->
                    <div class="dashboard-card purple-dark-gradient">
                        <h2>Conversaciones</h2>
                        <div class="conversations-container">
                            <div class="conversation-stats">
                                <div class="stat-box">
                                    <h3>Recibidas</h3>
                                    <div class="stat-value"><?php echo $metricas['conversaciones_recibidas']; ?></div>
                                </div>
                                <div class="stat-box">
                                    <h3>Atendidas</h3>
                                    <div class="stat-value"><?php echo $metricas['conversaciones_atendidas']; ?></div>
                                </div>
                            </div>
                            <div class="conversation-gauge">
                                <canvas id="gaugeConversaciones"></canvas>
                                <div class="gauge-value"><?php echo number_format($metricas['atencion'], 2); ?>%</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Conversaciones por Hora -->
                    <div class="dashboard-card wide-card brown-gradient">
                        <h2>Conversaciones por Hora</h2>
                        <div class="chart-container">
                            <canvas id="hourlyChats"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Fila inferior -->
                <div class="dashboard-row">
                    <!-- Métricas de Rendimiento -->
                    <div class="dashboard-card full-width dark-blue-gradient">
                        <h2>Métricas de Rendimiento</h2>
                        <table class="performance-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>CANTIDAD</th>
                                    <th>PORCENTAJE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Objetivos Alcanzados</td>
                                    <td><?php echo $metricas['objetivos_cantidad']; ?></td>
                                    <td>
                                        <div class="progress-container">
                                            <div class="progress-bar" style="width: <?php echo $metricas['objetivos_porcentaje']; ?>%"></div>
                                            <span><?php echo number_format($metricas['objetivos_porcentaje'], 2); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Conversaciones Abandonadas</td>
                                    <td><?php echo $metricas['abandonadas_cantidad']; ?></td>
                                    <td>
                                        <div class="progress-container">
                                            <div class="progress-bar red" style="width: <?php echo $metricas['abandono']; ?>%"></div>
                                            <span><?php echo number_format($metricas['abandono'], 2); ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Index.php: DOM Content Loaded');
    
    // Datos para el gráfico de conversaciones por hora
    var hourlyData = {
        labels: <?php echo json_encode($datos_grafico['labels'] ?? []); ?>,
        values: <?php echo json_encode($datos_grafico['values'] ?? []); ?>
    };
    
    console.log('Datos para gráfico de horas:', hourlyData);
    
    // Verificar si hay datos para graficar
    if (hourlyData.labels.length > 0 && hourlyData.values.length > 0) {
        console.log('Hay datos para actualizar el gráfico');
        
        // Esperamos 800ms para asegurar que el gráfico ya está inicializado
        setTimeout(function() {
            if (typeof updateHourlyChart === 'function') {
                updateHourlyChart(hourlyData.labels, hourlyData.values);
                console.log('Gráfico actualizado con datos reales');
            } else {
                console.error('Función updateHourlyChart no está disponible');
            }
        }, 800);
    } else {
        console.log('No hay datos para mostrar en el gráfico');
        
        // Para una fecha sin datos, forzar un gráfico vacío
        setTimeout(function() {
            if (typeof updateHourlyChart === 'function') {
                // Actualizar con arrays vacíos para mostrar un gráfico sin datos
                updateHourlyChart([], []);
                console.log('Gráfico actualizado con arrays vacíos');
            }
        }, 800);
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/charts.js"></script>
<script src="assets/js/dashboard.js"></script>
