<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: pedidos.php");
    exit;
}

$errors = [];

try {
    $pedidoStmt = $conn->prepare("SELECT * FROM Pedido WHERE idPedido = :id");
    $pedidoStmt->execute([':id'=>$id]);
    $p = $pedidoStmt->fetch(PDO::FETCH_ASSOC);
    if (!$p) {
        die("Pedido no encontrado.");
    }

  $clientes = $conn->query("SELECT idCliente, nombre FROM Cliente ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
  $articulos = $conn->query("SELECT idArticulo, nombre, costoRenta FROM Articulo ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
  // Cargar paquetes disponibles
  $paquetes = $conn->query("SELECT idPaquete, nombre, precioEspecial FROM Paquete ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

  $detallesStmt = $conn->prepare("SELECT * FROM DetallePedido WHERE idPedido = :id");
  $detallesStmt->execute([':id'=>$id]);
  $items = $detallesStmt->fetchAll(PDO::FETCH_ASSOC);
  // Cargar paquetes ya asociados a este pedido (si existe la tabla)
  try {
    $detallesPaqueteStmt = $conn->prepare("SELECT * FROM DetallePedidoPaquete WHERE idPedido = :id");
    $detallesPaqueteStmt->execute([':id'=>$id]);
    $paquetesPedido = $detallesPaqueteStmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    // Si la tabla no existe, simplemente usar array vacío
    $paquetesPedido = [];
  }
} catch (PDOException $e) {
    die("Error al cargar datos: " . htmlspecialchars($e->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idCliente = $_POST['idCliente'];
    $fechaEvento = $_POST['fechaEvento'] ?? $p['fechaEvento'];
    $fechaEntrega = $_POST['fechaEntrega'] ?? $p['fechaEntrega'];
    $fechaDevolucion = $_POST['fechaDevolucion'] ?? $p['fechaDevolucion'];
  $articulo = $_POST['articulo'] ?? [];
  $cantidad = $_POST['cantidad'] ?? [];
  // Nuevos campos para paquetes
  $paquete = $_POST['paquete'] ?? [];
  $cantidadPaquete = $_POST['cantidad_paquete'] ?? [];

  if ($idCliente === '') $errors[] = "Seleccione cliente.";
  if (empty($articulo) && empty($paquete)) $errors[] = "Debe incluir al menos un artículo o un paquete.";

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            // Actualiza campos de fechas y cliente (montoTotal se recalculará)
            $conn->prepare("UPDATE Pedido SET idCliente=:c, fechaEvento=:fe, fechaEntrega=:fent, fechaDevolucion=:fdev WHERE idPedido=:id")
                 ->execute([':c'=>$idCliente, ':fe'=>$fechaEvento, ':fent'=>$fechaEntrega, ':fdev'=>$fechaDevolucion, ':id'=>$id]);

      // Borrar detalles actuales y reinsertar (artículos)
      $conn->prepare("DELETE FROM DetallePedido WHERE idPedido = :id")->execute([':id'=>$id]);

      $total = 0;
      // Map de precios de paquetes para evitar queries repetidas
      $mapPaquetePrecio = [];
      foreach ($paquetes as $pck) {
        $mapPaquetePrecio[$pck['idPaquete']] = (float)$pck['precioEspecial'];
      }

      for ($i=0; $i<count($articulo); $i++) {
        $a = intval($articulo[$i]);
        $cant = intval($cantidad[$i]);
        if ($cant <= 0) continue;

        // obtener costoRenta
        $precio = $conn->prepare("SELECT costoRenta FROM Articulo WHERE idArticulo = :a");
        $precio->execute([':a'=>$a]);
        $costo = (float)$precio->fetchColumn();
        $subtotal = $costo * $cant;
        $total += $subtotal;

        $stmt = $conn->prepare("INSERT INTO DetallePedido (idPedido, idArticulo, cantidad) VALUES (:id, :a, :c)");
        $stmt->execute([':id'=>$id, ':a'=>$a, ':c'=>$cant]);
      }

      // Manejo de paquetes: eliminar y reinsertar en DetallePedidoPaquete (si existe la tabla)
      try {
        $conn->prepare("DELETE FROM DetallePedidoPaquete WHERE idPedido = :id")->execute([':id' => $id]);
        $stmtP = $conn->prepare("INSERT INTO DetallePedidoPaquete (idPedido, idPaquete, cantidad) VALUES (:idp, :idpkg, :c)");
        for ($i = 0; $i < count($paquete); $i++) {
          $idPkg = intval($paquete[$i]);
          $cantPkg = intval($cantidadPaquete[$i]);
          if ($cantPkg <= 0) continue;
          $stmtP->execute([':idp' => $id, ':idpkg' => $idPkg, ':c' => $cantPkg]);
          // sumar al total usando el map (si disponible)
          if (isset($mapPaquetePrecio[$idPkg])) {
            $total += $mapPaquetePrecio[$idPkg] * $cantPkg;
          } else {
            // fallback: consultar precio si no está en el map
            $pstmt = $conn->prepare("SELECT precioEspecial FROM Paquete WHERE idPaquete = :idp");
            $pstmt->execute([':idp' => $idPkg]);
            $precioPkg = (float)$pstmt->fetchColumn();
            $total += $precioPkg * $cantPkg;
          }
        }
      } catch (PDOException $e) {
        // Si la tabla no existe, omitir paquetes (no crítico)
      }

      $conn->prepare("UPDATE Pedido SET montoTotal = :t WHERE idPedido = :id")->execute([':t'=>$total, ':id'=>$id]);

            $conn->commit();
            header("Location: pedidos.php");
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Error al actualizar: ". $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar pedido #<?= htmlspecialchars($id) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h2>Editar Pedido #<?= htmlspecialchars($id) ?></h2>
  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Cliente</label>
      <select name="idCliente" class="form-select" required>
        <?php foreach($clientes as $c): ?>
          <option value="<?= $c['idCliente'] ?>" <?= $p['idCliente']==$c['idCliente']?'selected':'' ?>>
            <?= htmlspecialchars($c['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="row">
      <div class="col-md-3 mb-3">
        <label class="form-label">Fecha del pedido</label>
        <input type="date" name="fechaPedido" class="form-control" value="<?= htmlspecialchars($p['fechaPedido']) ?>" disabled>
        <small class="text-muted">La fecha de creación no puede modificarse aquí.</small>
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Fecha del evento</label>
        <input type="date" name="fechaEvento" class="form-control" value="<?= htmlspecialchars($p['fechaEvento']) ?>">
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Entrega</label>
        <input type="date" name="fechaEntrega" class="form-control" value="<?= htmlspecialchars($p['fechaEntrega']) ?>">
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label">Devolución</label>
        <input type="date" name="fechaDevolucion" class="form-control" value="<?= htmlspecialchars($p['fechaDevolucion']) ?>">
      </div>
    </div>

    <hr>
    <h5>Artículos</h5>
    <div id="articulos">
      <?php
        // Mostrar los items actuales; si se envió POST fallida, preferir los POST
        $currentItems = !empty($_POST['articulo']) ? $_POST : null;
        if (!empty($items) && empty($currentItems)):
          foreach($items as $it): ?>
          <div class="row g-2 mb-2">
            <div class="col-md-6">
              <select name="articulo[]" class="form-select">
                <?php foreach($articulos as $a): ?>
                  <option value="<?= $a['idArticulo'] ?>" <?= $a['idArticulo']==$it['idArticulo']?'selected':'' ?>>
                    <?= htmlspecialchars($a['nombre']) ?> ($<?= number_format($a['costoRenta'],2) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <input type="number" class="form-control" name="cantidad[]" min="1" value="<?= htmlspecialchars($it['cantidad']) ?>">
            </div>
          </div>
      <?php endforeach;
        else:
          // si no hay items o reenvío, creamos una fila vacía
      ?>
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <select name="articulo[]" class="form-select">
              <?php foreach($articulos as $a): ?>
                <option value="<?= $a['idArticulo'] ?>"><?= htmlspecialchars($a['nombre']) ?> ($<?= number_format($a['costoRenta'],2) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <input type="number" class="form-control" name="cantidad[]" min="1" value="1">
          </div>
        </div>
      <?php endif; ?>
    </div>

    <button type="button" class="btn btn-secondary mb-3" onclick="agregar()">+ Agregar artículo</button>
    <button type="button" class="btn btn-secondary mb-3" onclick="agregar()">+ Agregar artículo</button>

    <hr>
    <h5>Paquetes</h5>
    <div id="paquetes">
      <?php
        // Mostrar paquetes actuales asociados al pedido; si POST con reenvío, preferir POST
        $currentPkg = !empty($_POST['paquete']) ? $_POST : null;
        if (!empty($paquetesPedido) && empty($currentPkg)):
          foreach($paquetesPedido as $pp): ?>
          <div class="row g-2 mb-2">
            <div class="col-md-6">
              <select name="paquete[]" class="form-select">
                <?php foreach($paquetes as $pkg): ?>
                  <option value="<?= $pkg['idPaquete'] ?>" <?= $pkg['idPaquete']==$pp['idPaquete']?'selected':'' ?>>
                    <?= htmlspecialchars($pkg['nombre']) ?> ($<?= number_format($pkg['precioEspecial'],2) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <input type="number" class="form-control" name="cantidad_paquete[]" min="1" value="<?= htmlspecialchars($pp['cantidad']) ?>">
            </div>
          </div>
      <?php endforeach;
        else:
      ?>
        <div class="row g-2 mb-2">
          <div class="col-md-6">
            <select name="paquete[]" class="form-select">
              <?php foreach($paquetes as $pkg): ?>
                <option value="<?= $pkg['idPaquete'] ?>"><?= htmlspecialchars($pkg['nombre']) ?> ($<?= number_format($pkg['precioEspecial'],2) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <input type="number" class="form-control" name="cantidad_paquete[]" min="1" value="1">
          </div>
        </div>
      <?php endif; ?>
    </div>
    <button type="button" class="btn btn-secondary mb-3" onclick="agregarPaquete()">+ Agregar paquete</button>
<br /><br />
    <button class="btn btn-primary">Guardar cambios</button>
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
  // establecer cantidad por defecto a 1
  const input = row.querySelector('input');
  if (input) input.value = 1;
  const sel = row.querySelector('select');
  if (sel) sel.selectedIndex = 0;
  cont.appendChild(row);
}
</script>
</body>
</html>