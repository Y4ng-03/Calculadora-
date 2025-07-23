<?php
// Configuración de zona horaria de Venezuela
date_default_timezone_set('America/Caracas');

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/bcv.php';

// Verificar si el usuario está logueado
if (!is_logged_in()) {
    redirect('login.php');
}

$order_id = $_GET['order_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    redirect('shop.php');
}

// Obtener información de la orden
$stmt = $pdo->prepare("
    SELECT o.*, u.username, u.email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    redirect('shop.php');
}

// Obtener items de la orden
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

$tasa_bcv = get_bcv_rate();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido - Discarchar</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/shop.css">
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .confirmation-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .confirmation-icon {
            width: 80px;
            height: 80px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
        }
        
        .confirmation-title {
            color: #28a745;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .confirmation-subtitle {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .order-details {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .order-details h3 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 15px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
        }
        
        .detail-row:last-child {
            margin-bottom: 0;
            border-top: 1px solid #ecf0f1;
            padding-top: 15px;
            font-weight: bold;
        }
        
        .detail-label {
            color: #7f8c8d;
        }
        
        .detail-value {
            color: #2c3e50;
            font-weight: 500;
        }
        
        .billing-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        
        .billing-section h4 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 1.1rem;
        }
        
        .billing-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .billing-item {
            display: flex;
            flex-direction: column;
        }
        
        .billing-label {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .billing-value {
            color: #2c3e50;
            font-weight: 500;
        }
        
        .next-steps {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 30px;
            border-left: 4px solid #3498db;
        }
        
        .next-steps h3 {
            margin: 0 0 20px 0;
            color: #2c3e50;
        }
        
        .steps-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .steps-list li {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px 0;
        }
        
        .steps-list li:last-child {
            margin-bottom: 0;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .step-text {
            color: #2c3e50;
            line-height: 1.5;
        }
        
        .confirmation-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
        }
        
        .confirmation-actions .btn {
            padding: 15px 30px;
            font-size: 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .email-notification {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .confirmation-actions {
                flex-direction: column;
            }
            
            .confirmation-actions .btn {
                width: 100%;
                justify-content: center;
            }
            
            .billing-info {
                grid-template-columns: 1fr;
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
                    <a href="shop.php" class="btn btn-secondary">Volver a la Tienda</a>
                    <a href="dashboard.php" class="btn btn-secondary">Mi Cuenta</a>
                    <a href="logout.php" class="btn btn-outline">Cerrar Sesión</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Aviso de precios (fuera del contenedor principal) -->
    <div style="background: #e3f2fd; color: #1565c0; padding: 6px 10px; border-radius: 5px; margin: 16px auto 12px auto; text-align: center; font-size: 0.98rem; line-height: 1.3; max-width: 500px;">
        Precios en <b>USD</b> y <b>VES</b> a la tasa BCV del día: <b>Bs. <?php echo number_format($tasa_bcv, 2, ',', '.'); ?></b> x $1
    </div>

    <!-- Main Content -->
    <main class="checkout-main">
        <div class="checkout-container">
            <!-- Confirmación Header -->
            <div class="confirmation-header">
                <div class="confirmation-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1 class="confirmation-title">¡Pedido Confirmado!</h1>
                <p class="confirmation-subtitle">
                    Gracias por tu compra. Tu pedido ha sido procesado exitosamente.
                </p>
            </div>

            <div class="checkout-content">
                <!-- Detalles de la orden -->
                <div class="order-details">
                    <h3>Detalles del Pedido</h3>
                    
                    <div class="detail-row">
                        <span class="detail-label">Número de Pedido:</span>
                        <span class="detail-value">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Fecha del Pedido:</span>
                        <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Estado:</span>
                        <span class="detail-value">
                            <?php
                            $status_map = [
                                'pending' => 'Pendiente',
                                'processing' => 'Procesando',
                                'shipped' => 'Enviado',
                                'delivered' => 'Entregado',
                                'cancelled' => 'Cancelado'
                            ];
                            echo $status_map[$order['status']] ?? $order['status'];
                            ?>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span class="detail-label">Total:</span>
                        <span class="detail-value">$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                    
                    <!-- Datos de facturación -->
                    <?php if ($order['billing_name']): ?>
                    <div class="billing-section">
                        <h4>Información de Facturación</h4>
                        <div class="billing-info">
                            <div class="billing-item">
                                <span class="billing-label">Nombre:</span>
                                <span class="billing-value"><?php echo htmlspecialchars($order['billing_name']); ?></span>
                            </div>
                            <div class="billing-item">
                                <span class="billing-label">Teléfono:</span>
                                <span class="billing-value"><?php echo htmlspecialchars($order['billing_phone']); ?></span>
                            </div>
                            <div class="billing-item">
                                <span class="billing-label">Email:</span>
                                <span class="billing-value"><?php echo htmlspecialchars($order['billing_email']); ?></span>
                            </div>
                            <div class="billing-item">
                                <span class="billing-label">Ciudad:</span>
                                <span class="billing-value"><?php echo htmlspecialchars($order['billing_city']); ?></span>
                            </div>
                            <div class="billing-item">
                                <span class="billing-label">Código Postal:</span>
                                <span class="billing-value"><?php echo htmlspecialchars($order['billing_postal_code']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Dirección de envío -->
                    <div class="billing-section">
                        <h4>Dirección de Envío</h4>
                        <p style="margin: 0; color: #2c3e50; line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                        </p>
                    </div>
                    
                    <!-- Notas adicionales -->
                    <?php if ($order['notes']): ?>
                    <div class="billing-section">
                        <h4>Notas Adicionales</h4>
                        <p style="margin: 0; color: #2c3e50; line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Próximos pasos -->
                <div class="next-steps">
                    <h3>Próximos Pasos</h3>
                    <ul class="steps-list">
                        <li>
                            <div class="step-number">1</div>
                            <div class="step-text">
                                <strong>Confirmación por Email:</strong> Recibirás un email de confirmación con los detalles de tu pedido.
                            </div>
                        </li>
                        <li>
                            <div class="step-number">2</div>
                            <div class="step-text">
                                <strong>Procesamiento:</strong> Tu pedido será procesado y preparado para el envío en las próximas 24-48 horas.
                            </div>
                        </li>
                        <li>
                            <div class="step-number">3</div>
                            <div class="step-text">
                                <strong>Envío:</strong> Te notificaremos cuando tu pedido sea enviado con información de seguimiento.
                            </div>
                        </li>
                        <li>
                            <div class="step-number">4</div>
                            <div class="step-text">
                                <strong>Entrega:</strong> Tu pedido será entregado en la dirección especificada. El pago se realizará contra entrega.
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Resumen de productos -->
            <div class="order-summary" style="margin-top: 30px;">
                <h2>Resumen de tu Pedido</h2>
                <div class="order-items">
                    <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <div class="item-image">
                            <?php if ($item['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <?php else: ?>
                                <div class="product-placeholder">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="item-details">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p class="item-price">
                                $<?php echo number_format($item['price'], 2); ?> x <?php echo $item['quantity']; ?>
                                <br>
                                <span style="color:#388e3c; font-size:0.98em;">
                                    Bs. <?php echo number_format($item['price'] * $tasa_bcv, 2, ',', '.'); ?> x <?php echo $item['quantity']; ?>
                                </span>
                            </p>
                        </div>
                        <div class="item-total">
                            $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            <br>
                            <span style="color:#388e3c; font-size:0.98em;">
                                Bs. <?php echo number_format($item['price'] * $item['quantity'] * $tasa_bcv, 2, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="order-total">
                    <div class="total-line total-final">
                        <span>Total:</span>
                        <span>
                            $<?php echo number_format($order['total_amount'], 2); ?>
                            <br>
                            <span style="color:#388e3c; font-size:0.98em;">
                                Bs. <?php echo number_format($order['total_amount'] * $tasa_bcv, 2, ',', '.'); ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="confirmation-actions">
                <a href="shop.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Seguir Comprando
                </a>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-user"></i> Ver Mis Pedidos
                </a>
            </div>
        </div>
    </main>

    <script>
        // Mostrar mensaje de éxito
        setTimeout(() => {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #28a745;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                z-index: 10000;
                animation: slideIn 0.3s ease-out;
            `;
            notification.textContent = '¡Pedido confirmado exitosamente!';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => {
                    if (notification.parentElement) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }, 1000);
    </script>
</body>
</html> 