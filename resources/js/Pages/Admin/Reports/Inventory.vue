<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
);

const props = defineProps({
    consumption: Object,
    startDate: String,
    endDate: String,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2
    }).format(value);
};

// Top 10 materials chart
const topMaterialsData = computed(() => {
    const top10 = [...props.consumption.data].slice(0, 10);
    return {
        labels: top10.map(item => item.name),
        datasets: [{
            label: 'Cantidad Consumida',
            data: top10.map(item => item.total_consumed),
            backgroundColor: '#4F46E5',
        }]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            callbacks: {
                label: (context) => `${context.parsed.x} unidades`
            }
        }
    },
    scales: {
        x: {
            beginAtZero: true
        }
    }
};

const changePage = (url) => {
    if (url) {
        router.visit(url, {
            preserveScroll: true,
            preserveState: true,
        });
    }
};
</script>

<template>
    <Head title="Consumo de Inventario" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Consumo de Inventario
                </h2>
                <a :href="route('admin.reportes.dashboard')" class="text-sm text-indigo-600 hover:text-indigo-800">
                    ← Volver al Dashboard
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card>
                        <p class="text-sm font-medium text-gray-600">Materiales Diferentes</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ consumption.total }}</p>
                    </Card>
                    <Card>
                        <p class="text-sm font-medium text-gray-600">Cantidad Total Consumida</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ (consumption.globalTotalQuantity || 0).toFixed(2) }}</p>
                    </Card>
                    <Card>
                        <p class="text-sm font-medium text-gray-600">Valor Total</p>
                        <p class="text-3xl font-bold text-indigo-600 mt-2">{{ formatCurrency(consumption.globalTotalValue || 0) }}</p>
                    </Card>
                </div>

                <!-- Chart -->
                <Card v-if="consumption.total > 0" title="Top 10 Materiales Más Consumidos">
                    <div class="h-96">
                        <Bar :data="topMaterialsData" :options="chartOptions" />
                    </div>
                </Card>

                <!-- Consumption Table -->
                <Card title="Detalle de Consumo">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Material
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Unidad
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Precio Unitario
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cantidad Consumida
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Valor Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in consumption.data" :key="item.material_id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ item.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-center">
                                        {{ item.unit }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        {{ formatCurrency(item.unit_price) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                        {{ item.total_consumed.toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 text-right font-semibold">
                                        {{ formatCurrency(item.total_value) }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-sm font-bold text-gray-900 text-right">
                                        TOTAL:
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                        {{ (consumption.globalTotalQuantity || 0).toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600 text-right">
                                        {{ formatCurrency(consumption.globalTotalValue || 0) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                        <div v-if="consumption.total === 0" class="text-center py-12 text-gray-500">
                            No hay datos de consumo para el período seleccionado
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6" v-if="consumption.last_page > 1">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <button
                                    @click="changePage(consumption.prev_page_url)"
                                    :disabled="!consumption.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Anterior
                                </button>
                                <button
                                    @click="changePage(consumption.next_page_url)"
                                    :disabled="!consumption.next_page_url"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Siguiente
                                </button>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Mostrando
                                        <span class="font-medium">{{ consumption.from }}</span>
                                        a
                                        <span class="font-medium">{{ consumption.to }}</span>
                                        de
                                        <span class="font-medium">{{ consumption.total }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <button
                                            @click="changePage(consumption.prev_page_url)"
                                            :disabled="!consumption.prev_page_url"
                                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span class="sr-only">Anterior</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        
                                        <template v-for="link in consumption.links" :key="link.label">
                                            <button
                                                v-if="!link.label.includes('Previous') && !link.label.includes('Next')"
                                                @click="changePage(link.url)"
                                                :disabled="link.active || !link.url"
                                                :class="[
                                                    link.active
                                                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                                ]"
                                            >
                                                {{ link.label }}
                                            </button>
                                        </template>

                                        <button
                                            @click="changePage(consumption.next_page_url)"
                                            :disabled="!consumption.next_page_url"
                                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span class="sr-only">Siguiente</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
