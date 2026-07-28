<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/Button.vue'
import Badge from '@/components/ui/Badge.vue'
import { ArrowLeft, Pencil, Package } from '@lucide/vue'
import { computed } from 'vue'
import { formatCurrency } from '@/lib/format'

const props = defineProps<{
  producto: {
    id: number
    nombre: string
    categoria: string | null
    descripcion: string | null
    precio: number
    stock: number
    imagen: string | null
    activo: boolean
    created_at: string
  }
}>()

const imagenUrl = computed(() => {
  if (!props.producto.imagen) return null
  if (props.producto.imagen.startsWith('http')) return props.producto.imagen
  return '/storage/' + props.producto.imagen
})
</script>

<template>
  <AppLayout>
    <Head :title="producto.nombre" />

    <div class="mb-6 flex items-center gap-4">
      <Link :href="route('productos.index')">
        <Button variant="ghost" size="icon">
          <ArrowLeft class="h-5 w-5" />
        </Button>
      </Link>
      <div class="flex-1">
        <h1 class="text-2xl font-bold text-foreground">{{ producto.nombre }}</h1>
        <p class="text-muted-foreground">Detalle del producto</p>
      </div>
      <Link :href="route('productos.edit', producto.id)">
        <Button>
          <Pencil class="h-4 w-4" />
          Editar
        </Button>
      </Link>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="lg:col-span-2 space-y-6">
        <div class="card p-6">
          <h2 class="mb-4 text-lg font-semibold text-foreground">Información general</h2>
          <dl class="space-y-4">
            <div class="flex justify-between border-b border-white/5 pb-3">
              <dt class="text-sm text-muted-foreground">Nombre</dt>
              <dd class="text-sm text-foreground">{{ producto.nombre }}</dd>
            </div>
            <div class="flex justify-between border-b border-white/5 pb-3">
              <dt class="text-sm text-muted-foreground">Categoría</dt>
              <dd class="text-sm text-foreground">{{ producto.categoria ?? 'Sin categoría' }}</dd>
            </div>
            <div class="flex justify-between border-b border-white/5 pb-3">
              <dt class="text-sm text-muted-foreground">Precio</dt>
              <dd class="text-sm font-semibold text-fuchsia-400">{{ formatCurrency(producto.precio) }}</dd>
            </div>
            <div class="flex justify-between border-b border-white/5 pb-3">
              <dt class="text-sm text-muted-foreground">Stock</dt>
              <dd class="text-sm text-foreground">{{ producto.stock }}</dd>
            </div>
            <div class="flex justify-between border-b border-white/5 pb-3">
              <dt class="text-sm text-muted-foreground">Estado</dt>
              <dd>
                <Badge :variant="producto.activo ? 'success' : 'gray'">
                  {{ producto.activo ? 'Activo' : 'Inactivo' }}
                </Badge>
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-sm text-muted-foreground">Registrado</dt>
              <dd class="text-sm text-foreground">{{ new Date(producto.created_at).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' }) }}</dd>
            </div>
          </dl>
        </div>

        <div v-if="producto.descripcion" class="card p-6">
          <h2 class="mb-4 text-lg font-semibold text-foreground">Descripción</h2>
          <p class="text-sm text-muted-foreground leading-relaxed">{{ producto.descripcion }}</p>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card flex items-center justify-center overflow-hidden p-0">
          <div v-if="imagenUrl" class="w-full">
            <img :src="imagenUrl" :alt="producto.nombre" class="h-64 w-full object-cover" />
          </div>
          <div v-else class="flex h-64 w-full flex-col items-center justify-center text-muted-foreground">
            <Package class="mb-2 h-12 w-12" />
            <span class="text-sm">Sin imagen</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
