import DOMPurify from 'dompurify';

export const purify = (html: string) => {
    let clean = DOMPurify.sanitize(html, {
        USE_PROFILES: { html: true, svg: true },
        // On laisse DOMPurify nettoyer le contenu du style par défaut
        FORBID_TAGS: ['script', 'iframe', 'object', 'embed'],
        FORBID_ATTR: ['onerror', 'onclick', 'onload', 'onmouseover'],
        ALLOW_UNKNOWN_PROTOCOLS: false,
    });

    clean = clean.replace(/position\s*:\s*(fixed|absolute)/gi, 'position:static');
    clean = clean.replace(/z-index\s*:\s*\d+/gi, 'z-index:1');
    return clean;
}
