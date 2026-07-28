<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import {
  Building2, Users, DollarSign, Activity, TrendingUp,
  CreditCard, ArrowUpRight, Globe, Shield, TrendingDown,
  BarChart3, Zap, ArrowUp, ArrowDown,
} from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

const props = defineProps<{
  stats: {
    totalCompanies: number
    totalUsers: number
    totalIngresos: number
    empresasActivas: number
    mrr: number
    churnRate: number
    growth: { this_month: number; last_month: number; growth_percent: number }
  }
  ingresosPorPlan: { plan: string; total: number }[]
  empresasRecientes: { id: number; nombre: string; email: string; plan: string; estado: string; created_at: string }[]
  subscriptionsPorEstado: Record<string, number>
}>()

const statsCards = computed(() => [
  { label: 'Empresas', value: props.stats.totalCompanies, icon: Building2, color: 'from-cyan-500 to-blue-500', shadow: 'shadow-cyan-500/20' },
  { label: 'Usuarios', value: props.stats.totalUsers, icon: Users, color: 'from-violet-500 to-purple-500', shadow: 'shadow-violet-500/20' },
  { label: 'Activas', value: props.stats.empresasActivas, icon: Activity, color: 'from-emerald-500 to-green-500', shadow: 'shadow-emerald-500/20' },
  { label: 'MRR', value: formatCurrency(props.stats.mrr), icon: DollarSign, color: 'from-amber-500 to-orange-500', shadow: 'shadow-amber-500/20' },
])

const growthClass = computed(() =>
  props.stats.growth.growth_percent >= 0 ? 'text-emerald-400' : 'text-red-400'
)
const growthIcon = computed(() =>
  props.stats.growth.growth_percent >= 0 ? ArrowUp : ArrowDown
)
</script>

