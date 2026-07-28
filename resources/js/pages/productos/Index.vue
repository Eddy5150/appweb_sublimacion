<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { Search, Plus, Pencil, Trash2, Eye, Package, AlertTriangle, BadgeCheck, DollarSign } from '@lucide/vue'
import { toast } from 'vue-sonner'
import { formatCurrency } from '@/lib/format'

const props = defineProps<{
  productos: {
    data: Array<{
      id: number
      nombre: string
      categoria: string | null
      descripcion: string | null
      precio: number
      stock: number
      imagen: string | null
      activo: boolean
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: { search?: string }
  invStats: {
    total: number
    activos: number
    stockBajo: number
    agotados: number
    valor: number
  }
}>()

const form = useForm({
  search: props.filters?.search ?? '',
})

function search() {
  form.get(route('productos.index'))
}

function destroy(id: number) {
  if (!confirm('¿Estás seguro de eliminar este producto?')) return
  form.delete(route('productos.destroy', id), {
    onSuccess: () => toast.success('Producto eliminado'),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Inventario · Productos" />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Inventario</h1>
        <p class="text-muted-foreground">Gestión de stock y productos</p>
      </div>
      <Link :href="route('productos.create')">
        <Button>
          <Plus class="h-4 w-4" />
          Nuevo producto
        </Button>
      </Link>
    </div>

    <!-- Inventory Health -->
    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500/10"><Package class="h-4 w-4 text-blue-400" /></div>
          <div>
            <p class="text-lg font-bold text-white">{{ invStats.activos }} <span class="text-xs font-normal text-gray-500">/ {{ invStats.total }}</span></p>
            <p class="text-[11px] text-gray-500">Productos activos</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg" :class="invStats.stockBajo > 0 ? 'bg-amber-500/10' : 'bg-emerald-500/10'">
            <AlertTriangle class="h-4 w-4" :class="invStats.stockBajo > 0 ? 'text-amber-400' : 'text-emerald-400'" />
          </div>
          <div>
            <p class="text-lg font-bold" :class="invStats.stockBajo > 0 ? 'text-amber-400' : 'text-emerald-400'">{{ invStats.stockBajo }}</p>
            <p class="text-[11px] text-gray-500">Stock bajo (&le;10)</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg" :class="invStats.agotados > 0 ? 'bg-red-500/10' : 'bg-gray-500/10'">
            <BadgeCheck class="h-4 w-4" :class="invStats.agotados > 0 ? 'text-red-400' : 'text-gray-500'" />
          </div>
          <div>
            <p class="text-lg font-bold" :class="invStats.agotados > 0 ? 'text-red-400' : 'text-gray-400'">{{ invStats.agotados }}</p>
            <p class="text-[11px] text-gray-500">Agotados</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-white/[0.06] bg-[#101827] p-4">
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10"><DollarSign class="h-4 w-4 text-emerald-400" /></div>
          <div>
            <p class="text-lg font-bold text-emerald-400">{{ formatCurrency(invStats.valor) }}</p>
            <p class="text-[11px] text-gray-500">Valor inventario</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Search -->
    <div class="mb-6">
      <form @submit.prevent="search" class="flex gap-3">
        <Input v-model="form.search" placeholder="Buscar productos..." class="max-w-sm" />
        <Button type="submit" variant="outline">
          <Search class="h-4 w-4" />
          Buscar
        </Button>
      </form>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
      <table class="w-full">
        <thead>
          <tr class="border-b border-white/10 bg-white/[0.02]">
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Nombre</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Categoría</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Precio</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Stock</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Estado</th>
            <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="producto in productos.data" :key="producto.id" class="border-b border-white/[0.03] last:border-0 transition-colors hover:bg-white/[0.02]">
            <td class="px-4 py-3 text-sm text-foreground">
              <Link :href="route('productos.show', producto.id)" class="hover:text-fuchsia-400 transition-colors">
                {{ producto.nombre }}
              </Link>
            </td>
            <td class="px-4 py-3 text-sm text-muted-foreground">{{ producto.categoria }}</td>
            <td class="px-4 py-3 text-sm font-medium text-fuchsia-400">{{ formatCurrency(producto.precio) }}</td>
            <td class="px-4 py-3 text-sm">
              <span :class="producto.stock === 0 ? 'text-red-400 font-medium' : producto.stock <= 10 ? 'text-amber-400 font-medium' : 'text-foreground'">
                {{ producto.stock }}
              </span>
            </td>
            <td class="px-4 py-3">
              <Badge :variant="producto.activo ? 'success' : 'gray'">
                {{ producto.activo ? 'Activo' : 'Inactivo' }}
              </Badge>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-1">
                <Link :href="route('productos.show', producto.id)">
                  <Button variant="ghost" size="icon">
                    <Eye class="h-4 w-4" />
                  </Button>
                </Link>
                <Link :href="route('productos.edit', producto.id)">
                  <Button variant="ghost" size="icon">
                    <Pencil class="h-4 w-4" />
                  </Button>
                </Link>
                <Button variant="ghost" size="icon" @click="destroy(producto.id)">
                  <Trash2 class="h-4 w-4 text-destructive" />
                </Button>
              </div>
            </td>
          </tr>
          <tr v-if="productos.data.length === 0">
            <td colspan="6" class="px-4 py-12 text-center text-sm text-muted-foreground">
              No se encontraron productos
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="productos.links" class="mt-6">
      <Pagination :links="productos.links" />
    </div>
  </AppLayout>
</template>
