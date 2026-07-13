<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Healthcare Professional Registration | ABDM HPR Portal</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Noto+Sans+Devanagari:wght@400;500;600;700&display=swap"
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

        /* Wizard Stepper Layout */
        .stepper {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 32px;
            overflow-x: auto;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }

        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            padding-right: 12px;
        }

        .step-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 800;
            border: 2px solid var(--border2);
            color: var(--muted);
            background: var(--surface2);
            transition: all 0.3s ease;
        }

        .step-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            white-space: nowrap;
        }

        .step.done .step-circle {
            background: var(--success);
            border-color: var(--success);
            color: #fff;
        }

        .step.done .step-label {
            color: var(--success-light);
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, var(--primary), #1976d2);
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 0 14px rgba(21, 101, 192, 0.45);
        }

        .step.active .step-label {
            color: var(--primary-light);
        }

        .step-connector {
            width: 25px;
            height: 2px;
            background: var(--border2);
            margin: 0 8px;
            flex-shrink: 0;
        }

        .step.done+.step-connector {
            background: var(--success);
        }

        /* Panel Container */
        .form-panel {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .form-panel.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .panel-header {
            margin-bottom: 24px;
        }

        .panel-title {
            font-size: 16px;
            font-weight: 800;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-sub {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 4px;
        }

        /* Cards */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            margin-bottom: 24px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-body {
            padding: 24px;
        }

        /* Form grids & Layouts */
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

        .grid-4 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {

            .grid-2,
            .grid-3,
            .grid-4 {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 18px;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted2);
        }

        .form-group label .req {
            color: var(--danger-light);
            margin-left: 2px;
        }

        .form-control {
            background: var(--surface2);
            border: 1.5px solid var(--border2);
            color: var(--text);
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13.5px;
            outline: none;
            transition: all 0.2s ease;
            width: 100%;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.2);
        }

        .form-control.error {
            border-color: var(--danger-light);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.15);
        }

        .form-error {
            font-size: 11.5px;
            color: var(--danger-light);
            margin-top: 4px;
            display: none;
        }

        .form-hint {
            font-size: 11px;
            color: var(--muted);
            margin-top: 3px;
        }

        /* Buttons styling */
        .btn-row {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 14px;
            margin-top: 10px;
        }

        .btn {
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
            border: 1px solid var(--border2);
            background: rgba(255, 255, 255, 0.05);
            color: var(--muted2);
            text-decoration: none;
        }

        .btn:hover:not(:disabled) {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn.primary {
            background: linear-gradient(135deg, var(--primary), #1976d2);
            color: #fff;
            border: none;
            box-shadow: 0 0 14px rgba(21, 101, 192, 0.3);
        }

        .btn.primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #1976d2, #1e88e5);
            box-shadow: 0 0 18px rgba(21, 101, 192, 0.55);
        }

        .btn.saffron {
            background: linear-gradient(135deg, var(--saffron), #ef6c00);
            color: #fff;
            border: none;
            box-shadow: 0 0 14px rgba(230, 81, 0, 0.3);
        }

        .btn.saffron:hover:not(:disabled) {
            background: linear-gradient(135deg, #ef6c00, #f57c00);
            box-shadow: 0 0 18px rgba(230, 81, 0, 0.55);
        }

        /* Suggestions chips */
        .suggestions-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .suggest-chip {
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--muted2);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .suggest-chip:hover {
            border-color: var(--primary);
            color: #fff;
            background: rgba(21, 101, 192, 0.15);
        }

        /* Document upload dropzone card */
        .doc-upload-item {
            border: 1.5px dashed var(--border2);
            background: var(--surface2);
            border-radius: 12px;
            padding: 24px;
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
            border-color: var(--primary);
            background: rgba(21, 101, 192, 0.03);
        }

        .doc-upload-item.has-file {
            border-style: solid;
            border-color: var(--success);
            background: rgba(46, 125, 50, 0.03);
        }

        .doc-icon {
            font-size: 28px;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .doc-upload-item.has-file .doc-icon {
            color: var(--success-light);
        }

        .doc-name {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
        }

        .doc-meta {
            font-size: 11px;
            color: var(--muted);
        }

        .file-input-raw {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        /* Profile card summary details */
        .profile-summary-card {
            display: flex;
            gap: 24px;
            align-items: flex-start;
        }

        .profile-summary-photo {
            width: 110px;
            height: 130px;
            border-radius: 8px;
            border: 1px solid var(--border2);
            background: var(--surface2);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .profile-summary-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-summary-photo i {
            font-size: 40px;
            color: var(--muted);
        }

        .profile-summary-details {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .detail-row {
            display: flex;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            padding-bottom: 6px;
        }

        .detail-label {
            width: 140px;
            font-size: 12px;
            color: var(--muted);
            font-weight: 600;
        }

        .detail-val {
            font-size: 12.5px;
            color: #fff;
            font-weight: 500;
        }

        /* Custom notifications toast list */
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 320px;
        }

        .toast {
            background: #112240;
            border-left: 4px solid var(--primary);
            border-radius: 8px;
            color: #fff;
            padding: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideIn 0.3s forwards;
            font-size: 12.5px;
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

        .toast.success {
            border-color: var(--success);
        }

        .toast.error {
            border-color: var(--danger);
        }

        .toast.warning {
            border-color: var(--warning);
        }

        .toast-icon {
            font-size: 16px;
            margin-top: 1px;
        }

        .toast.success .toast-icon {
            color: var(--success-light);
        }

        .toast.error .toast-icon {
            color: var(--danger-light);
        }

        .toast.warning .toast-icon {
            color: var(--warning-light);
        }

        .toast-content {
            flex: 1;
        }

        .toast-close {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 14px;
        }

        .toast-close:hover {
            color: #fff;
        }

        /* Ministry selection grid input */
        .facility-list {
            margin-top: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-height: 250px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .facility-item {
            background: var(--surface2);
            border: 1px solid var(--border);
            padding: 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .facility-item:hover {
            border-color: var(--primary);
            background: rgba(21, 101, 192, 0.05);
        }

        .facility-info {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .fac-name {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
        }

        .fac-id {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--primary-light);
            letter-spacing: 0.5px;
        }

        .fac-address {
            font-size: 11px;
            color: var(--muted);
        }

        .facility-item .btn-link {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--primary-light);
        }

        /* Success tick animation screen */
        .success-animation {
            text-align: center;
            padding: 40px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .tick-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: rgba(46, 125, 50, 0.15);
            border: 2px solid var(--success);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: var(--success-light);
            animation: bounceIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0.3);
                opacity: 0;
            }

            50% {
                transform: scale(1.05);
            }

            70% {
                transform: scale(0.9);
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-title {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
        }

        .success-text {
            font-size: 13px;
            color: var(--muted);
            max-width: 420px;
            line-height: 1.6;
        }

        .result-box {
            background: var(--surface2);
            border: 1px solid var(--border);
            padding: 16px;
            border-radius: 8px;
            width: 100%;
            max-width: 420px;
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
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
                <a href="{{ route('nhpr.register.wizard') }}" class="nav-item active"><i
                        class="fa-solid fa-user-doctor"></i> HPR Onboarding</a>
                <a href="{{ route('nhpr.hfr.index') }}" class="nav-item"><i class="fa-solid fa-building-circle-check"></i> HFR Management</a>
                <a href="{{ route('nhpr.token.show') }}" class="nav-item"><i class="fa-solid fa-key"></i> Gateway
                    Token</a>
                <a href="{{ route('nhpr.track.show') }}" class="nav-item"><i class="fa-solid fa-binoculars"></i> Track
                    Status</a>
            </div>
        </div>

        <!-- Main Dashboard Section -->
        <div class="main">

            <!-- Sticky Uttarakhand Ribbon Header -->
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
                    <span class="status-dot active"></span>
                    <span>ABDM Sandbox Mode</span>
                </div>
            </div>

            <!-- Content Body -->
            <div class="content">

                <div class="page-header">
                    <h1 class="page-title">Healthcare Professional Onboarding</h1>
                    <p class="page-subtitle">Register and create your national HPR ID under ABDM Healthcare Professional
                        Registry.</p>
                </div>

                <!-- API Mode Control Toolbar -->
                <div
                    style="background: var(--surface); border: 1px solid var(--border); padding: 12px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 12.5px; font-weight: 700; color: #fff;">ABDM Gateway Live Mode</span>
                        <label class="switch-toggle"
                            style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                            <input type="checkbox" id="live-mode-switch" style="opacity: 0; width: 0; height: 0;" {{ $config['realApiMode'] ? 'checked' : '' }}>
                            <span class="slider"
                                style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-color: #1a2847; border: 1px solid var(--border2); transition: .3s; border-radius: 24px;"></span>
                        </label>
                    </div>
                    <div id="credentials-config-btn-wrap"
                        style="display: {{ $config['realApiMode'] ? 'block' : 'none' }};">
                        <a href="{{ route('nhpr.token.show') }}" class="btn"
                            style="padding: 6px 12px; font-size: 11px; background: rgba(249, 168, 37, 0.1); border-color: rgba(249, 168, 37, 0.4); color: var(--gold);"><i
                                class="fa-solid fa-gear"></i> Configure API Credentials</a>
                    </div>
                </div>

                <!-- Wizard Indicator Stepper -->
                <div class="stepper">
                    <div class="step active" id="step-1-indicator">
                        <div class="step-circle">1</div>
                        <div class="step-label">Aadhaar Auth</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step" id="step-2-indicator">
                        <div class="step-circle">2</div>
                        <div class="step-label">HPR ID Creation</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step" id="step-3-indicator">
                        <div class="step-circle">3</div>
                        <div class="step-label">Professional Link</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step" id="step-4-indicator">
                        <div class="step-circle">4</div>
                        <div class="step-label">Upload Docs</div>
                    </div>
                </div>

                <!-- STEP 1 PANEL: Aadhaar Link Authentication -->
                <div class="form-panel active" id="panel-1">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fa-solid fa-id-card"></i> Step 1: Aadhaar Authentication</h2>
                        <p class="panel-sub">Authenticate your identity securely using the official ABDM Aadhaar
                            authentication gateway.</p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-shield-halved"></i> ABDM Gateway
                                Redirect</span>
                        </div>
                        <div class="card-body">
                            <div id="aadhaar-redirect-instructions">
                                <p style="font-size: 13px; color: var(--muted); line-height: 1.6; margin-bottom: 16px;">
                                    To verify your credentials, you will be redirected to the secure government
                                    authentication portal. Please make sure popups are allowed in your browser.
                                </p>
                                <div
                                    style="background: rgba(245, 124, 0, 0.1); border: 1px solid rgba(245, 124, 0, 0.3); border-radius: 8px; padding: 14px; margin-bottom: 24px; display: flex; gap: 12px; align-items: flex-start;">
                                    <i class="fa-solid fa-circle-info"
                                        style="color: var(--warning-light); font-size: 16px; margin-top: 2px;"></i>
                                    <div style="font-size: 12px; color: var(--warning-light); line-height: 1.5;">
                                        <strong>Note:</strong> Once you complete verification on the redirected tab,
                                        this portal will automatically detect your authentication status and advance to
                                        the next step. Do not close this window.
                                    </div>
                                </div>
                            </div>

                            <!-- Polling status container -->
                            <div id="aadhaar-polling-indicator"
                                style="display: none; text-align: center; padding: 24px 0;">
                                <div style="margin-bottom: 16px;"><i class="fa-solid fa-spinner fa-spin"
                                        style="font-size: 36px; color: var(--primary-light);"></i></div>
                                <h4 style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 6px;">ABDM
                                    Authentication Pending</h4>
                                <p style="font-size: 12px; color: var(--muted);">Please complete verification in the
                                    opened tab. Checking status...</p>
                            </div>
                        </div>
                    </div>

                    <div class="btn-row" id="btn-step1-row">
                        <button class="btn primary" id="btn-step1-action">Launch ABDM Aadhaar Verification <i
                                class="fa-solid fa-arrow-up-right-from-square"></i></button>
                    </div>
                </div>

                <!-- STEP 2 PANEL: Username selection & HPR ID creation -->
                <div class="form-panel" id="panel-2">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fa-solid fa-user-plus"></i> Step 2: HPR Username & Credentials
                        </h2>
                        <p class="panel-sub">Select your desired healthcare professional alias username and set account
                            credentials.</p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-address-card"></i> Aadhaar Profile
                                Demographic details</span>
                        </div>
                        <div class="card-body">
                            <div class="profile-summary-card">
                                <div class="profile-summary-photo" id="profile-photo-container">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div class="profile-summary-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Full Name</span>
                                        <span class="detail-val" id="profile-name">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Gender</span>
                                        <span class="detail-val" id="profile-gender">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">Year of Birth</span>
                                        <span class="detail-val" id="profile-yob">-</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="detail-label">State Code</span>
                                        <span class="detail-val" id="profile-state">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Mobile Verification Card (required before HPR ID creation) --}}
                    <div class="card" id="mobile-verify-card" style="margin-top: 0;">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-mobile-screen-button"></i> Mobile Number
                                Verification</span>
                        </div>
                        <div class="card-body">
                            <p style="color: var(--muted); margin-bottom: 16px; font-size: 13px;">
                                ABDM requires mobile verification before creating your HPR ID. Enter the mobile number
                                linked to your Aadhaar.
                            </p>

                            <div class="grid-2" id="mobile-input-row">
                                <div class="form-group">
                                    <label for="mobile-number">Mobile Number <span class="req">*</span></label>
                                    <input type="tel" id="mobile-number" class="form-control" maxlength="10"
                                        placeholder="10-digit mobile number">
                                    <div class="form-error" id="mobile-error">Please enter a valid 10-digit mobile
                                        number.</div>
                                </div>
                                <div class="form-group" style="display: flex; align-items: flex-end;">
                                    <button class="btn primary" id="btn-verify-mobile" style="width: 100%;">
                                        <i class="fa-solid fa-shield-check"></i> Verify Mobile
                                    </button>
                                </div>
                            </div>

                            {{-- OTP block (shown only when demographic fails) --}}
                            <div id="mobile-otp-row" style="display: none; margin-top: 14px;">
                                <div class="grid-2">
                                    <div class="form-group">
                                        <label for="mobile-otp">OTP sent to your mobile <span
                                                class="req">*</span></label>
                                        <input type="text" id="mobile-otp" class="form-control" maxlength="6"
                                            placeholder="6-digit OTP">
                                        <div class="form-error" id="mobile-otp-error">Please enter the 6-digit OTP.
                                        </div>
                                    </div>
                                    <div class="form-group" style="display: flex; align-items: flex-end;">
                                        <button class="btn primary" id="btn-verify-mobile-otp" style="width: 100%;">
                                            <i class="fa-solid fa-key"></i> Submit OTP
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Success badge (shown after verification) --}}
                            <div id="mobile-verified-badge"
                                style="display: none; margin-top: 12px; padding: 10px 16px; background: rgba(72,199,142,0.12); border: 1px solid var(--success); border-radius: 8px; display: none; align-items: center; gap: 10px;">
                                <i class="fa-solid fa-circle-check" style="color: var(--success);"></i>
                                <span id="mobile-verified-text"
                                    style="font-size: 13px; font-weight: 600; color: var(--success);">Mobile verified
                                    successfully!</span>
                            </div>
                        </div>
                    </div>

                    <div class="card" style="margin-top: 14px;">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-user-lock"></i> HPR Account Settings</span>
                        </div>
                        <div class="card-body">
                            <div class="grid-2">
                                <div class="form-group">
                                    <label for="hpr-username">Desired HPR Username <span class="req">*</span></label>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <input type="text" id="hpr-username" class="form-control"
                                            placeholder="Alias Username">
                                        <span
                                            style="font-weight: 700; color: var(--primary-light); font-size: 13px;">@hpr.abdm</span>
                                    </div>
                                    <div class="suggestions-wrap" id="suggestions-container">
                                        <!-- Dynamic suggested usernames chips loaded here -->
                                    </div>
                                    <div class="form-error" id="username-error">Please choose a valid HPR username.
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="hpr-email">Email Address <span class="req">*</span></label>
                                    <input type="email" id="hpr-email" class="form-control"
                                        placeholder="doctor@example.com">
                                    <div class="form-error" id="email-error">Please enter a valid email address.</div>
                                </div>
                            </div>

                            <div class="grid-2" style="margin-top: 14px;">
                                <div class="form-group">
                                    <label for="hpr-password">Password <span class="req">*</span></label>
                                    <input type="password" id="hpr-password" class="form-control"
                                        placeholder="8+ characters">
                                    <span class="form-hint">At least 8 chars, 1 uppercase, 1 special char.</span>
                                    <div class="form-error" id="password-error">Invalid password constraints.</div>
                                </div>

                                <div class="form-group">
                                    <label for="hpr-password-confirm">Confirm Password <span
                                            class="req">*</span></label>
                                    <input type="password" id="hpr-password-confirm" class="form-control"
                                        placeholder="Repeat Password">
                                    <div class="form-error" id="password-confirm-error">Passwords do not match.</div>
                                </div>
                            </div>

                            <div class="grid-2" style="margin-top: 14px;">
                                <div class="form-group">
                                    <label for="hpr-category">Professional Type Category <span
                                            class="req">*</span></label>
                                    <select id="hpr-category" class="form-control">
                                        <option value="1">Doctor</option>
                                        <option value="2">Nurse</option>
                                        <option value="6">Pharmacist</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="hpr-subcategory">Subcategory Speciality <span
                                            class="req">*</span></label>
                                    <select id="hpr-subcategory" class="form-control">
                                        <!-- Loaded dynamically on category choice -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="btn-row">
                        <button class="btn primary" id="btn-step3-action" disabled
                            style="opacity:0.5; cursor: not-allowed;">
                            Create HPR ID Profile <i class="fa-solid fa-arrow-right"></i>
                        </button>
                        <span id="mobile-verify-hint" style="font-size: 12px; color: var(--muted); margin-left: 10px;">
                            <i class="fa-solid fa-triangle-exclamation"></i> Please verify your mobile number first.
                        </span>
                    </div>
                </div>

                <!-- STEP 3 PANEL: Professional Academic & Facility Association Details -->
                <div class="form-panel" id="panel-3">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fa-solid fa-graduation-cap"></i> Step 3: Professional
                            Credentials & Facility Linkage</h2>
                        <p class="panel-sub">Submit registration license credentials and map your associated healthcare
                            facility.</p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-building-columns"></i> Council Registration
                                Details</span>
                        </div>
                        <div class="card-body">
                            <div class="grid-3">
                                <div class="form-group">
                                    <label for="council-select">Medical/Nursing/Pharmacy Council <span
                                            class="req">*</span></label>
                                    <select id="council-select" class="form-control">
                                        <option value="41">Uttarakhand Medical Council</option>
                                        <option value="14">Uttarakhand Nurses and Midwives Council</option>
                                        <option value="7">Delhi Medical Council</option>
                                        <option value="50">Uttarakhand Pharmacy Council</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="registration-number">License / Registration Number <span
                                            class="req">*</span></label>
                                    <input type="text" id="registration-number" class="form-control"
                                        placeholder="REG-12345">
                                    <div class="form-error" id="reg-no-error">License number is required.</div>
                                </div>

                                <div class="form-group">
                                    <label for="registration-date">Registration Date <span class="req">*</span></label>
                                    <input type="date" id="registration-date" class="form-control">
                                    <div class="form-error" id="reg-date-error">Registration date is required.</div>
                                </div>
                            </div>

                            <div class="grid-2" id="doctor-license-validity-section"
                                style="margin-top: 14px; display: none;">
                                <div class="form-group">
                                    <label for="license-status-select">License Validity Status <span
                                            class="req">*</span></label>
                                    <select id="license-status-select" class="form-control">
                                        <option value="Permanent">Permanent</option>
                                        <option value="Renewable">Renewable</option>
                                    </select>
                                </div>

                                <div class="form-group" id="renewable-due-date-group" style="display: none;">
                                    <label for="renewable-due-date">Renewable Due Date <span
                                            class="req">*</span></label>
                                    <input type="date" id="renewable-due-date" class="form-control">
                                    <div class="form-error" id="renewable-due-date-error">Renewable due date is
                                        required.</div>
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 14px;">
                                <label>Upload Council Registration Certificate (.pdf, max 5MB) <span
                                        class="req">*</span></label>
                                <div class="doc-upload-item" id="upload-reg-cert-card">
                                    <i class="fa-solid fa-cloud-arrow-up doc-icon"></i>
                                    <span class="doc-name" id="reg-cert-name">Drag certificate PDF or click to
                                        browse</span>
                                    <span class="doc-meta">Supports PDF format, max 5MB</span>
                                    <input type="file" id="reg-cert-file" class="file-input-raw"
                                        accept="application/pdf">
                                </div>
                                <input type="hidden" id="reg-cert-base64">
                                <div class="form-error" id="reg-cert-error">Please upload your registration certificate.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-user-graduate"></i> Academic
                                Qualification</span>
                        </div>
                        <div class="card-body">
                            <div class="grid-3">
                                <div class="form-group">
                                    <label for="degree-select">Degree Title <span class="req">*</span></label>
                                    <select id="degree-select" class="form-control">
                                        <option value="4060">MBBS</option>
                                        <option value="4074">BDS</option>
                                        <option value="4079">BAMS</option>
                                        <option value="4082">BUMS</option>
                                        <option value="5522">BSC Nursing</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="degree-year">Year of Graduation <span class="req">*</span></label>
                                    <input type="text" id="degree-year" class="form-control" placeholder="2024"
                                        maxlength="4">
                                    <div class="form-error" id="degree-year-error">Graduation year required.</div>
                                </div>

                                <div class="form-group">
                                    <label for="degree-university">Awarding University <span
                                            class="req">*</span></label>
                                    <select id="degree-university" class="form-control">
                                        <option value="7010">HNB Garhwal University</option>
                                        <option value="10">Uttarakhand Technical University</option>
                                        <option value="1149">All India Institute of Medical Sciences (AIIMS)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group" style="margin-top: 14px;">
                                <label>Upload Degree Certificate (.pdf, max 5MB) <span class="req">*</span></label>
                                <div class="doc-upload-item" id="upload-degree-cert-card">
                                    <i class="fa-solid fa-cloud-arrow-up doc-icon"></i>
                                    <span class="doc-name" id="degree-cert-name">Drag degree certificate PDF or click to
                                        browse</span>
                                    <span class="doc-meta">Supports PDF format, max 5MB</span>
                                    <input type="file" id="degree-cert-file" class="file-input-raw"
                                        accept="application/pdf">
                                </div>
                                <input type="hidden" id="degree-cert-base64">
                                <div class="form-error" id="degree-cert-error">Please upload your degree certificate.
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-briefcase"></i> Work Settings</span>
                        </div>
                        <div class="card-body">
                            <div class="grid-2">
                                <div class="form-group">
                                    <label for="currently-working">Currently Practising? <span
                                            class="req">*</span></label>
                                    <select id="currently-working" class="form-control">
                                        <option value="1">Yes, Active Practising</option>
                                        <option value="0">No, Currently Inactive</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="work-status-select">Employment Type Status <span
                                            class="req">*</span></label>
                                    <select id="work-status-select" class="form-control">
                                        <option value="0">Private Sector</option>
                                        <option value="1">Government only</option>
                                        <option value="2">Both Government & Private</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid-2" id="govt-work-details-section" style="margin-top: 14px; display: none;">
                                <div class="form-group">
                                    <label for="govt-type-select">Government Level <span class="req">*</span></label>
                                    <select id="govt-type-select" class="form-control">
                                        <option value="State">State Government</option>
                                        <option value="Central">Central Government</option>
                                    </select>
                                </div>

                                <div class="form-group" id="central-ministry-group" style="display: none;">
                                    <label for="ministry-select">Associated Ministry <span class="req">*</span></label>
                                    <select id="ministry-select" class="form-control">
                                        <!-- Loaded dynamically via endpoint -->
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Facility Lookup container -->
                    <div class="card" id="facility-lookup-card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-magnifying-glass-location"></i> Facility
                                search (HFR)</span>
                        </div>
                        <div class="card-body">
                            <div class="grid-3">
                                <div class="form-group">
                                    <label for="facility-search-name">Hospital/Clinic Name</label>
                                    <input type="text" id="facility-search-name" class="form-control"
                                        placeholder="e.g. AIIMS Rishikesh">
                                </div>

                                <div class="form-group">
                                    <label for="facility-search-pincode">Facility Pincode</label>
                                    <input type="text" id="facility-search-pincode" class="form-control"
                                        placeholder="249201" maxlength="6">
                                </div>

                                <div class="form-group" style="justify-content: flex-end;">
                                    <button type="button" class="btn primary" id="btn-search-facility"
                                        style="height: 41px; width: 100%;"><i class="fa-solid fa-magnifying-glass"></i>
                                        Search Facilities</button>
                                </div>
                            </div>

                            <!-- Search results -->
                            <div class="facility-list" id="facility-results-container" style="display: none;">
                                <!-- Dynamic facility item rows loaded here -->
                            </div>

                            <!-- Selected facility confirmation -->
                            <div class="form-group" id="selected-facility-box"
                                style="display: none; margin-top: 24px; padding: 14px; background: rgba(46,125,50,0.05); border: 1px solid var(--success); border-radius: 8px;">
                                <span
                                    style="font-size: 11px; font-weight: 700; color: var(--success-light); text-transform: uppercase;">Selected
                                    Facility Mapped</span>
                                <div style="font-size: 14px; font-weight: 700; margin-top: 4px;" id="selected-fac-name">
                                    Apollo Hospital Dehradun</div>
                                <div style="font-size: 11px; color: var(--muted);" id="selected-fac-details">ID:
                                    IN2710000059 | Pincode: 765435</div>
                                <input type="hidden" id="selected-fac-id">
                            </div>
                        </div>
                    </div>

                    <div class="btn-row">
                        <button class="btn primary" id="btn-step5-action">Submit Professional Registry <i
                                class="fa-solid fa-arrow-right"></i></button>
                    </div>
                </div>

                <!-- STEP 4 PANEL: Document uploads checklist -->
                <div class="form-panel" id="panel-4">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fa-solid fa-cloud-arrow-up"></i> Step 4: Certificate Documents
                            Upload</h2>
                        <p class="panel-sub">Upload verified digital copies of required academic and identification
                            licenses.</p>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <span class="card-title"><i class="fa-solid fa-folder-open"></i> Upload Documents
                                Checklist</span>
                        </div>
                        <div class="card-body">
                            <div class="grid-2" id="document-check-list-grid">
                                <!-- Dynamic file drop items loaded here -->
                            </div>
                        </div>
                    </div>

                    <div class="btn-row">
                        <button class="btn primary" id="btn-step6-action">Complete Registry Onboarding <i
                                class="fa-solid fa-check-double"></i></button>
                    </div>
                </div>

                <!-- STEP 5 PANEL: Onboarding complete success dashboard -->
                <div class="form-panel" id="panel-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="success-animation">
                                <div class="tick-circle"><i class="fa-solid fa-check"></i></div>
                                <h2 class="success-title">Registration Completed Successfully</h2>
                                <p class="success-text">Congratulations! Your professional registration details have
                                    been verified and submitted successfully to the national ABDM registry database.</p>

                                <div class="result-box">
                                    <div
                                        style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 6px;">
                                        <span style="font-size: 11.5px; color: var(--muted);">HPR ID Username</span>
                                        <span style="font-weight: 700; color: #fff;"
                                            id="success-hpr-id">doctor1994@hpr.abdm</span>
                                    </div>
                                    <div
                                        style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 6px; margin-top: 6px;">
                                        <span style="font-size: 11.5px; color: var(--muted);">National HPR Number</span>
                                        <span style="font-weight: 700; color: var(--primary-light);"
                                            id="success-hpr-number">71-3563-6824-2283</span>
                                    </div>
                                    <div style="display: flex; justify-content: space-between; margin-top: 6px;">
                                        <span style="font-size: 11.5px; color: var(--muted);">Reference ID</span>
                                        <span style="font-weight: 600; color: var(--gold);"
                                            id="success-ref-id">8806aa4a-013b-xxxx</span>
                                    </div>
                                </div>

                                <div style="margin-top: 24px; display: flex; gap: 14px;">
                                    <a href="#" class="btn primary"><i class="fa-solid fa-download"></i> Download
                                        License Card</a>
                                    <a href="{{ route('nhpr.register.wizard', ['fresh' => 1]) }}" class="btn"><i
                                            class="fa-solid fa-arrow-rotate-right"></i> Register New Provider</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // CSRF Token setup
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Switch listener to toggle API mode
            const liveModeSwitch = document.getElementById('live-mode-switch');
            const configBtnWrap = document.getElementById('credentials-config-btn-wrap');

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
                            showToast(data.message);
                            if (isLive) {
                                configBtnWrap.style.display = 'block';
                            } else {
                                configBtnWrap.style.display = 'none';
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 800);
                        } else {
                            showToast('Failed to toggle API mode.', 'error');
                        }
                    })
                    .catch(err => {
                        showToast('API Toggle request failed.', 'error');
                    });
            });

            // Navigation Indicators
            const stepIndicators = {
                1: document.getElementById('step-1-indicator'),
                2: document.getElementById('step-2-indicator'),
                3: document.getElementById('step-3-indicator'),
                4: document.getElementById('step-4-indicator')
            };

            // Panels
            const panels = {
                1: document.getElementById('panel-1'),
                2: document.getElementById('panel-2'),
                3: document.getElementById('panel-3'),
                4: document.getElementById('panel-4'),
                5: document.getElementById('panel-5')
            };

            // Utility to show notification toast
            function showToast(message, type = 'success') {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;

                let iconClass = 'fa-circle-check';
                if (type === 'error') iconClass = 'fa-circle-exclamation';
                if (type === 'warning') iconClass = 'fa-triangle-exclamation';

                toast.innerHTML = `
                <i class="fa-solid ${iconClass} toast-icon"></i>
                <div class="toast-content">${message}</div>
                <button class="toast-close"><i class="fa-solid fa-xmark"></i></button>
            `;

                container.appendChild(toast);

                // Auto remove after 5 seconds
                const timer = setTimeout(() => {
                    toast.remove();
                }, 5000);

                toast.querySelector('.toast-close').addEventListener('click', function () {
                    clearTimeout(timer);
                    toast.remove();
                });
            }

            // Stepper Navigation controller
            function activateStep(stepNum) {
                // Deactivate all
                for (let i = 1; i <= 4; i++) {
                    if (stepIndicators[i]) stepIndicators[i].classList.remove('active', 'done');
                }
                for (let i = 1; i <= 5; i++) {
                    if (panels[i]) panels[i].classList.remove('active');
                }

                // Activate current + mark previous as done
                for (let i = 1; i < stepNum; i++) {
                    if (stepIndicators[i]) stepIndicators[i].classList.add('done');
                }
                if (stepIndicators[stepNum]) stepIndicators[stepNum].classList.add('active');
                if (panels[stepNum]) panels[stepNum].classList.add('active');
            }

            // Subcategory listings mapped on category change
            const doctorSubcategories = [
                { code: 1, name: 'Modern Medicine (Allopathy)' },
                { code: 2, name: 'Dentist' },
                { code: 3, name: 'Ayurveda' },
                { code: 4, name: 'Unani' },
                { code: 5, name: 'Siddha' },
                { code: 6, name: 'Homoeopathy' },
                { code: 89, name: 'Sowa-Rigpa' },
                { code: 220, name: 'Yoga and Naturopathy' }
            ];

            const nurseSubcategories = [
                { code: 7, name: 'Registered Auxiliary Nurse Midwife (RANM)' },
                { code: 8, name: 'Registered Nurse (RN)' },
                { code: 9, name: 'Registered Nurse and Registered Midwife (RN & RM)' },
                { code: 10, name: 'Registered Lady Health Visitor (RLHV)' }
            ];

            const pharmacistSubcategories = [
                { code: 33, name: 'Pharmacist' }
            ];

            const categorySelect = document.getElementById('hpr-category');
            const subcategorySelect = document.getElementById('hpr-subcategory');

            function loadSubcategories(catVal) {
                subcategorySelect.innerHTML = '';
                let list = [];
                if (catVal == 1) {
                    list = doctorSubcategories;
                } else if (catVal == 2) {
                    list = nurseSubcategories;
                } else if (catVal == 6) {
                    list = pharmacistSubcategories;
                }
                list.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.code;
                    opt.textContent = item.name;
                    subcategorySelect.appendChild(opt);
                });

                // Toggle Doctor License Validity section display based on category
                const licenseSection = document.getElementById('doctor-license-validity-section');
                const councilSelect = document.getElementById('council-select');
                if (catVal == 1) {
                    licenseSection.style.display = 'grid';
                    councilSelect.value = "41"; // Default to Medical Council
                } else {
                    licenseSection.style.display = 'none';
                    if (catVal == 2) {
                        councilSelect.value = "14"; // Default to Nursing Council
                    } else if (catVal == 6) {
                        councilSelect.value = "50"; // Default to Pharmacy Council
                    }
                }
            }

            categorySelect.addEventListener('change', function () {
                loadSubcategories(this.value);
            });
            loadSubcategories(1); // Default doctor options

            // Base64 file converter helper
            function handleFileAttachment(fileInputId, hiddenInputId, cardId, nameId) {
                const input = document.getElementById(fileInputId);
                const hidden = document.getElementById(hiddenInputId);
                const card = document.getElementById(cardId);
                const nameSpan = document.getElementById(nameId);

                input.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    if (file.size > 5 * 1024 * 1024) {
                        showToast('File size must not exceed 5MB.', 'error');
                        this.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (evt) {
                        // Extract base64 raw data block
                        const base64String = evt.target.result.split(',')[1];
                        hidden.value = base64String;

                        card.classList.add('has-file');
                        nameSpan.textContent = file.name + ' (' + (file.size / (1024 * 1024)).toFixed(2) + ' MB)';
                    };
                    reader.readAsDataURL(file);
                });
            }

            handleFileAttachment('reg-cert-file', 'reg-cert-base64', 'upload-reg-cert-card', 'reg-cert-name');
            handleFileAttachment('degree-cert-file', 'degree-cert-base64', 'upload-degree-cert-card', 'degree-cert-name');

            // Dynamic State Storage Variables
            let activeTxnId = '';
            let isFallbackMobileOtpSent = false;
            let verifiedAadhaarDemographics = {};
            let mobileVerified = false;

            // ==========================================
            // STEP 2: Mobile Verification Handlers
            // ==========================================
            function markMobileVerified(mobile) {
                mobileVerified = true;
                document.getElementById('mobile-input-row').style.display = 'none';
                document.getElementById('mobile-otp-row').style.display = 'none';
                const badge = document.getElementById('mobile-verified-badge');
                badge.style.display = 'flex';
                document.getElementById('mobile-verified-text').textContent = 'Mobile ' + mobile + ' verified successfully!';

                const btn = document.getElementById('btn-step3-action');
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
                document.getElementById('mobile-verify-hint').style.display = 'none';
            }

            document.getElementById('btn-verify-mobile').addEventListener('click', function () {
                const mobile = document.getElementById('mobile-number').value.trim();
                const mobileErr = document.getElementById('mobile-error');

                if (!/^\d{10}$/.test(mobile)) {
                    mobileErr.style.display = 'block';
                    return;
                }
                mobileErr.style.display = 'none';

                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying...';

                fetch('{{ route("nhpr.register.mobile.verify") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ mobile: mobile })
                })
                    .then(res => res.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-shield-check"></i> Verify Mobile';

                        if (data.success && data.verified) {
                            // Demographic match passed
                            markMobileVerified(mobile);
                            showToast('Mobile number verified via demographic check!');
                        } else if (data.success && !data.verified) {
                            // OTP fallback triggered
                            isFallbackMobileOtpSent = true;
                            document.getElementById('mobile-otp-row').style.display = 'block';
                            document.getElementById('mobile-number').disabled = true;
                            btn.disabled = true;
                            btn.style.opacity = '0.5';
                            showToast(data.message, 'warning');
                        } else {
                            showToast(data.message || 'Mobile verification failed.', 'error');
                        }
                    })
                    .catch(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-shield-check"></i> Verify Mobile';
                        showToast('Mobile verification service error.', 'error');
                    });
            });

            document.getElementById('btn-verify-mobile-otp').addEventListener('click', function () {
                const otp = document.getElementById('mobile-otp').value.trim();
                const otpErr = document.getElementById('mobile-otp-error');
                const mobile = document.getElementById('mobile-number').value.trim();

                if (!/^\d{6}$/.test(otp)) {
                    otpErr.style.display = 'block';
                    return;
                }
                otpErr.style.display = 'none';

                const btn = this;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting OTP...';

                fetch('{{ route("nhpr.register.mobile.verify-otp") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ otp: otp })
                })
                    .then(res => res.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-key"></i> Submit OTP';

                        if (data.success) {
                            markMobileVerified(mobile);
                            showToast('Mobile OTP verified successfully!');
                        } else {
                            showToast(data.message || 'Invalid OTP. Please try again.', 'error');
                        }
                    })
                    .catch(() => {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-key"></i> Submit OTP';
                        showToast('OTP verification service error.', 'error');
                    });
            });

            // ==========================================
            // STEP 1 ACTION: Aadhaar Link Redirect & Polling
            // ==========================================
            const btnStep1 = document.getElementById('btn-step1-action');
            const instructionsDiv = document.getElementById('aadhaar-redirect-instructions');
            const pollingDiv = document.getElementById('aadhaar-polling-indicator');
            const btnStep1Row = document.getElementById('btn-step1-row');
            let pollingInterval = null;

            btnStep1.addEventListener('click', function () {
                btnStep1.disabled = true;
                btnStep1.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating ABDM Link...';

                fetch('{{ route("nhpr.register.aadhaar.generate-link") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            activeTxnId = data.txnId;

                            // Open gateway authentication link in popup/new tab
                            window.open(data.url, '_blank');

                            // Show polling state
                            instructionsDiv.style.display = 'none';
                            pollingDiv.style.display = 'block';
                            btnStep1Row.style.display = 'none';

                            showToast('ABDM verification page launched in a new tab.');

                            // Start polling auth status
                            startPollingStatus();
                        } else {
                            btnStep1.disabled = false;
                            btnStep1.innerHTML = 'Launch ABDM Aadhaar Verification <i class="fa-solid fa-arrow-up-right-from-square"></i>';
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(err => {
                        btnStep1.disabled = false;
                        btnStep1.innerHTML = 'Launch ABDM Aadhaar Verification <i class="fa-solid fa-arrow-up-right-from-square"></i>';
                        showToast('Failed to generate verification link. Try again.', 'error');
                    });
            });

            function startPollingStatus() {
                if (pollingInterval) clearInterval(pollingInterval);

                pollingInterval = setInterval(function () {
                    fetch('{{ route("nhpr.register.aadhaar.check-status") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.authenticated) {
                                clearInterval(pollingInterval);

                                if (data.isExistingUser) {
                                    showToast(data.message, 'warning');
                                    panels[1].innerHTML = `
                                    <div class="card" style="border-color: var(--warning);">
                                        <div class="card-body text-center" style="padding: 40px; text-align: center;">
                                            <i class="fa-solid fa-circle-exclamation" style="font-size: 48px; color: var(--gold); margin-bottom: 16px;"></i>
                                            <h3 style="margin-bottom: 8px;">Existing HPR Record Found</h3>
                                            <p style="color: var(--muted); margin-bottom: 24px;">An active HPR profile is already associated with this Aadhaar number.</p>
                                            
                                            <div style="background: var(--surface2); padding: 16px; border-radius: 8px; max-width: 400px; margin: 0 auto; display: flex; flex-direction: column; gap: 8px; text-align: left;">
                                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--muted);">Full Name</span><span style="font-weight: 700;">${data.profile.name}</span></div>
                                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--muted);">HPR ID Number</span><span style="font-weight: 700; color: var(--primary-light);">${data.profile.hprIdNumber}</span></div>
                                                <div style="display: flex; justify-content: space-between;"><span style="color: var(--muted);">Gender</span><span style="font-weight: 700;">${data.profile.gender}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                } else {
                                    showToast(data.message);
                                    loadUsernameSuggestionsAndProceed();
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Error checking verification status:', err);
                        });
                }, 2000);
            }

            // Trigger suggestions loading and transition to Step 2 (ID Creation)
            function loadUsernameSuggestionsAndProceed() {
                // Since demographic data is saved inside Session, we will pull suggested usernames and demographics
                fetch('{{ route("nhpr.register.suggestions") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const container = document.getElementById('suggestions-container');
                            container.innerHTML = '';
                            data.suggestions.forEach(suggest => {
                                const chip = document.createElement('span');
                                chip.className = 'suggest-chip';
                                chip.textContent = suggest;
                                chip.addEventListener('click', function () {
                                    document.getElementById('hpr-username').value = suggest;
                                });
                                container.appendChild(chip);
                            });

                            // Populate demographic card fields
                            document.getElementById('profile-name').textContent = (data.demographics && data.demographics.name) ? data.demographics.name : '-';
                            document.getElementById('profile-gender').textContent = (data.demographics && data.demographics.gender) ? data.demographics.gender : '-';
                            document.getElementById('profile-yob').textContent = (data.demographics && data.demographics.yearOfBirth) ? data.demographics.yearOfBirth : '-';
                            document.getElementById('profile-state').textContent = (data.demographics && data.demographics.stateCode) ? data.demographics.stateCode : '-';

                            if (data.demographics && data.demographics.profilePhoto) {
                                const photoContainer = document.getElementById('profile-photo-container');
                                photoContainer.innerHTML = `<img src="data:image/jpeg;base64,${data.demographics.profilePhoto}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                            }

                            // Proceed to Step 2 (HPR ID Creation)
                            activateStep(2);
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Error loading suggestions:', err);
                        showToast('Failed to load profile suggestions.', 'error');
                    });
            }

            // ==========================================
            // STEP 3 ACTION: HPR ID creation
            // ==========================================
            const btnStep3 = document.getElementById('btn-step3-action');
            btnStep3.addEventListener('click', function () {
                const username = document.getElementById('hpr-username').value.trim();
                const email = document.getElementById('hpr-email').value.trim();
                const password = document.getElementById('hpr-password').value;
                const passwordConfirm = document.getElementById('hpr-password-confirm').value;
                const category = document.getElementById('hpr-category').value;
                const subcategory = document.getElementById('hpr-subcategory').value;

                // Form Validations
                let valid = true;
                if (!username) { document.getElementById('username-error').style.display = 'block'; valid = false; }
                else { document.getElementById('username-error').style.display = 'none'; }

                if (!email || !email.includes('@')) { document.getElementById('email-error').style.display = 'block'; valid = false; }
                else { document.getElementById('email-error').style.display = 'none'; }

                if (password.length < 8) { document.getElementById('password-error').style.display = 'block'; valid = false; }
                else { document.getElementById('password-error').style.display = 'none'; }

                if (password !== passwordConfirm) { document.getElementById('password-confirm-error').style.display = 'block'; valid = false; }
                else { document.getElementById('password-confirm-error').style.display = 'none'; }

                if (!valid) return;

                btnStep3.disabled = true;
                btnStep3.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Securing HPR ID...';

                fetch('{{ route("nhpr.register.create-id") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        username: username,
                        email: email,
                        password: password,
                        category: category,
                        subcategory: subcategory
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        btnStep3.disabled = false;
                        btnStep3.innerHTML = 'Create HPR ID Profile <i class="fa-solid fa-arrow-right"></i>';

                        if (data.success) {
                            showToast(data.message);
                            // Match council selector on Category Choice
                            const councilSelect = document.getElementById('council-select');
                            if (category == 2) {
                                councilSelect.value = "14"; // Default to Nursing Council
                            } else {
                                councilSelect.value = "41"; // Default to Medical Council
                            }
                            activateStep(3);
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(err => {
                        btnStep3.disabled = false;
                        btnStep3.innerHTML = 'Create HPR ID Profile <i class="fa-solid fa-arrow-right"></i>';
                        showToast('HPR Creation service error.', 'error');
                    });
            });
            // ==========================================
            // STEP 4 ACTION: Academic submissions
            // ==========================================
            const licenseStatusSelect = document.getElementById('license-status-select');
            const renewableGroup = document.getElementById('renewable-due-date-group');
            const renewableInput = document.getElementById('renewable-due-date');

            licenseStatusSelect.addEventListener('change', function () {
                if (this.value === 'Renewable') {
                    renewableGroup.style.display = 'block';
                } else {
                    renewableGroup.style.display = 'none';
                    renewableInput.value = '';
                }
            });



            // ==========================================
            // STEP 5 ACTION: Facility linkage search
            // ==========================================
            const btnSearchFac = document.getElementById('btn-search-facility');
            const facResultsContainer = document.getElementById('facility-results-container');

            btnSearchFac.addEventListener('click', function () {
                const name = document.getElementById('facility-search-name').value.trim();
                const pincode = document.getElementById('facility-search-pincode').value.trim();

                if (!name && !pincode) {
                    showToast('Please enter facility name or pincode to search.', 'warning');
                    return;
                }

                btnSearchFac.disabled = true;
                btnSearchFac.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Searching...';
                facResultsContainer.style.display = 'none';

                fetch('{{ route("nhpr.register.facility.search") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ facilityName: name, pincode: pincode })
                })
                    .then(res => res.json())
                    .then(data => {
                        btnSearchFac.disabled = false;
                        btnSearchFac.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Search Facilities';

                        if (data.success) {
                            facResultsContainer.innerHTML = '';

                            if (data.facilities.length === 0) {
                                facResultsContainer.innerHTML = '<div style="padding: 12px; color: var(--muted); font-size: 13px; text-align: center;">No facilities found matching details.</div>';
                                facResultsContainer.style.display = 'block';
                                return;
                            }

                            data.facilities.forEach(fac => {
                                const item = document.createElement('div');
                                item.className = 'facility-item';
                                item.innerHTML = `
                            <div class="facility-info">
                                <span class="fac-name">${fac.facilityName}</span>
                                <span class="fac-id">ID: ${fac.facilityId}</span>
                                <span class="fac-address">${fac.address || ''}, ${fac.stateName || ''}</span>
                            </div>
                            <span class="btn-link">Link Facility <i class="fa-solid fa-link"></i></span>
                        `;

                                item.addEventListener('click', function () {
                                    document.getElementById('selected-fac-name').textContent = fac.facilityName;
                                    document.getElementById('selected-fac-details').textContent = `ID: ${fac.facilityId} | address: ${fac.address || ''} | Pincode: ${fac.pincode || ''}`;
                                    document.getElementById('selected-fac-id').value = fac.facilityId;

                                    document.getElementById('selected-facility-box').style.display = 'block';
                                    facResultsContainer.style.display = 'none';
                                });

                                facResultsContainer.appendChild(item);
                            });

                            facResultsContainer.style.display = 'block';
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(err => {
                        btnSearchFac.disabled = false;
                        btnSearchFac.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Search Facilities';
                        showToast('Facility search failed.', 'error');
                    });
            });

            // Toggle search view based on Practising Select
            const practisingSelect = document.getElementById('currently-working');
            const facilityLookupCard = document.getElementById('facility-lookup-card');
            practisingSelect.addEventListener('change', function () {
                if (this.value == '1') {
                    facilityLookupCard.style.display = 'block';
                } else {
                    facilityLookupCard.style.display = 'none';
                    document.getElementById('selected-facility-box').style.display = 'none';
                    document.getElementById('selected-fac-id').value = '';
                }
            });

            // Employment Type listeners (Central vs State)
            const workStatusSelect = document.getElementById('work-status-select');
            const govtSection = document.getElementById('govt-work-details-section');
            const govtTypeSelect = document.getElementById('govt-type-select');
            const ministryGroup = document.getElementById('central-ministry-group');

            workStatusSelect.addEventListener('change', function () {
                if (this.value === '1' || this.value === '2') {
                    govtSection.style.display = 'grid';
                    toggleMinistryGroup();
                } else {
                    govtSection.style.display = 'none';
                    ministryGroup.style.display = 'none';
                }
            });

            govtTypeSelect.addEventListener('change', toggleMinistryGroup);

            function toggleMinistryGroup() {
                if (govtTypeSelect.value === 'Central') {
                    ministryGroup.style.display = 'block';
                    fetchCentralMinistries();
                } else {
                    ministryGroup.style.display = 'none';
                }
            }

            function fetchCentralMinistries() {
                const select = document.getElementById('ministry-select');
                if (select.children.length > 0) return; // already loaded

                fetch('{{ route("nhpr.register.masters.ministries") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            select.innerHTML = '';
                            data.ministries.forEach(min => {
                                const opt = document.createElement('option');
                                opt.value = min.name;
                                opt.textContent = min.name;
                                select.appendChild(opt);
                            });
                        }
                    });
            }

            // Submit registration & transition to Step 6 (Document Fetch checklist)
            const btnStep5 = document.getElementById('btn-step5-action');
            btnStep5.addEventListener('click', function () {
                const regNo = document.getElementById('registration-number').value.trim();
                const regDate = document.getElementById('registration-date').value;
                const regCert = document.getElementById('reg-cert-base64').value;

                const degreeYear = document.getElementById('degree-year').value.trim();
                const degreeCert = document.getElementById('degree-cert-base64').value;

                let valid = true;
                if (!regNo) { document.getElementById('reg-no-error').style.display = 'block'; valid = false; }
                else { document.getElementById('reg-no-error').style.display = 'none'; }

                if (!regDate) { document.getElementById('reg-date-error').style.display = 'block'; valid = false; }
                else { document.getElementById('reg-date-error').style.display = 'none'; }

                if (!regCert) { document.getElementById('reg-cert-error').style.display = 'block'; valid = false; }
                else { document.getElementById('reg-cert-error').style.display = 'none'; }

                if (degreeYear.length !== 4 || isNaN(degreeYear)) { document.getElementById('degree-year-error').style.display = 'block'; valid = false; }
                else { document.getElementById('degree-year-error').style.display = 'none'; }

                if (!degreeCert) { document.getElementById('degree-cert-error').style.display = 'block'; valid = false; }
                else { document.getElementById('degree-cert-error').style.display = 'none'; }

                // Doctor renewable due date validation
                const catVal = document.getElementById('hpr-category').value;
                if (catVal == 1 && licenseStatusSelect.value === 'Renewable') {
                    if (!renewableInput.value) {
                        document.getElementById('renewable-due-date-error').style.display = 'block';
                        valid = false;
                    } else {
                        document.getElementById('renewable-due-date-error').style.display = 'none';
                    }
                }

                const working = practisingSelect.value;
                const status = workStatusSelect.value;
                const facId = document.getElementById('selected-fac-id').value;

                if (working == '1' && !facId) {
                    showToast('Please search and select/link a facility to proceed.', 'warning');
                    valid = false;
                }

                if (!valid) {
                    showToast('Please complete all academic and facility requirements.', 'warning');
                    return;
                }

                btnStep5.disabled = true;
                btnStep5.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Submitting Profile to Council...';

                // Compile payload
                const payload = {
                    salutation: 1, // Default Dr/Mr/Ms
                    dob: '1994-08-12', // Aadhaar returned date formatting placeholder
                    languages: '1,2',
                    address: 'Dehradun, Uttarakhand',
                    pincode: '248001',
                    council_id: document.getElementById('council-select').value,
                    reg_no: document.getElementById('registration-number').value,
                    reg_date: document.getElementById('registration-date').value,
                    reg_cert_base64: document.getElementById('reg-cert-base64').value,
                    degree_code: document.getElementById('degree-select').value,
                    degree_college: 1149,
                    degree_university: document.getElementById('degree-university').value,
                    degree_year: document.getElementById('degree-year').value,
                    degree_cert_base64: document.getElementById('degree-cert-base64').value,
                    currently_working: working,
                    work_status: status,
                    facility_id: facId,
                    facility_name: document.getElementById('selected-fac-name').textContent,
                    facility_address: 'Dehradun',
                    facility_pincode: '248001',
                    // Dynamic fields
                    gov_type: (status === '1' || status === '2') ? govtTypeSelect.value : null,
                    ministry: (status === '1' || status === '2' && govtTypeSelect.value === 'Central') ? document.getElementById('ministry-select').value : null,
                    is_permanent: licenseStatusSelect.value,
                    renewable_due_date: (licenseStatusSelect.value === 'Renewable') ? renewableInput.value : null
                };

                fetch('{{ route("nhpr.register.professional.submit") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                })
                    .then(res => res.json())
                    .then(data => {
                        btnStep5.disabled = false;
                        btnStep5.innerHTML = 'Submit Professional Registry <i class="fa-solid fa-arrow-right"></i>';

                        if (data.success) {
                            showToast(data.message);

                            // Set completed summary box outputs
                            document.getElementById('success-hpr-id').textContent = data.hprId;

                            // Trigger step 6 Document list checklist fetch
                            fetchDocumentUploadChecklist();
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(err => {
                        btnStep5.disabled = false;
                        btnStep5.innerHTML = 'Submit Professional Registry <i class="fa-solid fa-arrow-right"></i>';
                        showToast('Professional submission failed.', 'error');
                    });
            });

            // ==========================================
            // STEP 6: Document Checklist Retrieval & upload
            // ==========================================
            let requiredDocChecklist = {};

            function fetchDocumentUploadChecklist() {
                fetch('{{ route("nhpr.register.documents.fetch") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            requiredDocChecklist = data.documentList;

                            const grid = document.getElementById('document-check-list-grid');
                            grid.innerHTML = '';

                            // Generate file upload cards dynamically
                            const docTypes = {
                                profilePhoto: { title: 'Passport Photo', icon: 'fa-user-tie', accept: 'image/jpeg,image/png' },
                                degreeCertificate: { title: 'Degree Certificate', icon: 'fa-file-pdf', accept: 'application/pdf' },
                                registrationCertificate: { title: 'Registration Certificate', icon: 'fa-file-shield', accept: 'application/pdf' },
                                proofOfWorkCertificate: { title: 'Proof of Work Certificate', icon: 'fa-briefcase', accept: 'application/pdf' }
                            };

                            Object.keys(requiredDocChecklist).forEach(key => {
                                const info = docTypes[key] || { title: key, icon: 'fa-file', accept: '*/*' };
                                const blockId = requiredDocChecklist[key].id || requiredDocChecklist[key];

                                const card = document.createElement('div');
                                card.className = 'form-group';
                                card.innerHTML = `
                            <label>${info.title} <span class="req">*</span></label>
                            <div class="doc-upload-item" id="card-dyn-${key}">
                                <i class="fa-solid ${info.icon} doc-icon"></i>
                                <span class="doc-name" id="name-dyn-${key}">Select file for upload</span>
                                <span class="doc-meta">Max size 5MB</span>
                                <input type="file" id="file-dyn-${key}" class="file-input-raw" accept="${info.accept}">
                            </div>
                            <input type="hidden" id="base-dyn-${key}" data-id="${blockId}" data-type="${key}">
                        `;

                                grid.appendChild(card);

                                // Setup dynamic file reader
                                handleFileAttachment(`file-dyn-${key}`, `base-dyn-${key}`, `card-dyn-${key}`, `name-dyn-${key}`);
                            });

                            activateStep(4);
                        } else {
                            showToast(data.message, 'error');
                        }
                    });
            }

            const btnStep6 = document.getElementById('btn-step6-action');
            btnStep6.addEventListener('click', function () {

                // Collect all base64 data strings
                const docPayloads = [];
                let valid = true;

                Object.keys(requiredDocChecklist).forEach(key => {
                    const hiddenInput = document.getElementById(`base-dyn-${key}`);
                    const base64Data = hiddenInput.value;
                    const blockId = hiddenInput.getAttribute('data-id');
                    const docType = hiddenInput.getAttribute('data-type');

                    if (!base64Data) {
                        showToast(`Please upload your ${key} file.`, 'warning');
                        valid = false;
                        return;
                    }

                    // Determine file mime type mapping
                    let fileType = 'application/pdf';
                    if (docType === 'profilePhoto') {
                        fileType = 'image/jpeg';
                    }

                    docPayloads.push({
                        document_id: parseInt(blockId),
                        document_type: docType,
                        fileType: fileType,
                        data: base64Data
                    });
                });

                if (!valid) return;

                btnStep6.disabled = true;
                btnStep6.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Finalizing registry uploads...';

                fetch('{{ route("nhpr.register.documents.upload") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ documents: docPayloads })
                })
                    .then(res => res.json())
                    .then(data => {
                        btnStep6.disabled = false;
                        btnStep6.innerHTML = 'Complete Registry Onboarding <i class="fa-solid fa-check-double"></i>';

                        if (data.success) {
                            showToast(data.message);

                            // Final display info updates
                            document.getElementById('success-hpr-number').textContent = data.result.hprIdNumber || 'Verified License';
                            document.getElementById('success-ref-id').textContent = activeTxnId || 'UIDAI-HANDSHAKE-DONE';

                            activateStep(5); // Show success splash screen!
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(err => {
                        btnStep6.disabled = false;
                        btnStep6.innerHTML = 'Complete Registry Onboarding <i class="fa-solid fa-check-double"></i>';
                        showToast('Document upload transactions failed.', 'error');
                    });
            });

        });
    </script>
</body>

</html>