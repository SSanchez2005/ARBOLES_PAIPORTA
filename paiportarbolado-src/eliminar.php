<?php
require_once 'auth.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// Obtener usuario (simulado)
$usuario = "admin";

$stmt = $conn->prepare("DELETE FROM arboles WHERE id = ?");
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    registerAction("Tree Deleted: ID $id", $usuario);
    header("Location: index.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}
