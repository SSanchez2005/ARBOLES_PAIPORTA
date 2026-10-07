<?php
session_start();
require_once 'config.php';

// Si ya está logueado, ir a la lista
if (isset($_SESSION['usuario'])) {
	header("Location: index.php");
	exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = $_POST['username'] ?? '';
	$password = $_POST['password'] ?? '';

	$stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
	$stmt->bind_param('s', $username);
	$stmt->execute();
	$user = $stmt->get_Result()->fetch_assoc();
	$stmt->close();

	if ($user && password_verify($password, $user['password_hash'])) {
		session_regenerate_id(true);
		$_SESSION['usuario'] = $user['username'];
		$_SESSION['usuario_id'] = $user['id'];
		registerAction("Login correcto", $user['username']);
		header("Location: index.php");
		exit();
	} else {
		registerAction("Login fallido para '$username'", 'anonimo');
		$error = 'Usuario o contraseña incorrectos.';
	}
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PaiportArbolado: Iniciar sesión</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Iniciar sesión</h1>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Usuario:</label>
        <input type="text" name="username" required><br>

        <label>Contraseña:</label>
        <input type="password" name="password" required><br>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>
