<script setup>
import { ref, computed } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    gastos: Object,
    stats: Object,
    categorias: Array,
    filtros: Object,
});

const fechaDesde = ref(props.filtros?.fecha_desde || '');
const fechaHasta = ref(props.filtros?.fecha_hasta || '');
const categoriaFiltro = ref(props.filtros?.categoria || '');

const aplicarFiltros = () => {
    router.get(route('admin.gastos-operativos.index'), {
        fecha_desde: fechaDesde.value,
        fecha_hasta: fechaHasta.value,
        categoria: categoriaFiltro.value,
    }, { preserveState: true });
};

const limpiarFiltros = () => {
    fechaDesde.value = '';
    fechaHasta.value = '';
    categoriaFiltro.value = '';
    router.get(route('admin.gastos-operativos.index'));
};

const eliminarGasto = (gastoId) => {
    if (confirm('¿Estás seguro de eliminar este gasto operativo?')) {
        router.delete(route('admin.gastos-operativos.destroy', gastoId));
    }
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

const verComprobante = (ruta) => {
    if (ruta) {
        window.open(`/storage/${ruta}`, '_blank');
    }
};
</script>

<template>
    <Head title="Gastos Operativos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gastos Operativos
                </h2>
                <Link :href="route('admin.gastos-operativos.create')">
                    <Button variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Registrar Gasto
                    </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Estadísticas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Gasto Hoy</p>
                            <p class="text-3xl font-bold text-orange-600">Bs {{ parseFloat(stats.total_dia).toFixed(2) }}</p>
                        </div>
                    </Card>

                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Gasto Mes Actual</p>
                            <p class="text-3xl font-bold text-red-600">Bs {{ parseFloat(stats.total_mes_actual).toFixed(2) }}</p>
                        </div>
                    </Card>

                    <Card>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Gasto Total</p>
                            <p class="text-3xl font-bold text-gray-900">Bs {{ parseFloat(stats.total_general).toFixed(2) }}</p>
                        </div>
                    </Card>
                </div>

                <!-- Filtros -->
                <Card>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filtrar Gastos</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Desde</label>
                            <input 
                                v-model="fechaDesde"
                                type="date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha Hasta</label>
                            <input 
                                v-model="fechaHasta"
                                type="date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                            <select 
                                v-model="categoriaFiltro"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Todas</option>
                                <option v-for="cat in categorias" :key="cat" :value="cat">{{ cat }}</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <Button variant="primary" @click="aplicarFiltros" class="flex-1">
                                Filtrar
                            </Button>
                            <Button variant="secondary" @click="limpiarFiltros" class="flex-1">
                                Limpiar
                            </Button>
                        </div>
                    </div>
                </Card>

                <!-- Lista de Gastos -->
                <Card v-if="gastos.data.length > 0">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoría</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descripción</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Registrado Por</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="gasto in gastos.data" :key="gasto.id_gasto_operativo" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(gasto.fecha) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <Badge variant="info">{{ gasto.categoria }}</Badge>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ gasto.descripcion }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                                        Bs {{ parseFloat(gasto.monto).toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ gasto.registrador?.name || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <Button 
                                                v-if="gasto.comprobante"
                                                variant="secondary" 
                                                size="sm"
                                                @click="verComprobante(gasto.comprobante)"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </Button>
                                            <Link :href="route('admin.gastos-operativos.edit', gasto.id_gasto_operativo)">
                                                <Button variant="primary" size="sm">
                                                    Editar
                                                </Button>
                                            </Link>
                                            <Button 
                                                variant="danger" 
                                                size="sm"
                                                @click="eliminarGasto(gasto.id_gasto_operativo)"
                                            >
                                                Eliminar
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="gastos.links.length > 3" class="mt-6 flex justify-center">
                        <nav class="flex gap-2">
                            <Link 
                                v-for="(link, index) in gastos.links" 
                                :key="index"
                                :href="link.url"
                                class="px-4 py-2 text-sm rounded-lg transition-colors"
                                :class="link.active ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                v-html="link.label"
                            />
                        </nav>
                    </div>
                </Card>

                <!-- Estado vacío -->
                <Card v-else>
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay gastos operativos</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Comienza registrando el primer gasto operativo.
                        </p>
                        <div class="mt-6">
                            <Link :href="route('admin.gastos-operativos.create')">
                                <Button variant="primary">
                                    Registrar Primer Gasto
                                </Button>
                            </Link>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
