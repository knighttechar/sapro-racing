<template>
  <q-page class="q-pa-md">
    <div class="row items-center justify-between q-mb-lg">
      <div class="column">
        <div class="text-h5 text-grey-9 text-weight-bold">ESTADÍSTICAS DE VENTAS</div>
        <div class="text-subtitle2 text-primary">Análisis de ventas por período</div>
      </div>
    </div>

    <!-- TABS DE PERÍODO -->
    <q-tabs
      v-model="tabActiva"
      dense
      class="text-grey-8 q-mb-lg"
      active-color="primary"
      indicator-color="primary"
      align="left"
    >
      <q-tab name="dia" label="Por Día" icon="today" @click="cargarEstadisticas('dia')" />
      <q-tab
        name="semana"
        label="Por Semana"
        icon="calendar_view_week"
        @click="cargarEstadisticas('semana')"
      />
      <q-tab
        name="mes"
        label="Por Mes"
        icon="calendar_view_month"
        @click="cargarEstadisticas('mes')"
      />
      <q-tab name="año" label="Por Año" icon="date_range" @click="cargarEstadisticas('año')" />
    </q-tabs>

    <!-- CONTENIDO DE LAS TABS -->
    <q-tab-panels v-model="tabActiva" animated>
      <!-- TAB DÍA -->
      <q-tab-panel name="dia">
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

        <div v-else>
          <!-- RESUMEN -->
          <div class="row q-gap-md q-mb-lg">
            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-blue-1">
                <q-card-section>
                  <div class="text-h6 text-blue-9">{{ resumen.total_ventas }}</div>
                  <div class="text-subtitle2 text-blue-7">Total de Ventas</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-green-1">
                <q-card-section>
                  <div class="text-h6 text-green-9">
                    \${{ formatearMoneda(resumen.monto_total) }}
                  </div>
                  <div class="text-subtitle2 text-green-7">Monto Total</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-orange-1">
                <q-card-section>
                  <div class="text-h6 text-orange-9">
                    \${{ formatearMoneda(resumen.promedio_venta) }}
                  </div>
                  <div class="text-subtitle2 text-orange-7">Promedio por Venta</div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <!-- TABLA -->
          <div class="bg-white rounded-borders">
            <q-table
              flat
              bordered
              :rows="datos"
              :columns="columnasTabla"
              row-key="fecha"
              dense
              v-if="tabActiva === 'dia'"
            >
              <template v-slot:body-cell-fecha="props">
                <q-td :props="props">
                  {{ formatearFecha(props.row.fecha) }}
                </q-td>
              </template>
              <template v-slot:body-cell-monto_total="props">
                <q-td :props="props"> \${{ formatearMoneda(props.row.monto_total) }} </q-td>
              </template>
            </q-table>
          </div>
        </div>
      </q-tab-panel>

      <!-- TAB SEMANA -->
      <q-tab-panel name="semana">
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

        <div v-else>
          <!-- RESUMEN -->
          <div class="row q-gap-md q-mb-lg">
            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-blue-1">
                <q-card-section>
                  <div class="text-h6 text-blue-9">{{ resumen.total_ventas }}</div>
                  <div class="text-subtitle2 text-blue-7">Total de Ventas</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-green-1">
                <q-card-section>
                  <div class="text-h6 text-green-9">
                    \${{ formatearMoneda(resumen.monto_total) }}
                  </div>
                  <div class="text-subtitle2 text-green-7">Monto Total</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-orange-1">
                <q-card-section>
                  <div class="text-h6 text-orange-9">
                    \${{ formatearMoneda(resumen.promedio_venta) }}
                  </div>
                  <div class="text-subtitle2 text-orange-7">Promedio por Venta</div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <!-- TABLA -->
          <div class="bg-white rounded-borders">
            <q-table
              flat
              bordered
              :rows="datos"
              :columns="columnasTabla"
              row-key="semana"
              dense
              v-if="tabActiva === 'semana'"
            >
              <template v-slot:body-cell-etiqueta="props">
                <q-td :props="props"> {{ props.row.etiqueta }} ({{ props.row.año }}) </q-td>
              </template>
              <template v-slot:body-cell-monto_total="props">
                <q-td :props="props"> \${{ formatearMoneda(props.row.monto_total) }} </q-td>
              </template>
            </q-table>
          </div>
        </div>
      </q-tab-panel>

      <!-- TAB MES -->
      <q-tab-panel name="mes">
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

        <div v-else>
          <!-- RESUMEN -->
          <div class="row q-gap-md q-mb-lg">
            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-blue-1">
                <q-card-section>
                  <div class="text-h6 text-blue-9">{{ resumen.total_ventas }}</div>
                  <div class="text-subtitle2 text-blue-7">Total de Ventas</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-green-1">
                <q-card-section>
                  <div class="text-h6 text-green-9">
                    \${{ formatearMoneda(resumen.monto_total) }}
                  </div>
                  <div class="text-subtitle2 text-green-7">Monto Total</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-orange-1">
                <q-card-section>
                  <div class="text-h6 text-orange-9">
                    \${{ formatearMoneda(resumen.promedio_venta) }}
                  </div>
                  <div class="text-subtitle2 text-orange-7">Promedio por Venta</div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <!-- TABLA -->
          <div class="bg-white rounded-borders">
            <q-table
              flat
              bordered
              :rows="datos"
              :columns="columnasTabla"
              row-key="mes"
              dense
              v-if="tabActiva === 'mes'"
            >
              <template v-slot:body-cell-etiqueta="props">
                <q-td :props="props">
                  {{ props.row.etiqueta }}
                </q-td>
              </template>
              <template v-slot:body-cell-monto_total="props">
                <q-td :props="props"> \${{ formatearMoneda(props.row.monto_total) }} </q-td>
              </template>
            </q-table>
          </div>
        </div>
      </q-tab-panel>

      <!-- TAB AÑO -->
      <q-tab-panel name="año">
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

        <div v-else>
          <!-- RESUMEN -->
          <div class="row q-gap-md q-mb-lg">
            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-blue-1">
                <q-card-section>
                  <div class="text-h6 text-blue-9">{{ resumen.total_ventas }}</div>
                  <div class="text-subtitle2 text-blue-7">Total de Ventas</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-green-1">
                <q-card-section>
                  <div class="text-h6 text-green-9">
                    \${{ formatearMoneda(resumen.monto_total) }}
                  </div>
                  <div class="text-subtitle2 text-green-7">Monto Total</div>
                </q-card-section>
              </q-card>
            </div>

            <div class="col-xs-12 col-sm-6 col-md-3">
              <q-card class="bg-orange-1">
                <q-card-section>
                  <div class="text-h6 text-orange-9">
                    \${{ formatearMoneda(resumen.promedio_venta) }}
                  </div>
                  <div class="text-subtitle2 text-orange-7">Promedio por Venta</div>
                </q-card-section>
              </q-card>
            </div>
          </div>

          <!-- TABLA -->
          <div class="bg-white rounded-borders">
            <q-table
              flat
              bordered
              :rows="datos"
              :columns="columnasTabla"
              row-key="año"
              dense
              v-if="tabActiva === 'año'"
            >
              <template v-slot:body-cell-monto_total="props">
                <q-td :props="props"> \${{ formatearMoneda(props.row.monto_total) }} </q-td>
              </template>
            </q-table>
          </div>
        </div>
      </q-tab-panel>
    </q-tab-panels>
  </q-page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// DATOS
