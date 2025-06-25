<template>
  <div class="container-fluid p-0">
    <div class="row g-0">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 bg-light border-end vh-100 position-sticky top-0">
        <div class="p-3">
          <!-- Avatar y nombre del usuario -->
          <div class="text-center mb-4">
            <div class="position-relative d-inline-block">
              <!-- ✅ SIMPLIFICAR esta línea -->
              <img :src="generateSimpleAvatar()" 
                   alt="Avatar del usuario"
                   class="img-fluid rounded-circle mb-2 border profile-photo"
                   style="width: 100px; height: 100px; object-fit: cover;">
              
              <!-- Botón para cambiar foto -->
              <button v-if="editingPersonal" 
                      @click="$refs.fileInput.click()"
                      class="btn btn-sm btn-primary rounded-circle position-absolute"
                      style="bottom: 10px; right: 5px; width: 30px; height: 30px; padding: 0;">
                <i class="fas fa-camera" style="font-size: 12px;"></i>
              </button>
              
              <!-- Input oculto para seleccionar archivo -->
              <input ref="fileInput" 
                     type="file" 
                     @change="onFileChange" 
                     accept="image/*" 
                     style="display: none;">
            </div>
            
            <h6 class="mb-0">{{ profileData.name || 'Usuario' }}</h6>
            <small class="text-muted">{{ getUserTypeLabel(profileData.user_type) }}</small>
          </div>

          <!-- Menú de navegación -->
          <div class="list-group list-group-flush">
            <button @click="activeSection = 'personal'" 
                    class="list-group-item list-group-item-action border-0"
                    :class="{ 'active': activeSection === 'personal' }">
              <i class="fas fa-user me-2"></i> Datos Personales
            </button>

            <!-- Menús específicos para técnicos -->
            <div v-if="profileData.user_type === 'technician'" class="list-group list-group-flush">
              <button @click="activeSection = 'education'" 
                      :class="['list-group-item', 'list-group-item-action', { active: activeSection === 'education' }]">
                <i class="fas fa-graduation-cap me-2"></i>Educación
              </button>
              
              <button @click="activeSection = 'experience'" 
                      :class="['list-group-item', 'list-group-item-action', { active: activeSection === 'experience' }]">
                <i class="fas fa-briefcase me-2"></i>Experiencia
              </button>
              
              <!-- NUEVAS SECCIONES -->
              <button @click="activeSection = 'available-requests'" 
                      :class="['list-group-item', 'list-group-item-action', { active: activeSection === 'available-requests' }]">
                <i class="fas fa-search me-2"></i>Solicitudes Disponibles
              </button>
              
              <button @click="activeSection = 'my-work'" 
                      :class="['list-group-item', 'list-group-item-action', { active: activeSection === 'my-work' }]">
                <i class="fas fa-tasks me-2"></i>Mis Trabajos
              </button>
            </div>

            <!-- Menús específicos para clientes -->
            <div v-if="profileData.user_type === 'client'">
              <!-- NUEVA SECCIÓN: Mis Servicios -->
              <button @click="activeSection = 'my-services'" 
                      :class="['list-group-item', 'list-group-item-action', { active: activeSection === 'my-services' }]">
                <i class="fas fa-clipboard-list me-2"></i>Mis Servicios
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="col-md-9 col-lg-10">
        <!-- Loading global -->
        <div v-if="globalLoading" class="d-flex justify-content-center align-items-center" 
             style="height: 60vh;">
          <div class="text-center">
            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
            <p class="text-muted">Cargando información del perfil...</p>
          </div>
        </div>

        <!-- Contenido principal -->
        <div v-else class="p-4">
          <!-- Mensajes de alerta -->
          <div v-if="message" :class="messageClass + ' alert-dismissible fade show'" role="alert">
            {{ message }}
            <button type="button" class="btn-close" @click="clearMessage"></button>
          </div>

          <!-- Sección Información Personal -->
          <div v-if="activeSection === 'personal'">
            <PersonalInfoSection 
              :profile-data="profileData"
              :editing-personal="editingPersonal"
              :loading="loading"
              @enable-edit="enablePersonalEdit"
              @save-personal="savePersonalInfo"
              @cancel-edit="cancelPersonalEdit"
            />
          </div>

          <!-- NUEVA SECCIÓN: Mis Servicios -->
          <div v-else-if="activeSection === 'my-services'">
            <MyServicesSection />
          </div>
          
          <!-- Sección Educación (solo técnicos) -->
          <div v-if="activeSection === 'education' && profileData.user_type === 'technician'">
            <EducationSection 
              :education="profileData.education || []"
              @update-education="handleEducationUpdate"
              @show-message="showMessage"
            />
          </div>

          <!-- Sección Experiencia (solo técnicos) -->
          <div v-if="activeSection === 'experience' && profileData.user_type === 'technician'">
            <ExperienceSection 
              :experience="profileData.experience || []"
              @update-experience="handleExperienceUpdate"
              @show-message="showMessage"
            />
          </div>

          <!-- Sección Servicios (solo clientes) -->
          <div v-if="activeSection === 'services' && profileData.user_type === 'client'">
            <ServicesSection 
              :service-requests="serviceRequests || []"
              :loading="loading"
              @request-service="requestService"
              @view-details="viewServiceDetails"
              @view-technician="viewTechnicianInfo"
              @leave-review="leaveServiceReview"
            />
          </div>

          <!-- NUEVAS SECCIONES para técnicos -->
          <div v-else-if="activeSection === 'available-requests'">
            <AvailableRequestsSection @postulation-sent="handlePostulationSent" />
          </div>

          <div v-else-if="activeSection === 'my-work'">
            <MyWorkSection />
          </div>

          
        </div>
      </div>
    </div>

    <!-- Modal de notificaciones -->
    <div v-if="showNotificationsModal" class="modal fade show d-block" tabindex="-1" 
         style="background-color: rgba(0,0,0,0.5);">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-bell me-2"></i>Notificaciones
            </h5>
            <button type="button" class="btn-close" @click="closeNotifications"></button>
          </div>
          <div class="modal-body">
            <div v-if="notifications.length === 0" class="text-center py-4">
              <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
              <p class="text-muted">No tienes notificaciones</p>
            </div>
            <div v-else>
              <div v-for="notification in notifications" :key="notification.id" 
                   class="border-bottom pb-2 mb-2">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="mb-1">{{ notification.title }}</h6>
                    <p class="mb-1 text-muted small">{{ notification.message }}</p>
                    <small class="text-muted">{{ formatDate(notification.created_at) }}</small>
                  </div>
                  <span v-if="!notification.read" class="badge bg-primary">Nuevo</span>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeNotifications">
              Cerrar
            </button>
            <button v-if="unreadNotifications > 0" type="button" class="btn btn-primary" 
                    @click="markAllAsRead">
              Marcar todas como leídas
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
import PersonalInfoSection from './profile/PersonalInfoSection.vue';
import EducationSection from './profile/EducationSection.vue';
import ExperienceSection from './profile/ExperienceSection.vue';
import ServicesSection from './profile/ServicesSection.vue';
import MyServicesSection from './profile/MyServicesSection.vue';
import AvailableRequestsSection from './profile/AvailableRequestsSection.vue'; // NUEVO IMPORT
import MyWorkSection from './profile/MyWorkSection.vue'; // NUEVO IMPORT
import { eventBus, AUTH_EVENTS } from '../utils/eventBus';

