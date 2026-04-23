<template>
  <MainLayout>
    <div class="formulario-page">

      <!-- ══ Breadcrumb navegable ══ -->
      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="cancelar">Seguridad y Usuarios</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-link" @click="cancelar">Usuarios</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">Nuevo Usuario</span>
      </nav>

      <!-- ══ Encabezado ══ -->
      <div class="page-header">
        <div>
          <h1 class="page-title">Nuevo Usuario</h1>
          <p class="subtitle">
            Complete todos los campos marcados con <span class="obligatorio">*</span>
            para crear una cuenta de acceso al sistema.
          </p>
        </div>
      </div>

      <!-- ══ Toast de notificación ══ -->
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

      <!-- ══ Tarjeta del formulario ══ -->
      <div class="form-card">

        <!-- ────────────────────────────────────────────
             SECCIÓN 1: Persona Asociada
        ──────────────────────────────────────────── -->
        <section class="seccion">
          <h2 class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" class="seccion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Persona Asociada
          </h2>

          <!-- Buscador de persona -->
          <div class="fila-campos">
            <div class="campo campo-expandido"
                 :class="{ 'campo-error': errors.persona, 'campo-valido': campoValido('persona') }">
              <label class="etiqueta">
                Buscar persona <span class="obligatorio">*</span>
                <span class="etiqueta-hint">Búsqueda por nombre completo</span>
              </label>
              <div class="input-busqueda-persona">
                <svg xmlns="http://www.w3.org/2000/svg" class="icono-busqueda-persona" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input
                  type="text"
                  v-model="busquedaPersona"
                  class="input-campo input-con-boton"
                  :class="{ 'borde-error': errors.persona, 'borde-valido': campoValido('persona') }"
                  placeholder="Escribe el nombre completo de la persona..."
                  @input="onBusquedaPersona"
                  @keydown.escape="cerrarSugerencias"
                >
                <button
                  type="button"
                  class="btn-buscar-persona"
                  @click="buscarPersona"
                  :disabled="buscandoPersona || !busquedaPersona.trim()"
                >
                  <span v-if="buscandoPersona" class="spinner-mini"></span>
                  <span v-else>Buscar</span>
                </button>
              </div>

              <!-- Listado de sugerencias de personas ══ -->
              <!-- Cuando el backend conecte, poblar con resultados de GET /api/personas?q=... -->
              <div v-if="sugerenciasPersona.length > 0" class="sugerencias-lista">
                <div
                  v-for="(persona, i) in sugerenciasPersona"
                  :key="i"
                  class="sugerencia-item"
                  @click="seleccionarPersona(persona)"
                >
                  <div class="sugerencia-avatar">{{ persona.nombre_completo.charAt(0) }}</div>
                  <div class="sugerencia-info">
                    <span class="sugerencia-nombre">{{ persona.nombre_completo }}</span>
                    <span class="sugerencia-detalle">{{ persona.curp || persona.email || '' }}</span>
                  </div>
                  <svg xmlns="http://www.w3.org/2000/svg" class="sugerencia-check" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </div>
              </div>

              <transition name="error-fade">
                <small v-if="errors.persona" class="mensaje-error">{{ errors.persona }}</small>
              </transition>
            </div>
          </div>

          <!-- Tarjeta de persona seleccionada ══ -->
          <div v-if="personaSeleccionada" class="persona-seleccionada">
            <div class="persona-avatar">{{ personaSeleccionada.nombre_completo.charAt(0) }}</div>
            <div class="persona-datos">
              <p class="persona-nombre">{{ personaSeleccionada.nombre_completo }}</p>
              <p class="persona-extra">{{ personaSeleccionada.curp || personaSeleccionada.email || 'Persona registrada en el sistema' }}</p>
            </div>
            <button type="button" class="btn-quitar-persona" @click="quitarPersona" title="Cambiar persona">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </section>

        <!-- ────────────────────────────────────────────
             SECCIÓN 2: Datos de Acceso
        ──────────────────────────────────────────── -->
        <section class="seccion">
          <h2 class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" class="seccion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
            </svg>
            Datos de Acceso
          </h2>

          <div class="fila-campos">

            <!-- Nombre de usuario -->
            <div class="campo"
                 :class="{ 'campo-error': errors.nombre_usuario, 'campo-valido': campoValido('nombre_usuario') }">
              <label class="etiqueta">
                Nombre de Usuario <span class="obligatorio">*</span>
                <span class="etiqueta-hint">Sin espacios ni caracteres especiales</span>
              </label>
              <input
                type="text"
                v-model.trim="form.nombre_usuario"
                class="input-campo"
                :class="{ 'borde-error': errors.nombre_usuario, 'borde-valido': campoValido('nombre_usuario') }"
                placeholder="Ej. jgarcia26"
                @input="validarCampo('nombre_usuario')"
                @blur="validarCampo('nombre_usuario')"
              >
              <!-- Sugerencia automática de nombre de usuario ══ -->
              <small v-if="sugerenciaNombreUsuario && !form.nombre_usuario" class="vista-previa-control">
                Sugerencia: <strong>{{ sugerenciaNombreUsuario }}</strong>
                <button type="button" class="btn-usar-sugerencia" @click="usarSugerencia">Usar</button>
              </small>
              <transition name="error-fade">
                <small v-if="errors.nombre_usuario" class="mensaje-error">{{ errors.nombre_usuario }}</small>
              </transition>
            </div>

            <!-- Estatus -->
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

          <div class="fila-campos">

            <!-- Contraseña -->
            <div class="campo"
                 :class="{ 'campo-error': errors.contrasena, 'campo-valido': campoValido('contrasena') }">
              <label class="etiqueta">Contraseña <span class="obligatorio">*</span></label>
              <div class="input-password-wrap">
                <input
                  :type="verContrasena ? 'text' : 'password'"
                  v-model="form.contrasena"
                  class="input-campo input-pass"
                  :class="{ 'borde-error': errors.contrasena, 'borde-valido': campoValido('contrasena') }"
                  placeholder="Mínimo 8 caracteres"
                  @input="validarCampo('contrasena')"
                  @blur="validarCampo('contrasena')"
                >
                <button type="button" class="btn-ver-pass" @click="verContrasena = !verContrasena">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="!verContrasena" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
              <!-- Indicador de fuerza de contraseña ══ -->
              <div v-if="form.contrasena" class="fuerza-contrasena">
                <div class="fuerza-barra">
                  <div class="fuerza-relleno" :class="clasesFuerzaContrasena" :style="{ width: anchoFuerza + '%' }"></div>
                </div>
                <span class="fuerza-texto" :class="clasesFuerzaContrasena">{{ textoFuerza }}</span>
              </div>
              <transition name="error-fade">
                <small v-if="errors.contrasena" class="mensaje-error">{{ errors.contrasena }}</small>
              </transition>
            </div>

            <!-- Confirmar contraseña -->
            <div class="campo"
                 :class="{ 'campo-error': errors.confirmar, 'campo-valido': campoValido('confirmar') }">
              <label class="etiqueta">Confirmar Contraseña <span class="obligatorio">*</span></label>
              <div class="input-password-wrap">
                <input
                  :type="verConfirmar ? 'text' : 'password'"
                  v-model="form.confirmar"
                  class="input-campo input-pass"
                  :class="{ 'borde-error': errors.confirmar, 'borde-valido': campoValido('confirmar') }"
                  placeholder="Repite la contraseña"
                  @input="validarCampo('confirmar')"
                  @blur="validarCampo('confirmar')"
                >
                <button type="button" class="btn-ver-pass" @click="verConfirmar = !verConfirmar">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              <transition name="error-fade">
                <small v-if="errors.confirmar" class="mensaje-error">{{ errors.confirmar }}</small>
              </transition>
            </div>
          </div>
        </section>

        <!-- ────────────────────────────────────────────
             SECCIÓN 3: Roles Asignados
        ──────────────────────────────────────────── -->
        <section class="seccion seccion-sin-borde">
          <h2 class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" class="seccion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Roles Asignados
            <span v-if="totalRolesSeleccionados > 0" class="roles-contador">
              {{ totalRolesSeleccionados }} seleccionado(s)
            </span>
          </h2>

          <!-- Aviso si no hay roles seleccionados ══ -->
          <div v-if="totalRolesSeleccionados === 0" class="aviso-roles">
            <svg xmlns="http://www.w3.org/2000/svg" class="aviso-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Selecciona al menos un rol para el usuario. Puedes asignar varios.
          </div>

          <!-- Grilla de roles con checkboxes
               Cuando el backend conecte: poblar rolesDisponibles desde GET /api/roles ══ -->
          <div class="grilla-roles">
            <label
              v-for="(rol, i) in rolesDisponibles"
              :key="i"
              class="rol-item"
              :class="{ 'rol-activo': rolesSeleccionados[rol.nombre] }"
            >
              <div class="rol-checkbox-wrap">
                <input
                  type="checkbox"
                  class="rol-checkbox"
                  v-model="rolesSeleccionados[rol.nombre]"
                >
              </div>
              <div class="rol-contenido">
                <div class="rol-encabezado">
                  <span class="rol-nombre">{{ rol.nombre }}</span>
                  <span v-if="rolesSeleccionados[rol.nombre]" class="rol-activo-badge">Asignado</span>
                </div>
                <span class="rol-descripcion">{{ rol.descripcion }}</span>
                <div class="rol-permisos-preview" v-if="rol.permisos">
                  <span v-for="(p, j) in rol.permisos.slice(0, 3)" :key="j" class="permiso-chip">{{ p }}</span>
                  <span v-if="rol.permisos.length > 3" class="permiso-chip permiso-chip-extra">
                    +{{ rol.permisos.length - 3 }} más
                  </span>
                </div>
              </div>
            </label>
          </div>
        </section>

        <!-- ══ Acciones ══ -->
        <div class="form-acciones">
          <button class="btn-cancelar" @click="cancelar" :disabled="isLoading">Cancelar</button>
          <button class="btn-guardar" @click="guardarUsuario" :disabled="isLoading">
            <span v-if="isLoading" class="spinner"></span>
            {{ isLoading ? 'Guardando...' : 'Guardar Usuario' }}
          </button>
        </div>

      </div>
    </div>
  </MainLayout>
