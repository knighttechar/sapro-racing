<template>
  <q-card class="my-card no-shadow border-grey-3" bordered>
    <q-img src="https://placehold.co/300x300?text=Producto+Sapro" ratio="1" />

    <q-card-section class="q-pa-sm text-center">
      <div class="text-caption text-grey-6 text-uppercase">SKU: {{ sku }}</div>
      <div class="text-subtitle2 text-weight-bold q-mt-xs">{{ nombre }}</div>
    </q-card-section>

    <q-card-actions align="center" class="q-pb-md">
      <q-btn outline color="blue-7" label="VER DETALLES" size="sm" class="full-width" />
    </q-card-actions>
  </q-card>
</template>

<script setup>
defineProps({
  nombre: String,
  sku: String
})
</script>

<style scoped>
.my-card { width: 100%; max-width: 250px; transition: transform 0.2s; }
.my-card:hover { transform: translateY(-5px); border-color: #1976D2; }
</style>