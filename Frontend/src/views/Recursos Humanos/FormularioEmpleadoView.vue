<template>
  <MainLayout>
    <div class="formulario-page">

      <!-- Breadcrumb -->
      <nav class="breadcrumb">
        <router-link to="/recursos-humanos" class="breadcrumb-link">Recursos Humanos</router-link>
        <span class="breadcrumb-sep">›</span>
        <router-link to="/recursos-humanos/empleados" class="breadcrumb-link">Empleados</router-link>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">{{ modoEdicion ? 'Editar Empleado' : 'Nuevo Empleado' }}</span>
      </nav>

      <!-- Encabezado -->
      <div class="page-header">
        <div>
          <h1 class="page-title">{{ modoEdicion ? 'Editar Empleado' : 'Nuevo Empleado' }}</h1>
          <p class="subtitle">Complete todos los campos marcados con <span class="obligatorio">*</span> para registrar al empleado.</p>
        </div>
      </div>

      <!-- Toast -->
      <transition name="toast-slide">
        <div v-if="notification.message" class="toast" :class="notification.type">
          <svg v-if="notification.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="toast-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          {{ notification.message }}
        </div>
      </transition>

      <div class="form-card">

        <!-- ══ SECCIÓN 1: Persona Asociada ══ -->
        <section class="seccion">
          <h2 class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" class="seccion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Persona Asociada
          </h2>

          <!-- Buscador de persona -->
          <div v-if="!personaSeleccionada" class="campo">
            <label class="etiqueta">Buscar persona <span class="obligatorio">*</span></label>
            <div class="search-group">
              <svg xmlns="http://www.w3.org/2000/svg" class="search-icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
              <input
                type="text"
                v-model="busquedaPersona"
                placeholder="Buscar por nombre o número de identificación..."
                class="search-input"
                @input="buscarPersonas"
                @keydown.escape="busquedaPersona = ''; resultadosPersona = []"
              >
              <span v-if="buscandoPersona" class="spinner-busqueda"></span>
            </div>
            <transition name="error-fade">
              <small v-if="errors.persona" class="mensaje-error">{{ errors.persona }}</small>
            </transition>

            <!-- Resultados de búsqueda de personas -->
            <div v-if="resultadosPersona.length > 0" class="resultados-persona">
              <div
                v-for="persona in resultadosPersona"
                :key="persona.id_persona"
                class="resultado-persona-item"
                @click="elegirPersona(persona)"
              >
                <div class="resultado-avatar">{{ persona.nombre.charAt(0).toUpperCase() }}</div>
                <div class="resultado-info">
                  <strong>{{ persona.nombre }}</strong>
                  <span>{{ persona.identificacion || 'Sin identificación' }}</span>
                </div>
                <button class="btn-seleccionar-persona">Seleccionar</button>
              </div>
            </div>
            <div v-else-if="busquedaPersona.length >= 3 && !buscandoPersona && resultadosPersona.length === 0" class="sin-resultados-persona">
              No se encontraron personas con ese criterio.
            </div>
          </div>

          <!-- Persona seleccionada — mismo estilo que alumno-seleccionado en InscripcionView -->
          <div v-if="personaSeleccionada" class="persona-seleccionada">
            <div class="persona-avatar">{{ personaSeleccionada.nombre.charAt(0).toUpperCase() }}</div>
            <div class="persona-datos">
              <strong>{{ personaSeleccionada.nombre }}</strong>
              <span>{{ personaSeleccionada.identificacion || 'Sin identificación registrada' }}</span>
            </div>
            <button class="btn-cambiar-persona" @click="cambiarPersona" :disabled="isLoading">
              Cambiar persona
            </button>
          </div>
        </section>

        <!-- ══ SECCIÓN 2: Datos Laborales ══ -->
        <section class="seccion">
          <h2 class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" class="seccion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Datos Laborales
          </h2>

          <div class="fila-campos">

            <!-- Número de empleado -->
            <div class="campo" :class="{ 'campo-error': errors.noEmpleado, 'campo-valido': campoValido('noEmpleado') }">
              <label class="etiqueta">Número de Empleado <span class="obligatorio">*</span></label>
              <input
                type="text"
                v-model.trim="form.noEmpleado"
                class="input-campo"
                :class="{ 'borde-error': errors.noEmpleado, 'borde-valido': campoValido('noEmpleado') }"
                placeholder="Ej. EMP-001"
                @input="validarCampo('noEmpleado')"
                @blur="validarCampo('noEmpleado')"
              >
              <transition name="error-fade">
                <small v-if="errors.noEmpleado" class="mensaje-error">{{ errors.noEmpleado }}</small>
              </transition>
            </div>

            <!-- Selector de puesto -->
            <div class="campo" :class="{ 'campo-error': errors.puesto, 'campo-valido': campoValido('puesto') }">
              <label class="etiqueta">Puesto <span class="obligatorio">*</span></label>
              <select
                v-model="form.id_puesto"
                class="input-campo"
                :class="{ 'borde-error': errors.puesto, 'borde-valido': campoValido('puesto') }"
                @change="validarCampo('puesto')"
              >
                <option value="">Seleccionar puesto</option>
                <option v-for="p in puestos" :key="p.id_puesto" :value="p.id_puesto">{{ p.nombre_puesto }}</option>
              </select>
              <transition name="error-fade">
                <small v-if="errors.puesto" class="mensaje-error">{{ errors.puesto }}</small>
              </transition>
            </div>

          </div>

          <div class="fila-campos fila-parcial">

            <!-- Fecha de contratación — mismo estilo que FormularioAlumnoView -->
            <div class="campo" :class="{ 'campo-error': errors.fechaContratacion, 'campo-valido': campoValido('fechaContratacion') }">
              <label class="etiqueta">Fecha de Contratación <span class="obligatorio">*</span></label>
              <input
                type="date"
                v-model="form.fechaContratacion"
                class="input-campo"
                :class="{ 'borde-error': errors.fechaContratacion, 'borde-valido': campoValido('fechaContratacion') }"
                :max="hoyISO"
                @change="validarCampo('fechaContratacion')"
              >
              <transition name="error-fade">
                <small v-if="errors.fechaContratacion" class="mensaje-error">{{ errors.fechaContratacion }}</small>
              </transition>
            </div>

            <!-- Toggle de estatus — mismo estilo que FormularioAlumnoView -->
            <div class="campo">
              <label class="etiqueta">Estatus <span class="obligatorio">*</span></label>
              <select v-model="form.estatus" class="input-campo">
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
              <div class="indicador-estatus" :class="form.estatus.toLowerCase()">
                {{ form.estatus }}
              </div>
            </div>

          </div>
        </section>

        <!-- ══ SECCIÓN 3: Registro como Docente ══ -->
        <section class="seccion seccion-sin-borde">
          <h2 class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" class="seccion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
            </svg>
            Registro como Docente
          </h2>

          <!-- Toggle de docente -->
          <div class="toggle-docente-wrap">
            <label class="toggle-label">
              <div class="toggle-switch" :class="{ activo: form.esDocente }" @click="form.esDocente = !form.esDocente">
                <div class="toggle-thumb"></div>
              </div>
              <span class="toggle-texto">Este empleado también es docente</span>
            </label>
          </div>

          <!-- Campos condicionales con animación suave -->
          <transition name="docente-fade">
            <div v-if="form.esDocente" class="campos-docente">
              <div class="fila-campos fila-parcial">

                <!-- Grado académico -->
                <div class="campo" :class="{ 'campo-error': errors.gradoAcademico, 'campo-valido': campoValido('gradoAcademico') }">
                  <label class="etiqueta">Grado Académico <span class="obligatorio">*</span></label>
                  <select
                    v-model="form.gradoAcademico"
                    class="input-campo"
                    :class="{ 'borde-error': errors.gradoAcademico, 'borde-valido': campoValido('gradoAcademico') }"
                    @change="validarCampo('gradoAcademico')"
                  >
                    <option value="">Seleccionar grado</option>
                    <option value="Licenciatura">Licenciatura</option>
                    <option value="Maestría">Maestría</option>
                    <option value="Doctorado">Doctorado</option>
                  </select>
                  <transition name="error-fade">
                    <small v-if="errors.gradoAcademico" class="mensaje-error">{{ errors.gradoAcademico }}</small>
                  </transition>
                </div>

                <!-- Especialidad -->
                <div class="campo" :class="{ 'campo-error': errors.especialidad, 'campo-valido': campoValido('especialidad') }">
                  <label class="etiqueta">Especialidad <span class="obligatorio">*</span></label>
                  <input
                    type="text"
                    v-model.trim="form.especialidad"
                    class="input-campo"
                    :class="{ 'borde-error': errors.especialidad, 'borde-valido': campoValido('especialidad') }"
                    placeholder="Ej. Ingeniería de Software"
                    @input="validarCampo('especialidad')"
                    @blur="validarCampo('especialidad')"
                  >
                  <transition name="error-fade">
                    <small v-if="errors.especialidad" class="mensaje-error">{{ errors.especialidad }}</small>
                  </transition>
                </div>

              </div>
            </div>
          </transition>
        </section>

        <!-- Acciones -->
        <div class="form-acciones">
          <button class="btn-cancelar" @click="cancelar" :disabled="isLoading">Cancelar</button>
          <button class="btn-guardar" @click="guardarEmpleado" :disabled="isLoading">
            <span v-if="isLoading" class="spinner"></span>
            {{ isLoading ? 'Guardando...' : 'Guardar registro' }}
          </button>
        </div>

      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const router = useRouter()
