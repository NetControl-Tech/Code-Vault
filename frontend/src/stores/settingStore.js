import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '../services/api'
import { useToast } from 'primevue/usetoast'

export const useSettingStore = defineStore('settings', () => {
    const items = ref([])
    const loading = ref(false)
    const error = ref(null)
    const toast = useToast()

    // Getters
    const lateLettersValue = computed(() => {
        const setting = items.value.find(s => s.key === 'late_letters')
        return setting ? parseInt(setting.value) : 7 // Default to 7
    })

    // Actions
    function fetchSettings() {
        loading.value = true
        return axios.get('/setting')
            .then(response => {
                // Normalize response: handle { data: [...] } or direct array
                items.value = Array.isArray(response.data.data) 
                    ? response.data.data 
                    : (Array.isArray(response.data) ? response.data : [])
                return items.value
            })
            .catch(err => {
                error.value = err.response?.data?.message || 'Failed to fetch settings'
                console.error('Error fetching settings:', err)
                throw err
            })
            .finally(() => {
                loading.value = false
            })
    }


    // Alias for bulk update to match component expectations
    function bulkUpdateSettings(payload) {
        return updateSetting({ settings: payload })
    }

    function createSetting(payload) {
        loading.value = true
        return axios.post('/setting', payload)
            .then(response => {
                items.value.push(response.data.data || response.data)
                toast.add({
                    severity: 'success',
                    summary: 'تم الإنشاء',
                    detail: 'تم إنشاء الإعداد بنجاح',
                    life: 3000
                })
                return response
            })
            .finally(() => {
                loading.value = false
            })
    }

    function getSetting(id) {
        // Can try finding in local cache first
        const local = items.value.find(i => i.id == id)
        if (local) return Promise.resolve({ data: local })

        loading.value = true
        return axios.get(`/setting/${id}`)
            .then(response => response.data)
            .finally(() => {
                loading.value = false
            })
    }

    // Convenience action for late letters
    function updateLateLetters(value) {
        return updateSetting({ settings: { late_letters: value } })
    }

    return {
        fetchSettings,
        bulkUpdateSettings,
        createSetting,
        getSetting,
        updateLateLetters,
        getSettings: fetchSettings,
        settings: computed(() => ({ data: items.value })) // adapter for store.settings.data access
    }
})
