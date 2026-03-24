<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="evaluaciones-page">

      <div class="breadcrumb">Servicios Escolares › Grupos › Evaluaciones</div>
      <h1 class="page-title">Evaluaciones</h1>

      <div class="subject-card">
        <div class="subject-info">
          <h2>Algoritmos y Programación</h2>
          <p>Aula: A-201 Periodo: Ago/Dic 2024<br>Docente: Mtro. Juan Morales</p>
        </div>
        <button @click="abrirModalNueva" class="btn-nueva-eval">
          <svg xmlns="http://www.w3.org/2000/svg" class="plus-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
          </svg>
          Nueva Evaluación
        </button>
      </div>

      <div class="table-container">
        <table class="eval-table">
          <thead>
            <tr>
              <th>Evaluación</th>
              <th class="text-center">Porcentaje</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in criterios" :key="index">
              <td>{{ item.nombre }}</td>
              <td class="text-center">
                <input v-model="item.porcentaje" type="number" min="0" max="100" class="porcentaje-input"> %
              </td>
              <td class="text-center actions">
                <button @click="editar(item)" class="btn-edit">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                </button>
                <button @click="eliminar(index)" class="btn-delete">
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.595 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.595-1.858L5 7m5 4v6m4-6v6m1-10V9a1 1 0 00-1-1h-4a1 1 0 00-1 1v1M12 4v6m2-3h2" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="bottom-bar">
        <div class="circular-wrapper">
          <div class="circular-progress">
            <svg viewBox="0 0 36 36" class="small-circle">
              <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#E5E7EB" stroke-width="4"/>
              <path :d="circlePath" fill="none" stroke="#005187" stroke-width="4" stroke-dasharray="100, 100"/>
            </svg>
            <div class="percent-text">{{ totalPorcentaje }}%</div>
          </div>
        </div>

        <button 
          @click="guardarCambios"
          :disabled="totalPorcentaje !== 100"
          class="btn-guardar"
        >
          Guardar Cambios
        </button>
      </div>

    </div>
  </MainLayout>

  <div v-if="showModal" class="modal-overlay" @click.self="cerrarModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Nueva Evaluación</h3>
        <button @click="cerrarModal" class="close-btn">✕</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Nombre de la evaluación</label>
          <input 
            v-model="nuevoNombre" 
            type="text" 
            placeholder="Ej: Examen Final, Práctica, Proyecto..." 
            class="modal-input" 
            @keyup.enter="guardarNuevaEvaluacion"
            autofocus
          >
        </div>
        <div class="form-group">
          <label>Porcentaje inicial (%)</label>
          <div class="percentage-input-wrapper">
            <input 
              v-model="nuevoPorcentaje" 
              type="number" 
              min="0" 
              max="100" 
              step="1"
              placeholder="0" 
              class="modal-input percentage-input" 
              @keyup.enter="guardarNuevaEvaluacion"
            >
            <span class="percentage-symbol">%</span>
          </div>
          <div class="percentage-hint">
            Valor recomendado: 20-40% (Total actual: {{ totalPorcentaje }}%)
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button @click="cerrarModal" class="btn-cancelar">Cancelar</button>
        <button 
          @click="guardarNuevaEvaluacion" 
          class="btn-guardar-modal" 
          :disabled="!nuevoNombre.trim() || nuevoPorcentaje < 0 || nuevoPorcentaje > 100"
        >
          Guardar Evaluación
        </button>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted, computed } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

// --- ESTADO ---
const idGrupo = ref(1) // ID del grupo actual (puedes recibirlo por props o ruta)
const criterios = ref([])
const showModal = ref(false)
const nuevoNombre = ref('')
const nuevoPorcentaje = ref(0)
const editandoIndex = ref(null)

// --- COMPUTADOS ---
const totalPorcentaje = computed(() => {
  return criterios.value.reduce((sum, c) => sum + Number(c.porcentaje), 0)
})

const circlePath = computed(() => `M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831`)

// --- MÉTODOS API ---

// Cargar evaluaciones desde el backend al montar el componente
const fetchEvaluaciones = async () => {
  try {
    const res = await fetch(`http://localhost:8000/api/evaluaciones/${idGrupo.value}`)
    const data = await res.json()
    
    // Si hay datos en la BD los cargamos, si no, dejamos una lista vacía o default
    if (data && data.length > 0) {
      criterios.value = data
    } else {
      criterios.value = [
        { nombre: 'Parcial 1', porcentaje: 30 },
        { nombre: 'Parcial 2', porcentaje: 30 },
        { nombre: 'Proyecto', porcentaje: 40 }
      ]
    }
  } catch (error) {
    console.error("Error al conectar con la API:", error)
  }
}

// Guardar todos los criterios en la base de datos
const guardarCambios = async () => {
  if (totalPorcentaje.value !== 100) {
    alert('El porcentaje total debe ser exactamente 100% para guardar.')
    return
  }

  try {
    const res = await fetch('http://localhost:8000/api/evaluaciones/guardar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id_grupo: idGrupo.value,
        criterios: criterios.value
      })
    })

    if (res.ok) {
      alert('✅ Evaluaciones guardadas correctamente en el sistema SICE')
    } else {
      throw new Error('Error en la respuesta del servidor')
    }
  } catch (error) {
    alert('❌ No se pudieron guardar los cambios. Revisa la conexión con Laravel.')
  }
}

