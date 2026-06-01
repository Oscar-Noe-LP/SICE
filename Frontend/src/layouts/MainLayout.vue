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
        <div class="grupo-busqueda" @click.stop>
  <BuscadorGlobal tema="header" @seleccionar="onSeleccionarResultado" />
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
    <nav class="barra-nav-horizontal" @click.stop @mouseenter="cancelarCierreTab" @mouseleave="cerrarTabConRetardo" aria-label="Menú principal"
         :style="tabActivo && colorModuloActivo ? `--color-modulo:${colorModuloActivo.color};--color-modulo-bg:${colorModuloActivo.colorBg};--color-modulo-light:${colorModuloActivo.colorLight};--color-modulo-border:${colorModuloActivo.colorBorder}` : ''">
      <div class="nav-scroll-inner" ref="navScrollRef">

        <!-- Inicio (siempre visible) -->
        <router-link
          v-if="['admin', 'docente'].includes(rolActual)"
          to="/inicio"
          class="nav-item nav-item-link"
          active-class="nav-activo"
          :title="tooltips.inicio"
          @click="cerrarTab"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H5a1 1 0 01-1-1V9.5z" fill="rgba(22,163,74,.2)" stroke="#16a34a" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 21V12h6v9" stroke="#16a34a" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <span class="nav-label">INICIO</span>
        </router-link>

        <!-- ════════════════════════════════════════════ -->
        <!-- SI ES SERVICIOS ESCOLARES: TABS DIRECTOS  -->
        <!-- ════════════════════════════════════════════ -->
        <template v-if="rolActual === 'servicios-escolares'">
        
          <!-- DASHBOARD -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'se-dashboard' }"
            @mouseenter="abrirTab('se-dashboard')"
            @click.stop="abrirTab('se-dashboard')"
            tabindex="0"
            title="Dashboard — Panel principal de Servicios Escolares"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24">
              <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H5a1 1 0 01-1-1V9.5z" fill="rgba(22,163,74,.2)" stroke="#16a34a" stroke-width="1.8" stroke-linejoin="round"/>
              <path d="M9 21V12h6v9" stroke="#16a34a" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="nav-label">PRINCIPAL</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'se-dashboard' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        
          <!-- ALUMNOS -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'se-alumnos' }"
            @mouseenter="abrirTab('se-alumnos')"
            @click.stop="abrirTab('se-alumnos')"
            tabindex="0"
            title="Alumnos — Gestión y expedientes"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="7" r="4" fill="rgba(37,99,235,.2)" stroke="#2563eb" stroke-width="1.8"/><path d="M4 21c0-4 3.582-7 8-7s8 3 8 7" stroke="#2563eb" stroke-width="1.7" stroke-linecap="round"/></svg>
            <span class="nav-label">ALUMNOS</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'se-alumnos' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        
          <!-- INSCRIPCIONES -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'se-inscripciones' }"
            @mouseenter="abrirTab('se-inscripciones')"
            @click.stop="abrirTab('se-inscripciones')"
            tabindex="0"
            title="Inscripciones — Nueva, cargas e historial"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20" stroke="#7c3aed" stroke-width="1.7" stroke-linecap="round"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" fill="rgba(124,58,237,.2)" stroke="#7c3aed" stroke-width="1.7" stroke-linejoin="round"/><path d="M8 7h8M8 11h5" stroke="#7c3aed" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span class="nav-label">INSCRIPCIONES</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'se-inscripciones' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        
          <!-- CALIFICACIONES -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'se-calificaciones' }"
            @mouseenter="abrirTab('se-calificaciones')"
            @click.stop="abrirTab('se-calificaciones')"
            tabindex="0"
            title="Calificaciones — Captura, actas, especiales, residencias y analítica"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3" fill="rgba(217,119,6,.2)" stroke="#d97706" stroke-width="1.8"/><path d="M8 12h2.5l2-5 2 9 2-4h3.5" stroke="#d97706" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span class="nav-label">CALIFICACIONES</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'se-calificaciones' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        
          <!-- GRUPOS Y HORARIOS -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'se-grupos' }"
            @mouseenter="abrirTab('se-grupos')"
            @click.stop="abrirTab('se-grupos')"
            tabindex="0"
            title="Grupos y Horarios"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="3" fill="rgba(13,148,136,.2)" stroke="#0d9488" stroke-width="1.8"/><path d="M16 2v4M8 2v4M3 10h18" stroke="#0d9488" stroke-width="1.7" stroke-linecap="round"/><circle cx="8" cy="15" r="1.2" fill="#0d9488"/><circle cx="12" cy="15" r="1.2" fill="#0d9488"/><circle cx="16" cy="15" r="1.2" fill="#0d9488"/></svg>
            <span class="nav-label">GRUPOS</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'se-grupos' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        
          <!-- DOCUMENTOS -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'se-documentos' }"
            @mouseenter="abrirTab('se-documentos')"
            @click.stop="abrirTab('se-documentos')"
            tabindex="0"
            title="Documentos — Constancias, boletas, certificados y más"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" fill="rgba(234,88,12,.2)" stroke="#ea580c" stroke-width="1.8" stroke-linejoin="round"/><path d="M14 2v6h6M8 13h8M8 17h5" stroke="#ea580c" stroke-width="1.6" stroke-linecap="round"/></svg>
            <span class="nav-label">DOCUMENTOS</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'se-documentos' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg> 
          </div>
        
          <!-- EGRESADOS -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'se-egresados' }"
            @mouseenter="abrirTab('se-egresados')"
            @click.stop="abrirTab('se-egresados')"
            tabindex="0"
            title="Egresados — Posibles, titulados y registro"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><path d="M12 2L2 8l10 6 10-6-10-6z" fill="rgba(219,39,119,.2)" stroke="#db2777" stroke-width="1.8" stroke-linejoin="round"/><path d="M6 11.5V17c0 0 2 3 6 3s6-3 6-3v-5.5" stroke="#db2777" stroke-width="1.7" stroke-linecap="round"/><path d="M20 8v5" stroke="#db2777" stroke-width="1.7" stroke-linecap="round"/></svg>
            <span class="nav-label">EGRESADOS</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'se-egresados' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        
          <!-- ASPIRANTES -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'se-aspirantes' }"
            @mouseenter="abrirTab('se-aspirantes')"
            @click.stop="abrirTab('se-aspirantes')"
            tabindex="0"
            title="Aspirantes — Configuración, solicitudes y fichas"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" fill="rgba(147,51,234,.2)" stroke="#9333ea" stroke-width="1.8"/><path d="M4 21a8 8 0 0116 0" stroke="#9333ea" stroke-width="1.7" stroke-linecap="round"/><path d="M17 4l2-2M17 12l2 2M12 3V1" stroke="#9333ea" stroke-width="1.5" stroke-linecap="round"/></svg>
            <span class="nav-label">ASPIRANTES</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'se-aspirantes' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        
          <!-- CONFIGURACIÓN -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'se-configuracion' }"
            @mouseenter="abrirTab('se-configuracion')"
            @click.stop="abrirTab('se-configuracion')"
            tabindex="0"
            title="Configuración — Carreras, especialidades, plan curricular y periodos"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="rgba(71,85,105,.3)" stroke="#475569" stroke-width="1.8"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" fill="rgba(71,85,105,.12)" stroke="#475569" stroke-width="1.7"/></svg>
            <span class="nav-label">CONFIGURACIÓN</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'se-configuracion' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>
        
          <!-- PROCESOS -->
          <div
            class="nav-item nav-item-tab"
            :class="{ 'nav-activo': tabActivo === 'se-procesos' }"
            @mouseenter="abrirTab('se-procesos')"
            @click.stop="abrirTab('se-procesos')"
            tabindex="0"
            title="Procesos — Cierre de semestre y procesos especiales"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><path d="M4 4v5h5" stroke="#dc2626" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/><path d="M20 20v-5h-5" stroke="#dc2626" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/><path d="M20.49 9A9 9 0 005.64 5.64L4 9M4 15a9 9 0 0014.36 3.36L20 15" stroke="#dc2626" stroke-width="1.7" stroke-linecap="round"/></svg>
            <span class="nav-label">PROCESOS</span>
            <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'se-procesos' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>

          <!-- ── EVENTOS ── -->
          <template v-if="puedeVer.eventos">
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'eventos' }"
              @mouseenter="abrirTab('eventos')"
              @focus="abrirTab('eventos')"
              @click.stop="abrirTab('eventos')"
              tabindex="0"
              :title="tooltips.eventos"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span class="nav-label">EVENTOS</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'eventos' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </template>
        
        </template>

        <!-- ════════════════════════════════════════════ -->
        <!-- OTROS ROLES: COMPORTAMIENTO ORIGINAL       -->
        <!-- ════════════════════════════════════════════ -->
        <template v-else>
          <!-- ── ESCOLARES ── -->
          <template v-if="puedeVer.serviciosEscolares">
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'servicios' }"
              @mouseenter="abrirTab('servicios')"
              @focus="abrirTab('servicios')"
              @click.stop="abrirTab('servicios')"
              :title="tooltips.servicios"
              aria-haspopup="true"
              :aria-expanded="tabActivo === 'servicios'"
              tabindex="0"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H5a1 1 0 01-1-1V9.5z" fill="rgba(37,99,235,.2)" stroke="#2563eb" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 21V13h6v8" stroke="#2563eb" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span class="nav-label">ESCOLARES</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'servicios' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </template>

          <!-- ── GESTION ── -->
          <template v-if="puedeVer.gestionAcademica">
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'academica' }"
              @mouseenter="abrirTab('academica')"
              @focus="abrirTab('academica')"
              @click.stop="abrirTab('academica')"
              tabindex="0"
              :title="tooltips.academica"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><path d="M12 2L2 8l10 6 10-6-10-6z" fill="rgba(13,148,136,.2)" stroke="#0d9488" stroke-width="1.8" stroke-linejoin="round"/><path d="M6 11.5V17c0 0 2 3 6 3s6-3 6-3v-5.5" stroke="#0d9488" stroke-width="1.7" stroke-linecap="round"/><path d="M20 8v5" stroke="#0d9488" stroke-width="1.7" stroke-linecap="round"/></svg>
              <span class="nav-label">GESTION</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'academica' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </template>

          <!-- ── Carreras ── -->
          <template v-if="puedeVer.carreras">
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'carreras' }"
              @click.stop="toggleTab('carreras')"
              :title="tooltips.carreras"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20" stroke="#9333ea" stroke-width="1.7" stroke-linecap="round"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" fill="rgba(147,51,234,.2)" stroke="#9333ea" stroke-width="1.7" stroke-linejoin="round"/><path d="M8 7h8M8 11h5" stroke="#9333ea" stroke-width="1.5" stroke-linecap="round"/></svg>
              <span class="nav-label">Carreras</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'carreras' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </template>

          <!-- ── EVENTOS ── -->
          <template v-if="puedeVer.eventos">
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'eventos' }"
              @mouseenter="abrirTab('eventos')"
              @focus="abrirTab('eventos')"
              @click.stop="abrirTab('eventos')"
              tabindex="0"
              :title="tooltips.eventos"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span class="nav-label">EVENTOS</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'eventos' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </template>

          <!-- ── COMITE ── -->
          <template v-if="puedeVer.comite">
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'comite' }"
              @mouseenter="abrirTab('comite')"
              @focus="abrirTab('comite')"
              @click.stop="abrirTab('comite')"
              tabindex="0"
              :title="tooltips.comite"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
              <span class="nav-label">COMITE</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'comite' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </template>

          <!-- ══ ADMIN ══ -->
          <template v-if="rolActual === 'admin'">
            <div class="nav-separador-admin">
              <span class="nav-separador-admin-label">ADMIN</span>
            </div>

            <!-- SEGURIDAD -->
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'seguridad' }"
              @mouseenter="abrirTab('seguridad')"
              @focus="abrirTab('seguridad')"
              @click.stop="abrirTab('seguridad')"
              tabindex="0"
              :title="tooltips.seguridad"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              <span class="nav-label">SEGURIDAD</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'seguridad' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>

            <!-- RECURSOS -->
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'rrhh' }"
              @mouseenter="abrirTab('rrhh')"
              @focus="abrirTab('rrhh')"
              @click.stop="abrirTab('rrhh')"
              tabindex="0"
              :title="tooltips.rrhh"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="3" fill="rgba(234,88,12,.2)" stroke="#ea580c" stroke-width="1.8"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" stroke="#ea580c" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="14" r="2" fill="rgba(234,88,12,.4)" stroke="#ea580c" stroke-width="1.5"/></svg>
              <span class="nav-label">RH</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'rrhh' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>

            <!-- PERSONAS -->
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'personas' }"
              @mouseenter="abrirTab('personas')"
              @focus="abrirTab('personas')"
              @click.stop="abrirTab('personas')"
              tabindex="0"
              :title="tooltips.personas"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><circle cx="9" cy="7" r="3.5" fill="rgba(37,99,235,.2)" stroke="#2563eb" stroke-width="1.7"/><path d="M2 20c0-3.314 3.134-6 7-6" stroke="#2563eb" stroke-width="1.7" stroke-linecap="round"/><circle cx="16.5" cy="8" r="3" fill="rgba(37,99,235,.2)" stroke="#2563eb" stroke-width="1.5"/><path d="M13 20c0-2.762 2.462-5 5.5-5S24 17.238 24 20" stroke="#2563eb" stroke-width="1.7" stroke-linecap="round"/></svg>
              <span class="nav-label">PERSONAS</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'personas' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </template>

          <!-- ── ASIGNACION ── -->
          <template v-if="puedeVer.asignacionDocente">
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'asignacion' }"
              @mouseenter="abrirTab('asignacion')"
              @focus="abrirTab('asignacion')"
              @click.stop="abrirTab('asignacion')"
              tabindex="0"
              :title="tooltips.asignacion"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20" stroke="#7c3aed" stroke-width="1.7" stroke-linecap="round"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" fill="rgba(124,58,237,.2)" stroke="#7c3aed" stroke-width="1.7" stroke-linejoin="round"/><path d="M8 9h8M8 12h4" stroke="#7c3aed" stroke-width="1.5" stroke-linecap="round"/><path d="M14 15l2 2 3-3" stroke="#7c3aed" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span class="nav-label">ASIGNACION</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'asignacion' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </template>

          <!-- KARDEX E HISTORIAL SOLO ADMIN -->
          <template v-if="rolActual === 'admin'">
            <!-- KARDEX -->
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'kardex' }"
              @mouseenter="abrirTab('kardex')"
              @focus="abrirTab('kardex')"
              @click.stop="abrirTab('kardex')"
              tabindex="0"
              :title="tooltips.kardex"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" fill="rgba(22,163,74,.2)" stroke="#16a34a" stroke-width="1.8" stroke-linejoin="round"/><path d="M14 2v6h6M8 13h8M8 17h5" stroke="#16a34a" stroke-width="1.6" stroke-linecap="round"/></svg>
              <span class="nav-label">KARDEX</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'kardex' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>

            <!-- HISTORIAL -->
            <div
              class="nav-item nav-item-tab"
              :class="{ 'nav-activo': tabActivo === 'historial' }"
              @mouseenter="abrirTab('historial')"
              @focus="abrirTab('historial')"
              @click.stop="abrirTab('historial')"
              tabindex="0"
              :title="tooltips.historial"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="nav-icono" fill="none" viewBox="0 0 24 24"><rect x="3" y="7" width="5" height="10" rx="1" fill="rgba(71,85,105,.25)" stroke="#475569" stroke-width="1.6"/><rect x="9.5" y="5" width="5" height="14" rx="1" fill="rgba(71,85,105,.2)" stroke="#475569" stroke-width="1.6"/><rect x="16" y="3" width="5" height="16" rx="1" fill="rgba(71,85,105,.15)" stroke="#475569" stroke-width="1.6"/></svg>
              <span class="nav-label">HISTORIAL</span>
              <svg class="nav-flecha" :class="{ 'nav-flecha-activa': tabActivo === 'historial' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </template>
        </template> <!-- fin v-else otros roles -->

      </div>

      <!-- ══ RIBBON PANEL ══ -->
      <Transition name="ribbon">
        <div v-if="tabActivo" class="ribbon-panel" @click.stop
             :style="colorModuloActivo ? `border-bottom-color:${colorModuloActivo.color}` : ''">

          <!-- ── Servicios Escolares ── -->
          <template v-if="tabActivo === 'servicios'">
            <router-link v-if="puedeVerItem('/servicios-escolares')" to="/servicios-escolares" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              </div>
              <span>PRINCIPAL</span>
            </router-link>

            <router-link v-if="puedeVerItem('/alumnos')" to="/alumnos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <span>ALUMNOS</span>
            </router-link>

            <router-link v-if="puedeVerItem('/evaluaciones')" to="/evaluaciones" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
              </div>
              <span>EVALUACIONES</span>
            </router-link>

            <router-link v-if="puedeVerItem('/calificaciones')" to="/calificaciones" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </div>
              <span>CALIFICACIONES</span>
            </router-link>

            <router-link v-if="puedeVerItem('/inscripcion')" to="/inscripcion" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              </div>
              <span>INSCRIPCIÓN</span>
            </router-link>

            <router-link v-if="puedeVerItem('/gestion-grupos')" to="/gestion-grupos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              </div>
              <span>GRUPOS Y HORARIOS</span>
            </router-link>

            <template v-if="puedeVerItem('/inscripciones')">
            <router-link to="/inscripciones/nuevo-ingreso" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="ribbon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3M6 20a6 6 0 0112 0M12 12a4 4 0 100-8 4 4 0 000 8z"/>
                </svg>
              </div>
              <span>NUEVO INGRESO</span>
            </router-link>

            <router-link to="/inscripciones/nueva" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="ribbon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <span>NUEVA INSCRIPCIÓN</span>
            </router-link>

            <router-link to="/inscripciones/cargas" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="ribbon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                        d="M9 5h6M9 12h6M9 16h4M8 3h8a1 1 0 011 1v2h1a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h1V4a1 1 0 011-1z"/>
                </svg>
              </div>
              <span>CARGAS ACADÉMICAS</span>
            </router-link>

            <router-link to="/inscripciones/historial" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="ribbon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                        d="M12 8v5l3 2M21 12a9 9 0 11-3.3-6.96M21 4v5h-5"/>
                </svg>
              </div>
              <span>HISTORIAL</span>
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
            <router-link to="/gestion-academica/carreras" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              <span>Carreras</span>
            </router-link>
          </template>

          <!-- ── Carreras ── -->
          <template v-if="tabActivo === 'carreras'">
            <router-link to="/gestion-academica/carreras" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              <span>Lista de Carreras</span>
            </router-link>
            <router-link to="/analytics" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
              </div>
              <span>Analíticas</span>
            </router-link>
            <div class="ribbon-separador"></div>
            <router-link to="/carreras/1" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                </svg>
              </div>
              <span>Sistemas Comp.</span>
            </router-link>
            <router-link to="/carreras/2" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </div>
              <span>Ing. Industrial</span>
            </router-link>
            <router-link to="/carreras/3" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
              </div>
              <span>Ing. Eléctrica</span>
            </router-link>
            <router-link to="/carreras/4" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                </svg>
              </div>
              <span>Ing. Mecánica</span>
            </router-link>
            <router-link to="/carreras/5" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <span>Gest. Empresarial</span>
            </router-link>
            <router-link to="/carreras/6" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
              </div>
              <span>Bioquímica</span>
            </router-link>
          </template>

          <!-- ── Eventos ── -->
          <template v-if="tabActivo === 'eventos'">
            <router-link to="/eventos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              </div>
              <span>LISTA DE EVENTOS</span>
            </router-link>

            <router-link to="/eventos/nuevo" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
              </div>
              <span>NUEVO EVENTO</span>
            </router-link>
          </template>

          <!-- ── Comité Académico ── -->
          <template v-if="tabActivo === 'comite'">
            <router-link to="/comite" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              </div>
              <span>PANEL PRINCIPAL</span>
            </router-link>

            <router-link to="/comite/solicitudes" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
              </div>
              <span>SOLICITUDES</span>
            </router-link>

            <router-link to="/comite/solicitudes/nueva" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
              </div>
              <span>NUEVA SOLICITUD</span>
            </router-link>

            <router-link to="/comite/sesiones" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <span>SESIONES</span>
            </router-link>

            <router-link to="/comite/resoluciones" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <span>RESOLUCIONES</span>
            </router-link>
          </template>

          <!-- ── Seguridad y Usuarios ── -->
          <template v-if="tabActivo === 'seguridad'">
            <router-link to="/roles" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              </div>
              <span>ROLES</span>
            </router-link>

            <router-link to="/permisos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
              </div>
              <span>PERMISOS</span>
            </router-link>

            <router-link to="/usuarios" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <span>USUARIOS</span>
            </router-link>

            <router-link to="/bitacora" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              </div>
              <span>BITÁCORA</span>
            </router-link>

            <router-link to="/nuevo-usuario" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
              </div>
              <span>NUEVO USUARIO</span>
            </router-link>
          </template>

          <!-- ── Recursos Humanos ── -->
          <template v-if="tabActivo === 'rrhh'">
            <router-link to="/recursos-humanos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              </div>
              <span>PRINCIPAL</span>
            </router-link>

            <router-link to="/recursos-humanos/empleados" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <span>EMPLEADOS</span>
            </router-link>

            <router-link to="/recursos-humanos/docentes" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
              </div>
              <span>DOCENTES</span>
            </router-link>

            <router-link to="/recursos-humanos/adscripciones" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
              </div>
              <span>ADSCRIPCIONES</span>
            </router-link>

            <router-link to="/recursos-humanos/puestos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              </div>
              <span>PUESTOS</span>
            </router-link>

            <router-link to="/recursos-humanos/departamentos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              </div>
              <span>DEPARTAMENTOS</span>
            </router-link>
          </template>

          <!-- ── Personas ── -->
          <template v-if="tabActivo === 'personas'">
            <router-link to="/personas" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <span>CATÁLOGO</span>
            </router-link>

            <router-link to="/personas/nueva" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
              </div>
              <span>NUEVA PERSONA</span>
            </router-link>
          </template>

          <!-- ── Asignación Docente ── -->
          <template v-if="tabActivo === 'asignacion'">
            <router-link v-if="rolActual === 'admin'" to="/asignacion-docente" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
              </div>
              <span>ASIGNACIÓN DE GRUPOS</span>
            </router-link>

            <router-link to="/asignacion-docente/carga" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
              </div>
              <span>CARGA ACADÉMICA</span>
            </router-link>
          </template>

          <!-- ── Kardex ── -->
          <template v-if="tabActivo === 'kardex'">
            <router-link to="/kardex" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <span>CONSULTA DE KARDEX</span>
            </router-link>
          </template>

          <!-- ── Historial Académico ── -->
          <template v-if="tabActivo === 'historial'">
            <router-link to="/historial-academico" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              </div>
              <span>AVANCE CURRICULAR</span>
            </router-link>
          </template>

          <template v-if="tabActivo === 'se-dashboard'">
            <router-link to="/servicios-escolares" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              </div>
              <span>Inicio</span>
            </router-link>
            <router-link to="/servicios-escolares/graficas" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              </div>
              <span>Gráficas</span>
            </router-link>
          </template>
          
          <!-- ── SE: Alumnos ── -->
          <template v-if="tabActivo === 'se-alumnos'">
            <router-link to="/alumnos/gestion" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              <span>Gestión de Alumnos</span>
            </router-link>
            <router-link to="/alumnos/expediente" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <span>Expediente Académico</span>
            </router-link>
          </template>
          
          <!-- ── SE: INSCRIPCIONES ── -->
          <template v-if="tabActivo === 'se-inscripciones'">
            <router-link to="/inscripciones/nuevo-ingreso" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="ribbon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3M6 20a6 6 0 0112 0M12 12a4 4 0 100-8 4 4 0 000 8z"/>
                </svg>
              </div>
              <span>NUEVO INGRESO</span>
            </router-link>

            <router-link to="/inscripciones/nueva" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="ribbon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <span>NUEVA INSCRIPCIÓN</span>
            </router-link>

            <router-link to="/inscripciones/cargas" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="ribbon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                        d="M9 5h6M9 12h6M9 16h4M8 3h8a1 1 0 011 1v2h1a2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h1V4a1 1 0 011-1z"/>
                </svg>
              </div>
              <span>CARGAS ACADÉMICAS</span>
            </router-link>

            <router-link to="/inscripciones/historial" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="ribbon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                        d="M12 8v5l3 2M21 12a9 9 0 11-3.3-6.96M21 4v5h-5"/>
                </svg>
              </div>
              <span>HISTORIAL</span>
            </router-link>

            <router-link to="/gestion-grupos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="ribbon-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                        d="M8 3v4M16 3v4M4 9h16M6 5h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2zM8 13h3M13 13h3M8 17h3M13 17h3"/>
                </svg>
              </div>
              <span>GRUPOS Y HORARIOS</span>
            </router-link>
          </template>
          
          <!-- ── SE: Calificaciones ── -->
          <template v-if="tabActivo === 'se-calificaciones'">
            <router-link to="/calificaciones/captura" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </div>
              <span>Captura</span>
            </router-link>
            <router-link to="/calificaciones/residencias" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              </div>
              <span>Residencias</span>
            </router-link>
            <router-link to="/calificaciones/analitica" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              </div>
              <span>Analítica</span>
            </router-link>
          </template>
          
          <!-- ── SE: Grupos y Horarios ── -->
          <template v-if="tabActivo === 'se-grupos'">
            <router-link to="/gestion-grupos/lista" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              </div>
              <span>Por Carrera / Semestre / Grupo</span>
            </router-link>
            <router-link to="/gestion-grupos/detalle" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
              </div>
              <span>Detalle de Grupo</span>
            </router-link>
          </template>
          
          <!-- ── SE: Documentos ── -->
          <template v-if="tabActivo === 'se-documentos'">
            <router-link to="/documentos/constancias" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <span>Constancias</span>
            </router-link>
            <router-link to="/documentos/boletas" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
              </div>
              <span>Boletas</span>
            </router-link>
            <router-link to="/documentos/certificados" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
              </div>
              <span>Certificados</span>
            </router-link>
            <router-link to="/documentos/actas" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
              </div>
              <span>Actas</span>
            </router-link>
            <router-link to="/documentos/cargas" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              </div>
              <span>Cargas Académicas</span>
            </router-link>
            <router-link to="/documentos/residencia" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
              </div>
              <span>Acta de Residencia</span>
            </router-link>
          </template>
          
          <!-- ── SE: Egresados ── -->
          <template v-if="tabActivo === 'se-egresados'">
            <router-link to="/egresados/posibles" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <span>Posibles Egresados</span>
            </router-link>
            <router-link to="/egresados/lista" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
              </div>
              <span>Egresados</span>
            </router-link>
            <router-link to="/egresados/titulados" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <span>Titulados</span>
            </router-link>
            <router-link to="/egresados/registro" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              </div>
              <span>Registro de Titulados</span>
            </router-link>
          </template>
          
          <!-- ── SE: Aspirantes ── -->
          <template v-if="tabActivo === 'se-aspirantes'">
            <router-link to="/aspirantes/configuracion" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </div>
              <span>Configuración Inicial</span>
            </router-link>
          </template>
          
          <!-- ── SE: Configuración ── -->
          <template v-if="tabActivo === 'se-configuracion'">
            <router-link to="/configuracion/carreras" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              </div>
              <span>Carreras</span>
            </router-link>
            <router-link to="/configuracion/especialidades" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
              </div>
              <span>Especialidades</span>
            </router-link>
            <router-link to="/configuracion/plan-curricular" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
              </div>
              <span>Plan Curricular</span>
            </router-link>
            <router-link to="/configuracion/periodos" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              </div>
              <span>Periodos Escolares</span>
            </router-link>
          </template>
          
          <!-- ── SE: Procesos ── -->
          <template v-if="tabActivo === 'se-procesos'">
            <router-link to="/procesos/cierre" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7"/></svg>
              </div>
              <span>Cierre de Semestre</span>
            </router-link>
            <router-link to="/procesos/especiales" class="ribbon-item" @click="cerrarTab">
              <div class="ribbon-icono-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
              </div>
              <span>Procesos Especiales</span>
            </router-link>
          </template>
 
        </div>
      </Transition>
    </nav>

    <!-- ══ DRAWER MÓVIL ══ -->
    <Transition name="drawer">
      <aside v-if="drawerAbierto" ref="sidebarRef" class="drawer-movil" @click.stop
            :style="colorDrawerActivo ? `--color-modulo:${colorDrawerActivo.color};--color-modulo-bg:${colorDrawerActivo.colorBg};--color-modulo-light:${colorDrawerActivo.colorLight};--color-modulo-border:${colorDrawerActivo.colorBorder}` : ''">
        <div class="drawer-encabezado"
             :style="colorDrawerActivo ? `border-bottom: 3px solid ${colorDrawerActivo.color}` : ''">
          <span class="drawer-titulo">Menú</span>
          <button class="drawer-cerrar" @click="drawerAbierto = false" aria-label="Cerrar menú">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <nav class="drawer-nav">
          <router-link to="/inicio" class="drawer-item" @click="drawerAbierto = false">
            <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H5a1 1 0 01-1-1V9.5z" fill="rgba(22,163,74,.18)" stroke="#16a34a" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 21V13h6v8" stroke="#16a34a" stroke-width="1.7" stroke-linecap="round"/></svg>
            Inicio
          </router-link>

          <!-- Servicios Escolares (adaptado según rol) -->
          <template v-if="rolActual === 'servicios-escolares'">
            <router-link to="/servicios-escolares" class="drawer-item" @click="drawerAbierto = false">Principal</router-link>
            <router-link to="/alumnos" class="drawer-item" @click="drawerAbierto = false">Alumnos</router-link>
            <router-link to="/evaluaciones" class="drawer-item" @click="drawerAbierto = false">Evaluaciones</router-link>
            <router-link to="/calificaciones" class="drawer-item" @click="drawerAbierto = false">Calificaciones</router-link>
            <router-link to="/inscripcion" class="drawer-item" @click="drawerAbierto = false">Inscripción</router-link>
            <router-link to="/gestion-grupos" class="drawer-item" @click="drawerAbierto = false">Grupos y Horarios</router-link>
            <router-link to="/inscripciones" class="drawer-item" @click="drawerAbierto = false">Inscripciones Detalladas</router-link>
            <router-link to="/inscripciones/historial" class="drawer-item" @click="drawerAbierto = false">Historial Inscripciones</router-link>
          </template>

          <template v-else>
            <!-- Servicios Escolares (desplegable) -->
            <template v-if="puedeVer.serviciosEscolares">
              <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('servicios')">
                <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H5a1 1 0 01-1-1V9.5z" fill="rgba(37,99,235,.18)" stroke="#2563eb" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 21V13h6v8" stroke="#2563eb" stroke-width="1.7" stroke-linecap="round"/></svg>
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
                <router-link to="/gestion-academica/planes"          class="drawer-subitem" @click="drawerAbierto = false">Planes de Estudio</router-link>
                <router-link to="/gestion-academica/materias"        class="drawer-subitem" @click="drawerAbierto = false">Materias</router-link>
                <router-link to="/gestion-academica/prerrequisitos"  class="drawer-subitem" @click="drawerAbierto = false">Prerrequisitos</router-link>
                <router-link to="/gestion-academica/periodos"        class="drawer-subitem" @click="drawerAbierto = false">Periodos Académicos</router-link>
                <router-link to="/gestion-academica/edificios-aulas" class="drawer-subitem" @click="drawerAbierto = false">Edificios y Aulas</router-link>
                <router-link to="/gestion-academica/carreras"        class="drawer-subitem" @click="drawerAbierto = false">Carreras</router-link>
              </div>
            </template>

            <!-- Carreras -->
            <template v-if="puedeVer.carreras">
              <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('carreras')">
                <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20" stroke="#9333ea" stroke-width="1.7" stroke-linecap="round"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" fill="rgba(147,51,234,.18)" stroke="#9333ea" stroke-width="1.7" stroke-linejoin="round"/><path d="M8 7h8M8 11h5" stroke="#9333ea" stroke-width="1.5" stroke-linecap="round"/></svg>
                <span>Carreras</span>
                <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'carreras' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
              <div v-if="drawerSeccionAbierta === 'carreras'" class="drawer-submenu">
                <router-link to="/gestion-academica/carreras"   class="drawer-subitem" @click="drawerAbierto = false">Lista de Carreras</router-link>
                <router-link to="/analytics"  class="drawer-subitem" @click="drawerAbierto = false">Analíticas</router-link>
                <router-link to="/carreras/1" class="drawer-subitem drawer-subitem--anidado" @click="drawerAbierto = false">Sistemas Comp.</router-link>
                <router-link to="/carreras/2" class="drawer-subitem drawer-subitem--anidado" @click="drawerAbierto = false">Ing. Industrial</router-link>
                <router-link to="/carreras/3" class="drawer-subitem drawer-subitem--anidado" @click="drawerAbierto = false">Ing. Eléctrica</router-link>
                <router-link to="/carreras/4" class="drawer-subitem drawer-subitem--anidado" @click="drawerAbierto = false">Ing. Mecánica</router-link>
                <router-link to="/carreras/5" class="drawer-subitem drawer-subitem--anidado" @click="drawerAbierto = false">Gest. Empresarial</router-link>
                <router-link to="/carreras/6" class="drawer-subitem drawer-subitem--anidado" @click="drawerAbierto = false">Bioquímica</router-link>
              </div>
            </template>

            <!-- Eventos -->
            <template v-if="puedeVer.eventos">
              <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('eventos')">
                <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="3" fill="rgba(217,119,6,.18)" stroke="#d97706" stroke-width="1.8"/><path d="M16 2v4M8 2v4M3 10h18" stroke="#d97706" stroke-width="1.7" stroke-linecap="round"/><circle cx="8" cy="15" r="1.2" fill="#d97706"/><circle cx="12" cy="15" r="1.2" fill="#d97706"/><circle cx="16" cy="15" r="1.2" fill="#d97706"/></svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="3" fill="rgba(219,39,119,.18)" stroke="#db2777" stroke-width="1.8"/><path d="M8 9h8M8 13h5" stroke="#db2777" stroke-width="1.6" stroke-linecap="round"/></svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24"><path d="M12 2.944A11.955 11.955 0 0120.618 6.04 12.02 12.02 0 0121 9c0 5.591-3.824 10.29-9 11.622C6.824 19.29 3 14.591 3 9a12.02 12.02 0 01.382-3.016A11.955 11.955 0 0112 2.944z" fill="rgba(220,38,38,.18)" stroke="#dc2626" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke="#dc2626" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="3" fill="rgba(234,88,12,.18)" stroke="#ea580c" stroke-width="1.8"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" stroke="#ea580c" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="14" r="2" fill="rgba(234,88,12,.4)" stroke="#ea580c" stroke-width="1.5"/></svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24"><circle cx="9" cy="7" r="3.5" fill="rgba(37,99,235,.18)" stroke="#2563eb" stroke-width="1.7"/><path d="M2 20c0-3.314 3.134-6 7-6" stroke="#2563eb" stroke-width="1.7" stroke-linecap="round"/><circle cx="16.5" cy="8" r="3" fill="rgba(37,99,235,.15)" stroke="#2563eb" stroke-width="1.5"/><path d="M13 20c0-2.762 2.462-5 5.5-5S24 17.238 24 20" stroke="#2563eb" stroke-width="1.7" stroke-linecap="round"/></svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6z" fill="rgba(22,163,74,.18)" stroke="#16a34a" stroke-width="1.8" stroke-linejoin="round"/><path d="M14 2v6h6M8 13h8M8 17h5" stroke="#16a34a" stroke-width="1.6" stroke-linecap="round"/></svg>
                <span>Kardex</span>
                <svg class="drawer-flecha" :class="{ rotada: drawerSeccionAbierta === 'kardex' }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
              <div v-if="drawerSeccionAbierta === 'kardex'" class="drawer-submenu">
                <router-link to="/kardex" class="drawer-subitem" @click="drawerAbierto = false">Consulta de Kardex</router-link>
              </div>

              <div class="drawer-grupo-titulo" @click="toggleDrawerSeccion('historial')">
                <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24"><rect x="3" y="7" width="5" height="10" rx="1" fill="rgba(71,85,105,.25)" stroke="#475569" stroke-width="1.6"/><rect x="9.5" y="5" width="5" height="14" rx="1" fill="rgba(71,85,105,.2)" stroke="#475569" stroke-width="1.6"/><rect x="16" y="3" width="5" height="16" rx="1" fill="rgba(71,85,105,.15)" stroke="#475569" stroke-width="1.6"/></svg>
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
                <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icono" fill="none" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20" stroke="#7c3aed" stroke-width="1.7" stroke-linecap="round"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" fill="rgba(124,58,237,.18)" stroke="#7c3aed" stroke-width="1.7" stroke-linejoin="round"/><path d="M14 15l2 2 3-3" stroke="#7c3aed" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
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
import BuscadorGlobal from '@/components/search/BuscadorGlobal.vue'
import { useKeyboardShortcuts } from '@/composables/useKeyboardShortcuts'
useKeyboardShortcuts()

