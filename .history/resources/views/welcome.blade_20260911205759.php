<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardMatch | DSSC Student Boarding House Finder</title>
    <meta name="description" content="BoardMatch helps Davao del Sur State College students find, compare, match with, and reserve verified boarding houses in Matti, Digos City.">
    <link rel="icon" type="image/png" href="{{ asset('images/boardmatch-final-logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/boardmatch-final-logo.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://images.unsplash.com">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --navy: #101827;
            --navy-2: #0b1220;
            --royal: #2563eb;
            --royal-dark: #1d4ed8;
            --teal: #0f9488;
            --teal-dark: #0f766e;
            --ink: #0f1f3a;
            --text: #475569;
            --muted: #64748b;
            --line: #dbe5f0;
            --soft: #f5f8fc;
            --white: #ffffff;
            --shadow: 0 22px 60px rgba(15, 31, 58, 0.14);
            --shadow-soft: 0 14px 34px rgba(15, 31, 58, 0.08);
            --radius: 8px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            background: var(--white);
            color: var(--text);
            font-family: Manrope, "Segoe UI", sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }
        body, button, input, select { font: inherit; }
        img { display: block; max-width: 100%; }
        a { color: inherit; text-decoration: none; }
        button { cursor: pointer; }
        .container { margin: 0 auto; width: min(1180px, calc(100% - 48px)); }

        .site-nav {
            background: #101827;
            background-color: #ffffff;
            border-bottom: 1px solid #e4ebf3;
            box-shadow: 0 8px 24px rgba(15, 31, 58, 0.08);
            position: sticky;
            top: 0;
            z-index: 80;
        }
        .nav-inner {
            align-items: center;
            display: flex;
            gap: 24px;
            justify-content: space-between;
            min-height: 72px;
        }
        .brand { align-items: center; display: inline-flex; gap: 10px; min-width: 0; }
        .brand-mark { border-radius: 8px; height: 40px; width: 40px; }
        .brand-name {
            color: var(--ink);
            font-size: 1.12rem;
            font-weight: 900;
            letter-spacing: 0;
            white-space: nowrap;
        }
        .site-nav .brand-name { color: var(--ink); }
        .site-nav .brand-mark { height: 30px; width: 30px; }
        .site-nav .brand-name { font-size: 1rem; }
        .nav-links {
            align-items: center;
            display: flex;
            flex: 1;
            gap: 4px;
            justify-content: center;
            list-style: none;
            margin: 0;
            min-width: 0;
            padding: 0;
        }
        .nav-links a {
            border-radius: 8px;
            color: #64748b;
            display: inline-flex;
            font-size: 0.84rem;
            font-weight: 800;
            padding: 9px 12px;
            transition: background 0.2s ease, color 0.2s ease;
            white-space: nowrap;
        }
        .site-nav .nav-links a.active,
        .nav-links a:hover {
            background: #eff6ff;
            color: var(--royal);
        }
        .site-nav .nav-links a.active {
            background: transparent;
            position: relative;
        }
        .site-nav .nav-links a.active::after {
            background: var(--royal);
            bottom: 2px;
            content: "";
            height: 2px;
            left: 12px;
            position: absolute;
            right: 12px;
        }
        .nav-actions { align-items: center; display: flex; flex-shrink: 0; gap: 10px; }
        .menu-toggle {
            align-items: center;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            color: var(--royal);
            display: none;
            height: 42px;
            justify-content: center;
            width: 42px;
        }
        .btn {
            align-items: center;
            border: 1px solid transparent;
            border-radius: 8px;
            display: inline-flex;
            font-size: 0.92rem;
            font-weight: 900;
            gap: 9px;
            justify-content: center;
            min-height: 44px;
            padding: 11px 18px;
            transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease, transform 0.2s ease;
            white-space: nowrap;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary,
        .site-nav .btn-primary {
            background: var(--royal);
            box-shadow: 0 14px 30px rgba(37, 99, 235, 0.28);
            color: var(--white);
        }
        .btn-primary:hover,
        .site-nav .btn-primary:hover { background: var(--royal-dark); color: var(--white); }
        .btn-ghost {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.28);
            color: var(--white);
        }
        .btn-light { background: var(--white); border-color: var(--line); color: var(--ink); }
        .btn-light:hover {
            border-color: rgba(37, 99, 235, 0.35);
            box-shadow: var(--shadow-soft);
            color: var(--royal);
        }
        .eyebrow {
            color: var(--royal);
            font-size: 0.76rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            margin: 0 0 8px;
            text-transform: uppercase;
        }

        .hero {
            background:
                linear-gradient(90deg, rgba(11, 18, 32, 0.94) 0%, rgba(15, 31, 58, 0.82) 42%, rgba(15, 31, 58, 0.28) 100%),
                url("https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1800&q=82") center / cover no-repeat;
            color: var(--white);
            min-height: 520px;
            position: relative;
        }
        .hero-inner {
            display: block;
            min-height: 520px;
            padding: 78px 0 126px;
            position: relative;
        }
        .hero-copy { align-self: center; max-width: 680px; }
        .hero .eyebrow { color: #67e8f9; }
        .hero h1 {
            color: var(--white);
            font-size: clamp(2.65rem, 3.4vw, 4.25rem);
            font-weight: 900;
            letter-spacing: 0;
            line-height: 0.98;
            margin: 0;
            max-width: 780px;
        }
        .hero-lead {
            color: rgba(255, 255, 255, 0.84);
            font-size: clamp(1rem, 2vw, 1.22rem);
            font-weight: 650;
            margin: 22px 0 0;
            max-width: 640px;
        }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 30px; }
        .hero-panel {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            box-shadow: var(--shadow);
            color: var(--ink);
            overflow: hidden;
        }

        .hero-panel { display: none; }

        .hero-trust {
            color: rgba(255, 255, 255, 0.72);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.02em;
            margin: 17px 0 0;
        }
        .hero-panel-image {
            aspect-ratio: 16 / 10;
            background: url("https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1000&q=82") center / cover no-repeat;
            position: relative;
        }
        .hero-panel-image span {
            background: rgba(15, 148, 136, 0.94);
            border-radius: 999px;
            bottom: 14px;
            color: var(--white);
            font-size: 0.72rem;
            font-weight: 900;
            left: 14px;
            padding: 7px 10px;
            position: absolute;
        }
        .hero-panel-body { padding: 18px; }
        .hero-panel-body h2 { color: var(--ink); font-size: 1.05rem; line-height: 1.25; margin: 0; }
        .hero-panel-body p { color: var(--muted); font-size: 0.88rem; font-weight: 700; margin: 7px 0 0; }
        .panel-metrics { display: grid; gap: 10px; grid-template-columns: repeat(3, 1fr); margin-top: 16px; }
        .panel-metrics div { background: var(--soft); border-radius: 8px; padding: 10px; }
        .panel-metrics strong { color: var(--teal-dark); display: block; font-size: 1rem; font-weight: 900; }
        .panel-metrics span { color: var(--muted); display: block; font-size: 0.72rem; font-weight: 800; }

        .search-shell { margin-top: -78px; position: relative; z-index: 10; }
        .search-card {
            background: var(--white);
            border: 1px solid rgba(219, 229, 240, 0.95);
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 22px;
        }
        .search-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: minmax(0, 1.25fr) repeat(3, minmax(140px, 1fr)) auto;
        }
        .field label { color: var(--ink); display: block; font-size: 0.75rem; font-weight: 900; margin-bottom: 7px; }
        .input-wrap {
            align-items: center;
            background: #f8fbff;
            border: 1px solid var(--line);
            border-radius: 8px;
            display: flex;
            gap: 10px;
            min-height: 46px;
            padding: 0 13px;
        }
        .input-wrap i { color: var(--royal); }
        .input-wrap input,
        .input-wrap select {
            appearance: none;
            background: transparent;
            border: 0;
            color: var(--ink);
            min-width: 0;
            outline: 0;
            width: 100%;
        }
        .search-card .btn { align-self: end; min-width: 128px; }
        .quick-filters { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
        .quick-filters span { color: var(--ink); font-size: 0.82rem; font-weight: 900; }
        .chip {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--text);
            font-size: 0.78rem;
            font-weight: 800;
            padding: 7px 12px;
        }
        .chip:hover,
        .chip.active { background: #eaf2ff; border-color: rgba(37, 99, 235, 0.28); color: var(--royal); }
        .stats { display: grid; gap: 22px; grid-template-columns: repeat(4, 1fr); padding: 34px 0 20px; }
        .stat { align-items: center; display: flex; gap: 13px; min-width: 0; }
        .stat i {
            align-items: center;
            background: #e8fbf7;
            border-radius: 8px;
            color: var(--teal-dark);
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 1.35rem;
            height: 48px;
            justify-content: center;
            width: 48px;
        }
        .stat strong { color: var(--ink); display: block; font-size: 1.2rem; font-weight: 900; }
        .stat span { color: var(--muted); display: block; font-size: 0.82rem; font-weight: 800; }

        .section { padding: 78px 0; }
        .section-soft { background: linear-gradient(180deg, #f7fbff 0%, #edf7ff 100%); }
        .section-header { align-items: end; display: flex; gap: 24px; justify-content: space-between; margin-bottom: 28px; }
        .section-title { max-width: 720px; }
        .section-title h2 {
            color: var(--ink);
            font-size: clamp(1.8rem, 3vw, 2.65rem);
            line-height: 1.08;
            margin: 0;
            overflow-wrap: anywhere;
        }
        .section-title p:not(.eyebrow) { color: var(--muted); font-weight: 700; margin: 10px 0 0; }

        .listing-grid { display: grid; gap: 24px; grid-template-columns: repeat(3, 1fr); }
        .listing-card {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow-soft);
            overflow: hidden;
        }
        .listing-media { aspect-ratio: 16 / 10; overflow: hidden; position: relative; }
        .listing-media img { height: 100%; object-fit: cover; transition: transform 0.3s ease; width: 100%; }
        .listing-card:hover .listing-media img { transform: scale(1.04); }
        .badge-row { display: flex; gap: 8px; left: 12px; position: absolute; right: 12px; top: 12px; }
        .badge {
            align-items: center;
            background: rgba(15, 148, 136, 0.94);
            border-radius: 999px;
            color: var(--white);
            display: inline-flex;
            font-size: 0.68rem;
            font-weight: 900;
            gap: 5px;
            padding: 6px 9px;
        }
        .badge.match { background: rgba(37, 99, 235, 0.94); margin-left: auto; }
        .listing-body { padding: 18px; }
        .listing-head { align-items: flex-start; display: flex; gap: 14px; justify-content: space-between; }
        .listing-head h3 { color: var(--ink); font-size: 1rem; line-height: 1.32; margin: 0; overflow-wrap: anywhere; }
        .price { color: var(--ink); flex: 0 0 auto; font-size: 0.95rem; font-weight: 900; }
        .location { align-items: center; color: var(--muted); display: flex; font-size: 0.82rem; font-weight: 750; gap: 7px; margin: 9px 0 0; }
        .location i { color: var(--royal); }
        .meta-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .meta {
            align-items: center;
            background: var(--soft);
            border: 1px solid #e8eef5;
            border-radius: 999px;
            color: #4b5d73;
            display: inline-flex;
            font-size: 0.72rem;
            font-weight: 800;
            gap: 6px;
            padding: 6px 9px;
        }
        .meta i { color: var(--teal-dark); }
        .listing-footer { align-items: center; border-top: 1px solid #edf2f7; display: flex; justify-content: space-between; margin-top: 16px; padding-top: 14px; }
        .match-pill { background: #dff9ec; border-radius: 999px; color: #047857; font-size: 0.78rem; font-weight: 900; padding: 7px 10px; }
        .text-link { color: var(--royal); font-size: 0.82rem; font-weight: 900; }
        .empty-listings { align-items: center; background: var(--soft); border: 1px dashed var(--line); border-radius: 8px; display: flex; gap: 16px; padding: 24px; }
        .empty-listings i { color: var(--teal-dark); font-size: 1.8rem; }
        .empty-listings h3 { color: var(--ink); margin: 0 0 3px; }
        .empty-listings p { margin: 0; }

        .match-layout { align-items: center; display: grid; gap: 32px; grid-template-columns: minmax(0, 0.92fr) minmax(420px, 1.08fr); }
        .match-card { background: rgba(255, 255, 255, 0.82); border: 1px solid rgba(219, 229, 240, 0.95); border-radius: 8px; box-shadow: var(--shadow-soft); padding: 20px; }
        .match-board { background: linear-gradient(135deg, #e7f5ff 0%, #f9fcff 100%); border-radius: 8px; display: grid; gap: 18px; grid-template-columns: minmax(220px, 0.72fr) minmax(280px, 1fr); padding: 18px; }
        .student-profile { background: var(--white); border: 1px solid #e4edf7; border-radius: 8px; padding: 18px; }
        .profile-head { align-items: center; display: flex; gap: 12px; }
        .avatar { background: linear-gradient(135deg, #dff9ec, #dbeafe); border-radius: 999px; display: grid; flex: 0 0 auto; height: 62px; place-items: center; width: 62px; }
        .avatar i { color: var(--royal); font-size: 1.55rem; }
        .profile-head strong,
        .match-list strong { color: var(--ink); display: block; font-weight: 900; }
        .profile-head span { color: var(--muted); font-size: 0.82rem; font-weight: 800; }
        .profile-list { display: grid; gap: 9px; list-style: none; margin: 18px 0 0; padding: 0; }
        .profile-list li { align-items: center; color: #40526a; display: flex; font-size: 0.82rem; font-weight: 800; gap: 8px; }
        .profile-list i { color: var(--teal-dark); width: 16px; }
        .match-details { display: grid; gap: 10px; list-style: none; margin: 22px 0 0; padding: 0; }
        .match-detail { align-items: flex-start; display: flex; gap: 10px; }
        .match-detail > i {
            align-items: center;
            background: #dbeafe;
            border-radius: 8px;
            color: var(--royal);
            display: inline-flex;
            flex: 0 0 auto;
            height: 30px;
            justify-content: center;
            width: 30px;
        }
        .match-detail strong { color: var(--ink); display: block; font-size: 0.86rem; }
        .match-detail span { color: var(--muted); display: block; font-size: 0.78rem; font-weight: 700; }
        .match-list { background: var(--white); border: 1px solid #e4edf7; border-radius: 8px; padding: 16px; }
        .match-list h3 { color: var(--ink); font-size: 0.92rem; margin: 0 0 12px; }
        .match-row { align-items: center; border-bottom: 1px solid #eef4fa; display: grid; gap: 12px; grid-template-columns: 64px minmax(0, 1fr) auto; padding: 10px 0; }
        .match-row:last-child { border-bottom: 0; padding-bottom: 0; }
        .match-thumb { background: #dbeafe; border-radius: 8px; height: 48px; overflow: hidden; }
        .match-thumb img { height: 100%; object-fit: cover; width: 100%; }
        .match-row span { color: var(--muted); font-size: 0.75rem; font-weight: 800; }
        .score { background: #dff9ec; border-radius: 999px; color: #047857; font-size: 0.78rem; font-weight: 900; padding: 7px 9px; text-align: center; white-space: nowrap; }

        .locations-layout { display: grid; gap: 24px; grid-template-columns: minmax(0, 1.45fr) minmax(300px, 0.75fr); }
        .map-panel { background: var(--white); border: 1px solid var(--line); border-radius: 8px; box-shadow: var(--shadow-soft); display: grid; gap: 18px; grid-template-columns: minmax(0, 1fr) minmax(220px, 0.66fr); padding: 18px; }
        .map-visual {
            background:
                radial-gradient(circle at 28% 34%, rgba(37, 99, 235, 0.95) 0 8px, transparent 9px),
                radial-gradient(circle at 66% 24%, rgba(15, 148, 136, 0.95) 0 7px, transparent 8px),
                radial-gradient(circle at 45% 66%, rgba(37, 99, 235, 0.84) 0 7px, transparent 8px),
                radial-gradient(circle at 72% 72%, rgba(15, 148, 136, 0.82) 0 7px, transparent 8px),
                linear-gradient(30deg, transparent 46%, rgba(37, 99, 235, 0.24) 47% 50%, transparent 51%),
                linear-gradient(120deg, transparent 45%, rgba(15, 148, 136, 0.18) 46% 49%, transparent 50%),
                #edf6ff;
            border: 1px solid #d8e8f7;
            border-radius: 8px;
            min-height: 260px;
            position: relative;
        }
        .map-visual::before {
            background: linear-gradient(90deg, rgba(15, 31, 58, 0.06) 1px, transparent 1px), linear-gradient(0deg, rgba(15, 31, 58, 0.06) 1px, transparent 1px);
            background-size: 34px 34px;
            border-radius: inherit;
            content: "";
            inset: 0;
            position: absolute;
        }
        .map-pin {
            background: var(--royal);
            border: 4px solid var(--white);
            border-radius: 999px;
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.34);
            color: var(--white);
            font-size: 0.76rem;
            font-weight: 900;
            left: 50%;
            padding: 10px 14px;
            position: absolute;
            top: 48%;
            transform: translate(-50%, -50%);
        }
        .area-list { display: grid; gap: 10px; list-style: none; margin: 0; padding: 0; }
        .area-list li { align-items: center; border-bottom: 1px solid #eef4fa; color: var(--text); display: flex; font-size: 0.84rem; font-weight: 800; gap: 9px; justify-content: space-between; padding-bottom: 10px; }
        .area-list li:last-child { border-bottom: 0; }
        .area-list i { color: var(--teal-dark); }
        .area-list span:last-child { color: var(--muted); white-space: nowrap; }
        .location-card { background: linear-gradient(160deg, #f4fbff 0%, #ffffff 100%); border: 1px solid var(--line); border-radius: 8px; box-shadow: var(--shadow-soft); overflow: hidden; }
        .location-card img { aspect-ratio: 16 / 9; object-fit: cover; width: 100%; }
        .location-card-body { padding: 20px; }
        .location-card h3 { color: var(--ink); font-size: 1.3rem; line-height: 1.15; margin: 0; }
        .location-card p { color: var(--muted); font-weight: 700; margin: 10px 0 0; }
        .location-card-body h4 { color: var(--ink); font-size: 0.9rem; margin: 20px 0 0; }
        .location-card-body .location-note { color: var(--teal-dark); font-size: 0.8rem; font-weight: 900; margin-top: 18px; }
        .amenity-list { display: grid; gap: 9px; list-style: none; margin: 10px 0 0; padding: 0; }
        .amenity-list li { align-items: flex-start; color: var(--text); display: flex; font-size: 0.78rem; font-weight: 800; gap: 8px; }
        .amenity-list i { color: var(--teal-dark); margin-top: 3px; width: 15px; }

        .steps-grid { display: grid; gap: 18px; grid-template-columns: repeat(4, 1fr); }
        .step-card { background: var(--white); border: 1px solid var(--line); border-radius: 8px; box-shadow: var(--shadow-soft); padding: 22px; }
        .step-icon { align-items: center; background: #dbeafe; border-radius: 8px; color: var(--royal); display: inline-flex; font-size: 1.28rem; height: 52px; justify-content: center; margin-bottom: 18px; width: 52px; }
        .step-card h3 { color: var(--ink); font-size: 1rem; margin: 0 0 8px; }
        .step-card p { color: var(--muted); font-size: 0.88rem; font-weight: 700; margin: 0; }

        .owner-strip { background: linear-gradient(135deg, #dff3ff 0%, #eaf8ff 52%, #e8fbf7 100%); color: var(--ink); padding: 48px 0; }
        .owner-inner { align-items: center; display: grid; gap: 28px; grid-template-columns: minmax(0, 1fr) auto; }
        .owner-strip .eyebrow { color: var(--royal); }
        .owner-strip h2 { color: var(--ink); font-size: clamp(1.7rem, 3vw, 2.4rem); line-height: 1.12; margin: 0; }
        .owner-strip p { color: var(--text); font-weight: 700; margin: 10px 0 0; max-width: 700px; }
        .cta { background: linear-gradient(135deg, #dff3ff 0%, #eaf8ff 46%, #edfdf8 100%); padding: 56px 0; }
        .cta-inner { align-items: center; display: grid; gap: 28px; grid-template-columns: minmax(0, 1fr) auto; }
        .cta h2 { color: var(--ink); font-size: clamp(1.8rem, 3vw, 2.55rem); line-height: 1.1; margin: 0; }
        .cta p { color: var(--text); font-weight: 750; margin: 10px 0 0; max-width: 760px; }
        .cta-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; }

        .footer { background: var(--navy); border-top: 1px solid rgba(255, 255, 255, 0.08); color: rgba(255, 255, 255, 0.72); padding: 42px 0 26px; }
        .footer-grid { display: grid; gap: 32px; grid-template-columns: minmax(0, 1.5fr) repeat(3, minmax(0, 1fr)); }
        .footer .brand-name { color: var(--white); }
        .footer p { font-size: 0.9rem; margin: 14px 0 0; max-width: 390px; }
        .footer h3 { color: var(--white); font-size: 0.86rem; margin: 0 0 12px; }
        .footer ul { display: grid; gap: 9px; list-style: none; margin: 0; padding: 0; }
        .footer a:hover { color: var(--white); }
        .footer-bottom { align-items: center; border-top: 1px solid rgba(255, 255, 255, 0.1); display: flex; flex-wrap: wrap; gap: 16px; justify-content: space-between; margin-top: 32px; padding-top: 20px; }
        .socials { display: flex; gap: 10px; }
        .socials a { align-items: center; background: rgba(255, 255, 255, 0.08); border-radius: 8px; display: inline-flex; height: 34px; justify-content: center; width: 34px; }

        @media (max-width: 1400px) and (min-width: 1241px) {
            .container { width: min(1120px, calc(100% - 56px)); }
            .hero h1 { font-size: 4.75rem; }
        }
        @media (max-width: 1240px) {
            .nav-links { gap: 0; }
            .search-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .search-card .btn { grid-column: span 2; width: 100%; }
            .listing-grid, .steps-grid { grid-template-columns: repeat(2, 1fr); }
            .match-layout, .locations-layout { grid-template-columns: 1fr; }
        }
        @media (max-width: 980px) {
            .menu-toggle { display: inline-flex; }
            .nav-links {
                background: var(--white);
                border-top: 1px solid var(--line);
                display: none;
                flex-direction: column;
                left: 0;
                padding: 12px 24px 20px;
                position: absolute;
                right: 0;
                top: 72px;
            }
            .nav-links.open { display: flex; }
            .nav-links li, .nav-links a { width: 100%; }
            .hero-inner { min-height: auto; padding: 72px 0 126px; }
            .hero-panel { max-width: 520px; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .match-board, .map-panel, .cta-inner, .owner-inner, .footer-grid { grid-template-columns: 1fr; }
            .cta-actions, .owner-inner .btn { justify-content: flex-start; }
        }
        @media (max-width: 680px) {
            .container { width: min(100% - 28px, 1180px); }
            .nav-actions .btn { display: none; }
            .hero { min-height: 0; }
            .hero h1 { font-size: clamp(2.35rem, 15vw, 3.5rem); }
            .hero-actions, .cta-actions { flex-direction: column; }
            .hero-actions .btn, .cta-actions .btn { width: 100%; }
            .search-shell { margin-top: -58px; }
            .search-card { padding: 16px; }
            .search-grid, .listing-grid, .steps-grid, .stats { grid-template-columns: 1fr; }
            .search-card .btn { grid-column: auto; }
            .section { padding: 58px 0; }
            .section-header { align-items: flex-start; flex-direction: column; }
            .match-row { grid-template-columns: 54px minmax(0, 1fr); }
            .match-row .score { grid-column: 2; justify-self: start; }
        }
    </style>
</head>
<body>
    @php
        $featured = collect($featuredBoardingHouses ?? []);
        $tenantRegisterUrl = route('register', ['role' => 'tenant']);
        $ownerRegisterUrl = route('register.owner');
    @endphp

    <nav class="site-nav" aria-label="Primary navigation">
        <div class="container nav-inner">
            <a class="brand" href="#home" aria-label="BoardMatch home">
                <img class="brand-mark" src="{{ asset('images/boardmatch-final-logo.png') }}" alt="" aria-hidden="true">
                <span class="brand-name">BoardMatch</span>
            </a>
            <ul class="nav-links" id="nav-links">
                <li><a class="active" href="#home">Home</a></li>
                <li><a href="#finder">Find a Room</a></li>
                <li><a href="#listings">Listings</a></li>
                <li><a href="#matching">Smart Match</a></li>
                <li><a href="#locations">Locations</a></li>
            </ul>
            <div class="nav-actions">
                <a class="btn btn-primary" href="{{ route('login') }}"><i class="fas fa-user" aria-hidden="true"></i> Sign In</a>
                <button class="menu-toggle" id="menu-toggle" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="nav-links">
                    <i class="fas fa-bars" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </nav>

    <main>
        <header class="hero" id="home">
            <div class="container hero-inner">
                <div class="hero-copy">
                    <p class="eyebrow">Exclusively for DSSC students</p>
                    <h1>Find the Right Boarding House Near DSSC</h1>
                    <p class="hero-lead">BoardMatch helps Davao del Sur State College students search, compare, match with, and reserve verified boarding houses inside Matti, Digos City.</p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="#finder"><i class="fas fa-search" aria-hidden="true"></i> Find a Boarding House</a>
                        <a class="btn btn-ghost" href="#listings"><i class="fas fa-house" aria-hidden="true"></i> View Listings</a>
                    </div>
                    <p class="hero-trust">Student-focused matching &middot; Smart compatibility ranking &middot; Secure online reservations</p>
                </div>
                <aside class="hero-panel" aria-label="BoardMatch student housing preview">
                    <div class="hero-panel-image"><span>Verified Matti listings</span></div>
                    <div class="hero-panel-body">
                        <h2>Student housing made simpler</h2>
                        <p>Use location, budget, room type, amenities, and compatibility scores to find a better fit.</p>
                        <div class="panel-metrics" aria-label="BoardMatch highlights">
                            <div><strong>50+</strong><span>Listings</span></div>
                            <div><strong>100+</strong><span>Rooms</span></div>
                            <div><strong>92%</strong><span>Top match</span></div>
                        </div>
                    </div>
                </aside>
            </div>
        </header>

        <section class="search-shell" id="finder" aria-label="Boarding house search filters">
            <div class="container">
                <form class="search-card" action="#" method="GET" id="search-form">
                    <div class="search-grid">
                        <div class="field">
                            <label for="location">Location</label>
                            <div class="input-wrap"><i class="fas fa-location-dot" aria-hidden="true"></i><select id="location" name="location" aria-label="Preferred location">
                                <option value="">Any area near DSSC</option>
                                <option value="Rizal Avenue">Rizal Avenue</option>
                                <option value="Digos Centro">Digos Centro</option>
                                <option value="Aplaya">Aplaya</option>
                                <option value="Tres de Mayo">Tres de Mayo</option>
                            </select></div>
                        </div>
                        <div class="field">
                            <label for="budget">Budget</label>
                            <div class="input-wrap"><i class="fas fa-peso-sign" aria-hidden="true"></i><select id="budget" name="budget" aria-label="Budget"><option value="">Any budget</option><option value="3500">Up to &#8369;3,500</option><option value="5000">Up to &#8369;5,000</option><option value="7000">Up to &#8369;7,000</option></select></div>
                        </div>
                        <div class="field">
                            <label for="room_type">Room Type</label>
                            <div class="input-wrap"><i class="fas fa-bed" aria-hidden="true"></i><select id="room_type" name="room_type" aria-label="Room type"><option value="">Any type</option><option value="Solo Room">Solo Room</option><option value="Shared Room">Shared Room</option><option value="Bedspace">Bedspace</option></select></div>
                        </div>
                        <div class="field">
                            <label for="amenity">Amenity</label>
                            <div class="input-wrap"><i class="fas fa-table-cells-large" aria-hidden="true"></i><select id="amenity" name="amenities" aria-label="Amenity"><option value="">Any amenity</option><option value="WiFi">WiFi</option><option value="Kitchen">Kitchen</option><option value="Laundry">Laundry</option><option value="Study Area">Study Area</option></select></div>
                        </div>
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search" aria-hidden="true"></i> Search</button>
                    </div>
                    <div class="quick-filters" aria-label="Popular filters">
                        <span>Popular:</span>
                        <button class="chip" type="button">Near school</button>
                        <button class="chip" type="button">WiFi ready</button>
                        <button class="chip" type="button">Solo room</button>
                        <button class="chip" type="button">Available now</button>
                    </div>
                </form>
                <div class="stats" aria-label="BoardMatch platform statistics">
                    <div class="stat"><i class="fas fa-house" aria-hidden="true"></i><div><strong>50+</strong><span>Verified Listings</span></div></div>
                    <div class="stat"><i class="fas fa-users" aria-hidden="true"></i><div><strong>100+</strong><span>Available Rooms</span></div></div>
                    <div class="stat"><i class="fas fa-location-dot" aria-hidden="true"></i><div><strong>Matti</strong><span>Official service area</span></div></div>
                    <div class="stat"><i class="fas fa-star" aria-hidden="true"></i><div><strong>Smart Match</strong><span>Find your best fit</span></div></div>
                </div>
            </div>
        </section>

        <section class="section" id="listings">
            <div class="container">
                <div class="section-header">
                    <div class="section-title">
                        <p class="eyebrow">Featured listings</p>
                        <h2>Compare boarding houses near your campus</h2>
                        <p>Browse verified spaces in Matti, compare room availability, monthly rates, amenities, and student-friendly location details.</p>
                    </div>
                    <a class="text-link" href="{{ $tenantRegisterUrl }}">View All Listings <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($featured->isNotEmpty())
                    <div class="listing-grid">
                        @foreach($featured as $index => $house)
                            @php
                                $availableRooms = max((int) ($house->available_rooms ?? 0), (int) ($house->available_rooms_count ?? 0), (int) ($house->room_categories_available_rooms_sum ?? 0));
                                $price = $house->effective_price;
                                $location = $house->distance_from_dssc !== null
                                    ? number_format((float) $house->distance_from_dssc, 2).' km from DSSC Main Campus'
                                    : ($house->full_address ?: ($house->address ?: ($house->display_barangay ?: 'Matti, Digos City')));
                                $highlights = $house->amenities
                                    ->take(3)
                                    ->map(fn ($amenity) => ['icon' => 'fa-circle-check', 'label' => $amenity->name]);
                                $roomType = $house->roomCategories->first()?->name ?: $house->rooms->first()?->room_type;
                                if ($roomType && $highlights->count() < 3) {
                                    $highlights->prepend(['icon' => 'fa-bed', 'label' => $roomType]);
                                }
                                $highlights->push([
                                    'icon' => 'fa-door-open',
                                    'label' => $availableRooms.' '.\Illuminate\Support\Str::plural('room', $availableRooms).' open',
                                ]);
                            @endphp
                            <article class="listing-card">
                                <div class="listing-media">
                                    <img src="{{ $house->cover_image_url }}" alt="{{ $house->name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';">
                                    <div class="badge-row">
                                        <span class="badge"><i class="fas fa-check" aria-hidden="true"></i> {{ $availableRooms }} available</span>
                                        <span class="badge match">
                                            @if((int) $house->reviews_count > 0)
                                                <i class="fas fa-star" aria-hidden="true"></i> {{ number_format((float) $house->reviews_avg_rating, 1) }} rating
                                            @else
                                                <i class="fas fa-building" aria-hidden="true"></i> Verified listing
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="listing-body">
                                    <div class="listing-head">
                                        <h3>{{ $house->name }}</h3>
                                        <span class="price">{{ $price !== null ? '&#8369;'.number_format($price, 0).'/mo' : 'Ask owner' }}</span>
                                    </div>
                                    <p class="location"><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $location }}</p>
                                    <div class="meta-grid">
                                        @foreach($highlights->take(4) as $highlight)
                                            <span class="meta"><i class="fas {{ $highlight['icon'] }}" aria-hidden="true"></i> {{ $highlight['label'] }}</span>
                                        @endforeach
                                    </div>
                                    <div class="listing-footer">
                                        <span class="match-pill">
                                            @if((int) $house->reviews_count > 0)
                                                <i class="fas fa-star" aria-hidden="true"></i> {{ number_format((float) $house->reviews_avg_rating, 1) }} rating
                                            @else
                                                Verified listing
                                            @endif
                                        </span>
                                        <a class="text-link" href="{{ route('login') }}">View Details <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="empty-listings" role="status">
                        <i class="fas fa-building-circle-check" aria-hidden="true"></i>
                        <div><h3>No available boarding houses yet</h3><p>Approved listings will appear here as soon as owners publish available rooms in Matti.</p></div>
                    </div>
                @endif
            </div>
        </section>

        <section class="section section-soft" id="matching">
            <div class="container match-layout">
                <div class="section-title">
                    <p class="eyebrow">Student matching</p>
                    <h2>A smarter way to find your perfect match</h2>
                    <p>BoardMatch ranks boarding houses around the needs and lifestyle preferences of DSSC students, where compatibility matters as much as rent.</p>
                    <ul class="match-details">
                        <li class="match-detail">
                            <i class="fas fa-user-gear" aria-hidden="true"></i>
                            <span><strong>Build a student profile</strong>Budget, room type, schedule, preferred area, amenities, and house-rule preferences.</span>
                        </li>
                        <li class="match-detail">
                            <i class="fas fa-list-check" aria-hidden="true"></i>
                            <span><strong>Compare recommended rooms</strong>Scan match percentage, price, availability, and location cues together.</span>
                        </li>
                        <li class="match-detail">
                            <i class="fas fa-comments" aria-hidden="true"></i>
                            <span><strong>Inquire or reserve</strong>Contact owners after checking the details that affect day-to-day boarding life.</span>
                        </li>
                    </ul>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="{{ $tenantRegisterUrl }}"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i> Create Student Account</a>
                        <a class="btn btn-light" href="#how-it-works">See the steps</a>
                    </div>
                </div>
                <div class="match-card">
                    <div class="match-board" aria-label="Smart matching preview">
                        <div class="student-profile">
                            <div class="profile-head"><span class="avatar"><i class="fas fa-user-graduate" aria-hidden="true"></i></span><div><strong>Your Student Profile</strong><span>DSSC Student</span></div></div>
                            <ul class="profile-list">
                                <li><i class="fas fa-wallet" aria-hidden="true"></i> Budget: 2,500 - 4,000</li>
                                <li><i class="fas fa-bed" aria-hidden="true"></i> Room Type: Solo room</li>
                                <li><i class="fas fa-location-dot" aria-hidden="true"></i> Preferred Location: Matti</li>
                                <li><i class="fas fa-wifi" aria-hidden="true"></i> Must Have: WiFi, study area</li>
                            </ul>
                        </div>
                        <div class="match-list">
                            <h3>Top Matching Boarding Houses</h3>
                            <div class="match-row"><div class="match-thumb"><img src="https://images.unsplash.com/photo-1560185127-6ed189bf02f4?auto=format&fit=crop&w=280&q=80" alt="" loading="lazy"></div><div><strong>DSSC Friendly Boarding House</strong><span>0.3 km from DSSC</span></div><span class="score">92% Match</span></div>
                            <div class="match-row"><div class="match-thumb"><img src="https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=280&q=80" alt="" loading="lazy"></div><div><strong>Sunrise Boarders Hub</strong><span>0.7 km from DSSC</span></div><span class="score">88% Match</span></div>
                            <div class="match-row"><div class="match-thumb"><img src="https://images.unsplash.com/photo-1560448204-603b3fc33ddc?auto=format&fit=crop&w=280&q=80" alt="" loading="lazy"></div><div><strong>Estudyante Residences</strong><span>1.1 km from DSSC</span></div><span class="score">85% Match</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="locations">
            <div class="container">
                <div class="section-header">
                    <div class="section-title">
                        <p class="eyebrow">Nearby locations</p>
                        <h2>Rooms close to what matters</h2>
                        <p>BoardMatch focuses the student housing search on Matti, Digos City, Davao del Sur, near DSSC and daily essentials.</p>
                    </div>
                </div>
                <div class="locations-layout">
                    <div class="map-panel">
                        <div class="map-visual" aria-label="Illustrated Matti map"><span class="map-pin">Matti</span></div>
                        <ul class="area-list">
                            <li><span><i class="fas fa-location-dot" aria-hidden="true"></i> DSSC Main Campus</span><span>0 - 1 km</span></li>
                            <li><span><i class="fas fa-location-dot" aria-hidden="true"></i> Matti proper</span><span>2.6 kilometers</span></li>
                            <li><span><i class="fas fa-location-dot" aria-hidden="true"></i> Student stores</span><span>Nearby</span></li>
                            <li><span><i class="fas fa-location-dot" aria-hidden="true"></i> Transit access</span><span>Easy routes</span></li>
                        </ul>
                    </div>
                    <article class="location-card">
                        <img src="https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=900&q=80" alt="Campus-style building near student housing" loading="lazy">
                        <div class="location-card-body">
                            <h3>Live closer. Study better.</h3>
                            <p>Find a boarding house near DSSC and save time for classes, errands, and rest.</p>
                            <p class="location-note">Focused details, less visual noise.</p>
                            <ul class="amenity-list">
                                <li><i class="fas fa-school" aria-hidden="true"></i> Distance from school and transit</li>
                                <li><i class="fas fa-peso-sign" aria-hidden="true"></i> Monthly rent, deposit, and included utilities</li>
                                <li><i class="fas fa-door-closed" aria-hidden="true"></i> Solo, shared, or bedspace room setup</li>
                                <li><i class="fas fa-wifi" aria-hidden="true"></i> WiFi, study area, kitchen, and laundry access</li>
                                <li><i class="fas fa-shield-halved" aria-hidden="true"></i> Owner verification and house rules</li>
                            </ul>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="section section-soft" id="how-it-works">
            <div class="container">
                <div class="section-header">
                    <div class="section-title">
                        <p class="eyebrow">How BoardMatch works</p>
                        <h2>Find your ideal boarding house in 4 simple steps</h2>
                        <p>A faster, clearer way for students to move from preferences to a reservation-ready shortlist.</p>
                    </div>
                </div>
                <div class="steps-grid">
                    <article class="step-card"><span class="step-icon"><i class="fas fa-magnifying-glass" aria-hidden="true"></i></span><h3>1. Set preferences</h3><p>Tell us your budget, room type, preferred area, and must-have amenities.</p></article>
                    <article class="step-card"><span class="step-icon"><i class="fas fa-clipboard-list" aria-hidden="true"></i></span><h3>2. Get matches</h3><p>See verified boarding houses ranked around your student housing needs.</p></article>
                    <article class="step-card"><span class="step-icon"><i class="fas fa-scale-balanced" aria-hidden="true"></i></span><h3>3. Compare options</h3><p>Review photos, prices, amenities, available rooms, and compatibility scores.</p></article>
                    <article class="step-card"><span class="step-icon"><i class="fas fa-message" aria-hidden="true"></i></span><h3>4. Contact or reserve</h3><p>Message the owner or reserve your room directly on BoardMatch.</p></article>
                </div>
            </div>
        </section>

        <section class="owner-strip" id="list-your-property">
            <div class="container owner-inner">
                <div><p class="eyebrow">Boarding house partners</p><h2>Own a boarding house near DSSC?</h2><p>List your property, publish available rooms, and help students find verified housing in Matti, Digos City.</p></div>
                <a class="btn btn-primary" href="{{ $ownerRegisterUrl }}"><i class="fas fa-plus" aria-hidden="true"></i> List Your Boarding House</a>
            </div>
        </section>

        <section class="cta" id="start">
            <div class="container cta-inner">
                <div><h2>Ready to find your student housing match?</h2><p>Create your student profile, browse featured listings, and get compatibility-ranked recommendations for boarding houses near DSSC.</p></div>
                <div class="cta-actions">
                    <a class="btn btn-primary" href="{{ $tenantRegisterUrl }}"><i class="fas fa-user-graduate" aria-hidden="true"></i> Create Student Account</a>
                    <a class="btn btn-light" href="{{ $ownerRegisterUrl }}"><i class="fas fa-building" aria-hidden="true"></i> Register as Owner</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div><a class="brand" href="#home"><img class="brand-mark" src="{{ asset('images/boardmatch-final-logo.png') }}" alt="" aria-hidden="true"><span class="brand-name">BoardMatch</span></a><p>Matching students with a brighter tomorrow through verified boarding house discovery in Matti, Digos City.</p></div>
                <div><h3>Explore</h3><ul><li><a href="#finder">Find a Room</a></li><li><a href="#listings">Listings</a></li><li><a href="#matching">Smart Match</a></li></ul></div>
                <div><h3>BoardMatch</h3><ul><li><a href="#locations">Locations</a></li><li><a href="#list-your-property">List Your Property</a></li><li><a href="{{ route('login') }}">Sign In</a></li></ul></div>
                <div><h3>Contact</h3><ul><li>Matti, Digos City, Davao del Sur, Davao Region</li><li>Rizal Avenue, Digos City</li><li><a href="tel:+639123456789">+63 912 345 6789</a></li><li><a href="mailto:support@boardmatch.local">support@boardmatch.local</a></li></ul></div>
            </div>
            <div class="footer-bottom"><span>&copy; {{ date('Y') }} BoardMatch. All rights reserved.</span><div class="socials" aria-label="Social links"><a href="#" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a><a href="#" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a><a href="#" aria-label="YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a></div></div>
        </div>
    </footer>

    <script>
        const navLinks = document.getElementById('nav-links');
        const menuToggle = document.getElementById('menu-toggle');
        const chips = document.querySelectorAll('.chip');
        const searchForm = document.getElementById('search-form');

        menuToggle?.addEventListener('click', () => {
            const open = navLinks.classList.toggle('open');
            menuToggle.setAttribute('aria-expanded', String(open));
            menuToggle.querySelector('i')?.classList.toggle('fa-bars', !open);
            menuToggle.querySelector('i')?.classList.toggle('fa-xmark', open);
        });

        navLinks?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('open');
                menuToggle?.setAttribute('aria-expanded', 'false');
                menuToggle?.querySelector('i')?.classList.add('fa-bars');
                menuToggle?.querySelector('i')?.classList.remove('fa-xmark');
            });
        });

        chips.forEach((chip) => {
            chip.addEventListener('click', () => chip.classList.toggle('active'));
        });

        searchForm?.addEventListener('submit', (event) => {
            event.preventDefault();
            document.getElementById('listings')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    </script>
</body>
</html>
