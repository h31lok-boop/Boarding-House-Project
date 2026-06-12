<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoardMatch | Student Boarding House Finder</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --ink: #172033;
            --text: #2f3b52;
            --muted: #647083;
            --line: #dde5ef;
            --soft: #f6f8fb;
            --white: #ffffff;
            --brand: #2563eb;
            --brand-dark: #1d4ed8;
            --teal: #0f766e;
            --teal-soft: #e9fbf7;
            --amber: #f59e0b;
            --amber-soft: #fff7e6;
            --shadow: 0 18px 44px rgba(23, 32, 51, 0.12);
            --radius: 8px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            background: var(--white);
            color: var(--text);
            font-family: Manrope, "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
        }

        img {
            display: block;
            max-width: 100%;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        .container {
            margin: 0 auto;
            width: min(1140px, calc(100% - 48px));
        }

        .site-nav {
            background: rgba(255, 255, 255, 0.94);
            border-bottom: 1px solid rgba(221, 229, 239, 0.9);
            backdrop-filter: blur(16px);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .nav-inner {
            align-items: center;
            display: flex;
            gap: 24px;
            justify-content: space-between;
            min-height: 72px;
        }

        .brand {
            align-items: center;
            display: inline-flex;
            flex-shrink: 0;
            gap: 10px;
            line-height: 1;
        }

        .brand-mark {
            border-radius: var(--radius);
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.16);
            height: 40px;
            width: 40px;
        }

        .brand-name {
            color: var(--ink);
            font-size: 1.16rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .brand-name span {
            color: var(--brand);
        }

        .nav-links {
            align-items: center;
            display: flex;
            flex: 1;
            gap: 24px;
            justify-content: center;
            list-style: none;
            min-width: 0;
        }

        .nav-links a {
            color: var(--muted);
            font-size: 0.92rem;
            font-weight: 700;
            transition: color 0.2s ease;
            white-space: nowrap;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--brand);
        }

        .nav-actions {
            align-items: center;
            display: flex;
            flex-shrink: 0;
            gap: 10px;
        }

        .mobile-auth-actions {
            display: none;
        }

        .menu-toggle {
            align-items: center;
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            color: var(--ink);
            cursor: pointer;
            display: none;
            height: 42px;
            justify-content: center;
            width: 42px;
        }

        .btn {
            align-items: center;
            border: 1px solid transparent;
            border-radius: var(--radius);
            cursor: pointer;
            display: inline-flex;
            font-weight: 800;
            gap: 10px;
            justify-content: center;
            min-height: 44px;
            padding: 11px 18px;
            transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease, transform 0.2s ease;
            white-space: nowrap;
        }

        .btn-primary {
            background: var(--brand);
            box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--brand-dark);
            box-shadow: 0 18px 34px rgba(37, 99, 235, 0.28);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--white);
            border-color: var(--line);
            color: var(--ink);
        }

        .btn-secondary:hover {
            border-color: var(--brand);
            color: var(--brand);
            transform: translateY(-1px);
        }

        .btn-light {
            background: var(--white);
            color: var(--ink);
        }

        .hero {
            color: var(--white);
            min-height: clamp(560px, 76vh, 660px);
            overflow: hidden;
            position: relative;
        }

        .hero::before {
            background:
                linear-gradient(90deg, rgba(10, 18, 32, 0.86) 0%, rgba(10, 18, 32, 0.64) 45%, rgba(10, 18, 32, 0.18) 100%),
                url("https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1800&q=80") center right / cover no-repeat;
            content: "";
            inset: 0;
            position: absolute;
        }

        .hero-inner {
            align-items: center;
            display: grid;
            min-height: clamp(560px, 76vh, 660px);
            padding: 72px 0 92px;
            position: relative;
            z-index: 1;
        }

        .hero-copy {
            max-width: 650px;
        }

        .eyebrow {
            align-items: center;
            color: var(--teal-soft);
            display: inline-flex;
            font-size: 0.76rem;
            font-weight: 800;
            gap: 8px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .eyebrow::before {
            background: var(--teal);
            border-radius: 999px;
            content: "";
            height: 8px;
            width: 8px;
        }

        .section .eyebrow {
            color: var(--teal);
        }

        .hero h1 {
            color: var(--white);
            font-size: clamp(2.5rem, 5.4vw, 4.7rem);
            font-weight: 800;
            line-height: 1.04;
            margin: 18px 0 20px;
            max-width: 760px;
        }

        .hero-copy p:not(.eyebrow) {
            color: rgba(255, 255, 255, 0.84);
            font-size: clamp(1rem, 1.5vw, 1.14rem);
            max-width: 610px;
        }

        .hero-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .hero-facts {
            display: flex;
            flex-wrap: wrap;
            gap: 26px;
            margin-top: 44px;
        }

        .hero-fact strong {
            color: var(--white);
            display: block;
            font-size: 1.42rem;
            line-height: 1;
        }

        .hero-fact span {
            color: rgba(255, 255, 255, 0.72);
            display: block;
            font-size: 0.86rem;
            margin-top: 6px;
        }

        .finder-band {
            margin-top: -60px;
            position: relative;
            z-index: 5;
        }

        .finder-panel {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 18px;
        }

        .finder-tabs {
            align-items: center;
            display: flex;
            gap: 8px;
            margin-bottom: 14px;
        }

        .finder-tab {
            align-items: center;
            background: var(--soft);
            border: 1px solid transparent;
            border-radius: var(--radius);
            color: var(--muted);
            display: inline-flex;
            font-size: 0.86rem;
            font-weight: 800;
            gap: 8px;
            min-height: 38px;
            padding: 8px 12px;
        }

        .finder-tab.active {
            background: var(--teal-soft);
            border-color: rgba(15, 118, 110, 0.18);
            color: var(--teal);
        }

        .search-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: 1.15fr 0.9fr 0.9fr 0.9fr auto;
        }

        .field {
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            display: grid;
            gap: 5px;
            padding: 12px 13px;
        }

        .field label {
            color: var(--muted);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .field input,
        .field select {
            background: transparent;
            border: 0;
            color: var(--ink);
            font-weight: 800;
            min-width: 0;
            outline: 0;
            width: 100%;
        }

        .search-button {
            min-height: 64px;
            padding-left: 24px;
            padding-right: 24px;
        }

        .quick-filters {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 14px;
        }

        .quick-filters span {
            color: var(--muted);
            font-size: 0.86rem;
            font-weight: 800;
        }

        .chip {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--text);
            cursor: pointer;
            font-size: 0.86rem;
            font-weight: 700;
            padding: 7px 12px;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        .chip:hover,
        .chip.active {
            background: var(--teal-soft);
            border-color: rgba(15, 118, 110, 0.28);
            color: var(--teal);
        }

        .section {
            padding: 88px 0;
        }

        .section-soft {
            background: var(--soft);
        }

        .section-header {
            align-items: end;
            display: flex;
            gap: 28px;
            justify-content: space-between;
            margin-bottom: 34px;
        }

        .section-title {
            max-width: 690px;
        }

        .section-title h2 {
            color: var(--ink);
            font-size: clamp(1.9rem, 3.4vw, 3rem);
            font-weight: 800;
            line-height: 1.12;
            margin-top: 10px;
        }

        .section-title p {
            color: var(--muted);
            margin-top: 12px;
            max-width: 620px;
        }

        .listing-grid {
            display: grid;
            gap: 22px;
            grid-template-columns: repeat(3, 1fr);
        }

        .listing-card {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: 0 12px 34px rgba(23, 32, 51, 0.08);
            overflow: hidden;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .listing-card:hover {
            box-shadow: var(--shadow);
            transform: translateY(-3px);
        }

        .listing-media {
            aspect-ratio: 16 / 10;
            overflow: hidden;
            position: relative;
        }

        .listing-media img {
            height: 100%;
            object-fit: cover;
            transition: transform 0.35s ease;
            width: 100%;
        }

        .listing-card:hover .listing-media img {
            transform: scale(1.04);
        }

        .badge-row {
            display: flex;
            gap: 8px;
            left: 12px;
            position: absolute;
            right: 12px;
            top: 12px;
        }

        .badge {
            align-items: center;
            background: rgba(23, 32, 51, 0.86);
            border-radius: 999px;
            color: var(--white);
            display: inline-flex;
            font-size: 0.78rem;
            font-weight: 800;
            gap: 6px;
            padding: 7px 10px;
        }

        .badge.match {
            background: var(--teal);
            margin-left: auto;
        }

        .listing-body {
            padding: 18px;
        }

        .listing-head {
            align-items: start;
            display: flex;
            gap: 14px;
            justify-content: space-between;
        }

        .listing-head h3 {
            color: var(--ink);
            font-size: 1.08rem;
            font-weight: 800;
            line-height: 1.3;
        }

        .price {
            color: var(--brand);
            font-size: 1rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .location {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 7px;
        }

        .meta-grid {
            display: grid;
            gap: 9px;
            grid-template-columns: repeat(2, 1fr);
            margin-top: 16px;
        }

        .meta {
            align-items: center;
            background: var(--soft);
            border-radius: var(--radius);
            color: var(--text);
            display: flex;
            font-size: 0.84rem;
            font-weight: 700;
            gap: 8px;
            padding: 9px 10px;
        }

        .meta i {
            color: var(--teal);
        }

        .match-layout {
            align-items: center;
            display: grid;
            gap: 44px;
            grid-template-columns: 0.95fr 1.05fr;
        }

        .match-preview {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .preview-top {
            align-items: center;
            background: var(--ink);
            color: var(--white);
            display: flex;
            gap: 14px;
            justify-content: space-between;
            padding: 18px;
        }

        .score {
            align-items: center;
            background: var(--teal);
            border-radius: 999px;
            color: var(--white);
            display: inline-flex;
            font-weight: 800;
            height: 42px;
            justify-content: center;
            width: 74px;
        }

        .preview-body {
            display: grid;
            gap: 12px;
            padding: 18px;
        }

        .match-row {
            align-items: center;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 13px 14px;
        }

        .match-row span {
            align-items: center;
            color: var(--ink);
            display: flex;
            font-weight: 800;
            gap: 9px;
        }

        .match-row i {
            color: var(--brand);
        }

        .match-row strong {
            color: var(--teal);
            font-size: 0.9rem;
        }

        .steps {
            display: grid;
            gap: 14px;
            margin-top: 28px;
        }

        .step {
            align-items: flex-start;
            display: flex;
            gap: 13px;
        }

        .step-number {
            align-items: center;
            background: var(--amber-soft);
            border: 1px solid rgba(245, 158, 11, 0.18);
            border-radius: var(--radius);
            color: #b45309;
            display: inline-flex;
            flex: 0 0 auto;
            font-weight: 800;
            height: 34px;
            justify-content: center;
            width: 34px;
        }

        .step h3 {
            color: var(--ink);
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 3px;
        }

        .step p {
            color: var(--muted);
            font-size: 0.92rem;
        }

        .location-layout {
            display: grid;
            gap: 24px;
            grid-template-columns: 1.1fr 0.9fr;
        }

        .photo-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: 1.2fr 0.8fr;
        }

        .photo-tile {
            border-radius: var(--radius);
            min-height: 210px;
            overflow: hidden;
            position: relative;
        }

        .photo-tile:first-child {
            min-height: 436px;
        }

        .photo-tile img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .photo-tile::after {
            background: linear-gradient(180deg, transparent 45%, rgba(23, 32, 51, 0.78));
            content: "";
            inset: 0;
            position: absolute;
        }

        .photo-label {
            bottom: 16px;
            color: var(--white);
            font-weight: 800;
            left: 16px;
            position: absolute;
            z-index: 1;
        }

        .amenity-panel {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 24px;
        }

        .amenity-panel h3 {
            color: var(--ink);
            font-size: 1.35rem;
            line-height: 1.25;
            margin-bottom: 10px;
        }

        .amenity-panel p {
            color: var(--muted);
            margin-bottom: 22px;
        }

        .amenity-list {
            display: grid;
            gap: 12px;
            list-style: none;
        }

        .amenity-list li {
            align-items: center;
            color: var(--ink);
            display: flex;
            font-weight: 700;
            gap: 11px;
        }

        .amenity-list i {
            align-items: center;
            background: var(--teal-soft);
            border-radius: var(--radius);
            color: var(--teal);
            display: inline-flex;
            height: 30px;
            justify-content: center;
            width: 30px;
        }

        .cta {
            background: var(--ink);
            color: var(--white);
            overflow: hidden;
            position: relative;
        }

        .cta::before {
            background:
                linear-gradient(90deg, rgba(23, 32, 51, 0.94), rgba(23, 32, 51, 0.74)),
                url("https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1500&q=80") center / cover no-repeat;
            content: "";
            inset: 0;
            position: absolute;
        }

        .cta-inner {
            align-items: center;
            display: flex;
            gap: 28px;
            justify-content: space-between;
            padding: 72px 0;
            position: relative;
            z-index: 1;
        }

        .cta h2 {
            color: var(--white);
            font-size: clamp(1.9rem, 3.4vw, 3rem);
            font-weight: 800;
            line-height: 1.12;
            max-width: 620px;
        }

        .cta p {
            color: rgba(255, 255, 255, 0.78);
            margin-top: 12px;
            max-width: 560px;
        }

        .footer {
            background: #101827;
            color: rgba(255, 255, 255, 0.72);
            padding: 38px 0 26px;
        }

        .footer-grid {
            align-items: start;
            display: grid;
            gap: 26px;
            grid-template-columns: 1.5fr repeat(3, 1fr);
        }

        .footer .brand-name {
            color: var(--white);
        }

        .footer p,
        .footer li,
        .footer a {
            color: rgba(255, 255, 255, 0.68);
            font-size: 0.9rem;
        }

        .footer h3 {
            color: var(--white);
            font-size: 0.98rem;
            margin-bottom: 12px;
        }

        .footer ul {
            display: grid;
            gap: 8px;
            list-style: none;
        }

        .footer a:hover {
            color: var(--white);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 30px;
            padding-top: 18px;
            text-align: center;
        }

        @media (max-width: 1120px) {
            .nav-links {
                background: var(--white);
                border-bottom: 1px solid var(--line);
                display: none;
                flex-direction: column;
                gap: 18px;
                left: 0;
                padding: 24px;
                position: absolute;
                right: 0;
                top: 72px;
            }

            .nav-links.open {
                display: flex;
            }

            .nav-links a {
                text-align: center;
            }

            .menu-toggle {
                display: inline-flex;
            }

            .nav-actions > .btn {
                display: none;
            }

            .mobile-auth-actions {
                border-top: 1px solid var(--line);
                display: flex;
                flex-direction: column;
                gap: 10px;
                padding-top: 16px;
                width: 100%;
            }

            .mobile-auth-actions .btn {
                width: 100%;
            }

            .search-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .search-button {
                grid-column: 1 / -1;
            }

            .listing-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .match-layout,
            .location-layout {
                grid-template-columns: 1fr;
            }

            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 720px) {
            .container {
                width: min(100% - 28px, 1140px);
            }

            .hero,
            .hero-inner {
                min-height: auto;
            }

            .hero::before {
                background:
                    linear-gradient(180deg, rgba(10, 18, 32, 0.86), rgba(10, 18, 32, 0.72)),
                    url("https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1100&q=80") center / cover no-repeat;
            }

            .hero-inner {
                padding: 66px 0 96px;
            }

            .hero h1 {
                font-size: clamp(2.2rem, 11vw, 3.2rem);
            }

            .hero-actions,
            .cta-inner {
                align-items: stretch;
                flex-direction: column;
            }

            .hero-actions .btn,
            .cta-inner .btn {
                width: 100%;
            }

            .hero-facts {
                display: grid;
                gap: 16px;
                grid-template-columns: repeat(2, 1fr);
            }

            .finder-band {
                margin-top: -48px;
            }

            .finder-panel {
                padding: 14px;
            }

            .finder-tabs {
                flex-wrap: wrap;
            }

            .search-grid,
            .listing-grid,
            .meta-grid,
            .footer-grid {
                grid-template-columns: 1fr;
            }

            .section {
                padding: 68px 0;
            }

            .section-header {
                align-items: start;
                flex-direction: column;
                gap: 18px;
            }

            .listing-head {
                display: grid;
            }

            .photo-grid {
                grid-template-columns: 1fr;
            }

            .photo-tile,
            .photo-tile:first-child {
                min-height: 240px;
            }

            .match-row {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <nav class="site-nav" aria-label="Primary navigation">
        <div class="container nav-inner">
            <a href="#home" class="brand" aria-label="BoardMatch home">
                <img class="brand-mark" src="{{ asset('images/boardmatch-mark.svg') }}" alt="" aria-hidden="true">
                <span class="brand-name">Board<span>Match</span></span>
            </a>

            <ul class="nav-links" id="nav-links">
                <li><a class="active" href="#home">Home</a></li>
                <li><a href="#finder">Find a Room</a></li>
                <li><a href="#listings">Listings</a></li>
                <li><a href="#matching">Matching</a></li>
                <li><a href="#locations">Locations</a></li>
                <li class="mobile-auth-actions">
                    <a class="btn btn-secondary" href="{{ route('auth.choice') }}"><i class="fas fa-right-to-bracket"></i> Sign In</a>
                    <a class="btn btn-primary" href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Get Started</a>
                </li>
            </ul>

            <div class="nav-actions">
                <a class="btn btn-secondary" href="{{ route('auth.choice') }}"><i class="fas fa-right-to-bracket"></i> Sign In</a>
                <a class="btn btn-primary" href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Get Started</a>
                <button class="menu-toggle" id="menu-toggle" type="button" aria-label="Open menu" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <header class="hero" id="home">
        <div class="container hero-inner">
            <div class="hero-copy">
                <p class="eyebrow">Boarding house finder</p>
                <h1>Student Boarding Houses in Digos City</h1>
                <p>Compare rooms by budget, distance, amenities, availability, and compatibility so students can shortlist safer places with less back-and-forth.</p>
                <div class="hero-actions">
                    <a class="btn btn-light" href="#finder"><i class="fas fa-magnifying-glass"></i> Start Searching</a>
                    <a class="btn btn-secondary" href="#matching"><i class="fas fa-sliders"></i> View Match Criteria</a>
                </div>
                <div class="hero-facts" aria-label="BoardMatch highlights">
                    <div class="hero-fact"><strong>120+</strong><span>rooms to compare</span></div>
                    <div class="hero-fact"><strong>3 min</strong><span>profile setup</span></div>
                    <div class="hero-fact"><strong>88%</strong><span>sample top match</span></div>
                </div>
            </div>
        </div>
    </header>

    <main>
        <section class="finder-band" id="finder" aria-label="Boarding house search">
            <div class="container">
                <form class="finder-panel" id="search-form">
                    <div class="finder-tabs" aria-label="Search audience">
                        <span class="finder-tab active"><i class="fas fa-user-graduate"></i> For students</span>
                        <span class="finder-tab"><i class="fas fa-house-user"></i> For owners</span>
                    </div>
                    <div class="search-grid">
                        <div class="field">
                            <label for="location-filter">Location</label>
                            <select id="location-filter" name="location">
                                <option value="">Any Digos City area</option>
                                <option value="Rizal Avenue">Rizal Avenue</option>
                                <option value="Digos Centro">Digos Centro</option>
                                <option value="Aplaya">Aplaya</option>
                                <option value="Tres de Mayo">Tres de Mayo</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="budget-filter">Budget</label>
                            <select id="budget-filter" name="budget">
                                <option value="">Any budget</option>
                                <option value="3500">Up to &#8369;3,500</option>
                                <option value="5000">Up to &#8369;5,000</option>
                                <option value="7000">Up to &#8369;7,000</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="room-filter">Room Type</label>
                            <select id="room-filter" name="room_type">
                                <option value="">Any type</option>
                                <option value="Solo Room">Solo Room</option>
                                <option value="Shared Room">Shared Room</option>
                                <option value="Bedspace">Bedspace</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="amenity-filter">Amenity</label>
                            <select id="amenity-filter" name="amenities">
                                <option value="">Any amenity</option>
                                <option value="WiFi">WiFi</option>
                                <option value="Kitchen">Kitchen</option>
                                <option value="Laundry">Laundry</option>
                                <option value="Study Area">Study Area</option>
                            </select>
                        </div>
                        <button class="btn btn-primary search-button" type="submit">
                            <i class="fas fa-magnifying-glass"></i> Search
                        </button>
                    </div>
                    <div class="quick-filters" aria-label="Popular filters">
                        <span>Popular:</span>
                        <button class="chip" type="button">Near school</button>
                        <button class="chip" type="button">WiFi ready</button>
                        <button class="chip" type="button">Solo room</button>
                        <button class="chip" type="button">Available now</button>
                    </div>
                </form>
            </div>
        </section>

        <section class="section" id="listings">
            <div class="container">
                <div class="section-header">
                    <div class="section-title">
                        <p class="eyebrow">Featured stays</p>
                        <h2>Boarding houses laid out like property listings</h2>
                        <p>Each listing keeps the details students need most visible: price, room setup, distance, availability, and match score.</p>
                    </div>
                    <a class="btn btn-secondary" href="{{ route('auth.choice') }}"><i class="fas fa-table-list"></i> View Dashboard</a>
                </div>

                <div class="listing-grid">
                    <article class="listing-card">
                        <div class="listing-media">
                            <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=900&q=80" alt="Clean solo boarding room" loading="lazy">
                            <div class="badge-row">
                                <span class="badge"><i class="fas fa-check"></i> Available</span>
                                <span class="badge match">92% match</span>
                            </div>
                        </div>
                        <div class="listing-body">
                            <div class="listing-head">
                                <h3>Rizal Avenue Solo Room</h3>
                                <span class="price">&#8369;4,500/mo</span>
                            </div>
                            <p class="location"><i class="fas fa-location-dot"></i> Near Digos City colleges</p>
                            <div class="meta-grid">
                                <span class="meta"><i class="fas fa-bed"></i> Solo room</span>
                                <span class="meta"><i class="fas fa-wifi"></i> WiFi included</span>
                                <span class="meta"><i class="fas fa-shield-halved"></i> Verified owner</span>
                                <span class="meta"><i class="fas fa-clock"></i> Quiet hours</span>
                            </div>
                        </div>
                    </article>

                    <article class="listing-card">
                        <div class="listing-media">
                            <img src="https://images.unsplash.com/photo-1555854877-bab0e564b8d5?auto=format&fit=crop&w=900&q=80" alt="Shared student boarding room" loading="lazy">
                            <div class="badge-row">
                                <span class="badge"><i class="fas fa-users"></i> Shared</span>
                                <span class="badge match">87% match</span>
                            </div>
                        </div>
                        <div class="listing-body">
                            <div class="listing-head">
                                <h3>Centro Shared Study House</h3>
                                <span class="price">&#8369;3,200/mo</span>
                            </div>
                            <p class="location"><i class="fas fa-location-dot"></i> Digos Centro</p>
                            <div class="meta-grid">
                                <span class="meta"><i class="fas fa-book-open"></i> Study area</span>
                                <span class="meta"><i class="fas fa-kitchen-set"></i> Kitchen use</span>
                                <span class="meta"><i class="fas fa-shirt"></i> Laundry area</span>
                                <span class="meta"><i class="fas fa-calendar-check"></i> 2 slots open</span>
                            </div>
                        </div>
                    </article>

                    <article class="listing-card">
                        <div class="listing-media">
                            <img src="https://images.unsplash.com/photo-1595526114035-0d45ed16cfbf?auto=format&fit=crop&w=900&q=80" alt="Compact boarding house bedspace" loading="lazy">
                            <div class="badge-row">
                                <span class="badge"><i class="fas fa-bolt"></i> New</span>
                                <span class="badge match">83% match</span>
                            </div>
                        </div>
                        <div class="listing-body">
                            <div class="listing-head">
                                <h3>Aplaya Bedspace with WiFi</h3>
                                <span class="price">&#8369;2,800/mo</span>
                            </div>
                            <p class="location"><i class="fas fa-location-dot"></i> Aplaya, Digos City</p>
                            <div class="meta-grid">
                                <span class="meta"><i class="fas fa-bed"></i> Bedspace</span>
                                <span class="meta"><i class="fas fa-wifi"></i> Fiber internet</span>
                                <span class="meta"><i class="fas fa-person-walking"></i> Near transit</span>
                                <span class="meta"><i class="fas fa-peso-sign"></i> Low deposit</span>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="section section-soft" id="matching">
            <div class="container match-layout">
                <div class="section-title">
                    <p class="eyebrow">Smart matching</p>
                    <h2>Shortlists based on student priorities</h2>
                    <p>BoardMatch adapts the real-estate browsing pattern for student boarding decisions, where compatibility matters as much as rent.</p>
                    <div class="steps">
                        <div class="step">
                            <span class="step-number">1</span>
                            <div>
                                <h3>Build a student profile</h3>
                                <p>Budget, room type, schedule, preferred area, amenities, and house-rule preferences.</p>
                            </div>
                        </div>
                        <div class="step">
                            <span class="step-number">2</span>
                            <div>
                                <h3>Compare recommended rooms</h3>
                                <p>Listings are easier to scan with match percentage, price, availability, and location cues.</p>
                            </div>
                        </div>
                        <div class="step">
                            <span class="step-number">3</span>
                            <div>
                                <h3>Inquire or reserve</h3>
                                <p>Students can contact owners after checking the details that affect day-to-day boarding life.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="match-preview" aria-label="Sample compatibility preview">
                    <div class="preview-top">
                        <div>
                            <strong>Rizal Avenue Solo Room</strong>
                            <p>Quiet, near school, WiFi ready</p>
                        </div>
                        <span class="score">92%</span>
                    </div>
                    <div class="preview-body">
                        <div class="match-row">
                            <span><i class="fas fa-wallet"></i> Budget fit</span>
                            <strong>&#8369;4,500 within range</strong>
                        </div>
                        <div class="match-row">
                            <span><i class="fas fa-location-dot"></i> Location fit</span>
                            <strong>Near preferred area</strong>
                        </div>
                        <div class="match-row">
                            <span><i class="fas fa-moon"></i> Lifestyle fit</span>
                            <strong>Quiet hours match</strong>
                        </div>
                        <div class="match-row">
                            <span><i class="fas fa-list-check"></i> Amenities fit</span>
                            <strong>WiFi, kitchen, laundry</strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section" id="locations">
            <div class="container location-layout">
                <div class="photo-grid">
                    <div class="photo-tile">
                        <img src="https://images.unsplash.com/photo-1560448204-603b3fc33ddc?auto=format&fit=crop&w=1000&q=80" alt="Boarding house exterior" loading="lazy">
                        <span class="photo-label">Verified exteriors</span>
                    </div>
                    <div class="photo-tile">
                        <img src="https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=700&q=80" alt="Study area" loading="lazy">
                        <span class="photo-label">Study spaces</span>
                    </div>
                    <div class="photo-tile">
                        <img src="https://images.unsplash.com/photo-1556911220-bff31c812dba?auto=format&fit=crop&w=700&q=80" alt="Shared kitchen" loading="lazy">
                        <span class="photo-label">Shared amenities</span>
                    </div>
                </div>

                <div class="amenity-panel">
                    <p class="eyebrow">Student filters</p>
                    <h3>Focused details, less visual noise</h3>
                    <p>The landing page keeps the real-estate feel while prioritizing the checks that matter for student boarding houses.</p>
                    <ul class="amenity-list">
                        <li><i class="fas fa-school"></i> Distance from school and transit</li>
                        <li><i class="fas fa-peso-sign"></i> Monthly rent, deposit, and included utilities</li>
                        <li><i class="fas fa-door-closed"></i> Solo, shared, or bedspace room setup</li>
                        <li><i class="fas fa-wifi"></i> WiFi, study area, kitchen, and laundry access</li>
                        <li><i class="fas fa-file-shield"></i> Owner verification and house rules</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="cta" id="start">
            <div class="container cta-inner">
                <div>
                    <h2>Ready to find a boarding house that fits?</h2>
                    <p>Create a profile, compare recommendations, and move from search to inquiry with cleaner information.</p>
                </div>
                <a class="btn btn-light" href="{{ route('register') }}"><i class="fas fa-user-plus"></i> Create Account</a>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <a href="#home" class="brand">
                        <img class="brand-mark" src="{{ asset('images/boardmatch-mark.svg') }}" alt="" aria-hidden="true">
                        <span class="brand-name">Board<span>Match</span></span>
                    </a>
                    <p style="margin-top: 14px;">A student boarding house finder and matchmaking system for Digos City.</p>
                </div>
                <div>
                    <h3>Explore</h3>
                    <ul>
                        <li><a href="#finder">Find a Room</a></li>
                        <li><a href="#listings">Listings</a></li>
                        <li><a href="#matching">Matching</a></li>
                    </ul>
                </div>
                <div>
                    <h3>Account</h3>
                    <ul>
                        <li><a href="{{ route('auth.choice') }}">Sign In</a></li>
                        <li><a href="{{ route('register') }}">Create Account</a></li>
                    </ul>
                </div>
                <div>
                    <h3>Contact</h3>
                    <ul>
                        <li>Rizal Avenue, Digos City</li>
                        <li>+63 912 345 6789</li>
                        <li>support@boardmatch.local</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">&copy; 2026 BoardMatch. All rights reserved.</div>
        </div>
    </footer>

    <script>
        const navLinks = document.getElementById('nav-links');
        const menuToggle = document.getElementById('menu-toggle');
        const navAnchors = document.querySelectorAll('.nav-links a');
        const sections = document.querySelectorAll('header[id], section[id]');
        const searchForm = document.getElementById('search-form');
        const chips = document.querySelectorAll('.chip');

        menuToggle.addEventListener('click', () => {
            const isOpen = navLinks.classList.toggle('open');
            menuToggle.setAttribute('aria-expanded', String(isOpen));
            menuToggle.querySelector('i').classList.toggle('fa-bars', !isOpen);
            menuToggle.querySelector('i').classList.toggle('fa-xmark', isOpen);
        });

        navAnchors.forEach(anchor => {
            anchor.addEventListener('click', () => {
                navLinks.classList.remove('open');
                menuToggle.setAttribute('aria-expanded', 'false');
                menuToggle.querySelector('i').classList.add('fa-bars');
                menuToggle.querySelector('i').classList.remove('fa-xmark');
            });
        });

        function updateActiveNav() {
            let current = 'home';
            const offset = window.scrollY + 120;

            sections.forEach(section => {
                if (offset >= section.offsetTop) {
                    current = section.id;
                }
            });

            navAnchors.forEach(anchor => {
                anchor.classList.toggle('active', anchor.getAttribute('href') === `#${current}`);
            });
        }

        window.addEventListener('scroll', updateActiveNav);
        window.addEventListener('load', updateActiveNav);

        searchForm.addEventListener('submit', event => {
            event.preventDefault();
            document.getElementById('listings').scrollIntoView({ behavior: 'smooth' });
        });

        chips.forEach(chip => {
            chip.addEventListener('click', () => {
                chip.classList.toggle('active');
            });
        });
    </script>
</body>
</html>