import { ref, computed, watch, onMounted, onUnmounted, onBeforeUnmount, nextTick, provide } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const tooltips = {
  inicio:     'Ir al inicio del sistema',
  servicios:  'Servicios Escolares → Alumnos, calificaciones, inscripciones y grupos',
  academica:  'Gestión Académica → Planes de estudio, materias, edificios y periodos',
  carreras:   'Carreras → Programas educativos, matrícula y estadísticas por carrera',
  eventos:    'Eventos → Consulta y crea eventos institucionales',
  comite:     'Comité Académico → Solicitudes, sesiones y resoluciones',
  seguridad:  'Seguridad → Roles, permisos, usuarios y bitácora',
  rrhh:       'Recursos Humanos → Empleados, docentes y departamentos',
  personas:   'Personas → Catálogo y registro de personas',
  asignacion: 'Asignación Docente → Grupos y carga académica',
  kardex:     'Kardex → Consulta el historial de calificaciones',
  historial:  'Historial Académico → Avance curricular del alumno',
  // Nuevos tooltips para Servicios Escolares
  se_principal:       'Servicios Escolares → Panel principal',
  se_alumnos:         'Servicios Escolares → Gestión de alumnos',
  se_evaluaciones:    'Servicios Escolares → Evaluaciones',
  se_calificaciones:  'Servicios Escolares → Captura de calificaciones',
  se_inscripcion:     'Servicios Escolares → Inscripción',
  se_grupos:          'Servicios Escolares → Grupos y horarios',
  se_inscripciones:   'Servicios Escolares → Inscripciones detalladas',
  se_historialinsc:   'Servicios Escolares → Historial de inscripciones',
}

