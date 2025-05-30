<template>
  <div class="container-fluid">
    <div class="row min-vh-100">
      <!-- Panel izquierdo decorativo - solo visible en pantallas md y superiores -->
      <div class="col-lg-5 d-none d-lg-flex bg-primary position-relative overflow-hidden">
        <!-- Decoración de fondo -->
        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
          <div class="position-absolute top-50 start-50 translate-middle">
            <i class="fas fa-tools fa-10x text-white opacity-25"></i>
          </div>
        </div>
        
        <!-- Contenido del panel -->
        <div class="d-flex flex-column justify-content-center text-white p-5 position-relative">
          <h1 class="display-4 fw-bold mb-4">Únete a nuestra comunidad</h1>
          <p class="lead mb-4">
            Regístrate para acceder a servicios de mantenimiento técnico profesional para tus equipos.
          </p>
          <div class="mb-5">
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle bg-white p-2 d-inline-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                <i class="fas fa-check text-primary"></i>
              </div>
              <span class="fw-bold">Servicio técnico especializado</span>
            </div>
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle bg-white p-2 d-inline-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                <i class="fas fa-check text-primary"></i>
              </div>
              <span class="fw-bold">Mantenimiento preventivo y correctivo</span>
            </div>
            <div class="d-flex align-items-center">
              <div class="rounded-circle bg-white p-2 d-inline-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                <i class="fas fa-check text-primary"></i>
              </div>
              <span class="fw-bold">Seguimiento y garantía en todos los servicios</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Panel del formulario -->
      <div class="col-lg-7 d-flex align-items-center justify-content-center py-5">
        <!-- Botón para volver a la página principal -->
        <div class="position-absolute top-0 start-0 m-4">
          <router-link to="/" class="btn btn-outline-primary rounded-pill">
            <i class="fas fa-arrow-left me-2"></i> Volver al inicio
          </router-link>
        </div>
        
        <div class="card border-0 shadow-lg p-4" style="max-width: 600px; width: 100%;">
          <div class="card-body p-3 p-md-4">
            <h2 class="text-center text-primary mb-4 fw-bold">
              <i class="fas fa-user-plus me-2"></i> Crear nueva cuenta
            </h2>
            
            <!-- Alerta para errores -->
            <div v-if="error" class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
              <i class="fas fa-exclamation-circle me-2"></i>
              {{ error }}
              <button type="button" class="btn-close" @click="error = ''" aria-label="Close"></button>
            </div>
            
            <!-- Selección de tipo de usuario -->
            <div class="mb-4">
              <h5 class="fw-bold mb-3">¿Cómo deseas registrarte?</h5>
              <div class="row g-3">
                <div class="col-md-6">
                  <div 
                    class="card h-100 border-0 p-3 cursor-pointer user-type-card"
                    :class="{'selected': form.user_type === 'client'}"
                    @click="selectUserType('client')"
                  >
                    <div class="text-center">
                      <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                          style="width: 70px; height: 70px; background-color: rgba(13, 110, 253, 0.1)">
                        <i class="fas fa-user fa-2x text-primary"></i>
                      </div>
                      <h5 class="mb-2">Cliente</h5>
                      <p class="text-muted small mb-0">
                        Solicita servicios de mantenimiento para tus equipos
                      </p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div 
                    class="card h-100 border-0 p-3 cursor-pointer user-type-card"
                    :class="{'selected': form.user_type === 'technician'}"
                    @click="selectUserType('technician')"
                  >
                    <div class="text-center">
                      <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                          style="width: 70px; height: 70px; background-color: rgba(25, 135, 84, 0.1)">
                        <i class="fas fa-tools fa-2x text-success"></i>
                      </div>
                      <h5 class="mb-2">Técnico</h5>
                      <p class="text-muted small mb-0">
                        Ofrece tus servicios de mantenimiento y reparación
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Error de tipo de usuario -->
              <div v-if="showUserTypeError" class="text-danger small mt-2">
                <i class="fas fa-exclamation-circle me-1"></i>
                Debes seleccionar un tipo de usuario
              </div>
            </div>
            
            <!-- Pestañas para métodos de registro -->
            <ul class="nav nav-pills nav-justified mb-4" id="registerTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="manual-tab" data-bs-toggle="pill" data-bs-target="#manual-tab-pane" type="button">
                  <i class="fas fa-edit me-2"></i>Registro manual
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="social-tab" data-bs-toggle="pill" data-bs-target="#social-tab-pane" type="button">
                  <i class="fas fa-share-alt me-2"></i>Registro con redes
                </button>
              </li>
            </ul>
            
            <div class="tab-content" id="registerTabsContent">
              <!-- Registro manual -->
              <div class="tab-pane fade show active" id="manual-tab-pane" role="tabpanel" aria-labelledby="manual-tab">
                <form @submit.prevent="register" class="needs-validation">
                  <div class="row g-3 mb-3">
                    <!-- Datos personales -->
                    <h5 class="col-12 fw-bold text-primary mb-0 border-bottom pb-2">
                      <i class="fas fa-user me-2"></i> Datos personales
                    </h5>
                    
                    <!-- Nombre completo -->
                    <div class="col-md-6">
                      <div class="form-floating uppercase">
                        <input 
                          v-model="form.name" 
                          type="text" 
                          id="name" 
                          class="form-control" 
                          placeholder="Nombre completo"
                          required 
                        />
                        <label for="name">Nombre completo</label>
                      </div>
                    </div>
                    
                    <!-- Número de cédula -->
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input 
                          v-model="form.idNumber" 
                          type="text" 
                          id="idNumber" 
                          class="form-control"
                          placeholder="Cédula"
                          required 
                        />
                        <label for="idNumber">Número de cédula</label>
                      </div>
                    </div>
                    
                    <!-- Teléfono -->
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input 
                          v-model="form.phone" 
                          type="tel" 
                          id="phone" 
                          class="form-control" 
                          placeholder="Teléfono"
                          required 
                        />
                        <label for="phone">Número de teléfono</label>
                      </div>
                    </div>
                    
                    <!-- Género -->
                    <div class="col-md-6">
                      <div class="form-floating">
                        <select 
                          v-model="form.gender" 
                          id="gender" 
                          class="form-select" 
                          required
                        >
                          <option value="" disabled selected>Seleccionar género</option>
                          <option value="Masculino">Masculino</option>
                          <option value="Femenino">Femenino</option>
                          <option value="Otro">Otro</option>
                        </select>
                        <label for="gender">Género</label>
                      </div>
                    </div>
                    
                    <!-- Fecha de nacimiento -->
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input 
                          v-model="form.birthDate" 
                          type="date" 
                          id="birthDate" 
                          class="form-control" 
                          required 
                        />
                        <label for="birthDate">Fecha de nacimiento</label>
                      </div>
                    </div>
                    
                    <!-- Email -->
                    <div class="col-md-6">
                      <div class="form-floating">
                        <input 
                          v-model="form.email" 
                          type="email" 
                          id="email" 
                          class="form-control" 
                          placeholder="Email"
                          required 
                        />
                        <label for="email">Correo electrónico</label>
                      </div>
                    </div>
                    
                    <!-- Campos específicos para técnicos -->
                    <template v-if="form.user_type === 'technician'">
                      <!-- Especialidad -->
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input 
                            v-model="form.specialty" 
                            type="text" 
                            id="specialty" 
                            class="form-control" 
                            placeholder="Especialidad"
                            required 
                          />
                          <label for="specialty">Especialidad técnica</label>
                        </div>
                      </div>
                      
                      <!-- Años de experiencia -->
                      <div class="col-md-6">
                        <div class="form-floating">
                          <input 
                            v-model.number="form.experience" 
                            type="number" 
                            id="experience" 
                            class="form-control" 
                            placeholder="Años"
                            min="0"
                            required 
                          />
                          <label for="experience">Años de experiencia</label>
                        </div>
                      </div>
                    </template>
                    
                    <!-- Datos de acceso -->
                    <h5 class="col-12 fw-bold text-primary mb-0 mt-2 border-bottom pb-2">
                      <i class="fas fa-lock me-2"></i> Datos de acceso
                    </h5>
                    
                    <!-- Contraseña -->
                    <div class="col-md-6">
                      <label for="password" class="form-label">Contraseña</label>
                      <div class="input-group">
                        <input 
                          v-model="form.password" 
                          :type="showPassword ? 'text' : 'password'" 
                          id="password" 
                          class="form-control"
                          placeholder="Crea una contraseña segura" 
                          @input="checkPasswordStrength"
                          required 
                        />
                        <button 
                          class="btn btn-outline-secondary" 
                          type="button" 
                          @click="showPassword = !showPassword"
                        >
                          <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </button>
                      </div>
                      
                      <!-- Indicador de fortaleza de contraseña -->
                      <div class="mt-2">
                        <div class="progress" style="height: 6px;">
                          <div 
                            class="progress-bar" 
                            :class="passwordStrengthClass" 
                            role="progressbar" 
                            :style="{ width: passwordStrength + '%' }" 
                            aria-valuenow="0" 
                            aria-valuemin="0" 
                            aria-valuemax="100"
                          ></div>
                        </div>
                        <small :class="passwordStrengthTextClass" class="d-block mt-1">
                          {{ passwordFeedback }}
                        </small>
                      </div>
                    </div>
                    
                    <!-- Confirmar contraseña -->
                    <div class="col-md-6">
                      <label for="confirmPassword" class="form-label">Confirmar contraseña</label>
                      <div class="input-group">
                        <input 
                          v-model="form.confirmPassword" 
                          :type="showConfirmPassword ? 'text' : 'password'" 
                          id="confirmPassword" 
                          class="form-control"
                          placeholder="Repite la contraseña" 
                          :class="{'is-invalid': passwordMismatch}"
                          required 
                        />
                        <button 
                          class="btn btn-outline-secondary" 
                          type="button" 
                          @click="showConfirmPassword = !showConfirmPassword"
                        >
                          <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </button>
                        <div class="invalid-feedback" v-if="passwordMismatch">
                          Las contraseñas no coinciden
                        </div>
                      </div>
                    </div>
                    
                    <!-- Requisitos de contraseña -->
                    <div class="col-12 mt-1">
                      <div class="d-flex flex-wrap gap-3 small">
                        <span :class="requirements.length ? 'text-success' : 'text-muted'">
                          <i :class="requirements.length ? 'fas fa-check-circle' : 'far fa-circle'"></i> 
                          Mín. 8 caracteres
                        </span>
                        <span :class="requirements.uppercase ? 'text-success' : 'text-muted'">
                          <i :class="requirements.uppercase ? 'fas fa-check-circle' : 'far fa-circle'"></i> 
                          Mayúscula
                        </span>
                        <span :class="requirements.lowercase ? 'text-success' : 'text-muted'">
                          <i :class="requirements.lowercase ? 'fas fa-check-circle' : 'far fa-circle'"></i> 
                          Minúscula
                        </span>
                        <span :class="requirements.number ? 'text-success' : 'text-muted'">
                          <i :class="requirements.number ? 'fas fa-check-circle' : 'far fa-circle'"></i> 
                          Número
                        </span>
                        <span :class="requirements.special ? 'text-success' : 'text-muted'">
                          <i :class="requirements.special ? 'fas fa-check-circle' : 'far fa-circle'"></i> 
                          Carácter especial
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Términos y condiciones -->
                  <div class="form-check mb-3">
                    <input v-model="acceptTerms" class="form-check-input" type="checkbox" id="acceptTerms" required>
                    <label class="form-check-label" for="acceptTerms">
                      Acepto los <a href="#" class="text-decoration-none">Términos y Condiciones</a> y la 
                      <a href="#" class="text-decoration-none">Política de Privacidad</a>
                    </label>
                  </div>
                  
                  <!-- Botón de registro con estado de carga -->
                  <button 
                    type="submit" 
                    class="btn btn-primary btn-lg w-100 mb-3"
                    :disabled="isLoading || !isValidForm"
                  >
                    <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                    {{ isLoading ? 'Procesando...' : 'Completar registro' }}
                  </button>
                </form>
              </div>
              
              <!-- Registro con redes sociales -->
              <div class="tab-pane fade" id="social-tab-pane" role="tabpanel" aria-labelledby="social-tab">
                <div class="alert alert-warning mb-4">
                  <i class="fas fa-exclamation-triangle me-2"></i>
                  Al continuar con una red social, aún deberás seleccionar si te registras como cliente o técnico.
                </div>
                
                <div class="text-center mb-4">
                  <p class="text-muted">Regístrate rápidamente con tu cuenta existente</p>
                </div>
                
                <div class="d-grid gap-3 mb-4">
                  <button @click="registerWithGoogle" class="btn btn-lg btn-outline-danger d-flex align-items-center justify-content-center" :disabled="!form.user_type">
                    <i class="fab fa-google me-3 fa-lg"></i> Continuar con Google
                  </button>
                  <button @click="registerWithMicrosoft" class="btn btn-lg btn-outline-primary d-flex align-items-center justify-content-center" :disabled="!form.user_type">
                    <i class="fab fa-microsoft me-3 fa-lg"></i> Continuar con Microsoft
                  </button>
                  <button @click="registerWithFacebook" class="btn btn-lg btn-outline-info d-flex align-items-center justify-content-center" :disabled="!form.user_type">
                    <i class="fab fa-facebook me-3 fa-lg"></i> Continuar con Facebook
                  </button>
                </div>
                
                <div class="text-center">
                  <small class="text-muted">
                    Al registrarte con una red social, aceptas nuestros 
                    <a href="#" class="text-decoration-none">Términos y Condiciones</a>
                  </small>
                </div>
              </div>
            </div>
            
            <div class="text-center mt-4">
              <p class="text-muted mb-0">
                ¿Ya tienes una cuenta?
                <router-link to="/login" class="fw-bold text-decoration-none">
                  Inicia sesión aquí
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

