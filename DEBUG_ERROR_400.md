# 🔧 Debugging Error 400 - Venta

## El Problema

```
POST https://saproracing.knighttech.com.ar/registrar_venta.php 400 (Bad Request)
```

**Causa**: Los datos enviados desde el carrito no son válidos o incompletos.

---

## ✅ Cambios Realizados

### 1. **CartWidget.vue** - Validación de datos mejorada

- Convierte todos los IDs a números (`Number(item.id)`)
- Valida que cantidad sea > 0
- Valida que precio sea >= 0
- Imprime en consola qué se está enviando

### 2. **registrar_venta.php** - Errores más descriptivos

- Devuelve detalles específicos sobre qué falló
- Valida cada campo del carrito
- Retorna lista de errores detallados

---

## 🚀 Cómo Debuggear - Paso a Paso

### Paso 1: Subir archivos actualizados al servidor

Sube estos archivos a `https://saproracing.knighttech.com.ar/`:

```
dist/spa/                        ← Resultado de quasar build
registrar_venta.php              ← ACTUALIZADO
```

### Paso 2: Abre la Consola del Navegador (F12)

1. **Presiona**: F12
2. **Ve a**: Pestaña "Console"
3. **Filtra**: Busca `📤` para ver qué datos se están enviando

### Paso 3: Intenta hacer una venta

1. Agrega un producto al carrito
2. Abre el carrito (botón esquina inferior derecha)
3. Click en "FINALIZAR VENTA"
4. Confirma cuando te lo pida

### Paso 4: Lee el error en la consola

**Deberías ver algo como:**

#### ✅ Si funciona:

```javascript
📤 Reques to: {
  carrito: [
    { id: 1, cantidad: 2, precio: 150.50 }
  ],
  total: 301,
  admin: "Admin"
}

// ✅ Venta registrada correctamente
```

#### ❌ Si falla:

```javascript
❌ Error: Validación de carrito fallida
// O ver qué error específico devuelve el servidor
```

---

## 📊 Estructura Esperada del Carrito

Cada item DEBE tener exactamente estos campos:

```javascript
{
  id: 1,                 // ← NÚMERO (no string)
  cantidad: 2,           // ← NÚMERO > 0
  precio: 150.50         // ← NÚMERO >= 0
}
```

**❌ INCORRECTO:**

```javascript
{
  id: "1",              // ← String
  cantidad: 2,
  precio: "150.50",     // ← String
  nombre: "Producto"    // ← NO debe incluirse
}
```

---

## 🔍 Lista de Verificación

- [ ] Archivo `dist/spa/` subido al servidor
- [ ] Archivo `registrar_venta.php` subido
- [ ] Consola del navegador muestra `📤 Reques to:` con datos
- [ ] Los datos tienen solo: id, cantidad, precio (números)
- [ ] El servidor responde (ver pestaña Network en F12)
- [ ] Si hay error, leer el mensaje detallado en rojo

---

## 🎯 Mensajes de Error Comunes y Soluciones

### Error: "JSON inválido"

```
javascript_error_analysis
→ Problema: Los datos no se serializan correctamente como JSON
→ Solución: Verificar que no haya valores `undefined` o `NaN`
```

### Error: "faltan campos"

```
→ Problema: Un item del carrito no tiene id, cantidad o precio
→ Solución: Verificar que ALL items tengan los 3 campos
```

### Error: "cantidad debe ser > 0"

```
→ Problema: Un producto tiene cantidad = 0
→ Solución: No permitir agregar con cantidad 0
```

### Error: "Stock insuficiente"

```
→ Problema: No hay suficiente stock del producto
→ Solución: Ver base de datos, actualizar stock manualmente si es necesario
```

---

## 🛠️ Comandos Útiles

### Ver los logs del servidor PHP

```bash
# SSH al servidor y ver últimos errores
tail -f /var/log/php-errors.log

# O revisar en el dominio
https://saproracing.knighttech.com.ar/registrar_venta.php
# (POST directamente genera error pero puedes ver estructura)
```

### Test rápido desde curl

```bash
curl -X POST https://saproracing.knighttech.com.ar/registrar_venta.php \
  -H "Content-Type: application/json" \
  -d '{
    "carrito": [{"id": 1, "cantidad": 1, "precio": 100}],
    "total": 100,
    "admin": "Admin"
  }'
```

---

## 📞 Si nada funciona

1. **Verifica en la consola (F12)**:
   - Tab "Network" → busca "registrar_venta.php"
   - Click en la respuesta
   - Lee el JSON response

2. **Verifica base de datos**:
   - ¿La tabla `ventas` existe?
   - ¿La tabla `detalle_ventas` existe?
   - ¿El usuario MySQL tiene permisos?

3. **Verifica permisos**:
   - ¿El servidor puede escribir en BD?
   - ¿Hay restricciones CORS?

4. **Limpia el navegador**:
   - Ctrl+Shift+F5 (reload sin cache)
   - Abre otra ventana incógnito

---

**Versión**: 2.0 - Debugging Mejorado
**Fecha**: 22 de Febrero 2026
