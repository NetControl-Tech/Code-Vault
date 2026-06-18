<template>
    <div class="space-y-6">
        <!-- PrimeVue Toast -->
        <Toast position="top-left" />

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">إدارة التطبيقات</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">التحكم في وصول التطبيقات للإنترنت على أجهزة الأبناء</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button v-if="can('apps.create')" @click="openAddModal()" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    إضافة تطبيق
                </button>
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
            <!-- Search Bar -->
            <div class="card p-4 rounded-none border-0 border-b border-slate-200 dark:border-slate-700 shadow-none">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm text-slate-500 dark:text-slate-400 hidden sm:block">
                        التطبيقات المحجوبة تماماً (حجب) أو المسموحة مع تحذير
                    </p>
                    <div class="flex items-end gap-2 max-w-xs w-full">
                        <InputText v-model="searchInput" placeholder="ابحث باسم الحزمة..." class="w-full"
                            @keyup.enter="debounceSearch" />
                        <button @click="debounceSearch" class="btn btn-primary !py-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <AppSpinner size="md" text="جاري تحميل البيانات..." />
            </div>

            <!-- Table -->
            <div v-else-if="apps.length" class="table-container border-0 rounded-none">
                <table class="table">
                    <thead>
                        <tr>
                            <th>اسم الحزمة (Package)</th>
                            <th>سياسة الإنترنت</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="app in apps" :key="app.id">
                            <td>
                                <span class="font-mono text-sm font-semibold text-slate-700 dark:text-slate-200">{{ app.package_name }}</span>
                            </td>
                            <td>
                                <!-- Badge + inline quick-toggle -->
                                <button v-if="can('apps.update')" @click="toggleBlock(app)"
                                    :disabled="togglingId === app.id"
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium transition-colors disabled:opacity-50',
                                        app.internet_block
                                            ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-300'
                                            : 'bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-300'
                                    ]"
                                    v-tooltip.top="'اضغط للتبديل'">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="app.internet_block ? 'bg-red-500' : 'bg-amber-500'"></span>
                                    {{ app.internet_block ? 'حجب (VPN)' : 'تحذير' }}
                                </button>
                                <!-- Read-only badge when user lacks update permission -->
                                <span v-else
                                    :class="[
                                        'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium',
                                        app.internet_block
                                            ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'
                                            : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
                                    ]">
                                    {{ app.internet_block ? 'حجب (VPN)' : 'تحذير' }}
                                </span>
                            </td>
                            <td class="text-slate-500 dark:text-slate-400">
                                {{ app.created_at ? new Date(app.created_at).toLocaleDateString('ar-EG') : '-' }}
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button v-if="can('apps.update')" @click="openAddModal(app)"
                                        class="btn text-xs px-3 py-1.5 bg-sky-600 text-white hover:bg-sky-700 rounded-lg"
                                        v-tooltip.top="'تعديل'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button v-if="can('apps.delete')" @click="confirmDelete(app)"
                                        class="btn text-xs px-3 py-1.5 bg-red-600 text-white hover:bg-red-700 rounded-lg"
                                        v-tooltip.top="'حذف'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
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
                <div class="w-16 h-16 mx-auto mb-4 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">لا توجد تطبيقات</h3>
                <p class="text-slate-600 dark:text-slate-400 mb-4">لم تتم إضافة أي تطبيق للقائمة بعد.</p>
            </div>

            <!-- Pagination -->
            <div v-if="meta.last_page > 1" class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        عرض الصفحة {{ meta.current_page }} من {{ meta.last_page }}
                        (إجمالي {{ meta.total }} تطبيق)
                    </p>
                    <div class="flex gap-2">
                        <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page === 1"
                            class="px-4 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 transition-colors">السابق</button>
                        <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page"
                            class="px-4 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 transition-colors">التالي</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit App Modal -->
        <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="!submitting && (showAddModal = false)">
            <div class="card max-w-sm w-full overflow-hidden">
                <div class="h-1 bg-sky-500"></div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-slate-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-sky-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ appForm.id ? 'تعديل تطبيق' : 'إضافة تطبيق جديد' }}
                    </h3>
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">اسم الحزمة (مثال: com.facebook.katana)</label>
                            <InputText v-model="appForm.package_name" class="w-full" :disabled="submitting"
                                placeholder="com.example.app" dir="ltr" />
                        </div>
                        <!-- internet_block toggle -->
                        <label class="flex items-center justify-between gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer">
                            <div>
                                <span class="block text-sm font-medium text-slate-700 dark:text-slate-300">حجب الإنترنت</span>
                                <span class="block text-xs text-slate-500 dark:text-slate-400">
                                    {{ appForm.internet_block ? 'حجب كامل عبر VPN' : 'مسموح مع رسالة تحذير' }}
                                </span>
                            </div>
                            <input type="checkbox" v-model="appForm.internet_block" :disabled="submitting"
                                class="h-5 w-5 rounded text-sky-600 focus:ring-sky-500 cursor-pointer" />
                        </label>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button @click="showAddModal = false" class="btn btn-secondary" :disabled="submitting">إلغاء</button>
                        <button @click="saveApp" class="btn btn-primary" :disabled="submitting || !appForm.package_name">
                            <svg v-if="submitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ submitting ? 'جاري الحفظ...' : 'حفظ' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="!deleting && (showDeleteModal = false)">
            <div class="card max-w-sm w-full overflow-hidden">
                <div class="h-1 bg-red-500"></div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-red-600 dark:text-red-400 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        تأكيد الحذف
                    </h3>
                    <p class="mb-6 text-sm text-slate-600 dark:text-slate-400">
                        هل أنت متأكد من حذف التطبيق <strong class="text-slate-900 dark:text-white font-mono">{{ appToDelete?.package_name }}</strong>؟ لا يمكن التراجع عن هذا الإجراء.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button @click="showDeleteModal = false" class="btn btn-secondary" :disabled="deleting">إلغاء</button>
                        <button @click="deleteApp" class="btn btn-danger" :disabled="deleting">
                            <svg v-if="deleting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ deleting ? 'جاري الحذف...' : 'تأكيد الحذف' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { appsApi } from '../../../services/appsApi'
