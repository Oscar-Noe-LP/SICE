<template>
  <MainLayout>
    <div class="ea-page">

      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="$router.push('/gestion-academica')">Gestión Académica</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">Edificios y Aulas</span>
      </nav>

      <div class="page-header">
        <h1 class="page-title">Edificios y Aulas</h1>
        <p class="page-subtitle">Administra la infraestructura del campus</p>
      </div>

      <div class="barra-carga" :class="{ visible: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <transition name="toast-slide">
        <div v-if="notificacion.visible" class="toast" :class="notificacion.tipo">
          <svg v-if="notificacion.tipo === 'exito'" xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- ══════════════════ EDIFICIOS ══════════════════ -->
      <div class="seccion-card">
        <div class="seccion-header">
          <div class="seccion-titulo-grupo">
            <div class="seccion-icono-wrapper">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div>
              <h2 class="seccion-titulo">Edificios</h2>
              <p class="seccion-subtitulo">{{ edificios.length }} edificio(s) registrado(s)</p>
            </div>
          </div>
          <!-- Buscador inline siempre visible (no necesita botón Filtros extra) -->
          <div class="seccion-acciones">
            <div class="search-group search-inline-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input type="text" placeholder="Buscar edificio..." v-model="busquedaEdificio" class="search-input" @keydown.escape="busquedaEdificio = ''">
              <button v-if="busquedaEdificio" class="btn-limpiar-input" @click="busquedaEdificio = ''; paginaEdificios = 1" title="Limpiar">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <button class="btn-nuevo" @click="abrirModalNuevoEdificio">+ Nuevo edificio</button>
          </div>
        </div>

        <div class="tabla-interna">
          <div v-if="cargando && edificios.length === 0" class="estado-cargando">
            <div class="spinner-tabla"></div>
            <p>Cargando edificios...</p>
          </div>

          <table v-else-if="edificiosFiltrados.length > 0" class="data-table">
            <thead>
              <tr>
                <th>Nombre del Edificio</th>
                <th class="th-centro">Aulas registradas</th>
                <th class="th-centro">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="edificio in edificiosPaginados"
                :key="edificio.id_edificio"
                :class="{ 'fila-seleccionada': edificioSeleccionado?.id_edificio === edificio.id_edificio }"
                @click="seleccionarEdificio(edificio)"
                class="fila-clickable"
              >
                <td class="celda-nombre">
                  <div class="nombre-con-icono">
                    <span class="punto-edificio"></span>
                    {{ edificio.nombre_edificio }}
                  </div>
                </td>
                <td class="td-centro">
                  <span class="badge-cantidad">{{ contarAulas(edificio.id_edificio) }}</span>
                </td>
                <td class="td-centro">
                  <div class="acciones-fila">
                    <button class="btn-icono editar" @click.stop="abrirModalEditarEdificio(edificio)" title="Editar">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button class="btn-icono eliminar-btn" @click.stop="solicitarEliminarEdificio(edificio)" title="Eliminar">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-else class="estado-vacio">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
            </svg>
            <p>No se encontraron edificios.</p>
          </div>

          <!-- Paginación edificios -->
          <div v-if="totalPaginasEdificios > 1" class="paginacion">
            <button class="btn-pag" @click="paginaEdificios--" :disabled="paginaEdificios === 1">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <span class="pag-info">Página {{ paginaEdificios }} de {{ totalPaginasEdificios }}</span>
            <button class="btn-pag" @click="paginaEdificios++" :disabled="paginaEdificios === totalPaginasEdificios">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>

        <!-- Indicador edificio seleccionado -->
        <div v-if="edificioSeleccionado" class="indicador-seleccion">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Mostrando aulas del edificio: <strong>{{ edificioSeleccionado.nombre_edificio }}</strong>
          <button class="btn-ver-todas" @click="edificioSeleccionado = null">Ver todas</button>
        </div>
      </div>

      <div class="divisor"><div class="divisor-linea"></div></div>

      <!-- ══════════════════ AULAS ══════════════════ -->
      <div class="seccion-card">
        <div class="seccion-header">
          <div class="seccion-titulo-grupo">
            <div class="seccion-icono-wrapper seccion-icono-morado">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
            <div>
              <h2 class="seccion-titulo">Aulas</h2>
              <p class="seccion-subtitulo">
                {{ aulasFiltradas.length }} aula(s)
                {{ edificioSeleccionado ? `en ${edificioSeleccionado.nombre_edificio}` : 'en total' }}
              </p>
            </div>
          </div>
          <!-- Buscador siempre visible + botón Filtros para el select Edificio (filtro real) -->
          <div class="seccion-acciones">
            <div class="search-group search-inline-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input type="text" placeholder="Buscar aula..." v-model="busquedaAula" class="search-input" @keydown.escape="busquedaAula = ''">
              <button v-if="busquedaAula" class="btn-limpiar-input" @click="busquedaAula = ''" title="Limpiar">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <button
              class="btn-filtro"
              @click="panelFiltrosAulas = !panelFiltrosAulas"
              :class="{ activo: panelFiltrosAulas || filtroEdificioAula }"
              title="Filtrar por edificio"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
              Filtros
              <span v-if="filtroEdificioAula" class="filtro-badge">1</span>
            </button>
            <button class="btn-nuevo" @click="abrirModalNuevaAula">+ Nueva aula</button>
          </div>
        </div>

        <!-- Panel colapsable: solo el select Edificio (filtro real) -->
        <transition name="panel-slide">
          <div v-if="panelFiltrosAulas" class="panel-filtros">
            <div class="panel-filtros-inner">
              <div class="filtro-grupo filtro-grupo-sm">
                <label class="filtro-label">Edificio</label>
                <select v-model="filtroEdificioAula" class="filter-select" :disabled="!!edificioSeleccionado">
                  <option value="">Todos</option>
                  <option v-for="e in edificios" :key="e.id_edificio" :value="e.id_edificio">{{ e.nombre_edificio }}</option>
                </select>
              </div>
              <button class="btn-limpiar-filtros" @click="filtroEdificioAula = ''">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                Limpiar
              </button>
            </div>
          </div>
        </transition>

        <div class="tabla-interna">
          <div v-if="cargando && aulas.length === 0" class="estado-cargando">
            <div class="spinner-tabla"></div>
            <p>Cargando aulas...</p>
          </div>

          <table v-else-if="aulasFiltradas.length > 0" class="data-table">
            <thead>
              <tr>
                <th>Nombre del Aula</th>
                <th>Edificio</th>
                <th class="th-centro">Capacidad</th>
                <th class="th-centro">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(aula, index) in aulasPaginadas"
                :key="aula.id_aula"
                :class="{ 'fila-seleccionada-aula': filaAulaActiva === index }"
                @click="filaAulaActiva = index"
              >
                <td class="celda-nombre">{{ aula.nombre }}</td>
                <td>
                  <span class="badge-edificio">{{ nombreEdificio(aula.id_edificio) }}</span>
                </td>
                <td class="td-centro">
                  <span class="badge-capacidad">{{ aula.capacidad }} lugares</span>
                </td>
                <td class="td-centro">
                  <div class="acciones-fila">
                    <button class="btn-icono editar" @click.stop="abrirModalEditarAula(aula)" title="Editar">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button class="btn-icono eliminar-btn" @click.stop="solicitarEliminarAula(aula)" title="Eliminar">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-else class="estado-vacio">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <p>{{ edificioSeleccionado ? `No hay aulas registradas en ${edificioSeleccionado.nombre_edificio}.` : 'No se encontraron aulas.' }}</p>
          </div>

          <!-- Paginación aulas -->
          <div v-if="totalPaginasAulas > 1" class="paginacion">
            <button class="btn-pag" @click="paginaAulas--" :disabled="paginaAulas === 1">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <span class="pag-info">Página {{ paginaAulas }} de {{ totalPaginasAulas }}</span>
            <button class="btn-pag" @click="paginaAulas++" :disabled="paginaAulas === totalPaginasAulas">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
        </div>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México | Todos los derechos reservados</footer>
    </div>

    <!-- ══ MODAL EDIFICIO ══ -->
    <div v-if="showModalEdificio" class="modal-overlay" @click.self="cerrarModalEdificio">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ formEdificio.id_edificio ? 'Editar Edificio' : 'Nuevo Edificio' }}</h3>
          <button @click="cerrarModalEdificio" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <div class="form-grupo">
            <label>Nombre del edificio <span class="obligatorio">*</span></label>
            <input v-model.trim="formEdificio.nombre_edificio" type="text" maxlength="100" class="modal-input"
                   :class="{ 'borde-error': erroresEdificio.nombre_edificio }" placeholder="Ej. Edificio A" ref="inputEdificio">
            <small v-if="erroresEdificio.nombre_edificio" class="mensaje-error">{{ erroresEdificio.nombre_edificio }}</small>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalEdificio" :disabled="guardando">Cancelar</button>
          <button class="btn-guardar" @click="guardarEdificio" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ══ MODAL AULA ══ -->
    <div v-if="showModalAula" class="modal-overlay" @click.self="cerrarModalAula">
      <div class="modal-content">
        <div class="modal-header">
          <h3>{{ formAula.id_aula ? 'Editar Aula' : 'Nueva Aula' }}</h3>
          <button @click="cerrarModalAula" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body">
          <div class="form-grupo">
            <label>Edificio <span class="obligatorio">*</span></label>
            <select v-model="formAula.id_edificio" class="modal-select" :class="{ 'borde-error': erroresAula.id_edificio }">
              <option value="">Seleccionar edificio</option>
              <option v-for="e in edificios" :key="e.id_edificio" :value="e.id_edificio">{{ e.nombre_edificio }}</option>
            </select>
            <small v-if="erroresAula.id_edificio" class="mensaje-error">{{ erroresAula.id_edificio }}</small>
          </div>
          <div class="form-grupo">
            <label>Nombre del aula <span class="obligatorio">*</span></label>
            <input v-model.trim="formAula.nombre" type="text" maxlength="50" class="modal-input"
                   :class="{ 'borde-error': erroresAula.nombre }" placeholder="Ej. A-101">
            <small v-if="erroresAula.nombre" class="mensaje-error">{{ erroresAula.nombre }}</small>
          </div>
          <div class="form-grupo">
            <label>Capacidad <span class="obligatorio">*</span></label>
            <input v-model.number="formAula.capacidad" type="number" min="1" max="500" class="modal-input"
                   :class="{ 'borde-error': erroresAula.capacidad }" placeholder="Ej. 30">
            <small v-if="erroresAula.capacidad" class="mensaje-error">{{ erroresAula.capacidad }}</small>
            <small class="campo-ayuda">Número de lugares disponibles en el aula</small>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="cerrarModalAula" :disabled="guardando">Cancelar</button>
          <button class="btn-guardar" @click="guardarAula" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ══ MODAL CONFIRMAR ELIMINAR ══ -->
    <div v-if="showModalEliminar" class="modal-overlay" @click.self="showModalEliminar = false">
      <div class="modal-content modal-confirmar">
        <div class="modal-header">
          <h3>Confirmar eliminación</h3>
          <button @click="showModalEliminar = false" class="btn-cerrar-modal">×</button>
        </div>
        <div class="modal-body confirmar-body">
          <svg xmlns="http://www.w3.org/2000/svg" class="confirmar-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <p>¿Estás seguro de que deseas eliminar <strong>{{ itemAEliminar?.nombre_edificio || itemAEliminar?.nombre }}</strong>? Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
          <button class="btn-secundario" @click="showModalEliminar = false">Cancelar</button>
          <button class="btn-eliminar" @click="confirmarEliminar" :disabled="guardando">
            <span v-if="guardando" class="spinner-btn"></span>
            {{ guardando ? 'Eliminando...' : 'Eliminar' }}
          </button>
        </div>
      </div>
    </div>

  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted, reactive, nextTick, watch } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const edificios            = ref([])
