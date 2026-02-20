<script lang="ts">
import { logout } from '@/routes';
import { ArrowLeftEndOnRectangleIcon } from '@heroicons/vue/20/solid';
</script>
<template>

    <div class="forbidden-screen">
        <a
            :href="logout.url()"
            class="group gap-2 px-4 py-2 rounded-lg text-right text-slate-400 hover:text-white hover:bg-red-600/20 flex items-center transition-all duration-200"
        >
            <span class="text-lg font-medium">Déconnexion</span>
            <ArrowLeftEndOnRectangleIcon class="w-6 h-6 group-hover:translate-x-1 transition-transform" />
        </a>
        <div class="card">
            <span class="emoji">🚫</span>
            <h1>Accès non autorisé</h1>
            <p>Désolé, Vous n'êtes pas membre du CRAN</p>
        </div>
    </div>
</template>

<style scoped>
.forbidden-screen {
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #111827; /* Fond sombre pour bien marquer la différence */
    color: white;
    font-family: ui-sans-serif, system-ui, sans-serif;
}
.card {
    text-align: center;
    padding: 2rem;
}
.emoji {
    font-size: 50px;
}
h1 {
    font-size: 2rem;
    margin: 1rem 0;
}
p {
    color: #9ca3af;
    margin-bottom: 2rem;
}
.link {
    color: #3b82f6;
    text-decoration: none;
    font-weight: bold;
}
</style>
