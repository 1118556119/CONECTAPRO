<template>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Mis Solicitudes de Servicio</h5>
      <button @click="requestService" class="btn btn-success btn-sm">
        <i class="fas fa-plus me-1"></i>Solicitar Nuevo Servicio
      </button>
    </div>
    <div class="card-body">
      <div v-if="loading" class="text-center py-4">
        <div class="spinner-border text-primary"></div>
      </div>
      
      <div v-else-if="serviceRequests.length === 0" class="text-center py-5">
        <i class="fas fa-tools fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No tienes solicitudes de servicio</h5>
        <p class="text-muted">¡Solicita tu primer servicio técnico!</p>
        <button @click="requestService" class="btn btn-primary">
          <i class="fas fa-plus me-2"></i>Solicitar Servicio
        </button>
      </div>
      
      <div v-else>
        <div v-for="request in serviceRequests" :key="request.id" 
             class="card mb-3 border-start border-4"
             :class="getServiceBorderClass(request.status)">
          <div class="card-body">
            <div class="row">
              <div class="col-md-8">
                <h6 class="card-title">
                  {{ request.request_number }}
                  <span class="badge ms-2" :class="getServiceBadgeClass(request.status)">
                    {{ getServiceStatusLabel(request.status) }}
                  </span>
                </h6>
                <p class="card-text mb-2">
                  <strong>Servicio:</strong> {{ getServiceTypeLabel(request.service_type) }}<br>
                  <strong>Equipo:</strong> {{ request.equipment_brand }}<br>
                  <strong>Descripción:</strong> {{ request.description }}
                </p>
                <small class="text-muted">
                  <i class="fas fa-calendar me-1"></i>
                  {{ formatDate(request.created_at) }}
                </small>
              </div>
              <div class="col-md-4 text-end">
                <div class="mb-2">
                  <span v-if="request.quoted_price" class="h6 text-success">
                    ${{ request.quoted_price.toLocaleString() }}
                  </span>
                </div>
                <div class="btn-group-vertical d-grid gap-1">
                  <button @click="viewServiceDetails(request)" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-eye me-1"></i>Ver Detalles
                  </button>
                  <button v-if="request.assigned_technician" 
                          @click="viewTechnicianInfo(request.assigned_technician)" 
                          class="btn btn-outline-info btn-sm">
                    <i class="fas fa-user me-1"></i>Ver Técnico
                  </button>
                  <button v-if="request.status === 'completed' && !request.review" 
                          @click="leaveServiceReview(request)" 
                          class="btn btn-warning btn-sm">
                    <i class="fas fa-star me-1"></i>Calificar
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
export default {
  name: 'ServicesSection',
  
  props: {
    serviceRequests: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    }
  },

  emits: ['request-service', 'view-details', 'view-technician', 'leave-review'],

  methods: {
    requestService() {
      this.$emit('request-service');
    },

    viewServiceDetails(request) {
      this.$emit('view-details', request);
    },

    viewTechnicianInfo(technician) {
      this.$emit('view-technician', technician);
    },

    leaveServiceReview(request) {
      this.$emit('leave-review', request);
    },

    getServiceBorderClass(status) {
      const classes = {
        'pending': 'border-warning',
        'assigned': 'border-info',
        'in_progress': 'border-primary',
        'completed': 'border-success',
        'cancelled': 'border-danger'
      };
      return classes[status] || 'border-secondary';
    },

    getServiceBadgeClass(status) {
      const classes = {
        'pending': 'bg-warning',
        'assigned': 'bg-info',
        'in_progress': 'bg-primary',
        'completed': 'bg-success',
        'cancelled': 'bg-danger'
      };
      return classes[status] || 'bg-secondary';
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
        console.error('Error mostrando fecha:', error);
        return dateString;
      }
    }
  }
};
</script>