<?php
require_once 'auth.php';
require_once 'config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Obtener datos del árbol
$stmt = $conn->prepare("SELECT * FROM arboles WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$arbol = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$arbol) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $especie = $_POST['especie'];
    $ubicacion = $_POST['ubicacion'];
    $fecha = $_POST['fecha_plantacion'];
    $estado = $_POST['estado'];
    $usuario = $_POST['usuario'];

    $stmt = $conn->prepare("UPDATE arboles SET especie = ?, ubicacion = ?, fecha_plantacion = ?, estado = ? WHERE id = ?");
    $stmt->bind_param('ssssi', $especie, $ubicacion, $fecha, $estado, $id);

    if ($stmt->execute()) {
        registerAction("Tree Updated: ID $id", $usuario);
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
    <head>
    <meta charset="UTF-8">
    <title>PaiportArbolado : Editar Árbol</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Editar Árbol</h1>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $arbol['id'] ?>">

        <label>Especie:</label>
        <input type="text" name="especie" value="<?= htmlspecialchars($arbol['especie']) ?>" required><br>

        <label>Ubicación:</label>
        <input type="text" name="ubicacion" value="<?= htmlspecialchars($arbol['ubicacion']) ?>" required><br>

        <label>Fecha de Plantación:</label>
        <input type="date" name="fecha_plantacion" value="<?= $arbol['fecha_plantacion'] ?>" required><br>

        <label>Estado:</label>
        <select name="estado" required>
        <option value="sano" <?= $arbol['estado'] === 'sano' ? 'selected' : '' ?>>Sano</option>
        <option value="enfermo" <?= $arbol['estado'] === 'enfermo' ? 'selected' : '' ?>>Enfermo</option>
        <option value="talado" <?= $arbol['estado'] === 'talado' ? 'selected' : '' ?>>Talado</option>
        </select><br>

        <label>Usuario:</label>
        <input type="text" name="usuario" value="<?= htmlspecialchars($arbol['usuario_registro']) ?>" required><br>

        <button type="submit">Actualizar</button>
        </form>
    <a href="index.php">Volver a la lista</a>
</body>
</html>
