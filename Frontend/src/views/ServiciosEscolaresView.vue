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

      <!-- ══════════════════════════════════════════════════
           RESULTADO DEL ALUMNO (aparece al encontrar uno)
      ══════════════════════════════════════════════════ -->
      <Transition name="resultado-appear">
        <section v-if="alumno && estadoBusqueda === 'exito'" class="resultado">

          <!-- Header del alumno -->
          <div class="al-hdr">
            <div class="al-av">{{ iniciales(alumno.nombre) }}</div>
            <div class="al-info">
              <div class="al-nombre-row">
                <h2 class="al-nombre">{{ alumno.nombre }}</h2>
                <span class="al-estatus" :class="estatusClass(alumno.estatus)">{{ alumno.estatus }}</span>
              </div>
              <p class="al-meta">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"/>
                </svg>
                N° Control: <strong>{{ alumno.numero_control }}</strong>
              </p>
              <p class="al-meta">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z"/>
                </svg>
                {{ alumno.carrera }} · Semestre {{ alumno.semestre_actual ?? alumno.semestre }}
              </p>
              <p class="al-meta">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                {{ alumno.email }}
              </p>
            </div>
            <div class="al-acciones">
              <button class="btn-outline" @click="limpiar" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Nueva búsqueda
              </button>
              <button class="btn-primary" @click="irExpediente" type="button">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ver expediente completo
              </button>
            </div>
          </div>

          <!-- Tabs de detalle -->
          <div class="tabs-bar">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              class="tab-btn"
              :class="{ 'tab-act': tabActivo === tab.id }"
              @click="tabActivo = tab.id"
              type="button"
            >
              {{ tab.label }}
            </button>
          </div>

          <!-- Contenido de cada Tab -->
          <Transition name="tab-fade" mode="out-in">

            <!-- Tab: Datos Generales -->
            <div v-if="tabActivo === 'general'" key="general" class="tab-body">
              <div class="info-grid">
                <div class="info-card">
                  <h3 class="info-t">Información Personal</h3>
                  <dl class="info-list">
                    <div class="if"><dt>Nombre completo</dt><dd>{{ alumno.nombre }}</dd></div>
                    <div class="if"><dt>Número de control</dt><dd class="mono">{{ alumno.numero_control }}</dd></div>
                    <div class="if"><dt>CURP</dt><dd class="mono">{{ alumno.curp ?? 'No registrado' }}</dd></div>
                    <div class="if"><dt>Fecha de nacimiento</dt><dd>{{ fFecha(alumno.fecha_nacimiento) }}</dd></div>
                    <div class="if"><dt>Correo institucional</dt><dd>{{ alumno.email }}</dd></div>
                    <div class="if"><dt>Teléfono</dt><dd>{{ alumno.telefono ?? 'No registrado' }}</dd></div>
                  </dl>
                </div>
                <div class="info-card">
                  <h3 class="info-t">Información Académica</h3>
                  <dl class="info-list">
                    <div class="if"><dt>Carrera</dt><dd>{{ alumno.carrera }}</dd></div>
                    <div class="if"><dt>Semestre actual</dt><dd>{{ alumno.semestre_actual ?? alumno.semestre }}° Semestre</dd></div>
                    <div class="if"><dt>Periodo de ingreso</dt><dd>{{ alumno.fecha_ingreso ?? alumno.periodo_ingreso ?? 'No registrado' }}</dd></div>
                    <div class="if"><dt>Plan de estudios</dt><dd>{{ alumno.plan_estudios ?? 'No registrado' }}</dd></div>
                    <div class="if"><dt>Modalidad</dt><dd>{{ alumno.modalidad ?? 'Escolarizado' }}</dd></div>
                    <div class="if"><dt>Estatus</dt><dd><span class="chip" :class="estatusClass(alumno.estatus)">{{ alumno.estatus }}</span></dd></div>
                  </dl>
                </div>
              </div>
            </div>

            <!-- Tab: Estado Académico -->
            <div v-else-if="tabActivo === 'academico'" key="academico" class="tab-body">
              <div class="acad-grid">
                <div class="acad-kpi"><div class="acad-val azul">{{ alumno.estado_academico?.promedio ?? '—' }}</div><div class="acad-lbl">Promedio General</div></div>
                <div class="acad-kpi"><div class="acad-val verde">{{ alumno.estado_academico?.creditos_obtenidos ?? alumno.kardex_resumen?.creditos_acumulados ?? '—' }}</div><div class="acad-lbl">Créditos Obtenidos</div></div>
                <div class="acad-kpi"><div class="acad-val naranja">{{ alumno.estado_academico?.materias_cursadas ?? '—' }}</div><div class="acad-lbl">Materias Cursadas</div></div>
                <div class="acad-kpi">
                  <div class="acad-val" :class="(alumno.estado_academico?.materias_reprobadas ?? 0) > 0 ? 'rojo' : 'verde'">
                    {{ alumno.estado_academico?.materias_reprobadas ?? '0' }}
                  </div>
                  <div class="acad-lbl">Materias Reprobadas</div>
                </div>
              </div>
              <div v-if="alumno.estado_academico" class="prog-card">
                <div class="prog-hdr">
                  <span class="prog-lbl">Avance en créditos de la carrera</span>
                  <span class="prog-pct">{{ Math.round((alumno.estado_academico.creditos_obtenidos / alumno.estado_academico.creditos_totales) * 100) }}%</span>
                </div>
                <div class="prog-bg">
                  <div class="prog-fill"
                    :style="{ width: Math.round((alumno.estado_academico.creditos_obtenidos / alumno.estado_academico.creditos_totales) * 100) + '%' }">
                  </div>
                </div>
                <p class="prog-det">{{ alumno.estado_academico.creditos_obtenidos }} de {{ alumno.estado_academico.creditos_totales }} créditos</p>
              </div>
            </div>

            <!-- Tab: Kardex -->
            <div v-else-if="tabActivo === 'kardex'" key="kardex" class="tab-body">
              <div v-if="alumno.kardex && alumno.kardex.length > 0">
                <div v-for="(per, pi) in alumno.kardex" :key="pi" class="kardex-per">
                  <div class="kardex-per-hdr">
                    <span class="kardex-per-n">{{ per.nombre }}</span>
                    <span class="kardex-per-p">Promedio: <strong>{{ per.promedio }}</strong></span>
                  </div>
                  <div class="tabla-wrap">
                    <table class="tabla">
                      <thead>
                        <tr><th>Clave</th><th>Materia</th><th>Créd.</th><th>Calif.</th><th>Estatus</th></tr>
                      </thead>
                      <tbody>
                        <tr v-for="(m, mi) in per.materias" :key="mi">
                          <td class="mono sm">{{ m.clave }}</td>
                          <td>{{ m.nombre }}</td>
                          <td class="tc">{{ m.creditos }}</td>
                          <td class="tc"><span class="calif" :class="califClass(m.calificacion)">{{ m.calificacion }}</span></td>
                          <td class="tc"><span class="chip-mini" :class="m.estatus==='Aprobada' ? 'ch-v' : m.estatus==='Reprobada' ? 'ch-r' : 'ch-g'">{{ m.estatus }}</span></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div v-else class="tab-vacio">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24"
                  stroke="#BDBDBD" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p>No hay información de kardex disponible para este alumno.</p>
              </div>
            </div>

            <!-- Tab: Horario -->
            <div v-else-if="tabActivo === 'horario'" key="horario" class="tab-body">
              <div v-if="alumno.horario && alumno.horario.length > 0" class="horario-list">
                <div v-for="(c, ci) in alumno.horario" :key="ci" class="horario-card">
                  <div class="hor-dia">{{ c.dia }}</div>
                  <div class="hor-info">
                    <p class="hor-mat">{{ c.materia }}</p>
                    <p class="hor-det">{{ c.hora_inicio }} – {{ c.hora_fin }} · {{ c.aula }}</p>
                  </div>
                  <div class="hor-doc">
                    <span class="hor-doc-l">Docente</span>
                    <span class="hor-doc-n">{{ c.docente }}</span>
                  </div>
                </div>
              </div>
              <div v-else class="tab-vacio">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24"
                  stroke="#BDBDBD" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p>No hay horario registrado para el periodo actual.</p>
              </div>
            </div>

            <!-- Tab: Adicional -->
            <div v-else-if="tabActivo === 'adicional'" key="adicional" class="tab-body">
              <div class="info-grid">
                <div class="info-card">
                  <h3 class="info-t">Adeudos y Documentos</h3>
                  <dl class="info-list">
                    <div class="if"><dt>Adeudo financiero</dt><dd><span class="chip" :class="(alumno.adeudo ?? 0) > 0 ? 'ch-r' : 'ch-v'">{{ (alumno.adeudo ?? 0) > 0 ? `$${alumno.adeudo} MXN` : 'Sin adeudo' }}</span></dd></div>
                    <div class="if"><dt>Acta de nacimiento</dt><dd>{{ alumno.documentos?.acta_nacimiento ? 'Entregado' : 'Pendiente' }}</dd></div>
                    <div class="if"><dt>CURP certificada</dt><dd>{{ alumno.documentos?.curp ? 'Entregado' : 'Pendiente' }}</dd></div>
                    <div class="if"><dt>Certificado de secundaria</dt><dd>{{ alumno.documentos?.cert_secundaria ? 'Entregado' : 'Pendiente' }}</dd></div>
                    <div class="if"><dt>Fotografías</dt><dd>{{ alumno.documentos?.fotografias ? 'Entregado' : 'Pendiente' }}</dd></div>
                  </dl>
                </div>
                <div class="info-card">
                  <h3 class="info-t">Contacto de Emergencia</h3>
                  <dl class="info-list">
                    <div class="if"><dt>Nombre</dt><dd>{{ alumno.contacto_emergencia?.nombre ?? 'No registrado' }}</dd></div>
                    <div class="if"><dt>Parentesco</dt><dd>{{ alumno.contacto_emergencia?.parentesco ?? 'No registrado' }}</dd></div>
                    <div class="if"><dt>Teléfono</dt><dd>{{ alumno.contacto_emergencia?.telefono ?? 'No registrado' }}</dd></div>
                  </dl>
                </div>
              </div>
            </div>

          </Transition>
        </section>
      </Transition>

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
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()
const API    = `${import.meta.env.VITE_API_URL}/api`

