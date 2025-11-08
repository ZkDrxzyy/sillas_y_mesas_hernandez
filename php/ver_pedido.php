<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: pedidos.php");
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT P.idPedido, P.fechaPedido, P.fechaEvento, P.fechaEntrega, P.fechaDevolucion, P.montoTotal,
               C.nombre AS cliente, C.correo, C.direccion
        FROM Pedido P
        JOIN Cliente C ON P.idCliente = C.idCliente
        WHERE P.idPedido = :id
    ");
    $stmt->execute([':id'=>$id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        echo "<div class='alert alert-warning'>Pedido no encontrado.</div>";
        exit;
    }

    $stmt = $conn->prepare("
        SELECT A.nombre,
               DP.cantidad,
               (A.costoRenta * DP.cantidad) AS subtotal
        FROM DetallePedido DP
        JOIN Articulo A ON DP.idArticulo = A.idArticulo
        WHERE DP.idPedido = :id
    ");
    $stmt->execute([':id'=>$id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
  // Intentar obtener paquetes si existen
  try {
    $stmt2 = $conn->prepare(
      "SELECT P.nombre, DPP.cantidad, (P.precioEspecial * DPP.cantidad) AS subtotal
       FROM DetallePedidoPaquete DPP
       JOIN Paquete P ON DPP.idPaquete = P.idPaquete
       WHERE DPP.idPedido = :id"
    );
    $stmt2->execute([':id' => $id]);
    $detallesPaquetes = $stmt2->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $detallesPaquetes = [];
  }
} catch (PDOException $e) {
    die("Error al cargar pedido: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Detalle Pedido #<?= htmlspecialchars($pedido['idPedido']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h2>Pedido #<?= htmlspecialchars($pedido['idPedido']) ?></h2>

  <div class="card mb-3">
    <div class="card-body">
      <h5 class="card-title">Cliente: <?= htmlspecialchars($pedido['cliente']) ?></h5>
      <p class="card-text">
        <strong>Correo:</strong> <?= htmlspecialchars($pedido['correo']) ?><br>
        <strong>Dirección:</strong> <?= htmlspecialchars($pedido['direccion']) ?><br>
        <strong>Fecha del pedido:</strong> <?= htmlspecialchars($pedido['fechaPedido']) ?><br>
        <strong>Fecha del evento:</strong> <?= htmlspecialchars($pedido['fechaEvento']) ?><br>
        <strong>Entrega:</strong> <?= htmlspecialchars($pedido['fechaEntrega']) ?><br>
        <strong>Devolución:</strong> <?= htmlspecialchars($pedido['fechaDevolucion']) ?><br>
      </p>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5>Artículos</h5>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Artículo</th>
            <th>Cantidad</th>
            <th>Precio unitario</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($detalles as $d): ?>
          <tr>
            <td><?= htmlspecialchars($d['nombre']) ?></td>
            <td><?= htmlspecialchars($d['cantidad']) ?></td>
            <td>$<?= number_format((float)$d['subtotal'] / max(1, (int)$d['cantidad']), 2) ?></td>
            <td>$<?= number_format($d['subtotal'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (!empty($detallesPaquetes)): ?>
      <h5 class="mt-4">Paquetes</h5>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Paquete</th>
            <th>Cantidad</th>
            <th>Precio unitario</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($detallesPaquetes as $dp): ?>
          <tr>
            <td><?= htmlspecialchars($dp['nombre']) ?></td>
            <td><?= htmlspecialchars($dp['cantidad']) ?></td>
            <td>$<?= number_format((float)$dp['subtotal'] / max(1, (int)$dp['cantidad']), 2) ?></td>
            <td>$<?= number_format($dp['subtotal'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
      <h5 class="mt-3">Total: <span class="text-success">$<?= number_format($pedido['montoTotal'], 2) ?></span></h5>
    </div>
  </div>

  <a href="pedidos.php" class="btn btn-secondary mt-3">Volver</a>
</div>
</body>
</html>
