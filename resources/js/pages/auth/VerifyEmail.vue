<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import Button from '@/components/ui/Button.vue'

defineProps<{
  status?: string
}>()

const form = useForm({})

function resend() {
  form.post(route('verification.send'))
}
</script>

<template>
  <Head title="Verificar correo" />

  <div class="flex min-h-screen items-center justify-center px-6">
    <div class="w-full max-w-md text-center">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-foreground">Verifica tu correo electrónico</h1>
        <p class="mt-2 text-muted-foreground">Te hemos enviado un enlace de verificación. Revisa tu bandeja de entrada.</p>
      </div>

      <div v-if="status" class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
        {{ status }}
      </div>

      <Button :loading="form.processing" @click="resend">
        Reenviar correo de verificación
      </Button>

      <form method="POST" :action="route('logout')" class="mt-6">
        <input type="hidden" name="_token" :value="($page.props as any).csrfToken" />
        <button type="submit" class="text-sm text-muted-foreground hover:text-foreground underline">Cerrar sesión</button>
      </form>
    </div>
  </div>
</template>
