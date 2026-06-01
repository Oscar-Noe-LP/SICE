<!-- ============================================= -->
<!-- src/views/ParticipantesEventoView.vue        -->
<!-- Módulo: Eventos — Participantes del evento   -->
<!-- ============================================= -->
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="participantes-page">
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>
      
      <div class="breadcrumb">
        <router-link to="/dashboard" class="breadcrumb-link">INICIO</router-link>
        <span class="sep">›</span>
        <router-link to="/eventos" class="breadcrumb-link">EVENTOS</router-link>
        <span class="sep">›</span>
        <span class="activo">PARTICIPANTES</span>
      </div>
      
      <div class="evento-resumen-card">
        <div class="resumen-info">
          <div class="resumen-icono-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="#0B2545" stroke-width="2" width="28" height="28">
              <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </div>
          <div class="resumen-datos">
            <!-- CORRECCIÓN: fallback nombre_evento ?? nombre para compatibilidad con backend -->
            <h1 class="resumen-nombre">{{ evento.nombre_evento ?? evento.nombre }}</h1>
            <div class="resumen-meta">
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                  <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/>
                  <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                </svg>
                {{ formatearFecha(evento.fecha) }}
              </span>
              <span v-if="evento.descripcion" class="meta-item descripcion-truncada">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                {{ evento.descripcion.substring(0, 80) }}{{ evento.descripcion.length > 80 ? '...' : '' }}
              </span>
            </div>
          </div>
        </div>
        <!-- CORRECCIÓN: evento.cupo_maximo (campo real del backend, antes era evento.cupo — incorrecto) -->
        <div v-if="evento.cupo_maximo" class="resumen-cupo">
          <div class="cupo-item">
            <span class="cupo-label">CUPO MÁXIMO</span>
            <span class="cupo-valor">{{ evento.cupo_maximo }}</span>
          </div>
          <div class="cupo-item">
            <span class="cupo-label">INSCRITOS</span>
            <span class="cupo-valor">{{ participantes.length }}</span>
          </div>
          <div class="cupo-item">
            <span class="cupo-label">DISPONIBLES</span>
            <span class="cupo-valor cupo-disponible">{{ Math.max(0, evento.cupo_maximo - participantes.length) }}</span>
          </div>
        </div>
      </div>

      <div class="tabla-header-acciones">
        <div>
          <h2 class="seccion-titulo">LISTADO DE PARTICIPANTES</h2>
          <p class="subtitulo">ALUMNOS REGISTRADOS EN ESTE EVENTO</p>
        </div>
        <!--
          CORRECCIÓN: eliminado el botón "EXPORTAR CSV".
          El backend no soporta exportación CSV. Solo soporta PDF mediante
          GET /api/eventos/{id}/constancias/{no_control} y ?download=1.
        -->
        <button @click="mostrarModalRegistro = true" class="btn-primario">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          REGISTRAR PARTICIPANTE
        </button>
      </div>
      
      <div class="tabla-card">
        <div class="tabla-scroll">
          <table class="tabla-principal">
            <thead>
              <tr>
                <th>NO. DE CONTROL</th>
                <th>NOMBRE COMPLETO</th>
                <th>CARRERA</th>
                <th class="centrado">SEMESTRE</th>
                <th class="centrado">CONSTANCIA</th>
                <th class="centrado">ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              <!--
                CORRECCIÓN: :key usa p.control (campo real del backend).
                Antes usaba p.id_participacion que NO existe en la respuesta del backend.

                CORRECCIÓN: todos los campos accedidos directamente desde el objeto plano
                del backend: p.control, p.nombre, p.carrera, p.semestre, p.constancia_emitida.
                Antes se accedía a p.alumno?.no_control etc. — estructura anidada inexistente.
              -->
              <tr v-for="p in participantes" :key="p.control">
                <td><span class="control-chip">{{ p.control || '—' }}</span></td>
                <td>
                  <div class="alumno-info">
                    <div class="alumno-avatar">{{ iniciales(p.nombre) }}</div>
                    <span class="texto-principal">{{ p.nombre || 'SIN NOMBRE' }}</span>
                  </div>
                </td>
                <td class="texto-secundario">{{ p.carrera || '—' }}</td>
                <td class="centrado texto-secundario">{{ p.semestre != null ? p.semestre + '°' : '—' }}</td>
                <td class="centrado">
                  <span class="badge-estado" :class="p.constancia_emitida ? 'emitida' : 'pendiente'">
                    {{ p.constancia_emitida ? 'EMITIDA' : 'PENDIENTE' }}
                  </span>
                </td>
                <td class="centrado">
                  <div class="acciones-fila">
                    <!-- Emitir constancia — solo si no está emitida -->
                    <button
                      v-if="!p.constancia_emitida"
                      @click="emitirConstancia(p)"
                      class="btn-secundario"
                      title="EMITIR CONSTANCIA"
                      :disabled="cargando"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                      </svg>
                      EMITIR
                    </button>

                    <!--
                      CORRECCIÓN: agregados botones de Ver PDF y Descargar PDF.
                      Implementados con los endpoints reales:
                        Ver:      GET /api/eventos/{id}/constancias/{no_control}
                        Descargar: GET /api/eventos/{id}/constancias/{no_control}?download=1
                      Solo visibles cuando la constancia ya fue emitida.
                    -->
                    <template v-if="p.constancia_emitida">
                      <button
                        @click="verConstanciaPDF(p)"
                        class="btn-secundario"
                        title="VER CONSTANCIA PDF"
                        :disabled="cargando"
                      >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                          <circle cx="12" cy="12" r="3"/>
                        </svg>
                        VER PDF
                      </button>
                      <button
                        @click="descargarConstanciaPDF(p)"
                        class="btn-secundario"
                        title="DESCARGAR CONSTANCIA PDF"
                        :disabled="cargando"
                      >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                          <polyline points="7 10 12 15 17 10"/>
                          <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        DESCARGAR
                      </button>
                    </template>

                    <button
                      @click="eliminarParticipante(p)"
                      class="btn-accion eliminar"
                      title="ELIMINAR PARTICIPANTE"
                      :disabled="cargando"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="participantes.length === 0 && !cargando">
                <td colspan="6" class="sin-resultados">
                  <svg viewBox="0 0 24 24" fill="none" stroke="#E0E0E0" stroke-width="1.5" width="40" height="40">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                  </svg>
                  <p>SIN PARTICIPANTES REGISTRADOS</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <footer class="pie-pagina">© 2026 TECNOLÓGICO NACIONAL DE MÉXICO · TODOS LOS DERECHOS RESERVADOS</footer>
    </div>
  </MainLayout>
  
  <!-- Modal Registro -->
  <transition name="modal-fade">
    <div v-if="mostrarModalRegistro" class="modal-fondo" @click.self="cerrarModal">
      <div class="modal-caja">
        <div class="modal-cabecera">
          <h3>REGISTRAR PARTICIPANTE</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="20" height="20">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="modal-cuerpo">
          <div class="campo-form">
            <!--
              CORRECCIÓN: el label ya indicaba "NÚMERO DE CONTROL" — correcto.
              El backend SOLO soporta búsqueda por número de control:
              GET /api/alumnos/buscar-control?no_control=XXXX
              NO existe búsqueda por nombre, apellido paterno ni apellido materno.
            -->
            <label class="campo-label">BUSCAR POR NÚMERO DE CONTROL <span class="requerido">*</span></label>
            <div class="busqueda-modal-wrap">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" class="icono-busqueda">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input
                v-model="busquedaModal"
                type="text"
                placeholder="INGRESA EL NÚMERO DE CONTROL..."
                class="campo-input"
                @input="buscarAlumnoModal"
              />
            </div>
          </div>

          <!--
            CORRECCIÓN: campos corregidos a los que devuelve el backend:
              nombre_completo  (antes: nombre — incorrecto)
              numero_control   (antes: no_control — incorrecto)
            El alumno encontrado se normaliza en buscarAlumnoModal() para
            exponer siempre las propiedades que el template necesita.
          -->
          <div v-if="alumnoEncontrado" class="alumno-encontrado">
            <div class="alumno-found-avatar">{{ iniciales(alumnoEncontrado.nombre_completo) }}</div>
            <div class="alumno-found-datos">
              <span class="alumno-found-nombre">{{ alumnoEncontrado.nombre_completo }}</span>
              <span class="alumno-found-info">{{ alumnoEncontrado.numero_control }} · {{ alumnoEncontrado.carrera }}</span>
            </div>
          </div>
          <div v-else-if="busquedaModal.length > 2 && !buscando" class="alumno-no-encontrado">
            NO SE ENCONTRÓ NINGÚN ALUMNO CON ESE NÚMERO DE CONTROL
          </div>
          <div v-if="buscando" class="alumno-buscando">
            BUSCANDO...
          </div>
        </div>
        <div class="modal-pie">
          <button @click="cerrarModal" class="btn-cancelar">CANCELAR</button>
          <button
            @click="registrarParticipante"
            class="btn-primario"
            :disabled="!alumnoEncontrado || cargando"
          >
            <span v-if="cargando" class="spinner"></span>
            {{ cargando ? 'REGISTRANDO...' : 'REGISTRAR' }}
          </button>
        </div>
      </div>
    </div>
  </transition>
  
  <!-- Toast -->
  <transition name="toast-slide">
    <div v-if="toast.visible" class="toast" :class="toast.tipo">
      <span class="toast-icono">
        <svg v-if="toast.tipo==='exito'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="16" height="16"><polyline points="20 6 9 17 4 12"/></svg>
        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </span>
      {{ toast.mensaje }}
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()
const route  = useRoute()
const API    = `${import.meta.env.VITE_API_URL}/api`

