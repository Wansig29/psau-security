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
            colors: {
                maroon: {
                    50: '#fdf3f3',
                    100: '#fbe4e4',
                    200: '#f8caca',
                    300: '#f2a3a3',
                    400: '#e96d6d',
                    500: '#dc4040',
                    600: '#c72525',
                    700: '#a61b1b',
                    800: '#8b0000', // PSAU Primary Maroon
                    900: '#731616',
                    950: '#3e0606',
                },
                accent: '#F5F5F5', // Light Gray Accent
            }
        },
    },

    plugins: [forms],
};
