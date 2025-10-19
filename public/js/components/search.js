/* Search JavaScript */
/* Location: public/js/components/search.js */

class SearchManager {
    constructor() {
        this.searchInput = document.getElementById('globalSearch');
        this.searchResults = document.getElementById('searchResults');
        this.isOpen = false;
        this.searchTimeout = null;
        this.currentResults = [];
        this.selectedIndex = -1;
        this.searchCache = new Map();
        this.cacheExpiry = 5 * 60 * 1000; // 5 minutes
        
        this.init();
    }
    
    init() {
        if (!this.searchInput) return;
        
        this.setupEventListeners();
        this.setupKeyboardShortcuts();
        this.setupSearchStyles();
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
                this.focusSearch();
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
    
    setupSearchStyles() {
        // Add CSS for search results if not already present
        if (!document.getElementById('searchStyles')) {
            const style = document.createElement('style');
            style.id = 'searchStyles';
            style.textContent = `
                .search-result-item {
                    display: flex;
                    align-items: center;
                    padding: var(--spacing-sm) var(--spacing-md);
                    cursor: pointer;
                    transition: var(--transition);
                    border-bottom: 1px solid var(--header-border);
                }
                
                .search-result-item:hover,
                .search-result-item.selected {
                    background: var(--header-border);
                }
                
                .search-result-item:last-child {
                    border-bottom: none;
                }
                
                .result-icon {
                    margin-right: var(--spacing-sm);
                    font-size: 16px;
                    flex-shrink: 0;
                }
                
                .result-content {
                    flex: 1;
                    min-width: 0;
                }
                
                .result-title {
                    font-size: 14px;
                    font-weight: 600;
                    color: var(--header-text);
                    margin-bottom: 2px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                
                .result-subtitle {
                    font-size: 12px;
                    color: var(--header-text-secondary);
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                
                .result-type {
                    font-size: 10px;
                    color: var(--header-text-secondary);
                    background: var(--header-border);
                    padding: 2px 6px;
                    border-radius: var(--border-radius-sm);
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    flex-shrink: 0;
                }
                
                .search-no-results,
                .search-error {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: var(--spacing-lg);
                    color: var(--header-text-secondary);
                    text-align: center;
                }
                
                .no-results-icon,
                .error-icon {
                    font-size: 24px;
                    margin-bottom: var(--spacing-sm);
                }
                
                .no-results-text,
                .error-text {
                    font-size: 14px;
                }
                
                .search-loading {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: var(--spacing-lg);
                }
                
                .search-spinner {
                    width: 16px;
                    height: 16px;
                    border: 2px solid var(--header-border);
                    border-top: 2px solid var(--brand-primary);
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin-right: var(--spacing-sm);
                }
                
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    focusSearch() {
        this.searchInput.focus();
        this.searchInput.select();
    }
    
    handleSearch(e) {
        const query = e.target.value.trim();
        
        if (query.length < 2) {
            this.hideResults();
            return;
        }
        
        // Check cache first
        const cachedResult = this.getCachedResult(query);
        if (cachedResult) {
            this.displayResults(cachedResult);
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
            // Show loading state
            this.showLoading();
            
            const response = await fetch(`${window.baseUrl}/app/api/search.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ 
                    query,
                    user_id: window.currentUser?.id || 0,
                    role: window.userRole || 'user'
                })
            });
            
            if (!response.ok) {
                throw new Error(`Search failed: ${response.status}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                this.cacheResult(query, result.data);
                this.displayResults(result.data);
            } else {
                throw new Error(result.message || 'Search failed');
            }
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
            <div class="search-result-item" data-index="${index}" data-url="${result.url}">
                <div class="result-icon">${result.icon}</div>
                <div class="result-content">
                    <div class="result-title">${this.highlightText(result.title, this.searchInput.value)}</div>
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
                <div class="no-results-text">No results found for "${this.searchInput.value}"</div>
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
    
    showLoading() {
        this.searchResults.innerHTML = `
            <div class="search-loading">
                <div class="search-spinner"></div>
                <div class="loading-text">Searching...</div>
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
            
            // Track search selection for analytics
            this.trackSearchSelection(result);
            
            // Navigate to result
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
    
    highlightText(text, query) {
        if (!query) return text;
        
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }
    
    cacheResult(query, results) {
        const cacheKey = query.toLowerCase();
        this.searchCache.set(cacheKey, {
            results,
            timestamp: Date.now()
        });
        
        // Clean old cache entries
        this.cleanCache();
    }
    
    getCachedResult(query) {
        const cacheKey = query.toLowerCase();
        const cached = this.searchCache.get(cacheKey);
        
        if (cached && (Date.now() - cached.timestamp) < this.cacheExpiry) {
            return cached.results;
        }
        
        return null;
    }
    
    cleanCache() {
        const now = Date.now();
        for (const [key, value] of this.searchCache.entries()) {
            if ((now - value.timestamp) > this.cacheExpiry) {
                this.searchCache.delete(key);
            }
        }
    }
    
    trackSearchSelection(result) {
        // Send analytics data
        if (typeof gtag !== 'undefined') {
            gtag('event', 'search_select', {
                'search_term': this.searchInput.value,
                'result_type': result.type,
                'result_title': result.title
            });
        }
        
        // Log to console for debugging
        console.log('Search selection:', {
            query: this.searchInput.value,
            result: result
        });
    }
    
    // Public methods
    clearCache() {
        this.searchCache.clear();
    }
    
    setCacheExpiry(expiry) {
        this.cacheExpiry = expiry;
    }
    
    getSearchStats() {
        return {
            cacheSize: this.searchCache.size,
            cacheExpiry: this.cacheExpiry,
            isOpen: this.isOpen,
            currentQuery: this.searchInput.value
        };
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.searchManager = new SearchManager();
});

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SearchManager;
}
