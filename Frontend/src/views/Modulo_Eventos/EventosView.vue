<!-- ============================================= -->
<!-- src/views/EventosView.vue                    -->
<!-- Módulo: Eventos — Vista principal (Corregido)-->
<!-- ============================================= -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="eventos-page">
      <div class="barra-carga" :class="{ activa: cargando }"><div class="barra-progreso"></div></div>
      
      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-link">Inicio</router-link>
        <span class="sep">›</span>
        <span class="activo">Eventos</span>
      </div>

      <div class="encabezado-seccion">
        <div>
          <h1 class="titulo-pagina">Eventos</h1>
          <p class="subtitulo">Gestión de eventos institucionales y control de participación</p>
        </div>
        <button @click="abrirModalEvento()" class="btn-primario">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nuevo Evento
        </button>
      </div>

      <!-- Eventos Próximos -->
      <div class="seccion-titulo-wrapper">
        <h2 class="seccion-titulo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Eventos Próximos
        </h2>
        <span class="seccion-contador">{{ eventosProximos.length }} evento(s)</span>
      </div>

      <div v-if="eventosProximos.length > 0" class="eventos-proximos-grid">
        <div v-for="evento in eventosProximos" :key="evento.id_evento" class="evento-card">
          <div class="evento-card-izq">
            <div class="stat-icono-wrapper" :style="{ background: colorFondoTipo(evento.tipo_evento?.nombre_tipo) }">
              <svg viewBox="0 0 24 24" fill="none" :stroke="colorTipo(evento.tipo_evento?.nombre_tipo)" stroke-width="2" width="24" height="24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
          </div>
          <div class="evento-card-cuerpo">
            <div class="evento-card-superior">
              <span class="evento-nombre">{{ evento.nombre_evento }}</span>
              <span class="badge-tipo" :style="estiloBadgeTipo(evento.tipo_evento?.nombre_tipo)">{{ evento.tipo_evento?.nombre_tipo || 'General' }}</span>
            </div>
            <div class="evento-card-meta">
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                {{ formatearFecha(evento.fecha) }}
              </span>
              <span v-if="evento.descripcion" class="meta-item descripcion-resumida">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                {{ evento.descripcion.substring(0, 60) }}{{ evento.descripcion.length > 60 ? '...' : '' }}
              </span>
            </div>
          </div>
          <div class="evento-card-acciones">
            <button @click="router.push(`/eventos/${evento.id_evento}/participantes`)" class="btn-secundario">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              Participantes
            </button>
          </div>
        </div>
      </div>
      <div v-else class="estado-vacio">
        <svg viewBox="0 0 24 24" fill="none" stroke="#D6D6D6" stroke-width="1.5" width="56" height="56"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
        <p class="vacio-titulo">Sin eventos próximos</p>
        <p class="vacio-subtitulo">Crea un nuevo evento para que aparezca aquí</p>
      </div>

      <!-- Historial y Filtros -->
      <div class="seccion-titulo-wrapper" style="margin-top: 2.5rem;">
        <h2 class="seccion-titulo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Historial de Eventos
        </h2>
      </div>

      <div class="filtros-card">
        <div class="busqueda-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-busqueda"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input v-model="busquedaNombre" type="text" placeholder="Buscar por nombre..." class="input-busqueda" @input="reiniciarPagina()" />
          <button v-if="busquedaNombre" @click="busquedaNombre = ''; reiniciarPagina()" class="btn-limpiar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <button @click="mostrarFiltrosAvanzados = true" class="btn-icono" title="Filtros Avanzados">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          Filtros
        </button>
      </div>

      <!-- Modal Filtros Avanzados -->
      <transition name="modal-fade">
        <div v-if="mostrarFiltrosAvanzados" class="modal-fondo" @click.self="mostrarFiltrosAvanzados = false">
          <div class="modal-caja">
            <div class="modal-cabecera"><h3>Filtros Avanzados</h3><button @click="mostrarFiltrosAvanzados = false" class="btn-cerrar-modal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button></div>
            <div class="modal-cuerpo filtros-grid">
              <div class="campo-form">
                <label class="campo-label">Tipo de Evento</label>
                <select v-model="filtrosAvanzados.tipo" class="campo-input" @change="reiniciarPagina()">
                  <option value="">Todos</option>
                  <option v-for="t in tiposEvento" :key="t.id_tipo_evento" :value="t.id_tipo_evento">{{ t.nombre_tipo }}</option>
                </select>
              </div>
              <div class="campo-form">
                <label class="campo-label">Estatus</label>
                <select v-model="filtrosAvanzados.estatus" class="campo-input" @change="reiniciarPagina()">
                  <option value="">Todos</option>
                  <option value="Próximo">Próximo</option>
                  <option value="Finalizado">Finalizado</option>
                  <option value="Cancelado">Cancelado</option>
                </select>
              </div>
              <div class="campo-form">
                <label class="campo-label">Fecha Inicio</label>
                <input v-model="filtrosAvanzados.fechaInicio" type="date" class="campo-input" @change="reiniciarPagina()" />
              </div>
              <div class="campo-form">
                <label class="campo-label">Fecha Fin</label>
                <input v-model="filtrosAvanzados.fechaFin" type="date" class="campo-input" @change="reiniciarPagina()" />
              </div>
            </div>
            <div class="modal-pie">
              <button @click="limpiarFiltros()" class="btn-cancelar">Limpiar</button>
              <button @click="mostrarFiltrosAvanzados = false" class="btn-guardar">Aplicar</button>
            </div>
          </div>
        </div>
      </transition>

      <!-- Tabla Historial -->
      <div class="tabla-card">
        <div class="tabla-encabezado">
          <span class="tabla-contador">{{ eventosFiltrados.length }} registro(s) · Página {{ paginaActual }} de {{ totalPaginas }}</span>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-principal tabla-densa">
            <thead>
              <tr>
                <th>Nombre del Evento</th>
                <th>Tipo</th>
                <th>Fecha</th>
                <th>Descripción</th>
                <th class="centrado">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="evento in eventosPaginados" :key="evento.id_evento">
                <td><span class="texto-principal">{{ evento.nombre_evento }}</span></td>
                <td><span class="badge-estado" :style="estiloBadgeTipo(evento.tipo_evento?.nombre_tipo)">{{ evento.tipo_evento?.nombre_tipo || 'General' }}</span></td>
                <td class="texto-secundario">{{ formatearFecha(evento.fecha) }}</td>
                <td class="texto-secundario texto-corto">{{ evento.descripcion || '—' }}</td>
                <td class="centrado">
                  <div class="acciones-fila">
                    <button @click="router.push(`/eventos/${evento.id_evento}/participantes`)" class="btn-accion ver" title="Ver participantes">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button @click="abrirModalEvento(evento)" class="btn-accion editar" title="Editar evento">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </button>
                    <button @click="eliminarEvento(evento)" class="btn-accion eliminar" title="Eliminar evento">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="eventosPaginados.length === 0">
                <td colspan="5" class="sin-resultados">
                  <svg viewBox="0 0 24 24" fill="none" stroke="#D6D6D6" stroke-width="1.5" width="40" height="40"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  <p>No se encontraron eventos con los filtros aplicados</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Paginación -->
        <div v-if="totalPaginas > 1" class="paginacion-wrap">
          <button @click="cambiarPagina(paginaActual - 1)" :disabled="paginaActual === 1" class="btn-pag">Anterior</button>
          <div class="pag-numeros">
            <button v-for="p in totalPaginas" :key="p" @click="cambiarPagina(p)" class="btn-pag-num" :class="{ activa: paginaActual === p }">{{ p }}</button>
          </div>
          <button @click="cambiarPagina(paginaActual + 1)" :disabled="paginaActual === totalPaginas" class="btn-pag">Siguiente</button>
        </div>
      </div>

      <!-- Modal Crear / Editar Evento -->
      <transition name="modal-fade">
        <div v-if="mostrarModalEvento" class="modal-fondo" @click.self="cerrarModalEvento()">
          <div class="modal-caja modal-ancho">
            <div class="modal-cabecera">
              <h3>{{ modoEdicion ? 'Editar Evento' : 'Nuevo Evento' }}</h3>
              <button @click="cerrarModalEvento()" class="btn-cerrar-modal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
            <form @submit.prevent="guardarEvento" novalidate>
              <div class="modal-cuerpo campos-grid-modal">
                <div class="campo-form campo-ancho">
                  <label class="campo-label">Nombre del Evento <span class="requerido">*</span></label>
                  <input v-model="form.nombre_evento" type="text" placeholder="Ej: Semana de Ingeniería 2026" class="campo-input" :class="{ 'campo-error': errores.nombre_evento }" @input="validarCampo('nombre_evento')" />
                  <span v-if="errores.nombre_evento" class="mensaje-error">{{ errores.nombre_evento }}</span>
                </div>
                <div class="campo-form">
                  <label class="campo-label">Tipo de Evento <span class="requerido">*</span></label>
                  <select v-model="form.id_tipo_evento" class="campo-input" :class="{ 'campo-error': errores.id_tipo_evento }" @change="validarCampo('id_tipo_evento')">
                    <option value="">Selecciona un tipo</option>
                    <option v-for="t in tiposEvento" :key="t.id_tipo_evento" :value="t.id_tipo_evento">{{ t.nombre_tipo }}</option>
                  </select>
                  <span v-if="errores.id_tipo_evento" class="mensaje-error">{{ errores.id_tipo_evento }}</span>
                </div>
                <div class="campo-form">
                  <label class="campo-label">Fecha del Evento <span class="requerido">*</span></label>
                  <input v-model="form.fecha" type="date" :min="fechaMinima" class="campo-input" :class="{ 'campo-error': errores.fecha }" @change="validarCampo('fecha')" />
                  <span v-if="errores.fecha" class="mensaje-error">{{ errores.fecha }}</span>
                </div>
                <div class="campo-form campo-ancho">
                  <label class="campo-label">Descripción</label>
                  <textarea v-model="form.descripcion" rows="3" placeholder="Describe brevemente el objetivo o contenido del evento..." class="campo-input campo-textarea"></textarea>
                </div>
              </div>
              <div class="modal-pie">
                <button type="button" @click="cerrarModalEvento()" class="btn-cancelar">Cancelar</button>
                <button type="submit" class="btn-guardar" :disabled="cargandoForm">
                  <span v-if="cargandoForm" class="spinner"></span>
                  {{ cargandoForm ? 'Guardando...' : (modoEdicion ? 'Actualizar Evento' : 'Guardar Evento') }}
                </button>
              </div>
            </form>
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

      <footer class="pie-pagina">© 2026 Tecnológico Nacional de México · Todos los derechos reservados</footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()
