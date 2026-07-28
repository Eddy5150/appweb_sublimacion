<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { Bell, BellOff, CheckCheck, Eye, AlertTriangle, UserPlus, Crown, UserMinus } from '@lucide/vue'
import { toast } from 'vue-sonner'

const props = defineProps<{
  alerts: {
    data: Array<{
      id: number
      cliente_id: number | null
      cliente_nombre: string | null
      tipo: string
      titulo: string
      mensaje: string | null
      leida: boolean
      leida_en: string | null
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  pendientes: number
}>()

const form = useForm({})

function tipoIcon(tipo: string): any {
  if (tipo === 'riesgo') return AlertTriangle
  if (tipo === 'nuevo_cliente') return UserPlus
  if (tipo === 'vip_compra') return Crown
  if (tipo === 'perdida_frecuente') return UserMinus
  return Bell
}

function tipoVariant(tipo: string): string {
  if (tipo === 'riesgo' || tipo === 'perdida_frecuente') return 'danger'
  if (tipo === 'vip_compra') return 'warning'
  if (tipo === 'nuevo_cliente') return 'success'
  return 'primary'
}

function markRead(alert: any) {
  form.post(route('crm.alerts.read', alert.id), {
    onSuccess: () => toast.success('Alerta marcada como leída'),
  })
}

function markAllRead() {
  form.post(route('crm.alerts.read-all'), {
    onSuccess: () => toast.success('Todas las alertas marcadas como leídas'),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Alertas CRM" />

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground">Alertas CRM</h1>
        <p class="text-muted-foreground">
          <template v-if="pendientes > 0">{{ pendientes }} alertas sin leer</template>
          <template v-else>No hay alertas pendientes</template>
        </p>
      </div>
      <div v-if="pendientes > 0" class="flex items-center gap-2">
        <Button variant="outline" :loading="form.processing" @click="markAllRead">
          <CheckCheck class="h-4 w-4" />
          Marcar todas leídas
        </Button>
      </div>
    </div>

    <div v-if="alerts.data.length === 0" class="rounded-xl border border-border bg-card p-12 text-center">
      <BellOff class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
      <h2 class="mb-2 text-lg font-semibold text-foreground">Sin alertas</h2>
      <p class="text-sm text-muted-foreground">Las alertas automáticas aparecerán aquí cuando se detecten eventos importantes.</p>
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="alert in alerts.data"
        :key="alert.id"
        class="rounded-xl border p-5 transition-all"
        :class="alert.leida ? 'border-border bg-card' : 'border-primary/20 bg-primary/5'"
      >
        <div class="flex items-start gap-4">
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
            :class="alert.tipo === 'riesgo' || alert.tipo === 'perdida_frecuente' ? 'bg-red-100 text-red-600 dark:bg-red-900/20 dark:text-red-400' : 'bg-primary/10 text-primary'"
          >
            <component :is="tipoIcon(alert.tipo)" class="h-5 w-5" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="text-sm font-semibold text-foreground">{{ alert.titulo }}</h3>
              <Badge :variant="tipoVariant(alert.tipo)">{{ alert.tipo.replace('_', ' ') }}</Badge>
              <span v-if="!alert.leida" class="h-2 w-2 rounded-full bg-primary" />
            </div>
            <p v-if="alert.mensaje" class="mt-1 text-sm text-muted-foreground">{{ alert.mensaje }}</p>
            <div class="mt-2 flex items-center gap-3 text-xs text-muted-foreground">
              <span>{{ alert.created_at }}</span>
              <span v-if="alert.cliente_nombre">
                <Link :href="route('clientes.show', alert.cliente_id)" class="hover:text-primary">Cliente: {{ alert.cliente_nombre }}</Link>
              </span>
              <span v-if="alert.leida">Leída: {{ alert.leida_en }}</span>
            </div>
          </div>
          <div v-if="!alert.leida" class="shrink-0">
            <Button variant="ghost" size="icon" @click="markRead(alert)">
              <Eye class="h-4 w-4" />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <Pagination :links="alerts.links" />
  </AppLayout>
</template>
