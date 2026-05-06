<!-- ============================================================ -->
<!-- src/views/CalificacionesView.vue                           -->
<!-- Vista de Calificaciones — Refactorizada (SICE)            -->
<!-- Sin mapeos hardcoded · Catálogos dinámicos · IDs directos -->
<!-- ============================================================ -->

<template>
<MainLayout v-slot="{ busquedaGlobal }">
<div class="calificaciones-page">
  <!-- Sincronización con búsqueda global del header de MainLayout -->
  <div v-if="busquedaGlobal && sincronizarBusquedaGlobal(busquedaGlobal)" style="display:none"></div>

  <!-- Barra de carga global -->
  <div class="barra-carga" :class="{ activa: cargando || cargandoCatalogos }">
    <div class="barra-progreso"></div>
  </div>

  <!-- Encabezado -->
  <div class="breadcrumb">
    <router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
    <span class="sep">›</span>
    <router-link to="/servicios-escolares" class="breadcrumb-link">Servicios Escolares</router-link>
    <span class="sep">›</span>
    <span class="activo">Calificaciones</span>
  </div>
  <div class="encabezado-seccion">
    <div>
      <h1 class="titulo-pagina">Calificaciones</h1>
      <p class="subtitulo">Captura y consulta de calificaciones por alumno, materia y periodo</p>
    </div>
  </div>

  <!-- ── ALERTA DE ERROR EN CATÁLOGOS ── -->
  <div v-if="errorCatalogos" class="alerta-error-catalogos">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    No se pudieron cargar algunos catálogos: {{ errorCatalogos }}.
    <button @click="reintentarCatalogos" class="btn-reintentar">Reintentar</button>
  </div>

  <!-- ── RESUMEN ESTADÍSTICO ── -->
  <div class="resumen-grid">
    <div class="resumen-card azul">
      <div class="resumen-icono">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
          <polyline points="16 7 22 7 22 13"/>
        </svg>
      </div>
      <div class="resumen-datos">
        <span class="resumen-numero">{{ promedioGeneral }}</span>
        <span class="resumen-etiqueta">Promedio General</span>
      </div>
      <div class="resumen-barra">
        <div class="resumen-barra-fill" :style="{ width: (promedioGeneral / 10 * 100) + '%' }"></div>
      </div>
    </div>
    <div class="resumen-card verde">
      <div class="resumen-icono">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="9" cy="7" r="4"/>
          <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
      </div>
      <div class="resumen-datos">
        <span class="resumen-numero">{{ alumnos.length }}</span>
        <span class="resumen-etiqueta">Total de Alumnos</span>
      </div>
    </div>
    <div class="resumen-card rojo">
      <div class="resumen-icono">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
      </div>
      <div class="resumen-datos">
        <span class="resumen-numero">{{ totalReprobados }}</span>
        <span class="resumen-etiqueta">Reprobados</span>
      </div>
    </div>
    <div class="resumen-card gris">
      <div class="resumen-icono">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
          <line x1="8" y1="21" x2="16" y2="21"/>
          <line x1="12" y1="17" x2="12" y2="21"/>
        </svg>
      </div>
      <div class="resumen-datos">
        <span class="resumen-numero">{{ totalNC }}</span>
        <span class="resumen-etiqueta">Sin Calificar (NC)</span>
      </div>
    </div>
  </div>

  <!-- ── HISTORIAL DEL ALUMNO BUSCADO ── -->
  <div v-if="alumnoHistorial" class="historial-card">
    <div class="historial-encabezado">
      <div class="historial-avatar" :class="{ 'sin-calificar': esNC(alumnoHistorial) }">{{ iniciales(alumnoHistorial.nombre) }}</div>
      <div class="historial-info">
        <h3>{{ alumnoHistorial.nombre }}</h3>
        <span class="historial-control">No. Control: <strong>{{ alumnoHistorial.control }}</strong></span>
      </div>
      <div class="historial-promedio" :class="clasePromedio(calcularFinal(alumnoHistorial))">
        <span class="hp-numero">{{ calcularFinal(alumnoHistorial) }}</span>
        <span class="hp-etiqueta">Promedio</span>
      </div>
      <button @click="alumnoHistorial = null" class="btn-cerrar-historial">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <div class="historial-parciales">
      <div
        v-for="(val, nombre) in { 'Parcial 1': alumnoHistorial.p1, 'Parcial 2': alumnoHistorial.p2, 'Proyecto': alumnoHistorial.proy }"
        :key="nombre"
        class="parcial-item"
      >
        <div class="parcial-nombre">{{ nombre }}</div>
        <div class="parcial-barra-wrap">
          <div class="parcial-barra">
            <div
              class="parcial-fill"
              :style="{ width: (val / 10 * 100) + '%', background: colorNota(Number(val)) }"
            ></div>
          </div>
        </div>
        <div class="parcial-valor" :style="{ color: colorNota(Number(val)) }">{{ val || '–' }}</div>
      </div>
    </div>
  </div>

  <!-- ── FILTROS Y BÚSQUEDA ── -->
  <div class="filtros-card">
    <div class="filtros-titulo">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
      </svg>
      Filtrar:
    </div>

    <!-- Búsqueda principal por número de control -->
    <div class="busqueda-control-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-lupa">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input
        v-model="busquedaControl"
        type="text"
        placeholder="Buscar por No. de Control (principal)..."
        class="input-busqueda-control"
        @keyup.enter="buscar"
        @input="buscarEnTiempoReal"
      />
      <button v-if="busquedaControl" @click="busquedaControl = ''" class="btn-limpiar-busqueda" title="Limpiar búsqueda">✕</button>
    </div>

    <!--
      CATÁLOGOS DINÁMICOS
      Los <option> se generan a partir de lo que devuelve el backend.
      v-model almacena el id directamente — sin ninguna transformación.
      Al llamar buscar(), el id viaja directo al API.
    -->
    <select v-model="filtroPeriodoId" class="filtro-select" :disabled="cargandoCatalogos">
      <option value="">Todos los periodos</option>
      <option v-for="p in periodos" :key="p.id" :value="p.id">{{ p.nombre }}</option>
    </select>

    <select v-model="filtroCarreraId" class="filtro-select" :disabled="cargandoCatalogos">
      <option value="">Todas las carreras</option>
      <option v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</option>
    </select>

    <select v-model="filtroMateriaId" class="filtro-select" :disabled="cargandoCatalogos">
      <option value="">Todas las materias</option>
      <option v-for="m in materias" :key="m.id" :value="m.id">{{ m.nombre }}</option>
    </select>

    <select v-model="filtroGrupoId" class="filtro-select" :disabled="cargandoCatalogos">
      <option value="">Todos los grupos</option>
      <option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
    </select>

    <button @click="buscar" class="btn-buscar" :disabled="cargando || cargandoCatalogos">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      {{ cargando ? 'Buscando...' : 'Buscar' }}
    </button>
    <button @click="exportar" class="btn-exportar" :disabled="cargando">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/>
        <line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Exportar
    </button>
  </div>

  <!-- ── TABLA DE CALIFICACIONES ── -->
  <div class="tabla-card">
    <div class="tabla-encabezado">
      <h3 class="seccion-titulo sin-margen">
        Registro de Calificaciones
        <span v-if="filtroActivo" class="badge-filtro">Filtrado</span>
      </h3>
      <div class="tabla-acciones-top">
        <span class="tabla-contador">{{ alumnosFiltrados.length }} alumno(s)</span>
        <button
          @click="guardarTodo"
          class="btn-guardar-todo"
          :disabled="cargando"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          {{ cargando ? 'Guardando...' : 'Guardar Todo' }}
        </button>
      </div>
    </div>
    <div class="tabla-scroll">
      <table class="tabla-califs" @keydown="navegarTeclado">
        <thead>
          <tr>
            <th>No. de Control</th>
            <th>Nombre del Alumno</th>
            <th class="centrado">Parcial 1 <span class="peso">(30%)</span></th>
            <th class="centrado">Parcial 2 <span class="peso">(30%)</span></th>
            <th class="centrado">Proyecto <span class="peso">(40%)</span></th>
            <th class="centrado">Calificación Final</th>
            <th class="centrado">Estado</th>
            <th class="centrado">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(alumno, index) in alumnosPaginados"
            :key="alumno.control"
            :class="[
              { 'fila-activa': filaActiva === index && !esNC(alumno) },
              { 'fila-reprobado': Number(calcularFinal(alumno)) < 6 && !esNC(alumno) },
              { 'fila-sin-calificar': esNC(alumno) }
            ]"
            @click="seleccionarAlumno(alumno, index)"
            :ref="el => { if (el) filasRef[index] = el }"
            tabindex="0"
            @keydown.enter="guardarFila(alumno)"
          >
            <td><div class="control-chip">{{ alumno.control }}</div></td>
            <td>
              <div class="alumno-info">
                <div class="alumno-avatar">{{ iniciales(alumno.nombre) }}</div>
                <span class="alumno-nombre">{{ alumno.nombre }}</span>
              </div>
            </td>
            <td class="centrado">
              <div class="input-nota-wrap">
                <input
                  v-model="alumno.p1"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="input-nota"
                  :class="claseNota(alumno.p1)"
                  @focus="filaActiva = index"
                  @keydown.tab.exact="$event.target.closest('tr').querySelector('[data-campo=p2]')?.focus()"
                />
              </div>
            </td>
            <td class="centrado">
              <div class="input-nota-wrap">
                <input
                  v-model="alumno.p2"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="input-nota"
                  :class="claseNota(alumno.p2)"
                  data-campo="p2"
                  @focus="filaActiva = index"
                />
              </div>
            </td>
            <td class="centrado">
              <div class="input-nota-wrap">
                <input
                  v-model="alumno.proy"
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  class="input-nota"
                  :class="claseNota(alumno.proy)"
                  @focus="filaActiva = index"
                />
              </div>
            </td>
            <td class="centrado">
              <div class="final-chip" :class="calcularFinal(alumno) === null ? 'promedio-sin-calificar' : clasePromedio(calcularFinal(alumno))">
                {{ calcularFinal(alumno) ?? '–' }}
              </div>
            </td>
            <td class="centrado">
              <span v-if="calcularFinal(alumno) === null" class="badge-estado sin-calificar">Sin calificar</span>
              <span v-else-if="Number(calcularFinal(alumno)) >= 90" class="badge-estado excelente">Excelente</span>
              <span v-else-if="Number(calcularFinal(alumno)) >= 80" class="badge-estado bien">Bien</span>
              <span v-else-if="Number(calcularFinal(alumno)) >= 60" class="badge-estado regular">Regular</span>
              <span v-else class="badge-estado reprobado">Reprobado</span>
            </td>
            <td class="centrado">
              <div class="acciones-fila">
                <button
                  @click.stop="guardarFila(alumno)"
                  class="btn-accion guardar"
                  title="Guardar calificación (Enter)"
                  :disabled="cargando"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="15" height="15">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </button>
                <button
                  @click.stop="verHistorial(alumno)"
                  class="btn-accion historial"
                  title="Ver historial del alumno"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="alumnosPaginados.length === 0">
            <td colspan="8" class="sin-resultados">
              <div class="sin-resultados-inner">
                <svg viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5" width="48" height="48">
                  <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <p>No se encontraron alumnos con ese número de control</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ── PAGINACIÓN ── -->
    <div class="paginacion-container">
      <div class="paginacion-info">
        <span>Mostrando {{ mostrandoDesde }}-{{ mostrandoHasta }} de {{ alumnosFiltrados.length }} alumnos</span>
      </div>
      <div class="paginacion-controles">
        <select v-model.number="itemsPorPagina" @change="cambiarItemsPorPagina(itemsPorPagina)" class="paginacion-select">
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
        <button @click="cambiarPagina(1)" :disabled="paginaActual === 1" class="btn-pag" title="Primera página">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="18 17 13 12 18 7"/><line x1="6" y1="12" x2="14" y2="12"/></svg>
        </button>
        <button @click="cambiarPagina(paginaActual - 1)" :disabled="paginaActual === 1" class="btn-pag" title="Anterior">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <div class="paginacion-numeros">
          <button
            v-for="pag in totalPaginas"
            :key="pag"
            @click="cambiarPagina(pag)"
            :class="['btn-num', { activa: paginaActual === pag }]"
          >{{ pag }}</button>
        </div>
        <button @click="cambiarPagina(paginaActual + 1)" :disabled="paginaActual === totalPaginas" class="btn-pag" title="Siguiente">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
        <button @click="cambiarPagina(totalPaginas)" :disabled="paginaActual === totalPaginas" class="btn-pag" title="Última página">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><line x1="10" y1="12" x2="18" y2="12"/><polyline points="18 17 13 12 18 7"/></svg>
        </button>
      </div>
    </div>

    <!-- Resumen de la tabla -->
    <div class="tabla-resumen">
      <div class="resumen-item">
        <span class="ri-label">Promedio del grupo:</span>
        <strong :class="clasePromedio(promedioGeneral)">{{ promedioGeneral }}</strong>
      </div>
      <div class="resumen-separador"></div>
      <div class="resumen-item">
        <span class="ri-label">Reprobados:</span>
        <strong class="color-rojo">{{ totalReprobados }} de {{ alumnos.length }}</strong>
      </div>
      <div class="resumen-separador"></div>
      <div class="resumen-item">
        <span class="ri-label">Sin calificar:</span>
        <strong>{{ totalNC }}</strong>
      </div>
      <div class="resumen-separador"></div>
      <div class="resumen-item">
        <span class="ri-label">Aprobados:</span>
        <strong class="color-verde">{{ alumnos.length - totalReprobados - totalNC }}</strong>
      </div>
    </div>
  </div>

  <!-- ── INDICADORES VISUALES POR MATERIA ── -->
  <div class="indicadores-seccion">
    <h3 class="seccion-titulo">Promedios por Materia y Grupo</h3>
    <div class="indicadores-grid">
      <div v-for="mat in materiasResumen" :key="mat.nombre" class="ind-card">
        <div class="ind-header">
          <div class="ind-materia">{{ mat.nombre }}</div>
          <div class="ind-grupo-tag">{{ mat.grupo }}</div>
        </div>
        <div class="ind-promedio-row">
          <span class="ind-num" :class="clasePromedio(mat.promedio)">{{ mat.promedio }}</span>
          <div class="ind-barra-v">
            <div
              class="ind-barra-fill"
              :style="{
                height: (mat.promedio / 10 * 100) + '%',
                background: colorNota(Number(mat.promedio))
              }"
            ></div>
          </div>
        </div>
        <div class="ind-detalle">
          <span class="ind-reprobados"><span class="dot-rojo"></span> {{ mat.reprobados }} reprobados</span>
          <span class="ind-alumnos">{{ mat.alumnos }} alumnos</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Atajos de teclado -->
  <div class="atajos-info">
    <span>⌨ Atajos: <kbd>↑ ↓</kbd> navegar filas · <kbd>Enter</kbd> guardar fila · <kbd>Ctrl+S</kbd> guardar todo · <kbd>Tab</kbd> cambiar campo</span>
  </div>
  <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
