<?php
// No incluimos header.php para que no aparezca la navegación principal
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - MundiMotos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="css/custom.css">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center vh-100">

<div class="container login-container">
    <div class="card custom-dark-bg-card border-secondary p-4 shadow-lg">
        <h3 class="card-title text-center mb-4 text-primary">
            <i class="fas fa-lock me-2"></i> Acceso al Sistema
        </h3>
        <form action="dashboard.php"> <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Ingresa tu usuario" required>
            </div>
            <div class="mb-4">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Ingresa tu contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 shadow-sm">
                <i class="fas fa-sign-in-alt me-2"></i> Entrar
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>