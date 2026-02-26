<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 md:p-8">
        <!-- Header -->
        <div class="max-w-7xl mx-auto mb-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h1 v-if="!loading" class="text-2xl md:text-3xl font-bold text-slate-800">
                    {{ setting.id ? `Update Setting` : 'Create New Setting' }}
                </h1>
            </div>
            <p v-if="setting.id && !loading" class="text-slate-500 ml-13">
                Editing: <span class="font-medium font-mono text-indigo-600">{{ setting.key }}</span>
            </p>
        </div>

        <div class="max-w-7xl mx-auto">
            <Spinner v-if="loading"
                class="fixed inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-50" />

            <form v-else @submit.prevent="onSubmit">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Setting Information
                        </h2>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Value Field -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Setting Value <span class="text-red-500">*</span>
                            </label>
                            <CustomInput v-model="setting.value" :errors="errors.value"
                                placeholder="Enter Setting Value" />
                        </div>
                    </div>
                </div>
                <!-- Actions Footer -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 px-6 py-4 
                flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 mt-6">
                    <RouterLink :to="{ name: 'app.settings' }" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 
                    text-slate-600 hover:text-slate-800 hover:bg-slate-100 
                    rounded-xl transition-all duration-200 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Cancel
                    </RouterLink>

                    <div class="flex gap-3">
                        <button type="button" @click="onSubmit($event, true)" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 
                        px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 
                        hover:from-indigo-700 hover:to-indigo-800 text-white 
                        rounded-xl font-medium shadow-lg shadow-indigo-500/25 
                        transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            Save Setting
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from "vue-router";
import { useSettingStore } from '../../stores/settingStore'; // Corrected path
import { useToast } from 'primevue/usetoast';

const route = useRoute()
const router = useRouter()
const store = useSettingStore()
const toast = useToast()

const loading = ref(false)
const setting = ref({
    id: null,
    key: '',
    value: '',
})

const errors = ref({})

function onSubmit($event, close = false) {
    loading.value = true;
    errors.value = {}

    // Determine action
    const isUpdate = !!setting.value.id;
    const promise = isUpdate
        ? store.updateSetting(setting.value.key, setting.value.value) // Use single update logic or if ID based
        : store.createSetting(setting.value);

    promise
        .then(response => {
            loading.value = false;
            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: isUpdate ? 'Setting updated successfully' : 'Setting created successfully',
                life: 3000
            });

            store.fetchSettings();

            if (close) {
                // Adjust route name if needed, assuming 'settings' or 'app.settings'
                router.push({ name: 'app.settings' });
            } else if (!isUpdate) {
                // If created, redirect to edit or reset
                if (response.data && response.data.id) {
                    // Assuming we have an edit route, otherwise just stay
                    setting.value = response.data.data || response.data;
                    router.push({ name: 'app.settings.edit', params: { id: response.data.id } });
                }
            }
        })
        .catch(err => {
            loading.value = false;
            if (err.response?.status === 422) {
                errors.value = err.response.data.errors;
            } else {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: err.response?.data?.message || 'An error occurred',
                    life: 3000
                });
            }
        });
}

onMounted(() => {
    if (route.params.id) {
        loading.value = true
        store.getSetting(route.params.id)
            .then((response) => {
                loading.value = false;
                setting.value = response.data || response;
            })
            .catch(() => {
                loading.value = false;
                router.push({ name: 'app.settings' });
            })
    }
})
</script>
