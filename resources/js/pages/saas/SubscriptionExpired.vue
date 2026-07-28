<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { AlertTriangle } from '@lucide/vue'
</script>

<template>
  <Head title="Suscripción Expirada" />

  <div class="flex min-h-screen items-center justify-center bg-[#020817]">
    <div class="w-full max-w-md rounded-xl border border-white/10 bg-white/5 p-8 text-center backdrop-blur-xl">
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-500/10">
        <AlertTriangle class="h-8 w-8 text-amber-400" />
      </div>

      <h1 class="mt-6 text-2xl font-bold text-white">Suscripción Expirada</h1>

      <p class="mt-2 text-gray-400">
        Tu plan ha expirado. Para seguir usando el sistema, contacta al administrador para renovar tu suscripción.
      </p>

      <div class="mt-8 space-y-3">
        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="w-full rounded-lg bg-gradient-to-r from-purple-500 to-fuchsia-500 px-4 py-2.5 text-sm font-medium text-white transition-all hover:from-purple-600 hover:to-fuchsia-600"
        >
          Cerrar Sesión
        </Link>
      </div>
    </div>
  </div>
</template>