// ── Auth ──────────────────────────────────────────────────────────────────────
const token      = localStorage.getItem('auth_token')
const headers    = { 'Content-Type': 'application/json', ...(token && { Authorization: `Bearer ${token}` }) }
const headersGet = token ? { Authorization: `Bearer ${token}` } : {}

// ── Estado global ─────────────────────────────────────────────────────────────
const cargando            = ref(false)
const buscando            = ref(false)
const mostrarModalRegistro = ref(false)
const busquedaModal       = ref('')
const alumnoEncontrado    = ref(null)
const participantes       = ref([])

// CORRECCIÓN: evento inicializado con los campos reales del backend.
// El backend devuelve: id, id_evento, nombre, nombre_evento, tipo, tipo_evento_id,
// tipo_evento, fecha, descripcion, participantes, cupo_maximo
const evento = ref({
  id_evento:     null,
  nombre:        '',
  nombre_evento: '',
  fecha:         '',
  descripcion:   '',
  cupo_maximo:   null,
})

const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null
let debounce   = null

// ── Toast ─────────────────────────────────────────────────────────────────────
const mostrarToast = (m, t = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value = { visible: true, mensaje: m, tipo: t }
  timerToast  = setTimeout(() => (toast.value.visible = false), 3500)
}

// ── Cargar evento ─────────────────────────────────────────────────────────────
// GET /api/eventos/{id}
const cargarEvento = async () => {
  try {
    const r = await fetch(`${API}/eventos/${route.params.id}`, { headers: headersGet })
    if (!r.ok) throw new Error(`Error ${r.status}`)
    evento.value = await r.json()
  } catch (e) {
    console.error(e)
    mostrarToast('No se pudo cargar el evento.', 'error')
  }
}