// Interceptor para manejar errores de autenticación
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default {
  name: 'UserProfile',

  components: {
    PersonalInfoSection,
    EducationSection,
    ExperienceSection,
    ServicesSection,
    MyServicesSection,
    AvailableRequestsSection, // NUEVO COMPONENTE
    MyWorkSection // NUEVO COMPONENTE
  },

  data() {
    return {
      // Estados principales
      globalLoading: true,
      loading: false,
      activeSection: 'personal',
      photoLoadError: false,
      localPhotoBase64: null, // ✅ NUEVA VARIABLE
      
      // Datos del perfil - usando nombres exactos
      profileData: {
        name: '',
        idNumber: '',
        phone: '',
        birthDate: '',
        gender: '',
        email: '',
        password: '',
        user_type: '',
        city: '',
        address: '',
        postal_code: '',
        photo: '',
        education: [],
        experience: []
      },
      
      // Estados de edición
      editingPersonal: false,
      originalProfileData: {},
      selectedFile: null,
      
      // Servicios
      serviceRequests: [],
      
      // Notificaciones
      notifications: [],
      unreadNotifications: 0,
      showNotificationsModal: false,
      
      // Mensajes
      message: '',
      messageClass: 'alert-info'
    };
  },

  computed: {
    isValidSection() {
      const validSections = ['personal'];
      
      if (this.profileData.user_type === 'technician') {
        validSections.push('education', 'experience');
      }
      
      if (this.profileData.user_type === 'client') {
        validSections.push('services');
      }
      
      return validSections.includes(this.activeSection);
    }
  },

  // ✅ MODIFICAR el método mounted para cargar la foto desde localStorage
  async mounted() {
    // Eliminar mensaje de debug
    const savedPhoto = localStorage.getItem('userPhoto');
    if (savedPhoto) {
      this.localPhotoBase64 = savedPhoto;
    }
    
    await this.fetchProfile();
    await this.fetchNotifications();
  },

  methods: {
    // ========== MÉTODOS PRINCIPALES ==========
    
    async fetchProfile() {
      try {
        this.globalLoading = true;
        const token = localStorage.getItem('token');
        
        if (!token) {
          this.$router.push('/login');
          return;
        }

        const response = await axios.get('/profile', {
          headers: { 'Authorization': `Bearer ${token}` }
        });

        console.log('📦 Respuesta del servidor:', response.data);

        // Manejar diferentes estructuras de respuesta del backend
        let userData;
        
        if (response.data.user) {
          userData = response.data.user;
        } else if (response.data.data) {
          userData = response.data.data;
        } else {
          userData = response.data;
        }

        // ✅ GUARDAR LA FOTO ACTUAL
        const currentPhoto = this.profileData.photo;
        const hasLocalPhoto = this.localPhotoBase64 !== null;

        // Mapear campos con nombres correctos
        this.profileData = {
          id: userData.id || null,
          name: userData.name || '',
          idNumber: userData.idNumber || userData.id_number || '',
          phone: userData.phone || '',
          birthDate: userData.birthDate || userData.birthDate || '',
          gender: userData.gender || '',
          email: userData.email || '',
          password: userData.password || '',
          user_type: userData.user_type || 'client',
          city: userData.city || '',
          address: userData.address || '',
          postal_code: userData.postal_code || '',
          // ✅ MANTENER LA FOTO LOCAL SI EXISTE
          photo: hasLocalPhoto ? currentPhoto : (userData.photo || userData.photo_url || ''),
          
          education: Array.isArray(userData.education) ? userData.education : 
                     Array.isArray(userData.educations) ? userData.educations : [],
          
          experience: Array.isArray(userData.experience) ? userData.experience : 
                      Array.isArray(userData.experiences) ? userData.experiences : []
        };

        console.log('✅ Datos del perfil procesados:', this.profileData);
        
        // ✅ VERIFICAR si tenemos foto local y mostrar mensaje
        if (hasLocalPhoto) {
          console.log('🖼️ Manteniendo foto local base64 después de fetchProfile');
        }

        // Cargar servicios si es cliente
        if (this.profileData.user_type === 'client') {
          await this.fetchServiceRequests();
        }

      } catch (error) {
        console.error('❌ Error fetching profile:', error);
        
        // Inicializar con valores por defecto en caso de error
        this.profileData = {
          id: null,
          name: '',
          idNumber: '',
          phone: '',
          birthDate: '',
          gender: '',
          email: '',
          password: '',
          user_type: 'client',
          city: '',
          address: '',
          postal_code: '',
          photo: '',
          education: [],
          experience: []
        };
        
        this.showMessage('Error al cargar el perfil', 'alert-danger');
        
        if (error.response?.status === 401) {
          localStorage.removeItem('token');
          localStorage.removeItem('user');
          this.$router.push('/login');
        }
      } finally {
        this.globalLoading = false;
      }
    },

    async fetchServiceRequests() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('/service-requests', {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        
        console.log('📦 Servicios recibidos:', response.data);
        
        // Manejar diferentes estructuras de respuesta
        if (response.data.data) {
          this.serviceRequests = Array.isArray(response.data.data) ? response.data.data : [];
        } else if (response.data.services) {
          this.serviceRequests = Array.isArray(response.data.services) ? response.data.services : [];
        } else if (Array.isArray(response.data)) {
          this.serviceRequests = response.data;
        } else {
          this.serviceRequests = [];
        }
        
        console.log('✅ Servicios procesados:', this.serviceRequests);
        
      } catch (error) {
        console.error('❌ Error fetching service requests:', error);
        this.serviceRequests = [];
        
        if (error.response?.status !== 404) {
          this.showMessage('Error al cargar los servicios', 'alert-warning');
        }
      }
    },

    async saveToServer(formData) {
      const token = localStorage.getItem('token');
      
      // Log para debug - mostrar todos los campos que se están enviando
      console.log('📡 Enviando al servidor:', {
        endpoint: '/profile',
        hasToken: !!token,
        formDataEntries: Array.from(formData.entries()).map(([key, value]) => ({
          key,
          value: value instanceof File ? `File: ${value.name} (${value.size} bytes)` : value
        }))
      });
      
      const response = await axios.post('/profile', formData, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      });

      return response;
    },

    // ========== INFORMACIÓN PERSONAL ==========
    
    enablePersonalEdit() {
      this.editingPersonal = true;
      this.originalProfileData = { ...this.profileData };
    },

    async savePersonalInfo() {
      try {
        this.loading = true;
        
        // ✅ GUARDAR LA IMAGEN BASE64 SI EXISTE
        const savedBase64Photo = this.profileData.photo?.startsWith('data:image') ? this.profileData.photo : null;
        
        const formData = new FormData();
        formData.append('name', this.profileData.name || '');
        formData.append('idNumber', this.profileData.idNumber || '');
        formData.append('phone', this.profileData.phone || '');
        formData.append('birthDate', this.profileData.birthDate || '');
        formData.append('gender', this.profileData.gender || '');
        formData.append('city', this.profileData.city || '');
        formData.append('address', this.profileData.address || '');
        formData.append('postal_code', this.profileData.postal_code || '');

        if (this.selectedFile) {
          formData.append('photo', this.selectedFile);
        }

        const response = await this.saveToServer(formData);
        
        console.log('✅ Respuesta del servidor (personal):', response.data);
        
        // Actualizar datos locales con la respuesta del servidor
        if (response.data.success && response.data.data) {
          Object.assign(this.profileData, response.data.data);
        } else if (response.data.user) {
          Object.assign(this.profileData, response.data.user);
        }
        
        // ✅ RESTAURAR LA IMAGEN BASE64 SI HABÍA UNA
        if (savedBase64Photo) {
          this.profileData.photo = savedBase64Photo;
          console.log('💾 Restaurando foto base64 después de guardar');
        }
        
        this.editingPersonal = false;
        this.selectedFile = null;
        this.showMessage('Información personal actualizada correctamente', 'alert-success');

      } catch (error) {
        console.error('❌ Error saving personal info:', error);
        this.showMessage('Error al guardar la información personal', 'alert-danger');
      } finally {
        this.loading = false;
      }
    },

    cancelPersonalEdit() {
      this.profileData = { ...this.originalProfileData };
      this.editingPersonal = false;
      this.selectedFile = null;
    },

    // ✅ MODIFICAR el método onFileChange para guardar la foto en localStorage
    onFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) {
          this.showMessage('La imagen debe ser menor a 2MB', 'alert-warning');
          return;
        }
        
        if (!file.type.startsWith('image/')) {
          this.showMessage('Solo se permiten archivos de imagen', 'alert-warning');
          return;
        }
        
        this.selectedFile = file;
        
        console.log('📸 Archivo seleccionado:', file.name, file.type, file.size);
        
        // Mostrar preview inmediatamente como base64
        const reader = new FileReader();
        reader.onload = (e) => {
          console.log('✅ Preview cargado como base64');
          this.profileData.photo = e.target.result;
          this.localPhotoBase64 = e.target.result;
          
          // ✅ GUARDAR EN LOCALSTORAGE - NUEVO
          localStorage.setItem('userPhoto', e.target.result);
          
          this.$forceUpdate();
          console.log('🔄 Avatar actualizado y guardado en localStorage');
        };
        reader.readAsDataURL(file);
        
        // Auto-guardar si estamos en modo edición
        if (this.editingPersonal) {
          this.savePhotoOnly();
        }
      }
    },

    // ✅ MODIFICAR savePhotoOnly para mantener la imagen base64
    async savePhotoOnly() {
      if (!this.selectedFile) return;
      
      try {
        this.loading = true;
        
        const formData = new FormData();
        formData.append('name', this.profileData.name || '');
        formData.append('idNumber', this.profileData.idNumber || '');
        formData.append('phone', this.profileData.phone || '');
        formData.append('birthDate', this.profileData.birthDate || '');
        formData.append('gender', this.profileData.gender || '');
        formData.append('city', this.profileData.city || '');
        formData.append('address', this.profileData.address || '');
        formData.append('postal_code', this.profileData.postal_code || '');
        formData.append('photo', this.selectedFile);
        
        console.log('📤 Enviando foto al servidor');

        const response = await this.saveToServer(formData);
        
        console.log('✅ Respuesta del servidor (foto):', response.data);
        
        // Ya tenemos this.localPhotoBase64 guardado, no necesitamos hacer más
        
        this.selectedFile = null;
        this.showMessage('Foto actualizada correctamente', 'alert-success');

      } catch (error) {
        console.error('❌ Error saving photo:', error);
        this.showMessage('Error al guardar la foto', 'alert-danger');
      } finally {
        this.loading = false;
      }
    },

    // ========== EDUCACIÓN ==========
    
    async handleEducationUpdate(educationData) {
      try {
        if (educationData.section === 'education_remove') {
          const result = await Swal.fire({
            title: '¿Eliminar educación?',
            text: '¿Estás seguro de eliminar esta información educativa?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
          });

          if (result.isConfirmed) {
            this.profileData.education.splice(educationData.index, 1);
            this.showMessage('Educación eliminada correctamente', 'alert-success');
          }
          return;
        }

        const formData = new FormData();
        
        if (educationData.section === 'education_add') {
          formData.append('section', 'education');
          formData.append('action', 'add');
          formData.append('education_level', educationData.data.education_level);
          formData.append('institution', educationData.data.institution);
          formData.append('degree', educationData.data.degree);
          formData.append('graduation_year', educationData.data.graduation_year || '');

          if (educationData.certificate) {
            formData.append('certificate', educationData.certificate);
          }
          
        } else if (educationData.section === 'education_edit') {
          formData.append('section', 'education');
          formData.append('action', 'edit');
          formData.append('index', educationData.index);
          formData.append('education_level', educationData.data.education_level);
          formData.append('institution', educationData.data.institution);
          formData.append('degree', educationData.data.degree);
          formData.append('graduation_year', educationData.data.graduation_year || '');

          if (educationData.certificate) {
            formData.append('certificate', educationData.certificate);
          }
        }

        try {
          await this.saveToServer(formData);
          await this.fetchProfile();
          this.showMessage('Educación actualizada correctamente', 'alert-success');
        } catch (error) {
          console.warn('⚠️ Guardado en servidor falló, manejando localmente:', error);
          
          if (educationData.section === 'education_add') {
            this.profileData.education.push({
              id: Date.now(),
              ...educationData.data,
              certificate_url: null
            });
          } else if (educationData.section === 'education_edit') {
            if (this.profileData.education[educationData.index]) {
              this.profileData.education[educationData.index] = {
                ...this.profileData.education[educationData.index],
                ...educationData.data
              };
            }
          }
          
          this.showMessage('Educación actualizada (pendiente de sincronización)', 'alert-info');
        }

      } catch (error) {
        console.error('❌ Error updating education:', error);
        this.showMessage('Error al procesar la educación', 'alert-danger');
      }
    },

    // ========== EXPERIENCIA ==========
    
    async handleExperienceUpdate(experienceData) {
      try {
        if (experienceData.section === 'experience_remove') {
          const result = await Swal.fire({
            title: '¿Eliminar experiencia?',
            text: '¿Estás seguro de eliminar esta experiencia laboral?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
          });

          if (result.isConfirmed) {
            this.profileData.experience.splice(experienceData.index, 1);
            this.showMessage('Experiencia eliminada correctamente', 'alert-success');
          }
          return;
        }

        const formData = new FormData();
        
        if (experienceData.section === 'experience_add') {
          formData.append('section', 'experience');
          formData.append('action', 'add');
          formData.append('company_name', experienceData.data.company_name);
          formData.append('position', experienceData.data.position);
          formData.append('supervisor_name', experienceData.data.supervisor_name || '');
          formData.append('supervisor_phone', experienceData.data.supervisor_phone || '');
          formData.append('start_date', experienceData.data.start_date);
          formData.append('end_date', experienceData.data.end_date || '');
          formData.append('currently_working', experienceData.data.currently_working ? '1' : '0');
          formData.append('activities', experienceData.data.activities);

          if (experienceData.certification) {
            formData.append('certification', experienceData.certification);
          }
          
        } else if (experienceData.section === 'experience_edit') {
          formData.append('section', 'experience');
          formData.append('action', 'edit');
          formData.append('index', experienceData.index);
          formData.append('company_name', experienceData.data.company_name);
          formData.append('position', experienceData.data.position);
          formData.append('supervisor_name', experienceData.data.supervisor_name || '');
          formData.append('supervisor_phone', experienceData.data.supervisor_phone || '');
          formData.append('start_date', experienceData.data.start_date);
          formData.append('end_date', experienceData.data.end_date || '');
          formData.append('currently_working', experienceData.data.currently_working ? '1' : '0');
          formData.append('activities', experienceData.data.activities);

          if (experienceData.certification) {
            formData.append('certification', experienceData.certification);
          }
        }

        try {
          await this.saveToServer(formData);
          await this.fetchProfile();
          this.showMessage('Experiencia actualizada correctamente', 'alert-success');
        } catch (error) {
          console.warn('⚠️ Guardado en servidor falló, manejando localmente:', error);
          
          if (experienceData.section === 'experience_add') {
            this.profileData.experience.push({
              id: Date.now(),
              ...experienceData.data,
              certification_url: null
            });
          } else if (experienceData.section === 'experience_edit') {
            if (this.profileData.experience[experienceData.index]) {
              this.profileData.experience[experienceData.index] = {
                ...this.profileData.experience[experienceData.index],
                ...experienceData.data
              };
            }
          }
          
          this.showMessage('Experiencia actualizada (pendiente de sincronización)', 'alert-info');
        }

      } catch (error) {
        console.error('❌ Error updating experience:', error);
        this.showMessage('Error al procesar la experiencia', 'alert-danger');
      }
    },

    // ========== SERVICIOS (CLIENTES) ==========
    
    requestService() {
      this.$router.push('/solicitar-servicio'); 
    },

    viewServiceDetails(request) {
      Swal.fire({
        title: `Servicio ${request.request_number}`,
        html: `
          <div class="text-start">
            <p><strong>Estado:</strong> ${this.getServiceStatusLabel(request.status)}</p>
            <p><strong>Tipo:</strong> ${this.getServiceTypeLabel(request.service_type)}</p>
            <p><strong>Equipo:</strong> ${request.equipment_brand}</p>
            <p><strong>Descripción:</strong> ${request.description}</p>
            ${request.quoted_price ? `<p><strong>Precio:</strong> $${request.quoted_price.toLocaleString()}</p>` : ''}
            ${request.assigned_technician ? `<p><strong>Técnico:</strong> ${request.assigned_technician.name}</p>` : ''}
          </div>
        `,
        confirmButtonText: 'Cerrar'
      });
    },

    viewTechnicianInfo(technician) {
      Swal.fire({
        title: `Técnico: ${technician.name}`,
        html: `
          <div class="text-start">
            <p><strong>Email:</strong> ${technician.email}</p>
            <p><strong>Teléfono:</strong> ${technician.phone || 'No disponible'}</p>
            <p><strong>Ciudad:</strong> ${technician.city || 'No especificada'}</p>
          </div>
        `,
        confirmButtonText: 'Cerrar'
      });
    },

    leaveServiceReview(request) {
      console.log('Calificar servicio:', request);
      this.showMessage('Función de calificación en desarrollo', 'alert-info');
    },

    // ========== NOTIFICACIONES ==========
    
    async fetchNotifications() {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('/notifications', {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        
        if (response.data.success) {
          // Obtener notificaciones del servidor
          let notifs = response.data.data || [];
          
          // ✅ OBTENER IDs DE NOTIFICACIONES YA LEÍDAS
          const readIds = JSON.parse(localStorage.getItem('readNotifications') || '[]');
          
          // ✅ FILTRAR PARA ELIMINAR LAS YA LEÍDAS
          this.notifications = notifs.filter(notification => !readIds.includes(notification.id));
          
          // Actualizar contador
          this.unreadNotifications = this.notifications.length;
          
          console.log(`✅ Mostrando ${this.notifications.length} notificaciones no leídas`);
        }
      } catch (error) {
        console.error('Error fetching notifications:', error);
        
        // Datos de ejemplo para desarrollo
        this.notifications = [
          {
            id: 1,
            title: 'Nuevo servicio asignado',
            message: 'Se te ha asignado un nuevo servicio de mantenimiento correctivo',
            read: false,
            created_at: new Date().toISOString()
          }
        ];
        
        // ✅ OBTENER IDs DE NOTIFICACIONES YA LEÍDAS
        const readIds = JSON.parse(localStorage.getItem('readNotifications') || '[]');
        
        // ✅ FILTRAR PARA ELIMINAR LAS YA LEÍDAS
        this.notifications = this.notifications.filter(n => !readIds.includes(n.id));
        
        this.unreadNotifications = this.notifications.length;
      }
      
      // Asegurarnos de tener al menos una notificación para pruebas solo si no hay ninguna
      if (this.notifications.length === 0 && !localStorage.getItem('readNotifications')) {
        this.notifications = [
          {
            id: 1,
            title: 'Bienvenido a CONECTAPRO',
            message: '¡Gracias por registrarte! Estamos aquí para ayudarte con tus servicios técnicos.',
            read: false,
            created_at: new Date().toISOString()
          }
        ];
        this.unreadNotifications = 1;
      }
    },

    // ✅ REEMPLAZAR showNotifications() para forzar actualización del UI
    showNotifications() {
      console.log('📣 Mostrando modal de notificaciones');
      this.showNotificationsModal = true;
      document.body.style.overflow = 'hidden';
      
      // Forzar actualización de la UI
      this.$nextTick(() => {
        // Si no hay notificaciones, agregamos una de prueba
        if (this.notifications.length === 0) {
          this.notifications = [
            {
              id: Date.now(),
              title: 'Bienvenido a CONECTAPRO',
              message: '¡Gracias por registrarte! Explora todas las funcionalidades disponibles.',
              read: false,
              created_at: new Date().toISOString()
            }
          ];
          this.unreadNotifications = 1;
        }
      });
    },

    closeNotifications() {
      this.showNotificationsModal = false;
      document.body.style.overflow = '';
    },

    // ✅ MODIFICAR markAllAsRead para eliminar notificaciones leídas
    async markAllAsRead() {
      try {
        const token = localStorage.getItem('token');
        await axios.post('/notifications/mark-all-read', {}, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        
        // ✅ GUARDAR IDs ANTES DE ELIMINAR
        const readIds = this.notifications.map(n => n.id);
        localStorage.setItem('readNotifications', JSON.stringify(readIds));
        
        // ✅ ELIMINAR NOTIFICACIONES EN VEZ DE MARCARLAS
        this.notifications = [];
        this.unreadNotifications = 0;
        
        console.log('✅ Notificaciones eliminadas después de marcar como leídas');
        
      } catch (error) {
        console.error('Error marking notifications as read:', error);
        
        // Si falla el servidor, eliminar localmente
        const readIds = this.notifications.map(n => n.id);
        localStorage.setItem('readNotifications', JSON.stringify(readIds));
        
        this.notifications = [];
        this.unreadNotifications = 0;
      }
    },

    // ✅ MODIFICAR markAsRead para eliminar notificación individual
    markAsRead(notificationId) {
      try {
        const token = localStorage.getItem('token');
        axios.post(`/notifications/${notificationId}/read`, {}, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        
        // ✅ GUARDAR ID EN LOCALSTORAGE
        const readIds = JSON.parse(localStorage.getItem('readNotifications') || '[]');
        readIds.push(notificationId);
        localStorage.setItem('readNotifications', JSON.stringify(readIds));
        
        // ✅ ELIMINAR LA NOTIFICACIÓN DEL ARRAY
        this.notifications = this.notifications.filter(n => n.id !== notificationId);
        
        // Actualizar contador
        this.unreadNotifications = this.notifications.length;
        
      } catch (error) {
        console.error(`Error marking notification ${notificationId} as read:`, error);
        
        // Eliminar localmente si falla
        this.notifications = this.notifications.filter(n => n.id !== notificationId);
        this.unreadNotifications = this.notifications.length;
        
        // Guardar en localStorage
        const readIds = JSON.parse(localStorage.getItem('readNotifications') || '[]');
        readIds.push(notificationId);
        localStorage.setItem('readNotifications', JSON.stringify(readIds));
      }
    },

    goToHome() {
      this.$router.push('/');
    },

    // ========== UTILIDADES ==========
    
    getUserTypeLabel(userType) {
      const labels = {
        'client': 'Cliente',
        'technician': 'Técnico',
        'admin': 'Administrador'
      };
      return labels[userType] || userType;
    },

    getServiceStatusLabel(status) {
      const labels = {
        'pending': 'Pendiente',
        'assigned': 'Asignado',
        'in_progress': 'En Progreso',
        'completed': 'Completado',
        'cancelled': 'Cancelado'
      };
      return labels[status] || status;
    },

    getServiceTypeLabel(type) {
      const labels = {
        'correctivo': 'Mantenimiento Correctivo',
        'preventivo': 'Mantenimiento Preventivo',
        'instalacion': 'Instalación',
        'limpieza': 'Limpieza',
        'reparacion': 'Reparación'
      };
      return labels[type] || type;
    },

    showMessage(message, className = 'alert-info') {
      this.message = message;
      this.messageClass = className;
      
      setTimeout(() => {
        this.clearMessage();
      }, 5000);
    },

    clearMessage() {
      this.message = '';
      this.messageClass = 'alert-info';
    },

    // ✅ MODIFICAR logout para limpiar los datos almacenados
    async logout() {
      const result = await Swal.fire({
        title: '¿Cerrar sesión?',
        text: '¿Estás seguro de que quieres cerrar sesión?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
      });

      if (result.isConfirmed) {
        try {
          const token = localStorage.getItem('token');
          
          await axios.post('/logout', {}, {
            headers: { 'Authorization': `Bearer ${token}` }
          });
        } catch (error) {
          console.error('Error during logout:', error);
        } finally {
          // ✅ LIMPIAR TAMBIÉN LOS DATOS DE FOTO Y NOTIFICACIONES - NUEVO
          localStorage.removeItem('token');
          localStorage.removeItem('user');
          localStorage.removeItem('userPhoto');
          localStorage.removeItem('readNotifications');
          
          // Emitir evento
          eventBus.emit(AUTH_EVENTS.LOGOUT);
          
          this.$router.push('/login');
        }
      }
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
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
        
      } catch (error) {
        console.error('Error formatting date:', error);
        return dateString;
      }
    },

    goToAvailableRequests() {
      // Verificar que sea técnico antes de navegar
      if (this.profileData.user_type === 'technician') {
        this.$router.push('/solicitudes-disponibles');
      } else {
        Swal.fire('Acceso Denegado', 'Solo los técnicos pueden ver las solicitudes disponibles.', 'warning');
      }
    },
    
    // NUEVO MÉTODO
    goToAssignedRequests() {
      if (this.profileData.user_type === 'technician') {
        this.$router.push('/mis-trabajos');
      } else {
        Swal.fire('Acceso Denegado', 'Solo los técnicos pueden ver sus trabajos asignados.', 'warning');
      }
    },

    // NUEVO MÉTODO: Manejar cuando el técnico envía una postulación
    handlePostulationSent() {
      // Cambiar automáticamente a "Mis Trabajos" para ver el trabajo recién cotizado
      this.activeSection = 'my-work';
      
      // Mostrar notificación
      this.$nextTick(() => {
        Swal.fire({
          title: '¡Postulación Enviada!',
          text: 'Puedes ver el estado de tu cotización en "Mis Trabajos"',
          icon: 'success',
          timer: 3000,
          toast: true,
          position: 'top-end',
          showConfirmButton: false
        });
      });
    },

    // ✅ AGREGAR ESTE MÉTODO NUEVO
    generateSimpleAvatar() {
      console.log('🎨 Generando avatar para:', this.profileData.name);
      
      // 1. Usar la foto base64 guardada localmente si existe
      if (this.localPhotoBase64) {
        console.log('✅ Usando foto base64 persistente');
        return this.localPhotoBase64;
      }
      
      // 2. Si hay una foto base64 nueva (recién subida), usarla
      if (this.profileData.photo && this.profileData.photo.startsWith('data:image')) {
        console.log('✅ Usando foto base64 recién subida');
        return this.profileData.photo;
      }
      
      // 3. Para todo lo demás, usar avatar con iniciales
      const name = this.profileData.name || 'Usuario';
      const initials = name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
      
      console.log('👤 Generando avatar con iniciales:', initials);
      
      // Determinar color basado en tipo de usuario
      const bgColor = this.profileData.user_type === 'technician' ? 
                     '%2328A745' : // Verde para técnicos (URL encoded #28A745)
                     '%230056B3'; // Azul para clientes (URL encoded #0056B3)
      
      return `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'%3E%3Ccircle cx='100' cy='100' r='100' fill='${bgColor}'/%3E%3Ctext x='100' y='115' text-anchor='middle' font-family='Arial,sans-serif' font-size='60' font-weight='bold' fill='white'%3E${initials}%3C/text%3E%3C/svg%3E`;
    },

    // ✅ AGREGAR este método para manejar errores
    handleImageError(event) {
      // No hacer nada aquí, el generateSimpleAvatar ya maneja todo
      console.log('🔄 Imagen no encontrada, ya usando avatar con iniciales');
    },
  }
};
</script>

