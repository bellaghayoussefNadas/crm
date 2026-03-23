<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900 font-primary italic">{{ t('analytics_performance') }}</h1>
      <span class="text-xs text-gray-400 bg-gray-100 p-2 rounded border border-gray-200 uppercase font-bold tracking-widest">{{ t('real_time') }}</span>
    </div>

    <!-- Main KPIs -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Total Revenue Paid -->
      <div class="bg-indigo-600 overflow-hidden shadow-xl rounded-2xl hover:scale-105 transition duration-300">
        <div class="p-5 text-white">
          <div class="flex items-center justify-between mb-4">
            <span class="text-xs font-bold uppercase tracking-wider opacity-70">{{ t('revenue') }}</span>
            <span class="text-2xl">{{ settings.currency === 'TND' ? 'د.ت' : '💶' }}</span>
          </div>
          <p class="text-3xl font-bold truncate">{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(stats.revenue_total || 0) }}</p>
          <div class="mt-4 text-xs font-medium opacity-80 flex items-center">
             <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
             {{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(stats.revenue_pending || 0) }} {{ t('pending') }}
          </div>
        </div>
      </div>

      <!-- Deals Pipeline Value -->
      <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200">
        <div class="p-5">
           <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ t('pipeline_won') }}</span>
              <span class="text-2xl">⚡</span>
           </div>
           <p class="text-3xl font-bold text-gray-900 italic">{{ stats.deals_won || 0 }} {{ t('deals') }}</p>
           <div class="mt-4 text-xs font-bold text-indigo-600 flex items-center">
              {{ t('conv_rate') }} : {{ conversionRate }}%
           </div>
        </div>
      </div>

      <!-- Support Tickets -->
      <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200">
         <div class="p-5">
            <div class="flex items-center justify-between mb-4">
               <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ t('active_support') }}</span>
               <span class="text-2xl">🎫</span>
            </div>
            <p class="text-3xl font-bold text-red-500 italic">{{ stats.tickets_count || 0 }} {{ t('tickets') }}</p>
            <div class="mt-4 text-xs text-gray-400 flex items-center space-x-2">
               <span v-for="p in stats.tickets_by_priority" :key="p.priority" :class="p.priority === 'urgent' ? 'text-red-600' : 'text-gray-600'" class="font-bold">
                 {{ p.count }} {{ p.priority }}
               </span>
            </div>
         </div>
      </div>

      <!-- Teams & Activity -->
      <div class="bg-gray-900 overflow-hidden shadow-xl rounded-2xl hover:scale-105 transition duration-300">
         <div class="p-5 text-white">
            <div class="flex items-center justify-between mb-4">
               <span class="text-xs font-bold uppercase tracking-wider opacity-60">{{ t('pending_tasks') }}</span>
               <span class="text-2xl">✅</span>
            </div>
            <p class="text-3xl font-bold italic">{{ stats.tasks_pending || 0 }} {{ t('pending') }}</p>
            <div class="mt-4 flex -space-x-2">
               <div v-for="i in 3" :key="i" class="w-6 h-6 rounded-full border-2 border-gray-900 bg-indigo-500 flex items-center justify-center text-[10px] font-bold">U{{i}}</div>
               <div class="text-xs ml-4 py-1 text-gray-400 font-bold">+ Équipe commerciale</div>
            </div>
         </div>
      </div>
    </div>

    <!-- Charts / Tables Placeholder -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
       <!-- Revenue Chart (Textual Simulation) -->
       <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
          <h3 class="font-bold text-gray-800 mb-6 uppercase tracking-wider text-sm italic">{{ t('revenue_evolution') }} (6 m)</h3>
          <div class="flex items-end space-x-4 h-48">
             <div v-for="m in stats.monthly_revenue" :key="m.month" class="flex-1 bg-indigo-50 rounded-t-lg relative group transition hover:bg-indigo-100" :style="{ height: (m.total / maxMonthlyRevenue * 100) + '%' }">
                <span class="absolute -top-10 left-1/2 -translate-x-1/2 bg-gray-900 text-white p-2 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap text-[10px] font-bold shadow-lg">
                  {{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(m.total) }}
                </span>
                <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-[10px] text-gray-400 font-bold text-center w-full uppercase">{{ m.month.split('-')[1] }}</span>
             </div>
          </div>
       </div>

       <!-- Top Accounts -->
       <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
          <h3 class="font-bold text-gray-800 mb-6 uppercase tracking-wider text-sm italic">{{ t('top_accounts_value') }}</h3>
          <div class="space-y-4">
             <div v-for="acc in stats.top_accounts" :key="acc.id" class="flex items-center justify-between p-3 rounded-xl border border-gray-50 hover:bg-gray-50 transition border-l-4 border-l-indigo-500">
                <div class="flex items-center space-x-3">
                   <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center font-bold">🏢</div>
                   <div class="font-bold text-gray-900 capitalize text-sm">{{ acc.name }}</div>
                </div>
                 <div class="text-sm font-bold text-indigo-600">
                    {{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency, maximumFractionDigits: 0 }).format(acc.deals_sum_amount || 0) }}
                 </div>
             </div>
             <div v-if="!stats.top_accounts?.length" class="text-center text-gray-400 py-10">Données insuffisantes</div>
          </div>
       </div>
    </div>

    <!-- Bottom summary -->
     <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">
           <p class="text-xs text-indigo-400 font-bold uppercase mb-2">{{ t('total_contacts') }}</p>
           <p class="text-4xl font-bold text-indigo-900 italic">{{ stats.contacts_count || 0 }}</p>
        </div>
        <div class="bg-green-50 p-6 rounded-2xl border border-green-100">
           <p class="text-xs text-green-400 font-bold uppercase mb-2">{{ t('total_leads') }}</p>
           <p class="text-4xl font-bold text-green-900 italic">{{ stats.leads_count || 0 }}</p>
        </div>
       <div class="bg-red-50 p-6 rounded-2xl border border-red-100">
          <p class="text-xs text-red-400 font-bold uppercase mb-2">{{ t('alerts_overdue') }}</p>
          <p class="text-4xl font-bold text-red-900 italic">{{ stats.overdue_invoices || 0 }}</p>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../utils/axios'
import { useSettingsStore } from '../stores/settings'
import { useI18n } from 'vue-i18n'

const settings = useSettingsStore()
const { t } = useI18n()
const stats = ref({})

const maxMonthlyRevenue = computed(() => {
  if (!stats.value.monthly_revenue?.length) return 1
  return Math.max(...stats.value.monthly_revenue.map(m => m.total))
})

const conversionRate = computed(() => {
  const total = (stats.value.deals_won || 0) + (stats.value.deals_lost || 0)
  if (!total) return 0
  return Math.round((stats.value.deals_won / total) * 100)
})

onMounted(async () => {
  try {
    const res = await api.get('/reports/dashboard')
    stats.value = res.data
  } catch (e) {
    console.error("Dashboard stats error", e)
  }
})
</script>

