// /public/js/pages/users.js
// The UserManager class is now located here.
class UserManager {
    // ...
}

function logout() {
    if (confirm('Are you sure you want to logout?')) {
        // Redirect to styled logout page
        window.location.href = window.baseUrl + '/api_logout.php';
    }
}
// ...