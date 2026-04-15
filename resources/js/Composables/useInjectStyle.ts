export const injectStyles = (content: string) => {
    return `
        <style>
            body {
                margin: 0;
                font-family: sans-serif; /* Ou ta police principale */
                color: #334155; /* Slate-700 par défaut */
                background: transparent;
            }
            /* Support du mode sombre à l'intérieur de l'iframe */
            @media (prefers-color-scheme: dark) {
                body {
                    color: #e2e8f0; /* Slate-200 */
                }
            }
            /* Si tu forces le mode sombre via une classe .dark sur le parent */
            .dark-mode body {
                color: #e2e8f0;
            }
            img { max-width: 100%; height: auto; }
        </style>
        <body class="${document.documentElement.classList.contains('dark') ? 'dark-mode' : ''}">
            ${content}
        </body>
    `;
};
