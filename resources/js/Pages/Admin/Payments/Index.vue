<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    pagos: Object,
    stats: Object,
});

const filtroTipo = ref('TODOS');
const busqueda = ref('');

const pagosFiltrados = computed(() => {
    let filtered = props.pagos.data;
    
    if (filtroTipo.value !== 'TODOS') {
        filtered = filtered.filter(p => p.tipo_pago === filtroTipo.value);
    }
    
    if (busqueda.value) {
        const query = busqueda.value.toLowerCase();
        filtered = filtered.filter(p => 
            p.trabajo.titulo.toLowerCase().includes(query) ||
            p.trabajo.cliente.nombre.toLowerCase().includes(query) ||
            p.trabajo.cliente.email.toLowerCase().includes(query)
        );
    }
    
    return filtered;
});

const getStatusVariant = (estado) => {
    const variants = {
        'PENDIENTE': 'warning',
        'COMPLETADO': 'success',
        'PARCIAL': 'info',
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

const verCliente = (clienteId) => {
    router.visit(route('admin.clientes.pagos', clienteId));
};

const verDetalle = (pago) => {
    if (pago.tipo_pago === 'CREDITO') {
        router.visit(route('admin.cuotas.index', pago.id_pago));
    } else {
        router.visit(route('admin.pagos.checkout', pago.id_pago));
    }
};

const descargarComprobante = (pago) => {
    window.open(route('admin.pagos.descargar-comprobante', pago.id_pago), '_blank');
};

const tieneComprobante = (pago) => {
    if (pago.tipo_pago === 'CONTADO') {
        return pago.pasarela?.estado === 'COMPLETADO';
    }
    return false;
};
</script>

<template>
    <Head title="Gestión de Pagos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Pagos
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
                            <p class="text-sm text-gray-500 mb-1">Cobrado</p>
                            <p class="text-3xl font-bold text-green-600">Bs {{ parseFloat(stats.monto_pagado).toFixed(2) }}</p>
                        </div>
                    </Card>

                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Por Cobrar</p>
                            <p class="text-3xl font-bold text-orange-600">Bs {{ parseFloat(stats.monto_pendiente).toFixed(2) }}</p>
                        </div>
                    </Card>
                </div>

                <!-- Filtros y búsqueda -->
                <Card>
                    <div class="flex flex-col md:flex-row gap-4">
                        <!-- Búsqueda -->
                        <div class="flex-1">
                            <input 
                                v-model="busqueda"
                                type="text" 
                                placeholder="Buscar por cliente, trabajo..."
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        
                        <!-- Filtros -->
                        <div class="flex gap-2">
                            <button 
                                @click="filtroTipo = 'TODOS'"
                                class="px-4 py-2 text-sm rounded-lg transition-colors"
                                :class="filtroTipo === 'TODOS' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            >
                                Todos
                            </button>
                            <button 
                                @click="filtroTipo = 'CONTADO'"
                                class="px-4 py-2 text-sm rounded-lg transition-colors"
                                :class="filtroTipo === 'CONTADO' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            >
                                Contado
                            </button>
                            <button 
                                @click="filtroTipo = 'CREDITO'"
                                class="px-4 py-2 text-sm rounded-lg transition-colors"
                                :class="filtroTipo === 'CREDITO' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                            >
                                Crédito
                            </button>
                        </div>
                    </div>
                </Card>

                <!-- Lista de Pagos -->
                <Card v-if="pagosFiltrados.length > 0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trabajo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="pago in pagosFiltrados" :key="pago.id_pago" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ pago.trabajo.cliente.nombre }}</div>
                                        <div class="text-sm text-gray-500">{{ pago.trabajo.cliente.email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ pago.trabajo.titulo }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Badge :variant="pago.tipo_pago === 'CONTADO' ? 'default' : 'info'">
                                            {{ pago.tipo_pago }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        Bs {{ parseFloat(pago.monto).toFixed(2) }}
                                        <div v-if="pago.tipo_pago === 'CREDITO'" class="text-xs text-gray-500">
                                            {{ pago.cuotas.filter(c => c.estado === 'PAGADA').length }}/{{ pago.numero_cuotas }} cuotas
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(pago.fecha) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Badge :variant="getStatusVariant(getEstadoPago(pago))">
                                            {{ getEstadoPago(pago) }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <Button 
                                                v-if="tieneComprobante(pago)"
                                                variant="success" 
                                                size="sm"
                                                @click="descargarComprobante(pago)"
                                                title="Descargar comprobante"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                            </Button>
                                            <Button 
                                                variant="secondary" 
                                                size="sm"
                                                @click="verCliente(pago.trabajo.id_cliente)"
                                            >
                                                Ver Cliente
                                            </Button>
                                            <Button 
                                                variant="primary" 
                                                size="sm"
                                                @click="verDetalle(pago)"
                                            >
                                                Detalle
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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
                            No se encontraron pagos con los filtros seleccionados.
                        </p>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
