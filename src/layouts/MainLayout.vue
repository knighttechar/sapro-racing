<template>
  <q-layout view="hHh Lpr lFf">
    <q-header elevated>
      <Navbar />
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
import { ref } from 'vue'
// Importamos los componentes que creaste en la carpeta components
import Navbar from '../components/Navbar.vue'
import SidebarFilters from '../components/SidebarFilters.vue'

const leftDrawerOpen = ref(false)
</script>

<style scoped>
/* Estilo para que el fondo se vea limpio como en la foto */
.q-page-container {
  padding-top: 10px;
}
</style>
