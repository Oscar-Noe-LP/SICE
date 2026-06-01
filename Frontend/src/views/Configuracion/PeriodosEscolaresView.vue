<template>
  <MainLayout>
    <div class="periodos-page">
      <!-- BREADCRUMB -->
      <nav class="breadcrumb" aria-label="MIGA DE PAN">
        <router-link to="/servicios-escolares" class="breadcrumb-link">
          SERVICIOS ESCOLARES
        </router-link>

        <span class="breadcrumb-sep" aria-hidden="true">›</span>

        <router-link to="/configuracion/periodos" class="breadcrumb-link">
          CONFIGURACIÓN
        </router-link>

        <span class="breadcrumb-sep" aria-hidden="true">›</span>

        <span class="breadcrumb-actual" aria-current="page">
          PERIODOS ESCOLARES
        </span>
      </nav>

      <!-- ENCABEZADO -->
      <section class="page-header">
        <div>
          <h1 class="page-title">PERIODOS ESCOLARES</h1>
          <span class="page-subtitle">
            ADMINISTRE LOS PERIODOS ESCOLARES DEL SISTEMA, EVITANDO FECHAS TRASLAPADAS Y MANTENIENDO UN SOLO PERIODO ACTIVO.
          </span>
        </div>

        <button class="btn-nuevo" @click="abrirModalNuevo">
          <span class="btn-icono-texto">+</span>
          NUEVO PERIODO
        </button>
      </section>

      <!-- NOTIFICACIÓN -->
      <transition name="fade">
        <div v-if="notificacion.mensaje" class="notificacion" :class="notificacion.tipo">
          {{ notificacion.mensaje }}
        </div>
      </transition>

      <!-- KPIS -->
      <section class="kpis-grid">
        <article class="kpi-card">
          <span class="kpi-numero">{{ periodos.length }}</span>
          <span class="kpi-label">PERIODOS REGISTRADOS</span>
        </article>

        <article class="kpi-card">
          <span class="kpi-numero">{{ periodoActivo ? '1' : '0' }}</span>
          <span class="kpi-label">PERIODO ACTIVO</span>
        </article>

        <article class="kpi-card">
          <span class="kpi-numero">{{ periodosProximos.length }}</span>
          <span class="kpi-label">PRÓXIMOS</span>
        </article>

        <article class="kpi-card">
          <span class="kpi-numero">{{ periodosFinalizados.length }}</span>
          <span class="kpi-label">FINALIZADOS</span>
        </article>
      </section>

      <!-- FILTROS -->
      <section class="filtros-card">
        <div class="campo-grupo">
          <label>BUSCAR PERIODO</label>
          <div class="input-con-icono">
            <svg class="input-icono" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>

            <input
              v-model="busqueda"
              type="text"
              placeholder="BUSCAR POR NOMBRE, ESTATUS O FECHA..."
              @input="normalizarBusqueda"
            />
          </div>
        </div>

        <div class="campo-grupo">
          <label>ESTATUS</label>
          <select v-model="filtroEstatus" class="select-control">
            <option value="">TODOS</option>
            <option value="ACTIVO">ACTIVO</option>
            <option value="PROGRAMADO">PROGRAMADO</option>
            <option value="FINALIZADO">FINALIZADO</option>
            <option value="INACTIVO">INACTIVO</option>
          </select>
        </div>

        <button class="btn-limpiar" @click="limpiarFiltros">
          LIMPIAR
        </button>
      </section>

      <!-- VALIDACIONES -->
      <section class="validaciones-card">
        <div class="validacion-item" :class="{ correcto: !hayTraslapes, alerta: hayTraslapes }">
          <div class="validacion-icono">
            {{ hayTraslapes ? '!' : '✓' }}
          </div>

          <div>
            <strong>FECHAS TRASLAPADAS</strong>
            <span>
              {{ hayTraslapes ? 'EXISTEN PERIODOS CON FECHAS EMPALMADAS.' : 'NO SE DETECTAN FECHAS TRASLAPADAS.' }}
            </span>
          </div>
        </div>

        <div class="validacion-item" :class="{ correcto: cantidadPeriodosActivos === 1, alerta: cantidadPeriodosActivos !== 1 }">
          <div class="validacion-icono">
            {{ cantidadPeriodosActivos === 1 ? '✓' : '!' }}
          </div>

          <div>
            <strong>PERIODO ACTIVO ÚNICO</strong>
            <span>
              {{ textoValidacionActivo }}
            </span>
          </div>
        </div>
      </section>

      <!-- TABLA -->
      <section class="tabla-card">
        <div class="tabla-header">
          <div>
            <h2>LISTA DE PERIODOS</h2>
            <span>{{ periodosFiltrados.length }} REGISTRO(S) ENCONTRADO(S)</span>
          </div>
        </div>

        <div class="tabla-scroll">
          <table class="tabla-periodos">
            <thead>
              <tr>
                <th>NOMBRE DEL PERIODO</th>
                <th>FECHA INICIO</th>
                <th>FECHA FIN</th>
                <th class="text-center">DURACIÓN</th>
                <th class="text-center">ESTATUS</th>
                <th class="text-center">ACTIVO</th>
                <th class="text-center">ACCIONES</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="periodo in periodosFiltrados" :key="periodo.id">
                <td>
                  <div class="periodo-nombre">
                    <strong>{{ periodo.nombre }}</strong>
                    <span>ID: {{ periodo.id }}</span>
                  </div>
                </td>

                <td>{{ formatoFecha(periodo.fechaInicio) }}</td>
                <td>{{ formatoFecha(periodo.fechaFin) }}</td>

                <td class="text-center">
                  <span class="duracion-badge">
                    {{ calcularDuracionDias(periodo.fechaInicio, periodo.fechaFin) }} DÍAS
                  </span>
                </td>

                <td class="text-center">
                  <span class="estatus-badge" :class="claseEstatus(periodo.estatus)">
                    {{ periodo.estatus }}
                  </span>
                </td>

                <td class="text-center">
                  <button
                    class="switch-activo"
                    :class="{ activo: periodo.activo }"
                    :title="periodo.activo ? 'PERIODO ACTIVO' : 'ACTIVAR PERIODO'"
                    @click="activarPeriodo(periodo)"
                  >
                    <span></span>
                  </button>
                </td>

                <td class="text-center">
                  <div class="acciones-tabla">
                    <button class="btn-accion ver" @click="abrirDetalle(periodo)">
                      VER
                    </button>

                    <button class="btn-accion editar" @click="abrirModalEditar(periodo)">
                      EDITAR
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="periodosFiltrados.length === 0">
                <td colspan="7">
                  <div class="estado-vacio">
                    <svg class="icono-vacio" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6M7 21h10a2 2 0 002-2V8.5L13.5 3H7a2 2 0 00-2 2v14a2 2 0 002 2zM13 3v6h6"/>
                    </svg>
                    <h3>SIN PERIODOS</h3>
                    <p>NO SE ENCONTRARON PERIODOS ESCOLARES CON LOS FILTROS APLICADOS.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- MODAL NUEVO / EDITAR -->
      <transition name="modal">
        <div v-if="modalFormularioAbierto" class="modal-overlay" @click.self="cerrarModalFormulario">
          <div class="modal-card">
            <header class="modal-header">
              <div>
                <h2>{{ modoFormulario === 'nuevo' ? 'NUEVO PERIODO' : 'EDITAR PERIODO' }}</h2>
                <span>
                  {{ modoFormulario === 'nuevo' ? 'REGISTRE UN NUEVO PERIODO ESCOLAR.' : 'ACTUALICE LOS DATOS DEL PERIODO ESCOLAR.' }}
                </span>
              </div>

              <button class="btn-cerrar" @click="cerrarModalFormulario">
                ×
              </button>
            </header>

            <form class="formulario-periodo" @submit.prevent="guardarPeriodo">
              <div class="campo-grupo">
                <label>NOMBRE DEL PERIODO</label>
                <input
                  v-model="formulario.nombre"
                  type="text"
                  placeholder="EJ: ENERO - JUNIO 2026"
                  @input="formulario.nombre = formulario.nombre.toUpperCase()"
                />
              </div>

              <div class="form-grid">
                <div class="campo-grupo">
                  <label>FECHA INICIO</label>
                  <input v-model="formulario.fechaInicio" type="date" />
                </div>

                <div class="campo-grupo">
                  <label>FECHA FIN</label>
                  <input v-model="formulario.fechaFin" type="date" />
                </div>
              </div>

              <div class="form-grid">
                <div class="campo-grupo">
                  <label>ESTATUS</label>
                  <select v-model="formulario.estatus">
                    <option value="PROGRAMADO">PROGRAMADO</option>
                    <option value="ACTIVO">ACTIVO</option>
                    <option value="FINALIZADO">FINALIZADO</option>
                    <option value="INACTIVO">INACTIVO</option>
                  </select>
                </div>

                <div class="campo-grupo">
                  <label>PERIODO ACTIVO</label>
                  <div class="check-card">
                    <input id="periodo-activo" v-model="formulario.activo" type="checkbox" />
                    <label for="periodo-activo">MARCAR COMO PERIODO ACTIVO</label>
                  </div>
                </div>
              </div>

              <div v-if="erroresFormulario.length > 0" class="errores-card">
                <strong>VALIDACIONES</strong>
                <ul>
                  <li v-for="error in erroresFormulario" :key="error">
                    {{ error }}
                  </li>
                </ul>
              </div>

              <div v-if="advertenciasFormulario.length > 0" class="advertencias-card">
                <strong>ADVERTENCIAS</strong>
                <ul>
                  <li v-for="advertencia in advertenciasFormulario" :key="advertencia">
                    {{ advertencia }}
                  </li>
                </ul>
              </div>

              <footer class="modal-footer">
                <button type="button" class="btn-secundario" @click="cerrarModalFormulario">
                  CANCELAR
                </button>

                <button type="submit" class="btn-guardar">
                  {{ modoFormulario === 'nuevo' ? 'GUARDAR PERIODO' : 'ACTUALIZAR PERIODO' }}
                </button>
              </footer>
            </form>
          </div>
        </div>
      </transition>

      <!-- MODAL DETALLE -->
      <transition name="modal">
        <div v-if="modalDetalleAbierto && periodoDetalle" class="modal-overlay" @click.self="cerrarDetalle">
          <div class="modal-card modal-detalle">
            <header class="modal-header">
              <div>
                <h2>DETALLE DEL PERIODO</h2>
                <span>{{ periodoDetalle.nombre }}</span>
              </div>

              <button class="btn-cerrar" @click="cerrarDetalle">
                ×
              </button>
            </header>

            <section class="detalle-grid">
              <article class="detalle-item">
                <span>NOMBRE</span>
                <strong>{{ periodoDetalle.nombre }}</strong>
              </article>

              <article class="detalle-item">
                <span>FECHA INICIO</span>
                <strong>{{ formatoFecha(periodoDetalle.fechaInicio) }}</strong>
              </article>

              <article class="detalle-item">
                <span>FECHA FIN</span>
                <strong>{{ formatoFecha(periodoDetalle.fechaFin) }}</strong>
              </article>

              <article class="detalle-item">
                <span>DURACIÓN</span>
                <strong>{{ calcularDuracionDias(periodoDetalle.fechaInicio, periodoDetalle.fechaFin) }} DÍAS</strong>
              </article>

              <article class="detalle-item">
                <span>ESTATUS</span>
                <strong>{{ periodoDetalle.estatus }}</strong>
              </article>

              <article class="detalle-item">
                <span>ACTIVO</span>
                <strong>{{ periodoDetalle.activo ? 'SÍ' : 'NO' }}</strong>
              </article>
            </section>

            <section class="detalle-validaciones">
              <h3>VALIDACIONES DEL PERIODO</h3>

              <div class="validacion-mini" :class="{ correcto: !periodoTieneTraslape(periodoDetalle), alerta: periodoTieneTraslape(periodoDetalle) }">
                <strong>FECHAS TRASLAPADAS</strong>
                <span>
                  {{ periodoTieneTraslape(periodoDetalle) ? 'ESTE PERIODO SE TRASLAPA CON OTRO REGISTRO.' : 'ESTE PERIODO NO PRESENTA TRASLAPES.' }}
                </span>
              </div>

              <div class="validacion-mini" :class="{ correcto: periodoDetalle.activo || !periodoDetalle.activo, alerta: false }">
                <strong>PERIODO ACTIVO ÚNICO</strong>
                <span>
                  {{ periodoDetalle.activo ? 'ESTE ES EL PERIODO ACTIVO ACTUAL.' : 'ESTE PERIODO NO ESTÁ MARCADO COMO ACTIVO.' }}
                </span>
              </div>
            </section>

            <footer class="modal-footer">
              <button type="button" class="btn-secundario" @click="cerrarDetalle">
                CERRAR
              </button>

              <button type="button" class="btn-guardar" @click="abrirModalEditar(periodoDetalle)">
                EDITAR PERIODO
              </button>
            </footer>
          </div>
        </div>
      </transition>

      <footer class="footer-institucional">
        © {{ new Date().getFullYear() }} TECNOLÓGICO NACIONAL DE MÉXICO · TODOS LOS DERECHOS RESERVADOS
      </footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, reactive, ref, watch, onMounted } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const busqueda = ref('')
