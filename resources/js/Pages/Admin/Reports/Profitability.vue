<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    profitability: Array,
    startDate: String,
    endDate: String,
});

const sortColumn = ref('profit');
const sortDirection = ref('desc');

const sortedData = computed(() => {
    const data = [...props.profitability];
    return data.sort((a, b) => {
        const aVal = a[sortColumn.value];
        const bVal = b[sortColumn.value];
        const multiplier = sortDirection.value === 'asc' ? 1 : -1;
        return aVal > bVal ? multiplier : -multiplier;
    });
});

const sort = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'desc';
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2
    }).format(value);
};

const buildPdfUrl = () => {
    let url = route('admin.reportes.rentabilidad.pdf');
    const params = new URLSearchParams();
    
    if (props.startDate) params.append('start_date', props.startDate);
    if (props.endDate) params.append('end_date', props.endDate);
    
    const queryString = params.toString();
    return queryString ? `${url}?${queryString}` : url;
};

const getMarginColor = (margin) => {
    if (margin >= 30) return 'success';
    if (margin >= 15) return 'warning';
    return 'danger';
};

const totalRevenue = computed(() => props.profitability.reduce((sum, item) => sum + item.revenue, 0));
const totalExpenses = computed(() => props.profitability.reduce((sum, item) => sum + item.expenses, 0));
const totalProfit = computed(() => props.profitability.reduce((sum, item) => sum + item.profit, 0));
const averageMargin = computed(() => {
    const sum = props.profitability.reduce((sum, item) => sum + item.margin_percentage, 0);
    return props.profitability.length > 0 ? sum / props.profitability.length : 0;
});
</script>

<template>
    <Head title="Rentabilidad por Trabajo" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Análisis de Rentabilidad
                </h2>
                <div class="flex gap-3">
                    <a 
                        :href="buildPdfUrl()" 
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Exportar PDF
                    </a>
                    <a :href="route('admin.reportes.dashboard')" class="text-sm text-indigo-600 hover:text-indigo-800">
                        ← Volver al Dashboard
                    </a>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <Card>
                        <p class="text-sm font-medium text-gray-600">Ingresos Totales</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ formatCurrency(totalRevenue) }}</p>
                    </Card>
                    <Card>
                        <p class="text-sm font-medium text-gray-600">Gastos Totales</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ formatCurrency(totalExpenses) }}</p>
                    </Card>
                    <Card>
                        <p class="text-sm font-medium text-gray-600">Ganancia Total</p>
                        <p class="text-2xl font-bold mt-2" :class="totalProfit >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatCurrency(totalProfit) }}
                        </p>
                    </Card>
                    <Card>
                        <p class="text-sm font-medium text-gray-600">Margen Promedio</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ averageMargin.toFixed(1) }}%</p>
                    </Card>
                </div>

                <!-- Profitability Table -->
                <Card title="Rentabilidad por Trabajo">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th @click="sort('client')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Cliente
                                        <span v-if="sortColumn === 'client'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    </th>
                                    <th @click="sort('job_title')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Trabajo
                                        <span v-if="sortColumn === 'job_title'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    </th>
                                    <th @click="sort('revenue')" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Presupuesto
                                        <span v-if="sortColumn === 'revenue'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    </th>
                                    <th @click="sort('expenses')" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Gastos
                                        <span v-if="sortColumn === 'expenses'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    </th>
                                    <th @click="sort('profit')" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Ganancia
                                        <span v-if="sortColumn === 'profit'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    </th>
                                    <th @click="sort('margin_percentage')" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                        Margen
                                        <span v-if="sortColumn === 'margin_percentage'">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span>
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in sortedData" :key="item.job_id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ item.client }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ item.job_title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        {{ formatCurrency(item.revenue) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                        {{ formatCurrency(item.expenses) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right" :class="item.profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ formatCurrency(item.profit) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <Badge :variant="getMarginColor(item.margin_percentage)">
                                            {{ item.margin_percentage.toFixed(1) }}%
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <Badge :variant="item.status === 'FINALIZADO' ? 'success' : 'default'">
                                            {{ item.status.replace('_', ' ') }}
                                        </Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div v-if="profitability.length === 0" class="text-center py-12 text-gray-500">
                            No hay datos de rentabilidad para mostrar
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
