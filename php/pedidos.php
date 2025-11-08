<?php
require_once "conexion.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Pedidos - Sillas y Mesas Hernández</title>
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
        <li class="nav-item"><a class="nav-link active" href="pedidos.php">Pedidos</a></li>
        <li class="nav-item"><a class="nav-link" href="pagos.php">Pagos</a></li>
        <li class="nav-item"><a class="nav-link" href="paquetes.php">Paquetes</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="mb-4">Pedidos registrados</h2>
	<a class="btn btn-success mb-3" href="nuevo_pedido.php">+ Nuevo pedido</a>
 
  <?php
  try {
      $sql = "
        SELECT P.idPedido, C.nombre AS cliente, 
               P.fechaPedido, P.fechaEvento, P.fechaEntrega, P.fechaDevolucion, P.montoTotal
        FROM Pedido P
        JOIN Cliente C ON P.idCliente = C.idCliente
        ORDER BY P.idPedido DESC;
      ";
      $stmt = $conn->query($sql);
      $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
      echo "<div class='alert alert-danger'>Error al leer pedidos: " . htmlspecialchars($e->getMessage()) . "</div>";
      $pedidos = [];
  }
  ?>

  <?php if (count($pedidos) === 0): ?>
    <div class="alert alert-info">No hay pedidos registrados.</div>
  <?php else: ?>
    <table class="table table-striped table-hover">
      <thead class="table-dark">
              <tr>
                <th>ID Pedido</th>
                <th>Cliente</th>
                <th>Fecha Pedido</th>
                <th>Fecha Evento</th>
                <th>Entrega</th>
                <th>Devolución</th>
                <th>Total</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pedidos as $p): ?>
                <tr>
                  <td><?= htmlspecialchars($p['idPedido']) ?></td>
                  <td><?= htmlspecialchars($p['cliente']) ?></td>
                  <td><?= htmlspecialchars($p['fechaPedido']) ?></td>
                  <td><?= htmlspecialchars($p['fechaEvento']) ?></td>
                  <td><?= htmlspecialchars($p['fechaEntrega']) ?></td>
                  <td><?= htmlspecialchars($p['fechaDevolucion']) ?></td>
                  <td>$<?= number_format($p['montoTotal'], 2) ?></td>
                  <td>
                    <a href="ver_pedido.php?id=<?= urlencode($p['idPedido']) ?>" class="btn btn-sm btn-info">Ver</a>
                    <a href="editar_pedido.php?id=<?= urlencode($p['idPedido']) ?>" class="btn btn-sm btn-primary">Editar</a>
                    <a href="eliminar_pedido.php?id=<?= urlencode($p['idPedido']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar pedido <?= htmlspecialchars($p['idPedido']) ?>?');">Eliminar</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
  <?php endif; ?>
</div>
    
<footer class="text-center mt-5 mb-3 text-muted">
  <small>© 2025 Sillas y Mesas Hernández — Gestión de Artículos</small>
</footer>
    
</body>
</html>
