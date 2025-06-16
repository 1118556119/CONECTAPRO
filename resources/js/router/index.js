import { createRouter, createWebHistory } from 'vue-router';

// Importar vistas
import Home from '@/views/home.vue';
import Login from '@/views/login.vue';
import Register from '@/views/register.vue';
import ForgotPassword from '@/views/forgotPassword.vue';

// Importar componentes
import Profile from '@/components/Profile.vue';
import ServiceRequestForm from '@/components/ServiceRequestForm.vue';

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
    path: '/dashboard',
    redirect: '/profile'
  },
  {
    path: '/client-profile',
    redirect: '/profile'  // O a cualquier otra página apropiada
  },
  {
    path: '/:catchAll(.*)',
    redirect: '/'
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Actualizar guard de navegación para que no redirija a /client-profile
router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAuth && record.meta.userType)) {
    const userType = localStorage.getItem('user_type');
    
    if (userType === to.meta.userType) {
      next();
    } else {
      if (userType === 'technician') {
        next('/profile');
      } else if (userType === 'client') {
        // Cambiar esta redirección a otra página apropiada
        next('/profile');  // O cualquier otra ruta adecuada para clientes
      } else {
        next('/login');
      }
    }
  } else {
    next();
  }
});

export default router;