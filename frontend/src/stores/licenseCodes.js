import { defineStore } from 'pinia'
import api from '../services/api'

export const useLicenseCodesStore = defineStore('licenseCodes', {
    state: () => ({
        codes: [],
        meta: {
            current_page: 1,
            last_page: 1,
            total: 0
        },
        loading: false,
        error: null,
    }),
    actions: {
        fetchLicenseCodes(params = {}) {
            this.loading = true
            return api.get('/admin/codes', { params })
                .then(res => res.data)
                .then(data => {
                    this.codes = data.data
                    this.meta = {
                        current_page: data.current_page,
                        last_page: data.last_page,
                        total: data.total
                    }
                })
                .catch(error => {
                    this.error = error
                    throw error
                })
                .finally(() => {
                    this.loading = false
                })
        },
        deleteRange(data) {
            return api.post('/admin/codes/destroy-range', data)
        },
        generateLicenseCodes(data) {
            return api.post('/admin/codes/generate', data, { responseType: 'blob' })
        },
        activateRange(data) {
            return api.post('/admin/codes/activate-range', data)
        },
        async exportLicenseCodes(params = {}) {
            const res = await api.get('/admin/codes/export', { params, responseType: 'blob' })
            const url = window.URL.createObjectURL(new Blob([res.data]))
            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', 'license_codes.xlsx')
            document.body.appendChild(link)
            link.click()
            link.remove()
            return { success: true }
        },
        revokeToken(deviceId) {
            return api.post(`/admin/devices/${deviceId}/revoke-token`)
        }
    }
})
