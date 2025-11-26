<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    roles: Array,
});

const eliminarRol = (roleId) => {
    if (!confirm('¿Estás seguro de eliminar este rol?')) return;
    
    router.delete(route('roles.destroy', roleId), {
        preserveScroll: true,
    });
};

const editarRol = (roleId) => {
    router.visit(route('roles.edit', roleId));
};

const crearRol = () => {
    router.visit(route('roles.create'));
};
</script>

<template>
    <Head title="Gestión de Roles" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestión de Roles y Permisos
                </h2>
                <Button variant="primary" @click="crearRol">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Crear Nuevo Rol
                </Button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Info Card -->
                <Card>
                    <div class="p-4 bg-blue-50 border-l-4 border-blue-500">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-blue-500 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-blue-800">Sobre Roles y Permisos</h3>
                                <p class="mt-1 text-sm text-blue-700">
                                    Los roles te permiten agrupar permisos y asignarlos a usuarios. Por ejemplo, puedes crear un rol "Diseñador" con permisos para ver y editar trabajos, pero sin acceso a inventario o pagos.
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Lista de Roles -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Card v-for="role in roles" :key="role.id_rol">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-gray-900 mb-1">
                                        {{ role.nombre }}
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        {{ role.descripcion || 'Sin descripción' }}
                                    </p>
                                </div>
                                <Badge :variant="role.activo ? 'success' : 'secondary'">
                                    {{ role.activo ? 'Activo' : 'Inactivo' }}
                                </Badge>
                            </div>

                            <div class="space-y-3 mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span class="font-medium">{{ role.permisos?.length || 0 }}</span>
                                    <span class="ml-1">{{ role.permisos?.length === 1 ? 'permiso' : 'permisos' }}</span>
                                </div>

                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span class="font-medium">{{ role.usuarios_count || 0 }}</span>
                                    <span class="ml-1">{{ role.usuarios_count === 1 ? 'usuario' : 'usuarios' }}</span>
                                </div>
                            </div>

                            <!-- Permisos Preview -->
                            <div v-if="role.permisos && role.permisos.length > 0" class="mb-4">
                                <p class="text-xs font-medium text-gray-500 mb-2">Permisos:</p>
                                <div class="flex flex-wrap gap-1">
                                    <span 
                                        v-for="(permiso, index) in role.permisos.slice(0, 3)" 
                                        :key="permiso.id_permiso"
                                        class="px-2 py-1 bg-indigo-100 text-indigo-700 text-xs rounded"
                                    >
                                        {{ permiso.modulo }}
                                    </span>
                                    <span 
                                        v-if="role.permisos.length > 3" 
                                        class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded"
                                    >
                                        +{{ role.permisos.length - 3 }} más
                                    </span>
                                </div>
                            </div>

                            <!-- Acciones -->
                            <div class="flex gap-2 pt-4 border-t border-gray-200">
                                <Button 
                                    variant="primary" 
                                    size="sm" 
                                    class="flex-1"
                                    @click="editarRol(role.id_rol)"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar
                                </Button>
                                <Button 
                                    variant="danger" 
                                    size="sm"
                                    @click="eliminarRol(role.id_rol)"
                                    :disabled="role.usuarios_count > 0"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </Button>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Empty State -->
                <Card v-if="!roles || roles.length === 0">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay roles</h3>
                        <p class="mt-1 text-sm text-gray-500">Comienza creando un nuevo rol.</p>
                        <div class="mt-6">
                            <Button variant="primary" @click="crearRol">
                                Crear Primer Rol
                            </Button>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