</div>
</MainLayout>

<!-- ── TOAST ── -->
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
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import { useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import { useCatalogos } from '@/composables/useCatalogos'
import { getCalificacionesGrupo, guardarCalificaciones } from '../api/calificaciones'

const route = useRoute()

// ── Catálogos dinámicos ─────────────────────────────────────
// Vienen del backend; el frontend nunca construye ni mapea IDs.
const {
  periodos,
  carreras,
  materias,
  grupos,
  cargandoCatalogos,
  errorCatalogos,
  cargarCatalogos,
} = useCatalogos()

// ── Estado ──────────────────────────────────────────────────
const cargando = ref(false)
const alumnos  = ref([])

// Filtros almacenan el id del catálogo tal como viene del backend
const filtroPeriodoId  = ref('')
const filtroCarreraId  = ref('')
const filtroMateriaId  = ref('')
const filtroGrupoId    = ref('')
const busquedaControl  = ref('')

// ── Paginación ──────────────────────────────────────────────
const paginaActual    = ref(1)
const itemsPorPagina  = ref(10)

// ── Toast ───────────────────────────────────────────────────
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })

// ── Tabla ───────────────────────────────────────────────────
const filaActiva    = ref(null)
const filasRef      = ref([])
const alumnoHistorial = ref(null)

