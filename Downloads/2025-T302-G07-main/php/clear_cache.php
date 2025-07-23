<?php
// Script para limpiar caché y forzar recarga
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpiar Caché - Discarchar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Limpiar Caché del Sistema</h1>
        
        <p class="warning">Si estás experimentando problemas con el panel de administración (como mensajes de error incorrectos), esto puede solucionarlo.</p>
        
        <h2>Pasos para limpiar la caché:</h2>
        
        <ol>
            <li><strong>Presiona Ctrl + F5</strong> (o Cmd + Shift + R en Mac) para forzar la recarga completa de la página</li>
            <li><strong>Limpia la caché del navegador</strong>:
                <ul>
                    <li>Chrome: Ctrl + Shift + Delete</li>
                    <li>Firefox: Ctrl + Shift + Delete</li>
                    <li>Edge: Ctrl + Shift + Delete</li>
                </ul>
            </li>
            <li><strong>O abre el panel en una ventana de incógnito</strong></li>
        </ol>
        
        <h2>Enlaces directos:</h2>
        
        <a href="admin/dashboard.php" class="btn">🛠️ Panel de Administración</a>
        <a href="shop.php" class="btn">🛒 Tienda</a>
        <a href="index.html" class="btn">🏠 Inicio</a>
        
        <h2>Información del sistema:</h2>
        <p><strong>Timestamp actual:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <p><strong>Versión del archivo JS:</strong> <?php echo filemtime('js/admin.js'); ?></p>
        
        <p class="success">✅ Después de limpiar la caché, los problemas deberían resolverse.</p>
    </div>
    
    <script>
        // Forzar recarga de recursos
        if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
            window.location.reload();
        }
    </script>
</body>
</html> 