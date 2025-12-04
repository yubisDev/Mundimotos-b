<?php 
// dashboard.php (Página de Administración)
include_once 'conexion.php';
// NOTA: Asegúrate que header.php incluya Bootstrap 5 CSS y los archivos JS
// También incluye las etiquetas <body> y <div class="container mt-5"> o similar
include('includes/header.php'); 

// 1. Determinar qué CRUD se debe mostrar (por defecto, productos)
$active_tab = $_GET['tab'] ?? 'productos';

// 2. Definir los archivos auxiliares de CRUD (lectura)
$crud_files = [
    'productos'       => 'includes/crud_productos.php',
    'tiposdeproducto' => 'includes/crud_tipos.php',
];

// 3. Obtener el archivo de CRUD a incluir
$file_to_include = $crud_files[$active_tab] ?? $crud_files['productos'];
?>

<div class="container mt-5 p-4 rounded-3 shadow-lg custom-dark-bg-card">
    
    <header class="mb-5 border-bottom border-secondary pb-3">
        <h1 class="display-4 text-primary">
            <i class="fas fa-motorcycle fa-fw me-3"></i> 
            Panel de Administración MundiMotos
        </h1>
        <p class="lead text-secondary">Gestión completa del inventario y categorías de la tienda.</p>
    </header>

    <ul class="nav nav-pills mb-4 custom-nav-dark" id="crudTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= ($active_tab == 'productos' ? 'active bg-primary text-white' : 'text-secondary') ?>" 
               href="?tab=productos" role="tab" aria-selected="<?= ($active_tab == 'productos' ? 'true' : 'false') ?>">
                <i class="fas fa-tools fa-fw me-2"></i> Gestión de Productos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= ($active_tab == 'tiposdeproducto' ? 'active bg-primary text-white' : 'text-secondary') ?>" 
               href="?tab=tiposdeproducto" role="tab" aria-selected="<?= ($active_tab == 'tiposdeproducto' ? 'true' : 'false') ?>">
                <i class="fas fa-tag fa-fw me-2"></i> Gestión de Tipos de Producto
            </a>
        </li>
    </ul>

    <div class="tab-content" id="crudTabsContent">
        <div class="tab-pane fade show active p-3 rounded-3 custom-dark-content-area" 
             role="tabpanel" aria-labelledby="<?= $active_tab ?>-tab">
            <?php 
            // Incluye la lógica de CRUD (ej: includes/crud_productos.php)
            include($file_to_include); 
            ?>
        </div>
    </div>

</div> <?php include('includes/footer.php'); ?>
