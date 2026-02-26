<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const route = useRoute()

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: true
    },
    darkMode: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['toggle-sidebar', 'toggle-dark-mode', 'logout'])

// Navigation items
const navItems = computed(() => {
    const items = []
    // Admin only section
    if (authStore.isAdmin) {
        items.push({
            label: 'الإدارة',
            isHeader: true
        })

        items.push({
            name: 'users',
            label: 'إدارة المستخدمين',
            icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
    </svg>`
        })
    }
    if (authStore.isSuperAdmin) {
        // Roles
        items.push({
            name: 'roles',
            label: 'الأدوار',
            icon: `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
    </svg>`
        })
    }
    return items
})

// Role badge
const roleBadge = computed(() => {
    if (authStore.isAdmin) {
        return { label: 'مسؤول النظام', class: 'bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300' }
    }
    return { label: 'موظف', class: 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300' }
})
</script>

<template>
    <aside :class="[
        'fixed top-0 right-0 z-40 h-screen transition-transform duration-300',
        isOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0 md:w-20'
    ]" :style="{ width: isOpen ? '280px' : '' }">
        <div
            class="h-full flex flex-col bg-white dark:bg-dark-800 border-l border-slate-200 dark:border-dark-700 shadow-lg">
            <!-- Logo -->
            <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-dark-700">
                <div v-if="isOpen" class="flex items-center gap-3">
                    <span class="text-lg font-bold text-slate-900 dark:text-white">أداره الكتب</span>
                </div>
                <button @click="emit('toggle-sidebar')"
                    class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-dark-700 text-slate-600 dark:text-slate-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <template v-for="item in navItems" :key="item.label">
                    <!-- Header -->
                    <div v-if="item.isHeader" class="pt-4 pb-2 px-4">
                        <span v-if="isOpen" class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                            {{ item.label }}
                        </span>
                        <div v-else class="h-px bg-slate-200 dark:bg-dark-700 mx-1"></div>
                    </div>

                    <!-- Link -->
                    <RouterLink v-else :to="{ name: item.name }" :class="[
                        'sidebar-link',
                        route.name === item.name || (item.name === 'users' && route.name?.startsWith('users')) ? 'active' : ''
                    ]">
                        <span v-html="item.icon"></span>
                        <span v-if="isOpen">{{ item.label }}</span>
                    </RouterLink>
                </template>
            </nav>

            <!-- User Section -->
            <div class="p-4 border-t border-slate-200 dark:border-dark-700">
                <div v-if="isOpen" class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-sky-100 dark:bg-sky-900/50 flex items-center justify-center">
                        <span class="text-sky-700 dark:text-sky-300 font-semibold">
                            {{ authStore.user?.name?.charAt(0) || 'U' }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-900 dark:text-white truncate">
                            {{ authStore.user?.name || 'المستخدم' }}
                        </p>
                        <span :class="['text-xs px-2 py-0.5 rounded-full', roleBadge.class]">
                            {{ roleBadge.label }}
                        </span>
                    </div>
                </div>

                <div class="flex gap-2">
                    <!-- Dark Mode Toggle -->
                    <button @click="emit('toggle-dark-mode')"
                        class="flex-1 p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-dark-700 text-slate-600 dark:text-slate-300 transition-colors"
                        :title="darkMode ? 'الوضع الفاتح' : 'الوضع الداكن'">
                        <svg v-if="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Logout -->
                    <button @click="emit('logout')"
                        class="flex-1 p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 transition-colors"
                        :class="!isOpen ? 'hidden' : ''" title="تسجيل الخروج">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </aside>
</template>
