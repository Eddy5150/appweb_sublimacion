<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'
import { ArrowLeft, Pencil } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

defineProps<{
  pedido: {
    id: number
    cliente_nombre: string
    cliente_telefono: string | null
    cliente_direccion: string | null
    total: number
    estado: string
    fecha_entrega: string | null
    contacto_nombre?: string
    contacto_telefono?: string
    detalles: Array<{
      producto_nombre: string
      cantidad: number
      precio_unitario: number
      subtotal: number
      detalles_diseno: string | null
    }>
  }
}>()

const badgeVariant: Record<string, 'warning' | 'info' | 'success' | 'gray'> = {
  pendiente: 'warning',
  en_proceso: 'info',
  listo: 'success',
  entregado: 'gray',
}
</script>

<template>
  <AppLayout>
    <Head :title="'Pedido #' + pedido.id" />

    <div class="mb-6 flex items-center gap-4">
      <Link :href="route('pedidos.index')">
        <Button variant="ghost" size="icon">
          <ArrowLeft class="h-5 w-5" />
        </Button>
      </Link>
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-foreground">Pedido #{{ pedido.id }}</h1>
        <p class="text-muted-foreground">Detalle del pedido</p>
      </div>
      <Link :href="route('pedidos.edit', pedido.id)">
        <Button variant="outline">
          <Pencil class="h-4 w-4" />
          Editar
        </Button>
      </Link>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 space-y-6">
        <div class="rounded-xl border border-border bg-card p-6">
          <h2 class="mb-4 text-lg font-semibold text-foreground">Productos</h2>
          <table class="w-full">
            <thead>
              <tr class="border-b border-border text-sm text-muted-foreground">
                <th class="pb-2 text-left font-medium">Producto</th>
                <th class="pb-2 text-center font-medium">Cantidad</th>
                <th class="pb-2 text-right font-medium">P/U</th>
                <th class="pb-2 text-right font-medium">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(detalle, index) in pedido.detalles" :key="index" class="border-b border-border/50 last:border-0">
                <td class="py-3 text-sm text-foreground">
                  {{ detalle.producto_nombre }}
                  <p v-if="detalle.detalles_diseno" class="text-xs text-muted-foreground mt-0.5">{{ detalle.detalles_diseno }}</p>
                </td>
                <td class="py-3 text-center text-sm text-foreground">{{ detalle.cantidad }}</td>
                <td class="py-3 text-right text-sm text-foreground">{{ formatCurrency(detalle.precio_unitario) }}</td>
                <td class="py-3 text-right text-sm font-medium text-foreground">{{ formatCurrency(detalle.subtotal) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="border-t border-border">
                <td colspan="3" class="pt-3 text-right text-sm font-semibold text-foreground">Total</td>
                <td class="pt-3 text-right text-sm font-bold text-fuchsia-400">{{ formatCurrency(pedido.total) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="space-y-6">
        <div class="rounded-xl border border-border bg-card p-6">
          <h2 class="mb-4 text-lg font-semibold text-foreground">Información</h2>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-sm text-muted-foreground">Estado</dt>
              <dd><Badge :variant="badgeVariant[pedido.estado] ?? 'gray'">{{ pedido.estado?.replace('_', ' ') }}</Badge></dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-muted-foreground">Fecha de entrega</dt>
              <dd class="text-sm text-foreground">{{ pedido.fecha_entrega ?? '—' }}</dd>
            </div>
          </dl>
        </div>

        <div class="rounded-xl border border-border bg-card p-6">
          <h2 class="mb-4 text-lg font-semibold text-foreground">Cliente</h2>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-sm text-muted-foreground">Nombre</dt>
              <dd class="text-sm text-foreground">{{ pedido.cliente_nombre }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-muted-foreground">Teléfono</dt>
              <dd class="text-sm text-foreground">{{ pedido.cliente_telefono ?? '—' }}</dd>
            </div>
            <div v-if="pedido.cliente_direccion" class="flex justify-between">
              <dt class="text-sm text-muted-foreground">Dirección</dt>
              <dd class="text-sm text-foreground">{{ pedido.cliente_direccion }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
