<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import Alert from '@/Components/UI/Alert.vue';
import MaterialSelector from '@/Components/MaterialSelector.vue';
import ProgressBar from '@/Components/UI/ProgressBar.vue';
import PhotoGallery from '@/Components/PhotoGallery.vue';

const props = defineProps({
    trabajo: Object,
    materiales: Array,
});

// Budget form
const budgetForm = useForm({
    materiales: [],
    mano_obra: 0,
    otros_costos: 0,
    notas: '',
    notas_adicionales: '',
});

// Calculate total
const totalPresupuesto = computed(() => {
    const totalMateriales = budgetForm.materiales.reduce((sum, m) => {
        return sum + (m.precio_unitario * m.cantidad);
    }, 0);
    
    return totalMateriales + parseFloat(budgetForm.mano_obra || 0) + parseFloat(budgetForm.otros_costos || 0);
});

const submitBudget = () => {
    budgetForm.post(route('admin.presupuestos.store', props.trabajo.id_trabajo), {
        onSuccess: () => {
            budgetForm.reset();
        }
    });
};

// Tracking form
const trackingForm = useForm({
    descripcion: '',
    porcentaje_avance: props.trabajo.seguimientos?.length > 0 
        ? props.trabajo.seguimientos[props.trabajo.seguimientos.length - 1].porcentaje_avance 
        : 0,
    horas_trabajadas: 0,
    fotos: [],
});

