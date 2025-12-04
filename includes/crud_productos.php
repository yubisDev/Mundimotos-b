<?php
// includes/crud_productos.php
// Este archivo maneja la visualización del listado y los formularios de Crear/Editar para productos.

// Asegúrate de que $conexion esté disponible (se incluye desde dashboard.php)

$action = $_GET['action'] ?? 'list'; // Acción por defecto: 'list'
$producto_id = $_GET['id'] ?? null; // ID del producto para editar/eliminar
$mensaje = ''; 
$errores_validacion = [];
$datos_formulario = []; // Para rellenar formularios en caso de error o edición

// -------------------------------------------------------------------------
// LÓGICA PARA PROCESAR FORMULARIOS (CREAR/EDITAR) y ELIMINAR
// -------------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? 'list'; // Detecta la acción del formulario
    $producto_id = $_POST['ProductoID'] ?? null;

    // Recoger los datos del formulario (aplicable a crear y editar)
    $datos_formulario = [
        'NombreProducto' => $_POST['NombreProducto'] ?? '',
        'Marca'          => $_POST['Marca'] ?? '',
        'Modelo'         => $_POST['Modelo'] ?? '',
        'TipoProductoID' => $_POST['TipoProductoID'] ?? '',
        'PrecioVenta'    => $_POST['PrecioVenta'] ?? '',
        'Stock'          => $_POST['Stock'] ?? '',
        'Descripcion'    => $_POST['Descripcion'] ?? ''
    ];

    // --- VALIDACIÓN ---
    if ($action == 'crear' || $action == 'editar') {
        if (empty($datos_formulario['NombreProducto'])) { $errores_validacion['NombreProducto'] = 'El nombre es obligatorio.'; }
        if (empty($datos_formulario['Marca'])) { $errores_validacion['Marca'] = 'La marca es obligatoria.'; }
        if (empty($datos_formulario['TipoProductoID'])) { $errores_validacion['TipoProductoID'] = 'Debe seleccionar un tipo.'; }

        $precio = filter_var($datos_formulario['PrecioVenta'], FILTER_VALIDATE_FLOAT);
        if ($precio === false || $precio <= 0) { $errores_validacion['PrecioVenta'] = 'El precio debe ser un número mayor a 0.'; }

        $stock = filter_var($datos_formulario['Stock'], FILTER_VALIDATE_INT);
        if ($stock === false || $stock < 0) { $errores_validacion['Stock'] = 'El stock debe ser un entero válido.'; }
    }

    // --- PROCESAR ACCIONES ---
    if (empty($errores_validacion)) {
        if ($action == 'crear') {
            
            // --- Lógica de UPSERT (Actualizar Stock o Insertar Producto) ---
            
            try {
                // 1. Verificar si el producto ya existe (basado en Nombre, Marca y Modelo)
                $sql_check = "SELECT ProductoID, Stock, TipoProductoID FROM productos 
                              WHERE NombreProducto = :nombre 
                              AND Marca = :marca 
                              AND Modelo = :modelo 
                              AND Estado = 'Activo'"; // Solo buscamos productos activos
                
                $stmt_check = $conexion->prepare($sql_check);
                $stmt_check->bindParam(':nombre', $datos_formulario['NombreProducto'], PDO::PARAM_STR);
                $stmt_check->bindParam(':marca', $datos_formulario['Marca'], PDO::PARAM_STR);
                $stmt_check->bindParam(':modelo', $datos_formulario['Modelo'], PDO::PARAM_STR);
                $stmt_check->execute();
                $producto_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if ($producto_existente) {
                    // 2. Si existe: Sumar el nuevo stock al existente (UPDATE)
                    $nuevo_stock = $producto_existente['Stock'] + $stock;
                    
                    $sql_update = "UPDATE productos SET 
                                         Stock = :nuevo_stock 
                                       WHERE ProductoID = :id";
                    
                    $stmt_update = $conexion->prepare($sql_update);
                    $stmt_update->bindParam(':nuevo_stock', $nuevo_stock, PDO::PARAM_INT);
                    $stmt_update->bindParam(':id', $producto_existente['ProductoID'], PDO::PARAM_INT);
                    $stmt_update->execute();
                    
                    // Alerta con estilo dark-mode
                    $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>✅ Producto existente encontrado.</strong> Se **sumaron ' . $stock . ' unidades** al stock actual (' . $producto_existente['Stock'] . '). Nuevo Stock Total: **' . $nuevo_stock . '**
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                    
                } else {
                    // 3. Si NO existe: Insertar el nuevo producto (INSERT)
                    $sql_insert = "INSERT INTO productos (TipoProductoID, NombreProducto, Marca, Modelo, PrecioVenta, Stock, Descripcion, Estado) 
                                     VALUES (:tipoid, :nombre, :marca, :modelo, :precio, :stock, :descripcion, 'Activo')";
                    
                    $stmt_insert = $conexion->prepare($sql_insert);
                    $stmt_insert->bindParam(':tipoid', $datos_formulario['TipoProductoID'], PDO::PARAM_INT);
                    $stmt_insert->bindParam(':nombre', $datos_formulario['NombreProducto'], PDO::PARAM_STR);
                    $stmt_insert->bindParam(':marca', $datos_formulario['Marca'], PDO::PARAM_STR);
                    $stmt_insert->bindParam(':modelo', $datos_formulario['Modelo'], PDO::PARAM_STR);
                    $stmt_insert->bindParam(':precio', $precio);
                    $stmt_insert->bindParam(':stock', $stock, PDO::PARAM_INT);
                    $stmt_insert->bindParam(':descripcion', $datos_formulario['Descripcion'], PDO::PARAM_STR);
                    $stmt_insert->execute();
                    
                    // Alerta con estilo dark-mode
                    $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>✅ Producto creado exitosamente.</strong> Producto <strong>' . htmlspecialchars($datos_formulario['NombreProducto']) . '</strong> creado con **' . $stock . ' unidades**.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }
                
                $datos_formulario = []; // Limpiar formulario
                $action = 'list'; // Volver al listado
                
            } catch (PDOException $e) {
                // Alerta con estilo dark-mode
                $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>❌ Error en la operación (Upsert):</strong> ' . $e->getMessage() . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        
        // Fin de la lógica de UPSERT
        } elseif ($action == 'editar') {
            try {
                // En el modo 'editar', se actualizan todos los campos, incluyendo el stock con el valor nuevo.
                $sql = "UPDATE productos SET TipoProductoID = :tipoid, NombreProducto = :nombre, Marca = :marca, 
                                 Modelo = :modelo, PrecioVenta = :precio, Stock = :stock, Descripcion = :descripcion
                        WHERE ProductoID = :id";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':tipoid', $datos_formulario['TipoProductoID'], PDO::PARAM_INT);
                $stmt->bindParam(':nombre', $datos_formulario['NombreProducto'], PDO::PARAM_STR);
                $stmt->bindParam(':marca', $datos_formulario['Marca'], PDO::PARAM_STR);
                $stmt->bindParam(':modelo', $datos_formulario['Modelo'], PDO::PARAM_STR);
                $stmt->bindParam(':precio', $precio);
                $stmt->bindParam(':stock', $stock, PDO::PARAM_INT);
                $stmt->bindParam(':descripcion', $datos_formulario['Descripcion'], PDO::PARAM_STR);
                $stmt->bindParam(':id', $producto_id, PDO::PARAM_INT);
                $stmt->execute();
                
                // Alerta con estilo dark-mode
                $mensaje = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>✅ Producto actualizado exitosamente.</strong> Producto <strong>' . htmlspecialchars($datos_formulario['NombreProducto']) . '</strong> actualizado.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
                $action = 'list'; // Volver al listado
            } catch (PDOException $e) {
                // Alerta con estilo dark-mode
                $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>❌ Error al actualizar:</strong> ' . $e->getMessage() . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        }
    } else {
        // Alerta con estilo dark-mode
        $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error de validación:</strong> Por favor, corrige los errores en el formulario.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

// Lógica de eliminación (Soft Delete - por GET, como un botón en la tabla)
if ($action == 'eliminar' && $producto_id) {
    try {
        $stmt = $conexion->prepare("UPDATE productos SET Estado = 'Inactivo' WHERE ProductoID = :id");
        $stmt->bindParam(':id', $producto_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Alerta con estilo dark-mode
        $mensaje = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>🗑️ Eliminado:</strong> Producto eliminado exitosamente.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        $action = 'list'; // Volver al listado
    } catch (PDOException $e) {
        // Alerta con estilo dark-mode
        $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error al eliminar:</strong> ' . $e->getMessage() . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

// -------------------------------------------------------------------------
// LÓGICA PARA PREPARAR DATOS PARA EL FORMULARIO DE EDICIÓN
// -------------------------------------------------------------------------

if ($action == 'editar' && $producto_id && empty($errores_validacion)) {
    try {
        $stmt = $conexion->prepare("SELECT * FROM productos WHERE ProductoID = :id");
        $stmt->bindParam(':id', $producto_id, PDO::PARAM_INT);
        $stmt->execute();
        $producto_a_editar = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($producto_a_editar) {
            $datos_formulario = $producto_a_editar; // Rellenar formulario con datos existentes
        } else {
            // Alerta con estilo dark-mode
            $mensaje = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>⚠️ Advertencia:</strong> Producto no encontrado para editar.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
            $action = 'list';
        }
    } catch (PDOException $e) {
        // Alerta con estilo dark-mode
        $mensaje = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error:</strong> Error al cargar producto para editar: ' . $e->getMessage() . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        $action = 'list';
    }
}


// Cargar dinámicamente Tipos de Producto para el SELECT
$tipos_productos = [];
try {
    $stmt_tipos = $conexion->query("SELECT TipoProductoID, NombreTipo FROM tiposdeproducto WHERE Estado = 'Activo' ORDER BY NombreTipo");
    $tipos_productos = $stmt_tipos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Puedes manejar el error de forma silenciosa o con un mensaje
}

// -------------------------------------------------------------------------
// VISUALIZACIÓN DE CONTENIDO (LISTADO O FORMULARIO)
// -------------------------------------------------------------------------

echo $mensaje; // Mostrar mensajes de éxito/error

if ($action == 'crear' || $action == 'editar'): // Mostrar formulario de Creación/Edición
?>
    <h3 class="mb-4 text-primary">
        <?= ($action == 'crear' ? 'Ingresar o Sumar Stock de Producto' : 'Editar Producto: ' . htmlspecialchars($datos_formulario['NombreProducto'] ?? '')) ?> 
        <i class="fas <?= ($action == 'crear' ? 'fa-truck-loading' : 'fa-edit') ?> fa-fw"></i>
    </h3>
    
    <div class="card custom-dark-bg-card p-4 border-secondary shadow"> 
        <form action="dashboard.php?tab=productos" method="POST">
            <input type="hidden" name="form_action" value="<?= $action ?>">
            <?php if ($action == 'editar'): ?>
                <input type="hidden" name="ProductoID" value="<?= htmlspecialchars($producto_id) ?>">
            <?php endif; ?>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <?php if ($action == 'crear'): ?>
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= isset($errores_validacion['NombreProducto']) ? 'is-invalid' : '' ?>" 
                               id="nombre" name="NombreProducto" 
                               value="<?= htmlspecialchars($datos_formulario['NombreProducto'] ?? '') ?>" required>
                        <?php if (isset($errores_validacion['NombreProducto'])): ?>
                            <div class="invalid-feedback"><?= $errores_validacion['NombreProducto'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="marca" class="form-label">Marca <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= isset($errores_validacion['Marca']) ? 'is-invalid' : '' ?>" 
                               id="marca" name="Marca" 
                               value="<?= htmlspecialchars($datos_formulario['Marca'] ?? '') ?>" required>
                        <?php if (isset($errores_validacion['Marca'])): ?>
                            <div class="invalid-feedback"><?= $errores_validacion['Marca'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modelo" class="form-label">Modelo (Opcional)</label>
                        <input type="text" class="form-control" id="modelo" name="Modelo" 
                               value="<?= htmlspecialchars($datos_formulario['Modelo'] ?? '') ?>">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tipoid" class="form-label">Tipo de Producto <span class="text-danger">*</span></label>
                        <select class="form-select <?= isset($errores_validacion['TipoProductoID']) ? 'is-invalid' : '' ?>" 
                                 id="tipoid" name="TipoProductoID" required>
                            <option value="" class="bg-dark text-light">Selecciona un tipo</option>
                            <?php foreach ($tipos_productos as $tipo): ?>
                                <option value="<?= $tipo['TipoProductoID'] ?>" class="bg-dark text-light"
                                    <?= (($datos_formulario['TipoProductoID'] ?? '') == $tipo['TipoProductoID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tipo['NombreTipo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errores_validacion['TipoProductoID'])): ?>
                            <div class="invalid-feedback"><?= $errores_validacion['TipoProductoID'] ?></div>
                        <?php elseif (empty($tipos_productos)): ?>
                            <div class="form-text text-danger">⚠️ No hay tipos de producto activos. Crea uno en la pestaña "Tipos de Producto".</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio de Venta ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control <?= isset($errores_validacion['PrecioVenta']) ? 'is-invalid' : '' ?>" 
                               id="precio" name="PrecioVenta" 
                               value="<?= htmlspecialchars($datos_formulario['PrecioVenta'] ?? '') ?>" required>
                        <?php if (isset($errores_validacion['PrecioVenta'])): ?>
                            <div class="invalid-feedback"><?= $errores_validacion['PrecioVenta'] ?></div>
                        <?php endif; ?>
                        <?php if ($action == 'crear'): ?>
                            <div class="form-text text-secondary">Si el producto ya existe, el precio NO se actualizará con este valor.</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock / Cantidad a Ingresar <span class="text-danger">*</span></label>
                        <input type="number" class="form-control <?= isset($errores_validacion['Stock']) ? 'is-invalid' : '' ?>" 
                               id="stock" name="Stock" 
                               value="<?= htmlspecialchars($datos_formulario['Stock'] ?? '0') ?>" required>
                        <?php if (isset($errores_validacion['Stock'])): ?>
                            <div class="invalid-feedback"><?= $errores_validacion['Stock'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="Descripcion" rows="3"><?= htmlspecialchars($datos_formulario['Descripcion'] ?? '') ?></textarea>
            </div>

            <div class="d-flex justify-content-start border-top border-secondary pt-3">
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-save me-1"></i> <?= ($action == 'crear' ? 'Procesar Ingreso / Stock' : 'Actualizar Producto') ?></button>
                <a href="dashboard.php?tab=productos" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>

<?php 
else: // Mostrar Listado de Productos
    $productos = [];
    try {
        $sql = "SELECT p.ProductoID, p.NombreProducto, p.Marca, p.PrecioVenta, p.Stock, t.NombreTipo 
                FROM productos p JOIN tiposdeproducto t ON p.TipoProductoID = t.TipoProductoID
                WHERE p.Estado = 'Activo' ORDER BY p.ProductoID DESC"; 
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $mensaje .= '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>❌ Error:</strong> Error al cargar productos: ' . $e->getMessage() . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
?>
    <div class="mb-4 d-flex justify-content-between align-items-center border-bottom border-secondary pb-3">
        <h3 class="text-light">
            <i class="fas fa-list-ul me-2 text-primary"></i> 
            Listado de Productos
        </h3>
        <a href="dashboard.php?tab=productos&action=crear" class="btn btn-primary shadow">
            <i class="fas fa-plus-circle me-2"></i> Ingreso de Stock / Nuevo Producto
        </a>
    </div>

    <div class="table-responsive p-3 rounded-3 custom-dark-content-area">
        <table class="table table-dark table-striped table-hover align-middle">
            <thead class="table-dark border-bottom border-light"> 
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Marca</th>
                    <th>Tipo</th>
                    <th>Precio Venta</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">
                            <i class="fas fa-box-open me-2"></i> No se encontraron productos activos.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?= $producto['ProductoID'] ?></td>
                            <td class="text-white"><?= htmlspecialchars($producto['NombreProducto']) ?></td>
                            <td><?= htmlspecialchars($producto['Marca']) ?></td>
                            <td><?= htmlspecialchars($producto['NombreTipo']) ?></td>
                            <td><span class="text-success fw-bold">$<?= number_format($producto['PrecioVenta'], 2) ?></span></td>
                            <td>
                                <span class="badge text-bg-<?= $producto['Stock'] > 10 ? 'success' : ($producto['Stock'] > 0 ? 'warning' : 'danger') ?>">
                                    <?= $producto['Stock'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="dashboard.php?tab=productos&action=editar&id=<?= $producto['ProductoID'] ?>" 
                                   class="btn btn-sm btn-info text-white me-1" title="Editar Producto">
                                   <i class="fas fa-edit"></i>
                                </a>
                                <a href="dashboard.php?tab=productos&action=eliminar&id=<?= $producto['ProductoID'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('¿Estás seguro de ELIMINAR este producto?');" 
                                   title="Eliminar Producto">
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