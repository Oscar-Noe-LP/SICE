<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="servicios-page">

      <!-- ── Breadcrumb ─────────────────────────────────────────────── -->
      <nav class="breadcrumb">
        <router-link to="/inicio" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <span class="breadcrumb-actual">Servicios Escolares</span>
      </nav>

      <!-- ── Toast de notificación ──────────────────────────────────── -->
      <transition name="toast-slide">
        <div v-if="notificacion.visible" class="toast" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg"
               class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg"
               class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ══════════════════════════════════════════════════════════════
           SECCIÓN HERO: Buscador principal (elemento más prominente)
      ═══════════════════════════════════════════════════════════════ -->
      <section class="hero-busqueda">
        <div class="hero-contenido">
          <div class="hero-texto">
            <h1 class="hero-titulo">Servicios Escolares</h1>
            <p class="hero-subtitulo">Busca a un alumno por su número de control para ver su información completa</p>
          </div>

          <!-- Campo de búsqueda prominente -->
          <div class="buscador-wrapper">
            <div class="buscador-inner" :class="{ 'buscador-activo': inputFocused, 'buscador-error': estadoBusqueda === 'error' }">
              <!-- Icono lupa -->
              <svg class="buscador-icono-lupa" xmlns="http://www.w3.org/2000/svg"
                   fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>

              <input
                ref="inputBusqueda"
                v-model="numeroBusqueda"
                type="text"
                class="buscador-input"
                placeholder="Número de control (ej. 22031234)"
                maxlength="8"
                @keydown.enter="buscarAlumno"
                @focus="inputFocused = true"
                @blur="inputFocused = false"
                @input="onInputBusqueda"
              />

              <!-- Spinner de carga -->
              <div v-if="estadoBusqueda === 'cargando'" class="buscador-spinner">
                <svg class="spinner-svg" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/>
                  <path fill="currentColor" class="opacity-75"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
              </div>

              <!--  limpiar -->
              <button
                v-else-if="numeroBusqueda"
                class="buscador-limpiar"
                @click="limpiarBusqueda"
                title="Limpiar búsqueda"
              >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <!-- Botón Buscar -->
              <button
                class="buscador-btn"
                :disabled="estadoBusqueda === 'cargando' || !numeroBusqueda.trim()"
                @click="buscarAlumno"
              >
                <span v-if="estadoBusqueda !== 'cargando'">Buscar</span>
                <span v-else>Buscando...</span>
              </button>
            </div>

            <!-- Mensajes de estado debajo del buscador -->
            <transition name="fade-slide">
              <p v-if="estadoBusqueda === 'error'" class="busqueda-mensaje busqueda-mensaje--error">
                <svg xmlns="http://www.w3.org/2000/svg" class="msg-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ mensajeError }}
              </p>
              <p v-else-if="estadoBusqueda === 'no-encontrado'" class="busqueda-mensaje busqueda-mensaje--vacio">
                <svg xmlns="http://www.w3.org/2000/svg" class="msg-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                No se encontró ningún alumno con el número de control <strong>{{ ultimaBusqueda }}</strong>.
              </p>
              <p v-else-if="estadoBusqueda === 'idle'" class="busqueda-mensaje busqueda-mensaje--hint">
                <svg xmlns="http://www.w3.org/2000/svg" class="msg-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Ingresa el número de control de 8 dígitos y presiona Enter o el botón Buscar
              </p>
            </transition>
          </div>
        </div>
      </section>

      <!-- ══════════════════════════════════════════════════════════════
           RESULTADO: Información completa del alumno encontrado
      ═══════════════════════════════════════════════════════════════ -->
      <transition name="resultado-appear">
        <section v-if="alumno && estadoBusqueda === 'exito'" class="resultado-seccion">

          <!-- Cabecera del alumno -->
          <div class="alumno-header">
            <div class="alumno-avatar-wrap">
              <img
                v-if="alumno.foto"
                :src="alumno.foto"
                :alt="`Foto de ${alumno.nombre}`"
                class="alumno-avatar"
              />
              <div v-else class="alumno-avatar-placeholder">
                {{ iniciales(alumno.nombre) }}
              </div>
            </div>

            <div class="alumno-header-info">
              <div class="alumno-nombre-row">
                <h2 class="alumno-nombre">{{ alumno.nombre }}</h2>
                <span class="alumno-estatus" :class="estatusClass(alumno.estatus)">
                  {{ alumno.estatus }}
                </span>
              </div>
              <p class="alumno-nc">
                <svg xmlns="http://www.w3.org/2000/svg" class="info-icono-mini" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                </svg>
                N° Control: <strong>{{ alumno.numero_control }}</strong>
              </p>
              <p class="alumno-meta">
                <svg xmlns="http://www.w3.org/2000/svg" class="info-icono-mini" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 14l9-5-9-5-9 5 9 5z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
                {{ alumno.carrera }} · Semestre {{ alumno.semestre }}
              </p>
              <p class="alumno-meta">
                <svg xmlns="http://www.w3.org/2000/svg" class="info-icono-mini" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ alumno.email }}
              </p>
            </div>

            <div class="alumno-header-acciones">
              <button class="btn-accion btn-accion--outline" @click="limpiarBusqueda">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Nueva búsqueda
              </button>
              <button class="btn-accion btn-accion--primary" @click="irExpediente">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Ver expediente completo
              </button>
            </div>
          </div>

          <!-- ── Tabs de información ─────────────────────────────────── -->
          <div class="tabs-barra">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              class="tab-btn"
              :class="{ 'tab-btn--activo': tabActivo === tab.id }"
              @click="tabActivo = tab.id"
            >
              <svg class="tab-icono" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tab.icono" />
              </svg>
              {{ tab.label }}
            </button>
          </div>

          <!-- ── TAB: Datos Generales ───────────────────────────────── -->
          <transition name="tab-fade" mode="out-in">
            <div v-if="tabActivo === 'general'" key="general" class="tab-contenido">
              <div class="info-grid">

                <div class="info-card">
                  <h3 class="info-card-titulo">Información Personal</h3>
                  <dl class="info-lista">
                    <div class="info-fila">
                      <dt>Nombre completo</dt>
                      <dd>{{ alumno.nombre }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Número de control</dt>
                      <dd class="dd-mono">{{ alumno.numero_control }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>CURP</dt>
                      <dd class="dd-mono">{{ alumno.curp ?? 'No registrado' }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Fecha de nacimiento</dt>
                      <dd>{{ formatFecha(alumno.fecha_nacimiento) }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Correo institucional</dt>
                      <dd>{{ alumno.email }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Teléfono</dt>
                      <dd>{{ alumno.telefono ?? 'No registrado' }}</dd>
                    </div>
                  </dl>
                </div>

                <div class="info-card">
                  <h3 class="info-card-titulo">Información Académica</h3>
                  <dl class="info-lista">
                    <div class="info-fila">
                      <dt>Carrera</dt>
                      <dd>{{ alumno.carrera }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Semestre actual</dt>
                      <dd>{{ alumno.semestre }}° Semestre</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Periodo de ingreso</dt>
                      <dd>{{ alumno.periodo_ingreso ?? 'No registrado' }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Plan de estudios</dt>
                      <dd>{{ alumno.plan_estudios ?? 'No registrado' }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Modalidad</dt>
                      <dd>{{ alumno.modalidad ?? 'Escolarizado' }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Estatus</dt>
                      <dd>
                        <span class="chip" :class="estatusClass(alumno.estatus)">
                          {{ alumno.estatus }}
                        </span>
                      </dd>
                    </div>
                  </dl>
                </div>

              </div>
            </div>

            <!-- ── TAB: Estado Académico ───────────────────────────── -->
            <div v-else-if="tabActivo === 'academico'" key="academico" class="tab-contenido">
              <div class="academico-grid">

                <!-- Promedio general -->
                <div class="kpi-card">
                  <div class="kpi-valor kpi-azul">{{ alumno.estado_academico?.promedio ?? '—' }}</div>
                  <div class="kpi-label">Promedio General</div>
                </div>

                <!-- Créditos -->
                <div class="kpi-card">
                  <div class="kpi-valor kpi-verde">{{ alumno.estado_academico?.creditos_obtenidos ?? '—' }}</div>
                  <div class="kpi-label">Créditos Obtenidos</div>
                </div>

                <!-- Materias cursadas -->
                <div class="kpi-card">
                  <div class="kpi-valor kpi-naranja">{{ alumno.estado_academico?.materias_cursadas ?? '—' }}</div>
                  <div class="kpi-label">Materias Cursadas</div>
                </div>

                <!-- Materias reprobadas -->
                <div class="kpi-card">
                  <div class="kpi-valor" :class="(alumno.estado_academico?.materias_reprobadas ?? 0) > 0 ? 'kpi-rojo' : 'kpi-verde'">
                    {{ alumno.estado_academico?.materias_reprobadas ?? '0' }}
                  </div>
                  <div class="kpi-label">Materias Reprobadas</div>
                </div>

              </div>

              <!-- Barra de avance de créditos -->
              <div v-if="alumno.estado_academico" class="progreso-card">
                <div class="progreso-header">
                  <span class="progreso-label">Avance en créditos de la carrera</span>
                  <span class="progreso-pct">
                    {{ Math.round((alumno.estado_academico.creditos_obtenidos / alumno.estado_academico.creditos_totales) * 100) }}%
                  </span>
                </div>
                <div class="progreso-barra-bg">
                  <div
                    class="progreso-barra-fill"
                    :style="{
                      width: Math.round((alumno.estado_academico.creditos_obtenidos / alumno.estado_academico.creditos_totales) * 100) + '%'
                    }"
                  ></div>
                </div>
                <p class="progreso-detalle">
                  {{ alumno.estado_academico.creditos_obtenidos }} de {{ alumno.estado_academico.creditos_totales }} créditos
                </p>
              </div>

              <!-- Situación especial -->
              <div v-if="alumno.estado_academico?.situacion" class="situacion-banner"
                   :class="alumno.estado_academico.situacion === 'Regular' ? 'situacion-ok' : 'situacion-alerta'">
                <svg xmlns="http://www.w3.org/2000/svg" class="situacion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path v-if="alumno.estado_academico.situacion === 'Regular'"
                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Situación académica: <strong>{{ alumno.estado_academico.situacion }}</strong>
                <span v-if="alumno.estado_academico.observacion"> — {{ alumno.estado_academico.observacion }}</span>
              </div>
            </div>

            <!-- ── TAB: Kardex ──────────────────────────────────────── -->
            <div v-else-if="tabActivo === 'kardex'" key="kardex" class="tab-contenido">
              <div v-if="alumno.kardex && alumno.kardex.length > 0">
                <div v-for="(periodo, idx) in alumno.kardex" :key="idx" class="kardex-periodo">
                  <div class="kardex-periodo-header">
                    <span class="kardex-periodo-label">{{ periodo.nombre }}</span>
                    <span class="kardex-periodo-prom">Promedio: <strong>{{ periodo.promedio }}</strong></span>
                  </div>
                  <div class="kardex-tabla-wrap">
                    <table class="kardex-tabla">
                      <thead>
                        <tr>
                          <th>Clave</th>
                          <th>Materia</th>
                          <th>Créditos</th>
                          <th>Calificación</th>
                          <th>Estatus</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(mat, mi) in periodo.materias" :key="mi" class="kardex-fila">
                          <td class="td-mono">{{ mat.clave }}</td>
                          <td>{{ mat.nombre }}</td>
                          <td class="td-centro">{{ mat.creditos }}</td>
                          <td class="td-centro">
                            <span class="calif" :class="califClass(mat.calificacion)">
                              {{ mat.calificacion }}
                            </span>
                          </td>
                          <td class="td-centro">
                            <span class="chip-mini" :class="mat.estatus === 'Aprobada' ? 'chip-verde' : mat.estatus === 'Reprobada' ? 'chip-rojo' : 'chip-gris'">
                              {{ mat.estatus }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="kardex-footer">
                  <button class="btn-ver-kardex" @click="irKardex">
                    Ver kardex completo en detalle
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                </div>
              </div>
              <div v-else class="tab-vacio">
                <svg xmlns="http://www.w3.org/2000/svg" class="vacio-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p>No hay información de kardex disponible para este alumno.</p>
              </div>
            </div>

            <!-- ── TAB: Horario ─────────────────────────────────────── -->
            <div v-else-if="tabActivo === 'horario'" key="horario" class="tab-contenido">
              <div v-if="alumno.horario && alumno.horario.length > 0" class="horario-lista">
                <div
                  v-for="(clase, idx) in alumno.horario"
                  :key="idx"
                  class="horario-card"
                >
                  <div class="horario-dia">{{ clase.dia }}</div>
                  <div class="horario-info">
                    <p class="horario-materia">{{ clase.materia }}</p>
                    <p class="horario-detalle">{{ clase.hora_inicio }} – {{ clase.hora_fin }} · {{ clase.aula }}</p>
                  </div>
                  <div class="horario-docente">
                    <span class="horario-docente-label">Docente</span>
                    <span class="horario-docente-nombre">{{ clase.docente }}</span>
                  </div>
                </div>
              </div>
              <div v-else class="tab-vacio">
                <svg xmlns="http://www.w3.org/2000/svg" class="vacio-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p>No hay horario registrado para el periodo actual.</p>
              </div>
            </div>

            <!-- ── TAB: Información Adicional ─────────────────────── -->
            <div v-else-if="tabActivo === 'adicional'" key="adicional" class="tab-contenido">
              <div class="info-grid">

                <div class="info-card">
                  <h3 class="info-card-titulo">Adeudos y Documentos</h3>
                  <dl class="info-lista">
                    <div class="info-fila">
                      <dt>Adeudo financiero</dt>
                      <dd>
                        <span class="chip" :class="(alumno.adeudo ?? 0) > 0 ? 'chip-rojo' : 'chip-verde'">
                          {{ (alumno.adeudo ?? 0) > 0 ? `$${alumno.adeudo} MXN` : 'Sin adeudo' }}
                        </span>
                      </dd>
                    </div>
                    <div class="info-fila">
                      <dt>Acta de nacimiento</dt>
                      <dd>{{ alumno.documentos?.acta_nacimiento ? 'Entregado' : 'Pendiente' }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>CURP certificada</dt>
                      <dd>{{ alumno.documentos?.curp ? 'Entregado' : 'Pendiente' }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Certificado de secundaria</dt>
                      <dd>{{ alumno.documentos?.cert_secundaria ? 'Entregado' : 'Pendiente' }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Fotografías</dt>
                      <dd>{{ alumno.documentos?.fotografias ? 'Entregado' : 'Pendiente' }}</dd>
                    </div>
                  </dl>
                </div>

                <div class="info-card">
                  <h3 class="info-card-titulo">Contacto de Emergencia</h3>
                  <dl class="info-lista">
                    <div class="info-fila">
                      <dt>Nombre</dt>
                      <dd>{{ alumno.contacto_emergencia?.nombre ?? 'No registrado' }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Parentesco</dt>
                      <dd>{{ alumno.contacto_emergencia?.parentesco ?? 'No registrado' }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Teléfono</dt>
                      <dd>{{ alumno.contacto_emergencia?.telefono ?? 'No registrado' }}</dd>
                    </div>
                  </dl>
                </div>

              </div>
            </div>
          </transition>

        </section>
      </transition>

      <!-- ══════════════════════════════════════════════════════════════
           DASHBOARD: KPIs + Accesos Rápidos (visibles cuando no hay resultado)
      ═══════════════════════════════════════════════════════════════ -->
      <transition name="fade">
        <div v-if="!alumno || estadoBusqueda !== 'exito'">

          <!-- KPIs -->
          <div class="stats-grid">

            <div class="stat-card stat-azul">
              <div class="stat-icono-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" class="stat-icono" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
              <div class="stat-info">
                <p class="stat-etiqueta">Alumnos Activos</p>
                <div v-if="cargandoDash" class="stat-skeleton"></div>
                <p v-else class="stat-numero">{{ alumnosActivos }}</p>
                <span class="stat-link" @click="irAAlumnos">Ver alumnos →</span>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icono-wrapper stat-icono-verde">
                <svg xmlns="http://www.w3.org/2000/svg" class="stat-icono-verde-svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
              </div>
              <div class="stat-info">
                <p class="stat-etiqueta">Inscripciones del Periodo</p>
                <div v-if="cargandoDash" class="stat-skeleton"></div>
                <p v-else class="stat-numero">{{ inscripcionesPeriodo }}</p>
                <span class="stat-link" @click="irAInscripciones">Ver inscripciones →</span>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icono-wrapper stat-icono-naranja">
                <svg xmlns="http://www.w3.org/2000/svg" class="stat-icono-naranja-svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div class="stat-info">
                <p class="stat-etiqueta">Grupos Abiertos</p>
                <div v-if="cargandoDash" class="stat-skeleton"></div>
                <p v-else class="stat-numero">{{ gruposAbiertos }}</p>
                <span class="stat-link" @click="irAGrupos">Ver grupos →</span>
              </div>
            </div>

            <div class="stat-card">
              <div class="stat-icono-wrapper stat-icono-azul-suave">
                <svg xmlns="http://www.w3.org/2000/svg" class="stat-icono-azul-suave-svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
              </div>
              <div class="stat-info">
                <p class="stat-etiqueta">Evaluaciones Pendientes</p>
                <div v-if="cargandoDash" class="stat-skeleton"></div>
                <p v-else class="stat-numero">{{ evaluacionesPendientes }}</p>
                <span class="stat-link" @click="irAEvaluaciones">Ver evaluaciones →</span>
              </div>
            </div>

          </div>

          <!-- Error de carga del dashboard -->
          <div v-if="errorCarga" class="alerta-error">
            <svg xmlns="http://www.w3.org/2000/svg" class="alerta-icono" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>No se pudieron cargar los datos. Verifica la conexión con el servidor.</span>
            <button class="btn-reintentar" @click="cargarDatos">Reintentar</button>
          </div>

          <!-- Accesos Rápidos -->
          <div class="accesos-seccion">
            <h2 class="seccion-titulo">Accesos Rápidos</h2>
            <div class="accesos-grid">

              <div class="acceso-card" @click="nuevaInscripcion">
                <div class="acceso-icono acceso-verde">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                       stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                  </svg>
                </div>
                <div class="acceso-contenido">
                  <h4>Gestionar Inscripciones</h4>
                  <p>Da de alta a los alumnos</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>

              <div class="acceso-card" @click="irAGrupos">
                <div class="acceso-icono acceso-azul">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                       stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <div class="acceso-contenido">
                  <h4>Administrar Grupos</h4>
                  <p>Visualiza y organiza grupos</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>

              <div class="acceso-card" @click="irAEvaluaciones">
                <div class="acceso-icono acceso-amarillo">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                       stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                  </svg>
                </div>
                <div class="acceso-contenido">
                  <h4>Supervisar Evaluaciones</h4>
                  <p>Revisa evaluaciones pendientes</p>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" class="acceso-flecha" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>

              <div class="acceso-card acceso-deshabilitado">
                <div class="acceso-icono acceso-gris">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                       stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                  </svg>
                </div>
                <div class="acceso-contenido">
                  <h4>Consultar Kardex</h4>
                  <p>Revisa el expediente académico</p>
                </div>
                <span class="badge-proximamente">Próximamente</span>
              </div>

            </div>
          </div>

        </div>
      </transition>

      <div class="spacer"></div>

      <footer class="pie-pagina">
        © 2026 Tecnológico Nacional de México · Todos los derechos reservados
      </footer>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()
const API    = `${import.meta.env.VITE_API_URL}/api`

// ── Búsqueda de alumno ──────────────────────────────────────────────────────
const inputBusqueda   = ref(null)
const numeroBusqueda  = ref('')
const ultimaBusqueda  = ref('')
const inputFocused    = ref(false)
// Estados: 'idle' | 'cargando' | 'exito' | 'no-encontrado' | 'error'
const estadoBusqueda  = ref('idle')
const mensajeError    = ref('')
const alumno          = ref(null)

/** Tab activo del panel de alumno */
const tabActivo = ref('general')
const tabs = [
  {
    id: 'general',
    label: 'Datos Generales',
    icono: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
  },
  {
    id: 'academico',
    label: 'Estado Académico',
    icono: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
  },
  {
    id: 'kardex',
    label: 'Kardex',
    icono: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'
  },
  {
    id: 'horario',
    label: 'Horario',
    icono: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
  },
  {
    id: 'adicional',
    label: 'Adicional',
    icono: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
  }
]

/** Limpia el campo y reinicia el estado */
const limpiarBusqueda = () => {
  numeroBusqueda.value = ''
  alumno.value         = null
  estadoBusqueda.value = 'idle'
  mensajeError.value   = ''
  ultimaBusqueda.value = ''
  tabActivo.value      = 'general'
  nextTick(() => inputBusqueda.value?.focus())
}

/** Limpia el resultado al escribir de nuevo */
const onInputBusqueda = () => {
  if (estadoBusqueda.value !== 'cargando') {
    alumno.value         = null
    estadoBusqueda.value = 'idle'
  }
}

/** Ejecuta la búsqueda por número de control */
const buscarAlumno = async () => {
  const nc = numeroBusqueda.value.trim()
  if (!nc) return

  estadoBusqueda.value = 'cargando'
  ultimaBusqueda.value  = nc
  alumno.value          = null
  tabActivo.value       = 'general'

  try {
    const res = await fetch(`${API}/alumnos/buscar?numero_control=${encodeURIComponent(nc)}`)

    if (res.status === 404) {
      estadoBusqueda.value = 'no-encontrado'
      return
    }

    if (!res.ok) throw new Error(`Error ${res.status}`)

    const data = await res.json()

    if (!data || (!data.id && !data.numero_control)) {
      estadoBusqueda.value = 'no-encontrado'
      return
    }

    alumno.value         = data
    estadoBusqueda.value = 'exito'
    mostrarNotificacion(`Alumno encontrado: ${data.nombre}`, 'exito')

  } catch (err) {
    console.error('Error buscando alumno:', err)
    estadoBusqueda.value = 'error'
    mensajeError.value   = 'Ocurrió un error al conectar con el servidor. Intenta de nuevo.'
  }
}

// ── Dashboard (KPIs globales) ───────────────────────────────────────────────
const cargandoDash           = ref(false)
const errorCarga             = ref(false)
const alumnosActivos         = ref(0)
const inscripcionesPeriodo   = ref(0)
const gruposAbiertos         = ref(0)
const evaluacionesPendientes = ref(0)

const cargarDatos = async () => {
  cargandoDash.value = true
  errorCarga.value   = false
  try {
    const res = await fetch(`${API}/dashboard`)
    if (!res.ok) throw new Error('Error en la respuesta del servidor')
    const data = await res.json()
    alumnosActivos.value         = data.kpis?.alumnos       ?? 0
    inscripcionesPeriodo.value   = data.kpis?.inscripciones ?? 0
    gruposAbiertos.value         = data.kpis?.grupos        ?? 0
    evaluacionesPendientes.value = data.kpis?.evaluaciones  ?? 0
  } catch (err) {
    console.error('Error cargando dashboard:', err)
    errorCarga.value = true
  } finally {
    cargandoDash.value = false
  }
}

onMounted(() => {
  cargarDatos()
  nextTick(() => inputBusqueda.value?.focus())
})

// ── Toast de notificación ───────────────────────────────────────────────────
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Helpers de formato y clases ─────────────────────────────────────────────

/** Obtiene las iniciales del nombre para el avatar placeholder */
const iniciales = (nombre = '') =>
  nombre.split(' ').slice(0, 2).map(p => p[0]).join('').toUpperCase()

/** Clase CSS según el estatus del alumno */
const estatusClass = (estatus = '') => {
  const m = {
    'Activo':      'estatus-activo',
    'Inactivo':    'estatus-inactivo',
    'Egresado':    'estatus-egresado',
    'Suspendido':  'estatus-suspendido',
    'Baja':        'estatus-baja',
  }
  return m[estatus] ?? 'estatus-inactivo'
}

/** Clase CSS según la calificación (semáforo) */
const califClass = (cal) => {
  if (cal === null || cal === undefined) return 'calif-nd'
  if (cal >= 80) return 'calif-verde'
  if (cal >= 70) return 'calif-amarillo'
  return 'calif-rojo'
}

/** Formatea una fecha ISO a formato legible */
const formatFecha = (iso) => {
  if (!iso) return 'No registrado'
  try {
    return new Date(iso).toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' })
  } catch { return iso }
}

// ── Navegación ──────────────────────────────────────────────────────────────
const irAAlumnos       = () => router.push('/alumnos')
const irAInscripciones = () => router.push('/inscripcion')
const irAGrupos        = () => router.push('/gestion-grupos')
const irAEvaluaciones  = () => router.push('/evaluaciones')
const nuevaInscripcion = () => router.push('/inscripcion')

const irExpediente = () => {
  if (alumno.value?.id) router.push(`/alumnos/${alumno.value.id}`)
}
const irKardex = () => {
  if (alumno.value?.id) router.push(`/alumnos/${alumno.value.id}/kardex`)
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ── Variables globales de la vista ──────────────────────────────────────── */
.servicios-page {
  --azul:        #1B396A;
  --azul-hover:  #1D4ED8;
  --azul-suave:  #DBEAFE;
  --azul-light:  #EFF6FF;
  --borde:       #E5E7EB;
  --fondo:       #F5F5F5;
  --texto:       #1A1A1A;
  --gris:        #6B7280;
  --verde:       #16A34A;
  --verde-suave: #DCFCE7;
  --rojo:        #DC2626;
  --rojo-suave:  #FEF2F2;
  --amarillo:    #F59E0B;
  --amarillo-suave: #FEF3C7;

  width: 100%;
  min-height: 100%;
  display: flex;
  flex-direction: column;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

.spacer { flex: 1; }

/* ── Breadcrumb ──────────────────────────────────────────────────────────── */
.breadcrumb {
  display: flex; align-items: center; gap: 0.4rem;
  color: var(--gris); font-size: 0.88rem; margin-bottom: 0.75rem;
}
.breadcrumb .sep    { color: #E5E7EB; }
.breadcrumb-link    { color: var(--gris); text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul); }
.breadcrumb-actual  { color: var(--azul); font-weight: 600; }

/* ── Toast ───────────────────────────────────────────────────────────────── */
.toast {
  position: fixed; bottom: 2rem; right: 2rem;
  display: flex; align-items: center; gap: 10px;
  padding: 0.9rem 1.4rem; border-radius: 10px;
  color: white; font-weight: 700; font-size: 0.9rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000;
  font-family: 'Montserrat', sans-serif; max-width: 380px;
}
.toast.exito { background: var(--azul); }
.toast.error { background: var(--rojo); }
.toast-icono { width: 20px; height: 20px; flex-shrink: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(100%); }

/* ══════════════════════════════════════════════════════════════════════════
   HERO BUSCADOR
══════════════════════════════════════════════════════════════════════════ */
.hero-busqueda {
  background: linear-gradient(135deg, var(--azul) 0%, #1e4b8f 60%, #2563EB 100%);
  border-radius: 16px;
  padding: 2.5rem 2rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 8px 32px rgba(27, 57, 106, 0.25);
  position: relative;
  overflow: hidden;
}

/* Decoración de fondo del hero */
.hero-busqueda::before {
  content: '';
  position: absolute; inset: 0;
  background-image: radial-gradient(circle at 80% 20%, rgba(255,255,255,0.08) 0%, transparent 50%),
                    radial-gradient(circle at 10% 80%, rgba(255,255,255,0.05) 0%, transparent 40%);
  pointer-events: none;
}

.hero-contenido { position: relative; z-index: 1; }

.hero-texto { margin-bottom: 1.5rem; }
.hero-titulo {
  color: #FFFFFF; font-size: 1.75rem; font-weight: 700;
  margin: 0 0 0.4rem; letter-spacing: -0.02em;
}
.hero-subtitulo {
  color: rgba(255,255,255,0.75); font-size: 0.95rem; margin: 0;
}

/* ── Caja del buscador ─────────────────────────────────────────────────── */
.buscador-wrapper { max-width: 680px; }

.buscador-inner {
  display: flex; align-items: center;
  background: #FFFFFF; border-radius: 12px;
  border: 2px solid transparent;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  transition: border-color 0.2s, box-shadow 0.2s;
  overflow: hidden;
}
.buscador-activo {
  border-color: rgba(255,255,255,0.5);
  box-shadow: 0 4px 24px rgba(0,0,0,0.2), 0 0 0 3px rgba(255,255,255,0.15);
}
.buscador-error { border-color: var(--rojo) !important; }

.buscador-icono-lupa {
  width: 22px; height: 22px; stroke: var(--gris);
  flex-shrink: 0; margin-left: 1rem;
}

.buscador-input {
  flex: 1; border: none; outline: none;
  font-family: 'Montserrat', sans-serif;
  font-size: 1rem; font-weight: 500; color: var(--texto);
  padding: 0.95rem 0.75rem;
  background: transparent;
  letter-spacing: 0.01em;
}
.buscador-input::placeholder { color: #9CA3AF; font-weight: 400; }

.buscador-spinner {
  padding: 0.5rem; flex-shrink: 0;
}
.spinner-svg {
  width: 22px; height: 22px; animation: spin 0.8s linear infinite;
  stroke: var(--azul);
}
@keyframes spin { to { transform: rotate(360deg); } }

.buscador-limpiar {
  padding: 0.5rem; background: none; border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: var(--gris); flex-shrink: 0; border-radius: 6px;
  transition: color 0.15s, background 0.15s;
}
.buscador-limpiar:hover { color: var(--rojo); background: var(--rojo-suave); }
.buscador-limpiar svg { width: 18px; height: 18px; }

.buscador-btn {
  background: var(--azul); color: #FFFFFF;
  border: none; cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.95rem; font-weight: 700;
  padding: 0 1.5rem; height: 100%; min-height: 52px;
  transition: background 0.15s;
  white-space: nowrap; flex-shrink: 0;
}
.buscador-btn:hover:not(:disabled) { background: var(--azul-hover); }
.buscador-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Mensajes de estado ────────────────────────────────────────────────── */
.busqueda-mensaje {
  display: flex; align-items: center; gap: 8px;
  margin-top: 0.75rem; font-size: 0.88rem; font-weight: 500;
}
.busqueda-mensaje--error   { color: #FCA5A5; }
.busqueda-mensaje--vacio   { color: rgba(255,255,255,0.8); }
.busqueda-mensaje--hint    { color: rgba(255,255,255,0.6); }
.msg-icono { width: 17px; height: 17px; flex-shrink: 0; }

.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.25s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(-6px); }

/* ══════════════════════════════════════════════════════════════════════════
   RESULTADO: PANEL DEL ALUMNO
══════════════════════════════════════════════════════════════════════════ */
.resultado-seccion {
  background: #FFFFFF;
  border-radius: 16px;
  border: 1px solid var(--borde);
  box-shadow: 0 4px 20px rgba(0,0,0,0.06);
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.resultado-appear-enter-active { transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1); }
.resultado-appear-enter-from   { opacity: 0; transform: translateY(20px); }

/* ── Cabecera del alumno ─────────────────────────────────────────────── */
.alumno-header {
  display: flex; align-items: flex-start; gap: 1.5rem;
  padding: 1.75rem 2rem;
  background: linear-gradient(to right, var(--azul-light), #FFFFFF);
  border-bottom: 1px solid var(--borde);
  flex-wrap: wrap;
}

.alumno-avatar-wrap { flex-shrink: 0; }
.alumno-avatar {
  width: 80px; height: 80px; border-radius: 50%;
  object-fit: cover; border: 3px solid #FFFFFF;
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.alumno-avatar-placeholder {
  width: 80px; height: 80px; border-radius: 50%;
  background: var(--azul); color: #FFFFFF;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.6rem; font-weight: 700;
  border: 3px solid #FFFFFF;
  box-shadow: 0 4px 12px rgba(27,57,106,0.25);
}

.alumno-header-info { flex: 1; min-width: 0; }
.alumno-nombre-row { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 0.4rem; }
.alumno-nombre {
  font-size: 1.35rem; font-weight: 700; color: var(--texto); margin: 0;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.alumno-nc, .alumno-meta {
  display: flex; align-items: center; gap: 6px;
  font-size: 0.88rem; color: var(--gris); margin: 0 0 0.2rem;
}
.alumno-nc strong { color: var(--texto); }
.info-icono-mini { width: 15px; height: 15px; stroke: var(--gris); flex-shrink: 0; }

.alumno-estatus {
  font-size: 0.78rem; font-weight: 700; padding: 3px 12px;
  border-radius: 20px; white-space: nowrap; flex-shrink: 0;
}

.alumno-header-acciones {
  display: flex; flex-direction: column; gap: 0.6rem;
  flex-shrink: 0; align-items: flex-end;
}

.btn-accion {
  display: flex; align-items: center; gap: 6px;
  padding: 0.55rem 1.1rem; border-radius: 8px;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.85rem; font-weight: 600;
  cursor: pointer; transition: all 0.15s; white-space: nowrap;
  border: none;
}
.btn-accion svg { width: 16px; height: 16px; }
.btn-accion--primary { background: var(--azul); color: #FFFFFF; }
.btn-accion--primary:hover { background: var(--azul-hover); }
.btn-accion--outline {
  background: transparent; color: var(--azul);
  border: 1.5px solid var(--azul);
}
.btn-accion--outline:hover { background: var(--azul-light); }

/* ── Estatus chips ─────────────────────────────────────────────────────── */
.estatus-activo     { background: var(--verde-suave); color: var(--verde); }
.estatus-inactivo   { background: #F3F4F6; color: var(--gris); }
.estatus-egresado   { background: var(--azul-suave); color: var(--azul); }
.estatus-suspendido { background: var(--amarillo-suave); color: var(--amarillo); }
.estatus-baja       { background: var(--rojo-suave); color: var(--rojo); }

/* ── Tabs ──────────────────────────────────────────────────────────────── */
.tabs-barra {
  display: flex; overflow-x: auto; gap: 0;
  border-bottom: 1px solid var(--borde);
  padding: 0 1.5rem;
  scrollbar-width: none;
}
.tabs-barra::-webkit-scrollbar { display: none; }

.tab-btn {
  display: flex; align-items: center; gap: 7px;
  padding: 0.85rem 1.1rem; border: none; background: none;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.88rem; font-weight: 600;
  color: var(--gris); cursor: pointer; white-space: nowrap;
  border-bottom: 3px solid transparent; margin-bottom: -1px;
  transition: color 0.15s, border-color 0.15s;
}
.tab-btn:hover { color: var(--azul); }
.tab-btn--activo { color: var(--azul); border-bottom-color: var(--azul); }
.tab-icono { width: 16px; height: 16px; }

.tab-contenido { padding: 1.75rem 2rem; }

.tab-fade-enter-active, .tab-fade-leave-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.tab-fade-enter-from { opacity: 0; transform: translateY(6px); }
.tab-fade-leave-to   { opacity: 0; transform: translateY(-4px); }

/* ── Info grid (2 columnas) ─────────────────────────────────────────── */
.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
}

.info-card {
  background: var(--fondo); border-radius: 12px;
  border: 1px solid var(--borde); padding: 1.25rem;
}
.info-card-titulo {
  font-size: 0.88rem; font-weight: 700;
  color: var(--azul); margin: 0 0 1rem;
  text-transform: uppercase; letter-spacing: 0.05em;
}

.info-lista { margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.65rem; }
.info-fila { display: flex; justify-content: space-between; align-items: baseline; gap: 0.5rem; }
.info-fila dt { font-size: 0.83rem; color: var(--gris); font-weight: 500; flex-shrink: 0; }
.info-fila dd { font-size: 0.88rem; color: var(--texto); font-weight: 600; text-align: right; margin: 0; }
.dd-mono { font-family: monospace; letter-spacing: 0.05em; }

/* ── Estado académico ────────────────────────────────────────────────── */
.academico-grid {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 1rem; margin-bottom: 1.25rem;
}

.kpi-card {
  background: var(--fondo); border: 1px solid var(--borde);
  border-radius: 12px; padding: 1.25rem 1rem;
  text-align: center;
}
.kpi-valor {
  font-size: 2.25rem; font-weight: 700; line-height: 1;
  margin-bottom: 0.4rem;
}
.kpi-label { font-size: 0.8rem; color: var(--gris); font-weight: 500; }
.kpi-azul    { color: var(--azul); }
.kpi-verde   { color: var(--verde); }
.kpi-naranja { color: var(--amarillo); }
.kpi-rojo    { color: var(--rojo); }

.progreso-card {
  background: var(--fondo); border: 1px solid var(--borde);
  border-radius: 12px; padding: 1.25rem; margin-bottom: 1rem;
}
.progreso-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 0.75rem;
}
.progreso-label { font-size: 0.88rem; font-weight: 600; color: var(--texto); }
.progreso-pct { font-size: 1.1rem; font-weight: 700; color: var(--azul); }
.progreso-barra-bg {
  height: 10px; background: var(--borde); border-radius: 99px; overflow: hidden;
}
.progreso-barra-fill {
  height: 100%; background: linear-gradient(to right, var(--azul), #2563EB);
  border-radius: 99px; transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.progreso-detalle { font-size: 0.82rem; color: var(--gris); margin: 0.5rem 0 0; }

.situacion-banner {
  display: flex; align-items: center; gap: 10px;
  padding: 0.9rem 1.1rem; border-radius: 10px;
  font-size: 0.9rem; font-weight: 500;
}
.situacion-ok     { background: var(--verde-suave); color: var(--verde); }
.situacion-alerta { background: var(--amarillo-suave); color: #92400E; }
.situacion-icono  { width: 20px; height: 20px; flex-shrink: 0; }

/* ── Kardex ──────────────────────────────────────────────────────────── */
.kardex-periodo { margin-bottom: 1.5rem; }
.kardex-periodo-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 0.6rem;
}
.kardex-periodo-label { font-size: 0.95rem; font-weight: 700; color: var(--texto); }
.kardex-periodo-prom  { font-size: 0.88rem; color: var(--gris); }
.kardex-periodo-prom strong { color: var(--azul); }

.kardex-tabla-wrap { overflow-x: auto; border-radius: 10px; border: 1px solid var(--borde); }
.kardex-tabla {
  width: 100%; border-collapse: collapse; min-width: 480px;
}
.kardex-tabla th {
  background: var(--fondo); padding: 0.65rem 1rem;
  font-size: 0.8rem; font-weight: 700;
  color: var(--gris); text-align: left;
  border-bottom: 1px solid var(--borde);
}
.kardex-fila td {
  padding: 0.6rem 1rem; font-size: 0.875rem; color: var(--texto);
  border-bottom: 1px solid #F9FAFB;
}
.kardex-fila:last-child td { border-bottom: none; }
.kardex-fila:hover td { background: var(--azul-light); }
.td-mono   { font-family: monospace; font-size: 0.82rem; color: var(--gris); }
.td-centro { text-align: center; }

.calif {
  display: inline-flex; align-items: center; justify-content: center;
  width: 38px; height: 28px; border-radius: 6px;
  font-size: 0.85rem; font-weight: 700;
}
.calif-verde    { background: var(--verde-suave); color: var(--verde); }
.calif-amarillo { background: var(--amarillo-suave); color: #92400E; }
.calif-rojo     { background: var(--rojo-suave); color: var(--rojo); }
.calif-nd       { background: #F3F4F6; color: var(--gris); }

.chip-mini {
  display: inline-flex; padding: 2px 10px; border-radius: 20px;
  font-size: 0.75rem; font-weight: 700; white-space: nowrap;
}
.chip-verde { background: var(--verde-suave); color: var(--verde); }
.chip-rojo  { background: var(--rojo-suave); color: var(--rojo); }
.chip-gris  { background: #F3F4F6; color: var(--gris); }

.kardex-footer { padding-top: 1rem; display: flex; justify-content: center; }
.btn-ver-kardex {
  display: flex; align-items: center; gap: 6px;
  background: var(--azul-light); color: var(--azul);
  border: 1.5px solid var(--azul-suave); border-radius: 8px;
  padding: 0.65rem 1.25rem; cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  font-size: 0.88rem; font-weight: 700;
  transition: background 0.15s, border-color 0.15s;
}
.btn-ver-kardex:hover { background: var(--azul-suave); border-color: var(--azul); }
.btn-ver-kardex svg { width: 16px; height: 16px; }

/* ── Horario ──────────────────────────────────────────────────────────── */
.horario-lista { display: flex; flex-direction: column; gap: 0.75rem; }
.horario-card {
  display: flex; align-items: center; gap: 1.25rem;
  background: var(--fondo); border: 1px solid var(--borde);
  border-radius: 10px; padding: 1rem 1.25rem;
  transition: border-color 0.15s;
}
.horario-card:hover { border-color: var(--azul-suave); }

.horario-dia {
  font-size: 0.78rem; font-weight: 700; color: var(--azul);
  background: var(--azul-light); padding: 4px 10px;
  border-radius: 6px; white-space: nowrap; flex-shrink: 0;
  min-width: 60px; text-align: center;
}
.horario-info { flex: 1; min-width: 0; }
.horario-materia { font-size: 0.93rem; font-weight: 700; color: var(--texto); margin: 0 0 2px; }
.horario-detalle { font-size: 0.82rem; color: var(--gris); margin: 0; }

.horario-docente {
  display: flex; flex-direction: column; align-items: flex-end;
  text-align: right; flex-shrink: 0;
}
.horario-docente-label { font-size: 0.72rem; color: var(--gris); font-weight: 500; margin-bottom: 2px; }
.horario-docente-nombre { font-size: 0.83rem; font-weight: 600; color: var(--texto); }

/* ── Estado vacío en tabs ───────────────────────────────────────────── */
.tab-vacio {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; gap: 0.75rem;
  padding: 3rem 1rem; color: var(--gris); text-align: center;
}
.vacio-icono { width: 48px; height: 48px; stroke: #D1D5DB; }
.tab-vacio p { font-size: 0.9rem; margin: 0; }

/* ── Chip genérico ──────────────────────────────────────────────────── */
.chip {
  display: inline-flex; align-items: center;
  padding: 3px 12px; border-radius: 20px;
  font-size: 0.78rem; font-weight: 700; white-space: nowrap;
}

/* ══════════════════════════════════════════════════════════════════════════
   DASHBOARD: KPIs
══════════════════════════════════════════════════════════════════════════ */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem; margin-bottom: 1.5rem;
}

.stat-card {
  background: #FFFFFF; border-radius: 12px;
  padding: 1.3rem 1.4rem; display: flex;
  align-items: center; gap: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  border: 1px solid var(--borde);
  transition: transform 0.2s, box-shadow 0.2s; min-width: 0;
}
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.09); }

.stat-azul { background: var(--azul); border-color: var(--azul); }
.stat-azul .stat-etiqueta    { color: rgba(255,255,255,0.8); }
.stat-azul .stat-numero      { color: #FFFFFF; }
.stat-azul .stat-link        { color: rgba(255,255,255,0.85); }
.stat-azul .stat-icono-wrapper { background: rgba(255,255,255,0.2); }
.stat-azul .stat-icono-wrapper svg { stroke: #FFFFFF; }

.stat-icono-wrapper {
  width: 46px; height: 46px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; background: var(--azul-suave);
}
.stat-icono-wrapper svg { width: 22px; height: 22px; stroke: var(--azul); }
.stat-icono-verde          { background: var(--verde-suave); }
.stat-icono-verde-svg      { width: 22px; height: 22px; stroke: var(--verde); }
.stat-icono-naranja        { background: var(--amarillo-suave); }
.stat-icono-naranja-svg    { width: 22px; height: 22px; stroke: var(--amarillo); }
.stat-icono-azul-suave     { background: var(--azul-suave); }
.stat-icono-azul-suave-svg { width: 22px; height: 22px; stroke: var(--azul); }

.stat-info { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.stat-etiqueta {
  font-size: 0.83rem; color: var(--gris); font-weight: 500; margin: 0;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.stat-numero { font-size: 2rem; font-weight: 700; color: var(--texto); margin: 2px 0; line-height: 1; }
.stat-link {
  font-size: 0.82rem; color: var(--azul); font-weight: 600;
  cursor: pointer; transition: color 0.15s; white-space: nowrap;
}
.stat-link:hover { color: var(--azul-hover); }

.stat-skeleton {
  width: 80px; height: 32px;
  background: linear-gradient(90deg, #E5E7EB 25%, #F3F4F6 50%, #E5E7EB 75%);
  background-size: 200% 100%; animation: shimmer 1.4s infinite;
  border-radius: 6px; margin: 2px 0;
}
.stat-azul .stat-skeleton {
  background: linear-gradient(90deg,
    rgba(255,255,255,0.2) 25%, rgba(255,255,255,0.35) 50%, rgba(255,255,255,0.2) 75%);
  background-size: 200% 100%;
}
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* ── Alerta de error ──────────────────────────────────────────────────── */
.alerta-error {
  display: flex; align-items: center; gap: 10px;
  background: var(--rojo-suave); border: 1px solid #FECACA;
  border-radius: 10px; padding: 12px 16px; margin-bottom: 1.4rem;
  font-size: 0.9rem; color: var(--rojo); font-weight: 500;
}
.alerta-icono { width: 20px; height: 20px; flex-shrink: 0; stroke: var(--rojo); }
.btn-reintentar {
  margin-left: auto; background: var(--rojo); color: white; border: none;
  padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 0.85rem;
  cursor: pointer; font-family: 'Montserrat', sans-serif;
  transition: background 0.15s; white-space: nowrap;
}
.btn-reintentar:hover { background: #B91C1C; }

/* ── Accesos rápidos ─────────────────────────────────────────────────── */
.accesos-seccion { margin-bottom: 2rem; }
.seccion-titulo  { font-size: 1.05rem; font-weight: 700; color: var(--texto); margin: 0 0 1rem; }

.accesos-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
}

.acceso-card {
  background: #FFFFFF; border-radius: 12px; padding: 1.2rem 1.4rem;
  display: flex; align-items: center; gap: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde);
  cursor: pointer; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; min-width: 0;
}
.acceso-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); border-color: var(--azul); }
.acceso-deshabilitado { cursor: not-allowed; opacity: 0.6; }
.acceso-deshabilitado:hover { transform: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border-color: var(--borde); }

.acceso-icono { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.acceso-icono svg { width: 22px; height: 22px; }

.acceso-verde    { background: var(--verde-suave); }
.acceso-verde svg    { stroke: var(--verde); }
.acceso-azul     { background: var(--azul-suave); }
.acceso-azul svg     { stroke: var(--azul); }
.acceso-amarillo { background: var(--amarillo-suave); }
.acceso-amarillo svg { stroke: var(--amarillo); }
.acceso-gris     { background: #F3F4F6; }
.acceso-gris svg     { stroke: var(--gris); }

.acceso-contenido { flex: 1; min-width: 0; }
.acceso-contenido h4 {
  font-size: 0.93rem; font-weight: 700; color: var(--texto); margin: 0 0 3px;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.acceso-contenido p { font-size: 0.82rem; color: var(--gris); margin: 0; }

.acceso-flecha { width: 18px; height: 18px; stroke: #9CA3AF; flex-shrink: 0; transition: stroke 0.15s; }
.acceso-card:not(.acceso-deshabilitado):hover .acceso-flecha { stroke: var(--azul); }

.badge-proximamente {
  background: #F3F4F6; color: var(--gris); font-size: 0.72rem; font-weight: 700;
  padding: 3px 10px; border-radius: 20px; white-space: nowrap;
  border: 1px solid var(--borde); flex-shrink: 0;
}

/* ── Fade transition para bloque dashboard ──────────────────────────── */
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }

/* ── Footer ──────────────────────────────────────────────────────────── */
.pie-pagina {
  text-align: center; color: #9CA3AF; font-size: 0.82rem;
  padding: 2rem 0; border-top: 1px solid var(--borde); margin-top: 0;
}

/* ══════════════════════════════════════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
  .stats-grid    { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .accesos-grid  { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .academico-grid { grid-template-columns: repeat(2, 1fr); }
  .info-grid     { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .hero-busqueda { padding: 2rem 1.25rem; }
  .hero-titulo   { font-size: 1.4rem; }

  .alumno-header { flex-direction: column; gap: 1rem; padding: 1.25rem; }
  .alumno-header-acciones { flex-direction: row; align-items: center; width: 100%; }

  .tabs-barra { padding: 0 0.75rem; }
  .tab-btn    { padding: 0.75rem 0.9rem; font-size: 0.82rem; }
  .tab-contenido { padding: 1.25rem; }

  .horario-card { flex-wrap: wrap; gap: 0.75rem; }
  .horario-docente { align-items: flex-start; text-align: left; }
}

@media (max-width: 640px) {
  .stats-grid    { grid-template-columns: 1fr; }
  .accesos-grid  { grid-template-columns: 1fr; }
  .academico-grid { grid-template-columns: repeat(2, 1fr); }

  .buscador-btn { padding: 0 1rem; font-size: 0.88rem; min-height: 48px; }

  .alumno-nombre { font-size: 1.15rem; }
  .alumno-header-acciones { flex-direction: column; }
  .btn-accion { width: 100%; justify-content: center; }
}
</style>