const aulas                = ref([])
const cargando             = ref(false)
const guardando            = ref(false)
const edificioSeleccionado = ref(null)
const filaAulaActiva       = ref(-1)

// Filtros
const busquedaEdificio       = ref('')
const busquedaAula           = ref('')
const filtroEdificioAula     = ref('')
const panelFiltrosEdificios  = ref(false)
const panelFiltrosAulas      = ref(false)

// Paginación
const paginaEdificios = ref(1)
const paginaAulas     = ref(1)
const porPagina       = 10

// Modales
const showModalEdificio  = ref(false)
const showModalAula      = ref(false)
const showModalEliminar  = ref(false)
const tipoEliminar       = ref('')
const itemAEliminar      = ref(null)
const inputEdificio      = ref(null)

// Forms
const formEdificio    = reactive({ id_edificio: null, nombre_edificio: '' })
const erroresEdificio = reactive({})
const formAula        = reactive({ id_aula: null, id_edificio: '', nombre: '', capacidad: '' })
const erroresAula     = reactive({})

const notificacion = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  if (timerNotif) clearTimeout(timerNotif)
  notificacion.value = { visible: true, mensaje, tipo }
  timerNotif = setTimeout(() => { notificacion.value.visible = false }, 3500)
}

const cargarEdificios = async () => {
  try {
    const res = await fetch(`${API}/edificios`)
    if (!res.ok) throw new Error()
    edificios.value = await res.json()
  } catch { mostrarNotificacion('No se pudieron cargar los edificios.', 'error') }
}

