<script setup lang="ts">
import { cn } from '@/lib/utils'
import { ChevronLeft, ChevronRight } from '@lucide/vue'

const props = defineProps<{
  links: {
    url: string | null
    label: string
    active: boolean
  }[]
  class?: string
}>()

function isNavLink(i: number): boolean {
  return i === 0 || i === props.links.length - 1
}

function displayLabel(label: string): string {
  const skip = ['pagination.previous', '&laquo; Previous', 'pagination.next', 'Next &raquo;']
  return skip.includes(label) ? '' : label
}
</script>

<template v-if="props.links.length > 3">
  <div :class="cn('mt-6 flex items-center justify-center gap-1', props.class)">
    <Link
      v-for="(link, i) in props.links"
      :key="i"
      :href="link.url ?? '#'"
      :class="cn(
        'inline-flex h-9 w-9 items-center justify-center rounded-lg text-sm transition-colors',
        link.active
          ? 'bg-primary text-primary-foreground'
          : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
        !link.url && 'pointer-events-none opacity-50'
      )"
    >
      <ChevronLeft v-if="i === 0" class="h-4 w-4" />
      <ChevronRight v-else-if="i === props.links.length - 1" class="h-4 w-4" />
      <span v-else>{{ displayLabel(link.label) }}</span>
    </Link>
  </div>
</template>
