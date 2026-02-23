<template>
  <div class="row q-col-gutter-md q-pa-md">
    <div v-if="isAdmin" class="col-12 col-sm-4 col-md-3">
      <q-card
        class="my-card no-shadow border-dashed flex flex-center cursor-pointer"
        style="height: 100%; min-height: 400px; border: 2px dashed #1976d2; background: #f8f9fa"
        @click="abrirModalNuevo"
      >
        <q-card-section class="text-center">
          <q-icon name="add_circle" size="64px" color="primary" />
          <div class="text-subtitle1 text-primary text-weight-bold q-mt-sm">AGREGAR PRODUCTO</div>
        </q-card-section>
      </q-card>
    </div>

    <div v-for="pro in productosFiltrados" :key="pro.id" class="col-12 col-sm-4 col-md-3">
      <ProductCard
        v-bind="pro"
        :is-admin="isAdmin"
        @eliminar="confirmarEliminar"
        @editar="prepararEdicion"
      />
    </div>

    <div v-if="productosFiltrados.length === 0" class="col-12 text-center q-pa-xl">
      <q-icon name="search_off" size="64px" color="grey-5" />
      <div class="text-h6 text-grey-6 q-mt-md">
        {{ errorApi ? 'Error de conexión con la base de datos' : 'No se encontraron productos' }}
      </div>
      <div v-if="errorApi" class="text-caption text-red q-mt-sm">
        Revisa que config.php esté bien configurado.
      </div>
    </div>

    <q-dialog v-model="modalProducto" persistent>
      <q-card style="min-width: 450px; border-radius: 15px">
        <q-card-section :class="esEdicion ? 'bg-orange text-white' : 'bg-grey-10 text-white'">
          <div class="text-h6">{{ esEdicion ? 'EDITAR PRODUCTO' : 'NUEVO PRODUCTO' }}</div>
        </q-card-section>

        <q-card-section class="q-pa-md">
          <q-form @submit="guardarProducto" class="q-gutter-md">
            <q-input
              v-model="formPro.nombre"
              label="Nombre"
              filled
              :rules="[(val) => !!val || 'Requerido']"
            />
            <div class="row q-col-gutter-sm">
              <q-input
                v-model="formPro.codigo"
                label="Código"
                filled
                class="col-6"
                :rules="[(val) => !!val || 'Requerido']"
              />
              <q-input
                v-model="formPro.precio"
                label="Precio"
                type="number"
                filled
                class="col-6"
                :rules="[(val) => !!val || 'Requerido']"
              />
            </div>
            <q-input
              v-model="formPro.stock"
              label="Stock"
              type="number"
              filled
              :rules="[(val) => (val !== '' && val !== null) || 'Requerido']"
            />
            <div class="row q-col-gutter-sm">
              <div class="col-6">
                <q-select
                  filled
                  v-model="formPro.categoria"
                  :options="['Cascos', 'Lubricantes', 'Repuestos', 'Indumentaria', 'Accesorios']"
                  label="Categoría"
                  :rules="[(val) => !!val || 'Requerido']"
                />
              </div>
              <div class="col-6">
                <q-select
                  filled
                  v-model="formPro.marca"
                  :options="['LS2', 'MT Helmets', 'Motul', 'Ipone', 'Honda', 'Yamaha', 'Generic']"
                  label="Marca"
                  :rules="[(val) => !!val || 'Requerido']"
                />
              </div>
            </div>
            <q-input
              v-model="formPro.descripcion"
              label="Descripción"
              type="textarea"
              filled
              rows="3"
            />
            <q-file v-model="foto" label="Imagen" filled accept="image/*" />
            <q-card-actions align="right">
              <q-btn flat label="CANCELAR" v-close-popup />
              <q-btn
                :label="esEdicion ? 'ACTUALIZAR' : 'GUARDAR'"
                type="submit"
                color="primary"
                :loading="loading"
              />
            </q-card-actions>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
