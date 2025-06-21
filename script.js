// ---AJAX Pagination for Notes------------------------------------
// AJAX Pagination for Notes
// This script handles pagination for the notes section using AJAX.
document.addEventListener('DOMContentLoaded', () => {// Add event listener to the document
    document.addEventListener('click', function (e) {
      if (e.target.classList.contains('pagination-link')) {// Check if clicked elmnt has class'pagination-link'
        e.preventDefault();
        const url = e.target.getAttribute('href');// Get the URL from the clicked link
  
        fetch(url, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
          .then(res => res.text())
          .then(data => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data, 'text/html');
            const newNotesArea = doc.querySelector('#notes-area');
            if (newNotesArea) {
              document.querySelector('#notes-area').innerHTML = newNotesArea.innerHTML;
              window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
              alert("⚠️ Couldn't find notes area. Please try again.");
            }
          })
          .catch(err => {
            console.error("AJAX Pagination Error:", err);
            alert("❌ Failed to load notes.");
          });
      }
    });
  });


// ---AJAX delete Note--------------------------------------------- 

// AJAX Delete Note
// This script handles the deletion of notes via AJAX.
document.addEventListener('DOMContentLoaded', () => {
    const notesArea = document.getElementById('notes-area');
  
    if (!notesArea) {
      console.error("notes-area not found in DOM");
      return;
    }
  
    notesArea.addEventListener('click', async function (e) {
      if (e.target.classList.contains('delete-btn')) {
        e.preventDefault();
  
        const confirmed = confirm("Delete this note?");
        if (!confirmed) return;
  
        const noteId = e.target.dataset.id;
        const card = e.target.closest('.card');
  
        try {
          const response = await fetch('delete.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${noteId}`
          });
  
          const result = await response.json();
  
          if (result.status === 'success') {
            card.remove();
  
            // 🧠 Check if there are any cards left
            const remainingCards = document.querySelectorAll('.card').length;
  
            // ✅ If none left, go to page - 1
            if (remainingCards === 0) {
              const url = new URL(window.location.href);
              let currentPage = parseInt(url.searchParams.get("page") || "1");
  
              if (currentPage > 1) {
                url.searchParams.set("page", currentPage - 1);
                window.location.href = url.toString(); // reload with updated page
              } else {
                window.location.reload(); // already on page 1
              }
            }
          } else {
            alert("❌ Failed to delete the note.");
          }
        } catch (err) {
          alert("⚠️ Error deleting the note.");
          console.error("Fetch error:", err);
        }
      }
    });
  });
    

  
  
  