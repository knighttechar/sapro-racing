<template>
  <q-card class="my-card no-shadow border-grey-3" bordered>
    <q-img
      :src="imagen && imagen !== 'default.jpg' ? 'https://saproracing.knighttech.com.ar/imagenes/' + imagen : 'https://placehold.co/300x300?text=Sapro+Racing'"
      ratio="1"
    >
      <div v-if="isAdmin" class="absolute-top-right q-gutter-xs q-pa-sm" style="background: transparent">
        <q-btn round color="orange" icon="edit" size="sm" @click="$emit('editar', {id, nombre, precio, stock, descripcion, codigo})" />
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
      <q-badge :color="stock > 0 ? 'green' : 'red'">Stock: {{ stock }}</q-badge>
    </q-card-section>

    <q-card-actions align="center" class="q-pb-md">
      <q-btn outline color="blue-7" label="VER DETALLES" size="sm" class="full-width" />
    </q-card-actions>
  </q-card>
</template>

<script setup>
defineProps({
  id: [Number, String],
  nombre: String,
  codigo: String,
  precio: [String, Number],
  stock: [String, Number],
  imagen: String,
  descripcion: String,
  isAdmin: Boolean // Nueva prop para controlar la vista
})

defineEmits(['eliminar', 'editar'])
</script>

<style scoped>
.my-card { transition: transform 0.2s; height: 100%; }
.my-card:hover { transform: translateY(-5px); border-color: #1976D2; }
</style>
