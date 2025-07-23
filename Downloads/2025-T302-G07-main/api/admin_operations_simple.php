<?php
header('Content-Type: application/json');
session_start();

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

// Manejar diferentes tipos de requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_order_status':
            $orderId = $_POST['order_id'] ?? null;
            $status = $_POST['status'] ?? null;
            
            if (!$orderId || !$status) {
                echo json_encode(['success' => false, 'message' => 'ID de orden y estado requeridos']);
                exit;
            }
            
            // Simular actualización exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Estado de orden actualizado exitosamente'
            ]);
            break;
            
        case 'delete_user':
            $userId = $_POST['user_id'] ?? null;
            
            if (!$userId) {
                echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
                exit;
            }
            
            // Simular eliminación exitosa
            echo json_encode([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?> 