<template>
  <div class="callback-page">
    <div class="callback-card">
      <div class="sice-badge">SICE</div>
      <div v-if="error" class="alerta-error">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ mensajeError }}
        <a href="/login" class="link-volver">Volver al login</a>
      </div>
      <div v-else class="cargando">
        <span class="spinner"></span>
        <p>VERIFICANDO CUENTA...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const error = ref(false)
const mensajeError = ref('')

const MENSAJES_ERROR = {
  dominio_invalido:      'Solo se permiten cuentas institucionales (@matehuala.tecnm.mx).',
  usuario_no_registrado: 'Tu cuenta de Microsoft no está registrada en el sistema.',
  sin_acceso:            'Tu cuenta no tiene acceso al sistema. Contacta al administrador.',
  cuenta_desactivada:    'Tu cuenta está desactivada. Contacta al administrador.',
  token_fallido:         'Error al comunicarse con Microsoft. Intenta de nuevo.',
  graph_fallido:         'No se pudo obtener la información de tu cuenta.',
  error_interno:         'Error interno del servidor. Intenta de nuevo.',
  codigo_invalido:       'Código de autorización inválido. Intenta de nuevo.',
}

onMounted(() => {
  const params = new URLSearchParams(window.location.search)

  // Verificar si hay error
  const errorCode = params.get('error')
  if (errorCode) {
    error.value = true
    mensajeError.value = MENSAJES_ERROR[errorCode] ?? 'Error de autenticación. Intenta de nuevo.'
    return
  }

  // Leer datos del usuario
  const token = params.get('token')
  if (!token) {
    error.value = true
    mensajeError.value = 'No se recibió token de autenticación.'
    return
  }

  // Guardar en localStorage — mismo formato que el login normal
  localStorage.setItem('auth_token', token)
  localStorage.setItem('usuario', JSON.stringify({
    id_usuario:     params.get('id_usuario'),
    nombre_usuario: params.get('nombre_usuario'),
    correo:         params.get('correo'),
    nombre:         params.get('nombre'),
    id_rol:         params.get('id_rol'),
    nombre_rol:     params.get('nombre_rol'),
    rol:            params.get('rol'),
  }))

  // Redirigir al inicio
  router.push('/inicio')
})
</script>

<style scoped>
.callback-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #F4F6F9;
  font-family: 'Montserrat', sans-serif;
}

.callback-card {
  background: white;
  border-radius: 20px;
  padding: 3rem 2.5rem;
  box-shadow: 0 16px 48px rgba(11, 37, 69, 0.12);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
  min-width: 300px;
}

.sice-badge {
  background: #0B2545;
  color: white;
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  padding: 4px 18px;
  border-radius: 10px;
}

.cargando {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  color: #828282;
  font-size: 0.85rem;
  font-weight: 600;
  letter-spacing: 0.06em;
}

.spinner {
  width: 36px;
  height: 36px;
  border: 3px solid rgba(11, 37, 69, 0.15);
  border-top-color: #0B2545;
  border-radius: 50%;
  animation: girar 0.8s linear infinite;
}

@keyframes girar { to { transform: rotate(360deg); } }

.alerta-error {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  color: #EB5757;
  font-size: 0.88rem;
  font-weight: 500;
  text-align: center;
}

.alerta-error svg { stroke: #EB5757; }

.link-volver {
  color: #1D52B7;
  font-weight: 600;
  font-size: 0.82rem;
  text-decoration: none;
  margin-top: 0.5rem;
}
.link-volver:hover { text-decoration: underline; }
</style>