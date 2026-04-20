import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
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
    // Carpeta: src/views/Inscripciones_detalladas/
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
    // Carpeta: src/views/Personas/
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
    // Carpeta: src/views/GestionAcademica/
    // ══════════════════════════════════════════════════════════════════════
    { path: '/gestion-academica',               name: 'GestionAcademica',      component: () => import('@/views/GestionAcademica/GestionAcademicaView.vue') },
    { path: '/gestion-academica/carreras',      name: 'Carreras',              component: () => import('@/views/GestionAcademica/CarrerasView.vue') },
    { path: '/gestion-academica/planes',        name: 'PlanesEstudio',         component: () => import('@/views/GestionAcademica/PlanesEstudioView.vue') },
    { path: '/gestion-academica/materias',      name: 'Materias',              component: () => import('@/views/GestionAcademica/MateriasView.vue') },
    { path: '/gestion-academica/prerrequisitos',name: 'Prerrequisitos',        component: () => import('@/views/GestionAcademica/PrerrequisitosView.vue') },
    { path: '/gestion-academica/periodos',      name: 'Periodos',              component: () => import('@/views/GestionAcademica/PeriodosView.vue') },
    { path: '/gestion-academica/edificios-aulas',name: 'EdificiosAulas',       component: () => import('@/views/GestionAcademica/EdificiosAulasView.vue') },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: RECURSOS HUMANOS
    // Carpeta: src/views/Recursos Humanos/
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
    // Carpeta: src/views/Seguridad y Usuarios/
    // ══════════════════════════════════════════════════════════════════════
    { path: '/roles',          name: 'Roles',          component: () => import('@/views/Seguridad y Usuarios/RolesView.vue') },
    { path: '/permisos',       name: 'Permisos',       component: () => import('@/views/Seguridad y Usuarios/PermisosView.vue') },
    { path: '/usuarios',       name: 'Usuarios',       component: () => import('@/views/Seguridad y Usuarios/UsuariosView.vue') },
    { path: '/bitacora',       name: 'Bitacora',       component: () => import('@/views/Seguridad y Usuarios/BitacoraView.vue') },
    { path: '/nuevo-usuario',  name: 'NuevoUsuario',   component: () => import('@/views/Seguridad y Usuarios/NuevoUsuarioView.vue') },


    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: ASIGNACIÓN DOCENTE A GRUPOS
    // Carpeta: src/views/Asignación_Docente_a_Grupos/
    // ══════════════════════════════════════════════════════════════════════
    { path: '/asignacion-docente',name: 'AsignacionDocente', component: () => import('@/views/Asignación_Docente_a_Grupos/Asignaciondocenteview.vue')},
    { path: '/asignacion-docente/carga', name: 'CargaDocente', component: () => import('@/views/Asignación_Docente_a_Grupos/CargaDocenteView.vue')  },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: EVENTOS
    // Carpeta: src/views/Modulo_Eventos/
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
    // Carpeta: src/views/Modulo_Comite_Academico/
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
    // Carpeta: src/views/Modulo_HistorialAcademico/
    // ══════════════════════════════════════════════════════════════════════
    {
      path: '/historial-academico',
      name: 'HistorialAcademico',
      component: () => import('@/views/Modulo_HistorialAcademico/AvanceCurricularView.vue')
    },

    // ══════════════════════════════════════════════════════════════════════
    // MÓDULO: KARDEX
    // Carpeta: src/views/Modulo_Kardex/
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

export default router