<script setup>
import { computed } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Alert from '@/Components/UI/Alert.vue';
import ProgressBar from '@/Components/UI/ProgressBar.vue';
import PhotoGallery from '@/Components/PhotoGallery.vue';

const props = defineProps({
    trabajo: Object,
    presupuestos: Array,
    pagos: Array,
    seguimientos: Array,
});

const approveForm = useForm({});
const rejectForm = useForm({});

const approveBudget = (presupuestoId) => {
    if (confirm('¿Estás seguro de aprobar este presupuesto? Serás redirigido a realizar el pago para que iniciemos tu trabajo.')) {
        approveForm.post(route('presupuestos.approve', presupuestoId));
    }
};

const rejectBudget = (presupuestoId) => {
    if (confirm('¿Estás seguro de rechazar este presupuesto?')) {
        rejectForm.post(route('presupuestos.reject', presupuestoId));
    }
};

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

const presupuestoPendiente = computed(() => {
    return props.trabajo.presupuestos?.find(p => p.estado === 'PENDIENTE');
});

const presupuestoAprobado = computed(() => {
    return props.trabajo.presupuestos?.find(p => p.estado === 'APROBADO');
});

const currentProgress = computed(() => {
    if (!props.trabajo.seguimientos || props.trabajo.seguimientos.length === 0) return 0;
    return props.trabajo.seguimientos[props.trabajo.seguimientos.length - 1].porcentaje_avance;
});

// Timeline events
const timelineEvents = computed(() => {
    const events = [];
    
    // Job requested
    events.push({
        type: 'solicitud',
        fecha: props.trabajo.fecha_solicitud,
        titulo: 'Trabajo Solicitado',
        descripcion: 'Has solicitado este trabajo',
        icon: '📝',
    });
    
    // Budgets
    if (props.trabajo.presupuestos) {
        props.trabajo.presupuestos.forEach(p => {
            events.push({
                type: 'presupuesto',
                fecha: p.fecha_emision,
                titulo: `Presupuesto ${p.estado}`,
                descripcion: `Monto: $${p.monto_total}`,
                icon: '💰',
            });
        });
    }
    
    // Payments
    if (props.trabajo.pagos) {
        props.trabajo.pagos.forEach(p => {
            events.push({
                type: 'pago',
                fecha: p.fecha_pago,
                titulo: 'Pago Realizado',
                descripcion: `Monto: $${p.monto}`,
                icon: '💳',
            });
        });
    }
    
    // Tracking updates
    if (props.trabajo.seguimientos) {
        props.trabajo.seguimientos.forEach(s => {
            events.push({
                type: 'seguimiento',
                fecha: s.fecha,
                titulo: `Avance: ${s.porcentaje_avance}%`,
                descripcion: s.descripcion,
                icon: '🔄',
                fotos: s.fotos
            });
        });
    }
    
    
    return events.sort((a, b) => new Date(b.fecha) - new Date(a.fecha)); // Sort newest first
});

import { onMounted, ref } from 'vue';

