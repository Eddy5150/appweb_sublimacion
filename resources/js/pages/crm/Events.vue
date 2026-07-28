<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { History, UserPlus, ShoppingCart, Crown, UserMinus, AlertTriangle, Bell, GitCompare, Zap } from '@lucide/vue'

const props = defineProps<{
  events: {
    data: Array<{
      id: number
      tipo: string
      causable_type: string | null
      causable_id: number | null
      cliente_id: number | null
      cliente_nombre: string | null
      data: Record<string, any> | null
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  tipos: string[]
}>()

function tipoIcon(tipo: string): any {
  if (tipo === 'cliente_created') return UserPlus
  if (tipo === 'pedido_created') return ShoppingCart
  if (tipo === 'cliente_vip_detected') return Crown
  if (tipo === 'cliente_inactivo_detected') return UserMinus
  if (tipo === 'cliente_en_riesgo_detected') return AlertTriangle
  if (tipo === 'alerta_generada') return Bell
  if (tipo === 'segmento_cambiado') return GitCompare
  if (tipo === 'regla_automatizacion') return Zap
  return History
}

function tipoVariant(tipo: string): string {
  if (tipo === 'cliente_vip_detected') return 'warning'
  if (tipo === 'cliente_inactivo_detected' || tipo === 'cliente_en_riesgo_detected') return 'danger'
  if (tipo === 'pedido_created' || tipo === 'cliente_created') return 'success'
  if (tipo === 'alerta_generada') return 'primary'
  return 'gray'
}

function formatData(data: Record<string, any> | null): string {
  if (!data) return ''
  return Object.entries(data)
    .map(([k, v]) => `${k}: ${v}`)
    .join(', ')
}
</script>

<template>
  <AppLayout>
    <Head title="Eventos CRM" />

    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">Eventos del sistema</h1>
      <p class="text-muted-foreground">Registro cronológico de todas las actividades del CRM</p>
    </div>

    <div v-if="events.data.length === 0" class="rounded-xl border border-border bg-card p-12 text-center">
      <History class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
      <h2 class="mb-2 text-lg font-semibold text-foreground">Sin eventos registrados</h2>
      <p class="text-sm text-muted-foreground">Los eventos aparecerán aquí cuando el CRM comience a procesar clientes.</p>
    </div>

    <div v-else class="overflow-hidden rounded-xl border border-border bg-card">
      <table class="w-full">
        <thead>
          <tr class="border-b border-border bg-muted/50">
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Tipo</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Cliente</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Causante</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Detalle</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Fecha</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="event in events.data" :key="event.id" class="border-b border-border last:border-0 hover:bg-muted/30">
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <component :is="tipoIcon(event.tipo)" class="h-4 w-4 text-muted-foreground" />
                <Badge :variant="tipoVariant(event.tipo)">{{ event.tipo.replace(/_/g, ' ') }}</Badge>
              </div>
            </td>
            <td class="px-4 py-3 text-sm">
              <Link v-if="event.cliente_id && event.cliente_nombre" :href="route('clientes.show', event.cliente_id)" class="font-medium text-foreground hover:text-primary">
                {{ event.cliente_nombre }}
              </Link>
              <span v-else class="text-muted-foreground">—</span>
            </td>
            <td class="px-4 py-3 text-sm text-muted-foreground">
              {{ event.causable_type ? `${event.causable_type}#${event.causable_id}` : '—' }}
            </td>
            <td class="px-4 py-3 text-sm text-muted-foreground max-w-[300px] truncate">
              {{ formatData(event.data) }}
            </td>
            <td class="px-4 py-3 text-right text-sm text-muted-foreground whitespace-nowrap">
              {{ event.created_at }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :links="events.links" />
  </AppLayout>
</template>
