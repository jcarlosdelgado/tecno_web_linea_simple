<template>
    <Head title="Historial de Trabajos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Historial de Trabajos
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <StatsCard 
                        title="Total de Trabajos" 
                        :value="stats.total_trabajos" 
                        icon="briefcase" 
                        color="blue" 
                    />
                    
                    <StatsCard 
                        title="Total Invertido" 
                        :value="`Bs. ${stats.total_gastado.toFixed(2)}`" 
                        icon="dollar-sign" 
                        color="green" 
                    />
                    
                    <StatsCard 
                        title="Promedio por Trabajo" 
                        :value="`Bs. ${stats.promedio_por_trabajo.toFixed(2)}`" 
                        icon="trending-up" 
                        color="purple" 
                    />
                    
                    <StatsCard 
                        title="Servicio Más Solicitado" 
                        :value="stats.servicio_mas_solicitado" 
                        icon="package" 
                        color="orange" 
                    />
                </div>

                <!-- Jobs Table -->
                <Card>
                    <template #header>
                        <h3 class="text-lg font-semibold text-gray-900">Todos los Trabajos</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            Historial completo de tus trabajos solicitados
                        </p>
                    </template>

                    <div v-if="trabajos.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay trabajos</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza solicitando tu primer trabajo.</p>
                        <div class="mt-6">
                            <Link :href="route('trabajos.create')">
                                <Button variant="primary">
                                    Solicitar Trabajo
                                </Button>
                            </Link>
                        </div>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Trabajo
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Servicio
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Monto
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="trabajo in trabajos" :key="trabajo.id_trabajo" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ trabajo.titulo }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ trabajo.servicio?.nombre || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <StatusBadge :status="trabajo.estado" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(trabajo.fecha_solicitud).toLocaleDateString('es-ES') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Bs. {{ calculateTotal(trabajo) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('trabajos.show', trabajo.id_trabajo)">
                                            <Button variant="ghost" size="sm">
                                                Ver Detalle
                                            </Button>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import StatsCard from '@/Components/StatsCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    trabajos: Array,
    stats: Object,
});

const calculateTotal = (trabajo) => {
    const total = trabajo.pagos
        ?.filter(p => p.estado === 'COMPLETADO')
        .reduce((sum, p) => sum + parseFloat(p.monto), 0) || 0;
    return total.toFixed(2);
};
</script>