const route  = useRoute()

const modoEdicion = computed(() => !!route.params.id)
const hoyISO      = new Date().toISOString().split('T')[0]

// ── Estado ──
const isLoading  = ref(false)
const puestos    = ref([])
const notification = reactive({ message: '', type: '' })
const errors       = reactive({})
const tocados      = reactive({})

// ── Formulario ──
const form = reactive({
  noEmpleado:       '',
  id_puesto:        '',
  fechaContratacion: '',
  estatus:          'Activo',
  esDocente:        false,
  gradoAcademico:   '',
  especialidad:     '',
})

// ── Persona ──
const busquedaPersona   = ref('')
const resultadosPersona = ref([])
const buscandoPersona   = ref(false)
const personaSeleccionada = ref(null)
let debouncePersona = null

const buscarPersonas = () => {
  clearTimeout(debouncePersona)
  if (busquedaPersona.value.trim().length < 3) {
    resultadosPersona.value = []
    return
  }
  buscandoPersona.value = true
  debouncePersona = setTimeout(async () => {
    try {
      const params = new URLSearchParams({ q: busquedaPersona.value.trim() })
      const res = await fetch(`${API}/personas?${params}`)
      if (!res.ok) throw new Error()
      const data = await res.json()
      resultadosPersona.value = data.map(p => ({
        id_persona:     p.id_persona || p.id,
        nombre:         p.nombre_completo || p.nombre || '',
        identificacion: p.curp || p.rfc || p.identificacion || '',
      }))
      console.log('✅ Personas encontradas:', resultadosPersona.value.length)
    } catch {
      resultadosPersona.value = []
    } finally {
      buscandoPersona.value = false
    }
  }, 350)
}

