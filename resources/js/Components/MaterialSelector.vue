<script setup>
import { ref, computed, watch } from 'vue';
import Button from '@/Components/UI/Button.vue';
import Badge from '@/Components/UI/Badge.vue';

const props = defineProps({
    materiales: Array, // All available materials
    modelValue: Array, // Selected materials
});

const emit = defineEmits(['update:modelValue']);

const searchQuery = ref('');
const selectedMaterials = ref(props.modelValue || []);

// Filter materials by search
const filteredMateriales = computed(() => {
    if (!searchQuery.value) return props.materiales;
    
    const query = searchQuery.value.toLowerCase();
    return props.materiales.filter(m => 
        m.nombre.toLowerCase().includes(query) ||
        m.descripcion?.toLowerCase().includes(query)
    );
});

// Add material to selection
const addMaterial = (material) => {
    const exists = selectedMaterials.value.find(m => m.id_material === material.id_material);
    
    if (!exists) {
        selectedMaterials.value.push({
            id_material: material.id_material,
            nombre: material.nombre,
            precio_unitario: material.precio_unitario,
            cantidad_disponible: material.cantidad_disponible,
            unidad_medida: material.unidad_medida,
            cantidad: 1,
        });
    }
    
    searchQuery.value = '';
};

// Remove material from selection
const removeMaterial = (index) => {
    selectedMaterials.value.splice(index, 1);
};

// Calculate subtotal for a material
const getSubtotal = (material) => {
    return (material.precio_unitario * material.cantidad).toFixed(2);
};

// Calculate total
const total = computed(() => {
    return selectedMaterials.value.reduce((sum, m) => {
        return sum + (m.precio_unitario * m.cantidad);
    }, 0).toFixed(2);
});

// Emit changes
watch(selectedMaterials, (newValue) => {
    emit('update:modelValue', newValue);
}, { deep: true });
</script>

<template>
    <div class="space-y-4">
        <!-- Search Input -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar materiales..."
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            />
        </div>

        <!-- Search Results -->
        <div v-if="searchQuery && filteredMateriales.length > 0" class="border border-gray-200 rounded-lg max-h-60 overflow-y-auto">
            <button
                v-for="material in filteredMateriales"
                :key="material.id_material"
                @click="addMaterial(material)"
                type="button"
                class="w-full px-4 py-3 text-left hover:bg-gray-50 border-b border-gray-100 last:border-b-0 transition-colors"
            >
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="font-medium text-gray-900">{{ material.nombre }}</p>
                        <p class="text-sm text-gray-500">
                            Stock: {{ material.cantidad_disponible }} {{ material.unidad_medida }} | 
                            Bs {{ material.precio_unitario }}/{{ material.unidad_medida }}
                        </p>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
            </button>
        </div>

        <!-- Selected Materials -->
        <div v-if="selectedMaterials.length > 0" class="space-y-3">
            <h4 class="font-medium text-gray-900">Materiales Seleccionados</h4>
            
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Material</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cantidad</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(material, index) in selectedMaterials" :key="material.id_material">
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ material.nombre }}</p>
                                    <p class="text-xs text-gray-500">
                                        Stock: {{ material.cantidad_disponible }} {{ material.unidad_medida }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <input
                                    v-model.number="material.cantidad"
                                    type="number"
                                    min="0.01"
                                    :max="material.cantidad_disponible"
                                    step="0.01"
                                    class="w-24 px-2 py-1 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                    :class="{ 'border-red-500': material.cantidad > material.cantidad_disponible }"
                                />
                                <span class="ml-1 text-xs text-gray-500">{{ material.unidad_medida }}</span>
                                <p v-if="material.cantidad > material.cantidad_disponible" class="text-xs text-red-600 mt-1">
                                    Stock insuficiente
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                Bs {{ material.precio_unitario }}
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                Bs {{ getSubtotal(material) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    @click="removeMaterial(index)"
                                    type="button"
                                    class="text-red-600 hover:text-red-800 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right font-medium text-gray-900">
                                Total Materiales:
                            </td>
                            <td colspan="2" class="px-4 py-3 text-sm font-bold text-indigo-600">
                                Bs {{ total }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500 text-sm">
            Busca y selecciona materiales para el presupuesto
        </div>
    </div>
</template>
