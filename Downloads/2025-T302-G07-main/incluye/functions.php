<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función para limpiar y validar datos de entrada
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Función para verificar si el usuario está logueado
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Función para redirigir
function redirect($url) {
    header("Location: $url");
    exit();
}

// Función para generar hash de contraseña
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Función para verificar contraseña
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

// Función para enviar email de confirmación de pedido (versión básica)
function send_order_confirmation_email($order_data, $order_items, $user_email, $user_name) {
    $order_number = str_pad($order_data['id'], 6, '0', STR_PAD_LEFT);
    $order_date = date('d/m/Y H:i', strtotime($order_data['created_at']));
    $total = number_format($order_data['total_amount'], 2);
    
    // Construir el contenido del email
    $subject = "Confirmación de Pedido #$order_number - Discarchar";
    
    $message = "
    <html>
    <head>
        <title>Confirmación de Pedido</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #3498db; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .order-details { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; }
            .product-item { border-bottom: 1px solid #eee; padding: 10px 0; }
            .total { font-weight: bold; font-size: 18px; text-align: right; margin-top: 20px; }
            .footer { text-align: center; padding: 20px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>¡Gracias por tu compra!</h1>
                <p>Tu pedido ha sido confirmado exitosamente</p>
            </div>
            
            <div class='content'>
                <h2>Hola $user_name,</h2>
                <p>Gracias por realizar tu compra en Discarchar. Tu pedido ha sido procesado y está siendo preparado.</p>
                
                <div class='order-details'>
                    <h3>Detalles del Pedido</h3>
                    <p><strong>Número de Pedido:</strong> #$order_number</p>
                    <p><strong>Fecha:</strong> $order_date</p>
                    <p><strong>Estado:</strong> Pendiente</p>
                    <p><strong>Dirección de Envío:</strong> " . htmlspecialchars($order_data['shipping_address']) . "</p>
                    
                    <h4>Productos:</h4>";
    
    foreach ($order_items as $item) {
        $item_total = number_format($item['price'] * $item['quantity'], 2);
        $message .= "
                    <div class='product-item'>
                        <p><strong>" . htmlspecialchars($item['name']) . "</strong></p>
                        <p>Cantidad: " . $item['quantity'] . " x $" . number_format($item['price'], 2) . " = $$item_total</p>
                    </div>";
    }
    
    $message .= "
                    <div class='total'>
                        <p>Total: $$total</p>
                    </div>
                </div>
                
                <h3>Próximos Pasos:</h3>
                <ol>
                    <li>Tu pedido será procesado en las próximas 24-48 horas</li>
                    <li>Recibirás una notificación cuando sea enviado</li>
                    <li>El pago se realizará contra entrega</li>
                </ol>
                
                <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
            </div>
            
            <div class='footer'>
                <p>Discarchar</p>
                <p>Gracias por confiar en nosotros</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Headers para email HTML
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Discarchar <noreply@discarchar.com>" . "\r\n";
    $headers .= "Reply-To: noreply@discarchar.com" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Intentar enviar el email
    $mail_sent = mail($user_email, $subject, $message, $headers);
    
    // Log del intento de envío
    $log_message = date('Y-m-d H:i:s') . " - Email " . ($mail_sent ? "enviado" : "falló") . " a $user_email para pedido #$order_number\n";
    file_put_contents('email_log.txt', $log_message, FILE_APPEND | LOCK_EX);
    
    return $mail_sent;
}

// Función alternativa para enviar email usando Gmail SMTP (requiere configuración)
function send_order_confirmation_email_smtp($order_data, $order_items, $user_email, $user_name) {
    // Esta función requiere PHPMailer y configuración de Gmail
    // Por ahora, solo guardamos en un archivo para simular el envío
    
    $order_number = str_pad($order_data['id'], 6, '0', STR_PAD_LEFT);
    $order_date = date('d/m/Y H:i', strtotime($order_data['created_at']));
    $total = number_format($order_data['total_amount'], 2);
    
    // Crear contenido del email
    $email_content = "
=== CONFIRMACIÓN DE PEDIDO ===
Fecha: " . date('Y-m-d H:i:s') . "
Para: $user_email
Nombre: $user_name

Número de Pedido: #$order_number
Fecha del Pedido: $order_date
Total: $$total

Dirección de Envío: " . htmlspecialchars($order_data['shipping_address']) . "

Productos:
";
    
    foreach ($order_items as $item) {
        $item_total = number_format($item['price'] * $item['quantity'], 2);
        $email_content .= "- " . htmlspecialchars($item['name']) . " x " . $item['quantity'] . " = $$item_total\n";
    }
    
    $email_content .= "
Total: $$total

Próximos Pasos:
1. Tu pedido será procesado en las próximas 24-48 horas
2. Recibirás una notificación cuando sea enviado
3. El pago se realizará contra entrega

Gracias por tu compra!
Discarchar Shop
";
    
    // Guardar en archivo para simular envío
    $filename = "emails/pedido_" . $order_number . "_" . date('Y-m-d_H-i-s') . ".txt";
    
    // Crear directorio si no existe
    if (!is_dir('emails')) {
        mkdir('emails', 0777, true);
    }
    
    file_put_contents($filename, $email_content);
    
    // Log del intento de envío
    $log_message = date('Y-m-d H:i:s') . " - Email simulado guardado en $filename para pedido #$order_number\n";
    file_put_contents('email_log.txt', $log_message, FILE_APPEND | LOCK_EX);
    
    return true;
}

// Función para simular envío de email (para desarrollo)
function send_order_confirmation_email_simulated($order_data, $order_items, $user_email, $user_name) {
    $order_number = str_pad($order_data['id'], 6, '0', STR_PAD_LEFT);
    
    // Crear archivo de simulación
    $simulation_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'to' => $user_email,
        'name' => $user_name,
        'order_number' => $order_number,
        'order_data' => $order_data,
        'order_items' => $order_items,
        'status' => 'simulated'
    ];
    
    // Crear directorio si no existe
    if (!is_dir('emails')) {
        mkdir('emails', 0777, true);
    }
    
    $filename = "emails/simulacion_pedido_" . $order_number . ".json";
    file_put_contents($filename, json_encode($simulation_data, JSON_PRETTY_PRINT));
    
    // Log del intento de envío
    $log_message = date('Y-m-d H:i:s') . " - Email simulado guardado en $filename para pedido #$order_number\n";
    file_put_contents('email_log.txt', $log_message, FILE_APPEND | LOCK_EX);
    
    return true;
}

// Función para enviar email real usando PHPMailer y Gmail SMTP
function send_order_confirmation_email_gmail($order_data, $order_items, $user_email, $user_name) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        // Configuración SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jeanguerrero04@gmail.com';
        $mail->Password = 'lobd fnnp ezoz euku';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Remitente y destinatario
        $mail->setFrom('jeanguerrero04@gmail.com', 'Discarchar');
        $mail->addAddress($user_email, $user_name);

        // Asunto y cuerpo
        $order_number = str_pad($order_data['id'], 6, '0', STR_PAD_LEFT);
        $order_date = date('d/m/Y H:i', strtotime($order_data['created_at']));
        $total = number_format($order_data['total_amount'], 2);
        $subject = "Confirmación de Pedido #$order_number - Discarchar";
        $body = "<h2>¡Gracias por tu compra, $user_name!</h2>";
        $body .= "<p>Tu pedido <b>#$order_number</b> ha sido confirmado el $order_date.</p>";
        $body .= "<h3>Productos:</h3><ul>";
        foreach ($order_items as $item) {
            $item_total = number_format($item['price'] * $item['quantity'], 2);
            $body .= "<li><b>" . htmlspecialchars($item['name']) . ":</b> " . $item['quantity'] . " x $" . number_format($item['price'], 2) . " = $$item_total</li>";
        }
        $body .= "</ul>";
        $body .= "<p><b>Total:</b> $$total</p>";
        $body .= "<p><b>Dirección de envío:</b> " . nl2br(htmlspecialchars($order_data['shipping_address'])) . "</p>";
        $body .= "<p>Pronto recibirás tu pedido. ¡Gracias por confiar en Discarchar!</p>";

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        // Log de éxito
        $log_message = date('Y-m-d H:i:s') . " - Email ENVIADO a $user_email para pedido #$order_number\n";
        file_put_contents('email_log.txt', $log_message, FILE_APPEND | LOCK_EX);
        return true;
    } catch (Exception $e) {
        // Log de error
        $log_message = date('Y-m-d H:i:s') . " - Email FALLÓ a $user_email para pedido #$order_number. Error: " . $mail->ErrorInfo . "\n";
        file_put_contents('email_log.txt', $log_message, FILE_APPEND | LOCK_EX);
        return false;
    }
}
?> 