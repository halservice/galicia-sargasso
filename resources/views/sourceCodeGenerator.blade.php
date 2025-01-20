<x-layout>

    <x-header
            description='Input functional requirements in <b>natural language</b> through a <i>user-friendly</i> interface.'>
            Natural Language Input
        </x-header>

    <div class="flex flex-col items-center h-screen bg-gray-100 py-4">
        <div class="py-3 w-[900px] overflow-y-auto flex-grow">
            <div id="chat-messages"></div>
        </div>


        <div class="bg-white w-[900px] min-w-[400px] text-center py-4 px-4 rounded-[10px] mt-5">
            <div class="textarea-wrapper relative">
                <textarea class="rounded-[8px] bg-gray-100 border-2 w-[95%] py-2 px-2 resize-none" id="user-input" placeholder="Type your natural language input here..." rows="3"></textarea>
                <span  id="submit-btn" class="absolute bottom-2 right-8 text-blue-400 cursor-pointer text-2xl duration-300 hover:text-blue-500">
            <p class="fa fa-paper-plane"></p>
        </span>
            </div>
            <p class="text-sm text-gray-400 text-center pt-3">Free Research Preview. GalicIA may produce inaccurate information.</p>
        </div>

    </div>

    @vite(['resources/js/sourceCodeGenerator.js'])
</x-layout>