const API = `${import.meta.env.VITE_API_URL}/api`

const cargando      = ref(false)
const cargandoForm  = ref(false)
const busquedaNombre = ref('')
const tiposEvento   = ref([])
const eventos       = ref([])

// Estado de Paginación
const paginaActual = ref(1)
const registrosPorPagina = 10

// Estado de Filtros Avanzados
const mostrarFiltrosAvanzados = ref(false)
const filtrosAvanzados = ref({
  tipo: '',
  estatus: '', // Próximo, Finalizado, Cancelado
  fechaInicio: '',
  fechaFin: ''
})

// Estado de Modal Evento
const mostrarModalEvento = ref(false)
const modoEdicion = ref(false)
const eventoEditandoId = ref(null)
const form = ref({ nombre_evento: '', id_tipo_evento: '', fecha: '', descripcion: '' })
const errores = ref({ nombre_evento: '', id_tipo_evento: '', fecha: '' })
const fechaMinima = computed(() => new Date().toISOString().split('T')[0])

// Toast
const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerNotif = null
const mostrarNotificacion = (m, t = 'exito') => {
  clearTimeout(timerNotif)
  toast.value = { visible: true, mensaje: m, tipo: t }
  timerNotif = setTimeout(() => toast.value.visible = false, 3500)
}