const elegirPersona = (persona) => {
  personaSeleccionada.value = persona
  resultadosPersona.value   = []
  busquedaPersona.value     = ''
  delete errors.persona
}

const cambiarPersona = () => {
  personaSeleccionada.value = null
  busquedaPersona.value     = ''
  resultadosPersona.value   = []
}

// ── Cargar puestos y datos en edición ──
const cargarPuestos = async () => {
  try {
    const res = await fetch(`${API}/puestos`)
    if (!res.ok) throw new Error()
    const json = await res.json()
    puestos.value = json.data || json
    console.log('✅ Puestos cargados:', puestos.value.length)
  } catch {
    console.error('❌ Error cargando puestos')
  }
}

const cargarEmpleado = async (id) => {
  try {
    const res = await fetch(`${API}/empleados/${id}`)
    if (!res.ok) throw new Error()
    const data = await res.json()
    form.noEmpleado        = data.numero_empleado || ''
    form.id_puesto         = data.id_puesto || ''
    form.fechaContratacion = data.fecha_contratacion?.split('T')[0] || ''
    form.estatus           = data.estatus || 'Activo'
    form.esDocente         = data.es_docente ?? false
    form.gradoAcademico    = data.grado_academico || ''
    form.especialidad      = data.especialidad || ''
    if (data.persona) {
      personaSeleccionada.value = {
        id_persona:     data.persona.id_persona || data.id_persona,
        nombre:         data.persona.nombre_completo || data.persona.nombre || '',
        identificacion: data.persona.curp || data.persona.rfc || '',
      }
    }
    console.log('✅ Empleado cargado para edición')
  } catch {
    console.error('❌ Error cargando empleado')
    showNotification('No se pudo cargar el empleado. Verifica la conexión.', 'error')
  }
}

