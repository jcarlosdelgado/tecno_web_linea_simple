<script setup>
import { computed } from 'vue';

const props = defineProps({
    percentage: {
        type: Number,
        required: true,
        validator: (value) => value >= 0 && value <= 100
    },
    showLabel: {
        type: Boolean,
        default: true
    },
    height: {
        type: String,
        default: 'h-4'
    },
    animated: {
        type: Boolean,
        default: true
    }
});

const colorClass = computed(() => {
    if (props.percentage < 30) return 'bg-red-500';
    if (props.percentage < 70) return 'bg-yellow-500';
    if (props.percentage < 100) return 'bg-blue-500';
    return 'bg-green-500';
});
</script>

<template>
    <div class="w-full">
        <div v-if="showLabel" class="flex justify-between mb-1">
            <span class="text-sm font-medium text-gray-700">Progreso</span>
            <span class="text-sm font-medium text-gray-700">{{ percentage }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full overflow-hidden" :class="height">
            <div
                class="transition-all duration-500 ease-out rounded-full flex items-center justify-center text-xs font-medium text-white"
                :class="[colorClass, { 'progress-bar-striped': animated }]"
                :style="{ width: `${percentage}%` }"
            >
                <span v-if="percentage > 10 && height !== 'h-2'">{{ percentage }}%</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.progress-bar-striped {
    background-image: linear-gradient(
        45deg,
        rgba(255, 255, 255, 0.15) 25%,
        transparent 25%,
        transparent 50%,
        rgba(255, 255, 255, 0.15) 50%,
        rgba(255, 255, 255, 0.15) 75%,
        transparent 75%,
        transparent
    );
    background-size: 1rem 1rem;
    animation: progress-bar-stripes 1s linear infinite;
}

@keyframes progress-bar-stripes {
    0% {
        background-position: 1rem 0;
    }
    100% {
        background-position: 0 0;
    }
}
</style>
