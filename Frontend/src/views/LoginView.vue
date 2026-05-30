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

        <!-- ══ VISTA: SELECTOR DE CUENTA (estilo ChatGPT) ══ -->
        <template v-if="vistaActual === 'cuentas'">
          <h1 class="title">BIENVENIDO DE VUELTA</h1>
          <p class="subtitle-cuentas">ELIGE UNA CUENTA PARA CONTINUAR</p>

          <div class="lista-cuentas">
            <button
              v-for="cuenta in cuentasRecientes"
              :key="cuenta.correo"
              class="cuenta-item"
              @click="seleccionarCuenta(cuenta)"
            >
              <div class="cuenta-avatar">{{ cuenta.iniciales }}</div>
              <div class="cuenta-info">
                <span class="cuenta-nombre">{{ cuenta.nombre }}</span>
                <span class="cuenta-correo">{{ cuenta.correo }}</span>
              </div>
              <svg xmlns="http://www.w3.org/2000/svg" class="cuenta-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              <div class="cuenta-remove" @click.stop="eliminarCuenta(cuenta.correo)" title="Eliminar cuenta guardada" role="button" tabindex="0" @keydown.enter.stop="eliminarCuenta(cuenta.correo)">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </div>
            </button>
          </div>

          <div class="divisor-cuentas">
            <span>O</span>
          </div>

          <button class="btn-otra-cuenta" @click="vistaActual = 'login'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            INICIAR SESIÓN CON OTRA CUENTA
          </button>
        </template>

        <!-- ══ VISTA: CONTRASEÑA (cuando se selecciona cuenta guardada) ══ -->
        <template v-else-if="vistaActual === 'contrasena-cuenta'">
          <button class="btn-volver" @click="vistaActual = 'cuentas'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            VOLVER
          </button>

          <h1 class="title">CONTINUAR COMO</h1>

          <div class="cuenta-seleccionada-info">
            <div class="cuenta-avatar grande">{{ cuentaSeleccionada?.iniciales }}</div>
            <span class="cuenta-nombre-grande">{{ cuentaSeleccionada?.nombre }}</span>
            <span class="cuenta-correo-grande">{{ cuentaSeleccionada?.correo }}</span>
          </div>

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

          <form @submit.prevent="handleLoginCuentaGuardada" class="form" novalidate>
            <div class="campo-grupo" :class="{ 'campo-error': errores.contrasena }">
              <label class="campo-label">CONTRASEÑA</label>
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
                  ref="inputContrasenaRapida"
                >
                <button type="button" class="btn-ojo" @click="verContrasena = !verContrasena" :title="verContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                  <svg v-if="!verContrasena" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
              <transition name="error-fade">
                <small v-if="errores.contrasena" class="campo-mensaje-error">{{ errores.contrasena }}</small>
              </transition>
            </div>

            <a href="#" class="link-olvide" @click.prevent>¿OLVIDASTE TU CONTRASEÑA?</a>

            <button type="submit" class="btn-login" :disabled="isLoading">
              <span v-if="isLoading" class="spinner-login"></span>
              <span>{{ isLoading ? 'VERIFICANDO...' : 'CONTINUAR' }}</span>
            </button>

            <div class="indicador-seguro">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              CONEXIÓN SEGURA
            </div>
          </form>
        </template>

        <!-- ══ VISTA: LOGIN COMPLETO (correo + contraseña) ══ -->
        <template v-else>
          <template v-if="cuentasRecientes.length > 0">
            <button class="btn-volver" @click="vistaActual = 'cuentas'">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              VOLVER
            </button>
          </template>

          <h1 class="title">INICIAR SESIÓN</h1>

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

            <!-- ── Correo institucional ── -->
            <div class="campo-grupo" :class="{ 'campo-error': errores.correo }">
              <label class="campo-label">CORREO INSTITUCIONAL</label>
              <div class="input-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" class="input-icono" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <input
                  v-model.trim="form.correo"
                  type="email"
                  placeholder="usuario@matehuala.tecnm.mx"
                  autocomplete="email"
                  :disabled="isLoading"
                  @input="limpiarError('correo')"
                  @blur="validarCampo('correo')"
                >
              </div>
              <transition name="error-fade">
                <small v-if="errores.correo" class="campo-mensaje-error">{{ errores.correo }}</small>
              </transition>

            </div>

            <!-- ── Contraseña ── -->
            <div class="campo-grupo" :class="{ 'campo-error': errores.contrasena }">
              <label class="campo-label">CONTRASEÑA</label>
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
                <button type="button" class="btn-ojo" @click="verContrasena = !verContrasena" :title="verContrasena ? 'Ocultar contraseña' : 'Mostrar contraseña'">
                  <svg v-if="!verContrasena" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                </button>
              </div>
              <transition name="error-fade">
                <small v-if="errores.contrasena" class="campo-mensaje-error">{{ errores.contrasena }}</small>
              </transition>
            </div>

            <!-- ── Recordar usuario ── -->
            <div class="campo-recordar">
              <label class="check-label">
                <input type="checkbox" v-model="recordarUsuario" class="check-input">
                <span class="check-custom"></span>
                RECORDAR ESTA CUENTA
              </label>
            </div>

            <a href="#" class="link-olvide" @click.prevent>¿OLVIDASTE TU CONTRASEÑA?</a>

            <button type="submit" class="btn-login" :disabled="isLoading">
              <span v-if="isLoading" class="spinner-login"></span>
              <span>{{ isLoading ? 'VERIFICANDO...' : 'INICIAR SESIÓN' }}</span>
            </button>

            <div class="indicador-seguro">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              CONEXIÓN SEGURA
            </div>
          </form>
        </template>

      </div>
    </main>

    <!-- ══ PIE ══ -->
    <footer class="pie-login">
      © {{ anioActual }} TECNOLÓGICO NACIONAL DE MÉXICO · TODOS LOS DERECHOS RESERVADOS
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
import { ref, reactive, computed, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import TransicionLogin from '@/components/TransicionLogin.vue'

const router    = useRouter()
const isLoading = ref(false)
const error     = ref('')
const verContrasena = ref(false)
const recordarUsuario = ref(false)

const mostrarSplash = ref(false)
const splashSaliendo = ref(false)
const nombreParaSplash = ref('')

// ── Vista actual: 'cuentas' | 'login' | 'contrasena-cuenta'
const vistaActual = ref('login')
const cuentaSeleccionada = ref(null)
const inputContrasenaRapida = ref(null)

const anioActual = computed(() => new Date().getFullYear())

// ── Cuentas recientes guardadas en localStorage
const STORAGE_KEY = 'sice_cuentas_recientes'

const cargarCuentas = () => {
  try {
    return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
  } catch { return [] }
}

const cuentasRecientes = ref(cargarCuentas())

// Si hay cuentas guardadas, mostrar el selector al cargar
if (cuentasRecientes.value.length > 0) {
  vistaActual.value = 'cuentas'
}

const guardarCuenta = (correo, nombre) => {
  const cuentas = cargarCuentas().filter(c => c.correo !== correo)
  const partes = nombre.split(' ')
  const iniciales = partes.length >= 2
    ? (partes[0][0] + partes[1][0]).toUpperCase()
    : nombre.substring(0, 2).toUpperCase()

  cuentas.unshift({ correo, nombre, iniciales })
  const cuentasLimitadas = cuentas.slice(0, 3) // máximo 3 cuentas
  localStorage.setItem(STORAGE_KEY, JSON.stringify(cuentasLimitadas))
  cuentasRecientes.value = cuentasLimitadas
}

const eliminarCuenta = (correo) => {
  const cuentas = cargarCuentas().filter(c => c.correo !== correo)
  localStorage.setItem(STORAGE_KEY, JSON.stringify(cuentas))
  cuentasRecientes.value = cuentas
  if (cuentas.length === 0) vistaActual.value = 'login'
}

const seleccionarCuenta = async (cuenta) => {
  cuentaSeleccionada.value = cuenta
  form.correo = cuenta.correo
  form.contrasena = ''
  errores.contrasena = ''
  error.value = ''
  vistaActual.value = 'contrasena-cuenta'
  await nextTick()
  inputContrasenaRapida.value?.focus()
}

// ── Dominio permitido
const DOMINIO = '@matehuala.tecnm.mx'

const form = reactive({
  correo: '',
  contrasena: ''
})

// NOTA: nombre_usuario se mantiene para compatibilidad con el backend
// Se deriva del correo antes de enviar

const errores = reactive({
  correo: '',
  contrasena: ''
})

// ── Dominio institucional permitido
const DOMINIO_REGEX = /^[a-zA-Z0-9._%+-]+@matehuala\.tecnm\.mx$/i

// ── Validaciones inline ──────────────────────────────────────────
const validarCampo = (campo) => {
  if (campo === 'correo') {
    if (!form.correo.trim()) {
      errores.correo = 'EL CORREO INSTITUCIONAL ES OBLIGATORIO'
    } else if (!DOMINIO_REGEX.test(form.correo.trim())) {
      errores.correo = 'USA TU CORREO @matehuala.tecnm.mx'
    } else {
      errores.correo = ''
    }
  }
  if (campo === 'contrasena') {
    errores.contrasena = !form.contrasena
      ? 'LA CONTRASEÑA ES OBLIGATORIA'
      : form.contrasena.length < 4
        ? 'LA CONTRASEÑA ES MUY CORTA'
        : ''
  }
}

const limpiarError = (campo) => {
  errores[campo] = ''
  error.value    = ''
}

const validarFormulario = () => {
  validarCampo('correo')
  validarCampo('contrasena')
  return !errores.correo && !errores.contrasena
}

const validarSoloContrasena = () => {
  validarCampo('contrasena')
  return !errores.contrasena
}

// ── Login desde cuenta guardada (solo contraseña)
const handleLoginCuentaGuardada = async () => {
  error.value = ''
  if (!validarSoloContrasena()) return

  isLoading.value = true
  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/login`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        correo:     cuentaSeleccionada.value.correo,
        contrasena: form.contrasena
      })
    })

    const data = await response.json()

    if (response.ok && data.success) {
      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('usuario', JSON.stringify(data.usuario))

      nombreParaSplash.value = data.usuario?.nombre || data.usuario?.nombre_usuario || 'USUARIO'
      mostrarSplash.value = true

      setTimeout(() => {
        splashSaliendo.value = true
        setTimeout(() => {
          router.push('/inicio')
        }, 400)
      }, 2200)
    } else {
      error.value = data.message || 'CREDENCIALES INCORRECTAS. INTENTA DE NUEVO.'
    }
  } catch (e) {
    error.value = 'ERROR DE CONEXIÓN. VERIFICA QUE EL SERVIDOR ESTÉ ACTIVO.'
  } finally {
    isLoading.value = false
  }
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
        correo:     form.correo.trim().toLowerCase(),
        contrasena: form.contrasena
      })
    })

    const data = await response.json()

    if (response.ok && data.success) {
      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('usuario', JSON.stringify(data.usuario))

      // Guardar cuenta si el usuario lo pidió
      if (recordarUsuario.value) {
        const nombreGuardar = data.usuario?.nombre || data.usuario?.nombre_usuario || extraerNombreUsuario(form.correo)
        guardarCuenta(form.correo, nombreGuardar)
      }

      nombreParaSplash.value = data.usuario?.nombre || data.usuario?.nombre_usuario || 'USUARIO'
      mostrarSplash.value = true

      setTimeout(() => {
        splashSaliendo.value = true
        setTimeout(() => {
          router.push('/inicio')
        }, 400)
      }, 2200)
    } else {
      error.value = data.message || 'CREDENCIALES INCORRECTAS. INTENTA DE NUEVO.'
    }
  } catch (e) {
    error.value = 'ERROR DE CONEXIÓN. VERIFICA QUE EL SERVIDOR ESTÉ ACTIVO.'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

/* ══ Variables — Colorimetría SICE ══ */
.login-page {
  --azul-marino:    #0B2545;
  --azul-rey:       #1D52B7;
  --azul-medio:     #1A4184;
  --azul-cyan:      #2F80ED;
  --azul-suave:     rgba(29, 82, 183, 0.15);
  --azul-focus:     rgba(29, 82, 183, 0.05);
  --verde:          #27AE60;
  --verde-suave:    rgba(39, 174, 96, 0.10);
  --rojo:           #EB5757;
  --rojo-suave:     rgba(235, 87, 87, 0.10);
  --rojo-fondo:     #FFF0F0;
  --naranja:        #F2994A;
  --blanco:         #FFFFFF;
  --fondo-app:      #F4F6F9;
  --fondo-sec:      #F2F4F7;
  --borde:          #E0E0E0;
  --texto-pri:      #333333;
  --texto-sec:      #4F4F4F;
  --texto-inac:     #828282;
  --texto-skel:     #BDBDBD;

  font-family: 'Montserrat', sans-serif;
  min-height: 100vh;
  background: var(--fondo-app);
  display: flex;
  flex-direction: column;
}

/* ══ Encabezado ══ */
.header {
  background: var(--azul-marino);
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
  background: var(--blanco);
  border-radius: 20px;
  box-shadow: 0 16px 48px rgba(11, 37, 69, 0.12);
  padding: 2.8rem 2.6rem;
  box-sizing: border-box;
  animation: card-in 0.35s ease;
}

@keyframes card-in {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── Marca SICE ── */
.card-marca {
  text-align: center;
  margin-bottom: 1.6rem;
}

.sice-badge {
  display: inline-block;
  background: var(--azul-marino);
  color: white;
  font-size: 1.5rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  padding: 8px 24px;
  border-radius: 10px;
  margin-bottom: 8px;
}

.card-subtitulo {
  color: var(--texto-inac);
  font-size: 0.82rem;
  font-weight: 500;
  margin: 0;
}

/* ── Título ── */
.title {
  color: var(--azul-marino);
  font-size: 1.5rem;
  font-weight: 700;
  text-align: center;
  margin: 0 0 0.6rem;
  letter-spacing: 0.02em;
}

.subtitle-cuentas {
  color: var(--texto-inac);
  font-size: 0.78rem;
  font-weight: 500;
  text-align: center;
  margin: 0 0 1.6rem;
  letter-spacing: 0.04em;
}

/* ══ Lista de cuentas recientes ══ */
.lista-cuentas {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.cuenta-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  background: var(--fondo-sec);
  border: 1.5px solid var(--borde);
  border-radius: 12px;
  cursor: pointer;
  text-align: left;
  font-family: 'Montserrat', sans-serif;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  position: relative;
  width: 100%;
}

.cuenta-item:hover {
  border-color: var(--azul-rey);
  background: var(--azul-focus);
  box-shadow: 0 0 0 3px var(--azul-suave);
}

.cuenta-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--azul-marino);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  font-weight: 700;
  flex-shrink: 0;
  letter-spacing: 0.05em;
}

.cuenta-avatar.grande {
  width: 56px;
  height: 56px;
  font-size: 1.2rem;
  margin: 0 auto 0.5rem;
}

.cuenta-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.cuenta-nombre {
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--texto-pri);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cuenta-correo {
  font-size: 0.78rem;
  color: var(--texto-inac);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.cuenta-chevron {
  stroke: var(--texto-inac);
  flex-shrink: 0;
  margin-right: 4px;
}

.cuenta-remove {
  position: absolute;
  right: 36px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  opacity: 0;
  transition: opacity 0.15s;
  min-height: unset;
  border-radius: 50%;
}
.cuenta-remove svg { stroke: var(--texto-inac); }
.cuenta-remove:hover svg { stroke: var(--rojo); }
.cuenta-item:hover .cuenta-remove { opacity: 1; }

/* ── Divisor cuentas ── */
.divisor-cuentas {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 1.2rem 0;
  color: var(--texto-inac);
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.08em;
}
.divisor-cuentas::before,
.divisor-cuentas::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--borde);
}

/* ── Botón otra cuenta ── */
.btn-otra-cuenta {
  width: 100%;
  height: 50px;
  background: var(--fondo-sec);
  color: var(--azul-marino);
  border: 1.5px solid var(--borde);
  border-radius: 12px;
  font-size: 0.88rem;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  letter-spacing: 0.03em;
}
.btn-otra-cuenta svg { stroke: var(--azul-marino); }
.btn-otra-cuenta:hover {
  border-color: var(--azul-rey);
  background: var(--azul-focus);
  box-shadow: 0 0 0 3px var(--azul-suave);
}

/* ── Cuenta seleccionada info ── */
.cuenta-seleccionada-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 1.6rem;
  padding: 1.2rem;
  background: var(--fondo-sec);
  border-radius: 12px;
  border: 1.5px solid var(--borde);
}

.cuenta-nombre-grande {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--texto-pri);
  margin-top: 0.3rem;
}

.cuenta-correo-grande {
  font-size: 0.82rem;
  color: var(--texto-inac);
  margin-top: 2px;
}

/* ── Botón volver ── */
.btn-volver {
  display: flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  color: var(--azul-cyan);
  font-size: 0.82rem;
  font-weight: 600;
  font-family: 'Montserrat', sans-serif;
  cursor: pointer;
  padding: 0;
  margin-bottom: 1.2rem;
  letter-spacing: 0.04em;
  transition: color 0.15s;
  min-height: unset;
}
.btn-volver svg { stroke: var(--azul-cyan); transition: stroke 0.15s; }
.btn-volver:hover { color: var(--azul-marino); }
.btn-volver:hover svg { stroke: var(--azul-marino); }

/* ── Alerta de error global ── */
.alerta-error {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--rojo-fondo);
  color: var(--rojo);
  border: 1px solid rgba(235, 87, 87, 0.3);
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
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--texto-pri);
  margin-bottom: 6px;
  letter-spacing: 0.04em;
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
  background: var(--fondo-sec);
  color: var(--texto-pri);
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  box-sizing: border-box;
}

.input-wrap input:focus {
  border-color: var(--azul-rey);
  box-shadow: 0 0 0 3px var(--azul-suave);
  background: var(--blanco);
}

.input-wrap input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.campo-grupo.campo-error .input-wrap input {
  border-color: var(--rojo);
}
.campo-grupo.campo-error .input-wrap input:focus {
  box-shadow: 0 0 0 3px var(--rojo-suave);
}

.input-icono {
  position: absolute;
  left: 1rem;
  width: 18px; height: 18px;
  stroke: var(--texto-inac);
  pointer-events: none;
  flex-shrink: 0;
}

.campo-hint {
  display: block;
  color: var(--texto-inac);
  font-size: 0.74rem;
  font-weight: 400;
  margin-top: 4px;
}

/* Botón mostrar/ocultar contraseña */
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
.btn-ojo svg { stroke: var(--texto-inac); transition: stroke 0.15s; }
.btn-ojo:hover svg { stroke: var(--azul-marino); }

/* Mensaje de error inline */
.campo-mensaje-error {
  display: block;
  color: var(--rojo);
  font-size: 0.78rem;
  font-weight: 500;
  margin-top: 5px;
}
.campo-mensaje-error::before { content: '⚠ '; }

/* ── Recordar cuenta ── */
.campo-recordar {
  margin-bottom: 1rem;
}

.check-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--texto-sec);
  letter-spacing: 0.04em;
  user-select: none;
}

.check-input {
  display: none;
}

.check-custom {
  width: 18px;
  height: 18px;
  border: 1.5px solid var(--borde);
  border-radius: 4px;
  background: var(--fondo-sec);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: border-color 0.2s, background 0.2s;
  position: relative;
}

.check-input:checked + .check-custom {
  background: var(--azul-marino);
  border-color: var(--azul-marino);
}

.check-input:checked + .check-custom::after {
  content: '';
  width: 5px;
  height: 9px;
  border: 2px solid white;
  border-top: none;
  border-left: none;
  transform: rotate(45deg) translateY(-1px);
}

/* ── Olvidé contraseña ── */
.link-olvide {
  display: block;
  text-align: right;
  color: var(--azul-cyan);
  font-size: 0.82rem;
  font-weight: 600;
  margin-bottom: 1.4rem;
  text-decoration: none;
  letter-spacing: 0.04em;
  transition: color 0.15s;
}
.link-olvide:hover { color: var(--azul-marino); text-decoration: underline; }

/* ── Botón de login ── */
.btn-login {
  width: 100%;
  height: 52px;
  background: var(--azul-marino);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 700;
  font-family: 'Montserrat', sans-serif;
  letter-spacing: 0.06em;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
}
.btn-login:hover:not(:disabled) {
  background: var(--azul-rey);
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(29, 82, 183, 0.3);
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
  color: var(--verde);
  font-size: 0.78rem;
  font-weight: 600;
  margin-top: 1.4rem;
  letter-spacing: 0.04em;
}
.indicador-seguro svg { stroke: var(--verde); }

/* ══ Pie ══ */
.pie-login {
  text-align: center;
  color: var(--texto-inac);
  font-size: 0.74rem;
  font-weight: 500;
  padding: 1rem;
  letter-spacing: 0.02em;
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
  .title { font-size: 1.3rem; }
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
  .title { font-size: 1.2rem; margin-bottom: 1rem; }
  .input-wrap input { height: 46px; font-size: 16px; }
  .btn-login { height: 48px; font-size: 0.9rem; }
}
</style>
