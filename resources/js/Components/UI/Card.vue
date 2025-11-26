<script setup>
defineProps({
    title: String,
    subtitle: String,
    padding: {
        type: Boolean,
        default: true
    }
});
</script>

<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div v-if="title || subtitle || $slots.header" class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <slot name="header">
                <h3 v-if="title" class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                <p v-if="subtitle" class="mt-1 text-sm text-gray-600">{{ subtitle }}</p>
            </slot>
        </div>
        
        <div :class="{ 'p-6': padding }">
            <slot />
        </div>
        
        <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <slot name="footer" />
        </div>
    </div>
</template>
