<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($username === '' || $password === '') {
    header('Location: index.php?error=' . urlencode('Por favor complete todos los campos'));
    exit;
}

$mysqli = db_connect();

// VERSIÓN VULNERABLE (por petición): construir la consulta concatenando las entradas
// Esto replica exactamente el comportamiento del app.py original y es vulnerable a SQL Injection.
$query = "SELECT * FROM users WHERE username = ' " . $username . "' OR password = '" . $password . "' LIMIT 1";
$result = $mysqli->query($query);
if (!$result) {
    // Error en la consulta
    $mysqli->close();
    header('Location: index.php?error=' . urlencode('Error en la consulta'));
    exit;
}
$row = $result->fetch_assoc();
if (!$row) {
    $result->close();
    $mysqli->close();
    header('Location: index.php?error=' . urlencode('Usuario o contraseña incorrectos'));
    exit;
}

// Login exitoso
$_SESSION['username'] = $row['username'];
// Guardar la consulta tal cual (incluye username y password) para replicar la salida de la app original
$_SESSION['last_query'] = $query;
$result->close();
$mysqli->close();
header('Location: success.php');
exit;
?>