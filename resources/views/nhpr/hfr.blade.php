<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Health Facility Registry (HFR) Management | ABDM Portal</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Noto+Sans+Devanagari:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">

    <style>
        :root {
            /* Branding Tokens */
            --bg: #071221;
            --surface: #0a1628;
            --surface2: #0e1e32;
            --border: rgba(255, 255, 255, 0.08);
            --border2: rgba(255, 255, 255, 0.12);
            --text: #e8f0fe;
            --muted: #7b9bbf;
            --muted2: #a0bbd8;
            --primary: #1565c0;
            --primary-light: #60a5fa;
            --success: #2e7d32;
            --success-light: #81c784;
            --warning: #f57c00;
            --warning-light: #ffb74d;
            --danger: #c62828;
            --danger-light: #ef5350;
            --saffron: #e65100;
            --gold: #f9a825;

            --sidebar-w: 250px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* App Shell */
        .app-shell {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar styling */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--surface);
            border-right: 1px solid var(--border);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 200;
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            padding: 20px 16px;
            border-bottom: 1px solid var(--border);
        }

        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-orb {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), #00695c);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: #fff;
            box-shadow: 0 0 15px rgba(21, 101, 192, 0.4);
        }

        .logo-txt .l1 {
            font-size: 13px;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .logo-txt .l2 {
            font-size: 9px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1px;
        }

        .sidebar-nav {
            padding: 20px 10px;
            flex: 1;
        }

        .nav-grp-title {
            font-size: 8.5px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.25);
            text-transform: uppercase;
            padding: 0 10px 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 8px;
            color: var(--muted2);
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            margin-bottom: 4px;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
        }

        .nav-item.active {
            background: rgba(21, 101, 192, 0.2);
            color: var(--primary-light);
            border-left: 3px solid var(--primary);
            font-weight: 600;
        }

        .nav-item i {
            width: 18px;
            text-align: center;
            font-size: 14px;
        }

        /* Main Content Container */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Gov Ribbon Header */
        .gov-topbar {
            background: #0b1a30;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 24px;
            flex-shrink: 0;
        }

        .gov-emblem {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .gov-emblem img {
            height: 38px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .gov-title-text {
            display: flex;
            flex-direction: column;
        }

        .gov-hindi {
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-size: 13.5px;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }

        .gov-english {
            font-size: 10px;
            font-weight: 600;
            color: var(--muted);
            letter-spacing: 0.5px;
        }

        .gateway-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            color: var(--muted2);
            background: rgba(255, 255, 255, 0.03);
            padding: 6px 12px;
            border-radius: 20px;
            border: 1px solid var(--border);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
            box-shadow: 0 0 8px var(--success-light);
        }

        /* Content Body */
        .content {
            padding: 30px;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            flex: 1;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--muted);
        }

        /* Tabs Nav */
        .tab-nav {
            display: flex;
            gap: 8px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 24px;
        }

        .tab-btn {
            background: none;
            border: none;
            color: var(--muted);
            padding: 12px 20px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.02);
        }

        .tab-btn.active {
            color: var(--primary-light);
            border-bottom-color: var(--primary);
        }

        /* Cards and Components */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: border-color 0.2s ease;
        }

        .card:hover {
            border-color: var(--border2);
        }

        .card-header {
            background: rgba(255, 255, 255, 0.02);
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-body {
            padding: 20px;
        }

        /* Forms styling */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .grid-2, .grid-3 {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            font-size: 12px;
            font-weight: 700;
            color: var(--muted2);
        }

        .req {
            color: var(--danger-light);
        }

        .form-control {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #fff;
            font-family: inherit;
            outline: none;
            width: 100%;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.15);
        }

        .form-control:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn {
            background: var(--primary);
            border: 1px solid transparent;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn:hover:not(:disabled) {
            background: #1976d2;
            transform: translateY(-1px);
        }

        .btn:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn.secondary {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--border);
            color: var(--text);
        }

        .btn.secondary:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Results table */
        .table-wrap {
            overflow-x: auto;
            margin-top: 15px;
            border: 1px solid var(--border);
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        th {
            background: rgba(255, 255, 255, 0.02);
            color: var(--muted2);
            font-weight: 700;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
            color: #fff;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.01);
        }

        /* Toast styles */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast-item {
            background: var(--surface2);
            border: 1px solid var(--border2);
            color: #fff;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 13px;
            min-width: 280px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease forwards;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-item.success {
            border-left: 4px solid var(--success-light);
        }

        .toast-item.error {
            border-left: 4px solid var(--danger-light);
        }

        .toast-item.info {
            border-left: 4px solid var(--primary-light);
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--muted);
            font-size: 13px;
        }

        .empty-state i {
            font-size: 32px;
            margin-bottom: 12px;
            display: block;
        }
    </style>
</head>

<body>

    <div id="toast-container"></div>

    <div class="app-shell">

        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-logo">
                <div class="logo-wrap">
                    <div class="logo-orb"><i class="fa-solid fa-heart-pulse"></i></div>
                    <div class="logo-txt">
                        <div class="l1">ParaCare+ HIMS</div>
                        <div class="l2">Uttarakhand Govt.</div>
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-grp-title">HIMS Clinical Care</div>
                <a href="{{ route('hip.dashboard') }}" class="nav-item">
                    <i class="fa-solid fa-notes-medical"></i> HIMS Records (HIP)
                </a>
                <a href="{{ route('hip.milestone2') }}" class="nav-item">
                    <i class="fa-solid fa-map-location-dot"></i> ABDM Milestone 2 Map
                </a>
                <a href="{{ route('hiu.dashboard') }}" class="nav-item">
                    <i class="fa-solid fa-shield-halved"></i> HIU Portal
                </a>
                <a href="{{ route('hip.consents') }}" class="nav-item">
                    <i class="fa-solid fa-file-shield"></i> Consent & Security Hub
                </a>

                <div class="nav-grp-title">ABDM Milestone 1</div>
                <a href="{{ route('abha.dashboard') }}" class="nav-item">
                    <i class="fa-solid fa-chart-line"></i> ABHA Dashboard
                </a>
                <a href="{{ route('abha.create') }}" class="nav-item">
                    <i class="fa-solid fa-user-plus"></i> Create ABHA Number
                </a>
                <a href="{{ route('abha.find') }}" class="nav-item">
                    <i class="fa-solid fa-magnifying-glass"></i> Find Existing ABHA
                </a>
                <a href="{{ route('abha.verify') }}" class="nav-item">
                    <i class="fa-solid fa-address-card"></i> Verify ABHA Address
                </a>

                <div class="nav-grp-title">HPR Onboarding</div>
                <a href="{{ route('nhpr.register.wizard') }}" class="nav-item">
                    <i class="fa-solid fa-user-doctor"></i> HPR Onboarding
                </a>
                <a href="{{ route('nhpr.hfr.index') }}" class="nav-item active">
                    <i class="fa-solid fa-building-circle-check"></i> HFR Management
                </a>
                <a href="{{ route('nhpr.token.show') }}" class="nav-item">
                    <i class="fa-solid fa-key"></i> Gateway Token
                </a>
                <a href="{{ route('nhpr.track.show') }}" class="nav-item">
                    <i class="fa-solid fa-binoculars"></i> Track Status
                </a>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main">

            <!-- Uttarakhand Government top bar -->
            <div class="gov-topbar">
                <div class="gov-emblem">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/9/99/Seal_of_Uttarakhand.svg"
                        alt="Govt Emblem">
                    <div class="gov-title-text">
                        <span class="gov-hindi">उत्तराखंड शासन</span>
                        <span class="gov-english">Government of Uttarakhand</span>
                    </div>
                </div>

                <div class="gateway-status">
                    <span class="status-dot"></span>
                    <span>ABDM Sandbox Mode</span>
                </div>
            </div>

            <!-- Page Body -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Health Facility Registry (HFR) Portal</h1>
                    <p class="page-subtitle">Search registered health facilities, register new health installations, or link bridge software credentials.</p>
                </div>

                <!-- Tabs Navigation -->
                <div class="tab-nav">
                    <button class="tab-btn active" data-tab="tab-search">
                        <i class="fa-solid fa-magnifying-glass"></i> Search Facilities
                    </button>
                    <button class="tab-btn" data-tab="tab-create">
                        <i class="fa-solid fa-circle-plus"></i> Register Facility
                    </button>
                    <button class="tab-btn" data-tab="tab-link">
                        <i class="fa-solid fa-link"></i> Link Bridge Software
                    </button>
                </div>

                <!-- TAB 1: Search HFR -->
                <div class="tab-panel active" id="tab-search">
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-search"></i> Search Registered Facilities</span>
                        </div>
                        <div class="card-body">
                            <form id="search-form">
                                <div class="grid-3">
                                    <div class="form-group">
                                        <label for="search-name">Facility Name</label>
                                        <input type="text" id="search-name" class="form-control" placeholder="e.g. Dehradun Hospital">
                                    </div>
                                    <div class="form-group">
                                        <label for="search-pincode">Pincode</label>
                                        <input type="text" id="search-pincode" class="form-control" maxlength="6" placeholder="e.g. 248001">
                                    </div>
                                    <div class="form-group">
                                        <label for="search-id">Facility ID</label>
                                        <input type="text" id="search-id" class="form-control" placeholder="e.g. IN2710000059">
                                    </div>
                                </div>
                                <div style="margin-top: 14px; text-align: right;">
                                    <button type="submit" class="btn" id="btn-search">
                                        <i class="fa-solid fa-magnifying-glass"></i> Search Registry
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Search Results Card -->
                    <div class="card" id="results-card" style="display: none;">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-list"></i> Search Results</span>
                        </div>
                        <div class="card-body">
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Facility ID</th>
                                            <th>Facility Name</th>
                                            <th>Address</th>
                                            <th>Pincode</th>
                                            <th>State</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="results-body">
                                        <!-- Loaded Dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Register New Facility -->
                <div class="tab-panel" id="tab-create">
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-hospital"></i> HFR Facility Registration Form</span>
                        </div>
                        <div class="card-body">
                            <form id="create-form">
                                <div class="grid-2">
                                    <div class="form-group">
                                        <label for="fac-name">Facility Name <span class="req">*</span></label>
                                        <input type="text" id="fac-name" class="form-control" placeholder="Official Facility Name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="fac-ownership">Ownership Type <span class="req">*</span></label>
                                        <select id="fac-ownership" class="form-control" required>
                                            <option value="Private">Private</option>
                                            <option value="Government">Government / Public</option>
                                            <option value="NGO">Non-Governmental Organization (NGO)</option>
                                            <option value="Trust">Charitable Trust</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid-2" style="margin-top: 14px;">
                                    <div class="form-group">
                                        <label for="fac-medicine">System of Medicine <span class="req">*</span></label>
                                        <select id="fac-medicine" class="form-control" required>
                                            <option value="Allopathy">Allopathy (Modern Medicine)</option>
                                            <option value="Ayurveda">Ayurveda</option>
                                            <option value="Homeopathy">Homeopathy</option>
                                            <option value="Unani">Unani</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="fac-type">Facility Category/Type <span class="req">*</span></label>
                                        <select id="fac-type" class="form-control" required>
                                            <option value="Hospital">Hospital</option>
                                            <option value="Clinic">Clinic / O.P.D.</option>
                                            <option value="Diagnostic Centre">Diagnostic Lab</option>
                                            <option value="Pharmacy">Pharmacy</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid-3" style="margin-top: 14px;">
                                    <div class="form-group">
                                        <label for="fac-pincode">Pincode <span class="req">*</span></label>
                                        <input type="text" id="fac-pincode" class="form-control" maxlength="6" placeholder="6-digit Pincode" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="fac-state">State LGD Code <span class="req">*</span></label>
                                        <input type="text" id="fac-state" class="form-control" placeholder="e.g. 05" value="05" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="fac-district">District LGD Code <span class="req">*</span></label>
                                        <input type="text" id="fac-district" class="form-control" placeholder="e.g. 060" value="060" required>
                                    </div>
                                </div>

                                <div class="form-group" style="margin-top: 14px;">
                                    <label for="fac-address">Complete Address <span class="req">*</span></label>
                                    <textarea id="fac-address" class="form-control" style="resize: vertical; min-height: 80px;" placeholder="Street address details" required></textarea>
                                </div>

                                <div class="grid-2" style="margin-top: 14px;">
                                    <div class="form-group">
                                        <label for="fac-contact">Nodal Contact Number <span class="req">*</span></label>
                                        <input type="tel" id="fac-contact" class="form-control" maxlength="10" placeholder="10-digit mobile number" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="fac-email">Nodal Email Address <span class="req">*</span></label>
                                        <input type="email" id="fac-email" class="form-control" placeholder="admin@facility.org" required>
                                    </div>
                                </div>

                                <div style="margin-top: 20px; text-align: right;">
                                    <button type="submit" class="btn" id="btn-create-fac">
                                        <i class="fa-solid fa-circle-check"></i> Register Facility
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: Link Bridge Software -->
                <div class="tab-panel" id="tab-link">
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-circle-nodes"></i> Software Bridge Facility Linkage</span>
                        </div>
                        <div class="card-body">
                            <form id="link-form">
                                <div class="form-group">
                                    <label for="link-facility-id">Health Facility ID (HFR ID) <span class="req">*</span></label>
                                    <input type="text" id="link-facility-id" class="form-control" placeholder="e.g. IN2710000059" required>
                                </div>
                                <div class="form-group">
                                    <label for="link-facility-name">Facility Name <span class="req">*</span></label>
                                    <input type="text" id="link-facility-name" class="form-control" placeholder="e.g. Dehradun Civil Hospital" required>
                                </div>
                                <div class="form-group">
                                    <label for="link-bridge-id">Bridge Client ID (HRP Software Client) <span class="req">*</span></label>
                                    <input type="text" id="link-bridge-id" class="form-control" value="{{ $defaultBridgeId }}" placeholder="Your Software Bridge Client ID" required>
                                    <span style="font-size: 11px; color: var(--muted);">Defaults to active gateway client ID.</span>
                                </div>

                                <div style="margin-top: 20px; text-align: right;">
                                    <button type="submit" class="btn" id="btn-link-fac">
                                        <i class="fa-solid fa-link"></i> Link Bridge Software
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // CSRF details
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Toast Notification System
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast-item ${type}`;

            let icon = 'fa-circle-check';
            if (type === 'error') icon = 'fa-circle-exclamation';
            if (type === 'info') icon = 'fa-circle-info';

            toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${message}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'slideIn 0.3s ease reverse forwards';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Tabs Toggle Logic
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabPanels = document.querySelectorAll('.tab-panel');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const targetTab = this.getAttribute('data-tab');

                tabBtns.forEach(b => b.classList.remove('active'));
                tabPanels.forEach(p => p.classList.remove('active'));

                this.classList.add('active');
                document.getElementById(targetTab).classList.add('active');
            });
        });

        // Search Action
        document.getElementById('search-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('btn-search');
            const facName = document.getElementById('search-name').value;
            const pincode = document.getElementById('search-pincode').value;
            const facId = document.getElementById('search-id').value;

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Searching...';

            fetch('{{ route("nhpr.hfr.search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    facilityName: facName,
                    pincode: pincode,
                    facilityId: facId
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Search Registry';

                if (data.success) {
                    const resultsCard = document.getElementById('results-card');
                    const resultsBody = document.getElementById('results-body');
                    resultsBody.innerHTML = '';

                    if (data.facilities.length === 0) {
                        resultsBody.innerHTML = `<tr><td colspan="6" class="empty-state"><i class="fa-solid fa-face-frown"></i> No health facilities found matching criteria.</td></tr>`;
                    } else {
                        data.facilities.forEach(fac => {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td><strong style="color: var(--primary-light);">${fac.facilityId}</strong></td>
                                <td>${fac.facilityName}</td>
                                <td>${fac.address || '—'}</td>
                                <td>${fac.pincode || '—'}</td>
                                <td>${fac.stateName || '—'}</td>
                                <td>
                                    <button class="btn secondary select-fac-btn" 
                                        style="padding: 4px 8px; font-size: 11px;"
                                        data-id="${fac.facilityId}"
                                        data-name="${fac.facilityName}">
                                        Select for Linkage
                                    </button>
                                </td>
                            `;
                            resultsBody.appendChild(tr);
                        });

                        // Bind selection buttons
                        document.querySelectorAll('.select-fac-btn').forEach(selBtn => {
                            selBtn.addEventListener('click', function() {
                                const id = this.getAttribute('data-id');
                                const name = this.getAttribute('data-name');
                                document.getElementById('link-facility-id').value = id;
                                document.getElementById('link-facility-name').value = name;
                                showToast(`Selected facility: ${name}`);
                                
                                // Auto switch to linkage tab
                                const tabLinkBtn = document.querySelector('.tab-btn[data-tab="tab-link"]');
                                tabLinkBtn.click();
                            });
                        });
                    }
                    resultsCard.style.display = 'block';
                    showToast(`Found ${data.facilities.length} health facilities.`);
                } else {
                    showToast(data.message || 'Search failed.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Search Registry';
                showToast('HFR search service error.', 'error');
            });
        });

        // Create/Register Action
        document.getElementById('create-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('btn-create-fac');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Registering...';

            const payload = {
                facilityName: document.getElementById('fac-name').value,
                ownershipCode: document.getElementById('fac-ownership').value,
                systemOfMedicineCode: document.getElementById('fac-medicine').value,
                facilityTypeCode: document.getElementById('fac-type').value,
                pincode: document.getElementById('fac-pincode').value,
                stateLGDCode: document.getElementById('fac-state').value,
                districtLGDCode: document.getElementById('fac-district').value,
                facilityAddress: document.getElementById('fac-address').value,
                contactNumber: document.getElementById('fac-contact').value,
                email: document.getElementById('fac-email').value,
            };

            fetch('{{ route("nhpr.hfr.create") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Register Facility';

                if (data.success) {
                    showToast(data.message || 'Facility registered successfully!');
                    document.getElementById('create-form').reset();
                    
                    // Set up link form with new details automatically
                    document.getElementById('link-facility-id').value = data.facilityId;
                    document.getElementById('link-facility-name').value = data.facilityName;
                    
                    // Auto switch to linkage tab
                    const tabLinkBtn = document.querySelector('.tab-btn[data-tab="tab-link"]');
                    tabLinkBtn.click();
                } else {
                    showToast(data.message || 'Registration failed.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-circle-check"></i> Register Facility';
                showToast('HFR registration service error.', 'error');
            });
        });

        // Link Action
        document.getElementById('link-form').addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('btn-link-fac');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Linking...';

            const payload = {
                facilityId: document.getElementById('link-facility-id').value,
                facilityName: document.getElementById('link-facility-name').value,
                bridgeId: document.getElementById('link-bridge-id').value,
            };

            fetch('{{ route("nhpr.hfr.link") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-link"></i> Link Bridge Software';

                if (data.success) {
                    showToast(data.message || 'Bridge linked successfully!');
                    document.getElementById('link-form').reset();
                } else {
                    showToast(data.message || 'Linkage failed.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-link"></i> Link Bridge Software';
                showToast('HFR linkage service error.', 'error');
            });
        });
    </script>
</body>

</html>