const cargarAulas = async () => {
  try {
    const res = await fetch(`${API}/aulas`)
    if (!res.ok) throw new Error()
    aulas.value = await res.json()
  } catch { mostrarNotificacion('No se pudieron cargar las aulas.', 'error') }
}

const cargarTodo = async () => {
  cargando.value = true
  await Promise.all([cargarEdificios(), cargarAulas()])
  cargando.value = false
}

onMounted(() => { cargarTodo() })

const normalize = (t) => t?.toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '') ?? ''
const nombreEdificio = (id) => edificios.value.find(e => e.id_edificio === id)?.nombre_edificio ?? '—'
const contarAulas = (idEdificio) => aulas.value.filter(a => a.id_edificio === idEdificio).length

// Computed — Edificios
const edificiosFiltrados = computed(() =>
  !busquedaEdificio.value
    ? edificios.value
    : edificios.value.filter(e => normalize(e.nombre_edificio).includes(normalize(busquedaEdificio.value)))
)
const totalPaginasEdificios = computed(() => Math.ceil(edificiosFiltrados.value.length / porPagina))
const edificiosPaginados    = computed(() => {
  const inicio = (paginaEdificios.value - 1) * porPagina
  return edificiosFiltrados.value.slice(inicio, inicio + porPagina)
})

