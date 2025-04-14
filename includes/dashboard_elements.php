<?php
// Funciones para generar el HTML de cada widget
function createCircularWidget($title, $percentage, $color1, $color2 = null) {
    // Asegurar que el porcentaje sea un número entre 0 y 100
    $percentage = is_numeric($percentage) ? max(0, min(100, $percentage)) : 0;

    // Formatear el porcentaje como texto (2 decimales solo si es necesario)
    $formatted_percentage = (floor($percentage) == $percentage) ? number_format($percentage, 0) : number_format($percentage, 2);
    
    // ID único para widget
    $widget_id = 'circular-widget-'.uniqid();
    
    // Si no se proporciona color2, creamos uno con opacidad basado en color1
    if ($color2 === null) {
        // Convertir color1 a formato rgba con opacidad si es necesario
        if (strpos($color1, 'rgb') === 0) {
            $color2 = str_replace(['rgba(', 'rgb(', ')'], ['rgba(', 'rgba(', ',0.5)'], $color1);
        } else {
            // Si es hex, usamos un color similar con menos saturación
            $color2 = $color1;
        }
    }
    
    // Generar HTML para widget circular
    $html = '
    <div class="metric-card">
        <div class="metric-header">
            <h3>' . htmlspecialchars($title) . '</h3>
        </div>
        <div class="metric-body">
            <div class="circular-progress" id="' . $widget_id . '">
                <div class="circular-progress-inner">
                    <div class="circular-progress-circle">
                        <div class="circular-progress-value">' . $formatted_percentage . '%</div>
                        <div class="circular-progress-label">basado en KPIs</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Crear el widget usando Chart.js
        const ctx = document.getElementById("' . $widget_id . '").getContext("2d");
        
        // Definir los colores con gradiente si es posible
        let gradientColors = ctx.createLinearGradient(0, 0, 0, 200);
        gradientColors.addColorStop(0, "' . $color1 . '");
        gradientColors.addColorStop(1, "' . $color2 . '");

        new Chart(ctx, {
            type: "doughnut",
            data: {
                datasets: [{
                    data: [' . $percentage . ', ' . (100 - $percentage) . '],
                    backgroundColor: [
                        gradientColors,
                        "#1a1e2c" // Color de fondo oscuro
                    ],
                    borderWidth: 0,
                    cutout: "75%"
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1500,
                    easing: "easeOutCubic"
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: false
                    }
                }
            }
        });
    });
    </script>
    ';

    return $html;
}

// Función alternativa usando CSS puro (sin Chart.js) por si prefieres algo más ligero
function createCircularWidgetCSS($title, $percentage, $color1, $color2 = null) {
    // Asegurar que el porcentaje sea un número entre 0 y 100
    $percentage = is_numeric($percentage) ? min(max((float)$percentage, 0), 100) : 0;
    
    // Formatear el porcentaje como texto
    $formatted_percentage = (floor($percentage) == $percentage) 
        ? number_format($percentage, 0) 
        : number_format($percentage, 2);
    
    // Si no se proporciona color2, usar el mismo color1
    if ($color2 === null) {
        $color2 = $color1;
    }
    
    // Calcular el ángulo para el gradiente cónico (CSS)
    $angle = $percentage * 3.6; // 100% = 360 grados
    
    $html = '
    <div class="metric-card">
        <div class="metric-header">
            <h3>' . htmlspecialchars($title) . '</h3>
        </div>
        <div class="metric-body">
            <div class="circular-progress-css" style="--percentage: ' . $percentage . '; --angle: ' . $angle . 'deg; --color1: ' . $color1 . '; --color2: ' . $color2 . ';">
                <div class="circular-progress-inner">
                    <div class="circular-progress-value">' . $formatted_percentage . '%</div>
                    <div class="circular-progress-label">basado en KPIs</div>
                </div>
            </div>
        </div>
    </div>';
    
    return $html;
}

