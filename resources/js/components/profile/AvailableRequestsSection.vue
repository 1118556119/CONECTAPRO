<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4><i class="fas fa-search me-2 text-primary"></i>Solicitudes Disponibles</h4>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Urgencia:</label>
            <select v-model="urgencyFilter" class="form-select form-select-sm">
              <option value="">Todas las urgencias</option>
              <option value="baja">Baja</option>
              <option value="media">Media</option>
              <option value="alta">Alta</option>
              <option value="critica">Crítica</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Tipo de Servicio:</label>
            <select v-model="serviceTypeFilter" class="form-select form-select-sm">
              <option value="">Todos los servicios</option>
              <option value="correctivo">Correctivo</option>
              <option value="preventivo">Preventivo</option>
              <option value="instalacion">Instalación</option>
              <option value="reparacion">Reparación</option>
              <option value="diagnostico">Diagnóstico</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">Buscar:</label>
            <input 
              v-model="searchText" 
              type="text" 
              class="form-control form-control-sm" 
              placeholder="Buscar por descripción..."
            >
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-2 text-muted">Cargando solicitudes disponibles...</p>
    </div>

    <!-- No hay solicitudes -->
    <div v-else-if="filteredRequests.length === 0" class="text-center py-5">
      <div class="mb-3">
        <i class="fas fa-search fa-4x text-muted"></i>
      </div>
      <h5 class="text-muted">No hay solicitudes disponibles</h5>
      <p class="text-muted">Nuevas solicitudes aparecerán aquí cuando los clientes las publiquen.</p>
    </div>

    <!-- Lista de solicitudes -->
    <div v-else class="row">
      <div v-for="request in filteredRequests" :key="request.id" class="col-12 mb-4">
        <div class="card h-100 border-warning">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-0">
                <i class="fas fa-ticket-alt me-2"></i>
                Solicitud #{{ request.request_number }}
              </h6>
              <small class="text-muted">
                {{ formatDateTime(request.created_at) }}
              </small>
            </div>
            <span class="badge bg-warning text-dark">
              Disponible
            </span>
          </div>
          
          <div class="card-body">
            <div class="row">
              <div class="col-md-8">
                <h6 class="card-title">{{ getServiceTypeLabel(request.service_type) }}</h6>
                
                <div class="mb-2">
                  <span class="badge me-2" :class="getUrgencyBadgeClass(request.urgency_level)">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    {{ getUrgencyLabel(request.urgency_level) }}
                  </span>
                  <span class="badge bg-light text-dark">
                    <i class="fas fa-desktop me-1"></i>
                    {{ getEquipmentTypeLabel(request.equipment_type) }}
                  </span>
                </div>
                
                <p class="card-text text-muted small">
                  <strong>Equipo:</strong> {{ request.equipment_brand }} {{ request.equipment_model }}<br>
                  <strong>Ubicación:</strong> {{ request.service_city }}<br>
                  <strong>Descripción:</strong> {{ request.description.substring(0, 100) }}{{ request.description.length > 100 ? '...' : '' }}
                </p>

                <!-- Información del cliente (básica) -->
                <div class="alert alert-light py-2 px-3 small">
                  <div class="d-flex align-items-center">
                    <i class="fas fa-user me-2"></i>
                    <div>
                      <strong>Cliente:</strong> {{ request.client?.name }}<br>
                      <strong>Ciudad:</strong> {{ request.service_city }}
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="btn-group-vertical d-grid gap-2">
                  <button 
                    @click="viewDetails(request)"
                    class="btn btn-outline-primary btn-sm"
                  >
                    <i class="fas fa-eye me-1"></i>
                    Ver Detalles
                  </button>
                  
                  <button 
                    @click="applyToRequest(request)"
                    class="btn btn-success btn-sm"
                  >
                    <i class="fas fa-hand-paper me-1"></i>
                    Postularme
                  </button>
                </div>
              </div>
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
  name: 'AvailableRequestsSection',
  
  data() {
    return {
      requests: [],
      loading: true,
      urgencyFilter: '',
      serviceTypeFilter: '',
      searchText: ''
    };
  },
  
  computed: {
    filteredRequests() {
      return this.requests.filter(request => {
        const matchesUrgency = !this.urgencyFilter || request.urgency_level === this.urgencyFilter;
        const matchesServiceType = !this.serviceTypeFilter || request.service_type === this.serviceTypeFilter;
        const matchesSearch = !this.searchText || 
          request.description.toLowerCase().includes(this.searchText.toLowerCase()) ||
          request.service_type.toLowerCase().includes(this.searchText.toLowerCase());
        
        return matchesUrgency && matchesServiceType && matchesSearch;
      });
    }
  },
  
  methods: {
    async loadRequests() {
      this.loading = true;
      
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('/service-requests/available/list', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.data.success) {
          this.requests = response.data.data.data || response.data.data;
        }
      } catch (error) {
        console.error('Error al cargar solicitudes:', error);
        
        if (error.response?.status === 403) {
          Swal.fire({
            title: 'Acceso Denegado',
            text: 'Solo los técnicos pueden ver las solicitudes disponibles.',
            icon: 'warning',
            confirmButtonText: 'Entendido'
          });
        } else {
          Swal.fire('Error', 'No se pudieron cargar las solicitudes', 'error');
        }
      } finally {
        this.loading = false;
      }
    },

    async viewDetails(request) {
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get(`/service-requests/${request.id}`, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.data.success) {
          const details = response.data.data;
          
          Swal.fire({
            title: `Solicitud #${details.request_number}`,
            html: `
              <div class="text-left">
                <p><strong>Cliente:</strong> ${details.client.name}</p>
                <p><strong>Tipo de Servicio:</strong> ${this.getServiceTypeLabel(details.service_type)}</p>
                <p><strong>Urgencia:</strong> <span class="badge ${this.getUrgencyBadgeClass(details.urgency_level)}">${this.getUrgencyLabel(details.urgency_level)}</span></p>
                <p><strong>Equipo:</strong> ${this.getEquipmentTypeLabel(details.equipment_type)} - ${details.equipment_brand}</p>
                <p><strong>Descripción:</strong> ${details.description}</p>
                <p><strong>Dirección:</strong> ${details.service_address}, ${details.service_city}</p>
                <p><strong>Fecha Preferida:</strong> ${details.preferred_date ? this.formatDate(details.preferred_date) : 'No especificada'}</p>
                ${details.client_notes ? `<p><strong>Notas del Cliente:</strong> ${details.client_notes}</p>` : ''}
              </div>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: 'Postularme',
            cancelButtonText: 'Cerrar',
            confirmButtonColor: '#28a745'
          }).then((result) => {
            if (result.isConfirmed) {
              this.applyToRequest(request);
            }
          });
        }
      } catch (error) {
        console.error('Error al obtener detalles:', error);
        Swal.fire('Error', 'No se pudieron obtener los detalles', 'error');
      }
    },

    async applyToRequest(request) {
      const { value: formValues } = await Swal.fire({
        title: 'Postularme a la Solicitud',
        html: `
          <div class="mb-3">
            <label for="quoted_price" class="form-label">Precio Cotizado ($)</label>
            <input type="number" id="quoted_price" class="form-control" min="0" step="0.01" required>
          </div>
          <div class="mb-3">
            <label for="technician_notes" class="form-label">Notas/Observaciones</label>
            <textarea id="technician_notes" class="form-control" rows="3" placeholder="Describe tu propuesta de trabajo..."></textarea>
          </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Enviar Cotización',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
          const quoted_price = document.getElementById('quoted_price').value;
          const technician_notes = document.getElementById('technician_notes').value;
          
          if (!quoted_price || quoted_price <= 0) {
            Swal.showValidationMessage('Por favor ingresa un precio válido');
            return false;
          }
          
          return {
            quoted_price: parseFloat(quoted_price),
            technician_notes: technician_notes
          };
        }
      });

      if (formValues) {
        try {
          const token = localStorage.getItem('token');
          const response = await axios.post(`/service-requests/${request.id}/apply`, formValues, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });

          if (response.data.success) {
            Swal.fire('¡Postulación Enviada!', 'Tu cotización ha sido enviada al cliente.', 'success');
            this.loadRequests(); // Recargar las solicitudes
            this.$emit('postulation-sent'); // Notificar al componente padre
          }
        } catch (error) {
          console.error('Error al postularse:', error);
          
          if (error.response?.status === 403) {
            Swal.fire('Acceso Denegado', 'Solo los técnicos pueden postularse a solicitudes.', 'warning');
          } else {
            Swal.fire('Error', 'No se pudo enviar la postulación', 'error');
          }
        }
      }
    },
    
    // Helper methods (mismos que otros componentes)
    getServiceTypeLabel(type) {
      const labels = {
        correctivo: 'Mantenimiento Correctivo',
        preventivo: 'Mantenimiento Preventivo',
        instalacion: 'Instalación',
        limpieza: 'Limpieza',
        reparacion: 'Reparación',
        asesoria: 'Asesoría',
        diagnostico: 'Diagnóstico',
        otro: 'Otro'
      };
      return labels[type] || type;
    },

    getUrgencyLabel(urgency) {
      const labels = {
        baja: 'Baja',
        media: 'Media',
        alta: 'Alta',
        critica: 'Crítica'
      };
      return labels[urgency] || urgency;
    },

    getUrgencyBadgeClass(urgency) {
      const classes = {
        baja: 'bg-secondary',
        media: 'bg-warning',
        alta: 'bg-orange',
        critica: 'bg-danger'
      };
      return classes[urgency] || 'bg-secondary';
    },

    getEquipmentTypeLabel(type) {
      const labels = {
        desktop: 'PC Escritorio',
        laptop: 'Laptop',
        server: 'Servidor',
        printer: 'Impresora',
        network: 'Red',
        mobile: 'Móvil',
        tablet: 'Tablet',
        scanner: 'Escáner',
        projector: 'Proyector',
        other: 'Otro'
      };
      return labels[type] || type;
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    
    formatDateTime(dateString) {
      return new Date(dateString).toLocaleString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }
  },
  
  mounted() {
    this.loadRequests();
  }
};
</script>

<style scoped>
.bg-orange {
  background-color: #fd7e14 !important;
}
</style>