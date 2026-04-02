import { onMounted, onUnmounted } from 'vue';

interface ShortcutOptions {
    key: string;
    action: () => void;
    isEnabled: boolean;
}

export function useShortcuts({ key, action, isEnabled }: ShortcutOptions) {
    const handleKeyDown = (event: KeyboardEvent) => {
        if (!isEnabled) return;

        const activeEl = document.activeElement;
        const isInput =
            activeEl instanceof HTMLInputElement ||
            activeEl instanceof HTMLTextAreaElement ||
            (activeEl instanceof HTMLElement && activeEl.isContentEditable);

        if (event.key.toLowerCase() === key.toLowerCase() && !isInput) {
            action();
        }
    };

    onMounted(() => window.addEventListener('keydown', handleKeyDown));
    onUnmounted(() => window.removeEventListener('keydown', handleKeyDown));
}
