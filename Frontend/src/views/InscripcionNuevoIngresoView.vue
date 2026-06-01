<template>
  <MainLayout>
    <div class="nuevo-ingreso-page" ref="paginaRef" tabindex="-1">
      <!-- BREADCRUMB -->
      <nav class="breadcrumb" aria-label="MIGA DE PAN">
        <router-link to="/servicios-escolares" class="breadcrumb-link">
          SERVICIOS ESCOLARES
        </router-link>

        <span class="breadcrumb-sep" aria-hidden="true">›</span>

        <router-link to="/inscripciones/nueva" class="breadcrumb-link">
          INSCRIPCIONES
        </router-link>

        <span class="breadcrumb-sep" aria-hidden="true">›</span>

        <span class="breadcrumb-actual" aria-current="page">
          NUEVO INGRESO
        </span>
      </nav>

      <div class="page-header">
        <div>
          <h1 class="page-title">INSCRIPCIÓN DE NUEVO INGRESO</h1>
          <p class="subtitle">
            BUSQUE AL ASPIRANTE O ALUMNO DE NUEVO INGRESO, VALIDE SUS DATOS Y ASIGNE SU CARGA ACADÉMICA INICIAL.
          </p>
        </div>
      </div>

      <!-- TOAST -->
      <div v-if="notification.message" class="toast" :class="notification.type">
        {{ notification.message?.toUpperCase() }}
      </div>

      <!-- BARRA DE PASOS -->
      <div class="pasos-barra">
        <div class="paso" :class="{ activo: paso >= 1, completado: paso > 1 }">
          <div class="paso-circulo">
            <span v-if="paso > 1">✓</span>
            <span v-else>1</span>
          </div>
          <span class="paso-label">BUSCAR ASPIRANTE</span>
        </div>

        <div class="paso-linea" :class="{ completado: paso > 1 }"></div>

        <div class="paso" :class="{ activo: paso >= 2, completado: paso > 2 }">
          <div class="paso-circulo">
            <span v-if="paso > 2">✓</span>
            <span v-else>2</span>
          </div>
          <span class="paso-label">VALIDAR DATOS</span>
        </div>

        <div class="paso-linea" :class="{ completado: paso > 2 }"></div>

        <div class="paso" :class="{ activo: paso >= 3, completado: paso > 3 }">
          <div class="paso-circulo">
            <span v-if="paso > 3">✓</span>
            <span v-else>3</span>
          </div>
          <span class="paso-label">ASIGNAR CARGA</span>
        </div>

        <div class="paso-linea" :class="{ completado: paso > 3 }"></div>

        <div class="paso" :class="{ activo: paso >= 4 }">
          <div class="paso-circulo">4</div>
          <span class="paso-label">CONFIRMAR</span>
        </div>
      </div>

      <div class="content-card">
        <!-- PASO 1 -->
        <div v-if="paso === 1">
          <div class="paso-header">
            <div class="paso-icono">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>

            <div>
              <h3>BUSCAR ASPIRANTE O ALUMNO DE NUEVO INGRESO</h3>
              <p class="paso-desc">
                INGRESE NÚMERO DE CONTROL, FOLIO, CURP O NOMBRE PARA LOCALIZAR AL REGISTRO DE NUEVO INGRESO.
              </p>
            </div>
          </div>

          <div class="busqueda-row">
            <div class="campo-grupo campo-prioritario">
              <label>BÚSQUEDA <span class="etiqueta-principal">NUEVO INGRESO</span></label>

              <div class="input-con-icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>

                <input
                  type="text"
                  v-model="busquedaNuevoIngreso"
                  placeholder="EJ: FOLIO, CONTROL, CURP O NOMBRE"
                  @input="normalizarBusqueda('nuevoIngreso')"
                  @keyup.enter="buscarNuevoIngreso"
                />
              </div>
            </div>

            <div class="campo-grupo campo-periodo">
              <label>PERIODO</label>

              <select v-model="periodo" class="select-periodo" :disabled="cargandoPeriodos">
                <option :value="null" disabled>
                  {{ cargandoPeriodos ? 'CARGANDO PERIODOS...' : periodos.length === 0 ? 'SIN PERIODOS DISPONIBLES' : 'SELECCIONAR PERIODO' }}
                </option>

                <option v-for="p in periodos" :key="p.id" :value="p.id">
                  {{ p.nombre?.toUpperCase() }}
                </option>
              </select>
            </div>
          </div>

          <div class="busqueda-acciones">
            <button class="btn-buscar" @click="buscarNuevoIngreso" :disabled="cargando || !busquedaNuevoIngreso.trim()">
              <span v-if="cargando" class="spinner-btn"></span>
              {{ cargando ? 'BUSCANDO...' : 'BUSCAR REGISTRO' }}
            </button>
          </div>

          <div v-if="resultadosBusqueda.length > 0" class="resultados-lista">
            <p class="resultados-titulo">SELECCIONE EL REGISTRO CORRESPONDIENTE:</p>

            <div
              v-for="alumno in resultadosBusqueda"
              :key="alumno.id_alumno || alumno.id_aspirante || alumno.noControl || alumno.folio"
              class="resultado-item"
              @click="seleccionarNuevoIngreso(alumno)"
            >
              <div class="resultado-avatar">
                {{ inicial(alumno.nombre) }}
              </div>

              <div class="resultado-info">
                <strong>{{ alumno.nombre?.toUpperCase() }}</strong>
                <span>
                  {{ alumno.noControl || alumno.folio || 'SIN FOLIO' }} ·
                  {{ alumno.carrera?.toUpperCase() || 'SIN CARRERA' }} ·
                  {{ alumno.semestre }}° SEMESTRE ·
                  {{ alumno.estatus?.toUpperCase() }}
                </span>
              </div>

              <button class="btn-seleccionar">
                SELECCIONAR
              </button>
            </div>
          </div>

          <div v-if="!cargando && busquedaRealizada && resultadosBusqueda.length === 0" class="estado-vacio">
            <svg xmlns="http://www.w3.org/2000/svg" class="icono-vacio" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
            </svg>
            <h3>SIN RESULTADOS</h3>
            <p>NO SE ENCONTRÓ UN ASPIRANTE O ALUMNO DE NUEVO INGRESO CON LOS DATOS PROPORCIONADOS.</p>
          </div>
        </div>

        <!-- PASO 2 -->
        <div v-if="paso === 2">
          <div class="paso-header">
            <div class="paso-icono">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>

            <div>
              <h3>VALIDAR DATOS DEL NUEVO INGRESO</h3>
              <p class="paso-desc">
                VERIFIQUE LOS DATOS DEL ASPIRANTE. NO SE VALIDA INFORMACIÓN FINANCIERA EN ESTE MÓDULO.
              </p>
            </div>
          </div>

          <div v-if="alumnoSeleccionado" class="validacion-card">
            <div class="validacion-principal">
              <div class="alumno-avatar">
                {{ inicial(alumnoSeleccionado.nombre) }}
              </div>

              <div>
                <h4>{{ alumnoSeleccionado.nombre?.toUpperCase() }}</h4>
                <p>
                  {{ alumnoSeleccionado.noControl || alumnoSeleccionado.folio || 'SIN FOLIO' }} ·
                  {{ alumnoSeleccionado.carrera?.toUpperCase() || 'SIN CARRERA' }}
                </p>
              </div>
            </div>

            <div class="validacion-grid">
              <div class="validacion-item">
                <span>TIPO DE INSCRIPCIÓN</span>
                <strong>NUEVO INGRESO</strong>
              </div>

              <div class="validacion-item">
                <span>SEMESTRE</span>
                <strong>{{ alumnoSeleccionado.semestre }}° SEMESTRE</strong>
              </div>

              <div class="validacion-item">
                <span>PERIODO</span>
                <strong>{{ nombrePeriodoSeleccionado }}</strong>
              </div>

              <div class="validacion-item">
                <span>ESTATUS</span>
                <strong>{{ alumnoSeleccionado.estatus?.toUpperCase() }}</strong>
              </div>
            </div>

            <div class="aviso-docente">
              <strong>INDICACIÓN:</strong>
              ESTE FLUJO SOLO ASIGNA LA CARGA ACADÉMICA INICIAL. LA VALIDACIÓN DE PAGOS O FOLIOS FINANCIEROS NO FORMA PARTE DE ESTE MÓDULO.
            </div>
          </div>

          <div class="paso-navegacion">
            <button class="btn-atras" @click="paso = 1">
              REGRESAR
            </button>

            <button class="btn-siguiente" @click="paso = 3">
              CONTINUAR A CARGA
            </button>
          </div>
        </div>

        <!-- PASO 3 -->
        <div v-if="paso === 3">
          <div class="paso-header">
            <div class="paso-icono">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
              </svg>
            </div>

            <div>
              <h3>ASIGNAR CARGA ACADÉMICA INICIAL</h3>
              <p class="paso-desc">
                SELECCIONE LAS MATERIAS DISPONIBLES PARA EL PRIMER SEMESTRE O PARA LA SITUACIÓN ACADÉMICA DEL ALUMNO.
              </p>
            </div>
          </div>

          <div class="filtros-card-inline">
            <div class="filtros-label">
              FILTRAR:
            </div>

            <div class="busqueda-grupos-wrap">
              <input
                type="text"
                v-model="busquedaGrupo"
                placeholder="MATERIA, DOCENTE O AULA..."
                class="input-busqueda-grupos"
                @input="normalizarBusqueda('grupo')"
              />

              <button v-if="busquedaGrupo" @click="busquedaGrupo = ''" class="btn-limpiar-busqueda-g">
                ✕
              </button>
            </div>

            <button class="btn-filtrar-inline" @click="filtrarGrupos">
              FILTRAR
            </button>
          </div>

          <div v-if="gruposSeleccionados.length > 0" class="seleccion-multiple-resumen">
            <div>
              <strong>{{ gruposSeleccionados.length }} MATERIA(S) SELECCIONADA(S)</strong>
              <span>TOTAL: {{ totalCreditosSeleccionados }} CRÉDITOS · {{ totalHorasSeleccionadas }} HRS / SEMANA</span>
            </div>

            <button class="btn-limpiar-seleccion" @click="limpiarSeleccionGrupos">
              LIMPIAR SELECCIÓN
            </button>
          </div>

          <div class="table-container">
            <div v-if="cargandoGrupos" class="loading-overlay">
              <div class="loading-spinner"></div>
              <span>CARGANDO MATERIAS DISPONIBLES...</span>
            </div>

            <table v-else class="nuevo-ingreso-table">
              <thead>
                <tr>
                  <th class="text-center">SEL.</th>
                  <th>MATERIA</th>
                  <th>DOCENTE</th>
                  <th>AULA</th>
                  <th>HORARIO</th>
                  <th class="text-center">CRÉDITOS</th>
                  <th class="text-center">LUGARES</th>
                  <th class="text-center">ACCIÓN</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="grupo in gruposFiltrados"
                  :key="grupo.id"
                  :class="{ 'fila-seleccionada': grupoEstaSeleccionado(grupo) }"
                >
                  <td class="text-center">
                    <input
                      type="checkbox"
                      class="checkbox-grupo"
                      :checked="grupoEstaSeleccionado(grupo)"
                      :disabled="grupo.inscritos >= grupo.capacidad"
                      @change.stop="alternarGrupoSeleccionado(grupo)"
                    />
                  </td>

                  <td class="celda-materia">{{ grupo.materia?.toUpperCase() }}</td>
                  <td>{{ grupo.docente?.toUpperCase() }}</td>
                  <td>{{ grupo.aula?.toUpperCase() }}</td>

                  <td>
                    <span v-if="grupo.horario?.dia" class="horario-badge">
                      {{ grupo.horario.dia?.toUpperCase() }} {{ grupo.horario.horaInicio }}–{{ grupo.horario.horaFin }}
                    </span>
                    <span v-else class="sin-dato">—</span>
                  </td>

                  <td class="text-center">
                    <span class="creditos-badge">{{ grupo.creditos }}</span>
                  </td>

                  <td class="text-center">
                    <span class="lugares-badge" :class="{ lleno: grupo.inscritos >= grupo.capacidad, casi: grupo.inscritos >= grupo.capacidad * 0.9 }">
                      {{ grupo.capacidad - grupo.inscritos }} DISPONIBLES
                    </span>
                  </td>

                  <td class="text-center">
                    <button
                      v-if="grupo.inscritos < grupo.capacidad"
                      class="btn-elegir"
                      :class="{ seleccionado: grupoEstaSeleccionado(grupo) }"
                      @click.stop="alternarGrupoSeleccionado(grupo)"
                    >
                      {{ grupoEstaSeleccionado(grupo) ? 'QUITAR' : 'AGREGAR' }}
                    </button>

                    <span v-else class="badge-lleno">SIN LUGARES</span>
                  </td>
                </tr>

                <tr v-if="gruposFiltrados.length === 0">
                  <td colspan="8" class="sin-resultados">
                    NO SE ENCONTRARON MATERIAS DISPONIBLES.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="pagination">
            <span>MOSTRANDO {{ gruposFiltrados.length }} DE {{ gruposBaseFiltrados.length }} GRUPOS</span>

            <div class="pagination-buttons">
              <button class="page-btn" @click="prevPage" :disabled="currentPage === 1">
                ‹
              </button>

              <button
                v-for="page in totalPages"
                :key="page"
                class="page-btn"
                :class="{ active: page === currentPage }"
                @click="currentPage = page"
              >
                {{ page }}
              </button>

              <button class="page-btn" @click="nextPage" :disabled="currentPage === totalPages">
                ›
              </button>
            </div>
          </div>

          <div class="paso-navegacion">
            <button class="btn-atras" @click="paso = 2">
              REGRESAR
            </button>

            <button class="btn-siguiente" @click="continuarAConfirmacion" :disabled="gruposSeleccionados.length === 0">
              CONTINUAR CON {{ gruposSeleccionados.length }} MATERIA(S)
            </button>
          </div>
        </div>

        <!-- PASO 4 -->
        <div v-if="paso === 4">
          <div class="paso-header">
            <div class="paso-icono icono-verde">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>

            <div>
              <h3>CONFIRMAR INSCRIPCIÓN DE NUEVO INGRESO</h3>
              <p class="paso-desc">VERIFIQUE LOS DATOS ANTES DE REGISTRAR LA CARGA ACADÉMICA INICIAL.</p>
            </div>
          </div>

          <div class="confirmacion-grid">
            <div class="confirmacion-bloque">
              <p class="bloque-titulo">ALUMNO / ASPIRANTE</p>
              <p class="bloque-valor">{{ alumnoSeleccionado?.nombre?.toUpperCase() }}</p>
              <p class="bloque-sub">{{ alumnoSeleccionado?.noControl || alumnoSeleccionado?.folio || 'SIN FOLIO' }}</p>
              <p class="bloque-sub">{{ alumnoSeleccionado?.carrera?.toUpperCase() }}</p>
            </div>

            <div class="confirmacion-bloque">
              <p class="bloque-titulo">PERIODO</p>
              <p class="bloque-valor">{{ nombrePeriodoSeleccionado }}</p>
              <p class="bloque-sub">{{ gruposSeleccionados.length }} MATERIA(S)</p>
              <p class="bloque-sub">{{ totalCreditosSeleccionados }} CRÉDITOS · {{ totalHorasSeleccionadas }} HRS / SEMANA</p>
            </div>
          </div>

          <div class="materias-confirmacion-card">
            <div class="materias-confirmacion-header">
              <h4>CARGA ACADÉMICA INICIAL</h4>
              <span>{{ gruposSeleccionados.length }} REGISTRO(S)</span>
            </div>

            <div class="table-container">
              <table class="nuevo-ingreso-table">
                <thead>
                  <tr>
                    <th>MATERIA</th>
                    <th>DOCENTE</th>
                    <th>AULA</th>
                    <th>HORARIO</th>
                    <th class="text-center">CRÉDITOS</th>
                    <th class="text-center">ACCIÓN</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-for="grupo in gruposSeleccionados" :key="grupo.id">
                    <td class="celda-materia">{{ grupo.materia?.toUpperCase() }}</td>
                    <td>{{ grupo.docente?.toUpperCase() }}</td>
                    <td>{{ grupo.aula?.toUpperCase() }}</td>
                    <td>
                      <span v-if="grupo.horario?.dia" class="horario-badge">
                        {{ grupo.horario.dia?.toUpperCase() }} {{ grupo.horario.horaInicio }}–{{ grupo.horario.horaFin }}
                      </span>
                      <span v-else class="sin-dato">—</span>
                    </td>
                    <td class="text-center">
                      <span class="creditos-badge">{{ grupo.creditos }}</span>
                    </td>
                    <td class="text-center">
                      <button class="btn-quitar-confirmacion" @click="alternarGrupoSeleccionado(grupo)">
                        QUITAR
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="paso-navegacion">
            <button class="btn-atras" @click="paso = 3" :disabled="cargando">
              REGRESAR
            </button>

            <button class="btn-confirmar" @click="confirmarNuevoIngreso" :disabled="cargando || gruposSeleccionados.length === 0">
              <span v-if="cargando" class="spinner-btn"></span>
              {{ cargando ? 'REGISTRANDO...' : 'CONFIRMAR NUEVO INGRESO' }}
            </button>
          </div>
        </div>
      </div>

      <footer class="footer-institucional">
        © {{ new Date().getFullYear() }} TECNOLÓGICO NACIONAL DE MÉXICO · TODOS LOS DERECHOS RESERVADOS
      </footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`
const API_BASE = `${API}/inscripcion`

const paginaRef = ref(null)

const paso = ref(1)
const cargando = ref(false)
const cargandoGrupos = ref(false)
const cargandoPeriodos = ref(false)

const notification = ref({ message: '', type: '' })
const busquedaNuevoIngreso = ref('')
const busquedaRealizada = ref(false)
const resultadosBusqueda = ref([])
const alumnoSeleccionado = ref(null)

const periodos = ref([])
const periodo = ref(null)

const busquedaGrupo = ref('')
const grupos = ref([])
const gruposSeleccionados = ref([])
const currentPage = ref(1)
const porPagina = 6

const showNotification = (message, type = 'success') => {
  notification.value = { message: message.toUpperCase(), type }
  setTimeout(() => {
    notification.value = { message: '', type: '' }
  }, 3500)
}

const inicial = (nombre) => {
  if (!nombre) return '?'
  return nombre.charAt(0).toUpperCase()
}

const normalizarBusqueda = (campo) => {
  if (campo === 'nuevoIngreso') {
    busquedaNuevoIngreso.value = busquedaNuevoIngreso.value.toUpperCase()
    resultadosBusqueda.value = []
    alumnoSeleccionado.value = null
    busquedaRealizada.value = false
  }

  if (campo === 'grupo') {
    busquedaGrupo.value = busquedaGrupo.value.toUpperCase()
    currentPage.value = 1
  }
}

const nombrePeriodoSeleccionado = computed(() => {
  return periodos.value.find(p => p.id === periodo.value)?.nombre?.toUpperCase() ?? '—'
})

const normalizarAlumnoNuevoIngreso = (a) => ({
  id_alumno: a.id_alumno ?? a.idAlumno ?? a.id,
  id_aspirante: a.id_aspirante ?? a.idAspirante ?? null,
  noControl: a.noControl ?? a.numero_control ?? a.no_control ?? '',
  folio: a.folio ?? a.folio_aspirante ?? a.folio_registro ?? '',
  nombre: (
    a.nombre_completo ??
    a.alumno ??
    a.nombre ??
    `${a.nombre_persona ?? ''} ${a.apellido_paterno ?? ''} ${a.apellido_materno ?? ''}`
  ).trim(),
  carrera: a.carrera ?? a.nombre_carrera ?? '',
  semestre: Number(a.semestre ?? a.semestre_actual ?? 1),
  estatus: (a.estatus ?? a.estado ?? 'ACEPTADO').toString().toUpperCase()
})

const cargarPeriodos = async () => {
  cargandoPeriodos.value = true

  try {
    const rutas = [
      `${API}/periodos`,
      `${API}/catalogos/periodos`
    ]

    for (const ruta of rutas) {
      try {
        const response = await fetch(ruta, {
          headers: { 'Accept': 'application/json' }
        })

        if (!response.ok) continue

        const data = await response.json()
        const lista = Array.isArray(data) ? data : data.periodos ?? data.data ?? []

        periodos.value = lista.map(p => ({
          id: p.id_periodo ?? p.id,
          nombre: p.nombre ?? p.nombre_periodo ?? p.descripcion ?? `PERIODO ${p.id_periodo ?? p.id}`
        }))

        if (!periodo.value && periodos.value.length > 0) {
          const activo = periodos.value.find(p =>
            p.nombre?.toLowerCase().includes('actual') ||
            p.nombre?.toLowerCase().includes('activo')
          )

          periodo.value = activo?.id ?? periodos.value[0].id
        }

        return
      } catch {}
    }
  } catch (error) {
    console.error('ERROR CARGANDO PERIODOS:', error)
    periodos.value = []
  } finally {
    cargandoPeriodos.value = false
  }
}

const obtenerRutasBusqueda = (termino) => {
  const q = encodeURIComponent(termino)

  return [
    `${API_BASE}/buscar-nuevo-ingreso?q=${q}`,
    `${API}/aspirantes/buscar?q=${q}`,
    `${API}/alumnos/nuevo-ingreso?q=${q}`,
    `${API_BASE}/buscar-alumno?q=${q}`
  ]
}

const buscarNuevoIngreso = async () => {
  const termino = busquedaNuevoIngreso.value.trim()

  if (!termino) {
    showNotification('INGRESE UN FOLIO, NÚMERO DE CONTROL, CURP O NOMBRE.', 'error')
    return
  }

  cargando.value = true
  busquedaRealizada.value = false
  resultadosBusqueda.value = []
  alumnoSeleccionado.value = null

  try {
    const rutas = obtenerRutasBusqueda(termino)
    let ultimoError = null

    for (const ruta of rutas) {
      try {
        const response = await fetch(ruta, {
          headers: { 'Accept': 'application/json' }
        })

        if (!response.ok) {
          ultimoError = new Error(`HTTP ${response.status}`)
          continue
        }

        const data = await response.json()

        const lista = Array.isArray(data)
          ? data
          : data.aspirantes ?? data.alumnos ?? data.data ?? [data]

        resultadosBusqueda.value = lista
          .filter(Boolean)
          .map(normalizarAlumnoNuevoIngreso)
          .filter(a => a.nombre || a.noControl || a.folio)

        busquedaRealizada.value = true

        if (resultadosBusqueda.value.length === 0) {
          showNotification('NO SE ENCONTRARON REGISTROS DE NUEVO INGRESO.', 'error')
        }

        return
      } catch (error) {
        ultimoError = error
      }
    }

    throw ultimoError || new Error('NO SE ENCONTRÓ ENDPOINT DE NUEVO INGRESO')
  } catch (error) {
    console.error('ERROR BUSCANDO NUEVO INGRESO:', error)
    busquedaRealizada.value = true
    showNotification('NO SE PUDO BUSCAR EL REGISTRO DE NUEVO INGRESO.', 'error')
  } finally {
    cargando.value = false
  }
}

const seleccionarNuevoIngreso = async (alumno) => {
  alumnoSeleccionado.value = { ...alumno, semestre: alumno.semestre || 1 }
  resultadosBusqueda.value = []
  busquedaNuevoIngreso.value = `${alumno.noControl || alumno.folio || ''} - ${alumno.nombre}`.toUpperCase()
  paso.value = 2
  await cargarGruposDisponibles()
}

const normalizarGrupo = (g) => ({
  id: g.id_grupo ?? g.id,
  claveGrupo: (g.clave_grupo ?? g.claveGrupo ?? g.grupo ?? '—').toString().toUpperCase(),
  materia: (g.materia ?? g.nombre_materia ?? g.nombre ?? 'SIN MATERIA').toString().toUpperCase(),
  docente: (g.docente ?? g.nombre_docente ?? 'SIN DOCENTE').toString().toUpperCase(),
  aula: (g.aula ?? g.nombre_aula ?? '—').toString().toUpperCase(),
  creditos: Number(g.creditos ?? g.materia_creditos ?? 0),
  capacidad: Number(g.capacidad ?? g.cupo ?? 0),
  inscritos: Number(g.inscritos ?? g.ocupados ?? 0),
  semestre: Number(g.semestre ?? g.semestre_materia ?? 1),
  horario: {
    dia: (g.dia ?? g.horario?.dia ?? '').toString().toUpperCase(),
    horaInicio: g.hora_inicio ?? g.horaInicio ?? g.horario?.horaInicio ?? '',
    horaFin: g.hora_fin ?? g.horaFin ?? g.horario?.horaFin ?? ''
  }
})

const obtenerRutasGrupos = () => {
  const params = new URLSearchParams()

  if (periodo.value) params.set('id_periodo', String(periodo.value))
  params.set('semestre', String(alumnoSeleccionado.value?.semestre || 1))
  params.set('nuevo_ingreso', '1')

  const query = params.toString()

  return [
    `${API_BASE}/grupos?${query}`,
    `${API}/grupos/disponibles?${query}`,
    `${API}/inscripcion/grupos-disponibles?${query}`
  ]
}

const cargarGruposDisponibles = async () => {
  cargandoGrupos.value = true
  grupos.value = []

  try {
    const rutas = obtenerRutasGrupos()
    let ultimoError = null

    for (const ruta of rutas) {
      try {
        const response = await fetch(ruta, {
          headers: { 'Accept': 'application/json' }
        })

        if (!response.ok) {
          ultimoError = new Error(`HTTP ${response.status}`)
          continue
        }

        const data = await response.json()
        const lista = Array.isArray(data) ? data : data.grupos ?? data.data ?? []

        grupos.value = lista.map(normalizarGrupo)
        return
      } catch (error) {
        ultimoError = error
      }
    }

    throw ultimoError || new Error('NO SE ENCONTRÓ ENDPOINT DE GRUPOS')
  } catch (error) {
    console.error('ERROR CARGANDO GRUPOS:', error)
    showNotification('NO SE PUDIERON CARGAR LAS MATERIAS DISPONIBLES.', 'error')
  } finally {
    cargandoGrupos.value = false
  }
}

const gruposBaseFiltrados = computed(() => {
  const q = busquedaGrupo.value.toLowerCase().trim()

  if (!q) return grupos.value

  return grupos.value.filter(g =>
    g.materia.toLowerCase().includes(q) ||
    g.docente.toLowerCase().includes(q) ||
    g.aula.toLowerCase().includes(q) ||
    g.claveGrupo.toLowerCase().includes(q)
  )
})

const gruposFiltrados = computed(() => {
  const inicio = (currentPage.value - 1) * porPagina
  return gruposBaseFiltrados.value.slice(inicio, inicio + porPagina)
})

const totalPages = computed(() => {
  return Math.ceil(gruposBaseFiltrados.value.length / porPagina) || 1
})

const filtrarGrupos = () => {
  currentPage.value = 1
}

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++
}

const grupoEstaSeleccionado = (grupo) => {
  return gruposSeleccionados.value.some(g => g.id === grupo.id)
}

const alternarGrupoSeleccionado = (grupo) => {
  if (!grupo || grupo.inscritos >= grupo.capacidad) return

  if (grupoEstaSeleccionado(grupo)) {
    gruposSeleccionados.value = gruposSeleccionados.value.filter(g => g.id !== grupo.id)
    return
  }

  gruposSeleccionados.value.push(grupo)
}

const limpiarSeleccionGrupos = () => {
  gruposSeleccionados.value = []
}

const calcularHoras = (inicio, fin) => {
  if (!inicio || !fin) return 1

  try {
    const [hI, mI] = inicio.split(':').map(Number)
    const [hF, mF] = fin.split(':').map(Number)
    const minutos = (hF * 60 + mF) - (hI * 60 + mI)

    return minutos > 0 ? Math.max(1, Math.round(minutos / 60)) : 1
  } catch {
    return 1
  }
}

const totalHorasSeleccionadas = computed(() => {
  return gruposSeleccionados.value.reduce((total, grupo) => {
    return total + calcularHoras(grupo.horario?.horaInicio, grupo.horario?.horaFin)
  }, 0)
})

const totalCreditosSeleccionados = computed(() => {
  return gruposSeleccionados.value.reduce((total, grupo) => {
    return total + Number(grupo.creditos || 0)
  }, 0)
})

const continuarAConfirmacion = () => {
  if (gruposSeleccionados.value.length === 0) {
    showNotification('SELECCIONE AL MENOS UNA MATERIA.', 'error')
    return
  }

  paso.value = 4
}

const registrarGrupoNuevoIngreso = async (grupo) => {
  const payload = {
    id_alumno: alumnoSeleccionado.value?.id_alumno,
    id_aspirante: alumnoSeleccionado.value?.id_aspirante,
    id_grupo: grupo.id,
    id_periodo: periodo.value,
    tipo_inscripcion: 'NUEVO_INGRESO',
    nuevo_ingreso: true
  }

  const rutas = [
    `${API_BASE}/nuevo-ingreso/registrar`,
    `${API_BASE}/registrar-nuevo-ingreso`,
    `${API_BASE}/registrar`
  ]

  let ultimoError = null

  for (const ruta of rutas) {
    try {
      const response = await fetch(ruta, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
      })

      const data = await response.json().catch(() => ({}))

      if (!response.ok) {
        ultimoError = new Error(data.error || data.message || `HTTP ${response.status}`)
        continue
      }

      return data
    } catch (error) {
      ultimoError = error
    }
  }

  throw ultimoError || new Error('NO SE PUDO REGISTRAR LA INSCRIPCIÓN')
}

const confirmarNuevoIngreso = async () => {
  if (!alumnoSeleccionado.value || gruposSeleccionados.value.length === 0) {
    showNotification('FALTAN DATOS PARA CONFIRMAR LA INSCRIPCIÓN.', 'error')
    return
  }

  cargando.value = true

  try {
    for (const grupo of gruposSeleccionados.value) {
      await registrarGrupoNuevoIngreso(grupo)
    }

    showNotification('INSCRIPCIÓN DE NUEVO INGRESO CONFIRMADA CORRECTAMENTE.', 'success')

    await cargarGruposDisponibles()

    paso.value = 1
    busquedaNuevoIngreso.value = ''
    busquedaGrupo.value = ''
    resultadosBusqueda.value = []
    alumnoSeleccionado.value = null
    gruposSeleccionados.value = []
    currentPage.value = 1
    busquedaRealizada.value = false
  } catch (error) {
    console.error('ERROR CONFIRMANDO NUEVO INGRESO:', error)
    showNotification(error.message || 'NO SE PUDO CONFIRMAR LA INSCRIPCIÓN.', 'error')
  } finally {
    cargando.value = false
  }
}

watch(periodo, () => {
  if (alumnoSeleccionado.value) {
    cargarGruposDisponibles()
  }
})

onMounted(async () => {
  await cargarPeriodos()
  nextTick(() => paginaRef.value?.focus())
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap');

/* BASE GENERAL */
.nuevo-ingreso-page { width: 100%; min-width: 0; box-sizing: border-box; background: #F4F6F9; font-family: 'Montserrat', sans-serif; font-weight: 400; color: #333333; outline: none; text-transform: uppercase; }
.nuevo-ingreso-page * { font-family: 'Montserrat', sans-serif; box-sizing: border-box; }

/* BREADCRUMB */
.breadcrumb { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 1rem; color: #828282; font-size: 0.875rem; line-height: 1; }
.breadcrumb-link { display: inline-flex; align-items: center; justify-content: center; color: #828282; font-size: 0.875rem; font-weight: 500; line-height: 1; text-decoration: none; white-space: nowrap; transition: color 0.2s ease; }
.breadcrumb-link:hover { color: #2F80ED; text-decoration: underline; }
.breadcrumb-sep { display: inline-flex; align-items: center; justify-content: center; width: 10px; min-width: 10px; height: 1em; color: #BDBDBD; font-size: 1rem; font-weight: 500; line-height: 1; transform: translateY(-1px); user-select: none; }
.breadcrumb-actual { display: inline-flex; align-items: center; color: #2F80ED; font-size: 0.875rem; font-weight: 600; line-height: 1; white-space: nowrap; }

/* HEADER */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.2rem; }
.page-title { color: #333333; font-size: 1.75rem; font-weight: 700; line-height: 1.2; letter-spacing: -0.02em; margin: 0 0 0.4rem; }
.subtitle { color: #4F4F4F; margin: 0 0 1.8rem; font-size: 0.875rem; font-weight: 400; line-height: 1.45; }

/* TOAST */
.toast { position: fixed; bottom: 2rem; right: 2rem; display: flex; align-items: center; gap: 0.6rem; padding: 0.9rem 1.4rem; border-radius: 10px; color: #FFFFFF; font-size: 0.875rem; font-weight: 600; line-height: 1.4; box-shadow: 0 8px 24px rgba(29,82,183,0.15); z-index: 9999; animation: slideInToast 0.3s ease; }
.toast.success { background: #27AE60; }
.toast.error { background: #EB5757; }
@keyframes slideInToast { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

/* PASOS */
.pasos-barra { display: flex; align-items: center; width: 100%; margin-bottom: 1.5rem; padding: 1.2rem 2rem; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 12px; box-shadow: 0 4px 14px rgba(29,82,183,0.05); }
.paso { display: flex; align-items: center; gap: 10px; }
.paso-circulo { width: 34px; height: 34px; border-radius: 50%; background: #F2F4F7; color: #828282; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; transition: all 0.3s ease; }
.paso.activo .paso-circulo { background: #1D52B7; color: #FFFFFF; }
.paso.completado .paso-circulo { background: #27AE60; color: #FFFFFF; }
.paso-label { color: #828282; font-size: 0.8rem; font-weight: 600; line-height: 1.3; letter-spacing: 0.02em; transition: color 0.3s ease; }
.paso.activo .paso-label { color: #1D52B7; }
.paso.completado .paso-label { color: #27AE60; }
.paso-linea { flex: 1; height: 2px; margin: 0 1rem; background: #E0E0E0; transition: background 0.3s ease; }
.paso-linea.completado { background: #27AE60; }

/* CARD */
.content-card { max-width: 1080px; margin: 0 auto; padding: 2.2rem; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 16px; box-shadow: 0 8px 25px rgba(29,82,183,0.05); }
.content-card > div { animation: fadeSlide 0.25s ease; }
@keyframes fadeSlide { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

.paso-header { display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #E0E0E0; }
.paso-icono { width: 48px; height: 48px; border-radius: 12px; background: rgba(47,128,237,0.10); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.paso-icono svg { width: 24px; height: 24px; stroke: #1D52B7; }
.icono-verde { background: rgba(39,174,96,0.10); }
.icono-verde svg { stroke: #27AE60; }
.paso-header h3 { color: #333333; font-size: 1.125rem; font-weight: 600; line-height: 1.3; margin: 0 0 4px; }
.paso-desc { color: #4F4F4F; font-size: 0.875rem; font-weight: 400; line-height: 1.45; margin: 0; }

/* FORMULARIOS */
.busqueda-row { display: grid; grid-template-columns: minmax(260px, 1fr) 220px; gap: 1rem; align-items: end; margin-bottom: 1.2rem; }
.campo-grupo { display: flex; flex-direction: column; gap: 6px; }
.campo-periodo { min-width: 160px; }
.campo-grupo label { display: flex; align-items: center; gap: 8px; color: #333333; font-size: 0.8rem; font-weight: 600; line-height: 1.3; letter-spacing: 0.02em; }
.etiqueta-principal { background: #0B2545; color: #FFFFFF; font-size: 0.68rem; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
.input-con-icono { position: relative; }
.input-con-icono svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: #828282; pointer-events: none; }
.input-con-icono input { width: 100%; padding: 12px 14px 12px 40px; border: 1.5px solid #E0E0E0; border-radius: 10px; background: #FFFFFF; color: #333333; font-size: 0.875rem; font-weight: 400; line-height: 1.4; text-transform: uppercase; transition: border-color 0.2s ease, box-shadow 0.2s ease; }
.input-con-icono input::placeholder { color: #828282; font-size: 0.875rem; font-weight: 400; }
.campo-prioritario .input-con-icono input { border-color: #1D52B7; }
.input-con-icono input:focus { outline: none; border-color: #1D52B7; box-shadow: 0 0 0 3px rgba(29,82,183,0.15); }
.select-periodo { width: 100%; padding: 12px 14px; border: 1.5px solid #E0E0E0; border-radius: 10px; background: #FFFFFF; color: #333333; font-size: 0.875rem; font-weight: 400; line-height: 1.4; text-transform: uppercase; }
.select-periodo:focus { outline: none; border-color: #1D52B7; box-shadow: 0 0 0 3px rgba(29,82,183,0.15); }

.busqueda-acciones { display: flex; justify-content: flex-end; margin-bottom: 1.5rem; }

/* BOTONES */
.btn-buscar, .btn-filtrar-inline, .btn-siguiente, .btn-confirmar, .btn-atras, .btn-seleccionar, .btn-elegir, .btn-limpiar-seleccion, .btn-quitar-confirmacion { font-family: 'Montserrat', sans-serif; font-size: 0.85rem; font-weight: 600; line-height: 1.2; letter-spacing: 0.01em; text-transform: uppercase; cursor: pointer; transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease; }
.btn-buscar, .btn-filtrar-inline, .btn-siguiente { display: flex; align-items: center; justify-content: center; gap: 8px; background: #1D52B7; color: #FFFFFF; border: none; border-radius: 10px; }
.btn-buscar { padding: 12px 32px; }
.btn-filtrar-inline { padding: 9px 20px; white-space: nowrap; }
.btn-siguiente { padding: 11px 22px; }
.btn-buscar:hover:not(:disabled), .btn-filtrar-inline:hover, .btn-siguiente:hover:not(:disabled) { background: #2F80ED; }
.btn-buscar:disabled, .btn-siguiente:disabled, .btn-confirmar:disabled { opacity: 0.55; cursor: not-allowed; }
.btn-atras { display: flex; align-items: center; justify-content: center; gap: 6px; background: #FFFFFF; color: #4F4F4F; border: 1px solid #E0E0E0; padding: 11px 22px; border-radius: 10px; }
.btn-atras:hover:not(:disabled) { background: #F2F4F7; }
.btn-seleccionar { background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); padding: 7px 18px; border-radius: 8px; }
.btn-seleccionar:hover { background: rgba(47,128,237,0.15); }
.btn-confirmar { display: flex; align-items: center; justify-content: center; gap: 8px; background: #27AE60; color: #FFFFFF; border: none; padding: 13px 36px; border-radius: 10px; font-weight: 700; }
.btn-confirmar:hover:not(:disabled) { background: #219653; }
.btn-elegir { background: #1D52B7; color: #FFFFFF; border: none; padding: 8px 18px; border-radius: 8px; }
.btn-elegir:hover { background: #2F80ED; }
.btn-elegir.seleccionado { background: #27AE60; color: #FFFFFF; }
.btn-elegir.seleccionado:hover { background: #219653; }

/* RESULTADOS */
.resultados-titulo { color: #333333; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.8rem; }
.resultados-lista { margin-top: 0.5rem; display: flex; flex-direction: column; gap: 8px; }
.resultado-item { display: flex; align-items: center; gap: 14px; border: 1px solid #E0E0E0; border-radius: 10px; padding: 12px 16px; cursor: pointer; transition: border-color 0.2s ease, background 0.2s ease; }
.resultado-item:hover { border-color: #1D52B7; background: rgba(29,82,183,0.05); }
.resultado-avatar, .alumno-avatar { width: 44px; height: 44px; border-radius: 50%; background: #1D52B7; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 700; flex-shrink: 0; }
.resultado-info { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.resultado-info strong { color: #333333; font-size: 0.95rem; font-weight: 600; line-height: 1.3; }
.resultado-info span { color: #4F4F4F; font-size: 0.85rem; font-weight: 400; line-height: 1.45; margin-top: 2px; }

/* VALIDACIÓN */
.validacion-card { background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 14px; padding: 1.3rem; box-shadow: 0 4px 14px rgba(29,82,183,0.05); }
.validacion-principal { display: flex; align-items: center; gap: 14px; margin-bottom: 1.2rem; }
.validacion-principal .alumno-avatar { background: #27AE60; }
.validacion-principal h4 { margin: 0; color: #333333; font-size: 1rem; font-weight: 700; }
.validacion-principal p { margin: 3px 0 0; color: #4F4F4F; font-size: 0.85rem; }
.validacion-grid { display: grid; grid-template-columns: repeat(4, minmax(140px, 1fr)); gap: 1rem; }
.validacion-item { background: #F4F6F9; border: 1px solid #E0E0E0; border-radius: 10px; padding: 0.9rem; }
.validacion-item span { display: block; color: #828282; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.05em; margin-bottom: 4px; }
.validacion-item strong { display: block; color: #333333; font-size: 0.9rem; font-weight: 700; }
.aviso-docente { margin-top: 1rem; background: rgba(47,128,237,0.10); border: 1px solid rgba(47,128,237,0.25); color: #1D52B7; border-radius: 10px; padding: 0.9rem 1rem; font-size: 0.82rem; line-height: 1.45; }
.aviso-docente strong { font-weight: 700; }

/* FILTROS */
.filtros-card-inline { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 1.2rem; padding: 0.75rem 1.1rem; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 12px; box-shadow: 0 4px 12px rgba(29,82,183,0.05); }
.filtros-label { display: flex; align-items: center; gap: 5px; color: #4F4F4F; font-size: 0.8rem; font-weight: 600; white-space: nowrap; }
.busqueda-grupos-wrap { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 160px; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 8px; padding: 0 12px; }
.input-busqueda-grupos { flex: 1; border: none; background: transparent; color: #333333; padding: 8px 0; font-size: 0.875rem; font-weight: 400; outline: none; text-transform: uppercase; }
.input-busqueda-grupos::placeholder { color: #828282; }
.btn-limpiar-busqueda-g { background: transparent; border: none; color: #828282; cursor: pointer; font-size: 0.9rem; padding: 2px; line-height: 1; }

/* SELECCIÓN */
.seleccion-multiple-resumen { display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: rgba(47,128,237,0.10); border: 1px solid rgba(47,128,237,0.25); border-radius: 12px; padding: 12px 16px; margin-bottom: 1rem; }
.seleccion-multiple-resumen strong { display: block; color: #1D52B7; font-size: 0.875rem; font-weight: 600; }
.seleccion-multiple-resumen span { display: block; color: #4F4F4F; font-size: 0.8rem; font-weight: 400; margin-top: 2px; }
.btn-limpiar-seleccion { background: #FFFFFF; color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); padding: 8px 14px; border-radius: 8px; white-space: nowrap; }
.btn-limpiar-seleccion:hover { background: rgba(47,128,237,0.10); }

/* TABLAS */
.table-container { position: relative; border-radius: 12px; overflow: hidden; border: 1px solid #E0E0E0; background: #FFFFFF; }
.nuevo-ingreso-table { width: 100%; border-collapse: collapse; }
.nuevo-ingreso-table th { background: #F2F4F7; color: #333333; padding: 14px 16px; border-bottom: 1px solid #E0E0E0; text-align: left; font-size: 0.78rem; font-weight: 600; line-height: 1.3; letter-spacing: 0.02em; white-space: nowrap; }
.nuevo-ingreso-table td { padding: 14px 16px; border-bottom: 1px solid #E0E0E0; color: #333333; font-size: 0.85rem; font-weight: 400; line-height: 1.4; vertical-align: middle; }
.nuevo-ingreso-table tbody tr:hover { background: rgba(29,82,183,0.05); }
.fila-seleccionada { background: rgba(29,82,183,0.15) !important; outline: 2px solid #1D52B7; outline-offset: -2px; }
.text-center { text-align: center; }
.checkbox-grupo { width: 17px; height: 17px; cursor: pointer; accent-color: #1D52B7; }
.celda-materia { font-weight: 600; min-width: 170px; }
.horario-badge { display: inline-block; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); border-radius: 6px; padding: 3px 9px; font-size: 0.8rem; font-weight: 500; white-space: nowrap; }
.sin-dato { color: #828282; font-size: 0.85rem; }
.creditos-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); border-radius: 999px; padding: 4px 10px; font-weight: 700; }
.lugares-badge { display: inline-block; background: rgba(39,174,96,0.10); color: #27AE60; border: 1px solid rgba(39,174,96,0.35); border-radius: 20px; padding: 4px 12px; font-size: 0.8rem; font-weight: 600; }
.lugares-badge.casi { background: rgba(242,153,74,0.10); color: #F2994A; border-color: rgba(242,153,74,0.35); }
.lugares-badge.lleno { background: #FFF0F0; color: #EB5757; border-color: rgba(235,87,87,0.35); }
.badge-lleno { display: inline-block; background: #F2F4F7; color: #828282; border: 1px solid #E0E0E0; border-radius: 8px; padding: 7px 14px; font-size: 0.82rem; font-weight: 600; }
.sin-resultados { text-align: center; padding: 2rem; color: #4F4F4F; font-size: 0.875rem; }

/* PAGINACIÓN */
.pagination { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-top: 1.2rem; color: #4F4F4F; font-size: 0.875rem; font-weight: 400; }
.pagination-buttons { display: flex; gap: 4px; }
.page-btn { padding: 6px 12px; border: 1px solid #E0E0E0; background: #FFFFFF; border-radius: 6px; cursor: pointer; color: #333333; font-size: 0.85rem; font-weight: 500; }
.page-btn.active { background: #1D52B7; color: #FFFFFF; border-color: #1D52B7; }
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* CONFIRMACIÓN */
.confirmacion-grid { display: flex; align-items: stretch; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; padding: 1.5rem; background: rgba(29,82,183,0.05); border: 1px solid #E0E0E0; border-radius: 12px; }
.confirmacion-bloque { flex: 1; min-width: 220px; }
.bloque-titulo { color: #828282; font-size: 0.78rem; font-weight: 600; letter-spacing: 0.05em; margin: 0 0 6px; }
.bloque-valor { color: #333333; font-size: 1rem; font-weight: 600; margin: 0 0 4px; }
.bloque-sub { color: #4F4F4F; font-size: 0.85rem; font-weight: 400; margin: 0 0 2px; }
.materias-confirmacion-card { margin-bottom: 1.5rem; background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 12px; overflow: hidden; }
.materias-confirmacion-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 14px 16px; background: #F2F4F7; border-bottom: 1px solid #E0E0E0; }
.materias-confirmacion-header h4 { margin: 0; color: #333333; font-size: 1rem; font-weight: 600; }
.materias-confirmacion-header span { color: #4F4F4F; font-size: 0.8rem; font-weight: 600; }
.btn-quitar-confirmacion { background: #FFF0F0; color: #EB5757; border: 1px solid rgba(235,87,87,0.35); padding: 7px 12px; border-radius: 8px; }
.btn-quitar-confirmacion:hover { background: rgba(235,87,87,0.10); }
.paso-navegacion { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #E0E0E0; }

/* LOADING */
.loading-overlay { position: absolute; inset: 0; z-index: 10; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 160px; background: rgba(255,255,255,0.88); border-radius: 12px; color: #1D52B7; font-size: 0.875rem; font-weight: 600; }
.loading-spinner { width: 30px; height: 30px; border: 3px solid #E0E0E0; border-top-color: #1D52B7; border-radius: 50%; animation: spin 0.7s linear infinite; }
.spinner-btn { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #FFFFFF; border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* VACÍO */
.estado-vacio { text-align: center; padding: 3.5rem 2rem; color: #4F4F4F; background: #FFFFFF; border-radius: 12px; border: 1px dashed #E0E0E0; }
.icono-vacio { width: 60px; height: 60px; stroke: #BDBDBD; margin-bottom: 14px; }
.estado-vacio h3 { color: #333333; font-size: 1rem; font-weight: 600; margin: 0 0 6px; }
.estado-vacio p { color: #4F4F4F; font-size: 0.875rem; font-weight: 400; margin: 0; }

/* FOOTER */
.footer-institucional { text-align: center; color: #828282; font-size: 0.82rem; font-weight: 400; padding-top: 2rem; margin-top: 1rem; }

/* RESPONSIVE */
@media (max-width: 800px) {
  .busqueda-row { grid-template-columns: 1fr; }
  .content-card { padding: 1.4rem 1rem; }
  .pasos-barra { padding: 1rem; }
  .paso-label { display: none; }
  .validacion-grid { grid-template-columns: 1fr; }
  .paso-navegacion { flex-direction: column; align-items: stretch; }
  .btn-atras, .btn-siguiente, .btn-confirmar { width: 100%; justify-content: center; }
  .seleccion-multiple-resumen { flex-direction: column; align-items: stretch; }
  .btn-limpiar-seleccion { width: 100%; }
  .materias-confirmacion-header { flex-direction: column; align-items: flex-start; }
}
</style>