const filtroEstatus = ref('')
const modalFormularioAbierto = ref(false)
const modalDetalleAbierto = ref(false)
const modoFormulario = ref('nuevo')
const periodoEditandoId = ref(null)
const periodoDetalle = ref(null)
const erroresFormulario = ref([])
const advertenciasFormulario = ref([])
const notificacion = ref({ mensaje: '', tipo: '' })

const formularioInicial = () => ({
  nombre: '',
  fechaInicio: '',
  fechaFin: '',
  estatus: 'PROGRAMADO',
  activo: false
})

const formulario = reactive(formularioInicial())

const periodos = ref([
  {
    id: 1,
    nombre: 'ENERO - JUNIO 2025',
    fechaInicio: '2025-01-15',
    fechaFin: '2025-06-30',
    estatus: 'FINALIZADO',
    activo: false
  },
  {
    id: 2,
    nombre: 'AGOSTO - DICIEMBRE 2025',
    fechaInicio: '2025-08-01',
    fechaFin: '2025-12-20',
    estatus: 'FINALIZADO',
    activo: false
  },
  {
    id: 3,
    nombre: 'ENERO - JUNIO 2026',
    fechaInicio: '2026-01-12',
    fechaFin: '2026-06-26',
    estatus: 'ACTIVO',
    activo: true
  }
])

