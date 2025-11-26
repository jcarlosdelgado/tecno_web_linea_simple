<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';

defineProps({
    status: Number,
});
</script>

<template>
    <Head title="Error del Servidor" />

    <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 flex items-center justify-center px-4">
        <div class="max-w-2xl w-full text-center">
            <!-- 500 Illustration -->
            <div class="mb-8">
                <svg class="mx-auto h-64 w-64 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <!-- Error Code -->
            <h1 class="text-9xl font-bold text-gray-600 mb-4">500</h1>
            
            <!-- Error Message -->
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                Error del Servidor
            </h2>
            
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
                Algo salió mal en nuestro servidor. Nuestro equipo ha sido notificado y estamos trabajando para solucionarlo.
            </p>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <Link :href="route('dashboard')">
                    <Button variant="primary" size="lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Ir al Inicio
                    </Button>
                </Link>
                
                <button @click="window.location.reload()" class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reintentar
                </button>
            </div>

            <!-- Help Text -->
            <div class="mt-12 p-6 bg-white rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">¿Qué puedes hacer?</h3>
                <ul class="text-sm text-gray-600 text-left max-w-md mx-auto space-y-2">
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Espera unos minutos y vuelve a intentarlo
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Limpia la caché de tu navegador
                    </li>
                    <li class="flex items-start">
                        <svg class="w-5 h-5 text-indigo-500 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Si el problema persiste, contacta al soporte técnico
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
