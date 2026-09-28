document.addEventListener("DOMContentLoaded", () => {
  // Paths chat.php is allowed to link to — anything else stays plain text.
  const ALLOWED_LINKS = new Set([
    "registration.html", "contact.html", "events.html", "bulletin.html",
    "offerings.html", "member.html", "about-us.html", "login.html",
    "index.html", "developer.html",
  ]);

  const QUICK_REPLIES = [
    "What time is Sunday service?",
    "How do I register?",
    "What events are coming up?",
    "How do I contact the church?",
  ];

  // --- Build the widget's DOM once, on every page ---
  const toggle = document.createElement("button");
  toggle.className = "chatbot-toggle";
  toggle.setAttribute("aria-label", "Open chat");
  toggle.textContent = "💬";

  const win = document.createElement("div");
  win.className = "chatbot-window";
  win.innerHTML = `
    <div class="chatbot-header">
      <div class="chatbot-header-title">
        <span class="chatbot-status-dot"></span>
        <div>
          <strong>Support Assistant</strong>
          <div class="chatbot-subtitle">Semenyih Joy Methodist Church</div>
        </div>
      </div>
      <button type="button" id="chatbotClose" aria-label="Close chat">&times;</button>
    </div>
    <div class="chatbot-messages" id="chatbotMessages"></div>
    <div class="chatbot-quick-replies" id="chatbotQuickReplies"></div>
    <form class="chatbot-form" id="chatbotForm">
      <input type="text" id="chatbotInput" placeholder="Type a message…" autocomplete="off" maxlength="2000">
      <button type="submit" aria-label="Send">➤</button>
    </form>
  `;

  document.body.appendChild(toggle);
  document.body.appendChild(win);

  const messagesBox = win.querySelector("#chatbotMessages");
  const quickRepliesBox = win.querySelector("#chatbotQuickReplies");
  const form = win.querySelector("#chatbotForm");
  const input = win.querySelector("#chatbotInput");
  const sendBtn = form.querySelector("button[type=submit]");
  const closeBtn = win.querySelector("#chatbotClose");

  let history = []; // [{role: "user"|"assistant", content: "..."}]
  let hasStartedChatting = false;

  toggle.addEventListener("click", () => {
    win.classList.toggle("open");
    if (win.classList.contains("open")) {
      input.focus();
      if (!hasStartedChatting) showGreeting();
    }
  });
  closeBtn.addEventListener("click", () => win.classList.remove("open"));

  function showGreeting() {
    addMessage("assistant",
      "Hi! I'm the church's support assistant 👋 I can help with service times, " +
      "registration, events, offerings, or getting in touch with us. What can I help with?"
    );
    quickRepliesBox.innerHTML = "";
    QUICK_REPLIES.forEach((q) => {
      const chip = document.createElement("button");
      chip.type = "button";
      chip.className = "chatbot-chip";
      chip.textContent = q;
      chip.addEventListener("click", () => sendMessage(q));
      quickRepliesBox.appendChild(chip);
    });
  }

  function escapeHtml(str) {
    return str.replace(/[&<>"']/g, (s) => ({
      "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;",
    }[s]));
  }

  // Renders **bold** and [text](allowed-relative-path.html) safely; everything
  // else is HTML-escaped first so the model's output can never inject markup.
  function renderAssistantText(raw) {
    let html = escapeHtml(raw);
    html = html.replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, (match, label, url) => {
      const cleanUrl = url.trim();
      if (!ALLOWED_LINKS.has(cleanUrl)) return escapeHtml(label);
      return `<a href="${cleanUrl}" class="chat-link">${escapeHtml(label)}</a>`;
    });
    html = html.replace(/\*\*([^*]+)\*\*/g, "<strong>$1</strong>");
    return html;
  }

  function addMessage(role, text) {
    const div = document.createElement("div");
    div.className = "chat-msg " + role;
    if (role === "assistant") {
      div.innerHTML = renderAssistantText(text);
    } else {
      div.textContent = text;
    }
    messagesBox.appendChild(div);
    messagesBox.scrollTop = messagesBox.scrollHeight;
    return div;
  }

  function addTypingIndicator() {
    const div = document.createElement("div");
    div.className = "chat-msg assistant typing";
    div.innerHTML = `<span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span>`;
    messagesBox.appendChild(div);
    messagesBox.scrollTop = messagesBox.scrollHeight;
    return div;
  }

  function sendMessage(text) {
    if (!text) return;
    hasStartedChatting = true;
    quickRepliesBox.innerHTML = "";

    addMessage("user", text);
    history.push({ role: "user", content: text });
    input.value = "";
    input.disabled = true;
    sendBtn.disabled = true;

    const typing = addTypingIndicator();

    fetch("chat.php", {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message: text, history: history.slice(0, -1) }),
    })
      .then(async (r) => {
        let data;
        try { data = await r.json(); } catch { data = null; }
        if (!r.ok || !data || data.error) {
          throw new Error((data && data.error) || "Something went wrong.");
        }
        return data.reply;
      })
      .then((reply) => {
        typing.remove();
        addMessage("assistant", reply);
        history.push({ role: "assistant", content: reply });
      })
      .catch((err) => {
        typing.remove();
        const msg = err.message.includes("Failed to fetch")
          ? "Couldn't reach the server. This needs to be running on a PHP server with an API key configured (see README)."
          : err.message;
        addMessage("error", msg);
      })
      .finally(() => {
        input.disabled = false;
        sendBtn.disabled = false;
        input.focus();
      });
  }

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (text) sendMessage(text);
  });
});
