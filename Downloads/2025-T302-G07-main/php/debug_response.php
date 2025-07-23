<?php
// Script para debuggear las respuestas del servidor
session_start();
require_once 'config/database.php';

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

// Capturar todos los datos de entrada
$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

echo "=== DEBUG RESPONSE ===\n";
echo "Método: $method\n";
echo "Acción: $action\n";
echo "POST data: " . print_r($_POST, true) . "\n";
echo "GET data: " . print_r($_GET, true) . "\n";
echo "Session: " . print_r($_SESSION, true) . "\n";

// Simular la lógica del switch
if ($method === 'POST') {
    switch ($action) {
        case 'update_order_status':
            $orderId = $_POST['order_id'] ?? null;
            $status = $_POST['status'] ?? null;
            echo "Actualizando orden $orderId a estado $status\n";
            
            if (!$orderId || !$status) {
                echo "ERROR: ID de orden y estado requeridos\n";
                break;
            }
            
            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($status, $validStatuses)) {
                echo "ERROR: Estado no válido\n";
                break;
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $stmt->execute([$status, $orderId]);
                
                if ($stmt->rowCount() > 0) {
                    echo "SUCCESS: Estado de orden actualizado exitosamente\n";
                } else {
                    echo "ERROR: Orden no encontrada\n";
                }
            } catch (PDOException $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
            }
            break;
            
        case 'delete_user':
            $userId = $_POST['user_id'] ?? null;
            echo "Eliminando usuario $userId\n";
            
            if (!$userId) {
                echo "ERROR: ID de usuario requerido\n";
                break;
            }
            
            try {
                // Verificar si el usuario tiene órdenes
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
                $stmt->execute([$userId]);
                $result = $stmt->fetch();
                
                if ($result['count'] > 0) {
                    echo "ERROR: No se puede eliminar usuario con órdenes existentes\n";
                    break;
                }
                
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                
                if ($stmt->rowCount() > 0) {
                    echo "SUCCESS: Usuario eliminado exitosamente\n";
                } else {
                    echo "ERROR: Usuario no encontrado\n";
                }
            } catch (PDOException $e) {
                echo "ERROR: " . $e->getMessage() . "\n";
            }
            break;
            
        default:
            echo "ERROR: Acción no válida\n";
    }
} else {
    echo "ERROR: Método no permitido\n";
}

echo "=== FIN DEBUG ===\n";
?> 