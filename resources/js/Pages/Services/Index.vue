<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';

const props = defineProps({
    servicios: Array,
});

const getImageUrl = (imagen) => {
    if (!imagen) return 'https://via.placeholder.com/400x300?text=Sin+Imagen';
    return `/storage/${imagen}`;
};

const formatPrice = (price) => {
    if (!price) return 'Consultar precio';
    return `Bs ${parseFloat(price).toFixed(2)}`;
};
</script>

<template>
    <Head title="Nuestros Servicios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuestros Servicios
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Hero Section -->
                <div class="mb-12 text-center">
                    <h1 class="text-4xl font-bold text-gray-900 mb-4">
                        Servicios Profesionales de Tecnología
                    </h1>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Ofrecemos soluciones tecnológicas de alta calidad para satisfacer todas tus necesidades.
                    </p>
                </div>

                <!-- Services Grid -->
                <div v-if="servicios.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="servicio in servicios" :key="servicio.id_servicio" class="group">
                        <Card class="overflow-hidden h-full hover:shadow-xl transition-shadow duration-300">
                            <!-- Image -->
                            <div class="relative h-56 bg-gradient-to-br from-indigo-500 to-purple-600 overflow-hidden">
                                <img 
                                    :src="getImageUrl(servicio.imagen)" 
                                    :alt="servicio.nombre"
                                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                                />
                                <div v-if="servicio.categoria" class="absolute top-4 left-4">
                                    <span class="bg-white/90 backdrop-blur-sm text-indigo-600 text-xs font-semibold px-3 py-1 rounded-full">
                                        {{ servicio.categoria }}
                                    </span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-6 flex flex-col flex-1">
                                <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-indigo-600 transition-colors">
                                    {{ servicio.nombre }}
                                </h3>
                                
                                <p class="text-gray-600 mb-4 flex-1 line-clamp-3">
                                    {{ servicio.descripcion }}
                                </p>

                                <!-- Price -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    <div>
                                        <span class="text-sm text-gray-500">Desde</span>
                                        <p class="text-2xl font-bold text-indigo-600">
                                            {{ formatPrice(servicio.precio_base) }}
                                        </p>
                                    </div>
                                    
                                    <Link :href="route('trabajos.create', { servicio: servicio.id_servicio })">
                                        <Button variant="primary" size="sm">
                                            Solicitar
                                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </Card>
                    </div>
                </div>

                <!-- Empty State -->
                <Card v-else>
                    <div class="text-center py-16">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No hay servicios disponibles</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Actualmente no tenemos servicios publicados. Por favor, vuelve más tarde.
                        </p>
                    </div>
                </Card>

                <!-- CTA Section -->
                <div v-if="servicios.length > 0" class="mt-16 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 text-center text-white">
                    <h2 class="text-3xl font-bold mb-4">¿No encuentras lo que buscas?</h2>
                    <p class="text-lg mb-6 text-indigo-100">
                        Contáctanos y cuéntanos sobre tu proyecto. Podemos crear una solución personalizada para ti.
                    </p>
                    <Link :href="route('trabajos.create')">
                        <Button variant="secondary" size="lg">
                            Solicitar Servicio Personalizado
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
