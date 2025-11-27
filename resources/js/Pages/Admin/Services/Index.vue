<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    servicios: Array,
});

const getImageUrl = (imagen) => {
    if (!imagen) return '/images/placeholder-service.jpg';
    return `/storage/${imagen}`;
};

const confirmDelete = (servicioId) => {
    if (confirm('¿Estás seguro de eliminar este servicio?')) {
        router.delete(route('admin.servicios.destroy', servicioId));
    }
};
</script>

<template>
    <Head title="Gestión de Servicios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Servicios
                </h2>
                <Link :href="route('admin.servicios.create')">
                    <Button variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Servicio
                    </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card v-if="servicios.length === 0">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay servicios</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza creando un nuevo servicio.</p>
                        <div class="mt-6">
                            <Link :href="route('admin.servicios.create')">
                                <Button variant="primary">
                                    Crear Servicio
                                </Button>
                            </Link>
                        </div>
                    </div>
                </Card>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card v-for="servicio in servicios" :key="servicio.id_servicio" class="overflow-hidden">
                        <!-- Image -->
                        <div class="relative h-48 bg-gray-200 overflow-hidden">
                            <img 
                                :src="getImageUrl(servicio.imagen)" 
                                :alt="servicio.nombre"
                                class="w-full h-full object-cover"
                            />
                            <div class="absolute top-2 right-2">
                                <Badge :variant="servicio.activo ? 'success' : 'danger'">
                                    {{ servicio.activo ? 'Activo' : 'Inactivo' }}
                                </Badge>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">{{ servicio.nombre }}</h3>
                                <span v-if="servicio.categoria" class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                    {{ servicio.categoria }}
                                </span>
                            </div>
                            
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ servicio.descripcion }}</p>
                            
                            <div v-if="servicio.precio_base" class="mb-4">
                                <span class="text-sm text-gray-500">Precio base:</span>
                                <span class="text-lg font-bold text-indigo-600 ml-2">
                                    Bs {{ parseFloat(servicio.precio_base).toFixed(2) }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2">
                                <Link :href="route('admin.servicios.edit', servicio.id_servicio)" class="flex-1">
                                    <Button variant="secondary" size="sm" class="w-full">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Editar
                                    </Button>
                                </Link>
                                
                                <Button 
                                    variant="danger" 
                                    size="sm" 
                                    class="flex-1"
                                    @click="confirmDelete(servicio.id_servicio)"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Eliminar
                                </Button>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
