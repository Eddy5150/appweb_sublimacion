<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'
import { formatCurrency } from '@/lib/format'

const props = defineProps<{
  cliente: {
    id: number
    nombre: string
    telefono: string | null
    email: string | null
    direccion: string | null
    estado: string
    created_at: string
    pedidos_count: number
    total_gastado: number
    promedio_compra: number
    ultima_compra: string | null
    primera_compra: string | null
    dias_ultima_compra: number | null
    frecuencia_promedio: number | null
    tendencia: string
    insight: string | null
    riesgo_score: number
    probabilidad_retorno: string
    pedidos: Array<{
      id: number
      codigo: string
      total: number
      estado: string
      created_at: string
    }>
  }
}>()

const form = useForm({})

function badgeVariant(estado: string): string {
  if (estado === 'vip') return 'warning'
  if (estado === 'frecuente') return 'success'
  if (estado === 'inactivo') return 'gray'
  if (estado === 'en_riesgo') return 'danger'
  return 'primary'
}

function estadoPedidoVariant(estado: string): string {
  if (estado === 'entregado') return 'success'
  if (estado === 'listo') return 'primary'
  if (estado === 'en_proceso') return 'warning'
  return 'gray'
}

function tendenciaIcon(t: string): string {
  if (t === 'sube') return '📈'
  if (t === 'baja') return '📉'
  return '➡️'
}

function formatDate(date: string | null): string {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('es-BO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

function daysLabel(days: number | null): string {
  if (days === null) return '—'
  if (days === 0) return 'Hoy'
  if (days === 1) return 'Ayer'
  return `${days} días`
}

function scoreColor(score: number): string {
  if (score >= 70) return 'text-emerald-500'
  if (score >= 40) return 'text-amber-500'
  return 'text-red-500'
}

function probaBadge(proba: string): string {
  if (proba === 'alta') return 'success'
  if (proba === 'media') return 'warning'
  return 'danger'
}

function destroy() {
  if (!confirm('¿Estás seguro de eliminar este cliente?')) return
  form.delete(route('clientes.destroy', props.cliente.id))
}
</script>

<template>
  <AppLayout>
    <Head :title="cliente.nombre" />

    <div class="mb-6">
      <Link :href="route('clientes.index')" class="text-sm text-muted-foreground hover:text-primary">&larr; Volver a clientes</Link>
    </div>

    <div class="mb-6 rounded-xl border border-border bg-card p-6">
      <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary/10 text-2xl font-bold text-primary">
            {{ cliente.nombre.charAt(0) }}
          </div>
          <div>
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-foreground">{{ cliente.nombre }}</h1>
              <Badge :variant="badgeVariant(cliente.estado)">{{ cliente.estado }}</Badge>
            </div>
            <p class="mt-1 text-sm text-muted-foreground">
              {{ cliente.telefono ?? 'Sin teléfono' }}
              <span v-if="cliente.email"> · {{ cliente.email }}</span>
              <span v-if="cliente.direccion"> · {{ cliente.direccion }}</span>
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="route('clientes.edit', cliente.id)">
            <Button variant="outline">Editar</Button>
          </Link>
          <Button variant="destructive" @click="destroy">Eliminar</Button>
        </div>
      </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-xs font-medium text-muted-foreground">Pedidos</p>
        <p class="text-lg font-bold text-foreground">{{ cliente.pedidos_count }}</p>
      </div>
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-xs font-medium text-muted-foreground">Total gastado</p>
        <p class="text-lg font-bold text-foreground">{{ formatCurrency(cliente.total_gastado) }}</p>
      </div>
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-xs font-medium text-muted-foreground">Promedio por compra</p>
        <p class="text-lg font-bold text-foreground">{{ formatCurrency(cliente.promedio_compra) }}</p>
      </div>
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-xs font-medium text-muted-foreground">Frecuencia promedio</p>
        <p class="text-lg font-bold text-foreground">{{ cliente.frecuencia_promedio ? `${cliente.frecuencia_promedio.toFixed(1)} días` : '—' }}</p>
      </div>
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-xs font-medium text-muted-foreground">Última compra</p>
        <p class="text-lg font-bold text-foreground">{{ daysLabel(cliente.dias_ultima_compra) }}</p>
        <p class="text-xs text-muted-foreground">{{ formatDate(cliente.ultima_compra) }}</p>
      </div>
    </div>

    <div v-if="cliente.insight || cliente.tendencia || cliente.riesgo_score !== undefined || cliente.probabilidad_retorno" class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-xs font-medium text-muted-foreground">Tendencia</p>
        <div class="mt-1 flex items-center gap-2">
          <span class="text-2xl">{{ tendenciaIcon(cliente.tendencia) }}</span>
          <span class="text-sm capitalize text-foreground">{{ cliente.tendencia }}</span>
        </div>
      </div>
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-xs font-medium text-muted-foreground">Riesgo de pérdida</p>
        <p class="mt-1 text-lg font-bold" :class="scoreColor(cliente.riesgo_score)">{{ cliente.riesgo_score }}%</p>
      </div>
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-xs font-medium text-muted-foreground">Probabilidad de retorno</p>
        <div class="mt-1">
          <Badge :variant="probaBadge(cliente.probabilidad_retorno)">
            {{ cliente.probabilidad_retorno === 'alta' ? 'Alta' : cliente.probabilidad_retorno === 'media' ? 'Media' : 'Baja' }}
          </Badge>
        </div>
      </div>
      <div class="rounded-xl border border-border bg-card p-4">
        <p class="text-xs font-medium text-muted-foreground">Insight automático</p>
        <p class="mt-1 text-sm text-foreground">{{ cliente.insight ?? '—' }}</p>
      </div>
    </div>

    <div class="rounded-xl border border-border bg-card p-6">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-foreground">Historial de pedidos</h2>
        <Link :href="route('pedidos.create', { cliente: cliente.id })">
          <Button variant="outline" size="sm">
            Nuevo pedido
          </Button>
        </Link>
      </div>
      <div v-if="cliente.pedidos.length > 0" class="overflow-hidden rounded-lg border border-border">
        <table class="w-full">
          <thead>
            <tr class="border-b border-border bg-muted/50">
              <th class="px-4 py-2 text-left text-sm font-medium text-muted-foreground">Código</th>
              <th class="px-4 py-2 text-left text-sm font-medium text-muted-foreground">Fecha</th>
              <th class="px-4 py-2 text-right text-sm font-medium text-muted-foreground">Total</th>
              <th class="px-4 py-2 text-center text-sm font-medium text-muted-foreground">Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="pedido in cliente.pedidos" :key="pedido.id" class="border-b border-border last:border-0 hover:bg-muted/30">
              <td class="px-4 py-2 text-sm font-medium text-foreground">{{ pedido.codigo }}</td>
              <td class="px-4 py-2 text-sm text-muted-foreground">{{ formatDate(pedido.created_at) }}</td>
              <td class="px-4 py-2 text-right text-sm font-semibold text-foreground">{{ formatCurrency(pedido.total) }}</td>
              <td class="px-4 py-2 text-center">
                <Badge :variant="estadoPedidoVariant(pedido.estado)">{{ pedido.estado }}</Badge>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else class="text-sm text-muted-foreground">Este cliente no tiene pedidos aún.</p>
    </div>
  </AppLayout>
</template>
