<?php
require_once "conexion.php";

try {
    $sql = "SELECT * FROM Paquete ORDER BY idPaquete DESC";
    $paquetes = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al leer paquetes: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Paquetes - Sillas y Mesas Hernández</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Sillas y Mesas Hernández</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="articulos.php">Artículos</a></li>
        <li class="nav-item"><a class="nav-link" href="clientes.php">Clientes</a></li>
        <li class="nav-item"><a class="nav-link" href="pedidos.php">Pedidos</a></li>
        <li class="nav-item"><a class="nav-link" href="pagos.php">Pagos</a></li>
        <li class="nav-item"><a class="nav-link active" href="paquetes.php">Paquetes</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="mb-4">Gestión de Paquetes</h2>
  <a href="nuevo_paquete.php" class="btn btn-success mb-3">+ Nuevo Paquete</a>

  <table class="table table-striped table-hover">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Precio Especial</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($paquetes): ?>
        <?php foreach ($paquetes as $p): ?>
        <tr>
          <td><?= htmlspecialchars($p['idPaquete']) ?></td>
          <td><?= htmlspecialchars($p['nombre']) ?></td>
          <td>$<?= number_format($p['precioEspecial'], 2) ?></td>
          <td>
            <a href="ver_paquete.php?id=<?= $p['idPaquete'] ?>" class="btn btn-sm btn-info">Ver</a>
            <a href="editar_paquete.php?id=<?= $p['idPaquete'] ?>" class="btn btn-sm btn-primary">Editar</a>
            <a href="eliminar_paquete.php?id=<?= $p['idPaquete'] ?>" class="btn btn-sm btn-danger"
               onclick="return confirm('¿Eliminar este paquete?');">Eliminar</a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="4" class="text-center text-muted">No hay paquetes registrados aún.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<footer class="text-center mt-5 mb-3 text-muted">
  <small>© 2025 Sillas y Mesas Hernández — Gestión de Paquetes</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
