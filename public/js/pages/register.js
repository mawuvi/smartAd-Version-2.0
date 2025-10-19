// /public/js/pages/register.js

document.getElementById('register-form').addEventListener('submit', function(e) {
    e.preventDefault();
    // This is where you would add the fetch() call to your registration API.
    showAlert('Registration functionality is not yet connected to an API.', 'info');
});

function showAlert(message, type) {
    const container = document.getElementById('alert-container');
    container.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    setTimeout(() => { container.innerHTML = ''; }, 5000);
}