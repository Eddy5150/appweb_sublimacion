<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { ShoppingBag, ShoppingCart, Gift, Send, Phone, Mail, MessageSquare, Clock, CheckCircle, XCircle, ThumbsUp, Play, DollarSign, TrendingUp, Target } from '@lucide/vue'
import { toast } from 'vue-sonner'

const props = defineProps<{
  pending: {
    data: Array<{
      id: number
      cliente_id: number
      cliente_nombre: string | null
      cliente_estado: string | null
      action_type: string
      status: string
      payload: Record<string, any> | null
      scheduled_at: string | null
      executed_at: string | null
      result: string | null
      approved_by: string | null
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  history: {
    data: Array<{
      id: number
      cliente_id: number
      cliente_nombre: string | null
      action_type: string
      status: string
      payload: Record<string, any> | null
      executed_at: string | null
      result: string | null
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  stats: {
    pending_count: number
    total_count: number
    total_revenue_impact: number
    auto_mode: boolean
    waiting_approval: number
    by_type: Record<string, { action_type: string; total: number; sent: number }>
  }
}>()

const form = useForm({})

function actionIcon(type: string): any {
  if (type === 'descuento_automatico') return ShoppingCart
  if (type === 'envio_cortesia') return Gift
  if (type === 'ofertas_volumen') return Target
  if (type === 'llamada_asesor') return Phone
  if (type === 'email_personalizado') return Mail
  if (type === 'sms_seguimiento') return MessageSquare
  return ShoppingBag
}

function actionColor(type: string): string {
  if (type === 'descuento_automatico') return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400'
  if (type === 'envio_cortesia') return 'bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400'
  if (type === 'ofertas_volumen') return 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400'
  if (type === 'llamada_asesor') return 'bg-amber-100 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400'
  if (type === 'email_personalizado') return 'bg-rose-100 text-rose-600 dark:bg-rose-900/20 dark:text-rose-400'
  if (type === 'sms_seguimiento') return 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/20 dark:text-cyan-400'
  return 'bg-primary/10 text-primary'
}

function approveAction(action: any) {
  form.post(route('crm.sales.approve', action.id), {
    onSuccess: () => toast.success('Acción aprobada'),
  })
}

function executeAction(action: any) {
  form.post(route('crm.sales.execute', action.id), {
    onSuccess: () => toast.success('Acción ejecutada'),
  })
}

function runPipeline() {
  form.post(route('crm.sales.pipeline'), {
    onSuccess: () => toast.success('Pipeline ejecutado'),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Auto Sales CRM" />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Auto Sales · Pipeline de ventas</h1>
        <p class="text-muted-foreground">
          {{ stats.pending_count }} pendientes · {{ stats.waiting_approval }} esperando aprobación
          · ${{ stats.total_revenue_impact.toFixed(2) }} impacto · Auto: {{ stats.auto_mode ? 'Sí' : 'No' }}
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" :loading="form.processing" @click="runPipeline">
          <Play class="h-4 w-4" />
          Ejecutar pipeline
        </Button>
      </div>
    </div>

    <div v-if="stats.by_type && Object.keys(stats.by_type).length > 0" class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="item in Object.values(stats.by_type)"
        :key="item.action_type"
        class="rounded-xl border border-border bg-card p-4"
      >
        <div class="flex items-center gap-3">
          <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg" :class="actionColor(item.action_type)">
            <component :is="actionIcon(item.action_type)" class="h-4 w-4" />
          </div>
          <div class="min-w-0">
            <p class="text-sm font-medium text-foreground capitalize">{{ item.action_type.replace(/_/g, ' ') }}</p>
            <p class="text-xs text-muted-foreground">{{ item.total }} generadas · {{ item.sent }} ejecutadas</p>
          </div>
        </div>
        <div class="mt-3 h-2 rounded-full bg-muted">
          <div
            class="h-2 rounded-full bg-primary transition-all"
            :style="{ width: item.total > 0 ? `${(item.sent / item.total) * 100}%` : '0%' }"
          />
        </div>
      </div>
    </div>

    <div class="mb-6">
      <h2 class="mb-4 text-lg font-semibold text-foreground">Acciones pendientes</h2>
      <div v-if="pending.data.length === 0" class="rounded-xl border border-border bg-card p-12 text-center">
        <ShoppingBag class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
        <h3 class="mb-1 text-lg font-semibold text-foreground">Sin acciones pendientes</h3>
        <p class="text-sm text-muted-foreground">Ejecuta el pipeline de ventas para generar nuevas acciones.</p>
      </div>
      <div v-else class="space-y-3">
        <div
          v-for="action in pending.data"
          :key="action.id"
          class="rounded-xl border p-5 transition-all"
          :class="action.status === 'pending' ? 'border-amber-500/20 bg-amber-500/5' : 'border-emerald-500/20 bg-emerald-500/5'"
        >
          <div class="flex items-start gap-4">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="actionColor(action.action_type)">
              <component :is="actionIcon(action.action_type)" class="h-5 w-5" />
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <Link :href="route('clientes.show', action.cliente_id)" class="text-sm font-semibold text-foreground hover:text-primary">
                  {{ action.cliente_nombre }}
                </Link>
                <Badge variant="primary" class="text-[10px] capitalize">{{ action.action_type.replace(/_/g, ' ') }}</Badge>
                <Badge :variant="action.status === 'pending' ? 'warning' : 'success'">{{ action.status }}</Badge>
                <span v-if="action.cliente_estado" class="text-[10px] capitalize text-muted-foreground">({{ action.cliente_estado }})</span>
              </div>
              <div v-if="action.payload" class="mt-1 text-xs text-muted-foreground">
                <span v-if="action.payload.discount_percent">Descuento: {{ action.payload.discount_percent }}%</span>
                <span v-if="action.payload.min_amount" class="ml-2">Mínimo: ${{ action.payload.min_amount }}</span>
                <span v-if="action.payload.channel">Canal: {{ action.payload.channel }}</span>
                <span v-if="action.payload.message">{{ action.payload.message }}</span>
              </div>
              <div class="mt-2 flex flex-wrap items-center gap-3 text-[10px] text-muted-foreground">
                <span class="flex items-center gap-1"><Clock class="h-3 w-3" /> {{ action.created_at }}</span>
                <span v-if="action.scheduled_at">Programada: {{ action.scheduled_at }}</span>
                <span v-if="action.approved_by">Aprobada por: {{ action.approved_by }}</span>
                <span v-if="action.result">Resultado: {{ action.result }}</span>
              </div>
            </div>
            <div class="flex shrink-0 items-center gap-1">
              <Button v-if="action.status === 'pending'" variant="ghost" size="icon" title="Aprobar" @click="approveAction(action)">
                <ThumbsUp class="h-4 w-4" />
              </Button>
              <Button v-if="action.status === 'approved'" variant="ghost" size="icon" title="Ejecutar" @click="executeAction(action)">
                <Play class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </div>
      <Pagination :links="pending.links" />
    </div>

    <div>
      <h2 class="mb-4 text-lg font-semibold text-foreground">Historial de acciones</h2>
      <div v-if="history.data.length === 0" class="rounded-xl border border-border bg-card p-12 text-center">
        <Clock class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
        <h3 class="mb-1 text-lg font-semibold text-foreground">Sin historial</h3>
        <p class="text-sm text-muted-foreground">Las acciones ejecutadas aparecerán aquí.</p>
      </div>
      <div v-else class="space-y-2">
        <div
          v-for="action in history.data"
          :key="action.id"
          class="flex items-center justify-between rounded-lg border border-border bg-card p-3"
        >
          <div class="flex items-center gap-3 min-w-0">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg" :class="actionColor(action.action_type)">
              <component :is="actionIcon(action.action_type)" class="h-3.5 w-3.5" />
            </div>
            <div class="min-w-0">
              <div class="flex items-center gap-1.5">
                <Link :href="route('clientes.show', action.cliente_id)" class="truncate text-xs font-medium text-foreground hover:text-primary">
                  {{ action.cliente_nombre }}
                </Link>
                <Badge variant="primary" class="text-[8px] capitalize">{{ action.action_type.replace(/_/g, ' ') }}</Badge>
              </div>
              <div class="flex items-center gap-2 text-[10px] text-muted-foreground">
                <span>{{ action.executed_at ?? action.created_at }}</span>
                <span v-if="action.result" class="truncate max-w-[200px]">{{ action.result }}</span>
              </div>
            </div>
          </div>
          <div class="flex shrink-0 items-center gap-2">
            <span v-if="action.status === 'executed'" class="text-emerald-500"><CheckCircle class="h-3.5 w-3.5" title="Ejecutado" /></span>
            <span v-else-if="action.status === 'cancelled'" class="text-red-500"><XCircle class="h-3.5 w-3.5" title="Cancelado" /></span>
            <span v-else class="text-muted-foreground"><Clock class="h-3.5 w-3.5" /></span>
          </div>
        </div>
      </div>
      <Pagination :links="history.links" />
    </div>
  </AppLayout>
</template>
