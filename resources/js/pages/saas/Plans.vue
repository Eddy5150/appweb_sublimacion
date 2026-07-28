<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CreditCard, Plus, Check, X, TrendingUp, Users, Package, Database, Shield, Zap, Globe, BarChart3 } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

const props = defineProps<{
  plans: any[]
}>()

const showCreate = ref(false)
const form = ref({
  nombre: '', slug: '', descripcion: '', precio_mensual: 0, precio_anual: 0,
  max_usuarios: 1, max_productos: 50, max_clientes: 100,
  crm_incluido: false, reportes_avanzados: false, exportacion_datos: false,
  api_integraciones: false, soporte_prioritario: false,
})

const editing = ref<Record<string, boolean>>({})
const editForm = ref<Record<string, any>>({})

const featureIcons: Record<string, any> = {
  crm_incluido: Users, reportes_avanzados: BarChart3, exportacion_datos: Database,
  api_integraciones: Globe, soporte_prioritario: Zap,
}

function createPlan() {
  router.post(route('saas.plans.store'), form.value, {
    preserveScroll: true,
    onSuccess: () => {
      showCreate.value = false
      form.value = { nombre: '', slug: '', descripcion: '', precio_mensual: 0, precio_anual: 0, max_usuarios: 1, max_productos: 50, max_clientes: 100, crm_incluido: false, reportes_avanzados: false, exportacion_datos: false, api_integraciones: false, soporte_prioritario: false }
    },
  })
}

function toggleEdit(plan: any) {
  editing.value[plan.id] = !editing.value[plan.id]
  if (editing.value[plan.id]) editForm.value[plan.id] = { ...plan }
}

function updatePlan(id: number) {
  router.put(route('saas.plans.update', id), editForm.value[id], {
    preserveScroll: true,
    onSuccess: () => { editing.value[id] = false },
  })
}

function togglePlan(id: number) {
  router.post(route('saas.plans.toggle', id), {}, { preserveScroll: true })
}

const featuresList = [
  { key: 'crm_incluido', label: 'CRM incluido', icon: Users },
  { key: 'reportes_avanzados', label: 'Reportes avanzados', icon: BarChart3 },
  { key: 'exportacion_datos', label: 'Exportación datos', icon: Database },
  { key: 'api_integraciones', label: 'API integraciones', icon: Globe },
  { key: 'soporte_prioritario', label: 'Soporte prioritario', icon: Zap },
]
</script>

