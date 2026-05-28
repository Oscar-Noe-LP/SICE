<template>
  <div class="sistema-layout" @click="cerrarMenus">

    <!-- ══ ENCABEZADO FIJO ══ -->
    <header class="encabezado-superior">
      <div class="encabezado-izquierda">
        <button
          class="btn-hamburguesa"
          @click.stop="toggleDrawer"
          aria-label="Abrir menú"
          title="Menú"
        >
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
        <img src="/logo-tecnm.png" alt="Logo TecNM" class="logo-encabezado">
        <span class="titulo-sistema">SISTEMA INTEGRAL DE CONTROL ESCOLAR</span>
      </div>

      <div class="encabezado-derecha">
        <!-- Buscador -->
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
            :tabindex="busquedaOculta ? -1 : 0"
            :readonly="busquedaOculta"
            autocomplete="off"
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
            <div style="padding: 10px 16px 8px; border-bottom: 1px solid #E5E7EB;">
              <p style="margin:0; font-size:0.85rem; font-weight:600; color:#1A1A1A;">{{ nombreUsuarioActual }}</p>
              <p style="margin:2px 0 0; font-size:0.78rem; color:#6B7280;">{{ etiquetaRol }}</p>
            </div>
            <div class="separador-desplegable"></div>
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

    <!-- ══ BARRA DE NAVEGACIÓN HORIZONTAL (solo desktop) ══ -->
    <nav class="barra-nav-horizontal" @click.stop aria-label="Menú principal">
      <div class="nav-scroll-inner" ref="navScrollRef">

        <!-- Inicio -->
        <router-link
          to="/inicio"
          class="nav-item nav-item-link"
          active-class="nav-activo"
          :title="tooltips.inicio"
          @click="cerrarTab"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span class="nav-label">Inicio</span>
        </router-link>

        <!-- ── Servicios Escolares ── -->
        <template v-if="puedeVer.serviciosEscolares">
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'servicios' }"
            @click.stop="toggleTab('servicios')"
            :title="tooltips.servicios"
            aria-haspopup="true"
            :aria-expanded="tabActivo === 'servicios'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="nav-label">Servicios Escolares</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'servicios' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </template>

        <!-- ── Gestión Académica ── -->
        <template v-if="puedeVer.gestionAcademica">
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'academica' }"
            @click.stop="toggleTab('academica')"
            :title="tooltips.academica"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            </svg>
            <span class="nav-label">Gestión Académica</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'academica' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </template>

        <!-- ── Eventos ── -->
        <template v-if="puedeVer.eventos">
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'eventos' }"
            @click.stop="toggleTab('eventos')"
            :title="tooltips.eventos"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="nav-label">Eventos</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'eventos' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </template>

        <!-- ── Comité Académico ── -->
        <template v-if="puedeVer.comite">
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'comite' }"
            @click.stop="toggleTab('comite')"
            :title="tooltips.comite"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span class="nav-label">Comité Académico</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'comite' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </template>

        <!-- ══ ADMINISTRACIÓN (solo admin) ══ -->
        <template v-if="rolActual === 'admin'">
          <div class="nav-separador-admin">
            <span class="nav-separador-admin-label">ADMINISTRACIÓN</span>
          </div>

          <!-- Seguridad y Usuarios -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'seguridad' }"
            @click.stop="toggleTab('seguridad')"
            :title="tooltips.seguridad"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span class="nav-label">Seguridad y Usuarios</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'seguridad' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>

          <!-- Recursos Humanos -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'rrhh' }"
            @click.stop="toggleTab('rrhh')"
            :title="tooltips.rrhh"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <span class="nav-label">Recursos Humanos</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'rrhh' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>

          <!-- Personas -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'personas' }"
            @click.stop="toggleTab('personas')"
            :title="tooltips.personas"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="nav-label">Personas</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'personas' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </template>

        <!-- ── Asignación Docente ── -->
        <template v-if="puedeVer.asignacionDocente">
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'asignacion' }"
            @click.stop="toggleTab('asignacion')"
            :title="tooltips.asignacion"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            <span class="nav-label">Asignación Docente</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'asignacion' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </template>

        <!-- Kardex e Historial solo admin -->
        <template v-if="rolActual === 'admin'">
          <!-- Kardex -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'kardex' }"
            @click.stop="toggleTab('kardex')"
            :title="tooltips.kardex"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="nav-label">Kardex</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'kardex' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>

          <!-- Historial Académico -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'historial' }"
            @click.stop="toggleTab('historial')"
            :title="tooltips.historial"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span class="nav-label">Historial Académico</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'historial' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        </template>

      </div>

      <!-- ══ RIBBON PANEL — fuera del scroll, pegado al fondo del nav ══ -->
      <Transition name="ribbon">
        <div v-if="tabActivo" class="ribbon-panel" @click.stop>

          <!-- ── Servicios Escolares ── -->
          <template v-if="tabActivo === 'servicios'">
            <router-link v-if="puedeVerItem('/servicios-escolares')" to="/servicios-escolares" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              </div>
              <span>Principal</span>
            </router-link>
            <router-link v-if="puedeVerItem('/alumnos')" to="/alumnos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <span>Alumnos</span>
            </router-link>
            <router-link v-if="puedeVerItem('/evaluaciones')" to="/evaluaciones" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
              </div>
              <span>Evaluaciones</span>
            </router-link>
            <router-link v-if="puedeVerItem('/calificaciones')" to="/calificaciones" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </div>
              <span>Calificaciones</span>
            </router-link>
            <router-link v-if="puedeVerItem('/inscripcion')" to="/inscripcion" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              </div>
              <span>Inscripción</span>
            </router-link>
            <router-link v-if="puedeVerItem('/gestion-grupos')" to="/gestion-grupos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              </div>
              <span>Grupos y Horarios</span>
            </router-link>
            <template v-if="puedeVerItem('/inscripciones')">
              <div class="ribbon-separador"></div>
              <router-link to="/inscripciones" class="ribbon-item" @click="cerrarTab">
                <div class="ribbon-icono-wrap">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                </div>
                <span>Insc. Detalladas</span>
              </router-link>
              <router-link to="/inscripciones/historial" class="ribbon-item" @click="cerrarTab">
                <div class="ribbon-icono-wrap">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span>Historial Insc.</span>
              </router-link>
            </template>
          </template>

          <!-- ── Gestión Académica ── -->
          <template v-if="tabActivo === 'academica'">
            <router-link to="/gestion-academica" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              </div>
              <span>Panel Principal</span>
            </router-link>
            <router-link to="/gestion-academica/carreras" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              </div>
              <span>Carreras</span>
            </router-link>
            <router-link to="/gestion-academica/planes" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <span>Planes de Estudio</span>
            </router-link>
            <router-link to="/gestion-academica/materias" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              </div>
              <span>Materias</span>
            </router-link>
            <router-link to="/gestion-academica/prerrequisitos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
              </div>
              <span>Prerrequisitos</span>
            </router-link>
            <router-link to="/gestion-academica/periodos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              </div>
              <span>Periodos Académicos</span>
            </router-link>
            <router-link to="/gestion-academica/edificios-aulas" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
              </div>
              <span>Edificios y Aulas</span>
            </router-link>
          </template>

          <!-- ── Eventos ── -->
          <template v-if="tabActivo === 'eventos'">
            <router-link to="/eventos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              </div>
              <span>Lista de Eventos</span>
            </router-link>
            <router-link to="/eventos/nuevo" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
              </div>
              <span>Nuevo Evento</span>
            </router-link>
          </template>

          <!-- ── Comité Académico ── -->
          <template v-if="tabActivo === 'comite'">
            <router-link to="/comite" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              </div>
              <span>Panel Principal</span>
            </router-link>
            <router-link to="/comite/solicitudes" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
              </div>
              <span>Solicitudes</span>
            </router-link>
            <router-link to="/comite/solicitudes/nueva" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
              </div>
              <span>Nueva Solicitud</span>
            </router-link>
            <router-link to="/comite/sesiones" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <span>Sesiones</span>
            </router-link>
            <router-link to="/comite/resoluciones" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <span>Resoluciones</span>
            </router-link>
          </template>

          <!-- ── Seguridad y Usuarios ── -->
          <template v-if="tabActivo === 'seguridad'">
            <router-link to="/roles" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              </div>
              <span>Roles</span>
            </router-link>
            <router-link to="/permisos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
              </div>
              <span>Permisos</span>
            </router-link>
            <router-link to="/usuarios" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <span>Usuarios</span>
            </router-link>
            <router-link to="/bitacora" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              </div>
              <span>Bitácora</span>
            </router-link>
            <router-link to="/nuevo-usuario" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
              </div>
              <span>Nuevo Usuario</span>
            </router-link>
          </template>

          <!-- ── Recursos Humanos ── -->
          <template v-if="tabActivo === 'rrhh'">
            <router-link to="/recursos-humanos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              </div>
              <span>Principal</span>
            </router-link>
            <router-link to="/recursos-humanos/empleados" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <span>Empleados</span>
            </router-link>
            <router-link to="/recursos-humanos/docentes" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
              </div>
              <span>Docentes</span>
            </router-link>
            <router-link to="/recursos-humanos/adscripciones" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
              </div>
              <span>Adscripciones</span>
            </router-link>
            <router-link to="/recursos-humanos/puestos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              </div>
              <span>Puestos</span>
            </router-link>
            <router-link to="/recursos-humanos/departamentos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              </div>
              <span>Departamentos</span>
            </router-link>
          </template>

          <!-- ── Personas ── -->
          <template v-if="tabActivo === 'personas'">
            <router-link to="/personas" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <span>Catálogo</span>
            </router-link>
            <router-link to="/personas/nueva" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
              </div>
              <span>Nueva Persona</span>
            </router-link>
          </template>

          <!-- ── Asignación Docente ── -->
          <template v-if="tabActivo === 'asignacion'">
            <router-link v-if="rolActual === 'admin'" to="/asignacion-docente" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
              </div>
              <span>Asignación de Grupos</span>
            </router-link>
            <router-link to="/asignacion-docente/carga" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              </div>
              <span>Carga Académica</span>
            </router-link>
          </template>

          <!-- ── Kardex ── -->
          <template v-if="tabActivo === 'kardex'">
            <router-link to="/kardex" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <span>Consulta de Kardex</span>
            </router-link>
          </template>

          <!-- ── Historial Académico ── -->
          <template v-if="tabActivo === 'historial'">
            <router-link to="/historial-academico" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              </div>
              <span>Avance Curricular</span>
            </router-link>
          </template>

        </div>
      </Transition>
    </nav>

    <!-- ══ DRAWER MÓVIL ══ -->
    <Transition name="drawer">
      <aside v-if="drawerAbierto" ref="sidebarRef" class="drawer-movil" @click.stop>
        <div class="drawer-encabezado">
          <span class="drawer-titulo">Menú</span>
          <button class="drawer-cerrar" @click="drawerAbierto = false" aria-label="Cerrar menú">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <nav class="drawer-nav">
          <router-link to="/inicio" class="drawer-item" @click="drawerAbierto = false">
            <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Inicio
          </router-link>

          <!-- Servicios Escolares -->
          <template v-if="puedeVer.serviciosEscolares">
            <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('servicios')">
              <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
              <span>Servicios Escolares</span>
              <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'servicios' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
            <div v-if="drawerSeccionAbierta === 'servicios'" class="drawer-submenu">
              <router-link v-if="puedeVerItem('/servicios-escolares')" to="/servicios-escolares" class="drawer-subitem" @click="drawerAbierto = false">Principal</router-link>
              <router-link v-if="puedeVerItem('/alumnos')"             to="/alumnos"             class="drawer-subitem" @click="drawerAbierto = false">Alumnos</router-link>
              <router-link v-if="puedeVerItem('/evaluaciones')"        to="/evaluaciones"        class="drawer-subitem" @click="drawerAbierto = false">Evaluaciones</router-link>
              <router-link v-if="puedeVerItem('/calificaciones')"      to="/calificaciones"      class="drawer-subitem" @click="drawerAbierto = false">Calificaciones</router-link>
              <router-link v-if="puedeVerItem('/inscripcion')"         to="/inscripcion"         class="drawer-subitem" @click="drawerAbierto = false">Inscripción</router-link>
              <router-link v-if="puedeVerItem('/gestion-grupos')"      to="/gestion-grupos"      class="drawer-subitem" @click="drawerAbierto = false">Grupos y Horarios</router-link>
              <template v-if="puedeVerItem('/inscripciones')">
                <router-link to="/inscripciones"           class="drawer-subitem" @click="drawerAbierto = false">Inscripciones Detalladas</router-link>
                <router-link to="/inscripciones/historial" class="drawer-subitem drawer-subitem--anidado" @click="drawerAbierto = false">Historial</router-link>
              </template>
            </div>
          </template>

          <!-- Gestión Académica -->
          <template v-if="puedeVer.gestionAcademica">
            <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('academica')">
              <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
              </svg>
              <span>Gestión Académica</span>
              <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'academica' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
            <div v-if="drawerSeccionAbierta === 'academica'" class="drawer-submenu">
              <router-link to="/gestion-academica"                 class="drawer-subitem" @click="drawerAbierto = false">Panel Principal</router-link>
              <router-link to="/gestion-academica/carreras"        class="drawer-subitem" @click="drawerAbierto = false">Carreras</router-link>
              <router-link to="/gestion-academica/planes"          class="drawer-subitem" @click="drawerAbierto = false">Planes de Estudio</router-link>
              <router-link to="/gestion-academica/materias"        class="drawer-subitem" @click="drawerAbierto = false">Materias</router-link>
              <router-link to="/gestion-academica/prerrequisitos"  class="drawer-subitem" @click="drawerAbierto = false">Prerrequisitos</router-link>
              <router-link to="/gestion-academica/periodos"        class="drawer-subitem" @click="drawerAbierto = false">Periodos Académicos</router-link>
              <router-link to="/gestion-academica/edificios-aulas" class="drawer-subitem" @click="drawerAbierto = false">Edificios y Aulas</router-link>
            </div>
          </template>

          <!-- Eventos -->
          <template v-if="puedeVer.eventos">
            <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('eventos')">
              <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <span>Eventos</span>
              <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'eventos' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
            <div v-if="drawerSeccionAbierta === 'eventos'" class="drawer-submenu">
              <router-link to="/eventos"       class="drawer-subitem" @click="drawerAbierto = false">Lista de Eventos</router-link>
              <router-link to="/eventos/nuevo" class="drawer-subitem" @click="drawerAbierto = false">Nuevo Evento</router-link>
            </div>
          </template>

          <!-- Comité -->
          <template v-if="puedeVer.comite">
            <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('comite')">
              <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
              </svg>
              <span>Comité Académico</span>
              <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'comite' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
            <div v-if="drawerSeccionAbierta === 'comite'" class="drawer-submenu">
              <router-link to="/comite"                   class="drawer-subitem" @click="drawerAbierto = false">Panel Principal</router-link>
              <router-link to="/comite/solicitudes"       class="drawer-subitem" @click="drawerAbierto = false">Solicitudes</router-link>
              <router-link to="/comite/solicitudes/nueva" class="drawer-subitem" @click="drawerAbierto = false">Nueva Solicitud</router-link>
              <router-link to="/comite/sesiones"          class="drawer-subitem" @click="drawerAbierto = false">Sesiones</router-link>
              <router-link to="/comite/resoluciones"      class="drawer-subitem" @click="drawerAbierto = false">Resoluciones</router-link>
            </div>
          </template>

          <!-- Admin -->
          <template v-if="rolActual === 'admin'">
            <div class="drawer-separador"><span>ADMINISTRACIÓN</span></div>

            <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('seguridad')">
              <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
              <span>Seguridad y Usuarios</span>
              <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'seguridad' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
            <div v-if="drawerSeccionAbierta === 'seguridad'" class="drawer-submenu">
              <router-link to="/roles"         class="drawer-subitem" @click="drawerAbierto = false">Roles</router-link>
              <router-link to="/permisos"      class="drawer-subitem" @click="drawerAbierto = false">Permisos</router-link>
              <router-link to="/usuarios"      class="drawer-subitem" @click="drawerAbierto = false">Usuarios</router-link>
              <router-link to="/bitacora"      class="drawer-subitem" @click="drawerAbierto = false">Bitácora</router-link>
              <router-link to="/nuevo-usuario" class="drawer-subitem" @click="drawerAbierto = false">Nuevo Usuario</router-link>
            </div>

            <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('rrhh')">
              <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <span>Recursos Humanos</span>
              <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'rrhh' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
            <div v-if="drawerSeccionAbierta === 'rrhh'" class="drawer-submenu">
              <router-link to="/recursos-humanos"               class="drawer-subitem" @click="drawerAbierto = false">Principal</router-link>
              <router-link to="/recursos-humanos/empleados"     class="drawer-subitem" @click="drawerAbierto = false">Empleados</router-link>
              <router-link to="/recursos-humanos/docentes"      class="drawer-subitem" @click="drawerAbierto = false">Docentes</router-link>
              <router-link to="/recursos-humanos/adscripciones" class="drawer-subitem" @click="drawerAbierto = false">Adscripciones</router-link>
              <router-link to="/recursos-humanos/puestos"       class="drawer-subitem" @click="drawerAbierto = false">Puestos</router-link>
              <router-link to="/recursos-humanos/departamentos" class="drawer-subitem" @click="drawerAbierto = false">Departamentos</router-link>
            </div>

            <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('personas')">
              <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <span>Personas</span>
              <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'personas' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
            <div v-if="drawerSeccionAbierta === 'personas'" class="drawer-submenu">
              <router-link to="/personas"       class="drawer-subitem" @click="drawerAbierto = false">Catálogo</router-link>
              <router-link to="/personas/nueva" class="drawer-subitem" @click="drawerAbierto = false">Nueva Persona</router-link>
            </div>

            <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('kardex')">
              <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <span>Kardex</span>
              <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'kardex' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
            <div v-if="drawerSeccionAbierta === 'kardex'" class="drawer-submenu">
              <router-link to="/kardex" class="drawer-subitem" @click="drawerAbierto = false">Consulta de Kardex</router-link>
            </div>

            <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('historial')">
              <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              <span>Historial Académico</span>
              <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'historial' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
            <div v-if="drawerSeccionAbierta === 'historial'" class="drawer-submenu">
              <router-link to="/historial-academico" class="drawer-subitem" @click="drawerAbierto = false">Avance Curricular</router-link>
            </div>
          </template>

          <!-- Asignación Docente -->
          <template v-if="puedeVer.asignacionDocente">
            <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('asignacion')">
              <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
              <span>Asignación Docente</span>
              <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'asignacion' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
            <div v-if="drawerSeccionAbierta === 'asignacion'" class="drawer-submenu">
              <router-link v-if="rolActual === 'admin'" to="/asignacion-docente" class="drawer-subitem" @click="drawerAbierto = false">Asignación de Grupos</router-link>
              <router-link to="/asignacion-docente/carga" class="drawer-subitem" @click="drawerAbierto = false">Carga Académica</router-link>
            </div>
          </template>
        </nav>
      </aside>
    </Transition>

    <!-- Overlay drawer móvil -->
    <Transition name="overlay">
      <div v-if="drawerAbierto" class="drawer-overlay" @click="drawerAbierto = false"></div>
    </Transition>

    <!-- ══ CONTENIDO PRINCIPAL ══ -->
    <main class="area-contenido" :class="{ 'con-ribbon': tabActivo !== null }">
      <slot :busquedaGlobal="busquedaGlobal" />
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
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
        </svg>
      </button>
    </Transition>

  </div>
