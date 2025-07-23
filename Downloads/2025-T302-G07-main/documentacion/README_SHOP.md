# Sistema de Tienda Online - Discarchar

## Descripción

Sistema completo de tienda online con carrito de compras, panel de administración y gestión de productos. Incluye funcionalidades para consumidores y administradores.

## Características

### Para Consumidores:
- ✅ Catálogo de productos con filtros y búsqueda
- ✅ Carrito de compras funcional
- ✅ Sistema de checkout
- ✅ Confirmación de pedidos
- ✅ Interfaz responsive y moderna

### Para Administradores:
- ✅ Panel de administración completo
- ✅ Gestión de productos (crear, editar, eliminar)
- ✅ Gestión de categorías
- ✅ Gestión de órdenes
- ✅ Gestión de usuarios
- ✅ Subida de imágenes
- ✅ Estadísticas del sistema

## Instalación

### 1. Requisitos del Sistema
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- XAMPP, WAMP, o similar

### 2. Configuración de la Base de Datos

1. Crear una base de datos MySQL llamada `discarchar`
2. Importar el archivo `database_setup.sql` en tu base de datos
3. Verificar que las tablas se crearon correctamente:
   - `users` - Usuarios del sistema
   - `categories` - Categorías de productos
   - `products` - Productos
   - `cart` - Carrito de compras
   - `orders` - Órdenes
   - `order_items` - Items de las órdenes

### 3. Configuración del Proyecto

1. Colocar todos los archivos en tu directorio web
2. Verificar que el directorio `uploads/` tenga permisos de escritura
3. Configurar la conexión a la base de datos en `config/database.php`:

```php
$host = 'localhost';
$dbname = 'discarchar';
$username = 'root';
$password = '';
```

### 4. Usuario Administrador por Defecto

Se crea automáticamente un usuario administrador:
- **Usuario:** admin
- **Contraseña:** admin123
- **Rol:** root

## Estructura del Proyecto

```
proyectjean/
├── api/
│   ├── cart_operations.php      # API del carrito
│   └── admin_operations.php     # API de administración
├── admin/
│   └── dashboard.php            # Panel de administración
├── css/
│   ├── style.css               # Estilos base
│   ├── shop.css                # Estilos de la tienda
│   ├── admin.css               # Estilos del admin
│   └── checkout.css            # Estilos del checkout
├── js/
│   ├── script.js               # JavaScript base
│   ├── shop.js                 # JavaScript de la tienda
│   └── admin.js                # JavaScript del admin
├── uploads/                    # Directorio de imágenes
├── config/
│   └── database.php            # Configuración de BD
├── includes/
│   └── functions.php           # Funciones auxiliares
├── shop.php                    # Página principal de la tienda
├── checkout.php                # Página de checkout
├── order_confirmation.php      # Confirmación de pedido
├── database_setup.sql          # Script de base de datos
└── README_SHOP.md             # Este archivo
```

## Uso del Sistema

### Para Consumidores:

1. **Navegar por la tienda:**
   - Visitar `shop.php`
   - Usar filtros por categoría y precio
   - Buscar productos por nombre

2. **Agregar productos al carrito:**
   - Hacer clic en "Agregar" en cualquier producto
   - Ver el carrito en el icono superior derecho
   - Modificar cantidades o eliminar productos

3. **Completar compra:**
   - Hacer clic en "Proceder al Pago"
   - Llenar información de envío
   - Confirmar pedido

### Para Administradores:

1. **Acceder al panel:**
   - Iniciar sesión con credenciales de admin
   - Hacer clic en "Admin" en la barra de navegación

2. **Gestionar productos:**
   - Ir a la sección "Productos"
   - Crear nuevos productos con imágenes
   - Editar precios, stock y descripciones
   - Activar/desactivar productos

3. **Gestionar categorías:**
   - Ir a la sección "Categorías"
   - Crear y organizar categorías
   - Asignar productos a categorías

4. **Gestionar órdenes:**
   - Ver todas las órdenes en la sección "Órdenes"
   - Actualizar estados de envío
   - Ver detalles de cada pedido

## Funcionalidades Técnicas

### Carrito de Compras:
- Persistencia en base de datos
- Validación de stock
- Cálculo automático de totales
- Interfaz dinámica con AJAX

### Panel de Administración:
- CRUD completo para productos y categorías
- Subida de imágenes con validación
- Gestión de estados de órdenes
- Estadísticas en tiempo real

### Seguridad:
- Validación de roles de usuario
- Sanitización de datos
- Protección contra SQL injection
- Validación de archivos subidos

## Personalización

### Cambiar Colores:
Editar las variables CSS en los archivos de estilos:
```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --success-color: #28a745;
    --danger-color: #dc3545;
}
```

### Agregar Categorías:
1. Ir al panel de administración
2. Sección "Categorías"
3. Hacer clic en "Nueva Categoría"

### Configurar Envío:
Modificar la lógica en `checkout.php` para integrar con servicios de envío.

## Solución de Problemas

### Error de Conexión a BD:
- Verificar credenciales en `config/database.php`
- Asegurar que MySQL esté ejecutándose
- Verificar que la base de datos existe

### Imágenes no se Suben:
- Verificar permisos del directorio `uploads/`
- Asegurar que PHP tenga permisos de escritura
- Verificar límites de tamaño de archivo en PHP

### Carrito no Funciona:
- Verificar que el usuario esté logueado
- Revisar la consola del navegador para errores JavaScript
- Verificar que las APIs estén accesibles

## Tecnologías Utilizadas

- **Backend:** PHP 7.4+, MySQL
- **Frontend:** HTML5, CSS3, JavaScript (ES6+)
- **Librerías:** Font Awesome (iconos)
- **Base de Datos:** MySQL con PDO
- **Arquitectura:** MVC simplificado

## Contribución

Para contribuir al proyecto:
1. Fork el repositorio
2. Crear una rama para tu feature
3. Hacer commit de tus cambios
4. Crear un Pull Request

## Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo LICENSE para más detalles.

## Soporte

Para soporte técnico o preguntas:
- Crear un issue en el repositorio
- Contactar al equipo de desarrollo

---

**Desarrollado con ❤️ para Discarchar** 