<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NHPR Portal – ABDM Integration</title>

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
            overflow: hidden; /* prevent double scrollbars */
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

        /* ─── Nav ─── */
        .nav-section-label {
            padding: 20px 18px 6px;
            font-size: 10px; font-weight: 700;
            letter-spacing: 1.2px; text-transform: uppercase;
            color: var(--text-light);
            flex-shrink: 0;
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
        .nav-badge {
            font-size: 9px; font-weight: 700; letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 2px 7px; border-radius: 999px;
        }
        .nav-badge.live { background: var(--success-light); color: #81c784; border: 1px solid rgba(76,175,80,0.2); }
        .nav-badge.new  { background: var(--primary-light); color: #64b5f6; border: 1px solid rgba(21,101,192,0.25); }

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

        /* ─── Gov Topbar ─── */
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

        /* ─── Content Area (scrollable) ─── */
        .hims-body {
            flex: 1;
            overflow-y: auto;
            padding: 0;
            position: relative;
        }

        /* ════════════════════════════════════════
           PANELS (content switching)
        ════════════════════════════════════════ */
        .panel { display: none; padding: 28px; height: 100%; animation: fadeIn 0.2s ease; }
        .panel.active { display: block; }

        @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:translateY(0); } }
        @keyframes pulse-dot { 0%,100%{opacity:1}50%{opacity:.4} }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes toast-shrink { from{width:100%} to{width:0} }

        /* ─── Page Header ─── */
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
        .page-title .title-icon.saffron { background: rgba(230,81,0,0.15); color: var(--saffron-light); }
        .page-title .title-icon.blue    { background: var(--primary-light); color: #64b5f6; }
        .page-title .title-icon.teal    { background: rgba(0,105,92,0.15); color: #4db6ac; }
        .page-subtitle { font-size: 13px; color: var(--text-muted); margin-top: 4px; margin-left: 46px; }

        /* ─── Grid Layouts ─── */
        .token-grid {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 24px;
            align-items: start;
        }
        .registry-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        /* ─── Cards ─── */
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
        .card-header-title { display: flex; align-items: center; gap: 10px; }
        .card-icon {
            width: 32px; height: 32px; border-radius: var(--r-md);
            display: flex; align-items: center; justify-content: center; font-size: 14px;
        }
        .card-icon.saffron { background: rgba(230,81,0,0.15); color: var(--saffron-light); }
        .card-icon.blue    { background: var(--primary-light); color: #64b5f6; }
        .card-icon.green   { background: var(--success-light); color: #81c784; }
        .card-icon.gold    { background: rgba(249,168,37,0.15); color: var(--gold); }
        .card-icon.teal    { background: rgba(0,105,92,0.15); color: #4db6ac; }
        .card-title { font-size: 13px; font-weight: 700; color: var(--text); }
        .card-subtitle { font-size: 11px; color: var(--text-muted); margin-top: 1px; }
        .card-body { padding: 20px; }

        /* ─── Stat Cards ─── */
        .stat-card {
            background: var(--navy-3);
            border: 1px solid var(--border);
            border-radius: var(--r-lg);
            padding: 18px 20px;
            position: relative; overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
        }
        .stat-card.blue::before   { background: var(--primary); }
        .stat-card.green::before  { background: var(--success); }
        .stat-card.saffron::before{ background: var(--saffron); }
        .stat-label { font-size: 10px; font-weight: 600; letter-spacing: 0.8px; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 6px; }
        .stat-value { font-size: 28px; font-weight: 800; color: var(--text); font-family: 'JetBrains Mono', monospace; display: block; }
        .stat-sub { font-size: 11px; color: var(--text-muted); margin-top: 4px; }

        /* ─── Token Stats Row ─── */
        .token-stat-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; }
        .token-stat {
            background: var(--navy-3); border: 1px solid var(--border);
            border-radius: var(--r-lg); padding: 14px 16px;
        }
        .token-stat-label { font-size: 10px; font-weight: 600; letter-spacing: 0.8px; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 5px; }
        .token-stat-value { font-size: 16px; font-weight: 700; color: var(--text); font-family: 'JetBrains Mono', monospace; }
        .token-stat-value.success { color: #81c784; }
        .token-stat-value.warning { color: #ffb74d; }
        .token-stat-value.danger  { color: #ef9a9a; }

        /* ─── Status Pills ─── */
        .status-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 999px;
            font-size: 10px; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase;
        }
        .status-pill.configured { background: var(--success-light); border: 1px solid rgba(76,175,80,0.25); color: #81c784; }
        .status-pill.missing    { background: var(--warning-light); border: 1px solid rgba(245,124,0,0.3); color: var(--saffron-light); }
        .status-pill.token-active { background: var(--success-light); border: 1px solid rgba(76,175,80,0.25); color: #81c784; }
        .status-pill.token-none { background: rgba(255,255,255,0.05); border: 1px solid var(--border-light); color: var(--text-muted); }
        .status-pill .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }

        /* ─── Forms ─── */
        .form-group { margin-bottom: 16px; }
        .form-group:last-child { margin-bottom: 0; }
        .form-label {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 11px; font-weight: 600; color: var(--text-sec);
            letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 6px;
        }
        .form-label .req { color: var(--danger); margin-left: 2px; }
        .form-label .hint { font-size: 10px; font-weight: 500; color: var(--text-muted); text-transform: none; letter-spacing: 0; }
        .form-control {
            width: 100%; padding: 10px 14px;
            background: var(--navy-3); border: 1px solid var(--border-light);
            border-radius: var(--r-md); color: var(--text);
            font-size: 13px; font-family: 'Inter', sans-serif;
            transition: border-color var(--t-base), box-shadow var(--t-base); outline: none;
        }
        .form-control::placeholder { color: var(--text-light); }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(21,101,192,0.15); }
        .form-control.font-mono { font-family: 'JetBrains Mono', monospace; font-size: 12px; }

        /* ─── Buttons ─── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 20px; border-radius: var(--r-md);
            font-size: 13px; font-weight: 600; font-family: 'Inter', sans-serif;
            border: none; cursor: pointer; transition: all var(--t-base);
            letter-spacing: 0.2px; text-decoration: none;
        }
        .btn:active { transform: scale(0.97); }
        .btn:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }
        .btn-primary { background: linear-gradient(135deg, var(--saffron), var(--saffron-mid)); color: #fff; box-shadow: 0 4px 14px rgba(230,81,0,0.25); }
        .btn-primary:hover:not(:disabled) { background: linear-gradient(135deg, var(--saffron-mid), var(--saffron-light)); box-shadow: 0 6px 20px rgba(230,81,0,0.35); }
        .btn-blue { background: linear-gradient(135deg, var(--primary), #1976d2); color: #fff; box-shadow: 0 4px 14px rgba(21,101,192,0.25); }
        .btn-blue:hover:not(:disabled) { background: linear-gradient(135deg, #1976d2, #1e88e5); }
        .btn-ghost { background: rgba(255,255,255,0.05); border: 1px solid var(--border-light); color: var(--text-muted); }
        .btn-ghost:hover:not(:disabled) { background: rgba(255,255,255,0.1); color: var(--text); }
        .btn-danger-ghost { background: var(--danger-light); border: 1px solid rgba(198,40,40,0.25); color: #ef9a9a; }
        .btn-danger-ghost:hover:not(:disabled) { background: rgba(198,40,40,0.2); color: #e57373; }
        .btn-full { width: 100%; }
        .btn-sm { padding: 7px 14px; font-size: 12px; }

        /* ─── Info / Warning Boxes ─── */
        .info-box {
            display: flex; gap: 10px; padding: 12px 14px;
            background: rgba(21,101,192,0.08); border: 1px solid rgba(21,101,192,0.2);
            border-radius: var(--r-md); font-size: 12px; color: var(--text-muted); line-height: 1.5;
        }
        .info-box i { color: #64b5f6; font-size: 13px; margin-top: 1px; flex-shrink: 0; }
        .warning-box {
            display: flex; gap: 10px; padding: 12px 14px;
            background: var(--warning-light); border: 1px solid rgba(245,124,0,0.25);
            border-radius: var(--r-md); font-size: 12px; color: #ffb74d; line-height: 1.5;
        }
        .warning-box i { color: var(--saffron-light); font-size: 13px; margin-top: 1px; flex-shrink: 0; }

        /* ─── Divider ─── */
        .divider { height: 1px; background: var(--border); margin: 18px 0; }

        /* ─── Section Label ─── */
        .section-label {
            font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase;
            color: var(--text-light); margin-bottom: 12px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-label::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        /* ─── Cred Row ─── */
        .cred-row { display: flex; align-items: center; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid var(--border); }
        .cred-row:last-child { border-bottom: none; padding-bottom: 0; }
        .cred-key { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; color: var(--text-muted); }
        .cred-val { font-size: 11px; font-family: 'JetBrains Mono', monospace; color: var(--text-sec); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; text-align: right; }

        /* ─── Token Box ─── */
        .token-box {
            background: var(--navy-3); border: 1px solid var(--border-light);
            border-radius: var(--r-lg); padding: 14px; min-height: 60px;
            font-family: 'JetBrains Mono', monospace; font-size: 11.5px;
            color: var(--text-sec); word-break: break-all; line-height: 1.7; position: relative;
        }
        .token-copy-btn {
            position: absolute; top: 10px; right: 10px;
            background: rgba(255,255,255,0.06); border: 1px solid var(--border);
            border-radius: var(--r-sm); color: var(--text-muted);
            width: 28px; height: 28px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: 11px; transition: all var(--t-base);
        }
        .token-copy-btn:hover { background: rgba(255,255,255,0.12); color: var(--text); }

        /* ─── JSON Inspector ─── */
        .json-inspector { background: var(--navy); border: 1px solid var(--border); border-radius: var(--r-md); overflow: hidden; }
        .json-inspector-header {
            padding: 10px 14px; background: var(--navy-3); border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            cursor: pointer; user-select: none;
        }
        .json-inspector-header span { font-size: 11px; font-weight: 600; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }
        .json-body { max-height: 220px; overflow-y: auto; padding: 14px; font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #90caf9; line-height: 1.8; white-space: pre-wrap; }

        /* ─── Registry Table ─── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            padding: 10px 14px; text-align: left;
            font-size: 10px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase;
            color: var(--text-muted); border-bottom: 1px solid var(--border); white-space: nowrap;
        }
        .data-table td { padding: 12px 14px; font-size: 12px; color: var(--text-sec); border-bottom: 1px solid var(--border); vertical-align: middle; }
        .data-table tr:last-child td { border-bottom: none; }
        .data-table tr:hover td { background: rgba(255,255,255,0.02); }
        .data-table .badge { padding: 2px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; }
        .badge-green { background: var(--success-light); color: #81c784; border: 1px solid rgba(76,175,80,0.2); }
        .badge-blue  { background: var(--primary-light); color: #64b5f6; border: 1px solid rgba(21,101,192,0.25); }
        .badge-gold  { background: rgba(249,168,37,0.15); color: var(--gold); border: 1px solid rgba(249,168,37,0.25); }

        /* ─── HPR Onboarding iframe ─── */
        .onboarding-frame-wrap {
            background: var(--navy-2); border: 1px solid var(--border);
            border-radius: var(--r-xl); overflow: hidden;
            height: calc(100vh - 180px);
        }
        .onboarding-frame-wrap iframe {
            width: 100%; height: 100%; border: none; display: block;
        }

        /* ─── Toast ─── */
        .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-width: 360px; width: 100%; }
        .toast { background: var(--navy-2); border: 1px solid var(--border-light); border-radius: var(--r-lg); overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.4); transform: translateX(110%); transition: transform var(--t-slow); }
        .toast.show { transform: translateX(0); }
        .toast-content { display: flex; align-items: flex-start; gap: 12px; padding: 14px 16px; }
        .toast-icon { font-size: 15px; flex-shrink: 0; margin-top: 1px; }
        .toast-icon.success { color: #81c784; }
        .toast-icon.error   { color: #ef9a9a; }
        .toast-icon.info    { color: #64b5f6; }
        .toast-msg { font-size: 12px; font-weight: 500; color: var(--text); flex: 1; line-height: 1.5; }
        .toast-close { color: var(--text-muted); cursor: pointer; padding: 2px; flex-shrink: 0; font-size: 12px; }
        .toast-close:hover { color: var(--text); }
        .toast-bar { height: 3px; background: var(--border); }
        .toast-progress { height: 100%; animation: toast-shrink 4.5s linear forwards; }
        .toast-progress.success { background: #81c784; }
        .toast-progress.error   { background: #ef9a9a; }
        .toast-progress.info    { background: #64b5f6; }

        /* ─── Spinner ─── */
        .spinner { width: 15px; height: 15px; border: 2px solid rgba(255,255,255,0.25); border-top-color: #fff; border-radius: 50%; animation: spin 0.7s linear infinite; display: inline-block; }

        /* ─── Responsive ─── */
        @media (max-width: 1100px) { .token-grid { grid-template-columns: 1fr; } }
        @media (max-width: 900px)  { .hims-sidebar { display: none; } }
        @media (max-width: 640px)  { .registry-grid { grid-template-columns: 1fr 1fr; } .hims-body { padding: 16px; } }
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

        <a href="{{ route('hip.dashboard') }}" class="nav-item">
            <div class="nav-icon-wrap"><i class="fas fa-notes-medical"></i></div>
            <span class="nav-item-label">HIP Dashboard</span>
        </a>
        <a href="{{ route('hip.milestone2') }}" class="nav-item">
            <div class="nav-icon-wrap"><i class="fas fa-map-location-dot"></i></div>
            <span class="nav-item-label">ABDM Milestone 2 Map</span>
        </a>
        <a href="{{ route('hiu.dashboard') }}" class="nav-item">
            <div class="nav-icon-wrap"><i class="fas fa-shield-halved"></i></div>
            <span class="nav-item-label">HIU Portal</span>
        </a>
        <a href="{{ route('abha.dashboard') }}" class="nav-item">
            <div class="nav-icon-wrap"><i class="fas fa-chart-line"></i></div>
            <span class="nav-item-label">ABHA Dashboard</span>
        </a>

        <a href="{{ route('nhpr.register.wizard') }}" class="nav-item">
            <div class="nav-icon-wrap"><i class="fas fa-user-plus"></i></div>
            <span class="nav-item-label">HPR Onboarding</span>
        </a>

        <a href="{{ route('nhpr.hfr.index') }}" class="nav-item">
            <div class="nav-icon-wrap"><i class="fas fa-building-circle-check"></i></div>
            <span class="nav-item-label">HFR Management</span>
        </a>

        <a href="{{ route('nhpr.token.show') }}" class="nav-item active">
            <div class="nav-icon-wrap"><i class="fas fa-key"></i></div>
            <span class="nav-item-label">Gateway Token</span>
        </a>

        <a href="{{ route('nhpr.track.show') }}" class="nav-item">
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

        <!-- Gov Topbar -->
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

        <!-- Scrollable body -->
        <div class="hims-body">

            <div class="panel active" id="panel-token">
                <div class="page-header">
                    <div class="breadcrumb">
                        <span>NHPR</span>
                        <span><i class="fas fa-chevron-right" style="font-size:9px;"></i></span>
                        <span style="color:var(--text-sec);">Gateway Token</span>
                    </div>
                    <div class="page-title">
                        <div class="title-icon saffron"><i class="fas fa-shield-halved"></i></div>
                        Gateway Session Token
                    </div>
                    <p class="page-subtitle">Configure ABDM credentials and generate the OAuth2 access token required for all NHPR API calls.</p>
                </div>

                <div class="token-grid">

                    <!-- Left: Credentials -->
                    <div style="display:flex;flex-direction:column;gap:18px;">

                        <div class="card">
                            <div class="card-header">
                                <div class="card-header-title">
                                    <div class="card-icon saffron"><i class="fas fa-lock"></i></div>
                                    <div>
                                        <div class="card-title">API Credentials</div>
                                        <div class="card-subtitle">Paste your ABDM sandbox credentials</div>
                                    </div>
                                </div>
                                <span class="status-pill {{ $config['isConfigured'] ? 'configured' : 'missing' }}" id="cred-status-pill">
                                    <span class="dot"></span>
                                    {{ $config['isConfigured'] ? 'Configured' : 'Missing' }}
                                </span>
                            </div>
                            <div class="card-body">
                                <form id="credentials-form" autocomplete="off">
                                    @csrf

                                    <div class="form-group">
                                        <div class="form-label">
                                            Client ID <span class="req">*</span>
                                            <span class="hint">From ABDM Developer Portal</span>
                                        </div>
                                        <input type="text" id="inp-client-id" class="form-control font-mono"
                                            placeholder="e.g. SBX_0020000123"
                                            value="{{ $config['clientId'] ?: '' }}"
                                            autocomplete="off" spellcheck="false">
                                    </div>

                                    <div class="form-group">
                                        <div class="form-label">
                                            Client Secret <span class="req">*</span>
                                            <span class="hint" style="cursor:pointer;" onclick="toggleSecret()">
                                                <i class="fas fa-eye" id="secret-toggle-icon"></i> Show
                                            </span>
                                        </div>
                                        <input type="password" id="inp-client-secret" class="form-control font-mono"
                                            placeholder="Paste your client secret here"
                                            autocomplete="new-password" spellcheck="false">
                                    </div>

                                    <div class="form-group">
                                        <div class="form-label">Gateway Base URL <span class="req">*</span></div>
                                        <input type="url" id="inp-base-url" class="form-control font-mono"
                                            placeholder="https://dev.abdm.gov.in"
                                            value="{{ $config['baseUrl'] }}"
                                            autocomplete="off" spellcheck="false">
                                    </div>

                                    <div class="form-group">
                                        <div class="form-label">API Base URL <span class="req">*</span></div>
                                        <input type="url" id="inp-api-url" class="form-control font-mono"
                                            placeholder="https://apihspsbx.abdm.gov.in"
                                            value="{{ $config['apiUrl'] }}"
                                            autocomplete="off" spellcheck="false">
                                    </div>

                                    <div class="form-group">
                                        <div class="form-label">
                                            X-CM-ID <span class="req">*</span>
                                            <span class="hint">Consent Manager ID</span>
                                        </div>
                                        <input type="text" id="inp-x-cm-id" class="form-control font-mono"
                                            placeholder="sbx"
                                            value="{{ $config['xCmId'] }}"
                                            autocomplete="off" spellcheck="false">
                                    </div>

                                    <div class="warning-box" style="margin-bottom:16px;">
                                        <i class="fas fa-triangle-exclamation"></i>
                                        <span>Credentials are stored in your session only — never persisted to disk or logged. Each new browser session requires re-entry.</span>
                                    </div>

                                    <div style="display:flex;gap:10px;">
                                        <button type="button" class="btn btn-blue btn-full" id="btn-save-creds" onclick="saveCredentials()">
                                            <i class="fas fa-floppy-disk"></i> Save Credentials
                                        </button>
                                        <button type="button" class="btn btn-danger-ghost btn-sm" id="btn-clear-creds"
                                            onclick="clearCredentials()" title="Clear saved credentials"
                                            {{ !$config['isConfigured'] ? 'disabled' : '' }}>
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </div>
                                </form>

                                <!-- Saved Preview -->
                                <div id="saved-preview" style="{{ $config['isConfigured'] ? '' : 'display:none;' }}">
                                    <div class="divider"></div>
                                    <div class="section-label">Saved in Session</div>
                                    <div class="cred-row">
                                        <span class="cred-key">Client ID</span>
                                        <span class="cred-val" id="prev-client-id">{{ $config['clientId'] ?: '—' }}</span>
                                    </div>
                                    <div class="cred-row">
                                        <span class="cred-key">Client Secret</span>
                                        <span class="cred-val">••••••••••••</span>
                                    </div>
                                    <div class="cred-row">
                                        <span class="cred-key">Gateway URL</span>
                                        <span class="cred-val" id="prev-base-url">{{ $config['baseUrl'] }}</span>
                                    </div>
                                    <div class="cred-row">
                                        <span class="cred-key">X-CM-ID</span>
                                        <span class="cred-val" id="prev-xcmid">{{ $config['xCmId'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Where to get credentials -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-header-title">
                                    <div class="card-icon gold"><i class="fas fa-circle-info"></i></div>
                                    <div><div class="card-title">Where to Get Credentials?</div></div>
                                </div>
                            </div>
                            <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
                                <div class="info-box">
                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                    <span>Register on the <strong style="color:var(--text);">ABDM Sandbox Portal</strong> at
                                    <a href="https://sandbox.abdm.gov.in" target="_blank" style="color:#64b5f6;">sandbox.abdm.gov.in</a>
                                    to obtain your Client ID and Secret.</span>
                                </div>
                                <div class="info-box">
                                    <i class="fas fa-list-check"></i>
                                    <span>Required: <strong style="color:var(--text);">clientId</strong>, <strong style="color:var(--text);">clientSecret</strong>, Gateway URL (<code style="color:#81c784;">dev.abdm.gov.in</code>), X-CM-ID (<code style="color:#81c784;">sbx</code>).</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Token Monitor -->
                    <div style="display:flex;flex-direction:column;gap:18px;">

                        <div class="card">
                            <div class="card-header">
                                <div class="card-header-title">
                                    <div class="card-icon blue"><i class="fas fa-bolt"></i></div>
                                    <div>
                                        <div class="card-title">Active Token Session</div>
                                        <div class="card-subtitle">OAuth2 Bearer token for ABDM API authentication</div>
                                    </div>
                                </div>
                                <span class="status-pill {{ $tokenData ? 'token-active' : 'token-none' }}" id="token-status-pill">
                                    <span class="dot"></span>
                                    {{ $tokenData ? 'Active & Cached' : 'No Session' }}
                                </span>
                            </div>
                            <div class="card-body" style="display:flex;flex-direction:column;gap:20px;">

                                <button id="btn-generate" class="btn btn-primary btn-full"
                                    onclick="generateToken()"
                                    {{ !$config['isConfigured'] ? 'disabled' : '' }}
                                    style="padding:13px 20px;font-size:14px;">
                                    <span id="btn-generate-icon"><i class="fas fa-rocket"></i></span>
                                    <span id="btn-generate-text">Generate Gateway Token</span>
                                </button>

                                @if(!$config['isConfigured'])
                                <div class="info-box">
                                    <i class="fas fa-circle-exclamation" style="color:var(--saffron-light);"></i>
                                    <span>Fill in your credentials on the left and click <strong style="color:var(--text);">Save Credentials</strong> to enable token generation.</span>
                                </div>
                                @endif

                                <div class="token-stat-grid">
                                    <div class="token-stat">
                                        <span class="token-stat-label">Token Type</span>
                                        <div class="token-stat-value" id="stat-type">{{ $tokenData ? ucfirst($tokenData['tokenType']) : '—' }}</div>
                                    </div>
                                    <div class="token-stat">
                                        <span class="token-stat-label">Expires In</span>
                                        <div class="token-stat-value" id="stat-expires">{{ $tokenData ? $tokenData['expiresIn'].'s' : '—' }}</div>
                                    </div>
                                    <div class="token-stat">
                                        <span class="token-stat-label">Remaining</span>
                                        <div class="token-stat-value" id="stat-remaining">—</div>
                                    </div>
                                </div>

                                <div>
                                    <div class="form-label" style="margin-bottom:8px;">
                                        Access Token
                                        <button onclick="toggleTokenView()" class="btn btn-ghost btn-sm" style="padding:3px 10px;font-size:10px;" id="btn-toggle-view">
                                            <i class="fas fa-eye"></i> Show
                                        </button>
                                    </div>
                                    <div class="token-box" id="token-box">
                                        @if($tokenData)
                                            <span class="masked-token">{{ substr($tokenData['accessToken'],0,18) }}...[SECURE TOKEN PROTECTED]...{{ substr($tokenData['accessToken'],-18) }}</span>
                                            <span class="raw-token" style="display:none;">{{ $tokenData['accessToken'] }}</span>
                                            <button class="token-copy-btn" onclick="copyToken()" title="Copy token"><i class="fas fa-copy"></i></button>
                                        @else
                                            <span style="color:var(--text-light);font-family:'Inter',sans-serif;font-size:12px;font-style:italic;">
                                                No access token yet. Save your credentials and click <strong>Generate Gateway Token</strong>.
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <div class="form-label" style="margin-bottom:8px;">Refresh Token</div>
                                    <div class="token-box" style="opacity:0.6;font-size:11px;color:var(--text-muted);font-family:'Inter',sans-serif;">
                                        @if($tokenData)
                                            <i class="fas fa-lock" style="margin-right:6px;"></i>
                                            [REFRESH TOKEN MASKED — STORED SECURELY IN SESSION CACHE]
                                        @else
                                            No active session.
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- API Details -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-header-title">
                                    <div class="card-icon green"><i class="fas fa-code"></i></div>
                                    <div>
                                        <div class="card-title">API Endpoint Details</div>
                                        <div class="card-subtitle">Gateway session handshake configuration</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="cred-row">
                                    <span class="cred-key">Method</span>
                                    <span class="status-pill" style="background:var(--danger-light);color:#ef9a9a;border:1px solid rgba(198,40,40,0.25);font-size:10px;">POST</span>
                                </div>
                                <div class="cred-row">
                                    <span class="cred-key">Endpoint</span>
                                    <span class="cred-val" style="font-size:10px;">/api/hiecm/gateway/v3/sessions</span>
                                </div>
                                <div class="cred-row">
                                    <span class="cred-key">Gateway</span>
                                    <span class="cred-val">{{ $config['baseUrl'] }}</span>
                                </div>
                                <div class="cred-row">
                                    <span class="cred-key">X-CM-ID</span>
                                    <span class="cred-val">{{ $config['xCmId'] }}</span>
                                </div>
                                <div class="cred-row">
                                    <span class="cred-key">Grant Type</span>
                                    <span class="cred-val">client_credentials</span>
                                </div>

                                <div class="divider"></div>
                                <div class="json-inspector">
                                    <div class="json-inspector-header" onclick="toggleJson()">
                                        <span><i class="fas fa-code"></i> Raw Response Inspector</span>
                                        <i class="fas fa-chevron-down" id="json-chevron" style="font-size:11px;color:var(--text-muted);transition:transform var(--t-base);"></i>
                                    </div>
                                    <div class="json-body" id="json-body" style="display:none;">
                                        <code id="json-inspector">@if($tokenData){{ json_encode([
                                            'accessToken'  => substr($tokenData['accessToken'],0,12).'...'.substr($tokenData['accessToken'],-12),
                                            'expiresIn'    => $tokenData['expiresIn'],
                                            'tokenType'    => $tokenData['tokenType'],
                                            'refreshToken' => '[MASKED]',
                                            'generatedAt'  => $tokenData['generatedAt'],
                                        ], JSON_PRETTY_PRINT) }}@else{
    "status": "Waiting for generation…"
}@endif</code>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /right -->
                </div><!-- /token-grid -->
            </div><!-- /panel-token -->

        </div><!-- /hims-body -->
    </main>
</div>

<!-- Toast -->
<div class="toast-container" id="toast-container"></div>

<script>
    /* ─── State ─── */
    let countdownInterval = null;
    let generatedTime     = null;
    let expiresInSeconds  = 0;
    let secretVisible     = false;
    let tokenVisible      = false;
    let iframeLoaded      = false;
    let activePanel       = 'onboarding';

    @if($tokenData)
        generatedTime    = new Date("{{ $tokenData['generatedAt'] }}");
        expiresInSeconds = {{ $tokenData['expiresIn'] }};
        startCountdown();
    @endif

    /* Auto-load iframe for the default onboarding panel */
    document.addEventListener('DOMContentLoaded', function() {
        const iframe = document.getElementById('onboarding-iframe');
        if (iframe && !iframeLoaded) {
            iframe.src = iframe.dataset.src;
            iframeLoaded = true;
        }
    });

    /* ─── Live Clock ─── */
    (function updateClock() {
        const el = document.getElementById('live-clock');
        if (el) el.textContent = new Date().toLocaleTimeString('en-IN', { hour12: false });
        setTimeout(updateClock, 1000);
    })();

    /* ─── Panel Switching ─── */
    function switchPanel(id) {
        if (activePanel === id) return;
        activePanel = id;

        // Hide all panels
        document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));

        // Show target panel
        document.getElementById('panel-' + id).classList.add('active');
        document.getElementById('nav-' + id).classList.add('active');

        // Lazy-load iframe only on first visit to onboarding
        if (id === 'onboarding' && !iframeLoaded) {
            const iframe = document.getElementById('onboarding-iframe');
            iframe.src = iframe.dataset.src;
            iframeLoaded = true;
        }
    }

    /* ─── Toast ─── */
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast';
        const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', info: 'fa-circle-info' };
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas ${icons[type] || icons.info} toast-icon ${type}"></i>
                <span class="toast-msg">${message}</span>
                <i class="fas fa-times toast-close" onclick="this.closest('.toast').remove()"></i>
            </div>
            <div class="toast-bar"><div class="toast-progress ${type}"></div></div>`;
        container.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 20);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 4500);
    }

    /* ─── Save Credentials ─── */
    function saveCredentials() {
        const clientId     = document.getElementById('inp-client-id').value.trim();
        const clientSecret = document.getElementById('inp-client-secret').value.trim();
        const baseUrl      = document.getElementById('inp-base-url').value.trim();
        const apiUrl       = document.getElementById('inp-api-url').value.trim();
        const xCmId        = document.getElementById('inp-x-cm-id').value.trim();

        if (!clientId)     { showToast('Client ID is required.', 'error'); return; }
        if (!clientSecret) { showToast('Client Secret is required.', 'error'); return; }
        if (!baseUrl)      { showToast('Gateway Base URL is required.', 'error'); return; }
        if (!apiUrl)       { showToast('API Base URL is required.', 'error'); return; }
        if (!xCmId)        { showToast('X-CM-ID is required.', 'error'); return; }

        const btn = document.getElementById('btn-save-creds');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> Saving…';

        fetch("{{ route('nhpr.token.credentials.save') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ client_id: clientId, client_secret: clientSecret, base_url: baseUrl, api_url: apiUrl, x_cm_id: xCmId }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Credentials saved. You can now generate a token.', 'success');
                updateCredStatusUI(true, clientId, baseUrl, xCmId);
            } else {
                showToast(res.message || 'Failed to save credentials.', 'error');
            }
        })
        .catch(() => showToast('Network error while saving credentials.', 'error'))
        .finally(() => { btn.disabled = false; btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Save Credentials'; });
    }

    /* ─── Clear Credentials ─── */
    function clearCredentials() {
        const btn = document.getElementById('btn-clear-creds');
        btn.disabled = true;

        fetch("{{ route('nhpr.token.credentials.clear') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                showToast('Credentials cleared. Token cache reset.', 'info');
                document.getElementById('inp-client-id').value     = '';
                document.getElementById('inp-client-secret').value = '';
                document.getElementById('inp-x-cm-id').value       = 'sbx';
                updateCredStatusUI(false, '', '', '');
            } else {
                showToast(res.message || 'Failed to clear credentials.', 'error');
            }
        })
        .catch(() => showToast('Network error while clearing credentials.', 'error'))
        .finally(() => { btn.disabled = false; });
    }

    function updateCredStatusUI(configured, clientId, baseUrl, xCmId) {
        const pill     = document.getElementById('cred-status-pill');
        const genBtn   = document.getElementById('btn-generate');
        const preview  = document.getElementById('saved-preview');
        const clearBtn = document.getElementById('btn-clear-creds');

        if (configured) {
            pill.className = 'status-pill configured';
            pill.innerHTML = '<span class="dot"></span> Configured';
            genBtn.disabled = false;
            preview.style.display = '';
            clearBtn.disabled = false;
            if (clientId) document.getElementById('prev-client-id').textContent = clientId;
            if (baseUrl)  document.getElementById('prev-base-url').textContent  = baseUrl;
            if (xCmId)    document.getElementById('prev-xcmid').textContent     = xCmId;
        } else {
            pill.className = 'status-pill missing';
            pill.innerHTML = '<span class="dot"></span> Missing';
            genBtn.disabled = true;
            preview.style.display = 'none';
            clearBtn.disabled = true;
            resetTokenDisplay();
        }
    }

    /* ─── Generate Token ─── */
    function generateToken() {
        const btn  = document.getElementById('btn-generate');
        const icon = document.getElementById('btn-generate-icon');
        const txt  = document.getElementById('btn-generate-text');

        btn.disabled = true;
        icon.innerHTML = '<span class="spinner"></span>';
        txt.textContent = 'Connecting to ABDM Gateway…';

        fetch("{{ route('nhpr.token.generate') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(async r => { const d = await r.json(); if (!r.ok) throw new Error(d.message || `HTTP ${r.status}`); return d; })
        .then(res => { showToast('Gateway Access Token generated successfully!', 'success'); updateTokenDisplay(res.data); })
        .catch(err => { showToast(err.message || 'Token generation failed. Check credentials and retry.', 'error'); })
        .finally(() => { btn.disabled = false; icon.innerHTML = '<i class="fas fa-rocket"></i>'; txt.textContent = 'Generate Gateway Token'; });
    }

    function updateTokenDisplay(data) {
        const rawToken    = data.accessToken;
        const maskedToken = rawToken.substring(0,18) + '...[SECURE TOKEN PROTECTED]...' + rawToken.substring(rawToken.length-18);

        document.getElementById('token-status-pill').className = 'status-pill token-active';
        document.getElementById('token-status-pill').innerHTML = '<span class="dot"></span> Active & Cached';
        document.getElementById('stat-type').textContent    = data.tokenType.charAt(0).toUpperCase() + data.tokenType.slice(1);
        document.getElementById('stat-expires').textContent = data.expiresIn + 's';

        document.getElementById('token-box').innerHTML = `
            <span class="masked-token">${maskedToken}</span>
            <span class="raw-token" style="display:none;">${rawToken}</span>
            <button class="token-copy-btn" onclick="copyToken()" title="Copy token"><i class="fas fa-copy"></i></button>`;
        tokenVisible = false;
        document.getElementById('btn-toggle-view').innerHTML = '<i class="fas fa-eye"></i> Show';

        const mp = { accessToken: rawToken.substring(0,12)+'...'+rawToken.substring(rawToken.length-12), expiresIn: data.expiresIn, tokenType: data.tokenType, refreshToken: '[MASKED]', generatedAt: data.generatedAt };
        document.getElementById('json-inspector').textContent = JSON.stringify(mp, null, 4);

        generatedTime    = new Date(data.generatedAt);
        expiresInSeconds = data.expiresIn;
        startCountdown();
    }

    function resetTokenDisplay() {
        document.getElementById('token-status-pill').className = 'status-pill token-none';
        document.getElementById('token-status-pill').innerHTML = '<span class="dot"></span> No Session';
        document.getElementById('stat-type').textContent      = '—';
        document.getElementById('stat-expires').textContent   = '—';
        document.getElementById('stat-remaining').textContent = '—';
        if (countdownInterval) clearInterval(countdownInterval);
    }

    /* ─── Token Visibility ─── */
    function toggleTokenView() {
        const box    = document.getElementById('token-box');
        const masked = box.querySelector('.masked-token');
        const raw    = box.querySelector('.raw-token');
        const btn    = document.getElementById('btn-toggle-view');
        if (!masked || !raw) return;
        tokenVisible = !tokenVisible;
        if (tokenVisible) {
            masked.style.display = 'none'; raw.style.display = 'inline';
            btn.innerHTML = '<i class="fas fa-eye-slash"></i> Hide';
            showToast('Token visible. Avoid sharing screenshots.', 'info');
        } else {
            masked.style.display = 'inline'; raw.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-eye"></i> Show';
        }
    }

    function toggleSecret() {
        const inp  = document.getElementById('inp-client-secret');
        const icon = document.getElementById('secret-toggle-icon');
        secretVisible = !secretVisible;
        inp.type       = secretVisible ? 'text' : 'password';
        icon.className = secretVisible ? 'fas fa-eye-slash' : 'fas fa-eye';
    }

    function copyToken() {
        const raw = document.querySelector('#token-box .raw-token');
        if (!raw || !raw.textContent) { showToast('No token to copy.', 'error'); return; }
        navigator.clipboard.writeText(raw.textContent)
            .then(()  => showToast('Token copied to clipboard!', 'success'))
            .catch(()  => showToast('Failed to copy token.', 'error'));
    }

    /* ─── JSON Inspector ─── */
    function toggleJson() {
        const body    = document.getElementById('json-body');
        const chevron = document.getElementById('json-chevron');
        const open    = body.style.display === 'none';
        body.style.display      = open ? 'block' : 'none';
        chevron.style.transform = open ? 'rotate(180deg)' : '';
    }

    /* ─── Countdown ─── */
    function startCountdown() {
        if (countdownInterval) clearInterval(countdownInterval);
        const el = document.getElementById('stat-remaining');
        countdownInterval = setInterval(() => {
            if (!generatedTime) { el.textContent = '—'; return; }
            const remaining = expiresInSeconds - Math.floor((new Date() - generatedTime) / 1000);
            if (remaining <= 0) {
                el.textContent = 'Expired'; el.className = 'token-stat-value danger';
                document.getElementById('token-status-pill').className = 'status-pill missing';
                document.getElementById('token-status-pill').innerHTML = '<span class="dot"></span> Expired';
                clearInterval(countdownInterval); return;
            }
            const h = Math.floor(remaining/3600).toString().padStart(2,'0');
            const m = Math.floor((remaining%3600)/60).toString().padStart(2,'0');
            const s = (remaining%60).toString().padStart(2,'0');
            el.textContent = `${h}:${m}:${s}`;
            el.className   = remaining < 300 ? 'token-stat-value warning' : 'token-stat-value success';
        }, 1000);
    }
</script>
</body>
</html>