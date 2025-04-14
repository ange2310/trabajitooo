<?php
// includes/get_metrics.php
// Funciones para obtener y procesar métricas

/**
 * Obtiene las métricas para el dashboard
 * 
 * @param string $fecha Fecha para la cual obtener las métricas
 * @return array Datos procesados para el dashboard
 */
function obtener_metricas($fecha = null) {
    // Para fines de desarrollo y evitar el timeout, generamos datos de muestra
    // En producción, se conectaría con la API real
    
    // Datos de muestra para el dashboard
    return [
        'atencion' => 85.7,
        'oportunidad' => 92.3,
        'abandono' => 7.8,
        'tiempo_espera' => 2.5,
        'tiempo_respuesta' => 1.2,
        'duracion_conversacion' => 8.7,
        'conversaciones_recibidas' => 345,
        'conversaciones_atendidas' => 296,
        'objetivos_cantidad' => '42/50',
        'objetivos_porcentaje' => 84,
        'abandonadas_cantidad' => '27/345',
        'total_chats' => 345
    ];
}

/**
 * Procesa las métricas aplicando reglas de negocio
 * 
 * @param array $metricas Métricas crudas
 * @return array Métricas procesadas
 */
function procesar_metricas($metricas) {
    // Por simplicidad, devolvemos las mismas métricas sin cambios
    return $metricas;
}

/**
 * Obtiene la configuración del dashboard
 * 
 * @return array Configuración del dashboard
 */
function obtener_config_dashboard() {
    // Simulamos una configuración
    return [
        'refresh_rate' => 60, // segundos
        'goal_targets' => [
            'atencion' => 90,
            'oportunidad' => 85,
            'abandono' => 10
        ]
    ];
}

/**
 * Obtiene estadísticas de chat por periodo
 * 
 * @param string $inicio Fecha de inicio
 * @param string $fin Fecha fin
 * @param string $agrupacion Tipo de agrupación (hour, day, week, month)
 * @return array Datos de conversaciones
 */
function obtener_estadisticas_chat($inicio, $fin, $agrupacion = 'day') {
    // Si es por hora, retorna datos por hora
    if ($agrupacion == 'hour') {
        return [
            ['hour' => '4:00 AM', 'count' => 2],
            ['hour' => '5:00 AM', 'count' => 3],
            ['hour' => '6:00 AM', 'count' => 5],
            ['hour' => '7:00 AM', 'count' => 8],
            ['hour' => '8:00 AM', 'count' => 10],
            ['hour' => '9:00 AM', 'count' => 9],
            ['hour' => '10:00 AM', 'count' => 7],
            ['hour' => '11:00 AM', 'count' => 8],
            ['hour' => '12:00 PM', 'count' => 10],
            ['hour' => '1:00 PM', 'count' => 9],
            ['hour' => '2:00 PM', 'count' => 7],
            ['hour' => '3:00 PM', 'count' => 8],
            ['hour' => '4:00 PM', 'count' => 9],
            ['hour' => '5:00 PM', 'count' => 6],
            ['hour' => '6:00 PM', 'count' => 5],
            ['hour' => '7:00 PM', 'count' => 4],
            ['hour' => '8:00 PM', 'count' => 3],
            ['hour' => '9:00 PM', 'count' => 2],
            ['hour' => '10:00 PM', 'count' => 1]
        ];
    }
    
    // Para agrupación diaria
    return [
        [
            'date' => '2025-04-01',
            'total_conversations' => 120,
            'attended_conversations' => 105,
            'abandoned_conversations' => 15,
            'avg_conversation_time' => 8.5
        ],
        [
            'date' => '2025-04-02',
            'total_conversations' => 135,
            'attended_conversations' => 118,
            'abandoned_conversations' => 17,
            'avg_conversation_time' => 7.8
        ],
        [
            'date' => '2025-04-03',
            'total_conversations' => 142,
            'attended_conversations' => 128,
            'abandoned_conversations' => 14,
            'avg_conversation_time' => 9.2
        ]
    ];
}

/**
 * Procesa datos para gráficos
 * 
 * @param array $datos Datos crudos 
 * @return array Datos procesados para gráficos
 */
function procesar_datos_grafico_horas($datos) {
    $labels = [];
    $values = [];
    
    foreach ($datos as $item) {
        $labels[] = $item['hour'];
        $values[] = $item['count'];
    }
    
    return [
        'labels' => $labels,
        'values' => $values
    ];
}

/**
 * Obtiene el rendimiento por agente
 * 
 * @param string $inicio Fecha inicio
 * @param string $fin Fecha fin
 * @param string $agent_email Email del agente (opcional)
 * @return array Datos de rendimiento
 */
function obtener_rendimiento_agente($inicio, $fin, $agent_email = null) {
    // Datos de muestra
    $agentes = [
        [
            'agent_name' => 'Ana García',
            'chats_received' => 345,
            'chats_attended' => 310,
            'avg_response_time' => 1.8,
            'avg_duration' => 10.5,
            'rating' => 4.7
        ],
        [
            'agent_name' => 'Carlos Martínez',
            'chats_received' => 298,
            'chats_attended' => 265,
            'avg_response_time' => 2.1,
            'avg_duration' => 8.9,
            'rating' => 4.5
        ],
        [
            'agent_name' => 'Laura Rodríguez',
            'chats_received' => 312,
            'chats_attended' => 290,
            'avg_response_time' => 1.5,
            'avg_duration' => 9.8,
            'rating' => 4.8
        ]
    ];
    
    // Si se especificó un agente, filtramos los resultados
    if ($agent_email !== null && $agent_email !== '') {
        return [$agentes[0]];
    }
    
    return $agentes;
}
?>