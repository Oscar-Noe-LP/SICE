<!-- ============================================================ -->
<!-- src/views/FormularioAlumnoView.vue                         -->
<!-- Módulo: Servicios Escolares — Nueva Inscripción de Alumno  -->
<!-- Autor: Diego (SICE) | Refactorizado: Junio 2025            -->
<!-- ============================================================ -->
<template>
  <MainLayout>
    <div class="formulario-page">

      <!-- ── Breadcrumb ── -->
      <nav class="breadcrumb" aria-label="Ruta de navegación">
        <span class="breadcrumb-link" @click="cancelar" role="button" tabindex="0">Servicios Escolares</span>
        <span class="breadcrumb-sep" aria-hidden="true">›</span>
        <span class="breadcrumb-link" @click="cancelar" role="button" tabindex="0">Alumnos</span>
        <span class="breadcrumb-sep" aria-hidden="true">›</span>
        <span class="breadcrumb-actual">Nueva Inscripción</span>
      </nav>

      <!-- ── Encabezado de página ── -->
      <div class="page-header">
        <div class="page-header-texto">
          <h1 class="page-title">Nueva Inscripción</h1>
          <p class="subtitle">
            Complete todos los campos marcados con
            <span class="obligatorio" aria-label="campo obligatorio">*</span>
            para registrar al alumno.
          </p>
        </div>
        <!-- Indicador de progreso del formulario -->
        <div class="progreso-wrap" aria-label="Progreso del formulario">
          <span class="progreso-label">{{ camposCompletados }}/{{ totalCampos }} campos</span>
          <div class="progreso-barra-track">
            <div class="progreso-barra-fill" :style="{ width: porcentajeProgreso + '%' }"></div>
          </div>
        </div>
      </div>

      <!-- ── Toast de notificaciones ── -->
      <transition name="toast-slide">
        <div
          v-if="notification.message"
          class="toast"
          :class="notification.type"
          role="alert"
          aria-live="polite"
        >
          <!-- Ícono éxito -->
          <svg v-if="notification.type === 'success'" class="toast-icono" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <!-- Ícono error -->
          <svg v-else class="toast-icono" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ notification.message }}
        </div>
      </transition>

      <!-- ── Alerta de error al cargar catálogos ── -->
      <transition name="fade">
        <div v-if="errorCatalogos" class="alerta-error" role="alert">
          <svg class="alerta-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>No se pudieron cargar los catálogos. Verifica la conexión con el servidor.</span>
          <button class="btn-reintentar" @click="cargarCatalogos">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Reintentar
          </button>
        </div>
      </transition>

      <!-- ── Skeleton de carga de catálogos ── -->
      <div v-if="cargandoCatalogos" class="skeleton-form" aria-busy="true" aria-label="Cargando formulario">
        <div class="sk-header"></div>
        <div class="sk-campos">
          <div class="sk-campo" v-for="i in 6" :key="i"></div>
        </div>
        <div class="sk-botones"></div>
      </div>

      <!-- ── Formulario principal ── -->
      <div v-else class="form-card">

        <!-- ════ SECCIÓN 1: Información Personal ════ -->
        <section class="seccion">
          <h2 class="seccion-titulo">
            <span class="seccion-num">01</span>
            <svg class="seccion-icono" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Información Personal
          </h2>

          <div class="fila-campos">
            <!-- Nombre -->
            <div class="campo" :class="estadoCampoClase('nombre')">
              <label class="etiqueta" for="campo-nombre">
                Nombre <span class="obligatorio" aria-label="obligatorio">*</span>
              </label>
              <div class="input-wrap">
                <input
                  id="campo-nombre"
                  type="text"
                  v-model.trim="form.nombre"
                  class="input-campo"
                  :class="inputClase('nombre')"
                  placeholder="Ej. Juan Carlos"
                  autocomplete="given-name"
                  @input="validarCampo('nombre')"
                  @blur="validarCampo('nombre')"
                />
                <span v-if="campoValido('nombre')" class="icono-validacion valido" aria-label="Campo válido">✓</span>
              </div>
              <transition name="error-fade">
                <small v-if="errors.nombre" class="mensaje-error" role="alert">{{ errors.nombre }}</small>
              </transition>
            </div>

            <!-- Apellido Paterno -->
            <div class="campo" :class="estadoCampoClase('apellidoPaterno')">
              <label class="etiqueta" for="campo-ap">
                Apellido Paterno <span class="obligatorio">*</span>
              </label>
              <div class="input-wrap">
                <input
                  id="campo-ap"
                  type="text"
                  v-model.trim="form.apellidoPaterno"
                  class="input-campo"
                  :class="inputClase('apellidoPaterno')"
                  placeholder="Ej. García"
                  autocomplete="family-name"
                  @input="validarCampo('apellidoPaterno')"
                  @blur="validarCampo('apellidoPaterno')"
                />
                <span v-if="campoValido('apellidoPaterno')" class="icono-validacion valido">✓</span>
              </div>
              <transition name="error-fade">
                <small v-if="errors.apellidoPaterno" class="mensaje-error" role="alert">{{ errors.apellidoPaterno }}</small>
              </transition>
            </div>

            <!-- Apellido Materno -->
            <div class="campo">
              <label class="etiqueta" for="campo-am">
                Apellido Materno
                <span class="etiqueta-opcional">(opcional)</span>
              </label>
              <input
                id="campo-am"
                type="text"
                v-model.trim="form.apellidoMaterno"
                class="input-campo"
                placeholder="Ej. López"
                autocomplete="additional-name"
              />
            </div>
          </div>

          <!-- Género -->
          <div class="fila-campos fila-parcial">
            <div class="campo" :class="estadoCampoClase('genero')">
              <label class="etiqueta" for="campo-genero">
                Género <span class="obligatorio">*</span>
              </label>
              <select
                id="campo-genero"
                v-model="form.genero"
                class="input-campo"
                :class="inputClase('genero')"
                @change="validarCampo('genero')"
              >
                <option value="" disabled>— Seleccionar género —</option>
                <option v-for="g in generos" :key="g.id_genero" :value="g.nombre_genero">
                  {{ g.nombre_genero }}
                </option>
              </select>
              <transition name="error-fade">
                <small v-if="errors.genero" class="mensaje-error" role="alert">{{ errors.genero }}</small>
              </transition>
            </div>
          </div>
        </section>

        <!-- ════ SECCIÓN 2: Datos Académicos ════ -->
        <section class="seccion">
          <h2 class="seccion-titulo">
            <span class="seccion-num">02</span>
            <svg class="seccion-icono" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            Datos Académicos
          </h2>

          <div class="fila-campos">
            <!-- Número de control -->
            <div class="campo" :class="estadoCampoClase('noControl')">
              <label class="etiqueta" for="campo-sufijo">
                Número de Control <span class="obligatorio">*</span>
                <span class="etiqueta-hint">Año {{ anioActual }} — prefijo <strong>{{ prefijoAnio }}</strong></span>
              </label>
              <div class="input-con-prefijo" :class="inputClase('noControl')">
                <span class="prefijo-fijo" aria-hidden="true">{{ prefijoAnio }}</span>
                <input
                  id="campo-sufijo"
                  type="text"
                  v-model="sufijoCodigo"
                  class="input-con-prefijo-campo"
                  placeholder="000000"
                  maxlength="6"
                  inputmode="numeric"
                  aria-label="Últimos 6 dígitos del número de control"
                  @input="aplicarMascaraControl"
                  @blur="validarCampo('noControl')"
                />
              </div>
              <small v-if="form.noControl && !errors.noControl" class="vista-previa-control">
                Número completo: <strong>{{ form.noControl }}</strong>
              </small>
              <transition name="error-fade">
                <small v-if="errors.noControl" class="mensaje-error" role="alert">{{ errors.noControl }}</small>
              </transition>
            </div>

            <!-- Carrera -->
            <div class="campo" :class="estadoCampoClase('carrera')">
              <label class="etiqueta" for="campo-carrera">
                Carrera <span class="obligatorio">*</span>
              </label>
              <select
                id="campo-carrera"
                v-model="form.id_carrera"
                class="input-campo"
                :class="inputClase('carrera')"
                @change="validarCampo('carrera')"
              >
                <option value="" disabled>— Seleccionar carrera —</option>
                <option v-for="c in carreras" :key="c.id_carrera" :value="c.id_carrera">
                  {{ c.nombre }}
                </option>
              </select>
              <transition name="error-fade">
                <small v-if="errors.carrera" class="mensaje-error" role="alert">{{ errors.carrera }}</small>
              </transition>
            </div>

            <!-- Semestre -->
            <div class="campo" :class="estadoCampoClase('semestre')">
              <label class="etiqueta" for="campo-semestre">
                Semestre <span class="obligatorio">*</span>
              </label>
              <select
                id="campo-semestre"
                v-model="form.semestre"
                class="input-campo"
                :class="inputClase('semestre')"
                @change="validarCampo('semestre')"
              >
                <option value="" disabled>— Seleccionar —</option>
                <option v-for="n in 8" :key="n" :value="n">{{ n }}° Semestre</option>
              </select>
              <transition name="error-fade">
                <small v-if="errors.semestre" class="mensaje-error" role="alert">{{ errors.semestre }}</small>
              </transition>
            </div>
          </div>
        </section>

        <!-- ════ SECCIÓN 3: Estado y Fecha de Ingreso ════ -->
        <section class="seccion seccion-sin-borde">
          <h2 class="seccion-titulo">
            <span class="seccion-num">03</span>
            <svg class="seccion-icono" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Estado y Fecha de Ingreso
          </h2>

          <div class="fila-campos fila-parcial">
            <!-- Estatus -->
            <div class="campo">
              <label class="etiqueta" for="campo-estatus">
                Estatus <span class="obligatorio">*</span>
              </label>
              <select id="campo-estatus" v-model="form.id_estatus_alumno" class="input-campo">
                <option v-for="e in estatusOptions" :key="e.id_estatus_alumno" :value="e.id_estatus_alumno">
                  {{ e.nombre }}
                </option>
              </select>
              <!-- Badge dinámico de estatus -->
              <div class="indicador-estatus" :class="getEstatusClass(form.id_estatus_alumno)" role="status">
                <span class="indicador-dot" aria-hidden="true"></span>
                {{ getEstatusNombre(form.id_estatus_alumno) }}
              </div>
            </div>

            <!-- Fecha de Ingreso -->
            <div class="campo" :class="estadoCampoClase('fechaIngreso')">
              <label class="etiqueta" for="campo-fecha">
                Fecha de Ingreso <span class="obligatorio">*</span>
              </label>
              <input
                id="campo-fecha"
                type="date"
                v-model="form.fechaIngreso"
                class="input-campo"
                :class="inputClase('fechaIngreso')"
                :max="hoyISO"
                @change="validarCampo('fechaIngreso')"
              />
              <transition name="error-fade">
                <small v-if="errors.fechaIngreso" class="mensaje-error" role="alert">{{ errors.fechaIngreso }}</small>
              </transition>
            </div>
          </div>
        </section>

        <!-- ── Acciones ── -->
        <div class="form-acciones">
          <button class="btn-cancelar" @click="cancelar" :disabled="isLoading" type="button">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Cancelar
          </button>

          <button
            class="btn-guardar"
            @click="guardarAlumno"
            :disabled="isLoading"
            type="button"
            :aria-busy="isLoading"
          >
            <span v-if="isLoading" class="spinner" aria-hidden="true"></span>
            <svg v-else fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ isLoading ? 'Guardando...' : 'Guardar registro' }}
          </button>
        </div>

      </div><!-- /form-card -->

    </div>
  </MainLayout>
