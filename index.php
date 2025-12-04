<?php 
// index.php (Página Principal/Pública)

// Asumiendo que 'conexion.php' contiene la conexión a la base de datos
include('conexion.php');
// Asumiendo que 'includes/header.php' contiene el navbar, CSS y body/html tags
include('includes/header.php'); 
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="card custom-dark-bg-card p-5 text-center shadow-lg border-2 border-primary">
        
        <h1 class="display-3 fw-bolder text-primary mb-3">
            <i class="fas fa-motorcycle me-2"></i> MundiMotos <i class="fas fa-tools ms-2"></i>
        </h1>
        
        <p class="lead mt-3 text-light">
            ¡Bienvenido a la mejor selección de motocicletas y repuestos!
        </p>
        
        <hr class="my-4 border-secondary">
        
        <p class="text-secondary fw-semibold">
            Si eres **administrador**, haz clic en el botón <span class="badge bg-primary">INICIAR SESIÓN</span> para acceder al panel de gestión.
        </p>

        <div class="mt-4">
            <a href="login.php" class="btn btn-primary btn-lg px-5 shadow-sm">
                <i class="fas fa-user-shield me-2"></i> Acceder al Dashboard
            </a>
        </div>
    </div>
</div>

<?php 
// Asumiendo que 'includes/footer.php' contiene el cierre de body/html tags y scripts
include('includes/footer.php'); 
?>