const submitTracking = () => {
    trackingForm.post(route('admin.seguimientos.store', props.trabajo.id_trabajo), {
        onSuccess: () => {
            trackingForm.reset();
            trackingForm.fotos = []; // Clear file input manually if needed
        }
    });
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
</script>

<template>
    <Head :title="`Gestionar: ${trabajo.titulo}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <Link :href="route('admin.dashboard')">
                        <Button variant="ghost" size="sm">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Volver
                        </Button>
                    </Link>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Gestionar Trabajo
                    </h2>
                </div>
                <Badge :variant="getStatusVariant(trabajo.estado)" size="lg" dot>
                    {{ trabajo.estado.replace('_', ' ') }}
                </Badge>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Progress Bar (Overall) -->
                <div v-if="trabajo.estado === 'EN_PRODUCCION' || trabajo.estado === 'FINALIZADO'" class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                    <ProgressBar :percentage="currentProgress" height="h-6" />
                </div>

                <!-- Job Info -->
                <Card title="Información del Trabajo">
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ trabajo.titulo }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Solicitado el {{ new Date(trabajo.fecha_solicitud).toLocaleDateString('es-ES') }}
                            </p>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Cliente:</h4>
                            <p class="text-gray-900">{{ trabajo.cliente.nombre }}</p>
                            <p class="text-sm text-gray-500">{{ trabajo.cliente.email }}</p>
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

                <!-- Existing Budgets -->
                <Card v-if="trabajo.presupuestos && trabajo.presupuestos.length > 0" title="Presupuestos">
                    <div class="space-y-4">
                        <div v-for="presupuesto in trabajo.presupuestos" :key="presupuesto.id_presupuesto" class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <Badge :variant="presupuesto.estado === 'PENDIENTE' ? 'warning' : presupuesto.estado === 'APROBADO' ? 'success' : 'danger'">
                                    {{ presupuesto.estado }}
                                </Badge>
                                <span class="text-sm text-gray-500">
                                    {{ new Date(presupuesto.fecha_emision).toLocaleDateString('es-ES') }}
                                </span>
                            </div>

                            <!-- Budget Details -->
                            <div v-if="presupuesto.detalles && presupuesto.detalles.length > 0" class="mb-3">
                                <h5 class="text-sm font-medium text-gray-700 mb-2">Materiales:</h5>
                                <div class="bg-gray-50 rounded p-3 space-y-1">
                                    <div v-for="detalle in presupuesto.detalles" :key="detalle.id_detalle" class="flex justify-between text-sm">
                                        <span>{{ detalle.material?.nombre }} ({{ detalle.cantidad }} {{ detalle.material?.unidad_medida }})</span>
                                        <span class="font-medium">Bs {{ detalle.subtotal }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Mano de obra:</span>
                                    <span class="font-medium">Bs {{ presupuesto.mano_obra }}</span>
                                </div>
                                <div v-if="presupuesto.otros_costos > 0" class="flex justify-between">
                                    <span class="text-gray-600">Otros costos:</span>
                                    <span class="font-medium">Bs {{ presupuesto.otros_costos }}</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-gray-200">
                                    <span class="font-semibold text-gray-900">Total:</span>
                                    <span class="font-bold text-indigo-600 text-lg">Bs {{ presupuesto.monto_total }}</span>
                                </div>
                            </div>

                            <div v-if="presupuesto.notas" class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-sm text-gray-600">{{ presupuesto.notas }}</p>
                            </div>

                            <!-- Download PDF Button -->
                            <div class="mt-3 pt-3 border-t border-gray-200 flex justify-end">
                                <a :href="route('admin.presupuestos.pdf', presupuesto.id_presupuesto)" target="_blank">
                                    <Button variant="outline" size="sm">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Descargar Cotización PDF
                                    </Button>
                                </a>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Create Budget (only if no pending or approved budget) -->
                <Card v-if="!presupuestoPendiente && !presupuestoAprobado" title="Crear Presupuesto">
                    <form @submit.prevent="submitBudget" class="space-y-6">
                        <!-- Material Selector -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Materiales Necesarios <span class="text-red-500">*</span>
                            </label>
                            <MaterialSelector v-model="budgetForm.materiales" :materiales="materiales" />
                            <p v-if="budgetForm.errors.materiales" class="mt-2 text-sm text-red-600">
                                {{ budgetForm.errors.materiales }}
                            </p>
                        </div>

                        <!-- Labor Cost -->
                        <div>
                            <label for="mano_obra" class="block text-sm font-medium text-gray-700 mb-2">
                                Mano de Obra <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                <input
                                    id="mano_obra"
                                    v-model.number="budgetForm.mano_obra"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    required
                                    class="block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                        </div>

                        <!-- Other Costs -->
                        <div>
                            <label for="otros_costos" class="block text-sm font-medium text-gray-700 mb-2">
                                Otros Costos
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                <input
                                    id="otros_costos"
                                    v-model.number="budgetForm.otros_costos"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notas" class="block text-sm font-medium text-gray-700 mb-2">
                                Notas para el Cliente
                            </label>
                            <textarea
                                id="notas"
                                v-model="budgetForm.notas"
                                rows="3"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Información adicional sobre el presupuesto..."
                            ></textarea>
                        </div>

                        <!-- Total -->
                        <div class="bg-indigo-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-semibold text-gray-900">Total del Presupuesto:</span>
                                <span class="text-2xl font-bold text-indigo-600">Bs {{ totalPresupuesto.toFixed(2) }}</span>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex justify-end">
                            <Button 
                                type="submit" 
                                variant="primary"
                                :loading="budgetForm.processing"
                                :disabled="budgetForm.processing || budgetForm.materiales.length === 0"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                {{ budgetForm.processing ? 'Enviando...' : 'Enviar Presupuesto al Cliente' }}
                            </Button>
                        </div>
                    </form>
                </Card>

                <!-- Tracking History -->
                <div v-if="trabajo.seguimientos && trabajo.seguimientos.length > 0" class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Historial de Avance</h3>
                    <div v-for="seguimiento in trabajo.seguimientos" :key="seguimiento.id_seguimiento" class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="text-sm text-gray-500">{{ new Date(seguimiento.fecha).toLocaleString('es-ES') }}</span>
                                <p class="text-gray-900 mt-1">{{ seguimiento.descripcion }}</p>
                            </div>
                            <Badge variant="default">{{ seguimiento.porcentaje_avance }}%</Badge>
                        </div>
                        
                        <div v-if="seguimiento.horas_trabajadas > 0" class="text-sm text-gray-600 mb-3">
                            ⏱️ {{ seguimiento.horas_trabajadas }} horas registradas
                        </div>

                        <!-- Photos -->
                        <div v-if="seguimiento.fotos && seguimiento.fotos.length > 0" class="mt-3">
                            <PhotoGallery :photos="seguimiento.fotos" />
                        </div>
                    </div>
                </div>

                <!-- Register Tracking -->
                <Card v-if="trabajo.estado === 'EN_PRODUCCION'" title="Registrar Actualización">
                    <form @submit.prevent="submitTracking" class="space-y-6">
                        <!-- Progress Slider -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Porcentaje de Avance: {{ trackingForm.porcentaje_avance }}%
                            </label>
                            <input
                                type="range"
                                v-model.number="trackingForm.porcentaje_avance"
                                min="0"
                                max="100"
                                step="5"
                                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                            />
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>0%</span>
                                <span>50%</span>
                                <span>100%</span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="descripcion_tracking" class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción del Avance
                            </label>
                            <textarea
                                id="descripcion_tracking"
                                v-model="trackingForm.descripcion"
                                rows="3"
                                required
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Describe qué se ha realizado..."
                            ></textarea>
                        </div>

                        <!-- Hours Worked -->
                        <div>
                            <label for="horas_trabajadas" class="block text-sm font-medium text-gray-700 mb-2">
                                Horas Trabajadas (Opcional)
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">⏱️</span>
                                <input
                                    id="horas_trabajadas"
                                    v-model.number="trackingForm.horas_trabajadas"
                                    type="number"
                                    min="0"
                                    step="0.5"
                                    class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                />
                            </div>
                        </div>

                        <!-- Photos Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Fotos de Evidencia (Máx 5)
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-500 transition-colors">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="fotos-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                            <span>Subir fotos</span>
                                            <input id="fotos-upload" type="file" multiple accept="image/*" class="sr-only" @input="trackingForm.fotos = $event.target.files" />
                                        </label>
                                        <p class="pl-1">o arrastrar y soltar</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PNG, JPG, WEBP hasta 5MB
                                    </p>
                                    <p v-if="trackingForm.fotos.length > 0" class="text-sm font-medium text-green-600">
                                        {{ trackingForm.fotos.length }} archivos seleccionados
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" variant="secondary" :loading="trackingForm.processing">
                                Registrar Actualización
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
