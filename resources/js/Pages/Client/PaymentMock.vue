<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    pago: Object,
    amount: Number,
    callbackUrl: String,
});

const form = useForm({
    pago_id: props.pago.id_pago,
    status: '',
});

const submitPayment = (status) => {
    form.status = status;
    form.post(props.callbackUrl);
};
</script>

<template>
    <Head title="PagoFácil Mock" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg text-center">
            <h2 class="text-2xl font-bold mb-4 text-blue-600">PagoFácil (Simulación)</h2>
            <p class="mb-4">Estás a punto de pagar: <span class="font-bold">{{ new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(amount) }}</span></p>
            
            <div class="space-y-4">
                <button @click="submitPayment('success')" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Simular Pago Exitoso
                </button>
                
                <button @click="submitPayment('failure')" class="w-full bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Simular Pago Fallido
                </button>
            </div>
        </div>
    </div>
</template>
