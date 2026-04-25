<!-- AI Chatbot FAB Button -->
<button class="chat-fab" onclick="toggleChatbot()" title="HRIS Assistant">
    <svg class="chat-fab-icon-open" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
    </svg>
    <svg class="chat-fab-icon-close" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: none;">
        <line x1="18" y1="6" x2="6" y2="18"/>
        <line x1="6" y1="6" x2="18" y2="18"/>
    </svg>
    <span class="chat-fab-badge">AI</span>
</button>

<!-- Chatbot Window -->
<div class="chatbot-window" id="chatbotWindow" style="display: none;">
    <div class="chatbot-header">
        <div class="chatbot-header-left">
            <div class="chatbot-avatar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div>
                <p class="chatbot-name">PRIME HRIS Assistant</p>
                <p class="chatbot-status">● Online</p>
            </div>
        </div>
        <button class="chatbot-close" onclick="toggleChatbot()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="chatbot-messages" id="chatbotMessages">
        <div class="chat-msg bot">
            <div class="chat-msg-avatar">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="chat-msg-bubble">Hello! I'm the PRIME HRIS Assistant. I can help you with employee information, departments, and HR data. I understand natural questions like "How many people work here?" or "Find John Doe" or "Who's in the Mayor's office?" Try asking me anything!</div>
        </div>
    </div>

    <div class="chatbot-faqs">
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('How many people work here?')">Total employees</button>
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('Show me active employees')">Active staff</button>
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('Who works in Mayor office?')">Mayor's Office</button>
        <button class="chatbot-faq-btn" onclick="sendPredefinedMessage('Find administrator')">Find employee</button>
    </div>

    <div class="chatbot-input-row">
        <input type="text" id="chatInput" placeholder="Type your question..." onkeypress="handleChatKeyPress(event)">
        <button class="chatbot-speaker" id="speakerButton" onclick="toggleSpeaker()" title="Stop speaking">
            <svg id="speakerIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                <path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>
            </svg>
            <svg id="speakerMutedIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                <line x1="23" y1="9" x2="17" y2="15"></line>
                <line x1="17" y1="9" x2="23" y2="15"></line>
            </svg>
        </button>
        <button class="chatbot-mic" id="micButton" onclick="toggleVoiceInput()" title="Voice input">
            <svg id="micIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                <line x1="12" y1="19" x2="12" y2="23"></line>
                <line x1="8" y1="23" x2="16" y2="23"></line>
            </svg>
            <svg id="micActiveIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: none;">
                <circle cx="12" cy="12" r="10" fill="#ef4444"></circle>
                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z" stroke="white"></path>
            </svg>
        </button>
        <button class="chatbot-send" onclick="sendChatMessage()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <line x1="22" y1="2" x2="11" y2="13"/>
                <polygon points="22 2 15 22 11 13 2 9 22 2"/>
            </svg>
        </button>
    </div>
</div>

<script>
let recognition = null;
let isListening = false;
let isSpeaking = false;
let speechSynthesis = window.speechSynthesis;
let currentUtterance = null;

if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.lang = 'fil-PH'; // Filipino language
    
    recognition.onresult = function(event) {
        const transcript = event.results[0][0].transcript;
        document.getElementById('chatInput').value = transcript;
        stopVoiceInput();
        // Automatically submit the message after voice input
        setTimeout(() => {
            sendChatMessage();
        }, 500); // Small delay to show the transcribed text
    };
    
    recognition.onerror = function(event) {
        console.error('Speech recognition error:', event.error);
        stopVoiceInput();
        if (event.error === 'no-speech') {
            addChatMessage('bot', 'Hindi ko narinig ang iyong sinabi. Subukan ulit.');
            speakText('Hindi ko narinig ang iyong sinabi. Subukan ulit.');
        } else if (event.error === 'not-allowed') {
            addChatMessage('bot', 'Microphone access denied. Please enable microphone permissions.');
            speakText('Microphone access denied. Please enable microphone permissions.');
        }
    };
    
    recognition.onend = function() {
        stopVoiceInput();
    };
}

function speakText(text) {
    // Stop any ongoing speech
    if (isSpeaking) {
        speechSynthesis.cancel();
    }
    
    // Clean text from HTML tags and markdown
    let cleanText = text.replace(/<[^>]*>/g, '');
    cleanText = cleanText.replace(/\*\*(.+?)\*\*/g, '$1');
    cleanText = cleanText.replace(/[📍💼🏢📧📊📅🎂⚧💑👤💰⏱️📋🎓🏫📚💼🏠📞]/g, '');
    
    // Create speech utterance
    currentUtterance = new SpeechSynthesisUtterance(cleanText);
    
    // Use Filipino/Tagalog voice - prefer Google voices
    const voices = speechSynthesis.getVoices();
    const filipinoVoice = voices.find(voice => 
        (voice.lang.includes('fil') || voice.lang.includes('tl')) && voice.name.includes('Google')
    ) || voices.find(voice => 
        voice.lang.includes('fil') || voice.lang.includes('tl')
    );
    
    if (filipinoVoice) {
        currentUtterance.voice = filipinoVoice;
        currentUtterance.lang = filipinoVoice.lang;
        console.log('Using voice:', filipinoVoice.name, filipinoVoice.lang);
    } else {
        currentUtterance.lang = 'fil-PH';
        console.log('No Filipino voice found, using default fil-PH');
    }
    
    currentUtterance.rate = 0.9; // Slightly slower for clarity
    currentUtterance.pitch = 1.0;
    currentUtterance.volume = 1.0;
    
    currentUtterance.onstart = function() {
        isSpeaking = true;
        updateSpeakerIcon(true);
    };
    
    currentUtterance.onend = function() {
        isSpeaking = false;
        updateSpeakerIcon(false);
    };
    
    currentUtterance.onerror = function(event) {
        console.error('Speech synthesis error:', event);
        isSpeaking = false;
        updateSpeakerIcon(false);
    };
    
    speechSynthesis.speak(currentUtterance);
}