const tabActiva = ref('dia')
const datos = ref([])
const cargando = ref(false)
const error = ref('')
const resumen = ref({
  total_ventas: 0,
  monto_total: 0,
  promedio_venta: 0,
})

// COLUMNAS PARA LAS TABLAS
const columnasTabla = computed(() => {
  if (tabActiva.value === 'dia') {
    return [
      {
        name: 'fecha',
        label: 'Fecha',
        field: 'fecha',
        align: 'left',
      },
      {
        name: 'total_ventas',
        label: 'Total de Ventas',
        field: 'total_ventas',
        align: 'center',
      },
      {
        name: 'monto_total',
        label: 'Monto Total',
        field: 'monto_total',
        align: 'right',
      },
    ]
  } else if (tabActiva.value === 'semana') {
    return [
      {
        name: 'etiqueta',
        label: 'Semana',
        field: 'etiqueta',
        align: 'left',
      },
      {
        name: 'total_ventas',
        label: 'Total de Ventas',
        field: 'total_ventas',
        align: 'center',
      },
      {
        name: 'monto_total',
        label: 'Monto Total',
        field: 'monto_total',
        align: 'right',
      },
    ]
  } else if (tabActiva.value === 'mes') {
    return [
      {
        name: 'etiqueta',
        label: 'Mes',
        field: 'etiqueta',
        align: 'left',
      },
      {
        name: 'total_ventas',
        label: 'Total de Ventas',
        field: 'total_ventas',
        align: 'center',
      },
      {
        name: 'monto_total',
        label: 'Monto Total',
        field: 'monto_total',
        align: 'right',
      },
    ]
  } else {
    return [
      {
        name: 'año',
        label: 'Año',
        field: 'año',
        align: 'left',
      },
      {
        name: 'total_ventas',
        label: 'Total de Ventas',
        field: 'total_ventas',
        align: 'center',
      },
      {
        name: 'monto_total',
        label: 'Monto Total',
        field: 'monto_total',
        align: 'right',
      },
    ]
  }
})

// MÉTODOS
const cargarEstadisticas = async (tipo) => {
  // Verificar autenticación
  const isLogged = localStorage.getItem('isLogged') === 'true'
  if (!isLogged) {
    router.push('/login')
    return
  }

  cargando.value = true
  error.value = ''

  try {
    const response = await fetch(`api_estadisticas.php?tipo=${tipo}`)

    if (!response.ok) throw new Error('Error al cargar estadísticas')

    const result = await response.json()

    if (result.success) {
      datos.value = result.datos
      resumen.value = result.resumen
    } else {
      error.value = result.error || 'Error al cargar estadísticas'
    }
  } catch (err) {
    console.error('Error:', err)
    error.value = 'Error al conectar con el servidor'
  } finally {
    cargando.value = false
  }
}

const formatearMoneda = (valor) => {
  if (!valor) return '0.00'
  return parseFloat(valor).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

const formatearFecha = (fecha) => {
  return new Date(fecha).toLocaleDateString('es-AR')
}

// INICIALIZACIÓN
onMounted(() => {
  const isLogged = localStorage.getItem('isLogged') === 'true'
  if (!isLogged) {
    router.push('/login')
  } else {
    cargarEstadisticas('dia')
  }
})
</script>

<style scoped lang="scss"></style>
