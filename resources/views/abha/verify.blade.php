<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verify ABHA Address – ABDM Milestone 1</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Noto+Sans+Devanagari:wght@400;600;700&display=swap" rel="stylesheet">
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
            margin-top: 20px;
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
            min-height: 100vh;
        }

        /* Uttarakhand Government Sticky Topbar */
        .gov-topbar {
            background: var(--surface);
            border-bottom: 3px solid var(--saffron);
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .gov-emblem {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .gov-emblem img {
            height: 38px;
            filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.2));
        }

        .gov-title-text {
            display: flex;
            flex-direction: column;
        }

        .gov-hindi {
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-size: 11.5px;
            font-weight: 600;
            color: #fff;
        }

        .gov-english {
            font-size: 9.5px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .gateway-status {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-dot.active {
            background: var(--success-light);
            box-shadow: 0 0 8px var(--success-light);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(129, 199, 132, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(129, 199, 132, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(129, 199, 132, 0);
            }
        }

        /* Content Area */
        .content {
            padding: 28px 24px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .page-header {
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.3px;
        }

        .page-subtitle {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 3px;
        }

        /* Tabs Nav styling */
        .tabs-nav {
            display: flex;
            gap: 12px;
            margin: 0 auto 24px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
            max-width: 640px;
            width: 100%;
        }

        .tab-btn {
            background: none;
            border: none;
            color: var(--muted);
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            padding-bottom: 8px;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .tab-btn:hover {
            color: #fff;
        }

        .tab-btn.active {
            color: var(--saffron);
            border-bottom-color: var(--saffron);
        }

        .tab-content {
            display: none;
            width: 100%;
        }

        .tab-content.active {
            display: block;
        }

        /* Form Panels */
        .form-panel {
            display: none;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            animation: slideUp 0.3s ease forwards;
            max-width: 640px;
            width: 100%;
            margin: 0 auto;
        }

        .form-panel.active {
            display: block;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .panel-header {
            margin-bottom: 24px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 14px;
        }

        .panel-title {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-title i {
            color: var(--saffron);
        }

        .panel-subtitle {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-label {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--muted2);
        }

        .req {
            color: var(--danger-light);
            margin-left: 2px;
        }

        .form-control {
            width: 100%;
            background: var(--surface2);
            border: 1.5px solid var(--border2);
            border-radius: 8px;
            padding: 11px 14px;
            color: #fff;
            font-size: 13.5px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--saffron);
            box-shadow: 0 0 0 3px rgba(230, 81, 0, 0.15);
        }

        .form-control.error {
            border-color: var(--danger-light);
        }

        .form-error {
            color: var(--danger-light);
            font-size: 11.5px;
            margin-top: 4px;
            display: none;
        }

        .auth-method-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .method-option {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .method-option:hover {
            border-color: rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.02);
        }

        .method-option input {
            cursor: pointer;
        }

        .method-details {
            text-align: left;
        }

        .method-title {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
        }

        .method-desc {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
        }

        .btn-action {
            background: var(--saffron);
            border: none;
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s ease, box-shadow 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-action:hover {
            background: var(--saffron-mid);
            box-shadow: 0 4px 12px rgba(230, 81, 0, 0.3);
        }

        .btn-action:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Upload Card UI */
        .doc-upload-item {
            border: 2.5px dashed var(--border2);
            background: var(--surface2);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 160px;
        }

        .doc-upload-item:hover {
            border-color: var(--saffron);
            background: rgba(230, 81, 0, 0.02);
        }

        .doc-icon {
            font-size: 32px;
            color: var(--muted);
        }

        .doc-title {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
        }

        .doc-subtitle {
            font-size: 11px;
            color: var(--muted);
        }

        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        /* Virtual Card UI */
        .abha-card-wrap {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .abha-virtual-card {
            width: 100%;
            max-width: 420px;
            height: 250px;
            background: linear-gradient(135deg, #2d1808, #160a03);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-header-gov {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            padding-bottom: 10px;
        }

        .card-gov-text {
            text-align: left;
        }

        .card-gov-text h3 {
            font-size: 10px;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.5px;
        }

        .card-gov-text p {
            font-size: 8px;
            color: var(--muted);
            text-transform: uppercase;
        }

        .card-logo-emblem img {
            height: 26px;
        }

        .card-profile-area {
            display: flex;
            gap: 14px;
            align-items: center;
            margin: 12px 0;
        }

        .card-photo-box {
            width: 72px;
            height: 86px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: var(--muted);
            overflow: hidden;
        }

        .card-photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-details {
            text-align: left;
        }

        .card-name {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }

        .card-number-label {
            font-size: 8.5px;
            color: var(--muted);
            text-transform: uppercase;
            margin-top: 6px;
        }

        .card-number-value {
            font-size: 15px;
            font-weight: 800;
            color: #ffb74d;
            letter-spacing: 0.5px;
        }

        .card-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
            font-size: 9.5px;
        }

        .card-meta-item span {
            color: var(--muted);
        }

        .card-meta-item strong {
            color: #fff;
        }

        .card-footer-ndhm {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid rgba(255,255,255,0.08);
            padding-top: 6px;
            font-size: 8.5px;
            color: var(--muted);
        }

        .ndhm-text {
            font-weight: 700;
            color: #fff;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
    </style>
</head>

<body>
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
                <a href="{{ route('abha.verify') }}" class="nav-item active">
                    <i class="fa-solid fa-address-card"></i> Verify ABHA Address
                </a>

                <div class="nav-grp-title">HPR (Doctors & Nurses)</div>
                <a href="{{ route('nhpr.register.wizard') }}" class="nav-item">
                    <i class="fa-solid fa-user-doctor"></i> HPR Register
                </a>
                <a href="{{ route('nhpr.track.show') }}" class="nav-item">
                    <i class="fa-solid fa-binoculars"></i> Track Status
                </a>
            </div>
        </div>

        <!-- Main Container -->
        <div class="main">
            <!-- Sticky Topbar -->
            <div class="gov-topbar">
                <div class="gov-emblem">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/9/99/Seal_of_Uttarakhand.svg" alt="Uttarakhand Govt Seal">
                    <div class="gov-title-text">
                        <span class="gov-hindi">उत्तराखंड शासन</span>
                        <span class="gov-english">State Health Intelligence Platform</span>
                    </div>
                </div>

                <div class="gateway-status">
                    <span class="status-dot active"></span>
                    <span style="color: var(--muted2);">Mode: {{ $config['realApiMode'] ? 'REAL API' : 'SIMULATION' }}</span>
                </div>
            </div>

            <!-- Scrollable Content Body -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Verify ABHA Address</h1>
                    <p class="page-subtitle">Verify ownership of an existing PHR address to secure patient healthcare linkage</p>
                </div>

                <!-- Tab Controls -->
                <div class="tabs-nav">
                    <button class="tab-btn active" data-tab="tab-otp">OTP Channel</button>
                    <button class="tab-btn" data-tab="tab-qr">QR Verification</button>
                    <button class="tab-btn" data-tab="tab-demographics">Demographic Verify</button>
                </div>

                <!-- SUCCESS SCREEN (Common target panel for all tabs) -->
                <div class="form-panel" id="panel-success" style="margin-top: 10px;">
                    <div class="panel-header" style="text-align: center;">
                        <h2 class="panel-title" style="justify-content: center; color: var(--success-light);">
                            <i class="fa-solid fa-circle-check"></i> Address Verified!
                        </h2>
                        <p class="panel-subtitle">ABHA Address successfully verified. Profile retrieved below.</p>
                    </div>

                    <!-- Virtual Card UI -->
                    <div class="abha-card-wrap">
                        <div class="abha-virtual-card">
                            <div class="card-header-gov">
                                <div class="card-gov-text">
                                    <h3>NATIONAL HEALTH AUTHORITY</h3>
                                    <p>Uttarakhand State Health Platform</p>
                                </div>
                                <div class="card-logo-emblem">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/9/99/Seal_of_Uttarakhand.svg" alt="UK Government Emblem">
                                </div>
                            </div>

                            <div class="card-profile-area">
                                <div class="card-photo-box" id="card-photo">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div class="card-details">
                                    <div class="card-name" id="card-name-val">Verified Profile</div>
                                    <div class="card-number-label">ABHA Address / PHR ID</div>
                                    <div class="card-number-value" id="card-address-val">username@sbx</div>
                                </div>
                            </div>

                            <div class="card-meta-grid">
                                <div class="card-meta-item">
                                    <span>ABHA No:</span> <strong id="card-number-val">00-0000-0000-0000</strong>
                                </div>
                                <div class="card-meta-item">
                                    <span>Status:</span> <strong style="color: var(--success-light);" id="card-status-val">ACTIVE</strong>
                                </div>
                            </div>

                            <div class="card-meta-grid" style="margin-top: 4px;">
                                <div class="card-meta-item">
                                    <span>Gender:</span> <strong id="card-gender-val">M</strong>
                                </div>
                                <div class="card-meta-item">
                                    <span>DOB:</span> <strong id="card-dob-val">1990-01-01</strong>
                                </div>
                            </div>

                            <div class="card-meta-grid" style="margin-top: 4px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 4px;">
                                <div class="card-meta-item">
                                    <span>Mobile:</span> <strong id="card-mobile-val">9988776655</strong>
                                </div>
                                <div class="card-meta-item">
                                    <span>Link Token:</span> <strong style="color: var(--success-light);" id="card-token-val">GENERATED</strong>
                                </div>
                            </div>

                            <div class="card-footer-ndhm">
                                <span>Ayushman Bharat Digital Mission</span>
                                <span class="ndhm-text">ABHA</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 24px; display: flex; gap: 12px;">
                        <button id="btn-download-card" class="btn-action" style="background: var(--primary); max-width: 240px;">
                            <i class="fa-solid fa-download"></i> Download ABHA Card
                        </button>
                        <a href="{{ route('abha.dashboard') }}" class="btn-action" style="background: var(--surface2); border: 1px solid var(--border); text-decoration: none; max-width: 180px;">
                            <i class="fa-solid fa-arrow-left-long"></i> Dashboard
                        </a>
                    </div>
                </div>

                <!-- TAB 1: OTP VERIFICATION -->
                <div class="tab-content active" id="tab-otp">
                    <!-- Panel 1: Search Address -->
                    <div class="form-panel active" id="panel-search">
                        <div class="panel-header">
                            <h2 class="panel-title"><i class="fa-solid fa-magnifying-glass"></i> Search ABHA Address</h2>
                            <p class="panel-subtitle">Enter the patient's custom ABHA Address (e.g., rahul@sbx).</p>
                        </div>
                        <form id="form-search">
                            <div class="form-group">
                                <label class="form-label" for="abha_address">ABHA Address / PHR ID <span class="req">*</span></label>
                                <input type="text" id="abha_address" class="form-control" placeholder="username@sbx" required>
                                <div class="form-error" id="error-address">Please enter a valid ABHA Address.</div>
                            </div>
                            <button type="submit" class="btn-action" id="btn-search">
                                Search Address <i class="fa-solid fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Panel 2: Select Auth Method -->
                    <div class="form-panel" id="panel-method">
                        <div class="panel-header">
                            <h2 class="panel-title"><i class="fa-solid fa-key"></i> Choose Authentication Method</h2>
                            <p class="panel-subtitle">Select how the user wishes to verify ownership of <strong id="searched-address">...</strong>.</p>
                        </div>
                        <form id="form-request-otp">
                            <div class="auth-method-grid" id="auth-methods-container">
                                <!-- Dynamic methods list -->
                            </div>
                            <button type="submit" class="btn-action" id="btn-request-otp">
                                Send OTP <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Panel 3: Input OTP -->
                    <div class="form-panel" id="panel-otp">
                        <div class="panel-header">
                            <h2 class="panel-title"><i class="fa-solid fa-lock"></i> OTP Verification</h2>
                            <p class="panel-subtitle" id="verify-hint-text">An OTP has been requested to the registered contact detail.</p>
                        </div>
                        <form id="form-verify">
                            <div class="form-group">
                                <label class="form-label" for="otp">Enter 6-Digit OTP <span class="req">*</span></label>
                                <input type="text" id="otp" class="form-control" placeholder="Enter 6-Digit OTP" maxlength="6" pattern="\d{6}" required>
                                <div class="form-error" id="error-otp">Please enter a valid 6-digit OTP.</div>
                            </div>
                            <button type="submit" class="btn-action" id="btn-verify">
                                Verify & Load Profile <i class="fa-solid fa-circle-check"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- TAB 2: QR VERIFICATION -->
                <div class="tab-content" id="tab-qr">
                    <div class="form-panel active" id="panel-qr">
                        <div class="panel-header">
                            <h2 class="panel-title"><i class="fa-solid fa-qrcode"></i> QR Code Verification</h2>
                            <p class="panel-subtitle">Upload the citizen's ABHA card QR code image to parse and link records instantly.</p>
                        </div>
                        <form id="form-qr-verify">
                            <div class="form-group" style="margin-bottom: 24px;">
                                <div class="doc-upload-item" id="qr-upload-item">
                                    <i class="fa-solid fa-qrcode doc-icon" id="qr-icon-status"></i>
                                    <div class="doc-title" id="qr-title-status">Upload / Drop ABHA Card QR</div>
                                    <div class="doc-subtitle">Supports JPG, PNG formats up to 2MB</div>
                                    <input type="file" id="qr-file-input" class="file-input" accept="image/*" required>
                                </div>
                            </div>

                            <button type="submit" class="btn-action" id="btn-qr-submit" disabled>
                                Scan & Verify QR <i class="fa-solid fa-bolt"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- TAB 3: DEMOGRAPHIC VERIFICATION -->
                <div class="tab-content" id="tab-demographics">
                    <div class="form-panel active" id="panel-demographics">
                        <div class="panel-header">
                            <h2 class="panel-title"><i class="fa-solid fa-fingerprint"></i> Demographic Verification</h2>
                            <p class="panel-subtitle">Enter key demographic details to match identity variables directly against ABDM registries.</p>
                        </div>
                        <form id="form-demographics-verify">
                            <div class="form-group">
                                <label class="form-label" for="demo-name">Full Name <span class="req">*</span></label>
                                <input type="text" id="demo-name" class="form-control" placeholder="Aadhaar Holder Name" required>
                            </div>

                            <div class="grid-2">
                                <div class="form-group">
                                    <label class="form-label" for="demo-gender">Gender <span class="req">*</span></label>
                                    <select id="demo-gender" class="form-control" style="background-color: var(--surface2); color: #fff;" required>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                        <option value="O">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="demo-yob">Year of Birth (YYYY) <span class="req">*</span></label>
                                    <input type="text" id="demo-yob" class="form-control" placeholder="e.g. 1988" maxlength="4" pattern="\d{4}" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="demo-mobile">Registered Mobile <span class="req">*</span></label>
                                <input type="text" id="demo-mobile" class="form-control" placeholder="10-digit mobile number" maxlength="10" pattern="\d{10}" required>
                            </div>

                            <button type="submit" class="btn-action" id="btn-demo-submit">
                                Verify Demographic Match <i class="fa-solid fa-user-check"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Actions -->
    <script>
        // Tab switching logic
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        const panelSuccess = document.getElementById('panel-success');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Clear active states
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                panelSuccess.classList.remove('active');

                // Set new active state
                btn.classList.add('active');
                const targetTab = btn.getAttribute('data-tab');
                document.getElementById(targetTab).classList.add('active');

                // Reset standard panels inside Tab 1 if switching back
                if (targetTab === 'tab-otp') {
                    document.getElementById('panel-search').classList.add('active');
                    document.getElementById('panel-method').classList.remove('active');
                    document.getElementById('panel-otp').classList.remove('active');
                }
            });
        });

        // ════════════════════════════════════════
        // TAB 1: OTP LOGIN JS
        // ════════════════════════════════════════
        const formSearch = document.getElementById('form-search');
        const formRequestOtp = document.getElementById('form-request-otp');
        const formVerify = document.getElementById('form-verify');
        const addressInput = document.getElementById('abha_address');
        const otpInput = document.getElementById('otp');
        
        const panelSearch = document.getElementById('panel-search');
        const panelMethod = document.getElementById('panel-method');
        const panelOtp = document.getElementById('panel-otp');

        formSearch.addEventListener('submit', function(e) {
            e.preventDefault();
            const addressVal = addressInput.value.trim();
            const btn = document.getElementById('btn-search');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Searching...';
            
            fetch("{{ route('abha.verify.search') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ abha_address: addressVal })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Search Address <i class="fa-solid fa-search"></i>';
                
                if (data.success) {
                    document.getElementById('searched-address').innerText = data.abhaAddress;
                    
                    const container = document.getElementById('auth-methods-container');
                    container.innerHTML = '';
                    
                    data.authMethods.forEach((method, idx) => {
                        const isChecked = idx === 0 ? 'checked' : '';
                        const title = method === 'AADHAAR_OTP' ? 'Aadhaar-Linked Mobile OTP' : 'ABHA-Linked Mobile OTP';
                        const desc = method === 'AADHAAR_OTP' ? 'Verification OTP sent to Aadhaar-registered mobile number.' : 'Verification OTP sent to ABHA account-registered mobile number.';
                        const icon = method === 'AADHAAR_OTP' ? 'fa-fingerprint' : 'fa-mobile-screen-button';
                        
                        container.innerHTML += `
                            <label class="method-option">
                                <input type="radio" name="auth_method" value="${method}" ${isChecked} required>
                                <div style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; background: var(--surface); color: var(--primary-light); font-size: 16px; flex-shrink: 0;">
                                    <i class="fa-solid ${icon}"></i>
                                </div>
                                <div class="method-details">
                                    <div class="method-title">${title}</div>
                                    <div class="method-desc">${desc}</div>
                                </div>
                            </label>
                        `;
                    });
                    
                    panelSearch.classList.remove('active');
                    panelMethod.classList.add('active');
                } else {
                    alert(data.message || "Search failed.");
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Search Address <i class="fa-solid fa-search"></i>';
                alert("Search failed. Connection error.");
            });
        });

        formRequestOtp.addEventListener('submit', function(e) {
            e.preventDefault();
            const selectedMethod = document.querySelector('input[name="auth_method"]:checked').value;
            const btn = document.getElementById('btn-request-otp');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Requesting OTP...';
            
            fetch("{{ route('abha.verify.request-otp') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ auth_method: selectedMethod })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Send OTP <i class="fa-solid fa-paper-plane"></i>';
                
                if (data.success) {
                    if(data.message) {
                        document.getElementById('verify-hint-text').innerText = data.message;
                    }
                    panelMethod.classList.remove('active');
                    panelOtp.classList.add('active');
                } else {
                    alert(data.message || "OTP request failed.");
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Send OTP <i class="fa-solid fa-paper-plane"></i>';
                alert("OTP Request failed. Connection error.");
            });
        });

        formVerify.addEventListener('submit', function(e) {
            e.preventDefault();
            const otpVal = otpInput.value.trim();
            const btn = document.getElementById('btn-verify');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Verifying...';
            
            fetch("{{ route('abha.verify.confirm') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ otp: otpVal })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Verify & Load Profile <i class="fa-solid fa-circle-check"></i>';
                
                if (data.success) {
                    showVerifiedCard(data.profile);
                } else {
                    alert(data.message || "Verification failed.");
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Verify & Load Profile <i class="fa-solid fa-circle-check"></i>';
                alert("Verification failed. Connection error.");
            });
        });

        // ════════════════════════════════════════
        // TAB 2: QR CODE VERIFICATION JS
        // ════════════════════════════════════════
        const qrFileInput = document.getElementById('qr-file-input');
        const qrUploadItem = document.getElementById('qr-upload-item');
        const formQrVerify = document.getElementById('form-qr-verify');
        const btnQrSubmit = document.getElementById('btn-qr-submit');

        qrFileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const fileName = e.target.files[0].name;
                document.getElementById('qr-icon-status').className = 'fa-solid fa-circle-check doc-icon';
                document.getElementById('qr-icon-status').style.color = 'var(--success-light)';
                document.getElementById('qr-title-status').innerText = "Loaded: " + fileName;
                qrUploadItem.style.borderColor = 'var(--success)';
                btnQrSubmit.disabled = false;
            }
        });

        formQrVerify.addEventListener('submit', function(e) {
            e.preventDefault();
            btnQrSubmit.disabled = true;
            btnQrSubmit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Decoding QR...';

            fetch("{{ route('abha.verify.qr') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ qr_data: "scanned-raw-abha-token-code- Uttarakhand-HIMS-Verified-QR" })
            })
            .then(res => res.json())
            .then(data => {
                btnQrSubmit.disabled = false;
                btnQrSubmit.innerHTML = 'Scan & Verify QR <i class="fa-solid fa-bolt"></i>';

                if (data.success) {
                    // Hide tabs & tab content, display success card
                    document.getElementById('tab-qr').classList.remove('active');
                    showVerifiedCard(data.profile);
                } else {
                    alert(data.message || "QR Code verification failed.");
                }
            })
            .catch(err => {
                btnQrSubmit.disabled = false;
                btnQrSubmit.innerHTML = 'Scan & Verify QR <i class="fa-solid fa-bolt"></i>';
                alert("QR Verification failed. Connection error.");
            });
        });

        // ════════════════════════════════════════
        // TAB 3: DEMOGRAPHICS VERIFICATION JS
        // ════════════════════════════════════════
        const formDemo = document.getElementById('form-demographics-verify');
        const btnDemoSubmit = document.getElementById('btn-demo-submit');

        formDemo.addEventListener('submit', function(e) {
            e.preventDefault();
            btnDemoSubmit.disabled = true;
            btnDemoSubmit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Authenticating...';

            const payload = {
                name: document.getElementById('demo-name').value.trim(),
                gender: document.getElementById('demo-gender').value,
                yob: document.getElementById('demo-yob').value.trim(),
                mobile: document.getElementById('demo-mobile').value.trim()
            };

            fetch("{{ route('abha.verify.demographics') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btnDemoSubmit.disabled = false;
                btnDemoSubmit.innerHTML = 'Verify Demographic Match <i class="fa-solid fa-user-check"></i>';

                if (data.success) {
                    document.getElementById('tab-demographics').classList.remove('active');
                    showVerifiedCard(data.profile);
                } else {
                    alert(data.message || "Demographics matching failed.");
                }
            })
            .catch(err => {
                btnDemoSubmit.disabled = false;
                btnDemoSubmit.innerHTML = 'Verify Demographic Match <i class="fa-solid fa-user-check"></i>';
                alert("Demographics Match failed. Connection error.");
            });
        });

        // Helper to render verified card layout
        function showVerifiedCard(p) {
            // Hide active tab content
            tabContents.forEach(c => c.classList.remove('active'));

            document.getElementById('card-name-val').innerText = p.name;
            document.getElementById('card-address-val').innerText = p.abhaAddress;
            document.getElementById('card-number-val').innerText = p.abhaNumber || "N/A";
            document.getElementById('card-status-val').innerText = p.status || "ACTIVE";
            document.getElementById('card-gender-val').innerText = p.gender;
            document.getElementById('card-dob-val').innerText = p.dob;
            document.getElementById('card-mobile-val').innerText = p.mobile || "N/A";
            document.getElementById('card-token-val').innerText = (p.linkingToken || "RETRIEVED").substring(0, 8) + '...';
            
            if (p.photo) {
                document.getElementById('card-photo').innerHTML = `<img src="data:image/jpeg;base64,${p.photo}" alt="profile">`;
            } else {
                document.getElementById('card-photo').innerHTML = `<i class="fa-solid fa-user"></i>`;
            }

            // Show success screen panel
            panelSuccess.classList.add('active');
        }

        document.getElementById('btn-download-card').addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Downloading...';

            fetch("{{ route('abha.card.download') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-download"></i> Download ABHA Card';
                
                if (data.success) {
                    const link = document.createElement('a');
                    link.href = 'data:image/png;base64,' + data.qr_code;
                    link.download = 'abha_card_qr.png';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert(data.message || "Failed to download card.");
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-download"></i> Download ABHA Card';
                alert("Download failed. Connection error.");
            });
        });
    </script>
</body>

</html>
