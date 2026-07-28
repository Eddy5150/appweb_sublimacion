import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

export interface CartItem {
  producto_id: number
  nombre: string
  precio: number
  cantidad: number
  imagen?: string
  variante?: string
  detalles_diseno?: string
}

const items = ref<CartItem[]>([])
const open = ref(false)

export function useCart() {
  const count = computed(() => items.value.reduce((s, i) => s + i.cantidad, 0))
  const subtotal = computed(() => items.value.reduce((s, i) => s + i.precio * i.cantidad, 0))
  const isEmpty = computed(() => items.value.length === 0)

  function addItem(producto: { id: number; nombre: string; precio: number; imagen?: string }, cantidad = 1, variante?: string, detalles_diseno?: string) {
    const existing = items.value.find(
      (i) => i.producto_id === producto.id && i.variante === variante
    )
    if (existing) {
      existing.cantidad += cantidad
    } else {
      items.value.push({
        producto_id: producto.id,
        nombre: producto.nombre,
        precio: producto.precio,
        cantidad,
        imagen: producto.imagen,
        variante,
        detalles_diseno,
      })
    }
    open.value = true
  }

  function updateQuantity(productoId: number, cantidad: number, variante?: string) {
    const item = items.value.find(
      (i) => i.producto_id === productoId && i.variante === variante
    )
    if (item) {
      if (cantidad <= 0) {
        items.value = items.value.filter(
          (i) => !(i.producto_id === productoId && i.variante === variante)
        )
      } else {
        item.cantidad = cantidad
      }
    }
  }

  function removeItem(productoId: number, variante?: string) {
    items.value = items.value.filter(
      (i) => !(i.producto_id === productoId && i.variante === variante)
    )
  }

  function clearCart() {
    items.value = []
  }

  function toggleCart() {
    open.value = !open.value
  }

  function closeCart() {
    open.value = false
  }

  function checkout(data?: Record<string, unknown>) {
    router.post(route('carrito.confirmar'), {
      items: items.value,
      ...data,
    }, {
      preserveScroll: true,
      onSuccess: () => {
        items.value = []
        open.value = false
      },
    })
  }

  return {
    items,
    open,
    count,
    subtotal,
    isEmpty,
    addItem,
    updateQuantity,
    removeItem,
    clearCart,
    toggleCart,
    closeCart,
    checkout,
  }
}
