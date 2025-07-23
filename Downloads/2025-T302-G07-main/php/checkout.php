<?php
// Configuración de zona horaria de Venezuela
date_default_timezone_set('America/Caracas');

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/bcv.php';

$tasa_bcv = get_bcv_rate();

// Verificar si el usuario está logueado
if (!is_logged_in()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Obtener información del usuario
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Obtener carrito del usuario
$stmt = $pdo->prepare("
    SELECT c.id, c.quantity, p.id as product_id, p.name, p.price, p.image_url, p.stock_quantity
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ? AND p.is_active = 1
    ORDER BY c.created_at DESC
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Calcular total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Si no hay items en el carrito, redirigir a la tienda
if (empty($cart_items)) {
    redirect('shop.php');
}

// Procesar checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar datos de facturación
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    $errors = [];
    
    // Validaciones
    if (empty($full_name)) {
        $errors[] = 'El nombre completo es requerido';
    }
    
    if (empty($phone)) {
        $errors[] = 'El número de teléfono es requerido';
    } elseif (!preg_match('/^[0-9+\-\s\(\)]{7,15}$/', $phone)) {
        $errors[] = 'El número de teléfono no es válido';
    }
    
    // El email se toma del usuario registrado, no del formulario
    $email = $user['email'];
    
    if (empty($shipping_address)) {
        $errors[] = 'La dirección de envío es requerida';
    }
    
    if (empty($city)) {
        $errors[] = 'La ciudad es requerida';
    }
    
    if (empty($postal_code)) {
        $errors[] = 'El código postal es requerido';
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Crear orden con datos de facturación
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, total_amount, shipping_address, status, billing_name, billing_phone, billing_email, billing_city, billing_postal_code, notes) 
                VALUES (?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $total, $shipping_address, $full_name, $phone, $email, $city, $postal_code, $notes]);
            $order_id = $pdo->lastInsertId();
            
            // Crear items de la orden
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");
            
            foreach ($cart_items as $item) {
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                // Actualizar stock
                $new_stock = $item['stock_quantity'] - $item['quantity'];
                $update_stmt = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
                $update_stmt->execute([$new_stock, $item['product_id']]);
            }
            
            // Limpiar carrito
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $pdo->commit();
            
            // Obtener datos de la orden para el email
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $order_data = $stmt->fetch();
            
            $stmt = $pdo->prepare("
                SELECT oi.*, p.name, p.image_url 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
            
            // Enviar email de confirmación real al email del usuario registrado
            send_order_confirmation_email_gmail($order_data, $order_items, $user['email'], $full_name);
            
            // Redirigir a confirmación
            redirect("order_confirmation.php?order_id=$order_id");
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Error al procesar la orden: ' . $e->getMessage();
            
            // Log del error para debugging
            $log_message = date('Y-m-d H:i:s') . " - Error en checkout para usuario $user_id: " . $e->getMessage() . "\n";
            file_put_contents('php_errors.log', $log_message, FILE_APPEND | LOCK_EX);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Discarchar</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/shop.css">
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <h1 class="header-title">Checkout</h1>
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
            <div class="checkout-content">
                <!-- Resumen del pedido -->
                <div class="order-summary">
                    <h2>Resumen del Pedido</h2>
                    <div class="order-items">
                        <?php foreach ($cart_items as $item): ?>
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
                        <div class="total-line">
                            <span>Subtotal:</span>
                            <span>
                                $<?php echo number_format($total, 2); ?>
                                <br>
                                <span style="color:#388e3c; font-size:0.98em;">
                                    Bs. <?php echo number_format($total * $tasa_bcv, 2, ',', '.'); ?>
                                </span>
                            </span>
                        </div>
                        <div class="total-line">
                            <span>Envío:</span>
                            <span>Gratis</span>
                        </div>
                        <div class="total-line total-final">
                            <span>Total:</span>
                            <span>
                                $<?php echo number_format($total, 2); ?>
                                <br>
                                <span style="color:#388e3c; font-size:0.98em;">
                                    Bs. <?php echo number_format($total * $tasa_bcv, 2, ',', '.'); ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Formulario de checkout -->
                <div class="checkout-form">
                    <h2>Información de Facturación y Envío</h2>
                    
                    <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="checkout-form-content">
                        <!-- Datos personales -->
                        <div class="form-section">
                            <h3>Datos Personales</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name">Nombre Completo *</label>
                                    <input type="text" id="full_name" name="full_name" required 
                                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? $user['username']); ?>"
                                           placeholder="Tu nombre completo">
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Teléfono *</label>
                                    <input type="tel" id="phone" name="phone" required 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                           placeholder="Tu número de teléfono">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email de Confirmación</label>
                                <input type="email" id="email" name="email" readonly 
                                       value="<?php echo htmlspecialchars($user['email']); ?>"
                                       style="background-color: #f8f9fa; color: #6c757d;"
                                       title="El email de confirmación se enviará a tu cuenta registrada">
                                <small style="color: #6c757d; font-size: 0.875rem;">
                                    <i class="fas fa-info-circle"></i> 
                                    La confirmación del pedido se enviará a tu email registrado: <?php echo htmlspecialchars($user['email']); ?>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Dirección de envío -->
                        <div class="form-section">
                            <h3>Dirección de Envío</h3>
                            
                            <div class="form-group">
                                <label for="shipping_address">Dirección Completa *</label>
                                <textarea id="shipping_address" name="shipping_address" rows="3" required 
                                          placeholder="Calle, número, colonia, etc..."><?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">Ciudad *</label>
                                    <input type="text" id="city" name="city" required 
                                           value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>"
                                           placeholder="Tu ciudad">
                                </div>
                                
                                <div class="form-group">
                                    <label for="postal_code">Código Postal *</label>
                                    <input type="text" id="postal_code" name="postal_code" required 
                                           value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>"
                                           placeholder="Código postal">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notas adicionales -->
                        <div class="form-section">
                            <h3>Información Adicional</h3>
                            
                            <div class="form-group">
                                <label for="notes">Notas adicionales (opcional)</label>
                                <textarea id="notes" name="notes" rows="3" 
                                          placeholder="Instrucciones especiales para la entrega, referencias, etc..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <!-- Información de pago -->
                        <div class="payment-info">
                            <h3>Información de Pago</h3>
                            <p class="payment-note">
                                <i class="fas fa-info-circle"></i>
                                Por el momento, solo aceptamos pagos contra entrega. 
                                El pago se realizará al momento de recibir tu pedido.
                            </p>
                        </div>
                        
                        <div class="checkout-actions">
                            <a href="shop.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Seguir Comprando
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Confirmar Pedido
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Validación del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = ['full_name', 'phone', 'shipping_address', 'city', 'postal_code'];
            let isValid = true;
            
            // Limpiar errores previos
            document.querySelectorAll('.field-error').forEach(el => el.remove());
            
            // Validar campos requeridos
            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const value = field.value.trim();
                
                if (!value) {
                    isValid = false;
                    showFieldError(field, 'Este campo es requerido');
                } else if (fieldName === 'phone' && !isValidPhone(value)) {
                    isValid = false;
                    showFieldError(field, 'Teléfono no válido');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, completa todos los campos requeridos correctamente');
                return false;
            }
            
            // Confirmar pedido
            if (!confirm('¿Estás seguro de que quieres confirmar este pedido?')) {
                e.preventDefault();
                return false;
            }
        });
        
        function showFieldError(field, message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.style.color = '#e74c3c';
            errorDiv.style.fontSize = '0.875rem';
            errorDiv.style.marginTop = '5px';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        }
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        function isValidPhone(phone) {
            const phoneRegex = /^[0-9+\-\s\(\)]{7,15}$/;
            return phoneRegex.test(phone);
        }
    </script>
</body>
</html> 