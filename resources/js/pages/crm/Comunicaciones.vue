<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Badge from '@/components/ui/Badge.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { MessageSquareText, Mail, Bell, Send } from '@lucide/vue'

const props = defineProps<{
  comunicaciones: {
    data: Array<{
      id: number
      cliente_id: number | null
      cliente_nombre: string | null
      tipo: string
      canal: string
      asunto: string
      mensaje: string
      enviado: boolean
      enviado_en: string | null
      created_at: string
    }>
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
}>()

function tipoColor(tipo: string): string {
  if (tipo === 'bienvenida') return 'text-emerald-500'
  if (tipo === 'agradecimiento') return 'text-blue-500'
  if (tipo === 'recuperacion') return 'text-amber-500'
  return 'text-gray-500'
}

function canalIcon(canal: string): any {
  if (canal === 'email') return Mail
  return Bell
}
</script>

<template>
  <AppLayout>
    <Head title="Mensajes CRM" />

    <div class="mb-6">
      <h1 class="text-2xl font-bold text-foreground">Mensajes automáticos</h1>
      <p class="text-muted-foreground">Comunicaciones generadas por las reglas de automatización</p>
    </div>

    <div v-if="comunicaciones.data.length === 0" class="rounded-xl border border-border bg-card p-12 text-center">
      <MessageSquareText class="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
      <h2 class="mb-2 text-lg font-semibold text-foreground">Sin mensajes</h2>
      <p class="text-sm text-muted-foreground">Los mensajes automáticos aparecerán aquí cuando las reglas del CRM los generen.</p>
    </div>

    <div v-else class="space-y-3">
      <div v-for="msg in comunicaciones.data" :key="msg.id" class="rounded-xl border border-border bg-card p-5">
        <div class="flex items-start gap-4">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
            <component :is="canalIcon(msg.canal)" class="h-5 w-5" />
          </div>
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <h3 class="text-sm font-semibold text-foreground">{{ msg.asunto }}</h3>
              <Badge :variant="msg.enviado ? 'success' : 'gray'">
                {{ msg.enviado ? 'Enviado' : 'Pendiente' }}
              </Badge>
              <span class="text-xs capitalize text-muted-foreground">{{ msg.canal }}</span>
              <span class="text-xs text-muted-foreground" :class="tipoColor(msg.tipo)">{{ msg.tipo }}</span>
            </div>
            <p class="mt-1 text-sm text-muted-foreground">{{ msg.mensaje }}</p>
            <div class="mt-2 flex items-center gap-3 text-xs text-muted-foreground">
              <span>{{ msg.created_at }}</span>
              <span v-if="msg.enviado_en">Enviado: {{ msg.enviado_en }}</span>
              <span v-if="msg.cliente_nombre">
                <Link :href="route('clientes.show', msg.cliente_id)" class="hover:text-primary">Para: {{ msg.cliente_nombre }}</Link>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Pagination :links="comunicaciones.links" />
  </AppLayout>
</template>
