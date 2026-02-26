<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noriks Call Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        /* Minimal overrides for compatibility - main styles in styles.css */

        /* Special view content areas - copy .main styles from styles.css */
        #smsDashboardContent,
        #smsSettingsContent,
        #smsAutomationContent,
        #buyersSettingsContent,
        #agentsContent,
        #followupsContent {
            margin-left: 260px;
            padding-top: 64px;
            min-height: 100vh;
            background: var(--content-bg);
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            width: 48px;
            height: 24px;
            display: inline-block;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--card-border);
            transition: 0.3s;
            border-radius: 24px;
        }
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }
        .toggle-switch input:checked + .toggle-slider {
            background-color: var(--accent-green);
        }
        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }
        .toggle-switch input:disabled + .toggle-slider {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Template Preview */
        .template-preview {
            margin-top: 12px;
            padding: 12px;
            background: var(--content-bg);
            border-radius: var(--radius-md);
            border: 1px solid var(--card-border);
            font-size: 13px;
            color: var(--text-secondary);
            line-height: 1.5;
            white-space: pre-wrap;
        }
        .template-preview-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        /* Page header in special views */
        .page-header {
            padding: 8px 16px;
        }

        /* Page title */
        .page-title-large {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        /* Table cards in special views - same as Leads */
        #buyersSettingsContent .table-card,
        #smsSettingsContent .table-card,
        #smsAutomationContent .table-card,
        #agentsContent .table-card {
            margin: 0 16px;
        }

        /* Collapsed sidebar */
        .sidebar.collapsed ~ #smsDashboardContent,
        .sidebar.collapsed ~ #smsSettingsContent,
        .sidebar.collapsed ~ #smsAutomationContent,
        .sidebar.collapsed ~ #buyersSettingsContent,
        .sidebar.collapsed ~ #agentsContent,
        .sidebar.collapsed ~ #followupsContent,
        .sidebar.collapsed ~ .dummy-selector-removed {
            margin-left: 70px;
        }

        /* Mobile */
        @media (max-width: 1024px) {
            #smsDashboardContent,
            #smsSettingsContent,
            #smsAutomationContent,
            #buyersSettingsContent,
            #agentsContent,
            #followupsContent,
            .dummy-selector-mobile-removed {
                margin-left: 0;
            }
        }

        /* Inline Status Select */
        .inline-status-select {
            padding: 4px 8px;
            font-size: 12px;
            border: 1px solid var(--card-border);
            border-radius: 6px;
            background: var(--card-bg);
            color: var(--text-primary);
            cursor: pointer;
            min-width: 130px;
        }
        .inline-status-select:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        /* Inline Notes Input with Save Button */
        .inline-notes-wrapper {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .inline-notes-input {
            padding: 4px 8px;
            font-size: 11px;
            border: 1px solid var(--card-border);
            border-radius: 4px;
            background: var(--content-bg);
            color: var(--text-primary);
            width: 120px;
            transition: all 0.2s;
        }
        .inline-notes-input:hover {
            border-color: var(--accent-blue);
        }
        .inline-notes-input:focus {
            outline: none;
            border-color: var(--accent-blue);
            background: var(--card-bg);
            width: 160px;
        }
        .inline-notes-input::placeholder {
            color: var(--text-muted);
            font-style: italic;
        }
        .inline-notes-input.has-notes {
            background: var(--accent-blue-light);
            border-color: var(--accent-blue);
        }
        .inline-notes-save {
            padding: 4px 6px;
            font-size: 12px;
            background: var(--accent-green);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            opacity: 0;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .inline-notes-save:hover {
            background: #16a34a;
        }
        .inline-notes-wrapper:focus-within .inline-notes-save,
        .inline-notes-save.show {
            opacity: 1;
        }
        .inline-notes-save.saving {
            background: var(--text-muted);
            cursor: not-allowed;
        }

        /* Large CREATE ORDER Button */
        .action-btn-order-large {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .action-btn-order-large:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        .action-btn-order-large i {
            font-size: 14px;
        }

        /* Hide show classes for mobile compatibility */
        .hide-mobile { }
        @media (max-width: 640px) { .hide-mobile { display: none !important; } }

        /* Content Type Tabs - Clean underlined style */
        .content-tabs {
            display: flex;
            gap: 0;
            padding: 0 16px;
            margin-bottom: 10px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            background: var(--card-bg);
            border-bottom: 1px solid var(--card-border);
        }
        .content-tab {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 8px 12px;
            background: none;
            border: none;
            border-bottom: 2px solid transparent;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .content-tab:hover {
            color: var(--text-primary);
        }
        .content-tab.active {
            color: var(--accent-blue);
            border-bottom-color: var(--accent-blue);
        }
        .content-tab i { font-size: 10px; opacity: 0.7; }
        .content-tab.active i { opacity: 1; }
        .content-tab .count {
            background: var(--content-bg);
            padding: 1px 5px;
            border-radius: 2px;
            font-size: 9px;
            font-weight: 600;
        }
        .content-tab.active .count {
            background: var(--accent-blue);
            color: #fff;
        }
        @media (max-width: 768px) {
            .content-tabs { padding: 0 12px; }
            .content-tab { padding: 6px 8px; font-size: 10px; }
            .content-tab span:not(.count) { display: none; }
            .content-tab i { font-size: 12px; }
        }

        /* Bulk Actions Bar */
        .bulk-bar {
            display: none;
            align-items: center;
            gap: 16px;
            padding: 12px 20px;
            background: var(--accent-blue);
            border-radius: var(--radius-lg);
            margin-bottom: 16px;
            color: #fff;
        }
        .bulk-bar.show { display: flex; }
        .bulk-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bulk-close:hover { background: rgba(255,255,255,0.3); }
        .selected-count { font-weight: 600; }
        .bulk-actions { margin-left: auto; display: flex; gap: 8px; }
        .bulk-btn {
            padding: 8px 16px;
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: var(--radius-md);
            color: white;
            cursor: pointer;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .bulk-btn:hover { background: rgba(255,255,255,0.3); }

        /* Table Checkbox */
        .row-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--accent-blue);
        }
        .checkbox-cell { width: 40px; text-align: center; }

        /* Follow-ups styles */
        .followup-card {
            background: var(--card-bg);
            border-radius: var(--radius-lg);
            padding: 16px;
            margin-bottom: 12px;
            border-left: 4px solid var(--accent-blue);
            transition: all 0.2s;
        }
        .followup-card:hover { transform: translateX(4px); }
        .followup-card.due { border-left-color: var(--accent-red); background: var(--accent-red-light); }
        .followup-card.today { border-left-color: var(--accent-orange); }
        .followup-card.tomorrow { border-left-color: var(--accent-green); }
        .followup-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }
        .followup-customer { font-weight: 600; font-size: 15px; }
        .followup-time {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .followup-time.due { background: var(--accent-red); color: white; }
        .followup-time.today { background: var(--accent-orange); color: white; }
        .followup-time.tomorrow { background: var(--accent-green); color: white; }
        .followup-time.future { background: var(--content-bg); }
        .followup-details { font-size: 13px; color: var(--text-muted); margin-bottom: 12px; }
        .followup-notes {
            background: var(--content-bg);
            padding: 10px;
            border-radius: var(--radius-md);
            font-size: 13px;
            margin-bottom: 12px;
        }
        .followup-actions { display: flex; gap: 8px; }

        /* Call Status Selector */
        .call-status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 8px;
            margin-bottom: 16px;
        }
        .call-status-option {
            padding: 12px;
            border: 2px solid var(--card-border);
            border-radius: var(--radius-md);
            cursor: pointer;
            text-align: center;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
            background: var(--card-bg);
        }
        .call-status-option:hover { border-color: var(--accent-blue); }
        .call-status-option.selected { border-color: var(--accent-blue); background: var(--accent-blue-light); color: var(--accent-blue); }
        .call-status-option.converted.selected { border-color: var(--accent-green); background: var(--accent-green-light); color: var(--accent-green); }
        .call-status-option.not_interested.selected { border-color: var(--accent-red); background: var(--accent-red-light); color: var(--accent-red); }
        .call-status-option.callback.selected { border-color: var(--accent-orange); background: var(--accent-orange-light); color: var(--accent-orange); }

        .leaderboard-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--content-bg);
            border-radius: var(--radius-md);
            margin-bottom: 8px;
        }
        .leaderboard-rank {
            width: 28px; height: 28px;
            background: var(--accent-blue);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            color: #fff;
        }
        .leaderboard-rank.gold { background: #fbbf24; }
        .leaderboard-rank.silver { background: #94a3b8; }
        .leaderboard-rank.bronze { background: #d97706; }
        .leaderboard-info { flex: 1; }
        .leaderboard-name { font-weight: 600; }
        .leaderboard-stats { font-size: 12px; color: var(--text-muted); }
        .leaderboard-rate { font-weight: 700; color: var(--accent-green); }

        /* Call log styles */
        .call-log-item {
            position: relative;
            margin-bottom: 16px;
            background: var(--content-bg);
            border-radius: var(--radius-md);
            padding: 14px;
        }
        .call-log-item::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 18px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent-blue);
            border: 2px solid var(--card-bg);
        }
        .call-log-item.converted::before { background: var(--accent-green); }
        .call-log-item.not_interested::before { background: var(--accent-red); }
        .call-log-item.callback::before { background: var(--accent-orange); }
        .call-log-item.no_answer::before { background: var(--text-muted); }
        .call-log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .call-log-status { font-weight: 600; font-size: 14px; }
        .call-log-time { font-size: 12px; color: var(--text-muted); }
        .call-log-agent { font-size: 12px; color: var(--accent-blue); margin-bottom: 4px; }
        .call-log-notes { font-size: 13px; color: var(--text-secondary); line-height: 1.5; }
        .call-log-duration { font-size: 12px; color: var(--text-muted); margin-top: 6px; }
        .call-log-callback {
            margin-top: 8px;
            padding: 8px 12px;
            background: var(--accent-orange-light);
            border-radius: var(--radius-md);
            font-size: 12px;
            color: var(--accent-orange);
        }

        /* Skeleton loading */
        .skeleton {
            background: linear-gradient(90deg, var(--content-bg) 25%, var(--card-bg) 50%, var(--content-bg) 75%);
            background-size: 200% 100%;
            animation: skeleton 1.5s ease infinite;
            border-radius: var(--radius-sm);
        }
        @keyframes skeleton {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .skeleton-row {
            display: flex;
            gap: 16px;
            padding: 16px;
            border-bottom: 1px solid var(--card-border);
        }
        .skeleton-avatar { width: 42px; height: 42px; border-radius: var(--radius-md); }
        .skeleton-text { height: 14px; flex: 1; }
        .skeleton-text.short { max-width: 100px; }

        /* Notes Button */
        .notes-btn {
            padding: 4px 8px;
            background: transparent;
            border: 1px solid var(--card-border);
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
            position: relative;
        }
        .notes-btn:hover {
            background: var(--content-bg);
            border-color: var(--accent-blue);
        }
        .notes-btn.has-notes {
            background: rgba(59, 130, 246, 0.1);
            border-color: var(--accent-blue);
        }
        .notes-btn .notes-dot {
            position: absolute;
            top: -3px;
            right: -3px;
            width: 8px;
            height: 8px;
            background: var(--accent-blue);
            border-radius: 50%;
        }

        /* Notes Modal */
        .notes-modal-content {
            max-width: 500px;
        }
        .notes-textarea {
            width: 100%;
            min-height: 150px;
            padding: 12px;
            border: 1px solid var(--card-border);
            border-radius: 8px;
            background: var(--content-bg);
            color: var(--text-primary);
            font-size: 14px;
            resize: vertical;
            font-family: inherit;
        }
        .notes-textarea:focus {
            outline: none;
            border-color: var(--accent-blue);
        }

        /* Toast container */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 400;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 90%;
        }
        .toast-item {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-left: 4px solid var(--accent-green);
            padding: 14px 20px;
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--card-shadow-hover);
            animation: toastIn 0.3s ease;
            min-width: 300px;
        }
        .toast-item.error { border-left-color: var(--accent-red); }
        .toast-item.info { border-left-color: var(--accent-blue); }
        .toast-item.warning { border-left-color: var(--accent-orange); }
        .toast-item i { font-size: 18px; }
        .toast-item.success i { color: var(--accent-green); }
        .toast-item.error i { color: var(--accent-red); }
        .toast-item.info i { color: var(--accent-blue); }
        .toast-item.warning i { color: var(--accent-orange); }
        .toast-item .toast-close {
            margin-left: auto;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
        }
        .toast-item .toast-close:hover { color: var(--text-primary); }
        @keyframes toastIn { from { transform: translateX(100%); opacity: 0; } }
        @keyframes toastOut { to { transform: translateX(100%); opacity: 0; } }
        .toast-item.removing { animation: toastOut 0.3s ease forwards; }

        /* Filter Pills */
        .filter-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: 20px;
            border: 1px solid var(--card-border);
            background: var(--bg-primary);
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }
        .filter-pill:hover {
            border-color: var(--accent-blue);
            color: var(--accent-blue);
            background: rgba(59, 130, 246, 0.1);
        }
        .filter-pill.active {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            color: white;
        }
        .filter-pill.active:hover {
            background: var(--accent-blue);
            color: white;
        }
        
        /* Urgent leads called row */
        .called-row {
            opacity: 0.6;
            background: var(--bg-secondary);
        }
        .called-row td { text-decoration: line-through; }
        .called-row td:first-child,
        .called-row td:last-child,
        .called-row td:nth-child(5) { text-decoration: none; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <!-- Header / Top Bar (outside main so it's always visible) -->
    <div class="top-bar" id="topBar">
        <button class="menu-btn" id="menuBtn"><i class="fas fa-bars"></i></button>
        <div class="page-title" id="pageTitle">Abandoned Carts</div>
        <div class="top-bar-actions">
            <button class="action-btn-header" onclick="refreshData()">
                <i class="fas fa-sync-alt"></i> <span class="hide-mobile">Refresh</span>
            </button>
        </div>
    </div>

    <!-- Main -->
    <main class="main" id="main">
        <!-- Stats Grid (at top) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-shopping-cart"></i></div>
                <div><div class="stat-value" id="statCarts">0</div><div class="stat-label">Abandoned Carts</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-euro-sign"></i></div>
                <div><div class="stat-value" id="statValue">€0</div><div class="stat-label">Total Value of Abandoned</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                <div><div class="stat-value" id="statPending">0</div><div class="stat-label">Pending Orders</div></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fas fa-comment-sms"></i></div>
                <div><div class="stat-value" id="statSms">0</div><div class="stat-label">SMS Sent Today</div></div>
            </div>
        </div>
        
        <!-- Country Tabs -->
        <div class="country-tabs" id="countryTabs"></div>

        <!-- Content Type Tabs (below country tabs) -->
        <div class="content-tabs" id="contentTabs">
            <button class="content-tab active" data-content="carts">
                <i class="fas fa-shopping-cart"></i>
                <span>Abandoned Carts</span>
                <span class="count" id="contentCount-carts">0</span>
            </button>
            <button class="content-tab" data-content="pending">
                <i class="fas fa-clock"></i>
                <span>Pending Orders</span>
                <span class="count" id="contentCount-pending">0</span>
            </button>
            <button class="content-tab" data-content="buyers">
                <i class="fas fa-user"></i>
                <span>Enkratni kupci</span>
                <span class="count" id="contentCount-buyers">0</span>
            </button>
            <button class="content-tab" data-content="paketomati">
                <i class="fas fa-box"></i>
                <span>Paketomati</span>
                <span class="count" id="contentCount-paketomati">0</span>
            </button>
            <button class="content-tab" data-content="urgent">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Nujno</span>
                <span class="count" id="contentCount-urgent">0</span>
            </button>
        </div>

        <div class="content">
            <!-- Urgent Add Button Bar (shown only for Nujno tab) -->
            <div id="urgentActionBar" style="display:none;margin-top:16px;margin-bottom:12px;">
                <button class="btn btn-save" id="addUrgentBtn" style="padding:10px 20px;font-size:14px;" onclick="showAddUrgentModal()">
                    <i class="fas fa-plus"></i> Dodaj nujni lead
                </button>
            </div>
            
            <div class="filters-bar">
                <input type="text" class="search-input" id="searchInput" placeholder="Search name, email, phone...">
                <select class="filter-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="not_called">Not Called</option>
                    <option value="no_answer_1">No Answer 1</option>
                    <option value="no_answer_2">No Answer 2</option>
                    <option value="no_answer_3">No Answer 3</option>
                    <option value="no_answer_4">No Answer 4+</option>
                    <option value="called_callback">Callback Scheduled</option>
                    <option value="called_interested">Interested</option>
                    <option value="called_not_interested">Not Interested</option>
                    <option value="invalid_number">Invalid Number</option>
                </select>
            </div>

            <!-- Bulk Actions Bar -->
            <div class="bulk-bar" id="bulkBar">
                <button class="bulk-close" onclick="clearSelection()"><i class="fas fa-times"></i></button>
                <span class="selected-count"><span id="selectedCount">0</span> selected</span>
                <div class="bulk-actions">
                    <button class="bulk-btn" onclick="bulkAddToSms()"><i class="fas fa-sms"></i> Add to SMS Queue</button>
                    <button class="bulk-btn" onclick="openBulkStatusModal()"><i class="fas fa-edit"></i> Change Status</button>
                </div>
            </div>

            <div class="table-card">
                <div id="tableContainer">
                    <div class="loading"><div class="spinner"></div>Loading...</div>
                </div>
            </div>
        </div>
    </main>

    <!-- Status Modal -->
    <div class="modal-bg" id="statusModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Update Status</div>
                <button class="modal-close" onclick="closeModal('statusModal')">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select class="form-select" id="modalStatus">
                        <option value="not_called">Not Called</option>
                        <option value="called">Called</option>
                        <option value="answered">Answered</option>
                        <option value="no_answer">No Answer</option>
                        <option value="converted">Converted</option>
                        <option value="not_interested">Not Interested</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea class="form-textarea" id="modalNotes" placeholder="Add notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeModal('statusModal')">Cancel</button>
                <button class="btn btn-save" onclick="saveStatus()">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Notes Modal REMOVED - Now inline in table -->

    <!-- Create Order Modal (Enhanced) -->
    <div class="modal-bg" id="orderModal">
        <div class="modal wide">
            <div class="modal-header">
                <div class="modal-title">🛒 Ustvari naročilo</div>
                <button class="modal-close" onclick="closeModal('orderModal')">×</button>
            </div>
            <div class="modal-body" id="orderModalBody">
                <!-- Customer Info -->
                <h4 style="margin-bottom:12px;color:var(--text-muted);font-size:12px;text-transform:uppercase;">Podatki stranke</h4>
                <div class="customer-info-card">
                    <div class="customer-info-row">
                        <div class="customer-info-field">
                            <label>Ime</label>
                            <input type="text" id="orderFirstName" placeholder="Ime">
                        </div>
                        <div class="customer-info-field">
                            <label>Priimek</label>
                            <input type="text" id="orderLastName" placeholder="Priimek">
                        </div>
                    </div>
                    <div class="customer-info-row">
                        <div class="customer-info-field">
                            <label>Email</label>
                            <input type="email" id="orderEmail" placeholder="email@example.com">
                        </div>
                        <div class="customer-info-field">
                            <label>Telefon</label>
                            <input type="tel" id="orderPhone" placeholder="+386...">
                        </div>
                    </div>
                    <div class="customer-info-row">
                        <div class="customer-info-field">
                            <label>Naslov</label>
                            <input type="text" id="orderAddress" placeholder="Ulica in številka">
                        </div>
                    </div>
                    <div class="customer-info-row">
                        <div class="customer-info-field">
                            <label>Mesto</label>
                            <input type="text" id="orderCity" placeholder="Mesto">
                        </div>
                        <div class="customer-info-field">
                            <label>Poštna</label>
                            <input type="text" id="orderPostcode" placeholder="1000">
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <h4 style="margin:20px 0 12px;color:var(--text-muted);font-size:12px;text-transform:uppercase;">Izdelki</h4>

                <!-- Product Search -->
                <div class="product-search-container">
                    <i class="fas fa-search product-search-icon"></i>
                    <input type="text" class="product-search-input" id="productSearchInput"
                           placeholder="Išči produkte po imenu ali SKU..."
                           autocomplete="off">
                    <i class="fas fa-spinner fa-spin product-search-spinner" id="productSearchSpinner"></i>
                    <div class="product-search-results" id="productSearchResults">
                        <!-- Results will be rendered here -->
                    </div>
                </div>

                <!-- Variation Selector (shown when selecting a variable product) -->
                <div class="variation-selector" id="variationSelector" style="display:none;">
                    <div class="variation-selector-header">
                        <img src="" alt="" class="variation-selector-img" id="variationProductImg">
                        <div>
                            <div class="variation-selector-title" id="variationProductName">Product Name</div>
                            <div class="variation-selector-price" id="variationProductPrice">€0.00</div>
                        </div>
                    </div>
                    <p style="font-size:12px;color:var(--text-muted);margin-bottom:8px;">Izberi variacijo:</p>
                    <div class="variation-options" id="variationOptions">
                        <!-- Options will be rendered here -->
                    </div>
                    <div class="variation-qty-row">
                        <label style="font-size:13px;color:var(--text-muted);">Količina:</label>
                        <input type="number" min="1" value="1" id="variationQty">
                        <button class="btn btn-save" style="flex:1;" onclick="addSelectedVariation()">
                            <i class="fas fa-plus"></i> Dodaj v naročilo
                        </button>
                        <button class="btn btn-cancel" style="flex:0;padding:12px;" onclick="cancelVariationSelection()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="order-items" id="orderItems">
                    <!-- Items will be rendered here -->
                </div>

                <!-- Options -->
                <div class="toggle-row">
                    <span class="toggle-label">🚚 Brezplačna dostava</span>
                    <div class="toggle" id="freeShippingToggle" onclick="toggleFreeShipping()"></div>
                </div>

                <!-- Summary -->
                <div class="order-summary">
                    <div class="order-summary-row">
                        <span>Izdelki</span>
                        <span id="orderSubtotal">€0.00</span>
                    </div>
                    <div class="order-summary-row" id="shippingRow">
                        <span>Dostava</span>
                        <span id="orderShipping">€5.00</span>
                    </div>
                    <div class="order-summary-row">
                        <span>SKUPAJ</span>
                        <span id="orderTotal">€0.00</span>
                    </div>
                </div>

                <p style="font-size:12px;color:var(--text-muted);margin-top:16px;">
                    <i class="fas fa-info-circle"></i> Naročilo bo ustvarjeno s statusom "Processing" in meta oznako "_call_center".
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeModal('orderModal')">Prekliči</button>
                <button class="btn btn-success" onclick="confirmCreateOrder()" id="createOrderBtn">
                    <i class="fas fa-check"></i> Ustvari naročilo
                </button>
            </div>
        </div>
    </div>

    <!-- SMS Modal -->
    <div class="modal-bg" id="smsModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">📱 Pošlji SMS</div>
                <button class="modal-close" onclick="closeModal('smsModal')">×</button>
            </div>
            <div class="modal-body">
                <div class="customer-info-card">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="avatar" id="smsAvatar">?</div>
                        <div>
                            <div style="font-weight:600;" id="smsCustomerName">Customer Name</div>
                            <div style="font-size:13px;color:var(--text-muted);" id="smsCustomerPhone">+123456789</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Predloga (avtomatski prevod za državo)</label>
                    <select class="form-select" id="smsTemplate" onchange="applySmsTemplate()">
                        <option value="">-- Izberi predlogo --</option>
                        <!-- Dynamically populated -->
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Sporočilo</label>
                    <textarea class="form-textarea" id="smsMessage" placeholder="Vnesite sporočilo..." oninput="updateCharCount()"></textarea>
                    <div class="char-count" id="smsCharCount">0 / 160 znakov</div>
                </div>

                <div class="message-preview" id="smsPreview" style="display:none;">
                    <strong style="font-size:11px;color:var(--text-muted);">PREDOGLED:</strong><br>
                    <span id="smsPreviewText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeModal('smsModal')">Prekliči</button>
                <button class="btn btn-save" onclick="queueSms()" id="sendSmsBtn" disabled title="SMS pošilja samo Dejan ročno">
                    <i class="fas fa-clock"></i> Dodaj v čakalno vrsto
                </button>
            </div>
        </div>
    </div>

    <!-- SMS Edit Modal (for editing phone before sending) -->
    <div class="modal-bg" id="smsEditModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">📱 Pošlji SMS</div>
                <button class="modal-close" onclick="closeModal('smsEditModal')">×</button>
            </div>
            <div class="modal-body">
                <div class="customer-info-card" style="margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="avatar" id="smsEditAvatar">?</div>
                        <div>
                            <div style="font-weight:600;" id="smsEditCustomerName">Customer Name</div>
                            <div style="font-size:12px;color:var(--text-muted);">Stranka</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Telefonska številka</label>
                    <input type="tel" class="form-input" id="smsEditPhone" placeholder="+38598xxxxxxx ali 098xxxxxxx">
                    <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                        <i class="fas fa-info-circle"></i> Podprti formati: +38598xxx, 38598xxx, 098xxx (sistem avtomatsko formatira)
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Sporočilo (samo za branje)</label>
                    <textarea class="form-textarea" id="smsEditMessage" readonly style="background:var(--content-bg);cursor:not-allowed;"></textarea>
                </div>

                <input type="hidden" id="smsEditId">
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeModal('smsEditModal')">Prekliči</button>
                <button class="btn btn-save" onclick="confirmSendSms()" style="background:var(--accent-green);">
                    <i class="fas fa-paper-plane"></i> Pošlji
                </button>
            </div>
        </div>
    </div>

    <!-- Customer 360° Modal -->
    <div class="modal-bg" id="customerModal">
        <div class="modal wide" style="max-width:800px;">
            <div class="modal-header">
                <div class="modal-title">👤 Customer 360° View</div>
                <button class="modal-close" onclick="closeModal('customerModal')">×</button>
            </div>
            <div class="modal-body" style="padding:0;">
                <!-- Customer Header -->
                <div class="customer-360-header" id="customer360Header">
                    <div class="customer-360-avatar" id="c360Avatar">?</div>
                    <div class="customer-360-info">
                        <h2 id="c360Name">Customer Name</h2>
                        <div class="customer-360-meta">
                            <span><i class="fas fa-envelope"></i> <span id="c360Email">email@example.com</span></span>
                            <span><i class="fas fa-phone"></i> <span id="c360Phone">+123456789</span></span>
                            <span><i class="fas fa-map-marker-alt"></i> <span id="c360Location">Location</span></span>
                        </div>
                    </div>
                    <div class="customer-360-stats">
                        <div class="c360-stat">
                            <div class="c360-stat-value" id="c360TotalSpent">€0</div>
                            <div class="c360-stat-label">Total Spent</div>
                        </div>
                        <div class="c360-stat">
                            <div class="c360-stat-value" id="c360Orders">0</div>
                            <div class="c360-stat-label">Orders</div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="customer-360-tabs">
                    <button class="c360-tab active" data-c360tab="timeline">📋 Timeline</button>
                    <button class="c360-tab" data-c360tab="orders">📦 Orders</button>
                    <button class="c360-tab" data-c360tab="carts">🛒 Carts</button>
                    <button class="c360-tab" data-c360tab="sms">📱 SMS History</button>
                    <button class="c360-tab" data-c360tab="notes">📝 Notes</button>
                </div>

                <!-- Tab Content -->
                <div class="customer-360-content" id="c360Content">
                    <!-- Content loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- SMS Automation Content -->
    <div id="smsAutomationContent" style="display:none;">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title-large"><i class="fas fa-robot"></i> SMS Automation</h1>
            <div class="page-header-actions">
                <button class="action-btn-header" onclick="runSmsAutomations()" id="runAutomationsBtn" title="Preveri pogoje in dodaj SMS-e v vrsto">
                    <i class="fas fa-play"></i> Zaženi preverjanje
                </button>
                <button class="action-btn-header primary" onclick="showAddAutomationModal()">
                    <i class="fas fa-plus"></i> Nova avtomatizacija
                </button>
                <button class="action-btn-header" onclick="loadSmsAutomations()">
                    <i class="fas fa-sync-alt"></i> Osveži
                </button>
            </div>
        </div>

        <div class="content">
            <!-- Info Banner -->
            <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid var(--accent-blue); border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-info-circle" style="color: var(--accent-blue); font-size: 20px;"></i>
                <div style="font-size: 13px; color: var(--text-secondary);">
                    <strong style="color: var(--text-primary);">Kako deluje:</strong>
                    Avtomatizacija preveri pogoje vsakih 30 min in dodaja SMS-e v čakalno vrsto.
                    <span style="color: var(--text-muted);">•</span>
                    Pošiljanje vedno sproži uporabnik <strong>ročno</strong> iz SMS Dashboard strani.
                    <span id="lastAutomationRun" style="margin-left: 12px; color: var(--accent-green); font-weight: 500;"></span>
                </div>
            </div>
            <!-- Filters Bar -->
            <div class="filters-bar" style="margin-bottom: 12px;">
                <select class="filter-select" id="automationCountryFilter" onchange="filterAutomations()">
                    <option value="">🌍 Vse države</option>
                    <option value="hr">🇭🇷 Hrvaška</option>
                    <option value="cz">🇨🇿 Češka</option>
                    <option value="pl">🇵🇱 Poljska</option>
                    <option value="sk">🇸🇰 Slovaška</option>
                    <option value="hu">🇭🇺 Madžarska</option>
                    <option value="gr">🇬🇷 Grčija</option>
                    <option value="it">🇮🇹 Italija</option>
                </select>
                <select class="filter-select" id="automationTypeFilter" onchange="filterAutomations()">
                    <option value="">📋 Vsi tipi</option>
                    <option value="abandoned_cart">🛒 Zapuščena košarica</option>
                </select>
                <select class="filter-select" id="automationStatusFilter" onchange="filterAutomations()">
                    <option value="">⚡ Vsi statusi</option>
                    <option value="active">✅ Aktivne</option>
                    <option value="paused">⏸️ Zaustavljene</option>
                </select>
            </div>

            <!-- Automations List -->
            <div class="table-card">
                <div id="automationsTableContainer">
                    <table class="data-table" id="automationsTable">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ON/OFF</th>
                                <th>Ime</th>
                                <th>Trgovina</th>
                                <th>Tip</th>
                                <th>Predloga</th>
                                <th title="Zamik pošiljanja / Max starost">Zamik / Max</th>
                                <th>V vrsti</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody id="automationsTableBody">
                            <tr><td colspan="8" style="text-align:center; padding: 40px; color: var(--text-muted);">Nalagam avtomatizacije...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Automation Modal -->
    <div id="automationModalBg" class="modal-bg" style="position:fixed;inset:0;background:rgba(0,0,0,0.6);display:none;align-items:center;justify-content:center;z-index:200;padding:20px;" onclick="if(event.target===this)closeAutomationModal()">
        <div class="modal" style="max-width:500px;background:var(--card-bg);border-radius:var(--radius-xl);box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;">
            <div class="modal-header">
                <h3 class="modal-title" id="automationModalTitle">
                    <i class="fas fa-robot" style="margin-right: 8px; color: var(--primary);"></i>Nova SMS avtomatizacija
                </h3>
                <button class="modal-close" onclick="closeAutomationModal()">&times;</button>
            </div>

            <div class="modal-body">
                <form id="automationForm">
                    <input type="hidden" id="automationId" value="">

                    <div class="form-group">
                        <label class="form-label">Ime avtomatizacije</label>
                        <input type="text" id="automationName" class="form-input" placeholder="npr. HR Zapuščena košarica 2h" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Trgovina</label>
                            <select id="automationStore" class="form-select" required onchange="if(this.value) loadTemplatesForStore(this.value)">
                                <option value="">Izberi...</option>
                                <option value="hr">🇭🇷 Hrvaška</option>
                                <option value="cz">🇨🇿 Češka</option>
                                <option value="pl">🇵🇱 Poljska</option>
                                <option value="sk">🇸🇰 Slovaška</option>
                                <option value="hu">🇭🇺 Madžarska</option>
                                <option value="gr">🇬🇷 Grčija</option>
                                <option value="it">🇮🇹 Italija</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tip</label>
                            <select id="automationType" class="form-select" required>
                                <option value="abandoned_cart">🛒 Zapuščena košarica</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">SMS predloga</label>
                        <select id="automationTemplate" class="form-select" required onchange="showTemplatePreviewInModal()">
                            <option value="">Najprej izberi trgovino</option>
                        </select>
                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 6px; display: block;">
                            <i class="fas fa-info-circle"></i> Predloge se naložijo glede na izbrano trgovino
                        </small>
                        <!-- Template Preview in Modal -->
                        <div id="modalTemplatePreview" class="template-preview" style="display: none;">
                            <div class="template-preview-label">📄 Predogled besedila:</div>
                            <div id="modalTemplateText"></div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label class="form-label">Zamik pošiljanja</label>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="number" id="automationDelay" class="form-input" style="width: 70px; text-align: center;" min="1" max="72" value="2" required>
                                <span style="color: var(--text-secondary); font-size: 13px;">ur</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Max starost košarice</label>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="number" id="automationMaxDays" class="form-input" style="width: 70px; text-align: center;" min="1" max="30" value="7">
                                <span style="color: var(--text-secondary); font-size: 13px;">dni</span>
                            </div>
                            <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">
                                Košarice starejše od tega se ne obdelujejo
                            </small>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0; padding: 16px; background: var(--content-bg); border-radius: var(--radius-md);">
                        <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin: 0;">
                            <input type="checkbox" id="automationEnabled" checked style="width: 18px; height: 18px; accent-color: var(--primary);">
                            <div>
                                <div style="font-weight: 500; font-size: 14px;">Avtomatizacija aktivna</div>
                                <div style="font-size: 12px; color: var(--text-muted);">SMS se bo pošiljal avtomatsko</div>
                            </div>
                        </label>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAutomationModal()">Prekliči</button>
                <button type="button" class="btn btn-primary" onclick="saveAutomation()">
                    <i class="fas fa-check"></i> Shrani
                </button>
            </div>
        </div>
    </div>

    <!-- Add/Edit Template Modal -->
    <div id="templateModalBg" class="modal-bg" style="position:fixed;inset:0;background:rgba(0,0,0,0.6);display:none;align-items:center;justify-content:center;z-index:200;padding:20px;overflow-y:auto;" onclick="if(event.target===this)closeTemplateModal()">
        <div class="modal" style="max-width:800px;width:100%;background:var(--card-bg);border-radius:var(--radius-xl);box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;max-height:90vh;overflow-y:auto;">
            <div class="modal-header">
                <h3 class="modal-title" id="templateModalTitle">
                    <i class="fas fa-edit" style="margin-right: 8px; color: var(--primary);"></i>Nova SMS predloga
                </h3>
                <button class="modal-close" onclick="closeTemplateModal()">&times;</button>
            </div>

            <div class="modal-body">
                <form id="templateForm">
                    <input type="hidden" id="templateId" value="">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">ID predloge</label>
                            <input type="text" id="templateKey" class="form-input" placeholder="npr. abandoned_cart" required>
                            <small style="color: var(--text-muted); font-size: 11px;">Unikatni ID (brez presledkov)</small>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-label">Kategorija</label>
                            <select id="templateCategory" class="form-select" required>
                                <option value="abandoned">🛒 Abandoned</option>
                                <option value="winback">💙 Winback</option>
                                <option value="custom">✏️ Custom</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ime predloge</label>
                        <input type="text" id="templateName" class="form-input" placeholder="npr. Opuščena košarica" required>
                    </div>

                    <!-- Translations for all countries -->
                    <div class="form-group">
                        <label class="form-label">Prevodi sporočil</label>
                        <p style="color: var(--text-muted); font-size: 12px; margin-bottom: 12px;">
                            Variabele: <code>{ime}</code>, <code>{produkt}</code>, <code>{cena}</code>, <code>{link}</code>
                        </p>

                        <div id="templateTranslations" style="display:flex;flex-direction:column;gap:12px;">
                            <!-- HR -->
                            <div style="background:var(--content-bg);padding:12px;border-radius:8px;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                    <span>🇭🇷</span>
                                    <strong style="font-size:13px;">Hrvaška (HR)</strong>
                                </div>
                                <textarea id="templateMsg_hr" class="form-input" rows="2" placeholder="Sporočilo v hrvaščini..." style="resize:vertical;"></textarea>
                            </div>
                            <!-- CZ -->
                            <div style="background:var(--content-bg);padding:12px;border-radius:8px;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                    <span>🇨🇿</span>
                                    <strong style="font-size:13px;">Češka (CZ)</strong>
                                </div>
                                <textarea id="templateMsg_cz" class="form-input" rows="2" placeholder="Sporočilo v češčini..." style="resize:vertical;"></textarea>
                            </div>
                            <!-- PL -->
                            <div style="background:var(--content-bg);padding:12px;border-radius:8px;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                    <span>🇵🇱</span>
                                    <strong style="font-size:13px;">Poljska (PL)</strong>
                                </div>
                                <textarea id="templateMsg_pl" class="form-input" rows="2" placeholder="Sporočilo v poljščini..." style="resize:vertical;"></textarea>
                            </div>
                            <!-- SK -->
                            <div style="background:var(--content-bg);padding:12px;border-radius:8px;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                    <span>🇸🇰</span>
                                    <strong style="font-size:13px;">Slovaška (SK)</strong>
                                </div>
                                <textarea id="templateMsg_sk" class="form-input" rows="2" placeholder="Sporočilo v slovaščini..." style="resize:vertical;"></textarea>
                            </div>
                            <!-- HU -->
                            <div style="background:var(--content-bg);padding:12px;border-radius:8px;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                    <span>🇭🇺</span>
                                    <strong style="font-size:13px;">Madžarska (HU)</strong>
                                </div>
                                <textarea id="templateMsg_hu" class="form-input" rows="2" placeholder="Sporočilo v madžarščini..." style="resize:vertical;"></textarea>
                            </div>
                            <!-- GR -->
                            <div style="background:var(--content-bg);padding:12px;border-radius:8px;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                    <span>🇬🇷</span>
                                    <strong style="font-size:13px;">Grčija (GR)</strong>
                                </div>
                                <textarea id="templateMsg_gr" class="form-input" rows="2" placeholder="Sporočilo v grščini..." style="resize:vertical;"></textarea>
                            </div>
                            <!-- IT -->
                            <div style="background:var(--content-bg);padding:12px;border-radius:8px;">
                                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                                    <span>🇮🇹</span>
                                    <strong style="font-size:13px;">Italija (IT)</strong>
                                </div>
                                <textarea id="templateMsg_it" class="form-input" rows="2" placeholder="Sporočilo v italijanščini..." style="resize:vertical;"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeTemplateModal()">Prekliči</button>
                <button type="button" class="btn btn-primary" onclick="saveTemplate()">
                    <i class="fas fa-check"></i> Shrani
                </button>
            </div>
        </div>
    </div>

    <!-- SMS Dashboard Content (shown when tab selected) -->
    <div id="smsDashboardContent" style="display:none;">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title-large"><i class="fas fa-comment-sms"></i> SMS Dashboard</h1>
        </div>

        <div class="content">
        <!-- Test SMS from Template Section -->
        <div class="table-card" style="margin-bottom:20px;border:2px solid var(--accent-purple);border-color:#a855f7;">
            <div style="padding:20px;border-bottom:1px solid var(--card-border);background:rgba(168,85,247,0.1);">
                <h3 style="margin:0 0 4px 0;">🧪 Test SMS iz predloge</h3>
                <p style="margin:0;color:var(--text-muted);font-size:13px;">Pošlji testni SMS na svoj telefon - vidiš točno kar bo stranka prejela</p>
            </div>
            <div style="padding:20px;">
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Tvoja telefonska št.</label>
                        <input type="tel" class="form-input" id="testSmsPhone" placeholder="+386 40 xxx xxx" value="">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Država telefona</label>
                        <select class="form-select" id="testSmsPhoneCountry">
                            <option value="si">🇸🇮 SI (+386)</option>
                            <option value="hr">🇭🇷 HR (+385)</option>
                            <option value="cz">🇨🇿 CZ (+420)</option>
                            <option value="pl">🇵🇱 PL (+48)</option>
                            <option value="sk">🇸🇰 SK (+421)</option>
                            <option value="hu">🇭🇺 HU (+36)</option>
                            <option value="gr">🇬🇷 GR (+30)</option>
                            <option value="it">🇮🇹 IT (+39)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Predloga</label>
                        <select class="form-select" id="testSmsTemplate" onchange="loadTestTemplatePreview()">
                            <option value="">-- Izberi predlogo --</option>
                            <option value="abandoned_cart_1">🛒 Zapuščena košarica 1 (opomnik)</option>
                            <option value="abandoned_cart_2">🎁 Zapuščena košarica 2 (20% popust)</option>
                            <option value="winback_1">💙 Povratek kupca 1 (generičen)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Jezik sporočila</label>
                        <select class="form-select" id="testSmsCountry" onchange="loadTestTemplatePreview()">
                            <option value="hr">🇭🇷 HR - Hrvaška</option>
                            <option value="cz">🇨🇿 CZ - Češka</option>
                            <option value="pl">🇵🇱 PL - Poljska</option>
                            <option value="sk">🇸🇰 SK - Slovaška</option>
                            <option value="hu">🇭🇺 HU - Madžarska</option>
                            <option value="gr">🇬🇷 GR - Grčija</option>
                            <option value="it">🇮🇹 IT - Italija</option>
                        </select>
                    </div>
                </div>

                <!-- Test Data Input -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;padding:16px;background:var(--bg-secondary);border-radius:8px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Ime stranke (za test)</label>
                        <input type="text" class="form-input" id="testSmsName" placeholder="npr. Marko" value="Marko" oninput="loadTestTemplatePreview()">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Ime produkta (za test)</label>
                        <input type="text" class="form-input" id="testSmsProduct" placeholder="npr. Noriks čarape" value="Noriks čarape" oninput="loadTestTemplatePreview()">
                    </div>
                </div>

                <!-- Preview -->
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">📋 Predogled SMS</label>
                    <div id="testSmsPreview" style="padding:16px;background:var(--bg-secondary);border-radius:8px;border:1px solid var(--card-border);font-family:monospace;font-size:14px;min-height:60px;color:var(--text-primary);">
                        <span style="color:var(--text-muted);font-style:italic;">Izberi predlogo za predogled...</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-top:4px;">
                        <span class="char-count" id="testSmsCharCount">0 / 160 znakov</span>
                    </div>
                </div>

                <div style="display:flex;gap:12px;align-items:center;">
                    <button class="btn" onclick="sendTestSms()" style="background:#a855f7;color:white;">
                        <i class="fas fa-paper-plane"></i> Pošlji testni SMS
                    </button>
                    <span id="testSmsStatus" style="font-size:13px;"></span>
                </div>
            </div>
        </div>

        <!-- Manual SMS Send Section -->
        <div class="table-card" style="margin-bottom:20px;border:2px solid var(--accent-green);">
            <div style="padding:20px;border-bottom:1px solid var(--card-border);background:rgba(34,197,94,0.1);">
                <h3 style="margin:0 0 4px 0;">📱 Ročno pošiljanje SMS</h3>
                <p style="margin:0;color:var(--text-muted);font-size:13px;">Pošlji poljuben SMS na poljubno številko</p>
            </div>
            <div style="padding:20px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Telefonska številka</label>
                        <input type="tel" class="form-input" id="manualSmsPhone" placeholder="+38598xxxxxxx ali 098xxxxxxx">
                        <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">
                            Podprti formati: +385xxx, 385xxx, 098xxx
                        </div>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Država (za formatiranje)</label>
                        <select class="form-select" id="manualSmsCountry">
                            <option value="si">🇸🇮 Slovenija (+386)</option>
                            <option value="hr">🇭🇷 Hrvaška (+385)</option>
                            <option value="cz">🇨🇿 Češka (+420)</option>
                            <option value="pl">🇵🇱 Poljska (+48)</option>
                            <option value="sk">🇸🇰 Slovaška (+421)</option>
                            <option value="hu">🇭🇺 Madžarska (+36)</option>
                            <option value="gr">🇬🇷 Grčija (+30)</option>
                            <option value="it">🇮🇹 Italija (+39)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label">Sporočilo</label>
                    <textarea class="form-textarea" id="manualSmsMessage" placeholder="Vpiši sporočilo..." style="min-height:80px;" oninput="updateManualSmsCharCount()"></textarea>
                    <div style="display:flex;justify-content:space-between;margin-top:4px;">
                        <span class="char-count" id="manualSmsCharCount">0 / 160 znakov</span>
                        <span style="font-size:11px;color:var(--text-muted);">1 SMS = 160 znakov</span>
                    </div>
                </div>
                <div style="display:flex;gap:12px;align-items:center;">
                    <button class="btn btn-save" onclick="sendManualSms()" style="background:var(--accent-green);">
                        <i class="fas fa-paper-plane"></i> Pošlji SMS
                    </button>
                    <span id="manualSmsStatus" style="font-size:13px;"></span>
                </div>
            </div>
        </div>

        <!-- SMS Queue Section (from API) -->
        <div class="table-card" style="margin-bottom:20px;">
            <div style="padding:20px;border-bottom:1px solid var(--card-border);">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <h3 style="margin:0 0 4px 0;">📤 Čakalna vrsta za pošiljanje</h3>
                        <p style="margin:0;color:var(--text-muted);font-size:13px;">SMS-i ki čakajo na pošiljanje</p>
                    </div>
                    <button class="btn" style="background:var(--card-border);" onclick="loadSmsDashboardQueue()">
                        <i class="fas fa-sync"></i> Osveži
                    </button>
                </div>
            </div>
            <div id="smsDashboardQueue" style="padding:20px;">
                <div class="loading"><div class="spinner"></div>Loading...</div>
            </div>
        </div>

        <!-- SMS History Section (from localStorage) -->
        <div class="table-card">
            <div style="padding:20px;border-bottom:1px solid var(--card-border);">
                <h3 style="margin:0 0 4px 0;">📋 Zgodovina pošiljanja</h3>
                <p style="margin:0;color:var(--text-muted);font-size:13px;">Poslani SMS-i (lokalna zgodovina)</p>
            </div>

            <!-- Modern Filter Bar -->
            <div style="padding:16px 20px;border-bottom:1px solid var(--card-border);background:var(--bg-secondary);">
                <!-- Date Range Pills -->
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:12px;">
                    <span style="font-size:12px;color:var(--text-muted);padding:6px 0;margin-right:4px;">📅 Obdobje:</span>
                    <button class="filter-pill active" data-range="today" onclick="setHistoryDateRange('today', this)">Danes</button>
                    <button class="filter-pill" data-range="yesterday" onclick="setHistoryDateRange('yesterday', this)">Včeraj</button>
                    <button class="filter-pill" data-range="week" onclick="setHistoryDateRange('week', this)">7 dni</button>
                    <button class="filter-pill" data-range="month" onclick="setHistoryDateRange('month', this)">30 dni</button>
                    <button class="filter-pill" data-range="all" onclick="setHistoryDateRange('all', this)">Vse</button>
                    <div style="border-left:1px solid var(--card-border);margin:0 8px;"></div>
                    <input type="date" class="form-input" id="smsDateFrom" style="width:130px;padding:6px 10px;font-size:12px;" onchange="setHistoryDateRange('custom', null)">
                    <span style="color:var(--text-muted);padding:6px 4px;">→</span>
                    <input type="date" class="form-input" id="smsDateTo" style="width:130px;padding:6px 10px;font-size:12px;" onchange="setHistoryDateRange('custom', null)">
                </div>

                <!-- Country + Status Pills -->
                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                    <span style="font-size:12px;color:var(--text-muted);padding:6px 0;margin-right:4px;">🌍 Država:</span>
                    <button class="filter-pill active" data-country="" onclick="setHistoryCountry('', this)">Vse</button>
                    <button class="filter-pill" data-country="hr" onclick="setHistoryCountry('hr', this)">🇭🇷 HR</button>
                    <button class="filter-pill" data-country="cz" onclick="setHistoryCountry('cz', this)">🇨🇿 CZ</button>
                    <button class="filter-pill" data-country="pl" onclick="setHistoryCountry('pl', this)">🇵🇱 PL</button>
                    <button class="filter-pill" data-country="sk" onclick="setHistoryCountry('sk', this)">🇸🇰 SK</button>
                    <button class="filter-pill" data-country="hu" onclick="setHistoryCountry('hu', this)">🇭🇺 HU</button>
                    <button class="filter-pill" data-country="gr" onclick="setHistoryCountry('gr', this)">🇬🇷 GR</button>
                    <button class="filter-pill" data-country="it" onclick="setHistoryCountry('it', this)">🇮🇹 IT</button>

                    <div style="border-left:1px solid var(--card-border);margin:0 12px;height:24px;"></div>

                    <span style="font-size:12px;color:var(--text-muted);margin-right:4px;">📊 Status:</span>
                    <button class="filter-pill active" data-status="" onclick="setHistoryStatus('', this)">Vsi</button>
                    <button class="filter-pill" data-status="sent" onclick="setHistoryStatus('sent', this)">✅ Poslano</button>
                    <button class="filter-pill" data-status="queued" onclick="setHistoryStatus('queued', this)">⏳ Čaka</button>
                    <button class="filter-pill" data-status="failed" onclick="setHistoryStatus('failed', this)">❌ Neuspešno</button>

                    <div style="margin-left:auto;">
                        <button class="btn" style="padding:6px 12px;font-size:12px;" onclick="exportSmsCsv()">
                            <i class="fas fa-download"></i> CSV
                        </button>
                    </div>
                </div>
            </div>

            <div id="smsTableContainer" style="padding:20px;">
                <div class="empty"><i class="fas fa-comment-sms"></i><p>Ni poslanih SMS sporočil</p></div>
            </div>
        </div>
        </div><!-- .content -->
    </div>

    <!-- SMS Settings (shown when tab selected) -->
    <div id="smsSettingsContent" style="display:none;">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title-large"><i class="fas fa-cog"></i> SMS Settings</h1>
        </div>

        <div class="content" style="max-width:1000px;">
            <!-- SMS Provider Settings -->
            <div class="table-card" style="padding:12px 16px;margin-bottom:12px;">
                <h3 style="margin-bottom:4px;font-size:14px;">📱 MetaKocka SMS Provider Settings</h3>
                <p style="color:var(--text-muted);margin-bottom:12px;font-size:12px;">
                    <i class="fas fa-info-circle"></i> SMS ID-ji so konfigurirani v kodi in niso nastavljivi preko vmesnika.
                </p>

                <div class="table-wrapper">
                    <table class="data-table" id="smsProviderTable">
                        <thead>
                            <tr>
                                <th>Država</th>
                                <th>Eshop Sync ID</th>
                                <th>Store</th>
                                <th>Akcije</th>
                            </tr>
                        </thead>
                        <tbody id="smsProviderRows">
                            <!-- Rows will be rendered by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SMS Templates Management -->
            <div class="table-card" style="padding:16px;margin-bottom:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                    <div>
                        <h3 style="margin:0;font-size:14px;">📝 SMS Predloge</h3>
                        <p style="color:var(--text-muted);margin:4px 0 0 0;font-size:12px;">
                            Upravljaj SMS predloge za vse države. Variabele: <code>{ime}</code>, <code>{produkt}</code>, <code>{cena}</code>, <code>{link}</code>
                        </p>
                    </div>
                    <button class="btn btn-primary" onclick="showAddTemplateModal()" style="padding:8px 16px;">
                        <i class="fas fa-plus"></i> Nova predloga
                    </button>
                </div>

                <!-- Category Filter -->
                <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
                    <button class="content-tab active" data-category="all" onclick="filterTemplatesByCategory('all', this)">
                        📋 Vse
                    </button>
                    <button class="content-tab" data-category="abandoned" onclick="filterTemplatesByCategory('abandoned', this)">
                        🛒 Abandoned
                    </button>
                    <button class="content-tab" data-category="winback" onclick="filterTemplatesByCategory('winback', this)">
                        💙 Winback
                    </button>
                    <button class="content-tab" data-category="custom" onclick="filterTemplatesByCategory('custom', this)">
                        ✏️ Custom
                    </button>
                </div>

                <!-- Templates List -->
                <div id="templatesListContainer">
                    <div style="text-align:center;padding:40px;color:var(--text-muted);">
                        <i class="fas fa-spinner fa-spin"></i> Nalagam predloge...
                    </div>
                </div>
            </div>

            <!-- Sample Variables for Preview -->
            <div class="table-card" style="padding:12px 16px;">
                <h3 style="margin-bottom:8px;font-size:14px;">🔍 Testni podatki za predogled</h3>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:120px;">
                        <label style="font-size:11px;color:var(--text-muted);">Ime</label>
                        <input type="text" class="form-input" id="sampleName" value="Marko" style="padding:8px;">
                    </div>
                    <div style="flex:1;min-width:120px;">
                        <label style="font-size:11px;color:var(--text-muted);">Produkt</label>
                        <input type="text" class="form-input" id="sampleProduct" value="Noriks majica" style="padding:8px;">
                    </div>
                    <div style="flex:1;min-width:80px;">
                        <label style="font-size:11px;color:var(--text-muted);">Cena</label>
                        <input type="text" class="form-input" id="samplePrice" value="€29.99" style="padding:8px;">
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Options -->
    <div id="buyersSettingsContent" style="display:none;">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title-large"><i class="fas fa-sliders-h"></i> Options</h1>
        </div>

        <div class="content" style="max-width:800px;">
            <!-- Filter Settings Card -->
            <div class="table-card" style="padding:16px 20px;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;">
                    <div>
                        <h3 style="margin:0 0 6px 0;font-size:15px;display:flex;align-items:center;gap:8px;">
                            <i class="fas fa-filter" style="color:var(--accent-blue);"></i> Filter nastavitve
                        </h3>
                        <p style="color:var(--text-muted);font-size:12px;margin:0;">
                            Nastavi koliko dni mora miniti od prvega nakupa, preden se kupec prikaže v tabeli "Enkratni kupci".
                        </p>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:16px;">
                    <label class="form-label" style="font-size:13px;">Prikaži naročila po X dneh od prvega nakupa</label>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <input type="number" class="form-input" id="buyersMinDays" min="0" max="365" value="10" style="width:100px;text-align:center;font-size:18px;font-weight:600;">
                        <span style="color:var(--text-muted);">dni</span>
                    </div>
                    <small style="color:var(--text-muted);margin-top:6px;display:block;font-size:11px;">
                        Privzeto: 10 dni. Kupci z enim samim nakupom se prikažejo šele po tem obdobju.
                    </small>
                </div>

                <div style="display:flex;gap:12px;align-items:center;padding-top:12px;border-top:1px solid var(--card-border);">
                    <button class="btn btn-save" onclick="saveBuyersSettings()" style="padding:10px 20px;">
                        <i class="fas fa-save"></i> Shrani nastavitve
                    </button>
                    <span id="buyersSettingsStatus" style="font-size:12px;color:var(--text-muted);"></span>
                </div>
            </div>

            <!-- Info Box -->
            <div style="background:rgba(59,130,246,0.1);border:1px solid var(--accent-blue);border-radius:8px;padding:14px 16px;display:flex;align-items:flex-start;gap:12px;">
                <i class="fas fa-info-circle" style="color:var(--accent-blue);font-size:18px;margin-top:2px;"></i>
                <div style="font-size:12px;color:var(--text-secondary);line-height:1.5;">
                    <strong style="color:var(--text-primary);">Kako deluje:</strong><br>
                    Če nastaviš 10 dni, bodo kupci z enim nakupom prikazani šele 10 dni po njihovem nakupu.
                    To omogoča, da kličeš kupce, ki so imeli dovolj časa za razmislek o ponovnem nakupu.
                </div>
            </div>
        </div>
    </div>

    <!-- Agents Management Content (Admin Only) -->
    <div id="agentsContent" style="display:none;">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title-large"><i class="fas fa-users-cog"></i> Agents</h1>
        </div>

        <div class="content" style="max-width:1000px;">
            <div class="table-card" style="padding:12px 16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <div>
                        <h3 style="margin-bottom:4px;font-size:14px;">👥 Agent Management</h3>
                        <p style="color:var(--text-muted);font-size:12px;margin:0;">Upravljaj z agenti in njihovimi dovoljenji za države.</p>
                    </div>
                    <button class="btn btn-save" onclick="openAgentModal()">
                        <i class="fas fa-plus"></i> Add Agent
                    </button>
                </div>

                <div class="table-wrapper">
                    <table class="data-table" id="agentsTable">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Role</th>
                                <th>Countries</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="agentsTableBody">
                            <!-- Rows rendered by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-ups Content -->
    <div id="followupsContent" style="display:none;">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title-large"><i class="fas fa-phone-volume"></i> Follow-ups</h1>
        </div>

        <div class="table-card" style="padding:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
                <div>
                    <h3 style="margin-bottom:8px;">📞 My Follow-ups</h3>
                    <p style="color:var(--text-muted);">Dogovorjeni callbacki za danes in prihodnje dni.</p>
                </div>
                <div style="display:flex;gap:8px;">
                    <button class="btn admin-only-btn" style="background:var(--card-border);" onclick="renderFollowups(true)">
                        <i class="fas fa-users"></i> Vsi agenti
                    </button>
                    <button class="btn btn-save" onclick="renderFollowups()">
                        <i class="fas fa-sync"></i> Osveži
                    </button>
                </div>
            </div>

            <div id="followupsContainer">
                <div class="loading"><div class="spinner"></div>Loading...</div>
            </div>
        </div>
    </div>

    <!-- Add Urgent Lead Modal -->
    <div class="modal-bg" id="addUrgentModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;z-index:200;padding:20px;">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title"><i class="fas fa-plus"></i> Dodaj nujni lead</div>
                <button class="modal-close" onclick="closeAddUrgentModal()">×</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Telefonska številka *</label>
                    <input type="tel" class="form-input" id="urgentPhone" placeholder="+386 40 xxx xxx">
                </div>
                <div class="form-group">
                    <label class="form-label">Navodilo / Razlog za klic *</label>
                    <textarea class="form-textarea" id="urgentNote" placeholder="Kaj je treba poklicati, posebna navodila..." style="min-height:100px;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeAddUrgentModal()">Prekliči</button>
                <button class="btn btn-save" onclick="saveUrgentLead()">
                    <i class="fas fa-plus"></i> Dodaj
                </button>
            </div>
        </div>
    </div>

    <!-- Notification Settings Modal -->
    <div id="notificationSettingsModal" class="modal" style="display:none;">
        <div class="modal-content" style="max-width:500px;">
            <div class="modal-header">
                <h3>🔔 Notification Settings</h3>
                <button class="modal-close" onclick="closeNotificationSettings()">×</button>
            </div>
            <div class="modal-body">
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <label style="display:flex;align-items:center;gap:12px;cursor:pointer;">
                        <input type="checkbox" id="notifDesktopToggle" onchange="saveNotificationSettings()">
                        <span>🖥️ Desktop obvestila</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:12px;cursor:pointer;">
                        <input type="checkbox" id="notifSoundToggle" onchange="saveNotificationSettings()">
                        <span>🔊 Zvočna opozorila</span>
                    </label>
                    <div style="margin-top:8px;">
                        <label style="display:block;margin-bottom:8px;color:var(--text-muted);font-size:13px;">Interval preverjanja:</label>
                        <select id="notifPollingInterval" class="filter-select" style="width:100%;" onchange="saveNotificationSettings()">
                            <option value="30000">30 sekund</option>
                            <option value="60000">1 minuta</option>
                            <option value="300000">5 minut</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeNotificationSettings()">Zapri</button>
                <button class="btn btn-save" onclick="testNotification()">🔔 Test</button>
            </div>
        </div>
    </div>

    <!-- Call Log Modal -->
    <div class="modal-bg" id="callLogModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">📞 Log Call</div>
                <button class="modal-close" onclick="closeModal('callLogModal')">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="callLogCustomerId">
                <input type="hidden" id="callLogStoreCode">

                <div class="form-group">
                    <label class="form-label">Call Status *</label>
                    <div class="call-status-grid" id="callStatusGrid">
                        <!-- Status options rendered by JS -->
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea class="form-textarea" id="callLogNotes" placeholder="Kaj ste se dogovorili, zakaj ni odgovoril, itd..." rows="3"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Call Duration (min)</label>
                        <input type="number" class="form-input" id="callLogDuration" placeholder="npr. 5" min="0" step="1">
                    </div>
                    <div class="form-group" id="callbackSection" style="display:none;">
                        <label class="form-label">Callback Date/Time *</label>
                        <input type="datetime-local" class="form-input" id="callLogCallback">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeModal('callLogModal')">Prekliči</button>
                <button class="btn btn-save" onclick="saveCallLog()">
                    <i class="fas fa-save"></i> Shrani klic
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Callback Modal (for inline status change) -->
    <div class="modal-bg" id="quickCallbackModal">
        <div class="modal" style="max-width:400px;">
            <div class="modal-header">
                <div class="modal-title">🔄 Schedule Callback</div>
                <button class="modal-close" onclick="cancelQuickCallback()">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="quickCallbackId">
                <input type="hidden" id="quickCallbackType">
                <p style="color:var(--text-muted);margin-bottom:16px;">Kdaj naj te spomnimo za klic nazaj?</p>
                <div class="form-group">
                    <label class="form-label">Datum in čas *</label>
                    <input type="datetime-local" class="form-input" id="quickCallbackDateTime">
                </div>
                <div class="form-group">
                    <label class="form-label">Opomba (opcijsko)</label>
                    <input type="text" class="form-input" id="quickCallbackNote" placeholder="npr. Pokliči po 14h">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="cancelQuickCallback()">Prekliči</button>
                <button class="btn btn-save" onclick="confirmQuickCallback()">
                    <i class="fas fa-bell"></i> Dodaj v Follow-ups
                </button>
            </div>
        </div>
    </div>

    <!-- Agent Modal -->
    <div class="modal-bg" id="agentModal">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" id="agentModalTitle">➕ Add Agent</div>
                <button class="modal-close" onclick="closeModal('agentModal')">×</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="agentEditId">

                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" class="form-input" id="agentUsername" placeholder="npr. marko">
                </div>

                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" class="form-input" id="agentPassword" placeholder="Geslo za prijavo">
                    <small style="color:var(--text-muted);font-size:12px;" id="passwordHint">Pusti prazno za ohranitev obstoječega (pri urejanju)</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select class="form-select" id="agentRole">
                        <option value="agent">Agent - vidi samo dodeljene države</option>
                        <option value="admin">Admin - vidi vse, upravlja nastavitve</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Assigned Countries</label>
                    <div class="country-checkboxes" id="agentCountries">
                        <!-- Checkboxes rendered by JS -->
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <div class="toggle-row">
                        <span class="toggle-label">Active</span>
                        <div class="toggle" id="agentActiveToggle" onclick="toggleAgentActive()"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-cancel" onclick="closeModal('agentModal')">Prekliči</button>
                <button class="btn btn-save" onclick="saveAgent()">
                    <i class="fas fa-save"></i> Shrani
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container (Enhanced) -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Legacy Toast (fallback) -->
    <div class="toast" id="toast"></div>

    <script>
        // Auth
        const user = JSON.parse(localStorage.getItem('callcenter_user') || 'null');
        if (!user) window.location.href = 'login.php';

        const isAdmin = user.role === 'admin';
        const userCountries = user.countries || [];
        const hasAllCountries = userCountries.includes('all');

        document.getElementById('userName').textContent = user.username;
        document.getElementById('userRole').textContent = isAdmin ? 'Administrator' : 'Agent';
        document.getElementById('userAvatar').textContent = user.username[0].toUpperCase();

        // Show admin sections
        if (isAdmin) {
            document.body.classList.add('is-admin');
        }

        // ========== BULLETPROOF API UTILITIES ==========
        const API_CONFIG = {
            timeout: 15000,      // 15 second timeout
            retries: 3,          // Retry 3 times
            retryDelay: 1000,    // 1 second between retries
            backoffMultiplier: 2 // Exponential backoff
        };

        // Loading state management
        const loadingState = {
            active: new Set(),
            show(component) {
                this.active.add(component);
                this.updateGlobalLoader();
            },
            hide(component) {
                this.active.delete(component);
                this.updateGlobalLoader();
            },
            updateGlobalLoader() {
                const loader = document.getElementById('globalLoader');
                if (loader) {
                    loader.style.display = this.active.size > 0 ? 'flex' : 'none';
                }
            }
        };

        // Bulletproof fetch with retry, timeout, and error handling
        async function apiFetch(url, options = {}) {
            const {
                retries = API_CONFIG.retries,
                timeout = API_CONFIG.timeout,
                silent = false,
                component = 'api'
            } = options;

            let lastError;
            let delay = API_CONFIG.retryDelay;

            for (let attempt = 1; attempt <= retries; attempt++) {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), timeout);

                try {
                    console.log(`[API] ${component} - Attempt ${attempt}/${retries}: ${url}`);

                    const response = await fetch(url, {
                        ...options,
                        signal: controller.signal
                    });

                    clearTimeout(timeoutId);

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();

                    // Check for API-level errors
                    if (data && data.error) {
                        console.warn(`[API] ${component} - API error:`, data.error);
                        return { success: false, error: data.error, data: null };
                    }

                    console.log(`[API] ${component} - Success:`, Array.isArray(data) ? `${data.length} items` : 'object');
                    return { success: true, data, error: null };

                } catch (error) {
                    clearTimeout(timeoutId);
                    lastError = error;

                    const isTimeout = error.name === 'AbortError';
                    const errorMsg = isTimeout ? 'Timeout' : error.message;

                    console.warn(`[API] ${component} - Attempt ${attempt} failed: ${errorMsg}`);

                    if (attempt < retries) {
                        console.log(`[API] ${component} - Retrying in ${delay}ms...`);
                        await new Promise(r => setTimeout(r, delay));
                        delay *= API_CONFIG.backoffMultiplier;
                    }
                }
            }

            // All retries failed
            const errorMsg = lastError?.name === 'AbortError'
                ? 'Request timed out'
                : lastError?.message || 'Unknown error';

            console.error(`[API] ${component} - All ${retries} attempts failed:`, errorMsg);

            if (!silent) {
                showToast(`⚠️ ${component}: ${errorMsg}`, 'error');
            }

            return { success: false, error: errorMsg, data: null };
        }

        // Show loading skeleton in a container
        function showLoadingSkeleton(containerId, rows = 5) {
            const container = document.getElementById(containerId);
            if (!container) return;

            container.innerHTML = `
                <tr>
                    <td colspan="10" style="padding:40px;text-align:center;">
                        <div class="spinner" style="margin:0 auto 16px;"></div>
                        <div style="color:var(--text-muted);">Nalagam podatke...</div>
                    </td>
                </tr>
            `;
        }

        // Show error state in a container
        function showErrorState(containerId, message, retryFn = null) {
            const container = document.getElementById(containerId);
            if (!container) return;

            container.innerHTML = `
                <tr>
                    <td colspan="10" style="padding:40px;text-align:center;">
                        <i class="fas fa-exclamation-triangle" style="font-size:32px;color:var(--accent-red);margin-bottom:12px;display:block;"></i>
                        <div style="color:var(--accent-red);margin-bottom:12px;">${message}</div>
                        ${retryFn ? `<button class="btn btn-save" onclick="${retryFn}" style="margin-top:8px;">
                            <i class="fas fa-redo"></i> Poskusi znova
                        </button>` : ''}
                    </td>
                </tr>
            `;
        }

        // Show empty state in a container
        function showEmptyState(containerId, message, icon = 'fa-inbox') {
            const container = document.getElementById(containerId);
            if (!container) return;

            container.innerHTML = `
                <tr>
                    <td colspan="10" style="padding:40px;text-align:center;color:var(--text-muted);">
                        <i class="fas ${icon}" style="font-size:32px;margin-bottom:12px;opacity:0.5;display:block;"></i>
                        <div>${message}</div>
                    </td>
                </tr>
            `;
        }

        // ========== END BULLETPROOF UTILITIES ==========

        console.log('[Auth] User:', user);
        console.log('[Auth] isAdmin:', isAdmin);
        console.log('[Auth] userCountries:', userCountries);

        // Hide admin-only sections for non-admins
        if (!isAdmin) {
            console.log('[Auth] Hiding admin-only sections for non-admin');
            // Hide entire Messaging section
            const messagingSection = document.getElementById('messagingSection');
            if (messagingSection) messagingSection.style.display = 'none';
            // Hide Admin section
            const adminSection = document.getElementById('adminSection');
            if (adminSection) adminSection.style.display = 'none';
            // Hide Reports/Statistics section
            const reportsSection = document.getElementById('reportsSection');
            if (reportsSection) reportsSection.style.display = 'none';
            // Hide "Vsi agenti" button in Follow-ups
            document.querySelectorAll('.admin-only-btn').forEach(btn => btn.style.display = 'none');
        } else {
            console.log('[Auth] Admin user - showing all sections');
        }

        // State
        let stores = [];
        let carts = [];
        let pending = [];
        let buyers = [];
        let smsLog = [];
        let smsAutomation = [];
        let paketomatiData = [];
        let currentStore = 'all';
        let currentTab = 'leads'; // Default to Leads view
        let editId = null;
        let orderCartId = null;
        let orderCart = null;
        let orderItems = [];
        let freeShipping = false;
        let smsTarget = null;

        // Global loading status for progressive loading (buyers takes longest)
        const globalLoadingStatus = {
            carts: true,
            pending: true,
            buyers: true,
            buyersError: null  // Will store error message if buyers fail
        };

        // Pagination & Sorting State
        let currentPage = 1;
        let itemsPerPage = 25;
        let sortColumn = 'abandonedAt';
        let sortDirection = 'desc';

        const SHIPPING_COST = 5.00;

        // SMS Templates
        const smsTemplates = {
            reminder: {
                text: 'Pozdravljeni {ime}! Vaša košarica z {produkt} vas še vedno čaka. Dokončajte nakup: {link}'
            },
            discount: {
                text: 'Pozdravljeni {ime}! Samo za vas: 10% popust na {produkt}! Koda: SAVE10. Nakup: {link}'
            },
            lastchance: {
                text: '{ime}, zadnja priložnost! {produkt} bo kmalu razprodan. Ne zamudite: {link}'
            }
        };

        // ========== UNIFIED CONTENT VISIBILITY CONTROL ==========
        function hideAllContent() {
            // Hide all special content areas
            const specialAreas = [
                'smsAutomationContent', 'smsDashboardContent', 'smsSettingsContent',
                'buyersSettingsContent', 'agentsContent', 'followupsContent'
            ];
            specialAreas.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });

            // Also ensure main content area is in correct state
            const mainContent = document.querySelector('.content');
            if (mainContent) mainContent.style.display = 'none';
        }

        function showMainView() {
            const main = document.getElementById('main');
            if (main) main.style.display = 'block';

            const statsGrid = document.querySelector('.stats-grid');
            if (statsGrid) statsGrid.style.display = 'grid';

            const countryTabs = document.querySelector('.country-tabs');
            if (countryTabs) countryTabs.style.display = 'flex';

            const contentTabs = document.getElementById('contentTabs');
            if (contentTabs) contentTabs.style.display = 'flex';

            const content = document.querySelector('.content');
            if (content) content.style.display = 'block';
        }

        function showSpecialView(contentId) {
            // Hide main completely
            const main = document.getElementById('main');
            if (main) main.style.display = 'none';

            // Show specific content
            const el = document.getElementById(contentId);
            if (el) el.style.display = 'block';
        }

        // Sidebar toggle
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('show');
        });

        // Nav items
        document.querySelectorAll('.nav-item[data-tab]').forEach(item => {
            item.addEventListener('click', () => {
                currentTab = item.dataset.tab;
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                item.classList.add('active');

                // Close sidebar on mobile
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('show');

                // Data tabs that use content tabs
                const dataTabs = ['carts', 'pending', 'buyers', 'paketomati'];
                const isDataTab = dataTabs.includes(currentTab);
                const isLeadsTab = currentTab === 'leads';

                // Update page title
                const titles = {
                    leads: 'Leads',
                    carts: 'Abandoned Carts',
                    pending: 'Pending Orders',
                    buyers: 'Enkratni kupci',
                    paketomati: 'Paketomati',
                    'sms-automation': 'SMS Automation',
                    'sms-dashboard': 'SMS Dashboard',
                    'sms-settings': 'SMS Provider Settings',
                    'buyers-settings': 'Options',
                    'agents': 'Agent Management',
                    'followups': 'My Follow-ups'
                };
                document.getElementById('pageTitle').textContent = titles[currentTab] || currentTab;

                // FIRST: Hide ALL content areas to prevent overlap
                hideAllContent();

                // Content tabs element
                const contentTabs = document.getElementById('contentTabs');

                if (isDataTab || isLeadsTab) {
                    // Show main view with stats, country tabs, content tabs
                    showMainView();

                    // For "leads" tab, default to carts view
                    if (isLeadsTab) {
                        currentContentTab = 'carts';
                        currentTab = 'carts';
                    } else {
                        currentContentTab = currentTab;
                    }

                    // Sync content tab state
                    contentTabs.querySelectorAll('.content-tab').forEach(t => {
                        t.classList.toggle('active', t.dataset.content === currentContentTab);
                    });

                    // All tabs now render in main content area
                    document.querySelector('.content').style.display = 'block';
                    currentPage = 1;
                    renderTable();
                    setTimeout(addCustomerClickHandlers, 100);

                    // Update country tab counts to reflect current content type
                    updateCounts();
                } else {
                    // Show specific special content
                    if (currentTab === 'sms-automation') {
                        showSpecialView('smsAutomationContent');
                        loadSmsAutomations();
                    } else if (currentTab === 'sms-dashboard') {
                        showSpecialView('smsDashboardContent');
                        loadSmsDashboardQueue();
                        renderSmsTable();
                    } else if (currentTab === 'sms-settings') {
                        showSpecialView('smsSettingsContent');
                        loadSmsSettingsUI();
                        loadAllTemplates();
                    } else if (currentTab === 'buyers-settings') {
                        showSpecialView('buyersSettingsContent');
                        loadBuyersSettings();
                    } else if (currentTab === 'agents') {
                        showSpecialView('agentsContent');
                        loadAgents();
                    } else if (currentTab === 'followups') {
                        showSpecialView('followupsContent');
                        renderFollowups();
                    }
                }
            });
        });

        // Content tab state
        let currentContentTab = 'carts';

        // Setup content tabs
        function setupContentTabs() {
            const contentTabs = document.getElementById('contentTabs');
            if (!contentTabs) return;

            contentTabs.querySelectorAll('.content-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    const newTab = tab.dataset.content;

                    // Update active state
                    contentTabs.querySelectorAll('.content-tab').forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');

                    // Update current content tab
                    currentContentTab = newTab;
                    currentTab = newTab; // Sync with main tab state

                    // Update page title
                    const titles = {
                        carts: 'Abandoned Carts',
                        pending: 'Pending Orders',
                        buyers: 'Enkratni kupci',
                        paketomati: 'Paketomati',
                        urgent: 'Nujno'
                    };
                    document.getElementById('pageTitle').textContent = titles[newTab] || newTab;

                    // Update sidebar active state - keep "Leads" highlighted for content tabs
                    document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                    const leadsItem = document.querySelector('.nav-item[data-tab="leads"]');
                    if (leadsItem) leadsItem.classList.add('active');

                    // Hide ALL content areas first (prevents overlap)
                    hideAllContent();

                    // Show main view elements
                    showMainView();
                    
                    // All tabs render in main content area
                    document.querySelector('.content').style.display = 'block';
                    currentPage = 1;
                    
                    // Direct render for special tabs
                    if (newTab === 'urgent') {
                        renderUrgentTableInline();
                    } else if (newTab === 'paketomati') {
                        renderPaketomatiInline();
                    } else {
                        renderTable();
                    }

                    // Update country tab counts to reflect current content type
                    updateCounts();
                });
            });
        }

        // Update content tab counts
        function updateContentTabCounts() {
            // Filter by user's allowed countries
            const shouldFilter = !hasAllCountries && !isAdmin;
            let filteredCarts = shouldFilter ? carts.filter(c => userCountries.includes(c.storeCode)) : carts;
            let filteredPending = shouldFilter ? pending.filter(p => userCountries.includes(p.storeCode)) : pending;
            let filteredBuyers = shouldFilter ? buyers.filter(b => userCountries.includes(b.storeCode)) : buyers;

            // Filter out converted carts - they should not be counted
            filteredCarts = filteredCarts.filter(c => c.converted !== true);

            // Apply country filter if not "all"
            if (currentStore !== 'all') {
                filteredCarts = filteredCarts.filter(c => c.storeCode === currentStore);
                filteredPending = filteredPending.filter(p => p.storeCode === currentStore);
                filteredBuyers = filteredBuyers.filter(b => b.storeCode === currentStore);
            }

            document.getElementById('contentCount-carts').textContent = filteredCarts.length;
            document.getElementById('contentCount-pending').textContent = filteredPending.length;

            // Buyers: Show spinner if still loading, error icon if failed
            const buyersCountEl = document.getElementById('contentCount-buyers');
            if (globalLoadingStatus.buyers) {
                buyersCountEl.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:9px"></i>';
            } else if (globalLoadingStatus.buyersError) {
                buyersCountEl.innerHTML = '<i class="fas fa-exclamation-triangle" style="color:var(--accent-red);font-size:9px" title="' + globalLoadingStatus.buyersError + '"></i>';
            } else {
                buyersCountEl.textContent = filteredBuyers.length;
            }

            // Paketomati count (filtered by country like other tabs)
            let filteredPaketomati = paketomatiData ? paketomatiData.filter(p => p.status === 'not_called') : [];
            console.log('[Paketomati Count] Total not_called:', filteredPaketomati.length, 'currentStore:', currentStore);
            if (currentStore !== 'all') {
                const beforeFilter = filteredPaketomati.length;
                filteredPaketomati = filteredPaketomati.filter(p => p.storeCode === currentStore);
                console.log('[Paketomati Count] After country filter:', filteredPaketomati.length, 'storeCodes in data:', [...new Set(paketomatiData.map(p => p.storeCode))]);
            }
            document.getElementById('contentCount-paketomati').textContent = filteredPaketomati.length;
        }

        // Init - Bulletproof version
        async function init() {
            console.log('[Init] Starting application...');
            const startTime = Date.now();

            // Show global loading state
            loadingState.show('init');

            try {
                // 1. Load stores (critical - app won't work without it)
                console.log('[Init] Loading stores...');
                const storesResult = await apiFetch('api.php?action=stores', {
                    component: 'Stores',
                    retries: 3
                });

                if (!storesResult.success || !storesResult.data) {
                    throw new Error('Napaka pri nalaganju trgovin');
                }
                stores = storesResult.data;
                console.log('[Init] Stores loaded:', stores.length);

                // 2. Render UI components
                renderCountryTabs();
                setupContentTabs();

                // 3. Load all data in parallel with graceful degradation
                console.log('[Init] Loading all data in parallel...');
                const results = await Promise.allSettled([
                    loadAllDataBulletproof(),
                    loadSmsDataBulletproof(),
                    loadSmsTemplatesBulletproof(),
                    loadPaketomatiBulletproof()
                ]);

                // Log results
                const labels = ['MainData', 'SmsData', 'SmsTemplates', 'Paketomati'];
                results.forEach((result, i) => {
                    if (result.status === 'fulfilled') {
                        console.log(`[Init] ✓ ${labels[i]} loaded successfully`);
                    } else {
                        console.error(`[Init] ✗ ${labels[i]} failed:`, result.reason);
                    }
                });

                // Update counts after all data loaded
                updateContentTabCounts();

                // 4. Set default view - LEADS (shows carts/pending/buyers tabs)
                currentTab = 'leads';
                currentContentTab = 'carts';
                document.getElementById('pageTitle').textContent = 'Leads';

                // Hide ALL special content areas first (ensures clean state)
                ['smsAutomationContent', 'smsDashboardContent', 'smsSettingsContent', 
                 'buyersSettingsContent', 'agentsContent', 'followupsContent'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.style.display = 'none';
                });

                // Show the main content area
                document.getElementById('main').style.display = 'block';
                document.querySelector('.stats-grid').style.display = 'grid';
                document.querySelector('.country-tabs').style.display = 'flex';
                document.getElementById('contentTabs').style.display = 'flex';
                document.querySelector('.content').style.display = 'block';

                // Update sidebar - Leads is active by default
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                document.querySelector('.nav-item[data-tab="leads"]')?.classList.add('active');

                renderTable();

                const loadTime = Date.now() - startTime;
                console.log(`[Init] ✓ Application ready in ${loadTime}ms`);

                // Handle hash navigation (from sidebar links)
                if (window.location.hash) {
                    const hash = window.location.hash.substring(1); // Remove #
                    const navItem = document.querySelector(`.nav-item[data-tab="${hash}"]`);
                    if (navItem) {
                        navItem.click();
                    }
                }

                // Auto-refresh data every 5 minutes (300000ms)
                setInterval(async () => {
                    console.log('[AutoRefresh] Refreshing data...');
                    try {
                        await loadAllDataBulletproof();
                        console.log('[AutoRefresh] ✓ Data refreshed');
                    } catch (e) {
                        console.error('[AutoRefresh] Failed:', e);
                    }
                }, 5 * 60 * 1000);

            } catch (error) {
                console.error('[Init] Critical error:', error);
                showToast(`❌ Napaka pri zagonu: ${error.message}`, 'error');

                // Show error state in main content
                document.querySelector('.content').innerHTML = `
                    <div style="padding:60px;text-align:center;">
                        <i class="fas fa-exclamation-triangle" style="font-size:48px;color:var(--accent-red);margin-bottom:20px;display:block;"></i>
                        <h3 style="margin-bottom:12px;">Napaka pri nalaganju aplikacije</h3>
                        <p style="color:var(--text-muted);margin-bottom:20px;">${error.message}</p>
                        <button class="btn btn-save" onclick="location.reload()">
                            <i class="fas fa-redo"></i> Ponovno naloži
                        </button>
                    </div>
                `;
            } finally {
                loadingState.hide('init');
            }
        }

        function renderCountryTabs() {
            const container = document.getElementById('countryTabs');

            // Filter stores based on user's assigned countries
            const allowedStores = hasAllCountries
                ? stores
                : stores.filter(s => userCountries.includes(s.code));

            // If agent has only one country, hide tabs and set that country
            if (!hasAllCountries && allowedStores.length === 1) {
                container.style.display = 'none';
                currentStore = allowedStores[0].code;
                return;
            }

            // Show "All" tab only for admins or users with multiple countries
            const showAllTab = hasAllCountries || allowedStores.length > 1;

            container.innerHTML = (showAllTab ? `
                <button class="country-tab active" data-store="all">
                    <span class="flag">🌍</span> All
                </button>
            ` : '') + allowedStores.map((s, i) => `
                <button class="country-tab ${!showAllTab && i === 0 ? 'active' : ''}" data-store="${s.code}">
                    <span class="flag">${s.flag}</span> ${s.name}
                </button>
            `).join('');

            // Set initial store for non-admins
            if (!showAllTab && allowedStores.length > 0) {
                currentStore = allowedStores[0].code;
            }

            container.querySelectorAll('.country-tab').forEach(tab => {
                tab.addEventListener('click', async () => {
                    currentStore = tab.dataset.store;
                    console.log('[Country Click] Changed to:', currentStore, 'currentContentTab:', currentContentTab);
                    container.querySelectorAll('.country-tab').forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    updateStats();
                    updateContentTabCounts();

                    // Re-render based on current content tab
                    if (currentContentTab === 'paketomati') {
                        console.log('[Country Click] Calling renderPaketomatiInline');
                        await renderPaketomatiInline();
                    } else if (currentContentTab === 'urgent') {
                        await renderUrgentTableInline();
                    } else {
                        renderTable();
                    }
                });
            });

            // SMS country filter is now using pills, no need to populate select
        }

        // Bulletproof data loading with PROGRESSIVE loading for better UX
        async function loadAllDataBulletproof() {
            console.log('[Data] Loading main data with progressive loading...');

            // Reset global loading status
            globalLoadingStatus.carts = true;
            globalLoadingStatus.pending = true;
            globalLoadingStatus.buyers = true;
            globalLoadingStatus.buyersError = null;

            // Helper to update tab loading indicators
            const updateTabLoading = (tab, loading) => {
                const tabEl = document.querySelector(`.content-tab[data-content="${tab}"]`);
                if (tabEl) {
                    const countEl = tabEl.querySelector('.count');
                    if (loading && countEl) {
                        countEl.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:8px"></i>';
                    }
                }
            };

            // Show loading on all tabs
            updateTabLoading('carts', true);
            updateTabLoading('pending', true);
            updateTabLoading('buyers', true);

            // PROGRESSIVE LOADING: Load carts & pending first (fastest), show them immediately
            // Then load buyers in background (slowest)

            // Phase 1: Load fast data (carts + pending) in parallel
            const [cartsResult, pendingResult] = await Promise.allSettled([
                apiFetch('api.php?action=abandoned-carts', { component: 'Carts', silent: true }),
                apiFetch('api.php?action=pending-orders', { component: 'Pending', silent: true })
            ]);

            // Process carts immediately
            if (cartsResult.status === 'fulfilled' && cartsResult.value.success) {
                carts = Array.isArray(cartsResult.value.data) ? cartsResult.value.data : [];
                console.log('[Data] ✓ Carts:', carts.length);
            } else {
                console.error('[Data] ✗ Carts failed:', cartsResult.reason || cartsResult.value?.error);
                carts = [];
            }
            globalLoadingStatus.carts = false;

            // Process pending immediately
            if (pendingResult.status === 'fulfilled' && pendingResult.value.success) {
                pending = Array.isArray(pendingResult.value.data) ? pendingResult.value.data : [];
                console.log('[Data] ✓ Pending:', pending.length);
            } else {
                console.error('[Data] ✗ Pending failed:', pendingResult.reason || pendingResult.value?.error);
                pending = [];
            }
            globalLoadingStatus.pending = false;

            // Render immediately with carts + pending (buyers will show spinner)
            updateCounts();
            updateStats();
            renderTable();
            console.log('[Data] Phase 1 complete - showing carts & pending');

            // Phase 2: Load buyers - SIMPLE AND DIRECT
            console.log('[Data] Phase 2: Loading buyers...');

            try {
                // Try cache first (instant if available)
                const cacheResult = await apiFetch('api.php?action=buyers-cache', {
                    component: 'BuyersCache',
                    silent: true,
                    timeout: 30000
                });

                console.log('[Data] Cache result:', cacheResult.success, cacheResult.data?.buyers?.length || 0);

                if (cacheResult.success && cacheResult.data?.buyers?.length > 0) {
                    buyers = cacheResult.data.buyers;
                    console.log('[Data] ✓ Buyers from cache:', buyers.length);
                } else {
                    // No cache - load directly (slower but works)
                    console.log('[Data] No cache, loading directly...');
                    const directResult = await apiFetch('api.php?action=one-time-buyers', {
                        component: 'Buyers',
                        silent: true,
                        timeout: 120000  // 2 minutes for full fetch
                    });

                    if (directResult.success && Array.isArray(directResult.data)) {
                        buyers = directResult.data;
                        console.log('[Data] ✓ Buyers direct:', buyers.length);
                    } else {
                        console.error('[Data] ✗ Buyers failed');
                        buyers = [];
                        globalLoadingStatus.buyersError = 'Napaka pri nalaganju';
                    }
                }
            } catch (err) {
                console.error('[Data] Buyers error:', err);
                buyers = [];
                globalLoadingStatus.buyersError = err.message || 'Napaka';
            }

            // Always mark as loaded
            globalLoadingStatus.buyers = false;
            globalLoadingStatus.buyersError = buyers.length === 0 ? (globalLoadingStatus.buyersError || null) : null;

            // Update UI
            updateCounts();
            renderTable();

            // Final render with all data
            console.log('[Data] Phase 2 complete - all data loaded');
            console.log('[Data] Totals:', { carts: carts.length, pending: pending.length, buyers: buyers.length });

            updateCounts();
            updateStats();
            renderTable();

            // Show warning if any failed
            const failures = [
                cartsResult.status !== 'fulfilled' || !cartsResult.value?.success ? 'Carts' : null,
                pendingResult.status !== 'fulfilled' || !pendingResult.value?.success ? 'Pending' : null,
                globalLoadingStatus.buyersError ? 'Buyers' : null
            ].filter(Boolean);

            if (failures.length > 0) {
                showToast(`⚠️ Nekateri podatki niso bili naloženi: ${failures.join(', ')}`, 'warning');
            }
        }

        // Background refresh for buyers cache
        async function refreshBuyersInBackground() {
            try {
                const result = await fetch('api.php?action=refresh-buyers-cache');
                const data = await result.json();
                if (data.success) {
                    console.log('[Data] Background buyers refresh complete:', data.count, 'buyers in', data.fetch_time_seconds, 's');
                    // Reload from cache to get fresh data
                    const freshCache = await apiFetch('api.php?action=buyers-cache', { silent: true, timeout: 30000 });
                    if (freshCache.success && freshCache.data?.buyers?.length > 0) {
                        buyers = freshCache.data.buyers;
                        updateCounts();
                        if (currentContentTab === 'buyers') renderTable();
                    }
                }
            } catch (err) {
                console.error('[Data] Background buyers refresh failed:', err);
            }
        }

        // Function to retry loading buyers only
        async function retryLoadBuyers() {
            console.log('[Data] Retrying buyers load...');
            globalLoadingStatus.buyers = true;
            globalLoadingStatus.buyersError = null;

            // Update UI immediately
            const tabEl = document.querySelector('.content-tab[data-content="buyers"]');
            if (tabEl) {
                const countEl = tabEl.querySelector('.count');
                if (countEl) countEl.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:8px"></i>';
            }
            renderTable();  // Show loading spinner

            // Force refresh the cache first
            showToast('🔄 Osveževanje podatkov...', 'info');
            try {
                await fetch('api.php?action=refresh-buyers-cache');
            } catch (e) {
                console.error('[Data] Cache refresh failed:', e);
            }

            // Then load from cache
            const cacheResult = await apiFetch('api.php?action=buyers-cache', {
                component: 'Buyers',
                silent: false,
                timeout: 5000
            });

            if (cacheResult.success && cacheResult.data?.buyers) {
                buyers = cacheResult.data.buyers;
                console.log('[Data] ✓ Buyers retry success:', buyers.length);
                globalLoadingStatus.buyersError = null;
                showToast(`✓ Enkratni kupci naloženi: ${buyers.length}`);
            } else {
                // Fallback to direct API
                const buyersResult = await apiFetch('api.php?action=one-time-buyers', {
                    component: 'Buyers',
                    silent: false,
                    timeout: 45000
                });

                if (buyersResult.success) {
                    buyers = Array.isArray(buyersResult.data) ? buyersResult.data : [];
                    console.log('[Data] ✓ Buyers retry success:', buyers.length);
                    globalLoadingStatus.buyersError = null;
                    showToast(`✓ Enkratni kupci naloženi: ${buyers.length}`);
                } else {
                    console.error('[Data] ✗ Buyers retry failed:', buyersResult.error);
                    buyers = [];
                    globalLoadingStatus.buyersError = buyersResult.error || 'Napaka pri nalaganju';
                }
            }
            globalLoadingStatus.buyers = false;

            updateCounts();
            renderTable();
        }

        // Bulletproof SMS data loading
        async function loadSmsDataBulletproof() {
            const result = await apiFetch('api.php?action=sms-queue', {
                component: 'SMS Queue',
                silent: true
            });

            if (result.success && Array.isArray(result.data)) {
                smsLog = result.data;
                console.log('[SMS] ✓ Queue loaded:', smsLog.length);
            } else {
                smsLog = [];
                console.warn('[SMS] ✗ Queue failed, using empty array');
            }
        }

        // Bulletproof SMS templates loading - uses direct JSON fetch
        async function loadSmsTemplatesBulletproof() {
            // Reuse the main loadSmsTemplates function
            await loadSmsTemplates();
        }

        // Bulletproof Paketomati loading
        async function loadPaketomatiBulletproof() {
            const result = await apiFetch('api.php?action=paketomati', {
                component: 'Paketomati',
                silent: true
            });

            if (result.success && Array.isArray(result.data)) {
                paketomatiData = result.data;
                console.log('[Paketomati] ✓ Loaded:', paketomatiData.length);
                // Count is updated by updateContentTabCounts() after init
            } else {
                paketomatiData = [];
                console.warn('[Paketomati] ✗ Failed, using empty array');
            }
        }

        // Legacy loadAllData for backwards compatibility
        async function loadAllData() {
            return loadAllDataBulletproof();
        }

        async function refreshData() {
            console.log('[Refresh] Starting data refresh...');
            document.getElementById('tableContainer').innerHTML = renderSkeletonTable(8);

            try {
                // Clear cache first
                await apiFetch('api.php?action=clear-cache', {
                    component: 'ClearCache',
                    silent: true,
                    retries: 1
                });

                // Reload all data
                await loadAllDataBulletproof();
                showToast('✓ Podatki osveženi!');
            } catch (e) {
                console.error('[Refresh] Error:', e);
                showToast('⚠️ Napaka pri osvežitvi podatkov', 'error');
            }
        }

        async function loadSmsData() {
            return loadSmsDataBulletproof();

            // Automation rules still in localStorage (not critical)
            smsAutomation = JSON.parse(localStorage.getItem('sms_automation') || '[]');

            // Default automation rules if empty
            if (smsAutomation.length === 0) {
                smsAutomation = [
                    { id: 1, name: 'Po 3 urah', delay: 3, template: 'abandoned_cart', country: 'all', active: false },
                    { id: 2, name: 'Po 12 urah', delay: 12, template: 'winback', country: 'all', active: false },
                    { id: 3, name: 'Po 24 urah', delay: 24, template: 'last_chance', country: 'all', active: false }
                ];
                saveSmsAutomation();
            }

            document.getElementById('navSms').textContent = smsLog.filter(s => s.status === 'queued').length;
        }

        function saveSmsLog() {
            localStorage.setItem('sms_log', JSON.stringify(smsLog));
            document.getElementById('navSms').textContent = smsLog.filter(s => s.status === 'queued').length;
        }

        function saveSmsAutomation() {
            localStorage.setItem('sms_automation', JSON.stringify(smsAutomation));
        }

        function updateCounts() {
            // Filter by user's allowed countries (admins see everything)
            const shouldFilter = !hasAllCountries && !isAdmin;
            let filteredCarts = shouldFilter ? carts.filter(c => userCountries.includes(c.storeCode)) : carts;
            const filteredPending = shouldFilter ? pending.filter(p => userCountries.includes(p.storeCode)) : pending;
            const filteredBuyers = shouldFilter ? buyers.filter(b => userCountries.includes(b.storeCode)) : buyers;

            // Filter out converted carts
            filteredCarts = filteredCarts.filter(c => c.converted !== true);

            // Update nav sidebar counts (guard against missing elements)
            const navCarts = document.getElementById('navCarts');
            const navPending = document.getElementById('navPending');
            const navBuyers = document.getElementById('navBuyers');
            if (navCarts) navCarts.textContent = filteredCarts.length;
            if (navPending) navPending.textContent = filteredPending.length;
            if (navBuyers) navBuyers.textContent = filteredBuyers.length;

            // Update content tab counts
            updateContentTabCounts();
        }

        // Currency conversion rates to EUR (approximate, Feb 2026)
        // Update periodically for accuracy
        const currencyToEur = {
            EUR: 1,
            CZK: 0.040,  // 1 CZK ≈ 0.040 EUR (25 CZK = 1 EUR)
            PLN: 0.23,   // 1 PLN ≈ 0.23 EUR (4.35 PLN = 1 EUR)
            HUF: 0.0025  // 1 HUF ≈ 0.0025 EUR (400 HUF = 1 EUR)
        };

        function convertToEur(value, currency) {
            const rate = currencyToEur[currency] || 1;
            return value * rate;
        }

        function updateStats() {
            // Filter by user's allowed countries first (same as updateContentTabCounts)
            const shouldFilter = !hasAllCountries && !isAdmin;
            let fc = shouldFilter ? carts.filter(c => userCountries.includes(c.storeCode)) : carts;
            let fp = shouldFilter ? pending.filter(p => userCountries.includes(p.storeCode)) : pending;

            // Filter out converted carts
            fc = fc.filter(c => c.converted !== true);

            // Then filter by currentStore if not 'all'
            if (currentStore !== 'all') {
                fc = fc.filter(c => c.storeCode === currentStore);
                fp = fp.filter(p => p.storeCode === currentStore);
            }

            // Convert all values to EUR before summing
            const totalValue = fc.reduce((sum, c) => sum + convertToEur(c.cartValue || 0, c.currency), 0);
            const sym = '€'; // Always show in EUR since we're converting

            const today = new Date().toDateString();
            const smsToday = smsLog.filter(s => new Date(s.date).toDateString() === today).length;

            document.getElementById('statCarts').textContent = fc.length;
            document.getElementById('statValue').textContent = sym + Math.round(totalValue).toLocaleString();
            document.getElementById('statPending').textContent = fp.length;
            document.getElementById('statSms').textContent = smsToday;
        }

        // Filters
        document.getElementById('searchInput').addEventListener('input', renderTable);
        document.getElementById('statusFilter').addEventListener('change', renderTable);

        function renderTable() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const status = document.getElementById('statusFilter').value;
            const container = document.getElementById('tableContainer');

            // BUYERS: Check loading/error state first
            if (currentTab === 'buyers') {
                // Still loading buyers
                if (globalLoadingStatus.buyers) {
                    container.innerHTML = `
                        <div class="loading" style="padding:60px;text-align:center;">
                            <div class="spinner" style="width:48px;height:48px;margin:0 auto 20px;"></div>
                            <div style="font-size:16px;margin-bottom:8px;">Nalagam enkratne kupce...</div>
                            <div style="color:var(--text-muted);font-size:13px;">To lahko traja do 30 sekund</div>
                        </div>
                    `;
                    return;
                }

                // Buyers failed to load - show error with retry
                if (globalLoadingStatus.buyersError) {
                    container.innerHTML = `
                        <div class="empty" style="padding:60px;">
                            <i class="fas fa-exclamation-triangle" style="font-size:48px;color:var(--accent-red);margin-bottom:16px;"></i>
                            <p style="color:var(--accent-red);margin-bottom:8px;">Napaka pri nalaganju enkratnih kupcev</p>
                            <small style="color:var(--text-muted);display:block;margin-bottom:20px;">${globalLoadingStatus.buyersError}</small>
                            <button class="btn btn-save" onclick="retryLoadBuyers()" style="margin:0 auto;">
                                <i class="fas fa-redo"></i> Poskusi znova
                            </button>
                        </div>
                    `;
                    return;
                }
            }

            // Hide urgent action bar by default
            const urgentActionBar = document.getElementById('urgentActionBar');
            if (urgentActionBar) urgentActionBar.style.display = 'none';
            
            // Handle urgent tab separately (uses localStorage, not server data)
            if (currentTab === 'urgent') {
                renderUrgentTableInline();
                return;
            }
            
            // Handle paketomati tab separately
            if (currentTab === 'paketomati') {
                renderPaketomatiInline();
                return;
            }
            
            let data = currentTab === 'carts' ? [...carts] : currentTab === 'pending' ? [...pending] : [...buyers];

            // Filter out converted carts - they should not appear in abandoned carts list
            if (currentTab === 'carts') {
                data = data.filter(d => d.converted !== true);
            }

            // Filter by user's allowed countries first (admins see everything)
            if (!hasAllCountries && !isAdmin) {
                data = data.filter(d => userCountries.includes(d.storeCode));
            }

            if (currentStore !== 'all') data = data.filter(d => d.storeCode === currentStore);
            if (status) data = data.filter(d => d.callStatus === status);
            if (search) data = data.filter(d =>
                (d.customerName || '').toLowerCase().includes(search) ||
                (d.email || '').toLowerCase().includes(search) ||
                (d.phone || '').includes(search)
            );

            // Sort data
            data.sort((a, b) => {
                let valA = a[sortColumn] || '';
                let valB = b[sortColumn] || '';

                if (sortColumn === 'cartValue' || sortColumn === 'orderTotal' || sortColumn === 'totalSpent') {
                    valA = parseFloat(valA) || 0;
                    valB = parseFloat(valB) || 0;
                } else if (sortColumn === 'abandonedAt' || sortColumn === 'createdAt' || sortColumn === 'registeredAt') {
                    valA = new Date(valA).getTime() || 0;
                    valB = new Date(valB).getTime() || 0;
                } else {
                    valA = String(valA).toLowerCase();
                    valB = String(valB).toLowerCase();
                }

                if (sortDirection === 'asc') {
                    return valA > valB ? 1 : valA < valB ? -1 : 0;
                } else {
                    return valA < valB ? 1 : valA > valB ? -1 : 0;
                }
            });

            const totalItems = data.length;

            if (!totalItems) {
                const emptyMessages = {
                    'carts': '<div class="empty"><i class="fas fa-shopping-cart"></i><p>Ni zapuščenih košaric</p><small style="color:var(--text-muted);">Vse košarice so bile pretvorjene ali filtrirane</small></div>',
                    'pending': '<div class="empty"><i class="fas fa-clock"></i><p>Ni čakajočih naročil</p><small style="color:var(--text-muted);">Vsa naročila so bila obdelana</small></div>',
                    'buyers': '<div class="empty"><i class="fas fa-user"></i><p>Ni enkratnih kupcev</p><small style="color:var(--text-muted);">Trenutno ni kupcev z natanko 1 naročilom ki ustrezajo filtru</small></div>',
                    'urgent': '<div class="empty"><i class="fas fa-phone-slash"></i><p>Ni nujnih leadov</p><small style="color:var(--text-muted);">Klikni + Dodaj za vnos novega leada</small></div>'
                };
                container.innerHTML = emptyMessages[currentTab] || '<div class="empty"><i class="fas fa-inbox"></i><p>Ni podatkov za prikaz</p></div>';
                return;
            }

            // Pagination
            const totalPages = Math.ceil(totalItems / itemsPerPage);
            if (currentPage > totalPages) currentPage = 1;
            const startIdx = (currentPage - 1) * itemsPerPage;
            const paginatedData = data.slice(startIdx, startIdx + itemsPerPage);

            if (currentTab === 'carts') renderCartsTable(paginatedData, totalItems, totalPages);
            else if (currentTab === 'pending') renderPendingTable(paginatedData, totalItems, totalPages);
            else if (currentTab === 'urgent') renderUrgentTableInline();
            else renderBuyersTable(paginatedData, totalItems, totalPages);
        }

        function sortTable(column) {
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = column;
                sortDirection = 'desc';
            }
            currentPage = 1;
            renderTable();
        }

        function goToPage(page) {
            currentPage = page;
            renderTable();
        }

        function changeItemsPerPage(val) {
            itemsPerPage = parseInt(val) || 25;
            currentPage = 1;
            renderTable();
        }

        function renderPagination(totalItems, totalPages) {
            const start = (currentPage - 1) * itemsPerPage + 1;
            const end = Math.min(currentPage * itemsPerPage, totalItems);

            let pageButtons = '';
            const maxButtons = 5;
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + maxButtons - 1);
            if (endPage - startPage < maxButtons - 1) startPage = Math.max(1, endPage - maxButtons + 1);

            for (let i = startPage; i <= endPage; i++) {
                pageButtons += `<button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="goToPage(${i})">${i}</button>`;
            }

            return `
                <div class="pagination">
                    <div class="pagination-info">
                        Prikazujem <strong>${start}-${end}</strong> od <strong>${totalItems}</strong> vnosov
                    </div>
                    <div class="pagination-controls">
                        <select class="pagination-select" onchange="changeItemsPerPage(this.value)">
                            <option value="10" ${itemsPerPage === 10 ? 'selected' : ''}>10 / stran</option>
                            <option value="25" ${itemsPerPage === 25 ? 'selected' : ''}>25 / stran</option>
                            <option value="50" ${itemsPerPage === 50 ? 'selected' : ''}>50 / stran</option>
                            <option value="100" ${itemsPerPage === 100 ? 'selected' : ''}>100 / stran</option>
                        </select>
                        <button class="pagination-btn" onclick="goToPage(1)" ${currentPage === 1 ? 'disabled' : ''}>
                            <i class="fas fa-angle-double-left"></i>
                        </button>
                        <button class="pagination-btn" onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                            <i class="fas fa-angle-left"></i>
                        </button>
                        ${pageButtons}
                        <button class="pagination-btn" onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                            <i class="fas fa-angle-right"></i>
                        </button>
                        <button class="pagination-btn" onclick="goToPage(${totalPages})" ${currentPage === totalPages ? 'disabled' : ''}>
                            <i class="fas fa-angle-double-right"></i>
                        </button>
                    </div>
                </div>
            `;
        }

        function renderCartsTable(data, totalItems, totalPages) {
            const getSortClass = (col) => sortColumn === col ? sortDirection : '';
            // Clear selection when table changes
            selectedItems.clear();
            updateBulkBar();

            document.getElementById('tableContainer').innerHTML = `
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th class="checkbox-cell"><input type="checkbox" class="row-checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"></th>
                        <th class="sortable ${getSortClass('customerName')}" onclick="sortTable('customerName')">Customer</th>
                        <th class="sortable ${getSortClass('storeCode')}" onclick="sortTable('storeCode')">Store</th>
                        <th class="sortable ${getSortClass('cartValue')}" onclick="sortTable('cartValue')">Value</th>
                        <th>Phone</th>
                        <th class="sortable ${getSortClass('callStatus')}" onclick="sortTable('callStatus')">Status</th>
                        <th>Notes</th>
                        <th class="sortable ${getSortClass('abandonedAt')}" onclick="sortTable('abandonedAt')">Time</th>
                        <th style="text-align:right;">Actions</th>
                    </tr></thead>
                    <tbody>
                        ${data.map(c => {
                            const sym = {EUR:'€',CZK:'Kč',PLN:'zł',HUF:'Ft'}[c.currency] || '€';
                            const isConverted = c.converted === true;
                            const rowStyle = isConverted ? 'background: linear-gradient(90deg, #d4edda 0%, #f8f9fa 100%); opacity: 0.85;' : '';
                            const convertedBadge = isConverted ? '<span style="background:#28a745;color:white;padding:2px 6px;border-radius:4px;font-size:10px;margin-left:6px;">KUPIL ✓</span>' : '';
                            return `<tr style="${rowStyle}">
                                <td class="checkbox-cell"><input type="checkbox" class="row-checkbox" data-id="${c.id}" onchange="toggleRowSelection(this)" ${isConverted ? 'disabled' : ''}></td>
                                <td><div class="customer-cell" onclick="openCustomer360(carts.find(x=>x.id==='${c.id}'))" style="cursor:pointer;"><div class="avatar" style="${isConverted ? 'background:#28a745;' : ''}">${initials(c.customerName)}</div><div><div class="customer-name">${esc(c.customerName)}${convertedBadge}</div><div class="customer-email">${esc(c.email)}</div></div></div></td>
                                <td>${c.storeFlag} ${c.storeName}</td>
                                <td><strong>${sym}${(c.cartValue||0).toFixed(2)}</strong></td>
                                <td>${c.phone ? `<a href="tel:${c.phone}" class="phone-link"><i class="fas fa-phone"></i> ${c.phone}</a>` : '-'}</td>
                                <td>
                                    ${isConverted ? '<span style="color:#28a745;font-weight:600;">✅ Converted</span>' : `<select class="inline-status-select" data-id="${c.id}" data-type="cart" onchange="inlineStatusChange(this)">
                                        <option value="not_called" ${c.callStatus==='not_called'?'selected':''}>⚪ Not Called</option>
                                        <option value="no_answer_1" ${c.callStatus==='no_answer_1'||c.callStatus==='called_no_answer'?'selected':''}>📵 No Answer 1</option>
                                        <option value="no_answer_2" ${c.callStatus==='no_answer_2'?'selected':''}>📵 No Answer 2</option>
                                        <option value="no_answer_3" ${c.callStatus==='no_answer_3'?'selected':''}>📵 No Answer 3</option>
                                        <option value="no_answer_4" ${c.callStatus==='no_answer_4'?'selected':''}>📵 No Answer 4+</option>
                                        <option value="called_callback" ${c.callStatus==='called_callback'?'selected':''}>🔄 Callback</option>
                                        <option value="called_interested" ${c.callStatus==='called_interested'?'selected':''}>💡 Interested</option>
                                        <option value="called_not_interested" ${c.callStatus==='called_not_interested'?'selected':''}>👎 Not Interested</option>
                                        <option value="invalid_number" ${c.callStatus==='invalid_number'?'selected':''}>🚫 Invalid Number</option>
                                    </select>`}
                                </td>
                                <td>
                                    <div class="inline-notes-wrapper">
                                        <input type="text" class="inline-notes-input ${c.notes ? 'has-notes' : ''}"
                                               data-id="${c.id}" data-type="cart"
                                               value="${escAttr(c.notes || '')}"
                                               placeholder="${isConverted ? 'Converted' : 'Add notes...'}"
                                               ${isConverted ? 'disabled' : ''}
                                               onchange="markNotesChanged(this)"
                                               onkeypress="if(event.key==='Enter'){saveInlineNotes(this)}">
                                        ${isConverted ? '' : '<button class="inline-notes-save" onclick="saveInlineNotes(this.previousElementSibling)" title="Save">💾</button>'}
                                    </div>
                                </td>
                                <td style="font-size:12px;">${timeAgo(c.abandonedAt)}</td>
                                <td style="white-space:nowrap;text-align:right;">
                                    ${isConverted ? '<span style="color:#28a745;font-size:11px;">No action needed</span>' : `
                                    ${c.phone ? `<button class="action-btn call" onclick="call('${c.phone}')" title="Call"><i class="fas fa-phone"></i></button>` : ''}
                                    ${c.phone ? `<button class="action-btn sms" onclick="openSmsModal('${c.id}','cart')" title="Send SMS"><i class="fas fa-comment-sms"></i></button>` : ''}
                                    <button class="action-btn-order-large" onclick="openOrderModal('${c.id}')" title="Create Order"><i class="fas fa-shopping-bag"></i> CREATE ORDER</button>
                                    `}
                                </td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
                </div>
                ${renderPagination(totalItems, totalPages)}`;
        }

        function renderPendingTable(data, totalItems, totalPages) {
            const getSortClass = (col) => sortColumn === col ? sortDirection : '';
            document.getElementById('tableContainer').innerHTML = `
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th class="sortable ${getSortClass('customerName')}" onclick="sortTable('customerName')">Customer</th>
                        <th class="sortable ${getSortClass('storeCode')}" onclick="sortTable('storeCode')">Store</th>
                        <th class="sortable ${getSortClass('orderId')}" onclick="sortTable('orderId')">Order #</th>
                        <th class="sortable ${getSortClass('orderTotal')}" onclick="sortTable('orderTotal')">Total</th>
                        <th>Phone</th>
                        <th class="sortable ${getSortClass('callStatus')}" onclick="sortTable('callStatus')">Status</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr></thead>
                    <tbody>
                        ${data.map(o => {
                            const sym = {EUR:'€',CZK:'Kč',PLN:'zł',HUF:'Ft'}[o.currency] || '€';
                            return `<tr>
                                <td><div class="customer-cell"><div class="avatar">${initials(o.customerName)}</div><div><div class="customer-name">${esc(o.customerName)}</div><div class="customer-email">${esc(o.email)}</div></div></div></td>
                                <td>${o.storeFlag} ${o.storeName}</td>
                                <td><strong>#${o.orderId}</strong></td>
                                <td><strong>${sym}${(o.orderTotal||0).toFixed(2)}</strong></td>
                                <td>${o.phone ? `<a href="tel:${o.phone}" class="phone-link"><i class="fas fa-phone"></i> ${o.phone}</a>` : '-'}</td>
                                <td>
                                    <select class="inline-status-select" data-id="${o.id}" data-type="pending" onchange="inlineStatusChange(this)">
                                        <option value="not_called" ${o.callStatus==='not_called'?'selected':''}>⚪ Not Called</option>
                                        <option value="no_answer_1" ${o.callStatus==='no_answer_1'||o.callStatus==='called_no_answer'?'selected':''}>📵 No Answer 1</option>
                                        <option value="no_answer_2" ${o.callStatus==='no_answer_2'?'selected':''}>📵 No Answer 2</option>
                                        <option value="no_answer_3" ${o.callStatus==='no_answer_3'?'selected':''}>📵 No Answer 3</option>
                                        <option value="no_answer_4" ${o.callStatus==='no_answer_4'?'selected':''}>📵 No Answer 4+</option>
                                        <option value="called_callback" ${o.callStatus==='called_callback'?'selected':''}>🔄 Callback</option>
                                        <option value="called_interested" ${o.callStatus==='called_interested'?'selected':''}>💡 Interested</option>
                                        <option value="called_not_interested" ${o.callStatus==='called_not_interested'?'selected':''}>👎 Not Interested</option>
                                        <option value="invalid_number" ${o.callStatus==='invalid_number'?'selected':''}>🚫 Invalid Number</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="inline-notes-wrapper">
                                        <input type="text" class="inline-notes-input ${o.notes ? 'has-notes' : ''}"
                                               data-id="${o.id}" data-type="pending"
                                               value="${escAttr(o.notes || '')}"
                                               placeholder="Add notes..."
                                               onchange="markNotesChanged(this)"
                                               onkeypress="if(event.key==='Enter'){saveInlineNotes(this)}">
                                        <button class="inline-notes-save" onclick="saveInlineNotes(this.previousElementSibling)" title="Save">💾</button>
                                    </div>
                                </td>
                                <td style="white-space:nowrap;">
                                    ${o.phone ? `<button class="action-btn call" onclick="call('${o.phone}')"><i class="fas fa-phone"></i></button>` : ''}
                                    ${o.phone ? `<button class="action-btn sms" onclick="openSmsModal('${o.id}','pending')" title="SMS"><i class="fas fa-comment-sms"></i></button>` : ''}
                                </td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
                </div>
                ${renderPagination(totalItems, totalPages)}`;
        }

        function renderBuyersTable(data, totalItems, totalPages) {
            const getSortClass = (col) => sortColumn === col ? sortDirection : '';
            document.getElementById('tableContainer').innerHTML = `
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th class="sortable ${getSortClass('customerName')}" onclick="sortTable('customerName')">Customer</th>
                        <th class="sortable ${getSortClass('storeCode')}" onclick="sortTable('storeCode')">Store</th>
                        <th class="sortable ${getSortClass('totalSpent')}" onclick="sortTable('totalSpent')">Spent</th>
                        <th>Phone</th>
                        <th class="sortable ${getSortClass('registeredAt')}" onclick="sortTable('registeredAt')">Registered</th>
                        <th class="sortable ${getSortClass('callStatus')}" onclick="sortTable('callStatus')">Status</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr></thead>
                    <tbody>
                        ${data.map(b => {
                            const sym = {EUR:'€',CZK:'Kč',PLN:'zł',HUF:'Ft'}[b.currency] || '€';
                            const isConverted = b.converted === true;
                            const rowStyle = isConverted ? 'background: linear-gradient(90deg, #d4edda 0%, #f8f9fa 100%); opacity: 0.85;' : '';
                            const convertedBadge = isConverted ? '<span style="background:#28a745;color:white;padding:2px 6px;border-radius:4px;font-size:10px;margin-left:6px;">PONOVNI ✓</span>' : '';
                            return `<tr style="${rowStyle}">
                                <td><div class="customer-cell"><div class="avatar" style="${isConverted ? 'background:#28a745;' : ''}">${initials(b.customerName)}</div><div><div class="customer-name">${esc(b.customerName)}${convertedBadge}</div><div class="customer-email">${esc(b.email)}</div></div></div></td>
                                <td>${b.storeFlag} ${b.storeName}</td>
                                <td><strong>${sym}${(b.totalSpent||0).toFixed(2)}</strong></td>
                                <td>${b.phone ? `<a href="tel:${b.phone}" class="phone-link"><i class="fas fa-phone"></i> ${b.phone}</a>` : '-'}</td>
                                <td style="font-size:12px;">${formatDate(b.registeredAt)}</td>
                                <td>
                                    ${isConverted ? '<span style="color:#28a745;font-weight:600;">✅ Repeat Customer</span>' : `<select class="inline-status-select" data-id="${b.id}" data-type="buyer" onchange="inlineStatusChange(this)">
                                        <option value="not_called" ${b.callStatus==='not_called'?'selected':''}>⚪ Not Called</option>
                                        <option value="no_answer_1" ${b.callStatus==='no_answer_1'||b.callStatus==='called_no_answer'?'selected':''}>📵 No Answer 1</option>
                                        <option value="no_answer_2" ${b.callStatus==='no_answer_2'?'selected':''}>📵 No Answer 2</option>
                                        <option value="no_answer_3" ${b.callStatus==='no_answer_3'?'selected':''}>📵 No Answer 3</option>
                                        <option value="no_answer_4" ${b.callStatus==='no_answer_4'?'selected':''}>📵 No Answer 4+</option>
                                        <option value="called_callback" ${b.callStatus==='called_callback'?'selected':''}>🔄 Callback</option>
                                        <option value="called_interested" ${b.callStatus==='called_interested'?'selected':''}>💡 Interested</option>
                                        <option value="called_not_interested" ${b.callStatus==='called_not_interested'?'selected':''}>👎 Not Interested</option>
                                        <option value="invalid_number" ${b.callStatus==='invalid_number'?'selected':''}>🚫 Invalid Number</option>
                                    </select>`}
                                </td>
                                <td>
                                    <div class="inline-notes-wrapper">
                                        <input type="text" class="inline-notes-input ${b.notes ? 'has-notes' : ''}"
                                               data-id="${b.id}" data-type="buyer"
                                               value="${escAttr(b.notes || '')}"
                                               placeholder="${isConverted ? 'Repeat customer' : 'Add notes...'}"
                                               ${isConverted ? 'disabled' : ''}
                                               onchange="markNotesChanged(this)"
                                               onkeypress="if(event.key==='Enter'){saveInlineNotes(this)}">
                                        ${isConverted ? '' : '<button class="inline-notes-save" onclick="saveInlineNotes(this.previousElementSibling)" title="Save">💾</button>'}
                                    </div>
                                </td>
                                <td style="white-space:nowrap;">
                                    ${isConverted ? '<span style="color:#28a745;font-size:11px;">No action needed</span>' : `
                                    ${b.phone ? `<button class="action-btn call" onclick="call('${b.phone}')"><i class="fas fa-phone"></i></button>` : ''}
                                    ${b.phone ? `<button class="action-btn sms" onclick="openSmsModal('${b.id}','buyer')" title="SMS"><i class="fas fa-comment-sms"></i></button>` : ''}
                                    `}
                                </td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
                </div>
                ${renderPagination(totalItems, totalPages)}`;
        }

        // Status Modal
        function openStatusModal(id, status, notes) {
            editId = id;
            document.getElementById('modalStatus').value = status || 'not_called';
            document.getElementById('modalNotes').value = notes || '';
            document.getElementById('statusModal').classList.add('open');
        }

        async function saveStatus() {
            const status = document.getElementById('modalStatus').value;
            const notes = document.getElementById('modalNotes').value;
            await fetch('api.php?action=update-status', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id: editId, callStatus: status, notes})
            });
            [carts, pending, buyers].forEach(arr => {
                const item = arr.find(i => i.id === editId);
                if (item) { item.callStatus = status; item.notes = notes; }
            });
            closeModal('statusModal');
            renderTable();
            showToast('Status updated!');
        }

        // Inline Status Change
        let pendingCallbackSelect = null; // Store reference to the select that triggered callback

        async function inlineStatusChange(select) {
            const id = select.dataset.id;
            const type = select.dataset.type;
            const newStatus = select.value;

            // If callback is selected, show the quick callback modal
            if (newStatus === 'called_callback') {
                pendingCallbackSelect = select;
                document.getElementById('quickCallbackId').value = id;
                document.getElementById('quickCallbackType').value = type;
                
                // Set default datetime to tomorrow at 10:00
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                tomorrow.setHours(10, 0, 0, 0);
                document.getElementById('quickCallbackDateTime').value = tomorrow.toISOString().slice(0, 16);
                document.getElementById('quickCallbackNote').value = '';
                
                document.getElementById('quickCallbackModal').classList.add('open');
                return;
            }

            // Find the item and get current notes
            let item = null;
            if (type === 'cart') item = carts.find(c => c.id === id);
            else if (type === 'pending') item = pending.find(p => p.id === id);
            else if (type === 'buyer') item = buyers.find(b => b.id === id);

            const currentNotes = item?.notes || '';

            try {
                await fetch('api.php?action=update-status', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({id, callStatus: newStatus, notes: currentNotes})
                });
                if (item) item.callStatus = newStatus;
                showToast('Status updated!');
            } catch (err) {
                showToast('Error updating status', false, 'error');
            }
        }

        function cancelQuickCallback() {
            // Reset the select to previous value
            if (pendingCallbackSelect) {
                const id = pendingCallbackSelect.dataset.id;
                const type = pendingCallbackSelect.dataset.type;
                let item = null;
                if (type === 'cart') item = carts.find(c => c.id === id);
                else if (type === 'pending') item = pending.find(p => p.id === id);
                else if (type === 'buyer') item = buyers.find(b => b.id === id);
                
                if (item) {
                    pendingCallbackSelect.value = item.callStatus || 'not_called';
                }
                pendingCallbackSelect = null;
            }
            closeModal('quickCallbackModal');
        }

        async function confirmQuickCallback() {
            const id = document.getElementById('quickCallbackId').value;
            const type = document.getElementById('quickCallbackType').value;
            const callbackDateTime = document.getElementById('quickCallbackDateTime').value;
            const note = document.getElementById('quickCallbackNote').value.trim();

            if (!callbackDateTime) {
                showToast('Prosim izberi datum in čas!', true);
                return;
            }

            // Find the item
            let item = null;
            if (type === 'cart') item = carts.find(c => c.id === id);
            else if (type === 'pending') item = pending.find(p => p.id === id);
            else if (type === 'buyer') item = buyers.find(b => b.id === id);

            if (!item) {
                showToast('Napaka: element ni najden', true);
                return;
            }

            try {
                // 1. Update status to callback
                const statusRes = await fetch('api.php?action=update-status', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({id, callStatus: 'called_callback', notes: item.notes || ''})
                });
                console.log('[QuickCallback] Status update response:', await statusRes.clone().text());

                // 2. Create call log with callback (this creates the follow-up)
                const logRes = await fetch('api.php?action=call-logs-add', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({
                        customerId: id,
                        storeCode: item.storeCode,
                        status: 'callback',
                        notes: note || 'Callback scheduled',
                        duration: 0,
                        callbackAt: new Date(callbackDateTime).toISOString(),
                        agentId: user?.username || 'unknown'
                    })
                });
                const logResult = await logRes.json();
                console.log('[QuickCallback] Call log response:', logResult);

                if (!logResult.success) {
                    throw new Error(logResult.error || 'Failed to create callback');
                }

                // Update local state
                if (item) item.callStatus = 'called_callback';

                closeModal('quickCallbackModal');
                pendingCallbackSelect = null;
                showToast('✅ Callback dodan v Follow-ups!');

                // Always refresh follow-ups count in sidebar
                loadFollowupsCount();

                // Refresh follow-ups table if we're on that tab
                if (currentTab === 'followups') {
                    renderFollowups();
                }
            } catch (err) {
                console.error('Error creating callback:', err);
                showToast('Napaka pri ustvarjanju callbacka: ' + err.message, true);
            }
        }

        // Inline Notes Functions
        function markNotesChanged(input) {
            const saveBtn = input.nextElementSibling;
            if (saveBtn) saveBtn.classList.add('show');
        }

        async function saveInlineNotes(input) {
            const id = input.dataset.id;
            const type = input.dataset.type;
            const newNotes = input.value.trim();
            const saveBtn = input.nextElementSibling;

            // Find the item and get current status
            let item = null;
            if (type === 'cart') item = carts.find(c => c.id === id);
            else if (type === 'pending') item = pending.find(p => p.id === id);
            else if (type === 'buyer') item = buyers.find(b => b.id === id);

            const currentStatus = item?.callStatus || 'not_called';

            // Show saving state
            if (saveBtn) {
                saveBtn.classList.add('saving');
                saveBtn.textContent = '⏳';
            }

            try {
                await fetch('api.php?action=update-status', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({id, callStatus: currentStatus, notes: newNotes})
                });

                // Update local data
                if (item) item.notes = newNotes;

                // Update input styling
                input.classList.toggle('has-notes', newNotes.length > 0);

                // Reset save button
                if (saveBtn) {
                    saveBtn.classList.remove('saving', 'show');
                    saveBtn.textContent = '💾';
                }

                showToast('✅ Notes saved!');
            } catch (err) {
                if (saveBtn) {
                    saveBtn.classList.remove('saving');
                    saveBtn.textContent = '💾';
                }
                showToast('❌ Error saving notes', false, 'error');
            }
        }

        // Order Modal (Enhanced)
        function openOrderModal(cartId) {
            orderCartId = cartId;
            orderCart = carts.find(c => c.id === cartId);
            if (!orderCart) return;

            // Populate customer info
            const nameParts = orderCart.customerName.split(' ');
            document.getElementById('orderFirstName').value = orderCart.firstName || nameParts[0] || '';
            document.getElementById('orderLastName').value = orderCart.lastName || nameParts.slice(1).join(' ') || '';
            document.getElementById('orderEmail').value = orderCart.email || '';
            document.getElementById('orderPhone').value = orderCart.phone || '';
            document.getElementById('orderAddress').value = orderCart.address || '';
            document.getElementById('orderCity').value = orderCart.city || '';
            document.getElementById('orderPostcode').value = orderCart.postcode || '';

            // Copy cart items for editing
            orderItems = (orderCart.cartContents || []).map((item, idx) => ({
                ...item,
                idx: idx,
                editPrice: item.price,
                editQty: item.quantity
            }));

            freeShipping = false;
            document.getElementById('freeShippingToggle').classList.remove('active');

            // Reset product search
            document.getElementById('productSearchInput').value = '';
            document.getElementById('productSearchResults').classList.remove('open');
            document.getElementById('productSearchResults').innerHTML = '';
            document.getElementById('variationSelector').style.display = 'none';
            selectedProduct = null;
            selectedVariation = null;

            renderOrderItems();
            document.getElementById('orderModal').classList.add('open');
        }

        function renderOrderItems() {
            const sym = {EUR:'€',CZK:'Kč',PLN:'zł',HUF:'Ft'}[orderCart?.currency] || '€';
            const container = document.getElementById('orderItems');

            if (orderItems.length === 0) {
                container.innerHTML = '<div class="empty" style="padding:20px;"><i class="fas fa-box-open"></i><p>Ni izdelkov - uporabi iskanje zgoraj</p></div>';
                updateOrderTotals();
                return;
            }

            container.innerHTML = orderItems.map((item, i) => `
                <div class="order-item">
                    ${item.image ? `<img src="${item.image}" alt="" style="width:40px;height:40px;border-radius:6px;object-fit:cover;flex-shrink:0;" onerror="this.style.display='none'">` : ''}
                    <div class="order-item-info">
                        <div class="order-item-name">${esc(item.name)}</div>
                        <div class="order-item-id">ID: ${item.productId || 'N/A'}${item.variationId ? ' / Var: ' + item.variationId : ''}</div>
                    </div>
                    <div class="order-item-controls">
                        <div>
                            <label>Količina</label>
                            <input type="number" min="1" value="${item.editQty}" onchange="updateItemQty(${i}, this.value)">
                        </div>
                        <div>
                            <label>Cena (${sym})</label>
                            <input type="number" step="0.01" min="0" value="${item.editPrice.toFixed(2)}" onchange="updateItemPrice(${i}, this.value)">
                        </div>
                    </div>
                    <div class="order-item-total">${sym}${(item.editPrice * item.editQty).toFixed(2)}</div>
                    <button class="btn-remove-item" onclick="removeOrderItem(${i})" title="Odstrani"><i class="fas fa-trash"></i></button>
                </div>
            `).join('');

            updateOrderTotals();
        }

        function updateItemQty(idx, val) {
            orderItems[idx].editQty = Math.max(1, parseInt(val) || 1);
            renderOrderItems();
        }

        function updateItemPrice(idx, val) {
            orderItems[idx].editPrice = Math.max(0, parseFloat(val) || 0);
            renderOrderItems();
        }

        function removeOrderItem(idx) {
            orderItems.splice(idx, 1);
            renderOrderItems();
        }

        function toggleFreeShipping() {
            freeShipping = !freeShipping;
            document.getElementById('freeShippingToggle').classList.toggle('active', freeShipping);
            updateOrderTotals();
        }

        // Product Search
        let searchTimeout = null;
        let selectedProduct = null;
        let selectedVariation = null;

        document.getElementById('productSearchInput').addEventListener('input', function(e) {
            const query = e.target.value.trim();
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                document.getElementById('productSearchResults').classList.remove('open');
                return;
            }

            document.getElementById('productSearchSpinner').classList.add('active');

            searchTimeout = setTimeout(() => {
                searchProducts(query);
            }, 300);
        });

        document.getElementById('productSearchInput').addEventListener('focus', function() {
            const results = document.getElementById('productSearchResults');
            if (results.children.length > 0 && this.value.length >= 2) {
                results.classList.add('open');
            }
        });

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            const container = document.querySelector('.product-search-container');
            if (!container.contains(e.target)) {
                document.getElementById('productSearchResults').classList.remove('open');
            }
        });

        async function searchProducts(query) {
            if (!orderCart) return;

            const storeCode = orderCart.storeCode;
            const spinner = document.getElementById('productSearchSpinner');
            const resultsContainer = document.getElementById('productSearchResults');

            try {
                const res = await fetch(`api.php?action=search-products&store=${storeCode}&q=${encodeURIComponent(query)}`);
                const products = await res.json();

                spinner.classList.remove('active');

                if (products.error) {
                    resultsContainer.innerHTML = `<div class="product-search-empty"><i class="fas fa-exclamation-circle"></i> ${products.error}</div>`;
                    resultsContainer.classList.add('open');
                    return;
                }

                if (products.length === 0) {
                    resultsContainer.innerHTML = '<div class="product-search-empty"><i class="fas fa-search"></i> Ni rezultatov</div>';
                    resultsContainer.classList.add('open');
                    return;
                }

                const sym = {EUR:'€',CZK:'Kč',PLN:'zł',HUF:'Ft'}[orderCart.currency] || '€';

                resultsContainer.innerHTML = products.map(p => `
                    <div class="product-search-item" onclick='selectProduct(${JSON.stringify(p).replace(/'/g, "&#39;")})'>
                        <img src="${p.image || 'https://via.placeholder.com/48'}" alt="" class="product-search-img" onerror="this.src='https://via.placeholder.com/48'">
                        <div class="product-search-info">
                            <div class="product-search-name">${esc(p.name)}</div>
                            <div class="product-search-meta">
                                ${p.sku ? `SKU: ${p.sku} • ` : ''}
                                ${p.type === 'variable' ? `<i class="fas fa-layer-group"></i> ${p.variations.length} variacije` : 'Preprost izdelek'}
                            </div>
                        </div>
                        <div class="product-search-price">${sym}${p.price.toFixed(2)}</div>
                    </div>
                `).join('');

                resultsContainer.classList.add('open');

            } catch (e) {
                spinner.classList.remove('active');
                resultsContainer.innerHTML = '<div class="product-search-empty"><i class="fas fa-exclamation-triangle"></i> Napaka pri iskanju</div>';
                resultsContainer.classList.add('open');
            }
        }

        function selectProduct(product) {
            document.getElementById('productSearchResults').classList.remove('open');
            document.getElementById('productSearchInput').value = '';

            const sym = {EUR:'€',CZK:'Kč',PLN:'zł',HUF:'Ft'}[orderCart.currency] || '€';

            if (product.type === 'variable' && product.variations.length > 0) {
                // Show variation selector
                selectedProduct = product;
                selectedVariation = null;

                document.getElementById('variationProductImg').src = product.image || 'https://via.placeholder.com/60';
                document.getElementById('variationProductName').textContent = product.name;
                document.getElementById('variationProductPrice').textContent = sym + product.price.toFixed(2);
                document.getElementById('variationQty').value = 1;

                const optionsContainer = document.getElementById('variationOptions');
                optionsContainer.innerHTML = product.variations.map(v => `
                    <div class="variation-option ${v.inStock ? '' : 'out-of-stock'}"
                         onclick="selectVariation(${v.id}, ${v.price}, '${escAttr(v.name)}', ${v.inStock})"
                         data-var-id="${v.id}">
                        ${esc(v.name)} - ${sym}${v.price.toFixed(2)}
                    </div>
                `).join('');

                document.getElementById('variationSelector').style.display = 'block';

            } else {
                // Simple product - add directly
                addProductToOrder({
                    productId: product.id,
                    variationId: null,
                    name: product.name,
                    price: product.price,
                    quantity: 1,
                    image: product.image
                });
            }
        }

        function selectVariation(varId, price, name, inStock) {
            if (!inStock) return;

            document.querySelectorAll('.variation-option').forEach(el => el.classList.remove('selected'));
            document.querySelector(`.variation-option[data-var-id="${varId}"]`).classList.add('selected');

            const sym = {EUR:'€',CZK:'Kč',PLN:'zł',HUF:'Ft'}[orderCart.currency] || '€';
            document.getElementById('variationProductPrice').textContent = sym + price.toFixed(2);

            selectedVariation = {
                id: varId,
                price: price,
                name: name
            };
        }

        function addSelectedVariation() {
            if (!selectedProduct || !selectedVariation) {
                showToast('Izberi variacijo', true);
                return;
            }

            const qty = parseInt(document.getElementById('variationQty').value) || 1;

            addProductToOrder({
                productId: selectedProduct.id,
                variationId: selectedVariation.id,
                name: `${selectedProduct.name} - ${selectedVariation.name}`,
                price: selectedVariation.price,
                quantity: qty,
                image: selectedProduct.image
            });

            cancelVariationSelection();
        }

        function cancelVariationSelection() {
            document.getElementById('variationSelector').style.display = 'none';
            selectedProduct = null;
            selectedVariation = null;
        }

        function addProductToOrder(product) {
            // Check if product already exists in order
            const existingIndex = orderItems.findIndex(item =>
                item.productId === product.productId &&
                item.variationId === product.variationId
            );

            if (existingIndex >= 0) {
                // Increase quantity
                orderItems[existingIndex].editQty += product.quantity;
            } else {
                // Add new item
                orderItems.push({
                    productId: product.productId,
                    variationId: product.variationId,
                    name: product.name,
                    price: product.price,
                    editPrice: product.price,
                    quantity: product.quantity,
                    editQty: product.quantity,
                    image: product.image
                });
            }

            renderOrderItems();
            showToast(`✓ ${product.name} dodano`, false, 'info');
        }

        function updateOrderTotals() {
            const sym = {EUR:'€',CZK:'Kč',PLN:'zł',HUF:'Ft'}[orderCart?.currency] || '€';
            const subtotal = orderItems.reduce((sum, item) => sum + (item.editPrice * item.editQty), 0);
            const shipping = freeShipping ? 0 : SHIPPING_COST;
            const total = subtotal + shipping;

            document.getElementById('orderSubtotal').textContent = sym + subtotal.toFixed(2);
            document.getElementById('orderShipping').textContent = freeShipping ? 'BREZPLAČNO' : sym + shipping.toFixed(2);
            document.getElementById('shippingRow').classList.toggle('free-shipping', freeShipping);
            document.getElementById('orderTotal').textContent = sym + total.toFixed(2);
        }

        async function confirmCreateOrder() {
            if (!orderCartId || orderItems.length === 0) {
                showToast('Dodajte vsaj en izdelek', true);
                return;
            }

            const btn = document.getElementById('createOrderBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ustvarjam...';

            // Gather edited data
            const orderData = {
                cartId: orderCartId,
                agent: user.username,
                customer: {
                    firstName: document.getElementById('orderFirstName').value,
                    lastName: document.getElementById('orderLastName').value,
                    email: document.getElementById('orderEmail').value,
                    phone: document.getElementById('orderPhone').value,
                    address: document.getElementById('orderAddress').value,
                    city: document.getElementById('orderCity').value,
                    postcode: document.getElementById('orderPostcode').value
                },
                items: orderItems.map(item => ({
                    productId: item.productId,
                    variationId: item.variationId,
                    quantity: item.editQty,
                    price: item.editPrice
                })),
                freeShipping: freeShipping
            };

            try {
                const res = await fetch('api.php?action=create-order', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify(orderData)
                });
                const result = await res.json();

                if (result.success) {
                    closeModal('orderModal');
                    showToast(`✅ Naročilo #${result.orderNumber} ustvarjeno!`);
                    carts = carts.filter(c => c.id !== orderCartId);
                    updateCounts();
                    updateStats();
                    renderTable();
                } else {
                    showToast(result.error || 'Napaka pri ustvarjanju naročila', true);
                }
            } catch (e) {
                showToast('Napaka pri povezavi', true);
            }

            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Ustvari naročilo';
        }

        // SMS Modal
        function openSmsModal(id, type) {
            const data = type === 'cart' ? carts : type === 'pending' ? pending : type === 'paketomat' ? paketomatiData : buyers;
            const item = data.find(d => d.id === id);
            if (!item || !item.phone) return;

            smsTarget = { ...item, type };

            document.getElementById('smsAvatar').textContent = initials(item.customerName);
            document.getElementById('smsCustomerName').textContent = item.customerName;
            document.getElementById('smsCustomerPhone').textContent = item.phone;
            document.getElementById('smsMessage').value = '';
            document.getElementById('smsPreview').style.display = 'none';
            updateCharCount();

            // Populate template dropdown from loaded templates
            const templateSelect = document.getElementById('smsTemplate');
            const storeCode = item.storeCode || 'hr';
            templateSelect.innerHTML = '<option value="">-- Izberi predlogo --</option>';
            
            console.log('[SMS Modal] Opening for store:', storeCode);
            console.log('[SMS Modal] smsTemplatesData:', smsTemplatesData);
            console.log('[SMS Modal] Templates keys:', smsTemplatesData?.templates ? Object.keys(smsTemplatesData.templates) : 'none');
            
            if (smsTemplatesData?.templates) {
                Object.keys(smsTemplatesData.templates).forEach(key => {
                    const tpl = smsTemplatesData.templates[key][storeCode];
                    console.log('[SMS Modal] Template', key, 'for', storeCode, ':', tpl);
                    if (tpl) {
                        const icon = key.includes('abandoned') ? '🛒' : key.includes('winback') ? '💙' : '📱';
                        templateSelect.innerHTML += `<option value="${key}">${icon} ${tpl.name}</option>`;
                    }
                });
            }
            templateSelect.innerHTML += '<option value="custom">✏️ Prilagojeno sporočilo</option>';

            document.getElementById('smsModal').classList.add('open');
        }

        function applySmsTemplate() {
            const tpl = document.getElementById('smsTemplate').value;
            if (!tpl || tpl === 'custom') {
                document.getElementById('smsMessage').value = '';
                updateCharCount();
                return;
            }

            // Get template from loaded data (per country)
            const storeCode = smsTarget?.storeCode || 'hr';
            let text = '';

            if (smsTemplatesData?.templates?.[tpl]?.[storeCode]) {
                text = smsTemplatesData.templates[tpl][storeCode].message;
            } else if (smsTemplates[tpl]) {
                // Fallback to hardcoded templates
                text = smsTemplates[tpl].text;
            } else {
                return;
            }

            // Replace variables
            const firstName = smsTarget?.firstName || smsTarget?.customerName?.split(' ')[0] || 'Stranka';
            const product = smsTarget?.cartContents?.[0]?.name || 'vaš izdelek';
            const sym = {EUR:'€',CZK:'Kč',PLN:'zł',HUF:'Ft'}[smsTarget?.currency] || '€';
            const price = sym + (smsTarget?.cartValue?.toFixed(2) || '0.00');
            const storeUrl = `noriks.com/${storeCode}`;
            const couponUrl = `noriks.com/${storeCode}/checkout/?coupon=SMS20`;

            text = text.replace(/{ime}/g, firstName)
                       .replace(/{produkt}/g, product)
                       .replace(/{cena}/g, price)
                       .replace(/{link_coupon}/g, couponUrl)
                       .replace(/{link}/g, storeUrl)
                       .replace(/{shop_link}/g, storeUrl);

            document.getElementById('smsMessage').value = text;
            updateCharCount();
            console.log('[SMS] Template applied:', tpl, 'for store:', storeCode);
        }

        function updateCharCount() {
            const msg = document.getElementById('smsMessage').value;
            const len = msg.length;
            const counter = document.getElementById('smsCharCount');
            counter.textContent = `${len} / 160 znakov`;
            counter.className = 'char-count' + (len > 160 ? ' error' : len > 140 ? ' warning' : '');

            // Show preview
            const preview = document.getElementById('smsPreview');
            if (msg.trim()) {
                preview.style.display = 'block';
                document.getElementById('smsPreviewText').textContent = msg;
            } else {
                preview.style.display = 'none';
            }

            // Enable/disable button
            document.getElementById('sendSmsBtn').disabled = !msg.trim();
        }

        async function queueSms() {
            const msg = document.getElementById('smsMessage').value.trim();
            if (!msg || !smsTarget) return;

            try {
                // Add to API queue (NOT send!)
                const res = await fetch('api.php?action=sms-add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        phone: smsTarget.phone,
                        customerName: smsTarget.customerName,
                        storeCode: smsTarget.storeCode,
                        message: msg,
                        cartId: smsTarget.id,
                        addedBy: user?.username || 'unknown'
                    })
                });

                const result = await res.json();
                if (result.success) {
                    closeModal('smsModal');
                    const phoneInfo = result.formattedPhone ? ` (${result.formattedPhone})` : '';
                    showToast(`📱 SMS dodan v čakalno vrsto${phoneInfo}. Dejan bo poslal ročno.`, false, 'info');
                    await loadSmsData(); // Refresh from API
                    updateStats();
                } else {
                    showToast('❌ Napaka: ' + (result.error || 'Neznana napaka'), true);
                    alert('⚠️ SMS ni bil dodan!\n\n' + (result.error || 'Neznana napaka'));
                }
            } catch (e) {
                console.error('SMS queue error:', e);
                showToast('Napaka pri dodajanju SMS', true);
            }
        }

        // =============================================
        // SMS AUTOMATION FUNCTIONS
        // =============================================

        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        let smsAutomations = [];
        let smsAutomationsFiltered = [];
        let smsTemplatesCache = {};
        let automationCheckInterval = null;
        const AUTOMATION_CHECK_INTERVAL_MS = 30 * 60 * 1000; // 30 minutes

        // Auto-run automations check if needed
        async function checkAndRunAutomations(forceRun = false) {
            const lastRunKey = 'smsAutomationLastRun';
            const lastRun = parseInt(localStorage.getItem(lastRunKey) || '0');
            const now = Date.now();
            const timeSinceLastRun = now - lastRun;

            // Update last run display
            const lastRunSpan = document.getElementById('lastAutomationRun');
            if (lastRunSpan && lastRun > 0) {
                const minutes = Math.floor(timeSinceLastRun / 60000);
                if (minutes < 1) {
                    lastRunSpan.textContent = '| Zadnje preverjanje: ravnokar';
                } else if (minutes < 60) {
                    lastRunSpan.textContent = `| Zadnje preverjanje: pred ${minutes} min`;
                } else {
                    lastRunSpan.textContent = '| Zadnje preverjanje: ' + new Date(lastRun).toLocaleTimeString('sl-SI');
                }
            }

            // Check if we should auto-run
            const hasEnabledAutomations = smsAutomations.some(a => a.enabled);
            const shouldAutoRun = hasEnabledAutomations && (forceRun || timeSinceLastRun > AUTOMATION_CHECK_INTERVAL_MS);

            if (shouldAutoRun) {
                console.log('Auto-running SMS automation check...');
                try {
                    const res = await fetch('api.php?action=run-sms-automations');
                    const result = await res.json();
                    localStorage.setItem(lastRunKey, now.toString());

                    if (result.success && result.totalQueued > 0) {
                        showToast(`🤖 Auto-check: ${result.totalQueued} SMS dodano v vrsto`);
                        loadSmsAutomations(); // Refresh to show updated counts
                    }

                    // Update last run display
                    if (lastRunSpan) {
                        lastRunSpan.textContent = '| Zadnje preverjanje: ravnokar';
                    }
                } catch (err) {
                    console.error('Auto-run error:', err);
                }
            }
        }

        // Start/stop periodic automation checking
        function startAutomationChecking() {
            if (automationCheckInterval) return;
            automationCheckInterval = setInterval(() => {
                if (document.getElementById('smsAutomationContent')?.style.display !== 'none') {
                    checkAndRunAutomations();
                }
            }, AUTOMATION_CHECK_INTERVAL_MS);
        }

        function stopAutomationChecking() {
            if (automationCheckInterval) {
                clearInterval(automationCheckInterval);
                automationCheckInterval = null;
            }
        }

        // ========== SMS TEMPLATE MANAGEMENT ==========
        let allTemplates = {};
        let currentTemplateFilter = 'all';

        async function loadAllTemplates() {
            const container = document.getElementById('templatesListContainer');
            if (!container) return;

            container.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);"><i class="fas fa-spinner fa-spin"></i> Nalagam predloge...</div>';

            try {
                const res = await fetch('api.php?action=all-sms-templates');
                allTemplates = await res.json();
                renderTemplatesList();
            } catch (err) {
                console.error('Error loading templates:', err);
                container.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);">Napaka pri nalaganju predlog</div>';
            }
        }

        function renderTemplatesList() {
            const container = document.getElementById('templatesListContainer');
            if (!container) return;

            const templates = allTemplates.templates || [];
            const filtered = currentTemplateFilter === 'all'
                ? templates
                : templates.filter(t => t.category === currentTemplateFilter);

            if (filtered.length === 0) {
                container.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);"><i class="fas fa-inbox" style="font-size:32px;margin-bottom:10px;display:block;opacity:0.3;"></i>Ni predlog v tej kategoriji</div>';
                return;
            }

            const categoryIcons = { abandoned: '🛒', winback: '💙', custom: '✏️' };
            const countryFlags = { hr: '🇭🇷', cz: '🇨🇿', pl: '🇵🇱', sk: '🇸🇰', hu: '🇭🇺', gr: '🇬🇷', it: '🇮🇹' };

            container.innerHTML = filtered.map(t => {
                const translations = Object.keys(t.messages || {}).map(c => countryFlags[c] || c).join(' ');
                return `
                    <div style="background:var(--content-bg);border-radius:8px;padding:12px 16px;margin-bottom:8px;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                                <span>${categoryIcons[t.category] || '📝'}</span>
                                <strong>${escapeHtml(t.name)}</strong>
                                <code style="font-size:11px;color:var(--text-muted);">${escapeHtml(t.id)}</code>
                            </div>
                            <div style="font-size:12px;color:var(--text-muted);">
                                Prevodi: ${translations || 'Ni prevodov'}
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button class="btn btn-sm btn-secondary" onclick="editTemplate('${escapeHtml(t.id)}')" title="Uredi">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteTemplate('${escapeHtml(t.id)}')" title="Izbriši">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function filterTemplatesByCategory(category, btn) {
            currentTemplateFilter = category;
            document.querySelectorAll('[data-category]').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            renderTemplatesList();
        }

        function showAddTemplateModal() {
            document.getElementById('templateModalTitle').innerHTML = '<i class="fas fa-plus" style="margin-right: 8px; color: var(--primary);"></i>Nova SMS predloga';
            document.getElementById('templateId').value = '';
            document.getElementById('templateKey').value = '';
            document.getElementById('templateKey').disabled = false;
            document.getElementById('templateName').value = '';
            document.getElementById('templateCategory').value = 'abandoned';
            ['hr', 'cz', 'pl', 'sk', 'hu', 'gr', 'it'].forEach(c => {
                document.getElementById('templateMsg_' + c).value = '';
            });
            document.getElementById('templateModalBg').style.display = 'flex';
        }

        function editTemplate(id) {
            const templates = allTemplates.templates || [];
            const template = templates.find(t => t.id === id);
            if (!template) return;

            document.getElementById('templateModalTitle').innerHTML = '<i class="fas fa-edit" style="margin-right: 8px; color: var(--primary);"></i>Uredi predlogo';
            document.getElementById('templateId').value = template.id;
            document.getElementById('templateKey').value = template.id;
            document.getElementById('templateKey').disabled = true;
            document.getElementById('templateName').value = template.name || '';
            document.getElementById('templateCategory').value = template.category || 'custom';

            ['hr', 'cz', 'pl', 'sk', 'hu', 'gr', 'it'].forEach(c => {
                const msg = template.messages?.[c] || '';
                document.getElementById('templateMsg_' + c).value = msg;
            });

            document.getElementById('templateModalBg').style.display = 'flex';
        }

        function closeTemplateModal() {
            document.getElementById('templateModalBg').style.display = 'none';
        }

        async function saveTemplate() {
            const id = document.getElementById('templateKey').value.trim();
            const name = document.getElementById('templateName').value.trim();
            const category = document.getElementById('templateCategory').value;

            if (!id || !name) {
                showToast('Izpolni ID in ime predloge', true);
                return;
            }

            const messages = {};
            ['hr', 'cz', 'pl', 'sk', 'hu', 'gr', 'it'].forEach(c => {
                const msg = document.getElementById('templateMsg_' + c).value.trim();
                if (msg) messages[c] = msg;
            });

            if (Object.keys(messages).length === 0) {
                showToast('Dodaj vsaj en prevod', true);
                return;
            }

            try {
                const res = await fetch('api.php?action=save-sms-template', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, name, category, messages })
                });
                const result = await res.json();

                if (result.success) {
                    showToast('Predloga shranjena!');
                    closeTemplateModal();
                    loadAllTemplates();
                } else {
                    showToast(result.error || 'Napaka pri shranjevanju', true);
                }
            } catch (err) {
                showToast('Napaka pri shranjevanju', true);
            }
        }

        async function deleteTemplate(id) {
            if (!confirm('Si prepričan, da želiš izbrisati to predlogo?')) return;

            try {
                const res = await fetch('api.php?action=delete-sms-template', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const result = await res.json();

                if (result.success) {
                    showToast('Predloga izbrisana');
                    loadAllTemplates();
                } else {
                    showToast(result.error || 'Napaka pri brisanju', true);
                }
            } catch (err) {
                showToast('Napaka pri brisanju', true);
            }
        }

        async function loadSmsAutomations() {
            const tbody = document.getElementById('automationsTableBody');
            if (!tbody) return;

            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 40px;"><div class="spinner"></div></td></tr>';

            try {
                const res = await fetch('api.php?action=sms-automations');
                smsAutomations = await res.json();

                if (!Array.isArray(smsAutomations) || smsAutomations.length === 0) {
                    tbody.innerHTML = `
                        <tr><td colspan="8" style="text-align:center; padding: 40px; color: var(--text-muted);">
                            <i class="fas fa-robot" style="font-size: 48px; margin-bottom: 15px; display: block; opacity: 0.3;"></i>
                            Ni še nobene avtomatizacije.<br>
                            <button class="btn btn-primary" style="margin-top: 15px;" onclick="showAddAutomationModal()">
                                <i class="fas fa-plus"></i> Dodaj prvo avtomatizacijo
                            </button>
                        </td></tr>
                    `;
                    return;
                }

                renderAutomationsTable();

                // Auto-run check if enabled automations exist and enough time passed
                checkAndRunAutomations();

                // Start periodic checking
                startAutomationChecking();

            } catch (err) {
                console.error('Error loading automations:', err);
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; color: var(--danger);">Napaka pri nalaganju</td></tr>';
            }
        }

        function filterAutomations() {
            const countryFilter = document.getElementById('automationCountryFilter')?.value || '';
            const typeFilter = document.getElementById('automationTypeFilter')?.value || '';
            const statusFilter = document.getElementById('automationStatusFilter')?.value || '';

            smsAutomationsFiltered = smsAutomations.filter(a => {
                if (countryFilter && a.store !== countryFilter) return false;
                if (typeFilter && a.type !== typeFilter) return false;
                if (statusFilter === 'active' && !a.enabled) return false;
                if (statusFilter === 'paused' && a.enabled) return false;
                return true;
            });

            renderAutomationsTable();
        }

        function renderAutomationsTable() {
            const tbody = document.getElementById('automationsTableBody');
            if (!tbody) return;

            const data = smsAutomationsFiltered.length > 0 ? smsAutomationsFiltered : smsAutomations;

            // Check if filtered resulted in empty when filters are applied
            const countryFilter = document.getElementById('automationCountryFilter')?.value || '';
            const typeFilter = document.getElementById('automationTypeFilter')?.value || '';
            const statusFilter = document.getElementById('automationStatusFilter')?.value || '';
            const hasFilters = countryFilter || typeFilter || statusFilter;

            if (hasFilters && smsAutomationsFiltered.length === 0) {
                tbody.innerHTML = `
                    <tr><td colspan="8" style="text-align:center; padding: 40px; color: var(--text-muted);">
                        <i class="fas fa-filter" style="font-size: 32px; margin-bottom: 10px; display: block; opacity: 0.3;"></i>
                        Nobena avtomatizacija ne ustreza filtrom
                    </td></tr>
                `;
                return;
            }

            const storeNames = {
                hr: '🇭🇷 HR', cz: '🇨🇿 CZ', pl: '🇵🇱 PL',
                sk: '🇸🇰 SK', hu: '🇭🇺 HU', gr: '🇬🇷 GR', it: '🇮🇹 IT'
            };

            const typeNames = {
                abandoned_cart: '🛒 Zapuščena košarica'
            };

            tbody.innerHTML = data.map(a => `
                <tr data-automation-id="${a.id}">
                    <td>
                        <label class="toggle-switch" title="${a.enabled ? 'Klikni za izklop' : 'Klikni za vklop'}">
                            <input type="checkbox" ${a.enabled ? 'checked' : ''} onchange="toggleAutomation('${a.id}', this)">
                            <span class="toggle-slider"></span>
                        </label>
                    </td>
                    <td><strong>${escapeHtml(a.name)}</strong></td>
                    <td>${storeNames[a.store] || a.store}</td>
                    <td>${typeNames[a.type] || a.type}</td>
                    <td>
                        <code style="cursor: pointer;" onclick="previewTemplate('${a.store}', '${escapeHtml(a.template)}')" title="Klikni za predogled">
                            ${escapeHtml(a.template)}
                        </code>
                    </td>
                    <td>${a.delay_hours}h <span style="color:var(--text-muted)">/ ${a.max_days || 7}d</span></td>
                    <td>${a.queued_count || 0}</td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick="editAutomation('${a.id}')" title="Uredi">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="resetAutomationQueue('${a.id}')" title="Reset evidenco (omogoči ponovno pošiljanje)">
                            <i class="fas fa-redo"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteAutomation('${a.id}')" title="Izbriši">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        async function previewTemplate(store, templateId) {
            // Load templates if not cached
            if (!smsTemplatesCache[store]) {
                try {
                    const res = await fetch(`api.php?action=sms-templates&store=${store}`);
                    smsTemplatesCache[store] = await res.json();
                } catch (err) {
                    showToast('Napaka pri nalaganju predloge', true);
                    return;
                }
            }

            const templates = smsTemplatesCache[store] || [];
            const template = templates.find(t => (t.id || t.name) === templateId);

            if (template && template.message) {
                // Show in a simple alert for now, or create a preview modal
                const previewHtml = `
                    <div style="padding: 20px;">
                        <h4 style="margin: 0 0 12px; font-size: 14px; color: var(--text-muted);">
                            <i class="fas fa-envelope"></i> Predloga: ${escapeHtml(template.name)}
                        </h4>
                        <div style="padding: 16px; background: var(--content-bg); border-radius: 8px; border: 1px solid var(--card-border); font-size: 13px; line-height: 1.6; white-space: pre-wrap;">
                            ${escapeHtml(template.message)}
                        </div>
                        <div style="margin-top: 12px; font-size: 11px; color: var(--text-muted);">
                            <i class="fas fa-info-circle"></i> Spremenljivke: {ime}, {link}, {znesek} bodo zamenjane ob pošiljanju
                        </div>
                    </div>
                `;
                showTemplatePreviewModal(previewHtml);
            } else {
                showToast('Predloga ni najdena', true);
            }
        }

        function showTemplatePreviewModal(content) {
            // Create modal if it doesn't exist
            let modal = document.getElementById('templatePreviewModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'templatePreviewModal';
                modal.className = 'modal-bg';
                modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.6);display:none;align-items:center;justify-content:center;z-index:300;padding:20px;';
                modal.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };
                modal.innerHTML = `
                    <div class="modal" style="max-width:500px;background:var(--card-bg);border-radius:var(--radius-xl);box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);overflow:hidden;">
                        <div class="modal-header" style="padding: 16px 20px; border-bottom: 1px solid var(--card-border);">
                            <h3 class="modal-title" style="font-size: 15px; margin: 0;">
                                <i class="fas fa-eye" style="margin-right: 8px; color: var(--primary);"></i>Predogled predloge
                            </h3>
                            <button class="modal-close" onclick="document.getElementById('templatePreviewModal').style.display='none'">&times;</button>
                        </div>
                        <div id="templatePreviewContent"></div>
                        <div class="modal-footer" style="padding: 12px 20px; border-top: 1px solid var(--card-border);">
                            <button class="btn btn-secondary" onclick="document.getElementById('templatePreviewModal').style.display='none'">Zapri</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            document.getElementById('templatePreviewContent').innerHTML = content;
            modal.style.display = 'flex';
        }

        function showAddAutomationModal() {
            document.getElementById('automationModalTitle').textContent = 'Nova SMS avtomatizacija';
            document.getElementById('automationForm').reset();
            document.getElementById('automationId').value = '';
            document.getElementById('automationEnabled').checked = true;
            document.getElementById('automationTemplate').innerHTML = '<option value="">-- Najprej izberi trgovino --</option>';
            // Hide template preview
            const previewDiv = document.getElementById('modalTemplatePreview');
            if (previewDiv) previewDiv.style.display = 'none';
            document.getElementById('automationModalBg').style.display = 'flex';
        }

        function closeAutomationModal() {
            document.getElementById('automationModalBg').style.display = 'none';
            // Hide template preview on close
            const previewDiv = document.getElementById('modalTemplatePreview');
            if (previewDiv) previewDiv.style.display = 'none';
        }

        async function loadTemplatesForStore(store) {
            const templateSelect = document.getElementById('automationTemplate');
            const previewDiv = document.getElementById('modalTemplatePreview');
            templateSelect.innerHTML = '<option value="">Nalagam predloge...</option>';
            if (previewDiv) previewDiv.style.display = 'none';

            try {
                const res = await fetch(`api.php?action=sms-templates&store=${store}`);
                const templates = await res.json();
                smsTemplatesCache[store] = templates;

                if (!templates || templates.length === 0) {
                    templateSelect.innerHTML = '<option value="">-- Ni predlog za to trgovino --</option>';
                    return;
                }

                templateSelect.innerHTML = '<option value="">-- Izberi predlogo --</option>' +
                    templates.map(t => `<option value="${escapeHtml(t.id || t.name)}">${escapeHtml(t.name)}</option>`).join('');

            } catch (err) {
                console.error('Error loading templates:', err);
                templateSelect.innerHTML = '<option value="">-- Napaka pri nalaganju --</option>';
            }
        }

        function showTemplatePreviewInModal() {
            const store = document.getElementById('automationStore').value;
            const templateId = document.getElementById('automationTemplate').value;
            const previewDiv = document.getElementById('modalTemplatePreview');
            const previewText = document.getElementById('modalTemplateText');

            if (!templateId || !store || !previewDiv) {
                if (previewDiv) previewDiv.style.display = 'none';
                return;
            }

            const templates = smsTemplatesCache[store] || [];
            const template = templates.find(t => (t.id || t.name) === templateId);

            if (template && template.message) {
                previewText.textContent = template.message;
                previewDiv.style.display = 'block';
            } else {
                previewDiv.style.display = 'none';
            }
        }

        // Listen for store change
        document.addEventListener('DOMContentLoaded', () => {
            const storeSelect = document.getElementById('automationStore');
            if (storeSelect) {
                storeSelect.addEventListener('change', (e) => {
                    if (e.target.value) {
                        loadTemplatesForStore(e.target.value);
                    }
                    // Hide preview when store changes
                    const previewDiv = document.getElementById('modalTemplatePreview');
                    if (previewDiv) previewDiv.style.display = 'none';
                });
            }
        });

        async function saveAutomation() {
            const id = document.getElementById('automationId').value;
            const data = {
                id: id || null,
                name: document.getElementById('automationName').value.trim(),
                store: document.getElementById('automationStore').value,
                type: document.getElementById('automationType').value,
                template: document.getElementById('automationTemplate').value,
                delay_hours: parseInt(document.getElementById('automationDelay').value) || 2,
                max_days: parseInt(document.getElementById('automationMaxDays').value) || 7,
                enabled: document.getElementById('automationEnabled').checked
            };

            if (!data.name || !data.store || !data.template) {
                showToast('Prosim izpolni vsa polja', true);
                return;
            }

            try {
                const res = await fetch('api.php?action=save-sms-automation', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();

                if (result.success) {
                    showToast(id ? 'Avtomatizacija posodobljena!' : 'Avtomatizacija dodana!');
                    closeAutomationModal();
                    loadSmsAutomations();
                } else {
                    showToast(result.error || 'Napaka pri shranjevanju', true);
                }
            } catch (err) {
                console.error('Error saving automation:', err);
                showToast('Napaka pri shranjevanju', true);
            }
        }

        function editAutomation(id) {
            const automation = smsAutomations.find(a => a.id === id);
            if (!automation) return;

            document.getElementById('automationModalTitle').textContent = 'Uredi avtomatizacijo';
            document.getElementById('automationId').value = automation.id;
            document.getElementById('automationName').value = automation.name;
            document.getElementById('automationStore').value = automation.store;
            document.getElementById('automationType').value = automation.type;
            document.getElementById('automationDelay').value = automation.delay_hours;
            document.getElementById('automationMaxDays').value = automation.max_days || 7;
            document.getElementById('automationEnabled').checked = automation.enabled;

            // Load templates then select the right one and show preview
            loadTemplatesForStore(automation.store).then(() => {
                document.getElementById('automationTemplate').value = automation.template;
                showTemplatePreviewInModal(); // Show preview after selecting template
            });

            document.getElementById('automationModalBg').style.display = 'flex';
        }

        async function toggleAutomation(id, checkbox) {
            const automation = smsAutomations.find(a => a.id === id);
            if (!automation) return;

            const newState = checkbox ? checkbox.checked : !automation.enabled;

            // Disable checkbox while saving
            if (checkbox) checkbox.disabled = true;

            try {
                automation.enabled = newState;
                await fetch('api.php?action=save-sms-automation', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(automation)
                });
                showToast(automation.enabled ? '✅ Avtomatizacija VKLOPLJENA' : '⏸️ Avtomatizacija IZKLOPLJENA');
                // Don't reload the whole table, just update the local state
                if (checkbox) checkbox.disabled = false;
            } catch (err) {
                showToast('Napaka pri posodabljanju', true);
                // Revert checkbox state on error
                if (checkbox) {
                    checkbox.checked = !newState;
                    checkbox.disabled = false;
                }
                automation.enabled = !newState;
            }
        }

        async function deleteAutomation(id) {
            if (!confirm('Si prepričan, da želiš izbrisati to avtomatizacijo?')) return;

            try {
                const res = await fetch('api.php?action=delete-sms-automation', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const result = await res.json();

                if (result.success) {
                    showToast('Avtomatizacija izbrisana');
                    loadSmsAutomations();
                } else {
                    showToast(result.error || 'Napaka pri brisanju', true);
                }
            } catch (err) {
                showToast('Napaka pri brisanju', true);
            }
        }

        async function resetAutomationQueue(id) {
            if (!confirm('Reset evidenco? To bo omogočilo ponovno pošiljanje SMS za vse košarice te avtomatizacije.')) return;

            try {
                const res = await fetch('api.php?action=reset-automation-queue', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ automation_id: id })
                });
                const result = await res.json();

                if (result.success) {
                    showToast(`Evidenca resetirana (${result.reset_count || 0} košaric)`);
                    loadSmsAutomations();
                } else {
                    showToast(result.error || 'Napaka pri resetiranju', true);
                }
            } catch (err) {
                showToast('Napaka pri resetiranju', true);
            }
        }

        // Run SMS automations - check conditions and add to queue
        async function runSmsAutomations() {
            const btn = document.getElementById('runAutomationsBtn');
            const originalHtml = btn.innerHTML;

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preverjam...';
            btn.disabled = true;

            try {
                const res = await fetch('api.php?action=run-sms-automations');
                const result = await res.json();

                if (result.success) {
                    const totalQueued = result.totalQueued || 0;
                    if (totalQueued > 0) {
                        showToast(`✅ Dodano ${totalQueued} SMS-ov v čakalno vrsto!`);
                    } else {
                        showToast('ℹ️ Ni novih SMS za dodati v vrsto');
                    }

                    // Save last run time
                    localStorage.setItem('smsAutomationLastRun', Date.now().toString());

                    // Update last run time display
                    const lastRunSpan = document.getElementById('lastAutomationRun');
                    if (lastRunSpan) {
                        lastRunSpan.textContent = '| Zadnje preverjanje: ravnokar';
                    }

                    // Reload automations to show updated queued counts (without triggering auto-run again)
                    const tbody = document.getElementById('automationsTableBody');
                    if (tbody) {
                        const res2 = await fetch('api.php?action=sms-automations');
                        smsAutomations = await res2.json();
                        if (Array.isArray(smsAutomations)) {
                            renderAutomationsTable();
                        }
                    }
                } else {
                    showToast(result.error || 'Napaka pri zagonu avtomatizacij', true);
                }
            } catch (err) {
                console.error('Run automations error:', err);
                showToast('Napaka pri zagonu avtomatizacij', true);
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        }

        // =============================================
        // SMS DASHBOARD FUNCTIONS
        // =============================================

        // SMS Dashboard - Load Queue from API
        async function loadSmsDashboardQueue() {
            const container = document.getElementById('smsDashboardQueue');
            if (!container) return;

            container.innerHTML = '<div class="loading"><div class="spinner"></div>Loading...</div>';

            try {
                const res = await fetch('api.php?action=sms-queue&status=queued');
                const queue = await res.json();

                if (!Array.isArray(queue) || queue.length === 0) {
                    container.innerHTML = `
                        <div class="empty" style="padding:20px;">
                            <i class="fas fa-inbox"></i>
                            <p>Čakalna vrsta je prazna</p>
                            <p style="font-size:12px;color:var(--text-muted);">SMS-i se dodajo ko pošlješ SMS stranki iz customer modal-a</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = `
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Prejemnik</th>
                                    <th>Sporočilo</th>
                                    <th>Država</th>
                                    <th>Akcije</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${queue.map(sms => {
                                    const store = stores.find(s => s.code === sms.storeCode);
                                    return `
                                        <tr>
                                            <td style="font-size:12px;">${new Date(sms.date).toLocaleString('sl-SI')}</td>
                                            <td>
                                                <div style="font-weight:500;">${esc(sms.customerName || 'Unknown')}</div>
                                                <div style="font-size:12px;color:var(--text-muted);">${sms.recipient || ''}</div>
                                            </td>
                                            <td style="max-width:300px;">
                                                <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${esc(sms.message || '')}">${esc(sms.message || '')}</div>
                                            </td>
                                            <td>${store?.flag || '🌍'} ${(sms.storeCode || '').toUpperCase()}</td>
                                            <td style="white-space:nowrap;">
                                                <button class="action-btn order" onclick="openSmsEditModal('${sms.id}', '${escAttr(sms.recipient || '')}', '${escAttr(sms.message || '')}', '${escAttr(sms.customerName || 'Unknown')}')" title="Pošlji SMS" style="background:var(--accent-green);color:white;">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                                <button class="action-btn" onclick="removeFromQueueApi('${sms.id}')" title="Odstrani">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:16px;padding:12px;background:rgba(34,197,94,0.1);border:1px solid var(--accent-green);border-radius:8px;">
                        <i class="fas fa-info-circle" style="color:var(--accent-green);"></i>
                        <strong style="color:var(--accent-green);">Pošlji SMS:</strong> Klikni zeleni gumb <i class="fas fa-paper-plane"></i> za pošiljanje SMS-a preko MetaKocka.
                    </div>
                `;
            } catch (e) {
                container.innerHTML = `<div class="empty" style="color:var(--accent-red);"><i class="fas fa-exclamation-triangle"></i><p>Napaka: ${e.message}</p><button class="btn btn-save" style="margin-top:12px;" onclick="loadSmsDashboardQueue()"><i class="fas fa-redo"></i> Poskusi znova</button></div>`;
            }
        }

        // Open SMS Edit Modal before sending
        function openSmsEditModal(smsId, phone, message, customerName) {
            document.getElementById('smsEditId').value = smsId;
            document.getElementById('smsEditPhone').value = phone || '';
            document.getElementById('smsEditMessage').value = message || '';
            document.getElementById('smsEditCustomerName').textContent = customerName || 'Unknown';
            document.getElementById('smsEditAvatar').textContent = (customerName || '?')[0].toUpperCase();
            document.getElementById('smsEditModal').classList.add('open');
        }

        // Confirm and send SMS with (potentially edited) phone number
        async function confirmSendSms() {
            const smsId = document.getElementById('smsEditId').value;
            const editedPhone = document.getElementById('smsEditPhone').value.trim();

            if (!editedPhone) {
                showToast('❌ Vnesi telefonsko številko!', true);
                return;
            }

            // Basic validation - at least 7 digits
            const digitsOnly = editedPhone.replace(/[^0-9]/g, '');
            if (digitsOnly.length < 7) {
                showToast('❌ Telefonska številka je prekratka (min. 7 številk)', true);
                return;
            }
            if (digitsOnly.length > 15) {
                showToast('❌ Telefonska številka je predolga (max. 15 številk)', true);
                return;
            }

            closeModal('smsEditModal');
            await sendSmsFromDashboard(smsId, editedPhone);
        }

        // Send SMS from Dashboard Queue
        async function sendSmsFromDashboard(smsId, overridePhone = null) {
            if (!confirm('Ali si prepričan, da želiš poslati ta SMS?\\n\\n⚠️ SMS bo poslan preko MetaKocka API!')) {
                return;
            }

            // Show loading state
            showToast('📤 Pošiljam SMS preko MetaKocka...', false, 'info');

            try {
                const payload = { id: smsId };
                if (overridePhone) {
                    payload.phone = overridePhone;
                }
                const res = await fetch('api.php?action=sms-send', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();

                // Log full response for debugging
                console.log('[SMS] MetaKocka response:', result);

                if (result.success) {
                    let msg = `✅ SMS uspešno poslan na ${result.recipient || 'prejemnika'}!`;
                    if (result.metakockaResponse) {
                        msg += `\n\n📊 MK Response: ${JSON.stringify(result.metakockaResponse)}`;
                    }
                    showToast(msg);
                    // Refresh both queues
                    await loadSmsDashboardQueue();
                    await loadSmsData();
                    renderSmsQueueManagement();
                    updateStats();
                } else {
                    let errorMsg = `❌ Napaka: ${result.error || 'Neznana napaka'}`;
                    if (result.debug) errorMsg += ` [${result.debug}]`;
                    if (result.httpCode) errorMsg += ` (HTTP ${result.httpCode})`;
                    if (result.metakockaResponse) {
                        console.error('[SMS] MK Error Response:', result.metakockaResponse);
                        errorMsg += `\n\n📊 MK: ${JSON.stringify(result.metakockaResponse)}`;
                    }
                    showToast(errorMsg, true);
                    alert('🚨 SMS NAPAKA!\n\n' + errorMsg);
                }
            } catch (e) {
                console.error('[SMS] Send failed:', e);
                showToast('❌ Napaka pri pošiljanju: ' + e.message, true);
                alert('🚨 SMS NAPAKA!\n\n' + e.message);
            }
        }

        async function removeFromQueueApi(smsId) {
            try {
                await fetch('api.php?action=sms-remove', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: smsId})
                });
                loadSmsDashboardQueue();
                renderSmsQueueManagement();
                showToast('SMS odstranjen iz čakalne vrste');
            } catch (e) {
                showToast('Napaka pri odstranjevanju', true);
            }
        }

        // ========== MANUAL SMS SEND ==========
        function updateManualSmsCharCount() {
            const msg = document.getElementById('manualSmsMessage').value;
            const len = msg.length;
            const counter = document.getElementById('manualSmsCharCount');
            const smsCount = Math.ceil(len / 160) || 1;
            counter.textContent = `${len} / 160 znakov (${smsCount} SMS)`;
            counter.className = 'char-count' + (len > 160 ? ' warning' : '');
        }

        async function sendManualSms() {
            const phone = document.getElementById('manualSmsPhone').value.trim();
            const country = document.getElementById('manualSmsCountry').value;
            const message = document.getElementById('manualSmsMessage').value.trim();
            const statusEl = document.getElementById('manualSmsStatus');

            // Basic validation
            if (!phone) {
                showToast('❌ Vnesi telefonsko številko!', true);
                return;
            }

            const digitsOnly = phone.replace(/[^0-9]/g, '');
            if (digitsOnly.length < 7) {
                showToast('❌ Telefonska številka je prekratka (min. 7 številk)', true);
                return;
            }

            if (!message) {
                showToast('❌ Vnesi sporočilo!', true);
                return;
            }

            if (!confirm(`📱 Pošlji SMS?\n\nŠtevilka: ${phone}\nDržava: ${country.toUpperCase()}\nSporočilo: ${message.substring(0, 50)}...`)) {
                return;
            }

            statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Pošiljam...';
            statusEl.style.color = 'var(--text-muted)';

            try {
                const res = await fetch('api.php?action=sms-send-direct', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        phone: phone,
                        storeCode: country,
                        message: message
                    })
                });

                const result = await res.json();
                console.log('[Manual SMS] Response:', result);

                if (result.success) {
                    statusEl.innerHTML = `<i class="fas fa-check-circle"></i> Poslano na ${result.recipient || phone}`;
                    statusEl.style.color = 'var(--accent-green)';
                    showToast(`✅ SMS uspešno poslan na ${result.recipient || phone}!`);

                    // Clear form
                    document.getElementById('manualSmsPhone').value = '';
                    document.getElementById('manualSmsMessage').value = '';
                    updateManualSmsCharCount();

                    // Refresh history
                    await loadSmsData();
                    renderSmsTable();
                } else {
                    statusEl.innerHTML = `<i class="fas fa-times-circle"></i> ${result.error || 'Napaka'}`;
                    statusEl.style.color = 'var(--accent-red)';
                    showToast('❌ ' + (result.error || 'Napaka pri pošiljanju'), true);

                    if (result.metakockaResponse) {
                        console.error('[Manual SMS] MK Response:', result.metakockaResponse);
                    }
                }
            } catch (e) {
                console.error('[Manual SMS] Error:', e);
                statusEl.innerHTML = `<i class="fas fa-times-circle"></i> ${e.message}`;
                statusEl.style.color = 'var(--accent-red)';
                showToast('❌ Napaka: ' + e.message, true);
            }
        }

        // ========== TEST SMS FROM TEMPLATE ==========
        // Note: smsTemplatesCache already defined above

        async function loadTestSmsTemplates() {
            try {
                const res = await fetch('api.php?action=all-sms-templates');
                return await res.json();
            } catch (e) {
                console.error('[Test SMS] Failed to load templates:', e);
                return { templates: {} };
            }
        }

        async function loadTestTemplatePreview() {
            const templateKey = document.getElementById('testSmsTemplate').value;
            const country = document.getElementById('testSmsCountry').value;
            const name = document.getElementById('testSmsName').value || 'Kupac';
            const product = document.getElementById('testSmsProduct').value || 'proizvod';
            const preview = document.getElementById('testSmsPreview');
            const charCount = document.getElementById('testSmsCharCount');

            if (!templateKey) {
                preview.innerHTML = '<span style="color:var(--text-muted);font-style:italic;">Izberi predlogo za predogled...</span>';
                charCount.textContent = '0 / 160 znakov';
                return;
            }

            const data = await loadTestSmsTemplates();
            console.log('[TestSMS] API data:', data);
            // API returns array: templates: [{id, name, messages: {hr: "...", cz: "..."}}]
            const templateList = data.templates || [];
            console.log('[TestSMS] Looking for templateKey:', templateKey, 'country:', country);
            console.log('[TestSMS] templateList:', templateList.map(t => t.id));
            const template = templateList.find(t => t.id === templateKey);
            console.log('[TestSMS] Found template:', template);
            const message = template?.messages?.[country];
            console.log('[TestSMS] Message:', message);

            if (!message) {
                preview.innerHTML = `<span style="color:var(--accent-red);">Predloga ni najdena (key=${templateKey}, country=${country}, templates=${templateList.length})</span>`;
                return;
            }

            // Build the links - always checkout, not cart
            const checkoutLink = `https://noriks.com/${country}/checkout/`;
            const checkoutLinkCoupon = `https://noriks.com/${country}/checkout/?coupon=SMS20`;
            const shopLink = `https://noriks.com/${country}/`;

            // Replace variables
            let finalMessage = message
                .replace(/{ime}/g, name)
                .replace(/{produkt}/g, product)
                .replace(/{link_coupon}/g, checkoutLinkCoupon)
                .replace(/{link}/g, checkoutLink)
                .replace(/{shop_link}/g, shopLink)
                .replace(/{cena}/g, '29.99');

            preview.textContent = finalMessage;

            const len = finalMessage.length;
            const smsCount = Math.ceil(len / 160) || 1;
            charCount.textContent = `${len} / 160 znakov (${smsCount} SMS)`;
            charCount.className = 'char-count' + (len > 160 ? ' warning' : '');
        }

        async function sendTestSms() {
            const phone = document.getElementById('testSmsPhone').value.trim();
            const phoneCountry = document.getElementById('testSmsPhoneCountry').value; // Za formatiranje telefona
            const templateKey = document.getElementById('testSmsTemplate').value;
            const templateCountry = document.getElementById('testSmsCountry').value; // Za jezik sporočila
            const name = document.getElementById('testSmsName').value || 'Kupac';
            const product = document.getElementById('testSmsProduct').value || 'proizvod';
            const statusEl = document.getElementById('testSmsStatus');

            if (!phone) {
                showToast('❌ Vnesi svojo telefonsko številko!', true);
                return;
            }

            if (!templateKey) {
                showToast('❌ Izberi predlogo!', true);
                return;
            }

            // Get the message from preview
            const message = document.getElementById('testSmsPreview').textContent;

            if (!message || message.includes('Izberi predlogo')) {
                showToast('❌ Ni veljavnega sporočila za pošiljanje', true);
                return;
            }

            if (!confirm(`🧪 Pošlji TESTNI SMS?\n\nŠtevilka: ${phone} (${phoneCountry.toUpperCase()})\nPredloga: ${templateKey}\nJezik: ${templateCountry.toUpperCase()}\n\n${message.substring(0, 100)}...`)) {
                return;
            }

            statusEl.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Pošiljam...';
            statusEl.style.color = 'var(--text-muted)';

            try {
                const res = await fetch('api.php?action=sms-send-direct', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        phone: phone,
                        storeCode: phoneCountry, // Uporabi državo telefona za formatiranje
                        message: message
                    })
                });

                const result = await res.json();
                console.log('[Test SMS] Response:', result);

                if (result.success) {
                    statusEl.innerHTML = `<i class="fas fa-check-circle"></i> Testni SMS poslan!`;
                    statusEl.style.color = '#a855f7';
                    showToast(`🧪 Testni SMS poslan na ${result.recipient || phone}!`);

                    // Refresh history
                    await loadSmsData();
                    renderSmsTable();
                } else {
                    statusEl.innerHTML = `<i class="fas fa-times-circle"></i> ${result.error || 'Napaka'}`;
                    statusEl.style.color = 'var(--accent-red)';
                    showToast('❌ ' + (result.error || 'Napaka pri pošiljanju'), true);
                }
            } catch (e) {
                console.error('[Test SMS] Error:', e);
                statusEl.innerHTML = `<i class="fas fa-times-circle"></i> ${e.message}`;
                statusEl.style.color = 'var(--accent-red)';
                showToast('❌ Napaka: ' + e.message, true);
            }
        }

        // SMS Dashboard - Load History
        async function renderSmsTable() {
            // Refresh from API first
            await loadSmsData();

            const dateFrom = document.getElementById('smsDateFrom')?.value;
            const dateTo = document.getElementById('smsDateTo')?.value;
            const country = smsHistoryFilters?.country || '';
            const status = smsHistoryFilters?.status || '';

            let filtered = [...smsLog];

            if (dateFrom) filtered = filtered.filter(s => s.date >= dateFrom);
            if (dateTo) filtered = filtered.filter(s => s.date <= dateTo + 'T23:59:59');
            if (country) filtered = filtered.filter(s => s.storeCode === country);
            if (status) filtered = filtered.filter(s => s.status === status);

            filtered.sort((a, b) => new Date(b.date) - new Date(a.date));

            const container = document.getElementById('smsTableContainer');

            if (!filtered.length) {
                container.innerHTML = '<div class="empty"><i class="fas fa-comment-sms"></i><p>Ni SMS sporočil za prikaz</p></div>';
                return;
            }

            container.innerHTML = `
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th>Datum/Čas</th>
                        <th>Prejemnik</th>
                        <th>Sporočilo</th>
                        <th>Država</th>
                        <th>Status</th>
                        <th>Akcije</th>
                    </tr></thead>
                    <tbody>
                        ${filtered.map(s => `
                            <tr>
                                <td style="font-size:12px;">${new Date(s.date).toLocaleString('sl-SI')}</td>
                                <td>
                                    <div style="font-weight:500;">${esc(s.customerName)}</div>
                                    <div style="font-size:12px;color:var(--text-muted);">${s.recipient}</div>
                                </td>
                                <td style="max-width:300px;">
                                    <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${esc(s.message)}">${esc(s.message)}</div>
                                </td>
                                <td>${stores.find(st => st.code === s.storeCode)?.flag || '🌍'}</td>
                                <td><span class="badge ${s.status === 'queued' ? 'called' : s.status === 'sent' ? 'answered' : s.status === 'delivered' ? 'converted' : 'not_interested'}">${s.status}</span></td>
                                <td>
                                    ${s.status === 'queued' ? `<button class="action-btn" onclick="removeSmsFromQueue(${s.id})" title="Odstrani"><i class="fas fa-trash"></i></button>` : ''}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                </div>
            `;
        }

        async function removeSmsFromQueue(id) {
            try {
                const res = await fetch('api.php?action=sms-remove', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: String(id) })
                });
                const result = await res.json();
                if (result.success) {
                    showToast('SMS odstranjen iz čakalne vrste');
                    await renderSmsTable();
                } else {
                    showToast('Napaka pri odstranjevanju', true);
                }
            } catch (e) {
                console.error('Remove SMS error:', e);
                showToast('Napaka pri odstranjevanju', true);
            }
        }

        function exportSmsCsv() {
            if (!smsLog.length) {
                showToast('Ni podatkov za izvoz', true);
                return;
            }

            const headers = ['Datum', 'Prejemnik', 'Telefon', 'Sporočilo', 'Država', 'Status'];
            const rows = smsLog.map(s => [
                new Date(s.date).toLocaleString('sl-SI'),
                s.customerName,
                s.recipient,
                `"${s.message.replace(/"/g, '""')}"`,
                s.storeCode,
                s.status
            ]);

            const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `sms-log-${new Date().toISOString().slice(0,10)}.csv`;
            a.click();
            URL.revokeObjectURL(url);

            showToast('CSV izvožen!');
        }

        // SMS Templates (loaded directly from JSON file)
        let smsTemplatesData = null;

        async function loadSmsTemplates() {
            try {
                const res = await fetch('sms-templates.json');
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                smsTemplatesData = await res.json();
                console.log('[SMS] ✓ Templates loaded from JSON:', Object.keys(smsTemplatesData.templates || {}).length, 'templates');

                // Populate preview country dropdown
                const previewCountry = document.getElementById('previewCountry');
                if (previewCountry && stores) {
                    stores.forEach(s => {
                        previewCountry.innerHTML += `<option value="${s.code}">${s.flag} ${s.name}</option>`;
                    });
                }
            } catch (e) {
                console.error('[SMS] ✗ Failed to load SMS templates:', e);
                smsTemplatesData = { templates: {} };
            }
        }

        // Load templates immediately on script load
        loadSmsTemplates();

        function updateTemplatePreview() {
            const templateKey = document.getElementById('previewTemplate').value;
            const countryCode = document.getElementById('previewCountry').value;
            const previewBox = document.getElementById('templatePreviewBox');

            if (!templateKey || !countryCode || !smsTemplatesData) {
                previewBox.style.display = 'none';
                return;
            }

            const template = smsTemplatesData.templates?.[templateKey]?.[countryCode];
            if (!template) {
                previewBox.style.display = 'none';
                return;
            }

            // Get sample values
            const sampleName = document.getElementById('sampleName').value || 'Kupec';
            const sampleProduct = document.getElementById('sampleProduct').value || 'Izdelek';
            const samplePrice = document.getElementById('samplePrice').value || '€0.00';
            const sampleLink = `noriks.com/${countryCode}/checkout`;

            // Replace variables
            let message = template.message
                .replace(/{ime}/g, sampleName)
                .replace(/{produkt}/g, sampleProduct)
                .replace(/{cena}/g, samplePrice)
                .replace(/{link}/g, sampleLink);

            document.getElementById('previewTemplateName').textContent = template.name;
            document.getElementById('previewMessage').textContent = message;

            // Character count
            const len = message.length;
            const charCount = document.getElementById('previewCharCount');
            charCount.textContent = `${len} / 160 znakov`;
            charCount.className = 'char-count' + (len > 160 ? ' error' : len > 140 ? ' warning' : '');

            previewBox.style.display = 'block';
        }

        function copyTemplateToClipboard() {
            const message = document.getElementById('previewMessage').textContent;
            navigator.clipboard.writeText(message).then(() => {
                showToast('📋 Sporočilo kopirano!', false, 'info');
            });
        }

        function getTemplateOptions() {
            if (!smsTemplatesData?.templates) {
                return `
                    <option value="abandoned_cart">🛒 Opuščena košarica</option>
                    <option value="winback">💙 Povratek kupca</option>
                    <option value="last_chance">⏰ Zadnja prilika</option>
                `;
            }

            const templateNames = {
                'abandoned_cart': '🛒 Opuščena košarica',
                'winback': '💙 Povratek kupca',
                'last_chance': '⏰ Zadnja prilika'
            };

            return Object.keys(smsTemplatesData.templates).map(key =>
                `<option value="${key}">${templateNames[key] || key}</option>`
            ).join('');
        }

        // SMS Automation Settings
        function renderAutomationRules() {
            const container = document.getElementById('automationRules');

            const countryOptions = stores.map(s =>
                `<option value="${s.code}">${s.flag} ${s.name}</option>`
            ).join('');

            container.innerHTML = smsAutomation.map((rule, i) => `
                <div class="order-item" style="margin-bottom:16px;flex-wrap:wrap;">
                    <div class="order-item-info" style="min-width:200px;flex:2;">
                        <input type="text" class="form-input" value="${esc(rule.name)}"
                               onchange="updateRule(${i}, 'name', this.value)"
                               placeholder="Ime pravila" style="margin-bottom:8px;">
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <select class="form-select" style="flex:1;min-width:140px;"
                                    onchange="updateRule(${i}, 'template', this.value)">
                                ${getTemplateOptions().replace(`value="${rule.template}"`, `value="${rule.template}" selected`)}
                            </select>
                            <select class="form-select" style="flex:1;min-width:100px;"
                                    onchange="updateRule(${i}, 'country', this.value)">
                                <option value="all" ${rule.country === 'all' ? 'selected' : ''}>🌍 Vse države</option>
                                ${countryOptions.replace(`value="${rule.country}"`, `value="${rule.country}" selected`)}
                            </select>
                            <div style="display:flex;align-items:center;gap:4px;">
                                <input type="number" min="1" max="72" value="${rule.delay}"
                                       style="width:60px;padding:8px;background:var(--content-bg);border:1px solid var(--card-border);border-radius:6px;color:var(--text-primary);text-align:center;"
                                       onchange="updateRule(${i}, 'delay', parseInt(this.value))">
                                <span style="font-size:12px;color:var(--text-muted);">ur</span>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px;margin-left:auto;">
                        <button class="action-btn" onclick="previewRuleTemplate(${i})" title="Predogled">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div class="toggle ${rule.active ? 'active' : ''}" onclick="toggleRule(${i})"></div>
                        <span style="font-size:12px;color:var(--text-muted);min-width:60px;">${rule.active ? 'Aktivno' : 'Neaktivno'}</span>
                        <button class="btn-remove-item" onclick="removeRule(${i})" title="Odstrani"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `).join('');

            if (smsAutomation.length === 0) {
                container.innerHTML = '<div class="empty" style="padding:20px;"><i class="fas fa-cog"></i><p>Ni nastavljenih pravil</p></div>';
            }
        }

        function previewRuleTemplate(idx) {
            const rule = smsAutomation[idx];
            if (!rule) return;

            document.getElementById('previewTemplate').value = rule.template || 'abandoned_cart';
            document.getElementById('previewCountry').value = rule.country === 'all' ? 'hr' : rule.country;
            updateTemplatePreview();

            // Scroll to preview
            document.querySelector('.table-card:last-child').scrollIntoView({ behavior: 'smooth' });
        }

        function updateRule(idx, field, value) {
            smsAutomation[idx][field] = value;
            saveSmsAutomation();
        }

        function toggleRule(idx) {
            smsAutomation[idx].active = !smsAutomation[idx].active;
            saveSmsAutomation();
            renderAutomationRules();

            if (smsAutomation[idx].active) {
                showToast('⚠️ Pravilo aktivirano. SMS sporočila se NE pošiljajo avtomatsko - dodajajo se v čakalno vrsto.', false, 'info');
            }
        }

        function removeRule(idx) {
            smsAutomation.splice(idx, 1);
            saveSmsAutomation();
            renderAutomationRules();
        }

        function addAutomationRule() {
            smsAutomation.push({
                id: Date.now(),
                name: 'Novo pravilo',
                delay: 3,
                template: 'abandoned_cart',
                country: 'all',
                active: false
            });
            saveSmsAutomation();
            renderAutomationRules();
        }

        // Add filter listeners for SMS dashboard (with null checks)
        const smsDateFromEl = document.getElementById('smsDateFrom');
        const smsDateToEl = document.getElementById('smsDateTo');
        if (smsDateFromEl) smsDateFromEl.addEventListener('change', renderSmsTable);
        if (smsDateToEl) smsDateToEl.addEventListener('change', renderSmsTable);

        // SMS History filter state
        let smsHistoryFilters = { country: '', status: '' };

        function setHistoryDateRange(range, btn) {
            const today = new Date();
            const dateFrom = document.getElementById('smsDateFrom');
            const dateTo = document.getElementById('smsDateTo');

            // Clear active state from all range pills
            document.querySelectorAll('.filter-pill[data-range]').forEach(p => p.classList.remove('active'));
            if (btn) btn.classList.add('active');

            const formatDate = (d) => d.toISOString().split('T')[0];

            switch(range) {
                case 'today':
                    dateFrom.value = formatDate(today);
                    dateTo.value = formatDate(today);
                    break;
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    dateFrom.value = formatDate(yesterday);
                    dateTo.value = formatDate(yesterday);
                    break;
                case 'week':
                    const weekAgo = new Date(today);
                    weekAgo.setDate(weekAgo.getDate() - 7);
                    dateFrom.value = formatDate(weekAgo);
                    dateTo.value = formatDate(today);
                    break;
                case 'month':
                    const monthAgo = new Date(today);
                    monthAgo.setDate(monthAgo.getDate() - 30);
                    dateFrom.value = formatDate(monthAgo);
                    dateTo.value = formatDate(today);
                    break;
                case 'all':
                    dateFrom.value = '';
                    dateTo.value = '';
                    break;
                case 'custom':
                    // Don't change dates, just mark as custom
                    break;
            }
            renderSmsTable();
        }

        function setHistoryCountry(country, btn) {
            document.querySelectorAll('.filter-pill[data-country]').forEach(p => p.classList.remove('active'));
            if (btn) btn.classList.add('active');
            smsHistoryFilters.country = country;
            renderSmsTable();
        }

        function setHistoryStatus(status, btn) {
            document.querySelectorAll('.filter-pill[data-status]').forEach(p => p.classList.remove('active'));
            if (btn) btn.classList.add('active');
            smsHistoryFilters.status = status;
            renderSmsTable();
        }

        // ========== SMS PROVIDER SETTINGS ==========
        let smsProviderSettings = null;

        async function loadSmsSettingsUI() {
            console.log('[SMS Settings] Loading UI...');
            try {
                const res = await fetch('api.php?action=sms-settings');
                smsProviderSettings = await res.json();
                console.log('[SMS Settings] Data loaded:', smsProviderSettings);
                renderSmsProviderTable();
                renderSmsQueueManagement();
                console.log('[SMS Settings] Render complete');
            } catch (e) {
                console.error('[SMS Settings] Failed to load:', e);
                showToast('Napaka pri nalaganju SMS nastavitev', true);
            }
        }

        function renderSmsProviderTable() {
            const tbody = document.getElementById('smsProviderRows');
            if (!tbody) return;

            // Hardcoded SMS eshop_sync_id values (configured in code)
            const countries = [
                { code: 'hr', flag: '🇭🇷', name: 'Croatia', eshop_sync_id: '637100000075', store: 'noriks.com/hr' },
                { code: 'cz', flag: '🇨🇿', name: 'Czech', eshop_sync_id: '637100000075', store: 'noriks.com/cz' },
                { code: 'pl', flag: '🇵🇱', name: 'Poland', eshop_sync_id: '637100000075', store: 'noriks.com/pl' },
                { code: 'sk', flag: '🇸🇰', name: 'Slovakia', eshop_sync_id: '637100000075', store: 'noriks.com/sk' },
                { code: 'gr', flag: '🇬🇷', name: 'Greece', eshop_sync_id: '637100000075', store: 'noriks.com/gr' },
                { code: 'it', flag: '🇮🇹', name: 'Italy', eshop_sync_id: '637100000075', store: 'noriks.com/it' },
                { code: 'hu', flag: '🇭🇺', name: 'Hungary', eshop_sync_id: '637100000075', store: 'noriks.com/hu' },
                { code: 'si', flag: '🇸🇮', name: 'Slovenia', eshop_sync_id: '637100367725', store: 'noriks.com/si' }
            ];

            tbody.innerHTML = countries.map(c => {
                return `
                    <tr>
                        <td><span style="font-size:18px;">${c.flag}</span> <strong>${c.name}</strong> (${c.code.toUpperCase()})</td>
                        <td>
                            <code style="background:var(--content-bg);padding:4px 8px;border-radius:4px;font-family:monospace;font-size:13px;">${c.eshop_sync_id}</code>
                        </td>
                        <td>
                            <a href="https://${c.store}" target="_blank" style="color:var(--accent-blue);text-decoration:none;">
                                <i class="fas fa-external-link-alt" style="font-size:10px;margin-right:4px;"></i>${c.store}
                            </a>
                        </td>
                        <td>
                            <button class="action-btn order"
                                    onclick="testSmsConnection('${c.code}')"
                                    title="Test Connection">
                                <i class="fas fa-plug"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        // SMS eshop_sync_id values are hardcoded - no save function needed
        const SMS_ESHOP_IDS = {
            'hr': '637100000075',
            'cz': '637100000075',
            'pl': '637100000075',
            'sk': '637100000075',
            'gr': '637100000075',
            'it': '637100000075',
            'hu': '637100000075',
            'si': '637100367725'
        };

        async function testSmsConnection(storeCode) {
            const btn = event.target.closest('button');
            const originalIcon = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            try {
                const res = await fetch('api.php?action=sms-test-connection', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ storeCode })
                });
                const result = await res.json();

                if (result.success) {
                    showToast(`✅ ${storeCode.toUpperCase()}: Connection OK!`);
                    if (smsProviderSettings?.providers?.[storeCode]) {
                        smsProviderSettings.providers[storeCode].lastTest = new Date().toISOString();
                        smsProviderSettings.providers[storeCode].lastTestResult = true;
                    }
                } else {
                    showToast(`❌ ${storeCode.toUpperCase()}: ${result.error}`, true);
                    if (smsProviderSettings?.providers?.[storeCode]) {
                        smsProviderSettings.providers[storeCode].lastTest = new Date().toISOString();
                        smsProviderSettings.providers[storeCode].lastTestResult = false;
                    }
                }
                renderSmsProviderTable();
            } catch (e) {
                showToast('Napaka pri testiranju', true);
            }

            btn.innerHTML = originalIcon;
            btn.disabled = false;
        }

        // ========== SMS QUEUE MANAGEMENT ==========
        async function renderSmsQueueManagement() {
            const container = document.getElementById('smsQueueManagement');
            if (!container) return;

            try {
                const res = await fetch('api.php?action=sms-queue&status=queued');
                const queue = await res.json();

                if (!queue.length) {
                    container.innerHTML = `
                        <div class="empty" style="padding:40px;">
                            <i class="fas fa-inbox"></i>
                            <p>Čakalna vrsta je prazna</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = `
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Datum</th>
                                    <th>Prejemnik</th>
                                    <th>Sporočilo</th>
                                    <th>Država</th>
                                    <th>Akcije</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${queue.map(sms => {
                                    const store = stores.find(s => s.code === sms.storeCode);
                                    const hasProvider = SMS_ESHOP_IDS[sms.storeCode]; // Always configured
                                    return `
                                        <tr>
                                            <td style="font-size:12px;">${new Date(sms.date).toLocaleString('sl-SI')}</td>
                                            <td>
                                                <div style="font-weight:500;">${esc(sms.customerName)}</div>
                                                <div style="font-size:12px;color:var(--text-muted);">${sms.recipient}</div>
                                            </td>
                                            <td style="max-width:300px;">
                                                <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${esc(sms.message)}">${esc(sms.message)}</div>
                                            </td>
                                            <td>${store?.flag || '🌍'} ${sms.storeCode?.toUpperCase() || ''}</td>
                                            <td style="white-space:nowrap;">
                                                <button class="action-btn"
                                                        onclick="openSmsEditModal('${sms.id}', '${escAttr(sms.recipient || '')}', '${escAttr(sms.message || '')}', '${escAttr(sms.customerName || 'Unknown')}')"
                                                        title="${hasProvider ? 'Pošlji SMS' : 'Provider ni nastavljen'}"
                                                        style="${hasProvider ? 'background:var(--accent-green);color:white;' : 'opacity:0.5;'}"
                                                        ${!hasProvider ? 'disabled' : ''}>
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                                <button class="action-btn" onclick="removeFromQueue('${sms.id}')" title="Odstrani">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    `;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top:16px;padding:12px;background:rgba(34,197,94,0.1);border:1px solid var(--accent-green);border-radius:8px;">
                        <i class="fas fa-info-circle" style="color:var(--accent-green);"></i>
                        <strong style="color:var(--accent-green);">Pošlji SMS:</strong> Klikni zeleni gumb <i class="fas fa-paper-plane"></i> za pošiljanje SMS-a preko MetaKocka.
                    </div>
                `;
            } catch (e) {
                container.innerHTML = '<div class="empty" style="color:var(--accent-red);"><i class="fas fa-exclamation-triangle"></i><p>Napaka pri nalaganju</p><button class="btn btn-save" style="margin-top:12px;" onclick="renderSmsQueueManagement()"><i class="fas fa-redo"></i> Poskusi znova</button></div>';
            }
        }

        async function sendSingleSms(smsId) {
            if (!confirm('Ali si prepričan, da želiš poslati ta SMS?\\n\\n⚠️ SMS bo poslan preko MetaKocka API!')) {
                return;
            }

            // Show loading
            showToast('📤 Pošiljam SMS preko MetaKocka...', false, 'info');

            try {
                const res = await fetch('api.php?action=sms-send', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: smsId })
                });
                const result = await res.json();

                // Log for debugging
                console.log('[SMS] MetaKocka response:', result);

                if (result.success) {
                    let msg = `✅ SMS uspešno poslan na ${result.recipient || 'prejemnika'}!`;
                    if (result.metakockaResponse) {
                        msg += `\n\n📊 MK: ${JSON.stringify(result.metakockaResponse)}`;
                    }
                    showToast(msg);
                    renderSmsQueueManagement();
                    loadSmsDashboardQueue();
                    await renderSmsTable();
                    await loadSmsData();
                    updateStats();
                } else {
                    let errorMsg = `❌ Napaka: ${result.error || 'Neznana napaka'}`;
                    if (result.debug) errorMsg += ` [${result.debug}]`;
                    if (result.httpCode) errorMsg += ` (HTTP ${result.httpCode})`;
                    if (result.metakockaResponse) {
                        console.error('[SMS] MK Error:', result.metakockaResponse);
                        errorMsg += `\n\n📊 MK: ${JSON.stringify(result.metakockaResponse)}`;
                    }
                    showToast(errorMsg, true);
                    alert('🚨 SMS NAPAKA!\n\n' + errorMsg);
                }
            } catch (e) {
                console.error('[SMS] Send error:', e);
                showToast('❌ Napaka pri pošiljanju: ' + e.message, true);
                alert('🚨 SMS NAPAKA!\n\n' + e.message);
            }
        }

        async function removeFromQueue(smsId) {
            try {
                await fetch('api.php?action=sms-remove', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: smsId })
                });
                showToast('SMS odstranjen iz čakalne vrste');
                renderSmsQueueManagement();
                loadSmsDashboardQueue(); // Update SMS Dashboard too
                updateStats();
            } catch (e) {
                showToast('Napaka pri odstranjevanju', true);
            }
        }

        function closeModal(id) { document.getElementById(id).classList.remove('open'); }
        function call(phone) { window.open('tel:'+phone, '_self'); }
        function logout() { localStorage.removeItem('callcenter_user'); window.location.href = 'login.php'; }

        function showToast(msg, isError = false, type = null) {
            const container = document.getElementById('toastContainer');
            const id = 'toast_' + Date.now();

            let icon = 'fa-check-circle';
            let toastType = 'success';
            if (isError) { icon = 'fa-times-circle'; toastType = 'error'; }
            else if (type === 'info') { icon = 'fa-info-circle'; toastType = 'info'; }
            else if (type === 'warning') { icon = 'fa-exclamation-triangle'; toastType = 'warning'; }

            const toast = document.createElement('div');
            toast.className = `toast-item ${toastType}`;
            toast.id = id;
            toast.innerHTML = `
                <i class="fas ${icon}"></i>
                <span>${msg}</span>
                <button class="toast-close" onclick="removeToast('${id}')"><i class="fas fa-times"></i></button>
            `;

            container.appendChild(toast);

            // Auto remove after 3s
            setTimeout(() => removeToast(id), 3000);
        }

        function removeToast(id) {
            const toast = document.getElementById(id);
            if (toast) {
                toast.classList.add('removing');
                setTimeout(() => toast.remove(), 300);
            }
        }

        // ========== SKELETON LOADING ==========
        function renderSkeletonTable(rows = 8) {
            return `
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead><tr>
                            <th class="checkbox-cell"><div class="skeleton skeleton-btn" style="width:18px;height:18px;margin:auto;"></div></th>
                            <th><div class="skeleton skeleton-text medium"></div></th>
                            <th><div class="skeleton skeleton-text short"></div></th>
                            <th><div class="skeleton skeleton-text short"></div></th>
                            <th><div class="skeleton skeleton-text medium"></div></th>
                            <th><div class="skeleton skeleton-text short"></div></th>
                            <th><div class="skeleton skeleton-text short"></div></th>
                        </tr></thead>
                        <tbody>
                            ${Array(rows).fill().map(() => `
                                <tr>
                                    <td class="checkbox-cell"><div class="skeleton skeleton-btn" style="width:18px;height:18px;margin:auto;"></div></td>
                                    <td>
                                        <div style="display:flex;gap:12px;align-items:center;">
                                            <div class="skeleton skeleton-avatar"></div>
                                            <div style="flex:1;">
                                                <div class="skeleton skeleton-text medium" style="margin-bottom:6px;"></div>
                                                <div class="skeleton skeleton-text short" style="height:10px;"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><div class="skeleton skeleton-text short"></div></td>
                                    <td><div class="skeleton skeleton-text short"></div></td>
                                    <td><div class="skeleton skeleton-text medium"></div></td>
                                    <td><div class="skeleton skeleton-badge"></div></td>
                                    <td>
                                        <div style="display:flex;gap:4px;">
                                            <div class="skeleton skeleton-btn"></div>
                                            <div class="skeleton skeleton-btn"></div>
                                            <div class="skeleton skeleton-btn"></div>
                                        </div>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        // ========== BULK SELECTION ==========
        let selectedItems = new Set();

        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
                const id = cb.dataset.id;
                if (checkbox.checked) {
                    selectedItems.add(id);
                } else {
                    selectedItems.delete(id);
                }
            });
            updateBulkBar();
        }

        function toggleRowSelection(checkbox) {
            const id = checkbox.dataset.id;
            if (checkbox.checked) {
                selectedItems.add(id);
            } else {
                selectedItems.delete(id);
            }
            updateBulkBar();

            // Update select all checkbox
            const selectAll = document.getElementById('selectAllCheckbox');
            const checkboxes = document.querySelectorAll('.row-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            if (selectAll) selectAll.checked = allChecked;
        }

        function updateBulkBar() {
            const bulkBar = document.getElementById('bulkBar');
            const count = selectedItems.size;

            document.getElementById('selectedCount').textContent = count;

            if (count > 0) {
                bulkBar.classList.add('show');
            } else {
                bulkBar.classList.remove('show');
            }
        }

        function clearSelection() {
            selectedItems.clear();
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
            const selectAll = document.getElementById('selectAllCheckbox');
            if (selectAll) selectAll.checked = false;
            updateBulkBar();
        }

        async function bulkAddToSms() {
            const selectedCarts = carts.filter(c => selectedItems.has(c.id) && c.phone);

            if (selectedCarts.length === 0) {
                showToast('Ni izbranih strank s telefonsko številko', true);
                return;
            }

            let added = 0;
            for (const cart of selectedCarts) {
                try {
                    // Get template for this store
                    const template = smsTemplates.find(t => t.storeCode === cart.storeCode && t.type === 'abandoned_cart');
                    if (!template) continue;

                    const message = template.template
                        .replace('{ime}', cart.firstName || cart.customerName.split(' ')[0])
                        .replace('{produkt}', cart.cartContents?.[0]?.name || 'vaš izdelek')
                        .replace('{link}', 'https://noriks.com/' + cart.storeCode);

                    await fetch('api.php?action=sms-add', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            phone: cart.phone,
                            customerName: cart.customerName,
                            storeCode: cart.storeCode,
                            message,
                            cartId: cart.id,
                            addedBy: user.username
                        })
                    });
                    added++;
                } catch (e) {
                    console.error('Failed to add SMS:', e);
                }
            }

            showToast(`${added} SMS dodanih v čakalno vrsto`, false, 'info');
            clearSelection();
            await loadSmsData();
        }

        function openBulkStatusModal() {
            if (selectedItems.size === 0) return;

            // Create simple status selection modal
            const statuses = [
                { value: 'not_called', label: 'Not Called' },
                { value: 'no_answer_1', label: 'No Answer (1)' },
                { value: 'callback', label: 'Callback' },
                { value: 'converted', label: 'Converted' },
                { value: 'not_interested', label: 'Not Interested' }
            ];

            const modal = document.createElement('div');
            modal.className = 'modal-bg open';
            modal.id = 'bulkStatusModal';
            modal.innerHTML = `
                <div class="modal" style="max-width:400px;">
                    <div class="modal-header">
                        <div class="modal-title">Change Status (${selectedItems.size} items)</div>
                        <button class="modal-close" onclick="closeBulkStatusModal()">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">New Status</label>
                            <select class="form-select" id="bulkNewStatus">
                                ${statuses.map(s => `<option value="${s.value}">${s.label}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-cancel" onclick="closeBulkStatusModal()">Cancel</button>
                        <button class="btn btn-save" onclick="applyBulkStatus()">Apply</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function closeBulkStatusModal() {
            const modal = document.getElementById('bulkStatusModal');
            if (modal) modal.remove();
        }

        async function applyBulkStatus() {
            const newStatus = document.getElementById('bulkNewStatus').value;

            let updated = 0;
            for (const id of selectedItems) {
                try {
                    await fetch('api.php?action=update-status', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            id,
                            callStatus: newStatus,
                            notes: ''
                        })
                    });

                    // Update local data
                    [carts, pending, buyers].forEach(arr => {
                        const item = arr.find(i => i.id === id);
                        if (item) item.callStatus = newStatus;
                    });

                    updated++;
                } catch (e) {
                    console.error('Failed to update status:', e);
                }
            }

            showToast(`${updated} items updated to "${statusLabel(newStatus)}"`);
            closeBulkStatusModal();
            clearSelection();
            renderTable();
        }

        // Helpers
        function initials(n) { return (n||'?').split(' ').map(x=>x[0]).join('').substring(0,2).toUpperCase(); }
        function esc(t) { const d=document.createElement('div'); d.textContent=t||''; return d.innerHTML; }
        function escAttr(t) { return (t||'').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'&quot;'); }
        function statusLabel(s) {
            const labels = {
                not_called:'Not Called',
                no_answer_1:'No Answer (1)',
                no_answer_2:'No Answer (2)',
                no_answer_3:'No Answer (3)',
                no_answer_4:'No Answer (4)',
                no_answer_5:'No Answer (5)',
                callback:'Callback',
                converted:'Converted',
                not_interested:'Not Interested',
                wrong_number:'Wrong #',
                voicemail:'Voicemail',
                called:'Called',
                answered:'Answered',
                no_answer:'No Answer'
            };
            return labels[s] || s;
        }
        function formatDate(d) { if(!d)return''; return new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}); }
        function timeAgo(d) { if(!d)return''; const s=Math.floor((new Date()-new Date(d))/1000); if(s<60)return'now'; if(s<3600)return Math.floor(s/60)+'m'; if(s<86400)return Math.floor(s/3600)+'h'; return Math.floor(s/86400)+'d'; }

        // ========== CUSTOMER 360° FUNCTIONS ==========
        let currentCustomer = null;
        let currentC360Tab = 'timeline';

        async function openCustomer360(customer) {
            currentCustomer = customer;
            currentCustomer.realOrders = [];
            currentCustomer.realSmsHistory = [];

            // Populate header
            document.getElementById('c360Avatar').textContent = initials(customer.customerName);
            document.getElementById('c360Name').textContent = customer.customerName;
            document.getElementById('c360Email').textContent = customer.email || 'N/A';
            document.getElementById('c360Phone').textContent = customer.phone || 'N/A';
            document.getElementById('c360Location').textContent = customer.location || customer.city || 'N/A';

            // Show loading state
            document.getElementById('c360TotalSpent').textContent = '...';
            document.getElementById('c360Orders').textContent = '...';

            // Setup tabs
            document.querySelectorAll('.c360-tab').forEach(tab => {
                tab.classList.remove('active');
                if (tab.dataset.c360tab === 'timeline') tab.classList.add('active');
            });

            currentC360Tab = 'timeline';
            document.getElementById('c360Content').innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
            document.getElementById('customerModal').classList.add('open');

            // Fetch real customer data from API
            if (customer.email) {
                try {
                    const resp = await fetch(`api.php?action=customer-360&email=${encodeURIComponent(customer.email)}`);
                    const data = await resp.json();
                    if (data.success) {
                        currentCustomer.realOrders = data.orders || [];
                        currentCustomer.realSmsHistory = data.smsHistory || [];
                        document.getElementById('c360TotalSpent').textContent = '€' + (data.totalSpent || 0).toFixed(2);
                        document.getElementById('c360Orders').textContent = data.orderCount || 0;
                    }
                } catch (e) {
                    console.error('Customer 360 fetch error:', e);
                }
            }

            renderC360Content();
        }

        // Tab click handlers
        document.querySelectorAll('.c360-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.c360-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                currentC360Tab = tab.dataset.c360tab;
                renderC360Content();
            });
        });

        function renderC360Content() {
            const container = document.getElementById('c360Content');
            if (!currentCustomer) return;

            const email = currentCustomer.email;
            const customerCarts = carts.filter(c => c.email === email);
            const realOrders = currentCustomer.realOrders || [];
            const realSmsHistory = currentCustomer.realSmsHistory || [];

            switch (currentC360Tab) {
                case 'timeline':
                    renderC360Timeline(customerCarts, realOrders, realSmsHistory);
                    break;
                case 'orders':
                    renderC360Orders(realOrders);
                    break;
                case 'carts':
                    renderC360Carts(customerCarts);
                    break;
                case 'sms':
                    renderC360Sms(realSmsHistory);
                    break;
                case 'notes':
                    renderC360Notes();
                    break;
            }
        }

        function renderC360Timeline(customerCarts, realOrders, realSmsHistory) {
            const container = document.getElementById('c360Content');

            const events = [];
            
            // Add abandoned carts
            customerCarts.forEach(c => {
                events.push({
                    type: 'cart',
                    icon: 'fa-shopping-cart',
                    title: '🛒 Cart abandoned',
                    desc: `€${c.cartValue?.toFixed(2)} - ${c.storeFlag} ${c.storeName}`,
                    time: c.abandonedAt
                });
            });
            
            // Add real orders from WooCommerce
            realOrders.forEach(o => {
                const statusEmoji = {completed: '✅', processing: '📦', 'on-hold': '⏸️'}[o.status] || '📋';
                events.push({
                    type: 'order',
                    icon: 'fa-check-circle',
                    title: `${statusEmoji} Order #${o.id}`,
                    desc: `€${o.total?.toFixed(2)} - ${o.storeFlag} ${o.storeName}`,
                    time: o.date
                });
            });
            
            // Add SMS history
            realSmsHistory.forEach(s => {
                const statusIcon = s.status === 'sent' ? '✅' : (s.status === 'failed' ? '❌' : '⏳');
                events.push({
                    type: 'sms',
                    icon: 'fa-comment-sms',
                    title: `${statusIcon} SMS ${s.status}`,
                    desc: (s.message || '').substring(0, 50) + '...',
                    time: s.sentAt || s.createdAt
                });
            });

            events.sort((a, b) => new Date(b.time) - new Date(a.time));

            if (events.length === 0) {
                container.innerHTML = '<div class="empty"><i class="fas fa-history"></i><p>No activity history</p></div>';
                return;
            }

            container.innerHTML = events.map(e => `
                <div class="timeline-item">
                    <div class="timeline-dot ${e.type}"><i class="fas ${e.icon}"></i></div>
                    <div class="timeline-content">
                        <div class="timeline-title">${esc(e.title)}</div>
                        <div class="timeline-desc">${esc(e.desc)}</div>
                        <div class="timeline-time">${formatDate(e.time)} ${new Date(e.time).toLocaleTimeString('sl-SI', {hour:'2-digit',minute:'2-digit'})}</div>
                    </div>
                </div>
            `).join('');
        }

        function renderC360Orders(realOrders) {
            const container = document.getElementById('c360Content');

            if (!realOrders || realOrders.length === 0) {
                container.innerHTML = '<div class="empty"><i class="fas fa-box-open"></i><p>No orders yet</p></div>';
                return;
            }

            container.innerHTML = realOrders.map(o => {
                const statusBadge = {
                    completed: '<span style="background:#28a745;color:white;padding:2px 6px;border-radius:4px;font-size:10px;">COMPLETED</span>',
                    processing: '<span style="background:#007bff;color:white;padding:2px 6px;border-radius:4px;font-size:10px;">PROCESSING</span>',
                    'on-hold': '<span style="background:#ffc107;color:black;padding:2px 6px;border-radius:4px;font-size:10px;">ON HOLD</span>'
                }[o.status] || `<span style="background:#6c757d;color:white;padding:2px 6px;border-radius:4px;font-size:10px;">${o.status?.toUpperCase()}</span>`;
                
                const itemsList = (o.items || []).map(i => `${i.quantity}x ${i.name}`).join(', ');
                
                return `
                <div class="order-item" style="margin-bottom:16px;padding:12px;background:#f8f9fa;border-radius:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <div>
                            <div class="order-item-name" style="font-weight:600;">Order #${o.id}</div>
                            <div class="order-item-id" style="color:#666;font-size:12px;">${o.storeFlag} ${o.storeName} • ${formatDate(o.date)}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:700;font-size:16px;">€${o.total?.toFixed(2) || '0.00'}</div>
                            ${statusBadge}
                        </div>
                    </div>
                    ${itemsList ? `<div style="font-size:12px;color:#555;border-top:1px solid #ddd;padding-top:8px;margin-top:8px;">${esc(itemsList)}</div>` : ''}
                </div>
            `}).join('');
        }

        function renderC360Carts(customerCarts) {
            const container = document.getElementById('c360Content');

            if (customerCarts.length === 0) {
                container.innerHTML = '<div class="empty"><i class="fas fa-shopping-cart"></i><p>No carts</p></div>';
                return;
            }

            container.innerHTML = customerCarts.map(c => `
                <div class="order-item" style="margin-bottom:12px;">
                    <div class="order-item-info">
                        <div class="order-item-name">${c.cartContents?.[0]?.name || 'Cart'}</div>
                        <div class="order-item-id">${c.storeFlag} ${c.storeName} • ${timeAgo(c.abandonedAt)} ago</div>
                    </div>
                    <span class="badge ${c.callStatus}">${statusLabel(c.callStatus)}</span>
                    <div class="order-item-total">€${c.cartValue?.toFixed(2) || '0.00'}</div>
                </div>
            `).join('');
        }

        function renderC360Sms(realSmsHistory) {
            const container = document.getElementById('c360Content');

            if (!realSmsHistory || realSmsHistory.length === 0) {
                container.innerHTML = '<div class="empty"><i class="fas fa-comment-sms"></i><p>No SMS history</p></div>';
                return;
            }

            container.innerHTML = realSmsHistory.map(s => {
                const statusBadge = {
                    sent: '<span style="background:#28a745;color:white;padding:2px 6px;border-radius:4px;font-size:10px;">SENT ✓</span>',
                    queued: '<span style="background:#ffc107;color:black;padding:2px 6px;border-radius:4px;font-size:10px;">QUEUED</span>',
                    failed: '<span style="background:#dc3545;color:white;padding:2px 6px;border-radius:4px;font-size:10px;">FAILED</span>'
                }[s.status] || `<span style="background:#6c757d;color:white;padding:2px 6px;border-radius:4px;font-size:10px;">${s.status?.toUpperCase()}</span>`;
                
                return `
                <div class="order-item" style="margin-bottom:12px;padding:10px;background:#f8f9fa;border-radius:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
                        <div style="font-size:12px;color:#666;">${formatDate(s.sentAt || s.createdAt)}</div>
                        ${statusBadge}
                    </div>
                    <div style="font-size:13px;white-space:normal;word-break:break-word;">${esc(s.message || '')}</div>
                </div>
            `}).join('');
        }

        function renderC360Notes() {
            const container = document.getElementById('c360Content');
            const notes = currentCustomer?.notes || '';

            container.innerHTML = `
                <div class="form-group">
                    <label class="form-label">Customer Notes</label>
                    <textarea class="form-textarea" id="c360NotesInput" style="min-height:150px;" placeholder="Add notes about this customer...">${esc(notes)}</textarea>
                </div>
                <button class="btn btn-save" onclick="saveCustomerNotes()">
                    <i class="fas fa-save"></i> Save Notes
                </button>
            `;
        }

        async function saveCustomerNotes() {
            const notes = document.getElementById('c360NotesInput').value;
            if (!currentCustomer) return;

            await fetch('api.php?action=update-status', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: currentCustomer.id,
                    callStatus: currentCustomer.callStatus,
                    notes
                })
            });

            currentCustomer.notes = notes;
            const cart = carts.find(c => c.id === currentCustomer.id);
            if (cart) cart.notes = notes;

            showToast('Notes saved!');
        }

        // Click on customer name to open 360° view
        function addCustomerClickHandlers() {
            document.querySelectorAll('.customer-cell').forEach(cell => {
                cell.style.cursor = 'pointer';
                cell.addEventListener('click', (e) => {
                    if (e.target.closest('.action-btn')) return;
                    const row = cell.closest('tr');
                    const cartId = row?.querySelector('[onclick*="openOrderModal"]')?.getAttribute('onclick')?.match(/'([^']+)'/)?.[1];
                    if (cartId) {
                        const cart = carts.find(c => c.id === cartId);
                        if (cart) openCustomer360(cart);
                    }
                });
            });
        }
        // ========== AGENT MANAGEMENT FUNCTIONS ==========
        let agentsList = [];
        let editingAgentId = null;
        let agentActiveState = true;

        async function loadAgents() {
            if (!isAdmin) return;

            try {
                const res = await fetch('api.php?action=agents-list');
                const data = await res.json();
                agentsList = data.users || [];
                renderAgentsTable();
            } catch (e) {
                console.error('Failed to load agents:', e);
            }
        }

        function renderAgentsTable() {
            const tbody = document.getElementById('agentsTableBody');
            if (!tbody) return;

            if (agentsList.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty"><i class="fas fa-users"></i><p>Ni agentov</p></td></tr>';
                return;
            }

            const countryFlags = { hr: '🇭🇷', cz: '🇨🇿', pl: '🇵🇱', gr: '🇬🇷', sk: '🇸🇰', it: '🇮🇹', hu: '🇭🇺', all: '🌍' };

            tbody.innerHTML = agentsList.map(agent => {
                const countries = (agent.countries || []).map(c =>
                    c === 'all' ? '🌍 All' : `${countryFlags[c] || ''} ${c.toUpperCase()}`
                ).join(', ');

                return `
                    <tr>
                        <td>
                            <div class="customer-cell">
                                <div class="avatar">${initials(agent.username)}</div>
                                <div>
                                    <div class="customer-name">${esc(agent.username)}</div>
                                    <div class="customer-email">ID: ${agent.id}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="agent-badge ${agent.role}">
                                <i class="fas ${agent.role === 'admin' ? 'fa-shield-alt' : 'fa-user'}"></i>
                                ${agent.role === 'admin' ? 'Admin' : 'Agent'}
                            </span>
                        </td>
                        <td style="max-width:200px;">
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                ${(agent.countries || []).map(c => `
                                    <span style="background:var(--content-bg);padding:2px 8px;border-radius:4px;font-size:12px;">
                                        ${c === 'all' ? '🌍 All' : `${countryFlags[c] || ''} ${c.toUpperCase()}`}
                                    </span>
                                `).join('')}
                            </div>
                        </td>
                        <td>
                            <span class="badge ${agent.active !== false ? 'converted' : 'not_interested'}">
                                ${agent.active !== false ? '✅ Active' : '❌ Inactive'}
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);">
                            ${agent.createdAt ? formatDate(agent.createdAt) : '-'}
                        </td>
                        <td style="white-space:nowrap;">
                            <button class="action-btn" onclick="openAgentModal('${agent.id}')" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            ${agent.role !== 'admin' || agentsList.filter(a => a.role === 'admin').length > 1 ? `
                                <button class="action-btn" onclick="deleteAgent('${agent.id}')" title="Delete" style="background:rgba(239,68,68,0.2);color:var(--accent-red);">
                                    <i class="fas fa-trash"></i>
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function openAgentModal(agentId = null) {
            editingAgentId = agentId;
            const agent = agentId ? agentsList.find(a => a.id === agentId) : null;

            // Update modal title
            document.getElementById('agentModalTitle').textContent = agent ? '✏️ Edit Agent' : '➕ Add Agent';
            document.getElementById('passwordHint').style.display = agent ? 'block' : 'none';

            // Populate fields
            document.getElementById('agentEditId').value = agentId || '';
            document.getElementById('agentUsername').value = agent?.username || '';
            document.getElementById('agentPassword').value = '';
            document.getElementById('agentRole').value = agent?.role || 'agent';

            agentActiveState = agent?.active !== false;
            document.getElementById('agentActiveToggle').classList.toggle('active', agentActiveState);

            // Render country checkboxes
            renderAgentCountryCheckboxes(agent?.countries || []);

            document.getElementById('agentModal').classList.add('open');
        }

        function renderAgentCountryCheckboxes(selectedCountries = []) {
            const container = document.getElementById('agentCountries');
            const countries = [
                { code: 'hr', flag: '🇭🇷', name: 'Croatia' },
                { code: 'cz', flag: '🇨🇿', name: 'Czech' },
                { code: 'pl', flag: '🇵🇱', name: 'Poland' },
                { code: 'gr', flag: '🇬🇷', name: 'Greece' },
                { code: 'sk', flag: '🇸🇰', name: 'Slovakia' },
                { code: 'it', flag: '🇮🇹', name: 'Italy' },
                { code: 'hu', flag: '🇭🇺', name: 'Hungary' }
            ];

            const hasAll = selectedCountries.includes('all');

            container.innerHTML = `
                <label class="country-checkbox ${hasAll ? 'selected' : ''}" onclick="toggleAgentCountry(this, 'all')">
                    <input type="checkbox" name="agent_country" value="all" ${hasAll ? 'checked' : ''}>
                    <span class="flag">🌍</span>
                    <span class="name">All Countries</span>
                </label>
                ${countries.map(c => `
                    <label class="country-checkbox ${selectedCountries.includes(c.code) ? 'selected' : ''}" onclick="toggleAgentCountry(this, '${c.code}')">
                        <input type="checkbox" name="agent_country" value="${c.code}" ${selectedCountries.includes(c.code) ? 'checked' : ''}>
                        <span class="flag">${c.flag}</span>
                        <span class="name">${c.code.toUpperCase()}</span>
                    </label>
                `).join('')}
            `;
        }

        function toggleAgentCountry(label, code) {
            const checkbox = label.querySelector('input');
            const isChecked = !checkbox.checked;
            checkbox.checked = isChecked;
            label.classList.toggle('selected', isChecked);

            // If "all" is selected, uncheck others
            if (code === 'all' && isChecked) {
                document.querySelectorAll('#agentCountries input[name="agent_country"]').forEach(cb => {
                    if (cb.value !== 'all') {
                        cb.checked = false;
                        cb.closest('label').classList.remove('selected');
                    }
                });
            }
            // If specific country selected, uncheck "all"
            else if (code !== 'all' && isChecked) {
                const allCb = document.querySelector('#agentCountries input[value="all"]');
                if (allCb) {
                    allCb.checked = false;
                    allCb.closest('label').classList.remove('selected');
                }
            }
        }

        function toggleAgentActive() {
            agentActiveState = !agentActiveState;
            document.getElementById('agentActiveToggle').classList.toggle('active', agentActiveState);
        }

        async function saveAgent() {
            const username = document.getElementById('agentUsername').value.trim();
            const password = document.getElementById('agentPassword').value;
            const role = document.getElementById('agentRole').value;

            // Get selected countries
            const countries = [];
            document.querySelectorAll('#agentCountries input[name="agent_country"]:checked').forEach(cb => {
                countries.push(cb.value);
            });

            // Validation
            if (!username) {
                showToast('Username is required', true);
                return;
            }

            if (!editingAgentId && !password) {
                showToast('Password is required for new agents', true);
                return;
            }

            if (countries.length === 0) {
                showToast('Select at least one country', true);
                return;
            }

            const payload = {
                username,
                role,
                countries,
                active: agentActiveState
            };

            if (password) {
                payload.password = password;
            }

            try {
                let res;
                if (editingAgentId) {
                    payload.id = editingAgentId;
                    res = await fetch('api.php?action=agents-update', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                } else {
                    res = await fetch('api.php?action=agents-add', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload)
                    });
                }

                const result = await res.json();

                if (result.success || result.id) {
                    closeModal('agentModal');
                    showToast(editingAgentId ? '✅ Agent updated!' : '✅ Agent added!');
                    await loadAgents();
                } else {
                    showToast(result.error || 'Error saving agent', true);
                }
            } catch (e) {
                showToast('Connection error', true);
            }
        }

        async function deleteAgent(agentId) {
            const agent = agentsList.find(a => a.id === agentId);
            if (!agent) return;

            if (!confirm(`Are you sure you want to delete agent "${agent.username}"?`)) {
                return;
            }

            try {
                const res = await fetch('api.php?action=agents-delete', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: agentId })
                });

                const result = await res.json();

                if (result.success) {
                    showToast('✅ Agent deleted!');
                    await loadAgents();
                } else {
                    showToast(result.error || 'Error deleting agent', true);
                }
            } catch (e) {
                showToast('Connection error', true);
            }
        }

        // ========== COUNTRY FILTERING FOR AGENTS ==========
        function filterCountryTabs() {
            if (hasAllCountries || isAdmin) return; // Admin sees everything

            // Hide country tabs that user doesn't have access to
            document.querySelectorAll('.country-tab[data-store]').forEach(tab => {
                const storeCode = tab.dataset.store;
                if (storeCode === 'all') {
                    // "All" tab - keep but will filter data
                    return;
                }
                if (!userCountries.includes(storeCode)) {
                    tab.style.display = 'none';
                }
            });

            // If user has only one country, auto-select it
            if (userCountries.length === 1) {
                currentStore = userCountries[0];
                document.querySelectorAll('.country-tab').forEach(t => t.classList.remove('active'));
                const targetTab = document.querySelector(`.country-tab[data-store="${userCountries[0]}"]`);
                if (targetTab) targetTab.classList.add('active');
            }
        }

        function filterDataByUserCountries(dataArray) {
            if (hasAllCountries || isAdmin) return dataArray;
            return dataArray.filter(item => userCountries.includes(item.storeCode));
        }

        // Override loadAllData to filter by user countries
        const originalLoadAllData = loadAllData;
        loadAllData = async function() {
            await originalLoadAllData();

            // Filter data by user countries
            if (!hasAllCountries && !isAdmin) {
                carts = filterDataByUserCountries(carts);
                pending = filterDataByUserCountries(pending);
                buyers = filterDataByUserCountries(buyers);
            }

            // Apply country tab filtering
            filterCountryTabs();

            // Update counts after filtering
            updateCounts();
            updateStats();
            renderTable();

            // Load agents if admin
            if (isAdmin) {
                loadAgents();
            }

            // Load follow-ups count
            loadFollowupsCount();
        };

        // ========== CALL LOGGING FUNCTIONS ==========
        const CALL_STATUSES = [
            { value: 'not_called', label: 'Not Called', icon: '📵', class: '' },
            { value: 'no_answer_1', label: 'No Answer (1)', icon: '📞', class: 'no_answer' },
            { value: 'no_answer_2', label: 'No Answer (2)', icon: '📞', class: 'no_answer' },
            { value: 'no_answer_3', label: 'No Answer (3)', icon: '📞', class: 'no_answer' },
            { value: 'no_answer_4', label: 'No Answer (4)', icon: '📞', class: 'no_answer' },
            { value: 'no_answer_5', label: 'No Answer (5)', icon: '📞', class: 'no_answer' },
            { value: 'callback', label: 'Callback', icon: '🔔', class: 'callback' },
            { value: 'converted', label: 'Converted', icon: '✅', class: 'converted' },
            { value: 'not_interested', label: 'Not Interested', icon: '❌', class: 'not_interested' },
            { value: 'wrong_number', label: 'Wrong Number', icon: '❓', class: 'not_interested' },
            { value: 'voicemail', label: 'Voicemail', icon: '📬', class: 'no_answer' }
        ];

        let currentCallLogCustomer = null;
        let selectedCallStatus = 'not_called';
        let followupsData = [];

        function openCallLogModal(customer) {
            currentCallLogCustomer = customer;
            selectedCallStatus = customer.callStatus || 'not_called';

            document.getElementById('callLogCustomerId').value = customer.id;
            document.getElementById('callLogStoreCode').value = customer.storeCode;
            document.getElementById('callLogNotes').value = '';
            document.getElementById('callLogDuration').value = '';
            document.getElementById('callLogCallback').value = '';

            renderCallStatusGrid();
            toggleCallbackSection();

            document.getElementById('callLogModal').classList.add('open');
        }

        function renderCallStatusGrid() {
            const grid = document.getElementById('callStatusGrid');
            grid.innerHTML = CALL_STATUSES.filter(s => s.value !== 'not_called').map(status => `
                <div class="call-status-option ${status.class} ${selectedCallStatus === status.value ? 'selected' : ''}"
                     onclick="selectCallStatus('${status.value}')">
                    <span style="font-size:18px;display:block;margin-bottom:4px;">${status.icon}</span>
                    ${status.label}
                </div>
            `).join('');
        }

        function selectCallStatus(status) {
            selectedCallStatus = status;
            renderCallStatusGrid();
            toggleCallbackSection();
        }

        function toggleCallbackSection() {
            const section = document.getElementById('callbackSection');
            section.style.display = selectedCallStatus === 'callback' ? 'block' : 'none';

            if (selectedCallStatus === 'callback' && !document.getElementById('callLogCallback').value) {
                // Default to tomorrow 10:00
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                tomorrow.setHours(10, 0, 0, 0);
                document.getElementById('callLogCallback').value = tomorrow.toISOString().slice(0, 16);
            }
        }

        async function saveCallLog() {
            const customerId = document.getElementById('callLogCustomerId').value;
            const storeCode = document.getElementById('callLogStoreCode').value;
            const notes = document.getElementById('callLogNotes').value;
            const duration = document.getElementById('callLogDuration').value;
            const callbackAt = document.getElementById('callLogCallback').value;

            if (selectedCallStatus === 'callback' && !callbackAt) {
                showToast('Prosim izberi datum in čas za callback!', true);
                return;
            }

            try {
                const res = await fetch('api.php?action=call-logs-add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        customerId,
                        storeCode,
                        status: selectedCallStatus,
                        notes,
                        duration: duration ? parseInt(duration) : null,
                        callbackAt: callbackAt ? new Date(callbackAt).toISOString() : null,
                        agentId: user.username
                    })
                });

                const result = await res.json();

                if (result.success) {
                    showToast('Call logged successfully!');
                    closeModal('callLogModal');

                    // Update local data
                    const cart = carts.find(c => c.id === customerId);
                    if (cart) {
                        cart.callStatus = selectedCallStatus;
                        cart.notes = notes;
                    }

                    renderTable();
                    loadFollowupsCount();

                    // Refresh customer 360 if open
                    if (currentCustomer && currentCustomer.id === customerId) {
                        renderC360Content();
                    }
                } else {
                    showToast(result.error || 'Failed to log call', true);
                }
            } catch (e) {
                showToast('Error: ' + e.message, true);
            }
        }

        async function loadFollowupsCount() {
            try {
                const res = await fetch(`api.php?action=my-followups&agentId=${encodeURIComponent(user.username)}`);
                followupsData = await res.json();

                const dueCount = followupsData.filter(f => f.isDue).length;
                const todayCount = followupsData.filter(f => f.isToday).length;
                const totalCount = followupsData.length;

                const badge = document.getElementById('navFollowups');
                badge.textContent = totalCount;

                if (dueCount > 0) {
                    badge.classList.add('due');
                    badge.title = `${dueCount} due now!`;
                } else {
                    badge.classList.remove('due');
                }
            } catch (e) {
                console.error('Failed to load followups count:', e);
            }
        }

        async function renderFollowups(showAll = false) {
            const container = document.getElementById('followupsContainer');
            container.innerHTML = '<div class="loading"><div class="spinner"></div>Loading...</div>';

            try {
                const url = showAll
                    ? 'api.php?action=my-followups&all=true'
                    : `api.php?action=my-followups&agentId=${encodeURIComponent(user.username)}`;
                const res = await fetch(url);
                const data = await res.json();

                if (data.length === 0) {
                    container.innerHTML = `
                        <div class="empty" style="padding:60px;">
                            <i class="fas fa-check-circle" style="font-size:48px;color:var(--accent-green);margin-bottom:16px;"></i>
                            <p>Ni scheduled follow-ups! 🎉</p>
                        </div>
                    `;
                    return;
                }

                // Group by date
                const today = new Date().toDateString();
                const tomorrow = new Date(Date.now() + 86400000).toDateString();

                const groups = {
                    due: { label: '⚠️ Due Now', items: [] },
                    today: { label: '📅 Danes', items: [] },
                    tomorrow: { label: '📆 Jutri', items: [] },
                    later: { label: '🗓️ Kasneje', items: [] }
                };

                data.forEach(f => {
                    if (f.isDue) groups.due.items.push(f);
                    else if (f.isToday) groups.today.items.push(f);
                    else if (f.isTomorrow) groups.tomorrow.items.push(f);
                    else groups.later.items.push(f);
                });

                let html = `
                    <div class="table-wrapper">
                    <table class="data-table">
                        <thead><tr>
                            <th>Status</th>
                            <th>Stranka</th>
                            <th>Telefon</th>
                            <th>Vrednost</th>
                            <th>Callback</th>
                            <th>Agent</th>
                            <th>Opombe</th>
                            <th style="text-align:right;">Akcije</th>
                        </tr></thead>
                        <tbody>
                `;

                for (const [key, group] of Object.entries(groups)) {
                    if (group.items.length === 0) continue;

                    group.items.forEach(f => {
                        const timeClass = f.isDue ? 'due' : (f.isToday ? 'today' : (f.isTomorrow ? 'tomorrow' : 'future'));
                        const timeLabel = f.isDue ? '⚠️ DUE!' : formatDateTime(f.callbackAt);
                        const statusBadge = {
                            due: '<span style="background:#dc3545;color:white;padding:3px 8px;border-radius:4px;font-size:11px;">DUE</span>',
                            today: '<span style="background:#fd7e14;color:white;padding:3px 8px;border-radius:4px;font-size:11px;">DANES</span>',
                            tomorrow: '<span style="background:#28a745;color:white;padding:3px 8px;border-radius:4px;font-size:11px;">JUTRI</span>',
                            future: '<span style="background:#6c757d;color:white;padding:3px 8px;border-radius:4px;font-size:11px;">KASNEJE</span>'
                        }[timeClass];
                        const isCompleted = f.completed === true;
                        const rowStyle = isCompleted ? 'background:linear-gradient(90deg, #d4edda 0%, #f8f9fa 100%);opacity:0.7;' : '';

                        html += `
                            <tr style="${rowStyle}">
                                <td>${isCompleted ? '<span style="background:#28a745;color:white;padding:3px 8px;border-radius:4px;font-size:11px;">✓ DONE</span>' : statusBadge}</td>
                                <td><strong>${f.customer?.storeFlag || ''} ${esc(f.customer?.name || 'Unknown')}</strong></td>
                                <td>${f.customer?.phone ? `<a href="tel:${f.customer.phone}" class="phone-link"><i class="fas fa-phone"></i> ${esc(f.customer.phone)}</a>` : '-'}</td>
                                <td><strong>${f.customer?.currency || '€'}${(f.customer?.cartValue || 0).toFixed(2)}</strong></td>
                                <td style="font-size:12px;">${timeLabel}</td>
                                <td style="font-size:12px;">${esc(f.agentId)}</td>
                                <td style="font-size:12px;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${escAttr(f.notes || '')}">${esc(f.notes || '-')}</td>
                                <td style="white-space:nowrap;text-align:right;">
                                    ${isCompleted ? `
                                        <span style="color:#28a745;font-size:11px;margin-right:8px;">✓ Zaključeno</span>
                                        <button class="action-btn" style="background:#dc3545;color:white;" onclick="deleteFollowup('${f.id}')" title="Briši"><i class="fas fa-trash"></i></button>
                                    ` : `
                                        <button class="action-btn call" onclick="callFollowupCustomer('${f.customerId}')" title="Pokliči"><i class="fas fa-phone"></i></button>
                                        <button class="action-btn" style="background:#28a745;color:white;" onclick="completeFollowup('${f.id}')" title="Zaključi"><i class="fas fa-check"></i></button>
                                        <button class="action-btn" style="background:#dc3545;color:white;" onclick="deleteFollowup('${f.id}')" title="Briši"><i class="fas fa-trash"></i></button>
                                    `}
                                </td>
                            </tr>
                        `;
                    });
                }

                html += `</tbody></table></div>`;
                container.innerHTML = html;
            } catch (e) {
                container.innerHTML = `<div class="empty" style="color:var(--accent-red);"><i class="fas fa-exclamation-triangle"></i><p>Error: ${e.message}</p></div>`;
            }
        }

        function callFollowupCustomer(customerId) {
            const customer = carts.find(c => c.id === customerId);
            if (customer) {
                openCallLogModal(customer);
            }
        }

        function viewFollowupCustomer(customerId) {
            const customer = carts.find(c => c.id === customerId);
            if (customer) {
                openCustomer360(customer);
            }
        }

        async function completeFollowup(followupId) {
            if (!confirm('Označiti follow-up kot zaključen?')) return;
            try {
                const res = await fetch('api.php?action=complete-followup', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id: followupId })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Follow-up zaključen!', 'success');
                    renderFollowups();
                } else {
                    showToast(data.error || 'Napaka', 'error');
                }
            } catch (e) {
                showToast('Napaka: ' + e.message, 'error');
            }
        }

        async function deleteFollowup(followupId) {
            if (!confirm('Res želiš izbrisati ta follow-up?')) return;
            try {
                const res = await fetch('api.php?action=delete-followup', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ id: followupId })
                });
                const data = await res.json();
                if (data.success) {
                    showToast('Follow-up izbrisan!', 'success');
                    renderFollowups();
                } else {
                    showToast(data.error || 'Napaka', 'error');
                }
            } catch (e) {
                showToast('Napaka: ' + e.message, 'error');
            }
        }

        function formatDateTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            return d.toLocaleDateString('sl-SI') + ' ' + d.toLocaleTimeString('sl-SI', { hour: '2-digit', minute: '2-digit' });
        }

        // ========== CUSTOMER 360° CALL HISTORY ==========
        async function loadCustomerCallHistory(customerId) {
            try {
                const res = await fetch(`api.php?action=call-logs-customer&customerId=${encodeURIComponent(customerId)}`);
                return await res.json();
            } catch (e) {
                console.error('Failed to load call history:', e);
                return [];
            }
        }

        // Override renderC360Timeline to include call history
        const originalRenderC360Timeline = renderC360Timeline;
        renderC360Timeline = async function(customerCarts, customerSms) {
            const container = document.getElementById('c360Content');

            // Load call history
            const callLogs = currentCustomer ? await loadCustomerCallHistory(currentCustomer.id) : [];

            const events = [];

            // Add call logs
            callLogs.forEach(log => {
                const statusInfo = CALL_STATUSES.find(s => s.value === log.status) || { label: log.status, icon: '📞' };
                events.push({
                    type: 'call',
                    icon: 'fa-phone',
                    title: `${statusInfo.icon} ${statusInfo.label}`,
                    desc: log.notes || 'No notes',
                    time: log.createdAt,
                    extra: log.duration ? `Duration: ${log.duration} min` : null,
                    callback: log.callbackAt,
                    agent: log.agentId,
                    statusClass: log.status.includes('no_answer') ? 'no_answer' : log.status
                });
            });

            // Add cart events
            customerCarts.forEach(c => {
                events.push({
                    type: 'cart',
                    icon: 'fa-shopping-cart',
                    title: 'Cart abandoned',
                    desc: `€${c.cartValue?.toFixed(2)} - ${c.storeFlag} ${c.storeName}`,
                    time: c.abandonedAt
                });
                if (c.callStatus === 'converted') {
                    events.push({
                        type: 'order',
                        icon: 'fa-check-circle',
                        title: 'Cart converted to order',
                        desc: `Order #${c.orderId || 'N/A'}`,
                        time: c.lastUpdated
                    });
                }
            });

            // Add SMS events
            customerSms.forEach(s => {
                events.push({
                    type: 'sms',
                    icon: 'fa-comment-sms',
                    title: s.status === 'sent' ? 'SMS sent' : 'SMS queued',
                    desc: s.message?.substring(0, 50) + '...',
                    time: s.date
                });
            });

            events.sort((a, b) => new Date(b.time) - new Date(a.time));

            if (events.length === 0) {
                container.innerHTML = '<div class="empty"><i class="fas fa-history"></i><p>No activity history</p></div>';
                return;
            }

            container.innerHTML = `
                <div class="call-timeline">
                    ${events.map(e => `
                        <div class="call-log-item ${e.statusClass || e.type}">
                            <div class="call-log-header">
                                <span class="call-log-status">${e.type === 'call' ? '' : '<i class="fas ' + e.icon + '"></i> '}${esc(e.title)}</span>
                                <span class="call-log-time">${timeAgo(e.time)}</span>
                            </div>
                            ${e.agent ? `<div class="call-log-agent">Agent: ${esc(e.agent)}</div>` : ''}
                            <div class="call-log-notes">${esc(e.desc)}</div>
                            ${e.extra ? `<div class="call-log-duration"><i class="fas fa-clock"></i> ${e.extra}</div>` : ''}
                            ${e.callback ? `<div class="call-log-callback"><i class="fas fa-bell"></i> Callback: ${formatDateTime(e.callback)}</div>` : ''}
                        </div>
                    `).join('')}
                </div>
            `;
        };

        // Add "Log Call" button to Customer 360°
        const originalOpenCustomer360 = openCustomer360;
        openCustomer360 = function(customer) {
            originalOpenCustomer360(customer);

            // Add log call button if not exists
            setTimeout(() => {
                const header = document.querySelector('.c360-header');
                if (header && !document.getElementById('c360LogCallBtn')) {
                    const btn = document.createElement('button');
                    btn.id = 'c360LogCallBtn';
                    btn.className = 'btn btn-save';
                    btn.style.marginLeft = 'auto';
                    btn.innerHTML = '<i class="fas fa-phone"></i> Log Call';
                    btn.onclick = () => openCallLogModal(currentCustomer);
                    header.appendChild(btn);
                }
            }, 100);
        };

        // ========== ENKRATNI KUPCI SETTINGS ==========
        let buyersSettings = { minDaysFromPurchase: 10 };

        async function loadBuyersSettings() {
            try {
                const res = await fetch('api.php?action=buyers-settings');
                const data = await res.json();
                if (data.success && data.settings) {
                    buyersSettings = data.settings;
                    document.getElementById('buyersMinDays').value = buyersSettings.minDaysFromPurchase !== undefined ? buyersSettings.minDaysFromPurchase : 10;
                }
            } catch (e) {
                console.error('Failed to load buyers settings:', e);
            }
        }

        async function saveBuyersSettings() {
            const val = document.getElementById('buyersMinDays').value;
            const minDays = val !== '' ? parseInt(val) : 10;
            buyersSettings.minDaysFromPurchase = minDays;

            try {
                const res = await fetch('api.php?action=buyers-settings-save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ settings: buyersSettings })
                });
                const data = await res.json();
                console.log('[BuyersSettings] Save response:', data);
                if (data.success) {
                    showToast('Nastavitve shranjene! (' + minDays + ' dni) Osvežujem podatke...');
                    // Clear cache and reload buyers data with new settings
                    await fetch('api.php?action=clear-cache');
                    await fetch('api.php?action=refresh-buyers-cache');
                    loadBuyers();
                } else {
                    showToast(data.error || 'Napaka pri shranjevanju', 'error');
                    console.error('[BuyersSettings] Save failed:', data);
                }
            } catch (e) {
                showToast('Napaka: ' + e.message, true);
            }
        }

        // ========== PAKETOMATI FUNCTIONS ==========

        async function loadPaketomati() {
            const filter = document.getElementById('paketomatFilter')?.value || 'all';
            console.log('[Paketomati] Loading with filter:', filter);

            // Show loading state
            document.getElementById('paketomatiTableBody').innerHTML = `
                <tr><td colspan="9" style="padding:40px;text-align:center;">
                    <div class="spinner" style="margin:0 auto 16px;"></div>
                    <div style="color:var(--text-muted);">Nalagam podatke iz MetaKocka...</div>
                </td></tr>
            `;

            const result = await apiFetch(`api.php?action=paketomati&filter=${filter}`, {
                component: 'Paketomati',
                timeout: 30000, // 30s timeout for MetaKocka
                retries: 2
            });

            if (result.success && Array.isArray(result.data)) {
                paketomatiData = result.data;
                console.log('[Paketomati] ✓ Loaded', paketomatiData.length, 'orders');
                renderPaketomatiTable();
                updatePaketomatiCount();
            } else {
                console.error('[Paketomati] ✗ Failed:', result.error);
                paketomatiData = [];
                document.getElementById('paketomatiTableBody').innerHTML = `
                    <tr><td colspan="9" style="padding:40px;text-align:center;">
                        <i class="fas fa-exclamation-triangle" style="font-size:32px;color:var(--accent-red);margin-bottom:12px;display:block;"></i>
                        <div style="color:var(--accent-red);margin-bottom:12px;">Napaka pri nalaganju: ${result.error || 'Unknown error'}</div>
                        <button class="btn btn-save" onclick="loadPaketomati()" style="margin-top:8px;">
                            <i class="fas fa-redo"></i> Poskusi znova
                        </button>
                    </td></tr>
                `;
            }
        }

        function renderPaketomatiTable() {
            const statusFilter = document.getElementById('paketomatStatusFilter')?.value || 'all';
            let filtered = paketomatiData;

            // Filter by country
            if (currentStore !== 'all') {
                filtered = filtered.filter(o => o.storeCode === currentStore);
            }

            // Filter by status
            if (statusFilter !== 'all') {
                filtered = filtered.filter(o => o.status === statusFilter);
            }

            if (filtered.length === 0) {
                console.log('[Paketomati] No orders found. Total data:', paketomatiData.length, 'Filter:', statusFilter);
                const typeFilter = document.getElementById('paketomatFilter')?.value || 'all';
                let msg;
                if (paketomatiData.length === 0) {
                    if (typeFilter === 'all_orders') {
                        msg = '<tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:40px;"><i class="fas fa-box" style="font-size:32px;margin-bottom:12px;opacity:0.5;display:block;"></i>Ni naročil v MetaKocka<br><small style="font-size:12px;">Preveri MetaKocka za stanje naročil</small></td></tr>';
                    } else {
                        msg = '<tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:40px;"><i class="fas fa-box" style="font-size:32px;margin-bottom:12px;opacity:0.5;display:block;"></i>🎉 Ni paketov, ki čakajo na prevzem<br><small style="font-size:12px;">Noben paket trenutno ni v paketomatu</small></td></tr>';
                    }
                } else {
                    msg = '<tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:40px;">Ni naročil za izbran filter</td></tr>';
                }
                document.getElementById('paketomatiTableBody').innerHTML = msg;
                return;
            }

            const html = filtered.map(order => {
                const statusColors = {
                    not_called: 'var(--text-muted)',
                    called: 'var(--accent-orange)',
                    notified: 'var(--accent-green)'
                };
                const statusLabels = {
                    not_called: 'Ni poklicano',
                    called: 'Poklicano',
                    notified: 'Obveščeno'
                };

                // Format last event date
                const eventDate = order.lastEventDate ? new Date(order.lastEventDate).toLocaleDateString('sl-SI') : '';
                const eventTitle = order.lastDeliveryEvent + (eventDate ? ` (${eventDate})` : '');

                return `
                    <tr>
                        <td>
                            <strong>${esc(order.orderNumber)}</strong>
                            ${order.trackingCode ? `<br><small style="color:var(--text-muted);font-size:11px;">📦 ${esc(order.trackingCode)}</small>` : ''}
                        </td>
                        <td>
                            ${esc(order.customerName)}
                            <br><small style="color:var(--text-muted);font-size:11px;">${esc(order.city || '')} ${order.country || ''}</small>
                        </td>
                        <td>
                            ${order.phone ? `
                                <a href="tel:${order.phone}" class="action-btn-mini" title="Pokliči">
                                    <i class="fas fa-phone"></i> ${esc(order.phone)}
                                </a>
                            ` : '<span style="color:var(--text-muted)">-</span>'}
                        </td>
                        <td>
                            ${order.email ? `<a href="mailto:${order.email}" style="color:var(--accent-blue);">${esc(order.email)}</a>` : '-'}
                        </td>
                        <td><span style="background:var(--card-border);padding:4px 8px;border-radius:4px;font-size:12px;">${esc(order.deliveryService || '-')}</span></td>
                        <td style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${esc(eventTitle)}">
                            <span style="color:var(--accent-green);font-weight:500;">📍 ${esc(order.lastDeliveryEvent || '-')}</span>
                            ${eventDate ? `<br><small style="color:var(--text-muted);font-size:11px;">${eventDate}</small>` : ''}
                        </td>
                        <td><strong>${order.orderTotal.toFixed(2)} ${order.currency}</strong></td>
                        <td>
                            <select onchange="updatePaketomatStatus('${order.id}', this.value)" style="background:var(--card-border);border:1px solid var(--card-border);border-radius:4px;padding:4px 8px;color:${statusColors[order.status]};cursor:pointer;">
                                <option value="not_called" ${order.status === 'not_called' ? 'selected' : ''}>Ni poklicano</option>
                                <option value="called" ${order.status === 'called' ? 'selected' : ''}>Poklicano</option>
                                <option value="notified" ${order.status === 'notified' ? 'selected' : ''}>Obveščeno</option>
                            </select>
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;">
                                ${order.phone ? `
                                    <button onclick="callCustomer('${order.phone}')" class="action-btn-mini" title="Pokliči">
                                        <i class="fas fa-phone"></i>
                                    </button>
                                    <button onclick="openSmsModal('${order.id}', '${escAttr(order.phone)}', '${escAttr(order.customerName)}', 'paketomat')" class="action-btn-mini" title="Pošlji SMS">
                                        <i class="fas fa-sms"></i>
                                    </button>
                                ` : ''}
                                <button onclick="showPaketomatNotes('${order.id}', '${escAttr(order.notes || '')}')" class="action-btn-mini" title="Opombe">
                                    <i class="fas fa-sticky-note"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');

            document.getElementById('paketomatiTableBody').innerHTML = html;
        }

        async function updatePaketomatStatus(orderId, status) {
            try {
                const order = paketomatiData.find(o => o.id === orderId);
                await fetch('api.php?action=paketomati-update', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: orderId, status: status, notes: order?.notes || ''})
                });

                // Update local data
                if (order) order.status = status;
                renderPaketomatiTable();
                showToast(`Status posodobljen: ${status}`);
            } catch (e) {
                showToast('Napaka pri posodabljanju statusa', 'error');
            }
        }

        function showPaketomatNotes(orderId, currentNotes) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.id = 'paketomatNotesModal';
            modal.innerHTML = `
                <div class="modal-content" style="max-width:500px;">
                    <div class="modal-header">
                        <h3>📝 Opombe</h3>
                        <button class="modal-close" onclick="document.getElementById('paketomatNotesModal').remove()">×</button>
                    </div>
                    <div class="modal-body">
                        <textarea id="paketomatNotesText" rows="5" style="width:100%;background:var(--content-bg);border:1px solid var(--card-border);border-radius:8px;padding:12px;color:var(--text-primary);resize:vertical;">${currentNotes}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-cancel" onclick="document.getElementById('paketomatNotesModal').remove()">Prekliči</button>
                        <button class="btn btn-save" onclick="savePaketomatNotes('${orderId}')">Shrani</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }

        async function savePaketomatNotes(orderId) {
            const notes = document.getElementById('paketomatNotesText').value;
            const order = paketomatiData.find(o => o.id === orderId);

            try {
                await fetch('api.php?action=paketomati-update', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: orderId, status: order?.status || 'not_called', notes: notes})
                });

                if (order) order.notes = notes;
                document.getElementById('paketomatNotesModal').remove();
                showToast('Opombe shranjene');
            } catch (e) {
                showToast('Napaka pri shranjevanju', 'error');
            }
        }

        async function refreshPaketomati() {
            console.log('[Paketomati] Refreshing...');
            await apiFetch('api.php?action=clear-cache', { component: 'ClearCache', silent: true, retries: 1 });
            await loadPaketomati();
            showToast('✓ Paketomati osveženi!');
        }

        function updatePaketomatiCount() {
            let filtered = paketomatiData.filter(o => o.status === 'not_called');
            // Filter by country if not 'all'
            if (currentStore !== 'all') {
                filtered = filtered.filter(o => o.storeCode === currentStore);
            }
            document.getElementById('contentCount-paketomati').textContent = filtered.length;
        }

        // Inline status change for paketomati (from select dropdown)
        async function updatePaketomatStatusFromSelect(selectEl) {
            const orderId = selectEl.dataset.id;
            const newStatus = selectEl.value;
            await updatePaketomatStatus(orderId, newStatus);
        }

        // Inline notes save for paketomati
        async function savePaketomatNotesInline(inputEl) {
            const orderId = inputEl.dataset.id;
            const notes = inputEl.value;
            const order = paketomatiData.find(o => o.id === orderId);

            try {
                await fetch('api.php?action=paketomati-update', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: orderId, status: order?.status || 'not_called', notes: notes})
                });

                if (order) order.notes = notes;
                inputEl.classList.toggle('has-notes', !!notes);
                showToast('✓ Opombe shranjene');
            } catch (e) {
                showToast('Napaka pri shranjevanju', 'error');
            }
        }

        // Open SMS modal for paketomati order
        function openSmsModalForPaketomat(orderId) {
            const order = paketomatiData.find(o => o.id === orderId);
            if (!order || !order.phone) {
                showToast('Ni telefonske številke', 'error');
                return;
            }
            // Use generic SMS modal with order data
            openSmsModal(orderId, 'paketomat');
        }

        // ========== URGENT LEADS FUNCTIONS ==========
        let urgentLeads = [];
        
        function loadUrgentLeads() {
            // Load from localStorage (per store)
            const key = `urgentLeads_${currentStore}`;
            const stored = localStorage.getItem(key);
            urgentLeads = stored ? JSON.parse(stored) : [];
            updateUrgentCount();
        }
        
        function saveUrgentLeads() {
            const key = `urgentLeads_${currentStore}`;
            localStorage.setItem(key, JSON.stringify(urgentLeads));
            updateUrgentCount();
        }
        
        function updateUrgentCount() {
            const uncalled = urgentLeads.filter(l => !l.called).length;
            const countEl = document.getElementById('contentCount-urgent');
            if (countEl) countEl.textContent = uncalled;
        }
        
        // Generate tracking URL based on delivery service and tracking code
        function getTrackingUrl(deliveryService, trackingCode) {
            if (!trackingCode) return '#';
            const service = (deliveryService || '').toLowerCase();
            const code = trackingCode.toUpperCase();
            
            // Detect carrier from tracking code suffix (international format)
            if (code.endsWith('HR')) return `https://posiljka.posta.hr/?broj=${trackingCode}`;
            if (code.endsWith('SI')) return `https://sledenje.posta.si/?id=${trackingCode}`;
            if (code.endsWith('CZ')) return `https://www.postaonline.cz/trackandtrace/-/zasilka/cislo?parcelNumbers=${trackingCode}`;
            if (code.endsWith('PL')) return `https://emonitoring.poczta-polska.pl/?numer=${trackingCode}`;
            if (code.endsWith('HU')) return `https://posta.hu/nyomkovetes?searchvalue=${trackingCode}`;
            if (code.endsWith('GR')) return `https://www.elta.gr/en-us/trackyourshipment.aspx?code=${trackingCode}`;
            if (code.endsWith('IT')) return `https://www.poste.it/cerca/index.html#/risultati-ricerca-702702702/${trackingCode}`;
            if (code.endsWith('SK')) return `https://tandt.posta.sk/?zession=${trackingCode}`;
            
            // Detect carrier from delivery service name
            if (service.includes('gls')) return `https://gls-group.com/EU/en/parcel-tracking?match=${trackingCode}`;
            if (service.includes('dpd')) return `https://tracking.dpd.de/status/en_D/parcel/${trackingCode}`;
            if (service.includes('inpost')) return `https://inpost.pl/sledzenie-przesylek?number=${trackingCode}`;
            if (service.includes('packeta') || service.includes('expedico') || service.includes('zásilkovna')) return `https://tracking.packeta.com/en/?id=${trackingCode}`;
            if (service.includes('ppl')) return `https://www.ppl.cz/vyhledat-zasilku?shipmentId=${trackingCode}`;
            if (service.includes('overseas')) return `https://www.overseas.hr/pracenje-posiljke?code=${trackingCode}`;
            if (service.includes('hr pošta') || service.includes('hr posta') || service.includes('hrvatska')) return `https://posiljka.posta.hr/?broj=${trackingCode}`;
            
            // Default - use 17track universal tracker
            return `https://t.17track.net/en#nums=${trackingCode}`;
        }
        
        async function renderPaketomatiInline() {
            const container = document.getElementById('tableContainer');
            if (!container) {
                console.error('[Paketomati] tableContainer not found!');
                return;
            }
            
            // Make sure container is visible
            container.style.display = 'block';
            container.innerHTML = '<div class="loading"><div class="spinner"></div>Nalagam paketomati...</div>';
            
            // Load data if not loaded
            if (!paketomatiData || paketomatiData.length === 0) {
                await loadPaketomati();
            }
            
            if (!paketomatiData || paketomatiData.length === 0) {
                container.innerHTML = `
                    <div class="empty" style="padding:60px;text-align:center;">
                        <i class="fas fa-box" style="font-size:48px;opacity:0.3;margin-bottom:16px;display:block;"></i>
                        <p style="font-size:16px;margin-bottom:8px;">Ni paketov v paketomatih</p>
                        <small style="color:var(--text-muted);">Noben paket trenutno ne čaka na prevzem</small>
                    </div>`;
                return;
            }
            
            const sym = (curr) => ({EUR:'€',CZK:'Kč',PLN:'zł',HUF:'Ft'}[curr] || '€');
            
            // Filter by country
            let displayData = paketomatiData;
            if (currentStore !== 'all') {
                displayData = paketomatiData.filter(o => o.storeCode === currentStore);
                console.log('[Paketomati Render] Filtering by', currentStore, '- showing', displayData.length, 'of', paketomatiData.length);
            }
            
            container.innerHTML = `
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th>Customer</th>
                        <th>Order</th>
                        <th>Items</th>
                        <th>Value</th>
                        <th>Phone</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th style="text-align:right;">Actions</th>
                    </tr></thead>
                    <tbody>
                        ${displayData.map(order => `
                            <tr>
                                <td>
                                    <div class="customer-cell">
                                        <div class="avatar">${order.customerName ? order.customerName.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase() : '?'}</div>
                                        <div>
                                            <div class="customer-name">${esc(order.customerName)}</div>
                                            <div class="customer-email" style="font-size:11px;">${esc(order.address || '')}${order.address && order.city ? ', ' : ''}${esc(order.city || '')} ${esc(order.postcode || '')}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>#${esc(order.orderNumber)}</strong>
                                    ${order.trackingCode ? `<br><a href="${order.trackingLink || getTrackingUrl(order.deliveryService, order.trackingCode)}" target="_blank" style="color:var(--accent-blue);font-size:11px;text-decoration:none;">📦 ${esc(order.trackingCode)} ↗</a>` : ''}
                                </td>
                                <td style="font-size:11px;max-width:200px;">
                                    ${(order.items || []).length > 0 
                                        ? order.items.map(item => `<div style="margin-bottom:2px;" title="${esc(item.variant || '')}">
                                            <strong>${item.quantity}x</strong> ${esc(item.name)}
                                          </div>`).join('')
                                        : '<span style="color:var(--text-muted);">-</span>'}
                                </td>
                                <td><strong>${sym(order.currency)}${(order.orderTotal||0).toFixed(2)}</strong></td>
                                <td>${order.phone ? `<a href="tel:${order.phone}" class="phone-link"><i class="fas fa-phone"></i> ${esc(order.phone)}</a>` : '-'}</td>
                                <td>
                                    <span class="badge">${esc(order.deliveryService || '-')}</span>
                                    ${order.lastDeliveryEvent ? `<br><small style="color:var(--text-muted);">${esc(order.lastDeliveryEvent)}</small>` : ''}
                                </td>
                                <td>
                                    <select class="inline-status-select" data-id="${order.id}" onchange="updatePaketomatStatusFromSelect(this)">
                                        <option value="not_called" ${order.status==='not_called'?'selected':''}>⚪ Čaka</option>
                                        <option value="called_1" ${order.status==='called_1'?'selected':''}>📞 Poklicano 1x</option>
                                        <option value="called_2" ${order.status==='called_2'?'selected':''}>📞 Poklicano 2x</option>
                                        <option value="called_3" ${order.status==='called_3'?'selected':''}>📞 Poklicano 3x</option>
                                        <option value="sms_1" ${order.status==='sms_1'?'selected':''}>💬 SMS 1x</option>
                                        <option value="sms_2" ${order.status==='sms_2'?'selected':''}>💬 SMS 2x</option>
                                        <option value="sms_3" ${order.status==='sms_3'?'selected':''}>💬 SMS 3x</option>
                                        <option value="notified" ${order.status==='notified'?'selected':''}>✅ Obveščeno</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="inline-notes-wrapper">
                                        <input type="text" class="inline-notes-input ${order.notes ? 'has-notes' : ''}"
                                               data-id="${order.id}"
                                               value="${order.notes ? esc(order.notes) : ''}"
                                               placeholder="Add notes..."
                                               onkeypress="if(event.key==='Enter'){savePaketomatNotesInline(this)}">
                                        <button class="inline-notes-save" onclick="savePaketomatNotesInline(this.previousElementSibling)" title="Save">💾</button>
                                    </div>
                                </td>
                                <td style="white-space:nowrap;text-align:right;">
                                    ${order.phone ? `<button class="action-btn call" onclick="call('${order.phone}')" title="Call"><i class="fas fa-phone"></i></button>` : ''}
                                    ${order.phone ? `<button class="action-btn sms" onclick="openSmsModalForPaketomat('${order.id}')" title="Send SMS"><i class="fas fa-comment-sms"></i></button>` : ''}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                </div>
            `;
            
            updatePaketomatiCount();
        }
        
        function cyclePaketomatStatus(orderId) {
            const order = paketomatiData.find(o => o.id === orderId);
            if (!order) return;
            const states = ['not_called', 'called', 'notified'];
            const currentIdx = states.indexOf(order.status);
            const nextStatus = states[(currentIdx + 1) % states.length];
            updatePaketomatStatus(orderId, nextStatus);
        }

        function renderUrgentTableInline() {
            loadUrgentLeads();
            const container = document.getElementById('tableContainer');
            if (!container) {
                console.error('[Urgent] tableContainer not found!');
                return;
            }
            
            // Show urgent action bar
            const actionBar = document.getElementById('urgentActionBar');
            if (actionBar) actionBar.style.display = 'block';
            
            // Make sure container is visible
            container.style.display = 'block';
            
            if (!urgentLeads || !urgentLeads.length) {
                container.innerHTML = `
                    <div class="empty" style="padding:60px;text-align:center;">
                        <i class="fas fa-phone-slash" style="font-size:48px;opacity:0.3;margin-bottom:16px;display:block;"></i>
                        <p style="font-size:16px;margin-bottom:8px;">Ni nujnih leadov</p>
                        <small style="color:var(--text-muted);">Klikni <strong>+ Dodaj</strong> za vnos novega leada</small>
                    </div>`;
                return;
            }
            
            container.innerHTML = `
                <div class="table-wrapper">
                <table class="data-table">
                    <thead><tr>
                        <th class="checkbox-cell"><input type="checkbox" id="urgentSelectAll" onchange="toggleAllUrgent(this)"></th>
                        <th>Telefon</th>
                        <th>Navodilo / Razlog</th>
                        <th>Dodano</th>
                        <th>Rešeno</th>
                        <th>Kako rešeno</th>
                        <th>Akcije</th>
                    </tr></thead>
                    <tbody>
                        ${urgentLeads.map((lead, idx) => `
                            <tr data-idx="${idx}" style="${lead.resolved ? 'background:rgba(34,197,94,0.15);' : ''}">
                                <td class="checkbox-cell"><input type="checkbox" class="urgent-checkbox row-checkbox" data-idx="${idx}" onchange="updateUrgentSelection()"></td>
                                <td>
                                    <div style="font-weight:500;">${esc(lead.phone)}</div>
                                    <a href="tel:${lead.phone}" class="action-btn" title="Pokliči"><i class="fas fa-phone"></i></a>
                                </td>
                                <td style="max-width:250px;">
                                    <div class="note-text">${esc(lead.note)}</div>
                                </td>
                                <td style="font-size:12px;color:var(--text-muted);">
                                    ${new Date(lead.addedAt).toLocaleString('sl-SI', {day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit'})}
                                </td>
                                <td style="text-align:center;">
                                    <input type="checkbox" ${lead.resolved ? 'checked' : ''} onchange="toggleUrgentResolved(${idx}, this.checked)" style="width:18px;height:18px;cursor:pointer;">
                                </td>
                                <td>
                                    <div class="inline-notes-wrapper">
                                        <input type="text" class="inline-notes-input ${lead.resolution ? 'has-notes' : ''}"
                                               data-idx="${idx}"
                                               value="${lead.resolution ? esc(lead.resolution) : ''}"
                                               placeholder="Kako rešeno..."
                                               onkeypress="if(event.key==='Enter'){saveUrgentResolution(${idx}, this.value)}">
                                        <button class="inline-notes-save" onclick="saveUrgentResolution(${idx}, this.previousElementSibling.value)" title="Shrani">💾</button>
                                    </div>
                                </td>
                                <td>
                                    <button class="action-btn" onclick="editUrgentLead(${idx})" title="Uredi"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn" onclick="deleteUrgentLead(${idx})" title="Izbriši"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                </div>
            `;
            
            updateUrgentCount();
        }
        
        function showAddUrgentModal(editIdx = null) {
            document.getElementById('urgentPhone').value = '';
            document.getElementById('urgentNote').value = '';
            document.getElementById('addUrgentModal').dataset.editIdx = editIdx !== null ? editIdx : '';
            
            if (editIdx !== null && urgentLeads[editIdx]) {
                document.getElementById('urgentPhone').value = urgentLeads[editIdx].phone;
                document.getElementById('urgentNote').value = urgentLeads[editIdx].note;
            }
            
            document.getElementById('addUrgentModal').style.display = 'flex';
            document.getElementById('urgentPhone').focus();
        }
        
        function closeAddUrgentModal() {
            document.getElementById('addUrgentModal').style.display = 'none';
        }
        
        function saveUrgentLead() {
            const phone = document.getElementById('urgentPhone').value.trim();
            const note = document.getElementById('urgentNote').value.trim();
            const editIdx = document.getElementById('addUrgentModal').dataset.editIdx;
            
            if (!phone) {
                showToast('❌ Vnesi telefonsko številko!', true);
                return;
            }
            if (!note) {
                showToast('❌ Vnesi navodilo/razlog!', true);
                return;
            }
            
            if (editIdx !== '') {
                // Edit existing
                urgentLeads[parseInt(editIdx)].phone = phone;
                urgentLeads[parseInt(editIdx)].note = note;
                showToast('✅ Lead posodobljen');
            } else {
                // Add new
                urgentLeads.unshift({
                    id: Date.now(),
                    phone: phone,
                    note: note,
                    addedAt: new Date().toISOString(),
                    called: false
                });
                showToast('✅ Lead dodan');
            }
            
            saveUrgentLeads();
            closeAddUrgentModal();
            renderUrgentTableInline();
        }
        
        function editUrgentLead(idx) {
            showAddUrgentModal(idx);
        }
        
        function deleteUrgentLead(idx) {
            if (!confirm('Ali res želiš izbrisati ta lead?')) return;
            urgentLeads.splice(idx, 1);
            saveUrgentLeads();
            renderUrgentTableInline();
            showToast('Lead izbrisan');
        }
        
        function toggleUrgentCalled(idx, called) {
            urgentLeads[idx].called = called;
            if (called) urgentLeads[idx].calledAt = new Date().toISOString();
            saveUrgentLeads();
            renderUrgentTableInline();
        }
        
        function toggleUrgentResolved(idx, resolved) {
            urgentLeads[idx].resolved = resolved;
            if (resolved) urgentLeads[idx].resolvedAt = new Date().toISOString();
            saveUrgentLeads();
            renderUrgentTableInline();
            showToast(resolved ? '✓ Označeno kot rešeno' : 'Oznaka rešeno odstranjena');
        }
        
        function saveUrgentResolution(idx, resolution) {
            urgentLeads[idx].resolution = resolution;
            saveUrgentLeads();
            showToast('✓ Rešitev shranjena');
            // Don't re-render, just update the class
            const input = document.querySelector(`input.inline-notes-input[data-idx="${idx}"]`);
            if (input) input.classList.toggle('has-notes', !!resolution);
        }
        
        function updateUrgentSelection() {
            const checked = document.querySelectorAll('.urgent-checkbox:checked').length;
            const bulkActions = document.getElementById('urgentBulkActions');
            document.getElementById('urgentSelectedCount').textContent = checked;
            bulkActions.style.display = checked > 0 ? 'flex' : 'none';
        }
        
        function toggleAllUrgent(el) {
            document.querySelectorAll('.urgent-checkbox').forEach(cb => cb.checked = el.checked);
            updateUrgentSelection();
        }
        
        function markSelectedUrgentCalled() {
            document.querySelectorAll('.urgent-checkbox:checked').forEach(cb => {
                const idx = parseInt(cb.dataset.idx);
                urgentLeads[idx].called = true;
                urgentLeads[idx].calledAt = new Date().toISOString();
            });
            saveUrgentLeads();
            renderUrgentTableInline();
            showToast('Označeno kot poklicano');
        }
        
        function deleteSelectedUrgent() {
            const selected = [...document.querySelectorAll('.urgent-checkbox:checked')].map(cb => parseInt(cb.dataset.idx));
            if (!selected.length) return;
            if (!confirm(`Ali res želiš izbrisati ${selected.length} leadov?`)) return;
            
            // Delete from highest index to lowest to preserve indices
            selected.sort((a, b) => b - a).forEach(idx => urgentLeads.splice(idx, 1));
            saveUrgentLeads();
            renderUrgentTableInline();
            showToast(`${selected.length} leadov izbrisanih`);
        }
        
        // Load urgent leads on init and store change
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(loadUrgentLeads, 100);
        });

        // ========== REAL-TIME NOTIFICATION SYSTEM ==========
        let notificationSettings = {
            desktopEnabled: true,
            soundEnabled: true,
            pollingInterval: 30000
        };
        let pollInterval = null;
        let notificationSound = null;

        async function initNotificationSystem() {
            // Load settings
            try {
                const res = await fetch('api.php?action=notification-settings');
                notificationSettings = await res.json();
            } catch (e) {
                console.log('Using default notification settings');
            }

            // Update UI
            document.getElementById('notifDesktopToggle').checked = notificationSettings.desktopEnabled;
            document.getElementById('notifSoundToggle').checked = notificationSettings.soundEnabled;
            document.getElementById('notifPollingInterval').value = notificationSettings.pollingInterval;

            // Request notification permission
            if (notificationSettings.desktopEnabled && Notification.permission === 'default') {
                Notification.requestPermission();
            }

            // Create notification sound
            createNotificationSound();

            // Start polling
            startPolling();

            // Mark current items as seen
            await markCurrentItemsSeen();
        }

        function createNotificationSound() {
            // Create a simple beep using Web Audio API
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                notificationSound = () => {
                    if (!notificationSettings.soundEnabled) return;
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    oscillator.frequency.value = 800;
                    oscillator.type = 'sine';
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.3);
                };
            } catch (e) {
                console.log('Audio not supported');
            }
        }

        function startPolling() {
            if (pollInterval) clearInterval(pollInterval);
            pollInterval = setInterval(pollForNewItems, notificationSettings.pollingInterval);
        }

        async function pollForNewItems() {
            try {
                const userId = currentUser?.id || 'default';
                const res = await fetch(`api.php?action=poll-new&userId=${userId}`);
                const data = await res.json();

                // Update badge counts
                document.getElementById('navCarts').textContent = data.totalCarts;
                // Paketomati count - filter by country locally (don't use server's unfiltered total)
                updateContentTabCounts();

                // Show notifications for new items
                if (data.newCarts.length > 0 || data.newPaketomati.length > 0) {
                    // Update badge with "new" indicator
                    if (data.newCarts.length > 0) {
                        const cartsBadge = document.getElementById('navCarts');
                        cartsBadge.style.background = 'var(--accent-red)';
                        cartsBadge.style.color = 'white';
                    }
                    if (data.newPaketomati.length > 0) {
                        const paketBadge = document.getElementById('contentCount-paketomati');
                        paketBadge.style.background = 'var(--accent-red)';
                        paketBadge.style.color = 'white';
                    }

                    // Play sound
                    if (notificationSound) notificationSound();

                    // Show desktop notifications
                    if (notificationSettings.desktopEnabled && Notification.permission === 'granted') {
                        if (data.newCarts.length > 0) {
                            const cart = data.newCarts[0];
                            const notif = new Notification('🛒 Nova zapuščena košarica!', {
                                body: `${cart.customerName} - ${cart.cartValue.toFixed(2)} ${cart.currency}`,
                                icon: '/favicon.ico',
                                tag: 'cart-' + cart.id
                            });
                            notif.onclick = () => {
                                window.focus();
                                document.querySelector('[data-tab="carts"]').click();
                                notif.close();
                            };
                        }

                        if (data.newPaketomati.length > 0) {
                            const order = data.newPaketomati[0];
                            const notif = new Notification('📦 Novo paketomat naročilo!', {
                                body: `${order.customerName} - ${order.orderTotal.toFixed(2)} ${order.currency}`,
                                icon: '/favicon.ico',
                                tag: 'paketomat-' + order.id
                            });
                            notif.onclick = () => {
                                window.focus();
                                document.querySelector('[data-tab="paketomati"]').click();
                                notif.close();
                            };
                        }
                    }

                    // Mark items as seen
                    await markItemsSeen(
                        data.newCarts.map(c => c.id),
                        data.newPaketomati.map(p => p.id)
                    );
                }
            } catch (e) {
                console.log('Poll error:', e);
            }
        }

        async function markCurrentItemsSeen() {
            try {
                const userId = currentUser?.id || 'default';
                const [cartsRes, paketRes] = await Promise.all([
                    fetch('api.php?action=abandoned-carts'),
                    fetch('api.php?action=paketomati&filter=all')
                ]);
                const currentCarts = await cartsRes.json();
                const currentPaketomati = await paketRes.json();

                await markItemsSeen(
                    currentCarts.map(c => c.id),
                    currentPaketomati.map(p => p.id)
                );
            } catch (e) {
                console.log('Mark seen error:', e);
            }
        }

        async function markItemsSeen(cartIds, paketomatIds) {
            try {
                const userId = currentUser?.id || 'default';
                await fetch('api.php?action=mark-seen', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({userId, cartIds, paketomatIds})
                });
            } catch (e) {
                console.log('Mark seen error:', e);
            }
        }

        async function saveNotificationSettings() {
            notificationSettings = {
                desktopEnabled: document.getElementById('notifDesktopToggle').checked,
                soundEnabled: document.getElementById('notifSoundToggle').checked,
                pollingInterval: parseInt(document.getElementById('notifPollingInterval').value)
            };

            // Request permission if enabling desktop notifications
            if (notificationSettings.desktopEnabled && Notification.permission === 'default') {
                await Notification.requestPermission();
            }

            // Save to server
            try {
                await fetch('api.php?action=notification-settings', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(notificationSettings)
                });
            } catch (e) {}

            // Restart polling with new interval
            startPolling();
            showToast('Notification settings saved');
        }

        function openNotificationSettings() {
            document.getElementById('notificationSettingsModal').style.display = 'flex';
        }

        function closeNotificationSettings() {
            document.getElementById('notificationSettingsModal').style.display = 'none';
        }

        function testNotification() {
            if (notificationSound) notificationSound();

            if (Notification.permission === 'granted') {
                new Notification('🔔 Test Notification', {
                    body: 'Obvestila delujejo pravilno!',
                    icon: '/favicon.ico'
                });
            } else if (Notification.permission === 'default') {
                Notification.requestPermission().then(perm => {
                    if (perm === 'granted') {
                        new Notification('🔔 Test Notification', {
                            body: 'Obvestila delujejo pravilno!',
                            icon: '/favicon.ico'
                        });
                    }
                });
            } else {
                showToast('Desktop notifications so blokirane v brskalniku', 'error');
            }
        }

        // Add notification bell to top bar
        function addNotificationBell() {
            const topBarActions = document.querySelector('.top-bar-actions');
            if (topBarActions && !document.getElementById('notificationBellBtn')) {
                const bellBtn = document.createElement('button');
                bellBtn.id = 'notificationBellBtn';
                bellBtn.className = 'action-btn-header';
                bellBtn.innerHTML = '<i class="fas fa-bell"></i>';
                bellBtn.title = 'Notification Settings';
                bellBtn.onclick = openNotificationSettings;
                topBarActions.insertBefore(bellBtn, topBarActions.firstChild);
            }
        }

        // Reset badge color when tab is visited
        document.querySelectorAll('.nav-item[data-tab]').forEach(item => {
            item.addEventListener('click', () => {
                const tab = item.dataset.tab;
                if (tab === 'carts') {
                    const badge = document.getElementById('navCarts');
                    badge.style.background = '';
                    badge.style.color = '';
                }
                if (tab === 'paketomati') {
                    const badge = document.getElementById('contentCount-paketomati');
                    badge.style.background = '';
                    badge.style.color = '';
                }
            });
        });

        // Initialize notification system after main init
        const originalInit = init;
        init = async function() {
            await originalInit();
            addNotificationBell();
            await initNotificationSystem();
        };

        init();
    </script>
</body>
</html>
