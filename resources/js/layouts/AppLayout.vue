<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Toaster } from 'vue-sonner'
import {
  LayoutDashboard,
  Package,
  ClipboardList,
  Users,
  BarChart3,
  Shield,
  UserCog,
  BookOpen,
  LogOut,
  BrainCircuit,
  Bell,
  Settings2,
  History,
  MessageSquareText,
  ChevronDown,
  Truck,
  ShoppingCart,
  Building2,
  CreditCard,
  Globe,
  DollarSign,
  Wallet,
  Activity,
} from '@lucide/vue'

const page = usePage()
const user = computed(() => page.props.auth?.user as any)
const company = computed(() => (page.props as any).company as any)
const planLimits = computed(() => (page.props as any).planLimits as any)
const isAdmin = computed(() => user.value?.role === 'admin')
const crmOpen = ref(false)
const erpOpen = ref(false)

onMounted(() => {
  crmOpen.value =
    page.url.startsWith('/crm/automations') ||
    page.url.startsWith('/crm/alerts') ||
    page.url.startsWith('/crm/events') ||
    page.url.startsWith('/crm/comunicaciones') ||
    page.url.startsWith('/crm/ai')
  erpOpen.value =
    page.url.startsWith('/proveedores') ||
    page.url.startsWith('/compras')
})

const navigation = computed(() => [
  { name: 'Panel', href: route('panel'), icon: LayoutDashboard },
  { name: 'Catálogo', href: route('catalogo'), icon: BookOpen },
  { name: 'Productos', href: route('productos.index'), icon: Package },
  { name: 'Pedidos', href: route('pedidos.index'), icon: ClipboardList },
  { name: 'Clientes', href: route('clientes.index'), icon: Users },
  { name: 'CRM', href: route('crm'), icon: BrainCircuit },
  { name: 'Facturación', href: route('company.billing'), icon: DollarSign },
  { name: 'Caja', href: route('caja.index'), icon: Wallet },
  { name: 'Actividad', href: route('activity.index'), icon: Activity },
  { name: 'Compras', href: '#', icon: ShoppingCart, submenu: 'erp' },
  { name: 'Reportes', href: route('reportes'), icon: BarChart3 },
  ...(isAdmin.value
    ? [
        { name: 'Roles', href: route('roles.index'), icon: Shield },
        { name: 'Usuarios', href: route('usuarios.index'), icon: UserCog },
      ]
    : []),
])

const crmSubnav = computed(() => [
  { name: 'Dashboard', href: route('crm'), icon: BarChart3 },
  { name: 'IA CRM', href: route('crm.ai'), icon: BrainCircuit },
  { name: 'Alertas', href: route('crm.alerts'), icon: Bell },
  { name: 'Automatizaciones', href: route('crm.automations'), icon: Settings2 },
  { name: 'Eventos', href: route('crm.events'), icon: History },
  { name: 'Mensajes', href: route('crm.comunicaciones'), icon: MessageSquareText },
])

const erpSubnav = computed(() => [
  { name: 'Proveedores', href: route('proveedores.index'), icon: Truck },
  { name: 'Compras', href: route('compras.index'), icon: ShoppingCart },
])

function logout() {
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = route('logout')
  document.body.appendChild(form)
  form.submit()
}

function getInitials(name: string): string {
  return name
    ?.split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2) || '?'
}
</script>

