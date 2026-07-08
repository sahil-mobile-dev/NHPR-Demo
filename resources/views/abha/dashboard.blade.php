<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ABHA Sandbox Milestone 1 Dashboard | UK HIMS</title>
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

        /* Cards and grid styling */
        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 10px;
        }

        @media (max-width: 992px) {
            .grid-3 {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            border-color: var(--border2);
        }

        .card-icon {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            background: var(--surface2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: var(--primary-light);
        }

        .card.card-orange .card-icon {
            color: var(--warning-light);
        }

        .card.card-green .card-icon {
            color: var(--success-light);
        }

        .card-title {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }

        .card-desc {
            font-size: 12.5px;
            color: var(--muted);
            line-height: 1.5;
            flex: 1;
        }

        .btn-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--primary-light);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: gap 0.2s ease;
            width: fit-content;
        }

        .btn-link:hover {
            gap: 10px;
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
                <a href="{{ route('abha.dashboard') }}" class="nav-item active">
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
                    <span id="gateway-mode-status-text" style="color: var(--muted2);">Mode: {{ $config['realApiMode'] ? 'REAL API' : 'SIMULATION' }}</span>
                </div>
            </div>

            <!-- Scrollable Content Body -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">ABHA Milestone 1 Sandbox Portal</h1>
                    <p class="page-subtitle">Generate unique health IDs (ABHA Numbers) for citizens, search existing profiles, and verify ABHA addresses securely.</p>
                </div>

                <!-- API Toggle Toolbar -->
                <div style="background: var(--surface); border: 1px solid var(--border); padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 13px; font-weight: 700; color: #fff;">Offline Sandbox Simulation</span>
                        <p style="font-size: 11.5px; color: var(--muted);">Bypass live gateway integrations to perform fast local tests.</p>
                    </div>
                    <div>
                        <label class="switch-toggle">
                            <input type="checkbox" id="mode-toggle-checkbox" {{ !$config['realApiMode'] ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>

                <!-- Grid Options -->
                <div class="grid-3">
                    <!-- Create ABHA Card -->
                    <div class="card" style="border-top: 3px solid var(--primary);">
                        <div class="card-icon"><i class="fa-solid fa-user-plus"></i></div>
                        <h2 class="card-title">Create ABHA Number</h2>
                        <p class="card-desc">Register a new citizen and generate a unique 14-digit Ayushman Bharat Health Account (ABHA) number. Synchronize demography details verified via Aadhaar OTP validation.</p>
                        <a href="{{ route('abha.create') }}" class="btn-link">Get Started <i class="fa-solid fa-arrow-right-long"></i></a>
                    </div>

                    <!-- Find Existing ABHA Card -->
                    <div class="card card-green" style="border-top: 3px solid var(--success);">
                        <div class="card-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <h2 class="card-title">Find Existing ABHA</h2>
                        <p class="card-desc">Search for matching patient records in ABDM systems using their registered mobile identifier. Retrieve their unique 14-digit ABHA Number and digital health credentials.</p>
                        <a href="{{ route('abha.find') }}" class="btn-link">Find ABHA <i class="fa-solid fa-arrow-right-long"></i></a>
                    </div>

                    <!-- Verify ABHA Card -->
                    <div class="card card-orange" style="border-top: 3px solid var(--warning);">
                        <div class="card-icon"><i class="fa-solid fa-address-card"></i></div>
                        <h2 class="card-title">Verify ABHA Address</h2>
                        <p class="card-desc">Search for custom health addresses (e.g. name@sbx) and confirm ownership using mobile/Aadhaar OTP, QR decoding, or demographic match validation.</p>
                        <a href="{{ route('abha.verify') }}" class="btn-link">Verify Address <i class="fa-solid fa-arrow-right-long"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toggle Script -->
    <script>
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
                    const statusText = document.getElementById('gateway-mode-status-text');
                    const isReal = data.real_api_mode;
                    statusText.innerText = "Mode: " + (isReal ? 'REAL API' : 'SIMULATION');
                    
                    // Show confirmation feedback
                    alert("Gateway mode switched to: " + (isReal ? 'REAL API' : 'SIMULATION'));
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