const mostrarNotificacion = (mensaje, tipo = 'exito') => {
  notificacion.value = { mensaje: mensaje.toUpperCase(), tipo }

  setTimeout(() => {
    notificacion.value = { mensaje: '', tipo: '' }
  }, 3500)
}

const normalizarBusqueda = () => {
  busqueda.value = busqueda.value.toUpperCase()
}

const limpiarFiltros = () => {
  busqueda.value = ''
  filtroEstatus.value = ''
}

const periodoActivo = computed(() => periodos.value.find(periodo => periodo.activo))

const cantidadPeriodosActivos = computed(() => {
  return periodos.value.filter(periodo => periodo.activo).length
})

const periodosProximos = computed(() => {
  const hoy = new Date()
  return periodos.value.filter(periodo => new Date(periodo.fechaInicio) > hoy)
})

const periodosFinalizados = computed(() => {
  const hoy = new Date()
  return periodos.value.filter(periodo => new Date(periodo.fechaFin) < hoy)
})

const textoValidacionActivo = computed(() => {
  if (cantidadPeriodosActivos.value === 1) return 'EXISTE UN SOLO PERIODO ACTIVO.'
  if (cantidadPeriodosActivos.value === 0) return 'NO HAY UN PERIODO ACTIVO DEFINIDO.'
  return 'EXISTE MÁS DE UN PERIODO ACTIVO.'
})

