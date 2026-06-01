<template>
  <MainLayout>
    <div class="formulario-page">

      <nav class="breadcrumb">
        <span class="breadcrumb-link" @click="cancelar">Personas</span>
        <span class="breadcrumb-sep">›</span>
        <span class="breadcrumb-actual">{{ esEdicion ? 'Editar Persona' : 'Nueva Persona' }}</span>
      </nav>

      <div class="page-header">
        <div>
          <h1 class="page-title">{{ esEdicion ? 'Editar Persona' : 'Nueva Persona' }}</h1>
          <p class="subtitle">Complete todos los campos marcados con <span class="obligatorio">*</span> para registrar la persona.</p>
        </div>
      </div>

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

        <!-- Sección 1: Identificación -->
        <section class="seccion">
          <h2 class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" class="seccion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
            </svg>
            Identificación
          </h2>

          <div class="fila-campos curp-row">
            <div class="campo" :class="{ 'campo-error': errors.curp, 'campo-valido': campoValido('curp') }">
              <label class="etiqueta">
                CURP <span class="obligatorio">*</span>
                <span class="etiqueta-hint">18 caracteres alfanuméricos</span>
              </label>
              <div class="input-curp-wrapper" :class="{ 'borde-error': errors.curp, 'borde-valido': campoValido('curp') && !errors.curp }">
                <input
                  type="text"
                  v-model="form.curp"
                  class="input-campo input-curp"
                  placeholder="AAAA######XXXXXXXX##"
                  maxlength="18"
                  @input="onCurpInput"
                  @blur="validarCampo('curp')"
                >
                <span v-if="form.curp.length > 0" class="curp-estado-icono">
                  <svg v-if="campoValido('curp')" xmlns="http://www.w3.org/2000/svg" class="icono-check" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="icono-error-curp" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </span>
              </div>
              <transition name="error-fade">
                <small v-if="errors.curp" class="mensaje-error">{{ errors.curp }}</small>
              </transition>
            </div>
          </div>

          <div class="fila-campos">
            <div class="campo" :class="{ 'campo-error': errors.nombre, 'campo-valido': campoValido('nombre') }">
              <label class="etiqueta">Nombre <span class="obligatorio">*</span></label>
              <input
                type="text"
                v-model.trim="form.nombre"
                class="input-campo"
                :class="{ 'borde-error': errors.nombre, 'borde-valido': campoValido('nombre') }"
                placeholder="Ej. Juan"
                @input="validarCampo('nombre')"
                @blur="validarCampo('nombre')"
              >
              <transition name="error-fade">
                <small v-if="errors.nombre" class="mensaje-error">{{ errors.nombre }}</small>
              </transition>
            </div>

            <div class="campo" :class="{ 'campo-error': errors.apellidoPaterno, 'campo-valido': campoValido('apellidoPaterno') }">
              <label class="etiqueta">Apellido Paterno <span class="obligatorio">*</span></label>
              <input
                type="text"
                v-model.trim="form.apellidoPaterno"
                class="input-campo"
                :class="{ 'borde-error': errors.apellidoPaterno, 'borde-valido': campoValido('apellidoPaterno') }"
                placeholder="Ej. García"
                @input="validarCampo('apellidoPaterno')"
                @blur="validarCampo('apellidoPaterno')"
              >
              <transition name="error-fade">
                <small v-if="errors.apellidoPaterno" class="mensaje-error">{{ errors.apellidoPaterno }}</small>
              </transition>
            </div>

            <div class="campo">
              <label class="etiqueta">Apellido Materno</label>
              <input
                type="text"
                v-model.trim="form.apellidoMaterno"
                class="input-campo"
                placeholder="Ej. López (opcional)"
              >
            </div>
          </div>
        </section>

        <!-- Sección 2: Datos Personales -->
        <section class="seccion">
          <h2 class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" class="seccion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Datos Personales
          </h2>

          <div class="fila-campos">
            <div class="campo" :class="{ 'campo-error': errors.fechaNacimiento, 'campo-valido': campoValido('fechaNacimiento') }">
              <label class="etiqueta">Fecha de Nacimiento <span class="obligatorio">*</span></label>
              <input
                type="date"
                v-model="form.fechaNacimiento"
                class="input-campo"
                :class="{ 'borde-error': errors.fechaNacimiento, 'borde-valido': campoValido('fechaNacimiento') }"
                :max="hoyISO"
                @change="validarCampo('fechaNacimiento')"
              >
              <transition name="error-fade">
                <small v-if="errors.fechaNacimiento" class="mensaje-error">{{ errors.fechaNacimiento }}</small>
              </transition>
            </div>

            <div class="campo" :class="{ 'campo-error': errors.genero, 'campo-valido': campoValido('genero') }">
              <label class="etiqueta">Género <span class="obligatorio">*</span></label>
              <select
                v-model="form.genero"
                class="input-campo"
                :class="{ 'borde-error': errors.genero, 'borde-valido': campoValido('genero') }"
                @change="validarCampo('genero')"
              >
                <option value="">Seleccionar</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
                <option value="No especificado">No especificado</option>
              </select>
              <transition name="error-fade">
                <small v-if="errors.genero" class="mensaje-error">{{ errors.genero }}</small>
              </transition>
            </div>

            <div class="campo">
              <label class="etiqueta">Estado Civil</label>
              <select v-model="form.estadoCivil" class="input-campo">
                <option value="">Seleccionar</option>
                <option value="Soltero/a">Soltero/a</option>
                <option value="Casado/a">Casado/a</option>
                <option value="Divorciado/a">Divorciado/a</option>
                <option value="Viudo/a">Viudo/a</option>
                <option value="Unión libre">Unión libre</option>
              </select>
            </div>

            <div class="campo">
              <label class="etiqueta">Nacionalidad</label>
              <input
                type="text"
                v-model.trim="form.nacionalidad"
                class="input-campo"
                placeholder="Ej. Mexicana"
              >
            </div>
          </div>
        </section>

        <!-- Sección 3: Contacto -->
        <section class="seccion seccion-sin-borde">
          <h2 class="seccion-titulo">
            <svg xmlns="http://www.w3.org/2000/svg" class="seccion-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Contacto
          </h2>

          <div class="fila-campos">
            <div class="campo" :class="{ 'campo-error': errors.correo, 'campo-valido': campoValido('correo') }">
              <label class="etiqueta">Correo Electrónico</label>
              <input
                type="email"
                v-model.trim="form.correo"
                class="input-campo"
                :class="{ 'borde-error': errors.correo, 'borde-valido': campoValido('correo') }"
                placeholder="ejemplo@correo.com"
                @input="validarCampo('correo')"
                @blur="validarCampo('correo')"
              >
              <transition name="error-fade">
                <small v-if="errors.correo" class="mensaje-error">{{ errors.correo }}</small>
              </transition>
            </div>

            <div class="campo" :class="{ 'campo-error': errors.telefono, 'campo-valido': campoValido('telefono') }">
              <label class="etiqueta">Teléfono <span class="etiqueta-hint">10 dígitos</span></label>
              <input
                type="tel"
                v-model="form.telefono"
                class="input-campo"
                :class="{ 'borde-error': errors.telefono, 'borde-valido': campoValido('telefono') }"
                placeholder="Ej. 9511234567"
                maxlength="10"
                @input="onTelefonoInput"
                @blur="validarCampo('telefono')"
              >
              <transition name="error-fade">
                <small v-if="errors.telefono" class="mensaje-error">{{ errors.telefono }}</small>
              </transition>
            </div>
          </div>

          <div class="fila-campos">
            <div class="campo campo-full">
              <label class="etiqueta">Dirección</label>
              <textarea
                v-model.trim="form.direccion"
                class="input-campo textarea-campo"
                placeholder="Calle, número, colonia, ciudad..."
                rows="3"
              ></textarea>
            </div>
          </div>
        </section>

        <div class="form-acciones">
          <button class="btn-cancelar" @click="cancelar" :disabled="isLoading">Cancelar</button>
          <button class="btn-guardar" @click="guardarPersona" :disabled="isLoading">
            <span v-if="isLoading" class="spinner"></span>
            {{ isLoading ? 'Guardando...' : 'Guardar registro' }}
          </button>
        </div>
      </div>

      <footer class="footer-institucional">
        <span>Sistema Integral de Control Escolar — TecNM</span>
        <span>© {{ anioActual }}</span>
      </footer>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const API = `${import.meta.env.VITE_API_URL}/api`

