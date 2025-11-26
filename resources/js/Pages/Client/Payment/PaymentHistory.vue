<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    pagos: Array,
    stats: Object,
});

const filtroTipo = ref('TODOS');

const pagosFiltrados = computed(() => {
    if (filtroTipo.value === 'TODOS') return props.pagos;
    return props.pagos.filter(p => p.tipo_pago === filtroTipo.value);
});

const getStatusVariant = (estado) => {
    const variants = {
        'PENDIENTE': 'warning',
        'PAGADA': 'success',
        'COMPLETADO': 'success',
        'VENCIDA': 'danger',
    };
    return variants[estado] || 'default';
};

const getStatusText = (estado) => {
    const texts = {
        'PENDIENTE': 'Pendiente',
        'PAGADA': 'Pagada',
        'COMPLETADO': 'Completado',
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

const getEstadoPago = (pago) => {
    if (pago.tipo_pago === 'CONTADO') {
        return pago.pasarela?.estado || 'PENDIENTE';
    } else {
        const todasPagadas = pago.cuotas.every(c => c.estado === 'PAGADA');
        if (todasPagadas) return 'COMPLETADO';
        const algunaPagada = pago.cuotas.some(c => c.estado === 'PAGADA');
        if (algunaPagada) return 'PARCIAL';
        return 'PENDIENTE';
    }
};

const verDetalle = (pago) => {
    if (pago.tipo_pago === 'CREDITO') {
        router.visit(route('cuotas.index', pago.id_pago));
    } else {
        router.visit(route('pagos.checkout', pago.id_pago));
    }
};

const verTrabajo = (trabajoId) => {
    router.visit(route('trabajos.show', trabajoId));
};

const descargarComprobante = (pagoId) => {
    window.open(route('pagos.descargar-comprobante', pagoId), '_blank');
};

const puedeDescargarComprobante = (pago) => {
    if (pago.tipo_pago === 'CONTADO') {
        return pago.estado === 'PAGADO' || pago.pasarela?.estado === 'COMPLETADO';
    } else {
        // Para crédito, se puede descargar si al menos una cuota está pagada
        return pago.cuotas.some(c => c.estado === 'PAGADA');
    }
};
</script>

<template>
    <Head title="Historial de Pagos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Historial de Pagos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Total Pagos</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.total_pagos }}</p>
                            <div class="mt-2 flex justify-center items-center gap-3 text-xs">
                                <span class="text-blue-600">{{ stats.pagos_contado }} contado</span>
                                <span class="text-purple-600">{{ stats.pagos_credito }} crédito</span>
                            </div>
                        </div>
                    </Card>

                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Monto Total</p>
                            <p class="text-3xl font-bold text-gray-900">Bs {{ parseFloat(stats.monto_total).toFixed(2) }}</p>
                        </div>
                    </Card>

                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Pagado</p>
                            <p class="text-3xl font-bold text-green-600">Bs {{ parseFloat(stats.monto_pagado).toFixed(2) }}</p>
                        </div>
                    </Card>

                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Pendiente</p>
                            <p class="text-3xl font-bold text-orange-600">Bs {{ parseFloat(stats.monto_pendiente).toFixed(2) }}</p>
                        </div>
                    </Card>
                </div>

                <!-- Cuotas (si hay créditos) -->
                <Card v-if="stats.pagos_credito > 0">
                    <div class="grid grid-cols-3 gap-6 text-center">
                        <div>
                            <p class="text-sm text-gray-500">Total Cuotas</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total_cuotas }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Cuotas Pagadas</p>
                            <p class="text-2xl font-bold text-green-600">{{ stats.cuotas_pagadas }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Cuotas Pendientes</p>
                            <p class="text-2xl font-bold text-orange-600">{{ stats.cuotas_pendientes }}</p>
                        </div>
                    </div>
                </Card>

                <!-- Filtros -->
                <Card>
                    <div class="flex items-center gap-4">
                        <span class="text-sm font-medium text-gray-700">Filtrar por:</span>
                        <div class="flex gap-2">
                            <button 
                                @click="filtroTipo = 'TODOS'"
                                class="px-4 py-2 text-sm rounded-lg transition-colors"
                                :class="filtroTipo === 'TODOS' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            >
                                Todos ({{ stats.total_pagos }})
                            </button>
                            <button 
                                @click="filtroTipo = 'CONTADO'"
                                class="px-4 py-2 text-sm rounded-lg transition-colors"
                                :class="filtroTipo === 'CONTADO' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            >
                                Contado ({{ stats.pagos_contado }})
                            </button>
                            <button 
                                @click="filtroTipo = 'CREDITO'"
                                class="px-4 py-2 text-sm rounded-lg transition-colors"
                                :class="filtroTipo === 'CREDITO' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            >
                                Crédito ({{ stats.pagos_credito }})
                            </button>
                        </div>
                    </div>
                </Card>

                <!-- Lista de Pagos -->
                <Card v-if="pagosFiltrados.length > 0">
                    <div class="space-y-4">
                        <div 
                            v-for="pago in pagosFiltrados" 
                            :key="pago.id_pago"
                            class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow bg-white"
                        >
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <!-- Info del Pago -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ pago.trabajo.titulo }}
                                        </h3>
                                        <Badge :variant="pago.tipo_pago === 'CONTADO' ? 'default' : 'info'">
                                            {{ pago.tipo_pago }}
                                        </Badge>
                                        <Badge :variant="getStatusVariant(getEstadoPago(pago))">
                                            {{ getStatusText(getEstadoPago(pago)) }}
                                        </Badge>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm mt-3">
                                        <div>
                                            <span class="text-gray-500">Monto Total:</span>
                                            <span class="ml-2 font-semibold text-gray-900">Bs {{ parseFloat(pago.monto).toFixed(2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Fecha:</span>
                                            <span class="ml-2 font-medium text-gray-900">{{ formatDate(pago.fecha) }}</span>
                                        </div>
                                        <div v-if="pago.tipo_pago === 'CREDITO'">
                                            <span class="text-gray-500">Cuotas:</span>
                                            <span class="ml-2 font-medium text-gray-900">
                                                {{ pago.cuotas.filter(c => c.estado === 'PAGADA').length }} / {{ pago.numero_cuotas }} pagadas
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Cuotas Preview (solo para crédito) -->
                                    <div v-if="pago.tipo_pago === 'CREDITO'" class="mt-4 flex gap-2 flex-wrap">
                                        <div 
                                            v-for="cuota in pago.cuotas.slice(0, 4)" 
                                            :key="cuota.id_cuota"
                                            class="px-2 py-1 rounded text-xs font-medium"
                                            :class="{
                                                'bg-green-100 text-green-700': cuota.estado === 'PAGADA',
                                                'bg-orange-100 text-orange-700': cuota.estado === 'PENDIENTE',
                                                'bg-red-100 text-red-700': cuota.estado === 'VENCIDA'
                                            }"
                                        >
                                            C{{ cuota.numero_cuota }}: Bs {{ parseFloat(cuota.monto).toFixed(2) }}
                                        </div>
                                        <span v-if="pago.cuotas.length > 4" class="px-2 py-1 text-xs text-gray-500">
                                            +{{ pago.cuotas.length - 4 }} más
                                        </span>
                                    </div>
                                </div>

                                <!-- Acciones -->
                                <div class="flex flex-col gap-2">
                                    <Button 
                                        variant="primary" 
                                        size="sm"
                                        @click="verDetalle(pago)"
                                    >
                                        {{ pago.tipo_pago === 'CREDITO' ? 'Ver Plan de Cuotas' : 'Ver Pago' }}
                                    </Button>
                                    <Button 
                                        v-if="puedeDescargarComprobante(pago)"
                                        variant="success" 
                                        size="sm"
                                        @click="descargarComprobante(pago.id_pago)"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Descargar Comprobante
                                    </Button>
                                    <Button 
                                        variant="secondary" 
                                        size="sm"
                                        @click="verTrabajo(pago.id_trabajo)"
                                    >
                                        Ver Trabajo
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Estado vacío -->
                <Card v-else>
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay pagos</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Aún no has realizado pagos{{ filtroTipo !== 'TODOS' ? ' de tipo ' + filtroTipo : '' }}.
                        </p>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
