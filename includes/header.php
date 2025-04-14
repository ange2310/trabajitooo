<!-- includes/header.php - Encabezado de todas las páginas -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Estadísticas de Chat</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- CSS personalizado -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <!-- Mobile navigation toggle - solo visible en móviles -->
    <?php if (isset($_SESSION['token']) && !empty($_SESSION['token'])): ?>
        <div class="mobile-nav">
            <button id="mobile-toggle" class="mobile-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    <?php endif; ?>