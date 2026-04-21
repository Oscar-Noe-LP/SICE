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
          <span class="nombre-usuario">{{ nombreRolActual }}</span>
          <span class="flecha-desplegable" :class="{ rotada: mostrarMenuUsuario }">▼</span>

          <div v-if="mostrarMenuUsuario" class="desplegable-usuario" @click.stop>
            <div class="elemento-desplegable" @click="establecerRol('servicios-escolares')">
              <svg xmlns="http://www.w3.org/2000/svg" class="icono-rol" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              Servicios Escolares
            </div>
            <div class="elemento-desplegable" @click="establecerRol('admin')">
              <svg xmlns="http://www.w3.org/2000/svg" class="icono-rol" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              </svg>
              Administrador
            </div>
            <div class="separador-desplegable"></div>
            <div class="elemento-desplegable elemento-cerrar-sesion">
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

    <!-- ══ MENÚ LATERAL ══ -->
    <aside class="menu-lateral" @click.stop>
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
        <div class="elemento-menu elemento-padre" @click.stop="toggleServicios" :aria-expanded="isServiciosOpen" aria-label="Servicios Escolares">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
          <span class="etiqueta-menu">Servicios Escolares</span>
          <span class="flecha-submenu" :class="{ abierto: isServiciosOpen }">›</span>
        </div>
        <div v-if="isServiciosOpen" class="submenu">
          <router-link to="/servicios-escolares" class="elemento-menu elemento-submenu" active-class="activo">Principal</router-link>
          <router-link to="/alumnos"             class="elemento-menu elemento-submenu" active-class="activo">Alumnos</router-link>
          <router-link to="/evaluaciones"        class="elemento-menu elemento-submenu" active-class="activo">Evaluaciones</router-link>
          <router-link to="/calificaciones"      class="elemento-menu elemento-submenu" active-class="activo">Calificaciones</router-link>
          <router-link to="/inscripcion"         class="elemento-menu elemento-submenu" active-class="activo">Inscripción</router-link>
          <router-link to="/gestion-grupos"      class="elemento-menu elemento-submenu" active-class="activo">Grupos</router-link>
          <!-- Inscripciones Detalladas: sub-submenú colapsable -->
          <div class="elemento-menu elemento-submenu elemento-padre" @click.stop="toggleInscripcionesDetalladas">
            <span style="flex:1">Inscripciones Detalladas</span>
            <span class="flecha-submenu" :class="{ abierto: isInscripcionesDetalladasOpen }">›</span>
          </div>
          <div v-if="isInscripcionesDetalladasOpen" class="submenu">
            <router-link to="/inscripciones"           class="elemento-menu elemento-submenu elemento-submenu-anidado" active-class="activo">Panel General</router-link>
            <router-link to="/inscripciones/historial" class="elemento-menu elemento-submenu elemento-submenu-anidado" active-class="activo">Historial</router-link>
          </div>
        </div>

        <!-- ── Gestión Académica ── -->
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

        <!-- ── Eventos ── -->
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

        <!-- ── Comité Académico ── -->
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

          <!-- ── Asignación Docente a Grupos ── -->
          <div class="elemento-menu elemento-padre" @click.stop="toggleAsignacionDocente" :aria-expanded="isAsignacionDocenteOpen" aria-label="Asignación Docente a Grupos">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-menu" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="etiqueta-menu">Asignación Docente</span>
            <span class="flecha-submenu" :class="{ abierto: isAsignacionDocenteOpen }">›</span>
          </div>
          <div v-if="isAsignacionDocenteOpen" class="submenu">
            <router-link to="/asignacion-docente"       class="elemento-menu elemento-submenu" active-class="activo">Asignación de Grupos</router-link>
            <router-link to="/asignacion-docente/carga" class="elemento-menu elemento-submenu" active-class="activo">Carga Académica</router-link>
          </div>

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

        </template>

      </nav>
    </aside>

    <!-- ══ CONTENIDO PRINCIPAL ══ -->
    <main class="area-contenido">
      <slot :key="$route.fullPath" :busquedaGlobal="busquedaGlobal" />
    </main>

  </div>
</template>

<script setup>
import { useKeyboardShortcuts } from '@/composables/useKeyboardShortcuts'
useKeyboardShortcuts()

import { ref, computed, watch, onMounted } from 'vue'

// ── Estado global ─────────────────────────────────────────────────────
const busquedaGlobal = ref('')
const isCollapsed    = ref(false)

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
const isHistorialAcademicoOpen       = ref(false)
const isInscripcionesDetalladasOpen  = ref(false)

// ── Encabezado ────────────────────────────────────────────────────────
const mostrarMenuUsuario    = ref(false)
const mostrarNotificaciones = ref(false)
const rolActual             = ref('servicios-escolares')
const notificaciones        = ref([])

