<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Registration Status – ABDM NHPR Portal</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* ─────────────────────────────────────────
           NHPR Design System – Tokens
        ───────────────────────────────────────── */
        :root {
            --navy:          #071221;
            --navy-2:        #0a1628;
            --navy-3:        #0e1e32;
            --navy-4:        #122040;

            --saffron:       #e65100;
            --saffron-mid:   #f57c00;
            --saffron-light: #ff8f00;
            --gold:          #f9a825;

            --primary:       #1565c0;
            --primary-dark:  #003580;
            --primary-light: rgba(21,101,192,0.15);

            --success:       #2e7d32;
            --success-light: rgba(46,125,50,0.15);
            --warning:       #f57c00;
            --warning-light: rgba(245,124,0,0.15);
            --danger:        #c62828;
            --danger-light:  rgba(198,40,40,0.15);

            --text:          #e8f0fe;
            --text-sec:      #b0ccdc;
            --text-muted:    #7b9bbf;
            --text-light:    #5a7a8e;

            --border:        rgba(255,255,255,0.08);
            --border-light:  rgba(255,255,255,0.13);

            --r-sm:  6px;
            --r-md:  10px;
            --r-lg:  14px;
            --r-xl:  18px;
            --r-2xl: 24px;
            --t-base: 0.18s ease;
            --t-slow: 0.28s ease;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            background: var(--navy);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            overflow: hidden;
        }

        /* ─── Shell ─── */
        .app-shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ════════════════════════════════════════
           SIDEBAR
        ════════════════════════════════════════ */
        .hims-sidebar {
            width: 260px;
            min-width: 260px;
            background: var(--navy-2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow-y: auto;
            position: relative;
            z-index: 10;
        }

        .sidebar-brand {
            padding: 20px 18px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .sidebar-logo-wrap {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--saffron), var(--gold));
            border-radius: var(--r-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fff;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(230,81,0,0.3);
        }
        .sidebar-brand-text h1 {
            font-size: 13px; font-weight: 700;
            color: var(--text); letter-spacing: 0.3px;
        }
        .sidebar-brand-text p {
            font-size: 10px; color: var(--text-muted);
            margin-top: 1px;
            font-family: 'Noto Sans Devanagari', sans-serif;
        }

        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 18px;
            color: var(--text-muted);
            font-size: 13px; font-weight: 500;
            border-left: 3px solid transparent;
            cursor: pointer;
            transition: all var(--t-base);
            user-select: none;
            text-decoration: none;
        }
        .nav-item:hover { color: var(--text); background: rgba(255,255,255,0.04); }
        .nav-item.active {
            color: #fff;
            background: rgba(21,101,192,0.18);
            border-left-color: var(--primary);
        }
        .nav-item.active .nav-icon-wrap { background: var(--primary-light); color: #64b5f6; }
        .nav-icon-wrap {
            width: 30px; height: 30px; border-radius: var(--r-sm);
            background: rgba(255,255,255,0.05);
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; color: var(--text-muted);
            flex-shrink: 0; transition: all var(--t-base);
        }
        .nav-item:hover .nav-icon-wrap { background: rgba(255,255,255,0.08); color: var(--text-sec); }
        .nav-item-label { flex: 1; }

        .sidebar-footer {
            margin-top: auto; padding: 16px 18px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }
        .env-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 10px;
            background: var(--warning-light);
            border: 1px solid rgba(245,124,0,0.3);
            border-radius: 999px;
            font-size: 10px; font-weight: 600;
            color: var(--saffron-light); letter-spacing: 0.5px;
        }
        .env-badge .dot {
            width: 6px; height: 6px; background: var(--saffron-light);
            border-radius: 50%; animation: pulse-dot 1.8s ease-in-out infinite;
        }

        /* ════════════════════════════════════════
           MAIN AREA
        ════════════════════════════════════════ */
        .hims-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            min-width: 0;
        }

        .gov-topbar {
            background: var(--navy-2);
            border-bottom: 3px solid var(--saffron);
            padding: 0 28px;
            height: 52px; min-height: 52px;
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
            z-index: 5;
        }
        .gov-topbar-left { display: flex; align-items: center; gap: 12px; }
        .gov-emblem {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, var(--gold), var(--saffron));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; color: #fff;
        }
        .gov-title { font-size: 12px; font-weight: 600; color: var(--text-sec); }
        .gov-title span {
            font-family: 'Noto Sans Devanagari', sans-serif;
            font-size: 10.5px; color: var(--text-muted); display: block;
        }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .topbar-pill {
            display: flex; align-items: center; gap: 5px;
            font-size: 11px; font-weight: 500;
            padding: 3px 10px; border-radius: 999px;
            background: var(--success-light);
            color: #4caf50; border: 1px solid rgba(76,175,80,0.2);
        }
        .topbar-pill .dot { width: 6px; height: 6px; background: #4caf50; border-radius: 50%; animation: pulse-dot 2s ease-in-out infinite; }
        .live-clock { font-size: 12px; font-weight: 500; color: var(--text-muted); font-family: 'JetBrains Mono', monospace; }

        .hims-body {
            flex: 1;
            overflow-y: auto;
            padding: 28px;
            position: relative;
        }

        .page-header { margin-bottom: 24px; }
        .breadcrumb {
            display: flex; align-items: center; gap: 6px;
            font-size: 11px; color: var(--text-muted); margin-bottom: 8px;
        }
        .breadcrumb span { color: var(--text-light); }
        .page-title {
            font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -0.3px;
            display: flex; align-items: center; gap: 10px;
        }
        .page-title .title-icon {
            width: 36px; height: 36px; border-radius: var(--r-md);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .page-title .title-icon.blue { background: var(--primary-light); color: #64b5f6; }
        .page-subtitle { font-size: 13px; color: var(--text-muted); margin-top: 4px; margin-left: 46px; }

        /* ─── Search Box Layout ─── */
        .search-container {
            max-width: 680px;
            margin-bottom: 28px;
        }
        .card {
            background: var(--navy-2);
            border: 1px solid var(--border);
            border-radius: var(--r-xl);
            overflow: hidden;
        }
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-title { font-size: 13px; font-weight: 700; color: var(--text); }
        .card-subtitle { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
        .card-body { padding: 20px; }

        .search-bar-wrap {
            display: flex;
            gap: 12px;
        }
        .form-control {
            flex: 1; padding: 12px 16px;
            background: var(--navy-3); border: 1px solid var(--border-light);
            border-radius: var(--r-md); color: var(--text);
            font-size: 14px; font-family: 'Inter', sans-serif;
            transition: border-color var(--t-base), box-shadow var(--t-base); outline: none;
        }
        .form-control::placeholder { color: var(--text-light); }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(21,101,192,0.15); }
        .form-control.font-mono { font-family: 'JetBrains Mono', monospace; font-size: 13px; letter-spacing: 0.5px; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px 24px; border-radius: var(--r-md);
            font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif;
            border: none; cursor: pointer; transition: all var(--t-base);
        }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background: linear-gradient(135deg, var(--saffron), var(--saffron-mid)); color: #fff; box-shadow: 0 4px 14px rgba(230,81,0,0.25); }
        .btn-primary:hover:not(:disabled) { background: linear-gradient(135deg, var(--saffron-mid), var(--saffron-light)); }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .status-pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 4px 12px; border-radius: 999px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
        }
        .status-pill.approved { background: var(--success-light); color: #81c784; border: 1px solid rgba(76,175,80,0.25); }
        .status-pill.review   { background: var(--primary-light); color: #64b5f6; border: 1px solid rgba(21,101,192,0.25); }
        .status-pill.issues   { background: var(--danger-light); color: #ef9a9a; border: 1px solid rgba(198,40,40,0.25); }
        .status-pill .dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

        /* ─── Timeline Steps ─── */
        .timeline {
            display: flex;
            flex-direction: column;
            position: relative;
            padding-left: 36px;
            margin-top: 10px;
        }
        .timeline::before {
            content: '';
            position: absolute; top: 8px; bottom: 8px; left: 13px;
            width: 2px; background: var(--border-light);
        }
        .timeline-step {
            position: relative;
            margin-bottom: 32px;
        }
        .timeline-step:last-child { margin-bottom: 0; }
        .timeline-marker {
            position: absolute; left: -36px; top: 2px;
            width: 28px; height: 28px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            background: var(--navy-3); border: 2px solid var(--border-light);
            color: var(--text-light);
            z-index: 2; transition: all var(--t-base);
        }
        .timeline-step.completed .timeline-marker {
            background: var(--success); border-color: var(--success); color: #fff;
            box-shadow: 0 0 10px rgba(46,125,50,0.3);
        }
        .timeline-step.processing .timeline-marker {
            background: var(--primary); border-color: var(--primary); color: #fff;
            box-shadow: 0 0 10px rgba(21,101,192,0.3);
        }
        .timeline-step.failed .timeline-marker {
            background: var(--danger); border-color: var(--danger); color: #fff;
            box-shadow: 0 0 10px rgba(198,40,40,0.3);
        }
        .timeline-content {
            background: var(--navy-3);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 14px 18px;
        }
        .timeline-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 4px;
        }
        .timeline-title { font-size: 13px; font-weight: 700; color: var(--text); }
        .timeline-time { font-size: 11px; color: var(--text-light); font-family: 'JetBrains Mono', monospace; }
        .timeline-desc { font-size: 12.5px; color: var(--text-sec); }

        .alert-bar {
            display: flex; gap: 12px; padding: 14px 18px;
            border-radius: var(--r-lg); font-size: 13px; margin-bottom: 24px;
            align-items: flex-start;
        }
        .alert-bar.issues { background: var(--danger-light); border: 1px solid rgba(198,40,40,0.25); color: #ef9a9a; }
        .alert-bar.review { background: var(--primary-light); border: 1px solid rgba(21,101,192,0.25); color: #90caf9; }
        .alert-bar.approved { background: var(--success-light); border: 1px solid rgba(46,125,50,0.25); color: #81c784; }
        .alert-bar i { font-size: 16px; margin-top: 2px; }

        .spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.25); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; }

        @keyframes pulse-dot { 0%,100%{opacity:1}50%{opacity:.4} }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>

<body>
<div class="app-shell">

    <!-- ════════════════════════════════════════
         SIDEBAR
    ════════════════════════════════════════ -->
    <aside class="hims-sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-logo-wrap"><i class="fas fa-hospital"></i></div>
            <div class="sidebar-brand-text">
                <h1>NHPR Portal</h1>
                <p>राष्ट्रीय स्वास्थ्य व्यावसायिक रजिस्ट्री</p>
            </div>
        </div>

        <a href="{{ route('nhpr.register.wizard') }}" class="nav-item">
            <div class="nav-icon-wrap"><i class="fas fa-user-plus"></i></div>
            <span class="nav-item-label">HPR Onboarding</span>
        </a>

        <a href="{{ route('nhpr.token.show') }}" class="nav-item">
            <div class="nav-icon-wrap"><i class="fas fa-key"></i></div>
            <span class="nav-item-label">Gateway Token</span>
        </a>

        <a href="{{ route('nhpr.track.show') }}" class="nav-item active">
            <div class="nav-icon-wrap"><i class="fas fa-binoculars"></i></div>
            <span class="nav-item-label">Track Status</span>
        </a>

        <div class="sidebar-footer">
            <div class="env-badge">
                <span class="dot"></span>
                Sandbox Environment
            </div>
            <div style="font-size:10px;color:var(--text-light);margin-top:8px;">ABDM Integration v1.0 &bull; &copy; {{ date('Y') }}</div>
        </div>
    </aside>

    <!-- ════════════════════════════════════════
         MAIN AREA
    ════════════════════════════════════════ -->
    <main class="hims-main">
        <div class="gov-topbar">
            <div class="gov-topbar-left">
                <div class="gov-emblem"><i class="fas fa-dharmachakra"></i></div>
                <div class="gov-title">
                    National Health Authority — ABDM Gateway
                    <span>Government of India &bull; राष्ट्रीय स्वास्थ्य प्राधिकरण</span>
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-pill">
                    <span class="dot"></span> API Online
                </div>
                <div class="live-clock" id="live-clock"></div>
            </div>
        </div>

        <div class="hims-body">
            <div class="page-header">
                <div class="breadcrumb">
                    <span>NHPR</span>
                    <span><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
                    <span style="color:var(--text-sec);">Track Status</span>
                </div>
                <div class="page-title">
                    <div class="title-icon blue"><i class="fas fa-binoculars"></i></div>
                    Track Application Status
                </div>
                <p class="page-subtitle">Monitor the live status and verification progress of your HPR (Healthcare Professional Registry) onboarding application.</p>
            </div>

            <!-- Search Form Card -->
            <div class="search-container">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Enter Details</div>
                            <div class="card-subtitle">Search using your application Reference Number or HPR ID</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="track-form" onsubmit="performTracking(event)">
                            <div class="search-bar-wrap">
                                <input type="text" id="inp-ref" class="form-control font-mono" 
                                    placeholder="e.g. REF-XXXXXXXXXX or your HPR ID" 
                                    autocomplete="off" spellcheck="false" required>
                                <button type="submit" class="btn btn-primary" id="btn-submit">
                                    <i class="fas fa-search"></i> Track Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tracking Result Section -->
            <div id="result-wrap" style="display:none; max-width:680px;">
                <!-- Overall Status Banner -->
                <div id="status-banner" class="alert-bar"></div>

                <!-- Steps Timeline Card -->
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="card-title">Verification Timeline</div>
                            <div class="card-subtitle">Detailed status of verification steps</div>
                        </div>
                        <div id="badge-wrap"></div>
                    </div>
                    <div class="card-body">
                        <div class="timeline" id="timeline-list"></div>
                    </div>
                </div>
            </div>

            <!-- Empty State / Loading -->
            <div id="placeholder-wrap" style="max-width:680px; text-align:center; padding:64px 20px;">
                <div style="display:flex; flex-direction:column; align-items:center; gap:14px;">
                    <div style="width:64px; height:64px; background:var(--navy-2); border:1px solid var(--border); border-radius:var(--r-xl); display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-clipboard-list" style="font-size:24px; color:var(--text-light);" id="placeholder-icon"></i>
                    </div>
                    <div>
                        <div style="font-size:15px; font-weight:600; color:var(--text-sec);" id="placeholder-title">No Application Tracked</div>
                        <div style="font-size:12.5px; color:var(--text-muted); margin-top:4px;" id="placeholder-desc">Enter your application reference number above to view its live status.</div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    /* ─── Live Clock ─── */
    (function updateClock() {
        const el = document.getElementById('live-clock');
        if (el) el.textContent = new Date().toLocaleTimeString('en-IN', { hour12: false });
        setTimeout(updateClock, 1000);
    })();

    /* ─── Perform Tracking ─── */
    function performTracking(e) {
        e.preventDefault();
        const ref = document.getElementById('inp-ref').value.trim();
        if (!ref) return;

        const btn = document.getElementById('btn-submit');
        const placeholderWrap = document.getElementById('placeholder-wrap');
        const placeholderTitle = document.getElementById('placeholder-title');
        const placeholderDesc = document.getElementById('placeholder-desc');
        const placeholderIcon = document.getElementById('placeholder-icon');
        const resultWrap = document.getElementById('result-wrap');

        // Show loading state
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Searching…';
        placeholderIcon.className = 'fas fa-spinner fa-spin';
        placeholderIcon.style.color = 'var(--saffron-light)';
        placeholderTitle.textContent = 'Retrieving status details…';
        placeholderDesc.textContent = 'Please wait while we connect to the ABDM Registry index.';
        resultWrap.style.display = 'none';

        fetch("{{ route('nhpr.track.post') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ reference_number: ref })
        })
        .then(async r => {
            const res = await r.json();
            if (!r.ok) throw new Error(res.message || 'Tracking failed.');
            return res;
        })
        .then(data => {
            placeholderWrap.style.display = 'none';
            resultWrap.style.display = 'block';
            renderStatusResult(data);
        })
        .catch(err => {
            // Restore placeholder to error state
            placeholderIcon.className = 'fas fa-circle-exclamation';
            placeholderIcon.style.color = 'var(--danger)';
            placeholderTitle.textContent = 'Unable to Find Application';
            placeholderDesc.textContent = err.message || 'Failed to track the status. Check reference number and try again.';
            placeholderWrap.style.display = 'block';
            resultWrap.style.display = 'none';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search"></i> Track Application';
        });
    }

    function renderStatusResult(data) {
        const banner = document.getElementById('status-banner');
        const badgeWrap = document.getElementById('badge-wrap');
        const timelineList = document.getElementById('timeline-list');

        // Clear previous list
        timelineList.innerHTML = '';

        // Configure class tags
        const classMap = {
            APPROVED: 'approved',
            REVIEW: 'review',
            ISSUES: 'issues'
        };
        const activeClass = classMap[data.status] || 'review';

        // 1. Status Banner configuration
        const icons = {
            APPROVED: 'fa-circle-check',
            REVIEW: 'fa-clock-rotate-left',
            ISSUES: 'fa-triangle-exclamation'
        };
        const bannerIcon = icons[data.status] || 'fa-info-circle';
        banner.className = `alert-bar ${activeClass}`;
        banner.innerHTML = `<i class="fas ${bannerIcon}"></i> <div><strong>Status:</strong> ${data.message}</div>`;

        // 2. Status Badge configuration
        const textLabels = {
            APPROVED: 'Active & Verified',
            REVIEW: 'Under Verification',
            ISSUES: 'Action Required'
        };
        badgeWrap.innerHTML = `
            <span class="status-pill ${activeClass}">
                <span class="dot"></span> ${textLabels[data.status] || data.status}
            </span>`;

        // 3. Step timeline rendering
        data.steps.forEach((step, index) => {
            const stepEl = document.createElement('div');
            // completed, processing, failed, pending
            const stepClass = step.status.toLowerCase();
            stepEl.className = `timeline-step ${stepClass}`;

            let markerContent = index + 1;
            if (step.status === 'COMPLETED') markerContent = '<i class="fas fa-check" style="font-size:10px;"></i>';
            if (step.status === 'FAILED') markerContent = '<i class="fas fa-exclamation" style="font-size:10px;"></i>';
            if (step.status === 'PROCESSING') markerContent = '<i class="fas fa-spinner fa-spin" style="font-size:10px;"></i>';

            stepEl.innerHTML = `
                <div class="timeline-marker">${markerContent}</div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <span class="timeline-title">${step.name}</span>
                        <span class="timeline-time">${step.updated_at || '—'}</span>
                    </div>
                    <div class="timeline-desc">${step.desc}</div>
                </div>`;
            timelineList.appendChild(stepEl);
        });
    }
</script>
</body>
</html>
