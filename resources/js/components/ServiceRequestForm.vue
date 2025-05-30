<template>
  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <!-- Barra de navegación del formulario -->
        <div class="card shadow-sm mb-4">
          <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
              <h4 class="m-0 text-primary">
                <i class="fas fa-tools me-2"></i>Solicitud de Servicio
              </h4>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb m-0">
                  <li class="breadcrumb-item"><router-link to="/dashboard">Inicio</router-link></li>
                  <li class="breadcrumb-item active" aria-current="page">Solicitud de Servicio</li>
                </ol>
              </nav>
            </div>
          </div>
        </div>
        
        <div class="card shadow">
          <div class="card-header bg-primary bg-opacity-10 py-3">
            <h5 class="mb-0 text-primary">
              <i class="fas fa-clipboard-list me-2"></i>Nueva Solicitud de Servicio Técnico
            </h5>
          </div>
          
          <div class="card-body p-4">
            <!-- Alerta de errores con animación mejorada -->
            <div v-if="error" class="alert alert-danger d-flex align-items-center fade show mb-4" role="alert">
              <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
              <div>{{ error }}</div>
              <button type="button" class="btn-close ms-auto" @click="error = ''"></button>
            </div>
            
            <!-- Panel de información mejorado -->
            <div class="alert alert-info border-start border-info border-4 mb-4">
              <div class="d-flex">
                <div class="me-3">
                  <i class="fas fa-info-circle fa-2x text-info"></i>
                </div>
                <div>
                  <h5 class="alert-heading">Información importante</h5>
                  <p class="mb-0">El servicio será asignado a un técnico disponible con experiencia en tu tipo de solicitud. 
                  Recibirás una <strong>confirmación por correo electrónico</strong> cuando tu solicitud sea procesada.</p>
                </div>
              </div>
            </div>
            
            <form @submit.prevent="submitRequest" class="needs-validation">
              <!-- Dividir el formulario en secciones más claras -->
              <div class="form-section mb-4">
                <h6 class="form-section-title"><i class="fas fa-tasks me-2"></i>Detalles del Servicio</h6>
                
                <div class="row g-3">
                  <!-- Tipo de Servicio -->
                  <div class="col-md-6">
                    <label for="type" class="form-label fw-bold">Tipo de Servicio</label>
                    <select v-model="form.type" id="type" class="form-select" required>
                      <option value="" disabled>Seleccionar tipo de servicio</option>
                      <option value="correctivo">Mantenimiento Correctivo</option>
                      <option value="preventivo">Mantenimiento Preventivo</option>
                      <option value="instalacion">Instalación de Software</option>
                      <option value="limpieza">Limpieza de Equipo</option>
                      <option value="reparacion">Reparación de Hardware</option>
                      <option value="asesoria">Asesoría Técnica</option>
                      <option value="otro">Otro (especificar)</option>
                    </select>
                    <div class="form-text" v-if="form.type">
                      <span v-if="form.type === 'correctivo'"><i class="fas fa-wrench me-1 text-secondary"></i>Solución de problemas existentes</span>
                      <span v-else-if="form.type === 'preventivo'"><i class="fas fa-shield-alt me-1 text-secondary"></i>Prevención de fallos futuros</span>
                      <span v-else-if="form.type === 'instalacion'"><i class="fas fa-download me-1 text-secondary"></i>Instalación de programas</span>
                    </div>
                  </div>
                  
                  <!-- Urgencia -->
                  <div class="col-md-6">
                    <label for="urgency" class="form-label fw-bold">Nivel de Urgencia</label>
                    <select v-model="form.urgency" id="urgency" class="form-select" required>
                      <option value="baja">Baja (1-2 semanas)</option>
                      <option value="media">Media (3-5 días)</option>
                      <option value="alta">Alta (24-48 horas)</option>
                      <option value="critica">Crítica (Inmediata)</option>
                    </select>
                    <div class="form-text text-danger" v-if="form.urgency === 'critica'">
                      <i class="fas fa-exclamation-circle me-1"></i>Los servicios de urgencia crítica pueden tener costos adicionales
                    </div>
                  </div>
                  
                  <!-- Especificar si seleccionó "otro" -->
                  <transition name="fade">
                    <div class="col-12" v-if="form.type === 'otro'">
                      <label for="other_type" class="form-label fw-bold">Especifique el tipo de servicio</label>
                      <input
                        v-model="form.other_type"
                        type="text"
                        id="other_type"
                        class="form-control"
                        placeholder="Especifique el servicio que necesita"
                        required
                      />
                    </div>
                  </transition>
                  
                  <!-- Descripción -->
                  <div class="col-12">
                    <label for="description" class="form-label fw-bold">Descripción del Problema</label>
                    <textarea
                      v-model="form.description"
                      id="description"
                      class="form-control"
                      rows="4"
                      placeholder="Describe detalladamente el problema que presenta el equipo"
                      required
                    ></textarea>
                    <div class="d-flex justify-content-between form-text">
                      <span>{{ form.description.length ? 'Caracteres: ' + form.description.length : 'Ingresa una descripción detallada' }}</span>
                      <span :class="{'text-danger': form.description.length > 450, 'text-warning': form.description.length > 400 && form.description.length <= 450}">
                        {{ 500 - form.description.length }} caracteres restantes
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Información del Equipo -->
              <div class="form-section mb-4">
                <h6 class="form-section-title"><i class="fas fa-laptop me-2"></i>Información del Equipo</h6>
                
                <div class="row g-3">
                  <!-- Equipo -->
                  <div class="col-md-6">
                    <label for="equipment_type" class="form-label fw-bold">Tipo de Equipo</label>
                    <select v-model="form.equipment_type" id="equipment_type" class="form-select" required>
                      <option value="" disabled>Seleccionar tipo de equipo</option>
                      <option value="desktop">PC de Escritorio</option>
                      <option value="laptop">Laptop / Portátil</option>
                      <option value="server">Servidor</option>
                      <option value="printer">Impresora</option>
                      <option value="network">Equipo de Red</option>
                      <option value="other">Otro</option>
                    </select>
                  </div>
                  
                  <!-- Marca -->
                  <div class="col-md-6">
                    <label for="brand" class="form-label fw-bold">Marca y Modelo</label>
                    <input
                      v-model="form.brand"
                      type="text"
                      id="brand"
                      class="form-control"
                      placeholder="Ej: HP Pavilion, Dell Inspiron"
                      required
                    />
                  </div>
                </div>
              </div>
              
              <!-- Información de Ubicación -->
              <div class="form-section mb-4">
                <h6 class="form-section-title"><i class="fas fa-map-marker-alt me-2"></i>Ubicación del Servicio</h6>
                
                <div class="row g-3">
                  <!-- Dirección -->
                  <div class="col-12">
                    <label for="address" class="form-label fw-bold">Dirección del Servicio</label>
                    <input
                      v-model="form.address"
                      id="address"
                      type="text"
                      class="form-control"
                      placeholder="Dirección completa donde se realizará el servicio"
                      required
                    />
                  </div>
                  
                  <!-- Ubicación adicional -->
                  <div class="col-md-6">
                    <label for="city" class="form-label fw-bold">Ciudad</label>
                    <input
                      v-model="form.city"
                      type="text"
                      id="city"
                      class="form-control"
                      required
                    />
                  </div>
                  
                  <!-- Código postal -->
                  <div class="col-md-6">
                    <label for="postal_code" class="form-label fw-bold">Código Postal</label>
                    <input
                      v-model="form.postal_code"
                      type="text"
                      id="postal_code"
                      class="form-control"
                    />
                  </div>
                </div>
              </div>
              
              <!-- Programación -->
              <div class="form-section mb-4">
                <h6 class="form-section-title"><i class="fas fa-calendar-alt me-2"></i>Programación del Servicio</h6>
                
                <div class="row g-3">
                  <!-- Preferencia de Horario -->
                  <div class="col-md-6">
                    <label for="preferred_date" class="form-label fw-bold">Fecha Preferida</label>
                    <input
                      v-model="form.preferred_date"
                      type="date"
                      id="preferred_date"
                      class="form-control"
                      :min="minDate"
                    />
                  </div>
                  
                  <div class="col-md-6">
                    <label for="preferred_time" class="form-label fw-bold">Horario Preferido</label>
                    <select v-model="form.preferred_time" id="preferred_time" class="form-select">
                      <option value="">Sin preferencia específica</option>
                      <option value="morning">Mañana (8:00 - 12:00)</option>
                      <option value="afternoon">Tarde (12:00 - 17:00)</option>
                      <option value="evening">Noche (17:00 - 20:00)</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <!-- Información Adicional -->
              <div class="form-section mb-4">
                <h6 class="form-section-title"><i class="fas fa-file-alt me-2"></i>Información Adicional</h6>
                
                <div class="row g-3">
                  <!-- Fotos del problema (opcional) -->
                  <div class="col-12">
                    <label for="photos" class="form-label fw-bold">
                      <i class="fas fa-camera me-1"></i>Fotos del Problema <span class="text-muted">(opcional)</span>
                    </label>
                    <div class="input-group mb-2">
                      <input 
                        type="file"
                        id="photos"
                        class="form-control"
                        @change="handleFileUpload"
                        multiple
                        accept="image/*"
                      />
                      <button 
                        class="btn btn-outline-secondary" 
                        type="button"
                        onclick="document.getElementById('photos').value = ''"
                        v-if="previewImages.length > 0"
                      >
                        <i class="fas fa-times"></i> Limpiar
                      </button>
                    </div>
                    <div class="form-text">
                      <i class="fas fa-info-circle me-1"></i>Puede subir hasta 3 imágenes (máx. 5MB cada una)
                    </div>
                    
                    <!-- Previsualización de imágenes -->
                    <div v-if="previewImages.length > 0" class="d-flex flex-wrap gap-2 mt-3">
                      <div v-for="(img, index) in previewImages" :key="index" class="position-relative image-preview">
                        <img :src="img" alt="Vista previa" class="img-thumbnail" style="height: 100px; width: 100px; object-fit: cover;">
                        <button 
                          type="button" 
                          class="btn btn-sm btn-danger position-absolute top-0 end-0 btn-close-img"
                          @click="removeImage(index)"
                        >
                          <i class="fas fa-times"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Comentarios adicionales -->
                  <div class="col-12">
                    <label for="comments" class="form-label fw-bold">
                      <i class="fas fa-comment-alt me-1"></i>Comentarios Adicionales <span class="text-muted">(opcional)</span>
                    </label>
                    <textarea
                      v-model="form.comments"
                      id="comments"
                      class="form-control"
                      rows="2"
                      placeholder="Cualquier detalle adicional que considere importante"
                    ></textarea>
                  </div>
                </div>
              </div>
              
              <!-- Términos y condiciones -->
              <div class="card bg-light mb-4">
                <div class="card-body py-3">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="terms" v-model="form.terms" required>
                    <label class="form-check-label" for="terms">
                      He leído y acepto los <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">términos y condiciones</a> del servicio
                    </label>
                  </div>
                </div>
              </div>
              
              <div class="d-flex flex-column flex-md-row gap-2 mt-4">
                <button 
                  type="submit" 
                  class="btn btn-primary py-2 flex-grow-1"
                  :disabled="isSubmitting || !formValid"
                >
                  <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                  <i v-else class="fas fa-paper-plane me-2"></i>
                  {{ isSubmitting ? 'Enviando solicitud...' : 'Enviar Solicitud de Servicio' }}
                </button>
                <button 
                  type="button" 
                  class="btn btn-outline-secondary" 
                  @click="$router.push('/dashboard')"
                >
                  <i class="fas fa-times me-1"></i> Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modal de Términos y Condiciones -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-light">
            <h5 class="modal-title" id="termsModalLabel">
              <i class="fas fa-file-contract me-2"></i>
              Términos y Condiciones del Servicio
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-4">
              <h6 class="fw-bold"><i class="fas fa-clipboard-check me-2 text-primary"></i>1. Alcance del Servicio</h6>
              <p>El servicio incluye únicamente las tareas especificadas en la solicitud. Cualquier servicio adicional deberá ser acordado y cotizado por separado.</p>
            </div>
            
            <div class="mb-4">
              <h6 class="fw-bold"><i class="fas fa-shield-alt me-2 text-primary"></i>2. Garantía</h6>
              <p>Los servicios de mantenimiento cuentan con una garantía de 30 días hábiles desde la fecha de entrega del equipo.</p>
            </div>
            
            <div class="mb-4">
              <h6 class="fw-bold"><i class="fas fa-exclamation-triangle me-2 text-primary"></i>3. Responsabilidad</h6>
              <p>No nos hacemos responsables por la pérdida de datos. Se recomienda realizar un respaldo antes de solicitar cualquier servicio.</p>
            </div>
            
            <div>
              <h6 class="fw-bold"><i class="fas fa-calendar-times me-2 text-primary"></i>4. Cancelaciones</h6>
              <p class="mb-0">Las cancelaciones deben realizarse con un mínimo de 24 horas de anticipación, de lo contrario podría aplicarse un cargo por cancelación.</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal" @click="acceptTerms">
              <i class="fas fa-check me-1"></i> Aceptar términos
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// En tu script, asegúrate de que las importaciones sean correctas
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  name: 'ServiceRequestForm',
  data() {
    return {
      form: {
        type: '',
        other_type: '',
        description: '',
        urgency: 'media',
        equipment_type: '',
        brand: '',
        address: '',
        city: '',
        postal_code: '',
        preferred_date: '',
        preferred_time: '',
        comments: '',
        terms: false
      },
      isSubmitting: false,
      error: '',
      uploadedFiles: [],
      previewImages: []
    };
  },
  computed: {
    minDate() {
      const today = new Date();
      return today.toISOString().split('T')[0];
    },
    formValid() {
      // Validación básica del formulario
      return this.form.type && 
             this.form.description && 
             this.form.equipment_type && 
             this.form.brand && 
             this.form.address && 
             this.form.city && 
             this.form.terms;
    }
  },
  methods: {
    acceptTerms() {
      this.form.terms = true;
    },
    
    handleFileUpload(event) {
      const files = event.target.files;
      
      if (files.length + this.previewImages.length > 3) {
        this.showError("Máximo 3 imágenes permitidas");
        return;
      }
      
      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        
        // Validar tamaño (5MB máximo)
        if (file.size > 5 * 1024 * 1024) {
          this.showError(`La imagen ${file.name} excede el tamaño máximo permitido (5MB)`);
          continue;
        }
        
        // Guardar archivo para envío
        this.uploadedFiles.push(file);
        
        // Crear vista previa
        const reader = new FileReader();
        reader.onload = (e) => {
          this.previewImages.push(e.target.result);
        };
        reader.readAsDataURL(file);
      }
    },
    
    removeImage(index) {
      this.previewImages.splice(index, 1);
      this.uploadedFiles.splice(index, 1);
    },
    
    showError(message) {
      this.error = message;
      
      // Desplazar al principio para mostrar el error
      window.scrollTo({ top: 0, behavior: 'smooth' });
      
      // Quitar el error después de un tiempo
      setTimeout(() => {
        if (this.error === message) {
          this.error = '';
        }
      }, 6000);
    },
    
    async submitRequest() {
      if (!this.form.terms) {
        this.showError("Debes aceptar los términos y condiciones para continuar");
        
        // Mostrar el modal de términos
        new bootstrap.Modal(document.getElementById('termsModal')).show();
        return;
      }
      
      this.isSubmitting = true;
      this.error = '';
      
      try {
        // Crear FormData para manejar archivos y datos
        const formData = new FormData();
        
        // Agregar todos los campos del formulario
        for (const key in this.form) {
          if (key !== 'terms') {
            formData.append(key, this.form[key]);
          }
        }
        
        // Si el tipo es "otro", usar el valor proporcionado
        if (this.form.type === 'otro' && this.form.other_type) {
          formData.append('service_type_detail', this.form.other_type);
        }
        
        // Agregar archivos si existen
        this.uploadedFiles.forEach((file, index) => {
          formData.append(`photos[${index}]`, file);
        });
        
        // Usar api/ manualmente para asegurar consistencia
        const response = await axios.post('/api/service-requests', formData, {
          headers: { 
            'Content-Type': 'multipart/form-data',
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        });
        
        // Mostrar mensaje de éxito usando SweetAlert2 con mejoras visuales
        Swal.fire({
          icon: 'success',
          title: '¡Solicitud enviada!',
          text: 'Tu solicitud ha sido recibida con el número de referencia ' + 
                (response.data.request_number || 'SR-' + Math.floor(Math.random() * 10000)),
          footer: 'Recibirás una confirmación por correo electrónico',
          confirmButtonText: '<i class="fas fa-clipboard-list me-2"></i>Ver mis solicitudes',
          showCancelButton: true,
          cancelButtonText: '<i class="fas fa-home me-2"></i>Ir al inicio',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) {
            this.$router.push('/my-requests');
          } else {
            this.$router.push('/dashboard');
          }
        });
        
      } catch (error) {
        console.error('Error al enviar solicitud:', error);
        
        if (error.response?.data?.message) {
          this.showError(error.response.data.message);
        } else if (error.response?.data?.errors) {
          const errorMessages = Object.values(error.response.data.errors).flat();
          this.showError(errorMessages.join('. '));
        } else {
          this.showError('Error al enviar la solicitud. Por favor, intente nuevamente.');
        }
        
      } finally {
        this.isSubmitting = false;
      }
    }
  },
  mounted() {
    // Inicializar la fecha preferida con la fecha actual + 1 día
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    this.form.preferred_date = tomorrow.toISOString().split('T')[0];
    
    // Inicializar tooltips de Bootstrap
    if (typeof bootstrap !== 'undefined') {
      const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      tooltips.forEach(tooltip => new bootstrap.Tooltip(tooltip));
    }
  }
};
</script>

<style scoped>
.form-section {
  border-radius: 8px;
  padding: 1.25rem;
  background-color: #f8f9fa;
  border-left: 4px solid #0d6efd;
}

.form-section-title {
  color: #0d6efd;
  font-weight: 600;
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.form-label {
  margin-bottom: 0.3rem;
}

/* Estilo para hover en el botón de enviar */
.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}

/* Animación para spinner */
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Estilo para la sección de vista previa de imágenes */
.image-preview {
  transition: all 0.3s ease;
  border-radius: 4px;
}

.image-preview:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-close-img {
  opacity: 0;
  transition: opacity 0.2s ease;
  padding: 0.1rem 0.3rem;
  font-size: 0.7rem;
}

.image-preview:hover .btn-close-img {
  opacity: 1;
}

/* Animaciones para transiciones */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.fade-enter, .fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>