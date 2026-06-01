<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="page">

      <!-- ── BREADCRUMB ── -->
      <div class="bc">
        <router-link to="/inicio" class="bc-lnk">Inicio</router-link>
        <span class="bc-sep">›</span>
        <span class="bc-cur">Servicios Escolares</span>
      </div>

      <!-- ── TOAST ── -->
      <Transition name="toast-slide">
        <div v-if="notif.visible" class="toast" :class="notif.tipo" role="alert">
          <!-- ✅ EXITO -->
          <svg v-if="notif.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <!-- ❌ ERROR -->
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none"
            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ notif.mensaje }}
        </div>
      </Transition>

      <!-- ══════════════════════════════════════════════════
           HERO BANNER — fondo #0B2545, respeta maqueta
      ══════════════════════════════════════════════════ -->
      <div class="hero">
        <div class="hero-deco"  aria-hidden="true"></div>
        <div class="hero-deco2" aria-hidden="true"></div>

        <div class="hero-left">
          <!-- Badge superior -->
          <div class="hero-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
            </svg>
            Servicios Escolares
          </div>

          <div class="hero-title">Consulta de alumnos</div>
          <div class="hero-sub">Busca por número de control para acceder al expediente completo del alumno</div>

          <!-- Formulario de búsqueda -->
          <div class="hero-form">
            <div class="hero-input-wrap"
              :class="{ 'hi-focus': inputFocused, 'hi-error': estadoBusqueda === 'error' }">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2" class="hi-lupa" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input
                ref="inputRef"
                v-model="numCtrl"
                class="hi-inp"
                type="text"
                placeholder="Número de control (ej. 22031234)"
                maxlength="8"
                inputmode="numeric"
                aria-label="Número de control del alumno"
                @keydown.enter="buscar"
                @focus="inputFocused = true"
                @blur="inputFocused = false"
                @input="onInput"
              />
              <!-- Spinner de carga -->
              <div v-if="estadoBusqueda === 'cargando'" class="hi-spinner" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2" class="spin">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16 8 8 0 01-8-8z"/>
                </svg>
              </div>
              <!-- Botón limpiar -->
              <button v-else-if="numCtrl" class="hi-clear" @click="limpiar" type="button" aria-label="Limpiar">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- Botón buscar — color #1D52B7 según PDF -->
            <button
              class="hero-btn"
              :disabled="estadoBusqueda === 'cargando' || !numCtrl.trim()"
              @click="buscar"
              type="button"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              {{ estadoBusqueda === 'cargando' ? 'Buscando...' : 'Buscar' }}
            </button>
          </div>

          <!-- Hints de estado -->
          <Transition name="fade-slide">
            <p v-if="estadoBusqueda === 'error'" class="hero-hint hint-err">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ mensajeError }}
            </p>
            <p v-else-if="estadoBusqueda === 'no-encontrado'" class="hero-hint hint-warn">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              No se encontró ningún alumno con el número <strong>{{ ultimaBusq }}</strong>.
            </p>
            <p v-else class="hero-hint">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Ingresa los 8 dígitos del número de control y presiona Enter o el botón Buscar
            </p>
          </Transition>
        </div>

        <!-- Stats del hero -->
        <div class="hero-stats">
          <div class="hstat">
            <div class="hstat-n">{{ fmt(kpis.alumnosActivos) }}</div>
            <div class="hstat-l">Alumnos activos</div>
          </div>
          <div class="hstat">
            <div class="hstat-n">Ene–Jun</div>
            <div class="hstat-l">Periodo 2025</div>
          </div>
        </div>
      </div>

      <!-- (el resultado ahora se muestra en ventana flotante vía Teleport — ver al final del template) -->

      <!-- ══════════════════════════════════════════════════
           DASHBOARD — visible cuando NO hay alumno activo
           Sigue exactamente el layout de maq_escolares.html
      ══════════════════════════════════════════════════ -->
      <Transition name="fade">
        <div v-if="!alumno || estadoBusqueda !== 'exito'" class="dash">

          <!-- KPI GRID (4 columnas, colores del PDF) -->
          <div class="kpi-grid">

            <!-- KPI 1: Alumnos Activos — featured: fondo #0B2545 -->
            <div class="kpi kpi-feat" @click="router.push('/alumnos')" role="button" tabindex="0">
              <div class="kpi-ico ki-bw" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
              </div>
              <div class="kpi-body">
                <div class="kpi-lbl">Alumnos Activos</div>
                <div v-if="cargando" class="kpi-sk kpi-sk-d"></div>
                <div v-else class="kpi-val">{{ fmt(kpis.alumnosActivos) }}</div>
                <div class="kpi-lnk">
                  Ver alumnos →
                </div>
              </div>
            </div>

            <!-- KPI 2: Inscripciones — ícono verde #27AE60 -->
            <div class="kpi" @click="router.push('/inscripciones')" role="button" tabindex="0">
              <div class="kpi-ico ki-g" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
              </div>
              <div class="kpi-body">
                <div class="kpi-lbl">Inscripciones del Periodo</div>
                <div v-if="cargando" class="kpi-sk"></div>
                <div v-else class="kpi-val">{{ fmt(kpis.inscripcionesPeriodo) }}</div>
                <div class="kpi-lnk">
                  Ver inscripciones →
                </div>
              </div>
            </div>

            <!-- KPI 3: Grupos — ícono naranja #F2994A -->
            <div class="kpi" @click="router.push('/gestion-grupos')" role="button" tabindex="0">
              <div class="kpi-ico ki-a" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
              </div>
              <div class="kpi-body">
                <div class="kpi-lbl">Grupos Abiertos</div>
                <div v-if="cargando" class="kpi-sk"></div>
                <div v-else class="kpi-val">{{ kpis.gruposAbiertos }}</div>
                <div class="kpi-lnk">
                  Ver grupos →
                </div>
              </div>
            </div>

            <!-- KPI 4: Evaluaciones — ícono azul oscuro #1A4184 -->
            <div class="kpi" @click="router.push('/evaluaciones')" role="button" tabindex="0">
              <div class="kpi-ico ki-v" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
              </div>
              <div class="kpi-body">
                <div class="kpi-lbl">Evaluaciones Pendientes</div>
                <div v-if="cargando" class="kpi-sk"></div>
                <div v-else class="kpi-val">{{ kpis.evaluacionesPendientes }}</div>
                <div class="kpi-lnk">
                  Ver evaluaciones →
                </div>
              </div>
            </div>

          </div>

          <!-- MAIN GRID: Accesos rápidos + Actividad reciente -->
          <div class="main-grid">

            <!-- Accesos Rápidos -->
            <div class="card">
              <div class="card-h">
                <!-- CAMBIO: card-t ahora es 16px (maqueta usa font-size:16px, el .vue tenía 14px) -->
                <span class="card-t">Accesos Rápidos</span>
                <span class="card-lk">Ver todos</span>
              </div>
              <div class="acc-list">

                <div class="acc-item" @click="router.push('/inscripcion')" role="button" tabindex="0">
                  <div class="acc-ico ai-g" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                  </div>
                  <div class="acc-body">
                    <div class="acc-t">Gestionar Inscripciones</div>
                    <div class="acc-d">Da de alta a los alumnos al periodo actual</div>
                  </div>
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2" class="acc-arrow">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                </div>

                <div class="acc-item" @click="router.push('/gestion-grupos')" role="button" tabindex="0">
                  <div class="acc-ico ai-b" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                  </div>
                  <div class="acc-body">
                    <div class="acc-t">Administrar Grupos</div>
                    <div class="acc-d">Visualiza y organiza grupos por carrera y turno</div>
                  </div>
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2" class="acc-arrow">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                </div>

                <div class="acc-item" @click="router.push('/evaluaciones')" role="button" tabindex="0">
                  <div class="acc-ico ai-a" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                  </div>
                  <div class="acc-body">
                    <div class="acc-t">Supervisar Evaluaciones</div>
                    <div class="acc-d">Revisa evaluaciones pendientes y actas de calificación</div>
                  </div>
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2" class="acc-arrow">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                </div>

                <!-- Próximamente — opacidad 0.55, no clickeable -->
                <div class="acc-item prox">
                  <div class="acc-ico ai-gr" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                  </div>
                  <div class="acc-body">
                    <div class="acc-t">Expediente Académico</div>
                    <div class="acc-d">Consulta el historial académico completo</div>
                  </div>
                  <span class="prox-bdg">Próximamente</span>
                </div>

                <div class="acc-item" @click="router.push('/kardex')" role="button" tabindex="0">
                  <div class="acc-ico ai-g" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                    </svg>
                  </div>
                  <div class="acc-body">
                    <div class="acc-t">Kardex del Alumno</div>
                    <div class="acc-d">Historial de calificaciones por semestre</div>
                  </div>
                  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2" class="acc-arrow">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                </div>

              </div>
            </div>

            <!-- Actividad Reciente (Bitácora) -->
            <div class="card">
              <div class="card-h">
                <span class="card-t">Actividad Reciente</span>
                <router-link to="/bitacora" class="card-lk">
                  <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                  </svg>
                  Ver bitácora
                </router-link>
              </div>
              <div class="bit-list">
                <div v-if="cargandoBit" class="bit-load">
                  <div class="spinner" aria-hidden="true"></div>
                  <span>Cargando...</span>
                </div>
                <template v-else-if="bitacora.length > 0">
                  <div v-for="(item, i) in bitacora" :key="item.id_bitacora || i" class="bit-item">
                    <div class="bit-av" aria-hidden="true">
                      {{ (item.usuario || item.nombre_usuario || '?').slice(0,2).toUpperCase() }}
                    </div>
                    <div class="bit-body">
                      <div class="bit-r1">
                        <span class="bit-usr">{{ item.usuario || item.nombre_usuario }}</span>
                        <span class="bdg" :class="claseBadge(item.accion)">{{ item.accion }}</span>
                      </div>
                      <div class="bit-desc">{{ item.accion }}</div>
                      <div class="bit-t">{{ tRel(item.fecha_hora) }} · {{ item.nombre_modulo || 'Sistema' }}</div>
                    </div>
                  </div>
                </template>
                <div v-else class="ev">Sin actividad reciente</div>
              </div>
            </div>

          </div>

          <!-- BOTTOM GRID: 3 Gráficas — idénticas a maq_escolares.html -->
          <div class="bottom-grid">

            <!-- Gráfica 1: Barras horizontales — Alumnos por carrera -->
            <!-- Colores exactos del PDF: #132B4F, #1A4184, #1D52B7, #2F80ED, #27AE60, #F2994A -->
            <div class="card">
              <div class="card-h">
                <span class="card-t">Alumnos por carrera</span>
                <span class="card-lk">
                  <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                  </svg>
                  Detalle
                </span>
              </div>
              <!-- CAMBIO: wrapper con height explícito para que maintainAspectRatio:false funcione -->
              <div class="chart-wrap">
                <canvas ref="c1" role="img" aria-label="Alumnos por carrera"></canvas>
              </div>
            </div>

            <!-- Gráfica 2: Línea — Inscripciones por semestre -->
            <!-- Color línea: #1D52B7 (Azul Rey), fill rgba(29,82,183,0.07) -->
            <div class="card">
              <div class="card-h">
                <span class="card-t">Inscripciones por semestre</span>
                <span class="card-lk">
                  <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                  </svg>
                  Analítica
                </span>
              </div>
              <div class="chart-wrap">
                <canvas ref="c2" role="img" aria-label="Inscripciones por semestre"></canvas>
              </div>
            </div>

            <!-- Gráfica 3: Donut — Estado de inscripciones -->
            <!-- cutout 72%, color principal #1D52B7, pendientes rgba(242,153,74,0.15) -->
            <div class="card">
              <div class="card-h">
                <span class="card-t">Estado de inscripciones</span>
                <!-- CAMBIO: badge usa clase bg-g igual que la maqueta (Periodo activo) -->
                <span class="bdg bg-g" style="font-size:9px;font-weight:700">Periodo activo</span>
              </div>
              <div class="chart-wrap chart-wrap--sm">
                <canvas ref="c3" role="img" aria-label="Porcentaje de inscripciones completadas vs pendientes"></canvas>
              </div>
              <!-- Contadores debajo del donut -->
              <div class="ins-mini" style="margin-top:12px">
                <div class="im">
                  <div class="im-v">{{ fmt(kpis.inscripcionesCompletas) }}</div>
                  <div class="im-l">Completadas</div>
                </div>
                <div class="im">
                  <div class="im-v" style="color:#F2994A">{{ fmt(kpis.inscripcionesPendientes) }}</div>
                  <div class="im-l">Pendientes</div>
                </div>
              </div>
            </div>

          </div>

          <!-- Alerta de error de carga -->
          <div v-if="errorCarga" class="alerta-err" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24"
              stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>No se pudieron cargar los datos. Verifica la conexión con el servidor.</span>
            <button class="btn-reintentar" @click="cargarDatos" type="button">Reintentar</button>
          </div>

        </div>
      </Transition>

      <div class="spacer"></div>
      <footer class="pie">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>

    </div>

    <!-- ══════════════════════════════════════════════════════════════
         MODAL EXPEDIENTE — idéntico a AlumnosSE, se abre al buscar
    ══════════════════════════════════════════════════════════════ -->
    <Teleport to="body">
    <transition name="modal-fade">
      <div v-if="showViewModal && alumno" class="se-modal-overlay" @click.self="cerrarModal" role="dialog" aria-modal="true" aria-label="Expediente del alumno">
        <div class="se-modal-content">

          <!-- ── Header ── -->
          <div class="se-modal-header">
            <div class="se-modal-avatar-group">
              <div class="se-avatar" :class="`avatar-${colorIndex(_resolverNombre(alumno))}`" aria-hidden="true">
                <span>{{ iniciales(_resolverNombre(alumno)) }}</span>
              </div>
              <div class="se-modal-header-info">
                <span class="se-modal-tag">EXPEDIENTE ACADÉMICO</span>
                <h3 class="se-modal-nombre">{{ _resolverNombre(alumno).toUpperCase() }}</h3>
                <span class="se-modal-control">{{ alumno.numero_control || alumno.noControl }}</span>
              </div>
            </div>
            <button @click="cerrarModal" class="se-btn-cerrar" title="CERRAR" aria-label="Cerrar expediente">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="22" height="22" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <!-- ── Pestañas ── -->
          <div class="se-modal-body-tabs">
            <div class="se-detalle-tabs" role="tablist">
              <button v-for="tab in tabs" :key="tab.id"
                class="se-tab-btn" :class="{ activo: tabActivo === tab.id }"
                @click="tabActivo = tab.id"
                role="tab" :aria-selected="tabActivo === tab.id"
              >
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon"/>
                </svg>
                {{ tab.label }}
              </button>
            </div>

            <div class="se-tab-scroll">

              <!-- ══ TAB: DATOS GENERALES ══ -->
              <div v-if="tabActivo === 'general'" class="se-tab-panel">

                <div class="se-exp-seccion">
                  <div class="se-exp-titulo">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                      <circle cx="12" cy="7" r="4"/>
                    </svg>
                    DATOS PERSONALES
                  </div>
                  <div class="se-detalle-grid">
                    <div class="se-campo">
                      <span class="se-label">NO. DE CONTROL</span>
                      <span class="se-valor se-mono">{{ alumno.numero_control || alumno.noControl || '—' }}</span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">CURP</span>
                      <span class="se-valor se-mono">{{ alumno.curp || alumno.persona?.curp || 'PENDIENTE DE REGISTRO' }}</span>
                    </div>
                    <div class="se-campo se-full">
                      <span class="se-label">NOMBRE COMPLETO</span>
                      <span class="se-valor">{{ _resolverNombre(alumno).toUpperCase() || '—' }}</span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">FECHA DE NACIMIENTO</span>
                      <span class="se-valor">{{ formatearFecha(alumno.fecha_nacimiento || alumno.persona?.fecha_nacimiento) || '—' }}</span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">GÉNERO</span>
                      <span class="se-valor">{{ (alumno.genero || alumno.persona?.genero || '—').toUpperCase() }}</span>
                    </div>
                    <div class="se-campo se-full">
                      <span class="se-label">CORREO INSTITUCIONAL</span>
                      <span class="se-valor">{{ alumno.email || alumno.persona?.email || '—' }}</span>
                    </div>
                  </div>
                </div>

                <div class="se-exp-seccion">
                  <div class="se-exp-titulo">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6 12v5c3 3 9 3 12 0v-5"/>
                    </svg>
                    INFORMACIÓN ACADÉMICA
                  </div>
                  <div class="se-detalle-grid">
                    <div class="se-campo se-full">
                      <span class="se-label">CARRERA</span>
                      <!-- resolverCarrera() corrige el bug del objeto JSON crudo -->
                      <span class="se-valor">{{ resolverCarrera(alumno).toUpperCase() || '—' }}</span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">SEMESTRE ACTUAL</span>
                      <span class="se-valor se-num">{{ alumno.semestre_actual || alumno.semestre || '—' }}°</span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">ESTATUS ACADÉMICO</span>
                      <span class="se-estatus-badge" :class="`badge-${slugEstatus(alumno.estatus)}`">
                        {{ (alumno.estatus || '—').toUpperCase() }}
                      </span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">FECHA DE INGRESO</span>
                      <span class="se-valor">{{ formatearFecha(alumno.fecha_ingreso) || '—' }}</span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">ESPECIALIDAD</span>
                      <span class="se-valor">{{ (alumno.especialidad || '—').toUpperCase() }}</span>
                    </div>
                  </div>
                </div>

                <div class="se-exp-seccion">
                  <div class="se-exp-titulo">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    BENEFICIOS Y CONTACTO DE EMERGENCIA
                  </div>
                  <div class="se-detalle-grid">
                    <div class="se-campo">
                      <span class="se-label">SEGURO MÉDICO</span>
                      <span class="se-valor">{{ (alumno.seguro_medico || '—').toUpperCase() }}</span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">SUBES / BECA</span>
                      <span class="se-valor">{{ (alumno.subes || alumno.beca || '—').toUpperCase() }}</span>
                    </div>
                    <div class="se-campo se-full">
                      <span class="se-label">CONTACTO DE EMERGENCIA</span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">NOMBRE</span>
                      <span class="se-valor">{{ (alumno.contacto_emergencia?.nombre || '—').toUpperCase() }}</span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">RELACIÓN</span>
                      <span class="se-valor">{{ (alumno.contacto_emergencia?.relacion || alumno.contacto_emergencia?.parentesco || '—').toUpperCase() }}</span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">TELÉFONO</span>
                      <span class="se-valor se-mono">{{ alumno.contacto_emergencia?.telefono || '—' }}</span>
                    </div>
                    <div class="se-campo">
                      <span class="se-label">CORREO</span>
                      <span class="se-valor">{{ alumno.contacto_emergencia?.email || '—' }}</span>
                    </div>
                  </div>
                </div>

              </div>
              <!-- ── FIN TAB DATOS ── -->

              <!-- ══ TAB: KARDEX ══ -->
              <div v-if="tabActivo === 'kardex'" class="se-tab-panel">
                <div v-if="cargandoKardex" class="se-kardex-cargando">
                  <div class="se-sk-linea largo"></div>
                  <div class="se-sk-linea medio"></div>
                  <div v-for="i in 4" :key="i" class="se-sk-fila"></div>
                </div>
                <div v-else-if="kardexError" class="se-kardex-error">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                  </svg>
                  <span>NO SE PUDO CARGAR EL KARDEX.</span>
                  <button @click="cargarKardex(alumno)">REINTENTAR</button>
                </div>
                <div v-else-if="kardexData">
                  <div class="se-creditos-bloque" v-if="kardexData.alumno?.creditos_totales">
                    <div class="se-creditos-row">
                      <span class="se-creditos-lbl">AVANCE DE CRÉDITOS</span>
                      <span class="se-creditos-pct" :class="{ verde: porcentajeCreditos >= 80 }">
                        {{ kardexData.alumno.creditos_acumulados }}/{{ kardexData.alumno.creditos_totales }} ({{ porcentajeCreditos }}%)
                      </span>
                    </div>
                    <div class="se-creditos-track">
                      <div class="se-creditos-fill" :style="{ width: porcentajeCreditos + '%', background: porcentajeCreditos >= 80 ? '#16A34A' : '#1B396A' }"></div>
                    </div>
                  </div>
                  <div v-if="kardexData.kardex?.semestres?.length" class="se-kardex-semestres">
                    <div v-for="sem in kardexData.kardex.semestres" :key="sem.numero" class="se-semestre-bloque">
                      <button class="se-semestre-btn" @click="toggleSemestre(sem.numero)" :class="{ abierto: semestresAbiertos.includes(sem.numero) }">
                        <span class="se-semestre-titulo">SEMESTRE {{ sem.numero }}</span>
                        <div class="se-semestre-badges">
                          <span class="se-badge-mini acreditadas">{{ sem.acreditadas }} ACRED.</span>
                          <span v-if="sem.reprobadas > 0" class="se-badge-mini reprobadas">{{ sem.reprobadas }} REPR.</span>
                          <svg class="se-flecha" :class="{ girado: semestresAbiertos.includes(sem.numero) }" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" stroke-width="2">
                            <polyline stroke-linecap="round" stroke-linejoin="round" points="6 9 12 15 18 9"/>
                          </svg>
                        </div>
                      </button>
                      <transition name="expand">
                        <div v-if="semestresAbiertos.includes(sem.numero)" class="se-semestre-materias">
                          <table class="se-tabla-mini">
                            <thead><tr><th>CLAVE</th><th>MATERIA</th><th>CAL.</th><th>ESTADO</th></tr></thead>
                            <tbody>
                              <tr v-for="m in sem.materias" :key="m.clave" :class="{ 'fila-repr': m.estado === 'reprobada' }">
                                <td class="se-clave-mono">{{ m.clave }}</td>
                                <td>{{ m.nombre }}</td>
                                <td class="centrado">
                                  <strong v-if="m.calificacion !== null" :class="{ 'text-verde': m.estado === 'acreditada', 'text-rojo': m.estado === 'reprobada' }">{{ m.calificacion }}</strong>
                                  <span v-else class="text-gris">—</span>
                                </td>
                                <td><span class="se-badge-estado" :style="estiloEstado(m.estado)">{{ etiquetaEstado(m.estado) }}</span></td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </transition>
                    </div>
                  </div>
                  <div v-else class="se-kardex-vacio"><p>NO HAY MATERIAS REGISTRADAS EN EL KARDEX.</p></div>
                </div>
                <div v-else class="se-kardex-vacio"><p>KARDEX NO DISPONIBLE.</p></div>
              </div>

              <!-- ══ TAB: HORARIO ══ -->
              <div v-if="tabActivo === 'horario'" class="se-tab-panel">
                <div v-if="cargandoHorario" class="se-kardex-cargando">
                  <div v-for="i in 5" :key="i" class="se-sk-fila"></div>
                </div>
                <div v-else-if="horarioData?.length" class="se-horario-lista">
                  <div v-for="mat in horarioData" :key="mat.id || mat.nombre" class="se-horario-item">
                    <div class="se-horario-color" :style="{ background: colorMateria(mat.nombre) }"></div>
                    <div class="se-horario-info">
                      <span class="se-horario-nombre">{{ mat.nombre }}</span>
                      <span class="se-horario-meta">{{ mat.dias }} · {{ mat.hora_inicio }}–{{ mat.hora_fin }}</span>
                      <span class="se-horario-aula">AULA: {{ mat.aula || 'N/D' }}</span>
                    </div>
                  </div>
                </div>
                <div v-else class="se-kardex-vacio">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="32" height="32" style="stroke:#9CA3AF;margin-bottom:8px" stroke-width="1.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                  </svg>
                  <p>NO HAY HORARIO REGISTRADO PARA ESTE ALUMNO.</p>
                </div>
              </div>

            </div>
          </div>

          <!-- ── Footer ── -->
          <div class="se-modal-footer">
            <button class="se-btn-sec" @click="cerrarModal" type="button">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline stroke-linecap="round" stroke-linejoin="round" points="12 19 5 12 12 5"/>
              </svg>
              NUEVA BÚSQUEDA
            </button>
            <button class="se-btn-outline" @click="irExpediente" type="button">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline stroke-linecap="round" stroke-linejoin="round" points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
              </svg>
              EXPEDIENTE COMPLETO
            </button>
          </div>

        </div>
      </div>
    </transition>
    </Teleport>

  </MainLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick, watch } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router  = useRouter()