// Definir una configuración base para SweetAlert2
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer);
    toast.addEventListener('mouseleave', Swal.resumeTimer);
  }
});

export default {
  data() {
    return {
      form: {
        name: '',
        idNumber: '',
        phone: '',
        birthDate: '',
        gender: '',
        email: '',
        password: '',
        confirmPassword: '',
        user_type: '', // Nuevo campo para tipo de usuario
        // Campos específicos para técnicos
        specialty: '',
        experience: ''
      },
      showPassword: false,
      showConfirmPassword: false,
      passwordStrength: 0,
      passwordFeedback: 'Ingresa una contraseña',
      requirements: {
        length: false,
        uppercase: false,
        lowercase: false,
        number: false,
        special: false
      },
      acceptTerms: false,
      error: '',
      isLoading: false,
      showUserTypeError: false // Para mostrar error si no se selecciona tipo
    };
  },
  computed: {
    passwordStrengthClass() {
      if (this.passwordStrength < 30) return 'bg-danger';
      if (this.passwordStrength < 60) return 'bg-warning';
      if (this.passwordStrength < 80) return 'bg-info';
      return 'bg-success';
    },
    passwordStrengthTextClass() {
      if (this.passwordStrength < 30) return 'text-danger';
      if (this.passwordStrength < 60) return 'text-warning';
      if (this.passwordStrength < 80) return 'text-info';
      return 'text-success';
    },
    isValidPassword() {
      return this.requirements.length && 
             this.requirements.uppercase && 
             this.requirements.lowercase && 
             this.requirements.number && 
             this.requirements.special;
    },
    passwordMismatch() {
      return this.form.password && 
             this.form.confirmPassword && 
             this.form.password !== this.form.confirmPassword;
    },
    isValidForm() {
      let baseValidation = this.isValidPassword && 
             !this.passwordMismatch && 
             this.acceptTerms && 
             this.form.name && 
             this.form.idNumber && 
             this.form.phone && 
             this.form.birthDate && 
             this.form.gender && 
             this.form.email &&
             this.form.user_type; // Añadido tipo de usuario como requerido
      
      // Validación adicional para técnicos
      if (this.form.user_type === 'technician') {
        return baseValidation && this.form.specialty && this.form.experience !== '';
      }
      
      return baseValidation;
    }
  },
  methods: {
    selectUserType(type) {
      this.form.user_type = type;
      this.showUserTypeError = false;
    },
    
    checkPasswordStrength() {
      const password = this.form.password;
      
      // Verificar requisitos
      this.requirements.length = password.length >= 8;
      this.requirements.uppercase = /[A-Z]/.test(password);
      this.requirements.lowercase = /[a-z]/.test(password);
      this.requirements.number = /[0-9]/.test(password);
      this.requirements.special = /[^A-Za-z0-9]/.test(password);
      
      // Calcular fortaleza (0-100)
      let strength = 0;
      if (!password) {
        strength = 0;
      } else {
        // Criterios básicos
        if (this.requirements.length) strength += 20;
        if (this.requirements.uppercase) strength += 20;
        if (this.requirements.lowercase) strength += 20;
        if (this.requirements.number) strength += 20;
        if (this.requirements.special) strength += 20;
      }
      
      this.passwordStrength = strength;
      
      // Establecer mensaje de retroalimentación
      if (!password) {
        this.passwordFeedback = 'Ingresa una contraseña';
      } else if (strength < 40) {
        this.passwordFeedback = 'Contraseña débil';
      } else if (strength < 70) {
        this.passwordFeedback = 'Contraseña moderada';
      } else {
        this.passwordFeedback = 'Contraseña fuerte';
      }
    },
    
    async register() {
      // Validar tipo de usuario
      if (!this.form.user_type) {
        this.showUserTypeError = true;
        this.error = 'Por favor, selecciona si te registras como Cliente o Técnico';
        return;
      }

      if (this.passwordMismatch) {
        this.error = 'Las contraseñas no coinciden';
        return;
      }
      
      if (!this.isValidPassword) {
        this.error = 'La contraseña no cumple con los requisitos mínimos de seguridad';
        return;
      }
      
      this.isLoading = true;
      this.error = '';
      
      try {
        // Crear objeto base de datos para enviar
        const registerData = {
          name: this.form.name,
          idNumber: this.form.idNumber,
          phone: this.form.phone,
          birthDate: this.form.birthDate,
          gender: this.form.gender,
          email: this.form.email,
          password: this.form.password,
          password_confirmation: this.form.confirmPassword,
          user_type: this.form.user_type // Añadir tipo de usuario
        };
        
        // Añadir campos específicos para técnicos
        if (this.form.user_type === 'technician') {
          registerData.specialty = this.form.specialty;
          registerData.experience = this.form.experience;
        }

        const response = await axios.post('/register', registerData);

        // Guardar token
        if (response.data.token) {
          localStorage.setItem('token', response.data.token);
          localStorage.setItem('user_type', this.form.user_type);
          
          // Usar SweetAlert2 para notificación de éxito
          Swal.fire({
            icon: 'success',
            title: '¡Registro exitoso!',
            text: `Tu cuenta de ${this.form.user_type === 'client' ? 'Cliente' : 'Técnico'} ha sido creada correctamente`,
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            // Redireccionar según el tipo de usuario
            if (this.form.user_type === 'technician') {
              this.$router.push('/profile');
            } else {
              this.$router.push('/dashboard');
            }
          });
        } else {
          this.error = 'No se recibió un token válido del servidor';
        }
      } catch (error) {
        console.error('Error al registrarse:', error);
        
        if (error.response?.data?.message) {
          this.error = error.response.data.message;
        } else if (error.response?.data?.errors) {
          // Procesar errores de validación de Laravel
          const errorMsgs = [];
          const errors = error.response.data.errors;
          
          for (const field in errors) {
            errorMsgs.push(errors[field][0]);
          }
          
          this.error = errorMsgs.join(' ');
        } else {
          this.error = 'Error al registrar. Por favor, intenta nuevamente.';
        }
      } finally {
        this.isLoading = false;
      }
    },
    
    registerWithGoogle() {
      if (!this.form.user_type) {
        this.showUserTypeError = true;
        this.error = 'Selecciona un tipo de usuario antes de continuar con Google';
        return;
      }
      
      // Pasar el tipo de usuario como parámetro de consulta
      window.location.href = `/auth/google?user_type=${this.form.user_type}`;
    },
    
    registerWithMicrosoft() {
      if (!this.form.user_type) {
        this.showUserTypeError = true;
        this.error = 'Selecciona un tipo de usuario antes de continuar con Microsoft';
        return;
      }
      
      window.location.href = `/auth/microsoft?user_type=${this.form.user_type}`;
    },
    
    registerWithFacebook() {
      if (!this.form.user_type) {
        this.showUserTypeError = true;
        this.error = 'Selecciona un tipo de usuario antes de continuar con Facebook';
        return;
      }
      
      window.location.href = `/auth/facebook?user_type=${this.form.user_type}`;
    },
    
    // Método actualizado para usar SweetAlert2
    showToast(title, message, type = 'info') {
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
      
      Toast.fire({
        icon: type,
        title
      });
    }
  },
  mounted() {
    // Inicializar pestañas de Bootstrap
    if (typeof bootstrap !== 'undefined') {
      const triggerTabList = document.querySelectorAll('#registerTabs button');
      triggerTabList.forEach(triggerEl => {
        new bootstrap.Tab(triggerEl);
      });
    }
  }
};
</script>

