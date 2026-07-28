<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Building2, Save, CreditCard, Users, Package, Globe } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

const page = usePage()
const user = computed(() => page.props.auth?.user as any)

const props = defineProps<{
  company: any
  plan: any | null
  subscription: any | null
}>()

const form = ref({
  nombre: props.company.nombre || '',
  email: props.company.email || '',
  telefono: props.company.telefono || '',
  direccion: props.company.direccion || '',
  sitio_web: props.company.sitio_web || '',
  ciudad: props.company.ciudad || '',
  pais: props.company.pais || '',
})

function submit() {
  router.put(route('company.settings.update'), form.value, {
    preserveScroll: true,
  })
}

const planBadgeClass = computed(() => {
  const colors: Record<string, string> = {
    free: 'bg-gray-500/10 text-gray-400',
    basic: 'bg-cyan-500/10 text-cyan-400',
    pro: 'bg-violet-500/10 text-violet-400',
    premium: 'bg-amber-500/10 text-amber-400',
  }
  return colors[props.company.plan] || colors.free
})
</script>

<template>
  <Head title="Configuración de la Empresa" />
  <div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
      <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-500/20">
        <Building2 class="h-6 w-6 text-cyan-400" />
      </div>
      <div>
        <h1 class="text-2xl font-bold text-white">Configuración de la Empresa</h1>
        <p class="text-sm text-gray-400">Administra los datos de tu empresa</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 space-y-6">
        <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6 space-y-5">
          <h2 class="text-sm font-semibold text-white">Información General</h2>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
              <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre de la Empresa</label>
              <input v-model="form.nombre" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-cyan-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-400 mb-1.5">Email</label>
              <input v-model="form.email" type="email" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-cyan-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-400 mb-1.5">Teléfono</label>
              <input v-model="form.telefono" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-cyan-500/50" />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-xs font-medium text-gray-400 mb-1.5">Dirección</label>
              <input v-model="form.direccion" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-cyan-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-400 mb-1.5">Ciudad</label>
              <input v-model="form.ciudad" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-cyan-500/50" />
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-400 mb-1.5">País</label>
              <input v-model="form.pais" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-cyan-500/50" />
            </div>
            <div class="sm:col-span-2">
              <label class="block text-xs font-medium text-gray-400 mb-1.5">Sitio Web</label>
              <input v-model="form.sitio_web" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-cyan-500/50" placeholder="https://" />
            </div>
          </div>
          <button @click="submit"
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-500 px-6 py-2.5 text-sm font-semibold text-white hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/20 transition-all cursor-pointer">
            <Save class="h-4 w-4" /> Guardar Cambios
          </button>
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6 space-y-4">
          <h2 class="text-sm font-semibold text-white">Plan Actual</h2>
          <div>
            <span class="rounded-full px-3 py-1 text-sm font-medium" :class="planBadgeClass">
              {{ plan?.nombre || company.plan || 'Free' }}
            </span>
          </div>
          <div v-if="subscription" class="space-y-2 text-sm">
            <div class="flex items-center justify-between">
              <span class="text-gray-500">Estado</span>
              <span class="text-white capitalize">{{ subscription.estado }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-500">Ciclo</span>
              <span class="text-white capitalize">{{ subscription.ciclo_pago }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-500">Precio</span>
              <span class="text-cyan-400 font-semibold">{{ formatCurrency(subscription.precio_pagado) }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-500">Renovación</span>
              <span class="text-gray-300">{{ subscription.fecha_renovacion || '-' }}</span>
            </div>
          </div>
          <div v-else class="text-sm text-gray-600">Sin suscripción activa</div>
        </div>

        <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6 space-y-3">
          <h2 class="text-sm font-semibold text-white">Límites del Plan</h2>
          <div v-if="plan" class="space-y-3 text-sm">
            <div class="flex items-center justify-between">
              <span class="flex items-center gap-1.5 text-gray-400"><Users class="h-3.5 w-3.5" /> Usuarios</span>
              <span class="text-white">{{ plan.max_usuarios }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="flex items-center gap-1.5 text-gray-400"><Package class="h-3.5 w-3.5" /> Productos</span>
              <span class="text-white">{{ plan.max_productos }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="flex items-center gap-1.5 text-gray-400"><Users class="h-3.5 w-3.5" /> Clientes</span>
              <span class="text-white">{{ plan.max_clientes }}</span>
            </div>
          </div>
          <div v-else class="text-sm text-gray-500">
            <div class="flex items-center justify-between"><span>Usuarios</span><span>Ilimitado (Free)</span></div>
            <div class="flex items-center justify-between"><span>Productos</span><span>50 (Free)</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