// ── Color de identidad por módulo ─────────────────────────────────────
// Cada módulo tiene color (stroke principal), colorBg (relleno semitransparente 20%)
// y colorLight (hover suave para drawer subitems)
const COLORES_MODULO = {
  'inicio':           { color: '#16a34a', colorBg: 'rgba(22,163,74,.18)',  colorLight: '#f0fdf4', colorBorder: '#bbf7d0' },
  'servicios':        { color: '#2563eb', colorBg: 'rgba(37,99,235,.18)',  colorLight: '#eff6ff', colorBorder: '#bfdbfe' },
  'se-dashboard':     { color: '#16a34a', colorBg: 'rgba(22,163,74,.18)',  colorLight: '#f0fdf4', colorBorder: '#bbf7d0' },
  'se-alumnos':       { color: '#2563eb', colorBg: 'rgba(37,99,235,.18)',  colorLight: '#eff6ff', colorBorder: '#bfdbfe' },
  'se-inscripciones': { color: '#7c3aed', colorBg: 'rgba(124,58,237,.18)', colorLight: '#f5f3ff', colorBorder: '#ddd6fe' },
  'se-calificaciones':{ color: '#d97706', colorBg: 'rgba(217,119,6,.18)',  colorLight: '#fffbeb', colorBorder: '#fde68a' },
  'se-grupos':        { color: '#0d9488', colorBg: 'rgba(13,148,136,.18)', colorLight: '#f0fdfa', colorBorder: '#99f6e4' },
  'se-documentos':    { color: '#ea580c', colorBg: 'rgba(234,88,12,.18)',  colorLight: '#fff7ed', colorBorder: '#fed7aa' },
  'se-egresados':     { color: '#db2777', colorBg: 'rgba(219,39,119,.18)', colorLight: '#fdf2f8', colorBorder: '#fbcfe8' },
  'se-aspirantes':    { color: '#9333ea', colorBg: 'rgba(147,51,234,.18)', colorLight: '#faf5ff', colorBorder: '#e9d5ff' },
  'se-configuracion': { color: '#475569', colorBg: 'rgba(71,85,105,.18)',  colorLight: '#f8fafc', colorBorder: '#cbd5e1' },
  'se-procesos':      { color: '#dc2626', colorBg: 'rgba(220,38,38,.18)',  colorLight: '#fef2f2', colorBorder: '#fecaca' },
  'academica':        { color: '#0d9488', colorBg: 'rgba(13,148,136,.18)', colorLight: '#f0fdfa', colorBorder: '#99f6e4' },
  'carreras':         { color: '#9333ea', colorBg: 'rgba(147,51,234,.18)', colorLight: '#faf5ff', colorBorder: '#e9d5ff' },
  'eventos':          { color: '#d97706', colorBg: 'rgba(217,119,6,.18)',  colorLight: '#fffbeb', colorBorder: '#fde68a' },
  'comite':           { color: '#db2777', colorBg: 'rgba(219,39,119,.18)', colorLight: '#fdf2f8', colorBorder: '#fbcfe8' },
  'seguridad':        { color: '#dc2626', colorBg: 'rgba(220,38,38,.18)',  colorLight: '#fef2f2', colorBorder: '#fecaca' },
  'rrhh':             { color: '#ea580c', colorBg: 'rgba(234,88,12,.18)',  colorLight: '#fff7ed', colorBorder: '#fed7aa' },
  'personas':         { color: '#2563eb', colorBg: 'rgba(37,99,235,.18)',  colorLight: '#eff6ff', colorBorder: '#bfdbfe' },
  'asignacion':       { color: '#7c3aed', colorBg: 'rgba(124,58,237,.18)', colorLight: '#f5f3ff', colorBorder: '#ddd6fe' },
  'kardex':           { color: '#16a34a', colorBg: 'rgba(22,163,74,.18)',  colorLight: '#f0fdf4', colorBorder: '#bbf7d0' },
  'historial':        { color: '#475569', colorBg: 'rgba(71,85,105,.18)',  colorLight: '#f8fafc', colorBorder: '#cbd5e1' },
}

