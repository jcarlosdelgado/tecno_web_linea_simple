<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import StatsCard from '@/Components/StatsCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    trabajos: Array,
    stats: Object,
});

// Search and filter state
const searchQuery = ref('');
const selectedStatus = ref('');

// Computed filtered jobs
const filteredTrabajos = computed(() => {
    let filtered = props.trabajos;
    
    // Filter by search query
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(trabajo => 
            trabajo.titulo.toLowerCase().includes(query) ||
            trabajo.descripcion?.toLowerCase().includes(query)
        );
    }
    
    // Filter by status
    if (selectedStatus.value) {
        filtered = filtered.filter(trabajo => trabajo.estado === selectedStatus.value);
    }
    
    return filtered;
});

// Status options for filter
const statusOptions = [
    { value: '', label: 'Todos los estados' },
    { value: 'SOLICITADO', label: 'Solicitado' },
    { value: 'PRESUPUESTADO', label: 'Presupuestado' },
    { value: 'EN_PRODUCCION', label: 'En Producción' },
    { value: 'FINALIZADO', label: 'Finalizado' },
    { value: 'CANCELADO', label: 'Cancelado' }
];

const getStatusVariant = (estado) => {
    const variants = {
        'SOLICITADO': 'warning',
        'PRESUPUESTADO': 'info',
        'EN_PRODUCCION': 'default',
        'FINALIZADO': 'success',
        'CANCELADO': 'danger'
    };
    return variants[estado] || 'default';
};

// Clear filters
const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = '';
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mis Trabajos
                </h2>
                <Link :href="route('trabajos.create')">
                    <Button variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Solicitar Nuevo Trabajo
                    </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <StatsCard 
                        title="Total de Trabajos" 
                        :value="stats.total_trabajos" 
                        icon="briefcase" 
                        color="blue" 
                    />
                    
                    <StatsCard 
                        title="Trabajos Activos" 
                        :value="stats.trabajos_activos" 
                        icon="clock" 
                        color="orange" 
                    />
                    
                    <StatsCard 
                        title="Trabajos Completados" 
                        :value="stats.trabajos_completados" 
                        icon="check-circle" 
                        color="green" 
                    />
                    
                    <StatsCard 
                        title="Total Gastado" 
                        :value="`Bs. ${stats.total_gastado.toFixed(2)}`" 
                        icon="dollar-sign" 
                        color="purple" 
                    />
                </div>

                <!-- Jobs List -->
                <Card>
                    <template #header>
                        <div class="space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Mis Solicitudes</h3>
                                    <p class="mt-1 text-sm text-gray-600">
                                        {{ filteredTrabajos.length }} de {{ trabajos.length }} trabajos
                                    </p>
                                </div>
                                <Button 
                                    v-if="searchQuery || selectedStatus" 
                                    variant="ghost" 
                                    size="sm"
                                    @click="clearFilters"
                                >
                                    Limpiar filtros
                                </Button>
                            </div>
                            
                            <!-- Search and Filter -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Search Input -->
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Buscar por título o descripción..."
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                </div>
                                
                                <!-- Status Filter -->
                                <select
                                    v-model="selectedStatus"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                >
                                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </template>

                    <div v-if="filteredTrabajos.length === 0 && (searchQuery || selectedStatus)" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron resultados</h3>
                        <p class="mt-1 text-sm text-gray-500">Intenta con otros filtros o términos de búsqueda.</p>
                        <div class="mt-6">
                            <Button variant="ghost" @click="clearFilters">
                                Limpiar filtros
                            </Button>
                        </div>
                    </div>

                    <div v-else-if="trabajos.length === 0" class="text-center py-12">
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
                                        Estado
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="trabajo in filteredTrabajos" :key="trabajo.id_trabajo" class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ trabajo.titulo }}
                                        </div>
                                        <div class="text-sm text-gray-500 line-clamp-2">
                                            {{ trabajo.descripcion }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <StatusBadge :status="trabajo.estado" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(trabajo.fecha_solicitud).toLocaleDateString('es-ES', { 
                                            year: 'numeric', 
                                            month: 'short', 
                                            day: 'numeric' 
                                        }) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('trabajos.show', trabajo.id_trabajo)">
                                            <Button variant="ghost" size="sm">
                                                Ver Detalles
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

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
