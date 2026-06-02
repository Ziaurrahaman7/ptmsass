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
        // priority & status dynamic colors
        { pattern: /bg-(red|orange|yellow|green|blue|purple|gray|indigo|violet)-(50|100|200)/ },
        { pattern: /text-(red|orange|yellow|green|blue|purple|gray|indigo|violet)-(400|500|600|700)/ },
        { pattern: /border-(red|orange|yellow|green|blue|purple|gray|indigo|violet)-(200|300)/ },
    ],

    plugins: [forms],
};