</template>

<script setup>
/**
 * FormularioAlumnoView.vue
 * Vista para el registro de nuevos alumnos en el sistema SICE.
 *
 * Responsable: Diego
 * Módulo: Búsqueda y visualización de alumno
 *
 * Mejoras aplicadas:
 * - Validación en tiempo real con mensajes claros
 * - Progreso visual del formulario
 * - Skeleton de carga mientras se obtienen catálogos
 * - Manejo de errores mejorado (duplicados, conexión, backend)
 * - Accesibilidad: aria-labels, roles, id en inputs
 * - Código limpio con JSDoc en funciones clave
 */
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

// ── Composables / Utilidades ────────────────────────────────────────────
const router = useRouter()
const API_URL = import.meta.env.VITE_API_URL

// ── Constantes de fecha ─────────────────────────────────────────────────
const anioActual  = new Date().getFullYear()
const prefijoAnio = String(anioActual).slice(-2)
const hoyISO      = new Date().toISOString().split('T')[0]

// ── Estado de catálogos ─────────────────────────────────────────────────
const generos          = ref([])
const carreras         = ref([])
const estatusOptions   = ref([])
const cargandoCatalogos = ref(false)
const errorCatalogos   = ref(false)

// ── Estado del número de control ────────────────────────────────────────
const sufijoCodigo = ref('')

