<script setup lang="ts">
// 1. Vue & Core
import { ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

// 2. Librairies tierces (Icônes)
import { PencilIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';

// 3. Composables & Utilitaires (Logique)
import { useDragAndDrop } from '@/Composables/useDragAndDrop';
import { decodeEntities } from '@/Composables/useDecodeModule';
import {
    isDocFile,
    isImageFile,
    isPresentationFile,
    isTabFile,
    isVideoFile
} from '@/Composables/useDocumentsTypeRegex';

// 4. Routes
import download from '@/routes/download';
import editor_route from '@/routes/editor';
import route from '@/routes/editor/document';

// Initialisation
const page = usePage();

const props = defineProps<{
    document: {
        // Document courant
        id: number;
        folder_id: number;
        title: string;
        content: string;
        attachments?: Array<{
            // Liste des pièces jointes
            id: number;
            name: string;
            storage_path: string; // chemin d'accès de la piece jointe
            mimetype: string; // Type de fichier de la pièce jointe (ex pdf, image, video etc ...)
            size: number; // taille du fichier en octet
        }>;
        departements: number[];
        color: string;
    };
}>();

// On récupère les départements de la page
const parentDpts = props.document.departements as number[];

// On récupère l'utilisateur
const user = page.props.auth.user;

// On récupère les départements de l'utilisateur
const userDpts = user.departements as number[];

// On récupère les départements en commun
const compareParentAndUser = parentDpts.filter((value) => userDpts.includes(value));
const canEdit = ref(
    user.role === 'admin' || // Si l'utilisateur est un admin, il peut créer.
        // Si c'est un editeur et qu'il a des roles en commun avec la page, il peut créer.
        (user.role === 'editor' && (parentDpts.length === 0 || compareParentAndUser.length > 0)),
);

// Logique du Drag & Drop pour les fichiers
const { isDragging } = useDragAndDrop({
    canDrop: canEdit.value,
    onDrop: (file) => {
        // Formulaire à envoyer au contrôleur Laravel
        const form = useForm({
            title: decodeEntities(props.document.title), // On récupère renvoie le meme titre
            content: props.document.content ?? '', // On récupère renvoie le meme contenu
            existing_attachments: props.document?.attachments ?? [], // On renvoie les piece jointe deja existante
            new_attachments: [] as File[], // On prépare l'envoie de la nouvelle pièce jointe
            departements: props.document?.departements ?? [], // On renvoie les meme departements
            // On renvoie la meme couleur et du blanc si on ne trouve pas de couleur
            color: props.document.color ?? '#ffffff',
            parent_id: props.document.folder_id ?? null, // on renvoie le même parent
        });

        // On met le fichier dans une liste pour rendre compatible avec le type attendu par le contrôleur Laravel
        form.new_attachments = Array.from(file);

        // on envoie le formulaire
        form.post(route.post.update.url(props.document.id), { method: 'patch' });
    },
});
</script>

<template>
    <div v-if="isDragging && canEdit" class="left-0 top-0 bg-sky-400/40 absolute z-50 flex h-full w-full">
        <div class="bg-sky-900/30 rounded-2xl border-sky-900 z-10 mx-auto my-auto flex h-[92%] w-[92%] border-4 border-dashed">
            <p class="text-sky-900 text-4xl font-black mx-auto my-auto">Déposez votre fichier</p>
        </div>
    </div>
    <div class="max-w-5xl mb-10 mx-auto overflow-hidden">
        <header class="group bg-slate-50 dark:bg-slate-800/50 p-6 rounded-t-2xl border-slate-200 dark:border-slate-700 relative border-b">
            <h1 class="text-4xl font-black text-slate-800 dark:text-white tracking-tight text-center first-letter:uppercase">
                {{ decodeEntities(document.title) }}
            </h1>

            <Link
                v-if="canEdit"
                :href="editor_route.document.update.url(document.id)"
                class="right-4 p-3 bg-white dark:bg-slate-700 shadow-sm hover:shadow-md rounded-xl text-sky-600 dark:text-sky-400 border-slate-100 dark:border-slate-600 absolute top-1/2 -translate-y-1/2 border transition-all hover:scale-110"
                title="Modifier le document"
            >
                <PencilIcon class="w-6 h-6" />
            </Link>
        </header>

        <article class="bg-white p-8 md:p-12 text-xl leading-relaxed text-slate-700 dark:text-slate-200 text-justify dark:bg-transparent">
            <div v-html="document.content" class="ckeditor-content-render prose prose-slate dark:prose-invert max-w-none"></div>
        </article>

        <section class="space-y-12 pb-12 border-slate-100 dark:border-slate-800 pt-10 border-t">
            <div v-for="attachment in document.attachments" :key="attachment.id" class="px-4">
                <div v-if="attachment.name.endsWith('pdf')" class="group">
                    <p class="text-xs font-black tracking-widest text-slate-400 mb-4 gap-2 flex items-center justify-center text-center uppercase">
                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                        Aperçu PDF : {{ attachment.name }}
                    </p>
                    <iframe
                        :id="'pdf-' + attachment.id"
                        :src="download.attachment.url(attachment.id)"
                        height="800px"
                        class="md:w-11/12 bg-white rounded-2xl shadow-2xl border-slate-200 dark:border-slate-700 mx-auto w-full border"
                    />
                </div>

                <figure v-else-if="isImageFile(attachment.mimetype)" class="flex flex-col items-center">
                    <img
                        :src="download.attachment.url(attachment.id)"
                        :alt="attachment.name"
                        class="md:w-10/12 rounded-2xl shadow-lg border-slate-100 dark:border-slate-800 mx-auto w-full border"
                    />
                    <figcaption class="mt-4 text-sm text-slate-400 italic">{{ attachment.name }}</figcaption>
                </figure>

                <video
                    v-else-if="isVideoFile(attachment.name)"
                    :src="download.attachment.url(attachment.id)"
                    controls
                    class="md:w-9/12 rounded-2xl shadow-2xl mx-auto w-full overflow-hidden"
                />

                <div v-else class="flex justify-center">
                    <a
                        class="group gap-6 p-1 bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:shadow-xl hover:border-sky-300 dark:hover:border-sky-500 flex items-center rounded-full border transition-all duration-300"
                        :href="download.attachment.url(attachment.id)"
                    >
                        <span class="pl-6 font-bold text-slate-600 dark:text-slate-300 group-hover:text-sky-600 dark:group-hover:text-sky-400">
                            Télécharger le document
                        </span>

                        <span
                            :class="[
                                isPresentationFile(attachment.name)
                                    ? 'bg-red-500 shadow-red-500/20'
                                    : isDocFile(attachment.name)
                                      ? 'bg-blue-500 shadow-blue-500/20'
                                      : isTabFile(attachment.name)
                                        ? 'bg-emerald-500 shadow-emerald-500/20'
                                        : 'bg-violet-500 shadow-violet-500/20',
                                'px-6 py-4 text-white font-black text-sm gap-2 shadow-lg flex items-center rounded-full transition-transform group-hover:scale-105',
                            ]"
                        >
                            <ArrowDownTrayIcon class="w-5 h-5 stroke-[3]" />
                            {{ attachment.name }}
                        </span>
                    </a>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* Amélioration du rendu CKEditor par défaut */
.ckeditor-content-render :deep(h2) {
    @apply text-2xl font-black text-slate-800 dark:text-white mb-4 mt-8 pb-2 dark:border-slate-800 border-b;
}
.ckeditor-content-render :deep(p) {
    @apply mb-6;
}
.ckeditor-content-render :deep(ul) {
    @apply mb-6 space-y-2 list-inside list-disc;
}
</style>
