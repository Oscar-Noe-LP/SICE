<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="alumnos-page">

      <!-- ── Header ── -->
      <div class="page-header">
        <h1 class="page-title">Inscripciones</h1>
        <span class="page-subtitle">{{ inscripcionesFiltradas.length }} registro(s) encontrado(s)</span>
      </div>

      <!-- ── Barra de carga global ── -->
      <div class="barra-carga-global" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- ── Toast ── -->
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

      <!-- ── KPIs ── -->
      <div class="stats-grid">
        <div class="stat-card stat-azul">
          <div class="stat-ico-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2" style="width:22px;height:22px">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-label">Total Inscritos</p>
            <div v-if="cargandoKpis" class="stat-skel stat-skel-white"></div>
            <p v-else class="stat-num" style="color:#fff">{{ kpis.totalInscritos }}</p>
            <span class="stat-sub" style="color:rgba(255,255,255,0.8)">Periodo activo</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-ico-wrap" style="background:#FEF3C7">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#F59E0B" stroke-width="2" style="width:22px;height:22px">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-label">Cambios Solicitados</p>
            <div v-if="cargandoKpis" class="stat-skel"></div>
            <p v-else class="stat-num">{{ kpis.cambiosSolicitados }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-ico-wrap" style="background:#FEF2F2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#DC2626" stroke-width="2" style="width:22px;height:22px">
              <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-label">Bajas Registradas</p>
            <div v-if="cargandoKpis" class="stat-skel"></div>
            <p v-else class="stat-num">{{ kpis.bajasRegistradas }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-ico-wrap" style="background:#DCFCE7">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#16A34A" stroke-width="2" style="width:22px;height:22px">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-label">Reinscripciones</p>
            <div v-if="cargandoKpis" class="stat-skel"></div>
            <p v-else class="stat-num">{{ kpis.reinscripciones }}</p>
          </div>
        </div>
      </div>

      <!-- ── Error carga ── -->
      <div v-if="errorCarga" class="alerta-error">
        <svg xmlns="http://www.w3.org/2000/svg" class="ae-ico" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>No se pudieron cargar los datos. Verifica la conexión con el servidor.</span>
        <button class="btn-reintentar" @click="cargarDatos">Reintentar</button>
      </div>

      <!-- ── Filtros ── -->
      <!-- NOTA: la búsqueda textual solo aplica al presionar Enter o el botón Buscar.
           Los selectores de carrera/estado/periodo aplican inmediatamente al cambiar. -->
      <div class="filters-bar">
        <div class="search-group">
          <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            type="text"
            placeholder="Número de control o nombre — presiona Enter para buscar"
            v-model="busquedaInput"
            class="search-input"
            @keydown.enter="aplicarBusqueda"
            @keydown.escape="limpiarBusqueda"
          >
          <!-- indicador de búsqueda activa -->
          <span v-if="busquedaActiva" class="badge-busqueda-activa" title="Búsqueda activa — presiona Esc o Limpiar para quitar">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:12px;height:12px;stroke:#1B396A">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
          </span>
        </div>

        <!-- Botón Buscar explícito -->
        <button class="btn-buscar-tabla" @click="aplicarBusqueda" :disabled="!busquedaInput.trim()">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:15px;height:15px;stroke:currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          Buscar
        </button>

        <select v-model="filtroPeriodo" class="filter-select" @change="currentPage = 1">
          <option value="">Periodo</option>
          <option v-for="p in periodosDisponibles" :key="p" :value="p">{{ p }}</option>
        </select>

        <select v-model="filtroCarrera" class="filter-select" @change="currentPage = 1">
          <option value="">Carrera</option>
          <option value="Contador Publico">Contador Público</option>
          <option value="Ingenieria Civil">Ingeniería Civil</option>
          <option value="Ingenieria en Gestion empresarial">Ing. en Gestión Empresarial</option>
          <option value="Ingenieria en Sistemas Computacionales">Ing. en Sistemas Computacionales</option>
          <option value="Ingenieria Industrial">Ingeniería Industrial</option>
        </select>

        <select v-model="filtroEstado" class="filter-select" @change="currentPage = 1">
          <option value="">Estado</option>
          <option value="activa">Activa</option>
          <option value="baja">Baja</option>
          <option value="cambio_pendiente">Cambio pendiente</option>
        </select>

        <button class="btn-limpiar" @click="resetFiltros">
          <svg xmlns="http://www.w3.org/2000/svg" class="reset-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
          Limpiar
        </button>

        <button class="btn-nuevo" @click="abrirModalNueva">+ Nueva Inscripción</button>
      </div>

      <!-- ── Tabla ── -->
      <div class="table-container">
        <div v-if="cargando && inscripciones.length === 0" class="estado-cargando">
          <div class="spinner-tabla"></div>
          <p>Cargando registros...</p>
        </div>

        <table v-else-if="inscripcionesPaginadas.length > 0" class="alumnos-table" tabindex="0">
          <thead>
            <tr>
              <th>No. Control</th>
              <th>Alumno</th>
              <th>Carrera</th>
              <th>Periodo</th>
              <th>Materias</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(ins, index) in inscripcionesPaginadas"
              :key="ins.id_inscripcion"
              :class="{ 'fila-seleccionada': filaActiva === index }"
              @click="filaActiva = index"
            >
              <td class="celda-control">{{ ins.numero_control }}</td>
              <td>{{ ins.nombre_alumno }}</td>
              <td>{{ ins.carrera }}</td>
              <td>{{ ins.periodo }}</td>
              <td class="celda-semestre">{{ ins.total_materias }}</td>
              <td>
                <span class="estatus-badge" :class="claseEstado(ins.estado)">{{ etiquetaEstado(ins.estado) }}</span>
              </td>
              <td class="celda-acciones">
                <button class="btn-accion ver" @click.stop="abrirModalVer(ins)" title="Ver detalles">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Ver
                </button>
                <button class="btn-accion editar" @click.stop="abrirModalEditar(ins)" title="Editar registro">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Editar
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else class="estado-vacio">
          <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <h3>Sin resultados</h3>
          <p>No se encontraron inscripciones con los criterios aplicados.</p>
          <button class="btn-limpiar-vacio" @click="resetFiltros">Limpiar filtros</button>
        </div>
      </div>

      <!-- ── Paginación ── -->
      <div class="paginacion">
        <div class="paginacion-izquierda">
          Filas por página:
          <select v-model="filasPorPagina" @change="currentPage = 1" class="select-filas">
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
        </div>
        <div class="paginacion-centro">Página {{ currentPage }} de {{ totalPages }}</div>
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

    </div><!-- fin .alumnos-page -->

    <!-- ══════════════════════════════════════════════════
         MODAL VER
    ══════════════════════════════════════════════════ -->
    <div v-if="showModalVer" class="modal-overlay" @click.self="showModalVer = false">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Detalle de Inscripción</h3>
          <button @click="showModalVer = false" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body" v-if="inscripcionVer">
          <div class="detalle-fila"><span class="detalle-label">No. Control</span><span class="detalle-valor">{{ inscripcionVer.numero_control }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Alumno</span><span class="detalle-valor">{{ inscripcionVer.nombre_alumno }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Carrera</span><span class="detalle-valor">{{ inscripcionVer.carrera }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Periodo</span><span class="detalle-valor">{{ inscripcionVer.periodo }}</span></div>
          <div class="detalle-fila"><span class="detalle-label">Total Materias</span><span class="detalle-valor">{{ inscripcionVer.total_materias }}</span></div>
          <div class="detalle-fila">
            <span class="detalle-label">Estado</span>
            <span class="estatus-badge" :class="claseEstado(inscripcionVer.estado)">{{ etiquetaEstado(inscripcionVer.estado) }}</span>
          </div>
          <div v-if="inscripcionVer.materias && inscripcionVer.materias.length">
            <p style="font-size:.88rem;font-weight:700;color:#1B396A;margin:12px 0 8px;font-family:'Montserrat',sans-serif">Materias inscritas</p>
            <div v-for="mat in inscripcionVer.materias" :key="mat.id_materia"
                 style="display:flex;align-items:center;gap:8px;font-size:.88rem;color:#1A1A1A;padding:5px 0;border-bottom:1px solid #F3F4F6;font-family:'Montserrat',sans-serif">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#16A34A" stroke-width="2" style="width:15px;height:15px;flex-shrink:0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ mat.nombre_materia }} — Grupo {{ mat.grupo }}
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="showModalVer = false">Cerrar</button>
          <button class="btn-guardar" @click="abrirModalEditar(inscripcionVer); showModalVer = false">Editar</button>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════
         MODAL CREAR / EDITAR
    ══════════════════════════════════════════════════ -->
    <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ form.id_inscripcion ? 'Editar Inscripción' : 'Nueva Inscripción' }}</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">

          <div class="form-grupo">
            <label>Número de Control *</label>
            <div v-if="form.id_inscripcion" class="modal-input deshabilitado">{{ form.numero_control }}</div>
            <template v-else>
              <div style="display:flex;gap:8px">
                <input
                  v-model="form.numero_control"
                  type="text"
                  class="modal-input"
                  placeholder="Ej: 21456887"
                  @keydown.enter="buscarAlumnoPorControl"
                  style="flex:1"
                />
                <button class="btn-buscar-modal" @click="buscarAlumnoPorControl" :disabled="buscandoAlumno">
                  <span v-if="buscandoAlumno" class="spinner-btn"></span>
                  <span v-else>Buscar</span>
                </button>
              </div>
            </template>
          </div>

          <transition name="notif-fade">
            <div v-if="alumnoEncontrado" class="bloque-alumno-found">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#16A34A" stroke-width="2" style="width:22px;height:22px;flex-shrink:0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div>
                <p style="margin:0 0 2px;font-weight:700;font-size:.92rem;color:#1A1A1A;font-family:'Montserrat',sans-serif">{{ alumnoEncontrado.nombre }}</p>
                <p style="margin:0;font-size:.8rem;color:#6B7280;font-family:'Montserrat',sans-serif">{{ alumnoEncontrado.carrera }} · Semestre {{ alumnoEncontrado.semestre }}</p>
              </div>
            </div>
          </transition>

          <div class="form-grupo-doble">
            <div class="form-grupo">
              <label>Periodo *</label>
              <select v-model="form.id_periodo" class="modal-select">
                <option value="">Seleccionar...</option>
                <option v-for="p in periodosDB" :key="p.id_periodo" :value="p.id_periodo">{{ p.nombre_periodo }}</option>
              </select>
            </div>
            <div class="form-grupo">
              <label>Estado *</label>
              <select v-model="form.estado" class="modal-select">
                <option value="activa">Activa</option>
                <option value="baja">Baja</option>
                <option value="cambio_pendiente">Cambio pendiente</option>
              </select>
            </div>
          </div>

          <div class="form-grupo">
            <label>Observaciones</label>
            <textarea v-model="form.observaciones" class="modal-textarea" rows="3" placeholder="Observaciones opcionales..."></textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModal" :disabled="guardando">Cancelar</button>
          <button v-if="form.id_inscripcion" class="btn-eliminar" @click="pedirConfirmarEliminar" :disabled="guardando">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke:#fff">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Eliminar
          </button>
          <button class="btn-guardar" @click="guardarInscripcion" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke:#fff">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ guardando ? 'Guardando...' : 'Guardar cambios' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════════════════
         MODAL CONFIRMAR ELIMINAR
    ══════════════════════════════════════════════════ -->
    <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false">
      <div class="modal-content" style="max-width:460px">
        <div class="modal-header" style="background:#DC2626">
          <h3>Confirmar eliminación</h3>
          <button @click="showModalEliminar = false" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <p style="font-size:.95rem;color:#1A1A1A;line-height:1.6;margin:0;font-family:'Montserrat',sans-serif">
            ¿Estás seguro de que deseas eliminar la inscripción de
            <strong>{{ inscripcionAEliminar?.nombre_alumno }}</strong>?
            Esta acción no se puede deshacer.
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="showModalEliminar = false" :disabled="guardando">Cancelar</button>
          <button class="btn-eliminar" @click="confirmarEliminar" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke:#fff">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            {{ guardando ? 'Eliminando...' : 'Sí, eliminar' }}
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

// ─────────────────────────────────────────────────────────────────────────────
// BASE URL — el equipo de back solo cambia esta constante
// ─────────────────────────────────────────────────────────────────────────────
const API = 'http://localhost:8000/api'

// ─────────────────────────────────────────────────────────────────────────────
// ESTADOS
// ─────────────────────────────────────────────────────────────────────────────
const inscripciones    = ref([])
const periodosDB       = ref([])
const cargando         = ref(false)
const cargandoKpis     = ref(false)
const guardando        = ref(false)
const errorCarga       = ref(false)
const filaActiva       = ref(-1)
const kpis = ref({ totalInscritos: 0, cambiosSolicitados: 0, bajasRegistradas: 0, reinscripciones: 0 })

// ── Búsqueda: dos variables separadas ──
// busquedaInput  → lo que escribe el usuario en tiempo real (no filtra nada)
// busquedaActiva → el término que se aplica SOLO cuando presiona Enter o Buscar
const busquedaInput  = ref('')
const busquedaActiva = ref('')

const filtroPeriodo  = ref('')
const filtroCarrera  = ref('')
const filtroEstado   = ref('')
const filasPorPagina = ref(10)
const currentPage    = ref(1)

const showModalVer      = ref(false)
const showModal         = ref(false)
const showModalEliminar = ref(false)
const inscripcionVer      = ref(null)
const inscripcionAEliminar = ref(null)
const alumnoEncontrado    = ref(null)
const buscandoAlumno      = ref(false)

const formVacio = () => ({
  id_inscripcion: null, numero_control: '', id_alumno: null,
  id_periodo: '', estado: 'activa', observaciones: ''
})
const form = ref(formVacio())

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

// ─────────────────────────────────────────────────────────────────────────────
// BÚSQUEDA — solo aplica al hacer Enter o clic en Buscar
// ─────────────────────────────────────────────────────────────────────────────
const aplicarBusqueda = () => {
  const q = busquedaInput.value.trim()
  if (!q) return
  busquedaActiva.value = q
  currentPage.value    = 1
}

const limpiarBusqueda = () => {
  busquedaInput.value  = ''
  busquedaActiva.value = ''
  currentPage.value    = 1
}

// ─────────────────────────────────────────────────────────────────────────────
// CARGA DE DATOS
// ─────────────────────────────────────────────────────────────────────────────

/*
 * GET /api/inscripciones
 * Respuesta esperada (array):
 * [{
 *   id_inscripcion: number,
 *   id_alumno:      number,
 *   numero_control: string,
 *   nombre_alumno:  string,
 *   carrera:        string,
 *   id_periodo:     number,
 *   periodo:        string,
 *   total_materias: number,
 *   estado:         "activa" | "baja" | "cambio_pendiente",
 *   observaciones:  string | null,
 *   materias: [{ id_materia, nombre_materia, grupo }]  <- opcional, para modal Ver
 * }]
 */
const cargarInscripciones = async () => {
  cargando.value   = true
  errorCarga.value = false
  try {
    const res = await fetch(`${API}/inscripciones`)
    if (!res.ok) throw new Error(`Error ${res.status}`)
    inscripciones.value = await res.json()
    console.log('✅ Inscripciones cargadas:', inscripciones.value.length)
  } catch (err) {
    console.error('❌ cargarInscripciones:', err)
    errorCarga.value = true
    mostrarNotificacion('No se pudo cargar la lista de inscripciones.', 'error')
  } finally {
    cargando.value = false
  }
}

/*
 * GET /api/inscripciones/kpis
 * Respuesta: { totalInscritos, cambiosSolicitados, bajasRegistradas, reinscripciones }
 */
const cargarKpis = async () => {
  cargandoKpis.value = true
  try {
    const res = await fetch(`${API}/inscripciones/kpis`)
    if (res.ok) kpis.value = await res.json()
  } catch { console.error('❌ kpis') }
  finally { cargandoKpis.value = false }
}

/*
 * GET /api/periodos
 * Respuesta: [{ id_periodo, nombre_periodo }]
 */
const cargarPeriodos = async () => {
  try {
    const res = await fetch(`${API}/periodos`)
    if (res.ok) periodosDB.value = await res.json()
  } catch { console.error('❌ periodos') }
}

const cargarDatos = () => { cargarInscripciones(); cargarKpis(); cargarPeriodos() }
onMounted(cargarDatos)

// ─────────────────────────────────────────────────────────────────────────────
// BUSCAR ALUMNO EN MODAL (por número de control exacto)
// GET /api/alumnos/control/:nc
// Respuesta: { id_alumno, nombre, carrera, semestre }
// ─────────────────────────────────────────────────────────────────────────────
const buscarAlumnoPorControl = async () => {
  const nc = (form.value.numero_control || '').trim()
  if (!nc) return
  buscandoAlumno.value   = true
  alumnoEncontrado.value = null
  try {
    const res = await fetch(`${API}/alumnos/control/${nc}`)
    if (!res.ok) throw new Error('No encontrado')
    const d = await res.json()
    alumnoEncontrado.value = d
    form.value.id_alumno   = d.id_alumno
    mostrarNotificacion('Alumno encontrado', 'exito')
  } catch {
    mostrarNotificacion(`No se encontró el No. de Control ${nc}`, 'error')
    form.value.id_alumno = null
  } finally {
    buscandoAlumno.value = false
  }
}

// ─────────────────────────────────────────────────────────────────────────────
// CRUD
// ─────────────────────────────────────────────────────────────────────────────

/*
 * POST /api/inscripciones
 * Body: { id_alumno, id_periodo, estado, observaciones }
 *
 * PUT /api/inscripciones/:id
 * Body: { id_periodo, estado, observaciones }
 */
const guardarInscripcion = async () => {
  if (!form.value.id_periodo) { mostrarNotificacion('El periodo es requerido.', 'error'); return }
  if (!form.value.id_inscripcion && !form.value.id_alumno) {
    mostrarNotificacion('Busca y selecciona un alumno primero.', 'error'); return
  }
  guardando.value = true
  const esEd = !!form.value.id_inscripcion
  try {
    const url  = esEd ? `${API}/inscripciones/${form.value.id_inscripcion}` : `${API}/inscripciones`
    const meth = esEd ? 'PUT' : 'POST'
    const body = esEd
      ? { id_periodo: form.value.id_periodo, estado: form.value.estado, observaciones: form.value.observaciones }
      : { id_alumno: form.value.id_alumno, id_periodo: form.value.id_periodo, estado: form.value.estado, observaciones: form.value.observaciones }
    console.log(`🔵 ${meth}`, url, body)
    const res  = await fetch(url, { method: meth, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(body) })
    const data = await res.json()
    if (!res.ok) throw new Error(JSON.stringify(data))
    await cargarInscripciones(); await cargarKpis()
    cerrarModal()
    mostrarNotificacion(esEd ? 'Inscripción actualizada.' : 'Inscripción creada.', 'exito')
  } catch (e) { console.error('❌', e); mostrarNotificacion('Error al guardar.', 'error') }
  finally { guardando.value = false }
}

/*
 * DELETE /api/inscripciones/:id
 * Respuesta: { message: "Eliminado correctamente" }
 */
const pedirConfirmarEliminar = () => {
  inscripcionAEliminar.value = { ...form.value, nombre_alumno: alumnoEncontrado.value?.nombre || form.value.numero_control }
  showModal.value         = false
  showModalEliminar.value = true
}

const confirmarEliminar = async () => {
  guardando.value = true
  try {
    const res  = await fetch(`${API}/inscripciones/${inscripcionAEliminar.value.id_inscripcion}`, {
      method: 'DELETE', headers: { 'Accept': 'application/json' }
    })
    const data = await res.json()
    if (!res.ok) throw new Error(JSON.stringify(data))
    await cargarInscripciones(); await cargarKpis()
    showModalEliminar.value = false
    mostrarNotificacion('Inscripción eliminada.', 'exito')
  } catch (e) { console.error('❌', e); mostrarNotificacion('Error al eliminar.', 'error') }
  finally { guardando.value = false }
}

// ─────────────────────────────────────────────────────────────────────────────
// MODALES
// ─────────────────────────────────────────────────────────────────────────────
const abrirModalNueva  = () => { form.value = formVacio(); alumnoEncontrado.value = null; showModal.value = true }
const abrirModalVer    = (ins) => { inscripcionVer.value = ins; showModalVer.value = true }
const abrirModalEditar = (ins) => {
  form.value = {
    id_inscripcion: ins.id_inscripcion,
    numero_control: ins.numero_control,
    id_alumno:      ins.id_alumno ?? null,
    id_periodo:     ins.id_periodo ?? '',
    estado:         ins.estado,
    observaciones:  ins.observaciones ?? ''
  }
  alumnoEncontrado.value = { nombre: ins.nombre_alumno, carrera: ins.carrera, semestre: ins.semestre ?? '' }
  showModal.value = true
}
const cerrarModal = () => { showModal.value = false }

// ─────────────────────────────────────────────────────────────────────────────
// FILTROS Y PAGINACIÓN
// Los selectores filtran en tiempo real.
// La búsqueda textual solo aplica cuando busquedaActiva tiene valor
// (se establece al presionar Enter o el botón Buscar).
// ─────────────────────────────────────────────────────────────────────────────
const normalize = t => (t || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')

const inscripcionesFiltradas = computed(() => {
  return inscripciones.value.filter(i => {
    // ── Búsqueda textual: solo activa si busquedaActiva tiene valor ──
    const q = normalize(busquedaActiva.value)
    const matchQ = !q
      || normalize(i.numero_control).includes(q)
      || normalize(i.nombre_alumno).includes(q)

    // ── Selectores: filtran inmediatamente ──
    const matchP = !filtroPeriodo.value || normalize(i.periodo).includes(normalize(filtroPeriodo.value))
    const matchC = !filtroCarrera.value || normalize(i.carrera).includes(normalize(filtroCarrera.value))
    const matchE = !filtroEstado.value  || i.estado === filtroEstado.value

    return matchQ && matchP && matchC && matchE
  })
})

const periodosDisponibles = computed(() => [...new Set(inscripciones.value.map(i => i.periodo).filter(Boolean))])

const totalPages = computed(() => Math.ceil(inscripcionesFiltradas.value.length / filasPorPagina.value) || 1)

const inscripcionesPaginadas = computed(() => {
  const s = (currentPage.value - 1) * filasPorPagina.value
  return inscripcionesFiltradas.value.slice(s, s + filasPorPagina.value)
})

const visiblePages = computed(() => {
  const t = totalPages.value, c = currentPage.value
  if (t <= 7) return Array.from({ length: t }, (_, i) => i + 1)
  const p = new Set([1, t, c, c - 1, c + 1].filter(x => x >= 1 && x <= t))
  return [...p].sort((a, b) => a - b)
})

const goToPage  = (p) => { currentPage.value = p; filaActiva.value = -1 }
const prevPage  = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage  = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const resetFiltros = () => {
  busquedaInput.value  = ''
  busquedaActiva.value = ''
  filtroPeriodo.value  = ''
  filtroCarrera.value  = ''
  filtroEstado.value   = ''
  currentPage.value    = 1
  filaActiva.value     = -1
}

// ─────────────────────────────────────────────────────────────────────────────
// HELPERS BADGE
// ─────────────────────────────────────────────────────────────────────────────
const claseEstado    = (e) => ({ 'activo': e === 'activa', 'baja-temporal': e === 'baja', 'baja-definitiva': e === 'cambio_pendiente' })
const etiquetaEstado = (e) => ({ activa: 'Activa', baja: 'Baja', cambio_pendiente: 'Cambio pendiente' }[e] || e)
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ════════════════════════════════════════════
   VARIABLES — solo dentro de .alumnos-page
   Los modales usan colores hardcoded
════════════════════════════════════════════ */
.alumnos-page {
  --azul:       #1B396A;
  --azul-hover: #1D4ED8;
  --borde:      #E5E7EB;
  --fondo:      #F5F5F5;
  --texto:      #1A1A1A;
  --gris:       #6B7280;
  max-width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

/* ── Header ── */
.page-header   { display: flex; align-items: baseline; gap: 1rem; margin-bottom: 1.2rem; }
.page-title    { color: #1A1A1A; font-size: 1.75rem; font-weight: 700; letter-spacing: -.02em; margin: 0; }
.page-subtitle { font-size: .9rem; color: #6B7280; font-weight: 500; }

/* ── Barra carga ── */
.barra-carga-global  { height: 3px; background: transparent; border-radius: 2px; margin-bottom: 1rem; overflow: hidden; transition: opacity .3s; opacity: 0; }
.barra-carga-global.visible { opacity: 1; }
.barra-progreso { height: 100%; width: 40%; background: #1B396A; border-radius: 2px; animation: deslizar 1.2s ease-in-out infinite; }
@keyframes deslizar { 0%{transform:translateX(-100%)} 100%{transform:translateX(350%)} }

/* ── Toast ── */
.notificacion-ui { display: flex; align-items: center; gap: 10px; padding: 12px 18px; border-radius: 10px; font-size: .93rem; font-weight: 500; margin-bottom: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.notificacion-ui.exito { background: #DCFCE7; color: #16A34A; border: 1px solid #86EFAC; }
.notificacion-ui.error { background: #FEE2E2; color: #DC2626; border: 1px solid #FCA5A5; }
.notif-icono { width: 20px; height: 20px; flex-shrink: 0; }
.notif-fade-enter-active, .notif-fade-leave-active { transition: all .35s ease; }
.notif-fade-enter-from, .notif-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ── KPIs ── */
.stats-grid { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card  { background: #FFFFFF; border-radius: 12px; padding: 1.3rem 1.4rem; display: flex; align-items: center; gap: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,.05); border: 1px solid #E5E7EB; transition: transform .2s, box-shadow .2s; min-width: 0; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,.09); }
.stat-azul { background: #1B396A !important; border-color: #1B396A !important; }
.stat-azul .stat-label { color: rgba(255,255,255,.8) !important; }
.stat-ico-wrap { width: 46px; height: 46px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #DBEAFE; }
.stat-azul .stat-ico-wrap { background: rgba(255,255,255,.2) !important; }
.stat-info  { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.stat-label { font-size: .83rem; color: #6B7280; font-weight: 500; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.stat-num   { font-size: 2rem; font-weight: 700; color: #1A1A1A; margin: 2px 0; line-height: 1; }
.stat-sub   { font-size: .82rem; color: #1B396A; font-weight: 600; white-space: nowrap; }
.stat-skel  { width: 80px; height: 32px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size: 200% 100%; animation: shimmer 1.4s infinite; border-radius: 6px; margin: 2px 0; }
.stat-skel-white { background: linear-gradient(90deg,rgba(255,255,255,.2) 25%,rgba(255,255,255,.35) 50%,rgba(255,255,255,.2) 75%) !important; background-size: 200% 100% !important; }
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ── Error carga ── */
.alerta-error { display: flex; align-items: center; gap: 10px; background: #FEF2F2; border: 1px solid #FECACA; border-radius: 10px; padding: 12px 16px; margin-bottom: 1.4rem; font-size: .9rem; color: #DC2626; font-weight: 500; }
.ae-ico { width: 20px; height: 20px; flex-shrink: 0; stroke: #DC2626; }
.btn-reintentar { margin-left: auto; background: #1B396A; color: #FFFFFF; border: none; padding: 7px 16px; border-radius: 6px; font-weight: 600; font-size: .85rem; cursor: pointer; font-family: 'Montserrat',sans-serif; transition: background .15s; white-space: nowrap; }
.btn-reintentar:hover { background: #1D4ED8; }

/* ── Filtros ── */
.filters-bar { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.2rem; flex-wrap: wrap; }

.search-group { position: relative; flex: 0 0 340px; min-width: 240px; }
.search-input {
  width: 100%; padding: 10px 14px 10px 42px;
  border: 1px solid #E5E7EB; border-radius: 8px;
  font-size: .93rem; background: #FFFFFF; color: #1A1A1A;
  font-family: 'Montserrat',sans-serif; outline: none;
  transition: border-color .2s, box-shadow .2s; box-sizing: border-box;
}
.search-input:focus   { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: #6B7280; pointer-events: none; }

/* indicador de búsqueda activa */
.badge-busqueda-activa {
  position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
  background: #DBEAFE; border-radius: 50%;
  width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;
}

/* Botón Buscar explícito */
.btn-buscar-tabla {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: .92rem;
  cursor: pointer; font-family: 'Montserrat',sans-serif; white-space: nowrap;
  background: #1B396A; color: #FFFFFF; border: 2px solid #1B396A;
  transition: background .15s, border-color .15s;
}
.btn-buscar-tabla:hover:not(:disabled) { background: #1D4ED8; border-color: #1D4ED8; }
.btn-buscar-tabla:disabled { opacity: .45; cursor: not-allowed; }

.filter-select { padding: 10px 12px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: .92rem; flex: 1 1 150px; min-width: 130px; background: #FFFFFF; color: #1A1A1A; font-family: 'Montserrat',sans-serif; cursor: pointer; outline: none; }
.filter-select:focus { border-color: #1B396A; }

.btn-limpiar { display: flex; align-items: center; gap: 6px; background: #FFFFFF; color: #1A1A1A; border: 1px solid #E5E7EB; padding: 10px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: .92rem; white-space: nowrap; font-family: 'Montserrat',sans-serif; transition: background .15s; }
.btn-limpiar:hover { background: #F5F5F5; }
.reset-icon { width: 16px; height: 16px; stroke: #6B7280; }

.btn-nuevo { background: #1B396A; color: #FFFFFF; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; white-space: nowrap; font-family: 'Montserrat',sans-serif; font-size: .92rem; transition: background .2s; margin-left: auto; }
.btn-nuevo:hover { background: #1D4ED8; }

/* ── Tabla ── */
.table-container { background: #FFFFFF; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,.05); border: 1px solid #E5E7EB; }
.alumnos-table   { width: 100%; border-collapse: collapse; outline: none; }
.alumnos-table th { background: #F5F5F5; padding: 12px 16px; text-align: left; font-weight: 600; font-size: .88rem; color: #1A1A1A; border-bottom: 1px solid #E5E7EB; font-family: 'Montserrat',sans-serif; white-space: nowrap; }
.alumnos-table td { padding: 11px 16px; border-bottom: 1px solid #E5E7EB; color: #1A1A1A; font-size: .93rem; font-family: 'Montserrat',sans-serif; }
.alumnos-table tbody tr { transition: background .15s; cursor: pointer; }
.alumnos-table tbody tr:hover { background: #F8FAFC; }
.alumnos-table tbody tr:last-child td { border-bottom: none; }
.fila-seleccionada { background: #DBEAFE !important; }
.celda-control  { font-weight: 600; color: #1B396A; font-size: .92rem; }
.celda-semestre { text-align: center; }

/* ── Badges ── */
.estatus-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: .83rem; font-weight: 600; font-family: 'Montserrat',sans-serif; }
.estatus-badge.activo         { background: #DCFCE7; color: #16A34A; }
.estatus-badge.baja-temporal  { background: #FEE2E2; color: #DC2626; }
.estatus-badge.baja-definitiva { background: #FEF3C7; color: #F59E0B; }

/* ── Botones acciones tabla ── */
.celda-acciones { display: flex; gap: 7px; align-items: center; }
.btn-accion { display: flex; align-items: center; gap: 5px; padding: 6px 13px; border-radius: 6px; font-size: .85rem; cursor: pointer; font-weight: 600; font-family: 'Montserrat',sans-serif; transition: background .15s, border-color .15s; white-space: nowrap; }
.btn-accion svg { width: 14px; height: 14px; }
.btn-accion.ver    { background: #FFFFFF; color: #1A1A1A; border: 1px solid #E5E7EB; }
.btn-accion.ver:hover { background: #F5F5F5; }
.btn-accion.editar { background: #1B396A; color: #FFFFFF; border: 1px solid #1B396A; }
.btn-accion.editar svg { stroke: #FFFFFF; }
.btn-accion.editar:hover { background: #1D4ED8; border-color: #1D4ED8; }

/* ── Estado vacío / cargando ── */
.estado-vacio, .estado-cargando { text-align: center; padding: 3.5rem 2rem; color: #6B7280; }
.icono-vacio { width: 56px; height: 56px; stroke: #9CA3AF; margin-bottom: 12px; }
.estado-vacio h3 { font-size: 1.2rem; color: #1A1A1A; margin: 0 0 6px; font-family: 'Montserrat',sans-serif; }
.estado-vacio p  { font-size: .93rem; margin: 0 0 1.2rem; font-family: 'Montserrat',sans-serif; }
.spinner-tabla { display: inline-block; width: 36px; height: 36px; border: 3px solid #E5E7EB; border-top-color: #1B396A; border-radius: 50%; animation: girar .8s linear infinite; margin-bottom: 12px; }
.btn-limpiar-vacio { background: #FFFFFF; color: #1A1A1A; border: 1px solid #E5E7EB; padding: 9px 20px; border-radius: 8px; font-weight: 500; cursor: pointer; font-family: 'Montserrat',sans-serif; }
.btn-limpiar-vacio:hover { background: #F5F5F5; }

/* ── Paginación ── */
.paginacion { margin-top: 1.2rem; display: flex; justify-content: space-between; align-items: center; font-size: .9rem; color: #6B7280; font-family: 'Montserrat',sans-serif; flex-wrap: wrap; gap: .5rem; }
.paginacion-izquierda, .paginacion-centro, .paginacion-derecha { display: flex; align-items: center; gap: 8px; }
.select-filas { border: 1px solid #E5E7EB; border-radius: 6px; padding: 4px 8px; font-size: .9rem; background: #FFFFFF; font-family: 'Montserrat',sans-serif; }
.btn-pag { padding: 5px 11px; border: 1px solid #E5E7EB; background: #FFFFFF; border-radius: 6px; cursor: pointer; color: #1A1A1A; font-family: 'Montserrat',sans-serif; font-size: .9rem; transition: background .15s; }
.btn-pag:hover:not(:disabled) { background: #F5F5F5; }
.btn-pag:disabled { opacity: .4; cursor: not-allowed; }
.btn-pag.activo { background: #1B396A; color: #FFFFFF; border-color: #1B396A; }

/* ════════════════════════════════════════════
   MODALES — colores hardcoded (no usan variables
   de .alumnos-page porque están fuera del div)
════════════════════════════════════════════ */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.55);
  display: flex; align-items: center; justify-content: center;
  z-index: 2000; padding: 1rem;
}
.modal-content {
  background: #FFFFFF; width: 520px; max-width: 95%;
  border-radius: 14px; box-shadow: 0 20px 50px rgba(0,0,0,.22);
  overflow: hidden; border: 1px solid #E5E7EB;
  max-height: 90vh; display: flex; flex-direction: column;
  font-family: 'Montserrat',sans-serif;
}
.modal-header {
  background: #1B396A; color: #FFFFFF;
  padding: 1.1rem 1.6rem; display: flex;
  justify-content: space-between; align-items: center; flex-shrink: 0;
}
.modal-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; color: #FFFFFF; font-family: 'Montserrat',sans-serif; }
.btn-cerrar-modal { background: none; border: none; color: #FFFFFF; font-size: 1.8rem; cursor: pointer; line-height: 1; opacity: .85; padding: 0; }
.btn-cerrar-modal:hover { opacity: 1; }

.modal-body { padding: 1.6rem; overflow-y: auto; flex: 1; }
.modal-footer {
  padding: 1rem 1.6rem; background: #F5F5F5;
  display: flex; gap: 10px; justify-content: flex-end;
  border-top: 1px solid #E5E7EB; flex-shrink: 0;
}

.form-grupo         { margin-bottom: 1.2rem; }
.form-grupo-doble   { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-grupo label   { display: block; margin-bottom: 6px; font-weight: 600; font-size: .9rem; color: #1A1A1A; font-family: 'Montserrat',sans-serif; }
.modal-input, .modal-select, .modal-textarea {
  width: 100%; padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px;
  font-size: .95rem; background: #FFFFFF; color: #1A1A1A;
  font-family: 'Montserrat',sans-serif; outline: none;
  transition: border-color .2s, box-shadow .2s; box-sizing: border-box;
}
.modal-input:focus, .modal-select:focus, .modal-textarea:focus { border-color: #1B396A; box-shadow: 0 0 0 3px #DBEAFE; }
.modal-input::placeholder, .modal-textarea::placeholder { color: #9CA3AF; }
.modal-textarea { resize: vertical; }
.modal-input.deshabilitado { background: #F5F5F5; cursor: not-allowed; color: #6B7280; font-weight: 600; display: flex; align-items: center; border: 1.5px solid #E5E7EB; border-radius: 8px; padding: 10px 14px; }

/* Botón buscar dentro del modal */
.btn-buscar-modal {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 16px; border-radius: 8px; font-weight: 600; font-size: .9rem;
  cursor: pointer; font-family: 'Montserrat',sans-serif; white-space: nowrap;
  background: #1B396A; color: #FFFFFF; border: 2px solid #1B396A;
  transition: background .15s;
}
.btn-buscar-modal:hover:not(:disabled) { background: #1D4ED8; border-color: #1D4ED8; }
.btn-buscar-modal:disabled { opacity: .6; cursor: not-allowed; }

/* Bloque alumno encontrado */
.bloque-alumno-found {
  display: flex; align-items: center; gap: 10px;
  background: #F0FDF4; border: 1px solid #86EFAC;
  border-radius: 8px; padding: 10px 14px; margin-bottom: 1.2rem;
}

/* Detalle en modal Ver */
.detalle-fila { display: flex; justify-content: space-between; align-items: center; padding: 11px 0; border-bottom: 1px solid #E5E7EB; font-size: .95rem; font-family: 'Montserrat',sans-serif; }
.detalle-fila:last-child { border-bottom: none; }
.detalle-label { font-weight: 600; color: #6B7280; }
.detalle-valor { font-weight: 500; color: #1A1A1A; }

/* ── Botón Cancelar (secundario) ── */
.btn-secundario {
  padding: 10px 22px; border-radius: 8px; font-weight: 600; font-size: .92rem;
  cursor: pointer; font-family: 'Montserrat',sans-serif;
  background: #FFFFFF; color: #1B396A;
  border: 2px solid #1B396A;
  transition: background .15s;
}
.btn-secundario:hover:not(:disabled) { background: #DBEAFE; }
.btn-secundario:disabled { opacity: .5; cursor: not-allowed; }

/* ── Botón Eliminar ── */
.btn-eliminar {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 22px; border-radius: 8px; font-weight: 600; font-size: .92rem;
  cursor: pointer; font-family: 'Montserrat',sans-serif;
  background: #DC2626; color: #FFFFFF;
  border: 2px solid #DC2626;
  transition: background .15s;
}
.btn-eliminar:hover:not(:disabled) { background: #B91C1C; border-color: #B91C1C; }
.btn-eliminar:disabled { opacity: .5; cursor: not-allowed; }

/* ── Botón Guardar (primario) ── */
.btn-guardar {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 10px 22px; border-radius: 8px; font-weight: 600; font-size: .92rem;
  cursor: pointer; font-family: 'Montserrat',sans-serif;
  background: #1B396A; color: #FFFFFF;
  border: 2px solid #1B396A;
  transition: background .15s;
}
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; border-color: #1D4ED8; }
.btn-guardar:disabled { opacity: .55; cursor: not-allowed; }

/* ── Spinner ── */
.spinner-btn { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,.4); border-top-color: #FFFFFF; border-radius: 50%; animation: girar .7s linear infinite; flex-shrink: 0; }
@keyframes girar { to{transform:rotate(360deg)} }

@media(max-width:1024px) { .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr));} }
@media(max-width:640px)  { .stats-grid{grid-template-columns:1fr;} .filters-bar{flex-direction:column;} .filter-select,.search-group{width:100%;flex:none;} .form-grupo-doble{grid-template-columns:1fr;} }
</style>