<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { LayoutDashboard, Package, ClipboardList, TrendingUp } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

defineProps<{
  stats: {
    totalProductos: number
    productosActivos: number
    pedidosPendientes: number
    ventasMes: number
  }
}>()
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />

    <div class="mb-8">
      <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
      <p class="text-muted-foreground">Resumen general del sistema</p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
      <div class="card p-6">
        <div class="flex items-center justify-between">
          <p class="text-sm text-muted-foreground">Productos totales</p>
          <Package class="h-5 w-5 text-primary" />
        </div>
        <p class="mt-2 text-3xl font-bold text-foreground">{{ stats.totalProductos }}</p>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between">
          <p class="text-sm text-muted-foreground">Productos activos</p>
          <LayoutDashboard class="h-5 w-5 text-emerald-500" />
        </div>
        <p class="mt-2 text-3xl font-bold text-foreground">{{ stats.productosActivos }}</p>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between">
          <p class="text-sm text-muted-foreground">Pedidos pendientes</p>
          <ClipboardList class="h-5 w-5 text-amber-500" />
        </div>
        <p class="mt-2 text-3xl font-bold text-foreground">{{ stats.pedidosPendientes }}</p>
      </div>

      <div class="card p-6">
        <div class="flex items-center justify-between">
          <p class="text-sm text-muted-foreground">Ventas del mes</p>
          <TrendingUp class="h-5 w-5 text-fuchsia-500" />
        </div>
        <p class="mt-2 text-3xl font-bold text-foreground">{{ formatCurrency(stats.ventasMes ?? 0) }}</p>
      </div>
    </div>
  </AppLayout>
</template>
