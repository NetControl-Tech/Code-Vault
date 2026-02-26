<template>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">إدارة المستخدمين</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">إضافة وتعديل المستخدمين وصلاحياتهم</p>
            </div>
            <RouterLink v-if="can('users.create')" :to="{ name: 'users.create' }" class="btn btn-primary">
                + إضافة مستخدم جديد
            </RouterLink>
        </div>

        <!-- Filters -->
        <div class="card p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">بحث</label>
                    <input v-model="filters.search" type="text" class="input"
                        placeholder="الاسم أو البريد الإلكتروني..." />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">الدور</label>
                    <Select v-model="filters.role" :options="rbacStore.roles" optionLabel="name" optionValue="name"
                        placeholder="الكل" showClear class="w-full" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">الحالة</label>
                    <Select v-model="filters.is_active" :options="statusOptions" optionLabel="label" optionValue="value"
                        placeholder="الكل" showClear class="w-full" />
                </div>
                <div class="flex items-end gap-2">
                    <button @click="applyFilters" class="btn btn-primary flex-1">بحث</button>
                    <button @click="clearFilters" class="btn btn-secondary">مسح</button>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="usersStore.loading" class="flex items-center justify-center py-12">
            <AppSpinner size="md" text="جاري تحميل البيانات..." />
        </div>

        <!-- Users Table -->
        <div v-else-if="usersStore.users.length" class="card overflow-hidden">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>البريد الإلكتروني</th>
                            <th>الدور</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in usersStore.users" :key="user.id">
                            <td class="font-medium">{{ user.name }}</td>
                            <td dir="rtl" class="text-right">{{ user.email }}</td>
                            <td>
                                <span :class="['badge', user.role.color]">
                                    {{ user.role }}
                                </span>
                            </td>
                            <td>
                                <span :class="['badge', user.is_active
                                    ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300'
                                    : 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300']">
                                    {{ user.is_active ? 'نشط' : 'معطل' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <RouterLink v-if="can('users.update')"
                                        :to="{ name: 'users.edit', params: { id: user.id } }"
                                        class="text-sky-600 hover:text-sky-800 dark:text-sky-400 dark:hover:text-sky-200 transition-colors"
                                        v-tooltip.top="'تعديل'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </RouterLink>
                                    <button v-if="can('users.toggle-active')" @click="openToggleModal(user)" :class="user.is_active
                                        ? 'text-red-600 hover:text-red-800 dark:text-red-400'
                                        : 'text-green-600 hover:text-green-800 dark:text-green-400'"
                                        v-tooltip.top="user.is_active ? 'تعطيل' : 'تفعيل'">
                                        <svg v-if="user.is_active" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <button v-if="can('users.delete')" @click="openDeleteModal(user)"
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

            <!-- Pagination -->
            <div v-if="usersStore.meta.last_page > 1" class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        عرض {{ usersStore.users.length }} من {{ usersStore.meta.total }} مستخدم
                    </p>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="card p-12 text-center">
            <div
                class="w-16 h-16 mx-auto mb-4 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">لا يوجد مستخدمين</h3>
            <p class="text-slate-600 dark:text-slate-400 mb-4">ابدأ بإضافة مستخدم جديد</p>
            <RouterLink v-if="can('users.create')" :to="{ name: 'users.create' }" class="btn btn-primary inline-block">
                + إضافة مستخدم جديد
            </RouterLink>
        </div>

        <!-- Toggle Active Modal -->
        <div v-if="showDeactivateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="card p-6 max-w-sm w-full">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                    {{ selectedUser?.is_active ? 'تعطيل المستخدم' : 'تفعيل المستخدم' }}
                </h3>
                <p class="text-slate-600 dark:text-slate-400 mb-6">
                    {{ selectedUser?.is_active
                        ? 'هل أنت متأكد من تعطيل هذا المستخدم؟ لن يتمكن من تسجيل الدخول.'
                        : 'هل أنت متأكد من تفعيل هذا المستخدم؟' }}
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeactivateModal = false" class="btn btn-secondary">
                        إلغاء
                    </button>
                    <button @click="confirmToggle"
                        :class="selectedUser?.is_active ? 'btn btn-danger' : 'btn btn-primary'">
                        {{ selectedUser?.is_active ? 'تعطيل' : 'تفعيل' }}
                    </button>
                </div>
            </div>
        </div>
        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="card p-6 max-w-sm w-full">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">
                    حذف المستخدم
                </h3>
                <p class="text-slate-600 dark:text-slate-400 mb-6">
                    هل أنت متأكد من حذف المستخدم "{{ selectedUser?.name }}"؟ لا يمكن التراجع عن هذا الإجراء.
                </p>
                <div class="flex gap-3 justify-end">
                    <button @click="showDeleteModal = false" class="btn btn-secondary">
                        إلغاء
                    </button>
                    <button @click="confirmDelete" class="btn btn-danger">
                        حذف
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { useUsersStore } from '../../stores/users'
import { useRBACStore } from '../../stores/RBAC'
import { useCan } from '../../composables/useCan'
import Select from 'primevue/select'
import AppSpinner from '../../components/core/AppSpinner.vue'
const usersStore = useUsersStore()
const rbacStore = useRBACStore()
const { can } = useCan()

const filters = ref({
    search: '',
    role: '',
    is_active: ''
})

const statusOptions = [
    { label: 'نشط', value: '1' },
    { label: 'معطل', value: '0' }
]

const showDeactivateModal = ref(false)
const showDeleteModal = ref(false)
const selectedUser = ref(null)

function loadUsers() {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.role) params.role = filters.value.role
    if (filters.value.is_active !== '') params.is_active = filters.value.is_active

    usersStore.fetchUsers(params)
}

function applyFilters() {
    loadUsers()
}

function clearFilters() {
    filters.value = { search: '', role: '', is_active: '' }
    loadUsers()
}

function openToggleModal(user) {
    selectedUser.value = user
    showDeactivateModal.value = true
}

function confirmToggle() {
    if (selectedUser.value) {
        usersStore.toggleActive(selectedUser.value.id)
            .then(() => {
                showDeactivateModal.value = false
                selectedUser.value = null
            })
    }
}

function openDeleteModal(user) {
    selectedUser.value = user
    showDeleteModal.value = true
}

function confirmDelete() {
    if (selectedUser.value) {
        usersStore.deleteUser(selectedUser.value.id)
            .then((res) => {
                if (res.success) {
                    showDeleteModal.value = false
                    selectedUser.value = null
                }
            })
    }
}



onMounted(() => {
    loadUsers()
    rbacStore.fetchRoles()
})
</script>
