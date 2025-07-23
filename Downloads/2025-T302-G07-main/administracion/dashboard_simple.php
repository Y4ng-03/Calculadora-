<?php
// Configuración de zona horaria de Venezuela
date_default_timezone_set('America/Caracas');

require_once '../config/database.php';
require_once '../includes/functions.php';

// Verificar si el usuario está logueado y es admin
if (!is_logged_in()) {
    redirect('../login.php');
}

if ($_SESSION['user_role'] !== 'admin') {
    redirect('../dashboard.php');
}

$username = $_SESSION['username'];
$userRole = $_SESSION['user_role'];

// Obtener estadísticas básicas
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
$stmt->execute();
$totalProducts = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
$stmt->execute();
$totalCategories = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$stmt->execute();
$pendingOrders = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stmt->execute();
$totalUsers = $stmt->fetch()['total'];

// Obtener tasa BCV
require_once '../includes/bcv.php';
$bcv_cache_file = __DIR__ . '/../bcv_rate_cache.json';
$tasa_actual = get_bcv_rate();
$mensaje_bcv = '';

if (isset($_POST['guardar_bcv_manual'])) {
    $nueva_tasa = floatval(str_replace(',', '.', $_POST['bcv_manual']));
    $hoy = date('Y-m-d');
    file_put_contents($bcv_cache_file, json_encode([
        'date' => $hoy,
        'rate' => $nueva_tasa
    ]));
    $tasa_actual = $nueva_tasa;
    $mensaje_bcv = '<div class="alert alert-success">Tasa BCV guardada manualmente: Bs. ' . number_format($nueva_tasa, 2, ',', '.') . ' x $1</div>';
}

$bcv_cache = file_exists($bcv_cache_file) ? json_decode(file_get_contents($bcv_cache_file), true) : null;
$ultima_fecha = $bcv_cache && isset($bcv_cache['date']) ? $bcv_cache['date'] : '';
$ultima_hora = $bcv_cache && file_exists($bcv_cache_file) ? date('H:i:s', filemtime($bcv_cache_file)) : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Discarchar</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../js/theme.js"></script>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="nav-logo">Discarchar Admin</h1>
                <div class="nav-menu">
                    <span class="welcome-text">Admin: <?php echo htmlspecialchars($username); ?></span>
                    <a href="../shop.php" class="btn btn-secondary">Ver Tienda</a>
                    <a href="../dashboard.php" class="btn btn-secondary">Mi Cuenta</a>
                    <a href="../logout.php" class="btn btn-outline">Cerrar Sesión</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="sidebar-nav">
                <ul>
                    <li class="nav-item active">
                        <a href="#dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../verificar_sincronizacion.php" target="_blank">
                            <i class="fas fa-cog"></i>
                            Configuración
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../shop.php" target="_blank">
                            <i class="fas fa-store"></i>
                            Ver Tienda
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-main">
            <!-- Dashboard Section -->
            <section id="dashboard" class="admin-section active">
                <div class="section-header">
                    <h2>Dashboard</h2>
                    <p>Bienvenido al panel de administración</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $totalProducts; ?></h3>
                            <p>Productos Activos</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $totalCategories; ?></h3>
                            <p>Categorías</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $pendingOrders; ?></h3>
                            <p>Órdenes Pendientes</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $totalUsers; ?></h3>
                            <p>Usuarios Registrados</p>
                        </div>
                    </div>
                </div>
                
                <div class="quick-actions">
                    <h3>Acciones Rápidas</h3>
                    <div class="action-buttons">
                        <a href="../verificar_sincronizacion.php" target="_blank" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Configuración del Sistema
                        </a>
                        <a href="../shop.php" target="_blank" class="btn btn-primary">
                            <i class="fas fa-store"></i> Ver Tienda
                        </a>
                        <a href="../diagnostico_completo.php" target="_blank" class="btn btn-primary">
                            <i class="fas fa-tools"></i> Diagnóstico Completo
                        </a>
                    </div>
                    
                    <!-- Formulario de Tasa BCV -->
                    <div style="margin-top:30px;">
                        <h4>Configuración de Tasa BCV</h4>
                        <form method="post" style="display:flex; align-items:center; gap:15px; flex-wrap:wrap;">
                            <div>
                                <label for="bcv_manual"><strong>Tasa BCV actual:</strong></label>
                                <input type="number" step="0.01" min="0" name="bcv_manual" id="bcv_manual" 
                                       value="<?php echo htmlspecialchars($tasa_actual); ?>" 
                                       style="width:120px; margin-left:10px;">
                            </div>
                            <button type="submit" name="guardar_bcv_manual" class="btn btn-warning">
                                <i class="fas fa-save"></i> Guardar tasa BCV
                            </button>
                        </form>
                        
                        <?php if ($ultima_fecha) { ?>
                            <div style="margin-top:10px; color:var(--color-text-secondary); font-size:14px;">
                                Última actualización: <b><?php echo htmlspecialchars($ultima_fecha); ?></b> 
                                a las <b><?php echo htmlspecialchars($ultima_hora); ?></b>
                            </div>
                        <?php } ?>
                        
                        <?php echo $mensaje_bcv; ?>
                    </div>
                </div>
                
                <!-- Información del Sistema -->
                <div class="card" style="margin-top:30px;">
                    <h3>Información del Sistema</h3>
                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:20px;">
                        <div>
                            <strong>Versión PHP:</strong> <?php echo phpversion(); ?>
                        </div>
                        <div>
                            <strong>Base de Datos:</strong> <?php echo $dbname; ?>
                        </div>
                        <div>
                            <strong>Zona Horaria:</strong> <?php echo date_default_timezone_get(); ?>
                        </div>
                        <div>
                            <strong>Fecha/Hora:</strong> <?php echo date('Y-m-d H:i:s'); ?>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Función simple para mostrar notificaciones
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                font-weight: 500;
                animation: slideIn 0.3s ease;
            `;
            
            if (type === 'success') {
                notification.style.background = 'var(--color-success)';
            } else if (type === 'error') {
                notification.style.background = 'var(--color-error)';
            } else {
                notification.style.background = 'var(--color-primary)';
            }
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // Agregar estilos de animación
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html> 