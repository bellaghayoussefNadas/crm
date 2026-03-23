import { defineStore } from 'pinia'
import api from '../utils/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token') || null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
  actions: {
    async login(credentials) {
      try {
        const { data } = await api.post('/login', credentials)
        this.token = data.token
        this.user = data.user
        localStorage.setItem('auth_token', this.token)
        return true
      } catch (error) {
        throw error
      }
    },
    async fetchUser() {
      if (!this.token) return
      try {
        const { data } = await api.get('/user')
        this.user = data
      } catch (error) {
        this.logout()
      }
    },
    logout() {
      api.post('/logout').catch(() => {})
      this.user = null
      this.token = null
      localStorage.removeItem('auth_token')
    }
  }
})