const periodosFiltrados = computed(() => {
  const q = busqueda.value.trim().toLowerCase()

  return periodos.value.filter(periodo => {
    const coincideBusqueda = !q ||
      periodo.nombre.toLowerCase().includes(q) ||
      periodo.estatus.toLowerCase().includes(q) ||
      periodo.fechaInicio.includes(q) ||
      periodo.fechaFin.includes(q)

    const coincideEstatus = !filtroEstatus.value || periodo.estatus === filtroEstatus.value

    return coincideBusqueda && coincideEstatus
  })
})

const hayTraslapes = computed(() => {
  return periodos.value.some(periodo => periodoTieneTraslape(periodo))
})

const resetFormulario = () => {
  Object.assign(formulario, formularioInicial())
  periodoEditandoId.value = null
  erroresFormulario.value = []
  advertenciasFormulario.value = []
}

const abrirModalNuevo = () => {
  resetFormulario()
  modoFormulario.value = 'nuevo'
  modalFormularioAbierto.value = true
}

const abrirModalEditar = (periodo) => {
  periodoDetalle.value = null
  modalDetalleAbierto.value = false
  resetFormulario()

  modoFormulario.value = 'editar'
  periodoEditandoId.value = periodo.id

  Object.assign(formulario, {
    nombre: periodo.nombre,
    fechaInicio: periodo.fechaInicio,
    fechaFin: periodo.fechaFin,
    estatus: periodo.estatus,
    activo: periodo.activo
  })

  modalFormularioAbierto.value = true
}

const cerrarModalFormulario = () => {
  modalFormularioAbierto.value = false
  resetFormulario()
}

const abrirDetalle = (periodo) => {
  periodoDetalle.value = { ...periodo }
  modalDetalleAbierto.value = true
}

const cerrarDetalle = () => {
  periodoDetalle.value = null
  modalDetalleAbierto.value = false
}

const formatoFecha = (fecha) => {
  if (!fecha) return '—'

  const [year, month, day] = fecha.split('-')
  return `${day}/${month}/${year}`
}

const calcularDuracionDias = (fechaInicio, fechaFin) => {
  if (!fechaInicio || !fechaFin) return 0

  const inicio = new Date(`${fechaInicio}T00:00:00`)
  const fin = new Date(`${fechaFin}T00:00:00`)
  const diferencia = fin - inicio

  if (Number.isNaN(diferencia) || diferencia < 0) return 0

  return Math.floor(diferencia / (1000 * 60 * 60 * 24)) + 1
}

const claseEstatus = (estatus) => {
  const mapa = {
    ACTIVO: 'activo',
    PROGRAMADO: 'programado',
    FINALIZADO: 'finalizado',
    INACTIVO: 'inactivo'
  }

  return mapa[estatus] ?? 'inactivo'
}

const fechasSeTraslapan = (aInicio, aFin, bInicio, bFin) => {
  return aInicio <= bFin && aFin >= bInicio
}

const periodoTieneTraslape = (periodoActual) => {
  if (!periodoActual?.fechaInicio || !periodoActual?.fechaFin) return false

  const inicioActual = new Date(`${periodoActual.fechaInicio}T00:00:00`)
  const finActual = new Date(`${periodoActual.fechaFin}T00:00:00`)

  return periodos.value.some(periodo => {
    if (periodo.id === periodoActual.id) return false
    if (!periodo.fechaInicio || !periodo.fechaFin) return false

    const inicio = new Date(`${periodo.fechaInicio}T00:00:00`)
    const fin = new Date(`${periodo.fechaFin}T00:00:00`)

    return fechasSeTraslapan(inicioActual, finActual, inicio, fin)
  })
}

