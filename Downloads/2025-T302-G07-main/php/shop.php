<?php
// Configuración de zona horaria de Venezuela
date_default_timezone_set('America/Caracas');

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/bcv.php';

$tasa_bcv = get_bcv_rate();

// Leer parámetros de filtros
$sort = $_GET['sort'] ?? 'newest';
$selected_categories = [];
if (isset($_GET['categories'])) {
    if (is_array($_GET['categories'])) {
        $selected_categories = $_GET['categories'];
    } else {
        $selected_categories = explode(',', $_GET['categories']);
    }
}
$order_by = 'p.created_at DESC';
switch ($sort) {
    case 'price_low':
        $order_by = 'p.price ASC';
        break;
    case 'price_high':
        $order_by = 'p.price DESC';
        break;
    case 'name':
        $order_by = 'p.name ASC';
        break;
    case 'newest':
    default:
        $order_by = 'p.created_at DESC';
        break;
}

// Construir filtro de categorías para SQL
$where_categories = '';
$params = [];
if (!empty($selected_categories)) {
    $in = implode(',', array_fill(0, count($selected_categories), '?'));
    $where_categories = " AND p.category_id IN ($in) ";
    $params = array_merge($params, $selected_categories);
}

// Obtener productos activos con filtros y orden
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 $where_categories
    ORDER BY $order_by
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Obtener solo categorías con productos activos
$stmt = $pdo->prepare("
    SELECT c.* FROM categories c
    INNER JOIN products p ON c.id = p.category_id
    WHERE c.is_active = 1 AND p.is_active = 1
    GROUP BY c.id
    ORDER BY c.name ASC
");
$stmt->execute();
$categories = $stmt->fetchAll();

$show_all_categories = count($categories) > 1;

// Definir autenticación del usuario
$isLoggedIn = is_logged_in();
$userRole = $isLoggedIn ? $_SESSION['user_role'] : 'guest';

// Helper para mantener los filtros en la URL
function build_query($overrides = []) {
    $params = $_GET;
    foreach ($overrides as $k => $v) {
        $params[$k] = $v;
    }
    return http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discarchar - Tienda Online</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/shop.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="js/theme.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="nav-logo">Discarchar</h1>
                <div class="nav-menu">
                    <div class="search-container">
                        <input type="text" id="searchInput" placeholder="Buscar productos..." class="search-input">
                        <button class="search-btn"><i class="fas fa-search"></i></button>
                    </div>
                    
                    <div class="cart-icon" onclick="toggleCart()">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount">0</span>
                    </div>
                    
                    <?php if ($isLoggedIn): ?>
                        <div class="user-menu">
                            <span class="welcome-text">Hola, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <?php if ($userRole === 'admin'): ?>
                                <a href="admin/dashboard.php" class="btn btn-secondary">Admin</a>
                            <?php endif; ?>
                            <a href="dashboard.php" class="btn btn-secondary">Mi Cuenta</a>
                            <a href="logout.php" class="btn btn-outline">Cerrar Sesión</a>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                            <a href="register.php" class="btn btn-outline">Registrarse</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Aviso de precios (fuera del contenedor principal) -->
    <div style="background: #e3f2fd; color: #1565c0; padding: 6px 10px; border-radius: 5px; margin: 16px auto 12px auto; text-align: center; font-size: 0.98rem; line-height: 1.3; max-width: 500px;">
        Precios en <b>USD</b> y <b>VES</b> a la tasa BCV del día: <b>Bs. <?php echo number_format($tasa_bcv, 2, ',', '.'); ?></b> x $1
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Filters Section -->
        <aside class="filters-sidebar">
            <div class="filter-section">
                <h3>Filtros</h3>
                <form id="filtersForm" method="get" style="margin:0;">
                    <div class="filter-group">
                        <h4>Categorías</h4>
                        <div class="category-filters">
                            <?php if ($show_all_categories): ?>
                            <label class="filter-checkbox">
                                <input type="checkbox" value="all" id="allCategories" <?php if (empty($selected_categories)) echo 'checked'; ?> onclick="toggleAllCategories(this)">
                                <span>Todas las categorías</span>
                            </label>
                            <?php endif; ?>
                            <?php foreach ($categories as $category): ?>
                            <label class="filter-checkbox">
                                <input type="checkbox" name="categories[]" value="<?php echo $category['id']; ?>" <?php if (in_array($category['id'], $selected_categories)) echo 'checked'; ?> onclick="document.getElementById('filtersForm').submit()">
                                <span><?php echo htmlspecialchars($category['name']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="filter-group">
                        <h4>Ordenar por</h4>
                        <select id="sortSelect" name="sort" onchange="document.getElementById('filtersForm').submit()">
                            <option value="newest" <?php if($sort=='newest') echo 'selected'; ?>>Más recientes</option>
                            <option value="price_low" <?php if($sort=='price_low') echo 'selected'; ?>>Precio: menor a mayor</option>
                            <option value="price_high" <?php if($sort=='price_high') echo 'selected'; ?>>Precio: mayor a menor</option>
                            <option value="name" <?php if($sort=='name') echo 'selected'; ?>>Nombre A-Z</option>
                        </select>
                    </div>
                </form>
                <script>
                // Si se marca "Todas las categorías", desmarcar las demás y enviar el formulario
                function toggleAllCategories(allBox) {
                    if (allBox.checked) {
                        document.querySelectorAll('.category-filters input[type=checkbox][name^=categories]').forEach(cb => cb.checked = false);
                        document.getElementById('filtersForm').submit();
                    }
                }
                // Si se marca cualquier categoría, desmarcar "Todas las categorías"
                document.querySelectorAll('.category-filters input[type=checkbox][name^=categories]').forEach(cb => {
                    cb.addEventListener('change', function() {
                        document.getElementById('allCategories') && (document.getElementById('allCategories').checked = false);
                    });
                });
                </script>
            </div>
            
                <!-- Eliminar la barra de filtrado de precios -->
                <!-- <div class="filter-group">
                    <h4>Precio</h4>
                    <div class="price-filter">
                        <input type="range" id="priceRange" min="0" max="1000" value="1000" onchange="filterProducts()">
                        <div class="price-labels">
                            <span>$0</span>
                            <span id="priceValue">$1000</span>
                        </div>
                    </div>
                </div> -->
                
                
            </div>
        </aside>

        <!-- Products Grid -->
        <section class="products-section">
            <div class="products-header">
                <h2>Productos Disponibles</h2>
                <div class="products-count">
                    <span id="productsCount"><?php echo count($products); ?></span> productos encontrados
                </div>
            </div>
            
            <div class="products-grid" id="productsGrid">
                <?php foreach ($products as $product): ?>
                <div class="product-card" data-category="<?php echo $product['category_id']; ?>" data-price="<?php echo $product['price']; ?>" data-name="<?php echo strtolower($product['name']); ?>">
                    <div class="product-image">
                        <?php if ($product['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <div class="product-placeholder">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        <div class="product-overlay">
                            <button class="btn btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-cart-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                        <div class="product-price">
                            <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                            <span class="price-ves" style="display:block; color:#388e3c; font-size:0.98em;">
                                Bs. <?php echo number_format($product['price'] * $tasa_bcv, 2, ',', '.'); ?>
                            </span>
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <span class="stock in-stock">En stock (<?php echo $product['stock_quantity']; ?>)</span>
                            <?php else: ?>
                                <span class="stock out-of-stock">Sin stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <!-- Shopping Cart Sidebar -->
    <div class="cart-sidebar" id="cartSidebar">
        <div class="cart-header">
            <h3>Carrito de Compras</h3>
            <button class="close-cart" onclick="toggleCart()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="cart-items" id="cartItems">
            <!-- Los items del carrito se cargarán dinámicamente -->
        </div>
        
        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span id="cartTotal">$0.00</span>
            </div>
            <button class="btn btn-primary checkout-btn" onclick="checkout()" disabled>
                Proceder al Pago
            </button>
        </div>
    </div>

    <!-- Cart Overlay -->
    <div class="cart-overlay" id="cartOverlay" onclick="toggleCart()"></div>

    <script>
window.tasaBCV = <?php echo json_encode($tasa_bcv); ?>;
</script>
    <script src="js/script.js"></script>
    <script src="js/shop.js"></script>
</body>
</html> 