import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                petita: ['Petita', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                ccpl: {
                    blue: '#007e95',
                    brown: '#7d6d6f',
                    orange: '#ef7918',
                    'light-green': '#d5d900',
                    green: '#3fa535',
                    'strong-green': '#00765b'
                }
            }
        },
    },

    plugins: [forms, typography],
};