<template>
  <div class="flex h-screen bg-[#020817]">
    <Toaster position="top-right" rich-colors close-button />

    <aside
      class="fixed inset-y-0 left-0 z-40 flex w-[260px] flex-col border-r border-white/10"
      style="background: var(--glass-heavy); backdrop-filter: blur(20px);"
    >
      <!-- Logo & branding -->
      <div class="flex flex-col items-center px-6 pb-6 pt-8">
        <div
          class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 via-fuchsia-500 to-orange-400 shadow-lg shadow-fuchsia-600/40"
        >
          <span class="text-xl font-bold text-white">{{ company?.nombre?.charAt(0) || 'E' }}</span>
        </div>
        <h1 class="mt-4 text-center text-sm font-semibold text-white">
          {{ company?.nombre || 'Mi Empresa' }}
        </h1>
        <p class="mt-1 text-center text-xs text-gray-400">
          {{ company?.ciudad || company?.pais || 'SaaS ERP' }}
        </p>
        <div v-if="planLimits" class="mt-2 flex items-center gap-1.5 rounded-full bg-white/5 px-3 py-1">
          <span class="text-xs font-medium text-fuchsia-400">{{ planLimits.plan }}</span>
          <span class="text-xs text-gray-500">·</span>
          <span class="text-xs text-gray-400">{{ planLimits.limits?.usuarios?.actual }}/{{ planLimits.limits?.usuarios?.max }} users</span>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 space-y-1 overflow-y-auto px-3">
        <template v-for="item in navigation" :key="item.name">
          <div v-if="item.name === 'CRM'" class="space-y-1">
            <button
              @click="crmOpen = !crmOpen"
              class="nav-item flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200"
              :class="
                $page.url.startsWith('/crm')
                  ? 'active text-white'
                  : 'text-gray-400'
              "
            >
              <component :is="item.icon" class="h-5 w-5 shrink-0" />
              {{ item.name }}
              <ChevronDown
                class="ml-auto h-4 w-4 transition-transform duration-200"
                :class="crmOpen ? '' : '-rotate-90'"
              />
            </button>
            <div v-if="crmOpen" class="ml-3 space-y-0.5 border-l border-white/10 pl-3">
              <Link
                v-for="sub in crmSubnav"
                :key="sub.name"
                :href="sub.href"
                class="nav-item flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200"
                :class="$page.url === sub.href ? 'active text-white' : 'text-gray-400'"
              >
                <component :is="sub.icon" class="h-4 w-4 shrink-0" />
                {{ sub.name }}
              </Link>
            </div>
          </div>
          <div v-else-if="item.submenu === 'erp'" class="space-y-1">
            <button
              @click="erpOpen = !erpOpen"
              class="nav-item flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200"
              :class="
                $page.url.startsWith('/proveedores') || $page.url.startsWith('/compras')
                  ? 'active text-white'
                  : 'text-gray-400'
              "
            >
              <component :is="item.icon" class="h-5 w-5 shrink-0" />
              {{ item.name }}
              <ChevronDown
                class="ml-auto h-4 w-4 transition-transform duration-200"
                :class="erpOpen ? '' : '-rotate-90'"
              />
            </button>
            <div v-if="erpOpen" class="ml-3 space-y-0.5 border-l border-white/10 pl-3">
              <Link
                v-for="sub in erpSubnav"
                :key="sub.name"
                :href="sub.href"
                class="nav-item flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-200"
                :class="$page.url === sub.href ? 'active text-white' : 'text-gray-400'"
              >
                <component :is="sub.icon" class="h-4 w-4 shrink-0" />
                {{ sub.name }}
              </Link>
            </div>
          </div>
          <Link
            v-else
            :href="item.href"
            class="nav-item flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200"
            :class="
              $page.url === item.href ||
              (item.href !== '/' && $page.url.startsWith(item.href + '/'))
                ? 'active text-white'
                : 'text-gray-400'
            "
          >
            <component :is="item.icon" class="h-5 w-5 shrink-0" />
            {{ item.name }}
          </Link>
        </template>
      </nav>

      <!-- User -->
      <div class="border-t border-white/10 px-4 py-4">
        <div class="flex items-center gap-3">
          <div
            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500/20 to-fuchsia-500/20 text-sm font-semibold text-fuchsia-400 ring-1 ring-white/10"
          >
            {{ getInitials(user?.name) }}
          </div>
          <div class="flex min-w-0 flex-col">
            <span class="truncate text-sm font-medium text-white">
              {{ user?.name }}
            </span>
            <span class="truncate text-xs text-gray-400">
              {{ user?.role }}
            </span>
          </div>
          <div class="ml-auto flex items-center gap-1">
            <Link :href="route('company.settings')" class="rounded-lg p-1.5 text-gray-500 transition-colors hover:bg-white/5 hover:text-white" title="Configuración">
              <Settings2 class="h-4 w-4" />
            </Link>
            <button
              @click="logout"
              class="rounded-lg p-1.5 text-gray-500 transition-colors hover:bg-white/5 hover:text-white"
            >
              <LogOut class="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main content -->
    <div class="flex flex-1 flex-col pl-[260px]">
      <main class="flex-1 overflow-y-auto p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<style scoped>
.nav-item {
  transition: all 0.3s ease;
}
.nav-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #fff;
}
.nav-item.active {
  background: rgba(168, 85, 247, 0.15);
  box-shadow: inset 0 0 20px rgba(168, 85, 247, 0.2);
}
</style>