onMounted(async () => {
  await cargarPuestos()
  if (modoEdicion.value) await cargarEmpleado(route.params.id)
})

// ── Validaciones ──
const validarCampo = (campo) => {
  tocados[campo] = true
  switch (campo) {
    case 'noEmpleado':
      if (!form.noEmpleado.trim()) errors.noEmpleado = 'El número de empleado es obligatorio'
      else if (form.noEmpleado.trim().length < 2) errors.noEmpleado = 'Debe tener al menos 2 caracteres'
      else delete errors.noEmpleado
      break
    case 'puesto':
      if (!form.id_puesto) errors.puesto = 'Seleccione un puesto'
      else delete errors.puesto
      break
    case 'fechaContratacion':
      if (!form.fechaContratacion) errors.fechaContratacion = 'La fecha de contratación es obligatoria'
      else if (form.fechaContratacion > hoyISO) errors.fechaContratacion = 'La fecha no puede ser futura'
      else delete errors.fechaContratacion
      break
    case 'gradoAcademico':
      if (form.esDocente && !form.gradoAcademico) errors.gradoAcademico = 'Seleccione el grado académico'
      else delete errors.gradoAcademico
      break
    case 'especialidad':
      if (form.esDocente && !form.especialidad.trim()) errors.especialidad = 'La especialidad es obligatoria'
      else delete errors.especialidad
      break
  }
}

const campoValido = (campo) => tocados[campo] && !errors[campo]

const validarFormulario = () => {
  if (!personaSeleccionada.value) errors.persona = 'Seleccione una persona para asociar al empleado'
  else delete errors.persona
  const campos = ['noEmpleado', 'puesto', 'fechaContratacion']
  if (form.esDocente) campos.push('gradoAcademico', 'especialidad')
  campos.forEach(c => validarCampo(c))
  return Object.keys(errors).length === 0
}

