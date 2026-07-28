<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'
import { TrendingUp, Users, DollarSign, ShoppingCart, Award, BarChart3, PieChart, Crown, Zap, Sparkles, RefreshCw, Star, Target, Trophy, AlertTriangle, Activity, UserMinus, TrendingUpDown, BrainCircuit, Bell, Flame, Gem, Play, Bot, MessageSquare, CheckCircle, XCircle, ShoppingBag, Send, Clock, ThumbsUp } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'
import { toast } from 'vue-sonner'

const props = defineProps<{
  metrics: {
    totalVentas: number
    clientesActivos: number
    clientesInactivos: number
    totalClientes: number
    totalPedidos: number
    tasaActividad: number
    promedioTicket: number
  }
  topClientes: Array<{
    id: number
    nombre: string
    total_gastado: number
    pedidos_count: number
    promedio_compra: number
    ultima_compra: string | null
    estado: string
    tendencia: string
    insight: string | null
    dias_ultima_compra: number | null
    probabilidad_retorno: string
    riesgo_score: number
  }>
  clienteMasRentable: {
    id: number
    nombre: string
    total_gastado: number
    pedidos_count: number
    promedio_compra: number
    estado: string
    tendencia: string
    insight: string | null
  } | null
  clientesEnRiesgo: Array<{
    id: number
    nombre: string
    total_gastado: number
    pedidos_count: number
    ultima_compra: string | null
    dias_ultima_compra: number | null
    riesgo_score: number
    probabilidad_retorno: string
    insight: string | null
  }>
  clientesEnCrecimiento: Array<{
    id: number
    nombre: string
    total_gastado: number
    pedidos_count: number
    tendencia: string
    insight: string | null
  }>
  segmentos: {
    vip: number
    frecuente: number
    nuevo: number
    inactivo: number
    en_riesgo: number
  }
  ingresosPorSegmento: Record<string, number>
  ventasPorMes: Array<{
    periodo: string
    total: number
  }>
  pedidosPorEstado: Array<{
    estado: string
    total: number
  }>
  aiStats?: {
    avg_score: number
    happy_count: number
    at_risk_count: number
    high_churn: number
    total_predicted: number
    total_ltv: number
  }
  alertasUrgentes?: Array<{
    id: number
    cliente_id: number | null
    cliente_nombre: string | null
    tipo: string
    titulo: string
    mensaje: string
    created_at: string
  }>
  clientesCalientes?: Array<{
    cliente_id: number
    cliente_nombre: string | null
    purchase_probability: number
    estimated_next_purchase: string | null
    emotional_state: string
    ltv_estimated: number
    ai_score: number
  }>
  candidatosVip?: Array<{
    cliente_id: number
    cliente_nombre: string | null
    total_gastado: number
    pedidos_count: number
    ltv_estimated: number
    ai_score: number
    churn_risk: string
  }>
  automationResult: {
    reenganche: number
    warnings: number
    vip_upgrades: number
    caidas: number
  }
  agentClientsToContact?: Array<{
    id: number
    cliente_id: number
    cliente_nombre: string | null
    cliente_telefono: string | null
    cliente_estado: string | null
    mensaje: string
    accion_recomendada: string
    created_at: string
  }>
  agentStats?: {
    pending_count: number
    sent_count: number
    total_processed: number
    action_breakdown: Record<string, number>
    feedback_count: number
    success_rate: number | null
  }
  salesStats?: {
    pending_count: number
    sent_count: number
    total_count: number
    by_type: Record<string, { action_type: string, total: number, sent: number }>
    total_revenue_impact: number
    auto_mode: boolean
    waiting_approval: number
  }
  salesPending?: Array<{
    id: number
    cliente_id: number
    cliente_nombre: string | null
    cliente_estado: string | null
    action_type: string
    status: string
    scheduled_at: string | null
    created_at: string
  }>
}>()

const form = useForm({})

function badgeVariant(estado: string): string {
  if (estado === 'vip') return 'warning'
  if (estado === 'frecuente') return 'success'
  if (estado === 'inactivo') return 'gray'
  if (estado === 'en_riesgo') return 'danger'
  return 'primary'
}

