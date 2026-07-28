<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { toast } from 'vue-sonner'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'
import Modal from '@/components/ui/Modal.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { formatCurrency } from '@/lib/format'
import {
  Search,
  Plus,
  Eye,
  Pencil,
  Trash2,
  Play,
  CheckCircle2,
  RotateCcw,
  AlertTriangle,
  Loader2,
  ChevronDown,
} from '@lucide/vue'

const props = defineProps<{
  pedidos: {
    data: Array<{
      id: number
      cliente_nombre: string
      cliente_telefono: string | null
      total: number
      total_calculado: number | null
      estado: string
      fecha_entrega: string | null
      created_at: string
      cliente: { id: number; nombre: string } | null
      detalles: Array<{
        id: number
        producto_nombre: string
        cantidad: number
        precio_unitario: number
      }>
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
    meta: { total: number }
  }
  stats: {
    total: number
    pendientes: number
    en_proceso: number
    listos: number
    entregados: number
    ingresos: number
  }
  filters: {
    search?: string
    estado?: string
    fecha_desde?: string
    fecha_hasta?: string
    total_min?: string
    total_max?: string
  }
}>()

const badgeVariant: Record<string, 'warning' | 'info' | 'success' | 'gray'> = {
  pendiente: 'warning',
  en_proceso: 'info',
  listo: 'success',
  entregado: 'gray',
}

const badgeLabel: Record<string, string> = {
  pendiente: 'Pendiente',
  en_proceso: 'En Proceso',
  listo: 'Listo',
  entregado: 'Entregado',
}

const search = ref(props.filters?.search ?? '')
const estado = ref(props.filters?.estado ?? '')
const fechaDesde = ref(props.filters?.fecha_desde ?? '')
const fechaHasta = ref(props.filters?.fecha_hasta ?? '')
const totalMin = ref(props.filters?.total_min ?? '')
const totalMax = ref(props.filters?.total_max ?? '')
const loading = ref(false)

function applyFilters() {
  if (!validateFilters()) return
  loading.value = true
  router.get(route('pedidos.index'), {
    search: search.value || undefined,
    estado: estado.value || undefined,
    fecha_desde: fechaDesde.value || undefined,
    fecha_hasta: fechaHasta.value || undefined,
    total_min: totalMin.value || undefined,
    total_max: totalMax.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['pedidos', 'stats', 'filters'],
    onFinish: () => { loading.value = false },
  })
}

function validateFilters(): boolean {
  if (fechaDesde.value && fechaHasta.value && fechaDesde.value > fechaHasta.value) {
    toast.error('La fecha "desde" no puede ser mayor a "hasta"')
    return false
  }
  const min = parseFloat(totalMin.value)
  const max = parseFloat(totalMax.value)
  if (totalMin.value && (isNaN(min) || min < 0)) {
    toast.error('El total mínimo debe ser un número válido')
    return false
  }
  if (totalMax.value && (isNaN(max) || max < 0)) {
    toast.error('El total máximo debe ser un número válido')
    return false
  }
  if (totalMin.value && totalMax.value && min > max) {
    toast.error('El total mínimo no puede ser mayor al máximo')
    return false
  }
  return true
}

function resetFilters() {
  search.value = ''
  estado.value = ''
  fechaDesde.value = ''
  fechaHasta.value = ''
  totalMin.value = ''
  totalMax.value = ''
  loading.value = true
  router.get(route('pedidos.index'), {}, {
    preserveState: true,
    preserveScroll: true,
    only: ['pedidos', 'stats', 'filters'],
    onFinish: () => { loading.value = false },
  })
}

const loadingEstado = ref<Record<number, boolean>>({})
const deletingId = ref<number | null>(null)
const confirmDeleteId = ref<number | null>(null)

function updateStatus(pedidoId: number, nuevoEstado: string) {
  loadingEstado.value[pedidoId] = true
  router.patch(route('pedidos.estado', { pedido: pedidoId }), {
    estado: nuevoEstado,
  }, {
    preserveState: true,
    preserveScroll: true,
    only: ['pedidos', 'stats'],
    onSuccess: () => {
      toast.success(`Pedido marcado como ${badgeLabel[nuevoEstado] || nuevoEstado}`)
    },
    onError: (errors) => {
      const msg = Object.values(errors).join(', ')
      toast.error(msg || 'Error al actualizar estado')
    },
    onFinish: () => {
      loadingEstado.value[pedidoId] = false
    },
  })
}

const allowedTransitions: Record<string, string[]> = {
  pendiente: ['en_proceso'],
  en_proceso: ['listo'],
  listo: ['entregado'],
  entregado: [],
}

function nextStatus(estado: string): string | null {
  const transitions = allowedTransitions[estado]
  return transitions?.length ? transitions[0] : null
}

function confirmDelete(pedidoId: number) {
  confirmDeleteId.value = pedidoId
}

function executeDelete() {
  if (confirmDeleteId.value === null) return
  const id = confirmDeleteId.value
  deletingId.value = id
  router.delete(route('pedidos.destroy', { pedido: id }), {
    preserveState: true,
    preserveScroll: true,
    only: ['pedidos', 'stats'],
    onSuccess: () => {
      toast.success('Pedido eliminado')
      confirmDeleteId.value = null
    },
    onError: (errors) => {
      const msg = Object.values(errors).join(', ')
      toast.error(msg || 'Error al eliminar pedido')
    },
    onFinish: () => {
      deletingId.value = null
    },
  })
}

const resumen = computed(() =>
  props.pedidos.data.map(p => ({
    ...p,
    totalDisplay: p.total_calculado ?? p.total,
    productosResumen: p.detalles?.map(d => `${d.cantidad}x ${d.producto_nombre}`).join(', ') || '—',
    cantidadProductos: p.detalles?.reduce((acc, d) => acc + d.cantidad, 0) || 0,
    sigEstado: nextStatus(p.estado),
  }))
)

const hasActiveFilters = computed(() =>
  !!props.filters?.search || !!props.filters?.estado ||
  !!props.filters?.fecha_desde || !!props.filters?.fecha_hasta ||
  !!props.filters?.total_min || !!props.filters?.total_max
)
</script>

<template>
  <AppLayout>
    <Head title="Pedidos" />

    <!-- Header -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Pedidos</h1>
        <p class="text-muted-foreground">Gestiona los pedidos de tus clientes</p>
      </div>
      <Link :href="route('pedidos.create')">
        <Button>
          <Plus class="h-4 w-4" />
          Nuevo pedido
        </Button>
      </Link>
    </div>

    <!-- Stats Cards -->
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
      <div class="rounded-xl border border-border bg-card p-4 transition-all hover:shadow-md">
        <p class="text-xs font-medium text-muted-foreground">Total</p>
        <p class="mt-1 text-2xl font-bold text-foreground">{{ stats.total }}</p>
      </div>
      <div class="rounded-xl border border-amber-200/50 bg-amber-50/50 p-4 transition-all hover:shadow-md dark:border-amber-800/30 dark:bg-amber-900/10">
        <p class="text-xs font-medium text-amber-600 dark:text-amber-400">Pendientes</p>
        <p class="mt-1 text-2xl font-bold text-amber-700 dark:text-amber-300">{{ stats.pendientes }}</p>
      </div>
      <div class="rounded-xl border border-blue-200/50 bg-blue-50/50 p-4 transition-all hover:shadow-md dark:border-blue-800/30 dark:bg-blue-900/10">
        <p class="text-xs font-medium text-blue-600 dark:text-blue-400">En Proceso</p>
        <p class="mt-1 text-2xl font-bold text-blue-700 dark:text-blue-300">{{ stats.en_proceso }}</p>
      </div>
      <div class="rounded-xl border border-emerald-200/50 bg-emerald-50/50 p-4 transition-all hover:shadow-md dark:border-emerald-800/30 dark:bg-emerald-900/10">
        <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Listos</p>
        <p class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ stats.listos }}</p>
      </div>
      <div class="rounded-xl border border-gray-200/50 bg-gray-50/50 p-4 transition-all hover:shadow-md dark:border-gray-800/30 dark:bg-gray-900/10">
        <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Entregados</p>
        <p class="mt-1 text-2xl font-bold text-gray-700 dark:text-gray-300">{{ stats.entregados }}</p>
      </div>
      <div class="rounded-xl border border-purple-200/50 bg-purple-50/50 p-4 transition-all hover:shadow-md dark:border-purple-800/30 dark:bg-purple-900/10">
        <p class="text-xs font-medium text-purple-600 dark:text-purple-400">Ingresos</p>
        <p class="mt-1 text-2xl font-bold text-purple-700 dark:text-purple-300">{{ formatCurrency(stats.ingresos) }}</p>
      </div>
    </div>

    <!-- Advanced Filters -->
    <div class="mb-6 rounded-xl border border-border bg-card p-4">
      <div class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
          <label class="mb-1 block text-xs font-medium text-muted-foreground">Buscar cliente</label>
          <Input v-model="search" placeholder="Nombre o teléfono..." @keyup.enter="applyFilters" />
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-muted-foreground">Estado</label>
          <select v-model="estado" @change="applyFilters"
            class="flex h-10 rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring min-w-[140px]">
            <option value="">Todos</option>
            <option value="pendiente">Pendiente</option>
            <option value="en_proceso">En proceso</option>
            <option value="listo">Listo</option>
            <option value="entregado">Entregado</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-muted-foreground">Fecha desde</label>
          <input type="date" v-model="fechaDesde"
            class="flex h-10 rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-muted-foreground">Fecha hasta</label>
          <input type="date" v-model="fechaHasta"
            class="flex h-10 rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-muted-foreground">Total min (Bs)</label>
          <Input v-model="totalMin" placeholder="0" type="number" min="0" step="0.01" class="w-24" />
        </div>
        <div>
          <label class="mb-1 block text-xs font-medium text-muted-foreground">Total max (Bs)</label>
          <Input v-model="totalMax" placeholder="9999" type="number" min="0" step="0.01" class="w-24" />
        </div>
        <div class="flex gap-2">
          <Button variant="primary" :loading="loading" @click="applyFilters">
            <Search class="h-4 w-4" />
            Filtrar
          </Button>
          <Button v-if="hasActiveFilters" variant="ghost" :loading="loading" @click="resetFilters">
            <RotateCcw class="h-4 w-4" />
            Limpiar
          </Button>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-xl border border-border bg-card">
      <table class="w-full">
        <thead>
          <tr class="border-b border-border bg-muted/50">
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Cliente</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Productos</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Total</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Estado</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Fecha</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="pedido in resumen" :key="pedido.id"
            class="border-b border-border last:border-0 hover:bg-muted/30 transition-colors"
            :class="{ 'opacity-60 pointer-events-none': loadingEstado[pedido.id] }">
            <td class="px-4 py-3">
              <div class="text-sm font-medium text-foreground">{{ pedido.cliente_nombre }}</div>
              <div v-if="pedido.cliente_telefono" class="text-xs text-muted-foreground">{{ pedido.cliente_telefono }}</div>
            </td>
            <td class="px-4 py-3">
              <div class="text-sm text-muted-foreground max-w-xs truncate" :title="pedido.productosResumen">
                {{ pedido.productosResumen }}
              </div>
              <div class="text-xs text-muted-foreground/60">{{ pedido.cantidadProductos }} unidades</div>
            </td>
            <td class="px-4 py-3 text-sm font-semibold text-foreground">{{ formatCurrency(pedido.totalDisplay) }}</td>
            <td class="px-4 py-3">
              <div class="relative">
                <Badge :variant="badgeVariant[pedido.estado] ?? 'gray'"
                  class="transition-all duration-300"
                  :class="{
                    'scale-110': loadingEstado[pedido.id],
                  }">
                  <Loader2 v-if="loadingEstado[pedido.id]" class="mr-1 h-3 w-3 animate-spin inline" />
                  {{ badgeLabel[pedido.estado] || pedido.estado }}
                </Badge>
              </div>
            </td>
            <td class="px-4 py-3 text-sm text-muted-foreground">
              {{ pedido.fecha_entrega ?? '—' }}
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <!-- Quick action: next status -->
                <template v-if="pedido.sigEstado">
                  <Button
                    variant="ghost"
                    size="sm"
                    :loading="!!loadingEstado[pedido.id]"
                    @click="updateStatus(pedido.id, pedido.sigEstado!)"
                    :title="`Marcar ${badgeLabel[pedido.sigEstado]}`"
                    class="text-blue-500 hover:text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                  >
                    <template v-if="pedido.estado === 'pendiente'">
                      <Play class="h-3.5 w-3.5" />
                    </template>
                    <template v-else-if="pedido.estado === 'en_proceso'">
                      <CheckCircle2 class="h-3.5 w-3.5 text-emerald-500" />
                    </template>
                    <template v-else-if="pedido.estado === 'listo'">
                      <CheckCircle2 class="h-3.5 w-3.5 text-gray-500" />
                    </template>
                  </Button>
                </template>

                <!-- View -->
                <Link :href="route('pedidos.show', pedido.id)">
                  <Button variant="ghost" size="sm" title="Ver detalle">
                    <Eye class="h-3.5 w-3.5" />
                  </Button>
                </Link>

                <!-- Edit -->
                <Link :href="route('pedidos.edit', pedido.id)">
                  <Button variant="ghost" size="sm" title="Editar">
                    <Pencil class="h-3.5 w-3.5" />
                  </Button>
                </Link>

                <!-- Delete -->
                <Button variant="ghost" size="sm"
                  :disabled="pedido.estado === 'entregado'"
                  @click="confirmDelete(pedido.id)"
                  title="Eliminar pedido"
                  class="text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                  :class="{ 'opacity-50 cursor-not-allowed': pedido.estado === 'entregado' }">
                  <Trash2 class="h-3.5 w-3.5" />
                </Button>
              </div>
            </td>
          </tr>
          <tr v-if="loading">
            <td colspan="6" class="px-4 py-12 text-center text-sm text-muted-foreground">
              <div class="flex items-center justify-center gap-2">
                <Loader2 class="h-5 w-5 animate-spin" />
                <span>Cargando...</span>
              </div>
            </td>
          </tr>
          <tr v-else-if="pedidos.data.length === 0">
            <td colspan="6" class="px-4 py-12 text-center text-sm text-muted-foreground">
              <div class="flex flex-col items-center gap-2">
                <AlertTriangle class="h-8 w-8 text-muted-foreground/40" />
                <span>No se encontraron pedidos</span>
                <Button v-if="hasActiveFilters" variant="outline" size="sm" @click="resetFilters">
                  Limpiar filtros
                </Button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <Pagination v-if="pedidos.links && pedidos.links.length > 3" :links="pedidos.links" class="mt-6" />

    <!-- Delete Confirmation Modal -->
    <Modal
      :show="confirmDeleteId !== null"
      @close="confirmDeleteId = null"
      title="Confirmar eliminación"
      size="sm"
    >
      <div class="space-y-4">
        <p class="text-sm text-muted-foreground">
          ¿Estás seguro de eliminar este pedido? Esta acción no se puede deshacer.
        </p>
        <div class="flex justify-end gap-3">
          <Button variant="outline" @click="confirmDeleteId = null">
            Cancelar
          </Button>
          <Button
            variant="destructive"
            :loading="deletingId !== null"
            @click="executeDelete"
          >
            <Trash2 class="h-4 w-4" />
            Eliminar
          </Button>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>