// ── Guardar ──
const guardarEmpleado = async () => {
  if (!validarFormulario()) {
    showNotification('Corrige los errores marcados antes de continuar.', 'error')
    return
  }

  isLoading.value = true

  const payload = {
    id_persona:        personaSeleccionada.value.id_persona,
    numero_empleado:   form.noEmpleado,
    id_puesto:         form.id_puesto,
    fecha_contratacion: form.fechaContratacion,
    estatus:           form.estatus,
    es_docente:        form.esDocente,
    grado_academico:   form.esDocente ? form.gradoAcademico : null,
    especialidad:      form.esDocente ? form.especialidad : null,
  }

  try {
    const url    = modoEdicion.value
      ? `${API}/empleados/${route.params.id}`
      : `${API}/empleados`
    const method = modoEdicion.value ? 'PUT' : 'POST'

    console.log(`🔵 ${method} payload:`, payload)

    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    })
    const data = await res.json()
    console.log('🟢 Respuesta backend:', data)

    if (res.ok) {
      showNotification(
        modoEdicion.value ? 'Empleado actualizado correctamente.' : 'Empleado registrado correctamente.',
        'success'
      )
      setTimeout(() => router.push('/recursos-humanos/empleados'), 1500)
    } else {
      throw new Error(JSON.stringify(data))
    }
  } catch (error) {
    console.error('❌ ERROR:', error)
    showNotification('Ocurrió un error al guardar el registro. Verifica la conexión con el servidor.', 'error')
  } finally {
    isLoading.value = false
  }
}

const cancelar = () => router.push('/recursos-humanos/empleados')

const showNotification = (message, type) => {
  notification.message = message
  notification.type    = type
  setTimeout(() => { notification.message = '' }, 4000)
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.formulario-page {
  --azul:       #1B396A;
  --azul-hover: #1D4ED8;
  --borde:      #E5E7EB;
  --fondo:      #F5F5F5;
  --texto:      #1A1A1A;
  --gris:       #6B7280;
  --verde:      #16A34A;
  --rojo:       #DC2626;

  width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

/* ── Breadcrumb ── */
.breadcrumb { display: flex; align-items: center; gap: 6px; color: var(--gris); font-size: 0.88rem; margin-bottom: 1rem; }
.breadcrumb-link { color: var(--azul); font-weight: 500; text-decoration: none; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul-hover); text-decoration: underline; }
.breadcrumb-sep { color: #9CA3AF; }
.breadcrumb-actual { color: var(--gris); font-weight: 600; }

/* ── Encabezado ── */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.4rem; }
.page-title { color: var(--texto); font-size: 1.75rem; font-weight: 700; margin: 0 0 0.25rem; letter-spacing: -0.02em; }
.subtitle { color: var(--gris); font-size: 0.93rem; margin: 0; }
.obligatorio { color: var(--rojo); }

/* ── Toast ── */
.toast {
  position: fixed; bottom: 2rem; right: 2rem;
  display: flex; align-items: center; gap: 10px;
  padding: 0.9rem 1.4rem; border-radius: 10px; color: white;
  font-weight: 700; font-size: 0.9rem;
  box-shadow: 0 8px 24px rgba(0,0,0,0.15); z-index: 9999;
  font-family: 'Montserrat', sans-serif; max-width: 400px;
}
.toast.success { background: var(--azul); }
.toast.error   { background: var(--rojo); }
.toast-icono { width: 20px; height: 20px; flex-shrink: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.35s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(100%); }

/* ── Card ── */
.form-card {
  background: #FFFFFF; border-radius: 14px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  padding: 2rem 2.2rem; width: 100%; max-width: 1000px;
  margin: 0 auto; border: 1px solid var(--borde);
}

/* ── Secciones ── */
.seccion { margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--borde); }
.seccion-sin-borde { border-bottom: none; margin-bottom: 1rem; padding-bottom: 0; }

.seccion-titulo {
  display: flex; align-items: center; gap: 8px;
  color: var(--azul); font-size: 1.1rem; font-weight: 700;
  margin: 0 0 1.2rem; padding-bottom: 0.6rem; border-bottom: 2px solid var(--borde);
}
.seccion-icono { width: 20px; height: 20px; stroke: var(--azul); flex-shrink: 0; }

/* ── Layout campos ── */
.fila-campos { display: flex; gap: 1.2rem; flex-wrap: wrap; }
.fila-parcial .campo { flex: 0 0 calc(50% - 0.6rem); min-width: 200px; }
.campo { flex: 1; min-width: 180px; position: relative; }

