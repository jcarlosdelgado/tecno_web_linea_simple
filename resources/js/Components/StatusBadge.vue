<template>
    <span :class="badgeClasses" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
        {{ statusText }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
        validator: (value) => ['SOLICITADO', 'PRESUPUESTADO', 'EN_PRODUCCION', 'FINALIZADO', 'CANCELADO', 'PENDIENTE', 'APROBADO', 'RECHAZADO', 'COMPLETADO'].includes(value)
    }
});

const statusConfig = {
    'SOLICITADO': {
        text: 'Solicitado',
        classes: 'bg-blue-100 text-blue-800'
    },
    'PRESUPUESTADO': {
        text: 'Presupuestado',
        classes: 'bg-yellow-100 text-yellow-800'
    },
    'EN_PRODUCCION': {
        text: 'En Producción',
        classes: 'bg-orange-100 text-orange-800'
    },
    'FINALIZADO': {
        text: 'Finalizado',
        classes: 'bg-green-100 text-green-800'
    },
    'CANCELADO': {
        text: 'Cancelado',
        classes: 'bg-red-100 text-red-800'
    },
    'PENDIENTE': {
        text: 'Pendiente',
        classes: 'bg-gray-100 text-gray-800'
    },
    'APROBADO': {
        text: 'Aprobado',
        classes: 'bg-green-100 text-green-800'
    },
    'RECHAZADO': {
        text: 'Rechazado',
        classes: 'bg-red-100 text-red-800'
    },
    'COMPLETADO': {
        text: 'Completado',
        classes: 'bg-green-100 text-green-800'
    }
};

const statusText = computed(() => statusConfig[props.status]?.text || props.status);
const badgeClasses = computed(() => statusConfig[props.status]?.classes || 'bg-gray-100 text-gray-800');
</script>
