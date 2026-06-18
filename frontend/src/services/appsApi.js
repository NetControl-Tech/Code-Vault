import api from './api'

export const appsApi = {
    getApps(params) {
        return api.get('/admin/apps', { params })
    },
    addApp(data) {
        return api.post('/admin/apps', data)
    },
    updateApp(id, data) {
        return api.put(`/admin/apps/${id}`, data)
    },
    deleteApp(id) {
        return api.delete(`/admin/apps/${id}`)
    }
}