const colorModuloActivo = computed(() => {
  if (!tabActivo.value) return null
  return COLORES_MODULO[tabActivo.value] ?? null
})

// Color del drawer según la sección abierta
const colorDrawerActivo = computed(() => {
  if (!drawerSeccionAbierta.value) return null
  return COLORES_MODULO[drawerSeccionAbierta.value] ?? null
})

const router = useRouter()
const route  = useRoute()

// ── Buscador global → navegación a AlumnosView ────────────────────────
const resultadoBuscador = ref(null)
provide('resultadoBuscador', resultadoBuscador)

const onSeleccionarResultado = async (resultado) => {
  if (resultado.tipo === 'ALUMNO') {
    resultadoBuscador.value = null  // reset para que el watch dispare aunque sea el mismo
    if (route.name !== 'Alumnos') {
      await router.push({ name: 'Alumnos' })
    }
    nextTick(() => { resultadoBuscador.value = resultado })
  }
}

// ── Estado global ─────────────────────────────────────────────────────
const busquedaGlobal = ref('')

// ── Control de ventana ────────────────────────────────────────────────
const anchoVentana   = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)
const esMobil        = computed(() => anchoVentana.value <= 768)
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
  if (cerrarTabTimer) clearTimeout(cerrarTabTimer)
})

