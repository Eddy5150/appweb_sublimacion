<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  Plus,
  Pencil,
  Trash2,
  UserCog,
  Search,
  ToggleLeft,
  ToggleRight,
} from '@lucide/vue'
import { toast } from 'vue-sonner'

const props = defineProps<{
  usuarios: {
    id: number
    name: string
    email: string
    estado: string
    roles: { id: number; nombre: string }[]
  }[]
}>()

const form = useForm({})

const search = ref('')

const filteredUsuarios = computed(() => {
  if (!search.value) return props.usuarios
  const q = search.value.toLowerCase()
  return props.usuarios.filter(
    (u) =>
      u.name.toLowerCase().includes(q) ||
      u.email.toLowerCase().includes(q),
  )
})

function toggleEstado(id: number) {
  form.patch(route('usuarios.toggle-estado', id), {
    onSuccess: () => toast.success('Estado actualizado'),
  })
}

function destroy(id: number) {
  if (!confirm('¿Estás seguro de eliminar este usuario?')) return
  form.delete(route('usuarios.destroy', id), {
    onSuccess: () => toast.success('Usuario eliminado'),
  })
}

function getInitials(name: string): string {
  return (
    name
      ?.split(' ')
      .map((n) => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2) || '?'
  )
}

function capitalize(str: string): string {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''
}
</script>

<template>
  <AppLayout>
    <Head title="Usuarios" />

    <!-- Header -->
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div
          class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500/20 to-fuchsia-500/20 ring-1 ring-purple-500/20"
        >
          <UserCog class="h-6 w-6 text-purple-400" />
        </div>
        <div>
          <h1 class="text-xl font-bold text-white">Gestión de usuarios</h1>
          <p class="text-sm text-gray-400">
            Administre los usuarios del sistema
          </p>
        </div>
      </div>
      <Link
        :href="route('usuarios.create')"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-fuchsia-500 via-pink-500 to-orange-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-fuchsia-500/25 transition-all duration-200 hover:shadow-fuchsia-500/40"
      >
        <Plus class="h-4 w-4" />
        Nuevo usuario
      </Link>
    </div>

    <!-- Search -->
    <div class="mb-6 rounded-2xl border border-white/10 bg-slate-900/80 p-4">
      <div class="relative">
        <Search
          class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-500"
        />
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por nombre o correo..."
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
              USUARIO
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400"
            >
              CORREO
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400"
            >
              ROL
            </th>
            <th
              class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-gray-400"
            >
              ESTADO
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
            v-for="usuario in filteredUsuarios"
            :key="usuario.id"
            class="transition-colors hover:bg-white/[0.02]"
          >
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div
                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-500/20 to-fuchsia-500/20 text-xs font-bold text-purple-400 ring-1 ring-purple-500/20"
                >
                  {{ getInitials(usuario.name) }}
                </div>
                <div class="flex flex-col">
                  <span class="text-sm font-medium text-white">{{
                    usuario.name
                  }}</span>
                  <span class="text-xs text-gray-500">{{
                    usuario.email
                  }}</span>
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="text-sm text-gray-400">{{ usuario.email }}</span>
            </td>
            <td class="px-6 py-4">
              <span
                v-if="usuario.roles.length > 0"
                class="inline-flex items-center rounded-full bg-purple-500/10 px-2.5 py-0.5 text-xs font-medium text-purple-400 ring-1 ring-purple-500/20"
              >
                {{ capitalize(usuario.roles[0].nombre) }}
              </span>
              <span v-else class="text-xs text-gray-500">Sin rol</span>
            </td>
            <td class="px-6 py-4">
              <span
                :class="
                  usuario.estado === 'activo'
                    ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20'
                    : 'bg-red-500/10 text-red-400 ring-red-500/20'
                "
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1"
              >
                {{ usuario.estado === 'activo' ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <button
                  @click="toggleEstado(usuario.id)"
                  class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-slate-900/60 text-gray-400 transition-colors hover:bg-white/5 hover:text-white"
                  :title="
                    usuario.estado === 'activo' ? 'Desactivar' : 'Activar'
                  "
                >
                  <component
                    :is="
                      usuario.estado === 'activo' ? ToggleRight : ToggleLeft
                    "
                    class="h-4 w-4"
                  />
                </button>
                <Link
                  :href="route('usuarios.edit', usuario.id)"
                  class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-slate-900/60 text-gray-400 transition-colors hover:bg-white/5 hover:text-white"
                >
                  <Pencil class="h-4 w-4" />
                </Link>
                <button
                  @click="destroy(usuario.id)"
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
        v-if="filteredUsuarios.length === 0"
        class="flex flex-col items-center py-12"
      >
        <UserCog class="mb-2 h-8 w-8 text-gray-600" />
        <p class="text-sm text-gray-500">No se encontraron usuarios</p>
      </div>
    </div>
  </AppLayout>
</template>