</template>


<script setup>
import { useKeyboardShortcuts } from '@/composables/useKeyboardShortcuts'
useKeyboardShortcuts()

import { ref, computed, watch, onMounted, onUnmounted, onBeforeUnmount } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const tooltips = {
  servicios:  'Servicios Escolares → Alumnos, calificaciones, inscripciones y grupos',
  academica:  'Gestión Académica → Planes de estudio, materias, edificios y periodos',
  eventos:    'Eventos → Consulta y crea eventos institucionales',
  comite:     'Comité Académico → Solicitudes, sesiones y resoluciones',
  seguridad:  'Seguridad → Roles, permisos, usuarios y bitácora',
  rrhh:       'Recursos Humanos → Empleados, docentes y departamentos',
  personas:   'Personas → Catálogo y registro de personas',
  asignacion: 'Asignación Docente → Grupos y carga académica',
  kardex:     'Kardex → Consulta el historial de calificaciones',
  historial:  'Historial Académico → Avance curricular del alumno',
}
const router = useRouter()
const route  = useRoute()

// ── Estado global ─────────────────────────────────────────────────────
const busquedaGlobal = ref('')

// ── Control de ventana ────────────────────────────────────────────────
const anchoVentana = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)
const esMobil      = computed(() => anchoVentana.value <= 768)
const busquedaOculta = computed(() => anchoVentana.value <= 480)

