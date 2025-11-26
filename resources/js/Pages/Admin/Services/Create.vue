<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Alert from '@/Components/UI/Alert.vue';

const imagePreview = ref(null);

const form = useForm({
    nombre: '',
    descripcion: '',
    precio_base: '',
    categoria: '',
    imagen: null,
    activo: true,
    orden: 0,
});

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.imagen = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    form.post(route('admin.servicios.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            imagePreview.value = null;
        },
    });
};
</script>

<template>
    <Head title="Crear Servicio" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear Nuevo Servicio
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">
                                Nombre del Servicio *
                            </label>
                            <input
                                id="nombre"
                                v-model="form.nombre"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Ej: Reparación de Computadoras"
                            />
                            <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">{{ form.errors.nombre }}</p>
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción *
                            </label>
                            <textarea
                                id="descripcion"
                                v-model="form.descripcion"
                                required
                                rows="4"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Describe el servicio que ofreces..."
                            ></textarea>
                            <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
                        </div>

                        <!-- Precio Base y Categoría -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="precio_base" class="block text-sm font-medium text-gray-700 mb-2">
                                    Precio Base (Bs)
                                </label>
                                <input
                                    id="precio_base"
                                    v-model="form.precio_base"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="0.00"
                                />
                                <p v-if="form.errors.precio_base" class="mt-1 text-sm text-red-600">{{ form.errors.precio_base }}</p>
                            </div>

                            <div>
                                <label for="categoria" class="block text-sm font-medium text-gray-700 mb-2">
                                    Categoría
                                </label>
                                <input
                                    id="categoria"
                                    v-model="form.categoria"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                    placeholder="Ej: Reparación, Mantenimiento"
                                />
                                <p v-if="form.errors.categoria" class="mt-1 text-sm text-red-600">{{ form.errors.categoria }}</p>
                            </div>
                        </div>

                        <!-- Imagen -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Imagen del Servicio
                            </label>
                            <div class="mt-1 flex items-center gap-4">
                                <div class="flex-1">
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="handleImageChange"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">PNG, JPG, WEBP hasta 2MB</p>
                                </div>
                                <div v-if="imagePreview" class="flex-shrink-0">
                                    <img :src="imagePreview" alt="Preview" class="h-20 w-20 object-cover rounded-lg border-2 border-gray-200" />
                                </div>
                            </div>
                            <p v-if="form.errors.imagen" class="mt-1 text-sm text-red-600">{{ form.errors.imagen }}</p>
                        </div>

                        <!-- Orden y Estado -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="orden" class="block text-sm font-medium text-gray-700 mb-2">
                                    Orden de Visualización
                                </label>
                                <input
                                    id="orden"
                                    v-model="form.orden"
                                    type="number"
                                    min="0"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                />
                                <p class="mt-1 text-xs text-gray-500">Menor número aparece primero</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Estado
                                </label>
                                <div class="flex items-center h-full">
                                    <label class="flex items-center cursor-pointer">
                                        <input
                                            v-model="form.activo"
                                            type="checkbox"
                                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-700">Servicio activo (visible para clientes)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Alert -->
                        <Alert type="info" title="Información">
                            Los servicios activos serán visibles en el catálogo público para que los clientes puedan solicitarlos.
                        </Alert>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-4 pt-4 border-t">
                            <a :href="route('admin.servicios.index')" class="text-gray-600 hover:text-gray-800">
                                Cancelar
                            </a>
                            <Button 
                                type="submit" 
                                variant="primary"
                                :disabled="form.processing"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Creando...' : 'Crear Servicio' }}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
