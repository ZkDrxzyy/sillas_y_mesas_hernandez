<?php
// Página de depuración: muestra todos los usuarios - NO exponer en producción
require_once 'config.php';
$mysqli = db_connect();
$result = $mysqli->query('SELECT id, username, password FROM users');
$rows = [];
if ($result) {
    while ($r = $result->fetch_row()) {
        $rows[] = $r;
    }
    $result->close();
}
$mysqli->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Dump Users (Prueba)</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background:#f7f7f7 }
        table { border-collapse: collapse; width: 100%; background: #fff }
        th, td { padding: 8px 12px; border: 1px solid #ddd; }
        th { background: #f0f0f0; text-align: left }
        caption { font-size: 1.2em; margin-bottom: 8px; font-weight: bold }
        .note { margin-bottom: 12px; color: #b33 }
    </style>
</head>
<body>
    <div>
        <div class="note">Ruta de depuración: muestra todos los usuarios y contraseñas hasheadas. No usar en producción.</div>
        <table>
            <caption>Usuarios</caption>
            <thead>
                <tr>
                    <th>id</th>
                    <th>username</th>
                    <th>password</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)): ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($r[0]); ?></td>
                            <td><?php echo htmlspecialchars($r[1]); ?></td>
                            <td><?php echo htmlspecialchars($r[2]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3">No hay usuarios</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p style="margin-top:12px;"><a href="index.php">Volver al login</a></p>
    </div>
</body>
</html>