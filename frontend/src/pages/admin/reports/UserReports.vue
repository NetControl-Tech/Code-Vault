<template>
    <div class="space-y-6">
        <!-- PrimeVue Toast -->
        <Toast position="top-left" />

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">مراجعة اقتراحات المستخدمين</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">راجع الروابط التي اقترحها الآباء للحجب وقرر إضافتها</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="card p-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">حالة الاقتراح</label>
                    <Select v-model="selectedStatus" :options="statuses" optionLabel="label" optionValue="value"
                        @change="onStatusChange" class="w-full" />
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button @click="loadData(1)" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        تحديث القائمة
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-12">
            <AppSpinner size="md" text="جاري تحميل البيانات..." />
        </div>

        <!-- Table -->
        <div v-else-if="reports.length" class="card overflow-hidden">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>الرابط المُقترح</th>
                            <th>مُرسَل بواسطة</th>
                            <th>تاريخ الإرسال</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="report in reports" :key="report.id">
                            <td>
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-semibold text-slate-700 dark:text-slate-200 truncate max-w-xs" :title="report.url">{{ report.url }}</span>
                                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ report.domain }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs font-mono bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded text-slate-600 dark:text-slate-300">
                                    {{ report.device?.device_id || 'غير معروف' }}
                                </span>
                            </td>
                            <td class="text-slate-500 dark:text-slate-400">
                                {{ report.created_at ? new Date(report.created_at).toLocaleDateString('ar-EG') : '-' }}
                            </td>
                            <td>
                                <span :class="[
                                    'badge',
                                    report.status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-300' : '',
                                    report.status === 'approved' ? 'bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-300' : '',
                                    report.status === 'rejected' ? 'bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-300' : ''
                                ]">
                                    {{ report.status === 'pending' ? 'قيد المراجعة' : (report.status === 'approved' ? 'مقبول' : 'مرفوض') }}
                                </span>
                            </td>
                            <td>
                                <!-- Pending Actions -->
                                <div v-if="report.status === 'pending'" class="flex gap-2">
                                    <button @click="openApproveModal(report)"
                                        class="btn text-xs px-3 py-1.5 bg-green-600 text-white hover:bg-green-700 rounded-lg"
                                        v-tooltip.top="'موافقة'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        موافقة
                                    </button>
                                    <button @click="openRejectModal(report)"
                                        class="btn text-xs px-3 py-1.5 bg-red-600 text-white hover:bg-red-700 rounded-lg"
                                        v-tooltip.top="'تجاهل'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        تجاهل
                                    </button>
                                </div>
                                <!-- Approved Status -->
                                <div v-else-if="report.status === 'approved'" class="text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    تمت الإضافة لدرع {{ categoriesMap[report.approved_category] || '' }}
                                </div>
                                <!-- Rejected Status -->
                                <div v-else class="text-sm text-red-500 dark:text-red-400 flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    مرفوض
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="meta.last_page > 1" class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        عرض الصفحة {{ meta.current_page }} من {{ meta.last_page }}
                        (إجمالي {{ meta.total }} اقتراح)
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

        <!-- Empty State -->
        <div v-else class="card p-12 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-slate-100 dark:bg-slate-700 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">لا توجد اقتراحات</h3>
            <p class="text-slate-600 dark:text-slate-400 mb-4">لا توجد اقتراحات بهذه الحالة حالياً.</p>
        </div>

        <!-- Approve Modal -->
        <div v-if="showApproveModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="!submitting && (showApproveModal = false)">
            <div class="card max-w-sm w-full overflow-hidden">
                <div class="h-1 bg-green-500"></div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-slate-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        الموافقة على الرابط
                    </h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                        أنت توافق على حجب الرابط <strong class="text-slate-900 dark:text-white" dir="ltr">{{ reportToApprove?.domain }}</strong>.
                        <br>الرجاء اختيار الدرع الذي سيضاف إليه هذا الرابط:
                    </p>
                    <div class="mb-6">
                        <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">قائمة الدرع</label>
                        <Select v-model="selectedCategory" :options="categoryOptions" optionLabel="label" optionValue="value"
                            placeholder="اختر قائمة الدرع" class="w-full" />
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button @click="showApproveModal = false" class="btn btn-secondary" :disabled="submitting">إلغاء</button>
                        <button @click="confirmApprove" class="btn bg-green-600 hover:bg-green-700 text-white" :disabled="submitting || !selectedCategory">
                            <svg v-if="submitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ submitting ? 'جاري الحفظ...' : 'موافقة وحفظ' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Confirm Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="!rejecting && (showRejectModal = false)">
            <div class="card max-w-sm w-full overflow-hidden">
                <div class="h-1 bg-red-500"></div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-red-600 dark:text-red-400 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        تأكيد الرفض
                    </h3>
                    <p class="mb-6 text-sm text-slate-600 dark:text-slate-400">
                        هل أنت متأكد من رفض وتجاهل الرابط <strong class="text-slate-900 dark:text-white">{{ reportToReject?.domain }}</strong>؟
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button @click="showRejectModal = false" class="btn btn-secondary" :disabled="rejecting">إلغاء</button>
                        <button @click="confirmReject" class="btn btn-danger" :disabled="rejecting">
                            <svg v-if="rejecting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ rejecting ? 'جاري الرفض...' : 'رفض الاقتراح' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { blocklistApi } from '../../../services/blocklistApi'
