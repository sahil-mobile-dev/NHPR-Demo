<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Find Existing ABHA – ABDM Milestone 1</title>
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
            color: var(--primary-light);
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
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.15);
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

        .btn-action {
            background: var(--primary);
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
            background: #1976d2;
            box-shadow: 0 4px 12px rgba(21, 101, 192, 0.3);
        }

        .btn-action:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Profile Selection Grid */
        .profile-list {
            margin-top: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .profile-item {
            background: var(--surface2);
            border: 1px solid var(--border);
            padding: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .profile-item:hover {
            border-color: var(--primary);
            background: rgba(21, 101, 192, 0.05);
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
            text-align: left;
        }

        .profile-name {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }

        .profile-abha {
            font-size: 11.5px;
            font-weight: 700;
            color: var(--primary-light);
            letter-spacing: 0.5px;
        }

        .profile-meta {
            font-size: 11px;
            color: var(--muted);
        }

        .btn-select {
            background: var(--primary);
            border: none;
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 600;
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
            background: linear-gradient(135deg, #0e2a4a, #07162b);
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
            color: var(--primary-light);
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
                <a href="{{ route('abha.find') }}" class="nav-item active">
                    <i class="fa-solid fa-magnifying-glass"></i> Find Existing ABHA
                </a>
                <a href="{{ route('abha.verify') }}" class="nav-item">
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
                    <h1 class="page-title">Find Existing ABHA</h1>
                    <p class="page-subtitle">Search database using registered mobile identifiers to retrieve existing ABHA profiles</p>
                </div>

                <!-- Panel 1: Search Form -->
                <div class="form-panel active" id="panel-search">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fa-solid fa-phone"></i> Search by Mobile Number</h2>
                        <p class="panel-subtitle">Enter the patient's registered 10-digit mobile number.</p>
                    </div>
                    <form id="form-search">
                        <div class="form-group">
                            <label class="form-label" for="mobile">Mobile Number <span class="req">*</span></label>
                            <input type="text" id="mobile" class="form-control" placeholder="10-digit mobile number" maxlength="10" pattern="\d{10}" required>
                            <div class="form-error" id="error-mobile">Please enter a valid 10-digit mobile number.</div>
                        </div>
                        <button type="submit" class="btn-action" id="btn-search">
                            Find Accounts <i class="fa-solid fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Panel 2: Select Profile Matches -->
                <div class="form-panel" id="panel-select">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fa-solid fa-users"></i> Matched ABHA Accounts</h2>
                        <p class="panel-subtitle">Select the profile corresponding to the patient to retrieve their digital details.</p>
                    </div>
                    <div class="profile-list" id="profile-matches-container">
                        <!-- Dynamic list of matched profiles -->
                    </div>
                </div>

                <!-- Panel 3: Success Screen -->
                <div class="form-panel" id="panel-success">
                    <div class="panel-header" style="text-align: center;">
                        <h2 class="panel-title" style="justify-content: center; color: var(--success-light);">
                            <i class="fa-solid fa-circle-check"></i> Profile Retrieved!
                        </h2>
                        <p class="panel-subtitle">Patient details have been retrieved and logged successfully.</p>
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
                                <div class="card-photo-box">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div class="card-details">
                                    <div class="card-name" id="card-name-val">Amit Shah</div>
                                    <div class="card-number-label">ABHA Number</div>
                                    <div class="card-number-value" id="card-number-val">00-0000-0000-0000</div>
                                </div>
                            </div>

                            <div class="card-meta-grid">
                                <div class="card-meta-item">
                                    <span>Gender:</span> <strong id="card-gender-val">M</strong>
                                </div>
                                <div class="card-meta-item">
                                    <span>DOB:</span> <strong id="card-dob-val">1988-08-15</strong>
                                </div>
                            </div>

                            <div class="card-meta-grid" style="margin-top: 4px;">
                                <div class="card-meta-item">
                                    <span>Address:</span> <strong id="card-address-val">username@sbx</strong>
                                </div>
                                <div class="card-meta-item">
                                    <span>Token:</span> <strong style="color: var(--success-light);" id="card-token-val">GENERATED</strong>
                                </div>
                            </div>

                            <div class="card-footer-ndhm">
                                <span>Ayushman Bharat Digital Mission</span>
                                <span class="ndhm-text">ABHA</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 24px;">
                        <a href="{{ route('abha.dashboard') }}" class="btn-action" style="background: var(--surface2); border: 1px solid var(--border); text-decoration: none;">
                            <i class="fa-solid fa-arrow-left-long"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Actions -->
    <script>
        const formSearch = document.getElementById('form-search');
        const mobileInput = document.getElementById('mobile');
        
        const panelSearch = document.getElementById('panel-search');
        const panelSelect = document.getElementById('panel-select');
        const panelSuccess = document.getElementById('panel-success');
        
        // Handle Search
        formSearch.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const val = mobileInput.value.trim();
            if (val.length !== 10 || isNaN(val)) {
                document.getElementById('error-mobile').style.display = 'block';
                mobileInput.classList.add('error');
                return;
            }
            document.getElementById('error-mobile').style.display = 'none';
            mobileInput.classList.remove('error');
            
            const btn = document.getElementById('btn-search');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Searching...';
            
            fetch("{{ route('abha.find.search-mobile') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ mobile: val })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Find Accounts <i class="fa-solid fa-search"></i>';
                
                if (data.success) {
                    const container = document.getElementById('profile-matches-container');
                    container.innerHTML = '';
                    
                    if (data.profiles.length === 0) {
                        container.innerHTML = '<p style="color: var(--muted); text-align: center; padding: 20px;">No matching ABHA profiles found for this mobile number.</p>';
                    } else {
                        data.profiles.forEach(p => {
                            container.innerHTML += `
                                <div class="profile-item" onclick="selectProfile('${p.abhaNumber}', '${p.abhaAddress}', '${p.name}', '${p.gender}', '${p.dob}')">
                                    <div class="profile-info">
                                        <div class="profile-name">${p.name}</div>
                                        <div class="profile-abha">ABHA: ${p.abhaNumber}</div>
                                        <div class="profile-meta">Address: ${p.abhaAddress} &bull; Gender: ${p.gender} &bull; DOB: ${p.dob}</div>
                                    </div>
                                    <button class="btn-select">Select Profile</button>
                                </div>
                            `;
                        });
                    }
                    
                    // Transition panel
                    panelSearch.classList.remove('active');
                    panelSelect.classList.add('active');
                } else {
                    alert(data.message || "Search failed.");
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Find Accounts <i class="fa-solid fa-search"></i>';
                alert("Search failed. Connection error.");
            });
        });

        // Handle Profile Selection
        function selectProfile(abhaNumber, abhaAddress, name, gender, dob) {
            fetch("{{ route('abha.find.select') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    abha_number: abhaNumber,
                    abha_address: abhaAddress,
                    name: name,
                    gender: gender,
                    dob: dob
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const p = data.profile;
                    document.getElementById('card-name-val').innerText = p.name;
                    document.getElementById('card-number-val').innerText = p.abhaNumber;
                    document.getElementById('card-gender-val').innerText = p.gender;
                    document.getElementById('card-dob-val').innerText = p.dob;
                    document.getElementById('card-address-val').innerText = p.abhaAddress;
                    document.getElementById('card-token-val').innerText = p.linkingToken.substring(0, 8) + '...';
                    
                    // Show success panel
                    panelSelect.classList.remove('active');
                    panelSuccess.classList.add('active');
                } else {
                    alert(data.message || "Selection failed.");
                }
            })
            .catch(err => {
                alert("Profile selection failed. Connection error.");
            });
        }
    </script>
</body>

</html>
