import { defineStore } from 'pinia'
import api from '../services/api'

export const useStatisticsStore = defineStore('statistics', {
    state: () => ({
        premarital: null,
        maternal: null,
        child: null,
        loading: false,
        error: null
    }),

    actions: {
        async fetchStatistics() {
            this.loading = true
            this.error = null
            try {
                const [premaritalRes, maternalRes, childRes] = await Promise.all([
                    api.get('/statistics/premarital'),
                    api.get('/statistics/maternal'),
                    api.get('/statistics/child')
                ])

                this.premarital = premaritalRes.data
                this.maternal = maternalRes.data
                this.child = childRes.data
            } catch (error) {
                console.error('Error fetching statistics:', error)
                this.error = 'حدث خطأ أثناء تحميل الإحصائيات'
                throw error
            } finally {
                this.loading = false
            }
        },

        reset() {
            this.premarital = null
            this.maternal = null
            this.child = null
            this.error = null
            this.loading = false
        }
    }
})
