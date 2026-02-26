<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">N</div>
        <div class="brand">
            Noriks
            <span>Call Center</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-title">Menu</div>
            <a href="index.php#leads" class="nav-item" data-tab="leads">
                <i class="fas fa-users"></i>
                <span>Leads</span>
            </a>
        </div>
        
        <div class="nav-section">
            <div class="nav-title">Calls</div>
            <a href="index.php#followups" class="nav-item" data-tab="followups">
                <i class="fas fa-phone-volume"></i>
                <span>My Follow-ups</span>
                <span class="badge followup-badge" id="navFollowups">0</span>
            </a>
        </div>
        
        <div class="nav-section admin-only" id="messagingSection">
            <div class="nav-title">Messaging</div>
            <a href="index.php#sms-automation" class="nav-item" data-tab="sms-automation">
                <i class="fas fa-robot"></i>
                <span>SMS Automation</span>
            </a>
            <a href="index.php#sms-dashboard" class="nav-item" data-tab="sms-dashboard">
                <i class="fas fa-comment-sms"></i>
                <span>SMS Dashboard</span>
                <span class="badge" id="navSms">0</span>
            </a>
            <a href="index.php#sms-settings" class="nav-item" data-tab="sms-settings">
                <i class="fas fa-cog"></i>
                <span>SMS Settings</span>
            </a>
            <a href="index.php#buyers-settings" class="nav-item" data-tab="buyers-settings">
                <i class="fas fa-user-cog"></i>
                <span>Options</span>
            </a>
        </div>
        
        <div class="nav-section admin-only" id="adminSection">
            <div class="nav-title">Admin</div>
            <a href="index.php#agents" class="nav-item" data-tab="agents">
                <i class="fas fa-users-cog"></i>
                <span>Agents</span>
            </a>
        </div>
        
        <div class="nav-section admin-only" id="reportsSection">
            <div class="nav-title">Reports</div>
            <a href="report.php" class="nav-item">
                <i class="fas fa-chart-bar"></i>
                <span>Statistics</span>
            </a>
        </div>
    </nav>
    
    <div class="sidebar-footer">
        <!-- Collapse Toggle Button -->
        <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" onclick="toggleSidebarCollapse()" title="Prikaži/Skrij sidebar">
            <i class="fas fa-chevron-left"></i>
            <span>Skrij meni</span>
        </button>
        
        <div class="user-card">
            <div class="user-avatar" id="userAvatar">N</div>
            <div class="user-info">
                <div class="user-name" id="userName">Noriks</div>
                <div class="user-role" id="userRole">Admin</div>
            </div>
            <button class="logout-btn" onclick="logout()" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>
</aside>

<style>
/* Sidebar Collapse Button */
.sidebar-collapse-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 12px 16px;
    margin-bottom: 12px;
    background: var(--sidebar-hover);
    border: 1px solid var(--sidebar-border);
    border-radius: var(--radius-md);
    color: var(--sidebar-text);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all var(--transition-normal);
}

.sidebar-collapse-btn:hover {
    background: var(--sidebar-active);
    color: var(--sidebar-text-active);
}

.sidebar-collapse-btn i {
    transition: transform 0.3s ease;
}

/* Collapsed Sidebar State */
.sidebar.collapsed {
    width: 70px;
}

.sidebar.collapsed .sidebar-header .brand {
    display: none;
}

.sidebar.collapsed .nav-title {
    display: none;
}

.sidebar.collapsed .nav-item span:not(.badge) {
    display: none;
}

.sidebar.collapsed .nav-item .badge {
    position: absolute;
    top: 2px;
    right: 2px;
    padding: 2px 5px;
    font-size: 9px;
    min-width: 16px;
}

.sidebar.collapsed .nav-item {
    justify-content: center;
    padding: 14px;
    position: relative;
}

.sidebar.collapsed .nav-item i {
    font-size: 18px;
}

.sidebar.collapsed .user-card {
    flex-direction: column;
    padding: 10px;
    gap: 8px;
}

.sidebar.collapsed .user-info {
    display: none;
}

.sidebar.collapsed .sidebar-collapse-btn {
    padding: 12px;
}

.sidebar.collapsed .sidebar-collapse-btn span {
    display: none;
}

.sidebar.collapsed .sidebar-collapse-btn i {
    transform: rotate(180deg);
}

/* Adjust main content when sidebar is collapsed */
.sidebar.collapsed ~ .main {
    margin-left: 70px;
}

.sidebar.collapsed ~ .top-bar {
    left: 70px;
}

/* Mobile: sidebar collapsed means hidden */
@media (max-width: 1024px) {
    .sidebar-collapse-btn {
        display: none;
    }
}
</style>

<script>
// Sidebar Collapse Toggle
function toggleSidebarCollapse() {
    const sidebar = document.getElementById('sidebar');
    const isCollapsed = sidebar.classList.toggle('collapsed');
    localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
}

// Restore sidebar state on load
function initSidebarState() {
    const isCollapsed = localStorage.getItem('sidebar_collapsed') === 'true';
    if (isCollapsed) {
        document.getElementById('sidebar').classList.add('collapsed');
    }
}

// Run on DOM load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSidebarState);
} else {
    initSidebarState();
}
</script>
