<?php
// Configuración de zona horaria de Venezuela
date_default_timezone_set('America/Caracas');

require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado y es admin
if (!is_logged_in()) {
    redirect('../login.php');
}

if ($_SESSION['user_role'] !== 'admin') {
    redirect('../dashboard.php');
}

$username = $_SESSION['username'];
$userRole = $_SESSION['user_role'];

// Obtener estadísticas
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
$stmt->execute();
$totalProducts = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
$stmt->execute();
$totalCategories = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$stmt->execute();
$pendingOrders = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stmt->execute();
$totalUsers = $stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Discarchar</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../js/theme.js"></script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="nav-logo">Discarchar Admin</h1>
                <div class="nav-menu">
                    <span class="welcome-text">Admin: <?php echo htmlspecialchars($username); ?></span>
                    <a href="../shop.php" class="btn btn-secondary">Ver Tienda</a>
                    <a href="../dashboard.php" class="btn btn-secondary">Mi Cuenta</a>
                    <a href="../logout.php" class="btn btn-outline">Cerrar Sesión</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active">
                        <a href="#dashboard" onclick="showSection('dashboard')">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#products" onclick="showSection('products')">
                            <i class="fas fa-box"></i>
                            Productos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#categories" onclick="showSection('categories')">
                            <i class="fas fa-tags"></i>
                            Categorías
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#orders" onclick="showSection('orders')">
                            <i class="fas fa-shopping-cart"></i>
                            Órdenes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#users" onclick="showSection('users')">
                            <i class="fas fa-users"></i>
                            Usuarios
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Dashboard Section -->
            <section id="dashboard" class="admin-section active">
                <div class="section-header">
                    <h2>Dashboard</h2>
                    <p>Bienvenido al panel de administración</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $totalProducts; ?></h3>
                            <p>Productos Activos</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $totalCategories; ?></h3>
                            <p>Categorías</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $pendingOrders; ?></h3>
                            <p>Órdenes Pendientes</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $totalUsers; ?></h3>
                            <p>Usuarios Registrados</p>
                        </div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <h3>Acciones Rápidas</h3>
                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="showSection('products')">
                            <i class="fas fa-plus"></i> Agregar Producto
                        </button>
                        <button class="btn btn-primary" onclick="showSection('categories')">
                            <i class="fas fa-plus"></i> Agregar Categoría
                        </button>
                        <button class="btn btn-primary" onclick="showSection('orders')">
                            <i class="fas fa-eye"></i> Ver Órdenes
                        </button>
                        <!-- --- NUEVO FORMULARIO MANUAL PARA LA TASA BCV --- -->
                        <?php
                        require_once '../includes/bcv.php';
                        $bcv_cache_file = __DIR__ . '/../bcv_rate_cache.json';
                        $tasa_actual = get_bcv_rate();
                        $mensaje_bcv = '';
                        if (isset($_POST['guardar_bcv_manual'])) {
                            $nueva_tasa = floatval(str_replace(',', '.', $_POST['bcv_manual']));
                            $hoy = date('Y-m-d');
                            file_put_contents($bcv_cache_file, json_encode([
                                'date' => $hoy,
                                'rate' => $nueva_tasa
                            ]));
                            $tasa_actual = $nueva_tasa;
                            $mensaje_bcv = '<div class="success" style="margin-top:10px; color: green; font-weight:bold;">Tasa BCV guardada manualmente: Bs. ' . number_format($nueva_tasa, 2, ',', '.') . ' x $1</div>';
                        }
                        $bcv_cache = file_exists($bcv_cache_file) ? json_decode(file_get_contents($bcv_cache_file), true) : null;
                        $ultima_fecha = $bcv_cache && isset($bcv_cache['date']) ? $bcv_cache['date'] : '';
                        $ultima_hora = $bcv_cache && file_exists($bcv_cache_file) ? date('H:i:s', filemtime($bcv_cache_file)) : '';
                        ?>
                        <div style="margin-top:20px;">
                            <form method="post" style="display:inline;">
                                <label for="bcv_manual"><b>Tasa BCV actual:</b></label> 
                                <input type="number" step="0.01" min="0" name="bcv_manual" id="bcv_manual" value="<?php echo htmlspecialchars($tasa_actual); ?>" style="width:100px;"> 
                                <button type="submit" name="guardar_bcv_manual" class="btn btn-warning"><i class="fas fa-save"></i> Guardar tasa BCV</button>
                            </form>
                            <?php if ($ultima_fecha) { ?>
                                <div style="margin-top:5px; color:#555; font-size:13px;">Última actualización: <b><?php echo htmlspecialchars($ultima_fecha); ?></b> a las <b><?php echo htmlspecialchars($ultima_hora); ?></b></div>
                            <?php } ?>
                            <?php echo $mensaje_bcv; ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Products Section -->
            <section id="products" class="admin-section">
                <div class="section-header">
                    <h2>Gestionar Productos</h2>
                    <button class="btn btn-primary" onclick="showProductModal()">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </button>
                </div>
                
                <div class="table-container">
                    <div class="table-filters">
                        <input type="text" id="productSearch" placeholder="Buscar productos..." class="search-input">
                        <select id="categoryFilter" class="filter-select">
                            <option value="">Todas las categorías</option>
                        </select>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="admin-table" id="productsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Imagen</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                <!-- Los productos se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Categories Section -->
            <section id="categories" class="admin-section">
                <div class="section-header">
                    <h2>Gestionar Categorías</h2>
                    <button class="btn btn-primary" onclick="showCategoryModal()">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </button>
                </div>
                
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="admin-table" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Productos</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="categoriesTableBody">
                                <!-- Las categorías se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Orders Section -->
            <section id="orders" class="admin-section">
                <div class="section-header">
                    <h2>Gestionar Órdenes</h2>
                </div>
                
                <div class="table-container">
                    <div class="table-filters">
                        <select id="orderStatusFilter" class="filter-select">
                            <option value="">Todos los estados</option>
                            <option value="pending">Pendiente</option>
                            <option value="processing">Procesando</option>
                            <option value="shipped">Enviado</option>
                            <option value="delivered">Entregado</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="admin-table" id="ordersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <!-- Las órdenes se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Users Section -->
            <section id="users" class="admin-section">
                <div class="section-header">
                    <h2>Gestionar Usuarios</h2>
                </div>
                
                <div class="table-container">
                    <div class="table-filters">
                        <input type="text" id="userSearch" placeholder="Buscar usuarios..." class="search-input">
                        <select id="userRoleFilter" class="filter-select">
                            <option value="">Todos los roles</option>
                            <option value="user">Usuario</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="admin-table" id="usersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Fecha Registro</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                <!-- Los usuarios se cargarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="productModalTitle">Nuevo Producto</h3>
            </div>
            <form id="productForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="productName">Nombre del Producto</label>
                    <input type="text" id="productName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="productDescription">Descripción</label>
                    <textarea id="productDescription" name="description" rows="4" required></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="productPrice">Precio</label>
                        <input type="number" id="productPrice" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="productStock">Stock</label>
                        <input type="number" id="productStock" name="stock_quantity" min="0" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="productCategory">Categoría</label>
                    <select id="productCategory" name="category_id" required>
                        <option value="">Seleccionar categoría</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="productImage">Imagen</label>
                    <input type="file" id="productImage" name="image" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="productActive" name="is_active" checked>
                        Producto activo
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeProductModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="categoryModalTitle">Nueva Categoría</h3>
            </div>
            <form id="categoryForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="categoryName">Nombre de la Categoría</label>
                    <input type="text" id="categoryName" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="categoryDescription">Descripción</label>
                    <textarea id="categoryDescription" name="description" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="categoryImage">Imagen</label>
                    <input type="file" id="categoryImage" name="image" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="categoryActive" name="is_active" checked>
                        Categoría activa
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Overlay -->
    <div id="modalOverlay" class="modal-overlay" onclick="closeAllModals()"></div>

    <!-- User Modal -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="userModalTitle">Editar Usuario</h3>
            </div>
            <form id="userForm">
                <input type="hidden" id="userId" name="user_id">
                <div class="form-group">
                    <label for="userName">Usuario</label>
                    <input type="text" id="userName" name="username" required>
                </div>
                <div class="form-group">
                    <label for="userEmail">Email</label>
                    <input type="email" id="userEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="userRole">Rol</label>
                    <select id="userRole" name="role" required>
                        <option value="user">Usuario</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="userActive" name="is_active">
                        Usuario activo
                    </label>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/admin.js?v=<?php echo time(); ?>"></script>
</body>
</html> 