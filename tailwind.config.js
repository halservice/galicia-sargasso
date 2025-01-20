import defaultTheme from 'tailwindcss/defaultTheme';

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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [function ({ addComponents }) {
        addComponents({
            '.chat-message': {
                backgroundColor: '#f3f3f9',
                borderRadius: '10px',
                padding: '15px',
                margin: '10px 0',
                maxWidth: '65%',
                width: 'fit-content',
                wordWrap: 'break-word',
            },
            '.user-message': {
                marginLeft: 'auto', /* Pushes message to right */
                marginRight: '15px',
                backgroundColor: '#74c7ec',
                color: '#1e1e2e',
            },
            '.assistant-message': {
                marginRight: 'auto', /* Pushes message to left */
                marginLeft: '0',
                backgroundColor: '#ffffff',
                color: '#1e1e2e',
            },
            '.assistant-message code':{
                backgroundColor: '#f3f3f9',
                color: '#5c5f77',
                borderRadius: '4px',
                fontFamily: 'monospace',
            },
            '.assistant-message pre':{
                backgroundColor: '#f3f3f9',
                color: '#5c5f77',
                borderRadius: '8px',
                padding: '5px',
                marginTop: '10px',
            },
            '.assistant-message ul':{
                margin: '0.5em 0',
                paddingLeft: '1.5em',
            },
            '.assistant-message ol':{
                margin: '0.5em 0',
                paddingLeft: '1.5em',
            },
            '.assistant-message a':{
                color: '#89b4fa',
            },
            '.assistant-message blockquote':{
                color: '#5c5f77',
                borderLeft: '3px solid #74c7ec',
                margin: '0.5em 0',
                paddingLeft: '1em',
            },

        });
    },],
};
