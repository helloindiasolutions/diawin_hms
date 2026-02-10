<!-- Global Search Modal -->
<div class="modal fade" id="globalSearchModal" tabindex="-1" aria-labelledby="globalSearchModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-lg" style="margin-top: 8vh; margin-bottom: auto;">
        <div class="modal-content global-search-modal">
            <div class="modal-body p-0">
                <!-- Search Input -->
                <div class="global-search-input-wrapper">
                    <i class="ri-search-line search-icon"></i>
                    <input type="text" class="form-control global-search-input" id="globalSearchInput"
                        placeholder="Search patients, appointments, navigation..." autocomplete="off"
                        spellcheck="false">
                    <button class="btn btn-sm btn-light" onclick="globalSearch.close()">Clear</button>
                </div>

                <!-- Search Tabs/Filters - Counts hidden by default -->
                <div class="search-tabs-wrapper">
                    <div class="search-tabs">
                        <button class="search-tab active" data-tab="all">
                            All <span class="tab-count d-none">0</span>
                        </button>
                        <button class="search-tab" data-tab="patients">
                            <i class="ri-user-line"></i> Patients <span class="tab-count d-none">0</span>
                        </button>
                        <button class="search-tab" data-tab="appointments">
                            <i class="ri-calendar-line"></i> Appointments <span class="tab-count d-none">0</span>
                        </button>
                        <button class="search-tab" data-tab="navigation">
                            <i class="ri-compass-line"></i> Navigation <span class="tab-count d-none">0</span>
                        </button>
                    </div>
                </div>

                <!-- Search Results -->
                <div class="global-search-results" id="globalSearchResults">
                    <!-- Loading State with Skeleton -->
                    <div class="search-loading d-none" id="searchLoading">
                        <div class="skeleton-loader">
                            <div class="skeleton-item">
                                <div class="skeleton-icon"></div>
                                <div class="skeleton-content">
                                    <div class="skeleton-title"></div>
                                    <div class="skeleton-subtitle"></div>
                                </div>
                            </div>
                            <div class="skeleton-item">
                                <div class="skeleton-icon"></div>
                                <div class="skeleton-content">
                                    <div class="skeleton-title"></div>
                                    <div class="skeleton-subtitle"></div>
                                </div>
                            </div>
                            <div class="skeleton-item">
                                <div class="skeleton-icon"></div>
                                <div class="skeleton-content">
                                    <div class="skeleton-title"></div>
                                    <div class="skeleton-subtitle"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div class="search-empty" id="searchEmpty">
                        <i class="ri-search-2-line search-empty-icon"></i>
                        <p class="mb-1">Start typing to search</p>
                        <small class="text-muted">Search for patients, appointments, or navigate to any page</small>
                    </div>

                    <!-- No Results -->
                    <div class="search-no-results d-none" id="searchNoResults">
                        <i class="ri-file-search-line search-empty-icon"></i>
                        <p class="mb-0">No results found</p>
                        <small class="text-muted">Try different keywords</small>
                    </div>

                    <!-- Results Container -->
                    <div class="search-results-container d-none" id="searchResultsContainer">
                        <!-- Results will be dynamically inserted here -->
                    </div>
                </div>

                <!-- Search Footer -->
                <div class="global-search-footer">
                    <div class="search-footer-shortcuts">
                        <span><kbd>↑</kbd><kbd>↓</kbd> Navigate</span>
                        <span><kbd>↵</kbd> Select</span>
                        <span><kbd>ESC</kbd> Close</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Trigger Button (Add to header) -->
<style>
    .global-search-trigger {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.2rem 0.3rem;
        background: #1a1a1aff;
        border: 1px solid #34495e;
        border-radius: 10px;
        color: #95a5a6;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.9rem;
        font-weight: 400;
        min-width: 300px;
    }

    .global-search-trigger:hover {
        background: #34495e;
        border-color: #4a5f7f;
    }

    .global-search-trigger:active {
        transform: scale(0.98);
    }

    .global-search-trigger i {
        font-size: 1.125rem;
        color: #95a5a6;
    }

    .global-search-trigger input {
        color: #95a5a6 !important;
    }

    .global-search-trigger kbd {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
        background: #34495e;
        border: 1px solid #4a5f7f;
        border-radius: 4px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-weight: 600;
        color: #95a5a6;
        margin-left: auto;
    }

    [data-theme-mode="dark"] .global-search-trigger {
        background: rgba(255, 255, 255, 0.05);
        border-color: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.7);
    }

    [data-theme-mode="dark"] .global-search-trigger:hover {
        background: rgba(255, 255, 255, 0.08);
        border-color: rgba(255, 255, 255, 0.15);
    }

    [data-theme-mode="dark"] .global-search-trigger kbd {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.15);
        color: rgba(255, 255, 255, 0.7);
    }

    /* Skeleton Loader Styles */
    .skeleton-loader {
        padding: 1rem;
    }

    .skeleton-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .skeleton-item:last-child {
        border-bottom: none;
    }

    .skeleton-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.5s infinite;
    }

    .skeleton-content {
        flex: 1;
    }

    .skeleton-title {
        height: 14px;
        width: 60%;
        border-radius: 4px;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.5s infinite;
        margin-bottom: 8px;
    }

    .skeleton-subtitle {
        height: 10px;
        width: 40%;
        border-radius: 4px;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: skeleton-shimmer 1.5s infinite;
    }

    @keyframes skeleton-shimmer {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    /* Tab count styling */
    .search-tab .tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        font-size: 11px;
        font-weight: 600;
        background: #e2e8f0;
        border-radius: 10px;
        margin-left: 4px;
    }

    .search-tab.active .tab-count {
        background: var(--primary-color);
        color: #fff;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .global-search-trigger {
            min-width: auto;
            padding: 0.5rem 0.75rem;
        }

        .global-search-trigger input {
            display: none;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .global-search-trigger {
            min-width: auto;
            padding: 0.5rem 0.75rem;
        }
    }
</style>