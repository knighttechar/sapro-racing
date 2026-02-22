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
          color="white"
          text-color="orange-9"
          icon="edit"
          size="sm"
          @click="$emit('editar', { id, nombre, precio, stock, descripcion, codigo, categoria, marca })"
        />
        <q-btn round color="negative" icon="delete" size="sm" @click="$emit('eliminar', id)" />
      </div>

      <div v-if="isAdmin" class="absolute-bottom-right q-pa-sm">
        <q-btn
          round
          color="primary"
          icon="add_shopping_cart"
          @click="agregarItem"
          class="shadow-5"
        />
      </div>

      <template v-slot:loading>
        <q-spinner-ios color="white" />
      </template>
    </q-img>

    <q-card-section class="q-pa-sm text-center">
      <div class="text-caption text-grey-6">CÓDIGO: {{ codigo }}</div>
      <div class="text-subtitle2 text-weight-bold ellipsis">{{ nombre }}</div>

      <div class="text-caption text-grey-7 ellipsis-2-lines" style="min-height: 32px">
        {{ descripcion || 'Sin descripción' }}
      </div>

      <div class="text-h6 text-blue-7 q-mt-xs">${{ precio }}</div>

      <q-badge :color="colorStock" class="q-pa-xs"> STOCK: {{ stock }} </q-badge>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { computed } from 'vue'
import { useCarrito } from 'src/composables/useCarrito' // Importamos el composable

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

// eslint-disable-next-line no-unused-vars
const emit = defineEmits(['eliminar', 'editar'])

// Usamos el composable
const { agregarAlCarrito } = useCarrito()

const agregarItem = () => {
  // Construimos el objeto producto basado en las props
  const producto = {
    id: props.id,
    nombre: props.nombre,
    codigo: props.codigo,
    precio: props.precio,
    imagen: props.imagen
  }
  agregarAlCarrito(producto)
}

const colorStock = computed(() => {
  const s = Number(props.stock)
  if (s <= 0) return 'grey-10'
  if (s <= 2) return 'red'
  if (s <= 5) return 'orange'
  return 'green'
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
