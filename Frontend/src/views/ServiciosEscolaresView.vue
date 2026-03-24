<template>
<MainLayout v-slot="{ busquedaGlobal }">
    <div class="servicios-escolares-page">

      <div class="breadcrumb">
        Servicios Escolares
      </div>

      <h1 class="page-title">Servicios Escolares</h1>

      <div v-if="notification.message" class="toast" :class="notification.type">
        {{ notification.message }}
      </div>

      <div class="content-card">

        <div class="stats-grid">
          <div class="stat-card blue">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2" />
            </svg>
            <div class="info">
              <h3>Alumnos Registrados</h3>
              <p class="number">{{ resumen.total_alumnos }}</p>
              <a href="#" class="ver-link" @click.prevent="irAAlumnos">
                Ver Alumnos →
              </a>
              <h3>Alumnos Activos</h3>
              <p class="number">{{ totalAlumnos }}</p>
              <a href="#" class="ver-link" @click.prevent="irAAlumnos">Ver Alumnos →</a>
            </div>
          </div>

          <div class="stat-card">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18 9.246 18 10.832 18.477 12 19.253zm0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18 14.754 18 13.168 18.477 12 19.253z" />
            </svg>
            <div class="info">
              <h3>Inscripciones Totales</h3>
              <p class="number">{{ resumen.total_inscripciones }}</p>
              <a href="#" class="ver-link" @click.prevent="irAInscripciones">
                Ver Inscripciones →
              </a>
              <h3>Inscripciones del Período</h3>
              <p class="number">{{ totalInscripciones }}</p>
              <a href="#" class="ver-link" @click.prevent="irAInscripciones">Ver Inscripciones →</a>
            </div>
          </div>

          <div class="stat-card">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2" />
            </svg>
            <div class="info">
              <h3>Grupos Abiertos</h3>
              <p class="number">24</p>
              <a href="#" class="ver-link" @click.prevent="irAGrupos">
                Ver Grupos →
              </a>
            </div>
          </div>

          <div class="stat-card">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 8.944 11.922.42.095.858.143 1.295.143a3 3 0 01.935-.072" />
            </svg>
            <div class="info">
              <h3>Evaluaciones</h3>
              <p class="number">16</p>
              <a href="#" class="ver-link" @click.prevent="irAEvaluaciones">
                Ver Evaluaciones →
              </a>
              <h3>Evaluaciones pendientes</h3>
              <p class="number">{{ totalEvaluaciones }}</p>
              <a href="#" class="ver-link" @click.prevent="irAEvaluaciones">Ver Evaluaciones →</a>
            </div>
          </div>
        </div>

        <div class="mensaje-importante">
          <svg xmlns="http://www.w3.org/2000/svg" class="mensaje-icon" fill="none" viewBox="0 0 24 24" stroke="#D2B48C" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <div class="mensaje-texto">
            <strong>Estado del Sistema</strong><br>
            Conexión establecida con la base de datos SICE. Mostrando registros en tiempo real.
          </div>
          <button class="btn-nueva-inscripcion">
            + Nueva Inscripción
          </button>
        </div>

        <div class="ultimas-inscripciones">
          <div class="table-header">
            <h3>Últimas Inscripciones</h3>
            <div class="filtros">
              <input type="text" placeholder="Filtrar resultados..." class="search-input">
              <button class="btn-gestionar" @click="cargarDatos">Actualizar</button>
            </div>
          </div>

          <div class="table-container">
            <table class="inscripciones-table">
              <thead>
                <tr>
                  <th>No. Control</th>
                  <th>Nombre</th>
                  <th>Carrera</th>
                  <th>Semestre</th>
                  <th>Fecha Inscripción</th>
                  <th>Estatus</th>
                  <th class="text-right">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in resumen.recientes" :key="item.id_inscripcion">
                  <td>{{ item.noControl || '—' }}</td>
                  <td>{{ item.nombre || 'Sin nombre' }}</td>
                  <td>{{ item.carrera || '—' }}</td>
                  <td class="text-center">{{ item.semestre || '—' }}</td>
                  <td>{{ item.fecha || '—' }}</td>
                  <td>
                    <span :class="['estatus-badge', (item.estatus || 'pendiente').toLowerCase()]">
                      {{ item.estatus || 'Pendiente' }}
                    </span>
                  </td>
                  <td class="text-right">⋯</td>
                </tr>
                <tr v-if="!resumen.recientes || resumen.recientes.length === 0">
                  <td colspan="7" class="text-center">No hay inscripciones registradas.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import MainLayout from '@/layouts/MainLayout.vue';