// Computed — Aulas
const aulasFiltradas = computed(() => {
  let r = aulas.value
  if (edificioSeleccionado.value) r = r.filter(a => a.id_edificio === edificioSeleccionado.value.id_edificio)
  else if (filtroEdificioAula.value) r = r.filter(a => a.id_edificio == filtroEdificioAula.value)
  if (busquedaAula.value) r = r.filter(a => normalize(a.nombre).includes(normalize(busquedaAula.value)))
  return r
})
const totalPaginasAulas = computed(() => Math.ceil(aulasFiltradas.value.length / porPagina))
const aulasPaginadas    = computed(() => {
  const inicio = (paginaAulas.value - 1) * porPagina
  return aulasFiltradas.value.slice(inicio, inicio + porPagina)
})

watch(busquedaEdificio, () => { paginaEdificios.value = 1 })
watch([busquedaAula, filtroEdificioAula, edificioSeleccionado], () => { paginaAulas.value = 1 })

const seleccionarEdificio = (edificio) => {
  if (edificioSeleccionado.value?.id_edificio === edificio.id_edificio) {
    edificioSeleccionado.value = null
  } else {
    edificioSeleccionado.value = edificio
    filtroEdificioAula.value   = ''
    busquedaAula.value         = ''
    filaAulaActiva.value       = -1
  }
}

const limpiarFiltrosAulas = () => {
  busquedaAula.value         = ''
  filtroEdificioAula.value   = ''
  edificioSeleccionado.value = null
  filaAulaActiva.value       = -1
  paginaAulas.value          = 1
}

// CRUD Edificios
const resetFormEdificio = () => {
  formEdificio.id_edificio = null; formEdificio.nombre_edificio = ''
  Object.keys(erroresEdificio).forEach(k => delete erroresEdificio[k])
}
const abrirModalNuevoEdificio = () => { resetFormEdificio(); showModalEdificio.value = true; nextTick(() => inputEdificio.value?.focus()) }
const abrirModalEditarEdificio = (e) => { resetFormEdificio(); formEdificio.id_edificio = e.id_edificio; formEdificio.nombre_edificio = e.nombre_edificio; showModalEdificio.value = true; nextTick(() => inputEdificio.value?.focus()) }
const cerrarModalEdificio = () => { showModalEdificio.value = false; resetFormEdificio() }
const validarEdificio = () => {
  Object.keys(erroresEdificio).forEach(k => delete erroresEdificio[k])
  if (!formEdificio.nombre_edificio.trim()) erroresEdificio.nombre_edificio = 'El nombre del edificio es obligatorio'
  return Object.keys(erroresEdificio).length === 0
}
const guardarEdificio = async () => {
  if (!validarEdificio()) return
  guardando.value = true
  const esEdicion = !!formEdificio.id_edificio
  const url = esEdicion ? `${API}/edificios/${formEdificio.id_edificio}` : `${API}/edificios`
  try {
    const res = await fetch(url, { method: esEdicion ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ nombre_edificio: formEdificio.nombre_edificio.trim() }) })
    if (!res.ok) throw new Error()
    await cargarEdificios(); cerrarModalEdificio()
    mostrarNotificacion(esEdicion ? 'Edificio actualizado correctamente.' : 'Edificio creado correctamente.')
  } catch { mostrarNotificacion('Ocurrió un error al guardar el edificio.', 'error') }
  finally { guardando.value = false }
}
const solicitarEliminarEdificio = (e) => { itemAEliminar.value = e; tipoEliminar.value = 'edificio'; showModalEliminar.value = true }