const API_URL = import.meta.env.VITE_API_URL

// ── Estado (patrón DashboardView) ─────────────────────────────────────
const state = reactive({
  cargando:         true,
  cargandoBitacora: false,
  error:            null,
  errorBitacora:    false,
  bitacora:         [],
  carreraData:      [],   // [{ carrera, total, porcentaje }]  → Chart 1
  semestreData:     [],   // [{ semestre, cantidad }]          → Chart 2
  kpis: {
    // Campos que devuelve /api/dashboard/kpis (mismo objeto que DashboardView)
    totalAlumnos:           0,
    alumnosInscritos:       0,
    nuevosAlumnos:          0,
    inscripciones:          0,
    inscripcionesCompletas: 0,
    inscripcionesPendientes:0,
    pctInscripciones:       0,
    gruposActivos:          0,
    numCarreras:            0,
    materiasActivas:        0,
    // 'evaluaciones' puede venir del KPI o calcularse como 0 si no existe
    evaluaciones:           0,
    periodoActivo:          'N/D',
  },
})

// ── Canvas refs (Chart.js) ────────────────────────────────────────────
const c1 = ref(null)
const c2 = ref(null)
const c3 = ref(null)
let chart1 = null, chart2 = null, chart3 = null

// ── Alias para el template (usa los mismos nombres que el template original) ──
const cargando   = computed(() => state.cargando)
const errorCarga = computed(() => !!state.error)
const bitacora   = computed(() => state.bitacora)
const cargandoBit= computed(() => state.cargandoBitacora)

