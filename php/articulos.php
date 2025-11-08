<?php
require_once "conexion.php";

try {
    // Consulta para obtener todos los artículos con sus detalles específicos
    $sql = "
        SELECT 
            a.*,
            s.tipoSilla, s.material,
            m.forma, m.capacidadPersonas, m.tamaño,
            acc.descripcion, acc.fragilidad,
            CASE
                WHEN s.idArticulo IS NOT NULL THEN 'Silla'
                WHEN m.idArticulo IS NOT NULL THEN 'Mesa'
                WHEN acc.idArticulo IS NOT NULL THEN 'Accesorio'
                ELSE 'General'
            END as tipoArticulo
        FROM Articulo a
        LEFT JOIN Silla s ON a.idArticulo = s.idArticulo
        LEFT JOIN Mesa m ON a.idArticulo = m.idArticulo
        LEFT JOIN Accesorio acc ON a.idArticulo = acc.idArticulo
        ORDER BY a.idArticulo DESC";
    
    $articulos = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al leer artículos: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Artículos - Sillas y Mesas Hernández</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Sillas y Mesas Hernández</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
		<li class="nav-item"><a class="nav-link active" href="articulos.php">Artículos</a></li>
        <li class="nav-item"><a class="nav-link" href="clientes.php">Clientes</a></li>
        <li class="nav-item"><a class="nav-link" href="pedidos.php">Pedidos</a></li>
        <li class="nav-item"><a class="nav-link" href="pagos.php">Pagos</a></li>
        <li class="nav-item"><a class="nav-link" href="paquetes.php">Paquetes</a></li>

      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="mb-4">Gestión de Artículos</h2>
  <div class="mb-3">
    <a href="nuevo_articulo.php?tipo=silla" class="btn btn-success">+ Nueva Silla</a>
    <a href="nuevo_articulo.php?tipo=mesa" class="btn btn-success">+ Nueva Mesa</a>
    <a href="nuevo_articulo.php?tipo=accesorio" class="btn btn-success">+ Nuevo Accesorio</a>
  </div>

  <table class="table table-striped table-hover">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Tipo</th>
        <th>Nombre</th>
        <th>Cantidades por Estado</th>
        <th>Costo Renta</th>
        <th>Detalles específicos</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($articulos as $a): ?>
      <tr>
        <td><?= htmlspecialchars($a['idArticulo']) ?></td>
        <td><?= htmlspecialchars($a['tipoArticulo']) ?></td>
        <td><?= htmlspecialchars($a['nombre']) ?></td>
        <td>
          <small class="d-block text-success">Disponible: <?= htmlspecialchars($a['cantidadDisponible']) ?></small>
          <small class="d-block text-primary">En uso: <?= htmlspecialchars($a['cantidadEnUso']) ?></small>
          <small class="d-block text-danger">Dañado: <?= htmlspecialchars($a['cantidadDanada']) ?></small>
          <small class="d-block text-muted">Total: <?= htmlspecialchars($a['cantidadTotal']) ?></small>
        </td>
        <td>$<?= number_format($a['costoRenta'], 2) ?></td>
        <td>
          <?php
          switch($a['tipoArticulo']) {
              case 'Silla':
                  echo "Tipo: " . htmlspecialchars($a['tipoSilla']) . 
                       "<br>Material: " . htmlspecialchars($a['material']);
                  break;
              case 'Mesa':
                  echo "Forma: " . htmlspecialchars($a['forma']) . 
                       "<br>Capacidad: " . htmlspecialchars($a['capacidadPersonas']) . " personas" .
                       "<br>Tamaño: " . htmlspecialchars($a['tamaño']);
                  break;
              case 'Accesorio':
                  echo "Descripción: " . htmlspecialchars($a['descripcion']) . 
                       "<br>Fragilidad: " . htmlspecialchars($a['fragilidad']);
                  break;
          }
          ?>
        </td>
        <td>
          <a href="editar_articulo.php?id=<?= $a['idArticulo'] ?>&tipo=<?= strtolower($a['tipoArticulo']) ?>" 
             class="btn btn-sm btn-primary">Editar</a>
          <a href="eliminar_articulo.php?id=<?= $a['idArticulo'] ?>" 
             class="btn btn-sm btn-danger"
             onclick="return confirm('¿Eliminar este artículo?');">Eliminar</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<footer class="text-center mt-5 mb-3 text-muted">
  <small>© 2025 Sillas y Mesas Hernández — Gestión de Artículos</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
