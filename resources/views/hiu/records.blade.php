<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParaCare+ Patient Health Record Viewer | Uttarakhand HIMS Portal</title>
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

        /* Record Panel Layout */
        .record-layout {
            display: grid;
            grid-template-columns: 2.2fr 1fr;
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
            justify-content: space-between;
        }

        .patient-card {
            background: linear-gradient(135deg, var(--surface2), var(--surface));
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .patient-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(21, 101, 192, 0.15);
            color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .patient-details h3 {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }

        .patient-details p {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 4px;
        }

        .patient-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
        }

        .meta-item {
            text-align: right;
        }

        .meta-item .val {
            font-weight: 700;
            color: #fff;
        }

        .meta-item .lbl {
            color: var(--muted);
            font-size: 11px;
            margin-top: 2px;
            text-transform: uppercase;
        }

        /* Timeline Navigation and Contents */
        .tabs-header {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
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

        /* Timeline Flow CSS */
        .timeline-container {
            position: relative;
            padding-left: 24px;
            border-left: 2px solid var(--border);
            margin-left: 12px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -33px;
            top: 2px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: var(--surface2);
            border: 3px solid var(--border);
            z-index: 2;
        }

        .timeline-item.prescription::before { border-color: var(--success); }
        .timeline-item.lab::before { border-color: var(--warning); }
        .timeline-item.encounter::before { border-color: var(--primary); }
        .timeline-item.document::before { border-color: var(--purple-light); }

        .timeline-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .timeline-item.prescription .timeline-badge { background: rgba(46, 125, 50, 0.15); color: var(--success-light); }
        .timeline-item.lab .timeline-badge { background: rgba(245, 124, 0, 0.15); color: var(--warning-light); }
        .timeline-item.encounter .timeline-badge { background: rgba(21, 101, 192, 0.15); color: var(--primary-light); }
        .timeline-item.document .timeline-badge { background: rgba(94, 53, 177, 0.15); color: var(--purple-light); }

        .timeline-card {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px;
        }

        .timeline-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .timeline-title {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }

        .timeline-date {
            font-size: 12px;
            color: var(--muted);
        }

        .timeline-meta {
            display: flex;
            gap: 16px;
            font-size: 12px;
            color: var(--muted2);
            border-top: 1px solid var(--border);
            padding-top: 10px;
            margin-top: 12px;
        }

        /* Clinical Resource Content Details */
        .med-list {
            margin-top: 10px;
        }

        .med-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 6px;
            font-size: 12.5px;
        }

        .med-item-header {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            color: #fff;
        }

        .med-item-desc {
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 4px;
        }

        /* Documents attachment styling */
        .doc-attachment {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border2);
            border-radius: 8px;
            padding: 12px;
            margin-top: 8px;
        }

        .doc-icon {
            font-size: 24px;
            color: var(--primary-light);
        }

        /* Facility Cards list */
        .facility-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .facility-item {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .facility-item:hover {
            border-color: var(--border2);
            transform: translateX(2px);
        }

        .facility-orb {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: var(--gold);
        }

        .facility-name {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
        }

        .facility-count {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
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
                            <i class="fa-solid fa-stethoscope" style="color: var(--primary-light);"></i>
                            ParaCare+ Clinical Health Record Viewer
                        </h1>
                        <p class="page-subtitle">Fully decrypted patient health records compiled and parsed from Uttarakhand ABDM network.</p>
                    </div>

                    <a href="{{ route('hiu.dashboard') }}" class="btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Back to Console
                    </a>
                </div>

                <!-- Patient Card -->
                <div class="patient-card">
                    <div class="patient-info">
                        <div class="patient-avatar"><i class="fa-solid fa-user-injured"></i></div>
                        <div class="patient-details">
                            <h3>Rahul Sharma</h3>
                            <p><i class="fa-solid fa-id-card" style="color: var(--primary-light);"></i> ABHA ADDRESS: <strong>{{ $abhaAddress }}</strong></p>
                        </div>
                    </div>

                    <div class="patient-meta">
                        <div class="meta-item">
                            <div class="val">36 Years</div>
                            <div class="lbl">Age</div>
                        </div>
                        <div class="meta-item">
                            <div class="val">Male</div>
                            <div class="lbl">Gender</div>
                        </div>
                        <div class="meta-item">
                            <div class="val">15 May 1990</div>
                            <div class="lbl">DOB</div>
                        </div>
                    </div>
                </div>

                <!-- Main Contents Grid -->
                <div class="record-layout">
                    <!-- Left: Timeline and filtered contents -->
                    <div class="panel">
                        <div class="tabs-header">
                            <button class="tab-btn active" onclick="switchTab('timeline')">Clinical Timeline</button>
                            <button class="tab-btn" onclick="switchTab('prescriptions')">Prescriptions ({{ $prescriptions->count() }})</button>
                            <button class="tab-btn" onclick="switchTab('reports')">Lab Reports ({{ $reports->count() }})</button>
                            <button class="tab-btn" onclick="switchTab('observations')">Observations ({{ $observations->count() }})</button>
                            <button class="tab-btn" onclick="switchTab('documents')">Discharge Summary & Docs ({{ $documents->count() }})</button>
                        </div>

                        <!-- Timeline Tab -->
                        <div id="tab-timeline" class="tab-content">
                            <div class="timeline-container">
                                @forelse($timeline as $item)
                                    @if($item['type'] == 'PRESCRIPTION')
                                        <div class="timeline-item prescription">
                                            <span class="timeline-badge"><i class="fa-solid fa-file-prescription"></i> Prescription</span>
                                            <div class="timeline-card">
                                                <div class="timeline-card-header">
                                                    <div>
                                                        <h4 class="timeline-title">{{ $item['title'] }}</h4>
                                                        <span style="font-size:11.5px; color: var(--muted); display:block; margin-top:2px;">Facility: <strong>{{ $item['facility'] }}</strong></span>
                                                    </div>
                                                    <span class="timeline-date">{{ $item['date']->format('d M Y H:i') }}</span>
                                                </div>
                                                
                                                <div class="med-list">
                                                    @foreach($item['details']->medications as $med)
                                                        <div class="med-item">
                                                            <div class="med-item-header">
                                                                <span><i class="fa-solid fa-pills" style="color: var(--success-light); margin-right:4px;"></i> {{ $med['name'] }}</span>
                                                                <span style="font-size: 11px; background: rgba(255,255,255,0.04); padding: 1px 6px; border-radius: 4px;">{{ $med['duration'] }}</span>
                                                            </div>
                                                            <div class="med-item-desc">
                                                                Dosage: <strong>{{ $med['dosage'] }}</strong> | Instructions: <em>{{ $med['instructions'] }}</em>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>

                                                <div class="timeline-meta">
                                                    <span><i class="fa-solid fa-user-doctor"></i> Doctor: <strong>{{ $item['doctor'] }}</strong></span>
                                                    @if($item['details']->doctor_hpr_id)
                                                        <span><i class="fa-solid fa-id-badge"></i> HPR: <strong>{{ $item['details']->doctor_hpr_id }}</strong></span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($item['type'] == 'DIAGNOSTIC_REPORT')
                                        <div class="timeline-item lab">
                                            <span class="timeline-badge"><i class="fa-solid fa-microscope"></i> Lab Report</span>
                                            <div class="timeline-card">
                                                <div class="timeline-card-header">
                                                    <div>
                                                        <h4 class="timeline-title">{{ $item['title'] }}</h4>
                                                        <span style="font-size:11.5px; color: var(--muted); display:block; margin-top:2px;">Facility: <strong>{{ $item['facility'] }}</strong></span>
                                                    </div>
                                                    <span class="timeline-date">{{ $item['date']->format('d M Y H:i') }}</span>
                                                </div>
                                                
                                                <div style="background: rgba(255, 255, 255, 0.01); border: 1px solid var(--border); padding: 12px; border-radius: 6px; margin-top: 8px;">
                                                    <span style="font-size: 11px; text-transform: uppercase; color: var(--warning-light); font-weight: 700; display: block; margin-bottom: 4px;">Conclusion / Interpretation</span>
                                                    <p style="font-size: 12.5px; line-height: 1.4; color: var(--text);">{{ $item['details']->conclusion }}</p>
                                                    
                                                    <span style="font-size: 11px; text-transform: uppercase; color: var(--muted); font-weight: 700; display: block; margin-top: 10px; margin-bottom: 4px;">Status</span>
                                                    <span style="font-size: 11.5px; font-weight: 700; color: var(--success-light);"><i class="fa-solid fa-check-double"></i> {{ $item['details']->result_status }}</span>
                                                </div>

                                                <div class="timeline-meta">
                                                    <span><i class="fa-solid fa-user-doctor"></i> Interpreter: <strong>{{ $item['doctor'] }}</strong></span>
                                                    <span><i class="fa-solid fa-tag"></i> Category: <strong>{{ $item['details']->category }}</strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($item['type'] == 'ENCOUNTER')
                                        <div class="timeline-item encounter">
                                            <span class="timeline-badge"><i class="fa-solid fa-user-nurse"></i> Encounter</span>
                                            <div class="timeline-card">
                                                <div class="timeline-card-header">
                                                    <div>
                                                        <h4 class="timeline-title">{{ $item['title'] }}</h4>
                                                        <span style="font-size:11.5px; color: var(--muted); display:block; margin-top:2px;">Facility: <strong>{{ $item['facility'] }}</strong></span>
                                                    </div>
                                                    <span class="timeline-date">{{ $item['date']->format('d M Y H:i') }}</span>
                                                </div>

                                                <div class="timeline-meta">
                                                    <span><i class="fa-solid fa-user-doctor"></i> Practitioner: <strong>{{ $item['doctor'] }}</strong></span>
                                                    <span><i class="fa-solid fa-hospital-user"></i> Class: <strong>{{ $item['details']->class_code }}</strong></span>
                                                    <span><i class="fa-solid fa-check"></i> Status: <strong>{{ $item['details']->status }}</strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($item['type'] == 'DOCUMENT')
                                        <div class="timeline-item document">
                                            <span class="timeline-badge"><i class="fa-solid fa-file-signature"></i> Clinical Document</span>
                                            <div class="timeline-card">
                                                <div class="timeline-card-header">
                                                    <div>
                                                        <h4 class="timeline-title">{{ $item['title'] }}</h4>
                                                        <span style="font-size:11.5px; color: var(--muted); display:block; margin-top:2px;">Facility: <strong>{{ $item['facility'] }}</strong></span>
                                                    </div>
                                                    <span class="timeline-date">{{ $item['date']->format('d M Y H:i') }}</span>
                                                </div>

                                                <div class="doc-attachment">
                                                    <div style="display:flex; align-items:center; gap:10px;">
                                                        <div class="doc-icon"><i class="fa-solid fa-file-text"></i></div>
                                                        <div>
                                                            <div style="font-size:13px; font-weight:700; color:#fff;">Attachment File</div>
                                                            <div style="font-size:11px; color:var(--muted); margin-top:2px;">Type: text/plain</div>
                                                        </div>
                                                    </div>
                                                    <button class="btn-secondary" style="padding: 6px 12px; font-size:11.5px;" onclick="showDocumentContent('{{ $item['details']->title }}', '{{ base64_encode($item['details']->file_content) }}')">
                                                        <i class="fa-solid fa-eye"></i> View File
                                                    </button>
                                                </div>

                                                <div class="timeline-meta">
                                                    <span><i class="fa-solid fa-user-doctor"></i> Author: <strong>{{ $item['doctor'] }}</strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @empty
                                    <div style="text-align: center; color: var(--muted); padding: 40px;">
                                        No health records logged on patient timeline yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Prescriptions Tab -->
                        <div id="tab-prescriptions" class="tab-content" style="display: none;">
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Facility</th>
                                            <th>Doctor Name</th>
                                            <th>Medications Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($prescriptions as $p)
                                            <tr>
                                                <td>{{ $p->prescription_date->format('d M Y') }}</td>
                                                <td>{{ $p->facility_name }}</td>
                                                <td>{{ $p->doctor_name }}</td>
                                                <td>
                                                    @foreach($p->medications as $med)
                                                        <div style="background: rgba(255,255,255,0.02); padding: 8px; border-radius: 4px; margin-bottom: 4px; border: 1px solid var(--border);">
                                                            <strong>{{ $med['name'] }}</strong> ({{ $med['dosage'] }} - {{ $med['duration'] }})<br>
                                                            <span style="font-size:11px; color: var(--muted);">Instructions: {{ $med['instructions'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" style="text-align: center; color: var(--muted); padding: 30px;">
                                                    No prescriptions found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Lab Reports Tab -->
                        <div id="tab-reports" class="tab-content" style="display: none;">
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Facility</th>
                                            <th>Test Name</th>
                                            <th>Interpreter</th>
                                            <th>Conclusion</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reports as $r)
                                            <tr>
                                                <td>{{ $r->report_date->format('d M Y') }}</td>
                                                <td>{{ $r->facility_name }}</td>
                                                <td><strong>{{ $r->test_name }}</strong></td>
                                                <td>{{ $r->doctor_name }}</td>
                                                <td>{{ $r->conclusion }}</td>
                                                <td><span class="badge badge-active">{{ $r->result_status }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" style="text-align: center; color: var(--muted); padding: 30px;">
                                                    No lab reports found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Observations Tab -->
                        <div id="tab-observations" class="tab-content" style="display: none;">
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Facility</th>
                                            <th>Observation Metric</th>
                                            <th>Value</th>
                                            <th>Unit</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($observations as $o)
                                            <tr>
                                                <td>{{ $o->observation_date->format('d M Y H:i') }}</td>
                                                <td>{{ $o->facility_name }}</td>
                                                <td><strong>{{ $o->display }}</strong></td>
                                                <td style="color: var(--warning-light); font-weight: 700;">{{ $o->value }}</td>
                                                <td>{{ $o->unit ?? '-' }}</td>
                                                <td><span class="badge badge-active">{{ $o->status }}</span></td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" style="text-align: center; color: var(--muted); padding: 30px;">
                                                    No observations recorded.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Documents Tab -->
                        <div id="tab-documents" class="tab-content" style="display: none;">
                            <div class="table-container">
                                <table class="custom-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Facility</th>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($documents as $d)
                                            <tr>
                                                <td>{{ $d->document_date->format('d M Y') }}</td>
                                                <td>{{ $d->facility_name }}</td>
                                                <td><strong>{{ $d->title }}</strong> ({{ $d->document_type }})</td>
                                                <td>{{ $d->author_name }}</td>
                                                <td>
                                                    <button class="btn-secondary" style="padding: 4px 8px; font-size: 11px;" onclick="showDocumentContent('{{ $d->title }}', '{{ base64_encode($d->file_content) }}')">
                                                        <i class="fa-solid fa-eye"></i> View File
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" style="text-align: center; color: var(--muted); padding: 30px;">
                                                    No clinical documents found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Linked Facilities -->
                    <div>
                        <div class="panel">
                            <h2 class="panel-title" style="margin-bottom: 16px;">
                                <span><i class="fa-solid fa-hospital" style="color: var(--gold);"></i> Originating Facilities</span>
                            </h2>
                            <p style="font-size: 12px; color: var(--muted); margin-bottom: 20px; line-height: 1.4;">
                                Clinics and facilities in Uttarakhand healthcare network that compiled and pushed these records.
                            </p>

                            <div class="facility-list">
                                @forelse($facilities as $facName)
                                    <div class="facility-item">
                                        <div class="facility-orb"><i class="fa-solid fa-clinic-medical"></i></div>
                                        <div>
                                            <div class="facility-name">{{ $facName }}</div>
                                            <div class="facility-count"> Uttarakhand ABDM Node</div>
                                        </div>
                                    </div>
                                @empty
                                    <div style="text-align: center; color: var(--muted); padding: 20px; font-size:12.5px;">
                                        No facilities linked to these records yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Document Modal dialog simulation -->
    <div id="document-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center; padding: 20px;">
        <div class="panel" style="width:100%; max-width:600px; background:var(--surface); border:1px solid var(--border2);">
            <div class="panel-title">
                <span id="modal-title">Clinical Attachment Document</span>
                <button class="btn-secondary" style="padding: 4px 10px;" onclick="closeDocumentModal()">Close</button>
            </div>
            <div style="background:var(--surface2); border:1px solid var(--border); padding:20px; border-radius:8px; max-height:400px; overflow-y:auto;">
                <p id="modal-content" style="font-family: monospace; font-size:13px; color:var(--text); white-space: pre-wrap; line-height:1.5;"></p>
            </div>
        </div>
    </div>

    <!-- Script Handling -->
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
            
            event.target.classList.add('active');
            document.getElementById('tab-' + tabId).style.display = 'block';
        }

        function showDocumentContent(title, base64Content) {
            document.getElementById('modal-title').textContent = title;
            try {
                // Decode base64 content
                const decoded = atob(base64Content);
                document.getElementById('modal-content').textContent = decoded;
            } catch (e) {
                document.getElementById('modal-content').textContent = "Unable to decode base64 attachment: " + base64Content;
            }
            document.getElementById('document-modal').style.display = 'flex';
        }

        function closeDocumentModal() {
            document.getElementById('document-modal').style.display = 'none';
        }
    </script>
</body>

</html>
