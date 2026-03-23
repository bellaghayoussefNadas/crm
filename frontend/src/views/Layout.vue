<template>
  <div class="min-h-screen bg-gray-100 flex flex-col sm:flex-row">
    <!-- Sidebar -->
    <div class="w-full sm:w-64 bg-indigo-900 text-white min-h-screen flex flex-col shadow-2xl z-20">
      <div class="h-20 flex items-center px-6 border-b border-indigo-800 bg-indigo-900/50 backdrop-blur-md sticky top-0">
        <div class="flex items-center space-x-3 group cursor-pointer" @click="router.push('/')">
          <img v-if="settings.logo_url" :src="settings.logo_url" class="h-10 w-10 rounded-xl shadow-lg ring-2 ring-white/20 transition group-hover:scale-110" />
          <div v-else class="h-10 w-10 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-xl flex items-center justify-center shadow-lg group-hover:rotate-12 transition">
            <span class="text-xl">🚀</span>
          </div>
          <span class="text-lg font-black tracking-tighter text-white group-hover:text-indigo-300 transition truncate">{{ settings.site_name }}</span>
        </div>
      </div>

      <nav class="flex-1 px-4 py-6 space-y-6 overflow-y-auto custom-scrollbar">
        <!-- Main -->
        <div>
          <p class="px-2 text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-3 opacity-80">{{ t('navigation') }}</p>
          <ul class="space-y-1">
            <li><router-link to="/" class="sidebar-link" :class="{ 'active': $route.name === 'Dashboard' }">📊 {{ t('dashboard') }}</router-link></li>
            <li><router-link to="/accounts" class="sidebar-link" :class="{ 'active': $route.path.startsWith('/accounts') }">🏢 {{ t('accounts') }}</router-link></li>
            <li><router-link to="/contacts" class="sidebar-link" :class="{ 'active': $route.path.startsWith('/contacts') }">👤 {{ t('contacts') }}</router-link></li>
            <li><router-link to="/leads" class="sidebar-link" :class="{ 'active': $route.name === 'Leads' }">🎯 {{ t('leads') }}</router-link></li>
            <li><router-link to="/deals" class="sidebar-link" :class="{ 'active': $route.name === 'Deals' }">💰 {{ t('pipeline') }}</router-link></li>
          </ul>
        </div>

        <!-- Finance -->
        <div>
          <p class="px-2 text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-3 opacity-80">{{ t('finance') }}</p>
          <ul class="space-y-1">
            <li><router-link to="/quotes" class="sidebar-link" :class="{ 'active': $route.path.startsWith('/quotes') }">📝 {{ t('quotes') }}</router-link></li>
            <li><router-link to="/invoices" class="sidebar-link" :class="{ 'active': $route.path.startsWith('/invoices') }">🧾 {{ t('invoices') }}</router-link></li>
          </ul>
        </div>

        <!-- Automation & Support -->
        <div>
          <p class="px-2 text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-3 opacity-80">{{ t('support_marketing') }}</p>
          <ul class="space-y-1">
            <li><router-link to="/tickets" class="sidebar-link" :class="{ 'active': $route.path.startsWith('/tickets') }">🎫 {{ t('tickets') }}</router-link></li>
            <li><router-link to="/documents" class="sidebar-link" :class="{ 'active': $route.path.startsWith('/documents') }">📁 {{ t('documents') }}</router-link></li>
            <li><router-link to="/campaigns" class="sidebar-link" :class="{ 'active': $route.path.startsWith('/campaigns') }">📣 {{ t('marketing') }}</router-link></li>
          </ul>
        </div>

        <!-- Extra -->
        <div class="mt-auto">
          <p class="px-2 text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-3 opacity-80">{{ t('admin') }}</p>
          <ul class="space-y-1">
            <li><router-link to="/users" class="sidebar-link" :class="{ 'active': $route.path.startsWith('/users') }">👥 {{ t('team_roles') }}</router-link></li>
            <li><router-link to="/settings" class="sidebar-link" :class="{ 'active': $route.path.startsWith('/settings') }">⚙️ {{ t('settings') }}</router-link></li>
            <li><button @click="logout" class="sidebar-link w-full text-left text-red-300 hover:text-white hover:bg-red-600/20">🚪 {{ t('logout') }}</button></li>
          </ul>
        </div>
      </nav>

      <!-- User Info -->
      <div class="p-4 border-t border-indigo-800 bg-indigo-950/50 backdrop-blur-sm">
        <div class="flex items-center space-x-3">
          <div class="h-10 w-10 bg-indigo-500 rounded-full flex items-center justify-center font-bold text-white shadow-inner border-2 border-white/10 ring-2 ring-indigo-400/20">
            {{ user?.name?.charAt(0) }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-bold text-white truncate">{{ user?.name }}</p>
            <p class="text-[10px] text-indigo-300 uppercase tracking-tighter">{{ user?.role || 'Agent' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Content Area -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
      <!-- Navbar (Mobile header) -->
      <header class="bg-white shadow sm:hidden h-16 flex items-center px-6 justify-between shrink-0">
        <span class="font-black tracking-tighter text-indigo-900 uppercase">{{ settings.site_name }}</span>
        <button class="p-2 bg-indigo-50 rounded-lg text-indigo-600">☰</button>
      </header>

      <!-- Main Viewer -->
      <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50/50 p-4 sm:p-8">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useSettingsStore } from '../stores/settings'
import { useI18n } from 'vue-i18n'

const authStore = useAuthStore()
const settings = useSettingsStore()
const router = useRouter()
const { t, locale } = useI18n()

onMounted(async () => {
  await settings.fetchSettings()
  locale.value = settings.locale
})

const user = computed(() => authStore.user)

const logout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
@reference "tailwindcss";

.sidebar-link {
  @apply flex items-center space-x-3 px-4 py-2.5 rounded-xl transition-all duration-200 text-indigo-100 hover:bg-white/10 hover:translate-x-1;
}
.sidebar-link.active {
  @apply bg-indigo-600 text-white shadow-lg shadow-indigo-900/20 translate-x-1;
}
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  @apply bg-indigo-700/50 rounded-full;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
