<template>
  <div>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm fixed-top">
      <div class="container">
        <a class="navbar-brand text-primary fw-bold" href="#">
          <i class="fas fa-tools"></i> CONECTAPRO
        </a>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
          aria-controls="navbarNav"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto">
            <!-- Enlaces de navegación principal -->
            <li class="nav-item">
              <a href="#servicios" class="nav-link">Servicios</a>
            </li>
            <li class="nav-item">
              <a href="#como-funciona" class="nav-link">Cómo Funciona</a>
            </li>
            <li class="nav-item">
              <a href="#faq" class="nav-link">Preguntas Frecuentes</a>
            </li>
            <li class="nav-item">
              <a href="#contacto" class="nav-link">Contacto</a>
            </li>
            
            <!-- Para usuarios autenticados - Solicitar servicio -->
            <li class="nav-item" v-if="isLoggedIn">
              <router-link to="/solicitar-servicio" class="nav-link btn btn-primary text-white px-3 mx-2">
                <i class="fas fa-plus-circle me-1"></i> Solicitar Servicio
              </router-link>
            </li>
          </ul>
          
          <!-- Botones de autenticación -->
          <ul class="navbar-nav ms-auto">
            <!-- Si el usuario NO está autenticado -->
            <template v-if="!isLoggedIn">
              <li class="nav-item">
                <router-link
                  to="/login"
                  class="btn btn-outline-primary me-2 d-flex align-items-center gap-2"
                >
                  <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                </router-link>
              </li>
              <li class="nav-item">
                <router-link
                  to="/register"
                  class="btn btn-outline-success d-flex align-items-center gap-2"
                >
                  <i class="fas fa-user-plus"></i> Registrarse
                </router-link>
              </li>
            </template>
            
            <!-- Si el usuario está autenticado -->
            <template v-else>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="fas fa-user-circle me-1"></i> Mi Cuenta
                  <!-- Badge de notificaciones -->
                  <span v-if="unreadNotifications > 0" class="badge bg-danger ms-1" style="font-size: 0.6rem;">
                    {{ unreadNotifications }}
                  </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                  <li>
                    <router-link to="/profile" class="dropdown-item">
                      <i class="fas fa-id-card me-2"></i> Mi Perfil
                      <span v-if="unreadNotifications > 0" class="badge bg-primary ms-2">{{ unreadNotifications }}</span>
                    </router-link>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <a class="dropdown-item text-danger" href="#" @click.prevent="logout">
                      <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                    </a>
                  </li>
                </ul>
              </li>
            </template>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container py-5 mt-5">
      <div class="text-center mb-5">
        <h1 class="display-4 fw-bold text-primary">
          Bienvenido al Servicio de Mantenimiento de Computadores
        </h1>
        <p class="lead text-secondary">
          Solicita servicios de mantenimiento correctivo, preventivo o instalación de programas a domicilio.
        </p>
        
        <!-- Botón destacado para solicitar servicio -->
        <div class="mt-4">
          <router-link 
            v-if="isLoggedIn" 
            to="/solicitar-servicio" 
            class="btn btn-accent btn-lg px-4 py-2 fw-bold shadow-sm"
          >
            <i class="fas fa-clipboard-check me-2"></i>
            Solicitar Servicio Ahora
          </router-link>
          
          <router-link 
            v-else 
            to="/register" 
            class="btn btn-accent btn-lg px-4 py-2 fw-bold shadow-sm"
          >
            <i class="fas fa-user-plus me-2"></i>
            Crear Cuenta Gratis
          </router-link>
        </div>

        <!-- Botones principales para usuarios autenticados -->
        <div class="mt-4" v-if="isLoggedIn">
          <!-- Para clientes -->
          <router-link 
            v-if="userType === 'client'" 
            to="/solicitar-servicio" 
            class="btn btn-primary btn-lg px-4 py-2 fw-bold shadow"
          >
            <i class="fas fa-clipboard-check me-2"></i>
            Solicitar Servicio Ahora
          </router-link>
          
          <!-- Para técnicos -->
          <router-link 
            v-if="userType === 'technician'" 
            to="/solicitudes-disponibles" 
            class="btn btn-success btn-lg px-4 py-2 fw-bold shadow"
          >
            <i class="fas fa-tools me-2"></i>
            Ver Solicitudes Disponibles
          </router-link>
        </div>
      </div>

      <!-- Tarjetas de servicios -->
      <div id="servicios" class="py-2 mt-5">
        <h2 class="text-center mb-4 fw-bold text-primary">
          <i class="fas fa-cogs me-2"></i> Nuestros Servicios
        </h2>
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card shadow-sm text-center h-100 service-card">
              <div class="card-body d-flex flex-column">
                <div class="icon-wrapper mb-3">
                  <i class="fas fa-wrench text-primary mb-3" style="font-size: 2rem;"></i>
                </div>
                <h3 class="card-title h5 fw-bold">Mantenimiento Correctivo</h3>
                <p class="card-text text-muted flex-grow-1">
                  Soluciona problemas técnicos y mejora el rendimiento de tu equipo cuando ya presenta fallas.
                </p>
                <div class="mt-3">
                  <router-link 
                    v-if="isLoggedIn" 
                    to="/solicitar-servicio" 
                    class="btn btn-outline-primary"
                  >
                    Solicitar Ahora
                  </router-link>
                  <router-link 
                    v-else 
                    to="/login" 
                    class="btn btn-outline-primary"
                  >
                    Iniciar Sesión
                  </router-link>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card shadow-sm text-center h-100 service-card">
              <div class="card-body d-flex flex-column">
                <div class="icon-wrapper mb-3">
                  <i class="fas fa-shield-alt text-primary mb-3" style="font-size: 2rem;"></i>
                </div>
                <h3 class="card-title h5 fw-bold">Mantenimiento Preventivo</h3>
                <p class="card-text text-muted flex-grow-1">
                  Evita problemas futuros con un mantenimiento regular que prolonga la vida útil de tu equipo.
                </p>
                <div class="mt-3">
                  <router-link 
                    v-if="isLoggedIn" 
                    to="/solicitar-servicio" 
                    class="btn btn-outline-primary"
                  >
                    Solicitar Ahora
                  </router-link>
                  <router-link 
                    v-else 
                    to="/login" 
                    class="btn btn-outline-primary"
                  >
                    Iniciar Sesión
                  </router-link>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card shadow-sm text-center h-100 service-card">
              <div class="card-body d-flex flex-column">
                <div class="icon-wrapper mb-3">
                  <i class="fas fa-download text-primary mb-3" style="font-size: 2rem;"></i>
                </div>
                <h3 class="card-title h5 fw-bold">Instalación de Programas</h3>
                <p class="card-text text-muted flex-grow-1">
                  Configura y optimiza tus programas esenciales para un mejor rendimiento de tu computador.
                </p>
                <div class="mt-3">
                  <router-link 
                    v-if="isLoggedIn" 
                    to="/solicitar-servicio" 
                    class="btn btn-outline-primary"
                  >
                    Solicitar Ahora
                  </router-link>
                  <router-link 
                    v-else 
                    to="/login" 
                    class="btn btn-outline-primary"
                  >
                    Iniciar Sesión
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Más Servicios -->
      <div class="mt-4">
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card shadow-sm text-center h-100 service-card">
              <div class="card-body d-flex flex-column">
                <i class="fas fa-network-wired text-primary mb-3" style="font-size: 2rem;"></i>
                <h3 class="card-title h5 fw-bold">Configuración de Redes</h3>
                <p class="card-text text-muted flex-grow-1">
                  Instalación y configuración de redes WiFi, cableado estructurado y optimización.
                </p>
                <div class="mt-3">
                  <router-link 
                    v-if="isLoggedIn" 
                    to="/solicitar-servicio" 
                    class="btn btn-outline-primary"
                  >
                    Solicitar Ahora
                  </router-link>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card shadow-sm text-center h-100 service-card">
              <div class="card-body d-flex flex-column">
                <i class="fas fa-shield-virus text-primary mb-3" style="font-size: 2rem;"></i>
                <h3 class="card-title h5 fw-bold">Seguridad Informática</h3>
                <p class="card-text text-muted flex-grow-1">
                  Protección contra virus, malware y configuración de backup de datos.
                </p>
                <div class="mt-3">
                  <router-link 
                    v-if="isLoggedIn" 
                    to="/solicitar-servicio" 
                    class="btn btn-outline-primary"
                  >
                    Solicitar Ahora
                  </router-link>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card shadow-sm text-center h-100 service-card">
              <div class="card-body d-flex flex-column">
                <i class="fas fa-laptop-medical text-primary mb-3" style="font-size: 2rem;"></i>
                <h3 class="card-title h5 fw-bold">Diagnóstico Especializado</h3>
                <p class="card-text text-muted flex-grow-1">
                  Detección y solución de fallos hardware/software con tecnología avanzada.
                </p>
                <div class="mt-3">
                  <router-link 
                    v-if="isLoggedIn" 
                    to="/solicitar-servicio" 
                    class="btn btn-outline-primary"
                  >
                    Solicitar Ahora
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Cómo Funciona -->
      <div id="como-funciona" class="py-5 bg-light mt-5">
        <div class="container">
          <h2 class="text-center mb-5 fw-bold text-primary">¿Cómo Funciona Nuestro Servicio?</h2>
          
          <div class="row g-4">
            <div class="col-md-3">
              <div class="text-center">
                <div class="circle-icon mb-3">
                  <i class="fas fa-calendar-check"></i>
                </div>
                <h4 class="h5 fw-bold">1. Agenda tu Servicio</h4>
                <p class="text-muted">Solicita un servicio a través de nuestra plataforma eligiendo fecha y hora.</p>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="text-center">
                <div class="circle-icon mb-3">
                  <i class="fas fa-user-check"></i>
                </div>
                <h4 class="h5 fw-bold">2. Te Asignamos un Técnico</h4>
                <p class="text-muted">Un técnico especializado será asignado según tus necesidades.</p>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="text-center">
                <div class="circle-icon mb-3">
                  <i class="fas fa-tools"></i>
                </div>
                <h4 class="h5 fw-bold">3. Servicio a Domicilio</h4>
                <p class="text-muted">El técnico acude a tu domicilio y realiza el mantenimiento requerido.</p>
              </div>
            </div>
            
            <div class="col-md-3">
              <div class="text-center">
                <div class="circle-icon mb-3">
                  <i class="fas fa-smile-beam"></i>
                </div>
                <h4 class="h5 fw-bold">4. Satisfacción Garantizada</h4>
                <p class="text-muted">Evalúa el servicio y disfruta de tu equipo en óptimas condiciones.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Beneficios -->
      <div class="container py-5">
        <h2 class="text-center mb-5 fw-bold text-primary">¿Por qué elegirnos?</h2>
        
        <div class="row g-4">
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body text-center">
                <i class="fas fa-clock text-primary mb-3" style="font-size: 2rem;"></i>
                <h3 class="h5 fw-bold">Respuesta Rápida</h3>
                <p class="card-text text-muted">Atendemos tu solicitud en menos de 24 horas.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body text-center">
                <i class="fas fa-certificate text-primary mb-3" style="font-size: 2rem;"></i>
                <h3 class="h5 fw-bold">Técnicos Certificados</h3>
                <p class="card-text text-muted">Profesionales con experiencia y certificaciones en el área.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body text-center">
                <i class="fas fa-hand-holding-usd text-primary mb-3" style="font-size: 2rem;"></i>
                <h3 class="h5 fw-bold">Precios Transparentes</h3>
                <p class="card-text text-muted">Sin costos ocultos, pagas exactamente lo que se te cotiza.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FAQ -->
      <div id="faq" class="container py-5">
        <h2 class="text-center mb-5 fw-bold text-primary">Preguntas Frecuentes</h2>
        
        <div class="accordion" id="accordionFAQ">
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                ¿Cuánto tiempo toma un mantenimiento preventivo?
              </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionFAQ">
              <div class="accordion-body">
                Un mantenimiento preventivo completo toma aproximadamente 1-2 horas, dependiendo del estado del equipo y la cantidad de datos a respaldar.
              </div>
            </div>
          </div>
          
          <div class="accordion-item">
            <h2 class="accordion-header" id="headingTwo">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                ¿Cómo puedo solicitar un servicio técnico?
              </button>
            </h2>
            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionFAQ">
              <div class="accordion-body">
                Para solicitar un servicio técnico, debes <router-link to="/register">crear una cuenta</router-link> o <router-link to="/login">iniciar sesión</router-link> si ya tienes una. Luego, dirígete a "Solicitar Servicio" y completa el formulario con los detalles de tu requerimiento.
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="py-5 bg-primary text-white">
        <div class="container text-center">
          <h2 class="fw-bold mb-3">¿Listo para mejorar el rendimiento de tu equipo?</h2>
          <p class="lead mb-4">Únete a miles de clientes satisfechos y agenda tu servicio ahora mismo.</p>
          
          <div class="d-flex justify-content-center gap-3">
            <router-link v-if="isLoggedIn" to="/solicitar-servicio" class="btn btn-light btn-lg fw-bold">
              Solicitar Servicio <i class="fas fa-arrow-right ms-2"></i>
            </router-link>
            <router-link v-else to="/register" class="btn btn-light btn-lg fw-bold">
              Crear Cuenta <i class="fas fa-arrow-right ms-2"></i>
            </router-link>
          </div>
        </div>
      </div>

      <!-- ✅ NUEVA SECCIÓN: PARA CLIENTES Y TÉCNICOS -->
      <div class="py-5 bg-light mt-5">
        <div class="container">
          <h2 class="text-center mb-5 fw-bold text-primary">
            <i class="fas fa-users me-2"></i> Una plataforma, dos perfiles
          </h2>
          
          <div class="row g-4">
            <!-- PARA CLIENTES -->
            <div class="col-md-6">
              <div class="card h-100 border-primary border-2 shadow-sm overflow-hidden">
                <div class="bg-primary text-white p-4 text-center">
                  <i class="fas fa-user-tie mb-2" style="font-size: 2.5rem;"></i>
                  <h3 class="h4 fw-bold">Para Clientes</h3>
                </div>
                <div class="card-body p-4">
                  <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                      <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <div class="ms-3">
                      <h4 class="h6 fw-bold mb-1">Encuentra técnicos certificados</h4>
                      <p class="text-muted mb-0">Accede a profesionales con experiencia verificada</p>
                    </div>
                  </div>
                  
                  <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                      <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <div class="ms-3">
                      <h4 class="h6 fw-bold mb-1">Solicita servicios a domicilio</h4>
                      <p class="text-muted mb-0">Sin moverte de casa, en el horario que prefieras</p>
                    </div>
                  </div>
                  
                  <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                      <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <div class="ms-3">
                      <h4 class="h6 fw-bold mb-1">Seguimiento en tiempo real</h4>
                      <p class="text-muted mb-0">Conoce el estado de tu solicitud en todo momento</p>
                    </div>
                  </div>
                  
                  <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                      <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <div class="ms-3">
                      <h4 class="h6 fw-bold mb-1">Califica el servicio</h4>
                      <p class="text-muted mb-0">Tu opinión ayuda a mantener la calidad del servicio</p>
                    </div>
                  </div>
                  
                  <div class="text-center mt-4">
                    <router-link 
                      v-if="!isLoggedIn" 
                      to="/register" 
                      class="btn btn-primary px-4 py-2 fw-bold"
                    >
                      <i class="fas fa-user-plus me-2"></i>Registrarme como Cliente
                    </router-link>
                    <router-link 
                      v-else 
                      to="/solicitar-servicio" 
                      class="btn btn-primary px-4 py-2 fw-bold"
                    >
                      <i class="fas fa-clipboard-check me-2"></i>Solicitar Servicio
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- PARA TÉCNICOS -->
            <div class="col-md-6">
              <div class="card h-100 border-success border-2 shadow-sm overflow-hidden">
                <div class="bg-success text-white p-4 text-center">
                  <i class="fas fa-user-cog mb-2" style="font-size: 2.5rem;"></i>
                  <h3 class="h4 fw-bold">Para Técnicos</h3>
                </div>
                <div class="card-body p-4">
                  <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                      <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <div class="ms-3">
                      <h4 class="h6 fw-bold mb-1">Consigue nuevos clientes</h4>
                      <p class="text-muted mb-0">Amplía tu cartera de clientes sin esfuerzo de ventas</p>
                    </div>
                  </div>
                  
                  <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                      <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <div class="ms-3">
                      <h4 class="h6 fw-bold mb-1">Gestiona tus servicios</h4>
                      <p class="text-muted mb-0">Organiza tu agenda y administra tus trabajos</p>
                    </div>
                  </div>
                  
                  <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                      <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <div class="ms-3">
                      <h4 class="h6 fw-bold mb-1">Construye tu reputación</h4>
                      <p class="text-muted mb-0">Recibe calificaciones y comentarios para destacarte</p>
                    </div>
                  </div>
                  
                  <div class="d-flex mb-3">
                    <div class="flex-shrink-0">
                      <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <div class="ms-3">
                      <h4 class="h6 fw-bold mb-1">Aumenta tus ingresos</h4>
                      <p class="text-muted mb-0">Recibe pagos seguros por cada servicio completado</p>
                    </div>
                  </div>
                  
                  <div class="text-center mt-4">
                    <router-link 
                      v-if="!isLoggedIn" 
                      to="/register-tech" 
                      class="btn btn-success px-4 py-2 fw-bold"
                    >
                      <i class="fas fa-user-plus me-2"></i>Registrarme como Técnico
                    </router-link>
                    <router-link 
                      v-else-if="userType === 'technician'" 
                      to="/solicitudes-disponibles" 
                      class="btn btn-success px-4 py-2 fw-bold"
                    >
                      <i class="fas fa-tools me-2"></i>Ver Solicitudes
                    </router-link>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ NUEVA SECCIÓN: ESTADÍSTICAS PARA MOTIVAR REGISTRO -->
      <div class="py-5 bg-white">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
              <h2 class="fw-bold text-primary mb-4">Únete a la comunidad de CONECTAPRO</h2>
              <p class="lead">Miles de clientes y técnicos ya están aprovechando nuestra plataforma para resolver sus necesidades informáticas.</p>
              
              <div class="d-flex flex-column gap-3 mt-4">
                <div class="d-flex align-items-center">
                  <div class="icon-box bg-primary text-white rounded p-2 me-3">
                    <i class="fas fa-shield-alt"></i>
                  </div>
                  <div>
                    <h4 class="h6 fw-bold mb-1">100% Seguro</h4>
                    <p class="text-muted mb-0">Tus datos personales están protegidos</p>
                  </div>
                </div>
                
                <div class="d-flex align-items-center">
                  <div class="icon-box bg-primary text-white rounded p-2 me-3">
                    <i class="fas fa-bolt"></i>
                  </div>
                  <div>
                    <h4 class="h6 fw-bold mb-1">Respuesta rápida</h4>
                    <p class="text-muted mb-0">Atención en menos de 24 horas</p>
                  </div>
                </div>
                
                <div class="d-flex align-items-center">
                  <div class="icon-box bg-primary text-white rounded p-2 me-3">
                    <i class="fas fa-star"></i>
                  </div>
                  <div>
                    <h4 class="h6 fw-bold mb-1">Servicio garantizado</h4>
                    <p class="text-muted mb-0">Satisfacción o te devolvemos tu dinero</p>
                  </div>
                </div>
              </div>
              
              <div class="mt-4">
                <router-link 
                  v-if="!isLoggedIn" 
                  to="/register" 
                  class="btn btn-primary btn-lg px-4 fw-bold me-2"
                >
                  <i class="fas fa-user-plus me-2"></i>Crear Cuenta
                </router-link>
                <a href="#servicios" class="btn btn-outline-primary btn-lg px-4">Ver Servicios</a>
              </div>
            </div>
            
            <div class="col-lg-6">
              <div class="row g-3 text-center">
                <div class="col-6">
                  <div class="card border-0 bg-light p-4">
                    <h3 class="display-4 fw-bold text-primary mb-0">2500+</h3>
                    <p class="text-muted mt-2">Clientes satisfechos</p>
                  </div>
                </div>
                <div class="col-6">
                  <div class="card border-0 bg-light p-4">
                    <h3 class="display-4 fw-bold text-success mb-0">150+</h3>
                    <p class="text-muted mt-2">Técnicos certificados</p>
                  </div>
                </div>
                <div class="col-6">
                  <div class="card border-0 bg-light p-4">
                    <h3 class="display-4 fw-bold text-info mb-0">5000+</h3>
                    <p class="text-muted mt-2">Servicios completados</p>
                  </div>
                </div>
                <div class="col-6">
                  <div class="card border-0 bg-light p-4">
                    <h3 class="display-4 fw-bold text-warning mb-0">4.8</h3>
                    <p class="text-muted mt-2">Valoración promedio</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ✅ NUEVA SECCIÓN: TESTIMONIOS -->
      <div class="py-5 bg-light">
        <div class="container">
          <h2 class="text-center mb-5 fw-bold text-primary">Lo que dicen nuestros usuarios</h2>
          
          <div class="row g-4">
            <!-- Testimonio Cliente -->
            <div class="col-md-4">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="d-flex mb-3">
                    <div class="text-warning">
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                    </div>
                  </div>
                  <p class="card-text text-muted mb-4">
                    "Mi computadora estaba muy lenta y necesitaba una solución urgente. 
                    En menos de 24 horas, un técnico vino a mi casa y resolvió todos los problemas. 
                    ¡Ahora funciona como nueva!"
                  </p>
                  <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                      <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" 
                           style="width: 50px; height: 50px;">
                        <span class="fw-bold">MC</span>
                      </div>
                    </div>
                    <div class="ms-3">
                      <h5 class="mb-0">María Castro</h5>
                      <p class="text-muted small mb-0">Cliente desde 2024</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Testimonio Cliente -->
            <div class="col-md-4">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="d-flex mb-3">
                    <div class="text-warning">
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star-half-alt"></i>
                    </div>
                  </div>
                  <p class="card-text text-muted mb-4">
                    "Necesitaba configurar una red para mi pequeña empresa. 
                    El técnico fue muy profesional y me explicó todo el proceso. 
                    Ahora tenemos una conexión estable y segura."
                  </p>
                  <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                      <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" 
                           style="width: 50px; height: 50px;">
                        <span class="fw-bold">JR</span>
                      </div>
                    </div>
                    <div class="ms-3">
                      <h5 class="mb-0">Juan Ramírez</h5>
                      <p class="text-muted small mb-0">Cliente desde 2023</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Testimonio Técnico -->
            <div class="col-md-4">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                  <div class="d-flex mb-3">
                    <div class="text-warning">
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                      <i class="fas fa-star"></i>
                    </div>
                  </div>
                  <p class="card-text text-muted mb-4">
                    "Como técnico independiente, CONECTAPRO me ha ayudado a encontrar 
                    nuevos clientes sin tener que invertir en publicidad. La plataforma 
                    es muy intuitiva y los pagos son puntuales."
                  </p>
                  <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                      <div class="bg-success rounded-circle d-flex align-items-center justify-content-center text-white" 
                           style="width: 50px; height: 50px;">
                        <span class="fw-bold">AT</span>
                      </div>
                    </div>
                    <div class="ms-3">
                      <h5 class="mb-0">Alex Torres</h5>
                      <p class="text-muted small mb-0">Técnico desde 2023</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pie de página -->
    <footer id="contacto" class="bg-white text-dark py-5 mt-5 shadow-sm border-top">
      <div class="container">
        <div class="row g-4">
          <!-- Logo y descripción -->
          <div class="col-lg-4 mb-4 mb-lg-0">
            <h5 class="fw-bold mb-3 text-primary">
              <i class="fas fa-tools me-2"></i> Mantenimiento de Computadores
            </h5>
            <p class="text-muted">
              Ofrecemos servicios profesionales de mantenimiento preventivo y correctivo para equipos de cómputo, con técnicos certificados y atención personalizada.
            </p>
            <div class="social-icons mt-3">
              <a href="#" class="text-primary me-3"><i class="fab fa-facebook-f"></i></a>
              <a href="#" class="text-primary me-3"><i class="fab fa-twitter"></i></a>
              <a href="#" class="text-primary me-3"><i class="fab fa-instagram"></i></a>
              <a href="#" class="text-primary"><i class="fab fa-linkedin-in"></i></a>
            </div>
          </div>
          
          <!-- Enlaces rápidos -->
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="fw-bold mb-3">Enlaces rápidos</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Inicio</a></li>
              <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Servicios</a></li>
              <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Sobre nosotros</a></li>
              <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Contacto</a></li>
              <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Preguntas frecuentes</a></li>
            </ul>
          </div>
          
          <!-- Servicios -->
          <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
            <h5 class="fw-bold mb-3">Servicios</h5>
            <ul class="list-unstyled">
              <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Mantenimiento preventivo</a></li>
              <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Mantenimiento correctivo</a></li>
              <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Instalación de software</a></li>
              <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Redes y conectividad</a></li>
              <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Seguridad informática</a></li>
            </ul>
          </div>
          
          <!-- Contacto -->
          <div class="col-lg-4 col-md-4">
            <h5 class="fw-bold mb-3">Contacto</h5>
            <ul class="list-unstyled">
              <li class="mb-2">
                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                <span class="text-muted">Av. Siempre Viva 123, Quito, Ecuador</span>
              </li>
              <li class="mb-2">
                <i class="fas fa-phone-alt text-primary me-2"></i>
                <span class="text-muted">+593 98 765 4321</span>
              </li>
              <li class="mb-2">
                <i class="fas fa-envelope text-primary me-2"></i>
                <span class="text-muted">info@mantenimiento.com</span>
              </li>
              <li class="mb-2">
                <i class="fas fa-clock text-primary me-2"></i>
                <span class="text-muted">Lun - Vie: 8:00 AM - 6:00 PM</span>
              </li>
            </ul>
          </div>
        </div>
        
        <!-- Línea divisora más suave -->
        <hr class="my-4 opacity-25">
        
        <!-- Parte inferior con copyright y políticas -->
        <div class="row align-items-center">
          <div class="col-md-6 text-center text-md-start">
            <p class="mb-0 text-muted small">
              &copy; 2025 Mantenimiento de Computadores. Todos los derechos reservados.
            </p>
          </div>
          <div class="col-md-6 text-center text-md-end">
            <ul class="list-inline mb-0">
              <li class="list-inline-item">
                <a href="#" class="text-muted small text-decoration-none">Términos y condiciones</a>
              </li>
              <li class="list-inline-item">
                <span class="text-muted">|</span>
              </li>
              <li class="list-inline-item">
                <a href="#" class="text-muted small text-decoration-none">Política de privacidad</a>
              </li>
              <li class="list-inline-item">
                <span class="text-muted">|</span>
              </li>
              <li class="list-inline-item">
                <a href="#" class="text-muted small text-decoration-none">Mapa del sitio</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
