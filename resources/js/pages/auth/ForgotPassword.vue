<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

defineProps<{
  status?: string
}>()

const form = useForm({
  email: '',
})

function submit() {
  form.post(route('password.email'))
}
</script>

<template>
  <Head title="Recuperar contraseña" />

  <div class="flex min-h-screen items-center justify-center px-6">
    <div class="w-full max-w-md">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-foreground">Recuperar contraseña</h1>
        <p class="mt-1 text-muted-foreground">Te enviaremos un enlace para restablecer tu contraseña</p>
      </div>

      <div v-if="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
        {{ status }}
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <Input v-model="form.email" label="Correo electrónico" type="email" placeholder="correo@ejemplo.com" :error="form.errors.email" />

        <Button type="submit" :loading="form.processing" class="w-full">
          Enviar enlace
        </Button>
      </form>
    </div>
  </div>
</template>
