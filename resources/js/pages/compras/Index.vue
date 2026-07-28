<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { ShoppingCart, Plus, Search, ArrowLeft, Package, Truck, Eye } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

const props = defineProps<{
  compras: any
  stats: { total: number; pendientes: number; completadas: number; canceladas: number; totalGastado: number }
  filters: { search?: string; estado?: string }
}>()

const search = ref(props.filters.search || '')
const estadoFiltro = ref(props.filters.estado || '')

const statCards = [
  { label: 'Total', value: props.stats.total, color: 'from-cyan-500 to-blue-500' },
  { label: 'Pendientes', value: props.stats.pendientes, color: 'from-amber-500 to-orange-500' },
  { label: 'Completadas', value: props.stats.completadas, color: 'from-emerald-500 to-green-500' },
  { label: 'Total Gastado', value: formatCurrency(props.stats.totalGastado), color: 'from-violet-500 to-purple-500' },
]

const estadoClasses: Record<string, string> = {
  pendiente: 'bg-amber-500/10 text-amber-400',
  completada: 'bg-emerald-500/10 text-emerald-400',
  cancelada: 'bg-red-500/10 text-red-400',
}

function applyFilters() {
  router.get(route('compras.index'), { search: search.value, estado: estadoFiltro.value || undefined }, { preserveState: true })
}

function updateEstado(id: number, estado: string) {
  router.patch(route('compras.estado', id), { estado }, { preserveScroll: true })
}
</script>

<template>
  <Head title="Compras" />
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Compras</h1>
        <p class="text-sm text-gray-400 mt-1">Gestiona las compras a proveedores</p>
      </div>
      <Link :href="route('compras.create')"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-500 px-4 py-2 text-sm font-semibold text-white hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/20 transition-all">
        <Plus class="h-4 w-4" /> Nueva Compra
      </Link>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
      <div v-for="card in statCards" :key="card.label"
        class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-4">
        <p class="text-xs text-gray-500">{{ card.label }}</p>
        <p class="mt-1 text-2xl font-bold text-white">{{ card.value }}</p>
      </div>
    </div>

    <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] overflow-hidden">
      <div class="p-4 border-b border-white/[0.04] flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/[0.06] px-3 py-2">
          <Search class="h-4 w-4 text-gray-500" />
          <input v-model="search" type="text" placeholder="Buscar..."
            class="bg-transparent text-sm text-white placeholder-gray-600 outline-none" @keyup.enter="applyFilters" />
        </div>
        <select v-model="estadoFiltro" @change="applyFilters"
          class="rounded-xl bg-white/[0.03] border border-white/[0.06] px-3 py-2 text-sm text-gray-300 outline-none">
          <option value="">Todos los estados</option>
          <option value="pendiente">Pendiente</option>
          <option value="completada">Completada</option>
          <option value="cancelada">Cancelada</option>
        </select>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-white/[0.04]">
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/[0.04]">
            <tr v-for="compra in compras.data" :key="compra.id" class="transition-colors hover:bg-white/[0.02]">
              <td class="px-4 py-3">
                <Link :href="route('compras.show', compra.id)" class="text-sm font-medium text-cyan-400 hover:text-cyan-300">
                  {{ compra.codigo }}
                </Link>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <Truck class="h-3.5 w-3.5 text-gray-500" />
                  <span class="text-sm text-white">{{ compra.proveedor_nombre }}</span>
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-400">{{ compra.fecha_compra }}</td>
              <td class="px-4 py-3 text-sm font-semibold text-cyan-400">{{ formatCurrency(compra.total) }}</td>
              <td class="px-4 py-3">
                <select :value="compra.estado" @change="updateEstado(compra.id, ($event.target as HTMLSelectElement).value)"
                  class="rounded-lg px-2 py-1 text-xs font-medium outline-none cursor-pointer"
                  :class="estadoClasses[compra.estado] || 'bg-gray-500/10 text-gray-400'">
                  <option value="pendiente">Pendiente</option>
                  <option value="completada">Completada</option>
                  <option value="cancelada">Cancelada</option>
                </select>
              </td>
              <td class="px-4 py-3 text-right">
                <Link :href="route('compras.show', compra.id)" class="rounded-lg p-1.5 text-gray-500 hover:bg-white/5 hover:text-white transition-all inline-flex">
                  <Eye class="h-4 w-4" />
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="compras.last_page > 1" class="flex justify-center gap-2">
      <Link v-for="page in compras.last_page" :key="page"
        :href="compras.path + '?page=' + page"
        class="flex h-8 w-8 items-center justify-center rounded-lg text-sm transition-colors"
        :class="page === compras.current_page ? 'bg-cyan-500/20 text-cyan-300' : 'text-gray-500 hover:bg-white/5 hover:text-white'">
        {{ page }}
      </Link>
    </div>
  </div>
</template>
