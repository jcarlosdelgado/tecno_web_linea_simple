<template>
    <Head title="Editar Usuario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Usuario
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <form @submit.prevent="submitForm" class="space-y-6">
                        <!-- Role (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Rol
                            </label>
                            <input
                                :value="usuario.rol"
                                type="text"
                                disabled
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                            />
                            <p class="mt-1 text-sm text-gray-500">El rol no se puede cambiar</p>
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

                        <!-- Password (Optional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Nueva Contraseña (opcional)
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                minlength="8"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                :class="{ 'border-red-500': form.errors.password }"
                            />
                            <p class="mt-1 text-sm text-gray-500">Dejar en blanco para mantener la contraseña actual</p>
                            <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- Role Selection -->
                        <div class="border-2 border-indigo-200 rounded-lg p-4 bg-indigo-50">
                            <label class="block text-sm font-semibold text-indigo-900 mb-2">
                                Rol del Usuario *
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
                                Define qué puede hacer este usuario en el sistema
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

                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-4">
                            <Link :href="route('admin.usuarios.index')">
                                <Button variant="ghost">
                                    Cancelar
                                </Button>
                            </Link>
                            <Button type="submit" variant="primary" :disabled="form.processing">
                                {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
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

const props = defineProps({
    usuario: Object,
    roles: Array,
});

const form = useForm({
    nombre: props.usuario.nombre,
    email: props.usuario.email,
    password: '',
    telefono: props.usuario.telefono || '',
    direccion: props.usuario.direccion || '',
    id_rol: props.usuario.id_rol || '',
});

const submitForm = () => {
    form.put(route('admin.usuarios.update', props.usuario.id_usuario));
};
</script>