// ── Formulario principal ────────────────────────────────────────────────
const form = reactive({
  nombre:           '',
  apellidoPaterno:  '',
  apellidoMaterno:  '',
  genero:           '',
  noControl:        '',
  id_carrera:       '',
  semestre:         '',
  id_estatus_alumno: 1,   // Por defecto: Activo
  fechaIngreso:     ''
})

const errors       = reactive({})
const tocados      = reactive({})
const notification = reactive({ message: '', type: '' })
const isLoading    = ref(false)

// ── Progreso visual del formulario ──────────────────────────────────────
/** Lista de campos obligatorios para calcular el progreso */
const camposObligatorios = ['nombre', 'apellidoPaterno', 'genero', 'noControl', 'id_carrera', 'semestre', 'fechaIngreso']
const totalCampos = computed(() => camposObligatorios.length)
const camposCompletados = computed(() =>
  camposObligatorios.filter(c => {
    if (c === 'noControl') return sufijoCodigo.value.length === 6
    return !!form[c]
  }).length
)
const porcentajeProgreso = computed(() =>
  Math.round((camposCompletados.value / totalCampos.value) * 100)
)

// ── Carga de catálogos ──────────────────────────────────────────────────
/**
 * Obtiene los catálogos (géneros, carreras, estatus) desde el backend.
 * En caso de error, muestra alerta y permite reintentar.
 */
