<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/Input.vue'
import Button from '@/components/ui/Button.vue'
import { ArrowLeft, Plus, Trash2, Search } from '@lucide/vue'
import { toast } from 'vue-sonner'
import { ref, computed } from 'vue'
import { formatCurrency } from '@/lib/format'

const props = defineProps<{
  productos: { id: number; nombre: string; precio: number }[]
  clientes: { id: number; nombre: string; telefono: string; email: string }[]
}>()

interface DetalleForm {
  producto_id: string
  producto_nombre: string
  cantidad: number
  precio_unitario: number
  subtotal: number
  detalles_diseno: string
}

const form = useForm({
  cliente_id: '',
  cliente_nombre: '',
  cliente_telefono: '',
  cliente_direccion: '',
  estado: 'pendiente',
  fecha_entrega: '',
  detalles: [] as DetalleForm[],
})

function addDetalle() {
  form.detalles.push({
    producto_id: '',
    producto_nombre: '',
    cantidad: 1,
    precio_unitario: 0,
    subtotal: 0,
    detalles_diseno: '',
  })
}

function removeDetalle(index: number) {
  form.detalles.splice(index, 1)
}

function updateSubtotal(index: number) {
  const d = form.detalles[index]
  d.subtotal = Number(d.cantidad) * Number(d.precio_unitario)
}

const total = computed(() =>
  form.detalles.reduce((sum, d) => sum + Number(d.subtotal), 0)
)

const searchCliente = ref('')
const showClientes = ref(false)

const clientesFiltrados = computed(() =>
  props.clientes.filter((c) =>
    c.nombre.toLowerCase().includes(searchCliente.value.toLowerCase())
  )
)

const selectedCliente = computed(() =>
  props.clientes.find((c) => c.id === Number(form.cliente_id))
)

function selectCliente(cliente: { id: number; nombre: string; telefono: string; email: string }) {
  form.cliente_id = String(cliente.id)
  form.cliente_nombre = cliente.nombre
  form.cliente_telefono = cliente.telefono
  showClientes.value = false
}

const searchProductos = ref<number[]>([])

function selectProducto(index: number, producto: { id: number; nombre: string; precio: number }) {
  const d = form.detalles[index]
  d.producto_id = String(producto.id)
  d.producto_nombre = producto.nombre
  d.precio_unitario = producto.precio
  d.subtotal = Number(d.cantidad) * producto.precio
  searchProductos.value[index] = ''
}

function submit() {
  form.post(route('pedidos.store'), {
    onSuccess: () => toast.success('Pedido creado'),
  })
}

addDetalle()
</script>

