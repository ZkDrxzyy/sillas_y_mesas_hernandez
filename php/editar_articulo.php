<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$tipo = strtolower($_GET['tipo'] ?? '');

if ($id <= 0 || !in_array($tipo, ['silla', 'mesa', 'accesorio'])) {
    header("Location: articulos.php");
    exit;
}

try {
    // Obtener datos básicos del artículo
    $stmt = $conn->prepare("SELECT * FROM Articulo WHERE idArticulo = :id");
    $stmt->execute([':id' => $id]);
    $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$articulo) {
        header("Location: articulos.php");
        exit;
    }

    // Obtener datos específicos según el tipo
    switch ($tipo) {
        case 'silla':
            $stmt = $conn->prepare("SELECT * FROM Silla WHERE idArticulo = :id");
            break;
        case 'mesa':
            $stmt = $conn->prepare("SELECT * FROM Mesa WHERE idArticulo = :id");
            break;
        case 'accesorio':
            $stmt = $conn->prepare("SELECT * FROM Accesorio WHERE idArticulo = :id");
            break;
    }
    $stmt->execute([':id' => $id]);
    $detalles = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$detalles) {
        header("Location: articulos.php");
        exit;
    }
    
    // Combinar datos básicos con específicos
    $articulo = array_merge($articulo, $detalles);
    
} catch (PDOException $e) {
    echo "Error: ".htmlspecialchars($e->getMessage());
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar datos básicos
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

    // Validar datos específicos según el tipo
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

            // Actualizar tabla Articulo
            $sql = "UPDATE Articulo SET nombre = :nombre, cantidadDisponible = :disponible, 
                   cantidadEnUso = :enUso, cantidadDanada = :danada, cantidadTotal = :total,
                   costoRenta = :costoRenta WHERE idArticulo = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nombre' => $nombre,
                ':disponible' => $cantidadDisponible,
                ':enUso' => $cantidadEnUso,
                ':danada' => $cantidadDanada,
                ':total' => $cantidadTotal,
                ':costoRenta' => $costoRenta,
                ':id' => $id
            ]);

            // Actualizar tabla específica
            switch ($tipo) {
                case 'silla':
                    $sql = "UPDATE Silla SET tipoSilla = :tipoSilla, material = :material WHERE idArticulo = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':tipoSilla' => $tipoSilla,
                        ':material' => $material,
                        ':id' => $id
                    ]);
                    break;
                case 'mesa':
                    $sql = "UPDATE Mesa SET forma = :forma, capacidadPersonas = :capacidadPersonas, tamaño = :tamano 
                           WHERE idArticulo = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':forma' => $forma,
                        ':capacidadPersonas' => $capacidadPersonas,
                        ':tamano' => $tamano,
                        ':id' => $id
                    ]);
                    break;
                case 'accesorio':
                    $sql = "UPDATE Accesorio SET descripcion = :descripcion, fragilidad = :fragilidad 
                           WHERE idArticulo = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':descripcion' => $descripcion,
                        ':fragilidad' => $fragilidad,
                        ':id' => $id
                    ]);
                    break;
            }

            $conn->commit();
            header("Location: articulos.php");
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Error al actualizar: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Editar artículo - Sillas y Mesas Hernández</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
  <h2>Editar artículo #<?= htmlspecialchars($articulo['idArticulo']) ?></h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach($errors as $err) echo "<li>".htmlspecialchars($err)."</li>"; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <!-- Campos comunes -->
    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input class="form-control" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? $articulo['nombre']) ?>" required>
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label text-success">Cantidad Disponible</label>
          <input type="number" min="0" class="form-control" name="cantidadDisponible" 
                 value="<?= htmlspecialchars($_POST['cantidadDisponible'] ?? $articulo['cantidadDisponible']) ?>" required>
        </div>
      </div>
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label text-primary">Cantidad En Uso</label>
          <input type="number" min="0" class="form-control" name="cantidadEnUso" 
                 value="<?= htmlspecialchars($_POST['cantidadEnUso'] ?? $articulo['cantidadEnUso']) ?>" required>
        </div>
      </div>
      <div class="col-md-4">
        <div class="mb-3">
          <label class="form-label text-danger">Cantidad Dañada</label>
          <input type="number" min="0" class="form-control" name="cantidadDanada" 
                 value="<?= htmlspecialchars($_POST['cantidadDanada'] ?? $articulo['cantidadDanada']) ?>" required>
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
             value="<?= htmlspecialchars($_POST['costoRenta'] ?? $articulo['costoRenta']) ?>" required>
    </div>

    <!-- Campos específicos según el tipo -->
    <?php if ($tipo === 'silla'): ?>
      <div class="mb-3">
        <label class="form-label">Tipo de Silla</label>
        <input class="form-control" name="tipoSilla" 
               value="<?= htmlspecialchars($_POST['tipoSilla'] ?? $articulo['tipoSilla']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Material</label>
        <input class="form-control" name="material" 
               value="<?= htmlspecialchars($_POST['material'] ?? $articulo['material']) ?>" required>
      </div>
    <?php elseif ($tipo === 'mesa'): ?>
      <div class="mb-3">
        <label class="form-label">Forma</label>
        <input class="form-control" name="forma" 
               value="<?= htmlspecialchars($_POST['forma'] ?? $articulo['forma']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Capacidad (personas)</label>
        <input type="number" min="1" class="form-control" name="capacidadPersonas" 
               value="<?= htmlspecialchars($_POST['capacidadPersonas'] ?? $articulo['capacidadPersonas']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Tamaño</label>
        <input class="form-control" name="tamano" 
               value="<?= htmlspecialchars($_POST['tamano'] ?? $articulo['tamaño']) ?>" required>
      </div>
    <?php elseif ($tipo === 'accesorio'): ?>
      <div class="mb-3">
        <label class="form-label">Descripción</label>
        <textarea class="form-control" name="descripcion" required><?= htmlspecialchars($_POST['descripcion'] ?? $articulo['descripcion']) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Fragilidad</label>
        <?php $sel = $_POST['fragilidad'] ?? $articulo['fragilidad']; ?>
        <select class="form-select" name="fragilidad">
          <option <?= $sel=='Baja'?'selected':'' ?>>Baja</option>
          <option <?= $sel=='Media'?'selected':'' ?>>Media</option>
          <option <?= $sel=='Alta'?'selected':'' ?>>Alta</option>
        </select>
      </div>
    <?php endif; ?>

    <button class="btn btn-primary" type="submit">Actualizar</button>
    <a class="btn btn-secondary" href="articulos.php">Cancelar</a>
  </form>
</div>
</body>
</html>
