<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps<{
  canResetPassword?: boolean
  status?: string
  invitation?: {
    team: string
    inviter: string
  } | null
}>()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)
const loginCardRef = ref<HTMLElement | null>(null)
let particlesAnimationId: number | null = null

function handleMouseMove(e: MouseEvent) {
  const x = e.clientX / window.innerWidth
  const y = e.clientY / window.innerHeight
  document.documentElement.style.setProperty('--x', String(x))
  document.documentElement.style.setProperty('--y', String(y))
  document.documentElement.style.setProperty('--mx', e.clientX + 'px')
  document.documentElement.style.setProperty('--my', e.clientY + 'px')
}

function handleCardTilt(e: MouseEvent) {
  if (!loginCardRef.value) return
  const card = loginCardRef.value
  const rect = card.getBoundingClientRect()
  const x = ((e.clientX - rect.left) / rect.width - 0.5) * 20
  const y = ((e.clientY - rect.top) / rect.height - 0.5) * 20
  card.style.transform = `rotateY(${x}deg) rotateX(${-y}deg)`
}

function handleCardLeave() {
  if (!loginCardRef.value) return
  loginCardRef.value.style.transform = ''
}

function initParticles() {
  const canvas = document.getElementById('particles') as HTMLCanvasElement | null
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  if (!ctx) return

  canvas.width = window.innerWidth
  canvas.height = window.innerHeight

  const particles = Array.from({ length: 60 }, () => ({
    x: Math.random() * canvas.width,
    y: Math.random() * canvas.height,
    r: Math.random() * 2 + 0.5,
    dx: (Math.random() - 0.5) * 0.3,
    dy: (Math.random() - 0.5) * 0.3,
  }))

  function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height)
    particles.forEach((p) => {
      p.x += p.dx
      p.y += p.dy
      if (p.x < 0) p.x = canvas.width
      if (p.x > canvas.width) p.x = 0
      if (p.y < 0) p.y = canvas.height
      if (p.y > canvas.height) p.y = 0
      ctx.beginPath()
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2)
      ctx.fillStyle = 'rgba(168,85,247,0.4)'
      ctx.fill()
    })
    particlesAnimationId = requestAnimationFrame(animate)
  }

  animate()
}

function destroyParticles() {
  if (particlesAnimationId !== null) {
    cancelAnimationFrame(particlesAnimationId)
    particlesAnimationId = null
  }
}

function handleResize() {
  const canvas = document.getElementById('particles') as HTMLCanvasElement | null
  if (canvas) {
    canvas.width = window.innerWidth
    canvas.height = window.innerHeight
  }
}

onMounted(() => {
  document.addEventListener('mousemove', handleMouseMove)
  window.addEventListener('resize', handleResize)
  initParticles()
})

onUnmounted(() => {
  document.removeEventListener('mousemove', handleMouseMove)
  window.removeEventListener('resize', handleResize)
  destroyParticles()
})

