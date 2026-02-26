<template>
    <div class="managmenet_container overflow-hidden">
        <div class="table-header-section">
            <div class="flex flex-col">
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">Settings</h3>
                <p class="text-xs text-slate-500 font-medium mt-1">Found {{ settings.data ? settings.data.length : 0 }}
                    settings</p>
            </div>
            <div class="relative flex-1 md:flex-none">
                <input v-model="search" @input="filterSettings" placeholder="Search settings..."
                    class="table-search-input">
                <svg class="w-4 h-4 absolute left-3 top-3 text-slate-400" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="premium-table">
                <thead>
                    <tr>
                        <TableHeadingCell field="id">ID</TableHeadingCell>
                        <TableHeadingCell field="key">Key</TableHeadingCell>
                        <TableHeadingCell field="value">Value</TableHeadingCell>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-right text-xs text-slate-500">
                            Actions</th>
                    </tr>
                </thead>
                <tbody v-if="settingloading">
                    <tr>
                        <td colspan="4" class="table-loading">
                            <Spinner />
                            <p class="text-xs text-slate-400 mt-3 font-medium">Loading settings...</p>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else-if="!filteredSettings.length">
                    <tr>
                        <td colspan="4">
                            <div class="table-empty-state">
                                <svg class="table-empty-state-icon" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <p class="table-empty-state-title">No settings found</p>
                                <p class="table-empty-state-description">Configure system settings to see them here</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tbody v-else>
                    <tr v-for="(setting, index) of filteredSettings" :key="setting.id">
                        <td>
                            <span class="id-tag">#{{ setting.id }}</span>
                        </td>
                        <td class="max-w-[250px]">
                            <span class="font-mono text-sm text-slate-800 truncate block" :title="setting.key">{{
                                setting.key }}</span>
                        </td>
                        <td class="max-w-[300px]">
                            <span class="text-slate-600 truncate block" :title="setting.value">{{ setting.value
                                }}</span>
                        </td>
                        <td class="text-right">
                            <Menu as="div" class="relative inline-block text-left">
                                <MenuButton class="table-action-btn table-action-btn-view">
                                    <EllipsisVerticalIcon class="h-5 w-5" aria-hidden="true" />
                                </MenuButton>

                                <transition enter-active-class="transition duration-100 ease-out"
                                    enter-from-class="transform scale-95 opacity-0"
                                    enter-to-class="transform scale-100 opacity-100"
                                    leave-active-class="transition duration-75 ease-in"
                                    leave-from-class="transform scale-100 opacity-100"
                                    leave-to-class="transform scale-95 opacity-0">
                                    <MenuItems
                                        class="absolute z-20 right-0 mt-2 w-36 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black/5 focus:outline-none overflow-hidden">
                                        <div class="py-1">
                                            <MenuItem v-slot="{ active }">
                                            <RouterLink :to="{ name: 'app.settings.edit', params: { id: setting.id } }"
                                                :class="[
                                                    active ? 'bg-indigo-50 text-indigo-600' : 'text-slate-700',
                                                    'group flex w-full items-center px-4 py-2.5 text-sm gap-3 transition-colors',
                                                ]">
                                                <PencilIcon class="h-4 w-4"
                                                    :class="active ? 'text-indigo-500' : 'text-slate-400'"
                                                    aria-hidden="true" />
                                                Edit
                                            </RouterLink>
                                            </MenuItem>
                                            <MenuItem v-slot="{ active }">
                                            <button :class="[
                                                active ? 'bg-rose-50 text-rose-600' : 'text-slate-700',
                                                'group flex w-full items-center px-4 py-2.5 text-sm gap-3 transition-colors',
                                            ]" @click="deleteSetting(setting)">
                                                <TrashIcon class="h-4 w-4"
                                                    :class="active ? 'text-rose-500' : 'text-slate-400'"
                                                    aria-hidden="true" />
                                                Delete
                                            </button>
                                            </MenuItem>
                                        </div>
                                    </MenuItems>
                                </transition>
                            </Menu>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import Spinner from "@/components/Core/Spinner.vue";
import TableHeadingCell from "@/components/Core/Table/TableHeadingCell.vue";
import { ref, computed, onMounted } from 'vue';
import store from "@/store";
import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/vue";
import { EllipsisVerticalIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { RouterLink } from "vue-router";


const emit = defineEmits(['clickEdit'])

const search = ref('')

const settings = computed(() => {
    return store.state.settings
})
const settingloading = ref('true')

const filteredSettings = computed(() => {
    if (!settings.value.data) return []
    if (!search.value) return settings.value.data

    return settings.value.data.filter(setting =>
        setting.key.toLowerCase().includes(search.value.toLowerCase()) ||
        setting.value.toLowerCase().includes(search.value.toLowerCase())
    )
})

function filterSettings() {
    // Reactive filtering handled by computed property
}

function getSettings(url = null) {
    store.dispatch('settings/getSettings').then(() => {
        settingloading.value = false
    })
}

function deleteSetting(setting) {
    if (!confirm('Are You Sure you want to delete the setting ? ')) {
        return
    }
    store.dispatch('settings/deleteSetting', setting.id)
        .then(res => {
            store.commit('showToast', 'Setting Deleted Successfully')
            store.dispatch('settings/getSettings')
        })
}

onMounted(() => {

    getSettings()

})

</script>
