<template>
  <q-page class="bg-grey-2">
    <div class="q-pa-md row items-center justify-between">
      <div class="text-h6 text-grey-8 text-weight-bold">CATÁLOGO DE PRODUCTOS</div>
      <q-chip outline color="grey-7" icon="event"> Actualizado hoy </q-chip>
    </div>

    <ProductGrid />
  </q-page>
</template>

<script setup>
// Importamos el componente de la carpeta components
import ProductGrid from 'components/ProductGrid.vue'

// Aquí podrías más adelante recibir los datos de la base de datos de DonWeb
</script>

<style scoped>
/* Un retoque para que el catálogo no esté pegado a los bordes */
.q-page {
  max-width: 1400px;
  margin: 0 auto;
}
</style>
