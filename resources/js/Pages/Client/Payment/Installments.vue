<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import axios from 'axios';

const props = defineProps({
    pago: Object,
    cuotas: Array,
    trabajo: Object,
});

const processingCuota = ref(null);

const cuotasPendientes = computed(() => {
    return props.cuotas.filter(c => c.estado === 'PENDIENTE').length;
});

const cuotasPagadas = computed(() => {
    return props.cuotas.filter(c => c.estado === 'PAGADA').length;
});

const progreso = computed(() => {
    return (cuotasPagadas.value / props.cuotas.length) * 100;
});

const getStatusVariant = (estado) => {
    const variants = {
        'PENDIENTE': 'warning',
        'PAGADA': 'success',
        'VENCIDA': 'danger',
    };
    return variants[estado] || 'default';
};

const getStatusText = (estado) => {
    const texts = {
        'PENDIENTE': 'Pendiente',
        'PAGADA': 'Pagada',
        'VENCIDA': 'Vencida',
    };
    return texts[estado] || estado;
};

const pagarCuota = async (cuota) => {
    if (cuota.estado !== 'PENDIENTE') {
        alert('Esta cuota ya ha sido pagada o está vencida.');
        return;
    }

    processingCuota.value = cuota.id_cuota;
    
    try {
        // Generar QR para la cuota
        const response = await axios.post(route('cuotas.generateQR', cuota.id_cuota));

        if (response.data.success) {
            // Redirigir al checkout (el controlador se encargará de mostrar el QR de esta cuota)
            router.visit(route('pagos.checkout', props.pago.id_pago));
        } else {
            alert('Error al generar QR para la cuota');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al procesar el pago de la cuota');
    } finally {
        processingCuota.value = null;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-BO', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

const isVencida = (cuota) => {
    if (cuota.estado !== 'PENDIENTE') return false;
    const vencimiento = new Date(cuota.fecha_vencimiento);
    return vencimiento < new Date();
};

const descargarComprobanteCuota = (cuotaId) => {
    window.open(route('cuotas.descargar-comprobante', cuotaId), '_blank');
};

</script>

<template>
    <Head title="Mis Cuotas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Plan de Cuotas - {{ trabajo.titulo }}
                </h2>
                <Button 
                    variant="secondary" 
                    size="sm"
                    @click="router.visit(route('trabajos.show', trabajo.id_trabajo))"
                >
                    Volver al Trabajo
                </Button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Resumen del Plan -->
                <Card title="Resumen del Plan de Pagos">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Total del Trabajo</p>
                            <p class="text-2xl font-bold text-gray-900">Bs {{ parseFloat(pago.monto).toFixed(2) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Número de Cuotas</p>
                            <p class="text-2xl font-bold text-indigo-600">{{ pago.numero_cuotas }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Cuotas Pagadas</p>
                            <p class="text-2xl font-bold text-green-600">{{ cuotasPagadas }} / {{ cuotas.length }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-500 mb-1">Cuotas Pendientes</p>
                            <p class="text-2xl font-bold text-orange-600">{{ cuotasPendientes }}</p>
                        </div>
                    </div>

                    <!-- Barra de Progreso -->
                    <div class="mt-6">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700">Progreso de Pagos</span>
                            <span class="text-sm font-medium text-gray-700">{{ progreso.toFixed(0) }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div 
                                class="bg-gradient-to-r from-indigo-500 to-indigo-600 h-3 rounded-full transition-all duration-500"
                                :style="{ width: progreso + '%' }"
                            ></div>
                        </div>
                    </div>
                </Card>

                <!-- Lista de Cuotas -->
                <Card title="Detalle de Cuotas">
                    <div class="space-y-4">
                        <div 
                            v-for="cuota in cuotas" 
                            :key="cuota.id_cuota"
                            class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow"
                            :class="{
                                'bg-green-50 border-green-200': cuota.estado === 'PAGADA',
                                'bg-red-50 border-red-200': isVencida(cuota),
                                'bg-white': cuota.estado === 'PENDIENTE' && !isVencida(cuota)
                            }"
                        >
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <!-- Info de la Cuota -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="text-lg font-semibold text-gray-900">
                                            Cuota {{ cuota.numero_cuota }} de {{ pago.numero_cuotas }}
                                        </h4>
                                        <Badge :variant="getStatusVariant(cuota.estado)">
                                            {{ getStatusText(cuota.estado) }}
                                        </Badge>
                                        <Badge v-if="isVencida(cuota)" variant="danger">
                                            Vencida
                                        </Badge>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">Monto:</span>
                                            <span class="ml-2 font-semibold text-gray-900">Bs {{ parseFloat(cuota.monto).toFixed(2) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Vencimiento:</span>
                                            <span class="ml-2 font-medium text-gray-900">{{ formatDate(cuota.fecha_vencimiento) }}</span>
                                        </div>
                                        <div v-if="cuota.fecha_pago">
                                            <span class="text-gray-500">Fecha de Pago:</span>
                                            <span class="ml-2 font-medium text-green-600">{{ formatDate(cuota.fecha_pago) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón de Pago -->
                                <div v-if="cuota.estado === 'PENDIENTE'" class="flex-shrink-0">
                                    <Button 
                                        @click="pagarCuota(cuota)"
                                        variant="primary"
                                        :loading="processingCuota === cuota.id_cuota"
                                        :disabled="processingCuota !== null && processingCuota !== cuota.id_cuota"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Pagar Ahora
                                    </Button>
                                </div>
                                <div v-else-if="cuota.estado === 'PAGADA'" class="flex-shrink-0 flex gap-2">
                                    <div class="flex items-center text-green-600">
                                        <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="font-medium">Pagada</span>
                                    </div>
                                    <Button 
                                        variant="success"
                                        size="sm"
                                        @click="descargarComprobanteCuota(cuota.id_cuota)"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Comprobante
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Mensaje informativo -->
                    <div v-if="cuotasPendientes > 0" class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium mb-1">Información importante:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li v-if="cuotasPagadas === 0">El trabajo iniciará después de que pagues la primera cuota.</li>
                                    <li>Puedes pagar tus cuotas en cualquier momento antes de su vencimiento.</li>
                                    <li>El trabajo se marcará como finalizado cuando todas las cuotas estén pagadas y el progreso sea del 100%.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div v-else class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-sm font-medium text-green-800">
                                ¡Felicidades! Has completado el pago de todas las cuotas.
                            </p>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Animación suave para la barra de progreso */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 500ms;
}
</style>