import { useCan } from '../../../composables/useCan'
import { useToast } from 'primevue/usetoast'
import Toast from 'primevue/toast'
import InputText from 'primevue/inputtext'
import AppSpinner from '../../../components/core/AppSpinner.vue'

const toast = useToast()
const { can } = useCan()

const apps = ref([])
const loading = ref(false)
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const searchInput = ref('')

// Modal states
const showAddModal = ref(false)
const showDeleteModal = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const togglingId = ref(null)

const appForm = ref({ id: null, package_name: '', internet_block: true })
const appToDelete = ref(null)

function loadData(page = 1) {
    loading.value = true
    const params = { page, per_page: 15 }
    if (searchInput.value) params.search = searchInput.value

    appsApi.getApps(params)
        .then(res => {
            apps.value = res.data.data
            meta.value = {
                current_page: res.data.current_page,
                last_page: res.data.last_page,
                total: res.data.total
            }
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'خطأ', detail: 'فشل في تحميل التطبيقات', life: 3000 })
        })
        .finally(() => {
            loading.value = false
        })
}

function goToPage(page) {
    if (page >= 1 && page <= meta.value.last_page) {
        loadData(page)
    }
}

function debounceSearch() {
    loadData(1)
}

// Add/Edit
function openAddModal(app = null) {
    if (app) {
        appForm.value = { id: app.id, package_name: app.package_name, internet_block: app.internet_block }
    } else {
        appForm.value = { id: null, package_name: '', internet_block: true }
    }
    showAddModal.value = true
}

function saveApp() {
    if (!appForm.value.package_name) return
    submitting.value = true

    const payload = {
        package_name: appForm.value.package_name,
        internet_block: appForm.value.internet_block
    }
    const promise = appForm.value.id
        ? appsApi.updateApp(appForm.value.id, payload)
        : appsApi.addApp(payload)

    promise.then(() => {
        toast.add({ severity: 'success', summary: 'نجاح', detail: appForm.value.id ? 'تم تحديث التطبيق بنجاح' : 'تم إضافة التطبيق بنجاح', life: 3000 })
        showAddModal.value = false
        loadData(meta.value.current_page)
    }).catch(err => {
        toast.add({ severity: 'error', summary: 'خطأ', detail: err.response?.data?.message || 'فشل في حفظ التطبيق', life: 4000 })
    }).finally(() => {
        submitting.value = false
    })
}

// Inline quick-toggle (update endpoint requires package_name alongside internet_block)
function toggleBlock(app) {
    togglingId.value = app.id
    appsApi.updateApp(app.id, { package_name: app.package_name, internet_block: !app.internet_block })
        .then(() => {
            app.internet_block = !app.internet_block
            toast.add({ severity: 'success', summary: 'نجاح', detail: 'تم تحديث سياسة الإنترنت', life: 2000 })
        })
        .catch(err => {
            toast.add({ severity: 'error', summary: 'خطأ', detail: err.response?.data?.message || 'فشل في التحديث', life: 3000 })
        })
        .finally(() => {
            togglingId.value = null
        })
}

// Delete
function confirmDelete(app) {
    appToDelete.value = app
    showDeleteModal.value = true
}

function deleteApp() {
    deleting.value = true
    appsApi.deleteApp(appToDelete.value.id)
        .then(() => {
            toast.add({ severity: 'success', summary: 'نجاح', detail: 'تم الحذف بنجاح', life: 3000 })
            showDeleteModal.value = false
            loadData(meta.value.current_page)
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'خطأ', detail: 'فشل في الحذف', life: 3000 })
        })
        .finally(() => {
            deleting.value = false
        })
}

onMounted(() => {
    loadData()
})
</script>
