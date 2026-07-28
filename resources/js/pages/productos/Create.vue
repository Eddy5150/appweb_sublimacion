<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import { ArrowLeft } from '@lucide/vue'
import { toast } from 'vue-sonner'

defineProps<{
  categorias?: string[]
}>()

const form = useForm({
  nombre: '',
  categoria: '',
  descripcion: '',
  precio: '',
  stock: '',
  imagen: null as File | null,
  activo: true,
})

function submit() {
  form.post(route('productos.store'), {
    onSuccess: () => toast.success('Producto creado'),
  })
}

function onFileChange(e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    form.imagen = file
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Nuevo producto" />

    <div class="mb-6 flex items-center gap-4">
      <Link :href="route('productos.index')">
        <Button variant="ghost" size="icon">
          <ArrowLeft class="h-5 w-5" />
        </Button>
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-foreground">Nuevo producto</h1>
        <p class="text-muted-foreground">Agrega un nuevo producto al catálogo</p>
      </div>
    </div>

    <div class="mx-auto max-w-2xl">
      <form @submit.prevent="submit" enctype="multipart/form-data" class="card p-6 space-y-6">
        <Input v-model="form.nombre" label="Nombre" placeholder="Nombre del producto" :error="form.errors.nombre" />

        <div class="space-y-1.5">
          <label class="text-sm font-medium text-foreground">Categoría</label>
          <select v-model="form.categoria" class="flex h-10 w-full rounded-lg border border-white/20 bg-slate-950/70 px-3 py-2 text-sm text-white placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fuchsia-500/20">
            <option value="" class="bg-[#05010a]">Sin categoría</option>
            <option v-for="cat in categorias" :key="cat" :value="cat" class="bg-[#05010a]">{{ cat }}</option>
          </select>
          <p v-if="form.errors.categoria" class="text-sm text-red-400">{{ form.errors.categoria }}</p>
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-medium text-foreground">Descripción</label>
          <textarea v-model="form.descripcion" rows="3" class="flex w-full rounded-lg border border-white/20 bg-slate-950/70 px-3 py-2 text-sm text-white placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-fuchsia-500/20" placeholder="Descripción del producto"></textarea>
          <p v-if="form.errors.descripcion" class="text-sm text-red-400">{{ form.errors.descripcion }}</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <Input v-model="form.precio" label="Precio" type="number" step="0.01" placeholder="0.00" :error="form.errors.precio" />
          <Input v-model="form.stock" label="Stock" type="number" placeholder="0" :error="form.errors.stock" />
        </div>

        <div class="space-y-1.5">
          <label class="text-sm font-medium text-foreground">Imagen</label>
          <input type="file" accept="image/*" @change="onFileChange" class="flex w-full text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-fuchsia-500/10 file:px-3 file:py-2 file:text-sm file:font-medium file:text-fuchsia-400" />
          <p v-if="form.errors.imagen" class="text-sm text-red-400">{{ form.errors.imagen }}</p>
        </div>

        <div class="flex items-center gap-2">
          <input id="activo" v-model="form.activo" type="checkbox" class="rounded border-white/30 bg-slate-950/70 text-fuchsia-500 focus:ring-fuchsia-500 focus:ring-offset-0" />
          <label for="activo" class="text-sm text-slate-300">Producto activo</label>
        </div>

        <div class="flex gap-3 pt-2">
          <Button type="submit" :loading="form.processing">Guardar producto</Button>
          <Link :href="route('productos.index')">
            <Button type="button" variant="outline">Cancelar</Button>
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
