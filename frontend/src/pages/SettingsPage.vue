<template>
    <SettingsLayout>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Late Letters Setting -->
            <LateLettersSettingCard />

            <!-- Future settings can be added here -->
        </div>

        <!-- Loading State -->
        <div v-if="loading"
            class="absolute inset-0 bg-white/50 dark:bg-dark-900/50 flex items-center justify-center z-10">
            <i class="pi pi-spin pi-spinner text-4xl text-blue-500"></i>
        </div>
    </SettingsLayout>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useSettingStore } from '../stores/settingStore'
import SettingsLayout from '../components/settings/SettingsLayout.vue'
import LateLettersSettingCard from '../components/settings/LateLettersSettingCard.vue'

const store = useSettingStore()
const loading = computed(() => store.loading)

onMounted(() => {
    store.fetchSettings()
})
</script>
