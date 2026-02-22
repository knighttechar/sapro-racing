// src/composables/useCarrito.js
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'

// Creamos el estado FUERA de la función para que sea global (compartido)
const carrito = ref([])

export function useCarrito() {
  const $q = useQuasar()

  // Agregar producto al carrito
  const agregarAlCarrito = (producto) => {
    // Verificamos si ya está en el carrito
    const itemExistente = carrito.value.find(item => item.id === producto.id)

    if (itemExistente) {
      itemExistente.cantidad++
    } else {
      carrito.value.push({
        ...producto,
        cantidad: 1
      })
    }

    $q.notify({
      message: 'Agregado al pedido',
      color: 'positive',
      icon: 'shopping_cart',
      position: 'bottom-right',
      timeout: 1000
    })
  }

  // Eliminar un ítem
  const eliminarDelCarrito = (id) => {
    carrito.value = carrito.value.filter(item => item.id !== id)
  }

  // Vaciar todo
  const vaciarCarrito = () => {
    carrito.value = []
  }

  // Cálculos automáticos
  const totalItems = computed(() => carrito.value.reduce((acc, item) => acc + item.cantidad, 0))
  const totalPrecio = computed(() => carrito.value.reduce((acc, item) => acc + (Number(item.precio) * item.cantidad), 0))

  return {
    carrito,
    agregarAlCarrito,
    eliminarDelCarrito,
    vaciarCarrito,
    totalItems,
    totalPrecio
  }
}
