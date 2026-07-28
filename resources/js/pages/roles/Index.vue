<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  Plus,
  Pencil,
  Trash2,
  Shield,
  Search,
} from '@lucide/vue'
import { toast } from 'vue-sonner'

const props = defineProps<{
  roles: {
    id: number
    nombre: string
    descripcion: string | null
    users_count: number
    permissions: { id: number; nombre: string }[]
  }[]
}>()

const form = useForm({})

const search = ref('')

const filteredRoles = computed(() => {
  if (!search.value) return props.roles
  const q = search.value.toLowerCase()
  return props.roles.filter(
    (r) =>
      r.nombre.toLowerCase().includes(q) ||
      (r.descripcion && r.descripcion.toLowerCase().includes(q)),
  )
})

function destroy(id: number) {
  if (!confirm('¿Estás seguro de eliminar este rol?')) return
  form.delete(route('roles.destroy', id), {
    onSuccess: () => toast.success('Rol eliminado'),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Roles" />

    <!-- Header -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div
          class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-fuchsia-500/20 to-purple-500/20 ring-1 ring-fuchsia-500/20"
        >
          <Shield class="h-6 w-6 text-fuchsia-400" />
        </div>
        <div>
          <h1 class="text-xl font-bold text-white">Gestión de roles</h1>
          <p class="text-sm text-gray-400">
            Administre los roles y permisos del sistema
          </p>
        </div>
      </div>
      <Link
        :href="route('roles.create')"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-fuchsia-500 via-pink-500 to-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-fuchsia-500/25 transition-all duration-200 hover:shadow-fuchsia-500/40"
      >
        <Plus class="h-4 w-4" />
        Nuevo rol
      </Link>
    </div>

    <!-- Search -->
    <div
      class="mb-6 rounded-2xl border border-white/10 bg-slate-900/80 p-4"
    >
      <div class="relative">
        <Search class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500" />
        <input
          v-model="search"
          type="text"
          placeholder="Buscar roles..."
          class="w-full rounded-xl border border-white/10 bg-[#101827] py-2.5 pl-10 pr-4 text-sm text-white placeholder-gray-500 transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
        />
      </div>
    </div>

    <!-- Table -->
    <div
      class="overflow-hidden rounded-3xl border border-white/10 bg-slate-900/80"
    >
      <table class="w-full">
        <thead>
          <tr class="border-b border-white/10">
            <th
              class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400"
            >
              ROL
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400"
            >
              DESCRIPCIÓN
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400"
            >
              PERMISOS
            </th>
            <th
              class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-wider text-gray-400"
            >
              ACCIONES
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
          <tr
            v-for="rol in filteredRoles"
            :key="rol.id"
            class="transition-colors hover:bg-white/[0.02]"
          >
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div
                  class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-fuchsia-500/10 to-purple-500/10 text-xs font-bold text-fuchsia-400 ring-1 ring-fuchsia-500/10"
                >
                  {{ rol.nombre.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <span class="text-sm font-medium text-white">{{
                    rol.nombre
                  }}</span>
                  <span
                    class="ml-2 text-xs text-gray-500"
                  >{{ rol.users_count }} usuario{{ rol.users_count !== 1 ? 's' : '' }}</span>
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="text-sm text-gray-400">{{
                rol.descripcion || '-'
              }}</span>
            </td>
            <td class="px-6 py-4">
              <div class="flex flex-wrap gap-1.5">
                <span
                  v-for="perm in rol.permissions"
                  :key="perm.id"
                  class="inline-flex items-center rounded-full bg-purple-500/10 px-2.5 py-0.5 text-xs font-medium text-purple-400 ring-1 ring-purple-500/20"
                >
                  {{ perm.nombre }}
                </span>
                <span
                  v-if="rol.permissions.length === 0"
                  class="text-xs text-gray-500"
                >Sin permisos</span>
              </div>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <Link
                  :href="route('roles.edit', rol.id)"
                  class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-slate-900/60 text-gray-400 transition-colors hover:bg-white/5 hover:text-white"
                >
                  <Pencil class="h-4 w-4" />
                </Link>
                <button
                  v-if="rol.nombre !== 'admin'"
                  @click="destroy(rol.id)"
                  class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-slate-900/60 text-gray-400 transition-colors hover:bg-red-500/10 hover:text-red-400"
                >
                  <Trash2 class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div
        v-if="filteredRoles.length === 0"
        class="flex flex-col items-center py-12"
      >
        <Shield class="mb-2 h-8 w-8 text-gray-600" />
        <p class="text-sm text-gray-500">No se encontraron roles</p>
      </div>
    </div>
  </AppLayout>
</template>
