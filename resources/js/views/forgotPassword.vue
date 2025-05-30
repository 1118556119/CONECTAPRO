<template>
    <div class="container d-flex justify-content-center align-items-center vh-100">
      <!-- Botón para volver a la página principal -->
      <div class="position-absolute top-0 start-0 m-3">
        <router-link to="/" class="text-decoration-none text-primary d-flex align-items-center gap-2">
          <i class="fas fa-arrow-left"></i> Volver a la página principal
        </router-link>
      </div>
  
      <div class="card shadow-lg p-4" style="max-width: 400px; width: 100%;">
        <h2 class="text-center text-primary mb-4">Recuperar Contraseña</h2>
        <form @submit.prevent="sendResetLink">
          <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input
              v-model="email"
              type="email"
              id="email"
              class="form-control"
              placeholder="Ingresa tu correo"
              required
            />
          </div>
          <button type="submit" class="btn btn-primary w-100">Enviar Enlace de Recuperación</button>
        </form>
        <div class="text-center mt-3">
          <p class="text-muted">
            ¿Ya tienes una cuenta?
            <router-link to="/login" class="text-primary text-decoration-none">
              Inicia sesión aquí
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  import Swal from 'sweetalert2';
  
  export default {
    data() {
      return {
        email: ''
      };
    },
    methods: {
      async sendResetLink() {
        try {
          const response = await axios.post('/forgot-password', { email: this.email });
          
          Swal.fire({
            icon: 'success',
            title: 'Enlace enviado',
            text: 'Se ha enviado un enlace de recuperación a tu correo.',
            confirmButtonText: 'Volver al login'
          }).then(() => {
            this.$router.push('/login');
          });
        } catch (error) {
          console.error('Error al enviar el enlace de recuperación:', error);
          
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Hubo un problema al enviar el enlace. Verifica tu correo e inténtalo nuevamente.'
          });
        }
      }
    }
  };
  </script>