const router     = useRouter()
const route      = useRoute()
const anioActual = new Date().getFullYear()
const hoyISO     = new Date().toISOString().split('T')[0]

const esEdicion  = computed(() => !!route.params.id)

const form = reactive({
  curp: '', nombre: '', apellidoPaterno: '', apellidoMaterno: '',
  fechaNacimiento: '', genero: '', estadoCivil: '',
  nacionalidad: 'Mexicana', correo: '', telefono: '', direccion: ''
})

const errors       = reactive({})
const tocados      = reactive({})
const notification = reactive({ message: '', type: '' })
const isLoading    = ref(false)

const CURP_PATTERN = /^[A-Z]{4}\d{6}[HM][A-Z]{5}[0-9A-Z]\d$/

const onCurpInput = () => {
  form.curp = form.curp.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 18)
  validarCampo('curp')
}

const onTelefonoInput = () => {
  form.telefono = form.telefono.replace(/\D/g, '').slice(0, 10)
  validarCampo('telefono')
}

const validarCampo = (campo) => {
  tocados[campo] = true
  switch (campo) {
    case 'curp':
      if (!form.curp) errors.curp = 'La CURP es obligatoria'
      else if (form.curp.length !== 18) errors.curp = 'La CURP debe tener exactamente 18 caracteres'
      else if (!CURP_PATTERN.test(form.curp)) errors.curp = 'Formato de CURP inválido'
      else delete errors.curp
      break
    case 'nombre':
      if (!form.nombre.trim()) errors.nombre = 'El nombre es obligatorio'
      else if (form.nombre.trim().length < 2) errors.nombre = 'Debe tener al menos 2 caracteres'
      else delete errors.nombre
      break
    case 'apellidoPaterno':
      if (!form.apellidoPaterno.trim()) errors.apellidoPaterno = 'El apellido paterno es obligatorio'
      else if (form.apellidoPaterno.trim().length < 2) errors.apellidoPaterno = 'Debe tener al menos 2 caracteres'
      else delete errors.apellidoPaterno
      break
    case 'fechaNacimiento':
      if (!form.fechaNacimiento) errors.fechaNacimiento = 'La fecha de nacimiento es obligatoria'
      else if (form.fechaNacimiento > hoyISO) errors.fechaNacimiento = 'La fecha no puede ser futura'
      else delete errors.fechaNacimiento
      break
    case 'genero':
      if (!form.genero) errors.genero = 'Seleccione un género'
      else delete errors.genero
      break
    case 'correo':
      if (form.correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.correo))
        errors.correo = 'Formato de correo inválido'
      else delete errors.correo
      break
    case 'telefono':
      if (form.telefono && form.telefono.length !== 10)
        errors.telefono = 'El teléfono debe tener 10 dígitos'
      else delete errors.telefono
      break
  }
}

