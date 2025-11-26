<script setup>
import { ref } from 'vue';
import { useTheme } from '@/Composables/useTheme';

const {
    tema,
    modo,
    tamano,
    contraste,
    TEMAS,
    MODOS,
    TAMANOS,
    CONTRASTES,
    cambiarTema,
    cambiarModo,
    cambiarTamano,
    cambiarContraste,
    aumentarTamano,
    disminuirTamano
} = useTheme();

const menuAbierto = ref(false);
</script>

<template>
    <div class="relative">
        <!-- Botón para abrir menú -->
        <button
            @click="menuAbierto = !menuAbierto"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
            :class="{'bg-gray-100': menuAbierto}"
            title="Personalización"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
        </button>

        <!-- Menú desplegable -->
        <transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="menuAbierto"
                class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-elegant-lg border border-gray-200 p-4 z-50"
            >
                <h3 class="text-lg font-bold mb-4 text-gray-900">Personalización</h3>

                <!-- Selector de Tema -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2 text-gray-700">Tema</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            @click="cambiarTema(TEMAS.NINOS)"
                            class="p-3 rounded-lg border-2 transition-all hover:scale-105"
                            :class="tema === TEMAS.NINOS ? 'border-pink-500 bg-pink-50' : 'border-gray-200'"
                        >
                            <div class="text-2xl mb-1">🎨</div>
                            <div class="text-xs font-medium">Niños</div>
                        </button>
                        <button
                            @click="cambiarTema(TEMAS.JOVENES)"
                            class="p-3 rounded-lg border-2 transition-all hover:scale-105"
                            :class="tema === TEMAS.JOVENES ? 'border-violet-500 bg-violet-50' : 'border-gray-200'"
                        >
                            <div class="text-2xl mb-1">⚡</div>
                            <div class="text-xs font-medium">Jóvenes</div>
                        </button>
                        <button
                            @click="cambiarTema(TEMAS.ADULTOS)"
                            class="p-3 rounded-lg border-2 transition-all hover:scale-105"
                            :class="tema === TEMAS.ADULTOS ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'"
                        >
                            <div class="text-2xl mb-1">💼</div>
                            <div class="text-xs font-medium">Adultos</div>
                        </button>
                    </div>
                </div>

                <!-- Selector de Modo -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2 text-gray-700">Modo de Color</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            @click="cambiarModo(MODOS.CLARO)"
                            class="p-2 rounded-lg border-2 text-xs transition-all"
                            :class="modo === MODOS.CLARO ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200'"
                        >
                            ☀️ Claro
                        </button>
                        <button
                            @click="cambiarModo(MODOS.OSCURO)"
                            class="p-2 rounded-lg border-2 text-xs transition-all"
                            :class="modo === MODOS.OSCURO ? 'border-gray-700 bg-gray-100' : 'border-gray-200'"
                        >
                            🌙 Oscuro
                        </button>
                        <button
                            @click="cambiarModo(MODOS.CREMA)"
                            class="p-2 rounded-lg border-2 text-xs transition-all"
                            :class="modo === MODOS.CREMA ? 'border-amber-500 bg-amber-50' : 'border-gray-200'"
                        >
                            🎨 Crema
                        </button>
                    </div>
                </div>

                <!-- Control de Tamaño de Fuente -->
                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-2 text-gray-700">Tamaño de Letra</label>
                    <div class="flex items-center gap-2">
                        <button
                            @click="disminuirTamano"
                            class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                            :disabled="tamano === TAMANOS.PEQUENO"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                        </button>
                        <div class="flex-1 text-center">
                            <span class="text-sm font-medium capitalize">{{ tamano }}</span>
                        </div>
                        <button
                            @click="aumentarTamano"
                            class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                            :disabled="tamano === TAMANOS.MUY_GRANDE"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Selector de Contraste -->
                <div class="mb-2">
                    <label class="block text-sm font-semibold mb-2 text-gray-700">Contraste</label>
                    <select
                        v-model="contraste"
                        @change="cambiarContraste(contraste)"
                        class="w-full p-2 border border-gray-300 rounded-lg text-sm"
                    >
                        <option :value="CONTRASTES.NORMAL">Normal</option>
                        <option :value="CONTRASTES.ALTO">Alto</option>
                        <option :value="CONTRASTES.MUY_ALTO">Muy Alto</option>
                    </select>
                </div>
            </div>
        </transition>

        <!-- Overlay para cerrar el menú -->
        <div
            v-if="menuAbierto"
            @click="menuAbierto = false"
            class="fixed inset-0 z-40"
        ></div>
    </div>
</template>