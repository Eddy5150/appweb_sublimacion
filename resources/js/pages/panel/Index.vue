<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  Package, ClipboardList, TrendingUp, Shield, Users,
  ArrowRight, AlertTriangle, DollarSign, ShoppingCart,
  Clock, PackageCheck, Zap, BadgeCheck,
} from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

const page = usePage()
const user = computed(() => page.props.auth?.user as any)

defineProps<{
  stats: {
    totalProductos: number
    productosActivos: number
    pedidosPendientes: number
    ventasHoy: number
    ventasMes: number
    totalClientes: number
    stockBajo: number
    stockAgotado: number
    valorInventario: number
  }
  topProductos: { producto_nombre: string; total: number; ingresos: number }[]
  pedidosRecientes: { id: number; cliente_nombre: string; total: number; estado: string; created_at: string }[]
}>()

function capitalize(str: string): string {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''
}

const badgeEstados: Record<string, string> = {
  pendiente: 'bg-amber-500/20 text-amber-300',
  en_proceso: 'bg-blue-500/20 text-blue-300',
  listo: 'bg-emerald-500/20 text-emerald-300',
  entregado: 'bg-gray-500/20 text-gray-400',
}
</script>

<template>
  <AppLayout>
    <Head title="Panel · ERP" />

    <!-- Hero -->
    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-br from-slate-900 via-slate-900 to-purple-950 p-8 shadow-2xl md:p-12">
      <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-purple-500/10 blur-3xl" />
      <div class="pointer-events-none absolute -bottom-20 -left-20 h-48 w-48 rounded-full bg-pink-500/5 blur-3xl" />

      <div class="relative z-10">
        <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-4 py-1.5 text-xs font-medium text-gray-300">
          <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" />
          ERP Sublimación Samuzabety
        </div>
        <h1 class="mt-4 text-3xl font-bold text-white md:text-4xl">
          Bienvenido, {{ user?.name }}
        </h1>
        <div class="mt-3">
          <span class="inline-flex items-center rounded-full bg-purple-500/10 px-3 py-1 text-xs font-medium text-purple-400 ring-1 ring-purple-500/20">
            {{ capitalize(user?.role) }}
          </span>
        </div>
        <p class="mt-4 max-w-2xl text-base leading-relaxed text-gray-400">
          Ventas, inventario, clientes y pedidos en un solo lugar — sistema empresarial de sublimación profesional.
        </p>
      </div>
    </div>

    <!-- KPI Row -->
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
      <!-- Ventas Hoy -->
      <div class="rounded-2xl border border-white/[0.06] bg-[#101827] p-5">
        <div class="flex items-center justify-between">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10"><DollarSign class="h-5 w-5 text-emerald-400" /></div>
          <span class="text-[10px] text-emerald-500/60 font-medium uppercase tracking-wider">Hoy</span>
        </div>
        <p class="mt-3 text-2xl font-bold text-white">{{ formatCurrency(stats.ventasHoy) }}</p>
        <p class="mt-0.5 text-xs text-gray-500">Ventas del día</p>
      </div>

      <!-- Ventas Mes -->
      <div class="rounded-2xl border border-white/[0.06] bg-[#101827] p-5">
        <div class="flex items-center justify-between">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-500/10"><TrendingUp class="h-5 w-5 text-cyan-400" /></div>
          <span class="text-[10px] text-cyan-500/60 font-medium uppercase tracking-wider">Mes</span>
        </div>
        <p class="mt-3 text-2xl font-bold text-white">{{ formatCurrency(stats.ventasMes) }}</p>
        <p class="mt-0.5 text-xs text-gray-500">Ventas del mes</p>
      </div>

      <!-- Pedidos pendientes -->
      <div class="rounded-2xl border border-white/[0.06] bg-[#101827] p-5">
        <div class="flex items-center justify-between">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500/10"><ClipboardList class="h-5 w-5 text-amber-400" /></div>
          <span v-if="stats.pedidosPendientes > 0" class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-500/20 text-[10px] font-bold text-amber-300">{{ stats.pedidosPendientes }}</span>
        </div>
        <p class="mt-3 text-2xl font-bold text-white">{{ stats.pedidosPendientes }}</p>
        <p class="mt-0.5 text-xs text-gray-500">Pedidos pendientes</p>
      </div>

      <!-- Stock bajo -->
      <div class="rounded-2xl border border-white/[0.06] bg-[#101827] p-5">
        <div class="flex items-center justify-between">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl" :class="stats.stockBajo > 0 ? 'bg-red-500/10' : 'bg-emerald-500/10'">
            <Package class="h-5 w-5" :class="stats.stockBajo > 0 ? 'text-red-400' : 'text-emerald-400'" />
          </div>
          <span v-if="stats.stockBajo > 0" class="flex h-5 w-5 items-center justify-center rounded-full bg-red-500/20 text-[10px] font-bold text-red-300">{{ stats.stockBajo }}</span>
        </div>
        <p class="mt-3 text-2xl font-bold text-white">{{ stats.stockBajo }} <span class="text-sm font-normal text-gray-500">/ {{ stats.productosActivos }}</span></p>
        <p class="mt-0.5 text-xs text-gray-500">Productos con stock bajo</p>
      </div>
    </div>

    <!-- Second row: smaller KPIs + quick actions -->
    <div class="mt-4 grid gap-4 lg:grid-cols-3">
      <!-- Left: Inventory health -->
      <div class="rounded-2xl border border-white/[0.06] bg-[#101827] p-5">
        <h3 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
          <PackageCheck class="h-4 w-4 text-cyan-400" /> Salud del inventario
        </h3>
        <div class="space-y-2.5">
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-400 flex items-center gap-1.5"><Package class="h-3.5 w-3.5 text-gray-600" /> Productos activos</span>
            <span class="font-medium text-white">{{ stats.productosActivos }} / {{ stats.totalProductos }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-400 flex items-center gap-1.5"><AlertTriangle class="h-3.5 w-3.5 text-amber-500" /> Stock bajo (&le;10)</span>
            <span class="font-medium" :class="stats.stockBajo > 0 ? 'text-amber-400' : 'text-emerald-400'">{{ stats.stockBajo }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-400 flex items-center gap-1.5"><Shield class="h-3.5 w-3.5 text-red-500" /> Agotados</span>
            <span class="font-medium" :class="stats.stockAgotado > 0 ? 'text-red-400' : 'text-gray-500'">{{ stats.stockAgotado }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-400 flex items-center gap-1.5"><BadgeCheck class="h-3.5 w-3.5 text-emerald-500" /> Valor inventario</span>
            <span class="font-medium text-emerald-400">{{ formatCurrency(stats.valorInventario) }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span class="text-gray-400 flex items-center gap-1.5"><Users class="h-3.5 w-3.5 text-blue-500" /> Clientes registrados</span>
            <span class="font-medium text-white">{{ stats.totalClientes }}</span>
          </div>
        </div>
      </div>

      <!-- Middle: Quick actions -->
      <div class="rounded-2xl border border-white/[0.06] bg-[#101827] p-5">
        <h3 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
          <Zap class="h-4 w-4 text-amber-400" /> Acciones rápidas
        </h3>
        <div class="grid gap-2">
          <Link href="/productos/create" class="flex items-center gap-3 rounded-xl border border-white/[0.06] bg-slate-900/60 p-3 text-sm font-medium text-gray-300 transition-colors hover:bg-white/5 hover:text-white">
            <Package class="h-4 w-4 text-blue-400" /> Nuevo producto <ArrowRight class="ml-auto h-3.5 w-3.5 text-gray-600" />
          </Link>
          <Link href="/pedidos/create" class="flex items-center gap-3 rounded-xl border border-white/[0.06] bg-slate-900/60 p-3 text-sm font-medium text-gray-300 transition-colors hover:bg-white/5 hover:text-white">
            <ClipboardList class="h-4 w-4 text-amber-400" /> Nuevo pedido <ArrowRight class="ml-auto h-3.5 w-3.5 text-gray-600" />
          </Link>
          <Link href="/clientes/create" class="flex items-center gap-3 rounded-xl border border-white/[0.06] bg-slate-900/60 p-3 text-sm font-medium text-gray-300 transition-colors hover:bg-white/5 hover:text-white">
            <Users class="h-4 w-4 text-emerald-400" /> Nuevo cliente <ArrowRight class="ml-auto h-3.5 w-3.5 text-gray-600" />
          </Link>
          <Link href="/catalogo" class="flex items-center gap-3 rounded-xl border border-white/[0.06] bg-slate-900/60 p-3 text-sm font-medium text-gray-300 transition-colors hover:bg-white/5 hover:text-white">
            <ShoppingCart class="h-4 w-4 text-cyan-400" /> Tienda pública <ArrowRight class="ml-auto h-3.5 w-3.5 text-gray-600" />
          </Link>
        </div>
      </div>

      <!-- Right: Top products this month -->
      <div class="rounded-2xl border border-white/[0.06] bg-[#101827] p-5">
        <h3 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
          <TrendingUp class="h-4 w-4 text-emerald-400" /> Más vendidos del mes
        </h3>
        <div v-if="topProductos.length === 0" class="text-xs text-gray-600 py-4 text-center">Sin ventas este mes</div>
        <div v-else class="space-y-2">
          <div v-for="(p, i) in topProductos" :key="i" class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-2 min-w-0 flex-1">
              <span class="shrink-0 flex h-5 w-5 items-center justify-center rounded-full bg-white/[0.04] text-[10px] font-bold text-gray-500">{{ i + 1 }}</span>
              <span class="text-sm text-gray-300 truncate">{{ p.producto_nombre }}</span>
            </div>
            <span class="shrink-0 text-xs font-medium text-gray-400">{{ p.total }} uds</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent orders -->
    <div class="mt-6 rounded-2xl border border-white/[0.06] bg-[#101827] p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-white flex items-center gap-2">
          <Clock class="h-4 w-4 text-cyan-400" /> Pedidos recientes
        </h3>
        <Link href="/pedidos" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">Ver todos →</Link>
      </div>
      <div v-if="pedidosRecientes.length === 0" class="text-xs text-gray-600 py-4 text-center">No hay pedidos aún</div>
      <div v-else class="divide-y divide-white/[0.04]">
        <div v-for="p in pedidosRecientes" :key="p.id" class="flex items-center justify-between py-2.5">
          <div class="flex items-center gap-3 min-w-0">
            <span class="shrink-0 flex h-7 w-7 items-center justify-center rounded-lg bg-white/[0.04] text-[10px] font-bold text-gray-500">#{{ p.id }}</span>
            <div class="min-w-0">
              <p class="text-sm text-white truncate">{{ p.cliente_nombre }}</p>
              <p class="text-[11px] text-gray-600">{{ p.created_at }}</p>
            </div>
          </div>
          <div class="flex items-center gap-3 shrink-0">
            <span class="text-xs rounded-full px-2 py-0.5 font-medium" :class="badgeEstados[p.estado] || 'bg-gray-500/20 text-gray-400'">{{ p.estado.replace('_', ' ') }}</span>
            <span class="text-sm font-semibold text-white">{{ formatCurrency(p.total) }}</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