const campoValido = (campo) => tocados[campo] && !errors[campo] && (
  campo === 'curp' ? form.curp.length === 18 :
  campo === 'correo' ? form.correo.length > 0 :
  campo === 'telefono' ? form.telefono.length > 0 : true
)

const validarFormulario = () => {
  ['curp', 'nombre', 'apellidoPaterno', 'fechaNacimiento', 'genero'].forEach(c => validarCampo(c))
  if (form.correo) validarCampo('correo')
  if (form.telefono) validarCampo('telefono')
  return Object.keys(errors).length === 0
}

onMounted(async () => {
  if (esEdicion.value) {
    try {
      const res = await fetch(`${API}/personas/${route.params.id}`)
      const data = await res.json()
      Object.assign(form, {
        curp: data.curp || '', nombre: data.nombre || '',
        apellidoPaterno: data.apellido_paterno || '',
        apellidoMaterno: data.apellido_materno || '',
        fechaNacimiento: data.fecha_nacimiento || '',
        genero: data.genero || '', estadoCivil: data.estado_civil || '',
        nacionalidad: data.nacionalidad || '', correo: data.correo || '',
        telefono: data.telefono || '', direccion: data.direccion || ''
      })
    } catch { showNotification('No se pudieron cargar los datos de la persona.', 'error') }
  }
})

