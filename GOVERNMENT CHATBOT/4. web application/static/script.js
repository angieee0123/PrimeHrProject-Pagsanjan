const chatContainer = document.getElementById('chatContainer');
const userInput = document.getElementById('userInput');

function getTimestamp() {
    const now = new Date();
    return now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function addMessage(text, isUser, followUpQuestions = [], fullResponse = null) {
    const messageWrapper = document.createElement('div');
    messageWrapper.className = `message-wrapper ${isUser ? 'user-wrapper' : 'bot-wrapper'}`;
    
    if (!isUser) {
        const avatar = document.createElement('div');
        avatar.className = 'avatar bot-avatar';
        const img = document.createElement('img');
        img.src = '/static/LGU-Sampaloc-Quezon-Official-Logo-scaled.png';
        img.alt = 'Bot Avatar';
        avatar.appendChild(img);
        messageWrapper.appendChild(avatar);
    }
    
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${isUser ? 'user-message' : 'bot-message'}`;
    
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/\n/g, '<br>');
    
    const timestamp = document.createElement('span');
    timestamp.className = 'timestamp';
    timestamp.textContent = getTimestamp();
    
    messageDiv.innerHTML = text;
    messageDiv.appendChild(timestamp);
    messageWrapper.appendChild(messageDiv);
    
    if (isUser) {
        const avatar = document.createElement('div');
        avatar.className = 'avatar user-avatar';
        avatar.textContent = '👤';
        messageWrapper.appendChild(avatar);
    }
    
    chatContainer.appendChild(messageWrapper);
    
    if (!isUser && fullResponse && fullResponse !== text) {
        const toggleDiv = document.createElement('div');
        toggleDiv.className = 'toggle-details';
        
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'toggle-btn';
        toggleBtn.textContent = '📄 See More';
        toggleBtn.onclick = () => {
            if (toggleBtn.textContent === '📄 See More') {
                const fullText = fullResponse.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
                messageDiv.innerHTML = fullText;
                messageDiv.appendChild(timestamp);
                toggleBtn.textContent = '📄 See Less';
            } else {
                messageDiv.innerHTML = text;
                messageDiv.appendChild(timestamp);
                toggleBtn.textContent = '📄 See More';
            }
        };
        
        toggleDiv.appendChild(toggleBtn);
        chatContainer.appendChild(toggleDiv);
    }
    
    if (!isUser && followUpQuestions && followUpQuestions.length > 0) {
        const followUpDiv = document.createElement('div');
        followUpDiv.className = 'follow-up-container';
        
        const label = document.createElement('p');
        label.className = 'follow-up-label';
        label.textContent = '💡 You might also want to ask:';
        followUpDiv.appendChild(label);
        
        followUpQuestions.forEach(question => {
            const button = document.createElement('button');
            button.className = 'follow-up-button';
            button.textContent = question;
            button.onclick = () => {
                userInput.value = question;
                sendMessage();
            };
            followUpDiv.appendChild(button);
        });
        
        chatContainer.appendChild(followUpDiv);
    }
    
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function showTypingIndicator() {
    const messageWrapper = document.createElement('div');
    messageWrapper.className = 'message-wrapper bot-wrapper';
    messageWrapper.id = 'typingWrapper';
    
    const avatar = document.createElement('div');
    avatar.className = 'avatar bot-avatar';
    const img = document.createElement('img');
    img.src = '/static/LGU-Sampaloc-Quezon-Official-Logo-scaled.png';
    img.alt = 'Bot Avatar';
    avatar.appendChild(img);
    messageWrapper.appendChild(avatar);
    
    const typingDiv = document.createElement('div');
    typingDiv.className = 'typing-indicator';
    typingDiv.innerHTML = '<span></span><span></span><span></span>';
    messageWrapper.appendChild(typingDiv);
    
    chatContainer.appendChild(messageWrapper);
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

function removeTypingIndicator() {
    const typingWrapper = document.getElementById('typingWrapper');
    if (typingWrapper) {
        typingWrapper.remove();
    }
}

function clearChat() {
    if (confirm('Are you sure you want to clear the conversation?')) {
        chatContainer.innerHTML = '';
        const messageWrapper = document.createElement('div');
        messageWrapper.className = 'message-wrapper bot-wrapper';
        
        const avatar = document.createElement('div');
        avatar.className = 'avatar bot-avatar';
        const img = document.createElement('img');
        img.src = '/static/LGU-Sampaloc-Quezon-Official-Logo-scaled.png';
        img.alt = 'Bot Avatar';
        avatar.appendChild(img);
        messageWrapper.appendChild(avatar);
        
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message bot-message';
        messageDiv.innerHTML = '<p>Hello! I\'m here to help you with information about municipal services. How can I assist you today?</p>';
        
        const timestamp = document.createElement('span');
        timestamp.className = 'timestamp';
        timestamp.textContent = getTimestamp();
        messageDiv.appendChild(timestamp);
        
        messageWrapper.appendChild(messageDiv);
        chatContainer.appendChild(messageWrapper);
    }
}

function quickAsk(question) {
    userInput.value = question;
    sendMessage();
}

async function sendMessage() {
    const message = userInput.value.trim();
    
    if (!message) return;
    
    addMessage(message, true);
    userInput.value = '';
    
    showTypingIndicator();
    
    try {
        const response = await fetch('/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: message })
        });
        
        const data = await response.json();
        
        removeTypingIndicator();
        
        if (data.error) {
            addMessage('Sorry, something went wrong. Please try again.', false);
        } else {
            addMessage(data.response, false, data.follow_up_questions || [], data.full_response || null);
        }
    } catch (error) {
        removeTypingIndicator();
        addMessage('Sorry, I could not connect to the server. Please try again.', false);
    }
}

userInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        sendMessage();
    }
});