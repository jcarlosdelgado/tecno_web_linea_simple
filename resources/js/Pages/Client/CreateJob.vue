<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Alert from '@/Components/UI/Alert.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    servicios: Array,
    servicioSeleccionado: Object,
});

const form = useForm({
    id_servicio: props.servicioSeleccionado?.id_servicio || null,
    titulo: props.servicioSeleccionado?.nombre || '',
    descripcion: '',
    imagenes: [],
});

const imagenesPreview = ref([]);

const handleImageUpload = (event) => {
    const files = Array.from(event.target.files);
    
    // Limit to 5 images
    if (files.length + form.imagenes.length > 5) {
        alert('Máximo 5 imágenes permitidas');
        return;
    }
    
    // Add files to form
    form.imagenes = [...form.imagenes, ...files];
    
    // Create previews
    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagenesPreview.value.push({
                url: e.target.result,
                name: file.name
            });
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    form.imagenes.splice(index, 1);
    imagenesPreview.value.splice(index, 1);
};

const submit = () => {
    form.post(route('trabajos.store'), {
        onSuccess: () => {
            form.reset();
            imagenesPreview.value = [];
        },
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Solicitar Trabajo" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link :href="route('dashboard')">
                    <Button variant="ghost" size="sm">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Volver
                    </Button>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Solicitar Nuevo Trabajo
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Selected Service Info -->
                <Card v-if="servicioSeleccionado">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-lg flex items-center justify-center text-3xl">
                            {{ servicioSeleccionado.categoria === 'Publicidad' ? '📢' : 
                               servicioSeleccionado.categoria === 'Decoración' ? '🎨' : 
                               servicioSeleccionado.categoria === 'Diseño de Interiores' ? '🏠' : '✨' }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">{{ servicioSeleccionado.nombre }}</h3>
                                <Badge variant="info" size="sm">{{ servicioSeleccionado.categoria }}</Badge>
                            </div>
                            <p class="text-sm text-gray-600">{{ servicioSeleccionado.descripcion }}</p>
                            <p v-if="servicioSeleccionado.precio_base" class="mt-2 text-sm text-gray-500">
                                Precio base: <span class="font-semibold text-indigo-600">Bs {{ servicioSeleccionado.precio_base }}</span>
                            </p>
                        </div>
                    </div>
                </Card>

                <!-- Main Form -->
                <Card>
                    <template #header>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Detalles del Trabajo</h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Completa la información sobre el trabajo que necesitas.
                            </p>
                        </div>
                    </template>

                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Service Selector (if not preselected) -->
                        <div v-if="!servicioSeleccionado">
                            <label for="servicio" class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Servicio (Opcional)
                            </label>
                            <select
                                id="servicio"
                                v-model="form.id_servicio"
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                                <option :value="null">Seleccionar servicio...</option>
                                <option v-for="servicio in servicios" :key="servicio.id_servicio" :value="servicio.id_servicio">
                                    {{ servicio.nombre }} - {{ servicio.categoria }}
                                </option>
                            </select>
                        </div>

                        <!-- Title -->
                        <div>
                            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                                Título del Trabajo <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="titulo"
                                v-model="form.titulo"
                                type="text"
                                required
                                placeholder="Ej: Banner publicitario para evento"
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                :class="{ 'border-red-500': form.errors.titulo }"
                            />
                            <p v-if="form.errors.titulo" class="mt-2 text-sm text-red-600">
                                {{ form.errors.titulo }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción Detallada <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="descripcion"
                                v-model="form.descripcion"
                                required
                                rows="6"
                                placeholder="Describe con detalle: medidas, colores, materiales preferidos, fecha deseada, etc."
                                class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                                :class="{ 'border-red-500': form.errors.descripcion }"
                            ></textarea>
                            <p v-if="form.errors.descripcion" class="mt-2 text-sm text-red-600">
                                {{ form.errors.descripcion }}
                            </p>
                        </div>

                        <!-- Image Upload -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Imágenes de Referencia (Opcional)
                            </label>
                            <div class="space-y-4">
                                <!-- Upload Button -->
                                <div class="flex items-center justify-center w-full">
                                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            <p class="mb-2 text-sm text-gray-500">
                                                <span class="font-semibold">Click para subir</span> o arrastra archivos
                                            </p>
                                            <p class="text-xs text-gray-500">PNG, JPG, WEBP (Máx. 5MB, hasta 5 imágenes)</p>
                                        </div>
                                        <input
                                            type="file"
                                            class="hidden"
                                            accept="image/*"
                                            multiple
                                            @change="handleImageUpload"
                                            :disabled="form.imagenes.length >= 5"
                                        />
                                    </label>
                                </div>

                                <!-- Image Previews -->
                                <div v-if="imagenesPreview.length > 0" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    <div
                                        v-for="(preview, index) in imagenesPreview"
                                        :key="index"
                                        class="relative group"
                                    >
                                        <img
                                            :src="preview.url"
                                            :alt="preview.name"
                                            class="w-full h-32 object-cover rounded-lg border-2 border-gray-200"
                                        />
                                        <button
                                            type="button"
                                            @click="removeImage(index)"
                                            class="absolute top-2 right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        <p class="mt-1 text-xs text-gray-500 truncate">{{ preview.name }}</p>
                                    </div>
                                </div>

                                <p v-if="form.errors.imagenes" class="text-sm text-red-600">
                                    {{ form.errors.imagenes }}
                                </p>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        <Alert type="info" title="¿Qué sucede después?">
                            <ul class="list-disc list-inside space-y-1 text-sm">
                                <li>Recibirás una confirmación de tu solicitud</li>
                                <li>Nuestro equipo revisará los detalles</li>
                                <li>Te enviaremos un presupuesto detallado</li>
                                <li>Podrás aprobar o rechazar el presupuesto</li>
                            </ul>
                        </Alert>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                            <Link :href="route('dashboard')">
                                <Button type="button" variant="ghost">
                                    Cancelar
                                </Button>
                            </Link>
                            <Button 
                                type="submit" 
                                variant="primary" 
                                :loading="form.processing"
                                :disabled="form.processing"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                {{ form.processing ? 'Enviando...' : 'Enviar Solicitud' }}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
