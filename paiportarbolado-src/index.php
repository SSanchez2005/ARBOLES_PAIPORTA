<?php
require_once 'auth.php';
require_once 'config.php';

// Consultar árboles
$sql = "SELECT * FROM arboles";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title> PaiportArbolado : Árboles de Paiporta</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Gestión de Árboles de Paiporta</h1>
    <a href="crear.php"> Añadir nuevo árbol</a>
    <input type="text" id="buscar" placeholder="Buscar por especie o ubicación..." onkeyup="buscarArboles()">

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Especie</th>
            <th>Ubicación</th>
            <th>Fecha Plantación</th>
            <th>Estado</th>
	    <th>Imagen</th>
            <th>Acciones</th>
        </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['especie']) ?></td>
            <td><?= htmlspecialchars($row['ubicacion']) ?></td>
            <td><?= $row['fecha_plantacion'] ?></td>
            <td><?= $row['estado'] ?></td>
	    <td>
                <?php if (!empty($row['imagen'])): ?>
                    <img src="<?= htmlspecialchars($row['imagen']) ?>" alt="Foto de <?= htmlspecialchars($row['especie']) ?>" style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
                <?php endif; ?>
            </td>
            <td>
            <a href="editar.php?id=<?= $row['id'] ?>">Editar</a>
            <form method="POST" action="eliminar.php" style="display:inline" onsubmit="return confirm('¿Eliminar este árbol?')">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit">Eliminar</button>
            </form>
            </td>
            </tr>
            <?php endwhile; ?>
    </table>

    <script src="js/script.js"></script>
    </body>
</html>
<?php $conn->close(); ?>