const guardarPersona = async () => {
  if (!validarFormulario()) { showNotification('Corrige los errores marcados.', 'error'); return }
  isLoading.value = true
  const payload = {
    curp: form.curp, nombre: form.nombre.trim(),
    apellido_paterno: form.apellidoPaterno.trim(),
    apellido_materno: form.apellidoMaterno.trim() || null,
    fecha_nacimiento: form.fechaNacimiento, genero: form.genero,
    estado_civil: form.estadoCivil || null, nacionalidad: form.nacionalidad || null,
    correo: form.correo || null, telefono: form.telefono || null, direccion: form.direccion || null
  }
  try {
    const url = esEdicion.value ? `${API}/personas/${route.params.id}` : `${API}/personas`
    const method = esEdicion.value ? 'PUT' : 'POST'
    const response = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(payload) })
    const data = await response.json()
    if (response.ok) {
      showNotification(esEdicion.value ? 'Persona actualizada correctamente.' : 'Persona registrada correctamente.', 'success')
      setTimeout(() => router.push('/personas'), 1500)
    } else throw new Error(JSON.stringify(data))
  } catch (error) {
    console.error(error)
    showNotification('Ocurrió un error al guardar el registro.', 'error')
  } finally { isLoading.value = false }
}

const cancelar = () => router.push('/personas')
const showNotification = (message, type) => {
  notification.message = message; notification.type = type
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

  /* Ocupa todo el ancho disponible del área de contenido */
  width: 100%;
  min-width: 0;
  box-sizing: border-box;
  background: var(--fondo);
  font-family: 'Montserrat', sans-serif;
}

