<?php
session_start();
$username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : null;
$last_query = isset($_SESSION['last_query']) ? $_SESSION['last_query'] : null;
if (!$username) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Login Exitoso</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f0f0f0; }
        .success-container { background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; }
        h1 { color: #4CAF50; }
    </style>
</head>
<body>
    <div class="success-container">
        <h1>¡Inicio de sesión exitoso!</h1>
        <p>Bienvenido al sistema, <?php echo $username; ?>!</p>
        <?php if ($last_query): ?>
        <div style="text-align:left; margin-top:15px;">
            <h3 style="margin:0 0 8px 0;">Consulta SQL ejecutada:</h3>
            <pre style="background:#f8f8f8;padding:10px;border-radius:4px;white-space:pre-wrap;word-break:break-all;"><?php echo htmlspecialchars($last_query); ?></pre>
        </div>
        <?php endif; ?>
        <p style="margin-top:12px;"><a href="index.php">Cerrar sesión</a></p>
    </div>
</body>
</html>