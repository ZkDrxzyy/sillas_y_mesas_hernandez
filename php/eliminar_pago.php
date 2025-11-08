<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: pagos.php");
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM Pago WHERE idPago = :id");
    $stmt->execute([':id' => $id]);
} catch (PDOException $e) {
    die("Error al eliminar: " . htmlspecialchars($e->getMessage()));
}

header("Location: pagos.php");
exit;
?>
