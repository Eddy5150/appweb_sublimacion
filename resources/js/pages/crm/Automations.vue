<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Badge from '@/components/ui/Badge.vue'
import Modal from '@/components/ui/Modal.vue'
import { Plus, Settings2, ToggleLeft, ToggleRight, Trash2 } from '@lucide/vue'
import { toast } from 'vue-sonner'

const props = defineProps<{
  reglas: Array<{
    id: number
    nombre: string
    descripcion: string | null
    activa: boolean
    condicion_tipo: string
    condicion_valor: string
    accion_tipo: string
    accion_valor: string
    prioridad: number
    created_at: string
  }>
}>()

const showCreateModal = ref(false)
const editingRule = ref<number | null>(null)

const form = useForm({
  nombre: '',
  descripcion: '',
  activa: true,
  condicion_tipo: 'total_gastado_gt',
  condicion_valor: '',
  condicion_extra: null as Record<string, any> | null,
  accion_tipo: 'marcar_estado',
  accion_valor: '',
  accion_extra: null as Record<string, any> | null,
  prioridad: 0,
})

function openCreate() {
  editingRule.value = null
  form.reset()
  form.clearErrors()
  showCreateModal.value = true
}

function openEdit(regla: any) {
  editingRule.value = regla.id
  form.nombre = regla.nombre
  form.descripcion = regla.descripcion ?? ''
  form.activa = regla.activa
  form.condicion_tipo = regla.condicion_tipo
  form.condicion_valor = regla.condicion_valor
  form.accion_tipo = regla.accion_tipo
  form.accion_valor = regla.accion_valor
  form.prioridad = regla.prioridad
  form.clearErrors()
  showCreateModal.value = true
}

function save() {
  if (editingRule.value) {
    form.put(route('crm.automations.update', editingRule.value), {
      onSuccess: () => {
        showCreateModal.value = false
        toast.success('Regla actualizada')
      },
    })
  } else {
    form.post(route('crm.automations.store'), {
      onSuccess: () => {
        showCreateModal.value = false
        toast.success('Regla creada')
      },
    })
  }
}

function toggle(regla: any) {
  form.post(route('crm.automations.toggle', regla.id), {
    onSuccess: () => toast.success(regla.activa ? 'Regla desactivada' : 'Regla activada'),
  })
}

function destroy(regla: any) {
  if (!confirm(`¿Eliminar la regla "${regla.nombre}"?`)) return
  form.delete(route('crm.automations.destroy', regla.id), {
    onSuccess: () => toast.success('Regla eliminada'),
  })
}

const condicionTipos = [
  { value: 'total_gastado_gt', label: 'Total gastado >' },
  { value: 'total_gastado_gte', label: 'Total gastado ≥' },
  { value: 'pedidos_count_gte', label: 'Pedidos ≥' },
  { value: 'pedidos_count_gt', label: 'Pedidos >' },
  { value: 'sin_compras_dias_gt', label: 'Sin compras (días) >' },
  { value: 'sin_compras_dias_gte', label: 'Sin compras (días) ≥' },
  { value: 'estado_eq', label: 'Estado =' },
  { value: 'estado_in', label: 'Estado en (separado por coma)' },
  { value: 'tendencia_eq', label: 'Tendencia =' },
  { value: 'compro_ultimos_dias', label: 'Compró en últimos (días)' },
]

const accionTipos = [
  { value: 'marcar_estado', label: 'Marcar estado' },
  { value: 'generar_alerta', label: 'Generar alerta' },
  { value: 'enviar_mensaje', label: 'Enviar mensaje' },
]

function condLabel(tipo: string): string {
  return condicionTipos.find(t => t.value === tipo)?.label ?? tipo
}

function accLabel(tipo: string): string {
  return accionTipos.find(t => t.value === tipo)?.label ?? tipo
}
</script>

