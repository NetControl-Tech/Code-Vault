<script setup>
import { watch, onUnmounted } from 'vue'

const props = defineProps({
    /** v-model:open — controls visibility */
    open: { type: Boolean, default: false },
    /** Max width utility for the panel (e.g. 'max-w-sm', 'max-w-md', 'max-w-lg') */
    maxWidth: { type: String, default: 'max-w-sm' },
    /** Tailwind class for the top accent bar (e.g. 'bg-sky-500', 'bg-red-500') */
    accent: { type: String, default: 'bg-sky-500' },
    /** When true, backdrop click and ESC are ignored (e.g. while submitting) */
    persistent: { type: Boolean, default: false }
})

const emit = defineEmits(['update:open', 'close'])

function close() {
    if (props.persistent) return
    emit('update:open', false)
    emit('close')
}

function onKeydown(e) {
    if (e.key === 'Escape' && props.open) close()
}

// Lock body scroll + bind ESC while open
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        document.body.style.overflow = 'hidden'
        document.addEventListener('keydown', onKeydown)
    } else {
        document.body.style.overflow = ''
        document.removeEventListener('keydown', onKeydown)
    }
})

onUnmounted(() => {
    document.body.style.overflow = ''
    document.removeEventListener('keydown', onKeydown)
})
</script>

<template>
    <Teleport to="body">
        <!-- Backdrop: fade -->
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-if="open"
                class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="close">
                <!-- Panel: fade + scale -->
                <Transition
                    appear
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 scale-95 translate-y-2"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-2">
                    <div v-if="open" :class="['card w-full overflow-hidden', maxWidth]" role="dialog" aria-modal="true">
                        <div class="h-1" :class="accent"></div>
                        <slot />
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
