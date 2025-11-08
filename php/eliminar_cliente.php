<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: clientes.php");
    exit;
}

try {
    $conn->beginTransaction();
    $conn->prepare("DELETE FROM ClienteParticular WHERE idCliente = :id")->execute([':id'=>$id]);
    $conn->prepare("DELETE FROM ClienteEmpresa WHERE idCliente = :id")->execute([':id'=>$id]);
    $conn->prepare("DELETE FROM Cliente WHERE idCliente = :id")->execute([':id'=>$id]);
    $conn->commit();
} catch (PDOException $e) {
    $conn->rollBack();
    echo "<div style='color:red;padding:10px;'>Error al eliminar: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
header("Location: clientes.php");
exit;
?>
