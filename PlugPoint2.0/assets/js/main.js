// No dark mode toggle needed. Site is always in dark mode. 

// Dark mode toggle with localStorage persistence
function setDarkMode(enabled) {
  if (enabled) {
    document.body.classList.add('dark-mode');
    localStorage.setItem('darkMode', 'enabled');
  } else {
    document.body.classList.remove('dark-mode');
    localStorage.setItem('darkMode', 'disabled');
  }
  updateDarkModeButtons();
}

function toggleDarkMode() {
  const enabled = !document.body.classList.contains('dark-mode');
  setDarkMode(enabled);
}

function updateDarkModeButtons() {
  const isDarkMode = document.body.classList.contains('dark-mode');
  
  // Update navbar toggle
  const navbarToggle = document.getElementById('darkModeToggle');
  if (navbarToggle) {
    if (isDarkMode) {
      navbarToggle.textContent = '☀️';
      navbarToggle.title = 'Switch to light mode';
    } else {
      navbarToggle.textContent = '🌙';
      navbarToggle.title = 'Switch to dark mode';
    }
  }
}

document.addEventListener('DOMContentLoaded', function() {
  // Apply dark mode if set in localStorage
  if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
  }
  
  updateDarkModeButtons();
  
  // Add event listener for navbar toggle only
  const navbarToggle = document.getElementById('darkModeToggle');
  if (navbarToggle) {
    navbarToggle.addEventListener('click', toggleDarkMode);
  }
}); 