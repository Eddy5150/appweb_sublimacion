<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft } from '@lucide/vue'
import { toast } from 'vue-sonner'

defineProps<{
  permissions: Record<string, { id: number; nombre: string; descripcion: string | null }[]>
}>()

const form = useForm({
  nombre: '',
  descripcion: '',
  permissions: [] as number[],
})

function togglePermission(id: number) {
  const idx = form.permissions.indexOf(id)
  if (idx === -1) {
    form.permissions.push(id)
  } else {
    form.permissions.splice(idx, 1)
  }
}

function submit() {
  form.post(route('roles.store'), {
    onSuccess: () => toast.success('Rol creado'),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Nuevo rol" />

    <!-- Header -->
    <div class="mb-6 flex items-center gap-3">
      <Link
        :href="route('roles.index')"
        class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-slate-900/60 text-gray-400 transition-colors hover:bg-white/5 hover:text-white"
      >
        <ArrowLeft class="h-4 w-4" />
      </Link>
      <div>
        <h1 class="text-xl font-bold text-white">Crear nuevo rol</h1>
        <p class="text-sm text-gray-400">
          Configure un nuevo rol con sus permisos
        </p>
      </div>
    </div>

    <div class="mx-auto max-w-3xl">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Info card -->
        <div
          class="rounded-3xl border border-white/10 bg-slate-900/80 p-6"
        >
          <h2
            class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400"
          >
            Información del rol
          </h2>
          <div class="space-y-4">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-300"
                >Nombre del rol</label
              >
              <input
                v-model="form.nombre"
                placeholder="Ej: Supervisor"
                class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white placeholder-gray-500 transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
              />
              <p
                v-if="form.errors.nombre"
                class="mt-1 text-sm text-red-400"
              >
                {{ form.errors.nombre }}
              </p>
            </div>
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-300"
                >Descripción</label
              >
              <textarea
                v-model="form.descripcion"
                rows="2"
                placeholder="Descripción del rol"
                class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white placeholder-gray-500 transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Permissions -->
        <div
          class="rounded-3xl border border-white/10 bg-slate-900/80 p-6"
        >
          <h2
            class="mb-4 text-xs font-semibold uppercase tracking-wider text-gray-400"
          >
            Permisos
          </h2>
          <div
            v-for="(perms, grupo) in permissions"
            :key="grupo"
            class="mb-6 last:mb-0"
          >
            <h3 class="mb-3 text-sm font-medium capitalize text-gray-300">
              {{ grupo }}
            </h3>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
              <label
                v-for="perm in perms"
                :key="perm.id"
                class="flex cursor-pointer items-center gap-3 rounded-2xl border border-white/10 bg-slate-900/60 p-3 transition-colors hover:bg-white/5"
                :class="{
                  'ring-1 ring-fuchsia-500/30':
                    form.permissions.includes(perm.id),
                }"
              >
                <input
                  type="checkbox"
                  :checked="form.permissions.includes(perm.id)"
                  @change="togglePermission(perm.id)"
                  class="h-4 w-4 rounded border-white/20 bg-[#101827] text-fuchsia-500 focus:ring-fuchsia-500/20 focus:ring-offset-0"
                />
                <span class="text-sm text-gray-300">{{
                  perm.descripcion ?? perm.nombre
                }}</span>
              </label>
            </div>
          </div>
          <p
            v-if="form.errors.permissions"
            class="mt-2 text-sm text-red-400"
          >
            {{ form.errors.permissions }}
          </p>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-fuchsia-500 via-pink-500 to-orange-500 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-fuchsia-500/25 transition-all duration-200 hover:shadow-fuchsia-500/40 disabled:opacity-50"
          >
            Guardar rol
          </button>
          <Link :href="route('roles.index')">
            <span
              class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-slate-900/60 px-6 py-2.5 text-sm font-medium text-gray-300 transition-colors hover:bg-white/5 hover:text-white"
            >
              Cancelar
            </span>
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