</template>



<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()

// ── URL base del backend (variable de entorno) ──────────────────────
const API_URL = import.meta.env.VITE_API_URL

// ==================== FORMULARIO ====================
const form = reactive({
  nombre_usuario: '',
  contrasena: '',
  confirmar: '',
  estatus: 'Activo'
})

const errors = reactive({})
const tocados = reactive({})
const notification = reactive({ message: '', type: '' })
const isLoading = ref(false)

// Visibilidad contraseñas
const verContrasena = ref(false)
const verConfirmar = ref(false)

// Sugerencia nombre de usuario
const sugerenciaNombreUsuario = ref('')

// ==================== PERSONA ====================
const busquedaPersona = ref('')
const buscandoPersona = ref(false)
const personaSeleccionada = ref(null)
const sugerenciasPersona = ref([])

// ==================== ROLES ====================
const rolesDisponibles = ref([])
const rolesSeleccionados = ref({})

// ==================== CARGAR ROLES ====================
// Endpoint: GET /api/roles-simple
const cargarRoles = async () => {
  try {
    const response = await fetch(`${API_URL}/api/roles-simple`)
    if (!response.ok) throw new Error('Error al cargar roles')

    const data = await response.json()
    rolesDisponibles.value = data

    // Inicializar checkboxes
    const obj = {}
    data.forEach(rol => { obj[rol.nombre] = false })
    rolesSeleccionados.value = obj

  } catch (error) {
    console.error('Error cargando roles:', error)
  }
}

