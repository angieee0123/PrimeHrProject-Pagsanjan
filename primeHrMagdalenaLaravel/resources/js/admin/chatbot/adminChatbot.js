let recognition = null;
let wakeWordRecognition = null;
let isListening = false;
let isWakeWordListening = false;
let isSpeaking = false;
let speechSynthesis = window.speechSynthesis;
let currentUtterance = null;
let isAwaitingReply = false;
// Captured at load so "Clear conversation" can put the starter prompts back.
let chatSuggestHtml = '';

const CHATBOT_GREETING = "Hello! I'm the PRIME HRIS Assistant. I can help you with employee information, departments, and HR data. I understand natural questions like \"How many people work here?\" or \"Find John Doe\" or \"Who's in the Mayor's office?\" Try asking me anything!";
const CHATBOT_BOT_ICON = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';

function chatbotTimestamp() {
    return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function escapeChatHtml(text) {
    const el = document.createElement('div');
    el.textContent = text;
    return el.innerHTML;
}

/* Escape first, then apply the markdown subset the assistant emits. Replies are
   built from HR records, so raw interpolation into innerHTML let any stray
   markup in a name or remark render as live HTML. Bullet runs become a real
   list and blank lines become paragraphs — every newline used to be a <br>,
   which flattened long answers into one dense block. */
function formatChatMessage(raw) {
    const lines = escapeChatHtml(raw).split('\n');
    let html = '';
    let inList = false;

    lines.forEach(line => {
        const bullet = line.match(/^\s*[•\-*]\s+(.*)$/);
        if (bullet) {
            if (!inList) { html += '<ul class="chat-list">'; inList = true; }
            html += '<li>' + bullet[1] + '</li>';
            return;
        }
        if (inList) { html += '</ul>'; inList = false; }
        if (line.trim() !== '') html += '<p class="chat-p">' + line + '</p>';
    });
    if (inList) html += '</ul>';

    return html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
}

function scrollChatToBottom() {
    const container = document.getElementById('chatbotMessages');
    container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
}

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
            if (!chatWindow.classList.contains('open')) {
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
    applyMicChrome(true);

    try { recognition.start(); } catch(e) { console.warn('Recognition start:', e.message); }
}

function stopVoiceInput() {
    isListening = false;
    applyMicChrome(false);

    try { recognition.stop(); } catch(e) {}
}

function applyMicChrome(listening) {
    const micButton = document.getElementById('micButton');
    const chatInput = document.getElementById('chatInput');

    micButton.classList.toggle('listening', listening);
    micButton.setAttribute('aria-pressed', String(listening));
    micButton.setAttribute('aria-label', listening ? 'Stop voice input' : 'Voice input');
    micButton.title = listening ? 'Stop listening' : 'Voice input';
    document.getElementById('micIcon').classList.toggle('cb-hidden', listening);
    document.getElementById('micActiveIcon').classList.toggle('cb-hidden', !listening);
    chatInput.placeholder = listening ? 'Nakikinig…' : 'Type your question…';
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

    const suggestions = document.getElementById('chatSuggestions');
    if (suggestions) chatSuggestHtml = suggestions.outerHTML;

    const greeting = document.getElementById('chatbotGreeting');
    if (greeting) {
        greeting.innerHTML = formatChatMessage(CHATBOT_GREETING)
            + '<span class="chat-ts">' + chatbotTimestamp() + '</span>';
    }

    const input = document.getElementById('chatInput');
    if (input) {
        input.addEventListener('input', function () {
            autosizeChatInput();
            syncChatSendState();
        });
        input.addEventListener('keydown', handleChatKeyPress);
    }

    restoreChatbotUiState();
    restoreChatbotHistory();
});

// Escape backs out of the confirmation first, then the panel.
document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (isClearConfirmOpen()) { closeClearConfirm(); return; }

    const chatWindow = document.getElementById('chatbotWindow');
    if (chatWindow && chatWindow.classList.contains('open')) toggleChatbot();
});