let resizeTimer = null
const onResize = () => {
  if (resizeTimer) clearTimeout(resizeTimer)
  resizeTimer = setTimeout(() => { anchoVentana.value = window.innerWidth }, 100)
}
if (typeof window !== 'undefined') window.addEventListener('resize', onResize, { passive: true })

onUnmounted(() => {
  if (typeof window !== 'undefined') window.removeEventListener('resize', onResize)
  if (resizeTimer) clearTimeout(resizeTimer)
})

// ── Tab activo (estilo Word) ──────────────────────────────────────────
const tabActivo = ref(null)

const toggleTab = (nombre) => {
  tabActivo.value = tabActivo.value === nombre ? null : nombre
}

const cerrarTab = () => {
  tabActivo.value = null
}

// ── Drawer móvil ──────────────────────────────────────────────────────
const drawerAbierto       = ref(false)
const drawerSeccionAbierta = ref(null)

const toggleDrawer = () => { drawerAbierto.value = !drawerAbierto.value }
const toggleDrawerSeccion = (nombre) => {
  drawerSeccionAbierta.value = drawerSeccionAbierta.value === nombre ? null : nombre
}

// ── Encabezado ────────────────────────────────────────────────────────
const mostrarMenuUsuario    = ref(false)
const mostrarNotificaciones = ref(false)
const notificaciones        = ref([])

