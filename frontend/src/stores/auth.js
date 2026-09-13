import { defineStore } from 'pinia'
import api from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('halastay_token') || null,
    user: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    role: (state) => state.user?.role || null,
  },

  actions: {
    setToken(token) {
      this.token = token
      localStorage.setItem('halastay_token', token)
    },

    clearAuth() {
      this.token = null
      this.user = null
      localStorage.removeItem('halastay_token')
    },

    async login(credentials) {
      const { data } = await api.post('/auth/login', credentials)
      this.setToken(data.data.token)
      this.user = data.data.user
      return this.user
    },

    async register(payload) {
      const { data } = await api.post('/auth/register', payload)
      this.setToken(data.data.token)
      this.user = data.data.user
      return this.user
    },

    async fetchUser() {
      const { data } = await api.get('/auth/me')
      this.user = data.data.user
      return this.user
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      } finally {
        this.clearAuth()
      }
    },
  },
})