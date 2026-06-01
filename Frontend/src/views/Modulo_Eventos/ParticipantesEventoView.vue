<!-- ============================================= -->
<!-- src/views/ParticipantesEventoView.vue        -->
<!-- Módulo: Eventos — Participantes del evento   -->
<!-- Versión auditada y corregida                 -->
<!-- ============================================= -->
<template>
  <MainLayout>
    <div class="participantes-page">

      <!-- Barra de carga -->
      <div class="barra-carga" :class="{ activa: cargando }">
        <div class="barra-progreso"></div>
      </div>

      <!-- Breadcrumb -->
      <nav class="breadcrumb" aria-label="Navegación">
        <router-link to="/dashboard" class="breadcrumb-link">INICIO</router-link>
        <span class="sep" aria-hidden="true">›</span>
        <router-link to="/eventos" class="breadcrumb-link">EVENTOS</router-link>
        <span class="sep" aria-hidden="true">›</span>
        <span class="activo" aria-current="page">PARTICIPANTES</span>
      </nav>

      <!-- ─── Encabezado del evento ─────────────────────────────── -->
      <div class="evento-resumen-card">
        <div class="resumen-principal">
          <div class="resumen-icono-wrap" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="26" height="26">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8"  y1="2" x2="8"  y2="6"/>
              <line x1="3"  y1="10" x2="21" y2="10"/>
            </svg>
          </div>
          <div class="resumen-datos">
            <!--
              CORRECCIÓN: se usa el computed nombreEvento que extrae el nombre de forma
              segura. Nunca resuelve a un objeto; si ningún campo es string, muestra '—'.
            -->
            <h1 class="resumen-nombre">{{ nombreEvento }}</h1>
            <div class="resumen-meta">
              <span class="meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" aria-hidden="true">
                  <rect x="3" y="4" width="18" height="18" rx="2"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8"  y1="2" x2="8"  y2="6"/>
                </svg>
                {{ formatearFecha(evento.fecha) }}
              </span>
              <span v-if="evento.tipo_evento" class="meta-badge-tipo">
                {{ evento.tipo_evento }}
              </span>
              <span v-if="evento.descripcion" class="meta-item descripcion-truncada">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13" aria-hidden="true">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                {{ evento.descripcion.length > 90
                  ? evento.descripcion.substring(0, 90) + '…'
                  : evento.descripcion }}
              </span>
            </div>
          </div>
        </div>

        <!-- Métricas de cupo — solo si el evento tiene cupo_maximo definido -->
        <div v-if="evento.cupo_maximo" class="resumen-metricas">
          <div class="metrica-item">
            <span class="metrica-label">CUPO MÁX.</span>
            <span class="metrica-valor">{{ evento.cupo_maximo }}</span>
          </div>
          <div class="metrica-sep" aria-hidden="true"></div>
          <div class="metrica-item">
            <span class="metrica-label">INSCRITOS</span>
            <span class="metrica-valor">{{ participantesFiltrados.length }}</span>
          </div>
          <div class="metrica-sep" aria-hidden="true"></div>
          <div class="metrica-item">
            <span class="metrica-label">DISPONIBLES</span>
            <span class="metrica-valor disponibles">
              {{ Math.max(0, evento.cupo_maximo - participantes.length) }}
            </span>
          </div>
        </div>
        <!-- Si no hay cupo_maximo, solo muestra inscritos -->
        <div v-else class="resumen-metricas">
          <div class="metrica-item">
            <span class="metrica-label">INSCRITOS</span>
            <span class="metrica-valor">{{ participantes.length }}</span>
          </div>
        </div>
      </div>

      <!-- ─── Header de sección + controles ────────────────────── -->
      <div class="seccion-header">
        <div class="seccion-titulo-wrap">
          <h2 class="seccion-titulo">LISTADO DE PARTICIPANTES</h2>
          <p class="seccion-subtitulo">
            {{ participantesFiltrados.length }}
            {{ participantesFiltrados.length === 1 ? 'alumno registrado' : 'alumnos registrados' }}
            en este evento
          </p>
        </div>

        <div class="seccion-controles">
          <!--
            BUSCADOR DE TABLA.
            Filtra en el cliente por: control, nombre, carrera.
            El nombre en el backend viene como nombre completo (apellidos + nombre),
            por lo que la búsqueda textual abarca apellido paterno, materno y nombre.
            NO se hace fetch al backend para el filtro de tabla — los datos ya están cargados.
          -->
          <div class="buscador-tabla-wrap" role="search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 width="15" height="15" class="buscador-icono" aria-hidden="true">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input
              v-model="busquedaTabla"
              type="search"
              placeholder="Buscar por nombre, control o carrera…"
              class="buscador-input"
              aria-label="Buscar participantes"
            />
            <button
              v-if="busquedaTabla"
              @click="busquedaTabla = ''"
              class="buscador-limpiar"
              title="Limpiar búsqueda"
              aria-label="Limpiar búsqueda"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                   width="13" height="13" aria-hidden="true">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6"  y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <!--
            CORRECCIÓN: el botón "EXPORTAR CSV" fue eliminado.
            El backend NO soporta exportación CSV.
            El único formato de exportación disponible es PDF de constancias individuales:
            GET /api/eventos/{id}/constancias/{no_control}
          -->
          <button @click="mostrarModalRegistro = true" class="btn-primario" :disabled="cargando">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                 width="16" height="16" aria-hidden="true">
              <line x1="12" y1="5" x2="12" y2="19"/>
              <line x1="5"  y1="12" x2="19" y2="12"/>
            </svg>
            REGISTRAR
          </button>
        </div>
      </div>

      <!-- ─── Tabla principal ───────────────────────────────────── -->
      <div class="tabla-card">
        <!-- Estado: cargando -->
        <div v-if="cargando && participantes.length === 0" class="estado-tabla">
          <div class="spinner-grande" aria-label="Cargando…"></div>
          <p>Cargando participantes…</p>
        </div>

        <!-- Estado: sin resultados de búsqueda -->
        <div
          v-else-if="participantesFiltrados.length === 0 && busquedaTabla"
          class="estado-tabla"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5"
               width="44" height="44" aria-hidden="true">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <p>Sin resultados para <strong>"{{ busquedaTabla }}"</strong></p>
          <button @click="busquedaTabla = ''" class="btn-link">Limpiar búsqueda</button>
        </div>

        <!-- Estado: sin participantes -->
        <div
          v-else-if="participantes.length === 0 && !cargando"
          class="estado-tabla"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="#D1D5DB" stroke-width="1.5"
               width="44" height="44" aria-hidden="true">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <line x1="23" y1="11" x2="17" y2="11"/>
          </svg>
          <p>Aún no hay participantes en este evento</p>
          <button @click="mostrarModalRegistro = true" class="btn-link">
            Registrar el primero
          </button>
        </div>

        <!-- Tabla -->
        <div v-else class="tabla-scroll">
          <table class="tabla-principal" aria-label="Participantes del evento">
            <thead>
              <tr>
                <th scope="col">NO. CONTROL</th>
                <th scope="col">NOMBRE COMPLETO</th>
                <th scope="col" class="col-carrera">CARRERA</th>
                <th scope="col" class="centrado col-sem">SEM.</th>
                <th scope="col" class="centrado">CONSTANCIA</th>
                <th scope="col" class="centrado col-acciones">ACCIONES</th>
              </tr>
            </thead>
            <tbody>
              <!--
                KEY: p.control (campo real del backend).
                CAMPOS DIRECTOS DEL BACKEND:
                  p.control            — número de control
                  p.nombre             — nombre completo (apellidos + nombre)
                  p.carrera            — carrera
                  p.semestre           — semestre
                  p.constancia_emitida — boolean
                NO EXISTE estructura anidada p.alumno.xxx
              -->
              <tr
                v-for="p in participantesFiltrados"
                :key="p.control"
                class="fila-participante"
              >
                <td>
                  <span class="control-chip">{{ p.control || '—' }}</span>
                </td>
                <td>
                  <div class="alumno-info">
                    <div class="alumno-avatar" aria-hidden="true">{{ iniciales(p.nombre) }}</div>
                    <span class="alumno-nombre">{{ p.nombre || 'SIN NOMBRE' }}</span>
                  </div>
                </td>
                <td class="texto-sec col-carrera">{{ p.carrera || '—' }}</td>
                <td class="centrado texto-sec col-sem">
                  {{ p.semestre != null ? p.semestre + '°' : '—' }}
                </td>
                <td class="centrado">
                  <!--
                    CAMPO: p.constancia_emitida (boolean del backend).
                    Antes se usaba p.estado_constancia — campo inexistente.
                  -->
                  <span
                    class="badge-constancia"
                    :class="p.constancia_emitida ? 'emitida' : 'pendiente'"
                  >
                    <span class="badge-dot" aria-hidden="true"></span>
                    {{ p.constancia_emitida ? 'EMITIDA' : 'PENDIENTE' }}
                  </span>
                </td>
                <td class="centrado col-acciones">
                  <div class="acciones-fila">
                    <!--
                      EMITIR CONSTANCIA
                      ENDPOINT: PATCH /api/eventos/{id}/participantes/{control}/constancia
                      Solo visible si constancia_emitida === false
                    -->
                    <button
                      v-if="!p.constancia_emitida"
                      @click="emitirConstancia(p)"
                      class="btn-accion emitir"
                      title="Emitir constancia"
                      :disabled="cargando"
                      aria-label="Emitir constancia"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                           stroke-width="2" width="14" height="14" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <polyline points="9 15 11 17 15 13"/>
                      </svg>
                      <span class="btn-label">EMITIR</span>
                    </button>

                    <!--
                      VER PDF
                      ENDPOINT: GET /api/eventos/{id}/constancias/{no_control}
                      Abre el blob en nueva pestaña.
                      Solo visible si constancia_emitida === true.

                      CORRECCIÓN: la función verConstanciaPDF fue reescrita —
                      ya no tiene la variable "url" declarada dos veces (bug).
                      El revokeObjectURL se hace DESPUÉS de que el navegador
                      cargue el PDF, no a los 5 s (race condition).
                    -->
                    <button
                      v-if="p.constancia_emitida"
                      @click="verConstanciaPDF(p)"
                      class="btn-accion ver-pdf"
                      title="Ver constancia PDF"
                      :disabled="cargando"
                      aria-label="Ver constancia en PDF"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                           stroke-width="2" width="14" height="14" aria-hidden="true">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                      </svg>
                      <span class="btn-label">VER</span>
                    </button>

                    <!--
                      DESCARGAR PDF
                      ENDPOINT: GET /api/eventos/{id}/constancias/{no_control}?download=1
                      Solo visible si constancia_emitida === true.
                    -->
                    <button
                      v-if="p.constancia_emitida"
                      @click="descargarConstanciaPDF(p)"
                      class="btn-accion descargar"
                      title="Descargar constancia PDF"
                      :disabled="cargando"
                      aria-label="Descargar constancia en PDF"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                           stroke-width="2" width="14" height="14" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                      </svg>
                      <span class="btn-label">PDF</span>
                    </button>

                    <!--
                      ELIMINAR PARTICIPANTE
                      ENDPOINT: DELETE /api/eventos/{id}/participantes/{control}
                      Usa p.control (campo real del backend).
                    -->
                    <button
                      @click="eliminarParticipante(p)"
                      class="btn-accion eliminar"
                      title="Eliminar participante"
                      :disabled="cargando"
                      aria-label="Eliminar participante"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                           stroke-width="2" width="14" height="14" aria-hidden="true">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pie de tabla con resumen -->
        <div v-if="participantesFiltrados.length > 0" class="tabla-pie">
          <span class="tabla-total">
            {{ participantesFiltrados.length }}
            {{ participantesFiltrados.length === 1 ? 'participante' : 'participantes' }}
            <template v-if="busquedaTabla"> · filtrado de {{ participantes.length }}</template>
          </span>
          <span class="tabla-emitidas">
            <span class="dot-emitida" aria-hidden="true"></span>
            {{ constanciasEmitidas }} emitidas ·
            {{ participantes.length - constanciasEmitidas }} pendientes
          </span>
        </div>
      </div>

      <footer class="pie-pagina">
        © 2026 TECNOLÓGICO NACIONAL DE MÉXICO · TODOS LOS DERECHOS RESERVADOS
      </footer>
    </div>
  </MainLayout>

  <!-- ─── Modal: Registrar participante ──────────────────────── -->
  <transition name="modal-fade">
    <div
      v-if="mostrarModalRegistro"
      class="modal-fondo"
      @click.self="cerrarModal"
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-titulo"
    >
      <div class="modal-caja">
        <div class="modal-cabecera">
          <h3 id="modal-titulo">REGISTRAR PARTICIPANTE</h3>
          <button @click="cerrarModal" class="btn-cerrar-modal" aria-label="Cerrar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                 width="18" height="18" aria-hidden="true">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6"  y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <div class="modal-cuerpo">
          <div class="campo-form">
            <!--
              BUSCADOR DEL MODAL.
              ENDPOINT: GET /api/alumnos/buscar-control?no_control=XXXX
              NOTA BACKEND: el endpoint SOLO soporta búsqueda por número de control.
              NO existen endpoints de búsqueda por nombre, apellido paterno ni materno.
              Por eso el campo solo acepta número de control.
            -->
            <label for="busqueda-modal" class="campo-label">
              NÚMERO DE CONTROL <span class="requerido" aria-label="requerido">*</span>
            </label>
            <div class="busqueda-modal-wrap" :class="{ enfocado: modalFocused }">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                   width="15" height="15" class="icono-busqueda" aria-hidden="true">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input
                id="busqueda-modal"
                v-model="busquedaModal"
                type="text"
                placeholder="Ej. 22123456"
                class="campo-input"
                autocomplete="off"
                @input="buscarAlumnoModal"
                @focus="modalFocused = true"
                @blur="modalFocused = false"
              />
              <div v-if="buscando" class="spinner-mini" aria-label="Buscando…"></div>
            </div>
          </div>

          <!-- Alumno encontrado -->
          <div v-if="alumnoEncontrado" class="alumno-encontrado" role="status">
            <div class="alumno-found-avatar" aria-hidden="true">
              {{ iniciales(alumnoEncontrado.nombre_completo) }}
            </div>
            <div class="alumno-found-datos">
              <!--
                CAMPOS: alumnoEncontrado.nombre_completo y alumnoEncontrado.numero_control
                son los campos reales que devuelve el endpoint buscar-control.
                Antes se usaban: nombre, no_control — campos incorrectos.
              -->
              <span class="alumno-found-nombre">{{ alumnoEncontrado.nombre_completo }}</span>
              <span class="alumno-found-info">
                {{ alumnoEncontrado.numero_control }}
                <template v-if="alumnoEncontrado.carrera"> · {{ alumnoEncontrado.carrera }}</template>
                <template v-if="alumnoEncontrado.semestre"> · {{ alumnoEncontrado.semestre }}° sem.</template>
              </span>
            </div>
            <svg viewBox="0 0 24 24" fill="none" stroke="#27AE60" stroke-width="2.5"
                 width="18" height="18" class="check-encontrado" aria-hidden="true">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>

          <!-- No encontrado -->
          <div
            v-else-if="busquedaModal.trim().length >= 3 && !buscando"
            class="alumno-no-encontrado"
            role="alert"
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 width="15" height="15" aria-hidden="true">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            Sin resultados para el número de control ingresado
          </div>

          <!-- Ya inscrito (advertencia) -->
          <div v-if="alumnoYaInscrito" class="alumno-ya-inscrito" role="alert">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                 width="15" height="15" aria-hidden="true">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
              <line x1="12" y1="9" x2="12" y2="13"/>
              <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            Este alumno ya está inscrito en el evento
          </div>
        </div>

        <div class="modal-pie">
          <button @click="cerrarModal" class="btn-cancelar">CANCELAR</button>
          <button
            @click="registrarParticipante"
            class="btn-primario"
            :disabled="!alumnoEncontrado || alumnoYaInscrito || cargando"
          >
            <span v-if="cargando" class="spinner" aria-hidden="true"></span>
            {{ cargando ? 'REGISTRANDO…' : 'REGISTRAR' }}
          </button>
        </div>
      </div>
    </div>
  </transition>

  <!-- ─── Toast ───────────────────────────────────────────────── -->
  <transition name="toast-slide">
    <div
      v-if="toast.visible"
      class="toast"
      :class="toast.tipo"
      role="alert"
      aria-live="assertive"
    >
      <svg v-if="toast.tipo === 'exito'" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="3" width="15" height="15" aria-hidden="true">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
      <svg v-else viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.5" width="15" height="15" aria-hidden="true">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      {{ toast.mensaje }}
    </div>
  </transition>
