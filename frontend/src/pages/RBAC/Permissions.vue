<template>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">إدارة الصلاحيات</h1>
            <Button label="إضافة صلاحية جديدة" icon="pi pi-plus" @click="openNew" />
        </div>

        <!-- Permissions Table Component -->
        <PermissionsTable @clickEdit="editPermission" />

        <!-- Permission Dialog -->
        <Dialog v-model:visible="dialogVisible" :header="isEdit ? 'تعديل الصلاحية' : 'صلاحية جديدة'"
            :style="{ width: '400px' }" modal>
            <div class="space-y-4 pt-4">
                <div class="flex flex-col gap-2">
                    <label class="font-medium">اسم الصلاحية</label>
                    <InputText v-model="form.name" :class="{ 'p-invalid': errors.name }"
                        placeholder="مثلاً:  edit users" />
                    <small v-if="errors.name" class="p-error">{{ errors.name[0] }}</small>
                </div>
            </div>
            <template #footer>
                <Button label="إلغاء" severity="secondary" text @click="dialogVisible = false" />
                <Button label="حفظ" @click="savePermission" :loading="rbacStore.permissionsLoading" />
            </template>
        </Dialog>

        <Toast position="top-left" />
    </div>
</template>
<script setup>
import { ref } from 'vue'
import { useRBACStore } from '../../stores/RBAC'
import PermissionsTable from './PermissionsTable.vue'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Toast from 'primevue/toast'
import { useToast } from 'primevue/usetoast'

const rbacStore = useRBACStore()
const toast = useToast()

const dialogVisible = ref(false)
const selectedPermission = ref(null)
const form = ref({
    name: ''
})
const errors = ref({})
const isEdit = ref(false)

const openNew = () => {
    form.value = { name: '' }
    isEdit.value = false
    errors.value = {}
    dialogVisible.value = true
}

const editPermission = (permission) => {
    selectedPermission.value = permission
    form.value = {
        name: permission.name
    }
    isEdit.value = true
    errors.value = {}
    dialogVisible.value = true
}

const savePermission = () => {
    errors.value = {}
    const action = isEdit.value
        ? rbacStore.updatePermission(selectedPermission.value.id, form.value)
        : rbacStore.createPermission(form.value)

    action.then((result) => {
        if (result.success) {
            toast.add({ severity: 'success', summary: 'نجاح', detail: isEdit.value ? 'تم تحديث الصلاحية بنجاح' : 'تم إنشاء الصلاحية بنجاح', life: 3000 })
            dialogVisible.value = false
        } else if (result.errors) {
            errors.value = result.errors
        } else {
            toast.add({ severity: 'error', summary: 'خطأ', detail: result.message, life: 3000 })
        }
    })
}
</script>