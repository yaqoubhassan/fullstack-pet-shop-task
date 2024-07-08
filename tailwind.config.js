/** @type {import('tailwindcss').Config} */

export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                primary: "rgb(78, 198, 144)",
            },
        },
    },
    plugins: [],
    prefix: "tw-",
};
