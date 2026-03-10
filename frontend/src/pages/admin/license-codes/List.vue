<template>
    <div class="space-y-6">
        <!-- PrimeVue Toast -->
        <Toast position="top-left" />

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">إدارة أكواد التفعيل</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">توليد وإدارة وتصدير أكواد التفعيل للبرنامج</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="openGenerateModal" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    توليد دفعة جديدة
                </button>
                <button @click="openActivateModal" class="btn bg-green-600 text-white hover:bg-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    تفعيل أكواد
                </button>
                <button @click="openDeleteModal" class="btn btn-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    حذف أكواد
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="card p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">بحث</label>
                    <InputText v-model="filters.search" class="w-full" :useGrouping="false"
                        placeholder="ابحث بالرقم التسلسلي..." />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">الحالة</label>
                    <Select v-model="filters.status" :options="statusOptions" optionLabel="label" optionValue="value"
                        placeholder="الكل" showClear class="w-full" />
                </div>
                <div class="flex items-end gap-2">
                    <button @click="applyFilters" class="btn btn-primary flex-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        بحث
                    </button>
                    <button @click="clearFilters" class="btn btn-secondary flex-1">مسح</button>
                    <button @click="exportData" class="btn bg-green-600 text-white hover:bg-green-700 flex-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        تصدير Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="licenseCodesStore.loading" class="flex items-center justify-center py-12">
            <AppSpinner size="md" text="جاري تحميل البيانات..." />
        </div>

        <!-- Table -->
        <div v-else-if="licenseCodesStore.codes.length" class="card overflow-hidden">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>الرقم التسلسلي</th>
                            <th>مدة الترخيص</th>
                            <th>تاريخ الانتهاء</th>
                            <th>معرف الجهاز</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="code in licenseCodesStore.codes" :key="code.id">
                            <td>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">
                                    {{ code.serial }}
                                </span>
                            </td>
                            <td>{{ code.duration_days }} يوم</td>

                            <td class="text-slate-500 dark:text-slate-400">
                                {{ code.expires_at ? new Date(code.expires_at).toLocaleDateString() : '-' }}
                            </td>
                            <td class="text-slate-500 dark:text-slate-400">
                                {{ code.device_hardware_id || '-' }}
                            </td>
                            <td>
                                <span :class="[
                                    'badge',
                                    code.status === 'active' ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300' : '',
                                    code.status === 'inactive' ? 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300' : '',
                                    code.status === 'redeemed' ? 'bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300' : ''
                                ]">
                                    {{ code.status === 'active' ? 'مفعل' : (code.status === 'inactive' ? 'غير مفعل' :
                                        'مستخدم') }}
                                </span>
                            </td>
                            <td>
                                <button v-if="code.status === 'redeemed' && code.device_id"
                                    @click="handleRevokeToken(code)"
                                    class="btn text-xs px-3 py-1.5 bg-red-600 text-white hover:bg-red-700 rounded-lg"
                                    :disabled="revokingDeviceId === code.device_id" v-tooltip.top="'إلغاء توكن الجهاز'">
                                    <svg v-if="revokingDeviceId !== code.device_id" xmlns="http://www.w3.org/2000/svg"
                                        class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    <svg v-else class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                    </svg>
                                    {{ revokingDeviceId === code.device_id ? 'جاري...' : 'إلغاء التوكن' }}
                                </button>
                                <span v-else class="text-slate-400 text-sm">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="licenseCodesStore.meta.last_page > 1"
                class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        عرض الصفحة {{ licenseCodesStore.meta.current_page }} من {{ licenseCodesStore.meta.last_page }}
                        (إجمالي {{ licenseCodesStore.meta.total }} كود)
                    </p>
                    <div class="flex gap-2">
                        <button @click="prevPage" :disabled="licenseCodesStore.meta.current_page === 1"
                            class="px-4 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 transition-colors">السابق</button>
                        <button @click="nextPage"
                            :disabled="licenseCodesStore.meta.current_page === licenseCodesStore.meta.last_page"
                            class="px-4 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-slate-800 dark:text-slate-300 dark:border-slate-600 dark:hover:bg-slate-700 transition-colors">التالي</button>
                    </div>
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
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">لا يوجد أكواد!</h3>
            <p class="text-slate-600 dark:text-slate-400 mb-4">اضغط على زر توليد الدفعة الجديدة للبدء</p>
        </div>

        <!-- Generate Modal -->
        <div v-if="showGenerateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="!generateLoading && (showGenerateModal = false)">
            <div class="card max-w-sm w-full overflow-hidden">
                <div class="h-1 bg-sky-500"></div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-slate-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-sky-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        توليد دفعة جديدة
                    </h3>
                    <div class="space-y-4 mb-6">
                        <div>
                            <label
                                class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">العدد</label>
                            <InputText v-model="generateForm.count" :min="1" :max="10000" class="w-full"
                                :disabled="generateLoading" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">مدة الترخيص
                                (بالأيام)</label>
                            <InputText v-model="generateForm.duration_days" :min="1" class="w-full"
                                :disabled="generateLoading" placeholder="30" />
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button @click="showGenerateModal = false" class="btn btn-secondary"
                            :disabled="generateLoading">إلغاء</button>
                        <button @click="confirmGenerate" class="btn btn-primary" :disabled="generateLoading">
                            <svg v-if="generateLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ generateLoading ? 'جاري التوليد...' : 'توليد وتحميل' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activate Range Modal -->
        <div v-if="showActivateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="!activateLoading && (showActivateModal = false)">
            <div class="card max-w-sm w-full overflow-hidden">
                <div class="h-1 bg-green-500"></div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-slate-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        تفعيل أكواد بالجملة
                    </h3>
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">من الرقم
                                التسلسلي (Serial)</label>
                            <InputText v-model="activateForm.from_serial" :min="1" class="w-full"
                                :disabled="activateLoading" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">إلى الرقم
                                التسلسلي (Serial)</label>
                            <InputText v-model="activateForm.to_serial" :min="1" class="w-full"
                                :disabled="activateLoading" />
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button @click="showActivateModal = false" class="btn btn-secondary"
                            :disabled="activateLoading">إلغاء</button>
                        <button @click="confirmActivate" class="btn bg-green-600 hover:bg-green-700 text-white"
                            :disabled="activateLoading">
                            <svg v-if="activateLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ activateLoading ? 'جاري التفعيل...' : 'تفعيل' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Range Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="!deleteLoading && (showDeleteModal = false)">
            <div class="card max-w-sm w-full overflow-hidden">
                <div class="h-1 bg-red-500"></div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-red-600 dark:text-red-400 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        حذف أكواد بالجملة
                    </h3>
                    <p class="mb-4 text-sm text-slate-600 dark:text-slate-400">سيتم حذف جميع الأكواد في النطاق المحدد
                        بشكل نهائي.</p>
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">من الرقم
                                التسلسلي (Serial)</label>
                            <InputText v-model="deleteForm.from_serial" :min="1" class="w-full"
                                :disabled="deleteLoading" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">إلى الرقم
                                التسلسلي (Serial)</label>
                            <InputText v-model="deleteForm.to_serial" :min="1" class="w-full"
                                :disabled="deleteLoading" />
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button @click="showDeleteModal = false" class="btn btn-secondary"
                            :disabled="deleteLoading">إلغاء</button>
                        <button @click="confirmDelete" class="btn btn-danger" :disabled="deleteLoading">
                            <svg v-if="deleteLoading" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4" />
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ deleteLoading ? 'جاري الحذف...' : 'تأكيد الحذف' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useLicenseCodesStore } from '../../../stores/licenseCodes'
import { useToast } from 'primevue/usetoast'
import Select from 'primevue/select'
import Toast from 'primevue/toast'
import AppSpinner from '../../../components/core/AppSpinner.vue'
import InputText from 'primevue/inputtext'