// ── Carga de datos ────────────────────────────────────────────
const cargarTipos = async () => {
  try {
    const res = await fetch(`${API}/tipos-evento`)
    if (!res.ok) throw new Error()
    tiposEvento.value = await res.json()
  } catch { tiposEvento.value = [] }
}

const cargarEventos = async () => {
  cargando.value = true
  try {
    const res = await fetch(`${API}/eventos`)
    if (!res.ok) throw new Error()
    eventos.value = await res.json()
  } catch (err) { console.error(err) }
  finally { cargando.value = false }
}

onMounted(() => { cargarTipos(); cargarEventos() })

// ── Lógica de Filtrado y Paginación ───────────────────────────
const hoy = new Date().toISOString().split('T')[0]

const eventosFiltrados = computed(() => {
  return eventos.value.filter(e => {
    const matchNombre = !busquedaNombre.value || e.nombre_evento?.toLowerCase().includes(busquedaNombre.value.toLowerCase())
    const matchTipo = !filtrosAvanzados.value.tipo || String(e.tipo_evento_id) === String(filtrosAvanzados.value.tipo)
    
    const esProximo = e.fecha >= hoy
    const esFinalizado = e.fecha < hoy
    let matchEstatus = true
    if (filtrosAvanzados.value.estatus === 'Próximo') matchEstatus = esProximo
    if (filtrosAvanzados.value.estatus === 'Finalizado') matchEstatus = esFinalizado
    // Cancelado se asume manejado por backend o campo adicional; aquí filtramos por fechas si aplica
    
    let matchFecha = true
    if (filtrosAvanzados.value.fechaInicio && e.fecha < filtrosAvanzados.value.fechaInicio) matchFecha = false
    if (filtrosAvanzados.value.fechaFin && e.fecha > filtrosAvanzados.value.fechaFin) matchFecha = false

    return matchNombre && matchTipo && matchEstatus && matchFecha
  })
})

