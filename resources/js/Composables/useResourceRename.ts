import { computed } from 'vue';
import folder_route from '@/routes/editor/folder';
import document_route from '@/routes/editor/document';
import file_route from '@/routes/editor/file';

export function useResourceRename(child: any) {
    const updateRoute = computed(() => {
        const routes = {
            folder: folder_route.post.update,
            document: document_route.post.update,
            file: file_route.post.update
        };
        return routes[child.type as keyof typeof routes].url(child.id);
    });

    return { updateRoute };
}
