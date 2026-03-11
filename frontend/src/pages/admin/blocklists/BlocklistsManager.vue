<template>
    <div class="space-y-6">
        <!-- PrimeVue Toast -->
        <Toast position="top-left" />

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">إدارة القوائم والدروع</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">إدارة الروابط المحجوبة لكل درع بشكل مستقل</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="openBulkModal" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    رفع جماعي
                </button>
                <button @click="openAddModal()" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    إضافة رابط
                </button>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="card p-0 overflow-hidden">
            <div class="flex border-b border-slate-200 dark:border-slate-700 overflow-x-auto">
                <button v-for="(cat, index) in categories" :key="cat.value"
                    @click="activeTabIndex = index"
                    :class="[
                        'flex items-center gap-2 px-5 py-3.5 text-sm font-medium transition-colors duration-200 whitespace-nowrap border-b-2 -mb-px',
                        activeTabIndex === index
                            ? 'border-sky-500 text-sky-600 dark:text-sky-400'
                            : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:border-slate-300'
                    ]">
                    <svg v-if="cat.value === 'family'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    <svg v-else-if="cat.value === 'social'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <svg v-else-if="cat.value === 'ads'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                    {{ cat.label }}
                </button>
            </div>

            <!-- Search Bar -->
            <div class="card p-4 rounded-none border-0 border-b border-slate-200 dark:border-slate-700 shadow-none">
                <div class="flex items-center justify-between gap-4">
                    <p class="text-sm text-slate-500 dark:text-slate-400 hidden sm:block">{{ categories[activeTabIndex].desc }}</p>
                    <div class="flex items-end gap-2 max-w-xs w-full">
                        <InputText v-model="searchInput" placeholder="ابحث عن رابط..." class="w-full" />
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
            <div v-else-if="domains.length" class="table-container border-0 rounded-none">
                <table class="table">
                    <thead>
                        <tr>
                            <th>نطاق الرابط (Domain)</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="domain in domains" :key="domain.id">
                            <td>
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ domain.domain }}</span>
                            </td>
                            <td class="text-slate-500 dark:text-slate-400">
                                {{ domain.created_at ? new Date(domain.created_at).toLocaleDateString('ar-EG') : '-' }}
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <button @click="openAddModal(domain)"
                                        class="btn text-xs px-3 py-1.5 bg-sky-600 text-white hover:bg-sky-700 rounded-lg"
                                        v-tooltip.top="'تعديل'">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button @click="confirmDelete(domain)"
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
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-slate-900 dark:text-white mb-2">لا توجد روابط</h3>
                <p class="text-slate-600 dark:text-slate-400 mb-4">لم تتم إضافة أي رابط لهذه القائمة بعد.</p>
            </div>

            <!-- Pagination -->
            <div v-if="meta.last_page > 1" class="px-4 py-3 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        عرض الصفحة {{ meta.current_page }} من {{ meta.last_page }}
                        (إجمالي {{ meta.total }} رابط)
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

        <!-- Add/Edit Domain Modal -->
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
                        {{ domainForm.id ? 'تعديل رابط' : 'إضافة رابط جديد' }}
                    </h3>
                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">نطاق الرابط (مثال: example.com)</label>
                            <InputText v-model="domainForm.domain" class="w-full" :disabled="submitting" placeholder="example.com" />
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button @click="showAddModal = false" class="btn btn-secondary" :disabled="submitting">إلغاء</button>
                        <button @click="saveDomain" class="btn btn-primary" :disabled="submitting || !domainForm.domain">
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

        <!-- Bulk Upload Modal -->
        <div v-if="showBulkModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="!submitting && (showBulkModal = false)">
            <div class="card max-w-md w-full overflow-hidden">
                <div class="h-1 bg-green-500"></div>
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4 text-slate-900 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        الرفع الجماعي للروابط
                    </h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 leading-relaxed">
                        قم برفع ملف نصي (.txt) أو (.csv) يحتوي على الروابط. سيتم تجاهل المكرر والغير الصالح تلقائياً.
                        <br><strong class="text-sky-600">سيتم الإضافة لقائمة: {{ categories[activeTabIndex].label }}</strong>
                    </p>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1 text-slate-700 dark:text-slate-300">اختر ملف</label>
                        <input type="file" accept=".txt,.csv" @change="onFileSelect"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 dark:file:bg-sky-900/30 dark:file:text-sky-300 cursor-pointer" />
                    </div>
                    <div v-if="uploadedFile"
                        class="mb-4 p-3 bg-sky-50 dark:bg-sky-900/20 text-sky-700 dark:text-sky-300 rounded-lg border border-sky-100 dark:border-sky-800 flex items-center gap-2 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>{{ uploadedFile.name }} ({{ (uploadedFile.size / 1024).toFixed(1) }} KB)</span>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button @click="showBulkModal = false" class="btn btn-secondary" :disabled="submitting">إلغاء</button>
                        <button @click="uploadFile" class="btn bg-green-600 hover:bg-green-700 text-white" :disabled="submitting || !uploadedFile">
                            <svg v-if="submitting" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                            {{ submitting ? 'جاري الرفع...' : 'بدء الرفع' }}
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
                        هل أنت متأكد من حذف الرابط <strong class="text-slate-900 dark:text-white">{{ domainToDelete?.domain }}</strong>؟ لا يمكن التراجع عن هذا الإجراء.
                    </p>
                    <div class="flex gap-3 justify-end">
                        <button @click="showDeleteModal = false" class="btn btn-secondary" :disabled="deleting">إلغاء</button>
                        <button @click="deleteDomain" class="btn btn-danger" :disabled="deleting">
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
import { ref, onMounted, watch } from 'vue'
import { blocklistApi } from '../../../services/blocklistApi'
import { useToast } from 'primevue/usetoast'
import Toast from 'primevue/toast'
import InputText from 'primevue/inputtext'
import AppSpinner from '../../../components/core/AppSpinner.vue'