<template>
  <AppLayout>
    <Head title="Automatizaciones CRM" />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Automatizaciones</h1>
        <p class="text-muted-foreground">Reglas inteligentes para acciones automáticas</p>
      </div>
      <Button @click="openCreate">
        <Plus class="h-4 w-4" />
        Nueva regla
      </Button>
    </div>

    <div v-if="reglas.length === 0" class="rounded-xl border border-border bg-card p-12 text-center">
      <Settings2 class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
      <h2 class="mb-2 text-lg font-semibold text-foreground">Sin reglas de automatización</h2>
      <p class="mb-6 text-sm text-muted-foreground">Crea tu primera regla para empezar a automatizar acciones del CRM.</p>
      <Button @click="openCreate">Crear primera regla</Button>
    </div>

    <div v-else class="space-y-4">
      <div v-for="regla in reglas" :key="regla.id" class="rounded-xl border border-border bg-card p-5">
        <div class="flex items-start justify-between gap-4">
          <div class="flex-1">
            <div class="flex items-center gap-3">
              <h3 class="text-lg font-semibold text-foreground">{{ regla.nombre }}</h3>
              <Badge :variant="regla.activa ? 'success' : 'gray'">
                {{ regla.activa ? 'Activa' : 'Inactiva' }}
              </Badge>
              <span class="text-xs text-muted-foreground">Prioridad: {{ regla.prioridad }}</span>
            </div>
            <p v-if="regla.descripcion" class="mt-1 text-sm text-muted-foreground">{{ regla.descripcion }}</p>
          </div>
          <div class="flex items-center gap-2">
            <Button variant="ghost" size="icon" @click="toggle(regla)">
              <ToggleRight v-if="regla.activa" class="h-5 w-5 text-emerald-500" />
              <ToggleLeft v-else class="h-5 w-5 text-muted-foreground" />
            </Button>
            <Button variant="ghost" size="icon" @click="openEdit(regla)">
              <Settings2 class="h-4 w-4" />
            </Button>
            <Button variant="ghost" size="icon" @click="destroy(regla)">
              <Trash2 class="h-4 w-4 text-destructive" />
            </Button>
          </div>
        </div>
        <div class="mt-4 flex flex-wrap gap-4 text-sm">
          <div class="rounded-lg border border-border bg-muted/30 px-3 py-2">
            <span class="text-xs text-muted-foreground">SI</span>
            <p class="font-medium text-foreground">{{ condLabel(regla.condicion_tipo) }} <span class="text-primary">{{ regla.condicion_valor }}</span></p>
          </div>
          <div class="flex items-center text-muted-foreground">→</div>
          <div class="rounded-lg border border-border bg-muted/30 px-3 py-2">
            <span class="text-xs text-muted-foreground">ENTONCES</span>
            <p class="font-medium text-foreground">{{ accLabel(regla.accion_tipo) }} → <span class="text-primary">{{ regla.accion_valor }}</span></p>
          </div>
        </div>
      </div>
    </div>

    <Modal :open="showCreateModal" @close="showCreateModal = false">
      <div class="space-y-4 p-6">
        <h2 class="text-lg font-semibold text-foreground">{{ editingRule ? 'Editar regla' : 'Nueva regla' }}</h2>

        <div>
          <label class="mb-1 block text-sm font-medium text-foreground">Nombre</label>
          <Input v-model="form.nombre" placeholder="Ej: VIP automático" />
          <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-foreground">Descripción (opcional)</label>
          <textarea v-model="form.descripcion" rows="2" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary" placeholder="¿Cuándo se dispara esta regla?" />
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-foreground">Condición</label>
            <select v-model="form.condicion_tipo" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary">
              <option v-for="t in condicionTipos" :key="t.value" :value="t.value">{{ t.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-foreground">Valor</label>
            <Input v-model="form.condicion_valor" placeholder="Ej: 1000" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="mb-1 block text-sm font-medium text-foreground">Acción</label>
            <select v-model="form.accion_tipo" class="w-full rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary">
              <option v-for="t in accionTipos" :key="t.value" :value="t.value">{{ t.label }}</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-sm font-medium text-foreground">Valor</label>
            <Input v-model="form.accion_valor" placeholder="Ej: vip" />
          </div>
        </div>

        <div>
          <label class="mb-1 block text-sm font-medium text-foreground">Prioridad (mayor = se evalúa primero)</label>
          <Input v-model="form.prioridad" type="number" />
        </div>

        <div class="flex justify-end gap-3 pt-4">
          <Button variant="outline" @click="showCreateModal = false">Cancelar</Button>
          <Button :loading="form.processing" @click="save">{{ editingRule ? 'Actualizar' : 'Crear' }}</Button>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>
