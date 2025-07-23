<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado y es admin
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

// Manejar diferentes tipos de requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_products':
            getProducts();
            break;
        case 'get_categories':
            getCategories();
            break;
        case 'get_orders':
            getOrders();
            break;
        case 'get_users':
            getUsers();
            break;
        case 'get_product':
            getProduct($_GET['product_id'] ?? null);
            break;
        case 'get_category':
            getCategory($_GET['category_id'] ?? null);
            break;
        case 'get_order_details':
            getOrderDetails($_GET['order_id'] ?? null);
            break;
        case 'get_order_items':
            getOrderItems($_GET['order_id'] ?? null);
            break;
        case 'get_user':
            getUser($_GET['user_id'] ?? null);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Logging para debug
    error_log("DEBUG: POST request - Action: $action, Data: " . print_r($_POST, true));
    
    switch ($action) {
        case 'create_product':
        case 'update_product':
            handleProduct($_POST);
            break;
        case 'delete_product':
            deleteProduct($_POST['product_id'] ?? null);
            break;
        case 'create_category':
        case 'update_category':
            handleCategory($_POST);
            break;
        case 'delete_category':
            deleteCategory($_POST['category_id'] ?? null);
            break;
        case 'update_order_status':
            updateOrderStatus($_POST['order_id'] ?? null, $_POST['status'] ?? null);
            break;
        case 'update_user':
            updateUser($_POST);
            break;
        case 'delete_user':
            deleteUser($_POST['user_id'] ?? null);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

// Función para obtener productos
function getProducts() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        $products = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'products' => $products
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener productos'
        ]);
    }
}

// Función para obtener categorías
function getCategories() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id 
            ORDER BY c.name
        ");
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'categories' => $categories
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener categorías'
        ]);
    }
}

// Función para obtener órdenes
function getOrders() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, u.username 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute();
        $orders = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'orders' => $orders
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener órdenes'
        ]);
    }
}

// Función para obtener usuarios
function getUsers() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, username, email, role, is_active, created_at 
            FROM users 
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'users' => $users
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener usuarios'
        ]);
    }
}

// Función para obtener un producto específico
function getProduct($productId) {
    global $pdo;
    
    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if ($product) {
            echo json_encode([
                'success' => true,
                'product' => $product
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener producto'
        ]);
    }
}

// Función para obtener una categoría específica
function getCategory($categoryId) {
    global $pdo;
    
    if (!$categoryId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de categoría requerido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch();
        
        if ($category) {
            echo json_encode([
                'success' => true,
                'category' => $category
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener categoría'
        ]);
    }
}

// Función para manejar productos (crear/actualizar)
function handleProduct($data) {
    global $pdo;
    
    $action = $data['action'];
    $name = $data['name'] ?? '';
    $description = $data['description'] ?? '';
    $price = $data['price'] ?? 0;
    $stock_quantity = $data['stock_quantity'] ?? 0;
    $category_id = $data['category_id'] ?? null;
    $is_active = isset($data['is_active']) ? 1 : 0;
    
    if (!$name || $price <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos requeridos incompletos']);
        return;
    }
    
    try {
        // Manejar imagen si se subió
        $image_url = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_url = uploadImage($_FILES['image']);
        }
        
        if ($action === 'create_product') {
            $stmt = $pdo->prepare("
                INSERT INTO products (name, description, price, stock_quantity, category_id, image_url, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $image_url, $is_active]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Producto creado exitosamente'
            ]);
        } else {
            $product_id = $data['product_id'] ?? null;
            if (!$product_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
                return;
            }
            
            // Si no se subió nueva imagen, mantener la existente
            if (!$image_url) {
                $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $current = $stmt->fetch();
                $image_url = $current['image_url'];
            }
            
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ?, image_url = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $price, $stock_quantity, $category_id, $image_url, $is_active, $product_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Producto actualizado exitosamente'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al procesar producto'
        ]);
    }
}

// Función para eliminar producto
function deleteProduct($productId) {
    global $pdo;
    
    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar producto'
        ]);
    }
}

