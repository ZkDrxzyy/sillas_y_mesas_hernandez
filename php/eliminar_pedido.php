<?php
require_once "conexion.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: pedidos.php");
    exit;
}

try {
    $conn->beginTransaction();
    $conn->prepare("DELETE FROM DetallePedido WHERE idPedido=:id")->execute([':id'=>$id]);
    $conn->prepare("DELETE FROM Pedido WHERE idPedido=:id")->execute([':id'=>$id]);
    $conn->commit();
} catch (PDOException $e) {
    $conn->rollBack();
    echo "<div style='color:red;padding:10px;'>Error al eliminar pedido: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
header("Location: pedidos.php");
exit;
?>
