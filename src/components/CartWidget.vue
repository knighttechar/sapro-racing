<template>
  <div>
    <q-page-sticky position="bottom-right" :offset="[18, 18]" style="z-index: 2000">
      <q-btn
        fab
        icon="shopping_cart"
        color="orange-9"
        text-color="white"
        @click="abrirCarrito"
        class="shadow-10"
      >
        <q-badge color="red" floating v-if="totalItems > 0">{{ totalItems }}</q-badge>
      </q-btn>
    </q-page-sticky>

    <q-dialog v-model="isOpen" position="right" full-height maximized>
      <q-card style="width: 400px; max-width: 100vw" class="column">

        <q-card-section class="bg-grey-9 text-white row items-center q-pb-sm">
          <div class="text-h6">Nuevo Pedido</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="col scroll q-pa-none">
          <div v-if="carrito.length === 0" class="column flex-center full-height text-grey-6">
            <q-icon name="shopping_cart_checkout" size="64px" />
            <div class="q-mt-md">El carrito está vacío</div>
          </div>

          <q-list separator v-else>
            <q-item v-for="item in carrito" :key="item.id" class="q-py-md">
              <q-item-section avatar>
                <q-avatar rounded size="50px">
                  <img :src="item.imagen && item.imagen !== 'default.jpg' ? `https://saproracing.knighttech.com.ar/imagenes/${item.imagen}` : 'https://placehold.co/100'" style="object-fit: cover;">
                </q-avatar>
              </q-item-section>

              <q-item-section>
                <q-item-label class="text-weight-bold">{{ item.nombre }}</q-item-label>
                <q-item-label caption>Código: {{ item.codigo }}</q-item-label>
                <q-item-label class="text-primary text-weight-bold">${{ item.precio * item.cantidad }}</q-item-label>
              </q-item-section>

              <q-item-section side style="min-width: 100px">
                <div class="row items-center no-wrap bg-grey-3 rounded-borders">
                  <q-btn flat dense round size="sm" icon="remove" @click="item.cantidad > 1 ? item.cantidad-- : eliminarDelCarrito(item.id)" color="grey-8" />
                  <div class="q-px-sm text-weight-bold">{{ item.cantidad }}</div>
                  <q-btn flat dense round size="sm" icon="add" @click="item.cantidad++" color="grey-8" />
                </div>
              </q-item-section>

              <q-item-section side>
                 <q-btn flat round color="negative" icon="delete" size="sm" @click="eliminarDelCarrito(item.id)" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>

        <q-card-section class="bg-grey-2 border-top">
          <div class="row justify-between items-center q-mb-md">
            <div class="text-subtitle1 text-grey-8">Total Estimado:</div>
            <div class="text-h5 text-primary text-weight-bolder">${{ totalPrecio }}</div>
          </div>

          <q-btn
            label="FINALIZAR VENTA"
            color="positive"
            class="full-width text-weight-bold q-py-sm"
            icon="check_circle"
            :disable="carrito.length === 0"
            @click="procesarVenta"
          />
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useCarrito } from 'src/composables/useCarrito'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const isOpen = ref(false)
const { carrito, eliminarDelCarrito, totalItems, totalPrecio, vaciarCarrito } = useCarrito()

const abrirCarrito = () => {
  isOpen.value = true
}

const procesarVenta = () => {
  // AQUÍ ES DONDE CONECTAREMOS CON PHP EN EL FUTURO PARA RESTAR STOCK
  $q.dialog({
    title: 'Confirmar Venta',
    message: `¿Registrar venta por $${totalPrecio.value}? Esto (a futuro) descontará el stock.`,
    cancel: true,
    persistent: true
  }).onOk(() => {
    // Simulación de éxito
    $q.notify({
      type: 'positive',
      message: 'Venta registrada correctamente (Simulación)'
    })
    vaciarCarrito()
    isOpen.value = false
  })
}
</script>

<style scoped>
.border-top {
  border-top: 1px solid #e0e0e0;
}
</style>
