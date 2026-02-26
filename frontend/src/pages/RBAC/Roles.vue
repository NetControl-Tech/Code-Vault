<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">إدارة الأدوار</h1>
            <Button label="إضافة دور جديد" icon="pi pi-plus" @click="openNew" />
        </div>

        <!-- Roles Table Component -->
        <RolesTable @clickEdit="editRole" />

        <!-- Role Dialog -->
        <Dialog v-model:visible="dialogVisible" :header="isEdit ? 'تعديل الدور' : 'دور جديد'"
            :style="{ width: '500px' }" modal>
            <div class="space-y-4 pt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-medium">اسم الدور</label>
                    <InputText v-model="form.name" :class="{ 'p-invalid': errors.name }"
                        placeholder="مثلاً: Admin, Editor" />
                    <small v-if="errors.name" class="p-error">{{ errors.name[0] }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label class="font-medium">الصلاحيات</label>
                    <div class="grid grid-cols-2 gap-3 max-h-60 overflow-y-auto p-2 border rounded-lg">
                        <div v-for="permission in rbacStore.permissions" :key="permission.id"
                            class="flex items-center gap-2">
                            <Checkbox v-model="form.permissions" :inputId="`perm-${permission.id}`" name="permissions"
                                :value="permission.name" />
                            <label :for="`perm-${permission.id}`" class="text-sm cursor-pointer">{{ permission.name_ar
                                }}</label>
                        </div>
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="إلغاء" severity="secondary" text @click="dialogVisible = false" />
                <Button label="حفظ" @click="saveRole" :loading="rbacStore.rolesLoading" />
            </template>
        </Dialog>

        <Toast position="top-left" />
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { useRBACStore } from '../../stores/RBAC'
import RolesTable from './RolesTable.vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Checkbox from 'primevue/checkbox'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'

const rbacStore = useRBACStore()
const toast = useToast()

const dialogVisible = ref(false)
const selectedRole = ref(null)
const form = ref({
    name: '',
    permissions: []
})
const errors = ref({})
const isEdit = ref(false)

onMounted(() => {
    // Roles are fetched by RolesTable
    rbacStore.fetchPermissions()
})

const openNew = () => {
    form.value = { name: '', permissions: [] }
    isEdit.value = false
    errors.value = {}
    dialogVisible.value = true
}

const editRole = (role) => {
    selectedRole.value = role
    form.value = {
        name: role.name,
        permissions: role.permissions ? role.permissions.map(p => p.name) : []
    }
    isEdit.value = true
    errors.value = {}
    dialogVisible.value = true
}

const saveRole = () => {
    errors.value = {}
    const action = isEdit.value
        ? rbacStore.updateRole(selectedRole.value.id, form.value)
        : rbacStore.createRole(form.value)

    action.then((result) => {
        if (result.success) {
            toast.add({ severity: 'success', summary: 'نجاح', detail: isEdit.value ? 'تم تحديث الدور بنجاح' : 'تم إنشاء الدور بنجاح', life: 3000 })
            dialogVisible.value = false
        } else if (result.errors) {
            errors.value = result.errors
        } else {
            toast.add({ severity: 'error', summary: 'خطأ', detail: result.message, life: 3000 })
        }
    })
}
</script>