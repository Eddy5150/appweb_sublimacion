<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Truck, ArrowLeft } from '@lucide/vue'

const form = ref({
  nombre: '', contacto: '', telefono: '', email: '',
  direccion: '', nit: '', tipo: 'nacional', notas: '',
})

function submit() {
  router.post(route('proveedores.store'), form.value)
}
</script>

<template>
  <Head title="Nuevo Proveedor" />
  <div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
      <Link :href="route('proveedores.index')" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/[0.03] text-gray-400 hover:bg-white/10 hover:text-white transition-all">
        <ArrowLeft class="h-4 w-4" />
      </Link>
      <div>
        <h1 class="text-xl font-bold text-white">Nuevo Proveedor</h1>
        <p class="text-sm text-gray-400">Registra un nuevo proveedor en el sistema</p>
      </div>
    </div>

    <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6 space-y-5">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre *</label>
          <input v-model="form.nombre" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none focus:border-cyan-500/50" placeholder="Nombre del proveedor" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Persona de Contacto</label>
          <input v-model="form.contacto" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none focus:border-cyan-500/50" placeholder="Nombre del contacto" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Teléfono</label>
          <input v-model="form.telefono" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none focus:border-cyan-500/50" placeholder="77712345" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Email</label>
          <input v-model="form.email" type="email" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none focus:border-cyan-500/50" placeholder="proveedor@ejemplo.com" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">NIT</label>
          <input v-model="form.nit" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none focus:border-cyan-500/50" placeholder="123456789" />
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Dirección</label>
          <input v-model="form.direccion" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none focus:border-cyan-500/50" placeholder="Dirección del proveedor" />
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Tipo</label>
          <select v-model="form.tipo" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white outline-none focus:border-cyan-500/50">
            <option value="nacional">Nacional</option>
            <option value="internacional">Internacional</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-400 mb-1.5">Notas</label>
          <input v-model="form.notas" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none focus:border-cyan-500/50" placeholder="Notas adicionales" />
        </div>
      </div>

      <div class="flex gap-3 pt-2">
        <button @click="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-sky-500 px-6 py-2.5 text-sm font-semibold text-white hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/20 transition-all cursor-pointer">
          <Truck class="h-4 w-4 inline mr-1.5" /> Guardar Proveedor
        </button>
        <Link :href="route('proveedores.index')" class="rounded-xl bg-white/[0.05] px-4 py-2.5 text-sm text-gray-400 hover:text-white transition-all">
          Cancelar
        </Link>
      </div>
    </div>
  </div>
</template>
