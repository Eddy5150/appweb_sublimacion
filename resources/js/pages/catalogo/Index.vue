<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Toaster, toast } from 'vue-sonner'
import { ref, computed } from 'vue'
import {
  ShoppingBag, Shirt, ArrowLeft, Sparkles, Package, Tag,
  Plus, Minus, X, ShoppingCart, Loader2, CheckCircle2,
  MapPin, Phone, User, CreditCard, PackageCheck,
} from '@lucide/vue'
import { formatCurrency } from '@/lib/format'
import { useCart } from '@/composables/useCart'

interface CatalogProduct {
    id: number; nombre: string; categoria: string; descripcion: string | null
    precio: number; stock: number; imagen: string; badge: string; color: string; tags: string[]
}

const props = defineProps<{
  productos: CatalogProduct[]
  stats: Record<string, number>
  catInfo: Record<string, { icon: string; tag: string; color: string; desc: string }>
  pedidoCreado: { id: number; total: number; cliente_nombre: string; created_at: string } | null
}>()

const { items, subtotal: total, count, addItem, removeItem, updateQuantity, clearCart } = useCart()

const categoriaActiva = ref<string | null>(null)
const cartOpen = ref(false)
const checkoutOpen = ref(false)
const submitting = ref(false)

const clienteNombre = ref('')
const clienteTelefono = ref('')
const clienteDireccion = ref('')

const categorias = computed(() => Object.keys(props.catInfo).sort())

const productosFiltrados = computed(() => {
  if (!categoriaActiva.value) return props.productos
  return props.productos.filter(p => p.categoria === categoriaActiva.value)
})

const badgeClasses: Record<string, string> = {
  cyan: 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30',
  orange: 'bg-orange-500/20 text-orange-300 border-orange-500/30',
  violet: 'bg-violet-500/20 text-violet-300 border-violet-500/30',
  gray: 'bg-gray-500/20 text-gray-300 border-gray-500/30',
}

const tagClasses: Record<string, string> = {
  PREMIUM: 'bg-amber-500/20 text-amber-300 border-amber-500/30',
  'ÚLTIMOS': 'bg-red-500/20 text-red-300 border-red-500/30',
  AGOTADO: 'bg-gray-500/20 text-gray-400 border-gray-500/30',
}

const totalProductos = computed(() => props.productos.length)

function toggleCart() { cartOpen.value = !cartOpen.value }

function addToCart(producto: CatalogProduct) {
  addItem({
    id: producto.id,
    nombre: producto.nombre,
    precio: producto.precio,
    imagen: producto.imagen,
  })
}

function abrirCheckout() {
  checkoutOpen.value = true
  cartOpen.value = false
}

function confirmarPedido() {
  if (!clienteNombre.value.trim()) return
  submitting.value = true
  router.post(route('carrito.confirmar'), {
    cliente_nombre: clienteNombre.value.trim(),
    cliente_telefono: clienteTelefono.value.trim() || null,
    cliente_direccion: clienteDireccion.value.trim() || null,
    items: items.value.map(i => ({
      producto_id: i.producto_id,
      nombre: i.nombre,
      precio: i.precio,
      cantidad: i.cantidad,
    })),
  }, {
    preserveScroll: true,
    onSuccess: () => {
      clearCart()
      checkoutOpen.value = false
      clienteNombre.value = ''
      clienteTelefono.value = ''
      clienteDireccion.value = ''
    },
    onError: () => toast.error('Error al confirmar el pedido'),
    onFinish: () => { submitting.value = false },
  })
}

const mostrarConfirmacion = computed(() => !!props.pedidoCreado)
</script>

