<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold font-heading text-gray-900">Paramètres du Site</h1>
      <button @click="save" :disabled="saving" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold shadow-lg hover:bg-indigo-700 transition transform hover:scale-105 active:scale-95 disabled:opacity-50">
        {{ saving ? 'Enregistrement...' : 'Enregistrer les modifications' }}
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Apparence -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
        <h3 class="text-lg font-bold flex items-center gap-2">🎨 Apparence</h3>
        <div>
          <label class="block text-sm font-bold text-gray-700">Nom du Site</label>
          <input v-model="form.site_name" type="text" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-3 border">
        </div>
        <div>
          <label class="block text-sm font-bold text-gray-700">Logo du Site</label>
          <div class="mt-2 flex items-center gap-4">
            <img v-if="logoPreview || form.logo_url" :src="logoPreview || form.logo_url" class="h-16 w-16 rounded-xl object-contain bg-gray-50 border p-2" />
            <div v-else class="h-16 w-16 bg-gray-100 rounded-xl flex items-center justify-center text-2xl">📸</div>
            <label class="cursor-pointer bg-white px-4 py-2 border rounded-lg text-sm font-bold shadow-sm hover:bg-gray-50 transition">
              Choisir une image
              <input type="file" @change="handleLogoChange" class="hidden" accept="image/*" />
            </label>
            <p class="text-[10px] text-gray-400 italic" v-if="!logoPreview">PNG, JPG, SVG supportés</p>
          </div>
        </div>
      </div>

      <!-- Localisation -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-4">
        <h3 class="text-lg font-bold flex items-center gap-2">🌍 Localisation</h3>
        <div>
          <label class="block text-sm font-bold text-gray-700">Devise par défaut</label>
          <select v-model="form.currency" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-3 border">
            <option value="TND">TND (Dinar Tunisien)</option>
            <option value="EUR">EUR (€)</option>
            <option value="USD">USD ($)</option>
            <option value="GBP">GBP (£)</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-bold text-gray-700">Langue du Système</label>
          <select v-model="form.locale" @change="changeLocale" class="mt-1 block w-full rounded-xl border-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-3 border">
            <option value="fr">🇫🇷 Français</option>
            <option value="en">🇺🇸 English</option>
            <option value="ar">🇹🇳 العربية (Arabic)</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Info Box -->
    <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl flex items-center gap-3 text-indigo-700">
      <span class="text-2xl">💡</span>
      <p class="text-sm">Ces changements s'appliquent globalement à tous les utilisateurs du CRM (Tenant Unique).</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useSettingsStore } from '../stores/settings'
import { useI18n } from 'vue-i18n'

const store = useSettingsStore()
const { locale } = useI18n()
const saving = ref(false)

const form = ref({
  site_name: '',
  currency: 'TND',
  locale: 'fr',
  logo_url: ''
})

const logoFile = ref(null)
const logoPreview = ref(null)

onMounted(async () => {
  await store.fetchSettings()
  form.value = { 
    site_name: store.site_name,
    currency: store.currency,
    locale: store.locale,
    logo_url: store.logo_url
  }
  locale.value = form.value.locale
})

const handleLogoChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    logoFile.value = file
    logoPreview.value = URL.createObjectURL(file)
  }
}

const changeLocale = () => {
  locale.value = form.value.locale
}

const save = async () => {
  saving.value = true
  try {
    const formData = new FormData()
    formData.append('site_name', form.value.site_name)
    formData.append('currency', form.value.currency)
    formData.append('locale', form.value.locale)
    if (logoFile.value) {
      formData.append('logo', logoFile.value)
    }

    await store.updateSettings(formData)
    alert("Paramètres enregistrés avec succès.")
    window.location.reload() // Reload to refresh logo everywhere
  } catch (error) {
    alert("Erreur lors de l'enregistrement.")
  } finally {
    saving.value = false
  }
}
</script>