// CRUD Aulas
const resetFormAula = () => {
  formAula.id_aula = null; formAula.id_edificio = edificioSeleccionado.value?.id_edificio ?? ''; formAula.nombre = ''; formAula.capacidad = ''
  Object.keys(erroresAula).forEach(k => delete erroresAula[k])
}
const abrirModalNuevaAula = () => { resetFormAula(); showModalAula.value = true }
const abrirModalEditarAula = (a) => { resetFormAula(); formAula.id_aula = a.id_aula; formAula.id_edificio = a.id_edificio; formAula.nombre = a.nombre; formAula.capacidad = a.capacidad; showModalAula.value = true }
const cerrarModalAula = () => { showModalAula.value = false; resetFormAula() }
const validarAula = () => {
  Object.keys(erroresAula).forEach(k => delete erroresAula[k])
  if (!formAula.id_edificio) erroresAula.id_edificio = 'Selecciona un edificio'
  if (!formAula.nombre.trim()) erroresAula.nombre = 'El nombre del aula es obligatorio'
  if (!formAula.capacidad || formAula.capacidad < 1) erroresAula.capacidad = 'La capacidad debe ser mayor a 0'
  return Object.keys(erroresAula).length === 0
}
const guardarAula = async () => {
  if (!validarAula()) return
  guardando.value = true
  const esEdicion = !!formAula.id_aula
  const url = esEdicion ? `${API}/aulas/${formAula.id_aula}` : `${API}/aulas`
  try {
    const res = await fetch(url, { method: esEdicion ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ id_edificio: formAula.id_edificio, nombre: formAula.nombre.trim(), capacidad: formAula.capacidad }) })
    if (!res.ok) throw new Error()
    await cargarAulas(); cerrarModalAula()
    mostrarNotificacion(esEdicion ? 'Aula actualizada correctamente.' : 'Aula creada correctamente.')
  } catch { mostrarNotificacion('Ocurrió un error al guardar el aula.', 'error') }
  finally { guardando.value = false }
}
const solicitarEliminarAula = (a) => { itemAEliminar.value = a; tipoEliminar.value = 'aula'; showModalEliminar.value = true }

