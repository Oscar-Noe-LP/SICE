<template>
  <MainLayout>
    <div class="formulario-page">
      <div class="breadcrumb">
        Servicios Escolares <span class="arrow">›</span> Formulario Alumno
      </div>

      <h1 class="page-title">Formulario Alumno</h1>
      <p class="subtitle">Complete la información del alumno para registrarlo en el sistema.</p>

      <div v-if="notification.message"
           class="toast"
           :class="notification.type">
        {{ notification.message }}
      </div>


      <div class="form-card">

        <div class="section">
          <h2 class="section-title">Información Personal</h2>
          <div class="row">
            <div class="field">
              <label>Nombre <span class="obligatorio">*</span></label>
              <input type="text" v-model.trim="form.nombre" :class="{ 'error': errors.nombre }">
              <small v-if="errors.nombre" class="error-msg">{{ errors.nombre }}</small>
            </div>
            <div class="field">
              <label>Apellido Paterno <span class="obligatorio">*</span></label>
              <input type="text" v-model.trim="form.apellidoPaterno" :class="{ 'error': errors.apellidoPaterno }">
              <small v-if="errors.apellidoPaterno" class="error-msg">{{ errors.apellidoPaterno }}</small>
            </div>
            <div class="field">
              <label>Apellido Materno</label>
              <input type="text" v-model.trim="form.apellidoMaterno">
            </div>
          </div>
          <div class="field">
            <label>Género <span class="obligatorio">*</span></label>
            <select v-model="form.genero" :class="{ 'error': errors.genero }">
              <option value="">Seleccionar</option>
              <option value="Masculino">Masculino</option>
              <option value="Femenino">Femenino</option>
              <option value="Otro">Otro</option>
            </select>
            <small v-if="errors.genero" class="error-msg">{{ errors.genero }}</small>
          </div>
        </div>

        <div class="section">
          <h2 class="section-title">Datos Académicos</h2>
          <div class="row">
            <div class="field">
              <label>Número de Control <span class="obligatorio">*</span></label>
              <input type="text" v-model.trim="form.noControl" maxlength="8" :class="{ 'error': errors.noControl }">
              <small v-if="errors.noControl" class="error-msg">{{ errors.noControl }}</small>
            </div>
            <div class="field">
              <label>Carrera <span class="obligatorio">*</span></label>
              <select v-model="form.carrera" :class="{ 'error': errors.carrera }">
                <option value="">Seleccionar carrera</option>
                <option value="Contador Publico">Contador Publico</option>
                <option value="Ingenieria Civil">Ingenieria Civil</option>
                <option value="Ingenieria en Gestion empresarial">Ingenieria en Gestion empresarial</option>
                <option value="Ingenieria en Sistemas Computacionales">Ingenieria en Sistemas Computacionales</option>
                <option value="Ingenieria Industrial">Ingenieria Industrial</option>
              </select>
              <small v-if="errors.carrera" class="error-msg">{{ errors.carrera }}</small>
            </div>
            <div class="field">
              <label>Semestre <span class="obligatorio">*</span></label>
              <select v-model="form.semestre" :class="{ 'error': errors.semestre }">
                <option value="">Seleccionar</option>
                <option v-for="n in 8" :key="n" :value="n">{{ n }}</option>
              </select>
              <small v-if="errors.semestre" class="error-msg">{{ errors.semestre }}</small>
            </div>
          </div>
        </div>

        <div class="section">
          <h2 class="section-title">Estado y Fecha de Ingreso</h2>
          <div class="row">
            <div class="field">
              <label>Estatus <span class="obligatorio">*</span></label>
              <select v-model="form.estatus" :class="{ 'error': errors.estatus }">
                <option value="Activo">Activo</option>
                <option value="Baja Temporal">Baja Temporal</option>
                <option value="Baja Definitiva">Baja Definitiva</option>
              </select>
            </div>
            <div class="field">
              <label>Fecha de Ingreso <span class="obligatorio">*</span></label>
              <input type="date" v-model="form.fechaIngreso" :class="{ 'error': errors.fechaIngreso }">
              <small v-if="errors.fechaIngreso" class="error-msg">{{ errors.fechaIngreso }}</small>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button class="btn-cancel" @click="cancelar" :disabled="isLoading">Cancelar</button>
          <button class="btn-guardar" @click="guardarAlumno" :disabled="isLoading">
            <span v-if="isLoading" class="spinner"></span>
            {{ isLoading ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'

const router = useRouter()

const form = reactive({
  nombre: '',
  apellidoPaterno: '',
  apellidoMaterno: '',
  genero: '',
  noControl: '',
  carrera: '',
  semestre: '',
  estatus: 'Activo',
  fechaIngreso: ''
})

const errors = reactive({})
const notification = reactive({ message: '', type: '' })
const isLoading = ref(false)

const validarFormulario = () => {
  Object.keys(errors).forEach(key => delete errors[key])

  if (!form.nombre.trim()) errors.nombre = 'El nombre es obligatorio'
  if (!form.apellidoPaterno.trim()) errors.apellidoPaterno = 'El apellido paterno es obligatorio'
  if (!form.noControl.trim() || form.noControl.length !== 8) errors.noControl = 'Debe tener exactamente 8 dígitos'
  if (!form.genero) errors.genero = 'Seleccione un género'
  if (!form.carrera) errors.carrera = 'Seleccione una carrera'
  if (!form.semestre) errors.semestre = 'Seleccione un semestre'
  if (!form.fechaIngreso) errors.fechaIngreso = 'La fecha es obligatoria'

  return Object.keys(errors).length === 0
}

const guardarAlumno = async () => {
  if (!validarFormulario()) {
    showNotification('❌ Corrige los errores marcados', 'error')
    return
  }

  isLoading.value = true

  try {
    const response = await fetch('http://localhost:8000/api/alumnos', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(form)
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.error || 'Error al guardar');
    }

    showNotification('✅ Alumno guardado correctamente', 'success')

    // Esperar un momento para que el usuario vea el mensaje y redirigir
    setTimeout(() => {
      router.push('/alumnos')
    }, 1500)

  } catch (error) {
    showNotification('❌ Error: ' + error.message, 'error')
  } finally {
    isLoading.value = false
  }
}

const cancelar = () => {
  router.push('/alumnos')
}

const showNotification = (message, type) => {
  notification.message = message
  notification.type = type
  setTimeout(() => { notification.message = '' }, 3000)
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.toast {
  position: fixed;
  top: 90px;
  right: 30px;
  padding: 14px 24px;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  box-shadow: 0 6px 20px rgba(0,0,0,0.25);
  z-index: 9999;
  animation: slideIn 0.3s ease;
}
.toast.success { background: #2E7D32; }
.toast.error   { background: #D32F2F; }
@keyframes slideIn { from { transform: translateX(120%); } to { transform: translateX(0); } }

.spinner {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-right: 8px;
  vertical-align: middle;
}
@keyframes spin { to { transform: rotate(360deg); } }


.formulario-page {
  width: 100%;
  padding: 2rem 2.5rem;
}
.page-title {
  text-align: middle;
}

.subtitle {
  text-align: middle;
}

.form-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 12px 35px rgba(0,0,0,0.09);
  padding: 3rem;
  width: 100%;
  max-width: 1000px;
  margin: 0 auto;
}

.section { margin-bottom: 3rem; }
.section-title {
  color: #005187;
  font-size: 1.45rem;
  font-weight: 700;
  margin-bottom: 1.4rem;
  border-bottom: 2px solid #E0E7FF;
  padding-bottom: 0.7rem;
}

.row {
  display: flex;
  gap: 2rem;
  margin-bottom: 1.6rem;
  flex-wrap: wrap;
}
.field { flex: 1; }
.field label { display: block; margin-bottom: 9px; font-weight: 600; color: #1A1A1A; font-size: 1.02rem; }
.obligatorio { color: #D32F2F; }

.field input, .field select {
  padding: 13px 16px;
  font-size: 1.02rem;
}
.field input.error, .field select.error { border-color: #D32F2F; }
.error-msg { color: #D32F2F; font-size: 0.88rem; margin-top: 6px; display: block; }

.form-actions { display: flex; justify-content: flex-end; gap: 1.4rem; margin-top: 3rem; }

.btn-cancel { padding: 13px 30px; background: #F5F7FA; color: #1A1A1A; border: 1px solid #D1D9E6; border-radius: 10px; font-weight: 600; cursor: pointer; }
.btn-guardar {
  padding: 13px 34px;
  background: #005187;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}
.btn-guardar:disabled { opacity: 0.8; cursor: not-allowed; }
</style>