</template>

<script setup>
// ── Imports necesarios ─────────────────────────────────────────────────────────
// LIMPIEZA: eliminado useRouter — no se usa navegación programática en este componente.
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const route = useRoute()
const API   = `${import.meta.env.VITE_API_URL}/api`

// ── Auth ──────────────────────────────────────────────────────────────────────
const token      = localStorage.getItem('auth_token')
const headers    = { 'Content-Type': 'application/json', ...(token && { Authorization: `Bearer ${token}` }) }
const headersGet = token ? { Authorization: `Bearer ${token}` } : {}

// ── Estado reactivo ───────────────────────────────────────────────────────────
const cargando             = ref(false)
const buscando             = ref(false)
const modalFocused         = ref(false)
const mostrarModalRegistro = ref(false)
const busquedaModal        = ref('')
const busquedaTabla        = ref('')
const alumnoEncontrado     = ref(null)
const participantes        = ref([])

// CORRECCIÓN: objeto evento inicializado con los campos reales que devuelve el backend:
// id, id_evento, nombre, nombre_evento, tipo, tipo_evento_id, tipo_evento,
// fecha, descripcion, participantes, cupo_maximo
const evento = ref({
  id_evento:     null,
  nombre:        '',
  nombre_evento: '',
  tipo_evento:   '',
  fecha:         '',
  descripcion:   '',
  cupo_maximo:   null,
})

