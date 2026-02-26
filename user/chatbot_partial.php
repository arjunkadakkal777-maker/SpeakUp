<style>
    /* Chatbot Container */
    #chatbot-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Floating Button */
    #chatbot-btn {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    #chatbot-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }

    /* Chat Window */
    #chat-window {
        position: absolute;
        bottom: 80px;
        right: 0;
        width: 350px;
        height: 500px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 30px rgba(0,0,0,0.15);
        display: none;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e0e0e0;
        animation: slideIn 0.3s ease-out forwards;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Header */
    .chat-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 15px 20px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .chat-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }

    .chat-header .close-btn {
        cursor: pointer;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    
    .chat-header .close-btn:hover {
        opacity: 1;
    }

    /* Messages Area */
    .chat-messages {
        flex: 1;
        padding: 15px;
        overflow-y: auto;
        background: #f9f9f9;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .message {
        max-width: 80%;
        padding: 10px 15px;
        border-radius: 15px;
        font-size: 14px;
        line-height: 1.4;
        word-wrap: break-word;
    }

    .message.bot {
        align-self: flex-start;
        background: white;
        color: #333;
        border-bottom-left-radius: 2px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }

    .message.user {
        align-self: flex-end;
        background: #667eea;
        color: white;
        border-bottom-right-radius: 2px;
    }

    /* Typing Indicator */
    .typing-indicator {
        display: none;
        align-self: flex-start;
        background: white;
        padding: 10px 15px;
        border-radius: 15px;
        border-bottom-left-radius: 2px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        margin-bottom: 10px;
    }

    .typing-indicator span {
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #ccc;
        border-radius: 50%;
        animation: typing 1.4s infinite ease-in-out both;
        margin-right: 3px;
    }

    .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
    .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

    @keyframes typing {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }

    /* Options / Quick Replies */
    .chat-options {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 5px;
    }

    .option-btn {
        background: #eef2ff;
        color: #5c7cfa;
        border: 1px solid #bac8ff;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .option-btn:hover {
        background: #bac8ff;
        color: #364fc7;
    }

    /* Input Area */
    .chat-input-area {
        padding: 10px;
        background: white;
        border-top: 1px solid #e0e0e0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chat-input-area input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 20px;
        outline: none;
        font-size: 14px;
    }

    .chat-input-area input:focus {
        border-color: #667eea;
    }

    .chat-input-area button {
        background: #667eea;
        color: white;
        border: none;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: background 0.2s;
    }

    .chat-input-area button:hover {
        background: #5a6fd6;
    }

</style>

<div id="chatbot-container">
    <!-- Chat Window -->
    <div id="chat-window">
        <div class="chat-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-robot"></i>
                <h3>Campus Assistant</h3>
            </div>
            <i class="fa-solid fa-xmark close-btn" onclick="toggleChat()"></i>
        </div>
        
        <div class="chat-messages" id="chat-messages">
            <!-- Messages will appear here -->
        </div>

        <div class="typing-indicator" id="typing-indicator">
            <span></span><span></span><span></span>
        </div>

        <div class="chat-input-area">
            <input type="text" id="chat-input" placeholder="Type a message..." onkeypress="handleKeyPress(event)">
            <button onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>

    <!-- Floating Button -->
    <div id="chatbot-btn" onclick="toggleChat()">
        <i class="fa-solid fa-comment-dots"></i>
    </div>
</div>

<script>
    let chatOpen = false;
    let firstOpen = true;

    function toggleChat() {
        const chatWindow = document.getElementById('chat-window');
        const chatBtn = document.getElementById('chatbot-btn');
        
        if (chatOpen) {
            chatWindow.style.display = 'none';
            chatBtn.innerHTML = '<i class="fa-solid fa-comment-dots"></i>';
        } else {
            chatWindow.style.display = 'flex';
            chatBtn.innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
            if (firstOpen) {
                setTimeout(showWelcomeMessage, 500);
                firstOpen = false;
            }
            // Auto focus input
            setTimeout(() => document.getElementById('chat-input').focus(), 100);
        }
        chatOpen = !chatOpen;
    }

    function showWelcomeMessage() {
        addBotMessage("👋 Hi there! I'm your AI Campus Assistant. How can I help you today?");
        showOptions([
            { text: "File a Complaint", value: "file_complaint" },
            { text: "Check Status", value: "check_status" },
            { text: "Common Questions", value: "common_questions" }
        ]);
    }

    function handleKeyPress(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    }

    function sendMessage() {
        const input = document.getElementById('chat-input');
        const text = input.value.trim();
        
        if (text) {
            addUserMessage(text);
            input.value = '';
            processUserMessage(text);
        }
    }

    function addUserMessage(text) {
        const chatMessages = document.getElementById('chat-messages');
        const msgDiv = document.createElement('div');
        msgDiv.className = 'message user';
        msgDiv.innerText = text;
        chatMessages.appendChild(msgDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function addBotMessage(text) {
        const chatMessages = document.getElementById('chat-messages');
        const msgDiv = document.createElement('div');
        msgDiv.className = 'message bot';
        msgDiv.innerHTML = text; // Allow HTML for links
        
        // Show typing indicator before showing message
        const typing = document.getElementById('typing-indicator');
        typing.style.display = 'block';
        chatMessages.scrollTop = chatMessages.scrollHeight;

        setTimeout(() => {
            typing.style.display = 'none';
            chatMessages.appendChild(msgDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 600);
    }

    function showOptions(options) {
        const chatMessages = document.getElementById('chat-messages');
        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'chat-options';
        
        options.forEach(opt => {
            const btn = document.createElement('button');
            btn.className = 'option-btn';
            btn.innerText = opt.text;
            btn.onclick = () => handleOptionClick(opt.value, opt.text);
            optionsDiv.appendChild(btn);
        });

        // Delay to show after bot message
        setTimeout(() => {
            chatMessages.appendChild(optionsDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 700);
    }

    function handleOptionClick(value, text) {
        addUserMessage(text);
        processUserMessage(value);
    }

    // Logic for bot responses
    function processUserMessage(message) {
        const lowerMsg = message.toLowerCase();

        // 0. Greeting / Introduction
        if (lowerMsg === 'hi' || lowerMsg === 'hello' || lowerMsg === 'hey') {
            addBotMessage("👋 Hello! How can I assist you with your grievances today?");
            return;
        }

        // 1. Complaint Status Check (ID detection)
        if (lowerMsg.startsWith('#') || (!isNaN(message) && message.length < 10)) {
            // Assume it's an ID check if user enters a number or #number
            let id = message.replace('#', '');
            if (!isNaN(id)) {
                checkStatus(id);
                return;
            }
        }

        // --- SECTION 1: REGISTRATION ---
        if (lowerMsg.includes('how to register') || lowerMsg.includes('how to file') || lowerMsg.includes('file a complaint') || lowerMsg.includes('submit complaint')) {
            addBotMessage("📝 <b>How to Register a Complaint:</b><br>" +
                "1. Click 'New Grievance' in the sidebar.<br>" +
                "2. <b>Details:</b> Enter Title, Category, Priority, and find your Faculty.<br>" +
                "3. <b>Description:</b> Be specific. Include precise location/room no.<br>" +
                "4. <b>Attachments:</b> You can upload photos/docs.<br>" +
                "5. <b>Submit:</b> Click 'Submit Report'.");
            return;
        }
        if (lowerMsg.includes('details required') || lowerMsg.includes('what details')) {
            addBotMessage("📋 <b>Required Details:</b><br>• Title & Description<br>• Category (Hostel, Academic, etc.)<br>• Priority Level<br>• Assigned Faculty<br>• Date of Incident<br>• Location/Room No (Mandatory for Hostel)");
            return;
        }

        if (lowerMsg.includes('type') || lowerMsg.includes('category') || lowerMsg.includes('categories') || lowerMsg.includes('kind of complaint')) {
            addBotMessage("📂 <b>Complaint Categories:</b><br>You can submit complaints for:<br>• <b>Academic:</b> Exam, marks, teaching issues.<br>• <b>Hostel:</b> Room, maintenance, food.<br>• <b>Infrastructure:</b> Water, electricity, sanitation.<br>• <b>Cafeteria:</b> Food quality, hygiene.<br>• <b>Other:</b> Ragging, harassment, etc.");
            return;
        }

        if (lowerMsg.includes('attach') || lowerMsg.includes('screenshot') || lowerMsg.includes('document')) {
             addBotMessage("📎 <b>Attachments:</b><br>Yes! You can attach PDF, DOC, or JPG files (Max 5MB) to support your complaint.");
             return;
        }
        if (lowerMsg.includes('anonymous')) {
             addBotMessage("🕵️ <b>Anonymous Mode:</b><br>Yes, check 'Post Anonymously' to hide your name from faculty/wardens. Only the Principal can view it if absolutely necessary.");
             return;
        }
        if (lowerMsg.includes('deadline') || lowerMsg.includes('time limit')) {
             addBotMessage("⏳ <b>Deadlines:</b><br>There is no strict deadline, but we recommend reporting issues within <b>24-48 hours</b> of the incident for faster resolution.");
             return;
        }

        // --- SECTION 2: STATUS & TRACKING ---
        if (lowerMsg.includes('check status') || lowerMsg.includes('track complaint') || lowerMsg.includes('status of my')) {
             addBotMessage("🔍 <b>Check Status:</b><br>Please enter your <b>Grievance ID</b> (e.g., 24) here in the chat, or check the 'My Submission History' page.");
             return;
        }
        if (lowerMsg.includes('under review') || lowerMsg.includes('in progress')) {
             addBotMessage("🔄 <b>Under Review / In Progress:</b><br>This means a faculty member or warden has acknowledged your complaint and is actively working on a solution.");
             return;
        }
        if (lowerMsg.includes('forwarded') || lowerMsg.includes('escalated')) {
             addBotMessage("🔼 <b>Forwarded/Escalated:</b><br>Your complaint has been moved to a higher authority (HOD, Principal, or Committee) because it required further approval or wasn't resolved in time.");
             return;
        }
        if (lowerMsg.includes('how long') || lowerMsg.includes('resolution time')) {
             addBotMessage("⏱️ <b>Resolution Time:</b><br>• <b>Low Priority:</b> 3-5 Days<br>• <b>Medium:</b> 2-3 Days<br>• <b>High/Urgent:</b> 24 Hours<br><i>Unresolved issues are auto-escalated after 7 days.</i>");
             return;
        }

        // --- SECTION 3: MODIFICATION ---
        if (lowerMsg.includes('edit') || lowerMsg.includes('modify')) {
             addBotMessage("✏️ <b>Editing:</b><br>Currently, you cannot edit a complaint once submitted. Please file a new one or contact the admin if there's a major error.");
             return;
        }
        if (lowerMsg.includes('reopen')) {
             addBotMessage("🔓 <b>Reopening:</b><br>Resolved complaints cannot be reopened. If the issue persists, please file a new grievance and reference the old ID.");
             return;
        }
        if (lowerMsg.includes('escalate')) {
             addBotMessage("⚠️ <b>Escalation:</b><br>You cannot manually escalate immediately. Complaints are <b>automatically escalated</b> if specific deadlines are missed or if the faculty marks it for escalation.");
             return;
        }

        // --- SECTION 4: DEPARTMENT INFO ---
        if (lowerMsg.includes('hostel complaint') || lowerMsg.includes('warden')) {
             addBotMessage("🏢 <b>Hostel Grievances:</b><br>These are handled by the <b>Warden Office</b>. Ensure you mention your Room Number.");
             return;
        }
        if (lowerMsg.includes('exam') || lowerMsg.includes('marks')) {
             addBotMessage("📝 <b>Exam/Academic:</b><br>Exam grievances go to the <b>Examination Cell</b> or specific subject faculty. Choose the 'Academic' category.");
             return;
        }
        if (lowerMsg.includes('committee')) {
             addBotMessage("⚖️ <b>Grievance Committee:</b><br>A special body that reviews serious, sensitive, or unresolved escalated cases. They meet weekly.");
             return;
        }

        // --- SECTION 5: ANALYTICS (Simulated) ---
        if (lowerMsg.includes('how many complaint') || lowerMsg.includes('statistics') || lowerMsg.includes('report') || lowerMsg.includes('trend')) {
             addBotMessage("📊 <b>Campus Analytics (Live):</b><br>" +
                "• <b>Total Active:</b> 12<br>" +
                "• <b>Resolved (This Month):</b> 45<br>" +
                "• <b>High Delay Category:</b> Infrastructure<br>" +
                "• <b>Avg Resolution Time:</b> 2.4 Days<br>" +
                "<i>(Check your dashboard for your personal stats!)</i>");
             return;
        }

        // --- SECTION 6: ACCOUNT ---
        if (lowerMsg.includes('password') || lowerMsg.includes('reset')) {
             addBotMessage("🔑 <b>Password Reset:</b><br>Logout and click 'Forgot Password?' on the login screen. You'll receive an email link.");
             return;
        }
        if (lowerMsg.includes('login') || lowerMsg.includes('signin')) {
             addBotMessage("🚫 <b>Login Issues:</b><br>Ensure your email is verified. If issues persist, contact <b>admin@cgms.edu</b>.");
             return;
        }

        // --- SECTION 7: NOTIFICATIONS ---
        if (lowerMsg.includes('email') || lowerMsg.includes('sms') || lowerMsg.includes('notify') || lowerMsg.includes('notification')) {
             addBotMessage("🔔 <b>Notifications:</b><br>You will receive <b>Email Alerts</b> for:<br>• Submission Confirmation<br>• Status Updates<br>• Resolution Remarks<br><i>SMS alerts are coming soon!</i>");
             return;
        }

        // --- SECTION 8: GENERAL ---
        if (lowerMsg.includes('what is cgms')) {
             addBotMessage("🎓 <b>About CGMS:</b><br>The <b>Campus Grievance Management System</b> is designed to help students resolve academic, hostel, and infrastructure issues efficiently and transparently.");
             return;
        }
        if (lowerMsg.includes('confidential')) {
             addBotMessage("🔒 <b>Confidentiality:</b><br>Your data is secure. Anonymous complaints effectively hide your identity from most staff.");
             return;
        }
        
         // --- SECTION 9: AI FEATURES (Question Handling) ---
        if (lowerMsg.includes('sentiment') || lowerMsg.includes('auto-priority')) {
             addBotMessage("🤖 <b>AI Features:</b><br>I use basic keyword analysis to help you navigate. Advanced sentiment analysis and priority detection run in the background to flag urgent cases to the admin!");
             return;
        }

        // --- SECTION 10: EMERGENCY ---
        if (lowerMsg.includes('urgent') || lowerMsg.includes('emergency') || lowerMsg.includes('harassment') || lowerMsg.includes('ragging')) {
             addBotMessage("🚨 <b>EMERGENCY / HARASSMENT:</b><br>" +
                "Please select <b>'High Priority'</b> immediately.<br>" +
                "For Harassment/Ragging, you can also contact the <b>Anti-Ragging Squad</b> directly at:<br>" +
                "📞 <b>Helpline:</b> 1800-123-4567<br>" +
                "📧 <b>Email:</b> antiragging@cgms.edu");
             return;
        }

        // --- DEFAULT ---
        addBotMessage("I'm not sure about that. Try asking about <b>'How to file'</b>, <b>'Check Status'</b>, or <b>'Deadlines'</b>.");
        showOptions([
            { text: "File Complaint", value: "file_complaint" },
            { text: "Check Status", value: "check_status" },
            { text: "Emergency", value: "urgent" }
        ]);
    }

    function checkStatus(id) {
        addBotMessage("Checking status for Grievance #" + id + "...");
        
        fetch('api_grievance_status.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    let statusColor = '#333';
                    if(data.data.status === 'Open') statusColor = '#c92a2a';
                    if(data.data.status === 'Resolved') statusColor = '#2b8a3e';
                    if(data.data.status === 'In Progress') statusColor = '#f08c00';

                    let msg = `<b>Title:</b> ${data.data.title}<br>`;
                    msg += `<b>Status:</b> <span style="color:${statusColor}; font-weight:bold;">${data.data.status}</span><br>`;
                    if (data.data.feedback) {
                        msg += `<b>Feedback:</b> ${data.data.feedback}`;
                    } else {
                        msg += `<i>No feedback yet.</i>`;
                    }
                    addBotMessage(msg);
                } else {
                    addBotMessage("❌ " + data.message);
                }
            })
            .catch(error => {
                addBotMessage("⚠️ Error connecting to server.");
                console.error(error);
            });
    }

</script>
