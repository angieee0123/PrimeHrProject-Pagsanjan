<div class="admin-chatbot-ui">
@include('admin.chatbot.partials.fab-button')

<!-- Chatbot Window -->
<div class="chatbot-window" id="chatbotWindow" style="display: none;">
    @include('admin.chatbot.partials.chatbot-header')

    @include('admin.chatbot.partials.quick-actions')

    @include('admin.chatbot.partials.messages')

    @include('admin.chatbot.partials.input-row')
</div>
</div>

<script>
let recognition = null;
let wakeWordRecognition = null;
let isListening = false;
let isWakeWordListening = false;
let isSpeaking = false;
let speechSynthesis = window.speechSynthesis;
let currentUtterance = null;

if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

    // Wake word recognition (always listening)
    wakeWordRecognition = new SpeechRecognition();
    wakeWordRecognition.continuous = true;
    wakeWordRecognition.interimResults = true;
    wakeWordRecognition.lang = 'fil-PH';

    wakeWordRecognition.onresult = function(event) {
        const last = event.results.length - 1;
        const transcript = event.results[last][0].transcript.toLowerCase().trim();
        console.log('Wake word detection:', transcript);

        if (transcript.includes('hey anna') || transcript.includes('ei anna') || transcript.includes('ey anna') || transcript.includes('hay anna')) {
            console.log('Wake word detected: Hey Anna!');
            playWakeSound();

            // Open chatbot if closed
            const chatWindow = document.getElementById('chatbotWindow');
            if (chatWindow.style.display === 'none') {
                toggleChatbot();
            }

            stopWakeWordListening();
            setTimeout(() => {
                startVoiceInput();
            }, 300); // Small delay after sound
        }
    };

    wakeWordRecognition.onerror = function(event) {
        if (event.error !== 'no-speech') {
            console.error('Wake word error:', event.error);
        }
    };

    wakeWordRecognition.onend = function() {
        if (isWakeWordListening) {
            setTimeout(() => {
                try { wakeWordRecognition.start(); } catch(e) {}
            }, 100);
        }
    };

    // Normal recognition
    recognition = new SpeechRecognition();
    recognition.continuous = true; // Keep listening continuously
    recognition.interimResults = true; // Show interim results
    recognition.lang = 'fil-PH'; // Filipino language

    recognition.onresult = function(event) {
        const last = event.results.length - 1;
        const transcript = event.results[last][0].transcript;
        const isFinal = event.results[last].isFinal;

        document.getElementById('chatInput').value = transcript;

        // Auto-send when speech is final and there's a pause
        if (isFinal && transcript.trim()) {
            setTimeout(() => {
                if (isListening) {
                    stopVoiceInput();
                    sendChatMessage();
                }
            }, 1000); // 1 second pause before auto-sending
        }
    };

    recognition.onerror = function(event) {
        console.error('Speech recognition error:', event.error);
        if (event.error === 'not-allowed') {
            stopVoiceInput();
            addChatMessage('bot', 'Microphone access denied. Please enable microphone permissions in your browser settings.');
        } else if (event.error === 'aborted' || event.error === 'no-speech') {
            // non-fatal, let onend handle restart
        } else {
            stopVoiceInput();
        }
    };

    recognition.onend = function() {
        if (isListening) {
            try { recognition.start(); } catch(e) { console.warn('Recognition restart:', e.message); }
        } else {
            startWakeWordListening();
        }
    };
}

function startWakeWordListening() {
    if (!wakeWordRecognition || isWakeWordListening) return;

    isWakeWordListening = true;
    try {
        wakeWordRecognition.start();
        console.log('Wake word listening started - say "Hey PRIME"');
    } catch(e) {
        console.error('Wake word start error:', e);
    }
}

function stopWakeWordListening() {
    if (!wakeWordRecognition || !isWakeWordListening) return;

    isWakeWordListening = false;
    try {
        wakeWordRecognition.stop();
        console.log('Wake word listening stopped');
    } catch(e) {}
}

