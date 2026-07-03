<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HIMS Consent Registry & Security Audit Logs | UK HIMS</title>
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
            color: var(--saffron);
        }

        .stat-card.success .stat-icon {
            color: var(--success-light);
        }

        /* Core Layout Grid */
        .layout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
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
            color: var(--saffron);
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
            border-color: var(--saffron);
            box-shadow: 0 0 0 3px rgba(230, 81, 0, 0.15);
        }

        .btn-action {
            background: var(--saffron);
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
            background: #e65100;
            box-shadow: 0 4px 12px rgba(230, 81, 0, 0.3);
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

        .badge-info {
            background: rgba(21, 101, 192, 0.15);
            border: 1px solid rgba(21, 101, 192, 0.25);
            color: var(--primary-light);
        }

        /* Modal Overlay */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
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
            max-width: 580px;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
            from { opacity:0; transform: translateY(-20px); }
            to { opacity:1; transform: translateY(0); }
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
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
            padding-bottom: 8px;
            font-size: 13px;
        }

        .detail-label {
            color: var(--muted);
            font-weight: 500;
        }

        .detail-value {
            color: #fff;
            font-weight: 700;
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
                <a href="{{ route('hip.dashboard') }}" class="nav-item">
                    <i class="fa-solid fa-notes-medical"></i> HIMS Records (HIP)
                </a>
                <a href="{{ route('hip.consents') }}" class="nav-item active">
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
                    <img src="https://upload.wikimedia.org/wikipedia/commons/9/99/Seal_of_Uttarakhand.svg" alt="Uttarakhand Govt Seal">
                    <div class="gov-title-text">
                        <span class="gov-hindi">उत्तराखंड शासन</span>
                        <span class="gov-english">State Health Intelligence Platform</span>
                    </div>
                </div>
            </div>

            <!-- Content Container -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Patient Consent Registry & Security Audit Logs</h1>
                    <p class="page-subtitle">Verify authorized patient consent policies and monitor encrypted health data transfer events</p>
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
                            <h4>Total Registered Consents</h4>
                            <p>{{ $stats['total_consents'] }}</p>
                        </div>
                        <div class="stat-icon"><i class="fa-solid fa-file-shield"></i></div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-info">
                            <h4>Active Granted Consents</h4>
                            <p>{{ $stats['active_consents'] }}</p>
                        </div>
                        <div class="stat-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    </div>
                    <div class="stat-card success">
                        <div class="stat-info">
                            <h4>Secure Encrypted Transfers</h4>
                            <p>{{ $stats['transfers_count'] }}</p>
                        </div>
                        <div class="stat-icon"><i class="fa-solid fa-lock-open"></i></div>
                    </div>
                </div>

                <!-- Layout Columns -->
                <div class="layout-grid">
                    
                    <!-- Left Column: Consents -->
                    <div class="panel-box">
                        <h3 class="panel-title"><i class="fa-solid fa-shield-halved"></i> Patient Consent Permissions</h3>
                        
                        <!-- Mini Register Consent form -->
                        <form id="consent-register-form" style="background:var(--surface2); padding:16px; border-radius:8px; border:1px solid var(--border);">
                            <h4 style="font-size:11px; text-transform:uppercase; color:var(--saffron); margin-bottom:12px; letter-spacing:0.5px;">Register Incoming Patient Consent Policy</h4>
                            <div class="form-group">
                                <label class="form-label" for="consent_address">Patient ABHA Address <span class="req">*</span></label>
                                <input type="text" id="consent_address" class="form-control" placeholder="username@sbx" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="consent_status">Policy Status</label>
                                <select id="consent_status" class="form-control">
                                    <option value="GRANTED">GRANTED (Access Permitted)</option>
                                    <option value="REVOKED">REVOKED (Access Terminated)</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-action" style="width:100%;" id="btn-register-consent">
                                Save Policy <i class="fa-solid fa-floppy-disk"></i>
                            </button>
                        </form>

                        <div style="overflow-x: auto;">
                            <table class="records-table">
                                <thead>
                                    <tr>
                                        <th>Consent Ref</th>
                                        <th>Patient ABHA</th>
                                        <th>Status</th>
                                        <th>Authorized Purpose</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($consents as $c)
                                        <tr>
                                            <td style="font-family: monospace; font-weight: 700; color: var(--muted2);">{{ $c->consent_id }}</td>
                                            <td>{{ $c->patient_abha_address }}</td>
                                            <td>
                                                @if ($c->status === 'GRANTED')
                                                    <span class="badge badge-success"><i class="fa-solid fa-shield-halved"></i> GRANTED</span>
                                                @else
                                                    <span class="badge badge-warning"><i class="fa-solid fa-ban"></i> REVOKED</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span style="font-size: 11.5px; color: var(--muted);">{{ $c->consent_detail['purpose'] ?? 'General Consultation' }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" style="text-align:center; color:var(--muted); padding:20px;">
                                                No consent policies logged in this hospital database yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right Column: Audit Logs -->
                    <div class="panel-box">
                        <h3 class="panel-title"><i class="fa-solid fa-lock"></i> Security & Cryptography Audit Trail</h3>
                        
                        <!-- Mini Exchange Trigger form -->
                        <form id="audit-trigger-form" style="background:var(--surface2); padding:16px; border-radius:8px; border:1px solid var(--border);">
                            <h4 style="font-size:11px; text-transform:uppercase; color:var(--saffron); margin-bottom:12px; letter-spacing:0.5px;">Trigger Secure Data Exchange Audit</h4>
                            <div class="form-group">
                                <label class="form-label" for="request_consent_id">Select Active Consent Reference</label>
                                <select id="request_consent_id" class="form-control" required>
                                    <option value="">-- Choose Active Consent ID --</option>
                                    @foreach ($consents->where('status', 'GRANTED') as $c)
                                        <option value="{{ $c->consent_id }}">{{ $c->consent_id }} ({{ $c->patient_abha_address }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn-action" style="width:100%;" id="btn-exchange">
                                Perform Secure Exchange <i class="fa-solid fa-key"></i>
                            </button>
                        </form>

                        <div style="overflow-x: auto;">
                            <table class="records-table">
                                <thead>
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>HIU Requester</th>
                                        <th>Records</th>
                                        <th>Security Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($auditLogs as $log)
                                        <tr>
                                            <td style="font-family: monospace; font-weight: 700; color: var(--muted2);">{{ $log->transaction_id }}</td>
                                            <td>{{ $log->hiu_id }}</td>
                                            <td style="font-weight: 700;">{{ $log->records_transferred }} FHIR Bundles</td>
                                            <td>
                                                @if ($log->status === 'SUCCESS')
                                                    <span class="badge badge-success"><i class="fa-solid fa-lock"></i> AES-GCM SECURED</span>
                                                @else
                                                    <span class="badge badge-warning"><i class="fa-solid fa-circle-exclamation"></i> FAILED</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" style="text-align:center; color:var(--muted); padding:20px;">
                                                No clinical records secure transfers logged yet.
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

    <!-- Crypto Detail Modal -->
    <div class="modal-overlay" id="crypto-modal">
        <div class="modal-box">
            <div class="modal-header">
                <h3 style="font-size: 14px; font-weight: 700; color: #fff;"><i class="fa-solid fa-circle-check" style="color: var(--success-light);"></i> Secure Health Data Exchange Completed</h3>
                <button onclick="closeModal()" style="background:none; border:none; color:var(--muted); font-size:18px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <p style="font-size: 12.5px; color: var(--muted); margin-bottom: 12px;">The clinical bundles were packaged in FHIR R4 document formats and encrypted natively in the background using the official ECDH key exchange specification before transfer.</p>
                
                <div class="detail-row">
                    <span class="detail-label">Symmetric Algorithm</span>
                    <span class="detail-value">AES-GCM-256 (12-byte IV)</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Key Exchange Curve</span>
                    <span class="detail-value">ECDH prime256v1 (NIST P-256)</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Local Public Key (HIP)</span>
                    <span class="detail-value" style="font-family: monospace; font-size: 11px;" id="local-key-val"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Remote Public Key (HIU)</span>
                    <span class="detail-value" style="font-family: monospace; font-size: 11px;" id="remote-key-val"></span>
                </div>
                <div class="detail-row" style="border-bottom: none;">
                    <span class="detail-label">Encrypted Payload size</span>
                    <span class="detail-value" style="color: var(--success-light);">AES-256 Authenticated Tag Verification: OK</span>
                </div>

                <button class="btn-action" style="margin-top: 10px;" onclick="closeModal()">Acknowledge & Close</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Register Consent Policy Submit
        const formConsent = document.getElementById('consent-register-form');
        formConsent.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btn-register-consent');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

            const payload = {
                patient_abha_address: document.getElementById('consent_address').value.trim(),
                status: document.getElementById('consent_status').value
            };

            fetch("{{ route('hip.consents.register') }}", {
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
                btn.innerHTML = 'Save Policy <i class="fa-solid fa-floppy-disk"></i>';
                
                if (data.success) {
                    alert("Consent policy successfully saved/updated in the database!");
                    window.location.reload();
                } else {
                    alert(data.message || "Failed to save policy.");
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Save Policy <i class="fa-solid fa-floppy-disk"></i>';
                alert("Connection error.");
            });
        });

        // Exchange Submit
        const formExchange = document.getElementById('audit-trigger-form');
        formExchange.addEventListener('submit', function(e) {
            e.preventDefault();
            const consentId = document.getElementById('request_consent_id').value;
            if (!consentId) return;

            const btn = document.getElementById('btn-exchange');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Encrypting & Transferring...';

            fetch("{{ route('hip.consents.exchange') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ consent_id: consentId })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Perform Secure Exchange <i class="fa-solid fa-key"></i>';
                
                if (data.success) {
                    document.getElementById('local-key-val').innerText = data.crypto.hipPublicKey;
                    document.getElementById('remote-key-val').innerText = data.crypto.hiuPublicKey;
                    document.getElementById('crypto-modal').classList.add('active');
                } else {
                    alert(data.message || "Exchange failed.");
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Perform Secure Exchange <i class="fa-solid fa-key"></i>';
                alert("Connection failed.");
            });
        });

        function closeModal() {
            document.getElementById('crypto-modal').classList.remove('active');
            window.location.reload();
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