import Swal from 'sweetalert2';
import axios from 'axios';

export default {
  name: 'Home',
  data() {
    return {
      isLoggedIn: false,
      unreadNotifications: 0,
      userType: '' // Nuevo dato para el tipo de usuario
    };
  },
  methods: {
    checkLoginStatus() {
      const token = localStorage.getItem('token');
      this.isLoggedIn = !!token;
      
      // Si está logueado, verificar notificaciones y tipo de usuario
      if (this.isLoggedIn) {
        this.checkNotifications();
        this.getUserType();
      }
    },
    
    async checkNotifications() {
      try {
        const response = await axios.get('/notifications/count', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json'
          }
        });
        
        if (response.data && response.data.success) {
          this.unreadNotifications = response.data.unread_count || 0;
        }
      } catch (error) {
        console.error('Error al verificar notificaciones:', error);
        this.unreadNotifications = 0;
      }
    },
    
    async getUserType() {
      try {
        const response = await axios.get('/user/type', {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json'
          }
        });
        
        if (response.data && response.data.success) {
          this.userType = response.data.user_type || '';
        }
      } catch (error) {
        console.error('Error al obtener el tipo de usuario:', error);
        this.userType = '';
      }
    },
    
    logout() {
      Swal.fire({
        title: '¿Cerrar sesión?',
        text: '¿Estás seguro de que deseas cerrar sesión?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          // Eliminar el token y redirigir
          localStorage.removeItem('token');
          localStorage.removeItem('user_type');
          
          // Mostrar notificación
          const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
          });
          
          Toast.fire({
            icon: 'success',
            title: 'Has cerrado sesión exitosamente'
          });
          
          // Actualizar estado
          this.isLoggedIn = false;
          this.userType = ''; // Reiniciar tipo de usuario
        }
      });
    }
  },
  created() {
    this.checkLoginStatus();
  }
};
</script>

