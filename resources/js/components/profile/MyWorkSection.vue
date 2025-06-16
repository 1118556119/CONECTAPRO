<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4><i class="fas fa-tasks me-2 text-primary"></i>Mis Trabajos Asignados</h4>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Estado:</label>
            <select v-model="statusFilter" class="form-select form-select-sm">
              <option value="">Todos los estados</option>
              <option value="quoted">Cotizada</option>
              <option value="accepted">Aceptada</option>
              <option value="in_progress">En Progreso</option>
              <option value="completed">Completada</option>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label">Buscar:</label>
            <input 
              v-model="searchText" 
              type="text" 
              class="form-control form-control-sm" 
              placeholder="Buscar por cliente o descripción..."
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
      <p class="mt-2">Cargando tus trabajos...</p>
    </div>

    <!-- No hay trabajos -->
    <div v-else-if="filteredRequests.length === 0" class="text-center py-5">
      <i class="fas fa-briefcase fa-4x text-muted"></i>
      <h5 class="mt-3 text-muted">No tienes trabajos asignados</h5>
      <p class="text-muted">Cuando te asignen un trabajo, aparecerá aquí</p>
    </div>

    <!-- Lista de trabajos -->
    <div v-else class="row">
      <div v-for="request in filteredRequests" :key="request.id" class="col-12 mb-4">
        <div class="card h-100" :class="getStatusBorderClass(request.status)">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h6 class="mb-0">
                <i class="fas fa-ticket-alt me-2"></i>
                Solicitud #{{ request.request_number }}
              </h6>
              <small class="text-muted">
                Cliente: {{ request.client?.name }}
              </small>
            </div>
            <span class="badge" :class="getStatusBadgeClass(request.status)">
              {{ getStatusLabel(request.status) }}
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
                  <strong>Ubicación:</strong> {{ request.service_address }}, {{ request.service_city }}<br>
                  <strong>Descripción:</strong> {{ request.description.substring(0, 100) }}{{ request.description.length > 100 ? '...' : '' }}
                </p>

                <!-- Información del cliente -->
                <div class="alert alert-info py-2 px-3 small">
                  <div class="d-flex align-items-center">
                    <i class="fas fa-user me-2"></i>
                    <div>
                      <strong>Cliente:</strong> {{ request.client?.name }}<br>
                      <strong>Email:</strong> {{ request.client?.email }}<br>
                      <strong>Teléfono:</strong> {{ request.client?.phone || 'No disponible' }}
                    </div>
                  </div>
                </div>

                <!-- Precio cotizado -->
                <div v-if="request.quoted_price" class="alert alert-success py-2 px-3">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <i class="fas fa-dollar-sign me-1"></i>
                      <strong>Precio Cotizado: ${{ formatCurrency(request.quoted_price) }}</strong>
                    </div>
                  </div>
                  <div v-if="request.technician_notes" class="mt-2 small">
                    <strong>Tus notas:</strong> {{ request.technician_notes }}
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
                  
                  <!-- Acciones según el estado -->
                  <div v-if="request.status === 'quoted'">
                    <div class="alert alert-warning py-2 px-2 small mb-2">
                      <i class="fas fa-clock me-1"></i>
                      Esperando respuesta del cliente
                    </div>
                  </div>
                  
                  <div v-else-if="request.status === 'accepted'" class="d-grid gap-1">
                    <div class="alert alert-success py-2 px-2 small mb-2">
                      <i class="fas fa-check me-1"></i>
                      ¡Cotización aceptada!
                    </div>
                    <button 
                      @click="startWork(request)"
                      class="btn btn-success btn-sm"
                    >
                      <i class="fas fa-play me-1"></i>
                      Iniciar Trabajo
                    </button>
                  </div>
                  
                  <div v-else-if="request.status === 'in_progress'" class="d-grid gap-1">
                    <div class="alert alert-primary py-2 px-2 small mb-2">
                      <i class="fas fa-cog me-1"></i>
                      Trabajo en progreso
                    </div>
                    <button 
                      @click="completeWork(request)"
                      class="btn btn-primary btn-sm"
                    >
                      <i class="fas fa-check-circle me-1"></i>
                      Marcar Completado
                    </button>
                  </div>
                  
                  <div v-else-if="request.status === 'completed'">
                    <div class="alert alert-success py-2 px-2 small mb-2">
                      <i class="fas fa-check-circle me-1"></i>
                      Trabajo completado
                    </div>
                  </div>
                  
                  <!-- Contactar cliente -->
                  <button 
                    @click="contactClient(request)"
                    class="btn btn-outline-info btn-sm"
                  >
                    <i class="fas fa-phone me-1"></i>
                    Contactar Cliente
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
  name: 'MyWorkSection',
  
  data() {
    return {
      requests: [],
      loading: true,
      statusFilter: '',
      searchText: ''
    };
  },
  
  computed: {
    filteredRequests() {
      return this.requests.filter(request => {
        const matchesStatus = !this.statusFilter || request.status === this.statusFilter;
        const matchesSearch = !this.searchText || 
          request.description.toLowerCase().includes(this.searchText.toLowerCase()) ||
          request.client?.name.toLowerCase().includes(this.searchText.toLowerCase()) ||
          request.service_type.toLowerCase().includes(this.searchText.toLowerCase());
        
        return matchesStatus && matchesSearch;
      });
    }
  },
  
  methods: {
    async loadRequests() {
      this.loading = true;
      
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('/service-requests/my-assigned/list', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.data.success) {
          this.requests = response.data.data.data || response.data.data;
        }
      } catch (error) {
        console.error('Error al cargar trabajos asignados:', error);
        
        if (error.response?.status === 403) {
          Swal.fire({
            title: 'Acceso Denegado',
            text: 'Solo los técnicos pueden ver sus trabajos asignados.',
            icon: 'warning',
            confirmButtonText: 'Entendido'
          });
        } else {
          Swal.fire('Error', 'No se pudieron cargar los trabajos asignados', 'error');
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
                <p><strong>Estado:</strong> <span class="badge ${this.getStatusBadgeClass(details.status)}">${this.getStatusLabel(details.status)}</span></p>
                <p><strong>Cliente:</strong> ${details.client.name}</p>
                <p><strong>Email:</strong> ${details.client.email}</p>
                <p><strong>Teléfono:</strong> ${details.client.phone || 'No disponible'}</p>
                <p><strong>Tipo de Servicio:</strong> ${this.getServiceTypeLabel(details.service_type)}</p>
                <p><strong>Urgencia:</strong> <span class="badge ${this.getUrgencyBadgeClass(details.urgency_level)}">${this.getUrgencyLabel(details.urgency_level)}</span></p>
                <p><strong>Equipo:</strong> ${this.getEquipmentTypeLabel(details.equipment_type)} - ${details.equipment_brand}</p>
                <p><strong>Descripción:</strong> ${details.description}</p>
                <p><strong>Dirección:</strong> ${details.service_address}, ${details.service_city}</p>
                <p><strong>Fecha Preferida:</strong> ${details.preferred_date ? this.formatDate(details.preferred_date) : 'No especificada'}</p>
                ${details.client_notes ? `<p><strong>Notas del Cliente:</strong> ${details.client_notes}</p>` : ''}
                ${details.quoted_price ? `<p><strong>Precio Cotizado:</strong> $${this.formatCurrency(details.quoted_price)}</p>` : ''}
                ${details.technician_notes ? `<p><strong>Tus Notas:</strong> ${details.technician_notes}</p>` : ''}
              </div>
            `,
            width: '700px',
            confirmButtonText: 'Cerrar'
          });
        }
      } catch (error) {
        console.error('Error al obtener detalles:', error);
        Swal.fire('Error', 'No se pudieron obtener los detalles', 'error');
      }
    },

    async startWork(request) {
      const result = await Swal.fire({
        title: '¿Iniciar Trabajo?',
        html: `
          <div class="text-center">
            <i class="fas fa-play-circle fa-3x text-success mb-3"></i>
            <p>¿Estás listo para iniciar el trabajo en:</p>
            <div class="alert alert-info">
              <strong>${this.getServiceTypeLabel(request.service_type)}</strong><br>
              Cliente: ${request.client?.name}<br>
              Dirección: ${request.service_address}
            </div>
            <p class="text-muted small">
              Al iniciar el trabajo, el cliente será notificado y podrá seguir el progreso.
            </p>
          </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, iniciar trabajo',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#28a745'
      });

      if (result.isConfirmed) {
        try {
          const token = localStorage.getItem('token');
          const response = await axios.post(`/service-requests/${request.id}/start`, {}, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });

          if (response.data.success) {
            Swal.fire('¡Trabajo Iniciado!', 'El trabajo ha sido iniciado correctamente.', 'success');
            this.loadRequests(); // Recargar la lista
          }
        } catch (error) {
          console.error('Error al iniciar trabajo:', error);
          Swal.fire('Error', 'No se pudo iniciar el trabajo.', 'error');
        }
      }
    },

    async completeWork(request) {
      const { value: formValues } = await Swal.fire({
        title: 'Completar Trabajo',
        html: `
          <div class="mb-3">
            <label for="final_cost" class="form-label">Costo Final ($)</label>
            <input type="number" id="final_cost" class="form-control" min="0" step="0.01" value="${request.quoted_price || ''}" required>
          </div>
          <div class="mb-3">
            <label for="completion_notes" class="form-label">Notas de Finalización</label>
            <textarea id="completion_notes" class="form-control" rows="3" placeholder="Describe el trabajo realizado..."></textarea>
          </div>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Marcar Completado',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
          const final_cost = document.getElementById('final_cost').value;
          const completion_notes = document.getElementById('completion_notes').value;
          
          if (!final_cost || final_cost <= 0) {
            Swal.showValidationMessage('Por favor ingresa un costo válido');
            return false;
          }
          
          return {
            final_cost: parseFloat(final_cost),
            completion_notes: completion_notes
          };
        }
      });

      if (formValues) {
        try {
          const token = localStorage.getItem('token');
          const response = await axios.post(`/service-requests/${request.id}/complete`, formValues, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });

          if (response.data.success) {
            Swal.fire('¡Trabajo Completado!', 'El trabajo ha sido marcado como completado.', 'success');
            this.loadRequests(); // Recargar la lista
          }
        } catch (error) {
          console.error('Error al completar trabajo:', error);
          Swal.fire('Error', 'No se pudo completar el trabajo.', 'error');
        }
      }
    },

    contactClient(request) {
      const client = request.client;
      if (!client) {
        Swal.fire('Error', 'No se encontró información del cliente', 'error');
        return;
      }

      Swal.fire({
        title: `Contactar a ${client.name}`,
        html: `
          <div class="text-left">
            <p><strong>Teléfono:</strong> ${client.phone || 'No disponible'}</p>
            <p><strong>Email:</strong> ${client.email}</p>
            <p><strong>Dirección del servicio:</strong> ${request.service_address}</p>
          </div>
          <div class="d-grid gap-2 mt-3">
            ${client.phone ? `
              <a href="tel:${client.phone}" class="btn btn-success">
                <i class="fas fa-phone me-1"></i>Llamar
              </a>
              <a href="https://wa.me/${client.phone.replace(/\D/g, '')}" target="_blank" class="btn btn-success">
                <i class="fab fa-whatsapp me-1"></i>WhatsApp
              </a>
            ` : ''}
            <a href="mailto:${client.email}" class="btn btn-primary">
              <i class="fas fa-envelope me-1"></i>Enviar Email
            </a>
          </div>
        `,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Cerrar'
      });
    },

    // Helper methods (mismos que otros componentes)
    getStatusLabel(status) {
      const labels = {
        pending: 'Pendiente',
        quoted: 'Cotizada',
        accepted: 'Aceptada',
        in_progress: 'En Progreso',
        completed: 'Completada',
        cancelled: 'Cancelada'
      };
      return labels[status] || status;
    },
    
    getStatusBadgeClass(status) {
      const classes = {
        pending: 'bg-warning text-dark',
        quoted: 'bg-info text-white',
        accepted: 'bg-success text-white',
        in_progress: 'bg-primary text-white',
        completed: 'bg-success text-white',
        cancelled: 'bg-danger text-white'
      };
      return classes[status] || 'bg-secondary';
    },

    getStatusBorderClass(status) {
      const classes = {
        pending: 'border-warning',
        quoted: 'border-info',
        accepted: 'border-success',
        in_progress: 'border-primary',
        completed: 'border-success',
        cancelled: 'border-danger'
      };
      return classes[status] || 'border-secondary';
    },

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
    
    formatCurrency(amount) {
      return new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(amount);
    }
  },
  
  mounted() {
    this.loadRequests();
  }
};
</script>

<style scoped>
.border-orange {
  border-color: #fd7e14 !important;
}

.bg-orange {
  background-color: #fd7e14 !important;
}
</style>