const cargarCatalogos = async () => {
  cargandoCatalogos.value = true
  errorCatalogos.value    = false

  try {
    const res = await fetch(`${API_URL}/api/alumnos/catalogos`)
    if (!res.ok) throw new Error('Error al cargar catálogos')

    const data = await res.json()
    generos.value        = data.generos         || []
    carreras.value       = data.carreras        || []
    estatusOptions.value = data.estatus_alumno  || []
  } catch (err) {
    console.error('[FormularioAlumno] Error al cargar catálogos:', err)
    errorCatalogos.value = true
  } finally {
    cargandoCatalogos.value = false
  }
}

onMounted(cargarCatalogos)

// ── Máscara de número de control ────────────────────────────────────────
/** Solo permite dígitos, máximo 6, y construye el número completo */
const aplicarMascaraControl = () => {
  sufijoCodigo.value = sufijoCodigo.value.replace(/\D/g, '').slice(0, 6)
  form.noControl = prefijoAnio + sufijoCodigo.value
  validarCampo('noControl')
}

// ── Validaciones ────────────────────────────────────────────────────────
/** Valida un campo específico y actualiza el estado de errores */
const validarCampo = (campo) => {
  tocados[campo] = true

  const reglas = {
    nombre: () => {
      if (!form.nombre.trim())             return 'El nombre es obligatorio'
      if (form.nombre.trim().length < 2)   return 'Debe tener al menos 2 caracteres'
    },
    apellidoPaterno: () => {
      if (!form.apellidoPaterno.trim())           return 'El apellido paterno es obligatorio'
      if (form.apellidoPaterno.trim().length < 2) return 'Debe tener al menos 2 caracteres'
    },
    genero: () => {
      if (!form.genero) return 'Seleccione un género'
    },
    noControl: () => {
      if (sufijoCodigo.value.length < 6)     return `Ingresa los 6 dígitos faltantes (prefijo ${prefijoAnio} + 6)`
      if (!/^\d{8}$/.test(form.noControl))   return 'Solo se permiten números'
    },
    carrera: () => {
      if (!form.id_carrera) return 'Seleccione una carrera'
    },
    semestre: () => {
      if (!form.semestre) return 'Seleccione un semestre'
    },
    fechaIngreso: () => {
      if (!form.fechaIngreso)              return 'La fecha de ingreso es obligatoria'
      if (form.fechaIngreso > hoyISO)      return 'La fecha no puede ser futura'
    }
  }

  const mensajeError = reglas[campo]?.()
  if (mensajeError) {
    errors[campo] = mensajeError
  } else {
    delete errors[campo]
  }
}

