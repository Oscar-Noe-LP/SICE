<!-- ============================================= -->
<!-- src/views/EvaluacionesView.vue               -->
<!-- Vista de Evaluaciones - Versión mejorada     -->
<!-- ============================================= -->

<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="evaluaciones-page">

      <!-- Sincronización con búsqueda global del header de MainLayout -->
      <div v-if="busquedaGlobal && sincronizarBusquedaGlobal(busquedaGlobal)" style="display:none"></div>

      <!-- Barra de carga global -->
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Encabezado -->
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

      <!-- ── ESTADÍSTICAS GENERALES ── -->
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
            <span class="stat-etiqueta">Promedio General del Grupo</span>
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
            <span class="stat-etiqueta">Reprobados en el Grupo</span>
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
            <span class="stat-etiqueta">Criterios de Evaluación</span>
          </div>
        </div>
      </div>

      <!-- ── TARJETA DE MATERIA + PROGRESO CIRCULAR ── -->
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

        <!-- Progreso circular -->
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

      <!-- ── DISTRIBUCIÓN DE CALIFICACIONES POR GRUPO ── -->
      <div class="distribucion-card">
        <h3 class="seccion-titulo">Distribución de Calificaciones del Grupo</h3>
        <div class="distribucion-barras">
          <div
            v-for="rango in distribucionCalifs"
            :key="rango.etiqueta"
            class="barra-rango"
          >
            <div class="barra-contenedor">
              <div
                class="barra-fill"
                :style="{ height: rango.porcentaje + '%', background: rango.color }"
                :title="`${rango.cantidad} alumnos`"
              ></div>
            </div>
            <span class="barra-cantidad">{{ rango.cantidad }}</span>
            <span class="barra-etiqueta">{{ rango.etiqueta }}</span>
          </div>
        </div>
        <div class="distribucion-leyenda">
          <span v-for="rango in distribucionCalifs" :key="rango.etiqueta" class="leyenda-item">
            <span class="leyenda-color" :style="{ background: rango.color }"></span>
            {{ rango.etiqueta }}
          </span>
        </div>
      </div>

      <!-- ── FILTROS ── -->
      <div class="filtros-card">
        <div class="filtros-titulo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
          </svg>
          Filtrar por:
        </div>

        <!-- Búsqueda por número de control -->
        <div class="busqueda-control">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-busqueda">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input
            v-model="busquedaControl"
            type="text"
            placeholder="Buscar por No. de Control..."
            class="input-control"
            @keyup.enter="buscar"
            @keyup.ctrl.enter="buscar"
          />
        </div>

        <select v-model="filtroPeriodo" class="filtro-select">
          <option value="">Todos los periodos</option>
          <option>Ago/Dic 2024</option>
          <option>Ene/Jun 2025</option>
        </select>
        <select v-model="filtroMateria" class="filtro-select">
          <option value="">Todas las materias</option>
          <option>Algoritmos y Programación</option>
          <option>Cálculo Diferencial</option>
        </select>
        <select v-model="filtroGrupo" class="filtro-select">
          <option value="">Todos los grupos</option>
          <option>IS-601-A</option>
          <option>IS-601-B</option>
        </select>

        <button @click="buscar" class="btn-buscar" :disabled="cargando">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          {{ cargando ? 'Buscando...' : 'Buscar' }}
        </button>

        <button @click="generarReporte" class="btn-reporte" :disabled="cargando">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
          </svg>
          Generar Reporte
        </button>
      </div>

      <!-- ── TABLA DE CRITERIOS DE EVALUACIÓN ── -->
      <div class="tabla-card">
        <div class="tabla-encabezado">
          <h3 class="seccion-titulo sin-margen">Criterios de Evaluación</h3>
          <span class="tabla-contador">{{ criteriosFiltrados.length }} criterio(s)</span>
        </div>

        <div class="tabla-scroll">
          <table class="tabla-eval" @keydown="navegarTeclado">
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
                v-for="(item, index) in criteriosFiltrados"
                :key="index"
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
                      class="input-porcentaje"
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
                    <button
                      @click.stop="guardarFila(item)"
                      class="btn-accion guardar"
                      title="Guardar (Enter)"
                      :disabled="cargando"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16">
                        <polyline points="20 6 9 17 4 12"/>
                      </svg>
                    </button>
                    <button
                      @click.stop="editarEvaluacion(item)"
                      class="btn-accion editar"
                      title="Editar"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                      </svg>
                    </button>
                    <button
                      @click.stop="eliminarEvaluacion(index)"
                      class="btn-accion eliminar"
                      title="Eliminar"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="criteriosFiltrados.length === 0">
                <td colspan="4" class="sin-resultados">
                  No se encontraron criterios de evaluación
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Footer de la tabla -->
        <div class="tabla-footer">
          <div class="total-porcentaje" :class="{ completo: totalPorcentaje === 100, excedido: totalPorcentaje > 100 }">
            <span>Total acumulado:</span>
            <strong>{{ totalPorcentaje }}%</strong>
            <span v-if="totalPorcentaje === 100" class="check-ok">Correcto</span>
            <span v-else-if="totalPorcentaje > 100" class="alerta-exceso">Excede el 100%</span>
            <span v-else class="alerta-faltante">Faltan {{ 100 - totalPorcentaje }}% por asignar</span>
          </div>

          <button
            @click="guardarTodo"
            :disabled="totalPorcentaje !== 100 || cargando"
            class="btn-guardar-todo"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
              <polyline points="17 21 17 13 7 13 7 21"/>
              <polyline points="7 3 7 8 15 8"/>
            </svg>
            {{ cargando ? 'Guardando...' : 'Guardar Todos los Cambios' }}
          </button>
        </div>
      </div>

      <!-- ── INDICADORES POR MATERIA ── -->
      <div class="indicadores-materia">
        <h3 class="seccion-titulo">Indicadores por Materia</h3>
        <div class="materias-grid">
          <div v-for="mat in materiasList" :key="mat.nombre" class="mat-indicador-card">
            <div class="mat-header">
              <span class="mat-nombre">{{ mat.nombre }}</span>
              <span class="mat-grupo">{{ mat.grupo }}</span>
            </div>
            <div class="mat-stats">
              <div class="mat-stat">
                <span class="mat-stat-valor">{{ mat.promedio }}</span>
                <span class="mat-stat-label">Promedio</span>
              </div>
              <div class="mat-stat">
                <span class="mat-stat-valor reprobado">{{ mat.reprobados }}</span>
                <span class="mat-stat-label">Reprobados</span>
              </div>
              <div class="mat-stat">
                <span class="mat-stat-valor">{{ mat.alumnos }}</span>
                <span class="mat-stat-label">Alumnos</span>
              </div>
            </div>
            <div class="mat-nivel">
              <div class="nivel-barra">
                <div class="nivel-fill" :style="{ width: mat.avance + '%', background: colorNivel(mat.avance) }"></div>
              </div>
              <span class="nivel-texto">{{ mat.avance }}% de avance — Nivel: {{ nivelTexto(mat.avance) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Atajo de teclado -->
      <div class="atajos-info">
        <span>⌨ Atajos: <kbd>↑ ↓</kbd> navegar filas · <kbd>Enter</kbd> guardar fila · <kbd>Ctrl+S</kbd> guardar todo</span>
      </div>

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
    </div>
  </MainLayout>

  <!-- ── MODAL NUEVA EVALUACIÓN ── -->
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
            <input
              v-model="nuevoNombre"
              ref="inputNombre"
              type="text"
              placeholder="Ej: Examen Parcial 1"
              class="input-modal"
              @keyup.enter="guardarNuevaEvaluacion"
            />
          </div>
          <div class="campo-form">
            <label>Porcentaje que representa (%)</label>
            <input
              v-model.number="nuevoPorcentaje"
              type="number"
              min="0"
              max="100"
              placeholder="0"
              class="input-modal"
              @keyup.enter="guardarNuevaEvaluacion"
            />
            <span class="campo-ayuda">
              Porcentaje disponible restante: <strong>{{ 100 - totalPorcentaje }}%</strong>
            </span>
          </div>
        </div>
        <div class="modal-pie">
          <button @click="cerrarModal" class="btn-cancelar">Cancelar</button>
          <button
            @click="guardarNuevaEvaluacion"
            class="btn-confirmar"
            :disabled="!nuevoNombre.trim() || cargando"
          >
            {{ cargando ? 'Guardando...' : (modoEdicion ? 'Actualizar' : 'Agregar Evaluación') }}
          </button>
        </div>
      </div>
    </div>
  </transition>

  <!-- ── NOTIFICACIÓN TOAST ── -->
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
import MainLayout from '@/layouts/MainLayout.vue'
import { getEvaluaciones, guardarEvaluaciones, eliminarEvaluacion as eliminarEvaluacionApi } from '../api/evaluaciones'

const API = `${import.meta.env.VITE_API_URL}/api`

const eliminarEvaluacion = async (index) => {
  const item = criterios.value[index]
  if (!confirm('¿Deseas eliminar esta evaluación? Esta acción no se puede deshacer.')) return
  cargando.value = true
  try {
    if (item.id_evaluacion) {
      await eliminarEvaluacionApi(item.id_evaluacion)
    }
    criterios.value.splice(index, 1)
    mostrarToast('Evaluación eliminada')
  } catch {
    mostrarToast('No se pudo eliminar. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Estado general ──
const cargando = ref(false)
const criterios = ref([])
const filtroPeriodo = ref('Ago/Dic 2024')
const filtroMateria = ref('Algoritmos y Programación')
const filtroGrupo = ref('')
const busquedaControl = ref('')

// Sincroniza la búsqueda global del header (MainLayout) con el campo de búsqueda
const sincronizarBusquedaGlobal = (valorGlobal) => {
  if (valorGlobal && valorGlobal.trim()) {
    busquedaControl.value = valorGlobal.trim()
  }
}
const filaActiva = ref(null)
const filasRef = ref([])

// ── Modal ──
const mostrarModal = ref(false)
const modoEdicion = ref(false)
const itemEditando = ref(null)
const nuevoNombre = ref('')
const nuevoPorcentaje = ref(0)
const inputNombre = ref(null)

// ── Toast ──
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })

// ── Datos de materia actual ──
const materiaActual = ref({
  nombre: 'Algoritmos y Programación',
  aula: 'A-201',
  periodo: 'Ago/Dic 2024',
  docente: 'Mtro. Juan Morales'
})

// ── Estadísticas generales (simuladas / se sustituyen con datos reales) ──
const estadisticas = ref({
  totalAlumnos: 32,
  promedioGeneral: '7.8',
  reprobados: 5
})

// ── Distribución de calificaciones ──
const distribucionCalifs = ref([
  { etiqueta: '< 6 (Reprobado)', cantidad: 5, porcentaje: 15, color: '#DC2626' },
  { etiqueta: '6 – 6.9',         cantidad: 7, porcentaje: 21, color: '#F59E0B' },
  { etiqueta: '7 – 7.9',         cantidad: 10, porcentaje: 31, color: '#F59E0B' },
  { etiqueta: '8 – 8.9',         cantidad: 6, porcentaje: 18, color: '#1B396A' },
  { etiqueta: '9 – 10',          cantidad: 4, porcentaje: 12, color: '#16A34A' },
])

// ── Lista de materias con indicadores ──
const materiasList = ref([
  { nombre: 'Algoritmos y Programación', grupo: 'IS-601-A', promedio: '7.8', reprobados: 5, alumnos: 32, avance: 78 },
  { nombre: 'Cálculo Diferencial', grupo: 'IS-601-A', promedio: '6.9', reprobados: 9, alumnos: 32, avance: 69 },
  { nombre: 'Fundamentos de BD', grupo: 'IS-602-B', promedio: '8.4', reprobados: 2, alumnos: 28, avance: 84 },
])

// ── Computed ──
const totalPorcentaje = computed(() =>
  criterios.value.reduce((sum, c) => sum + Number(c.porcentaje || 0), 0)
)

const criteriosFiltrados = computed(() => {
  if (!busquedaControl.value.trim()) return criterios.value
  return criterios.value.filter(c =>
    c.nombre?.toLowerCase().includes(busquedaControl.value.toLowerCase())
  )
})

const statusClass = computed(() => {
  if (totalPorcentaje.value === 100) return 'status-ok'
  if (totalPorcentaje.value > 100) return 'status-error'
  return 'status-pendiente'
})

const statusMensaje = computed(() => {
  if (totalPorcentaje.value === 100) return 'El total es correcto'
  if (totalPorcentaje.value > 100) return `Excede en ${totalPorcentaje.value - 100}%`
  return `Faltan ${100 - totalPorcentaje.value}% por asignar`
})

// ── Helpers ──
const colorNivel = (avance) => {
  if (avance >= 90) return '#16A34A'
  if (avance >= 70) return '#1B396A'
  if (avance >= 50) return '#F59E0B'
  return '#DC2626'
}

const nivelTexto = (avance) => {
  if (avance >= 90) return 'Excelente'
  if (avance >= 70) return 'Bueno'
  if (avance >= 50) return 'Regular'
  return 'Bajo'
}

const mostrarToast = (mensaje, tipo = 'exito') => {
  toast.value = { visible: true, mensaje, tipo }
  setTimeout(() => { toast.value.visible = false }, 3500)
}

// ── Ciclo de vida ──
import { useRoute } from 'vue-router'
const route = useRoute()

onMounted(async () => {
  cargando.value = true
  try {
    const grupoId = route.params.id || 1
    criterios.value = await getEvaluaciones(grupoId)
  } catch (error) {
    console.log('Error:', error) // ← agrega esto
    criterios.value = [
      { nombre: 'Parcial 1', porcentaje: 30 },
      { nombre: 'Parcial 2', porcentaje: 30 },
      { nombre: 'Proyecto Final', porcentaje: 40 },
    ]
  } finally {
    cargando.value = false
  }
  window.addEventListener('keydown', atajoGlobal)
})


onUnmounted(() => {
  window.removeEventListener('keydown', atajoGlobal)
})

const atajoGlobal = (e) => {
  if (e.ctrlKey && e.key === 's') {
    e.preventDefault()
    if (totalPorcentaje.value === 100) guardarTodo()
  }
}

// ── Navegación por teclado en tabla ──
const navegarTeclado = (e) => {
  const max = criteriosFiltrados.value.length - 1
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

// ── Buscar ──
const buscar = async () => {
  cargando.value = true
  try {
    await new Promise(r => setTimeout(r, 500)) // simula latencia
    criterios.value = await getEvaluaciones(1)
    mostrarToast('Búsqueda completada')
  } catch {
    mostrarToast('Error al buscar. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Guardar fila ──
const guardarFila = async (item) => {
  cargando.value = true
  try {
    await guardarEvaluaciones(item)
    mostrarToast(`Evaluación "${item.nombre}" guardada correctamente`)
  } catch {
    mostrarToast('No se pudo guardar. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Guardar todo ──
const guardarTodo = async () => {
  cargando.value = true
  try {
    await guardarEvaluaciones(criterios.value)
    mostrarToast('Todos los criterios guardados correctamente')
  } catch {
    mostrarToast('Error al guardar. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Modal ──
const abrirModalNueva = () => {
  nuevoNombre.value = ''
  nuevoPorcentaje.value = 0
  modoEdicion.value = false
  itemEditando.value = null
  mostrarModal.value = true
  nextTick(() => inputNombre.value?.focus())
}

const editarEvaluacion = (item) => {
  nuevoNombre.value = item.nombre
  nuevoPorcentaje.value = item.porcentaje
  modoEdicion.value = true
  itemEditando.value = item
  mostrarModal.value = true
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
      await guardarEvaluaciones(itemEditando.value)
      mostrarToast(`Evaluación "${itemEditando.value.nombre}" actualizada`)
    } else {
      await guardarEvaluaciones({ nombre: nuevoNombre.value.trim(), porcentaje: Number(nuevoPorcentaje.value) || 0 })
      criterios.value = await getEvaluaciones(1)
      mostrarToast('Nueva evaluación agregada')
    }
    cerrarModal()
  } catch {
    mostrarToast('No se pudo guardar. Intenta de nuevo.', 'error')
  } finally {
    cargando.value = false
  }
}



// ── Reporte ──
const generarReporte = async () => {
  cargando.value = true
  try {
    await new Promise(r => setTimeout(r, 1200))
    mostrarToast('Reporte generado correctamente')
  } finally {
    cargando.value = false
  }
}
</script>


<style scoped>
/* Fuente: Montserrat (cargada por MainLayout.vue)
   Paleta alineada con MainLayout:
     Header:  #1B396A (fondo) / blanco (texto)
     Sidebar: #D6D6D6 (fondo) / #1A1A1A (texto) / #E5E7EB (hover/active/submenu)
     Main:    #F5F5F5 (fondo) / padding 2rem
   z-index: header 1000, barra-carga 1001, modal 2000, toast 3000 */
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* PALETA OFICIAL: Fondo #F5F5F5 | Cards #FFFFFF border #E5E7EB shadow 0 4px 12px rgba(0,0,0,.05)
   Texto: #1A1A1A / #6B7280 / #9CA3AF(disabled)
   Acción: #1B396A hover #1D4ED8 fondo suave #DBEAFE
   Éxito: #16A34A/#DCFCE7  Error: #DC2626/#FEF2F2  Warn: #F5960B/#FEF3C7  Info: #2563EB/#DBEAFE */

.evaluaciones-page {
  /* MainLayout provee: background #F5F5F5, padding 2rem, font Montserrat,
     margin-top 74px (header), margin-left 260px (sidebar). */
  width: 100%;
  font-family: 'Montserrat', sans-serif;
  padding-bottom: 2rem;
  position: relative;
}

/* BARRA DE CARGA */
.barra-carga {
  /* top:74px para quedar justo debajo del header fijo de MainLayout */
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
/* Breadcrumb links coherentes con rutas de MainLayout */
.breadcrumb-link {
  color: #6B7280;
  text-decoration: none;
  transition: color 0.15s;
}
.breadcrumb-link:hover { color: #1B396A; }


/* ENCABEZADO */
.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; }
.subtitulo     { color: #6B7280; font-size: 0.9rem; margin: 0; }

/* ESTADÍSTICAS */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.2rem 1.4rem;
  display: flex; align-items: center; gap: 1rem;
  border-left: 4px solid transparent; transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.09); }
.stat-card.azul    { border-left-color: #1B396A; }
.stat-card.verde   { border-left-color: #16A34A; }
.stat-card.rojo    { border-left-color: #DC2626; }
.stat-card.naranja { border-left-color: #F5960B; }
.stat-icono { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-card.azul    .stat-icono { background: #DBEAFE; color: #1B396A; }
.stat-card.verde   .stat-icono { background: #DCFCE7; color: #16A34A; }
.stat-card.rojo    .stat-icono { background: #FEF2F2; color: #DC2626; }
.stat-card.naranja .stat-icono { background: #FEF3C7; color: #F5960B; }
.stat-datos   { display: flex; flex-direction: column; }
.stat-numero  { font-size: 1.8rem; font-weight: 800; color: #1A1A1A; line-height: 1; }
.stat-etiqueta { font-size: 0.78rem; color: #6B7280; font-weight: 600; margin-top: 2px; }

/* MATERIA + PROGRESO */
.materia-progreso-row { display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1.5rem; }
.materia-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.4rem 1.6rem;
  display: flex; align-items: center; gap: 1.2rem;
}
.materia-badge  { background: #1B396A; color: #FFFFFF; font-weight: 800; font-size: 0.85rem; padding: 0.6rem 0.8rem; border-radius: 8px; flex-shrink: 0; }
.materia-nombre { font-size: 1.15rem; font-weight: 800; color: #1A1A1A; margin: 0 0 0.3rem; }
.materia-meta   { display: flex; gap: 1.2rem; flex-wrap: wrap; font-size: 0.82rem; color: #6B7280; }
.btn-nueva-eval {
  margin-left: auto; background: #1B396A; color: #FFFFFF;
  padding: 10px 16px; border-radius: 10px; font-weight: 500; font-size: 0.9rem;
  display: flex; align-items: center; gap: 6px; border: none; cursor: pointer;
  white-space: nowrap; transition: background 0.2s;
}
.btn-nueva-eval:hover    { background: #1D4ED8; }
.btn-nueva-eval:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

.progreso-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.2rem 1.6rem;
  display: flex; flex-direction: column; align-items: center; gap: 0.75rem; min-width: 200px;
}
.progreso-circular-wrap { position: relative; width: 100px; height: 100px; }
.svg-circular  { width: 100px; height: 100px; }
.progreso-texto {
  position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;
}
.progreso-numero         { display: block; font-size: 1.8rem; font-weight: 800; color: #1B396A; line-height: 1; }
.progreso-numero.completo { color: #16A34A; }
.progreso-numero.excedido { color: #DC2626; }
.progreso-label  { font-size: 0.72rem; color: #6B7280; }
.progreso-status { font-size: 0.82rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
.status-ok        { background: #DCFCE7; color: #16A34A; }
.status-error     { background: #FEF2F2; color: #DC2626; }
.status-pendiente { background: #DBEAFE; color: #1B396A; }

/* DISTRIBUCIÓN */
.distribucion-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.4rem 1.6rem; margin-bottom: 1.5rem;
}
.seccion-titulo     { font-size: 1rem; font-weight: 800; color: #1A1A1A; margin: 0 0 1.2rem; }
.seccion-titulo.sin-margen { margin: 0; }
.distribucion-barras {
  display: flex; gap: 1.5rem; align-items: flex-end; height: 120px; padding-bottom: 0.5rem;
}
.barra-rango    { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; }
.barra-contenedor {
  width: 100%; height: 90px; background: #F5F5F5; border: 1px solid #E5E7EB;
  border-radius: 6px; display: flex; align-items: flex-end; overflow: hidden;
}
.barra-fill    { width: 100%; border-radius: 6px 6px 0 0; transition: height 0.8s ease; min-height: 4px; }
.barra-cantidad { font-size: 0.85rem; font-weight: 800; color: #1A1A1A; }
.barra-etiqueta { font-size: 0.7rem; color: #6B7280; text-align: center; }
.distribucion-leyenda {
  display: flex; gap: 1rem; flex-wrap: wrap;
  margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #E5E7EB;
}
.leyenda-item { display: flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #6B7280; }
.leyenda-color { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

/* FILTROS */
.filtros-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1rem 1.4rem;
  display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;
}
.filtros-titulo { display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 700; color: #6B7280; white-space: nowrap; }
.busqueda-control {
  display: flex; align-items: center; gap: 8px;
  background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 12px;
}
.busqueda-control:focus-within { border-color: #1B396A; background: #DBEAFE; }
.icono-busqueda { color: #6B7280; flex-shrink: 0; }
.input-control {
  border: none; background: transparent; padding: 10px 0;
  font-size: 0.875rem; font-family: inherit; outline: none; width: 200px; color: #1A1A1A;
}
.input-control::placeholder { color: #9CA3AF; }
.filtro-select {
  padding: 10px 12px; border: 1px solid #E5E7EB; border-radius: 8px;
  font-size: 0.875rem; font-family: inherit; color: #1A1A1A; background: #F5F5F5; outline: none;
}
.filtro-select:focus { border-color: #1B396A; }
.btn-buscar {
  background: #1B396A; color: #FFFFFF; padding: 10px 1.2rem; border-radius: 10px;
  font-weight: 500; font-size: 0.875rem; display: flex; align-items: center; gap: 6px;
  border: none; cursor: pointer; transition: background 0.2s;
}
.btn-buscar:hover    { background: #1D4ED8; }
.btn-buscar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }
.btn-reporte {
  background: transparent; color: #1B396A; border: 1px solid #1B396A; padding: 10px 1.2rem;
  border-radius: 10px; font-weight: 500; font-size: 0.875rem;
  display: flex; align-items: center; gap: 6px; cursor: pointer; transition: background 0.2s;
}
.btn-reporte:hover    { background: #DBEAFE; }
.btn-reporte:disabled { opacity: 0.45; cursor: not-allowed; }

/* TABLA */
.tabla-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem;
}
.tabla-encabezado {
  padding: 1.1rem 1.6rem; display: flex; align-items: center;
  justify-content: space-between; border-bottom: 1px solid #E5E7EB;
}
.tabla-contador {
  font-size: 0.8rem; color: #6B7280; background: #F5F5F5;
  border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px;
}
.tabla-scroll { overflow-x: auto; }
.tabla-eval   { width: 100%; border-collapse: collapse; }
.tabla-eval th {
  background: #F5F5F5; padding: 12px 16px; font-size: 0.82rem; font-weight: 700;
  color: #6B7280; text-transform: uppercase; letter-spacing: 0.05em;
  border-bottom: 1px solid #E5E7EB; text-align: left;
}
.tabla-eval th.centrado { text-align: center; }
.tabla-eval td { padding: 12px 16px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; }
.tabla-eval td.centrado { text-align: center; }
.tabla-eval tr { transition: background 0.15s; }
.tabla-eval tr:hover      { background: #F5F5F5; }
.tabla-eval tr.fila-activa { background: #DBEAFE; }
.tabla-eval tr:focus { outline: 2px solid #1B396A; outline-offset: -2px; }
.tabla-eval tr:last-child td { border-bottom: none; }

.nombre-eval  { font-weight: 700; color: #1A1A1A; font-size: 0.95rem; }
.celda-nombre { min-width: 200px; }

.input-porcentaje-wrap {
  display: inline-flex; align-items: center; gap: 4px;
  background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 4px 10px;
}
.input-porcentaje-wrap:focus-within { border-color: #1B396A; background: #DBEAFE; }
.input-porcentaje {
  width: 60px; border: none; background: transparent;
  font-size: 0.95rem; font-weight: 700; font-family: inherit; text-align: center; color: #1A1A1A; outline: none;
}
.pct-signo { color: #6B7280; font-size: 0.85rem; }

.mini-barra-wrap { display: flex; flex-direction: column; align-items: center; gap: 4px; min-width: 100px; }
.mini-barra { width: 100%; height: 8px; background: #E5E7EB; border-radius: 4px; overflow: hidden; }
.mini-barra-fill { height: 100%; background: #1B396A; border-radius: 4px; transition: width 0.4s ease; }
.mini-pct { font-size: 0.75rem; font-weight: 700; color: #1B396A; }

.acciones-fila { display: flex; gap: 6px; justify-content: center; }
.btn-accion {
  width: 34px; height: 34px; border-radius: 8px; border: none;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: transform 0.15s, opacity 0.2s;
}
.btn-accion:hover    { transform: scale(1.1); }
.btn-accion:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
.btn-accion.guardar          { background: #DCFCE7; color: #16A34A; }
.btn-accion.guardar:hover    { background: #bbf7d0; }
.btn-accion.editar           { background: #DBEAFE; color: #1B396A; }
.btn-accion.editar:hover     { background: #bfdbfe; }
.btn-accion.eliminar         { background: #FEF2F2; color: #DC2626; }
.btn-accion.eliminar:hover   { background: #fecaca; }

.sin-resultados { padding: 2.5rem; text-align: center; color: #9CA3AF; font-size: 0.9rem; }

/* FOOTER TABLA */
.tabla-footer {
  padding: 1rem 1.6rem; background: #F5F5F5;
  display: flex; align-items: center; justify-content: space-between;
  border-top: 1px solid #E5E7EB; flex-wrap: wrap; gap: 1rem;
}
.total-porcentaje { display: flex; align-items: center; gap: 0.6rem; font-size: 0.9rem; color: #6B7280; }
.total-porcentaje strong { font-size: 1.1rem; font-weight: 800; color: #1A1A1A; }
.total-porcentaje.completo strong { color: #16A34A; }
.total-porcentaje.excedido strong { color: #DC2626; }
.check-ok        { background: #DCFCE7; color: #16A34A; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }
.alerta-exceso   { background: #FEF2F2; color: #DC2626; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }
.alerta-faltante { background: #DBEAFE; color: #1B396A; padding: 3px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }
.btn-guardar-todo {
  background: #1B396A; color: #FFFFFF; padding: 10px 1.5rem; border-radius: 10px;
  font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;
  border: none; cursor: pointer; transition: background 0.2s;
}
.btn-guardar-todo:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar-todo:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

/* INDICADORES POR MATERIA */
.indicadores-materia { margin-bottom: 1.5rem; }
.materias-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.mat-indicador-card {
  background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.2rem 1.4rem;
}
.mat-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
.mat-nombre { font-size: 0.9rem; font-weight: 800; color: #1A1A1A; }
.mat-grupo  { background: #DBEAFE; color: #1B396A; font-size: 0.75rem; font-weight: 700; padding: 3px 8px; border-radius: 6px; }
.mat-stats  { display: flex; gap: 0.75rem; margin-bottom: 1rem; }
.mat-stat   { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 2px; }
.mat-stat-valor           { font-size: 1.3rem; font-weight: 800; color: #1A1A1A; }
.mat-stat-valor.reprobado { color: #DC2626; }
.mat-stat-label           { font-size: 0.72rem; color: #6B7280; font-weight: 600; }
.mat-nivel  { display: flex; flex-direction: column; gap: 4px; }
.nivel-barra { height: 8px; background: #E5E7EB; border-radius: 4px; overflow: hidden; }
.nivel-fill  { height: 100%; border-radius: 4px; transition: width 0.8s ease; }
.nivel-texto { font-size: 0.75rem; color: #6B7280; }

/* ATAJOS */
.atajos-info { text-align: center; color: #9CA3AF; font-size: 0.78rem; margin-bottom: 1.5rem; }
kbd { background: #E5E7EB; border-radius: 4px; padding: 1px 6px; font-family: monospace; font-size: 0.8rem; color: #1A1A1A; }

/* MODAL */
.modal-fondo {
  position: fixed; inset: 0; background: rgba(0,0,0,0.55);
  display: flex; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(3px);
}
.modal-caja { background: #FFFFFF; width: 460px; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.25); }
.modal-cabecera { background: #1B396A; color: #FFFFFF; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; }
.modal-cabecera h3 { margin: 0; font-size: 1.1rem; font-weight: 800; }
.btn-cerrar {
  background: none; border: none; color: rgba(255,255,255,0.8); cursor: pointer;
  display: flex; align-items: center; justify-content: center; padding: 4px; border-radius: 6px; transition: color 0.2s;
}
.btn-cerrar:hover { color: #FFFFFF; }
.modal-cuerpo { padding: 1.6rem; }
.campo-form   { margin-bottom: 1.2rem; }
.campo-form label { display: block; font-weight: 700; font-size: 0.875rem; color: #1A1A1A; margin-bottom: 6px; }
.input-modal {
  width: 100%; padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px;
  font-size: 0.95rem; font-family: inherit; color: #1A1A1A; outline: none;
  transition: border-color 0.2s; box-sizing: border-box; background: #FFFFFF;
}
.input-modal:focus      { border-color: #1B396A; background: #DBEAFE; }
.input-modal::placeholder { color: #9CA3AF; }
.campo-ayuda { font-size: 0.78rem; color: #6B7280; margin-top: 4px; display: block; }
.modal-pie {
  padding: 1rem 1.6rem; background: #F5F5F5;
  display: flex; gap: 0.75rem; justify-content: flex-end; border-top: 1px solid #E5E7EB;
}
.btn-cancelar {
  background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB;
  padding: 10px 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem;
  cursor: pointer; transition: background 0.2s;
}
.btn-cancelar:hover { background: #F5F5F5; }
.btn-confirmar {
  background: #1B396A; color: #FFFFFF; border: none;
  padding: 10px 1.4rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem;
  cursor: pointer; transition: background 0.2s;
}
.btn-confirmar:hover:not(:disabled) { background: #1D4ED8; }
.btn-confirmar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

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
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s; }
.modal-fade-enter-from, .modal-fade-leave-to       { opacity: 0; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from   { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to     { transform: translateX(100%); opacity: 0; }

/* PIE */
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }

/* RESPONSIVE */
@media (max-width: 1024px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .materias-grid { grid-template-columns: repeat(2, 1fr); }
  .materia-progreso-row { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .stats-grid { grid-template-columns: 1fr 1fr; }
  .materias-grid { grid-template-columns: 1fr; }
}
</style>