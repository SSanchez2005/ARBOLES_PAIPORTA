<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['usuario'])) {
	registerAction("Logout", $_SESSION['usuario']);
}

$_SESSION = [];
session_destroy();

header("Location: login.php");
exit();