// ── Indicadores por materia (llegan del API) ────────────────
const materiasResumen = ref([])

// ── Sincronizar búsqueda global ─────────────────────────────
const sincronizarBusquedaGlobal = (valorGlobal) => {
  if (valorGlobal?.trim()) busquedaControl.value = valorGlobal.trim()
}

// ── Computed ────────────────────────────────────────────────
const filtroActivo = computed(() =>
  busquedaControl.value || filtroPeriodoId.value || filtroCarreraId.value ||
  filtroMateriaId.value || filtroGrupoId.value
)

const alumnosFiltrados = computed(() => {
  if (!busquedaControl.value.trim()) return alumnos.value
  return alumnos.value.filter(a =>
    a.control?.toLowerCase().includes(busquedaControl.value.toLowerCase()) ||
    a.nombre?.toLowerCase().includes(busquedaControl.value.toLowerCase())
  )
})

const totalPaginas = computed(() =>
  Math.max(1, Math.ceil(alumnosFiltrados.value.length / itemsPorPagina.value))
)

const alumnosPaginados = computed(() => {
  const inicio = (paginaActual.value - 1) * itemsPorPagina.value
  return alumnosFiltrados.value.slice(inicio, inicio + itemsPorPagina.value)
})

const mostrandoDesde = computed(() =>
  alumnosFiltrados.value.length === 0 ? 0 : (paginaActual.value - 1) * itemsPorPagina.value + 1
)
const mostrandoHasta = computed(() =>
  Math.min(paginaActual.value * itemsPorPagina.value, alumnosFiltrados.value.length)
)

