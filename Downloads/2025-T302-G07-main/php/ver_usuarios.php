<?php
require_once 'config/database.php';

echo "<h1>👥 Usuarios Registrados en Discarchar</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .user-card { 
        background: #f8f9fa; 
        border: 1px solid #dee2e6; 
        border-radius: 8px; 
        padding: 15px; 
        margin: 10px 0; 
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .admin { border-left: 4px solid #dc3545; }
    .user { border-left: 4px solid #28a745; }
    .user-info { margin: 5px 0; }
    .user-id { font-weight: bold; color: #007bff; }
    .user-role { 
        display: inline-block; 
        padding: 2px 8px; 
        border-radius: 12px; 
        font-size: 12px; 
        font-weight: bold;
    }
    .role-admin { background: #dc3545; color: white; }
    .role-user { background: #28a745; color: white; }
    .user-date { color: #6c757d; font-size: 14px; }
    .stats { 
        background: #e9ecef; 
        padding: 15px; 
        border-radius: 8px; 
        margin: 20px 0;
        text-align: center;
    }
    .count { font-size: 24px; font-weight: bold; color: #007bff; }
</style>";

try {
    // Obtener estadísticas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $total_users = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $total_admins = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $total_regular_users = $stmt->fetch()['total'];
    
    // Mostrar estadísticas
    echo "<div class='stats'>";
    echo "<h2>📊 Estadísticas de Usuarios</h2>";
    echo "<p><span class='count'>$total_users</span> usuarios totales</p>";
    echo "<p><span class='count'>$total_admins</span> administradores</p>";
    echo "<p><span class='count'>$total_regular_users</span> usuarios regulares</p>";
    echo "</div>";
    
    // Obtener todos los usuarios
    $stmt = $pdo->query("SELECT id, username, email, role, is_active, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "<h2>👤 Lista de Usuarios</h2>";
        
        foreach ($users as $user) {
            $role_class = $user['role'] === 'admin' ? 'admin' : 'user';
            $role_badge_class = $user['role'] === 'admin' ? 'role-admin' : 'role-user';
            $status_text = $user['is_active'] ? 'Activo' : 'Inactivo';
            $status_color = $user['is_active'] ? '#28a745' : '#dc3545';
            
            echo "<div class='user-card $role_class'>";
            echo "<div class='user-info'>";
            echo "<span class='user-id'>ID: {$user['id']}</span> ";
            echo "<span class='user-role $role_badge_class'>{$user['role']}</span>";
            echo "</div>";
            echo "<div class='user-info'><strong>Usuario:</strong> {$user['username']}</div>";
            echo "<div class='user-info'><strong>Email:</strong> {$user['email']}</div>";
            echo "<div class='user-info'><strong>Estado:</strong> <span style='color: $status_color;'>$status_text</span></div>";
            echo "<div class='user-info user-date'><strong>Registrado:</strong> " . date('d/m/Y H:i', strtotime($user['created_at'])) . "</div>";
            echo "</div>";
        }
    } else {
        echo "<p>No hay usuarios registrados.</p>";
    }
    
    // Información adicional
    echo "<div class='stats'>";
    echo "<h3>ℹ️ Información</h3>";
    echo "<p><strong>Base de datos:</strong> $dbname</p>";
    echo "<p><strong>Host:</strong> $host</p>";
    echo "<p><strong>Fecha de verificación:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?> 