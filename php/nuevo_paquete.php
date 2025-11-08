<?php
require_once "conexion.php";

$errores = [];
$nombre = "";
$precioEspecial = "";
$articulosSeleccionados = [];

try {
    $articulos = $conn->query("SELECT idArticulo, nombre FROM Articulo ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar artículos: " . htmlspecialchars($e->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $precioEspecial = $_POST['precioEspecial'];
    $articulosSeleccionados = $_POST['articulo'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];

    if ($nombre === '') $errores[] = "El nombre del paquete es obligatorio.";
    if ($precioEspecial === '' || $precioEspecial < 0) $errores[] = "El precio especial es inválido.";
    if (empty($articulosSeleccionados)) $errores[] = "Debe seleccionar al menos un artículo.";

    if (empty($errores)) {
        try {
            $conn->beginTransaction();

            $stmt = $conn->prepare("INSERT INTO Paquete (nombre, precioEspecial) VALUES (:n, :p)");
            $stmt->execute([':n' => $nombre, ':p' => $precioEspecial]);
            $idPaquete = $conn->lastInsertId();

            $stmtDetalle = $conn->prepare("INSERT INTO PaqueteArticulo (idPaquete, idArticulo, cantidad) VALUES (:idp, :ida, :c)");
            for ($i = 0; $i < count($articulosSeleccionados); $i++) {
                $idArticulo = $articulosSeleccionados[$i];
                $cantidad = $cantidades[$i];
                if ($cantidad > 0) {
                    $stmtDetalle->execute([':idp' => $idPaquete, ':ida' => $idArticulo, ':c' => $cantidad]);
                }
            }

            $conn->commit();
            header("Location: paquetes.php");
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            $errores[] = "Error al guardar el paquete: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Nuevo Paquete</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Sillas y Mesas Hernández</a>
  </div>
</nav>

<div class="container mt-4">
  <h2>Registrar Nuevo Paquete</h2>
  <?php if ($errores): ?>
  <div class="alert alert-danger"><ul><?php foreach($errores as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Nombre del paquete</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($nombre) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Precio especial</label>
      <input type="number" step="0.01" name="precioEspecial" class="form-control" value="<?= htmlspecialchars($precioEspecial) ?>" required>
    </div>

    <hr>
    <h5>Artículos incluidos</h5>
    <div id="articulos">
      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <select name="articulo[]" class="form-select">
            <?php foreach($articulos as $a): ?>
              <option value="<?= $a['idArticulo'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <input type="number" name="cantidad[]" class="form-control" min="1" value="1">
        </div>
      </div>
    </div>

    <button type="button" class="btn btn-secondary mb-3" onclick="agregar()">+ Agregar artículo</button>
    <br>
    <button class="btn btn-success">Guardar</button>
    <a href="paquetes.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>

<script>
function agregar() {
  const cont = document.getElementById('articulos');
  const fila = cont.querySelector('.row').cloneNode(true);
  fila.querySelector('input').value = 1;
  fila.querySelector('select').selectedIndex = 0;
  cont.appendChild(fila);
}
</script>
</body>
</html>