/* eslint-disable */
import { ref, onMounted, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import ProductCard from './ProductCard.vue'
import { API_ENDPOINTS } from 'src/config/api'

const $q = useQuasar()

const props = defineProps({
  filtroCategoria: { type: String, default: 'todos' },
  busqueda: { type: String, default: '' },
})

const productos = ref([])
const isAdmin = ref(false)
const loading = ref(false)
const modalProducto = ref(false)
const esEdicion = ref(false)
const errorApi = ref(false)
const foto = ref(null)

const formPro = ref({
  id: null,
  nombre: '',
  codigo: '',
  precio: 0,
  stock: 0,
  descripcion: '',
  categoria: '',
  marca: '',
})

const productosFiltrados = computed(() => {
  if (!Array.isArray(productos.value)) return []
  let res = productos.value
  if (props.filtroCategoria && props.filtroCategoria !== 'todos') {
    res = res.filter(
      (p) => p.categoria && p.categoria.toLowerCase() === props.filtroCategoria.toLowerCase(),
    )
  }
  if (props.busqueda && props.busqueda.trim() !== '') {
    const term = props.busqueda.toLowerCase()
    res = res.filter(
      (p) =>
        (p.nombre && p.nombre.toLowerCase().includes(term)) ||
        (p.codigo && p.codigo.toLowerCase().includes(term)) ||
        (p.marca && p.marca.toLowerCase().includes(term)),
    )
  }
  return res
})

const cargarProductos = async () => {
  try {
    const res = await axios.get(API_ENDPOINTS.PRODUCTOS)
    if (Array.isArray(res.data)) {
      productos.value = res.data
      errorApi.value = false
    } else {
      productos.value = []
      errorApi.value = true
    }
  } catch (e) {
    console.error('Error cargando productos:', e)
    errorApi.value = true
  }
}

const confirmarEliminar = (id) => {
  $q.dialog({
    title: 'CONFIRMAR',
    message: '¿Eliminar producto?',
    cancel: true,
    ok: { color: 'negative', label: 'ELIMINAR' },
  }).onOk(async () => {
    try {
      const response = await axios.post(API_ENDPOINTS.ELIMINAR_PRODUCTO, {
        id: id,
        isAdmin: 'true',
      })

      if (response.data.success) {
        $q.notify({ color: 'negative', message: 'Eliminado' })
        cargarProductos()
      } else {
        $q.notify({ color: 'warning', message: response.data.mensaje || 'Error al eliminar' })
      }
    } catch (e) {
      if (e.response && e.response.status === 403) {
        $q.notify({ color: 'negative', message: 'No tienes permisos para eliminar' })
      } else {
        $q.notify({ color: 'negative', message: 'Error de red' })
      }
    }
  })
}

const abrirModalNuevo = () => {
  esEdicion.value = false
  formPro.value = {
    id: null,
    nombre: '',
    codigo: '',
    precio: 0,
    stock: 0,
    descripcion: '',
    categoria: '',
    marca: '',
  }
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
      // Si hay imagen nueva, enviar con FormData
      if (foto.value) {
        const fd = new FormData()
        Object.keys(formPro.value).forEach((key) => {
          if (formPro.value[key] !== null) fd.append(key, formPro.value[key])
        })
        fd.append('imagen', foto.value)
        fd.append('isAdmin', 'true')
        await axios.post(API_ENDPOINTS.EDITAR_PRODUCTO, fd)
      } else {
        // Sin imagen nueva, enviar JSON normal
        await axios.post(API_ENDPOINTS.EDITAR_PRODUCTO, {
          ...formPro.value,
          isAdmin: 'true',
        })
      }
    } else {
      const fd = new FormData()
      Object.keys(formPro.value).forEach((key) => {
        if (formPro.value[key] !== null) fd.append(key, formPro.value[key])
      })
      if (foto.value) fd.append('imagen', foto.value)
      fd.append('isAdmin', 'true')
      await axios.post(API_ENDPOINTS.AGREGAR_PRODUCTO, fd)
    }
    foto.value = null
    modalProducto.value = false
    cargarProductos()
    $q.notify({ color: 'positive', message: 'Guardado' })
  } catch (e) {
    console.error('Error al guardar:', e)
    $q.notify({ color: 'negative', message: 'Error al guardar' })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  isAdmin.value = localStorage.getItem('isLogged') === 'true'
  cargarProductos()
})
</script>
