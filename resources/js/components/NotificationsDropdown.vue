<template>
  <div class="dropdown">
    <!-- Icono de campana con contador de notificaciones -->
    <button 
      class="btn btn-link position-relative text-decoration-none" 
      type="button" 
      id="notificationsDropdown" 
      data-bs-toggle="dropdown" 
      aria-expanded="false"
    >
      <i class="bi bi-bell fs-5"></i>
      <span 
        v-if="unreadCount > 0" 
        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
        style="font-size: 0.6rem;"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>
    
    <!-- Dropdown de notificaciones -->
    <div 
      class="dropdown-menu dropdown-menu-end shadow-sm notification-dropdown" 
      aria-labelledby="notificationsDropdown"
      style="width: 320px; max-height: 400px; overflow-y: auto;"
    >
      <!-- Header de notificaciones -->
      <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
        <h6 class="mb-0">Notificaciones</h6>
        <button 
          class="btn btn-sm btn-link text-decoration-none" 
          @click.prevent="markAllAsRead"
          :disabled="loading || notifications.filter(n => !n.read_at).length === 0"
        >
          Marcar todas como leídas
        </button>
      </div>
      
      <!-- Listado de notificaciones -->
      <div v-if="loading" class="p-3 text-center">
        <div class="spinner-border spinner-border-sm text-primary" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
      </div>
      
      <div v-else-if="notifications.length === 0" class="p-3 text-center text-muted">
        <i class="bi bi-bell-slash mb-2" style="font-size: 1.5rem;"></i>
        <p class="mb-0">No tienes notificaciones</p>
      </div>
      
      <div v-else>
        <a 
          v-for="notification in notifications" 
          :key="notification.id" 
          href="#" 
          class="dropdown-item notification-item px-3 py-2 border-bottom text-decoration-none"
          :class="{'unread': !notification.read_at}"
          @click.prevent="openNotification(notification)"
        >
          <div class="d-flex">
            <!-- Icono de notificación según tipo -->
            <div class="me-3 notification-icon">
              <i :class="getNotificationIcon(notification.type)"></i>
            </div>
            
            <!-- Contenido de notificación -->
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between">
                <p class="notification-title mb-0" style="font-weight: 500;">{{ notification.title }}</p>
                <small class="text-muted ms-2">{{ formatTime(notification.created_at) }}</small>
              </div>
              <p class="notification-text mb-0 small text-truncate">{{ notification.message }}</p>
            </div>
          </div>
        </a>
        
        <div class="p-2 text-center border-top">
          <button class="btn btn-link btn-sm text-decoration-none" @click="viewAllNotifications">
            Ver todas
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