function stopSpeaking() {
    if (isSpeaking) {
        speechSynthesis.cancel();
        isSpeaking = false;
        updateSpeakerIcon(false);
    }
}

function updateSpeakerIcon(speaking) {
    const speakerBtn = document.getElementById('speakerButton');
    if (speakerBtn) {
        if (speaking) {
            speakerBtn.classList.add('speaking');
        } else {
            speakerBtn.classList.remove('speaking');
        }
    }
}

function toggleSpeaker() {
    if (isSpeaking) {
        stopSpeaking();
    }
}

function toggleVoiceInput() {
    if (!recognition) {
        addChatMessage('bot', 'Speech recognition is not supported in your browser. Please use Chrome, Edge, or Safari.');
        return;
    }
    
    if (isListening) {
        stopVoiceInput();
    } else {
        startVoiceInput();
    }
}

function startVoiceInput() {
    // Stop speaking if currently speaking
    stopSpeaking();
    
    isListening = true;
    const micButton = document.getElementById('micButton');
    const micIcon = document.getElementById('micIcon');
    const micActiveIcon = document.getElementById('micActiveIcon');
    const chatInput = document.getElementById('chatInput');
    
    micButton.classList.add('listening');
    micIcon.style.display = 'none';
    micActiveIcon.style.display = 'block';
    chatInput.placeholder = 'Nakikinig...';
    
    recognition.start();
}

function stopVoiceInput() {
    isListening = false;
    const micButton = document.getElementById('micButton');
    const micIcon = document.getElementById('micIcon');
    const micActiveIcon = document.getElementById('micActiveIcon');
    const chatInput = document.getElementById('chatInput');
    
    micButton.classList.remove('listening');
    micIcon.style.display = 'block';
    micActiveIcon.style.display = 'none';
    chatInput.placeholder = 'Type your question...';
    
    if (recognition) {
        recognition.stop();
    }
}

// Load voices when available
if (speechSynthesis.onvoiceschanged !== undefined) {
    speechSynthesis.onvoiceschanged = function() {
        speechSynthesis.getVoices();
    };
}

// Stop speech when page is about to unload/refresh
window.addEventListener('beforeunload', function() {
    stopSpeaking();
});

function toggleChatbot() {
    const window = document.getElementById('chatbotWindow');
    const fab = document.querySelector('.chat-fab');
    const openIcon = document.querySelector('.chat-fab-icon-open');
    const closeIcon = document.querySelector('.chat-fab-icon-close');
    const badge = document.querySelector('.chat-fab-badge');
    
    if (window.style.display === 'none') {
        window.style.display = 'flex';
        fab.classList.add('open');
        openIcon.style.display = 'none';
        closeIcon.style.display = 'block';
        badge.style.display = 'none';
    } else {
        window.style.display = 'none';
        fab.classList.remove('open');
        openIcon.style.display = 'block';
        closeIcon.style.display = 'none';
        badge.style.display = 'block';
    }
}

function sendChatMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    addChatMessage('user', message);
    input.value = '';
    
    addTypingIndicator();
    
    fetch('http://127.0.0.1:5001/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message: message })
    })
    .then(response => response.json())
    .then(data => {
        removeTypingIndicator();
        if (data.status === 'success') {
            addChatMessage('bot', data.response);
            // Automatically speak the bot's response
            speakText(data.response);
        } else {
            addChatMessage('bot', 'Sorry, I encountered an error. Please try again.');
        }
    })
    .catch(error => {
        removeTypingIndicator();
        console.error('Error:', error);
        addChatMessage('bot', 'Sorry, I couldn\'t process your request. Please try again.');
    });
}

function sendPredefinedMessage(message) {
    document.getElementById('chatInput').value = message;
    sendChatMessage();
}

function addChatMessage(from, text) {
    const messagesContainer = document.getElementById('chatbotMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-msg ${from}`;
    
    // Convert markdown-style formatting to HTML
    text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/\n/g, '<br>');
    
    if (from === 'bot') {
        messageDiv.innerHTML = `
            <div class="chat-msg-avatar">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="chat-msg-bubble">${text}</div>
        `;
    } else {
        messageDiv.innerHTML = `<div class="chat-msg-bubble">${text}</div>`;
    }
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function addTypingIndicator() {
    const messagesContainer = document.getElementById('chatbotMessages');
    const typingDiv = document.createElement('div');
    typingDiv.className = 'chat-msg bot typing-indicator';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = `
        <div class="chat-msg-avatar">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </div>
        <div class="chat-msg-bubble">Typing...</div>
    `;
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function removeTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) {
        indicator.remove();
    }
}

function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendChatMessage();
    }
}
</script>