import { useRouter } from 'vue-router';
const router = useRouter();

const irAAlumnos = () => router.push({ name: 'Alumnos' });
const irAInscripciones = () => router.push('/inscripcion');
const irAGrupos = () => router.push('/gestion-grupos');
const irAEvaluaciones = () => router.push('/evaluaciones');  

// 1. Agrupamos todo en un objeto 'resumen' para que el HTML lo encuentre
const resumen = ref({
  total_alumnos: 0,
  total_inscripciones: 0,
  recientes: []
});

const notification = ref({ message: '', type: '' });

const cargarDatos = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/resumen-escolar');
    if (!response.ok) throw new Error('Error en la API');
    
    const data = await response.json();
    // 2. Guardamos la respuesta tal cual viene de Laravel
    resumen.value = data;
  } catch (error) {
    console.error("Error al conectar con SICE:", error);
    notification.value = { 
      message: 'Error: No se pudo conectar con el servidor backend (Laravel)', 
      type: 'error' 
    };
  }
};

onMounted(() => {
  cargarDatos();
});
const totalAlumnos = ref(0)
const totalInscripciones = ref(0)
const totalEvaluaciones = ref(0)

const cargarResumen = async () => {
  try {
    const response = await fetch('http://localhost:8000/api/resumen-escolar')
    if (!response.ok) throw new Error('Error')
    const data = await response.json()
    totalAlumnos.value = data.total_alumnos
    totalInscripciones.value = data.total_inscripciones
  } catch (error) {
    console.error('Error cargando resumen:', error)
  }
}

onMounted(() => {
  cargarResumen()
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.servicios-escolares-page {
  width: 100%;
  padding: 2rem 2.5rem;
}

.breadcrumb {
  color: #5A5A5A;
  font-size: 0.95rem;
  margin-bottom: 1rem;
}

.page-title {
  text-align: left;
  font-size: 2.4rem;
  font-weight: 700;
  color: #1A1A1A;
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card {
  background: #F5F7FA;
  border-radius: 14px;
  padding: 1.8rem;
  display: flex;
  align-items: center;
  gap: 1.2rem;
}

.stat-card.blue {
  background: #005187;
  color: white;
}

.icon {
  width: 48px;
  height: 48px;
}

.info h3 { font-size: 1.1rem; margin: 0 0 0.3rem 0; font-weight: 600; }
.number { font-size: 2.4rem; font-weight: 700; margin: 0; }
.ver-link { color: #005187; font-weight: 600; text-decoration: none; }
.stat-card.blue .ver-link { color: white; }

.mensaje-importante {
  background: #FFF9E6;
  border-left: 6px solid #D2B48C;
  border-radius: 12px;
  padding: 1.4rem 1.8rem;
  display: flex;
  align-items: center;
  gap: 1.2rem;
  margin-bottom: 3rem;
}

.mensaje-icon {
  width: 36px;
  height: 36px;
}

.mensaje-texto {
  flex: 1;
  color: #1A1A1A;
  line-height: 1.5;
}

.btn-nueva-inscripcion {
  background: #005187;
  color: white;
  padding: 12px 28px;
  border-radius: 10px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1rem;
}

.filtros {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.search-input, .filter-select {
  padding: 12px 16px;
  border: 1px solid #D1D9E6;
  border-radius: 10px;
}

.btn-gestionar {
  background: #005187;
  color: white;
  padding: 12px 26px;
  border-radius: 10px;
  font-weight: 600;
}

.inscripciones-table {
  width: 100%;
  border-collapse: collapse;
}

.inscripciones-table th {
  background: #F5F7FA;
  padding: 18px 16px;
  font-weight: 600;
  color: #1A1A1A;
}

.inscripciones-table td {
  padding: 18px 16px;
  border-bottom: 1px solid #E0E7FF;
}

.estatus-badge {
  background: #2E7D32;
  color: white;
  padding: 4px 14px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 2rem;
  color: #5A5A5A;
  font-size: 0.95rem;
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
