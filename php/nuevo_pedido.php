<?php
require_once "conexion.php";

$errors = [];

try {
  $clientes = $conn->query("SELECT idCliente, nombre FROM Cliente ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
  $articulos = $conn->query("SELECT idArticulo, nombre, costoRenta, estado FROM Articulo ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
  // Cargar paquetes disponibles
  $paquetes = $conn->query("SELECT idPaquete, nombre, precioEspecial FROM Paquete ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error cargando datos: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCliente = $_POST['idCliente'] ?? '';
    $fechaPedido = $_POST['fechaPedido'] ?? date('Y-m-d');
    $fechaEvento = $_POST['fechaEvento'] ?? '';
    $fechaEntrega = $_POST['fechaEntrega'] ?? null;
    $fechaDevolucion = $_POST['fechaDevolucion'] ?? null;
  $items = $_POST['articulo'] ?? [];
  $cantidades = $_POST['cantidad'] ?? [];
  // paquetes
  $paquetesSel = $_POST['paquete'] ?? [];
  $cantPaquetes = $_POST['cantidad_paquete'] ?? [];

    if ($idCliente === '') $errors[] = "Debe seleccionar un cliente.";
    if ($fechaEvento === '') $errors[] = "Debe indicar la fecha del evento.";
  if (count($items) === 0 && count($paquetesSel) === 0) $errors[] = "Debe seleccionar al menos un artículo o un paquete.";

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            // Insertar el pedido base
            $stmt = $conn->prepare("
                INSERT INTO Pedido (idCliente, fechaPedido, fechaEvento, fechaEntrega, fechaDevolucion, montoTotal)
                VALUES (:c, :fp, :fe, :fent, :fdev, 0)
            ");
            $stmt->execute([
                ':c'=>$idCliente,
                ':fp'=>$fechaPedido,
                ':fe'=>$fechaEvento,
                ':fent'=>$fechaEntrega,
                ':fdev'=>$fechaDevolucion
            ]);

            $idPedido = $conn->lastInsertId();

      // Insertar los artículos
      $total = 0;
      for ($i = 0; $i < count($items); $i++) {
        $idArt = intval($items[$i]);
        $cant = intval($cantidades[$i]);
        if ($cant <= 0) continue;

        $precio = $conn->query("SELECT costoRenta FROM Articulo WHERE idArticulo=$idArt")->fetchColumn();
        $subtotal = $precio * $cant;
        $total += $subtotal;

        $stmt2 = $conn->prepare("INSERT INTO DetallePedido (idPedido, idArticulo, cantidad) VALUES (:p, :a, :c)");
        $stmt2->execute([':p'=>$idPedido, ':a'=>$idArt, ':c'=>$cant]);
      }

      // Insertar paquetes (si hay)
      if (!empty($paquetesSel)) {
        try {
          $stmtP = $conn->prepare("INSERT INTO DetallePedidoPaquete (idPedido, idPaquete, cantidad) VALUES (:p, :pkg, :c)");
          // map de precios de paquetes para sumar al total
          $mapPkg = [];
          foreach ($paquetes as $pk) $mapPkg[$pk['idPaquete']] = (float)$pk['precioEspecial'];

          for ($i = 0; $i < count($paquetesSel); $i++) {
            $idPkg = intval($paquetesSel[$i]);
            $cantPkg = intval($cantPaquetes[$i]);
            if ($cantPkg <= 0) continue;
            $stmtP->execute([':p'=>$idPedido, ':pkg'=>$idPkg, ':c'=>$cantPkg]);
            if (isset($mapPkg[$idPkg])) {
              $total += $mapPkg[$idPkg] * $cantPkg;
            } else {
              $precioPkg = $conn->prepare("SELECT precioEspecial FROM Paquete WHERE idPaquete = :idp");
              $precioPkg->execute([':idp' => $idPkg]);
              $total += (float)$precioPkg->fetchColumn() * $cantPkg;
            }
          }
        } catch (PDOException $e) {
          // Si la tabla no existe, ignorar paquetes (no crítico)
        }
      }

      // Actualizar el total del pedido
      $conn->prepare("UPDATE Pedido SET montoTotal = :t WHERE idPedido = :id")
         ->execute([':t'=>$total, ':id'=>$idPedido]);

            $conn->commit();
            header("Location: pedidos.php");
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Error al registrar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Nuevo pedido</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h2>Registrar nuevo pedido</h2>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Cliente</label>
      <select name="idCliente" class="form-select" required>
        <option value="">-- Seleccione cliente --</option>
        <?php foreach($clientes as $c): ?>
          <option value="<?= $c['idCliente'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="row">
      <div class="col-md-3 mb-3">
        <label class="form-label">Fecha del pedido</label>
        <input type="date" name="fechaPedido" class="form-control" value="<?= date('Y-m-d') ?>">
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Fecha del evento</label>
        <input type="date" name="fechaEvento" class="form-control" required>
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Entrega</label>
        <input type="date" name="fechaEntrega" class="form-control">
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Devolución</label>
        <input type="date" name="fechaDevolucion" class="form-control">
      </div>
    </div>

    <hr>
    <h5>Artículos del pedido</h5>

    <div id="articulos">
      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <select name="articulo[]" class="form-select" required>
            <option value="">-- Seleccione artículo --</option>
            <?php foreach($articulos as $a): ?>
              <option value="<?= $a['idArticulo'] ?>">
                <?= htmlspecialchars($a['nombre']) ?> (<?= htmlspecialchars($a['estado']) ?>)
                - $<?= number_format($a['costoRenta'],2) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <input type="number" name="cantidad[]" min="1" class="form-control" placeholder="Cantidad">
        </div>
      </div>
    </div>

    <button type="button" class="btn btn-secondary mb-3" onclick="agregar()">+ Agregar artículo</button>
    
    <hr>
    <h5>Paquetes</h5>
    <div id="paquetes">
      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <select name="paquete[]" class="form-select">
            <option value="">-- Seleccione paquete --</option>
            <?php foreach($paquetes as $pkg): ?>
              <option value="<?= $pkg['idPaquete'] ?>"><?= htmlspecialchars($pkg['nombre']) ?> - $<?= number_format($pkg['precioEspecial'],2) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <input type="number" name="cantidad_paquete[]" min="1" class="form-control" value="1">
        </div>
      </div>
    </div>
    <button type="button" class="btn btn-secondary mb-3" onclick="agregarPaquete()">+ Agregar paquete</button>

    <button class="btn btn-success">Guardar pedido</button>
    <a href="pedidos.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<script>
function agregar() {
  const cont = document.getElementById('articulos');
  const row = cont.querySelector('.row').cloneNode(true);
  row.querySelectorAll('input').forEach(i => i.value = '');
  row.querySelector('select').selectedIndex = 0;
  cont.appendChild(row);
}
function agregarPaquete() {
  const cont = document.getElementById('paquetes');
  const row = cont.querySelector('.row').cloneNode(true);
  const input = row.querySelector('input');
  if (input) input.value = 1;
  const sel = row.querySelector('select');
  if (sel) sel.selectedIndex = 0;
  cont.appendChild(row);
}
</script>
</body>
</html>
