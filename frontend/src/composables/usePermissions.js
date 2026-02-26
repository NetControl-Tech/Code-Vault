import { computed } from 'vue'
import { useAuthStore } from '../stores/auth'
import {useUsersStore} from '../stores/users'
/**
 * Composable for role-based permission checks
 * Roles: admin, employee, viewer
 */
const usersStore = useUsersStore()

export function usePermissions() {
    const authStore = useAuthStore()
    
    const role = computed(() => authStore.user?.role || 'viewer')
    
    // Permission matrix
    const permissions = {
        admin: ['view', 'create', 'update', 'delete'],
        employee: ['view', 'create', 'update'],
        viewer: ['view']
    }
    
    /**
     * Check if user can perform action
     * @param {string} action - 'view' | 'create' | 'update' | 'delete'
     * @returns {boolean}
     */
function can(action) {
  const userPermissions = usersStore.user?.permissions || []
  return userPermissions.includes(action)
}
    
    const canView = computed(() => can('view'))
    const canCreate = computed(() => can('create'))
    const canUpdate = computed(() => can('update'))
    const canDelete = computed(() => can('delete'))
    
    const isAdmin = computed(() => role.value === 'admin')
    const isEmployee = computed(() => role.value === 'employee')
    const isViewer = computed(() => role.value === 'viewer')
    
    return {
        role,
        can,
        canView,
        canCreate,
        canUpdate,
        canDelete,
        isAdmin,
        isEmployee,
        isViewer
    }
}
