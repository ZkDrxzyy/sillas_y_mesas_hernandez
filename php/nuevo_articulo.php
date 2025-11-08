<?php
require_once "conexion.php";

// Obtener el tipo de artículo de la URL
$tipo = strtolower($_GET['tipo'] ?? '');
if (!in_array($tipo, ['silla', 'mesa', 'accesorio'])) {
    header("Location: articulos.php");
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar datos comunes
    $nombre = trim($_POST['nombre'] ?? '');
    $cantidadDisponible = intval($_POST['cantidadDisponible'] ?? 0);
    $cantidadEnUso = intval($_POST['cantidadEnUso'] ?? 0);
    $cantidadDanada = intval($_POST['cantidadDanada'] ?? 0);
    $cantidadTotal = $cantidadDisponible + $cantidadEnUso + $cantidadDanada;
    $costoRenta = $_POST['costoRenta'] ?? '';

    if ($nombre === '') $errors[] = "El nombre es obligatorio.";
    if ($cantidadDisponible < 0) $errors[] = "La cantidad disponible debe ser mayor o igual a 0.";
    if ($cantidadEnUso < 0) $errors[] = "La cantidad en uso debe ser mayor o igual a 0.";
    if ($cantidadDanada < 0) $errors[] = "La cantidad dañada debe ser mayor o igual a 0.";
    if ($cantidadTotal <= 0) $errors[] = "La cantidad total debe ser mayor a 0.";
    if (!is_numeric($costoRenta) || $costoRenta < 0) $errors[] = "El costo debe ser un número válido.";

    // Validar campos específicos según el tipo
    switch ($tipo) {
        case 'silla':
            $tipoSilla = trim($_POST['tipoSilla'] ?? '');
            $material = trim($_POST['material'] ?? '');
            if (empty($tipoSilla)) $errors[] = "El tipo de silla es obligatorio.";
            if (empty($material)) $errors[] = "El material es obligatorio.";
            break;
        case 'mesa':
            $forma = trim($_POST['forma'] ?? '');
            $capacidadPersonas = intval($_POST['capacidadPersonas'] ?? 0);
            $tamano = trim($_POST['tamano'] ?? '');
            if (empty($forma)) $errors[] = "La forma es obligatoria.";
            if ($capacidadPersonas <= 0) $errors[] = "La capacidad debe ser mayor a 0.";
            if (empty($tamano)) $errors[] = "El tamaño es obligatorio.";
            break;
        case 'accesorio':
            $descripcion = trim($_POST['descripcion'] ?? '');
            $fragilidad = trim($_POST['fragilidad'] ?? '');
            if (empty($descripcion)) $errors[] = "La descripción es obligatoria.";
            if (empty($fragilidad)) $errors[] = "La fragilidad es obligatoria.";
            break;
    }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();

            // Insertar en la tabla Articulo
            $sql = "INSERT INTO Articulo (nombre, cantidadDisponible, cantidadEnUso, cantidadDanada, cantidadTotal, costoRenta) 
                   VALUES (:nombre, :disponible, :enUso, :danada, :total, :costoRenta)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':disponible' => $cantidadDisponible,
                ':enUso' => $cantidadEnUso,
                ':danada' => $cantidadDanada,
                ':total' => $cantidadTotal,
                ':costoRenta' => $costoRenta
            ]);
            
            $idArticulo = $conn->lastInsertId();

            // Insertar en la tabla específica según el tipo
            switch ($tipo) {
                case 'silla':
                    $sql = "INSERT INTO Silla (idArticulo, tipoSilla, material) 
                           VALUES (:idArticulo, :tipoSilla, :material)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':idArticulo' => $idArticulo,
                        ':tipoSilla' => $tipoSilla,
                        ':material' => $material
                    ]);
                    break;
                case 'mesa':
                    $sql = "INSERT INTO Mesa (idArticulo, forma, capacidadPersonas, tamaño) 
                           VALUES (:idArticulo, :forma, :capacidadPersonas, :tamano)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':idArticulo' => $idArticulo,
                        ':forma' => $forma,
                        ':capacidadPersonas' => $capacidadPersonas,
                        ':tamano' => $tamano
                    ]);
                    break;
                case 'accesorio':
                    $sql = "INSERT INTO Accesorio (idArticulo, descripcion, fragilidad) 
                           VALUES (:idArticulo, :descripcion, :fragilidad)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':idArticulo' => $idArticulo,
                        ':descripcion' => $descripcion,
                        ':fragilidad' => $fragilidad
                    ]);
                    break;
            }

            $conn->commit();
            header("Location: articulos.php");
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Error al insertar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Nuevo artículo - Sillas y Mesas Hernández</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h2>Registrar nuevo artículo</h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach($errors as $err) echo "<li>".htmlspecialchars($err)."</li>"; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <!-- Campos comunes para todos los tipos de artículos -->
    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input class="form-control" name="nombre" value="<?= isset($nombre) ? htmlspecialchars($nombre) : '' ?>" required>
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label text-success">Cantidad Disponible</label>
          <input type="number" min="0" class="form-control" name="cantidadDisponible" 
                 value="<?= isset($cantidadDisponible) ? (int)$cantidadDisponible : 0 ?>" required>
        </div>
      </div>
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label text-primary">Cantidad En Uso</label>
          <input type="number" min="0" class="form-control" name="cantidadEnUso" 
                 value="<?= isset($cantidadEnUso) ? (int)$cantidadEnUso : 0 ?>" required>
        </div>
      </div>
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label text-danger">Cantidad Dañada</label>
          <input type="number" min="0" class="form-control" name="cantidadDanada" 
                 value="<?= isset($cantidadDanada) ? (int)$cantidadDanada : 0 ?>" required>
        </div>
      </div>
    </div>
    <div class="mb-3">
      <label class="form-label text-muted">Cantidad Total (calculada automáticamente)</label>
      <input type="text" class="form-control" id="cantidadTotal" readonly>
    </div>

    <script>
    function actualizarTotal() {
      const disp = parseInt(document.querySelector('[name="cantidadDisponible"]').value) || 0;
      const uso = parseInt(document.querySelector('[name="cantidadEnUso"]').value) || 0;
      const danada = parseInt(document.querySelector('[name="cantidadDanada"]').value) || 0;
      document.getElementById('cantidadTotal').value = disp + uso + danada;
    }
    document.querySelectorAll('input[type="number"]').forEach(input => {
      input.addEventListener('input', actualizarTotal);
    });
    actualizarTotal(); // Calcular total inicial
    </script>

    <div class="mb-3">
      <label class="form-label">Costo de renta ($)</label>
      <input type="number" step="0.01" min="0" class="form-control" name="costoRenta" 
             value="<?= isset($costoRenta)? htmlspecialchars($costoRenta) : '0.00' ?>" required>
    </div>

    <!-- Campos específicos según el tipo de artículo -->
    <?php if ($tipo === 'silla'): ?>
      <div class="mb-3">
        <label class="form-label">Tipo de Silla</label>
        <input class="form-control" name="tipoSilla" 
               value="<?= isset($tipoSilla) ? htmlspecialchars($tipoSilla) : '' ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Material</label>
        <input class="form-control" name="material" 
               value="<?= isset($material) ? htmlspecialchars($material) : '' ?>" required>
      </div>
    <?php elseif ($tipo === 'mesa'): ?>
      <div class="mb-3">
        <label class="form-label">Forma</label>
        <input class="form-control" name="forma" 
               value="<?= isset($forma) ? htmlspecialchars($forma) : '' ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Capacidad (personas)</label>
        <input type="number" min="1" class="form-control" name="capacidadPersonas" 
               value="<?= isset($capacidadPersonas) ? (int)$capacidadPersonas : 1 ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Tamaño</label>
        <input class="form-control" name="tamano" 
               value="<?= isset($tamano) ? htmlspecialchars($tamano) : '' ?>" required>
      </div>
    <?php elseif ($tipo === 'accesorio'): ?>
      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea class="form-control" name="descripcion" required><?= isset($descripcion) ? htmlspecialchars($descripcion) : '' ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Fragilidad</label>
        <select class="form-select" name="fragilidad">
          <?php $sel = $fragilidad ?? 'Baja'; ?>
          <option <?= $sel=='Baja'?'selected':'' ?>>Baja</option>
          <option <?= $sel=='Media'?'selected':'' ?>>Media</option>
          <option <?= $sel=='Alta'?'selected':'' ?>>Alta</option>
        </select>
      </div>
    <?php endif; ?>

    <button class="btn btn-success" type="submit">Guardar</button>
    <a class="btn btn-secondary" href="articulos.php">Cancelar</a>
  </form>
</div>
</body>
</html>
