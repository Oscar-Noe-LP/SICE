<!-- ============================================================ -->
<!-- src/views/KardexDetalleView.vue                           -->
<!-- Módulo: Kardex — Visualización completa del alumno        -->
<!-- Autor: Diego (SICE) | Refactorizado: Junio 2025           -->
<!-- ============================================================ -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="kardex-page">

      <!-- ── Barra de progreso de carga global ── -->
      <div class="barra-carga" :class="{ activa: cargando }" aria-hidden="true">
        <div class="barra-progreso"></div>
      </div>

      <!-- ── Breadcrumb ── -->
      <nav class="breadcrumb" aria-label="Ruta de navegación">
        <router-link to="/inicio" class="breadcrumb-link">Inicio</router-link>
        <span class="sep" aria-hidden="true">›</span>
        <router-link to="/kardex" class="breadcrumb-link">Kardex</router-link>
        <span class="sep" aria-hidden="true">›</span>
        <span class="breadcrumb-actual">{{ route.params.no_control }}</span>
      </nav>

      <!-- ── Toast ── -->
      <transition name="toast-slide">
        <div v-if="toast.visible" class="toast" :class="toast.tipo" role="alert" aria-live="polite">
          <span class="toast-icono" aria-hidden="true">
            <svg v-if="toast.tipo === 'exito'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </span>
          {{ toast.mensaje }}
        </div>
      </transition>

      <!-- ── Estado: Error de carga ── -->
      <transition name="fade">
        <div v-if="errorCarga && !cargando" class="alerta-error" role="alert">
          <svg class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>No se pudo cargar el kardex. Verifica la conexión con el servidor.</span>
          <button class="btn-reintentar" @click="cargarDatos">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Reintentar
          </button>
        </div>
      </transition>

      <!-- ── Estado: Skeleton de carga ── -->
      <div v-if="cargando" class="skeleton-wrap" aria-busy="true" aria-label="Cargando información del alumno">
        <div class="sk-hero">
          <div class="sk-avatar"></div>
          <div class="sk-hero-texto">
            <div class="sk-linea sk-ancha"></div>
            <div class="sk-linea sk-media"></div>
            <div class="sk-linea sk-corta"></div>
          </div>
        </div>
        <div class="sk-stats">
          <div class="sk-stat" v-for="i in 4" :key="i"></div>
        </div>
        <div class="sk-tabla">
          <div class="sk-thead"></div>
          <div class="sk-fila" v-for="i in 8" :key="'f'+i"></div>
        </div>
      </div>

      <!-- ══════════════════════════════════════════════════════
           CONTENIDO PRINCIPAL — visible cuando alumno cargó
      ══════════════════════════════════════════════════════ -->
      <div v-if="alumno && !cargando" class="contenido">

        <!-- ── Encabezado con acciones ── -->
        <div class="cabecera-fila">
          <h1 class="page-title">Kardex Académico</h1>
          <div class="cabecera-acciones">
            <button @click="router.back()" class="btn-secundario" type="button">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Regresar
            </button>
            <button @click="exportarPDF" class="btn-primario" :disabled="exportando" type="button" :aria-busy="exportando">
              <span v-if="exportando" class="spinner" aria-hidden="true"></span>
              <svg v-else fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
              </svg>
              {{ exportando ? 'Generando...' : 'Exportar PDF' }}
            </button>
          </div>
        </div>

        <!-- ── Hero card del alumno ── -->
        <div class="alumno-hero">
          <!-- Avatar / foto -->
          <div class="alumno-avatar-wrap" aria-hidden="true">
            <img v-if="alumno.foto" :src="alumno.foto" :alt="`Foto de ${alumno.nombre}`" class="alumno-foto"/>
            <div v-else class="alumno-foto-placeholder">
              <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="40" height="40">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <span class="avatar-iniciales">{{ iniciales }}</span>
            </div>
          </div>

          <!-- Datos principales -->
          <div class="alumno-datos-principales">
            <div class="alumno-nombre-fila">
              <h2 class="alumno-nombre">{{ alumno.nombre }}</h2>
              <div class="estatus-badge" :class="estatusClase">
                <span class="estatus-dot" aria-hidden="true"></span>
                {{ alumno.estatus || 'Activo' }}
              </div>
            </div>

            <div class="alumno-meta-grid">
              <div class="meta-chip">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1"/>
                </svg>
                <span>{{ alumno.no_control }}</span>
              </div>
              <div class="meta-chip">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
                <span>{{ alumno.carrera }}</span>
              </div>
              <div class="meta-chip">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                </svg>
                <span>Plan: <strong>{{ alumno.plan_estudio }}</strong></span>
              </div>
              <div class="meta-chip">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Semestre: <strong>{{ alumno.semestre_actual }}°</strong></span>
              </div>
            </div>
          </div>

          <!-- Mini estadísticas rápidas -->
          <div class="alumno-stats-rapidas" role="list" aria-label="Estadísticas del alumno">
            <div class="mini-stat" role="listitem">
              <span class="mini-stat-valor azul">{{ resumenKardex.totalMaterias }}</span>
              <span class="mini-stat-label">Total materias</span>
            </div>
            <div class="mini-stat" role="listitem">
              <span class="mini-stat-valor verde">{{ resumenKardex.acreditadas }}</span>
              <span class="mini-stat-label">Acreditadas</span>
            </div>
            <div class="mini-stat" role="listitem">
              <span class="mini-stat-valor rojo">{{ resumenKardex.reprobadas }}</span>
              <span class="mini-stat-label">Reprobadas</span>
            </div>
            <div class="mini-stat" role="listitem">
              <span class="mini-stat-valor amarillo">{{ resumenKardex.pendientes }}</span>
              <span class="mini-stat-label">En curso</span>
            </div>
          </div>
        </div>

        <!-- ── Barra de progreso de créditos ── -->
        <div class="creditos-card" role="progressbar" :aria-valuenow="porcentajeCreditos" aria-valuemin="0" aria-valuemax="100" :aria-label="`Avance de créditos: ${porcentajeCreditos}%`">
          <div class="creditos-header">
            <div class="creditos-titulo-wrap">
              <span class="creditos-titulo">Avance en Créditos</span>
              <span class="creditos-porcentaje" :class="{ completo: porcentajeCreditos >= 80 }">{{ porcentajeCreditos }}%</span>
            </div>
            <span class="creditos-detalle">
              {{ alumno.creditos_acumulados }} de {{ alumno.creditos_totales }} créditos
            </span>
          </div>
          <div class="creditos-barra-track">
            <div
              class="creditos-barra-fill"
              :style="{
                width: porcentajeCreditos + '%',
                background: porcentajeCreditos >= 80 ? '#16A34A' : '#1B396A'
              }"
            ></div>
          </div>
          <div class="creditos-hitos" aria-hidden="true">
            <span v-for="hito in [25,50,75,100]" :key="hito" class="creditos-hito" :style="{ left: hito + '%' }">{{ hito }}%</span>
          </div>
        </div>

        <!-- ══ TABS de secciones ══ -->
        <div class="tabs-wrap">
          <div class="tabs-header" role="tablist" aria-label="Secciones del kardex">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              class="tab-btn"
              :class="{ activo: tabActivo === tab.id }"
              @click="tabActivo = tab.id"
              role="tab"
              :aria-selected="tabActivo === tab.id"
              :aria-controls="`panel-${tab.id}`"
              :id="`tab-${tab.id}`"
              type="button"
            >
              <component :is="'span'" v-html="tab.icono" class="tab-icono" aria-hidden="true"></component>
              {{ tab.label }}
              <span v-if="tab.badge" class="tab-badge">{{ tab.badge }}</span>
            </button>
          </div>

          <!-- ─ Panel: Materias por Semestre ─ -->
          <transition name="tab-fade" mode="out-in">
            <div v-if="tabActivo === 'materias'" id="panel-materias" role="tabpanel" aria-labelledby="tab-materias" class="tab-panel">

              <div class="semestres-controles">
                <span class="seccion-subtitulo">{{ kardex.semestres?.length || 0 }} semestres registrados</span>
                <div class="controles-grupo">
                  <button @click="expandirTodo" class="btn-control" type="button">Expandir todo</button>
                  <button @click="contraerTodo"  class="btn-control" type="button">Contraer todo</button>
                </div>
              </div>

              <div
                v-for="semestre in kardex.semestres"
                :key="semestre.numero"
                class="semestre-item"
              >
                <button
                  class="semestre-cabecera"
                  :class="{ abierto: semestresAbiertos.includes(semestre.numero) }"
                  @click="toggleSemestre(semestre.numero)"
                  type="button"
                  :aria-expanded="semestresAbiertos.includes(semestre.numero)"
                  :aria-controls="`sem-${semestre.numero}`"
                >
                  <div class="semestre-izq">
                    <span class="semestre-num-badge" aria-hidden="true">{{ semestre.numero }}</span>
                    <span class="semestre-titulo">Semestre {{ semestre.numero }}</span>
                    <span class="semestre-conteo">{{ semestre.materias.length }} materias</span>
                  </div>
                  <div class="semestre-meta">
                    <span class="semestre-badge acreditadas" v-if="semestre.acreditadas > 0">✓ {{ semestre.acreditadas }}</span>
                    <span class="semestre-badge reprobadas"  v-if="semestre.reprobadas > 0">✗ {{ semestre.reprobadas }}</span>
                    <span class="semestre-badge pendientes"  v-if="semestre.pendientes > 0">● {{ semestre.pendientes }}</span>
                    <svg class="semestre-flecha" :class="{ girado: semestresAbiertos.includes(semestre.numero) }" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </button>

                <!-- Tabla de materias del semestre -->
                <transition name="semestre-expand">
                  <div v-if="semestresAbiertos.includes(semestre.numero)" :id="`sem-${semestre.numero}`" class="semestre-contenido">
                    <div class="tabla-responsive">
                      <table class="tabla-materias" aria-label="`Materias del semestre ${semestre.numero}`">
                        <thead>
                          <tr>
                            <th>Clave</th>
                            <th>Materia</th>
                            <th class="centrado">Créditos</th>
                            <th class="centrado">Calificación</th>
                            <th class="centrado">Estado</th>
                            <th class="centrado">Período</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr
                            v-for="materia in semestre.materias"
                            :key="materia.clave"
                            :class="{ 'fila-reprobada': materia.estado === 'Reprobada', 'fila-pendiente': materia.estado === 'En curso' }"
                          >
                            <td><span class="clave-chip">{{ materia.clave }}</span></td>
                            <td>
                              <div class="materia-nombre-celda">
                                <svg v-if="materia.estado === 'Reprobada'" class="icono-advertencia" fill="none" viewBox="0 0 24 24" stroke="#DC2626" width="14" height="14" aria-label="Materia reprobada">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                {{ materia.nombre }}
                              </div>
                            </td>
                            <td class="centrado texto-secundario">{{ materia.creditos }}</td>
                            <td class="centrado">
                              <span
                                v-if="materia.calificacion !== null && materia.calificacion !== undefined"
                                class="calificacion"
                                :class="materia.calificacion >= 70 ? 'calif-aprobada' : 'calif-reprobada'"
                              >
                                {{ materia.calificacion }}
                              </span>
                              <span v-else class="texto-secundario">—</span>
                            </td>
                            <td class="centrado">
                              <span class="badge-estado" :class="badgeEstadoClase(materia.estado)">
                                {{ materia.estado }}
                              </span>
                            </td>
                            <td class="centrado texto-secundario">{{ materia.periodo || '—' }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <!-- Promedio del semestre -->
                    <div class="semestre-pie">
                      <span class="semestre-promedio">
                        Promedio del semestre:
                        <strong :class="semestre.promedio >= 70 ? 'valor-verde' : 'valor-rojo'">
                          {{ semestre.promedio ?? '—' }}
                        </strong>
                      </span>
                    </div>
                  </div>
                </transition>
              </div>

              <!-- Estado vacío -->
              <div v-if="!kardex.semestres?.length" class="estado-vacio">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="48" height="48" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                </svg>
                <p>No hay materias registradas en el kardex</p>
              </div>

            </div>

            <!-- ─ Panel: Horario actual ─ -->
            <div v-else-if="tabActivo === 'horario'" id="panel-horario" role="tabpanel" aria-labelledby="tab-horario" class="tab-panel">
              <div v-if="horario.length > 0" class="tabla-responsive">
                <table class="tabla-materias" aria-label="Horario actual del alumno">
                  <thead>
                    <tr>
                      <th>Materia</th>
                      <th class="centrado">Grupo</th>
                      <th class="centrado">Docente</th>
                      <th class="centrado">Días</th>
                      <th class="centrado">Horario</th>
                      <th class="centrado">Aula</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(clase, i) in horario" :key="i">
                      <td>
                        <div class="materia-nombre-celda">
                          <span class="punto-horario" :style="{ background: coloresHorario[i % coloresHorario.length] }" aria-hidden="true"></span>
                          {{ clase.materia }}
                        </div>
                      </td>
                      <td class="centrado"><span class="clave-chip">{{ clase.grupo }}</span></td>
                      <td class="centrado texto-secundario">{{ clase.docente }}</td>
                      <td class="centrado"><span class="dias-chip">{{ clase.dias }}</span></td>
                      <td class="centrado texto-secundario">{{ clase.hora_inicio }} – {{ clase.hora_fin }}</td>
                      <td class="centrado texto-secundario">{{ clase.aula }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="estado-vacio">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="48" height="48" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p>No hay horario registrado para el semestre actual</p>
              </div>
            </div>

            <!-- ─ Panel: Información adicional ─ -->
            <div v-else-if="tabActivo === 'info'" id="panel-info" role="tabpanel" aria-labelledby="tab-info" class="tab-panel">
              <div class="info-grid">
                <div class="info-card">
                  <h3 class="info-card-titulo">Datos del Alumno</h3>
                  <dl class="info-lista">
                    <div class="info-fila">
                      <dt>Número de Control</dt>
                      <dd><span class="clave-chip">{{ alumno.no_control }}</span></dd>
                    </div>
                    <div class="info-fila">
                      <dt>Nombre completo</dt>
                      <dd>{{ alumno.nombre }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Carrera</dt>
                      <dd>{{ alumno.carrera }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Semestre actual</dt>
                      <dd>{{ alumno.semestre_actual }}° Semestre</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Plan de estudios</dt>
                      <dd>{{ alumno.plan_estudio }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Fecha de ingreso</dt>
                      <dd>{{ alumno.fecha_ingreso ? formatearFecha(alumno.fecha_ingreso) : '—' }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Estatus</dt>
                      <dd>
                        <div class="estatus-badge" :class="estatusClase">
                          <span class="estatus-dot" aria-hidden="true"></span>
                          {{ alumno.estatus || 'Activo' }}
                        </div>
                      </dd>
                    </div>
                  </dl>
                </div>

                <div class="info-card">
                  <h3 class="info-card-titulo">Resumen Académico</h3>
                  <dl class="info-lista">
                    <div class="info-fila">
                      <dt>Promedio general</dt>
                      <dd>
                        <span :class="alumno.promedio_general >= 70 ? 'valor-verde' : 'valor-rojo'" style="font-weight:700; font-size:1.1rem;">
                          {{ alumno.promedio_general ?? '—' }}
                        </span>
                      </dd>
                    </div>
                    <div class="info-fila">
                      <dt>Créditos acumulados</dt>
                      <dd>{{ alumno.creditos_acumulados }} / {{ alumno.creditos_totales }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Materias acreditadas</dt>
                      <dd><span class="valor-verde font-bold">{{ resumenKardex.acreditadas }}</span></dd>
                    </div>
                    <div class="info-fila">
                      <dt>Materias reprobadas</dt>
                      <dd><span class="valor-rojo font-bold">{{ resumenKardex.reprobadas }}</span></dd>
                    </div>
                    <div class="info-fila">
                      <dt>En curso</dt>
                      <dd>{{ resumenKardex.pendientes }}</dd>
                    </div>
                    <div class="info-fila">
                      <dt>Semestres cursados</dt>
                      <dd>{{ kardex.semestres?.length ?? 0 }}</dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>
          </transition>
        </div>

        <!-- ── Pie de página ── -->
        <footer class="pie-pagina">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          SICE — Kardex generado el {{ fechaGeneracion }}
        </footer>

      </div><!-- /contenido -->

    </div>
  </MainLayout>
</template>

<script setup>
/**
 * KardexDetalleView.vue
 * Visualización completa del kardex académico de un alumno.
 *
 * Responsable: Diego
 * Módulo: Búsqueda y visualización de alumno
 *
 * Mejoras aplicadas:
 * - Carga automática al montar (no requiere buscar dos veces)
 * - Organización por Tabs: Materias | Horario | Info
 * - Hero card con estadísticas rápidas
 * - Barra de progreso de créditos con hitos visuales
 * - Skeleton de carga + error con reintentar
 * - Accesibilidad completa (aria-*)
 */
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const route  = useRoute()
const router = useRouter()
const API_URL = import.meta.env.VITE_API_URL

// ── Estado de UI ────────────────────────────────────────────────────────
const cargando   = ref(false)
const errorCarga = ref(false)
const exportando = ref(false)
const tabActivo  = ref('materias')

// ── Datos del alumno ────────────────────────────────────────────────────
const alumno = ref(null)
const kardex = ref({ semestres: [] })
const horario = ref([])

// ── Control del acordeón de semestres ──────────────────────────────────
const semestresAbiertos = ref([])

// ── Toast ────────────────────────────────────────────────────────────────
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let toastTimer = null

const mostrarToast = (mensaje, tipo = 'exito', duracion = 3500) => {
  toast.value = { visible: true, mensaje, tipo }
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => { toast.value.visible = false }, duracion)
}

// ── Tabs disponibles ────────────────────────────────────────────────────
const tabs = computed(() => [
  {
    id: 'materias',
    label: 'Materias',
    icono: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/></svg>',
    badge: kardex.value.semestres?.length || null
  },
  {
    id: 'horario',
    label: 'Horario Actual',
    icono: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
    badge: horario.value.length || null
  },
  {
    id: 'info',
    label: 'Información',
    icono: '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
    badge: null
  }
])

// ── Computed de resumen ─────────────────────────────────────────────────
/** Totaliza materias del kardex por estado */
const resumenKardex = computed(() => {
  let total = 0, acreditadas = 0, reprobadas = 0, pendientes = 0
  for (const sem of (kardex.value.semestres || [])) {
    total      += sem.materias.length
    acreditadas += sem.acreditadas ?? 0
    reprobadas  += sem.reprobadas  ?? 0
    pendientes  += sem.pendientes  ?? 0
  }
  return { totalMaterias: total, acreditadas, reprobadas, pendientes }
})

/** Porcentaje de créditos completados */
const porcentajeCreditos = computed(() => {
  if (!alumno.value?.creditos_totales) return 0
  return Math.round((alumno.value.creditos_acumulados / alumno.value.creditos_totales) * 100)
})

/** Iniciales del alumno para el placeholder de avatar */
const iniciales = computed(() => {
  if (!alumno.value?.nombre) return '?'
  return alumno.value.nombre
    .split(' ')
    .slice(0, 2)
    .map(p => p[0]?.toUpperCase())
    .join('')
})

/** Clase del badge de estatus */
const estatusClase = computed(() => {
  const e = (alumno.value?.estatus || '').toLowerCase()
  if (e.includes('activo'))       return 'estatus-activo'
  if (e.includes('temporal'))     return 'estatus-temporal'
  if (e.includes('definitiva'))   return 'estatus-definitiva'
  if (e.includes('titulado'))     return 'estatus-titulado'
  if (e.includes('egresado'))     return 'estatus-egresado'
  return 'estatus-activo'
})

/** Fecha de generación del reporte */
const fechaGeneracion = computed(() =>
  new Date().toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' })
)

// ── Colores para el horario ─────────────────────────────────────────────
const coloresHorario = ['#1B396A','#16A34A','#F59E0B','#7C3AED','#DC2626','#0891B2']

// ── Carga de datos ──────────────────────────────────────────────────────
/**
 * Carga automáticamente el kardex del alumno usando el no_control de la ruta.
 * Se invoca en onMounted — el usuario no necesita buscar dos veces.
 */
const cargarDatos = async () => {
  const noControl = route.params.no_control
  if (!noControl) {
    errorCarga.value = true
    return
  }

  cargando.value   = true
  errorCarga.value = false

  try {
    const [resKardex, resHorario] = await Promise.allSettled([
      fetch(`${API_URL}/api/kardex/${noControl}`),
      fetch(`${API_URL}/api/horario/${noControl}`)
    ])

    // Kardex principal
    if (resKardex.status === 'fulfilled' && resKardex.value.ok) {
      const data    = await resKardex.value.json()
      alumno.value  = data.alumno  || null
      kardex.value  = data.kardex  || { semestres: [] }

      // Abrir el semestre más reciente por defecto
      if (kardex.value.semestres?.length > 0) {
        const ultimo = kardex.value.semestres.at(-1)
        semestresAbiertos.value = [ultimo.numero]
      }
    } else {
      throw new Error('Error al cargar el kardex')
    }

    // Horario (opcional, no bloquea si falla)
    if (resHorario.status === 'fulfilled' && resHorario.value.ok) {
      const dataH   = await resHorario.value.json()
      horario.value = dataH.horario || []
    }

  } catch (err) {
    console.error('[KardexDetalle] Error al cargar datos:', err)
    errorCarga.value = true
  } finally {
    cargando.value = false
  }
}

onMounted(cargarDatos)

// ── Acordeón de semestres ───────────────────────────────────────────────
const toggleSemestre = (num) => {
  const idx = semestresAbiertos.value.indexOf(num)
  if (idx === -1) semestresAbiertos.value.push(num)
  else            semestresAbiertos.value.splice(idx, 1)
}
const expandirTodo = () => {
  semestresAbiertos.value = (kardex.value.semestres || []).map(s => s.numero)
}
const contraerTodo = () => {
  semestresAbiertos.value = []
}

// ── Helpers de UI ───────────────────────────────────────────────────────
const badgeEstadoClase = (estado) => {
  const e = (estado || '').toLowerCase()
  if (e.includes('aprobada') || e.includes('acreditada')) return 'badge-aprobada'
  if (e.includes('reprobada'))                             return 'badge-reprobada'
  if (e.includes('curso') || e.includes('pendiente'))     return 'badge-pendiente'
  return 'badge-neutro'
}

const formatearFecha = (fecha) =>
  new Date(fecha).toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' })

// ── Exportar PDF ────────────────────────────────────────────────────────
const exportarPDF = async () => {
  exportando.value = true
  try {
    const res = await fetch(`${API_URL}/api/kardex/${route.params.no_control}/pdf`)
    if (!res.ok) throw new Error('Error al generar PDF')
    const blob = await res.blob()
    const url  = URL.createObjectURL(blob)
    const a    = document.createElement('a')
    a.href     = url
    a.download = `kardex_${route.params.no_control}.pdf`
    a.click()
    URL.revokeObjectURL(url)
    mostrarToast('PDF generado exitosamente', 'exito')
  } catch (err) {
    console.error('[KardexDetalle] Error al exportar PDF:', err)
    mostrarToast('No se pudo generar el PDF. Intenta de nuevo.', 'error')
  } finally {
    exportando.value = false
  }
}
</script>

<style scoped>
/* ══════════════════════════════════════════════════════
   VARIABLES — paleta SICE
══════════════════════════════════════════════════════ */
.kardex-page {
  --azul:       #1B396A;
  --azul-hover: #15305A;
  --azul-suave: #DBEAFE;
  --verde:      #16A34A;
  --rojo:       #DC2626;
  --amarillo:   #F59E0B;
  --borde:      #E5E7EB;
  --fondo:      #F9FAFB;
  --texto:      #111827;
  --gris:       #6B7280;
  --radio:      12px;

  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

/* ── Barra de carga ── */
.barra-carga {
  position: fixed;
  top: 0; left: 0; right: 0;
  height: 3px;
  z-index: 9999;
  opacity: 0;
  transition: opacity 0.2s;
  pointer-events: none;
}
.barra-carga.activa { opacity: 1; }
.barra-progreso {
  height: 100%;
  background: var(--azul);
  animation: progreso-carga 1.5s ease-in-out infinite;
}
@keyframes progreso-carga {
  0%   { width: 0%; opacity: 1; }
  70%  { width: 85%; opacity: 1; }
  100% { width: 100%; opacity: 0; }
}

/* ── Breadcrumb ── */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  color: var(--gris);
  margin-bottom: 1.2rem;
  flex-wrap: wrap;
}
.breadcrumb-link { color: var(--gris); text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul); }
.sep { color: #D1D5DB; }
.breadcrumb-actual { color: var(--azul); font-weight: 600; }

/* ── Toast ── */
.toast {
  position: fixed;
  top: 80px; right: 24px;
  display: flex; align-items: center; gap: 10px;
  padding: 0.9rem 1.4rem;
  border-radius: var(--radio);
  color: white; font-weight: 600; font-size: 0.9rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  z-index: 3000; max-width: 380px;
}
.toast.exito { background: var(--verde); }
.toast.error { background: var(--rojo); }
.toast-icono { display: flex; align-items: center; flex-shrink: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to       { opacity: 0; transform: translateX(110%); }

/* ── Alerta de error ── */
.alerta-error {
  display: flex; align-items: center; gap: 10px;
  background: #FEF2F2; border: 1px solid #FECACA;
  border-radius: var(--radio); padding: 12px 16px;
  margin-bottom: 1.2rem; font-size: 0.875rem;
  color: var(--rojo); font-weight: 500;
}
.alerta-icono { width: 20px; height: 20px; flex-shrink: 0; stroke: var(--rojo); }
.btn-reintentar {
  margin-left: auto; display: flex; align-items: center; gap: 6px;
  background: var(--rojo); color: white; border: none;
  padding: 6px 14px; border-radius: 6px; font-weight: 600;
  font-size: 0.82rem; cursor: pointer; font-family: inherit;
  transition: background 0.15s; white-space: nowrap;
}
.btn-reintentar:hover { background: #B91C1C; }

/* ── Skeleton ── */
.skeleton-wrap { display: flex; flex-direction: column; gap: 1rem; }
.sk-hero { display: flex; gap: 1.2rem; background: #fff; border-radius: var(--radio); border: 1px solid var(--borde); padding: 1.4rem; }
.sk-avatar { width: 88px; height: 88px; border-radius: var(--radio); background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation: shimmer 1.4s infinite; flex-shrink:0; }
.sk-hero-texto { flex:1; display:flex; flex-direction:column; gap:10px; justify-content:center; }
.sk-linea { border-radius: 6px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.sk-ancha { height: 22px; width: 60%; }
.sk-media { height: 16px; width: 45%; }
.sk-corta { height: 14px; width: 30%; }
.sk-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 1rem; }
.sk-stat { height: 72px; border-radius: var(--radio); background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.sk-tabla { background:#fff; border-radius:var(--radio); border:1px solid var(--borde); overflow:hidden; }
.sk-thead { height:40px; background:linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
.sk-fila  { height:46px; border-top:1px solid var(--borde); background:linear-gradient(90deg,#F5F5F5 25%,#FFFFFF 50%,#F5F5F5 75%); background-size:200% 100%; animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Encabezado principal ── */
.cabecera-fila {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1.2rem; flex-wrap: wrap; gap: 1rem;
}
.page-title { color: var(--texto); font-size: 1.75rem; font-weight: 700; margin: 0; }
.cabecera-acciones { display: flex; gap: 0.75rem; flex-wrap: wrap; }

/* ── Hero card del alumno ── */
.alumno-hero {
  background: #FFFFFF;
  border-radius: var(--radio);
  border: 1px solid var(--borde);
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  padding: 1.6rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

/* Avatar */
.alumno-avatar-wrap { flex-shrink: 0; position: relative; }
.alumno-foto {
  width: 88px; height: 88px; border-radius: var(--radio);
  object-fit: cover; border: 2px solid var(--borde);
  display: block;
}
.alumno-foto-placeholder {
  width: 88px; height: 88px; border-radius: var(--radio);
  background: var(--azul-suave);
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  border: 2px solid var(--borde); gap: 4px;
}
.alumno-foto-placeholder svg { stroke: var(--azul); }
.avatar-iniciales { font-size: 1.1rem; font-weight: 800; color: var(--azul); line-height: 1; }

/* Datos */
.alumno-datos-principales { flex: 1; min-width: 0; }
.alumno-nombre-fila { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 10px; }
.alumno-nombre { font-size: 1.3rem; font-weight: 700; color: var(--texto); margin: 0; }

/* Estatus badge */
.estatus-badge {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 4px 12px; border-radius: 20px;
  font-size: 0.78rem; font-weight: 700;
}
.estatus-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; flex-shrink: 0; }
.estatus-activo     { background: #DCFCE7; color: #16A34A; }
.estatus-temporal   { background: #FEF3C7; color: #F59E0B; }
.estatus-definitiva { background: #FEF2F2; color: #DC2626; }
.estatus-titulado   { background: #EDE9FE; color: #7C3AED; }
.estatus-egresado   { background: #DBEAFE; color: #1B396A; }

.alumno-meta-grid {
  display: flex; flex-wrap: wrap; gap: 8px;
}
.meta-chip {
  display: inline-flex; align-items: center; gap: 5px;
  background: var(--fondo); border: 1px solid var(--borde);
  border-radius: 20px; padding: 4px 10px;
  font-size: 0.8rem; color: var(--gris);
}
.meta-chip svg { stroke: var(--gris); flex-shrink: 0; }
.meta-chip strong { color: var(--texto); }

/* Mini stats */
.alumno-stats-rapidas {
  display: flex; gap: 1px;
  background: var(--borde);
  border-radius: var(--radio);
  overflow: hidden;
  flex-shrink: 0;
  align-self: stretch;
}
.mini-stat {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 12px 18px; background: #FFFFFF; gap: 4px; min-width: 70px; text-align: center;
}
.mini-stat-valor { font-size: 1.5rem; font-weight: 800; line-height: 1; }
.mini-stat-label { font-size: 0.72rem; color: var(--gris); font-weight: 500; white-space: nowrap; }
.mini-stat-valor.azul     { color: var(--azul); }
.mini-stat-valor.verde    { color: var(--verde); }
.mini-stat-valor.rojo     { color: var(--rojo); }
.mini-stat-valor.amarillo { color: var(--amarillo); }

/* ── Créditos ── */
.creditos-card {
  background: #FFFFFF; border-radius: var(--radio); border: 1px solid var(--borde);
  box-shadow: 0 4px 12px rgba(0,0,0,0.04); padding: 1.2rem 1.4rem;
  margin-bottom: 1.5rem; position: relative;
}
.creditos-header {
  display: flex; justify-content: space-between; align-items: center;
  flex-wrap: wrap; gap: 0.5rem; margin-bottom: 10px;
}
.creditos-titulo-wrap { display: flex; align-items: center; gap: 10px; }
.creditos-titulo { font-size: 0.9rem; font-weight: 700; color: var(--texto); }
.creditos-porcentaje { font-size: 1rem; font-weight: 800; color: var(--azul); }
.creditos-porcentaje.completo { color: var(--verde); }
.creditos-detalle { font-size: 0.82rem; color: var(--gris); }
.creditos-barra-track { height: 10px; background: #E5E7EB; border-radius: 5px; overflow: hidden; margin-bottom: 6px; }
.creditos-barra-fill  { height: 100%; border-radius: 5px; transition: width 0.9s ease; }
.creditos-hitos {
  position: relative; height: 14px;
}
.creditos-hito {
  position: absolute;
  transform: translateX(-50%);
  font-size: 0.7rem;
  color: #9CA3AF;
}

/* ── Tabs ── */
.tabs-wrap { background: #FFFFFF; border-radius: var(--radio); border: 1px solid var(--borde); box-shadow: 0 4px 12px rgba(0,0,0,0.04); overflow: hidden; }
.tabs-header {
  display: flex;
  border-bottom: 1px solid var(--borde);
  background: var(--fondo);
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.tab-btn {
  display: flex; align-items: center; gap: 7px;
  padding: 14px 20px;
  border: none; background: transparent;
  font-family: inherit; font-size: 0.875rem; font-weight: 600;
  color: var(--gris); cursor: pointer;
  border-bottom: 3px solid transparent;
  white-space: nowrap;
  transition: color 0.2s, border-color 0.2s, background 0.15s;
}
.tab-btn:hover { color: var(--azul); background: rgba(27,57,106,0.04); }
.tab-btn.activo { color: var(--azul); border-bottom-color: var(--azul); background: #FFFFFF; }
.tab-icono { display: flex; align-items: center; }
.tab-icono svg { stroke: currentColor; }
.tab-badge {
  background: var(--azul-suave); color: var(--azul);
  font-size: 0.7rem; font-weight: 800; padding: 2px 7px; border-radius: 20px;
}

.tab-panel { padding: 1.5rem; }

/* Tab fade */
.tab-fade-enter-active, .tab-fade-leave-active { transition: opacity 0.18s, transform 0.18s; }
.tab-fade-enter-from { opacity: 0; transform: translateY(4px); }
.tab-fade-leave-to   { opacity: 0; }

/* ── Panel de materias ── */
.semestres-controles {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;
}
.seccion-subtitulo { font-size: 0.85rem; color: var(--gris); font-weight: 500; }
.controles-grupo { display: flex; gap: 0.5rem; }
.btn-control {
  background: var(--fondo); border: 1px solid var(--borde); border-radius: 8px;
  padding: 6px 14px; font-size: 0.8rem; font-weight: 600; color: var(--gris);
  cursor: pointer; font-family: inherit; transition: all 0.15s;
}
.btn-control:hover { background: var(--azul-suave); color: var(--azul); border-color: var(--azul); }

.semestre-item { background: #FFFFFF; border-radius: var(--radio); border: 1px solid var(--borde); margin-bottom: 0.75rem; overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.03); }

.semestre-cabecera {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  padding: 1rem 1.4rem; background: none; border: none;
  cursor: pointer; font-family: inherit; transition: background 0.15s;
}
.semestre-cabecera:hover { background: var(--fondo); }
.semestre-cabecera.abierto { background: var(--azul-suave); }

.semestre-izq { display: flex; align-items: center; gap: 10px; }
.semestre-num-badge {
  width: 26px; height: 26px; border-radius: 6px;
  background: var(--azul); color: white;
  font-size: 0.78rem; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.semestre-titulo { font-size: 0.95rem; font-weight: 700; color: var(--texto); }
.semestre-conteo { font-size: 0.78rem; color: var(--gris); }

.semestre-meta { display: flex; align-items: center; gap: 8px; }
.semestre-badge { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.semestre-badge.acreditadas { background: #DCFCE7; color: var(--verde); }
.semestre-badge.reprobadas  { background: #FEF2F2; color: var(--rojo); }
.semestre-badge.pendientes  { background: #FEF3C7; color: var(--amarillo); }

.semestre-flecha { stroke: var(--gris); transition: transform 0.25s; flex-shrink: 0; }
.semestre-flecha.girado { transform: rotate(180deg); }

/* Expand de semestre */
.semestre-expand-enter-active { transition: all 0.25s ease; }
.semestre-expand-leave-active { transition: all 0.2s ease; }
.semestre-expand-enter-from, .semestre-expand-leave-to { opacity: 0; transform: translateY(-6px); }

.semestre-contenido { border-top: 1px solid var(--borde); }

/* ── Tabla de materias ── */
.tabla-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.tabla-materias { width: 100%; border-collapse: collapse; min-width: 560px; }
.tabla-materias th {
  background: var(--fondo); padding: 10px 14px;
  font-size: 0.75rem; font-weight: 700; color: var(--gris);
  text-transform: uppercase; letter-spacing: 0.05em;
  border-bottom: 1px solid var(--borde); text-align: left; white-space: nowrap;
}
.tabla-materias th.centrado { text-align: center; }
.tabla-materias td { padding: 9px 14px; border-bottom: 1px solid #F3F4F6; vertical-align: middle; font-size: 0.875rem; }
.tabla-materias td.centrado { text-align: center; }
.tabla-materias tr:last-child td { border-bottom: none; }
.tabla-materias tr:hover { background: #FAFAFA; }
.tabla-materias tr.fila-reprobada { background: #FFF5F5; }
.tabla-materias tr.fila-pendiente { background: #FFFBEB; }

.clave-chip { font-family: 'Courier New', monospace; font-size: 0.82rem; font-weight: 700; color: var(--texto); background: var(--fondo); padding: 2px 8px; border-radius: 6px; border: 1px solid var(--borde); }
.materia-nombre-celda { display: flex; align-items: center; gap: 6px; font-weight: 500; color: var(--texto); }
.texto-secundario { color: var(--gris); }

.calificacion { font-weight: 800; font-size: 0.9rem; }
.calif-aprobada { color: var(--verde); }
.calif-reprobada { color: var(--rojo); }

.badge-estado { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; white-space: nowrap; }
.badge-aprobada  { background: #DCFCE7; color: var(--verde); }
.badge-reprobada { background: #FEF2F2; color: var(--rojo); }
.badge-pendiente { background: #FEF3C7; color: var(--amarillo); }
.badge-neutro    { background: var(--fondo); color: var(--gris); }

.punto-horario { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; display: inline-block; }
.dias-chip { background: var(--azul-suave); color: var(--azul); font-size: 0.78rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; }

.semestre-pie {
  padding: 10px 14px; background: var(--fondo);
  border-top: 1px solid var(--borde); text-align: right;
}
.semestre-promedio { font-size: 0.82rem; color: var(--gris); }
.valor-verde { color: var(--verde); font-weight: 700; }
.valor-rojo  { color: var(--rojo);  font-weight: 700; }
.font-bold   { font-weight: 700; }

/* ── Estado vacío ── */
.estado-vacio {
  display: flex; flex-direction: column; align-items: center;
  justify-content: center; gap: 12px; padding: 3rem;
  color: var(--gris); font-size: 0.9rem; text-align: center;
}
.estado-vacio svg { stroke: #9CA3AF; opacity: 0.7; }

/* ── Panel info ── */
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
.info-card {
  background: var(--fondo); border: 1px solid var(--borde);
  border-radius: var(--radio); padding: 1.2rem 1.4rem;
}
.info-card-titulo { font-size: 0.9rem; font-weight: 700; color: var(--azul); margin: 0 0 1rem; }
.info-lista { display: flex; flex-direction: column; gap: 10px; margin: 0; }
.info-fila { display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; gap: 1rem; padding: 6px 0; border-bottom: 1px solid var(--borde); }
.info-fila:last-child { border-bottom: none; }
.info-fila dt { color: var(--gris); font-weight: 500; flex-shrink: 0; }
.info-fila dd { color: var(--texto); font-weight: 600; margin: 0; text-align: right; }

/* ── Botones ── */
.btn-primario {
  background: var(--azul); color: white; border: none;
  padding: 10px 18px; border-radius: var(--radio);
  font-weight: 600; font-size: 0.875rem;
  display: flex; align-items: center; gap: 6px;
  cursor: pointer; font-family: inherit;
  transition: background 0.2s, transform 0.1s;
  box-shadow: 0 2px 8px rgba(27,57,106,0.2);
  white-space: nowrap;
}
.btn-primario:hover:not(:disabled) { background: var(--azul-hover); transform: translateY(-1px); }
.btn-primario:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; box-shadow: none; }

.btn-secundario {
  background: var(--azul-suave); color: var(--azul); border: none;
  padding: 10px 16px; border-radius: var(--radio);
  font-weight: 600; font-size: 0.875rem;
  display: flex; align-items: center; gap: 6px;
  cursor: pointer; font-family: inherit;
  transition: background 0.2s; white-space: nowrap;
}
.btn-secundario:hover { background: #BFDBFE; }

/* Spinner */
.spinner { width: 16px; height: 16px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; animation: spin 0.75s linear infinite; flex-shrink: 0; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Fade */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* ── Pie ── */
.pie-pagina { display: flex; align-items: center; justify-content: center; gap: 6px; text-align: center; color: #9CA3AF; font-size: 0.82rem; padding: 1.5rem 0 0; border-top: 1px solid var(--borde); margin-top: 1rem; }
.pie-pagina svg { stroke: #9CA3AF; flex-shrink: 0; }

/* ==================== RESPONSIVE ==================== */
@media (max-width: 900px) {
  .alumno-hero {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 1.25rem;
  }
  .alumno-stats-rapidas {
    width: 100%;
  }
  .info-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .kardex-page {
    padding: 0.75rem 0.5rem 2rem;
  }
  
  .alumno-hero {
    padding: 1.25rem;
  }
  
  .alumno-foto, .alumno-foto-placeholder {
    width: 70px;
    height: 70px;
  }
  
  .tabla-materias th,
  .tabla-materias td {
    padding: 8px 6px;
    font-size: 0.78rem;
  }
  
  .semestre-cabecera {
    padding: 0.9rem 1rem;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }
  
  .semestre-meta {
    width: 100%;
    justify-content: space-between;
  }
  
  .tabs-header {
    flex-wrap: nowrap;
    overflow-x: auto;
  }
  
  .tab-btn {
    padding: 12px 14px;
    font-size: 0.82rem;
  }
  
  .tab-panel {
    padding: 1rem;
  }
}
</style>