let isFullscreen = false;

// Widget open/closed and fullscreen state survive page navigations (this
// partial is re-rendered fresh on every admin page load) via localStorage.
function restoreChatbotUiState() {
    if (localStorage.getItem('chatbotOpen') === 'true') {
        const chatWindow = document.getElementById('chatbotWindow');
        const fab = document.querySelector('.chat-fab');

        // The ✕ icon and the hidden badge are driven by .chat-fab.open in CSS,
        // so restoring the state is a single class either side.
        chatWindow.classList.add('open');
        chatWindow.setAttribute('aria-hidden', 'false');
        fab.classList.add('open');
        fab.setAttribute('aria-expanded', 'true');
        fab.setAttribute('aria-label', 'Close HRIS assistant');
    }

    if (localStorage.getItem('chatbotFullscreen') === 'true') {
        isFullscreen = true;
        document.getElementById('chatbotWindow').classList.add('fullscreen-mode');
        applyFullscreenChrome(true);
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
                    addChatMessage(turn.role === 'user' ? 'user' : 'bot', turn.content, false);
                });
            }
        })
        .catch(error => console.error('Error restoring chatbot history:', error));
}

function applyFullscreenChrome(expanded) {
    const button = document.getElementById('fullscreenButton');
    document.getElementById('fullscreenIcon').classList.toggle('cb-hidden', expanded);
    document.getElementById('fullscreenExitIcon').classList.toggle('cb-hidden', !expanded);
    button.setAttribute('aria-pressed', String(expanded));
    button.setAttribute('aria-label', expanded ? 'Shrink panel' : 'Expand panel');
    button.title = expanded ? 'Shrink panel' : 'Expand panel';
}

function toggleFullscreen() {
    const chatWindow = document.getElementById('chatbotWindow');

    isFullscreen = !isFullscreen;
    localStorage.setItem('chatbotFullscreen', isFullscreen ? 'true' : 'false');

    chatWindow.classList.toggle('fullscreen-mode', isFullscreen);
    applyFullscreenChrome(isFullscreen);
}

function toggleChatbot() {
    const chatWindow = document.getElementById('chatbotWindow');
    const fab = document.querySelector('.chat-fab');
    const isOpen = chatWindow.classList.contains('open');

    chatWindow.classList.toggle('open', !isOpen);
    chatWindow.setAttribute('aria-hidden', String(isOpen));
    fab.classList.toggle('open', !isOpen);
    fab.setAttribute('aria-expanded', String(!isOpen));
    fab.setAttribute('aria-label', isOpen ? 'Open HRIS assistant' : 'Close HRIS assistant');
    localStorage.setItem('chatbotOpen', isOpen ? 'false' : 'true');

    if (isOpen) {
        stopVoiceInput();
        fab.focus();
    } else {
        const input = document.getElementById('chatInput');
        if (input) input.focus();
        scrollChatToBottom();
    }
}

function sendChatMessage() {
    if (isAwaitingReply) return;

    const input = document.getElementById('chatInput');
    const message = input.value.trim();

    if (!message) return;

    addChatMessage('user', message);
    input.value = '';
    autosizeChatInput();
    setChatAwaiting(true);

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
    })
    .finally(() => setChatAwaiting(false));
}

function sendPredefinedMessage(message) {
    if (isAwaitingReply) return;
    document.getElementById('chatInput').value = message;
    sendChatMessage();
}

/* Opens the in-panel sheet rather than a browser confirm(), and names what is
   actually at stake — the count of messages, and the fact that this also wipes
   the assistant's server-side memory of the thread, which the old one-liner
   never said. */
function clearChatbotConversation() {
    const messages = document.querySelectorAll('#chatbotMessages .chat-msg').length;

    document.getElementById('chatClearConfirmText').textContent = messages <= 1
        ? 'There is nothing to clear yet — this chat only has the welcome message.'
        : 'This removes all ' + messages + ' messages and resets what the assistant '
          + 'remembers of this thread. Employee records are not affected.';

    document.getElementById('chatClearConfirm').classList.add('is-open');
    document.getElementById('chatClearConfirmCancel').focus();
}

