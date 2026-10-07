<?php
require_once 'auth.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $especie = $_POST['especie'];
    $ubicacion = $_POST['ubicacion'];
    $fecha = $_POST['fecha_plantacion'];
    $usuario = $_POST['usuario'];
    $imagen = null;

    // Subida de imagen (opcional)
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $permitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        // Detectamos el tipo real mirando el contenido, no el nombre
        $mime = mime_content_type($_FILES['imagen']['tmp_name']);

        if (!isset($permitidos[$mime])) {
            die("Formato no permitido. Solo JPG, PNG o WEBP.");
        }

        $nombre = bin2hex(random_bytes(8)) . '.' . $permitidos[$mime];
        $destino = __DIR__ . '/uploads/' . $nombre;

        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
            die("No se pudo guardar la imagen. Revisa los permisos de uploads/.");
        }
        $imagen = 'uploads/' . $nombre;
    }

    $stmt = $conn->prepare("INSERT INTO arboles (especie, ubicacion, fecha_plantacion, usuario_registro, imagen) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $especie, $ubicacion, $fecha, $usuario, $imagen);

    if ($stmt->execute()) {
        registerAction("Tree added $especie in $ubicacion", $usuario);
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
		<title>PaiportArbolado: Añadir Árbol</title>
		<link rel="stylesheet" href="css/style.css">
	</head>
<body>
	<h1>Añadir Nuevo Árbol</h1>
		<form method="POST" enctype="multipart/form-data">
		<label>Especie:</label>
		<input type="text" name="especie" required><br>

		<label>Ubicación:</label>
		<input type="text" name="ubicacion" required><br>

		<label>Fecha de Plantación:</label>
		<input type="date" name="fecha_plantacion" required><br>

		<label>Usuario:</label>
		<input type="text" name="usuario" required><br>

		<button type="submit">Guardar</button>

		<label>Imagen:</label>
		<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp"><br>
	</form>
	<a href="index.php">Volver a la lista</a>
</body>
</html>