<template>
  <div class="min-h-screen bg-[#070b14]">
    <Toaster position="top-right" rich-colors close-button />
    <Head title="Tienda Deportiva · Samuzabety" />

    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
      <!-- Top bar -->
      <div class="mb-6 flex items-center justify-between">
        <Link href="/" class="inline-flex items-center gap-2 rounded-xl border border-white/5 bg-white/[0.03] px-4 py-2 text-sm text-gray-500 hover:bg-white/10 hover:text-white transition-all duration-200">
          <ArrowLeft class="h-4 w-4" />
          Volver
        </Link>
        <div class="flex items-center gap-3">
          <span class="text-xs text-gray-600">{{ totalProductos }} productos</span>
          <button
            @click="toggleCart"
            class="relative inline-flex items-center gap-2 rounded-xl border border-white/5 bg-white/[0.03] px-4 py-2 text-sm text-gray-400 hover:bg-white/10 hover:text-white transition-all duration-200 cursor-pointer"
          >
            <ShoppingCart class="h-4 w-4" />
            Carrito
            <span v-if="count > 0" class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-cyan-500 text-[10px] font-bold text-white shadow-lg shadow-cyan-500/30">
              {{ count }}
            </span>
          </button>
        </div>
      </div>

      <!-- Hero -->
      <div class="relative mb-10 overflow-hidden rounded-2xl bg-gradient-to-br from-[#0c1628] via-[#14203a] to-[#0a0e17] border border-white/[0.04]">
        <div class="absolute inset-0 overflow-hidden">
          <div class="absolute -top-40 -right-40 h-[500px] w-[500px] rounded-full bg-cyan-500/[0.06] blur-[120px]"></div>
          <div class="absolute -bottom-40 -left-40 h-[500px] w-[500px] rounded-full bg-orange-500/[0.04] blur-[120px]"></div>
          <div class="absolute inset-0" style="background-image: radial-gradient(circle at 25% 50%, rgba(6, 182, 212, 0.03) 0%, transparent 50%)"></div>
        </div>
        <div class="relative px-6 py-10 sm:px-10 sm:py-14">
          <div class="inline-flex items-center gap-2 rounded-full border border-cyan-500/15 bg-cyan-500/[0.07] px-3.5 py-1 text-xs font-medium text-cyan-300/90 mb-4">
            <Sparkles class="h-3.5 w-3.5" />
            Colección 2026 — Sublimación Profesional
          </div>
          <div class="max-w-2xl">
            <h1 class="text-3xl font-black tracking-tight text-white sm:text-5xl lg:text-6xl leading-[1.1]">
              ROPA
              <span class="bg-gradient-to-r from-cyan-400 via-sky-400 to-orange-400 bg-clip-text text-transparent">DEPORTIVA</span>
              <br />SUBLIMADA
            </h1>
            <p class="mt-3 max-w-xl text-sm sm:text-base text-gray-500 leading-relaxed">
              Tecnología de sublimación total en poleras y shorts deportivos.
              Diseños profesionales con colores vibrantes, dry-fit y máxima durabilidad.
            </p>
          </div>
          <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-xs text-gray-600">
            <span class="flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-cyan-500"></span>Sublimación total</span>
            <span class="flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>Dry-fit profesional</span>
            <span class="flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>Diseños únicos</span>
            <span class="flex items-center gap-1.5"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Envío nacional</span>
          </div>
        </div>
      </div>

      <!-- Category filter -->
      <div class="mb-8 flex flex-wrap items-center gap-2">
        <button @click="categoriaActiva = null"
          class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 border cursor-pointer"
          :class="categoriaActiva === null
            ? 'bg-white text-gray-900 border-white shadow-md shadow-white/5'
            : 'text-gray-400 bg-white/[0.03] border-white/[0.06] hover:bg-white/[0.07] hover:text-white hover:border-white/20'">
          <Package class="h-4 w-4" /> Todos
          <span class="ml-1 rounded-md bg-white/[0.08] px-2 py-0.5 text-xs" :class="categoriaActiva === null ? 'bg-gray-200 text-gray-600' : ''">{{ totalProductos }}</span>
        </button>
        <button v-for="cat in categorias" :key="cat" @click="categoriaActiva = cat"
          class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all duration-200 border cursor-pointer"
          :class="categoriaActiva === cat
            ? 'bg-white text-gray-900 border-white shadow-md shadow-white/5'
            : 'text-gray-400 bg-white/[0.03] border-white/[0.06] hover:bg-white/[0.07] hover:text-white hover:border-white/20'">
          {{ catInfo[cat].icon }} {{ cat }}
          <span class="ml-1 rounded-md bg-white/[0.08] px-2 py-0.5 text-xs" :class="categoriaActiva === cat ? 'bg-gray-200 text-gray-600' : ''">{{ stats[cat] || 0 }}</span>
        </button>
      </div>

      <!-- Empty -->
      <div v-if="productosFiltrados.length === 0" class="rounded-2xl border border-white/[0.04] bg-white/[0.02] p-16 text-center">
        <ShoppingBag class="mx-auto h-14 w-14 text-gray-700" />
        <p class="mt-4 text-base text-gray-500">No hay productos en esta categoría.</p>
      </div>

      <!-- Product Grid -->
      <div v-else class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <div v-for="producto in productosFiltrados" :key="producto.id"
          class="group relative flex flex-col overflow-hidden rounded-xl bg-[#111827] border border-white/[0.04] transition-all duration-300 hover:border-white/20 hover:shadow-2xl hover:shadow-cyan-500/5">
          <div class="relative aspect-[4/5] overflow-hidden bg-[#1a2332]">
            <img :src="producto.imagen" :alt="producto.nombre" class="h-full w-full object-cover transition-all duration-700 group-hover:scale-110" loading="lazy" />
            <div class="absolute inset-0 bg-gradient-to-t from-[#111827] via-transparent to-transparent opacity-0 group-hover:opacity-60 transition-opacity duration-300"></div>
            <span class="absolute left-3 top-3 rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest backdrop-blur-sm"
              :class="badgeClasses[producto.color] || badgeClasses.gray">{{ producto.badge }}</span>
            <div class="absolute right-3 top-3 flex flex-col gap-1.5">
              <span v-for="tag in producto.tags" :key="tag"
                class="rounded-full border px-2 py-0.5 text-[10px] font-semibold backdrop-blur-sm"
                :class="tagClasses[tag] || 'bg-gray-500/20 text-gray-300 border-gray-500/30'">{{ tag }}</span>
            </div>
            <div class="absolute inset-x-0 bottom-0 flex items-center justify-center p-4 translate-y-full group-hover:translate-y-0 transition-transform duration-300">
              <button @click="addToCart(producto); toast.success('Agregado: ' + producto.nombre, { duration: 2000 })"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-cyan-500/90 px-5 py-2.5 text-sm font-semibold text-white backdrop-blur-sm shadow-lg shadow-cyan-500/20 hover:bg-cyan-400 transition-colors w-full cursor-pointer">
                <ShoppingBag class="h-4 w-4" /> Agregar al carrito
              </button>
            </div>
          </div>
          <div class="flex flex-1 flex-col p-3.5">
            <h3 class="text-sm font-semibold text-white leading-snug line-clamp-2 min-h-[2.5rem]">{{ producto.nombre }}</h3>
            <p v-if="producto.descripcion" class="mt-1 text-[11px] text-gray-600 line-clamp-2 leading-relaxed">{{ producto.descripcion }}</p>
            <div class="mt-auto pt-3 flex items-end justify-between">
              <div>
                <span class="text-lg font-bold text-white tracking-tight">{{ formatCurrency(producto.precio) }}</span>
                <span class="ml-1.5 text-[10px] text-gray-600">+IVA</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="h-2 w-2 rounded-full" :class="producto.stock > 10 ? 'bg-emerald-500' : producto.stock > 0 ? 'bg-amber-500' : 'bg-red-500'"></span>
                <span class="text-[10px] text-gray-500">{{ producto.stock > 0 ? `${producto.stock} uds` : 'Agotado' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <footer class="mt-14 border-t border-white/[0.04] pt-8 pb-6 text-center">
        <div class="flex items-center justify-center gap-6 text-xs text-gray-700 mb-3">
          <span class="flex items-center gap-1.5"><Zap class="h-3 w-3" /> Sublimación total</span>
          <span class="flex items-center gap-1.5"><Tag class="h-3 w-3" /> Precios en Bs</span>
          <span class="flex items-center gap-1.5"><ShoppingBag class="h-3 w-3" /> Envíos a todo Bolivia</span>
        </div>
        <p class="text-[11px] text-gray-700">&copy; {{ new Date().getFullYear() }} Sublimación Samuzabety &mdash; Villa Tunari, Cochabamba</p>
      </footer>
    </div>

    <!-- ===== ORDER CONFIRMED OVERLAY ===== -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="mostrarConfirmacion" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="router.get(route('catalogo'), {}, { preserveState: false })"></div>
          <div class="relative w-full max-w-lg rounded-2xl bg-[#0d111c] border border-white/[0.06] shadow-2xl p-8 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10 mx-auto mb-4">
              <CheckCircle2 class="h-8 w-8 text-emerald-400" />
            </div>
            <h2 class="text-xl font-bold text-white">Pedido confirmado</h2>
            <p class="mt-1 text-sm text-gray-400">Gracias por tu compra, {{ pedidoCreado?.cliente_nombre }}</p>

            <div class="mt-6 rounded-xl bg-white/[0.03] border border-white/[0.04] p-4 space-y-2">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Pedido</span>
                <span class="font-semibold text-white">#{{ pedidoCreado?.id }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Total</span>
                <span class="font-semibold text-cyan-400">{{ formatCurrency(pedidoCreado?.total ?? 0) }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Fecha</span>
                <span class="text-gray-300">{{ pedidoCreado?.created_at }}</span>
              </div>
            </div>

            <p class="mt-4 text-xs text-gray-600">Recibirás notificaciones sobre el estado de tu pedido.</p>

            <Link :href="route('catalogo')"
              class="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-cyan-500 py-3 text-sm font-bold text-white hover:bg-cyan-400 transition-all shadow-lg shadow-cyan-500/20 cursor-pointer"
            >
              Seguir comprando
            </Link>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ===== CART DRAWER ===== -->
    <Teleport to="body">
      <Transition name="drawer">
        <div v-if="cartOpen" class="fixed inset-0 z-50 flex justify-end">
          <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="cartOpen = false"></div>
          <div class="relative w-full sm:w-[400px] bg-[#0a0f1a] border-l border-white/[0.06] shadow-2xl flex flex-col h-full">
            <div class="flex items-center justify-between px-5 py-4 border-b border-white/[0.06] shrink-0">
              <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-500/10">
                  <ShoppingCart class="h-5 w-5 text-cyan-400" />
                </div>
                <div>
                  <h2 class="text-base font-semibold text-white">Carrito</h2>
                  <p class="text-xs text-gray-500">{{ count }} {{ count === 1 ? 'producto' : 'productos' }}</p>
                </div>
              </div>
              <button @click="cartOpen = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-white/5 hover:text-white transition-all cursor-pointer">
                <X class="h-4 w-4" />
              </button>
            </div>
            <div class="flex-1 overflow-y-auto px-5 py-4 space-y-3 scrollbar-thin">
              <div v-if="items.length === 0" class="flex flex-col items-center justify-center h-full text-center px-4">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-white/[0.03] mb-4"><ShoppingBag class="h-10 w-10 text-gray-700" /></div>
                <p class="text-base font-medium text-gray-400">Tu carrito está vacío</p>
                <p class="text-sm text-gray-600 mt-1">Explora el catálogo y agrega productos</p>
                <button @click="cartOpen = false" class="mt-6 rounded-xl bg-cyan-500/10 px-6 py-2.5 text-sm font-medium text-cyan-300 hover:bg-cyan-500/20 transition-colors border border-cyan-500/20 cursor-pointer">Seguir comprando</button>
              </div>
              <div v-for="(item, index) in items" :key="item.producto_id"
                class="group relative flex gap-3 rounded-xl bg-white/[0.02] border border-white/[0.04] p-3 transition-all duration-200 hover:bg-white/[0.04] hover:border-white/[0.08]"
                :style="{ animationDelay: index * 50 + 'ms' }">
                <div class="h-24 w-24 shrink-0 overflow-hidden rounded-lg bg-[#1a2332]">
                  <img :src="item.imagen" :alt="item.nombre" class="h-full w-full object-cover" />
                </div>
                <div class="flex flex-1 flex-col min-w-0">
                  <div class="flex items-start justify-between gap-2">
                    <h4 class="text-sm font-medium text-white leading-snug line-clamp-2 flex-1">{{ item.nombre }}</h4>
                     <button @click="removeItem(item.producto_id)" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-gray-600 opacity-0 group-hover:opacity-100 hover:bg-red-500/10 hover:text-red-400 transition-all cursor-pointer"><X class="h-3.5 w-3.5" /></button>
                  </div>
                  <span class="text-sm font-semibold text-cyan-400 mt-1">{{ formatCurrency(item.precio) }}</span>
                  <div class="mt-auto flex items-center justify-between">
                    <div class="flex items-center rounded-lg border border-white/[0.08] bg-white/[0.03]">
                       <button @click="updateQuantity(item.producto_id, item.cantidad - 1)" class="flex h-8 w-8 items-center justify-center text-gray-400 hover:text-white hover:bg-white/5 transition-colors rounded-l-lg cursor-pointer"><Minus class="h-3.5 w-3.5" /></button>
                      <span class="flex h-8 w-10 items-center justify-center text-sm font-medium text-white border-x border-white/[0.06]">{{ item.cantidad }}</span>
                       <button @click="updateQuantity(item.producto_id, item.cantidad + 1)" class="flex h-8 w-8 items-center justify-center text-gray-400 hover:text-white hover:bg-white/5 transition-colors rounded-r-lg cursor-pointer"><Plus class="h-3.5 w-3.5" /></button>
                    </div>
                    <span class="text-sm font-bold text-white">{{ formatCurrency(item.precio * item.cantidad) }}</span>
                  </div>
                </div>
              </div>
            </div>
            <div v-if="items.length > 0" class="border-t border-white/[0.06] px-5 py-4 space-y-3 shrink-0 bg-[#0a0f1a]">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-400">Subtotal</span>
                <span class="text-lg font-bold text-white">{{ formatCurrency(total) }}</span>
              </div>
              <p class="text-[11px] text-gray-600">Impuestos y envío calculados al finalizar</p>
              <button @click="abrirCheckout" class="w-full rounded-xl bg-gradient-to-r from-cyan-500 to-sky-500 py-3.5 text-sm font-bold text-white hover:from-cyan-400 hover:to-sky-400 transition-all shadow-lg shadow-cyan-500/20 hover:shadow-cyan-500/30 cursor-pointer">
                Finalizar compra
              </button>
               <button @click="clearCart(); cartOpen = false" class="w-full rounded-xl py-2.5 text-xs font-medium text-gray-500 hover:text-gray-300 hover:bg-white/[0.03] transition-all cursor-pointer">
                Vaciar carrito
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ===== CHECKOUT MODAL (Shopify-style) ===== -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="checkoutOpen" class="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto py-8">
          <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="checkoutOpen = false"></div>
          <div class="relative w-full max-w-3xl mx-4 rounded-2xl bg-[#0d111c] border border-white/[0.06] shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/[0.06]">
              <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-500/10">
                  <CreditCard class="h-5 w-5 text-cyan-400" />
                </div>
                <div>
                  <h2 class="text-base font-semibold text-white">Finalizar compra</h2>
                  <p class="text-xs text-gray-500">{{ count }} {{ count === 1 ? 'producto' : 'productos' }} en tu pedido</p>
                </div>
              </div>
              <button @click="checkoutOpen = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-white/5 hover:text-white transition-all cursor-pointer">
                <X class="h-4 w-4" />
              </button>
            </div>

            <!-- Body: 2 columns on desktop -->
            <div class="flex flex-col lg:flex-row">
              <!-- Left: Form -->
              <div class="flex-1 p-6 space-y-5">
                <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                  <User class="h-4 w-4 text-cyan-400" /> Tus datos
                </h3>

                <div>
                  <label class="block text-xs font-medium text-gray-400 mb-1.5">Nombre completo *</label>
                  <input v-model="clienteNombre" type="text" placeholder="Ej: María García"
                    class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none focus:border-cyan-500/50 focus:bg-white/[0.05] transition-colors"
                    @keyup.enter="confirmarPedido" />
                </div>

                <div>
                  <label class="block text-xs font-medium text-gray-400 mb-1.5 flex items-center gap-1.5">
                    <Phone class="h-3.5 w-3.5" /> Teléfono
                  </label>
                  <input v-model="clienteTelefono" type="text" placeholder="Ej: 77712345"
                    class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none focus:border-cyan-500/50 focus:bg-white/[0.05] transition-colors"
                    @keyup.enter="confirmarPedido" />
                </div>

                <div>
                  <label class="block text-xs font-medium text-gray-400 mb-1.5 flex items-center gap-1.5">
                    <MapPin class="h-3.5 w-3.5" /> Dirección de entrega <span class="text-gray-600">(opcional)</span>
                  </label>
                  <textarea v-model="clienteDireccion" rows="2" placeholder="Ej: Av. Integración #123, Villa Tunari"
                    class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none resize-none focus:border-cyan-500/50 focus:bg-white/[0.05] transition-colors"></textarea>
                </div>

                <div class="rounded-xl bg-white/[0.02] border border-white/[0.04] p-3">
                  <div class="flex items-center gap-2 text-xs text-gray-500">
                    <PackageCheck class="h-4 w-4 text-cyan-400/60" />
                    Método de entrega: recojo en tienda
                  </div>
                </div>
              </div>

              <!-- Right: Order summary -->
              <div class="lg:w-80 bg-white/[0.02] border-t lg:border-t-0 lg:border-l border-white/[0.06] p-6 space-y-4">
                <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                  <ShoppingBag class="h-4 w-4 text-cyan-400" /> Resumen
                </h3>

                <div class="space-y-3">
                  <div v-for="item in items" :key="item.producto_id"
                    class="flex gap-3 rounded-lg bg-white/[0.02] p-2.5">
                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-[#1a2332]">
                      <img :src="item.imagen" :alt="item.nombre" class="h-full w-full object-cover" />
                    </div>
                    <div class="flex flex-1 flex-col min-w-0">
                      <h4 class="text-xs font-medium text-white leading-snug line-clamp-1">{{ item.nombre }}</h4>
                      <span class="text-[11px] text-gray-500 mt-0.5">Cant: {{ item.cantidad }}</span>
                      <span class="text-xs font-semibold text-cyan-400 mt-auto">{{ formatCurrency(item.precio * item.cantidad) }}</span>
                    </div>
                  </div>
                </div>

                <div class="border-t border-white/[0.06] pt-3 space-y-1.5">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-400">Subtotal</span>
                    <span class="font-medium text-white">{{ formatCurrency(total) }}</span>
                  </div>
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-400">Envío</span>
                    <span class="text-gray-500">Por definir</span>
                  </div>
                  <div class="flex items-center justify-between text-base pt-1.5 border-t border-white/[0.06]">
                    <span class="text-gray-300 font-medium">Total</span>
                    <span class="text-lg font-bold text-white">{{ formatCurrency(total) }}</span>
                  </div>
                </div>

                <button @click="confirmarPedido" :disabled="submitting || !clienteNombre.trim()"
                  class="w-full rounded-xl py-3 text-sm font-bold text-white transition-all cursor-pointer flex items-center justify-center gap-2"
                  :class="submitting || !clienteNombre.trim()
                    ? 'bg-gray-700 text-gray-400 cursor-not-allowed'
                    : 'bg-gradient-to-r from-cyan-500 to-sky-500 hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/20'">
                  <Loader2 v-if="submitting" class="h-4 w-4 animate-spin" />
                  {{ submitting ? 'Procesando...' : 'Confirmar pedido' }}
                </button>

                <p class="text-[10px] text-gray-700 text-center leading-relaxed">
                  Al confirmar aceptas nuestros términos y condiciones. Los datos serán utilizados solo para procesar tu pedido.
                </p>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
.drawer-enter-active, .drawer-leave-active { transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
.drawer-enter-from > div:last-child, .drawer-leave-to > div:last-child { transform: translateX(100%); }
.drawer-enter-from > div:first-child, .drawer-leave-to > div:first-child { opacity: 0; }
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from > div:last-child, .modal-leave-to > div:last-child { transform: scale(0.95) translateY(10px); }
.group > div { animation: fadeSlideIn 0.3s ease both; }
@keyframes fadeSlideIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 99px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.15); }
</style>
