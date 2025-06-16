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
              <div class="rounded-circle bg-white p-2 d-inline-flex justify-content-center align-items-center me-3"
                style="width: 40px; height: 40px;">
                <i class="fas fa-check text-primary"></i>
              </div>
              <span class="fw-bold">Servicio técnico especializado</span>
            </div>
            <div class="d-flex align-items-center mb-3">
              <div class="rounded-circle bg-white p-2 d-inline-flex justify-content-center align-items-center me-3"
                style="width: 40px; height: 40px;">
                <i class="fas fa-check text-primary"></i>
              </div>
              <span class="fw-bold">Mantenimiento preventivo y correctivo</span>
            </div>
            <div class="d-flex align-items-center">
              <div class="rounded-circle bg-white p-2 d-inline-flex justify-content-center align-items-center me-3"
                style="width: 40px; height: 40px;">
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

            <!-- Formulario de registro manual -->
            <form @submit.prevent="register" class="needs-validation">
              <div class="row g-3 mb-3">
                <!-- Datos personales -->
                <h5 class="col-12 fw-bold text-primary mb-0 border-bottom pb-2">
                  <i class="fas fa-user me-2"></i> Datos personales
                </h5>

                <!-- Tipo de usuario -->
                <div class="col-12">
                  <div class="form-floating">
                    <select v-model="form.user_type" id="user_type" class="form-select" required>
                      <option value="" disabled selected>Seleccionar tipo de usuario</option>
                      <option value="client">Cliente - Solicito servicios de mantenimiento</option>
                      <option value="technician">Técnico - Ofrezco servicios de mantenimiento</option>
                    </select>
                    <label for="user_type">¿Cómo te registras?</label>
                  </div>
                </div>

                <!-- Nombre completo -->
                <div class="col-md-6">
                  <div class="form-floating uppercase">
                    <input v-model="form.name" type="text" id="name" class="form-control"
                      placeholder="Nombre completo" required />
                    <label for="name">Nombre completo</label>
                  </div>
                </div>

                <!-- Número de cédula -->
                <div class="col-md-6">
                  <div class="form-floating">
                    <input v-model="form.idNumber" type="text" id="idNumber" class="form-control"
                      placeholder="Cédula" required />
                    <label for="idNumber">Número de cédula</label>
                  </div>
                </div>

                <!-- Teléfono -->
                <div class="col-md-6">
                  <div class="form-floating">
                    <input v-model="form.phone" type="tel" id="phone" class="form-control" placeholder="Teléfono"
                      required />
                    <label for="phone">Número de teléfono</label>
                  </div>
                </div>

                <!-- Género -->
                <div class="col-md-6">
                  <div class="form-floating">
                    <select v-model="form.gender" id="gender" class="form-select" required>
                      <option value="" disabled selected>Seleccionar género</option>
                      <option value="male">Masculino</option>
                      <option value="female">Femenino</option>
                      <option value="other">Otro</option>
                    </select>
                    <label for="gender">Género</label>
                  </div>
                </div>

                <!-- Fecha de nacimiento -->
                <div class="col-md-6">
                  <div class="form-floating">
                    <input v-model="form.birthDate" type="date" id="birthDate" class="form-control" required />
                    <label for="birthDate">Fecha de nacimiento</label>
                  </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                  <div class="form-floating">
                    <input v-model="form.email" type="email" id="email" class="form-control" placeholder="Email"
                      required />
                    <label for="email">Correo electrónico</label>
                  </div>
                </div>

                <!-- Contraseña -->
                <div class="col-md-6">
                  <div class="form-floating position-relative">
                    <input v-model="form.password" :type="showPassword ? 'text' : 'password'" id="password"
                      class="form-control" placeholder="Contraseña" required @input="checkPasswordStrength" />
                    <label for="password">Contraseña</label>
                    <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-3"
                      @click="showPassword = !showPassword" style="z-index: 10;">
                      <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                    </button>
                  </div>
                  <!-- Indicador de fortaleza de contraseña -->
                  <div class="progress mt-2" style="height: 4px;">
                    <div class="progress-bar" :class="passwordStrengthClass" :style="`width: ${passwordStrength}%`">
                    </div>
                  </div>
                  <small :class="passwordStrengthTextClass">{{ passwordFeedback }}</small>
                </div>

                <!-- Confirmar contraseña -->
                <div class="col-md-6">
                  <div class="form-floating position-relative">
                    <input v-model="form.confirmPassword" :type="showConfirmPassword ? 'text' : 'password'"
                      id="confirmPassword" class="form-control" placeholder="Confirmar contraseña" required />
                    <label for="confirmPassword">Confirmar contraseña</label>
                    <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-3"
                      @click="showConfirmPassword = !showConfirmPassword" style="z-index: 10;">
                      <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                    </button>
                  </div>
                  <small v-if="passwordMismatch" class="text-danger">Las contraseñas no coinciden</small>
                </div>

                <!-- Requisitos de contraseña -->
                <div class="col-12">
                  <div class="card bg-light">
                    <div class="card-body p-3">
                      <h6 class="card-title mb-2">
                        <i class="fas fa-shield-alt me-2"></i>Requisitos de contraseña
                      </h6>
                      <div class="row text-sm">
                        <div class="col-md-6">
                          <div class="d-flex align-items-center mb-1">
                            <i :class="requirements.length ? 'fas fa-check text-success' : 'fas fa-times text-danger'"
                              class="me-2"></i>
                            <span>Mínimo 8 caracteres</span>
                          </div>
                          <div class="d-flex align-items-center mb-1">
                            <i :class="requirements.uppercase ? 'fas fa-check text-success' : 'fas fa-times text-danger'"
                              class="me-2"></i>
                            <span>Una letra mayúscula</span>
                          </div>
                          <div class="d-flex align-items-center mb-1">
                            <i :class="requirements.lowercase ? 'fas fa-check text-success' : 'fas fa-times text-danger'"
                              class="me-2"></i>
                            <span>Una letra minúscula</span>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="d-flex align-items-center mb-1">
                            <i :class="requirements.number ? 'fas fa-check text-success' : 'fas fa-times text-danger'"
                              class="me-2"></i>
                            <span>Un número</span>
                          </div>
                          <div class="d-flex align-items-center mb-1">
                            <i :class="requirements.special ? 'fas fa-check text-success' : 'fas fa-times text-danger'"
                              class="me-2"></i>
                            <span>Un carácter especial</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Términos y condiciones -->
                <div class="col-12">
                  <div class="form-check">
                    <input v-model="acceptTerms" class="form-check-input" type="checkbox" id="acceptTerms" required>
                    <label class="form-check-label" for="acceptTerms">
                      Acepto los
                      <a href="#" class="text-decoration-none fw-bold">Términos y Condiciones</a>
                      y la
                      <a href="#" class="text-decoration-none fw-bold">Política de Privacidad</a>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Botón de registro -->
              <div class="d-grid gap-2 mb-3">
                <button type="submit" class="btn btn-primary btn-lg" :disabled="!isValidForm || isLoading">
                  <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status"
                    aria-hidden="true"></span>
                  <i v-else class="fas fa-user-plus me-2"></i>
                  {{ isLoading ? 'Registrando...' : 'Crear cuenta' }}
                </button>
              </div>
            </form>

            <!-- Divisor -->
            <div class="text-center mb-4">
              <div class="position-relative">
                <hr class="my-4">
                <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted">
                  o regístrate con
                </span>
              </div>
            </div>

            <!-- Registro con Google -->
            <div class="d-grid mb-4">
              <button @click="registerWithGoogle"
                class="btn btn-lg btn-outline-danger d-flex align-items-center justify-content-center"
                :disabled="!form.user_type">
                <i class="fab fa-google me-3 fa-lg"></i> Continuar con Google
              </button>
            </div>

            <div class="text-center mt-4">
              <p class="text-muted mb-0">
                ¿Ya tienes una cuenta?
                <router-link to="/login" class="fw-bold text-decoration-none">
                  Inicia sesión 
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
        user_type: '',
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
      showUserTypeError: false
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
      return this.isValidPassword &&
        !this.passwordMismatch &&
        this.acceptTerms &&
        this.form.name &&
        this.form.idNumber &&
        this.form.phone &&
        this.form.birthDate &&
        this.form.gender &&
        this.form.email &&
        this.form.user_type;
    }
  },
  methods: {
    selectUserType(type) {
      this.form.user_type = type;
      this.showUserTypeError = false;
    },

    checkPasswordStrength() {
      const password = this.form.password;

      this.requirements.length = password.length >= 8;
      this.requirements.uppercase = /[A-Z]/.test(password);
      this.requirements.lowercase = /[a-z]/.test(password);
      this.requirements.number = /[0-9]/.test(password);
      this.requirements.special = /[^A-Za-z0-9]/.test(password);

      let strength = 0;
      if (!password) {
        strength = 0;
      } else {
        if (this.requirements.length) strength += 20;
        if (this.requirements.uppercase) strength += 20;
        if (this.requirements.lowercase) strength += 20;
        if (this.requirements.number) strength += 20;
        if (this.requirements.special) strength += 20;
      }

      this.passwordStrength = strength;

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
        const registerData = {
          name: this.form.name,
          idNumber: this.form.idNumber,
          phone: this.form.phone,
          birthDate: this.form.birthDate,
          gender: this.form.gender,
          email: this.form.email,
          password: this.form.password,
          password_confirmation: this.form.confirmPassword,
          user_type: this.form.user_type
        };

        console.log('Enviando datos:', registerData);

        const response = await axios.post('/register', registerData, {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          }
        });

        console.log('Respuesta del servidor:', response.data);

        if (response.data.success) {
            // MANEJAR CASO DE VERIFICACIÓN REQUERIDA
            if (response.data.data.requires_verification) {
                await Swal.fire({
                    icon: 'info',
                    title: '¡Registro casi completo!',
                    html: `
                        <div class="text-center">
                            <p>Tu cuenta ha sido creada exitosamente.</p>
                            <p><strong>Ahora debes verificar tu email:</strong></p>
                            <p class="text-primary">${this.form.email}</p>
                            <p class="text-muted small">Revisa tu bandeja de entrada y haz clic en el enlace de verificación.</p>
                        </div>
                    `,
                    confirmButtonText: 'Entendido',
                    showCancelButton: true,
                    cancelButtonText: 'Reenviar correo'
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        this.resendVerificationEmail();
                    }
                });

                // Redirigir a login con mensaje
                this.$router.push({
                    path: '/login',
                    query: { message: 'verify_email', email: this.form.email }
                });
            } else {
                // Caso normal (si no requiere verificación)
                const userData = response.data.data;
                
                localStorage.setItem('token', userData.token);
                localStorage.setItem('user_type', userData.user_type);

                await Swal.fire({
                    icon: 'success',
                    title: '¡Registro exitoso!',
                    text: `Tu cuenta de ${this.form.user_type === 'client' ? 'Cliente' : 'Técnico'} ha sido creada correctamente`,
                    timer: 2000,
                    showConfirmButton: false
                });

                if (this.form.user_type === 'technician') {
                    this.$router.push('/profile');
                } else {
                    this.$router.push('/dashboard');
                }
            }
        } else {
            this.error = 'Respuesta del servidor inválida';
            console.error('Respuesta inesperada:', response.data);
        }

      } catch (error) {
        console.log('Error completo:', error);
        console.log('Respuesta del error:', error.response);
        
        if (error.response && error.response.data) {
            console.log('Datos del error:', error.response.data);
            console.log('Errores de validación:', error.response.data.errors);
            
            // Agregar esta línea para ver el mensaje específico del error
            if (error.response.data.errors.idNumber) {
                console.log('Mensaje específico de idNumber:', error.response.data.errors.idNumber[0]);
            }
            
            // Ver todos los errores de forma detallada
            Object.keys(error.response.data.errors).forEach(key => {
                console.log(`Error ${key}:`, error.response.data.errors[key]);
            });
        }
        
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors;
          this.error = 'Por favor corrige los errores en el formulario';
        } else if (error.response?.data?.message) {
          this.error = error.response.data.message;
        } else if (error.response?.status === 422) {
          this.error = 'Error de validación en los datos enviados';
        } else {
          this.error = 'Error del servidor. Por favor intenta nuevamente.';
        }

        Toast.fire({
          icon: 'error',
          title: this.error
        });
      } finally {
        this.isLoading = false;
      }
    },

    async resendVerificationEmail() {
        try {
            const response = await axios.post('/email/resend', {
                email: this.form.email
            });

            if (response.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Correo reenviado',
                    text: 'Hemos reenviado el email de verificación a tu correo.',
                    timer: 3000
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo reenviar el correo de verificación.'
            });
        }
    },

    registerWithGoogle() {
      if (!this.form.user_type) {
        this.showUserTypeError = true;
        this.error = 'Selecciona un tipo de usuario antes de continuar con Google';
        return;
      }

      window.location.href = `/auth/google?user_type=${this.form.user_type}`;
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

.form-floating>label {
  padding-left: 1rem;
}

/* Animación de entrada para card */
.card {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Transiciones suaves para todos los elementos */
.btn,
input,
select {
  transition: all 0.2s ease-in-out;
}

/* Estilo específico para móviles */
@media (max-width: 768px) {
  .card {
    border-radius: 0;
    box-shadow: none !important;
  }
}
</style>