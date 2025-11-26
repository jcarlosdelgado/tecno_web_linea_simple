<template>
    <div>
        <!-- Gallery Grid -->
        <div :class="`grid grid-cols-${columns} gap-4`" class="grid gap-4">
            <div 
                v-for="(photo, index) in photos" 
                :key="index"
                @click="openLightbox(index)"
                class="relative cursor-pointer group overflow-hidden rounded-lg aspect-square"
            >
                <img 
                    :src="getPhotoUrl(photo)" 
                    :alt="`Foto ${index + 1}`"
                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                />
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-opacity duration-300 flex items-center justify-center">
                    <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Lightbox -->
        <Teleport to="body">
            <Transition name="fade">
                <div 
                    v-if="lightboxOpen" 
                    @click="closeLightbox"
                    class="fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center p-4"
                >
                    <!-- Close Button -->
                    <button 
                        @click="closeLightbox"
                        class="absolute top-4 right-4 text-white hover:text-gray-300 transition-colors"
                    >
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Previous Button -->
                    <button 
                        v-if="photos.length > 1"
                        @click.stop="previousPhoto"
                        class="absolute left-4 text-white hover:text-gray-300 transition-colors"
                    >
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>

                    <!-- Image -->
                    <div @click.stop class="max-w-5xl max-h-full">
                        <img 
                            :src="getPhotoUrl(photos[currentIndex])" 
                            :alt="`Foto ${currentIndex + 1}`"
                            class="max-w-full max-h-[90vh] object-contain"
                        />
                        <p class="text-white text-center mt-4">
                            {{ currentIndex + 1 }} / {{ photos.length }}
                        </p>
                    </div>

                    <!-- Next Button -->
                    <button 
                        v-if="photos.length > 1"
                        @click.stop="nextPhoto"
                        class="absolute right-4 text-white hover:text-gray-300 transition-colors"
                    >
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    photos: {
        type: Array,
        required: true,
        // Array of photo URLs or objects with url property
    },
    columns: {
        type: Number,
        default: 3,
        validator: (value) => value >= 1 && value <= 6
    }
});

const lightboxOpen = ref(false);
const currentIndex = ref(0);

const getPhotoUrl = (photo) => {
    if (typeof photo === 'string') {
        return photo.startsWith('http') ? photo : `/storage/${photo}`;
    }
    return photo.url_foto?.startsWith('http') ? photo.url_foto : `/storage/${photo.url_foto}`;
};

const openLightbox = (index) => {
    currentIndex.value = index;
    lightboxOpen.value = true;
};

const closeLightbox = () => {
    lightboxOpen.value = false;
};

const nextPhoto = () => {
    currentIndex.value = (currentIndex.value + 1) % props.photos.length;
};

const previousPhoto = () => {
    currentIndex.value = (currentIndex.value - 1 + props.photos.length) % props.photos.length;
};

const handleKeydown = (e) => {
    if (!lightboxOpen.value) return;
    
    if (e.key === 'Escape') {
        closeLightbox();
    } else if (e.key === 'ArrowRight') {
        nextPhoto();
    } else if (e.key === 'ArrowLeft') {
        previousPhoto();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
