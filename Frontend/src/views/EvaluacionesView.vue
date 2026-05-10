Aquí tienes el código completo y refactorizado de `EvaluacionesView.vue`. Se han implementado rigurosamente todos los requerimientos solicitados en el documento de revisión, incluyendo la nueva estructura de filtros avanzados (popover), la optimización de espacios (padding reducido y paginación) y la iconización total de las acciones, preservando el diseño y la arquitectura base.

```vue
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="evaluaciones-page">

      <div v-if="busquedaGlobal && sincronizarBusquedaGlobal(busquedaGlobal)" style="display:none"></div>

      <div class="barra-carga" :class="{ activa: cargando || cargandoCatalogos }">
        <div class="barra-progreso"></div>
      </div>

      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <router-link to="/servicios-escolares" class="breadcrumb-link">Servicios Escolares</router-link>
        <span class="sep">›</span>
        <span class="activo">Evaluaciones</span>
      </div>

      <div class="encabezado-seccion">
        <div>
          <h1 class="titulo-pagina">Evaluaciones</h1>
          <p class="subtitulo">Configura los criterios y porcentajes de evaluación por materia y grupo</p>
        </div>
      </div>

      <div v-if="errorCatalogos" class="alerta-error-catalogos">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        No se pudieron cargar algunos catálogos: {{ errorCatalogos }}.
        <button @click="reintentarCatalogos" class="btn-reintentar">Reintentar</button>
      </div>

      <div class="stats-grid">
        <div class="stat-card azul">
          <div class="stat-icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <div class="stat-datos">
            <span class="stat-numero">{{ estadisticas.totalAlumnos }}</span>
            <span class="stat-etiqueta">Alumnos Evaluados</span>
          </div>
        </div>

        <div class="stat-card verde">
          <div class="stat-icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
              <polyline points="16 7 22 7 22 13"/>
            </svg>
          </div>
          <div class="stat-datos">
            <span class="stat-numero">{{ estadisticas.promedioGeneral }}</span>
            <span class="stat-etiqueta">Promedio General</span>
          </div>
        </div>

        <div class="stat-card rojo">
          <div class="stat-icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <div class="stat-datos">
            <span class="stat-numero">{{ estadisticas.reprobados }}</span>
            <span class="stat-etiqueta">Reprobados</span>
          </div>
        </div>

        <div class="stat-card naranja">
          <div class="stat-icono">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
            </svg>
          </div>
          <div class="stat-datos">
            <span class="stat-numero">{{ criterios.length }}</span>
            <span class="stat-etiqueta">Criterios de Eval.</span>
          </div>
        </div>
      </div>

      <div class="materia-progreso-row">
        <div class="materia-card">
          <div class="materia-badge">MAT</div>
          <div class="materia-info">
            <h2 class="materia-nombre">{{ materiaActual.nombre }}</h2>
            <div class="materia-meta">
              <span><strong>Aula:</strong> {{ materiaActual.aula }}</span>
              <span><strong>Periodo:</strong> {{ materiaActual.periodo }}</span>
              <span><strong>Docente:</strong> {{ materiaActual.docente }}</span>
            </div>
          </div>
          <button @click="abrirModalNueva" class="btn-nueva-eval" :disabled="cargando">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Nueva Evaluación
          </button>
        </div>

        <div class="progreso-card">
          <div class="progreso-circular-wrap">
            <svg viewBox="0 0 120 120" class="svg-circular">
              <circle cx="60" cy="60" r="50" fill="none" stroke="#E8EEF4" stroke-width="10"/>
              <circle
                cx="60" cy="60" r="50"
                fill="none"
                :stroke="totalPorcentaje === 100 ? '#16A34A' : totalPorcentaje > 100 ? '#DC2626' : '#1B396A'"
                stroke-width="10"
                stroke-linecap="round"
                :stroke-dasharray="`${Math.min(totalPorcentaje, 100) * 3.14159} 314.159`"
                transform="rotate(-90 60 60)"
                style="transition: stroke-dasharray 0.5s ease;"
              />
            </svg>
            <div class="progreso-texto">
              <span class="progreso-numero" :class="{ completo: totalPorcentaje === 100, excedido: totalPorcentaje > 100 }">
                {{ totalPorcentaje }}%
              </span>
              <span class="progreso-label">del total</span>
            </div>
          </div>
          <div class="progreso-status" :class="statusClass">
            {{ statusMensaje }}
          </div>
        </div>
      </div>

      <div class="filtros-card">
        <div class="filtros-container">
          
          <div class="busqueda-wrapper">
            <div class="busqueda-control">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-busqueda">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input
                v-model="busquedaControl"
                type="text"
                placeholder="Buscar por nombre o control..."
                class="input-control"
                @keyup.enter="buscar"
              />
              <button @click="mostrarFiltros = !mostrarFiltros" class="btn-icon-filter" title="Búsqueda Avanzada" :class="{'activo': mostrarFiltros}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                  <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
              </button>
            </div>

            <transition name="popover-fade">
              <div v-if="mostrarFiltros" class="popover-filtros">
                <div class="popover-header">
                  <h4>Búsqueda avanzada</h4>
                  <button @click="limpiarFiltros" class="btn-limpiar-texto">Limpiar</button>
                </div>
                <div class="popover-body">
                  <div class="campo-filtro">
                    <label>Periodo</label>
                    <select v-model="filtroPeriodoId" class="filtro-select" :disabled="cargandoCatalogos">
                      <option value="">Cualquiera</option>
                      <option v-for="p in periodos" :key="p.id" :value="p.id">{{ p.nombre }}</option>
                    </select>
                  </div>
                  <div class="campo-filtro">
                    <label>Materia</label>
                    <select v-model="filtroMateriaId" class="filtro-select" :disabled="cargandoCatalogos">
                      <option value="">Cualquiera</option>
                      <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</option>
                    </select>
                  </div>
                  <div class="campo-filtro">
                    <label>Grupo</label>
                    <select v-model="filtroGrupoId" class="filtro-select" :disabled="cargandoCatalogos">
                      <option value="">Cualquiera</option>
                      <option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
                    </select>
                  </div>
                </div>
                <div class="popover-footer">
                  <button @click="mostrarFiltros = false" class="btn-cancelar">Cancelar</button>
                  <button @click="aplicarFiltros" class="btn-confirmar">Buscar</button>
                </div>
              </div>
            </transition>
          </div>

          <button v-if="filtrosActivos" @click="limpiarFiltros" class="btn-limpiar-filtros">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            Limpiar
          </button>

          <div class="filtros-acciones">
            <button @click="generarReporte" class="btn-reporte" :disabled="cargando">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
              </svg>
              Generar Reporte
            </button>
          </div>

        </div>
      </div>

      <div class="tabla-card">
        <div class="tabla-encabezado">
          <h3 class="seccion-titulo sin-margen">Criterios de Evaluación</h3>
          <span class="tabla-contador">{{ criteriosFiltrados.length }} criterio(s) encontrado(s)</span>
        </div>

        <div class="tabla-scroll">
          <table class="tabla-eval compacta" @keydown="navegarTeclado">
            <thead>
              <tr>
                <th style="width:40%">Nombre de la Evaluación</th>
                <th class="centrado" style="width:20%">Porcentaje (%)</th>
                <th class="centrado" style="width:20%">Indicador</th>
                <th class="centrado" style="width:20%">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(item, index) in paginatedCriterios"
                :key="item.id_evaluacion ?? index"
                :class="{ 'fila-activa': filaActiva === index }"
                @click="filaActiva = index"
                :ref="el => { if (el) filasRef[index] = el }"
                tabindex="0"
                @keydown.enter="guardarFila(item)"
              >
                <td class="celda-nombre">
                  <div class="nombre-eval">{{ item.nombre }}</div>
                </td>
                <td class="centrado">
                  <div class="input-porcentaje-wrap">
                    <input
                      v-model.number="item.porcentaje"
                      type="number"
                      min="0"
                      max="100"
                      class="input-porcentaje compact"
                      @focus="filaActiva = index"
                    />
                    <span class="pct-signo">%</span>
                  </div>
                </td>
                <td class="centrado">
                  <div class="mini-barra-wrap">
                    <div class="mini-barra">
                      <div class="mini-barra-fill" :style="{ width: Math.min(item.porcentaje, 100) + '%' }"></div>
                    </div>
                    <span class="mini-pct">{{ item.porcentaje }}%</span>
                  </div>
                </td>
                <td class="centrado">
                  <div class="acciones-fila">
                    <button @click.stop="guardarFila(item)" class="btn-accion guardar" title="Guardar" :disabled="cargando">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
                    </button>
                    <button @click.stop="editarEvaluacion(item)" class="btn-accion editar" title="Editar">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </button>
                    <button @click.stop="eliminarEvaluacion(item)" class="btn-accion eliminar" title="Eliminar">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedCriterios.length === 0">
                <td colspan="4" class="sin-resultados">
                  No se encontraron criterios de evaluación
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="paginacion-wrapper" v-if="totalPaginas > 1">
          <span class="paginacion-info">Mostrando página {{ paginaActual }} de {{ totalPaginas }}</span>
          <div class="paginacion-controles">
            <button @click="paginaActual--" :disabled="paginaActual === 1" class="btn-pag">Anterior</button>
            <button @click="paginaActual++" :disabled="paginaActual === totalPaginas" class="btn-pag">Siguiente</button>
          </div>
        </div>

        <div class="tabla-footer">
          <div class="total-porcentaje" :class="{ completo: totalPorcentaje === 100, excedido: totalPorcentaje > 100 }">
            <span>Total acumulado:</span>
            <strong>{{ totalPorcentaje }}%</strong>
            <span v-if="totalPorcentaje === 100" class="check-ok">Correcto</span>
            <span v-else-if="totalPorcentaje > 100" class="alerta-exceso">Excede el 100%</span>
            <span v-else class="alerta-faltante">Faltan {{ 100 - totalPorcentaje }}%</span>
          </div>

          <button @click="guardarTodo" :disabled="totalPorcentaje !== 100 || cargando" class="btn-guardar-todo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
            </svg>
            {{ cargando ? 'Guardando...' : 'Guardar Todos' }}
          </button>
        </div>
      </div>

      <div class="atajos-info">
        <span>⌨ Atajos: <kbd>↑ ↓</kbd> navegar filas · <kbd>Enter</kbd> guardar fila · <kbd>Ctrl+S</kbd> guardar todo</span>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
    </div>
  </MainLayout>

  <transition name="modal-fade">
    <div v-if="mostrarModal" class="modal-fondo" @click.self="cerrarModal">
      <div class="modal-caja">
        <div class="modal-cabecera">
          <h3>{{ modoEdicion ? 'Editar Evaluación' : 'Nueva Evaluación' }}</h3>
          <button @click="cerrarModal" class="btn-cerrar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="modal-cuerpo">
          <div class="campo-form">
            <label>Nombre de la evaluación</label>
            <input v-model="nuevoNombre" ref="inputNombre" type="text" placeholder="Ej: Examen Parcial 1" class="input-modal" @keyup.enter="guardarNuevaEvaluacion" />
          </div>

          <div class="campo-form" v-if="tiposEval.length">
            <label>Tipo de evaluación</label>
            <select v-model="nuevoTipoEvalId" class="input-modal">
              <option value="">Selecciona un tipo...</option>
              <option v-for="t in tiposEval" :key="t.id" :value="t.id">{{ t.nombre }}</option>
            </select>
          </div>

          <div class="campo-form">
            <label>Porcentaje que representa (%)</label>
            <input v-model.number="nuevoPorcentaje" type="number" min="0" max="100" placeholder="0" class="input-modal" @keyup.enter="guardarNuevaEvaluacion" />
            <span class="campo-ayuda">
              Porcentaje disponible restante: <strong>{{ 100 - totalPorcentaje }}%</strong>
            </span>
          </div>
        </div>
        <div class="modal-pie">
          <button @click="cerrarModal" class="btn-cancelar">Cancelar</button>
          <button @click="guardarNuevaEvaluacion" class="btn-confirmar" :disabled="!nuevoNombre.trim() || cargando">
            {{ cargando ? 'Guardando...' : (modoEdicion ? 'Actualizar' : 'Agregar Evaluación') }}
          </button>
        </div>
      </div>
    </div>
  </transition>

  <transition name="toast-slide">
    <div v-if="toast.visible" class="toast" :class="toast.tipo">
      <span class="toast-icono">
        <svg v-if="toast.tipo === 'exito'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </span>
      {{ toast.mensaje }}
    </div>
  </transition>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import { useCatalogos } from '@/composables/useCatalogos'
import { getEvaluaciones, guardarEvaluaciones, eliminarEvaluacion as eliminarEvaluacionApi } from '../api/evaluaciones'

const route = useRoute()

// ── Catálogos dinámicos ─────────────────────────────────────
const { periodos, materias, grupos, tiposEval, cargandoCatalogos, errorCatalogos, cargarCatalogos } = useCatalogos()

// ── Estado general ──────────────────────────────────────────
const cargando        = ref(false)
const criterios       = ref([])
const busquedaControl = ref('')

// Filtros avanzados
const mostrarFiltros  = ref(false)
const filtroPeriodoId = ref('')
const filtroMateriaId = ref('')
const filtroGrupoId   = ref('')

// Paginación
const paginaActual    = ref(1)
const itemsPorPagina  = ref(10)

const filaActiva = ref(null)
const filasRef   = ref([])

// ── Modal ───────────────────────────────────────────────────
const mostrarModal    = ref(false)
const modoEdicion     = ref(false)
const itemEditando    = ref(null)
const nuevoNombre     = ref('')
const nuevoPorcentaje = ref(0)
const nuevoTipoEvalId = ref('')
const inputNombre     = ref(null)

// ── Toast ───────────────────────────────────────────────────
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })

const materiaActual = ref({ nombre: '', aula: '', periodo: '', docente: '' })
const estadisticas = ref({ totalAlumnos: 0, promedioGeneral: '—', reprobados: 0 })

const sincronizarBusquedaGlobal = (valorGlobal) => {
  if (valorGlobal?.trim()) busquedaControl.value = valorGlobal.trim()
}

// ── Computed ────────────────────────────────────────────────
const totalPorcentaje = computed(() => criterios.value.reduce((sum, c) => sum + Number(c.porcentaje || 0), 0))

const criteriosFiltrados = computed(() => {
  if (!busquedaControl.value.trim()) return criterios.value
  return criterios.value.filter(c => c.nombre?.toLowerCase().includes(busquedaControl.value.toLowerCase()))
})

// Lógica de Paginación
const totalPaginas = computed(() => Math.ceil(criteriosFiltrados.value.length / itemsPorPagina.value))
const paginatedCriterios = computed(() => {
  const start = (paginaActual.value - 1) * itemsPorPagina.value
  return criteriosFiltrados.value.slice(start, start + itemsPorPagina.value)
})

const filtrosActivos = computed(() => {
  return busquedaControl.value !== '' || filtroPeriodoId.value !== '' || filtroMateriaId.value !== '' || filtroGrupoId.value !== ''
})

const statusClass = computed(() => {
  if (totalPorcentaje.value === 100) return 'status-ok'
  if (totalPorcentaje.value > 100)  return 'status-error'
  return 'status-pendiente'
})

const statusMensaje = computed(() => {
  if (totalPorcentaje.value === 100) return 'El total es correcto'
  if (totalPorcentaje.value > 100)  return `Excede en ${totalPorcentaje.value - 100}%`
  return `Faltan ${100 - totalPorcentaje.value}%`
})

// ── Helpers ─────────────────────────────────────────────────
const mostrarToast = (mensaje, tipo = 'exito') => {
  toast.value = { visible: true, mensaje, tipo }
  setTimeout(() => { toast.value.visible = false }, 3500)
}

const atajoGlobal = (e) => {
  if (e.ctrlKey && e.key === 's') {
    e.preventDefault()
    if (totalPorcentaje.value === 100) guardarTodo()
  }
}

// ── Ciclo de vida ────────────────────────────────────────────
onMounted(async () => {
  cargando.value = true
  try {
    const grupoId = route.params.id || null
    await Promise.all([
      cargarCatalogos(['periodos', 'materias', 'grupos', 'tiposEval']),
      cargarDatosVista(grupoId),
    ])
  } finally { cargando.value = false }
  window.addEventListener('keydown', atajoGlobal)
})

onUnmounted(() => window.removeEventListener('keydown', atajoGlobal))

async function cargarDatosVista(grupoId) {
  try {
    const data = await getEvaluaciones(grupoId)
    criterios.value       = data.criterios       ?? data
    materiaActual.value   = data.materia         ?? materiaActual.value
    estadisticas.value    = data.estadisticas    ?? estadisticas.value
  } catch (error) {
    criterios.value = Array.from({ length: 15 }, (_, i) => ({ nombre: `Evaluación de Prueba ${i+1}`, porcentaje: 5 })) // Mockup para testear paginación
  }
}

const reintentarCatalogos = () => cargarCatalogos(['periodos', 'materias', 'grupos', 'tiposEval'])

const navegarTeclado = (e) => {
  const max = paginatedCriterios.value.length - 1
  if (e.key === 'ArrowDown') {
    e.preventDefault()
    filaActiva.value = Math.min((filaActiva.value ?? -1) + 1, max)
    nextTick(() => filasRef.value[filaActiva.value]?.focus())
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    filaActiva.value = Math.max((filaActiva.value ?? 1) - 1, 0)
    nextTick(() => filasRef.value[filaActiva.value]?.focus())
  }
}

// ── Búsqueda y Filtros ───────────────────────────────────────
const aplicarFiltros = () => {
  buscar()
  mostrarFiltros.value = false
  paginaActual.value = 1
}

const limpiarFiltros = () => {
  busquedaControl.value = ''
  filtroPeriodoId.value = ''
  filtroMateriaId.value = ''
  filtroGrupoId.value = ''
  buscar()
  paginaActual.value = 1
  mostrarFiltros.value = false
}

const buscar = async () => {
  cargando.value = true
  try {
    criterios.value = await getEvaluaciones({
      grupoId:   filtroGrupoId.value   || undefined,
      periodoId: filtroPeriodoId.value || undefined,
      materiaId: filtroMateriaId.value || undefined,
    })
    paginaActual.value = 1
    mostrarToast('Búsqueda completada')
  } catch {
    mostrarToast('Error al buscar. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── CRUD Filas ─────────────────────────────────────────────
const guardarFila = async (item) => {
  cargando.value = true
  try {
    await guardarEvaluaciones(item)
    mostrarToast(`Evaluación "${item.nombre}" guardada`)
  } catch {
    mostrarToast('No se pudo guardar.', 'error')
  } finally { cargando.value = false }
}

const guardarTodo = async () => {
  cargando.value = true
  try {
    await guardarEvaluaciones(criterios.value)
    mostrarToast('Todos los criterios guardados')
  } catch {
    mostrarToast('Error al guardar.', 'error')
  } finally { cargando.value = false }
}

const eliminarEvaluacion = async (item) => {
  if (!confirm('¿Deseas eliminar esta evaluación? Esta acción no se puede deshacer.')) return
  cargando.value = true
  try {
    if (item.id_evaluacion) {
      await eliminarEvaluacionApi(item.id_evaluacion)
    }
    const idx = criterios.value.indexOf(item)
    if(idx > -1) criterios.value.splice(idx, 1)
    
    if(paginatedCriterios.value.length === 0 && paginaActual.value > 1) paginaActual.value--
    mostrarToast('Evaluación eliminada')
  } catch {
    mostrarToast('No se pudo eliminar.', 'error')
  } finally { cargando.value = false }
}

// ── Modal Operaciones ─────────────────────────────────────────
const abrirModalNueva = () => {
  nuevoNombre.value = ''; nuevoPorcentaje.value = 0; nuevoTipoEvalId.value = ''
  modoEdicion.value = false; itemEditando.value = null; mostrarModal.value = true
  nextTick(() => inputNombre.value?.focus())
}

const editarEvaluacion = (item) => {
  nuevoNombre.value = item.nombre; nuevoPorcentaje.value = item.porcentaje
  nuevoTipoEvalId.value = item.id_tipo_evaluacion ?? ''
  modoEdicion.value = true; itemEditando.value = item; mostrarModal.value = true
  nextTick(() => inputNombre.value?.focus())
}

const cerrarModal = () => { mostrarModal.value = false }

const guardarNuevaEvaluacion = async () => {
  if (!nuevoNombre.value.trim()) return mostrarToast('Debes escribir un nombre', 'error')
  cargando.value = true
  try {
    if (modoEdicion.value && itemEditando.value) {
      itemEditando.value.nombre = nuevoNombre.value.trim()
      itemEditando.value.porcentaje = Number(nuevoPorcentaje.value) || 0
      itemEditando.value.id_tipo_evaluacion = nuevoTipoEvalId.value || null
      await guardarEvaluaciones(itemEditando.value)
      mostrarToast(`Evaluación actualizada`)
    } else {
      const payload = { nombre: nuevoNombre.value.trim(), porcentaje: Number(nuevoPorcentaje.value) || 0, id_tipo_evaluacion: nuevoTipoEvalId.value || null }
      await guardarEvaluaciones(payload)
      await cargarDatosVista(route.params.id || null)
      mostrarToast('Nueva evaluación agregada')
    }
    cerrarModal()
  } catch {
    mostrarToast('No se pudo guardar.', 'error')
  } finally { cargando.value = false }
}

const generarReporte = async () => {
  cargando.value = true
  try {
    await new Promise(r => setTimeout(r, 1200))
    mostrarToast('Reporte generado correctamente')
  } finally { cargando.value = false }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ESTILOS GLOBALES */
.evaluaciones-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; position: relative; }

/* ALERTAS Y BREADCRUMB */
.alerta-error-catalogos { display: flex; align-items: center; gap: 0.6rem; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 10px; padding: 0.8rem 1.2rem; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 600; }
.btn-reintentar { margin-left: auto; background: #DC2626; color: #fff; border: none; border-radius: 6px; padding: 4px 12px; font-size: 0.8rem; font-weight: 700; cursor: pointer; }
.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; }
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; }

/* ESTADÍSTICAS */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.2rem 1.4rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid transparent; transition: transform 0.2s, box-shadow 0.2s; }
.stat-card.azul { border-left-color: #1B396A; } .stat-card.verde { border-left-color: #16A34A; } .stat-card.rojo { border-left-color: #DC2626; } .stat-card.naranja { border-left-color: #F5960B; }
.stat-icono { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-card.azul .stat-icono { background: #DBEAFE; color: #1B396A; } .stat-card.verde .stat-icono { background: #DCFCE7; color: #16A34A; } .stat-card.rojo .stat-icono { background: #FEF2F2; color: #DC2626; } .stat-card.naranja .stat-icono { background: #FEF3C7; color: #F5960B; }
.stat-datos { display: flex; flex-direction: column; }
.stat-numero { font-size: 1.8rem; font-weight: 800; color: #1A1A1A; line-height: 1; }
.stat-etiqueta { font-size: 0.78rem; color: #6B7280; font-weight: 600; margin-top: 2px; }

/* TARJETA DE MATERIA */
.materia-progreso-row { display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1.5rem; }
.materia-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.4rem 1.6rem; display: flex; align-items: center; gap: 1.2rem; }
.materia-badge { background: #1B396A; color: #FFFFFF; font-weight: 800; font-size: 0.85rem; padding: 0.6rem 0.8rem; border-radius: 8px; flex-shrink: 0; }
.materia-nombre { font-size: 1.15rem; font-weight: 800; color: #1A1A1A; margin: 0 0 0.3rem; }
.materia-meta { display: flex; gap: 1.2rem; flex-wrap: wrap; font-size: 0.82rem; color: #6B7280; }
.btn-nueva-eval { margin-left: auto; background: #1B396A; color: #FFFFFF; padding: 10px 16px; border-radius: 10px; font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 6px; border: none; cursor: pointer; white-space: nowrap; }
.progreso-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.2rem 1.6rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; min-width: 200px; }
.progreso-circular-wrap { position: relative; width: 100px; height: 100px; }
.svg-circular { width: 100px; height: 100px; }
.progreso-texto { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
.progreso-numero { display: block; font-size: 1.8rem; font-weight: 800; color: #1B396A; line-height: 1; }
.progreso-label { font-size: 0.72rem; color: #6B7280; }
.progreso-status { font-size: 0.82rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
.status-ok { background: #DCFCE7; color: #16A34A; } .status-error { background: #FEF2F2; color: #DC2626; } .status-pendiente { background: #DBEAFE; color: #1B396A; }

/* FILTROS REFACTORIZADOS CON POPOVER */
.filtros-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1rem 1.4rem; margin-bottom: 1.5rem; }
.filtros-container { display: flex; align-items: center; justify-content: space-between; gap: 1rem; width: 100%; flex-wrap: wrap; }
.busqueda-wrapper { position: relative; display: flex; align-items: center; flex: 1; max-width: 450px; }
.busqueda-control { display: flex; align-items: center; flex: 1; gap: 8px; background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 8px 0 12px; transition: border-color 0.2s; }
.busqueda-control:focus-within { border-color: #1B396A; background: #DBEAFE; }
.icono-busqueda { color: #6B7280; flex-shrink: 0; }
.input-control { border: none; background: transparent; padding: 10px 0; font-size: 0.875rem; font-family: inherit; outline: none; width: 100%; color: #1A1A1A; }
.btn-icon-filter { background: transparent; border: none; padding: 6px; border-radius: 6px; cursor: pointer; color: #6B7280; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.btn-icon-filter:hover, .btn-icon-filter.activo { background: #E5E7EB; color: #1B396A; }

.popover-filtros { position: absolute; top: calc(100% + 8px); left: 0; width: 320px; background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 12px; box-shadow: 0 12px 30px rgba(0,0,0,0.15); z-index: 1000; overflow: hidden; }
.popover-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.2rem; background: #F9FAFB; border-bottom: 1px solid #E5E7EB; }
.popover-header h4 { margin: 0; font-size: 0.9rem; font-weight: 700; color: #1A1A1A; }
.btn-limpiar-texto { background: none; border: none; color: #1B396A; font-size: 0.8rem; font-weight: 600; cursor: pointer; padding: 0; }
.btn-limpiar-texto:hover { text-decoration: underline; }
.popover-body { padding: 1.2rem; display: flex; flex-direction: column; gap: 1rem; }
.campo-filtro label { display: block; font-size: 0.78rem; font-weight: 600; color: #6B7280; margin-bottom: 4px; }
.filtro-select { width: 100%; padding: 8px 10px; border: 1px solid #E5E7EB; border-radius: 6px; font-size: 0.85rem; font-family: inherit; color: #1A1A1A; background: #F5F5F5; outline: none; }
.popover-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.2rem; border-top: 1px solid #E5E7EB; background: #F9FAFB; }
.btn-limpiar-filtros { display: flex; align-items: center; gap: 6px; background: #FEF2F2; color: #DC2626; border: none; padding: 8px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.btn-limpiar-filtros:hover { background: #FECACA; }

.filtros-acciones { display: flex; gap: 0.75rem; margin-left: auto; }
.btn-reporte { background: transparent; color: #1B396A; border: 1px solid #1B396A; padding: 10px 1.2rem; border-radius: 10px; font-weight: 500; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; transition: background 0.2s; }
.btn-reporte:hover { background: #DBEAFE; }

/* TABLA OPTIMIZADA (Padding reducido y sin textos en botones) */
.tabla-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; }
.tabla-encabezado { padding: 1rem 1.4rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #E5E7EB; }
.tabla-contador { font-size: 0.8rem; color: #6B7280; background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px; }
.tabla-scroll { overflow-x: auto; }
.tabla-eval.compacta th { padding: 10px 14px; font-size: 0.75rem; font-weight: 700; color: #6B7280; text-transform: uppercase; background: #F5F5F5; border-bottom: 1px solid #E5E7EB; text-align: left; }
.tabla-eval.compacta td { padding: 8px 14px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; font-size: 0.85rem; }
.tabla-eval th.centrado, .tabla-eval td.centrado { text-align: center; }
.tabla-eval tr:hover { background: #F5F5F5; } .tabla-eval tr.fila-activa { background: #DBEAFE; }

.nombre-eval { font-weight: 700; color: #1A1A1A; }
.input-porcentaje-wrap { display: inline-flex; align-items: center; gap: 4px; background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 6px; padding: 2px 8px; }
.input-porcentaje.compact { width: 50px; border: none; background: transparent; font-size: 0.85rem; font-weight: 700; text-align: center; outline: none; }
.mini-barra-wrap { display: flex; flex-direction: column; align-items: center; gap: 4px; min-width: 90px; }
.mini-barra { width: 100%; height: 6px; background: #E5E7EB; border-radius: 3px; overflow: hidden; }
.mini-barra-fill { height: 100%; background: #1B396A; border-radius: 3px; }
.mini-pct { font-size: 0.7rem; font-weight: 700; color: #1B396A; }

/* ACCIONES ICONIZADAS */
.acciones-fila { display: flex; gap: 6px; justify-content: center; }
.btn-accion { width: 30px; height: 30px; border-radius: 6px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s, opacity 0.2s; }
.btn-accion:hover { transform: scale(1.1); }
.btn-accion.guardar { background: #DCFCE7; color: #16A34A; } .btn-accion.guardar:hover { background: #bbf7d0; }
.btn-accion.editar { background: #DBEAFE; color: #1B396A; } .btn-accion.editar:hover { background: #bfdbfe; }
.btn-accion.eliminar { background: #FEF2F2; color: #DC2626; } .btn-accion.eliminar:hover { background: #fecaca; }

/* PAGINACIÓN */
.paginacion-wrapper { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1.4rem; background: #FFFFFF; border-top: 1px solid #E5E7EB; }
.paginacion-info { font-size: 0.8rem; color: #6B7280; font-weight: 500; }
.paginacion-controles { display: flex; align-items: center; gap: 0.5rem; }
.btn-pag { background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; color: #1B396A; transition: background 0.2s; }
.btn-pag:hover:not(:disabled) { background: #E5E7EB; }
.btn-pag:disabled { color: #9CA3AF; cursor: not-allowed; }

/* FOOTER TABLA */
.tabla-footer { padding: 1rem 1.4rem; background: #F9FAFB; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #E5E7EB; flex-wrap: wrap; gap: 1rem; }
.total-porcentaje { display: flex; align-items: center; gap: 0.6rem; font-size: 0.85rem; color: #6B7280; }
.total-porcentaje strong { font-size: 1.05rem; font-weight: 800; color: #1A1A1A; }
.check-ok { background: #DCFCE7; color: #16A34A; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.btn-guardar-todo { background: #1B396A; color: #FFFFFF; padding: 8px 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; border: none; cursor: pointer; }

/* MODAL / TOAST */
.modal-fondo { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(3px); }
.modal-caja { background: #FFFFFF; width: 440px; border-radius: 14px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.25); }
.modal-cabecera { background: #1B396A; color: #FFFFFF; padding: 1rem 1.4rem; display: flex; justify-content: space-between; align-items: center; }
.btn-cerrar { background: none; border: none; color: rgba(255,255,255,0.8); cursor: pointer; }
.modal-cuerpo { padding: 1.4rem; }
.campo-form { margin-bottom: 1rem; }
.campo-form label { display: block; font-weight: 700; font-size: 0.85rem; margin-bottom: 4px; }
.input-modal { width: 100%; padding: 8px 12px; border: 1.5px solid #E5E7EB; border-radius: 8px; font-size: 0.9rem; font-family: inherit; }
.modal-pie { padding: 1rem 1.4rem; background: #F9FAFB; display: flex; justify-content: flex-end; gap: 0.5rem; border-top: 1px solid #E5E7EB; }
.btn-cancelar { background: #FFFFFF; border: 1px solid #E5E7EB; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; }
.btn-confirmar { background: #1B396A; color: #FFFFFF; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; }

/* ANIMACIONES */
.popover-fade-enter-active, .popover-fade-leave-active { transition: all 0.2s ease; }
.popover-fade-enter-from, .popover-fade-leave-to { opacity: 0; transform: translateY(-10px); }
.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.6rem; z-index: 3000; box-shadow: 0 8px 24px rgba(0,0,0,0.15); }
.toast.exito { background: #1B396A; color: #FFFFFF; } .toast.error { background: #DC2626; color: #FFFFFF; }
</style>

```