<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'
import { BrainCircuit, TrendingUp, DollarSign, Users, RefreshCw, AlertTriangle, Crown, Heart, TrendingDown, Sparkles, Zap, Target, Calendar } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'
import { toast } from 'vue-sonner'

const props = defineProps<{
  forecast: {
    estimated_7d: number
    estimated_monthly: number
    avg_daily: number
    trend: string
    confidence: string
    daily_breakdown: Array<{ date: string; day_name: string; estimated: number }>
    days_analyzed: number
  }
  topClients: Array<{
    cliente_id: number
    cliente_nombre: string
    ai_score: number
    purchase_probability: number
    ltv_estimated: number
    estimated_next_purchase: string | null
    emotional_state: string
    churn_risk: string
  }>
  clientesAI: Array<{
    cliente_id: number
    cliente_nombre: string
    ai_score: number
    purchase_probability: number
    estimated_next_purchase: string | null
    emotional_state: string
    future_trend: string
    ltv_estimated: number
    churn_risk: string
  }>
  stats: {
    total_predictions: number
    avg_ai_score: number
    avg_probability: number
    high_churn_count: number
    happy_count: number
    at_risk_count: number
    trending_up: number
    total_ltv: number
  }
}>()

const form = useForm({})

function procesar() {
  form.post(route('crm.ai.procesar'), {
    onSuccess: () => toast.success('Predicciones IA actualizadas'),
  })
}

function emotionIcon(state: string): any {
  if (state === 'feliz') return Heart
  if (state === 'en_riesgo') return AlertTriangle
  return Sparkles
}

function emotionColor(state: string): string {
  if (state === 'feliz') return 'text-emerald-500'
  if (state === 'en_riesgo') return 'text-red-500'
  return 'text-amber-500'
}

function emotionBadge(state: string): string {
  if (state === 'feliz') return 'success'
  if (state === 'en_riesgo') return 'danger'
  return 'warning'
}

function riskBadge(risk: string): string {
  if (risk === 'baja') return 'success'
  if (risk === 'media') return 'warning'
  return 'danger'
}

function trendIcon(trend: string): string {
  if (trend === 'sube') return '📈'
  if (trend === 'baja') return '📉'
  return '➡️'
}

function scoreColor(score: number): string {
  if (score >= 70) return 'text-emerald-500'
  if (score >= 40) return 'text-amber-500'
  return 'text-red-500'
}

function confidenceLabel(c: string): string {
  if (c === 'alta') return 'Alta'
  if (c === 'media') return 'Media'
  return 'Baja'
}

function confidenceBadge(c: string): string {
  if (c === 'alta') return 'success'
  if (c === 'media') return 'warning'
  return 'gray'
}

function maxEstimated(): number {
  return Math.max(...props.forecast.daily_breakdown.map(d => d.estimated), 1)
}
</script>

