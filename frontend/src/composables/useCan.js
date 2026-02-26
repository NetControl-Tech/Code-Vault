import { useAuthStore } from '../stores/auth'

export function useCan() {
    const auth = useAuthStore()

    /**
     * Check if the authenticated user has the given permission.
     * @param {string} permission 
     * @returns {boolean}
     */
    const can = (permission) => {
        return auth.permissions?.includes(permission) || false
    }

    return { 
        can 
    }
}
