<template>
  <SettingsLayout>
    <div class="mx-auto">
      <!-- Header Area -->
      <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
          <!-- Header handled by Layout -->
        </div>
        <div class="flex gap-3">
          <Button v-if="hasChanges" label="Discard Changes" icon="pi pi-refresh" severity="secondary" text
            @click="resetForm" :disabled="loading" />
          <Button label="Save All Changes" icon="pi pi-check" @click="saveSettings" :loading="loading"
            class="shadow-lg shadow-indigo-100" />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="fetching"
        class="flex flex-col items-center justify-center py-20 bg-white dark:bg-dark-800 rounded-2xl border border-slate-200 dark:border-dark-700 shadow-sm">
        <i class="pi pi-spin pi-spinner text-4xl text-indigo-500 mb-4"></i>
        <p class="text-slate-500 font-medium">Fetching settings...</p>
      </div>

      <!-- Settings Form -->
      <div v-else class="space-y-6">
        <div
          class="bg-white dark:bg-dark-800 rounded-2xl border border-slate-200 dark:border-dark-700 shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-100 dark:border-dark-700 bg-slate-50/50 dark:bg-dark-700/50">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-gray-200">General Settings</h2>
          </div>
          <div class="p-6 space-y-8">
            <div v-for="setting in editableSettings" :key="setting.key" class="group">
              <div class="flex flex-col gap-2">
                <label :for="setting.key"
                  class="text-sm font-semibold text-slate-700 dark:text-gray-300 flex items-center gap-2">
                  {{ formatLabel(setting.key) }}
                  <span v-if="setting.isChanged" class="w-1.5 h-1.5 rounded-full bg-indigo-500" title="Modified"></span>
                </label>

                <!-- Dynamic Input Choice -->
                <div class="flex flex-col gap-1">
                  <!-- Select for Mode-like keys -->
                  <Select v-if="isSelectType(setting.key)" v-model="setting.value" :options="getOptions(setting.key)"
                    optionLabel="label" optionValue="value" class="w-full" @change="markAsChanged(setting)" />

                  <!-- Toggle for Boolean-like keys -->
                  <div v-else-if="isToggleType(setting.key)" class="flex items-center gap-3 py-2">
                    <ToggleSwitch v-model="setting.value" @change="markAsChanged(setting)" />
                    <span class="text-sm text-slate-500">{{ setting.value ? 'Enabled' : 'Disabled' }}</span>
                  </div>

                  <!-- Number Input for thresholds/limits -->
                  <InputNumber v-else-if="isNumberType(setting.key)" v-model="setting.value" class="w-full"
                    @input="markAsChanged(setting)" />

                  <!-- Default Text Input -->
                  <InputText v-else :id="setting.key" v-model="setting.value" class="w-full"
                    placeholder="Enter value..." @input="markAsChanged(setting)" />
                </div>

                <p class="text-xs text-slate-400 font-mono">{{ setting.key }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Feedback -->
        <transition name="fade">
          <Message v-if="feedback.show" :severity="feedback.severity" class="mt-4" closable
            @close="feedback.show = false">
            {{ feedback.message }}
          </Message>
        </transition>

        <!-- Bottom Actions (Mobile) -->
        <div class="md:hidden flex justify-end pb-12">
          <Button label="Save All Changes" icon="pi pi-check" @click="saveSettings" :loading="loading"
            class="w-full shadow-lg" />
        </div>
      </div>
    </div>
  </SettingsLayout>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useSettingStore } from '../../stores/settingStore';
import { useToast } from 'primevue/usetoast';
import SettingsLayout from '../../components/settings/SettingsLayout.vue';
import Button from 'primevue/button';
import Message from 'primevue/message';
import Select from 'primevue/select';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import ToggleSwitch from 'primevue/toggleswitch';

const settingStore = useSettingStore();
const toast = useToast();

const fetching = ref(false);
const loading = ref(false);
const editableSettings = ref([]);
const originalValues = ref({});

const feedback = ref({
  show: false,
  message: '',
  severity: 'success'
});

const hasChanges = computed(() => {
  return editableSettings.value.some(s => s.isChanged);
});

onMounted(async () => {
  await loadSettings();
});

async function loadSettings() {
  fetching.value = true;
  try {
    const items = await settingStore.fetchSettings();

    // Transform settings into editable objects
    editableSettings.value = items.map(s => {
      // Handle boolean values if they are stored as strings "0"/"1" or "true"/"false"
      let val = s.value;
      if (isToggleType(s.key)) {
        val = val === '1' || val === 1 || val === 'true' || val === true;
      } else if (isNumberType(s.key)) {
        val = Number(val);
      }

      // Store original value using the transformed value
      originalValues.value[s.key] = val;

      return {
        key: s.key,
        value: val,
        isChanged: false
      };
    });
  } catch (err) {
    showFeedback('Failed to load settings', 'error');
  } finally {
    fetching.value = false;
  }
}

function formatLabel(key) {
  return key
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ');
}

function markAsChanged(setting) {
  setting.isChanged = true;
}

function isSelectType(key) {
  return key.toLowerCase().endsWith('_mode');
}

function isToggleType(key) {
  const k = key.toLowerCase();
  return k.includes('enabled') || k.includes('active') || k.includes('status');
}

function isNumberType(key) {
  const k = key.toLowerCase();
  return k.includes('threshold') || k.includes('quantity') || k.includes('limit') || k.includes('price') || k.includes('late_letters');
}

function getOptions(key) {
  if (key === 'PAYPAL_MODE') {
    return [
      { label: 'Sandbox', value: 'sandbox' },
      { label: 'Live', value: 'live' }
    ];
  }
  return [];
}

function resetForm() {
  editableSettings.value.forEach(s => {
    s.value = originalValues.value[s.key];
    s.isChanged = false;
  });
}

async function saveSettings() {
  loading.value = true;
  feedback.value.show = false;

  // Prepare payload
  const settingsPayload = {};
  editableSettings.value.forEach(s => {
    if (s.isChanged) {
      let val = s.value;
      if (isToggleType(s.key)) {
        val = val ? '1' : '0';
      }
      settingsPayload[s.key] = val;
    }
  });

  if (Object.keys(settingsPayload).length === 0) {
    loading.value = false;
    return;
  }

  try {
    await settingStore.bulkUpdateSettings(settingsPayload);
    showFeedback('Settings updated successfully!', 'success');

    // Update originals
    editableSettings.value.forEach(s => {
      if (s.isChanged) {
        originalValues.value[s.key] = s.value;
        s.isChanged = false;
      }
    });
  } catch (err) {
    const msg = err.response?.data?.message || 'Failed to update settings';
    showFeedback(msg, 'error');
  } finally {
    loading.value = false;
  }
}

function showFeedback(message, severity) {
  toast.add({
    severity: severity,
    summary: severity === 'success' ? 'Success' : 'Error',
    detail: message,
    life: 3000
  });

  feedback.value = {
    show: true,
    message,
    severity
  };
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

:deep(.p-select),
:deep(.p-inputtext) {
  border-radius: 10px;
}

.group:hover label {
  color: #6366f1;
}
</style>
