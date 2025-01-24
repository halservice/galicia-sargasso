import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
		'./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
		 './storage/framework/views/*.php',
		 './resources/**/*.blade.php',
		 './resources/**/*.js',
		 './resources/**/*.vue',
		 "./vendor/robsontenorio/mary/src/View/Components/**/*.php"
	],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
              background: '#1a1d21',
                component: '#212529',
                text: '#fab387'
            },
        },
    },
    plugins: [
		function({addComponents}){addComponents({'.chat-message':{backgroundColor:'#f3f3f9',
		borderRadius:'10px',
		padding:'15px',
		margin:'10px0',
		maxWidth:'65%',
		width:'fit-content',
		wordWrap:'break-word',
		},
		'.user-message':{marginLeft:'auto',
		/*Pushesmessagetoright*/marginRight:'15px',
		backgroundColor:'#74c7ec',
		color:'#1e1e2e',
		},
		'.assistant-message':{marginRight:'auto',
		/*Pushesmessagetoleft*/marginLeft:'0',
		backgroundColor:'#ffffff',
		color:'#1e1e2e',
		},
		'.assistant-messagecode':{backgroundColor:'#f3f3f9',
		color:'#5c5f77',
		borderRadius:'4px',
		fontFamily:'monospace',
		},
		'.assistant-messagepre':{backgroundColor:'#f3f3f9',
		color:'#5c5f77',
		borderRadius:'8px',
		padding:'5px',
		marginTop:'10px',
		},
		'.assistant-messageul':{margin:'0.5em0',
		paddingLeft:'1.5em',
		},
		'.assistant-messageol':{margin:'0.5em0',
		paddingLeft:'1.5em',
		},
		'.assistant-messagea':{color:'#89b4fa',
		},
		'.assistant-messageblockquote':{color:'#5c5f77',
		borderLeft:'3pxsolid#74c7ec',
		margin:'0.5em0',
		paddingLeft:'1em',
		},
		});},
		require("daisyui")
	],
};
