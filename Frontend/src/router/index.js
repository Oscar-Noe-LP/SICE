import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  
  // Scroll Behavior configurado para no resetear el scroll automáticamente
  scrollBehavior(to, from, savedPosition) {
    // Retornar false evita que Vue Router maneje el scroll por defecto
    // Esto ayuda a que el sidebar mantenga su posición de scroll
    return false
  },

  routes: [

    // ── Raíz ──────────────────────────────────────────────────────────────
    {
      path: '/',
      redirect: '/inicio'
    },

    // ── Autenticación ─────────────────────────────────────────────────────
    {
      path: '/login',
      name: 'Login',
      component: () => import('@/views/LoginView.vue')
    },

    // ── Dashboard principal ───────────────────────────────────────────────
    {
      path: '/inicio',
      name: 'Inicio',
      component: () => import('@/views/DashboardView.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: SERVICIOS ESCOLARES
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/servicios-escolares',
      name: 'ServiciosEscolares',
      component: () => import('@/views/ServiciosEscolaresView.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: INSCRIPCIONES DETALLADAS (Nelly — Semana 3)
    // ══════════════════════════════════════════════════════════════════════
    { 
        path: '/inscripciones', 
        name: 'InscripcionesPanel', 
        component: () => import('@/views/Inscripciones_detalladas/InscripcionesView.vue') 
    },
    { 
        path: '/inscripciones/gestionar/:id', 
        name: 'GestionInscripcion', 
        component: () => import('@/views/Inscripciones_detalladas/GestionInscripcionView.vue') 
    },
    { 
        path: '/inscripciones/historial', 
        name: 'HistorialInscripciones', 
        component: () => import('@/views/Inscripciones_detalladas/HistorialInscripcionesView.vue') 
    },


    // Alumnos
    { path: '/alumnos', name: 'Alumnos', component: () => import('@/views/AlumnosView.vue') },
    { path: '/formulario-alumno', name: 'FormularioAlumno', component: () => import('@/views/FormularioAlumnoView.vue') },
    { path: '/formulario-alumno/:id', name: 'EditarAlumno', component: () => import('@/views/FormularioAlumnoView.vue') },

    // Evaluaciones y Calificaciones
    { path: '/evaluaciones', name: 'EvaluacionesGeneral', component: () => import('@/views/EvaluacionesView.vue') },
    { path: '/evaluaciones/:id', name: 'Evaluaciones', component: () => import('@/views/EvaluacionesView.vue') },
    { path: '/calificaciones', name: 'CalificacionesGeneral', component: () => import('@/views/CalificacionesView.vue') },
    { path: '/calificaciones/:id', name: 'Calificaciones', component: () => import('@/views/CalificacionesView.vue') },

    // Inscripción y Grupos
    { path: '/inscripcion', name: 'Inscripcion', component: () => import('@/views/InscripcionView.vue') },
    { path: '/gestion-grupos', name: 'GestionGrupos', component: () => import('@/views/GestionGruposView.vue') },


    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: PERSONAS
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/personas',
      name: 'Personas',
      component: () => import('@/views/Personas/PersonasView.vue')
    },
    {
      path: '/personas/nueva',
      name: 'NuevaPersona',
      component: () => import('@/views/Personas/FormularioPersonaView.vue')
    },
    {
      path: '/personas/editar/:id',
      name: 'EditarPersona',
      component: () => import('@/views/Personas/FormularioPersonaView.vue')
    },
    {
      path: '/personas/:id',
      name: 'DetallePersona',
      component: () => import('@/views/Personas/DetallePersonaView.vue')
    },


    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: GESTIÓN ACADÉMICA
    // ══════════════════════════════════════════════════════════════════════
    { 
      path: '/gestion-academica',               
      name: 'GestionAcademica',      
      component: () => import('@/views/GestionAcademica/GestionAcademicaView.vue') 
    },
    // ⭐ LISTA DE CARRERAS (CRUD) - VISTA PRINCIPAL CON DRILL-DOWN
    { 
      path: '/gestion-academica/carreras',      
      name: 'CarrerasList',              
      component: () => import('@/views/GestionAcademica/CarrerasView.vue'),
      props: true
    },
    // ⭐ DETALLE DE CARRERA (grupos, alumnos, estadísticas)
    // CORREGIR: CareerDetail.vue → CareerDetailView.vue
    { 
      path: '/gestion-academica/carreras/:id',      
      name: 'CarreraDetail',              
      component: () => import('@/views/GestionAcademica/CareerDetail.vue'),
      props: true
    },
    { 
      path: '/gestion-academica/planes',        
      name: 'PlanesEstudio',         
      component: () => import('@/views/GestionAcademica/PlanesEstudioView.vue') 
    },
    { 
      path: '/gestion-academica/materias',      
      name: 'Materias',              
      component: () => import('@/views/GestionAcademica/MateriasView.vue') 
    },
    { 
      path: '/gestion-academica/prerrequisitos',
      name: 'Prerrequisitos',        
      component: () => import('@/views/GestionAcademica/PrerrequisitosView.vue') 
    },
    { 
      path: '/gestion-academica/periodos',      
      name: 'Periodos',              
      component: () => import('@/views/GestionAcademica/PeriodosView.vue') 
    },
    { 
      path: '/gestion-academica/edificios-aulas',
      name: 'EdificiosAulas',       
      component: () => import('@/views/GestionAcademica/EdificiosAulasView.vue') 
    },

    {
      path: '/analytics',
      name: 'Analytics',
      component: () => import('@/views/Analiticas/AcademicAnalyticsView.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // RUTAS LEGACY / REDIRECCIONES PARA COMPATIBILIDAD
    // ══════════════════════════════════════════════════════════════════════
    { 
      path: '/carreras',      
      redirect: '/gestion-academica/carreras' 
    },
    { 
      path: '/carreras/:id',      
      redirect: to => `/gestion-academica/carreras/${to.params.id}`
    },
    // Rutas estáticas de ejemplo (redirigen a la nueva ruta dinámica)
    { path: '/carreras/1', redirect: '/gestion-academica/carreras/1' },
    { path: '/carreras/2', redirect: '/gestion-academica/carreras/2' },
    { path: '/carreras/3', redirect: '/gestion-academica/carreras/3' },
    { path: '/carreras/4', redirect: '/gestion-academica/carreras/4' },
    { path: '/carreras/5', redirect: '/gestion-academica/carreras/5' },
    { path: '/carreras/6', redirect: '/gestion-academica/carreras/6' },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: RECURSOS HUMANOS
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/recursos-humanos',
      name: 'RecursosHumanos',
      component: () => import('@/views/Recursos Humanos/RecursosHumanosView.vue')
    },
    {
      path: '/recursos-humanos/empleados',
      name: 'Empleados',
      component: () => import('@/views/Recursos Humanos/EmpleadosView.vue')
    },
    {
      path: '/recursos-humanos/empleados/nuevo',
      name: 'NuevoEmpleado',
      component: () => import('@/views/Recursos Humanos/FormularioEmpleadoView.vue')
    },
    {
      path: '/recursos-humanos/empleados/:id/editar',
      name: 'EditarEmpleado',
      component: () => import('@/views/Recursos Humanos/FormularioEmpleadoView.vue')
    },
    {
      path: '/recursos-humanos/docentes',
      name: 'Docentes',
      component: () => import('@/views/Recursos Humanos/DocentesView.vue')
    },
    {
      path: '/recursos-humanos/adscripciones',
      name: 'Adscripciones',
      component: () => import('@/views/Recursos Humanos/AdscripcionView.vue')
    },
    {
      path: '/recursos-humanos/puestos',
      name: 'Puestos',
      component: () => import('@/views/Recursos Humanos/PuestosView.vue')
    },
  
     {
      path: '/recursos-humanos/departamentos',
      name: 'Departamentos',
      component: () => import('@/views/Recursos Humanos/Departamentosview.vue')
    },

    

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: SEGURIDAD Y USUARIOS
    // ══════════════════════════════════════════════════════════════════════
    { path: '/roles',          name: 'Roles',          component: () => import('@/views/Seguridad y Usuarios/RolesView.vue') },
    { path: '/permisos',       name: 'Permisos',       component: () => import('@/views/Seguridad y Usuarios/PermisosView.vue') },
    { path: '/usuarios',       name: 'Usuarios',       component: () => import('@/views/Seguridad y Usuarios/UsuariosView.vue') },
    { path: '/bitacora',       name: 'Bitacora',       component: () => import('@/views/Seguridad y Usuarios/BitacoraView.vue') },
    { path: '/nuevo-usuario',  name: 'NuevoUsuario',   component: () => import('@/views/Seguridad y Usuarios/NuevoUsuarioView.vue') },


    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: ASIGNACIÓN DOCENTE A GRUPOS
    // ══════════════════════════════════════════════════════════════════════
    { path: '/asignacion-docente',name: 'AsignacionDocente', component: () => import('@/views/Asignación_Docente_a_Grupos/Asignaciondocenteview.vue')},
    { path: '/asignacion-docente/carga', name: 'CargaDocente', component: () => import('@/views/Asignación_Docente_a_Grupos/CargaDocenteView.vue')  },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: EVENTOS
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/eventos',
      name: 'Eventos',
      component: () => import('@/views/Modulo_Eventos/EventosView.vue')
    },
    {
      path: '/eventos/nuevo',
      name: 'NuevoEvento',
      component: () => import('@/views/Modulo_Eventos/FormularioEventoView.vue')
    },
    {
      path: '/eventos/:id/editar',
      name: 'EditarEvento',
      component: () => import('@/views/Modulo_Eventos/FormularioEventoView.vue')
    },
    {
      path: '/eventos/:id/participantes',
      name: 'ParticipantesEvento',
      component: () => import('@/views/Modulo_Eventos/ParticipantesEventoView.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: COMITÉ ACADÉMICO
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/comite',
      name: 'Comite',
      component: () => import('@/views/Modulo_Comite_Academico/ComiteView.vue')
    },
    {
      path: '/comite/solicitudes',
      name: 'SolicitudesComite',
      component: () => import('@/views/Modulo_Comite_Academico/SolicitudesComiteView.vue')
    },
    {
      path: '/comite/solicitudes/nueva',
      name: 'NuevaSolicitud',
      component: () => import('@/views/Modulo_Comite_Academico/NuevaSolicitudView.vue')
    },
    {
      path: '/comite/solicitudes/:id',
      name: 'DetalleSolicitud',
      component: () => import('@/views/Modulo_Comite_Academico/NuevaSolicitudView.vue')
    },
    {
      path: '/comite/sesiones',
      name: 'SesionesComite',
      component: () => import('@/views/Modulo_Comite_Academico/SesionesComiteView.vue')
    },
    {
      path: '/comite/resoluciones',
      name: 'ResolucionesComite',
      component: () => import('@/views/Modulo_Comite_Academico/ResolucionesComiteView.vue')
    },
    {
      path: '/comite/resoluciones/nueva',
      name: 'NuevaResolucion',
      component: () => import('@/views/Modulo_Comite_Academico/ResolucionesComiteView.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: HISTORIAL ACADÉMICO
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/historial-academico',
      name: 'HistorialAcademico',
      component: () => import('@/views/Modulo_HistorialAcademico/AvanceCurricularView.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: KARDEX
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/kardex',
      name: 'Kardex',
      component: () => import('@/views/Modulo_Kardex/KardexView.vue')
    },
    {
      path: '/kardex/:id',
      name: 'KardexDetalle',
      component: () => import('@/views/Modulo_Kardex/KardexDetalleView.vue')
    },

    // ── Ruta 404 ──────────────────────────────────────────────────────────
    {
      path: '/:pathMatch(.*)*',
      name: 'NotFound',
      redirect: '/inicio'
    }
  ]
})

// ══════════════════════════════════════════════════════════════════════
// GUARD DE NAVEGACIÓN POR ROLES
// ══════════════════════════════════════════════════════════════════════

// Rutas que no requieren autenticación
const RUTAS_PUBLICAS = ['/login']

// Rutas permitidas por rol (admin tiene acceso total, no se lista aquí)
const PERMISOS_POR_ROL = {
  'docente': [
    '/inicio',
    '/asignacion-docente/carga',
    '/evaluaciones',
    '/calificaciones',
    '/gestion-grupos',
    '/eventos',
  ],
  'servicios-escolares': [
    '/inicio',
    '/servicios-escolares',
    '/alumnos',
    '/formulario-alumno',
    '/evaluaciones',
    '/calificaciones',
    '/inscripcion',
    '/gestion-grupos',
    '/inscripciones',
    '/gestion-academica',
    '/gestion-academica/carreras',       // Lista de carreras
    '/gestion-academica/carreras/',      // Detalle de carrera (cualquier id)
    '/eventos',
    '/comite',
  ],
}

router.beforeEach((to, from, next) => {
  // 1. Rutas públicas — siempre pasan
  if (RUTAS_PUBLICAS.includes(to.path)) {
    return next()
  }

  // 2. Sin sesión — redirigir al login
  const token   = localStorage.getItem('auth_token')
  const usuario = JSON.parse(localStorage.getItem('usuario') || 'null')
  if (!token || !usuario) {
    return next('/login')
  }

  const rol = usuario.rol ?? ''

  // 3. Admin — acceso total
  if (rol === 'admin') {
    return next()
  }

  const permitidas  = PERMISOS_POR_ROL[rol] ?? []
  const tieneAcceso = permitidas.some(ruta => to.path.startsWith(ruta))

  if (tieneAcceso) {
    return next()
  }

  // 5. Sin permiso — redirigir al inicio
  return next('/inicio')
})

export default router