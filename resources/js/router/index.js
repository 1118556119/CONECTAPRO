// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router';

// Importar vistas
import Home from '@/views/home.vue';
import Login from '@/views/login.vue';
import Register from '@/views/register.vue';
import ForgotPassword from '@/views/forgotPassword.vue';

// Importar componentes
import Profile from '@/components/profile.vue';
import ServiceRequestForm from '@/components/ServiceRequestForm.vue';
import MyServiceRequests from '@/components/MyServiceRequests.vue';

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
  {
    path: '/register',
    name: 'Register',
    component: Register
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword
  },
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: { requiresAuth: true }
  },
  {
    path: '/solicitar-servicio',
    name: 'ServiceRequestForm',
    component: ServiceRequestForm,
    meta: { requiresAuth: true }
  },
  {
    path: '/my-requests',
    name: 'MyServiceRequests',
    component: MyServiceRequests,
    meta: { requiresAuth: true }
  },
  {
    path: '/dashboard',
    redirect: '/profile'
  },
  {
    path: '/:catchAll(.*)',
    redirect: '/'
  }
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes
});

// Guard para rutas protegidas
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth) {
    const token = localStorage.getItem('token');
    if (!token) {
      next({ path: '/login', query: { redirect: to.fullPath } });
    } else {
      next();
    }
  } else {
    next();
  }
});

export default router;