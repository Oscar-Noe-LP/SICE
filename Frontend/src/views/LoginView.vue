<template>
  <div class="login-page">

    <!-- ══ ENCABEZADO ══ -->
    <header class="header">
      <div class="header-content">
        <img
          src="/logo-tecnm.png"
          alt="Tecnológico Nacional de México"
          class="logo"
        >
        <span class="header-title">TECNOLÓGICO NACIONAL DE MÉXICO</span>
      </div>
    </header>

    <!-- ══ CONTENIDO PRINCIPAL ══ -->
    <main class="main-content">
      <div class="login-card">

        <!-- Logo SICE dentro del card -->
        <div class="card-marca">
          <div class="sice-badge">SICE</div>
          <p class="card-subtitulo">Sistema Integral de Control Escolar</p>
        </div>

        <h1 class="title">Iniciar sesión</h1>

        <!-- ── Notificación de error ── -->
        <transition name="error-slide">
          <div v-if="error" class="alerta-error">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ error }}
          </div>
        </transition>

        <form @submit.prevent="handleLogin" class="form" novalidate>

          <!-- ── Usuario ── -->
          <div class="campo-grupo" :class="{ 'campo-error': errores.nombre_usuario }">
            <label class="campo-label">Nombre de usuario</label>
            <div class="input-wrap">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <input
                v-model.trim="form.nombre_usuario"
                type="text"
                placeholder="Ej. jgarcia26"
                autocomplete="username"
                :disabled="isLoading"
                @input="limpiarError('nombre_usuario')"
                @blur="validarCampo('nombre_usuario')"
              >
            </div>
            <transition name="error-fade">
              <small v-if="errores.nombre_usuario" class="campo-mensaje-error">
                {{ errores.nombre_usuario }}
              </small>
            </transition>
          </div>

          <!-- ── Contraseña ── -->
          <div class="campo-grupo" :class="{ 'campo-error': errores.contrasena }">
            <label class="campo-label">Contraseña</label>
            <div class="input-wrap">
              <svg xmlns="http://www.w3.org/2000/svg" class="input-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              <input
                v-model="form.contrasena"
                :type="verContrasena ? 'text' : 'password'"
                placeholder="Tu contraseña"
                autocomplete="current-password"
                :disabled="isLoading"
                @input="limpiarError('contrasena')"
                @blur="validarCampo('contrasena')"
              >
              <!-- Botón mostrar/ocultar contraseña -->
              <button
                type="button"
                class="btn-ojo"
                @click="verContrasena = !verContrasena"
                :title="verContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña'"
              >
                <svg v-if="!verContrasena" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
              </button>
            </div>
            <transition name="error-fade">
              <small v-if="errores.contrasena" class="campo-mensaje-error">
                {{ errores.contrasena }}
              </small>
            </transition>
          </div>

          <!-- ── Olvidé mi contraseña ── -->
          <a href="#" class="link-olvide" @click.prevent>¿Olvidaste tu contraseña?</a>

          <!-- ── Botón submit ── -->
          <button type="submit" class="btn-login" :disabled="isLoading">
            <span v-if="isLoading" class="spinner-login"></span>
            <span>{{ isLoading ? 'Verificando...' : 'Iniciar sesión' }}</span>
          </button>

          <!-- ── Indicador seguro ── -->
          <div class="indicador-seguro">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            Conexión segura
          </div>

        </form>
      </div>
    </main>

    <!-- ══ PIE ══ -->
    <footer class="pie-login">
      © {{ anioActual }} Tecnológico Nacional de México · Todos los derechos reservados
    </footer>

    <!-- ── Transición de bienvenida ── -->
    <TransicionLogin
      :visible="mostrarSplash"
      :saliendo="splashSaliendo"
      :nombre-usuario="nombreParaSplash"
    />

  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import TransicionLogin from '@/components/TransicionLogin.vue'

const router    = useRouter()
const isLoading = ref(false)
const error     = ref('')
const verContrasena = ref(false)

const mostrarSplash = ref(false)
const splashSaliendo = ref(false)
const nombreParaSplash = ref('')

const anioActual = computed(() => new Date().getFullYear())

const form = reactive({
  nombre_usuario: '',
  contrasena:     ''
})

const errores = reactive({
  nombre_usuario: '',
  contrasena:     ''
})

