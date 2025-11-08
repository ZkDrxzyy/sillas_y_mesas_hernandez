<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: pagos.php");
    exit;
}

$errores = [];

try {
    // Obtener datos actuales del pago
    $stmt = $conn->prepare("SELECT * FROM Pago WHERE idPago = :id");
    $stmt->execute([':id' => $id]);
    $pago = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pago) {
        die("Pago no encontrado.");
    }

    // Cargar pedidos disponibles
    $pedidos = $conn->query("
        SELECT Pe.idPedido, C.nombre AS cliente
        FROM Pedido Pe
        JOIN Cliente C ON Pe.idCliente = C.idCliente
        ORDER BY Pe.idPedido DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar datos: " . htmlspecialchars($e->getMessage()));
}

// Actualización
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
                UPDATE Pago 
                SET idPedido = :idPedido, fechaPago = :fechaPago, monto = :monto, estadoPago = :estadoPago
                WHERE idPago = :id
            ");
            $stmt->execute([
                ':idPedido' => $idPedido,
                ':fechaPago' => $fechaPago,
                ':monto' => $monto,
                ':estadoPago' => $estadoPago,
                ':id' => $id
            ]);
            header("Location: pagos.php");
            exit;
        } catch (PDOException $e) {
            $errores[] = "Error al actualizar: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar Pago #<?= htmlspecialchars($id) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h2>Editar Pago #<?= htmlspecialchars($id) ?></h2>

  <?php if($errores): ?>
  <div class="alert alert-danger"><ul><?php foreach($errores as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Pedido</label>
      <select name="idPedido" class="form-select" required>
        <?php foreach($pedidos as $p): ?>
          <option value="<?= $p['idPedido'] ?>" <?= $p['idPedido']==$pago['idPedido']?'selected':'' ?>>
            #<?= $p['idPedido'] ?> - <?= htmlspecialchars($p['cliente']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Fecha de pago</label>
      <input type="date" name="fechaPago" class="form-control" value="<?= htmlspecialchars($pago['fechaPago']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Monto</label>
      <input type="number" step="0.01" name="monto" class="form-control" value="<?= htmlspecialchars($pago['monto']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Estado</label>
      <select name="estadoPago" class="form-select">
        <option value="Pendiente" <?= $pago['estadoPago']=="Pendiente"?'selected':'' ?>>Pendiente</option>
        <option value="Pagado" <?= $pago['estadoPago']=="Pagado"?'selected':'' ?>>Pagado</option>
        <option value="Cancelado" <?= $pago['estadoPago']=="Cancelado"?'selected':'' ?>>Cancelado</option>
      </select>
    </div>

    <button class="btn btn-primary">Guardar cambios</button>
    <a href="pagos.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
