<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: paquetes.php");
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM Paquete WHERE idPaquete = :id");
    $stmt->execute([':id' => $id]);
} catch (PDOException $e) {
    die("Error al eliminar: " . htmlspecialchars($e->getMessage()));
}

header("Location: paquetes.php");
exit;
?>
