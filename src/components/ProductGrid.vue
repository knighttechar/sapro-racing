<template>
  <div class="row q-col-gutter-md q-pa-md">
    <div v-for="n in 8" :key="n" class="col-12 col-sm-4 col-md-3">
      <ProductCard 
        :nombre="'Producto de Prueba ' + n" 
        :sku="'SAP-00' + n" 
      />
    </div>
  </div>
</template>

<script setup>
import ProductCard from './ProductCard.vue'
</script>