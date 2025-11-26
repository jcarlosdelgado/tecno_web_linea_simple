<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    pago: Object,
    qrData: Object, // { qr_base64, transaction_id, expiration_date, checkout_url, etc }
});

const loading = ref(false);
const paymentStatus = ref('PENDIENTE');
const pollInterval = ref(null);

// Calcular tiempo restante basado en expiration_date
const timeLeft = ref(0);
const timerInterval = ref(null);

onMounted(() => {
    // Calcular tiempo restante
    if (props.qrData?.expiration_date) {
        const expirationTime = new Date(props.qrData.expiration_date).getTime();
        const now = new Date().getTime();
        timeLeft.value = Math.max(0, Math.floor((expirationTime - now) / 1000));
    } else {
        timeLeft.value = 300; // 5 minutos por defecto
    }

    // Iniciar countdown
    timerInterval.value = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval.value);
        }
    }, 1000);

    // Iniciar polling del estado del pago
    startPolling();
});

onUnmounted(() => {
    stopPolling();
    if (timerInterval.value) {
        clearInterval(timerInterval.value);
    }
});

const startPolling = () => {
    // Verificar estado cada 5 segundos
    pollInterval.value = setInterval(async () => {
        await checkPaymentStatus();
    }, 5000);
};

const stopPolling = () => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
        pollInterval.value = null;
    }
};

const checkPaymentStatus = async () => {
    try {
        const response = await axios.get(route('pagos.status', props.pago.id_pago));
        
        if (response.data.success) {
            const estado = response.data.data.estado;
            paymentStatus.value = estado;

            // Si el pago fue completado, redirigir
            if (estado === 'PAGADO') {
                stopPolling();
                setTimeout(() => {
                    router.visit(route('trabajos.show', props.pago.id_trabajo), {
                        method: 'get',
                        data: { payment_success: true }
                    });
                }, 1500);
            }
        }
    } catch (error) {
        console.error('Error verificando estado:', error);
    }
};

const formatTime = (seconds) => {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s < 10 ? '0' : ''}${s}`;
};

const qrImageSrc = computed(() => {
    if (props.qrData?.qr_base64) {
        return `data:image/png;base64,${props.qrData.qr_base64}`;
    }
    return null;
});

const isExpired = computed(() => timeLeft.value === 0);
const isPaid = computed(() => paymentStatus.value === 'PAGADO');
</script>

<template>
    <Head title="Pasarela de Pago" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-purple-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-2xl shadow-2xl">
            <!-- Header -->
            <div class="text-center">
                <img class="mx-auto h-12 w-auto" src="https://pagofacil.com.bo/images/logo_pagofacil.png" alt="Pago Fácil" />
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                    Escanea el QR
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    <span v-if="qrData?.cuota">
                        Cuota {{ qrData.cuota.numero_cuota }} de {{ pago.numero_cuotas }}: 
                        <strong class="text-indigo-600">Bs {{ parseFloat(qrData.cuota.monto).toFixed(2) }}</strong>
                    </span>
                    <span v-else>
                        Para completar tu pago de <strong class="text-indigo-600">Bs {{ parseFloat(pago.monto).toFixed(2) }}</strong>
                    </span>
                </p>
                <p v-if="pago.tipo_pago === 'CREDITO' && qrData?.cuota" class="mt-1 text-xs text-gray-500">
                    Vence: {{ new Date(qrData.cuota.fecha_vencimiento).toLocaleDateString('es-BO') }}
                </p>
            </div>

            <!-- Payment Status Badge -->
            <div v-if="isPaid" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg text-center animate-pulse">
                <svg class="w-6 h-6 inline-block mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <strong>¡Pago Confirmado!</strong> Redirigiendo...
            </div>

            <div v-else-if="isExpired" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg text-center">
                <strong>QR Expirado</strong> - Por favor, genera un nuevo código
            </div>

            <!-- QR Container -->
            <div class="flex justify-center my-8">
                <div v-if="loading" class="w-64 h-64 bg-gray-200 animate-pulse rounded-lg flex items-center justify-center">
                    <span class="text-gray-500">Cargando QR...</span>
                </div>
                <div v-else-if="qrImageSrc" class="relative group">
                    <img 
                        :src="qrImageSrc" 
                        alt="QR de Pago" 
                        class="w-64 h-64 border-4 rounded-lg shadow-lg transition-all"
                        :class="isExpired ? 'border-red-300 opacity-50 grayscale' : 'border-indigo-500'"
                    />
                    <div v-if="!isExpired && !isPaid" class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black bg-opacity-10 rounded-lg">
                        <span class="bg-white px-3 py-1 rounded-full text-xs font-bold shadow">Escanear con app</span>
                    </div>
                </div>
                <div v-else class="w-64 h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                    <span class="text-gray-400">QR no disponible</span>
                </div>
            </div>

            <!-- Timer -->
            <div class="text-center" v-if="!isPaid">
                <p class="text-sm font-medium" :class="timeLeft < 60 ? 'text-red-600 animate-pulse' : 'text-gray-600'">
                    <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                    </svg>
                    Tiempo restante: {{ formatTime(timeLeft) }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    ID: {{ qrData?.transaction_id || pago.transaction_id || 'N/A' }}
                </p>
            </div>
            <!-- Alternative Payment Link -->
            <div v-if="qrData?.checkout_url && !isPaid && !isExpired" class="text-center">
                <a 
                    :href="qrData.checkout_url" 
                    target="_blank"
                    class="text-indigo-600 hover:text-indigo-800 text-sm font-medium underline"
                >
                    O paga desde el navegador →
                </a>
            </div>

            <!-- Actions -->
            <div class="mt-8 space-y-4">
                <Link 
                    v-if="pago.tipo_pago === 'CREDITO' && !isPaid"
                    :href="route('cuotas.index', pago.id_pago)" 
                    class="w-full flex justify-center py-2 px-4 border border-indigo-300 rounded-md shadow-sm text-sm font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 focus:outline-none transition-colors"
                >
                    Ver Plan de Cuotas
                </Link>
                
                <Link 
                    :href="route('trabajos.show', pago.id_trabajo)" 
                    class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors"
                >
                    {{ isPaid ? 'Ver Trabajo' : 'Cancelar' }}
                </Link>
            </div>
            
            <div class="mt-4 text-center">
                <p class="text-xs text-gray-400">
                    Pago seguro procesado por Pago Fácil
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
