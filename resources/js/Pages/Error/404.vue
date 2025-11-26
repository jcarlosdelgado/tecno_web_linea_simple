<script setup>
import { Head, Link } from '@inertiajs/vue3';
import Button from '@/Components/UI/Button.vue';

defineProps({
    status: Number,
});
</script>

<template>
    <Head title="Página no encontrada" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center px-4">
        <div class="max-w-2xl w-full text-center">
            <!-- 404 Illustration -->
            <div class="mb-8">
                <svg class="mx-auto h-64 w-64 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="0.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <!-- Error Code -->
            <h1 class="text-9xl font-bold text-indigo-600 mb-4">404</h1>
            
            <!-- Error Message -->
            <h2 class="text-3xl font-bold text-gray-900 mb-4">
                ¡Oops! Página no encontrada
            </h2>
            
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
                La página que estás buscando no existe o ha sido movida. Verifica la URL o regresa al inicio.
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
                
                <button @click="$inertia.visit(window.history.back())" class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver Atrás
                </button>
            </div>

            <!-- Help Text -->
            <div class="mt-12 p-6 bg-white rounded-lg shadow-sm border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900 mb-2">¿Necesitas ayuda?</h3>
                <p class="text-sm text-gray-600">
                    Si crees que esto es un error, por favor contacta al administrador del sistema.
                </p>
            </div>
        </div>
    </div>
</template>
