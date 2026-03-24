<template>
  <MainLayout v-slot="{ busquedaGlobal }">
    <div class="inscripcion-page">

      <div class="breadcrumb">
        Servicios Escolares <span class="arrow">›</span> Inscripción
      </div>

      <h1 class="page-title">Inscripción</h1>
      <p class="subtitle">Seleccione al alumno y asígnelo a un grupo disponible para el periodo vigente.</p>

      <div v-if="notification.message" class="toast" :class="notification.type">
        {{ notification.message }}
      </div>

      <div class="content-card">

        <div class="buscar-alumno-section">
          <h3>Buscar Alumno</h3>
          <div class="search-row">
            <div class="selected-student-wrapper">
              <input 
                type="text" 
                v-model="busquedaAlumno"
                placeholder="Nombre o número de control"
                @keyup.enter="seleccionarAlumno"
              />
              <button class="btn-buscar" @click="seleccionarAlumno">Buscar</button>
            </div>
            <select v-model="periodo" class="period-select">
              <option value="Ago/Dic 2024">Ago/Dic 2024</option>
              <option value="Ene/Jun 2025">Ene/Jun 2025</option>
            </select>
          </div>

          <div v-if="alumnoSeleccionado" class="selected-student">
            {{ alumnoSeleccionado.nombre }} • {{ alumnoSeleccionado.noControl }} • 
            {{ alumnoSeleccionado.carrera }} ({{ alumnoSeleccionado.semestre }} Semestre)
          </div>
        </div>

        <div class="seleccionar-grupo-section">
          <h3>Seleccionar Grupo</h3>
          <div class="group-search-bar">
            <input 
              type="text" 
              v-model="busquedaGrupo"
              placeholder="Buscar grupo..."
              class="group-input"
            />
            <button class="btn-filtrar" @click="filtrarGrupos">Filtrar</button>
          </div>

          <div class="table-container">
            <table class="inscripcion-table">
              <thead>
                <tr>
                  <th>Materia</th>
                  <th>Docente</th>
                  <th>Aula</th>
                  <th>Capacidad</th>
                  <th>Inscritos</th>
                  <th class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="grupo in gruposFiltrados" :key="grupo.id">
                  <td>{{ grupo.materia }}</td>
                  <td>{{ grupo.docente }}</td>
                  <td>{{ grupo.aula }}</td>
                  <td class="text-center">{{ grupo.capacidad }}</td>
                  <td class="text-center">
                    <span class="inscritos-badge">{{ grupo.inscritos }} / {{ grupo.capacidad }}</span>
                  </td>
                  <td class="text-center">
                    <button 
                      v-if="grupo.inscritos < grupo.capacidad"
                      class="btn-inscribir"
                      @click="inscribirAlumno(grupo)">
                      Inscribir
                    </button>
                    <button v-else class="btn-lleno" disabled>Lleno</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="pagination">
            <span>Página 1 de 4</span>
            <div class="pagination-buttons">
              <button class="page-btn" disabled>‹</button>
              <button class="page-btn active">1</button>
              <button class="page-btn">2</button>
              <button class="page-btn">3</button>
              <button class="page-btn">4</button>
              <button class="page-btn">›</button>
            </div>
            <span class="mostrando">Mostrando 3 de 10 grupos disponibles.</span>
          </div>
        </div>

      </div>
    </div>
  </MainLayout>
</template>


<script setup>
import { ref, onMounted, computed, reactive } from 'vue'
import MainLayout from '@/layouts/MainLayout.vue'

const busquedaAlumno = ref('')
const busquedaGrupo = ref('')
const periodo = ref('Ago/Dic 2024')
const alumnoSeleccionado = ref(null)
const grupos = ref([])

const notification = reactive({ message: '', type: '' })

// Cargar grupos al iniciar
onMounted(async () => {
  const res = await fetch('http://localhost:8000/api/grupos-disponibles');
  grupos.value = await res.json();
});

const gruposFiltrados = computed(() => {
  return grupos.value.filter(g => 
    g.materia.toLowerCase().includes(busquedaGrupo.value.toLowerCase())
  )
})

