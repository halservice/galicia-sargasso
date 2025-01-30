import defaultTheme from 'tailwindcss/defaultTheme';
import daisyui from "daisyui";

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
        daisyui: {
            themes: ["cupcake"],
        },
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
    //         // colors: {
    //         //     primary: '#fab387',
    //         //     secondary: '#f9a36f',
    //         //     base: {
    //         //         '100': '#1a1d21',
    //         //         '200': '#212529',
    //         //     },
    //         // },

        },
    },
    plugins: [
        daisyui
        ],
        // function({addComponents}){addComponents({'.chat-message':{backgroundColor:'#f3f3f9',
		// borderRadius:'10px',
		// padding:'15px',
        // gap: '5px',
		// maxWidth:'65%',
		// },
		// '.user-message':{marginLeft:'auto',
        // marginBottom: '10px',
		// backgroundColor:'#fab387',
		// color:'#1e1e2e',
        // width:'fit-content',
        // wordWrap:'break-word',
		// },
		// '.assistant-message':{marginRight:'auto',
        // marginBottom: '10px',
        // backgroundColor:'#1a1d21',
		// color:'#f3f3f9',
        // width:'fit-content',
        // wordWrap:'break-word',
		// },
		// // '.assistant-messagecode':{backgroundColor:'#ffffff',
		// // color:'#ffffff',
		// // borderRadius:'4px',
		// // fontFamily:'monospace',
		// // },
		// // '.assistant-messagepre':{backgroundColor:'#ffffff',
		// // color:'#ffffff',
		// // borderRadius:'8px',
		// // padding:'5px',
		// // marginTop:'10px',
		// // },
		// // '.assistant-messageul':{margin:'0.5em0',
		// // paddingLeft:'1.5em',
		// // },
		// // '.assistant-messageol':{margin:'0.5em0',
		// // paddingLeft:'1.5em',
		// // },
		// // '.assistant-messagea':{color:'#89b4fa',
		// // },
		// // '.assistant-messageblockquote':{color:'#ffffff',
		// // borderLeft:'3pxsolid#74c7ec',
		// // margin:'0.5em0',
		// // paddingLeft:'1em',
		// // },
		// });},
	// ],
};