// ── Refs canvas (gráficas) ─────────────────────────────────────────────
const c1 = ref(null)
const c2 = ref(null)
const c3 = ref(null)
let chart1 = null, chart2 = null, chart3 = null

// ── Búsqueda ──────────────────────────────────────────────────────────
const inputRef       = ref(null)
const numCtrl        = ref('')
const ultimaBusq     = ref('')
const inputFocused   = ref(false)
const estadoBusqueda = ref('idle')   // idle | cargando | exito | no-encontrado | error
const mensajeError   = ref('')
const alumno         = ref(null)
const tabActivo      = ref('general')

const tabs = [
  { id: 'general',   label: 'Datos Generales'  },
  { id: 'academico', label: 'Estado Académico' },
  { id: 'kardex',    label: 'Kardex'           },
  { id: 'horario',   label: 'Horario'          },
  { id: 'adicional', label: 'Adicional'        },
]

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

const buscar = async () => {
  const nc = numCtrl.value.trim()
  if (!nc) return
  estadoBusqueda.value = 'cargando'
  ultimaBusq.value     = nc
  alumno.value         = null
  tabActivo.value      = 'general'
  try {
    const res = await fetch(`${API}/alumnos/buscar?numero_control=${encodeURIComponent(nc)}`)
    if (res.status === 404) { estadoBusqueda.value = 'no-encontrado'; return }
    if (!res.ok) throw new Error(`Error ${res.status}`)
    const data = await res.json()
    if (!data || (!data.id && !data.numero_control)) { estadoBusqueda.value = 'no-encontrado'; return }
    alumno.value         = data
    estadoBusqueda.value = 'exito'
    mostrarNotif(`Alumno encontrado: ${data.nombre}`, 'exito')
  } catch (e) {
    console.error('[Escolares] buscar:', e)
    estadoBusqueda.value = 'error'
    mensajeError.value   = 'Ocurrió un error al conectar con el servidor. Intenta de nuevo.'
  }
}

