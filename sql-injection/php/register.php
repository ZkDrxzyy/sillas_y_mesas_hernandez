<?php
session_start();
require_once 'config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === '' || $password === '') {
        $errors[] = 'Por favor complete todos los campos';
    }

    if (empty($errors)) {
        $mysqli = db_connect();
        // Versión solicitada: almacenar la contraseña tal cual (texto plano)
        // NOTA: esto NO es seguro para producción, pero se mantiene por compatibilidad con la versión original.
        $password_plain = $password;
        $stmt = $mysqli->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        if (!$stmt) {
            die('Prepare failed: ' . $mysqli->error);
        }
        $stmt->bind_param('ss', $username, $password_plain);
        if ($stmt->execute()) {
            $stmt->close();
            $mysqli->close();
            header('Location: index.php');
            exit;
        } else {
            // Posible usuario duplicado
            $errors[] = 'El usuario ya existe';
            $stmt->close();
            $mysqli->close();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Registro</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f0f0f0; }
        .register-container { background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        input { padding: 8px; width: 200px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #45a049; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Registro de Usuario</h2>
        <?php if (!empty($errors)): ?>
            <div class="error"><?php echo htmlspecialchars(implode('<br>', $errors)); ?></div>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="Usuario" required>
            </div>
            <div class="form-group">
                <!-- Mostrar la contraseña en texto claro en lugar de ocultarla -->
                <input type="text" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit">Registrarse</button>
        </form>
        <div style="margin-top: 15px; text-align: center;">
            <a href="index.php" style="color: #4CAF50; text-decoration: none;">Volver al login</a>
        </div>
    </div>
</body>
</html>