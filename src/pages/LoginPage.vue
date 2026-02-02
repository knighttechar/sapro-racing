<template>
  <q-layout view="Lhh Lpr lFf">
    <q-page-container>
      <q-page class="bg-grey-10 flex flex-center window-height window-width">

        <q-card class="login-card no-shadow" bordered style="width: 100%; max-width: 400px; border-radius: 15px;">
          <q-card-section class="text-center q-pa-xl">
             <div class="text-h4 text-weight-bolder text-grey-10 q-mb-xs">SAPRO</div>
             <div class="text-subtitle1 text-grey-7">Panel de Administración</div>
          </q-card-section>

          <q-card-section class="q-px-lg">
            <q-form @submit="handleLogin" class="q-gutter-md">
              <q-input v-model="form.usuario" label="Usuario" filled color="grey-10">
                <template v-slot:prepend><q-icon name="person" /></template>
              </q-input>

              <q-input v-model="form.password" label="Contraseña" type="password" filled color="grey-10">
                <template v-slot:prepend><q-icon name="lock" /></template>
              </q-input>

              <div class="q-mt-xl">
                <q-btn
                  label="INGRESAR"
                  type="submit"
                  color="primary"
                  class="full-width text-weight-bold"
                  size="lg"
                  :loading="loading"
                />
              </div>
            </q-form>
          </q-card-section>

          <q-card-section class="text-center q-pb-lg">
            <q-btn flat color="grey-7" label="Volver al inicio" to="/" no-caps />
          </q-card-section>
        </q-card>

      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import axios from 'axios'

const $q = useQuasar()
const router = useRouter()
const loading = ref(false)
const form = ref({ usuario: '', password: '' })

const handleLogin = async () => {
  loading.value = true
  try {
    const response = await axios.post('https://saproracing.knighttech.com.ar/login.php', form.value)

    if (response.data.success) {
      // 1. Notificación de éxito
      $q.notify({
        color: 'positive',
        message: response.data.mensaje,
        position: 'top',
        icon: 'check_circle'
      })

      // 2. Guardamos los datos en el navegador
      localStorage.setItem('isLogged', 'true')
      // Limpiamos el nombre para que no diga "Bienvenido Juan", solo "Juan"
      const nombreLimpio = response.data.mensaje.replace('Bienvenido ', '')
      localStorage.setItem('adminNombre', nombreLimpio)

      // 3. Redirigimos al inicio
      router.push('/')

      // 4. Forzamos un refresco pequeño para que el Layout se entere del cambio
      setTimeout(() => {
        location.reload()
      }, 500)

    } else {
      $q.notify({
        color: 'negative',
        message: response.data.mensaje,
        position: 'top',
        icon: 'error'
      })
    }
  } catch (error) {
    console.error('Error en login:', error)
    $q.notify({
      color: 'negative',
      message: 'No se pudo conectar con el servidor',
      position: 'top'
    })
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-card {
  width: 100%;
  max-width: 400px;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05) !important;
}
</style>
