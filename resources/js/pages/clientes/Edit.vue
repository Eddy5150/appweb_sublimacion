<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import { ArrowLeft } from '@lucide/vue'
import { toast } from 'vue-sonner'

const props = defineProps<{
  cliente: {
    id: number
    nombre: string
    telefono: string | null
    email: string | null
    direccion: string | null
    estado: string
  }
}>()

const form = useForm({
  nombre: props.cliente.nombre,
  telefono: props.cliente.telefono ?? '',
  email: props.cliente.email ?? '',
  direccion: props.cliente.direccion ?? '',
  estado: props.cliente.estado,
  _method: 'PUT',
})

function submit() {
  form.post(route('clientes.update', props.cliente.id), {
    onSuccess: () => toast.success('Cliente actualizado'),
  })
}
</script>

<template>
  <AppLayout>
    <Head :title="'Editar - ' + cliente.nombre" />

    <div class="mb-6 flex items-center gap-4">
      <Link :href="route('clientes.index')">
        <Button variant="ghost" size="icon">
          <ArrowLeft class="h-5 w-5" />
        </Button>
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-foreground">Editar cliente</h1>
        <p class="text-muted-foreground">{{ cliente.nombre }}</p>
      </div>
    </div>

    <div class="mx-auto max-w-2xl">
      <form @submit.prevent="submit" class="space-y-6 rounded-xl border border-border bg-card p-6">
        <Input v-model="form.nombre" label="Nombre" :error="form.errors.nombre" />
        <Input v-model="form.telefono" label="Teléfono" :error="form.errors.telefono" />
        <Input v-model="form.email" label="Email" type="email" :error="form.errors.email" />
        <div class="space-y-1.5">
          <label class="text-sm font-medium text-foreground">Dirección</label>
          <textarea v-model="form.direccion" rows="3" class="flex w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"></textarea>
        </div>
        <div class="space-y-1.5">
          <label class="text-sm font-medium text-foreground">Estado</label>
          <select v-model="form.estado" class="flex w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
            <option value="nuevo">Nuevo</option>
            <option value="frecuente">Frecuente</option>
            <option value="vip">VIP</option>
          </select>
        </div>

        <div class="flex gap-3">
          <Button type="submit" :loading="form.processing">Actualizar cliente</Button>
          <Link :href="route('clientes.index')">
            <Button type="button" variant="outline">Cancelar</Button>
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
