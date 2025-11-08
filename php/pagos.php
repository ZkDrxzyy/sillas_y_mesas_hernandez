<?php
require_once "conexion.php";
try {
    $sql = "
        SELECT P.idPago, P.fechaPago, P.monto, P.estadoPago,
               Pe.idPedido, C.nombre AS cliente
        FROM Pago P
        JOIN Pedido Pe ON P.idPedido = Pe.idPedido
        JOIN Cliente C ON Pe.idCliente = C.idCliente
        ORDER BY P.idPago DESC
    ";
    $pagos = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al leer pagos: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Pagos - Sillas y Mesas Hernández</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
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
        <li class="nav-item"><a class="nav-link active" href="pagos.php">Pagos</a></li>
        <li class="nav-item"><a class="nav-link" href="paquetes.php">Paquetes</a></li>
      </ul>
    </div>
  </div>
</nav>
<div class="container mt-4">
  <h2 class="mb-4">Gestión de Pagos</h2>
  <a href="nuevo_pago.php" class="btn btn-success mb-3">+ Nuevo Pago</a>
  <table class="table table-striped table-hover">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Pedido</th>
        <th>Cliente</th>
        <th>Fecha</th>
        <th>Monto</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
     <?php if ($pagos): ?>
      <?php foreach($pagos as $p): ?>
      <tr>
        <td><?= htmlspecialchars($p['idPago']) ?></td>
        <td>#<?= htmlspecialchars($p['idPedido']) ?></td>
        <td><?= htmlspecialchars($p['cliente']) ?></td>
        <td><?= htmlspecialchars($p['fechaPago']) ?></td>
        <td>$<?= number_format($p['monto'],2) ?></td>
        <td><?= htmlspecialchars($p['estadoPago']) ?></td>
        <td>
          <a href="ver_pago.php?id=<?= $p['idPago'] ?>" class="btn btn-sm btn-info">Ver</a>
          <a href="editar_pago.php?id=<?= $p['idPago'] ?>" class="btn btn-sm btn-primary">Editar</a>
          <a href="eliminar_pago.php?id=<?= $p['idPago'] ?>" class="btn btn-sm btn-danger"
             onclick="return confirm('¿Eliminar este pago?');">Eliminar</a>
        </td>
      </tr>
      <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7" class="text-center text-muted">No hay pagos registrados aún.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
    <footer class="text-center mt-5 mb-3 text-muted">
  <small>© 2025 Sillas y Mesas Hernández — Gestión de Artículos</small>
</footer>

</body>
</html>
