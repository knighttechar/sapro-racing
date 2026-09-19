<template>
  <q-card class="my-card no-shadow border-grey-3" bordered>
    <!-- IMAGEN CON BOTONES ADMIN -->
    <q-img :src="getImageUrl(imagen)" ratio="1">
      <!-- Botones Editar/Eliminar en esquina superior derecha -->
      <div
        v-if="isAdmin"
        class="absolute-top-right q-gutter-xs q-pa-sm"
        style="background: rgba(0, 0, 0, 0.3); border-radius: 4px"
      >
        <q-btn
          round
          color="white"
          text-color="orange-9"
          icon="edit"
          size="sm"
          @click="
            $emit('editar', { id, nombre, precio, stock, descripcion, codigo, categoria, marca })
          "
          class="shadow-2"
        />
        <q-btn
          round
          color="negative"
          icon="delete"
          size="sm"
          @click="$emit('eliminar', id)"
          class="shadow-2"
        />
      </div>

      <!-- Botón Carrito en esquina inferior derecha -->
      <div v-if="isAdmin" class="absolute-bottom-right q-pa-sm">
        <q-btn
          round
          color="primary"
          icon="add_shopping_cart"
          @click="agregarItem"
          class="shadow-5"
        />
      </div>

      <!-- Loader -->
      <template v-slot:loading>
        <q-spinner-ios color="white" />
      </template>
    </q-img>

    <!-- CONTENIDO DE LA TARJETA -->
    <q-card-section class="q-pa-xs q-pa-sm q-md-pa-md text-center">
      <!-- Código -->
      <div class="text-caption text-grey-6 ellipsis">CÓDIGO: {{ codigo }}</div>

      <!-- Marca -->
      <div class="text-caption text-primary text-weight-bold ellipsis">
        {{ marca }}
      </div>

      <!-- Nombre (Responsive) -->
      <div class="text-subtitle2 text-subtitle1-sm text-weight-bold ellipsis q-my-xs">
        {{ nombre }}
      </div>

      <!-- Descripción (máximo 2 líneas) -->
      <div class="text-caption text-grey-7 ellipsis-2-lines" style="min-height: 32px">
        {{ descripcion || 'Sin descripción' }}
      </div>

      <!-- Precio (Grande y destacado) -->
      <div class="text-h6 text-h5-sm text-blue-7 q-my-sm text-weight-bold">
        ${{ parseFloat(precio).toFixed(2) }}
      </div>

      <!-- Stock Badge -->
      <q-badge :color="colorStock" class="q-pa-xs text-caption"> 📦 STOCK: {{ stock }} </q-badge>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { computed } from 'vue'
import { useCarrito } from 'src/composables/useCarrito'
import { getImageUrl } from 'src/config/api'

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
    imagen: props.imagen,
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
