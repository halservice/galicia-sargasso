<x-layout>

    <x-header
        description='Allow users to customize the <i>maximum number of iterations</i> for the code refinement.'>
        Customization Options
    </x-header>

    <div class="bg-white w-[800px] w-min[400px] text-align-left py-3 px-3 rounded-[10px]">
        <x-select-info id="programming-language-tool-select" label="Select a programming language:" placeholder="Select a language.."
            :options="[
            'c' => 'C',
            'php' => 'PHP',
        ]"
        />

        <x-select-info id="model-tool-select" label="Select formal model tool:" placeholder="Select a tool..."
                       :options="[
            'nusmv' => 'NuSMV',
            'eventb' => 'Event-B',
        ]"
        />

        <x-select-info id="number-iteration-tool-select" label="Select the number of iterations:" placeholder="Select a number"
                       :options="[
            '1' => '1',
            '2' => '2',
            '3' => '3',
            '4' => '4',
            '5' => '5',
        ]"
        />

        <x-select-info id="llm-code-tool-select" label="Select the LLM for generating the code:" placeholder="Select a LLM..."
                       :options="[
            'chatgpt' => 'ChatGPT',
            'llama'=> 'Llama-3.1',
        ]"
        />

        <x-select-info id="llm-formal-tool-select" label="Select the LLM for generating the formal model:" placeholder="Select a LLM..."
                       :options="[
            'chatgpt' => 'ChatGPT',
            'llama'=> 'Llama-3.1',
        ]"
        />

        <x-select-info id="llm-validation-tool-select" label="Select the LLM for validating the code:" placeholder="Select a LLM..."
                       :options="[
            'chatgpt' => 'ChatGPT',
            'llama'=> 'Llama-3.1',
        ]"
        />

    </div>

    <script>
        const languageSelect = document.querySelector('#programming-language-tool-select')
        const modelSelect = document.querySelector('#model-tool-select')
        const numberSelect = document.querySelector('#number-iteration-tool-select')
        const llmCodeSelect = document.querySelector('#llm-code-tool-select')
        const llmFormalSelect = document.querySelector('#llm-formal-tool-select')
        const llmValidationSelect = document.querySelector('#llm-validation-tool-select')

        // Show values saved
        function setDropdownValue(dropdown, key) {
            const savedValue = localStorage.getItem(key);
            if (savedValue) {
                dropdown.value = savedValue;
            }
        }

        languageSelect.addEventListener('change', function() {
            const programmingLanguageDropdown = document.getElementById('programming-language-tool-select');
            localStorage.setItem('selectedProgrammingLanguage', programmingLanguageDropdown.value);
            console.log(localStorage.getItem('selectedProgrammingLanguage'));
            localStorage.setItem('sourceCodeGenerated','false')
            localStorage.setItem('showSourceCode','false');
        });

        modelSelect.addEventListener('change', function() {
            const formalToolDropdown = document.getElementById('model-tool-select');
            localStorage.setItem('selectedFormalTool', formalToolDropdown.value);
            localStorage.setItem('formalModelGenerated', 'false');
            localStorage.setItem('showFormalModel','false');
        });

        numberSelect.addEventListener('change', function() {
            const numberDropdown = document.getElementById('number-iteration-tool-select');
            localStorage.setItem('selectedNumberIteration', numberDropdown.value);
            localStorage.setItem('formalModelGenerated','false');
            localStorage.setItem('showValidated','false');
        });

        llmCodeSelect.addEventListener('change', function() {
            const llmCodeDropdown = document.getElementById('llm-code-tool-select');
            localStorage.setItem('selectedLLMCode', llmCodeDropdown.value);
            localStorage.setItem('sourceCodeGenerated','false')
            localStorage.setItem('showSourceCode','false');
        });

        llmFormalSelect.addEventListener('change', function() {
            const llmFormalDropdown = document.getElementById('llm-formal-tool-select');
            localStorage.setItem('selectedLLMFormal', llmFormalDropdown.value);
            localStorage.setItem('formalModelGenerated', 'false');
            localStorage.setItem('showFormalModel','false');
        });

        llmValidationSelect.addEventListener('change', function() {
            const llmValidationDropdown = document.getElementById('llm-validation-tool-select');
            localStorage.setItem('selectedLLMValidation', llmValidationDropdown.value);
            localStorage.setItem('formalModelGenerated','false');
            localStorage.setItem('showValidated','false');
        });

        document.addEventListener('DOMContentLoaded', function () {
            setDropdownValue(languageSelect, 'selectedProgrammingLanguage');
            setDropdownValue(modelSelect, 'selectedFormalTool');
            setDropdownValue(numberSelect, 'selectedNumberIteration');
            setDropdownValue(llmCodeSelect, 'selectedLLMCode');
            setDropdownValue(llmFormalSelect, 'selectedLLMFormal');
            setDropdownValue(llmValidationSelect, 'selectedLLMValidation');
        });
    </script>


</x-layout>
