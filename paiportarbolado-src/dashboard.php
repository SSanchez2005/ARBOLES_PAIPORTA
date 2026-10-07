<?php
require_once 'auth.php';
require_once 'config.php';

// Árboles por especie
$porEspecie = [];
$res = $conn->query("SELECT especie, COUNT(*) AS total FROM arboles GROUP BY especie ORDER BY total DESC");
while ($row = $res->fetch_assoc()) {
    $porEspecie[] = $row;
}

// Árboles por estado
$porEstado = [];
$res = $conn->query("SELECT estado, COUNT(*) AS total FROM arboles GROUP BY estado");
while ($row = $res->fetch_assoc()) {
    $porEstado[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PaiportArbolado: Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>Dashboard de árboles</h1>
    <a href="index.php">Volver a la lista</a>

    <div style="display:flex; flex-wrap:wrap; gap:30px; margin-top:20px;">
        <div style="width:400px;">
            <h2>Por especie</h2>
            <canvas id="graficoEspecie"></canvas>
        </div>
        <div style="width:400px;">
            <h2>Por estado</h2>
            <canvas id="graficoEstado"></canvas>
        </div>
    </div>

    <script>
        const porEspecie = <?= json_encode($porEspecie) ?>;
        const porEstado = <?= json_encode($porEstado) ?>;

        new Chart(document.getElementById('graficoEspecie'), {
            type: 'bar',
            data: {
                labels: porEspecie.map(f => f.especie),
                datasets: [{
                    label: 'Nº de árboles',
                    data: porEspecie.map(f => f.total),
                    backgroundColor: '#3498db'
                }]
            },
            options: { scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        new Chart(document.getElementById('graficoEstado'), {
            type: 'pie',
            data: {
                labels: porEstado.map(f => f.estado),
                datasets: [{
                    data: porEstado.map(f => f.total),
                    backgroundColor: ['#2ecc71', '#f1c40f', '#e74c3c']
                }]
            }
        });
    </script>
</body>
</html>
