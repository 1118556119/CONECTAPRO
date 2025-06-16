<template>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        <i class="fas fa-user me-2"></i>Datos Personales
      </h5>
      <button v-if="!editingPersonal" @click="enablePersonalEdit" class="btn btn-outline-primary btn-sm">
        <i class="fas fa-edit me-1"></i>Editar
      </button>
    </div>
    <div class="card-body">
      <!-- Formulario de datos personales -->
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
          <input v-if="editingPersonal" v-model="profileData.name" 
                 type="text" class="form-control" required>
          <p v-else class="form-control-plaintext">{{ profileData.name || 'No especificado' }}</p>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Cédula</label>
          <input v-if="editingPersonal" v-model="profileData.idNumber" 
                 type="text" class="form-control" placeholder="Número de identificación">
          <p v-else class="form-control-plaintext">{{ profileData.idNumber || 'No especificado' }}</p>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <p class="form-control-plaintext">
            {{ profileData.email }}
            <small class="text-muted d-block">No se puede modificar</small>
          </p>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Teléfono</label>
          <input v-if="editingPersonal" v-model="profileData.phone" 
                 type="tel" class="form-control" placeholder="Número de teléfono">
          <p v-else class="form-control-plaintext">{{ profileData.phone || 'No especificado' }}</p>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Fecha de Nacimiento</label>
          <template v-if="editingPersonal">
            <input 
              type="date" 
              :value="profileData.birthDate ? profileData.birthDate.split('T')[0] : ''"
              @input="profileData.birthDate = $event.target.value"
              class="form-control"
            />
          </template>
          <template v-else>
            <p class="form-control-plaintext">{{ formatDate(profileData.birthDate) || 'No especificado' }}</p>
          </template>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Género</label>
          <select v-if="editingPersonal" v-model="profileData.gender" class="form-select">
            <option value="">Seleccionar</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Otro">Otro</option>
          </select>
          <p v-else class="form-control-plaintext">{{ profileData.gender || 'No especificado' }}</p>
        </div>
        
        <div class="col-12">
          <label class="form-label">Dirección</label>
          <input v-if="editingPersonal" v-model="profileData.address" 
                 type="text" class="form-control" placeholder="Dirección completa">
          <p v-else class="form-control-plaintext">{{ profileData.address || 'No especificado' }}</p>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Ciudad</label>
          <input v-if="editingPersonal" v-model="profileData.city" 
                 type="text" class="form-control" placeholder="Ciudad de residencia">
          <p v-else class="form-control-plaintext">{{ profileData.city || 'No especificado' }}</p>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Código Postal</label>
          <input v-if="editingPersonal" v-model="profileData.postal_code" 
                 type="text" class="form-control" placeholder="Código postal">
          <p v-else class="form-control-plaintext">{{ profileData.postal_code || 'No especificado' }}</p>
        </div>
      </div>
      
      <div v-if="editingPersonal" class="d-flex gap-2 justify-content-end mt-4">
        <button @click="cancelPersonalEdit" class="btn btn-secondary">
          <i class="fas fa-times me-1"></i>Cancelar
        </button>
        <button @click="savePersonalInfo" :disabled="loading" class="btn btn-primary">
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          <i v-else class="fas fa-save me-1"></i>
          {{ loading ? 'Guardando...' : 'Guardar Cambios' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PersonalInfoSection',
  
  props: {
    profileData: {
      type: Object,
      required: true
    },
    editingPersonal: {
      type: Boolean,
      default: false
    },
    loading: {
      type: Boolean,
      default: false
    }
  },

  emits: ['enable-edit', 'save-personal', 'cancel-edit'],

  methods: {
    enablePersonalEdit() {
      this.$emit('enable-edit');
    },

    savePersonalInfo() {
      this.$emit('save-personal');
    },

    cancelPersonalEdit() {
      this.$emit('cancel-edit');
    },

    formatDate(dateString) {
      if (!dateString) return '';
      
      try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
          return dateString;
        }
        
        return date.toLocaleDateString('es-ES', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
        
      } catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
      }
    }
  }
};
</script>

<style scoped>
.form-control-plaintext {
  padding-left: 0;
  border: none;
  background: transparent;
}
</style>