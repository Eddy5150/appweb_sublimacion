<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { X } from '@lucide/vue'

const props = withDefaults(defineProps<{
  show: boolean
  title?: string
  size?: 'sm' | 'md' | 'lg' | 'xl' | 'full'
  closeable?: boolean
}>(), {
  size: 'md',
  closeable: true,
})

const emit = defineEmits<{
  close: []
}>()

const sizes: Record<string, string> = {
  sm: 'max-w-sm',
  md: 'max-w-lg',
  lg: 'max-w-2xl',
  xl: 'max-w-4xl',
  full: 'max-w-[95vw]',
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape' && props.closeable) emit('close')
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      leave-active-class="transition-all duration-150 ease-in"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="closeable && emit('close')"></div>
        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          leave-active-class="transition-all duration-150 ease-in"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div v-if="show" :class="`relative w-full ${sizes[size]} rounded-2xl border border-white/10 bg-[#0d111c] shadow-2xl shadow-black/50`">
            <div v-if="title" class="flex items-center justify-between border-b border-white/10 px-6 py-4">
              <h3 class="text-base font-semibold text-white">{{ title }}</h3>
              <button v-if="closeable" @click="emit('close')" class="rounded-lg p-1.5 text-gray-500 hover:bg-white/5 hover:text-white transition-all cursor-pointer">
                <X class="h-4 w-4" />
              </button>
            </div>
            <div class="p-6">
              <slot />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