const confirmarEliminar = async () => {
  if (!itemAEliminar.value) return
  guardando.value = true
  const esEdificio = tipoEliminar.value === 'edificio'
  const url = esEdificio ? `${API}/edificios/${itemAEliminar.value.id_edificio}` : `${API}/aulas/${itemAEliminar.value.id_aula}`
  try {
    const res = await fetch(url, { method: 'DELETE', headers: { 'Accept': 'application/json' } })
    if (!res.ok) throw new Error()
    if (esEdificio) {
      await cargarEdificios(); await cargarAulas()
      if (edificioSeleccionado.value?.id_edificio === itemAEliminar.value.id_edificio) edificioSeleccionado.value = null
      mostrarNotificacion('Edificio eliminado correctamente.')
    } else {
      await cargarAulas()
      mostrarNotificacion('Aula eliminada correctamente.')
    }
    showModalEliminar.value = false; itemAEliminar.value = null; tipoEliminar.value = ''
  } catch { mostrarNotificacion('Ocurrió un error al eliminar.', 'error') }
  finally { guardando.value = false }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.ea-page{--azul:#1B396A;--azul-hover:#1D4ED8;--azul-suave:#DBEAFE;--borde:#E5E7EB;--fondo:#F5F5F5;--texto:#1A1A1A;--gris:#6B7280;--verde:#16A34A;--rojo:#DC2626;width:100%;background:var(--fondo);font-family:'Montserrat',sans-serif;padding-bottom:2rem}
.breadcrumb{display:flex;align-items:center;gap:6px;color:var(--gris);font-size:0.88rem;margin-bottom:0.75rem}.breadcrumb-link{color:var(--azul);font-weight:500;cursor:pointer;transition:color 0.15s}.breadcrumb-link:hover{color:var(--azul-hover);text-decoration:underline}.breadcrumb-sep{color:#9CA3AF}.breadcrumb-actual{color:var(--gris);font-weight:600}
.page-header{margin-bottom:1.4rem}.page-title{color:var(--texto);font-size:1.75rem;font-weight:700;letter-spacing:-0.02em;margin:0 0 4px}.page-subtitle{color:var(--gris);font-size:0.93rem;margin:0}
.barra-carga{height:3px;background:transparent;border-radius:2px;margin-bottom:1rem;overflow:hidden;opacity:0;transition:opacity 0.3s}.barra-carga.visible{opacity:1}.barra-progreso{height:100%;width:40%;background:var(--azul);border-radius:2px;animation:deslizar 1.2s ease-in-out infinite}
@keyframes deslizar{0%{transform:translateX(-100%)}100%{transform:translateX(350%)}}
.toast{position:fixed;bottom:2rem;right:2rem;display:flex;align-items:center;gap:10px;padding:12px 20px;border-radius:10px;color:white;font-weight:500;font-size:0.93rem;box-shadow:0 6px 20px rgba(0,0,0,0.18);z-index:3000;max-width:380px}.toast.exito{background:var(--azul)}.toast.error{background:var(--rojo)}.toast-icono{width:20px;height:20px;flex-shrink:0}
.toast-slide-enter-active,.toast-slide-leave-active{transition:all 0.35s ease}.toast-slide-enter-from,.toast-slide-leave-to{opacity:0;transform:translateX(110%)}

.seccion-card{background:#FFFFFF;border-radius:12px;border:1px solid var(--borde);box-shadow:0 4px 12px rgba(0,0,0,0.05);overflow:hidden}
.seccion-header{display:flex;align-items:center;justify-content:space-between;padding:1.2rem 1.6rem;border-bottom:1px solid var(--borde);background:#FAFAFA;flex-wrap:wrap;gap:1rem}
.seccion-titulo-grupo{display:flex;align-items:center;gap:0.85rem}
.seccion-acciones{display:flex;align-items:center;gap:0.6rem}
.seccion-icono-wrapper{width:40px;height:40px;border-radius:9px;background:var(--azul-suave);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.seccion-icono-wrapper svg{width:20px;height:20px;stroke:var(--azul)}
.seccion-icono-morado{background:#EDE9FE}.seccion-icono-morado svg{stroke:#7C3AED}
.seccion-titulo{font-size:1rem;font-weight:700;color:var(--texto);margin:0 0 2px}.seccion-subtitulo{font-size:0.82rem;color:var(--gris);margin:0}

/* Botón filtros */
.btn-filtro{display:flex;align-items:center;gap:7px;padding:8px 14px;border-radius:8px;border:1.5px solid var(--borde);background:#FFFFFF;color:var(--texto);font-weight:600;font-size:0.86rem;font-family:'Montserrat',sans-serif;cursor:pointer;transition:all 0.2s;position:relative}
.btn-filtro svg{width:15px;height:15px;stroke:var(--gris)}
.btn-filtro:hover,.btn-filtro.activo{border-color:var(--azul);color:var(--azul);background:var(--azul-suave)}
.btn-filtro.activo svg{stroke:var(--azul)}
.filtro-badge{display:inline-flex;align-items:center;justify-content:center;width:17px;height:17px;border-radius:50%;background:var(--azul);color:white;font-size:0.68rem;font-weight:700}
.btn-nuevo{background:var(--azul);color:white;border:none;padding:8px 16px;border-radius:8px;font-weight:600;cursor:pointer;white-space:nowrap;font-family:'Montserrat',sans-serif;font-size:0.86rem;transition:background 0.2s}.btn-nuevo:hover{background:var(--azul-hover)}

/* Panel de filtros desplegable */
.panel-filtros{border-bottom:1px solid var(--borde);background:#FAFAFA}
.panel-filtros-inner{display:flex;align-items:flex-end;gap:1rem;padding:1rem 1.6rem;flex-wrap:wrap}
.filtro-grupo{display:flex;flex-direction:column;gap:5px;flex:0 0 250px;min-width:160px}
.filtro-grupo-sm{flex:0 0 180px;min-width:140px}
.filtro-label{font-size:0.8rem;font-weight:600;color:var(--gris)}
.search-group{position:relative;width:100%}.search-input{width:100%;padding:8px 12px 8px 36px;border:1.5px solid var(--borde);border-radius:7px;font-size:0.88rem;background:#FFFFFF;color:var(--texto);font-family:'Montserrat',sans-serif;outline:none;transition:border-color 0.2s,box-shadow 0.2s;box-sizing:border-box}.search-input:focus{border-color:var(--azul);box-shadow:0 0 0 3px #DBEAFE}.search-input::placeholder{color:#9CA3AF}.search-icon-svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;stroke:var(--gris);pointer-events:none}
.filter-select{width:100%;padding:8px 10px;border:1.5px solid var(--borde);border-radius:7px;font-size:0.88rem;background:#FFFFFF;color:var(--texto);font-family:'Montserrat',sans-serif;cursor:pointer;outline:none}.filter-select:focus{border-color:var(--azul)}.filter-select:disabled{opacity:0.5;cursor:not-allowed;background:var(--fondo)}
.btn-limpiar-filtros{display:flex;align-items:center;gap:5px;padding:8px 12px;border-radius:7px;border:1px solid var(--borde);background:#FFFFFF;color:var(--gris);font-size:0.85rem;font-weight:600;font-family:'Montserrat',sans-serif;cursor:pointer;white-space:nowrap;transition:all 0.15s;align-self:flex-end}
.btn-limpiar-filtros svg{width:13px;height:13px}.btn-limpiar-filtros:hover{background:#FEF2F2;color:var(--rojo);border-color:#FECACA}

/* Transición panel */
.panel-slide-enter-active,.panel-slide-leave-active{transition:all 0.22s ease}
.panel-slide-enter-from,.panel-slide-leave-to{opacity:0;transform:translateY(-6px)}

/* Tabla */
.tabla-interna{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse}.data-table th{background:var(--fondo);padding:11px 16px;text-align:left;font-weight:600;font-size:0.85rem;color:var(--texto);border-bottom:1px solid var(--borde);font-family:'Montserrat',sans-serif;white-space:nowrap}.th-centro{text-align:center}.data-table td{padding:11px 16px;border-bottom:1px solid var(--borde);color:var(--texto);font-size:0.91rem;font-family:'Montserrat',sans-serif}.td-centro{text-align:center}.data-table tbody tr:last-child td{border-bottom:none}.data-table tbody tr{transition:background 0.15s}
.fila-clickable{cursor:pointer}.fila-clickable:hover{background:#F8FAFC}
.fila-seleccionada{background:var(--azul-suave)!important}.fila-seleccionada .celda-nombre{color:var(--azul);font-weight:700}
.fila-seleccionada-aula{background:#F0FDF4!important}.celda-nombre{font-weight:600}
.nombre-con-icono{display:flex;align-items:center;gap:8px}.punto-edificio{width:8px;height:8px;border-radius:50%;background:var(--azul);flex-shrink:0}
.badge-cantidad{display:inline-flex;align-items:center;justify-content:center;background:var(--azul-suave);color:var(--azul);font-size:0.82rem;font-weight:700;padding:3px 12px;border-radius:20px;min-width:32px}
.badge-edificio{display:inline-block;background:#F3F4F6;color:var(--gris);font-size:0.82rem;font-weight:600;padding:3px 10px;border-radius:6px;border:1px solid var(--borde)}
.badge-capacidad{display:inline-block;background:#DCFCE7;color:var(--verde);font-size:0.82rem;font-weight:600;padding:3px 10px;border-radius:20px}

/* Acciones — solo íconos */
.acciones-fila{display:flex;gap:6px;justify-content:center;align-items:center}
.btn-icono{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:7px;cursor:pointer;transition:all 0.15s;position:relative}
.btn-icono svg{width:14px;height:14px}
.btn-icono.editar{background:#1B396A;color:#FFFFFF;border:1px solid #1B396A}.btn-icono.editar:hover{background:#1D4ED8;border-color:#1D4ED8}
.btn-icono.eliminar-btn{background:#FEF2F2;color:var(--rojo);border:1px solid #FECACA}.btn-icono.eliminar-btn:hover{background:#FEE2E2}
.btn-icono[title]:hover::after{content:attr(title);position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);background:#1A1A1A;color:white;font-size:0.7rem;font-weight:600;padding:3px 8px;border-radius:5px;white-space:nowrap;pointer-events:none;font-family:'Montserrat',sans-serif;z-index:10}
.btn-icono[title]:hover::before{content:'';position:absolute;bottom:calc(100% + 2px);left:50%;transform:translateX(-50%);border:4px solid transparent;border-top-color:#1A1A1A;pointer-events:none;z-index:10}

.estado-vacio,.estado-cargando{text-align:center;padding:2.5rem 2rem;color:var(--gris)}.icono-vacio{width:48px;height:48px;stroke:#9CA3AF;margin-bottom:10px}.estado-vacio p{font-size:0.9rem;margin:0}.spinner-tabla{display:inline-block;width:32px;height:32px;border:3px solid #E5E7EB;border-top-color:var(--azul);border-radius:50%;animation:girar 0.8s linear infinite;margin-bottom:10px}

/* Paginación */
.paginacion{display:flex;align-items:center;justify-content:center;gap:12px;padding:12px 16px;border-top:1px solid var(--borde);background:#FAFAFA}
.btn-pag{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:7px;border:1.5px solid var(--borde);background:#FFFFFF;color:var(--texto);cursor:pointer;transition:all 0.15s}
.btn-pag svg{width:15px;height:15px;stroke:currentColor}
.btn-pag:hover:not(:disabled){border-color:var(--azul);color:var(--azul);background:var(--azul-suave)}
.btn-pag:disabled{opacity:0.35;cursor:not-allowed}
.pag-info{font-size:0.83rem;font-weight:600;color:var(--gris);font-family:'Montserrat',sans-serif}

.indicador-seleccion{display:flex;align-items:center;gap:8px;padding:10px 16px;background:var(--azul-suave);border-top:1px solid #BFDBFE;font-size:0.88rem;color:var(--azul);font-weight:500}
.indicador-seleccion svg{width:16px;height:16px;stroke:var(--azul);flex-shrink:0}
.btn-ver-todas{margin-left:auto;background:var(--azul);color:white;border:none;padding:4px 12px;border-radius:6px;font-size:0.8rem;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;transition:background 0.15s}.btn-ver-todas:hover{background:var(--azul-hover)}

.divisor{padding:1.2rem 0;display:flex;align-items:center}.divisor-linea{width:100%;height:1px;background:var(--borde)}

/* Modales */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.55);display:flex;align-items:center;justify-content:center;z-index:2000}
.modal-content{background:#FFFFFF;width:480px;max-width:92%;border-radius:14px;box-shadow:0 20px 50px rgba(0,0,0,0.18);overflow:hidden;border:1px solid var(--borde)}
.modal-confirmar{width:440px}
.modal-header{background:#1B396A;color:white;padding:1.1rem 1.6rem;display:flex;justify-content:space-between;align-items:center}
.modal-header h3{margin:0;font-size:1.1rem;font-weight:700;font-family:'Montserrat',sans-serif}
.btn-cerrar-modal{background:none;border:none;color:white;font-size:1.7rem;cursor:pointer;line-height:1;opacity:0.85}.btn-cerrar-modal:hover{opacity:1}
.modal-body{padding:1.6rem}.form-grupo{margin-bottom:1.2rem}.form-grupo label{display:block;margin-bottom:6px;font-weight:600;font-size:0.9rem;color:var(--texto);font-family:'Montserrat',sans-serif}.obligatorio{color:var(--rojo)}
.modal-input,.modal-select{width:100%;padding:10px 14px;border:1.5px solid var(--borde);border-radius:8px;font-size:0.95rem;background:#FFFFFF;color:var(--texto);font-family:'Montserrat',sans-serif;outline:none;transition:border-color 0.2s,box-shadow 0.2s;box-sizing:border-box}.modal-input:focus,.modal-select:focus{border-color:var(--azul);box-shadow:0 0 0 3px #DBEAFE}
.borde-error{border-color:var(--rojo)!important}.mensaje-error{display:block;color:var(--rojo);font-size:0.82rem;margin-top:5px;font-family:'Montserrat',sans-serif}
.campo-ayuda{display:block;color:var(--gris);font-size:0.8rem;margin-top:4px;font-family:'Montserrat',sans-serif}
.modal-footer{padding:1rem 1.6rem;background:var(--fondo);display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--borde)}
.btn-secundario{padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#FFFFFF;color:var(--texto);border:1px solid var(--borde);transition:background 0.15s}.btn-secundario:hover{background:var(--fondo)}.btn-secundario:disabled{opacity:0.5;cursor:not-allowed}
.btn-eliminar{display:flex;align-items:center;gap:8px;padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#DC2626;color:white;border:none;transition:background 0.15s}.btn-eliminar:hover{background:#B91C1C}.btn-eliminar:disabled{opacity:0.5;cursor:not-allowed}
.btn-guardar{display:flex;align-items:center;gap:8px;padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-family:'Montserrat',sans-serif;background:#1B396A;color:#FFFFFF;border:none;transition:background 0.15s}.btn-guardar:hover:not(:disabled){background:#1D4ED8}.btn-guardar:disabled{background:#E5E7EB;color:#9CA3AF;cursor:not-allowed}
.spinner-btn{display:inline-block;width:15px;height:15px;border:2px solid rgba(255,255,255,0.4);border-top-color:white;border-radius:50%;animation:girar 0.7s linear infinite;flex-shrink:0}
.confirmar-body{display:flex;flex-direction:column;align-items:center;gap:1rem;text-align:center;padding:2rem 1.6rem}.confirmar-icono{width:52px;height:52px;stroke:#F59E0B}.confirmar-body p{color:var(--texto);font-size:0.95rem;margin:0;line-height:1.5}

.pie-pagina{text-align:center;color:#9CA3AF;font-size:0.82rem;padding-top:2rem;border-top:1px solid var(--borde);margin-top:1.2rem;font-family:'Montserrat',sans-serif}

@media(max-width:700px){.seccion-header{flex-direction:column;align-items:flex-start}.panel-filtros-inner{flex-direction:column}.filtro-grupo{flex:1 1 100%;min-width:100%}.acciones-fila{flex-direction:column;gap:4px}}
@keyframes girar{to{transform:rotate(360deg)}}
</style>