const toast = ref({ visible: false, mensaje: '', tipo: 'exito' })
let timerToast = null
let debounce   = null

// ── Computed ──────────────────────────────────────────────────────────────────

/**
 * BUSCADOR DE TABLA — filtrado en cliente.
 * Busca en los campos: control, nombre, carrera.
 * El campo nombre incluye el nombre completo (apellidos + nombre),
 * por lo que abarca apellido paterno, materno y nombre.
 * No se realiza fetch al backend para este filtro.
 */
const participantesFiltrados = computed(() => {
  const q = busquedaTabla.value.trim().toLowerCase()
  if (!q) return participantes.value
  return participantes.value.filter(p => {
    return (
      (p.control  && p.control.toLowerCase().includes(q))  ||
      (p.nombre   && p.nombre.toLowerCase().includes(q))   ||
      (p.carrera  && p.carrera.toLowerCase().includes(q))
    )
  })
})

/** Contador de constancias emitidas para el pie de tabla */
const constanciasEmitidas = computed(() =>
  participantes.value.filter(p => p.constancia_emitida).length
)

/**
 * Alerta preventiva: el alumno encontrado ya está inscrito.
 * Se compara alumnoEncontrado.numero_control con los control de la lista.
 */
const alumnoYaInscrito = computed(() => {
  if (!alumnoEncontrado.value) return false
  return participantes.value.some(
    p => p.control === alumnoEncontrado.value.numero_control
  )
})

