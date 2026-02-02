<template>
  <div class="row q-col-gutter-md q-pa-md">
    <div v-if="isAdmin" class="col-12 col-sm-4 col-md-3">
      <q-card
        class="my-card no-shadow border-dashed flex flex-center cursor-pointer"
        style="height: 100%; min-height: 400px; border: 2px dashed #1976D2; background: #f8f9fa"
        @click="abrirModalNuevo"
      >
        <q-card-section class="text-center">
          <q-icon name="add_circle" size="64px" color="primary" />
          <div class="text-subtitle1 text-primary text-weight-bold q-mt-sm">AGREGAR PRODUCTO</div>
        </q-card-section>
      </q-card>
    </div>

    <div v-for="pro in productos" :key="pro.id" class="col-12 col-sm-4 col-md-3">
      <ProductCard
        v-bind="pro"
        :is-admin="isAdmin"
        @eliminar="confirmarEliminar"
        @editar="prepararEdicion"
      />
    </div>

    <q-dialog v-model="modalProducto" persistent>
      <q-card style="min-width: 400px; border-radius: 15px">
        <q-card-section :class="esEdicion ? 'bg-orange text-white' : 'bg-grey-10 text-white'" class="row items-center">
          <div class="text-h6">{{ esEdicion ? 'Editar Producto' : 'Cargar Nuevo Producto' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="q-pt-md">
          <q-form @submit="guardarProducto" class="q-gutter-md">
            <q-input v-model="formPro.nombre" label="Nombre" filled :rules="[val => !!val || 'Requerido']" />
            <q-input v-model="formPro.codigo" label="Código" filled :rules="[val => !!val || 'Requerido']" />
            <q-input v-model="formPro.descripcion" label="Descripción" filled type="textarea" rows="2" />

            <div class="row q-col-gutter-sm">
              <q-input v-model.number="formPro.precio" label="Precio" filled type="number" class="col-6" prefix="$" />
              <q-input v-model.number="formPro.stock" label="Stock" filled type="number" class="col-6" />
            </div>

            <q-file v-if="!esEdicion" v-model="foto" label="Seleccionar Imagen" filled accept=".jpg, image/*">
              <template v-slot:prepend><q-icon name="image" /></template>
            </q-file>

            <div class="row q-mt-lg">
              <q-btn label="Cancelar" flat color="grey" v-close-popup class="col" />
              <q-btn
                :label="esEdicion ? 'Actualizar' : 'Guardar'"
                type="submit"
                :color="esEdicion ? 'orange' : 'primary'"
                class="col q-ml-sm"
                :loading="loading"
              />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useQuasar } from 'quasar'
import ProductCard from './ProductCard.vue'

const $q = useQuasar()
const productos = ref([])
const isAdmin = ref(false)
const modalProducto = ref(false)
const esEdicion = ref(false)
const loading = ref(false)
const foto = ref(null)

// Modelo para el formulario
const formPro = ref({ id: null, nombre: '', codigo: '', precio: 0, stock: 0, descripcion: '' })

const cargarProductos = async () => {
  try {
    const response = await axios.get('https://saproracing.knighttech.com.ar/api.php')
    productos.value = response.data
  } catch (error) {
    console.error('Error al cargar:', error)
  }
}

const abrirModalNuevo = () => {
  esEdicion.value = false
  formPro.value = { id: null, nombre: '', codigo: '', precio: 0, stock: 0, descripcion: '' }
  foto.value = null
  modalProducto.value = true
}

const prepararEdicion = (pro) => {
  esEdicion.value = true
  formPro.value = { ...pro }
  modalProducto.value = true
}

const guardarProducto = async () => {
  loading.value = true
  try {
    if (esEdicion.value) {
      // EDITAR PRODUCTO
      await axios.post('https://saproracing.knighttech.com.ar/api_acciones.php?accion=editar', formPro.value)
      $q.notify({ color: 'orange', message: '¡Producto actualizado!', icon: 'edit' })
    } else {
      // NUEVO PRODUCTO
      const fd = new FormData()
      Object.keys(formPro.value).forEach(key => fd.append(key, formPro.value[key]))
      if (foto.value) fd.append('imagen', foto.value)

      await axios.post('https://saproracing.knighttech.com.ar/agregar_producto.php', fd)
      $q.notify({ color: 'positive', message: '¡Producto guardado!', icon: 'check' })
    }
    modalProducto.value = false
    cargarProductos()
  } catch (err) {
    console.error('Error en operación:', err)
    $q.notify({ color: 'negative', message: 'Error de conexión' })
  } finally {
    loading.value = false
  }
}

const confirmarEliminar = (id) => {
  $q.dialog({
    title: '¿Eliminar producto?',
    message: 'Esta acción borrará el registro y la imagen del servidor.',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await axios.post('https://saproracing.knighttech.com.ar/api_acciones.php?accion=eliminar', { id })
      $q.notify({ color: 'negative', icon: 'delete', message: 'Eliminado con éxito' })
      cargarProductos()
    } catch (err) {
      console.error('Error al eliminar:', err)
    }
  })
}

onMounted(() => {
  isAdmin.value = localStorage.getItem('isLogged') === 'true'
  cargarProductos()
})
</script>

<style scoped>
.my-card {
  width: 100%;
  max-width: 250px;
  margin: 0 auto;
}
.border-dashed {
  border-radius: 8px;
}
</style>
