// Global application javascript
document.addEventListener('DOMContentLoaded', function() {
    console.log('smartAd App Initialized.');

    const logoutButton = document.getElementById('logout-button');
    if (logoutButton) {
        logoutButton.addEventListener('click', async function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                // Redirect to styled logout page
                window.location.href = window.baseUrl + '/api_logout.php';
            }
        });
    }
});