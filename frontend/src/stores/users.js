import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useUsersStore = defineStore('users', () => {
    //states
    const users = ref([])
    const user = ref(null)
    const loading = ref(false)
    const error = ref(null)
    const meta = ref({})
    //actions
    
    function fetchUsers(params = {}) {
        loading.value = true
        error.value = null

        return api.get('/users', { params })
            .then((response) => {
                users.value = response.data.data
                meta.value = response.data.meta || {}
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                error.value = err.response?.data?.message || 'حدث خطأ أثناء جلب المستخدمين'
                return { success: false, message: error.value }
            })
            .finally(() => {
                loading.value = false
            })
    }

    function fetchUser(id) {
        loading.value = true
        error.value = null

        return api.get(`/users/${id}`)
            .then((response) => {
                user.value = response.data.data
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                error.value = err.response?.data?.message || 'حدث خطأ أثناء جلب المستخدم'
                return { success: false, message: error.value }
            })
            .finally(() => {
                loading.value = false
            })
    }

    function createUser(data) {
        loading.value = true
        error.value = null

        return api.post('/users', data)
            .then((response) => {
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                if (err.response?.status === 422) {
                    return { success: false, errors: err.response.data.errors }
                }
                error.value = err.response?.data?.message || 'حدث خطأ أثناء إنشاء المستخدم'
                return { success: false, message: error.value }
            })
            .finally(() => {
                loading.value = false
            })
    }

    function updateUser(id, data) {
        loading.value = true
        error.value = null

        return api.put(`/users/${id}`, data)
            .then((response) => {
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                if (err.response?.status === 422) {
                    return { success: false, errors: err.response.data.errors }
                }
                error.value = err.response?.data?.message || 'حدث خطأ أثناء تحديث المستخدم'
                return { success: false, message: error.value }
            })
            .finally(() => {
                loading.value = false
            })
    }

    function toggleActive(id) {
        loading.value = true
        error.value = null

        return api.post(`/users/${id}/toggle-active`)
            .then((response) => {
                // Update user in local list
                const index = users.value.findIndex(u => u.id === id)
                if (index !== -1) {
                    users.value[index].is_active = response.data.data.is_active
                }
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                error.value = err.response?.data?.message || 'حدث خطأ أثناء تغيير حالة المستخدم'
                return { success: false, message: error.value }
            })
            .finally(() => {
                loading.value = false
            })
    }

    function deleteUser(id) {
        loading.value = true
        error.value = null

        return api.delete(`/users/${id}`)
            .then((response) => {
                // Remove user from local list
                users.value = users.value.filter(u => u.id !== id)
                return { success: true, message: response.data.message }
            })
            .catch((err) => {
                error.value = err.response?.data?.message || 'حدث خطأ أثناء حذف المستخدم'
                return { success: false, message: error.value }
            })
            .finally(() => {    
                loading.value = false
            })
    }

    return {
        users,
        user,
        loading,
        error,
        meta,
        fetchUsers,
        fetchUser,
        createUser,
        updateUser,
        updateUser,
        toggleActive,
        deleteUser,
    }
})