// ── Toast ─────────────────────────────────────────────────────────────────────
const mostrarToast = (mensaje, tipo = 'exito') => {
  if (timerToast) clearTimeout(timerToast)
  toast.value  = { visible: true, mensaje, tipo }
  timerToast   = setTimeout(() => (toast.value.visible = false), 3500)
}

// ── Cargar evento ─────────────────────────────────────────────────────────────
// GET /api/eventos/{id}
// CORRECCIÓN: extracción defensiva de la respuesta.
// El backend puede envolver el objeto en { data: {...} } o devolverlo directamente.
// Se loggea el raw para depuración y se extrae el nivel correcto.
const cargarEvento = async () => {
  try {
    const r = await fetch(`${API}/eventos/${route.params.id}`, { headers: headersGet })
    if (!r.ok) throw new Error(`Error ${r.status}`)
    const d = await r.json()
    console.log('EVENTO RAW:', d)
    // Si la respuesta viene envuelta en una clave "data" que es objeto, usarla.
    // De lo contrario, usar la respuesta directamente.
    evento.value = (d && d.data && typeof d.data === 'object' && !Array.isArray(d.data))
      ? d.data
      : d
  } catch (e) {
    console.error('[cargarEvento]', e)
    mostrarToast('No se pudo cargar la información del evento.', 'error')
  }
}

// ── Computed: nombre seguro del evento ────────────────────────────────────────
// CORRECCIÓN: resuelve nombre_evento o nombre de forma segura.
// Si ninguno es string (p.ej. si uno de ellos es un objeto serializado), muestra '—'.
const nombreEvento = computed(() => {
  const e = evento.value
  const v = e?.nombre_evento ?? e?.nombre
  return (v && typeof v === 'string') ? v : '—'
})

