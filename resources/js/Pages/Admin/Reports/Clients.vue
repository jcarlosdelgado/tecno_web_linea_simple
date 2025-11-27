<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Badge from '@/Components/UI/Badge.vue';
import Alert from '@/Components/UI/Alert.vue';

const props = defineProps({
    clients: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-BO', {
        style: 'currency',
        currency: 'BOB',
        minimumFractionDigits: 2
    }).format(value);
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
    <Head title="Clientes con Deuda" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Clientes con Deuda
                </h2>
                <div class="flex gap-3">
                    <a 
                        :href="route('admin.reportes.clientes.pdf')" 
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
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card>
                        <p class="text-sm font-medium text-gray-600">Clientes con Deuda</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ clients.total }}</p>
                    </Card>
                    <Card>
                        <p class="text-sm font-medium text-gray-600">Deuda Total</p>
                        <p class="text-3xl font-bold text-red-600 mt-2">{{ formatCurrency(clients.globalTotalDebt || 0) }}</p>
                    </Card>
                    <Card>
                        <p class="text-sm font-medium text-gray-600">Pagos Vencidos</p>
                        <p class="text-3xl font-bold text-orange-600 mt-2">{{ clients.globalOverduePayments || 0 }}</p>
                    </Card>
                </div>

                <!-- Alert if no debt -->
                <Alert v-if="clients.total === 0" type="success" title="¡Excelente!">
                    No hay clientes con deudas pendientes en este momento.
                </Alert>

                <!-- Clients Table -->
                <Card v-if="clients.total > 0" title="Detalle de Clientes">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Trabajos con Deuda
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Deuda Total
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Pagos Vencidos
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="client in clients.data" :key="client.client_id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <span class="text-indigo-600 font-semibold text-sm">
                                                    {{ client.name.charAt(0).toUpperCase() }}
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ client.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ client.email }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <div class="flex flex-wrap gap-1">
                                            <Badge v-for="(job, index) in client.jobs_with_debt" :key="index" variant="default" size="sm">
                                                {{ job }}
                                            </Badge>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600 text-right">
                                        {{ formatCurrency(client.total_debt) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <Badge v-if="client.overdue_payments > 0" variant="danger">
                                            {{ client.overdue_payments }} vencidas
                                        </Badge>
                                        <Badge v-else variant="warning">
                                            Al día
                                        </Badge>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6" v-if="clients.last_page > 1">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <button
                                    @click="changePage(clients.prev_page_url)"
                                    :disabled="!clients.prev_page_url"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Anterior
                                </button>
                                <button
                                    @click="changePage(clients.next_page_url)"
                                    :disabled="!clients.next_page_url"
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Siguiente
                                </button>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Mostrando
                                        <span class="font-medium">{{ clients.from }}</span>
                                        a
                                        <span class="font-medium">{{ clients.to }}</span>
                                        de
                                        <span class="font-medium">{{ clients.total }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <button
                                            @click="changePage(clients.prev_page_url)"
                                            :disabled="!clients.prev_page_url"
                                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <span class="sr-only">Anterior</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        
                                        <template v-for="link in clients.links" :key="link.label">
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
                                            @click="changePage(clients.next_page_url)"
                                            :disabled="!clients.next_page_url"
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
