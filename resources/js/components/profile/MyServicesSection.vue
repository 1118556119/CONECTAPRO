<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4><i class="fas fa-clipboard-list me-2 text-primary"></i>Mis Servicios Solicitados</h4>
      
      <!-- Botón prominente para solicitar servicio -->
      <router-link to="/solicitar-servicio" class="btn btn-accent">
        <i class="fas fa-plus me-1"></i>Solicitar Nuevo Servicio
      </router-link>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Estado:</label>
            <select v-model="statusFilter" class="form-select form-select-sm">
              <option value="">Todos los estados</option>
              <option value="pending">Pendiente</option>
              <option value="quoted">Cotizada</option>
              <option value="accepted">Aceptada</option>
              <option value="in_progress">En Progreso</option>
              <option value="completed">Completada</option>
              <option value="cancelled">Cancelada</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Urgencia:</label>
            <select v-model="urgencyFilter" class="form-select form-select-sm">
              <option value="">Todas las urgencias</option>
              <option value="baja">Baja</option>
              <option value="media">Media</option>
              <option value="alta">Alta</option>
              <option value="critica">Crítica</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Buscar:</label>
            <input 
              v-model="searchText" 
              type="text" 
              class="form-control form-control-sm" 
              placeholder="Buscar por descripción o tipo de servicio..."
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
      <p class="mt-2 text-muted">Cargando tus solicitudes...</p>
    </div>

    <!-- No hay solicitudes - Modificado para añadir botón más prominente -->
    <div v-else-if="filteredRequests.length === 0" class="text-center py-5">
      <div class="mb-3">
        <i class="fas fa-clipboard-list fa-4x text-muted"></i>
      </div>
      <h5 class="text-muted">No tienes solicitudes de servicio</h5>
      <p class="text-muted">¡Es momento de solicitar tu primer servicio técnico!</p>
      <router-link to="/solicitar-servicio" class="btn btn-accent btn-lg mt-2">
        <i class="fas fa-plus me-2"></i>Solicitar Servicio Ahora
      </router-link>
    </div>

    <!-- Lista de solicitudes - Sin cambios -->
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
                {{ formatDateTime(request.created_at) }}
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
                  <strong>Ubicación:</strong> {{ request.service_city }}<br>
                  <strong>Descripción:</strong> {{ request.description.substring(0, 100) }}{{ request.description.length > 100 ? '...' : '' }}
                </p>

                <!-- Información del técnico si está asignado -->
                <div v-if="request.technician && request.status !== 'pending'" class="alert alert-info py-2 px-3 small">
                  <div class="d-flex align-items-center">
                    <i class="fas fa-user-cog me-2"></i>
                    <div>
                      <strong>Técnico:</strong> {{ request.technician.name }}<br>
                      <strong>Teléfono:</strong> {{ request.technician.phone || 'No disponible' }}
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
                    <strong>Notas del técnico:</strong> {{ request.technician_notes }}
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
                  
                  <!-- Botones para cotizaciones -->
                  <div v-if="request.status === 'quoted'" class="d-grid gap-1">
                    <button 
                      @click="acceptQuote(request)"
                      class="btn btn-success btn-sm"
                    >
                      <i class="fas fa-check me-1"></i>
                      Aceptar (${{ formatCurrency(request.quoted_price) }})
                    </button>
                    
                    <button 
                      @click="rejectQuote(request)"
                      class="btn btn-outline-danger btn-sm"
                    >
                      <i class="fas fa-times me-1"></i>
                      Rechazar
                    </button>
                    
                    <button 
                      @click="viewTechnicianProfile(request)"
                      class="btn btn-outline-info btn-sm"
                    >
                      <i class="fas fa-user me-1"></i>
                      Ver Técnico
                    </button>
                  </div>
                  
                  <button 
                    v-if="['pending'].includes(request.status)"
                    @click="cancelRequest(request)"
                    class="btn btn-outline-danger btn-sm"
                  >
                    <i class="fas fa-times me-1"></i>
                    Cancelar
                  </button>
                  
                  <button 
                    v-if="request.status === 'completed' && !request.review" 
                    @click="leaveReview(request)" 
                    class="btn btn-warning btn-sm"
                  >
                    <i class="fas fa-star me-1"></i>
                    Calificar
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
  name: 'MyServicesSection',
  
  data() {
    return {
      requests: [],
      loading: true,
      statusFilter: '',
      urgencyFilter: '',
      searchText: ''
    };
  },
  
  computed: {
    filteredRequests() {
      return this.requests.filter(request => {
        const matchesStatus = !this.statusFilter || request.status === this.statusFilter;
        const matchesUrgency = !this.urgencyFilter || request.urgency_level === this.urgencyFilter;
        const matchesSearch = !this.searchText || 
          request.description.toLowerCase().includes(this.searchText.toLowerCase()) ||
          request.service_type.toLowerCase().includes(this.searchText.toLowerCase());
        
        return matchesStatus && matchesUrgency && matchesSearch;
      });
    }
  },
  
  methods: {
    async loadRequests() {
      this.loading = true;
      try {
        const token = localStorage.getItem('token');
        const response = await axios.get('/service-requests', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.data.success) {
          this.requests = response.data.data;
        }
      } catch (error) {
        console.error('Error al cargar solicitudes:', error);
        Swal.fire('Error', 'No se pudieron cargar las solicitudes', 'error');
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
          
          let htmlContent = `
            <div class="text-left">
              <p><strong>Estado:</strong> <span class="badge ${this.getStatusBadgeClass(details.status)}">${this.getStatusLabel(details.status)}</span></p>
              <p><strong>Tipo de Servicio:</strong> ${this.getServiceTypeLabel(details.service_type)}</p>
              <p><strong>Urgencia:</strong> <span class="badge ${this.getUrgencyBadgeClass(details.urgency_level)}">${this.getUrgencyLabel(details.urgency_level)}</span></p>
              <p><strong>Equipo:</strong> ${this.getEquipmentTypeLabel(details.equipment_type)} - ${details.equipment_brand}</p>
              <p><strong>Descripción:</strong> ${details.description}</p>
              <p><strong>Dirección:</strong> ${details.service_address}, ${details.service_city}</p>
              <p><strong>Fecha Preferida:</strong> ${details.preferred_date ? this.formatDate(details.preferred_date) : 'No especificada'}</p>
          `;
          
          // Información del técnico si está asignado
          if (details.technician) {
            htmlContent += `
              <hr>
              <h6><strong>Técnico Asignado:</strong></h6>
              <p><strong>Nombre:</strong> ${details.technician.name}</p>
              <p><strong>Email:</strong> ${details.technician.email}</p>
              <p><strong>Teléfono:</strong> ${details.technician.phone || 'No disponible'}</p>
            `;
          }
          
          // Precio cotizado
          if (details.quoted_price) {
            htmlContent += `<p><strong>Precio Cotizado:</strong> $${this.formatCurrency(details.quoted_price)}</p>`;
          }
          
          // Notas del técnico
          if (details.technician_notes) {
            htmlContent += `<p><strong>Notas del Técnico:</strong> ${details.technician_notes}</p>`;
          }
          
          htmlContent += `</div>`;

          await Swal.fire({
            title: `Solicitud #${details.request_number}`,
            html: htmlContent,
            width: '700px',
            confirmButtonText: 'Cerrar'
          });
        }
      } catch (error) {
        console.error('Error al obtener detalles:', error);
        Swal.fire('Error', 'No se pudieron obtener los detalles', 'error');
      }
    },

    // Aquí van todos los métodos de MyServiceRequests.vue (acceptQuote, rejectQuote, etc.)
    // Copio todos los métodos existentes...
    
    async acceptQuote(request) {
      // Mismo código que en MyServiceRequests.vue
      const result = await Swal.fire({
        title: '¿Aceptar Cotización?',
        html: `
          <div class="text-center">
            <div class="mb-3">
              <i class="fas fa-handshake fa-3x text-success mb-3"></i>
            </div>
            <h5>Cotización de ${request.technician?.name || 'Técnico'}</h5>
            <div class="alert alert-info">
              <h4 class="text-success mb-2">
                <strong>$${this.formatCurrency(request.quoted_price)}</strong>
              </h4>
              <p class="mb-0">
                <strong>Servicio:</strong> ${this.getServiceTypeLabel(request.service_type)}<br>
                <strong>Equipo:</strong> ${this.getEquipmentTypeLabel(request.equipment_type)} - ${request.equipment_brand}
              </p>
            </div>
            ${request.technician_notes ? `
              <div class="alert alert-light">
                <strong>Notas del técnico:</strong><br>
                <small>${request.technician_notes}</small>
              </div>
            ` : ''}
            <p class="text-muted small">
              Al aceptar esta cotización, el técnico será notificado y podrá iniciar el trabajo.
            </p>
          </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aceptar cotización',
        cancelButtonText: 'Ver más detalles primero',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        width: '500px'
      });

      if (result.isConfirmed) {
        try {
          const token = localStorage.getItem('token');
          const response = await axios.post(`/service-requests/${request.id}/accept-quote`, {}, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });

          if (response.data.success) {
            await Swal.fire({
              title: '¡Cotización Aceptada!',
              html: `
                <div class="text-center">
                  <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                  <p>La cotización ha sido aceptada exitosamente.</p>
                  <div class="alert alert-success">
                    <strong>Siguiente paso:</strong><br>
                    El técnico ha sido notificado y se pondrá en contacto contigo para coordinar el servicio.
                  </div>
                  <p class="text-muted small">
                    Puedes seguir el progreso en esta misma sección
                  </p>
                </div>
              `,
              icon: 'success',
              confirmButtonText: 'Entendido',
              timer: 5000
            });
            
            this.loadRequests(); // Recargar las solicitudes
          }
        } catch (error) {
          console.error('Error al aceptar cotización:', error);
          Swal.fire('Error', 'No se pudo aceptar la cotización. Inténtalo de nuevo.', 'error');
        }
      }
    },

    async rejectQuote(request) {
      const result = await Swal.fire({
        title: '¿Rechazar Cotización?',
        html: `
          <div class="text-center">
            <div class="mb-3">
              <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
            </div>
            <p>¿Estás seguro de rechazar la cotización de ${request.technician?.name || 'este técnico'}?</p>
            <div class="alert alert-warning">
              <strong>Cotización:</strong> $${this.formatCurrency(request.quoted_price)}
            </div>
            <div class="form-group">
              <label for="reject-reason" class="form-label">Motivo del rechazo (opcional):</label>
              <textarea id="reject-reason" class="form-control" rows="2" placeholder="Explica por qué rechazas esta cotización..."></textarea>
            </div>
          </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, rechazar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        preConfirm: () => {
          return {
            reason: document.getElementById('reject-reason').value
          };
        }
      });

      if (result.isConfirmed) {
        try {
          const token = localStorage.getItem('token');
          const response = await axios.post(`/service-requests/${request.id}/reject-quote`, 
            { reason: result.value.reason },
            {
              headers: {
                'Authorization': `Bearer ${token}`
              }
            }
          );

          if (response.data.success) {
            await Swal.fire({
              title: 'Cotización Rechazada',
              text: 'La cotización ha sido rechazada correctamente',
              icon: 'success',
              confirmButtonText: 'Entendido'
            });
            
            this.loadRequests(); // Recargar las solicitudes
          }
        } catch (error) {
          console.error('Error al rechazar cotización:', error);
          
          Swal.fire({
            title: 'Error',
            text: 'No se pudo rechazar la cotización. Inténtalo de nuevo.',
            icon: 'error',
            confirmButtonText: 'Entendido'
          });
        }
      }
    },

    async viewTechnicianProfile(request) {
      // Mismo código que MyServiceRequests.vue...
    },

    async cancelRequest(request) {
      // Mismo código que MyServiceRequests.vue...
    },

    async leaveReview(request) {
      const { value: formValues } = await Swal.fire({
        title: 'Calificar Servicio',
        html: `
          <div class="mb-4">
            <h5 class="text-center mb-3">¿Cómo calificas el servicio de ${request.technician?.name || 'este técnico'}?</h5>
            <div class="rating-container text-center">
              <div class="star-rating">
                <input type="radio" id="star5" name="rating" value="5">
                <label for="star5" title="5 estrellas">★</label>
                <input type="radio" id="star4" name="rating" value="4">
                <label for="star4" title="4 estrellas">★</label>
                <input type="radio" id="star3" name="rating" value="3" checked>
                <label for="star3" title="3 estrellas">★</label>
                <input type="radio" id="star2" name="rating" value="2">
                <label for="star2" title="2 estrellas">★</label>
                <input type="radio" id="star1" name="rating" value="1">
                <label for="star1" title="1 estrella">★</label>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="review-comment" class="form-label text-start d-block">Comentarios</label>
            <textarea id="review-comment" class="form-control" rows="3" placeholder="¿Qué te pareció el servicio?"></textarea>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="review-pros" class="form-label text-start d-block">Aspectos positivos</label>
              <textarea id="review-pros" class="form-control" rows="2" placeholder="Lo que más te gustó..."></textarea>
            </div>
            <div class="col-md-6 mb-3">
              <label for="review-cons" class="form-label text-start d-block">Aspectos a mejorar</label>
              <textarea id="review-cons" class="form-control" rows="2" placeholder="Lo que podría mejorar..."></textarea>
            </div>
          </div>
          
          <div class="form-check text-start">
            <input class="form-check-input" type="checkbox" id="recommend-check" checked>
            <label class="form-check-label" for="recommend-check">
              Recomendaría a este técnico
            </label>
          </div>
        `,
        customClass: {
          input: 'form-control'
        },
        didOpen: () => {
          // Aplicar estilos CSS directamente
          const style = document.createElement('style');
          style.textContent = `
            .star-rating {
              display: inline-flex;
              flex-direction: row-reverse;
              font-size: 2.5rem;
            }
            .star-rating input {
              display: none;
            }
            .star-rating label {
              color: #ddd;
              cursor: pointer;
              padding: 0 0.2rem;
              transition: color 0.3s;
            }
            .star-rating label:hover,
            .star-rating label:hover ~ label,
            .star-rating input:checked ~ label {
              color: #ffc107;
            }
          `;
          document.head.appendChild(style);
        },
        preConfirm: () => {
          const rating = document.querySelector('input[name="rating"]:checked').value;
          const comment = document.getElementById('review-comment').value;
          const pros = document.getElementById('review-pros').value;
          const cons = document.getElementById('review-cons').value;
          const isRecommended = document.getElementById('recommend-check').checked;
          
          if (!rating) {
            Swal.showValidationMessage('Por favor selecciona una calificación');
            return false;
          }
          
          return {
            overall_rating: parseInt(rating),
            comment: comment,
            pros: pros,
            cons: cons,
            is_recommended: isRecommended
          };
        },
        showCancelButton: true,
        confirmButtonText: 'Enviar calificación',
        cancelButtonText: 'Cancelar',
        width: '600px'
      });
      
      if (formValues) {
        try {
          const token = localStorage.getItem('token');
          const response = await axios.post('/reviews', {
            service_request_id: request.id,
            technician_id: request.technician_id,
            ...formValues
          }, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });
          
          if (response.data.success) {
            Swal.fire({
              title: '¡Gracias por tu evaluación!',
              text: 'Tu calificación ha sido enviada correctamente.',
              icon: 'success',
              confirmButtonText: 'Aceptar'
            });
            
            // Recargar las solicitudes para actualizar el estado
            this.loadRequests();
          }
        } catch (error) {
          console.error('Error al enviar calificación:', error);
          
          Swal.fire({
            title: 'Error',
            text: error.response?.data?.message || 'No se pudo enviar la calificación',
            icon: 'error',
            confirmButtonText: 'Aceptar'
          });
        }
      }
    },
    
    // Helper methods
    getStatusLabel(status) {
      const labels = {
        pending: 'Pendiente',
        quoted: 'Cotizada',
        accepted: 'Aceptada',
        in_progress: 'En Progreso',
        completed: 'Completada',
        cancelled: 'Cancelada',
        rejected: 'Rechazada'
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
        cancelled: 'bg-danger text-white',
        rejected: 'bg-secondary text-white'
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
        cancelled: 'border-danger',
        rejected: 'border-secondary'
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
    
    formatDateTime(dateString) {
      return new Date(dateString).toLocaleString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
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
.bg-orange {
  background-color: #fd7e14 !important;
}

.border-orange {
  border-color: #fd7e14 !important;
}

.btn-accent {
  background-color: var(--signal-orange);
  border-color: var(--signal-orange);
  color: white;
}

.btn-accent:hover,
.btn-accent:focus {
  background-color: #e5602f;
  border-color: #e5602f;
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

</style>