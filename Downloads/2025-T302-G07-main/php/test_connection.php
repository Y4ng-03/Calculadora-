<?php
// Archivo de prueba para verificar la conexión a la base de datos
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - Discarchar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover {
            background: #5a6fd8;
        }
    </style>
</head>
<body>
    <h1>🔧 Test de Conexión - Discarchar</h1>
    
    <div class="test-section">
        <h2>1. Verificación de PHP</h2>
        <?php
        echo "<p class='success'>✅ PHP está funcionando correctamente</p>";
        echo "<p><strong>Versión de PHP:</strong> " . phpversion() . "</p>";
        
        // Verificar extensiones necesarias
        $required_extensions = ['pdo', 'pdo_mysql', 'json'];
        foreach ($required_extensions as $ext) {
            if (extension_loaded($ext)) {
                echo "<p class='success'>✅ Extensión $ext está habilitada</p>";
            } else {
                echo "<p class='error'>❌ Extensión $ext NO está habilitada</p>";
            }
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>2. Conexión a la Base de Datos</h2>
        <?php
        try {
            require_once '../config/database.php';
            echo "<p class='success'>✅ Conexión a la base de datos exitosa</p>";
            echo "<p><strong>Servidor:</strong> $host</p>";
            echo "<p><strong>Base de datos:</strong> $dbname</p>";
            
            // Verificar si la tabla users existe
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() > 0) {
                echo "<p class='success'>✅ Tabla 'users' existe</p>";
                
                // Contar usuarios
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
                $count = $stmt->fetch()['count'];
                echo "<p><strong>Total de usuarios:</strong> $count</p>";
                
                // Mostrar algunos usuarios de ejemplo
                if ($count > 0) {
                    $stmt = $pdo->query("SELECT id, username, email, created_at FROM users LIMIT 5");
                    $users = $stmt->fetchAll();
                    echo "<h3>Usuarios en la base de datos:</h3>";
                    echo "<pre>";
                    foreach ($users as $user) {
                        echo "ID: {$user['id']} | Usuario: {$user['username']} | Email: {$user['email']} | Creado: {$user['created_at']}\n";
                    }
                    echo "</pre>";
                }
            } else {
                echo "<p class='error'>❌ Tabla 'users' NO existe</p>";
                echo "<p class='warning'>⚠️ Ejecuta el archivo discarchar.sql para crear las tablas</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>3. Verificación de Archivos</h2>
        <?php
        $required_files = [
            '../config/database.php',
            '../includes/functions.php',
            'check_session.php',
            '../api/user_operations.php',
            '../css/style.css',
            '../js/script.js'
        ];
        
        foreach ($required_files as $file) {
            if (file_exists($file)) {
                echo "<p class='success'>✅ $file existe</p>";
            } else {
                echo "<p class='error'>❌ $file NO existe</p>";
            }
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>4. Test de Funciones</h2>
        <?php
        try {
            require_once '../includes/functions.php';
            
            // Test de clean_input
            $test_input = "  <script>alert('test')</script>  ";
            $cleaned = clean_input($test_input);
            echo "<p><strong>Test clean_input:</strong> '$test_input' → '$cleaned'</p>";
            
            // Test de hash_password
            $test_password = "123456";
            $hashed = hash_password($test_password);
            echo "<p><strong>Test hash_password:</strong> Contraseña hasheada generada correctamente</p>";
            
            // Test de verify_password
            if (verify_password($test_password, $hashed)) {
                echo "<p class='success'>✅ verify_password funciona correctamente</p>";
            } else {
                echo "<p class='error'>❌ verify_password NO funciona</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error en funciones: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
    
    <div class="test-section">
        <h2>5. Enlaces de Prueba</h2>
        <p>Usa estos enlaces para probar la aplicación:</p>
        <a href="index.html" class="btn">🏠 Página Principal</a>
        <a href="login.php" class="btn">🔐 Login</a>
        <a href="register.php" class="btn">📝 Registro</a>
        <a href="check_session.php" class="btn">🔍 Verificar Sesión (JSON)</a>
    </div>
    
    <div class="test-section">
        <h2>6. Información del Sistema</h2>
        <?php
        echo "<p><strong>Sistema operativo:</strong> " . php_uname() . "</p>";
        echo "<p><strong>Servidor web:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
        echo "<p><strong>Directorio actual:</strong> " . getcwd() . "</p>";
        echo "<p><strong>Permisos de escritura:</strong> " . (is_writable('.') ? 'Sí' : 'No') . "</p>";
        ?>
    </div>
    
    <script>
        // Test básico de JavaScript
        console.log('JavaScript está funcionando correctamente');
        
        // Test de fetch (si estamos en el dashboard)
        if (window.location.pathname.includes('test_connection.php')) {
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    console.log('Test de fetch exitoso:', data);
                })
                .catch(error => {
                    console.error('Error en fetch:', error);
                });
        }
    </script>
</body>
</html> 