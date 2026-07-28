<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Wallet, Plus, X, DollarSign, ArrowUpRight, ArrowDownRight, History, TrendingUp, TrendingDown } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'
import Modal from '@/components/ui/Modal.vue'
import StatCard from '@/components/ui/StatCard.vue'
import Badge from '@/components/ui/Badge.vue'

const props = defineProps<{
  cajaAbierta: {
    id: number; monto_inicial: number; ingresos: number; egresos: number
    saldo_actual: number; user_name: string; apertura_en: string
  } | null
  stats: { abierta: any; total_hoy: number; movimientos_hoy: number }
  aperturas: any[]
}>()

const showAbrirModal = ref(false)
const showCerrarModal = ref(false)
const saving = ref(false)

const formAbrir = ref({ monto_inicial: 0, notas_apertura: '' })
const formCerrar = ref({ monto_final: 0, notas_cierre: '' })

function abrirCaja() {
  saving.value = true
  router.post(route('caja.abrir'), formAbrir.value, {
    preserveScroll: true,
    onSuccess: () => { showAbrirModal.value = false; formAbrir.value = { monto_inicial: 0, notas_apertura: '' }; saving.value = false },
    onError: () => { saving.value = false },
  })
}

function cerrarCaja() {
  saving.value = true
  router.post(route('caja.cerrar'), formCerrar.value, {
    preserveScroll: true,
    onSuccess: () => { showCerrarModal.value = false; formCerrar.value = { monto_final: 0, notas_cierre: '' }; saving.value = false },
    onError: () => { saving.value = false },
  })
}
</script>

