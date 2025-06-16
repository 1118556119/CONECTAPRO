<template>
  <div class="container-fluid">
    <div class="row vh-100">
      <!-- Columna izquierda decorativa - visible solo en pantallas md y superiores -->
      <div class="col-md-6 d-none d-md-flex bg-primary align-items-center justify-content-center text-white">
        <div class="text-center px-4">
          <i class="fas fa-tools fa-4x mb-4"></i>
          <h1 class="display-4 fw-bold mb-4">CONECTAPRO</h1>
          <p class="lead">
            Accede a tu cuenta para solicitar servicios de mantenimiento o administrar tu perfil profesional.
          </p>
        </div>
      </div>
      
      <!-- Columna del formulario - ocupa toda la pantalla en móviles -->
      <div class="col-md-6 d-flex align-items-center justify-content-center">
        <!-- Botón para volver a la página principal -->
        <div class="position-absolute top-0 start-0 m-3">
          <router-link to="/" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver
          </router-link>
        </div>
        
        <div class="card border-0 shadow-lg" style="max-width: 450px; width: 100%;">
          <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
              <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
              <h2 class="fw-bold">Iniciar Sesión</h2>
              <p class="text-muted small">Ingresa tus credenciales para continuar</p>
            </div>
            
            <!-- Alerta para errores, se muestra solo si hay error -->
            <div v-if="error" class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="fas fa-exclamation-triangle me-2"></i>
              {{ error }}
              <button type="button" class="btn-close" @click="error = ''" aria-label="Close"></button>
            </div>

            <form @submit.prevent="login">
              <!-- Email -->
              <div class="form-floating mb-3">
                <input
                  v-model="form.email"
                  type="email"
                  class="form-control"
                  id="email"
                  placeholder="nombre@ejemplo.com"
                  required
                />
                <label for="email">Correo Electrónico</label>
              </div>
              
              <!-- Password con vista previa -->
              <div class="form-floating mb-3">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  class="form-control"
                  id="password"
                  placeholder="Contraseña"
                  required
                />
                <label for="password">Contraseña</label>
                <button 
                  type="button" 
                  class="btn btn-sm text-primary position-absolute end-0 top-50 translate-middle-y me-2" 
                  @click="showPassword = !showPassword"
                  style="background: transparent; border: none;"
                >
                  <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
              
              <!-- Recordarme y ¿Olvidaste tu contraseña? -->
              <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" v-model="form.remember" id="remember">
                  <label class="form-check-label small" for="remember">
                    Recordarme
                  </label>
                </div>
                <router-link to="/forgot-password" class="text-primary text-decoration-none small">
                  ¿Olvidaste tu contraseña?
                </router-link>
              </div>

              <!-- Botón de inicio de sesión con estado de carga -->
              <button 
                type="submit" 
                class="btn btn-primary w-100 py-2 mb-3"
                :disabled="isLoading"
              >
                <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                {{ isLoading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
              </button>
              
              <!-- Separador -->
              <div class="text-center position-relative mb-3">
                <hr class="text-muted">
                <span class="position-absolute top-50 start-50 translate-middle px-3 bg-white text-muted small">O</span>
              </div>
              
              <!-- Iniciar sesión con redes sociales -->
              <div class="row g-2 mb-3">
                <div class="col">
                  <button type="button" class="btn btn-outline-secondary w-100">
                    <i class="fab fa-google me-2"></i> Google
                  </button>
                </div>
                <div class="col">
                  <button type="button" class="btn btn-outline-secondary w-100">
                    <i class="fab fa-microsoft me-2"></i> Microsoft
                  </button>
                </div>
              </div>
            </form>
            
            <div class="text-center mt-4">
              <p class="text-muted small mb-0">
                ¿No tienes una cuenta?
                <router-link to="/register" class="text-primary fw-bold text-decoration-none">
                  Regístrate 
                </router-link>
              </p>
            </div>
          </div>
        </div>
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
      form: {
        email: '',
        password: '',
        remember: false
      },
      isLoading: false,
      error: '',
      showPassword: false
    };
  },
  methods: {
    async login() {
      try {
        this.isLoading = true;
        this.error = '';
        
        // Validar formulario
        if (!this.validateForm()) {
          this.isLoading = false;
          return;
        }
        
        // Mostrar loading
        Swal.fire({
          title: 'Iniciando sesión...',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        
        // ✅ Solo log del email si es necesario para depuración
        console.log('Intentando login para:', this.form.email);
        
        const response = await axios.post('/login', {
          email: this.form.email,
          password: this.form.password,
          remember: this.form.remember
        }, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });
      
        
        // ✅ Solo log del estado de éxito
        console.log('Login exitoso para:', this.form.email);
        
        if (response.data.success && response.data.data && response.data.data.token) {
          // Guardar token y datos del usuario
          localStorage.setItem('token', response.data.data.token);
          localStorage.setItem('user_type', response.data.data.user_type);
          localStorage.setItem('user', JSON.stringify(response.data.data.user));
          
          // Configurar el header de Authorization para futuras peticiones
          axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.data.token}`;
          
          // Cerrar loading
          Swal.close();
          
          // Mostrar mensaje de éxito
          await Swal.fire({
            icon: 'success',
            title: '¡Bienvenido!',
            text: `Hola ${response.data.data.user.name}`,
            timer: 2000,
            showConfirmButton: false
          });
          
          // Redirigir según tipo de usuario
          if (response.data.data.user_type === 'technician') {
            this.$router.push('/profile');
          } else if (response.data.data.user_type === 'client') {
            this.$router.push('/client-profile');
          } else {
            this.$router.push('/dashboard');
          }
          
        } else {
          throw new Error('Respuesta del servidor inválida');
        }
        
      } catch (error) {
        Swal.close();
        // ✅ Solo log de errores sin datos sensibles
        console.error('Error en login:', error.response?.status || 'Error desconocido');
        
        // Mostrar mensaje de error adecuado
        if (error.response?.status === 401) {
          this.error = 'Credenciales incorrectas. Por favor, verifica tu email y contraseña.';
        } else if (error.response?.data?.message) {
          this.error = error.response.data.message;
        } else {
          this.error = 'Error al conectar con el servidor. Inténtalo nuevamente.';
        }
        
        await Swal.fire({
          icon: 'error',
          title: 'Error de inicio de sesión',
          text: this.error
        });
        
      } finally {
        this.isLoading = false;
      }
    },
    
    validateForm() {
      if (!this.form.email) {
        this.error = 'El email es obligatorio';
        return false;
      }
      
      if (!this.form.password) {
        this.error = 'La contraseña es obligatoria';
        return false;
      }
      
      return true;
    },
    
    async logout() {
      const result = await Swal.fire({
        title: '¿Cerrar sesión?',
        text: '¿Estás seguro de que deseas salir?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
      });
      
      if (result.isConfirmed) {
        try {
          await axios.post('/api/logout', {}, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
          });
        } catch (e) {
          console.error('Error al cerrar sesión');
        } finally {
          // Limpiar datos del localStorage
          localStorage.removeItem('token');
          localStorage.removeItem('user_type');
          localStorage.removeItem('user');
          delete axios.defaults.headers.common['Authorization'];
          this.$router.push('/login');
        }
      }
    }
  }
};
</script>

<style scoped>
.form-floating > label {
  padding-left: 1rem;
}

/* Gradiente para fondo de columna izquierda */
.bg-primary {
  background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%) !important;
}

/* Transiciones suaves */
.btn, .form-control, .card {
  transition: all 0.3s ease;
}

/* Hover para botones de inicio de sesión social */
.btn-outline-secondary:hover {
  background-color: #f8f9fa;
  color: #0d6efd;
  border-color: #dee2e6;
}
</style>