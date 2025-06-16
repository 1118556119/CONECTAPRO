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
              
              <!-- Botones de navegación -->
              <div class="d-flex align-items-center gap-2">
                <!-- Botón de notificaciones -->
                <div class="dropdown">
                  <button class="btn btn-outline-secondary btn-sm position-relative" type="button"
                    id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell"></i>
                    <span v-if="unreadNotifications > 0"
                      class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                      style="font-size: 0.6rem;">
                      {{ unreadNotifications }}
                      <span class="visually-hidden">notificaciones sin leer</span>
                    </span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end notification-menu" aria-labelledby="notificationsDropdown"
                    style="width: 300px; max-height: 400px; overflow-y: auto;">
                    <li>
                      <h6 class="dropdown-header d-flex justify-content-between">
                        <span>Notificaciones</span>
                        <span v-if="notifications.length > 0" class="text-primary"
                          style="cursor: pointer; font-size: 0.8rem;" @click="markAllAsRead">
                          Marcar todas como leídas
                        </span>
                      </h6>
                    </li>

                    <!-- Cargando notificaciones -->
                    <li v-if="loadingNotifications" class="text-center py-3">
                      <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                      </div>
                    </li>

                    <!-- Lista de notificaciones -->
                    <li v-else-if="notifications.length > 0">
                      <div v-for="(notification, index) in notifications" :key="'notif-' + index"
                        :class="{ 'bg-light': !notification.read_at }" class="dropdown-item notification-item"
                        style="white-space: normal; border-bottom: 1px solid #f0f0f0;">
                        <div class="d-flex">
                          <div class="me-2">
                            <i :class="getNotificationIcon(notification.type)" class="text-primary"></i>
                          </div>
                          <div class="flex-grow-1">
                            <div class="fw-semibold small">{{ notification.title }}</div>
                            <p class="text-muted mb-0 small">{{ notification.message }}</p>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                              <small class="text-muted">{{ formatNotificationDate(notification.created_at) }}</small>
                              <button v-if="!notification.read_at" @click="markAsRead(notification.id)"
                                class="btn btn-sm btn-link p-0 text-primary" style="font-size: 0.75rem;">
                                Marcar como leída
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </li>

                    <!-- Sin notificaciones -->
                    <li v-else class="text-center py-3">
                      <i class="bi bi-bell-slash text-muted fs-4"></i>
                      <p class="text-muted mb-0 small">No tienes notificaciones</p>
                    </li>

                    <li v-if="notifications.length > 0">
                      <hr class="dropdown-divider">
                    </li>
                    <li v-if="notifications.length > 0">
                      <a class="dropdown-item text-center small" href="#" @click.prevent="viewAllNotifications">
                        Ver todas
                      </a>
                    </li>
                  </ul>
                </div>

                <!-- Botón ir a página principal -->
                <button class="btn btn-outline-primary btn-sm" @click="goToHome">
                  <i class="bi bi-house me-1"></i> Inicio
                </button>

                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><router-link to="/dashboard">Perfil</router-link></li>
                    <li class="breadcrumb-item active" aria-current="page">Solicitud de Servicio</li>
                  </ol>
                </nav>
              </div>
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
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  name: 'ServiceRequestForm',
  data() {
    return {
      form: {
        type: '',
        description: '',
        urgency: 'media',
        equipment_type: '',
        brand: '',
        address: '',
        city: '',
        postal_code: '',
        preferred_date: '',
        preferred_time: '',
        comments: '', // Asegurar que esté inicializado
        other_type: '',
        service_type_detail: '',
        terms: false
      },
      isSubmitting: false,
      error: '',
      uploadedFiles: [],
      previewImages: [],
      
      // Datos para notificaciones
      notifications: [],
      unreadNotifications: 0,
      loadingNotifications: false,
      notificationDropdownListener: null
    };
  },
  
  computed: {
    minDate() {
      const today = new Date();
      return today.toISOString().split('T')[0];
    },
    formValid() {
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
        this.showError('Debes aceptar los términos y condiciones');
        return;
      }
      
      this.isSubmitting = true;
      this.error = '';
      
      try {
        const formData = new FormData();
        
        // Agregar todos los datos del formulario, incluso si están vacíos
        const fieldsToSend = [
          'type', 'description', 'urgency', 'equipment_type', 'brand',
          'address', 'city', 'postal_code', 'preferred_date', 'preferred_time',
          'comments', 'other_type', 'service_type_detail'
        ];
        
        fieldsToSend.forEach(field => {
          const value = this.form[field];
          if (value !== null && value !== undefined) {
            formData.append(field, value);
          }
        });
        
        // Agregar archivos
        this.uploadedFiles.forEach((file, index) => {
          formData.append(`photos[${index}]`, file);
        });
        
        // Debug: Mostrar qué se está enviando
        console.log('Datos del formulario a enviar:');
        for (let [key, value] of formData.entries()) {
          console.log(key, value);
        }
        
        const token = localStorage.getItem('token');
        const response = await axios.post('/service-requests', formData, {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'multipart/form-data'
          }
        });
        
        if (response.data.success) {
          await Swal.fire({
            title: '¡Solicitud Enviada!',
            text: 'Tu solicitud ha sido enviada exitosamente. Pronto un técnico se pondrá en contacto contigo.',
            icon: 'success',
            confirmButtonText: 'Ver Mis Solicitudes'
          });
          
          // Limpiar formulario
          this.resetForm();
          
          // Redirigir a mis solicitudes
          this.$router.push('/profile');
          // O si quieres ir directamente a la sección:
          // this.$router.push({ path: '/profile', hash: '#my-services' });
        }
        
      } catch (error) {
        console.error('Error completo:', error);
        console.error('Respuesta del servidor:', error.response?.data);
        
        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat();
          this.showError(errors.join(', '));
        } else {
          this.showError(error.response?.data?.message || 'Error al enviar la solicitud');
        }
      } finally {
        this.isSubmitting = false;
      }
    },
    
    resetForm() {
      this.form = {
        type: '',
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
        other_type: '',
        service_type_detail: '',
        terms: false
      };
      this.uploadedFiles = [];
      this.previewImages = [];
      
      // Resetear la fecha a mañana
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      this.form.preferred_date = tomorrow.toISOString().split('T')[0];
    },
    
    // Métodos para navegación
    goToHome() {
      window.location.href = '/';
    },

    // Métodos para notificaciones
    async fetchNotifications() {
      console.log('fetchNotifications fue llamado');
      this.loadingNotifications = true;
      try {
        const token = localStorage.getItem('token');
        if (!token) return;
        
        const response = await axios.get('/notifications', {
          headers: {
            Authorization: `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });

        if (response.data && response.data.success) {
          this.notifications = response.data.data || [];
          this.unreadNotifications = this.notifications.filter(n => !n.read_at).length;
        }
        
      } catch (error) {
        console.error('Error al cargar notificaciones:', error);
        this.notifications = [];
        this.unreadNotifications = 0;
      } finally {
        this.loadingNotifications = false;
      }
    },

    async markAsRead(notificationId) {
      try {
        await axios.post(`/notifications/${notificationId}/mark-read`, {}, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json'
          }
        });

        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification) {
          notification.read_at = new Date().toISOString();
          this.unreadNotifications = Math.max(0, this.unreadNotifications - 1);
        }
      } catch (error) {
        console.error('Error al marcar notificación como leída:', error);
      }
    },

    async markAllAsRead() {
      try {
        await axios.post('/notifications/mark-all-read', {}, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json'
          }
        });

        this.notifications.forEach(notification => {
          notification.read_at = new Date().toISOString();
        });
        this.unreadNotifications = 0;
      } catch (error) {
        console.error('Error al marcar todas como leídas:', error);
      }
    },

    getNotificationIcon(type) {
      const icons = {
        'service_request': 'bi bi-tools',
        'message': 'bi bi-chat-dots',
        'profile_update': 'bi bi-person-check',
        'payment': 'bi bi-credit-card',
        'system': 'bi bi-gear',
      };
      return icons[type] || 'bi bi-bell';
    },

    formatNotificationDate(dateString) {
      if (!dateString) return '';
      
      const date = new Date(dateString);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.round(diffMs / 60000);
      const diffHours = Math.round(diffMs / 3600000);
      const diffDays = Math.round(diffMs / 86400000);
      
      if (diffMins < 60) {
        return `Hace ${diffMins} minuto${diffMins !== 1 ? 's' : ''}`;
      } else if (diffHours < 24) {
        return `Hace ${diffHours} hora${diffHours !== 1 ? 's' : ''}`;
      } else if (diffDays < 7) {
        return `Hace ${diffDays} día${diffDays !== 1 ? 's' : ''}`;
      } else {
        return date.toLocaleDateString('es-ES');
      }
    },

    viewAllNotifications() {
      const notificationsHtml = this.notifications.length === 0 
        ? '<p class="text-center text-muted">No tienes notificaciones</p>'
        : `
          <div class="notification-list" style="max-height: 400px; overflow-y: auto; text-align: left;">
            ${this.notifications.map(notification => `
              <div class="notification-item p-2 mb-2 ${!notification.read_at ? 'bg-light' : ''}" 
                  style="border-radius: 8px; border-bottom: 1px solid #eee;">
                <div class="d-flex">
                  <div class="me-2">
                    <i class="${this.getNotificationIcon(notification.type)} text-primary"></i>
                  </div>
                  <div class="flex-grow-1">
                    <div class="fw-semibold small">${notification.title}</div>
                    <p class="text-muted mb-1 small">${notification.message}</p>
                    <small class="text-muted">${this.formatNotificationDate(notification.created_at)}</small>
                  </div>
                </div>
              </div>
            `).join('')}
          </div>
        `;

      Swal.fire({
        title: 'Todas las notificaciones',
        html: notificationsHtml,
        width: '600px',
        confirmButtonText: 'Cerrar',
        showClass: {
          popup: 'animate__animated animate__fadeIn'
        }
      });
    },

    // ...existing methods for form...
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

    // Configurar dropdown de notificaciones
    this.$nextTick(() => {
      const dropdownElement = document.getElementById('notificationsDropdown');
      if (dropdownElement) {
        if (window.bootstrap && window.bootstrap.Dropdown) {
          if (!dropdownElement.hasAttribute('data-bs-dropdown-initialized')) {
            new window.bootstrap.Dropdown(dropdownElement);
            dropdownElement.setAttribute('data-bs-dropdown-initialized', 'true');
            console.log('Dropdown de notificaciones inicializado manualmente.');
          }
        }

        this.notificationDropdownListener = () => {
          console.log('Evento show.bs.dropdown disparado. Llamando a fetchNotifications...');
          this.fetchNotifications();
        };
        dropdownElement.addEventListener('show.bs.dropdown', this.notificationDropdownListener);
      }
    });
  },

  beforeDestroy() {
    const dropdownElement = document.getElementById('notificationsDropdown');
    if (dropdownElement && this.notificationDropdownListener) {
      dropdownElement.removeEventListener('show.bs.dropdown', this.notificationDropdownListener);
      this.notificationDropdownListener = null;
    }
  }
};
</script>

