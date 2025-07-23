<?php
// Configurar headers para JSON y CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once '../includes/functions.php';
    require_once '../config/database.php';
    
    // Verificar si el usuario está logueado
    $response = array(
        'logged_in' => is_logged_in(),
        'timestamp' => date('Y-m-d H:i:s'),
        'status' => 'success'
    );
    
    if (is_logged_in()) {
        // Obtener información adicional del usuario desde la base de datos
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user) {
                $response['user'] = array(
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'created_at' => $user['created_at']
                );
                
                // Actualizar último login
                $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->execute([$_SESSION['user_id']]);
            } else {
                // Usuario no encontrado en BD, limpiar sesión
                session_destroy();
                $response['logged_in'] = false;
                $response['message'] = 'Usuario no encontrado en la base de datos';
            }
        } catch (PDOException $e) {
            $response['error'] = 'Error al obtener datos del usuario';
            $response['status'] = 'error';
        }
    }
    
} catch (Exception $e) {
    $response = array(
        'logged_in' => false,
        'status' => 'error',
        'error' => 'Error interno del servidor',
        'timestamp' => date('Y-m-d H:i:s')
    );
    
    // Log del error (en producción, usar un sistema de logging)
    error_log("Error en check_session.php: " . $e->getMessage());
}

// Devolver respuesta JSON
echo json_encode($response);
?> 