.breadcrumb { display: flex; align-items: center; gap: 6px; color: var(--gris); font-size: 0.88rem; margin-bottom: 1rem; }
.breadcrumb-link { cursor: pointer; color: var(--azul); font-weight: 500; transition: color 0.15s; }
.breadcrumb-link:hover { color: var(--azul-hover); text-decoration: underline; }
.breadcrumb-sep { color: #9CA3AF; }
.breadcrumb-actual { color: var(--gris); font-weight: 600; }

.page-header { margin-bottom: 1.4rem; }
.page-title { color: var(--texto); font-size: 1.75rem; font-weight: 700; margin: 0 0 0.25rem; letter-spacing: -0.02em; }
.subtitle { color: var(--gris); font-size: 0.93rem; margin: 0; }
.obligatorio { color: var(--rojo); }

/* Toast */
.toast {
  position: fixed; top: 88px; right: 28px; display: flex; align-items: center; gap: 10px;
  padding: 13px 20px; border-radius: 10px; color: white; font-weight: 500; font-size: 0.93rem;
  box-shadow: 0 6px 20px rgba(0,0,0,0.2); z-index: 9999;
  font-family: 'Montserrat', sans-serif; max-width: 380px;
}
.toast.success { background: #16A34A; }
.toast.error   { background: #DC2626; }
.toast-icono { width: 20px; height: 20px; flex-shrink: 0; }
.toast-slide-enter-active, .toast-slide-leave-active { transition: all 0.35s ease; }
.toast-slide-enter-from, .toast-slide-leave-to { opacity: 0; transform: translateX(110%); }

/* Card: 100% del ancho disponible, sin max-width */
.form-card {
  background: #FFF;
  border-radius: 14px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
  padding: 2rem 2.2rem;
  width: 100%;
  box-sizing: border-box;
  border: 1px solid var(--borde);
}

/* Secciones */
.seccion { margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid var(--borde); }
.seccion-sin-borde { border-bottom: none; margin-bottom: 1rem; padding-bottom: 0; }
.seccion-titulo {
  display: flex; align-items: center; gap: 8px; color: var(--azul); font-size: 1.1rem;
  font-weight: 700; margin: 0 0 1.2rem; padding-bottom: 0.6rem; border-bottom: 2px solid var(--borde);
}
.seccion-icono { width: 20px; height: 20px; stroke: var(--azul); flex-shrink: 0; }

/* Grid fluido: columnas de mínimo 220px que se adaptan al espacio */
.fila-campos {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.2rem;
  margin-bottom: 1rem;
}

/* CURP: ocupa toda la fila (1 columna que se expande) */
.curp-row {
  grid-template-columns: 1fr;
}

/* Dirección: ocupa toda la fila */
.campo-full { grid-column: 1 / -1; }

.campo { min-width: 0; position: relative; }

.etiqueta {
  display: flex; align-items: center; gap: 6px; margin-bottom: 6px;
  font-weight: 600; font-size: 0.9rem; color: var(--texto);
  font-family: 'Montserrat', sans-serif; flex-wrap: wrap;
}
.etiqueta-hint {
  font-weight: 400; font-size: 0.78rem; color: var(--azul);
  background: #DBEAFE; padding: 2px 7px; border-radius: 10px; margin-left: 4px;
}

.input-campo {
  width: 100%; padding: 10px 14px; font-size: 0.95rem; border: 1.5px solid var(--borde);
  border-radius: 8px; background: #FFF; color: var(--texto);
  font-family: 'Montserrat', sans-serif; outline: none;
  transition: border-color 0.2s, box-shadow 0.2s; box-sizing: border-box;
}
.input-campo:focus { border-color: var(--azul); box-shadow: 0 0 0 3px #DBEAFE; }
.input-campo::placeholder { color: #9CA3AF; }
.textarea-campo { resize: vertical; min-height: 80px; }

.borde-error { border-color: var(--rojo) !important; }
.borde-error:focus { box-shadow: 0 0 0 3px #FEE2E2 !important; }
.borde-valido { border-color: #16A34A !important; }
.borde-valido:focus { box-shadow: 0 0 0 3px #DCFCE7 !important; }

.mensaje-error {
  display: flex; align-items: center; gap: 4px; color: var(--rojo);
  font-size: 0.82rem; margin-top: 5px; font-family: 'Montserrat', sans-serif;
}
.mensaje-error::before { content: '⚠'; font-size: 0.78rem; }
.error-fade-enter-active, .error-fade-leave-active { transition: all 0.25s ease; }
.error-fade-enter-from, .error-fade-leave-to { opacity: 0; transform: translateY(-4px); }

/* CURP wrapper */
.input-curp-wrapper {
  display: flex; align-items: center; border: 1.5px solid var(--borde); border-radius: 8px;
  overflow: hidden; background: #FFF; transition: border-color 0.2s, box-shadow 0.2s;
}
.input-curp-wrapper:focus-within { border-color: var(--azul); box-shadow: 0 0 0 3px #DBEAFE; }
.input-curp-wrapper.borde-error  { border-color: var(--rojo); }
.input-curp-wrapper.borde-error:focus-within  { box-shadow: 0 0 0 3px #FEE2E2; }
.input-curp-wrapper.borde-valido { border-color: #16A34A; }
.input-curp-wrapper.borde-valido:focus-within { box-shadow: 0 0 0 3px #DCFCE7; }

.input-curp {
  border: none !important; box-shadow: none !important; flex: 1;
  font-family: monospace; letter-spacing: 0.08em; font-size: 1rem; font-weight: 600; text-transform: uppercase;
}
.input-curp:focus { box-shadow: none !important; }

.curp-estado-icono { display: flex; align-items: center; padding: 0 12px; flex-shrink: 0; }
.icono-check      { width: 20px; height: 20px; stroke: #16A34A; }
.icono-error-curp { width: 20px; height: 20px; stroke: #DC2626; }

/* Acciones */
.form-acciones {
  display: flex; justify-content: flex-end; gap: 1rem;
  margin-top: 1.6rem; padding-top: 1.4rem; border-top: 1px solid var(--borde);
}
.btn-cancelar {
  padding: 11px 26px; background: #FFF; color: var(--texto); border: 1px solid var(--borde);
  border-radius: 8px; font-weight: 600; cursor: pointer;
  font-family: 'Montserrat', sans-serif; font-size: 0.95rem; transition: background 0.15s;
}
.btn-cancelar:hover:not(:disabled) { background: var(--fondo); }
.btn-cancelar:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-guardar {
  display: flex; align-items: center; gap: 8px; padding: 11px 28px;
  background: var(--azul); color: white; border: none; border-radius: 8px;
  font-weight: 700; cursor: pointer; font-family: 'Montserrat', sans-serif;
  font-size: 0.95rem; transition: background 0.2s;
}
.btn-guardar:hover:not(:disabled) { background: var(--azul-hover); }
.btn-guardar:disabled { opacity: 0.7; cursor: not-allowed; }

.spinner {
  display: inline-block; width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3); border-top-color: white;
  border-radius: 50%; animation: girar 0.8s linear infinite; flex-shrink: 0;
}
@keyframes girar { to { transform: rotate(360deg); } }

.footer-institucional {
  margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--borde);
  display: flex; justify-content: space-between; align-items: center;
  font-size: 0.82rem; color: #9CA3AF; font-family: 'Montserrat', sans-serif;
}

/* Pantallas pequeñas */
@media (max-width: 600px) {
  .form-card { padding: 1.2rem 1rem; }
  .fila-campos { grid-template-columns: 1fr; }
  .form-acciones { flex-direction: column; }
  .btn-cancelar, .btn-guardar { width: 100%; justify-content: center; }
}
</style>