<template>
  <div class="q-pa-md">
    <q-btn outline color="grey-7" label="LIMPIAR FILTROS" class="full-width q-mb-md" size="sm" />

    <q-list bordered separator class="rounded-borders">
      <q-expansion-item label="MARCAS" default-opened header-class="text-weight-bold">
        <q-card>
          <q-card-section>
            <q-checkbox v-model="marcas" val="yamaha" label="Yamaha" dense class="full-width q-mb-xs" />
            <q-checkbox v-model="marcas" val="honda" label="Honda" dense class="full-width" />
          </q-card-section>
        </q-card>
      </q-expansion-item>

      <q-expansion-item label="CATEGORÍAS" header-class="text-weight-bold">
        <q-card>
          <q-card-section>
            <div class="cursor-pointer text-grey-8 q-py-xs">Cascos Rebatibles</div>
            <div class="cursor-pointer text-grey-8 q-py-xs">Lubricantes</div>
          </q-card-section>
        </q-card>
      </q-expansion-item>
    </q-list>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const marcas = ref([])
</script>