<template>
  <div class="sistema-layout" :class="{ 'sidebar-collapsed': isCollapsed }" @click="cerrarMenus">

    <!-- ══ ENCABEZADO FIJO ══ -->
    <header class="encabezado-superior">
      <div class="encabezado-izquierda">
        <button
          class="btn-toggle-menu"
          @click.stop="toggleSidebar"
          aria-label="Ocultar o mostrar menú lateral"
          title="Ocultar/Mostrar menú"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-toggle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                  :d="isCollapsed ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7'" />
          </svg>
        </button>
        <img src="/logo-tecnm.png" alt="Logo TecNM" class="logo-encabezado">
        <span class="titulo-sistema">SISTEMA INTEGRAL DE CONTROL ESCOLAR</span>
      </div>

      <div class="encabezado-derecha">

        <div class="grupo-busqueda">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-busqueda" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por número de control..."
            v-model="busquedaGlobal"
            @keydown.escape="busquedaGlobal = ''"
            @click.stop
            aria-label="Búsqueda global"
          >
        </div>

        <!-- Campana -->
        <div class="campana-notificaciones" @click.stop="toggleNotificaciones" aria-label="Notificaciones" title="Notificaciones">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-campana" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <span v-if="notificaciones.length > 0" class="contador-notificaciones">{{ notificaciones.length }}</span>

          <div v-if="mostrarNotificaciones" class="panel-notificaciones" @click.stop>
            <div class="panel-encabezado">
              <h4>Notificaciones</h4>
              <span v-if="notificaciones.length > 0" class="marcar-todo" @click="marcarTodasLeidas">Marcar todo como leído</span>
            </div>
            <div class="lista-notificaciones">
              <div v-if="notificaciones.length > 0" v-for="(notif, index) in notificaciones" :key="index" class="elemento-notificacion">
                <svg xmlns="http://www.w3.org/2000/svg" class="icono-notif" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <div class="contenido-notif">
                  <p><strong>{{ notif.titulo }}</strong></p>
                  <p class="tiempo-notif">{{ notif.subtitulo }} · {{ notif.hora }}</p>
                </div>
              </div>
              <div v-else class="sin-notificaciones">
                <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p class="titulo-vacio">Sin notificaciones</p>
                <p class="subtitulo-vacio">Cuando ocurra algo importante aparecerá aquí</p>
              </div>
            </div>
            <div v-if="notificaciones.length > 0" class="pie-notificaciones">Ver todas las notificaciones</div>
          </div>
        </div>

        <!-- Menú usuario -->
        <div class="menu-usuario" @click.stop="toggleMenuUsuario" aria-label="Menú de usuario">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-usuario" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          <span class="nombre-usuario">{{ nombreUsuarioActual }}</span>
          <span class="flecha-desplegable" :class="{ rotada: mostrarMenuUsuario }">▼</span>

          <div v-if="mostrarMenuUsuario" class="desplegable-usuario" @click.stop>
            <!-- Info del usuario logueado -->
            <div style="padding: 10px 16px 8px; border-bottom: 1px solid #E5E7EB;">
              <p style="margin:0; font-size:0.85rem; font-weight:600; color:#1A1A1A;">{{ nombreUsuarioActual }}</p>
              <p style="margin:2px 0 0; font-size:0.78rem; color:#6B7280;">{{ etiquetaRol }}</p>
            </div>
            <div class="separador-desplegable"></div>
            <!-- Cerrar sesión -->
            <div class="elemento-desplegable elemento-cerrar-sesion" @click="cerrarSesion">
              <svg xmlns="http://www.w3.org/2000/svg" class="icono-rol" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Cerrar sesión
            </div>
          </div>
        </div>

      </div>
    </header>

    <!-- Franja de hover para expandir sidebar colapsado -->
    <div
      v-if="!isFixed && isCollapsed"
      class="franja-hover-sidebar"
      @mouseenter="onSidebarEnter"
    ></div>

    <!-- ══ MENÚ LATERAL ══ -->
    <aside
      class="menu-lateral"
      :class="{ 'colapsando': colapsandoSuave }"
      @click.stop
      @mouseleave="onSidebarLeave"
    >
      <nav class="navegacion" role="navigation" aria-label="Menú principal">

        <!-- Inicio -->
        <router-link to="/inicio" class="elemento-menu" active-class="activo">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span class="etiqueta-menu">Inicio</span>
        </router-link>

        <!-- ── Servicios Escolares ── -->
        <template v-if="puedeVer.serviciosEscolares">
        <div class="elemento-menu elemento-padre" @click.stop="toggleServicios" :aria-expanded="isServiciosOpen" aria-label="Servicios Escolares">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <span class="etiqueta-menu">Servicios Escolares</span>
          <span class="flecha-submenu" :class="{ abierto: isServiciosOpen }">›</span>
        </div>
        <div v-if="isServiciosOpen" class="submenu">
          <router-link v-if="puedeVerItem('/servicios-escolares')" to="/servicios-escolares" class="elemento-menu elemento-submenu" active-class="activo">Principal</router-link>
          <router-link v-if="puedeVerItem('/alumnos')"             to="/alumnos"             class="elemento-menu elemento-submenu" active-class="activo">Alumnos</router-link>
          <router-link v-if="puedeVerItem('/evaluaciones')"        to="/evaluaciones"        class="elemento-menu elemento-submenu" active-class="activo">Evaluaciones</router-link>
          <router-link v-if="puedeVerItem('/calificaciones')"      to="/calificaciones"      class="elemento-menu elemento-submenu" active-class="activo">Calificaciones</router-link>
          <router-link v-if="puedeVerItem('/inscripcion')"         to="/inscripcion"         class="elemento-menu elemento-submenu" active-class="activo">Inscripción</router-link>
          <router-link v-if="puedeVerItem('/gestion-grupos')"      to="/gestion-grupos"      class="elemento-menu elemento-submenu" active-class="activo">Grupos y Horarios</router-link>
          <!-- Inscripciones Detalladas: sub-submenú colapsable -->
          <template v-if="puedeVerItem('/inscripciones')">
          <div class="elemento-menu elemento-submenu elemento-padre" @click.stop="toggleInscripcionesDetalladas">
            <span style="flex:1">Inscripciones Detalladas</span>
            <span class="flecha-submenu" :class="{ abierto: isInscripcionesDetalladasOpen }">›</span>
          </div>
          <div v-if="isInscripcionesDetalladasOpen" class="submenu">
            <router-link to="/inscripciones"           class="elemento-menu elemento-submenu elemento-submenu-anidado" active-class="activo">Panel General</router-link>
            <router-link to="/inscripciones/historial" class="elemento-menu elemento-submenu elemento-submenu-anidado" active-class="activo">Historial</router-link>
          </div>
          </template>
        </div>
        </template><!-- /serviciosEscolares -->

        <!-- ── Gestión Académica ── -->
        <template v-if="puedeVer.gestionAcademica">
        <div class="elemento-menu elemento-padre" @click.stop="toggleGestionAcademica" :aria-expanded="isGestionAcademicaOpen" aria-label="Gestión Académica">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
          </svg>
          <span class="etiqueta-menu">Gestión Académica</span>
          <span class="flecha-submenu" :class="{ abierto: isGestionAcademicaOpen }">›</span>
        </div>
        <div v-if="isGestionAcademicaOpen" class="submenu">
          <router-link to="/gestion-academica"                 class="elemento-menu elemento-submenu" active-class="activo">Panel Principal</router-link>
          <router-link to="/gestion-academica/carreras"        class="elemento-menu elemento-submenu" active-class="activo">Carreras</router-link>
          <router-link to="/gestion-academica/planes"          class="elemento-menu elemento-submenu" active-class="activo">Planes de Estudio</router-link>
          <router-link to="/gestion-academica/materias"        class="elemento-menu elemento-submenu" active-class="activo">Materias</router-link>
          <router-link to="/gestion-academica/prerrequisitos"  class="elemento-menu elemento-submenu" active-class="activo">Prerrequisitos</router-link>
          <router-link to="/gestion-academica/periodos"        class="elemento-menu elemento-submenu" active-class="activo">Periodos Académicos</router-link>
          <router-link to="/gestion-academica/edificios-aulas" class="elemento-menu elemento-submenu" active-class="activo">Edificios y Aulas</router-link>
        </div>
        </template><!-- /gestionAcademica -->

        <!-- ── Eventos ── -->
        <template v-if="puedeVer.eventos">
        <div class="elemento-menu elemento-padre" @click.stop="toggleEventos" :aria-expanded="isEventosOpen" aria-label="Eventos">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span class="etiqueta-menu">Eventos</span>
          <span class="flecha-submenu" :class="{ abierto: isEventosOpen }">›</span>
        </div>
        <div v-if="isEventosOpen" class="submenu">
          <router-link to="/eventos"       class="elemento-menu elemento-submenu" active-class="activo">Lista de Eventos</router-link>
          <router-link to="/eventos/nuevo" class="elemento-menu elemento-submenu" active-class="activo">Nuevo Evento</router-link>
        </div>
        </template><!-- /eventos -->

        <!-- ── Comité Académico ── -->
        <template v-if="puedeVer.comite">
        <div class="elemento-menu elemento-padre" @click.stop="toggleComite" :aria-expanded="isComiteOpen" aria-label="Comité Académico">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          <span class="etiqueta-menu">Comité Académico</span>
          <span class="flecha-submenu" :class="{ abierto: isComiteOpen }">›</span>
        </div>
        <div v-if="isComiteOpen" class="submenu">
          <router-link to="/comite"                   class="elemento-menu elemento-submenu" active-class="activo">Panel Principal</router-link>
          <router-link to="/comite/solicitudes"       class="elemento-menu elemento-submenu" active-class="activo">Solicitudes</router-link>
          <router-link to="/comite/solicitudes/nueva" class="elemento-menu elemento-submenu" active-class="activo">Nueva Solicitud</router-link>
          <router-link to="/comite/sesiones"          class="elemento-menu elemento-submenu" active-class="activo">Sesiones</router-link>
          <router-link to="/comite/resoluciones"      class="elemento-menu elemento-submenu" active-class="activo">Resoluciones</router-link>
        </div>
        </template><!-- /comite -->

        <!-- ══════════════════════════════════════
             ADMINISTRACIÓN (solo admin)
        ══════════════════════════════════════ -->
        <template v-if="rolActual === 'admin'">

          <div class="separador-menu"><span>Administración</span></div>

          <!-- ── Seguridad y Usuarios ── -->
          <div class="elemento-menu elemento-padre" @click.stop="toggleSeguridad" :aria-expanded="isSeguridadOpen" aria-label="Seguridad y Usuarios">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span class="etiqueta-menu">Seguridad y Usuarios</span>
            <span class="flecha-submenu" :class="{ abierto: isSeguridadOpen }">›</span>
          </div>
          <div v-if="isSeguridadOpen" class="submenu">
            <router-link to="/roles"         class="elemento-menu elemento-submenu" active-class="activo">Roles</router-link>
            <router-link to="/permisos"      class="elemento-menu elemento-submenu" active-class="activo">Permisos</router-link>
            <router-link to="/usuarios"      class="elemento-menu elemento-submenu" active-class="activo">Usuarios</router-link>
            <router-link to="/bitacora"      class="elemento-menu elemento-submenu" active-class="activo">Bitácora</router-link>
            <router-link to="/nuevo-usuario" class="elemento-menu elemento-submenu" active-class="activo">Nuevo Usuario</router-link>
          </div>

          <!-- ── Recursos Humanos ── -->
          <div class="elemento-menu elemento-padre" @click.stop="toggleRecursosHumanos" :aria-expanded="isRecursosHumanosOpen" aria-label="Recursos Humanos">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span class="etiqueta-menu">Recursos Humanos</span>
            <span class="flecha-submenu" :class="{ abierto: isRecursosHumanosOpen }">›</span>
          </div>
          <div v-if="isRecursosHumanosOpen" class="submenu">
            <router-link to="/recursos-humanos"               class="elemento-menu elemento-submenu" active-class="activo">Principal</router-link>
            <router-link to="/recursos-humanos/empleados"     class="elemento-menu elemento-submenu" active-class="activo">Empleados</router-link>
            <router-link to="/recursos-humanos/docentes"      class="elemento-menu elemento-submenu" active-class="activo">Docentes</router-link>
            <router-link to="/recursos-humanos/adscripciones" class="elemento-menu elemento-submenu" active-class="activo">Adscripciones</router-link>
            <router-link to="/recursos-humanos/puestos"       class="elemento-menu elemento-submenu" active-class="activo">Puestos</router-link>
            <router-link to="/recursos-humanos/departamentos" class="elemento-menu elemento-submenu" active-class="activo">Departamentos</router-link>
          </div>

          <!-- ── Personas ── -->
          <div class="elemento-menu elemento-padre" @click.stop="togglePersonas" :aria-expanded="isPersonasOpen" aria-label="Personas">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="etiqueta-menu">Personas</span>
            <span class="flecha-submenu" :class="{ abierto: isPersonasOpen }">›</span>
          </div>
          <div v-if="isPersonasOpen" class="submenu">
            <router-link to="/personas"       class="elemento-menu elemento-submenu" active-class="activo">Catálogo</router-link>
            <router-link to="/personas/nueva" class="elemento-menu elemento-submenu" active-class="activo">Nueva Persona</router-link>
          </div>

        </template><!-- /admin -->

        <!-- ── Asignación Docente a Grupos ── -->
        <!-- Visible para admin y docente -->
        <template v-if="puedeVer.asignacionDocente">
          <div class="elemento-menu elemento-padre" @click.stop="toggleAsignacionDocente" :aria-expanded="isAsignacionDocenteOpen" aria-label="Asignación Docente a Grupos">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="etiqueta-menu">Asignación Docente</span>
            <span class="flecha-submenu" :class="{ abierto: isAsignacionDocenteOpen }">›</span>
          </div>
          <div v-if="isAsignacionDocenteOpen" class="submenu">
            <router-link v-if="rolActual === 'admin'" to="/asignacion-docente" class="elemento-menu elemento-submenu" active-class="activo">Asignación de Grupos</router-link>
            <router-link to="/asignacion-docente/carga" class="elemento-menu elemento-submenu" active-class="activo">Carga Académica</router-link>
          </div>
        </template><!-- /asignacionDocente -->

        <!-- Kardex e Historial solo admin -->
        <template v-if="rolActual === 'admin'">

          <!-- ── Kardex ── -->
          <div class="elemento-menu elemento-padre" @click.stop="toggleKardex" :aria-expanded="isKardexOpen" aria-label="Kardex">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="etiqueta-menu">Kardex</span>
            <span class="flecha-submenu" :class="{ abierto: isKardexOpen }">›</span>
          </div>
          <div v-if="isKardexOpen" class="submenu">
            <router-link to="/kardex" class="elemento-menu elemento-submenu" active-class="activo">Consulta de Kardex</router-link>
          </div>

          <!-- ── Historial Académico ── -->
          <div class="elemento-menu elemento-padre" @click.stop="toggleHistorialAcademico" :aria-expanded="isHistorialAcademicoOpen" aria-label="Historial Académico">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span class="etiqueta-menu">Historial Académico</span>
            <span class="flecha-submenu" :class="{ abierto: isHistorialAcademicoOpen }">›</span>
          </div>
          <div v-if="isHistorialAcademicoOpen" class="submenu">
            <router-link to="/historial-academico" class="elemento-menu elemento-submenu" active-class="activo">Avance Curricular</router-link>
          </div>

        </template><!-- /admin kardex historial -->

      </nav>
    </aside>

    <!-- ══ CONTENIDO PRINCIPAL ══ -->
    <main class="area-contenido" :class="{ 'contenido-retrasado': contenidoMoviendo }">
      <slot :key="$route.fullPath" :busquedaGlobal="busquedaGlobal" />
    </main>

    <!-- ══ BOTÓN REGRESAR FLOTANTE ══ -->
    <Transition name="fab-back">
      <button
        v-if="mostrarBotonRegresar"
        class="fab-regresar"
        @click.stop="regresarPagina"
        aria-label="Regresar a la página anterior"
        title="Regresar"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="fab-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M15 19l-7-7 7-7" />
        </svg>
      </button>
    </Transition>

  </div>