const eventosProximos = computed(() => eventosFiltrados.value.filter(e => e.fecha >= hoy))
const eventosPaginados = computed(() => {
  const inicio = (paginaActual.value - 1) * registrosPorPagina
  const fin = inicio + registrosPorPagina
  return eventosFiltrados.value.slice(inicio, fin)
})
const totalPaginas = computed(() => Math.ceil(eventosFiltrados.value.length / registrosPorPagina))

const reiniciarPagina = () => { paginaActual.value = 1 }
const cambiarPagina = (p) => {
  if (p >= 1 && p <= totalPaginas.value) paginaActual.value = p
}
const limpiarFiltros = () => {
  filtrosAvanzados.value = { tipo: '', estatus: '', fechaInicio: '', fechaFin: '' }
  reiniciarPagina()
}

// ── Modal y Formulario ────────────────────────────────────────
const abrirModalEvento = (evento = null) => {
  modoEdicion.value = !!evento
  eventoEditandoId.value = evento?.id_evento || null
  if (evento) {
    form.value = {
      nombre_evento: evento.nombre_evento,
      id_tipo_evento: evento.tipo_evento_id,
      fecha: evento.fecha,
      descripcion: evento.descripcion || ''
    }
  } else {
    form.value = { nombre_evento: '', id_tipo_evento: '', fecha: '', descripcion: '' }
  }
  errores.value = { nombre_evento: '', id_tipo_evento: '', fecha: '' }
  mostrarModalEvento.value = true
}

const cerrarModalEvento = () => { mostrarModalEvento.value = false }

const validarCampo = (c) => {
  errores.value[c] = ''
  if (c === 'nombre_evento' && !form.value.nombre_evento.trim()) errores.value.nombre_evento = 'Requerido'
  if (c === 'id_tipo_evento' && !form.value.id_tipo_evento) errores.value.id_tipo_evento = 'Selecciona un tipo'
  if (c === 'fecha') {
    if (!form.value.fecha) errores.value.fecha = 'Requerida'
    else if (form.value.fecha < fechaMinima.value && !modoEdicion.value) errores.value.fecha = 'La fecha no puede ser pasada'
  }
}