const validarFormulario = () => {
  const errores = []
  const advertencias = []

  if (!formulario.nombre.trim()) {
    errores.push('EL NOMBRE DEL PERIODO ES OBLIGATORIO.')
  }

  if (!formulario.fechaInicio) {
    errores.push('LA FECHA DE INICIO ES OBLIGATORIA.')
  }

  if (!formulario.fechaFin) {
    errores.push('LA FECHA DE FIN ES OBLIGATORIA.')
  }

  if (formulario.fechaInicio && formulario.fechaFin) {
    const inicio = new Date(`${formulario.fechaInicio}T00:00:00`)
    const fin = new Date(`${formulario.fechaFin}T00:00:00`)

    if (inicio > fin) {
      errores.push('LA FECHA DE INICIO NO PUEDE SER MAYOR A LA FECHA DE FIN.')
    }

    const existeTraslape = periodos.value.some(periodo => {
      if (periodo.id === periodoEditandoId.value) return false

      const inicioPeriodo = new Date(`${periodo.fechaInicio}T00:00:00`)
      const finPeriodo = new Date(`${periodo.fechaFin}T00:00:00`)

      return fechasSeTraslapan(inicio, fin, inicioPeriodo, finPeriodo)
    })

    if (existeTraslape) {
      errores.push('LAS FECHAS DEL PERIODO SE TRASLAPAN CON OTRO PERIODO EXISTENTE.')
    }
  }

  if (formulario.activo) {
    const otroActivo = periodos.value.find(periodo => {
      return periodo.activo && periodo.id !== periodoEditandoId.value
    })

    if (otroActivo) {
      advertencias.push(`EL PERIODO ${otroActivo.nombre} DEJARÁ DE SER ACTIVO.`)
    }
  }

  erroresFormulario.value = errores
  advertenciasFormulario.value = advertencias

  return errores.length === 0
}

const guardarPeriodo = () => {
  if (!validarFormulario()) return

  if (formulario.activo) {
    periodos.value = periodos.value.map(periodo => ({
      ...periodo,
      activo: periodo.id === periodoEditandoId.value ? periodo.activo : false,
      estatus: periodo.id === periodoEditandoId.value ? periodo.estatus : periodo.estatus === 'ACTIVO' ? 'INACTIVO' : periodo.estatus
    }))
  }

  if (modoFormulario.value === 'nuevo') {
    const nuevoPeriodo = {
      id: generarNuevoId(),
      nombre: formulario.nombre.trim().toUpperCase(),
      fechaInicio: formulario.fechaInicio,
      fechaFin: formulario.fechaFin,
      estatus: formulario.activo ? 'ACTIVO' : formulario.estatus,
      activo: formulario.activo
    }

    periodos.value.push(nuevoPeriodo)
    mostrarNotificacion('PERIODO ESCOLAR REGISTRADO CORRECTAMENTE.', 'exito')
  } else {
    periodos.value = periodos.value.map(periodo => {
      if (periodo.id !== periodoEditandoId.value) return periodo

      return {
        ...periodo,
        nombre: formulario.nombre.trim().toUpperCase(),
        fechaInicio: formulario.fechaInicio,
        fechaFin: formulario.fechaFin,
        estatus: formulario.activo ? 'ACTIVO' : formulario.estatus,
        activo: formulario.activo
      }
    })

    mostrarNotificacion('PERIODO ESCOLAR ACTUALIZADO CORRECTAMENTE.', 'exito')
  }

  cerrarModalFormulario()
}

const activarPeriodo = (periodoSeleccionado) => {
  if (periodoSeleccionado.activo) {
    mostrarNotificacion('ESTE PERIODO YA SE ENCUENTRA ACTIVO.', 'exito')
    return
  }

  periodos.value = periodos.value.map(periodo => ({
    ...periodo,
    activo: periodo.id === periodoSeleccionado.id,
    estatus: periodo.id === periodoSeleccionado.id
      ? 'ACTIVO'
      : periodo.estatus === 'ACTIVO'
        ? 'INACTIVO'
        : periodo.estatus
  }))

  mostrarNotificacion(`PERIODO ACTIVO ACTUALIZADO A ${periodoSeleccionado.nombre}.`, 'exito')
}

const generarNuevoId = () => {
  if (periodos.value.length === 0) return 1
  return Math.max(...periodos.value.map(periodo => Number(periodo.id))) + 1
}

watch(
  () => ({ ...formulario }),
  () => {
    if (modalFormularioAbierto.value) {
      validarFormulario()
    }
  },
  { deep: true }
)

