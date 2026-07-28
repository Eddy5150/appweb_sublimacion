<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft, Save, UserPlus } from '@lucide/vue'
import { toast } from 'vue-sonner'

defineProps<{
  roles: { id: number; nombre: string; descripcion: string | null }[]
}>()

const form = useForm({
  name: '',
  email: '',
  telefono: '',
  password: '',
  password_confirmation: '',
  role_id: '',
  estado: 'activo',
  fecha_ingreso: '',
  fecha_fin: '',
  observacion: '',
})

function submit() {
  form.post(route('usuarios.store'), {
    onSuccess: () => toast.success('Usuario creado'),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Nuevo usuario" />

    <!-- Header card -->
    <div
      class="mb-6 rounded-2xl border border-white/10 bg-slate-900/80 p-4"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div
            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500/20 to-fuchsia-500/20 ring-1 ring-purple-500/20"
          >
            <UserPlus class="h-6 w-6 text-purple-400" />
          </div>
          <div>
            <h1 class="text-xl font-bold text-white">Crear usuario</h1>
            <p class="text-sm text-gray-400">
              Registre cuentas internas para el personal autorizado
            </p>
          </div>
        </div>
        <Link
          :href="route('usuarios.index')"
          class="flex h-9 w-9 items-center justify-center rounded-xl border border-white/10 bg-slate-900/60 text-gray-400 transition-colors hover:bg-white/5 hover:text-white"
        >
          <ArrowLeft class="h-4 w-4" />
        </Link>
      </div>
    </div>

    <!-- Form card -->
    <div class="rounded-3xl border border-white/10 bg-slate-900/80 p-6">
      <form @submit.prevent="submit" class="space-y-6">
        <div
          class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
        >
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-300"
              >Nombre completo</label
            >
            <input
              v-model="form.name"
              placeholder="Nombre completo"
              class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white placeholder-gray-500 transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
            />
            <p
              v-if="form.errors.name"
              class="mt-1 text-sm text-red-400"
            >
              {{ form.errors.name }}
            </p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-300"
              >Correo electrónico</label
            >
            <input
              v-model="form.email"
              type="email"
              placeholder="usuario@ejemplo.com"
              class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white placeholder-gray-500 transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
            />
            <p
              v-if="form.errors.email"
              class="mt-1 text-sm text-red-400"
            >
              {{ form.errors.email }}
            </p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-300"
              >Teléfono</label
            >
            <input
              v-model="form.telefono"
              placeholder="Teléfono"
              class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white placeholder-gray-500 transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
            />
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-300"
              >Rol</label
            >
            <select
              v-model="form.role_id"
              class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
            >
              <option value="" class="bg-[#101827]">Seleccionar rol</option>
              <option
                v-for="rol in roles"
                :key="rol.id"
                :value="rol.id"
                class="bg-[#101827]"
              >
                {{ rol.nombre }}
              </option>
            </select>
            <p
              v-if="form.errors.role_id"
              class="mt-1 text-sm text-red-400"
            >
              {{ form.errors.role_id }}
            </p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-300"
              >Estado del usuario</label
            >
            <select
              v-model="form.estado"
              class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
            >
              <option value="activo" class="bg-[#101827]">Activo</option>
              <option value="inactivo" class="bg-[#101827]">Inactivo</option>
            </select>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-300"
              >Fecha de ingreso</label
            >
            <input
              v-model="form.fecha_ingreso"
              type="date"
              class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
            />
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-300"
              >Fecha de fin</label
            >
            <input
              v-model="form.fecha_fin"
              type="date"
              class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
            />
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-300"
              >Contraseña</label
            >
            <input
              v-model="form.password"
              type="password"
              placeholder="Mínimo 8 caracteres"
              class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white placeholder-gray-500 transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
            />
            <p
              v-if="form.errors.password"
              class="mt-1 text-sm text-red-400"
            >
              {{ form.errors.password }}
            </p>
          </div>

          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-300"
              >Confirmar contraseña</label
            >
            <input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Repite la contraseña"
              class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white placeholder-gray-500 transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
            />
          </div>
        </div>

        <!-- Observación -->
        <div>
          <label class="mb-1.5 block text-sm font-medium text-gray-300"
            >Observación / Cargo</label
          >
          <textarea
            v-model="form.observacion"
            rows="2"
            placeholder="Observación o cargo del usuario"
            class="w-full rounded-xl border border-white/10 bg-[#101827] px-4 py-2.5 text-sm text-white placeholder-gray-500 transition-colors focus:border-fuchsia-500/50 focus:outline-none focus:ring-2 focus:ring-fuchsia-500/20"
          ></textarea>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-fuchsia-500 via-pink-500 to-orange-500 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-fuchsia-500/25 transition-all duration-200 hover:shadow-fuchsia-500/40 disabled:opacity-50"
          >
            <Save class="h-4 w-4" />
            Guardar usuario
          </button>
          <Link :href="route('usuarios.index')">
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