function tendenciaIcon(t: string): string {
  if (t === 'sube') return '📈'
  if (t === 'baja') return '📉'
  return '➡️'
}

function maxVentas(): number {
  return Math.max(...props.ventasPorMes.map(v => v.total), 1)
}

function maxPedidos(): number {
  return Math.max(...props.pedidosPorEstado.map(p => p.total), 1)
}

function maxSegmento(): number {
  return Math.max(...Object.values(props.segmentos), 1)
}

function scoreColor(score: number): string {
  if (score >= 70) return 'text-emerald-500'
  if (score >= 40) return 'text-amber-500'
  return 'text-red-500'
}

function probaBadge(proba: string): string {
  if (proba === 'alta') return 'success'
  if (proba === 'media') return 'warning'
  return 'danger'
}

function clasificar() {
  form.post(route('clientes.clasificar'), {
    onSuccess: () => toast.success('Clientes clasificados correctamente'),
  })
}

function runAutomation() {
  form.post(route('crm.automation.run'), {
    onSuccess: () => toast.success('Automatización ejecutada'),
  })
}

function runAgent() {
  form.post(route('crm.ai.agent.run'), {
    onSuccess: () => toast.success('Agente IA ejecutado'),
  })
}

function runSalesPipeline() {
  form.post(route('crm.sales.pipeline'), {
    onSuccess: () => toast.success('Pipeline de ventas ejecutado'),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="CRM Inteligente" />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground">CRM Inteligente</h1>
        <p class="text-muted-foreground">Analítica predictiva · Segmentación dinámica · Insights automáticos</p>
      </div>
      <Button variant="outline" :loading="form.processing" @click="clasificar">
        <RefreshCw class="h-4 w-4" />
        Reclasificar segmentos
      </Button>
    </div>

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
      <div class="rounded-xl border border-red-500/20 bg-card p-4">
        <div class="mb-3 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <Bell class="h-4 w-4 text-red-500" />
            <h2 class="text-sm font-semibold text-foreground">Alertas urgentes</h2>
          </div>
          <Link v-if="alertasUrgentes && alertasUrgentes.length > 0" :href="route('crm.alerts')" class="text-xs text-primary hover:underline">
            Ver todas
          </Link>
        </div>
        <div v-if="alertasUrgentes && alertasUrgentes.length > 0" class="space-y-2">
          <div v-for="alerta in alertasUrgentes.slice(0, 5)" :key="alerta.id" class="rounded-lg border border-red-500/10 bg-red-500/5 p-2.5">
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-medium text-foreground">{{ alerta.titulo }}</p>
                <p class="mt-0.5 line-clamp-2 text-[11px] text-muted-foreground">{{ alerta.mensaje }}</p>
              </div>
              <Badge variant="danger" class="shrink-0 text-[9px]">{{ alerta.tipo }}</Badge>
            </div>
          </div>
        </div>
        <p v-else class="text-xs text-muted-foreground">No hay alertas pendientes.</p>
        <div class="mt-3 flex items-center justify-between border-t border-border pt-2 text-xs text-muted-foreground">
          <span>{{ alertasUrgentes?.length ?? 0 }} sin leer</span>
          <Button variant="ghost" size="sm" class="h-6 text-xs" @click="runAutomation">
            <Play class="mr-1 h-3 w-3" />
            Ejecutar
          </Button>
        </div>
      </div>

      <div class="rounded-xl border border-orange-500/20 bg-card p-4">
        <div class="mb-3 flex items-center gap-2">
          <Flame class="h-4 w-4 text-orange-500" />
          <h2 class="text-sm font-semibold text-foreground">Clientes calientes</h2>
        </div>
        <div v-if="clientesCalientes && clientesCalientes.length > 0" class="space-y-2">
          <div v-for="cliente in clientesCalientes" :key="cliente.cliente_id" class="flex items-center justify-between rounded-lg border border-orange-500/10 bg-orange-500/5 p-2.5">
            <div class="min-w-0 flex-1">
              <Link :href="route('clientes.show', cliente.cliente_id)" class="truncate text-xs font-medium text-foreground hover:text-primary">
                {{ cliente.cliente_nombre }}
              </Link>
              <div class="flex items-center gap-2 text-[11px] text-muted-foreground">
                <span>{{ cliente.purchase_probability }}% prob.</span>
                <span v-if="cliente.estimated_next_purchase">próx. {{ cliente.estimated_next_purchase }}</span>
              </div>
            </div>
            <div class="shrink-0 text-right text-[11px]">
              <span class="font-medium text-foreground">{{ formatCurrency(cliente.ltv_estimated) }}</span>
              <p class="text-muted-foreground">LTV</p>
            </div>
          </div>
        </div>
        <p v-else class="text-xs text-muted-foreground">Ejecuta la automatización para detectar clientes calientes.</p>
      </div>

      <div class="rounded-xl border border-amber-500/20 bg-card p-4">
        <div class="mb-3 flex items-center gap-2">
          <Gem class="h-4 w-4 text-amber-500" />
          <h2 class="text-sm font-semibold text-foreground">Candidatos VIP</h2>
        </div>
        <div v-if="candidatosVip && candidatosVip.length > 0" class="space-y-2">
          <div v-for="candidato in candidatosVip" :key="candidato.cliente_id" class="flex items-center justify-between rounded-lg border border-amber-500/10 bg-amber-500/5 p-2.5">
            <div class="min-w-0 flex-1">
              <Link :href="route('clientes.show', candidato.cliente_id)" class="truncate text-xs font-medium text-foreground hover:text-primary">
                {{ candidato.cliente_nombre }}
              </Link>
              <div class="flex items-center gap-2 text-[11px] text-muted-foreground">
                <span>{{ formatCurrency(candidato.total_gastado) }}</span>
                <span>{{ candidato.pedidos_count }} pedidos</span>
              </div>
            </div>
            <div class="shrink-0 text-right text-[11px]">
              <Badge :variant="candidato.churn_risk === 'baja' ? 'success' : candidato.churn_risk === 'media' ? 'warning' : 'danger'" class="text-[9px]">
                Score {{ candidato.ai_score }}
              </Badge>
            </div>
          </div>
        </div>
        <p v-else class="text-xs text-muted-foreground">No se detectaron candidatos VIP aún.</p>
      </div>
    </div>

    <div class="mb-6 rounded-xl border border-purple-500/20 bg-card p-5">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30">
            <Bot class="h-5 w-5" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-sm font-semibold text-foreground">AI Agent · Próximas acciones</h2>
              <span class="rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-semibold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">NIVEL 9</span>
            </div>
            <p class="text-xs text-muted-foreground">
              {{ agentStats?.pending_count ?? 0 }} recomendaciones pendientes · {{ agentStats?.total_processed ?? 0 }} clientes procesados
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <Link v-if="agentStats && agentStats.pending_count > 0" :href="route('crm.ai.agent.recommendations')" class="text-xs font-medium text-purple-500 hover:text-purple-400">
            <MessageSquare class="mr-1 inline-block h-3 w-3" />
            Ver todas
          </Link>
          <Button variant="outline" size="sm" :loading="form.processing" class="h-8 text-xs" @click="runAgent">
            <Play class="mr-1 h-3 w-3" />
            Analizar clientes
          </Button>
        </div>
      </div>

      <div v-if="agentClientsToContact && agentClientsToContact.length > 0" class="mt-4 space-y-2">
        <div v-for="rec in agentClientsToContact" :key="rec.id" class="flex items-start gap-3 rounded-lg border border-purple-500/10 bg-purple-500/5 p-3">
          <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-purple-100 text-xs font-bold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
            {{ rec.cliente_nombre?.charAt(0) ?? '?' }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
              <Link :href="route('clientes.show', rec.cliente_id)" class="truncate text-sm font-medium text-foreground hover:text-primary">
                {{ rec.cliente_nombre }}
              </Link>
              <Badge variant="warning" class="text-[9px]">{{ rec.accion_recomendada.replace('_', ' ') }}</Badge>
              <span v-if="rec.cliente_estado" class="text-[10px] text-muted-foreground capitalize">{{ rec.cliente_estado }}</span>
            </div>
            <p class="mt-1 text-xs text-muted-foreground italic">"{{ rec.mensaje }}"</p>
          </div>
        </div>
      </div>
      <div v-else class="mt-4 text-xs text-muted-foreground">
        Ejecuta "Analizar clientes" para obtener recomendaciones del agente IA.
      </div>
    </div>

    <div v-if="salesStats" class="mb-6 rounded-xl border border-emerald-500/20 bg-card p-5">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30">
            <ShoppingBag class="h-5 w-5" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-sm font-semibold text-foreground">Auto Sales · Pipeline de ventas</h2>
              <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">NIVEL 10</span>
            </div>
            <p class="text-xs text-muted-foreground">
              {{ salesStats.pending_count }} pendientes · {{ salesStats.sent_count }} enviadas · ${{ salesStats.total_revenue_impact.toFixed(2) }} impacto
              <span v-if="salesStats.auto_mode" class="ml-2 text-emerald-500">[Auto]</span>
              <span v-else class="ml-2 text-amber-500">[Requiere aprobación]</span>
            </p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="route('crm.sales.actions')" class="text-xs font-medium text-emerald-500 hover:text-emerald-400">
            Gestionar
          </Link>
          <Button variant="outline" size="sm" :loading="form.processing" class="h-8 text-xs" @click="runSalesPipeline">
            <Send class="mr-1 h-3 w-3" />
            Ejecutar pipeline
          </Button>
        </div>
      </div>

      <div v-if="salesPending && salesPending.length > 0" class="mt-4 space-y-2">
        <div v-for="action in salesPending" :key="action.id" class="flex items-center justify-between rounded-lg border border-emerald-500/10 bg-emerald-500/5 p-2.5">
          <div class="flex items-center gap-2 min-w-0">
            <Clock v-if="action.status === 'pending'" class="h-3.5 w-3.5 shrink-0 text-amber-500" />
            <ThumbsUp v-else class="h-3.5 w-3.5 shrink-0 text-emerald-500" />
            <div class="min-w-0">
              <Link :href="route('clientes.show', action.cliente_id)" class="truncate text-xs font-medium text-foreground hover:text-primary">
                {{ action.cliente_nombre }}
              </Link>
              <span v-if="action.cliente_estado" class="ml-1.5 text-[10px] text-muted-foreground capitalize">({{ action.cliente_estado }})</span>
            </div>
          </div>
          <div class="flex shrink-0 items-center gap-2">
            <Badge variant="primary" class="text-[9px]">{{ action.action_type.replace('_', ' ') }}</Badge>
            <span class="text-[10px] text-muted-foreground">{{ action.scheduled_at ?? action.created_at }}</span>
          </div>
        </div>
      </div>
      <div v-else class="mt-4 text-xs text-muted-foreground">
        Ejecuta "Pipeline de ventas" para generar acciones automáticas.
      </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-xl border border-border bg-card p-5">
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
            <DollarSign class="h-5 w-5" />
          </div>
          <div>
            <p class="text-xs font-medium text-muted-foreground">Ventas totales</p>
            <p class="text-xl font-bold text-foreground">{{ formatCurrency(metrics.totalVentas) }}</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-border bg-card p-5">
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
            <Activity class="h-5 w-5" />
          </div>
          <div>
            <p class="text-xs font-medium text-muted-foreground">Activos / Inactivos</p>
            <p class="text-xl font-bold text-foreground">{{ metrics.clientesActivos }}<span class="text-sm font-normal text-muted-foreground"> / {{ metrics.clientesInactivos }}</span></p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-border bg-card p-5">
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400">
            <TrendingUp class="h-5 w-5" />
          </div>
          <div>
            <p class="text-xs font-medium text-muted-foreground">Ticket promedio</p>
            <p class="text-xl font-bold text-foreground">{{ formatCurrency(metrics.promedioTicket) }}</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-border bg-card p-5">
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400">
            <TrendingUpDown class="h-5 w-5" />
          </div>
          <div>
            <p class="text-xs font-medium text-muted-foreground">Tasa actividad</p>
            <p class="text-xl font-bold text-foreground">{{ metrics.tasaActividad }}%</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="aiStats && aiStats.total_predicted > 0" class="mb-6 rounded-xl border border-purple-500/20 bg-gradient-to-r from-purple-500/5 to-purple-500/0 p-5">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
            <BrainCircuit class="h-5 w-5" />
          </div>
          <div>
            <div class="flex items-center gap-2">
              <h2 class="text-sm font-semibold text-foreground">IA CRM activa</h2>
              <span class="rounded-full bg-purple-100 px-2 py-0.5 text-[10px] font-semibold text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">NUEVO</span>
            </div>
            <p class="text-xs text-muted-foreground">Predicciones inteligentes · Scoring automático · LTV estimado</p>
          </div>
        </div>
        <Link :href="route('crm.ai')" class="text-xs font-medium text-purple-500 hover:text-purple-400">
          Ver dashboard IA →
        </Link>
      </div>
      <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="text-center">
          <p class="text-lg font-bold text-foreground">{{ aiStats.avg_score }}</p>
          <p class="text-xs text-muted-foreground">Score IA promedio</p>
        </div>
        <div class="text-center">
          <p class="text-lg font-bold text-emerald-500">{{ aiStats.happy_count }}</p>
          <p class="text-xs text-muted-foreground">Clientes felices 😊</p>
        </div>
        <div class="text-center">
          <p class="text-lg font-bold text-red-500">{{ aiStats.at_risk_count }}</p>
          <p class="text-xs text-muted-foreground">En riesgo ⚠️</p>
        </div>
        <div class="text-center">
          <p class="text-lg font-bold text-foreground">{{ formatCurrency(aiStats.total_ltv) }}</p>
          <p class="text-xs text-muted-foreground">LTV total estimado</p>
        </div>
      </div>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-4">
      <div class="rounded-xl border border-border bg-card p-6 lg:col-span-1">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-foreground">Segmentación</h2>
          <BarChart3 class="h-5 w-5 text-muted-foreground" />
        </div>
        <p class="mb-4 text-xs text-muted-foreground">VIP &gt; 1.000 Bs · Frecuente ≥ 3 · Inactivo &gt; 30d · Riesgo &gt; 60d</p>
        <div class="space-y-4">
          <div v-for="(count, key) in segmentos" :key="key">
            <div class="mb-1.5 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <component :is="key === 'vip' ? Crown : key === 'frecuente' ? Zap : key === 'inactivo' ? UserMinus : key === 'en_riesgo' ? AlertTriangle : Sparkles" class="h-4 w-4"
                  :class="key === 'vip' ? 'text-amber-500' : key === 'frecuente' ? 'text-emerald-500' : key === 'inactivo' ? 'text-gray-500' : key === 'en_riesgo' ? 'text-red-500' : 'text-blue-500'"
                />
                <span class="text-sm font-medium capitalize text-foreground">{{ key.replace('_', ' ') }}</span>
              </div>
              <span class="text-sm font-bold text-foreground">{{ count }}</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-muted">
              <div class="h-full rounded-full transition-all"
                :class="key === 'vip' ? 'bg-amber-500' : key === 'frecuente' ? 'bg-emerald-500' : key === 'inactivo' ? 'bg-gray-400' : key === 'en_riesgo' ? 'bg-red-500' : 'bg-blue-500'"
                :style="{ width: `${(count / maxSegmento()) * 100}%` }" />
            </div>
          </div>
        </div>

        <div v-if="Object.keys(ingresosPorSegmento).length > 0" class="mt-6 border-t border-border pt-4">
          <h3 class="mb-3 text-sm font-semibold text-foreground">Ingresos por segmento</h3>
          <div class="space-y-2">
            <div v-for="(total, segmento) in ingresosPorSegmento" :key="segmento" class="flex items-center justify-between text-sm">
              <span class="capitalize text-muted-foreground">{{ segmento.replace('_', ' ') }}</span>
              <span class="font-medium text-foreground">{{ formatCurrency(total) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-border bg-card p-6 lg:col-span-3">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-foreground">Ventas por mes</h2>
          <TrendingUp class="h-5 w-5 text-muted-foreground" />
        </div>
        <div v-if="ventasPorMes.length > 0" class="flex items-end gap-1.5" style="height: 160px">
          <div
            v-for="item in ventasPorMes"
            :key="item.periodo"
            class="flex flex-1 flex-col items-center justify-end gap-1"
          >
            <span class="text-[10px] font-medium text-muted-foreground whitespace-nowrap">{{ formatCurrency(item.total) }}</span>
            <div
              class="w-full rounded-t-sm bg-gradient-to-t from-primary/60 to-primary transition-all hover:from-primary hover:to-primary/80"
              :style="{ height: `${(item.total / maxVentas()) * 100}%` }"
            />
            <span class="text-[10px] text-muted-foreground">{{ item.periodo.slice(-2) }}/{{ item.periodo.slice(0, 4) }}</span>
          </div>
        </div>
        <p v-else class="text-sm text-muted-foreground">No hay datos de ventas.</p>
      </div>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-3">
      <div v-if="clienteMasRentable" class="rounded-xl border border-amber-500/20 bg-gradient-to-br from-amber-50/5 to-amber-950/5 p-6">
        <div class="mb-2 flex items-center gap-2">
          <Trophy class="h-5 w-5 text-amber-500" />
          <h2 class="text-lg font-semibold text-foreground">Cliente más rentable</h2>
        </div>
        <div class="mt-4">
          <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-lg font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">
              {{ clienteMasRentable.nombre.charAt(0) }}
            </div>
            <div>
              <Link :href="route('clientes.show', clienteMasRentable.id)" class="text-lg font-bold text-foreground hover:text-primary">
                {{ clienteMasRentable.nombre }}
              </Link>
              <div class="mt-1 flex items-center gap-2">
                <Badge :variant="badgeVariant(clienteMasRentable.estado)">{{ clienteMasRentable.estado }}</Badge>
                <span v-if="clienteMasRentable.insight" class="text-xs text-muted-foreground">{{ clienteMasRentable.insight }}</span>
              </div>
            </div>
          </div>
          <div class="mt-6 grid grid-cols-3 gap-4">
            <div>
              <p class="text-xs text-muted-foreground">Total gastado</p>
              <p class="text-lg font-bold text-foreground">{{ formatCurrency(clienteMasRentable.total_gastado) }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">Pedidos</p>
              <p class="text-lg font-bold text-foreground">{{ clienteMasRentable.pedidos_count }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">Promedio</p>
              <p class="text-lg font-bold text-foreground">{{ formatCurrency(clienteMasRentable.promedio_compra) }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-border bg-card p-6">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-foreground">En riesgo</h2>
          <AlertTriangle class="h-5 w-5 text-red-500" />
        </div>
        <div v-if="clientesEnRiesgo.length > 0" class="space-y-3">
          <div v-for="cliente in clientesEnRiesgo" :key="cliente.id" class="flex items-center gap-3 rounded-lg border border-red-500/10 bg-red-500/5 p-3">
            <div class="min-w-0 flex-1">
              <Link :href="route('clientes.show', cliente.id)" class="truncate text-sm font-medium text-foreground hover:text-primary">
                {{ cliente.nombre }}
              </Link>
              <div class="mt-0.5 flex items-center gap-2 text-xs text-muted-foreground">
                <span>{{ cliente.dias_ultima_compra ?? '?' }} días sin comprar</span>
                <span>{{ cliente.pedidos_count }} pedidos</span>
              </div>
            </div>
            <div class="text-right text-xs">
              <span v-if="cliente.probabilidad_retorno" class="font-medium"
                :class="probaBadge(cliente.probabilidad_retorno) === 'success' ? 'text-emerald-500' : probaBadge(cliente.probabilidad_retorno) === 'warning' ? 'text-amber-500' : 'text-red-500'"
              >
                {{ cliente.probabilidad_retorno }}
              </span>
              <p class="text-muted-foreground">{{ formatCurrency(cliente.total_gastado) }}</p>
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-muted-foreground">No hay clientes en riesgo.</p>
      </div>

      <div class="rounded-xl border border-border bg-card p-6">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-foreground">En crecimiento</h2>
          <TrendingUp class="h-5 w-5 text-emerald-500" />
        </div>
        <div v-if="clientesEnCrecimiento.length > 0" class="space-y-3">
          <div v-for="cliente in clientesEnCrecimiento" :key="cliente.id" class="flex items-center gap-3 rounded-lg border border-emerald-500/10 bg-emerald-500/5 p-3">
            <div class="min-w-0 flex-1">
              <Link :href="route('clientes.show', cliente.id)" class="truncate text-sm font-medium text-foreground hover:text-primary">
                {{ cliente.nombre }}
              </Link>
              <div class="mt-0.5 flex items-center gap-2 text-xs text-muted-foreground">
                <span>{{ cliente.pedidos_count }} pedidos</span>
                <span>{{ formatCurrency(cliente.total_gastado) }}</span>
              </div>
            </div>
            <span class="text-sm">{{ cliente.insight }}</span>
          </div>
        </div>
        <p v-else class="text-sm text-muted-foreground">No hay clientes en crecimiento.</p>
      </div>
    </div>

    <div class="mb-6 rounded-xl border border-border bg-card p-6">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-foreground">Top 5 clientes</h2>
        <Award class="h-5 w-5 text-muted-foreground" />
      </div>
      <div v-if="topClientes.length > 0" class="overflow-hidden rounded-lg border border-border">
        <table class="w-full">
          <thead>
            <tr class="border-b border-border bg-muted/50">
              <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">#</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Cliente</th>
              <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Pedidos</th>
              <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">Total</th>
              <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Tendencia</th>
              <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Score</th>
              <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Retorno</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Insight</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(cliente, index) in topClientes" :key="cliente.id" class="border-b border-border last:border-0 hover:bg-muted/30">
              <td class="px-4 py-3 text-center">
                <span class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold"
                  :class="index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-muted text-muted-foreground'"
                >{{ index + 1 }}</span>
              </td>
              <td class="px-4 py-3">
                <Link :href="route('clientes.show', cliente.id)" class="font-medium text-foreground hover:text-primary">{{ cliente.nombre }}</Link>
                <Badge :variant="badgeVariant(cliente.estado)" class="ml-2">{{ cliente.estado }}</Badge>
              </td>
              <td class="px-4 py-3 text-center text-sm text-foreground">{{ cliente.pedidos_count }}</td>
              <td class="px-4 py-3 text-right text-sm font-semibold text-foreground">{{ formatCurrency(cliente.total_gastado) }}</td>
              <td class="px-4 py-3 text-center text-lg">{{ tendenciaIcon(cliente.tendencia) }}</td>
              <td class="px-4 py-3 text-center">
                <span class="text-sm font-bold" :class="scoreColor(cliente.riesgo_score)">{{ cliente.riesgo_score }}</span>
              </td>
              <td class="px-4 py-3 text-center">
                <Badge :variant="probaBadge(cliente.probabilidad_retorno)">{{ cliente.probabilidad_retorno }}</Badge>
              </td>
              <td class="px-4 py-3 text-sm text-muted-foreground">{{ cliente.insight ?? '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else class="text-sm text-muted-foreground">No hay clientes con pedidos.</p>
    </div>

    <div class="grid gap-6">
      <div class="rounded-xl border border-border bg-card p-6">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-foreground">Pedidos por estado</h2>
          <PieChart class="h-5 w-5 text-muted-foreground" />
        </div>
        <div v-if="pedidosPorEstado.length > 0" class="space-y-4">
          <div v-for="item in pedidosPorEstado" :key="item.estado">
            <div class="mb-1.5 flex items-center justify-between">
              <span class="text-sm capitalize text-foreground">{{ item.estado.replace('_', ' ') }}</span>
              <span class="text-sm font-medium text-foreground">{{ item.total }}</span>
            </div>
            <div class="h-2.5 overflow-hidden rounded-full bg-muted">
              <div class="h-full rounded-full transition-all"
                :class="item.estado === 'entregado' ? 'bg-emerald-500' : item.estado === 'listo' ? 'bg-blue-500' : item.estado === 'en_proceso' ? 'bg-amber-500' : 'bg-gray-400'"
                :style="{ width: `${(item.total / maxPedidos()) * 100}%` }" />
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-muted-foreground">No hay pedidos registrados.</p>
      </div>
    </div>
  </AppLayout>
</template>