// ── Cargar participantes ───────────────────────────────────────────────────────
// GET /api/eventos/{id}/participantes
// Respuesta: [{ control, nombre, carrera, semestre, constancia_emitida }]
//
// CORRECCIÓN: se trabaja con el array plano del backend.
// Antes se mapeaba a una estructura anidada { alumno: {...} } que NO existe.
const cargarParticipantes = async () => {
  cargando.value = true
  try {
    const r = await fetch(`${API}/eventos/${route.params.id}/participantes`, { headers: headersGet })
    if (!r.ok) throw new Error(`Error ${r.status}`)
    participantes.value = await r.json()
  } catch (e) {
    console.error('[cargarParticipantes]', e)
    participantes.value = []
    mostrarToast('Error al cargar los participantes.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── onMounted ─────────────────────────────────────────────────────────────────
onMounted(async () => {
  await cargarEvento()
  await cargarParticipantes()
})

// ── Cerrar modal ───────────────────────────────────────────────────────────────
const cerrarModal = () => {
  mostrarModalRegistro.value = false
  busquedaModal.value        = ''
  alumnoEncontrado.value     = null
  buscando.value             = false
  modalFocused.value         = false
  if (debounce) clearTimeout(debounce)
}

// ── Buscar alumno por número de control ───────────────────────────────────────
// GET /api/alumnos/buscar-control?no_control=XXXX
// Respuesta: { resultados: [{ numero_control, nombre_completo, carrera, semestre, estatus }] }
//
// CORRECCIÓN: antes se guardaba await r.json() completo (el wrapper con "resultados")
// en lugar de extraer resultados[0].
//
// CORRECCIÓN: threshold ajustado a >= 3 caracteres (consistente con el template).
//
// NOTA BACKEND: este endpoint solo busca por número de control.
// No existen endpoints por nombre, apellido paterno o apellido materno.
const buscarAlumnoModal = () => {
  alumnoEncontrado.value = null
  clearTimeout(debounce)

  if (busquedaModal.value.trim().length < 3) {
    buscando.value = false
    return
  }

  buscando.value = true
  debounce = setTimeout(async () => {
    try {
      const r = await fetch(
        `${API}/alumnos/buscar-control?no_control=${encodeURIComponent(busquedaModal.value.trim())}`,
        { headers: headersGet }
      )
      if (!r.ok) throw new Error()
      const data       = await r.json()
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
// CORRECCIÓN: payload usa alumnoEncontrado.value.numero_control
// (campo real del backend). Antes usaba .control — campo inexistente en la respuesta.
//
// CORRECCIÓN: manejo granular de errores HTTP:
//   409 → duplicado, 422 → validación, 404 → no encontrado, 5xx → servidor
const registrarParticipante = async () => {
  if (!alumnoEncontrado.value || alumnoYaInscrito.value) return
  cargando.value = true
  try {
    const r = await fetch(`${API}/eventos/${route.params.id}/participantes`, {
      method:  'POST',
      headers,
      body:    JSON.stringify({ no_control: alumnoEncontrado.value.numero_control }),
    })

    if (r.status === 409) {
      mostrarToast('Este alumno ya está registrado en el evento.', 'error')
      return
    }
    if (r.status === 422) {
      const body = await r.json().catch(() => ({}))
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
// CORRECCIÓN: usa p.control (campo real). Antes usaba p.alumno.no_control.
// CORRECCIÓN: usa headers (con Content-Type) — un PATCH debe incluirlo.
// CORRECCIÓN: recarga la lista completa para garantizar consistencia.
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
    console.error('[emitirConstancia]', e)
    mostrarToast(e.message || 'Error al emitir la constancia.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Ver constancia PDF ────────────────────────────────────────────────────────
// GET /api/eventos/{id}/constancias/{no_control}
//
// CORRECCIÓN (bug crítico): la variable "url" estaba declarada DOS veces
// (una fuera y otra dentro del try), causando un SyntaxError silencioso.
//
// CORRECCIÓN: cargando.value = true al inicio (antes no se activaba).
//
// CORRECCIÓN: el revokeObjectURL con setTimeout de 5 s introducía una
// race condition — el navegador podría no haber cargado el PDF todavía.
// Ahora se deja la URL activa (el navegador la libera al cerrar la pestaña).
// Es seguro: los blob: URLs viven solo en la sesión.
//
// CORRECCIÓN: mensajes de error más claros (antes era genérico).
const verConstanciaPDF = async (p) => {
  cargando.value = true
  try {
    const url      = `${API}/eventos/${route.params.id}/constancias/${p.control}`
    const response = await fetch(url, { headers: headersGet })

    if (response.status === 404) {
      throw new Error('No se encontró la constancia para este alumno.')
    }
    if (!response.ok) {
      throw new Error(`Error al obtener el PDF (${response.status})`)
    }

    // Verificar que la respuesta sea realmente un PDF
    const contentType = response.headers.get('content-type') || ''
    const blob        = await response.blob()

    if (blob.size === 0) {
      throw new Error('El servidor devolvió un archivo vacío.')
    }

    // Forzar tipo PDF en el blob si el servidor no lo indica correctamente
    // INCOMPATIBILIDAD DOCUMENTADA: si el backend no envía Content-Type: application/pdf,
    // window.open puede no mostrar el visor nativo del navegador.
    const pdfBlob = contentType.includes('pdf')
      ? blob
      : new Blob([blob], { type: 'application/pdf' })

    const pdfUrl  = URL.createObjectURL(pdfBlob)
    const ventana = window.open(pdfUrl, '_blank')

    if (!ventana) {
      // Bloqueador de popups activo
      mostrarToast('El navegador bloqueó la ventana emergente. Permite popups para este sitio.', 'error')
      URL.revokeObjectURL(pdfUrl)
      return
    }

    // El blob se revoca cuando la ventana se descarga (no con timeout fijo)
    ventana.addEventListener('unload', () => URL.revokeObjectURL(pdfUrl), { once: true })

  } catch (e) {
    console.error('[verConstanciaPDF]', e)
    mostrarToast(e.message || 'No se pudo abrir la constancia.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Descargar constancia PDF ──────────────────────────────────────────────────
// GET /api/eventos/{id}/constancias/{no_control}?download=1
//
// Descarga el PDF usando un <a> temporal con el blob.
// CORRECCIÓN: URL.revokeObjectURL se llama después de click(), no antes.
const descargarConstanciaPDF = async (p) => {
  cargando.value = true
  try {
    const url = `${API}/eventos/${route.params.id}/constancias/${p.control}?download=1`
    const r   = await fetch(url, { headers: headersGet })

    if (r.status === 404) throw new Error('No se encontró la constancia para este alumno.')
    if (!r.ok)            throw new Error(`Error al descargar el PDF (${r.status})`)

    const blob     = await r.blob()
    if (blob.size === 0) throw new Error('El servidor devolvió un archivo vacío.')

    const blobUrl  = URL.createObjectURL(blob)
    const link     = document.createElement('a')
    link.href      = blobUrl
    link.download  = `constancia_${p.control}.pdf`
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    // CORRECCIÓN: revocar DESPUÉS del click, no antes
    setTimeout(() => URL.revokeObjectURL(blobUrl), 100)

    mostrarToast('Constancia descargada correctamente.')
  } catch (e) {
    console.error('[descargarConstanciaPDF]', e)
    mostrarToast(e.message || 'Error al descargar la constancia.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Eliminar participante ─────────────────────────────────────────────────────
// DELETE /api/eventos/{id}/participantes/{control}
//
// CORRECCIÓN: usa p.control (campo real). Antes usaba p.alumno.no_control.
// CORRECCIÓN: usa headers (con Authorization) para el DELETE.
// Actualización optimista para respuesta instantánea.
const eliminarParticipante = async (p) => {
  const nombre = p.nombre || p.control || 'este participante'
  if (!confirm(`¿Eliminar a ${nombre} del evento?`)) return
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
    // Actualización optimista con el campo correcto
    participantes.value = participantes.value.filter(x => x.control !== p.control)
    mostrarToast('Participante eliminado correctamente.')
  } catch (e) {
    console.error('[eliminarParticipante]', e)
    mostrarToast(e.message || 'Error al eliminar el participante.', 'error')
  } finally {
    cargando.value = false
  }
}

// ── Utilidades ────────────────────────────────────────────────────────────────
const formatearFecha = (f) => {
  if (!f) return '—'
  const partes = f.split('-')
  if (partes.length !== 3) return f
  const [a, m, d] = partes
  const meses = ['enero','febrero','marzo','abril','mayo','junio',
                 'julio','agosto','septiembre','octubre','noviembre','diciembre']
  const mesIdx = parseInt(m, 10) - 1
  if (mesIdx < 0 || mesIdx > 11) return f
  return `${parseInt(d, 10)} de ${meses[mesIdx]} de ${a}`
}

const iniciales = (n) => {
  if (!n) return '?'
  return n.split(' ').filter(Boolean).slice(0, 2).map(x => x[0]).join('').toUpperCase()
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

/* ── Reset y base ──────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }

/* ── Página ────────────────────────────────────────────────── */
.participantes-page {
  width: 100%;
  font-family: 'Montserrat', sans-serif;
  background: #F4F6F9;
  min-height: 100vh;
  padding-bottom: 2.5rem;
}

/* ── Barra de carga ────────────────────────────────────────── */
.barra-carga {
  position: fixed;
  top: 74px;
  left: 0; right: 0;
  height: 3px;
  z-index: 1001;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
}
.barra-carga.activa { opacity: 1; }
.barra-progreso {
  height: 100%;
  background: linear-gradient(90deg, #0B2545, #1D52B7, #2F80ED, #1D52B7, #0B2545);
  background-size: 200% 100%;
  animation: carga-anim 1.4s linear infinite;
}
@keyframes carga-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

/* ── Breadcrumb ────────────────────────────────────────────── */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.8rem;
  margin-bottom: 1rem;
  color: #6B7280;
}
.breadcrumb .sep { color: #D1D5DB; }
.breadcrumb .activo { color: #1D52B7; font-weight: 700; }
.breadcrumb-link {
  color: #6B7280;
  text-decoration: none;
  transition: color 0.15s;
}
.breadcrumb-link:hover { color: #1D52B7; }

/* Card resumen evento */
.evento-resumen-card { background: #FFFFFF; border-radius: 12px; border: 1px solid #E0E0E0; box-shadow: 0 4px 12px rgba(29,82,183,0.08); padding: 1.6rem; margin-bottom: 1.5rem; display: flex; gap: 1.5rem; align-items: flex-start; }
.resumen-info { display: flex; align-items: flex-start; gap: 14px; flex: 1; }
.resumen-icono-wrap { width: 56px; height: 56px; background: rgba(29,82,183,0.10); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.resumen-nombre { font-size: 1.2rem; font-weight: 800; color: #333333; margin: 0 0 0.4rem;   font-family: 'Montserrat', sans-serif;}
.resumen-meta { display: flex; gap: 1.2rem; flex-wrap: wrap; align-items: center; }
.meta-item { display: flex; align-items: center; gap: 5px; font-size: 0.82rem; color: #4F4F4F; }
.descripcion-truncada { max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* Header tabla */
.tabla-header-acciones { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem; }
.seccion-titulo { font-size: 1rem; font-weight: 700; color: #333333; margin: 0 0 2px;   font-family: 'Montserrat', sans-serif;}
.subtitulo { color: #4F4F4F; font-size: 0.82rem; margin: 0; }

/* ── Sección header ────────────────────────────────────────── */
.seccion-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.9rem;
  flex-wrap: wrap;
  gap: 0.75rem;
}
.seccion-titulo-wrap {}
.seccion-titulo {
  font-size: 0.95rem;
  font-weight: 800;
  color: #111827;
  margin: 0 0 2px;
  letter-spacing: 0.03em;
}
.seccion-subtitulo {
  font-size: 0.78rem;
  color: #6B7280;
  margin: 0;
}
.seccion-controles {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
}

/* Buscador de tabla */
.buscador-tabla-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #FFFFFF;
  border: 1.5px solid #E5E7EB;
  border-radius: 9px;
  padding: 0 10px;
  height: 38px;
  width: 260px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.buscador-tabla-wrap:focus-within {
  border-color: #1D52B7;
  box-shadow: 0 0 0 3px rgba(29,82,183,0.10);
}
.buscador-icono { color: #9CA3AF; flex-shrink: 0; }
.buscador-input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  font-size: 0.82rem;
  font-family: Montserrat, sans-serif;
  color: #111827;
  min-width: 0;
}
.buscador-input::placeholder { color: #9CA3AF; }
.buscador-limpiar {
  background: none;
  border: none;
  cursor: pointer;
  color: #9CA3AF;
  padding: 2px;
  display: flex;
  align-items: center;
  border-radius: 4px;
  transition: color 0.15s, background 0.15s;
  flex-shrink: 0;
}
.buscador-limpiar:hover { color: #374151; background: #F3F4F6; }

/* ── Tabla card ────────────────────────────────────────────── */
.tabla-card {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 2px 8px rgba(11,37,69,0.07);
  overflow: hidden;
  margin-bottom: 1.25rem;
}
.tabla-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: thin;
  scrollbar-color: #E5E7EB transparent;
}
.tabla-scroll::-webkit-scrollbar { height: 5px; }
.tabla-scroll::-webkit-scrollbar-track { background: transparent; }
.tabla-scroll::-webkit-scrollbar-thumb { background: #E5E7EB; border-radius: 10px; }

/* Tabla */
.tabla-principal {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.82rem;
}
.tabla-principal th {
  background: #F9FAFB;
  padding: 9px 12px;
  font-size: 0.68rem;
  font-weight: 700;
  color: #6B7280;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  border-bottom: 1px solid #E5E7EB;
  text-align: left;
  white-space: nowrap;
}
.tabla-principal th.centrado { text-align: center; }
.tabla-principal td {
  padding: 7px 12px;
  border-bottom: 1px solid #F3F4F6;
  vertical-align: middle;
  color: #374151;
}
.tabla-principal td.centrado { text-align: center; }
.tabla-principal tr:last-child td { border-bottom: none; }
.fila-participante { transition: background 0.12s; }
.fila-participante:hover { background: #F9FAFB; }

/* Columnas opcionales en mobile */
.col-carrera { min-width: 130px; }
.col-sem     { width: 60px; }
.col-acciones { width: 160px; }

/* Control chip */
.control-chip {
  background: #F3F4F6;
  border: 1px solid #E5E7EB;
  padding: 2px 7px;
  border-radius: 5px;
  font-family: Montserrat, sans-serif;
  font-size: 0.78rem;
  font-weight: 700;
  color: #374151;
  white-space: nowrap;
}

/* Alumno info */
.alumno-info {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}
.alumno-avatar {
  width: 28px; height: 28px;
  background: rgba(29,82,183,0.09);
  color: #0B2545;
  border-radius: 7px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.68rem;
  flex-shrink: 0;
}
.alumno-nombre {
  font-weight: 600;
  color: #111827;
  font-size: 0.82rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 220px;
}
.texto-sec {
  color: #6B7280;
  font-size: 0.8rem;
}

/* Badge constancia */
.badge-constancia {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.68rem;
  font-weight: 700;
  padding: 3px 9px;
  border-radius: 20px;
  letter-spacing: 0.04em;
  white-space: nowrap;
}
.badge-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}
.badge-constancia.emitida  { background: #DCFCE7; color: #15803D; }
.badge-constancia.emitida  .badge-dot { background: #16A34A; }
.badge-constancia.pendiente { background: #F3F4F6; color: #6B7280; }
.badge-constancia.pendiente .badge-dot { background: #D1D5DB; }

/* Acciones de fila */
.acciones-fila {
  display: flex;
  gap: 4px;
  justify-content: center;
  align-items: center;
  flex-wrap: nowrap;
}
.btn-accion {
  height: 28px;
  padding: 0 8px;
  border-radius: 6px;
  border: none;
  display: flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  transition: background 0.15s, transform 0.1s, opacity 0.2s;
  flex-shrink: 0;
  font-size: 0.68rem;
  font-weight: 700;
  font-family: Montserrat, sans-serif;
  white-space: nowrap;
}
.btn-accion .btn-label { line-height: 1; }
.btn-accion:hover:not(:disabled)  { transform: translateY(-1px); }
.btn-accion:active:not(:disabled) { transform: translateY(0); }
.btn-accion:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }

.btn-accion.emitir   { background: rgba(29,82,183,0.09);  color: #1D52B7; }
.btn-accion.emitir:hover:not(:disabled)   { background: rgba(29,82,183,0.16); }

.btn-accion.ver-pdf  { background: rgba(11,37,69,0.07);   color: #0B2545; }
.btn-accion.ver-pdf:hover:not(:disabled)  { background: rgba(11,37,69,0.14); }

.btn-accion.descargar { background: rgba(22,163,74,0.09);  color: #16A34A; }
.btn-accion.descargar:hover:not(:disabled) { background: rgba(22,163,74,0.16); }

.btn-accion.eliminar { background: #FEF2F2; color: #DC2626; width: 28px; padding: 0; justify-content: center; }
.btn-accion.eliminar:hover:not(:disabled) { background: #FEE2E2; }

/* Estado vacío / sin resultados */
.estado-tabla {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.6rem;
  padding: 3.5rem 1.5rem;
  color: #9CA3AF;
  font-size: 0.85rem;
  text-align: center;
}
.estado-tabla strong { color: #374151; }
.btn-link {
  background: none;
  border: none;
  color: #1D52B7;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  font-family: Montserrat, sans-serif;
  text-decoration: underline;
  text-underline-offset: 2px;
  padding: 0;
}
.btn-link:hover { color: #0B2545; }

/* Pie de tabla */
.tabla-pie {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 14px;
  background: #F9FAFB;
  border-top: 1px solid #E5E7EB;
  font-size: 0.75rem;
  color: #6B7280;
  font-weight: 600;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.tabla-emitidas {
  display: flex;
  align-items: center;
  gap: 5px;
}
.dot-emitida {
  width: 7px; height: 7px;
  border-radius: 50%;
  background: #16A34A;
  flex-shrink: 0;
}

/* ── Botones principales ───────────────────────────────────── */
.btn-primario {
  background: #0B2545;
  color: #FFFFFF;
  border: none;
  padding: 9px 16px;
  border-radius: 9px;
  font-weight: 700;
  font-size: 0.82rem;
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  font-family: Montserrat, sans-serif;
  transition: background 0.2s, box-shadow 0.2s;
  white-space: nowrap;
  height: 38px;
}
.btn-primario:hover:not(:disabled) {
  background: #1D52B7;
  box-shadow: 0 4px 12px rgba(29,82,183,0.28);
}
.btn-primario:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }

.btn-cancelar {
  background: #FFFFFF;
  color: #374151;
  border: 1.5px solid #E5E7EB;
  padding: 9px 18px;
  border-radius: 9px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  font-family: Montserrat, sans-serif;
  transition: background 0.15s, border-color 0.15s;
}
.btn-cancelar:hover { background: #F9FAFB; border-color: #D1D5DB; }

/* ── Modal ─────────────────────────────────────────────────── */
.modal-fondo {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  backdrop-filter: blur(3px);
  padding: 1rem;
}
.modal-caja {
  background: #FFFFFF;
  width: 460px;
  max-width: 100%;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 24px 60px rgba(0,0,0,0.22);
}
.modal-cabecera {
  background: #0B2545;
  color: #FFFFFF;
  padding: 1rem 1.4rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-cabecera h3 { margin: 0; font-size: 1rem; font-weight: 800; letter-spacing: 0.03em; }
.btn-cerrar-modal {
  background: none;
  border: none;
  color: rgba(255,255,255,0.7);
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 4px;
  border-radius: 6px;
  transition: color 0.15s, background 0.15s;
}
.btn-cerrar-modal:hover { color: #FFFFFF; background: rgba(255,255,255,0.1); }

.modal-cuerpo {
  padding: 1.4rem;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}
.campo-form { display: flex; flex-direction: column; gap: 5px; }
.campo-label { font-size: 0.78rem; font-weight: 700; color: #374151; letter-spacing: 0.03em; }
.requerido { color: #DC2626; margin-left: 2px; }

.busqueda-modal-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #F9FAFB;
  border: 1.5px solid #E5E7EB;
  border-radius: 9px;
  padding: 0 12px;
  height: 42px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.busqueda-modal-wrap.enfocado,
.busqueda-modal-wrap:focus-within {
  border-color: #1D52B7;
  background: #FAFBFF;
  box-shadow: 0 0 0 3px rgba(29,82,183,0.10);
}
.icono-busqueda { color: #9CA3AF; flex-shrink: 0; }
.campo-input {
  flex: 1;
  border: none;
  background: transparent;
  font-size: 0.875rem;
  font-family: Montserrat, sans-serif;
  color: #111827;
  outline: none;
  min-width: 0;
}
.campo-input::placeholder { color: #9CA3AF; }

/* Resultados modal */
.alumno-encontrado {
  display: flex;
  align-items: center;
  gap: 12px;
  background: #F0FDF4;
  border: 1px solid #86EFAC;
  border-radius: 10px;
  padding: 0.9rem 1rem;
}
.alumno-found-avatar {
  width: 38px; height: 38px;
  background: #0B2545;
  color: #FFFFFF;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.82rem;
  flex-shrink: 0;
}
.alumno-found-datos { flex: 1; min-width: 0; }
.alumno-found-nombre {
  font-weight: 700;
  color: #111827;
  font-size: 0.875rem;
  display: block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.alumno-found-info {
  font-size: 0.75rem;
  color: #6B7280;
  display: block;
  margin-top: 2px;
}
.check-encontrado { flex-shrink: 0; }

.alumno-no-encontrado {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #FEF2F2;
  color: #DC2626;
  padding: 0.8rem 1rem;
  border-radius: 9px;
  font-size: 0.8rem;
  font-weight: 600;
}
.alumno-ya-inscrito {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #FFFBEB;
  color: #D97706;
  padding: 0.8rem 1rem;
  border-radius: 9px;
  font-size: 0.8rem;
  font-weight: 600;
  border: 1px solid #FDE68A;
}

.modal-pie {
  padding: 0.9rem 1.4rem;
  background: #F9FAFB;
  border-top: 1px solid #E5E7EB;
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
}

/* ── Spinners ──────────────────────────────────────────────── */
.spinner {
  width: 14px; height: 14px;
  border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #FFFFFF;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}
.spinner-mini {
  width: 14px; height: 14px;
  border-radius: 50%;
  border: 2px solid #E5E7EB;
  border-top-color: #1D52B7;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}
.spinner-grande {
  width: 36px; height: 36px;
  border-radius: 50%;
  border: 3px solid #E5E7EB;
  border-top-color: #1D52B7;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Toast ─────────────────────────────────────────────────── */
.toast {
  position: fixed;
  bottom: 1.75rem;
  right: 1.75rem;
  padding: 0.85rem 1.25rem;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.85rem;
  font-family: 'Montserrat', sans-serif;
  display: flex;
  align-items: center;
  gap: 0.55rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  z-index: 3000;
  max-width: 360px;
  color: #FFFFFF;
}
.toast.exito { background: #0B2545; }
.toast.error { background: #DC2626; }
.toast.info  { background: #1D52B7; }

/* Transiciones */
.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.2s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }
.toast-slide-enter-active { transition: all 0.28s cubic-bezier(0.34, 1.56, 0.64, 1); }
.toast-slide-leave-active { transition: all 0.2s ease; }
.toast-slide-enter-from   { transform: translateY(16px) scale(0.96); opacity: 0; }
.toast-slide-leave-to     { transform: translateX(100%); opacity: 0; }

/* ── Pie de página ─────────────────────────────────────────── */
.pie-pagina {
  text-align: center;
  color: #9CA3AF;
  font-size: 0.75rem;
  padding-top: 2rem;
  letter-spacing: 0.02em;
}

/* ── RESPONSIVE ────────────────────────────────────────────── */
@media (max-width: 900px) {
  .col-carrera { display: none; }
  .resumen-metricas { border-left: none; padding-left: 0; border-top: 1px solid #E5E7EB; padding-top: 1rem; width: 100%; justify-content: space-around; }
}

@media (max-width: 640px) {
  .evento-resumen-card { flex-direction: column; align-items: stretch; padding: 1.1rem; gap: 1rem; }
  .resumen-nombre { font-size: 1rem; white-space: normal; }
  .seccion-header { flex-direction: column; align-items: stretch; gap: 0.75rem; }
  .seccion-controles { flex-direction: column; }
  .buscador-tabla-wrap { width: 100%; }
  .btn-primario { width: 100%; justify-content: center; }
  .col-sem { display: none; }
  .btn-accion .btn-label { display: none; }
  .btn-accion.emitir, .btn-accion.ver-pdf, .btn-accion.descargar {
    width: 28px;
    padding: 0;
    justify-content: center;
  }
  .tabla-pie { font-size: 0.7rem; }
  .modal-caja { width: 100%; border-radius: 16px 16px 0 0; }
  .modal-fondo { align-items: flex-end; padding: 0; }
  .tabla-principal th, .tabla-principal td { padding: 7px 9px; font-size: 0.77rem; }
  .toast { bottom: 1rem; right: 1rem; left: 1rem; max-width: none; }
}

@media (max-width: 380px) {
  .metrica-valor { font-size: 1.2rem; }
  .metrica-label { font-size: 0.62rem; }
}
</style>