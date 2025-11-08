<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: paquetes.php");
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM Paquete WHERE idPaquete = :id");
    $stmt->execute([':id' => $id]);
    $paquete = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paquete) {
        echo "<div class='alert alert-warning'>Paquete no encontrado.</div>";
        exit;
    }

    $stmt = $conn->prepare("
        SELECT A.nombre, PA.cantidad
        FROM PaqueteArticulo PA
        JOIN Articulo A ON PA.idArticulo = A.idArticulo
        WHERE PA.idPaquete = :id
    ");
    $stmt->execute([':id' => $id]);
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al leer paquete: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Detalle Paquete #<?= htmlspecialchars($paquete['idPaquete']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Sillas y Mesas Hernández</a>
  </div>
</nav>

<div class="container mt-4">
  <h2>Detalle del Paquete: <?= htmlspecialchars($paquete['nombre']) ?></h2>
  <p><strong>Precio especial:</strong> $<?= number_format($paquete['precioEspecial'], 2) ?></p>

  <h5>Artículos incluidos</h5>
  <table class="table table-striped">
    <thead><tr><th>Artículo</th><th>Cantidad</th></tr></thead>
    <tbody>
      <?php foreach ($articulos as $a): ?>
      <tr>
        <td><?= htmlspecialchars($a['nombre']) ?></td>
        <td><?= htmlspecialchars($a['cantidad']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="editar_paquete.php?id=<?= $paquete['idPaquete'] ?>" class="btn btn-warning">Editar</a>
  <a href="paquetes.php" class="btn btn-secondary">Volver</a>
</div>
</body>
</html>
