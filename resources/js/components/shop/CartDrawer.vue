<script setup lang="ts">
import { ShoppingCart, X, Plus, Minus, Trash2, ArrowRight, Package } from '@lucide/vue'
import { usePage } from '@inertiajs/vue3'
import { useCart } from '@/composables/useCart'
import { formatCurrency } from '@/lib/format'

const { items, open, count, subtotal, isEmpty, closeCart, updateQuantity, removeItem, clearCart, checkout } = useCart()
const page = usePage()
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="open" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm" @click="closeCart"></div>
    </Transition>

    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="translate-x-full opacity-0"
    >
      <div v-if="open"
        class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col border-l border-white/10 bg-[#0d111c] shadow-2xl">
        <div class="flex items-center justify-between border-b border-white/10 px-6 py-4">
          <div class="flex items-center gap-2">
            <ShoppingCart class="h-5 w-5 text-cyan-400" />
            <h2 class="text-base font-semibold text-white">Carrito ({{ count }})</h2>
          </div>
          <button @click="closeCart" class="rounded-lg p-1.5 text-gray-500 hover:bg-white/5 hover:text-white transition-all cursor-pointer">
            <X class="h-5 w-5" />
          </button>
        </div>

        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3">
          <div v-if="isEmpty" class="flex flex-col items-center justify-center h-full text-center">
            <Package class="h-16 w-16 text-gray-600 mb-4" />
            <p class="text-gray-500 font-medium">Carrito vacío</p>
            <p class="text-sm text-gray-600 mt-1">Agrega productos para empezar</p>
          </div>

          <TransitionGroup v-else name="cart-item" tag="div" class="space-y-3">
            <div v-for="item in items" :key="`${item.producto_id}-${item.variante || ''}`"
              class="flex gap-4 rounded-xl border border-white/[0.04] bg-white/[0.02] p-3 transition-all hover:border-white/10">
              <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500/10 to-fuchsia-500/10">
                <Package class="h-6 w-6 text-fuchsia-400" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ item.nombre }}</p>
                <p v-if="item.variante" class="text-xs text-gray-500 mt-0.5">{{ item.variante }}</p>
                <p class="text-sm font-semibold text-cyan-400 mt-1">{{ formatCurrency(item.precio) }}</p>
                <div class="flex items-center gap-2 mt-2">
                  <div class="flex items-center rounded-lg border border-white/10 bg-white/[0.03]">
                    <button @click="updateQuantity(item.producto_id, item.cantidad - 1, item.variante)"
                      class="flex h-7 w-7 items-center justify-center text-gray-400 hover:text-white transition-colors cursor-pointer">
                      <Minus class="h-3 w-3" />
                    </button>
                    <span class="w-8 text-center text-xs font-medium text-white">{{ item.cantidad }}</span>
                    <button @click="updateQuantity(item.producto_id, item.cantidad + 1, item.variante)"
                      class="flex h-7 w-7 items-center justify-center text-gray-400 hover:text-white transition-colors cursor-pointer">
                      <Plus class="h-3 w-3" />
                    </button>
                  </div>
                  <button @click="removeItem(item.producto_id, item.variante)"
                    class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 hover:bg-red-500/10 hover:text-red-400 transition-colors cursor-pointer">
                    <Trash2 class="h-3.5 w-3.5" />
                  </button>
                </div>
              </div>
            </div>
          </TransitionGroup>
        </div>

        <div v-if="!isEmpty" class="border-t border-white/10 px-6 py-4 space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-sm text-gray-400">Subtotal</span>
            <span class="text-lg font-bold text-white">{{ formatCurrency(subtotal) }}</span>
          </div>
          <div class="flex gap-2">
            <button @click="clearCart"
              class="flex-1 rounded-xl border border-white/10 px-4 py-2.5 text-sm font-medium text-gray-400 hover:bg-white/5 hover:text-white transition-all cursor-pointer">
              Limpiar
            </button>
            <button @click="checkout()"
              class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-sky-500 px-4 py-2.5 text-sm font-semibold text-white hover:from-cyan-400 hover:to-sky-400 shadow-lg shadow-cyan-500/20 transition-all cursor-pointer">
              Confirmar Pedido <ArrowRight class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.cart-item-enter-active, .cart-item-leave-active {
  transition: all 0.3s ease;
}
.cart-item-enter-from {
  opacity: 0;
  transform: translateX(20px);
}
.cart-item-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}
</style>
