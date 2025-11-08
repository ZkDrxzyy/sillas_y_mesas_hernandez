<?php
require_once "conexion.php";

$errores = [];
$fechaPago = date('Y-m-d');
$idPedido = "";
$monto = "";
$estadoPago = "Pendiente";

try {
    $pedidos = $conn->query("
        SELECT Pe.idPedido, C.nombre AS cliente
        FROM Pedido Pe
        JOIN Cliente C ON Pe.idCliente = C.idCliente
        ORDER BY Pe.idPedido DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar pedidos: " . htmlspecialchars($e->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPedido = $_POST['idPedido'];
    $fechaPago = $_POST['fechaPago'];
    $monto = $_POST['monto'];
    $estadoPago = $_POST['estadoPago'];

    if (!$idPedido) $errores[] = "Debe seleccionar un pedido.";
    if (!$monto || $monto <= 0) $errores[] = "Monto inválido.";

    if (empty($errores)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO Pago (idPedido, fechaPago, monto, estadoPago)
                VALUES (:idPedido, :fechaPago, :monto, :estadoPago)
            ");
            $stmt->execute([
                ':idPedido' => $idPedido,
                ':fechaPago' => $fechaPago,
                ':monto' => $monto,
                ':estadoPago' => $estadoPago
            ]);
            header("Location: pagos.php");
            exit;
        } catch (PDOException $e) {
            $errores[] = "Error al guardar: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Nuevo Pago</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h2>Registrar Nuevo Pago</h2>
  <?php if($errores): ?>
  <div class="alert alert-danger">
    <ul><?php foreach($errores as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
  </div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Pedido</label>
      <select name="idPedido" class="form-select" required>
        <option value="">Seleccione pedido...</option>
        <?php foreach($pedidos as $p): ?>
          <option value="<?= $p['idPedido'] ?>" <?= $idPedido==$p['idPedido']?'selected':'' ?>>
            #<?= $p['idPedido'] ?> - <?= htmlspecialchars($p['cliente']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Fecha de pago</label>
      <input type="date" name="fechaPago" class="form-control" value="<?= htmlspecialchars($fechaPago) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Monto</label>
      <input type="number" step="0.01" name="monto" class="form-control" value="<?= htmlspecialchars($monto) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Estado</label>
      <select name="estadoPago" class="form-select">
        <option value="Pendiente" <?= $estadoPago=="Pendiente"?'selected':'' ?>>Pendiente</option>
        <option value="Pagado" <?= $estadoPago=="Pagado"?'selected':'' ?>>Pagado</option>
        <option value="Cancelado" <?= $estadoPago=="Cancelado"?'selected':'' ?>>Cancelado</option>
      </select>
    </div>

    <button class="btn btn-success">Guardar</button>
    <a href="pagos.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
