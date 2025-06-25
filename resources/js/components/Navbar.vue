<template>
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3">
    <div class="container">
      <!-- Corregir la ruta del logo -->
      <router-link class="navbar-brand" to="/">
        <img src="/public/img/Logo.png" height="30" alt="CONECTAPRO" />
      </router-link>
      
      <!-- Toggler -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <!-- Contenido de la barra de navegación -->
      <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <router-link class="nav-link" to="/">Inicio</router-link>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/#servicios">Servicios</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/#como-funciona">Cómo Funciona</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/#faq">Preguntas Frecuentes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/#contacto">Contacto</a>
          </li>
        </ul>
        
        <!-- Menú de usuario (lado derecho) -->
        <div class="navbar-nav">
          <!-- Componente de notificaciones (solo si está logueado) -->
          <notifications-dropdown v-if="isLoggedIn" class="nav-item me-2" />
          
          <!-- Usuario no autenticado -->
          <template v-if="!isLoggedIn">
            <div class="nav-item">
              <router-link to="/login" class="btn btn-outline-primary me-2">Iniciar Sesión</router-link>
            </div>
            <div class="nav-item">
              <router-link to="/register" class="btn btn-primary">Registrarse</router-link>
            </div>
          </template>
          
          <!-- Usuario autenticado -->
          <div v-else class="nav-item dropdown">
            <a 
              class="nav-link dropdown-toggle d-flex align-items-center" 
              href="#" 
              id="navbarDropdown" 
              role="button" 
              data-bs-toggle="dropdown"
            >
              <img 
                v-if="userPhoto" 
                :src="userPhoto" 
                alt="Profile" 
                class="rounded-circle me-2" 
                style="width: 32px; height: 32px; object-fit: cover;"
              >
              <div 
                v-else 
                class="avatar-initials rounded-circle me-2 d-flex align-items-center justify-content-center"
                style="width: 32px; height: 32px; background-color: #0B4F6C; color: white;"
              >
                {{ userInitials }}
              </div>
              <span>{{ userName }}</span>
            </a>
            
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
              <li>
                <router-link class="dropdown-item" to="/profile">
                  <i class="bi bi-person me-2"></i>Mi Perfil
                </router-link>
              </li>
              <li v-if="userType === 'client'">
                <router-link class="dropdown-item" to="/solicitar-servicio">
                  <i class="bi bi-tools me-2"></i>Solicitar Servicio
                </router-link>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item" href="#" @click.prevent="logout">
                  <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import NotificationsDropdown from './NotificationsDropdown.vue';
import { eventBus, AUTH_EVENTS } from '../utils/eventBus';
import Swal from 'sweetalert2';

export default {
  name: 'Navbar',
  
  components: {
    NotificationsDropdown
  },
  
  data() {
    return {
      isLoggedIn: false,
      userName: '',
      userPhoto: '',
      userType: ''
    };
  },
  
  computed: {
    userInitials() {
      if (!this.userName) return '';
      return this.userName.split(' ')
        .map(name => name[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    }
  },
  
  methods: {
    logout() {
      Swal.fire({
        title: '¿Cerrar sesión?',
        text: '¿Estás seguro de que deseas cerrar sesión?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          // Eliminar datos de sesión
          localStorage.removeItem('token');
          localStorage.removeItem('user');
          localStorage.removeItem('userPhoto');
          
          // Emitir evento de cierre de sesión
          eventBus.emit(AUTH_EVENTS.LOGOUT);
          
          // Actualizar estado local
          this.isLoggedIn = false;
          this.userName = '';
          this.userPhoto = '';
          this.userType = '';
          
          // Redireccionar
          this.$router.push('/');
        }
      });
    },
    
    checkAuth() {
      const token = localStorage.getItem('token');
      const userData = JSON.parse(localStorage.getItem('user') || '{}');
      
      this.isLoggedIn = !!token;
      if (this.isLoggedIn && userData) {
        this.userName = userData.name || '';
        this.userPhoto = userData.photo_url || localStorage.getItem('userPhoto') || '';
        this.userType = userData.user_type || '';
      }
    }
  },
  
  mounted() {
    this.checkAuth();
    
    // Escuchar eventos de autenticación
    eventBus.on(AUTH_EVENTS.LOGIN, () => {
      this.checkAuth();
    });
    
    eventBus.on(AUTH_EVENTS.LOGOUT, () => {
      this.isLoggedIn = false;
      this.userName = '';
      this.userPhoto = '';
      this.userType = '';
    });
  }
};
</script>

<style scoped>
.navbar {
  transition: background-color 0.3s ease;
}

.navbar.scrolled {
  background-color: #f8f9fa !important;
}
</style>