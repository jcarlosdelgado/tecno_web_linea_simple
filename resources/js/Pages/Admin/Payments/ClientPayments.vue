<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    cliente: Object,
    pagos: Array,
    stats: Object,
});

const filtroTipo = ref('TODOS');

const pagosFiltrados = computed(() => {
    if (filtroTipo.value === 'TODOS') {
        return props.pagos;
    }
    return props.pagos.filter(p => p.tipo_pago === filtroTipo.value);
});

const getStatusVariant = (estado) => {
    const variants = {
        'PENDIENTE': 'warning',
        'COMPLETADO': 'success',
        'PARCIAL': 'info',
        'PAGADA': 'success',
    };
    return variants[estado] || 'default';
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
        router.visit(route('admin.cuotas.index', pago.id_pago));
    } else {
        router.visit(route('admin.pagos.checkout', pago.id_pago));
    }
};

const verTrabajo = (trabajoId) => {
    router.visit(route('admin.trabajos.show', trabajoId));
};

const puedeDescargarComprobante = (pago) => {
    if (pago.tipo_pago === 'CONTADO') {
        return pago.pasarela?.estado === 'COMPLETADO';
    } else {
        // Para crédito, se puede descargar si hay al menos una cuota pagada
        return pago.cuotas.some(c => c.estado === 'PAGADA');
    }
};

const descargarComprobante = (pago) => {
    window.open(route('admin.pagos.descargar-comprobante', pago.id_pago), '_blank');
};

const descargarComprobanteCuota = (cuotaId) => {
    window.open(route('admin.cuotas.descargar-comprobante', cuotaId), '_blank');
};

const volverLista = () => {
    router.visit(route('admin.pagos.index'));
};
</script>

<template>
    <Head :title="`Pagos de ${cliente.nombre}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Pagos de {{ cliente.nombre }}
                </h2>
                <Button variant="secondary" @click="volverLista">
                    Volver a Lista
                </Button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Información del Cliente -->
                <Card>
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-2xl font-semibold text-indigo-600">
                                    {{ cliente.nombre.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900">{{ cliente.nombre }}</h3>
                            <p class="text-sm text-gray-500">{{ cliente.email }}</p>
                            <p v-if="cliente.telefono" class="text-sm text-gray-500">{{ cliente.telefono }}</p>
                        </div>
                    </div>
                </Card>

                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Total Pagos</p>
                            <p class="text-3xl font-bold text-gray-900">{{ stats.total_pagos }}</p>
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

                <!-- Filtros -->
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

                <!-- Lista de Pagos -->
                <div class="space-y-4">
                    <Card v-for="pago in pagosFiltrados" :key="pago.id_pago">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <!-- Información del Pago -->
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ pago.trabajo.titulo }}</h3>
                                    <Badge :variant="pago.tipo_pago === 'CONTADO' ? 'default' : 'info'">
                                        {{ pago.tipo_pago }}
                                    </Badge>
                                    <Badge :variant="getStatusVariant(getEstadoPago(pago))">
                                        {{ getEstadoPago(pago) }}
                                    </Badge>
                                </div>

                                <div class="space-y-2 text-sm text-gray-600">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-medium">Bs {{ parseFloat(pago.monto).toFixed(2) }}</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ formatDate(pago.fecha) }}</span>
                                    </div>
                                </div>

                                <!-- Cuotas Preview -->
                                <div v-if="pago.tipo_pago === 'CREDITO'" class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-sm font-medium text-gray-700 mb-2">
                                        Cuotas: {{ pago.cuotas.filter(c => c.estado === 'PAGADA').length }}/{{ pago.numero_cuotas }} pagadas
                                    </p>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                        <div 
                                            v-for="cuota in pago.cuotas.slice(0, 4)" 
                                            :key="cuota.id_cuota"
                                            class="p-2 rounded-lg border text-xs relative"
                                            :class="cuota.estado === 'PAGADA' ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200'"
                                        >
                                            <div class="font-medium">Cuota {{ cuota.numero_cuota }}</div>
                                            <div class="text-gray-600">Bs {{ parseFloat(cuota.monto).toFixed(2) }}</div>
                                            <Badge :variant="getStatusVariant(cuota.estado)" class="mt-1">
                                                {{ cuota.estado }}
                                            </Badge>
                                            <button
                                                v-if="cuota.estado === 'PAGADA'"
                                                @click.stop="descargarComprobanteCuota(cuota.id_cuota)"
                                                class="mt-1 w-full text-xs bg-green-600 hover:bg-green-700 text-white py-1 px-2 rounded transition-colors"
                                                title="Descargar comprobante"
                                            >
                                                PDF
                                            </button>
                                        </div>
                                    </div>
                                    <p v-if="pago.cuotas.length > 4" class="text-xs text-gray-500 mt-2">
                                        +{{ pago.cuotas.length - 4 }} cuotas más
                                    </p>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="flex flex-col gap-2 min-w-[200px]">
                                <Button 
                                    v-if="pago.tipo_pago === 'CREDITO'"
                                    variant="primary" 
                                    size="sm"
                                    @click="verDetalle(pago)"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Ver Plan de Cuotas
                                </Button>

                                <Button 
                                    v-if="puedeDescargarComprobante(pago) && pago.tipo_pago === 'CONTADO'"
                                    variant="success" 
                                    size="sm"
                                    @click="descargarComprobante(pago)"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Descargar Comprobante
                                </Button>

                                <Button 
                                    variant="secondary" 
                                    size="sm"
                                    @click="verTrabajo(pago.id_trabajo)"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Ver Trabajo
                                </Button>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Estado vacío -->
                <Card v-if="pagosFiltrados.length === 0">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay pagos</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Este cliente no tiene pagos registrados.
                        </p>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
