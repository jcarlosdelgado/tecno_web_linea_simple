<script setup>
import { ref, onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const toasts = ref([]);
let toastId = 0;

const addToast = (message, type = 'success') => {
    const id = toastId++;
    toasts.value.push({
        id,
        message,
        type,
        show: false,
    });

    // Trigger animation
    setTimeout(() => {
        const toast = toasts.value.find(t => t.id === id);
        if (toast) toast.show = true;
    }, 10);

    // Auto dismiss after 5 seconds
    setTimeout(() => {
        removeToast(id);
    }, 5000);
};

const removeToast = (id) => {
    const toast = toasts.value.find(t => t.id === id);
    if (toast) {
        toast.show = false;
        setTimeout(() => {
            toasts.value = toasts.value.filter(t => t.id !== id);
        }, 300);
    }
};

// Watch for flash messages from Laravel
watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        addToast(flash.success, 'success');
    }
    if (flash?.error) {
        addToast(flash.error, 'error');
    }
    if (flash?.warning) {
        addToast(flash.warning, 'warning');
    }
    if (flash?.info) {
        addToast(flash.info, 'info');
    }
}, { deep: true, immediate: true });

const getIcon = (type) => {
    switch (type) {
        case 'success':
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`;
        case 'error':
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`;
        case 'warning':
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>`;
        case 'info':
            return `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`;
        default:
            return '';
    }
};

const getColorClasses = (type) => {
    switch (type) {
        case 'success':
            return 'bg-green-50 text-green-800 border-green-200';
        case 'error':
            return 'bg-red-50 text-red-800 border-red-200';
        case 'warning':
            return 'bg-yellow-50 text-yellow-800 border-yellow-200';
        case 'info':
            return 'bg-blue-50 text-blue-800 border-blue-200';
        default:
            return 'bg-gray-50 text-gray-800 border-gray-200';
    }
};
</script>

<template>
    <div class="fixed top-4 right-4 z-50 space-y-2 max-w-sm">
        <TransitionGroup name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'flex items-center gap-3 p-4 rounded-lg border shadow-lg transition-all duration-300',
                    getColorClasses(toast.type),
                    toast.show ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-full'
                ]"
            >
                <div v-html="getIcon(toast.type)" class="flex-shrink-0"></div>
                <p class="flex-1 text-sm font-medium">{{ toast.message }}</p>
                <button
                    @click="removeToast(toast.id)"
                    class="flex-shrink-0 hover:opacity-70 transition-opacity"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
