<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="asignacion-docente-page">

      <!-- ══ Breadcrumb ══ -->
      <nav class="breadcrumb">
        <span class="breadcrumb-actual">Asignación Docente a Grupos</span>
      </nav>

      <!-- ══ Encabezado ══ -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Asignación Docente a Grupos</h1>
          <span class="page-subtitle">Gestión de asignaciones del periodo activo</span>
        </div>
      </div>

      <!-- ══ Barra de carga global ══ -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ══ Notificación UI ══ -->
      <transition name="notif-fade">
        <div v-if="notificacion.visible" class="notificacion-ui" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="notif-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ══ Alerta fija: grupos sin docente ══ -->
      <transition name="alerta-fade">
        <div v-if="!cargando && gruposSinDocente > 0" class="alerta-urgente">
          <svg xmlns="http://www.w3.org/2000/svg" class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <span>
            <strong>Atención:</strong> Hay <strong>{{ gruposSinDocente }}</strong>
            grupo{{ gruposSinDocente !== 1 ? 's' : '' }} sin docente asignado en el periodo activo.
          </span>
          <button class="alerta-btn-filtrar" @click="filtrarSinDocente">
            Ver grupos sin docente
          </button>
        </div>
      </transition>

      <!-- ══ KPIs con skeleton loading ══ -->
      <div class="kpi-grid">

        <!-- Skeleton mientras carga -->
        <template v-if="cargando">
          <div v-for="i in 3" :key="i" class="kpi-card kpi-skeleton">
            <div class="skeleton-icono"></div>
            <div class="skeleton-texto">
              <div class="skeleton-linea skeleton-linea-corta"></div>
              <div class="skeleton-linea skeleton-linea-larga"></div>
            </div>
          </div>
        </template>

        <!-- KPIs reales -->
        <template v-else>
          <div class="kpi-card kpi-rojo">
            <div class="kpi-icono-wrap kpi-icono-rojo">
              <svg xmlns="http://www.w3.org/2000/svg" class="kpi-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <div class="kpi-contenido">
              <p class="kpi-valor kpi-valor-rojo">{{ kpis.sinDocente }}</p>
              <p class="kpi-etiqueta">Grupos sin docente asignado</p>
            </div>
          </div>

          <div class="kpi-card kpi-verde">
            <div class="kpi-icono-wrap kpi-icono-verde">
              <svg xmlns="http://www.w3.org/2000/svg" class="kpi-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="kpi-contenido">
              <p class="kpi-valor kpi-valor-verde">{{ kpis.conDocente }}</p>
              <p class="kpi-etiqueta">Grupos con docente asignado</p>
            </div>
          </div>

          <div class="kpi-card kpi-azul">
            <div class="kpi-icono-wrap kpi-icono-azul">
              <svg xmlns="http://www.w3.org/2000/svg" class="kpi-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <div class="kpi-contenido">
              <p class="kpi-valor kpi-valor-azul">{{ kpis.docentesDisponibles }}</p>
              <p class="kpi-etiqueta">Docentes disponibles en el periodo</p>
            </div>
          </div>
        </template>
      </div>

      <!-- ══ Barra de filtros ══ -->
      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Buscar por materia o clave de grupo..."
            v-model="filtros.busqueda"
            class="search-input"
            @keydown.escape="filtros.busqueda = ''"
          >
          <span v-if="cargandoBusqueda" class="spinner-busqueda"></span>
        </div>

        <select v-model="filtros.periodo" class="filter-select" @change="currentPage = 1">
          <option value="">Todos los periodos</option>
          <option v-for="p in periodosDisponibles" :key="p" :value="p">{{ p }}</option>
        </select>

        <select v-model="filtros.carrera" class="filter-select" @change="currentPage = 1">
          <option value="">Todas las carreras</option>
          <option value="Ingenieria en Sistemas Computacionales">Ing. en Sistemas Computacionales</option>
          <option value="Ingenieria Civil">Ingeniería Civil</option>
          <option value="Ingenieria Industrial">Ingeniería Industrial</option>
          <option value="Ingenieria en Gestion empresarial">Ing. en Gestión Empresarial</option>
          <option value="Contador Publico">Contador Público</option>
        </select>

        <select v-model="filtros.estado" class="filter-select" @change="currentPage = 1">
          <option value="">Todos los estados</option>
          <option value="Con docente">Con docente</option>
          <option value="Sin docente">Sin docente</option>
        </select>

        <button class="btn-limpiar" @click="resetFiltros">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpiar
        </button>
      </div>

      <!-- ══ Tabla principal ══ -->
      <div class="table-container">

        <!-- Estado: cargando con skeleton -->
        <template v-if="cargando && grupos.length === 0">
          <div class="skeleton-tabla">
            <div v-for="i in 5" :key="i" class="skeleton-fila">
              <div class="skeleton-celda skeleton-celda-corta"></div>
              <div class="skeleton-celda skeleton-celda-larga"></div>
              <div class="skeleton-celda skeleton-celda-media"></div>
              <div class="skeleton-celda skeleton-celda-corta"></div>
              <div class="skeleton-celda skeleton-celda-media"></div>
              <div class="skeleton-celda skeleton-celda-corta"></div>
              <div class="skeleton-celda skeleton-celda-badge"></div>
              <div class="skeleton-celda skeleton-celda-acciones"></div>
            </div>
          </div>
        </template>

        <table
          v-else-if="paginatedGrupos.length > 0"
          class="alumnos-table"
          ref="tablaRef"
          @keydown="navegarTeclado"
          tabindex="0"
        >
          <thead>
            <tr>
              <th>Clave del Grupo</th>
              <th>Materia</th>
              <th>Carrera</th>
              <th>Semestre</th>
              <th>Horario</th>
              <th>Capacidad</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(grupo, index) in paginatedGrupos"
              :key="grupo.id_grupo || grupo.id"
              :class="{ 'fila-seleccionada': filaActiva === index, 'fila-urgente': grupo.estado === 'Sin docente' }"
              @click="filaActiva = index"
            >
              <td class="celda-clave">{{ grupo.clave_grupo }}</td>
              <td class="celda-materia">{{ grupo.materia }}</td>
              <td class="celda-carrera">{{ grupo.carrera }}</td>
              <td class="celda-semestre">{{ grupo.semestre }}°</td>
              <td class="celda-horario">
                <div class="horario-linea">{{ grupo.dia }}</div>
                <div class="horario-hora">{{ grupo.hora_inicio }} - {{ grupo.hora_fin }}</div>
              </td>
              <td class="celda-capacidad">
                <div class="capacidad-wrap">
                  <span class="capacidad-numero">{{ grupo.inscritos }}/{{ grupo.capacidad }}</span>
                  <div class="capacidad-barra">
                    <div class="capacidad-relleno"
                         :style="{ width: calcularCapacidad(grupo) + '%' }"
                         :class="claseCapacidad(grupo)">
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <div class="estado-celda">
                  <span class="estado-badge" :class="claseEstado(grupo.estado)">
                    {{ grupo.estado }}
                  </span>
                  <span v-if="grupo.docente" class="docente-asignado">{{ grupo.docente }}</span>
                </div>
              </td>
              <td class="celda-acciones">
                <button
                  class="btn-accion ver"
                  @click.stop="abrirModalDetalle(grupo)"
                  title="Ver detalle del grupo"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver detalle
                </button>
                <button
                  :class="['btn-accion', grupo.estado === 'Sin docente' ? 'asignar' : 'cambiar']"
                  @click.stop="abrirModalAsignacion(grupo)"
                  :title="grupo.estado === 'Sin docente' ? 'Asignar docente' : 'Cambiar docente'"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {{ grupo.estado === 'Sin docente' ? 'Asignar' : 'Cambiar' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <h3>Sin grupos encontrados</h3>
          <p>No se encontraron grupos con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFiltros">Limpiar filtros</button>
        </div>
      </div>

      <!-- ══ Paginación ══ -->
      <div class="paginacion">
        <div class="paginacion-izquierda">
          Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-centro">
          Página {{ currentPage }} de {{ totalPages }}
        </div>
        <div class="paginacion-derecha">
          <button class="btn-pag" @click="prevPage" :disabled="currentPage === 1">‹</button>
          <button
            v-for="page in visiblePages" :key="page"
            class="btn-pag" :class="{ activo: page === currentPage }"
            @click="goToPage(page)"
          >{{ page }}</button>
          <button class="btn-pag" @click="nextPage" :disabled="currentPage === totalPages">›</button>
        </div>
      </div>

    </div>

    <!-- ══════════════════════════════════════
         MODAL: VER DETALLE DEL GRUPO (solo lectura)
    ═══════════════════════════════════════ -->
    <div v-if="showModalDetalle" class="modal-overlay" @click.self="cerrarModalDetalle">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Detalle del Grupo</h3>
          <button @click="cerrarModalDetalle" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <div class="detalle-fila">
            <span class="detalle-label">Clave del Grupo</span>
            <span class="detalle-valor">{{ grupoDetalle.clave_grupo }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Materia</span>
            <span class="detalle-valor">{{ grupoDetalle.materia }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Carrera</span>
            <span class="detalle-valor">{{ grupoDetalle.carrera }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Semestre</span>
            <span class="detalle-valor">{{ grupoDetalle.semestre }}° Semestre</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Horario</span>
            <span class="detalle-valor">{{ grupoDetalle.dia }} {{ grupoDetalle.hora_inicio }} - {{ grupoDetalle.hora_fin }}</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Capacidad</span>
            <span class="detalle-valor">{{ grupoDetalle.inscritos }} inscritos / {{ grupoDetalle.capacidad }} lugares</span>
          </div>
          <div class="detalle-fila">
            <span class="detalle-label">Estado</span>
            <span class="estado-badge" :class="claseEstado(grupoDetalle.estado)">
              {{ grupoDetalle.estado }}
            </span>
          </div>
          <div class="detalle-fila" v-if="grupoDetalle.docente">
            <span class="detalle-label">Docente asignado</span>
            <span class="detalle-valor">{{ grupoDetalle.docente }}</span>
          </div>
          <div class="detalle-fila" v-if="grupoDetalle.periodo">
            <span class="detalle-label">Periodo</span>
            <span class="detalle-valor">{{ grupoDetalle.periodo }}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalDetalle">Cerrar</button>
          <button class="btn-guardar" @click="cerrarModalDetalle(); abrirModalAsignacion(grupoDetalle)">
            {{ grupoDetalle.estado === 'Sin docente' ? 'Asignar docente' : 'Cambiar docente' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         MODAL: ASIGNACIÓN / CAMBIO DE DOCENTE (grande)
    ═══════════════════════════════════════ -->
    <div v-if="showModalAsignacion" class="modal-overlay" @click.self="cerrarModalAsignacion">
      <div class="modal-content modal-asignacion">
        <div class="modal-header">
          <div>
            <h3>{{ grupoAsignacion.estado === 'Sin docente' ? 'Asignar Docente' : 'Cambiar Docente' }}</h3>
            <span class="modal-subtitulo">{{ grupoAsignacion.clave_grupo }} — {{ grupoAsignacion.materia }}</span>
          </div>
          <button @click="cerrarModalAsignacion" class="btn-cerrar-modal">×</button>
        </div>

        <div class="modal-body">

          <!-- Información del grupo (solo lectura) -->
          <div class="info-grupo-modal">
            <div class="info-grupo-item">
              <span class="info-grupo-label">Carrera</span>
              <span class="info-grupo-valor">{{ grupoAsignacion.carrera }}</span>
            </div>
            <div class="info-grupo-item">
              <span class="info-grupo-label">Semestre</span>
              <span class="info-grupo-valor">{{ grupoAsignacion.semestre }}°</span>
            </div>
            <div class="info-grupo-item">
              <span class="info-grupo-label">Horario</span>
              <span class="info-grupo-valor">{{ grupoAsignacion.dia }} {{ grupoAsignacion.hora_inicio }} - {{ grupoAsignacion.hora_fin }}</span>
            </div>
            <div class="info-grupo-item" v-if="grupoAsignacion.docente">
              <span class="info-grupo-label">Docente actual</span>
              <span class="info-grupo-valor info-grupo-docente">{{ grupoAsignacion.docente }}</span>
            </div>
          </div>

          <!-- Alerta de conflicto de horario -->
          <transition name="alerta-fade">
            <div v-if="conflictoHorario" class="alerta-conflicto">
              <svg xmlns="http://www.w3.org/2000/svg" class="conflicto-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <div>
                <p class="conflicto-titulo">Conflicto de horario detectado</p>
                <p class="conflicto-desc">{{ mensajeConflicto }}</p>
              </div>
            </div>
          </transition>

          <!-- Buscador de docente -->
          <div class="form-grupo">
            <label class="etiqueta-modal">
              Buscar docente
              <span class="obligatorio">*</span>
              <span class="etiqueta-hint">Por nombre o número de empleado</span>
            </label>
            <div class="search-group search-docente">
              <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                type="text"
                placeholder="Nombre del docente o número de empleado..."
                v-model="busquedaDocente"
                class="search-input"
                @input="filtrarDocentes"
                @keydown.escape="busquedaDocente = ''"
              >
              <span v-if="cargandoDocentes" class="spinner-busqueda"></span>
            </div>
          </div>

          <!-- Lista de docentes disponibles -->
          <div class="lista-docentes">
            <div v-if="cargandoDocentes" class="docentes-cargando">
              <div class="spinner-tabla"></div>
              <p>Buscando docentes...</p>
            </div>

            <template v-else-if="docentesFiltrados.length > 0">
              <div
                v-for="(docente, i) in docentesFiltrados"
                :key="docente.id_docente || i"
                class="docente-item"
                :class="{
                  'docente-seleccionado': docenteSeleccionado?.id_docente === docente.id_docente,
                  'docente-conflicto': docente.tieneConflicto
                }"
                @click="seleccionarDocente(docente)"
              >
                <div class="docente-avatar">{{ inicialDocente(docente.nombre) }}</div>
                <div class="docente-info">
                  <span class="docente-nombre">{{ docente.nombre }}</span>
                  <span class="docente-detalle">
                    Núm. empleado: {{ docente.numero_empleado }} ·
                    {{ docente.carga_actual }} grupo{{ docente.carga_actual !== 1 ? 's' : '' }} asignado{{ docente.carga_actual !== 1 ? 's' : '' }}
                  </span>
                </div>
                <div class="docente-estado-wrap">
                  <span v-if="docente.tieneConflicto" class="docente-badge conflicto-badge">
                    Conflicto de horario
                  </span>
                  <span v-else class="docente-badge disponible-badge">Disponible</span>
                  <svg v-if="docenteSeleccionado?.id_docente === docente.id_docente"
                       xmlns="http://www.w3.org/2000/svg" class="check-seleccion" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </div>
            </template>

            <div v-else class="docentes-vacio">
              <p>{{ busquedaDocente ? 'No se encontraron docentes con ese criterio.' : 'Escribe para buscar docentes disponibles.' }}</p>
            </div>
          </div>

          <!-- Error de validación -->
          <transition name="error-fade">
            <small v-if="errorAsignacion" class="mensaje-error">{{ errorAsignacion }}</small>
          </transition>
        </div>

        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalAsignacion" :disabled="guardando">Cancelar</button>
          <button
            class="btn-guardar"
            @click="guardarAsignacion"
            :disabled="guardando || !docenteSeleccionado || conflictoHorario"
          >
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Guardando...' : 'Guardar asignación' }}
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>


<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

// ── URL base del backend (variable de entorno) ──────────────────────
const API_URL = import.meta.env.VITE_API_URL

// ── Estado principal ────────────────────────────────────────────────
const grupos          = ref([])
const docentes        = ref([])
const cargando        = ref(false)
const cargandoDocentes = ref(false)
const cargandoBusqueda = ref(false)
const guardando       = ref(false)
const filaActiva      = ref(-1)
const tablaRef        = ref(null)

// ── Filtros y paginación ────────────────────────────────────────────
const filtros = ref({
  busqueda: '',
  periodo:  '',
  carrera:  '',
  estado:   ''
})
const filasPorPagina = ref(10)
const currentPage    = ref(1)

// ── Periodos disponibles (se poblan desde backend) ──────────────────
const periodosDisponibles = ref(['2026-1', '2025-2', '2025-1'])

// ── Notificación UI ─────────────────────────────────────────────────
const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ── Modales ─────────────────────────────────────────────────────────
const showModalDetalle   = ref(false)
const showModalAsignacion = ref(false)
const grupoDetalle       = ref({})
const grupoAsignacion    = ref({})

// ── Asignación de docente ────────────────────────────────────────────
const busquedaDocente    = ref('')
const docenteSeleccionado = ref(null)
const conflictoHorario   = ref(false)
const mensajeConflicto   = ref('')
const errorAsignacion    = ref('')

// ── KPIs ─────────────────────────────────────────────────────────────
const kpis = ref({ sinDocente: 0, conDocente: 0, docentesDisponibles: 0 })

const gruposSinDocente = computed(() => kpis.value.sinDocente)

// ── Carga de grupos desde backend ────────────────────────────────────
const cargarGrupos = async () => {
  cargando.value = true
  try {
    const response = await fetch(`${API_URL}/api/asignacion-docente/grupos`)
    if (!response.ok) throw new Error('Error del servidor')
    const data = await response.json()
    grupos.value = data
    calcularKPIs()
    console.log('✅ Grupos cargados:', data.length, 'registros')
  } catch (error) {
    console.error('❌ Error cargando grupos:', error)
    mostrarNotificacion('No se pudo cargar la lista de grupos. Verifica que el servidor esté activo.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Carga de docentes disponibles ────────────────────────────────────
const cargarDocentes = async () => {
  cargandoDocentes.value = true
  try {
    const response = await fetch(`${API_URL}/api/docentes/disponibles`)
    if (!response.ok) throw new Error('Error del servidor')
    const data = await response.json()
    docentes.value = data
    kpis.value.docentesDisponibles = data.length
    console.log('✅ Docentes cargados:', data.length, 'registros')
  } catch (error) {
    console.error('❌ Error cargando docentes:', error)
  } finally {
    cargandoDocentes.value = false
  }
}

// ── Calcular KPIs desde los datos cargados ───────────────────────────
const calcularKPIs = () => {
  kpis.value.sinDocente = grupos.value.filter(g => g.estado === 'Sin docente').length
  kpis.value.conDocente = grupos.value.filter(g => g.estado === 'Con docente').length
}

onMounted(() => {
  cargarGrupos()
  cargarDocentes()
})

// ── Detección de conflicto de horario ────────────────────────────────
const verificarConflicto = (docente) => {
  if (!docente.horarios || docente.horarios.length === 0) {
    conflictoHorario.value = false
    mensajeConflicto.value = ''
    return
  }

  const hayConflicto = docente.horarios.some(h =>
    h.dia === grupoAsignacion.value.dia &&
    horariosSeSuperponen(
      grupoAsignacion.value.hora_inicio,
      grupoAsignacion.value.hora_fin,
      h.hora_inicio,
      h.hora_fin
    )
  )

  conflictoHorario.value = hayConflicto
  if (hayConflicto) {
    mensajeConflicto.value = `${docente.nombre} ya tiene asignado otro grupo el ${grupoAsignacion.value.dia} en ese horario.`
  } else {
    mensajeConflicto.value = ''
  }
}

const horariosSeSuperponen = (ini1, fin1, ini2, fin2) => {
  return ini1 < fin2 && fin1 > ini2
}

// ── Guardar asignación ───────────────────────────────────────────────
const guardarAsignacion = async () => {
  if (!docenteSeleccionado.value) {
    errorAsignacion.value = 'Selecciona un docente antes de guardar.'
    return
  }
  if (conflictoHorario.value) return

  const payload = {
    id_grupo:   grupoAsignacion.value.id_grupo,
    id_docente: docenteSeleccionado.value.id_docente
  }

  guardando.value = true
  try {
    console.log('🔵 Guardando asignación:', payload)
    const response = await fetch(`${API_URL}/api/asignacion-docente`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body:    JSON.stringify(payload)
    })
    const data = await response.json()
    console.log('🟢 Respuesta backend:', data)

    if (response.ok) {
      await cargarGrupos()
      cerrarModalAsignacion()
      mostrarNotificacion(
        `Docente asignado correctamente al grupo ${grupoAsignacion.value.clave_grupo}.`,
        'exito'
      )
    } else {
      throw new Error(JSON.stringify(data))
    }
  } catch (error) {
    console.error('❌ ERROR:', error)
    mostrarNotificacion('Ocurrió un error al guardar la asignación.', 'error')
  } finally {
    guardando.value = false
  }
}

// ── Modales: abrir y cerrar ──────────────────────────────────────────
const abrirModalDetalle = (grupo) => {
  grupoDetalle.value = { ...grupo }
  showModalDetalle.value = true
}
const cerrarModalDetalle = () => { showModalDetalle.value = false }

const abrirModalAsignacion = (grupo) => {
  grupoAsignacion.value   = { ...grupo }
  docenteSeleccionado.value = null
  busquedaDocente.value   = ''
  conflictoHorario.value  = false
  mensajeConflicto.value  = ''
  errorAsignacion.value   = ''
  showModalAsignacion.value = true
}
const cerrarModalAsignacion = () => { showModalAsignacion.value = false }

const seleccionarDocente = (docente) => {
  docenteSeleccionado.value = docente
  errorAsignacion.value = ''
  verificarConflicto(docente)
}

// ── Filtrar docentes por búsqueda ────────────────────────────────────
const docentesFiltrados = computed(() => {
  if (!busquedaDocente.value) return docentes.value
  const q = normalize(busquedaDocente.value)
  return docentes.value.filter(d =>
    normalize(d.nombre).includes(q) ||
    String(d.numero_empleado).includes(q)
  )
})

const filtrarDocentes = () => {
  docenteSeleccionado.value = null
  conflictoHorario.value = false
}

// ── Filtro rápido: solo sin docente ─────────────────────────────────
const filtrarSinDocente = () => {
  filtros.value.estado = 'Sin docente'
  currentPage.value = 1
}

// ── Helpers ─────────────────────────────────────────────────────────
const normalize = (text) => {
  if (!text) return ''
  return text.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
}

const inicialDocente = (nombre) => {
  if (!nombre) return '?'
  return nombre.charAt(0).toUpperCase()
}

const claseEstado = (estado) => {
  if (!estado) return ''
  return estado === 'Con docente' ? 'estado-con-docente' : 'estado-sin-docente'
}

const calcularCapacidad = (grupo) => {
  if (!grupo.capacidad || grupo.capacidad === 0) return 0
  return Math.round((grupo.inscritos / grupo.capacidad) * 100)
}

const claseCapacidad = (grupo) => {
  const pct = calcularCapacidad(grupo)
  if (pct >= 90) return 'capacidad-alta'
  if (pct >= 60) return 'capacidad-media'
  return 'capacidad-baja'
}

// ── Debounce de búsqueda ─────────────────────────────────────────────
let timerBusqueda = null
watch(() => filtros.value.busqueda, () => {
  cargandoBusqueda.value = true
  if (timerBusqueda) clearTimeout(timerBusqueda)
  timerBusqueda = setTimeout(() => {
    cargandoBusqueda.value = false
    currentPage.value = 1
  }, 350)
})

// ── Filtrado ─────────────────────────────────────────────────────────
const gruposFiltrados = computed(() => {
  return grupos.value.filter(g => {
    const coincideBusqueda = !filtros.value.busqueda ||
      normalize(g.materia).includes(normalize(filtros.value.busqueda)) ||
      normalize(g.clave_grupo).includes(normalize(filtros.value.busqueda))

    const coincidePeriodo = !filtros.value.periodo ||
      g.periodo === filtros.value.periodo

    const coincideCarrera = !filtros.value.carrera ||
      normalize(g.carrera).includes(normalize(filtros.value.carrera))

    const coincideEstado = !filtros.value.estado ||
      g.estado === filtros.value.estado

    return coincideBusqueda && coincidePeriodo && coincideCarrera && coincideEstado
  })
})

// ── Paginación ───────────────────────────────────────────────────────
const totalPages = computed(() =>
  Math.ceil(gruposFiltrados.value.length / filasPorPagina.value) || 1
)
const paginatedGrupos = computed(() => {
  const start = (currentPage.value - 1) * filasPorPagina.value
  return gruposFiltrados.value.slice(start, start + filasPorPagina.value)
})
const visiblePages = computed(() => {
  const total = totalPages.value, current = currentPage.value
  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1)
  const pages = new Set([1, total, current, current - 1, current + 1].filter(p => p >= 1 && p <= total))
  return [...pages].sort((a, b) => a - b)
})

const goToPage  = (page) => { currentPage.value = page; filaActiva.value = -1 }
const prevPage  = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage  = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const resetFiltros = () => {
  filtros.value = { busqueda: '', periodo: '', carrera: '', estado: '' }
  currentPage.value = 1
  filaActiva.value  = -1
}

// ── Navegación por teclado ───────────────────────────────────────────
const navegarTeclado = (e) => {
  const total = paginatedGrupos.value.length
  if (total === 0) return
  if (e.key === 'ArrowDown') { e.preventDefault(); filaActiva.value = Math.min(filaActiva.value + 1, total - 1) }
  else if (e.key === 'ArrowUp') { e.preventDefault(); filaActiva.value = Math.max(filaActiva.value - 1, 0) }
  else if (e.key === 'Enter' && filaActiva.value >= 0) { e.preventDefault(); abrirModalDetalle(paginatedGrupos.value[filaActiva.value]) }
  else if (e.key === 'PageDown') { e.preventDefault(); nextPage() }
  else if (e.key === 'PageUp')   { e.preventDefault(); prevPage() }
}
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ══ Variables locales ══ */
.asignacion-docente-page {
  --azul:        #1B396A;
  --azul-hover:  #1D4ED8;
  --azul-suave:  #DBEAFE;
  --borde:       #E5E7EB;
  --fondo:       #F5F5F5;
  --texto:       #1A1A1A;
  --gris:        #6B7280;
  --verde:       #16A34A;
  --rojo:        #DC2626;

  max-width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

/* ══ Breadcrumb ══ */
.breadcrumb { display: flex; align-items: center; gap: 6px; color: var(--gris); font-size: 0.88rem; margin-bottom: 1rem; }
.breadcrumb-actual { color: var(--gris); font-weight: 600; }

/* ══ Encabezado ══ */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.2rem; }
.page-title  { color: var(--texto); font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { font-size: 0.9rem; color: var(--gris); display: block; margin-top: 3px; }

/* ══ Barra de carga ══ */
.barra-carga-global { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; opacity: 0; transition: opacity 0.3s; }
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: var(--azul); border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }

/* ══ Notificación ══ */
.notificacion-ui { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-size: 0.93rem; font-weight: 500; margin-bottom: 1rem; }
.notificacion-ui.exito { background: #DCFCE7; color: var(--verde); border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: var(--rojo); border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all 0.35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ══ Alerta urgente ══ */
.alerta-urgente {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 18px; margin-bottom: 1.2rem;
  background: #FEF2F2; border: 1.5px solid #FECACA;
  border-radius: 10px; font-size: 0.92rem; color: var(--rojo);
  font-family: 'Montserrat', sans-serif;
}
.alerta-icono { width: 22px; height: 22px; stroke: var(--rojo); flex-shrink: 0; }
.alerta-urgente span { flex: 1; }
.alerta-btn-filtrar {
  background: var(--rojo); color: white; border: none;
  padding: 6px 14px; border-radius: 6px; font-size: 0.82rem;
  font-weight: 600; cursor: pointer; white-space: nowrap;
  font-family: 'Montserrat', sans-serif; transition: background 0.15s;
}
.alerta-btn-filtrar:hover { background: #B91C1C; }
.alerta-fade-enter-active, .alerta-fade-leave-active { transition: all 0.3s ease; }
.alerta-fade-enter-from, .alerta-fade-leave-to { opacity: 0; transform: translateY(-6px); }

/* ══ KPIs ══ */
.kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.4rem; }
.kpi-card {
  background: #FFFFFF; border-radius: 12px;
  padding: 1.2rem 1.4rem; display: flex; align-items: center; gap: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde);
  transition: transform 0.2s, box-shadow 0.2s;
}
.kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.09); }
.kpi-icono-wrap { width: 46px; height: 46px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.kpi-icono-rojo  { background: #FEF2F2; }
.kpi-icono-verde { background: #DCFCE7; }
.kpi-icono-azul  { background: var(--azul-suave); }
.kpi-icono { width: 24px; height: 24px; }
.kpi-icono-rojo  .kpi-icono { stroke: var(--rojo); }
.kpi-icono-verde .kpi-icono { stroke: var(--verde); }
.kpi-icono-azul  .kpi-icono { stroke: var(--azul); }
.kpi-valor { font-size: 1.9rem; font-weight: 700; margin: 0 0 2px; }
.kpi-valor-rojo  { color: var(--rojo); }
.kpi-valor-verde { color: var(--verde); }
.kpi-valor-azul  { color: var(--azul); }
.kpi-etiqueta { font-size: 0.82rem; color: var(--gris); margin: 0; line-height: 1.3; }

/* Skeleton KPI */
.kpi-skeleton { animation: pulso 1.5s ease-in-out infinite; }
@keyframes pulso { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
.skeleton-icono { width: 46px; height: 46px; background: #E5E7EB; border-radius: 10px; flex-shrink: 0; }
.skeleton-texto { flex: 1; display: flex; flex-direction: column; gap: 8px; }
.skeleton-linea { background: #E5E7EB; border-radius: 4px; height: 14px; }
.skeleton-linea-corta { width: 40%; }
.skeleton-linea-larga { width: 80%; }

/* ══ Filtros ══ */
.filters-bar { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.2rem; flex-wrap: wrap; }
.search-group { position: relative; flex: 0 0 300px; min-width: 220px; }
.search-input { width: 100%; padding: 10px 14px 10px 42px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.93rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; outline: none; transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box; }
.search-input:focus { border-color: var(--azul); box-shadow: 0 0 0 3px var(--azul-suave); }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: var(--gris); pointer-events: none; }
.spinner-busqueda { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; border: 2px solid var(--borde); border-top-color: var(--azul); border-radius: 50%; animation: girar 0.7s linear infinite; }
.filter-select { padding: 10px 12px; border: 1px solid var(--borde); border-radius: 8px; font-size: 0.92rem; background: #FFFFFF; color: var(--texto); font-family: 'Montserrat', sans-serif; cursor: pointer; outline: none; flex: 1 1 160px; min-width: 140px; }
.filter-select:focus { border-color: var(--azul); }
.btn-limpiar { display: flex; align-items: center; gap: 6px; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.92rem; white-space: nowrap; font-family: 'Montserrat', sans-serif; transition: background 0.15s; }
.btn-limpiar:hover { background: var(--fondo); }
.reset-icon { width: 16px; height: 16px; stroke: var(--gris); }

/* ══ Tabla ══ */
.table-container { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid var(--borde); }
.alumnos-table { width: 100%; border-collapse: collapse; outline: none; }
.alumnos-table th { background: var(--fondo); padding: 12px 14px; text-align: left; font-weight: 600; font-size: 0.83rem; color: var(--texto); border-bottom: 1px solid var(--borde); white-space: nowrap; }
.alumnos-table td { padding: 11px 14px; border-bottom: 1px solid var(--borde); color: var(--texto); font-size: 0.88rem; font-family: 'Montserrat', sans-serif; vertical-align: middle; }
.alumnos-table tbody tr { transition: background 0.15s; cursor: pointer; }
.alumnos-table tbody tr:hover { background: #F8FAFC; }
.alumnos-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: var(--azul-suave) !important; }
.fila-urgente td:first-child { border-left: 3px solid var(--rojo); }

/* Celdas específicas ══ */
.celda-clave  { font-weight: 700; color: var(--azul); font-size: 0.85rem; white-space: nowrap; }
.celda-materia { font-weight: 500; max-width: 200px; }
.celda-carrera { color: var(--gris); font-size: 0.83rem; max-width: 160px; }
.celda-semestre { text-align: center; font-weight: 600; }
.celda-horario  { white-space: nowrap; }
.horario-linea  { font-weight: 600; font-size: 0.85rem; }
.horario-hora   { font-size: 0.78rem; color: var(--gris); }

/* Capacidad ══ */
.celda-capacidad { min-width: 100px; }
.capacidad-wrap  { display: flex; flex-direction: column; gap: 4px; }
.capacidad-numero { font-size: 0.82rem; font-weight: 600; color: var(--texto); }
.capacidad-barra  { height: 5px; background: var(--borde); border-radius: 9999px; overflow: hidden; width: 80px; }
.capacidad-relleno { height: 100%; border-radius: 9999px; transition: width 0.5s ease; }
.capacidad-baja   { background: var(--verde); }
.capacidad-media  { background: #F59E0B; }
.capacidad-alta   { background: var(--rojo); }

/* Estado de asignación ══ */
.estado-celda { display: flex; flex-direction: column; gap: 4px; }
.estado-badge { display: inline-block; padding: 4px 11px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; white-space: nowrap; }
.estado-con-docente { background: #DCFCE7; color: var(--verde); }
.estado-sin-docente { background: #FEF2F2; color: var(--rojo); }
.docente-asignado   { font-size: 0.78rem; color: var(--gris); }

/* Acciones ══ */
.celda-acciones { display: flex; gap: 5px; align-items: center; white-space: nowrap; }
.btn-accion { display: flex; align-items: center; gap: 5px; padding: 6px 11px; border-radius: 6px; font-size: 0.8rem; cursor: pointer; font-weight: 600; font-family: 'Montserrat', sans-serif; transition: background 0.15s; white-space: nowrap; }
.btn-accion svg { width: 13px; height: 13px; }
.btn-accion.ver     { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); }
.btn-accion.ver:hover { background: var(--fondo); }
.btn-accion.asignar { background: var(--rojo); color: white; border: 1px solid var(--rojo); }
.btn-accion.asignar:hover { background: #B91C1C; }
.btn-accion.cambiar { background: var(--azul); color: white; border: 1px solid var(--azul); }
.btn-accion.cambiar:hover { background: var(--azul-hover); }

/* Skeleton tabla ══ */
.skeleton-tabla { padding: 0; }
.skeleton-fila { display: flex; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--borde); animation: pulso 1.5s ease-in-out infinite; }
.skeleton-celda { background: #E5E7EB; border-radius: 4px; height: 12px; }
.skeleton-celda-corta   { width: 60px; }
.skeleton-celda-media   { width: 120px; }
.skeleton-celda-larga   { flex: 1; }
.skeleton-celda-badge   { width: 80px; }
.skeleton-celda-acciones { width: 140px; }

/* Estado vacío ══ */
.estado-vacio { text-align: center; padding: 3.5rem 2rem; color: var(--gris); }
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: var(--texto); margin: 0 0 6px; }
.estado-vacio p  { font-size: 0.93rem; margin: 0 0 1.2rem; }
.btn-limpiar-vacio { background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); padding: 9px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; font-family: 'Montserrat', sans-serif; }

/* ══ Paginación ══ */
.paginacion { margin-top: 1.2rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; color: var(--gris); flex-wrap: wrap; gap: 0.5rem; }
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas { border: 1px solid var(--borde); border-radius: 6px; padding: 4px 8px; font-size: 0.9rem; background: #FFFFFF; }
.btn-pag { padding: 5px 11px; border: 1px solid var(--borde); background: #FFFFFF; border-radius: 6px; cursor: pointer; color: var(--texto); font-size: 0.9rem; transition: background 0.15s; }
.btn-pag:hover:not(:disabled) { background: var(--fondo); }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-pag.activo { background: var(--azul); color: white; border-color: var(--azul); }

/* ══ Modales ══ */
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: #FFFFFF; width: 520px; max-width: 92%; border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,0.18); overflow: hidden; border: 1px solid var(--borde); max-height: 90vh; display: flex; flex-direction: column; }
.modal-asignacion { width: 680px; }

.modal-header { background: var(--azul); color: white; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: flex-start; flex-shrink: 0; }
.modal-header h3 { margin: 0; font-size: 1.2rem; font-weight: 700; }
.modal-subtitulo { font-size: 0.82rem; opacity: 0.85; margin-top: 3px; display: block; }
.btn-cerrar-modal { background: none; border: none; color: white; font-size: 1.7rem; cursor: pointer; line-height: 1; opacity: 0.85; }
.btn-cerrar-modal:hover { opacity: 1; }

.modal-body { padding: 1.4rem 1.6rem; overflow-y: auto; flex: 1; }

/* Detalle lectura ══ */
.detalle-fila { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--borde); font-size: 0.93rem; gap: 1rem; }
.detalle-fila:last-child { border-bottom: none; }
.detalle-label { font-weight: 600; color: var(--gris); }
.detalle-valor { font-weight: 500; text-align: right; }

/* Info grupo en modal asignación ══ */
.info-grupo-modal {
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 0.6rem; padding: 12px 14px;
  background: var(--fondo); border-radius: 10px;
  margin-bottom: 1.2rem; border: 1px solid var(--borde);
}
.info-grupo-item { display: flex; flex-direction: column; gap: 2px; }
.info-grupo-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gris); }
.info-grupo-valor { font-size: 0.88rem; font-weight: 600; color: var(--texto); }
.info-grupo-docente { color: var(--azul); }

/* Alerta conflicto ══ */
.alerta-conflicto {
  display: flex; align-items: flex-start; gap: 10px;
  padding: 12px 14px; margin-bottom: 1rem;
  background: #FEF3C7; border: 1px solid #FDE68A;
  border-radius: 8px;
}
.conflicto-icono { width: 20px; height: 20px; stroke: #D97706; flex-shrink: 0; margin-top: 1px; }
.conflicto-titulo { font-size: 0.88rem; font-weight: 700; color: #92400E; margin: 0 0 2px; }
.conflicto-desc   { font-size: 0.82rem; color: #92400E; margin: 0; }

/* Formulario en modal ══ */
.form-grupo { margin-bottom: 1rem; }
.etiqueta-modal { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-weight: 600; font-size: 0.9rem; color: var(--texto); }
.obligatorio { color: var(--rojo); }
.etiqueta-hint { font-weight: 400; font-size: 0.78rem; color: var(--azul); background: var(--azul-suave); padding: 2px 7px; border-radius: 10px; }
.search-docente { flex: 1; width: 100%; }
.search-docente .search-input { flex: 1; }

/* Lista docentes ══ */
.lista-docentes {
  border: 1px solid var(--borde); border-radius: 10px;
  overflow: hidden; max-height: 280px; overflow-y: auto;
}
.docentes-cargando, .docentes-vacio {
  text-align: center; padding: 2rem;
  color: var(--gris); font-size: 0.88rem;
}
.spinner-tabla { display: inline-block; width: 28px; height: 28px; border: 3px solid var(--borde); border-top-color: var(--azul); border-radius: 50%; animation: girar 0.8s linear infinite; margin-bottom: 8px; }

.docente-item {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px; cursor: pointer;
  border-bottom: 1px solid var(--borde);
  transition: background 0.15s;
}
.docente-item:last-child { border-bottom: none; }
.docente-item:hover { background: #F8FAFC; }
.docente-seleccionado { background: #EFF6FF !important; }
.docente-conflicto { opacity: 0.7; }

.docente-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--azul); color: white; font-size: 0.9rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.docente-info { flex: 1; }
.docente-nombre  { display: block; font-weight: 600; font-size: 0.9rem; color: var(--texto); }
.docente-detalle { display: block; font-size: 0.78rem; color: var(--gris); margin-top: 2px; }
.docente-estado-wrap { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.docente-badge { font-size: 0.75rem; font-weight: 600; padding: 3px 9px; border-radius: 20px; }
.disponible-badge { background: #DCFCE7; color: var(--verde); }
.conflicto-badge  { background: #FEF3C7; color: #D97706; }
.check-seleccion  { width: 18px; height: 18px; stroke: var(--azul); }

.mensaje-error { display: flex; align-items: center; gap: 4px; color: var(--rojo); font-size: 0.82rem; margin-top: 8px; }
.error-fade-enter-active, .error-fade-leave-active { transition: all 0.25s ease; }
.error-fade-enter-from, .error-fade-leave-to { opacity: 0; }


/* Footer modal ═══════════════════════════════ */
.modal-footer {
  padding: 1rem 1.6rem;
  background: var(--fondo);
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  border-top: 1px solid var(--borde);
  flex-shrink: 0;
}

/* Botón Guardar */
.btn-guardar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 1.02rem;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: #1B396A; /* Azul institucional */
  color: white;
  border: none;
  box-shadow: 0 4px 12px rgba(27, 57, 106, 0.3);
  transition: all 0.2s ease;
}

.btn-guardar:hover:not(:disabled) {
  background: #1D4ED8;
  box-shadow: 0 6px 16px rgba(27, 57, 106, 0.4);
  transform: translateY(-1px);
}

.btn-guardar:disabled {
  background: #9AA3AF;
  color: white;
  opacity: 0.85;
  cursor: not-allowed;
  box-shadow: none;
}

/* Botón Cancelar  */
.btn-secundario {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  font-family: 'Montserrat', sans-serif;
  background: transparent;
  color: #1B396A;
  border: 2px solid #1B396A;
  transition: all 0.2s ease;
}

.btn-secundario:hover:not(:disabled) {
  background: #1B396A;
  color: white;
  transform: translateY(-1px);
}

.btn-secundario:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

/* Spinner del botón */
.spinner-btn {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: girar 0.7s linear infinite;
}

/* Animación */
@keyframes girar { 
  to { transform: rotate(360deg); } 
}

@keyframes girar { to { transform: rotate(360deg); } }
</style>
