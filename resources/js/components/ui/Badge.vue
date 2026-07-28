<script setup lang="ts">
withDefaults(defineProps<{
  variant?: 'default' | 'success' | 'warning' | 'danger' | 'info' | 'purple'
  size?: 'sm' | 'md'
}>(), {
  variant: 'default',
  size: 'sm',
})

const styles: Record<string, string> = {
  default: 'bg-gray-500/10 text-gray-400',
  success: 'bg-emerald-500/10 text-emerald-400',
  warning: 'bg-amber-500/10 text-amber-400',
  danger: 'bg-red-500/10 text-red-400',
  info: 'bg-cyan-500/10 text-cyan-400',
  purple: 'bg-purple-500/10 text-purple-400',
}
</script>

<template>
  <span :class="`inline-flex items-center rounded-full font-medium ${styles[variant]} ${size === 'sm' ? 'px-2 py-0.5 text-[10px]' : 'px-3 py-1 text-xs'}`">
    <slot />
  </span>
</template>