<template>
  <Head title="SaaS · Panel Global" />
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Panel SaaS</h1>
        <p class="text-sm text-gray-400 mt-1">Administración global del sistema multiempresa</p>
      </div>
      <Globe class="h-8 w-8 text-cyan-400" />
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div v-for="card in statsCards" :key="card.label"
        class="group relative overflow-hidden rounded-2xl border border-white/[0.04] bg-[#0d111c] p-5 transition-all duration-300 hover:border-white/20">
        <div class="flex items-start justify-between">
          <div>
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ card.label }}</p>
            <p class="mt-2 text-2xl font-bold text-white">{{ card.value }}</p>
          </div>
          <div :class="`flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br ${card.color} ${card.shadow}`">
            <component :is="card.icon" class="h-5 w-5 text-white" />
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-5">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs text-gray-500 uppercase tracking-wider">Churn Rate</span>
          <TrendingDown class="h-4 w-4 text-red-400" />
        </div>
        <p class="text-2xl font-bold text-white">{{ stats.churnRate }}%</p>
        <div class="mt-1 h-1.5 rounded-full bg-white/5 overflow-hidden">
          <div class="h-full rounded-full bg-gradient-to-r from-red-500 to-orange-500 transition-all"
            :style="{ width: Math.min(stats.churnRate, 100) + '%' }"></div>
        </div>
      </div>
      <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-5">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs text-gray-500 uppercase tracking-wider">Crecimiento</span>
          <component :is="growthIcon" class="h-4 w-4" :class="growthClass" />
        </div>
        <p class="text-2xl font-bold text-white" :class="growthClass">{{ stats.growth.growth_percent > 0 ? '+' : '' }}{{ stats.growth.growth_percent }}%</p>
        <p class="text-xs text-gray-500 mt-1">{{ stats.growth.this_month }} este mes vs {{ stats.growth.last_month }} anterior</p>
      </div>
      <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-5">
        <div class="flex items-center justify-between mb-2">
          <span class="text-xs text-gray-500 uppercase tracking-wider">Ingresos Totales</span>
          <BarChart3 class="h-4 w-4 text-amber-400" />
        </div>
        <p class="text-2xl font-bold text-white">{{ formatCurrency(stats.totalIngresos) }}</p>
        <p class="text-xs text-gray-500 mt-1">Histórico acumulado</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
      <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
        <h3 class="text-sm font-semibold text-white mb-4">Ingresos por Plan</h3>
        <div class="space-y-3">
          <div v-for="item in ingresosPorPlan" :key="item.plan"
            class="flex items-center justify-between rounded-xl bg-white/[0.02] px-4 py-3">
            <span class="text-sm text-gray-300">{{ item.plan }}</span>
            <span class="text-sm font-semibold text-cyan-400">{{ formatCurrency(item.total) }}</span>
          </div>
          <div v-if="ingresosPorPlan.length === 0" class="text-center text-sm text-gray-600 py-8">
            No hay ingresos registrados
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
        <h3 class="text-sm font-semibold text-white mb-4">Empresas Recientes</h3>
        <div class="space-y-2">
          <div v-for="empresa in empresasRecientes" :key="empresa.id"
            class="flex items-center justify-between rounded-xl bg-white/[0.02] px-4 py-3 transition-all hover:bg-white/[0.04]">
            <div class="flex items-center gap-3">
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-500/10">
                <Building2 class="h-4 w-4 text-cyan-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-white">{{ empresa.nombre }}</p>
                <p class="text-xs text-gray-500">{{ empresa.email }}</p>
              </div>
            </div>
            <div class="text-right">
              <span class="text-xs text-gray-500">{{ empresa.created_at }}</span>
              <span class="ml-2 rounded-full px-2 py-0.5 text-[10px] font-medium"
                :class="empresa.estado === 'activa' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'">
                {{ empresa.estado }}
              </span>
            </div>
          </div>
          <div v-if="empresasRecientes.length === 0" class="text-center text-sm text-gray-600 py-8">
            No hay empresas registradas
          </div>
        </div>
        <Link :href="route('saas.companies')" class="mt-4 inline-flex items-center gap-1 text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
          Ver todas <ArrowUpRight class="h-3 w-3" />
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
        <h3 class="text-sm font-semibold text-white mb-4">Suscripciones por Estado</h3>
        <div class="space-y-3">
          <div v-for="(total, estado) in subscriptionsPorEstado" :key="estado"
            class="flex items-center justify-between rounded-xl bg-white/[0.02] px-4 py-3">
            <span class="text-sm capitalize text-gray-300">{{ estado }}</span>
            <span class="text-sm font-semibold text-white">{{ total }}</span>
          </div>
        </div>
      </div>
      <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
        <h3 class="text-sm font-semibold text-white mb-4">Acciones Rápidas</h3>
        <div class="space-y-2">
          <Link :href="route('saas.companies')" class="flex items-center gap-3 rounded-xl bg-white/[0.02] px-4 py-3 text-sm text-gray-300 hover:bg-white/[0.04] hover:text-white transition-all">
            <Building2 class="h-4 w-4 text-cyan-400" /> Gestionar Empresas
          </Link>
          <Link :href="route('saas.plans')" class="flex items-center gap-3 rounded-xl bg-white/[0.02] px-4 py-3 text-sm text-gray-300 hover:bg-white/[0.04] hover:text-white transition-all">
            <CreditCard class="h-4 w-4 text-violet-400" /> Gestionar Planes
          </Link>
          <Link :href="route('saas.subscriptions')" class="flex items-center gap-3 rounded-xl bg-white/[0.02] px-4 py-3 text-sm text-gray-300 hover:bg-white/[0.04] hover:text-white transition-all">
            <DollarSign class="h-4 w-4 text-emerald-400" /> Ver Suscripciones
          </Link>
          <Link :href="route('panel')" class="flex items-center gap-3 rounded-xl bg-white/[0.02] px-4 py-3 text-sm text-gray-300 hover:bg-white/[0.04] hover:text-white transition-all">
            <Shield class="h-4 w-4 text-fuchsia-400" /> Ir al Panel ERP
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>
