<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'

const props = defineProps<{
  recovery?: boolean
}>()

const form = useForm({
  code: '',
  recovery_code: '',
})

const recovery = ref(props.recovery ?? false)

function submit() {
  form.post(route('two-factor.login'))
}
</script>

<template>
  <Head title="Autenticación de dos factores" />

  <div class="flex min-h-screen items-center justify-center px-6">
    <div class="w-full max-w-md">
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-foreground">Autenticación de dos factores</h1>
        <p class="mt-1 text-muted-foreground">
          {{ recovery ? 'Ingresa tu código de recuperación' : 'Ingresa el código de tu aplicación autenticadora' }}
        </p>
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <template v-if="!recovery">
          <Input v-model="form.code" label="Código" placeholder="000000" :error="form.errors.code" />
        </template>
        <template v-else>
          <Input v-model="form.recovery_code" label="Código de recuperación" placeholder="••••••••" :error="form.errors.recovery_code" />
        </template>

        <Button type="submit" :loading="form.processing" class="w-full">
          Verificar
        </Button>
      </form>

      <button
        type="button"
        class="mt-4 w-full text-center text-sm text-primary hover:underline"
        @click="recovery = !recovery"
      >
        {{ recovery ? 'Usar código de autenticación' : 'Usar código de recuperación' }}
      </button>
    </div>
  </div>
</template>
