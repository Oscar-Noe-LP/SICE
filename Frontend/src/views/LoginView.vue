<template>
  <div class="login-page">
    <header class="header">
      <div class="header-content">
        <img 
          src="/logo-tecnm.png"
          alt="TECNOLÓGICO NACIONAL DE MÉXICO"
          class="logo"
        >
        <span class="header-title">TECNOLÓGICO NACIONAL DE MÉXICO®</span>
      </div>
    </header>

    <main class="main-content">
      <div class="login-card">
        <h1 class="title">Iniciar sesión</h1>

        <form @submit.prevent="handleLogin" class="form">
          <div class="input-group">
            <span class="icon">👤</span>
            <input
              v-model="form.nombre_usuario"
              type="text"
              placeholder="Nombre de usuario"
              required
              :disabled="isLoading"
            >
          </div>

          <div class="input-group">
            <span class="icon">🔒</span>
            <input
              v-model="form.contrasena"
              type="password"
              placeholder="Contraseña"
              required
              :disabled="isLoading"
            >
          </div>

          <div v-if="error" style="color:#DC2626;font-size:0.9rem;margin-bottom:1rem;text-align:left;">
            ⚠ {{ error }}
          </div>

          <a href="#" class="forgot">¿Olvidaste tu contraseña?</a>

          <button type="submit" class="btn-login" :disabled="isLoading">
            {{ isLoading ? 'Verificando...' : 'Iniciar sesión' }}
          </button>

          <div class="secure">
            <span class="secure-icon">🔐</span>
            Acceso seguro
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const form = reactive({ nombre_usuario: '', contrasena: '' })
const error = ref('')
const isLoading = ref(false)

const handleLogin = async () => {
  error.value = ''
  isLoading.value = true

  try {
    const response = await fetch(`${import.meta.env.VITE_API_URL}/api/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        nombre_usuario: form.nombre_usuario,
        contrasena:     form.contrasena,
      }),
    })

    const data = await response.json()

    if (response.ok && data.success) {
      localStorage.setItem('auth_token', data.token)
      localStorage.setItem('usuario', JSON.stringify(data.usuario))
      router.push('/inicio')
    } else {
      error.value = data.message || 'Credenciales incorrectas'
    }
  } catch (e) {
    error.value = 'Error de conexión. Verifica que el servidor esté activo.'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap');

.login-page {
  font-family: 'Montserrat', sans-serif;
  min-height: 100vh;
  background: #F5F7FA;
}

.header {
  background: #1B396A;
  padding: 1.2rem 3rem;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
}

.header-content {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.logo {
  height: 68px;
  width: auto;
  filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.85));
}

.header-title {
  color: white;
  font-size: 1.42rem;
  font-weight: 700;
}


.main-content {
  padding-top: 115px;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.login-card {
  width: 100%;
  max-width: 440px;
  background: #FFFFFF;
  border-radius: 18px;
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.09);
  padding: 3.5rem 2.8rem;
  text-align: center;
  box-sizing: border-box;
}

.title {
  color: #005187;
  font-size: 2.05rem;
  font-weight: 700;
  margin-bottom: 2.4rem;
}

.input-group {
  position: relative;
  margin-bottom: 1.4rem;
}

.input-group input {
  width: 100%;
  height: 56px;
  padding: 0 1.3rem 0 3.7rem;
  border: 1.5px solid #D1D9E6;
  border-radius: 12px;
  font-size: 1.06rem;
  background: #FAFBFC;
  box-sizing: border-box;
}

.input-group input:focus {
  outline: none;
  border-color: #005187;
  box-shadow: 0 0 0 4px rgba(0, 81, 135, 0.15);
}

.icon {
  position: absolute;
  left: 1.3rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1.55rem;
  color: #6B7280;
}

.forgot {
  display: block;
  text-align: right;
  color: #4D82BE;
  font-size: 0.96rem;
  margin-bottom: 1.6rem;
  text-decoration: none;
}

.forgot:hover { text-decoration: underline; }

.btn-login {
  width: 100%;
  height: 56px;
  background: #005187;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.18rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-login:hover { background: #004070; }

.secure {
  margin-top: 2.4rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: #2E7D32;
  font-size: 0.98rem;
  font-weight: 500;
}
</style>