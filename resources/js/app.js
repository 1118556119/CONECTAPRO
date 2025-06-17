// Importar Bootstrap CSS y JS
import { createApp } from 'vue';
import router from './router';
import axios from 'axios';
import Swal from 'sweetalert2';
import App from './App.vue'; 

// Importar CSS (verificar orden)
import '../css/variables.css';  // PRIMERO variables CSS
import '../css/app.css';        // LUEGO app CSS
import 'bootstrap/dist/css/bootstrap.min.css'; // DESPUÉS Bootstrap
import 'bootstrap-icons/font/bootstrap-icons.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Configurar axios
axios.defaults.baseURL = window.location.origin + '/api';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Interceptores
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user_type');
      if (router.currentRoute.value.path !== '/login') {
        router.push('/login');
      }
    }
    return Promise.reject(error);
  }
);

// Crear app con Vue 3
const app = createApp(App);

app.config.globalProperties.$axios = axios;
app.config.globalProperties.$swal = Swal;

app.use(router);
app.mount('#app');

// Configuración de tema según tipo de usuario (versión unificada)
document.addEventListener('DOMContentLoaded', () => {
  // Obtener tipo de usuario del localStorage
  const userType = localStorage.getItem('user_type');
  
  // Aplicar clase al body según el tipo de usuario
  if (userType === 'technician') {
    document.body.classList.add('technician-mode');
    document.body.classList.add('user-technician');
    
    // Cambia el color del theme-color meta tag
    const metaThemeColor = document.querySelector('meta[name="theme-color"]');
    if (metaThemeColor) {
      metaThemeColor.setAttribute('content', '#01A66F');
    }
  } else if (userType === 'client') {
    document.body.classList.add('user-client');
  }
  
  // Animación sutil de carga
  const appElement = document.getElementById('app');
  if (appElement) {
    appElement.style.opacity = '0';
    setTimeout(() => {
      appElement.style.transition = 'opacity 0.5s ease';
      appElement.style.opacity = '1';
    }, 100);
  }
});