const validarTodo = () => {
  ['nombre_evento', 'id_tipo_evento', 'fecha'].forEach(validarCampo)
  return !Object.values(errores.value).some(Boolean)
}

const guardarEvento = async () => {
  if (!validarTodo()) return mostrarNotificacion('Revisa los campos marcados', 'error')
  cargandoForm.value = true
  try {
    const url = modoEdicion.value ? `${API}/eventos/${eventoEditandoId.value}` : `${API}/eventos`
    const method = modoEdicion.value ? 'PUT' : 'POST'
    const payload = {
      nombre: form.value.nombre_evento.trim(),
      tipo_evento_id: Number(form.value.id_tipo_evento),
      fecha: form.value.fecha,
      descripcion: form.value.descripcion.trim() || null
    }
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    })
    if (!res.ok) throw new Error((await res.json()).message || 'Error del servidor')
    mostrarNotificacion(modoEdicion.value ? 'Evento actualizado' : 'Evento creado')
    cerrarModalEvento()
    await cargarEventos() // Refresca lista y paginación
  } catch (e) {
    mostrarNotificacion(e.message, 'error')
  } finally {
    cargandoForm.value = false
  }
}

const eliminarEvento = async (e) => {
  if (!confirm(`¿Eliminar el evento "${e.nombre_evento}"?`)) return
  cargando.value = true
  try {
    const res = await fetch(`${API}/eventos/${e.id_evento}`, { method: 'DELETE' })
    if (!res.ok) throw new Error('No se pudo eliminar')
    mostrarNotificacion('Evento eliminado correctamente')
    await cargarEventos()
  } catch (err) { mostrarNotificacion(err.message, 'error') }
  finally { cargando.value = false }
}

// ── Helpers ───────────────────────────────────────────────────
const formatearFecha = (f) => {
  if (!f) return '—'
  const [a, m, d] = f.split('-')
  const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre']
  return `${parseInt(d)} de ${meses[parseInt(m) - 1]} de ${a}`
}
const colorTipo      = (t) => ({'Académico':'#1B396A','Cultural':'#F59E0B','Deportivo':'#16A34A','Institucional':'#2563EB'}[t] || '#6B7280')
const colorFondoTipo = (t) => ({'Académico':'#DBEAFE','Cultural':'#FEF3C7','Deportivo':'#DCFCE7','Institucional':'#EDE9FE'}[t] || '#F3F4F6')
const estiloBadgeTipo = (t) => ({ background: colorFondoTipo(t), color: colorTipo(t) })
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.eventos-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; }
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #1B396A, #1D4ED8, #1B396A); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

