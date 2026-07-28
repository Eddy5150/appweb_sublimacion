<script setup lang="ts">
withDefaults(defineProps<{
  label: string
  value: string | number
  icon?: any
  trend?: number
  trendLabel?: string
  color?: string
}>(), {
  color: 'from-cyan-500 to-blue-500',
})
</script>

<template>
  <div class="group relative overflow-hidden rounded-2xl border border-white/[0.04] bg-[#0d111c] p-5 transition-all duration-300 hover:border-white/20">
    <div class="flex items-start justify-between">
      <div class="space-y-1">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ label }}</p>
        <p class="text-2xl font-bold text-white">{{ value }}</p>
        <div v-if="trend !== undefined" class="flex items-center gap-1.5">
          <span :class="trend >= 0 ? 'text-emerald-400' : 'text-red-400'" class="text-xs font-medium">
            {{ trend >= 0 ? '+' : '' }}{{ trend }}%
          </span>
          <span v-if="trendLabel" class="text-xs text-gray-500">{{ trendLabel }}</span>
        </div>
      </div>
      <div v-if="icon" :class="`flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br ${color} shadow-lg`">
        <component :is="icon" class="h-5 w-5 text-white" />
      </div>
    </div>
  </div>
</template>
