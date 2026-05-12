<template>
  <transition name="splash-fade">
    <div v-if="visible" class="splash-overlay">

      <div class="splash-contenido" :class="{ 'splash-salida': saliendo }">

        <!-- Logo TecNM -->
        <img
          src="/logo-tecnm.png"
          alt="TecNM"
          class="splash-logo"
        >

        <!-- Badge SICE -->
        <div class="splash-sice">SICE</div>

        <!-- Texto de bienvenida -->
        <p class="splash-bienvenida">
          Bienvenido(a)
          <strong>{{ nombreUsuario }}</strong>
        </p>

        <!-- Subtítulo -->
        <p class="splash-subtitulo">Sistema Integral de Control Escolar</p>

        <!-- Barra de progreso decorativa -->
        <div class="splash-barra-wrap">
          <div class="splash-barra-relleno"></div>
        </div>

      </div>

    </div>
  </transition>
</template>

<script setup>
defineProps({
  visible:       { type: Boolean, default: false },
  saliendo:      { type: Boolean, default: false },
  nombreUsuario: { type: String,  default: ''    }
})
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap');

.splash-overlay {
  position: fixed;
  inset: 0;
  background: #1B396A;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  font-family: 'Montserrat', sans-serif;
}

.splash-contenido {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  opacity: 0;
  transform: translateY(20px);
  animation: splash-entrar 0.5s ease forwards 0.1s;
}

.splash-contenido.splash-salida {
  animation: splash-salir 0.4s ease forwards;
}

@keyframes splash-entrar {
  to { opacity: 1; transform: translateY(0); }
}

@keyframes splash-salir {
  to { opacity: 0; transform: translateY(-16px); }
}

.splash-logo {
  height: 72px;
  width: auto;
  filter: drop-shadow(0 0 16px rgba(255,255,255,0.5));
  margin-bottom: 0.5rem;
}

.splash-sice {
  background: white;
  color: #1B396A;
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: 0.18em;
  padding: 10px 32px;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
}

.splash-bienvenida {
  color: white;
  font-size: 1.15rem;
  font-weight: 500;
  margin: 0.5rem 0 0;
  text-align: center;
  opacity: 0.95;
}

.splash-bienvenida strong {
  font-weight: 700;
  display: block;
  font-size: 1.3rem;
  margin-top: 4px;
}

.splash-subtitulo {
  color: rgba(255,255,255,0.65);
  font-size: 0.85rem;
  font-weight: 500;
  margin: 0;
  letter-spacing: 0.04em;
  text-align: center;
}

.splash-barra-wrap {
  width: 200px;
  height: 3px;
  background: rgba(255,255,255,0.2);
  border-radius: 9999px;
  overflow: hidden;
  margin-top: 1.5rem;
}

.splash-barra-relleno {
  height: 100%;
  width: 0%;
  background: white;
  border-radius: 9999px;
  animation: splash-barra 1.8s ease forwards 0.3s;
}

@keyframes splash-barra {
  0%   { width: 0%; }
  60%  { width: 80%; }
  100% { width: 100%; }
}

/* Transición del overlay completo */
.splash-fade-enter-active { transition: opacity 0.3s ease; }
.splash-fade-leave-active  { transition: opacity 0.4s ease; }
.splash-fade-enter-from,
.splash-fade-leave-to      { opacity: 0; }
</style>