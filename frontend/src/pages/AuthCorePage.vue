<template>
    <main
        class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 antialiased relative flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8"
        dir="rtl">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-30 pointer-events-none">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(120,119,198,0.1),transparent_50%)]">
            </div>
        </div>

        <div class="max-w-md w-full relative">
            <div
                class="group relative overflow-hidden rounded-3xl bg-white/5 backdrop-blur-xl border border-white/10 shadow-2xl shadow-black/40 p-8">
                <!-- Top Gradient Accent -->
                <div
                    class="absolute top-0 right-0 w-full h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 transition-all duration-500 group-hover:h-1.5">
                </div>

                <div class="mb-10 text-center">
                    <h1
                        class="text-3xl font-bold bg-gradient-to-r from-white via-slate-200 to-slate-400 bg-clip-text text-transparent">
                        <slot name="title">{{ title }}</slot>
                    </h1>
                    <p class="mt-2 text-slate-400">
                        <slot name="subtitle">{{ subtitle }}</slot>
                    </p>
                </div>

                <slot></slot>

                <!-- Optional: Footer links -->
                <div class="mt-8 pt-6 border-t border-white/5 text-center">
                    <p class="text-sm text-slate-500">
                        <slot name="footer">{{ footerText }}</slot>
                    </p>
                </div>
            </div>

            <!-- Security Badge -->
            <div class="mt-8 flex items-center justify-center gap-2 text-slate-500 text-xs tracking-wider uppercase">
                <LockClosedIcon class="w-3.5 h-3.5" />
                <span>
                    <slot name="badge">محمي بتشفير بيانات متقدم</slot>
                </span>
            </div>
        </div>
    </main>
</template>

<script setup>
import { LockClosedIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    title: {
        type: String,
        default: ''
    },
    subtitle: {
        type: String,
        default: ''
    },
    footerText: {
        type: String,
        default: 'وصول آمن ومضمون للنظام'
    }
})
</script>

<style>
.animate-shake {
    animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@keyframes shake {

    10%,
    90% {
        transform: translate3d(-1px, 0, 0);
    }

    20%,
    80% {
        transform: translate3d(2px, 0, 0);
    }

    30%,
    50%,
    70% {
        transform: translate3d(-4px, 0, 0);
    }

    40%,
    60% {
        transform: translate3d(4px, 0, 0);
    }
}
</style>