// ==================== BÚSQUEDA DE PERSONA ====================
// Endpoint: GET /api/personas/buscar?q=...
const buscarPersona = async () => {
  const q = busquedaPersona.value.trim()
  if (q.length < 2) {
    sugerenciasPersona.value = []
    return
  }

  buscandoPersona.value = true
  try {
    const response = await fetch(`${API_URL}/api/personas/buscar?q=${encodeURIComponent(q)}`)
    if (response.ok) {
      sugerenciasPersona.value = await response.json()
    } else {
      sugerenciasPersona.value = []
    }
  } catch (error) {
    console.error('Error buscando persona:', error)
    sugerenciasPersona.value = []
  } finally {
    buscandoPersona.value = false
  }
}

const seleccionarPersona = (persona) => {
  personaSeleccionada.value = persona
  sugerenciasPersona.value = []
  busquedaPersona.value = ''
}

const quitarPersona = () => {
  personaSeleccionada.value = null
  sugerenciasPersona.value = []
}

// ==================== VALIDACIONES ====================
const validarCampo = (campo) => {
  tocados[campo] = true

  if (campo === 'persona') {
    if (!personaSeleccionada.value) {
      errors.persona = 'Debes seleccionar una persona'
    } else {
      delete errors.persona
    }
  }

  if (campo === 'nombre_usuario') {
    if (!form.nombre_usuario.trim()) {
      errors.nombre_usuario = 'Obligatorio'
    } else if (form.nombre_usuario.length < 4) {
      errors.nombre_usuario = 'Mínimo 4 caracteres'
    } else {
      delete errors.nombre_usuario
    }
  }

  if (campo === 'contrasena') {
    if (!form.contrasena) {
      errors.contrasena = 'Obligatoria'
    } else if (form.contrasena.length < 8) {
      errors.contrasena = 'Mínimo 8 caracteres'
    } else {
      delete errors.contrasena
    }
  }

  if (campo === 'confirmar') {
    if (form.confirmar !== form.contrasena) {
      errors.confirmar = 'No coinciden'
    } else {
      delete errors.confirmar
    }
  }
}