// ── Datos del usuario logueado ────────────────────────────────────────
const usuarioLogueado = ref(JSON.parse(localStorage.getItem('usuario') || 'null'))
const rolActual       = computed(() => usuarioLogueado.value?.rol ?? 'servicios-escolares')

const ETIQUETAS_ROL = {
  'admin':               'Administrador',
  'docente':             'Docente',
  'servicios-escolares': 'Servicios Escolares',
}
const etiquetaRol         = computed(() => ETIQUETAS_ROL[rolActual.value] ?? rolActual.value)
const nombreUsuarioActual = computed(() => usuarioLogueado.value?.nombre_usuario ?? etiquetaRol.value)

// ── Permisos por módulo ───────────────────────────────────────────────
const MODULOS_POR_ROL = {
  'docente':             ['servicios-escolares', 'eventos', 'asignacion-docente'],
  'servicios-escolares': ['servicios-escolares', 'gestion-academica', 'eventos', 'comite'],
}

const puedeVer = computed(() => {
  const rol = rolActual.value
  if (rol === 'admin') return { serviciosEscolares: true, gestionAcademica: true, eventos: true, comite: true, asignacionDocente: true }
  const modulos = MODULOS_POR_ROL[rol] ?? []
  return {
    serviciosEscolares: modulos.includes('servicios-escolares'),
    gestionAcademica:   modulos.includes('gestion-academica'),
    eventos:            modulos.includes('eventos'),
    comite:             modulos.includes('comite'),
    asignacionDocente:  modulos.includes('asignacion-docente'),
  }
})

