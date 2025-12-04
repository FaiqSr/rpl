// Navbar JavaScript - Konsisten untuk semua halaman

// Toggle category dropdown
function toggleCategory() {
  const dropdown = document.getElementById('categoryDropdown');
  if (dropdown) {
    dropdown.classList.toggle('show');
  }
}

// Toggle profile dropdown
function toggleProfileDropdown(event) {
  if (event) {
    event.stopPropagation();
  }
  const dropdown = document.getElementById('profileDropdown');
  if (dropdown) {
    dropdown.classList.toggle('show');
  }
}

// Close dropdowns when clicking outside
document.addEventListener('DOMContentLoaded', function() {
  // Close category dropdown when clicking outside
  window.addEventListener('click', function(e) {
    const categoryDropdown = document.getElementById('categoryDropdown');
    if (categoryDropdown) {
      if (!e.target.closest('#categoryDropdown') && !e.target.closest('.navbar-category-btn')) {
        if (categoryDropdown.classList.contains('show')) {
          categoryDropdown.classList.remove('show');
        }
      }
    }
    
    // Close profile dropdown when clicking outside
    const profileDropdown = document.getElementById('profileDropdown');
    if (profileDropdown) {
      if (!e.target.closest('#profileDropdown') && !e.target.closest('.navbar-profile-btn')) {
        if (profileDropdown.classList.contains('show')) {
          profileDropdown.classList.remove('show');
        }
      }
    }
  });
  
  // Close dropdown when selecting a link
  const categoryDropdown = document.getElementById('categoryDropdown');
  if (categoryDropdown) {
    categoryDropdown.addEventListener('click', (e) => {
      const link = e.target.closest('.cat-link');
      if (link) {
        categoryDropdown.classList.remove('show');
      }
    });
  }
});

