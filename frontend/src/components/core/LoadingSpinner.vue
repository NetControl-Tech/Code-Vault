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
    sm: 'h-6 w-6',
    md: 'h-10 w-10',
    lg: 'h-14 w-14',
    xl: 'h-20 w-20'
}
</script>

<template>
    <div :class="[
        'flex flex-col items-center justify-center gap-4',
        overlay ? 'fixed inset-0 bg-white/80 dark:bg-dark-900/80 backdrop-blur-sm z-50' : ''
    ]">
        <!-- Spinner -->
        <div class="relative">
            <!-- Outer ring with gradient -->
            <div :class="[
                'rounded-full border-4 border-slate-200 dark:border-dark-700',
                sizeClasses[size]
            ]"></div>

            <!-- Spinning gradient arc -->
            <div :class="[
                'absolute inset-0 rounded-full border-4 border-transparent animate-spin',
                sizeClasses[size]
            ]" style="border-top-color: #0ea5e9; border-right-color: #0ea5e9; animation-duration: 0.8s;"></div>

            <!-- Inner glow -->
            <div :class="[
                'absolute inset-2 rounded-full bg-gradient-to-br from-sky-400/20 to-purple-500/20 animate-pulse',
            ]"></div>
        </div>

        <!-- Loading text -->
        <p v-if="text" class="text-sm font-medium text-slate-600 dark:text-slate-400 animate-pulse">
            {{ text }}
        </p>
    </div>
</template>

<style scoped>
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
