<template>
    <div class="min-h-screen bg-slate-100 dark:bg-dark-900 text-right" dir="rtl">
        <!-- Sidebar -->
        <Sidebar :is-open="sidebarOpen" :dark-mode="darkMode" @toggle-sidebar="toggleSidebar"
            @toggle-dark-mode="toggleDarkMode" @logout="handleLogout" />

        <!-- Main Content -->
        <main class="transition-all duration-300 flex flex-col min-h-screen">
            <!-- Top Header -->
            <header
                class="sticky top-0 z-30 bg-white/80 dark:bg-dark-800/80 backdrop-blur border-b border-slate-200 dark:border-dark-700">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-4">
                        <button @click="toggleSidebar" :aria-expanded="sidebarOpen" aria-controls="sidebar-drawer"
                            aria-label="Toggle Sidebar" ref="toggleBtnRef"
                            class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-dark-700 text-slate-600 dark:text-slate-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                            {{ route.meta.title || 'لوحة التحكم' }}
                        </h2>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="p-4 md:p-6 flex-1">
                <RouterView />
            </div>
        </main>
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import Sidebar from '../components/core/Sidebar.vue'

const authStore = useAuthStore()
const route = useRoute()

const sidebarOpen = ref(true)
const darkMode = ref(document.documentElement.classList.contains('dark'))

function toggleDarkMode() {
    darkMode.value = !darkMode.value
    document.documentElement.classList.toggle('dark')
    localStorage.setItem('darkMode', darkMode.value)
}

function toggleSidebar() {
    sidebarOpen.value = !sidebarOpen.value
}

async function handleLogout() {
    await authStore.logout()
    window.location.href = '/login'
}

onMounted(() => {
    // Check screen size for mobile
    if (window.innerWidth < 768) {
        sidebarOpen.value = false
    }
})
</script>