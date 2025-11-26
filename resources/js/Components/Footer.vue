<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const visitas = ref(0);
const pagina = ref('');
const loading = ref(true);

onMounted(async () => {
    try {
        const currentPath = window.location.pathname;
        const response = await axios.get(`/api/page-visits?url=${encodeURIComponent(currentPath)}`);
        
        visitas.value = response.data.total_visitas || 0;
        pagina.value = response.data.page_name || '';
        loading.value = false;
    } catch (error) {
        console.error('Error al obtener visitas:', error);
        loading.value = false;
    }
});
</script>

<template>
    <footer class="mt-auto py-6 border-t" style="background-color: rgb(var(--bg-card)); border-color: rgb(var(--border-color));">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <!-- Información de la empresa -->
                <div class="text-center md:text-left">
                    <p class="text-sm font-semibold" style="color: rgb(var(--text-primary));">
                        Sistema de Gestión de Trabajos
                    </p>
                    <p class="text-xs mt-1" style="color: rgb(var(--text-secondary));">
                        © {{ new Date().getFullYear() }} Todos los derechos reservados
                    </p>
                </div>

                <!-- Contador de visitas -->
                <div class="flex items-center gap-3 px-4 py-2 rounded-lg" style="background-color: rgb(var(--bg-secondary));">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: rgb(var(--color-primary));">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <div v-if="!loading">
                        <p class="text-xs font-medium" style="color: rgb(var(--text-secondary));">
                            Visitas en esta página
                        </p>
                        <p class="text-lg font-bold" style="color: rgb(var(--color-primary));">
                            {{ visitas.toLocaleString() }}
                        </p>
                    </div>
                    <div v-else class="animate-pulse">
                        <p class="text-xs" style="color: rgb(var(--text-secondary));">Cargando...</p>
                    </div>
                </div>

                <!-- Enlaces adicionales -->
                <div class="flex gap-4 text-xs" style="color: rgb(var(--text-secondary));">
                    <a href="#" class="hover:underline">Ayuda</a>
                    <a href="#" class="hover:underline">Contacto</a>
                    <a href="#" class="hover:underline">Privacidad</a>
                </div>
            </div>
        </div>
    </footer>
</template>
