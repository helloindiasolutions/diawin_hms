/**
 * Global Search Engine
 * High-performance search with debouncing, dynamic tabs, and categorized results
 * @version 2.0.0
 */

// Prevent class re-declaration during SPA navigation
if (typeof window.GlobalSearch === 'undefined') {

    class GlobalSearch {
        constructor() {
            this.modal = null;
            this.input = null;
            this.resultsContainer = null;
            this.tabsContainer = null;
            this.searchTimeout = null;
            this.debounceDelay = 300; // ms - optimized for performance
            this.minSearchLength = 2;
            this.currentIndex = -1;
            this.activeTab = 'all';
            this.results = {
                navigation: [],
                patients: [],
                appointments: [],
                all: []
            };
            this.recentSearches = this.loadRecentSearches();
            this.abortController = null; // For canceling previous requests

            this.init();
        }

        init() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.setup());
            } else {
                this.setup();
            }
        }

        setup() {
            this.modal = document.getElementById('globalSearchModal');
            this.input = document.getElementById('globalSearchInput');
            this.resultsContainer = document.getElementById('searchResultsContainer');
            this.tabsContainer = document.querySelector('.search-tabs');

            if (!this.modal || !this.input) {
                console.warn('Global search elements not found');
                return;
            }

            this.attachEventListeners();
        }

        attachEventListeners() {
            // Keyboard shortcut (Ctrl+K / Cmd+K)
            document.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    this.open();
                }
            });

            // Search input with debouncing
            this.input.addEventListener('input', (e) => {
                this.handleSearch(e.target.value);
            });

            // Keyboard navigation
            this.input.addEventListener('keydown', (e) => {
                this.handleKeyboardNavigation(e);
            });

            // Tab switching
            if (this.tabsContainer) {
                this.tabsContainer.addEventListener('click', (e) => {
                    const tab = e.target.closest('.search-tab');
                    if (tab) {
                        const tabType = tab.dataset.tab;
                        this.switchTab(tabType);
                    }
                });
            }

            // Modal events
            this.modal.addEventListener('shown.bs.modal', () => {
                this.input.focus();
                this.showRecentSearches();
            });

            this.modal.addEventListener('hidden.bs.modal', () => {
                this.reset();
            });
        }

        open() {
            const modalInstance = new bootstrap.Modal(this.modal);
            modalInstance.show();
        }

        close() {
            const modalInstance = bootstrap.Modal.getInstance(this.modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }

        reset() {
            this.input.value = '';
            this.currentIndex = -1;
            this.activeTab = 'all';
            this.results = { navigation: [], patients: [], appointments: [], all: [] };
            this.showEmpty();
            this.hideTabCounts();
        }

        handleSearch(query) {
            // PROFESSIONAL FIX: Removed AbortController as per user request to prevent 'canceled' requests
            // We still use debouncing to keep performance high

            // Clear previous timeout
            clearTimeout(this.searchTimeout);

            // Trim query
            query = query.trim();

            // Show appropriate state
            if (query.length < this.minSearchLength) {
                if (query.length === 0) {
                    this.showRecentSearches();
                } else {
                    this.showEmpty();
                }
                return;
            }

            // Show loading
            this.showLoading();

            // Debounce search
            this.searchTimeout = setTimeout(() => {
                this.performSearch(query);
            }, this.debounceDelay);
        }

        async performSearch(query) {
            try {
                // Search in parallel for better performance
                const [navigationResults, patientResults, appointmentResults] = await Promise.all([
                    this.searchNavigation(query),
                    this.searchPatients(query),
                    this.searchAppointments(query)
                ]);

                // Store results by category
                this.results = {
                    navigation: navigationResults,
                    patients: patientResults,
                    appointments: appointmentResults,
                    all: [...navigationResults, ...patientResults, ...appointmentResults]
                };

                // Update tab counts dynamically
                this.updateTabCounts();

                // Display results for active tab
                this.displayResults();

            } catch (error) {
                console.error('Search error:', error);
                this.showNoResults();
            }
        }

        async searchNavigation(query) {
            const menuItems = this.getMenuItems();
            const matches = this.fuzzyMatch(menuItems, query, ['label', 'keywords']);

            return matches.slice(0, 10).map(item => ({
                type: 'navigation',
                icon: item.icon || 'ri-pages-line',
                title: this.highlightMatch(item.label, query),
                subtitle: 'Navigation',
                url: item.url,
                badge: 'Navigate',
                badgeClass: 'success'
            }));
        }

        async searchPatients(query) {
            try {
                const response = await fetch(
                    `/api/v1/search/patients?q=${encodeURIComponent(query)}&limit=10`
                );

                if (!response.ok) return [];

                const data = await response.json();
                if (!data.success || !data.data) return [];

                const isNumeric = /^\d+$/.test(query);

                return data.data.map(patient => {
                    const fullName = patient.name || (patient.first_name + ' ' + (patient.last_name || ''));
                    const mobile = patient.mobile || 'No mobile';

                    // USER REQUEST: Swap order based on search type
                    let displayTitle, displaySubtitle;
                    if (isNumeric) {
                        displayTitle = this.highlightMatch(mobile, query);
                        displaySubtitle = `${fullName} • ${patient.age || 'N/A'} yrs • MRN: ${patient.mrn}`;
                    } else {
                        displayTitle = this.highlightMatch(fullName, query);
                        displaySubtitle = `${mobile} • ${patient.age || 'N/A'} yrs • MRN: ${patient.mrn}`;
                    }

                    return {
                        type: 'patient',
                        icon: 'ri-user-line',
                        title: displayTitle,
                        subtitle: displaySubtitle,
                        url: `/patients/${patient.patient_id}`,
                        badge: 'Patient',
                        badgeClass: 'primary'
                    };
                });
            } catch (error) {
                console.error('Patient search error:', error);
                return [];
            }
        }

        async searchAppointments(query) {
            try {
                const response = await fetch(
                    `/api/v1/search/appointments?q=${encodeURIComponent(query)}&limit=10`
                );

                if (!response.ok) return [];

                const data = await response.json();
                if (!data.success || !data.data) return [];

                const isNumeric = /^\d+$/.test(query);

                return data.data.map(apt => {
                    const patientName = apt.patient_name;
                    const mobile = apt.patient_mobile || 'No mobile';

                    let displayTitle, displaySubtitle;
                    if (isNumeric) {
                        displayTitle = this.highlightMatch(mobile, query);
                        displaySubtitle = `${patientName} • ${apt.appointment_date} • ${apt.provider_name || 'No provider'}`;
                    } else {
                        displayTitle = this.highlightMatch(patientName, query);
                        displaySubtitle = `${mobile} • ${apt.appointment_date} • ${apt.provider_name || 'No provider'}`;
                    }

                    return {
                        type: 'appointment',
                        icon: 'ri-calendar-line',
                        title: displayTitle,
                        subtitle: displaySubtitle,
                        url: `/appointments/${apt.appointment_id}`,
                        badge: 'Appointment',
                        badgeClass: 'warning'
                    };
                });
            } catch (error) {
                console.error('Appointment search error:', error);
                return [];
            }
        }

        getMenuItems() {
            const menuItems = [];
            document.querySelectorAll('.side-menu__item').forEach(item => {
                const label = item.querySelector('.side-menu__label')?.textContent.trim();
                const href = item.getAttribute('href');
                const iconEl = item.querySelector('.side-menu__icon, i[class*="ri-"]');
                const icon = iconEl?.className || 'ri-pages-line';

                if (label && href && href !== '#' && href !== 'javascript:void(0);') {
                    menuItems.push({
                        label,
                        url: href,
                        icon,
                        keywords: label.toLowerCase()
                    });
                }
            });
            return menuItems;
        }

        fuzzyMatch(items, query, searchFields) {
            const queryLower = query.toLowerCase();
            const matches = [];

            items.forEach(item => {
                let score = 0;
                let matchFound = false;

                searchFields.forEach(field => {
                    const value = item[field]?.toLowerCase() || '';

                    if (value === queryLower) {
                        score += 100;
                        matchFound = true;
                    } else if (value.startsWith(queryLower)) {
                        score += 50;
                        matchFound = true;
                    } else if (value.includes(queryLower)) {
                        score += 25;
                        matchFound = true;
                    } else {
                        // Fuzzy character matching
                        let queryIndex = 0;
                        for (let i = 0; i < value.length && queryIndex < queryLower.length; i++) {
                            if (value[i] === queryLower[queryIndex]) {
                                queryIndex++;
                                score += 1;
                            }
                        }
                        if (queryIndex === queryLower.length) {
                            matchFound = true;
                        }
                    }
                });

                if (matchFound) {
                    matches.push({ ...item, score });
                }
            });

            return matches.sort((a, b) => b.score - a.score);
        }

        highlightMatch(text, query) {
            if (!text || !query) return text;
            const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
            return text.replace(regex, '<mark>$1</mark>');
        }

        updateTabCounts() {
            if (!this.tabsContainer) return;

            const counts = {
                all: this.results.all.length,
                patients: this.results.patients.length,
                appointments: this.results.appointments.length,
                navigation: this.results.navigation.length
            };

            const hasResults = counts.all > 0;

            // Update each tab count dynamically - show/hide based on results
            this.tabsContainer.querySelectorAll('.search-tab').forEach(tab => {
                const tabType = tab.dataset.tab;
                const countEl = tab.querySelector('.tab-count');
                if (countEl && counts[tabType] !== undefined) {
                    countEl.textContent = counts[tabType];
                    // Show count only when there are results
                    if (hasResults) {
                        countEl.classList.remove('d-none');
                    } else {
                        countEl.classList.add('d-none');
                    }
                }
            });
        }

        hideTabCounts() {
            if (!this.tabsContainer) return;
            this.tabsContainer.querySelectorAll('.tab-count').forEach(countEl => {
                countEl.classList.add('d-none');
            });
        }

        switchTab(tabType) {
            this.activeTab = tabType;

            // Update active tab UI
            this.tabsContainer.querySelectorAll('.search-tab').forEach(tab => {
                if (tab.dataset.tab === tabType) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });

            // Display results for selected tab
            this.displayResults();
        }

        displayResults() {
            const resultsToShow = this.activeTab === 'all'
                ? this.results.all
                : this.results[this.activeTab] || [];

            if (resultsToShow.length === 0) {
                this.showNoResults();
                return;
            }

            // Group by type if showing all
            let html = '';
            if (this.activeTab === 'all') {
                // Check if we have items that aren't in standard categories (e.g. Recent Searches)
                const uncategorized = this.results.all.filter(item => !['navigation', 'patient', 'appointment'].includes(item.type));

                if (uncategorized.length > 0) {
                    html += this.renderCategory('RECENT & OTHERS', uncategorized);
                }

                if (this.results.navigation.length > 0) {
                    html += this.renderCategory('NAVIGATION', this.results.navigation);
                }
                if (this.results.patients.length > 0) {
                    html += this.renderCategory('PATIENTS', this.results.patients);
                }
                if (this.results.appointments.length > 0) {
                    html += this.renderCategory('APPOINTMENTS', this.results.appointments);
                }
            } else {
                html = this.renderResults(resultsToShow);
            }

            this.resultsContainer.innerHTML = html;
            this.showResults();
            this.currentIndex = -1;
            this.attachResultHandlers();
        }

        renderCategory(title, items) {
            return `
            <div class="search-category">
                <div class="search-category-title">${title}</div>
                ${this.renderResults(items)}
            </div>
        `;
        }

        renderResults(items) {
            return items.map((item, index) => `
            <a href="${item.url}" class="search-result-item" data-index="${index}">
                <div class="search-result-icon">
                    <i class="${item.icon}"></i>
                </div>
                <div class="search-result-content">
                    <div class="search-result-title">${item.title}</div>
                    <div class="search-result-subtitle">${item.subtitle}</div>
                </div>
                <span class="search-result-badge bg-${item.badgeClass}-transparent text-${item.badgeClass}">${item.badge}</span>
            </a>
        `).join('');
        }

        attachResultHandlers() {
            document.querySelectorAll('.search-result-item').forEach((item, index) => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = item.getAttribute('href');
                    const title = item.querySelector('.search-result-title').textContent.replace(/<[^>]*>/g, '');
                    this.saveRecentSearch(title, url);
                    this.close();
                    window.location.href = url;
                });

                item.addEventListener('mouseenter', () => {
                    this.setActiveIndex(index);
                });
            });
        }

        handleKeyboardNavigation(e) {
            const items = document.querySelectorAll('.search-result-item');
            if (items.length === 0) return;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.currentIndex = Math.min(this.currentIndex + 1, items.length - 1);
                    this.setActiveIndex(this.currentIndex);
                    break;

                case 'ArrowUp':
                    e.preventDefault();
                    this.currentIndex = Math.max(this.currentIndex - 1, 0);
                    this.setActiveIndex(this.currentIndex);
                    break;

                case 'Enter':
                    e.preventDefault();
                    if (this.currentIndex >= 0 && items[this.currentIndex]) {
                        items[this.currentIndex].click();
                    }
                    break;

                case 'Escape':
                    e.preventDefault();
                    this.close();
                    break;
            }
        }

        setActiveIndex(index) {
            const items = document.querySelectorAll('.search-result-item');
            items.forEach((item, i) => {
                if (i === index) {
                    item.classList.add('active');
                    item.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                } else {
                    item.classList.remove('active');
                }
            });
            this.currentIndex = index;
        }

        showLoading() {
            document.getElementById('searchLoading')?.classList.remove('d-none');
            document.getElementById('searchEmpty')?.classList.add('d-none');
            document.getElementById('searchNoResults')?.classList.add('d-none');
            this.resultsContainer?.classList.add('d-none');
        }

        showEmpty() {
            document.getElementById('searchLoading')?.classList.add('d-none');
            document.getElementById('searchEmpty')?.classList.remove('d-none');
            document.getElementById('searchNoResults')?.classList.add('d-none');
            this.resultsContainer?.classList.add('d-none');
        }

        showNoResults() {
            document.getElementById('searchLoading')?.classList.add('d-none');
            document.getElementById('searchEmpty')?.classList.add('d-none');
            document.getElementById('searchNoResults')?.classList.remove('d-none');
            this.resultsContainer?.classList.add('d-none');
        }

        showResults() {
            document.getElementById('searchLoading')?.classList.add('d-none');
            document.getElementById('searchEmpty')?.classList.add('d-none');
            document.getElementById('searchNoResults')?.classList.add('d-none');
            this.resultsContainer?.classList.remove('d-none');
        }

        showRecentSearches() {
            if (this.recentSearches.length === 0) {
                this.showEmpty();
                return;
            }

            const results = this.recentSearches.map(search => ({
                type: 'recent',
                icon: 'ri-history-line',
                title: search.title,
                subtitle: 'Recent search',
                url: search.url,
                badge: 'Recent',
                badgeClass: 'secondary'
            }));

            this.results = { all: results, navigation: [], patients: [], appointments: [] };
            this.updateTabCounts();
            this.displayResults();
        }

        saveRecentSearch(title, url) {
            const search = { title, url, timestamp: Date.now() };
            this.recentSearches = [search, ...this.recentSearches.filter(s => s.url !== url)].slice(0, 5);
            localStorage.setItem('recentSearches', JSON.stringify(this.recentSearches));
        }

        loadRecentSearches() {
            try {
                return JSON.parse(localStorage.getItem('recentSearches') || '[]');
            } catch {
                return [];
            }
        }
    }

    // Make class globally accessible
    window.GlobalSearch = GlobalSearch;

    // Initialize global search (only if not already initialized)
    if (!window.globalSearch) {
        window.globalSearch = new GlobalSearch();
    }

} // End of SPA guard if block
