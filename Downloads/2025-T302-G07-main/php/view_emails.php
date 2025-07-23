<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Verificar si el usuario está logueado
if (!is_logged_in()) {
    redirect('login.php');
}

// Obtener emails simulados
$emails_dir = 'emails/';
$emails = [];

if (is_dir($emails_dir)) {
    $files = scandir($emails_dir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            $filepath = $emails_dir . $file;
            $content = file_get_contents($filepath);
            $email_data = json_decode($content, true);
            if ($email_data) {
                $emails[] = [
                    'file' => $file,
                    'data' => $email_data,
                    'size' => filesize($filepath)
                ];
            }
        }
    }
}

// Ordenar por fecha más reciente
usort($emails, function($a, $b) {
    return strtotime($b['data']['timestamp']) - strtotime($a['data']['timestamp']);
});
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emails Simulados - Discarchar</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .emails-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .email-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #3498db;
        }
        
        .email-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .email-title {
            font-size: 1.3rem;
            color: #2c3e50;
            font-weight: bold;
        }
        
        .email-meta {
            display: flex;
            gap: 20px;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .email-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .email-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        
        .email-details h4 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
        }
        
        .detail-label {
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .detail-value {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .email-products {
            background: white;
            border: 1px solid #ecf0f1;
            border-radius: 8px;
            padding: 20px;
        }
        
        .email-products h4 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .product-item:last-child {
            border-bottom: none;
        }
        
        .product-name {
            color: #2c3e50;
            font-weight: 500;
        }
        
        .product-price {
            color: #7f8c8d;
        }
        
        .product-total {
            color: #2c3e50;
            font-weight: bold;
        }
        
        .total-line {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #ecf0f1;
            font-weight: bold;
            font-size: 1.1rem;
            color: #2c3e50;
        }
        
        .no-emails {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .no-emails i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #bdc3c7;
        }
        
        .status-badge {
            background: #27ae60;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .email-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .email-meta {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="nav-logo">Discarchar</h1>
                <div class="nav-menu">
                    <a href="shop.php" class="btn btn-secondary">Tienda</a>
                    <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                    <a href="logout.php" class="btn btn-outline">Cerrar Sesión</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="emails-container">
        <div style="margin-bottom: 30px;">
            <h1 style="color: #2c3e50; margin-bottom: 10px;">
                <i class="fas fa-envelope"></i> Emails Simulados
            </h1>
            <p style="color: #7f8c8d; margin: 0;">
                Estos son los emails que se habrían enviado si el servidor tuviera configurado SMTP.
            </p>
        </div>

        <?php if (empty($emails)): ?>
        <div class="no-emails">
            <i class="fas fa-inbox"></i>
            <h3>No hay emails simulados</h3>
            <p>Los emails aparecerán aquí después de realizar pedidos o ejecutar pruebas.</p>
            <a href="test_email_simulated.php" class="btn btn-primary" style="margin-top: 20px;">
                <i class="fas fa-play"></i> Ejecutar Prueba
            </a>
        </div>
        <?php else: ?>
        
        <?php foreach ($emails as $email): ?>
        <div class="email-card">
            <div class="email-header">
                <div>
                    <div class="email-title">
                        Confirmación de Pedido #<?php echo $email['data']['order_number']; ?>
                    </div>
                    <div class="email-meta">
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($email['data']['name']); ?></span>
                        <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($email['data']['to']); ?></span>
                        <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($email['data']['timestamp'])); ?></span>
                        <span class="status-badge"><?php echo ucfirst($email['data']['status']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="email-content">
                <div class="email-details">
                    <h4><i class="fas fa-info-circle"></i> Detalles del Pedido</h4>
                    
                    <div class="detail-item">
                        <span class="detail-label">Número de Pedido:</span>
                        <span class="detail-value">#<?php echo $email['data']['order_number']; ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Fecha:</span>
                        <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($email['data']['timestamp'])); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Total:</span>
                        <span class="detail-value">$<?php echo number_format($email['data']['order_data']['total_amount'], 2); ?></span>
                    </div>
                    
                    <div class="detail-item">
                        <span class="detail-label">Estado:</span>
                        <span class="detail-value">Pendiente</span>
                    </div>
                    
                    <div class="detail-item" style="flex-direction: column; align-items: flex-start;">
                        <span class="detail-label">Dirección de Envío:</span>
                        <span class="detail-value" style="margin-top: 5px; font-weight: normal;">
                            <?php echo nl2br(htmlspecialchars($email['data']['order_data']['shipping_address'])); ?>
                        </span>
                    </div>
                </div>
                
                <div class="email-products">
                    <h4><i class="fas fa-shopping-cart"></i> Productos</h4>
                    
                    <?php foreach ($email['data']['order_items'] as $item): ?>
                    <div class="product-item">
                        <div>
                            <div class="product-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="product-price">$<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?></div>
                        </div>
                        <div class="product-total">
                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="total-line">
                        <span>Total:</span>
                        <span>$<?php echo number_format($email['data']['order_data']['total_amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php endif; ?>
        
        <div style="margin-top: 40px; text-align: center;">
            <a href="shop.php" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Ir a la Tienda
            </a>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-user"></i> Dashboard
            </a>
        </div>
    </main>
</body>
</html> 