import { onMounted, onUnmounted } from 'vue';
import { logout } from '@/routes';

const IDLE_TIMEOUT = 30 * 60 * 1000;

export default {
    setup() {
        let timerInstance : number|null = null;

        const logoutUser = () => {
            window.location.href = logout.url();
        };

        const resetTimer = () => {
            if (timerInstance !== null) {
                clearTimeout(timerInstance);
            }
            timerInstance = setTimeout(logoutUser, IDLE_TIMEOUT);
        };

        onMounted(() => {
            const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];

            events.forEach(event => {
                window.addEventListener(event, resetTimer);
            });

            resetTimer();
        });

        onUnmounted(() => {
            if (timerInstance !== null) {
                clearTimeout(timerInstance);
            }
            const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'];
            events.forEach(event => {
                window.removeEventListener(event, resetTimer);
            });
        });
    }
}
