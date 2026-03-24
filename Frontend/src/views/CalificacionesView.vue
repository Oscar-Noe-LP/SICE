```vue
<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="calificaciones-page">

      <div class="breadcrumb">Servicios Escolares › Grupos › Calificaciones</div>
      <h1 class="page-title">Calificaciones</h1>
      <p class="subtitle">Captura de calificaciones de los alumnos del grupo seleccionado</p>

      <!-- FILTROS DINÁMICOS -->
      <div class="filters-card">

        <select v-model="filtroPeriodo" class="filter-select">
          <option v-for="p in periodos" :key="p.id_periodo" :value="p.id_periodo">
            {{ p.nombre_periodo }}
          </option>
        </select>

        <select v-model="filtroCarrera" class="filter-select">
          <option v-for="c in carreras" :key="c.id_carrera" :value="c.id_carrera">
            {{ c.nombre }}
          </option>
        </select>

        <select v-model="filtroMateria" class="filter-select">
          <option v-for="m in materias" :key="m.id_materia" :value="m.id_materia">
            {{ m.nombre }}
          </option>
        </select>

        <select v-model="filtroGrupo" class="filter-select">
          <option v-for="g in grupos" :key="g.id_grupo" :value="g.id_grupo">
            {{ g.clave_grupo }}
          </option>
        </select>

        <button @click="buscar" class="btn-buscar">
          Buscar
        </button>

        <button class="btn-exportar">Exportar ▼</button>
      </div>

      <!-- PROMEDIO -->
      <div class="average-card">
        <div class="avg-text">
          <span class="avg-label">Promedio General</span>
          <span class="avg-number">{{ promedioGeneral }}</span>
        </div>
      </div>

      <!-- TABLA -->
      <div class="table-container">
        <table class="calif-table">
          <thead>
            <tr>
              <th>No. Control</th>
              <th>Alumno</th>
              <th class="text-center">Parcial 1 (30%)</th>
              <th class="text-center">Parcial 2 (30%)</th>
              <th class="text-center">Proyecto (40%)</th>
              <th class="text-center">Final</th>
              <th class="text-center">NC</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="alumno in alumnos" :key="alumno.control">
              <td>{{ alumno.control }}</td>
              <td>{{ alumno.nombre }}</td>

              <td class="text-center">
                <input v-model="alumno.p1" type="number" step="0.1" class="nota-input">
              </td>

              <td class="text-center">
                <input v-model="alumno.p2" type="number" step="0.1" class="nota-input">
              </td>

              <td class="text-center">
                <input v-model="alumno.proy" type="number" step="0.1" class="nota-input">
              </td>

              <td class="text-center final">
                {{ calcularFinal(alumno) }}
              </td>

              <td class="text-center">
                <span v-if="esNC(alumno)" class="nc-badge">NC</span>
                <span v-else>{{ calcularFinal(alumno) }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- BOTÓN -->
      <div class="actions-bar">
        <button 
          type="button"
          @click="guardarTodo" 
          class="btn-guardar" 
          :disabled="isLoading"
        >
          {{ isLoading ? 'Guardando...' : 'Guardar Cambios' }}
        </button>
      </div>

    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const API_URL = 'http://localhost:8000/api'

// 🔹 FILTROS
const filtroPeriodo = ref(null)
const filtroCarrera = ref(null)
const filtroMateria = ref(null)
const filtroGrupo = ref(null)

// 🔹 LISTAS DINÁMICAS
const periodos = ref([])
const carreras = ref([])
const materias = ref([])
const grupos = ref([])

// 🔹 TABLA
const alumnos = ref([])
const isLoading = ref(false)

// 🔥 CARGAR FILTROS DESDE LARAVEL
const cargarFiltros = async () => {
  try {
    const response = await fetch(`${API_URL}/filtros`)
    const data = await response.json()

    periodos.value = data.periodos
    carreras.value = data.carreras
    materias.value = data.materias
    grupos.value = data.grupos

  } catch (error) {
    console.error("Error cargando filtros", error)
  }
}

// 🔥 CARGAR CALIFICACIONES
const cargarDatos = async () => {
  try {
    if (!filtroGrupo.value) return

    const response = await fetch(
      `${API_URL}/calificaciones-grupo?grupo_id=${filtroGrupo.value}`
    )

    alumnos.value = await response.json()
  } catch (error) {
    console.error("Error cargando calificaciones", error)
  }
}

// 🔍 BOTÓN BUSCAR
const buscar = () => {
  cargarDatos()
}

// 🧮 FINAL
const calcularFinal = (a) => {
  const nota =
    (Number(a.p1 || 0) * 0.3) +
    (Number(a.p2 || 0) * 0.3) +
    (Number(a.proy || 0) * 0.4)

  return nota.toFixed(1)
}

// 📊 PROMEDIO
const promedioGeneral = computed(() => {
  if (alumnos.value.length === 0) return 0

  const suma = alumnos.value.reduce(
    (acc, a) => acc + Number(calcularFinal(a)),
    0
  )

  return (suma / alumnos.value.length).toFixed(2)
})

// ❌ NC
const esNC = (a) => {
  return Number(calcularFinal(a)) < 6
}

// 💾 GUARDAR
const guardarTodo = async () => {
  isLoading.value = true

  try {
    const response = await fetch(`${API_URL}/guardar-calificaciones`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ alumnos: alumnos.value })
    })

    if (response.ok) {
      alert('✅ Guardado correctamente')
    }
  } catch (error) {
    alert('❌ Error al guardar')
  } finally {
    isLoading.value = false
  }
}

// 🚀 INIT
onMounted(async () => {
  await cargarFiltros()

  // opcional: seleccionar el primer grupo automáticamente
  if (grupos.value.length > 0) {
    filtroGrupo.value = grupos.value[0].id_grupo
    cargarDatos()
  }
})
</script>

<style scoped>
.calificaciones-page { max-width: 100%; }

.page-title { 
  color: #005187;
  font-size: 2.1rem;
  font-weight: 700;
  margin-bottom: 0.4rem;
}

.subtitle { color: #5A5A5A; margin-bottom: 1.8rem; }
.breadcrumb { color: #5A5A5A; margin-bottom: 1rem; font-size: 0.95rem; }

.filters-card {
  background: white;
  padding: 1.4rem;
  border-radius: 12px;
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 2rem;
  box-shadow: 0 8px 25px rgba(0,0,0,0.07);
}

.filter-select {
  padding: 12px 16px;
  border: 1px solid #84B6E4;
  border-radius: 8px;
  min-width: 180px;
}

.btn-buscar {
  background: #005187;
  color: white;
  padding: 12px 28px;
  border-radius: 8px;
  font-weight: 600;
}

.btn-exportar {
  background: white;
  border: 1px solid #005187;
  color: #005187;
  padding: 12px 28px;
  border-radius: 8px;
  font-weight: 600;
}

.average-card {
  background: #F8FAFC;
  border-radius: 12px;
  padding: 1rem 1.6rem;
  margin-bottom: 2rem;
  max-width: 340px;
}

.avg-number {
  font-size: 2.1rem;
  font-weight: 700;
  color: #005187;
}

.table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
}

.calif-table th {
  background: #F8FAFC;
  padding: 16px;
  text-align: center;
}

.nota-input {
  width: 90px;
  text-align: center;
}

.actions-bar {
  display: flex;
  justify-content: flex-end;
  margin-top: 2.5rem;
}

.btn-guardar {
  background: #005187;
  color: white;
  padding: 14px 42px;
  border-radius: 8px;
}

.nc-badge {
  background: #E1F5FE;
  padding: 6px 18px;
  border-radius: 6px;
}
</style>
```