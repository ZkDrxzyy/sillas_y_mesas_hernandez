<?php
require_once "conexion.php";
if (!isset($_GET['id'])) {
    header("Location: articulos.php");
    exit;
}

$id = intval($_GET['id']);

try {
    $conn->beginTransaction();

    // Primero eliminamos de las tablas específicas (aunque el CASCADE lo haría automáticamente)
    $stmt = $conn->prepare("DELETE FROM Silla WHERE idArticulo = :id");
    $stmt->execute([':id' => $id]);
    
    $stmt = $conn->prepare("DELETE FROM Mesa WHERE idArticulo = :id");
    $stmt->execute([':id' => $id]);
    
    $stmt = $conn->prepare("DELETE FROM Accesorio WHERE idArticulo = :id");
    $stmt->execute([':id' => $id]);

    // Finalmente eliminamos de la tabla Articulo
    $stmt = $conn->prepare("DELETE FROM Articulo WHERE idArticulo = :id");
    $stmt->execute([':id' => $id]);

    $conn->commit();
} catch (PDOException $e) {
    $conn->rollBack();
    echo "<div style='color:red;'>Error al eliminar: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}

header("Location: articulos.php");
exit;
?>
