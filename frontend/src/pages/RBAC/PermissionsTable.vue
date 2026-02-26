<template>
    <div class="card overflow-hidden">
        <!-- Loading -->
        <div v-if="rbacStore.permissionsLoading" class="flex items-center justify-center py-12">
            <AppSpinner size="md" text="جاري تحميل البيانات..." />
        </div>

        <!-- Table -->
        <div v-else-if="rbacStore.permissions.length" class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 80px">#</th>
                        <th>اسم الصلاحية</th>
                        <th>الاسم بالعربية</th>
                        <th>تاريخ الإنشاء</th>
                        <th style="width: 120px">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="permission in rbacStore.permissions" :key="permission.id">
                        <td>
                            <span class="text-xs font-mono bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded">
                                {{ permission.id }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm border border-emerald-200 dark:border-emerald-800/50">
                                    {{ permission.name?.charAt(0)?.toUpperCase() }}
                                </div>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ permission.name
                                    }}</span>
                            </div>
                        </td>
                        <td>
                            <span v-if="permission.name_ar" class="font-medium text-slate-700 dark:text-slate-200">
                                {{ permission.name_ar }}
                            </span>
                            <span v-else class="text-slate-400 dark:text-slate-500 italic">-</span>
                        </td>
                        <td class="text-slate-500 dark:text-slate-400">{{ permission.created_at || '-' }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <button @click="editPermission(permission)"
                                    class="text-sky-600 hover:text-sky-800 dark:text-sky-400 dark:hover:text-sky-200 transition-colors"
                                    v-tooltip.top="'تعديل'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button @click="deletePermission(permission)"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 transition-colors"
                                    v-tooltip.top="'حذف'">
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
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">لا توجد صلاحيات</h3>
            <p class="text-slate-600 dark:text-slate-400">ابدأ بإضافة صلاحية جديدة</p>
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
    rbacStore.fetchPermissions();
})

function deletePermission(permission) {
    if (!confirm(`هل أنت متأكد من حذف الصلاحية "${permission.name}"؟`)) {
        return
    }
    rbacStore.deletePermission(permission.id)
}

function editPermission(permission) {
    emit('clickEdit', permission)
}
</script>
