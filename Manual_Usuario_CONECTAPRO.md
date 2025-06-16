# Manual de Usuario - CONECTAPRO

## Plataforma de Conexión de Clientes con Profesionales Técnicos

---

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Requisitos del Sistema](#requisitos-del-sistema)
3. [Acceso a la Plataforma](#acceso-a-la-plataforma)
4. [Registro de Usuario](#registro-de-usuario)
5. [Inicio de Sesión](#inicio-de-sesión)
6. [Tipos de Usuario](#tipos-de-usuario)
7. [Para Clientes](#para-clientes)
8. [Para Técnicos](#para-técnicos)
9. [Sistema de Calificaciones y Reseñas](#sistema-de-calificaciones-y-reseñas)
10. [Notificaciones](#notificaciones)
11. [Solución de Problemas](#solución-de-problemas)
12. [Preguntas Frecuentes](#preguntas-frecuentes)
13. [Contacto y Soporte](#contacto-y-soporte)

---

## 🚀 Introducción

**CONECTAPRO** es una plataforma digital que conecta clientes que necesitan servicios técnicos de mantenimiento con profesionales calificados. La plataforma facilita la solicitud, cotización y seguimiento de servicios de mantenimiento preventivo, correctivo y especializado para equipos electrónicos, computadoras y otros dispositivos.

### Beneficios Principales:
- **Para Clientes**: Acceso a técnicos calificados, cotizaciones transparentes, seguimiento en tiempo real
- **Para Técnicos**: Mayor visibilidad, gestión de servicios, sistema de calificaciones profesional
- **Para Ambos**: Comunicación directa, historial de servicios, sistema de pagos seguro

---

## 💻 Requisitos del Sistema

### Navegadores Compatibles:
- Google Chrome (versión 90 o superior)
- Mozilla Firefox (versión 88 o superior)
- Microsoft Edge (versión 90 o superior)
- Safari (versión 14 o superior)

### Dispositivos Soportados:
- Computadoras de escritorio
- Laptops
- Tablets
- Smartphones (responsive design)

### Conexión a Internet:
- Conexión estable recomendada para carga de archivos
- Ancho de banda mínimo: 1 Mbps

---

## 🌐 Acceso a la Plataforma

### URL de Acceso:
```
http://localhost/CONECTAPRO
```

### Página Principal:
La página de inicio presenta:
- **Navegación principal**: Servicios, Cómo Funciona, FAQ, Contacto
- **Botones de acceso**: Iniciar Sesión y Registrarse
- **Información sobre servicios disponibles**
- **Testimonios de usuarios**

---

## 📝 Registro de Usuario

### Paso 1: Acceder al Formulario de Registro
1. Haga clic en **"Registrarse"** en la página principal
2. Seleccione el tipo de usuario:
   - **Cliente**: Para solicitar servicios
   - **Técnico**: Para ofrecer servicios

### Paso 2: Completar Información Personal
Campos obligatorios marcados con *:
- **Nombre completo** *
- **Número de identificación**
- **Teléfono**
- **Fecha de nacimiento**
- **Género**: Masculino, Femenino, Otro
- **Correo electrónico** *
- **Contraseña** * (mínimo 8 caracteres)
- **Confirmar contraseña** *

### Paso 3: Información de Ubicación
- **Ciudad**
- **Dirección**
- **Código postal**

### Paso 4: Confirmación
1. Revise los datos ingresados
2. Acepte los términos y condiciones
3. Haga clic en **"Registrar Cuenta"**
4. Recibirá una confirmación por email

### Validaciones del Sistema:
- Email único en el sistema
- Contraseña segura (8+ caracteres)
- Número de identificación único
- Formato de email válido

---

## 🔐 Inicio de Sesión

### Proceso de Login:
1. Haga clic en **"Iniciar Sesión"**
2. Ingrese su **email** y **contraseña**
3. Haga clic en **"Iniciar Sesión"**

### Opciones Adicionales:
- **"Recordar sesión"**: Mantiene la sesión activa
- **"¿Olvidaste tu contraseña?"**: Para recuperar acceso

### Seguridad:
- Las sesiones usan tokens seguros (Laravel Sanctum)
- Auto-logout por inactividad
- Protección contra accesos no autorizados

---

## 👥 Tipos de Usuario

### Cliente
- Solicita servicios técnicos
- Gestiona sus solicitudes
- Califica técnicos
- Accede a historial de servicios

### Técnico
- Ofrece servicios profesionales
- Gestiona su perfil profesional
- Recibe y cotiza solicitudes
- Construye reputación a través de calificaciones

---

## 🛠️ Para Clientes

### Dashboard Principal
Al iniciar sesión, los clientes acceden a:
- **Resumen de solicitudes activas**
- **Acceso rápido a "Solicitar Servicio"**
- **Historial de servicios**
- **Notificaciones**

### Solicitar un Servicio

#### Paso 1: Acceder al Formulario
- Desde el dashboard: **"Solicitar Servicio"**
- Desde la navegación: **"Solicitar Servicio"**

#### Paso 2: Información del Servicio
**Tipo de Servicio**:
- Mantenimiento Preventivo
- Mantenimiento Correctivo
- Instalación
- Reparación
- Otro (especificar)

**Detalles del Equipo**:
- Tipo de equipo: Desktop, Laptop, Impresora, etc.
- Marca del equipo
- Descripción detallada del problema/servicio

#### Paso 3: Información Adicional
- **Nivel de urgencia**: Baja, Media, Alta
- **Dirección del servicio**
- **Fecha preferida**
- **Hora preferida**
- **Comentarios adicionales**

#### Paso 4: Archivos Adjuntos
- **Fotos del equipo**: Hasta 5 imágenes (5MB c/u)
- Formatos: JPG, PNG, GIF
- Ayuda a los técnicos a entender mejor el problema

#### Paso 5: Confirmación
1. Revise toda la información
2. Haga clic en **"Enviar Solicitud"**
3. Recibirá un número de solicitud único (ej: SR-2024-0001)

### Gestión de Solicitudes

#### Mis Solicitudes
**Ubicación**: Dashboard → "Mis Solicitudes"

**Estados de Solicitud**:
- **Pendiente**: Esperando asignación de técnico
- **Cotizado**: Técnico ha enviado cotización
- **Aceptado**: Cliente aceptó cotización
- **Asignado**: Técnico confirmado para el servicio
- **En Progreso**: Servicio en ejecución
- **Completado**: Servicio finalizado
- **Cancelado**: Solicitud cancelada

#### Acciones Disponibles:
**Ver Detalles**: Información completa de la solicitud
- Número de solicitud
- Descripción del servicio
- Estado actual
- Información del técnico asignado
- Historial de cambios

**Ver Técnico**: Perfil del técnico asignado
- Información profesional
- Especialidades
- Calificaciones y reseñas
- Información de contacto

**Calificar Servicio**: Disponible al completarse
- Calificación general (1-5 estrellas)
- Calificaciones específicas:
  - Calidad del trabajo
  - Puntualidad
  - Comunicación
  - Precio/valor
- Comentarios escritos
- Aspectos positivos y negativos
- Recomendación (Sí/No)

### Sistema de Filtros
**Filtrar por Estado**:
- Todos los estados
- Pendiente
- Cotizado
- Aceptado
- En progreso
- Completado
- Cancelado

**Búsqueda**:
- Por número de solicitud
- Por descripción
- Por tipo de servicio

### Historial de Servicios
- **Servicios completados**
- **Técnicos contratados anteriormente**
- **Calificaciones otorgadas**
- **Descargas de facturas/comprobantes**

---

## 🔧 Para Técnicos

### Perfil Profesional

#### Información Personal
**Datos Básicos** (editables):
- Nombre completo
- Número de identificación
- Teléfono de contacto
- Fecha de nacimiento
- Género
- Dirección y ciudad
- Foto de perfil profesional

#### Información Técnica
**Especialidad Principal**:
- Reparación de Hardware
- Mantenimiento Preventivo
- Instalación de Software
- Redes y Conectividad
- Otro (especificar)

**Experiencia**:
- Años de experiencia
- Descripción profesional (bio)
- Tarifa por hora

### Educación y Certificaciones

#### Agregar Educación
**Campos requeridos**:
- Nivel educativo
- Institución
- Título/Grado obtenido
- Año de graduación
- Certificado (archivo PDF/imagen)

**Gestión**:
- Agregar múltiples registros educativos
- Editar información existente
- Subir certificados de estudios
- Orden cronológico automático

#### Experiencia Laboral
**Información por trabajo**:
- Nombre de la empresa
- Cargo desempeñado
- Nombre del supervisor
- Teléfono del supervisor
- Fecha de inicio
- Fecha de finalización
- "Trabajo actual" (checkbox)
- Descripción de actividades

**Validación**:
- Fechas de inicio anteriores a fechas de fin
- Información de supervisores para referencias
- Descripción detallada de responsabilidades

### Dashboard del Técnico

#### Métricas Principales
- **Calificación promedio**: Basada en reseñas de clientes
- **Total de trabajos**: Servicios completados
- **Tasa de finalización**: Porcentaje de trabajos completados exitosamente
- **Tiempo de respuesta promedio**: Para cotizaciones
- **Completitud del perfil**: Porcentaje de información completada

#### Solicitudes Pendientes
- **Nuevas solicitudes**: Requieren cotización
- **Solicitudes asignadas**: Confirmadas para ejecución
- **Trabajos en progreso**: En ejecución actual

#### Configuración de Disponibilidad
**Estado General**:
- Disponible/No disponible
- Tarifa por hora actual
- Áreas de servicio (ciudades)

**Horarios de Trabajo**:
- Configuración por días de la semana
- Horarios específicos
- Servicios de emergencia
- Radio de desplazamiento

**Servicios Especializados**:
- Trabajo remoto disponible
- Servicios a domicilio
- Tarifa mínima de servicio
- Método de contacto preferido

### Gestión de Solicitudes

#### Recibir Solicitudes
**Notificaciones automáticas** cuando:
- Nueva solicitud coincide con especialidad
- Cliente acepta cotización
- Cambios en solicitudes asignadas

#### Proceso de Cotización
1. **Revisar detalles** de la solicitud
2. **Evaluar fotos** adjuntas por el cliente
3. **Crear cotización** con:
   - Descripción del trabajo a realizar
   - Costo estimado
   - Tiempo estimado
   - Incluye/no incluye materiales
   - Validez de la cotización

#### Estados de Seguimiento
- **Solicitudes por cotizar**: Nuevas asignaciones
- **Cotizaciones enviadas**: Esperando respuesta del cliente
- **Trabajos confirmados**: Cliente aceptó cotización
- **En ejecución**: Trabajos activos
- **Completados**: Trabajos finalizados

### Sistema de Calificaciones

#### Recibir Calificaciones
**Criterios evaluados**:
- **Calificación general** (1-5 estrellas)
- **Calidad del trabajo** (1-5 estrellas)
- **Puntualidad** (1-5 estrellas)
- **Comunicación** (1-5 estrellas)
- **Relación precio/valor** (1-5 estrellas)

**Comentarios de clientes**:
- Aspectos positivos del servicio
- Aspectos a mejorar
- Recomendación general

#### Impacto en el Perfil
- **Promedio general**: Visible en búsquedas
- **Desglose por categoría**: Detalle de fortalezas
- **Testimonios públicos**: Construyen reputación
- **Ranking en búsquedas**: Mejor posicionamiento

---

## ⭐ Sistema de Calificaciones y Reseñas

### Para Clientes: Calificar un Servicio

#### Cuándo Calificar
- Disponible después de completar un servicio
- Botón "Calificar" en la solicitud completada
- Recordatorios automáticos por notificación

#### Proceso de Calificación
1. **Acceder** desde "Mis Solicitudes" → "Calificar"
2. **Calificación General**: 1-5 estrellas (obligatorio)
3. **Calificaciones Específicas**:
   - Calidad del trabajo (1-5 estrellas)
   - Puntualidad (1-5 estrellas)
   - Comunicación (1-5 estrellas)
   - Relación precio/valor (1-5 estrellas)

#### Comentarios Detallados
- **Comentario general**: Experiencia completa
- **Aspectos positivos**: Lo que más gustó
- **Aspectos a mejorar**: Sugerencias constructivas
- **¿Recomendarías?**: Sí/No

#### Política de Reseñas
- **Una reseña por servicio**
- **Modificable dentro de 48 horas**
- **Verificación automática**: Solo clientes que recibieron el servicio
- **Moderación**: Contenido inapropiado será removido

### Para Técnicos: Gestión de Reseñas

#### Visualización de Calificaciones
**En el perfil profesional**:
- Promedio general destacado
- Desglose por categorías
- Número total de reseñas
- Reseñas recientes

#### Respuesta a Reseñas
- **Agradecer** comentarios positivos
- **Abordar constructivamente** críticas
- **Mantener profesionalismo** en respuestas
- **Límite de caracteres** en respuestas

#### Impacto en la Visibilidad
- **Algoritmo de búsqueda** favorece mejores calificaciones
- **Badge de "Técnico Verificado"** para profesionales destacados
- **Posicionamiento preferencial** en listados
- **Estadísticas públicas** de rendimiento

---

## 🔔 Notificaciones

### Tipos de Notificaciones

#### Para Clientes
- **Nueva cotización recibida**
- **Técnico asignado** a solicitud
- **Cambio de estado** en solicitud
- **Recordatorio de calificación**
- **Solicitud completada**

#### Para Técnicos
- **Nueva solicitud** en su especialidad
- **Cliente aceptó cotización**
- **Nueva reseña recibida**
- **Recordatorio de trabajos pendientes**
- **Actualizaciones del sistema**

### Gestión de Notificaciones

#### Centro de Notificaciones
**Ubicación**: Icono de campana en la barra superior

**Funcionalidades**:
- **Contador** de notificaciones no leídas
- **Vista previa** de notificaciones recientes
- **Marcar como leída** individualmente
- **Marcar todas como leídas**
- **Ver todas las notificaciones**

#### Configuración
**Preferencias de notificación**:
- Notificaciones por email
- Notificaciones en la plataforma
- Frecuencia de recordatorios
- Tipos de notificaciones activas

### Estados de Notificación
- **No leída**: Fondo resaltado, contador activo
- **Leída**: Fondo normal, sin contador
- **Archivada**: No visible en vista principal

---

## 🚨 Solución de Problemas

### Problemas de Acceso

#### No puedo iniciar sesión
**Verificar**:
1. Email y contraseña correctos
2. Cuenta activada (revisar email de confirmación)
3. Conexión a internet estable
4. Navegador compatible y actualizado

**Soluciones**:
- Usar "¿Olvidaste tu contraseña?"
- Verificar bandeja de spam para emails del sistema
- Limpiar caché y cookies del navegador
- Intentar con otro navegador

#### Olvide mi contraseña
1. Click en **"¿Olvidaste tu contraseña?"**
2. Ingrese su email registrado
3. Revise su correo (incluir spam)
4. Siga el enlace para restablecer
5. Cree una nueva contraseña segura

### Problemas con Solicitudes

#### Mi solicitud no aparece
**Verificar**:
- Estado de la conexión durante el envío
- Filtros aplicados en "Mis Solicitudes"
- Navegación correcta al área de solicitudes

**Solución**:
- Refrescar la página
- Verificar todos los estados de filtro
- Contactar soporte con número de solicitud

#### No recibo cotizaciones
**Posibles causas**:
- Solicitud muy específica o poco común
- Área geográfica con pocos técnicos
- Información insuficiente en la solicitud

**Recomendaciones**:
- Agregar más detalles y fotos
- Considerar ampliar el área de servicio
- Revisar urgencia asignada

### Problemas Técnicos

#### La página no carga correctamente
**Soluciones inmediatas**:
1. Refrescar página (F5 o Ctrl+R)
2. Limpiar caché del navegador
3. Desactivar temporalmente extensiones
4. Probar navegación privada/incógnito

#### Problemas subiendo archivos
**Verificar**:
- Tamaño del archivo (máximo 5MB por imagen)
- Formato soportado (JPG, PNG, GIF, PDF)
- Conexión a internet estable
- Espacio disponible en el dispositivo

#### Problemas de rendimiento
**Optimizaciones**:
- Cerrar pestañas innecesarias
- Verificar recursos del sistema
- Usar navegador actualizado
- Reiniciar navegador

---

## ❓ Preguntas Frecuentes

### General

**¿Es gratuito usar CONECTAPRO?**
- El registro y uso básico de la plataforma es gratuito
- Los técnicos pueden aplicar comisiones por servicios prestados
- No hay costos ocultos para clientes

**¿Cómo se garantiza la calidad de los técnicos?**
- Proceso de verificación de credenciales
- Sistema de calificaciones y reseñas
- Historial transparente de trabajos
- Política de técnicos verificados

**¿Qué tipos de servicios están disponibles?**
- Mantenimiento preventivo
- Reparaciones correctivas
- Instalación de software/hardware
- Soporte técnico remoto
- Diagnóstico de problemas
- Servicios especializados por técnico

### Para Clientes

**¿Cómo elijo el mejor técnico?**
- Revisar calificaciones y reseñas
- Verificar especialidad y experiencia
- Comparar cotizaciones recibidas
- Considerar proximidad geográfica

**¿Puedo cancelar una solicitud?**
- Sí, antes de aceptar una cotización
- Después de aceptar, coordinar con el técnico
- Políticas de cancelación pueden aplicar

**¿Cómo funciona el pago?**
- Acordar método de pago con el técnico
- Pago directo entre cliente y técnico
- Plataforma facilita la conexión, no procesa pagos

### Para Técnicos

**¿Cómo aumento mi visibilidad?**
- Completar 100% del perfil profesional
- Obtener calificaciones altas consistentes
- Responder rápidamente a solicitudes
- Mantener disponibilidad actualizada
- Subir certificaciones y credenciales

**¿Puedo trabajar en múltiples ciudades?**
- Sí, configurar en "Áreas de servicio"
- Actualizar disponibilidad por ubicación
- Considerar costos de desplazamiento

**¿Cómo manejo disputas con clientes?**
- Comunicación directa y profesional
- Documentar acuerdos por escrito
- Contactar soporte de plataforma si es necesario
- Mantener evidencia del trabajo realizado

### Técnicas

**¿Puedo usar la plataforma en mi móvil?**
- Sí, la plataforma es responsive
- Funcionalidad completa en dispositivos móviles
- Aplicación móvil nativa en desarrollo

**¿Qué hago si encuentro un error?**
- Tomar captura de pantalla del error
- Anotar pasos que llevaron al problema
- Contactar soporte técnico
- Proporcionar información del navegador y dispositivo

---

## 📞 Contacto y Soporte

### Canales de Soporte

#### Soporte Técnico
**Email**: soporte@conectapro.com
**Horario**: Lunes a Viernes, 8:00 AM - 6:00 PM
**Tiempo de respuesta**: 24-48 horas

#### Soporte para Técnicos
**Email**: tecnicos@conectapro.com
**Especializado en**: Verificación de cuentas, gestión de perfil profesional

#### Soporte para Clientes
**Email**: clientes@conectapro.com
**Especializado en**: Solicitudes de servicio, problemas de facturación

### Información de Contacto

**Dirección Física**:
[Insertar dirección de la empresa]

**Teléfono Principal**:
[Insertar número de teléfono]

**Redes Sociales**:
- Facebook: /ConectaProOficial
- Instagram: @conectapro
- LinkedIn: /company/conectapro

### Horarios de Atención
- **Lunes a Viernes**: 8:00 AM - 6:00 PM
- **Sábados**: 9:00 AM - 2:00 PM
- **Domingos y Festivos**: Cerrado

### Soporte de Emergencia
Para problemas críticos de la plataforma:
**Email de emergencia**: emergencia@conectapro.com
**Disponible**: 24/7 para problemas que afecten la operación

---

## 📋 Anexos

### Códigos de Estado de Solicitudes

| Código | Estado | Descripción |
|--------|--------|-------------|
| pending | Pendiente | Solicitud creada, esperando asignación |
| quoted | Cotizado | Técnico envió cotización |
| accepted | Aceptado | Cliente aceptó cotización |
| assigned | Asignado | Técnico confirmado |
| in_progress | En Progreso | Servicio en ejecución |
| completed | Completado | Servicio finalizado exitosamente |
| cancelled | Cancelado | Solicitud cancelada |

### Formatos de Archivo Soportados

**Imágenes**:
- JPG/JPEG (hasta 5MB)
- PNG (hasta 5MB)
- GIF (hasta 5MB)

**Documentos**:
- PDF (hasta 5MB)
- Para certificados y documentos oficiales

### Navegadores y Versiones Mínimas

| Navegador | Versión Mínima | Recomendada |
|-----------|----------------|-------------|
| Chrome | 90+ | Última versión |
| Firefox | 88+ | Última versión |
| Safari | 14+ | Última versión |
| Edge | 90+ | Última versión |

---

## 📝 Notas de Versión

**Versión Actual**: 1.0.0
**Fecha de Actualización**: [Fecha actual]

### Características Incluidas:
- Sistema completo de registro y autenticación
- Gestión de perfiles de usuario (clientes y técnicos)
- Sistema de solicitudes de servicio
- Calificaciones y reseñas
- Sistema de notificaciones
- Dashboard personalizado por tipo de usuario
- Subida de archivos e imágenes
- Filtros y búsquedas avanzadas

### Próximas Funcionalidades:
- Sistema de pagos integrado
- Chat en tiempo real
- Aplicación móvil nativa
- Geolocalización avanzada
- Integración con calendarios
- Sistema de reportes avanzados

---

*Este manual está sujeto a actualizaciones conforme se desarrollen nuevas funcionalidades en la plataforma CONECTAPRO.*

**© 2024 CONECTAPRO - Todos los derechos reservados**
