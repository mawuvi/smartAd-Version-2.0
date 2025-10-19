/* Navigation JavaScript */
/* Location: public/js/components/navigation.js */

class NavigationManager {
    constructor() {
        this.sidebar = document.getElementById('sidebar');
        this.sidebarToggle = document.getElementById('sidebarToggle');
        this.sidebarOverlay = document.getElementById('sidebarOverlay');
        this.mobileMenuToggle = document.getElementById('mobileMenuToggle');
        this.mobileOverlay = document.getElementById('mobileOverlay');
        this.isCollapsed = false;
        this.isMobile = window.innerWidth <= 768;
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupResponsive();
        this.setupKeyboardShortcuts();
        this.setupActiveStates();
        this.loadSidebarState();
    }
    
    setupEventListeners() {
        // Sidebar toggle
        if (this.sidebarToggle) {
            this.sidebarToggle.addEventListener('click', () => this.toggleSidebar());
        }
        
        // Mobile menu toggle
        if (this.mobileMenuToggle) {
            this.mobileMenuToggle.addEventListener('click', () => this.toggleMobileMenu());
        }
        
        // Overlay clicks
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', () => this.closeMobileMenu());
        }
        
        if (this.mobileOverlay) {
            this.mobileOverlay.addEventListener('click', () => this.closeMobileMenu());
        }
        
        // Collapsible menu items
        document.querySelectorAll('[data-toggle="collapse"]').forEach(toggle => {
            toggle.addEventListener('click', (e) => this.toggleCollapse(e));
        });
        
        // Window resize
        window.addEventListener('resize', () => this.handleResize());
        
        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeMobileMenu();
            }
        });
    }
    
    setupResponsive() {
        if (this.isMobile) {
            this.sidebar?.classList.add('mobile-sidebar');
            document.body.classList.add('mobile-layout');
        } else {
            this.sidebar?.classList.remove('mobile-sidebar');
            document.body.classList.remove('mobile-layout');
            this.updateBodyMargin();
        }
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + B to toggle sidebar
            if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                e.preventDefault();
                this.toggleSidebar();
            }
            
            // Ctrl/Cmd + M to toggle mobile menu
            if ((e.ctrlKey || e.metaKey) && e.key === 'm') {
                e.preventDefault();
                this.toggleMobileMenu();
            }
        });
    }
    
    setupActiveStates() {
        // Set active states based on current page
        const currentPage = window.currentPage || '';
        const currentPath = window.currentPath || '';
        
        document.querySelectorAll('.nav-link').forEach(link => {
            const href = link.getAttribute('href');
            if (href && (currentPath.includes(href) || currentPage === href.split('/').pop().split('.')[0])) {
                link.closest('.nav-item, .nav-subitem')?.classList.add('active');
            }
        });
        
        // Auto-expand active menu sections
        document.querySelectorAll('.nav-item.active').forEach(item => {
            const collapseTarget = item.querySelector('[data-target]');
            if (collapseTarget) {
                const targetId = collapseTarget.getAttribute('data-target');
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.classList.add('show');
                    collapseTarget.setAttribute('aria-expanded', 'true');
                }
            }
        });
    }
    
    toggleSidebar() {
        if (this.isMobile) {
            this.toggleMobileMenu();
            return;
        }
        
        this.isCollapsed = !this.isCollapsed;
        
        if (this.isCollapsed) {
            this.sidebar?.classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
        } else {
            this.sidebar?.classList.remove('collapsed');
            document.body.classList.remove('sidebar-collapsed');
        }
        
        this.saveSidebarState();
        this.updateBodyMargin();
    }
    
    toggleMobileMenu() {
        const isOpen = this.sidebar?.classList.contains('mobile-open');
        
        if (isOpen) {
            this.closeMobileMenu();
        } else {
            this.openMobileMenu();
        }
    }
    
    openMobileMenu() {
        this.sidebar?.classList.add('mobile-open');
        this.sidebarOverlay?.classList.add('show');
        this.mobileOverlay?.classList.add('show');
        document.body.classList.add('menu-open');
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
    
    closeMobileMenu() {
        this.sidebar?.classList.remove('mobile-open');
        this.sidebarOverlay?.classList.remove('show');
        this.mobileOverlay?.classList.remove('show');
        document.body.classList.remove('menu-open');
        
        // Restore body scroll
        document.body.style.overflow = '';
    }
    
    toggleCollapse(e) {
        e.preventDefault();
        const targetId = e.currentTarget.getAttribute('data-target');
        const targetElement = document.querySelector(targetId);
        
        if (!targetElement) return;
        
        const isExpanded = e.currentTarget.getAttribute('aria-expanded') === 'true';
        
        if (isExpanded) {
            targetElement.classList.remove('show');
            e.currentTarget.setAttribute('aria-expanded', 'false');
        } else {
            targetElement.classList.add('show');
            e.currentTarget.setAttribute('aria-expanded', 'true');
        }
    }
    
    handleResize() {
        const wasMobile = this.isMobile;
        this.isMobile = window.innerWidth <= 768;
        
        if (wasMobile !== this.isMobile) {
            this.setupResponsive();
            
            if (!this.isMobile) {
                this.closeMobileMenu();
            }
        }
    }
    
    updateBodyMargin() {
        if (this.isMobile) {
            document.body.style.marginLeft = '';
            return;
        }
        
        if (this.isCollapsed) {
            document.body.style.marginLeft = '60px';
        } else {
            document.body.style.marginLeft = '280px';
        }
    }
    
    saveSidebarState() {
        localStorage.setItem('sidebarCollapsed', this.isCollapsed.toString());
    }
    
    loadSidebarState() {
        if (this.isMobile) return;
        
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            this.isCollapsed = true;
            this.sidebar?.classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
            this.updateBodyMargin();
        }
    }
    
    // Public methods for external use
    collapse() {
        if (!this.isCollapsed) {
            this.toggleSidebar();
        }
    }
    
    expand() {
        if (this.isCollapsed) {
            this.toggleSidebar();
        }
    }
    
    close() {
        if (this.isMobile) {
            this.closeMobileMenu();
        } else {
            this.collapse();
        }
    }
}

