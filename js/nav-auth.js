document.addEventListener("DOMContentLoaded", () => {
  const slot = document.getElementById("nav-account-slot");
  if (!slot) return;

  fetch("session_check.php", { credentials: "same-origin" })
    .then(r => r.ok ? r.json() : { loggedIn: false })
    .then(data => {
      if (data && data.loggedIn) {
        slot.innerHTML = `
          <div class="nav-account-info">
            <span>Hi, ${escapeHtml(data.name)}</span>
            <button type="button" class="nav-logout-btn" id="navLogoutBtn">Log Out</button>
          </div>`;
        document.getElementById("navLogoutBtn").addEventListener("click", () => {
          fetch("logout.php", { method: "POST", credentials: "same-origin" })
            .finally(() => window.location.href = "index.html");
        });
      } else {
        slot.innerHTML = `<a href="login.html" class="nav-login-btn">Login</a>`;
      }
    })
    .catch(() => {
      // No PHP/session backend available (e.g. viewing as static files) —
      // fall back to a plain Login link so the button is still always visible.
      slot.innerHTML = `<a href="login.html" class="nav-login-btn">Login</a>`;
    });

  function escapeHtml(str){
    return String(str).replace(/[&<>"']/g, s => ({
      "&":"&amp;", "<":"&lt;", ">":"&gt;", '"':"&quot;", "'":"&#39;"
    }[s]));
  }
});
