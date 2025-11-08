<?php
session_start();
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}
require_once "conexion.php";

// Verificar si el usuario es administrador
$stmt = $conn->prepare("SELECT rol FROM Usuario WHERE nombreUsuario = :usuario LIMIT 1");
$stmt->bindParam(":usuario", $_SESSION["usuario"], PDO::PARAM_STR);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$esAdmin = ($usuario["rol"] === 'admin');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Sillas y Mesas Hernández - Sistema de Gestión</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Sillas y Mesas Hernández</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="articulos.php">Artículos</a></li>
        <li class="nav-item"><a class="nav-link" href="clientes.php">Clientes</a></li>
        <li class="nav-item"><a class="nav-link" href="pedidos.php">Pedidos</a></li>
        <li class="nav-item"><a class="nav-link" href="pagos.php">Pagos</a></li>
        <li class="nav-item"><a class="nav-link" href="paquetes.php">Paquetes</a></li>
        <?php if ($esAdmin): ?>
        <li class="nav-item"><a class="nav-link text-primary fw-bold" href="nuevo_usuario.php">Usuarios</a></li>
        <?php endif; ?>
        <li class="nav-item">
          <a class="nav-link text-danger fw-bold" href="logout.php">
            Cerrar sesión (<?= htmlspecialchars($_SESSION["usuario"]) ?>)
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-5">
  <div class="text-center">
    <h1 class="mb-4">Bienvenid@, <?= htmlspecialchars($_SESSION["usuario"]) ?></h1>
    <p class="lead">Administra los artículos, clientes, pedidos, pagos y paquetes del negocio familiar.</p>
  </div>

  <div class="row text-center mt-5">
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5>Artículos</h5>
          <p>Consulta y gestiona sillas, mesas y accesorios.</p>
          <a href="articulos.php" class="btn btn-outline-primary">Entrar</a>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5>Clientes</h5>
          <p>Registra clientes particulares o empresas.</p>
          <a href="clientes.php" class="btn btn-outline-primary">Entrar</a>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5>Pedidos</h5>
          <p>Controla las rentas realizadas por los clientes.</p>
          <a href="pedidos.php" class="btn btn-outline-primary">Entrar</a>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5>Pagos</h5>
          <p>Registra y gestiona los cobros asociados a pedidos.</p>
          <a href="pagos.php" class="btn btn-outline-primary">Entrar</a>
        </div>
      </div>
    </div>
  </div>

  <div class="row text-center mt-4">
    <div class="col-md-4 offset-md-4">
      <div class="card shadow-sm border-0">
        <div class="card-body">
          <h5>Paquetes</h5>
          <p>Crea y administra paquetes con artículos especiales.</p>
          <a href="paquetes.php" class="btn btn-outline-primary">Entrar</a>
        </div>
      </div>
    </div>
  </div>

</div>

<footer class="text-center mt-5 mb-3 text-muted">
  <small>© 2025 Sillas y Mesas Hernández — Sistema de Gestión Interna</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
