<template>
  <q-card class="my-card no-shadow border-grey-3" bordered>
    <q-img
      :src="
        imagen && imagen !== 'default.jpg'
          ? `${API_URL}/imagenes/${imagen}`
          : 'https://placehold.co/300x300?text=Sapro+Racing'
      "
      ratio="1"
    >
      <div
        v-if="isAdmin"
        class="absolute-top-right q-gutter-xs q-pa-sm"
        style="background: transparent"
      >
        <q-btn
          round
          color="orange"
          icon="edit"
          size="sm"
          @click="
            $emit('editar', { id, nombre, precio, stock, descripcion, codigo, categoria, marca })
          "
        />
        <q-btn round color="negative" icon="delete" size="sm" @click="$emit('eliminar', id)" />
      </div>

      <template v-slot:loading>
        <q-spinner-ios color="white" />
      </template>
    </q-img>

    <q-card-section class="q-pa-sm text-center">
      <div class="text-caption text-grey-6">CÓDIGO: {{ codigo }}</div>
      <div class="text-subtitle2 text-weight-bold">{{ nombre }}</div>

      <div class="text-caption text-grey-7 ellipsis-2-lines" style="min-height: 32px">
        {{ descripcion || 'Sin descripción' }}
      </div>

      <div class="text-h6 text-blue-7 q-mt-xs">${{ precio }}</div>

      <q-badge :color="colorStock" class="q-pa-xs"> STOCK: {{ stock }} </q-badge>
    </q-card-section>

    <q-card-actions align="center" class="q-pb-md">
      <q-btn outline color="blue-7" label="VER DETALLES" size="sm" class="full-width" />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import { computed } from 'vue'

const API_URL = 'https://saproracing.knighttech.com.ar'

const props = defineProps({
  id: [Number, String],
  nombre: String,
  codigo: String,
  precio: [String, Number],
  stock: [String, Number],
  imagen: String,
  descripcion: String,
  categoria: String,
  marca: String,
  isAdmin: Boolean,
})

defineEmits(['eliminar', 'editar'])

// Lógica inteligente para el color del stock
const colorStock = computed(() => {
  const s = Number(props.stock)
  if (s <= 0) return 'grey-10' // Sin stock (Negro/Gris oscuro)
  if (s <= 5) return 'red' // Crítico (Rojo)
  if (s <= 12) return 'orange' // Advertencia (Naranja/Amarillo fuerte)
  return 'green' // Saludable (Verde)
})
</script>

<style scoped>
.my-card {
  transition: transform 0.2s;
  height: 100%;
}
.my-card:hover {
  transform: translateY(-5px);
  border-color: #1976d2;
}
</style>
