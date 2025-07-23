<?php
// Configuración compartida de base de datos para Discarchar
// Este archivo debe ser usado por ambos compañeros para mantener sincronización

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'discarchar';
$username = 'root';
$password = '';

// Configuración adicional
$charset = 'utf8mb4';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password, $options);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Función para verificar si la base de datos está configurada correctamente
function check_database_setup() {
    global $pdo;
    
    $required_tables = ['users', 'categories', 'products', 'cart', 'orders', 'order_items'];
    $missing_tables = [];
    
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $existing_tables = [];
        while ($row = $stmt->fetch()) {
            // Obtener el primer valor del array, sin importar la clave
            $existing_tables[] = reset($row);
        }
        
        foreach ($required_tables as $table) {
            if (!in_array($table, $existing_tables)) {
                $missing_tables[] = $table;
            }
        }
        
        return $missing_tables;
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// Función para crear las tablas si no existen
function create_missing_tables($missing_tables) {
    global $pdo;
    
    if (empty($missing_tables) || isset($missing_tables['error'])) {
        return false;
    }
    
    $sql_file = file_get_contents(__DIR__ . '/../BD/discarchar.sql');
    $statements = explode(';', $sql_file);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
            } catch (Exception $e) {
                // Ignorar errores de tablas que ya existen
                if (strpos($e->getMessage(), 'already exists') === false) {
                    error_log("Error creando tabla: " . $e->getMessage());
                }
            }
        }
    }
    
    return true;
}

// Función para insertar datos de ejemplo si no existen
function insert_sample_data_if_needed() {
    global $pdo;
    
    try {
        // Verificar si ya hay productos
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
        $product_count = $stmt->fetch()['count'];
        
        if ($product_count == 0) {
            // Insertar categorías
            $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
            $categories = [
                ['Electrónicos', 'Productos electrónicos y tecnología'],
                ['Ropa', 'Vestimenta y accesorios'],
                ['Hogar', 'Artículos para el hogar'],
                ['Deportes', 'Equipamiento deportivo']
            ];
            
            foreach ($categories as $cat) {
                $stmt->execute($cat);
            }
            
            // Insertar productos
            $stmt = $pdo->prepare("INSERT IGNORE INTO products (name, description, price, stock_quantity, category_id) VALUES (?, ?, ?, ?, ?)");
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
            
            foreach ($products as $prod) {
                $stmt->execute($prod);
            }
            
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error insertando datos de ejemplo: " . $e->getMessage());
        return false;
    }
}

// Función para verificar y crear usuario admin si no existe
function ensure_admin_user_exists() {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
        $stmt->execute();
        
        if ($stmt->rowCount() == 0) {
            // Crear usuario admin por defecto (password: admin123)
            $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute(['admin', 'admin@discarchar.com', $hashed_password, 'root']);
            return true;
        }
        
        return false;
    } catch (Exception $e) {
        error_log("Error creando usuario admin: " . $e->getMessage());
        return false;
    }
}

// Auto-configuración al incluir este archivo
$missing_tables = check_database_setup();
if (!empty($missing_tables) && !isset($missing_tables['error'])) {
    create_missing_tables($missing_tables);
    insert_sample_data_if_needed();
    ensure_admin_user_exists();
}
?> 