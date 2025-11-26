<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    materiales: Array,
    proveedores: Array,
});

const showCreateModal = ref(false);
const showProviderModal = ref(false);
const editingMaterial = ref(null);
const selectedMaterial = ref(null);

const form = useForm({
    nombre: '',
    descripcion: '',
    unidad_medida: '',
    stock_actual: 0,
    stock_minimo: 0,
    id_proveedor: '',
    precio_unitario: 0,
});

const providerForm = useForm({
    id_proveedor: '',
    precio_unitario: 0,
    es_principal: false,
});

const openCreateModal = () => {
    editingMaterial.value = null;
    form.reset();
    showCreateModal.value = true;
};

const openEditModal = (material) => {
    editingMaterial.value = material;
    form.nombre = material.nombre;
    form.descripcion = material.descripcion;
    form.unidad_medida = material.unidad_medida;
    form.stock_actual = material.stock_actual;
    form.stock_minimo = material.stock_minimo;
    showCreateModal.value = true;
};

const openProviderModal = (material) => {
    selectedMaterial.value = material;
    providerForm.reset();
    showProviderModal.value = true;
};

const closeModal = () => {
    showCreateModal.value = false;
    form.reset();
    editingMaterial.value = null;
};

const closeProviderModal = () => {
    showProviderModal.value = false;
    providerForm.reset();
    selectedMaterial.value = null;
};

const submit = () => {
    if (editingMaterial.value) {
        form.put(route('admin.materiales.update', editingMaterial.value.id_material), {
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.materiales.store'), {
            onSuccess: () => closeModal(),
        });
    }
};

const addProvider = () => {
    providerForm.post(route('admin.materiales.attach-provider', selectedMaterial.value.id_material), {
        onSuccess: () => closeProviderModal(),
    });
};

const removeProvider = (materialId, proveedorId) => {
    if (confirm('¿Remover este proveedor del material?')) {
        useForm({}).delete(route('admin.materiales.detach-provider', [materialId, proveedorId]));
    }
};

const deleteMaterial = (id) => {
    if (confirm('¿Estás seguro de eliminar este material?')) {
        useForm({}).delete(route('admin.materiales.destroy', id));
    }
};

const getProveedorPrincipal = (material) => {
    return material.proveedores?.find(p => p.pivot.es_principal);
};
</script>

<template>
    <Head title="Inventario" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Inventario</h2>
                <PrimaryButton @click="openCreateModal">Nuevo Material</PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <table class="min-w-full leading-normal">
                            <thead>
                                <tr>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Nombre</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Unidad</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Stock</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Proveedor Principal</th>
                                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="material in materiales" :key="material.id_material" class="hover:bg-gray-50">
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <div class="font-medium text-gray-900">{{ material.nombre }}</div>
                                        <div v-if="material.descripcion" class="text-xs text-gray-500">{{ material.descripcion }}</div>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">{{ material.unidad_medida }}</td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm" :class="{'text-red-600 font-bold': material.stock_actual <= material.stock_minimo}">
                                        {{ material.stock_actual }} / {{ material.stock_minimo }}
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <div v-if="getProveedorPrincipal(material)">
                                            <div class="font-medium">{{ getProveedorPrincipal(material).nombre }}</div>
                                            <div class="text-xs text-gray-500">Bs. {{ getProveedorPrincipal(material).pivot.precio_unitario }}</div>
                                        </div>
                                        <span v-else class="text-gray-400">Sin proveedor</span>
                                    </td>
                                    <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                                        <button @click="openProviderModal(material)" class="text-green-600 hover:text-green-900 mr-3">Proveedores ({{ material.proveedores?.length || 0 }})</button>
                                        <button @click="openEditModal(material)" class="text-blue-600 hover:text-blue-900 mr-3">Editar</button>
                                        <button @click="deleteMaterial(material.id_material)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Material Modal -->
        <Modal :show="showCreateModal" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    {{ editingMaterial ? 'Editar Material' : 'Nuevo Material' }}
                </h2>

                <div class="mt-6">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <InputLabel for="nombre" value="Nombre" />
                            <TextInput id="nombre" type="text" class="mt-1 block w-full" v-model="form.nombre" required />
                        </div>
                        <div>
                            <InputLabel for="unidad" value="Unidad de Medida" />
                            <TextInput id="unidad" type="text" class="mt-1 block w-full" v-model="form.unidad_medida" required />
                        </div>
                        <div>
                            <InputLabel for="stock" value="Stock Actual" />
                            <TextInput id="stock" type="number" step="0.01" class="mt-1 block w-full" v-model="form.stock_actual" required />
                        </div>
                        <div>
                            <InputLabel for="minimo" value="Stock Mínimo" />
                            <TextInput id="minimo" type="number" step="0.01" class="mt-1 block w-full" v-model="form.stock_minimo" required />
                        </div>
                    </div>

                    <!-- Provider Selection (Only for new materials) -->
                    <div v-if="!editingMaterial" class="mt-6 border-t pt-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Proveedor Inicial (Opcional)</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <InputLabel for="proveedor_inicial" value="Proveedor" />
                                <select id="proveedor_inicial" v-model="form.id_proveedor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Seleccionar proveedor</option>
                                    <option v-for="prov in proveedores" :key="prov.id_proveedor" :value="prov.id_proveedor">
                                        {{ prov.nombre }}
                                    </option>
                                </select>
                            </div>
                            <div v-if="form.id_proveedor">
                                <InputLabel for="precio_inicial" value="Precio de Compra (Bs.)" />
                                <TextInput id="precio_inicial" type="number" step="0.01" class="mt-1 block w-full" v-model="form.precio_unitario" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                    <PrimaryButton class="ms-3" @click="submit" :disabled="form.processing">
                        {{ editingMaterial ? 'Actualizar' : 'Guardar' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Provider Management Modal -->
        <Modal :show="showProviderModal" @close="closeProviderModal" max-width="3xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    Proveedores de: {{ selectedMaterial?.nombre }}
                </h2>

                <!-- Current Providers -->
                <div v-if="selectedMaterial?.proveedores?.length > 0" class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Proveedores Actuales</h3>
                    <div class="space-y-2">
                        <div v-for="proveedor in selectedMaterial.proveedores" :key="proveedor.id_proveedor" 
                             class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ proveedor.nombre }}</span>
                                    <span v-if="proveedor.pivot.es_principal" class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Principal</span>
                                </div>
                                <div class="text-sm text-gray-600">Precio: Bs. {{ proveedor.pivot.precio_unitario }}</div>
                            </div>
                            <button @click="removeProvider(selectedMaterial.id_material, proveedor.id_proveedor)" 
                                    class="text-red-600 hover:text-red-900 text-sm">
                                Remover
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add Provider Form -->
                <div class="border-t pt-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Agregar Proveedor</h3>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <InputLabel for="proveedor" value="Proveedor" />
                            <select v-model="providerForm.id_proveedor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Seleccionar proveedor</option>
                                <option v-for="prov in proveedores" :key="prov.id_proveedor" :value="prov.id_proveedor">
                                    {{ prov.nombre }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="precio" value="Precio Unitario (Bs.)" />
                            <TextInput id="precio" type="number" step="0.01" class="mt-1 block w-full" v-model="providerForm.precio_unitario" />
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" v-model="providerForm.es_principal" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <label class="ml-2 text-sm text-gray-700">Marcar como proveedor principal</label>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeProviderModal">Cerrar</SecondaryButton>
                    <PrimaryButton @click="addProvider" :disabled="providerForm.processing || !providerForm.id_proveedor">
                        Agregar Proveedor
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
