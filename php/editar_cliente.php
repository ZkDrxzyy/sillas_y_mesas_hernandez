<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: clientes.php");
    exit;
}

try {
    $sql = "
        SELECT C.*, 
               CP.fechaNacimiento, CP.CURP, 
               CE.razonSocial, CE.RFC, CE.contactoEmpresa,
               CASE WHEN CP.idCliente IS NOT NULL THEN 'Particular'
                    WHEN CE.idCliente IS NOT NULL THEN 'Empresa'
                    ELSE 'Sin tipo' END AS tipo
        FROM Cliente C
        LEFT JOIN ClienteParticular CP ON C.idCliente = CP.idCliente
        LEFT JOIN ClienteEmpresa CE ON C.idCliente = CE.idCliente
        WHERE C.idCliente = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cliente) {
        header("Location: clientes.php");
        exit;
    }
  // Obtener teléfonos asociados (como cadena separada por comas)
  $stmtTel = $conn->prepare("SELECT GROUP_CONCAT(numero SEPARATOR ', ') AS telefonos FROM Telefono WHERE idCliente = :id");
  $stmtTel->execute([':id' => $id]);
  $telRow = $stmtTel->fetch(PDO::FETCH_ASSOC);
  $cliente['telefonos'] = $telRow ? $telRow['telefonos'] : '';
} catch (PDOException $e) {
    die("Error al cargar cliente: " . htmlspecialchars($e->getMessage()));
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $tipoNuevo = $_POST['tipo'] ?? '';

  $telefono = trim($_POST['telefono'] ?? '');

    if ($nombre === '') $errors[] = "El nombre es obligatorio.";
    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = "Correo no válido.";
  if ($telefono !== '' && !preg_match('/^[0-9+\-\s\(\),]+$/', $telefono)) {
    $errors[] = "El teléfono contiene caracteres inválidos.";
  }
    if (!in_array($tipoNuevo, ['Particular', 'Empresa'])) $errors[] = "Tipo de cliente inválido.";

    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare("UPDATE Cliente SET nombre = :n, direccion = :d, correo = :c WHERE idCliente = :id");
            $stmt->execute([':n'=>$nombre, ':d'=>$direccion, ':c'=>$correo, ':id'=>$id]);

            // eliminar subtipos previos
            $conn->prepare("DELETE FROM ClienteParticular WHERE idCliente = :id")->execute([':id'=>$id]);
            $conn->prepare("DELETE FROM ClienteEmpresa WHERE idCliente = :id")->execute([':id'=>$id]);

            // insertar en el subtipo nuevo
            if ($tipoNuevo === 'Particular') {
                $fechaNac = $_POST['fechaNacimiento'] ?? null;
                $curp = trim($_POST['curp'] ?? '');
                $stmt2 = $conn->prepare("INSERT INTO ClienteParticular (idCliente, fechaNacimiento, CURP) VALUES (:id, :f, :curp)");
                $stmt2->execute([':id'=>$id, ':f'=>$fechaNac, ':curp'=>$curp]);
            } elseif ($tipoNuevo === 'Empresa') {
                $razon = trim($_POST['razonSocial'] ?? '');
                $rfc = trim($_POST['rfc'] ?? '');
                $contacto = trim($_POST['contacto'] ?? '');
                $stmt3 = $conn->prepare("INSERT INTO ClienteEmpresa (idCliente, razonSocial, RFC, contactoEmpresa) VALUES (:id, :razon, :rfc, :contacto)");
                $stmt3->execute([':id'=>$id, ':razon'=>$razon, ':rfc'=>$rfc, ':contacto'=>$contacto]);
            }

      // Actualizar teléfonos: eliminamos los previos y agregamos los nuevos (soporte para varios separados por comas)
      $conn->prepare("DELETE FROM Telefono WHERE idCliente = :id")->execute([':id'=>$id]);
      if (!empty($telefono)) {
        $nums = array_map('trim', explode(',', $telefono));
        $stmtTelIns = $conn->prepare("INSERT INTO Telefono (idCliente, numero) VALUES (:id, :num)");
        foreach ($nums as $num) {
          if ($num === '') continue;
          $stmtTelIns->execute([':id' => $id, ':num' => $num]);
        }
      }

            $conn->commit();
            header("Location: clientes.php");
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
  <title>Editar cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
  function mostrarCampos() {
    const tipo = document.getElementById('tipo').value;
    document.getElementById('particular').style.display = tipo==='Particular' ? 'block' : 'none';
    document.getElementById('empresa').style.display = tipo==='Empresa' ? 'block' : 'none';
  }
  </script>
</head>
<body class="bg-light" onload="mostrarCampos()">
<div class="container mt-4">
  <h2>Editar cliente #<?= htmlspecialchars($cliente['idCliente']) ?></h2>

  <?php if ($errors): ?>
    <div class="alert alert-danger"><ul><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input class="form-control" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? $cliente['nombre']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Dirección</label>
      <input class="form-control" name="direccion" value="<?= htmlspecialchars($_POST['direccion'] ?? $cliente['direccion']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Correo electrónico</label>
      <input type="email" class="form-control" name="correo" value="<?= htmlspecialchars($_POST['correo'] ?? $cliente['correo']) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label">Teléfono(s)</label>
      <input class="form-control" name="telefono" placeholder="Ej: 5512345678 o 5512345678, 5523456789" value="<?= htmlspecialchars($_POST['telefono'] ?? $cliente['telefonos']) ?>">
      <div class="form-text">Puedes agregar varios separados por comas.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">Tipo de cliente</label>
      <?php $tipoSel = $_POST['tipo'] ?? $cliente['tipo']; ?>
      <select name="tipo" id="tipo" class="form-select" onchange="mostrarCampos()" required>
        <option value="Particular" <?= $tipoSel==='Particular'?'selected':'' ?>>Particular</option>
        <option value="Empresa" <?= $tipoSel==='Empresa'?'selected':'' ?>>Empresa</option>
      </select>
    </div>

    <div id="particular" style="display:none;">
      <h5>Datos particulares</h5>
      <div class="mb-3">
        <label class="form-label">Fecha de nacimiento</label>
        <input type="date" class="form-control" name="fechaNacimiento" value="<?= htmlspecialchars($_POST['fechaNacimiento'] ?? $cliente['fechaNacimiento']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">CURP</label>
        <input class="form-control" name="curp" maxlength="18" value="<?= htmlspecialchars($_POST['curp'] ?? $cliente['CURP']) ?>">
      </div>
    </div>

    <div id="empresa" style="display:none;">
      <h5>Datos de empresa</h5>
      <div class="mb-3">
        <label class="form-label">Razón social</label>
        <input class="form-control" name="razonSocial" value="<?= htmlspecialchars($_POST['razonSocial'] ?? $cliente['razonSocial']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">RFC</label>
        <input class="form-control" name="rfc" maxlength="13" value="<?= htmlspecialchars($_POST['rfc'] ?? $cliente['RFC']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Contacto</label>
        <input class="form-control" name="contacto" value="<?= htmlspecialchars($_POST['contacto'] ?? $cliente['contactoEmpresa']) ?>">
      </div>
    </div>

    <button class="btn btn-primary">Actualizar</button>
    <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