export default {
  name: 'NotificationsDropdown',
  
  data() {
    return {
      notifications: [],
      unreadCount: 0,
      loading: false
    };
  },
  
  methods: {
    async fetchNotifications() {
      this.loading = true;
      
      try {
        const token = localStorage.getItem('token');
        if (!token) {
          this.loading = false;
          return;
        }
        
        const response = await axios.get('/notifications', {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        if (response.data && (response.data.success || Array.isArray(response.data))) {
          // Manejar diferentes formatos de respuesta
          this.notifications = Array.isArray(response.data) 
            ? response.data 
            : (response.data.data || []);
          
          // Contar notificaciones no leídas
          this.unreadCount = this.notifications.filter(n => !n.read_at).length;
        }
      } catch (error) {
        console.error('Error al cargar notificaciones:', error);
        
        // Si la API aún no está implementada, usar datos de ejemplo 
        // para no romper la funcionalidad
        if (error.response && error.response.status === 404) {
          this.notifications = this.getDummyNotifications();
          this.unreadCount = this.notifications.filter(n => !n.read_at).length;
        }
      } finally {
        this.loading = false;
      }
    },
    
    async markAsRead(notification) {
      try {
        if (!notification.id) return;
        
        const token = localStorage.getItem('token');
        await axios.post(`/notifications/${notification.id}/mark-read`, {}, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        notification.read_at = new Date().toISOString();
        this.unreadCount = this.notifications.filter(n => !n.read_at).length;
      } catch (error) {
        console.error('Error al marcar notificación como leída:', error);
        
        // Si la API falla, marcar como leída localmente para mejorar UX
        if (error.response && (error.response.status === 404 || error.response.status === 405)) {
          notification.read_at = new Date().toISOString();
          this.unreadCount = this.notifications.filter(n => !n.read_at).length;
        }
      }
    },
    
    async markAllAsRead() {
      try {
        const token = localStorage.getItem('token');
        await axios.post('/notifications/mark-all-read', {}, {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        });
        
        this.notifications.forEach(notification => {
          notification.read_at = new Date().toISOString();
        });
        this.unreadCount = 0;
      } catch (error) {
        console.error('Error al marcar todas las notificaciones como leídas:', error);
        
        // Si la API falla, marcar todas como leídas localmente
        if (error.response && (error.response.status === 404 || error.response.status === 405)) {
          this.notifications.forEach(notification => {
            notification.read_at = new Date().toISOString();
          });
          this.unreadCount = 0;
        }
      }
    },
    
    openNotification(notification) {
      // Marcar como leída
      this.markAsRead(notification);
      
      // Mostrar detalles y redirigir si es necesario
      if (notification.link) {
        window.location.href = notification.link;
      } else if (notification.data && notification.data.url) {
        window.location.href = notification.data.url;
      } else {
        Swal.fire({
          title: notification.title || 'Notificación',
          text: notification.message || notification.content || '',
          icon: this.getNotificationSwalIcon(notification.type),
          confirmButtonText: 'Entendido'
        });
      }
    },
    
    viewAllNotifications() {
      const notificationsHtml = this.notifications.length === 0 
        ? '<p class="text-center">No tienes notificaciones</p>'
        : this.notifications.map(n => {
            return `
              <div class="notification-item p-3 ${!n.read_at ? 'bg-light' : ''}" style="border-bottom: 1px solid #eee;">
                <div class="d-flex">
                  <div class="me-3">
                    <i class="${this.getNotificationIcon(n.type)}"></i>
                  </div>
                  <div class="flex-grow-1 text-start">
                    <div class="d-flex justify-content-between">
                      <h6 class="mb-1">${n.title || 'Notificación'}</h6>
                      <small class="text-muted">${this.formatTime(n.created_at)}</small>
                    </div>
                    <p class="mb-0">${n.message || n.content || ''}</p>
                  </div>
                </div>
              </div>
            `;
          }).join('');

      Swal.fire({
        title: 'Todas las notificaciones',
        html: `<div class="notifications-list">${notificationsHtml}</div>`,
        width: '600px',
        confirmButtonText: 'Cerrar',
        showClass: {
          popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
          popup: 'animate__animated animate__fadeOutUp'
        }
      });
      
      // Marcar todas como leídas después de ver todas
      this.markAllAsRead();
    },
    
    getNotificationIcon(type) {
      const icons = {
        'service_request': 'bi bi-tools text-primary',
        'quote': 'bi bi-cash-coin text-success',
        'system': 'bi bi-info-circle text-info',
        'warning': 'bi bi-exclamation-triangle text-warning',
        'success': 'bi bi-check-circle text-success',
        'review': 'bi bi-star text-warning',
        'message': 'bi bi-chat-dots text-primary',
        'profile': 'bi bi-person text-primary'
      };
      return icons[type] || 'bi bi-bell text-secondary';
    },
    
    getNotificationSwalIcon(type) {
      const icons = {
        'service_request': 'info',
        'quote': 'success',
        'system': 'info',
        'warning': 'warning',
        'success': 'success',
        'review': 'success'
      };
      return icons[type] || 'info';
    },
    
    formatTime(timestamp) {
      if (!timestamp) return '';
      
      const now = new Date();
      const date = new Date(timestamp);
      const diffMs = now - date;
      const diffMins = Math.round(diffMs / 60000);
      const diffHours = Math.round(diffMs / 3600000);
      const diffDays = Math.round(diffMs / 86400000);
      
      if (diffMins < 60) {
        return `Hace ${diffMins} min`;
      } else if (diffHours < 24) {
        return `Hace ${diffHours} h`;
      } else if (diffDays < 7) {
        return `Hace ${diffDays} día${diffDays !== 1 ? 's' : ''}`;
      } else {
        return date.toLocaleDateString();
      }
    },
    
    getDummyNotifications() {
      // Datos de ejemplo para mostrar mientras no hay API real
      return [
        {
          id: 1,
          title: 'Nueva solicitud de servicio',
          message: 'Tu solicitud de mantenimiento correctivo ha sido creada correctamente.',
          type: 'service_request',
          read_at: null,
          created_at: new Date(Date.now() - 15 * 60000).toISOString(),
          data: { url: '/profile' }
        },
        {
          id: 2,
          title: 'Cotización recibida',
          message: 'Has recibido una nueva cotización para tu solicitud #SR-2025-000123.',
          type: 'quote',
          read_at: null,
          created_at: new Date(Date.now() - 3 * 3600000).toISOString(),
          data: { url: '/profile' }
        },
        {
          id: 3,
          title: 'Servicio completado',
          message: 'El técnico ha marcado tu servicio #SR-2025-000120 como completado.',
          type: 'success',
          read_at: new Date(Date.now() - 1 * 86400000).toISOString(),
          created_at: new Date(Date.now() - 2 * 86400000).toISOString(),
          data: { url: '/profile' }
        }
      ];
    }
  },
  
  mounted() {
    this.fetchNotifications();
    
    // Refrescar notificaciones al abrir el dropdown
    const dropdownElement = document.getElementById('notificationsDropdown');
    if (dropdownElement) {
      dropdownElement.addEventListener('show.bs.dropdown', () => {
        this.fetchNotifications();
      });
    }

    // Refrescar cada 2 minutos para mantener notificaciones actualizadas
    setInterval(() => {
      this.fetchNotifications();
    }, 120000); // 2 minutos
  }
};
</script>

<style scoped>
.notification-item {
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.notification-item:hover {
  background-color: #f8f9fa;
}

.notification-item.unread {
  background-color: #f0f7ff;
}

.notification-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  background-color: #f8f9fa;
  border-radius: 50%;
}

.notification-icon i {
  font-size: 1rem;
}

.notification-title {
  color: #212529;
  font-size: 0.875rem;
}

.notification-text {
  color: #6c757d;
}

.dropdown-menu.notification-dropdown {
  margin-top: 0.5rem;
  padding: 0;
  border-radius: 0.5rem;
}
</style>