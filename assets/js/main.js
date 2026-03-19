// Global variables for webhook loop
let loopInterval = null;
let isLoopRunning = false;

// Text editor tools
document.addEventListener('DOMContentLoaded', function() {
    // Initialize text editor tools
    const messageInput = document.getElementById('message');
    if (messageInput) {
        setupTextEditor();
    }
    
    // Update credits every minute
    setInterval(updateCredits, 60000);
    
    // Check for loop status on page load
    checkLoopStatus();
});

function setupTextEditor() {
    const messageInput = document.getElementById('message');
    
    // Bold
    document.getElementById('bold-btn')?.addEventListener('click', function() {
        wrapText(messageInput, '**', '**');
    });
    
    // Italic
    document.getElementById('italic-btn')?.addEventListener('click', function() {
        wrapText(messageInput, '*', '*');
    });
    
    // Underline
    document.getElementById('underline-btn')?.addEventListener('click', function() {
        wrapText(messageInput, '__', '__');
    });
    
    // Code
    document.getElementById('code-btn')?.addEventListener('click', function() {
        wrapText(messageInput, '`', '`');
    });
    
    // Code Block
    document.getElementById('codeblock-btn')?.addEventListener('click', function() {
        wrapText(messageInput, '```\n', '\n```');
    });
    
    // Spoiler
    document.getElementById('spoiler-btn')?.addEventListener('click', function() {
        wrapText(messageInput, '||', '||');
    });
}

function wrapText(input, before, after) {
    const start = input.selectionStart;
    const end = input.selectionEnd;
    const text = input.value;
    const selectedText = text.substring(start, end);
    
    input.value = text.substring(0, start) + before + selectedText + after + text.substring(end);
    
    // Restore cursor position
    input.selectionStart = start + before.length;
    input.selectionEnd = end + before.length;
}

function updateCredits() {
    fetch('/discord-webhook-portal/api/earn-credits.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const creditDisplay = document.getElementById('credit-display');
                if (creditDisplay) {
                    creditDisplay.textContent = data.credits;
                    creditDisplay.classList.add('credit-change');
                    setTimeout(() => {
                        creditDisplay.classList.remove('credit-change');
                    }, 500);
                    
                    // Show notification if credits were added
                    if (data.added > 0) {
                        showNotification(`Earned ${data.added} credits!`, 'success');
                    }
                }
            }
        })
        .catch(error => console.error('Error updating credits:', error));
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type} fade-in`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Webhook sending functions
function sendSingleMessage() {
    const webhookUrl = document.getElementById('webhook-url').value;
    const message = document.getElementById('message').value;
    
    if (!webhookUrl || !message) {
        showNotification('Please fill in all fields', 'error');
        return;
    }
    
    fetch('/discord-webhook-portal/api/send-webhook.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            webhook_url: webhookUrl,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Message sent successfully!', 'success');
            updateCredits();
            loadHistory();
        } else {
            showNotification(data.error || 'Failed to send message', 'error');
        }
    })
    .catch(error => {
        showNotification('Error sending message', 'error');
        console.error('Error:', error);
    });
}

function startLoop() {
    if (isLoopRunning) return;
    
    const webhookUrl = document.getElementById('webhook-url').value;
    const message = document.getElementById('message').value;
    const amount = parseInt(document.getElementById('amount').value) || 1;
    const delay = parseInt(document.getElementById('delay').value) || 1;
    
    if (!webhookUrl || !message) {
        showNotification('Please fill in all fields', 'error');
        return;
    }
    
    // Check credits first
    fetch('/discord-webhook-portal/api/send-webhook.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            check_credits: true,
            amount: amount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.has_enough_credits) {
            startLoopWithParams(webhookUrl, message, amount, delay);
        } else {
            showNotification('Insufficient credits!', 'error');
        }
    });
}

function startLoopWithParams(webhookUrl, message, amount, delay) {
    isLoopRunning = true;
    document.getElementById('start-btn').disabled = true;
    document.getElementById('stop-btn').disabled = false;
    document.getElementById('loop-status').className = 'status-running';
    document.getElementById('status-text').textContent = 'Running';
    
    let messagesSent = 0;
    
    loopInterval = setInterval(() => {
        if (messagesSent >= amount) {
            stopLoop();
            return;
        }
        
        fetch('/discord-webhook-portal/api/send-webhook.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                webhook_url: webhookUrl,
                message: message,
                loop_message: true
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                messagesSent++;
                document.getElementById('sent-count').textContent = messagesSent;
                updateCredits();
                
                if (messagesSent >= amount) {
                    stopLoop();
                }
            } else {
                showNotification(data.error || 'Failed to send message', 'error');
                stopLoop();
            }
        })
        .catch(error => {
            showNotification('Error sending message', 'error');
            stopLoop();
        });
        
    }, delay * 1000);
}

function stopLoop() {
    if (loopInterval) {
        clearInterval(loopInterval);
        loopInterval = null;
    }
    
    isLoopRunning = false;
    document.getElementById('start-btn').disabled = false;
    document.getElementById('stop-btn').disabled = true;
    document.getElementById('loop-status').className = 'status-stopped';
    document.getElementById('status-text').textContent = 'Stopped';
    
    // Notify server to stop
    fetch('/discord-webhook-portal/api/stop-loop.php');
}

function checkLoopStatus() {
    fetch('/discord-webhook-portal/api/stop-loop.php?check=1')
        .then(response => response.json())
        .then(data => {
            if (data.is_running) {
                startLoopWithParams(data.webhookUrl, data.message, data.amount, data.delay);
            }
        });
}

function loadHistory() {
    fetch('/discord-webhook-portal/api/get-history.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateHistoryTable(data.history);
            }
        });
}

function updateHistoryTable(history) {
    const tbody = document.getElementById('history-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    history.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${new Date(item.created_at).toLocaleString()}</td>
            <td>${item.webhook_url.substring(0, 30)}...</td>
            <td>${item.message.substring(0, 50)}...</td>
            <td class="status-${item.status}">${item.status}</td>
        `;
        tbody.appendChild(row);
    });
}

// Logout function
function logout() {
    window.location.href = '/discord-webhook-portal/pages/logout.php';
}
