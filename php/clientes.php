<?php
require_once "conexion.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Clientes - Sillas y Mesas Hernández</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Sillas y Mesas Hernández</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
		<li class="nav-item"><a class="nav-link" href="articulos.php">Artículos</a></li>
        <li class="nav-item"><a class="nav-link active" href="clientes.php">Clientes</a></li>
        <li class="nav-item"><a class="nav-link" href="pedidos.php">Pedidos</a></li>
        <li class="nav-item"><a class="nav-link" href="pagos.php">Pagos</a></li>
        <li class="nav-item"><a class="nav-link" href="paquetes.php">Paquetes</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="mb-4">Clientes registrados</h2>
  <a class="btn btn-success mb-3" href="nuevo_cliente.php">+ Nuevo cliente</a>

  <?php
  try {
      // Obtenemos los datos del cliente y, mediante una subconsulta, concatenamos
      // los teléfonos asociados (si los hay) en una sola cadena.
      $sql = "
        SELECT C.idCliente, C.nombre, C.direccion, C.correo,
               (SELECT GROUP_CONCAT(T.numero SEPARATOR ', ') FROM Telefono T WHERE T.idCliente = C.idCliente) AS telefonos,
               CASE 
                 WHEN CP.idCliente IS NOT NULL THEN 'Particular'
                 WHEN CE.idCliente IS NOT NULL THEN 'Empresa'
                 ELSE 'Sin tipo'
               END AS tipo
        FROM Cliente C
        LEFT JOIN ClienteParticular CP ON C.idCliente = CP.idCliente
        LEFT JOIN ClienteEmpresa CE ON C.idCliente = CE.idCliente
        ORDER BY C.idCliente ASC;
      ";
      $stmt = $conn->query($sql);
      $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
      echo "<div class='alert alert-danger'>Error al leer clientes: " . htmlspecialchars($e->getMessage()) . "</div>";
      $clientes = [];
  }
  ?>

  <?php if (count($clientes) === 0): ?>
    <div class="alert alert-info">No hay clientes registrados.</div>
  <?php else: ?>
    <table class="table table-striped table-hover">
      <thead class="table-dark">
                <tr>
                  <th>ID</th>
                  <th>Nombre</th>
                  <th>Dirección</th>
                  <th>Correo</th>
                  <th>Teléfono(s)</th>
                  <th>Tipo</th>
                  <th style="width:160px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
              <?php foreach ($clientes as $c): ?>
                <tr>
                  <td><?= htmlspecialchars($c['idCliente']) ?></td>
                  <td><?= htmlspecialchars($c['nombre']) ?></td>
                  <td><?= htmlspecialchars($c['direccion']) ?></td>
                    <td><?= htmlspecialchars($c['correo']) ?></td>
                    <td><?= htmlspecialchars($c['telefonos'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['tipo']) ?></td>
                  <td>
                    <a href="editar_cliente.php?id=<?= urlencode($c['idCliente']) ?>" class="btn btn-sm btn-primary">Editar</a>
                    <a href="eliminar_cliente.php?id=<?= urlencode($c['idCliente']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar cliente <?= htmlspecialchars($c['nombre']) ?>?');">Eliminar</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
  <?php endif; ?>
</div>
    
<footer class="text-center mt-5 mb-3 text-muted">
  <small>© 2025 Sillas y Mesas Hernández — Gestión de Artículos</small>
</footer>

</body>
</html>
