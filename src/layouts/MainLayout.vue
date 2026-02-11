<template>
  <q-layout view="hHh Lpr lFf">
    <q-header elevated>
      <q-toolbar class="bg-grey-10 text-white q-py-sm">
        <q-toolbar-title class="text-weight-bold q-ml-md cursor-pointer" @click="irAInicio">
          SAPRO RACING
        </q-toolbar-title>

        <q-space />

        <q-input
          dark
          dense
          standout
          v-model="search"
          placeholder="Busca repuestos, marcas o accesorios..."
          class="q-ml-md"
          style="width: 50%"
          @keyup.enter="irABusqueda"
        >
          <template v-slot:append>
            <q-icon name="search" class="cursor-pointer" @click="irABusqueda" />
          </template>
        </q-input>

        <q-space />

        <div v-if="isAdmin" class="text-subtitle2 q-mr-md text-orange-5 row items-center">
          <q-icon name="admin_panel_settings" class="q-mr-xs" />
          {{ adminNombre }}
        </div>

        <q-btn
          flat
          dense
          no-caps
          color="white"
          :icon="isAdmin ? 'logout' : 'login'"
          :label="isAdmin ? 'Salir' : 'Iniciar Sesión'"
          @click="handleAuthAction"
        />
      </q-toolbar>

      <div class="bg-grey-9 q-py-sm q-px-md row items-center justify-center q-gutter-x-md no-wrap scroll">

        <q-btn flat no-caps color="white" icon="home" label="INICIO" @click="irAInicio" />

        <q-btn flat no-caps color="white" icon="sports_motorsports" label="CASCOS" @click="irACategoria('Cascos')" />

        <q-btn flat no-caps color="white" icon="opacity" label="LUBRICANTES" @click="irACategoria('Lubricantes')" />

        <q-btn flat no-caps color="white" icon="settings" label="REPUESTOS" @click="irACategoria('Repuestos')" />

        <q-btn flat no-caps color="white" icon="checkroom" label="INDUMENTARIA" @click="irACategoria('Indumentaria')" />

        <q-btn flat no-caps color="white" icon="handyman" label="ACCESORIOS" @click="irACategoria('Accesorios')" />

      </div>
    </q-header>

    <q-page-container class="bg-grey-2">
      <router-view :key="$route.fullPath" />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const router = useRouter()
const search = ref('')
const isAdmin = ref(false)
const adminNombre = ref('')

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