<style scoped>
/* Estilos para los círculos de "Cómo funciona" */
.circle-icon {
  width: 80px;
  height: 80px;
  background-color: rgba(0, 86, 179, 0.1); /* Versión semitransparente de --blue-tech */
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  font-size: 2rem;
  color: var(--blue-tech);
}

/* Gradiente para fondo de columna izquierda */
.bg-primary {
  background: linear-gradient(135deg, var(--blue-tech) 0%, var(--turquoise-accent) 100%) !important;
}

/* Tarjetas para Cliente/Técnico */
.border-primary.border-2 {
  border-top: 5px solid var(--blue-tech) !important;
}

.border-success.border-2 {
  border-top: 5px solid var(--green-service) !important;
}

/* CTA naranja destacado */
.btn-highlight {
  background-color: var(--orange-accent);
  border-color: var(--orange-accent);
  color: white;
}

.btn-highlight:hover {
  background-color: #e86c2a;
  border-color: #e86c2a;
  color: white;
}

/* Cards para estadísticas */
.card:hover .text-primary {
  color: var(--blue-tech) !important;
}

.card:hover .text-success {
  color: var(--green-service) !important;
}

.card:hover .text-info {
  color: var(--turquoise-accent) !important;
}

.card:hover .text-warning {
  color: var(--warning) !important;
}

/* Estilos para tarjetas de servicios */
.service-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: 1px solid rgba(0,0,0,0.05);
}

.service-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.icon-wrapper {
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Estilos para scrolling suave */
html {
  scroll-behavior: smooth;
}

/* Ajustes para dispositivos móviles */
@media (max-width: 768px) {
  .navbar-nav .btn {
    margin: 5px 0;
    width: 100%;
    justify-content: center;
  }
  
  .circle-icon {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
  }
}
</style>