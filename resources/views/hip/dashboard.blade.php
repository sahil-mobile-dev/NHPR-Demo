<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HIMS Records & Care Context Management | UK HIMS</title>
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

        /* Stat Grid */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-info h4 {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info p {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            margin-top: 4px;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: var(--surface2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            color: var(--primary-light);
        }

        .stat-card.linked .stat-icon {
            color: var(--success-light);
        }

        .stat-card.unlinked .stat-icon {
            color: var(--warning-light);
        }

        /* Core Layout Grid */
        .layout-grid {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 24px;
        }

        @media (max-width: 1024px) {
            .layout-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Panel Box */
        .panel-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .panel-title {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 12px;
        }

        .panel-title i {
            color: var(--primary-light);
        }

        /* Form Controls */
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
        }

        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--muted2);
        }

        .req {
            color: var(--danger-light);
        }

        .form-control {
            width: 100%;
            background: var(--surface2);
            border: 1.5px solid var(--border2);
            border-radius: 8px;
            padding: 10px 12px;
            color: #fff;
            font-size: 13px;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.15);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        /* Toggles */
        .type-tabs {
            display: flex;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 4px;
        }

        .type-tab {
            flex: 1;
            background: none;
            border: none;
            color: var(--muted);
            font-size: 12.5px;
            font-weight: 600;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .type-tab.active {
            background: var(--surface);
            color: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
        }

        /* Medication Rows */
        .med-row {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1.2fr auto;
            gap: 8px;
            align-items: center;
            margin-bottom: 8px;
        }

        .btn-icon-danger {
            background: rgba(198, 40, 40, 0.15);
            border: 1px solid rgba(198, 40, 40, 0.25);
            color: var(--danger-light);
            width: 36px;
            height: 36px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-icon-danger:hover {
            background: var(--danger);
            color: #fff;
        }

        .btn-secondary {
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-secondary:hover {
            border-color: var(--border2);
        }

        .btn-action {
            background: var(--primary);
            border: none;
            color: #fff;
            padding: 11px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-action:hover {
            background: #1976d2;
            box-shadow: 0 4px 12px rgba(21, 101, 192, 0.3);
        }

        /* Table design */
        .records-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            text-align: left;
        }

        .records-table th {
            padding: 12px;
            background: var(--surface2);
            color: var(--muted2);
            font-weight: 600;
            border-bottom: 1px solid var(--border);
        }

        .records-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }

        .badge-success {
            background: rgba(46, 125, 50, 0.15);
            border: 1px solid rgba(46, 125, 50, 0.25);
            color: var(--success-light);
        }

        .badge-warning {
            background: rgba(245, 124, 0, 0.15);
            border: 1px solid rgba(245, 124, 0, 0.25);
            color: var(--warning-light);
        }

        .btn-table-link {
            background: none;
            border: none;
            color: var(--primary-light);
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-table-link:hover {
            text-decoration: underline;
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 500;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            width: 100%;
            max-width: 680px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-body {
            padding: 20px;
        }

        .fhir-pre {
            background: var(--surface2);
            border: 1px solid var(--border);
            padding: 14px;
            border-radius: 8px;
            color: #81c784;
            font-family: monospace;
            font-size: 11.5px;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 380px;
            overflow-y: auto;
            text-align: left;
        }

        /* Toggle switch */
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
                    <div class="logo-orb"><i class="fa-solid fa-heart-pulse"></i></div>
                    <div class="logo-txt">
                        <div class="l1">ParaCare+ HIMS</div>
                        <div class="l2">Uttarakhand Govt.</div>
                    </div>
                </div>
            </div>

            <div class="sidebar-nav">
                <div class="nav-grp-title">HIMS Clinical Care</div>
                <a href="{{ route('hip.dashboard') }}" class="nav-item active">
                    <i class="fa-solid fa-notes-medical"></i> HIMS Records (HIP)
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
                    <i class="fa-solid fa-user-doctor"></i> HPR Register
                </a>
                <a href="{{ route('nhpr.track.show') }}" class="nav-item">
                    <i class="fa-solid fa-binoculars"></i> Track Status
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main">
            <!-- Sticky Topbar -->
            <div class="gov-topbar">
                <div class="gov-emblem">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/9/99/Seal_of_Uttarakhand.svg"
                        alt="Uttarakhand Govt Seal">
                    <div class="gov-title-text">
                        <span class="gov-hindi">उत्तराखंड शासन</span>
                        <span class="gov-english">State Health Intelligence Platform</span>
                    </div>
                </div>

                <div class="gateway-status">
                    <span class="status-dot active"></span>
                    <span style="color: var(--muted2);">Mode: {{ $realApiMode ? 'REAL API' : 'SIMULATION' }}</span>
                </div>
            </div>

            <!-- Content Container -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">HIMS Clinical Care Contexts (HIP)</h1>
                    <p class="page-subtitle">Compile clinical patient encounters to standard HL7 FHIR bundles and link
                        contexts securely to Ayushman Bharat accounts</p>
                </div>

                <!-- API Toggle Toolbar -->
                <div style="background: var(--surface); border: 1px solid var(--border); padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 13px; font-weight: 700; color: #fff;">Offline Sandbox Simulation</span>
                        <p style="font-size: 11.5px; color: var(--muted); margin: 0;">Bypass live gateway integrations to perform fast local tests.</p>
                    </div>
                    <div>
                        <label class="switch-toggle">
                            <input type="checkbox" id="mode-toggle-checkbox" {{ !$realApiMode ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Stats Toolbar -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h4>Total Care Contexts</h4>
                            <p>{{ $stats['total'] }}</p>
                        </div>
                        <div class="stat-icon"><i class="fa-solid fa-folder-open"></i></div>
                    </div>
                    <div class="stat-card linked">
                        <div class="stat-info">
                            <h4>Linked to ABHA</h4>
                            <p>{{ $stats['linked'] }}</p>
                        </div>
                        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                    </div>
                    <div class="stat-card unlinked">
                        <div class="stat-info">
                            <h4>Pending Link</h4>
                            <p>{{ $stats['unlinked'] }}</p>
                        </div>
                        <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
                    </div>
                </div>

                <!-- Main Layout -->
                <div class="layout-grid">
                    <!-- Left: Create Record Form -->
                    <div class="panel-box">
                        <h3 class="panel-title"><i class="fa-solid fa-file-medical"></i> Add New Medical Encounter</h3>

                        <div class="form-group">
                            <label class="form-label">Select Medical Document Type</label>
                            <div class="type-tabs">
                                <button class="type-tab active" id="tab-presc" onclick="switchFormType('PRESCRIPTION')">
                                    <i class="fa-solid fa-prescription"></i> Prescription
                                </button>
                                <button class="type-tab" id="tab-report" onclick="switchFormType('DIAGNOSTIC_REPORT')">
                                    <i class="fa-solid fa-microscope"></i> Diagnostic Report
                                </button>
                            </div>
                        </div>

                        <form id="record-form">
                            <input type="hidden" id="record_type" value="PRESCRIPTION">

                            <h4
                                style="font-size: 11px; text-transform: uppercase; color: var(--primary-light); letter-spacing: 0.5px; border-bottom: 1px solid var(--border); padding-bottom: 6px; margin-bottom: 12px;">
                                Patient Information</h4>

                            <div class="grid-2">
                                <div class="form-group">
                                    <label class="form-label" for="patient_name">Patient Name <span
                                            class="req">*</span></label>
                                    <input type="text" id="patient_name" class="form-control" placeholder="Amit Shah"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="patient_gender">Gender <span
                                            class="req">*</span></label>
                                    <select id="patient_gender" class="form-control" required>
                                        <option value="M">Male</option>
                                        <option value="F">Female</option>
                                        <option value="O">Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid-2">
                                <div class="form-group">
                                    <label class="form-label" for="patient_dob">Date of Birth <span
                                            class="req">*</span></label>
                                    <input type="date" id="patient_dob" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="patient_abha_address">ABHA Address / PHR <span
                                            class="req">*</span></label>
                                    <input type="text" id="patient_abha_address" class="form-control"
                                        placeholder="username@sbx" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="patient_abha_number">ABHA Number (Optional)</label>
                                <input type="text" id="patient_abha_number" class="form-control"
                                    placeholder="91-XXXX-XXXX-XXXX">
                            </div>

                            <h4
                                style="font-size: 11px; text-transform: uppercase; color: var(--primary-light); letter-spacing: 0.5px; border-bottom: 1px solid var(--border); padding-bottom: 6px; margin-bottom: 12px;">
                                Doctor Information</h4>
                            <div class="grid-2">
                                <div class="form-group">
                                    <label class="form-label" for="doctor_name">Doctor Name <span
                                            class="req">*</span></label>
                                    <input type="text" id="doctor_name" class="form-control"
                                        placeholder="Dr. R. K. Rawat" value="Dr. R. K. Rawat" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="doctor_hpr_id">HPR ID <span
                                            class="req">*</span></label>
                                    <input type="text" id="doctor_hpr_id" class="form-control"
                                        placeholder="hpr-doc-id@hpr" value="rawat@hpr" required>
                                </div>
                            </div>

                            <!-- Prescription Block -->
                            <div id="prescription-fields">
                                <h4
                                    style="font-size: 11px; text-transform: uppercase; color: var(--primary-light); letter-spacing: 0.5px; border-bottom: 1px solid var(--border); padding-bottom: 6px; margin-bottom: 12px;">
                                    Medications List</h4>
                                <div id="medications-container">
                                    <div class="med-row">
                                        <input type="text" class="form-control med-name" placeholder="Medication Name"
                                            required>
                                        <input type="text" class="form-control med-dosage" placeholder="e.g. 1 Tab"
                                            value="1 Tab">
                                        <input type="text" class="form-control med-duration" placeholder="e.g. 5 Days"
                                            value="5 Days">
                                        <input type="text" class="form-control med-instructions"
                                            placeholder="e.g. After food" value="After food">
                                        <button type="button" class="btn-icon-danger" onclick="removeMedRow(this)"><i
                                                class="fa-solid fa-trash"></i></button>
                                    </div>
                                </div>
                                <button type="button" class="btn-secondary" style="margin-top: 8px;"
                                    onclick="addMedRow()">
                                    <i class="fa-solid fa-plus"></i> Add Medication
                                </button>
                            </div>

                            <!-- Diagnostic Report Block -->
                            <div id="report-fields" style="display: none;">
                                <h4
                                    style="font-size: 11px; text-transform: uppercase; color: var(--primary-light); letter-spacing: 0.5px; border-bottom: 1px solid var(--border); padding-bottom: 6px; margin-bottom: 12px;">
                                    Lab Diagnostic Results</h4>
                                <div class="grid-2">
                                    <div class="form-group">
                                        <label class="form-label" for="test_name">Test Name <span
                                                class="req">*</span></label>
                                        <input type="text" id="test_name" class="form-control"
                                            placeholder="Hemoglobin (Hb)">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="test_category">Category <span
                                                class="req">*</span></label>
                                        <input type="text" id="test_category" class="form-control" value="LAB"
                                            placeholder="LAB">
                                    </div>
                                </div>
                                <div class="grid-2">
                                    <div class="form-group">
                                        <label class="form-label" for="test_result_value">Result Value <span
                                                class="req">*</span></label>
                                        <input type="number" step="0.01" id="test_result_value" class="form-control"
                                            placeholder="e.g. 14.2">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="test_result_unit">Result Unit <span
                                                class="req">*</span></label>
                                        <input type="text" id="test_result_unit" class="form-control"
                                            placeholder="e.g. g/dL">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="test_result_interpretation">Interpretation <span
                                            class="req">*</span></label>
                                    <select id="test_result_interpretation" class="form-control">
                                        <option value="N">Normal (N)</option>
                                        <option value="A">Abnormal (A)</option>
                                        <option value="H">High (H)</option>
                                        <option value="L">Low (L)</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn-action" style="width: 100%; margin-top: 20px;"
                                id="btn-submit-record">
                                Compile to FHIR R4 & Save <i class="fa-solid fa-code"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Right: Records Table -->
                    <div class="panel-box" style="align-self: flex-start;">
                        <h3 class="panel-title"><i class="fa-solid fa-folder-tree"></i> Care Context Records (HIP
                            Database)</h3>

                        <div style="overflow-x: auto;">
                            <table class="records-table">
                                <thead>
                                    <tr>
                                        <th>Patient PHR</th>
                                        <th>Context Ref</th>
                                        <th>Document Type</th>
                                        <th>Link Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="records-table-body">
                                    @forelse ($contexts as $c)
                                        <tr>
                                            <td>
                                                <div style="font-weight: 700; color: #fff;">{{ $c->patient_abha_address }}
                                                </div>
                                                <div style="font-size: 10px; color: var(--muted);">
                                                    {{ $c->created_at->format('M d, Y h:i A') }}</div>
                                            </td>
                                            <td style="font-family: monospace; font-weight: 700; color: var(--muted2);">
                                                {{ $c->care_context_reference }}</td>
                                            <td>
                                                @php
                                                    $type = $c->healthRecords->first()?->record_type ?? 'PRESCRIPTION';
                                                @endphp
                                                <span class="badge"
                                                    style="background: rgba(255,255,255,0.04); border: 1px solid var(--border); color: #fff;">
                                                    <i
                                                        class="fa-solid {{ $type === 'PRESCRIPTION' ? 'fa-prescription' : 'fa-microscope' }}"></i>
                                                    {{ $type }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($c->is_linked)
                                                    <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i>
                                                        Linked</span>
                                                @else
                                                    <span class="badge badge-warning" id="status-badge-{{ $c->id }}"><i
                                                            class="fa-solid fa-clock"></i> Unlinked</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                                    @if (!$c->is_linked)
                                                        <button class="btn-table-link" id="link-btn-{{ $c->id }}"
                                                            onclick="linkCareContext({{ $c->id }})">
                                                            <i class="fa-solid fa-link"></i> Link Context
                                                        </button>
                                                    @endif
                                                    <button class="btn-table-link" style="color: var(--success-light);"
                                                        onclick="viewFhirJson({{ json_encode($c->healthRecords->first()?->fhir_data) }})">
                                                        <i class="fa-solid fa-code"></i> View FHIR
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" style="text-align: center; color: var(--muted); padding: 30px;">
                                                No Care Context encounters registered in this hospital database yet.
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

    <!-- FHIR Viewer Modal -->
    <div class="modal-overlay" id="fhir-modal">
        <div class="modal-box" style="max-width: 680px;">
            <div class="modal-header">
                <h3 style="font-size: 14px; font-weight: 700; color: #fff;"><i class="fa-solid fa-file-invoice" style="color: var(--saffron);"></i> Patient Clinical Slip</h3>
                <button onclick="closeFhirModal()" style="background:none; border:none; color:var(--muted); font-size:18px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            
            <div class="modal-body" style="padding-top: 10px;">
                <!-- Preview Container -->
                <div id="modal-view-preview-container">
                    <!-- Dynamic rendering in JS -->
                </div>

                <div style="display: flex; gap: 10px; margin-top: 18px; border-top: 1px solid var(--border); padding-top: 12px;">
                    <button type="button" class="btn-secondary" style="flex: 1;" onclick="closeFhirModal()">Close Encounter Slip</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripting -->
    <script>
        function switchFormType(type) {
            document.getElementById('record_type').value = type;
            if (type === 'PRESCRIPTION') {
                document.getElementById('tab-presc').classList.add('active');
                document.getElementById('tab-report').classList.remove('active');
                document.getElementById('prescription-fields').style.display = 'block';
                document.getElementById('report-fields').style.display = 'none';

                // Toggle required fields
                document.getElementById('test_name').required = false;
                document.getElementById('test_result_value').required = false;
                document.getElementById('test_result_unit').required = false;
                document.querySelectorAll('.med-name').forEach(input => input.required = true);
            } else {
                document.getElementById('tab-presc').classList.remove('active');
                document.getElementById('tab-report').classList.add('active');
                document.getElementById('prescription-fields').style.display = 'none';
                document.getElementById('report-fields').style.display = 'block';

                // Toggle required fields
                document.getElementById('test_name').required = true;
                document.getElementById('test_result_value').required = true;
                document.getElementById('test_result_unit').required = true;
                document.querySelectorAll('.med-name').forEach(input => input.required = false);
            }
        }

        function addMedRow() {
            const container = document.getElementById('medications-container');
            const row = document.createElement('div');
            row.className = 'med-row';
            const isPrescription = (document.getElementById('record_type').value === 'PRESCRIPTION');
            row.innerHTML = `
                <input type="text" class="form-control med-name" placeholder="Medication Name" ${isPrescription ? 'required' : ''}>
                <input type="text" class="form-control med-dosage" placeholder="e.g. 1 Tab" value="1 Tab">
                <input type="text" class="form-control med-duration" placeholder="e.g. 5 Days" value="5 Days">
                <input type="text" class="form-control med-instructions" placeholder="e.g. After food" value="After food">
                <button type="button" class="btn-icon-danger" onclick="removeMedRow(this)"><i class="fa-solid fa-trash"></i></button>
            `;
            container.appendChild(row);
        }

        function removeMedRow(button) {
            const container = document.getElementById('medications-container');
            if (container.children.length > 1) {
                button.parentElement.remove();
            } else {
                alert("At least one medication is required.");
            }
        }

        // Form Submit -> Create Record & Compile FHIR
        const recordForm = document.getElementById('record-form');
        recordForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const btn = document.getElementById('btn-submit-record');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Compiling FHIR R4 Bundle...';

            const type = document.getElementById('record_type').value;

            const payload = {
                patient_name: document.getElementById('patient_name').value.trim(),
                patient_gender: document.getElementById('patient_gender').value,
                patient_dob: document.getElementById('patient_dob').value,
                patient_abha_address: document.getElementById('patient_abha_address').value.trim(),
                patient_abha_number: document.getElementById('patient_abha_number').value.trim(),
                record_type: type,
                doctor_name: document.getElementById('doctor_name').value.trim(),
                doctor_hpr_id: document.getElementById('doctor_hpr_id').value.trim()
            };

            if (type === 'PRESCRIPTION') {
                const meds = [];
                document.querySelectorAll('.med-row').forEach(row => {
                    meds.push({
                        name: row.querySelector('.med-name').value.trim(),
                        dosage: row.querySelector('.med-dosage').value.trim(),
                        duration: row.querySelector('.med-duration').value.trim(),
                        instructions: row.querySelector('.med-instructions').value.trim()
                    });
                });
                payload.medications = meds;
            } else {
                payload.test_name = document.getElementById('test_name').value.trim();
                payload.test_category = document.getElementById('test_category').value.trim();
                payload.test_result_value = document.getElementById('test_result_value').value;
                payload.test_result_unit = document.getElementById('test_result_unit').value.trim();
                payload.test_result_interpretation = document.getElementById('test_result_interpretation').value;
            }

            fetch("{{ route('hip.record.create') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = 'Compile to FHIR R4 & Save <i class="fa-solid fa-code"></i>';

                    if (data.success) {
                        alert("Encounter record successfully compiled to FHIR & saved!");
                        viewFhirJson(data.fhir);
                        // Reload window to refresh table after a delay
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        alert(data.message || "Compilation failed.");
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = 'Compile to FHIR R4 & Save <i class="fa-solid fa-code"></i>';
                    alert("Connection failed.");
                });
        });

        // Link Care Context Linkpost
        function linkCareContext(contextId) {
            const btn = document.getElementById('link-btn-' + contextId);
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Linking...';

            fetch("{{ route('hip.link') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ care_context_id: contextId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update badge
                        const badge = document.getElementById('status-badge-' + contextId);
                        badge.className = 'badge badge-success';
                        badge.innerHTML = '<i class="fa-solid fa-circle-check"></i> Linked';
                        btn.remove();
                        alert(data.message);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-link"></i> Link Context';
                        alert(data.message || "Failed to link context.");
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-link"></i> Link Context';
                    alert("Linking request failed. Connection error.");
                });
        }

        // FHIR Modal controls
        let activeBundleData = null;

        function parseFhirBundle(bundle) {
            let patientName = '';
            let patientGender = '';
            let patientDob = '';
            let patientAbha = '';
            
            let doctorName = '';
            let doctorHpr = '';
            
            let docType = 'PRESCRIPTION';
            let medications = [];
            let testResult = null;

            if (bundle && bundle.entry) {
                bundle.entry.forEach(entry => {
                    const res = entry.resource;
                    if (!res) return;

                    if (res.resourceType === 'Composition') {
                        patientName = res.subject ? res.subject.display : '';
                        doctorName = res.author && res.author[0] ? res.author[0].display : '';
                        docType = res.title === 'Prescription' ? 'PRESCRIPTION' : 'DIAGNOSTIC_REPORT';
                    } else if (res.resourceType === 'Patient') {
                        patientGender = res.gender || '';
                        patientDob = res.birthDate || '';
                        patientAbha = res.identifier && res.identifier[0] ? res.identifier[0].value : '';
                    } else if (res.resourceType === 'Practitioner') {
                        doctorHpr = res.identifier && res.identifier[0] ? res.identifier[0].value : '';
                    } else if (res.resourceType === 'MedicationRequest') {
                        medications.push({
                            name: res.medicationCodeableConcept ? res.medicationCodeableConcept.text : '',
                            dosage: res.dosageInstruction && res.dosageInstruction[0] ? res.dosageInstruction[0].text : ''
                        });
                    } else if (res.resourceType === 'Observation') {
                        testResult = {
                            name: res.code ? res.code.text : '',
                            value: res.valueQuantity ? res.valueQuantity.value : '',
                            unit: res.valueQuantity ? res.valueQuantity.unit : '',
                            interpretation: res.interpretation && res.interpretation[0] && res.interpretation[0].coding && res.interpretation[0].coding[0] ? res.interpretation[0].coding[0].display : 'Normal'
                        };
                    }
                });
            }

            // Render Slip HTML
            let contentHtml = `
                <div style="background:#0e1e32; border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:20px; color:#fff; font-family:'Inter', sans-serif;">
                    <!-- Govt Header -->
                    <div style="display:flex; align-items:center; justify-content:space-between; border-bottom:2px solid var(--saffron); padding-bottom:12px; margin-bottom:16px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/9/99/Seal_of_Uttarakhand.svg" style="height:38px;" alt="Govt Logo">
                            <div>
                                <div style="font-size:11px; font-weight:700; color:#fff; font-family:'Noto Sans Devanagari', sans-serif;">उत्तराखंड शासन</div>
                                <div style="font-size:10px; text-transform:uppercase; color:var(--muted); letter-spacing:0.5px;">ParaCare+ HIMS Portal</div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:12px; font-weight:800; color:var(--saffron);">${docType}</div>
                            <div style="font-size:9.5px; color:var(--muted);">Ref: ${bundle.identifier ? bundle.identifier.value : 'N/A'}</div>
                        </div>
                    </div>

                    <!-- Info Grid -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px; font-size:12.5px; background:rgba(255,255,255,0.02); padding:12px; border-radius:8px; border:1px solid var(--border);">
                        <div>
                            <div style="color:var(--muted2); font-weight:600; margin-bottom:4px;">Patient Details</div>
                            <div>Name: <strong>${patientName}</strong></div>
                            <div>Gender/DOB: ${patientGender} (${patientDob})</div>
                            <div style="margin-top:4px;">ABHA: <span class="badge badge-success" style="font-size:9px; padding:2px 6px;">${patientAbha}</span></div>
                        </div>
                        <div>
                            <div style="color:var(--muted2); font-weight:600; margin-bottom:4px;">Clinician Details</div>
                            <div>Doctor: <strong>${doctorName}</strong></div>
                            <div>HPR Registration ID: <span style="font-family:monospace; font-weight:700; color:var(--primary-light);">${doctorHpr}</span></div>
                        </div>
                    </div>

                    <!-- Clinical Data Section -->
                    <div style="border-top:1px solid var(--border); padding-top:14px;">
            `;

            if (docType === 'PRESCRIPTION') {
                contentHtml += `
                    <h4 style="font-size:12px; text-transform:uppercase; color:var(--primary-light); margin-bottom:10px;"><i class="fa-solid fa-prescription"></i> Rx - Prescribed Medications</h4>
                    <table style="width:100%; border-collapse:collapse; font-size:12px; text-align:left;">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border);">
                                <th style="padding:6px; color:var(--muted);">Medication</th>
                                <th style="padding:6px; color:var(--muted);">Dosage & Schedule</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                medications.forEach(med => {
                    contentHtml += `
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.04);">
                            <td style="padding:8px 6px; font-weight:700; color:#fff;">${med.name}</td>
                            <td style="padding:8px 6px; color:var(--muted2);">${med.dosage}</td>
                        </tr>
                    `;
                });
                contentHtml += `
                        </tbody>
                    </table>
                `;
            } else if (testResult) {
                let badgeClass = 'badge-success';
                if (testResult.interpretation.includes('High') || testResult.interpretation.includes('Abnormal')) {
                    badgeClass = 'badge-warning';
                }
                contentHtml += `
                    <h4 style="font-size:12px; text-transform:uppercase; color:var(--primary-light); margin-bottom:10px;"><i class="fa-solid fa-microscope"></i> Laboratory Observation Result</h4>
                    <div style="background:rgba(255,255,255,0.01); border:1px solid var(--border); border-radius:8px; padding:14px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <div style="font-size:11px; color:var(--muted);">Test Parameter</div>
                            <div style="font-size:14px; font-weight:700; color:#fff; margin-top:2px;">${testResult.name}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:11px; color:var(--muted);">Measured Value</div>
                            <div style="font-size:16px; font-weight:800; color:var(--gold); margin-top:2px;">${testResult.value} ${testResult.unit}</div>
                            <span class="badge ${badgeClass}" style="margin-top:6px; display:inline-flex;">${testResult.interpretation}</span>
                        </div>
                    </div>
                `;
            }

            contentHtml += `
                    </div>
                </div>
            `;

            return contentHtml;
        }

        function viewFhirJson(fhirJson) {
            activeBundleData = fhirJson;
            const previewContainer = document.getElementById('modal-view-preview-container');
            previewContainer.innerHTML = parseFhirBundle(fhirJson);
            
            document.getElementById('fhir-modal').classList.add('active');
        }

        function closeFhirModal() {
            document.getElementById('fhir-modal').classList.remove('active');
        }

        // Toggle Switch script
        document.getElementById('mode-toggle-checkbox').addEventListener('change', function() {
            const useSimulatedMode = this.checked;
            
            fetch("{{ route('nhpr.register.toggle-mode') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
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
    </script>
</body>

</html>