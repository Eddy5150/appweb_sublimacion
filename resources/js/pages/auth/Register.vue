<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

function submit() {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Registrarse" />

  <div class="flex min-h-screen items-center justify-center px-6">
    <div class="w-full max-w-md">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-foreground">Crear cuenta</h1>
        <p class="mt-1 text-muted-foreground">Regístrate para comenzar</p>
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <Input v-model="form.name" label="Nombre completo" placeholder="Tu nombre" :error="form.errors.name" />
        <Input v-model="form.email" label="Correo electrónico" type="email" placeholder="correo@ejemplo.com" :error="form.errors.email" />
        <Input v-model="form.password" label="Contraseña" type="password" placeholder="••••••••" :error="form.errors.password" />
        <Input v-model="form.password_confirmation" label="Confirmar contraseña" type="password" placeholder="••••••••" :error="form.errors.password_confirmation" />

        <Button type="submit" :loading="form.processing" class="w-full">
          Registrarse
        </Button>
      </form>

      <p class="mt-6 text-center text-sm text-muted-foreground">
        ¿Ya tienes cuenta?
        <Link :href="route('login')" class="text-primary hover:underline">Iniciar sesión</Link>
      </p>
    </div>
  </div>
</template>
