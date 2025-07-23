<?php
header('Content-Type: application/json');
session_start();

// Configuración de base de datos directa
$host = 'localhost';
$dbname = 'discarchar';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
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
    
    switch ($action) {
        case 'update_order_status':
            updateOrderStatus($_POST['order_id'] ?? null, $_POST['status'] ?? null);
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

// Función para actualizar el estado de una orden
function updateOrderStatus($orderId, $status) {
    global $pdo;
    
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