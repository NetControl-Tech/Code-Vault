import api from './api'

export const blocklistApi = {
    // Blocklists
    getBlocklists(category, params) {
        return api.get('/admin/blocklists', { params: { category, ...params } })
    },
    addDomain(data) {
        return api.post('/admin/blocklists', data)
    },
    updateDomain(id, data) {
        return api.put(`/admin/blocklists/${id}`, data)
    },
    deleteDomain(id) {
        return api.delete(`/admin/blocklists/${id}`)
    },
    bulkUpload(file, category) {
        const formData = new FormData()
        formData.append('file', file)
        formData.append('category', category)
        return api.post('/admin/blocklists/bulk-upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
    },

    // Reports
    getReports(params) {
        return api.get('/admin/reports', { params })
    },
    approveReport(id, category) {
        return api.post(`/admin/reports/${id}/approve`, { category })
    },
    rejectReport(id) {
        return api.post(`/admin/reports/${id}/reject`)
    }
}