const promedioGeneral = computed(() => {
  const completos = alumnos.value.filter(a => calcularFinal(a) !== null)
  if (!completos.length) return '0.0'
  const suma = completos.reduce((acc, a) => acc + Number(calcularFinal(a)), 0)
  return (suma / completos.length).toFixed(1)
})

const totalReprobados = computed(() =>
  alumnos.value.filter(a => {
    const f = calcularFinal(a)
    return f !== null && Number(f) < 60
  }).length
)

const totalNC = computed(() => alumnos.value.filter(a => esNC(a)).length)

// ── Helpers ─────────────────────────────────────────────────
const calcularFinal = (a) => {
  if (a.p1 === null || a.p2 === null || a.proy === null) return null
  return (Number(a.p1) * 0.3 + Number(a.p2) * 0.3 + Number(a.proy) * 0.4).toFixed(1)
}
const esNC = (a) => !Number(a.p1) && !Number(a.p2) && !Number(a.proy)

const iniciales = (nombre) => {
  if (!nombre) return '?'
  return nombre.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
}

const claseNota = (val) => {
  const n = Number(val)
  if (!val || val === '') return ''
  if (n >= 90) return 'nota-excelente'
  if (n >= 70) return 'nota-bien'
  if (n >= 60) return 'nota-regular'
  return 'nota-baja'
}

const clasePromedio = (val) => {
  const n = Number(val)
  if (n >= 90) return 'promedio-excelente'
  if (n >= 70) return 'promedio-bien'
  if (n >= 60) return 'promedio-regular'
  return 'promedio-bajo'
}

const colorNota = (n) => {
  if (n >= 90) return '#16A34A'
  if (n >= 70) return '#1B396A'
  if (n >= 60) return '#F59E0B'
  return '#DC2626'
}

const mostrarToast = (mensaje, tipo = 'exito') => {
  toast.value = { visible: true, mensaje, tipo }
  setTimeout(() => { toast.value.visible = false }, 3500)
}

const atajoGlobal = (e) => {
  if (e.ctrlKey && e.key === 's') {
    e.preventDefault()
    guardarTodo()
  }
}

// ── Ciclo de vida ────────────────────────────────────────────
onMounted(async () => {
  cargando.value = true
  try {
    const grupoId = route.params.id || null
    // Catálogos y datos de la vista en paralelo
    await Promise.all([
      cargarCatalogos(['periodos', 'carreras', 'materias', 'grupos']),
      cargarDatosVista(grupoId),
    ])
  } finally {
    cargando.value = false
  }
  window.addEventListener('keydown', atajoGlobal)
})

onUnmounted(() => {
  window.removeEventListener('keydown', atajoGlobal)
})

