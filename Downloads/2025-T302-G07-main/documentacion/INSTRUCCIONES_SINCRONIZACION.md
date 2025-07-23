# Instrucciones de Sincronización - Discarchar

## Problema Identificado
Tu compañero y tú están viendo diferentes productos y hay errores al cargar usuarios porque las bases de datos no están sincronizadas.

## Solución Paso a Paso

### 1. Ejecutar Diagnóstico Completo

**Ambos compañeros deben hacer esto:**

1. Abrir en el navegador: `http://localhost/tu-proyecto/diagnostico_completo.php`
2. Revisar todos los resultados del diagnóstico
3. Identificar qué tablas faltan o tienen problemas

### 2. Sincronizar Base de Datos

**Si las tablas no existen o hay errores:**

1. En la página de diagnóstico, hacer clic en **"Crear/Actualizar Tablas"**
2. Esperar a que se complete el proceso
3. Recargar la página para verificar que todo esté correcto

### 3. Insertar Datos de Ejemplo

**Si no hay productos o usuarios:**

1. En la página de diagnóstico, hacer clic en **"Insertar Datos de Ejemplo"**
2. Esto creará:
   - 4 categorías (Electrónicos, Ropa, Hogar, Deportes)
   - 16 productos de ejemplo
   - Usuario admin (admin/admin123)

### 4. Verificar Configuración

**Ambos deben tener la misma configuración:**

1. Abrir `config/database.php`
2. Verificar que contenga:
   ```php
   <?php
   // Configuración de la base de datos
   // Usar configuración compartida para mantener sincronización entre compañeros
   require_once __DIR__ . '/database_shared.php';
   ?>
   ```

### 5. Limpiar Caché

**Si hay problemas de caché:**

1. En la página de diagnóstico, hacer clic en **"Limpiar Caché"**
2. Esto eliminará archivos de caché que pueden causar problemas

### 6. Verificar Usuarios

**Para verificar que los usuarios se cargan correctamente:**

1. Ir a `http://localhost/tu-proyecto/register.php`
2. Registrar un nuevo usuario
3. Verificar que se guarde correctamente
4. Ir a `http://localhost/tu-proyecto/login.php`
5. Iniciar sesión con el usuario creado

### 7. Verificar Productos

**Para verificar que los productos se muestran correctamente:**

1. Ir a `http://localhost/tu-proyecto/shop.php`
2. Verificar que se muestren los productos
3. Contar cuántos productos aparecen
4. Comparar con tu compañero

## Datos de Ejemplo que se Insertarán

### Categorías:
- Electrónicos
- Ropa  
- Hogar
- Deportes

### Productos (16 total):
- Laptop HP Pavilion ($899.99)
- Smartphone Samsung Galaxy ($599.99)
- Auriculares Bluetooth ($89.99)
- Tablet iPad Air ($649.99)
- Camiseta Básica ($19.99)
- Pantalón Jeans ($49.99)
- Zapatillas Deportivas ($79.99)
- Chaqueta de Cuero ($129.99)
- Lámpara de Mesa ($29.99)
- Sofá de 3 Plazas ($299.99)
- Juego de Sábanas ($39.99)
- Mesa de Centro ($89.99)
- Pelota de Fútbol ($39.99)
- Raqueta de Tenis ($69.99)
- Bicicleta de Spinning ($199.99)
- Pesas de 5kg ($24.99)

### Usuario Admin:
- Usuario: `admin`
- Contraseña: `admin123`
- Rol: `root`

## Verificación Final

**Ambos compañeros deben verificar:**

1. ✅ Misma cantidad de productos (16)
2. ✅ Mismas categorías (4)
3. ✅ Usuario admin funciona
4. ✅ Registro de usuarios funciona
5. ✅ Login funciona
6. ✅ Carrito funciona
7. ✅ Checkout funciona

## Si Persisten los Problemas

### Opción 1: Reset Completo
1. Eliminar la base de datos `discarchar`
2. Crear nueva base de datos `discarchar`
3. Ejecutar `diagnostico_completo.php`
4. Usar "Crear/Actualizar Tablas"
5. Usar "Insertar Datos de Ejemplo"

### Opción 2: Exportar/Importar
1. Un compañero exporta su base de datos completa
2. El otro importa la base de datos exportada
3. Verificar que todo funcione igual

### Opción 3: Usar Base de Datos Compartida
1. Configurar una base de datos en un servidor compartido
2. Ambos conectarse a la misma base de datos
3. Actualizar `config/database_shared.php` con los datos del servidor

## Comandos SQL Útiles

```sql
-- Verificar tablas existentes
SHOW TABLES;

-- Verificar estructura de tabla users
DESCRIBE users;

-- Verificar estructura de tabla products  
DESCRIBE products;

-- Contar usuarios
SELECT COUNT(*) FROM users;

-- Contar productos
SELECT COUNT(*) FROM products;

-- Ver productos con categorías
SELECT p.name, p.price, c.name as category 
FROM products p 
LEFT JOIN categories c ON p.category_id = c.id 
WHERE p.is_active = 1;
```

## Contacto para Soporte

Si después de seguir estas instrucciones persisten los problemas:

1. Ejecutar `diagnostico_completo.php`
2. Tomar captura de pantalla de los resultados
3. Compartir los errores específicos que aparecen
4. Indicar qué pasos ya se intentaron

---

**Nota:** Este archivo debe mantenerse actualizado y compartirse entre todos los miembros del equipo para mantener la sincronización. 