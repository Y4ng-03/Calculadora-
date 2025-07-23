# DOCUMENTACIÓN COMPLETA DEL SISTEMA DISCARCHAR

## 📋 ÍNDICE
1. [Descripción General](#descripción-general)
2. [Estructura del Proyecto](#estructura-del-proyecto)
3. [Configuración de Base de Datos](#configuración-de-base-de-datos)
4. [Sistema de Autenticación](#sistema-de-autenticación)
5. [Panel de Administración](#panel-de-administración)
6. [Página Principal](#página-principal)
7. [Tienda Online](#tienda-online)
8. [Sistema de Órdenes](#sistema-de-órdenes)
9. [Sistema de Emails](#sistema-de-emails)
10. [Archivos API](#archivos-api)
11. [Funciones JavaScript](#funciones-javascript)
12. [Solución de Problemas](#solución-de-problemas)

---

## 🏗️ DESCRIPCIÓN GENERAL

**Discarchar** es un sistema completo de tienda online desarrollado en PHP con las siguientes características:

- ✅ **Panel de Administración** completo
- ✅ **Sistema de Usuarios** con roles (admin/user)
- ✅ **Gestión de Productos** y Categorías
- ✅ **Sistema de Órdenes** con estados
- ✅ **Carrito de Compras** funcional
- ✅ **Sistema de Emails** de confirmación
- ✅ **Interfaz Responsive** y moderna

---

## 📁 ESTRUCTURA DEL PROYECTO

```
proyectjean/
├── admin/                          # Panel de administración
│   └── dashboard.php              # Dashboard principal del admin
├── api/                           # APIs del sistema
│   ├── admin_operations.php       # API principal del admin
│   ├── cart_operations.php        # API del carrito
│   ├── user_operations.php        # API de usuarios
│   └── .htaccess                  # Configuración para ejecutar PHP
├── config/                        # Configuraciones
│   └── database.php               # Conexión a base de datos
├── css/                          # Estilos CSS
│   ├── admin.css                 # Estilos del panel admin
│   ├── shop.css                  # Estilos de la tienda
│   ├── checkout.css              # Estilos del checkout
│   └── style.css                 # Estilos generales
├── includes/                     # Funciones auxiliares
│   └── functions.php             # Funciones del sistema
├── js/                          # JavaScript
│   ├── admin.js                 # Funciones del panel admin
│   ├── shop.js                  # Funciones de la tienda
│   └── script.js                # Scripts generales
├── uploads/                     # Imágenes subidas
├── vendor/                      # Dependencias (PHPMailer)
├── emails/                      # Emails simulados
├── index.html                   # Página principal (redirige a shop.php)
├── login.php                    # Página de login
├── register.php                 # Página de registro
├── shop.php                     # Tienda online
├── checkout.php                 # Proceso de checkout
├── order_confirmation.php       # Confirmación de orden
└── dashboard.php                # Dashboard de usuario
```

---

## 🗄️ CONFIGURACIÓN DE BASE DE DATOS

### Archivo: `config/database.php`
```php
<?php
$host = 'localhost';
$dbname = 'discarchar';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
```

### Tablas de la Base de Datos:
- **users**: Usuarios del sistema
- **categories**: Categorías de productos
- **products**: Productos de la tienda
- **orders**: Órdenes de compra
- **order_items**: Items de cada orden
- **cart_items**: Items del carrito (temporal)

---

## 🔐 SISTEMA DE AUTENTICACIÓN

### Archivos Principales:
- `login.php` - Página de inicio de sesión
- `register.php` - Página de registro
- `logout.php` - Cerrar sesión

### Roles de Usuario:
- **admin**: Acceso completo al panel de administración
- **user**: Usuario normal de la tienda

### Funciones de Autenticación (`includes/functions.php`):
```php
// Verificar si el usuario está logueado
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Generar hash de contraseña
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verificar contraseña
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}
```

---

## ⚙️ PANEL DE ADMINISTRACIÓN

### Archivo Principal: `admin/dashboard.php`

### Secciones del Panel:
1. **Dashboard** - Estadísticas generales
2. **Productos** - Gestión de productos
3. **Categorías** - Gestión de categorías
4. **Órdenes** - Gestión de órdenes
5. **Usuarios** - Gestión de usuarios

### Funcionalidades del Panel:

#### 📦 Gestión de Productos
- ✅ Crear nuevo producto
- ✅ Editar producto existente
- ✅ Eliminar producto
- ✅ Subir imágenes
- ✅ Gestionar stock
- ✅ Activar/desactivar productos

#### 🏷️ Gestión de Categorías
- ✅ Crear nueva categoría
- ✅ Editar categoría existente
- ✅ Eliminar categoría
- ✅ Ver productos por categoría

#### 📋 Gestión de Órdenes
- ✅ Ver todas las órdenes
- ✅ Actualizar estado de órdenes
- ✅ Ver detalles de orden
- ✅ Filtrar por estado

#### 👥 Gestión de Usuarios
- ✅ Ver todos los usuarios
- ✅ Editar información de usuario
- ✅ Cambiar rol de usuario
- ✅ Eliminar usuario (con órdenes incluidas)
- ✅ Activar/desactivar usuarios

---

## 🏠 PÁGINA PRINCIPAL

### Archivo Principal: `index.html`
- **Función**: Página de entrada que redirige automáticamente a la tienda
- **Redirección**: Usa `<meta http-equiv="refresh">` para redirección inmediata
- **Fallback**: Incluye enlace manual en caso de que la redirección no funcione
- **Diseño**: Página de carga con spinner y logo de Discarchar

---

## 🛒 TIENDA ONLINE

### Archivo Principal: `shop.php`

### Funcionalidades:
- ✅ **Catálogo de productos** con filtros
- ✅ **Búsqueda de productos**
- ✅ **Filtro por categorías**
- ✅ **Carrito de compras**
- ✅ **Proceso de checkout**
- ✅ **Confirmación de orden**

### Carrito de Compras:
- Almacenamiento en sesión
- Agregar/quitar productos
- Actualizar cantidades
- Calcular totales

---

## 📦 SISTEMA DE ÓRDENES

### Estados de Orden:
1. **pending** - Pendiente
2. **processing** - Procesando
3. **shipped** - Enviado
4. **delivered** - Entregado
5. **cancelled** - Cancelado

### Proceso de Compra:
1. Usuario agrega productos al carrito
2. Procede al checkout
3. Completa información de envío
4. Confirma la orden
5. Recibe email de confirmación

---

## 📧 SISTEMA DE EMAILS

### Funciones de Email (`includes/functions.php`):

#### 1. `send_order_confirmation_email()`
- Envía email básico usando `mail()`
- Formato HTML con estilos
- Log de envío en `email_log.txt`

#### 2. `send_order_confirmation_email_smtp()`
- Simula envío SMTP
- Guarda emails en archivos de texto
- Para desarrollo/pruebas

#### 3. `send_order_confirmation_email_simulated()`
- Crea archivos JSON con datos del email
- Ideal para desarrollo
- Sin dependencias externas

---

## 🔌 ARCHIVOS API

### 1. `api/admin_operations.php` - API Principal del Admin

#### Endpoints GET:
- `get_products` - Obtener todos los productos
- `get_categories` - Obtener todas las categorías
- `get_orders` - Obtener todas las órdenes
- `get_users` - Obtener todos los usuarios
- `get_product` - Obtener producto específico
- `get_category` - Obtener categoría específica
- `get_order_details` - Obtener detalles de orden
- `get_order_items` - Obtener items de orden
- `get_user` - Obtener usuario específico

#### Endpoints POST:
- `create_product` / `update_product` - Crear/actualizar producto
- `delete_product` - Eliminar producto
- `create_category` / `update_category` - Crear/actualizar categoría
- `delete_category` - Eliminar categoría
- `update_order_status` - Actualizar estado de orden
- `update_user` - Actualizar usuario
- `delete_user` - Eliminar usuario (con órdenes incluidas)

### 2. `api/cart_operations.php` - API del Carrito

#### Endpoints:
- `add_to_cart` - Agregar producto al carrito
- `remove_from_cart` - Remover producto del carrito
- `update_cart_item` - Actualizar cantidad
- `get_cart` - Obtener carrito actual
- `clear_cart` - Limpiar carrito

### 3. `api/user_operations.php` - API de Usuarios

#### Endpoints:
- `login` - Iniciar sesión
- `register` - Registrar usuario
- `logout` - Cerrar sesión
- `update_profile` - Actualizar perfil

---

## 🎯 FUNCIONES JAVASCRIPT

### Archivo: `js/admin.js`

#### Funciones Principales:

##### Navegación:
```javascript
showSection(sectionName) // Cambiar entre secciones del panel
```

##### Carga de Datos:
```javascript
loadProducts()     // Cargar productos
loadCategories()   // Cargar categorías
loadOrders()       // Cargar órdenes
loadUsers()        // Cargar usuarios
```

##### Gestión de Productos:
```javascript
showProductModal()     // Mostrar modal de producto
editProduct(id)        // Editar producto
deleteProduct(id)      // Eliminar producto
handleProductSubmit()  // Manejar envío de formulario
```

##### Gestión de Categorías:
```javascript
showCategoryModal()    // Mostrar modal de categoría
editCategory(id)       // Editar categoría
deleteCategory(id)     // Eliminar categoría
handleCategorySubmit() // Manejar envío de formulario
```

##### Gestión de Órdenes:
```javascript
viewOrder(id)              // Ver detalles de orden
updateOrderStatus(id)      // Actualizar estado
confirmUpdateStatus(id)    // Confirmar actualización
```

##### Gestión de Usuarios:
```javascript
editUser(id)       // Editar usuario
deleteUser(id)     // Eliminar usuario
closeUserModal()   // Cerrar modal de usuario
```

##### Utilidades:
```javascript
showNotification(message, type)  // Mostrar notificaciones
filterProducts()                 // Filtrar productos
filterOrders()                   // Filtrar órdenes
filterUsers()                    // Filtrar usuarios
debounce(func, wait)             // Debounce para búsquedas
```

### Archivo: `js/shop.js`

#### Funciones Principales:
```javascript
addToCart(productId, quantity)   // Agregar al carrito
removeFromCart(itemId)           // Remover del carrito
updateCartQuantity(itemId, qty)  // Actualizar cantidad
loadCart()                       // Cargar carrito
checkout()                       // Proceder al checkout
```

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### Problemas Comunes y Soluciones:

#### 1. Error 404 en APIs
**Problema**: No se pueden acceder a los archivos PHP en la carpeta `api/`
**Solución**: Verificar que existe el archivo `.htaccess` en la carpeta `api/` con:
```apache
AddType application/x-httpd-php .php
```

#### 2. Error de Conexión a Base de Datos
**Problema**: No se puede conectar a la base de datos
**Solución**: 
- Verificar que XAMPP esté ejecutándose
- Verificar credenciales en `config/database.php`
- Verificar que la base de datos `discarchar` existe

#### 3. No se Pueden Eliminar Usuarios
**Problema**: Error al eliminar usuarios con órdenes
**Solución**: La función `deleteUser()` ahora elimina automáticamente las órdenes del usuario antes de eliminarlo.

#### 4. No se Actualizan los Estados de Órdenes
**Problema**: Los cambios de estado no persisten
**Solución**: Verificar que se esté usando el archivo API correcto (`admin_operations.php`)

#### 5. Emails No se Envían
**Problema**: Los emails de confirmación no llegan
**Solución**: 
- Verificar configuración de email en el servidor
- Usar función de email simulado para desarrollo
- Revisar archivo `email_log.txt` para logs

### Archivos de Debug:
- `php_errors.log` - Errores de PHP
- `email_log.txt` - Logs de emails
- `check_user_orders.php` - Verificar órdenes de usuario
- `test_connection.php` - Probar conexión a BD

---

## 📝 NOTAS IMPORTANTES

### Seguridad:
- ✅ Todas las consultas usan PDO con prepared statements
- ✅ Validación de sesiones en todas las páginas admin
- ✅ Sanitización de datos de entrada
- ✅ Verificación de roles de usuario

### Rendimiento:
- ✅ Uso de transacciones para operaciones críticas
- ✅ Debounce en búsquedas para evitar muchas peticiones
- ✅ Carga dinámica de datos con AJAX
- ✅ Optimización de consultas SQL

### Mantenimiento:
- ✅ Código documentado y estructurado
- ✅ Separación de responsabilidades
- ✅ Funciones reutilizables
- ✅ Manejo de errores consistente

---

## 🚀 CÓMO USAR EL SISTEMA

### 1. Instalación:
1. Clonar/descargar el proyecto en `htdocs/`
2. Crear base de datos `discarchar`
3. Importar `discarchar.sql`
4. Configurar `config/database.php`
5. Acceder a `http://localhost/proyectjean/` (redirige automáticamente a la tienda)

### 2. Crear Usuario Admin:
1. Registrarse normalmente
2. Cambiar rol a "admin" en la base de datos
3. Acceder al panel de administración

### 3. Configurar Productos:
1. Crear categorías
2. Agregar productos
3. Configurar precios y stock
4. Activar productos

### 4. Probar Tienda:
1. Navegar a la tienda
2. Agregar productos al carrito
3. Completar checkout
4. Verificar email de confirmación

---

**Sistema desarrollado con PHP, MySQL, JavaScript y CSS**
**Versión**: 1.0
**Última actualización**: Diciembre 2024 