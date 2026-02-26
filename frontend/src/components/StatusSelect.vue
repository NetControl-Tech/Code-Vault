<script setup>
import Select from 'primevue/select'
import { computed } from 'vue'

const props = defineProps({
    modelValue: {
        required: true
    }
})

const emit = defineEmits(['update:modelValue', 'change'])

const value = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val)
})
</script>

<template>
    <Select v-bind="$attrs" v-model="value" :class="{ 'border-green-500! ring-green-500!': value === 1 }"
        @change="emit('change', $event)" />
</template>
