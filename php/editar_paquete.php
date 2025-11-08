<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: paquetes.php");
    exit;
}

$errores = [];

try {
    $stmt = $conn->prepare("SELECT * FROM Paquete WHERE idPaquete = :id");
    $stmt->execute([':id' => $id]);
    $paquete = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$paquete) die("Paquete no encontrado.");

    $articulos = $conn->query("SELECT idArticulo, nombre FROM Articulo ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM PaqueteArticulo WHERE idPaquete = :id");
    $stmt->execute([':id' => $id]);
    $articulosPaquete = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar datos: " . htmlspecialchars($e->getMessage()));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precioEspecial'];
    $articulosSel = $_POST['articulo'] ?? [];
    $cantidades = $_POST['cantidad'] ?? [];

    if ($nombre === '') $errores[] = "Nombre obligatorio.";
    if ($precio === '' || $precio < 0) $errores[] = "Precio inválido.";

    if (empty($errores)) {
        try {
            $conn->beginTransaction();

            $conn->prepare("UPDATE Paquete SET nombre = :n, precioEspecial = :p WHERE idPaquete = :id")
                 ->execute([':n' => $nombre, ':p' => $precio, ':id' => $id]);

            $conn->prepare("DELETE FROM PaqueteArticulo WHERE idPaquete = :id")->execute([':id' => $id]);

            $stmtDetalle = $conn->prepare("INSERT INTO PaqueteArticulo (idPaquete, idArticulo, cantidad) VALUES (:idp, :ida, :c)");
            for ($i = 0; $i < count($articulosSel); $i++) {
                $stmtDetalle->execute([':idp' => $id, ':ida' => $articulosSel[$i], ':c' => $cantidades[$i]]);
            }

            $conn->commit();
            header("Location: paquetes.php");
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            $errores[] = "Error al actualizar: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar Paquete</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Sillas y Mesas Hernández</a>
  </div>
</nav>
<div class="container mt-4">
  <h2>Editar Paquete</h2>
  <?php if ($errores): ?>
  <div class="alert alert-danger"><ul><?php foreach($errores as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($paquete['nombre']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Precio especial</label>
      <input type="number" step="0.01" name="precioEspecial" class="form-control" value="<?= htmlspecialchars($paquete['precioEspecial']) ?>" required>
    </div>

    <hr>
    <h5>Artículos incluidos</h5>
    <div id="articulos">
      <?php foreach($articulosPaquete as $aP): ?>
      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <select name="articulo[]" class="form-select">
            <?php foreach($articulos as $a): ?>
              <option value="<?= $a['idArticulo'] ?>" <?= $a['idArticulo']==$aP['idArticulo']?'selected':'' ?>>
                <?= htmlspecialchars($a['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <input type="number" name="cantidad[]" class="form-control" value="<?= htmlspecialchars($aP['cantidad']) ?>" min="1">
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <button type="button" class="btn btn-secondary mb-3" onclick="agregar()">+ Agregar artículo</button>
    <br>
    <button class="btn btn-primary">Guardar cambios</button>
    <a href="paquetes.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
<script>
function agregar() {
  const cont = document.getElementById('articulos');
  const row = cont.querySelector('.row').cloneNode(true);
  row.querySelector('input').value = 1;
  cont.appendChild(row);
}
</script>
</body>
</html>