const licenseCodesStore = useLicenseCodesStore()
const toast = useToast()

const filters = ref({ search: '', status: '' })
const statusOptions = [
    { label: 'مفعل', value: 'active' },
    { label: 'غير مفعل', value: 'inactive' },
    { label: 'مستخدم', value: 'redeemed' }
]

const showGenerateModal = ref(false)
const showActivateModal = ref(false)
const showDeleteModal = ref(false)

const generateLoading = ref(false)
const activateLoading = ref(false)
const deleteLoading = ref(false)
const revokingDeviceId = ref(null)

const generateForm = ref({ count: 100, duration_days: 30 })
const activateForm = ref({ from_serial: null, to_serial: null })
const deleteForm = ref({ from_serial: null, to_serial: null })

function loadData(page = 1) {
    const params = { page, per_page: 15 }
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.status) params.status = filters.value.status
    licenseCodesStore.fetchLicenseCodes(params)
}

function prevPage() {
    if (licenseCodesStore.meta.current_page > 1) {
        loadData(licenseCodesStore.meta.current_page - 1)
    }
}

function nextPage() {
    if (licenseCodesStore.meta.current_page < licenseCodesStore.meta.last_page) {
        loadData(licenseCodesStore.meta.current_page + 1)
    }
}

function applyFilters() {
    loadData(1)
}

