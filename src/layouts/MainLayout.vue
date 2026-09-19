<template>
  <q-layout view="hHh Lpr lFf">
    <q-header elevated>
      <!-- TOOLBAR PRINCIPAL -->
      <q-toolbar class="bg-grey-10 text-white q-py-sm">
        <!-- Logo y Menú Hamburguesa (Solo en móviles) -->
        <q-btn
          flat
          dense
          round
          icon="menu"
          @click="drawerAbierto = true"
          class="lt-sm q-mr-sm"
          size="lg"
        />

        <BrandLogo @click="irAInicio" class="q-ml-md" />

        <!-- Buscador (Oculto en xs, normal en sm+) -->
        <q-input
          dark
          dense
          standout
          v-model="search"
          placeholder="Busca repuestos..."
          class="gt-xs q-mx-md"
          style="max-width: 400px; width: 100%"
          @keyup.enter="irABusqueda"
          @update:model-value="busquedaEnTiempoReal"
        >
          <template v-slot:append>
            <q-icon name="search" class="cursor-pointer" @click="irABusqueda" />
          </template>
        </q-input>

        <q-space />

        <!-- Info Admin (Oculto en xs) -->
        <div v-if="isAdmin" class="text-subtitle2 q-mr-md text-orange-5 row items-center gt-xs">
          <q-icon name="admin_panel_settings" class="q-mr-xs" />
          {{ adminNombre }}
        </div>

        <!-- Botón Search (Solo en xs) -->
        <q-btn
          flat
          dense
          round
          icon="search"
          @click="mostrarBuscadorMobil = true"
          class="lt-sm q-mr-xs"
        />

        <!-- Botón Login/Logout -->
        <q-btn
          flat
          dense
          no-caps
          color="white"
          :icon="isAdmin ? 'logout' : 'login'"
          :label="$q.screen.gt.xs ? (isAdmin ? 'Salir' : 'Iniciar Sesión') : ''"
          @click="handleAuthAction"
        />
      </q-toolbar>

      <!-- BARRA DE CATEGORÍAS (Oculta en xs, normal en sm+) -->
      <div
        class="bg-grey-9 q-py-sm q-px-md row items-center justify-center q-gutter-x-md no-wrap scroll gt-xs"
      >
        <q-btn flat no-caps color="white" icon="home" label="INICIO" @click="irAInicio" />
        <q-btn
          flat
          no-caps
          color="white"
          icon="sports_motorsports"
          label="CASCOS"
          @click="irACategoria('Cascos')"
        />
        <q-btn
          flat
          no-caps
          color="white"
          icon="opacity"
          label="LUBRICANTES"
          @click="irACategoria('Lubricantes')"
        />
        <q-btn
          flat
          no-caps
          color="white"
          icon="settings"
          label="REPUESTOS"
          @click="irACategoria('Repuestos')"
        />
        <q-btn
          flat
          no-caps
          color="white"
          icon="checkroom"
          label="INDUMENTARIA"
          @click="irACategoria('Indumentaria')"
        />
        <q-btn
          flat
          no-caps
          color="white"
          icon="handyman"
          label="ACCESORIOS"
          @click="irACategoria('Accesorios')"
        />
        <q-separator vertical color="white" v-if="isAdmin" class="q-mx-md" />
        <q-btn
          v-if="isAdmin"
          flat
          no-caps
          color="white"
          icon="storage"
          label="DATOS"
          @click="irADatos"
        />
        <q-btn
          v-if="isAdmin"
          flat
          no-caps
          color="white"
          icon="bar_chart"
          label="ESTADÍSTICAS"
          @click="irAEstadisticas"
        />
      </div>
    </q-header>

    <!-- DRAWER PARA MÓVILES -->
    <q-drawer v-model="drawerAbierto" side="left" bordered class="bg-grey-10">
      <q-list class="text-white">
        <q-item-label header class="text-white text-weight-bold">MENÚ</q-item-label>

        <q-item
          clickable
          v-ripple
          @click="irAInicio; drawerAbierto = false"
        >
          <q-item-section avatar>
            <q-icon name="home" />
          </q-item-section>
          <q-item-section>INICIO</q-item-section>
        </q-item>

        <q-separator class="q-my-md" />

        <q-item-label header class="text-white text-weight-bold">CATEGORÍAS</q-item-label>

        <q-item
          clickable
          v-ripple
          @click="irACategoria('Cascos'); drawerAbierto = false"
        >
          <q-item-section avatar>
            <q-icon name="sports_motorsports" />
          </q-item-section>
          <q-item-section>CASCOS</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          @click="irACategoria('Lubricantes'); drawerAbierto = false"
        >
          <q-item-section avatar>
            <q-icon name="opacity" />
          </q-item-section>
          <q-item-section>LUBRICANTES</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          @click="irACategoria('Repuestos'); drawerAbierto = false"
        >
          <q-item-section avatar>
            <q-icon name="settings" />
          </q-item-section>
          <q-item-section>REPUESTOS</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          @click="irACategoria('Indumentaria'); drawerAbierto = false"
        >
          <q-item-section avatar>
            <q-icon name="checkroom" />
          </q-item-section>
          <q-item-section>INDUMENTARIA</q-item-section>
        </q-item>

        <q-item
          clickable
          v-ripple
          @click="irACategoria('Accesorios'); drawerAbierto = false"
        >
          <q-item-section avatar>
            <q-icon name="handyman" />
          </q-item-section>
          <q-item-section>ACCESORIOS</q-item-section>
        </q-item>

        <q-separator v-if="isAdmin" class="q-my-md" />

        <q-item-label v-if="isAdmin" header class="text-white text-weight-bold"
          >ADMINISTRACIÓN</q-item-label
        >

        <q-item
          v-if="isAdmin"
          clickable
          v-ripple
          @click="irADatos; drawerAbierto = false"
        >
          <q-item-section avatar>
            <q-icon name="storage" />
          </q-item-section>
          <q-item-section>DATOS</q-item-section>
        </q-item>

        <q-item
          v-if="isAdmin"
          clickable
          v-ripple
          @click="irAEstadisticas; drawerAbierto = false"
        >
          <q-item-section avatar>
            <q-icon name="bar_chart" />
          </q-item-section>
          <q-item-section>ESTADÍSTICAS</q-item-section>
        </q-item>
      </q-list>
    </q-drawer>

    <!-- DIÁLOGO BUSCADOR MÓVIL -->
    <q-dialog v-model="mostrarBuscadorMobil" position="top">
      <q-card class="full-width" style="max-height: 120px">
        <q-card-section class="bg-grey-10 q-pa-md">
          <q-input
            v-model="search"
            autofocus
            dark
            dense
            standout
            placeholder="Busca repuestos..."
            class="full-width"
            @keyup.enter="irABusqueda; mostrarBuscadorMobil = false"
          >
            <template v-slot:append>
              <q-icon
                name="search"
                class="cursor-pointer"
                @click="irABusqueda; mostrarBuscadorMobil = false"
              />
            </template>
          </q-input>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-page-container class="bg-grey-2">
      <router-view :key="$route.fullPath" />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import BrandLogo from 'components/BrandLogo.vue'

