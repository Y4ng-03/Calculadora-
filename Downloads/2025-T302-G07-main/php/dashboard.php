<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Verificar si el usuario está logueado
if (!is_logged_in()) {
    redirect('login.php');
}

$username = $_SESSION['username'];

// Obtener información del usuario
$stmt = $pdo->prepare("SELECT email, created_at, last_login FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userData = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Discarchar</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="nav-logo">Discarchar</h1>
            <div class="nav-menu">
                <span class="welcome-text">Bienvenido, <?php echo htmlspecialchars($username); ?></span>
                <a href="logout.php" class="btn btn-secondary">Cerrar Sesión</a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="dashboard">
            <h2>Panel de Control</h2>
            <div class="dashboard-content">
                <div class="card">
                    <h3>Información del Usuario</h3>
                    <div id="userInfo">
                        <p><strong>Usuario:</strong> <?php echo htmlspecialchars($username); ?></p>
                        <p><strong>ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
                        <p><strong>Email:</strong> <span id="userEmail"><?php echo htmlspecialchars($userData['email']); ?></span></p>
                        <p><strong>Miembro desde:</strong> <span id="userCreated"><?php echo date('d \d\e F \d\e Y, H:i', strtotime($userData['created_at'])); ?></span></p>
                        <p><strong>Último acceso:</strong> <span id="userLastLogin"><?php echo $userData['last_login'] ? date('d/m/Y, H:i', strtotime($userData['last_login'])) : 'Nunca'; ?></span></p>
                    </div>
                </div>
                
                <div class="card">
                    <h3>Estadísticas</h3>
                    <div id="userStats">
                        <p><strong>Total de usuarios:</strong> <span id="totalUsers">Cargando...</span></p>
                        <p><strong>Días registrado:</strong> <span id="daysRegistered">Cargando...</span></p>
                    </div>
                </div>
                
                <div class="card">
                    <h3>Acciones Rápidas</h3>
                    <div class="action-buttons">
                        <a href="shop.php" class="btn btn-primary" style="text-decoration: none; display: inline-block;">
                            <i class="fas fa-shopping-cart"></i> Ir a la Tienda
                        </a>
                        <a href="view_emails.php" class="btn btn-secondary" style="text-decoration: none; display: inline-block;">
                            <i class="fas fa-envelope"></i> Ver Emails
                        </a>
                        <button class="btn btn-primary" onclick="loadUserProfile()">
                            Ver Perfil Completo
                        </button>
                        <button class="btn btn-primary" onclick="showSettings()">
                            Configuración
                        </button>
                        <button class="btn btn-primary" onclick="refreshData()">
                            Actualizar Datos
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para perfil -->
    <div id="profileModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Perfil de Usuario</h3>
            <div id="profileContent">
                <p>Cargando perfil...</p>
            </div>
            <button class="btn btn-secondary" onclick="closeModal('profileModal')">Cancelar</button>
        </div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html> 