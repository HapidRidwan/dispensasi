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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#163A5F',
                    dark: '#0F2740',
                },
                accent: '#F4B400',
                surface: '#FFFFFF',
                background: '#F7F9FC',
                ink: '#172033',
                muted: '#64748B',
                line: '#E2E8F0',
            },
            borderRadius: {
                DEFAULT: '0.5rem',
            },
        },
    },

    plugins: [forms, typography],
};
