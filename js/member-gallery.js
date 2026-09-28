document.addEventListener("DOMContentLoaded", () => {
  const gallery = document.getElementById("member-gallery");
  if (!gallery) return;

  fetch("get_member_images.json")
    .then(r => {
      if (!r.ok) throw new Error("Could not load member images.");
      return r.json();
    })
    .then(images => {
      if (!Array.isArray(images) || images.length === 0) {
        gallery.innerHTML = "<p>No photos have been added yet.</p>";
        return;
      }
      images.forEach(img => {
        gallery.innerHTML += `<div class="member-card"><img src="images/members/${img}" alt="Church member photo" loading="lazy"></div>`;
      });
    })
    .catch(() => {
      gallery.innerHTML = "<p>Sorry, the gallery couldn't be loaded right now.</p>";
    });
});
