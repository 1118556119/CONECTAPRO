<template>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        <i class="fas fa-graduation-cap me-2"></i>Educación
      </h5>
      <button @click="showAddEducationModal" class="btn btn-success btn-sm" :disabled="showModalState">
        <i class="fas fa-plus me-1"></i>Agregar Educación
      </button>
    </div>
    <div class="card-body">
      <!-- Sin educación -->
      <div v-if="education.length === 0" class="text-center py-5">
        <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No hay información educativa</h5>
        <p class="text-muted">Agrega tu formación académica para mejorar tu perfil profesional</p>
        <button @click="showAddEducationModal" class="btn btn-primary" :disabled="showModalState">
          <i class="fas fa-plus me-2"></i>Agregar Primera Educación
        </button>
      </div>

      <!-- Lista de educación -->
      <div v-else>
        <div v-for="(edu, index) in sortedEducation" :key="edu.id || index" 
             class="card mb-3 border-start border-4"
             :class="edu.is_primary ? 'border-primary' : 'border-info'">
          <div class="card-body">
            <div class="row">
              <div class="col-md-8">
                <div class="d-flex align-items-start mb-2">
                  <h6 class="card-title mb-1">
                    {{ edu.degree }}
                    <span v-if="edu.is_primary" class="badge bg-primary ms-2">Principal</span>
                    <span v-if="edu.is_verified" class="badge bg-success ms-1">
                      <i class="fas fa-check me-1"></i>Verificado
                    </span>
                  </h6>
                </div>
                
                <p class="card-text mb-2">
                  <strong>Nivel:</strong> {{ edu.education_level }}<br>
                  <strong>Institución:</strong> {{ edu.institution }}<br>
                  <strong>Período:</strong> {{ getFormattedPeriod(edu) }}<br>
                  <strong>Ubicación:</strong> {{ getFormattedLocation(edu) }}
                </p>

                <!-- Certificado -->
                <div v-if="edu.certificate_file_path" class="mt-2">
                  <a :href="getCertificateUrl(edu.certificate_file_path)" 
                     target="_blank" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-file-pdf me-1"></i>
                    Ver Certificado
                    <small class="ms-1">({{ formatFileSize(edu.certificate_file_size) }})</small>
                  </a>
                </div>
              </div>
              
              <div class="col-md-4 text-end">
                <div class="btn-group-vertical d-grid gap-1">
                  <button @click="editEducation(index)" 
                          class="btn btn-outline-primary btn-sm" 
                          :disabled="showModalState">
                    <i class="fas fa-edit me-1"></i>Editar
                  </button>
                  <button v-if="!edu.is_primary" @click="setPrimaryEducation(index)" 
                          class="btn btn-outline-info btn-sm"
                          :disabled="showModalState">
                    <i class="fas fa-star me-1"></i>Marcar Principal
                  </button>
                  <button @click="removeEducation(index)" 
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
              <i class="fas fa-graduation-cap me-2"></i>
              {{ isEditing ? 'Editar' : 'Agregar' }} Educación
            </h5>
            <button type="button" class="custom-close-btn" @click="cancelAndClose" 
                    aria-label="Cerrar modal" :disabled="loading">
              <i class="fas fa-times"></i>
            </button>
          </div>
          
          <div class="custom-modal-body">
            <form @submit.prevent="saveEducation">
              <div class="row g-3">
                <!-- Nivel educativo -->
                <div class="col-md-6">
                  <label class="form-label">Nivel Educativo <span class="text-danger">*</span></label>
                  <select v-model="educationForm.education_level" class="form-select" required 
                          ref="firstInput" :disabled="loading">
                    <option value="">Seleccionar nivel</option>
                    <option value="Primaria">Primaria</option>
                    <option value="Secundaria">Secundaria</option>
                    <option value="Técnico">Técnico</option>
                    <option value="Tecnológico">Tecnológico</option>
                    <option value="Universitario">Universitario</option>
                    <option value="Especialización">Especialización</option>
                    <option value="Maestría">Maestría</option>
                    <option value="Doctorado">Doctorado</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>

                <!-- Institución -->
                <div class="col-md-6">
                  <label class="form-label">Institución <span class="text-danger">*</span></label>
                  <input v-model="educationForm.institution" type="text" class="form-control" 
                         placeholder="Nombre de la institución educativa" required :disabled="loading">
                </div>

                <!-- Título obtenido -->
                <div class="col-12">
                  <label class="form-label">Título Obtenido <span class="text-danger">*</span></label>
                  <input v-model="educationForm.degree" type="text" class="form-control" 
                         placeholder="Ej: Bachiller, Técnico en Sistemas, Ingeniero de Sistemas" 
                         required :disabled="loading">
                </div>

                <!-- Años -->
                <div class="col-md-6">
                  <label class="form-label">Año de Inicio</label>
                  <input v-model="educationForm.start_year" type="number" class="form-control" 
                         :min="1950" :max="currentYear" placeholder="Año" :disabled="loading">
                </div>

                <div class="col-md-6">
                  <label class="form-label">Año de Graduación</label>
                  <input v-model="educationForm.graduation_year" type="number" class="form-control" 
                         :min="1950" :max="currentYear + 10" placeholder="Año" :disabled="loading">
                </div>

                <!-- Ubicación -->
                <div class="col-md-4">
                  <label class="form-label">País</label>
                  <input v-model="educationForm.country" type="text" class="form-control" 
                         placeholder="País" :disabled="loading">
                </div>

                <div class="col-md-4">
                  <label class="form-label">Departamento</label>
                  <input v-model="educationForm.department" type="text" class="form-control" 
                         placeholder="Departamento" :disabled="loading">
                </div>

                <div class="col-md-4">
                  <label class="form-label">Ciudad</label>
                  <input v-model="educationForm.city" type="text" class="form-control" 
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
                  <div v-if="educationForm.certificate_file_path && isEditing" class="mt-2">
                    <small class="text-muted">
                      <i class="fas fa-file-pdf me-1"></i>
                      Archivo actual: {{ educationForm.certificate_original_name }}
                    </small>
                  </div>
                </div>

                <!-- Opciones adicionales -->
                <div class="col-12">
                  <div class="form-check">
                    <input v-model="educationForm.is_primary" type="checkbox" 
                           class="form-check-input" id="isPrimary" :disabled="loading">
                    <label class="form-check-label" for="isPrimary">
                      Marcar como educación principal
                    </label>
                  </div>
                </div>
              </div>
            </form>
          </div>
          
          <div class="custom-modal-footer">
            <button type="button" class="btn btn-secondary" @click="cancelAndClose" :disabled="loading">
              <i class="fas fa-times me-1"></i>Cancelar
            </button>
            <button @click="saveEducation" :disabled="loading || isFormInvalid" class="btn btn-primary">
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
  name: 'EducationSection',
  
  props: {
    education: {
      type: Array,
      default: () => []
    }
  },

  emits: ['update-education', 'show-message'],

  data() {
    return {
      loading: false,
      isEditing: false,
      editingIndex: -1,
      selectedCertificate: null,
      showModalState: false,
      
      educationForm: {
        education_level: '',
        institution: '',
        degree: '',
        start_year: null,
        graduation_year: null,
        country: 'Colombia',
        department: '',
        city: '',
        certificate_file_path: '',
        certificate_original_name: '',
        certificate_mime_type: '',
        certificate_file_size: null,
        sort_order: 0,
        is_primary: false,
        is_verified: false
      }
    };
  },

  computed: {
    currentYear() {
      return new Date().getFullYear();
    },

    sortedEducation() {
      return [...this.education].sort((a, b) => {
        if (a.is_primary && !b.is_primary) return -1;
        if (!a.is_primary && b.is_primary) return 1;
        
        const yearA = a.graduation_year || a.start_year || 0;
        const yearB = b.graduation_year || b.start_year || 0;
        
        return yearB - yearA;
      });
    },

    isFormInvalid() {
      return !this.educationForm.education_level || 
             !this.educationForm.institution || 
             !this.educationForm.degree;
    }
  },

  methods: {
    showAddEducationModal() {
      if (this.showModalState) return;
      
      this.isEditing = false;
      this.editingIndex = -1;
      this.resetForm();
      this.openModal();
    },

    editEducation(index) {
      if (this.showModalState) return;
      
      this.isEditing = true;
      this.editingIndex = index;
      const edu = this.education[index];
      
      Object.keys(this.educationForm).forEach(key => {
        this.educationForm[key] = edu[key] !== undefined ? edu[key] : this.educationForm[key];
      });
      
      this.openModal();
    },

    openModal() {
      if (this.showModalState) return;
      
      this.showModalState = true;
      document.body.style.overflow = 'hidden';
      
      this.$nextTick(() => {
        if (this.$refs.firstInput) {
          this.$refs.firstInput.focus();
        }
      });
      
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

    async saveEducation() {
      try {
        if (this.isFormInvalid) {
          this.$emit('show-message', 'Por favor completa todos los campos obligatorios', 'alert-warning');
          return;
        }

        this.loading = true;

        const educationData = {
          section: this.isEditing ? 'education_edit' : 'education_add',
          index: this.editingIndex,
          data: { ...this.educationForm },
          certificate: this.selectedCertificate
        };

        this.$emit('update-education', educationData);
        
        setTimeout(() => {
          this.closeModal();
        }, 100);

      } catch (error) {
        console.error('Error saving education:', error);
        this.$emit('show-message', 'Error al guardar la educación', 'alert-danger');
      } finally {
        this.loading = false;
      }
    },

    async setPrimaryEducation(index) {
      if (this.showModalState) return;
      
      try {
        const result = await Swal.fire({
          title: '¿Marcar como principal?',
          text: '¿Quieres establecer esta educación como principal?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#0d6efd',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sí, marcar como principal',
          cancelButtonText: 'Cancelar',
          allowOutsideClick: false
        });

        if (result.isConfirmed) {
          this.education.forEach((edu, i) => {
            edu.is_primary = (i === index);
          });

          this.$emit('update-education', {
            section: 'education_set_primary',
            index: index
          });

          this.$emit('show-message', 'Educación principal actualizada', 'alert-success');
        }
      } catch (error) {
        console.error('Error setting primary education:', error);
        this.$emit('show-message', 'Error al establecer educación principal', 'alert-danger');
      }
    },

    async removeEducation(index) {
      if (this.showModalState) return;
      
      const edu = this.education[index];
      
      const result = await Swal.fire({
        title: '¿Eliminar educación?',
        html: `¿Estás seguro de eliminar:<br><strong>${edu.degree}</strong><br>de <strong>${edu.institution}</strong>?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        allowOutsideClick: false
      });

      if (result.isConfirmed) {
        this.$emit('update-education', {
          section: 'education_remove',
          index: index
        });
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
      this.educationForm.certificate_original_name = file.name;
      this.educationForm.certificate_mime_type = file.type;
      this.educationForm.certificate_file_size = file.size;
    },

    getCertificateUrl(path) {
      return path && path.startsWith('http') ? path : `/storage/${path}`;
    },

    formatFileSize(bytes) {
      if (!bytes) return '';
      const mb = bytes / (1024 * 1024);
      return mb > 1 ? `${mb.toFixed(1)}MB` : `${(bytes / 1024).toFixed(0)}KB`;
    },

    getFormattedPeriod(edu) {
      if (edu.start_year && edu.graduation_year) {
        return `${edu.start_year} - ${edu.graduation_year}`;
      }
      
      if (edu.graduation_year) {
        return `Graduado en ${edu.graduation_year}`;
      }
      
      if (edu.start_year) {
        return `Desde ${edu.start_year}`;
      }
      
      return 'Período no especificado';
    },

    getFormattedLocation(edu) {
      const location = [];
      
      if (edu.city) location.push(edu.city);
      if (edu.department) location.push(edu.department);
      if (edu.country) location.push(edu.country);
      
      return location.join(', ') || 'Ubicación no especificada';
    },

    resetForm() {
      this.educationForm = {
        education_level: '',
        institution: '',
        degree: '',
        start_year: null,
        graduation_year: null,
        country: 'Colombia',
        department: '',
        city: '',
        certificate_file_path: '',
        certificate_original_name: '',
        certificate_mime_type: '',
        certificate_file_size: null,
        sort_order: 0,
        is_primary: false,
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