<template>
  <Head title="Caja · Control Diario" />
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500/20 to-green-500/20">
          <Wallet class="h-6 w-6 text-emerald-400" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-white">Control de Caja</h1>
          <p class="text-sm text-gray-400 mt-1">Apertura, cierre y movimientos del día</p>
        </div>
      </div>
      <div v-if="!cajaAbierta" class="flex gap-2">
        <button @click="showAbrirModal = true"
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-500 px-5 py-2.5 text-sm font-semibold text-white hover:from-emerald-400 hover:to-green-400 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
          <Plus class="h-4 w-4" /> Abrir Caja
        </button>
      </div>
    </div>

    <div v-if="cajaAbierta" class="rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-6">
      <div class="flex items-start justify-between">
        <div>
          <Badge variant="success">Caja Abierta</Badge>
          <p class="text-sm text-gray-400 mt-2">Abierta por {{ cajaAbierta.user_name }} - {{ cajaAbierta.apertura_en }}</p>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
            <div>
              <p class="text-xs text-gray-500">Inicial</p>
              <p class="text-lg font-bold text-white">{{ formatCurrency(cajaAbierta.monto_inicial) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 flex items-center gap-1"><TrendingUp class="h-3 w-3 text-emerald-400" /> Ingresos</p>
              <p class="text-lg font-bold text-emerald-400">{{ formatCurrency(cajaAbierta.ingresos) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 flex items-center gap-1"><TrendingDown class="h-3 w-3 text-red-400" /> Egresos</p>
              <p class="text-lg font-bold text-red-400">{{ formatCurrency(cajaAbierta.egresos) }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Saldo Actual</p>
              <p class="text-lg font-bold text-cyan-400">{{ formatCurrency(cajaAbierta.saldo_actual) }}</p>
            </div>
          </div>
        </div>
        <button @click="showCerrarModal = true"
          class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-red-500 to-orange-500 px-5 py-2.5 text-sm font-semibold text-white hover:from-red-400 hover:to-orange-400 shadow-lg shadow-red-500/20 transition-all cursor-pointer">
          <X class="h-4 w-4" /> Cerrar Caja
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <StatCard label="Ingresos Hoy" :value="formatCurrency(stats.total_hoy)" :icon="DollarSign" color="from-emerald-500 to-green-500" />
      <StatCard label="Movimientos Hoy" :value="stats.movimientos_hoy" :icon="History" color="from-cyan-500 to-blue-500" />
      <StatCard v-if="cajaAbierta" label="Saldo en Caja" :value="formatCurrency(cajaAbierta.saldo_actual)" :icon="Wallet" color="from-purple-500 to-fuchsia-500" />
    </div>

    <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
      <h3 class="text-sm font-semibold text-white mb-4">Historial de Aperturas</h3>
      <div class="space-y-2">
        <div v-for="a in aperturas" :key="a.id"
          class="flex items-center justify-between rounded-xl bg-white/[0.02] px-4 py-3 transition-all hover:bg-white/[0.04]">
          <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg"
              :class="a.estado === 'abierta' ? 'bg-emerald-500/10' : 'bg-gray-500/10'">
              <Wallet class="h-4 w-4" :class="a.estado === 'abierta' ? 'text-emerald-400' : 'text-gray-400'" />
            </div>
            <div>
              <p class="text-sm font-medium text-white">{{ a.user_name }}</p>
              <p class="text-xs text-gray-500">{{ a.apertura_en }}</p>
            </div>
          </div>
          <div class="flex items-center gap-4">
            <div class="text-right">
              <p class="text-sm text-white">{{ formatCurrency(a.monto_inicial) }}</p>
              <p class="text-xs" :class="a.estado === 'cerrada' ? 'text-gray-500' : 'text-emerald-400'">
                {{ a.estado === 'cerrada' ? formatCurrency(a.monto_final) : 'Abierta' }}
              </p>
            </div>
            <Badge :variant="a.estado === 'abierta' ? 'success' : 'default'">{{ a.estado }}</Badge>
            <Link v-if="a.estado === 'cerrada'" :href="route('caja.movimientos', a.id)" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
              Ver movimientos
            </Link>
          </div>
        </div>
      </div>
    </div>

    <Modal :show="showAbrirModal" title="Abrir Caja" @close="showAbrirModal = false">
      <div class="space-y-4">
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Monto Inicial</label>
          <input v-model="formAbrir.monto_inicial" type="number" step="0.01" min="0"
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500/50" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas (opcional)</label>
          <textarea v-model="formAbrir.notas_apertura" rows="2"
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-emerald-500/50"></textarea>
        </div>
        <button @click="abrirCaja" :disabled="saving"
          class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-green-500 px-5 py-2.5 text-sm font-semibold text-white hover:from-emerald-400 hover:to-green-400 transition-all cursor-pointer">
          {{ saving ? 'Abriendo...' : 'Abrir Caja' }}
        </button>
      </div>
    </Modal>

    <Modal :show="showCerrarModal" title="Cerrar Caja" @close="showCerrarModal = false">
      <div class="space-y-4">
        <div class="rounded-xl bg-cyan-500/5 border border-cyan-500/20 p-4 text-sm">
          <div class="flex justify-between mb-1"><span class="text-gray-400">Saldo Actual:</span><span class="text-white font-semibold">{{ formatCurrency(cajaAbierta?.saldo_actual || 0) }}</span></div>
          <div class="flex justify-between"><span class="text-gray-400">Ingresos:</span><span class="text-emerald-400 font-semibold">{{ formatCurrency(cajaAbierta?.ingresos || 0) }}</span></div>
          <div class="flex justify-between"><span class="text-gray-400">Egresos:</span><span class="text-red-400 font-semibold">{{ formatCurrency(cajaAbierta?.egresos || 0) }}</span></div>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Monto Final (conteo físico)</label>
          <input v-model="formCerrar.monto_final" type="number" step="0.01" min="0"
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-red-500/50" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas (opcional)</label>
          <textarea v-model="formCerrar.notas_cierre" rows="2"
            class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-red-500/50"></textarea>
        </div>
        <button @click="cerrarCaja" :disabled="saving"
          class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-red-500 to-orange-500 px-5 py-2.5 text-sm font-semibold text-white hover:from-red-400 hover:to-orange-400 transition-all cursor-pointer">
          {{ saving ? 'Cerrando...' : 'Cerrar Caja y Corte' }}
        </button>
      </div>
    </Modal>
  </div>
</template>