// ── Persistencia en localStorage ─────────────────────────────────────
onMounted(() => {
  const load = (key, refVar, parse = true) => {
    const val = localStorage.getItem(key)
    if (val !== null) refVar.value = parse ? JSON.parse(val) : val
  }
  load('isServiciosOpen',          isServiciosOpen)
  load('isGestionAcademicaOpen',   isGestionAcademicaOpen)
  load('isEventosOpen',            isEventosOpen)
  load('isComiteOpen',             isComiteOpen)
  load('isSeguridadOpen',          isSeguridadOpen)
  load('isRecursosHumanosOpen',    isRecursosHumanosOpen)
  load('isPersonasOpen',           isPersonasOpen)
  load('isAsignacionDocenteOpen',  isAsignacionDocenteOpen)
  load('isKardexOpen',             isKardexOpen)
  load('isHistorialAcademicoOpen', isHistorialAcademicoOpen)
  load('isInscripcionesDetalladasOpen', isInscripcionesDetalladasOpen)
  load('rolActual',                rolActual, false)
})

watch(
  [
    isServiciosOpen, isGestionAcademicaOpen, isEventosOpen,
    isComiteOpen, isSeguridadOpen, isRecursosHumanosOpen,
    isPersonasOpen, isAsignacionDocenteOpen, isKardexOpen,
    isHistorialAcademicoOpen, isInscripcionesDetalladasOpen, rolActual
  ],
  () => {
    localStorage.setItem('isServiciosOpen',          JSON.stringify(isServiciosOpen.value))
    localStorage.setItem('isGestionAcademicaOpen',   JSON.stringify(isGestionAcademicaOpen.value))
    localStorage.setItem('isEventosOpen',            JSON.stringify(isEventosOpen.value))
    localStorage.setItem('isComiteOpen',             JSON.stringify(isComiteOpen.value))
    localStorage.setItem('isSeguridadOpen',          JSON.stringify(isSeguridadOpen.value))
    localStorage.setItem('isRecursosHumanosOpen',    JSON.stringify(isRecursosHumanosOpen.value))
    localStorage.setItem('isPersonasOpen',           JSON.stringify(isPersonasOpen.value))
    localStorage.setItem('isAsignacionDocenteOpen',  JSON.stringify(isAsignacionDocenteOpen.value))
    localStorage.setItem('isKardexOpen',             JSON.stringify(isKardexOpen.value))
    localStorage.setItem('isHistorialAcademicoOpen', JSON.stringify(isHistorialAcademicoOpen.value))
    localStorage.setItem('isInscripcionesDetalladasOpen', JSON.stringify(isInscripcionesDetalladasOpen.value))
    localStorage.setItem('rolActual',                rolActual.value)
  },
  { deep: true }
)

// ── Computed ──────────────────────────────────────────────────────────
const nombreRolActual = computed(() =>
  rolActual.value === 'admin' ? 'Administrador' : 'Servicios Escolares'
)

// ── Toggles ───────────────────────────────────────────────────────────
const toggleSidebar            = () => { isCollapsed.value = !isCollapsed.value }
const toggleServicios          = () => { isServiciosOpen.value = !isServiciosOpen.value }
const toggleGestionAcademica   = () => { isGestionAcademicaOpen.value = !isGestionAcademicaOpen.value }
const toggleEventos            = () => { isEventosOpen.value = !isEventosOpen.value }
const toggleComite             = () => { isComiteOpen.value = !isComiteOpen.value }
const toggleSeguridad          = () => { isSeguridadOpen.value = !isSeguridadOpen.value }
const toggleRecursosHumanos    = () => { isRecursosHumanosOpen.value = !isRecursosHumanosOpen.value }
const togglePersonas           = () => { isPersonasOpen.value = !isPersonasOpen.value }
const toggleAsignacionDocente  = () => { isAsignacionDocenteOpen.value = !isAsignacionDocenteOpen.value }
const toggleKardex             = () => { isKardexOpen.value = !isKardexOpen.value }
const toggleHistorialAcademico = () => { isHistorialAcademicoOpen.value = !isHistorialAcademicoOpen.value }
const toggleInscripcionesDetalladas = () => { isInscripcionesDetalladasOpen.value = !isInscripcionesDetalladasOpen.value }

const toggleMenuUsuario = () => {
  mostrarMenuUsuario.value = !mostrarMenuUsuario.value
  mostrarNotificaciones.value = false
}
const toggleNotificaciones = () => {
  mostrarNotificaciones.value = !mostrarNotificaciones.value
  mostrarMenuUsuario.value = false
}
const cerrarMenus = () => {
  mostrarMenuUsuario.value = false
  mostrarNotificaciones.value = false
}
const marcarTodasLeidas = () => {
  notificaciones.value = []
  mostrarNotificaciones.value = false
}
const establecerRol = (rol) => {
  rolActual.value = rol
  mostrarMenuUsuario.value = false
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
  transition: width 0.35s ease;
  z-index: 900;
  box-shadow: 2px 0 8px rgba(0,0,0,0.07);
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

.menu-lateral::-webkit-scrollbar { width: 4px; }
.menu-lateral::-webkit-scrollbar-track { background: transparent; }
.menu-lateral::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 4px; }

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
</style>