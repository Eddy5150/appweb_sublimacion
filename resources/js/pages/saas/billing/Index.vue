<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CreditCard, Check, ArrowLeft, Zap, AlertTriangle } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

const props = defineProps<{
  company: any
  currentPlan: any
  subscription: any
  plans: any[]
  usage: {
    usuarios_actual: number
    productos_actual: number
    clientes_actual: number
    planes: any[]
  }
}>()

const upgrading = ref(false)
const selectedPlan = ref<number | null>(null)

function upgradePlan(planId: number) {
  selectedPlan.value = planId
  upgrading.value = true
  router.post(route('company.subscription.upgrade'), { plan_id: planId }, {
    preserveScroll: true,
    onSuccess: () => { upgrading.value = false; selectedPlan.value = null },
    onError: () => { upgrading.value = false; selectedPlan.value = null },
  })
}

function getRecommendedPlan(): number | null {
  const current = props.currentPlan
  if (!current) return null

  const limits = props.usage

  for (const plan of props.plans) {
    if (plan.id === current.id) continue
    if (limits.usuarios_actual <= plan.max_usuarios &&
        limits.productos_actual <= plan.max_productos &&
        limits.clientes_actual <= plan.max_clientes) {
      return plan.id
    }
  }
  return null
}

const recommendedId = getRecommendedPlan()
</script>

<template>
  <Head title="Facturación · SaaS" />
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <Link :href="route('company.settings')" class="inline-flex items-center gap-1 text-xs text-gray-400 hover:text-white transition-colors mb-2">
          <ArrowLeft class="h-3 w-3" /> Volver a Configuración
        </Link>
        <h1 class="text-2xl font-bold text-white">Facturación y Plan</h1>
        <p class="text-sm text-gray-400 mt-1">Gestiona tu suscripción, plan y método de pago</p>
      </div>
      <CreditCard class="h-8 w-8 text-cyan-400" />
    </div>

    <div v-if="subscription" class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
      <div class="flex items-start justify-between">
        <div>
          <h3 class="text-sm font-semibold text-white mb-1">Plan Actual</h3>
          <p class="text-2xl font-bold text-white">{{ currentPlan?.nombre || company.plan }}</p>
          <div class="mt-2 flex items-center gap-3">
            <span class="rounded-full px-2 py-0.5 text-xs font-medium"
              :class="subscription.estado === 'activa' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-blue-500/10 text-blue-400'">
              {{ subscription.estado === 'activa' ? 'Activo' : 'En Prueba' }}
            </span>
            <span class="text-sm text-gray-500">${{ subscription.precio_pagado }}/{{ subscription.ciclo_pago }}</span>
            <span v-if="subscription.fecha_renovacion" class="text-sm text-gray-500">
              Renueva: {{ subscription.fecha_renovacion }}
            </span>
          </div>
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500 to-fuchsia-500">
          <Zap class="h-6 w-6 text-white" />
        </div>
      </div>
    </div>

    <div v-if="usage" class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4">
      <div class="flex items-start gap-3">
        <AlertTriangle class="h-5 w-5 text-amber-400 shrink-0 mt-0.5" />
        <div>
          <p class="text-sm font-medium text-amber-300">Uso del Plan</p>
          <div class="mt-2 space-y-1.5">
            <div class="flex items-center gap-2 text-sm">
              <span class="text-gray-400 w-24">Usuarios:</span>
              <div class="flex-1 h-2 rounded-full bg-white/5 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all"
                  :style="{ width: Math.min((usage.usuarios_actual / (currentPlan?.max_usuarios || 1)) * 100, 100) + '%' }">
                </div>
              </div>
              <span class="text-gray-300 w-20 text-right">{{ usage.usuarios_actual }}/{{ currentPlan?.max_usuarios || '?' }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="text-gray-400 w-24">Productos:</span>
              <div class="flex-1 h-2 rounded-full bg-white/5 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all"
                  :style="{ width: Math.min((usage.productos_actual / (currentPlan?.max_productos || 1)) * 100, 100) + '%' }">
                </div>
              </div>
              <span class="text-gray-300 w-20 text-right">{{ usage.productos_actual }}/{{ currentPlan?.max_productos || '?' }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <span class="text-gray-400 w-24">Clientes:</span>
              <div class="flex-1 h-2 rounded-full bg-white/5 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all"
                  :style="{ width: Math.min((usage.clientes_actual / (currentPlan?.max_clientes || 1)) * 100, 100) + '%' }">
                </div>
              </div>
              <span class="text-gray-300 w-20 text-right">{{ usage.clientes_actual }}/{{ currentPlan?.max_clientes || '?' }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
      <h3 class="text-sm font-semibold text-white mb-6">Cambiar de Plan</h3>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="plan in plans" :key="plan.id"
          class="relative rounded-xl border p-4 transition-all duration-300 cursor-pointer"
          :class="currentPlan?.id === plan.id ? 'border-fuchsia-500/50 bg-fuchsia-500/5' : recommendedId === plan.id && currentPlan?.id !== plan.id ? 'border-cyan-500/30 bg-cyan-500/5' : 'border-white/[0.04] hover:border-white/20'">
          <div v-if="recommendedId === plan.id && currentPlan?.id !== plan.id" class="absolute -top-2 right-2 rounded-full bg-cyan-500 px-2 py-0.5 text-[10px] font-medium text-white">
            Recomendado
          </div>
          <h4 class="text-base font-semibold text-white">{{ plan.nombre }}</h4>
          <p class="mt-1 text-2xl font-bold text-white">${{ plan.precio_mensual }}</p>
          <p class="text-xs text-gray-500">/mes</p>
          <ul class="mt-3 space-y-1.5">
            <li v-for="f in (plan.caracteristicas || [])" :key="f" class="flex items-center gap-1.5 text-xs text-gray-400">
              <Check class="h-3 w-3 text-emerald-400 shrink-0" /> {{ f }}
            </li>
          </ul>
          <button v-if="currentPlan?.id !== plan.id"
            @click="upgradePlan(plan.id)"
            :disabled="upgrading && selectedPlan === plan.id"
            class="mt-4 w-full rounded-lg px-3 py-2 text-xs font-semibold transition-all cursor-pointer"
            :class="recommendedId === plan.id ? 'bg-gradient-to-r from-cyan-500 to-blue-500 text-white hover:from-cyan-400 hover:to-blue-400' : 'border border-white/20 text-white hover:bg-white/5'">
            {{ upgrading && selectedPlan === plan.id ? 'Procesando...' : plan.precio_mensual > 0 ? 'Cambiar a ' + plan.nombre : 'Mantenerse Gratis' }}
          </button>
          <div v-else class="mt-4 flex items-center justify-center gap-1 rounded-lg bg-emerald-500/10 px-3 py-2 text-xs font-medium text-emerald-400">
            <Check class="h-3 w-3" /> Plan Actual
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
