<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import { Line, Doughnut, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
);

const props = defineProps({
    kpis: Object,
    salesTrend: Object,
    topServices: Array,
    currentMonth: Number,
    currentYear: Number,
});

// Sales trend chart data
const salesChartData = computed(() => ({
    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
    datasets: [{
        label: 'Ventas (Bs)',
        data: props.salesTrend.sales,
        borderColor: '#4F46E5',
        backgroundColor: 'rgba(79, 70, 229, 0.1)',
        tension: 0.4,
        fill: true,
    }]
}));

const salesChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            callbacks: {
                label: (context) => `Bs ${context.parsed.y.toFixed(2)}`
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value) => `Bs ${value}`
            }
        }
    }
};

// Jobs by status chart
const jobsStatusData = computed(() => {
    const statusMap = props.kpis.jobs_by_status || {};
    return {
        labels: Object.keys(statusMap).map(status => status.replace('_', ' ')),
        datasets: [{
            data: Object.values(statusMap),
            backgroundColor: [
                '#FCD34D', // SOLICITADO - Yellow
                '#60A5FA', // PRESUPUESTADO - Blue
                '#4F46E5', // EN_PRODUCCION - Indigo
                '#10B981', // FINALIZADO - Green
                '#EF4444', // CANCELADO - Red
            ],
        }]
    };
});

const jobsStatusOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom'
        }
    }
};

// Top services chart
const topServicesData = computed(() => ({
    labels: props.topServices.map(s => s.service),
    datasets: [{
        label: 'Solicitudes',
        data: props.topServices.map(s => s.count),
        backgroundColor: '#4F46E5',
    }]
}));

const topServicesOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        x: {
            beginAtZero: true,
            ticks: {
                stepSize: 1
            }
        }
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2
    }).format(value);
};
</script>

<template>
    <Head title="Dashboard de Reportes" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard de Reportes
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Sales -->
                    <Card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Ventas del Mes</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">
                                    {{ formatCurrency(kpis.total_sales) }}
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </Card>

                    <!-- Completed Jobs -->
                    <Card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Trabajos Completados</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ kpis.completed_jobs }}</p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </Card>

                    <!-- Net Profit -->
                    <Card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Ganancia Neta</p>
                                <p class="text-3xl font-bold mt-2" :class="kpis.net_profit >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(kpis.net_profit) }}
                                </p>
                            </div>
                            <div class="p-3 rounded-full" :class="kpis.net_profit >= 0 ? 'bg-green-100' : 'bg-red-100'">
                                <svg class="w-8 h-8" :class="kpis.net_profit >= 0 ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                    </Card>

                    <!-- Active Clients -->
                    <Card>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Clientes Activos</p>
                                <p class="text-3xl font-bold text-gray-900 mt-2">{{ kpis.active_clients }}</p>
                            </div>
                            <div class="p-3 bg-indigo-100 rounded-full">
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Charts Row 1 -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Sales Trend -->
                    <Card title="Ventas Mensuales">
                        <div class="h-80">
                            <Line :data="salesChartData" :options="salesChartOptions" />
                        </div>
                    </Card>

                    <!-- Jobs by Status -->
                    <Card title="Trabajos por Estado">
                        <div class="h-80 flex items-center justify-center">
                            <div class="w-full max-w-sm">
                                <Doughnut :data="jobsStatusData" :options="jobsStatusOptions" />
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Charts Row 2 -->
                <div class="grid grid-cols-1 gap-6">
                    <!-- Top Services -->
                    <Card title="Top 5 Servicios Más Solicitados">
                        <div class="h-80">
                            <Bar :data="topServicesData" :options="topServicesOptions" />
                        </div>
                    </Card>
                </div>

                <!-- Quick Links -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <a :href="route('admin.reportes.rentabilidad')" class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-full mr-4">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Rentabilidad</h3>
                                <p class="text-sm text-gray-600">Análisis por trabajo</p>
                            </div>
                        </div>
                    </a>

                    <a :href="route('admin.reportes.clientes')" class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-100 rounded-full mr-4">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Clientes con Deuda</h3>
                                <p class="text-sm text-gray-600">Gestión de cobros</p>
                            </div>
                        </div>
                    </a>

                    <a :href="route('admin.reportes.inventario')" class="block p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-100 rounded-full mr-4">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Inventario</h3>
                                <p class="text-sm text-gray-600">Consumo de materiales</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
