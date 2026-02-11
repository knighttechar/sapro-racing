<template>
  <q-page class="bg-grey-2">
    <div class="q-pa-md row items-center justify-between">
      <div class="column">
        <div class="text-h6 text-grey-8 text-weight-bold">CATÁLOGO DE PRODUCTOS</div>
        <div class="text-subtitle2 text-primary">
          {{ textoFiltro }}
        </div>
      </div>

      <q-chip outline color="grey-7" icon="event"> Actualizado: {{ fechaHoy }} </q-chip>
    </div>

    <ProductGrid :filtro-categoria="categoriaActual" :busqueda="termBusqueda" />
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute } from 'vue-router'
import ProductGrid from 'components/ProductGrid.vue'

const route = useRoute()
const categoriaActual = ref('todos')
const termBusqueda = ref('')

const fechaHoy = computed(() => {
  return new Date().toLocaleDateString('es-AR')
})

const textoFiltro = computed(() => {
  if (termBusqueda.value) {
    return `Resultados para: "${termBusqueda.value}"`
  }
  return `Mostrando: ${categoriaActual.value.toUpperCase()}`
})

const actualizarFiltro = () => {
  categoriaActual.value = route.query.filtro || 'todos'
  termBusqueda.value = route.query.busqueda || ''
}

onMounted(() => {
  actualizarFiltro()
})

watch(
  () => route.query,
  () => {
    actualizarFiltro()
  },
)
</script>

<style scoped>
.q-page {
  max-width: 1400px;
  margin: 0 auto;
}
</style>
