<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { Search, Plus, Eye, Pencil, Trash2 } from '@lucide/vue'
import { toast } from 'vue-sonner'
import { formatCurrency } from '@/lib/format'

defineProps<{
  clientes: {
    data: Array<{
      id: number
      nombre: string
      telefono: string | null
      email: string | null
      estado: string
      pedidos_count: number
      total_gastado: number
      promedio_compra: number
      ultima_compra: string | null
      dias_ultima_compra: number | null
      tendencia: string
      insight: string | null
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: {
    search?: string
  }
}>()

function badgeVariant(estado: string): string {
  if (estado === 'vip') return 'warning'
  if (estado === 'frecuente') return 'success'
  if (estado === 'inactivo') return 'gray'
  if (estado === 'en_riesgo') return 'danger'
  return 'primary'
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

const form = useForm({
  search: '',
})

function search() {
  form.get(route('clientes.index'))
}

function destroy(id: number) {
  if (!confirm('¿Estás seguro de eliminar este cliente?')) return
  form.delete(route('clientes.destroy', id), {
    onSuccess: () => toast.success('Cliente eliminado'),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Clientes" />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Clientes</h1>
        <p class="text-muted-foreground">Gestiona tus clientes con inteligencia CRM</p>
      </div>
      <Link :href="route('clientes.create')">
        <Button>
          <Plus class="h-4 w-4" />
          Nuevo cliente
        </Button>
      </Link>
    </div>

    <div class="mb-6">
      <form @submit.prevent="search" class="flex gap-3">
        <Input v-model="form.search" placeholder="Buscar clientes..." class="max-w-sm" />
        <Button type="submit" variant="outline">
          <Search class="h-4 w-4" />
          Buscar
        </Button>
      </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-border bg-card">
      <table class="w-full">
        <thead>
          <tr class="border-b border-border bg-muted/50">
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Cliente</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Contacto</th>
            <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Pedidos</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Total</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Promedio</th>
            <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Última compra</th>
            <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Tendencia</th>
            <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Segmento</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Insight</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="cliente in clientes.data" :key="cliente.id" class="border-b border-border last:border-0 hover:bg-muted/30">
            <td class="px-4 py-3">
              <Link :href="route('clientes.show', cliente.id)" class="font-medium text-foreground hover:text-primary">
                {{ cliente.nombre }}
              </Link>
            </td>
            <td class="px-4 py-3 text-sm text-muted-foreground">
              <div v-if="cliente.telefono || cliente.email" class="space-y-0.5">
                <div v-if="cliente.telefono">{{ cliente.telefono }}</div>
                <div v-if="cliente.email" class="truncate max-w-[160px]">{{ cliente.email }}</div>
              </div>
              <span v-else>—</span>
            </td>
            <td class="px-4 py-3 text-center text-sm text-foreground">{{ cliente.pedidos_count }}</td>
            <td class="px-4 py-3 text-right text-sm font-semibold text-foreground">{{ formatCurrency(cliente.total_gastado) }}</td>
            <td class="px-4 py-3 text-right text-sm text-foreground">{{ formatCurrency(cliente.promedio_compra) }}</td>
            <td class="px-4 py-3 text-center text-sm text-muted-foreground">
              <div>{{ formatDate(cliente.ultima_compra) }}</div>
              <div v-if="cliente.dias_ultima_compra !== null" class="text-xs" :class="cliente.dias_ultima_compra > 30 ? 'text-red-400' : 'text-muted-foreground'">
                {{ daysLabel(cliente.dias_ultima_compra) }}
              </div>
            </td>
            <td class="px-4 py-3 text-center text-lg">{{ tendenciaIcon(cliente.tendencia) }}</td>
            <td class="px-4 py-3 text-center">
              <Badge :variant="badgeVariant(cliente.estado)">
                {{ cliente.estado }}
              </Badge>
            </td>
            <td class="px-4 py-3 text-xs text-muted-foreground max-w-[160px] truncate">{{ cliente.insight ?? '—' }}</td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                <Link :href="route('clientes.show', cliente.id)">
                  <Button variant="ghost" size="icon">
                    <Eye class="h-4 w-4" />
                  </Button>
                </Link>
                <Link :href="route('clientes.edit', cliente.id)">
                  <Button variant="ghost" size="icon">
                    <Pencil class="h-4 w-4" />
                  </Button>
                </Link>
                <Button variant="ghost" size="icon" @click="destroy(cliente.id)">
                  <Trash2 class="h-4 w-4 text-destructive" />
                </Button>
              </div>
            </td>
          </tr>
          <tr v-if="clientes.data.length === 0">
            <td colspan="10" class="px-4 py-8 text-center text-sm text-muted-foreground">No se encontraron clientes</td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination :links="clientes.links" />
  </AppLayout>
</template>
