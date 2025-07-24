// script.js

// Function to toggle doctor-specific fields on the registration form
function toggleDoctorFields() {
    const doctorFields = document.getElementById('doctorFields');
    const role = document.querySelector('input[name="role"]:checked').value;
    if (role === 'doctor') {
        doctorFields.style.display = 'block';
        doctorFields.querySelectorAll('input').forEach(input => input.required = true);
    } else {
        doctorFields.style.display = 'none';
        doctorFields.querySelectorAll('input').forEach(input => input.required = false);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Theme Toggler
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;

    // Apply saved theme on load
    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark-mode');
        themeToggle.textContent = '☀️';
    }

    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-mode');
        if (body.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'dark');
            themeToggle.textContent = '☀️'; // Sun icon
        } else {
            localStorage.setItem('theme', 'light');
            themeToggle.textContent = '🌙'; // Moon icon
        }
    });
});