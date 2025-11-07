/**
 * Ce fichier contient des fonctions utilitaires pour vérifier le type de fichier
 * basé sur son MIME type (plutôt que son extension).
 *
 * @param mimeType Le MIME type du fichier (ex: 'application/pdf', 'image/jpeg').
 */

export const isPresentationFile = (mimeType: string): boolean => {
    // Les présentations sont principalement dans la famille 'application'
    return (
        mimeType.includes('presentation') || // Par exemple, application/vnd.ms-powerpoint
        mimeType.includes('powerpoint') ||
        mimeType.includes('vnd.openxmlformats-officedocument.presentationml') || // .pptx
        mimeType.includes('vnd.oasis.opendocument.presentation') // .odp
    );
};

export const isDocFile = (mimeType: string): boolean => {
    // Les documents texte et de traitement de texte
    return (
        mimeType.includes('text/plain') ||
        mimeType.includes('application/msword') || // .doc
        mimeType.includes('vnd.openxmlformats-officedocument.wordprocessingml') || // .docx
        mimeType.includes('vnd.oasis.opendocument.text') || // .odt
        mimeType.includes('application/rtf')
    );
};

export const isTabFile = (mimeType: string): boolean => {
    // Les feuilles de calcul
    return (
        mimeType.includes('sheet') || // Par exemple, application/vnd.ms-excel
        mimeType.includes('excel') ||
        mimeType.includes('spreadsheet') || // .ods
        mimeType.includes('csv')
    );
};

export const isVideoFile = (mimeType: string): boolean => {
    // Tous les types qui commencent par 'video/'
    return mimeType.startsWith('video/');
};

export const isImageFile = (mimeType: string): boolean => {
    // Tous les types qui commencent par 'image/', mais nous excluons explicitement les GIFs
    return mimeType.startsWith('image/') && !isGifFile(mimeType);
};

export const isGifFile = (mimeType: string): boolean => {
    // Spécifique pour le format GIF
    return mimeType === 'image/gif';
};

/**
 * Fonction générique pour vérifier si c'est un fichier audio
 */
export const isAudioFile = (mimeType: string): boolean => {
    return mimeType.startsWith('audio/');
}

/**
 * Fonction générique pour vérifier si c'est une archive compressée
 */
export const isArchiveFile = (mimeType: string): boolean => {
    return (
        mimeType.includes('zip') ||
        mimeType.includes('rar') ||
        mimeType.includes('tar') ||
        mimeType.includes('compress')
    );
};