/* ── Etiquetas e inputs — exactamente igual a FormularioAlumnoView ── */
.etiqueta {
  display: flex; align-items: center; gap: 6px;
  margin-bottom: 6px; font-weight: 600; font-size: 0.9rem;
  color: var(--texto); font-family: 'Montserrat', sans-serif;
}
.input-campo {
  width: 100%; padding: 10px 14px; font-size: 0.95rem;
  border: 1.5px solid var(--borde); border-radius: 8px;
  background: #FFFFFF; color: var(--texto);
  font-family: 'Montserrat', sans-serif; outline: none;
  transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box;
}
.input-campo:focus { border-color: var(--azul); box-shadow: 0 0 0 3px #DBEAFE; }
.input-campo::placeholder { color: #9CA3AF; }
.borde-error { border-color: var(--rojo) !important; }
.borde-error:focus { box-shadow: 0 0 0 3px #FEE2E2 !important; }
.borde-valido { border-color: #16A34A !important; }
.borde-valido:focus { box-shadow: 0 0 0 3px #DCFCE7 !important; }

.mensaje-error {
  display: flex; align-items: center; gap: 4px;
  color: var(--rojo); font-size: 0.82rem; margin-top: 5px;
  font-family: 'Montserrat', sans-serif;
}
.mensaje-error::before { content: '⚠'; font-size: 0.78rem; }
.error-fade-enter-active, .error-fade-leave-active { transition: all 0.25s ease; }
.error-fade-enter-from, .error-fade-leave-to { opacity: 0; transform: translateY(-4px); }

/* ── Buscador de persona — igual a search-group de AlumnosView ── */
.search-group {
  position: relative; width: 100%;
}
.search-input {
  width: 100%; padding: 10px 14px 10px 42px;
  border: 1.5px solid var(--borde); border-radius: 8px;
  font-size: 0.95rem; background: #FFFFFF; color: var(--texto);
  font-family: 'Montserrat', sans-serif; outline: none;
  transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box;
}
.search-input:focus { border-color: var(--azul); box-shadow: 0 0 0 3px #DBEAFE; }
.search-input::placeholder { color: #9CA3AF; }
.search-icon-svg {
  position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
  width: 18px; height: 18px; stroke: var(--gris); pointer-events: none;
}
.spinner-busqueda {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  width: 16px; height: 16px; border: 2px solid #E5E7EB;
  border-top-color: var(--azul); border-radius: 50%;
  animation: girar 0.7s linear infinite;
}

/* Resultados de búsqueda de personas */
.resultados-persona {
  margin-top: 8px; border: 1px solid var(--borde); border-radius: 10px;
  overflow: hidden; background: #FFFFFF;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.resultado-persona-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 14px; cursor: pointer; transition: background 0.15s;
  border-bottom: 1px solid var(--borde);
}
.resultado-persona-item:last-child { border-bottom: none; }
.resultado-persona-item:hover { background: #F8FAFF; }
.resultado-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  background: var(--azul); color: white;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 1rem; flex-shrink: 0;
}
.resultado-info { flex: 1; display: flex; flex-direction: column; }
.resultado-info strong { font-size: 0.93rem; color: var(--texto); }
.resultado-info span { font-size: 0.82rem; color: var(--gris); margin-top: 1px; }
.btn-seleccionar-persona {
  background: #EFF6FF; color: var(--azul); border: 1px solid #BFDBFE;
  padding: 6px 14px; border-radius: 6px; font-weight: 600;
  font-size: 0.85rem; cursor: pointer; white-space: nowrap;
  font-family: 'Montserrat', sans-serif;
}
.sin-resultados-persona {
  margin-top: 8px; padding: 12px 14px; border-radius: 8px;
  background: #F5F5F5; color: var(--gris); font-size: 0.88rem;
  border: 1px solid var(--borde);
}

/* Persona seleccionada — exactamente igual a alumno-seleccionado de InscripcionView */
.persona-seleccionada {
  display: flex; align-items: center; gap: 16px;
  background: #F0FDF4; border: 1.5px solid #86EFAC;
  border-radius: 12px; padding: 16px 20px; margin-top: 8px;
}
.persona-avatar {
  width: 46px; height: 46px; border-radius: 50%;
  background: #16A34A; color: white; display: flex;
  align-items: center; justify-content: center;
  font-weight: 700; font-size: 1.2rem; flex-shrink: 0;
}
.persona-datos { flex: 1; }
.persona-datos strong { display: block; color: var(--texto); font-size: 1rem; }
.persona-datos span { color: var(--gris); font-size: 0.85rem; }
.btn-cambiar-persona {
  background: white; color: var(--gris); border: 1px solid var(--borde);
  padding: 9px 18px; border-radius: 8px; font-weight: 600;
  cursor: pointer; font-size: 0.9rem; font-family: 'Montserrat', sans-serif;
  transition: background 0.15s;
}
.btn-cambiar-persona:hover:not(:disabled) { background: var(--fondo); }
.btn-cambiar-persona:disabled { opacity: 0.5; cursor: not-allowed; }

/* Indicador de estatus — igual a FormularioAlumnoView */
.indicador-estatus {
  display: inline-flex; align-items: center; margin-top: 7px;
  padding: 4px 12px; border-radius: 20px;
  font-size: 0.82rem; font-weight: 600; font-family: 'Montserrat', sans-serif;
}
.indicador-estatus.activo   { background: #DCFCE7; color: #16A34A; }
.indicador-estatus.inactivo { background: #FEF2F2; color: #DC2626; }

/* ── Toggle de docente ── */
.toggle-docente-wrap { margin-bottom: 1.2rem; }
.toggle-label { display: flex; align-items: center; gap: 12px; cursor: pointer; user-select: none; }
.toggle-switch {
  width: 44px; height: 24px; border-radius: 12px;
  background: #E5E7EB; position: relative;
  transition: background 0.25s; flex-shrink: 0; cursor: pointer;
}
.toggle-switch.activo { background: #16A34A; }
.toggle-thumb {
  position: absolute; top: 2px; left: 2px;
  width: 20px; height: 20px; border-radius: 50%;
  background: white; transition: transform 0.25s;
  box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
.toggle-switch.activo .toggle-thumb { transform: translateX(20px); }
.toggle-texto { font-size: 0.95rem; font-weight: 600; color: var(--texto); }

/* Campos de docente con animación suave */
.campos-docente { margin-top: 1.2rem; }
.docente-fade-enter-active { transition: all 0.3s ease; }
.docente-fade-leave-active { transition: all 0.2s ease; }
.docente-fade-enter-from, .docente-fade-leave-to { opacity: 0; transform: translateY(-8px); }

/* ── Acciones ── */
.form-acciones {
  display: flex; justify-content: flex-end; gap: 1rem;
  margin-top: 1.6rem; padding-top: 1.4rem; border-top: 1px solid var(--borde);
}
.btn-cancelar {
  padding: 11px 26px; background: #FFFFFF; color: var(--texto);
  border: 1px solid var(--borde); border-radius: 8px;
  font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif;
  font-size: 0.95rem; transition: background 0.15s;
}
.btn-cancelar:hover:not(:disabled) { background: var(--fondo); }
.btn-cancelar:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-guardar {
  display: flex; align-items: center; gap: 8px;
  padding: 11px 28px; background: var(--azul); color: white;
  border: none; border-radius: 8px; font-weight: 700; cursor: pointer;
  font-family: 'Montserrat', sans-serif; font-size: 0.95rem; transition: background 0.2s;
}
.btn-guardar:hover:not(:disabled) { background: var(--azul-hover); }
.btn-guardar:disabled { opacity: 0.7; cursor: not-allowed; }

.spinner {
  display: inline-block; width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3); border-top-color: white;
  border-radius: 50%; animation: girar 0.8s linear infinite; flex-shrink: 0;
}

@keyframes girar { to { transform: rotate(360deg); } }
</style>