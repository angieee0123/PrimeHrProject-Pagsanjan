<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PrimeHR Chatbot - With Laravel Session</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 800px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }

        .chat-header h1 {
            font-size: 22px;
            margin-bottom: 8px;
        }

        .chat-header p {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .auth-status {
            font-size: 12px;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }

        .auth-status.authenticated {
            background: #4caf50;
            color: white;
        }

        .auth-status.unauthenticated {
            background: #f44336;
            color: white;
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f9f9f9;
            min-height: 300px;
            max-height: 400px;
        }

        .message {
            margin-bottom: 15px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            display: flex;
            justify-content: flex-end;
        }

        .message.bot {
            display: flex;
            justify-content: flex-start;
        }

        .message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 10px;
            word-wrap: break-word;
            font-size: 14px;
            line-height: 1.4;
        }

        .message.user .message-content {
            background: #667eea;
            color: white;
            border-bottom-right-radius: 0;
        }

        .message.bot .message-content {
            background: #e0e0e0;
            color: #333;
            border-bottom-left-radius: 0;
        }

        .message-meta {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
            padding: 0 5px;
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 12px 16px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #999;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% {
                opacity: 0.3;
                transform: translateY(0);
            }
            30% {
                opacity: 1;
                transform: translateY(-10px);
            }
        }

        .input-area {
            padding: 15px;
            border-top: 1px solid #ddd;
            background: white;
        }

        .input-wrapper {
            display: flex;
            gap: 10px;
        }

        input[type="text"] {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            border-color: #667eea;
        }

        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 14px;
        }

        button:hover {
            background: #764ba2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .status {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 5px;
            display: none;
        }

        .status.show {
            display: block;
        }

        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .demo-users {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 12px;
        }

        .demo-users p {
            color: #666;
            margin-bottom: 8px;
        }

        .demo-btn {
            background: #f0f0f0;
            color: #333;
            padding: 8px 12px;
            margin-right: 5px;
            margin-bottom: 5px;
            border-radius: 5px;
            font-size: 11px;
            cursor: pointer;
            border: 1px solid #ddd;
        }

        .demo-btn:hover {
            background: #e0e0e0;
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
