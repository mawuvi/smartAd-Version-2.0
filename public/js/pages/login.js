class LoginManager {
    constructor() {
        this.form = document.getElementById('login-form');
        this.loginBtn = document.getElementById('login-btn');
        this.alertContainer = document.getElementById('alert-container');
        this.init();
    }

    init() {
        this.form.addEventListener('submit', (e) => this.handleLogin(e));
        
        // Add enter key support
        this.form.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.handleLogin(e);
            }
        });
    }

    async handleLogin(e) {
        e.preventDefault();
        const formData = new FormData(this.form);
        const data = Object.fromEntries(formData.entries());

        if (!data.username || !data.password) {
            this.showAlert('Please enter both username and password', 'error');
            return;
        }

        // Show loading state
        this.setLoadingState(true);

        try {
            const response = await fetch('/smartAd/public/api_login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('Login successful! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = result.data.redirect_url || window.baseUrl + '/pages/dashboard.php';
                }, 1500);
            } else {
                this.showAlert(result.message || 'Login failed. Please check your credentials.', 'error');
            }
        } catch (error) {
            this.showAlert('Network error. Please check your connection and try again.', 'error');
            console.error('Login error:', error);
        } finally {
            this.setLoadingState(false);
        }
    }

    setLoadingState(loading) {
        if (loading) {
            this.loginBtn.disabled = true;
            this.loginBtn.classList.add('loading');
            this.loginBtn.textContent = 'Signing In...';
        } else {
            this.loginBtn.disabled = false;
            this.loginBtn.classList.remove('loading');
            this.loginBtn.textContent = 'Sign In';
        }
    }

    showAlert(message, type) {
        this.alertContainer.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        
        // Auto-hide success messages, keep error messages longer
        const timeout = type === 'success' ? 3000 : 5000;
        setTimeout(() => { 
            this.alertContainer.innerHTML = ''; 
        }, timeout);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new LoginManager();
});