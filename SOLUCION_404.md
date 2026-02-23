# 🔧 Solución: Error 404 en API

## El Problema

```
Error: AxiosError: Request failed with status code 404
```

Esta error significa que los archivos PHP no se encuentran en el servidor. Las URLs están hardcodeadas o mal configuradas.

---

## ✅ Solución Implementada

He centralizado todas las URLs de API en un único archivo de configuración: `src/config/api.js`

### Archivo de Configuración

**Ubicación**: `src/config/api.js`

```javascript
// La URL se detecta automáticamente según el ambiente
const API_BASE_URL =
  import.meta.env.VITE_API_URL ||
  (isDevelopment
    ? 'http://localhost' // ← Para desarrollo local
    : 'https://saproracing.knighttech.com.ar') // ← Para producción
```

---

## 🚀 Pasos para Configurar

### 1. Crear archivo `.env.local` en la raíz del proyecto

```bash
# En la carpeta: c:\Users\Angel\Desktop\knighttech\sapro-system\
# Crear archivo: .env.local
```

**Contenido según tu ambiente:**

#### Para Desarrollo Local:

```env
VITE_API_URL=http://localhost:8000
```

#### Para Producción:

```env
VITE_API_URL=https://saproracing.knighttech.com.ar
```

### 2. Verificar estructura de carpetas

Los archivos PHP deben estar en **la raíz del servidor Web**:

```
/public_html/              ← Raíz del servidor
├── api.php                ✓
├── agregar_producto.php   ✓
├── api_acciones.php       ✓
├── registrar_venta.php    ✓
├── init_db.php            ✓
├── config.php             ✓
├── login.php              ✓
├── imagenes/              ✓
│   ├── producto1.jpg
│   └── product2.jpg
└── dist/                  ← Build de Quasar
    ├── index.html
    ├── js/
    └── css/
```

### 3. Compilar Quasar

```bash
cd c:\Users\Angel\Desktop\knighttech\sapro-system
quasar build
```

---

## 🔍 Verificar Configuración

### En Desarrollo Local

1. **Servidor PHP corriendo**:

```bash
# En la carpeta con los PHP
php -S localhost:8000
```

2. **Verificar acceso a API**:
   - Abre: `http://localhost:8000/api.php`
   - Debe devolver JSON de productos

3. **Verificar Quasar DevServer**:
   - URL: `http://localhost:9000` (o similar)
   - Debe cargar sin errores 404

### En Producción

1. **Verificar URLs**:
   - `https://saproracing.knighttech.com.ar/api.php` debe funcionar
   - Todos los archivos PHP deben estar online

2. **CORS habilitado**:
   - Todos los PHP tienen: `header("Access-Control-Allow-Origin: *");`
   - Verifica que no haya restricciones adicionales

---

## 📝 Componentes Actualizados

| Componente          | Cambio                                   |
| ------------------- | ---------------------------------------- |
| `ProductGrid.vue`   | ✅ Usa `API_ENDPOINTS`                   |
| `CartWidget.vue`    | ✅ Usa `API_ENDPOINTS` y `getImageUrl()` |
| `ProductCard.vue`   | ✅ Usa `getImageUrl()`                   |
| `src/config/api.js` | ✅ **NUEVA** - Centraliza todas las URLs |

---

## 🛠️ Troubleshooting

### Error: "VITE_API_URL not found"

- Solución: Reinicia el servidor de Quasar después de crear `.env.local`

```bash
quasar dev
# Ctrl+C
# quasar dev  ← Reiniciar
```

### Error: "Cannot GET /api.php"

- Solución 1: Verifica que los PHP estén en el servidor correcto
- Solución 2: Verifica la URL en `.env.local`
- Solución 3: Confirma que el servidor PHP está corriendo

### Error: "Network error"

- Verifica CORS: Todos los PHP deben tener los headers correctos
- Verifica firewall local: Que no bloquee conexiones

### Las imágenes no cargan

- Función `getImageUrl()` automáticamente valida:
  - Si no existe imagen → muestra placeholder
  - Si existe → construye URL correctamente
- Verifica que carpeta `imagenes/` existe en servidor

---

## 🎯 Flujo Correcto

```
1. Quasar Dev/Build
   ↓
2. Carga componentes Vue
   ↓
3. Importan 'src/config/api.js'
   ↓
4. Lee VITE_API_URL de .env.local
   ↓
5. Construye URLs correctas
   ↓
6. Hace peticiones axios a endpoints reales
   ↓
7. ✅ Datos cargan correctamente
```

---

## 📞 Lista de Verificación

- [ ] `.env.local` creado con URL correcta
- [ ] Servidor PHP corriendo (desarrollo) o online (producción)
- [ ] Archivos PHP en servidor web
- [ ] Quasar compilado/corriendo sin errores
- [ ] Base de datos inicializada (`init_db.php` ejecutado)
- [ ] Carpeta `imagenes/` existe y es accesible
- [ ] CORS headers presentes en PHP
- [ ] Logs del navegador (F12) muestran URLs correctas

---

**Estado**: ✅ Configuración centralizada lista para usar