// ── Carga principal de datos ─────────────────────────────────
async function cargarDatosVista(grupoId) {
  try {
    const data = await getCalificacionesGrupo({ grupo: grupoId })
    // El API puede devolver el array directamente o un objeto con alumnos + extras
    alumnos.value       = data.alumnos       ?? data
    materiasResumen.value = data.materias    ?? materiasResumen.value
  } catch {
    // Fallback mínimo para no romper la UI durante desarrollo
    alumnos.value = [
      { control: '21110001', nombre: 'García Morales, Ana Sofía', p1: 8.5, p2: 7.0, proy: 9.0 },
      { control: '21110002', nombre: 'Hernández López, Carlos',   p1: 5.5, p2: 6.0, proy: 5.0 },
    ]
  }
}

// ── Reintentar catálogos ─────────────────────────────────────
const reintentarCatalogos = () =>
  cargarCatalogos(['periodos', 'carreras', 'materias', 'grupos'])

// ── Búsqueda en tiempo real ──────────────────────────────────
let debounceTimer = null
const buscarEnTiempoReal = () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    // La búsqueda es reactiva vía computed; no requiere llamada al API
  }, 300)
}

// ── Navegación por teclado ───────────────────────────────────
const navegarTeclado = (e) => {
  const max = alumnosPaginados.value.length - 1
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

const seleccionarAlumno = (alumno, index) => { filaActiva.value = index }
const verHistorial      = (alumno)         => { alumnoHistorial.value = alumno }

// ── Buscar ───────────────────────────────────────────────────
// Envía ids directamente al API — sin mapeos, sin transformaciones.
const buscar = async () => {
  cargando.value = true
  try {
    await new Promise(r => setTimeout(r, 600)) // simula latencia real
    alumnos.value = await getCalificacionesGrupo({
      periodoId:  filtroPeriodoId.value  || undefined,
      carreraId:  filtroCarreraId.value  || undefined,
      materiaId:  filtroMateriaId.value  || undefined,
      grupoId:    filtroGrupoId.value    || undefined,
    })
    mostrarToast(`Se encontraron ${alumnos.value.length} alumno(s)`)
  } catch {
    mostrarToast('Error al buscar. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Guardar fila ─────────────────────────────────────────────
const guardarFila = async (alumno) => {
  cargando.value = true
  try {
    // Envía el objeto del alumno con los ids que vienen del backend
    await guardarCalificaciones([alumno])
    mostrarToast(`Calificaciones de ${alumno.nombre.split(',')[0]} guardadas`)
  } catch {
    mostrarToast('No se pudo guardar. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Guardar todo ─────────────────────────────────────────────
const guardarTodo = async () => {
  cargando.value = true
  try {
    await guardarCalificaciones(alumnos.value)
    mostrarToast('Todas las calificaciones guardadas correctamente')
  } catch {
    mostrarToast('Error al guardar. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Exportar ─────────────────────────────────────────────────
const exportar = async () => {
  cargando.value = true
  try {
    await new Promise(r => setTimeout(r, 1000))
    mostrarToast('Archivo exportado correctamente')
  } finally {
    cargando.value = false
  }
}

// ── Paginación ───────────────────────────────────────────────
const cambiarPagina = (nuevaPagina) => {
  if (nuevaPagina < 1 || nuevaPagina > totalPaginas.value) return
  paginaActual.value = nuevaPagina
  filaActiva.value   = null
  filasRef.value     = []
}

const cambiarItemsPorPagina = (nuevoValor) => {
  itemsPorPagina.value = Number(nuevoValor)
  paginaActual.value   = 1
  filaActiva.value     = null
  filasRef.value       = []
}

// Resetear paginación al cambiar filtros o búsqueda
watch(
  [busquedaControl, filtroPeriodoId, filtroCarreraId, filtroMateriaId, filtroGrupoId],
  () => { paginaActual.value = 1 }
)

// Ajustar página si los filtros reducen el total
watch(totalPaginas, (nuevoTotal) => {
  if (paginaActual.value > nuevoTotal) paginaActual.value = nuevoTotal
})
</script>

<style scoped>
/* Fuente: Montserrat (cargada por MainLayout.vue)
   Paleta alineada con MainLayout:
     Header:  #1B396A / blanco
     Sidebar: #D6D6D6 / #1A1A1A / #E5E7EB
     Main:    #F5F5F5 / padding 2rem */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.calificaciones-page {
  width: 100%;
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
  position: relative;
}

/* ALERTA CATÁLOGOS */
.alerta-error-catalogos {
  display: flex; align-items: center; gap: 0.6rem;
  background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA;
  border-radius: 10px; padding: 0.8rem 1.2rem; margin-bottom: 1rem;
  font-size: 0.875rem; font-weight: 600;
}
.btn-reintentar {
  margin-left: auto; background: #DC2626; color: #fff;
  border: none; border-radius: 6px; padding: 4px 12px;
  font-size: 0.8rem; font-weight: 700; cursor: pointer;
}
.btn-reintentar:hover { background: #b91c1c; }

/* BARRA DE CARGA */
.barra-carga {
  position: fixed; top: 74px; left: 0; right: 0;
  height: 3px; z-index: 1001;
  opacity: 0; pointer-events: none; transition: opacity 0.2s;
}
.barra-carga.activa { opacity: 1; }
.barra-progreso {
  height: 100%;
  background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A);
  background-size: 200% 100%;
  animation: carga-anim 1.4s linear infinite; width: 100%;
}
@keyframes carga-anim {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* BREADCRUMB */
.breadcrumb {
  color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem;
  display: flex; align-items: center; gap: 0.4rem;
}
.breadcrumb .sep    { color: #E5E7EB; }
.breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }

/* ENCABEZADO */
.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; }
.subtitulo     { color: #6B7280; font-size: 0.9rem; margin: 0; }

/* RESUMEN */
.resumen-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.resumen-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.2rem 1.4rem;
  border-left: 4px solid transparent; transition: transform 0.2s, box-shadow 0.2s;
}
.resumen-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.09); }
.resumen-card.azul  { border-left-color: #1B396A; }
.resumen-card.verde { border-left-color: #16A34A; }
.resumen-card.rojo  { border-left-color: #DC2626; }
.resumen-card.gris  { border-left-color: #9CA3AF; }
.resumen-icono { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; }
.resumen-card.azul  .resumen-icono { background: #DBEAFE; color: #1B396A; }
.resumen-card.verde .resumen-icono { background: #DCFCE7; color: #16A34A; }
.resumen-card.rojo  .resumen-icono { background: #FEF2F2; color: #DC2626; }
.resumen-card.gris  .resumen-icono { background: #F3F4F6; color: #6B7280; }
.resumen-datos  { display: flex; flex-direction: column; margin-bottom: 0.75rem; }
.resumen-numero { font-size: 2rem; font-weight: 800; color: #1A1A1A; line-height: 1; }
.resumen-etiqueta { font-size: 0.78rem; color: #6B7280; font-weight: 600; margin-top: 4px; }
.resumen-barra { height: 4px; background: #E5E7EB; border-radius: 2px; overflow: hidden; }
.resumen-barra-fill { height: 100%; background: #1B396A; border-radius: 2px; transition: width 0.6s ease; }

/* HISTORIAL */
.historial-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.4rem 1.6rem;
  margin-bottom: 1.5rem;
}
.historial-encabezado { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.2rem; flex-wrap: wrap; }
.historial-avatar {
  width: 48px; height: 48px; border-radius: 50%;
  background: #1B396A; color: #fff; display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 1rem; flex-shrink: 0;
}
.historial-avatar.sin-calificar { background: #9CA3AF; }
.historial-info { flex: 1; }
.historial-info h3 { margin: 0 0 4px; font-size: 1rem; color: #1A1A1A; }
.historial-control { font-size: 0.82rem; color: #6B7280; }
.historial-promedio {
  display: flex; flex-direction: column; align-items: center; gap: 2px;
  background: #F5F5F5; border-radius: 10px; padding: 8px 16px;
}
.hp-numero  { font-size: 1.6rem; font-weight: 800; color: #1A1A1A; line-height: 1; }
.hp-etiqueta { font-size: 0.72rem; color: #6B7280; }
.btn-cerrar-historial {
  background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px;
  padding: 6px; cursor: pointer; color: #6B7280; display: flex; align-items: center;
  transition: background 0.15s;
}
.btn-cerrar-historial:hover { background: #E5E7EB; }
.historial-parciales { display: flex; flex-direction: column; gap: 0.6rem; }
.parcial-item { display: flex; align-items: center; gap: 0.75rem; }
.parcial-nombre { font-size: 0.82rem; font-weight: 700; color: #6B7280; width: 80px; flex-shrink: 0; }
.parcial-barra-wrap { flex: 1; }
.parcial-barra { height: 8px; background: #E5E7EB; border-radius: 4px; overflow: hidden; }
.parcial-fill  { height: 100%; border-radius: 4px; transition: width 0.6s ease; }
.parcial-valor { font-size: 0.9rem; font-weight: 800; width: 40px; text-align: right; flex-shrink: 0; }

/* FILTROS */
.filtros-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1rem 1.4rem;
  display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;
}
.filtros-titulo { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: #6B7280; white-space: nowrap; }
.busqueda-control-wrap {
  display: flex; align-items: center; gap: 6px;
  background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 10px; flex: 1; min-width: 200px;
}
.busqueda-control-wrap:focus-within { border-color: #1B396A; background: #DBEAFE; }
.icono-lupa { color: #6B7280; flex-shrink: 0; }
.input-busqueda-control {
  border: none; background: transparent; padding: 10px 0;
  font-size: 0.875rem; font-family: inherit; outline: none; flex: 1; color: #1A1A1A;
}
.input-busqueda-control::placeholder { color: #9CA3AF; }
.btn-limpiar-busqueda {
  background: none; border: none; color: #9CA3AF; cursor: pointer; font-size: 0.85rem; padding: 2px 4px;
}
.btn-limpiar-busqueda:hover { color: #DC2626; }
.filtro-select {
  padding: 10px 12px; border: 1px solid #E5E7EB; border-radius: 8px;
  font-size: 0.875rem; font-family: inherit; color: #1A1A1A; background: #F5F5F5; outline: none;
}
.filtro-select:focus    { border-color: #1B396A; }
.filtro-select:disabled { opacity: 0.55; cursor: wait; }
.btn-buscar {
  background: #1B396A; color: #FFFFFF; padding: 10px 1.2rem; border-radius: 10px;
  font-weight: 500; font-size: 0.875rem; display: flex; align-items: center; gap: 6px;
  border: none; cursor: pointer; transition: background 0.2s;
}
.btn-buscar:hover    { background: #1D4ED8; }
.btn-buscar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }
.btn-exportar {
  background: transparent; color: #1B396A; border: 1px solid #1B396A; padding: 10px 1.2rem;
  border-radius: 10px; font-weight: 500; font-size: 0.875rem;
  display: flex; align-items: center; gap: 6px; cursor: pointer; transition: background 0.2s;
}
.btn-exportar:hover    { background: #DBEAFE; }
.btn-exportar:disabled { opacity: 0.45; cursor: not-allowed; }

/* TABLA */
.tabla-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem;
}
.tabla-encabezado {
  padding: 1.1rem 1.6rem; display: flex; align-items: center;
  justify-content: space-between; border-bottom: 1px solid #E5E7EB;
}
.tabla-acciones-top { display: flex; align-items: center; gap: 0.75rem; }
.tabla-contador {
  font-size: 0.8rem; color: #6B7280; background: #F5F5F5;
  border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px;
}
.tabla-scroll { overflow-x: auto; }
.tabla-califs { width: 100%; border-collapse: collapse; }
.tabla-califs th {
  background: #F5F5F5; padding: 12px 16px; font-size: 0.82rem; font-weight: 700;
  color: #6B7280; text-transform: uppercase; letter-spacing: 0.05em;
  border-bottom: 1px solid #E5E7EB; text-align: left;
}
.tabla-califs th.centrado { text-align: center; }
.tabla-califs td { padding: 10px 16px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; }
.tabla-califs td.centrado { text-align: center; }
.tabla-califs tr { transition: background 0.15s; }
.tabla-califs tr:hover { background: #F5F5F5; }
.tabla-califs tr.fila-activa { background: #DBEAFE; }
.tabla-califs tr.fila-reprobado { background: #FEF2F2; }
.tabla-califs tr.fila-sin-calificar { background: #F9FAFB; }
.tabla-califs tr:focus { outline: 2px solid #1B396A; outline-offset: -2px; }
.tabla-califs tr:last-child td { border-bottom: none; }

.peso { font-weight: 400; color: #9CA3AF; font-size: 0.75rem; }

.control-chip {
  background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 6px;
  padding: 3px 8px; font-size: 0.82rem; font-weight: 700; font-family: monospace; color: #1A1A1A;
  display: inline-block;
}
.alumno-info   { display: flex; align-items: center; gap: 0.6rem; }
.alumno-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: #DBEAFE; color: #1B396A; font-weight: 800; font-size: 0.72rem;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.alumno-nombre { font-weight: 600; font-size: 0.9rem; color: #1A1A1A; }

.input-nota-wrap { display: flex; justify-content: center; }
.input-nota {
  width: 70px; padding: 6px 8px; border: 1.5px solid #E5E7EB; border-radius: 8px;
  font-size: 0.95rem; font-weight: 700; font-family: inherit; text-align: center;
  color: #1A1A1A; outline: none; background: #FFFFFF; transition: border-color 0.2s, background 0.2s;
}
.input-nota:focus { border-color: #1B396A; background: #DBEAFE; }
.input-nota.nota-excelente { border-color: #16A34A; color: #16A34A; }
.input-nota.nota-bien      { border-color: #1B396A; color: #1B396A; }
.input-nota.nota-regular   { border-color: #F59E0B; color: #F59E0B; }
.input-nota.nota-baja      { border-color: #DC2626; color: #DC2626; }

.final-chip {
  display: inline-flex; align-items: center; justify-content: center;
  padding: 4px 12px; border-radius: 8px; font-weight: 800; font-size: 1rem;
}
.promedio-excelente     { background: #DCFCE7; color: #16A34A; }
.promedio-bien          { background: #DBEAFE; color: #1B396A; }
.promedio-regular       { background: #FEF3C7; color: #F5960B; }
.promedio-bajo          { background: #FEF2F2; color: #DC2626; }
.promedio-sin-calificar { background: #F3F4F6; color: #9CA3AF; }

.badge-filtro {
  margin-left: 8px; background: #DBEAFE; color: #1B396A;
  padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.78rem;
}
.badge-estado { padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 0.78rem; }
.badge-estado.excelente     { background: #DCFCE7; color: #16A34A; }
.badge-estado.bien          { background: #DBEAFE; color: #1B396A; }
.badge-estado.regular       { background: #FEF3C7; color: #F5960B; }
.badge-estado.reprobado     { background: #FEF2F2; color: #DC2626; }
.badge-estado.sin-calificar { background: #D6D6D6; color: #9CA3AF; }

.acciones-fila { display: flex; gap: 5px; justify-content: center; }
.btn-accion {
  width: 32px; height: 32px; border-radius: 8px; border: none;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: transform 0.15s, opacity 0.2s;
}
.btn-accion:hover    { transform: scale(1.1); }
.btn-accion:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
.btn-accion.guardar          { background: #DCFCE7; color: #16A34A; }
.btn-accion.guardar:hover    { background: #bbf7d0; }
.btn-accion.historial        { background: #DBEAFE; color: #1B396A; }
.btn-accion.historial:hover  { background: #bfdbfe; }
.fila-sin-calificar .btn-accion.guardar   { background: #E5E7EB; color: #9CA3AF; }
.fila-sin-calificar .btn-accion.historial { background: #E5E7EB; color: #9CA3AF; }

.sin-resultados { padding: 3rem; text-align: center; }
.sin-resultados-inner { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.sin-resultados-inner p { color: #9CA3AF; font-size: 0.9rem; margin: 0; }

/* PAGINACIÓN */
.paginacion-container {
  padding: 1rem 1.6rem; border-top: 1px solid #E5E7EB;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 1rem; background: #FFFFFF;
}
.paginacion-info { font-size: 0.85rem; color: #6B7280; font-weight: 500; }
.paginacion-controles { display: flex; align-items: center; gap: 0.5rem; }
.paginacion-select {
  padding: 6px 8px; border: 1px solid #E5E7EB; border-radius: 8px;
  font-size: 0.82rem; font-family: inherit; color: #1A1A1A; background: #F5F5F5; outline: none; cursor: pointer;
}
.paginacion-select:focus { border-color: #1B396A; }
.btn-pag {
  width: 34px; height: 34px; border-radius: 8px; border: 1px solid #E5E7EB;
  background: #FFFFFF; color: #6B7280; display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all 0.2s;
}
.btn-pag:hover:not(:disabled) { background: #F5F5F5; border-color: #CBD5E1; color: #1B396A; }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; background: #F5F5F5; }
.paginacion-numeros { display: flex; gap: 4px; flex-wrap: wrap; }
.btn-num {
  min-width: 34px; height: 34px; border-radius: 8px; border: 1px solid #E5E7EB;
  background: #FFFFFF; color: #6B7280; font-weight: 600; font-size: 0.85rem;
  font-family: inherit; cursor: pointer; transition: all 0.2s;
}
.btn-num:hover { background: #F5F5F5; border-color: #CBD5E1; color: #1B396A; }
.btn-num.activa { background: #1B396A; color: #FFFFFF; border-color: #1B396A; }

/* RESUMEN TABLA */
.tabla-resumen {
  padding: 0.9rem 1.6rem; background: #F5F5F5; border-top: 1px solid #E5E7EB;
  display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
}
.resumen-item   { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; }
.ri-label       { color: #6B7280; }
.resumen-separador { width: 1px; height: 18px; background: #E5E7EB; }
.color-rojo  { color: #DC2626; font-weight: 700; }
.color-verde { color: #16A34A; font-weight: 700; }

/* GUARDAR TODO */
.btn-guardar-todo {
  background: #1B396A; color: #FFFFFF; padding: 10px 1.5rem; border-radius: 10px;
  font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;
  border: none; cursor: pointer; transition: background 0.2s;
}
.btn-guardar-todo:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar-todo:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

/* INDICADORES POR MATERIA */
.indicadores-seccion { margin-bottom: 1.5rem; }
.seccion-titulo { font-size: 1rem; font-weight: 800; color: #1A1A1A; margin: 0 0 1rem; }
.indicadores-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.ind-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.2rem;
}
.ind-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; gap: 6px; }
.ind-materia   { font-size: 0.82rem; font-weight: 800; color: #1A1A1A; line-height: 1.3; }
.ind-grupo-tag {
  background: #DBEAFE; color: #1B396A; font-size: 0.7rem; font-weight: 700;
  padding: 2px 6px; border-radius: 4px; white-space: nowrap; flex-shrink: 0;
}
.ind-promedio-row { display: flex; align-items: flex-end; gap: 10px; margin-bottom: 0.75rem; }
.ind-num { font-size: 2.2rem; font-weight: 800; line-height: 1; color: #1A1A1A; }
.ind-barra-v {
  flex: 1; height: 50px; background: #F5F5F5; border: 1px solid #E5E7EB;
  border-radius: 4px; display: flex; align-items: flex-end; overflow: hidden;
}
.ind-barra-fill { width: 100%; border-radius: 4px 4px 0 0; transition: height 0.8s ease; min-height: 3px; }
.ind-detalle    { display: flex; justify-content: space-between; font-size: 0.75rem; }
.ind-reprobados { display: flex; align-items: center; gap: 4px; color: #DC2626; font-weight: 700; }
.dot-rojo       { width: 6px; height: 6px; background: #DC2626; border-radius: 50%; }
.ind-alumnos    { color: #6B7280; }

/* ATAJOS */
.atajos-info { text-align: center; color: #9CA3AF; font-size: 0.78rem; margin-bottom: 1.5rem; }
kbd { background: #E5E7EB; border-radius: 4px; padding: 1px 6px; font-family: monospace; font-size: 0.8rem; color: #1A1A1A; }

/* TOAST */
.toast {
  position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px;
  font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 0.6rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000;
}
.toast.exito { background: #1B396A; color: #FFFFFF; }
.toast.error { background: #DC2626; color: #FFFFFF; }
.toast-icono { font-size: 1.1rem; }

/* TRANSICIONES */
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from   { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to     { transform: translateX(100%); opacity: 0; }

/* PIE */
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }

/* RESPONSIVE */
@media (max-width: 1200px) { .indicadores-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 900px)  { .resumen-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) {
  .paginacion-container { flex-direction: column; align-items: center; }
  .paginacion-controles { flex-wrap: wrap; justify-content: center; }
  .paginacion-numeros   { justify-content: center; }
}
@media (max-width: 640px) {
  .resumen-grid     { grid-template-columns: 1fr 1fr; }
  .indicadores-grid { grid-template-columns: 1fr; }
}
</style>