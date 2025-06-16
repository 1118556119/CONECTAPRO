// filepath: c:\xampp\htdocs\CONECTAPRO\resources\js\components\profile\ExperienceSection.vue
<template>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        <i class="fas fa-briefcase me-2"></i>Experiencia Profesional
      </h5>
      <button @click="showAddExperienceModal" class="btn btn-success btn-sm" :disabled="showModalState">
        <i class="fas fa-plus me-1"></i>Agregar Experiencia
      </button>
    </div>
    <div class="card-body">
      <!-- Sin experiencia -->
      <div v-if="experience.length === 0" class="text-center py-5">
        <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No hay experiencia profesional</h5>
        <p class="text-muted">Agrega tu experiencia laboral para fortalecer tu perfil profesional</p>
        <button @click="showAddExperienceModal" class="btn btn-primary" :disabled="showModalState">
          <i class="fas fa-plus me-2"></i>Agregar Primera Experiencia
        </button>
      </div>

      <!-- Lista de experiencias -->
      <div v-else>
        <div v-for="(exp, index) in sortedExperience" :key="exp.id || index" 
             class="card mb-3 border-start border-4"
             :class="exp.is_featured ? 'border-warning' : 'border-success'">
          <div class="card-body">
            <div class="row">
              <div class="col-md-8">
                <div class="d-flex align-items-start mb-2">
                  <h6 class="card-title mb-1">
                    {{ exp.position }}
                    <span v-if="exp.is_featured" class="badge bg-warning text-dark ms-2">Destacada</span>
                    <span v-if="exp.is_verified" class="badge bg-success ms-1">
                      <i class="fas fa-check me-1"></i>Verificada
                    </span>
                    <span v-if="exp.currently_working" class="badge bg-primary ms-1">Actual</span>
                  </h6>
                </div>
                
                <p class="card-text mb-2">
                  <strong>Empresa:</strong> {{ exp.company_name }}<br>
                  <strong>Período:</strong> {{ getFormattedPeriod(exp) }}
                  <span v-if="getDuration(exp)" class="text-muted ms-2">({{ getDuration(exp) }})</span><br>
                  <strong>Ubicación:</strong> {{ getFormattedLocation(exp) }}
                </p>

                <!-- Certificado -->
                <div v-if="exp.certificate_file_path" class="mt-2">
                  <a :href="getCertificateUrl(exp.certificate_file_path)" 
                     target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-file-pdf me-1"></i>
                    Ver Certificado
                    <small class="ms-1">({{ formatFileSize(exp.certificate_file_size) }})</small>
                  </a>
                </div>
              </div>
              
              <div class="col-md-4 text-end">
                <div class="btn-group-vertical d-grid gap-1">
                  <button @click="editExperience(index)" 
                          class="btn btn-outline-primary btn-sm"
                          :disabled="showModalState">
                    <i class="fas fa-edit me-1"></i>Editar
                  </button>
                  <button v-if="!exp.is_featured" @click="setFeaturedExperience(index)" 
                          class="btn btn-outline-warning btn-sm"
                          :disabled="showModalState">
                    <i class="fas fa-star me-1"></i>Destacar
                  </button>
                  <button @click="removeExperience(index)" 
                          class="btn btn-outline-danger btn-sm"
                          :disabled="showModalState">
                    <i class="fas fa-trash me-1"></i>Eliminar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal personalizado -->
    <Teleport to="body" v-if="showModalState">
      <div class="custom-modal-overlay" @click="handleOverlayClick">
        <div class="custom-modal" @click.stop>
          <div class="custom-modal-header">
            <h5 class="custom-modal-title">
              <i class="fas fa-briefcase me-2"></i>
              {{ isEditing ? 'Editar' : 'Agregar' }} Experiencia
            </h5>
            <button type="button" class="custom-close-btn" @click="cancelAndClose" 
                    aria-label="Cerrar modal" :disabled="loading">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <div class="custom-modal-body">
            <form @submit.prevent="saveExperience">
              <div class="row g-3">
                <!-- Información básica -->
                <div class="col-md-6">
                  <label class="form-label">Nombre de la Empresa <span class="text-danger">*</span></label>
                  <input v-model="experienceForm.company_name" type="text" class="form-control" 
                         placeholder="Ej: Empresa ABC S.A.S." required :disabled="loading">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Cargo/Posición <span class="text-danger">*</span></label>
                  <input v-model="experienceForm.position" type="text" class="form-control" 
                         placeholder="Ej: Técnico Electricista Senior" required :disabled="loading">
                </div>

                <!-- Fechas -->
                <div class="col-md-6">
                  <label class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                  <input v-model="experienceForm.start_date" type="date" class="form-control" 
                         required :disabled="loading">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Fecha de Fin</label>
                  <input v-model="experienceForm.end_date" type="date" class="form-control" 
                         :disabled="experienceForm.currently_working || loading">
                </div>

                <!-- Ubicación -->
                <div class="col-md-4">
                  <label class="form-label">País</label>
                  <input v-model="experienceForm.country" type="text" class="form-control" 
                         placeholder="País" :disabled="loading">
                </div>

                <div class="col-md-4">
                  <label class="form-label">Departamento</label>
                  <input v-model="experienceForm.department" type="text" class="form-control" 
                         placeholder="Departamento" :disabled="loading">
                </div>

                <div class="col-md-4">
                  <label class="form-label">Ciudad</label>
                  <input v-model="experienceForm.city" type="text" class="form-control" 
                         placeholder="Ciudad" :disabled="loading">
                </div>

                <!-- Certificado -->
                <div class="col-12">
                  <label class="form-label">Certificado o Diploma (PDF)</label>
                  <input type="file" @change="onCertificateChange" accept=".pdf" 
                         class="form-control" ref="fileInput" :disabled="loading">
                  <small class="form-text text-muted">
                    Máximo 5MB - Solo archivos PDF
                  </small>
                  
                  <!-- Archivo actual -->
                  <div v-if="experienceForm.certificate_file_path && isEditing" class="mt-2">
                    <small class="text-muted">
                      <i class="fas fa-file-pdf me-1"></i>
                      Archivo actual: {{ experienceForm.certificate_original_name }}
                    </small>
                  </div>
                </div>

                <!-- Opciones adicionales -->
                <div class="col-12">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-check">
                        <input v-model="experienceForm.currently_working" type="checkbox" 
                               class="form-check-input" id="currentlyWorking"
                               @change="handleCurrentlyWorkingChange" :disabled="loading">
                        <label class="form-check-label" for="currentlyWorking">
                          Trabajo actualmente aquí
                        </label>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-check">
                        <input v-model="experienceForm.is_featured" type="checkbox" 
                               class="form-check-input" id="isFeatured" :disabled="loading">
                        <label class="form-check-label" for="isFeatured">
                          Marcar como experiencia destacada
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
          
          <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" @click="cancelAndClose" :disabled="loading">
              <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button @click="saveExperience" :disabled="loading || isFormInvalid" class="btn btn-primary">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              <i v-else class="fas fa-save me-1"></i>
              {{ loading ? 'Guardando...' : (isEditing ? 'Actualizar' : 'Guardar') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script>
import Swal from 'sweetalert2';

export default {
  name: 'ExperienceSection',
  
  props: {
    experience: {
      type: Array,
      default: () => []
    }
  },

  emits: ['update-experience', 'show-message'],

  data() {
    return {
      loading: false,
      isEditing: false,
      editingIndex: -1,
      selectedCertificate: null,
      showModalState: false,
      
      experienceForm: {
        company_name: '',
        position: '',
        start_date: '',
        end_date: '',
        currently_working: false,
        country: 'Colombia',
        department: '',
        city: '',
        certificate_file_path: '',
        certificate_original_name: '',
        certificate_mime_type: '',
        certificate_file_size: null,
        sort_order: 0,
        is_featured: false,
        is_verified: false
      }
    };
  },

  computed: {
    sortedExperience() {
      return [...this.experience].sort((a, b) => {
        if (a.is_featured && !b.is_featured) return -1;
        if (!a.is_featured && b.is_featured) return 1;
        
        if (a.currently_working && !b.currently_working) return -1;
        if (!a.currently_working && b.currently_working) return 1;
        
        const dateA = new Date(a.start_date || 0);
        const dateB = new Date(b.start_date || 0);
        
        return dateB - dateA;
      });
    },

    isFormInvalid() {
      return !this.experienceForm.company_name || 
             !this.experienceForm.position || 
             !this.experienceForm.start_date;
    }
  },

  methods: {
    showAddExperienceModal() {
      if (this.showModalState) return;
      
      this.isEditing = false;
      this.editingIndex = -1;
      this.resetForm();
      this.openModal();
    },

    editExperience(index) {
      if (this.showModalState) return;
      
      this.isEditing = true;
      this.editingIndex = index;
      const exp = this.experience[index];
      
      // Copiar datos y formatear fechas
      Object.keys(this.experienceForm).forEach(key => {
        if (key === 'start_date' || key === 'end_date') {
          // Formatear fechas para el input date
          if (exp[key]) {
            this.experienceForm[key] = this.formatDateForInput(exp[key]);
          } else {
            this.experienceForm[key] = '';
          }
        } else {
          this.experienceForm[key] = exp[key] !== undefined ? exp[key] : this.experienceForm[key];
        }
      });
      
      this.openModal();
    },

    openModal() {
      if (this.showModalState) return;
      
      this.showModalState = true;
      document.body.style.overflow = 'hidden';
      document.addEventListener('keydown', this.handleEscKey);
    },

    closeModal() {
      if (!this.showModalState) return;
      
      this.showModalState = false;
      document.body.style.overflow = '';
      document.removeEventListener('keydown', this.handleEscKey);
      
      setTimeout(() => {
        this.resetForm();
      }, 300);
    },

    handleOverlayClick(event) {
      if (event.target === event.currentTarget) {
        this.cancelAndClose();
      }
    },

    handleEscKey(event) {
      if (event.key === 'Escape' && this.showModalState) {
        event.preventDefault();
        this.cancelAndClose();
      }
    },

    cancelAndClose() {
      if (this.loading) return;
      this.closeModal();
    },

    async saveExperience() {
      try {
        if (this.isFormInvalid) {
          this.$emit('show-message', 'Por favor completa todos los campos obligatorios', 'alert-warning');
          return;
        }

        this.loading = true;

        // Formatear fechas correctamente
        const formattedData = { ...this.experienceForm };
        
        // Convertir fechas al formato correcto YYYY-MM-DD
        if (formattedData.start_date) {
          formattedData.start_date = this.formatDateForServer(formattedData.start_date);
        }
        
        if (formattedData.end_date && !formattedData.currently_working) {
          formattedData.end_date = this.formatDateForServer(formattedData.end_date);
        } else if (formattedData.currently_working) {
          formattedData.end_date = null;
        }

        const experienceData = {
          section: this.isEditing ? 'experience_edit' : 'experience_add',
          index: this.editingIndex,
          data: formattedData,
          certificate: this.selectedCertificate
        };

        this.$emit('update-experience', experienceData);
        
        setTimeout(() => {
          this.closeModal();
        }, 100);

      } catch (error) {
        console.error('Error saving experience:', error);
        this.$emit('show-message', 'Error al guardar la experiencia', 'alert-danger');
      } finally {
        this.loading = false;
      }
    },

    // Método para formatear fechas
    formatDateForServer(dateInput) {
      if (!dateInput) return null;
      
      // Si ya está en formato YYYY-MM-DD, devolverlo tal como está
      if (typeof dateInput === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(dateInput)) {
        return dateInput;
      }
      
      // Si es un objeto Date o string ISO
      const date = new Date(dateInput);
      if (isNaN(date.getTime())) {
        console.error('Fecha inválida:', dateInput);
        return null;
      }
      
      // Formatear a YYYY-MM-DD
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      
      return `${year}-${month}-${day}`;
    },

    // Método para formatear fechas para input date
    formatDateForInput(dateInput) {
      if (!dateInput) return '';
      
      // Si ya está en formato YYYY-MM-DD
      if (typeof dateInput === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(dateInput)) {
        return dateInput;
      }
      
      // Convertir a formato YYYY-MM-DD para input date
      const date = new Date(dateInput);
      if (isNaN(date.getTime())) {
        return '';
      }
      
      return date.toISOString().split('T')[0];
    },

    async setFeaturedExperience(index) {
      if (this.showModalState) return;
      
      try {
        const result = await Swal.fire({
          title: '¿Marcar como destacada?',
          text: '¿Quieres establecer esta experiencia como destacada?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#ffc107',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sí, destacar',
          cancelButtonText: 'Cancelar',
          allowOutsideClick: false
        });

        if (result.isConfirmed) {
          this.experience.forEach((exp, i) => {
            exp.is_featured = (i === index);
          });

          this.$emit('update-experience', {
            section: 'experience_set_featured',
            index: index
          });

          this.$emit('show-message', 'Experiencia destacada actualizada', 'alert-success');
        }
      } catch (error) {
        console.error('Error setting featured experience:', error);
        this.$emit('show-message', 'Error al establecer experiencia destacada', 'alert-danger');
      }
    },

    async removeExperience(index) {
      if (this.showModalState) return;
      
      const exp = this.experience[index];
      
      const result = await Swal.fire({
        title: '¿Eliminar experiencia?',
        html: `¿Estás seguro de eliminar:<br><strong>${exp.position}</strong><br>en <strong>${exp.company_name}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false
      });

      if (result.isConfirmed) {
        this.$emit('update-experience', {
          section: 'experience_remove',
          index: index
        });
      }
    },

    handleCurrentlyWorkingChange() {
      if (this.experienceForm.currently_working) {
        this.experienceForm.end_date = '';
      }
    },

    onCertificateChange(event) {
      const file = event.target.files[0];
      if (!file) {
        this.selectedCertificate = null;
        return;
      }

      if (file.size > 5 * 1024 * 1024) {
        this.$emit('show-message', 'El certificado debe ser menor a 5MB', 'alert-warning');
        if (this.$refs.fileInput) {
          this.$refs.fileInput.value = '';
        }
        this.selectedCertificate = null;
        return;
      }
      
      if (file.type !== 'application/pdf') {
        this.$emit('show-message', 'Solo se permiten archivos PDF', 'alert-warning');
        if (this.$refs.fileInput) {
          this.$refs.fileInput.value = '';
        }
        this.selectedCertificate = null;
        return;
      }
      
      this.selectedCertificate = file;
      this.experienceForm.certificate_original_name = file.name;
      this.experienceForm.certificate_mime_type = file.type;
      this.experienceForm.certificate_file_size = file.size;
    },

    getCertificateUrl(path) {
      return path && path.startsWith('http') ? path : `/storage/${path}`;
    },

    formatFileSize(bytes) {
      if (!bytes) return '';
      const mb = bytes / (1024 * 1024);
      return mb > 1 ? `${mb.toFixed(1)}MB` : `${(bytes / 1024).toFixed(0)}KB`;
    },

    getFormattedPeriod(exp) {
      if (!exp.start_date) return 'Período no especificado';
      
      const startDate = new Date(exp.start_date);
      const start = startDate.toLocaleDateString('es-ES', { year: 'numeric', month: 'short' });
      
      if (exp.currently_working) {
        return `${start} - Presente`;
      }
      
      if (exp.end_date) {
        const endDate = new Date(exp.end_date);
        const end = endDate.toLocaleDateString('es-ES', { year: 'numeric', month: 'short' });
        return `${start} - ${end}`;
      }
      
      return `Desde ${start}`;
    },

    getDuration(exp) {
      if (!exp.start_date) return null;
      
      const startDate = new Date(exp.start_date);
      const endDate = exp.currently_working ? new Date() : (exp.end_date ? new Date(exp.end_date) : null);
      
      if (!endDate) return null;
      
      const diffTime = Math.abs(endDate - startDate);
      const diffMonths = Math.ceil(diffTime / (1000 * 60 * 60 * 24 * 30.44));
      
      if (diffMonths < 12) {
        return `${diffMonths} ${diffMonths === 1 ? 'mes' : 'meses'}`;
      }
      
      const years = Math.floor(diffMonths / 12);
      const months = diffMonths % 12;
      
      let duration = `${years} ${years === 1 ? 'año' : 'años'}`;
      if (months > 0) {
        duration += ` y ${months} ${months === 1 ? 'mes' : 'meses'}`;
      }
      
      return duration;
    },

    getFormattedLocation(exp) {
      const location = [];
      
      if (exp.city) location.push(exp.city);
      if (exp.department) location.push(exp.department);
      if (exp.country) location.push(exp.country);
      
      return location.join(', ') || 'Ubicación no especificada';
    },

    resetForm() {
      this.experienceForm = {
        company_name: '',
        position: '',
        start_date: '',
        end_date: '',
        currently_working: false,
        country: 'Colombia',
        department: '',
        city: '',
        certificate_file_path: '',
        certificate_original_name: '',
        certificate_mime_type: '',
        certificate_file_size: null,
        sort_order: 0,
        is_featured: false,
        is_verified: false
      };
      
      this.selectedCertificate = null;
      this.isEditing = false;
      this.editingIndex = -1;
    }
  },

  beforeUnmount() {
    document.removeEventListener('keydown', this.handleEscKey);
    document.body.style.overflow = '';
    this.showModalState = false;
  }
};
</script>

<style scoped>
.border-4 {
  border-width: 4px !important;
}

.card-title {
  line-height: 1.3;
}

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

/* Modal personalizado */
.custom-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  padding: 1rem;
}

.custom-modal {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  max-width: 700px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(-50px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.custom-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
  background-color: #f8f9fa;
  border-radius: 0.5rem 0.5rem 0 0;
}

.custom-modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 500;
  color: #212529;
}

.custom-close-btn {
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  color: #6c757d;
  font-size: 1.5rem;
  border-radius: 0.25rem;
  transition: all 0.2s;
}

.custom-close-btn:hover:not(:disabled) {
  color: #dc3545;
  background-color: #f8f9fa;
}

.custom-close-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.custom-modal-body {
  padding: 1.5rem;
  max-height: 60vh;
  overflow-y: auto;
}

.custom-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  background-color: #f8f9fa;
  border-radius: 0 0 0.5rem 0.5rem;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
  .custom-modal {
    max-width: 95%;
    margin: 0.5rem;
  }
  
  .custom-modal-body {
    padding: 1rem;
  }
  
  .custom-modal-header,
  .custom-modal-footer {
    padding: 1rem;
  }
}
</style>