function closeClearConfirm() {
    document.getElementById('chatClearConfirm').classList.remove('is-open');
    const input = document.getElementById('chatInput');
    if (input) input.focus();
}

function isClearConfirmOpen() {
    const sheet = document.getElementById('chatClearConfirm');
    return !!sheet && sheet.classList.contains('is-open');
}

function confirmClearChatbotConversation() {
    closeClearConfirm();

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
    messagesContainer.innerHTML = '';
    addChatMessage('bot', CHATBOT_GREETING);
    messagesContainer.insertAdjacentHTML('beforeend', chatSuggestHtml);
}

// withTime is false for turns replayed from session history, which carry no
// timestamp of their own — stamping them "now" would be a lie.
function removeChatSuggestions() {
    const el = document.getElementById('chatSuggestions');
    if (el) el.remove();
}

function addChatMessage(from, text, withTime = true) {
    const messagesContainer = document.getElementById('chatbotMessages');
    if (from === 'user') removeChatSuggestions();

    const messageDiv = document.createElement('div');
    messageDiv.className = `chat-msg ${from}`;

    if (from === 'bot') {
        const avatar = document.createElement('div');
        avatar.className = 'chat-msg-avatar';
        avatar.innerHTML = CHATBOT_BOT_ICON;
        messageDiv.appendChild(avatar);
    }

    const bubble = document.createElement('div');
    bubble.className = 'chat-msg-bubble';
    bubble.innerHTML = formatChatMessage(text);

    if (withTime) {
        const ts = document.createElement('span');
        ts.className = 'chat-ts';
        ts.textContent = chatbotTimestamp();
        bubble.appendChild(ts);
    }

    messageDiv.appendChild(bubble);
    messagesContainer.appendChild(messageDiv);
    scrollChatToBottom();
}

function addTypingIndicator() {
    const messagesContainer = document.getElementById('chatbotMessages');
    const typingDiv = document.createElement('div');
    typingDiv.className = 'chat-msg bot';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = '<div class="chat-msg-avatar">' + CHATBOT_BOT_ICON + '</div>'
        + '<div class="chat-typing-indicator" role="status" aria-label="Assistant is typing">'
        + '<span></span><span></span><span></span></div>';
    messagesContainer.appendChild(typingDiv);
    scrollChatToBottom();
}

function removeTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    if (indicator) {
        indicator.remove();
    }
}

// Locks the composer while a request is in flight, so a slow reply cannot be
// overtaken by a second question.
function setChatAwaiting(awaiting) {
    isAwaitingReply = awaiting;
    const input = document.getElementById('chatInput');
    input.disabled = awaiting;
    syncChatSendState();
    if (!awaiting) input.focus();
}

function syncChatSendState() {
    const input = document.getElementById('chatInput');
    const button = document.getElementById('chatSendButton');
    if (input && button) button.disabled = isAwaitingReply || input.value.trim() === '';
}

function autosizeChatInput() {
    const input = document.getElementById('chatInput');
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 96) + 'px';
}

// Enter sends; Shift+Enter is a new line.
function handleChatKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendChatMessage();
    }
}

window.toggleChatbot = toggleChatbot;
window.clearChatbotConversation = clearChatbotConversation;
window.confirmClearChatbotConversation = confirmClearChatbotConversation;
window.closeClearConfirm = closeClearConfirm;
window.toggleFullscreen = toggleFullscreen;
window.sendPredefinedMessage = sendPredefinedMessage;
window.toggleSpeaker = toggleSpeaker;
window.toggleVoiceInput = toggleVoiceInput;
window.sendChatMessage = sendChatMessage;
window.handleChatKeyPress = handleChatKeyPress;
