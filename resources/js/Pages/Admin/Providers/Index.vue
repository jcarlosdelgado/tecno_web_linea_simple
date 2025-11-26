<template>
    <Head title="Gestión de Proveedores" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Proveedores
                </h2>
                <Link :href="route('admin.proveedores.create')">
                    <Button variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Crear Proveedor
                    </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <template #header>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <input
                                v-model="searchForm.search"
                                @input="search"
                                type="text"
                                placeholder="Buscar por nombre..."
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <select
                                v-model="searchForm.activo"
                                @change="search"
                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Todos los estados</option>
                                <option value="true">Activos</option>
                                <option value="false">Inactivos</option>
                            </select>
                        </div>
                    </template>

                    <div v-if="proveedores.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay proveedores</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza creando un nuevo proveedor.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contacto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Teléfono</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="proveedor in proveedores.data" :key="proveedor.id_proveedor" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ proveedor.nombre }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ proveedor.contacto || 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ proveedor.telefono || 'N/A' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ proveedor.email || 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="proveedor.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ proveedor.activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <Link :href="route('admin.proveedores.edit', proveedor.id_proveedor)">
                                            <Button variant="ghost" size="sm">Editar</Button>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="px-6 py-4 border-t">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Mostrando {{ proveedores.from }} a {{ proveedores.to }} de {{ proveedores.total }}
                                </div>
                                <div class="flex space-x-2">
                                    <Link v-if="proveedores.prev_page_url" :href="proveedores.prev_page_url" preserve-state>
                                        <Button variant="ghost" size="sm">Anterior</Button>
                                    </Link>
                                    <Link v-if="proveedores.next_page_url" :href="proveedores.next_page_url" preserve-state>
                                        <Button variant="ghost" size="sm">Siguiente</Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    proveedores: Object,
    filters: Object,
});

const searchForm = ref({
    search: props.filters?.search || '',
    activo: props.filters?.activo || '',
});

const search = () => {
    router.get(route('admin.proveedores.index'), searchForm.value, {
        preserveState: true,
        replace: true,
    });
};
</script>