const toast = useToast()

const categories = [
    { label: 'أمان الأسرة', value: 'family', desc: 'المواقع الإباحية والضارة' },
    { label: 'السوشيال ميديا', value: 'social', desc: 'منصات التواصل الاجتماعي' },
    { label: 'الإعلانات', value: 'ads', desc: 'سيرفرات الإعلانات المزعجة' },
    { label: 'الخصوصية', value: 'privacy', desc: 'متتبعات البيانات (Trackers)' }
]

const activeTabIndex = ref(0)
const activeCategory = ref(categories[0].value)

const domains = ref([])
const loading = ref(false)
const meta = ref({ current_page: 1, last_page: 1, total: 0 })
const searchInput = ref('')

// Modal states
const showAddModal = ref(false)
const showBulkModal = ref(false)
const showDeleteModal = ref(false)
const submitting = ref(false)
const deleting = ref(false)

const domainForm = ref({ id: null, domain: '' })
const domainToDelete = ref(null)
const uploadedFile = ref(null)

function loadData(page = 1) {
    loading.value = true
    const params = { page, per_page: 15, category: activeCategory.value }
    if (searchInput.value) params.search = searchInput.value

    blocklistApi.getBlocklists(activeCategory.value, params)
        .then(res => {
            domains.value = res.data.data
            meta.value = {
                current_page: res.data.current_page,
                last_page: res.data.last_page,
                total: res.data.total
            }
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'خطأ', detail: 'فشل في تحميل الروابط', life: 3000 })
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

watch(activeTabIndex, (newIndex) => {
    activeCategory.value = categories[newIndex].value
    searchInput.value = ''
    loadData(1)
})

// Add/Edit
function openAddModal(domain = null) {
    if (domain) {
        domainForm.value = { ...domain }
    } else {
        domainForm.value = { id: null, domain: '' }
    }
    showAddModal.value = true
}

function saveDomain() {
    if (!domainForm.value.domain) return
    submitting.value = true

    const payload = { domain: domainForm.value.domain, category: activeCategory.value }
    const promise = domainForm.value.id
        ? blocklistApi.updateDomain(domainForm.value.id, payload)
        : blocklistApi.addDomain(payload)

    promise.then(() => {
        toast.add({ severity: 'success', summary: 'نجاح', detail: domainForm.value.id ? 'تم تحديث الرابط بنجاح' : 'تم إضافة الرابط بنجاح', life: 3000 })
        showAddModal.value = false
        loadData(meta.value.current_page)
    }).catch(err => {
        toast.add({ severity: 'error', summary: 'خطأ', detail: err.response?.data?.message || 'فشل في حفظ الرابط', life: 4000 })
    }).finally(() => {
        submitting.value = false
    })
}

// Delete
function confirmDelete(domain) {
    domainToDelete.value = domain
    showDeleteModal.value = true
}

function deleteDomain() {
    deleting.value = true
    blocklistApi.deleteDomain(domainToDelete.value.id)
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

// Bulk Upload
function openBulkModal() {
    uploadedFile.value = null
    showBulkModal.value = true
}

function onFileSelect(event) {
    uploadedFile.value = event.target.files[0] || null
}

function uploadFile() {
    if (!uploadedFile.value) return
    submitting.value = true

    blocklistApi.bulkUpload(uploadedFile.value, activeCategory.value)
        .then(res => {
            toast.add({
                severity: 'success',
                summary: 'نجاح',
                detail: `تم إضافة ${res.data.inserted_count} روابط متجاوزاً ${res.data.duplicate_count} متكرر.`,
                life: 5000
            })
            showBulkModal.value = false
            uploadedFile.value = null
            loadData(1)
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'خطأ', detail: 'فشل رفع الملف', life: 3000 })
        })
        .finally(() => {
            submitting.value = false
        })
}

onMounted(() => {
    loadData()
})
</script>