/** Devuelve true si el campo fue tocado y no tiene errores */
const campoValido    = (campo) => tocados[campo] && !errors[campo]

/** Valida todos los campos requeridos */
const validarFormulario = () => {
  const requeridos = ['nombre', 'apellidoPaterno', 'genero', 'noControl', 'carrera', 'semestre', 'fechaIngreso']
  requeridos.forEach(validarCampo)
  return Object.keys(errors).length === 0
}

// ── Helpers de clases ───────────────────────────────────────────────────
/** Clase del contenedor del campo según su estado */
const estadoCampoClase = (campo) => ({
  'campo-error': errors[campo],
  'campo-valido': campoValido(campo)
})

/** Clase del input según su estado */
const inputClase = (campo) => ({
  'borde-error': errors[campo],
  'borde-valido': campoValido(campo)
})

// ── Estatus helpers ─────────────────────────────────────────────────────
const getEstatusNombre = (id) => {
  const e = estatusOptions.value.find(x => x.id_estatus_alumno === id)
  return e?.nombre || 'Sin estatus'
}

const getEstatusClass = (id) => {
  const nombre = getEstatusNombre(id).toLowerCase()
  if (nombre.includes('activo'))              return 'activo'
  if (nombre.includes('temporal'))            return 'baja-temporal'
  if (nombre.includes('definitiva'))          return 'baja-definitiva'
  if (nombre.includes('titulado'))            return 'titulado'
  if (nombre.includes('egresado'))            return 'egresado'
  return ''
}

// ── Notificaciones ──────────────────────────────────────────────────────
let notifTimer = null
const showNotification = (message, type = 'success', duracion = 4000) => {
  notification.message = message
  notification.type    = type
  clearTimeout(notifTimer)
  notifTimer = setTimeout(() => { notification.message = '' }, duracion)
}

// ── Extraer mensaje de error del backend ────────────────────────────────
const extraerMensajeError = (data, noControl) => {
  const raw = data?.message || data?.error || data?.errors?.[0] || ''
  if (typeof raw === 'string' && (raw.toLowerCase().includes('duplicate') || raw.toLowerCase().includes('ya existe'))) {
    return `El número de control ${noControl} ya está registrado en el sistema`
  }
  return raw || 'Ocurrió un error al guardar. Intenta de nuevo.'
}

const esDuplicadoControl = (data) => {
  const raw = JSON.stringify(data).toLowerCase()
  return raw.includes('duplicate') || raw.includes('ya existe') || raw.includes('unique')
}

// ── Guardar alumno ──────────────────────────────────────────────────────
/**
 * Envía el formulario al backend.
 * Valida localmente antes del request.
 * Maneja duplicados, errores de red y errores de servidor.
 */
const guardarAlumno = async () => {
  if (!validarFormulario()) {
    showNotification('Corrige los errores marcados antes de continuar', 'error')
    // Scroll al primer error
    const primerError = document.querySelector('.campo-error')
    primerError?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    return
  }

  isLoading.value = true

  const payload = {
    numero_control:    form.noControl,
    nombre:            form.nombre.trim(),
    apellido_paterno:  form.apellidoPaterno.trim(),
    apellido_materno:  form.apellidoMaterno.trim() || null,
    genero:            form.genero,
    id_carrera:        form.id_carrera,
    semestre_actual:   parseInt(form.semestre),
    id_estatus_alumno: form.id_estatus_alumno,
    fecha_ingreso:     form.fechaIngreso
  }

  try {
    const res = await fetch(`${API_URL}/api/alumnos`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body:    JSON.stringify(payload)
    })

    const data = await res.json()

    if (res.ok) {
      showNotification('¡Alumno registrado exitosamente!', 'success')
      setTimeout(() => router.push('/alumnos'), 1500)
    } else {
      const msg = extraerMensajeError(data, form.noControl)
      showNotification(msg, 'error')

      if (esDuplicadoControl(data)) {
        errors.noControl  = `El número de control ${form.noControl} ya está registrado`
        tocados.noControl = true
      }
    }
  } catch (err) {
    console.error('[FormularioAlumno] Error de red:', err)
    showNotification('No se pudo conectar al servidor. Verifica tu conexión.', 'error')
  } finally {
    isLoading.value = false
  }
}

