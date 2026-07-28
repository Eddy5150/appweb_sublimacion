<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Wallet, ArrowUpRight, ArrowDownRight, DollarSign } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'
import Badge from '@/components/ui/Badge.vue'
import DataTable from '@/components/ui/DataTable.vue'

const props = defineProps<{
  caja: { id: number; monto_inicial: number; monto_final: number; ingresos: number; egresos: number; saldo_actual: number; estado: string; apertura_en: string }
  movimientos: any[]
}>()

const columns = [
  { key: 'created_at', label: 'Fecha', sortable: true },
  { key: 'tipo', label: 'Tipo', sortable: true },
  { key: 'concepto', label: 'Concepto' },
  { key: 'monto', label: 'Monto', sortable: true },
  { key: 'metodo_pago', label: 'Método' },
  { key: 'user_name', label: 'Usuario' },
]
</script>

<template>
  <Head title="Movimientos de Caja" />
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <Link :href="route('caja.index')" class="text-xs text-gray-400 hover:text-white transition-colors">← Volver a Caja</Link>
    </div>

    <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div><p class="text-xs text-gray-500">Inicial</p><p class="text-lg font-bold text-white">{{ formatCurrency(caja.monto_inicial) }}</p></div>
        <div><p class="text-xs text-gray-500">Ingresos</p><p class="text-lg font-bold text-emerald-400">{{ formatCurrency(caja.ingresos) }}</p></div>
        <div><p class="text-xs text-gray-500">Egresos</p><p class="text-lg font-bold text-red-400">{{ formatCurrency(caja.egresos) }}</p></div>
        <div><p class="text-xs text-gray-500">Saldo</p><p class="text-lg font-bold text-cyan-400">{{ formatCurrency(caja.saldo_actual) }}</p></div>
        <div><p class="text-xs text-gray-500">Final</p><p class="text-lg font-bold text-white">{{ formatCurrency(caja.monto_final) }}</p></div>
      </div>
    </div>

    <DataTable :columns="columns" :rows="movimientos">
      <template #cell-tipo="{ value }">
        <div class="flex items-center gap-1.5">
          <component :is="value === 'ingreso' ? ArrowUpRight : ArrowDownRight" class="h-3.5 w-3.5" :class="value === 'ingreso' ? 'text-emerald-400' : 'text-red-400'" />
          <Badge :variant="value === 'ingreso' ? 'success' : value === 'egreso' ? 'danger' : 'warning'">{{ value }}</Badge>
        </div>
      </template>
      <template #cell-monto="{ value, row }">
        <span :class="row.tipo === 'ingreso' ? 'text-emerald-400' : 'text-red-400'" class="font-medium">
          {{ row.tipo === 'ingreso' ? '+' : '-' }}{{ formatCurrency(value) }}
        </span>
      </template>
      <template #cell-metodo_pago="{ value }">
        <span v-if="value" class="text-xs text-gray-400 capitalize">{{ value }}</span>
        <span v-else class="text-xs text-gray-600">-</span>
      </template>
    </DataTable>
  </div>
</template>
