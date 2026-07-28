<script setup lang="ts">
import { X } from '@lucide/vue'

withDefaults(defineProps<{
  open: boolean
  title?: string
  side?: 'left' | 'right'
  width?: string
}>(), {
  side: 'right',
  width: 'max-w-md',
})

const emit = defineEmits<{ close: [] }>()
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
      <div v-if="open" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm" @click="emit('close')"></div>
    </Transition>
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      :enter-from-class="side === 'right' ? 'translate-x-full' : '-translate-x-full'"
      enter-to-class="translate-x-0"
      :leave-from-class="side === 'right' ? 'translate-x-0' : 'translate-x-0'"
      :leave-to-class="side === 'right' ? 'translate-x-full' : '-translate-x-full'"
    >
      <div v-if="open"
        :class="`fixed inset-y-0 z-50 flex w-full ${width} flex-col border-l border-white/10 bg-[#0d111c] shadow-2xl ${side === 'right' ? 'right-0' : 'left-0'}`">
        <div v-if="title" class="flex items-center justify-between border-b border-white/10 px-6 py-4">
          <h2 class="text-base font-semibold text-white">{{ title }}</h2>
          <button @click="emit('close')" class="rounded-lg p-1.5 text-gray-500 hover:bg-white/5 hover:text-white transition-all cursor-pointer">
            <X class="h-5 w-5" />
          </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
