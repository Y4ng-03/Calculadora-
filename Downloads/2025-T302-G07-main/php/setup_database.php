<?php
// Script para configurar la base de datos
echo "Configurando base de datos...\n";

// Configuración de la base de datos
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Conectar sin especificar base de datos
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Conexión a MySQL establecida\n";
    
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS discarchar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Base de datos 'discarchar' creada/verificada\n";
    
    // Usar la base de datos
    $pdo->exec("USE discarchar");
    
    // Crear tablas
    $tables = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_login TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE
        )",
        
        "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            image_url VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            stock_quantity INT DEFAULT 0,
            category_id INT,
            image_url VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )",
        
        "CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_product (user_id, product_id)
        )",
        
        "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            shipping_address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        )"
    ];
    
    foreach ($tables as $table) {
        $pdo->exec($table);
    }
    echo "✓ Todas las tablas creadas\n";
    
    // Insertar categorías
    $categories = [
        ['Electrónicos', 'Productos electrónicos y tecnología'],
        ['Ropa', 'Vestimenta y accesorios'],
        ['Hogar', 'Artículos para el hogar'],
        ['Deportes', 'Equipamiento deportivo']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    echo "✓ Categorías insertadas\n";
    
    // Insertar productos
    $products = [
        ['Laptop HP Pavilion', 'Laptop de 15 pulgadas con procesador Intel i5, 8GB RAM, 256GB SSD', 899.99, 10, 1],
        ['Smartphone Samsung Galaxy', 'Teléfono inteligente con cámara de 48MP, 128GB almacenamiento', 599.99, 15, 1],
        ['Auriculares Bluetooth', 'Auriculares inalámbricos con cancelación de ruido', 89.99, 25, 1],
        ['Tablet iPad Air', 'Tablet Apple con pantalla de 10.9 pulgadas, 64GB', 649.99, 8, 1],
        ['Camiseta Básica', 'Camiseta de algodón 100% en varios colores, talles S-XL', 19.99, 50, 2],
        ['Pantalón Jeans', 'Jeans clásicos de alta calidad, diferentes talles', 49.99, 30, 2],
        ['Zapatillas Deportivas', 'Zapatillas cómodas para running y deportes', 79.99, 20, 2],
        ['Chaqueta de Cuero', 'Chaqueta elegante de cuero sintético', 129.99, 12, 2],
        ['Lámpara de Mesa', 'Lámpara LED moderna para escritorio, luz ajustable', 29.99, 20, 3],
        ['Sofá de 3 Plazas', 'Sofá cómodo y elegante para sala de estar', 299.99, 5, 3],
        ['Juego de Sábanas', 'Sábanas de algodón egipcio, 1000 hilos', 39.99, 35, 3],
        ['Mesa de Centro', 'Mesa de centro moderna con almacenamiento', 89.99, 15, 3],
        ['Pelota de Fútbol', 'Pelota oficial de competición, tamaño 5', 39.99, 25, 4],
        ['Raqueta de Tenis', 'Raqueta profesional con funda incluida', 69.99, 18, 4],
        ['Bicicleta de Spinning', 'Bicicleta estática para ejercicios en casa', 199.99, 8, 4],
        ['Pesas de 5kg', 'Par de pesas de 5kg para entrenamiento', 24.99, 30, 4]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO products (name, description, price, stock_quantity, category_id) VALUES (?, ?, ?, ?, ?)");
    foreach ($products as $product) {
        $stmt->execute($product);
    }
    echo "✓ Productos insertados\n";
    
    // Crear usuario admin
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@discarchar.com', $admin_password, 'admin']);
    echo "✓ Usuario administrador creado\n";
    
    echo "\n🎉 ¡Base de datos configurada exitosamente!\n";
    echo "\nCredenciales de administrador:\n";
    echo "Usuario: admin\n";
    echo "Contraseña: admin123\n";
    echo "\nPuedes acceder a la tienda en: http://localhost/proyectjean/shop.php\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?> 