function createTableWidget($title, $headers, $data, $options = []) {
    // Opciones por defecto
    $defaults = [
        'tableClass' => 'widget-table',
        'showRowNumbers' => false,
        'progressBars' => [], // Array de columnas que tendrán progress bar
        'progressBarColors' => [], // Colores para las barras
        'numericColumns' => [], // Array de columnas que serán numéricas
        'percentColumns' => [], // Columnas que son porcentajes
        'actionColumn' => false, // Si es true, la última columna es para acciones
        'emptyMessage' => 'No hay datos disponibles',
        'cardClass' => 'metric-card',
    ];
    
    // Combinar opciones proporcionadas con las predeterminadas
    $options = array_merge($defaults, $options);
    
    // Validar datos
    if (empty($data) && count($data) === 0) {
        return '
        <div class="' . htmlspecialchars($options['cardClass']) . '">
            <div class="metric-header">
                <h3>' . htmlspecialchars($title) . '</h3>
            </div>
            <div class="metric-body">
                <div class="empty-data-message">' . htmlspecialchars($options['emptyMessage']) . '</div>
            </div>
        </div>';
    }
    
    // Construir HTML de la tabla
    $html = '
    <div class="' . htmlspecialchars($options['cardClass']) . '">
        <div class="metric-header">
            <h3>' . htmlspecialchars($title) . '</h3>
        </div>
        <div class="metric-body">
            <div class="table-responsive">
                <table class="' . htmlspecialchars($options['tableClass']) . '">
                    <thead>
                        <tr>';
    
    // Añadir columna de números de fila si está activada
    if ($options['showRowNumbers']) {
        $html .= '<th class="row-number-header">#</th>';
    }
    
    // Añadir cabeceras
    foreach ($headers as $index => $header) {
        $class = '';
        if (in_array($index, $options['numericColumns'])) {
            $class .= ' numeric-column';
        }
        if (in_array($index, $options['percentColumns'])) {
            $class .= ' percent-column';
        }
        $html .= '<th' . ($class ? ' class="' . trim($class) . '"' : '') . '>' . htmlspecialchars($header) . '</th>';
    }
    
    $html .= '
                        </tr>
                    </thead>
                    <tbody>';
    
    // Añadir filas de datos
    foreach ($data as $rowIndex => $row) {
        $html .= '<tr>';
        
        // Añadir número de fila si está activado
        if ($options['showRowNumbers']) {
            $html .= '<td class="row-number">' . ($rowIndex + 1) . '</td>';
        }
        
        // Añadir celdas
        foreach ($row as $cellIndex => $cell) {
            $class = '';
            if (in_array($cellIndex, $options['numericColumns'])) {
                $class .= ' numeric-column';
            }
            if (in_array($cellIndex, $options['percentColumns'])) {
                $class .= ' percent-column';
            }
            
            $html .= '<td' . ($class ? ' class="' . trim($class) . '"' : '') . '>';
            
            // Si esta columna debe mostrar una barra de progreso
            if (in_array($cellIndex, $options['progressBars'])) {
                $percentage = is_numeric($cell) ? min(max(floatval($cell), 0), 100) : 0;
                $color = isset($options['progressBarColors'][$cellIndex]) ? $options['progressBarColors'][$cellIndex] : '#4169E1';
                
                $html .= '
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ' . $percentage . '%; background-color: ' . $color . ';"></div>
                    <span>' . (in_array($cellIndex, $options['percentColumns']) ? number_format($percentage, 2) . '%' : $cell) . '</span>
                </div>';
            } else {
                // Celda normal
                $html .= htmlspecialchars($cell);
                
                // Añadir el símbolo % si es una columna de porcentaje
                if (in_array($cellIndex, $options['percentColumns']) && is_numeric($cell)) {
                    $html .= '%';
                }
            }
            
            $html .= '</td>';
        }
        
        $html .= '</tr>';
    }
    
    $html .= '
                    </tbody>
                </table>
            </div>
        </div>
    </div>';
    
    return $html;
}

/**
 * Crear un widget de tabla para métricas de rendimiento
 * 
 * @param string $title Título del widget
 * @param array $metrics Datos de métricas de rendimiento
 * @return string HTML del widget de tabla
 */
