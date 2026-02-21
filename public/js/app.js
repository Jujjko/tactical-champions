// public/js/app.js - Basic application JavaScript

// Console log to confirm loading
console.log('App.js loaded successfully');

// Close modal helper function
function closeModal() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.classList.remove('active');
    });
}

// Add global click handler for modals
document.addEventListener('DOMContentLoaded', () => {
    // Close modal when clicking outside
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            closeModal();
        }
    });
});