const campoValido = (campo) => tocados[campo] && !errors[campo]

const validarFormulario = () => {
  validarCampo('persona')
  validarCampo('nombre_usuario')
  validarCampo('contrasena')
  validarCampo('confirmar')
  return Object.keys(errors).length === 0 && totalRolesSeleccionados.value > 0
}

// ==================== GUARDAR ====================
// Endpoint: POST /api/usuarios
const guardarUsuario = async () => {
  if (!validarFormulario()) {
    showNotification('Corrige los errores antes de guardar', 'error')
    return
  }

  isLoading.value = true

  const rolesActivos = Object.keys(rolesSeleccionados.value)
    .filter(key => rolesSeleccionados.value[key])

  const payload = {
    id_persona:     personaSeleccionada.value?.id_persona,
    nombre_usuario: form.nombre_usuario.trim(),
    contrasena:     form.contrasena,
    estatus:        form.estatus,
    roles:          rolesActivos
  }

  try {
    const response = await fetch(`${API_URL}/api/usuarios`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload)
    })

    const data = await response.json()

    if (response.ok) {
      showNotification('Usuario creado correctamente', 'success')
      setTimeout(() => router.push('/usuarios'), 1800)
    } else {
      showNotification(data.error || 'No se pudo crear el usuario', 'error')
    }

  } catch (error) {
    console.error(error)
    showNotification('Error de conexión con el servidor', 'error')
  } finally {
    isLoading.value = false
  }
}

// ==================== HELPERS ====================
const totalRolesSeleccionados = computed(() =>
  Object.values(rolesSeleccionados.value).filter(Boolean).length
)

const cancelar = () => router.push('/usuarios')

const showNotification = (message, type) => {
  notification.message = message
  notification.type = type
  setTimeout(() => { notification.message = '' }, 4500)
}

// Watch para búsqueda en tiempo real de persona
watch(busquedaPersona, () => {
  if (busquedaPersona.value.length >= 3) {
    buscarPersona()
  } else {
    sugerenciasPersona.value = []
  }
})

onMounted(() => {
  cargarRoles()
})
</script>




<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