const ITEMS_POR_ROL = {
  'docente': ['/evaluaciones', '/calificaciones', '/gestion-grupos', '/asignacion-docente/carga', '/eventos'],
  'servicios-escolares': null,
}

const puedeVerItem = computed(() => (ruta) => {
  const rol = rolActual.value
  if (rol === 'admin') return true
  const items = ITEMS_POR_ROL[rol]
  if (items === null || items === undefined) return true
  return items.some(r => ruta.startsWith(r))
})

// ── Cerrar menús ──────────────────────────────────────────────────────
const cerrarMenus = () => {
  mostrarMenuUsuario.value    = false
  mostrarNotificaciones.value = false
  tabActivo.value = null        // ← Modificado
}

const toggleMenuUsuario = () => {
  mostrarMenuUsuario.value    = !mostrarMenuUsuario.value
  mostrarNotificaciones.value = false
}
const toggleNotificaciones = () => {
  mostrarNotificaciones.value = !mostrarNotificaciones.value
  mostrarMenuUsuario.value    = false
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

// ── Cerrar al navegar ─────────────────────────────────────────────────
watch(() => router.currentRoute.value.fullPath, () => {
  cerrarMenus()
  drawerAbierto.value = false
})

// ── Cerrar drawer al pasar a desktop ─────────────────────────────────
watch(esMobil, (ahoraMobil) => {
  if (!ahoraMobil) drawerAbierto.value = false
})

onMounted(() => {
  // Scroll horizontal con rueda del ratón en el nav
  const navScroll = document.querySelector('.nav-scroll-inner')
  if (navScroll) {
    navScroll.addEventListener('wheel', (e) => {
      if (e.deltaY !== 0) {
        e.preventDefault()
        navScroll.scrollLeft += e.deltaY
      }
    }, { passive: false })
  }
})

onBeforeUnmount(() => {
  // Limpieza si es necesario
})

const RUTAS_PRINCIPALES = new Set([
  '/inicio', '/dashboard', '/servicios-escolares', '/alumnos', '/evaluaciones',
  '/calificaciones', '/inscripcion', '/inscripciones', '/gestion-grupos',
  '/gestion-academica', '/eventos', '/comite', '/kardex', '/historial-academico',
  '/asignacion-docente', '/roles', '/permisos', '/usuarios', '/bitacora',
  '/nuevo-usuario', '/recursos-humanos', '/personas',
])

const mostrarBotonRegresar = computed(() => {
  const path = route.path.replace(/\/$/, '')
  return !RUTAS_PRINCIPALES.has(path) && window.history.length > 1
})

const regresarPagina = () => router.back()
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ══════════════════════════════════════
   VARIABLES GLOBALES
══════════════════════════════════════ */
.sistema-layout {
  --azul:           #1B396A;
  --azul-hover:     #1D4ED8;
  --azul-suave:     #DBEAFE;
  --azul-text:      #1B396A;
  --borde:          #E5E7EB;
  --fondo:          #F5F5F5;
  --blanco:         #FFFFFF;
  --texto:          #1A1A1A;
  --gris:           #6B7280;
  --header-h:       62px;
  --nav-h:          74px;
  --ribbon-h:       42px;           /* Se ajusta dinámicamente */
  --total-h:        calc(var(--header-h) + var(--nav-h));

  font-family: 'Montserrat', sans-serif;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background: var(--fondo);
}

/* ══════════════════════════════════════
   HEADER SUPERIOR
══════════════════════════════════════ */
.encabezado-superior {
  background: var(--azul);
  padding: 0 1.5rem;
  height: var(--header-h);
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 1000;
  box-shadow: 0 2px 12px rgba(0,0,0,0.2);
}
.encabezado-izquierda { display: flex; align-items: center; gap: 0.75rem; }
.logo-encabezado { height: 44px; filter: drop-shadow(0 0 6px rgba(255,255,255,0.8)); }
.titulo-sistema  { font-size: 0.98rem; font-weight: 700; color: #fff; letter-spacing: 0.01em; white-space: nowrap; }

/* Hamburguesa: solo visible en móvil */
.btn-hamburguesa {
  display: none;
  background: none; border: none; color: white;
  width: 36px; height: 36px;
  align-items: center; justify-content: center;
  cursor: pointer; border-radius: 6px; transition: background 0.2s; flex-shrink: 0;
}
.btn-hamburguesa svg { width: 22px; height: 22px; stroke: white; }
.btn-hamburguesa:hover { background: rgba(255,255,255,0.15); }

.encabezado-derecha { display: flex; align-items: center; gap: 1.5rem; height: 100%; }

/* Buscador */
.grupo-busqueda { position: relative; width: 280px; }
.grupo-busqueda input {
  width: 100%; padding: 8px 14px 8px 40px;
  border: none; border-radius: 8px;
  background: #fff; color: var(--texto);
  font-size: 0.88rem; font-family: 'Montserrat', sans-serif;
  box-shadow: 0 1px 6px rgba(0,0,0,0.1);
  outline: none; transition: box-shadow 0.2s; box-sizing: border-box;
}
.grupo-busqueda input::placeholder { color: #9CA3AF; }
.grupo-busqueda input:focus { box-shadow: 0 0 0 3px var(--azul-suave); }
.icono-busqueda {
  position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
  width: 16px; height: 16px; stroke: #6B7280; pointer-events: none;
}

/* Campana */
.campana-notificaciones {
  position: relative; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; border-radius: 8px; transition: background 0.2s;
}
.campana-notificaciones:hover { background: rgba(255,255,255,0.15); }
.icono-campana { width: 22px; height: 22px; stroke: white; }
.contador-notificaciones {
  position: absolute; top: -3px; right: -3px;
  background: #EF4444; color: white;
  font-size: 0.65rem; font-weight: 700;
  min-width: 16px; height: 16px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; padding: 0 3px;
}

/* Panel notificaciones */
.panel-notificaciones {
  position: absolute; top: calc(var(--header-h) - 10px); right: 0;
  width: 340px; background: #fff;
  border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.16);
  border: 1px solid var(--borde); overflow: hidden; z-index: 1100;
}
.panel-encabezado {
  padding: 12px 16px; background: #F8FAFC;
  border-bottom: 1px solid var(--borde);
  display: flex; justify-content: space-between; align-items: center;
}
.panel-encabezado h4 { margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--texto); }
.marcar-todo { font-size: 0.8rem; color: var(--azul); cursor: pointer; font-weight: 500; }
.lista-notificaciones { max-height: 300px; overflow-y: auto; }
.elemento-notificacion {
  display: flex; gap: 10px; padding: 12px 16px;
  border-bottom: 1px solid #F1F5F9; transition: background 0.15s;
}
.elemento-notificacion:hover { background: #F8FAFC; }
.icono-notif { width: 20px; height: 20px; stroke: var(--azul); flex-shrink: 0; }
.contenido-notif p { margin: 0; line-height: 1.4; color: var(--texto); font-size: 0.88rem; }
.tiempo-notif { font-size: 0.76rem; color: var(--gris); margin-top: 2px !important; }
.sin-notificaciones { padding: 40px 20px; text-align: center; color: var(--gris); }
.icono-vacio { width: 44px; height: 44px; stroke: #9CA3AF; margin-bottom: 10px; }
.titulo-vacio { font-size: 0.95rem; font-weight: 600; margin: 0 0 4px; }
.subtitulo-vacio { font-size: 0.82rem; margin: 0; }
.pie-notificaciones {
  padding: 10px 16px; text-align: center;
  color: var(--azul); font-weight: 600; font-size: 0.86rem;
  border-top: 1px solid var(--borde); cursor: pointer;
}

/* Menú usuario */
.menu-usuario {
  display: flex; align-items: center; gap: 7px;
  color: white; font-weight: 500; cursor: pointer;
  position: relative; padding: 5px 9px;
  border-radius: 8px; transition: background 0.2s;
}
.menu-usuario:hover { background: rgba(255,255,255,0.15); }
.icono-usuario { width: 22px; height: 22px; stroke: white; }
.nombre-usuario { font-size: 0.9rem; }
.flecha-desplegable { font-size: 0.65rem; opacity: 0.8; transition: transform 0.25s; }
.flecha-desplegable.rotada { transform: rotate(180deg); }
.desplegable-usuario {
  position: absolute; top: calc(100% + 6px); right: 0;
  background: #fff; border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.14);
  padding: 6px 0; min-width: 190px; z-index: 1100;
  border: 1px solid var(--borde);
}
.elemento-desplegable {
  display: flex; align-items: center; gap: 9px;
  padding: 9px 14px; cursor: pointer;
  color: var(--texto); font-size: 0.9rem; transition: background 0.15s;
}
.elemento-desplegable:hover { background: #F5F5F5; }
.icono-rol { width: 16px; height: 16px; stroke: var(--gris); flex-shrink: 0; }
.separador-desplegable { height: 1px; background: var(--borde); margin: 4px 0; }
.elemento-cerrar-sesion { color: #DC2626; }
.elemento-cerrar-sesion .icono-rol { stroke: #DC2626; }

/* ══════════════════════════════════════
   BARRA DE NAVEGACIÓN PRINCIPAL
   Módulos grandes
══════════════════════════════════════ */
.barra-nav-horizontal {
  position: fixed;
  top: var(--header-h);
  left: 0;
  right: 0;
  height: var(--nav-h);
  background: var(--blanco);
  border-bottom: 1px solid var(--borde);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  z-index: 995;
  overflow: visible;
}

.nav-scroll-inner {
  height: 100%;
  display: flex;
  align-items: stretch;
  gap: 2px;
  overflow-x: auto;
  overflow-y: hidden;
  padding: 0 1.4rem;
  scrollbar-width: none;
}

.nav-scroll-inner::-webkit-scrollbar {
  display: none;
}

.nav-item {
  position: relative;
  min-width: 82px;
  height: 100%;
  padding: 8px 12px 7px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  color: #4B5563;
  text-decoration: none;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  transition:
    color 0.16s ease,
    background 0.16s ease,
    border-color 0.16s ease;
  flex-shrink: 0;
  user-select: none;
}

.nav-item:hover {
  color: var(--azul);
  background: #F0F4FF;
}

.nav-item.nav-activo,
.nav-item.router-link-active {
  color: var(--azul);
  font-weight: 700;
  border-bottom-color: var(--azul);
  background: #EFF6FF;
}

.nav-item-tab.nav-activo {
  color: var(--azul);
  font-weight: 700;
  border-bottom-color: var(--azul);
  background: #EFF6FF;
}

.nav-icono {
  width: 23px;
  height: 23px;
  stroke: currentColor;
  flex-shrink: 0;
}

.nav-label {
  font-size: 0.76rem;
  font-weight: 600;
  line-height: 1.1;
  text-align: center;
  white-space: nowrap;
}

.nav-flecha {
  display: none;
}

/* Separador admin en la barra */
.nav-separador-admin {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 10px;
  flex-shrink: 0;
}

.nav-separador-admin-label {
  font-size: 0.62rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #9CA3AF;
  white-space: nowrap;
  padding: 3px 9px;
  background: #F3F4F6;
  border-radius: 999px;
}

/* ══════════════════════════════════════
   RIBBON PANEL (Nuevo)
══════════════════════════════════════ */
/* ══════════════════════════════════════
   SUBMENÚ CONTEXTUAL
   Recuadros compactos
══════════════════════════════════════ */
.ribbon-panel {
  position: fixed;
  top: var(--total-h);
  left: 0;
  right: 0;
  min-height: var(--ribbon-h);
  background: #F8FAFC;
  border-top: 1px solid #E5E7EB;
  border-bottom: 1px solid var(--borde);
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
  z-index: 990;

  display: flex;
  align-items: center;
  gap: 6px;
  padding: 5px 1.5rem;
  overflow-x: auto;
  overflow-y: hidden;
  white-space: nowrap;
}

.ribbon-panel::-webkit-scrollbar {
  height: 4px;
}

.ribbon-panel::-webkit-scrollbar-track {
  background: transparent;
}

.ribbon-panel::-webkit-scrollbar-thumb {
  background: #CBD5E1;
  border-radius: 999px;
}

.ribbon-item {
  height: 30px;
  min-width: auto;
  padding: 0 11px;
  border-radius: 7px;
  border: 1px solid transparent;
  background: transparent;

  display: inline-flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 7px;

  text-decoration: none;
  color: #374151;
  font-size: 0.78rem;
  font-weight: 500;
  line-height: 1;
  cursor: pointer;
  transition:
    background 0.15s ease,
    color 0.15s ease,
    border-color 0.15s ease,
    box-shadow 0.15s ease;
  flex-shrink: 0;
}

.ribbon-item:hover {
  background: #DBEAFE;
  color: var(--azul);
  border-color: #BFDBFE;
}

.ribbon-item.router-link-active {
  background: #EFF6FF;
  color: var(--azul);
  border-color: #BFDBFE;
  font-weight: 700;
  box-shadow: inset 0 0 0 1px rgba(27, 57, 106, 0.08);
}

.ribbon-icono-wrap {
  width: 18px;
  height: 18px;
  background: transparent;
  border-radius: 0;

  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.ribbon-icono-wrap svg {
  width: 15px;
  height: 15px;
  stroke: currentColor;
}

.ribbon-item:hover .ribbon-icono-wrap {
  background: transparent;
}

.ribbon-separador {
  width: 1px;
  height: 24px;
  background: #D1D5DB;
  margin: 0 5px;
  flex-shrink: 0;
}

/* ══════════════════════════════════════
   ÁREA DE CONTENIDO (con ribbon)
══════════════════════════════════════ */
.area-contenido {
  margin-top: var(--total-h);
  padding: 1.5rem 2rem;
  flex: 1;
  background: var(--fondo);
  min-height: calc(100vh - var(--total-h));
  box-sizing: border-box;
  transition: margin-top 0.18s ease;
}
.area-contenido.con-ribbon {
  margin-top: calc(var(--total-h) + var(--ribbon-h));
}

/* ══════════════════════════════════════
   DRAWER MÓVIL
══════════════════════════════════════ */
.drawer-movil {
  position: fixed;
  top: 0; left: 0; bottom: 0;
  width: 280px;
  background: #fff;
  z-index: 1200;
  overflow-y: auto;
  box-shadow: 4px 0 20px rgba(0,0,0,0.2);
  display: flex; flex-direction: column;
}
.drawer-encabezado {
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 16px;
  height: 60px;
  background: var(--azul);
  flex-shrink: 0;
}
.drawer-titulo { font-size: 1rem; font-weight: 700; color: white; }
.drawer-cerrar {
  background: none; border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 6px;
  transition: background 0.2s;
}
.drawer-cerrar svg { width: 20px; height: 20px; stroke: white; }
.drawer-cerrar:hover { background: rgba(255,255,255,0.15); }
.drawer-nav { flex: 1; padding: 8px 0; overflow-y: auto; }

.drawer-item {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 18px;
  font-size: 0.9rem; font-weight: 500;
  color: var(--texto); text-decoration: none;
  transition: background 0.15s; cursor: pointer;
}
.drawer-item:hover { background: #F3F4F6; }
.drawer-item.router-link-active { color: var(--azul); font-weight: 600; background: #EFF6FF; }

.drawer-grupo-titulo {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 18px;
  font-size: 0.9rem; font-weight: 500;
  color: var(--texto); cursor: pointer;
  transition: background 0.15s; user-select: none;
}
.drawer-grupo-titulo:hover { background: #F3F4F6; }
.drawer-icono { width: 18px; height: 18px; stroke: var(--gris); flex-shrink: 0; }
.drawer-grupo-titulo span { flex: 1; }
.drawer-flecha { width: 14px; height: 14px; stroke: var(--gris); transition: transform 0.22s; }
.drawer-flecha.rotada { transform: rotate(180deg); }

.drawer-submenu { background: #F9FAFB; }
.drawer-subitem {
  display: block;
  padding: 9px 18px 9px 46px;
  font-size: 0.84rem; font-weight: 400;
  color: #4B5563; text-decoration: none;
  transition: background 0.15s;
}
.drawer-subitem:hover { background: var(--azul-suave); color: var(--azul); }
.drawer-subitem.router-link-active { color: var(--azul); font-weight: 600; }
.drawer-subitem--anidado { padding-left: 60px; font-size: 0.81rem; color: var(--gris); }

.drawer-separador {
  padding: 10px 18px 4px; margin-top: 4px;
  border-top: 1px solid var(--borde);
}
.drawer-separador span {
  font-size: 0.68rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.08em; color: var(--gris);
}

/* Overlay */
.drawer-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.45);
  z-index: 1100;
}

/* Transiciones drawer */
.drawer-enter-active { transition: transform 0.28s ease; }
.drawer-leave-active { transition: transform 0.22s ease; }
.drawer-enter-from, .drawer-leave-to { transform: translateX(-100%); }

.overlay-enter-active { transition: opacity 0.25s ease; }
.overlay-leave-active { transition: opacity 0.2s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

/* ══════════════════════════════════════
   BOTÓN REGRESAR FLOTANTE (FAB)
══════════════════════════════════════ */
.fab-regresar {
  position: fixed; bottom: 1.5rem; left: 1.5rem; z-index: 1200;
  display: flex; align-items: center; justify-content: center;
  width: 44px; height: 44px; border-radius: 50%; border: none; cursor: pointer;
  background-color: var(--azul); color: #fff;
  box-shadow: 0 4px 14px rgba(27,57,106,0.45);
  opacity: 0.88; transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s;
}
.fab-regresar:hover { opacity: 1; transform: scale(1.08); box-shadow: 0 6px 20px rgba(27,57,106,0.6); }
.fab-regresar:active { transform: scale(0.93); }
.fab-regresar:focus-visible { outline: 3px solid var(--azul-suave); outline-offset: 3px; }
.fab-icono { width: 20px; height: 20px; pointer-events: none; flex-shrink: 0; }

.fab-back-enter-active, .fab-back-leave-active { transition: opacity 0.22s, transform 0.22s; }
.fab-back-enter-from, .fab-back-leave-to { opacity: 0; transform: scale(0.65) translateY(10px); }

/* ══════════════════════════════════════
   BARRA DE SCROLL GLOBAL
══════════════════════════════════════ */
* {
  scrollbar-width: thin;
  scrollbar-color: #1a3a5f #f1f5f9;
}
*::-webkit-scrollbar { width: 6px; height: 6px; }
*::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
*::-webkit-scrollbar-thumb { background: #1a3a5f; border-radius: 4px; }
*::-webkit-scrollbar-thumb:hover { background: #193d94; }

/* ══════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════ */

/* ── Tablet (≤1200px) ── */
@media (max-width: 1200px) {
  .sistema-layout {
    --nav-h: 68px;
    --ribbon-h: 40px;
  }

  .nav-item {
    min-width: 74px;
    padding: 7px 10px 6px;
  }

  .nav-icono {
    width: 21px;
    height: 21px;
  }

  .nav-label {
    font-size: 0.7rem;
  }

  .ribbon-item {
    height: 29px;
    padding: 0 9px;
    font-size: 0.74rem;
  }

  .grupo-busqueda {
    width: 200px;
  }
}

/* ── Tablet pequeña (≤1024px) ── */
@media (max-width: 1024px) {
  .titulo-sistema { font-size: 0.86rem; }
  .grupo-busqueda { width: 180px; }
  .encabezado-superior { padding: 0 1rem; }
  .encabezado-derecha { gap: 1rem; }
  .area-contenido { padding: 1.2rem 1.4rem; }
}

/* ── Móvil (≤768px) ── */
@media (max-width: 768px) {
  .sistema-layout { --header-h: 56px; --nav-h: 0px; }

  .btn-hamburguesa { display: flex; }
  .barra-nav-horizontal { display: none; }

  .titulo-sistema { font-size: 0; }
  .titulo-sistema::after {
    content: 'SICE';
    font-size: 1.05rem; font-weight: 800; letter-spacing: 0.12em; color: white;
  }

  .grupo-busqueda { width: 150px; }
  .grupo-busqueda input { font-size: 0.82rem; padding: 7px 10px 7px 32px; }
  .logo-encabezado { height: 38px; }

  .area-contenido {
    margin-top: var(--header-h);
    padding: 1rem;
    min-height: calc(100vh - var(--header-h));
  }

  .panel-notificaciones { width: 300px; right: -50px; }
  .nombre-usuario { display: none; }
  .flecha-desplegable { display: none; }
  .encabezado-derecha { gap: 0.6rem; }

  .fab-regresar { width: 46px; height: 46px; bottom: 1.2rem; left: 1rem; opacity: 1; }
  .fab-icono { width: 21px; height: 21px; }
}

/* ── Móvil pequeño (≤480px) ── */
@media (max-width: 480px) {
  .grupo-busqueda { width: 34px; overflow: hidden; }
  .grupo-busqueda input { opacity: 0; width: 0; padding: 0; pointer-events: none; position: absolute; }
  .grupo-busqueda:focus-within {
    width: 170px; position: fixed;
    top: 9px; left: 56px; right: 8px; z-index: 1100;
  }
  .grupo-busqueda:focus-within input {
    opacity: 1; width: 100%;
    padding: 7px 10px 7px 32px;
    pointer-events: auto; position: relative;
  }
  .encabezado-superior { padding: 0 0.7rem; }
  .panel-notificaciones { width: 270px; right: -70px; }
  .desplegable-usuario { right: -8px; min-width: 175px; }
  .fab-regresar { bottom: 0.9rem; left: 0.7rem; }
}

/* ══════════════════════════════════════
   ANTI-ZOOM & TIPOGRAFÍA RESPONSIVE
══════════════════════════════════════ */
html {
  font-size: 16px;
  -webkit-text-size-adjust: 100%;
  text-size-adjust: 100%;
  overflow-x: hidden;
}
button, input, select, textarea, a { min-height: 36px; font-size: 0.875rem; }
@media (max-width: 768px) { input, select, textarea { font-size: 16px !important; } }

table { width: 100%; border-collapse: collapse; }
.table-container, .tabla-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
img, svg, video { max-width: 100%; height: auto; }

p, span, td, th, label, li { font-size: clamp(0.8rem, 2vw, 1rem); line-height: 1.55; }
h1 { font-size: clamp(1.3rem, 4vw, 1.75rem); }
h2 { font-size: clamp(1.1rem, 3vw, 1.4rem); }
h3 { font-size: clamp(1rem, 2.5vw, 1.2rem); }

.modal-content, .modal-caja { max-width: 95vw; max-height: 90vh; overflow-y: auto; }
.kpi-card, .grafica-card, .panel-card, .form-card, .tabla-card { min-width: 0; overflow: hidden; }

@media (max-width: 768px) {
  .kpi-grid { grid-template-columns: repeat(2, 1fr) !important; }
  .fila-graficas, .fila-inferior { grid-template-columns: 1fr !important; }
  .form-row, .fila-campos { flex-direction: column !important; }
  .grilla-roles, .grilla-permisos, .grilla-acciones,
  .campos-grid-modal, .filtros-grid { grid-template-columns: 1fr !important; }
}

@media (max-width: 480px) {
  .kpi-grid { grid-template-columns: 1fr !important; }
  .form-grupo-doble { grid-template-columns: 1fr !important; }
  .filters-bar, .filtros-fila { flex-direction: column !important; align-items: stretch !important; }
  .filters-bar .btn-nuevo, .filters-bar .btn-limpiar { width: 100%; justify-content: center; }
  .paginacion { flex-direction: column !important; align-items: center !important; gap: 0.5rem !important; }
  .modal-footer, .modal-pie { flex-direction: column !important; gap: 0.5rem !important; }
  .modal-footer button, .modal-pie button { width: 100% !important; justify-content: center; }
}
</style>