<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useUsersStore } from '../../stores/users'
import { useRBACStore } from '../../stores/RBAC'
import Select from 'primevue/select'
import AppSpinner from '../../components/core/AppSpinner.vue'

const route = useRoute()
const router = useRouter()
const usersStore = useUsersStore()
const rbacStore = useRBACStore()

const isEdit = computed(() => !!route.params.id)
const pageTitle = computed(() => isEdit.value ? 'تعديل المستخدم' : 'إضافة مستخدم جديد')
const isLoading = computed(() => usersStore.loading || rbacStore.rolesLoading)
const showPassword = ref(false)

const form = ref({
    name: '',
    email: '',
    password: '',
    role: ''
})

const errors = ref({})

// Dynamic role options from backend
// Dynamic role options from backend
const roleOptions = computed(() => {
    return rbacStore.roles.map(role => ({
        label: role.name,
        value: role.name
    }))
})

function handleSubmit() {
    errors.value = {}

    const savePromise = isEdit.value
        ? usersStore.updateUser(route.params.id, form.value)
        : usersStore.createUser(form.value)

    savePromise
        .then((result) => {
            if (result.success) {
                router.push({ name: 'users' })
            } else if (result.errors) {
                errors.value = result.errors
            }
        })
}

function loadUser() {
    if (isEdit.value) {
        usersStore.fetchUser(route.params.id)
            .then((result) => {
                if (result.success) {
                    form.value = {
                        name: result.data.name,
                        email: result.data.email,
                        password: '',
                        role: result.data.role || ''
                    }
                }
            })
    }
}

onMounted(() => {
    rbacStore.fetchRoles().then(() => {
        // Set default role after roles are loaded
        if (!isEdit.value && roleOptions.value.length > 0) {
            form.value.role = roleOptions.value[0].value
        }
    })
    loadUser()
})
</script>

<template>
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex items-center gap-4">
            <button @click="router.back()"
                class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ pageTitle }}</h1>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="isLoading && isEdit" class="flex items-center justify-center py-12">
            <AppSpinner size="lg" text="جاري التحميل..." />
        </div>

        <!-- Form -->
        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-6">بيانات المستخدم</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            الاسم <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.name" type="text" class="input" required />
                        <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            البريد الإلكتروني <span class="text-red-500">*</span>
                        </label>
                        <input v-model="form.email" type="email" class="input" dir="ltr" required />
                        <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            كلمة المرور
                            <span v-if="!isEdit" class="text-red-500">*</span>
                            <span v-else class="text-slate-400">(اتركها فارغة للإبقاء على الحالية)</span>
                        </label>
                        <div class="relative">
                            <input v-model="form.password" :type="showPassword ? 'text' : 'password'"
                                class="input pr-10" dir="ltr" :required="!isEdit" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                tabindex="-1">
                                <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password[0] }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            الدور <span class="text-red-500">*</span>
                        </label>
                        <Select v-model="form.role" :options="roleOptions" optionLabel="label" optionValue="value"
                            :loading="rbacStore.rolesLoading" placeholder="اختر الدور" class="w-full" required />
                        <p v-if="errors.role" class="text-red-500 text-sm mt-1">{{ errors.role[0] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            <span class="font-medium">عميل:</span> يمكنه مشاهدة الكتب فقط<br>
                            <span class="font-medium">مشرف/مدير النظام:</span> صلاحيات كاملة أو شبه كاملة
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-4">
                <button type="button" @click="router.back()" class="btn btn-secondary">
                    إلغاء
                </button>
                <button type="submit" :disabled="usersStore.loading" class="btn btn-primary flex items-center gap-2">
                    <svg v-if="usersStore.loading" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    {{ usersStore.loading ? 'جاري الحفظ...' : 'حفظ' }}
                </button>
            </div>
        </form>
    </div>
</template>
