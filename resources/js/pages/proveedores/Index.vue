<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Truck, Plus, Search, MoreHorizontal, Globe, MapPin, Phone, Mail } from '@lucide/vue'

const props = defineProps<{
  proveedores: any
  stats: { total: number; activos: number; nacionales: number; internacionales: number }
  filters: { search?: string }
}>()

const search = ref(props.filters.search || '')

function toggleActivo(id: number) {
  router.post(route('proveedores.toggle', id), {}, { preserveScroll: true })
}

function destroy(id: number) {
  if (confirm('¿Eliminar este proveedor?')) {
    router.delete(route('proveedores.destroy', id), { preserveScroll: true })
  }
}

const statCards = [
  { label: 'Total', value: props.stats.total, color: 'from-cyan-500 to-blue-500' },
  { label: 'Activos', value: props.stats.activos, color: 'from-emerald-500 to-green-500' },
  { label: 'Nacionales', value: props.stats.nacionales, color: 'from-violet-500 to-purple-500' },
  { label: 'Internacionales', value: props.stats.internacionales, color: 'from-amber-500 to-orange-500' },
]
</script>

<template>
  <Head title="Proveedores" />
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Proveedores</h1>
        <p class="text-sm text-gray-400 mt-1">Gestiona tus proveedores de insumos y materiales</p>
      </div>
      <Link :href="route('proveedores.create')"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-500 px-4 py-2 text-sm font-semibold text-white hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/20 transition-all">
        <Plus class="h-4 w-4" /> Nuevo Proveedor
      </Link>
    </div>

    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
      <div v-for="card in statCards" :key="card.label"
        class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-4">
        <p class="text-xs text-gray-500">{{ card.label }}</p>
        <p class="mt-1 text-2xl font-bold text-white">{{ card.value }}</p>
        <div class="mt-2 h-1 rounded-full bg-gradient-to-r" :class="card.color + '/20'">
          <div class="h-full rounded-full bg-gradient-to-r" :class="card.color" :style="{ width: (card.value / Math.max(props.stats.total, 1) * 100) + '%' }"></div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] overflow-hidden">
      <div class="p-4 border-b border-white/[0.04]">
        <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/[0.06] px-3 py-2 max-w-xs">
          <Search class="h-4 w-4 text-gray-500" />
          <input v-model="search" type="text" placeholder="Buscar proveedores..."
            class="bg-transparent text-sm text-white placeholder-gray-600 outline-none w-full"
            @keyup.enter="router.get(route('proveedores.index'), { search }, { preserveState: true })" />
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-white/[0.04]">
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Proveedor</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIT</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/[0.04]">
            <tr v-for="prov in proveedores.data" :key="prov.id" class="transition-colors hover:bg-white/[0.02]">
              <td class="px-4 py-3">
                <Link :href="route('proveedores.show', prov.id)" class="flex items-center gap-3">
                  <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500/20 to-blue-500/20">
                    <Truck class="h-4 w-4 text-cyan-400" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-white">{{ prov.nombre }}</p>
                    <p class="text-xs text-gray-500">{{ prov.telefono || prov.email }}</p>
                  </div>
                </Link>
              </td>
              <td class="px-4 py-3 text-sm text-gray-400">
                <div class="flex items-center gap-1.5">
                  <Phone class="h-3 w-3" />{{ prov.contacto || '-' }}
                </div>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs"
                  :class="prov.tipo === 'nacional' ? 'bg-violet-500/10 text-violet-300' : 'bg-amber-500/10 text-amber-300'">
                  <Globe class="h-3 w-3" /> {{ prov.tipo }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ prov.nit || '-' }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="prov.activo ? 'bg-emerald-500/10 text-emerald-400' : 'bg-gray-500/10 text-gray-400'">
                  <span class="h-1.5 w-1.5 rounded-full" :class="prov.activo ? 'bg-emerald-400' : 'bg-gray-400'"></span>
                  {{ prov.activo ? 'Activo' : 'Inactivo' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-1">
                  <Link :href="route('proveedores.edit', prov.id)" class="rounded-lg p-1.5 text-gray-500 hover:bg-white/5 hover:text-white transition-colors">
                    <MoreHorizontal class="h-4 w-4" />
                  </Link>
                  <button @click="toggleActivo(prov.id)" class="rounded-lg p-1.5 text-gray-500 hover:bg-white/5 transition-colors" :class="prov.activo ? 'hover:text-red-400' : 'hover:text-emerald-400'">
                    <component :is="prov.activo ? 'span' : 'span'" class="h-4 w-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="proveedores.last_page > 1" class="flex justify-center gap-2">
      <Link v-for="page in proveedores.last_page" :key="page"
        :href="proveedores.path + '?page=' + page"
        class="flex h-8 w-8 items-center justify-center rounded-lg text-sm transition-colors"
        :class="page === proveedores.current_page ? 'bg-cyan-500/20 text-cyan-300' : 'text-gray-500 hover:bg-white/5 hover:text-white'">
        {{ page }}
      </Link>
    </div>
  </div>
</template>
