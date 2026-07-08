<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HIU Consent Management | Uttarakhand HIMS Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Noto+Sans+Devanagari:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">

    <style>
        :root {
            /* Branding Tokens matching hims.css */
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

        .content-body {
            padding: 30px;
            flex: 1;
        }

        /* Page Headers */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-subtitle {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 4px;
        }

        /* Stats Cards Row */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            overflow: hidden;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: var(--border2);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
        }

        .stat-card.blue::before { background: var(--primary); }
        .stat-card.green::before { background: var(--success); }
        .stat-card.orange::before { background: var(--warning); }
        .stat-card.red::before { background: var(--danger); }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat-card.blue .stat-icon { background: rgba(21, 101, 192, 0.15); color: var(--primary-light); }
        .stat-card.green .stat-icon { background: rgba(46, 125, 50, 0.15); color: var(--success-light); }
        .stat-card.orange .stat-icon { background: rgba(245, 124, 0, 0.15); color: var(--warning-light); }
        .stat-card.red .stat-icon { background: rgba(198, 40, 40, 0.15); color: var(--danger-light); }

        .stat-info .val {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
        }

        .stat-info .lbl {
            font-size: 11.5px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        /* Forms and Layout Panels */
        .layout-grid {
            display: grid;
            grid-template-columns: 1fr 1.8fr;
            gap: 30px;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            height: fit-content;
        }

        .panel-title {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--muted2);
            margin-bottom: 8px;
        }

        .req {
            color: var(--danger-light);
        }

        .form-control {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13.5px;
            color: #fff;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.2);
        }

        .form-control::placeholder {
            color: var(--muted);
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            background: var(--surface2);
            padding: 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: var(--text);
            cursor: pointer;
        }

        .checkbox-item input {
            cursor: pointer;
        }

        .btn-action {
            background: linear-gradient(135deg, var(--primary), #1e88e5);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(21, 101, 192, 0.3);
        }

        .btn-action:disabled {
            background: var(--border);
            color: var(--muted);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-color: var(--border2);
        }

        /* Tabs and Filtering */
        .tabs-header {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .tab-btn:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.03);
        }

        .tab-btn.active {
            color: var(--primary-light);
            background: rgba(21, 101, 192, 0.15);
        }

        /* Tables & Lists */
        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }

        .custom-table th {
            color: var(--muted);
            font-weight: 600;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border2);
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }

        .custom-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
        }

        .custom-table tr:hover td {
            background: rgba(255, 255, 255, 0.01);
        }

        /* Status Pills */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-pending { background: rgba(245, 124, 0, 0.15); color: var(--warning-light); }
        .badge-active { background: rgba(46, 125, 50, 0.15); color: var(--success-light); }
        .badge-revoked { background: rgba(198, 40, 40, 0.15); color: var(--danger-light); }
        .badge-expired { background: rgba(255, 255, 255, 0.05); color: var(--muted); }

        /* Simulator Widget styling */
        .sim-box {
            background: rgba(69, 39, 160, 0.08);
            border: 1.5px dashed var(--gold);
            border-radius: 10px;
            padding: 16px;
            margin-top: 20px;
        }

        .sim-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--gold-light);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sim-desc {
            font-size: 11.5px;
            color: var(--muted2);
            margin-bottom: 14px;
            line-height: 1.4;
        }

        .sim-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .btn-sim {
            background: linear-gradient(135deg, var(--saffron), var(--gold));
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-sim:hover {
            box-shadow: 0 3px 8px rgba(245, 124, 0, 0.3);
        }

        .btn-sim-purple {
            background: linear-gradient(135deg, var(--purple), #5e35b1);
        }

        .btn-sim-purple:hover {
            box-shadow: 0 3px 8px rgba(94, 53, 177, 0.3);
        }

        .hint-text {
            font-size: 11px;
            color: var(--muted);
            margin-top: 4px;
        }

        /* Toggle Switch styling */
        .switch-toggle {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            cursor: pointer;
        }

        .switch-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--surface2);
            transition: .4s;
            border-radius: 24px;
            border: 1px solid var(--border);
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: var(--muted);
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: var(--primary);
            border-color: var(--primary-light);
        }

        input:checked + .slider:before {
            transform: translateX(20px);
            background-color: #fff;
        }
    </style>
</head>

