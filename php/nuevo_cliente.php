<?php
require_once "conexion.php";

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
  $telefono = trim($_POST['telefono'] ?? '');
    $tipo = $_POST['tipo'] ?? '';
    
    if ($nombre === '') $errors[] = "El nombre es obligatorio.";
    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = "Correo no válido.";
  // validar teléfono (permitir dígitos, espacios, +, -, paréntesis y comas si se envían varios)
  if ($telefono !== '' && !preg_match('/^[0-9+\-\s\(\),]+$/', $telefono)) {
    $errors[] = "El teléfono contiene caracteres inválidos.";
  }

    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare("INSERT INTO Cliente (nombre, direccion, correo) VALUES (:n, :d, :c)");
            $stmt->execute([':n'=>$nombre, ':d'=>$direccion, ':c'=>$correo]);
            $id = $conn->lastInsertId();

      // Insertar números de teléfono (si se proporcionaron). Se permiten varios separados por comas.
      if ($telefono !== '') {
        $nums = array_map('trim', explode(',', $telefono));
        $stmtTel = $conn->prepare("INSERT INTO Telefono (idCliente, numero) VALUES (:id, :num)");
        foreach ($nums as $num) {
          if ($num === '') continue;
          $stmtTel->execute([':id' => $id, ':num' => $num]);
        }
      }

            if ($tipo === 'Particular') {
                $fechaNac = $_POST['fechaNacimiento'] ?? null;
                $curp = trim($_POST['curp'] ?? '');
                $stmt2 = $conn->prepare("INSERT INTO ClienteParticular (idCliente, fechaNacimiento, CURP) VALUES (:id, :f, :curp)");
                $stmt2->execute([':id'=>$id, ':f'=>$fechaNac, ':curp'=>$curp]);
            } elseif ($tipo === 'Empresa') {
                $razon = trim($_POST['razonSocial'] ?? '');
                $rfc = trim($_POST['rfc'] ?? '');
                $contacto = trim($_POST['contacto'] ?? '');
                $stmt2 = $conn->prepare("INSERT INTO ClienteEmpresa (idCliente, razonSocial, RFC, contactoEmpresa) VALUES (:id, :razon, :rfc, :contacto)");
                $stmt2->execute([':id'=>$id, ':razon'=>$razon, ':rfc'=>$rfc, ':contacto'=>$contacto]);
            }

            $conn->commit();
            header("Location: clientes.php");
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
  <title>Nuevo cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
  function mostrarCampos() {
    const tipo = document.getElementById('tipo').value;
    document.getElementById('particular').style.display = tipo==='Particular' ? 'block' : 'none';
    document.getElementById('empresa').style.display = tipo==='Empresa' ? 'block' : 'none';
  }
  </script>
</head>
<body class="bg-light">
<div class="container mt-4">
  <h2>Registrar nuevo cliente</h2>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul><?php foreach($errors as $e) echo "<li>".htmlspecialchars($e)."</li>"; ?></ul>
    </div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label class="form-label">Nombre</label>
      <input class="form-control" name="nombre" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Dirección</label>
      <input class="form-control" name="direccion">
    </div>

    <div class="mb-3">
      <label class="form-label">Correo electrónico</label>
      <input type="email" class="form-control" name="correo">
    </div>

    <div class="mb-3">
      <label class="form-label">Teléfono(s)</label>
      <input class="form-control" name="telefono" placeholder="Ej: 5512345678 o 5512345678, 5523456789" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
      <div class="form-text">Puedes agregar varios separados por comas.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">Tipo de cliente</label>
      <select name="tipo" id="tipo" class="form-select" onchange="mostrarCampos()" required>
        <option value="">-- Seleccione --</option>
        <option value="Particular">Particular</option>
        <option value="Empresa">Empresa</option>
      </select>
    </div>

    <div id="particular" style="display:none;">
      <h5>Datos particulares</h5>
      <div class="mb-3">
        <label class="form-label">Fecha de nacimiento</label>
        <input type="date" class="form-control" name="fechaNacimiento">
      </div>
      <div class="mb-3">
        <label class="form-label">CURP</label>
        <input class="form-control" name="curp" maxlength="18">
      </div>
    </div>

    <div id="empresa" style="display:none;">
      <h5>Datos de empresa</h5>
      <div class="mb-3">
        <label class="form-label">Razón social</label>
        <input class="form-control" name="razonSocial">
      </div>
      <div class="mb-3">
        <label class="form-label">RFC</label>
        <input class="form-control" name="rfc" maxlength="13">
      </div>
      <div class="mb-3">
        <label class="form-label">Contacto</label>
        <input class="form-control" name="contacto">
      </div>
    </div>

    <button class="btn btn-success">Guardar</button>
    <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
