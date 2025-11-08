<?php
session_start();
require_once "conexion.php";

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["usuario"])) {
    header("Location: login.php");
    exit();
}

// Verificar si el usuario es administrador
$stmt = $conn->prepare("SELECT rol FROM Usuario WHERE nombreUsuario = :usuario LIMIT 1");
$stmt->bindParam(":usuario", $_SESSION["usuario"], PDO::PARAM_STR);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || $usuario["rol"] !== 'admin') {
    header("Location: index.php");
    exit("Acceso denegado. Se requieren privilegios de administrador.");
}

// Manejo del formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombreUsuario = trim($_POST["nombreUsuario"]);
    $contrasena = $_POST["contrasena"];
    $confirmarContrasena = $_POST["confirmarContrasena"];
    $esAdmin = isset($_POST["esAdmin"]) ? 1 : 0;

    $error = null;

    // Validaciones
    if (empty($nombreUsuario) || empty($contrasena)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($contrasena !== $confirmarContrasena) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Verificar si el usuario ya existe
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Usuario WHERE nombreUsuario = :nombreUsuario");
        $stmt->bindParam(":nombreUsuario", $nombreUsuario, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $error = "El nombre de usuario ya existe.";
        } else {
            // Crear el nuevo usuario
            try {
                $hashedPassword = password_hash($contrasena, PASSWORD_DEFAULT);
                $rol = isset($_POST["esAdmin"]) ? 'admin' : 'usuario';
                $stmt = $conn->prepare("INSERT INTO Usuario (nombreUsuario, contrasena, rol) VALUES (:nombreUsuario, :contrasena, :rol)");
                $stmt->bindParam(":nombreUsuario", $nombreUsuario, PDO::PARAM_STR);
                $stmt->bindParam(":contrasena", $hashedPassword, PDO::PARAM_STR);
                $stmt->bindParam(":rol", $rol, PDO::PARAM_STR);
                
                if ($stmt->execute()) {
                    $mensaje = "Usuario creado exitosamente.";
                } else {
                    $error = "Error al crear el usuario.";
                }
            } catch (PDOException $e) {
                $error = "Error en la base de datos: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuevo Usuario - Sillas y Mesas Hernández</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="card-title text-center mb-4">Crear Nuevo Usuario</h2>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($mensaje)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($mensaje); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" novalidate>
                            <div class="mb-3">
                                <label for="nombreUsuario" class="form-label">Nombre de Usuario</label>
                                <input type="text" class="form-control" id="nombreUsuario" name="nombreUsuario" 
                                       value="<?php echo isset($nombreUsuario) ? htmlspecialchars($nombreUsuario) : ''; ?>" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                            </div>

                            <div class="mb-3">
                                <label for="confirmarContrasena" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" id="confirmarContrasena" 
                                       name="confirmarContrasena" required>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="esAdmin" name="esAdmin">
                                    <label class="form-check-label" for="esAdmin">
                                        Es Administrador
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Crear Usuario</button>
                                <a href="index.php" class="btn btn-secondary">Volver</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>