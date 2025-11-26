<template>
    <Head title="Crear Usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Usuario
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Role Selection -->
                        <div class="border-2 border-indigo-200 rounded-lg p-4 bg-indigo-50">
                            <label class="block text-sm font-semibold text-indigo-900 mb-2">
                                Rol del Empleado *
                            </label>
                            <select
                                v-model="form.id_rol"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.id_rol }"
                            >
                                <option value="">Selecciona un rol...</option>
                                <option v-for="role in roles" :key="role.id_rol" :value="role.id_rol">
                                    {{ role.nombre }} - {{ role.permisos?.length || 0 }} permisos
                                </option>
                            </select>
                            <p v-if="form.errors.id_rol" class="mt-1 text-sm text-red-600">
                                {{ form.errors.id_rol }}
                            </p>
                            <p class="mt-2 text-sm text-indigo-700">
                                <strong>Información:</strong><br>
                                Este usuario será un <strong>empleado</strong> del sistema con los permisos que defina el rol seleccionado.
                            </p>
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Nombre Completo *
                            </label>
                            <input
                                v-model="form.nombre"
                                type="text"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.nombre }"
                            />
                            <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">
                                {{ form.errors.nombre }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Correo Electrónico *
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.email }"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Contraseña *
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                required
                                minlength="8"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.password }"
                            />
                            <p class="mt-1 text-sm text-gray-500">Mínimo 8 caracteres</p>
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Teléfono
                            </label>
                            <input
                                v-model="form.telefono"
                                type="tel"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Address -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Dirección
                            </label>
                            <textarea
                                v-model="form.direccion"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                        </div>

                        <!-- Permisos Personalizados -->
                        <div v-if="rolSeleccionado && rolSeleccionado.permisos" class="border-2 border-gray-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">
                                Personalizar Permisos para {{ rolSeleccionado.nombre }}
                            </h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Marca o desmarca los permisos específicos que tendrá este empleado
                            </p>
                            
                            <div class="space-y-4">
                                <div v-for="(permisosModulo, modulo) in agruparPermisosPorModulo(rolSeleccionado.permisos)" :key="modulo" 
                                     class="border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                                        <h4 class="font-medium text-gray-900 capitalize">{{ modulo }}</h4>
                                    </div>
                                    <div class="p-4 bg-white grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div v-for="permiso in permisosModulo" :key="permiso.id_permiso" class="flex items-start">
                                            <input
                                                :id="`permiso-${permiso.id_permiso}`"
                                                type="checkbox"
                                                :checked="form.permisos_personalizados.includes(permiso.id_permiso)"
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
                            
                            <div class="mt-4 p-3 bg-green-50 rounded-lg">
                                <p class="text-sm font-medium text-green-900">
                                    ✓ {{ form.permisos_personalizados.length }} permisos seleccionados
                                </p>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-4">
                            <Link :href="route('admin.usuarios.index')">
                                <Button variant="ghost">
                                    Cancelar
                                </Button>
                            </Link>
                            <Button type="submit" variant="primary" :disabled="form.processing">
                                {{ form.processing ? 'Creando...' : 'Crear Usuario' }}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    roles: Array,
});

const form = useForm({
    nombre: '',
    email: '',
    password: '',
    telefono: '',
    direccion: '',
    id_rol: '',
    permisos_personalizados: [], // Array de IDs de permisos
});

// Obtener el rol seleccionado
const rolSeleccionado = computed(() => {
    return props.roles?.find(r => r.id_rol === form.id_rol);
});

// Cargar permisos del rol cuando se selecciona
watch(() => form.id_rol, (newRol) => {
    if (newRol && rolSeleccionado.value) {
        // Cargar los permisos del rol como base
        form.permisos_personalizados = rolSeleccionado.value.permisos.map(p => p.id_permiso);
    } else {
        form.permisos_personalizados = [];
    }
});

// Toggle permiso individual
const togglePermiso = (idPermiso) => {
    const index = form.permisos_personalizados.indexOf(idPermiso);
    if (index > -1) {
        form.permisos_personalizados.splice(index, 1);
    } else {
        form.permisos_personalizados.push(idPermiso);
    }
};

// Agrupar permisos por módulo
const agruparPermisosPorModulo = (permisos) => {
    return permisos.reduce((acc, permiso) => {
        if (!acc[permiso.modulo]) {
            acc[permiso.modulo] = [];
        }
        acc[permiso.modulo].push(permiso);
        return acc;
    }, {});
};

const submitForm = () => {
    form.post(route('admin.usuarios.store'));
};

</script>
