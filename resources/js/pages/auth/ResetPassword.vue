<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps<{
  token: string
  email: string
}>()

const form = useForm({
  token: '',
  email: '',
  password: '',
  password_confirmation: '',
})

form.token = props.token
form.email = props.email

function submit() {
  form.post(route('password.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Restablecer contraseña" />

  <div class="flex min-h-screen items-center justify-center px-6">
    <div class="w-full max-w-md">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-foreground">Restablecer contraseña</h1>
        <p class="mt-1 text-muted-foreground">Ingresa tu nueva contraseña</p>
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <Input v-model="form.email" label="Correo electrónico" type="email" :error="form.errors.email" />
        <Input v-model="form.password" label="Nueva contraseña" type="password" placeholder="••••••••" :error="form.errors.password" />
        <Input v-model="form.password_confirmation" label="Confirmar contraseña" type="password" placeholder="••••••••" :error="form.errors.password_confirmation" />

        <Button type="submit" :loading="form.processing" class="w-full">
          Restablecer contraseña
        </Button>
      </form>
    </div>
  </div>
</template>
