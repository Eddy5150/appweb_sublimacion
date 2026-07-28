<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Bar, Line, Pie, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale, LinearScale, BarElement, PointElement,
  LineElement, ArcElement, Title, Tooltip, Legend, Filler,
} from 'chart.js'
import {
  TrendingUp, Calendar, DollarSign, ShoppingCart,
  Package, Users, Download, FileText,
} from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

ChartJS.register(
  CategoryScale, LinearScale, BarElement, PointElement,
  LineElement, ArcElement, Title, Tooltip, Legend, Filler,
)

const props = defineProps<{
  resumen: {
    ventasHoy: number; ventasMes: number; ventasAnual: number
    pedidosHoy: number; pedidosMes: number; pedidosAnual: number
    gananciaEstimada: number; ingresosEstimados: number
  }
  topProductos: { producto_nombre: string; total_cantidad: number; total_ingresos: number }[]
  topClientes: { nombre: string; total_gastado: number }[]
  ventasPorDia: { fecha: string; total: number }[]
  ventasPorMes: { mes: string; total: number }[]
}>()

const tabActivo = ref<'diario' | 'mensual' | 'anual' | 'productos' | 'clientes'>('diario')

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1a1a2e',
      titleColor: '#fff',
      bodyColor: '#94a3b8',
      padding: 12,
      cornerRadius: 8,
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#64748b', font: { size: 10 } },
    },
    y: {
      grid: { color: 'rgba(255,255,255,0.04)' },
      ticks: {
        color: '#64748b',
        font: { size: 10 },
        callback: (v: any) => 'Bs ' + Number(v).toLocaleString('es-BO'),
      },
    },
  },
}

const ventasChartData = computed(() => ({
  labels: props.ventasPorDia.map(d => {
    const parts = d.fecha.split('-')
    return `${parts[2]}/${parts[1]}`
  }),
  datasets: [{
    label: 'Ventas',
    data: props.ventasPorDia.map(d => d.total),
    backgroundColor: 'rgba(6, 182, 212, 0.2)',
    borderColor: '#06b6d4',
    borderWidth: 2,
    fill: true,
    tension: 0.4,
    pointBackgroundColor: '#06b6d4',
    pointRadius: 3,
  }],
}))

const mensualChartData = computed(() => ({
  labels: props.ventasPorMes.map(d => d.mes),
  datasets: [{
    label: 'Ventas',
    data: props.ventasPorMes.map(d => d.total),
    backgroundColor: ['rgba(6, 182, 212, 0.7)', 'rgba(168, 85, 247, 0.7)', 'rgba(34, 197, 94, 0.7)'],
    borderRadius: 6,
  }],
}))

const topProductosChart = computed(() => ({
  labels: props.topProductos.slice(0, 5).map(p => p.producto_nombre.length > 18 ? p.producto_nombre.substring(0, 18) + '…' : p.producto_nombre),
  datasets: [{
    data: props.topProductos.slice(0, 5).map(p => p.total_cantidad),
    backgroundColor: ['#06b6d4', '#a855f7', '#22c55e', '#f59e0b', '#ef4444'],
    borderWidth: 0,
  }],
}))

const topClientesChart = computed(() => ({
  labels: props.topClientes.map(c => c.nombre.length > 15 ? c.nombre.substring(0, 15) + '…' : c.nombre),
  datasets: [{
    data: props.topClientes.map(c => c.total_gastado),
    backgroundColor: ['#a855f7', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444'],
    borderWidth: 0,
  }],
}))

const pieOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: { color: '#94a3b8', padding: 16, usePointStyle: true, font: { size: 11 } },
    },
    tooltip: {
      backgroundColor: '#1a1a2e',
      titleColor: '#fff',
      bodyColor: '#94a3b8',
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: (ctx: any) => {
          const total = ctx.dataset.data.reduce((a: number, b: number) => a + b, 0)
          const pct = ((ctx.parsed / total) * 100).toFixed(1)
          return ` ${ctx.label}: ${ctx.parsed} uds (${pct}%)`
        },
      },
    },
  },
}

function exportar(tipo: string) {
  const des = new URLSearchParams(window.location.search).get('desde') || ''
  const has = new URLSearchParams(window.location.search).get('hasta') || ''
  window.location.href = route(`reportes.exportar-${tipo}`, { desde: des, hasta: has })
}
</script>