.breadcrumb { color: #6B7280; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E5E7EB; }
.breadcrumb .activo { color: #1B396A; font-weight: 600; }
.breadcrumb-link { color: #6B7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1B396A; }

.encabezado-seccion { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.titulo-pagina { color: #1A1A1A; font-size: 1.9rem; font-weight: 800; margin: 0 0 0.25rem; }
.subtitulo { color: #6B7280; font-size: 0.9rem; margin: 0; }
.btn-primario { background: #1B396A; color: #FFFFFF; border: none; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-primario:hover { background: #1D4ED8; }

.seccion-titulo-wrapper { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
.seccion-titulo { display: flex; align-items: center; gap: 8px; font-size: 1rem; font-weight: 700; color: #1A1A1A; margin: 0; }
.seccion-contador { font-size: 0.8rem; color: #6B7280; background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px; }

.eventos-proximos-grid { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1rem; }
.evento-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 1.4rem 1.6rem; display: flex; align-items: center; gap: 1.4rem; transition: box-shadow 0.2s, transform 0.2s; }
.evento-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.09); transform: translateY(-1px); }
.evento-card-izq { flex-shrink: 0; }
.stat-icono-wrapper { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.evento-card-cuerpo { flex: 1; min-width: 0; }
.evento-card-superior { display: flex; align-items: center; gap: 10px; margin-bottom: 0.6rem; flex-wrap: wrap; }
.evento-nombre { font-size: 1rem; font-weight: 700; color: #1A1A1A; }
.badge-tipo { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.evento-card-meta { display: flex; align-items: center; gap: 1.2rem; flex-wrap: wrap; }
.meta-item { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: #6B7280; }
.descripcion-resumida { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.evento-card-acciones { flex-shrink: 0; }
.btn-secundario { background: #DBEAFE; color: #1B396A; border: none; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 0.82rem; display: flex; align-items: center; gap: 5px; cursor: pointer; font-family: inherit; transition: background 0.2s; white-space: nowrap; }
.btn-secundario:hover { background: #bfdbfe; }

.estado-vacio { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; padding: 3rem; text-align: center; margin-bottom: 1rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }
.vacio-titulo { font-size: 0.95rem; font-weight: 700; color: #6B7280; margin: 0; }
.vacio-subtitulo { font-size: 0.82rem; color: #9CA3AF; margin: 0; }

.filtros-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 0.9rem 1.4rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
.busqueda-wrap { display: flex; align-items: center; gap: 8px; background: #F5F5F5; border: 1px solid #E5E7EB; border-radius: 8px; padding: 0 12px; flex: 1; min-width: 220px; }
.busqueda-wrap:focus-within { border-color: #1B396A; background: #DBEAFE; }
.icono-busqueda { color: #6B7280; flex-shrink: 0; }
.input-busqueda { border: none; background: transparent; padding: 9px 0; font-size: 0.875rem; font-family: inherit; outline: none; flex: 1; color: #1A1A1A; }
.input-busqueda::placeholder { color: #9CA3AF; }
.btn-limpiar { background: none; border: none; color: #6B7280; cursor: pointer; padding: 2px; display: flex; align-items: center; }
.btn-icono { background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB; padding: 9px 14px; border-radius: 8px; font-weight: 600; font-size: 0.82rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: all 0.2s; white-space: nowrap; }
.btn-icono:hover { background: #F5F5F5; color: #1B396A; border-color: #1B396A; }

/* Modal Filtros y Formulario */
.modal-fondo { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(3px); }
.modal-caja { background: #FFFFFF; width: 480px; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.25); }
.modal-caja.modal-ancho { width: 600px; }
.modal-cabecera { background: #1B396A; color: #FFFFFF; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; }
.modal-cabecera h3 { margin: 0; font-size: 1.1rem; font-weight: 800; }
.btn-cerrar-modal { background: none; border: none; color: rgba(255,255,255,0.8); cursor: pointer; display: flex; align-items: center; transition: color 0.2s; }
.btn-cerrar-modal:hover { color: #FFFFFF; }
.modal-cuerpo { padding: 1.6rem; display: flex; flex-direction: column; gap: 1rem; }
.filtros-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.modal-pie { padding: 1rem 1.6rem; background: #F5F5F5; border-top: 1px solid #E5E7EB; display: flex; justify-content: flex-end; gap: 0.75rem; }
.btn-cancelar { background: #FFFFFF; color: #6B7280; border: 1px solid #E5E7EB; padding: 9px 1.2rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; font-family: inherit; }
.btn-guardar { background: #1B396A; color: #FFFFFF; border: none; padding: 9px 1.4rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-family: inherit; transition: background 0.2s; }
.btn-guardar:hover:not(:disabled) { background: #1D4ED8; }
.btn-guardar:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }
.spinner { width: 14px; height: 14px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.3); border-top-color: #FFFFFF; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* Campos de Formulario */
.campos-grid-modal { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
.campo-form { display: flex; flex-direction: column; gap: 6px; }
.campo-ancho { grid-column: 1 / -1; }
.campo-label { font-size: 0.82rem; font-weight: 700; color: #1A1A1A; }
.requerido { color: #DC2626; }
.campo-input { padding: 10px 14px; border: 1.5px solid #E5E7EB; border-radius: 8px; font-size: 0.875rem; font-family: inherit; color: #1A1A1A; outline: none; background: #FFFFFF; transition: border-color 0.2s; }
.campo-input:focus { border-color: #1B396A; background: #DBEAFE; }
.campo-input.campo-error { border-color: #DC2626; }
.campo-textarea { resize: vertical; min-height: 80px; }
.mensaje-error { font-size: 0.78rem; color: #DC2626; font-weight: 500; }

/* Tabla Densa */
.tabla-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E5E7EB; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 1.5rem; }
.tabla-encabezado { padding: 0.8rem 1.4rem; border-bottom: 1px solid #E5E7EB; display: flex; align-items: center; justify-content: flex-end; }
.tabla-contador { font-size: 0.8rem; color: #6B7280; background: #F5F5F5; border: 1px solid #E5E7EB; padding: 4px 10px; border-radius: 20px; }
.tabla-scroll { overflow-x: auto; }
.tabla-principal { width: 100%; border-collapse: collapse; }
.tabla-principal th { background: #F5F5F5; padding: 8px 12px; font-size: 0.75rem; font-weight: 700; color: #6B7280; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E5E7EB; text-align: left; }
.tabla-principal th.centrado { text-align: center; }
.tabla-principal td { padding: 6px 12px; border-bottom: 1px solid #E5E7EB; vertical-align: middle; }
.tabla-principal td.centrado { text-align: center; }
.tabla-principal tr:last-child td { border-bottom: none; }
.tabla-principal tr:hover { background: #F9FAFB; }
.texto-principal { font-weight: 600; color: #1A1A1A; font-size: 0.85rem; }
.texto-secundario { color: #6B7280; font-size: 0.82rem; }
.texto-corto { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.badge-estado { font-size: 0.7rem; font-weight: 700; padding: 2px 8px; border-radius: 20px; }

/* Acciones solo iconos */
.acciones-fila { display: flex; gap: 4px; justify-content: center; align-items: center; }
.btn-accion { width: 28px; height: 28px; border-radius: 6px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s, background 0.15s; }
.btn-accion:hover { transform: scale(1.1); }
.btn-accion.ver { background: #DBEAFE; color: #1B396A; }
.btn-accion.ver:hover { background: #bfdbfe; }
.btn-accion.editar { background: #F3F4F6; color: #4B5563; }
.btn-accion.editar:hover { background: #E5E7EB; }
.btn-accion.eliminar { background: #FEE2E2; color: #DC2626; }
.btn-accion.eliminar:hover { background: #FECACA; }

.sin-resultados { padding: 2.5rem; text-align: center; color: #9CA3AF; font-size: 0.85rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.sin-resultados p { margin: 0; }

/* Paginación */
.paginacion-wrap { padding: 0.8rem 1.4rem; border-top: 1px solid #E5E7EB; display: flex; align-items: center; justify-content: space-between; background: #F9FAFB; }
.btn-pag { padding: 6px 12px; border: 1px solid #E5E7EB; background: #FFFFFF; border-radius: 6px; font-size: 0.8rem; cursor: pointer; color: #4B5563; }
.btn-pag:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-pag:hover:not(:disabled) { background: #F3F4F6; }
.pag-numeros { display: flex; gap: 4px; }
.btn-pag-num { width: 28px; height: 28px; border-radius: 6px; border: 1px solid #E5E7EB; background: #FFFFFF; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.btn-pag-num.activa { background: #1B396A; color: #FFFFFF; border-color: #1B396A; }
.btn-pag-num:hover:not(.activa) { background: #F3F4F6; }

/* Toast */
.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; }
.toast.exito { background: #1B396A; color: #FFFFFF; }
.toast.error { background: #DC2626; color: #FFFFFF; }
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to { transform: translateX(100%); opacity: 0; }

.pie-pagina { text-align: center; color: #9CA3AF; font-size: 0.82rem; padding-top: 2rem; }
@media (max-width: 640px) { 
  .filtros-grid, .campos-grid-modal { grid-template-columns: 1fr; } 
  .paginacion-wrap { flex-direction: column; gap: 0.5rem; }
}
</style>