import { displayMessage } from './utils/messageUtils';

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const submitButton = document.querySelector('#submit-btn');
const input = document.querySelector('#user-input');

async function getMessage(userMessage) {
    try {
        localStorage.setItem('lastUserInput', userMessage);

        displayMessage(userMessage, true);

        const response = await fetch('/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                user_input: userMessage,
                programming_language: localStorage.getItem('selectedProgrammingLanguage'),
                llm_code: localStorage.getItem('selectedLLMCode'),
            })
        });

        const data = await response.json();
        console.log(data);
        displayMessage(data.message, false);

        localStorage.setItem('sourceCodeOutput', data.message);
        localStorage.setItem('sourceCodeGenerated', 'true');

    } catch (error) {
        console.error('Error:', error);
    }
}

// Event Listeners
submitButton.addEventListener('click', () => {
    const message = input.value.trim();
    if (message) {
        getMessage(message);
        input.value = '';
    }
});

input.addEventListener('keypress', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        submitButton.click();
    }
});

// Load previous conversation if exists
if (localStorage.getItem('sourceCodeOutput')) {
    displayMessage(localStorage.getItem('lastUserInput'), true);
    displayMessage(localStorage.getItem('sourceCodeOutput'), false);
}