onMounted(() => {
  // EN CUANTO BACKEND EXPONGA EL ENDPOINT, AQUÍ SE CARGARÁN LOS PERIODOS DESDE LA API.
  // cargarPeriodos()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap');

/* ============================================= */
/* BASE GENERAL                                  */
/* ============================================= */
.periodos-page { width: 100%; min-width: 0; background: #F4F6F9; font-family: 'Montserrat', sans-serif; font-weight: 400; color: #333333; text-transform: uppercase; }
.periodos-page * { font-family: 'Montserrat', sans-serif; box-sizing: border-box; }

/* ============================================= */
/* BREADCRUMB                                    */
/* ============================================= */
.breadcrumb { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 1rem; color: #828282; font-size: 0.875rem; line-height: 1; }
.breadcrumb-link { display: inline-flex; align-items: center; color: #828282; font-size: 0.875rem; font-weight: 500; line-height: 1; text-decoration: none; white-space: nowrap; transition: color 0.2s ease; }
.breadcrumb-link:hover { color: #2F80ED; text-decoration: underline; }
.breadcrumb-sep { display: inline-flex; align-items: center; justify-content: center; width: 10px; min-width: 10px; height: 1em; color: #BDBDBD; font-size: 1rem; font-weight: 500; line-height: 1; transform: translateY(-1px); user-select: none; }
.breadcrumb-actual { display: inline-flex; align-items: center; color: #2F80ED; font-size: 0.875rem; font-weight: 600; line-height: 1; white-space: nowrap; }

/* ============================================= */
/* HEADER                                        */
/* ============================================= */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.2rem; }
.page-title { color: #333333; font-size: 1.75rem; font-weight: 700; line-height: 1.2; letter-spacing: -0.02em; margin: 0; }
.page-subtitle { display: block; color: #4F4F4F; font-size: 0.875rem; font-weight: 400; line-height: 1.45; margin-top: 4px; }

/* ============================================= */
/* BOTONES                                       */
/* ============================================= */
.btn-nuevo, .btn-limpiar, .btn-guardar, .btn-secundario, .btn-accion { display: inline-flex; align-items: center; justify-content: center; gap: 7px; border-radius: 9px; font-size: 0.85rem; font-weight: 600; line-height: 1.2; letter-spacing: 0.01em; text-transform: uppercase; cursor: pointer; transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease; white-space: nowrap; }
.btn-nuevo { background: #1D52B7; color: #FFFFFF; border: none; padding: 11px 20px; }
.btn-nuevo:hover { background: #2F80ED; }
.btn-icono-texto { font-size: 1.1rem; font-weight: 700; line-height: 1; }
.btn-limpiar { height: 43px; align-self: end; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); padding: 0 18px; }
.btn-limpiar:hover { background: rgba(47,128,237,0.15); border-color: #2F80ED; }
.btn-guardar { background: #1D52B7; color: #FFFFFF; border: none; padding: 11px 20px; }
.btn-guardar:hover { background: #2F80ED; }
.btn-secundario { background: #FFFFFF; color: #4F4F4F; border: 1px solid #E0E0E0; padding: 11px 20px; }
.btn-secundario:hover { background: #F2F4F7; }
.btn-accion { border: 1px solid transparent; padding: 7px 12px; font-size: 0.75rem; }
.btn-accion.ver { background: rgba(47,128,237,0.10); color: #1D52B7; border-color: rgba(47,128,237,0.25); }
.btn-accion.editar { background: rgba(39,174,96,0.10); color: #27AE60; border-color: rgba(39,174,96,0.30); }
.btn-accion:hover { filter: brightness(0.98); }

/* ============================================= */
/* NOTIFICACIÓN                                  */
/* ============================================= */
.notificacion { padding: 12px 18px; border-radius: 10px; font-size: 0.875rem; font-weight: 600; line-height: 1.4; margin-bottom: 1rem; }
.notificacion.exito { background: rgba(39,174,96,0.10); color: #27AE60; border: 1px solid rgba(39,174,96,0.35); }
.notificacion.error { background: #FFF0F0; color: #EB5757; border: 1px solid rgba(235,87,87,0.35); }
.fade-enter-active, .fade-leave-active { transition: all 0.25s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-6px); }

/* ============================================= */
/* KPIS                                          */
/* ============================================= */
.kpis-grid { display: grid; grid-template-columns: repeat(4, minmax(130px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
.kpi-card { background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 12px; padding: 1rem; text-align: center; box-shadow: 0 4px 14px rgba(29,82,183,0.05); }
.kpi-numero { display: block; color: #333333; font-size: 2rem; font-weight: 700; line-height: 1.1; }
.kpi-label { display: block; color: #828282; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.05em; margin-top: 4px; }

/* ============================================= */
/* FILTROS                                       */
/* ============================================= */
.filtros-card { display: grid; grid-template-columns: minmax(260px, 1fr) 220px auto; gap: 1rem; align-items: end; background: #FFFFFF; border-radius: 12px; padding: 1.3rem 1.6rem; margin-bottom: 1rem; box-shadow: 0 4px 14px rgba(29,82,183,0.05); border: 1px solid #E0E0E0; }
.campo-grupo { display: flex; flex-direction: column; gap: 6px; }
.campo-grupo label { color: #333333; font-size: 0.8rem; font-weight: 600; line-height: 1.3; letter-spacing: 0.02em; }
.input-con-icono { position: relative; }
.input-icono { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; stroke: #828282; pointer-events: none; }
.input-con-icono input, .campo-grupo input, .campo-grupo select, .select-control { width: 100%; padding: 11px 14px; border: 1.5px solid #E0E0E0; border-radius: 9px; background: #FFFFFF; color: #333333; font-size: 0.875rem; font-weight: 400; line-height: 1.4; text-transform: uppercase; outline: none; transition: border-color 0.2s ease, box-shadow 0.2s ease; }
.input-con-icono input { padding-left: 42px; }
.input-con-icono input::placeholder { color: #828282; font-size: 0.875rem; font-weight: 400; }
.input-con-icono input:focus, .campo-grupo input:focus, .campo-grupo select:focus, .select-control:focus { border-color: #1D52B7; box-shadow: 0 0 0 3px rgba(29,82,183,0.15); }

/* ============================================= */
/* VALIDACIONES                                  */
/* ============================================= */
.validaciones-card { display: grid; grid-template-columns: repeat(2, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
.validacion-item { display: flex; align-items: flex-start; gap: 12px; background: #FFFFFF; border-radius: 12px; border: 1px solid #E0E0E0; padding: 1rem; box-shadow: 0 4px 14px rgba(29,82,183,0.05); }
.validacion-item.correcto { border-color: rgba(39,174,96,0.35); background: rgba(39,174,96,0.06); }
.validacion-item.alerta { border-color: rgba(235,87,87,0.35); background: #FFF0F0; }
.validacion-icono { display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; min-width: 30px; border-radius: 999px; color: #FFFFFF; background: #27AE60; font-weight: 800; }
.validacion-item.alerta .validacion-icono { background: #EB5757; }
.validacion-item strong { display: block; color: #333333; font-size: 0.85rem; font-weight: 700; margin-bottom: 3px; }
.validacion-item span { display: block; color: #4F4F4F; font-size: 0.8rem; line-height: 1.4; }

/* ============================================= */
/* TABLA                                         */
/* ============================================= */
.tabla-card { background: #FFFFFF; border: 1px solid #E0E0E0; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 14px rgba(29,82,183,0.05); }
.tabla-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.2rem; background: #FFFFFF; border-bottom: 1px solid #E0E0E0; }
.tabla-header h2 { color: #333333; font-size: 1rem; font-weight: 700; margin: 0; }
.tabla-header span { display: block; color: #828282; font-size: 0.75rem; font-weight: 600; margin-top: 3px; }
.tabla-scroll { width: 100%; overflow-x: auto; }
.tabla-periodos { width: 100%; border-collapse: collapse; }
.tabla-periodos th { background: #F2F4F7; padding: 13px 14px; text-align: left; color: #333333; font-size: 0.78rem; font-weight: 600; line-height: 1.3; letter-spacing: 0.02em; border-bottom: 1px solid #E0E0E0; white-space: nowrap; }
.tabla-periodos td { padding: 13px 14px; border-bottom: 1px solid #E0E0E0; color: #333333; font-size: 0.85rem; font-weight: 400; line-height: 1.4; vertical-align: middle; }
.tabla-periodos tbody tr:hover { background: rgba(29,82,183,0.05); }
.tabla-periodos tbody tr:last-child td { border-bottom: none; }
.text-center { text-align: center; }
.periodo-nombre strong { display: block; color: #333333; font-weight: 700; }
.periodo-nombre span { display: block; color: #828282; font-size: 0.72rem; margin-top: 2px; }
.duracion-badge { display: inline-flex; align-items: center; justify-content: center; background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); border-radius: 999px; padding: 4px 10px; font-size: 0.76rem; font-weight: 700; white-space: nowrap; }
.estatus-badge { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: 4px 10px; font-size: 0.76rem; font-weight: 700; white-space: nowrap; }
.estatus-badge.activo { background: rgba(39,174,96,0.10); color: #27AE60; border: 1px solid rgba(39,174,96,0.35); }
.estatus-badge.programado { background: rgba(47,128,237,0.10); color: #1D52B7; border: 1px solid rgba(47,128,237,0.25); }
.estatus-badge.finalizado { background: #F2F4F7; color: #4F4F4F; border: 1px solid #E0E0E0; }
.estatus-badge.inactivo { background: #FFF0F0; color: #EB5757; border: 1px solid rgba(235,87,87,0.35); }
.acciones-tabla { display: inline-flex; align-items: center; justify-content: center; gap: 6px; }

/* ============================================= */
/* SWITCH                                        */
/* ============================================= */
.switch-activo { width: 46px; height: 24px; border-radius: 999px; border: none; padding: 3px; background: #BDBDBD; cursor: pointer; transition: background 0.2s ease; }
.switch-activo span { display: block; width: 18px; height: 18px; border-radius: 50%; background: #FFFFFF; transition: transform 0.2s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.15); }
.switch-activo.activo { background: #27AE60; }
.switch-activo.activo span { transform: translateX(22px); }

/* ============================================= */
/* ESTADO VACÍO                                  */
/* ============================================= */
.estado-vacio { text-align: center; padding: 3.5rem 2rem; color: #4F4F4F; background: #FFFFFF; border-radius: 12px; }
.icono-vacio { width: 60px; height: 60px; stroke: #BDBDBD; margin-bottom: 14px; }
.estado-vacio h3 { color: #333333; font-size: 1rem; font-weight: 600; margin: 0 0 6px; }
.estado-vacio p { color: #4F4F4F; font-size: 0.875rem; font-weight: 400; margin: 0; }

/* ============================================= */
/* MODALES                                       */
/* ============================================= */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.55); display: flex; align-items: center; justify-content: center; padding: 1rem; z-index: 2000; }
.modal-card { width: min(720px, 96vw); max-height: 92vh; overflow-y: auto; background: #FFFFFF; border-radius: 16px; border: 1px solid #E0E0E0; box-shadow: 0 20px 45px rgba(0,0,0,0.20); }
.modal-detalle { width: min(760px, 96vw); }
.modal-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.4rem 1.6rem; border-bottom: 1px solid #E0E0E0; background: #F8FAFC; }
.modal-header h2 { color: #333333; font-size: 1.15rem; font-weight: 700; margin: 0; }
.modal-header span { display: block; color: #4F4F4F; font-size: 0.8rem; font-weight: 500; margin-top: 3px; }
.btn-cerrar { width: 34px; height: 34px; border: none; background: #FFFFFF; color: #4F4F4F; border-radius: 8px; font-size: 1.4rem; line-height: 1; cursor: pointer; border: 1px solid #E0E0E0; }
.btn-cerrar:hover { color: #EB5757; background: #FFF0F0; }
.formulario-periodo { padding: 1.5rem 1.6rem; }
.form-grid { display: grid; grid-template-columns: repeat(2, minmax(180px, 1fr)); gap: 1rem; margin-top: 1rem; }
.check-card { display: flex; align-items: center; gap: 10px; min-height: 43px; padding: 10px 12px; border: 1.5px solid #E0E0E0; border-radius: 9px; background: #FFFFFF; }
.check-card input { width: 18px; height: 18px; accent-color: #1D52B7; }
.check-card label { color: #333333; font-size: 0.8rem; font-weight: 600; cursor: pointer; }
.errores-card, .advertencias-card { margin-top: 1rem; border-radius: 10px; padding: 1rem; }
.errores-card { background: #FFF0F0; border: 1px solid rgba(235,87,87,0.35); color: #EB5757; }
.advertencias-card { background: rgba(242,153,74,0.10); border: 1px solid rgba(242,153,74,0.35); color: #B65E00; }
.errores-card strong, .advertencias-card strong { display: block; font-size: 0.82rem; margin-bottom: 6px; }
.errores-card ul, .advertencias-card ul { margin: 0; padding-left: 1.2rem; }
.errores-card li, .advertencias-card li { font-size: 0.82rem; line-height: 1.5; }
.modal-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 1.2rem 1.6rem; border-top: 1px solid #E0E0E0; background: #FFFFFF; }

/* ============================================= */
/* DETALLE                                       */
/* ============================================= */
.detalle-grid { display: grid; grid-template-columns: repeat(2, minmax(180px, 1fr)); gap: 1rem; padding: 1.5rem 1.6rem; }
.detalle-item { background: #F4F6F9; border: 1px solid #E0E0E0; border-radius: 10px; padding: 1rem; }
.detalle-item span { display: block; color: #828282; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.05em; margin-bottom: 5px; }
.detalle-item strong { display: block; color: #333333; font-size: 0.9rem; font-weight: 700; }
.detalle-validaciones { padding: 0 1.6rem 1.5rem; }
.detalle-validaciones h3 { color: #333333; font-size: 0.95rem; font-weight: 700; margin: 0 0 10px; }
.validacion-mini { border-radius: 10px; padding: 0.9rem 1rem; margin-bottom: 8px; border: 1px solid #E0E0E0; }
.validacion-mini.correcto { background: rgba(39,174,96,0.06); border-color: rgba(39,174,96,0.35); }
.validacion-mini.alerta { background: #FFF0F0; border-color: rgba(235,87,87,0.35); }
.validacion-mini strong { display: block; color: #333333; font-size: 0.82rem; margin-bottom: 3px; }
.validacion-mini span { color: #4F4F4F; font-size: 0.8rem; }

/* ============================================= */
/* TRANSICIONES                                  */
/* ============================================= */
.modal-enter-active, .modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

/* ============================================= */
/* FOOTER                                        */
/* ============================================= */
.footer-institucional { text-align: center; color: #828282; font-size: 0.82rem; font-weight: 400; padding-top: 2rem; margin-top: 1rem; }

/* ============================================= */
/* RESPONSIVE                                    */
/* ============================================= */
@media (max-width: 900px) {
  .page-header { flex-direction: column; }
  .btn-nuevo { width: 100%; }
  .kpis-grid { grid-template-columns: repeat(2, minmax(130px, 1fr)); }
  .filtros-card { grid-template-columns: 1fr; }
  .validaciones-card { grid-template-columns: 1fr; }
  .form-grid, .detalle-grid { grid-template-columns: 1fr; }
  .modal-footer { flex-direction: column-reverse; }
  .btn-secundario, .btn-guardar { width: 100%; }
}

@media (max-width: 520px) {
  .kpis-grid { grid-template-columns: 1fr; }
  .acciones-tabla { flex-direction: column; }
  .btn-accion { width: 100%; }
}
</style>