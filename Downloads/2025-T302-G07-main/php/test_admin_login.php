<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$username = 'admin';
$password = 'admin123';

$stmt = $pdo->prepare('SELECT id, username, email, password, role, is_active FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo '<h2 style="color:red">No existe el usuario admin en la base de datos.</h2>';
    exit;
}

$hash = $user['password'];
$activo = $user['is_active'];
$role = $user['role'];
$email = $user['email'];

$verifica = password_verify($password, $hash);

echo '<h2>Diagnóstico de login admin</h2>';
echo '<ul>';
echo '<li>Usuario: <b>' . htmlspecialchars($user['username']) . '</b></li>';
echo '<li>Email: <b>' . htmlspecialchars($email) . '</b></li>';
echo '<li>Rol: <b>' . htmlspecialchars($role) . '</b></li>';
echo '<li>Activo: <b>' . ($activo ? 'Sí' : 'No') . '</b></li>';
echo '<li>Hash en BD: <code>' . htmlspecialchars($hash) . '</code></li>';
echo '<li>¿password_verify("admin123", hash)?: <b style="color:' . ($verifica ? 'green' : 'red') . '">' . ($verifica ? 'SÍ' : 'NO') . '</b></li>';
echo '</ul>';

if ($verifica && $activo && $role === 'admin') {
    echo '<h3 style="color:green">¡El usuario admin debería poder iniciar sesión!</h3>';
} else {
    echo '<h3 style="color:red">Hay un problema con el usuario admin. Revisa los datos arriba.</h3>';
}
?> 