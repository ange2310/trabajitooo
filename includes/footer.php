<footer>
        <!-- Pie de página común -->
    </footer>
    
    <!-- Scripts comunes -->
    <script src="assets/librerias/chart.min.js"></script>
    <script src="assets/js/charts.js"></script>
<!-- Chart.js desde CDN (versión 4.4.8) con fallback -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script>
    // Fallback si el CDN falla
    window.addEventListener('error', function(e) {
        if (e.target.src && e.target.src.indexOf('chart.js') !== -1) {
            var script = document.createElement('script');
            script.src = 'assets/librerias/chart.min.js';
            document.body.appendChild(script);
            console.log('Cargando Chart.js desde local debido a fallo en CDN');
        }
    }, true);
</script>

<!-- Tu script personalizado para los gráficos -->
<script src="assets/js/charts.js"></script>
</body>
</html>