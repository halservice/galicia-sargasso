<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GalicIA</title>
    @vite('resources/css/app.css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gray-100 flex h-screen w-full overflow-hidden">
{{--<div class="menu-toggle">--}}
{{--    <i class="fas fa-bars"></i>--}}
{{--</div>--}}

<section class="flex flex-col bg-white min-w-[330px] max-w-full h-screen justify-left items-center overflow-y-auto">
    <a href="/" class="py-5 text-7xl text-[#74c7ec] w-[90%] mx-auto text-center hover:text-[#89b4fa] duration-300 ease-in-out px-3" style="font-family: 'Poppins', cursive;"><h1>GalicIA</h1></a>

    <x-nav-link href="/SourceCodeGeneration"
                symbol="&lt;/&gt;"
                description="Communicates with a backend LLM to generate source code"
                :active="request()->is('SourceCodeGeneration')"
    >Source Code Generation</x-nav-link>


    <x-nav-link href="/FormalModelGeneration"
                symbol='<p class="fas fa-cog"></p>'
                description="Generates a formal model of the source code"
                :active="request()->is('FormalModelGeneration')"
    >Formal Model Generation</x-nav-link>

    <x-nav-link href="/CodeVerification"
                symbol="✓"
                description="Automatically checks the generated code"
                :active="request()->is('CodeVerification')"
    >Code Verification</x-nav-link>

    <x-nav-link href="/Feedback"
                symbol='<p class="fas fa-exclamation-triangle"></p>'
                description="Provides detailed feedback of the generated code"
                :active="request()->is('Feedback')"
    >Feedback and Iteration</x-nav-link>

    <x-nav-link href="/Customization"
                symbol="☰"
                description="Customizes the maximum number of iterations"
                :active="request()->is('Customization')"
    >Customization Options</x-nav-link>

</section>

<section class="flex flex-col bg-[#74c7ec] w-[3px] h-screen"></section>

<section class="flex flex-col items-center h-screen w-full p-0 overflow-y-auto">
 {{ $slot }}
</section>


{{--<script src="app.js"></script>--}}

<script>
    // document.querySelector('.menu-toggle').addEventListener('click', function() {
    //     document.querySelector('.side-bar').classList.toggle('active');
    // });
    //
    // document.addEventListener('click', function(event) {
    //     const sidebar = document.querySelector('.side-bar');
    //     const menuToggle = document.querySelector('.menu-toggle');
    //
    //
    //     // If clicking outside both the sidebar and menu toggle button
    //     if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
    //         sidebar.classList.remove('active');
    //     }
    // });

</script>

</body>
</html>