// Global Search Manager
class SearchManager {
    constructor() {
        this.searchInput = document.getElementById('globalSearch');
        this.searchResults = document.getElementById('searchResults');
        this.isOpen = false;
        this.searchTimeout = null;
        this.currentResults = [];
        this.selectedIndex = -1;
        
        this.init();
    }
    
    init() {
        if (!this.searchInput) return;
        
        this.setupEventListeners();
        this.setupKeyboardShortcuts();
    }
    
    setupEventListeners() {
        this.searchInput.addEventListener('input', (e) => this.handleSearch(e));
        this.searchInput.addEventListener('focus', () => this.showResults());
        this.searchInput.addEventListener('blur', (e) => this.handleBlur(e));
        
        // Click outside to close
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.header-search')) {
                this.hideResults();
            }
        });
    }
    
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Focus search with /
            if (e.key === '/' && !e.target.matches('input, textarea')) {
                e.preventDefault();
                this.searchInput.focus();
            }
            
            // Handle search navigation
            if (this.isOpen) {
                switch (e.key) {
                    case 'ArrowDown':
                        e.preventDefault();
                        this.navigateResults(1);
                        break;
                    case 'ArrowUp':
                        e.preventDefault();
                        this.navigateResults(-1);
                        break;
                    case 'Enter':
                        e.preventDefault();
                        this.selectResult();
                        break;
                    case 'Escape':
                        this.hideResults();
                        this.searchInput.blur();
                        break;
                }
            }
        });
    }
    
    handleSearch(e) {
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            this.hideResults();
            return;
        }
        
        // Debounce search
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.performSearch(query);
        }, 300);
    }
    
    async performSearch(query) {
        try {
            const response = await fetch(`${window.baseUrl}/app/api/search.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ query })
            });
            
            if (!response.ok) throw new Error('Search failed');
            
            const results = await response.json();
            this.displayResults(results);
        } catch (error) {
            console.error('Search error:', error);
            this.displayError('Search temporarily unavailable');
        }
    }
    
    displayResults(results) {
        if (!results || results.length === 0) {
            this.displayNoResults();
            return;
        }
        
        this.currentResults = results;
        this.selectedIndex = -1;
        
        const html = results.map((result, index) => `
            <div class="search-result-item" data-index="${index}">
                <div class="result-icon">${result.icon}</div>
                <div class="result-content">
                    <div class="result-title">${result.title}</div>
                    <div class="result-subtitle">${result.subtitle}</div>
                </div>
                <div class="result-type">${result.type}</div>
            </div>
        `).join('');
        
        this.searchResults.innerHTML = html;
        this.showResults();
        
        // Add click handlers
        this.searchResults.querySelectorAll('.search-result-item').forEach((item, index) => {
            item.addEventListener('click', () => this.selectResult(index));
        });
    }
    
    displayNoResults() {
        this.searchResults.innerHTML = `
            <div class="search-no-results">
                <div class="no-results-icon">🔍</div>
                <div class="no-results-text">No results found</div>
            </div>
        `;
        this.showResults();
    }
    
    displayError(message) {
        this.searchResults.innerHTML = `
            <div class="search-error">
                <div class="error-icon">⚠️</div>
                <div class="error-text">${message}</div>
            </div>
        `;
        this.showResults();
    }
    
    navigateResults(direction) {
        if (this.currentResults.length === 0) return;
        
        this.selectedIndex += direction;
        
        if (this.selectedIndex < 0) {
            this.selectedIndex = this.currentResults.length - 1;
        } else if (this.selectedIndex >= this.currentResults.length) {
            this.selectedIndex = 0;
        }
        
        this.updateSelection();
    }
    
    updateSelection() {
        this.searchResults.querySelectorAll('.search-result-item').forEach((item, index) => {
            item.classList.toggle('selected', index === this.selectedIndex);
        });
    }
    
    selectResult(index = null) {
        const targetIndex = index !== null ? index : this.selectedIndex;
        
        if (targetIndex >= 0 && targetIndex < this.currentResults.length) {
            const result = this.currentResults[targetIndex];
            window.location.href = `${window.baseUrl}${result.url}`;
        }
    }
    
    showResults() {
        this.searchResults.classList.add('show');
        this.isOpen = true;
    }
    
    hideResults() {
        this.searchResults.classList.remove('show');
        this.isOpen = false;
        this.selectedIndex = -1;
    }
    
    handleBlur(e) {
        // Delay hiding to allow clicks on results
        setTimeout(() => {
            if (!this.searchResults.contains(document.activeElement)) {
                this.hideResults();
            }
        }, 150);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.navigationManager = new NavigationManager();
    window.searchManager = new SearchManager();
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { NavigationManager, SearchManager };
}
