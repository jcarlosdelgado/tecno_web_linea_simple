<template>
    <Head title="Dashboard - Trabajador" />

    <WorkerLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mis Trabajos Asignados
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <StatsCard 
                        title="Trabajos Activos" 
                        :value="stats.trabajos_activos" 
                        icon="clock" 
                        color="orange" 
                    />
                    
                    <StatsCard 
                        title="Completados Este Mes" 
                        :value="stats.trabajos_completados_mes" 
                        icon="check-circle" 
                        color="green" 
                    />
                    
                    <StatsCard 
                        title="Horas Trabajadas (Mes)" 
                        :value="stats.horas_trabajadas_mes.toFixed(1)" 
                        icon="trending-up" 
                        color="blue" 
                    />
                </div>

                <!-- Jobs Table -->
                <Card>
                    <template #header>
                        <h3 class="text-lg font-semibold text-gray-900">Trabajos Asignados</h3>
                    </template>

                    <div v-if="trabajos.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay trabajos asignados</h3>
                        <p class="mt-1 text-sm text-gray-500">Espera a que el propietario te asigne trabajos.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Trabajo
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Progreso
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
                                        {{ trabajo.cliente?.nombre || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <StatusBadge :status="trabajo.estado" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <ProgressBar 
                                            :percentage="getLatestProgress(trabajo)" 
                                            size="sm" 
                                            :show-label="false"
                                        />
                                        <span class="text-xs text-gray-500 mt-1">
                                            {{ getLatestProgress(trabajo) }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('trabajador.trabajos.show', trabajo.id_trabajo)">
                                            <Button variant="primary" size="sm">
                                                Actualizar Progreso
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
    </WorkerLayout>
</template>

<script setup>
import WorkerLayout from '@/Layouts/WorkerLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import StatsCard from '@/Components/StatsCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    trabajos: Array,
    stats: Object,
});

const getLatestProgress = (trabajo) => {
    if (!trabajo.seguimientos || trabajo.seguimientos.length === 0) {
        return 0;
    }
    const latest = trabajo.seguimientos[trabajo.seguimientos.length - 1];
    return latest.porcentaje_avance || 0;
};
</script>
