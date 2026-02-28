/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                poppins: ['Poppins', 'sans-serif'],
                sans: ['"Public Sans"', 'sans-serif'],
            },
        },
    },
    darkMode: 'class',
    plugins: [require("daisyui")],
    daisyui: {
        themes: ["light", "posdark"],
    },
}
