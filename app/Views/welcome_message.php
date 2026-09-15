<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
            <!-- FILTER CARD -->
            <div class="filter-card" id="filterCard">

                <!-- Industry Tabs -->
                <div class="industry-tabs">
                    <!-- Textiles -->
                    <button class="ind-tab active" onclick="setTab(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1" />
                            <rect x="14" y="3" width="7" height="7" rx="1" />
                            <rect x="3" y="14" width="7" height="7" rx="1" />
                            <rect x="14" y="14" width="7" height="7" rx="1" />
                        </svg>
                        Textiles
                    </button>
                    <!-- Leather -->
                    <button class="ind-tab" onclick="setTab(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" />
                            <path d="M8 12s1-2 4-2 4 2 4 2" />
                        </svg>
                        Leather
                    </button>
                    <!-- Polymers -->
                    <button class="ind-tab" onclick="setTab(this)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3" />
                            <circle cx="4" cy="6" r="2" />
                            <circle cx="20" cy="6" r="2" />
                            <circle cx="4" cy="18" r="2" />
                            <circle cx="20" cy="18" r="2" />
                            <line x1="6" y1="6" x2="10" y2="10" />
                            <line x1="18" y1="6" x2="14" y2="10" />
                            <line x1="6" y1="18" x2="10" y2="14" />
                            <line x1="18" y1="18" x2="14" y2="14" />
                        </svg>
                        Polymers
                    </button>
                </div>

                <!-- Filter dropdowns -->
                <div class="filter-dropdowns" id="filterDropdowns">
                    <select class="filter-select active-filter">
                        <option selected>Chemical supplier</option>
                        <option>Test CS</option>
                        <option>Supplier A</option>
                    </select>
                    <select class="filter-select">
                        <option selected>Category</option>
                        <option>Textile auxiliaries and functional agents</option>
                        <option>Dyestuffs</option>
                    </select>
                    <select class="filter-select">
                        <option selected>Subcategory</option>
                        <option>Softening agents</option>
                        <option>Wetting agents</option>
                    </select>
                    <select class="filter-select">
                        <option selected>Product range</option>
                        <option>Persoftal</option>
                    </select>
                    <select class="filter-select">
                        <option selected>Rating</option>
                        <option>BLUE</option>
                        <option>GREEN</option>
                    </select>
                    <select class="filter-select">
                        <option selected>Usage range</option>
                        <option>A/B/C</option>
                    </select>
                    <select class="filter-select">
                        <option selected>Sustainability attributes</option>
                        <option>Renewable feedstock</option>
                        <option>Recycled</option>
                    </select>
                    <button class="btn-last">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        Last 3 months
                    </button>
                    <button class="btn-clear">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="1 4 1 10 7 10" />
                            <path d="M3.51 15a9 9 0 1 0 .49-3.53" />
                        </svg>
                        CLEAR
                    </button>
                </div>

                <div class="hide-filters-btn" id="hideFiltersBtn" onclick="toggleFilters()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15" />
                    </svg>
                    Hide Filters
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15" />
                    </svg>
                </div>
            </div>

            <!-- RESULTS TABLE -->
            <div class="results-card">
                <div style="overflow-x:auto;">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th style="width:40px;"></th>
                                <th class="col-toolid">
                                    TOOL ID
                                    <span class="sort-icons">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="m18 15-6-6-6 6" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="m6 9 6 6 6-6" />
                                        </svg>
                                    </span>
                                </th>
                                <th>
                                    Product
                                    <span class="sort-icons">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="m18 15-6-6-6 6" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="m6 9 6 6 6-6" />
                                        </svg>
                                    </span>
                                </th>
                                <th class="col-industry">Industry</th>
                                <th class="col-supplier">
                                    Chemical supplier
                                    <span class="sort-icons">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="m18 15-6-6-6 6" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="m6 9 6 6 6-6" />
                                        </svg>
                                    </span>
                                </th>
                                <th class="col-subcat">
                                    Subcategory
                                    <span class="sort-icons">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="m18 15-6-6-6 6" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="m6 9 6 6 6-6" />
                                        </svg>
                                    </span>
                                </th>
                                <th class="col-range">
                                    Product range
                                    <span class="sort-icons">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="m18 15-6-6-6 6" />
                                        </svg>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <path d="m6 9 6 6 6-6" />
                                        </svg>
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Main row -->
                            <tr class="main-row" onclick="toggleDetail('row1', this)">
                                <td>
                                    <button class="expand-btn" id="btn-row1">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m6 9 6 6 6-6" />
                                        </svg>
                                    </button>
                                </td>
                                <td><span class="tool-id-badge">30057</span></td>
                                <td><span class="product-name">SAM TEST RENEWABLE FEEDSTOCK</span></td>
                                <td class="col-industry">
                                    <svg class="industry-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="3" width="7" height="7" rx="1" />
                                        <rect x="14" y="3" width="7" height="7" rx="1" />
                                        <rect x="3" y="14" width="7" height="7" rx="1" />
                                        <rect x="14" y="14" width="7" height="7" rx="1" />
                                    </svg>
                                </td>
                                <td class="col-supplier"><span class="supplier-name">Test CS</span></td>
                                <td class="col-subcat"><span class="subcat-text">Softening agents</span></td>
                                <td class="col-range"><span class="prodrange-text">Persoftal</span></td>
                            </tr>
                            <!-- Detail row -->
                            <tr class="detail-row" id="detail-row1">
                                <td colspan="7">
                                    <div class="detail-inner" id="inner-row1">

                                        <!-- Industry icon -->
                                        <div class="detail-industry">
                                            <div class="detail-industry-icon">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="7" height="7" rx="1" />
                                                    <rect x="14" y="3" width="7" height="7" rx="1" />
                                                    <rect x="3" y="14" width="7" height="7" rx="1" />
                                                    <rect x="14" y="14" width="7" height="7" rx="1" />
                                                </svg>
                                            </div>
                                            <span class="detail-industry-label">Textiles</span>
                                        </div>

                                        <!-- Info table -->
                                        <div class="detail-info">
                                            <table>
                                                <tr>
                                                    <td class="detail-label">TOOL ID</td>
                                                    <td>30057</td>
                                                </tr>
                                                <tr>
                                                    <td class="detail-label">Product</td>
                                                    <td>SAM TEST RENEWABLE FEEDSTOCK</td>
                                                </tr>
                                                <tr>
                                                    <td class="detail-label">Chemical supplier</td>
                                                    <td>
                                                        <a href="#" class="detail-link">Test CS</a>
                                                        &nbsp;
                                                        <a href="#" class="detail-link">Open Details ↗</a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="detail-label">Category</td>
                                                    <td><a href="#" class="detail-link">Textile auxiliaries and functional agents</a></td>
                                                </tr>
                                                <tr>
                                                    <td class="detail-label">Subcategory</td>
                                                    <td><a href="#" class="detail-link">Softening agents</a></td>
                                                </tr>
                                                <tr>
                                                    <td class="detail-label">Product range</td>
                                                    <td><a href="#" class="detail-link">Persoftal</a></td>
                                                </tr>
                                                <tr>
                                                    <td class="detail-label">Usage range</td>
                                                    <td>A/B/C</td>
                                                </tr>
                                                <tr>
                                                    <td class="detail-label">Rating</td>
                                                    <td><span class="badge-rating badge-blue">BLUE</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="detail-label">Creation date</td>
                                                    <td>18.04.2023</td>
                                                </tr>
                                            </table>
                                        </div>

                                        <!-- Description -->
                                        <div class="detail-desc">
                                            <h6>Description</h6>
                                            <p>Softening and smoothing agent Test with Oussama &amp; Joanna on 02.04.2026, 14:16 SAM; purposely made it blue-rated</p>
                                        </div>

                                        <!-- Sustainability attributes -->
                                        <div class="detail-sustain">
                                            <h6>Sustainability attributes</h6>
                                            <div class="sustain-item">
                                                <div class="sustain-icon">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M2 20h20M6 20V10l6-8 6 8v10M10 20v-5h4v5" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span class="sustain-label">Made from Renewable Feedstock:</span>
                                                    <span class="sustain-text">40% biobased carbon as fraction of total organic carbon (TOC) (plant source)</span>
                                                </div>
                                            </div>
                                            <div class="sustain-item">
                                                <div class="sustain-icon">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M12 2a10 10 0 0 1 10 10H2A10 10 0 0 1 12 2z" />
                                                        <path d="M12 22a10 10 0 0 0 10-10H2a10 10 0 0 0 10 10z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span class="sustain-label">Made from Certified Renewable Feedstock:</span>
                                                    <span class="sustain-text">99.9% certified biobased carbon as fraction of total organic carbon (TOC) (plant source)</span>
                                                </div>
                                            </div>
                                            <div class="sustain-item">
                                                <div class="sustain-icon">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="1 4 1 10 7 10" />
                                                        <polyline points="23 20 23 14 17 14" />
                                                        <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4-4.64 4.36A9 9 0 0 1 3.51 15" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span class="sustain-label">Made from Recycled Feedstock:</span>
                                                    <span class="sustain-text">&mdash;</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PAGINATION FOOTER -->
            <div class="results-footer">
                <div class="result-count">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    <strong>1</strong>&nbsp;bluesign® APPROVED chemical product
                </div>
                <div class="rows-selector">
                    <span>Rows</span>
                    <select class="rows-select">
                        <option>10</option>
                        <option>25</option>
                        <option>50</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button class="page-btn active">1</button>
                </div>
            </div>
<?= $this->endSection() ?>