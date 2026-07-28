<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Button from '@/components/ui/Button.vue'
import { Phone, AlertTriangle, Heart, Sparkles, ArrowLeft } from '@lucide/vue'
import { formatCurrency } from '@/lib/format'

defineProps<{
  clientes: Array<{
    cliente_id: number
    cliente_nombre: string
    cliente_telefono: string | null
    motivo: string
    ai_score: number
    purchase_probability: number
    emotional_state: string
    churn_risk: string
  }>
}>()

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

function estadoIcon(state: string): any {
  if (state === 'feliz') return Heart
  if (state === 'en_riesgo') return AlertTriangle
  return Sparkles
}
</script>

<template>
  <AppLayout>
    <Head title="Contactar hoy" />

    <div class="mb-6">
      <Link :href="route('crm.ai')" class="mb-4 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary">
        <ArrowLeft class="h-4 w-4" />
        Volver a IA CRM
      </Link>
      <h1 class="text-2xl font-bold text-foreground">Clientes para contactar hoy</h1>
      <p class="text-muted-foreground">Recomendaciones inteligentes basadas en predicciones IA</p>
    </div>

    <div v-if="clientes.length === 0" class="rounded-xl border border-border bg-card p-12 text-center">
      <Heart class="mx-auto mb-4 h-12 w-12 text-emerald-500" />
      <h2 class="mb-2 text-lg font-semibold text-foreground">No hay clientes urgentes</h2>
      <p class="text-sm text-muted-foreground">Todos los clientes están al día. Vuelve más tarde para ver nuevas recomendaciones.</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="cliente in clientes" :key="cliente.cliente_id" class="rounded-xl border p-5"
        :class="cliente.churn_risk === 'alta' || cliente.emotional_state === 'en_riesgo' ? 'border-red-500/20 bg-red-500/5' : 'border-emerald-500/20 bg-emerald-500/5'"
      >
        <div class="flex items-start gap-4">
          <div
            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-lg font-bold"
            :class="cliente.emotional_state === 'feliz' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'"
          >
            {{ cliente.cliente_nombre?.charAt(0) }}
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <Link :href="route('clientes.show', cliente.cliente_id)" class="text-lg font-semibold text-foreground hover:text-primary">
                {{ cliente.cliente_nombre }}
              </Link>
              <Badge :variant="emotionBadge(cliente.emotional_state)">{{ cliente.emotional_state }}</Badge>
              <Badge :variant="riskBadge(cliente.churn_risk)">Riesgo {{ cliente.churn_risk }}</Badge>
            </div>
            <p class="mt-2 text-sm text-foreground">{{ cliente.motivo }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-4 text-sm">
              <div class="flex items-center gap-1">
                <component :is="estadoIcon(cliente.emotional_state)" class="h-4 w-4" :class="cliente.emotional_state === 'feliz' ? 'text-emerald-500' : cliente.emotional_state === 'en_riesgo' ? 'text-red-500' : 'text-amber-500'" />
                <span class="text-muted-foreground">Score IA: <strong class="text-foreground">{{ cliente.ai_score }}</strong></span>
              </div>
              <span class="text-muted-foreground">Prob. compra: <strong class="text-foreground">{{ cliente.purchase_probability }}%</strong></span>
              <span v-if="cliente.cliente_telefono" class="flex items-center gap-1 text-muted-foreground">
                <Phone class="h-3 w-3" />
                {{ cliente.cliente_telefono }}
              </span>
            </div>
          </div>
          <Link :href="route('clientes.show', cliente.cliente_id)">
            <Button variant="outline" size="sm">Ver perfil</Button>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
