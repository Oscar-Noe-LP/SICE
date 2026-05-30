import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  scrollBehavior(to, from, savedPosition) {
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
    // MÓDULO: SERVICIOS ESCOLARES — DASHBOARD
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/servicios-escolares',
      name: 'ServiciosEscolares',
      component: () => import('@/views/ServiciosEscolares/DashboardSE.vue')
    },
    {
      path: '/servicios-escolares/graficas',
      name: 'SEGraficas',
      component: () => import('@/views/ServiciosEscolares/DashboardSE.vue')
    },
    {
      path: '/servicios-escolares/actividad',
      name: 'SEActividad',
      component: () => import('@/views/ServiciosEscolares/DashboardSE.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: SERVICIOS ESCOLARES — ALUMNOS
    // Ruta raíz redirige al primer subtab
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/alumnos',
      redirect: () => {
        const usuario = JSON.parse(localStorage.getItem('usuario') || 'null')
        if (usuario?.rol === 'servicios-escolares') return '/alumnos/gestion'
        return '/alumnos/lista' // fallback otros roles (ajusta si aplica)
      }
    },
    {
      path: '/alumnos/gestion',
      name: 'AlumnosGestion',
      component: () => import('@/views/AlumnosView.vue')
    },
    // ── EXPEDIENTE ACADÉMICO — módulo 2.2 (NELLY) ─────────────────────────
    // Ruta sin noControl: redirige a gestión (el alumno se busca desde ahí)
    {
      path: '/alumnos/expediente',
      name: 'ExpedienteAcademico',
      component: () => import('@/views/ServiciosEscolares/ExpedienteSE.vue')
    },
    // Ruta con noControl: abre el expediente completo del alumno
    {
      path: '/alumnos/expediente/:noControl',
      name: 'ExpedienteDetalle',
      component: () => import('@/views/ServiciosEscolares/ExpedienteSE.vue')
    },
    // Rutas existentes de formulario alumno — se mantienen
    {
      path: '/formulario-alumno',
      name: 'FormularioAlumno',
      component: () => import('@/views/FormularioAlumnoView.vue')
    },
    {
      path: '/formulario-alumno/:id',
      name: 'EditarAlumno',
      component: () => import('@/views/FormularioAlumnoView.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: SERVICIOS ESCOLARES — INSCRIPCIONES
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/inscripciones/nueva',
      name: 'NuevaInscripcion',
      component: () => import('@/views/ServiciosEscolares/InscripcionesSE.vue')
    },
    {
      path: '/inscripciones/cargas',
      name: 'CargasAcademicas',
      component: () => import('@/views/ServiciosEscolares/InscripcionesSE.vue')
    },
    {
      path: '/inscripciones/historial',
      name: 'HistorialInscripciones',
      component: () => import('@/views/Inscripciones_detalladas/HistorialInscripcionesView.vue')
    },
    // Rutas existentes inscripciones detalladas — se mantienen
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
    // Ruta legacy inscripcion simple
    {
      path: '/inscripcion',
      name: 'Inscripcion',
      component: () => import('@/views/InscripcionView.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: SERVICIOS ESCOLARES — CALIFICACIONES
    // Ruta raíz redirige al primer subtab según rol
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/calificaciones',
      redirect: () => {
        const usuario = JSON.parse(localStorage.getItem('usuario') || 'null')
        if (usuario?.rol === 'servicios-escolares') return '/calificaciones/captura'
        return '/calificaciones/general'
      }
    },
    {
      path: '/calificaciones/captura',
      name: 'CalificacionesCaptura',
      component: () => import('@/views/ServiciosEscolares/CalificacionesSE.vue')
    },
    {
      path: '/calificaciones/actas',
      name: 'CalificacionesActas',
      component: () => import('@/views/ServiciosEscolares/CalificacionesSE.vue')
    },
    {
      path: '/calificaciones/especiales',
      name: 'CalificacionesEspeciales',
      component: () => import('@/views/ServiciosEscolares/CalificacionesSE.vue')
    },
    {
      path: '/calificaciones/residencias',
      name: 'CalificacionesResidencias',
      component: () => import('@/views/ServiciosEscolares/CalificacionesSE.vue')
    },
    {
      path: '/calificaciones/analitica',
      name: 'CalificacionesAnalitica',
      component: () => import('@/views/ServiciosEscolares/CalificacionesSE.vue')
    },
    // Rutas existentes calificaciones — se mantienen para otros roles
    {
      path: '/evaluaciones',
      name: 'EvaluacionesGeneral',
      component: () => import('@/views/EvaluacionesView.vue')
    },
    {
      path: '/evaluaciones/:id',
      name: 'Evaluaciones',
      component: () => import('@/views/EvaluacionesView.vue')
    },
    {
      path: '/calificaciones/general',
      name: 'CalificacionesGeneral',
      component: () => import('@/views/CalificacionesView.vue')
    },
    {
      path: '/calificaciones/:id',
      name: 'Calificaciones',
      component: () => import('@/views/CalificacionesView.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: SERVICIOS ESCOLARES — GRUPOS Y HORARIOS
    // Ruta raíz redirige al primer subtab
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/gestion-grupos',
      redirect: '/gestion-grupos/lista'
    },
    {
      path: '/gestion-grupos/lista',
      name: 'GestionGrupos',
      component: () => import('@/views/ServiciosEscolares/GruposSE.vue')
    },
    {
      path: '/gestion-grupos/horarios',
      name: 'HorariosGrupo',
      component: () => import('@/views/ServiciosEscolares/GruposSE.vue')
    },

    // ✅ NUEVA — detalle individual de grupo
    {
      path: '/gestion-grupos/:id',
      name: 'GrupoDetail',
      component: () => import('@/views/ServiciosEscolares/GrupoDetailView.vue')
    },
    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: SERVICIOS ESCOLARES — DOCUMENTOS
    // Ruta raíz redirige al primer subtab
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/documentos',
      redirect: '/documentos/constancias'
    },
    {
      path: '/documentos/constancias',
      name: 'Constancias',
      component: () => import('@/views/ServiciosEscolares/DocumentosSE.vue')
    },
    {
      path: '/documentos/boletas',
      name: 'Boletas',
      component: () => import('@/views/ServiciosEscolares/DocumentosSE.vue')
    },
    {
      path: '/documentos/certificados',
      name: 'Certificados',
      component: () => import('@/views/ServiciosEscolares/DocumentosSE.vue')
    },
    {
      path: '/documentos/actas',
      name: 'DocumentosActas',
      component: () => import('@/views/ServiciosEscolares/DocumentosSE.vue')
    },
    {
      path: '/documentos/cargas',
      name: 'DocumentosCargas',
      component: () => import('@/views/ServiciosEscolares/DocumentosSE.vue')
    },
    {
      path: '/documentos/residencia',
      name: 'ActaResidencia',
      component: () => import('@/views/ServiciosEscolares/DocumentosSE.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: SERVICIOS ESCOLARES — EGRESADOS
    // Ruta raíz redirige al primer subtab
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/egresados',
      redirect: '/egresados/lista'
    },
    {
      path: '/egresados/lista',
      name: 'Egresados',
      component: () => import('@/views/ServiciosEscolares/EgresadosSE.vue')
    },
    {
      path: '/egresados/posibles',
      name: 'PosiblesEgresados',
      component: () => import('@/views/ServiciosEscolares/EgresadosSE.vue')
    },
    {
      path: '/egresados/titulados',
      name: 'Titulados',
      component: () => import('@/views/ServiciosEscolares/EgresadosSE.vue')
    },
    {
      path: '/egresados/registro',
      name: 'RegistroTitulados',
      component: () => import('@/views/ServiciosEscolares/EgresadosSE.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: SERVICIOS ESCOLARES — ASPIRANTES
    // Ruta raíz redirige al primer subtab
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/aspirantes',
      redirect: '/aspirantes/solicitudes'
    },
    {
      path: '/aspirantes/solicitudes',
      name: 'SolicitudesAspirantes',
      component: () => import('@/views/ServiciosEscolares/AspirantesSE.vue')
    },
    {
      path: '/aspirantes/configuracion',
      name: 'ConfiguracionAspirantes',
      component: () => import('@/views/ServiciosEscolares/AspirantesSE.vue')
    },
    {
      path: '/aspirantes/fichas',
      name: 'FichasAspirantes',
      component: () => import('@/views/ServiciosEscolares/AspirantesSE.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: SERVICIOS ESCOLARES — CONFIGURACIÓN
    // Ruta raíz redirige al primer subtab
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/configuracion',
      redirect: '/configuracion/carreras'
    },
    {
      path: '/configuracion/carreras',
      name: 'ConfigCarreras',
      component: () => import('@/views/ServiciosEscolares/ConfiguracionSE.vue')
    },
    {
      path: '/configuracion/especialidades',
      name: 'ConfigEspecialidades',
      component: () => import('@/views/ServiciosEscolares/ConfiguracionSE.vue')
    },
    {
      path: '/configuracion/plan-curricular',
      name: 'ConfigPlanCurricular',
      component: () => import('@/views/ServiciosEscolares/ConfiguracionSE.vue')
    },
    {
      path: '/configuracion/periodos',
      name: 'ConfigPeriodos',
      component: () => import('@/views/ServiciosEscolares/ConfiguracionSE.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: SERVICIOS ESCOLARES — PROCESOS
    // Ruta raíz redirige al primer subtab
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/procesos',
      redirect: '/procesos/cierre'
    },
    {
      path: '/procesos/cierre',
      name: 'CierreSemestre',
      component: () => import('@/views/ServiciosEscolares/ProcesosSE.vue')
    },
    {
      path: '/procesos/especiales',
      name: 'ProcesosEspeciales',
      component: () => import('@/views/ServiciosEscolares/ProcesosSE.vue')
    },

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
    {
      path: '/gestion-academica/carreras',
      name: 'CarrerasList',
      component: () => import('@/views/GestionAcademica/CarrerasView.vue'),
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
    
    {
      path: '/analytics/academica',
      name: 'Analitica',
      component: () => import('@/views/AnaliticaAcademica.vue')
    },


    // ══════════════════════════════════════════════════════════════════════
    // RUTAS LEGACY
    // ══════════════════════════════════════════════════════════════════════
    { path: '/carreras',     redirect: '/gestion-academica/carreras' },
    { path: '/carreras/:id', redirect: to => `/gestion-academica/carreras/${to.params.id}` },
    { path: '/carreras/1',   redirect: '/gestion-academica/carreras/1' },
    { path: '/carreras/2',   redirect: '/gestion-academica/carreras/2' },
    { path: '/carreras/3',   redirect: '/gestion-academica/carreras/3' },
    { path: '/carreras/4',   redirect: '/gestion-academica/carreras/4' },
    { path: '/carreras/5',   redirect: '/gestion-academica/carreras/5' },
    { path: '/carreras/6',   redirect: '/gestion-academica/carreras/6' },

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
    { path: '/roles',         name: 'Roles',        component: () => import('@/views/Seguridad y Usuarios/RolesView.vue') },
    { path: '/permisos',      name: 'Permisos',     component: () => import('@/views/Seguridad y Usuarios/PermisosView.vue') },
    { path: '/usuarios',      name: 'Usuarios',     component: () => import('@/views/Seguridad y Usuarios/UsuariosView.vue') },
    { path: '/bitacora',      name: 'Bitacora',     component: () => import('@/views/Seguridad y Usuarios/BitacoraView.vue') },
    { path: '/nuevo-usuario', name: 'NuevoUsuario', component: () => import('@/views/Seguridad y Usuarios/NuevoUsuarioView.vue') },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: ASIGNACIÓN DOCENTE
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/asignacion-docente',
      name: 'AsignacionDocente',
      component: () => import('@/views/Asignación_Docente_a_Grupos/Asignaciondocenteview.vue')
    },
    {
      path: '/asignacion-docente/carga',
      name: 'CargaDocente',
      component: () => import('@/views/Asignación_Docente_a_Grupos/CargaDocenteView.vue')
    },

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
      path: '/kardex/:no_control',
      name: 'KardexDetalle',
      component: () => import('@/views/Modulo_Kardex/KardexDetalleView.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: ASPIRANTES (TERESA — Módulo 8)
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/aspirantes',
      name: 'Aspirantes',
      component: () => import('@/views/AspirantesView.vue')
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

const RUTAS_PUBLICAS = ['/login']

// /inicio es accesible para cualquier usuario autenticado — siempre
const RUTA_FALLBACK = '/inicio'

// Normaliza el nombre_rol de la BD al identificador interno usado en el frontend.
// BD: 'Administrador' | 'Docente' | 'Escolares'
// Frontend: 'admin'   | 'docente' | 'servicios-escolares'
const normalizarRol = (rol) => {
  if (!rol) return ''
  const mapa = {
    'administrador':       'admin',
    'admin':               'admin',
    'docente':             'docente',
    'docentes':            'docente',
    'escolares':           'servicios-escolares',
    'servicios escolares': 'servicios-escolares',
    'servicios-escolares': 'servicios-escolares',
  }
  return mapa[rol.toLowerCase().trim()] ?? rol.toLowerCase().trim()
}

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
    '/servicios-escolares',
    '/alumnos',
    '/formulario-alumno',
    '/evaluaciones',
    '/calificaciones',
    '/inscripcion',
    '/inscripciones',
    '/gestion-grupos',
    '/gestion-academica',
    '/gestion-academica/carreras',
    '/eventos',
    '/comite',
    '/documentos',
    '/egresados',
    '/aspirantes',
    '/configuracion',
    '/procesos',
    '/aspirantes',                       
    '/analytics',
  ],
}

router.beforeEach((to, from, next) => {
  // 1. Rutas públicas — siempre permitidas
  if (RUTAS_PUBLICAS.includes(to.path)) {
    return next()
  }

  // 2. Sin sesión → login
  const token   = localStorage.getItem('auth_token')
  const usuario = JSON.parse(localStorage.getItem('usuario') || 'null')
  if (!token || !usuario) {
    return next('/login')
  }

  const rol = normalizarRol(usuario.rol ?? '')

  // 3. Admin → acceso total
  if (rol === 'admin') {
    return next()
  }

  // 4. /inicio es el fallback universal para cualquier usuario autenticado.
  //    Permitirlo siempre evita el loop infinito cuando el rol no tiene
  //    rutas configuradas o cuando el guard mismo redirige aquí.
  if (to.path === RUTA_FALLBACK) {
    return next()
  }

  // 5. Rol conocido → verificar permisos
  const permitidas  = PERMISOS_POR_ROL[rol] ?? []
  const tieneAcceso = permitidas.some(ruta => to.path.startsWith(ruta))

  if (tieneAcceso) {
    return next()
  }

  // 6. Sin permiso → redirigir al inicio (nunca producirá loop gracias al paso 4)
  return next(RUTA_FALLBACK)
})

export default router
