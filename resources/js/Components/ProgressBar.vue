<template>
    <div class="w-full">
        <div class="flex items-center justify-between mb-1" v-if="showLabel">
            <span class="text-sm font-medium text-gray-700">Progreso</span>
            <span class="text-sm font-medium text-gray-700">{{ percentage }}%</span>
        </div>
        <div :class="containerClasses" class="bg-gray-200 rounded-full overflow-hidden">
            <div 
                :class="barClasses" 
                :style="{ width: percentage + '%' }"
                class="h-full transition-all duration-500 ease-out rounded-full flex items-center justify-end pr-2"
            >
                <span v-if="size === 'lg' && percentage > 10" class="text-xs font-medium text-white">
                    {{ percentage }}%
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    percentage: {
        type: Number,
        required: true,
        validator: (value) => value >= 0 && value <= 100
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    },
    showLabel: {
        type: Boolean,
        default: true
    },
    color: {
        type: String,
        default: 'blue',
        validator: (value) => ['blue', 'green', 'orange', 'red'].includes(value)
    }
});

const sizeClasses = {
    sm: 'h-2',
    md: 'h-4',
    lg: 'h-6'
};

const colorClasses = {
    blue: 'bg-blue-600',
    green: 'bg-green-600',
    orange: 'bg-orange-600',
    red: 'bg-red-600'
};

const containerClasses = computed(() => sizeClasses[props.size]);
const barClasses = computed(() => {
    // Change color based on percentage
    if (props.percentage === 100) {
        return colorClasses.green;
    } else if (props.percentage >= 75) {
        return colorClasses.blue;
    } else if (props.percentage >= 25) {
        return colorClasses.orange;
    } else {
        return colorClasses.orange;
    }
});
</script>
