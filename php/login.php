<?php
session_start();
require_once "conexion.php";

error_reporting(0);
ini_set('display_errors', 0);

if (!isset($conn) || !$conn) {
    error_log("Error: No se pudo establecer conexión a la base de datos.");
    exit("Error interno del servidor.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"]);
    $contrasena = $_POST["contrasena"];

    // Consulta segura (sin exponer contraseñas)
    $stmt = $conn->prepare("SELECT * FROM Usuario WHERE nombreUsuario = :usuario LIMIT 1");
    $stmt->bindParam(":usuario", $usuario, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($contrasena, $user["contrasena"])) {
        $_SESSION["usuario"] = $user["nombreUsuario"];
        header("Location: index.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso al Sistema - Sillas y Mesas Hernández</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg,#f8fafc 0%,#eef2f7 100%);
        }
        .login-card {
            max-width: 480px;
            margin: 6rem auto;
            border: 0;
            border-radius: 12px;
            box-shadow: 0 6px 30px rgba(25, 40, 60, 0.08);
        }
        .brand { font-weight:700; letter-spacing:.4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card login-card">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h3 class="brand mb-1">Sillas y Mesas Hernández</h3>
                    <p class="text-muted small mb-0">Sistema de Gestión — Inicia sesión para continuar</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" class="form-control form-control-lg" id="usuario" name="usuario"
                               placeholder="Introduce tu usuario" required autofocus
                               value="<?php echo isset($usuario) ? htmlspecialchars($usuario) : ''; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña</label>
                        <input type="password" class="form-control form-control-lg" id="contrasena" name="contrasena"
                               placeholder="••••••••" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Entrar</button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <small class="text-muted">¿Problemas para iniciar sesión? Contacta al administrador.</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