const showPaymentSuccess = ref(false);

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('payment_success') === 'true') {
        showPaymentSuccess.value = true;
        // Limpiar URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
</script>

<template>
    <Head :title="trabajo.titulo" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="route('dashboard')">
                        <Button variant="ghost" size="sm">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Volver
                        </Button>
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Detalles del Trabajo
                    </h2>
                </div>
                <Badge :variant="getStatusVariant(trabajo.estado)" size="lg" dot>
                    {{ trabajo.estado.replace('_', ' ') }}
                </Badge>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Progress Bar (Overall) -->
                <div v-if="trabajo.estado === 'EN_PRODUCCION' || trabajo.estado === 'FINALIZADO'" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
                    <ProgressBar :percentage="currentProgress" height="h-6" />
                </div>

                <!-- Payment Success Alert -->
                <div v-if="showPaymentSuccess" class="mb-6">
                    <Alert type="success" title="¡Pago Realizado con Éxito!">
                        Tu pago ha sido procesado correctamente. El estado del trabajo se ha actualizado.
                    </Alert>
                </div>
                
                <!-- DEBUG INFO -->
                <div class="bg-gray-100 p-4 mb-4 rounded text-xs font-mono hidden">
                    PRESUPUESTO APROBADO: {{ presupuestoAprobado ? 'SI' : 'NO' }}
                    ESTADO: {{ trabajo.estado }}
                    PRESUPUESTOS: {{ trabajo.presupuestos }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Job Info -->
                        <Card>
                            <div class="space-y-4">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">{{ trabajo.titulo }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Solicitado el {{ new Date(trabajo.fecha_solicitud).toLocaleDateString('es-ES') }}
                                    </p>
                                </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-700 mb-1">Descripción:</h4>
                                    <p class="text-gray-900 whitespace-pre-wrap">{{ trabajo.descripcion }}</p>
                                </div>

                                <!-- Reference Images -->
                                <div v-if="trabajo.imagenes_referencia && trabajo.imagenes_referencia.length > 0">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Imágenes de Referencia:</h4>
                                    <PhotoGallery :photos="trabajo.imagenes_referencia" />
                                </div>
                            </div>
                        </Card>

                        <!-- Pending Budget -->
                        <Card v-if="presupuestoPendiente" title="Presupuesto Recibido">
                            <div class="space-y-4">
                                <Alert type="info" title="Nuevo Presupuesto">
                                    Tienes un presupuesto pendiente de revisión. Por favor revisa los detalles y decide si deseas aprobarlo o rechazarlo.
                                </Alert>

                                <!-- Budget Details -->
                                <div v-if="presupuestoPendiente.detalles && presupuestoPendiente.detalles.length > 0">
                                    <h5 class="text-sm font-medium text-gray-700 mb-3">Materiales Incluidos:</h5>
                                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="detalle in presupuestoPendiente.detalles" :key="detalle.id_detalle">
                                                    <td class="px-4 py-3 text-sm text-gray-900">{{ detalle.material?.nombre }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">{{ detalle.cantidad }} {{ detalle.material?.unidad_medida }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-600">Bs {{ detalle.precio_unitario }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">Bs {{ detalle.subtotal }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <!-- Cost Breakdown -->
                                <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Mano de obra:</span>
                                        <span class="font-medium">Bs {{ presupuestoPendiente.mano_obra }}</span>
                                    </div>
                                    <div v-if="presupuestoPendiente.otros_costos > 0" class="flex justify-between text-sm">
                                        <span class="text-gray-600">Otros costos:</span>
                                        <span class="font-medium">Bs {{ presupuestoPendiente.otros_costos }}</span>
                                    </div>
                                    <div class="flex justify-between pt-2 border-t border-gray-200">
                                        <span class="font-semibold text-gray-900 text-lg">Total:</span>
                                        <span class="font-bold text-indigo-600 text-2xl">Bs {{ presupuestoPendiente.monto_total }}</span>
                                    </div>
                                </div>

                                <div v-if="presupuestoPendiente.notas" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h5 class="text-sm font-medium text-blue-900 mb-1">Notas del Presupuesto:</h5>
                                    <p class="text-sm text-blue-800">{{ presupuestoPendiente.notas }}</p>
                                </div>

                                <!-- Download PDF Button -->
                                <div class="flex justify-end">
                                    <a :href="route('presupuestos.pdf', presupuestoPendiente.id_presupuesto)" target="_blank">
                                        <Button variant="outline" size="sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Descargar Cotización PDF
                                        </Button>
                                    </a>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-3 pt-4">
                                    <Button
                                        @click="approveBudget(presupuestoPendiente.id_presupuesto)"
                                        variant="primary"
                                        class="flex-1"
                                        :loading="approveForm.processing"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Aprobar Presupuesto
                                    </Button>
                                    <Button
                                        @click="rejectBudget(presupuestoPendiente.id_presupuesto)"
                                        variant="danger"
                                        class="flex-1"
                                        :loading="rejectForm.processing"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Rechazar
                                    </Button>
                                </div>
                            </div>
                        </Card>

                        <!-- Waiting for Budget -->
                        <Alert v-if="!presupuestoPendiente && !presupuestoAprobado && trabajo.estado === 'SOLICITADO'" type="info" title="En Espera">
                            Tu solicitud ha sido recibida. Pronto recibirás un presupuesto detallado.
                        </Alert>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Timeline -->
                        <Card title="Historial">
                            <div class="space-y-6">
                                <div v-for="(event, index) in timelineEvents" :key="index" class="relative pl-8 pb-6 border-l-2 border-indigo-100 last:pb-0">
                                    <div class="absolute -left-[17px] top-0 w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-lg border-2 border-white">
                                        {{ event.icon }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex justify-between items-start">
                                            <p class="text-sm font-bold text-gray-900">{{ event.titulo }}</p>
                                            <span class="text-xs text-gray-500">{{ new Date(event.fecha).toLocaleDateString('es-ES') }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1">{{ event.descripcion }}</p>
                                        
                                        <!-- Photos in timeline -->
                                        <div v-if="event.fotos && event.fotos.length > 0" class="mt-3">
                                            <PhotoGallery :photos="event.fotos" />
                                        </div>
                                    </div>
                                </div>

                                <div v-if="timelineEvents.length === 0" class="text-center py-4 text-gray-500 text-sm">
                                    No hay eventos aún
                                </div>
                            </div>
                        </Card>

                        <!-- Payment Button - Después de aprobar presupuesto -->
                        <Card v-if="presupuestoAprobado && trabajo.estado === 'PRESUPUESTADO'" title="Proceder al Pago">
                            <div class="space-y-3">
                                <Alert type="info" title="Pago Requerido">
                                    Para que iniciemos tu trabajo, necesitas realizar el pago primero.
                                </Alert>
                                <p class="text-sm text-gray-600">
                                    Monto a pagar: <span class="font-bold text-gray-900">Bs {{ presupuestoAprobado.monto_total }}</span>
                                </p>
                                <Link :href="route('pagos.select', trabajo.id_trabajo)">
                                    <Button variant="primary" class="w-full">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        Pagar Ahora
                                    </Button>
                                </Link>
                            </div>
                        </Card>

                        <!-- Work in Progress Info -->
                        <Card v-if="trabajo.estado === 'EN_PRODUCCION'" title="Estado del Trabajo">
                            <div class="space-y-3">
                                <Alert type="success" title="Trabajo en Producción">
                                    ¡Tu trabajo está siendo procesado! Ya realizaste el pago.
                                </Alert>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Progreso actual:</span>
                                    <span class="font-bold text-indigo-600">{{ currentProgress }}%</span>
                                </div>
                                
                                <!-- Plan de Cuotas (si aplica) -->
                                <div v-if="trabajo.pagos && trabajo.pagos.some(p => p.tipo_pago === 'CREDITO')">
                                    <Link :href="route('cuotas.index', trabajo.pagos.find(p => p.tipo_pago === 'CREDITO').id_pago)" class="block mt-3">
                                        <Button variant="secondary" class="w-full" size="sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                            Ver Plan de Cuotas
                                        </Button>
                                    </Link>
                                </div>
                                
                                <!-- Download Quotation -->
                                <a :href="route('presupuestos.pdf', presupuestoAprobado.id_presupuesto)" target="_blank" class="mt-3">
                                    <Button variant="outline" class="w-full" size="sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Ver Cotización
                                    </Button>
                                </a>
                            </div>
                        </Card>

                        <!-- Work Completed - Download Invoice -->
                        <Card v-if="trabajo.estado === 'FINALIZADO' && trabajo.pagos && trabajo.pagos.some(p => p.estado === 'COMPLETADO')" title="Trabajo Completado">
                            <div class="space-y-3">
                                <Alert type="success" title="¡Listo!">
                                    Tu trabajo ha sido completado exitosamente.
                                </Alert>
                                
                                <!-- Plan de Cuotas (si aplica) -->
                                <div v-if="trabajo.pagos.some(p => p.tipo_pago === 'CREDITO')">
                                    <Link :href="route('cuotas.index', trabajo.pagos.find(p => p.tipo_pago === 'CREDITO').id_pago)" class="block mb-3">
                                        <Button variant="secondary" class="w-full" size="sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                            Ver Plan de Cuotas
                                        </Button>
                                    </Link>
                                </div>
                                
                                <a :href="route('pagos.comprobante', trabajo.pagos.find(p => p.estado === 'COMPLETADO').id_pago)" target="_blank" class="mt-3">
                                    <Button variant="outline" class="w-full">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Descargar Comprobante
                                    </Button>
                                </a>
                                
                                <a :href="route('presupuestos.pdf', presupuestoAprobado.id_presupuesto)" target="_blank">
                                    <Button variant="outline" class="w-full" size="sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Ver Cotización
                                    </Button>
                                </a>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
