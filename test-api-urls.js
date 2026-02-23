/**
 * Script de prueba - Verifica que las URLs de API estén configuradas correctamente
 *
 * Uso: Pega esto en la consola del navegador (F12) cuando cargas la aplicación
 */

// Importar configuración
import { API_ENDPOINTS, getImageUrl } from './src/config/api.js'

console.log('=== VERIFICACIÓN DE URLs de API ===\n')

console.log('📍 ENDPOINTS CONFIGURADOS:')
Object.entries(API_ENDPOINTS).forEach(([key, url]) => {
  console.log(`  ${key}: ${url}`)
})

console.log('\n💾 BASE DE DATOS:')
console.log(`  Inicializar: ${API_ENDPOINTS.PRODUCTOS.replace('/api.php', '/init_db.php')}`)

console.log('\n🖼️ FUNCIÓN DE IMÁGENES:')
console.log(`  getImageUrl('default.jpg'): ${getImageUrl('default.jpg')}`)
console.log(`  getImageUrl('producto1.jpg'): ${getImageUrl('producto1.jpg')}`)

console.log('\n✅ Si todas las URLs están correctas (localhost o dominio correcto), estás listo!')
console.log('❌ Si ves URLs incorrectas, edita .env.local con la URL correcta')