/* ══ Variables locales ══ */
.formulario-page {
  --azul:          #1B396A;
  --azul-hover:    #1D4ED8;
  --azul-suave:    #DBEAFE;
  --borde:         #E5E7EB;
  --fondo:         #F5F5F5;
  --texto:         #1A1A1A;
  --gris:          #6B7280;
  --verde:         #16A34A;
  --rojo:          #DC2626;

  width: 100%;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

/* ══ Breadcrumb ══ */
.breadcrumb { display: flex; align-items: center; gap: 6px; color: var(--gris); font-size: 0.88rem; margin-bottom: 1rem; }
.breadcrumb-link { cursor: pointer; color: var(--azul); font-weight: 500; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul-hover); text-decoration: underline; }
.breadcrumb-sep { color: #9CA3AF; }
.breadcrumb-actual { color: var(--gris); font-weight: 600; }

/* ══ Encabezado ══ */
.page-header { margin-bottom: 1.4rem; }
.page-title  { color: var(--texto); font-size: 1.75rem; font-weight: 700; margin: 0 0 0.25rem; letter-spacing: -0.02em; }
.subtitle    { color: var(--gris); font-size: 0.93rem; margin: 0; }
.obligatorio { color: var(--rojo); }

/* ══ Toast ══ */
.toast {
  position: fixed; top: 88px; right: 28px;
  display: flex; align-items: center; gap: 10px;
  padding: 13px 20px; border-radius: 10px;
  color: white; font-weight: 500; font-size: 0.93rem;
  box-shadow: 0 6px 20px rgba(0,0,0,0.2); z-index: 9999;
  font-family: 'Montserrat', sans-serif; max-width: 400px;
}
.toast.success { background: #16A34A; }
.toast.error   { background: #DC2626; }
.toast-icono   { width: 20px; height: 20px; flex-shrink: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.35s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(110%); }

/* ══ Tarjeta del formulario ══ */
.form-card {
  background: #FFFFFF;
  border-radius: 14px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  padding: 2rem 2.2rem;
  max-width: 1000px;
  margin: 0 auto;
  border: 1px solid var(--borde);
}

/* ══ Secciones ══ */
.seccion { margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--borde); }
.seccion-sin-borde { border-bottom: none; margin-bottom: 1rem; padding-bottom: 0; }

.seccion-titulo {
  display: flex; align-items: center; gap: 8px;
  color: var(--azul); font-size: 1.05rem; font-weight: 700;
  margin: 0 0 1.2rem; padding-bottom: 0.6rem;
  border-bottom: 2px solid var(--borde);
}
.seccion-icono { width: 20px; height: 20px; stroke: var(--azul); flex-shrink: 0; }

/* ══ Filas de campos ══ */
.fila-campos { display: flex; gap: 1.2rem; flex-wrap: wrap; margin-bottom: 1rem; }
.campo { flex: 1; min-width: 180px; position: relative; }
.campo-expandido { flex: 2; }

/* ══ Etiquetas ══ */
.etiqueta {
  display: flex; align-items: center; gap: 6px;
  margin-bottom: 6px; font-weight: 600;
  font-size: 0.9rem; color: var(--texto);
  font-family: 'Montserrat', sans-serif;
}
.etiqueta-hint {
  font-weight: 400; font-size: 0.78rem;
  color: var(--azul); background: var(--azul-suave);
  padding: 2px 7px; border-radius: 10px;
}
.roles-contador {
  margin-left: auto;
  background: var(--azul);
  color: white;
  font-size: 0.75rem;
  padding: 2px 10px;
  border-radius: 20px;
  font-weight: 600;
}

/* ══ Inputs ══ */
.input-campo {
  width: 100%; padding: 10px 14px;
  font-size: 0.95rem; border: 1.5px solid var(--borde);
  border-radius: 8px; background: #FFFFFF;
  color: var(--texto); font-family: 'Montserrat', sans-serif;
  outline: none; transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}
.input-campo:focus { border-color: var(--azul); box-shadow: 0 0 0 3px var(--azul-suave); }
.input-campo::placeholder { color: #9CA3AF; }
.borde-error { border-color: var(--rojo) !important; }
.borde-error:focus { box-shadow: 0 0 0 3px #FEE2E2 !important; }
.borde-valido { border-color: var(--verde) !important; }
.borde-valido:focus { box-shadow: 0 0 0 3px #DCFCE7 !important; }

/* Mensajes de error ══ */
.mensaje-error {
  display: flex; align-items: center; gap: 4px;
  color: var(--rojo); font-size: 0.82rem;
  margin-top: 5px; font-family: 'Montserrat', sans-serif;
}
.mensaje-error::before { content: '⚠'; font-size: 0.78rem; }
.error-fade-enter-active, .error-fade-leave-active { transition: all 0.25s ease; }
.error-fade-enter-from, .error-fade-leave-to { opacity: 0; transform: translateY(-4px); }

/* Vista previa sugerencia usuario ══ */
.vista-previa-control { display: flex; align-items: center; gap: 8px; margin-top: 5px; font-size: 0.82rem; color: var(--gris); }
.vista-previa-control strong { color: var(--azul); font-weight: 700; }
.btn-usar-sugerencia { background: var(--azul-suave); color: var(--azul); border: none; padding: 2px 10px; border-radius: 6px; font-size: 0.78rem; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; }
.btn-usar-sugerencia:hover { background: #BFDBFE; }

/* ══ Buscador de persona ══ */
.input-busqueda-persona { position: relative; display: flex; align-items: stretch; gap: 0; }
.icono-busqueda-persona { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); width: 17px; height: 17px; stroke: var(--gris); pointer-events: none; z-index: 1; }
.input-con-boton { padding-left: 42px !important; border-radius: 8px 0 0 8px !important; flex: 1; }
.btn-buscar-persona {
  background: var(--azul); color: white; border: none;
  padding: 0 18px; border-radius: 0 8px 8px 0;
  font-weight: 600; font-size: 0.88rem; cursor: pointer;
  font-family: 'Montserrat', sans-serif; white-space: nowrap;
  transition: background 0.15s; flex-shrink: 0;
}
.btn-buscar-persona:hover:not(:disabled) { background: var(--azul-hover); }
.btn-buscar-persona:disabled { opacity: 0.5; cursor: not-allowed; }
.spinner-mini { display: inline-block; width: 14px; height: 14px; border: 2px solid rgba(255,255,255,0.4); border-top-color: white; border-radius: 50%; animation: girar 0.7s linear infinite; }

/* Lista de sugerencias ══ */
.sugerencias-lista {
  position: absolute; top: calc(100% + 4px); left: 0; right: 0;
  background: #FFFFFF; border-radius: 10px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.14);
  border: 1px solid var(--borde); z-index: 100;
  overflow: hidden;
}
.sugerencia-item {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 16px; cursor: pointer;
  transition: background 0.15s;
}
.sugerencia-item:hover { background: #F8FAFC; }
.sugerencia-avatar { width: 34px; height: 34px; border-radius: 50%; background: var(--azul); color: white; font-size: 0.9rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sugerencia-info { flex: 1; }
.sugerencia-nombre { display: block; font-weight: 600; font-size: 0.92rem; color: var(--texto); }
.sugerencia-detalle { display: block; font-size: 0.78rem; color: var(--gris); margin-top: 2px; }
.sugerencia-check { width: 16px; height: 16px; stroke: #9CA3AF; }

/* Tarjeta de persona seleccionada ══ */
.persona-seleccionada {
  display: flex; align-items: center; gap: 14px;
  padding: 12px 16px; margin-top: 10px;
  background: #EFF6FF;
  border: 1.5px solid #BFDBFE;
  border-radius: 10px;
}
.persona-avatar { width: 42px; height: 42px; border-radius: 50%; background: var(--azul); color: white; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.persona-datos { flex: 1; }
.persona-nombre { margin: 0; font-weight: 700; font-size: 0.95rem; color: var(--texto); }
.persona-extra  { margin: 3px 0 0; font-size: 0.82rem; color: var(--gris); }
.btn-quitar-persona { background: none; border: none; cursor: pointer; padding: 4px; border-radius: 6px; transition: background 0.15s; }
.btn-quitar-persona:hover { background: #DBEAFE; }
.btn-quitar-persona svg { width: 18px; height: 18px; stroke: var(--gris); }

/* ══ Contraseña ══ */
.input-password-wrap { position: relative; }
.input-pass { padding-right: 44px !important; }
.btn-ver-pass { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center; }
.btn-ver-pass svg { width: 18px; height: 18px; stroke: var(--gris); }

/* Indicador de fuerza ══ */
.fuerza-contrasena { display: flex; align-items: center; gap: 10px; margin-top: 7px; }
.fuerza-barra { flex: 1; height: 5px; background: var(--borde); border-radius: 9999px; overflow: hidden; }
.fuerza-relleno { height: 100%; border-radius: 9999px; transition: width 0.4s ease, background 0.4s; }
.fuerza-texto { font-size: 0.78rem; font-weight: 600; white-space: nowrap; font-family: 'Montserrat', sans-serif; }
.fuerza-roja    { background: #DC2626; color: #DC2626; }
.fuerza-naranja { background: #F97316; color: #F97316; }
.fuerza-amarilla { background: #F59E0B; color: #F59E0B; }
.fuerza-azul    { background: var(--azul); color: var(--azul); }
.fuerza-verde   { background: var(--verde); color: var(--verde); }

/* ══ Indicador de estatus ══ */
.indicador-estatus { display: inline-flex; align-items: center; margin-top: 7px; padding: 4px 12px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; font-family: 'Montserrat', sans-serif; }
.indicador-estatus.activo   { background: #DCFCE7; color: var(--verde); }
.indicador-estatus.inactivo { background: #FEE2E2; color: var(--rojo); }

/* ══ Aviso de roles ══ */
.aviso-roles {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 14px; margin-bottom: 1rem;
  background: #FFFBEB; border: 1px solid #FDE68A;
  border-radius: 8px; font-size: 0.85rem;
  color: #92400E; font-family: 'Montserrat', sans-serif;
}
.aviso-icono { width: 18px; height: 18px; stroke: #92400E; flex-shrink: 0; }

/* ══ Grilla de roles ══ */
.grilla-roles { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 0.9rem; }

.rol-item {
  display: flex; align-items: flex-start; gap: 12px;
  padding: 14px 16px; border-radius: 10px;
  border: 1.5px solid var(--borde);
  cursor: pointer; transition: all 0.15s;
  background: #FFFFFF;
}
.rol-item:hover { border-color: #BFDBFE; background: #F8FAFC; }
.rol-activo { background: #EFF6FF !important; border-color: #BFDBFE !important; }

.rol-checkbox-wrap { padding-top: 2px; flex-shrink: 0; }
.rol-checkbox { width: 17px; height: 17px; cursor: pointer; accent-color: var(--azul); }

.rol-contenido { flex: 1; }
.rol-encabezado { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.rol-nombre { font-weight: 700; font-size: 0.92rem; color: var(--texto); font-family: 'Montserrat', sans-serif; }
.rol-activo-badge { background: var(--azul); color: white; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
.rol-descripcion { display: block; font-size: 0.82rem; color: var(--gris); line-height: 1.4; font-family: 'Montserrat', sans-serif; margin-bottom: 8px; }

/* Chips de permisos del rol ══ */
.rol-permisos-preview { display: flex; flex-wrap: wrap; gap: 4px; }
.permiso-chip { display: inline-block; background: var(--fondo); border: 1px solid var(--borde); color: var(--gris); font-size: 0.72rem; font-weight: 500; padding: 2px 8px; border-radius: 8px; font-family: 'Montserrat', sans-serif; }
.permiso-chip-extra { color: var(--azul); border-color: #BFDBFE; background: var(--azul-suave); }

/* ══ Acciones ══ */
.form-acciones { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.6rem; padding-top: 1.4rem; border-top: 1px solid var(--borde); }

.btn-cancelar { padding: 11px 26px; background: #FFFFFF; color: var(--texto); border: 1px solid var(--borde); border-radius: 8px; font-weight: 600; cursor: pointer; font-family: 'Montserrat', sans-serif; font-size: 0.95rem; transition: background 0.15s; }
.btn-cancelar:hover:not(:disabled) { background: var(--fondo); }
.btn-cancelar:disabled { opacity: 0.5; cursor: not-allowed; }

.btn-guardar { display: flex; align-items: center; gap: 8px; padding: 11px 28px; background: var(--azul); color: white; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-family: 'Montserrat', sans-serif; font-size: 0.95rem; transition: background 0.2s; }
.btn-guardar:hover:not(:disabled) { background: var(--azul-hover); }
.btn-guardar:disabled { opacity: 0.7; cursor: not-allowed; }

.spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: girar 0.8s linear infinite; flex-shrink: 0; }

@keyframes girar { to { transform: rotate(360deg); } }
</style>
