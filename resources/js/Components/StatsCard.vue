<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600">{{ title }}</p>
                <p class="mt-2 text-3xl font-semibold" :class="valueColorClass">
                    {{ value }}
                </p>
                <div v-if="trend" class="mt-2 flex items-center text-sm">
                    <span :class="trend.isPositive ? 'text-green-600' : 'text-red-600'" class="flex items-center">
                        <svg v-if="trend.isPositive" class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <svg v-else class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ trend.value }}
                    </span>
                </div>
            </div>
            <div :class="iconBgClass" class="p-3 rounded-full">
                <component :is="iconComponent" :class="iconColorClass" class="w-8 h-8" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { 
    Briefcase, 
    Clock, 
    DollarSign, 
    CheckCircle, 
    Users,
    Package,
    TrendingUp,
    AlertCircle
} from 'lucide-vue-next';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    icon: {
        type: String,
        default: 'briefcase',
        validator: (value) => ['briefcase', 'clock', 'dollar-sign', 'check-circle', 'users', 'package', 'trending-up', 'alert-circle'].includes(value)
    },
    color: {
        type: String,
        default: 'blue',
        validator: (value) => ['blue', 'green', 'red', 'yellow', 'purple', 'orange'].includes(value)
    },
    trend: {
        type: Object,
        default: null,
        // { value: '+12%', isPositive: true }
    }
});

const iconComponent = computed(() => {
    const icons = {
        'briefcase': Briefcase,
        'clock': Clock,
        'dollar-sign': DollarSign,
        'check-circle': CheckCircle,
        'users': Users,
        'package': Package,
        'trending-up': TrendingUp,
        'alert-circle': AlertCircle
    };
    return icons[props.icon] || Briefcase;
});

const colorClasses = {
    blue: {
        icon: 'text-blue-600',
        bg: 'bg-blue-100',
        value: 'text-gray-900'
    },
    green: {
        icon: 'text-green-600',
        bg: 'bg-green-100',
        value: 'text-gray-900'
    },
    red: {
        icon: 'text-red-600',
        bg: 'bg-red-100',
        value: 'text-gray-900'
    },
    yellow: {
        icon: 'text-yellow-600',
        bg: 'bg-yellow-100',
        value: 'text-gray-900'
    },
    purple: {
        icon: 'text-purple-600',
        bg: 'bg-purple-100',
        value: 'text-gray-900'
    },
    orange: {
        icon: 'text-orange-600',
        bg: 'bg-orange-100',
        value: 'text-gray-900'
    }
};

const iconColorClass = computed(() => colorClasses[props.color]?.icon || colorClasses.blue.icon);
const iconBgClass = computed(() => colorClasses[props.color]?.bg || colorClasses.blue.bg);
const valueColorClass = computed(() => colorClasses[props.color]?.value || colorClasses.blue.value);
</script>