// ── Validaciones inline ──────────────────────────────────────────
const validarCampo = (campo) => {
  if (campo === 'nombre_usuario') {
    errores.nombre_usuario = !form.nombre_usuario.trim()
      ? 'El nombre de usuario es obligatorio'
      : ''
  }
  if (campo === 'contrasena') {
    errores.contrasena = !form.contrasena
      ? 'La contraseña es obligatoria'
      : form.contrasena.length < 4
        ? 'La contraseña es muy corta'
        : ''
  }
}

const limpiarError = (campo) => {
  errores[campo] = ''
  error.value    = ''
}

const validarFormulario = () => {
  validarCampo('nombre_usuario')
  validarCampo('contrasena')
  return !errores.nombre_usuario && !errores.contrasena
}

// ── Login — Endpoint: POST /api/login ────────────────────────────
const handleLogin = async () => {
  error.value = ''
  if (!validarFormulario()) return

  isLoading.value = true
  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/login`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        nombre_usuario: form.nombre_usuario,
        contrasena:     form.contrasena
      })
    })

    const data = await response.json()

    if (response.ok && data.success) {
      // Guardar token y datos del usuario en localStorage
      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('usuario', JSON.stringify(data.usuario))

      // Mostrar splash con el nombre del usuario
      nombreParaSplash.value = data.usuario?.nombre || data.usuario?.nombre_usuario || 'Usuario'
      mostrarSplash.value = true

      // Después de que la barra termina, iniciar salida y navegar
      setTimeout(() => {
        splashSaliendo.value = true
        setTimeout(() => {
          router.push('/inicio')
        }, 400)
      }, 2200)
    } else {
      error.value = data.message || 'Credenciales incorrectas. Intenta de nuevo.'
    }
  } catch (e) {
    error.value = 'Error de conexión. Verifica que el servidor esté activo.'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

/* ══ Variables ══ */
.login-page {
  --azul:       #1B396A;
  --azul-hover: #1D4ED8;
  --azul-suave: #DBEAFE;
  --borde:      #D1D9E6;
  --fondo:      #F0F4F8;
  --texto:      #1A1A1A;
  --gris:       #6B7280;
  --rojo:       #DC2626;

  font-family: 'Montserrat', sans-serif;
  min-height: 100vh;
  background: var(--fondo);
  display: flex;
  flex-direction: column;
}

/* ══ Encabezado ══ */
.header {
  background: var(--azul);
  padding: 0 2rem;
  height: 68px;
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 100;
  box-shadow: 0 4px 20px rgba(0,0,0,0.18);
  display: flex;
  align-items: center;
}

.header-content {
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.logo {
  height: 48px;
  width: auto;
  filter: drop-shadow(0 0 8px rgba(255,255,255,0.85));
  flex-shrink: 0;
}

.header-title {
  color: white;
  font-size: 1.1rem;
  font-weight: 700;
  letter-spacing: 0.01em;
  white-space: nowrap;
}

/* ══ Contenido principal ══ */
.main-content {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 100px 1rem 2rem;
  min-height: 100vh;
  box-sizing: border-box;
}

/* ══ Card de login ══ */
.login-card {
  width: 100%;
  max-width: 440px;
  background: #FFFFFF;
  border-radius: 20px;
  box-shadow: 0 16px 48px rgba(0,0,0,0.1);
  padding: 2.8rem 2.6rem;
  box-sizing: border-box;
}

/* ── Marca SICE ── */
.card-marca {
  text-align: center;
  margin-bottom: 1.6rem;
}

.sice-badge {
  display: inline-block;
  background: var(--azul);
  color: white;
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  padding: 8px 24px;
  border-radius: 10px;
  margin-bottom: 8px;
}

.card-subtitulo {
  color: var(--gris);
  font-size: 0.82rem;
  font-weight: 500;
  margin: 0;
}

/* ── Título ── */
.title {
  color: var(--azul);
  font-size: 1.7rem;
  font-weight: 700;
  text-align: center;
  margin: 0 0 1.6rem;
}

/* ── Alerta de error global ── */
.alerta-error {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #FEE2E2;
  color: var(--rojo);
  border: 1px solid #FECACA;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 0.88rem;
  font-weight: 500;
  margin-bottom: 1.2rem;
}
.alerta-error svg { stroke: var(--rojo); flex-shrink: 0; }

/* ── Campos de formulario ── */
.campo-grupo { margin-bottom: 1.2rem; }

.campo-label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--texto);
  margin-bottom: 6px;
}

.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.input-wrap input {
  width: 100%;
  height: 50px;
  padding: 0 3rem 0 3.2rem;
  border: 1.5px solid var(--borde);
  border-radius: 10px;
  font-size: 0.95rem;
  font-family: 'Montserrat', sans-serif;
  background: #FAFBFC;
  color: var(--texto);
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}

.input-wrap input:focus {
  border-color: var(--azul);
  box-shadow: 0 0 0 3px var(--azul-suave);
  background: #FFFFFF;
}

.input-wrap input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.campo-grupo.campo-error .input-wrap input {
  border-color: var(--rojo);
}
.campo-grupo.campo-error .input-wrap input:focus {
  box-shadow: 0 0 0 3px #FEE2E2;
}

.input-icono {
  position: absolute;
  left: 1rem;
  width: 18px; height: 18px;
  stroke: var(--gris);
  pointer-events: none;
  flex-shrink: 0;
}

/* Botón de mostrar/ocultar contraseña */
.btn-ojo {
  position: absolute;
  right: 0.9rem;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  min-height: unset;
}
.btn-ojo svg { stroke: var(--gris); transition: stroke 0.15s; }
.btn-ojo:hover svg { stroke: var(--azul); }

/* Mensaje de error inline */
.campo-mensaje-error {
  display: block;
  color: var(--rojo);
  font-size: 0.78rem;
  font-weight: 500;
  margin-top: 5px;
}
.campo-mensaje-error::before { content: '⚠ '; }

/* ── Olvidé contraseña ── */
.link-olvide {
  display: block;
  text-align: right;
  color: #4D82BE;
  font-size: 0.88rem;
  font-weight: 500;
  margin-bottom: 1.4rem;
  text-decoration: none;
  transition: color 0.15s;
}
.link-olvide:hover { color: var(--azul); text-decoration: underline; }

/* ── Botón de login ── */
.btn-login {
  width: 100%;
  height: 52px;
  background: var(--azul);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.2s, transform 0.1s;
}
.btn-login:hover:not(:disabled) {
  background: var(--azul-hover);
  transform: translateY(-1px);
}
.btn-login:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.spinner-login {
  width: 18px; height: 18px;
  border: 2px solid rgba(255,255,255,0.35);
  border-top-color: white;
  border-radius: 50%;
  animation: girar 0.7s linear infinite;
  flex-shrink: 0;
}
@keyframes girar { to { transform: rotate(360deg); } }

/* ── Indicador seguro ── */
.indicador-seguro {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  color: #16A34A;
  font-size: 0.82rem;
  font-weight: 600;
  margin-top: 1.4rem;
}
.indicador-seguro svg { stroke: #16A34A; }

/* ══ Pie ══ */
.pie-login {
  text-align: center;
  color: var(--gris);
  font-size: 0.78rem;
  padding: 1rem;
}

/* ══ Animaciones ══ */
.error-slide-enter-active { transition: all 0.3s ease; }
.error-slide-leave-active { transition: all 0.2s ease; }
.error-slide-enter-from   { opacity: 0; transform: translateY(-8px); }
.error-slide-leave-to     { opacity: 0; transform: translateY(-4px); }

.error-fade-enter-active, .error-fade-leave-active { transition: all 0.25s ease; }
.error-fade-enter-from, .error-fade-leave-to       { opacity: 0; transform: translateY(-4px); }

/* ══ RESPONSIVE ══ */
@media (max-width: 768px) {
  .header-title {
    font-size: 0.88rem;
    letter-spacing: 0;
  }

  .logo { height: 38px; }

  .header { height: 58px; padding: 0 1rem; }

  .main-content { padding-top: 80px; }

  .login-card {
    padding: 2.2rem 1.8rem;
    border-radius: 16px;
    max-width: 400px;
  }

  .title { font-size: 1.5rem; }
}

@media (max-width: 480px) {
  .header-title { display: none; }

  .main-content { padding: 72px 0.75rem 1rem; }

  .login-card {
    padding: 2rem 1.4rem;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  }

  .sice-badge { font-size: 1.3rem; padding: 6px 18px; }

  .title { font-size: 1.35rem; margin-bottom: 1.2rem; }

  .input-wrap input { height: 46px; font-size: 16px; }

  .btn-login { height: 48px; font-size: 0.95rem; }
}
</style>