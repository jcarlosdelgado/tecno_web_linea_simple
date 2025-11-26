<script setup>
import { computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    pagos: Array,
    stats: Object,
});

const getStatusVariant = (estado) => {
    const variants = {
        'PENDIENTE': 'warning',
        'PAGADA': 'success',
        'VENCIDA': 'danger',
    };
    return variants[estado] || 'default';
};

const getStatusText = (estado) => {
    const texts = {
        'PENDIENTE': 'Pendiente',
        'PAGADA': 'Pagada',
        'VENCIDA': 'Vencida',
    };
    return texts[estado] || estado;
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-BO', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};

const verPlanCuotas = (pagoId) => {
    router.visit(route('cuotas.index', pagoId));
};
</script>

<template>
    <Head title="Mis Cuotas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mis Cuotas
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Total de Cuotas</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.total_cuotas }}</p>
                            <div class="mt-2 flex justify-center items-center gap-4 text-sm">
                                <span class="text-green-600 font-medium">{{ stats.cuotas_pagadas }} pagadas</span>
                                <span class="text-orange-600 font-medium">{{ stats.cuotas_pendientes }} pendientes</span>
                            </div>
                        </div>
                    </Card>

                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Monto Total</p>
                            <p class="text-3xl font-bold text-gray-900">Bs {{ parseFloat(stats.monto_total).toFixed(2) }}</p>
                            <p class="mt-2 text-sm text-gray-600">
                                Pagado: <span class="font-semibold text-green-600">Bs {{ parseFloat(stats.monto_pagado).toFixed(2) }}</span>
                            </p>
                        </div>
                    </Card>

                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Progreso General</p>
                            <p class="text-3xl font-bold text-indigo-600">{{ stats.progreso.toFixed(0) }}%</p>
                            <div class="mt-3 w-full bg-gray-200 rounded-full h-2.5">
                                <div 
                                    class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-2.5 rounded-full transition-all duration-500"
                                    :style="{ width: stats.progreso + '%' }"
                                ></div>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Lista de Pagos con Cuotas -->
                <Card v-if="pagos.length > 0">
                    <div class="space-y-6">
                        <div 
                            v-for="pago in pagos" 
                            :key="pago.id_pago"
                            class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow bg-white"
                        >
                            <!-- Cabecera del Pago -->
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ pago.trabajo.titulo }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        {{ pago.numero_cuotas }} cuotas de Bs {{ parseFloat(pago.monto / pago.numero_cuotas).toFixed(2) }}
                                    </p>
                                </div>
                                <Button 
                                    variant="secondary" 
                                    size="sm"
                                    @click="verPlanCuotas(pago.id_pago)"
                                >
                                    Ver Detalle
                                </Button>
                            </div>

                            <!-- Cuotas del Pago -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div 
                                    v-for="cuota in pago.cuotas" 
                                    :key="cuota.id_cuota"
                                    class="border rounded-lg p-3"
                                    :class="{
                                        'bg-green-50 border-green-200': cuota.estado === 'PAGADA',
                                        'bg-orange-50 border-orange-200': cuota.estado === 'PENDIENTE',
                                        'bg-red-50 border-red-200': cuota.estado === 'VENCIDA'
                                    }"
                                >
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">
                                            Cuota {{ cuota.numero_cuota }}
                                        </span>
                                        <Badge :variant="getStatusVariant(cuota.estado)" size="sm">
                                            {{ getStatusText(cuota.estado) }}
                                        </Badge>
                                    </div>
                                    <p class="text-lg font-bold text-gray-900">
                                        Bs {{ parseFloat(cuota.monto).toFixed(2) }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Vence: {{ formatDate(cuota.fecha_vencimiento) }}
                                    </p>
                                    <p v-if="cuota.fecha_pago" class="text-xs text-green-600 mt-1">
                                        Pagado: {{ formatDate(cuota.fecha_pago) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Estado vacío -->
                <Card v-else>
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No tienes cuotas</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Aún no has realizado pagos a crédito.
                        </p>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
