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
      <q-card style="width: 100%; max-width: 400px" class="column">
        <!-- HEADER -->
        <q-card-section class="bg-grey-9 text-white row items-center q-pb-sm">
          <div class="text-h6">Nuevo Pedido</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <!-- CONTENIDO (Items) -->
        <q-card-section class="col scroll q-pa-none">
          <!-- Carrito vacío -->
          <div v-if="carrito.length === 0" class="column flex-center full-height text-grey-6">
            <q-icon name="shopping_cart_checkout" size="64px" />
            <div class="q-mt-md">El carrito está vacío</div>
          </div>

          <!-- Items en carrito -->
          <q-list separator v-else>
            <q-item v-for="item in carrito" :key="item.id" class="q-py-sm q-px-md">
              <!-- Avatar/Imagen -->
              <q-item-section avatar>
                <q-avatar rounded size="50px">
                  <img :src="getImageUrl(item.imagen)" style="object-fit: cover" />
                </q-avatar>
              </q-item-section>

              <!-- Nombre, código, precio -->
              <q-item-section>
                <q-item-label class="text-weight-bold ellipsis">
                  {{ item.nombre }}
                </q-item-label>
                <q-item-label caption>Código: {{ item.codigo }}</q-item-label>
                <q-item-label class="text-primary text-weight-bold">
                  ${{ (item.precio * item.cantidad).toFixed(2) }}
                </q-item-label>
              </q-item-section>

              <!-- Controles cantidad (Responsive) -->
              <q-item-section side class="q-gutter-xs">
                <div class="row items-center bg-grey-3 rounded-borders">
                  <q-btn
                    flat
                    dense
                    round
                    size="sm"
                    icon="remove"
                    @click="item.cantidad > 1 ? item.cantidad-- : eliminarDelCarrito(item.id)"
                    color="grey-8"
                  />
                  <div class="q-px-xs text-weight-bold text-caption">{{ item.cantidad }}</div>
                  <q-btn
                    flat
                    dense
                    round
                    size="sm"
                    icon="add"
                    @click="item.cantidad++"
                    color="grey-8"
                  />
                </div>

                <!-- Botón Eliminar -->
                <q-btn
                  flat
                  round
                  color="negative"
                  icon="delete"
                  size="sm"
                  @click="eliminarDelCarrito(item.id)"
                />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>

        <!-- FOOTER - Total y Botón -->
        <q-card-section class="bg-grey-2 border-top">
          <div class="row justify-between items-center q-mb-md">
            <div class="text-subtitle2 text-subtitle1-sm text-grey-8">Total Estimado:</div>
            <div class="text-h5 text-h6-sm text-primary text-weight-bolder">${{ totalPrecio }}</div>
          </div>

          <q-btn
            label="FINALIZAR VENTA"
            color="positive"
            class="full-width text-weight-bold q-py-sm"
            icon="check_circle"
            :disable="carrito.length === 0 || loading"
            :loading="loading"
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
import axios from 'axios'
import { API_ENDPOINTS, getImageUrl } from 'src/config/api'

const $q = useQuasar()
const isOpen = ref(false)
const loading = ref(false)
const { carrito, eliminarDelCarrito, totalItems, totalPrecio, vaciarCarrito } = useCarrito()

const abrirCarrito = () => {
  isOpen.value = true
}

const procesarVenta = () => {
  $q.dialog({
    title: 'Confirmar Venta',
    message: `¿Registrar venta por $${totalPrecio.value}? Se descontará el stock de los productos.`,
    cancel: true,
    persistent: true,
  }).onOk(async () => {
    loading.value = true
    try {
      // Construir carrito limpio solo con los datos necesarios
      const carritoLimpio = carrito.value.map((item) => ({
        id: Number(item.id),
        cantidad: Number(item.cantidad),
        precio: Number(item.precio),
      }))

      // Validar datos antes de enviar
      if (carritoLimpio.some((item) => !item.id || item.cantidad <= 0 || item.precio < 0)) {
        throw new Error('Datos inválidos en el carrito')
      }

      const totalLimpio = Number(totalPrecio.value)
      if (totalLimpio <= 0) {
        throw new Error('Total debe ser mayor a 0')
      }

      console.log('📤 Reques to:', { carrito: carritoLimpio, total: totalLimpio, admin: 'Admin' })

      const response = await axios.post(API_ENDPOINTS.REGISTRAR_VENTA, {
        carrito: carritoLimpio,
        total: totalLimpio,
        admin: 'Admin',
      })

      if (response.data.success) {
        $q.notify({
          type: 'positive',
          message: response.data.mensaje || 'Venta registrada correctamente',
          position: 'top',
        })
        vaciarCarrito()
        isOpen.value = false
      } else {
        $q.notify({
          type: 'negative',
          message: response.data.mensaje || 'Error al registrar la venta',
          position: 'top',
        })
      }
    } catch (error) {
      console.error('❌ Error:', error)
      const mensajeError =
        error.response?.data?.mensaje || error.message || 'Error de conexión al servidor'
      $q.notify({
        type: 'negative',
        message: mensajeError,
        position: 'top',
      })
    } finally {
      loading.value = false
    }
  })
}
</script>

<style scoped>
.border-top {
  border-top: 1px solid #e0e0e0;
}
</style>
