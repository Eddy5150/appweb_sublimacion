<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Toaster } from 'vue-sonner'
import {
  ArrowRight, LogIn, Package, ShoppingCart, BarChart3,
  Users, BrainCircuit, Shield, Check, Zap, DollarSign,
  MessageCircle, Star, ChevronRight, Building2, Globe,
} from '@lucide/vue'

const plans = [
  {
    name: 'Free',
    price: '0',
    period: '/mes',
    desc: 'Para emprender',
    features: ['Hasta 20 productos', 'Hasta 50 clientes', '1 usuario', 'Catálogo público', 'Panel básico'],
    cta: 'Comenzar gratis',
    highlight: false,
    color: 'from-gray-500 to-gray-400',
  },
  {
    name: 'Starter',
    price: '99',
    period: '/mes',
    desc: 'Para pequeños negocios',
    features: ['Hasta 100 productos', 'Hasta 200 clientes', '3 usuarios', 'CRM básico', 'Proveedores', 'Compras', 'Exportación de datos'],
    cta: 'Elegir Starter',
    highlight: true,
    color: 'from-purple-500 to-fuchsia-500',
  },
  {
    name: 'Pro',
    price: '199',
    period: '/mes',
    desc: 'Para negocios en crecimiento',
    features: ['Hasta 500 productos', 'Hasta 1000 clientes', '10 usuarios', 'CRM con IA', 'Reportes avanzados', 'API integraciones', 'Soporte prioritario'],
    cta: 'Elegir Pro',
    highlight: false,
    color: 'from-cyan-500 to-blue-500',
  },
  {
    name: 'Enterprise',
    price: '399',
    period: '/mes',
    desc: 'Para empresas grandes',
    features: ['Productos ilimitados', 'Clientes ilimitados', '50 usuarios', 'CRM completo con IA', 'Todo incluido', 'Soporte 24/7', 'Onboarding personalizado'],
    cta: 'Contactar',
    highlight: false,
    color: 'from-amber-500 to-orange-500',
  },
]

const features = [
  { icon: Package, title: 'Gestión de Productos', desc: 'Control total de inventario, precios y variantes. Stock en tiempo real.' },
  { icon: ShoppingCart, title: 'Pedidos & Ventas', desc: 'Registro de pedidos, carrito de compras y seguimiento de estado.' },
  { icon: Users, title: 'CRM Inteligente', desc: 'Segmentación de clientes, scoring IA, automatizaciones y alertas.' },
  { icon: BarChart3, title: 'Reportes Avanzados', desc: 'Gráficos de ventas, exportación PDF/Excel, métricas en tiempo real.' },
  { icon: BrainCircuit, title: 'IA & Automación', desc: 'Predicciones de ventas, recomendaciones, agente IA para CRM.' },
  { icon: Shield, title: 'Multi-empresa Seguro', desc: 'Datos aislados por empresa. Subdominios personalizados.' },
]
</script>

