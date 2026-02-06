// tailwind.config.cjs

const formsPlugin = require('@tailwindcss/forms');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./resources/js/**/*.vue",
    ],
    theme: {
        extend: {},
    },
    plugins: [
        formsPlugin,
    ],
}
