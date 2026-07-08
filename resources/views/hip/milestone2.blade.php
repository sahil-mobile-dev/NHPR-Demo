<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ABDM Milestone 2 Feature Map | ParaCare+ HIMS</title>
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

        /* Topbar */
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
            background: var(--success-light);
            box-shadow: 0 0 8px var(--success-light);
            display: inline-block;
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
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.3px;
        }

        .page-subtitle {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 3px;
        }

        /* Live System Stats Grid */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 30px;
        }

        @media (max-width: 1024px) {
            .stat-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .stat-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .stat-info h4 {
            font-size: 11px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info p {
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            margin-top: 6px;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: var(--surface2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-card.blue .stat-icon { color: var(--primary-light); }
        .stat-card.green .stat-icon { color: var(--success-light); }
        .stat-card.amber .stat-icon { color: var(--warning-light); }
        .stat-card.danger .stat-icon { color: var(--danger-light); }

        /* Categorized Feature Flow Panels */
        .category-section {
            margin-bottom: 35px;
        }

        .category-title {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--primary-light);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 2px solid var(--border2);
            padding-bottom: 8px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        @media (max-width: 900px) {
            .feature-grid {
                grid-template-columns: 1fr;
            }
        }

        .feature-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
        }

        .feature-card:hover {
            border-color: rgba(96, 165, 250, 0.3);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .feature-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .feature-index {
            font-size: 11px;
            font-weight: 800;
            color: rgba(255, 255, 255, 0.15);
            background: var(--surface2);
            padding: 3px 8px;
            border-radius: 6px;
        }

        .feature-title {
            font-size: 14.5px;
            font-weight: 700;
            color: #fff;
            flex: 1;
            margin-left: 8px;
        }

        .status-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.active {
            background: rgba(46, 125, 50, 0.15);
            color: var(--success-light);
            border: 1px solid rgba(46, 125, 50, 0.3);
        }

        .status-badge.simulated {
            background: rgba(245, 124, 0, 0.15);
            color: var(--warning-light);
            border: 1px solid rgba(245, 124, 0, 0.3);
        }

        .feature-desc {
            font-size: 12.5px;
            color: var(--muted2);
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .feature-tech {
            background: var(--surface2);
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 11.5px;
            border: 1px solid var(--border);
        }

        .tech-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .tech-row:last-child {
            margin-bottom: 0;
        }

        .tech-label {
            color: var(--muted);
            font-weight: 500;
        }

        .tech-val {
            font-family: monospace;
            color: var(--primary-light);
            font-weight: 600;
        }

        .tech-val.success {
            color: var(--success-light);
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
                <a href="{{ route('hip.milestone2') }}" class="nav-item active">
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
                    <span class="status-dot"></span>
                    <span style="color: var(--muted2);">ABDM Gateway Certification Center</span>
                </div>
            </div>

            <!-- Content Area -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">ABDM Milestone 2 Feature Map</h1>
                    <p class="page-subtitle">Examine the 24 foundational capabilities enabling consent-based health information exchange (HIE) as a certified HIP/HIU</p>
                </div>

                <!-- Live System Stats -->
                <div class="stat-grid">
                    <div class="stat-card blue">
                        <div class="stat-info">
                            <h4>HIMS Care Contexts</h4>
                            <p>{{ $stats['contexts_count'] }}</p>
                        </div>
                        <div class="stat-icon"><i class="fa-solid fa-folder-tree"></i></div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-info">
                            <h4>ABHA Consent Artefacts</h4>
                            <p>{{ $stats['consents_count'] }}</p>
                        </div>
                        <div class="stat-icon"><i class="fa-solid fa-shield-check"></i></div>
                    </div>
                    <div class="stat-card amber">
                        <div class="stat-info">
                            <h4>HL7 FHIR Bundles</h4>
                            <p>{{ $stats['records_count'] }}</p>
                        </div>
                        <div class="stat-icon"><i class="fa-solid fa-file-code"></i></div>
                    </div>
                    <div class="stat-card danger">
                        <div class="stat-info">
                            <h4>Security Audits Logged</h4>
                            <p>{{ $stats['audits_count'] }}</p>
                        </div>
                        <div class="stat-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    </div>
                </div>

                <!-- 1. Care Context & Patient Identification -->
                <div class="category-section">
                    <h3 class="category-title"><i class="fa-solid fa-user-gear"></i> Care Context & Patient Linking</h3>
                    <div class="feature-grid">
                        <!-- Feature 1 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.01</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Care Context Management</h4>
                                <p class="feature-desc">Organize clinic encounters (OPD visit, Lab test, Admission) into logical Care Context structures mapped to unique visit identifiers.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Model:</span><span class="tech-val">CareContext</span></div>
                                <div class="tech-row"><span class="tech-label">Relationship:</span><span class="tech-val">hasMany(HealthRecord)</span></div>
                            </div>
                        </div>

                        <!-- Feature 2 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.02</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">ABHA Record Linking</h4>
                                <p class="feature-desc">Establishes robust cryptographic bindings linking internal patient visit records to their validated National ABHA IDs.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">API Triggered:</span><span class="tech-val">/v3/hip/link/care-contexts</span></div>
                                <div class="tech-row"><span class="tech-label">Action:</span><span class="tech-val">linkCareContext()</span></div>
                            </div>
                        </div>

                        <!-- Feature 3 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.03</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">HIP Initiated Linking</h4>
                                <p class="feature-desc">The hospital initiates instant linking of doctors' consultations directly after generation without patient discovery steps.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Class:</span><span class="tech-val">HipLinkingService</span></div>
                                <div class="tech-row"><span class="tech-label">Status:</span><span class="tech-val success">Ready</span></div>
                            </div>
                        </div>

                        <!-- Feature 4 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.04</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">User Initiated Linking</h4>
                                <p class="feature-desc">Handles incoming OTP validation and linkage requests when patients search and link hospital records using PHR applications.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Callback API:</span><span class="tech-val">/v3/hip/link/init</span></div>
                                <div class="tech-row"><span class="tech-label">Confirm API:</span><span class="tech-val">/v3/hip/link/confirm</span></div>
                            </div>
                        </div>

                        <!-- Feature 5 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.05</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Patient Discovery</h4>
                                <p class="feature-desc">Fulfills asynchronous queries checking if matching patient demographics and care records exist in HIMS records.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Callback API:</span><span class="tech-val">/v3/hip/discover</span></div>
                                <div class="tech-row"><span class="tech-label">Response:</span><span class="tech-val">onDiscoverResponse()</span></div>
                            </div>
                        </div>

                        <!-- Feature 6 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.06</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Patient Profile Share</h4>
                                <p class="feature-desc">Enables patients to instantly share basic demographic profiles at registry desks by scanning HIMS QR Codes.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Simulated:</span><span class="tech-val">abha/verify/qr</span></div>
                                <div class="tech-row"><span class="tech-label">Status:</span><span class="tech-val success">Supported</span></div>
                            </div>
                        </div>

                        <!-- Feature 7 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.07</span>
                                    <span class="status-badge simulated">Simulated</span>
                                </div>
                                <h4 class="feature-title">Deep Linking (SMS)</h4>
                                <p class="feature-desc">Hospital triggers ABDM SMS notifications containing direct deep links, allowing patients to approve linking in one click.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Outgoing API:</span><span class="tech-val">/v3/hip/link/notify-sms</span></div>
                                <div class="tech-row"><span class="tech-label">Helper:</span><span class="tech-val">notifyPatientSms()</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Consent Management -->
                <div class="category-section">
                    <h3 class="category-title"><i class="fa-solid fa-file-shield"></i> Consent Management</h3>
                    <div class="feature-grid">
                        <!-- Feature 8 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.08</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Consent Request & Approval Flow</h4>
                                <p class="feature-desc">Handles the complete lifecycle of consent request generation, patient notification, and approval/rejection webhooks.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Incoming API:</span><span class="tech-val">/v3/consents/hip/notify</span></div>
                                <div class="tech-row"><span class="tech-label">Method:</span><span class="tech-val">apiConsentNotify()</span></div>
                            </div>
                        </div>

                        <!-- Feature 9 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.09</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Consent Artefact Storage</h4>
                                <p class="feature-desc">Securely stores authorized Consent Artefacts outlining allowed purposes, validation ranges, and expiry epochs.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Model:</span><span class="tech-val">ConsentArtefact</span></div>
                                <div class="tech-row"><span class="tech-label">Validation:</span><span class="tech-val">isExpired() Check</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. FHIR Records & Packaging -->
                <div class="category-section">
                    <h3 class="category-title"><i class="fa-solid fa-cubes"></i> Standardized FHIR Records</h3>
                    <div class="feature-grid">
                        <!-- Feature 10 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.10</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Health Record Sharing</h4>
                                <p class="feature-desc">Enables clinical documentation sharing including doctor prescriptions, diagnostic reports, discharge summaries, etc.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Model:</span><span class="tech-val">HealthRecord</span></div>
                                <div class="tech-row"><span class="tech-label">Controller Action:</span><span class="tech-val">createRecordStore()</span></div>
                            </div>
                        </div>

                        <!-- Feature 11 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.11</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Health Record Discovery</h4>
                                <p class="feature-desc">Exposes care contexts to HIUs allowing remote health intelligence portals to identify shareable visit groups.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Callback API:</span><span class="tech-val">/v3/hip/discover</span></div>
                                <div class="tech-row"><span class="tech-label">Action:</span><span class="tech-val">apiDiscover()</span></div>
                            </div>
                        </div>

                        <!-- Feature 12 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.12</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Standardized FHIR Records</h4>
                                <p class="feature-desc">Converts internal database rows into global HL7 FHIR (R4) compliant JSON documents representing clinics, encounters, and diagnoses.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Class:</span><span class="tech-val">FhirBundleService</span></div>
                                <div class="tech-row"><span class="tech-label">Resources:</span><span class="tech-val">Patient, Encounter, Practitioner</span></div>
                            </div>
                        </div>

                        <!-- Feature 13 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.13</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">FHIR Bundle Generation</h4>
                                <p class="feature-desc">Assembles separate FHIR entities (Practitioners, Encounters, Medications) into unified, queryable clinical FHIR Bundles.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Method Presc:</span><span class="tech-val">buildPrescriptionBundle()</span></div>
                                <div class="tech-row"><span class="tech-label">Method Report:</span><span class="tech-val">buildReportBundle()</span></div>
                            </div>
                        </div>

                        <!-- Feature 14 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.14</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Health Data Packaging</h4>
                                <p class="feature-desc">Bundles generated FHIR records with signature markers, timestamps, and compliance headers before initiating cryptographic packaging.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Service:</span><span class="tech-val">FhirBundleService</span></div>
                                <div class="tech-row"><span class="tech-label">Output:</span><span class="tech-val">Encapsulated JSON</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Security & Secure Exchange -->
                <div class="category-section">
                    <h3 class="category-title"><i class="fa-solid fa-lock"></i> Encryption & Secure Exchange</h3>
                    <div class="feature-grid">
                        <!-- Feature 15 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.15</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Encryption & Secure Exchange</h4>
                                <p class="feature-desc">Enforces ECDH key exchange with AES-256-GCM encryption. Health records are never exchanged over the wire in plain JSON.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Crypt Engine:</span><span class="tech-val">AbdmCryptEngine</span></div>
                                <div class="tech-row"><span class="tech-label">Cipher:</span><span class="tech-val">aes-256-gcm</span></div>
                            </div>
                        </div>

                        <!-- Feature 16 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.16</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Main Envelope Generation</h4>
                                <p class="feature-desc">Wraps encrypted data arrays, digital signatures, and recipient public keys into standard envelopes required by ABDM Gateway.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Format:</span><span class="tech-val">healthInformation.request</span></div>
                                <div class="tech-row"><span class="tech-label">Wrapper:</span><span class="tech-val">Main Payload Schema</span></div>
                            </div>
                        </div>

                        <!-- Feature 17 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.17</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Secure Data Transfer</h4>
                                <p class="feature-desc">Initiates direct HTTP payload delivery to the client-specified data push URL (HIU node endpoint) following gateway directives.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Outgoing Call:</span><span class="tech-val">Http::post($pushUrl)</span></div>
                                <div class="tech-row"><span class="tech-label">Engine:</span><span class="tech-val">Laravel HTTP Client</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Operational, Monitoring & Config -->
                <div class="category-section">
                    <h3 class="category-title"><i class="fa-solid fa-sliders"></i> Operations & Monitoring</h3>
                    <div class="feature-grid">
                        <!-- Feature 18 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.18</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">System Notifications</h4>
                                <p class="feature-desc">Dispatches webhook notifications notifying subscribers when a patient grants a new consent or care context links change.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Endpoint:</span><span class="tech-val">/v3/hip/link/context/notify</span></div>
                                <div class="tech-row"><span class="tech-label">Status:</span><span class="tech-val success">Supported</span></div>
                            </div>
                        </div>

                        <!-- Feature 19 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.19</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Audit Logging</h4>
                                <p class="feature-desc">Logs every data request, gateway handshake, consent change, and system transaction for strict regulatory compliance auditing.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Model:</span><span class="tech-val">SecurityAuditLog</span></div>
                                <div class="tech-row"><span class="tech-label">Logger:</span><span class="tech-val">SecurityAuditLog::log()</span></div>
                            </div>
                        </div>

                        <!-- Feature 20 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.20</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Transaction Tracking</h4>
                                <p class="feature-desc">Identifies every sequence flow step using a unique tracking transaction UUID, ensuring complete trace capability.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Header:</span><span class="tech-val">REQUEST-ID</span></div>
                                <div class="tech-row"><span class="tech-label">Format:</span><span class="tech-val">UUIDv4 String</span></div>
                            </div>
                        </div>

                        <!-- Feature 21 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.21</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Facility Registration</h4>
                                <p class="feature-desc">Configures hospital and medical centers within the central NHA registry, binding clinic resources to active Bridge IDs.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Form Wizard:</span><span class="tech-val">nhpr/register</span></div>
                                <div class="tech-row"><span class="tech-label">Model:</span><span class="tech-val">HprProfile</span></div>
                            </div>
                        </div>

                        <!-- Feature 22 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.22</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Bridge Configuration</h4>
                                <p class="feature-desc">Binds the client application base URLs and webhook services to the ABDM gateway configurations.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Controller:</span><span class="tech-val">NhprController</span></div>
                                <div class="tech-row"><span class="tech-label">Credentials:</span><span class="tech-val">Client ID & Secret</span></div>
                            </div>
                        </div>

                        <!-- Feature 23 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.23</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Health Information Exchange (HIE)</h4>
                                <p class="feature-desc">Implements HIE patterns enabling doctors at Hospital A to securely fetch patient records generated at Hospital B under active consent.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Gateways:</span><span class="tech-val">HIP/HIU Handshake</span></div>
                                <div class="tech-row"><span class="tech-label">Status:</span><span class="tech-val success">Certified</span></div>
                            </div>
                        </div>

                        <!-- Feature 24 -->
                        <div class="feature-card">
                            <div>
                                <div class="feature-header">
                                    <span class="feature-index">M2.24</span>
                                    <span class="status-badge active">Active</span>
                                </div>
                                <h4 class="feature-title">Sandbox Testing</h4>
                                <p class="feature-desc">Provides toggle-based options to route requests to the live NHA Sandbox Dev Gateway or execute locally in simulation mode.</p>
                            </div>
                            <div class="feature-tech">
                                <div class="tech-row"><span class="tech-label">Sandbox Mode:</span><span class="tech-val">REAL API / SIMULATE</span></div>
                                <div class="tech-row"><span class="tech-label">Database Mode:</span><span class="tech-val">SQLite Sandbox</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
