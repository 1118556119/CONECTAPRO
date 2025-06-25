<template>
  <div class="container-fluid">
    <div class="row vh-100">
      <!-- Columna izquierda decorativa - visible solo en pantallas md y superiores -->
      <div class="col-md-6 d-none d-md-flex login-gradient align-items-center justify-content-center text-white">
        <div class="text-center px-4">
          <div class="text-center mb-4">
            <router-link class="navbar-brand" to="/">
        <img src="/public/img/LogoBlanco.png" height="100" alt="CONECTAPRO" />
      </router-link>
          </div>
          <p class="lead">
            Conectando talentos tecnológicos con quienes más los necesitan
          </p>
        </div>
      </div>
      
      <!-- Columna del formulario - ocupa toda la pantalla en móviles -->
      <div class="col-md-6 d-flex align-items-center justify-content-center">
        <!-- Botón para volver a la página principal -->
        <div class="position-absolute top-0 start-0 m-3">
          
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
// Añadir la importación del eventBus
import { eventBus, AUTH_EVENTS } from '../utils/eventBus';

const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
});

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
      if (!this.validateForm()) {
        return;
      }
      
      try {
        this.isLoading = true;
        this.error = '';
        
        console.log('Intentando login para:', this.form.email);
        
        const response = await axios.post('/login', {
          email: this.form.email,
          password: this.form.password
        });
        
        // Inspeccionar la respuesta para depuración
        console.log('Respuesta del servidor:', response.data);
        
        // Verificación más flexible de la respuesta
        if (response.data) {
          // Buscar el token en diferentes lugares de la respuesta
          const token = response.data.token || 
                        (response.data.data && response.data.data.token) || 
                        response.data.access_token;
          
          if (token) {
            console.log('Login exitoso para:', this.form.email);
            
            // Guardar token
            localStorage.setItem('token', token);
            
            // Guardar datos de usuario buscando en diferentes estructuras posibles
            const userData = response.data.user || 
                            response.data.data || 
                            (response.data.data && response.data.data.user) || 
                            {};
                            
            if (Object.keys(userData).length > 0) {
              localStorage.setItem('user', JSON.stringify(userData));
            }
            
            // Mostrar mensaje de éxito
            Toast.fire({
              icon: 'success',
              title: '¡Bienvenido de nuevo!'
            });
            
            // Emitir evento de login
            try {
              if (typeof eventBus !== 'undefined' && eventBus.emit) {
                eventBus.emit(AUTH_EVENTS.LOGIN, userData);
              }
            } catch (eventError) {
              console.warn('Error en eventBus:', eventError);
            }
            
            // Redireccionar
            this.$router.push('/profile');
            return; // Salir de la función después del éxito
          }
        }
        
        // Si llegamos aquí es porque no se encontró un token válido
        throw new Error('No se encontró un token de autenticación en la respuesta');
        
      } catch (error) {
        this.isLoading = false;
        
        // Determinar el tipo de error
        if (error.response && error.response.status === 422) {
          const validationErrors = error.response.data.errors;
          
          // Crear mensaje de error de validación
          let errorMessage = 'Por favor verifica los datos ingresados';
          if (validationErrors) {
            errorMessage = Object.values(validationErrors)
              .flat()
              .join('<br>');
          }
          
          // Mostrar error con SweetAlert
          Swal.fire({
            icon: 'error',
            title: 'Error de validación',
            html: errorMessage,
            confirmButtonColor: '#3085d6'
          });
          
        } else if (error.response && error.response.data) {
          // Otros errores del servidor
          Swal.fire({
            icon: 'error',
            title: 'Error de autenticación',
            text: error.response.data.message || 'Error al intentar iniciar sesión',
            confirmButtonColor: '#3085d6'
          });
        } else {
          // Error general
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Ha ocurrido un problema al intentar iniciar sesión',
            confirmButtonColor: '#3085d6'
          });
        }
        
        // Mantener el console.error para depuración
        console.error('Error en login:', error);
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

/* Añadir este selector específico que no sobrescribe el global */
.login-gradient {
  background: var(--gradient-primary) !important;
  color: white;
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

/* Estilos adicionales para el login */
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  background-color: var(--tech-gray);
}

.login-box {
  background: white;
  border-radius: var(--border-radius);
  overflow: hidden;
  box-shadow: var(--box-shadow);
}

.login-sidebar {
  background: var(--gradient-primary);
  color: white;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.login-title {
  font-weight: 700;
  margin-bottom: 1rem;
}

.login-subtitle {
  opacity: 0.9;
  margin-bottom: 2rem;
}

.login-feature {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
}

.login-feature-icon {
  background-color: rgba(255, 255, 255, 0.2);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
}

.login-form {
  padding: 2rem;
}

.form-floating>.form-control:focus~label {
  color: var(--tech-blue);
}

.form-floating>.form-control:focus {
  border-color: var(--tech-blue);
  box-shadow: 0 0 0 0.25rem rgba(11, 79, 108, 0.25);
}
</style>