<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import { ShoppingCart, ArrowLeft, Truck, Package, FileText, Calendar, Hash } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

const props = defineProps<{
  compra: any
}>()

const infoItems = computed(() => [
  { icon: Truck, label: 'Proveedor', value: props.compra.proveedor_nombre },
  { icon: Calendar, label: 'Fecha', value: props.compra.fecha_compra },
  { icon: Hash, label: 'Factura', value: props.compra.factura || '-' },
  { icon: FileText, label: 'Pago', value: props.compra.tipo_pago || '-' },
])

const estadoClass = computed(() => ({
  'bg-emerald-500/10 text-emerald-400': props.compra.estado === 'completada',
  'bg-amber-500/10 text-amber-400': props.compra.estado === 'pendiente',
  'bg-red-500/10 text-red-400': props.compra.estado === 'cancelada',
}[props.compra.estado] || 'bg-gray-500/10 text-gray-400'))
</script>

<template>
  <Head :title="'Compra ' + compra.codigo" />
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <Link :href="route('compras.index')" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/[0.03] text-gray-400 hover:bg-white/10 hover:text-white">
        <ArrowLeft class="h-4 w-4" />
      </Link>
      <div>
        <div class="flex items-center gap-3">
          <h1 class="text-xl font-bold text-white">{{ compra.codigo }}</h1>
          <span class="rounded-full px-2.5 py-0.5 text-xs font-medium" :class="estadoClass">{{ compra.estado }}</span>
        </div>
        <p class="text-sm text-gray-400">Detalle de la compra</p>
      </div>
      <Link :href="route('compras.edit', compra.id)" v-if="compra.estado === 'pendiente'"
        class="ml-auto rounded-xl bg-cyan-500/10 px-4 py-2 text-sm text-cyan-300 hover:bg-cyan-500/20 transition-all border border-cyan-500/20">
        Editar
      </Link>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2">
        <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] overflow-hidden">
          <div class="p-6 border-b border-white/[0.04]">
            <h2 class="text-sm font-semibold text-white">Productos</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-white/[0.04]">
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Costo Unit.</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/[0.04]">
                <tr v-for="det in compra.detalles" :key="det.id" class="hover:bg-white/[0.02]">
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                      <Package class="h-4 w-4 text-gray-500" />
                      <span class="text-sm text-white">{{ det.producto_nombre }}</span>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-center text-sm text-gray-400">{{ det.cantidad }}</td>
                  <td class="px-4 py-3 text-right text-sm text-gray-300">{{ formatCurrency(det.costo_unitario) }}</td>
                  <td class="px-4 py-3 text-right text-sm font-semibold text-cyan-400">{{ formatCurrency(det.subtotal) }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr class="border-t border-white/[0.04] bg-white/[0.02]">
                  <td colspan="3" class="px-4 py-3 text-sm font-medium text-gray-300 text-right">Total</td>
                  <td class="px-4 py-3 text-right text-lg font-bold text-cyan-400">{{ formatCurrency(compra.total) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6 space-y-4">
          <h2 class="text-sm font-semibold text-white">Información</h2>
          <div v-for="item in infoItems" :key="item.label" class="flex items-center gap-3">
            <component :is="item.icon" class="h-4 w-4 text-gray-500" />
            <div>
              <p class="text-xs text-gray-500">{{ item.label }}</p>
              <p class="text-sm text-white">{{ item.value }}</p>
            </div>
          </div>
          <div v-if="compra.notas" class="border-t border-white/[0.04] pt-3">
            <p class="text-xs text-gray-500 mb-1">Notas</p>
            <p class="text-sm text-gray-300">{{ compra.notas }}</p>
          </div>
        </div>

        <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
          <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Estado</h3>
          <span class="rounded-full px-3 py-1 text-sm font-medium" :class="estadoClass">{{ compra.estado }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