<body>
    <div class="app-shell">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-logo">
                <div class="logo-wrap">
                    <div class="logo-orb">
                        <i class="fa-solid fa-laptop-medical"></i>
                    </div>
                    <div class="logo-txt">
                        <div class="l1">ParaCare+ HIMS</div>
                        <div class="l2">Govt. of Uttarakhand</div>
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-grp-title">Clinical Portals</div>
                <a href="{{ route('hip.dashboard') }}" class="nav-item">
                    <i class="fa-solid fa-square-h"></i> HIP Dashboard
                </a>
                <a href="{{ route('hip.milestone2') }}" class="nav-item">
                    <i class="fa-solid fa-map-location-dot"></i> ABDM Milestone 2 Map
                </a>
                <a href="{{ route('hiu.dashboard') }}" class="nav-item active">
                    <i class="fa-solid fa-shield-halved"></i> HIU Portal
                </a>
                <a href="{{ route('hip.consents') }}" class="nav-item">
                    <i class="fa-solid fa-file-shield"></i> Consent & Security Hub
                </a>

                <div class="nav-grp-title">ABHA Services</div>
                <a href="{{ route('abha.dashboard') }}" class="nav-item">
                    <i class="fa-solid fa-address-card"></i> ABHA Dashboard
                </a>
                <a href="{{ route('abha.create') }}" class="nav-item">
                    <i class="fa-solid fa-user-plus"></i> Register ABHA
                </a>
                <a href="{{ route('abha.find') }}" class="nav-item">
                    <i class="fa-solid fa-search"></i> Search ABHA
                </a>
                <a href="{{ route('abha.verify') }}" class="nav-item">
                    <i class="fa-solid fa-circle-check"></i> Verify ABHA
                </a>

                <div class="nav-grp-title">NHPR Registry</div>
                <a href="{{ route('nhpr.register.wizard') }}" class="nav-item">
                    <i class="fa-solid fa-user-doctor"></i> HPR Onboarding
                </a>
                <a href="{{ route('nhpr.track.show') }}" class="nav-item">
                    <i class="fa-solid fa-binoculars"></i> Track Status
                </a>
            </div>
        </div>

        <!-- Main Window -->
        <div class="main">
            <!-- Goverment Topbar -->
            <div class="gov-topbar">
                <div class="gov-emblem">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/af/Emblem_of_Uttarakhand.svg" alt="Uttarakhand Emblem">
                    <div class="gov-title-text">
                        <span class="gov-hindi">उत्तराखंड शासन</span>
                        <span class="gov-english">Government of Uttarakhand</span>
                    </div>
                </div>

                <div class="gateway-status">
                    <span class="status-dot active"></span>
                    <span>ABDM SANDBOX GATEWAY: CONNECTED</span>
                </div>
            </div>

            <!-- Page Body -->
            <div class="content-body">
                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">
                            <i class="fa-solid fa-folder-medical" style="color: var(--primary-light);"></i>
                            Health Information User (HIU) Console
                        </h1>
                        <p class="page-subtitle">Initiate patient consent requests and request medical history securely under ABDM Milestone 3.</p>
                    </div>

                    @if ($realApiMode)
                        <span class="badge badge-active" style="background: rgba(21, 101, 192, 0.2); color: var(--primary-light);">
                            <i class="fa-solid fa-network-wired"></i> Live Sandbox mode
                        </span>
                    @else
                        <span class="badge badge-pending" style="background: rgba(245, 124, 0, 0.2); color: var(--warning-light);">
                            <i class="fa-solid fa-vial"></i> Simulated dev mode
                        </span>
                    @endif
                </div>

                <!-- API Toggle Toolbar -->
                <div style="background: var(--surface); border: 1px solid var(--border); padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 13px; font-weight: 700; color: #fff;"><i class="fa-solid fa-vial" style="color: var(--gold); margin-right: 6px;"></i>Offline Sandbox Simulation</span>
                        <p style="font-size: 11.5px; color: var(--muted); margin: 0;">Bypass live gateway integrations to perform fast local tests.</p>
                    </div>
                    <div>
                        <label class="switch-toggle">
                            <input type="checkbox" id="mode-toggle-checkbox" {{ !$realApiMode ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Stats Summary Row -->
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-icon"><i class="fa-solid fa-file-signature"></i></div>
                        <div class="stat-info">
                            <div class="val">{{ $stats['total'] }}</div>
                            <div class="lbl">Consent Requests</div>
                        </div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                        <div class="stat-info">
                            <div class="val">{{ $stats['active'] }}</div>
                            <div class="lbl">Active Granted</div>
                        </div>
                    </div>
                    <div class="stat-card orange">
                        <div class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
                        <div class="stat-info">
                            <div class="val">{{ $stats['revoked'] }}</div>
                            <div class="lbl">Revoked Policies</div>
                        </div>
                    </div>
                    <div class="stat-card red">
                        <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
                        <div class="stat-info">
                            <div class="val">{{ $stats['expired'] }}</div>
                            <div class="lbl">Expired Policies</div>
                        </div>
                    </div>
                </div>

                <!-- Main Layout Grid -->
                <div class="layout-grid">
                    <!-- Left Column: Create Request & Simulation -->
                    <div>
                        <!-- Consent Request Form -->
                        <div class="panel" style="margin-bottom: 20px;">
                            <h2 class="panel-title">
                                <i class="fa-solid fa-plus-circle" style="color: var(--primary-light);"></i>
                                Create Consent Request
                            </h2>
                            <form id="consent-request-form">
                                <div class="form-group">
                                    <label class="form-label" for="patient_address">Patient ABHA Address <span class="req">*</span></label>
                                    <input type="text" id="patient_address" class="form-control" placeholder="username@sbx" required>
                                    <div class="hint-text">Enter the patient's unique ABHA ID.</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="purpose">Purpose of Access <span class="req">*</span></label>
                                    <select id="purpose" class="form-control" required>
                                        <option value="General Consultation">General Consultation</option>
                                        <option value="Referral Clinic Consultation">Referral Consultation</option>
                                        <option value="Emergency Treatment Care">Emergency Treatment</option>
                                        <option value="Chronic Condition Monitoring">Chronic Care Monitoring</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Requested Data Types <span class="req">*</span></label>
                                    <div class="checkbox-group">
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="hi_types" value="Prescription" checked>
                                            Prescriptions
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="hi_types" value="DiagnosticReport" checked>
                                            Lab Reports
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="hi_types" value="DischargeSummary" checked>
                                            Discharge Summaries
                                        </label>
                                        <label class="checkbox-item">
                                            <input type="checkbox" name="hi_types" value="OPDDoc" checked>
                                            OPD Documents
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                    <div>
                                        <label class="form-label" for="date_from">Record Date From <span class="req">*</span></label>
                                        <input type="date" id="date_from" class="form-control" value="{{ date('Y-m-d', strtotime('-1 year')) }}" required>
                                    </div>
                                    <div>
                                        <label class="form-label" for="date_to">Record Date To <span class="req">*</span></label>
                                        <input type="date" id="date_to" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label" for="expiry">Consent Expiry Date <span class="req">*</span></label>
                                    <input type="date" id="expiry" class="form-control" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required>
                                    <div class="hint-text">Date until which HIMS can access this data.</div>
                                </div>

                                <button type="submit" class="btn-action" style="width: 100%; margin-top: 10px;" id="btn-submit-request">
                                    <i class="fa-solid fa-paper-plane"></i> Dispatch Request
                                </button>
                            </form>
                        </div>

                        <!-- Simulator Control Hub -->
                        @if (!$realApiMode)
                        <div class="sim-box">
                            <h3 class="sim-title">
                                <i class="fa-solid fa-vial-virus"></i> ABDM Simulation Hub
                            </h3>
                            <p class="sim-desc">Use this controller to simulate patient interactions and HIP transfers step-by-step in your local development stack.</p>
                            
                            <div class="sim-actions">
                                <div style="margin-bottom: 8px;">
                                    <span style="font-size: 11px; font-weight: 700; color: var(--gold); display: block; margin-bottom: 4px;">Step 1: Patient Approval</span>
                                    <button class="btn-sim" id="btn-sim-approve" disabled>
                                        <i class="fa-solid fa-thumbs-up"></i> Approve Pending Request
                                    </button>
                                </div>

                                <div>
                                    <span style="font-size: 11px; font-weight: 700; color: var(--purple-light); display: block; margin-bottom: 4px;">Step 2: Exchange Data Pushes</span>
                                    <button class="btn-sim btn-sim-purple" id="btn-sim-push" disabled>
                                        <i class="fa-solid fa-exchange-alt"></i> Push Encrypted Data Bundle
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Right Column: Consent Registries -->
                    <div class="panel">
                        <div class="tabs-header">
                            <button class="tab-btn active" onclick="switchTab('requested')">Pending Requests</button>
                            <button class="tab-btn" onclick="switchTab('granted')">Granted Policies</button>
                            <button class="tab-btn" onclick="switchTab('revoked')">Revoked/Expired</button>
                        </div>

                        <!-- Pending Requests Tab -->
                        <div id="tab-requested" class="tab-content">
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Req Ref</th>
                                            <th>ABHA Address</th>
                                            <th>Purpose</th>
                                            <th>Expiry</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($requests->whereIn('status', ['REQUESTED', 'INITIATED']) as $req)
                                            <tr data-req-id="{{ $req->consent_request_id }}">
                                                <td style="font-family: monospace; font-weight: 700;">{{ $req->consent_request_id ?? 'Pending ID' }}</td>
                                                <td>{{ $req->patient_abha_address }}</td>
                                                <td>{{ $req->purpose }}</td>
                                                <td>{{ $req->expiry->format('d M Y') }}</td>
                                                <td>
                                                    <span class="badge badge-pending">{{ $req->status }}</span>
                                                </td>
                                                <td>
                                                    @if (!$realApiMode && $req->consent_request_id)
                                                        <div style="display: flex; gap: 4px;">
                                                            <button class="btn-secondary" style="padding: 4px 8px; font-size: 11px;" onclick="selectRequestForApproval('{{ $req->consent_request_id }}')">
                                                                <i class="fa-solid fa-circle-check"></i> Select
                                                            </button>
                                                            <button class="btn-secondary" style="padding: 4px 8px; font-size: 11px; background: rgba(244, 67, 54, 0.15); color: #ef5350; border-color: rgba(244, 67, 54, 0.3);" onclick="simulateDenyConsent('{{ $req->consent_request_id }}')">
                                                                <i class="fa-solid fa-circle-xmark"></i> Reject
                                                            </button>
                                                        </div>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" style="text-align: center; color: var(--muted); padding: 30px;">
                                                    No pending consent requests found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Granted Policies Tab -->
                        <div id="tab-granted" class="tab-content" style="display: none;">
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Consent ID</th>
                                            <th>Patient ABHA</th>
                                            <th>HI Types</th>
                                            <th>Status</th>
                                            <th>Records Status</th>
                                            <th>Clinical Record Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($artefacts->where('status', 'GRANTED') as $art)
                                            @php
                                                $txn = \App\Models\HiuTransaction::where('consent_id', $art->consent_id)->latest()->first();
                                                $hiTypes = $art->consent_detail['permission']['dataTypes'] ?? $art->consent_detail['notification']['consentDetail']['hiTypes'] ?? ['Prescription', 'DiagnosticReport'];
                                            @endphp
                                            <tr>
                                                <td style="font-family: monospace; font-weight: 700; color: var(--success-light);">{{ $art->consent_id }}</td>
                                                <td>{{ $art->patient_abha_address }}</td>
                                                <td>
                                                    @foreach($hiTypes as $type)
                                                        <span style="font-size: 10px; background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 4px; margin-right: 2px;">{{ $type }}</span>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <span class="badge badge-active">{{ $art->status }}</span>
                                                </td>
                                                <td>
                                                    @if($txn)
                                                        <span class="badge @if($txn->status == 'DELIVERED') badge-active @elseif($txn->status == 'FAILED') badge-revoked @else badge-pending @endif">{{ $txn->status }}</span>
                                                    @else
                                                        <span class="badge badge-expired">NOT REQUESTED</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div style="display: flex; gap: 6px;">
                                                        @if(!$txn)
                                                            <button class="btn-action" style="padding: 6px 12px; font-size: 11.5px;" onclick="requestHealthData('{{ $art->consent_id }}')">
                                                                <i class="fa-solid fa-download"></i> Fetch Records
                                                            </button>
                                                        @elseif($txn->status == 'REQUESTED' && !$realApiMode)
                                                            <button class="btn-secondary" style="padding: 6px 12px; font-size: 11.5px;" onclick="selectTransactionForPush('{{ $txn->transaction_id }}')">
                                                                <i class="fa-solid fa-flask"></i> Select Push
                                                            </button>
                                                        @elseif($txn->status == 'DELIVERED')
                                                            <a href="{{ route('hiu.records', $art->patient_abha_address) }}" class="btn-action" style="padding: 6px 12px; font-size: 11.5px; background: linear-gradient(135deg, var(--success), #43a047);">
                                                                <i class="fa-solid fa-user-doctor"></i> View Records
                                                            </a>
                                                        @else
                                                            <button class="btn-action" style="padding: 6px 12px; font-size: 11.5px;" onclick="requestHealthData('{{ $art->consent_id }}')">
                                                                <i class="fa-solid fa-redo"></i> Retry Fetch
                                                            </button>
                                                        @endif

                                                        <button class="btn-secondary" style="padding: 6px 12px; font-size: 11.5px; background: rgba(255, 255, 255, 0.05); color: var(--muted); border-color: var(--border);" onclick="revokeConsentLocal('{{ $art->consent_id }}')">
                                                            <i class="fa-solid fa-trash-can"></i> Revoke & Wipe
                                                        </button>

                                                        @if(!$realApiMode)
                                                            <button class="btn-secondary" style="padding: 6px 12px; font-size: 11.5px; background: rgba(244, 67, 54, 0.15); color: #ef5350; border-color: rgba(244, 67, 54, 0.3);" onclick="simulateRevokeConsent('{{ $art->consent_id }}')">
                                                                <i class="fa-solid fa-ban"></i> Sim Revoke
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" style="text-align: center; color: var(--muted); padding: 30px;">
                                                    No active granted consents found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Revoked/Expired Tab -->
                        <div id="tab-revoked" class="tab-content" style="display: none;">
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Consent ID</th>
                                            <th>Patient ABHA</th>
                                            <th>Status</th>
                                            <th>Last Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($artefacts->whereIn('status', ['REVOKED', 'EXPIRED']) as $art)
                                            <tr>
                                                <td style="font-family: monospace; font-weight: 700; color: var(--danger-light);">{{ $art->consent_id }}</td>
                                                <td>{{ $art->patient_abha_address }}</td>
                                                <td>
                                                    <span class="badge @if($art->status == 'REVOKED') badge-revoked @else badge-expired @endif">{{ $art->status }}</span>
                                                </td>
                                                <td>{{ $art->updated_at->format('d M Y H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" style="text-align: center; color: var(--muted); padding: 30px;">
                                                    No revoked or expired policies logged.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script Handling -->
    <script>
        let selectedConsentReqId = null;
        let selectedTransactionId = null;

        // Switch Active Tabs
        function switchTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
            
            event.target.classList.add('active');
            document.getElementById('tab-' + tabId).style.display = 'block';
        }

        // Register Consent Request Form
        const formRequest = document.getElementById('consent-request-form');
        formRequest.addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-request');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

            const checkboxes = document.querySelectorAll('input[name="hi_types"]:checked');
            const hiTypes = Array.from(checkboxes).map(cb => cb.value);

            if (hiTypes.length === 0) {
                alert("Please select at least one requested data type.");
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Dispatch Request';
                return;
            }

            const data = {
                patient_abha_address: document.getElementById('patient_address').value.trim(),
                purpose: document.getElementById('purpose').value,
                hi_types: hiTypes,
                date_from: document.getElementById('date_from').value,
                date_to: document.getElementById('date_to').value,
                expiry: document.getElementById('expiry').value
            };

            fetch("{{ route('hiu.consent.request') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert("Consent Request dispatched and registered successfully! ID: " + res.consent_request_id);
                    location.reload();
                } else {
                    alert("Error: " + res.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Dispatch Request';
                }
            })
            .catch(err => {
                console.error(err);
                alert("Server request failed. Verify logs.");
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Dispatch Request';
            });
        });

        // Fetch Health Data Request
        function requestHealthData(consentId) {
            if (!confirm("Are you sure you want to request health records for Consent ID " + consentId + "? This will generate key material, establish a secure transaction, and poll data from the HIP.")) {
                return;
            }

            fetch("{{ route('hiu.health-information.request') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ consent_id: consentId })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert("Records Request successfully submitted! Transaction ID: " + res.transaction_id);
                    location.reload();
                } else {
                    alert("Error: " + res.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Failed to submit request.");
            });
        }

        // Selection functions for Simulation
        function selectRequestForApproval(reqId) {
            selectedConsentReqId = reqId;
            document.getElementById('btn-sim-approve').disabled = false;
            document.getElementById('btn-sim-approve').innerHTML = '<i class="fa-solid fa-thumbs-up"></i> Approve: ' + reqId;
            
            // Highlight selected row
            document.querySelectorAll('tr').forEach(r => r.style.background = 'transparent');
            const row = document.querySelector(`tr[data-req-id="${reqId}"]`);
            if (row) {
                row.style.background = 'rgba(245, 124, 0, 0.08)';
            }
        }

        function selectTransactionForPush(txnId) {
            selectedTransactionId = txnId;
            document.getElementById('btn-sim-push').disabled = false;
            document.getElementById('btn-sim-push').innerHTML = '<i class="fa-solid fa-exchange-alt"></i> Push data for: ' + txnId.substring(0, 8) + '...';
        }

        // Simulators Actions Triggers
        @if (!$realApiMode)
        const btnApprove = document.getElementById('btn-sim-approve');
        btnApprove.addEventListener('click', function() {
            if (!selectedConsentReqId) return;
            btnApprove.disabled = true;
            btnApprove.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Notifying Approval...';

            fetch("{{ route('hiu.simulator.approve-consent') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ consent_request_id: selectedConsentReqId })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert("Consent Approved! Fetching artefact now.");
                    
                    // Fetch the artefact
                    fetch(`/hiu/consent/fetch/${res.consent_id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(r => r.json())
                    .then(r => {
                        if (r.success) {
                            alert("Consent Artefact cached successfully! Ready to request health records.");
                            location.reload();
                        } else {
                            alert("Artefact caching failed: " + r.message);
                            location.reload();
                        }
                    });
                } else {
                    alert("Approval failed: " + res.message);
                    location.reload();
                }
            })
            .catch(err => {
                console.error(err);
                alert("Simulated approval request failed.");
                location.reload();
            });
        });

        const btnPush = document.getElementById('btn-sim-push');
        btnPush.addEventListener('click', function() {
            if (!selectedTransactionId) return;
            btnPush.disabled = true;
            btnPush.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Pushing Encrypted Data...';

            fetch("{{ route('hiu.simulator.push-health-data') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ transaction_id: selectedTransactionId })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert("Data transferred, decrypted via Fidelius fallback, and parsed to Patient Timeline!");
                    location.reload();
                } else {
                    alert("Push failed: " + res.message);
                    location.reload();
                }
            })
            .catch(err => {
                console.error(err);
                alert("Push failed.");
                location.reload();
            });
        });
        @endif

        // Toggle Switch script
        document.getElementById('mode-toggle-checkbox').addEventListener('change', function() {
            const useSimulatedMode = this.checked;
            
            fetch("{{ route('nhpr.register.toggle-mode') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ real_api_mode: useSimulatedMode ? 0 : 1 })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Gateway mode switched successfully!");
                    window.location.reload();
                } else {
                    alert("Failed to switch gateway mode.");
                }
            })
            .catch(err => {
                alert("Connection failed. Check server status.");
            });
        });

        function simulateDenyConsent(reqId) {
            if (!confirm("Are you sure you want to simulate patient rejection for Request ID " + reqId + "?")) {
                return;
            }

            fetch("{{ route('hiu.simulator.deny-consent') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ consent_request_id: reqId })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert("Simulated rejection completed successfully!");
                    location.reload();
                } else {
                    alert("Simulation failed: " + res.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Failed to dispatch simulated rejection.");
            });
        }

        function simulateRevokeConsent(consentId) {
            if (!confirm("Are you sure you want to simulate patient revocation for Consent ID " + consentId + "? This will send a REVOKED notification from the Gateway.")) {
                return;
            }

            fetch("{{ route('hiu.simulator.revoke-consent') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ consent_id: consentId })
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert("Simulated revocation completed successfully!");
                    location.reload();
                } else {
                    alert("Simulation failed: " + res.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Failed to dispatch simulated revocation.");
            });
        }

        function revokeConsentLocal(consentId) {
            if (!confirm("WARNING: Are you sure you want to revoke this consent locally and WIPE all associated clinical records from this HIMS? This action cannot be undone.")) {
                return;
            }

            fetch(`/hiu/consent/revoke/${consentId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert("Consent policy revoked and all local patient files completely wiped!");
                    location.reload();
                } else {
                    alert("Revocation failed: " + res.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Failed to revoke consent locally.");
            });
        }
    </script>
</body>

</html>