<template>
  <AppLayout>
    <Head title="IA CRM" />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="flex items-center gap-2 text-2xl font-bold text-foreground">
          <BrainCircuit class="h-7 w-7 text-purple-500" />
          Inteligencia Artificial CRM
        </h1>
        <p class="text-muted-foreground">Predicciones · Scoring · Recomendaciones inteligentes</p>
      </div>
      <Button :loading="form.processing" @click="procesar">
        <RefreshCw class="h-4 w-4" />
        Recalcular IA
      </Button>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-xl border border-border bg-card p-5">
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-purple-50 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400">
            <BrainCircuit class="h-5 w-5" />
          </div>
          <div>
            <p class="text-xs font-medium text-muted-foreground">Score IA promedio</p>
            <p class="text-xl font-bold text-foreground">{{ stats.avg_ai_score }}<span class="text-sm font-normal text-muted-foreground">/100</span></p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-border bg-card p-5">
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400">
            <Heart class="h-5 w-5" />
          </div>
          <div>
            <p class="text-xs font-medium text-muted-foreground">Clientes felices</p>
            <p class="text-xl font-bold text-foreground">{{ stats.happy_count }}<span class="text-sm font-normal text-muted-foreground"> / {{ stats.total_predictions }}</span></p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-border bg-card p-5">
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400">
            <AlertTriangle class="h-5 w-5" />
          </div>
          <div>
            <p class="text-xs font-medium text-muted-foreground">En riesgo / Abandono</p>
            <p class="text-xl font-bold text-foreground">{{ stats.at_risk_count }}<span class="text-sm font-normal text-muted-foreground"> / {{ stats.high_churn_count }}</span></p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-border bg-card p-5">
        <div class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400">
            <TrendingUp class="h-5 w-5" />
          </div>
          <div>
            <p class="text-xs font-medium text-muted-foreground">En crecimiento</p>
            <p class="text-xl font-bold text-foreground">{{ stats.trending_up }}<span class="text-sm font-normal text-muted-foreground"> clientes</span></p>
          </div>
        </div>
      </div>
    </div>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
      <div class="rounded-xl border border-border bg-card p-6">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-foreground">Pronóstico de ingresos</h2>
          <DollarSign class="h-5 w-5 text-muted-foreground" />
        </div>
        <div class="grid grid-cols-3 gap-4">
          <div>
            <p class="text-xs text-muted-foreground">Próximos 7 días</p>
            <p class="text-xl font-bold text-foreground">{{ formatCurrency(forecast.estimated_7d) }}</p>
          </div>
          <div>
            <p class="text-xs text-muted-foreground">Estimado mensual</p>
            <p class="text-xl font-bold text-foreground">{{ formatCurrency(forecast.estimated_monthly) }}</p>
          </div>
          <div>
            <p class="text-xs text-muted-foreground">Media diaria</p>
            <p class="text-xl font-bold text-foreground">{{ formatCurrency(forecast.avg_daily) }}</p>
          </div>
        </div>
        <div class="mt-4 flex items-center gap-3 text-sm">
          <Badge :variant="confidenceBadge(forecast.confidence)">Confianza {{ confidenceLabel(forecast.confidence) }}</Badge>
          <span class="text-muted-foreground">{{ forecast.days_analyzed }} días analizados</span>
          <span v-if="forecast.trend === 'up'" class="text-emerald-500">📈 Tendencia al alza</span>
          <span v-else-if="forecast.trend === 'down'" class="text-red-500">📉 Tendencia a la baja</span>
          <span v-else class="text-muted-foreground">➡️ Tendencia estable</span>
        </div>
        <div v-if="forecast.daily_breakdown.length > 0" class="mt-4 flex items-end gap-1.5" style="height: 100px">
          <div v-for="day in forecast.daily_breakdown" :key="day.date" class="group relative flex flex-1 flex-col items-center justify-end">
            <div class="absolute bottom-full mb-1 hidden rounded bg-muted px-2 py-1 text-xs text-foreground group-hover:block whitespace-nowrap">
              {{ day.day_name }}<br>{{ formatCurrency(day.estimated) }}
            </div>
            <div
              class="w-full rounded-t-sm bg-gradient-to-t from-purple-500/60 to-purple-400 transition-all hover:from-purple-500 hover:to-purple-400"
              :style="{ height: `${(day.estimated / maxEstimated()) * 100}%` }"
            />
            <span class="mt-1 text-[10px] text-muted-foreground">{{ day.day_name.slice(0, 3) }}</span>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-border bg-card p-6">
        <div class="mb-4 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-foreground">Clientes con mayor potencial</h2>
          <Target class="h-5 w-5 text-muted-foreground" />
        </div>
        <div v-if="topClients.length > 0" class="space-y-3">
          <div v-for="cliente in topClients" :key="cliente.cliente_id" class="flex items-center gap-3 rounded-lg border border-border bg-muted/20 p-3">
            <div
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold"
              :class="cliente.emotional_state === 'feliz' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-muted text-muted-foreground'"
            >
              {{ cliente.cliente_nombre?.charAt(0) }}
            </div>
            <div class="min-w-0 flex-1">
              <Link :href="route('clientes.show', cliente.cliente_id)" class="truncate text-sm font-medium text-foreground hover:text-primary">
                {{ cliente.cliente_nombre }}
              </Link>
              <div class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                <Badge :variant="emotionBadge(cliente.emotional_state)">{{ cliente.emotional_state }}</Badge>
                <Badge :variant="riskBadge(cliente.churn_risk)">{{ cliente.churn_risk }}</Badge>
              </div>
            </div>
            <div class="text-right">
              <p class="text-sm font-bold" :class="scoreColor(cliente.ai_score)">{{ cliente.ai_score }}%</p>
              <p class="text-xs text-muted-foreground">Prob. {{ cliente.purchase_probability }}%</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-semibold text-foreground">{{ formatCurrency(cliente.ltv_estimated) }}</p>
              <p class="text-xs text-muted-foreground">LTV estimado</p>
            </div>
          </div>
        </div>
        <p v-else class="text-sm text-muted-foreground">Procesa las predicciones IA para ver clientes con potencial.</p>
      </div>
    </div>

    <div class="mb-6 rounded-xl border border-border bg-card p-6">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-foreground">Scoring completo de clientes</h2>
        <BrainCircuit class="h-5 w-5 text-muted-foreground" />
      </div>
      <div v-if="clientesAI.length > 0" class="overflow-hidden rounded-lg border border-border">
        <table class="w-full">
          <thead>
            <tr class="border-b border-border bg-muted/50">
              <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Cliente</th>
              <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Score IA</th>
              <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Prob. recompra</th>
              <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Estado emocional</th>
              <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Riesgo</th>
              <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Tendencia</th>
              <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">Próxima compra</th>
              <th class="px-4 py-3 text-right text-sm font-medium text-muted-foreground">LTV estimado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="c in clientesAI" :key="c.cliente_id" class="border-b border-border last:border-0 hover:bg-muted/30">
              <td class="px-4 py-3">
                <Link :href="route('clientes.show', c.cliente_id)" class="font-medium text-foreground hover:text-primary">
                  {{ c.cliente_nombre }}
                </Link>
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-2">
                  <div class="h-2 w-16 overflow-hidden rounded-full bg-muted">
                    <div
                      class="h-full rounded-full transition-all"
                      :class="c.ai_score >= 70 ? 'bg-emerald-500' : c.ai_score >= 40 ? 'bg-amber-500' : 'bg-red-500'"
                      :style="{ width: `${c.ai_score}%` }"
                    />
                  </div>
                  <span class="text-sm font-bold" :class="scoreColor(c.ai_score)">{{ c.ai_score }}</span>
                </div>
              </td>
              <td class="px-4 py-3 text-center text-sm font-medium text-foreground">{{ c.purchase_probability }}%</td>
              <td class="px-4 py-3 text-center">
                <Badge :variant="emotionBadge(c.emotional_state)">{{ c.emotional_state }}</Badge>
              </td>
              <td class="px-4 py-3 text-center">
                <Badge :variant="riskBadge(c.churn_risk)">{{ c.churn_risk }}</Badge>
              </td>
              <td class="px-4 py-3 text-center text-lg">{{ trendIcon(c.future_trend) }}</td>
              <td class="px-4 py-3 text-center text-sm text-muted-foreground">{{ c.estimated_next_purchase ?? '—' }}</td>
              <td class="px-4 py-3 text-right text-sm font-semibold text-foreground">{{ formatCurrency(c.ltv_estimated) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <p v-else class="text-sm text-muted-foreground">No hay predicciones IA. Haz clic en "Recalcular IA" para generar el scoring.</p>
    </div>

    <div class="grid gap-6">
      <div class="rounded-xl border border-border bg-card p-6">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/20 dark:text-purple-400">
            <Zap class="h-5 w-5" />
          </div>
          <div>
            <h2 class="text-lg font-semibold text-foreground">Recomendaciones inteligentes</h2>
            <p class="text-sm text-muted-foreground">Basadas en análisis predictivo de datos reales</p>
          </div>
        </div>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/5 p-4">
            <div class="flex items-center gap-2">
              <Calendar class="h-4 w-4 text-emerald-500" />
              <h3 class="text-sm font-semibold text-foreground">Contactar hoy</h3>
            </div>
            <p class="mt-1 text-xs text-muted-foreground">
              {{ stats.at_risk_count > 0
                ? "{$stats.at_risk_count} clientes necesitan atención urgente."
                : 'No hay clientes que requieran contacto urgente.' }}
            </p>
            <Link :href="route('crm.ai.contactar')" class="mt-2 inline-block text-xs font-medium text-emerald-500 hover:text-emerald-400">
              Ver lista →
            </Link>
          </div>
          <div class="rounded-lg border border-blue-500/20 bg-blue-500/5 p-4">
            <div class="flex items-center gap-2">
              <Target class="h-4 w-4 text-blue-500" />
              <h3 class="text-sm font-semibold text-foreground">Ventas potenciales</h3>
            </div>
            <p class="mt-1 text-xs text-muted-foreground">
              {{ topClients.length }} clientes con alta probabilidad de compra. LTV total estimado: {{ formatCurrency(stats.total_ltv) }}
            </p>
          </div>
          <div class="rounded-lg border border-amber-500/20 bg-amber-500/5 p-4">
            <div class="flex items-center gap-2">
              <TrendingUp class="h-4 w-4 text-amber-500" />
              <h3 class="text-sm font-semibold text-foreground">Pronóstico</h3>
            </div>
            <p class="mt-1 text-xs text-muted-foreground">
              Ingresos estimados próximos 7 días: {{ formatCurrency(forecast.estimated_7d) }} · Confianza {{ confidenceLabel(forecast.confidence) }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
