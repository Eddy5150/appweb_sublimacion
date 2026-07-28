<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Building2, Search, ChevronDown, MoreHorizontal, Check, X, Shield, Users as UsersIcon } from '@lucide/vue'

const props = defineProps<{
  companies: any
  plans: { id: number; nombre: string; slug: string; precio_mensual: number }[]
}>()

const search = ref('')
const editing = ref<Record<string, boolean>>({})
const editForm = ref<Record<string, any>>({})

function toggleEdit(id: number, company: any) {
  editing.value[id] = !editing.value[id]
  if (editing.value[id]) {
    editForm.value[id] = { ...company }
  }
}

function saveCompany(id: number) {
  router.patch(route('saas.companies.update', id), editForm.value[id], {
    preserveScroll: true,
    onSuccess: () => { editing.value[id] = false },
  })
}

function toggleCompany(id: number) {
  router.post(route('saas.companies.toggle', id), {}, { preserveScroll: true })
}

function deleteCompany(id: number) {
  if (confirm('¿Eliminar esta empresa? Esta acción no se puede deshacer.')) {
    router.delete(route('saas.companies.destroy', id), { preserveScroll: true })
  }
}
</script>

<template>
  <Head title="SaaS · Empresas" />
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Empresas</h1>
        <p class="text-sm text-gray-400 mt-1">{{ companies.total }} empresas registradas en el sistema</p>
      </div>
      <div class="flex items-center gap-2 rounded-xl bg-white/[0.03] border border-white/[0.06] px-3 py-2">
        <Search class="h-4 w-4 text-gray-500" />
        <input v-model="search" type="text" placeholder="Buscar empresas..."
          class="bg-transparent text-sm text-white placeholder-gray-600 outline-none w-48" />
      </div>
    </div>

    <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-white/[0.04]">
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuarios</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creada</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/[0.04]">
            <tr v-for="company in companies.data" :key="company.id"
              class="transition-colors hover:bg-white/[0.02]">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500/20 to-blue-500/20">
                    <Building2 class="h-4 w-4 text-cyan-400" />
                  </div>
                  <div>
                    <template v-if="editing[company.id]">
                      <input v-model="editForm[company.id].nombre" class="rounded-lg bg-white/[0.05] border border-white/[0.08] px-2 py-1 text-sm text-white outline-none" />
                    </template>
                    <template v-else>
                      <p class="text-sm font-medium text-white">{{ company.nombre }}</p>
                      <p class="text-xs text-gray-500">{{ company.email }}</p>
                    </template>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3">
                <template v-if="editing[company.id]">
                  <select v-model="editForm[company.id].plan" class="rounded-lg bg-white/[0.05] border border-white/[0.08] px-2 py-1 text-sm text-white outline-none">
                    <option v-for="plan in plans" :key="plan.slug" :value="plan.slug">{{ plan.nombre }}</option>
                  </select>
                </template>
                <template v-else>
                  <span class="rounded-full bg-cyan-500/10 px-2.5 py-0.5 text-xs font-medium text-cyan-300">
                    {{ company.subscription_plan || company.plan }}
                  </span>
                </template>
              </td>
              <td class="px-4 py-3">
                <template v-if="editing[company.id]">
                  <select v-model="editForm[company.id].estado" class="rounded-lg bg-white/[0.05] border border-white/[0.08] px-2 py-1 text-sm text-white outline-none">
                    <option value="activa">Activa</option>
                    <option value="suspendida">Suspendida</option>
                    <option value="cancelada">Cancelada</option>
                  </select>
                </template>
                <template v-else>
                  <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="company.estado === 'activa' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'">
                    <span class="h-1.5 w-1.5 rounded-full" :class="company.estado === 'activa' ? 'bg-emerald-400' : 'bg-red-400'"></span>
                    {{ company.estado }}
                  </span>
                </template>
              </td>
              <td class="px-4 py-3 text-sm text-gray-400">
                <div class="flex items-center gap-1.5">
                  <UsersIcon class="h-3.5 w-3.5" />
                  {{ company.usuarios_count }}
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ company.created_at }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-1">
                  <template v-if="editing[company.id]">
                    <button @click="saveCompany(company.id)" class="rounded-lg p-1.5 text-emerald-400 hover:bg-emerald-500/10 transition-colors">
                      <Check class="h-4 w-4" />
                    </button>
                    <button @click="editing[company.id] = false" class="rounded-lg p-1.5 text-gray-500 hover:bg-white/5 hover:text-white transition-colors">
                      <X class="h-4 w-4" />
                    </button>
                  </template>
                  <template v-else>
                    <button @click="toggleEdit(company.id, company)" class="rounded-lg p-1.5 text-gray-500 hover:bg-white/5 hover:text-white transition-colors" title="Editar">
                      <MoreHorizontal class="h-4 w-4" />
                    </button>
                    <button @click="toggleCompany(company.id)" class="rounded-lg p-1.5 text-gray-500 hover:bg-white/5 transition-colors"
                      :class="company.estado === 'activa' ? 'hover:text-red-400' : 'hover:text-emerald-400'" :title="company.estado === 'activa' ? 'Suspender' : 'Activar'">
                      <Shield class="h-4 w-4" />
                    </button>
                  </template>
                </div>
              </td>
            </tr>
            <tr v-if="companies.data.length === 0">
              <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-600">No hay empresas registradas</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="companies.last_page > 1" class="flex items-center justify-center gap-2">
      <Link v-for="page in companies.last_page" :key="page" :href="companies.path + '?page=' + page"
        class="flex h-8 w-8 items-center justify-center rounded-lg text-sm transition-colors"
        :class="page === companies.current_page ? 'bg-cyan-500/20 text-cyan-300' : 'text-gray-500 hover:bg-white/5 hover:text-white'">
        {{ page }}
      </Link>
    </div>
  </div>
</template>
