import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useRBACStore = defineStore('rbac', () => {
    // Roles State
    const roles = ref([])
    const role = ref(null)
    const rolesLoading = ref(false)
    
    // Permissions State
    const permissions = ref([])
    const permission = ref(null)
    const permissionsLoading = ref(false)
    
    const error = ref(null)

    // Roles Actions
    function fetchRoles() {
        rolesLoading.value = true
        error.value = null
        return api.get('/roles')
            .then((response) => {
                roles.value = response.data.data
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                error.value = err.response?.data?.message || 'Error fetching roles'
                return { success: false, message: error.value }
            })
            .finally(() => {
                rolesLoading.value = false
            })
    }

    function fetchRole(id) {
        rolesLoading.value = true
        error.value = null
        return api.get(`/roles/${id}`)
            .then((response) => {
                role.value = response.data.data
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                error.value = err.response?.data?.message || 'Error fetching role'
                return { success: false, message: error.value }
            })
            .finally(() => {
                rolesLoading.value = false
            })
    }

    function createRole(data) {
        rolesLoading.value = true
        return api.post('/roles', data)
            .then((response) => {
                fetchRoles()
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                return { success: false, errors: err.response?.data?.errors, message: err.response?.data?.message }
            })
            .finally(() => {
                rolesLoading.value = false
            })
    }

    function updateRole(id, data) {
        rolesLoading.value = true
        return api.put(`/roles/${id}`, data)
            .then((response) => {
                fetchRoles()
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                return { success: false, errors: err.response?.data?.errors, message: err.response?.data?.message }
            })
            .finally(() => {
                rolesLoading.value = false
            })
    }

    function deleteRole(id) {
        rolesLoading.value = true
        return api.delete(`/roles/${id}`)
            .then(() => {
                fetchRoles()
                return { success: true }
            })
            .catch((err) => {
                return { success: false, message: err.response?.data?.message || 'Error deleting role' }
            })
            .finally(() => {
                rolesLoading.value = false
            })
    }

    function syncRolePermissions(roleId, permissions) {
        rolesLoading.value = true
        return api.post(`/roles/${roleId}/permissions`, { permissions })
            .then((response) => {
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                return { success: false, message: err.response?.data?.message || 'Error syncing permissions' }
            })
            .finally(() => {
                rolesLoading.value = false
            })
    }

    // Permissions Actions
    function fetchPermissions() {
        permissionsLoading.value = true
        error.value = null
        return api.get('/permissions')
            .then((response) => {
                permissions.value = response.data.data
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                error.value = err.response?.data?.message || 'Error fetching permissions'
                return { success: false, message: error.value }
            })
            .finally(() => {
                permissionsLoading.value = false
            })
    }

    function createPermission(data) {
        permissionsLoading.value = true
        return api.post('/permissions', data)
            .then((response) => {
                fetchPermissions()
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                return { success: false, errors: err.response?.data?.errors, message: err.response?.data?.message }
            })
            .finally(() => {
                permissionsLoading.value = false
            })
    }

    function updatePermission(id, data) {
        permissionsLoading.value = true
        return api.put(`/permissions/${id}`, data)
            .then((response) => {
                fetchPermissions()
                return { success: true, data: response.data.data }
            })
            .catch((err) => {
                return { success: false, errors: err.response?.data?.errors, message: err.response?.data?.message }
            })
            .finally(() => {
                permissionsLoading.value = false
            })
    }

    function deletePermission(id) {
        permissionsLoading.value = true
        return api.delete(`/permissions/${id}`)
            .then(() => {
                fetchPermissions()
                return { success: true }
            })
            .catch((err) => {
                return { success: false, message: err.response?.data?.message || 'Error deleting permission' }
            })
            .finally(() => {
                permissionsLoading.value = false
            })
    }

    return {
        roles,
        role,
        rolesLoading,
        permissions,
        permission,
        permissionsLoading,
        error,
        fetchRoles,
        fetchRole,
        createRole,
        updateRole,
        deleteRole,
        syncRolePermissions,
        fetchPermissions,
        createPermission,
        updatePermission,
        deletePermission
    }
})
