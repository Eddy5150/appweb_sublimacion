<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { ShoppingCart, Plus, Minus, Trash2, ArrowLeft, Search } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'
import { toast } from 'vue-sonner'

const props = defineProps<{
  compra: any
  proveedores: { id: number; nombre: string }[]
  productos: { id: number; nombre: string; precio: number; stock: number }[]
}>()

const form = ref({
  proveedor_id: props.compra.proveedor_id || '',
  proveedor_nombre: props.compra.proveedor_nombre,
  tipo_pago: props.compra.tipo_pago || '',
  factura: props.compra.factura || '',
  notas: props.compra.notas || '',
  fecha_compra: props.compra.fecha_compra,
})

const detalles = ref(props.compra.detalles.map((d: any) => ({
  producto_id: d.producto_id,
  producto_nombre: d.producto_nombre,
  cantidad: d.cantidad,
  costo_unitario: parseFloat(d.costo_unitario),
})))
const searchProd = ref('')
const showProductPicker = ref(false)

const productosFiltrados = computed(() => {
  if (!searchProd.value) return props.productos
  return props.productos.filter(p => p.nombre.toLowerCase().includes(searchProd.value.toLowerCase()))
})

const total = computed(() => detalles.value.reduce((sum, d) => sum + d.cantidad * d.costo_unitario, 0))

function addDetalle(producto: typeof props.productos[0]) {
  const existente = detalles.value.find(d => d.producto_id === producto.id)
  if (existente) { existente.cantidad++; return }
  detalles.value.push({ producto_id: producto.id, producto_nombre: producto.nombre, cantidad: 1, costo_unitario: producto.precio * 0.6 })
  showProductPicker.value = false; searchProd.value = ''
}

function removeDetalle(index: number) { detalles.value.splice(index, 1) }

function submit() {
  if (detalles.value.length === 0) { toast.error('Agrega al menos un producto'); return }
  router.put(route('compras.update', props.compra.id), { ...form.value, detalles: detalles.value })
}
</script>

<template>
  <Head title="Editar Compra" />
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
      <Link :href="route('compras.index')" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/[0.03] text-gray-400 hover:bg-white/10 hover:text-white">
        <ArrowLeft class="h-4 w-4" />
      </Link>
      <div>
        <h1 class="text-xl font-bold text-white">Editar Compra {{ compra.codigo }}</h1>
        <p class="text-sm text-gray-400">Modifica los datos de la compra</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 space-y-6">
        <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6">
          <h2 class="text-sm font-semibold text-white mb-4">Productos</h2>
          <button @click="showProductPicker = !showProductPicker"
            class="mb-4 rounded-xl bg-cyan-500/10 px-3 py-2 text-xs font-medium text-cyan-300 hover:bg-cyan-500/20 transition-all border border-cyan-500/20 cursor-pointer">
            <Plus class="h-3.5 w-3.5 inline mr-1" /> Agregar Producto
          </button>

          <div v-if="showProductPicker" class="mb-4 rounded-xl bg-white/[0.02] border border-white/[0.06] p-3">
            <div class="flex items-center gap-2 rounded-lg bg-white/[0.03] border border-white/[0.06] px-3 py-2 mb-3">
              <Search class="h-4 w-4 text-gray-500" />
              <input v-model="searchProd" class="bg-transparent text-sm text-white placeholder-gray-600 outline-none w-full" placeholder="Buscar..." />
            </div>
            <div class="max-h-40 overflow-y-auto space-y-1">
              <button v-for="p in productosFiltrados" :key="p.id" @click="addDetalle(p)"
                class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm text-gray-300 hover:bg-white/[0.04] hover:text-white cursor-pointer">
                <span>{{ p.nombre }}</span><span class="text-xs text-gray-500">Bs {{ p.precio }}</span>
              </button>
            </div>
          </div>

          <div class="space-y-2">
            <div v-for="(det, index) in detalles" :key="index"
              class="flex items-center gap-3 rounded-xl bg-white/[0.02] border border-white/[0.04] p-3">
              <div class="flex-1 min-w-0"><p class="text-sm font-medium text-white">{{ det.producto_nombre }}</p></div>
              <div class="flex items-center gap-2">
                <button @click="det.cantidad = Math.max(1, det.cantidad - 1)" class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/[0.03] text-gray-400 hover:text-white cursor-pointer"><Minus class="h-3 w-3" /></button>
                <span class="w-8 text-center text-sm text-white">{{ det.cantidad }}</span>
                <button @click="det.cantidad++" class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/[0.03] text-gray-400 hover:text-white cursor-pointer"><Plus class="h-3 w-3" /></button>
              </div>
              <input v-model.number="det.costo_unitario" type="number" step="0.01" class="w-20 rounded-lg bg-white/[0.03] border border-white/[0.06] px-2 py-1 text-sm text-cyan-400 text-right outline-none" />
              <span class="w-20 text-right text-sm font-semibold text-white">{{ formatCurrency(det.cantidad * det.costo_unitario) }}</span>
              <button @click="removeDetalle(index)" class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-600 hover:bg-red-500/10 hover:text-red-400 cursor-pointer"><Trash2 class="h-3.5 w-3.5" /></button>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6 space-y-4">
          <h2 class="text-sm font-semibold text-white">Datos</h2>
          <select v-model="form.proveedor_id" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none">
            <option value="">-- Seleccionar --</option>
            <option v-for="p in proveedores" :key="p.id" :value="p.id">{{ p.nombre }}</option>
          </select>
          <input v-model="form.fecha_compra" type="date" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none" />
          <input v-model="form.tipo_pago" placeholder="Tipo de pago" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none" />
          <input v-model="form.factura" placeholder="Factura" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none" />
          <textarea v-model="form.notas" rows="2" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none resize-none" placeholder="Notas"></textarea>
        </div>
        <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6 space-y-3">
          <div class="flex items-center justify-between text-lg">
            <span class="text-gray-300">Total</span>
            <span class="font-bold text-cyan-400">{{ formatCurrency(total) }}</span>
          </div>
          <button @click="submit" class="w-full rounded-xl bg-gradient-to-r from-cyan-500 to-sky-500 py-3 text-sm font-bold text-white hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/20 transition-all cursor-pointer">
            Actualizar Compra
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