// ── Cargar participantes ───────────────────────────────────────────────────────
// GET /api/eventos/{id}/participantes
// Respuesta: [{ control, nombre, carrera, semestre, constancia_emitida }]
//
// CORRECCIÓN: eliminado el mapeo a objeto anidado { alumno: {...} } que no
// corresponde a la estructura del backend. Se trabaja con los campos planos
// directamente tal como los devuelve el backend.
const cargarParticipantes = async () => {
  cargando.value = true
  try {
    const r = await fetch(`${API}/eventos/${route.params.id}/participantes`, { headers: headersGet })
    if (!r.ok) throw new Error(`Error ${r.status}`)
    participantes.value = await r.json()
  } catch (e) {
    console.error(e)
    participantes.value = []
    mostrarToast('Error cargando participantes.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── onMounted ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  await cargarEvento()
  await cargarParticipantes()
})

// ── Cerrar modal y limpiar estado ─────────────────────────────────────────────
const cerrarModal = () => {
  mostrarModalRegistro.value = false
  busquedaModal.value        = ''
  alumnoEncontrado.value     = null
  buscando.value             = false
  if (debounce) clearTimeout(debounce)
}

// ── Buscar alumno por número de control ───────────────────────────────────────
// GET /api/alumnos/buscar-control?no_control=XXXX
// Respuesta: { resultados: [{ numero_control, nombre_completo, carrera, semestre, estatus }] }
//
// CORRECCIÓN: antes guardaba await r.json() directamente (el objeto completo con
// la clave "resultados"), en vez de extraer el primer resultado del array.
//
// CORRECCIÓN: los campos del alumno encontrado son numero_control y nombre_completo
// (no no_control ni nombre como se usaba antes en el template).
//
// NOTA: el backend SOLO permite búsqueda por número de control.
// NO existe endpoint de búsqueda por nombre, apellido paterno o apellido materno.
const buscarAlumnoModal = () => {
  alumnoEncontrado.value = null
  if (busquedaModal.value.trim().length < 3) {
    buscando.value = false
    return
  }
  clearTimeout(debounce)
  buscando.value = true
  debounce = setTimeout(async () => {
    try {
     const r = await fetch(
  `${API}/alumnos/buscar-control?no_control=${encodeURIComponent(busquedaModal.value.trim())}`,
  {
    headers: headersGet
  }
)
      if (!r.ok) throw new Error()
      const data = await r.json()
      // El backend devuelve { resultados: [...] } — tomamos el primer resultado
      const resultados = data.resultados ?? []
      alumnoEncontrado.value = resultados.length > 0 ? resultados[0] : null
    } catch {
      alumnoEncontrado.value = null
    } finally {
      buscando.value = false
    }
  }, 400)
}

// ── Registrar participante ────────────────────────────────────────────────────
// POST /api/eventos/{id}/participantes
// Payload: { no_control: "22123456" }
//
// CORRECCIÓN: payload usaba alumnoEncontrado.value.control (campo inexistente).
// El backend devuelve el alumno con "numero_control" — se usa ese campo.
//
// CORRECCIÓN: manejo explícito de errores 409 (duplicado), 422 (validación),
// 404 (no encontrado) y 500 (error del servidor).
const registrarParticipante = async () => {
  if (!alumnoEncontrado.value) return
  cargando.value = true
  try {
    const r = await fetch(`${API}/eventos/${route.params.id}/participantes`, {
      method: 'POST',
      headers,
      body: JSON.stringify({ no_control: alumnoEncontrado.value.numero_control }),
    })

    if (r.status === 409) {
      mostrarToast('Este alumno ya está registrado en el evento.', 'error')
      return
    }
    if (r.status === 422) {
      const body = await r.json()
      const msg  = body.errors
        ? Object.values(body.errors).flat().join(' · ')
        : body.message || 'Datos inválidos.'
      mostrarToast(msg, 'error')
      return
    }
    if (r.status === 404) {
      mostrarToast('El alumno o el evento no fue encontrado.', 'error')
      return
    }
    if (!r.ok) {
      const body = await r.json().catch(() => ({}))
      throw new Error(body.message || `Error del servidor (${r.status})`)
    }

    cerrarModal()
    await cargarParticipantes()
    mostrarToast('Participante registrado correctamente.')
  } catch (e) {
    mostrarToast(e.message || 'Ocurrió un error inesperado.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Emitir constancia ─────────────────────────────────────────────────────────
// PATCH /api/eventos/{id}/participantes/{control}/constancia
//
// CORRECCIÓN: ahora usa p.control (campo real del backend).
// Antes usaba p.alumno.no_control — objeto anidado inexistente.
//
// CORRECCIÓN: usa headers (con Content-Type) en lugar de headersGet.
// Un PATCH debe incluir Content-Type aunque el body sea vacío.
//
// CORRECCIÓN: en lugar de mutar p.constancia_emitida directamente,
// recarga la lista completa para garantizar consistencia con el servidor.
const emitirConstancia = async (p) => {
  cargando.value = true
  try {
    const r = await fetch(
      `${API}/eventos/${route.params.id}/participantes/${p.control}/constancia`,
      { method: 'PATCH', headers }
    )
    if (!r.ok) {
      const body = await r.json().catch(() => ({}))
      throw new Error(body.message || 'No se pudo emitir la constancia.')
    }
    await cargarParticipantes()
    mostrarToast('Constancia emitida correctamente.')
  } catch (e) {
    mostrarToast(e.message, 'error')
  } finally {
    cargando.value = false
  }
}

// ── Eliminar participante ─────────────────────────────────────────────────────
// DELETE /api/eventos/{id}/participantes/{control}
//
// CORRECCIÓN: usa p.control (campo real del backend).
// Antes usaba p.alumno.no_control — objeto anidado inexistente.
//
// CORRECCIÓN: usa headers (con Authorization) para DELETE.
// Antes usaba headersGet sin Content-Type.
const eliminarParticipante = async (p) => {
  if (!confirm(`¿Eliminar a ${p.nombre} del evento?`)) return
  cargando.value = true
  try {
    const r = await fetch(
      `${API}/eventos/${route.params.id}/participantes/${p.control}`,
      { method: 'DELETE', headers }
    )
    if (!r.ok) {
      const body = await r.json().catch(() => ({}))
      throw new Error(body.message || 'No se pudo eliminar al participante.')
    }
    // Actualización optimista: filtra localmente con el campo correcto
    participantes.value = participantes.value.filter(x => x.control !== p.control)
    mostrarToast('Participante eliminado correctamente.')
  } catch (e) {
    mostrarToast(e.message, 'error')
  } finally {
    cargando.value = false
  }
}

// ── Ver constancia PDF ────────────────────────────────────────────────────────
// GET /api/eventos/{id}/constancias/{no_control}
//
// CORRECCIÓN: funcionalidad nueva — no existía en el archivo original.
// Abre el PDF en una nueva pestaña del navegador.
// Usa route.params.id (id del evento) y p.control (número de control del alumno).
const verConstanciaPDF = async (p) => {
  console.log('EVENTO:', route.params.id)
  console.log('CONTROL:', p.control)

  const url = `${API}/eventos/${route.params.id}/constancias/${p.control}`

  console.log('URL:', url)

  try {
    const url = `${API}/eventos/${route.params.id}/constancias/${p.control}`

    const response = await fetch(url, {
      headers: headersGet
    })

    if (!response.ok) {
      throw new Error(`Error ${response.status}`)
    }

    const blob = await response.blob()

    const pdfUrl = URL.createObjectURL(blob)

    window.open(pdfUrl, '_blank')

    setTimeout(() => {
      URL.revokeObjectURL(pdfUrl)
    }, 5000)

  } catch (e) {
    console.error(e)
    mostrarToast(
      'No se pudo abrir la constancia PDF.',
      'error'
    )
  } finally {
    cargando.value = false
  }
}

// ── Descargar constancia PDF ──────────────────────────────────────────────────
// GET /api/eventos/{id}/constancias/{no_control}?download=1
//
// CORRECCIÓN: funcionalidad nueva — no existía en el archivo original.
// Descarga el PDF usando un enlace temporal con el token de autenticación.
// Usa route.params.id (id del evento) y p.control (número de control del alumno).
const descargarConstanciaPDF = async (p) => {
  cargando.value = true
  try {
    const url = `${API}/eventos/${route.params.id}/constancias/${p.control}?download=1`
    const r   = await fetch(url, { headers: headersGet })
    if (!r.ok) throw new Error('No se pudo descargar la constancia.')
    const blob   = await r.blob()
    const link   = document.createElement('a')
    link.href     = URL.createObjectURL(blob)
    link.download = `constancia_${p.control}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    URL.revokeObjectURL(link.href)
  } catch (e) {
    mostrarToast(e.message || 'Error al descargar la constancia.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Utilidades ────────────────────────────────────────────────────────────────
const formatearFecha = (f) => {
  if (!f) return '—'
  const [a, m, d] = f.split('-')
  const mes = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre']
  return `${parseInt(d)} de ${mes[parseInt(m) - 1]} de ${a}`
}

const iniciales = (n) => {
  if (!n) return '?'
  return n.split(' ').filter(Boolean).slice(0, 2).map(x => x[0]).join('').toUpperCase()
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ============================================= */
/* ESTILOS GLOBALES - PALETA OFICIAL SICE       */
/* ============================================= */
.participantes-page { width: 100%; font-family: 'Montserrat', sans-serif; padding-bottom: 2rem; background: #F4F6F9; min-height: 100vh; }

/* Barra de carga */
.barra-carga { position: fixed; top: 74px; left: 0; right: 0; height: 3px; z-index: 1001; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.barra-carga.activa { opacity: 1; }
.barra-progreso { height: 100%; background: linear-gradient(90deg, #0B2545, #1D52B7, #2F80ED, #1D52B7, #0B2545); background-size: 200% 100%; animation: carga-anim 1.4s linear infinite; }
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* Breadcrumb */
.breadcrumb { color: #4F4F4F; font-size: 0.875rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; }
.breadcrumb .sep { color: #E0E0E0; }
.breadcrumb .activo { color: #1D52B7; font-weight: 600; }
.breadcrumb-link { color: #4F4F4F; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: #1D52B7; }

/* Card resumen evento */
.evento-resumen-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E0E0E0; box-shadow: 0 4px 12px rgba(29,82,183,0.08); padding: 1.6rem; margin-bottom: 1.5rem; display: flex; gap: 1.5rem; align-items: flex-start; }
.resumen-info { display: flex; align-items: flex-start; gap: 14px; flex: 1; }
.resumen-icono-wrap { width: 56px; height: 56px; background: rgba(29,82,183,0.10); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.resumen-nombre { font-size: 1.2rem; font-weight: 800; color: #333333; margin: 0 0 0.4rem; }
.resumen-meta { display: flex; gap: 1.2rem; flex-wrap: wrap; align-items: center; }
.meta-item { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: #4F4F4F; }
.descripcion-truncada { max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Header tabla */
.tabla-header-acciones { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem; }
.seccion-titulo { font-size: 1rem; font-weight: 700; color: #333333; margin: 0 0 2px; }
.subtitulo { color: #4F4F4F; font-size: 0.82rem; margin: 0; }

/* Tabla Estándar */
.tabla-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E0E0E0; box-shadow: 0 4px 12px rgba(29,82,183,0.08); overflow: hidden; margin-bottom: 1.5rem; }
.tabla-scroll { overflow-x: auto; }
.tabla-principal { width: 100%; border-collapse: collapse; }
.tabla-principal th { background: #F4F6F9; padding: 10px 14px; font-size: 0.72rem; font-weight: 600; color: #828282; text-transform: uppercase; letter-spacing: 0.06em; border-bottom: 1px solid #E0E0E0; text-align: left; } .tabla-principal th._old { padding: 10px 14px; font-size: 0.78rem; font-weight: 700; color: #4F4F4F; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #E0E0E0; text-align: left; }
.tabla-principal th.centrado { text-align: center; }
.tabla-principal td { padding: 8px 14px; border-bottom: 1px solid #E0E0E0; vertical-align: middle; font-size: 0.875rem; color: #333333; }
.tabla-principal td.centrado { text-align: center; }
.tabla-principal tr:last-child td { border-bottom: none; }
.tabla-principal tr:hover { background: #F4F6F9; }

.control-chip { background: #F4F6F9; border: 1px solid #E0E0E0; padding: 3px 8px; border-radius: 6px; font-family: monospace; font-size: 0.82rem; font-weight: 700; color: #333333; }
.alumno-info { display: flex; align-items: center; gap: 8px; }
.alumno-avatar { width: 32px; height: 32px; background: rgba(29,82,183,0.10); color: #0B2545; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.75rem; flex-shrink: 0; }
.texto-principal { font-weight: 600; color: #333333; font-size: 0.875rem; }
.texto-secundario { color: #4F4F4F; font-size: 0.875rem; }

/* Badge estado */
.badge-estado { font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
.badge-estado.emitida { background: #DCFCE7; color: #27AE60; }
.badge-estado.pendiente { background: #F3F4F6; color: #4F4F4F; }

/* Acciones iconizadas */
.acciones-fila { display: flex; gap: 6px; justify-content: center; align-items: center; flex-wrap: wrap; }
.btn-accion { width: 30px; height: 30px; border-radius: 7px; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.15s, opacity 0.2s; flex-shrink: 0; }
.btn-accion:hover:not(:disabled) { transform: scale(1.1); }
.btn-accion:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
.btn-accion.eliminar { background: #FEF2F2; color: #EB5757; }

.sin-resultados { padding: 3rem; text-align: center; color: #828282; font-size: 0.875rem; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.sin-resultados p { margin: 0; }

/* Cupo disponible */
.resumen-cupo { display: flex; gap: 1.5rem; padding: 0.75rem 1.5rem; background: rgba(29,82,183,0.04); border-top: 1px solid #E0E0E0; }
.cupo-item { display: flex; flex-direction: column; gap: 2px; }
.cupo-label { font-size: 0.72rem; font-weight: 600; color: #828282; text-transform: uppercase; letter-spacing: 0.05em; }
.cupo-valor { font-size: 1.2rem; font-weight: 700; color: #333333; }
.cupo-disponible { color: #27AE60; }

/* Modal */
.modal-fondo { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; z-index: 2000; backdrop-filter: blur(3px); }
.modal-caja { background: #FFFFFF; width: 480px; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(0,0,0,0.25); }
.modal-cabecera { background: #0B2545; color: #FFFFFF; padding: 1.1rem 1.6rem; display: flex; justify-content: space-between; align-items: center; }
.modal-cabecera h3 { margin: 0; font-size: 1.1rem; font-weight: 800; }
.btn-cerrar-modal { background: none; border: none; color: rgba(255,255,255,0.8); cursor: pointer; display: flex; align-items: center; transition: color 0.2s; }
.btn-cerrar-modal:hover { color: #FFFFFF; }
.modal-cuerpo { padding: 1.6rem; display: flex; flex-direction: column; gap: 1rem; }
.campo-form { display: flex; flex-direction: column; gap: 6px; }
.campo-label { font-size: 0.82rem; font-weight: 700; color: #333333; }
.requerido { color: #EB5757; }
.busqueda-modal-wrap { display: flex; align-items: center; gap: 8px; background: #F4F6F9; border: 1.5px solid #E0E0E0; border-radius: 8px; padding: 0 12px; }
.busqueda-modal-wrap:focus-within { border-color: #1D52B7; background: rgba(29,82,183,0.05); box-shadow: 0 0 0 3px rgba(29,82,183,0.10); }
.icono-busqueda { color: #4F4F4F; flex-shrink: 0; }
.campo-input { width: 100%; padding: 10px 0; border: none; background: transparent; font-size: 0.875rem; font-family: inherit; color: #333333; outline: none; }
.campo-input::placeholder { color: #828282; }
.alumno-encontrado { display: flex; align-items: center; gap: 12px; background: #DCFCE7; border: 1px solid #27AE60; border-radius: 10px; padding: 1rem; }
.alumno-found-avatar { width: 40px; height: 40px; background: #0B2545; color: #FFFFFF; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.875rem; flex-shrink: 0; }
.alumno-found-nombre { font-weight: 700; color: #333333; font-size: 0.875rem; display: block; }
.alumno-found-info { font-size: 0.78rem; color: #4F4F4F; }
.alumno-no-encontrado { background: #FEF2F2; color: #EB5757; padding: 0.9rem 1rem; border-radius: 8px; font-size: 0.82rem; font-weight: 600; text-align: center; }
.alumno-buscando { background: #F4F6F9; color: #828282; padding: 0.9rem 1rem; border-radius: 8px; font-size: 0.82rem; font-weight: 600; text-align: center; }
.modal-pie { padding: 1rem 1.6rem; background: #F4F6F9; border-top: 1px solid #E0E0E0; display: flex; justify-content: flex-end; gap: 0.75rem; }

/* Toast Estándar */
.toast { position: fixed; bottom: 2rem; right: 2rem; padding: 0.9rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.9rem; font-family: 'Montserrat', sans-serif; display: flex; align-items: center; gap: 0.6rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 3000; max-width: 380px; color: #FFFFFF; }
.toast.exito { background: #0B2545; }
.toast.error { background: #EB5757; }
.toast.info { background: #2563EB; }
.toast-slide-enter-active { transition: all 0.3s ease; }
.toast-slide-leave-active { transition: all 0.25s ease; }
.toast-slide-enter-from { transform: translateY(20px); opacity: 0; }
.toast-slide-leave-to { transform: translateX(100%); opacity: 0; }

/* ============================================= */
/* BOTONES ESTANDARIZADOS - PALETA OFICIAL      */
/* ============================================= */
.btn-primario {
  background: #0B2545;
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
.btn-primario:hover:not(:disabled) { background: #1D52B7; box-shadow: 0 4px 12px rgba(29,82,183,0.3); }
.btn-primario:disabled { background: #E0E0E0; color: #828282; cursor: not-allowed; }

.btn-secundario {
  background: rgba(29,82,183,0.10);
  color: #0B2545;
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
.btn-secundario:hover:not(:disabled) { background: rgba(29,82,183,0.16); }
.btn-secundario:disabled { opacity: 0.45; cursor: not-allowed; }

.btn-cancelar {
  background: #FFFFFF;
  color: #4F4F4F;
  border: 1px solid #E0E0E0;
  padding: 10px 1.4rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s;
}
.btn-cancelar:hover { background: #F4F6F9; }

.spinner {
  width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #FFFFFF;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Transiciones */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.pie-pagina { text-align: center; color: #828282; font-size: 0.82rem; padding-top: 2rem; }

/* ==================== RESPONSIVE ==================== */
@media (max-width: 640px) {
  .evento-resumen-card {
    flex-direction: column;
    gap: 1rem;
    padding: 1.25rem;
  }
  
  .resumen-meta {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .tabla-header-acciones {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .tabla-header-acciones .btn-primario {
    width: 100%;
  }

  .tabla-principal th,
  .tabla-principal td {
    padding: 8px 6px;
    font-size: 0.8rem;
  }
  
  .control-chip {
    font-size: 0.78rem;
    padding: 2px 6px;
  }
  
  .alumno-info {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
  
  .acciones-fila {
    flex-direction: column;
    gap: 6px;
  }
  
  .btn-secundario {
    width: 100%;
    justify-content: center;
    font-size: 0.8rem;
  }

  .modal-caja {
    width: 92%;
    margin: 0 4%;
  }
  
  .alumno-encontrado,
  .alumno-no-encontrado {
    flex-direction: column;
    text-align: center;
    gap: 8px;
  }
}

* {
  box-sizing: border-box;
}

.eventos-page,
.formulario-evento-page,
.participantes-page {
  padding: 1rem;
  min-height: 100vh;
}

.tabla-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
}
</style>