// ── Cancelar / Limpiar ──────────────────────────────────────────────────
const cancelar = () => router.push('/alumnos')
</script>

<style scoped>
/* ══════════════════════════════════════════════════════
   VARIABLES GLOBALES — paleta SICE
══════════════════════════════════════════════════════ */
.formulario-page {
  --azul:       #1B396A;
  --azul-hover: #15305A;
  --azul-suave: #DBEAFE;
  --verde:      #16A34A;
  --rojo:       #DC2626;
  --borde:      #E5E7EB;
  --fondo:      #F9FAFB;
  --texto:      #111827;
  --gris:       #6B7280;
  --radio:      10px;

  padding-bottom: 2rem;
  font-family: 'Montserrat', sans-serif;
}

/* ── Breadcrumb ── */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  color: var(--gris);
  margin-bottom: 1.2rem;
  flex-wrap: wrap;
}
.breadcrumb-link {
  cursor: pointer;
  color: var(--gris);
  transition: color 0.15s;
}
.breadcrumb-link:hover { color: var(--azul); }
.breadcrumb-sep        { color: #D1D5DB; }
.breadcrumb-actual     { color: var(--azul); font-weight: 600; }

/* ── Encabezado ── */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1.5rem;
  margin-bottom: 1.8rem;
  flex-wrap: wrap;
}
.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--texto);
  letter-spacing: -0.02em;
  margin: 0 0 4px;
  font-family: 'Montserrat', sans-serif;
}
.subtitle {
  font-size: 0.88rem;
  color: var(--gris);
  margin: 0;
}
.obligatorio { color: var(--rojo); font-weight: 700; }

/* Progreso del formulario */
.progreso-wrap {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  min-width: 180px;
}
.progreso-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--gris);
}
.progreso-barra-track {
  width: 180px;
  height: 6px;
  background: var(--borde);
  border-radius: 3px;
  overflow: hidden;
}
.progreso-barra-fill {
  height: 100%;
  background: var(--azul);
  border-radius: 3px;
  transition: width 0.4s ease;
}

/* ── Toast ── */
.toast {
  position: fixed;
  top: 80px;
  right: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0.9rem 1.4rem;
  border-radius: var(--radio);
  color: white;
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
  z-index: 3000;
  max-width: 380px;
}
.toast.success { background: var(--verde); }
.toast.error   { background: var(--rojo); }
.toast-icono   { width: 20px; height: 20px; flex-shrink: 0; }

.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.3s ease; }
.toast-slide-enter-from, .toast-slide-leave-to       { opacity: 0; transform: translateX(110%); }

