<template>
  <q-layout view="hHh Lpr lFf">
    <q-header elevated>
      <q-toolbar class="bg-grey-10 text-white q-py-sm">
        <q-btn flat dense round icon="menu" @click="toggleLeftDrawer" />

        <q-toolbar-title v-if="$q.screen.gt.xs" class="text-weight-bold">
          SAPRO RACING
        </q-toolbar-title>

        <q-space />

        <q-input
          dark
          dense
          standout
          v-model="search"
          placeholder="¿Qué estás buscando? (Ej: Casco, Aceite...)"
          class="q-ml-md"
          style="width: 45%"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>

        <q-space />

        <div v-if="isAdmin" class="text-subtitle2 q-mr-md text-orange-5">
          <q-icon name="admin_panel_settings" /> {{ adminNombre }}
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

      <q-tabs align="left" class="bg-grey-9 text-white" dense>
        <q-route-tab label="INICIO" to="/" />
        <q-route-tab label="CASCOS" />
        <q-route-tab label="LUBRICANTES" />
        <q-route-tab label="ACCESORIOS" />
      </q-tabs>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above bordered :width="280" class="bg-white">
      <SidebarFilters />
    </q-drawer>

    <q-page-container class="bg-grey-2">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SidebarFilters from '../components/SidebarFilters.vue'

const router = useRouter()
const leftDrawerOpen = ref(false)
const search = ref('')
const isAdmin = ref(false)
const adminNombre = ref('')

const toggleLeftDrawer = () => {
  leftDrawerOpen.value = !leftDrawerOpen.value
}

const checkAuth = () => {
  isAdmin.value = localStorage.getItem('isLogged') === 'true'
  adminNombre.value = localStorage.getItem('adminNombre') || 'Admin'
}

const handleAuthAction = () => {
  if (isAdmin.value) {
    localStorage.removeItem('isLogged')
    localStorage.removeItem('adminNombre')
    isAdmin.value = false
    location.reload()
  } else {
    router.push('/login')
  }
}

onMounted(() => {
  checkAuth()
})
</script>
