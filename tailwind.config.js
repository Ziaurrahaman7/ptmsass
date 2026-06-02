import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    safelist: [
        // light mode dynamic colors
        { pattern: /bg-(red|orange|yellow|green|blue|purple|gray|indigo|violet)-(50|100|200)/ },
        { pattern: /text-(red|orange|yellow|green|blue|purple|gray|indigo|violet)-(400|500|600|700)/ },
        { pattern: /border-(red|orange|yellow|green|blue|purple|gray|indigo|violet)-(200|300)/ },
        // dark mode dynamic colors
        { pattern: /bg-(red|orange|yellow|green|blue|purple|gray|indigo|violet)-(800|900)(\/50)?/, variants: [''] },
        'bg-red-900/50', 'bg-orange-900/50', 'bg-yellow-900/50',
        'bg-green-900/50', 'bg-blue-900/50', 'bg-purple-900/50',
        'bg-indigo-900/50', 'bg-violet-900/50', 'bg-gray-900/50',
        'text-red-400', 'text-orange-400', 'text-yellow-400',
        'text-green-400', 'text-blue-400', 'text-purple-400',
        'text-indigo-400', 'text-violet-400', 'text-gray-400',
    ],

    plugins: [forms],
};
