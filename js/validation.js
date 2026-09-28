document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("registrationForm");
  if (!form) return;

  const fields = {
    name: document.getElementById("name"),
    age: document.getElementById("age"),
    email: document.getElementById("email"),
    phone: document.getElementById("phone"),
    password: document.getElementById("password"),
    confirmPassword: document.getElementById("confirmPassword"),
    terms: document.getElementById("terms"),
  };

  function setError(id, message) {
    const errorBox = document.getElementById(id + "-error");
    const field = fields[id];
    if (errorBox) errorBox.textContent = message;
    if (field) field.classList.toggle("field-error", Boolean(message));
  }

  function validate() {
    let isValid = true;

    if (!fields.name.value.trim()) {
      setError("name", "Please enter your name.");
      isValid = false;
    } else {
      setError("name", "");
    }

    const age = Number(fields.age.value);
    if (!fields.age.value || age <= 0 || age > 120) {
      setError("age", "Please enter a valid age.");
      isValid = false;
    } else {
      setError("age", "");
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(fields.email.value.trim())) {
      setError("email", "Please enter a valid email address.");
      isValid = false;
    } else {
      setError("email", "");
    }

    if (fields.phone.value.trim().length < 7) {
      setError("phone", "Please enter a valid phone number.");
      isValid = false;
    } else {
      setError("phone", "");
    }

    if (fields.password.value.length < 6) {
      setError("password", "Password must be at least 6 characters.");
      isValid = false;
    } else {
      setError("password", "");
    }

    if (fields.confirmPassword.value !== fields.password.value || !fields.confirmPassword.value) {
      setError("confirmPassword", "Passwords do not match.");
      isValid = false;
    } else {
      setError("confirmPassword", "");
    }

    if (!fields.terms.checked) {
      setError("terms", "You must agree to the Terms & Conditions.");
      isValid = false;
    } else {
      setError("terms", "");
    }

    return isValid;
  }

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    if (!validate()) return;

    const memberSelect = document.getElementById("member");
    const submitBtn = document.getElementById("registerBtn");
    const formError = document.getElementById("form-error");

    submitBtn.disabled = true;
    submitBtn.textContent = "Registering…";
    if (formError) formError.textContent = "";

    fetch("register.php", {
      method: "POST",
      credentials: "same-origin",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        name: fields.name.value.trim(),
        age: fields.age.value.trim(),
        email: fields.email.value.trim(),
        phone: fields.phone.value.trim(),
        member: memberSelect.value || "Not specified",
        password: fields.password.value,
      }),
    })
      .then(async (r) => {
        let data;
        try { data = await r.json(); } catch { data = null; }
        if (!r.ok || !data || !data.success) {
          throw new Error((data && data.message) || "Something went wrong. Please try again.");
        }
        return data;
      })
      .then(() => {
        document.getElementById("summary-name").textContent = fields.name.value.trim();
        document.getElementById("summary-email").textContent = fields.email.value.trim();
        document.getElementById("summary-phone").textContent = fields.phone.value.trim();
        document.getElementById("summary-member").textContent = memberSelect.value || "Not specified";

        document.getElementById("registrationView").style.display = "none";
        document.getElementById("confirmationView").style.display = "block";
        window.scrollTo({ top: 0, behavior: "smooth" });
      })
      .catch((err) => {
        if (formError) {
          formError.textContent = err.message.includes("Failed to fetch")
            ? "Couldn't reach the server. This form needs to be running on a PHP + MySQL server (see README)."
            : err.message;
        }
        submitBtn.disabled = false;
        submitBtn.textContent = "Register";
      });
  });
});
