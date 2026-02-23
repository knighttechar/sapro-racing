/**
 * Configuración centralizada de URLs de API
 * Permite cambiar fácilmente entre desarrollo y producción
 *
 * En archivo .env.local puedes definir:
 * VITE_API_URL=http://localhost:8000
 */

// Detectar si estamos en desarrollo o producción
const isDevelopment = process.env.NODE_ENV === 'development'

// Base URL según el ambiente - obtenida de .env.local o definida aquí
const API_BASE_URL =
  import.meta.env.VITE_API_URL ||
  (isDevelopment
    ? 'http://localhost' // ← CAMBIAR AQUÍ para desarrollo local
    : 'https://saproracing.knighttech.com.ar')

// Endpoints de la API
export const API_ENDPOINTS = {
  // Productos
  PRODUCTOS: `${API_BASE_URL}/api.php`,
  AGREGAR_PRODUCTO: `${API_BASE_URL}/agregar_producto.php`,
  EDITAR_PRODUCTO: `${API_BASE_URL}/api_acciones.php?accion=editar`,
  ELIMINAR_PRODUCTO: `${API_BASE_URL}/api_acciones.php?accion=eliminar`,

  // Ventas
  REGISTRAR_VENTA: `${API_BASE_URL}/registrar_venta.php`,

  // Otros
  IMAGENES: `${API_BASE_URL}/imagenes`,
}

// Función auxiliar para obtener la URL base
export function getApiUrl(endpoint) {
  return API_ENDPOINTS[endpoint] || endpoint
}

// Función para construir URL de imagen
export function getImageUrl(nombreImagen) {
  if (!nombreImagen || nombreImagen === 'default.jpg') {
    return 'https://placehold.co/300x300?text=Sapro+Racing'
  }
  return `${API_ENDPOINTS.IMAGENES}/${nombreImagen}`
}

export default API_BASE_URL
