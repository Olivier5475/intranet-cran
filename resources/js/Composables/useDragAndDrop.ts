import { onMounted, onUnmounted, ref } from 'vue';

interface DragDropOptions {
    canDrop: boolean;
    onDrop: (files: FileList) => void;
}

export function useDragAndDrop({ canDrop, onDrop }: DragDropOptions) {
    const isDragging = ref(false);
    let dragCounter = 0;

    const preventDefaults = (e: Event) => e.preventDefault();

    const handleEnter = (e: DragEvent) => {
        preventDefaults(e);

        // Vérifie si l'objet traîné est un fichier (Files)
        const isFile = e.dataTransfer?.types?.includes('Files');
        if (!isFile) return;

        dragCounter++;
        if (dragCounter === 1) isDragging.value = true;
    };

    const handleLeave = (e: DragEvent) => {
        preventDefaults(e);
        dragCounter--;
        if (dragCounter === 0) isDragging.value = false;
    };

    const handleDrop = (e: DragEvent) => {
        preventDefaults(e);
        dragCounter = 0;
        isDragging.value = false;
        if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) {
            onDrop(e.dataTransfer.files);
        }
    };

    onMounted(() => {
        if (!canDrop) return; // si l'utilisateur n'a pas le droit, on n'initialise pas l'action
        window.addEventListener('dragover', preventDefaults);
        window.addEventListener('dragenter', handleEnter);
        window.addEventListener('dragleave', handleLeave);
        window.addEventListener('drop', handleDrop);
    });

    onUnmounted(() => {
        window.removeEventListener('dragover', preventDefaults);
        window.removeEventListener('dragenter', handleEnter);
        window.removeEventListener('dragleave', handleLeave);
        window.removeEventListener('drop', handleDrop);
    });

    return { isDragging };
}
