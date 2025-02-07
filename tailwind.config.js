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
    daisyui: {
        themes: ["cmyk","dark"], // false: only light + dark | true: all themes | array: specific themes like this ["light", "dark", "cupcake"]
        darkTheme: "dark", // name of one of the included themes for dark mode
        base: true, // applies background color and foreground color for root element by default
        styled: true, // include daisyUI colors and design decisions for all components
        utils: true, // adds responsive and modifier utility classes
        prefix: "", // prefix for daisyUI classnames (components, modifiers and responsive class names. Not colors)
        logs: true, // Shows info about daisyUI version and used config in the console when building your CSS
        themeRoot: ":root", // The element that receives theme color CSS variables
    },
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // primary: '#3864fc',
    //         //     secondary: '#f9a36f',
    //         //     base: {
    //         //         '100': '#1a1d21',
    //         //         '200': '#212529',
    //         //     },
            },
    //
        },
    },
    plugins: [
        function({addComponents}){addComponents({
        '.chat-message':{backgroundColor:'#f3f3f9',
		borderRadius:'10px',
		padding:'15px',
        gap: '5px',
		maxWidth:'65%',
        width:'fit-content',
        wordWrap:'break-word',
        overflowWrap: 'break-word',
        },
		'.user-message':{marginLeft:'auto',
        marginBottom: '10px',
		backgroundColor:'#ffffff',
		color:'#1e1e2e',
		},
		'.assistant-message':{marginRight:'auto',
        marginBottom: '10px',
        backgroundColor:'#3864fc',
		color:'#f3f3f9',
        },
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
		});},
        require("daisyui"),
	],
};