function submit() {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Iniciar sesión" />

  <div class="relative flex min-h-screen overflow-hidden" style="background:
    radial-gradient(circle at 50% 20%, rgba(168,85,247,0.15), transparent 40%),
    radial-gradient(circle at 80% 80%, rgba(59,130,246,0.12), transparent 45%),
    radial-gradient(circle at 20% 80%, rgba(236,72,153,0.10), transparent 50%),
    var(--bg);">
    <div class="absolute inset-0" style="background:
      radial-gradient(circle at calc(var(--x, 0.5) * 100%) calc(var(--y, 0.5) * 100%), rgba(168,85,247,0.25), transparent 60%),
      radial-gradient(circle at calc((1 - var(--x, 0.5)) * 100%) calc(var(--y, 0.5) * 100%), rgba(59,130,246,0.18), transparent 55%),
      var(--bg);"></div>
    <!-- Floating blobs -->
    <div class="blob b1" style="background: #a855f7; top: 20%; left: 10%; width: 300px; height: 300px; filter: blur(80px); opacity: 0.4; position: absolute; border-radius: 50%; animation: blobFloat 10s infinite ease-in-out;"></div>
    <div class="blob b2" style="background: #3b82f6; bottom: 10%; right: 15%; width: 300px; height: 300px; filter: blur(80px); opacity: 0.4; position: absolute; border-radius: 50%; animation: blobFloat 12s infinite ease-in-out 2s;"></div>
    <div class="blob b3" style="background: #ec4899; top: 60%; right: 40%; width: 300px; height: 300px; filter: blur(80px); opacity: 0.4; position: absolute; border-radius: 50%; animation: blobFloat 14s infinite ease-in-out 4s;"></div>
    <!-- Particles canvas -->
    <canvas id="particles" class="pointer-events-none absolute inset-0 z-0"></canvas>
    <!-- Cursor light -->
    <div style="position: fixed; width: 400px; height: 400px; background: radial-gradient(circle, rgba(168,85,247,0.15), transparent 60%); left: var(--mx); top: var(--my); transform: translate(-50%, -50%); pointer-events: none; z-index: 1;"></div>
    <!-- Left column: Login form -->
    <div class="relative flex w-full items-center justify-center px-6 lg:w-1/2">
      <div class="absolute inset-0 bg-gradient-to-r from-[#05010a]/80 via-transparent to-[#05010a]/60"></div>

    <div class="w-full max-w-lg" style="perspective: 1000px;">
      <div ref="loginCardRef" @mousemove="handleCardTilt" @mouseleave="handleCardLeave" class="login-card relative z-10 w-full rounded-[2rem] border border-white/[0.03] px-10 py-12 will-change-transform transition-all duration-300 ease-out hover:-translate-y-[2px] hover:shadow-[0_40px_140px_rgba(168,85,247,0.25)]" style="background: rgba(15, 10, 35, 0.45); backdrop-filter: blur(45px) saturate(200%); box-shadow: 0 100px 250px rgba(0,0,0,0.6), inset 0 1px 0 rgba(255,255,255,0.04);">
      <div class="mb-6 text-center">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 via-fuchsia-500 to-orange-400 shadow-lg shadow-fuchsia-600/40">
          <svg class="h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold tracking-[0.35em] text-white">SAMUZABETY</h2>
        <p class="mt-2 text-sm text-fuchsia-300/80">Sublimación personalizada</p>
      </div>

      <div v-if="invitation" class="mb-6 rounded-lg border border-primary/20 bg-primary/5 p-4 text-sm text-white">
        Has sido invitado a unirte al equipo <strong>{{ invitation.team }}</strong> por <strong>{{ invitation.inviter }}</strong>.
      </div>

      <div v-if="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
        {{ status }}
      </div>

      <div class="mb-6">
        <h1 class="text-xl font-bold text-white">Ingresar al sistema</h1>
        <p class="mt-1 text-sm text-slate-400">Acceda con su cuenta autorizada</p>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <div class="space-y-1.5">
          <label class="text-sm font-medium text-slate-300">Correo electrónico</label>
          <div class="relative group">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-all duration-300 group-focus-within:scale-110 group-focus-within:text-fuchsia-400">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
              </svg>
            </div>
            <input
              v-model="form.email"
              type="email"
              placeholder="correo@ejemplo.com"
              class="flex h-14 w-full rounded-xl border border-white/20 bg-slate-950/70 pl-12 pr-4 text-sm text-white placeholder:text-slate-400 transition-all duration-300 focus-visible:outline-none focus-visible:border-fuchsia-400/70 focus-visible:ring-2 focus-visible:ring-fuchsia-500/20 focus-visible:scale-[1.01]"
              :class="{ 'border-red-400': form.errors.email }"
            />
          </div>
          <p v-if="form.errors.email" class="text-sm text-red-400">{{ form.errors.email }}</p>
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-medium text-slate-300">Contraseña</label>
          <div class="relative group">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 transition-all duration-300 group-focus-within:scale-110 group-focus-within:text-fuchsia-400">
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
              </svg>
            </div>
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="••••••••"
              class="flex h-14 w-full rounded-xl border border-white/20 bg-slate-950/70 pl-12 pr-12 text-sm text-white placeholder:text-slate-400 transition-all duration-300 focus-visible:outline-none focus-visible:border-fuchsia-400/70 focus-visible:ring-2 focus-visible:ring-fuchsia-500/20 focus-visible:scale-[1.01]"
              :class="{ 'border-red-400': form.errors.password }"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-300"
            >
              <svg v-if="!showPassword" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
              </svg>
              <svg v-else class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </button>
          </div>
          <p v-if="form.errors.password" class="text-sm text-red-400">{{ form.errors.password }}</p>
          <Link v-if="canResetPassword" :href="route('password.request')" class="mt-1 block text-right text-sm text-fuchsia-300 hover:text-fuchsia-200">
            ¿Olvidaste tu contraseña?
          </Link>
        </div>

        <div class="flex items-center gap-2">
          <input id="remember" v-model="form.remember" type="checkbox" class="rounded border-white/30 bg-slate-950/70 text-fuchsia-500 focus:ring-fuchsia-500 focus:ring-offset-0" />
          <label for="remember" class="text-sm text-slate-400">Recordarme</label>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="btn-shine inline-flex h-14 w-full items-center justify-center gap-3 overflow-hidden rounded-xl bg-gradient-to-r from-purple-500 via-fuchsia-500 to-orange-400 bg-[length:200%_auto] px-4 text-sm font-semibold text-white shadow-lg shadow-fuchsia-600/20 transition-all duration-300 hover:-translate-y-[2px] hover:scale-[1.02] hover:shadow-[0_20px_60px_rgba(168,85,247,0.2)] disabled:pointer-events-none disabled:opacity-50 animate-[gradientShift_4s_ease_infinite]"
        >
          <svg v-if="form.processing" class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
          </svg>
          <svg v-else class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
          </svg>
          <span>Ingresar</span>
        </button>
      </form>

      <p class="mt-8 text-center text-xs text-slate-500/80">
        &copy; 2026 Samuzabety &mdash; Sublimación personalizada
      </p>
      </div>
    </div>
    </div>

    <!-- Right column: Ambient backdrop -->
    <div class="relative hidden lg:block lg:w-1/2">
      <div class="absolute inset-0" style="backdrop-filter: blur(3px);"></div>
      <div class="absolute inset-0 bg-gradient-to-l from-transparent via-[#05010a]/40 to-[#05010a]/90"></div>
    </div>
  </div>
</template>

<style scoped>
@keyframes blobFloat {
  0% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-30px) scale(1.1); }
  100% { transform: translateY(0px) scale(1); }
}

@keyframes intro {
  from { opacity: 0; transform: translateY(40px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes glow {
  0% { filter: brightness(1); }
  50% { filter: brightness(1.15); }
  100% { filter: brightness(1); }
}

@keyframes gradientShift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.login-card {
  animation: intro 1s ease-out forwards, glow 6s ease-in-out infinite 1s;
}

.btn-shine {
  position: relative;
  overflow: hidden;
}

.btn-shine::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.12), transparent);
  pointer-events: none;
  transform: translateX(-100%);
  transition: transform 0.6s ease;
}

.btn-shine:hover::after {
  transform: translateX(100%);
}
</style>
