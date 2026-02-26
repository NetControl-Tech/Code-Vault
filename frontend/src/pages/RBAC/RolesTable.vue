<template>
    <div class="card overflow-hidden">
        <!-- Loading -->
        <div v-if="rbacStore.rolesLoading" class="flex items-center justify-center py-12">
            <AppSpinner size="md" text="جاري تحميل البيانات..." />
        </div>

        <!-- Table -->
        <div v-else-if="rbacStore.roles.length" class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px">#</th>
                        <th>اسم الدور</th>
                        <th>تاريخ الإنشاء</th>
                        <th style="width: 120px">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="role in rbacStore.roles" :key="role.id">
                        <td>
                            <span class="text-xs font-mono bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded">
                                {{ role.id }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 flex items-center justify-center font-bold text-sm border border-primary-200 dark:border-primary-800/50">
                                    {{ role.name.charAt(0) }}
                                </div>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ role.name }}</span>
                            </div>
                        </td>
                        <td class="text-slate-500 dark:text-slate-400">{{ role.created_at || '-' }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <button @click="editRole(role)"
                                    class="text-sky-600 hover:text-sky-800 dark:text-sky-400 dark:hover:text-sky-200 transition-colors"
                                    v-tooltip.top="'تعديل'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="deleteRole(role)" :disabled="isSystemRole(role.name)" :class="[
                                    'transition-colors',
                                    isSystemRole(role.name)
                                        ? 'text-slate-300 dark:text-slate-600 cursor-not-allowed'
                                        : 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200'
                                ]" v-tooltip.top="'حذف'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div v-else class="p-12 text-center">
            <div
                class="w-16 h-16 mx-auto mb-4 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">لا توجد أدوار</h3>
            <p class="text-slate-600 dark:text-slate-400">ابدأ بإضافة دور جديد</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useRBACStore } from "../../stores/RBAC"
import AppSpinner from "../../components/core/AppSpinner.vue"

const rbacStore = useRBACStore()
const emit = defineEmits(['clickEdit'])

onMounted(() => {
    rbacStore.fetchRoles();
})

function isSystemRole(name) {
    return ['super-admin'].includes(name);
}

function deleteRole(role) {
    if (isSystemRole(role.name)) {
        alert('لا يمكن حذف دور أساسي في النظام');
        return;
    }
    if (!confirm(`هل أنت متأكد من حذف الدور "${role.name}"؟`)) {
        return
    }
    rbacStore.deleteRole(role.id)
}

function editRole(role) {
    emit('clickEdit', role)
}
</script>
