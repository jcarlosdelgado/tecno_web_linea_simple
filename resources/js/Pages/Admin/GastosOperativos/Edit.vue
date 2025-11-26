<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    gasto: Object,
});

const form = useForm({
    categoria: props.gasto.categoria,
    descripcion: props.gasto.descripcion,
    monto: props.gasto.monto,
    fecha: props.gasto.fecha,
    comprobante: null,
});

const categoriasPredefinidas = [
    'Transporte',
    'Alimentación',
    'Servicios Básicos',
    'Mantenimiento',
    'Publicidad',
    'Suministros de Oficina',
    'Salarios',
    'Impuestos',
    'Otros',
];

const handleFileChange = (event) => {
    form.comprobante = event.target.files[0];
};

const submit = () => {
    form.post(route('admin.gastos-operativos.update', props.gasto.id_gasto_operativo), {
        _method: 'PUT',
    });
};

const cancelar = () => {
    router.visit(route('admin.gastos-operativos.index'));
};

const verComprobante = () => {
    if (props.gasto.comprobante) {
        window.open(`/storage/${props.gasto.comprobante}`, '_blank');
    }
};
</script>

<template>
    <Head title="Editar Gasto Operativo" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Gasto Operativo
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <Card>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Categoría -->
                        <div>
                            <label for="categoria" class="block text-sm font-medium text-gray-700 mb-2">
                                Categoría <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="categoria"
                                v-model="form.categoria"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            >
                                <option value="">Seleccione una categoría</option>
                                <option v-for="cat in categoriasPredefinidas" :key="cat" :value="cat">
                                    {{ cat }}
                                </option>
                            </select>
                            <InputError :message="form.errors.categoria" class="mt-2" />
                        </div>

                        <!-- Descripción -->
                        <div>
                            <label for="descripcion" class="block text-sm font-medium text-gray-700 mb-2">
                                Descripción <span class="text-red-500">*</span>
                            </label>
                            <textarea 
                                id="descripcion"
                                v-model="form.descripcion"
                                rows="3"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <InputError :message="form.errors.descripcion" class="mt-2" />
                        </div>

                        <!-- Monto y Fecha -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="monto" class="block text-sm font-medium text-gray-700 mb-2">
                                    Monto (Bs) <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    id="monto"
                                    v-model="form.monto"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                                <InputError :message="form.errors.monto" class="mt-2" />
                            </div>

                            <div>
                                <label for="fecha" class="block text-sm font-medium text-gray-700 mb-2">
                                    Fecha <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    id="fecha"
                                    v-model="form.fecha"
                                    type="date"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                                <InputError :message="form.errors.fecha" class="mt-2" />
                            </div>
                        </div>

                        <!-- Comprobante Actual -->
                        <div v-if="gasto.comprobante" class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Comprobante actual</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ gasto.comprobante.split('/').pop() }}</p>
                                </div>
                                <Button 
                                    type="button"
                                    variant="secondary"
                                    size="sm"
                                    @click="verComprobante"
                                >
                                    Ver
                                </Button>
                            </div>
                        </div>

                        <!-- Nuevo Comprobante -->
                        <div>
                            <label for="comprobante" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ gasto.comprobante ? 'Cambiar Comprobante (opcional)' : 'Agregar Comprobante (opcional)' }}
                            </label>
                            <input 
                                id="comprobante"
                                type="file"
                                accept=".jpg,.jpeg,.png,.pdf"
                                @change="handleFileChange"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                            />
                            <p class="mt-2 text-xs text-gray-500">
                                Formatos permitidos: JPG, PNG, PDF. Tamaño máximo: 2MB
                            </p>
                            <InputError :message="form.errors.comprobante" class="mt-2" />
                        </div>

                        <!-- Información adicional -->
                        <div class="p-4 bg-blue-50 rounded-lg">
                            <p class="text-sm text-gray-700">
                                <span class="font-medium">Registrado por:</span> {{ gasto.registrador?.name || 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-700 mt-1">
                                <span class="font-medium">Fecha de registro:</span> {{ new Date(gasto.created_at).toLocaleString('es-BO') }}
                            </p>
                        </div>

                        <!-- Botones -->
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                            <Button 
                                type="button"
                                variant="secondary"
                                @click="cancelar"
                                :disabled="form.processing"
                            >
                                Cancelar
                            </Button>
                            <Button 
                                type="submit"
                                variant="primary"
                                :disabled="form.processing"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Guardando...' : 'Actualizar Gasto' }}
                            </Button>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