<template>
  <Head title="SaaS · Planes" />
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-white">Planes de Suscripción</h1>
        <p class="text-sm text-gray-400 mt-1">Configura los planes disponibles para las empresas</p>
      </div>
      <button @click="showCreate = !showCreate"
        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-500 px-4 py-2 text-sm font-semibold text-white hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/20 transition-all cursor-pointer">
        <Plus class="h-4 w-4" /> Nuevo Plan
      </button>
    </div>

    <div v-if="showCreate" class="rounded-2xl border border-cyan-500/20 bg-[#0d111c] p-6">
      <h3 class="text-sm font-semibold text-white mb-4">Nuevo Plan</h3>
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div>
          <label class="block text-xs text-gray-400 mb-1">Nombre</label>
          <input v-model="form.nombre" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none focus:border-cyan-500/50" />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Slug</label>
          <input v-model="form.slug" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none focus:border-cyan-500/50" />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Precio Mensual (Bs)</label>
          <input v-model.number="form.precio_mensual" type="number" step="0.01" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none focus:border-cyan-500/50" />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Precio Anual (Bs)</label>
          <input v-model.number="form.precio_anual" type="number" step="0.01" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none focus:border-cyan-500/50" />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Máx. Usuarios</label>
          <input v-model.number="form.max_usuarios" type="number" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none focus:border-cyan-500/50" />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Máx. Productos</label>
          <input v-model.number="form.max_productos" type="number" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none focus:border-cyan-500/50" />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1">Máx. Clientes</label>
          <input v-model.number="form.max_clientes" type="number" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none focus:border-cyan-500/50" />
        </div>
        <div class="sm:col-span-2">
          <label class="block text-xs text-gray-400 mb-1">Descripción</label>
          <textarea v-model="form.descripcion" rows="2" class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-3 py-2 text-sm text-white outline-none focus:border-cyan-500/50 resize-none"></textarea>
        </div>
      </div>
      <div class="mt-4 flex flex-wrap gap-4">
        <label v-for="feat in featuresList" :key="feat.key" class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" v-model="form[feat.key as keyof typeof form]" class="rounded border-white/20 bg-white/5 text-cyan-500 focus:ring-cyan-500" />
          <span class="text-xs text-gray-300">{{ feat.label }}</span>
        </label>
      </div>
      <div class="mt-4 flex gap-2">
        <button @click="createPlan" class="rounded-xl bg-cyan-500 px-6 py-2 text-sm font-semibold text-white hover:bg-cyan-400 transition-all cursor-pointer">Crear Plan</button>
        <button @click="showCreate = false" class="rounded-xl bg-white/[0.05] px-4 py-2 text-sm text-gray-400 hover:text-white transition-all cursor-pointer">Cancelar</button>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      <div v-for="plan in plans" :key="plan.id"
        class="relative rounded-2xl border p-6 transition-all duration-300"
        :class="plan.activo ? 'border-white/[0.06] bg-[#0d111c] hover:border-white/20' : 'border-red-500/10 bg-[#0d111c]/50 opacity-60'">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-lg font-bold text-white">{{ plan.nombre }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ plan.descripcion || 'Sin descripción' }}</p>
          </div>
          <button @click="togglePlan(plan.id)" class="rounded-lg p-1.5 transition-colors"
            :class="plan.activo ? 'text-emerald-400 hover:bg-emerald-500/10' : 'text-gray-500 hover:bg-white/5'">
            <Shield class="h-4 w-4" />
          </button>
        </div>
        <div class="mb-4">
          <span class="text-3xl font-black text-white">{{ formatCurrency(plan.precio_mensual) }}</span>
          <span class="text-sm text-gray-500">/mes</span>
          <div class="mt-1">
            <span class="text-sm text-gray-400">{{ formatCurrency(plan.precio_anual) }}</span>
            <span class="text-xs text-gray-600">/año</span>
          </div>
        </div>
        <div class="space-y-2 text-sm">
          <div class="flex items-center gap-2 text-gray-400">
            <Users class="h-4 w-4 text-cyan-400" /> {{ plan.max_usuarios }} usuarios
          </div>
          <div class="flex items-center gap-2 text-gray-400">
            <Package class="h-4 w-4 text-violet-400" /> {{ plan.max_productos }} productos
          </div>
          <div class="flex items-center gap-2 text-gray-400">
            <Users class="h-4 w-4 text-orange-400" /> {{ plan.max_clientes }} clientes
          </div>
          <div v-if="plan.crm_incluido" class="flex items-center gap-2 text-emerald-400">
            <TrendingUp class="h-4 w-4" /> CRM incluido
          </div>
          <div v-if="plan.reportes_avanzados" class="flex items-center gap-2 text-emerald-400">
            <BarChart3 class="h-4 w-4" /> Reportes avanzados
          </div>
          <div v-if="plan.exportacion_datos" class="flex items-center gap-2 text-emerald-400">
            <Database class="h-4 w-4" /> Exportación de datos
          </div>
        </div>
        <div class="mt-4 flex gap-2">
          <button @click="toggleEdit(plan)" class="flex-1 rounded-xl bg-white/[0.05] py-2 text-sm text-gray-400 hover:text-white hover:bg-white/10 transition-all cursor-pointer">
            {{ editing[plan.id] ? 'Cancelar' : 'Editar' }}
          </button>
          <button v-if="editing[plan.id]" @click="updatePlan(plan.id)" class="flex-1 rounded-xl bg-cyan-500 py-2 text-sm font-semibold text-white hover:bg-cyan-400 transition-all cursor-pointer">
            Guardar
          </button>
        </div>
        <div v-if="editing[plan.id]" class="mt-4 space-y-3 border-t border-white/[0.04] pt-4">
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-[10px] text-gray-500">Nombre</label>
              <input v-model="editForm[plan.id].nombre" class="w-full rounded-lg border border-white/[0.08] bg-white/[0.03] px-2 py-1 text-xs text-white outline-none" />
            </div>
            <div>
              <label class="block text-[10px] text-gray-500">Precio Mensual</label>
              <input v-model.number="editForm[plan.id].precio_mensual" type="number" class="w-full rounded-lg border border-white/[0.08] bg-white/[0.03] px-2 py-1 text-xs text-white outline-none" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