<template>
  <AppLayout>
    <Head title="Nuevo pedido" />

    <div class="mb-6 flex items-center gap-4">
      <Link :href="route('pedidos.index')">
        <Button variant="ghost" size="icon">
          <ArrowLeft class="h-5 w-5" />
        </Button>
      </Link>
      <div>
        <h1 class="text-2xl font-bold text-foreground">Nuevo pedido</h1>
        <p class="text-muted-foreground">Crea un nuevo pedido con uno o más productos</p>
      </div>
    </div>

    <div class="mx-auto max-w-3xl">
      <form @submit.prevent="submit" class="space-y-6 rounded-xl border border-border bg-card p-6">
        <div class="space-y-4">
          <h2 class="text-lg font-semibold text-foreground">Cliente</h2>
          <div class="space-y-1.5">
            <label class="text-sm font-medium text-foreground">Buscar cliente</label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
              <input v-model="searchCliente" @focus="showClientes = true" placeholder="Buscar cliente..." class="flex h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 py-2 text-sm text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
            </div>
            <div v-if="showClientes && clientesFiltrados.length > 0" class="relative">
              <div class="absolute z-10 mt-1 max-h-40 w-full overflow-auto rounded-lg border border-border bg-card shadow-lg">
                <button v-for="cliente in clientesFiltrados" :key="cliente.id" type="button" @click="selectCliente(cliente)" class="w-full px-3 py-2 text-left text-sm text-foreground hover:bg-accent">
                  {{ cliente.nombre }}
                </button>
              </div>
            </div>
            <p v-if="selectedCliente" class="text-xs text-muted-foreground">Seleccionado: {{ selectedCliente.nombre }}</p>
            <p v-if="form.errors.cliente_id" class="text-sm text-destructive">{{ form.errors.cliente_id }}</p>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
             <Input v-model="form.cliente_nombre" label="Nombre de contacto" :error="form.errors.cliente_nombre" />
             <Input v-model="form.cliente_telefono" label="Teléfono de contacto" :error="form.errors.cliente_telefono" />
          </div>

          <div class="space-y-1.5">
            <label class="text-sm font-medium text-foreground">Dirección</label>
            <textarea v-model="form.cliente_direccion" rows="2" class="flex w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" placeholder="Dirección del cliente"></textarea>
          </div>
        </div>

        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-foreground">Productos</h2>
            <Button type="button" variant="outline" size="sm" @click="addDetalle">
              <Plus class="h-4 w-4" />
              Agregar producto
            </Button>
          </div>

          <div v-for="(detalle, index) in form.detalles" :key="index" class="rounded-lg border border-border bg-background/50 p-4 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-muted-foreground">Producto #{{ index + 1 }}</span>
              <Button v-if="form.detalles.length > 1" type="button" variant="ghost" size="icon" @click="removeDetalle(index)">
                <Trash2 class="h-4 w-4 text-destructive" />
              </Button>
            </div>

            <div class="space-y-1.5">
              <label class="text-sm font-medium text-foreground">Buscar producto</label>
              <div class="relative">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="searchProductos[index]" @focus="searchProductos[index] = searchProductos[index] || ''" placeholder="Buscar producto..." class="flex h-10 w-full rounded-lg border border-input bg-background pl-10 pr-3 py-2 text-sm text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
              </div>
              <div v-if="searchProductos[index]?.length > 0" class="relative">
                <div class="absolute z-10 mt-1 max-h-40 w-full overflow-auto rounded-lg border border-border bg-card shadow-lg">
                  <button v-for="producto in props.productos.filter(p => p.nombre.toLowerCase().includes((searchProductos[index] || '').toLowerCase()))" :key="producto.id" type="button" @click="selectProducto(index, producto); searchProductos[index] = ''" class="w-full px-3 py-2 text-left text-sm text-foreground hover:bg-accent">
                    {{ producto.nombre }} - {{ formatCurrency(producto.precio) }}
                  </button>
                </div>
              </div>
              <p v-if="detalle.producto_nombre" class="text-xs text-muted-foreground">Seleccionado: {{ detalle.producto_nombre }} - {{ formatCurrency(detalle.precio_unitario) }}</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Cantidad</label>
                <input v-model.number="detalle.cantidad" type="number" min="1" @input="updateSubtotal(index)" class="flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                <p v-if="form.errors['detalles.' + index + '.cantidad']" class="text-sm text-destructive">{{ form.errors['detalles.' + index + '.cantidad'] }}</p>
              </div>
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Precio unitario</label>
                <input v-model.number="detalle.precio_unitario" type="number" step="0.01" @input="updateSubtotal(index)" class="flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" />
                <p v-if="form.errors['detalles.' + index + '.precio_unitario']" class="text-sm text-destructive">{{ form.errors['detalles.' + index + '.precio_unitario'] }}</p>
              </div>
              <div class="space-y-1.5">
                <label class="text-sm font-medium text-foreground">Subtotal</label>
                <div class="flex h-10 items-center rounded-lg border border-border bg-background px-3 text-sm text-foreground">{{ formatCurrency(detalle.subtotal) }}</div>
              </div>
            </div>

            <div class="space-y-1.5">
              <label class="text-sm font-medium text-foreground">Detalles de diseño</label>
              <textarea v-model="detalle.detalles_diseno" rows="2" class="flex w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring" placeholder="Detalles del diseño para este producto"></textarea>
            </div>
          </div>

          <p v-if="form.errors.detalles" class="text-sm text-destructive">{{ form.errors.detalles }}</p>
        </div>

        <div class="flex items-center justify-between rounded-lg border border-border bg-background/50 px-4 py-3">
          <span class="text-sm font-medium text-muted-foreground">Total del pedido</span>
          <span class="text-lg font-bold text-foreground">{{ formatCurrency(total) }}</span>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
          <div class="space-y-1.5">
            <label class="text-sm font-medium text-foreground">Estado</label>
            <select v-model="form.estado" class="flex h-10 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
              <option value="pendiente">Pendiente</option>
              <option value="en_proceso">En proceso</option>
              <option value="listo">Listo</option>
              <option value="entregado">Entregado</option>
            </select>
          </div>
          <Input v-model="form.fecha_entrega" label="Fecha de entrega" type="date" :error="form.errors.fecha_entrega" />
        </div>

        <div class="flex gap-3">
          <Button type="submit" :loading="form.processing">Guardar pedido</Button>
          <Link :href="route('pedidos.index')">
            <Button type="button" variant="outline">Cancelar</Button>
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
