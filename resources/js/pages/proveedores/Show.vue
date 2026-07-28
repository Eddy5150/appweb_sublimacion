<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Truck, Phone, Mail, MapPin, Globe, FileText, ArrowLeft, ShoppingCart } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

const props = defineProps<{
  proveedor: any
  compras: any[]
  totalCompras: number
  totalGastado: number
}>()

const infoItems = computed(() => [
  { icon: Phone, label: 'Teléfono', value: props.proveedor.telefono || '-' },
  { icon: Mail, label: 'Email', value: props.proveedor.email || '-' },
  { icon: MapPin, label: 'Dirección', value: props.proveedor.direccion || '-' },
  { icon: FileText, label: 'NIT', value: props.proveedor.nit || '-' },
  { icon: Globe, label: 'Tipo', value: props.proveedor.tipo === 'nacional' ? 'Nacional' : 'Internacional' },
])
</script>

<template>
  <Head :title="proveedor.nombre" />
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <Link :href="route('proveedores.index')" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/[0.03] text-gray-400 hover:bg-white/10 hover:text-white transition-all">
        <ArrowLeft class="h-4 w-4" />
      </Link>
      <div>
        <h1 class="text-xl font-bold text-white">{{ proveedor.nombre }}</h1>
        <p class="text-sm text-gray-400">Detalles del proveedor</p>
      </div>
      <Link :href="route('proveedores.edit', proveedor.id)" class="ml-auto rounded-xl bg-cyan-500/10 px-4 py-2 text-sm text-cyan-300 hover:bg-cyan-500/20 transition-all border border-cyan-500/20">
        Editar
      </Link>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2">
        <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
          <h2 class="text-sm font-semibold text-white mb-4">Información de Contacto</h2>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div v-for="item in infoItems" :key="item.label" class="flex items-center gap-3 rounded-xl bg-white/[0.02] px-4 py-3">
              <component :is="item.icon" class="h-4 w-4 text-cyan-400" />
              <div>
                <p class="text-xs text-gray-500">{{ item.label }}</p>
                <p class="text-sm text-white">{{ item.value }}</p>
              </div>
            </div>
          </div>
          <div v-if="proveedor.notas" class="mt-4 rounded-xl bg-white/[0.02] px-4 py-3">
            <p class="text-xs text-gray-500 mb-1">Notas</p>
            <p class="text-sm text-gray-300">{{ proveedor.notas }}</p>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
          <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Estadísticas</h3>
          <div class="space-y-3">
            <div>
              <p class="text-xs text-gray-500">Total Compras</p>
              <p class="text-xl font-bold text-white">{{ totalCompras }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Total Gastado</p>
              <p class="text-xl font-bold text-cyan-400">{{ formatCurrency(totalGastado) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] overflow-hidden">
      <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.04]">
        <h2 class="text-sm font-semibold text-white">Compras Recientes</h2>
        <Link :href="route('compras.index', { proveedor_id: proveedor.id })" class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">Ver todas</Link>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-white/[0.04]">
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/[0.04]">
            <tr v-for="compra in compras" :key="compra.id" class="hover:bg-white/[0.02]">
              <td class="px-4 py-3 text-sm font-medium text-white">{{ compra.codigo }}</td>
              <td class="px-4 py-3 text-sm text-gray-400">{{ compra.fecha_compra }}</td>
              <td class="px-4 py-3 text-sm font-semibold text-cyan-400">{{ formatCurrency(compra.total) }}</td>
              <td class="px-4 py-3">
                <span class="rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="compra.estado === 'completada' ? 'bg-emerald-500/10 text-emerald-400' : compra.estado === 'cancelada' ? 'bg-red-500/10 text-red-400' : 'bg-amber-500/10 text-amber-400'">
                  {{ compra.estado }}
                </span>
              </td>
            </tr>
            <tr v-if="compras.length === 0">
              <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-600">No hay compras registradas</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
