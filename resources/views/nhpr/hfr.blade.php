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
            background: var(--warning);
            box-shadow: 0 0 8px var(--warning-light);
        }
        
        .status-dot.active {
            background: var(--success);
            box-shadow: 0 0 8px var(--success-light);
        }

        /* Dynamic Switch toggle */
        .switch-toggle input:checked+.slider {
            background-color: var(--primary);
        }

        .switch-toggle .slider::before {
            content: "";
            position: absolute;
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: #fff;
            transition: .3s;
            border-radius: 50%;
        }

        .switch-toggle input:checked+.slider::before {
            transform: translateX(20px);
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

        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        @media (max-width: 992px) {
            .grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 576px) {
            .grid-4 {
                grid-template-columns: 1fr;
            }
        }

        .form-section-header {
            font-size: 11px;
            font-weight: 800;
            color: var(--primary-light);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 28px 0 16px 0;
            padding-bottom: 6px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-section-header:first-of-type {
            margin-top: 10px;
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
                    <span class="status-dot {{ session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false)) ? 'active' : '' }}" id="gateway-status-dot"></span>
                    <span id="gateway-status-text">{{ session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false)) ? 'Live API Connected' : 'Simulated Sandbox Mode' }}</span>
                </div>
            </div>

            <!-- Page Body -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Health Facility Registry (HFR) Portal</h1>
                    <p class="page-subtitle">Search registered health facilities, register new health installations, or link bridge software credentials.</p>
                </div>

                <!-- API Mode Control Toolbar -->
                <div style="background: var(--surface); border: 1px solid var(--border); padding: 12px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 12.5px; font-weight: 700; color: #fff;">ABDM Gateway Live Mode</span>
                        <label class="switch-toggle" style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                            <input type="checkbox" id="live-mode-switch" style="opacity: 0; width: 0; height: 0;" {{ session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false)) ? 'checked' : '' }}>
                            <span class="slider" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #1a2847; border: 1px solid var(--border2); transition: .3s; border-radius: 24px;"></span>
                        </label>
                    </div>
                    <div id="credentials-config-btn-wrap" style="display: {{ session('nhpr_real_api_mode', config('services.nhpr.real_api_mode', false)) ? 'block' : 'none' }};">
                        <a href="{{ route('nhpr.token.show') }}" class="btn" style="padding: 6px 12px; font-size: 11px; background: rgba(249, 168, 37, 0.1); border-color: rgba(249, 168, 37, 0.4); color: var(--gold);"><i class="fa-solid fa-gear"></i> Configure API Credentials</a>
                    </div>
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
                        <i class="fa-solid fa-link"></i> Link HPR/Facility Manager
                    </button>
                    <button class="tab-btn" data-tab="tab-track">
                        <i class="fa-solid fa-radar"></i> Track Registration
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

                <div class="tab-panel" id="tab-create">
                    @if(!$hprAuthenticated)
                        <!-- HPR Authentication Block -->
                        <div id="hpr-auth-card" class="card" style="max-width: 600px; margin: 0 auto;">
                            <div class="card-header" style="background: linear-gradient(135deg, #1565c0, #1e88e5); color: #fff; padding: 16px 20px;">
                                <span class="card-title" style="color: #fff; font-weight: 700;"><i class="fa-solid fa-lock"></i> HPR Authentication Required</span>
                            </div>
                            <div class="card-body" style="padding: 24px;">
                                <p style="font-size: 13.5px; color: var(--muted); margin-bottom: 20px; line-height: 1.6;">
                                    To register a new healthcare facility in the Health Facility Registry (HFR), you must first verify and log in using your Healthcare Professional Registry (HPR) ID.
                                </p>
                                
                                <!-- Step 1: Base Details (Verify HPR ID) -->
                                <div id="login-step-1">
                                    <div class="form-group" style="margin-bottom: 16px;">
                                        <label for="hpr-login-id" style="font-weight: 600; margin-bottom: 6px; display: block;">HPR ID / Practitioner ID <span class="req">*</span></label>
                                        <input type="text" id="hpr-login-id" class="form-control" placeholder="e.g. doctor@hpr.abdm" style="padding: 10px 14px;" required>
                                        <span style="font-size: 11px; color: var(--muted); display: block; margin-top: 4px;">Enter your registered HPR ID (e.g., username@hpr.abdm).</span>
                                    </div>
                                    <div style="text-align: right; margin-top: 20px;">
                                        <button type="button" class="btn" id="btn-login-verify-hpr" onclick="verifyLoginHprId()" style="background: var(--primary); color: #fff; padding: 10px 20px; border-radius: 6px; font-weight: 600;">
                                            <i class="fa-solid fa-user-shield"></i> Verify HPR Profile
                                        </button>
                                    </div>
                                </div>

                                <!-- Step 2: HPR Login Options (Hidden until verified) -->
                                <div id="login-step-2" style="display: none; margin-top: 14px;">
                                    <!-- Profile Card -->
                                    <div class="hpr-profile-card" style="padding: 16px; background: var(--surface2); border: 1px solid var(--border2); border-radius: 8px; margin-bottom: 20px;">
                                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                                            <div style="font-size: 24px; color: var(--primary-light);"><i class="fa-solid fa-user-doctor"></i></div>
                                            <div>
                                                <div style="font-weight: 700; font-size: 15px;" id="hpr-login-profile-name">Loading...</div>
                                                <div style="font-size: 11px; color: var(--muted);" id="hpr-login-profile-id">ID: </div>
                                            </div>
                                        </div>
                                        <div class="grid-2" style="font-size: 12px; gap: 8px; color: var(--muted2); margin-top: 12px;">
                                            <div><strong>Medical Council:</strong> <span id="hpr-login-profile-council">—</span></div>
                                            <div><strong>Reg No:</strong> <span id="hpr-login-profile-regno">—</span></div>
                                            <div><strong>Mobile:</strong> <span id="hpr-login-profile-mobile">—</span></div>
                                            <div><strong>DOB:</strong> <span id="hpr-login-profile-dob">—</span></div>
                                        </div>
                                    </div>

                                    <!-- Auth Method Selection -->
                                    <div class="form-group" style="margin-bottom: 14px;">
                                        <label for="hpr-login-auth-method" style="font-weight: 600; display: block; margin-bottom: 6px;">Select Authentication Method <span class="req">*</span></label>
                                        <select id="hpr-login-auth-method" class="form-control" onchange="toggleLoginAuthFields()" style="padding: 10px 14px;">
                                            <option value="PASSWORD">Login Via Password API</option>
                                            <option value="AADHAAR_OTP">Login Via Aadhaar OTP API</option>
                                        </select>
                                    </div>

                                    <!-- Password Fields -->
                                    <div class="form-group login-auth-field" id="login-auth-password-group" style="margin-bottom: 14px;">
                                        <label for="hpr-login-password" style="font-weight: 600; display: block; margin-bottom: 6px;">HPR Password <span class="req">*</span></label>
                                        <input type="password" id="hpr-login-password" class="form-control" placeholder="Enter HPR Registry Password" style="padding: 10px 14px;">
                                    </div>

                                    <!-- Mobile Fields -->
                                    <div class="form-group login-auth-field" id="login-auth-mobile-group" style="margin-bottom: 14px; display: none;">
                                        <label for="hpr-login-mobile-number" style="font-weight: 600; display: block; margin-bottom: 6px;">Registered Mobile Number <span class="req">*</span></label>
                                        <input type="text" id="hpr-login-mobile-number" class="form-control" maxlength="10" placeholder="e.g. 9876543210" style="padding: 10px 14px;">
                                    </div>

                                    <!-- OTP Fields (Common for Mobile OTP / Aadhaar OTP) -->
                                    <div class="form-group login-auth-field" id="login-auth-otp-group" style="margin-bottom: 14px; display: none;">
                                        <!-- Send OTP Button -->
                                        <div id="login-otp-request-container" style="text-align: center; margin-bottom: 10px;">
                                            <p style="font-size: 12.5px; color: var(--muted); margin-bottom: 10px;" id="login-otp-message-text">OTP will be sent to the registered mobile number.</p>
                                            <button type="button" class="btn" id="btn-login-send-otp" onclick="triggerLoginSendOtp()" style="background: var(--primary); color: #fff; width: 100%; padding: 10px 14px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                                                <i class="fa-solid fa-paper-plane"></i> Send OTP
                                            </button>
                                        </div>

                                        <!-- OTP Input Fields -->
                                        <div id="login-otp-input-container" style="display: none;">
                                            <label for="hpr-login-otp" style="font-weight: 600; display: block; margin-bottom: 6px;">Enter 6-Digit OTP <span class="req">*</span></label>
                                            <div style="display: flex; gap: 10px; margin-top: 6px; margin-bottom: 6px;">
                                                <input type="text" id="hpr-login-otp" class="form-control font-mono" maxlength="6" placeholder="Enter OTP (e.g. 123456)" style="flex: 1; padding: 10px 14px;">
                                                <button type="button" class="btn" id="btn-login-resend-otp" onclick="triggerLoginSendOtp()" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border2); padding: 8px 16px; border-radius: 6px; font-size: 13px;">
                                                    <i class="fa-solid fa-rotate-right"></i> Resend OTP
                                                </button>
                                            </div>
                                            <span style="font-size: 11px; color: var(--muted); display: block; margin-top: 4px;">For simulated mode, enter `123456`.</span>
                                            
                                            <!-- Button to Verify OTP for Mobile Login -->
                                            <button type="button" class="btn" id="btn-login-verify-mobile-otp" onclick="verifyLoginMobileOtpAction()" style="background: var(--primary); color: #fff; width: 100%; padding: 10px 14px; margin-top: 10px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: none;">
                                                <i class="fa-solid fa-shield-halved"></i> Verify OTP & Fetch Profiles
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Linked HPR Profiles list dropdown (For Mobile OTP only) -->
                                    <div class="form-group login-auth-field" id="login-auth-profiles-group" style="margin-bottom: 14px; display: none;">
                                        <label for="hpr-login-selected-hpr-id" style="font-weight: 600; display: block; margin-bottom: 6px;">Select HPR Profile / HPID <span class="req">*</span></label>
                                        <select id="hpr-login-selected-hpr-id" class="form-control" style="padding: 10px 14px;">
                                            <!-- Populated dynamically via JS -->
                                        </select>
                                        <span style="font-size: 11px; color: var(--muted); display: block; margin-top: 4px;">Select the HPR ID profile you wish to log in with.</span>
                                    </div>

                                    <div style="margin-top: 24px; display: flex; justify-content: space-between; align-items: center;">
                                        <button type="button" class="btn" id="btn-login-back-step1" onclick="goBackToLoginStep1()" style="background: rgba(255,255,255,0.05); color: #fff; padding: 10px 20px; border-radius: 6px;">
                                            <i class="fa-solid fa-arrow-left"></i> Back
                                        </button>
                                        <button type="button" class="btn" id="btn-login-submit" onclick="submitHprLogin()" style="background: var(--success); color: #fff; padding: 10px 20px; border-radius: 6px; font-weight: 600;">
                                            <i class="fa-solid fa-right-to-bracket"></i> Login & Authenticate
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- HPR Authenticated Banner -->
                        <div style="background: rgba(46, 125, 50, 0.1); border: 1px solid rgba(46, 125, 50, 0.3); padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 36px; height: 36px; background: rgba(46, 125, 50, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #81c784; font-size: 18px;">
                                    <i class="fa-solid fa-user-check"></i>
                                </div>
                                <div>
                                    <h4 style="margin: 0; font-size: 14px; color: #e8f0fe; font-weight: 700;">HPR Profile Authenticated</h4>
                                    <p style="margin: 2px 0 0 0; font-size: 12px; color: var(--muted);">Logged in as: <strong style="color: var(--primary-light);">{{ $loggedInHprId }}</strong></p>
                                </div>
                            </div>
                            <button type="button" class="btn" onclick="logoutHpr()" style="background: rgba(211, 47, 47, 0.1); border-color: rgba(211, 47, 47, 0.3); color: #ef5350; padding: 6px 14px; font-size: 12px; font-weight: 600;">
                                <i class="fa-solid fa-right-from-bracket"></i> Logout HPR
                            </button>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <span class="card-title"><i class="fa-solid fa-hospital"></i> HFR Facility Registration Form</span>
                            </div>
                            <div class="card-body">
                                <form id="create-form">
                                    <!-- Section 1: Basic Facility Information -->
                                    <div class="form-section-header">
                                        <i class="fa-solid fa-circle-info"></i> Basic Facility Details
                                    </div>
                                    <div class="grid-3">
                                        <div class="form-group">
                                            <label for="fac-name">Facility Name <span class="req">*</span></label>
                                            <input type="text" id="fac-name" class="form-control" placeholder="e.g. Sunrise Multispeciality Hospital" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-ownership">Ownership Type <span class="req">*</span></label>
                                            <select id="fac-ownership" class="form-control" onchange="triggerOwnershipChange()" required>
                                                <option value="">Select Ownership Type</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-ownership-subtype">Owner Subtype <span class="req">*</span></label>
                                            <select id="fac-ownership-subtype" class="form-control" onchange="triggerOwnerSubtypeChange()" required disabled>
                                                <option value="">Select Ownership First</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid-3" style="margin-top: 14px;">
                                        <div class="form-group">
                                            <label for="fac-ownership-subtype2">Owner Subtype 2 <span class="req">*</span></label>
                                            <select id="fac-ownership-subtype2" class="form-control" required disabled>
                                                <option value="">Select Subtype First</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-medicine">System of Medicine</label>
                                            <select id="fac-medicine" class="form-control" onchange="triggerOwnershipOrMedicineChange()">
                                                <option value="">Select System of Medicine</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-type">Facility Category/Type</label>
                                            <select id="fac-type" class="form-control" disabled>
                                                <option value="">Select Ownership & Medicine First</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid-3" style="margin-top: 14px;">
                                        <div class="form-group">
                                            <label for="fac-service-type">Type of Service</label>
                                            <select id="fac-service-type" class="form-control">
                                                <option value="">Select Type of Service</option>
                                                <option value="IPD">IPD (In-Patient Department)</option>
                                                <option value="OPD">OPD (Out-Patient Department)</option>
                                                <option value="IPD_OPD" selected>IPD &amp; OPD (Both)</option>
                                                <option value="DAYCARE">Day Care</option>
                                                <option value="DIAGNOSTIC">Diagnostic Services</option>
                                            </select>
                                            <span style="font-size: 11px; color: var(--muted); display: block; margin-top: 4px;">Not needed for Diagnostic Lab, Imaging, Cath Lab, Dialysis, Blood Bank, Pharmacy.</span>
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-speciality-type">Speciality Type</label>
                                            <select id="fac-speciality-type" class="form-control">
                                                <option value="">Select Speciality Type</option>
                                                <option value="SINGLE" selected>Single Speciality</option>
                                                <option value="MULTI">Multi Speciality</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-region">Facility Region</label>
                                            <select id="fac-region" class="form-control">
                                                <option value="">Select Region</option>
                                                <option value="U" selected>Urban</option>
                                                <option value="R">Rural</option>
                                            </select>
                                            <span style="font-size: 11px; color: var(--muted); display: block; margin-top: 4px;">API accepts: U (Urban) or R (Rural)</span>
                                        </div>
                                    </div>

                                    <!-- Section 2: Address & Location Details -->
                                    <div class="form-section-header">
                                        <i class="fa-solid fa-map-location-dot"></i> Address &amp; Location Details
                                    </div>
                                    <div class="grid-2">
                                        <div class="form-group">
                                            <label for="fac-address">Address Line 1 <span class="req">*</span></label>
                                            <input type="text" id="fac-address" class="form-control" placeholder="e.g. SG Highway, Vejalpur" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-address2">Address Line 2 <span style="font-size: 11px; font-weight: 400; color: var(--muted);">(Optional)</span></label>
                                            <input type="text" id="fac-address2" class="form-control" placeholder="e.g. Near Civil Hospital, Block B">
                                        </div>
                                    </div>

                                    <div class="grid-3" style="margin-top: 14px;">
                                        <div class="form-group">
                                            <label for="fac-state">State LGD Code <span class="req">*</span></label>
                                            <select id="fac-state" class="form-control" onchange="triggerStateChange()" required>
                                                <option value="">Select State</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-district">District LGD Code</label>
                                            <select id="fac-district" class="form-control" onchange="triggerDistrictChange()" required disabled>
                                                <option value="">Select State First</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-subdistrict">Sub-District LGD Code</label>
                                            <select id="fac-subdistrict" class="form-control" required disabled>
                                                <option value="">Select District First</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid-3" style="margin-top: 14px;">
                                        <div class="form-group">
                                            <label for="fac-pincode">Pincode <span class="req">*</span></label>
                                            <input type="text" id="fac-pincode" class="form-control" maxlength="6" placeholder="e.g. 380051" required pattern="[0-9]{6}">
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-latitude">Latitude</label>
                                            <input type="text" id="fac-latitude" class="form-control" placeholder="e.g. 23.0395">
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-longitude">Longitude</label>
                                            <input type="text" id="fac-longitude" class="form-control" placeholder="e.g. 72.5660">
                                        </div>
                                    </div>

                                    <!-- Section 3: Contact Details -->
                                    <div class="form-section-header">
                                        <i class="fa-solid fa-address-book"></i> Contact Details
                                    </div>
                                    <div class="grid-3">
                                        <div class="form-group">
                                            <label for="fac-email">Facility Email ID</label>
                                            <input type="email" id="fac-email" class="form-control" placeholder="e.g. info@sunrisehospital.com">
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-contact">Facility Mobile Number</label>
                                            <input type="tel" id="fac-contact" class="form-control" maxlength="10" placeholder="e.g. 9876543210">
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-website">Website URL <span style="font-size: 11px; font-weight: 400; color: var(--muted);">(Optional)</span></label>
                                            <input type="url" id="fac-website" class="form-control" placeholder="e.g. https://sunrisehospital.com">
                                        </div>
                                    </div>
                                    <div class="grid-2" style="margin-top: 14px;">
                                        <div class="form-group">
                                            <label for="fac-std">STD Code</label>
                                            <input type="text" id="fac-std" class="form-control" placeholder="e.g. 079">
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-landline">Landline Number</label>
                                            <input type="text" id="fac-landline" class="form-control" placeholder="e.g. 40000000">
                                        </div>
                                    </div>

                                    <!-- Section 4: Identifiers & Registrations -->
                                    <div class="form-section-header">
                                        <i class="fa-solid fa-id-card"></i> Identifiers & Registrations (Optional)
                                    </div>
                                    <div class="grid-3">
                                        <div class="form-group">
                                            <label for="fac-abpmjay">AB-PMJAY ID</label>
                                            <input type="text" id="fac-abpmjay" class="form-control" placeholder="AB-PMJAY ID">
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-nin">National Identification Number (NIN ID)</label>
                                            <input type="text" id="fac-nin" class="form-control" placeholder="NIN ID">
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-cea">CEA ID (Clinical Establishment Act)</label>
                                            <input type="text" id="fac-cea" class="form-control" placeholder="CEA ID">
                                        </div>
                                    </div>

                                    <div class="grid-2" style="margin-top: 14px;">
                                        <div class="form-group">
                                            <label for="fac-hrpsource">HRP Source</label>
                                            <input type="text" id="fac-hrpsource" class="form-control" placeholder="e.g. NIC-HIMS">
                                        </div>
                                        <div class="form-group">
                                            <label for="fac-hrp-facid">HRP Source Facility ID</label>
                                            <input type="text" id="fac-hrp-facid" class="form-control" placeholder="External Facility ID">
                                        </div>
                                    </div>

                                    <!-- Section 5: Facility Photos -->
                                    <div class="form-section-header" style="margin-top: 20px;">
                                        <i class="fa-solid fa-images"></i> Facility Photos <span style="font-size: 11px; font-weight: 400; color: var(--muted); margin-left: 6px;">(Optional — Building & Board photos)</span>
                                    </div>
                                    <div class="grid-2">
                                        <!-- Building Photo -->
                                        <div class="form-group">
                                            <label for="fac-building-photo">Building / Exterior Photo</label>
                                            <div style="border: 1px dashed var(--border2); border-radius: 8px; padding: 14px; background: rgba(255,255,255,0.02); cursor: pointer;" onclick="document.getElementById('fac-building-photo').click()">
                                                <div id="fac-building-photo-preview" style="display: none; margin-bottom: 10px; text-align: center;">
                                                    <img id="fac-building-photo-img" src="" alt="Building Photo Preview" style="max-height: 120px; max-width: 100%; border-radius: 6px; border: 1px solid var(--border2);">
                                                </div>
                                                <div id="fac-building-photo-placeholder" style="text-align: center; color: var(--muted); font-size: 13px; padding: 8px 0;">
                                                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 22px; margin-bottom: 6px; display: block;"></i>
                                                    Click to upload building photo<br>
                                                    <span style="font-size: 11px;">JPG, PNG — max 2 MB</span>
                                                </div>
                                            </div>
                                            <input type="file" id="fac-building-photo" accept="image/jpeg,image/png" style="display: none;" onchange="handleFacilityPhotoUpload(this, 'building')">
                                            <input type="hidden" id="fac-building-photo-name" value="">
                                            <input type="hidden" id="fac-building-photo-value" value="">
                                        </div>

                                        <!-- Board / Sign Photo -->
                                        <div class="form-group">
                                            <label for="fac-board-photo">Board / Sign Photo</label>
                                            <div style="border: 1px dashed var(--border2); border-radius: 8px; padding: 14px; background: rgba(255,255,255,0.02); cursor: pointer;" onclick="document.getElementById('fac-board-photo').click()">
                                                <div id="fac-board-photo-preview" style="display: none; margin-bottom: 10px; text-align: center;">
                                                    <img id="fac-board-photo-img" src="" alt="Board Photo Preview" style="max-height: 120px; max-width: 100%; border-radius: 6px; border: 1px solid var(--border2);">
                                                </div>
                                                <div id="fac-board-photo-placeholder" style="text-align: center; color: var(--muted); font-size: 13px; padding: 8px 0;">
                                                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 22px; margin-bottom: 6px; display: block;"></i>
                                                    Click to upload board/sign photo<br>
                                                    <span style="font-size: 11px;">JPG, PNG — max 2 MB</span>
                                                </div>
                                            </div>
                                            <input type="file" id="fac-board-photo" accept="image/jpeg,image/png" style="display: none;" onchange="handleFacilityPhotoUpload(this, 'board')">
                                            <input type="hidden" id="fac-board-photo-name" value="">
                                            <input type="hidden" id="fac-board-photo-value" value="">
                                        </div>
                                    </div>

                                    <!-- Section 6: Facility Timings -->
                                    <div class="form-section-header" style="margin-top: 20px;">
                                        <i class="fa-solid fa-clock"></i> Facility Timings
                                    </div>
                                    <div style="margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                                        <span style="font-size: 12px; color: var(--muted);">Check the days your facility is open and set working hours for each.</span>
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <label style="font-size: 12px; color: var(--muted2); font-weight: 600;">Apply to all open days:</label>
                                            <select id="timing-bulk-open" class="form-control" style="padding: 5px 8px; font-size: 12px; width: auto;">
                                                <option value="">Open</option>
                                                @for ($h = 6; $h <= 22; $h++)
                                                    @php $suffix = $h < 12 ? 'AM' : 'PM'; $hr = $h > 12 ? $h - 12 : ($h == 0 ? 12 : $h); @endphp
                                                    <option value="{{ sprintf('%02d:00 %s', $hr, $suffix) }}">{{ sprintf('%02d:00 %s', $hr, $suffix) }}</option>
                                                    <option value="{{ sprintf('%02d:30 %s', $hr, $suffix) }}">{{ sprintf('%02d:30 %s', $hr, $suffix) }}</option>
                                                @endfor
                                            </select>
                                            <label style="font-size: 12px; color: var(--muted2); font-weight: 600;">to</label>
                                            <select id="timing-bulk-close" class="form-control" style="padding: 5px 8px; font-size: 12px; width: auto;">
                                                <option value="">Close</option>
                                                @for ($h = 6; $h <= 23; $h++)
                                                    @php $suffix = $h < 12 ? 'AM' : 'PM'; $hr = $h > 12 ? $h - 12 : ($h == 0 ? 12 : $h); @endphp
                                                    <option value="{{ sprintf('%02d:00 %s', $hr, $suffix) }}" {{ $h === 18 ? 'selected' : '' }}>{{ sprintf('%02d:00 %s', $hr, $suffix) }}</option>
                                                    <option value="{{ sprintf('%02d:30 %s', $hr, $suffix) }}">{{ sprintf('%02d:30 %s', $hr, $suffix) }}</option>
                                                @endfor
                                            </select>
                                            <button type="button" class="btn" onclick="applyBulkTimings()" style="background: var(--primary); color:#fff; padding: 5px 12px; font-size: 12px; border-radius: 6px;"><i class="fa-solid fa-check-double"></i> Apply</button>
                                        </div>
                                    </div>
                                    <div style="border: 1px solid var(--border); border-radius: 8px; overflow: hidden;">
                                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                            <thead>
                                                <tr style="background: rgba(255,255,255,0.04);">
                                                    <th style="padding: 10px 14px; text-align: left; color: var(--muted2); font-weight: 600; border-bottom: 1px solid var(--border);">Day</th>
                                                    <th style="padding: 10px 14px; text-align: center; color: var(--muted2); font-weight: 600; border-bottom: 1px solid var(--border);">Open</th>
                                                    <th style="padding: 10px 14px; text-align: left; color: var(--muted2); font-weight: 600; border-bottom: 1px solid var(--border);">Opens At</th>
                                                    <th style="padding: 10px 14px; text-align: left; color: var(--muted2); font-weight: 600; border-bottom: 1px solid var(--border);">Closes At</th>
                                                </tr>
                                            </thead>
                                            <tbody id="timings-table-body">
                                                @php
                                                    $days = [
                                                        ['code' => 'MON', 'label' => 'Monday',    'default' => true],
                                                        ['code' => 'TUE', 'label' => 'Tuesday',   'default' => true],
                                                        ['code' => 'WED', 'label' => 'Wednesday', 'default' => true],
                                                        ['code' => 'THU', 'label' => 'Thursday',  'default' => true],
                                                        ['code' => 'FRI', 'label' => 'Friday',    'default' => true],
                                                        ['code' => 'SAT', 'label' => 'Saturday',  'default' => false],
                                                        ['code' => 'SUN', 'label' => 'Sunday',    'default' => false],
                                                    ];
                                                @endphp
                                                @foreach($days as $i => $day)
                                                <tr class="timing-row" id="timing-row-{{ $day['code'] }}" style="border-bottom: 1px solid var(--border); {{ !$day['default'] ? 'opacity: 0.5;' : '' }}">
                                                    <td style="padding: 10px 14px; font-weight: 600;">{{ $day['label'] }}</td>
                                                    <td style="padding: 10px 14px; text-align: center;">
                                                        <input type="checkbox" id="timing-open-{{ $day['code'] }}" class="timing-day-check" data-day="{{ $day['code'] }}" {{ $day['default'] ? 'checked' : '' }} onchange="toggleTimingRow('{{ $day['code'] }}')" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--primary-light);">
                                                    </td>
                                                    <td style="padding: 8px 14px;">
                                                        <select id="timing-opens-{{ $day['code'] }}" class="form-control timing-time-select" style="padding: 6px 10px; font-size: 12px;" {{ !$day['default'] ? 'disabled' : '' }}>
                                                            @for ($h = 6; $h <= 22; $h++)
                                                                @php $suffix = $h < 12 ? 'AM' : 'PM'; $hr = $h > 12 ? $h - 12 : ($h == 0 ? 12 : $h); $val = sprintf('%02d:00 %s', $hr, $suffix); @endphp
                                                                <option value="{{ $val }}" {{ $h === 9 ? 'selected' : '' }}>{{ $val }}</option>
                                                                @php $val30 = sprintf('%02d:30 %s', $hr, $suffix); @endphp
                                                                <option value="{{ $val30 }}">{{ $val30 }}</option>
                                                            @endfor
                                                        </select>
                                                    </td>
                                                    <td style="padding: 8px 14px;">
                                                        <select id="timing-closes-{{ $day['code'] }}" class="form-control timing-time-select" style="padding: 6px 10px; font-size: 12px;" {{ !$day['default'] ? 'disabled' : '' }}>
                                                            @for ($h = 6; $h <= 23; $h++)
                                                                @php $suffix = $h < 12 ? 'AM' : 'PM'; $hr = $h > 12 ? $h - 12 : ($h == 0 ? 12 : $h); $val = sprintf('%02d:00 %s', $hr, $suffix); @endphp
                                                                <option value="{{ $val }}" {{ $h === 18 ? 'selected' : '' }}>{{ $val }}</option>
                                                                @php $val30 = sprintf('%02d:30 %s', $hr, $suffix); @endphp
                                                                <option value="{{ $val30 }}">{{ $val30 }}</option>
                                                            @endfor
                                                        </select>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div style="margin-top: 24px; text-align: right;">
                                        <button type="submit" class="btn" id="btn-create-fac">
                                            <i class="fa-solid fa-circle-check"></i> Register Facility
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 3: Link HPR/Facility Manager -->
                <!-- TAB 3: Link HPR/Facility Manager -->
                <div class="tab-panel" id="tab-link">
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-user-tie"></i> HFR to HPR / Facility Manager Linkage</span>
                        </div>
                        <div class="card-body">
                            <form id="link-form" onsubmit="submitLinkageForm(event)">
                                <!-- Hidden Fields to hold details for link-existing API -->
                                <input type="hidden" id="link-facility-address" value="Dehradun">
                                <input type="hidden" id="link-facility-pincode" value="248001">

                                <!-- Step 1: Base Details -->
                                <div id="link-step-1">
                                    <div class="form-group" style="margin-bottom: 14px;">
                                        <label for="link-facility-id">Health Facility ID (HFR ID) <span class="req">*</span></label>
                                        <input type="text" id="link-facility-id" class="form-control" placeholder="e.g. IN2710000059" required>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 14px;">
                                        <label for="link-facility-name">Facility Name <span class="req">*</span></label>
                                        <input type="text" id="link-facility-name" class="form-control" placeholder="e.g. Dehradun Civil Hospital" required>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 14px;">
                                        <label for="link-hpr-id">HPR ID / Facility Manager ID <span class="req">*</span></label>
                                        <input type="text" id="link-hpr-id" class="form-control" value="{{ $loggedInHprId }}" placeholder="e.g. doctor@hpr.abdm" required>
                                        <span style="font-size: 11px; color: var(--muted);">Pre-populated with active logged-in HPR session identifier.</span>
                                    </div>

                                    <div style="margin-top: 20px; text-align: right;">
                                        <button type="button" class="btn" id="btn-verify-hpr" onclick="verifyHprId()" style="background: var(--primary); color: #fff;">
                                            <i class="fa-solid fa-user-shield"></i> Verify HPR Profile
                                        </button>
                                    </div>
                                </div>

                                <!-- Step 2: HPR Verification (Hidden by default) -->
                                <div id="link-step-2" style="display: none; margin-top: 14px;">
                                    <!-- Fetched Profile Card -->
                                    <div class="hpr-profile-card" style="padding: 16px; background: var(--surface2); border: 1px solid var(--border2); border-radius: 8px; margin-bottom: 20px;">
                                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                                            <div style="font-size: 24px; color: var(--primary-light);"><i class="fa-solid fa-user-doctor"></i></div>
                                            <div>
                                                <div style="font-weight: 700; font-size: 15px;" id="hpr-profile-name">Loading...</div>
                                                <div style="font-size: 11px; color: var(--muted);" id="hpr-profile-id">ID: </div>
                                            </div>
                                        </div>
                                        <div class="grid-2" style="font-size: 12px; gap: 8px; color: var(--muted2); margin-top: 12px;">
                                            <div><strong>Medical Council:</strong> <span id="hpr-profile-council">—</span></div>
                                            <div><strong>Reg No:</strong> <span id="hpr-profile-regno">—</span></div>
                                            <div><strong>Mobile:</strong> <span id="hpr-profile-mobile">—</span></div>
                                            <div><strong>DOB:</strong> <span id="hpr-profile-dob">—</span></div>
                                        </div>
                                    </div>

                                    <!-- Auth Type Selection -->
                                    <div class="form-group" style="margin-bottom: 14px;">
                                        <label for="link-auth-method">Select Authentication Method <span class="req">*</span></label>
                                        <select id="link-auth-method" class="form-control" onchange="toggleAuthFields()">
                                            <option value="PASSWORD">Login Via Password API</option>
                                            <option value="AADHAAR_OTP">Login Via Aadhaar OTP API</option>
                                        </select>
                                    </div>

                                    <!-- Password Fields -->
                                    <div class="form-group id-auth-field" id="auth-password-group" style="margin-bottom: 14px;">
                                        <label for="link-password">HPR Password <span class="req">*</span></label>
                                        <input type="password" id="link-password" class="form-control" placeholder="Enter HPR Registry Password">
                                    </div>

                                    <!-- Mobile Fields -->
                                    <div class="form-group id-auth-field" id="auth-mobile-group" style="margin-bottom: 14px; display: none;">
                                        <label for="link-mobile-number">Registered Mobile Number <span class="req">*</span></label>
                                        <input type="text" id="link-mobile-number" class="form-control" maxlength="10" placeholder="e.g. 9876543210">
                                    </div>

                                    <!-- OTP Fields (Common for Mobile OTP / Aadhaar OTP) -->
                                    <div class="form-group id-auth-field" id="auth-otp-group" style="margin-bottom: 14px; display: none;">
                                        <!-- Step A: Send OTP Button -->
                                        <div id="hfr-otp-request-container" style="text-align: center; margin-bottom: 10px;">
                                            <p style="font-size: 12.5px; color: var(--muted); margin-bottom: 10px;" id="otp-message-text">OTP will be sent to the registered mobile number.</p>
                                            <button type="button" class="btn" id="btn-hfr-send-otp" style="background: var(--primary); color: #fff; width: 100%; padding: 10px 14px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                                                <i class="fa-solid fa-paper-plane"></i> Send OTP
                                            </button>
                                        </div>

                                        <!-- Step B: OTP Input Fields (Hidden until OTP is successfully sent) -->
                                        <div id="hfr-otp-input-container" style="display: none;">
                                            <label for="link-otp">Enter 6-Digit OTP <span class="req">*</span></label>
                                            <div style="display: flex; gap: 10px; margin-top: 6px; margin-bottom: 6px;">
                                                <input type="text" id="link-otp" class="form-control font-mono" maxlength="6" placeholder="Enter OTP (e.g. 123456)" style="flex: 1;">
                                                <button type="button" class="btn" id="btn-hfr-resend-otp" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border2); padding: 8px 16px; border-radius: 6px; font-size: 13px;">
                                                    <i class="fa-solid fa-rotate-right"></i> Resend OTP
                                                </button>
                                            </div>
                                            <span style="font-size: 11px; color: var(--muted); display: block; margin-top: 4px;">For simulated mode, enter `123456`.</span>
                                            
                                            <!-- Button to Verify OTP for Mobile Login (Intermediate Step) -->
                                            <button type="button" class="btn" id="btn-hfr-verify-mobile-otp" style="background: var(--primary); color: #fff; width: 100%; padding: 10px 14px; margin-top: 10px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: none;">
                                                <i class="fa-solid fa-shield-halved"></i> Verify OTP & Fetch Profiles
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Linked HPR Profiles list dropdown (For Mobile OTP only) -->
                                    <div class="form-group id-auth-field" id="auth-profiles-group" style="margin-bottom: 14px; display: none;">
                                        <label for="link-selected-hpr-id">Select HPR Profile / HPID <span class="req">*</span></label>
                                        <select id="link-selected-hpr-id" class="form-control">
                                            <!-- Populated dynamically via JS -->
                                        </select>
                                        <span style="font-size: 11px; color: var(--muted); display: block; margin-top: 4px;">Select the HPR ID profile you wish to link to this facility.</span>
                                    </div>

                                    <div style="margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                                        <button type="button" class="btn" id="btn-back-step1" onclick="goBackToStep1()" style="background: rgba(255,255,255,0.05); color: #fff;">
                                            <i class="fa-solid fa-arrow-left"></i> Back
                                        </button>
                                        <button type="submit" class="btn" id="btn-link-fac" style="background: var(--success); color: #fff; display: none;">
                                            <i class="fa-solid fa-link"></i> Link HPR/Facility Manager
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: Track Registration -->
                <div class="tab-panel" id="tab-track">
                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-magnifying-glass"></i> Look Up Registered Facility</span>
                        </div>
                        <div class="card-body">
                            <p style="color:var(--text-muted);margin-bottom:20px;">
                                Enter the <strong>HFR Facility ID</strong> (e.g. <code>IN2710000059</code>) to look up its current status and details.
                                <br><small style="opacity:.7;"><i class="fa-solid fa-circle-info"></i> The numeric Tracking ID is only used during registration and cannot be queried after submission.</small>
                            </p>
                            <div class="grid-2" style="align-items:flex-end;gap:14px;">
                                <div class="form-group" style="margin-bottom:0;">
                                    <label for="track-id-input">HFR Facility ID <span class="req">*</span></label>
                                    <input type="text" id="track-id-input" class="form-control" placeholder="e.g. IN2710000059" autocomplete="off">
                                </div>
                                <div>
                                    <button class="btn" id="btn-track" onclick="trackRegistration()">
                                        <i class="fa-solid fa-magnifying-glass"></i> Look Up
                                    </button>
                                </div>
                            </div>

                            <!-- Result Panel -->
                            <div id="track-result" style="margin-top:28px;display:none;">
                                <!-- Summary header -->
                                <div id="track-summary-card" style="background:var(--card-bg,#1e2535);border:1px solid var(--border-color,#2a3347);border-radius:12px;padding:20px 24px;margin-bottom:20px;">
                                    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:14px;">
                                        <div style="flex:2;">
                                            <div style="font-size:12px;color:var(--text-muted);margin-bottom:3px;">Facility Name</div>
                                            <div id="track-facility-name" style="font-size:17px;font-weight:700;">—</div>
                                        </div>
                                        <div style="flex:1;">
                                            <div style="font-size:12px;color:var(--text-muted);margin-bottom:3px;">Facility ID</div>
                                            <div id="track-facility-id" style="font-size:15px;font-weight:600;color:var(--primary);">—</div>
                                        </div>
                                        <div style="flex:1;">
                                            <div style="font-size:12px;color:var(--text-muted);margin-bottom:3px;">Status</div>
                                            <div id="track-status-badge"></div>
                                        </div>
                                    </div>
                                    <!-- Detail grid -->
                                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;border-top:1px solid var(--border-color,#2a3347);padding-top:14px;">
                                        <div><div style="font-size:11px;color:var(--text-muted);margin-bottom:2px;">Ownership</div><div id="track-ownership" style="font-size:13px;font-weight:500;">—</div></div>
                                        <div><div style="font-size:11px;color:var(--text-muted);margin-bottom:2px;">Facility Type</div><div id="track-facility-type" style="font-size:13px;font-weight:500;">—</div></div>
                                        <div><div style="font-size:11px;color:var(--text-muted);margin-bottom:2px;">System of Medicine</div><div id="track-medicine" style="font-size:13px;font-weight:500;">—</div></div>
                                        <div><div style="font-size:11px;color:var(--text-muted);margin-bottom:2px;">State</div><div id="track-state" style="font-size:13px;font-weight:500;">—</div></div>
                                        <div><div style="font-size:11px;color:var(--text-muted);margin-bottom:2px;">District</div><div id="track-district" style="font-size:13px;font-weight:500;">—</div></div>
                                        <div><div style="font-size:11px;color:var(--text-muted);margin-bottom:2px;">Pincode</div><div id="track-pincode" style="font-size:13px;font-weight:500;">—</div></div>
                                        <div style="grid-column:1/-1;"><div style="font-size:11px;color:var(--text-muted);margin-bottom:2px;">Address</div><div id="track-address" style="font-size:13px;font-weight:500;">—</div></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Error panel -->
                            <div id="track-error" style="margin-top:24px;display:none;" class="alert alert-error">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span id="track-error-text">Something went wrong.</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // CSRF details
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        let hfrOtpSent = false;
        let hfrOtpTxnId = null;
        let loginOtpSent = false;
        let loginOtpTxnId = null;

        // HPR Login Functions
        function verifyLoginHprId() {
            const hprId = document.getElementById('hpr-login-id').value.trim();
            if (!hprId) {
                showToast('Please enter an HPR ID.', 'error');
                return;
            }

            const btn = document.getElementById('btn-login-verify-hpr');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying...';

            fetch('{{ route("nhpr.register.fetch-hpr-profile") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ hpr_id: hprId })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Verification failed.');
                }
                return res.json();
            })
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-user-shield"></i> Verify HPR Profile';

                if (data.success && data.profile) {
                    const prof = data.profile;
                    document.getElementById('hpr-login-profile-name').innerText = prof.name || 'Healthcare Professional';
                    document.getElementById('hpr-login-profile-id').innerText = 'ID: ' + (prof.hprId || hprId);
                    document.getElementById('hpr-login-profile-council').innerText = prof.council || '—';
                    document.getElementById('hpr-login-profile-regno').innerText = prof.registrationNumber || '—';
                    document.getElementById('hpr-login-profile-mobile').innerText = prof.maskedMobile || '—';
                    document.getElementById('hpr-login-profile-dob').innerText = prof.dob || '—';

                    // Reset OTP state and set auth selection back to default Password Login
                    loginOtpSent = false;
                    loginOtpTxnId = null;
                    document.getElementById('hpr-login-auth-method').value = 'PASSWORD';
                    document.getElementById('hpr-login-selected-hpr-id').innerHTML = '';
                    toggleLoginAuthFields();

                    // Slide to Step 2
                    document.getElementById('login-step-1').style.display = 'none';
                    document.getElementById('login-step-2').style.display = 'block';
                    showToast('Practitioner profile retrieved successfully!');
                } else {
                    showToast(data.message || 'HPR ID not found.', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-user-shield"></i> Verify HPR Profile';
                showToast('HPR verification service offline.', 'error');
            });
        }

        function goBackToLoginStep1() {
            document.getElementById('login-step-2').style.display = 'none';
            document.getElementById('login-step-1').style.display = 'block';
        }

        function toggleLoginAuthFields() {
            const method = document.getElementById('hpr-login-auth-method').value;
            const authPasswordGroup = document.getElementById('login-auth-password-group');
            const authMobileGroup = document.getElementById('login-auth-mobile-group');
            const authOtpGroup = document.getElementById('login-auth-otp-group');
            const authProfilesGroup = document.getElementById('login-auth-profiles-group');
            
            const otpRequestContainer = document.getElementById('login-otp-request-container');
            const otpInputContainer = document.getElementById('login-otp-input-container');
            const btnVerifyMobileOtp = document.getElementById('btn-login-verify-mobile-otp');
            const btnLoginSubmit = document.getElementById('btn-login-submit');

            // Hide all auth fields initially
            authPasswordGroup.style.display = 'none';
            authMobileGroup.style.display = 'none';
            authOtpGroup.style.display = 'none';
            authProfilesGroup.style.display = 'none';
            btnLoginSubmit.style.display = 'block'; // Show login button by default

            if (method === 'PASSWORD') {
                authPasswordGroup.style.display = 'block';
                btnLoginSubmit.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Login & Authenticate';
            } else if (method === 'MOBILE_OTP') {
                authMobileGroup.style.display = 'block';
                authOtpGroup.style.display = 'block';
                document.getElementById('login-otp-message-text').innerText = 'OTP will be sent to this mobile number.';
                
                if (loginOtpSent) {
                    otpRequestContainer.style.display = 'none';
                    otpInputContainer.style.display = 'block';
                    
                    if (document.getElementById('hpr-login-selected-hpr-id').options.length > 0) {
                        authProfilesGroup.style.display = 'block';
                        btnLoginSubmit.style.display = 'block';
                        btnLoginSubmit.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Login Selected HPR';
                        btnVerifyMobileOtp.style.display = 'none';
                    } else {
                        btnVerifyMobileOtp.style.display = 'block';
                        btnLoginSubmit.style.display = 'none';
                    }
                } else {
                    otpRequestContainer.style.display = 'block';
                    otpInputContainer.style.display = 'none';
                    btnVerifyMobileOtp.style.display = 'none';
                    btnLoginSubmit.style.display = 'none';
                }
            } else if (method === 'AADHAAR_OTP') {
                authOtpGroup.style.display = 'block';
                document.getElementById('login-otp-message-text').innerText = 'OTP will be sent to the Aadhaar-linked mobile number.';
                
                if (loginOtpSent) {
                    otpRequestContainer.style.display = 'none';
                    otpInputContainer.style.display = 'block';
                    btnLoginSubmit.style.display = 'block';
                    btnLoginSubmit.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Verify OTP & Login';
                } else {
                    otpRequestContainer.style.display = 'block';
                    otpInputContainer.style.display = 'none';
                    btnLoginSubmit.style.display = 'none';
                }
            }
        }

        function triggerLoginSendOtp() {
            const method = document.getElementById('hpr-login-auth-method').value;
            const btnElement = document.getElementById('btn-login-send-otp');
            const payload = { auth_method: method };

            if (method === 'MOBILE_OTP') {
                const mobile = document.getElementById('hpr-login-mobile-number').value.trim();
                if (!mobile || mobile.length !== 10) {
                    showToast('Please enter a valid 10-digit mobile number.', 'warning');
                    return;
                }
                payload.mobile = mobile;
            } else {
                const hprId = document.getElementById('hpr-login-id').value.trim();
                if (!hprId) {
                    showToast('Please enter HPR ID.', 'warning');
                    return;
                }
                payload.hpr_id = hprId;
            }

            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

            fetch('{{ route("nhpr.register.link-existing.send-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btnElement.disabled = false;
                btnElement.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send OTP';

                if (data.success) {
                    loginOtpSent = true;
                    loginOtpTxnId = data.txnId;
                    document.getElementById('hpr-login-selected-hpr-id').innerHTML = ''; // clear previous profiles

                    document.getElementById('login-otp-request-container').style.display = 'none';
                    document.getElementById('login-otp-input-container').style.display = 'block';
                    
                    toggleLoginAuthFields();
                    showToast(data.message || 'OTP sent successfully!');
                } else {
                    showToast(data.message || 'Failed to send OTP.', 'error');
                }
            })
            .catch(() => {
                btnElement.disabled = false;
                btnElement.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Send OTP';
                showToast('Failed to send OTP.', 'error');
            });
        }

        function verifyLoginMobileOtpAction() {
            const mobile = document.getElementById('hpr-login-mobile-number').value.trim();
            const otp = document.getElementById('hpr-login-otp').value.trim();
            const btn = document.getElementById('btn-login-verify-mobile-otp');
            
            if (!otp || otp.length !== 6) {
                showToast('Please enter a valid 6-digit OTP.', 'warning');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying OTP...';

            fetch('{{ route("nhpr.register.link-existing.verify-mobile-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    mobile: mobile,
                    txn_id: loginOtpTxnId,
                    otp: otp
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-shield-halved"></i> Verify OTP & Fetch Profiles';

                if (data.success) {
                    loginOtpTxnId = data.txnId;
                    const dropdown = document.getElementById('hpr-login-selected-hpr-id');
                    dropdown.innerHTML = '';

                    if (data.profiles && data.profiles.length > 0) {
                        data.profiles.forEach(p => {
                            const opt = document.createElement('option');
                            opt.value = p.hprId;
                            opt.innerText = `${p.name} (${p.hprId})`;
                            dropdown.appendChild(opt);
                        });
                        toggleLoginAuthFields();
                        showToast('HPR profiles fetched successfully! Select HPR profile to complete login.');
                    } else {
                        showToast('No HPR profiles found linked to this mobile number.', 'error');
                    }
                } else {
                    showToast(data.message || 'OTP verification failed.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-shield-halved"></i> Verify OTP & Fetch Profiles';
                showToast('OTP verification failed.', 'error');
            });
        }

        function submitHprLogin() {
            const btn = document.getElementById('btn-login-submit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Authenticating...';

            const authMethod = document.getElementById('hpr-login-auth-method').value;
            const payload = {
                auth_method: authMethod
            };

            if (authMethod === 'PASSWORD') {
                const passVal = document.getElementById('hpr-login-password').value;
                if (!passVal) {
                    showToast('Please enter HPR password.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Login & Authenticate';
                    return;
                }
                payload.hpr_id = document.getElementById('hpr-login-id').value.trim();
                payload.password = passVal;
            } else if (authMethod === 'MOBILE_OTP') {
                const selectedHpr = document.getElementById('hpr-login-selected-hpr-id').value;
                if (!selectedHpr) {
                    showToast('Please select HPR Profile first.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Login Selected HPR';
                    return;
                }
                payload.mobile = document.getElementById('hpr-login-mobile-number').value.trim();
                payload.selected_hpr_id = selectedHpr;
                payload.txn_id = loginOtpTxnId;
            } else if (authMethod === 'AADHAAR_OTP') {
                const otpVal = document.getElementById('hpr-login-otp').value.trim();
                if (!otpVal) {
                    showToast('Please enter the OTP.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Verify OTP & Login';
                    return;
                }
                payload.hpr_id = document.getElementById('hpr-login-id').value.trim();
                payload.otp = otpVal;
                payload.txn_id = loginOtpTxnId;
            }

            fetch('{{ route("nhpr.hfr.hpr-login") }}', {
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
                btn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Login & Authenticate';

                if (data.success) {
                    showToast(data.message || 'Successfully authenticated HPR profile!');
                    // Reload to update views and populate the facility registration form
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast(data.message || 'Authentication failed.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-right-to-bracket"></i> Login & Authenticate';
                showToast('HPR login request failed.', 'error');
            });
        }

        function logoutHpr() {
            fetch('{{ route("nhpr.hfr.hpr-logout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Logged out successfully!');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast('Logout failed.', 'error');
                }
            })
            .catch(() => {
                showToast('Logout request failed.', 'error');
            });
        }

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
                                        data-name="${fac.facilityName}"
                                        data-address="${fac.address || 'Dehradun'}"
                                        data-pincode="${fac.pincode || '248001'}">
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
                                const address = this.getAttribute('data-address');
                                const pincode = this.getAttribute('data-pincode');
                                document.getElementById('link-facility-id').value = id;
                                document.getElementById('link-facility-name').value = name;
                                document.getElementById('link-facility-address').value = address;
                                document.getElementById('link-facility-pincode').value = pincode;
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
                ownershipSubTypeCode: document.getElementById('fac-ownership-subtype').value,
                ownershipSubTypeCode2: document.getElementById('fac-ownership-subtype2').value,
                systemOfMedicineCode: document.getElementById('fac-medicine').value,
                facilityTypeCode: document.getElementById('fac-type').value,
                // Type of service, speciality, region
                typeOfServiceCode: document.getElementById('fac-service-type').value || null,
                specialityTypeCode: document.getElementById('fac-speciality-type').value || null,
                facilityRegion: document.getElementById('fac-region').value || null,
                pincode: document.getElementById('fac-pincode').value,
                stateLGDCode: document.getElementById('fac-state').value,
                districtLGDCode: document.getElementById('fac-district').value,
                subDistrictLGDCode: document.getElementById('fac-subdistrict').value,
                address: document.getElementById('fac-address').value,
                address2: document.getElementById('fac-address2').value || null,
                facilityContactNumber: document.getElementById('fac-contact').value,
                facilityEmailId: document.getElementById('fac-email').value,
                websiteLink: document.getElementById('fac-website').value || null,
                facilityLandlineNumber: document.getElementById('fac-landline').value,
                facilityStdCode: document.getElementById('fac-std').value,
                latitude: document.getElementById('fac-latitude').value,
                longitude: document.getElementById('fac-longitude').value,
                abpmjayId: document.getElementById('fac-abpmjay').value,
                ninID: document.getElementById('fac-nin').value,
                ceaId: document.getElementById('fac-cea').value,
                hrpSource: document.getElementById('fac-hrpsource').value,
                hrpSourceFacilityId: document.getElementById('fac-hrp-facid').value,
                // Facility Photos
                facilityBuildingPhotoName: document.getElementById('fac-building-photo-name').value || null,
                facilityBuildingPhotoValue: document.getElementById('fac-building-photo-value').value || null,
                facilityBoardPhotoName: document.getElementById('fac-board-photo-name').value || null,
                facilityBoardPhotoValue: document.getElementById('fac-board-photo-value').value || null,
                // Facility Timings
                timingsOfFacility: collectFacilityTimings(),
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
                    document.getElementById('link-facility-address').value = payload.address || 'Dehradun';
                    document.getElementById('link-facility-pincode').value = payload.pincode || '248001';

                    // Pre-fill the Track Registration tab with the new facilityId
                    if (data.facilityId) {
                        document.getElementById('track-id-input').value = data.facilityId;
                        showToast(`Facility ID: ${data.facilityId} — switch to "Look Up Facility" tab to view details.`, 'info');
                    }
                    
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

        // Toggle Authentication fields based on selection (Password vs Mobile OTP vs Aadhaar OTP)
        function toggleAuthFields() {
            const method = document.getElementById('link-auth-method').value;
            const authPasswordGroup = document.getElementById('auth-password-group');
            const authMobileGroup = document.getElementById('auth-mobile-group');
            const authOtpGroup = document.getElementById('auth-otp-group');
            const authProfilesGroup = document.getElementById('auth-profiles-group');
            
            const hfrOtpRequestContainer = document.getElementById('hfr-otp-request-container');
            const hfrOtpInputContainer = document.getElementById('hfr-otp-input-container');
            const btnHfrVerifyMobileOtp = document.getElementById('btn-hfr-verify-mobile-otp');
            const btnLinkFac = document.getElementById('btn-link-fac');

            // Hide all auth fields initially
            authPasswordGroup.style.display = 'none';
            authMobileGroup.style.display = 'none';
            authOtpGroup.style.display = 'none';
            authProfilesGroup.style.display = 'none';
            btnLinkFac.style.display = 'none';

            if (method === 'PASSWORD') {
                authPasswordGroup.style.display = 'block';
                btnLinkFac.style.display = 'block';
                btnLinkFac.innerHTML = '<i class="fa-solid fa-link"></i> Link HPR/Facility Manager';
            } else if (method === 'MOBILE_OTP') {
                authMobileGroup.style.display = 'block';
                authOtpGroup.style.display = 'block';
                document.getElementById('otp-message-text').innerText = 'OTP will be sent to this mobile number.';
                
                if (hfrOtpSent) {
                    hfrOtpRequestContainer.style.display = 'none';
                    hfrOtpInputContainer.style.display = 'block';
                    
                    if (document.getElementById('link-selected-hpr-id').options.length > 0) {
                        authProfilesGroup.style.display = 'block';
                        btnLinkFac.style.display = 'block';
                        btnLinkFac.innerHTML = '<i class="fa-solid fa-link"></i> Link Selected HPR';
                        btnHfrVerifyMobileOtp.style.display = 'none';
                    } else {
                        btnHfrVerifyMobileOtp.style.display = 'block';
                        btnLinkFac.style.display = 'none';
                    }
                } else {
                    hfrOtpRequestContainer.style.display = 'block';
                    hfrOtpInputContainer.style.display = 'none';
                    btnHfrVerifyMobileOtp.style.display = 'none';
                }
            } else if (method === 'AADHAAR_OTP') {
                authOtpGroup.style.display = 'block';
                document.getElementById('otp-message-text').innerText = 'OTP will be sent to the Aadhaar-linked mobile number for HPR ID.';
                
                if (hfrOtpSent) {
                    hfrOtpRequestContainer.style.display = 'none';
                    hfrOtpInputContainer.style.display = 'block';
                    btnLinkFac.style.display = 'block';
                    btnLinkFac.innerHTML = '<i class="fa-solid fa-link"></i> Verify OTP & Link HPR';
                } else {
                    hfrOtpRequestContainer.style.display = 'block';
                    hfrOtpInputContainer.style.display = 'none';
                }
            }
        }

        // Verify HPR ID Registry Details (Step 1 -> Step 2)
        function verifyHprId() {
            const hprId = document.getElementById('link-hpr-id').value.trim();
            if (!hprId) {
                showToast('Please enter an HPR ID.', 'error');
                return;
            }

            const btn = document.getElementById('btn-verify-hpr');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying...';

            fetch('{{ route("nhpr.register.fetch-hpr-profile") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ hpr_id: hprId })
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Verification failed.');
                }
                return res.json();
            })
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-user-shield"></i> Verify HPR Profile';

                if (data.success && data.profile) {
                    const prof = data.profile;
                    document.getElementById('hpr-profile-name').innerText = prof.name || 'Healthcare Professional';
                    document.getElementById('hpr-profile-id').innerText = 'ID: ' + (prof.hprId || hprId);
                    document.getElementById('hpr-profile-council').innerText = prof.council || '—';
                    document.getElementById('hpr-profile-regno').innerText = prof.registrationNumber || '—';
                    document.getElementById('hpr-profile-mobile').innerText = prof.maskedMobile || '—';
                    document.getElementById('hpr-profile-dob').innerText = prof.dob || '—';

                    // Reset OTP state and set auth selection back to default Password Login
                    hfrOtpSent = false;
                    hfrOtpTxnId = null;
                    document.getElementById('link-auth-method').value = 'PASSWORD';
                    document.getElementById('link-selected-hpr-id').innerHTML = '';
                    toggleAuthFields();

                    // Slide to Step 2
                    document.getElementById('link-step-1').style.display = 'none';
                    document.getElementById('link-step-2').style.display = 'block';
                    showToast('Practitioner profile retrieved successfully!');
                } else {
                    showToast(data.message || 'HPR ID not found.', 'error');
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-user-shield"></i> Verify HPR Profile';
                showToast('HPR verification service offline.', 'error');
            });
        }

        // Back action (Step 2 -> Step 1)
        function goBackToStep1() {
            document.getElementById('link-step-2').style.display = 'none';
            document.getElementById('link-step-1').style.display = 'block';
        }

        // Bind buttons after DOM loads
        document.addEventListener('DOMContentLoaded', function() {
            const btnHfrSendOtp = document.getElementById('btn-hfr-send-otp');
            const btnHfrResendOtp = document.getElementById('btn-hfr-resend-otp');
            const btnHfrVerifyMobileOtp = document.getElementById('btn-hfr-verify-mobile-otp');
            const hfrOtpRequestContainer = document.getElementById('hfr-otp-request-container');
            const hfrOtpInputContainer = document.getElementById('hfr-otp-input-container');

            function triggerSendOtp(btnElement) {
                const method = document.getElementById('link-auth-method').value;
                const payload = { auth_method: method };

                if (method === 'MOBILE_OTP') {
                    const mobile = document.getElementById('link-mobile-number').value.trim();
                    if (!mobile || mobile.length !== 10) {
                        showToast('Please enter a valid 10-digit mobile number.', 'warning');
                        return;
                    }
                    payload.mobile = mobile;
                } else {
                    const hprId = document.getElementById('link-hpr-id').value.trim();
                    if (!hprId) {
                        showToast('Please enter HPR ID.', 'warning');
                        return;
                    }
                    payload.hpr_id = hprId;
                }

                btnElement.disabled = true;
                btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

                fetch('{{ route("nhpr.register.link-existing.send-otp") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    btnElement.disabled = false;
                    btnElement.innerHTML = btnElement.id === 'btn-hfr-send-otp' ? '<i class="fa-solid fa-paper-plane"></i> Send OTP' : '<i class="fa-solid fa-rotate-right"></i> Resend OTP';

                    if (data.success) {
                        hfrOtpSent = true;
                        hfrOtpTxnId = data.txnId;
                        document.getElementById('link-selected-hpr-id').innerHTML = ''; // clear previous profiles

                        hfrOtpRequestContainer.style.display = 'none';
                        hfrOtpInputContainer.style.display = 'block';
                        
                        toggleAuthFields();
                        showToast(data.message || 'OTP sent successfully!');
                    } else {
                        showToast(data.message || 'Failed to send OTP.', 'error');
                    }
                })
                .catch(() => {
                    btnElement.disabled = false;
                    btnElement.innerHTML = btnElement.id === 'btn-hfr-send-otp' ? '<i class="fa-solid fa-paper-plane"></i> Send OTP' : '<i class="fa-solid fa-rotate-right"></i> Resend OTP';
                    showToast('Failed to send OTP.', 'error');
                });
            }

            if (btnHfrSendOtp) {
                btnHfrSendOtp.addEventListener('click', function() {
                    triggerSendOtp(btnHfrSendOtp);
                });
            }

            if (btnHfrResendOtp) {
                btnHfrResendOtp.addEventListener('click', function() {
                    triggerSendOtp(btnHfrResendOtp);
                });
            }

            if (btnHfrVerifyMobileOtp) {
                btnHfrVerifyMobileOtp.addEventListener('click', function() {
                    const mobile = document.getElementById('link-mobile-number').value.trim();
                    const otp = document.getElementById('link-otp').value.trim();
                    
                    if (!otp || otp.length !== 6) {
                        showToast('Please enter a valid 6-digit OTP.', 'warning');
                        return;
                    }

                    btnHfrVerifyMobileOtp.disabled = true;
                    btnHfrVerifyMobileOtp.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying OTP...';

                    fetch('{{ route("nhpr.register.link-existing.verify-mobile-otp") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            mobile: mobile,
                            txn_id: hfrOtpTxnId,
                            otp: otp
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        btnHfrVerifyMobileOtp.disabled = false;
                        btnHfrVerifyMobileOtp.innerHTML = '<i class="fa-solid fa-shield-halved"></i> Verify OTP & Fetch Profiles';

                        if (data.success) {
                            hfrOtpTxnId = data.txnId;
                            const dropdown = document.getElementById('link-selected-hpr-id');
                            dropdown.innerHTML = '';

                            if (data.profiles && data.profiles.length > 0) {
                                data.profiles.forEach(p => {
                                    const opt = document.createElement('option');
                                    opt.value = p.hprId;
                                    opt.innerText = `${p.name} (${p.hprId})`;
                                    dropdown.appendChild(opt);
                                });
                                toggleAuthFields();
                                showToast('HPR profiles fetched successfully! Select HPR profile to complete linkage.');
                            } else {
                                showToast('No HPR profiles found linked to this mobile number.', 'error');
                            }
                        } else {
                            showToast(data.message || 'OTP verification failed.', 'error');
                        }
                    })
                    .catch(() => {
                        btnHfrVerifyMobileOtp.disabled = false;
                        btnHfrVerifyMobileOtp.innerHTML = '<i class="fa-solid fa-shield-halved"></i> Verify OTP & Fetch Profiles';
                        showToast('OTP verification failed.', 'error');
                    });
                });
            }

            // Initialize HFR Master Dropdowns if authenticated/form present
            initMasterDropdowns();
        });

        // Initialize HFR Master Dropdowns
        function initMasterDropdowns() {
            const createForm = document.getElementById('create-form');
            if (!createForm) return;

            // Load LGD States
            fetch('{{ route("nhpr.hfr.masters.states") }}')
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const stateSel = document.getElementById('fac-state');
                        stateSel.innerHTML = '<option value="">Select State</option>';
                        res.data.forEach(state => {
                            const opt = document.createElement('option');
                            opt.value = state.code;
                            opt.innerText = `${state.name} (${state.code})`;
                            stateSel.appendChild(opt);
                        });
                        // Set default to Uttarakhand (05)
                        stateSel.value = "05";
                        triggerStateChange();
                    }
                })
                .catch(err => console.error('Failed to load states', err));
            // Load Ownership Types from Master Data API
            fetch('{{ route("nhpr.hfr.masters.data") }}?type=OWNER')
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const ownerSel = document.getElementById('fac-ownership');
                        ownerSel.innerHTML = '<option value="">Select Ownership Type</option>';
                        res.data.forEach(item => {
                            const opt = document.createElement('option');
                            opt.value = item.code.trim();
                            opt.innerText = `${item.value} (${item.code.trim()})`;
                            ownerSel.appendChild(opt);
                        });
                        ownerSel.value = "P";
                        triggerOwnershipChange();
                    }
                })
                .catch(err => console.error('Failed to load ownership types', err));

            // Load Systems of Medicine from Master Data API
            fetch('{{ route("nhpr.hfr.masters.data") }}?type=MEDICINE')
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const medSel = document.getElementById('fac-medicine');
                        medSel.innerHTML = '<option value="">Select System of Medicine</option>';
                        res.data.forEach(item => {
                            const opt = document.createElement('option');
                            opt.value = item.code.trim();
                            opt.innerText = `${item.value} (${item.code.trim()})`;
                            medSel.appendChild(opt);
                        });
                        medSel.value = "M";
                        triggerOwnershipOrMedicineChange();
                    }
                })
                .catch(err => console.error('Failed to load systems of medicine', err));
        }

        function triggerStateChange() {
            const stateCode = document.getElementById('fac-state').value;
            const distSel = document.getElementById('fac-district');
            const subdistSel = document.getElementById('fac-subdistrict');

            distSel.innerHTML = '<option value="">Select District</option>';
            distSel.disabled = true;
            subdistSel.innerHTML = '<option value="">Select District First</option>';
            subdistSel.disabled = true;

            if (!stateCode) return;

            distSel.innerHTML = '<option value="">Loading districts...</option>';
            fetch(`{{ route("nhpr.hfr.masters.districts") }}?stateCode=${stateCode}`)
                .then(res => res.json())
                .then(res => {
                    distSel.innerHTML = '<option value="">Select District</option>';
                    if (res.success && res.data) {
                        res.data.forEach(dist => {
                            const opt = document.createElement('option');
                            opt.value = dist.code;
                            opt.innerText = `${dist.name} (${dist.code})`;
                            distSel.appendChild(opt);
                        });
                        distSel.disabled = false;
                        if (stateCode === "05") {
                            distSel.value = "060";
                        } else if (stateCode === "24") {
                            distSel.value = "474";
                        }
                        triggerDistrictChange();
                    }
                })
                .catch(err => {
                    distSel.innerHTML = '<option value="">Error loading districts</option>';
                    console.error(err);
                });
        }

        function triggerDistrictChange() {
            const distCode = document.getElementById('fac-district').value;
            const subdistSel = document.getElementById('fac-subdistrict');

            subdistSel.innerHTML = '<option value="">Select Sub-District</option>';
            subdistSel.disabled = true;

            if (!distCode) return;

            subdistSel.innerHTML = '<option value="">Loading sub-districts...</option>';
            fetch(`{{ route("nhpr.hfr.masters.subdistricts") }}?districtCode=${distCode}`)
                .then(res => res.json())
                .then(res => {
                    subdistSel.innerHTML = '<option value="">Select Sub-District</option>';
                    if (res.success && res.data) {
                        res.data.forEach(sub => {
                            const opt = document.createElement('option');
                            opt.value = sub.code;
                            opt.innerText = `${sub.name} (${sub.code})`;
                            subdistSel.appendChild(opt);
                        });
                        subdistSel.disabled = false;
                        if (distCode === "060") {
                            subdistSel.value = "0501";
                        } else if (distCode === "474") {
                            subdistSel.value = "3924";
                        }
                    }
                })
                .catch(err => {
                    subdistSel.innerHTML = '<option value="">Error loading sub-districts</option>';
                    console.error(err);
                });
        }

        function triggerOwnershipOrMedicineChange() {
            const ownershipCode = document.getElementById('fac-ownership').value;
            const systemOfMedicineCode = document.getElementById('fac-medicine').value;
            const typeSel = document.getElementById('fac-type');

            typeSel.innerHTML = '<option value="">Select Facility Category/Type</option>';
            typeSel.disabled = true;

            if (!ownershipCode || !systemOfMedicineCode) return;

            typeSel.innerHTML = '<option value="">Loading facility types...</option>';
            fetch('{{ route("nhpr.hfr.masters.facility-types") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ ownershipCode, systemOfMedicineCode })
            })
            .then(res => res.json())
            .then(res => {
                typeSel.innerHTML = '<option value="">Select Facility Category/Type</option>';
                if (res.success && res.data) {
                    res.data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.code.trim();
                        opt.innerText = `${item.value} (${item.code.trim()})`;
                        typeSel.appendChild(opt);
                    });
                    typeSel.disabled = false;
                    typeSel.value = "5";
                }
            })
            .catch(err => {
                typeSel.innerHTML = '<option value="">Error loading facility types</option>';
                console.error(err);
            });
        }

        function triggerOwnershipChange() {
            const ownershipCode = document.getElementById('fac-ownership').value;
            const subtypeSel = document.getElementById('fac-ownership-subtype');
            const subtype2Sel = document.getElementById('fac-ownership-subtype2');

            subtypeSel.innerHTML = '<option value="">Select Owner Subtype</option>';
            subtypeSel.disabled = true;
            subtype2Sel.innerHTML = '<option value="">Select Subtype First</option>';
            subtype2Sel.disabled = true;

            triggerOwnershipOrMedicineChange();

            if (!ownershipCode) return;

            subtypeSel.innerHTML = '';
            if (ownershipCode === "G") {
                const options = [
                    { code: "S", value: "State Government" },
                    { code: "C", value: "Central Government" },
                    { code: "L", value: "Local Body" }
                ];
                options.forEach(opt => {
                    const el = document.createElement('option');
                    el.value = opt.code;
                    el.innerText = `${opt.value} (${opt.code})`;
                    subtypeSel.appendChild(el);
                });
                subtypeSel.disabled = false;
                subtypeSel.value = "S";
            } else if (ownershipCode === "P" || ownershipCode === "PP") {
                const options = [
                    { code: "P", value: "Profit" },
                    { code: "NP", value: "Non-Profit" }
                ];
                options.forEach(opt => {
                    const el = document.createElement('option');
                    el.value = opt.code;
                    el.innerText = `${opt.value} (${opt.code})`;
                    subtypeSel.appendChild(el);
                });
                subtypeSel.disabled = false;
                subtypeSel.value = "P";
            }

            triggerOwnerSubtypeChange();
        }

        function triggerOwnerSubtypeChange() {
            const ownershipCode = document.getElementById('fac-ownership').value;
            const ownerSubtypeCode = document.getElementById('fac-ownership-subtype').value;
            const subtype2Sel = document.getElementById('fac-ownership-subtype2');

            subtype2Sel.innerHTML = '<option value="">Select Owner Subtype 2</option>';
            subtype2Sel.disabled = true;

            if (!ownershipCode || !ownerSubtypeCode) return;

            subtype2Sel.innerHTML = '<option value="">Loading subtypes...</option>';
            fetch('{{ route("nhpr.hfr.masters.owner-subtypes") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ ownershipCode: ownershipCode, ownerSubtypeCode: ownerSubtypeCode })
            })            .then(res => res.json())
            .then(res => {
                subtype2Sel.innerHTML = '<option value="">Select Owner Subtype 2</option>';
                if (res.success && res.data && res.data.length > 0) {
                    res.data.forEach(item => {
                        const opt = document.createElement('option');
                        opt.value = item.code.trim();
                        opt.innerText = `${item.value} (${item.code.trim()})`;
                        subtype2Sel.appendChild(opt);
                    });
                    subtype2Sel.disabled = false;
                    if (ownershipCode === "G" && ownerSubtypeCode === "S") {
                        subtype2Sel.value = "MOHF";
                    } else if (ownershipCode === "P" && ownerSubtypeCode === "P") {
                        subtype2Sel.value = "PP01";
                    }
                } else {
                    subtype2Sel.innerHTML = '<option value="">Not Applicable</option>';
                    subtype2Sel.disabled = true;
                }
            })
            .catch(err => {
                subtype2Sel.innerHTML = '<option value="">Error loading subtypes</option>';
                console.error(err);
            });
        }

        // Final submit linkage form (Password, Mobile OTP or Aadhaar OTP)
        function submitLinkageForm(event) {
            event.preventDefault();
            const btn = document.getElementById('btn-link-fac');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Authenticating & Linking...';

            const authMethod = document.getElementById('link-auth-method').value;
            const payload = {
                auth_method: authMethod,
                facility_id: document.getElementById('link-facility-id').value,
                facility_name: document.getElementById('link-facility-name').value,
                facility_address: document.getElementById('link-facility-address').value || 'Dehradun',
                facility_pincode: document.getElementById('link-facility-pincode').value || '248001',
            };

            if (authMethod === 'PASSWORD') {
                const passVal = document.getElementById('link-password').value;
                if (!passVal) {
                    showToast('Please enter HPR password.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-link"></i> Link HPR/Facility Manager';
                    return;
                }
                payload.hpr_id = document.getElementById('link-hpr-id').value.trim();
                payload.password = passVal;
            } else if (authMethod === 'MOBILE_OTP') {
                const selectedHpr = document.getElementById('link-selected-hpr-id').value;
                if (!selectedHpr) {
                    showToast('Please select HPR Profile first.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-link"></i> Link Selected HPR';
                    return;
                }
                payload.mobile = document.getElementById('link-mobile-number').value.trim();
                payload.selected_hpr_id = selectedHpr;
                payload.txn_id = hfrOtpTxnId;
            } else if (authMethod === 'AADHAAR_OTP') {
                const otpVal = document.getElementById('link-otp').value.trim();
                if (!otpVal) {
                    showToast('Please enter the OTP.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-link"></i> Verify OTP & Link HPR';
                    return;
                }
                payload.hpr_id = document.getElementById('link-hpr-id').value.trim();
                payload.otp = otpVal;
                payload.txn_id = hfrOtpTxnId;
            }

            fetch('{{ route("nhpr.register.link-existing") }}', {
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
                btn.innerHTML = '<i class="fa-solid fa-link"></i> Link HPR/Facility Manager';

                if (data.success) {
                    showToast(data.message || 'Successfully linked HFR with HPR/Facility Manager!');
                    
                    // Clear inputs and reset views back to Step 1
                    document.getElementById('link-facility-id').value = '';
                    document.getElementById('link-facility-name').value = '';
                    document.getElementById('link-otp').value = '';
                    document.getElementById('link-password').value = '';
                    document.getElementById('link-mobile-number').value = '';
                    document.getElementById('link-selected-hpr-id').innerHTML = '';
                    hfrOtpSent = false;
                    hfrOtpTxnId = null;
                    
                    goBackToStep1();
                } else {
                    showToast(data.message || 'Authentication & linkage failed.', 'error');
                }
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = authMethod === 'OTP' 
                    ? '<i class="fa-solid fa-link"></i> Verify OTP & Link HPR' 
                    : '<i class="fa-solid fa-link"></i> Link HPR/Facility Manager';
                showToast('HFR linkage request failed.', 'error');
            });
        }

        // Switch listener to toggle API mode
        const liveModeSwitch = document.getElementById('live-mode-switch');
        const configBtnWrap = document.getElementById('credentials-config-btn-wrap');
        const statusDot = document.getElementById('gateway-status-dot');
        const statusText = document.getElementById('gateway-status-text');

        if (liveModeSwitch) {
            liveModeSwitch.addEventListener('change', function () {
                const isLive = this.checked;

                fetch('{{ route("nhpr.register.toggle-mode") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ real_api_mode: isLive ? 1 : 0 })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(isLive ? 'Live API Mode activated.' : 'Simulated Mode activated.', 'info');
                        if (isLive) {
                            configBtnWrap.style.display = 'block';
                            if (statusDot) {
                                statusDot.classList.add('active');
                            }
                            if (statusText) {
                                statusText.innerText = 'Live API Connected';
                            }
                        } else {
                            configBtnWrap.style.display = 'none';
                            if (statusDot) {
                                statusDot.classList.remove('active');
                            }
                            if (statusText) {
                                statusText.innerText = 'Simulated Sandbox Mode';
                            }
                        }
                    } else {
                        showToast('Failed to toggle API mode.', 'error');
                        liveModeSwitch.checked = !isLive;
                    }
                })
                .catch(() => {
                    showToast('Communication error with portal server.', 'error');
                    liveModeSwitch.checked = !isLive;
                });
            });
        }

        // ─── Facility Photo Upload Helper ────────────────────────────────────
        function handleFacilityPhotoUpload(input, type) {
            const file = input.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                showToast('Image must be under 2 MB.', 'error');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                const base64Full = e.target.result;   // data:image/png;base64,iVBOR...
                const base64Only = base64Full.split(',')[1]; // strip data URI prefix
                const prefix     = type === 'building' ? 'fac-building-photo' : 'fac-board-photo';

                document.getElementById(prefix + '-name').value  = file.name;
                document.getElementById(prefix + '-value').value = base64Only;

                // Show preview
                document.getElementById(prefix + '-img').src              = base64Full;
                document.getElementById(prefix + '-preview').style.display = 'block';
                document.getElementById(prefix + '-placeholder').style.display = 'none';
            };
            reader.readAsDataURL(file);
        }

        // ─── Facility Timings Helper ──────────────────────────────────────────
        function toggleTimingRow(dayCode) {
            const checked = document.getElementById('timing-open-' + dayCode).checked;
            const row     = document.getElementById('timing-row-' + dayCode);
            row.style.opacity = checked ? '1' : '0.5';
            document.getElementById('timing-opens-'  + dayCode).disabled = !checked;
            document.getElementById('timing-closes-' + dayCode).disabled = !checked;
        }

        function applyBulkTimings() {
            const openVal  = document.getElementById('timing-bulk-open').value;
            const closeVal = document.getElementById('timing-bulk-close').value;
            if (!openVal || !closeVal) {
                showToast('Please select both open and close times.', 'error');
                return;
            }
            const days = ['MON','TUE','WED','THU','FRI','SAT','SUN'];
            days.forEach(day => {
                if (document.getElementById('timing-open-' + day).checked) {
                    document.getElementById('timing-opens-'  + day).value = openVal;
                    document.getElementById('timing-closes-' + day).value = closeVal;
                }
            });
            showToast('Timings applied to all open days.');
        }

        function collectFacilityTimings() {
            const days    = ['MON','TUE','WED','THU','FRI','SAT','SUN'];
            const timings = [];
            days.forEach(day => {
                if (document.getElementById('timing-open-' + day).checked) {
                    const opensAt  = document.getElementById('timing-opens-'  + day).value;
                    const closesAt = document.getElementById('timing-closes-' + day).value;
                    timings.push({
                        workingDays:  day,
                        openingHours: opensAt + ' - ' + closesAt,
                    });
                }
            });
            return timings;
        }

        // ─── TAB 4: Look Up Facility ────────────────────────────────────────
        function trackRegistration() {
            const facilityId = document.getElementById('track-id-input').value.trim();
            if (!facilityId) {
                showToast('Please enter an HFR Facility ID (e.g. IN2710000059).', 'error');
                return;
            }

            const btn = document.getElementById('btn-track');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Looking up...';

            // Hide previous results/errors
            document.getElementById('track-result').style.display = 'none';
            document.getElementById('track-error').style.display  = 'none';

            fetch('{{ route("nhpr.hfr.track") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ facilityId })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Look Up';

                if (!data.success) {
                    document.getElementById('track-error-text').textContent = data.message || 'Facility not found.';
                    document.getElementById('track-error').style.display = 'flex';
                    return;
                }

                const fac = data.facility || {};

                // Name & ID
                document.getElementById('track-facility-name').textContent = fac.facilityName || data.facilityId || '—';
                document.getElementById('track-facility-id').textContent   = fac.facilityId   || data.facilityId || '—';

                // Detail fields
                document.getElementById('track-ownership').textContent     = fac.ownership     || fac.ownershipCode || '—';
                document.getElementById('track-facility-type').textContent = fac.facilityType  || fac.facilityTypeCode || '—';
                document.getElementById('track-medicine').textContent      = fac.systemOfMedicine || fac.systemOfMedicineCode || '—';
                document.getElementById('track-state').textContent         = fac.stateName     || '—';
                document.getElementById('track-district').textContent      = fac.districtName  || '—';
                document.getElementById('track-pincode').textContent       = fac.pincode       || '—';
                document.getElementById('track-address').textContent       = fac.address       || '—';

                // Status badge
                const statusColors = {
                    'SUBMITTED': 'var(--success)',
                    'APPROVED':  'var(--success)',
                    'PENDING':   'var(--warning,#f59e0b)',
                    'DRAFT':     'var(--text-muted)',
                    'REJECTED':  'var(--danger,#ef4444)',
                };
                const st    = (fac.facilityStatus || data.status || 'UNKNOWN').toUpperCase();
                const color = statusColors[st] || 'var(--primary)';
                document.getElementById('track-status-badge').innerHTML =
                    `<span style="background:${color}22;color:${color};padding:4px 14px;border-radius:20px;font-size:13px;font-weight:600;">${st}</span>`;

                document.getElementById('track-result').style.display = 'block';
                showToast('Facility details loaded successfully.');
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Look Up';
                const msg = err.message || 'An error occurred.';
                document.getElementById('track-error-text').textContent = msg;
                document.getElementById('track-error').style.display = 'flex';
                showToast(msg, 'error');
            });
        }
        // ────────────────────────────────────────────────────────────────────
    </script>
</body>

</html>
