<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

const form = useForm({
  password: '',
})

function submit() {
  form.post(route('password.confirm'), {
    onFinish: () => form.reset(),
  })
}
</script>

<template>
  <Head title="Confirmar contraseña" />

  <div class="flex min-h-screen items-center justify-center px-6">
    <div class="w-full max-w-md">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-foreground">Confirma tu contraseña</h1>
        <p class="mt-1 text-muted-foreground">Por razones de seguridad, confirma tu contraseña para continuar</p>
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <Input v-model="form.password" label="Contraseña" type="password" placeholder="••••••••" :error="form.errors.password" />

        <Button type="submit" :loading="form.processing" class="w-full">
          Confirmar
        </Button>
      </form>
    </div>
  </div>
</template>
