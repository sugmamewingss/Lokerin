import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Hijau pakis: identitas Lokerin untuk Kecamatan Pakis.
                brand: {
                    50: '#effaf3',
                    100: '#d8f3e2',
                    200: '#b4e6c9',
                    300: '#82d2a8',
                    400: '#4db781',
                    500: '#2a9c66',
                    600: '#1c7e52',
                    700: '#176544',
                    800: '#155138',
                    900: '#12432f',
                    950: '#08251a',
                },
            },
            boxShadow: {
                kartu: '0 1px 2px rgba(15, 23, 42, 0.04), 0 8px 24px -12px rgba(15, 23, 42, 0.12)',
                angkat: '0 2px 4px rgba(15, 23, 42, 0.04), 0 16px 32px -12px rgba(15, 23, 42, 0.18)',
            },
        },
    },
    plugins: [forms],
};
