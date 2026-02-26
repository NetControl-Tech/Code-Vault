<script setup>
defineProps({
    size: {
        type: String,
        default: 'md', // sm, md, lg, xl
        validator: (v) => ['sm', 'md', 'lg', 'xl'].includes(v)
    },
    text: {
        type: String,
        default: ''
    },
    overlay: {
        type: Boolean,
        default: false
    }
})

const sizeClasses = {
    sm: 'h-6 w-6 border-2',
    md: 'h-10 w-10 border-4',
    lg: 'h-14 w-14 border-4',
    xl: 'h-20 w-20 border-[5px]'
}
</script>

<template>
    <div :class="[
        'flex flex-col items-center justify-center transition-all duration-300',
        overlay ? 'fixed inset-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm z-50' : ''
    ]">
        <div class="relative flex flex-col items-center">
            <!-- Spinner -->
            <div :class="[
                'rounded-full border-slate-200 dark:border-slate-700 animate-spin',
                sizeClasses[size]
            ]" style="border-top-color: var(--primary-500, #3b82f6); border-right-color: var(--primary-500, #3b82f6);">
            </div>

            <!-- Pulse Effect (Optional) -->
            <div v-if="size !== 'sm'"
                class="absolute inset-0 rounded-full animate-pulse bg-primary-500/10 dark:bg-primary-400/10 blur-xl">
            </div>
        </div>

        <!-- Loading text -->
        <p v-if="text" :class="[
            'font-medium text-slate-600 dark:text-slate-400 animate-pulse mt-4',
            size === 'sm' ? 'text-xs' : 'text-sm'
        ]">
            {{ text }}
        </p>
    </div>
</template>

<style scoped>
/* Ensure primary color variable fallback */
:root {
    --primary-500: #3b82f6;
}
</style>