function createPerformanceTableWidget($title, $metrics) {
    $headers = ['', 'CANTIDAD', 'PORCENTAJE'];
    $data = [];
    
    foreach ($metrics as $metricName => $metric) {
        $count = isset($metric['count']) ? $metric['count'] : 0;
        $total = isset($metric['total']) ? $metric['total'] : 0;
        $percentage = isset($metric['percentage']) ? $metric['percentage'] : 0;
        
        // Formatear el nombre de la métrica para mostrar
        $displayName = ucwords(preg_replace('/([A-Z])/', ' $1', $metricName));
        
        $data[] = [
            $displayName,
            $count . '/' . $total,
            $percentage
        ];
    }
    
    // Configurar colores para las barras de progreso
    $progressBarColors = [];
    foreach ($metrics as $index => $metric) {
        $color = '#4169E1'; // Color por defecto
        
        // Asignar colores según el nombre de la métrica
        if (strpos(strtolower($index), 'goal') !== false || 
            strpos(strtolower($index), 'achieved') !== false) {
            $color = '#00D897'; // Verde para objetivos alcanzados
        }
        else if (strpos(strtolower($index), 'abandon') !== false) {
            $color = '#FF4560'; // Rojo para abandonos
        }
        
        $progressBarColors[2] = $color;
    }
    
    return createTableWidget(
        $title,
        $headers,
        $data,
        [
            'progressBars' => [2], // La tercera columna (índice 2) muestra barras de progreso
            'progressBarColors' => $progressBarColors,
            'percentColumns' => [2], // La tercera columna (índice 2) son porcentajes
            'numericColumns' => [1, 2] // Segunda y tercera columnas son numéricas
        ]
    );
}

/**
 * Obtiene el CSS necesario para los widgets
 * 
 * @return string CSS para los widgets
 */
function getWidgetCSS() {
    return '
    <style>
    /* Estilos para las tarjetas de métricas */
    .metric-card {
        background: #262a38;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 20px;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .metric-header {
        padding-bottom: 12px;
    }
    
    .metric-header h3 {
        font-size: 18px;
        font-weight: 500;
        color: #ffffff;
        margin: 0;
    }
    
    .metric-body {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px 0;
    }
    
    /* Estilos para los widgets circulares con CSS puro */
    .circular-progress-css {
        position: relative;
        width: 150px;
        height: 150px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: conic-gradient(
            var(--color1) 0deg,
            var(--color2) var(--angle),
            #1a1e2c var(--angle),
            #1a1e2c 360deg
        );
        transform: rotate(-90deg);
    }
    
    .circular-progress-css .circular-progress-inner {
        position: absolute;
        width: 80%;
        height: 80%;
        background-color: #262a38;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        transform: rotate(90deg);
    }
    
    .circular-progress-value {
        font-size: 24px;
        font-weight: bold;
        color: #ffffff;
    }
    
    .circular-progress-label {
        font-size: 12px;
        color: #9ba3af;
        margin-top: 4px;
    }
    
    /* Estilos para los widgets que usan Chart.js */
    .circular-progress {
        position: relative;
        width: 150px;
        height: 150px;
    }
    
    .circular-progress-inner {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .circular-progress-circle {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    
    /* Estilos para tablas en widgets */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    
    .widget-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    
    .widget-table th, 
    .widget-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #373d49;
    }
    
    .widget-table th {
        font-weight: 600;
        background-color: rgba(0, 0, 0, 0.2);
        color: #9ba3af;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .widget-table tr:last-child td {
        border-bottom: none;
    }
    
    .widget-table tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .numeric-column {
        text-align: right;
    }
    
    .percent-column {
        text-align: right;
    }
    
    .row-number-header,
    .row-number {
        width: 40px;
        text-align: center;
    }
    
    /* Barras de progreso */
    .progress-bar {
        height: 10px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 5px;
        position: relative;
        margin-top: 5px;
        margin-bottom: 5px;
        width: 100%;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        border-radius: 5px;
        background-color: #4169E1;
        transition: width 1s ease;
    }
    
    .progress-bar span {
        position: absolute;
        right: 0;
        top: -18px;
        font-size: 12px;
        color: #9ba3af;
    }
    
    /* Mensaje de datos vacíos */
    .empty-data-message {
        text-align: center;
        padding: 30px;
        color: #9ba3af;
        font-style: italic;
    }
    </style>
    ';
}

// Otras funciones para diferentes tipos de widgets...
?>