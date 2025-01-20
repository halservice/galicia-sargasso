<x-layout>

    <x-header
        description='Automatically <i>checks the generated code</i> against the formal model and <b>refines it</b> based on the errors detected.'>
        Code Verification
    </x-header>

    <x-generate-page
        button="Validate Code"
    >
        Would you like to proceed with the code validation:
    </x-generate-page>

    <div class="chat-container-formal">
        <div id="chat-messages"></div>
    </div>

</x-layout>