</template>

<script setup>
import { useKeyboardShortcuts } from '@/composables/useKeyboardShortcuts'
useKeyboardShortcuts()

import { ref, computed, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route  = useRoute()

// ── Estado global ─────────────────────────────────────────────────────
const busquedaGlobal = ref('')
const isCollapsed    = ref(false)

// ── Fijado del sidebar ────────────────────────────────────────────────
// isFixed: true = sidebar siempre visible (fijado)
//          false = sidebar se colapsa al navegar y se muestra solo con hover
const isFixed       = ref(false)
const isHovered     = ref(false)
const colapsandoSuave = ref(false)
const contenidoMoviendo = ref(false)

// El sidebar está visible si está fijado O si el usuario está encima con el cursor
const sidebarVisible = computed(() => isFixed.value || isHovered.value)

// ── Estados de submenús ───────────────────────────────────────────────
const isServiciosOpen          = ref(true)
const isGestionAcademicaOpen   = ref(false)
const isEventosOpen            = ref(false)
const isComiteOpen             = ref(false)
const isSeguridadOpen          = ref(false)
const isRecursosHumanosOpen    = ref(false)
const isPersonasOpen           = ref(false)
const isAsignacionDocenteOpen  = ref(false)
const isKardexOpen             = ref(false)
const isHistorialAcademicoOpen      = ref(false)
const isInscripcionesDetalladasOpen = ref(false)

// ── Encabezado ────────────────────────────────────────────────────────
const mostrarMenuUsuario    = ref(false)
const mostrarNotificaciones = ref(false)
const notificaciones        = ref([])

// ── Datos del usuario logueado (leídos desde localStorage) ───────────
const usuarioLogueado = ref(JSON.parse(localStorage.getItem('usuario') || 'null'))
const rolActual       = computed(() => usuarioLogueado.value?.rol ?? 'servicios-escolares')

// ── Persistencia en localStorage ─────────────────────────────────────
onMounted(() => {
  const load = (key, refVar, parse = true) => {
    const val = localStorage.getItem(key)
    if (val !== null) refVar.value = parse ? JSON.parse(val) : val
  }
  load('isServiciosOpen',               isServiciosOpen)
  load('isGestionAcademicaOpen',        isGestionAcademicaOpen)
  load('isEventosOpen',                 isEventosOpen)
  load('isComiteOpen',                  isComiteOpen)
  load('isSeguridadOpen',               isSeguridadOpen)
  load('isRecursosHumanosOpen',         isRecursosHumanosOpen)
  load('isPersonasOpen',                isPersonasOpen)
  load('isAsignacionDocenteOpen',       isAsignacionDocenteOpen)
  load('isKardexOpen',                  isKardexOpen)
  load('isHistorialAcademicoOpen',      isHistorialAcademicoOpen)
  load('isInscripcionesDetalladasOpen', isInscripcionesDetalladasOpen)
  load('isFixed',                       isFixed)

  // Si estaba fijado, mostrar sidebar inmediatamente
  if (isFixed.value) {
    isCollapsed.value = false
  } else {
    // Si no estaba fijado, colapsar al cargar
    isCollapsed.value = true
  }
})

watch(
  [
    isServiciosOpen, isGestionAcademicaOpen, isEventosOpen,
    isComiteOpen, isSeguridadOpen, isRecursosHumanosOpen,
    isPersonasOpen, isAsignacionDocenteOpen, isKardexOpen,
    isHistorialAcademicoOpen, isInscripcionesDetalladasOpen, isFixed
  ],
  () => {
    localStorage.setItem('isServiciosOpen',               JSON.stringify(isServiciosOpen.value))
    localStorage.setItem('isGestionAcademicaOpen',        JSON.stringify(isGestionAcademicaOpen.value))
    localStorage.setItem('isEventosOpen',                 JSON.stringify(isEventosOpen.value))
    localStorage.setItem('isComiteOpen',                  JSON.stringify(isComiteOpen.value))
    localStorage.setItem('isSeguridadOpen',               JSON.stringify(isSeguridadOpen.value))
    localStorage.setItem('isRecursosHumanosOpen',         JSON.stringify(isRecursosHumanosOpen.value))
    localStorage.setItem('isPersonasOpen',                JSON.stringify(isPersonasOpen.value))
    localStorage.setItem('isAsignacionDocenteOpen',       JSON.stringify(isAsignacionDocenteOpen.value))
    localStorage.setItem('isKardexOpen',                  JSON.stringify(isKardexOpen.value))
    localStorage.setItem('isHistorialAcademicoOpen',      JSON.stringify(isHistorialAcademicoOpen.value))
    localStorage.setItem('isInscripcionesDetalladasOpen', JSON.stringify(isInscripcionesDetalladasOpen.value))
    localStorage.setItem('isFixed',                       JSON.stringify(isFixed.value))
  },
  { deep: true }
)

// ── Computed ──────────────────────────────────────────────────────────
const ETIQUETAS_ROL = {
  'admin':               'Administrador',
  'docente':             'Docente',
  'servicios-escolares': 'Servicios Escolares',
}
const etiquetaRol       = computed(() => ETIQUETAS_ROL[rolActual.value] ?? rolActual.value)
const nombreUsuarioActual = computed(() =>
  usuarioLogueado.value?.nombre_usuario ?? etiquetaRol.value
)

// ── Visibilidad de módulos en el sidebar por rol ──────────────────────
// admin ve todo (no se lista aquí). Para otros roles se define qué módulos
// aparecen en el menú lateral, alineado con PERMISOS_POR_ROL del router.
const MODULOS_POR_ROL = {
  'docente': ['servicios-escolares', 'eventos', 'asignacion-docente'],
  'servicios-escolares': ['servicios-escolares', 'gestion-academica', 'eventos', 'comite'],
}

const puedeVer = computed(() => {
  const rol = rolActual.value
  if (rol === 'admin') {
    // Admin ve todos los módulos
    return {
      serviciosEscolares:   true,
      gestionAcademica:     true,
      eventos:              true,
      comite:               true,
      asignacionDocente:    true,
    }
  }
  const modulos = MODULOS_POR_ROL[rol] ?? []
  return {
    serviciosEscolares:   modulos.includes('servicios-escolares'),
    gestionAcademica:     modulos.includes('gestion-academica'),
    eventos:              modulos.includes('eventos'),
    comite:               modulos.includes('comite'),
    asignacionDocente:    modulos.includes('asignacion-docente'),
  }
})
// Alias para la compatibilidad con el template existente (v-if="rolActual === 'admin'")


// ── Visibilidad de ítems dentro de submenús por rol ──────────────────
// Devuelve true si la ruta está permitida para el rol actual.
// Se usa para ocultar items individuales dentro de módulos compartidos.
const ITEMS_POR_ROL = {
  'docente': [
    '/evaluaciones', '/calificaciones', '/gestion-grupos',
    '/asignacion-docente/carga', '/eventos',
  ],
  'servicios-escolares': null, // null = ve todo
}

const puedeVerItem = computed(() => (ruta) => {
  const rol = rolActual.value
  if (rol === 'admin') return true
  const items = ITEMS_POR_ROL[rol]
  if (items === null || items === undefined) return true
  // startsWith para cubrir rutas con parámetros
  return items.some(r => ruta.startsWith(r))
})
// El botón de hamburguesa ahora alterna entre fijado y no fijado
const toggleSidebar = () => {
  isFixed.value = !isFixed.value
  if (isFixed.value) {
    // Al fijar: expandir inmediatamente
    isCollapsed.value = false
  } else {
    // Al desfijar: colapsar si el cursor no está encima
    if (!isHovered.value) {
      isCollapsed.value = true
    }
  }
}

// ── Hover del sidebar ─────────────────────────────────────────────────
// Solo actúa cuando el sidebar NO está fijado
const onSidebarEnter = () => {
  if (!isFixed.value) {
    isHovered.value  = true
    isCollapsed.value = false
  }
}

const onSidebarLeave = () => {
  if (!isFixed.value) {
    isHovered.value  = false
    isCollapsed.value = true
  }
}

// ── Auto-colapso al navegar ───────────────────────────────────────────
// Cuando el usuario hace clic en un link y cambia de ruta,
// si el sidebar NO está fijado se colapsa automáticamente
watch(
  () => router.currentRoute.value.fullPath,
  () => {
    if (!isFixed.value) {
      isHovered.value         = false
      colapsandoSuave.value   = true
      contenidoMoviendo.value = true
      setTimeout(() => {
        isCollapsed.value       = true
        colapsandoSuave.value   = false
        contenidoMoviendo.value = false
      }, 280)
    }
    cerrarMenus()
  }
)


// ── Toggles de submenús ───────────────────────────────────────────────
const toggleServicios           = () => { isServiciosOpen.value          = !isServiciosOpen.value }
const toggleGestionAcademica    = () => { isGestionAcademicaOpen.value   = !isGestionAcademicaOpen.value }
const toggleEventos             = () => { isEventosOpen.value            = !isEventosOpen.value }
const toggleComite              = () => { isComiteOpen.value             = !isComiteOpen.value }
const toggleSeguridad           = () => { isSeguridadOpen.value          = !isSeguridadOpen.value }
const toggleRecursosHumanos     = () => { isRecursosHumanosOpen.value    = !isRecursosHumanosOpen.value }
const togglePersonas            = () => { isPersonasOpen.value           = !isPersonasOpen.value }
const toggleAsignacionDocente   = () => { isAsignacionDocenteOpen.value  = !isAsignacionDocenteOpen.value }
const toggleKardex              = () => { isKardexOpen.value             = !isKardexOpen.value }
const toggleHistorialAcademico  = () => { isHistorialAcademicoOpen.value = !isHistorialAcademicoOpen.value }
const toggleInscripcionesDetalladas = () => { isInscripcionesDetalladasOpen.value = !isInscripcionesDetalladasOpen.value }

const toggleMenuUsuario = () => {
  mostrarMenuUsuario.value    = !mostrarMenuUsuario.value
  mostrarNotificaciones.value = false
}
const toggleNotificaciones = () => {
  mostrarNotificaciones.value = !mostrarNotificaciones.value
  mostrarMenuUsuario.value    = false
}
const cerrarMenus = () => {
  mostrarMenuUsuario.value    = false
  mostrarNotificaciones.value = false
}
const marcarTodasLeidas = () => {
  notificaciones.value        = []
  mostrarNotificaciones.value = false
}
// ── Cerrar sesión ─────────────────────────────────────────────────────
const cerrarSesion = async () => {
  try {
    const token = localStorage.getItem('auth_token')
    if (token) {
      await fetch(`${import.meta.env.VITE_API_URL}/api/logout`, {
        method: 'POST',
        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
      })
    }
  } catch (_) { /* silencioso */ }
  localStorage.removeItem('auth_token')
  localStorage.removeItem('usuario')
  router.push('/login')
}

// ══════════════════════════════════════════════════════════════════════
// ── BOTÓN REGRESAR FLOTANTE ───────────────────────────────────────────
// ══════════════════════════════════════════════════════════════════════

// Rutas principales donde el botón NO debe mostrarse.
// Son las "pantallas raíz" de cada módulo; en subrutas sí aparece.
const RUTAS_PRINCIPALES = new Set([
  '/inicio',
  '/dashboard',
  '/servicios-escolares',
  '/alumnos',
  '/evaluaciones',
  '/calificaciones',
  '/inscripcion',
  '/inscripciones',
  '/gestion-grupos',
  '/gestion-academica',
  '/eventos',
  '/comite',
  '/kardex',
  '/historial-academico',
  '/asignacion-docente',
  '/roles',
  '/permisos',
  '/usuarios',
  '/bitacora',
  '/nuevo-usuario',
  '/recursos-humanos',
  '/personas',
])

// El botón aparece cuando:
//   1. La ruta actual NO es una ruta principal exacta.
//   2. El historial del navegador tiene al menos una página atrás.
const mostrarBotonRegresar = computed(() => {
  const path = route.path.replace(/\/$/, '') // quitar trailing slash
  const esRutaPrincipal = RUTAS_PRINCIPALES.has(path)
  const hayHistorial    = window.history.length > 1
  return !esRutaPrincipal && hayHistorial
})

const regresarPagina = () => {
  router.back()
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.sistema-layout {
  --fondo-general:     #F5F5F5;
  --borde:             #E5E7EB;
  --texto-principal:   #1A1A1A;
  --texto-secundario:  #6B7280;
  --texto-placeholder: #9CA3AF;
  --azul-principal:    #1B396A;
  --azul-hover:        #1D4ED8;
  --azul-suave:        #DBEAFE;

  font-family: 'Montserrat', sans-serif;
  display: flex;
  min-height: 100vh;
  background: var(--fondo-general);
  position: relative;
}

/* ══ Encabezado ══ */
.encabezado-superior {
  background: #1B396A;
  padding: 0 2rem;
  position: fixed;
  top: 0; left: 0; right: 0;
  height: 74px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  z-index: 1000;
  box-shadow: 0 4px 20px rgba(0,0,0,0.18);
}
.encabezado-izquierda { display: flex; align-items: center; gap: 0.9rem; }
.logo-encabezado { height: 52px; filter: drop-shadow(0 0 8px rgba(255,255,255,0.9)); }
.titulo-sistema { font-size: 1.05rem; font-weight: 700; color: #FFFFFF; letter-spacing: 0.01em; white-space: nowrap; }

.btn-toggle-menu {
  background: none; border: none; color: white;
  width: 38px; height: 38px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; border-radius: 6px; transition: background 0.2s; flex-shrink: 0;
}
.btn-toggle-menu:hover { background: rgba(255,255,255,0.15); }
.icono-toggle { width: 22px; height: 22px; stroke: white; }

.encabezado-derecha { display: flex; align-items: center; gap: 2rem; height: 100%; }

.grupo-busqueda { position: relative; width: 300px; }
.grupo-busqueda input {
  width: 100%; padding: 9px 16px 9px 44px;
  border: none; border-radius: 8px;
  background: #FFFFFF; color: #1A1A1A;
  font-size: 0.92rem; font-family: 'Montserrat', sans-serif;
  box-shadow: 0 2px 8px rgba(0,0,0,0.12);
  outline: none; transition: box-shadow 0.2s; box-sizing: border-box;
}
.grupo-busqueda input::placeholder { color: #9CA3AF; }
.grupo-busqueda input:focus { box-shadow: 0 0 0 3px #DBEAFE; }
.icono-busqueda {
  position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
  width: 18px; height: 18px; stroke: #6B7280; pointer-events: none;
}

.campana-notificaciones {
  position: relative; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; border-radius: 8px; transition: background 0.2s;
}
.campana-notificaciones:hover { background: rgba(255,255,255,0.15); }
.icono-campana { width: 24px; height: 24px; stroke: white; }
.contador-notificaciones {
  position: absolute; top: -4px; right: -4px;
  background: #EF4444; color: white;
  font-size: 0.7rem; font-weight: 700;
  min-width: 18px; height: 18px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; padding: 0 4px;
}
.panel-notificaciones {
  position: absolute; top: 56px; right: 0;
  width: 360px; background: #FFFFFF;
  border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.18);
  border: 1px solid #E5E7EB; overflow: hidden; z-index: 1100;
}
.panel-encabezado {
  padding: 14px 18px; background: #F8FAFC;
  border-bottom: 1px solid #E5E7EB;
  display: flex; justify-content: space-between; align-items: center;
}
.panel-encabezado h4 { margin: 0; font-size: 1rem; font-weight: 600; color: #1A1A1A; }
.marcar-todo { font-size: 0.82rem; color: #1B396A; cursor: pointer; font-weight: 500; }
.lista-notificaciones { max-height: 320px; overflow-y: auto; }
.elemento-notificacion {
  display: flex; gap: 12px; padding: 14px 18px;
  border-bottom: 1px solid #F1F5F9; transition: background 0.2s;
}
.elemento-notificacion:hover { background: #F8FAFC; }
.icono-notif { width: 22px; height: 22px; stroke: #1B396A; flex-shrink: 0; }
.contenido-notif p { margin: 0; line-height: 1.4; color: #1A1A1A; }
.tiempo-notif { font-size: 0.8rem; color: #6B7280; margin-top: 3px !important; }
.sin-notificaciones { padding: 48px 20px; text-align: center; color: #6B7280; }
.icono-vacio { width: 52px; height: 52px; stroke: #9CA3AF; margin-bottom: 12px; }
.titulo-vacio { font-size: 1rem; font-weight: 600; margin: 0 0 4px; }
.subtitulo-vacio { font-size: 0.85rem; margin: 0; }
.pie-notificaciones {
  padding: 12px 18px; text-align: center;
  color: #1B396A; font-weight: 600; font-size: 0.9rem;
  border-top: 1px solid #E5E7EB; cursor: pointer;
}

.menu-usuario {
  display: flex; align-items: center; gap: 8px;
  color: white; font-weight: 500; cursor: pointer;
  position: relative; padding: 6px 10px;
  border-radius: 8px; transition: background 0.2s;
}
.menu-usuario:hover { background: rgba(255,255,255,0.15); }
.icono-usuario { width: 24px; height: 24px; stroke: white; }
.nombre-usuario { font-size: 0.95rem; }
.flecha-desplegable { font-size: 0.7rem; opacity: 0.8; transition: transform 0.25s; }
.flecha-desplegable.rotada { transform: rotate(180deg); }
.desplegable-usuario {
  position: absolute; top: calc(100% + 8px); right: 0;
  background: #FFFFFF; border-radius: 10px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  padding: 6px 0; min-width: 200px; z-index: 1100;
  border: 1px solid #E5E7EB;
}
.elemento-desplegable {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 16px; cursor: pointer;
  color: #1A1A1A; font-size: 0.93rem; transition: background 0.15s;
}
.elemento-desplegable:hover { background: #F5F5F5; }
.icono-rol { width: 18px; height: 18px; stroke: #6B7280; flex-shrink: 0; }
.separador-desplegable { height: 1px; background: #E5E7EB; margin: 4px 0; }
.elemento-cerrar-sesion { color: #DC2626; }
.elemento-cerrar-sesion .icono-rol { stroke: #DC2626; }

/* ══ Menú lateral ══ */
.menu-lateral {
  width: 260px;
  background: #D6D6D6;
  position: fixed;
  top: 74px; bottom: 0; left: 0;
  overflow-y: auto; overflow-x: hidden;
  padding-top: 0.5rem;
  transition: width 0.35s ease, opacity 0.3s ease;
  z-index: 900;
  box-shadow: 2px 0 8px rgba(0,0,0,0.07);
}

.menu-lateral.colapsando {
  opacity: 0;
  pointer-events: none;
  transition: width 0.35s ease, opacity 0.28s ease;
}
.sistema-layout.sidebar-collapsed .menu-lateral { width: 0; padding-top: 0; }

.navegacion { width: 260px; display: flex; flex-direction: column; }

.elemento-menu {
  display: flex; align-items: center; gap: 11px;
  padding: 12px 20px;
  color: #1A1A1A; text-decoration: none;
  font-size: 0.93rem; font-weight: 500; cursor: pointer;
  transition: background 0.18s, color 0.18s;
  white-space: nowrap; border-left: 3px solid transparent;
}
.elemento-menu:hover { background: #E5E7EB; color: #1B396A; }
.elemento-menu.activo {
  background: #FFFFFF; color: #1B396A;
  font-weight: 600; border-left-color: #1B396A;
}
.elemento-menu.activo .icono-menu { stroke: #1B396A; }

.icono-menu { width: 20px; height: 20px; stroke: #6B7280; flex-shrink: 0; transition: stroke 0.18s; }
.etiqueta-menu { flex: 1; }

.elemento-padre { user-select: none; }
.flecha-submenu { margin-left: auto; font-size: 1.1rem; color: #6B7280; transition: transform 0.25s; }
.flecha-submenu.abierto { transform: rotate(90deg); }

.submenu { background: rgba(0,0,0,0.05); }
.elemento-submenu { padding-left: 44px; font-size: 0.88rem; font-weight: 400; }
.elemento-submenu-anidado { padding-left: 60px; font-size: 0.85rem; }

.separador-menu { padding: 10px 20px 4px; margin-top: 6px; border-top: 1px solid rgba(0,0,0,0.1); }
.separador-menu span {
  font-size: 0.72rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.08em; color: #6B7280;
}

/* ══ Barra de scroll — Navy Slim Minimal ══ */
* {
  scrollbar-width: thin;
  scrollbar-color: #1a3a5f #f1f5f9;
}

*::-webkit-scrollbar {
  width: 0px;
  height: 8px;
}

*::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

*::-webkit-scrollbar-thumb {
  background: #1a3a5f;
  border-radius: 4px;
  transition: background 200ms ease;
}

*::-webkit-scrollbar-thumb:hover {
  background: #193d94;
}

*::-webkit-scrollbar-thumb:active {
  background: #2c5282;
}

/* ══ Área de contenido ══ */
.area-contenido {
  margin-left: 260px;
  margin-top: 74px;
  padding: 1.5rem 2rem;
  flex: 1;
  transition: margin-left 0.35s ease;
  background: #F5F5F5;
  min-height: calc(100vh - 74px);
  box-sizing: border-box;
}
.sistema-layout.sidebar-collapsed .area-contenido { margin-left: 0; }
.area-contenido.contenido-retrasado {
  transition: margin-left 0.28s ease;
  margin-left: 260px !important;
}

/* ══ Sidebar fijado — indicador visual en el botón ══ */
.sistema-layout:not(.sidebar-collapsed) .btn-toggle-menu {
  background: rgba(255,255,255,0.15);
}

/* Franja lateral izquierda visible cuando sidebar está colapsado */
.sistema-layout.sidebar-collapsed .menu-lateral {
  width: 0;
  padding-top: 0;
}

/* Franja invisible de 8px para activar expansión por hover */
.franja-hover-sidebar {
  position: fixed;
  top: 74px;
  left: 0;
  width: 8px;
  bottom: 0;
  z-index: 901;
  cursor: pointer;
}

/* Cuando el sidebar está fijo, el área de contenido tiene margin-left normal */
.sistema-layout:not(.sidebar-collapsed) .area-contenido {
  margin-left: 260px;
}

/* Eliminar el pseudo-elemento anterior que no funcionaba */
.sistema-layout.sidebar-collapsed::before {
  display: none;
}

/* ══════════════════════════════════════
   RESPONSIVE — Navbar y Layout
   Santiago Acosta — Modificaciones SICE
══════════════════════════════════════ */

/* ── Tablet (768px – 1024px) ── */
@media (max-width: 1024px) {
  .titulo-sistema {
    font-size: 0.88rem;
    letter-spacing: 0;
  }

  .grupo-busqueda {
    width: 200px;
  }

  .encabezado-superior {
    padding: 0 1.2rem;
  }

  .encabezado-derecha {
    gap: 1.2rem;
  }

  .area-contenido {
    padding: 1.2rem 1.4rem;
  }
}

/* ── Móvil grande (640px – 768px) ── */
@media (max-width: 768px) {

  /* Ocultar título largo, mostrar solo "SICE" */
  .titulo-sistema {
    display: none;
  }

  /* Buscador más compacto */
  .grupo-busqueda {
    width: 160px;
  }

  .grupo-busqueda input {
    font-size: 0.82rem;
    padding: 8px 12px 8px 36px;
  }

  .encabezado-superior {
    padding: 0 1rem;
    height: 60px;
  }

  .logo-encabezado {
    height: 40px;
  }

  /* Sidebar ocupa toda la pantalla en móvil */
  .menu-lateral {
    top: 60px;
    width: 260px;
  }

  /* Cuando sidebar está abierto en móvil, oscurecer fondo */
  .sistema-layout:not(.sidebar-collapsed) .menu-lateral {
    box-shadow: 4px 0 20px rgba(0,0,0,0.3);
  }

  /* El contenido no se desplaza — el sidebar flota encima */
  .area-contenido {
    margin-left: 0 !important;
    margin-top: 60px;
    padding: 1rem;
  }

  .area-contenido.contenido-retrasado {
    margin-left: 0 !important;
  }

  /* Panel de notificaciones más angosto */
  .panel-notificaciones {
    width: 300px;
    right: -60px;
  }

  .nombre-usuario {
    display: none;
  }

  .flecha-desplegable {
    display: none;
  }

  .encabezado-derecha {
    gap: 0.75rem;
  }
}

/* ── Móvil pequeño (menos de 480px) ── */
@media (max-width: 480px) {

  /* Ocultar búsqueda — solo icono de lupa queda */
  .grupo-busqueda {
    width: 36px;
    overflow: hidden;
  }

  .grupo-busqueda input {
    opacity: 0;
    width: 0;
    padding: 0;
  }

  /* Al hacer focus en la lupa, expandir */
  .grupo-busqueda:focus-within {
    width: 180px;
    position: fixed;
    top: 10px;
    left: 60px;
    right: 10px;
    z-index: 1100;
  }

  .grupo-busqueda:focus-within input {
    opacity: 1;
    width: 100%;
    padding: 8px 12px 8px 36px;
  }

  .encabezado-superior {
    padding: 0 0.75rem;
  }

  .panel-notificaciones {
    width: 280px;
    right: -80px;
  }

  .desplegable-usuario {
    right: -10px;
    min-width: 180px;
  }
}

/* ── Overlay para sidebar en móvil ── */
@media (max-width: 768px) {
  .sistema-layout:not(.sidebar-collapsed)::after {
    content: '';
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 899;
    top: 60px;
  }
}

/* ══════════════════════════════════════
  ANTI-ZOOM — SICE
══════════════════════════════════════ */

/* ── Viewport meta comportamiento ── */
html {
  font-size: 16px;
  -webkit-text-size-adjust: 100%;
  text-size-adjust: 100%;
  overflow-x: hidden;
}

/* ── Tamaños mínimos táctiles para botones ── */
button, input, select, textarea, a {
  min-height: 36px;
  font-size: 0.875rem;
}

/* ── Inputs nunca más pequeños que 16px en móvil
   (evita zoom automático en iOS al enfocar) ── */
@media (max-width: 768px) {
  input, select, textarea {
    font-size: 16px !important;
  }
}

/* ── Tablas con scroll horizontal en lugar de overflow oculto ── */
table {
  width: 100%;
  border-collapse: collapse;
}

.table-container,
.tabla-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

/* ── Contenedores no desbordan en pantallas pequeñas ── */
img, svg, video {
  max-width: 100%;
  height: auto;
}

/* ── Texto mínimo legible sin zoom ── */
p, span, td, th, label, li {
  font-size: clamp(0.8rem, 2vw, 1rem);
  line-height: 1.55;
}

h1 { font-size: clamp(1.3rem, 4vw, 1.75rem); }
h2 { font-size: clamp(1.1rem, 3vw, 1.4rem);  }
h3 { font-size: clamp(1rem,  2.5vw, 1.2rem); }

/* ── Modales no desbordan en pantallas pequeñas ── */
.modal-content,
.modal-caja {
  max-width: 95vw;
  max-height: 90vh;
  overflow-y: auto;
}

/* ── Cards y paneles ── */
.kpi-card,
.grafica-card,
.panel-card,
.form-card,
.tabla-card {
  min-width: 0;
  overflow: hidden;
}

/* ── Grids adaptativos ── */
@media (max-width: 768px) {
  .kpi-grid {
    grid-template-columns: repeat(2, 1fr) !important;
  }

  .fila-graficas,
  .fila-inferior {
    grid-template-columns: 1fr !important;
  }

  .form-row,
  .fila-campos {
    flex-direction: column !important;
  }

  .grilla-roles,
  .grilla-permisos,
  .grilla-acciones,
  .campos-grid-modal,
  .filtros-grid {
    grid-template-columns: 1fr !important;
  }
}

@media (max-width: 480px) {
  .kpi-grid {
    grid-template-columns: 1fr !important;
  }

  .form-grupo-doble {
    grid-template-columns: 1fr !important;
  }

  .filters-bar,
  .filtros-fila {
    flex-direction: column !important;
    align-items: stretch !important;
  }

  .filters-bar .btn-nuevo,
  .filters-bar .btn-limpiar {
    width: 100%;
    justify-content: center;
  }

  .paginacion {
    flex-direction: column !important;
    align-items: center !important;
    gap: 0.5rem !important;
  }

  .modal-footer,
  .modal-pie {
    flex-direction: column !important;
    gap: 0.5rem !important;
  }

  .modal-footer button,
  .modal-pie button {
    width: 100% !important;
    justify-content: center;
  }
}

/* ══════════════════════════════════════
   BOTÓN REGRESAR FLOTANTE (FAB)
   Diego — SICE Back Button
══════════════════════════════════════ */

.fab-regresar {
  position: fixed;
  bottom: 1.5rem;
  left: 1.5rem;
  z-index: 1200;

  display: flex;
  align-items: center;
  justify-content: center;

  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: none;
  cursor: pointer;

  background-color: #1B396A;
  color: #ffffff;
  box-shadow: 0 4px 14px rgba(27, 57, 106, 0.45);

  opacity: 0.88;
  transition: opacity 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}

.fab-regresar:hover {
  opacity: 1;
  transform: scale(1.08);
  box-shadow: 0 6px 20px rgba(27, 57, 106, 0.6);
}

.fab-regresar:active {
  transform: scale(0.93);
  box-shadow: 0 2px 8px rgba(27, 57, 106, 0.4);
}

.fab-regresar:focus-visible {
  outline: 3px solid #DBEAFE;
  outline-offset: 3px;
}

.fab-icono {
  width: 20px;
  height: 20px;
  pointer-events: none;
  flex-shrink: 0;
}

/* ── Animación de entrada / salida ── */
.fab-back-enter-active,
.fab-back-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}
.fab-back-enter-from,
.fab-back-leave-to {
  opacity: 0;
  transform: scale(0.65) translateY(10px);
}

/* ── Móvil: área táctil más generosa ── */
@media (max-width: 768px) {
  .fab-regresar {
    width: 48px;
    height: 48px;
    bottom: 1.25rem;
    left: 1rem;
    opacity: 1; /* siempre visible en móvil */
  }

  .fab-icono {
    width: 22px;
    height: 22px;
  }
}

/* ── Móvil muy pequeño ── */
@media (max-width: 480px) {
  .fab-regresar {
    bottom: 1rem;
    left: 0.75rem;
  }
}
</style>