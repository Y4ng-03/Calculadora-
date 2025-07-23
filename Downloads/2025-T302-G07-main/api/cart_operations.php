<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Manejar diferentes tipos de requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener carrito del usuario
    if (isset($_GET['action']) && $_GET['action'] === 'get') {
        getCart($user_id);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'add':
            addToCart($user_id, $input);
            break;
        case 'update':
            updateCartItem($user_id, $input);
            break;
        case 'remove':
            removeFromCart($user_id, $input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

// Función para obtener el carrito del usuario
function getCart($user_id) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.image_url, p.stock_quantity
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ? AND p.is_active = 1
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'cart' => $cart_items
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener el carrito'
        ]);
    }
}

// Función para agregar producto al carrito
function addToCart($user_id, $data) {
    global $pdo;
    
    $product_id = $data['product_id'] ?? null;
    $quantity = $data['quantity'] ?? 1;
    
    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
        return;
    }
    
    try {
        // Verificar que el producto existe y está activo
        $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            return;
        }
        
        // Verificar stock
        if ($product['stock_quantity'] < $quantity) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Stock insuficiente']);
            return;
        }
        
        // Verificar si el producto ya está en el carrito
        $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing_item = $stmt->fetch();
        
        if ($existing_item) {
            // Actualizar cantidad
            $new_quantity = $existing_item['quantity'] + $quantity;
            
            if ($new_quantity > $product['stock_quantity']) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Stock insuficiente']);
                return;
            }
            
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$new_quantity, $existing_item['id']]);
        } else {
            // Agregar nuevo item
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Producto agregado al carrito'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al agregar producto al carrito'
        ]);
    }
}

// Función para actualizar cantidad en el carrito
function updateCartItem($user_id, $data) {
    global $pdo;
    
    $product_id = $data['product_id'] ?? null;
    $quantity = $data['quantity'] ?? null;
    
    if (!$product_id || $quantity === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de producto y cantidad requeridos']);
        return;
    }
    
    if ($quantity < 1) {
        // Si la cantidad es menor a 1, remover el item
        removeFromCart($user_id, $data);
        return;
    }
    
    try {
        // Verificar stock
        $stmt = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            return;
        }
        
        if ($product['stock_quantity'] < $quantity) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Stock insuficiente']);
            return;
        }
        
        // Actualizar cantidad
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE user_id = ? AND product_id = ?");
        $result = $stmt->execute([$quantity, $user_id, $product_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Cantidad actualizada'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado en el carrito']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar cantidad'
        ]);
    }
}

// Función para remover producto del carrito
function removeFromCart($user_id, $data) {
    global $pdo;
    
    $product_id = $data['product_id'] ?? null;
    
    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID de producto requerido']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto removido del carrito'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado en el carrito']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al remover producto del carrito'
        ]);
    }
}
?> 