const $q = useQuasar()
const router = useRouter()
const search = ref('')
const isAdmin = ref(false)
const adminNombre = ref('')
const drawerAbierto = ref(false)
const mostrarBuscadorMobil = ref(false)

// --- NAVEGACIÓN SEGURA ---

const irAInicio = () => {
  search.value = ''
  router.push('/')
}

const irACategoria = (categoria) => {
  search.value = '' // Limpiamos el buscador al cambiar de categoría
  // Usamos path: '/' para asegurar que vaya al IndexPage
  router.push({ path: '/', query: { filtro: categoria } })
}

const irABusqueda = () => {
  if (search.value.trim()) {
    router.push({ path: '/', query: { busqueda: search.value } })
  }
}

// Nueva función: búsqueda en tiempo real
const busquedaEnTiempoReal = (val) => {
  router.replace({ path: '/', query: val.trim() ? { busqueda: val } : {} })
}

const irADatos = () => {
  router.push('/datos')
}

const irAEstadisticas = () => {
  router.push('/estadisticas')
}

// --- LÓGICA DE INACTIVIDAD Y AUTH (Mantenida intacta) ---
const logoutTimer = ref(null)
const TIEMPO_INACTIVIDAD = 30 * 60 * 1000
let debounceTimer = null

const logoutAutomatico = () => {
  if (isAdmin.value) {
    localStorage.removeItem('isLogged')
    localStorage.removeItem('adminNombre')
    isAdmin.value = false
    $q.notify({ color: 'warning', message: 'Sesión cerrada por inactividad', icon: 'timer_off' })
    router.push('/')
  }
}

const reiniciarCronometro = () => {
  if (logoutTimer.value) clearTimeout(logoutTimer.value)
  if (isAdmin.value) logoutTimer.value = setTimeout(logoutAutomatico, TIEMPO_INACTIVIDAD)
}

// Optimizamos los eventos para que no se ejecuten mil veces por segundo
const debouncedReinicio = () => {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(reiniciarCronometro, 300)
}

const checkAuth = () => {
  isAdmin.value = localStorage.getItem('isLogged') === 'true'
  adminNombre.value = localStorage.getItem('adminNombre') || 'Admin'
  if (isAdmin.value) reiniciarCronometro()
}

const handleAuthAction = () => {
  if (isAdmin.value) {
    localStorage.removeItem('isLogged')
    localStorage.removeItem('adminNombre')
    isAdmin.value = false
    if (logoutTimer.value) clearTimeout(logoutTimer.value)
    router.push('/')
  } else {
    router.push('/login')
  }
}

onMounted(() => {
  checkAuth()
  window.addEventListener('mousemove', debouncedReinicio)
  window.addEventListener('keydown', debouncedReinicio)
  window.addEventListener('click', debouncedReinicio)
})

onUnmounted(() => {
  window.removeEventListener('mousemove', debouncedReinicio)
  window.removeEventListener('keydown', debouncedReinicio)
  window.removeEventListener('click', debouncedReinicio)
  if (logoutTimer.value) clearTimeout(logoutTimer.value)
  if (debounceTimer) clearTimeout(debounceTimer)
})
</script>

<style scoped>
.scroll::-webkit-scrollbar {
  display: none;
}
.scroll {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>


