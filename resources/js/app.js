import { createApp } from 'vue';
import router from './router';
import axios from 'axios';
import Swal from 'sweetalert2';
import App from './App.vue'; 

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap'; 
import 'bootstrap-icons/font/bootstrap-icons.css';

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