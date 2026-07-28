<script setup lang="ts">
import { ShoppingCart, Check } from '@lucide/vue'
import { ref } from 'vue'
import { useCart } from '@/composables/useCart'

const props = withDefaults(defineProps<{
  product: { id: number; nombre: string; precio: number; imagen?: string }
  variant?: string
  disabled?: boolean
  label?: string
}>(), {
  label: 'Agregar al carrito',
})

const { addItem } = useCart()
const added = ref(false)

function handleClick() {
  if (props.disabled) return
  addItem(props.product, 1, props.variant)
  added.value = true
  setTimeout(() => { added.value = false }, 1500)
}
</script>

<template>
  <button @click="handleClick" :disabled="disabled"
    class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all cursor-pointer"
    :class="added
      ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
      : disabled
        ? 'bg-white/5 text-gray-600 cursor-not-allowed border border-white/10'
        : 'bg-gradient-to-r from-purple-500 to-fuchsia-500 text-white hover:from-purple-400 hover:to-fuchsia-400 shadow-lg shadow-fuchsia-500/20 border border-transparent'">
    <component :is="added ? Check : ShoppingCart" class="h-4 w-4" />
    {{ added ? 'Agregado' : label }}
  </button>
</template>
