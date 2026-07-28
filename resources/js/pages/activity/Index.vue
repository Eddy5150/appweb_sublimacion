<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { Activity, Users, CalendarDays } from '@lucide/vue'
import StatCard from '@/components/ui/StatCard.vue'
import Badge from '@/components/ui/Badge.vue'
import DataTable from '@/components/ui/DataTable.vue'

const props = defineProps<{
  logs: any[]
  stats: { total: number; hoy: number; usuarios_activos: number }
}>()

const columns = [
  { key: 'created_at', label: 'Fecha', width: '140px' },
  { key: 'user_name', label: 'Usuario', width: '120px' },
  { key: 'tipo', label: 'Tipo', width: '80px' },
  { key: 'modulo', label: 'Módulo', width: '100px' },
  { key: 'accion', label: 'Acción' },
]

const tipoColors: Record<string, string> = {
  create: 'success',
  update: 'info',
  delete: 'danger',
  export: 'warning',
  login: 'purple',
}
</script>

<template>
  <Head title="Registro de Actividad" />
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500/20 to-blue-500/20">
        <Activity class="h-6 w-6 text-cyan-400" />
      </div>
      <div>
        <h1 class="text-2xl font-bold text-white">Registro de Actividad</h1>
        <p class="text-sm text-gray-400 mt-1">Auditoría de acciones de usuarios</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
      <StatCard label="Total Actividades" :value="stats.total" :icon="Activity" color="from-cyan-500 to-blue-500" />
      <StatCard label="Actividades Hoy" :value="stats.hoy" :icon="CalendarDays" color="from-emerald-500 to-green-500" />
      <StatCard label="Usuarios Activos Hoy" :value="stats.usuarios_activos" :icon="Users" color="from-purple-500 to-fuchsia-500" />
    </div>

    <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
      <DataTable :columns="columns" :rows="logs">
        <template #cell-tipo="{ value }">
          <Badge :variant="tipoColors[value] || 'default'">{{ value }}</Badge>
        </template>
        <template #cell-modulo="{ value }">
          <Badge variant="default">{{ value }}</Badge>
        </template>
        <template #cell-accion="{ row }">
          <div>
            <p class="text-sm text-gray-300">{{ row.accion }}</p>
            <p v-if="row.detalles" class="text-xs text-gray-600 mt-0.5">{{ JSON.stringify(row.detalles).slice(0, 80) }}{{ JSON.stringify(row.detalles).length > 80 ? '...' : '' }}</p>
          </div>
        </template>
      </DataTable>
    </div>
  </div>
</template>
