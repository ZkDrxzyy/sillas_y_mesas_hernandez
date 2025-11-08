<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: pagos.php");
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT P.idPago, P.fechaPago, P.monto, P.estadoPago,
               Pe.idPedido, Pe.montoTotal,
               C.nombre AS cliente, C.correo
        FROM Pago P
        JOIN Pedido Pe ON P.idPedido = Pe.idPedido
        JOIN Cliente C ON Pe.idCliente = C.idCliente
        WHERE P.idPago = :id
    ");
    $stmt->execute([':id' => $id]);
    $pago = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pago) {
        echo "<div class='alert alert-warning'>Pago no encontrado.</div>";
        exit;
    }
} catch (PDOException $e) {
    die("Error al leer pago: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Detalle de Pago #<?= htmlspecialchars($pago['idPago']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h2>Detalle de Pago #<?= htmlspecialchars($pago['idPago']) ?></h2>
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Cliente: <?= htmlspecialchars($pago['cliente']) ?></h5>
      <p class="card-text">
        <strong>Correo:</strong> <?= htmlspecialchars($pago['correo']) ?><br>
        <strong>Pedido asociado:</strong> #<?= htmlspecialchars($pago['idPedido']) ?><br>
        <strong>Monto total del pedido:</strong> $<?= number_format($pago['montoTotal'], 2) ?><br>
        <strong>Fecha de pago:</strong> <?= htmlspecialchars($pago['fechaPago']) ?><br>
        <strong>Monto pagado:</strong> $<?= number_format($pago['monto'], 2) ?><br>
        <strong>Estado:</strong> <?= htmlspecialchars($pago['estadoPago']) ?><br>
      </p>
      <a href="editar_pago.php?id=<?= $pago['idPago'] ?>" class="btn btn-warning">Editar</a>
      <a href="pagos.php" class="btn btn-secondary">Volver</a>
    </div>
  </div>
</div>
</body>
</html>
