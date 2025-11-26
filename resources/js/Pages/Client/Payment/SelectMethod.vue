<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';
import axios from 'axios';

const $page = usePage();

const props = defineProps({
    trabajo: Object,
    total: Number,
});

const selectedType = ref('CONTADO');
const selectedInstallments = ref(3);
const processing = ref(false);

const installmentsOptions = [2, 3, 6, 12];

const installmentAmount = computed(() => {
    return props.total / selectedInstallments.value;
});

const form = useForm({
    tipo_pago: 'CONTADO',
    numero_cuotas: 1,
    metodo_pago: 'QR_PAGOFACIL',
});

const handlePayment = async () => {
    processing.value = true;
    try {
        console.log('Initiating payment...');
        
        // Actualizar el form con los valores seleccionados
        form.tipo_pago = selectedType.value;
        form.numero_cuotas = selectedType.value === 'CREDITO' ? selectedInstallments.value : 1;
        form.metodo_pago = 'QR_PAGOFACIL';
        
        // Llamar al endpoint de iniciar pago
        const response = await axios.post(route('pagos.initiate', props.trabajo.id_trabajo), {
            tipo_pago: form.tipo_pago,
            numero_cuotas: form.numero_cuotas,
            metodo_pago: form.metodo_pago
        });

        if (response.data.success) {
            console.log('Payment initiated successfully:', response.data);
            
            // Redirigir al checkout para mostrar el QR, tanto para CONTADO como para CREDITO
            // En caso de crédito, se muestra el QR de la primera cuota
            router.visit(route('pagos.checkout', response.data.pago.id_pago));
        } else {
            alert('Error al iniciar el pago');
        }
    } catch (error) {
        console.error('Payment error:', error);
        const errorMsg = error.response?.data?.message || error.response?.data?.error || 'Error al procesar el pago';
        alert(errorMsg);
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Head title="Seleccionar Método de Pago" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Finalizar Contratación
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <Card title="Resumen del Pedido">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ trabajo.titulo }}</h3>
                            <p class="text-gray-500">{{ trabajo.descripcion }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total a Pagar</p>
                            <p class="text-3xl font-bold text-indigo-600">Bs {{ total.toFixed(2) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Option: Contado -->
                        <div 
                            class="border-2 rounded-xl p-6 cursor-pointer transition-all hover:shadow-md"
                            :class="selectedType === 'CONTADO' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300'"
                            @click="selectedType = 'CONTADO'"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-bold text-lg text-gray-900">Pago al Contado</h4>
                                <div v-if="selectedType === 'CONTADO'" class="text-indigo-600">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                </div>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">Paga el total ahora y asegura el inicio inmediato de tu trabajo.</p>
                            <Badge variant="success">Recomendado</Badge>
                        </div>

                        <!-- Option: Crédito -->
                        <div 
                            class="border-2 rounded-xl p-6 cursor-pointer transition-all hover:shadow-md"
                            :class="selectedType === 'CREDITO' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300'"
                            @click="selectedType = 'CREDITO'"
                        >
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="font-bold text-lg text-gray-900">Pago a Crédito</h4>
                                <div v-if="selectedType === 'CREDITO'" class="text-indigo-600">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                </div>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">Paga en cuotas mensuales sin intereses.</p>
                        </div>
                    </div>

                    <!-- Credit Options -->
                    <div v-if="selectedType === 'CREDITO'" class="bg-white border border-gray-200 rounded-lg p-6 mb-8 animate-fade-in-down">
                        <h4 class="font-medium text-gray-900 mb-4">Configura tu plan de pagos</h4>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Número de Cuotas</label>
                            <div class="flex gap-3 flex-wrap">
                                <button 
                                    v-for="n in installmentsOptions" 
                                    :key="n"
                                    type="button"
                                    class="px-4 py-2 rounded-lg border transition-colors"
                                    :class="selectedInstallments === n ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:border-indigo-500'"
                                    @click="selectedInstallments = n"
                                >
                                    {{ n }} Cuotas
                                </button>
                            </div>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex justify-between items-center text-sm mb-2">
                                <span class="text-gray-600">Monto por cuota:</span>
                                <span class="font-bold text-gray-900">Bs {{ installmentAmount.toFixed(2) }} / mes</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">Total a pagar:</span>
                                <span class="font-bold text-gray-900">Bs {{ total.toFixed(2) }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">* Sin intereses</p>
                        </div>
                    </div>

                    <!-- Payment Button -->
                    <div class="flex justify-end pt-6 border-t border-gray-200">
                        <Button 
                            @click="handlePayment" 
                            variant="primary" 
                            size="lg"
                            :loading="processing"
                        >
                            <span class="mr-2">{{ selectedType === 'CREDITO' ? 'Crear Plan de Cuotas' : 'Pagar con' }}</span>
                            <img v-if="selectedType === 'CONTADO'" src="https://pagofacil.com.bo/images/logo_pagofacil.png" alt="Pago Fácil" class="h-6 inline-block bg-white rounded px-1" />
                        </Button>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-in-down {
    animation: fadeInDown 0.3s ease-out;
}
@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