function playWakeSound() {
    // Create Siri-like activation sound using Web Audio API
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = audioContext.createOscillator();
    const gainNode = audioContext.createGain();

    oscillator.connect(gainNode);
    gainNode.connect(audioContext.destination);

    // Siri-like double beep
    oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
    oscillator.frequency.setValueAtTime(1000, audioContext.currentTime + 0.1);

    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

    oscillator.start(audioContext.currentTime);
    oscillator.stop(audioContext.currentTime + 0.2);
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

    // Use Filipino/Tagalog voice - prefer macOS native voices
    const voices = speechSynthesis.getVoices();

    // Priority order for voices:
    // 1. macOS native Filipino voices (Amelie, Lekha)
    // 2. Google Filipino voices
    // 3. Any Filipino voice
    const filipinoVoice = voices.find(voice =>
        (voice.lang.includes('fil') || voice.lang.includes('tl')) &&
        (voice.name.includes('Amelie') || voice.name.includes('Lekha'))
    ) || voices.find(voice =>
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
    stopSpeaking();
    stopWakeWordListening();

    isListening = true;
    const micButton = document.getElementById('micButton');
    const micIcon = document.getElementById('micIcon');
    const micActiveIcon = document.getElementById('micActiveIcon');
    const chatInput = document.getElementById('chatInput');

    micButton.classList.add('listening');
    micIcon.style.display = 'none';
    micActiveIcon.style.display = 'block';
    chatInput.placeholder = 'Nakikinig...';

    try { recognition.start(); } catch(e) { console.warn('Recognition start:', e.message); }
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

    try { recognition.stop(); } catch(e) {}
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

// Start wake word listening when page loads
window.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        startWakeWordListening();
        console.log('🎤 Ready! Say "Hey Anna" to activate chatbot');
    }, 2000);

    restoreChatbotUiState();
    restoreChatbotHistory();
});

let isFullscreen = false;

// Widget open/closed and fullscreen state survive page navigations (this
// partial is re-rendered fresh on every admin page load) via localStorage.
function restoreChatbotUiState() {
    if (localStorage.getItem('chatbotOpen') === 'true') {
        const chatWindow = document.getElementById('chatbotWindow');
        const fab = document.querySelector('.chat-fab');

        chatWindow.style.display = 'flex';
        fab.classList.add('open');
        document.querySelector('.chat-fab-icon-open').style.display = 'none';
        document.querySelector('.chat-fab-icon-close').style.display = 'block';
        document.querySelector('.chat-fab-badge').style.display = 'none';
    }

    if (localStorage.getItem('chatbotFullscreen') === 'true') {
        isFullscreen = true;
        document.getElementById('chatbotWindow').classList.add('fullscreen-mode');
        document.getElementById('fullscreenIcon').style.display = 'none';
        document.getElementById('fullscreenExitIcon').style.display = 'block';
    }
}

// The conversation itself lives in the Laravel session (see ChatbotController)
// so re-fetch and re-render it here instead of showing the static greeting.
function restoreChatbotHistory() {
    fetch('/chatbot/history', { headers: { 'Accept': 'application/json' } })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && Array.isArray(data.history) && data.history.length > 0) {
                const messagesContainer = document.getElementById('chatbotMessages');
                messagesContainer.innerHTML = '';
                data.history.forEach(turn => {
                    addChatMessage(turn.role === 'user' ? 'user' : 'bot', turn.content);
                });
            }
        })
        .catch(error => console.error('Error restoring chatbot history:', error));
}

function toggleFullscreen() {
    const chatWindow = document.getElementById('chatbotWindow');
    const fullscreenIcon = document.getElementById('fullscreenIcon');
    const fullscreenExitIcon = document.getElementById('fullscreenExitIcon');

    isFullscreen = !isFullscreen;
    localStorage.setItem('chatbotFullscreen', isFullscreen ? 'true' : 'false');

    if (isFullscreen) {
        chatWindow.classList.add('fullscreen-mode');
        fullscreenIcon.style.display = 'none';
        fullscreenExitIcon.style.display = 'block';
    } else {
        chatWindow.classList.remove('fullscreen-mode');
        fullscreenIcon.style.display = 'block';
        fullscreenExitIcon.style.display = 'none';
    }
}

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
        localStorage.setItem('chatbotOpen', 'true');
    } else {
        window.style.display = 'none';
        fab.classList.remove('open');
        openIcon.style.display = 'block';
        closeIcon.style.display = 'none';
        badge.style.display = 'block';
        stopVoiceInput();
        localStorage.setItem('chatbotOpen', 'false');
    }
}

function sendChatMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();

    if (!message) return;

    addChatMessage('user', message);
    input.value = '';

    addTypingIndicator();

    fetch('/chatbot/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
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

function clearChatbotConversation() {
    if (!confirm('Clear the conversation?')) return;

    fetch('/chatbot/chat', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ message: '', reset: true })
    }).catch(error => console.error('Error clearing chatbot memory:', error));

    const messagesContainer = document.getElementById('chatbotMessages');
    messagesContainer.innerHTML = `
        <div class="chat-msg bot">
            <div class="chat-msg-avatar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
            </div>
            <div class="chat-msg-bubble">Hello! I'm the PRIME HRIS Assistant. I can help you with employee information, departments, and HR data. I understand natural questions like "How many people work here?" or "Find John Doe" or "Who's in the Mayor's office?" Try asking me anything!</div>
        </div>
    `;
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
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
