<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    permisos: Object,
});

const form = useForm({
    nombre: '',
    descripcion: '',
    permisos: [],
});

const togglePermiso = (idPermiso) => {
    const index = form.permisos.indexOf(idPermiso);
    if (index > -1) {
        form.permisos.splice(index, 1);
    } else {
        form.permisos.push(idPermiso);
    }
};

const toggleModulo = (modulo) => {
    const permisosModulo = props.permisos[modulo].map(p => p.id_permiso);
    const todosSeleccionados = permisosModulo.every(id => form.permisos.includes(id));
    
    if (todosSeleccionados) {
        // Deseleccionar todos del módulo
        form.permisos = form.permisos.filter(id => !permisosModulo.includes(id));
    } else {
        // Seleccionar todos del módulo
        permisosModulo.forEach(id => {
            if (!form.permisos.includes(id)) {
                form.permisos.push(id);
            }
        });
    }
};

const moduloSeleccionado = (modulo) => {
    const permisosModulo = props.permisos[modulo].map(p => p.id_permiso);
    return permisosModulo.every(id => form.permisos.includes(id));
};

const submit = () => {
    form.post(route('roles.store'), {
        preserveScroll: true,
    });
};

const cancelar = () => {
    router.visit(route('roles.index'));
};
</script>

<template>
    <Head title="Crear Rol" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Nuevo Rol
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <form @submit.prevent="submit">
                    <!-- Información Básica -->
                    <Card class="mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Información Básica</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="nombre" value="Nombre del Rol *" />
                                    <TextInput
                                        id="nombre"
                                        v-model="form.nombre"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="Ej: Diseñador, Instalador, Contador"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.nombre" />
                                    <p class="mt-1 text-sm text-gray-500">
                                        El nombre debe ser descriptivo y único
                                    </p>
                                </div>

                                <div>
                                    <InputLabel for="descripcion" value="Descripción" />
                                    <textarea
                                        id="descripcion"
                                        v-model="form.descripcion"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        rows="3"
                                        placeholder="Breve descripción de las responsabilidades de este rol"
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.descripcion" />
                                </div>
                            </div>
                        </div>
                    </Card>

                    <!-- Permisos -->
                    <Card>
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Permisos del Rol</h3>
                            <p class="text-sm text-gray-600 mb-6">
                                Selecciona los permisos que tendrá este rol. Los usuarios con este rol podrán realizar únicamente las acciones seleccionadas.
                            </p>

                            <div class="space-y-6">
                                <div v-for="(permisosModulo, modulo) in permisos" :key="modulo" class="border border-gray-200 rounded-lg overflow-hidden">
                                    <!-- Header del Módulo -->
                                    <div class="bg-gray-50 px-4 py-3 flex items-center justify-between border-b border-gray-200">
                                        <div class="flex items-center">
                                            <input
                                                :id="`modulo-${modulo}`"
                                                type="checkbox"
                                                :checked="moduloSeleccionado(modulo)"
                                                @change="toggleModulo(modulo)"
                                                class="h-5 w-5 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            />
                                            <label :for="`modulo-${modulo}`" class="ml-3 font-semibold text-gray-900 capitalize cursor-pointer">
                                                {{ modulo }}
                                            </label>
                                        </div>
                                        <span class="text-sm text-gray-500">
                                            {{ permisosModulo.length }} {{ permisosModulo.length === 1 ? 'permiso' : 'permisos' }}
                                        </span>
                                    </div>

                                    <!-- Lista de Permisos -->
                                    <div class="p-4 bg-white">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div v-for="permiso in permisosModulo" :key="permiso.id_permiso" class="flex items-start">
                                                <input
                                                    :id="`permiso-${permiso.id_permiso}`"
                                                    type="checkbox"
                                                    :value="permiso.id_permiso"
                                                    :checked="form.permisos.includes(permiso.id_permiso)"
                                                    @change="togglePermiso(permiso.id_permiso)"
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-0.5"
                                                />
                                                <label :for="`permiso-${permiso.id_permiso}`" class="ml-3 text-sm cursor-pointer">
                                                    <span class="font-medium text-gray-900">{{ permiso.nombre.replace(/_/g, ' ') }}</span>
                                                    <p class="text-gray-500 text-xs">{{ permiso.descripcion }}</p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <InputError class="mt-2" :message="form.errors.permisos" />

                            <!-- Resumen de selección -->
                            <div v-if="form.permisos.length > 0" class="mt-6 p-4 bg-indigo-50 rounded-lg">
                                <p class="text-sm font-medium text-indigo-900">
                                    ✓ {{ form.permisos.length }} {{ form.permisos.length === 1 ? 'permiso seleccionado' : 'permisos seleccionados' }}
                                </p>
                            </div>
                        </div>
                    </Card>

                    <!-- Acciones -->
                    <div class="flex justify-end gap-3 mt-6">
                        <Button type="button" variant="secondary" @click="cancelar">
                            Cancelar
                        </Button>
                        <Button type="submit" variant="primary" :disabled="form.processing">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ form.processing ? 'Creando...' : 'Crear Rol' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
