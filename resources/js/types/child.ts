export interface Child {
    // liste des elements du dossier
    id: number; // id de la ressource
    name: string; // nom de la ressource
    type: string; // type de la ressource (folder, file ou document)
    color?: string; // couleur (uniquement pour document et folder)
    mimetype?: string; // mimetype (pour les files) donne le type de fichier (ex : pdf, image, video etc ...)
    storage_path?: string;
    departements?: number[]; // departements associés
    created_at: string; // date de creation de la ressource
    is_archived: boolean; // true si l'élément est archivé, false sinon
}
