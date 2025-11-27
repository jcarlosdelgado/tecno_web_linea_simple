<template>
    <Head title="Gestión de Usuarios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Usuarios
                </h2>
                <Link :href="route('admin.usuarios.create')">
                    <Button variant="primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Crear Usuario
                    </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <template #header>
                        <!-- Filters -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Search -->
                                <div>
                                    <input
                                        v-model="searchForm.search"
                                        @input="search"
                                        type="text"
                                        placeholder="Buscar por nombre o email..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>

                                <!-- Role Filter -->
                                <div>
                                    <select
                                        v-model="searchForm.rol"
                                        @change="search"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Todos los roles</option>
                                        <option value="CLIENTE">Clientes</option>
                                        <option value="TRABAJADOR">Trabajadores</option>
                                    </select>
                                </div>

                                <!-- Clear Filters -->
                                <div>
                                    <Button variant="ghost" @click="clearFilters" class="w-full">
                                        Limpiar Filtros
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Users Table -->
                    <div v-if="usuarios.data.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay usuarios</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza creando un nuevo usuario.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Usuario
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rol
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Contacto
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha Registro
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="usuario in usuarios.data" :key="usuario.id_usuario" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <span class="text-indigo-600 font-medium">
                                                    {{ usuario.nombre.charAt(0).toUpperCase() }}
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ usuario.nombre }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ usuario.email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            <span v-if="usuario.role_custom || usuario.roleCustom" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                {{ (usuario.role_custom || usuario.roleCustom).nombre }}
                                            </span>
                                            <span v-else-if="usuario.rol === 'CLIENTE'" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Cliente
                                            </span>
                                            <span v-else-if="usuario.rol === 'PROPIETARIO'" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                Propietario
                                            </span>
                                            <span v-else class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Sin rol
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ usuario.telefono || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(usuario.creado_en) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <Link :href="route('admin.usuarios.edit', usuario.id_usuario)">
                                            <Button variant="ghost" size="sm">
                                                Editar
                                            </Button>
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="px-6 py-4 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Mostrando {{ usuarios.from }} a {{ usuarios.to }} de {{ usuarios.total }} usuarios
                                </div>
                                <div class="flex space-x-2">
                                    <Link v-if="usuarios.prev_page_url" :href="usuarios.prev_page_url" preserve-state>
                                        <Button variant="ghost" size="sm">Anterior</Button>
                                    </Link>
                                    <Link v-if="usuarios.next_page_url" :href="usuarios.next_page_url" preserve-state>
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
    usuarios: Object,
    filters: Object,
});

const searchForm = ref({
    search: props.filters?.search || '',
    rol: props.filters?.rol || '',
});

const search = () => {
    router.get(route('admin.usuarios.index'), searchForm.value, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchForm.value = { search: '', rol: '' };
    search();
};

const getRoleBadgeClass = (rol) => {
    return rol === 'CLIENTE' 
        ? 'bg-blue-100 text-blue-800' 
        : 'bg-green-100 text-green-800';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>
