<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrimeHR Chatbot - With Laravel Session</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7f6ff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #0b044d;
        }

        .container {
            width: 100%;
            max-width: 840px;
            background: #fff;
            border: 1px solid #eceaf8;
            border-radius: 18px;
            box-shadow: 0 16px 46px rgba(11, 4, 77, 0.16);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%);
            color: #fff;
            padding: 20px;
            border-bottom: 1px solid #1f1675;
        }

        .chat-header h1 { font-size: 22px; margin-bottom: 8px; }
        .chat-header p { font-size: 13px; opacity: 0.92; margin-bottom: 5px; }

        .auth-status {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.2px;
            padding: 6px 10px;
            border-radius: 999px;
            display: inline-block;
            margin-top: 10px;
            background: rgba(255, 255, 255, 0.2);
        }

        .auth-status.authenticated { background: #16a34a; color: #fff; }
        .auth-status.unauthenticated { background: #dc2626; color: #fff; }

        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px 14px;
            min-height: 320px;
            max-height: 430px;
            background: radial-gradient(circle at top right, rgba(11, 4, 77, 0.06), transparent 45%), #fcfcff;
        }

        .message {
            margin-bottom: 12px;
            display: flex;
            flex-direction: column;
            animation: slideIn 0.22s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.user { align-items: flex-end; }
        .message.bot { align-items: flex-start; }

        .message-content {
            max-width: 78%;
            padding: 10px 13px;
            border-radius: 14px;
            word-wrap: break-word;
            font-size: 13px;
            line-height: 1.55;
        }

        .message.user .message-content {
            background: #0b044d;
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .message.bot .message-content {
            background: #f1f0ff;
            color: #0b044d;
            border-bottom-left-radius: 4px;
        }

        .message-meta {
            margin-top: 5px;
            font-size: 10.5px;
            color: #8b87ad;
            padding: 0 6px;
        }

        .typing-indicator { display: flex; gap: 4px; padding: 6px 2px; }
        .typing-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #9f9bc5;
            animation: typing 1.2s infinite;
        }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typing {
            0%, 60%, 100% { opacity: 0.35; transform: translateY(0); }
            30% { opacity: 1; transform: translateY(-6px); }
        }

        .input-area {
            padding: 12px;
            border-top: 1px solid #eceaf8;
            background: #fff;
        }

        .input-wrapper { display: flex; gap: 8px; }

        input[type="text"] {
            flex: 1;
            border: 1.5px solid #e4e3f0;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
            color: #0b044d;
            background: #fcfcff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input[type="text"]:focus {
            border-color: #0b044d;
            box-shadow: 0 0 0 3px rgba(11, 4, 77, 0.09);
            background: #fff;
        }

        button {
            border: none;
            border-radius: 10px;
            background: #0b044d;
            color: #fff;
            padding: 10px 18px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: transform 0.2s ease, background-color 0.2s ease;
        }

        button:hover { background: #8e1e18; transform: translateY(-1px); }
        button:disabled { background: #bbb7d6; cursor: not-allowed; transform: none; }

        .status {
            font-size: 12px;
            margin-top: 10px;
            padding: 9px 11px;
            border-radius: 8px;
            display: none;
            border: 1px solid transparent;
        }
        .status.show { display: block; }
        .status.success { background: #eaf8ef; color: #166534; border-color: #b6e5c6; }
        .status.error { background: #fdecec; color: #991b1b; border-color: #f8c2c2; }

        .demo-users {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #eceaf8;
            font-size: 12px;
        }
        .demo-users p { color: #65608a; margin-bottom: 8px; }

        .demo-btn {
            background: #f4f3ff;
            color: #0b044d;
            border: 1px solid #dddaf1;
            border-radius: 999px;
            padding: 6px 10px;
            margin-right: 5px;
            margin-bottom: 6px;
            font-size: 11px;
            font-weight: 500;
        }
        .demo-btn:hover { background: #0b044d; color: #fff; border-color: #0b044d; transform: none; }

        @media (max-width: 768px) {
            body { padding: 12px; }
            .container { border-radius: 14px; }
            .messages { max-height: 52vh; }
            .message-content { max-width: 88%; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="chat-header">
            <h1>🤖 PrimeHR Assistant</h1>
            <p id="headerInfo">Connecting to your Laravel session...</p>
            <div class="auth-status" id="authStatus">
                <span id="authStatusText">Loading...</span>
            </div>
        </div>

        <div class="messages" id="messages"></div>

        <div class="input-area">
            <div class="input-wrapper">
                <input
                    type="text"
                    id="messageInput"
                    placeholder="Ask me anything about the system..."
                    disabled
                />
                <button id="sendBtn" disabled>Send</button>
            </div>
            <div class="status" id="status"></div>

            <div class="demo-users">
                <p><strong>💡 Test as different users:</strong></p>
                <button class="demo-btn" onclick="setDemoUser(1, 'Maria Cruz')">Maria (ID: 1)</button>
                <button class="demo-btn" onclick="setDemoUser(6, 'Juan Dela Cruz')">Juan (ID: 6)</button>
                <button class="demo-btn" onclick="setDemoUser(8, 'Ana Ramos')">Ana (ID: 8)</button>
                <button class="demo-btn" onclick="clearMessages()">Clear Chat</button>
            </div>
        </div>
    </div>

    <script>
        let currentUser = null;
        const chatAPI = 'http://localhost:5001';
        const laravelAPI = 'http://localhost:8000';  // Adjust if needed

        // Initialize on page load
        async function init() {
            try {
                // ✅ FETCH USER ID FROM LARAVEL SESSION
                const response = await fetch(`${laravelAPI}/api/auth/user-id`, {
                    credentials: 'include'  // Include cookies for session
                });

                const data = await response.json();

                if (data.status === 'success') {
                    currentUser = {
                        id: data.user_id,
                        email: data.email,
                        name: data.name
                    };

                    // Update UI
                    document.getElementById('headerInfo').textContent = `Logged in as ${currentUser.name}`;
                    document.getElementById('authStatus').className = 'auth-status authenticated';
                    document.getElementById('authStatusText').textContent = `✅ Authenticated (ID: ${currentUser.id})`;

                    // Enable chat
                    document.getElementById('messageInput').disabled = false;
                    document.getElementById('sendBtn').disabled = false;

                    addMessage(`Welcome ${currentUser.name}! How can I help you today?`, 'bot');
                } else {
                    // Not authenticated
                    document.getElementById('headerInfo').textContent = 'Not logged in - Using demo mode';
                    document.getElementById('authStatus').className = 'auth-status unauthenticated';
                    document.getElementById('authStatusText').textContent = '❌ Not Authenticated (Demo Mode)';

                    addMessage('You are not logged in. Please log in to track your chat history. You can also test with demo users below.', 'bot');

                    // Enable chat for demo
                    document.getElementById('messageInput').disabled = false;
                    document.getElementById('sendBtn').disabled = false;
                }
            } catch (error) {
                console.error('Error fetching user:', error);

                document.getElementById('headerInfo').textContent = 'Demo Mode (Laravel connection failed)';
                document.getElementById('authStatus').className = 'auth-status unauthenticated';
                document.getElementById('authStatusText').textContent = '⚠️ Demo Mode';

                addMessage('Could not connect to Laravel. Running in demo mode.', 'bot');
                document.getElementById('messageInput').disabled = false;
                document.getElementById('sendBtn').disabled = false;
            }

            // Setup event listeners
            document.getElementById('sendBtn').onclick = sendMessage;
            document.getElementById('messageInput').onkeypress = (e) => {
                if (e.key === 'Enter') sendMessage();
            };
        }

        // Set demo user
        function setDemoUser(userId, userName) {
            currentUser = { id: userId, name: userName, email: `${userName.toLowerCase().replace(' ', '.')}@primehr.com` };
            document.getElementById('headerInfo').textContent = `Demo Mode: ${userName}`;
            addMessage(`Switched to demo user: ${userName} (ID: ${userId})`, 'bot');
        }

        // Send message
        async function sendMessage() {
            if (!currentUser) {
                showStatus('Please log in or select a demo user', 'error');
                return;
            }

            const input = document.getElementById('messageInput');
            const message = input.value.trim();

            if (!message) return;

            // Add user message
            addMessage(message, 'user');
            input.value = '';

            // Show typing
            const messagesDiv = document.getElementById('messages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot';
            typingDiv.innerHTML = '<div class="typing-indicator"><div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div></div>';
            messagesDiv.appendChild(typingDiv);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;

            try {
                // ✅ SEND MESSAGE WITH USER_ID FROM LARAVEL
                const response = await fetch(`${chatAPI}/chat`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        message: message,
                        user_id: currentUser.id  // ✅ FROM LARAVEL SESSION
                    })
                });

                const data = await response.json();
                typingDiv.remove();

                if (data.status === 'success') {
                    addMessage(data.response, 'bot', data.question_type);
                    showStatus(`✅ Saved (User: ${currentUser.name}, ID: ${currentUser.id})`, 'success');
                } else {
                    addMessage('Error: ' + (data.error || 'Unknown error'), 'bot');
                    showStatus('❌ Error', 'error');
                }
            } catch (error) {
                typingDiv.remove();
                addMessage('Cannot connect to chatbot. Make sure it\'s running on port 5001.', 'bot');
                showStatus('❌ Connection error', 'error');
            }
        }

        // Add message
        function addMessage(text, sender, type = null) {
            const messagesDiv = document.getElementById('messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;

            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.textContent = text;

            const metaDiv = document.createElement('div');
            metaDiv.className = 'message-meta';
            metaDiv.textContent = sender === 'user' ? 'You' : `Bot • ${type || 'system'}`;

            messageDiv.appendChild(contentDiv);
            messageDiv.appendChild(metaDiv);
            messagesDiv.appendChild(messageDiv);

            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        // Show status
        function showStatus(message, type) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = `status show ${type}`;
            setTimeout(() => status.classList.remove('show'), 4000);
        }

        // Clear messages
        function clearMessages() {
            document.getElementById('messages').innerHTML = '';
        }

        // Start
        init();
    </script>
</body>
</html>