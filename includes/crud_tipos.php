<?php
// includes/crud_tipos.php

// Asegúrate de que $conexion esté disponible (se incluye desde dashboard.php)

$action = $_GET['action'] ?? 'list'; // Acción por defecto: 'list'
$tipo_id = $_GET['id'] ?? null;
$mensaje = ''; 
$errores_validacion = [];
$datos_formulario = []; // Para rellenar formularios en caso de error o edición

// -------------------------------------------------------------------------
// LÓGICA PARA PROCESAR FORMULARIOS (CREAR/EDITAR) y ELIMINAR
// -------------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? 'list'; // Detecta la acción del formulario
    $tipo_id = $_POST['TipoProductoID'] ?? null;

    // Recoger los datos del formulario
    $datos_formulario = ['NombreTipo' => $_POST['NombreTipo'] ?? ''];

    // --- VALIDACIÓN ---
    if ($action == 'crear' || $action == 'editar') {
        if (empty($datos_formulario['NombreTipo'])) {
            $errores_validacion['NombreTipo'] = 'El nombre del tipo es obligatorio.';
        }
    }

    // --- PROCESAR ACCIONES ---
    if (empty($errores_validacion)) {
        if ($action == 'crear') {
            try {
                $sql = "INSERT INTO tiposdeproducto (NombreTipo, Estado) VALUES (:nombre, 'Activo')";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':nombre', $datos_formulario['NombreTipo'], PDO::PARAM_STR);
                $stmt->execute();
                
                // Alerta con estilo dark-mode
                $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>✅ Éxito:</strong> Tipo de Producto <strong>' . htmlspecialchars($datos_formulario['NombreTipo']) . '</strong> creado exitosamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
                $datos_formulario = []; // Limpiar formulario
                $action = 'list'; // Volver al listado
            } catch (PDOException $e) {
                // Alerta con estilo dark-mode
                $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>❌ Error:</strong> Error al crear el tipo: ' . $e->getMessage() . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        } elseif ($action == 'editar') {
            try {
                $sql = "UPDATE tiposdeproducto SET NombreTipo = :nombre WHERE TipoProductoID = :id";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':nombre', $datos_formulario['NombreTipo'], PDO::PARAM_STR);
                $stmt->bindParam(':id', $tipo_id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Alerta con estilo dark-mode
                $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>✅ Éxito:</strong> Tipo <strong>' . htmlspecialchars($datos_formulario['NombreTipo']) . '</strong> actualizado exitosamente.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
                $action = 'list'; // Volver al listado
            } catch (PDOException $e) {
                 // Alerta con estilo dark-mode
                $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>❌ Error:</strong> Error al actualizar: ' . $e->getMessage() . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        }
    } else {
         // Alerta con estilo dark-mode
        $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error de Validación:</strong> Por favor, corrige los errores en el formulario.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

// Lógica de eliminación (Soft Delete - por GET)
if ($action == 'eliminar' && $tipo_id) {
    try {
        $stmt = $conexion->prepare("UPDATE tiposdeproducto SET Estado = 'Inactivo' WHERE TipoProductoID = :id");
        $stmt->bindParam(':id', $tipo_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Alerta con estilo dark-mode
        $mensaje = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>🗑️ Eliminado:</strong> Tipo de Producto eliminado exitosamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        $action = 'list'; // Volver al listado
    } catch (PDOException $e) {
         // Alerta con estilo dark-mode
        $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error:</strong> Error al eliminar el tipo: ' . $e->getMessage() . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

// -------------------------------------------------------------------------
// LÓGICA PARA PREPARAR DATOS PARA EL FORMULARIO DE EDICIÓN
// -------------------------------------------------------------------------

if ($action == 'editar' && $tipo_id && empty($errores_validacion)) {
    try {
        $stmt = $conexion->prepare("SELECT TipoProductoID, NombreTipo FROM tiposdeproducto WHERE TipoProductoID = :id");
        $stmt->bindParam(':id', $tipo_id, PDO::PARAM_INT);
        $stmt->execute();
        $tipo_a_editar = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($tipo_a_editar) {
            $datos_formulario = $tipo_a_editar; // Rellenar formulario con datos existentes
        } else {
            // Alerta con estilo dark-mode
            $mensaje = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>⚠️ Advertencia:</strong> Tipo de producto no encontrado para editar.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
            $action = 'list';
        }
    } catch (PDOException $e) {
         // Alerta con estilo dark-mode
        $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error:</strong> Error al cargar tipo para editar: ' . $e->getMessage() . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        $action = 'list';
    }
}

// -------------------------------------------------------------------------
// VISUALIZACIÓN DE CONTENIDO (LISTADO O FORMULARIO)
// -------------------------------------------------------------------------

echo $mensaje; // Mostrar mensajes de éxito/error

if ($action == 'crear' || $action == 'editar'): // Mostrar formulario de Creación/Edición
?>
    <h3 class="mb-4 text-primary">
        <?= ($action == 'crear' ? 'Crear Nuevo Tipo de Producto' : 'Editar Tipo: ' . htmlspecialchars($datos_formulario['NombreTipo'] ?? '')) ?> 
        <i class="fas <?= ($action == 'crear' ? 'fa-plus-circle' : 'fa-edit') ?> fa-fw"></i>
    </h3>
    
    <div class="card custom-dark-bg-card p-4 border-secondary shadow"> 
        <form action="dashboard.php?tab=tiposdeproducto" method="POST">
            <input type="hidden" name="form_action" value="<?= $action ?>">
            <?php if ($action == 'editar'): ?>
                <input type="hidden" name="TipoProductoID" value="<?= htmlspecialchars($tipo_id) ?>">
            <?php endif; ?>
            
            <div class="mb-4">
                <label for="nombretipo" class="form-label fs-5">Nombre del Tipo <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-lg <?= isset($errores_validacion['NombreTipo']) ? 'is-invalid' : '' ?>" 
                       id="nombretipo" name="NombreTipo" placeholder="Ej: Cascos Integrales" 
                       value="<?= htmlspecialchars($datos_formulario['NombreTipo'] ?? '') ?>" required>
                <?php if (isset($errores_validacion['NombreTipo'])): ?>
                    <div class="invalid-feedback"><?= $errores_validacion['NombreTipo'] ?></div>
                <?php endif; ?>
                <div class="form-text text-secondary">El nombre debe ser claro y descriptivo.</div>
            </div>

            <div class="d-flex justify-content-start border-top border-secondary pt-3">
                <button type="submit" class="btn btn-primary btn-lg me-2"><i class="fas fa-save me-1"></i> <?= ($action == 'crear' ? 'Guardar Tipo' : 'Actualizar Tipo') ?></button>
                <a href="dashboard.php?tab=tiposdeproducto" class="btn btn-outline-secondary btn-lg">Cancelar</a>
            </div>
        </form>
    </div>

<?php 
else: // Mostrar Listado de Tipos de Producto
    $tipos = [];
    try {
        $sql = "SELECT TipoProductoID, NombreTipo FROM tiposdeproducto WHERE Estado = 'Activo' ORDER BY NombreTipo"; 
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Alerta con estilo dark-mode
        $mensaje .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error:</strong> Error al cargar tipos de producto: ' . $e->getMessage() . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
?>
    <div class="mb-4 d-flex justify-content-between align-items-center border-bottom border-secondary pb-3">
        <h3 class="text-light">
            <i class="fas fa-tags me-2 text-primary"></i> 
            Listado de Tipos de Producto
        </h3>
        <a href="dashboard.php?tab=tiposdeproducto&action=crear" class="btn btn-primary shadow">
            <i class="fas fa-plus-circle me-2"></i> Crear Nuevo Tipo
        </a>
    </div>

    <div class="table-responsive p-3 rounded-3 custom-dark-content-area">
        <table class="table table-dark table-striped table-hover align-middle">
            <thead class="table-dark border-bottom border-light">
                <tr>
                    <th>ID</th>
                    <th>Nombre del Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tipos)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-secondary py-4">
                            <i class="fas fa-tag me-2"></i> No se encontraron tipos de producto activos.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tipos as $tipo): ?>
                        <tr>
                            <td><?= $tipo['TipoProductoID'] ?></td>
                            <td class="text-white fw-bold"><?= htmlspecialchars($tipo['NombreTipo']) ?></td>
                            <td>
                                <a href="dashboard.php?tab=tiposdeproducto&action=editar&id=<?= $tipo['TipoProductoID'] ?>" 
                                   class="btn btn-sm btn-info text-white me-1" title="Editar Tipo">
                                   <i class="fas fa-edit"></i>
                                </a>
                                <a href="dashboard.php?tab=tiposdeproducto&action=eliminar&id=<?= $tipo['TipoProductoID'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('¿Estás seguro de ELIMINAR este tipo de producto?');" 
                                   title="Eliminar Tipo">
                                   <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>