const seleccionarAlumno = async () => {
  if (!busquedaAlumno.value.trim()) return;
  
  try {
    const res = await fetch(`http://localhost:8000/api/buscar-alumno?q=${busquedaAlumno.value}`);
    if (!res.ok) throw new Error();
    alumnoSeleccionado.value = await res.json();
    showNotification('Alumno encontrado', 'success');
  } catch (err) {
    showNotification('No se encontró el alumno', 'error');
    alumnoSeleccionado.value = null;
  }
}

const inscribirAlumno = async (grupo) => {
  if (!alumnoSeleccionado.value) {
    showNotification('Primero busca y selecciona un alumno', 'error');
    return;
  }

  try {
    const res = await fetch('http://localhost:8000/api/inscribir', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        id_alumno: alumnoSeleccionado.value.id_alumno,
        id_grupo: grupo.id
      })
    });

    if (res.ok) {
      grupo.inscritos++;
      showNotification(`✅ Inscrito en ${grupo.materia}`, 'success');
    }
  } catch (err) {
    showNotification('Error al procesar inscripción', 'error');
  }
}

const showNotification = (message, type) => {
  notification.message = message
  notification.type = type
  setTimeout(() => { notification.message = '' }, 3000)
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.inscripcion-page {
  width: 100%;
  padding: 2rem 2.5rem;
}

.breadcrumb {
  color: #5A5A5A;
  font-size: 0.95rem;
  margin-bottom: 1rem;
}
.arrow { color: #1A1A1A; font-weight: bold; }

.page-title {
  text-align: left;
  font-size: 2.4rem;
  font-weight: 700;
  color: #1A1A1A;
  margin-bottom: 0.5rem;
}
.subtitle {
  text-align: left;
  color: #5A5A5A;
  margin-bottom: 2rem;
}

.content-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 12px 35px rgba(0,0,0,0.09);
  padding: 3rem;
  max-width: 1100px;
  margin: 0 auto;
}

.buscar-alumno-section h3 {
  color: #005187;
  margin-bottom: 1rem;
  font-size: 1.35rem;
}
.search-row {
  display: flex;
  gap: 12px;
  align-items: center;
  flex-wrap: wrap;
}
.selected-student-wrapper { flex: 1; display: flex; }
.selected-student-wrapper input {
  flex: 1;
  padding: 14px 16px;
  border: 1px solid #D1D9E6;
  border-radius: 10px;
}
.btn-buscar {
  background: #005187;
  color: white;
  padding: 14px 28px;
  border-radius: 10px;
  font-weight: 600;
}
.period-select {
  padding: 14px 16px;
  border: 1px solid #D1D9E6;
  border-radius: 10px;
}
.selected-student {
  margin-top: 1rem;
  padding: 14px 20px;
  background: #F5F7FA;
  border-radius: 10px;
  font-weight: 500;
  color: #1A1A1A;
}

.seleccionar-grupo-section h3 {
  color: #005187;
  margin: 2.5rem 0 1rem;
  font-size: 1.35rem;
}
.group-search-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 1.5rem;
}
.group-input {
  flex: 1;
  padding: 14px 16px;
  border: 1px solid #D1D9E6;
  border-radius: 10px;
}
.btn-filtrar {
  background: #005187;
  color: white;
  padding: 14px 28px;
  border-radius: 10px;
  font-weight: 600;
}

.table-container { overflow-x: auto; }
.inscripcion-table {
  width: 100%;
  border-collapse: collapse;
}
.inscripcion-table th {
  background: #F5F7FA;
  padding: 18px 16px;
  font-weight: 600;
  color: #1A1A1A;
}
.inscripcion-table td {
  padding: 18px 16px;
  border-bottom: 1px solid #E0E7FF;
}
.inscritos-badge { font-weight: 600; color: #1A1A1A; }

.btn-inscribir {
  background: #005187;
  color: white;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}
.btn-lleno {
  background: #F5F7FA;
  color: #1A1A1A;
  border: 1px solid #D1D9E6;
  padding: 10px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: not-allowed;
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 2rem;
  font-size: 0.95rem;
  color: #5A5A5A;
}
.pagination-buttons button {
  margin: 0 4px;
  padding: 6px 12px;
  border: 1px solid #D1D9E6;
  background: white;
  border-radius: 6px;
}
.pagination-buttons .active {
  background: #005187;
  color: white;
}

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
.toast.error { background: #D32F2F; }
@keyframes slideIn { from { transform: translateX(120%); } to { transform: translateX(0); } }
</style>