<style scoped>
/* Estilo para el fondo de la columna izquierda */
.bg-primary {
  background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%) !important;
}

/* Mejorar espaciado y diseño responsivo */
.form-floating {
  margin-bottom: 0.5rem;
}

.form-floating > label {
  padding-left: 1rem;
}

/* Estilos para las tarjetas de selección de tipo de usuario */
.cursor-pointer {
  cursor: pointer;
}

.user-type-card {
  transition: all 0.3s ease;
  border: 2px solid transparent !important;
}

.user-type-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.user-type-card.selected {
  border-color: #0d6efd !important;
  background-color: rgba(13, 110, 253, 0.05) !important;
}

.user-type-card.selected[class*="technician"] {
  border-color: #198754 !important;
  background-color: rgba(25, 135, 84, 0.05) !important;
}

/* Efecto hover para links */
a:hover {
  color: var(--bs-primary-hover) !important;
}

/* Animación de entrada para card */
.card {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Estilo para pestañas de registro */
#registerTabs .nav-link {
  color: #6c757d;
  border: 1px solid transparent;
  border-radius: 8px;
  padding: 0.75rem 1rem;
  margin: 0 0.25rem;
  transition: all 0.3s ease;
}

#registerTabs .nav-link.active {
  color: #0d6efd;
  background-color: rgba(13, 110, 253, 0.1);
  border: 1px solid rgba(13, 110, 253, 0.2);
}

/* Transiciones suaves para todos los elementos */
.btn, input, select {
  transition: all 0.2s ease-in-out;
}

/* Estilo específico para móviles */
@media (max-width: 768px) {
  #registerTabs .nav-link {
    padding: 0.5rem;
    font-size: 0.9rem;
  }
  
  .card {
    border-radius: 0;
    box-shadow: none !important;
  }
}
</style>