const irExpediente = () => { if (alumno.value?.id) router.push(`/alumnos/${alumno.value.id}`) }

// ── KPIs dashboard ────────────────────────────────────────────────────
const cargando   = ref(false)
const errorCarga = ref(false)
const kpis = ref({
  alumnosActivos:          0,
  inscripcionesPeriodo:    0,
  inscripcionesCompletas:  0,
  inscripcionesPendientes: 0,
  pctInscripciones:        0,
  gruposAbiertos:          0,
  evaluacionesPendientes:  0,
})

const cargarDatos = async () => {
  cargando.value   = true
  errorCarga.value = false
  try {
    const res  = await fetch(`${API}/dashboard`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    kpis.value.alumnosActivos          = data.kpis?.alumnos       ?? 0
    kpis.value.inscripcionesPeriodo    = data.kpis?.inscripciones ?? 0
    kpis.value.gruposAbiertos          = data.kpis?.grupos        ?? 0
    kpis.value.evaluacionesPendientes  = data.kpis?.evaluaciones  ?? 0
    kpis.value.inscripcionesCompletas  = data.kpis?.ins_completas ?? Math.round((data.kpis?.inscripciones ?? 0) * 0.89)
    kpis.value.inscripcionesPendientes = data.kpis?.ins_pendientes ?? Math.round((data.kpis?.inscripciones ?? 0) * 0.11)
    kpis.value.pctInscripciones        = data.kpis?.pct_ins       ?? 89
  } catch {
    errorCarga.value = true
    // Fallback con datos realistas
    kpis.value = {
      alumnosActivos: 1235, inscripcionesPeriodo: 7020,
      inscripcionesCompletas: 1147, inscripcionesPendientes: 137,
      pctInscripciones: 89, gruposAbiertos: 72, evaluacionesPendientes: 0,
    }
  } finally {
    cargando.value = false
    nextTick(() => inicializarCharts())
  }
}

// ── Bitácora ──────────────────────────────────────────────────────────
const bitacora    = ref([])
const cargandoBit = ref(false)

const cargarBitacora = async () => {
  cargandoBit.value = true
  try {
    const res  = await fetch(`${API}/bitacora?limit=5`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    bitacora.value = Array.isArray(data) ? data : (data.bitacora ?? [])
  } catch { /* silencioso */ }
  finally { cargandoBit.value = false }
}

// ══════════════════════════════════════════════════════════════════════
// CHARTS con Chart.js (CDN)
// Configuración fiel al maq_escolares.html + paleta del PDF COLORIMETRIA
// ══════════════════════════════════════════════════════════════════════
const inicializarCharts = () => {
  if (typeof window === 'undefined' || !window.Chart) return
  const ChartJS = window.Chart

  // Defaults globales — fuente Montserrat del PDF
  ChartJS.defaults.font.family = "'Montserrat', system-ui, sans-serif"
  ChartJS.defaults.font.size   = 11
  ChartJS.defaults.color       = '#828282'  // Gris Medio Inactivo del PDF

  // ── CHART 1: Barras verticales — Alumnos por Carrera ──────────────
  // Colores: Bloque Azules + semántico del PDF (#132B4F → #F2994A)
  if (c1.value) {
    if (chart1) chart1.destroy()
    chart1 = new ChartJS(c1.value, {
      type: 'bar',
      data: {
        labels: ['ISC', 'Industrial', 'Eléctrica', 'Bioquímica', 'Mecánica', 'Empresarial'],
        datasets: [{
          data: [312, 268, 198, 176, 174, 156],
          // Paleta exacta del PDF — Bloque Azules de Cobalto Oscuro a Cyan + verde y naranja
          backgroundColor: ['#132B4F', '#1A4184', '#1D52B7', '#2F80ED', '#27AE60', '#F2994A'],
          borderRadius: 6,         // Esquinas redondeadas según maqueta
          borderSkipped: false,    // Redondear también la base
          hoverBackgroundColor: ['#0B2545', '#132B4F', '#1A4184', '#1D52B7', '#1e8449', '#d68910'],
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0B2545',      // Azul Marino Profundo del PDF
            titleColor: 'rgba(255,255,255,0.6)',
            bodyColor: '#FFFFFF',
            borderColor: '#1D52B7',
            borderWidth: 1,
            padding: 10,
            callbacks: { label: c => ' ' + c.parsed.y + ' alumnos' }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            border: { display: false },
            ticks: { font: { size: 10, family: "'Montserrat', sans-serif" }, color: '#828282' }
          },
          y: {
            grid: { color: '#F4F6F9' },   // Gris Hielo del PDF como líneas de cuadrícula
            border: { display: false },
            ticks: { font: { size: 10, family: "'Montserrat', sans-serif" }, color: '#828282' }
          }
        }
      }
    })
  }

  // ── CHART 2: Línea con fill — Inscripciones por Semestre ──────────
  // Color principal: #1D52B7 (Azul Rey de Enfoque — Primary Brand del PDF)
  // Fill: rgba(29,82,183,0.07) según PDF: "Ideal para cartas modernas como sombra suave"
  if (c2.value) {
    if (chart2) chart2.destroy()
    chart2 = new ChartJS(c2.value, {
      type: 'line',
      data: {
        labels: ['1°', '2°', '3°', '4°', '5°', '6°', '7°', '8°'],
        datasets: [{
          data: [180, 165, 158, 144, 152, 138, 175, 172],
          borderColor: '#1D52B7',                    // Azul Rey — Primary Brand
          backgroundColor: 'rgba(29,82,183,0.07)',   // Sombra suave del PDF
          borderWidth: 2.5,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#FFFFFF',           // Blanco Absoluto del PDF
          pointBorderColor: '#1D52B7',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointHoverBackgroundColor: '#1D52B7',
          pointHoverBorderColor: '#FFFFFF',
          pointHoverBorderWidth: 2,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0B2545',
            titleColor: 'rgba(255,255,255,0.6)',
            bodyColor: '#FFFFFF',
            borderColor: '#1D52B7',
            borderWidth: 1,
            padding: 10,
            callbacks: { label: c => ' ' + c.parsed.y + ' alumnos' }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            border: { display: false },
            ticks: { font: { size: 10, family: "'Montserrat', sans-serif" }, color: '#828282' }
          },
          y: {
            grid: { color: '#F4F6F9' },
            border: { display: false },
            min: 100,
            ticks: { font: { size: 10, family: "'Montserrat', sans-serif" }, color: '#828282' }
          }
        }
      }
    })
  }

  // ── CHART 3: Donut — Estado de Inscripciones ─────────────────────
  // cutout 72% igual que maqueta
  // Color principal: #1D52B7 (completas), pendientes rgba(242,153,74,0.15)
  // Borde naranja #F2994A (Naranja Calma — Advertencia del PDF)
  if (c3.value) {
    if (chart3) chart3.destroy()
    const pct = kpis.value.pctInscripciones || 89
    chart3 = new ChartJS(c3.value, {
      type: 'doughnut',
      data: {
        labels: [`Completadas ${pct}%`, `Pendientes ${100 - pct}%`],
        datasets: [{
          data: [pct, 100 - pct],
          backgroundColor: ['#1D52B7', 'rgba(242,153,74,0.15)'],  // PDF: Azul Rey + Naranja con 85% transparencia
          borderColor: ['#1D52B7', '#F2994A'],                     // PDF: Azul Rey + Naranja Calma
          borderWidth: 1,
          hoverOffset: 4,
          hoverBackgroundColor: ['#1A4184', 'rgba(242,153,74,0.25)'],
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',              // Grosor del anillo — igual que maqueta
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0B2545',
            bodyColor: '#FFFFFF',
            borderColor: '#1D52B7',
            borderWidth: 1,
            padding: 10,
            callbacks: { label: c => ' ' + c.label }
          }
        }
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

// ── Helpers ───────────────────────────────────────────────────────────
const fmt      = (n) => n?.toLocaleString('es-MX') ?? '0'
const iniciales = (n = '') => n.split(' ').slice(0,2).map(p => p[0]).join('').toUpperCase()

const fFecha = (iso) => {
  if (!iso) return 'No registrado'
  try { return new Date(iso).toLocaleDateString('es-MX', { day:'2-digit', month:'long', year:'numeric' }) }
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

const tRel = (f) => {
  if (!f) return ''
  const m = Math.floor((Date.now() - new Date(f).getTime()) / 60000)
  if (m < 1)  return 'Ahora'
  if (m < 60) return `Hace ${m} min`
  const h = Math.floor(m/60)
  if (h < 24) return `Hace ${h} h`
  return `Hace ${Math.floor(h/24)} día(s)`
}

const claseBadge = (a = '') => {
  const s = a.toLowerCase()
  if (s.includes('inscri') || s.includes('cre') || s.includes('alta')) return 'bg-g'
  if (s.includes('edit') || s.includes('actualiz'))                      return 'bg-a'
  if (s.includes('elim') || s.includes('baja'))                          return 'bg-r'
  return 'bg-b'
}

// ── Lifecycle ─────────────────────────────────────────────────────────
onMounted(() => {
  cargarDatos()
  cargarBitacora()
  nextTick(() => inputRef.value?.focus())

  // Carga Chart.js desde CDN si no está ya en window
  if (!window.Chart) {
    const script    = document.createElement('script')
    script.src      = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js'
    script.onload   = () => nextTick(() => inicializarCharts())
    document.head.appendChild(script)
  } else {
    nextTick(() => inicializarCharts())
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
}
@media (max-width:640px) {
  .kpi-grid         { grid-template-columns:1fr; }
  .al-acciones      { flex-direction:column; }
  .btn-primary, .btn-outline { width:100%; justify-content:center; }
  .acad-grid        { grid-template-columns:repeat(2,1fr); }
}
</style>
