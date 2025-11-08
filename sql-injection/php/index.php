<?php
session_start();
// index.php - formulario de login
// Si viene un mensaje de error vía GET o sesión, mostrarlo
$error = '';
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background-color: #f0f0f0; }
        .login-container { background-color: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        input { padding: 8px; width: 200px; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #45a049; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="Usuario" required>
            </div>
            <div class="form-group">
                <!-- Mostrar la contraseña en texto claro en lugar de ocultarla -->
                <input type="text" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit">Ingresar</button>
        </form>
        <div style="margin-top: 15px; text-align: center;">
            <a href="register.php" style="color: #4CAF50; text-decoration: none;">Registrarse</a>
        </div>
    </div>
</body>
</html>