// El template usa kpis.alumnosActivos, kpis.inscripcionesPeriodo, etc.
const kpis = computed(() => ({
  alumnosActivos:          state.kpis.totalAlumnos,
  inscripcionesPeriodo:    state.kpis.inscripciones,
  inscripcionesCompletas:  state.kpis.inscripcionesCompletas,
  inscripcionesPendientes: state.kpis.inscripcionesPendientes,
  pctInscripciones:        state.kpis.pctInscripciones,
  gruposAbiertos:          state.kpis.gruposActivos,
  evaluacionesPendientes:  state.kpis.evaluaciones,
}))

// ── Búsqueda de alumno (lógica de AlumnosSE: client-side sobre /api/alumnos-crud) ──
const inputRef       = ref(null)
const numCtrl        = ref('')
const ultimaBusq     = ref('')
const inputFocused   = ref(false)
const estadoBusqueda = ref('idle')
const mensajeError   = ref('')
const alumno         = ref(null)
const tabActivo      = ref('general')

// Caché del listado completo — se carga una vez al montar
// Igual que AlumnosSE: un solo fetch, filtrado client-side
const _listaAlumnos  = ref([])
const _listaLista    = ref(false)   // true cuando ya se cargó
const _cargandoLista = ref(false)

const tabs = [
  { id: 'general', label: 'DATOS',   icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
  { id: 'kardex',  label: 'KARDEX',  icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z' },
  { id: 'horario', label: 'HORARIO', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
]

// ── Estado del modal (igual que AlumnosSE) ────────────────────────────
const showViewModal    = ref(false)
const kardexData       = ref(null)
const cargandoKardex   = ref(false)
const kardexError      = ref(false)
const horarioData      = ref(null)
const cargandoHorario  = ref(false)
const semestresAbiertos= ref([])

// Abre el modal al encontrar un alumno
watch(alumno, (val) => {
  if (val && estadoBusqueda.value === 'exito') {
    kardexData.value    = null
    horarioData.value   = null
    kardexError.value   = false
    semestresAbiertos.value = []
    tabActivo.value     = 'general'
    showViewModal.value = true
  }
})

// Carga lazy al cambiar de pestaña (igual que AlumnosSE)
watch(tabActivo, async (tab) => {
  if (!alumno.value) return
  if (tab === 'kardex'  && !kardexData.value)  await cargarKardex(alumno.value)
  if (tab === 'horario' && !horarioData.value) await cargarHorario(alumno.value)
})

const cerrarModal = () => {
  showViewModal.value = false
  setTimeout(() => limpiar(), 250)
}

// ── Carga lazy Kardex (igual que AlumnosSE) ───────────────────────────
const cargarKardex = async (a) => {
  const nc = a.numero_control || a.noControl
  if (!nc) return
  cargandoKardex.value = true
  kardexError.value    = false
  try {
    const res = await fetch(`${API_URL}/api/kardex/${nc}`)
    if (!res.ok) throw new Error()
    kardexData.value = await res.json()
    if (kardexData.value?.kardex?.semestres?.length) {
      semestresAbiertos.value = [kardexData.value.kardex.semestres[0].numero]
    }
  } catch {
    kardexError.value = true
  } finally {
    cargandoKardex.value = false
  }
}

// ── Carga lazy Horario (igual que AlumnosSE) ──────────────────────────
const cargarHorario = async (a) => {
  const nc = a.numero_control || a.noControl
  if (!nc) return
  cargandoHorario.value = true
  try {
    const res = await fetch(`${API_URL}/api/horario/${nc}`)
    if (!res.ok) throw new Error()
    horarioData.value = await res.json()
  } catch {
    horarioData.value = []
  } finally {
    cargandoHorario.value = false
  }
}

// ── Helpers copiados de AlumnosSE ─────────────────────────────────────
/** Resuelve nombre desde cualquier estructura que devuelva el backend */
const resolverCarrera = (a) =>
  a?.carrera?.nombre_carrera || a?.carrera?.nombre || (typeof a?.carrera === 'string' ? a.carrera : '') || ''

const colorIndex = (nombre) => {
  if (!nombre) return 0
  let h = 0
  for (let i = 0; i < nombre.length; i++) h += nombre.charCodeAt(i)
  return h % 5
}

const formatearFecha = (f) =>
  !f ? '' : new Date(f).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })

const colorMateria = (nombre) => {
  const colors = ['#1B396A','#7C3AED','#0891B2','#059669','#DC2626','#D97706']
  let h = 0
  for (let i = 0; i < (nombre || '').length; i++) h += nombre.charCodeAt(i)
  return colors[h % colors.length]
}

const slugEstatus = (estatus) => {
  if (!estatus) return 'desconocido'
  const e = estatus.toLowerCase()
  if (e === 'activo')           return 'activo'
  if (e.includes('temporal'))   return 'temporal'
  if (e.includes('definitiva')) return 'definitiva'
  if (e === 'titulado')         return 'titulado'
  if (e === 'egresado')         return 'egresado'
  return 'desconocido'
}

const toggleSemestre = (num) => {
  const idx = semestresAbiertos.value.indexOf(num)
  if (idx === -1) semestresAbiertos.value.push(num)
  else            semestresAbiertos.value.splice(idx, 1)
}

const porcentajeCreditos = computed(() => {
  const a = kardexData.value?.alumno
  if (!a?.creditos_totales) return 0
  return Math.round((a.creditos_acumulados / a.creditos_totales) * 100)
})

const estiloEstado = (estado) => ({
  acreditada: { background: '#DCFCE7', color: '#16A34A' },
  reprobada:  { background: '#FEF2F2', color: '#DC2626' },
  pendiente:  { background: '#FEF3C7', color: '#F59E0B' },
  no_cursada: { background: '#F3F4F6', color: '#6B7280' },
}[estado] ?? { background: '#F3F4F6', color: '#6B7280' })

const etiquetaEstado = (estado) => ({
  acreditada: 'ACRED.',
  reprobada:  'REPR.',
  pendiente:  'EN CURSO',
  no_cursada: 'PENDIENTE',
}[estado] ?? (estado || '').toUpperCase())

/** Normaliza texto para comparación: sin tildes, minúsculas (igual que AlumnosSE) */
const _normalize = (t) =>
  !t ? '' : t.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')

/** Resuelve el nombre desde cualquier forma que devuelva el backend (igual que AlumnosSE) */
const _resolverNombre = (a) =>
  a?.nombre || a?.persona?.nombre_completo || a?.persona?.nombre || ''

/** Carga el listado completo una sola vez (lazy) */
const _cargarLista = async () => {
  if (_listaLista.value || _cargandoLista.value) return
  _cargandoLista.value = true
  try {
    const res  = await fetch(`${API_URL}/api/alumnos-crud`)
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const data = await res.json()
    // Normaliza nombre igual que AlumnosSE
    _listaAlumnos.value = data.map(a => ({
      ...a,
      nombre: _resolverNombre(a),
    }))
    _listaLista.value = true
  } catch (e) {
    console.error('[ServiciosEscolares] _cargarLista:', e)
    // Si falla el caché, la próxima búsqueda lo reintentará
    _listaLista.value = false
  } finally {
    _cargandoLista.value = false
  }
}

const limpiar = () => {
  numCtrl.value        = ''
  alumno.value         = null
  estadoBusqueda.value = 'idle'
  mensajeError.value   = ''
  ultimaBusq.value     = ''
  tabActivo.value      = 'general'
  nextTick(() => inputRef.value?.focus())
}

const onInput = () => {
  if (estadoBusqueda.value !== 'cargando') {
    alumno.value         = null
    estadoBusqueda.value = 'idle'
  }
}

/**
 * buscar() — filtrado client-side sobre /api/alumnos-crud
 * Acepta:
 *   • Número de control exacto de 8 dígitos → match exacto
 *   • Número parcial (ej. "2203")           → match por includes en noControl
 *   • Nombre o apellido (≥ 2 chars)         → normalize + includes (sin tildes)
 * Si hay varios resultados toma el primero y muestra cuántos coincidieron.
 */
const buscar = async () => {
  const term = numCtrl.value.trim()
  if (!term) return

  estadoBusqueda.value = 'cargando'
  ultimaBusq.value     = term
  alumno.value         = null
  tabActivo.value      = 'general'

  try {
    // Carga lazy del listado (solo la primera vez)
    if (!_listaLista.value) await _cargarLista()

    if (!_listaLista.value) {
      // Si _cargarLista() falló → error de conexión
      estadoBusqueda.value = 'error'
      mensajeError.value   = 'Error al conectar con el servidor. Intenta de nuevo.'
      return
    }

    const termNorm   = _normalize(term)
    const esNumerico = /^\d+$/.test(term)

    const resultados = _listaAlumnos.value.filter(a => {
      const noCtrl = (a.numero_control || a.noControl || '').toString()
      const nombre = _resolverNombre(a)
      if (esNumerico) {
        // Exacto primero; si no, partial (igual que AlumnosSE)
        return noCtrl === term || noCtrl.includes(term)
      }
      return _normalize(nombre).includes(termNorm)
    })

    if (resultados.length === 0) {
      estadoBusqueda.value = 'no-encontrado'
      return
    }

    // Toma el primer resultado; si hay exacto de 8 dígitos, priorízalo
    const exacto = resultados.find(a =>
      (a.numero_control || a.noControl || '').toString() === term
    )
    alumno.value         = exacto ?? resultados[0]
    estadoBusqueda.value = 'exito'
    const extra = resultados.length > 1 ? ` (${resultados.length} coincidencias)` : ''
    mostrarNotif(`Alumno encontrado: ${alumno.value.nombre}${extra}`, 'exito')

  } catch (e) {
    console.error('[ServiciosEscolares] buscar:', e)
    estadoBusqueda.value = 'error'
    mensajeError.value   = 'Error al conectar con el servidor. Intenta de nuevo.'
  }
}

const irExpediente = () => {
  const nc = alumno.value?.numero_control || alumno.value?.noControl
  if (nc) router.push(`/alumnos/expediente/${nc}`)
}

// ── Carga del dashboard (lógica exacta de DashboardView) ──────────────
const cargarDatos = async () => {
  state.cargando = true
  state.error    = null
  try {
    // Promise.all con los mismos 3 endpoints de DashboardView
    const [resKpis, resCarreras, resSem] = await Promise.all([
      fetch(`${API_URL}/api/dashboard/kpis`).then(r => r.json()),
      fetch(`${API_URL}/api/dashboard/carreras`).then(r => r.json()),
      fetch(`${API_URL}/api/dashboard/semestres`).then(r => r.json()),
    ])

    // KPIs — igual que DashboardView: acepta { kpis: {...} } o el objeto plano
    const kpiRaw = resKpis.kpis ?? resKpis
    Object.assign(state.kpis, kpiRaw)
    // 'evaluaciones' puede llegar con distintos nombres según el backend
    state.kpis.evaluaciones = kpiRaw.evaluaciones ?? kpiRaw.evaluaciones_pendientes ?? kpiRaw.adeudosPendientes ?? 0

    // Carreras — para Chart 1 (barras)
    state.carreraData  = resCarreras.carreraData ?? resCarreras.carreras ?? resCarreras ?? []

    // Semestres — para Chart 2 (línea por semestre)
    state.semestreData = resSem.semestres ?? resSem ?? []

  } catch (e) {
    state.error = 'Error al cargar los datos del panel escolar.'
    console.error('[ServiciosEscolares] cargarDatos:', e)
  } finally {
    state.cargando = false
    nextTick(() => inicializarCharts())
  }
}

// ── Carga de bitácora (igual que DashboardView) ───────────────────────
const cargarBitacora = async () => {
  state.cargandoBitacora = true
  state.errorBitacora    = false
  try {
    const res  = await fetch(`${API_URL}/api/bitacora?limit=5`)
    const data = await res.json()
    state.bitacora = data.registros ?? data ?? []
  } catch (e) {
    state.errorBitacora = true
    console.error('[ServiciosEscolares] cargarBitacora:', e)
  } finally {
    state.cargandoBitacora = false
  }
}

// ── Charts (sin cambios respecto al original — solo los datos son reales) ──
const COLORES_CARRERA = ['#132B4F','#1A4184','#1D52B7','#2F80ED','#27AE60','#F2994A']

const inicializarCharts = () => {
  if (typeof window === 'undefined' || !window.Chart) return
  const C = window.Chart
  C.defaults.font.family = "'Montserrat', system-ui, sans-serif"
  C.defaults.font.size   = 11
  C.defaults.color       = '#828282'

  const tooltip = {
    backgroundColor: '#0B2545',
    titleColor: 'rgba(255,255,255,0.6)',
    bodyColor: '#FFFFFF',
    borderColor: '#1D52B7',
    borderWidth: 1,
    padding: 10,
  }

  // Chart 1: Barras — Alumnos por carrera (datos reales de BD)
  if (c1.value) {
    if (chart1) chart1.destroy()
    const labels = state.carreraData.map(c => c.carrera ?? c.nombre ?? '')
    const data   = state.carreraData.map(c => c.total   ?? c.cantidad ?? 0)
    chart1 = new C(c1.value, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          data,
          backgroundColor: COLORES_CARRERA.slice(0, labels.length),
          borderRadius: 6,
          borderSkipped: false,
          hoverBackgroundColor: ['#0B2545','#132B4F','#1A4184','#1D52B7','#1e8449','#d68910'].slice(0, labels.length),
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { ...tooltip, callbacks: { label: c => ` ${c.parsed.y} alumnos` } } },
        scales: {
          x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 10, family: "'Montserrat', sans-serif" }, color: '#828282' } },
          y: { grid: { color: '#F4F6F9' }, border: { display: false }, ticks: { font: { size: 10, family: "'Montserrat', sans-serif" }, color: '#828282' } },
        }
      }
    })
  }

  // Chart 2: Línea — Alumnos por semestre (datos reales de BD)
  if (c2.value) {
    if (chart2) chart2.destroy()
    const labels = state.semestreData.map(s => `${s.semestre ?? s.nombre}°`)
    const data   = state.semestreData.map(s => s.cantidad ?? s.total ?? 0)
    chart2 = new C(c2.value, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          data,
          borderColor: '#1D52B7',
          backgroundColor: 'rgba(29,82,183,0.07)',
          borderWidth: 2.5, fill: true, tension: 0.4,
          pointBackgroundColor: '#FFFFFF', pointBorderColor: '#1D52B7',
          pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6,
          pointHoverBackgroundColor: '#1D52B7', pointHoverBorderColor: '#FFFFFF', pointHoverBorderWidth: 2,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { ...tooltip, callbacks: { label: c => ` ${c.parsed.y} alumnos` } } },
        scales: {
          x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 10, family: "'Montserrat', sans-serif" }, color: '#828282' } },
          y: { grid: { color: '#F4F6F9' }, border: { display: false }, ticks: { font: { size: 10, family: "'Montserrat', sans-serif" }, color: '#828282' } },
        }
      }
    })
  }

  // Chart 3: Donut — Estado inscripciones (datos reales de BD)
  if (c3.value) {
    if (chart3) chart3.destroy()
    const pct = state.kpis.pctInscripciones || 0
    chart3 = new C(c3.value, {
      type: 'doughnut',
      data: {
        labels: [`Completadas ${pct}%`, `Pendientes ${100 - pct}%`],
        datasets: [{
          data: [state.kpis.inscripcionesCompletas || pct, state.kpis.inscripcionesPendientes || (100 - pct)],
          backgroundColor: ['#1D52B7', 'rgba(242,153,74,0.15)'],
          borderColor: ['#1D52B7', '#F2994A'],
          borderWidth: 1, hoverOffset: 4,
          hoverBackgroundColor: ['#1A4184', 'rgba(242,153,74,0.25)'],
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '72%',
        plugins: { legend: { display: false }, tooltip: { ...tooltip, callbacks: { label: c => ` ${c.label}` } } }
      }
    })
  }
}

// ── Toast ─────────────────────────────────────────────────────────────
const notif = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotif = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notif.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notif.value.visible = false }, 3500)
}

