<?php
// tables.php - Página de tablas de datos
session_start();

// Incluir archivos necesarios
require_once 'includes/conexion_api.php';
require_once 'config/config.php';
require_once 'includes/get_metrics.php'; // Eliminando la referencia a procesador_datos.php

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

// Debug: Ver la estructura de datos real
if (!empty($rendimiento_agentes) && isset($rendimiento_agentes[0])) {
    error_log('Estructura de datos del primer agente: ' . print_r($rendimiento_agentes[0], true));
}

// Asegurarse de que siempre sea un array
if (!is_array($rendimiento_agentes)) {
    $rendimiento_agentes = [];
    error_log('obtener_rendimiento_agente() devolvió un valor no array: ' . gettype($rendimiento_agentes));
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
                            <input type="date" id="inicio" name="inicio" value="<?php echo $inicio; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="fin">Fecha Fin</label>
                            <input type="date" id="fin" name="fin" value="<?php echo $fin; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="agent">Agente (opcional)</label>
                            <input type="email" id="agent" name="agent" value="<?php echo $agent_email ?? ''; ?>" placeholder="correo@ejemplo.com">
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
                
                <?php if (empty($rendimiento_agentes)): ?>
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
                                    <th>Valoración</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rendimiento_agentes as $agente): ?>
                                    <tr>
                                        <td><?php echo $agente['agent_name'] ?? 'Sin nombre'; ?></td>
                                        <td><?php echo $agente['chats_received'] ?? 0; ?></td>
                                        <td><?php echo $agente['chats_attended'] ?? 0; ?></td>
                                        <td>
                                            <?php 
                                                $tasa = (isset($agente['chats_received']) && $agente['chats_received'] > 0) 
                                                    ? (($agente['chats_attended'] ?? 0) / $agente['chats_received']) * 100 
                                                    : 0;
                                                echo number_format($tasa, 2) . '%';
                                            ?>
                                        </td>
                                        <td><?php echo number_format($agente['avg_response_time'] ?? 0, 2); ?> min</td>
                                        <td><?php echo number_format($agente['avg_duration'] ?? 0, 2); ?> min</td>
                                        <td>
                                            <div class="rating">
                                                <?php 
                                                    $rating = $agente['rating'] ?? 0;
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $rating) {
                                                            echo '<i class="fas fa-star"></i>';
                                                        } elseif ($i - 0.5 <= $rating) {
                                                            echo '<i class="fas fa-star-half-alt"></i>';
                                                        } else {
                                                            echo '<i class="far fa-star"></i>';
                                                        }
                                                    }
                                                ?>
                                                <span>(<?php echo number_format($rating, 1); ?>)</span>
                                            </div>
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
                
                <?php
                // Obtener estadísticas por día
                $estadisticas_diarias = obtener_estadisticas_chat($inicio, $fin, 'day');
                
                // Preparar datos para la tabla
                $estadisticas = [];
                if (isset($estadisticas_diarias['statistics']) && is_array($estadisticas_diarias['statistics'])) {
                    $estadisticas = $estadisticas_diarias['statistics'];
                }
                ?>
                
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
                                        <td><?php echo date('d/m/Y', strtotime($dia['period'] ?? date('Y-m-d'))); ?></td>
                                        <td><?php echo $dia['total_chats'] ?? 0; ?></td>
                                        <td><?php echo $dia['attended_chats'] ?? 0; ?></td>
                                        <td><?php echo $dia['abandoned_chats'] ?? 0; ?></td>
                                        <td>
                                            <?php 
                                                $total = $dia['total_chats'] ?? 0;
                                                $tasa = ($total > 0) 
                                                    ? (($dia['attended_chats'] ?? 0) / $total) * 100 
                                                    : 0;
                                                echo number_format($tasa, 2) . '%';
                                            ?>
                                        </td>
                                        <td><?php echo number_format($dia['avg_conversation_time'] ?? 0, 2); ?> min</td>
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
