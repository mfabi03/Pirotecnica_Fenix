<?php
namespace App\Pirotecnicafenix\Controller;

use App\Pirotecnicafenix\Config\Connect\ConnectDB;
use App\Pirotecnicafenix\Model\CategoriaModel;
use Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==========================================
// 1. CARGA DEL MODELO
// ==========================================
$rutaRaiz = dirname(__DIR__, 2);
$pathModel = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'CategoriaModel.php';

if (file_exists($pathModel)) {
    require_once $pathModel;
} else {
    die("ERROR CRÍTICO: No se encuentra el archivo: " . $pathModel);
}

// ==========================================
// 2. INICIALIZACIÓN DE CONEXIÓN Y MODELO
// ==========================================
try {
    $db = (new ConnectDB())->getConnection();
    $modelo = new \App\Pirotecnicafenix\Model\CategoriaModel($db);
} catch (Exception $e) {
    die("ERROR de conexión a la base de datos: " . $e->getMessage());
}

$type = $_GET['type'] ?? 'list';
$error = null;
$success = null;
$categoria = null;
$categorias = [];

// ==========================================
// 3. PROCESAMIENTO DE FORMULARIOS (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ==========================================
    // 3a. REGISTRAR NUEVA CATEGORÍA (store)
    // ==========================================
    if ($type === 'store') {
        $datosCategoria = [
            'nombre_categoria' => trim($_POST['nombre_categoria'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? '')
        ];

        if (empty($datosCategoria['nombre_categoria'])) {
            $error = 'El nombre de la categoría es obligatorio.';
        } else {
            try {
                if ($modelo->registrarCategoria($datosCategoria)) {
                    // 🔥 GUARDAR EN SESIÓN PARA EL RETORNO
                    $idCategoria = $db->lastInsertId();
                    $_SESSION['nueva_categoria_id'] = $idCategoria;
                    $_SESSION['nueva_categoria_nombre'] = $datosCategoria['nombre_categoria'];
                    $_SESSION['mensaje_rapido'] = "✅ Categoría '{$datosCategoria['nombre_categoria']}' registrada exitosamente";
                    $_SESSION['tipo_rapido'] = 'success';
                    
                    // 🔥 REDIRIGIR DE VUELTA (si viene de registro rápido)
                    if (isset($_GET['return'])) {
                        header("Location: ?url=" . $_GET['return'] . "&type=create");
                        exit;
                    }
                    
                    header("Location: ?url=configuracion&type=list");
                    exit();
                } else {
                    $error = 'No se pudo guardar la categoría.';
                }
            } catch (Exception $e) {
                $error = 'Error al guardar: ' . $e->getMessage();
            }
        }
    }
    
    // ==========================================
    // 3b. 🔥 REGISTRO RÁPIDO CATEGORÍA (MODIFICADO - SIN JSON)
    // ==========================================
    if ($type === 'store_rapido') {
        try {
            // Validar campos requeridos
            $nombreCategoria = trim($_POST['nombre_categoria'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            if ($nombreCategoria === '') {
                throw new Exception('El nombre de la categoría es obligatorio.');
            }

            $resultado = $modelo->registrarCategoria([
                'nombre_categoria' => $nombreCategoria,
                'descripcion' => $descripcion
            ]);

            if (!$resultado) {
                throw new Exception('No se pudo registrar la categoría.');
            }

            $idCategoria = $db->lastInsertId();
            
            // 🔥 GUARDAR EN SESIÓN PARA EL RETORNO
            $_SESSION['nueva_categoria_id'] = $idCategoria;
            $_SESSION['nueva_categoria_nombre'] = $nombreCategoria;
            $_SESSION['mensaje_rapido'] = "✅ Categoría '{$nombreCategoria}' registrada exitosamente";
            $_SESSION['tipo_rapido'] = 'success';
            
            // 🔥 REDIRIGIR DE VUELTA (si viene de registro rápido)
            if (isset($_GET['return'])) {
                header("Location: ?url=" . $_GET['return'] . "&type=create");
                exit;
            }
            
            header("Location: ?url=configuracion&type=list");
            exit;
            
        } catch (Exception $e) {
            $_SESSION['mensaje_rapido'] = "❌ " . $e->getMessage();
            $_SESSION['tipo_rapido'] = 'danger';
            
            if (isset($_GET['return'])) {
                header("Location: ?url=" . $_GET['return'] . "&type=create");
                exit;
            }
            
            header("Location: ?url=configuracion&type=list");
            exit;
        }
    }

    if ($type === 'update') {
        $id = $_POST['id_categoria'] ?? 0;
        
        $datosCategoria = [
            'nombre_categoria' => trim($_POST['nombre_categoria'] ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? '')
        ];

        if (empty($datosCategoria['nombre_categoria'])) {
            $error = 'El nombre de la categoría es obligatorio.';
        } else {
            try {
                if ($modelo->actualizarCategoria($id, $datosCategoria)) {
                    $_SESSION['mensaje'] = '✅ Categoría actualizada exitosamente.';
                    $_SESSION['tipo_mensaje'] = 'success';
                    header("Location: ?url=configuracion&type=list");
                    exit();
                } else {
                    $error = 'No se pudo actualizar la categoría.';
                }
            } catch (Exception $e) {
                $error = 'Error al actualizar: ' . $e->getMessage();
            }
        }
    }
    
    // ==========================================
    // 3c. ELIMINAR CATEGORÍA (delete)
    // ==========================================
    if ($type === 'delete') {
        $id = $_POST['id_categoria'] ?? 0;
        
        try {
            if ($modelo->eliminarCategoria($id)) {
                $_SESSION['mensaje'] = '✅ Categoría eliminada exitosamente.';
                $_SESSION['tipo_mensaje'] = 'success';
                header("Location: ?url=configuracion&type=list");
                exit();
            } else {
                $error = 'No se pudo eliminar la categoría.';
            }
        } catch (Exception $e) {
            $error = 'Error al eliminar: ' . $e->getMessage();
        }
    }
}

// ==========================================
// 4. LÓGICA DE VISTAS
// ==========================================

$baseViewPath = $rutaRaiz . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'configuracion';

if (!is_dir($baseViewPath)) {
    mkdir($baseViewPath, 0777, true);
}

// ==========================================
// 4a. CREAR - Mostrar formulario de registro
// ==========================================
if ($type === 'create') {
    $viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "crearCategoriaView.php";
    if (!file_exists($viewFile)) {
        $viewFile = crearVistaCrear($baseViewPath);
    }
    require_once $viewFile;
    exit();
}

// ==========================================
// 4b. EDITAR - Mostrar formulario de edición
// ==========================================
if ($type === 'edit') {
    $id = $_GET['id'] ?? 0;
    
    try {
        $categoria = $modelo->obtenerCategoriaPorId($id);
        if (!$categoria) {
            die("ERROR: Categoría no encontrada.");
        }
    } catch (Exception $e) {
        die("ERROR al obtener categoría: " . $e->getMessage());
    }
    
    $viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "editarCategoriaView.php";
    if (!file_exists($viewFile)) {
        $viewFile = crearVistaEditar($baseViewPath);
    }
    require_once $viewFile;
    exit();
}

// ==========================================
// 4c. VER DETALLE
// ==========================================
if ($type === 'show') {
    $id = $_GET['id'] ?? 0;
    
    try {
        $categoria = $modelo->obtenerCategoriaPorId($id);
        if (!$categoria) {
            die("ERROR: Categoría no encontrada.");
        }
    } catch (Exception $e) {
        die("ERROR al obtener categoría: " . $e->getMessage());
    }
    
    $viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "detalleCategoriaView.php";
    if (!file_exists($viewFile)) {
        $viewFile = crearVistaDetalle($baseViewPath);
    }
    require_once $viewFile;
    exit();
}

// ==========================================
// 4d. LISTAR (por defecto)
// ==========================================
try {
    $categorias = $modelo->obtenerCategorias();
    
    if (isset($_SESSION['mensaje'])) {
        $success = $_SESSION['mensaje'];
        unset($_SESSION['mensaje']);
    }
    if (isset($_SESSION['tipo_mensaje'])) {
        $tipo_mensaje = $_SESSION['tipo_mensaje'];
        unset($_SESSION['tipo_mensaje']);
    }
} catch (Exception $e) {
    die("ERROR al obtener categorías: " . $e->getMessage());
}

$viewFile = $baseViewPath . DIRECTORY_SEPARATOR . "listCategoriaView.php";
if (!file_exists($viewFile)) {
    $viewFile = crearVistaListar($baseViewPath);
}
require_once $viewFile;

// ==========================================
// 5. FUNCIONES GENERADORAS DE VISTAS
// ==========================================

function crearVistaListar($path) {
    $content = <<<'EOD'
<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-tags me-2"></i> Categorías
                </h3>
                <p class="text-muted mb-0">Administra las categorías para tus productos.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <a href="?url=configuracion&type=create" class="btn btn-gold btn-sm fw-bold">
                    <i class="fas fa-plus me-1"></i> Registrar Categoría
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-custom"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-custom"><?= $error ?></div>
    <?php endif; ?>

    <div class="card card-custom mb-4">
        <div class="table-responsive">
            <table class="table table-fenix table-hover m-0">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="pe-4 text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="fas fa-database mb-2" style="font-size: 2rem; display: block;"></i>
                                No hay categorías registradas.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td class="ps-4"><?= $cat['id_categoria'] ?></td>
                                <td><?= htmlspecialchars($cat['nombre_categoria']) ?></td>
                                <td><?= htmlspecialchars($cat['descripcion'] ?? 'N/A') ?></td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="?url=configuracion&type=show&id=<?= $cat['id_categoria'] ?>" 
                                           class="btn btn-outline-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?url=configuracion&type=edit&id=<?= $cat['id_categoria'] ?>" 
                                           class="btn btn-outline-warning" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display:inline;" 
                                              onsubmit="return confirm('¿Eliminar esta categoría?');">
                                            <input type="hidden" name="id_categoria" value="<?= $cat['id_categoria'] ?>">
                                            <button type="submit" class="btn btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
EOD;
    file_put_contents($path . '/listCategoriaView.php', $content);
    return $path . '/listCategoriaView.php';
}

function crearVistaCrear($path) {
    $content = <<<'EOD'
<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-tag me-2"></i> Registrar Categoría
                </h3>
                <p class="text-muted mb-0">Ingresa los datos de la nueva categoría.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <a href="?url=configuracion&type=list" class="btn btn-secondary btn-sm fw-bold">
                    <i class="fas fa-list me-1"></i> Ver Categorías
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-custom p-4 mb-4 bg-white">
        <form method="POST" action="?url=configuracion&type=store<?= isset($_GET['return']) ? '&return=' . $_GET['return'] : '' ?>">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label form-label-custom fw-bold">
                        Nombre de la Categoría <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nombre_categoria" class="form-control" 
                           placeholder="Ej: Electrónica, Ropa, Alimentos..." required>
                    <small class="text-muted">Nombre único para la categoría</small>
                </div>

                <div class="col-md-12">
                    <label class="form-label form-label-custom">
                        Descripción
                    </label>
                    <textarea name="descripcion" class="form-control" rows="3" 
                              placeholder="Descripción detallada de la categoría (opcional)"></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-gold fw-bold">
                    <i class="fas fa-save me-1"></i> Guardar Categoría
                </button>
                <?php if (isset($_GET['return'])): ?>
                    <a href="?url=<?= $_GET['return'] ?>&type=create" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-1"></i> Cancelar y volver
                    </a>
                <?php else: ?>
                    <a href="?url=configuracion&type=list" class="btn btn-secondary ms-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
EOD;
    file_put_contents($path . '/crearCategoriaView.php', $content);
    return $path . '/crearCategoriaView.php';
}

function crearVistaEditar($path) {
    $content = <<<'EOD'
<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-edit me-2"></i> Editar Categoría
                </h3>
                <p class="text-muted mb-0">Modifica los datos de la categoría seleccionada.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <a href="?url=configuracion&type=list" class="btn btn-secondary btn-sm fw-bold">
                    <i class="fas fa-list me-1"></i> Ver Categorías
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-custom p-3 mb-4 bg-light">
        <div class="row align-items-center">
            <div class="col-md-6">
                <i class="fas fa-info-circle text-primary me-2"></i>
                <strong>Categoría #<?= $categoria['id_categoria'] ?></strong>
                <span class="text-muted ms-2">
                    <?= htmlspecialchars($categoria['nombre_categoria']) ?>
                </span>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="text-muted">
                    <i class="fas fa-tag me-1"></i>
                    ID: <?= $categoria['id_categoria'] ?>
                </span>
            </div>
        </div>
    </div>

    <div class="card card-custom p-4 mb-4 bg-white">
        <form method="POST" action="?url=configuracion&type=update">
            <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">

            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label form-label-custom fw-bold">
                        Nombre de la Categoría <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="nombre_categoria" class="form-control" 
                           value="<?= htmlspecialchars($categoria['nombre_categoria']) ?>" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label form-label-custom">
                        Descripción
                    </label>
                    <textarea name="descripcion" class="form-control" rows="3"><?= htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-gold fw-bold">
                    <i class="fas fa-save me-1"></i> Actualizar Categoría
                </button>
                <a href="?url=configuracion&type=list" class="btn btn-secondary ms-2">
                    <i class="fas fa-times me-1"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
EOD;
    file_put_contents($path . '/editarCategoriaView.php', $content);
    return $path . '/editarCategoriaView.php';
}

function crearVistaDetalle($path) {
    $content = <<<'EOD'
<?php
require_once __DIR__ . '/../header.php';
?>

<div class="col-md-9 col-lg-10">
    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-md-8 col-lg-7">
                <h3 class="m-0 font-weight-bold text-dark">
                    <i class="fas fa-tag me-2"></i> Detalle de la Categoría
                </h3>
                <p class="text-muted mb-0">Información completa de la categoría seleccionada.</p>
            </div>
            <div class="col-md-4 col-lg-5 text-md-end">
                <a href="?url=configuracion&type=list" class="btn btn-secondary btn-sm fw-bold">
                    <i class="fas fa-arrow-left me-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-custom p-4 mb-4 bg-white">
        <div class="row g-4">
            <div class="col-md-12">
                <div class="card bg-light p-3">
                    <h6 class="text-muted text-uppercase small fw-bold border-bottom pb-2">
                        <i class="fas fa-info-circle me-2"></i> Datos de la Categoría
                    </h6>
                    <div class="mt-2">
                        <p><strong>ID:</strong> <span class="badge bg-primary">#<?= $categoria['id_categoria'] ?></span></p>
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($categoria['nombre_categoria']) ?></p>
                        <p><strong>Descripción:</strong> <?= htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom p-4 bg-white">
        <div class="d-flex gap-2 flex-wrap">
            <a href="?url=configuracion&type=list" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <a href="?url=configuracion&type=edit&id=<?= $categoria['id_categoria'] ?>" 
               class="btn btn-gold">
                <i class="fas fa-edit me-1"></i> Editar Categoría
            </a>
            <form method="POST" style="display:inline;" 
                  onsubmit="return confirm('¿Estás seguro de eliminar esta categoría?');">
                <input type="hidden" name="id_categoria" value="<?= $categoria['id_categoria'] ?>">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>
EOD;
    file_put_contents($path . '/detalleCategoriaView.php', $content);
    return $path . '/detalleCategoriaView.php';
}
?>