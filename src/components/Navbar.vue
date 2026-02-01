<template>
  <q-toolbar class="bg-grey-10 text-white q-py-sm">
    <q-avatar size="lg" class="q-ml-md">
      <img src="https://cdn.quasar.dev/logo-v2/svg/logo-mono-white.svg" />
    </q-avatar>

    <q-toolbar-title class="text-weight-bolder">
      SAPRO <span class="text-weight-thin text-blue-5">RACING</span>
    </q-toolbar-title>

    <q-input
      dark
      dense
      standout
      v-model="search"
      class="col-grow q-mx-lg"
      placeholder="¿Qué estás buscando? (Ej: Casco, Aceite, Cubierta...)"
    >
      <template v-slot:append>
        <q-icon name="search" />
      </template>
    </q-input>

    <q-btn color="positive" label="ACCESO CLIENTES" push class="q-px-md text-weight-bold" />
  </q-toolbar>
</template>

<script setup>
defineOptions({
  name: 'NavBar',
})
import { ref } from 'vue'
const search = ref('')
</script>
