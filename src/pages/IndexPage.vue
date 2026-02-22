<template>
  <q-page class="fondo-racing">
    <div class="q-pa-md row items-center justify-between backdrop-blur">
      <div class="column">
        <div class="text-h6 text-grey-9 text-weight-bold">CATÁLOGO DE PRODUCTOS</div>
        <div class="text-subtitle2 text-primary text-weight-bold">
          {{ textoFiltro }}
        </div>
      </div>
      <q-chip outline color="grey-9" icon="event" class="bg-white">
        Actualizado: {{ fechaHoy }}
      </q-chip>
    </div>

    <ProductGrid :filtro-categoria="categoriaActual" :busqueda="termBusqueda" />

    <CartWidget v-if="isAdmin" />

  </q-page>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import ProductGrid from 'components/ProductGrid.vue'
import CartWidget from 'components/CartWidget.vue' // <--- IMPORTAMOS EL WIDGET

const route = useRoute()
const categoriaActual = ref('todos')
const termBusqueda = ref('')
const isAdmin = ref(false) // Necesario para mostrar el carrito solo si es admin

const fechaHoy = computed(() => new Date().toLocaleDateString('es-AR'))

const textoFiltro = computed(() => {
  if (termBusqueda.value) return `Resultados para: "${termBusqueda.value}"`
  return `Mostrando: ${categoriaActual.value.toUpperCase()}`
})

const actualizarFiltro = () => {
  categoriaActual.value = route.query.filtro || 'todos'
  termBusqueda.value = route.query.busqueda || ''
}

onMounted(() => {
  // Verificamos si es admin para mostrar el carrito
  isAdmin.value = localStorage.getItem('isLogged') === 'true'
  actualizarFiltro()
})

watch(() => route.query.filtro, actualizarFiltro)
watch(() => route.query.busqueda, actualizarFiltro)
</script>

<style scoped>
/* ... Mantén tus estilos de fondo aquí ... */
.fondo-racing {
  background-image:
    linear-gradient(rgba(255, 255, 255, 0.85), rgba(255, 255, 255, 0.85)),
    url('https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?q=80&w=2070&auto=format&fit=crop');
  background-size: cover;
  background-position: center;
  background-attachment: fixed;
  min-height: 100vh;
}
.backdrop-blur {
  background: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(5px);
  border-bottom: 1px solid rgba(0,0,0,0.05);
}
.q-page {
  max-width: 100%;
}
</style>