function clearFilters() {
    filters.value = { search: '', status: '' }
    loadData(1)
}

function exportData() {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.status) params.status = filters.value.status
    licenseCodesStore.exportLicenseCodes(params)
}

function openGenerateModal() {
    generateForm.value = { count: 100, duration_days: 30 }
    showGenerateModal.value = true
}

function confirmGenerate() {
    generateLoading.value = true
    licenseCodesStore.generateLicenseCodes(generateForm.value).then((res) => {
        const url = window.URL.createObjectURL(new Blob([res.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', 'generated_license_codes.xlsx')
        document.body.appendChild(link)
        link.click()
        link.remove()
        showGenerateModal.value = false
        toast.add({ severity: 'success', summary: 'نجاح', detail: 'تم توليد الأكواد وتحميل الملف بنجاح', life: 4000 })
        loadData(1)
    }).catch((err) => {
        toast.add({ severity: 'error', summary: 'خطأ', detail: err?.response?.data?.message || 'حدث خطأ أثناء توليد الأكواد', life: 5000 })
    }).finally(() => {
        generateLoading.value = false
    })
}

function openActivateModal() {
    activateForm.value = { from_serial: null, to_serial: null }
    showActivateModal.value = true
}

function confirmActivate() {
    activateLoading.value = true
    licenseCodesStore.activateRange(activateForm.value).then((res) => {
        showActivateModal.value = false
        toast.add({ severity: 'success', summary: 'نجاح', detail: res?.data?.message, life: 4000 })
        loadData(1)
    }).catch((err) => {
        toast.add({ severity: 'error', summary: 'خطأ', detail: err?.response?.data?.message || 'حدث خطأ أثناء تفعيل الأكواد', life: 5000 })
    }).finally(() => {
        activateLoading.value = false
    })
}

function openDeleteModal() {
    deleteForm.value = { from_serial: null, to_serial: null }
    showDeleteModal.value = true
}

function confirmDelete() {
    deleteLoading.value = true
    licenseCodesStore.deleteRange(deleteForm.value).then((res) => {
        showDeleteModal.value = false
        toast.add({ severity: 'success', summary: 'نجاح', detail: res?.data?.message, life: 4000 })
        loadData(1)
    }).catch((err) => {
        toast.add({ severity: 'error', summary: 'خطأ', detail: err?.response?.data?.message || 'حدث خطأ أثناء حذف الأكواد', life: 5000 })
    }).finally(() => {
        deleteLoading.value = false
    })
}

function handleRevokeToken(code) {
    if (!confirm('هل أنت متأكد من إلغاء التوكن لهذا الجهاز؟')) return
    revokingDeviceId.value = code.device_id
    licenseCodesStore.revokeToken(code.device_id).then((res) => {
        toast.add({ severity: 'success', summary: 'نجاح', detail: res?.data?.message || 'تم إلغاء التوكن بنجاح', life: 4000 })
        loadData(1)
    }).catch((err) => {
        toast.add({ severity: 'error', summary: 'خطأ', detail: err?.response?.data?.message || 'حدث خطأ أثناء إلغاء التوكن', life: 5000 })
    }).finally(() => {
        revokingDeviceId.value = null
    })
}

onMounted(() => {
    loadData(1)
})
</script>