<style scoped>
.vh-100 {
  height: 100vh !important;
}

.position-sticky {
  position: sticky !important;
}

.top-0 {
  top: 0 !important;
}

.list-group-item.active {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: white;
}

.list-group-item:hover:not(.active) {
  background-color: #f8f9fa;
}

.sticky-top {
  position: sticky;
  top: 0;
  z-index: 1020;
}

.modal.show {
  display: block !important;
}

.position-relative .badge {
  font-size: 0.6em;
}

/* Estilos para el botón de cámara */
.position-relative .btn-primary {
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.position-relative .btn-primary:hover {
  transform: scale(1.1);
  transition: transform 0.2s ease;
}

/* Estilos para la foto de perfil */
.profile-photo {
  transition: transform 0.3s ease;
}

.profile-photo:hover {
  transform: scale(1.05);
}

/* Añadir al <style> de Profile.vue y ServiceRequestForm.vue */

/* Menú lateral activo según rol */
.user-client .list-group-item.active {
  background-color: var(--blue-tech);
  border-color: var(--blue-tech);
}

.user-technician .list-group-item.active {
  background-color: var(--green-service);
  border-color: var(--green-service);
}

/* Avatar con iniciales personalizado */
.user-client .avatar-initials {
  background-color: var(--blue-tech);
}

.user-technician .avatar-initials {
  background-color: var(--green-service);
}

/* Notificaciones */
.badge.bg-danger {
  background-color: var(--danger) !important;
}

/* Iconos de secciones */
.section-icon {
  color: var(--blue-tech);
}

.user-technician .section-icon {
  color: var(--green-service);
}

/* Formularios */
.form-section-title {
  color: var(--blue-tech);
  border-bottom: 2px solid var(--gray-light);
  padding-bottom: 0.5rem;
  margin-bottom: 1rem;
}

.user-technician .form-section-title {
  color: var(--green-service);
}
</style>