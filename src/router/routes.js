const routes = [
  // Ruta para el catálogo (usa el Layout con Navbar y Menú)
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      { path: 'datos', component: () => import('pages/DataViewPage.vue') },
      { path: 'estadisticas', component: () => import('pages/SalesStatisticsPage.vue') },
    ],
  },

  // Ruta para el Login (Independiente, ocupa toda la pantalla)
  {
    path: '/login',
    component: () => import('pages/LoginPage.vue'),
  },

  // Error 404
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
]

export default routes
