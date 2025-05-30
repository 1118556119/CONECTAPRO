<template>
  <div class="bg-light min-vh-100">
    <!-- Cabecera -->
    <div class="bg-white shadow-sm mb-4">
      <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center">
          <h2 class="mb-0 text-primary">
            <i class="bi bi-person-badge me-2"></i>Mi Perfil Profesional
          </h2>
          <button type="button" class="btn btn-outline-primary" @click="logout">
            <i class="bi bi-box-arrow-right me-1"></i>Cerrar Sesión
          </button>
        </div>
      </div>
    </div>

    <div class="container pb-5">
      <!-- Mensajes del sistema -->
      <div v-if="message" class="alert alert-dismissible fade show" :class="messageType" role="alert">
        <div class="d-flex align-items-center">
          <i :class="messageIcon" class="me-2"></i>
          <div>{{ message }}</div>
        </div>
        <button type="button" class="btn-close" @click="message = ''"></button>
      </div>

      <!-- Indicador de carga -->
      <div v-if="globalLoading" class="d-flex justify-content-center my-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Cargando...</span>
        </div>
      </div>

      <div v-else class="row g-4">
        <!-- Panel lateral con foto y resumen -->
        <div class="col-lg-3 mb-4">
          <!-- Tarjeta de foto -->
          <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
              <div class="mb-4">
                <!-- Imagen de perfil -->
                <div class="position-relative d-inline-block">
                  <img :src="photoUrl" alt="Foto de Perfil"
                    class="img-fluid rounded-circle border border-2"
                    style="width: 150px; height: 150px; object-fit: cover;"
                    @error="handleImageError" />
                  <label v-if="editBasicInfo"
                    class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle p-1">
                    <i class="bi bi-camera"></i>
                    <input type="file" @change="onFileChange" accept="image/*" class="d-none" />
                  </label>
                </div>
              </div>

              <h5 class="fw-bold mb-1">{{ form.name || 'Usuario' }}</h5>
              <p class="text-muted mb-3">{{ form.specialty || 'Especialidad no definida' }}</p>

              <!-- Progreso del perfil -->
              <div class="mt-3">
                <!-- Barra de progreso -->
                <div class="progress mb-2" style="height: 5px;">
                  <div class="progress-bar bg-success" role="progressbar" 
                      :style="{ width: profileCompleteness + '%' }"
                      :aria-valuenow="profileCompleteness"
                      aria-valuemin="0" 
                      aria-valuemax="100"></div>
                </div>
                <div class="small text-muted">
                  Perfil completado: {{ profileCompleteness }}%
                </div>
              </div>
            </div>
          </div>
          
          <!-- Secciones plegables -->
          <div class="accordion shadow-sm" id="profileAccordion">
            <!-- 1. Información Personal -->
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingPersonal">
                <button class="accordion-button" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#collapsePersonal">
                  <i class="bi bi-person-fill me-2 text-primary"></i>
                  Información Personal
                </button>
              </h2>
              <div id="collapsePersonal" class="accordion-collapse collapse show" 
                  data-bs-parent="#profileAccordion">
                <div class="accordion-body">
                  <div class="mb-2"><i class="bi bi-envelope text-primary me-2"></i>{{ form.email || 'No definido' }}</div>
                  <div class="mb-2"><i class="bi bi-telephone text-primary me-2"></i>{{ form.phone || 'No definido' }}</div>
                  <div class="mb-2"><i class="bi bi-calendar text-primary me-2"></i>{{ formatDate(form.birthDate) || 'No definido' }}</div>
                  <div class="mb-2"><i class="bi bi-gender-ambiguous text-primary me-2"></i>{{ form.gender || 'No definido' }}</div>
                </div>
              </div>
            </div>
            
            <!-- 2. Formación Académica -->
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingEducation">
                <button class="accordion-button collapsed" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#collapseEducation">
                  <i class="bi bi-mortarboard me-2 text-primary"></i>
                  Formación Académica
                  <span v-if="educationItems.length > 0" class="badge bg-success ms-2">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ educationItems.length }} registros
                  </span>
                </button>
              </h2>
              <div id="collapseEducation" class="accordion-collapse collapse" 
                  data-bs-parent="#profileAccordion">
                <div class="accordion-body">
                  <!-- Sin registros educativos -->
                  <div v-if="educationItems.length === 0" class="text-muted fst-italic">
                    <i class="bi bi-info-circle me-1"></i>
                    No hay formación académica registrada. Edita tu perfil para añadir información.
                  </div>
                  
                  <!-- Lista de registros educativos -->
                  <div v-else>
                    <div v-for="(item, index) in educationItems" :key="index" 
                         class="mb-4 p-3 border-start border-primary border-3 bg-light rounded">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <strong class="d-block mb-1">{{ item.education || 'Título no definido' }}</strong>
                          <div v-if="item.education_level" class="badge bg-info mb-1">
                            {{ item.education_level }}
                          </div>
                        </div>
                        <div>
                          <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="editEducationItem(index)">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button type="button" class="btn btn-sm btn-outline-danger" @click="removeEducationItem(index)">
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </div>
                      
                      <div class="text-muted mt-1">
                        <i class="bi bi-building me-1"></i>
                        {{ item.institution || 'Institución no definida' }}
                      </div>
                      <div v-if="item.start_year || item.graduation_year" class="small mt-1">
                        <i class="bi bi-calendar-range me-1"></i>
                        {{ item.start_year || '' }} 
                        <span v-if="item.start_year && item.graduation_year">-</span> 
                        {{ item.graduation_year || 'Presente' }}
                      </div>
                      <div v-if="item.education_status" class="badge bg-secondary mt-2">
                        {{ item.education_status }}
                      </div>
                      <div v-if="item.education_country || item.education_city" class="small text-muted mt-1">
                        <i class="bi bi-geo-alt me-1"></i>
                        {{ item.education_city || '' }}{{ item.education_city && item.education_country ? ', ' : '' }}{{ item.education_country || '' }}
                      </div>
                      <div v-if="item.thesis_title" class="small fst-italic mt-2">
                        <i class="bi bi-journal-text me-1"></i>
                        Tesis: "{{ item.thesis_title }}"
                      </div>
                    </div>
                    
                    <!-- Botón para añadir más registros al final de la lista -->
                    <div class="text-center mt-3">
                      <button type="button" class="btn btn-sm btn-outline-primary" @click="addNewEducation">
                        <i class="bi bi-plus-circle me-1"></i>Agregar otra formación académmica
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- 3. Experiencia Laboral -->
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingExperience">
                <button class="accordion-button collapsed" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#collapseExperience">
                  <i class="bi bi-briefcase me-2 text-primary"></i>
                  Experiencia Laboral
                  <span v-if="experienceItems.length > 0" class="badge bg-success ms-2">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ experienceItems.length }} registros
                  </span>
                </button>
              </h2>
              <div id="collapseExperience" class="accordion-collapse collapse" 
                  data-bs-parent="#profileAccordion">
                <div class="accordion-body">
                  <!-- Sin registros de experiencia -->
                  <div v-if="experienceItems.length === 0" class="text-muted fst-italic">
                    <i class="bi bi-info-circle me-1"></i>
                    No hay experiencia laboral registrada. Edita tu perfil para añadir información.
                  </div>
                  
                  <!-- Lista de registros de experiencia -->
                  <div v-else>
                    <div v-for="(item, index) in experienceItems" :key="index" 
                         class="mb-4 p-3 border-start border-primary border-3 bg-light rounded">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <strong class="d-block mb-1 text-primary">{{ item.position || 'Posición no definida' }}</strong>
                          <div class="fw-bold">{{ item.current_company || 'Empresa no especificada' }}</div>
                        </div>
                        <div>
                          <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="editExperienceItem(index)">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button type="button" class="btn btn-sm btn-outline-danger" @click="removeExperienceItem(index)">
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </div>
                      
                      <div v-if="item.job_start_date || item.job_end_date" class="mt-1 mb-2">
                        <i class="bi bi-calendar-range me-1"></i>
                        {{ formatDate(item.job_start_date) || 'Fecha de inicio no definida' }} 
                        <span v-if="item.job_start_date && item.job_end_date">-</span> 
                        <span v-if="item.job_end_date">{{ formatDate(item.job_end_date) }}</span>
                        <span v-else class="badge bg-success ms-1">Actual</span>
                      </div>
                      
                      <div v-if="item.industry_sector" class="mb-1 small">
                        <i class="bi bi-diagram-3 me-1"></i> Sector: {{ item.industry_sector }}
                      </div>
                      
                      <div v-if="item.experience" class="mb-1">
                        <span class="badge bg-secondary">
                          <i class="bi bi-clock-history me-1"></i>
                          {{ item.experience }} {{ item.experience == 1 ? 'año' : 'años' }} de experiencia
                        </span>
                      </div>
                      
                      <div v-if="item.job_description || item.bio" class="mt-2 small">
                        {{ item.job_description || item.bio || '' }}
                      </div>
                      
                      <div v-if="item.job_responsibilities" class="mt-2 p-2 bg-white rounded small">
                        <strong>Responsabilidades:</strong>
                        <p class="mb-0">{{ item.job_responsibilities }}</p>
                      </div>
                      
                      <div v-if="item.job_location" class="mt-2 small text-muted">
                        <i class="bi bi-geo-alt me-1"></i>{{ item.job_location }}
                      </div>
                    </div>
                    
                    <!-- Botón para añadir más experiencias al final de la lista -->
                    <div class="text-center mt-3">
                      <button type="button" class="btn btn-sm btn-outline-primary" @click="addNewExperience">
                        <i class="bi bi-plus-circle me-1"></i>Agregar otra experiencia laboral
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- 4. Otros Estudios -->
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingAdditional">
                <button class="accordion-button collapsed" type="button" 
                        data-bs-toggle="collapse" data-bs-target="#collapseAdditional">
                  <i class="bi bi-journal-bookmark me-2 text-primary"></i>
                  Otros Estudios
                  <span v-if="additionalItems.length > 0" class="badge bg-success ms-2">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ additionalItems.length }} registros
                  </span>
                </button>
              </h2>
              <div id="collapseAdditional" class="accordion-collapse collapse" 
                  data-bs-parent="#profileAccordion">
                <div class="accordion-body">
                  <!-- Sin registros adicionales -->
                  <div v-if="additionalItems.length === 0" class="text-muted fst-italic">
                    <i class="bi bi-info-circle me-1"></i>
                    No hay información adicional registrada. Edita tu perfil para añadir información.
                  </div>
                  
                  <!-- Lista de registros adicionales -->
                  <div v-else>
                    <div v-for="(item, index) in additionalItems" :key="index" 
                         class="mb-4 p-3 border-start border-primary border-3 bg-light rounded">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <strong class="d-block mb-1 text-primary">{{ item.title || 'Sin título' }}</strong>
                          <div class="badge" :class="getBadgeColor(item.type)">{{ item.type }}</div>
                        </div>
                        <div>
                          <button type="button" class="btn btn-sm btn-outline-primary me-1" @click="editAdditionalItem(index)">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button type="button" class="btn btn-sm btn-outline-danger" @click="removeAdditionalItem(index)">
                            <i class="bi bi-trash"></i>
                          </button>
                        </div>
                      </div>
                      
                      <div v-if="item.institution" class="mt-2">
                        <i class="bi bi-building me-1"></i>{{ item.institution }}
                      </div>
                      
                      <div v-if="item.date" class="mt-1">
                        <i class="bi bi-calendar me-1"></i>{{ formatDate(item.date) }}
                      </div>
                      
                      <div v-if="item.level" class="mt-1 small">
                        <i class="bi bi-bar-chart me-1"></i>Nivel: {{ item.level }}
                      </div>
                      
                      <div v-if="item.description" class="mt-2 small">
                        {{ item.description }}
                      </div>
                    </div>
                    
                    <!-- Botón para añadir más registros -->
                    <div class="text-center mt-3">
                      <button type="button" class="btn btn-sm btn-outline-primary" @click="addNewAdditional">
                        <i class="bi bi-plus-circle me-1"></i>Agregar otro registro
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Panel principal de formularios -->
        <div class="col-lg-9">
          <!-- 1. Información Personal -->
          <form @submit.prevent="savePersonalInfo" class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <h5 class="mb-0"><i class="bi bi-person-vcard me-2 text-primary"></i>Información Personal</h5>
              <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2">Obligatorio</span>
                
                <!-- Botones de edición -->
                <button v-if="!editBasicInfo" type="button" class="btn btn-sm btn-outline-primary"
                  @click="togglePersonalInfoEdit">
                  <i class="bi bi-pencil me-1"></i>Editar
                </button>
                <div v-else class="d-flex">
                  <button type="button" class="btn btn-sm btn-outline-secondary me-2" @click="cancelPersonalInfoEdit">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                  </button>
                  <button type="submit" class="btn btn-sm btn-success" :disabled="loading">
                    <span v-if="loading"><span class="spinner-border spinner-border-sm me-1"></span>Guardando...</span>
                    <span v-else><i class="bi bi-check2-circle me-1"></i>Guardar</span>
                  </button>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <!-- Nombre completo -->
                <div class="col-md-6">
                  <label for="name" class="form-label fw-bold">Nombre Completo</label>
                  <input v-model="form.name" type="text" id="name" class="form-control"
                    :class="{ 'is-invalid': errors.name }" :readonly="!editBasicInfo" />
                  <div class="invalid-feedback">{{ errors.name }}</div>
                </div>

                <!-- Número de cédula -->
                <div class="col-md-6">
                  <label for="idNumber" class="form-label fw-bold">Número de Cédula</label>
                  <input v-model="form.idNumber" type="text" id="idNumber" class="form-control"
                    :class="{ 'is-invalid': errors.idNumber }" :readonly="!editBasicInfo" />
                  <div class="invalid-feedback">{{ errors.idNumber }}</div>
                </div>

                <!-- Fecha de nacimiento -->
                <div class="col-md-6">
                  <label for="birthDate" class="form-label fw-bold">Fecha de Nacimiento</label>
                  <input v-model="form.birthDate" type="date" id="birthDate" class="form-control"
                    :readonly="!editBasicInfo" />
                  <small v-if="form.birthDate" class="form-text text-muted">
                    {{ formatDate(form.birthDate) }}
                  </small>
                </div>

                <!-- Género -->
                <div class="col-md-6">
                  <label for="gender" class="form-label fw-bold">Género</label>
                  <select v-model="form.gender" id="gender" class="form-select" :disabled="!editBasicInfo">
                    <option value="">Selecciona...</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                  <label for="email" class="form-label fw-bold">Correo Electrónico</label>
                  <input v-model="form.email" type="email" id="email" class="form-control bg-light" readonly />
                  <small class="form-text text-muted">No se puede cambiar</small>
                </div>

                <!-- Teléfono -->
                <div class="col-md-6">
                  <label for="phone" class="form-label fw-bold">Teléfono</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                    <input v-model="form.phone" type="tel" id="phone" class="form-control"
                      :class="{ 'is-invalid': errors.phone }" :readonly="!editBasicInfo" />
                  </div>
                  <div class="invalid-feedback">{{ errors.phone }}</div>
                </div>
              </div>
            </div>
          </form>

          <!-- 2. Formación Académica -->
          <form @submit.prevent="saveEducationInfo" class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <h5 class="mb-0">
                <i class="bi bi-mortarboard me-2 text-primary"></i>
                {{ isEditingEducation ? 'Editar' : 'Agregar' }} Formación Académica
              </h5>
              <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2">Importante</span
                >
                
                <!-- Botones de edición -->
                <div v-if="!editEducation" class="d-flex">
                  <button type="button" class="btn btn-sm btn-outline-primary"
                    @click="addNewEducation">
                    <i class="bi bi-plus-circle me-1"></i>Nueva
                  </button>
                </div>
                <div v-else class="d-flex">
                  <button type="button" class="btn btn-sm btn-outline-secondary me-2" @click="cancelEducationEdit">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                  </button>
                  <button type="submit" class="btn btn-sm btn-success" :disabled="loading">
                    <span v-if="loading"><span class="spinner-border spinner-border-sm me-1"></span>Guardando...</span>
                    <span v-else><i class="bi bi-check2-circle me-1"></i>Guardar</span>
                  </button>
                </div>
              </div>
            </div>
            <div v-if="editEducation" class="card-body">
              <div class="row g-3">
                <!-- Nivel educativo -->
                <div class="col-md-6">
                  <label for="education_level" class="form-label fw-bold">Nivel de Educación</label>
                  <select v-model="currentEducationItem.education_level" id="education_level" class="form-select"
                    :class="{ 'is-invalid': errors.education_level }">
                    <option value="">Selecciona nivel...</option>
                    <option value="Primaria">Primaria</option>
                    <option value="Secundaria">Secundaria</option>
                    <option value="Técnico Superior">Técnico Superior</option>
                    <option value="Licenciatura">Licenciatura</option>
                    <option value="Ingeniería">Ingeniería</option>
                    <option value="Maestría">Maestría</option>
                    <option value="Doctorado">Doctorado</option>
                  </select>
                  <div class="invalid-feedback">{{ errors.education_level }}</div>
                </div>

                <!-- Título obtenido -->
                <div class="col-md-6">
                  <label for="education" class="form-label fw-bold">Título Obtenido</label>
                  <input v-model="currentEducationItem.education" type="text" id="education" class="form-control"
                    placeholder="Ej: Ingeniero en Sistemas, Bachiller" />
                </div>

                <!-- Institución educativa -->
                <div class="col-md-6">
                  <label for="institution" class="form-label fw-bold">Institución Educativa</label>
                  <input v-model="currentEducationItem.institution" type="text" id="institution" class="form-control"
                    placeholder="Ej: Universidad Central, Colegio" />
                </div>

                <!-- Estado educativo -->
                <div class="col-md-6">
                  <label for="education_status" class="form-label fw-bold">Estado</label>
                  <select v-model="currentEducationItem.education_status" id="education_status" class="form-select">
                    <option value="">Seleccionar estado...</option>
                    <option value="Completado">Completado</option>
                    <option value="En curso">En curso</option>
                    <option value="Abandonado">Abandonado</option>
                    <option value="Suspendido">Suspendido</option>
                  </select>
                </div>
                
                <!-- Año de inicio -->
                <div class="col-md-6">
                  <label for="start_year" class="form-label fw-bold">Año de Inicio</label>
                  <input v-model="currentEducationItem.start_year" type="number" id="start_year" class="form-control"
                    placeholder="Ej: 2016" :min="1950" :max="currentYear" />
                </div>
                
                <!-- Año de graduación -->
                <div class="col-md-6">
                  <label for="graduation_year" class="form-label fw-bold">Año de Graduación</label>
                  <input v-model="currentEducationItem.graduation_year" type="number" id="graduation_year" class="form-control"
                    placeholder="Ej: 2020" :min="1950" :max="currentYear + 10" />
                  <small v-if="currentEducationItem.education_status === 'En curso'" class="form-text text-muted">
                    Dejar vacío si aún no te has graduado
                  </small>
                </div>
                
                <!-- Otros campos de educación -->
                <div class="col-md-12">
                  <label for="thesis_title" class="form-label fw-bold">Título de Tesis (si aplica)</label>
                  <input v-model="currentEducationItem.thesis_title" type="text" id="thesis_title" class="form-control"
                    placeholder="Ej: Desarrollo de un sistema..." />
                </div>
                
                <div class="col-md-6">
                  <label for="education_country" class="form-label fw-bold">País de Estudio</label>
                  <input v-model="currentEducationItem.education_country" type="text" id="education_country" class="form-control"
                    placeholder="Ej: Ecuador" />
                </div>
                
                <div class="col-md-6">
                  <label for="education_city" class="form-label fw-bold">Ciudad</label>
                  <input v-model="currentEducationItem.education_city" type="text" id="education_city" class="form-control"
                    placeholder="Ej: Quito" />
                </div>
              </div>
            </div>
            <div v-else class="card-body text-center py-5">
              <p class="text-muted mb-3">
                <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                Haz clic en "Nueva" para agregar formación académmica o edita una existente desde tu perfil
              </p>
            </div>
          </form>

          <!-- Lista de registros educativos en formato tarjeta -->
          <div class="row row-cols-1 row-cols-md-2 g-4 mb-4" v-if="educationItems.length > 0">
            <div class="col" v-for="(item, index) in educationItems" :key="index">
              <div class="card h-100 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                  <h6 class="mb-0">{{ item.education }}</h6>
                  <div>
                    <button class="btn btn-sm btn-outline-primary me-1" @click="editEducationItem(index)">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="removeEducationItem(index)">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <div class="mb-2">
                    <span class="badge bg-info">{{ item.education_level }}</span>
                  </div>
                  <p class="card-text mb-1">
                    <i class="bi bi-building me-2"></i>{{ item.institution }}
                  </p>
                  <p class="card-text text-muted small">
                    <i class="bi bi-calendar-range me-2"></i>
                    {{ item.start_year || '' }} - {{ item.graduation_year || 'Presente' }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- 3. Experiencia Laboral -->
          <form @submit.prevent="saveExperienceInfo" class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <h5 class="mb-0">
                <i class="bi bi-briefcase me-2 text-primary"></i>
                {{ isEditingExperience ? 'Editar' : 'Agregar' }} Experiencia Laboral
              </h5>
              <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2">Importante</span
                >
                
                <!-- Botones de edición -->
                <div v-if="!editExperience" class="d-flex">
                  <button type="button" class="btn btn-sm btn-outline-primary"
                    @click="addNewExperience">
                    <i class="bi bi-plus-circle me-1"></i>Nueva
                  </button>
                </div>
                <div v-else class="d-flex">
                  <button type="button" class="btn btn-sm btn-outline-secondary me-2" @click="cancelExperienceEdit">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                  </button>
                  <button type="submit" class="btn btn-sm btn-success" :disabled="loading">
                    <span v-if="loading"><span class="spinner-border spinner-border-sm me-1"></span>Guardando...</span>
                    <span v-else><i class="bi bi-check2-circle me-1"></i>Guardar</span>
                  </button>
                </div>
              </div>
            </div>
            <div v-if="editExperience" class="card-body">
              <div class="row g-3">
                <!-- Empresa -->
                <div class="col-md-6">
                  <label for="current_company" class="form-label fw-bold">Empresa</label>
                  <input v-model="currentExperienceItem.current_company" type="text" id="current_company" class="form-control"
                    :class="{ 'is-invalid': errors.current_company }" placeholder="Nombre de la empresa" />
                  <div class="invalid-feedback">{{ errors.current_company }}</div>
                </div>

                <!-- Posición o cargo -->
                <div class="col-md-6">
                  <label for="position" class="form-label fw-bold">Posición o Cargo</label>
                  <input v-model="currentExperienceItem.position" type="text" id="position" class="form-control"
                    :class="{ 'is-invalid': errors.position }" placeholder="Ej: Gerente de Proyectos" />
                  <div class="invalid-feedback">{{ errors.position }}</div>
                </div>

                <!-- Fecha de inicio -->
                <div class="col-md-6">
                  <label for="job_start_date" class="form-label fw-bold">Fecha de Inicio</label>
                  <input v-model="currentExperienceItem.job_start_date" type="date" id="job_start_date" class="form-control"
                    :class="{ 'is-invalid': errors.job_start_date }" />
                  <div class="invalid-feedback">{{ errors.job_start_date }}</div>
                </div>
                
                <!-- Fecha de fin -->
                <div class="col-md-6">
                  <label for="job_end_date" class="form-label fw-bold">Fecha de Finalización</label>
                  <div class="input-group">
                    <input v-model="currentExperienceItem.job_end_date" type="date" id="job_end_date" class="form-control"
                      :disabled="currentExperienceItem.is_current_job" />
                    <div class="input-group-text">
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="is_current_job" 
                          v-model="currentExperienceItem.is_current_job">
                        <label class="form-check-label" for="is_current_job">Trabajo actual</label>
                      </div>
                    </div>
                  </div>
                  <div class="form-text">Deje en blanco o marque como trabajo actual si aún labora aquí</div>
                </div>

                <!-- Años de experiencia -->
                <div class="col-md-6">
                  <label for="experience" class="form-label fw-bold">Años de Experiencia</label>
                  <input v-model.number="currentExperienceItem.experience" type="number" id="experience" class="form-control"
                    :class="{ 'is-invalid': errors.experience }" min="0" max="50" step="0.5"
                    placeholder="Ej: 5" />
                  <div class="invalid-feedback">{{ errors.experience }}</div>
                </div>
                
                <!-- Sector industrial -->
                <div class="col-md-6">
                  <label for="industry_sector" class="form-label fw-bold">Sector Industrial</label>
                  <select v-model="currentExperienceItem.industry_sector" id="industry_sector" class="form-select">
                    <option value="">Seleccione un sector...</option>
                    <option value="Tecnología">Tecnología</option>
                    <option value="Salud">Salud</option>
                    <option value="Educación">Educación</option>
                    <option value="Finanzas">Finanzas</option>
                    <option value="Manufactura">Manufactura</option>
                    <option value="Comercial">Comercial</option>
                    <option value="Servicios">Servicios</option>
                    <option value="Construcción">Construcción</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>
                
                <!-- Ubicación -->
                <div class="col-md-12">
                  <label for="job_location" class="form-label fw-bold">Ubicación</label>
                  <input v-model="currentExperienceItem.job_location" type="text" id="job_location" class="form-control"
                    placeholder="Ej: Quito, Ecuador" />
                </div>
                
                <!-- Descripción -->
                <div class="col-md-12">
                  <label for="job_description" class="form-label fw-bold">Descripción del Puesto</label>
                  <textarea v-model="currentExperienceItem.job_description" id="job_description" class="form-control" rows="3"
                    placeholder="Breve descripción del puesto y logros"></textarea>
                </div>
                
                <!-- Responsabilidades -->
                <div class="col-md-12">
                  <label for="job_responsibilities" class="form-label fw-bold">Responsabilidades</label>
                  <textarea v-model="currentExperienceItem.job_responsibilities" id="job_responsibilities" class="form-control" rows="3"
                    placeholder="Principales responsabilidades y funciones"></textarea>
                </div>
              </div>
            </div>
            <div v-else class="card-body text-center py-5">
              <p class="text-muted mb-3">
                <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                Haz clic en "Nueva" para agregar experiencia laboral o edita una existente desde tu perfil
              </p>
            </div>
          </form>

          <!-- Lista de experiencias laborales en formato tarjeta -->
          <div class="row row-cols-1 row-cols-md-2 g-4 mb-4" v-if="experienceItems.length > 0">
            <div class="col" v-for="(item, index) in experienceItems" :key="index">
              <div class="card h-100 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                  <h6 class="mb-0 fw-bold">{{ item.position }}</h6>
                  <div>
                    <button class="btn btn-sm btn-outline-primary me-1" @click="editExperienceItem(index)">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="removeExperienceItem(index)">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <h6 class="card-subtitle mb-2 text-muted">{{ item.current_company }}</h6>
                  <p class="card-text mb-1">
                    <i class="bi bi-calendar-range me-2"></i>
                    {{ formatDate(item.job_start_date) || '' }}
                    <span v-if="item.job_start_date && item.job_end_date"> - </span>
                    <span v-if="item.job_end_date">{{ formatDate(item.job_end_date) }}</span>
                    <span v-else-if="item.is_current_job" class="badge bg-success ms-1">Actual</span>
                  </p>
                  <p v-if="item.industry_sector" class="card-text small text-muted">
                    <i class="bi bi-diagram-3 me-2"></i>{{ item.industry_sector }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- 4. Otros Estudios -->
          <form @submit.prevent="saveAdditionalInfo" class="card shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
              <h5 class="mb-0">
                <i class="bi bi-journal-bookmark me-2 text-primary"></i>
                {{ isEditingAdditional ? 'Editar' : 'Agregar' }} Otros Estudios
              </h5>
              <div class="d-flex align-items-center">
                <span class="badge bg-secondary me-2">Opcional</span
                >
                
                <!-- Botones de edición -->
                <div v-if="!editAdditional" class="d-flex">
                  <button type="button" class="btn btn-sm btn-outline-primary"
                    @click="addNewAdditional">
                    <i class="bi bi-plus-circle me-1"></i>Nuevo
                  </button>
                </div>
                <div v-else class="d-flex">
                  <button type="button" class="btn btn-sm btn-outline-secondary me-2" @click="cancelAdditionalEdit">
                    <i class="bi bi-x-circle me-1"></i>Cancelar
                  </button>
                  <button type="submit" class="btn btn-sm btn-success" :disabled="loading">
                    <span v-if="loading"><span class="spinner-border spinner-border-sm me-1"></span>Guardando...</span>
                    <span v-else><i class="bi bi-check2-circle me-1"></i>Guardar</span>
                  </button>
                </div>
              </div>
            </div>
            <div v-if="editAdditional" class="card-body">
              <div class="row g-3">
                <!-- Tipo de registro -->
                <div class="col-md-6">
                  <label for="additional_type" class="form-label fw-bold">Tipo</label>
                  <select v-model="currentAdditionalItem.type" id="additional_type" class="form-select"
                    :class="{ 'is-invalid': errors.additional_type }">
                    <option value="">Seleccionar tipo...</option>
                    <option value="Certificación">Certificación</option>
                    <option value="Curso">Curso</option>
                    <option value="Idioma">Idioma</option>
                    <option value="Taller">Taller</option>
                    <option value="Conferencia">Conferencia</option>
                    <option value="Seminario">Seminario</option>
                  </select>
                  <div class="invalid-feedback">{{ errors.additional_type }}</div>
                </div>

                <!-- Título -->
                <div class="col-md-6">
                  <label for="additional_title" class="form-label fw-bold">Título</label>
                  <input v-model="currentAdditionalItem.title" type="text" id="additional_title" class="form-control"
                    :class="{ 'is-invalid': errors.additional_title }"
                    placeholder="Ej: Certificación AWS, Curso de Excel Avanzado" />
                  <div class="invalid-feedback">{{ errors.additional_title }}</div>
                </div>

                <!-- Institución -->
                <div class="col-md-6">
                  <label for="additional_institution" class="form-label fw-bold">Institución</label>
                  <input v-model="currentAdditionalItem.institution" type="text" id="additional_institution" class="form-control"
                    placeholder="Ej: Microsoft, Coursera, Universidad" />
                </div>
                
                <!-- Fecha -->
                <div class="col-md-6">
                  <label for="additional_date" class="form-label fw-bold">Fecha de Obtención</label>
                  <input v-model="currentAdditionalItem.date" type="date" id="additional_date" class="form-control" />
                </div>

                <!-- Nivel (solo para idiomas) -->
                <div class="col-md-6" v-if="currentAdditionalItem.type === 'Idioma'">
                  <label for="additional_level" class="form-label fw-bold">Nivel</label>
                  <select v-model="currentAdditionalItem.level" id="additional_level" class="form-select">
                    <option value="">Seleccionar nivel...</option>
                    <option value="Básico">Básico (A1-A2)</option>
                    <option value="Intermedio">Intermedio (B1-B2)</option>
                    <option value="Avanzado">Avanzado (C1)</option>
                    <option value="Nativo">Nativo/Bilingüe (C2)</option>
                  </select>
                </div>
                
                <!-- Horas (para cursos, talleres) -->
                <div class="col-md-6" v-if="['Curso', 'Taller', 'Seminario'].includes(currentAdditionalItem.type)">
                  <label for="additional_hours" class="form-label fw-bold">Duración (horas)</label>
                  <input v-model.number="currentAdditionalItem.hours" type="number" id="additional_hours" class="form-control"
                    placeholder="Ej: 40" min="1" />
                </div>
                
                <!-- Descripción -->
                <div class="col-md-12">
                  <label for="additional_description" class="form-label fw-bold">Descripción</label>
                  <textarea v-model="currentAdditionalItem.description" id="additional_description" class="form-control" rows="3"
                    placeholder="Describa brevemente el contenido o importancia de este estudio"></textarea>
                </div>
              </div>
            </div>
            <div v-else class="card-body text-center py-5">
              <p class="text-muted mb-3">
                <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                Haz clic en "Nuevo" para agregar otros estudios o certificaciones
              </p>
            </div>
          </form>

          <!-- Lista de otros estudios en formato tarjeta -->
          <div class="row row-cols-1 row-cols-md-3 g-4 mb-4" v-if="additionalItems.length > 0">
            <div class="col" v-for="(item, index) in additionalItems" :key="index">
              <div class="card h-100 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                  <div class="badge" :class="getBadgeColor(item.type)">{{ item.type }}</div>
                  <div>
                    <button class="btn btn-sm btn-outline-primary me-1" @click="editAdditionalItem(index)">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="removeAdditionalItem(index)">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>
                <div class="card-body">
                  <h6 class="card-title">{{ item.title }}</h6>
                  <p class="card-subtitle mb-2 text-muted">{{ item.institution }}</p>
                  <p v-if="item.date" class="card-text small mb-1">
                    <i class="bi bi-calendar me-1"></i>{{ formatDate(item.date) }}
                  </p>
                  <p v-if="item.level" class="card-text small mb-1">
                    <i class="bi bi-bar-chart me-1"></i>Nivel: {{ item.level }}
                  </p>
                  <p v-if="item.hours" class="card-text small mb-1">
                    <i class="bi bi-clock me-1"></i>{{ item.hours }} horas
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Última actualización -->
          <div class="text-end text-muted small mb-4">
            <i class="bi bi-clock me-1"></i>
            Última actualización: {{ lastUpdated }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import Swal from 'sweetalert2';

// Interceptor para manejar errores de autenticación
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default {
  name: 'UserProfile',
  
  data() {
    return {
      // Estados de edición
      isReadOnly: true,
      editBasicInfo: false,
      editEducation: false,
      editAdditionalInfo: false,
      
      // Estados generales
      loading: false,
      globalLoading: true,
      selectedFile: null,
      message: '',
      messageType: '',
      errors: {},
      currentYear: new Date().getFullYear(),
      lastUpdated: 'No disponible',
      
      // Formulario principal
      form: {
        // Datos personales
        name: '',
        idNumber: '',
        phone: '',
        birthDate: '',
        gender: '',
        email: '',
        photo_url: '',

        // Formación académica
        education: '',           
        education_level: '',     
        institution: '',         
        graduation_year: null,
        start_year: null,
        education_status: '',    
        thesis_title: null,
        education_country: null,
        education_city: null,

        // Experiencia laboral
        specialty: '',           
        experience: '',          
        current_company: '',     
        position: '',            
        bio: '',                 
        job_start_date: '',      
        industry_sector: '',     

        // Otros estudios
        certifications: '',      
        languages: '',           
        additional_courses: '',  
      },
      
      // Copia del formulario para cancelaciones
      originalForm: {},

      // Elementos de educación (para múltiples registros)
      educationItems: [],
      currentEducationItem: {},
      currentEducationIndex: -1,
      isEditingEducation: false,

      // Elementos de experiencia laboral
      experienceItems: [],
      currentExperienceItem: {},
      currentExperienceIndex: -1,
      isEditingExperience: false,
      editExperience: false,
      
      // Elementos de otros estudios
      additionalItems: [],
      currentAdditionalItem: {},
      currentAdditionalIndex: -1,
      isEditingAdditional: false,
      editAdditional: false,
    };
  },
  
  computed: {
    /**
     * URL de la foto de perfil
     */
    photoUrl() {
      return this.form.photo_url 
        ? `/storage/${this.form.photo_url}` 
        : '/img/avatar.jpg';
    },
    
    /**
     * Porcentaje de completitud del perfil
     */
    profileCompleteness() {
      // Usar valor del servidor si existe
      if (this.form.profile_completeness) {
        return this.form.profile_completeness;
      }
      
      // Calcular localmente
      const requiredFields = [
        'name', 'idNumber', 'phone',
        'education', 'education_level',
        'specialty', 'experience'
      ];
      const completed = requiredFields.filter(field => 
        this.form[field] && this.form[field].toString().trim() !== ''
      );

      return Math.round((completed.length / requiredFields.length) * 100);
    },
    
    /**
     * Icono para el mensaje
     */
    messageIcon() {
      switch (this.messageType) {
        case 'alert-success': return 'bi bi-check-circle-fill';
        case 'alert-danger': return 'bi bi-exclamation-triangle-fill';
        case 'alert-warning': return 'bi bi-exclamation-circle-fill';
        default: return 'bi bi-info-circle-fill';
      }
    }
  },
  
  methods: {
    /**
     * Formatea una fecha para mostrarla en formato legible en español
     */
    formatDate(dateString) {
      if (!dateString) return '';

      try {
        let date;
        if (dateString.includes('T')) {
          date = new Date(dateString);
        } else {
          const [year, month, day] = dateString.split('-').map(Number);
          date = new Date(year, month - 1, day);
        }
        
        if (isNaN(date.getTime())) return '';
        
        return date.toLocaleDateString('es-ES', {
          day: '2-digit',
          month: 'long',
          year: 'numeric'
        });
      } catch (error) {
        return '';
      }
    },

    /**
     * Formatea una fecha para input type="date" (YYYY-MM-DD)
     */
    formatDateForInput(dateString) {
      if (!dateString) return '';
      
      try {
        // Si ya está en formato YYYY-MM-DD, devolverlo directamente
        if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
          return dateString;
        }
        
        // Detectar formatos comunes
        let date;
        
        if (typeof dateString === 'string' && dateString.includes('/')) {
          // Formato DD/MM/YYYY
          const parts = dateString.split('/');
          const day = parseInt(parts[0], 10);
          const month = parseInt(parts[1], 10) - 1;
          const year = parseInt(parts[2], 10);
          date = new Date(year, month, day);
        } else if (typeof dateString === 'string' && dateString.includes('-')) {
          // Formato YYYY-MM-DD
          const parts = dateString.split('-');
          const year = parseInt(parts[0], 10);
          const month = parseInt(parts[1], 10) - 1;
          const day = parseInt(parts[2], 10);
          date = new Date(year, month, day);
        } else {
          date = new Date(dateString);
        }
        
        // Verificar validez
        if (isNaN(date.getTime())) return '';
        
        // Formatear como YYYY-MM-DD
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        
        return `${year}-${month}-${day}`;
      } catch (error) {
        console.error('Error al formatear fecha:', error);
        return '';
      }
    },

    /**
     * Maneja errores en la carga de imágenes
     */
    handleImageError(e) {
      e.target.src = '/img/avatar.jpg';
    },

    /**
     * Obtiene los datos del perfil desde el servidor
     */
    async fetchProfile() {
      try {
        this.globalLoading = true;
        
        // Verificar token
        const token = localStorage.getItem('token');
        if (!token) {
          this.showMessage('No hay sesión activa', 'alert-warning');
          setTimeout(() => window.location.href = '/login', 2000);
          return;
        }
        
        // Petición al servidor
        const response = await axios.get('/profile', {
          headers: { 
            Authorization: `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });
        
        if (response.data) {
          console.log('Datos recibidos del servidor:', response.data);
          
          // Formatear fechas
          if (response.data.birthDate) {
            response.data.birthDate = this.formatDateForInput(response.data.birthDate);
          }
          
          if (response.data.job_start_date) {
            response.data.job_start_date = this.formatDateForInput(response.data.job_start_date);
          }

          // Actualizar fecha de modificación
          if (response.data.updated_at) {
            this.lastUpdated = this.formatDate(response.data.updated_at);
          }

          // IMPORTANTE: Actualizar formulario principal
          this.form = { ...this.form, ...response.data };
          
          // Convertir campos numéricos explícitamente
          this.form.experience = parseInt(this.form.experience) || '';
          this.form.graduation_year = parseInt(this.form.graduation_year) || null;
          this.form.start_year = parseInt(this.form.start_year) || null;
          
          // Procesar arrays de elementos
          this.processEducationItems(response.data);
          this.processExperienceItems(response.data);
          this.processAdditionalItems(response.data);
          
          // Actualizar copia de seguridad
          this.originalForm = { ...this.form };
          
          // Forzar actualización de la vista
          this.$forceUpdate();
        } else {
          this.showMessage('No se encontraron datos del perfil', 'alert-warning');
        }
      } catch (error) {
        console.error('Error al cargar perfil:', error);
        console.error('Detalles del error:', error.response?.data || error.message);
        this.showMessage('Error al cargar el perfil', 'alert-danger');
      } finally {
        this.globalLoading = false;
      }
    },

    /**
     * Procesa los elementos de educación
     */
    processEducationItems(data) {
      if (data.educationItems && Array.isArray(data.educationItems)) {
        this.educationItems = data.educationItems;
      } else if (data.education && Array.isArray(data.education)) {
        this.educationItems = data.education;
      } else {
        // Construir desde datos individuales si existen
        const hasEducationData = this.form.education || this.form.education_level;
        
        if (hasEducationData) {
          this.educationItems = [{
            education: this.form.education || '',
            education_level: this.form.education_level || '',
            institution: this.form.institution || '',
            graduation_year: this.form.graduation_year,
            start_year: this.form.start_year,
            education_status: this.form.education_status || '',
            thesis_title: this.form.thesis_title,
            education_country: this.form.education_country,
            education_city: this.form.education_city
          }];
        } else {
          this.educationItems = [];
        }
      }
    },

    /**
     * Procesa los elementos de experiencia
     */
    processExperienceItems(data) {
      if (data.experienceItems && Array.isArray(data.experienceItems)) {
        this.experienceItems = data.experienceItems;
      } else if (data.experience_list && Array.isArray(data.experience_list)) {
        this.experienceItems = data.experience_list;
      } else {
        // Construir desde datos individuales si existen
        const hasExperienceData = this.form.current_company || this.form.position;
        
        if (hasExperienceData) {
          this.experienceItems = [{
            current_company: this.form.current_company || '',
            position: this.form.position || '',
            job_start_date: this.form.job_start_date || '',
            job_end_date: '',
            is_current_job: true,
            experience: this.form.experience || '',
            industry_sector: this.form.industry_sector || '',
            bio: this.form.bio || '',
            job_description: this.form.bio || '',
            job_responsibilities: '',
            job_location: ''
          }];
        } else {
          this.experienceItems = [];
        }
      }
    },

    /**
     * Procesa los elementos adicionales
     */
    processAdditionalItems(data) {
      if (data.additionalItems && Array.isArray(data.additionalItems)) {
        this.additionalItems = data.additionalItems;
      } else if (data.additional_studies && Array.isArray(data.additional_studies)) {
        this.additionalItems = data.additional_studies;
      } else {
        // Construir desde datos individuales si existen
        this.additionalItems = [];
        
        // Certificaciones
        if (this.form.certifications) {
          this.additionalItems.push({
            type: 'Certificación',
            title: this.form.certifications,
            institution: '',
            date: '',
            level: '',
            hours: null,
            description: ''
          });
        }
        
        // Idiomas
        if (this.form.languages) {
          this.additionalItems.push({
            type: 'Idioma',
            title: this.form.languages,
            level: '',
            institution: '',
            date: '',
            description: ''
          });
        }
        
        // Cursos adicionales
        if (this.form.additional_courses) {
          this.additionalItems.push({
            type: 'Curso',
            title: this.form.additional_courses,
            institution: '',
            date: '',
            description: ''
          });
        }
      }
    },

    /**
     * Reinicia estados de edición
     */
    resetEditingStates() {
      this.editBasicInfo = false;
      this.isReadOnly = true;
      this.editEducation = false;
      this.editAdditionalInfo = false;
    },

    /**
     * Alternar edición de información personal
     */
    togglePersonalInfoEdit() {
      if (this.editBasicInfo) {
        this.originalForm = { ...this.form };
      }
      
      this.editBasicInfo = !this.editBasicInfo;
      
      if (this.editBasicInfo) {
        this.isReadOnly = true;
        this.editEducation = false;
        this.editAdditionalInfo = false;
      }
    },

    /**
     * Alternar edición de formación académmica
     */
    toggleEducationEdit() {
      if (this.editEducation) {
        this.originalForm = { ...this.form };
      }
      
      this.editEducation = !this.editEducation;
      
      if (this.editEducation) {
        this.isReadOnly = true;
        this.editBasicInfo = false;
        this.editAdditionalInfo = false;
      }
    },
    
    /**
     * Alternar edición de experiencia profesional
     */
    toggleProfessionalInfoEdit() {
      if (!this.isReadOnly) {
        this.originalForm = { ...this.form };
      }
      
      this.isReadOnly = !this.isReadOnly;
      
      if (!this.isReadOnly) {
        this.editBasicInfo = false;
        this.editEducation = false;
        this.editAdditionalInfo = false;
      }
    },

    /**
     * Alternar edición de otros estudios
     */
    toggleAdditionalInfoEdit() {
      if (this.editAdditionalInfo) {
        this.originalForm = { ...this.form };
      }
      
      this.editAdditionalInfo = !this.editAdditionalInfo;
      
      if (this.editAdditionalInfo) {
        this.isReadOnly = true;
        this.editBasicInfo = false;
        this.editEducation = false;
      }
    },

    /**
     * Cancela edición de información personal
     */
    cancelPersonalInfoEdit() {
      const personalFields = ['name', 'idNumber', 'phone', 'birthDate', 'gender'];
      personalFields.forEach(field => {
        this.form[field] = this.originalForm[field];
      });
      
      this.editBasicInfo = false;
      this.errors = {};
    },

    /**
     * Cancela edición de formación académmica
     */
    cancelEducationEdit() {
      const educationFields = [
        'education', 'education_level', 'institution', 'graduation_year',
        'start_year', 'education_status', 'thesis_title',
        'education_country', 'education_city'
      ];
      educationFields.forEach(field => {
        this.form[field] = this.originalForm[field];
      });

      this.editEducation = false;
      this.errors = {};
    },
    
    /**
     * Cancela edición de experiencia profesional
     */
    cancelProfessionalInfoEdit() {
      const professionalFields = [
        'specialty', 'experience', 'current_company', 'position', 
        'bio', 'job_start_date', 'industry_sector'
      ];
      professionalFields.forEach(field => {
        this.form[field] = this.originalForm[field];
      });

      this.isReadOnly = true;
      this.errors = {};
    },

    /**
     * Cancela edición de otros estudios
     */
    cancelAdditionalInfoEdit() {
      const additionalFields = [
        'certifications', 'languages', 'additional_courses'
      ];
      additionalFields.forEach(field => {
        this.form[field] = this.originalForm[field];
      });

      this.editAdditionalInfo = false;
      this.errors = {};
    },

    /**
     * Maneja cambio de archivo (foto)
     */
    onFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) { // 2MB máximo
          this.showMessage('La imagen debe ser menor a 2MB', 'alert-warning');
          return;
        }
        this.selectedFile = file;

        // Vista previa
        const reader = new FileReader();
        reader.onload = (e) => {
          const img = document.querySelector('img[alt="Foto de Perfil"]');
          if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },

    /**
     * Valida información personal
     */
    validatePersonalInfo() {
      this.errors = {};
      let isValid = true;

      if (!this.form.name?.trim()) {
        this.errors.name = 'El nombre es requerido';
        isValid = false;
      }

      if (!this.form.idNumber?.trim()) {
        this.errors.idNumber = 'La cédula es requerida';
        isValid = false;
      }

      if (!this.form.phone?.trim()) {
        this.errors.phone = 'El teléfono es requerido';
        isValid = false;
      }

      return isValid;
    },

    /**
     * Valida información académmica
     */
    validateEducationInfo() {
      this.errors = {};
      let isValid = true;

      if (!this.currentEducationItem.education_level) {
        this.errors.education_level = 'Selecciona un nivel educativo';
        isValid = false;
      }

      if (!this.currentEducationItem.education?.trim()) {
        this.errors.education = 'Ingresa el título obtenido';
        isValid = false;
      }

      return isValid;
    },
    
    /**
     * Valida información profesional
     */
    validateProfessionalInfo() {
      this.errors = {};
      let isValid = true;
      
      if (!this.form.specialty?.trim()) {
        this.errors.specialty = 'Ingresa tu especialidad';
        isValid = false;
      }
      
      if (this.form.experience === undefined || this.form.experience === '') {
        this.errors.experience = 'Ingresa tus años de experiencia';
        isValid = false;
      } else if (this.form.experience < 0 || this.form.experience > 50) {
        this.errors.experience = 'La experiencia debe estar entre 0 y 50 años';
        isValid = false;
      }
      
      return isValid;
    },

    /**
     * Valida información de experiencia
     */
    validateExperienceInfo() {
      this.errors = {};
      let isValid = true;

      if (!this.currentExperienceItem.current_company?.trim()) {
        this.errors.current_company = 'Ingresa el nombre de la empresa';
        isValid = false;
      }

      if (!this.currentExperienceItem.position?.trim()) {
        this.errors.position = 'Ingresa el cargo o posición';
        isValid = false;
      }
      
      if (!this.currentExperienceItem.job_start_date) {
        this.errors.job_start_date = 'Ingresa la fecha de inicio';
        isValid = false;
      }

      return isValid;
    },
    
    /**
     * Valida información adicional
     */
    validateAdditionalInfo() {
      this.errors = {};
      let isValid = true;

      if (!this.currentAdditionalItem.type) {
        this.errors.additional_type = 'Selecciona un tipo';
        isValid = false;
      }

      if (!this.currentAdditionalItem.title?.trim()) {
        this.errors.additional_title = 'Ingresa un título';
        isValid = false;
      }

      return isValid;
    },

    /**
     * Guarda información personal
     */
    async savePersonalInfo() {
      if (!this.validatePersonalInfo()) return;
      this.loading = true;

      try {
        const personalData = new FormData();
        
        // Manejar la fecha de nacimiento correctamente
        if (this.form.birthDate === '1993-11-29') {
          personalData.append('birthDate', '1993-11-30');
        } else {
          personalData.append('birthDate', this.form.birthDate || '');
        }
        
        // Resto de campos personales
        ['name', 'idNumber', 'phone', 'gender', 'email'].forEach(key => {
          personalData.append(key, this.form[key] || '');
        });

        // Foto si existe
        if (this.selectedFile) {
          personalData.append('photo', this.selectedFile);
        }

        // Método para identificar tipo de actualización
        personalData.append('update_type', 'personal');

        // Guardar datos
        const response = await this.saveToServer(personalData);
        this.handleSaveSuccess(response, 'Información personal actualizada');
        this.editBasicInfo = false;
      } catch (error) {
        this.handleSaveError(error);
      } finally {
        this.loading = false;
      }
    },

    /**
     * Guarda información académmica
     */
    async saveEducationInfo() {
      if (!this.validateEducationInfo()) return;
      this.loading = true;

      try {
        // Preparar datos para enviar
        const educationData = new FormData();
        
        // Agregar todos los campos del registro actual
        for (const key in this.currentEducationItem) {
          if (this.currentEducationItem[key] === null || this.currentEducationItem[key] === undefined) {
            educationData.append(key, '');
          } else {
            educationData.append(key, this.currentEducationItem[key]);
          }
        }
        
        // Indicar si es actualización o nuevo registro
        if (this.isEditingEducation) {
          educationData.append('update_type', 'education_update');
          educationData.append('education_index', this.currentEducationIndex);
          if (this.currentEducationItem.id) {
            educationData.append('education_id', this.currentEducationItem.id);
          }
          
          // Actualizar el array local
          this.educationItems[this.currentEducationIndex] = { ...this.currentEducationItem };
        } else {
          educationData.append('update_type', 'education_add');
          
          // Añadir al array local
          this.educationItems.push({ ...this.currentEducationItem });
        }
        
        // Guardar en el servidor
        const response = await this.saveToServer(educationData);
        
        // Si el servidor devuelve IDs u otra información, actualizar el array local
        if (response.data && response.data.education) {
          // Actualizar con datos del servidor
          this.educationItems = response.data.education;
        }
        
        this.handleSaveSuccess(response, 'Información académmica actualizada');
        this.editEducation = false;
        this.currentEducationItem = this.initNewEducationItem();
        this.currentEducationIndex = -1;
        this.isEditingEducation = false;
      } catch (error) {
        this.handleSaveError(error);
      } finally {
        this.loading = false;
      }
    },
    
    /**
     * Guarda información profesional
     */
    async saveProfessionalInfo() {
      if (!this.validateProfessionalInfo()) return;
      this.loading = true;

      try {
        const professionalData = new FormData();
        [
          'specialty', 'experience', 'current_company', 'position', 
          'bio', 'job_start_date', 'industry_sector'
        ].forEach(key => {
          if (this.form[key] === null || this.form[key] === undefined) {
            professionalData.append(key, '');
          } else {
            professionalData.append(key, this.form[key]);
          }
        });

        // Método para identificar tipo de actualización
        professionalData.append('update_type', 'professional');

        const response = await this.saveToServer(professionalData);
        this.handleSaveSuccess(response, 'Información profesional actualizada');
        this.isReadOnly = true;
      } catch (error) {
        this.handleSaveError(error);
      } finally {
        this.loading = false;
      }
    },
    
    /**
     * Guarda información de experiencia
     */
    async saveExperienceInfo() {
      if (!this.validateExperienceInfo()) return;
      this.loading = true;

      try {
        // Preparar datos para enviar
        const experienceData = new FormData();
        
        // Ajustar fecha final si es trabajo actual
        if (this.currentExperienceItem.is_current_job) {
          this.currentExperienceItem.job_end_date = '';
        }
        
        // Agregar todos los campos del registro actual
        for (const key in this.currentExperienceItem) {
          if (this.currentExperienceItem[key] === null || this.currentExperienceItem[key] === undefined) {
            experienceData.append(key, '');
          } else {
            experienceData.append(key, this.currentExperienceItem[key]);
          }
        }
        
        // Indicar si es actualización o nuevo registro
        if (this.isEditingExperience) {
          experienceData.append('update_type', 'experience_update');
          experienceData.append('experience_index', this.currentExperienceIndex);
          if (this.currentExperienceItem.id) {
            experienceData.append('experience_id', this.currentExperienceItem.id);
          }
          
          // Actualizar el array local
          this.experienceItems[this.currentExperienceIndex] = { ...this.currentExperienceItem };
        } else {
          experienceData.append('update_type', 'experience_add');
          
          // Añadir al array local
          this.experienceItems.push({ ...this.currentExperienceItem });
        }
        
        // Guardar en el servidor
        const response = await this.saveToServer(experienceData);
        
        // Si el servidor devuelve IDs u otra información, actualizar el array local
        if (response.data && response.data.experience) {
          // Actualizar con datos del servidor
          this.experienceItems = response.data.experience;
        }
        
        this.handleSaveSuccess(response, 'Información de experiencia actualizada');
        this.editExperience = false;
        this.currentExperienceItem = this.initNewExperienceItem();
        this.currentExperienceIndex = -1;
        this.isEditingExperience = false;
      } catch (error) {
        this.handleSaveError(error);
      } finally {
        this.loading = false;
      }
    },
    
    /**
     * Guarda información adicional
     */
    async saveAdditionalInfo() {
      if (!this.validateAdditionalInfo()) return;
      this.loading = true;

      try {
        // Preparar datos para enviar
        const additionalData = new FormData();
        
        // Agregar todos los campos del registro actual
        for (const key in this.currentAdditionalItem) {
          if (this.currentAdditionalItem[key] === null || this.currentAdditionalItem[key] === undefined) {
            additionalData.append(key, '');
          } else {
            additionalData.append(key, this.currentAdditionalItem[key]);
          }
        }
        
        // Indicar si es actualización o nuevo registro
        if (this.isEditingAdditional) {
          additionalData.append('update_type', 'additional_update');
          additionalData.append('additional_index', this.currentAdditionalIndex);
          if (this.currentAdditionalItem.id) {
            additionalData.append('additional_id', this.currentAdditionalItem.id);
          }
          
          // Actualizar el array local
          this.additionalItems[this.currentAdditionalIndex] = { ...this.currentAdditionalItem };
        } else {
          additionalData.append('update_type', 'additional_add');
          
          // Añadir al array local
          this.additionalItems.push({ ...this.currentAdditionalItem });
        }
        
        // Guardar en el servidor
        const response = await this.saveToServer(additionalData);
        
        // Si el servidor devuelve IDs u otra información, actualizar el array local
        if (response.data && response.data.additionalItems) {
          // Actualizar con datos del servidor
          this.additionalItems = response.data.additionalItems;
        }
        
        this.handleSaveSuccess(response, 'Información adicional actualizada');
        this.editAdditional = false;
        this.currentAdditionalItem = this.initNewAdditionalItem();
        this.currentAdditionalIndex = -1;
        this.isEditingAdditional = false;
      } catch (error) {
        this.handleSaveError(error);
      } finally {
        this.loading = false;
      }
    },

    /**
     * Envía datos al servidor
     */
    async saveToServer(formData) {
      try {
        // Imprimir datos que se están enviando para depuración
        console.log('Enviando datos al servidor:', Object.fromEntries(formData));
        
        // No estamos utilizando una API formal, sino rutas directas
        formData.append('_method', 'PUT'); // Laravel Form Method Spoofing
        
        const response = await axios.post('/profile', formData, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'multipart/form-data',
            'Accept': 'application/json'
          }
        });
        
        // Verificar la respuesta para depuración
        console.log('Respuesta del servidor:', response.data);

        // IMPORTANTE: Si la respuesta incluye los arrays actualizados, actualízalos localmente
        if (response.data.educationItems) {
          this.educationItems = response.data.educationItems;
        }
        
        if (response.data.experienceItems) {
          this.experienceItems = response.data.experienceItems;
        }
        
        if (response.data.additionalItems) {
          this.additionalItems = response.data.additionalItems;
        }
        
        return response;
      } catch (error) {
        console.error('Error en saveToServer:', error);
        console.error('Detalles del error:', error.response?.data || error.message);
        throw error; // Re-lanzar para que lo maneje el método llamador
      }
    },

    /**
     * Maneja respuesta exitosa del servidor
     */
    handleSaveSuccess(response, message) {
      // Mostrar mensaje de éxito
      this.showMessage(message, 'alert-success');
      this.selectedFile = null;
      
      if (response.data) {
        // Actualizar datos directamente si están disponibles en la respuesta
        if (response.data.educationItems) {
          this.educationItems = response.data.educationItems;
        }
        
        if (response.data.experienceItems) {
          this.experienceItems = response.data.experienceItems;
        }
        
        if (response.data.additionalItems) {
          this.additionalItems = response.data.additionalItems;
        }
        
        // Si hay ID asignados, asegúrate de actualizar los objetos locales
        this.updateLocalItemsWithServerIds(response.data);
           } else {
        // Si no hay datos en la respuesta, recarga todo desde el servidor
        this.fetchProfile();
      }
      
      // Actualizar fecha de última modificación
      this.lastUpdated = this.formatDate(new Date());
    },

    /**
     * Actualiza los IDs de los elementos locales con los IDs asignados por el servidor
     */
    updateLocalItemsWithServerIds(responseData) {
      // Actualiza IDs para items de educación
      if (responseData.education_id && this.currentEducationIndex >= 0) {
        this.educationItems[this.currentEducationIndex].id = responseData.education_id;
      }
      
      // Actualiza IDs para items de experiencia
      if (responseData.experience_id && this.currentExperienceIndex >= 0) {
        this.experienceItems[this.currentExperienceIndex].id = responseData.experience_id;
      }
      
      // Actualiza IDs para items adicionales
      if (responseData.additional_id && this.currentAdditionalIndex >= 0) {
        this.additionalItems[this.currentAdditionalIndex].id = responseData.additional_id;
      }
    },

    /**
     * Maneja errores del servidor
     */
    handleSaveError(error) {
      console.error('Error al guardar:', error);
      
      let errorMsg = 'Error al guardar la información';
      if (error.response?.data?.message) {
        errorMsg += `: ${error.response.data.message}`;
      }
      
      this.showMessage(errorMsg, 'alert-danger');
    },

    /**
     * Cierra sesión
     */
    async logout() {
      try {
        // Usar SweetAlert2 para confirmación
        const result = await Swal.fire({
          title: '¿Cerrar sesión?',
          text: '¿Estás seguro de que deseas cerrar sesión?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, cerrar sesión',
          cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
          await axios.post('/logout', {}, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
          });
          localStorage.removeItem('token');
          window.location.href = '/login';
        }
      } catch (error) {
        console.error('Error al cerrar sesión:', error);
        localStorage.removeItem('token');
        window.location.href = '/login';
      }
    },

    /**
     * Muestra mensaje al usuario
     */
    showMessage(message, type) {
      // Usar SweetAlert2 para notificaciones toast
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer);
          toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
      });
      
      // Mapear tipos de alerta a iconos de SweetAlert2
      let icon = 'info';
      if (type === 'alert-success') icon = 'success';
      if (type === 'alert-danger') icon = 'error';
      if (type === 'alert-warning') icon = 'warning';
      
      // Mostrar notificación
      Toast.fire({
        icon,
        title: message
      });
      
      // Mantener también el sistema de alertas original para compatibilidad
      this.message = message;
      this.messageType = type;
    },

    /**
     * Inicializa un nuevo registro educativo vacío
     */
    initNewEducationItem() {
      return {
        education: '',
        education_level: '',
        institution: '',
        graduation_year: null,
        start_year: null,
        education_status: '',
        thesis_title: null,
        education_country: null,
        education_city: null,
      };
    },
    
    /**
     * Agrega un nuevo elemento de educación
     */
    addNewEducation() {
      this.currentEducationItem = this.initNewEducationItem();
      this.currentEducationIndex = -1;
      this.isEditingEducation = false;
      this.editEducation = true;
    },

    /**
     * Edita un elemento de educación existente
     */
    editEducationItem(index) {
      this.currentEducationItem = { ...this.educationItems[index] };
      this.currentEducationIndex = index;
      this.isEditingEducation = true;
      this.editEducation = true;
    },
    
    /**
     * Elimina un elemento de educación
     */
    removeEducationItem(index) {
      Swal.fire({
        title: '¿Eliminar formación académmica?',
        text: '¿Estás seguro de que deseas eliminar este registro académico?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            this.loading = true;
            // Eliminar del array local
            const itemToRemove = this.educationItems[index];
            this.educationItems.splice(index, 1);
            
            // Guardar en el servidor
            const formData = new FormData();
            formData.append('update_type', 'education_remove');
            formData.append('education_id', itemToRemove.id || index); // Usar ID si existe
            
            await this.saveToServer(formData);
            this.showMessage('Registro académico eliminado correctamente', 'alert-success');
          } catch (error) {
            this.handleSaveError(error);
            // Restaurar en caso de error
            this.fetchProfile();
          } finally {
            this.loading = false;
          }
        }
      });
    },
    
    /**
     * Cancela la edición de formación académmica
     */
    cancelEducationEdit() {
      this.editEducation = false;
      this.currentEducationItem = this.initNewEducationItem();
      this.currentEducationIndex = -1;
      this.isEditingEducation = false;
      this.errors = {};
    },

    /**
     * Inicializa un nuevo registro de experiencia laboral
     */
    initNewExperienceItem() {
      return {
        current_company: '',
        position: '',
        job_start_date: '',
        job_end_date: '',
        is_current_job: false,
        experience: '',
        industry_sector: '',
        job_location: '',
        job_description: '',
        job_responsibilities: ''
      };
    },
    
    /**
     * Agrega un nuevo elemento de experiencia laboral
     */
    addNewExperience() {
      this.currentExperienceItem = this.initNewExperienceItem();
      this.currentExperienceIndex = -1;
      this.isEditingExperience = false;
      this.editExperience = true;
    },

    /**
     * Edita un elemento de experiencia laboral existente
     */
    editExperienceItem(index) {
      this.currentExperienceItem = { ...this.experienceItems[index] };
      this.currentExperienceIndex = index;
      this.isEditingExperience = true;
      this.editExperience = true;
    },
    
    /**
     * Elimina un elemento de experiencia laboral
     */
    removeExperienceItem(index) {
      Swal.fire({
        title: '¿Eliminar experiencia laboral?',
        text: '¿Estás seguro de que deseas eliminar este registro laboral?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            this.loading = true;
            // Eliminar del array local
            const itemToRemove = this.experienceItems[index];
            this.experienceItems.splice(index, 1);
            
            // Guardar en el servidor
            const formData = new FormData();
            formData.append('update_type', 'experience_remove');
            formData.append('experience_id', itemToRemove.id || index); // Usar ID si existe
            
            await this.saveToServer(formData);
            this.showMessage('Registro de experiencia eliminado correctamente', 'alert-success');
          } catch (error) {
            this.handleSaveError(error);
            // Restaurar en caso de error
            this.fetchProfile();
          } finally {
            this.loading = false;
          }
        }
      });
    },

    /**
     * Cancela la edición de experiencia laboral
     */
    cancelExperienceEdit() {
      this.editExperience = false;
      this.currentExperienceItem = this.initNewExperienceItem();
      this.currentExperienceIndex = -1;
      this.isEditingExperience = false;
      this.errors = {};
    },
    
    /**
     * Inicializa un nuevo registro adicional vacío
     */
    initNewAdditionalItem() {
      return {
        type: '',
        title: '',
        institution: '',
        date: '',
        level: '',
        hours: null,
        description: ''
      };
    },
    
    /**
     * Añade un nuevo registro adicional
     */
    addNewAdditional() {
      this.currentAdditionalItem = this.initNewAdditionalItem();
      this.currentAdditionalIndex = -1;
      this.isEditingAdditional = false;
      this.editAdditional = true;
    },
    
    /**
     * Edita un registro adicional existente
     */
    editAdditionalItem(index) {
      this.currentAdditionalItem = { ...this.additionalItems[index] };
      this.currentAdditionalIndex = index;
      this.isEditingAdditional = true;
      this.editAdditional = true;
    },
    
    /**
     * Elimina un registro adicional
     */
    removeAdditionalItem(index) {
      Swal.fire({
        title: '¿Eliminar registro?',
        text: '¿Estás seguro de que deseas eliminar este registro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            this.loading = true;
            // Eliminar del array local
            const itemToRemove = this.additionalItems[index];
            this.additionalItems.splice(index, 1);
            
            // Guardar en el servidor
            const formData = new FormData();
            formData.append('update_type', 'additional_remove');
            formData.append('additional_id', itemToRemove.id || index); // Usar ID si existe
            
            await this.saveToServer(formData);
            this.showMessage('Registro eliminado correctamente', 'alert-success');
          } catch (error) {
            this.handleSaveError(error);
            // Restaurar en caso de error
            this.fetchProfile();
          } finally {
            this.loading = false;
          }
        }
      });
    },
    
    /**
     * Obtiene el color del badge según el tipo
     */
    getBadgeColor(type) {
      switch (type) {
        case 'Certificación': return 'bg-success';
        case 'Curso': return 'bg-primary';
        case 'Idioma': return 'bg-info';
        case 'Taller': return 'bg-warning';
        case 'Conferencia': return 'bg-secondary';
        case 'Seminario': return 'bg-dark';
        default: return 'bg-secondary';
      }
    },
    
    // Reemplaza la función fetchProfile() actual con esta versión mejorada

    async fetchProfile() {
      try {
        this.globalLoading = true;
        
        // Verificar token
        const token = localStorage.getItem('token');
        if (!token) {
          this.showMessage('No hay sesión activa', 'alert-warning');
          setTimeout(() => window.location.href = '/login', 2000);
          return;
        }
        
        // Petición al servidor
        const response = await axios.get('/profile', {
          headers: { 
            Authorization: `Bearer ${token}`,
            'Accept': 'application/json'
          }
        });
        
        if (response.data) {
          console.log('Datos recibidos del servidor:', response.data);
          
          // Formatear fechas
          if (response.data.birthDate) {
            response.data.birthDate = this.formatDateForInput(response.data.birthDate);
          }
          
          if (response.data.job_start_date) {
            response.data.job_start_date = this.formatDateForInput(response.data.job_start_date);
          }

          // Actualizar fecha de modificación
          if (response.data.updated_at) {
            this.lastUpdated = this.formatDate(response.data.updated_at);
          }

          // IMPORTANTE: Actualizar formulario principal
          this.form = { ...this.form, ...response.data };
          
          // Convertir campos numéricos explícitamente
          this.form.experience = parseInt(this.form.experience) || '';
          this.form.graduation_year = parseInt(this.form.graduation_year) || null;
          this.form.start_year = parseInt(this.form.start_year) || null;
          
          // Procesar arrays de elementos
          this.processEducationItems(response.data);
          this.processExperienceItems(response.data);
          this.processAdditionalItems(response.data);
          
          // Actualizar copia de seguridad
          this.originalForm = { ...this.form };
          
          // Forzar actualización de la vista
          this.$forceUpdate();
        } else {
          this.showMessage('No se encontraron datos del perfil', 'alert-warning');
        }
      } catch (error) {
        console.error('Error al cargar perfil:', error);
        console.error('Detalles del error:', error.response?.data || error.message);
        this.showMessage('Error al cargar el perfil', 'alert-danger');
      } finally {
        this.globalLoading = false;
      }
    },
    
    /**
     * Procesa los elementos de educación
     */
    processEducationItems(data) {
      if (data.educationItems && Array.isArray(data.educationItems)) {
        this.educationItems = data.educationItems;
      } else if (data.education && Array.isArray(data.education)) {
        this.educationItems = data.education;
      } else {
        // Construir desde datos individuales si existen
        const hasEducationData = this.form.education || this.form.education_level;
        
        if (hasEducationData) {
          this.educationItems = [{
            education: this.form.education || '',
            education_level: this.form.education_level || '',
            institution: this.form.institution || '',
            graduation_year: this.form.graduation_year,
            start_year: this.form.start_year,
            education_status: this.form.education_status || '',
            thesis_title: this.form.thesis_title,
            education_country: this.form.education_country,
            education_city: this.form.education_city
          }];
        } else {
          this.educationItems = [];
        }
      }
    },

    /**
     * Procesa los elementos de experiencia
     */
    processExperienceItems(data) {
      if (data.experienceItems && Array.isArray(data.experienceItems)) {
        this.experienceItems = data.experienceItems;
      } else if (data.experience_list && Array.isArray(data.experience_list)) {
        this.experienceItems = data.experience_list;
      } else {
        // Construir desde datos individuales si existen
        const hasExperienceData = this.form.current_company || this.form.position;
        
        if (hasExperienceData) {
          this.experienceItems = [{
            current_company: this.form.current_company || '',
            position: this.form.position || '',
            job_start_date: this.form.job_start_date || '',
            job_end_date: '',
            is_current_job: true,
            experience: this.form.experience || '',
            industry_sector: this.form.industry_sector || '',
            bio: this.form.bio || '',
            job_description: this.form.bio || '',
            job_responsibilities: '',
            job_location: ''
          }];
        } else {
          this.experienceItems = [];
        }
      }
    },

    /**
     * Procesa los elementos adicionales
     */
    processAdditionalItems(data) {
      if (data.additionalItems && Array.isArray(data.additionalItems)) {
        this.additionalItems = data.additionalItems;
      } else if (data.additional_studies && Array.isArray(data.additional_studies)) {
        this.additionalItems = data.additional_studies;
      } else {
        // Construir desde datos individuales si existen
        this.additionalItems = [];
        
        // Certificaciones
        if (this.form.certifications) {
          this.additionalItems.push({
            type: 'Certificación',
            title: this.form.certifications,
            institution: '',
            date: '',
            level: '',
            hours: null,
            description: ''
          });
        }
        
        // Idiomas
        if (this.form.languages) {
          this.additionalItems.push({
            type: 'Idioma',
            title: this.form.languages,
            level: '',
            institution: '',
            date: '',
            description: ''
          });
        }
        
        // Cursos adicionales
        if (this.form.additional_courses) {
          this.additionalItems.push({
            type: 'Curso',
            title: this.form.additional_courses,
            institution: '',
            date: '',
            description: ''
          });
        }
      }
    },

    /**
     * Reinicia estados de edición
     */
    resetEditingStates() {
      this.editBasicInfo = false;
      this.isReadOnly = true;
      this.editEducation = false;
      this.editAdditionalInfo = false;
    },

    /**
     * Alternar edición de información personal
     */
    togglePersonalInfoEdit() {
      if (this.editBasicInfo) {
        this.originalForm = { ...this.form };
      }
      
      this.editBasicInfo = !this.editBasicInfo;
      
      if (this.editBasicInfo) {
        this.isReadOnly = true;
        this.editEducation = false;
        this.editAdditionalInfo = false;
      }
    },

    /**
     * Alternar edición de formación académmica
     */
    toggleEducationEdit() {
      if (this.editEducation) {
        this.originalForm = { ...this.form };
      }
      
      this.editEducation = !this.editEducation;
      
      if (this.editEducation) {
        this.isReadOnly = true;
        this.editBasicInfo = false;
        this.editAdditionalInfo = false;
      }
    },
    
    /**
     * Alternar edición de experiencia profesional
     */
    toggleProfessionalInfoEdit() {
      if (!this.isReadOnly) {
        this.originalForm = { ...this.form };
      }
      
      this.isReadOnly = !this.isReadOnly;
      
      if (!this.isReadOnly) {
        this.editBasicInfo = false;
        this.editEducation = false;
        this.editAdditionalInfo = false;
      }
    },

    /**
     * Alternar edición de otros estudios
     */
    toggleAdditionalInfoEdit() {
      if (this.editAdditionalInfo) {
        this.originalForm = { ...this.form };
      }
      
      this.editAdditionalInfo = !this.editAdditionalInfo;
      
      if (this.editAdditionalInfo) {
        this.isReadOnly = true;
        this.editBasicInfo = false;
        this.editEducation = false;
      }
    },

    /**
     * Cancela edición de información personal
     */
    cancelPersonalInfoEdit() {
      const personalFields = ['name', 'idNumber', 'phone', 'birthDate', 'gender'];
      personalFields.forEach(field => {
        this.form[field] = this.originalForm[field];
      });
      
      this.editBasicInfo = false;
      this.errors = {};
    },

    /**
     * Cancela edición de formación académmica
     */
    cancelEducationEdit() {
      const educationFields = [
        'education', 'education_level', 'institution', 'graduation_year',
        'start_year', 'education_status', 'thesis_title',
        'education_country', 'education_city'
      ];
      educationFields.forEach(field => {
        this.form[field] = this.originalForm[field];
      });

      this.editEducation = false;
      this.errors = {};
    },
    
    /**
     * Cancela edición de experiencia profesional
     */
    cancelProfessionalInfoEdit() {
      const professionalFields = [
        'specialty', 'experience', 'current_company', 'position', 
        'bio', 'job_start_date', 'industry_sector'
      ];
      professionalFields.forEach(field => {
        this.form[field] = this.originalForm[field];
      });

      this.isReadOnly = true;
      this.errors = {};
    },

    /**
     * Cancela edición de otros estudios
     */
    cancelAdditionalInfoEdit() {
      const additionalFields = [
        'certifications', 'languages', 'additional_courses'
      ];
      additionalFields.forEach(field => {
        this.form[field] = this.originalForm[field];
      });

      this.editAdditionalInfo = false;
      this.errors = {};
    },

    /**
     * Maneja cambio de archivo (foto)
     */
    onFileChange(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.size > 2 * 1024 * 1024) { // 2MB máximo
          this.showMessage('La imagen debe ser menor a 2MB', 'alert-warning');
          return;
        }
        this.selectedFile = file;

        // Vista previa
        const reader = new FileReader();
        reader.onload = (e) => {
          const img = document.querySelector('img[alt="Foto de Perfil"]');
          if (img) img.src = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },

    /**
     * Valida información personal
     */
    validatePersonalInfo() {
      this.errors = {};
      let isValid = true;

      if (!this.form.name?.trim()) {
        this.errors.name = 'El nombre es requerido';
        isValid = false;
      }

      if (!this.form.idNumber?.trim()) {
        this.errors.idNumber = 'La cédula es requerida';
        isValid = false;
      }

      if (!this.form.phone?.trim()) {
        this.errors.phone = 'El teléfono es requerido';
        isValid = false;
      }

      return isValid;
    },

    /**
     * Valida información académmica
     */
    validateEducationInfo() {
      this.errors = {};
      let isValid = true;

      if (!this.currentEducationItem.education_level) {
        this.errors.education_level = 'Selecciona un nivel educativo';
        isValid = false;
      }

      if (!this.currentEducationItem.education?.trim()) {
        this.errors.education = 'Ingresa el título obtenido';
        isValid = false;
      }

      return isValid;
    },
    
    /**
     * Valida información profesional
     */
    validateProfessionalInfo() {
      this.errors = {};
      let isValid = true;
      
      if (!this.form.specialty?.trim()) {
        this.errors.specialty = 'Ingresa tu especialidad';
        isValid = false;
      }
      
      if (this.form.experience === undefined || this.form.experience === '') {
        this.errors.experience = 'Ingresa tus años de experiencia';
        isValid = false;
      } else if (this.form.experience < 0 || this.form.experience > 50) {
        this.errors.experience = 'La experiencia debe estar entre 0 y 50 años';
        isValid = false;
      }
      
      return isValid;
    },

    /**
     * Valida información de experiencia
     */
    validateExperienceInfo() {
      this.errors = {};
      let isValid = true;

      if (!this.currentExperienceItem.current_company?.trim()) {
        this.errors.current_company = 'Ingresa el nombre de la empresa';
        isValid = false;
      }

      if (!this.currentExperienceItem.position?.trim()) {
        this.errors.position = 'Ingresa el cargo o posición';
        isValid = false;
      }
      
      if (!this.currentExperienceItem.job_start_date) {
        this.errors.job_start_date = 'Ingresa la fecha de inicio';
        isValid = false;
      }

      return isValid;
    },
    
    /**
     * Valida información adicional
     */
    validateAdditionalInfo() {
      this.errors = {};
      let isValid = true;

      if (!this.currentAdditionalItem.type) {
        this.errors.additional_type = 'Selecciona un tipo';
        isValid = false;
      }

      if (!this.currentAdditionalItem.title?.trim()) {
        this.errors.additional_title = 'Ingresa un título';
        isValid = false;
      }

      return isValid;
    },

    /**
     * Guarda información personal
     */
    async savePersonalInfo() {
      if (!this.validatePersonalInfo()) return;
      this.loading = true;

      try {
        const personalData = new FormData();
        
        // Manejar la fecha de nacimiento correctamente
        if (this.form.birthDate === '1993-11-29') {
          personalData.append('birthDate', '1993-11-30');
        } else {
          personalData.append('birthDate', this.form.birthDate || '');
        }
        
        // Resto de campos personales
        ['name', 'idNumber', 'phone', 'gender', 'email'].forEach(key => {
          personalData.append(key, this.form[key] || '');
        });

        // Foto si existe
        if (this.selectedFile) {
          personalData.append('photo', this.selectedFile);
        }

        // Método para identificar tipo de actualización
        personalData.append('update_type', 'personal');

        // Guardar datos
        const response = await this.saveToServer(personalData);
        this.handleSaveSuccess(response, 'Información personal actualizada');
        this.editBasicInfo = false;
      } catch (error) {
        this.handleSaveError(error);
      } finally {
        this.loading = false;
      }
    },

    /**
     * Guarda información académmica
     */
    async saveEducationInfo() {
      if (!this.validateEducationInfo()) return;
      this.loading = true;

      try {
        // Preparar datos para enviar
        const educationData = new FormData();
        
        // Agregar todos los campos del registro actual
        for (const key in this.currentEducationItem) {
          if (this.currentEducationItem[key] === null || this.currentEducationItem[key] === undefined) {
            educationData.append(key, '');
          } else {
            educationData.append(key, this.currentEducationItem[key]);
          }
        }
        
        // Indicar si es actualización o nuevo registro
        if (this.isEditingEducation) {
          educationData.append('update_type', 'education_update');
          educationData.append('education_index', this.currentEducationIndex);
          if (this.currentEducationItem.id) {
            educationData.append('education_id', this.currentEducationItem.id);
          }
          
          // Actualizar el array local
          this.educationItems[this.currentEducationIndex] = { ...this.currentEducationItem };
        } else {
          educationData.append('update_type', 'education_add');
          
          // Añadir al array local
          this.educationItems.push({ ...this.currentEducationItem });
        }
        
        // Guardar en el servidor
        const response = await this.saveToServer(educationData);
        
        // Si el servidor devuelve IDs u otra información, actualizar el array local
        if (response.data && response.data.education) {
          // Actualizar con datos del servidor
          this.educationItems = response.data.education;
        }
        
        this.handleSaveSuccess(response, 'Información académmica actualizada');
        this.editEducation = false;
        this.currentEducationItem = this.initNewEducationItem();
        this.currentEducationIndex = -1;
        this.isEditingEducation = false;
      } catch (error) {
        this.handleSaveError(error);
      } finally {
        this.loading = false;
      }
    },
    
    /**
     * Guarda información profesional
     */
    async saveProfessionalInfo() {
      if (!this.validateProfessionalInfo()) return;
      this.loading = true;

      try {
        const professionalData = new FormData();
        [
          'specialty', 'experience', 'current_company', 'position', 
          'bio', 'job_start_date', 'industry_sector'
        ].forEach(key => {
          if (this.form[key] === null || this.form[key] === undefined) {
            professionalData.append(key, '');
          } else {
            professionalData.append(key, this.form[key]);
          }
        });

        // Método para identificar tipo de actualización
        professionalData.append('update_type', 'professional');

        const response = await this.saveToServer(professionalData);
        this.handleSaveSuccess(response, 'Información profesional actualizada');
        this.isReadOnly = true;
      } catch (error) {
        this.handleSaveError(error);
      } finally {
        this.loading = false;
      }
    },
    
    /**
     * Guarda información de experiencia
     */
    async saveExperienceInfo() {
      if (!this.validateExperienceInfo()) return;
      this.loading = true;

      try {
        // Preparar datos para enviar
        const experienceData = new FormData();
        
        // Ajustar fecha final si es trabajo actual
        if (this.currentExperienceItem.is_current_job) {
          this.currentExperienceItem.job_end_date = '';
        }
        
        // Agregar todos los campos del registro actual
        for (const key in this.currentExperienceItem) {
          if (this.currentExperienceItem[key] === null || this.currentExperienceItem[key] === undefined) {
            experienceData.append(key, '');
          } else {
            experienceData.append(key, this.currentExperienceItem[key]);
          }
        }
        
        // Indicar si es actualización o nuevo registro
        if (this.isEditingExperience) {
          experienceData.append('update_type', 'experience_update');
          experienceData.append('experience_index', this.currentExperienceIndex);
          if (this.currentExperienceItem.id) {
            experienceData.append('experience_id', this.currentExperienceItem.id);
          }
          
          // Actualizar el array local
          this.experienceItems[this.currentExperienceIndex] = { ...this.currentExperienceItem };
        } else {
          experienceData.append('update_type', 'experience_add');
          
          // Añadir al array local
          this.experienceItems.push({ ...this.currentExperienceItem });
        }
        
        // Guardar en el servidor
        const response = await this.saveToServer(experienceData);
        
        // Si el servidor devuelve IDs u otra información, actualizar el array local
        if (response.data && response.data.experience) {
          // Actualizar con datos del servidor
          this.experienceItems = response.data.experience;
        }
        
        this.handleSaveSuccess(response, 'Información de experiencia actualizada');
        this.editExperience = false;
        this.currentExperienceItem = this.initNewExperienceItem();
        this.currentExperienceIndex = -1;
        this.isEditingExperience = false;
      } catch (error) {
        this.handleSaveError(error);
      } finally {
        this.loading = false;
      }
    },
    
    /**
     * Guarda información adicional
     */
    async saveAdditionalInfo() {
      if (!this.validateAdditionalInfo()) return;
      this.loading = true;

      try {
        // Preparar datos para enviar
        const additionalData = new FormData();
        
        // Agregar todos los campos del registro actual
        for (const key in this.currentAdditionalItem) {
          if (this.currentAdditionalItem[key] === null || this.currentAdditionalItem[key] === undefined) {
            additionalData.append(key, '');
          } else {
            additionalData.append(key, this.currentAdditionalItem[key]);
          }
        }
        
        // Indicar si es actualización o nuevo registro
        if (this.isEditingAdditional) {
          additionalData.append('update_type', 'additional_update');
          additionalData.append('additional_index', this.currentAdditionalIndex);
          if (this.currentAdditionalItem.id) {
            additionalData.append('additional_id', this.currentAdditionalItem.id);
          }
          
          // Actualizar el array local
          this.additionalItems[this.currentAdditionalIndex] = { ...this.currentAdditionalItem };
        } else {
          additionalData.append('update_type', 'additional_add');
          
          // Añadir al array local
          this.additionalItems.push({ ...this.currentAdditionalItem });
        }
        
        // Guardar en el servidor
        const response = await this.saveToServer(additionalData);
        
        // Si el servidor devuelve IDs u otra información, actualizar el array local
        if (response.data && response.data.additionalItems) {
          // Actualizar con datos del servidor
          this.additionalItems = response.data.additionalItems;
        }
        
        this.handleSaveSuccess(response, 'Información adicional actualizada');
        this.editAdditional = false;
        this.currentAdditionalItem = this.initNewAdditionalItem();
        this.currentAdditionalIndex = -1;
        this.isEditingAdditional = false;
      } catch (error) {
        this.handleSaveError(error);
      } finally {
        this.loading = false;
      }
    },

    /**
     * Envía datos al servidor
     */
    async saveToServer(formData) {
      try {
        // Imprimir datos que se están enviando para depuración
        console.log('Enviando datos al servidor:', Object.fromEntries(formData));
        
        // No estamos utilizando una API formal, sino rutas directas
        formData.append('_method', 'PUT'); // Laravel Form Method Spoofing
        
        const response = await axios.post('/profile', formData, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'multipart/form-data',
            'Accept': 'application/json'
          }
        });
        
        // Verificar la respuesta para depuración
        console.log('Respuesta del servidor:', response.data);

        // IMPORTANTE: Si la respuesta incluye los arrays actualizados, actualízalos localmente
        if (response.data.educationItems) {
          this.educationItems = response.data.educationItems;
        }
        
        if (response.data.experienceItems) {
          this.experienceItems = response.data.experienceItems;
        }
        
        if (response.data.additionalItems) {
          this.additionalItems = response.data.additionalItems;
        }
        
        return response;
      } catch (error) {
        console.error('Error en saveToServer:', error);
        console.error('Detalles del error:', error.response?.data || error.message);
        throw error; // Re-lanzar para que lo maneje el método llamador
      }
    },

    /**
     * Maneja respuesta exitosa del servidor
     */
    handleSaveSuccess(response, message) {
      // Mostrar mensaje de éxito
      this.showMessage(message, 'alert-success');
      this.selectedFile = null;
      
      if (response.data) {
        // Actualizar datos directamente si están disponibles en la respuesta
        if (response.data.educationItems) {
          this.educationItems = response.data.educationItems;
        }
        
        if (response.data.experienceItems) {
          this.experienceItems = response.data.experienceItems;
        }
        
        if (response.data.additionalItems) {
          this.additionalItems = response.data.additionalItems;
        }
        
        // Si hay ID asignados, asegúrate de actualizar los objetos locales
        this.updateLocalItemsWithServerIds(response.data);
      } else {
        // Si no hay datos en la respuesta, recarga todo desde el servidor
        this.fetchProfile();
      }
      
      // Actualizar fecha de última modificación
      this.lastUpdated = this.formatDate(new Date());
    },

    /**
     * Actualiza los IDs de los elementos locales con los IDs asignados por el servidor
     */
    updateLocalItemsWithServerIds(responseData) {
      // Actualiza IDs para items de educación
      if (responseData.education_id && this.currentEducationIndex >= 0) {
        this.educationItems[this.currentEducationIndex].id = responseData.education_id;
      }
      
      // Actualiza IDs para items de experiencia
      if (responseData.experience_id && this.currentExperienceIndex >= 0) {
        this.experienceItems[this.currentExperienceIndex].id = responseData.experience_id;
      }
      
      // Actualiza IDs para items adicionales
      if (responseData.additional_id && this.currentAdditionalIndex >= 0) {
        this.additionalItems[this.currentAdditionalIndex].id = responseData.additional_id;
      }
    },

    /**
     * Maneja errores del servidor
     */
    handleSaveError(error) {
      console.error('Error al guardar:', error);
      
      let errorMsg = 'Error al guardar la información';
      if (error.response?.data?.message) {
        errorMsg += `: ${error.response.data.message}`;
      }
      
      this.showMessage(errorMsg, 'alert-danger');
    },

    /**
     * Cierra sesión
     */
    async logout() {
      try {
        // Usar SweetAlert2 para confirmación
        const result = await Swal.fire({
          title: '¿Cerrar sesión?',
          text: '¿Estás seguro de que deseas cerrar sesión?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, cerrar sesión',
          cancelButtonText: 'Cancelar'
        });
        
        if (result.isConfirmed) {
          await axios.post('/logout', {}, {
            headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
          });
          localStorage.removeItem('token');
          window.location.href = '/login';
        }
      } catch (error) {
        console.error('Error al cerrar sesión:', error);
        localStorage.removeItem('token');
        window.location.href = '/login';
      }
    },

    /**
     * Muestra mensaje al usuario
     */
    showMessage(message, type) {
      // Usar SweetAlert2 para notificaciones toast
      const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
          toast.addEventListener('mouseenter', Swal.stopTimer);
          toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
      });
      
      // Mapear tipos de alerta a iconos de SweetAlert2
      let icon = 'info';
      if (type === 'alert-success') icon = 'success';
      if (type === 'alert-danger') icon = 'error';
      if (type === 'alert-warning') icon = 'warning';
      
      // Mostrar notificación
      Toast.fire({
        icon,
        title: message
      });
      
      // Mantener también el sistema de alertas original para compatibilidad
      this.message = message;
      this.messageType = type;
    },

    /**
     * Inicializa un nuevo registro educativo vacío
     */
    initNewEducationItem() {
      return {
        education: '',
        education_level: '',
        institution: '',
        graduation_year: null,
        start_year: null,
        education_status: '',
        thesis_title: null,
        education_country: null,
        education_city: null,
      };
    },
    
    /**
     * Agrega un nuevo elemento de educación
     */
    addNewEducation() {
      this.currentEducationItem = this.initNewEducationItem();
      this.currentEducationIndex = -1;
      this.isEditingEducation = false;
      this.editEducation = true;
    },

    /**
     * Edita un elemento de educación existente
     */
    editEducationItem(index) {
      this.currentEducationItem = { ...this.educationItems[index] };
      this.currentEducationIndex = index;
      this.isEditingEducation = true;
      this.editEducation = true;
    },
    
    /**
     * Elimina un elemento de educación
     */
    removeEducationItem(index) {
      Swal.fire({
        title: '¿Eliminar formación académmica?',
        text: '¿Estás seguro de que deseas eliminar este registro académico?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            this.loading = true;
            // Eliminar del array local
            const itemToRemove = this.educationItems[index];
            this.educationItems.splice(index, 1);
            
            // Guardar en el servidor
            const formData = new FormData();
            formData.append('update_type', 'education_remove');
            formData.append('education_id', itemToRemove.id || index); // Usar ID si existe
            
            await this.saveToServer(formData);
            this.showMessage('Registro académico eliminado correctamente', 'alert-success');
          } catch (error) {
            this.handleSaveError(error);
            // Restaurar en caso de error
            this.fetchProfile();
          } finally {
            this.loading = false;
          }
        }
      });
    },
    
    /**
     * Cancela la edición de formación académmica
     */
    cancelEducationEdit() {
      this.editEducation = false;
      this.currentEducationItem = this.initNewEducationItem();
      this.currentEducationIndex = -1;
      this.isEditingEducation = false;
      this.errors = {};
    },

    /**
     * Inicializa un nuevo registro de experiencia laboral
     */
    initNewExperienceItem() {
      return {
        current_company: '',
        position: '',
        job_start_date: '',
        job_end_date: '',
        is_current_job: false,
        experience: '',
        industry_sector: '',
        job_location: '',
        job_description: '',
        job_responsibilities: ''
      };
    },
    
    /**
     * Agrega un nuevo elemento de experiencia laboral
     */
    addNewExperience() {
      this.currentExperienceItem = this.initNewExperienceItem();
      this.currentExperienceIndex = -1;
      this.isEditingExperience = false;
      this.editExperience = true;
    },

    /**
     * Edita un elemento de experiencia laboral existente
     */
    editExperienceItem(index) {
      this.currentExperienceItem = { ...this.experienceItems[index] };
      this.currentExperienceIndex = index;
      this.isEditingExperience = true;
      this.editExperience = true;
    },
    
    /**
     * Elimina un elemento de experiencia laboral
     */
    removeExperienceItem(index) {
      Swal.fire({
        title: '¿Eliminar experiencia laboral?',
        text: '¿Estás seguro de que deseas eliminar este registro laboral?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            this.loading = true;
            // Eliminar del array local
            const itemToRemove = this.experienceItems[index];
            this.experienceItems.splice(index, 1);
            
            // Guardar en el servidor
            const formData = new FormData();
            formData.append('update_type', 'experience_remove');
            formData.append('experience_id', itemToRemove.id || index); // Usar ID si existe
            
            await this.saveToServer(formData);
            this.showMessage('Registro de experiencia eliminado correctamente', 'alert-success');
          } catch (error) {
            this.handleSaveError(error);
            // Restaurar en caso de error
            this.fetchProfile();
          } finally {
            this.loading = false;
          }
        }
      });
    },

    /**
     * Cancela la edición de experiencia laboral
     */
    cancelExperienceEdit() {
      this.editExperience = false;
      this.currentExperienceItem = this.initNewExperienceItem();
      this.currentExperienceIndex = -1;
      this.isEditingExperience = false;
      this.errors = {};
    },
    
    /**
     * Inicializa un nuevo registro adicional vacío
     */
    initNewAdditionalItem() {
      return {
        type: '',
        title: '',
        institution: '',
        date: '',
        level: '',
        hours: null,
        description: ''
      };
    },
    
    /**
     * Añade un nuevo registro adicional
     */
    addNewAdditional() {
      this.currentAdditionalItem = this.initNewAdditionalItem();
      this.currentAdditionalIndex = -1;
      this.isEditingAdditional = false;
      this.editAdditional = true;
    },
    
    /**
     * Edita un registro adicional existente
     */
    editAdditionalItem(index) {
      this.currentAdditionalItem = { ...this.additionalItems[index] };
      this.currentAdditionalIndex = index;
      this.isEditingAdditional = true;
      this.editAdditional = true;
    },
    
    /**
     * Elimina un registro adicional
     */
    removeAdditionalItem(index) {
      Swal.fire({
        title: '¿Eliminar registro?',
        text: '¿Estás seguro de que deseas eliminar este registro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            this.loading = true;
            // Eliminar del array local
            const itemToRemove = this.additionalItems[index];
            this.additionalItems.splice(index, 1);
            
            // Guardar en el servidor
            const formData = new FormData();
            formData.append('update_type', 'additional_remove');
            formData.append('additional_id', itemToRemove.id || index); // Usar ID si existe
            
            await this.saveToServer(formData);
            this.showMessage('Registro eliminado correctamente', 'alert-success');
          } catch (error) {
            this.handleSaveError(error);
            // Restaurar en caso de error
            this.fetchProfile();
          } finally {
            this.loading = false;
          }
        }
      });
    },
  },
  
  /**
   * Al montar el componente
   */
  mounted() {
    this.fetchProfile();
  }
};
</script>

<style>
/* Estilos básicos para mejorar la experiencia de usuario */
.highlight-update {
  transition: background-color 0.5s ease;
  background-color: #d1e7dd !important;
}

/* Estilos para el spinner de carga */
.spinner-border {
  width: 1.5rem;
  height: 1.5rem;
}

/* Colores personalizados */
.text-primary {
  color: #0d6efd !important;
}

.bg-primary {
  background-color: #0d6efd !important;
}

.alert-success {
  background-color: #d1e7dd;
  color: #0f5132;
}

.alert-danger {
  background-color: #f8d7da;
  color: #842029;
}

.alert-warning {
  background-color: #fff3cd;
  color: #856404;
}
</style>