<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-lg">
      <div class="column">
        <div class="text-h5 text-grey-9 text-weight-bold">VISUALIZADOR DE DATOS</div>
        <div class="text-subtitle2 text-primary">Gestión de tablas de base de datos</div>
      </div>
    </div>

    <!-- SELECTOR DE TABLA Y FILTROS -->
    <div class="row q-gap-md q-mb-lg items-end">
      <q-select
        outlined
        v-model="tablaSeleccionada"
        :options="tablasDisponibles"
        label="Seleccionar tabla"
        class="col-xs-12 col-sm-6 col-md-3"
        @update:model-value="cargarDatos"
        emit-value
        map-options
      />

      <q-input
        outlined
        v-model="registrosPorPagina"
        label="Registros por página"
        type="number"
        class="col-xs-12 col-sm-6 col-md-2"
        min="10"
        max="100"
        @update:model-value="cargarDatos"
      />

      <q-btn
        color="primary"
        label="Recargar"
        icon="refresh"
        @click="cargarDatos"
        class="col-xs-12 col-sm-6 col-md-1"
      />
    </div>

    <!-- INFORMACIÓN DE LA TABLA -->
    <div v-if="cargando" class="q-pa-md text-center">
      <q-spinner size="50px" color="primary" />
    </div>

    <div v-else-if="error" class="q-pa-md">
      <q-banner class="bg-negative text-white rounded-borders">
        <template v-slot:avatar>
          <q-icon name="error" />
        </template>
        {{ error }}
      </q-banner>
    </div>

    <div v-else-if="datos.length > 0" class="q-mt-lg">
      <!-- TABLA CON DATOS -->
      <div class="q-mb-md">
        <q-linear-progress v-if="cargando" indeterminate color="primary" class="q-mb-md" />
      </div>

      <div class="bg-white rounded-borders">
        <!-- TABLA RESPONSIVA -->
        <q-table
          flat
          bordered
          :rows="datos"
          :columns="columnas"
          row-key="id"
          v-model:pagination="paginacion"
          dense
          class="tabla-datos"
        >
          <template v-slot:header="props">
            <q-tr :props="props">
              <q-th v-for="col in props.cols" :key="col.name" :props="props">
                {{ col.label }}
              </q-th>
            </q-tr>
          </template>

          <template v-slot:body="props">
            <q-tr :props="props">
              <q-td v-for="col in props.cols" :key="col.name" :props="props">
                <div class="text-truncate" style="max-width: 200px">
                  {{ formatearDato(props.row[col.name]) }}
                </div>
              </q-td>
            </q-tr>
          </template>
        </q-table>
      </div>

      <!-- INFORMACIÓN DE PAGINACIÓN -->
      <div class="row items-center justify-between q-mt-md">
        <div class="text-subtitle2 text-grey-8">
          Mostrando {{ desde }} a {{ hasta }} de {{ totalRegistros }} registros
        </div>
        <div class="row q-gap-md">
          <q-btn
            flat
            dense
            icon="chevron_left"
            @click="paginaAnterior"
            :disable="paginaActual <= 1"
          />
          <span class="q-px-md text-subtitle2"
            >Página {{ paginaActual }} de {{ totalPaginas }}</span
          >
          <q-btn
            flat
            dense
            icon="chevron_right"
            @click="paginaSiguiente"
            :disable="paginaActual >= totalPaginas"
          />
        </div>
      </div>
    </div>

    <div v-else class="q-pa-md text-center text-grey-7">
      <q-icon name="inbox" size="lg" class="q-mb-md" />
      <div>No hay datos para mostrar</div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// DATOS
const tablaSeleccionada = ref('detalle_ventas')
const tablasDisponibles = ref([
  { label: 'Detalles de Ventas', value: 'detalle_ventas' },
  { label: 'Ventas', value: 'ventas' },
])

const datos = ref([])
const columnas = ref([])
const cargando = ref(false)
const error = ref('')
const registrosPorPagina = ref(50)
const paginaActual = ref(1)
const totalRegistros = ref(0)

// COMPUTADAS
const desde = computed(() => (paginaActual.value - 1) * registrosPorPagina.value + 1)
const hasta = computed(() =>
  Math.min(paginaActual.value * registrosPorPagina.value, totalRegistros.value),
)
const totalPaginas = computed(() => Math.ceil(totalRegistros.value / registrosPorPagina.value) || 1)

const paginacion = computed({
  get: () => ({
    page: paginaActual.value,
    rowsPerPage: registrosPorPagina.value,
    rowsNumber: totalRegistros.value,
  }),
  set: (val) => {
    paginaActual.value = val.page || 1
    registrosPorPagina.value = val.rowsPerPage || 50
  },
})

// MÉTODOS
const cargarDatos = async () => {
  // Verificar autenticación
  const isLogged = localStorage.getItem('isLogged') === 'true'
  if (!isLogged) {
    router.push('/login')
    return
  }

  cargando.value = true
  error.value = ''

  try {
    const offset = (paginaActual.value - 1) * registrosPorPagina.value
    const response = await fetch(
      `api_get_tables.php?tabla=${tablaSeleccionada.value}&limit=${registrosPorPagina.value}&offset=${offset}`,
    )

    if (!response.ok) throw new Error('Error al cargar datos')

    const result = await response.json()

    if (result.success) {
      datos.value = result.data
      totalRegistros.value = result.total

      // Transformar columnas para la tabla
      columnas.value = result.columns.map((col) => ({
        name: col,
        label: col.charAt(0).toUpperCase() + col.slice(1).replace(/_/g, ' '),
        field: col,
        align: 'left',
      }))
    } else {
      error.value = result.error || 'Error al cargar datos'
    }
  } catch (err) {
    console.error('Error:', err)
    error.value = 'Error al conectar con el servidor'
  } finally {
    cargando.value = false
  }
}

const formatearDato = (valor) => {
  if (valor === null || valor === undefined) return '—'
  if (typeof valor === 'object') return JSON.stringify(valor)
  if (typeof valor === 'string' && valor.length > 100) return valor.substring(0, 100) + '...'
  return valor
}

const paginaAnterior = () => {
  if (paginaActual.value > 1) {
    paginaActual.value--
    cargarDatos()
  }
}

const paginaSiguiente = () => {
  if (paginaActual.value < totalPaginas.value) {
    paginaActual.value++
    cargarDatos()
  }
}

// INICIALIZACIÓN
onMounted(() => {
  const isLogged = localStorage.getItem('isLogged') === 'true'
  if (!isLogged) {
    router.push('/login')
  } else {
    cargarDatos()
  }
})
</script>

<style scoped lang="scss">
.tabla-datos {
  :deep(.q-table__card) {
    box-shadow: none;
  }

  :deep(.q-table tr) {
    &:hover {
      background-color: #f5f5f5;
    }
  }
}

.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
