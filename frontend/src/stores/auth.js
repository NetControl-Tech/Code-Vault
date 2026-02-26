import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../services/api'

export const useAuthStore = defineStore('auth', () => {
    //states
    const user = ref(JSON.parse(localStorage.getItem('user')) || null)
    const token = ref(localStorage.getItem('token') || null)
    const tempToken = ref(localStorage.getItem('temp_token') || null)
    const loading = ref(false)
    const error = ref(null)

    //computed
    const isAuthenticated = computed(() => !!token.value)
    const requiresVerification = computed(() => !!tempToken.value && !isAuthenticated.value)
    const isSuperAdmin = computed(() => user.value?.role === 'super-admin')
    const isAdmin = computed(() => user.value?.role === 'admin' || user.value?.role === 'super-admin')
    const isClient = computed(() => user.value?.role === 'client')
    const permissions = computed(() => user.value?.permissions || [])

    //actions
    function login(credentials) {
        loading.value = true
        error.value = null
    
        return api.post('/login', credentials)
      .then((response) => {
        debugger
        if (response.data.requires_2fa) {
             tempToken.value = response.data.data.temp_token
             localStorage.setItem('temp_token', tempToken.value)
             // We don't set user.value until 2FA is verified, to keep isAuthenticated = true restricted
             loading.value = false
             return { success: true, requires_2fa: true }
        }

        token.value = response.data.data.token
        user.value = response.data.data.user
        
        localStorage.setItem('token', token.value)
        localStorage.setItem('user', JSON.stringify(user.value))
        
        loading.value = false
        return { success: true }
      })
      .catch((err) => {
        error.value = err.response?.data?.message || 'فشل تسجيل الدخول'
        loading.value = false
        return { success: false, message: error.value }
      })
    }

    function verify2fa(code) {
        loading.value = true
        error.value = null

        return api.post('/verify-2fa', { code })
      .then((response) => {
        token.value = response.data.data.token
        user.value = response.data.data.user
        
        localStorage.setItem('token', token.value)
        localStorage.setItem('user', JSON.stringify(user.value))
        
        tempToken.value = null
        localStorage.removeItem('temp_token')
        
        loading.value = false
        return { success: true }
      })
      .catch((err) => {
        error.value = err.response?.data?.message || 'رمز التحقق غير صحيح'
        loading.value = false
        return { success: false, message: error.value }
      })
    }

    function resend2fa() {
        loading.value = true
        error.value = null

        return api.post('/resend-2fa')
      .then((response) => {
        loading.value = false
        return { success: true, message: response.data.message }
      })
      .catch((err) => {
        error.value = err.response?.data?.message || 'حدث خطأ أثناء إعادة إرسال الرمز'
        loading.value = false
        return { success: false, message: error.value }
      })
    }

    function logout() {
        loading.value = true
    
        return api.post('/logout')
      .then(() => {
        clearAuth()
        loading.value = false
      })
      .catch(() => {
        // Ignore logout errors, clear local state anyway
        clearAuth()
        loading.value = false
      })
    }

    function clearAuth() {
        token.value = null
        tempToken.value = null
        user.value = null
        localStorage.removeItem('token')
        localStorage.removeItem('temp_token')
        localStorage.removeItem('user')
    }

    function fetchUser() {
        if (!token.value) return Promise.resolve()

        return api.get('/me')
      .then((response) => {
        user.value = response.data.data
        localStorage.setItem('user', JSON.stringify(user.value))
      })
      .catch(() => {
        // Token invalid, clear auth
        clearAuth()
      })
    }

    return {
    user,
    token,
    tempToken,
    loading,
    error,
    isAuthenticated,
    requiresVerification,
    isSuperAdmin,
    isAdmin,
    isClient,
    permissions,
    login,
    verify2fa,
    resend2fa,
    logout,
    fetchUser,
    clearAuth
    }
})