/* ── Alerta de error de catálogos ── */
.alerta-error {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #FEF2F2;
  border: 1px solid #FECACA;
  border-radius: var(--radio);
  padding: 12px 16px;
  margin-bottom: 1.2rem;
  font-size: 0.875rem;
  color: var(--rojo);
  font-weight: 500;
}
.alerta-icono  { width: 20px; height: 20px; flex-shrink: 0; stroke: var(--rojo); }
.btn-reintentar {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--rojo);
  color: white;
  border: none;
  padding: 6px 14px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.82rem;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.15s;
  white-space: nowrap;
}
.btn-reintentar:hover { background: #B91C1C; }

/* ── Skeleton de carga ── */
.skeleton-form { display: flex; flex-direction: column; gap: 1rem; }
.sk-header  { height: 60px; border-radius: 12px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation: shimmer 1.4s infinite; }
.sk-campos  { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; background: white; border-radius: 12px; padding: 1.5rem; border: 1px solid var(--borde); }
.sk-campo   { height: 68px; border-radius: 8px; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation: shimmer 1.4s infinite; }
.sk-botones { height: 48px; border-radius: 8px; width: 260px; margin-left: auto; background: linear-gradient(90deg,#E5E7EB 25%,#F3F4F6 50%,#E5E7EB 75%); background-size:200% 100%; animation: shimmer 1.4s infinite; }
@keyframes shimmer { 0% { background-position:200% 0; } 100% { background-position:-200% 0; } }

/* ── Card del formulario ── */
.form-card {
  background: #FFFFFF;
  border-radius: 16px;
  border: 1px solid var(--borde);
  box-shadow: 0 4px 16px rgba(0,0,0,0.06);
  padding: 2rem 2.4rem;
}

/* ── Secciones ── */
.seccion {
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid var(--borde);
}
.seccion-sin-borde { border-bottom: none; margin-bottom: 1rem; }

.seccion-titulo {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1rem;
  font-weight: 700;
  color: var(--texto);
  margin: 0 0 1.4rem;
  font-family: 'Montserrat', sans-serif;
}
.seccion-num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 26px;
  height: 26px;
  border-radius: 6px;
  background: var(--azul);
  color: white;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0;
  flex-shrink: 0;
}
.seccion-icono { width: 18px; height: 18px; stroke: var(--azul); flex-shrink: 0; }

/* ── Grid de campos ── */
.fila-campos {
  display: flex;
  flex-wrap: wrap;
  gap: 1.2rem;
}
.fila-campos .campo { flex: 1 1 200px; min-width: 0; }

.fila-parcial .campo {
  flex: 0 0 calc(33.33% - 0.85rem);
  min-width: 180px;
}

/* ── Campo ── */
.campo { display: flex; flex-direction: column; gap: 4px; }

.etiqueta {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--texto);
}
.etiqueta-opcional {
  font-size: 0.78rem;
  color: var(--gris);
  font-weight: 400;
}
.etiqueta-hint {
  font-size: 0.75rem;
  color: var(--azul);
  background: var(--azul-suave);
  border-radius: 20px;
  padding: 2px 8px;
  font-weight: 600;
  white-space: nowrap;
}

/* Input base */
.input-campo {
  width: 100%;
  padding: 10px 14px;
  border: 1.5px solid var(--borde);
  border-radius: 8px;
  font-size: 0.925rem;
  font-family: inherit;
  color: var(--texto);
  background: #FAFAFA;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.15s;
  box-sizing: border-box;
  outline: none;
}
.input-campo:focus {
  border-color: var(--azul);
  box-shadow: 0 0 0 3px rgba(27,57,106,0.1);
  background: #FFFFFF;
}
.input-campo::placeholder { color: #9CA3AF; }

/* Estados de validación */
.borde-error { border-color: var(--rojo) !important; background: #FFF5F5 !important; }
.borde-error:focus { box-shadow: 0 0 0 3px rgba(220,38,38,0.12) !important; }
.borde-valido { border-color: var(--verde) !important; }
.borde-valido:focus { box-shadow: 0 0 0 3px rgba(22,163,74,0.1) !important; }

.campo-error > .etiqueta  { color: var(--rojo); }
.campo-valido > .etiqueta { color: var(--verde); }

/* Input con ícono de validación */
.input-wrap { position: relative; }
.input-wrap .input-campo { padding-right: 36px; }
.icono-validacion {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.9rem;
  font-weight: 700;
  pointer-events: none;
}
.icono-validacion.valido { color: var(--verde); }

/* Número de control con prefijo */
.input-con-prefijo {
  display: flex;
  align-items: stretch;
  border: 1.5px solid var(--borde);
  border-radius: 8px;
  overflow: hidden;
  background: #FAFAFA;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.input-con-prefijo:focus-within {
  border-color: var(--azul);
  box-shadow: 0 0 0 3px rgba(27,57,106,0.1);
  background: #FFFFFF;
}
.prefijo-fijo {
  padding: 10px 14px;
  background: var(--azul-suave);
  color: var(--azul);
  font-weight: 800;
  font-size: 0.925rem;
  border-right: 1.5px solid var(--borde);
  white-space: nowrap;
  flex-shrink: 0;
  display: flex;
  align-items: center;
}
.input-con-prefijo-campo {
  flex: 1;
  border: none;
  padding: 10px 14px;
  font-size: 0.925rem;
  font-family: 'Montserrat', monospace;
  font-weight: 700;
  color: var(--texto);
  background: transparent;
  outline: none;
  letter-spacing: 0.1em;
}
.input-con-prefijo-campo::placeholder { font-weight: 400; letter-spacing: 0; color: #9CA3AF; }

.campo.campo-error  .input-con-prefijo { border-color: var(--rojo); }
.campo.campo-valido .input-con-prefijo { border-color: var(--verde); }

.vista-previa-control {
  font-size: 0.8rem;
  color: var(--gris);
  margin-top: 4px;
}
.vista-previa-control strong { color: var(--azul); font-weight: 700; }

/* Errores */
.mensaje-error { font-size: 0.8rem; color: var(--rojo); font-weight: 500; }
.error-fade-enter-active, .error-fade-leave-active { transition: all 0.2s ease; }
.error-fade-enter-from, .error-fade-leave-to       { opacity: 0; transform: translateY(-4px); }

/* ── Indicador de estatus ── */
.indicador-estatus {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 8px;
  padding: 5px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
}
.indicador-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}
.indicador-estatus.activo          { background: #DCFCE7; color: #16A34A; }
.indicador-estatus.baja-temporal   { background: #FEF3C7; color: #F59E0B; }
.indicador-estatus.baja-definitiva { background: #FEF2F2; color: #DC2626; }
.indicador-estatus.titulado        { background: #EDE9FE; color: #7C3AED; }
.indicador-estatus.egresado        { background: #DBEAFE; color: #1B396A; }

/* ── Acciones ── */
.form-acciones {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.8rem;
  padding-top: 1.4rem;
  border-top: 1px solid var(--borde);
}
.btn-cancelar {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 11px 22px;
  background: #FFFFFF;
  color: var(--gris);
  border: 1.5px solid var(--borde);
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.15s;
}
.btn-cancelar:hover:not(:disabled) { background: var(--fondo); border-color: #9CA3AF; color: var(--texto); }
.btn-cancelar:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-guardar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 11px 28px;
  background: var(--azul);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.925rem;
  cursor: pointer;
  font-family: inherit;
  transition: background 0.2s, transform 0.1s;
  box-shadow: 0 2px 8px rgba(27,57,106,0.25);
}
.btn-guardar:hover:not(:disabled)  { background: var(--azul-hover); transform: translateY(-1px); }
.btn-guardar:active:not(:disabled) { transform: translateY(0); }
.btn-guardar:disabled { opacity: 0.65; cursor: not-allowed; box-shadow: none; }

/* Spinner */
.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.35);
  border-top-color: white;
  border-radius: 50%;
  animation: girar 0.75s linear infinite;
  flex-shrink: 0;
}
@keyframes girar { to { transform: rotate(360deg); } }

/* Fade utilitario */
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }

/* ══════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════ */
@media (max-width: 1024px) {
  .form-card { padding: 1.6rem 1.8rem; }
  .fila-parcial .campo { flex: 0 0 calc(50% - 0.65rem); }
  .progreso-wrap { min-width: 140px; }
  .progreso-barra-track { width: 140px; }
}

@media (max-width: 768px) {
  .page-header { flex-direction: column; gap: 1rem; }
  .progreso-wrap { align-items: flex-start; }
  .form-card { padding: 1.4rem 1.2rem; }
  .fila-campos .campo { flex: 0 0 calc(50% - 0.6rem); }
  .fila-parcial .campo { flex: 0 0 calc(50% - 0.6rem); }
  .form-acciones { flex-direction: column-reverse; gap: 0.6rem; }
  .btn-cancelar, .btn-guardar { width: 100%; justify-content: center; }
  .toast { top: 70px; right: 12px; left: 12px; max-width: unset; }
}

@media (max-width: 480px) {
  .page-title { font-size: 1.4rem; }
  .form-card { padding: 1.2rem 1rem; border-radius: 12px; }
  .fila-campos .campo { flex: 0 0 100%; }
  .fila-parcial .campo { flex: 0 0 100%; }
  .input-campo, .input-con-prefijo-campo { font-size: 16px !important; } /* evita zoom en iOS */
  .sk-campos { grid-template-columns: 1fr; }
}
</style>