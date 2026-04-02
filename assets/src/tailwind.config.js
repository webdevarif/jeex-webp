/** @type {import('tailwindcss').Config} */
module.exports = {
    prefix: 'jw-',
    content: [
        '../../templates/**/*.php',
        '../js/admin.js',
    ],
    corePlugins: {
        preflight: false,
    },
    theme: {
        extend: {
            colors: {
                'wp-blue': '#2271b1',
                'wp-blue-dark': '#135e96',
                'wp-gray': '#1d2327',
            },
        },
    },
    plugins: [],
};