<template>
  <AppLayout>
    <Head title="Reportes · ERP" />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Reportes</h1>
        <p class="text-muted-foreground">Ventas, productos y clientes — datos en tiempo real</p>
      </div>
      <div class="flex items-center gap-2">
        <button @click="exportar('pdf')" class="inline-flex items-center gap-2 rounded-xl border border-white/[0.06] bg-white/[0.03] px-4 py-2 text-xs font-medium text-gray-400 hover:bg-white/10 hover:text-white transition-colors cursor-pointer">
          <FileText class="h-3.5 w-3.5" /> PDF
        </button>
        <button @click="exportar('excel')" class="inline-flex items-center gap-2 rounded-xl border border-white/[0.06] bg-white/[0.03] px-4 py-2 text-xs font-medium text-gray-400 hover:bg-white/10 hover:text-white transition-colors cursor-pointer">
          <Download class="h-3.5 w-3.5" /> CSV
        </button>
      </div>
    </div>

    <!-- Resumen cards -->
    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10"><DollarSign class="h-4 w-4 text-emerald-400" /></div>
          <div>
            <p class="text-lg font-bold text-white">{{ formatCurrency(resumen.ventasHoy) }}</p>
            <p class="text-[11px] text-gray-500">Hoy · {{ resumen.pedidosHoy }} pedidos</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-500/10"><Calendar class="h-4 w-4 text-cyan-400" /></div>
          <div>
            <p class="text-lg font-bold text-white">{{ formatCurrency(resumen.ventasMes) }}</p>
            <p class="text-[11px] text-gray-500">Este mes · {{ resumen.pedidosMes }} pedidos</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-500/10"><TrendingUp class="h-4 w-4 text-purple-400" /></div>
          <div>
            <p class="text-lg font-bold text-white">{{ formatCurrency(resumen.ventasAnual) }}</p>
            <p class="text-[11px] text-gray-500">Este año · {{ resumen.pedidosAnual }} pedidos</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10"><ShoppingCart class="h-4 w-4 text-amber-400" /></div>
          <div>
            <p class="text-lg font-bold text-amber-400">{{ formatCurrency(resumen.gananciaEstimada) }}</p>
            <p class="text-[11px] text-gray-500">Ganancia estimada (60% margen)</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="mb-6 flex flex-wrap gap-1.5">
      <button @click="tabActivo = 'diario'" class="rounded-lg px-4 py-2 text-xs font-medium transition-all cursor-pointer"
        :class="tabActivo === 'diario' ? 'bg-white text-gray-900 shadow-md' : 'bg-white/[0.03] text-gray-400 hover:bg-white/[0.06] hover:text-white border border-white/[0.06]'">
        Últimos 30 días
      </button>
      <button @click="tabActivo = 'mensual'" class="rounded-lg px-4 py-2 text-xs font-medium transition-all cursor-pointer"
        :class="tabActivo === 'mensual' ? 'bg-white text-gray-900 shadow-md' : 'bg-white/[0.03] text-gray-400 hover:bg-white/[0.06] hover:text-white border border-white/[0.06]'">
        Mensual
      </button>
      <button @click="tabActivo = 'productos'" class="rounded-lg px-4 py-2 text-xs font-medium transition-all cursor-pointer"
        :class="tabActivo === 'productos' ? 'bg-white text-gray-900 shadow-md' : 'bg-white/[0.03] text-gray-400 hover:bg-white/[0.06] hover:text-white border border-white/[0.06]'">
        Top productos
      </button>
      <button @click="tabActivo = 'clientes'" class="rounded-lg px-4 py-2 text-xs font-medium transition-all cursor-pointer"
        :class="tabActivo === 'clientes' ? 'bg-white text-gray-900 shadow-md' : 'bg-white/[0.03] text-gray-400 hover:bg-white/[0.06] hover:text-white border border-white/[0.06]'">
        Top clientes
      </button>
    </div>

    <!-- Charts -->
    <div class="grid gap-6">
      <!-- Diario: Line Chart -->
      <div v-if="tabActivo === 'diario' || tabActivo === 'mensual'" class="rounded-xl border border-white/[0.06] bg-[#101827] p-5">
        <h3 class="text-sm font-semibold text-white mb-4">
          {{ tabActivo === 'diario' ? 'Ventas diarias (últimos 30 días)' : 'Ventas mensuales' }}
        </h3>
        <div class="h-72">
          <Line v-if="tabActivo === 'diario' && ventasPorDia.length > 0" :data="ventasChartData" :options="chartOptions" />
          <Bar v-if="tabActivo === 'mensual' && ventasPorMes.length > 0" :data="mensualChartData" :options="chartOptions" />
          <div v-if="(tabActivo === 'diario' && ventasPorDia.length === 0) || (tabActivo === 'mensual' && ventasPorMes.length === 0)" class="flex h-full items-center justify-center text-sm text-gray-600">
            Sin datos disponibles
          </div>
        </div>
      </div>

      <!-- Productos: Pie + Table -->
      <div v-if="tabActivo === 'productos'" class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-5">
          <h3 class="text-sm font-semibold text-white mb-4">Top 5 productos vendidos</h3>
          <div class="h-64 flex items-center justify-center">
            <Doughnut v-if="topProductos.length > 0" :data="topProductosChart" :options="pieOptions" />
            <span v-else class="text-sm text-gray-600">Sin ventas</span>
          </div>
        </div>
        <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-5">
          <h3 class="text-sm font-semibold text-white mb-4">Detalle</h3>
          <div v-if="topProductos.length === 0" class="text-sm text-gray-600 py-8 text-center">Sin ventas registradas</div>
          <div v-else class="space-y-2">
            <div v-for="(p, i) in topProductos" :key="i" class="flex items-center justify-between rounded-lg bg-white/[0.02] p-3">
              <div class="flex items-center gap-3 min-w-0">
                <span class="shrink-0 flex h-6 w-6 items-center justify-center rounded-full bg-white/[0.04] text-[10px] font-bold text-gray-500">{{ i + 1 }}</span>
                <div class="min-w-0">
                  <p class="text-sm text-white truncate">{{ p.producto_nombre }}</p>
                  <p class="text-[11px] text-gray-500">{{ p.total_cantidad }} unidades · {{ formatCurrency(p.total_ingresos) }}</p>
                </div>
              </div>
              <span class="shrink-0 text-xs font-semibold text-cyan-400">{{ formatCurrency(p.total_ingresos) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Clientes -->
      <div v-if="tabActivo === 'clientes'" class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-5">
          <h3 class="text-sm font-semibold text-white mb-4">Top clientes por gasto</h3>
          <div class="h-64 flex items-center justify-center">
            <Doughnut v-if="topClientes.length > 0" :data="topClientesChart" :options="pieOptions" />
            <span v-else class="text-sm text-gray-600">Sin datos</span>
          </div>
        </div>
        <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-5">
          <h3 class="text-sm font-semibold text-white mb-4">Clientes más rentables</h3>
          <div v-if="topClientes.length === 0" class="text-sm text-gray-600 py-8 text-center">Sin clientes registrados</div>
          <div v-else class="space-y-2">
            <div v-for="(c, i) in topClientes" :key="i" class="flex items-center justify-between rounded-lg bg-white/[0.02] p-3">
              <div class="flex items-center gap-3">
                <span class="shrink-0 flex h-6 w-6 items-center justify-center rounded-full bg-white/[0.04] text-[10px] font-bold text-gray-500">{{ i + 1 }}</span>
                <span class="text-sm text-white">{{ c.nombre }}</span>
              </div>
              <span class="text-xs font-semibold text-purple-400">{{ formatCurrency(c.total_gastado) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Resumen financiero -->
    <div class="mt-6 rounded-xl border border-white/[0.06] bg-[#101827] p-5">
      <h3 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
        <DollarSign class="h-4 w-4 text-emerald-400" /> Resumen financiero
      </h3>
      <div class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-lg bg-white/[0.02] p-3">
          <p class="text-xs text-gray-500">Ingresos totales</p>
          <p class="text-lg font-bold text-white">{{ formatCurrency(resumen.ingresosEstimados) }}</p>
        </div>
        <div class="rounded-lg bg-white/[0.02] p-3">
          <p class="text-xs text-gray-500">Ganancia estimada (60%)</p>
          <p class="text-lg font-bold text-emerald-400">{{ formatCurrency(resumen.gananciaEstimada) }}</p>
        </div>
        <div class="rounded-lg bg-white/[0.02] p-3">
          <p class="text-xs text-gray-500">Margen bruto</p>
          <p class="text-lg font-bold text-cyan-400">60%</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