// ── Helpers (sin cambios) ─────────────────────────────────────────────
const fmt = (n) => Number(n || 0).toLocaleString('es-MX')

const iniciales = (n = '') => n.split(' ').slice(0, 2).map(p => p[0] ?? '').join('').toUpperCase()

const fFecha = (iso) => {
  if (!iso) return 'No registrado'
  try { return new Date(iso).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' }) }
  catch { return iso }
}

const estatusClass = (e = '') => {
  const m = { 'Activo':'es-act', 'Inactivo':'es-ina', 'Egresado':'es-egr', 'Suspendido':'es-sus', 'Baja':'es-baj' }
  return m[e] ?? 'es-ina'
}

const califClass = (c) => {
  if (c === null || c === undefined) return 'ca-nd'
  if (c >= 80) return 'ca-v'
  if (c >= 70) return 'ca-a'
  return 'ca-r'
}

const tRel = (fecha) => {
  if (!fecha) return '—'
  const diff = Date.now() - new Date(fecha).getTime()
  const min  = Math.floor(diff / 60000)
  if (min < 1)  return 'Ahora'
  if (min < 60) return `Hace ${min} min`
  const h = Math.floor(min / 60)
  if (h  < 24)  return `Hace ${h} h`
  return `Hace ${Math.floor(h / 24)} día(s)`
}

const claseBadge = (accion = '') => {
  const a = accion.toLowerCase()
  if (a.includes('insert') || a.includes('crear') || a.includes('inscri') || a.includes('alta')) return 'bg-g'
  if (a.includes('update') || a.includes('edit')  || a.includes('actualiz'))                     return 'bg-a'
  if (a.includes('delet')  || a.includes('elim')  || a.includes('baja'))                         return 'bg-r'
  return 'bg-b'
}

// ── Lifecycle ──────────────────────────────────────────────────────────
onMounted(() => {
  cargarDatos()
  cargarBitacora()
  // Precarga la lista de alumnos en background para que la primera búsqueda sea instantánea
  _cargarLista()
  nextTick(() => inputRef.value?.focus())

  if (!window.Chart) {
    const script  = document.createElement('script')
    script.src    = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js'
    script.onload = () => nextTick(() => inicializarCharts())
    document.head.appendChild(script)
  }
})
</script>


<style scoped>
/* ══════════════════════════════════════════════════════════════
   VARIABLES — Paleta completa del PDF COLORIMETRIA
   Todos los colores referenciados en el PDF están aquí.
══════════════════════════════════════════════════════════════ */
:root {
  /* Bloque Azules */
  --azul-marino-profundo : #0B2545;
  --azul-rey             : #1D52B7;
  --azul-marino-medio    : #1A4184;
  --azul-cyan            : #2F80ED;
  --azul-cobalto-oscuro  : #132B4F;
  /* Semántico */
  --verde-esmeralda      : #27AE60;
  --rojo-coral           : #EB5757;
  --rosa-alerta          : #FFF0F0;
  --naranja-calma        : #F2994A;
  /* Neutros */
  --blanco               : #FFFFFF;
  --gris-hielo           : #F4F6F9;
  --gris-contenedor      : #F2F4F7;
  --gris-bordes          : #E0E0E0;
  /* Tipográfico */
  --carbon-oscuro        : #333333;
  --gris-pizarra         : #4F4F4F;
  --gris-medio           : #828282;
  --gris-carga           : #BDBDBD;
}

/* ══ BASE ══ */
.page {
  font-family: 'Montserrat', system-ui, sans-serif;
  background: var(--gris-hielo);   /* #F4F6F9 — Canvas Background del PDF */
  display: flex;
  flex-direction: column;
  gap: 14px;
  min-height: 100%;
  padding-bottom: 2rem;
  text-transform: uppercase; 
}
.spacer { flex: 1; }

/* ── Breadcrumb ── */
.bc     { display:flex; align-items:center; gap:5px; font-size:11px; color:var(--gris-medio); }
.bc-sep { color:var(--gris-carga); opacity:.6; }
.bc-lnk { color:var(--gris-medio); text-decoration:none; transition:color .15s; }
.bc-lnk:hover { color:var(--azul-rey); }
.bc-cur { color:var(--azul-cyan); font-weight:600; }  /* Azul Cyan — texto activo del PDF */

/* ── Toast ── */
.toast {
  position:fixed; bottom:2rem; right:2rem;
  display:flex; align-items:center; gap:10px;
  padding:.9rem 1.4rem; border-radius:10px;
  color:var(--blanco); font-weight:700; font-size:.9rem;
  box-shadow:0 8px 24px rgba(0,0,0,.15);
  z-index:3000; font-family:'Montserrat',sans-serif; max-width:380px;
}
.toast.exito { background:var(--azul-marino-profundo); }  /* #0B2545 */
.toast.error { background:var(--rojo-coral); }             /* #EB5757 */
.toast svg   { flex-shrink:0; stroke:var(--blanco); }
.toast-slide-enter-active,.toast-slide-leave-active { transition:all .3s ease; }
.toast-slide-enter-from,.toast-slide-leave-to { opacity:0; transform:translateX(100%); }

/* ══ HERO ══ */
.hero {
  background: #0B2545 !important;
  background-color: #0B2545 !important;
  border-radius: 14px;
  padding: 22px 26px;
  display: flex;
  align-items: center;
  gap: 20px;
  position: relative;
  overflow: hidden;
  /* Forzar que el fondo no sea transparente bajo ninguna circunstancia */
  isolation: isolate;
}
.hero-deco  {
  position: absolute; right: -40px; top: -40px;
  width: 220px; height: 220px; border-radius: 50%;
  background: rgba(29,82,183,.08);
  pointer-events: none;
  z-index: 0;
}
.hero-deco2 {
  position: absolute; right: 60px; bottom: -60px;
  width: 140px; height: 140px; border-radius: 50%;
  background: rgba(29,82,183,.05);
  pointer-events: none;
  z-index: 0;
}
.hero-left  { flex: 1; position: relative; z-index: 1; }
.hero-stats { display: flex; flex-direction: column; gap: 10px; position: relative; z-index: 1; }

.hero-badge {
  display: inline-flex; align-items: center; gap: 5px;
  background: rgba(47,128,237,.18);
  border: 1px solid rgba(47,128,237,.3);
  border-radius: 20px; padding: 3px 10px;
  font-size: 10px; font-weight: 600; color: #2F80ED;
  margin-bottom: 10px; letter-spacing: .03em;
}
.hero-badge svg { stroke: #2F80ED; }

.hero-title {
  font-size: 24px; font-weight: 700;
  color: #FFFFFF !important;
  margin-bottom: 5px; line-height: 1.2;
  font-family: 'Montserrat', sans-serif;
}
.hero-sub {
  font-size: 12px;
  color: rgba(255,255,255,.5) !important;
  margin-bottom: 16px; font-weight: 300;
  font-family: 'Montserrat', sans-serif;
}

.hero-form        { display: flex; gap: 8px; max-width: 520px; }
.hero-input-wrap  {
  flex: 1; display: flex; align-items: center;
  background: rgba(255,255,255,.09);
  border: 1px solid rgba(255,255,255,.18);
  border-radius: 9px; padding: 0 12px;
  transition: border-color .15s;
}
.hi-focus { border-color: rgba(29,82,183,.7) !important; background: rgba(29,82,183,.15) !important; }
.hi-error { border-color: #EB5757 !important; }
.hi-lupa  { stroke: #828282; flex-shrink: 0; margin-right: 8px; }
.hi-inp {
  flex: 1; background: transparent; border: none; outline: none;
  font-family: 'Montserrat', sans-serif; font-size: 13px;
  color: #FFFFFF !important;
  padding: 10px 0;
}
.hi-inp::placeholder { color: #828282; }
.hi-spinner { display: flex; align-items: center; justify-content: center; width: 22px; height: 22px; flex-shrink: 0; }
.spin { animation: girar .75s linear infinite; }
@keyframes girar { to { transform: rotate(360deg); } }
.hi-clear {
  background: transparent; border: none; cursor: pointer; padding: 4px;
  display: flex; align-items: center; justify-content: center;
  color: #828282; border-radius: 4px; flex-shrink: 0;
  transition: color .15s;
}
.hi-clear:hover { color: #FFFFFF; }
.hi-clear svg { stroke: currentColor; }

.hero-btn {
  background: #1D52B7; border: none; border-radius: 9px;
  padding: 10px 20px; color: #FFFFFF;
  font-size: 13px; font-weight: 600; cursor: pointer;
  display: flex; align-items: center; gap: 6px;
  font-family: 'Montserrat', sans-serif; white-space: nowrap;
  transition: background .15s;
}
.hero-btn:hover    { background: #0B2545; }
.hero-btn:disabled { opacity: .55; cursor: not-allowed; }
.hero-btn svg      { stroke: #FFFFFF; }

.hero-hint     { font-size: 10px; color: #828282; margin-top: 8px; display: flex; align-items: center; gap: 4px; }
.hero-hint svg { stroke: currentColor; flex-shrink: 0; }
.hint-err      { color: #EB5757 !important; }
.hint-warn     { color: #F2994A !important; }

.hstat {
  background: rgba(255,255,255,.06);
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 10px; padding: 12px 16px;
  min-width: 130px; text-align: center;
}
.hstat-n { font-size: 20px; font-weight: 700; color: #FFFFFF !important; font-family: 'Montserrat', sans-serif; }
.hstat-l { font-size: 10px; color: #828282; margin-top: 2px; font-family: 'Montserrat', sans-serif; }

/* ══ RESULTADO ALUMNO ══ */
.resultado {
  background:var(--blanco); border:1px solid var(--gris-bordes);
  border-radius:12px; overflow:hidden;
  box-shadow:0 2px 8px rgba(29,82,183,.05);
}

/* Header del alumno */
.al-hdr {
  display:flex; align-items:center; gap:16px;
  padding:1.5rem 1.75rem; border-bottom:1px solid var(--gris-hielo);
  background:var(--blanco);
}
/* Avatar — fondo Azul Marino Profundo */
.al-av {
  width:52px; height:52px; border-radius:12px; flex-shrink:0;
  background:var(--azul-marino-profundo); color:var(--blanco);
  font-size:18px; font-weight:700; display:flex; align-items:center;
  justify-content:center; font-family:'Montserrat',sans-serif;
}
.al-info { flex:1; min-width:0; }
.al-nombre-row { display:flex; align-items:center; gap:10px; flex-wrap:wrap; margin-bottom:4px; }
/* Título nombre — Montserrat Bold 24px del PDF */
.al-nombre { font-size:18px; font-weight:700; color:var(--carbon-oscuro); font-family:'Montserrat',sans-serif; margin:0; }
.al-meta {
  display:flex; align-items:center; gap:5px;
  font-size:12px; color:var(--gris-pizarra); margin:2px 0;
  font-family:'Montserrat',sans-serif;
}
.al-meta svg { stroke:var(--gris-medio); flex-shrink:0; }
.al-acciones { display:flex; gap:8px; flex-shrink:0; }

/* Estatus chips */
.al-estatus { font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; font-family:'Montserrat',sans-serif; }
.es-act { background:rgba(39,174,96,.12); color:var(--verde-esmeralda); }
.es-ina { background:rgba(189,189,189,.15); color:var(--gris-medio); }
.es-egr { background:rgba(29,82,183,.10); color:var(--azul-rey); }
.es-sus { background:rgba(242,153,74,.12); color:var(--naranja-calma); }
.es-baj { background:rgba(235,87,87,.10); color:var(--rojo-coral); }

/* Botones primario/outline */
.btn-primary {
  background:var(--azul-rey); color:var(--blanco); border:none;
  padding:9px 16px; border-radius:8px; font-size:12px; font-weight:600;
  cursor:pointer; display:flex; align-items:center; gap:6px;
  font-family:'Montserrat',sans-serif; transition:background .15s; white-space:nowrap;
}
.btn-primary:hover { background:var(--azul-marino-profundo); }
.btn-primary svg { stroke:var(--blanco); }
.btn-outline {
  background:transparent; color:var(--carbon-oscuro); border:1px solid var(--gris-bordes);
  padding:9px 16px; border-radius:8px; font-size:12px; font-weight:600;
  cursor:pointer; display:flex; align-items:center; gap:6px;
  font-family:'Montserrat',sans-serif; transition:all .15s; white-space:nowrap;
}
.btn-outline:hover { border-color:var(--azul-cyan); color:var(--azul-cyan); }
.btn-outline svg { stroke:currentColor; }

/* Tabs */
.tabs-bar {
  display:flex; gap:0; border-bottom:1px solid var(--gris-hielo);
  padding:0 1.75rem; background:var(--blanco); overflow-x:auto;
}
.tab-btn {
  background:transparent; border:none; border-bottom:3px solid transparent;
  padding:14px 18px; font-size:12px; font-weight:500; color:var(--gris-medio);
  cursor:pointer; white-space:nowrap; font-family:'Montserrat',sans-serif;
  transition:all .15s;
}
.tab-btn:hover { color:var(--carbon-oscuro); }
/* Tab activo — color Azul Cyan del PDF */
.tab-act { color:var(--azul-cyan) !important; border-bottom-color:var(--azul-rey) !important; font-weight:600; }

/* Contenido de tabs */
.tab-body { padding:1.5rem 1.75rem; }

/* Info grid (tab General y Adicional) */
.info-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.info-card { background:var(--gris-hielo); border-radius:10px; padding:16px 18px; border:1px solid var(--gris-bordes); }
/* Título de tarjeta — Montserrat SemiBold 16-18px del PDF */
.info-t { font-size:14px; font-weight:600; color:var(--carbon-oscuro); margin:0 0 12px; font-family:'Montserrat',sans-serif; }
.info-list { display:flex; flex-direction:column; gap:10px; list-style:none; padding:0; margin:0; }
.if { display:flex; flex-direction:column; gap:2px; }
.if dt { font-size:10px; font-weight:600; letter-spacing:.05em; color:var(--gris-medio); font-family:'Montserrat',sans-serif; }
.if dd { font-size:13px; color:var(--carbon-oscuro); font-family:'Montserrat',sans-serif; margin:0; }
.mono { font-family:'Courier New', monospace; font-size:12px; }

/* Tab Académico */
.acad-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px; }
.acad-kpi  { background:var(--gris-hielo); border:1px solid var(--gris-bordes); border-radius:10px; padding:14px; text-align:center; }
/* KPI numérico — Montserrat Bold 32-36px del PDF */
.acad-val  { font-size:28px; font-weight:700; line-height:1; font-family:'Montserrat',sans-serif; }
.acad-lbl  { font-size:10px; color:var(--gris-medio); margin-top:5px; font-weight:600; letter-spacing:.05em; font-family:'Montserrat',sans-serif; }
.azul   { color:var(--azul-cyan); }
.verde  { color:var(--verde-esmeralda); }
.naranja { color:var(--naranja-calma); }
.rojo   { color:var(--rojo-coral); }

/* Barra de progreso */
.prog-card { background:var(--gris-hielo); border-radius:10px; padding:16px 18px; border:1px solid var(--gris-bordes); }
.prog-hdr  { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.prog-lbl  { font-size:12px; font-weight:600; color:var(--carbon-oscuro); font-family:'Montserrat',sans-serif; }
.prog-pct  { font-size:14px; font-weight:700; color:var(--azul-rey); font-family:'Montserrat',sans-serif; }
.prog-bg   { height:8px; background:var(--gris-bordes); border-radius:4px; overflow:hidden; }
.prog-fill { height:100%; background:var(--azul-rey); border-radius:4px; transition:width .5s ease; }
.prog-det  { font-size:11px; color:var(--gris-medio); margin-top:6px; font-family:'Montserrat',sans-serif; }

/* Kardex */
.kardex-per     { margin-bottom:20px; }
.kardex-per-hdr { display:flex; align-items:center; justify-content:space-between; background:var(--azul-marino-profundo); color:var(--blanco); padding:8px 14px; border-radius:8px 8px 0 0; font-size:12px; font-family:'Montserrat',sans-serif; }
.kardex-per-n   { font-weight:600; }
.kardex-per-p   { font-size:11px; color:rgba(255,255,255,.65); }
.tabla-wrap     { overflow-x:auto; border:1px solid var(--gris-bordes); border-top:none; border-radius:0 0 8px 8px; }
.tabla          { width:100%; border-collapse:collapse; font-size:12px; font-family:'Montserrat',sans-serif; }
.tabla th       { background:var(--gris-contenedor); color:var(--gris-pizarra); font-weight:600; font-size:10px; letter-spacing:.04em; padding:8px 12px; text-align:left; border-bottom:1px solid var(--gris-bordes); }
.tabla td       { padding:9px 12px; border-bottom:1px solid var(--gris-hielo); color:var(--carbon-oscuro); vertical-align:middle; }
.tabla tr:last-child td { border-bottom:none; }
.tabla tr:hover td { background:rgba(29,82,183,.02); }
.tc             { text-align:center; }
.sm             { font-size:11px; }
.calif          { font-weight:700; font-size:11px; padding:2px 7px; border-radius:5px; }
.ca-v           { background:rgba(39,174,96,.12); color:var(--verde-esmeralda); }
.ca-a           { background:rgba(242,153,74,.12); color:var(--naranja-calma); }
.ca-r           { background:rgba(235,87,87,.12); color:var(--rojo-coral); }
.ca-nd          { background:rgba(189,189,189,.15); color:var(--gris-medio); }
.chip-mini      { font-size:10px; font-weight:600; padding:2px 8px; border-radius:10px; white-space:nowrap; }
.ch-v           { background:rgba(39,174,96,.12); color:var(--verde-esmeralda); }
.ch-r           { background:rgba(235,87,87,.12); color:var(--rojo-coral); }
.ch-g           { background:rgba(189,189,189,.15); color:var(--gris-medio); }

/* Horario */
.horario-list { display:flex; flex-direction:column; gap:8px; }
.horario-card {
  display:flex; align-items:center; gap:12px;
  padding:12px 14px; border-radius:10px;
  border:1px solid var(--gris-bordes); background:var(--gris-hielo);
}
.hor-dia  { font-size:11px; font-weight:700; color:var(--azul-rey); min-width:40px; font-family:'Montserrat',sans-serif; }
.hor-info { flex:1; min-width:0; }
.hor-mat  { font-size:.93rem; font-weight:700; color:var(--carbon-oscuro); margin:0 0 2px; font-family:'Montserrat',sans-serif; }
.hor-det  { font-size:.82rem; color:var(--gris-medio); margin:0; font-family:'Montserrat',sans-serif; }
.hor-doc  { display:flex; flex-direction:column; align-items:flex-end; text-align:right; flex-shrink:0; }
.hor-doc-l { font-size:.72rem; color:var(--gris-medio); margin-bottom:2px; font-family:'Montserrat',sans-serif; }
.hor-doc-n { font-size:.83rem; font-weight:600; color:var(--carbon-oscuro); font-family:'Montserrat',sans-serif; }

/* Estado vacío de tabs */
.tab-vacio { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.75rem; padding:3rem 1rem; color:var(--gris-medio); text-align:center; }
.tab-vacio p { font-size:.9rem; margin:0; font-family:'Montserrat',sans-serif; }

/* Chips genéricos */
.chip { display:inline-flex; align-items:center; padding:3px 12px; border-radius:20px; font-size:.78rem; font-weight:700; white-space:nowrap; font-family:'Montserrat',sans-serif; }

/* ══ DASHBOARD ══ */
.dash { display:flex; flex-direction:column; gap:14px; }

/* KPI Grid — 4 columnas */
.kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
.kpi { background:var(--blanco); border:1px solid var(--gris-bordes); border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:13px; cursor:pointer; transition:all .15s; box-shadow:0 2px 8px rgba(29,82,183,.05); }
.kpi:hover { border-color:var(--azul-cyan); box-shadow:0 0 0 3px rgba(29,82,183,.07); }


/* KPI featured — fondo Azul Marino Profundo  */
.kpi-feat { background: linear-gradient(135deg, #0B2545 0%, #1A4184 100%) !important; border-color: #0B2545 !important; position: relative; overflow: hidden; align-items: center; }
.kpi-feat::after { content: ''; position: absolute; right: -20px; top: -20px; width: 110px; height: 110px; border-radius: 50%; background: rgba(47, 128, 237, 0.15); pointer-events: none; }
.kpi-feat .kpi-lbl { color: rgba(255,255,255,.6) !important; letter-spacing: .08em; font-size: 10px; }
.kpi-feat .kpi-val { color: #FFFFFF !important; }
.kpi-feat .kpi-lnk { color: rgba(255,255,255,.7) !important; letter-spacing: .04em; font-size: 10px; display: flex; align-items: center; gap: 3px; }
.kpi-feat .kpi-ico { background: rgba(255,255,255,.12) !important; color: #FFFFFF !important; position: relative; z-index: 1; align-self: center; }
.kpi-feat .kpi-ico svg { stroke: #FFFFFF !important; }
.kpi-ico { align-self: center; }
.kpi-feat .kpi-body { position: relative; z-index: 1; align-items: flex-start; display: flex; flex-direction: column; }

/* Íconos de KPI */
.kpi-ico { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; align-self:center; }
.kpi-ico svg { stroke:currentColor; }
.ki-bw { background:rgba(255,255,255,.1); color:var(--azul-cyan); }
.ki-g  { background:rgba(39,174,96,.10); color:var(--verde-esmeralda); }
.ki-a  { background:rgba(242,153,74,.10); color:var(--naranja-calma); }
.ki-v  { background:rgba(47,128,237,.10); color:var(--azul-marino-medio); }
.kpi-ico { align-self: center; }

/* Cuerpo del KPI */
.kpi-body { flex:1; min-width:0; display:flex; flex-direction:column; align-items:flex-start; }
.kpi-lbl { font-size:10px; font-weight:600; letter-spacing:.07em; color:var(--gris-medio); margin-bottom:4px; font-family:'Montserrat',sans-serif; }
.kpi-val { font-size:32px; font-weight:700; color:var(--carbon-oscuro); line-height:1; font-family:'Montserrat',sans-serif; }
.kpi-lnk { font-size:11px; font-weight:500; color:var(--azul-cyan); margin-top:5px; display:flex; align-items:center; gap:3px; font-family:'Montserrat',sans-serif; letter-spacing:.04em; }
.kpi-lnk svg { stroke:var(--azul-cyan); }

/* Skeleton de carga */
.kpi-sk   { width:80px; height:32px; border-radius:6px; background:linear-gradient(90deg,#E0E0E0 25%,#F2F4F7 50%,#E0E0E0 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.kpi-sk-d { background:linear-gradient(90deg,rgba(255,255,255,.1) 25%,rgba(255,255,255,.2) 50%,rgba(255,255,255,.1) 75%); background-size:200% 100%; }
@keyframes shimmer { 0%{background-position:200% 0}100%{background-position:-200% 0} }

/* Main grid: 1.4fr / 1fr */
.main-grid { display:grid; grid-template-columns:minmax(0,1.4fr) minmax(0,1fr); gap:14px; }
.card {
  background:var(--blanco); border:1px solid var(--gris-bordes);
  border-radius:12px; padding:18px;
  box-shadow:0 2px 8px rgba(29,82,183,.05);
}
.card-h { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
/* Título de tarjeta — 16px SemiBold del PDF (la maqueta usa 16px) */
.card-t { font-size:16px; font-weight:600; color:var(--carbon-oscuro); font-family:'Montserrat',sans-serif; }
.card-lk { font-size:11px; font-weight:600; color:var(--azul-cyan); cursor:pointer; display:flex; align-items:center; gap:3px; text-decoration:none; font-family:'Montserrat',sans-serif; }
.card-lk:hover { color:var(--azul-marino-medio); }
.card-lk svg { stroke:currentColor; }

/* Accesos rápidos */
.acc-list { display:flex; flex-direction:column; gap:8px; }
.acc-item {
  display:flex; align-items:center; gap:12px; padding:12px 14px;
  border-radius:10px; border:1px solid var(--gris-bordes);
  background:var(--gris-contenedor); cursor:pointer; transition:all .15s;
}
.acc-item:hover { border-color:var(--azul-cyan); background:rgba(29,82,183,.05); }
.acc-item.prox  { opacity:.55; cursor:default; }
.acc-item.prox:hover { border-color:var(--gris-bordes); background:var(--gris-contenedor); }
.acc-ico { width:38px; height:38px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.acc-ico svg { stroke:currentColor; }
.ai-g  { background:rgba(39,174,96,.10); color:var(--verde-esmeralda); }
.ai-b  { background:rgba(47,128,237,.10); color:var(--azul-rey); }
.ai-a  { background:rgba(242,153,74,.10); color:var(--naranja-calma); }
.ai-gr { background:var(--gris-contenedor); color:var(--gris-medio); }
.acc-body { flex:1; min-width:0; }
.acc-t { font-size:12px; font-weight:600; color:var(--carbon-oscuro); margin-bottom:2px; font-family:'Montserrat',sans-serif; }
.acc-d { font-size:11px; color:var(--gris-pizarra); font-family:'Montserrat',sans-serif; }
.acc-arrow { stroke:var(--gris-carga); flex-shrink:0; transition:stroke .15s; }
.acc-item:not(.prox):hover .acc-arrow { stroke:var(--azul-rey); }
.prox-bdg { font-size:9px; font-weight:700; color:var(--gris-medio); background:var(--gris-contenedor); border-radius:20px; padding:2px 8px; flex-shrink:0; letter-spacing:.04em; border:1px solid var(--gris-bordes); }

/* Bitácora */
.bit-list { display:flex; flex-direction:column; }
.bit-load { display:flex; align-items:center; gap:8px; padding:1rem 0; color:var(--gris-medio); font-size:11px; font-family:'Montserrat',sans-serif; }
/* Spinner — borde azul rey del PDF */
.spinner { width:15px; height:15px; border:2px solid var(--gris-bordes); border-top-color:var(--azul-rey); border-radius:50%; animation:girar .75s linear infinite; flex-shrink:0; }
.bit-item { display:flex; align-items:flex-start; gap:10px; padding:10px 0; border-bottom:1px solid var(--gris-hielo); }
.bit-item:last-child { border-bottom:none; padding-bottom:0; }
/* Avatar bitácora — fondo Azul Marino Profundo del PDF */
.bit-av { width:30px; height:30px; border-radius:50%; background:var(--azul-marino-profundo); color:var(--blanco); font-size:10px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-family:'Montserrat',sans-serif; }
.bit-body { flex:1; min-width:0; }
.bit-r1  { display:flex; align-items:center; gap:6px; margin-bottom:2px; flex-wrap:wrap; }
.bit-usr  { font-size:12px; font-weight:600; color:var(--carbon-oscuro); font-family:'Montserrat',sans-serif; }
.bit-desc { font-size:11px; color:var(--gris-pizarra); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-family:'Montserrat',sans-serif; }
.bit-t    { font-size:10px; color:var(--gris-medio); margin-top:2px; font-family:'Montserrat',sans-serif; }

/* Badges */
.bdg   { font-size:9px; font-weight:700; padding:2px 8px; border-radius:20px; letter-spacing:.02em; font-family:'Montserrat',sans-serif; }
.bg-g  { background:rgba(39,174,96,.10); color:var(--verde-esmeralda); }
.bg-b  { background:rgba(47,128,237,.10); color:var(--azul-rey); }
.bg-a  { background:rgba(242,153,74,.10); color:var(--naranja-calma); }
.bg-r  { background:rgba(235,87,87,.10); color:var(--rojo-coral); }

/* Bottom grid — 3 columnas para gráficas */
.bottom-grid { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1fr) minmax(0,1fr); gap:14px; }

/* ── CHART WRAPPERS ──
   CRÍTICO: Chart.js necesita un contenedor con height definido
   cuando maintainAspectRatio:false está activo.
   La maqueta usa height:180px para chart-wrap y 140px para el donut.
*/
.chart-wrap {
  position:relative; width:100%;
  height:180px;    /* Igual que maq_escolares.html */
}
.chart-wrap--sm {
  height:140px;    /* Donut más pequeño — igual que maqueta */
}

/* Inscripciones mini (contadores bajo el donut) */
.ins-mini { display:flex; gap:8px; }
.im       { flex:1; background:var(--gris-hielo); border:1px solid var(--gris-bordes); border-radius:8px; padding:10px; text-align:center; }
/* Valor numérico — Montserrat Bold del PDF */
.im-v     { font-size:16px; font-weight:700; color:var(--carbon-oscuro); font-family:'Montserrat',sans-serif; }
.im-l     { font-size:10px; color:var(--gris-pizarra); margin-top:2px; font-family:'Montserrat',sans-serif; }

/* Estado vacío de gráficas */
.ev { display:flex; align-items:center; justify-content:center; padding:2rem; color:var(--gris-medio); font-size:11px; font-family:'Montserrat',sans-serif; }

/* Alerta de error carga — fondo #FFF0F0 del PDF (Rosa Alerta) */
.alerta-err {
  display:flex; align-items:center; gap:10px;
  background:var(--rosa-alerta); border:1px solid rgba(235,87,87,.20);
  border-radius:10px; padding:12px 16px;
  font-size:.9rem; color:var(--rojo-coral); font-weight:500;
  font-family:'Montserrat',sans-serif;
}
.alerta-err svg { stroke:var(--rojo-coral); flex-shrink:0; }
/* Botón reintentar — rojo coral del PDF */
.btn-reintentar {
  margin-left:auto; background:var(--rojo-coral); color:var(--blanco);
  border:none; padding:6px 16px; border-radius:6px;
  font-weight:600; font-size:.85rem; cursor:pointer;
  font-family:'Montserrat',sans-serif; transition:background .15s; white-space:nowrap;
}
.btn-reintentar:hover { background:#c0392b; }

/* Footer */
.pie { text-align:center; color:var(--gris-carga); font-size:.82rem; padding:2rem 0; border-top:1px solid var(--gris-bordes); font-family:'Montserrat',sans-serif; }

/* ══ TRANSICIONES ══ */
.fade-enter-active,.fade-leave-active { transition:opacity .2s ease; }
.fade-enter-from,.fade-leave-to { opacity:0; }
.fade-slide-enter-active,.fade-slide-leave-active { transition:all .25s ease; }
.fade-slide-enter-from,.fade-slide-leave-to { opacity:0; transform:translateY(-6px); }
.tab-fade-enter-active,.tab-fade-leave-active { transition:all .18s ease; }
.tab-fade-enter-from,.tab-fade-leave-to { opacity:0; transform:translateY(4px); }
.resultado-appear-enter-active { transition:all .3s ease; }
.resultado-appear-enter-from   { opacity:0; transform:translateY(8px); }

/* ══ RESPONSIVE ══ */
@media (max-width:1024px) {
  .kpi-grid    { grid-template-columns:repeat(2,1fr); }
  .info-grid   { grid-template-columns:1fr; }
  .acad-grid   { grid-template-columns:repeat(2,1fr); }
  .bottom-grid { grid-template-columns:1fr 1fr; }
  .main-grid   { grid-template-columns:1fr; }
}
@media (max-width:768px) {
  .hero             { flex-direction:column; align-items:flex-start; gap:14px; }
  .hero-stats       { flex-direction:row; }
  .hero-title       { font-size:18px; }
  .hero-form        { flex-direction:column; }
  .hero-btn         { width:100%; justify-content:center; }
  .bottom-grid      { grid-template-columns:1fr; }
  .al-hdr           { flex-direction:column; gap:1rem; padding:1.25rem; }
  .al-acciones      { flex-direction:row; width:100%; }
  .tab-body         { padding:1.25rem; }
  .se-detalle-grid  { grid-template-columns:1fr; }
  .se-campo.se-full { grid-column:1; }
}
@media (max-width:640px) {
  .kpi-grid         { grid-template-columns:1fr; }
  .al-acciones      { flex-direction:column; }
  .btn-primary, .btn-outline { width:100%; justify-content:center; }
  .acad-grid        { grid-template-columns:repeat(2,1fr); }
  .se-modal-footer  { flex-direction:column; }
  .se-modal-footer button { width:100%; justify-content:center; }
}

/* ══════════════════════════════════════════════════════════════
   MODAL EXPEDIENTE — copiado de AlumnosSE, prefijo se-
══════════════════════════════════════════════════════════════ */
.se-modal-overlay {
  position:fixed; inset:0;
  background:rgba(15,23,42,.55);
  backdrop-filter:blur(3px);
  display:flex; align-items:center; justify-content:center;
  z-index:1000; padding:16px;
}
.se-modal-content {
  background:#fff; border-radius:16px;
  width:100%; max-width:660px; max-height:90vh;
  display:flex; flex-direction:column;
  box-shadow:0 20px 60px rgba(0,0,0,.2); overflow:hidden;
}
.se-modal-header {
  display:flex; align-items:center; justify-content:space-between;
  padding:20px 22px 18px;
  background:linear-gradient(135deg,#1B396A 0%,#1D4ED8 100%);
  flex-shrink:0;
}
.se-modal-avatar-group { display:flex; gap:14px; align-items:center; }
.se-avatar {
  width:50px; height:50px; border-radius:12px;
  display:flex; align-items:center; justify-content:center;
  border:2px solid rgba(255,255,255,.3); flex-shrink:0;
}
.se-avatar span { color:#fff; font-weight:800; font-size:1.1rem; }
.avatar-0 { background:linear-gradient(135deg,#1B396A,#2563EB); }
.avatar-1 { background:linear-gradient(135deg,#7C3AED,#A78BFA); }
.avatar-2 { background:linear-gradient(135deg,#0891B2,#38BDF8); }
.avatar-3 { background:linear-gradient(135deg,#059669,#34D399); }
.avatar-4 { background:linear-gradient(135deg,#DC2626,#F87171); }
.se-modal-header-info { display:flex; flex-direction:column; gap:3px; }
.se-modal-tag    { font-size:.68rem; font-weight:700; letter-spacing:.1em; color:rgba(255,255,255,.7); }
.se-modal-nombre { margin:0; font-size:1.05rem; font-weight:800; color:#fff; letter-spacing:-.01em; }
.se-modal-control{ font-size:.82rem; font-weight:700; font-family:monospace; color:rgba(255,255,255,.8); }
.se-btn-cerrar {
  background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.2);
  color:#fff; cursor:pointer; border-radius:8px;
  display:flex; align-items:center; justify-content:center;
  width:36px; height:36px; flex-shrink:0; transition:background .15s;
}
.se-btn-cerrar:hover { background:rgba(255,255,255,.2); }
.se-modal-body-tabs { display:flex; flex-direction:column; flex:1; overflow:hidden; }
.se-detalle-tabs    { display:flex; background:#F8FAFC; border-bottom:1px solid #E5E7EB; flex-shrink:0; }
.se-tab-btn {
  flex:1; display:flex; align-items:center; justify-content:center; gap:6px;
  padding:12px 8px; background:none; border:none;
  border-bottom:2px solid transparent; cursor:pointer;
  font-size:.78rem; font-weight:700; letter-spacing:.05em; color:#9CA3AF;
  font-family:'Montserrat',sans-serif; transition:all .15s;
}
.se-tab-btn:hover  { color:#1B396A; }
.se-tab-btn.activo { color:#1B396A; border-bottom-color:#1B396A; background:#fff; }
.se-tab-scroll { overflow-y:auto; padding:1.4rem 1.6rem; flex:1; }
.se-tab-panel  { display:flex; flex-direction:column; gap:1rem; }
.se-exp-seccion {
  border:1.5px solid #E5E7EB; border-radius:12px; overflow:hidden; background:#fff;
}
.se-exp-titulo {
  display:flex; align-items:center; gap:7px;
  background:#F0F5FF; padding:10px 14px;
  font-size:.72rem; font-weight:800; letter-spacing:.08em;
  color:#1B396A; border-bottom:1px solid #DBEAFE;
}
.se-exp-titulo svg { stroke:#1B396A; flex-shrink:0; }
.se-detalle-grid {
  display:grid; grid-template-columns:1fr 1fr; gap:.9rem; padding:14px;
}
.se-campo {
  display:flex; flex-direction:column; gap:4px;
  padding:.8rem 1rem; background:#F8FAFC;
  border-radius:9px; border:1px solid #E5E7EB;
}
.se-campo.se-full { grid-column:1/-1; }
.se-label { font-size:.68rem; font-weight:700; color:#9CA3AF; letter-spacing:.08em; }
.se-valor { font-size:.92rem; font-weight:600; color:#1A1A1A; }
.se-mono  { font-family:'Courier New',monospace; font-weight:800; font-size:1rem; color:#1B396A; }
.se-num   { font-size:1.1rem; font-weight:800; color:#1B396A; }
.se-estatus-badge {
  display:inline-flex; align-items:center;
  padding:5px 14px; border-radius:20px;
  font-size:.78rem; font-weight:800; letter-spacing:.06em; width:fit-content;
}
.badge-activo     { background:#DCFCE7; color:#15803D; }
.badge-temporal   { background:#FEF3C7; color:#B45309; }
.badge-definitiva { background:#FEE2E2; color:#B91C1C; }
.badge-titulado   { background:#EDE9FE; color:#6D28D9; }
.badge-egresado   { background:#E0F2FE; color:#0369A1; }
.badge-desconocido{ background:#F3F4F6; color:#6B7280; }
.se-creditos-bloque {
  background:#F8FAFC; border-radius:10px; padding:12px 14px;
  border:1px solid #E5E7EB; margin-bottom:12px;
}
.se-creditos-row  { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
.se-creditos-lbl  { font-weight:700; color:#1A1A1A; font-size:.8rem; letter-spacing:.04em; }
.se-creditos-pct  { font-weight:700; color:#6B7280; font-size:.8rem; }
.se-creditos-pct.verde { color:#16A34A; }
.se-creditos-track{ height:8px; background:#E5E7EB; border-radius:4px; overflow:hidden; }
.se-creditos-fill { height:100%; border-radius:4px; transition:width .8s ease; }
.se-kardex-semestres { display:flex; flex-direction:column; gap:.5rem; }
.se-semestre-bloque  { border:1px solid #E5E7EB; border-radius:10px; overflow:hidden; background:#fff; }
.se-semestre-btn {
  width:100%; display:flex; align-items:center; justify-content:space-between;
  padding:10px 14px; background:none; border:none; cursor:pointer;
  font-family:'Montserrat',sans-serif; transition:background .15s;
}
.se-semestre-btn:hover   { background:#F8FAFC; }
.se-semestre-btn.abierto { background:#EFF6FF; }
.se-semestre-titulo { font-size:.85rem; font-weight:800; color:#1A1A1A; letter-spacing:.04em; }
.se-semestre-badges { display:flex; align-items:center; gap:5px; }
.se-badge-mini { font-size:.68rem; font-weight:800; padding:2px 8px; border-radius:20px; letter-spacing:.04em; }
.se-badge-mini.acreditadas { background:#DCFCE7; color:#16A34A; }
.se-badge-mini.reprobadas  { background:#FEE2E2; color:#DC2626; }
.se-flecha { stroke:#6B7280; transition:transform .2s; flex-shrink:0; }
.se-flecha.girado { transform:rotate(180deg); }
.se-semestre-materias { border-top:1px solid #E5E7EB; }
.se-tabla-mini { width:100%; border-collapse:collapse; font-size:.8rem; }
.se-tabla-mini th {
  background:#F8FAFC; padding:7px 12px; text-align:left;
  font-size:.68rem; font-weight:800; color:#9CA3AF;
  text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid #E5E7EB;
}
.se-tabla-mini td { padding:7px 12px; border-bottom:.5px solid #F9FAFB; vertical-align:middle; color:#1A1A1A; font-weight:500; }
.se-tabla-mini tr.fila-repr { background:#FEF2F2; }
.se-tabla-mini td.centrado  { text-align:center; }
.se-clave-mono { font-family:monospace; font-size:.78rem; font-weight:800; color:#1B396A; }
.se-badge-estado { font-size:.68rem; font-weight:800; padding:2px 8px; border-radius:10px; display:inline-block; letter-spacing:.03em; }
.text-verde { color:#16A34A; } .text-rojo { color:#DC2626; } .text-gris { color:#9CA3AF; }
.se-horario-lista { display:flex; flex-direction:column; gap:.6rem; }
.se-horario-item  { display:flex; align-items:stretch; border:1px solid #E5E7EB; border-radius:9px; overflow:hidden; background:#fff; }
.se-horario-color { width:5px; flex-shrink:0; }
.se-horario-info  { padding:10px 12px; display:flex; flex-direction:column; gap:2px; }
.se-horario-nombre{ font-weight:800; font-size:.86rem; color:#1A1A1A; }
.se-horario-meta  { font-size:.75rem; color:#6B7280; font-weight:600; }
.se-horario-aula  { font-size:.72rem; color:#9CA3AF; font-weight:600; letter-spacing:.03em; }
.se-kardex-cargando { display:flex; flex-direction:column; gap:8px; }
.se-sk-linea {
  height:14px; border-radius:6px;
  background:linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%);
  background-size:200% 100%; animation:se-shimmer 1.4s infinite;
}
.se-sk-linea.largo { width:70%; } .se-sk-linea.medio { width:45%; }
.se-sk-fila {
  height:40px; border-radius:8px;
  background:linear-gradient(90deg,#F3F4F6 25%,#FFF 50%,#F3F4F6 75%);
  background-size:200% 100%; animation:se-shimmer 1.4s infinite;
}
@keyframes se-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
.se-kardex-error {
  display:flex; align-items:center; gap:8px; padding:14px;
  background:#FEF2F2; border-radius:8px; font-size:.85rem; font-weight:600; color:#DC2626;
}
.se-kardex-error button {
  margin-left:auto; padding:5px 12px; border-radius:6px;
  border:1.5px solid #DC2626; background:none; color:#DC2626;
  font-family:'Montserrat',sans-serif; font-weight:700; font-size:.78rem;
  letter-spacing:.04em; cursor:pointer;
}
.se-kardex-vacio {
  display:flex; flex-direction:column; align-items:center;
  padding:30px; text-align:center; color:#9CA3AF;
  font-size:.85rem; font-weight:600; letter-spacing:.04em; gap:8px;
}
.se-modal-footer {
  padding:14px 20px; background:#F8FAFC;
  display:flex; gap:8px; justify-content:flex-end;
  border-top:1px solid #E5E7EB; flex-shrink:0; flex-wrap:wrap;
}
.se-btn-sec {
  display:flex; align-items:center; gap:6px;
  padding:9px 18px; border-radius:9px; font-weight:700; cursor:pointer;
  font-family:'Montserrat',sans-serif; background:#fff; color:#374151;
  border:1.5px solid #D1D5DB; transition:background .15s;
  font-size:.82rem; letter-spacing:.04em;
}
.se-btn-sec:hover { background:#F3F4F6; }
.se-btn-outline {
  display:flex; align-items:center; gap:7px;
  padding:9px 16px; border-radius:9px; font-weight:700; cursor:pointer;
  font-family:'Montserrat',sans-serif; background:#fff; color:#1B396A;
  border:1.5px solid #1B396A; transition:background .15s;
  font-size:.82rem; letter-spacing:.04em;
}
.se-btn-outline:hover { background:#EFF6FF; }
.modal-fade-enter-active,.modal-fade-leave-active { transition:all .25s cubic-bezier(.4,0,.2,1); }
.modal-fade-enter-from,.modal-fade-leave-to { opacity:0; }
.modal-fade-enter-from .se-modal-content,
.modal-fade-leave-to   .se-modal-content { transform:scale(0.95) translateY(10px); }
.expand-enter-active,.expand-leave-active { transition:all .25s ease; overflow:hidden; }
.expand-enter-from,.expand-leave-to { max-height:0; opacity:0; }
.expand-enter-to,.expand-leave-from { max-height:500px; opacity:1; }
</style>