// Función para manejar categorías (crear/actualizar)
function handleCategory($data) {
    global $pdo;
    
    $action = $data['action'];
    $name = $data['name'] ?? '';
    $description = $data['description'] ?? '';
    $is_active = isset($data['is_active']) ? 1 : 0;
    
    if (!$name) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nombre de categoría requerido']);
        return;
    }
    
    try {
        // Manejar imagen si se subió
        $image_url = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image_url = uploadImage($_FILES['image']);
        }
        
        if ($action === 'create_category') {
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, description, image_url, is_active) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$name, $description, $image_url, $is_active]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría creada exitosamente'
            ]);
        } else {
            $category_id = $data['category_id'] ?? null;
            if (!$category_id) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de categoría requerido']);
                return;
            }
            
            // Si no se subió nueva imagen, mantener la existente
            if (!$image_url) {
                $stmt = $pdo->prepare("SELECT image_url FROM categories WHERE id = ?");
                $stmt->execute([$category_id]);
                $current = $stmt->fetch();
                $image_url = $current['image_url'];
            }
            
            $stmt = $pdo->prepare("
                UPDATE categories 
                SET name = ?, description = ?, image_url = ?, is_active = ? 
                WHERE id = ?
            ");
            $stmt->execute([$name, $description, $image_url, $is_active, $category_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al procesar categoría'
        ]);
    }
}

// Función para eliminar categoría
function deleteCategory($categoryId) {
    global $pdo;
    
    if (!$categoryId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de categoría requerido']);
        return;
    }
    
    try {
        // Verificar si hay productos en esta categoría
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar categoría con productos']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar categoría'
        ]);
    }
}

// Función para obtener detalles de una orden
function getOrderDetails($order_id) {
    global $pdo;
    if (!$order_id) {
        echo json_encode(['success' => false, 'message' => 'ID de orden requerido']);
        return;
    }
    $stmt = $pdo->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if ($order) {
        echo json_encode(['success' => true, 'order' => $order]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
    }
}

// Función para obtener los items de una orden
function getOrderItems($order_id) {
    global $pdo;
    if (!$order_id) {
        echo json_encode(['success' => false, 'message' => 'ID de orden requerido']);
        return;
    }
    $stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    echo json_encode(['success' => true, 'items' => $items]);
}

// Función para subir imágenes
function uploadImage($file) {
    $upload_dir = '../uploads/';
    
    // Crear directorio si no existe
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Tipo de archivo no permitido');
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('Archivo demasiado grande');
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $upload_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return 'uploads/' . $filename;
    } else {
        throw new Exception('Error al subir archivo');
    }
}

// Función para actualizar el estado de una orden
function updateOrderStatus($orderId, $status) {
    global $pdo;
    
    // Logging para debug
    error_log("DEBUG: updateOrderStatus called - OrderId: $orderId, Status: $status");
    
    if (!$orderId || !$status) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de orden y estado requeridos']);
        return;
    }
    
    // Validar estado
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $validStatuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Estado no válido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Estado de orden actualizado exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar estado de orden'
        ]);
    }
}

// Obtener usuario por id
function getUser($userId) {
    global $pdo;
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
        return;
    }
    $stmt = $pdo->prepare("SELECT id, username, email, role, is_active FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
}

// Actualizar usuario
function updateUser($data) {
    global $pdo;
    $userId = $data['user_id'] ?? null;
    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $role = $data['role'] ?? 'user';
    $is_active = isset($data['is_active']) ? 1 : 0;
    if (!$userId || !$username || !$email || !$role) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        return;
    }
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$username, $email, $role, $is_active, $userId]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar usuario']);
    }
}

// Eliminar usuario
function deleteUser($userId) {
    global $pdo;
    
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
        return;
    }
    
    try {
        // Verificar si es el último admin
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user && $user['role'] === 'admin') {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
            $stmt->execute();
            $adminCount = $stmt->fetch()['count'];
            
            if ($adminCount <= 1) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar el último administrador']);
                return;
            }
        }
        
        // Iniciar transacción para asegurar consistencia
        $pdo->beginTransaction();
        
        // Eliminar primero los items de las órdenes del usuario
        $stmt = $pdo->prepare("DELETE oi FROM order_items oi INNER JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ?");
        $stmt->execute([$userId]);
        
        // Eliminar las órdenes del usuario
        $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Finalmente eliminar el usuario
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Usuario y todas sus órdenes eliminados exitosamente'
            ]);
        } else {
            $pdo->rollback();
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
    } catch (PDOException $e) {
        $pdo->rollback();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar usuario: ' . $e->getMessage()
        ]);
    }
}
?> 