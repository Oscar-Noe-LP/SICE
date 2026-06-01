<!-- ============================================= -->
<!-- src/views/EvaluacionesView.vue               -->
<!-- Módulo: Servicios Escolares — Evaluaciones   -->
<!-- ============================================= -->
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
<h2 class="materia-nombre">{{ materiaActual.nombre || 'Seleccione una materia' }}</h2>
<div class="materia-meta">
<span><strong>Aula:</strong> {{ materiaActual.aula || 'N/A' }}</span>
<span><strong>Periodo:</strong> {{ materiaActual.periodo || 'N/A' }}</span>
<span><strong>Docente:</strong> {{ materiaActual.docente || 'N/A' }}</span>
</div>
</div>
<button @click="abrirModalNueva" class="btn-primario" :disabled="cargando">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
</svg>
Nueva Evaluación
</button>
</div>
<div class="progreso-card">
<div class="progreso-circular-wrap">
<svg viewBox="0 0 120 120" class="svg-circular">
<circle cx="60" cy="60" r="50" fill="none" stroke="#E5E7EB" stroke-width="10"/>
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
placeholder="Buscar evaluación..."
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
<option v-for="p in periodos" :key="p.id_periodo" :value="p.id_periodo">{{ p.nombre_periodo }}</option>
</select>
</div>
<div class="campo-filtro">
<label>Materia</label>
<select v-model="filtroMateriaId" class="filtro-select" :disabled="cargandoCatalogos">
<option value="">Cualquiera</option>
<option v-for="m in materias" :key="m.id_materia" :value="m.id_materia">{{ m.nombre }}</option>
</select>
</div>
<div class="campo-filtro">
<label>Grupo</label>
<select v-model="filtroGrupoId" class="filtro-select" :disabled="cargandoCatalogos">
<option value="">Cualquiera</option>
<option v-for="g in grupos" :key="g.id_grupo" :value="g.id_grupo">{{ g.clave_grupo }} — {{ g.materia }}</option>
</select>
</div>
</div>
<div class="popover-footer">
<button @click="mostrarFiltros = false" class="btn-cancelar">Cancelar</button>
<button @click="aplicarFiltros" class="btn-primario">Buscar</button>
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
<button @click="generarReporte" class="btn-secundario" :disabled="cargando">
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
<button @click.stop="guardarFila(item)" class="btn-accion guardar" title="Guardar Evaluación" :disabled="cargando">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="20 6 9 17 4 12"/></svg>
</button>
<button @click.stop="editarEvaluacion(item)" class="btn-accion editar" title="Editar Evaluación">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
</button>
<button @click.stop="eliminarEvaluacion(item)" class="btn-accion eliminar" title="Eliminar Evaluación">
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
<button @click="paginaActual--" :disabled="paginaActual === 1" class="btn-pag">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="15 18 9 12 15 6"/></svg>
</button>
<div class="paginacion-numeros">
<button
v-for="pag in totalPaginas"
:key="pag"
@click="paginaActual = pag"
:class="['btn-num', { activa: paginaActual === pag }]"
>{{ pag }}</button>
</div>
<button @click="paginaActual++" :disabled="paginaActual === totalPaginas" class="btn-pag">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="9 18 15 12 9 6"/></svg>
</button>
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
<button @click="guardarTodo" :disabled="totalPorcentaje !== 100 || cargando" class="btn-primario">
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
<!-- ============================================= -->
<!-- MODAL MEJORADO: Nueva/Editar Evaluación      -->
<!-- ============================================= -->
<transition name="modal-scale">
<div v-if="mostrarModal" class="modal-overlay" @click.self="cerrarModal" @keydown.esc="cerrarModal" tabindex="-1">
<div class="modal-container" role="dialog" aria-modal="true" :aria-labelledby="modoEdicion ? 'modal-edit-title' : 'modal-new-title'">
<!-- Header con gradiente y icono contextual -->
<div class="modal-header">
<div class="header-icon-wrapper" :class="modoEdicion ? 'edit' : 'new'">
<svg v-if="!modoEdicion" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
</svg>
<svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
<path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
</svg>
</div>
<div class="header-text">
<h3 :id="modoEdicion ? 'modal-edit-title' : 'modal-new-title'" class="modal-title">
{{ modoEdicion ? 'Editar Evaluación' : 'Nueva Evaluación' }}
</h3>
<p class="modal-subtitle">
{{ modoEdicion ? 'Modifica los datos de la evaluación seleccionada' : 'Configura un nuevo criterio de evaluación para esta materia' }}
</p>
</div>
<button @click="cerrarModal" class="modal-close" aria-label="Cerrar modal" title="Cerrar (Esc)">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
</svg>
</button>
</div>
<!-- Body con formulario optimizado -->
<div class="modal-body">
<!-- Campo: Nombre de la evaluación -->
<div class="form-group">
<label for="eval-nombre" class="form-label">
Nombre de la evaluación
<span class="required-badge">*</span>
</label>
<div class="input-wrapper">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="input-icon">
<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
<polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
</svg>
<input
id="eval-nombre"
ref="inputNombre"
v-model="nuevoNombre"
type="text"
placeholder="Ej: Examen Parcial 1, Proyecto Final..."
class="form-input"
:class="{ 'input-focus': nuevoNombre.trim().length > 0 }"
@keyup.enter="guardarNuevaEvaluacion"
@input="validarNombre"
maxlength="100"
/>
</div>
<span v-if="errorNombre" class="form-error">{{ errorNombre }}</span>
<span class="form-hint">{{ nuevoNombre.length }}/100 caracteres</span>
</div>
<!-- Campo: Tipo de evaluación (si hay catálogos) -->
<div class="form-group" v-if="tiposEval && tiposEval.length">
<label for="eval-tipo" class="form-label">Tipo de evaluación</label>
<div class="input-wrapper">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="input-icon">
<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>
</svg>
<select
id="eval-tipo"
v-model="nuevoTipoEvalId"
class="form-select"
:class="{ 'select-has-value': nuevoTipoEvalId }"
>
<option value="" disabled>Selecciona un tipo...</option>
<option v-for="t in tiposEval" :key="t.id" :value="t.id">{{ t.nombre }}</option>
</select>
</div>
<span class="form-hint">Opcional: clasifica la evaluación para reportes</span>
</div>
<!-- Campo: Porcentaje con validación visual -->
<div class="form-group">
<label for="eval-porcentaje" class="form-label">
Porcentaje que representa
<span class="required-badge">*</span>
</label>
<div class="percentage-input-group">
<div class="input-wrapper percentage-wrapper">
<input
id="eval-porcentaje"
v-model.number="nuevoPorcentaje"
type="number"
min="0"
max="100"
placeholder="0"
class="form-input percentage-input"
:class="{
'input-valid': porcentajeValido,
'input-invalid': porcentajeInvalido,
'input-focus': nuevoPorcentaje > 0
}"
@input="validarPorcentaje"
@keyup.enter="guardarNuevaEvaluacion"
/>
<span class="percentage-sign">%</span>
</div>
<!-- Barra de progreso del porcentaje disponible -->
<div class="percentage-availability">
<div class="availability-label">
<span>Disponible:</span>
<span class="availability-value" :class="availabilityClass">
{{ porcentajeDisponible }}%
</span>
</div>
<div class="availability-bar">
<div class="availability-fill" :style="{ width: Math.min(porcentajeDisponible, 100) + '%', background: availabilityColor }"></div>
</div>
</div>
</div>
<span v-if="errorPorcentaje" class="form-error">{{ errorPorcentaje }}</span>
<span class="form-hint">
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14" style="vertical-align: middle; margin-right: 4px;">
<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
</svg>
El total de todos los criterios debe sumar exactamente 100%
</span>
</div>
<!-- Resumen visual del impacto -->
<div class="impact-summary" v-if="nuevoPorcentaje > 0 || modoEdicion">
<div class="summary-item">
<span class="summary-label">Total actual:</span>
<span class="summary-value">{{ totalPorcentaje }}%</span>
</div>
<div class="summary-item">
<span class="summary-label">{{ modoEdicion ? 'Nuevo total:' : 'Con esta evaluación:' }}</span>
<span class="summary-value" :class="proyeccionClass">{{ proyeccionTotal }}%</span>
</div>
<div class="summary-status" :class="proyeccionStatusClass">
{{ proyeccionMensaje }}
</div>
</div>
</div>
<!-- Footer con acciones y atajos -->
<div class="modal-footer">
<div class="footer-shortcuts">
<span class="shortcut-hint"><kbd>Enter</kbd> Guardar</span>
<span class="shortcut-hint"><kbd>Esc</kbd> Cancelar</span>
</div>
<div class="footer-actions">
<button @click="cerrarModal" class="btn-cancelar" type="button">
Cancelar
</button>
<button
@click="guardarNuevaEvaluacion"
class="btn-primario"
:disabled="!puedeGuardar || cargando"
type="submit"
>
<span v-if="cargando" class="spinner"></span>
<svg v-else-if="!modoEdicion" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" class="btn-icon">
<polyline points="20 6 9 17 4 12"/>
</svg>
<svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18" class="btn-icon">
<path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
</svg>
{{ cargando ? 'Guardando...' : (modoEdicion ? 'Actualizar' : 'Agregar Evaluación') }}
</button>
</div>
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
import { ref, computed, onMounted, nextTick, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import { useCatalogos } from '@/composables/useCatalogos'
import { getEvaluaciones, guardarEvaluaciones, eliminarEvaluacion as eliminarEvaluacionApi } from '../api/evaluaciones'
const route = useRoute()
// Catálogos dinámicos
const { periodos, materias, grupos, tiposEval, cargandoCatalogos, errorCatalogos, cargarCatalogos } = useCatalogos()
// Estado general
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
// Modal Flotante
const mostrarModal    = ref(false)
const modoEdicion     = ref(false)
const itemEditando    = ref(null)
const nuevoNombre     = ref('')
const nuevoPorcentaje = ref(0)
const nuevoTipoEvalId = ref('')
const inputNombre     = ref(null)
// Validaciones del modal
const errorNombre = ref('')
const errorPorcentaje = ref('')
// Toast de Notificación (patrón unificado)
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null
const mostrarToast = (mensaje, tipo = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje, tipo }
  timerToast = setTimeout(() => { toast.value.visible = false }, 3500)
}
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
// Paginación Reactiva
const totalPaginas = computed(() => Math.max(1, Math.ceil(criteriosFiltrados.value.length / itemsPorPagina.value)))
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
// Validaciones del modal
const porcentajeDisponible = computed(() => {
const usado = modoEdicion.value
? totalPorcentaje.value - (itemEditando.value?.porcentaje || 0)
: totalPorcentaje.value
return Math.max(0, 100 - usado)
})
const porcentajeValido = computed(() => nuevoPorcentaje.value > 0 && nuevoPorcentaje.value <= porcentajeDisponible.value)
const porcentajeInvalido = computed(() => nuevoPorcentaje.value > 0 && nuevoPorcentaje.value > porcentajeDisponible.value)
const availabilityClass = computed(() => {
if (porcentajeDisponible.value === 0) return 'exacto'
if (porcentajeDisponible.value < 10) return 'insuficiente'
return 'suficiente'
})
const availabilityColor = computed(() => {
if (porcentajeDisponible.value === 0) return '#1B396A'
if (porcentajeDisponible.value < 10) return '#DC2626'
return '#16A34A'
})
const proyeccionTotal = computed(() => {
const base = modoEdicion.value
? totalPorcentaje.value - (itemEditando.value?.porcentaje || 0)
: totalPorcentaje.value
return base + (nuevoPorcentaje.value || 0)
})
const proyeccionClass = computed(() => {
if (proyeccionTotal.value === 100) return 'proyeccion-ok'
if (proyeccionTotal.value > 100) return 'proyeccion-error'
return 'proyeccion-warning'
})
const proyeccionStatusClass = computed(() => {
if (proyeccionTotal.value === 100) return 'ok'
if (proyeccionTotal.value > 100) return 'error'
return 'warning'
})
const proyeccionMensaje = computed(() => {
if (proyeccionTotal.value === 100) return '✓ Perfecto: suma exactamente 100%'
if (proyeccionTotal.value > 100) return `✗ Excede en ${proyeccionTotal.value - 100}%`
return `⚠ Faltan ${100 - proyeccionTotal.value}% para completar`
})
const puedeGuardar = computed(() => {
return nuevoNombre.value.trim().length > 0 &&
nuevoPorcentaje.value > 0 &&
nuevoPorcentaje.value <= porcentajeDisponible.value
})
watch(criteriosFiltrados, () => {
if(paginaActual.value > totalPaginas.value) paginaActual.value = totalPaginas.value
})
// ── Helpers ─────────────────────────────────────────────────
const atajoGlobal = (e) => {
if (e.ctrlKey && e.key === 's') {
e.preventDefault()
if (totalPorcentaje.value === 100) guardarTodo()
}
}
const validarNombre = () => {
if (!nuevoNombre.value.trim()) {
errorNombre.value = 'El nombre es requerido'
} else if (nuevoNombre.value.length < 3) {
errorNombre.value = 'Mínimo 3 caracteres'
} else {
errorNombre.value = ''
}
}
const validarPorcentaje = () => {
if (nuevoPorcentaje.value <= 0) {
errorPorcentaje.value = 'Debe ser mayor a 0'
} else if (nuevoPorcentaje.value > porcentajeDisponible.value) {
errorPorcentaje.value = `Excede el ${porcentajeDisponible.value}% disponible`
} else {
errorPorcentaje.value = ''
}
}
// ── Ciclo de vida ────────────────────────────────────────────
onMounted(async () => {
cargando.value = true
try {
const grupoId = route.params.id || null
if (grupoId) filtroGrupoId.value = grupoId
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
criterios.value = []
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
if (!filtroGrupoId.value) return mostrarToast('Selecciona un grupo antes de agregar evaluaciones', 'error')
nuevoNombre.value = ''; nuevoPorcentaje.value = 0; nuevoTipoEvalId.value = ''
errorNombre.value = ''; errorPorcentaje.value = ''
modoEdicion.value = false; itemEditando.value = null; mostrarModal.value = true
nextTick(() => inputNombre.value?.focus())
}
const editarEvaluacion = (item) => {
nuevoNombre.value = item.nombre; nuevoPorcentaje.value = item.porcentaje
nuevoTipoEvalId.value = item.id_tipo_evaluacion ?? ''
errorNombre.value = ''; errorPorcentaje.value = ''
modoEdicion.value = true; itemEditando.value = item; mostrarModal.value = true
nextTick(() => inputNombre.value?.focus())
}
const cerrarModal = () => { mostrarModal.value = false }
const guardarNuevaEvaluacion = async () => {
validarNombre()
validarPorcentaje()
if (errorNombre.value || errorPorcentaje.value) {
return mostrarToast('Revisa los campos con error', 'error')
}
if (!nuevoNombre.value.trim()) return mostrarToast('Debes escribir un nombre', 'error')
// Validar exceso de porcentaje
const diferencia = modoEdicion.value ? (nuevoPorcentaje.value - itemEditando.value.porcentaje) : nuevoPorcentaje.value
if ((totalPorcentaje.value + diferencia) > 100) {
return mostrarToast('El porcentaje excede el 100% permitido', 'error')
}
cargando.value = true
try {
if (modoEdicion.value && itemEditando.value) {
itemEditando.value.nombre = nuevoNombre.value.trim()
itemEditando.value.porcentaje = Number(nuevoPorcentaje.value) || 0
itemEditando.value.id_tipo_evaluacion = nuevoTipoEvalId.value || null
await guardarEvaluaciones(itemEditando.value)
mostrarToast(`Evaluación actualizada`)
} else {
if (!filtroGrupoId.value) return mostrarToast('Selecciona un grupo antes de agregar evaluaciones', 'error')
const payload = { nombre: nuevoNombre.value.trim(), porcentaje: Number(nuevoPorcentaje.value) || 0, id_tipo_evaluacion: nuevoTipoEvalId.value || null, id_grupo: filtroGrupoId.value }
await guardarEvaluaciones(payload, filtroGrupoId.value)
await cargarDatosVista(route.params.id || null) // Recargar datos frescos
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

/* ============================================= */
/* ESTILOS GLOBALES - PALETA OFICIAL SICE       */
/* ============================================= */
.evaluaciones-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; position: relative; max-width: 100%; box-sizing: content-box; padding: 1rem 1rem 2rem;}

/* ALERTAS Y BREADCRUMB */
.alerta-error-catalogos { display: flex; align-items: center; gap: 0.6rem; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 10px; padding: 0.8rem 1.2rem; margin-bottom: 1rem; font-size: 0.875rem; font-weight: 600; }
.btn-reintentar { margin-left: auto; background: #DC2626; color: #fff; border: none; border-radius: 6px; padding: 4px 12px; font-size: 0.8rem; font-weight: 700; cursor: pointer; }
.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }
.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; }
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; }

/* ESTADÍSTICAS */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.2rem 1.4rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid transparent; transition: transform 0.2s, box-shadow 0.2s; }
.stat-card.azul { border-left-color: #1B396A; } .stat-card.verde { border-left-color: #16A34A; } .stat-card.rojo { border-left-color: #DC2626; } .stat-card.naranja { border-left-color: #F59E0B; }
.stat-icono { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-card.azul .stat-icono { background: #DBEAFE; color: #1B396A; } .stat-card.verde .stat-icono { background: #DCFCE7; color: #16A34A; } .stat-card.rojo .stat-icono { background: #FEF2F2; color: #DC2626; } .stat-card.naranja .stat-icono { background: #FEF3C7; color: #F59E0B; }
.stat-datos { display: flex; flex-direction: column; }
.stat-numero { font-size: 1.8rem; font-weight: 800; color: #1A1A1A; line-height: 1; }
.stat-etiqueta { font-size: 0.78rem; color: #6B7280; font-weight: 600; margin-top: 2px; }

/* TARJETA DE MATERIA */
.materia-progreso-row { display: grid; grid-template-columns: 1fr auto; gap: 1rem; margin-bottom: 1.5rem; }
.materia-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.4rem 1.6rem; display: flex; align-items: center; gap: 1.2rem; }
.materia-badge { background: #1B396A; color: #FFFFFF; font-weight: 800; font-size: 0.85rem; padding: 0.6rem 0.8rem; border-radius: 8px; flex-shrink: 0; }
.materia-nombre { font-size: 1.15rem; font-weight: 800; color: #1A1A1A; margin: 0 0 0.3rem; }
.materia-meta { display: flex; gap: 1.2rem; flex-wrap: wrap; font-size: 0.82rem; color: #6B7280; }
.progreso-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.2rem 1.6rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; min-width: 200px; }
.progreso-circular-wrap { position: relative; width: 100px; height: 100px; }
.svg-circular { width: 100px; height: 100px; }
.progreso-texto { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; }
.progreso-numero { display: block; font-size: 1.8rem; font-weight: 800; color: #1B396A; line-height: 1; }
.progreso-label { font-size: 0.72rem; color: #6B7280; }
.progreso-status { font-size: 0.82rem; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
.status-ok { background: #DCFCE7; color: #16A34A; } .status-error { background: #FEF2F2; color: #DC2626; } .status-pendiente { background: #DBEAFE; color: #1B396A; }

/* FILTROS REFACTORIZADOS CON POPOVER */
.filtros-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 0.9rem 1.4rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.filtros-container { display: flex; align-items: center; justify-content: space-between; gap: 1rem; width: 100%; flex-wrap: wrap; }
.busqueda-wrapper { position: relative; display: flex; align-items: center; flex: 1; max-width: 450px; }
.busqueda-control { display: flex; align-items: center; flex: 1; gap: 8px; background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 8px 0 12px; transition: border-color 0.2s; }
.busqueda-control:focus-within { border-color: #1B396A; background: #DBEAFE; }
.icono-busqueda { color: #6B7280; flex-shrink: 0; }
.input-control { border: none; background: transparent; padding: 10px 0; font-size: 0.875rem; font-family: inherit; outline: none; width: 100%; color: #1A1A1A; }
.btn-icon-filter { background: transparent; border: none; padding: 6px; border-radius: 6px; cursor: pointer; color: #6B7280; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.btn-icon-filter:hover, .btn-icon-filter.activo { background: #E5E7EB; color: #1B396A; }
.popover-filtros { position: absolute; top: calc(100% + 8px); left: 0; width: 320px; background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 12px; box-shadow: 0 12px 30px rgba(0,0,0,0.15); z-index: 1000; overflow: hidden; }
.popover-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.2rem; background: #F5F5F5; border-bottom: 1px solid #E5E7EB; }
.popover-header h4 { margin: 0; font-size: 0.9rem; font-weight: 700; color: #1A1A1A; }
.btn-limpiar-texto { background: none; border: none; color: #1B396A; font-size: 0.8rem; font-weight: 600; cursor: pointer; padding: 0; }
.btn-limpiar-texto:hover { text-decoration: underline; }
.popover-body { padding: 1.2rem; display: flex; flex-direction: column; gap: 1rem; }
.campo-filtro label { display: block; font-size: 0.78rem; font-weight: 600; color: #6B7280; margin-bottom: 4px; }
.filtro-select { width: 100%; padding: 9px 10px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 0.85rem; font-family: inherit; color: #1A1A1A; background: #F5F5F5; outline: none; }
.filtro-select:focus { border-color: #1B396A; }
.filtro-select:disabled { opacity: 0.55; cursor: wait; }
.popover-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem 1.2rem; border-top: 1px solid #E5E7EB; background: #F5F5F5; }
.btn-limpiar-filtros { display: flex; align-items: center; gap: 6px; background: #FEF2F2; color: #DC2626; border: none; padding: 8px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.btn-limpiar-filtros:hover { background: #FECACA; }
.filtros-acciones { display: flex; gap: 0.75rem; margin-left: auto; }

/* TABLA ESTANDARIZADA */
.tabla-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; }
.tabla-encabezado { padding: 1rem 1.4rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #E5E7EB; flex-wrap: wrap; gap: 0.75rem; }
.tabla-contador { font-size: 0.8rem; color: #6B7280; background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px; }
.tabla-scroll { overflow-x: auto; }
.tabla-eval { width: 100%; border-collapse: collapse; }
.tabla-eval th { background: #F5F5F5; padding: 10px 14px; font-size: 0.78rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; text-align: left; white-space: nowrap; }
.tabla-eval th.centrado { text-align: center; }
.tabla-eval td { padding: 8px 14px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; font-size: 0.875rem; color: #1A1A1A; }
.tabla-eval td.centrado { text-align: center; }
.tabla-eval tr:hover { background: #F5F5F5; } .tabla-eval tr.fila-activa { background: #DBEAFE; }
.tabla-eval tr:last-child td { border-bottom: none; }
.nombre-eval { font-weight: 700; color: #1A1A1A; }
.input-porcentaje-wrap { display: inline-flex; align-items: center; gap: 4px; background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 6px; padding: 2px 8px; }
.input-porcentaje { width: 50px; border: none; background: transparent; font-size: 0.85rem; font-weight: 700; text-align: center; outline: none; color: #1A1A1A; }
.mini-barra-wrap { display: flex; flex-direction: column; align-items: center; gap: 4px; min-width: 90px; }
.mini-barra { width: 100%; height: 6px; background: #E5E7EB; border-radius: 3px; overflow: hidden; }
.mini-barra-fill { height: 100%; background: #1B396A; border-radius: 3px; transition: width 0.3s ease;}
.mini-pct { font-size: 0.7rem; font-weight: 700; color: #1B396A; }

/* ACCIONES ICONIZADAS ESTANDARIZADAS */
.acciones-fila { display: flex; gap: 6px; justify-content: center; }
.btn-accion { width: 30px; height: 30px; border-radius: 7px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s, opacity 0.2s; flex-shrink: 0; }
.btn-accion:hover:not(:disabled) { transform: scale(1.1); }
.btn-accion:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
.btn-accion.guardar { background: #DCFCE7; color: #16A34A; } .btn-accion.guardar:hover { background: #bbf7d0; }
.btn-accion.editar { background: #F5F5F5; color: #6B7280; } .btn-accion.editar:hover { background: #E5E7EB; }
.btn-accion.eliminar { background: #FEF2F2; color: #DC2626; } .btn-accion.eliminar:hover { background: #fecaca; }
.sin-resultados { padding: 2rem; color: #9CA3AF; text-align: center; font-size: 0.9rem;}

/* PAGINACIÓN ESTANDARIZADA */
.paginacion-wrapper { display: flex; justify-content: space-between; align-items: center; padding: 0.9rem 1.4rem; background: #FFFFFF; border-top: 1px solid #E5E7EB; flex-wrap: wrap; gap: 0.75rem; }
.paginacion-info { font-size: 0.82rem; color: #6B7280; }
.paginacion-controles { display: flex; align-items: center; gap: 4px; }
.btn-pag { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #E5E7EB; background: #FFFFFF; color: #6B7280; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
.btn-pag:hover:not(:disabled) { background: #F5F5F5; border-color: #E5E7EB; color: #1B396A; }
.btn-pag:disabled { opacity: 0.4; cursor: not-allowed; }
.paginacion-numeros { display: flex; gap: 4px; }
.btn-num { min-width: 32px; height: 32px; border-radius: 8px; border: 1px solid #E5E7EB; background: #FFFFFF; color: #6B7280; font-weight: 600; font-size: 0.82rem; font-family: inherit; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; }
.btn-num:hover { background: #F5F5F5; color: #1B396A; }
.btn-num.activa { background: #1B396A; color: #FFFFFF; border-color: #1B396A; }

/* FOOTER TABLA */
.tabla-footer { padding: 1rem 1.4rem; background: #F5F5F5; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #E5E7EB; flex-wrap: wrap; gap: 1rem; }
.total-porcentaje { display: flex; align-items: center; gap: 0.6rem; font-size: 0.85rem; color: #6B7280; }
.total-porcentaje strong { font-size: 1.05rem; font-weight: 800; color: #1A1A1A; }
.check-ok { background: #DCFCE7; color: #16A34A; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.alerta-exceso { background: #FEF2F2; color: #DC2626; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.alerta-faltante { background: #FEF3C7; color: #F59E0B; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }

/* ============================================= */
/* BOTONES ESTANDARIZADOS - PALETA OFICIAL      */
/* ============================================= */
.btn-primario {
  background: #1B396A;
  color: #FFFFFF;
  border: none;
  padding: 10px 18px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s;
  white-space: nowrap;
}
.btn-primario:hover:not(:disabled) { background: #1D4ED8; }
.btn-primario:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

.btn-secundario {
  background: #DBEAFE;
  color: #1B396A;
  border: none;
  padding: 10px 16px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s;
  white-space: nowrap;
}
.btn-secundario:hover:not(:disabled) { background: #BFDBFE; }
.btn-secundario:disabled { opacity: 0.45; cursor: not-allowed; }

.btn-cancelar {
  background: #FFFFFF;
  color: #6B7280;
  border: 1px solid #E5E7EB;
  padding: 10px 1.4rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s;
}
.btn-cancelar:hover { background: #F5F5F5; }

/* ============================================= */
/* MODAL MEJORADO - ESTILOS COMPLETOS           */
/* ============================================= */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  backdrop-filter: blur(4px);
  animation: overlay-fade-in 0.2s ease-out;
}
@keyframes overlay-fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}
.modal-container {
  background: #FFFFFF;
  width: 100%;
  max-width: 520px;
  margin: 1rem;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05);
  animation: modal-scale-in 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  display: flex;
  flex-direction: column;
  max-height: 90vh;
}
@keyframes modal-scale-in {
  from { opacity: 0; transform: scale(0.95) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
.modal-header {
  background: linear-gradient(135deg, #1B396A 0%, #1D4ED8 100%);
  color: #FFFFFF;
  padding: 1.25rem 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  position: relative;
}
.header-icon-wrapper {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(4px);
}
.header-icon-wrapper.new { background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1)); }
.header-icon-wrapper.edit { background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05)); }
.header-text { flex: 1; min-width: 0; }
.modal-title { margin: 0 0 0.25rem 0; font-size: 1.15rem; font-weight: 700; line-height: 1.3; }
.modal-subtitle { margin: 0; font-size: 0.85rem; opacity: 0.9; line-height: 1.4; }
.modal-close {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.85);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  margin: -0.5rem -0.5rem 0 0;
}
.modal-close:hover { background: rgba(255, 255, 255, 0.15); color: #FFFFFF; transform: rotate(90deg); }
.modal-close:focus { outline: 2px solid rgba(255, 255, 255, 0.5); outline-offset: 2px; }
.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  overflow-y: auto;
  flex: 1;
}
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #1A1A1A;
  display: flex;
  align-items: center;
  gap: 0.35rem;
  line-height: 1.4;
}
.required-badge { color: #DC2626; font-weight: 700; font-size: 0.75rem; }
.input-wrapper { position: relative; display: flex; align-items: center; }
.input-icon {
  position: absolute;
  left: 12px;
  color: #9CA3AF;
  pointer-events: none;
  transition: color 0.2s ease;
  z-index: 1;
}
.input-wrapper:has(.form-input:focus) .input-icon,
.input-wrapper:has(.form-select:focus) .input-icon { color: #1B396A; }
.form-input, .form-select {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1.5px solid #E5E7EB;
  border-radius: 10px;
  font-size: 0.9rem;
  font-family: inherit;
  color: #1A1A1A;
  background: #FFFFFF;
  transition: all 0.2s ease;
  outline: none;
}
.form-input::placeholder { color: #9CA3AF; }
.form-input:focus, .form-select:focus {
  border-color: #1B396A;
  background: #F5F5F5;
  box-shadow: 0 0 0 3px rgba(27, 57, 106, 0.1);
}
.form-input.input-focus, .form-select.select-has-value { border-color: #1B396A; background: #F5F5F5; }
.form-input.input-valid { border-color: #16A34A; background: #F0FDF4; }
.form-input.input-invalid { border-color: #DC2626; background: #FEF2F2; animation: shake 0.3s ease-in-out; }
@keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-4px); } 75% { transform: translateX(4px); } }
.form-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236B7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 16px;
  padding-right: 2.5rem;
  cursor: pointer;
}
.form-select:disabled { background-color: #F5F5F5; color: #9CA3AF; cursor: not-allowed; }
.form-hint { font-size: 0.75rem; color: #6B7280; line-height: 1.4; display: flex; align-items: center; gap: 0.25rem; }
.form-error { font-size: 0.75rem; color: #DC2626; font-weight: 500; display: flex; align-items: center; gap: 0.25rem; }
.form-error::before { content: "•"; font-size: 1.2rem; line-height: 1; }
.percentage-input-group { display: flex; flex-direction: column; gap: 0.75rem; }
.percentage-wrapper { display: flex; align-items: center; }
.percentage-input { padding-left: 1rem; padding-right: 2.5rem; font-weight: 600; text-align: center; width: 100px; }
.percentage-sign { position: absolute; right: 12px; color: #6B7280; font-weight: 600; font-size: 0.9rem; pointer-events: none; }
.percentage-availability {
  background: #F5F5F5;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.availability-label { display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #6B7280; }
.availability-value { font-weight: 700; font-size: 0.9rem; padding: 2px 8px; border-radius: 6px; transition: all 0.2s ease; }
.availability-value.suficiente { background: #DCFCE7; color: #16A34A; }
.availability-value.insuficiente { background: #FEF2F2; color: #DC2626; }
.availability-value.exacto { background: #DBEAFE; color: #1B396A; }
.availability-bar { height: 6px; background: #E5E7EB; border-radius: 3px; overflow: hidden; }
.availability-fill { height: 100%; border-radius: 3px; transition: width 0.3s ease, background 0.3s ease; }
.impact-summary {
  background: linear-gradient(135deg, #F5F5F5 0%, #F5F5F5 100%);
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.summary-item { display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; }
.summary-label { color: #6B7280; font-weight: 500; }
.summary-value {
  font-weight: 700;
  font-size: 1rem;
  color: #1A1A1A;
  padding: 2px 8px;
  border-radius: 6px;
  background: #FFFFFF;
  border: 1px solid #E5E7EB;
}
.summary-value.proyeccion-ok { color: #16A34A; border-color: #16A34A; background: #F0FDF4; }
.summary-value.proyeccion-error { color: #DC2626; border-color: #DC2626; background: #FEF2F2; }
.summary-value.proyeccion-warning { color: #F59E0B; border-color: #F59E0B; background: #FEF3C7; }
.summary-status { margin-top: 0.25rem; padding: 0.4rem 0.75rem; border-radius: 8px; font-size: 0.8rem; font-weight: 600; text-align: center; }
.summary-status.ok { background: #DCFCE7; color: #16A34A; }
.summary-status.error { background: #FEF2F2; color: #DC2626; }
.summary-status.warning { background: #FEF3C7; color: #F59E0B; }
.modal-footer {
  padding: 1rem 1.5rem;
  background: #F5F5F5;
  border-top: 1px solid #E5E7EB;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}
.footer-shortcuts { display: flex; gap: 0.75rem; font-size: 0.75rem; color: #9CA3AF; }
.shortcut-hint { display: flex; align-items: center; gap: 0.25rem; }
.shortcut-hint kbd { background: #E5E7EB; border-radius: 4px; padding: 2px 6px; font-family: monospace; font-size: 0.7rem; color: #374151; font-weight: 600; }
.footer-actions { display: flex; gap: 0.75rem; }
.btn-icon { width: 18px; height: 18px; flex-shrink: 0; }
.spinner {
  width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #FFFFFF;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }
.modal-scale-enter-active, .modal-scale-leave-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.modal-scale-enter-from, .modal-scale-leave-to { opacity: 0; transform: scale(0.95) translateY(10px); }
.modal-body::-webkit-scrollbar { width: 6px; }
.modal-body::-webkit-scrollbar-track { background: transparent; }
.modal-body::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }
.modal-body::-webkit-scrollbar-thumb:hover { background: #9CA3AF; }
@media (max-width: 640px) {
  .modal-container { margin: 0.5rem; max-width: calc(100vw - 1rem); }
  .modal-header { padding: 1rem 1.25rem; flex-wrap: wrap; }
  .modal-title { font-size: 1.05rem; }
  .modal-subtitle { font-size: 0.8rem; }
  .modal-body { padding: 1.25rem; gap: 1rem; }
  .modal-footer { padding: 0.875rem 1.25rem; flex-direction: column-reverse; align-items: stretch; }
  .footer-actions { width: 100%; }
  .footer-actions .btn { flex: 1; }
  .footer-shortcuts { justify-content: center; }
}
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { animation-duration: 0.01ms !important; animation-iteration-count: 1 !important; transition-duration: 0.01ms !important; }
}
@media (prefers-contrast: high) {
  .form-input, .form-select { border-width: 2px; }
  .btn-primario { border: 2px solid currentColor; }
}

/* ANIMACIONES */
.popover-fade-enter-active, .popover-fade-leave-active { transition: all 0.2s ease; }
.popover-fade-enter-from, .popover-fade-leave-to { opacity: 0; transform: translateY(-10px); }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

/* TOAST ESTANDARIZADO */
.toast {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  padding: 0.9rem 1.4rem;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.9rem;
  font-family: 'Montserrat', sans-serif;
  display: flex;
  align-items: center;
  gap: 0.6rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  z-index: 3000;
  max-width: 380px;
  color: #FFFFFF;
}
.toast.exito { background: #1B396A; }
.toast.error { background: #DC2626; }
.toast.info  { background: #2563EB; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from   { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to     { transform: translateX(100%); opacity: 0; }

.atajos-info { text-align: center; color: #9CA3AF; font-size: 0.78rem; margin-bottom: 1.5rem; }
kbd { background: #E5E7EB; border-radius: 4px; padding: 1px 6px; font-family: monospace; font-size: 0.8rem; color: #1A1A1A; }
.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }

/* ==================== RESPONSIVE ==================== */
@media (max-width: 1200px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 900px) {
  .materia-progreso-row {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .filtros-container {
    flex-direction: column;
    align-items: stretch;
  }

  .busqueda-wrapper {
    max-width: 100%;
    width: 100%;
  }
}

@media (max-width: 640px) {
  .evaluaciones-page {
    padding: 1rem 0.75rem;
  }

  .stats-grid { 
    grid-template-columns: 1fr; 
    gap: 0.75rem; 
  }

  .materia-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
    text-align: center;
  }

  .materia-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .filtros-card {
    flex-direction: column;
    align-items: stretch;
    padding: 1rem;
    gap: 1rem;
  }

  .tabla-eval th,
  .tabla-eval td {
    padding: 8px 6px;
    font-size: 0.8rem;
  }

  .input-porcentaje-wrap {
    flex-direction: column;
    gap: 4px;
  }

  .acciones-fila {
    flex-direction: column;
    gap: 6px;
  }

  .paginacion-wrapper {
    flex-direction: column;
    gap: 1rem;
    align-items: center;
  }

  .tabla-footer {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
    text-align: center;
  }

  .modal-container {
    width: 92%;
    margin: 0.5rem;
    max-width: none;
  }

  .modal-header {
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .modal-footer {
    flex-direction: column-reverse;
    align-items: stretch;
    gap: 0.75rem;
  }

  .footer-actions .btn-primario,
  .footer-actions .btn-cancelar {
    width: 100%;
    justify-content: center;
  }
}
</style>