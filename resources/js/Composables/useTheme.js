import { ref, onMounted } from 'vue';

export const TEMAS = {
    NINOS: 'ninos',
    JOVENES: 'jovenes',
    ADULTOS: 'adultos'
};

export const MODOS = {
    CLARO: 'claro',
    OSCURO: 'oscuro',
    CREMA: 'crema'
};

export const TAMANOS = {
    PEQUENO: 'pequeno',
    NORMAL: 'normal',
    GRANDE: 'grande'
};

export const CONTRASTES = {
    NORMAL: 'normal',
    ALTO: 'alto'
};

// Estado reactivo global
const tema = ref(localStorage.getItem('tema') || TEMAS.ADULTOS);
const modo = ref(localStorage.getItem('modo') || MODOS.CLARO);
const tamano = ref(localStorage.getItem('tamano') || TAMANOS.NORMAL);
const contraste = ref(localStorage.getItem('contraste') || CONTRASTES.NORMAL);

// Función para aplicar las clases
function aplicarClases() {
    const html = document.documentElement;
    
    // Remover solo las clases de tema
    html.classList.remove('tema-ninos', 'tema-jovenes', 'tema-adultos');
    html.classList.remove('modo-claro', 'modo-oscuro', 'modo-crema');
    html.classList.remove('tamano-pequeno', 'tamano-normal', 'tamano-grande');
    html.classList.remove('contraste-normal', 'contraste-alto');
    
    // Aplicar nuevas clases
    html.classList.add(`tema-${tema.value}`);
    html.classList.add(`modo-${modo.value}`);
    html.classList.add(`tamano-${tamano.value}`);
    html.classList.add(`contraste-${contraste.value}`);
    
    console.log('��� Tema aplicado:', tema.value, modo.value);
}

// Aplicar al cargar el módulo
if (typeof window !== 'undefined') {
    aplicarClases();
}

export function useTheme() {
    
    const cambiarTema = (nuevoTema) => {
        console.log('Cambiando tema a:', nuevoTema);
        tema.value = nuevoTema;
        localStorage.setItem('tema', nuevoTema);
        aplicarClases();
    };
    
    const cambiarModo = (nuevoModo) => {
        console.log('Cambiando modo a:', nuevoModo);
        modo.value = nuevoModo;
        localStorage.setItem('modo', nuevoModo);
        aplicarClases();
    };
    
    const cambiarTamano = (nuevoTamano) => {
        tamano.value = nuevoTamano;
        localStorage.setItem('tamano', nuevoTamano);
        aplicarClases();
    };
    
    const cambiarContraste = (nuevoContraste) => {
        contraste.value = nuevoContraste;
        localStorage.setItem('contraste', nuevoContraste);
        aplicarClases();
    };
    
    const aumentarTamano = () => {
        const orden = [TAMANOS.PEQUENO, TAMANOS.NORMAL, TAMANOS.GRANDE];
        const index = orden.indexOf(tamano.value);
        if (index < orden.length - 1) {
            cambiarTamano(orden[index + 1]);
        }
    };
    
    const disminuirTamano = () => {
        const orden = [TAMANOS.PEQUENO, TAMANOS.NORMAL, TAMANOS.GRANDE];
        const index = orden.indexOf(tamano.value);
        if (index > 0) {
            cambiarTamano(orden[index - 1]);
        }
    };
    
    onMounted(() => {
        aplicarClases();
    });
    
    return {
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
    };
}