// --- LÓGICA DE INTERFAZ (MODAL Y TABLA) ---

const abrirModalNueva = () => {
  nuevoNombre.value = ''
  nuevoPorcentaje.value = 0
  editandoIndex.value = null
  showModal.value = true
}

const cerrarModal = () => {
  showModal.value = false
}

const guardarNuevaEvaluacion = () => {
  if (!nuevoNombre.value.trim()) {
    alert('Debes escribir un nombre para la evaluación')
    return
  }

  const porcentajeNum = Number(nuevoPorcentaje.value)

  if (porcentajeNum < 0 || porcentajeNum > 100) {
    alert('El porcentaje debe estar entre 0 y 100')
    return
  }

  // Si estamos creando una nueva, verificamos que no pase del 100%
  if (editandoIndex.value === null) {
    if (totalPorcentaje.value + porcentajeNum > 100) {
      alert(`El porcentaje total excedería el 100% (Actual: ${totalPorcentaje.value}%)`)
      return
    }
    criterios.value.push({ 
      nombre: nuevoNombre.value.trim(), 
      porcentaje: porcentajeNum 
    })
  } else {
    // Si estamos editando una existente
    criterios.value[editandoIndex.value].nombre = nuevoNombre.value.trim()
    criterios.value[editandoIndex.value].porcentaje = porcentajeNum
  }

  cerrarModal()
}

const editar = (item) => {
  const index = criterios.value.indexOf(item)
  nuevoNombre.value = item.nombre
  nuevoPorcentaje.value = item.porcentaje
  editandoIndex.value = index
  showModal.value = true
}

const eliminar = (index) => {
  if (confirm('¿Estás seguro de eliminar este criterio de evaluación?')) {
    criterios.value.splice(index, 1)
  }
}

// Ejecutar carga inicial
onMounted(fetchEvaluaciones)
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.evaluaciones-page { width: 100%; max-width: 1200px; margin: 0 auto; }

.page-title { color: #005187; font-size: 2.4rem; font-weight: 700; margin-bottom: 0.5rem; }
.breadcrumb { color: #5A5A5A; margin-bottom: 1rem; font-size: 0.95rem; }

.subject-card { background: white; padding: 1.8rem; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; box-shadow: 0 8px 25px rgba(0,0,0,0.07); }
.btn-nueva-eval { background: #005187; color: white; padding: 12px 24px; border-radius: 8px; font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer; border: none; }
.plus-icon { width: 20px; height: 20px; stroke: white; }

.table-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.07); margin-bottom: 2rem; }
.eval-table { width: 100%; border-collapse: collapse; }
.eval-table th { background: #F8FAFC; padding: 18px 16px; font-weight: 600; }
.eval-table td { padding: 18px 16px; border-bottom: 1px solid #E5E9F0; }
.porcentaje-input { width: 100px; text-align: center; padding: 10px; border: 1px solid #D1D9E6; border-radius: 8px; }
.actions { display: flex; gap: 8px; }
.btn-edit, .btn-delete { width: 38px; height: 38px; border: none; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.btn-edit { background: #4D82BE; }
.btn-delete { background: #D32F2F; }
.icon-svg { width: 18px; height: 18px; stroke: white; }

.bottom-bar { display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; }
.circular-wrapper { width: 130px; height: 130px; position: relative; }
.circular-progress svg { transform: rotate(-90deg); width: 130px; height: 130px; }
.percent-text { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2rem; font-weight: 700; color: #005187; }

.btn-guardar { background: #005187; color: white; padding: 14px 40px; border-radius: 8px; font-weight: 600; font-size: 1.05rem; cursor: pointer; border: none; }
.btn-guardar:hover:not(:disabled) { background: #003F6F; }
.btn-guardar:disabled { background: #9AA3AF; cursor: not-allowed; }

.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 2000; }
.modal-content { background: white; width: 520px; max-width: 90%; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.3); }
.modal-header { background: #005187; color: white; padding: 1.25rem 1.8rem; display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; font-size: 1.45rem; font-weight: 700; }
.close-btn { background: none; border: none; color: white; font-size: 1.8rem; cursor: pointer; }
.modal-body { padding: 2rem 1.8rem; }
.form-group { margin-bottom: 1.8rem; }
.form-group label { display: block; margin-bottom: 10px; font-weight: 600; color: #1A1A1A; }
.modal-input { width: 100%; padding: 14px 16px; border: 2px solid #E5E9F0; border-radius: 12px; font-size: 1rem; }
.percentage-input-wrapper { position: relative; }
.percentage-symbol { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: #005187; font-weight: 700; }
.percentage-hint { margin-top: 8px; font-size: 0.85rem; color: #6B7280; }
.modal-footer { padding: 1.2rem 1.8rem; background: #F8FAFC; display: flex; gap: 12px; justify-content: flex-end; border-top: 1px solid #E5E9F0; }
.btn-cancelar, .btn-guardar-modal { padding: 12px 28px; border-radius: 10px; font-weight: 600; cursor: pointer; }
.btn-cancelar { background: #9AA3AF; color: white; }
.btn-guardar-modal { background: #005187; color: white; }
</style>