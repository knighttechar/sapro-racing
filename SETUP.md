# Guía de Configuración - Sistema de Ventas Sapro Racing

## Cambios Realizados

### 1. ✅ Campo de Stock en Formulario de Productos

- **Archivo**: `src/components/ProductGrid.vue`
- **Cambio**: Se agregó el input para capturar el campo `stock` en los formularios de agregar y editar productos
- **Ubicación**: Entre el campo de precio y categoría

### 2. ✅ Conexión Real con Registro de Ventas

- **Archivo**: `src/components/CartWidget.vue`
- **Cambio**: Se reemplazó la simulación por una llamada real a `registrar_venta.php`
- **Funcionalidad**:
  - Envía el carrito completo con productos y cantidades
  - Obtiene respuesta del servidor
  - Descuenta el stock automáticamente
  - Registra la venta en la base de datos

### 3. ✅ Base de Datos Actualizada

- **Archivo**: `schema_auditoria.sql`
- **Cambio**: Se incluyeron todas las tablas necesarias:
  - `productos` - Catálogo de productos
  - `usuarios` - Usuarios del sistema
  - `ventas` - Encabezado de ventas
  - `detalle_ventas` - Detalles de cada línea de venta
  - `auditoria` - Registro de cambios

### 4. ✅ Inicializador de Base de Datos

- **Archivo**: `init_db.php`
- **Cambio**: Nuevo script para crear todas las tablas automáticamente

---

## 🚀 INSTRUCCIONES DE CONFIGURACIÓN

### Paso 1: Inicializar la Base de Datos

1. Sube todos los archivos PHP al servidor
2. Accede a: `https://tu-dominio.com/init_db.php`
3. Verifica que recibas una respuesta JSON con `"success": true`

**Respuesta esperada:**

```json
{
  "success": true,
  "mensaje": "Base de datos inicializada correctamente",
  "detalles": {
    "tablas_creadas": ["productos", "usuarios", "ventas", "detalle_ventas", "auditoria"]
  }
}
```

### Paso 2: Verificar Q Resources

1. Compila el proyecto Quasar: `quasar build`
2. Verifica que no haya errores de compilación

### Paso 3: Prueba del Sistema Completo

1. **Agregar un producto**:
   - Rellenar todos los campos incluido **Stock**
   - Hacer click en "GUARDAR"

2. **Editar producto**:
   - Click en el ícono de lápiz
   - Actualizar el stock
   - Click en "ACTUALIZAR"

3. **Realizar una venta**:
   - Agregar productos al carrito
   - Click en el botón del carrito (esquina inferior derecha)
   - Cambiar cantidades si es necesario
   - Click en "FINALIZAR VENTA"
   - Confirmar la venta
   - **Verificar en la base de datos que:**
     - La tabla `ventas` tenga un nuevo registro
     - La tabla `detalle_ventas` tenga los detalles de cada producto
     - El `stock` de los productos se haya restado correctamente

---

## 📊 Estructura de Tablas

### productos

```
id (PK), nombre, codigo (UNIQUE), precio, stock, descripcion,
categoria, marca, imagen, created_at, updated_at
```

### ventas

```
id (PK), total, usuario_admin, fecha, estado
```

### detalle_ventas

```
id (PK), venta_id (FK), producto_id (FK), cantidad,
precio_unitario, subtotal (GENERATED), created_at
```

### auditoria

```
id (PK), timestamp, accion, tabla, registro_id, detalles,
ip_origen, user_agent
```

---

## 🔧 Troubleshooting

### El carrito no registra la venta

- ✓ Revisa que el archivo `registrar_venta.php` esté en el servidor
- ✓ Verifica la consola del navegador (F12) para mensajes de error
- ✓ Confirma que `init_db.php` haya sido ejecutado

### El stock no se descuenta

- ✓ Asegúrate de que la tabla `productos` tenga el campo `stock`
- ✓ Verifica que la tabla `detalle_ventas` exista
- ✓ Revisa los logs del servidor para errores SQL

### Error de conexión a la base de datos

- ✓ Verifica que `config.php` tenga las credenciales correctas
- ✓ Confirma que la base de datos existe
- ✓ Revisa la configuración de permisos en MySQL

---

## 📝 Notas Importantes

- El campo de **stock** es obligatorio al crear un producto
- Las ventas se registran en **transacciones MySQL** para garantizar integridad de datos
- Si hay error en algún producto, se revierte toda la venta
- La base de datos usa **UTF-8 Unicode** en todas las tablas
- Se guardan registros de auditoría de todos los cambios

---

## ✨ URLs Importantes

- **API de productos**: `/api.php` - GET todos los productos
- **Agregar producto**: `/agregar_producto.php` - POST
- **Acciones (editar/eliminar)**: `/api_acciones.php?accion=editar|eliminar` - POST
- **Registrar venta**: `/registrar_venta.php` - POST
- **Inicializar DB**: `/init_db.php` - GET o POST
