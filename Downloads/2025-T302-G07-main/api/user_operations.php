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
    
    $response = array('status' => 'error', 'message' => 'Operación no válida');
    
    // Obtener el tipo de operación
    $operation = $_POST['operation'] ?? $_GET['operation'] ?? '';
    
    switch ($operation) {
        case 'get_user_info':
            if (!is_logged_in()) {
                $response = array('status' => 'error', 'message' => 'Usuario no autenticado');
                break;
            }
            
            try {
                $stmt = $pdo->prepare("SELECT id, username, email, created_at, last_login FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $response = array(
                        'status' => 'success',
                        'user' => $user
                    );
                } else {
                    $response = array('status' => 'error', 'message' => 'Usuario no encontrado');
                }
            } catch (PDOException $e) {
                $response = array('status' => 'error', 'message' => 'Error de base de datos');
            }
            break;
            
        case 'update_profile':
            if (!is_logged_in()) {
                $response = array('status' => 'error', 'message' => 'Usuario no autenticado');
                break;
            }
            
            $username = clean_input($_POST['username'] ?? '');
            $email = clean_input($_POST['email'] ?? '');
            
            if (empty($username) || empty($email)) {
                $response = array('status' => 'error', 'message' => 'Todos los campos son requeridos');
                break;
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response = array('status' => 'error', 'message' => 'Email no válido');
                break;
            }
            
            try {
                // Verificar si el username o email ya existe
                $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
                $stmt->execute([$username, $email, $_SESSION['user_id']]);
                
                if ($stmt->rowCount() > 0) {
                    $response = array('status' => 'error', 'message' => 'El usuario o email ya existe');
                    break;
                }
                
                // Actualizar perfil
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$username, $email, $_SESSION['user_id']]);
                
                $_SESSION['username'] = $username;
                
                $response = array('status' => 'success', 'message' => 'Perfil actualizado correctamente');
            } catch (PDOException $e) {
                $response = array('status' => 'error', 'message' => 'Error al actualizar perfil');
            }
            break;
            
        case 'change_password':
            if (!is_logged_in()) {
                $response = array('status' => 'error', 'message' => 'Usuario no autenticado');
                break;
            }
            
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $response = array('status' => 'error', 'message' => 'Todos los campos son requeridos');
                break;
            }
            
            if ($new_password !== $confirm_password) {
                $response = array('status' => 'error', 'message' => 'Las contraseñas no coinciden');
                break;
            }
            
            if (strlen($new_password) < 6) {
                $response = array('status' => 'error', 'message' => 'La contraseña debe tener al menos 6 caracteres');
                break;
            }
            
            try {
                // Verificar contraseña actual
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if (!$user || !verify_password($current_password, $user['password'])) {
                    $response = array('status' => 'error', 'message' => 'Contraseña actual incorrecta');
                    break;
                }
                
                // Actualizar contraseña
                $hashed_password = hash_password($new_password);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$hashed_password, $_SESSION['user_id']]);
                
                $response = array('status' => 'success', 'message' => 'Contraseña actualizada correctamente');
            } catch (PDOException $e) {
                $response = array('status' => 'error', 'message' => 'Error al cambiar contraseña');
            }
            break;
            
        case 'get_stats':
            if (!is_logged_in()) {
                $response = array('status' => 'error', 'message' => 'Usuario no autenticado');
                break;
            }
            
            try {
                // Obtener estadísticas básicas
                $stmt = $pdo->prepare("SELECT COUNT(*) as total_users FROM users");
                $stmt->execute();
                $total_users = $stmt->fetch()['total_users'];
                
                $stmt = $pdo->prepare("SELECT created_at FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user_created = $stmt->fetch()['created_at'];
                
                $response = array(
                    'status' => 'success',
                    'stats' => array(
                        'total_users' => $total_users,
                        'user_created' => $user_created,
                        'days_registered' => floor((time() - strtotime($user_created)) / 86400)
                    )
                );
            } catch (PDOException $e) {
                $response = array('status' => 'error', 'message' => 'Error al obtener estadísticas');
            }
            break;
            
        default:
            $response = array('status' => 'error', 'message' => 'Operación no reconocida');
            break;
    }
    
} catch (Exception $e) {
    $response = array(
        'status' => 'error',
        'message' => 'Error interno del servidor',
        'timestamp' => date('Y-m-d H:i:s')
    );
    
    error_log("Error en user_operations.php: " . $e->getMessage());
}

// Devolver respuesta JSON
echo json_encode($response);
?> 