import { defineStore } from 'pinia'
import api from '../utils/axios'

export const useSettingsStore = defineStore('settings', {
  state: () => ({
    site_name: 'GoTravel CRM',
    currency: 'TND',
    locale: 'fr',
    logo_url: ''
  }),
  actions: {
    async fetchSettings() {
      try {
        const res = await api.get('/settings')
        const s = res.data
        if (s.site_name) this.site_name = s.site_name
        if (s.currency) this.currency = s.currency
        if (s.locale) this.locale = s.locale
        if (s.logo_url) this.logo_url = s.logo_url
      } catch (e) {
        console.error("Erreur chargement paramètres:", e)
      }
    },
    async updateSettings(data) {
      try {
        const isFormData = data instanceof FormData
        await api.post('/settings', data, {
          headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : {}
        })
        await this.fetchSettings()
      } catch (error) {
        throw error
      }
    }
  }
})