import { useToast } from 'primevue/usetoast'
import Toast from 'primevue/toast'
import Select from 'primevue/select'
import AppSpinner from '../../../components/core/AppSpinner.vue'

const toast = useToast()

const statuses = [
    { label: 'الكل', value: 'all' },
    { label: 'قيد المراجعة', value: 'pending' },
    { label: 'مقبول', value: 'approved' },
    { label: 'مرفوض', value: 'rejected' }
]

const categoryOptions = [
    { label: 'أمان الأسرة', value: 'family' },
    { label: 'السوشيال ميديا', value: 'social' },
    { label: 'الإعلانات', value: 'ads' },
    { label: 'الخصوصية', value: 'privacy' }
]

const categoriesMap = {
    family: 'أمان الأسرة',
    social: 'السوشيال ميديا',
    ads: 'الإعلانات',
    privacy: 'الخصوصية'
}

const reports = ref([])
const loading = ref(false)
const selectedStatus = ref('all')
const meta = ref({ current_page: 1, last_page: 1, total: 0 })

// Modal states
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const submitting = ref(false)
const rejecting = ref(false)

const reportToApprove = ref(null)
const reportToReject = ref(null)
const selectedCategory = ref(null)

function loadData(page = 1) {
    loading.value = true
    const params = { page, per_page: 15, status: selectedStatus.value }

    blocklistApi.getReports(params)
        .then(res => {
            reports.value = res.data.data
            meta.value = {
                current_page: res.data.current_page,
                last_page: res.data.last_page,
                total: res.data.total
            }
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'خطأ', detail: 'فشل في تحميل الاقتراحات', life: 3000 })
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

function onStatusChange() {
    loadData(1)
}

// Approve Logic
function openApproveModal(report) {
    reportToApprove.value = report
    selectedCategory.value = null
    showApproveModal.value = true
}

function confirmApprove() {
    if (!selectedCategory.value) return
    submitting.value = true

    blocklistApi.approveReport(reportToApprove.value.id, selectedCategory.value)
        .then(() => {
            toast.add({ severity: 'success', summary: 'تمت الموافقة', detail: 'تمت إضافة الرابط للقائمة بنجاح', life: 3000 })
            showApproveModal.value = false
            loadData(meta.value.current_page)
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'خطأ', detail: 'فشل في الموافقة على الاقتراح', life: 3000 })
        })
        .finally(() => {
            submitting.value = false
        })
}

// Reject Logic
function openRejectModal(report) {
    reportToReject.value = report
    showRejectModal.value = true
}

function confirmReject() {
    rejecting.value = true

    blocklistApi.rejectReport(reportToReject.value.id)
        .then(() => {
            toast.add({ severity: 'success', summary: 'تم الرفض', detail: 'تم رفض الاقتراح بنجاح', life: 3000 })
            showRejectModal.value = false
            loadData(meta.value.current_page)
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'خطأ', detail: 'فشل في رفض الاقتراح', life: 3000 })
        })
        .finally(() => {
            rejecting.value = false
        })
}

onMounted(() => {
    loadData()
})
</script>
