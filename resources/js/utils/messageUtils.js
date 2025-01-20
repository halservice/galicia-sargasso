import { marked } from 'marked';
/**
 * Displays a message in the specified chat container
 * @param {string} message - The message to display
 * @param {boolean} isUser - Whether the message is from the user (true) or assistant (false)
 */
export function displayMessage(message, isUser = true) {
    const output=document.querySelector('#chat-messages')
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-message ${isUser ? 'user-message' : 'assistant-message'}`;

    if (isUser) {
        messageDiv.textContent = message;
    } else {
        messageDiv.innerHTML = marked.parse(message);
    }

    output.appendChild(messageDiv);
    output.scrollTop = output.scrollHeight;
}