// ── Tab activo ────────────────────────────────────────────────────────
const tabActivo = ref(null)
let cerrarTabTimer = null

const abrirTab = (nombre) => {
  cancelarCierreTab()
  tabActivo.value = nombre
}

const toggleTab = (nombre) => {
  cancelarCierreTab()
  tabActivo.value = tabActivo.value === nombre ? null : nombre
}
const cerrarTab = () => {
  cancelarCierreTab()
  tabActivo.value = null
}

const cerrarTabConRetardo = () => {
  cancelarCierreTab()
  cerrarTabTimer = setTimeout(() => {
    tabActivo.value = null
  }, 180)
}

const cancelarCierreTab = () => {
  if (cerrarTabTimer) {
    clearTimeout(cerrarTabTimer)
    cerrarTabTimer = null
  }
}

// ── Drawer móvil ──────────────────────────────────────────────────────
const drawerAbierto        = ref(false)
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

// Normaliza el nombre_rol que viene de la BD al identificador interno del frontend.
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

const rolActual = computed(() => normalizarRol(usuarioLogueado.value?.rol ?? ''))

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
  'servicios-escolares': ['servicios-escolares', 'gestion-academica', 'carreras', 'eventos', 'comite'],
}

const puedeVer = computed(() => {
  const rol = rolActual.value
  if (rol === 'admin') return {
    serviciosEscolares: true,
    gestionAcademica:   true,
    carreras:           true,
    eventos:            true,
    comite:             true,
    asignacionDocente:  true,
  }
  const modulos = MODULOS_POR_ROL[rol] ?? []
  return {
    serviciosEscolares: modulos.includes('servicios-escolares'),
    gestionAcademica:   modulos.includes('gestion-academica'),
    carreras:           modulos.includes('carreras'),
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
  tabActivo.value             = null
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
  '/inicio',
  '/dashboard',
  '/servicios-escolares',
  '/alumnos',
  '/evaluaciones',
  '/calificaciones',
  '/inscripcion',
  '/inscripciones',
  '/inscripciones/nuevo-ingreso',
  '/inscripciones/nueva',
  '/inscripciones/cargas',
  '/inscripciones/historial',
  '/inscripciones/panel',
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
  '/gestion-academica/carreras',
])

const mostrarBotonRegresar = computed(() => {
  const path = route.path.replace(/\/$/, '')
  return !RUTAS_PRINCIPALES.has(path) && window.history.length > 1
})

const regresarPagina = () => router.back()
</script>

<!-- El bloque <style scoped> se mantiene exactamente igual al original -->
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
  --ribbon-h:       42px;
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
══════════════════════════════════════ */
.barra-nav-horizontal {
  position: fixed;
  top: var(--header-h);
  left: 0; right: 0;
  height: var(--nav-h);
  background: var(--blanco);
  border-bottom: 1px solid var(--borde);
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
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
.nav-scroll-inner::-webkit-scrollbar { display: none; }

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
  transition: color 0.16s ease, background 0.16s ease, border-color 0.16s ease;
  flex-shrink: 0;
  user-select: none;
}
.nav-item:hover { color: var(--color-modulo, var(--azul)); background: var(--color-modulo-light, #F0F4FF); }
.nav-item.nav-activo,
.nav-item.router-link-active { color: var(--color-modulo, var(--azul)); font-weight: 700; border-bottom-color: var(--color-modulo, var(--azul)); background: var(--color-modulo-bg, #EFF6FF); }
.nav-item-tab.nav-activo     { color: var(--color-modulo, var(--azul)); font-weight: 700; border-bottom-color: var(--color-modulo, var(--azul)); background: var(--color-modulo-bg, #EFF6FF); }

.nav-icono  { width: 26px; height: 26px; stroke: currentColor; flex-shrink: 0; }
.nav-label  { font-size: 0.76rem; font-weight: 600; line-height: 1.1; text-align: center; white-space: nowrap; }
.nav-flecha { display: none; }

.nav-separador-admin {
  display: flex; align-items: center; justify-content: center;
  padding: 0 10px; flex-shrink: 0;
}
.nav-separador-admin-label {
  font-size: 0.62rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.08em; color: #9CA3AF;
  white-space: nowrap; padding: 3px 9px;
  background: #F3F4F6; border-radius: 999px;
}

/* ══════════════════════════════════════
   ÁREA DE CONTENIDO
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
  padding: 0 16px; height: 60px;
  background: var(--azul); flex-shrink: 0;
}
.drawer-titulo { font-size: 1rem; font-weight: 700; color: white; }
.drawer-cerrar {
  background: none; border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 6px; transition: background 0.2s;
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
.drawer-item.router-link-active { color: var(--color-modulo, var(--azul)); font-weight: 600; background: var(--color-modulo-light, #EFF6FF); }

.drawer-grupo-titulo {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 18px;
  font-size: 0.9rem; font-weight: 500;
  color: var(--texto); cursor: pointer;
  transition: background 0.15s; user-select: none;
}
.drawer-grupo-titulo:hover { background: var(--color-modulo-light, #F3F4F6); }
.drawer-grupo-titulo:hover .drawer-icono { stroke: var(--color-modulo, var(--gris)); }
.drawer-icono  { width: 18px; height: 18px; stroke: var(--gris); flex-shrink: 0; transition: stroke 0.2s; }
.drawer-grupo-titulo span { flex: 1; }
.drawer-flecha { width: 14px; height: 14px; stroke: var(--gris); transition: transform 0.22s; }
.drawer-flecha.rotada { transform: rotate(180deg); }

.drawer-submenu { background: var(--color-modulo-light, #F9FAFB); border-left: 3px solid var(--color-modulo, transparent); margin-left: 0; }
.drawer-subitem {
  display: block;
  padding: 9px 18px 9px 46px;
  font-size: 0.84rem; font-weight: 400;
  color: #4B5563; text-decoration: none;
  transition: background 0.15s;
}
.drawer-subitem:hover { background: var(--color-modulo-light, var(--azul-suave)); color: var(--color-modulo, var(--azul)); }
.drawer-subitem.router-link-active { color: var(--color-modulo, var(--azul)); font-weight: 600; }
.drawer-subitem--anidado { padding-left: 60px; font-size: 0.81rem; color: var(--gris); }

.drawer-separador { padding: 10px 18px 4px; margin-top: 4px; border-top: 1px solid var(--borde); }
.drawer-separador span { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--gris); }

/* Overlay */
.drawer-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1100; }

/* Transiciones drawer */
.drawer-enter-active  { transition: transform 0.28s ease; }
.drawer-leave-active  { transition: transform 0.22s ease; }
.drawer-enter-from, .drawer-leave-to { transform: translateX(-100%); }

.overlay-enter-active { transition: opacity 0.25s ease; }
.overlay-leave-active { transition: opacity 0.2s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

/* Ribbon transition */
.ribbon-enter-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.ribbon-leave-active { transition: opacity 0.14s ease, transform 0.14s ease; }
.ribbon-enter-from, .ribbon-leave-to { opacity: 0; transform: translateY(-6px); }

/* ══════════════════════════════════════
   BOTÓN REGRESAR FLOTANTE
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
* { scrollbar-width: thin; scrollbar-color: #1a3a5f #f1f5f9; }
*::-webkit-scrollbar { width: 6px; height: 6px; }
*::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
*::-webkit-scrollbar-thumb { background: #1a3a5f; border-radius: 4px; }
*::-webkit-scrollbar-thumb:hover { background: #193d94; }

/* ══════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════ */
@media (max-width: 1200px) {
  .sistema-layout { --nav-h: 68px; --ribbon-h: 40px; }
  .nav-item { min-width: 74px; padding: 7px 10px 6px; }
  .nav-icono { width: 21px; height: 21px; }
  .nav-label { font-size: 0.7rem; }
  .ribbon-item { height: 29px; padding: 0 9px; font-size: 0.74rem; }
  .grupo-busqueda { width: 200px; }
}

@media (max-width: 1024px) {
  .titulo-sistema { font-size: 0.86rem; }
  .grupo-busqueda { width: 180px; }
  .encabezado-superior { padding: 0 1rem; }
  .encabezado-derecha { gap: 1rem; }
  .area-contenido { padding: 1.2rem 1.4rem; }
}

@media (max-width: 768px) {
  .sistema-layout { --header-h: 56px; --nav-h: 0px; }
  .btn-hamburguesa { display: flex; }
  .barra-nav-horizontal { display: none; }
  .titulo-sistema { font-size: 0; }
  .titulo-sistema::after { content: 'SICE'; font-size: 1.05rem; font-weight: 800; letter-spacing: 0.12em; color: white; }
  .grupo-busqueda { width: 150px; }
  .grupo-busqueda input { font-size: 0.82rem; padding: 7px 10px 7px 32px; }
  .logo-encabezado { height: 38px; }
  .area-contenido { margin-top: var(--header-h); padding: 1rem; min-height: calc(100vh - var(--header-h)); }
  .panel-notificaciones { width: 300px; right: -50px; }
  .nombre-usuario { display: none; }
  .flecha-desplegable { display: none; }
  .encabezado-derecha { gap: 0.6rem; }
  .fab-regresar { width: 46px; height: 46px; bottom: 1.2rem; left: 1rem; opacity: 1; }
  .fab-icono { width: 21px; height: 21px; }
}

@media (max-width: 480px) {
  .grupo-busqueda { width: 34px; overflow: hidden; }
  .grupo-busqueda input { opacity: 0; width: 0; padding: 0; pointer-events: none; position: absolute; }
  .grupo-busqueda:focus-within { width: 170px; position: fixed; top: 9px; left: 56px; right: 8px; z-index: 1100; }
  .grupo-busqueda:focus-within input { opacity: 1; width: 100%; padding: 7px 10px 7px 32px; pointer-events: auto; position: relative; }
  .encabezado-superior { padding: 0 0.7rem; }
  .panel-notificaciones { width: 270px; right: -70px; }
  .desplegable-usuario { right: -8px; min-width: 175px; }
  .fab-regresar { bottom: 0.9rem; left: 0.7rem; }
}

/* ══════════════════════════════════════
   ANTI-ZOOM & TIPOGRAFÍA
══════════════════════════════════════ */
html { font-size: 16px; -webkit-text-size-adjust: 100%; text-size-adjust: 100%; overflow-x: hidden; }
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

/* ══════════════════════════════════════
   AJUSTE FINAL RIBBON
══════════════════════════════════════ */
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
  transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
  flex-shrink: 0;
  text-transform: uppercase;
}

.ribbon-item:hover {
  background: var(--color-modulo-light, #DBEAFE);
  color: var(--color-modulo, var(--azul));
  border-color: var(--color-modulo-border, #BFDBFE);
}

.ribbon-item.router-link-active,
.ribbon-item.router-link-exact-active {
  background: var(--color-modulo-light, #EFF6FF);
  color: var(--color-modulo, var(--azul));
  border-color: var(--color-modulo-border, #BFDBFE);
  font-weight: 700;
  box-shadow: inset 0 0 0 1px rgba(27,57,106,0.08);
}

.ribbon-icono-wrap {
  width: 18px;
  min-width: 18px;
  height: 18px;
  min-height: 18px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  line-height: 1;
}

.ribbon-icono-wrap svg,
.ribbon-icon {
  display: block;
  width: 15px;
  min-width: 15px;
  height: 15px;
  min-height: 15px;
  max-width: none;
  stroke: currentColor;
  fill: none;
  flex-shrink: 0;
}

.ribbon-item span {
  font-family: 'Montserrat', sans-serif;
  font-size: 0.78rem !important;
  font-weight: 500;
  line-height: 1 !important;
  color: inherit;
  white-space: nowrap;
  text-transform: uppercase;
}

.ribbon-item.router-link-active span,
.ribbon-item.router-link-exact-active span {
  font-weight: 700;
}
</style>