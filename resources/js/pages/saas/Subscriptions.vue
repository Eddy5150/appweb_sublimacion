<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CreditCard, Plus, Search, Check, X } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

const props = defineProps<{
  subscriptions: any
  plans: { id: number; nombre: string }[]
  companies: { id: number; nombre: string }[]
}>()

const showCreate = ref(false)
const form = ref({
  company_id: '', plan_id: '', ciclo_pago: 'mensual', estado: 'activa',
  fecha_inicio: '', fecha_renovacion: '', precio_pagado: 0, metodo_pago: '', notas: '',
})

const search = ref('')

function createSubscription() {
  router.post(route('saas.subscriptions.store'), form.value, {
    preserveScroll: true,
    onSuccess: () => {
      showCreate.value = false
      form.value = { company_id: '', plan_id: '', ciclo_pago: 'mensual', estado: 'activa', fecha_inicio: '', fecha_renovacion: '', precio_pagado: 0, metodo_pago: '', notas: '' }
    },
  })
}

const estadoClasses: Record<string, string> = {
  activa: 'bg-emerald-500/10 text-emerald-400',
  prueba: 'bg-blue-500/10 text-blue-400',
  vencida: 'bg-red-500/10 text-red-400',
  cancelada: 'bg-gray-500/10 text-gray-400',
}
</script>

<template>
  <Head title="SaaS · Suscripciones" />
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Suscripciones</h1>
        <p class="text-sm text-gray-400 mt-1">Administra las suscripciones de todas las empresas</p>
      </div>
      <button @click="showCreate = !showCreate"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-500 px-4 py-2 text-sm font-semibold text-white hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/20 transition-all cursor-pointer">
        <Plus class="h-4 w-4" /> Nueva Suscripción
      </button>
    </div>

    <div v-if="showCreate" class="rounded-2xl border border-cyan-500/20 bg-[#0d111c] p-6">
      <h3 class="text-sm font-semibold text-white mb-4">Nueva Suscripción</h3>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
          <label class="block text-xs text-gray-400 mb-1">Empresa</label>
          <select v-model="form.company_id" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none">
            <option value="">Seleccionar...</option>
            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.nombre }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Plan</label>
          <select v-model="form.plan_id" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none">
            <option value="">Seleccionar...</option>
            <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.nombre }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Ciclo de Pago</label>
          <select v-model="form.ciclo_pago" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none">
            <option value="mensual">Mensual</option>
            <option value="anual">Anual</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Estado</label>
          <select v-model="form.estado" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none">
            <option value="activa">Activa</option>
            <option value="prueba">Prueba</option>
            <option value="vencida">Vencida</option>
            <option value="cancelada">Cancelada</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Fecha Inicio</label>
          <input v-model="form.fecha_inicio" type="date" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none" />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Fecha Renovación</label>
          <input v-model="form.fecha_renovacion" type="date" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none" />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Precio Pagado (Bs)</label>
          <input v-model.number="form.precio_pagado" type="number" step="0.01" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none" />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Método de Pago</label>
          <input v-model="form.metodo_pago" placeholder="Tigo Money, BCB, etc." class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none" />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Notas</label>
          <input v-model="form.notas" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none" />
        </div>
      </div>
      <div class="mt-4 flex gap-2">
        <button @click="createSubscription" class="rounded-xl bg-cyan-500 px-6 py-2 text-sm font-semibold text-white hover:bg-cyan-400 transition-all cursor-pointer">Crear</button>
        <button @click="showCreate = false" class="rounded-xl bg-white/[0.05] px-4 py-2 text-sm text-gray-400 hover:text-white transition-all cursor-pointer">Cancelar</button>
      </div>
    </div>

    <div class="rounded-2xl border border-white/[0.04] bg-[#0d111c] overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-white/[0.04]">
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empresa</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ciclo</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Renovación</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creada</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/[0.04]">
            <tr v-for="sub in subscriptions.data" :key="sub.id" class="transition-colors hover:bg-white/[0.02]">
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-cyan-500/10">
                    <CreditCard class="h-3.5 w-3.5 text-cyan-400" />
                  </div>
                  <span class="text-sm text-white">{{ sub.company_nombre }}</span>
                </div>
              </td>
              <td class="px-4 py-3"><span class="text-sm text-gray-300">{{ sub.plan_nombre }}</span></td>
              <td class="px-4 py-3">
                <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="estadoClasses[sub.estado] || 'bg-gray-500/10 text-gray-400'">
                  {{ sub.estado }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-400 capitalize">{{ sub.ciclo_pago }}</td>
              <td class="px-4 py-3 text-sm font-semibold text-cyan-400">{{ formatCurrency(sub.precio_pagado) }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ sub.fecha_renovacion || '-' }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ sub.created_at }}</td>
            </tr>
            <tr v-if="subscriptions.data.length === 0">
              <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-600">No hay suscripciones registradas</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