<template>
  <div class="min-h-screen bg-[#020817]">
    <Toaster position="top-right" rich-colors close-button />
    <Head title="SaaS ERP · Sistema de Gestión Empresarial" />

    <header class="fixed top-0 z-50 w-full border-b border-white/10 bg-[#020817]/80 backdrop-blur-xl">
      <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
        <div class="flex items-center gap-3">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500 to-fuchsia-500">
            <Zap class="h-4 w-4 text-white" />
          </div>
          <span class="text-lg font-bold text-white">SaaS <span class="text-fuchsia-400">ERP</span></span>
        </div>
        <nav class="hidden items-center gap-6 md:flex">
          <a href="#features" class="text-sm text-gray-400 hover:text-white transition-colors">Funcionalidades</a>
          <a href="#pricing" class="text-sm text-gray-400 hover:text-white transition-colors">Planes</a>
          <Link href="/login" class="text-sm text-gray-400 hover:text-white transition-colors">Iniciar Sesión</Link>
          <Link href="/register" class="rounded-lg bg-gradient-to-r from-purple-500 to-fuchsia-500 px-4 py-2 text-sm font-medium text-white hover:from-purple-400 hover:to-fuchsia-400 transition-all shadow-lg shadow-fuchsia-500/20">
            Crear Cuenta
          </Link>
        </nav>
      </div>
    </header>

    <section class="relative overflow-hidden pt-32 pb-20">
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(168,85,247,0.15),transparent_70%)]"></div>
      <div class="mx-auto max-w-7xl px-6 text-center relative z-10">
        <div class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[0.03] px-4 py-1.5 text-sm text-gray-400 mb-8">
          <Star class="h-3.5 w-3.5 text-amber-400" />
          SaaS ERP Profesional para Sublimación y Retail
        </div>
        <h1 class="text-5xl font-extrabold tracking-tight text-white sm:text-7xl">
          Gestiona tu negocio<br>
          <span class="bg-gradient-to-r from-purple-400 via-fuchsia-400 to-orange-400 bg-clip-text text-transparent">como un Enterprise</span>
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-400">
          ERP + CRM + Ecommerce en un solo lugar. Controla ventas, inventario, clientes con IA y haz crecer tu negocio.
          Listo para empresas de sublimación, retail y servicios.
        </p>
        <div class="mt-10 flex items-center justify-center gap-4">
          <Link href="/register" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-purple-500 to-fuchsia-500 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-fuchsia-500/30 hover:from-purple-400 hover:to-fuchsia-400 transition-all">
            Comenzar Gratis <ArrowRight class="h-5 w-5" />
          </Link>
          <Link href="/catalogo" class="inline-flex items-center gap-2 rounded-xl border border-white/20 px-8 py-3.5 text-base font-semibold text-white hover:bg-white/5 transition-all">
            Ver Demo <ChevronRight class="h-5 w-5" />
          </Link>
        </div>
        <div class="mt-16 grid grid-cols-2 gap-6 sm:grid-cols-4">
          <div class="rounded-xl border border-white/[0.04] bg-[#0d111c] p-4">
            <p class="text-2xl font-bold text-white">ERP</p>
            <p class="text-xs text-gray-500 mt-1">Gestión Completa</p>
          </div>
          <div class="rounded-xl border border-white/[0.04] bg-[#0d111c] p-4">
            <p class="text-2xl font-bold text-white">CRM</p>
            <p class="text-xs text-gray-500 mt-1">Con IA Integrada</p>
          </div>
          <div class="rounded-xl border border-white/[0.04] bg-[#0d111c] p-4">
            <p class="text-2xl font-bold text-white">Ecommerce</p>
            <p class="text-xs text-gray-500 mt-1">Catálogo Online</p>
          </div>
          <div class="rounded-xl border border-white/[0.04] bg-[#0d111c] p-4">
            <p class="text-2xl font-bold text-white">SaaS</p>
            <p class="text-xs text-gray-500 mt-1">Multi-empresa</p>
          </div>
        </div>
      </div>
    </section>

    <section id="features" class="py-20">
      <div class="mx-auto max-w-7xl px-6">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-white sm:text-4xl">Todo lo que necesitas en un solo lugar</h2>
          <p class="mt-4 text-gray-400">ERP + CRM + Ecommerce + IA. Un sistema completo para tu negocio.</p>
        </div>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div v-for="f in features" :key="f.title" class="group rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6 transition-all duration-300 hover:border-fuchsia-500/30 hover:shadow-lg hover:shadow-fuchsia-500/5">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-purple-500/10 to-fuchsia-500/10 mb-4 group-hover:from-purple-500/20 group-hover:to-fuchsia-500/20 transition-all">
              <component :is="f.icon" class="h-6 w-6 text-fuchsia-400" />
            </div>
            <h3 class="text-lg font-semibold text-white">{{ f.title }}</h3>
            <p class="mt-2 text-sm text-gray-400">{{ f.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <section id="pricing" class="py-20">
      <div class="mx-auto max-w-7xl px-6">
        <div class="text-center mb-16">
          <h2 class="text-3xl font-bold text-white sm:text-4xl">Planes para cada negocio</h2>
          <p class="mt-4 text-gray-400">Desde empezar hasta escalar. Todos los planes incluyen 14 días de prueba gratis.</p>
        </div>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">
          <div v-for="plan in plans" :key="plan.name" class="relative rounded-2xl border border-white/[0.04] bg-[#0d111c] p-6 transition-all duration-300 hover:border-white/20"
            :class="plan.highlight ? 'ring-2 ring-fuchsia-500/50 scale-[1.02]' : ''">
            <div v-if="plan.highlight" class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-gradient-to-r from-purple-500 to-fuchsia-500 px-3 py-0.5 text-xs font-medium text-white">
              Más popular
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br" :class="plan.color">
              <Zap class="h-5 w-5 text-white" />
            </div>
            <h3 class="mt-4 text-lg font-semibold text-white">{{ plan.name }}</h3>
            <p class="text-xs text-gray-500">{{ plan.desc }}</p>
            <div class="mt-4 flex items-baseline gap-1">
              <span class="text-3xl font-bold text-white">${{ plan.price }}</span>
              <span class="text-sm text-gray-500">{{ plan.period }}</span>
            </div>
            <ul class="mt-6 space-y-3">
              <li v-for="f in plan.features" :key="f" class="flex items-start gap-2">
                <Check class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" />
                <span class="text-sm text-gray-300">{{ f }}</span>
              </li>
            </ul>
            <Link :href="plan.name === 'Enterprise' ? 'https://wa.me/591XXXXXXXXX?text=Quiero%20información%20del%20plan%20Enterprise' : '/register'"
              class="mt-6 flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all"
              :class="plan.highlight ? 'bg-gradient-to-r from-purple-500 to-fuchsia-500 text-white hover:from-purple-400 hover:to-fuchsia-400 shadow-lg shadow-fuchsia-500/20' : 'border border-white/20 text-white hover:bg-white/5'">
              {{ plan.cta }}
            </Link>
          </div>
        </div>
      </div>
    </section>

    <section class="py-20 border-t border-white/[0.04]">
      <div class="mx-auto max-w-7xl px-6 text-center">
        <div class="mx-auto max-w-2xl">
          <h2 class="text-3xl font-bold text-white sm:text-4xl">¿Listo para transformar tu negocio?</h2>
          <p class="mt-4 text-gray-400">Comienza hoy con 14 días de prueba gratis. Sin tarjeta de crédito.</p>
          <div class="mt-8 flex items-center justify-center gap-4">
            <Link href="/register" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-purple-500 to-fuchsia-500 px-8 py-3.5 text-base font-semibold text-white shadow-lg shadow-fuchsia-500/30 hover:from-purple-400 hover:to-fuchsia-400 transition-all">
              Crear Cuenta Gratis <ArrowRight class="h-5 w-5" />
            </Link>
            <a href="https://wa.me/591XXXXXXXXX?text=Quiero%20información%20sobre%20SaaS%20ERP" target="_blank" class="inline-flex items-center gap-2 rounded-xl border border-white/20 px-8 py-3.5 text-base font-semibold text-white hover:bg-white/5 transition-all">
              <MessageCircle class="h-5 w-5 text-emerald-400" /> WhatsApp
            </a>
          </div>
        </div>
      </div>
    </section>

    <footer class="border-t border-white/[0.04] py-12">
      <div class="mx-auto max-w-7xl px-6">
        <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
          <div class="flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500 to-fuchsia-500">
              <Zap class="h-4 w-4 text-white" />
            </div>
            <span class="text-sm font-semibold text-white">SaaS ERP</span>
          </div>
          <div class="flex items-center gap-6">
            <a href="https://wa.me/591XXXXXXXXX" target="_blank" class="text-sm text-gray-500 hover:text-emerald-400 transition-colors">WhatsApp</a>
            <Link href="/login" class="text-sm text-gray-500 hover:text-white transition-colors">Iniciar Sesión</Link>
            <Link href="/register" class="text-sm text-gray-500 hover:text-white transition-colors">Registro</Link>
          </div>
          <p class="text-xs text-gray-600">&copy; {{ new Date().getFullYear() }} SaaS ERP. Todos los derechos reservados.</p>
        </div>
      </div>
    </footer>
  </div>
</template>
