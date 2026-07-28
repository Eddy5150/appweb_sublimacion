<script setup lang="ts">
import { computed } from 'vue'
import {
  Search,
  ChevronLeft,
  ChevronRight,
  ArrowUpDown,
  ArrowUp,
  ArrowDown
} from '@lucide/vue'

export interface Column {
  key: string
  label: string
  sortable?: boolean
  width?: string
}

const props = withDefaults(defineProps<{
  columns: Column[]
  rows: any[]
  search?: string
  loading?: boolean
  sortKey?: string
  sortDir?: 'asc' | 'desc'
  meta?: {
    current_page?: number
    last_page?: number
    total?: number
    from?: number
    to?: number
  }
}>(), {
  rows: () => [],
  loading: false,
  sortDir: 'asc'
})

const emit = defineEmits<{
  (e: 'update:search', value: string): void
  (e: 'update:sortKey', value: string): void
  (e: 'update:sortDir', value: 'asc' | 'desc'): void
  (e: 'page', value: number): void
  (e: 'row-click', value: any): void
}>()

function toggleSort(key: string) {
  if (props.sortKey === key) {
    emit('update:sortDir', props.sortDir === 'asc' ? 'desc' : 'asc')
  } else {
    emit('update:sortKey', key)
    emit('update:sortDir', 'asc')
  }
}

function getSortIcon(key: string) {
  if (props.sortKey !== key) return ArrowUpDown
  return props.sortDir === 'asc' ? ArrowUp : ArrowDown
}

const pages = computed(() => {
  if (!props.meta?.last_page) return []

  const p = props.meta.last_page
  const c = props.meta.current_page || 1

  if (p <= 7) return Array.from({ length: p }, (_, i) => i + 1)

  const arr: number[] = [1]

  const start = Math.max(2, c - 1)
  const end = Math.min(p - 1, c + 1)

  if (start > 2) arr.push(-1)

  for (let i = start; i <= end; i++) arr.push(i)

  if (end < p - 1) arr.push(-1)

  arr.push(p)

  return arr
})
</script>

<template>
  <div class="space-y-4">

    <!-- SEARCH -->
    <div v-if="search !== undefined" class="relative">
      <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-500" />

      <input
        :value="search"
        @input="emit('update:search', ($event.target as HTMLInputElement).value)"
        placeholder="Buscar..."
        class="w-full rounded-xl border border-white/[0.08] bg-white/[0.03] pl-10 pr-4 py-2.5 text-sm text-white outline-none focus:border-cyan-500/50 transition-all"
      />
    </div>

    <!-- TABLE -->
    <div class="overflow-hidden rounded-2xl border border-white/[0.04]">
      <div class="overflow-x-auto">
        <table class="w-full">

          <thead>
            <tr class="border-b border-white/[0.04] bg-white/[0.02]">

              <th
                v-for="col in columns"
                :key="col.key"
                @click="col.sortable && toggleSort(col.key)"
                :style="col.width ? { width: col.width } : {}"
                :class="col.sortable ? 'cursor-pointer hover:text-white' : ''"
                class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider transition-colors"
              >
                <div class="flex items-center gap-1.5">
                  {{ col.label }}
                  <component
                    v-if="col.sortable"
                    :is="getSortIcon(col.key)"
                    class="h-3 w-3"
                  />
                </div>
              </th>

            </tr>
          </thead>

          <tbody class="divide-y divide-white/[0.02]">

            <tr
              v-for="(row, i) in rows"
              :key="i"
              @click="emit('row-click', row)"
              class="transition-colors hover:bg-white/[0.02] cursor-pointer"
            >
              <td
                v-for="col in columns"
                :key="col.key"
                class="px-4 py-3 text-sm text-gray-300 whitespace-nowrap"
              >
                <slot
                  :name="`cell-${col.key}`"
                  :row="row"
                  :value="row[col.key]"
                >
                  {{ row[col.key] ?? '-' }}
                </slot>
              </td>
            </tr>

            <tr v-if="rows.length === 0">
              <td :colspan="columns.length" class="px-4 py-12 text-center text-sm text-gray-600">
                {{ loading ? 'Cargando...' : 'Sin resultados' }}
              </td>
            </tr>

          </tbody>

        </table>
      </div>
    </div>

    <!-- PAGINATION -->
    <div v-if="meta" class="flex items-center justify-between">

      <p class="text-xs text-gray-500">
        Mostrando {{ meta.from || 0 }} - {{ meta.to || 0 }} de {{ meta.total || 0 }}
      </p>

      <div v-if="meta.last_page && meta.last_page > 1" class="flex items-center gap-1">

        <button
          @click="emit('page', (meta.current_page || 1) - 1)"
          :disabled="(meta.current_page || 1) <= 1"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-white/5 hover:text-white transition-all disabled:opacity-30"
        >
          <ChevronLeft class="h-4 w-4" />
        </button>

        <button
          v-for="p in pages"
          :key="p"
          @click="p > 0 && emit('page', p)"
          :class="p === (meta.current_page || 1)
            ? 'bg-cyan-500/20 text-cyan-400'
            : 'text-gray-500 hover:text-white'"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-sm transition-all"
        >
          {{ p > 0 ? p : '...' }}
        </button>

        <button
          @click="emit('page', (meta.current_page || 1) + 1)"
          :disabled="(meta.current_page || 1) >= (meta.last_page || 1)"
          class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-white/5 hover:text-white transition-all disabled:opacity-30"
        >
          <ChevronRight class="h-4 w-4" />
        </button>

      </div>

    </div>

  </div>
</template>
