import { defineStore } from 'pinia'
import axios from '../services/api'

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    items: [],
    loading: false,
    error: null,
    pollingInterval: null
  }),
  getters: {
    count: (state) => state.items.length,
    hasUnread: (state) => state.items.length > 0
  },
  actions: {
    fetchAll() {
      this.loading = true
      return axios.get('/notifications')
        .then(response => {
          if (response.data.success) {
            this.items = response.data.data
          }
        })
        .catch(e => {
          this.error = e.response?.data?.message || e.message
          console.error('Failed to fetch notifications:', e)
        })
        .finally(() => {
          this.loading = false
        })
    },

    markAsRead(id) {
      console.log('test')
      return axios.post(`/notifications/${id}/read`)
        .then(() => {
          const item = this.items.find(i => i.notification_id === id)
          if (item) {
            item.read_at = new Date().toISOString()
          }
        })
        .catch(e => {
            console.error('Failed to mark notification as read:', e)
        })
    },
    
    startPolling(interval = 30000) {
      this.fetchAll()
      if (this.pollingInterval) clearInterval(this.pollingInterval)
      this.pollingInterval = setInterval(() => {
        this.fetchAll()
      }, interval)
    },

    stopPolling() {
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval)
        this.pollingInterval = null
      }
    }
  }
})
