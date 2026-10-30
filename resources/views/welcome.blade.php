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
        html { scroll-behavior: smooth; scroll-padding-top: 112px; }
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
        .mobile-signin { display: none; }
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
                linear-gradient(112deg, #07111f 0%, #0b1d38 43%, #123f68 100%);
            color: var(--white);
            min-height: 560px;
            overflow: hidden;
            position: relative;
        }
        .hero::before {
            background:
                radial-gradient(ellipse at 76% 35%, rgba(45, 212, 191, .16), transparent 25%),
                radial-gradient(ellipse at 96% 100%, rgba(37, 99, 235, .28), transparent 42%),
                linear-gradient(135deg, transparent 0 59%, rgba(103, 232, 249, .045) 59% 60%, transparent 60% 100%);
            content: "";
            inset: 0;
            pointer-events: none;
            position: absolute;
        }
        .hero::after {
            background-image: radial-gradient(rgba(148, 229, 255, .3) 1px, transparent 1px);
            background-size: 28px 28px;
            content: "";
            height: 310px;
            opacity: .28;
            pointer-events: none;
            position: absolute;
            right: -35px;
            top: 0;
            transform: rotate(-8deg);
            width: 530px;
        }
        .hero::after { display: none; }
        .hero-inner {
            display: block;
            min-height: 560px;
            padding: 78px 0 150px;
            position: relative;
            z-index: 1;
        }
        .hero-copy { align-self: center; max-width: 590px; position: relative; z-index: 5; }
        .hero .eyebrow { color: #67e8f9; }
        .hero h1 {
            color: var(--white);
            font-size: clamp(2.45rem, 3.1vw, 3.75rem);
            font-weight: 900;
            letter-spacing: 0;
            line-height: 0.98;
            margin: 0;
            max-width: 780px;
        }
        .hero-entrance { animation: hero-entrance 0.62s ease-out both; }
        .hero-entrance-delay-1 { animation-delay: 0.08s; }
        .hero-entrance-delay-2 { animation-delay: 0.16s; }
        .hero-entrance-delay-3 { animation-delay: 0.24s; }
        .hero h1 .hero-heading-accent { color: #67e8f9; display: inline-block; }
        .hero-lead {
            color: rgba(255, 255, 255, 0.84);
            font-size: clamp(1rem, 2vw, 1.22rem);
            font-weight: 400;
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
        .hero-features { display: flex; flex-wrap: wrap; gap: 14px 22px; list-style: none; margin: 22px 0 0; padding: 0; }
        .hero-features li { align-items: center; color: rgba(255,255,255,.72); display: inline-flex; font-size: .68rem; font-weight: 800; gap: 7px; }
        .hero-features i { align-items: center; background: rgba(103,232,249,.13); border: 1px solid rgba(103,232,249,.25); border-radius: 50%; color: #67e8f9; display: inline-flex; height: 24px; justify-content: center; width: 24px; }

        /* Contained hero illustration: the copy remains the primary focus. */
        .hero-animation {
            height: 450px;
            pointer-events: none;
            position: absolute;
            right: -22px;
            top: 38px;
            width: min(53vw, 650px);
        }
        .hero-animation::before {
            display: none;
        }
        .hero-brand-lockup {
            align-items: center;
            animation: hero-fade-up 0.8s both;
            color: #fff;
            display: flex;
            font-size: 1.08rem;
            font-weight: 900;
            gap: 8px;
            left: 8%;
            letter-spacing: -0.02em;
            position: absolute;
            top: -2px;
        }
        .hero-brand-lockup i { color: #67e8f9; font-size: 1.18rem; }
        .hero-brand-lockup strong { color: #67e8f9; }
        .hero-brand-lockup::after {
            animation: hero-underline 2.8s ease-in-out infinite;
            background: linear-gradient(90deg, #2dd4bf, #67e8f9, transparent);
            bottom: -7px;
            content: "";
            height: 2px;
            left: 25px;
            position: absolute;
            transform-origin: left;
            width: 120px;
        }
        .hero-brand-tagline {
            color: rgba(255,255,255,.64);
            display: block;
            font-size: .54rem;
            font-weight: 700;
            letter-spacing: .04em;
            margin-left: 25px;
            margin-top: 12px;
            line-height: 1.25;
            white-space: nowrap;
        }
        .hero-phone {
            animation: phone-float 5s ease-in-out infinite;
            background: #071525;
            border: 6px solid rgba(255,255,255,.88);
            border-radius: 31px;
            box-shadow: 0 18px 36px rgba(0,0,0,.28), 0 0 0 1px rgba(103,232,249,.22);
            height: 370px;
            left: 44%;
            overflow: hidden;
            padding: 9px;
            position: absolute;
            top: 76px;
            transform: rotate(4deg);
            width: 196px;
            will-change: transform;
            z-index: 2;
        }
        .phone-notch { background: #071525; border-radius: 0 0 10px 10px; height: 15px; left: 50%; position: absolute; top: 0; transform: translateX(-50%); width: 74px; z-index: 2; }
        .phone-screen { background: linear-gradient(180deg,#effcff 0 52%,#d9f3f1 52%); border-radius: 19px; height: 100%; overflow: hidden; padding: 22px 10px 10px; position: relative; }
        .phone-topline { align-items: center; color: #0f1f3a; display: flex; font-size: .56rem; font-weight: 900; justify-content: space-between; }
        .phone-topline i { color: #2563eb; font-size: .7rem; }
        .phone-search { background: #fff; border: 1px solid #d5e8ed; border-radius: 8px; box-shadow: 0 4px 12px rgba(15,31,58,.08); color: #76909d; font-size: .48rem; margin-top: 10px; padding: 7px; }
        .mini-map { background: #bde8e3; border-radius: 12px; height: 160px; margin-top: 9px; overflow: hidden; position: relative; }
        .mini-map::before, .mini-map::after { border: 2px solid rgba(37,99,235,.18); border-radius: 50%; content: ""; height: 150px; left: 18px; position: absolute; top: 24px; transform: rotate(-25deg); width: 260px; }
        .mini-map::after { border-color: rgba(15,118,110,.2); height: 210px; left: -90px; top: 75px; transform: rotate(30deg); width: 300px; }
        .map-road { background: rgba(255,255,255,.75); height: 6px; left: -25px; position: absolute; top: 72px; transform: rotate(-25deg); width: 250px; }
        .mini-pin { align-items: center; background: #2563eb; border: 2px solid #fff; border-radius: 50% 50% 50% 0; box-shadow: 0 4px 9px rgba(37,99,235,.35); display: flex; height: 16px; justify-content: center; position: absolute; transform: rotate(-45deg); width: 16px; }
        .mini-pin::after { background: #fff; border-radius: 50%; content: ""; height: 4px; width: 4px; }
        .mini-pin.one { left: 35px; top: 49px; } .mini-pin.two { background: #0f9488; right: 36px; top: 92px; } .mini-pin.three { left: 92px; top: 119px; }
        .mini-pin { animation: mini-pin-pulse 3.2s ease-in-out infinite; }
        .mini-pin.two { animation-delay: -1.1s; }
        .mini-pin.three { animation-delay: -2.1s; }
        .phone-cta { background: #2563eb; border-radius: 8px; box-shadow: 0 7px 14px rgba(37,99,235,.24); color: #fff; font-size: .58rem; font-weight: 900; margin-top: 10px; padding: 9px; text-align: center; }
        .phone-caption { color: #0f766e; font-size: .48rem; font-weight: 900; margin-top: 9px; text-align: center; }
        .listing-float { animation: hero-float 5s ease-in-out infinite; background: rgba(255,255,255,.96); border: 1px solid rgba(255,255,255,.8); border-radius: 13px; box-shadow: 0 16px 30px rgba(3,20,43,.23); display: grid; gap: 8px; grid-template-columns: 43px 1fr; padding: 8px; position: absolute; width: 190px; z-index: 3; }
        .listing-float img { border-radius: 9px; height: 43px; object-fit: cover; width: 43px; }
        .listing-float h3 { color: #0f1f3a; font-size: .59rem; line-height: 1.2; margin: 1px 0 3px; }
        .listing-float p { color: #64748b; font-size: .5rem; font-weight: 800; line-height: 1.2; margin: 0; }
        .listing-float p strong { color: #0f9488; }
        .listing-float .listing-match { background: #dcfce7; border-radius: 5px; color: #15803d; display: inline-block; font-size: .46rem; font-weight: 900; margin-top: 4px; padding: 2px 4px; }
        .listing-one { animation-delay: -.8s; left: 0; top: 142px; } .listing-two { animation-delay: -2.5s; right: 0; top: 205px; } .listing-three { animation-delay: -4s; left: 7%; top: 335px; }
        .route-lines { height: 100%; inset: 0; overflow: visible; position: absolute; width: 100%; z-index: 1; }
        .route-lines path { animation: route-dash 3.8s linear infinite; fill: none; stroke: #5eead4; stroke-dasharray: 5 9; stroke-linecap: round; stroke-width: 2; }
        .route-lines path:nth-child(2) { animation-delay: -1.2s; stroke: #67e8f9; }
        .route-lines path:nth-child(3) { animation-delay: -2.2s; opacity: .6; }
        .hero-badge { align-items: center; animation: hero-fade-up .8s .35s both; background: rgba(15,148,136,.2); border: 1px solid rgba(103,232,249,.32); border-radius: 999px; color: #cffafe; display: flex; font-size: .58rem; font-weight: 900; gap: 6px; padding: 7px 10px; position: absolute; right: 11%; top: 66px; z-index: 4; }
        .hero-badge i { color: #67e8f9; }
        .hero-sparkle { animation: hero-pulse 2.5s ease-in-out infinite; color: #fff; font-size: .8rem; position: absolute; }
        .sparkle-one { left: 25%; top: 104px; } .sparkle-two { right: 5%; top: 145px; animation-delay: -1.1s; } .sparkle-three { right: 21%; bottom: 24px; animation-delay: -.5s; }
        .hero-brand-lockup, .hero-badge, .hero-sparkle, .listing-float, .route-lines { display: none; }
        @keyframes hero-float { 0%,100% { margin-top: 0; } 50% { margin-top: -10px; } }
        @keyframes hero-entrance { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes phone-float { 0%,100% { transform: rotate(4deg) translateY(0); } 50% { transform: rotate(4deg) translateY(-8px); } }
        @keyframes mobile-phone-float { 0%,100% { transform: translateX(-50%) rotate(4deg) translateY(0); } 50% { transform: translateX(-50%) rotate(4deg) translateY(-8px); } }
        @keyframes mini-pin-pulse { 0%,100% { opacity: .84; transform: rotate(-45deg) scale(1); } 50% { opacity: 1; transform: rotate(-45deg) scale(1.12); } }
        @keyframes scroll-reveal { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes hero-pulse { 0%,100% { opacity: .35; transform: scale(.85); } 50% { opacity: 1; transform: scale(1.15); } }
        @keyframes hero-underline { 0%,100% { transform: scaleX(.35); opacity: .55; } 50% { transform: scaleX(1); opacity: 1; } }
        @keyframes hero-fade-up { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes route-dash { to { stroke-dashoffset: -56; } }
        @media (prefers-reduced-motion: reduce) { .hero-animation *, .hero-animation::before { animation: none !important; } }
        @media (prefers-reduced-motion: reduce) {
            .hero-entrance, .mobile-hero-illustration .hero-phone, .mini-pin { animation: none !important; }
            .hero .btn { transition: none; }
            .hero .btn:hover, .hero .btn:focus-visible { transform: none; }
            html.js .reveal-on-scroll { opacity: 1; transform: none; }
        }
        @media (max-width: 980px) {
            .hero-animation { height: 390px; margin: 22px auto -46px; max-width: 650px; position: relative; right: auto; top: auto; width: 100%; }
            .hero-copy { position: relative; z-index: 5; }
            .hero-brand-lockup { left: 8%; top: 0; }
            .hero-phone { left: 44%; top: 68px; }
        }
        @media (max-width: 680px) {
            .hero { display: none; }
            .mobile-hero { display: block; }
            .hero-animation { height: 300px; margin: 14px auto -38px; transform: scale(.88); transform-origin: top center; width: 114%; }
            .hero-brand-lockup { left: 6%; top: 0; }
            .hero-badge { right: 4%; top: 54px; }
            .hero-phone { height: 260px; left: 44%; top: 58px; width: 140px; }
            .mini-map { height: 104px; }
            .listing-float { width: 156px; }
            .listing-two { display: none; right: -2%; top: 174px; }
            .listing-three { display: none; }
            .listing-one { left: -1%; top: 128px; }
            .search-shell { margin-top: 0; }
            .leaflet-popup-content-wrapper { max-width: calc(100vw - 56px); }
            .leaflet-popup-content { margin: 10px; max-width: calc(100vw - 78px); }
            .boardmatch-map-popup { max-width: 240px; min-width: 0; width: min(240px, calc(100vw - 88px)); }
            .map-visual { aspect-ratio: auto; height: 300px; margin-bottom: 18px; }
        }
        .mobile-hero { display: none; }
        .mobile-hero-map { display: none; }
        .mobile-hero-illustration { display: none; }
        .mobile-hero-map::before { background-image: radial-gradient(rgba(148,229,255,.28) 1px, transparent 1px); background-size: 22px 22px; content: ""; inset: 0; opacity: .45; position: absolute; }
        .mobile-hero-map::after { background: linear-gradient(120deg, transparent 30%, rgba(94,234,212,.15) 31% 32%, transparent 33% 63%, rgba(103,232,249,.12) 64% 65%, transparent 66%); content: ""; inset: -10%; position: absolute; transform: rotate(-8deg); }
        .mobile-hero-map-badge { background: rgba(15,148,136,.25); border: 1px solid rgba(103,232,249,.35); border-radius: 999px; color: #cffafe; font-size: .55rem; font-weight: 900; padding: 7px 10px; position: absolute; right: 14px; top: 14px; z-index: 3; }
        .mobile-hero-map-label { color: rgba(255,255,255,.7); font-size: .62rem; font-weight: 900; left: 18px; letter-spacing: .08em; position: absolute; text-transform: uppercase; top: 18px; z-index: 3; }
        .mobile-route { height: 100%; inset: 0; position: absolute; width: 100%; z-index: 1; }
        .mobile-route path { animation: route-dash 4s linear infinite; fill: none; stroke: #67e8f9; stroke-dasharray: 3 8; stroke-linecap: round; stroke-width: 2; }
        .mobile-route path:nth-child(2) { animation-delay: -1.5s; stroke: #5eead4; }
        .mobile-map-pin { animation: hero-pulse 2.6s ease-in-out infinite; background: #0f9488; border: 3px solid #fff; border-radius: 50% 50% 50% 0; box-shadow: 0 0 0 6px rgba(45,212,191,.15), 0 7px 15px rgba(0,0,0,.3); height: 21px; position: absolute; transform: rotate(-45deg); width: 21px; z-index: 2; }
        .mobile-map-pin::after { background: #fff; border-radius: 50%; content: ""; height: 5px; left: 5px; position: absolute; top: 5px; width: 5px; }
        .mobile-map-pin.pin-a { left: 49%; top: 45%; background: #2563eb; } .mobile-map-pin.pin-b { left: 22%; top: 30%; animation-delay: -.8s; } .mobile-map-pin.pin-c { right: 18%; top: 67%; animation-delay: -1.6s; }
        .mobile-map-campus { background: #2563eb; border: 3px solid #fff; border-radius: 999px; box-shadow: 0 8px 18px rgba(37,99,235,.35); color: #fff; font-size: .56rem; font-weight: 900; left: 39%; padding: 8px 11px; position: absolute; top: 52%; z-index: 3; }
        .mobile-listing { animation: hero-float 5s ease-in-out infinite; background: rgba(255,255,255,.98); border: 1px solid rgba(255,255,255,.7); border-radius: 13px; box-shadow: 0 12px 25px rgba(0,0,0,.25); display: grid; gap: 10px; grid-template-columns: 52px 1fr; padding: 9px; position: absolute; width: min(280px, calc(100% - 24px)); z-index: 4; }
        .mobile-listing img { background: #dbeafe; border-radius: 9px; height: 52px; object-fit: cover; width: 52px; }
        .mobile-listing h3 { color: #0f1f3a; font-size: .66rem; line-height: 1.2; margin: 1px 0 3px; }
        .mobile-listing p { color: #64748b; font-size: .54rem; font-weight: 800; line-height: 1.25; margin: 0; }
        .mobile-listing strong { color: #0f9488; }
        .mobile-listing .listing-match { background: #dcfce7; border-radius: 5px; color: #15803d; display: inline-block; font-size: .43rem; font-weight: 900; margin-top: 3px; padding: 2px 4px; }
        .mobile-listing-one { bottom: 14px; left: 12px; } .mobile-listing-two, .mobile-listing-three { display: none; }
        .mobile-hero-copy { background: linear-gradient(180deg, #071525, #0b2c50); color: #fff; padding: 20px 14px 24px; }
        .mobile-hero-nav { align-items: center; display: flex; justify-content: space-between; }
        .mobile-hero-brand { align-items: center; display: inline-flex; font-size: 1rem; font-weight: 900; gap: 7px; }
        .mobile-hero-brand img { border-radius: 7px; height: 28px; width: 28px; }
        .mobile-hero-menu { align-items: center; background: rgba(103,232,249,.12); border: 1px solid rgba(103,232,249,.3); border-radius: 9px; color: #67e8f9; display: inline-flex; height: 38px; justify-content: center; width: 38px; }
        .mobile-hero-eyebrow { color: #67e8f9; font-size: .59rem; font-weight: 900; letter-spacing: .1em; margin: 34px 0 10px; }
        .mobile-hero h1 { color: #fff; font-size: clamp(1.95rem, 9vw, 2.55rem); line-height: 1.05; margin: 0; max-width: 520px; }
        .mobile-hero h1 span { color: #67e8f9; }
        .mobile-hero-description { color: rgba(255,255,255,.76); font-size: .78rem; font-weight: 400; line-height: 1.55; margin: 16px 0 0; }
        .mobile-hero-actions { display: grid; gap: 9px; margin-top: 16px; }
        .mobile-hero-actions .btn { min-height: 45px; width: 100%; }
        @media (prefers-reduced-motion: reduce) { .mobile-hero-map *, .mobile-hero-map::after { animation: none !important; } }
        @media (max-width: 680px) {
            .mobile-hero { display: block; }
            .mobile-hero-nav { display: none; }
            .mobile-hero-eyebrow { margin-top: 0; }
            .mobile-hero h1 { font-size: clamp(1.85rem, 8vw, 2.35rem); }
            .mobile-hero-illustration { background: linear-gradient(180deg, #071525, #0b2c50); border-radius: 18px; display: block; height: 292px; margin: 0 auto 26px; max-width: 320px; overflow: hidden; position: relative; }
            .mobile-hero-illustration .hero-phone { height: 260px; left: 50%; top: 20px; transform: translateX(-50%) rotate(4deg); width: 140px; }
            .mobile-hero-illustration .hero-phone { animation-name: mobile-phone-float; }
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

        .search-shell { margin-bottom: 34px; margin-top: -48px; position: relative; scroll-margin-top: 92px; z-index: 10; }
        #listings { background: #eef8ff; scroll-margin-top: 112px; }
        html.js .reveal-on-scroll { opacity: 0; transform: translateY(16px); }
        html.js .reveal-on-scroll.is-visible { opacity: 1; transform: translateY(0); transition: opacity 0.6s ease, transform 0.6s ease; }
        .btn, .carousel-card-link { transition: background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease, transform 0.2s ease; }
        .btn:hover, .btn:focus-visible, .carousel-card-link:hover, .carousel-card-link:focus-visible { box-shadow: 0 12px 24px rgba(15, 31, 58, 0.14); transform: translateY(-2px); }
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
        /* Let the next section begin directly after the featured cards. */
        #listings.section { padding-bottom: 34px; scroll-margin-top: 112px; }
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
        .listing-section-header .text-link { margin-left: auto; white-space: nowrap; }

        .listing-grid, .legacy-listing-empty { display: none; }
        .listing-card {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: var(--shadow-soft);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .listing-media { aspect-ratio: 16 / 10; background: #dbeafe; overflow: hidden; position: relative; }
        .listing-media img { height: 100%; object-fit: cover; transition: transform 0.3s ease; width: 100%; }
        .listing-card:hover .listing-media img { transform: scale(1.04); }
        .badge-row { display: flex; gap: 8px; left: 12px; position: absolute; top: 12px; }
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
        .listing-body { display: flex; flex: 1; flex-direction: column; min-width: 0; padding: 18px; }
        .listing-head { min-width: 0; }
        .listing-head h3 { color: var(--ink); font-size: 1.05rem; font-weight: 900; line-height: 1.3; margin: 0; overflow-wrap: anywhere; }
        .price { color: var(--ink); display: block; font-size: 1rem; font-weight: 900; margin-top: 7px; }
        .location { align-items: flex-start; color: var(--muted); display: flex; font-size: 0.84rem; font-weight: 700; gap: 7px; line-height: 1.35; margin: 10px 0 0; }
        .location i { color: var(--royal); }
        .meta-grid { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .meta {
            align-items: center;
            background: var(--soft);
            border: 1px solid #e8eef5;
            border-radius: 999px;
            color: #4b5d73;
            display: inline-flex;
            font-size: 0.76rem;
            font-weight: 800;
            gap: 6px;
            padding: 6px 9px;
        }
        .meta i { color: var(--teal-dark); }
        .listing-footer { align-items: center; border-top: 1px solid #edf2f7; display: flex; gap: 12px; justify-content: space-between; margin-top: auto; padding-top: 16px; }
        .availability { align-items: center; color: var(--teal-dark); display: inline-flex; font-size: 0.8rem; font-weight: 900; gap: 6px; line-height: 1.3; }
        .availability i { font-size: 0.78rem; }
        .listing-footer .btn { flex: 0 0 auto; min-height: 40px; padding: 9px 13px; }
        .text-link { color: var(--royal); font-size: 0.82rem; font-weight: 900; }
        .listing-gallery-grid { display: none; }
        .boarding-house-carousel { min-width: 0; position: relative; }
        .carousel-viewport { isolation: isolate; overflow: hidden; }
        .carousel-track { display: flex; gap: 20px; transform: translateX(0); transition: transform 0.45s ease; will-change: transform; }
        .carousel-card { flex: 0 0 calc((100% - 40px) / 3); min-width: 0; }
        .carousel-card-link { background: var(--white); border: 1px solid var(--line); border-radius: 10px; box-shadow: var(--shadow-soft); color: inherit; display: block; overflow: hidden; text-decoration: none; }
        .carousel-card-media { aspect-ratio: 4 / 3; background: #dbeafe; overflow: hidden; }
        .carousel-card-media img { display: block; height: 100%; object-fit: cover; transition: transform 0.3s ease; width: 100%; }
        .carousel-card-link:hover img, .carousel-card-link:focus-visible img { transform: scale(1.03); }
        .carousel-card h3 { color: var(--ink); font-size: 1.05rem; font-weight: 900; line-height: 1.25; margin: 0; min-height: 2.7em; overflow-wrap: anywhere; padding: 16px 18px 18px; }
        .carousel-nav { align-items: center; border: 0; border-radius: 999px; cursor: pointer; display: inline-flex; justify-content: center; position: absolute; z-index: 50; }
        .carousel-nav { background: var(--white); box-shadow: 0 8px 18px rgba(15, 31, 58, .12); color: var(--ink); height: 44px; top: 205px; transform: translateY(-50%); width: 44px; }
        .carousel-nav:hover, .carousel-nav:focus-visible { background: #eff6ff; color: var(--royal); }
        .carousel-nav:disabled { cursor: default; opacity: 0.45; }
        .carousel-prev { left: -2px; }
        .carousel-next { right: -2px; }
        .boarding-house-carousel { padding-bottom: 30px; }
        .carousel-viewport { min-height: 430px; position: relative; }
        .carousel-track { height: 430px; isolation: isolate; position: relative; }
        html.js .carousel-card { flex: none; left: 50%; position: absolute; top: 0; transition: opacity .45s ease, transform .45s ease, visibility .45s ease; width: min(460px, 72%); z-index: 0; }
        html.js .carousel-card:not(.is-current):not(.is-prev):not(.is-next) { opacity: 0; pointer-events: none; transform: translateX(-50%) scale(.78); visibility: hidden; }
        .carousel-card.is-current, .carousel-card.is-prev, .carousel-card.is-next { opacity: 1; pointer-events: auto; visibility: visible; }
        .carousel-card.is-current { opacity: 1; pointer-events: auto; transform: translateX(-50%) scale(1); z-index: 30; }
        .carousel-card.is-prev { opacity: 1; transform: translateX(-128%) scale(.82); z-index: 10; }
        .carousel-card.is-next { opacity: 1; transform: translateX(28%) scale(.82); z-index: 10; }
        .carousel-card.is-prev .carousel-card-link, .carousel-card.is-next .carousel-card-link { box-shadow: 0 8px 18px rgba(15, 31, 58, .09); }
        .carousel-dots { bottom: 0; display: flex; gap: 7px; justify-content: center; left: 0; position: absolute; right: 0; }
        .carousel-dot { background: #cbd5e1; border: 0; border-radius: 999px; cursor: pointer; height: 7px; padding: 0; transition: background .2s ease, transform .2s ease, width .2s ease; width: 7px; }
        .carousel-dot:hover, .carousel-dot:focus-visible { background: #60a5fa; }
        .carousel-dot.is-active { background: var(--royal); transform: scale(1.15); width: 22px; }
        .gallery-tile { background: var(--white); border: 1px solid var(--line); border-radius: 10px; box-shadow: var(--shadow-soft); min-width: 0; overflow: hidden; }
        .gallery-frame { aspect-ratio: 4 / 3; background: #dbeafe; overflow: hidden; position: relative; }
        .gallery-trigger { background: #dbeafe; border: 0; cursor: zoom-in; display: block; height: 100%; padding: 0; width: 100%; }
        .gallery-trigger img { display: block; height: 100%; object-fit: cover; transition: opacity 0.35s ease, transform 0.3s ease; width: 100%; }
        .gallery-frame.is-fading .gallery-trigger img { opacity: 0; }
        .gallery-trigger:hover img, .gallery-trigger:focus-visible img { transform: scale(1.03); }
        .gallery-counter { background: rgba(15, 31, 58, 0.78); border-radius: 999px; color: var(--white); font-size: 0.78rem; font-weight: 900; left: 12px; padding: 6px 9px; position: absolute; top: 12px; }
        .gallery-nav { align-items: center; background: rgba(15, 31, 58, 0.78); border: 0; border-radius: 999px; color: var(--white); cursor: pointer; display: inline-flex; height: 36px; justify-content: center; position: absolute; top: 50%; transform: translateY(-50%); width: 36px; z-index: 1; }
        .gallery-nav:hover, .gallery-nav:focus-visible { background: var(--royal); }
        .gallery-nav[hidden] { display: none; }
        .gallery-prev { left: 12px; }
        .gallery-next { right: 12px; }
        .gallery-toggle { align-items: center; background: rgba(15, 31, 58, 0.78); border: 0; border-radius: 999px; bottom: 12px; color: var(--white); cursor: pointer; display: inline-flex; height: 30px; justify-content: center; position: absolute; right: 12px; width: 30px; z-index: 1; }
        .gallery-toggle:hover, .gallery-toggle:focus-visible { background: var(--royal); }
        .gallery-toggle[hidden] { display: none; }
        .gallery-modal { align-items: center; background: rgba(4, 12, 26, 0.94); display: flex; inset: 0; justify-content: center; padding: 28px; position: fixed; z-index: 200; }
        .gallery-modal[hidden] { display: none; }
        .gallery-modal-content { align-items: center; display: flex; flex-direction: column; gap: 14px; max-height: 100%; max-width: min(1100px, 100%); min-width: 0; position: relative; width: 100%; }
        .gallery-modal-image { border-radius: 10px; display: block; max-height: min(78vh, 760px); max-width: 100%; object-fit: contain; width: auto; }
        .gallery-modal-caption { color: rgba(255, 255, 255, 0.82); font-size: 0.92rem; text-align: center; }
        .gallery-modal-counter { color: var(--white); font-size: 0.82rem; font-weight: 900; }
        .gallery-modal-close, .gallery-modal-prev, .gallery-modal-next { align-items: center; background: rgba(255, 255, 255, 0.14); border: 1px solid rgba(255, 255, 255, 0.25); border-radius: 999px; color: var(--white); cursor: pointer; display: inline-flex; height: 42px; justify-content: center; position: absolute; width: 42px; }
        .gallery-modal-close { right: 0; top: -8px; }
        .gallery-modal-prev { left: -54px; top: 50%; }
        .gallery-modal-next { right: -54px; top: 50%; }
        .gallery-modal-close:hover, .gallery-modal-prev:hover, .gallery-modal-next:hover, .gallery-modal-close:focus-visible, .gallery-modal-prev:focus-visible, .gallery-modal-next:focus-visible { background: var(--royal); }
        .gallery-modal-link { color: #bfdbfe; font-size: 0.9rem; font-weight: 900; }
        .gallery-modal-link:hover { color: var(--white); }
        @media (prefers-reduced-motion: reduce) {
            .gallery-trigger img { transition: none; }
            .carousel-track, .carousel-card, .carousel-card-media img, .carousel-dot { transition: none; }
        }
        .empty-listings { align-items: center; background: var(--soft); border: 1px dashed var(--line); border-radius: 8px; display: flex; gap: 16px; padding: 24px; }
        .empty-listings i { color: var(--teal-dark); font-size: 1.8rem; }
        .empty-listings h3 { color: var(--ink); margin: 0 0 3px; }
        .empty-listings p { margin: 0; }

        .match-layout { align-items: start; display: grid; gap: 32px; grid-template-columns: minmax(0, 2fr) minmax(0, 3fr); }
        .match-board { background: linear-gradient(135deg, #e7f5ff 0%, #f9fcff 100%); border: 1px solid rgba(219, 229, 240, 0.95); border-radius: 8px; box-shadow: var(--shadow-soft); min-width: 0; padding: 20px; }
        .preference-strip { display: flex; flex-wrap: wrap; gap: 8px; list-style: none; margin: 0 0 18px; padding: 0; }
        .preference-chip { align-items: center; background: var(--white); border: 1px solid #dbe7f2; border-radius: 999px; color: var(--text); display: inline-flex; font-size: 0.82rem; font-weight: 700; gap: 6px; line-height: 1.25; padding: 8px 11px; }
        .preference-chip i { color: var(--royal); }
        .preference-chip strong { color: var(--ink); font-weight: 900; }
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
        .match-detail strong { color: var(--ink); display: block; font-size: 0.9rem; }
        .match-detail span { color: var(--muted); display: block; font-size: 0.82rem; font-weight: 400; }
        .match-list { background: var(--white); border: 1px solid #e4edf7; border-radius: 8px; padding: 16px; }
        .match-list, .match-row > div { min-width: 0; }
        .match-list h3 { color: var(--ink); font-size: 1rem; margin: 0; }
        .match-list > p { color: var(--muted); font-size: 0.78rem; margin: 4px 0 10px; }
        .match-row { align-items: center; border-bottom: 1px solid #eef4fa; display: grid; gap: 12px; grid-template-columns: 64px minmax(150px, 1fr) auto; padding: 12px 0; }
        .match-row:last-child { border-bottom: 0; padding-bottom: 0; }
        .match-thumb { background: #dbeafe; border-radius: 8px; height: 56px; overflow: hidden; }
        .match-thumb img { height: 100%; object-fit: cover; width: 100%; }
        .match-row strong { color: var(--ink); display: block; font-size: 0.92rem; font-weight: 900; line-height: 1.25; overflow-wrap: normal; }
        .match-row span { color: var(--muted); display: block; font-size: 0.82rem; font-weight: 400; margin-top: 3px; }
        .match-row > .score { display: inline-flex; margin-top: 0; }
        .score { background: #dff9ec; border-radius: 999px; color: #047857; font-size: 0.78rem; font-weight: 900; padding: 7px 9px; text-align: center; white-space: nowrap; }

        .locations-layout { align-items: stretch; display: grid; gap: 28px; grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr); }
        .locations-layout > * { min-width: 0; }
        .map-panel { background: var(--white); border: 1px solid var(--line); border-radius: 8px; box-shadow: var(--shadow-soft); min-width: 0; overflow: hidden; padding: 18px; position: relative; width: 100%; z-index: 0; }
        .map-visual {
            background: #113d4a;
            border: 1px solid #294f66;
            border-radius: 8px;
            aspect-ratio: auto;
            height: 460px;
            margin-inline: auto;
            max-width: none;
            min-height: 0;
            width: 100%;
            overflow: hidden;
            position: relative;
        }
        #landing-osm-map { height: 100%; min-height: 0; position: relative; width: 100%; }
        .boardmatch-map-marker { background: transparent; border: 0; }
        .boardmatch-map-marker__glyph { align-items: center; background: var(--teal-dark); border: 3px solid #fff; border-radius: 50% 50% 50% 0; box-shadow: 0 5px 14px rgba(15, 31, 58, .3); color: #fff; display: flex; font-size: 15px; height: 34px; justify-content: center; transform: rotate(-45deg); width: 34px; }
        .boardmatch-map-marker__glyph i { transform: rotate(45deg); }
        .boardmatch-map-marker--campus .boardmatch-map-marker__glyph { background: var(--royal); box-shadow: 0 0 0 5px rgba(37, 99, 235, .2), 0 5px 14px rgba(15, 31, 58, .3); }
        .leaflet-popup-content-wrapper { border-radius: 14px; }
        .leaflet-popup-content { margin: 12px 14px; width: auto !important; }
        .boardmatch-map-popup { box-sizing: border-box; font-family: Manrope, "Segoe UI", sans-serif; max-width: 270px; min-width: 0; width: 270px; }
        .boardmatch-map-popup__image { border-radius: 8px; height: 92px; object-fit: cover; width: 100%; }
        .boardmatch-map-popup__body { padding: 8px 2px 0; }
        .boardmatch-map-popup h3 { color: var(--ink); font-size: .86rem; line-height: 1.25; margin: 0 0 5px; overflow-wrap: anywhere; }
        .boardmatch-map-popup p { color: var(--muted); font-size: .72rem; font-weight: 700; margin: 3px 0; }
        .boardmatch-map-popup .boardmatch-map-popup__price { color: var(--teal-dark); font-weight: 900; }
        .boardmatch-map-popup__link { color: var(--royal); display: inline-flex; font-size: .72rem; font-weight: 900; gap: 6px; margin-top: 8px; }
        .leaflet-container { font: inherit; }
        .leaflet-control-zoom a { color: var(--ink); }
        .location-labels { display: grid; gap: 10px; grid-template-columns: repeat(3, minmax(0, 1fr)); list-style: none; margin: 16px 0 0; padding: 0; }
        .location-label { align-items: flex-start; background: #f8fbff; border: 1px solid #dbe7f2; border-radius: 8px; color: var(--text); display: flex; font-size: 0.76rem; font-weight: 800; gap: 8px; line-height: 1.35; min-width: 0; padding: 11px; }
        .location-label i { color: var(--teal-dark); flex: 0 0 auto; margin-top: 2px; }
        .location-label strong { color: var(--ink); display: block; font-size: 0.76rem; font-weight: 900; }
        .location-label span { color: var(--muted); display: block; font-size: 0.7rem; font-weight: 600; margin-top: 2px; }
        .location-card { background: linear-gradient(160deg, #f4fbff 0%, #ffffff 100%); border: 1px solid var(--line); border-radius: 8px; box-shadow: var(--shadow-soft); margin-top: 0; overflow: hidden; }
        .location-card img { aspect-ratio: 16 / 9; object-fit: cover; width: 100%; }
        .location-card-body { padding: 20px; }
        .location-card h3 { color: var(--ink); font-size: 1.3rem; line-height: 1.15; margin: 0; }
        .location-card p { color: var(--muted); font-weight: 400; margin: 10px 0 0; }
        .location-details { display: grid; gap: 10px; list-style: none; margin: 18px 0 20px; padding: 0; }
        .location-details li { align-items: flex-start; color: var(--text); display: flex; font-size: 0.78rem; gap: 9px; line-height: 1.4; }
        .location-details i { color: var(--teal-dark); flex: 0 0 15px; margin-top: 3px; text-align: center; }
        .location-card .btn { width: 100%; }
        .amenity-list { display: grid; gap: 9px; list-style: none; margin: 10px 0 0; padding: 0; }
        .amenity-list li { align-items: flex-start; color: var(--text); display: flex; font-size: 0.78rem; font-weight: 800; gap: 8px; }
        .amenity-list i { color: var(--teal-dark); margin-top: 3px; width: 15px; }

        .how-it-works { padding: 52px 0; }
        .how-it-works .section-header { justify-content: center; margin-bottom: 24px; text-align: center; }
        .how-it-works .section-title { max-width: 620px; }
        .how-it-works .section-title h2 { font-size: clamp(1.65rem, 2.5vw, 2.2rem); }
        .how-it-works .section-title p:not(.eyebrow) { font-weight: 400; }
        .steps-grid { align-items: stretch; display: grid; gap: 18px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .step-card { background: var(--white); border: 1px solid var(--line); border-radius: 8px; box-shadow: var(--shadow-soft); display: flex; flex-direction: column; height: 100%; padding: 20px; }
        .step-icon { align-items: center; background: #dbeafe; border-radius: 8px; color: var(--royal); display: inline-flex; flex: 0 0 52px; font-size: 1.28rem; height: 52px; justify-content: center; margin-bottom: 16px; width: 52px; }
        .step-card h3 { color: var(--ink); font-size: 1rem; font-weight: 900; line-height: 1.25; margin: 0 0 8px; }
        .step-card p { color: var(--muted); font-size: 0.88rem; font-weight: 400; line-height: 1.55; margin: 0; }

        .get-started { background: linear-gradient(135deg, #f5faff 0%, #eefcf9 100%); padding: 64px 0; }
        .get-started-header { margin: 0 auto 28px; max-width: 680px; text-align: center; }
        .get-started-header .eyebrow { color: var(--royal); }
        .get-started-header h2 { color: var(--ink); font-size: clamp(1.8rem, 3vw, 2.55rem); line-height: 1.1; margin: 0; }
        .get-started-grid { display: grid; gap: 20px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .get-started-card { background: var(--white); border: 1px solid var(--line); border-radius: 12px; box-shadow: var(--shadow-soft); display: flex; flex-direction: column; min-height: 248px; padding: 30px; }
        .get-started-card.student { border-top: 4px solid var(--royal); }
        .get-started-card.owner { border-top: 4px solid var(--teal); }
        .get-started-card h3 { color: var(--ink); font-size: 1.35rem; margin: 0; }
        .get-started-card p { color: var(--text); font-weight: 700; margin: 10px 0 24px; max-width: 420px; }
        .get-started-card .btn { align-self: flex-start; margin-top: auto; }

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
            .listing-grid, .listing-gallery-grid, .steps-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .carousel-card { flex-basis: calc((100% - 20px) / 2); }
            .match-layout { grid-template-columns: 1fr; }
            .locations-layout { grid-template-columns: minmax(0, 1fr); }
            .map-panel { grid-template-columns: minmax(0, 1fr); max-width: none; }
            .map-visual { aspect-ratio: auto; height: 420px; }
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
            .mobile-signin { display: block; }
            .hero-inner { min-height: auto; padding: 72px 0 150px; }
            .hero-panel { max-width: 520px; }
            .search-shell { margin-top: -18px; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .match-board, .get-started-grid, .footer-grid { grid-template-columns: 1fr; }
            .locations-layout { grid-template-columns: 1fr; }
            .map-panel { grid-template-columns: 1fr; }
            .map-visual { aspect-ratio: auto; height: 360px; max-width: 460px; }
            .location-labels { grid-template-columns: 1fr; }
        }
        @media (max-width: 680px) {
            .container { width: min(100% - 28px, 1180px); }
            .site-nav .nav-inner { min-height: 58px; }
            .site-nav .brand-mark { height: 28px; width: 28px; }
            .site-nav .brand-name { font-size: .92rem; }
            .menu-toggle { height: 38px; width: 38px; }
            .nav-actions .btn { display: none; }
            .hero { min-height: 0; }
            .hero h1 { font-size: clamp(2.35rem, 15vw, 3.5rem); }
            .hero-actions { flex-direction: column; }
            .hero-actions .btn { width: 100%; }
            .get-started { padding: 52px 0; }
            .get-started-card { min-height: 0; padding: 24px; }
            .get-started-card .btn { width: 100%; }
            .how-it-works { padding: 44px 0; }
            .listing-footer { align-items: stretch; flex-direction: column; }
            .listing-footer .btn { width: 100%; }
            .search-shell { margin-top: -22px; }
            .search-card { border-radius: 16px; padding: 13px; }
            .search-grid { gap: 9px; }
            .field label { font-size: .68rem; margin-bottom: 4px; }
            .input-wrap { min-height: 42px; padding: 0 10px; }
            .input-wrap select { font-size: .86rem; }
            .search-card .btn { min-height: 44px; }
            .search-grid, .listing-grid, .listing-gallery-grid, .steps-grid, .stats { grid-template-columns: 1fr; }
            .carousel-track { gap: 14px; }
            .carousel-card { flex-basis: 100%; }
            html.js .carousel-card { width: 78%; }
            .carousel-viewport, .carousel-track { min-height: 350px; height: 350px; }
            .carousel-nav { top: 175px; }
            .carousel-card.is-prev { transform: translateX(-88%) scale(.72); }
            .carousel-card.is-next { transform: translateX(9%) scale(.72); }
            .search-card .btn { grid-column: auto; }
            .stats { gap: 14px; padding: 24px 0 12px; }
            .stat { gap: 10px; }
            .stat i { font-size: 1.05rem; height: 40px; width: 40px; }
            .stat strong { font-size: 1rem; }
            .stat span { font-size: .72rem; }
            .section { padding: 58px 0; }
            .section-header { align-items: flex-start; flex-direction: column; }
            .listing-section-header .text-link { margin-left: 0; }
            .gallery-modal { padding: 18px; }
            .gallery-modal-prev { left: 8px; }
            .gallery-modal-next { right: 8px; }
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
        $smartMatchUrl = auth()->check() ? route('user.matchmaking.index') : $tenantRegisterUrl;
        $heroCards = $featured->take(3)->values();
        $matchingPreviewRows = $featured->take(3)->map(function ($house): array {
            return [
                'name' => $house->name,
                'image' => $house->cover_image_url,
                'price' => $house->effective_price !== null ? '₱'.number_format((float) $house->effective_price, 0).'/mo' : 'Contact owner',
                'distance' => $house->distance_from_dssc !== null ? number_format((float) $house->distance_from_dssc, 1).' km from DSSC Main Campus' : 'Distance unavailable',
                'match' => $house->match_score !== null ? $house->match_score.'% Match' : 'Example match',
            ];
        })->values();
        if ($matchingPreviewRows->count() < 3) {
            $matchingPreviewRows = $matchingPreviewRows->concat(collect([
                ['name' => 'DSSC Friendly Boarding House', 'image' => 'https://images.unsplash.com/photo-1560185127-6ed189bf02f4?auto=format&fit=crop&w=280&q=80', 'price' => '₱2,500/mo', 'distance' => '0.3 km from DSSC Main Campus', 'match' => 'Example match'],
                ['name' => 'Sunrise Boarders Hub', 'image' => 'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=280&q=80', 'price' => '₱3,200/mo', 'distance' => '0.7 km from DSSC Main Campus', 'match' => 'Example match'],
                ['name' => 'Estudyante Residences', 'image' => 'https://images.unsplash.com/photo-1560448204-603b3fc33ddc?auto=format&fit=crop&w=280&q=80', 'price' => '₱3,800/mo', 'distance' => '1.1 km from DSSC Main Campus', 'match' => 'Example match'],
            ])->take(3 - $matchingPreviewRows->count()));
        }
        $hasMatchPreferences = (bool) ($hasMatchPreferences ?? false);
        $recanaImage = $recanaBoardingHouse?->exterior_image
            ? \Illuminate\Support\Facades\Storage::url($recanaBoardingHouse->exterior_image)
            : ($recanaBoardingHouse?->cover_image_url
            ?? 'https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=900&q=80');
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
                <li class="mobile-signin"><a href="{{ route('login') }}"><i class="fas fa-user" aria-hidden="true"></i> Sign In</a></li>
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
        <section class="mobile-hero" aria-label="BoardMatch mobile introduction">
            <div class="mobile-hero-copy">
                <nav class="mobile-hero-nav" aria-label="Mobile navigation">
                    <a class="mobile-hero-brand" href="#home"><img src="{{ asset('images/boardmatch-final-logo.png') }}" alt=""> <span>BoardMatch</span></a>
                    <button class="mobile-hero-menu" type="button" aria-label="Open navigation" aria-controls="nav-links" aria-expanded="false"><i class="fas fa-bars" aria-hidden="true"></i></button>
                </nav>
                <p class="mobile-hero-eyebrow">Exclusively for DSSC students</p>
                <h1 class="hero-entrance">Find the Right Boarding House <span>Near DSSC</span></h1>
                <p class="mobile-hero-description hero-entrance hero-entrance-delay-1">Find, compare, and reserve verified boarding houses near DSSC that fit your budget and preferences.</p>
                <div class="mobile-hero-actions hero-entrance hero-entrance-delay-2">
                    <a class="btn btn-primary" href="#finder"><i class="fas fa-search" aria-hidden="true"></i> Find a Boarding House</a>
                    <a class="btn btn-ghost" href="{{ $smartMatchUrl }}"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i> Get My Matches</a>
                </div>
                <div class="mobile-hero-map" aria-label="BoardMatch map and listings preview">
                    <span class="mobile-map-label">Map &amp; Listings</span>
                    <span class="mobile-hero-map-badge"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i> Smart Matching</span>
                    <svg class="mobile-route" viewBox="0 0 400 390" preserveAspectRatio="none" aria-hidden="true"><path d="M68 140 C135 92 178 170 204 180 S270 132 320 168"></path><path d="M320 168 C346 215 265 264 220 279 S112 280 72 326"></path></svg>
                    <span class="mobile-map-pin pin-a"></span><span class="mobile-map-pin pin-b"></span><span class="mobile-map-pin pin-c"></span><span class="mobile-map-campus">Near DSSC</span>
                    @foreach($heroCards as $heroIndex => $heroHouse)
                        @php $mobilePrice = $heroHouse->effective_price; @endphp
                        <article class="mobile-listing mobile-listing-{{ ['one', 'two', 'three'][$heroIndex] ?? 'one' }}">
                            <img src="{{ $heroHouse->cover_image_url }}" alt="" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';">
                            <div><h3>{{ $heroHouse->name }}</h3><p><strong>{{ $mobilePrice !== null ? '₱'.number_format($mobilePrice, 0).'/mo' : 'Ask owner' }}</strong> · {{ $heroHouse->distance_from_dssc !== null ? number_format((float) $heroHouse->distance_from_dssc, 1).' km from DSSC' : 'Near DSSC' }}</p><span class="listing-match">{{ $hasMatchPreferences && $heroHouse->match_score !== null ? $heroHouse->match_score.'% match' : 'Personalize match' }}</span></div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        <header class="hero" id="home">
            <div class="container hero-inner">
                <div class="hero-copy">
                    <p class="eyebrow">Exclusively for DSSC students</p>
                    <h1 class="hero-entrance">Find the Right Boarding House <span class="hero-heading-accent">Near DSSC</span></h1>
                    <p class="hero-lead hero-entrance hero-entrance-delay-1">Find, compare, and reserve verified boarding houses near DSSC that fit your budget and preferences.</p>
                    <div class="hero-actions hero-entrance hero-entrance-delay-2">
                        <a class="btn btn-primary" href="#finder"><i class="fas fa-search" aria-hidden="true"></i> Find a Boarding House</a>
                        <a class="btn btn-ghost" href="{{ $smartMatchUrl }}"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i> Get My Matches</a>
                    </div>
                    <ul class="hero-features hero-entrance hero-entrance-delay-3" aria-label="BoardMatch benefits">
                        <li><i class="fas fa-user-graduate" aria-hidden="true"></i> Student-focused matching</li>
                        <li><i class="fas fa-chart-line" aria-hidden="true"></i> Smart compatibility ranking</li>
                        <li><i class="fas fa-lock" aria-hidden="true"></i> Secure online reservations</li>
                    </ul>
                    </div>
                <div class="hero-animation" aria-hidden="true">
                    <div class="hero-brand-lockup"><i class="fas fa-location-dot"></i><span>Board<strong>Match</strong><small class="hero-brand-tagline">Students. Spaces. Perfectly Matched.</small></span></div>
                    <span class="hero-badge"><i class="fas fa-wand-magic-sparkles"></i> Smart Matching</span>
                    <span class="hero-sparkle sparkle-one">✦</span><span class="hero-sparkle sparkle-two">✦</span><span class="hero-sparkle sparkle-three">✦</span>
                    <svg class="route-lines" viewBox="0 0 650 450" preserveAspectRatio="none">
                        <path d="M74 167 C162 107, 236 173, 304 210 S430 224, 486 235"></path>
                        <path d="M490 235 C535 270, 466 327, 358 342 S208 371, 116 352"></path>
                        <path d="M117 352 C176 302, 238 266, 306 210"></path>
                    </svg>
                    @foreach($heroCards as $heroIndex => $heroHouse)
                        @php
                            $heroPrice = $heroHouse->effective_price;
                            $heroDistance = $heroHouse->distance_from_dssc !== null
                                ? number_format((float) $heroHouse->distance_from_dssc, 1).' km from DSSC'
                                : 'Near DSSC';
                        @endphp
                        <div class="listing-float listing-{{ ['one', 'two', 'three'][$heroIndex] ?? 'one' }}">
                            <img src="{{ $heroHouse->cover_image_url }}" alt="" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';">
                            <div><h3>{{ $heroHouse->name }}</h3><p><strong>{{ $heroPrice !== null ? '₱'.number_format($heroPrice, 0).'/mo' : 'Ask owner' }}</strong> · {{ $heroDistance }}</p>
                            @if($hasMatchPreferences && $heroHouse->match_score !== null)
                                <span class="listing-match">{{ $heroHouse->match_score }}% match</span>
                            @else
                                <span class="listing-match">Personalize match</span>
                            @endif</div>
                        </div>
                    @endforeach
                    <div class="hero-phone">
                        <span class="phone-notch"></span>
                        <div class="phone-screen">
                            <div class="phone-topline"><span>Good morning, Hazel</span><i class="fas fa-bell"></i></div>
                            <div class="phone-search"><i class="fas fa-magnifying-glass"></i> Find a room near DSSC</div>
                            <div class="mini-map"><span class="map-road"></span><span class="mini-pin one"></span><span class="mini-pin two"></span><span class="mini-pin three"></span></div>
                            <div class="phone-cta">Find Your Match <i class="fas fa-arrow-right"></i></div>
                            <div class="phone-caption"><i class="fas fa-shield-heart"></i> Verified student spaces</div>
                        </div>
                    </div>
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
                </form>
                <div class="stats" aria-label="BoardMatch platform statistics">
                    <div class="stat"><i class="fas fa-house" aria-hidden="true"></i><div><strong>50+</strong><span>Verified Listings</span></div></div>
                    <div class="stat"><i class="fas fa-users" aria-hidden="true"></i><div><strong>100+</strong><span>Available Rooms</span></div></div>
                    <div class="stat"><i class="fas fa-location-dot" aria-hidden="true"></i><div><strong>Matti</strong><span>Official service area</span></div></div>
                    <div class="stat"><i class="fas fa-star" aria-hidden="true"></i><div><strong>Smart Match</strong><span>Find your best fit</span></div></div>
                </div>
            </div>
        </section>

        <div class="mobile-hero-illustration" aria-hidden="true">
            <div class="hero-phone">
                <span class="phone-notch"></span>
                <div class="phone-screen">
                    <div class="phone-topline"><span>Good morning, Hazel</span><i class="fas fa-bell"></i></div>
                    <div class="phone-search"><i class="fas fa-magnifying-glass"></i> Find a room near DSSC</div>
                    <div class="mini-map"><span class="map-road"></span><span class="mini-pin one"></span><span class="mini-pin two"></span><span class="mini-pin three"></span></div>
                    <div class="phone-cta">Find Your Match <i class="fas fa-arrow-right"></i></div>
                    <div class="phone-caption"><i class="fas fa-shield-heart"></i> Verified student spaces</div>
                </div>
            </div>
        </div>

        <section class="section" id="listings">
            <div class="container">
                <div class="section-header listing-section-header">
                    <div class="section-title">
                        <p class="eyebrow">Featured listings</p>
                        <h2>Explore Boarding Houses Near DSSC</h2>
                        <p>Browse boarding houses and find your next home.</p>
                    </div>
                    <a class="text-link" href="{{ route('user.boarding-houses.index') }}">View All Listings <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if($featured->isNotEmpty())
                    @php $carouselHouses = $featured->unique('id')->values(); @endphp
                    <div class="boarding-house-carousel" id="boarding-house-carousel" aria-label="Featured boarding houses">
                        <div class="carousel-viewport" data-carousel-viewport>
                            <div class="carousel-track">
                                @foreach($carouselHouses as $house)
                                    <article class="carousel-card{{ $loop->first ? ' is-current' : ($loop->iteration === 2 ? ' is-next' : ($loop->last && $carouselHouses->count() > 2 ? ' is-prev' : '')) }}">
                                        <a class="carousel-card-link" href="{{ route('user.boarding-houses.show', $house) }}" aria-label="View {{ $house->name }}">
                                            <div class="carousel-card-media">
                                                <img src="{{ $house->cover_image_url ?: asset('images/boarding-house-placeholder.svg') }}" alt="{{ $house->name }} boarding house cover photo" loading="eager" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';">
                                            </div>
                                            <h3>{{ $house->name }}</h3>
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                        <button class="carousel-nav carousel-prev" type="button" data-carousel-prev aria-label="Previous boarding houses"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                        <button class="carousel-nav carousel-next" type="button" data-carousel-next aria-label="Next boarding houses"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                        <div class="carousel-dots" role="tablist" aria-label="Choose a boarding house">
                            @foreach($carouselHouses as $dotIndex => $dotHouse)
                                <button class="carousel-dot{{ $dotIndex === 0 ? ' is-active' : '' }}" type="button" data-carousel-dot="{{ $dotIndex }}" role="tab" aria-label="Show {{ $dotHouse->name }}" aria-selected="{{ $dotIndex === 0 ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="empty-listings carousel-empty" role="status">
                        <i class="fas fa-building-circle-check" aria-hidden="true"></i>
                        <div><h3>No boarding houses available yet</h3><p>Approved listings will appear here as soon as owners publish available rooms in Matti.</p></div>
                    </div>
                @endif
                @if($featured->isNotEmpty())
                    @php $galleryPayload = []; @endphp
                    <div class="listing-gallery-grid" aria-label="Featured boarding house photo galleries">
                        @foreach($featured->take(3) as $index => $house)
                            @php
                                $galleryImages = collect();
                                $addGalleryImage = function ($path, string $alt) use (&$galleryImages): void {
                                    if (blank($path)) {
                                        return;
                                    }
                                    $url = \Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])
                                        ? $path
                                        : \Illuminate\Support\Facades\Storage::disk('public')->url($path);
                                    $galleryImages->push(['src' => $url, 'alt' => $alt]);
                                };
                                foreach ([
                                    'exterior_image' => $house->name.' property exterior',
                                    'room_image' => $house->name.' bedroom',
                                    'featured_image' => $house->name.' boarding house photo',
                                    'kitchen_image' => $house->name.' shared kitchen',
                                    'cr_image' => $house->name.' shared space',
                                ] as $field => $alt) {
                                    $addGalleryImage($house->{$field} ?? null, $alt);
                                }
                                foreach ($house->images as $image) {
                                    $addGalleryImage($image->url, filled($image->image_label) ? $house->name.' '.$image->image_label : $house->name.' boarding house photo');
                                }
                                foreach ($house->photos as $photo) {
                                    $addGalleryImage($photo->photo_path, $house->name.' boarding house photo');
                                }
                                if ($galleryImages->isEmpty()) {
                                    $addGalleryImage($house->cover_image_url, $house->name.' boarding house photo');
                                }
                                $galleryImages = $galleryImages->unique('src')->values();
                                $firstPhoto = $galleryImages->first();
                                $galleryPayload[] = [
                                    'name' => $house->name,
                                    'images' => $galleryImages->all(),
                                    'details_url' => route('user.boarding-houses.show', $house),
                                ];
                            @endphp
                            <article class="gallery-tile">
                                <div class="gallery-frame" data-gallery-id="{{ $index }}" data-gallery-count="{{ $galleryImages->count() }}">
                                    <button class="gallery-trigger" type="button" data-gallery-open data-gallery-id="{{ $index }}" data-gallery-index="0" aria-label="Open photos for {{ $house->name }}">
                                        <img src="{{ $firstPhoto['src'] ?? asset('images/boarding-house-placeholder.svg') }}" alt="{{ $firstPhoto['alt'] ?? $house->name.' boarding house photo' }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';">
                                    </button>
                                    <span class="gallery-counter" data-gallery-counter aria-live="polite">1/{{ $galleryImages->count() }}</span>
                                    <button class="gallery-nav gallery-prev" type="button" data-gallery-prev data-gallery-id="{{ $index }}" aria-label="Previous photo" @if($galleryImages->count() < 2) hidden @endif><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                                    <button class="gallery-nav gallery-next" type="button" data-gallery-next data-gallery-id="{{ $index }}" aria-label="Next photo" @if($galleryImages->count() < 2) hidden @endif><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                                    <button class="gallery-toggle" type="button" data-gallery-toggle data-gallery-id="{{ $index }}" aria-label="Pause slideshow" aria-pressed="false" @if($galleryImages->count() < 2) hidden @endif><i class="fas fa-pause" aria-hidden="true"></i></button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    <script id="boarding-house-galleries" type="application/json">{!! json_encode($galleryPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                @endif
                @if($featured->isNotEmpty())
                    <div class="listing-grid">
                        @foreach($featured as $index => $house)
                            @php
                                $availableRooms = max((int) ($house->available_rooms ?? 0), (int) ($house->available_rooms_count ?? 0), (int) ($house->room_categories_available_rooms_sum ?? 0));
                                $price = $house->effective_price;
                                $listingImage = $house->exterior_image ?: ($house->room_image ?: $house->cover_image_url);
                                if ($listingImage && !\Illuminate\Support\Str::startsWith($listingImage, ['http://', 'https://', '/'])) {
                                    $listingImage = \Illuminate\Support\Facades\Storage::disk('public')->url($listingImage);
                                }
                                $location = $house->distance_from_dssc !== null
                                    ? number_format((float) $house->distance_from_dssc, 2).' km from DSSC Main Campus'
                                    : ($house->full_address ?: ($house->address ?: ($house->display_barangay ?: 'Matti, Digos City')));
                                $highlights = $house->amenities
                                    ->take(3)
                                    ->map(fn ($amenity) => ['icon' => 'fa-circle-check', 'label' => $amenity->name]);
                                $hasBedspace = $house->roomCategories->contains(fn ($category) => \Illuminate\Support\Str::contains(strtolower((string) $category->name), ['bedspace', 'bed space']))
                                    || $house->rooms->contains(fn ($room) => \Illuminate\Support\Str::contains(strtolower((string) ($room->room_type ?? '')), ['bedspace', 'bed space']));
                                $availabilityUnit = $hasBedspace ? 'bedspace' : 'room';
                                $isVerified = strtolower((string) $house->approval_status) === 'approved';
                            @endphp
                            <article class="listing-card">
                                <div class="listing-media">
                                    <img src="{{ $listingImage }}" alt="{{ $house->name }}" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';">
                                    <div class="badge-row">
                                        @if($isVerified)
                                            <span class="badge"><i class="fas fa-circle-check" aria-hidden="true"></i> Verified</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="listing-body">
                                    <div class="listing-head">
                                        <h3>{{ $house->name }}</h3>
                                        <span class="price">{{ $price !== null ? '₱'.number_format($price, 0).'/mo' : 'Ask owner' }}</span>
                                    </div>
                                    <p class="location"><i class="fas fa-location-dot" aria-hidden="true"></i> {{ $location }}</p>
                                    <div class="meta-grid">
                                        @foreach($highlights->take(3) as $highlight)
                                            <span class="meta"><i class="fas {{ $highlight['icon'] }}" aria-hidden="true"></i> {{ $highlight['label'] }}</span>
                                        @endforeach
                                    </div>
                                    <div class="listing-footer">
                                        <span class="availability"><i class="fas fa-door-open" aria-hidden="true"></i> {{ $availableRooms }} {{ \Illuminate\Support\Str::plural($availabilityUnit, $availableRooms) }} available</span>
                                        <a class="btn btn-light" href="{{ route('login') }}">View Details <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="empty-listings legacy-listing-empty" role="status">
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
                    <h2>Find a room that fits you</h2>
                    <p>Match boarding houses to your budget, room preferences, and daily needs.</p>
                    <ul class="match-details">
                        <li class="match-detail">
                            <i class="fas fa-sliders" aria-hidden="true"></i>
                            <span><strong>Set your preferences</strong>Tell us what matters for your room and routine.</span>
                        </li>
                        <li class="match-detail">
                            <i class="fas fa-list-check" aria-hidden="true"></i>
                            <span><strong>Compare your matches</strong>Review price, location, amenities, and fit together.</span>
                        </li>
                        <li class="match-detail">
                            <i class="fas fa-comments" aria-hidden="true"></i>
                            <span><strong>Contact or reserve</strong>Reach out to owners when you find the right room.</span>
                        </li>
                    </ul>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="{{ $smartMatchUrl }}"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i> Get My Matches</a>
                        <a class="btn btn-light" href="#how-it-works">How matching works</a>
                    </div>
                </div>
                <div class="match-board" aria-label="Smart matching preview">
                    <div class="preference-strip" aria-label="Example matching preferences">
                        <span class="preference-chip"><i class="fas fa-wallet" aria-hidden="true"></i><strong>Budget</strong> ₱2,500–₱4,000</span>
                        <span class="preference-chip"><i class="fas fa-bed" aria-hidden="true"></i><strong>Room type</strong> Solo room</span>
                        <span class="preference-chip"><i class="fas fa-location-dot" aria-hidden="true"></i><strong>Location</strong> Matti</span>
                        <span class="preference-chip"><i class="fas fa-wifi" aria-hidden="true"></i><strong>Amenities</strong> WiFi, study area</span>
                    </div>
                        <div class="match-list">
                            <h3>Example matches</h3>
                            <p>Illustrative results using actual listing details when available.</p>
                            @foreach ($matchingPreviewRows as $row)
                                <div class="match-row"><div class="match-thumb"><img src="{{ $row['image'] }}" alt="" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';"></div><div><strong>{{ $row['name'] }}</strong><span>{{ $row['price'] }} · {{ $row['distance'] }}</span></div><span class="score">{{ $row['match'] }}</span></div>
                            @endforeach
                        </div>
                </div>
            </div>
        </section>

        <section class="section" id="locations">
            <div class="container">
                <div class="section-header">
                    <div class="section-title">
                        <p class="eyebrow">Nearby locations</p>
                        <h2>Find Boarding Houses Near DSSC</h2>
                        <p>Explore boarding houses in Matti, Digos City, near campus and daily essentials.</p>
                    </div>
                </div>
                <div class="locations-layout">
                    <div class="map-panel">
                        <div class="map-visual" aria-label="Matti, Digos City service area map">
                            <div id="landing-osm-map" role="application" aria-label="Interactive OpenStreetMap showing DSSC Main Campus and nearby boarding houses"></div>
                        </div>
                        <script id="landing-map-listings" type="application/json">{!! json_encode($landingMapListings ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                        <div class="location-labels" aria-label="Nearby location details">
                            <div class="location-label"><i class="fas fa-school" aria-hidden="true"></i><div><strong>DSSC Main Campus</strong><span>Origin for map distances</span></div></div>
                            <div class="location-label"><i class="fas fa-store" aria-hidden="true"></i><div><strong>Daily essentials</strong><span>Student stores in Matti</span></div></div>
                            <div class="location-label"><i class="fas fa-route" aria-hidden="true"></i><div><strong>Transport access</strong><span>Local routes nearby</span></div></div>
                        </div>
                    </div>
                    <article class="location-card">
                        <img src="{{ $recanaImage }}" alt="Recaña Boarding House room" loading="lazy" onerror="this.onerror=null;this.src='{{ asset('images/boarding-house-placeholder.svg') }}';">
                        <div class="location-card-body">
                            <h3>Live closer. Study better.</h3>
                            <p>Choose a place that keeps campus, everyday errands, and your commute within easy reach.</p>
                            <ul class="location-details">
                                <li><i class="fas fa-school" aria-hidden="true"></i><span>Near DSSC Main Campus</span></li>
                                <li><i class="fas fa-store" aria-hidden="true"></i><span>Close to student stores and daily essentials</span></li>
                                <li><i class="fas fa-bus" aria-hidden="true"></i><span>Connected to local transport routes</span></li>
                            </ul>
                            <a class="btn btn-primary" href="{{ route('user.boarding-houses.index') }}"><i class="fas fa-location-arrow" aria-hidden="true"></i> Explore Locations</a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="section section-soft how-it-works" id="how-it-works">
            <div class="container">
                <div class="section-header">
                    <div class="section-title">
                        <h2>How BoardMatch Works</h2>
                        <p>Find your boarding house in four simple steps.</p>
                    </div>
                </div>
                <div class="steps-grid">
                    <article class="step-card"><span class="step-icon"><i class="fas fa-sliders" aria-hidden="true"></i></span><h3>1. Set Preferences</h3><p>Choose your budget, room type, location, and amenities.</p></article>
                    <article class="step-card"><span class="step-icon"><i class="fas fa-sparkles" aria-hidden="true"></i></span><h3>2. Get Matches</h3><p>Discover boarding houses that match your needs.</p></article>
                    <article class="step-card"><span class="step-icon"><i class="fas fa-scale-balanced" aria-hidden="true"></i></span><h3>3. Compare Options</h3><p>Compare prices, photos, amenities, and available rooms.</p></article>
                    <article class="step-card"><span class="step-icon"><i class="fas fa-message" aria-hidden="true"></i></span><h3>4. Contact or Reserve</h3><p>Message the owner or send a room reservation request.</p></article>
                </div>
            </div>
        </section>

        <section class="get-started" id="get-started">
            <div class="container">
                <div class="get-started-header">
                    <p class="eyebrow">Get started</p>
                    <h2>Get Started with BoardMatch</h2>
                </div>
                <div class="get-started-grid">
                    <article class="get-started-card student">
                        <h3>Looking for a room?</h3>
                        <p>Find rooms that fit your budget, location, and preferences.</p>
                        <a class="btn btn-primary" href="{{ $tenantRegisterUrl }}"><i class="fas fa-user-graduate" aria-hidden="true"></i> Create Student Account</a>
                    </article>
                    <article class="get-started-card owner">
                        <h3>Own a boarding house near DSSC?</h3>
                        <p>List your property and manage rooms, tenants, and reservations.</p>
                        <a class="btn btn-light" href="{{ $ownerRegisterUrl }}"><i class="fas fa-building" aria-hidden="true"></i> Register as Owner</a>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div><a class="brand" href="#home"><img class="brand-mark" src="{{ asset('images/boardmatch-final-logo.png') }}" alt="" aria-hidden="true"><span class="brand-name">BoardMatch</span></a><p>Matching students with a brighter tomorrow through verified boarding house discovery in Matti, Digos City.</p></div>
                <div><h3>Explore</h3><ul><li><a href="#finder">Find a Room</a></li><li><a href="#listings">Listings</a></li><li><a href="#matching">Smart Match</a></li></ul></div>
                <div><h3>BoardMatch</h3><ul><li><a href="#locations">Locations</a></li><li><a href="#get-started">Get Started</a></li><li><a href="{{ route('login') }}">Sign In</a></li></ul></div>
                <div><h3>Contact</h3><ul><li>Matti, Digos City, Davao del Sur, Davao Region</li><li>Rizal Avenue, Digos City</li><li><a href="tel:+639123456789">+63 912 345 6789</a></li><li><a href="mailto:support@boardmatch.local">support@boardmatch.local</a></li></ul></div>
            </div>
            <div class="footer-bottom"><span>&copy; {{ date('Y') }} BoardMatch. All rights reserved.</span><div class="socials" aria-label="Social links"><a href="#" aria-label="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a><a href="#" aria-label="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a><a href="#" aria-label="YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a></div></div>
        </div>
    </footer>

    <div class="gallery-modal" id="gallery-modal" role="dialog" aria-modal="true" aria-labelledby="gallery-modal-title" hidden>
        <div class="gallery-modal-content">
            <button class="gallery-modal-close" type="button" data-gallery-close aria-label="Close photo gallery"><i class="fas fa-xmark" aria-hidden="true"></i></button>
            <button class="gallery-modal-prev" type="button" data-modal-prev aria-label="Previous photo"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
            <img class="gallery-modal-image" data-modal-image src="" alt="">
            <button class="gallery-modal-next" type="button" data-modal-next aria-label="Next photo"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
            <div class="gallery-modal-counter" data-modal-counter aria-live="polite"></div>
            <div class="gallery-modal-caption" id="gallery-modal-title" data-modal-caption></div>
            <a class="gallery-modal-link" data-modal-details href="#">View Boarding House <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
    </div>

    <script>
        const navLinks = document.getElementById('nav-links');
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenuToggle = document.querySelector('.mobile-hero-menu');
        const searchForm = document.getElementById('search-form');

        menuToggle?.addEventListener('click', () => {
            const open = navLinks.classList.toggle('open');
            menuToggle.setAttribute('aria-expanded', String(open));
            menuToggle.querySelector('i')?.classList.toggle('fa-bars', !open);
            menuToggle.querySelector('i')?.classList.toggle('fa-xmark', open);
        });
        mobileMenuToggle?.addEventListener('click', () => menuToggle?.click());

        navLinks?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('open');
                menuToggle?.setAttribute('aria-expanded', 'false');
                menuToggle?.querySelector('i')?.classList.add('fa-bars');
                menuToggle?.querySelector('i')?.classList.remove('fa-xmark');
            });
        });

        searchForm?.addEventListener('submit', (event) => {
            event.preventDefault();
            document.getElementById('listings')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    </script>
    <script>
        (() => {
            document.documentElement.classList.add('js');
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const revealTargets = Array.from(document.querySelectorAll(
                '.section-title h2, .step-card, .get-started-card, .location-card, .match-board'
            ));
            revealTargets.forEach((element, index) => {
                element.classList.add('reveal-on-scroll');
                element.style.transitionDelay = `${Math.min(index * 55, 275)}ms`;
            });

            if (reduceMotion || !('IntersectionObserver' in window)) {
                revealTargets.forEach((element) => element.classList.add('is-visible'));
            } else {
                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    });
                }, { threshold: 0.12 });
                revealTargets.forEach((element) => revealObserver.observe(element));
            }

            const visualContainers = Array.from(document.querySelectorAll('.hero-animation, .mobile-hero-illustration'));
            const visualVisibility = new WeakMap(visualContainers.map((element) => [element, false]));
            const animatedVisuals = Array.from(document.querySelectorAll('.hero-phone, .mini-pin'));
            const syncVisualMotion = () => {
                animatedVisuals.forEach((element) => {
                    const container = element.closest('.hero-animation, .mobile-hero-illustration');
                    const canAnimate = !document.hidden && (visualVisibility.get(container) ?? true);
                    element.style.animationPlayState = canAnimate ? 'running' : 'paused';
                });
            };
            if ('IntersectionObserver' in window) {
                const visualObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => visualVisibility.set(entry.target, entry.isIntersecting));
                    syncVisualMotion();
                }, { threshold: 0.05 });
                visualContainers.forEach((element) => visualObserver.observe(element));
            } else {
                visualContainers.forEach((element) => visualVisibility.set(element, true));
            }
            document.addEventListener('visibilitychange', syncVisualMotion);
            syncVisualMotion();
        })();
    </script>
    <script>
        (() => {
            if (document.getElementById('boarding-house-carousel')) return;
            const dataNode = document.getElementById('boarding-house-galleries');
            const modal = document.getElementById('gallery-modal');
            if (!dataNode || !modal) return;

            let galleries = [];
            try { galleries = JSON.parse(dataNode.textContent || '[]'); } catch (error) { return; }

            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const frames = Array.from(document.querySelectorAll('.gallery-frame'));
            const states = new WeakMap();
            let galleryId = 0;
            let photoIndex = 0;
            let lastTrigger = null;
            let modalOpen = false;
            let touchStartX = null;
            let touchMoved = false;
            const modalImage = modal.querySelector('[data-modal-image]');
            const modalCounter = modal.querySelector('[data-modal-counter]');
            const modalCaption = modal.querySelector('[data-modal-caption]');
            const modalDetails = modal.querySelector('[data-modal-details]');
            const modalPrev = modal.querySelector('[data-modal-prev]');
            const modalNext = modal.querySelector('[data-modal-next]');

            const getState = (frame) => {
                if (!states.has(frame)) {
                    states.set(frame, { frame, autoPlay: !reduceMotion, inView: false, hovered: false, focused: false, timer: null, fadeTimer: null });
                }
                return states.get(frame);
            };
            const currentGallery = () => galleries[galleryId] || { name: '', images: [] };
            const stopTimer = (state) => {
                if (state.timer) {
                    window.clearTimeout(state.timer);
                    state.timer = null;
                }
            };
            const canAutoplay = (state) => state.autoPlay
                && state.inView
                && !state.hovered
                && !state.focused
                && !document.hidden
                && !modalOpen
                && galleries[Number(state.frame.dataset.galleryId)]?.images?.length > 1;
            const updateToggle = (state) => {
                const toggle = state.frame.querySelector('[data-gallery-toggle]');
                if (!toggle) return;
                toggle.setAttribute('aria-label', state.autoPlay ? 'Pause slideshow' : 'Play slideshow');
                toggle.setAttribute('aria-pressed', String(!state.autoPlay));
                const icon = toggle.querySelector('i');
                icon?.classList.toggle('fa-pause', state.autoPlay);
                icon?.classList.toggle('fa-play', !state.autoPlay);
            };
            const schedule = (state) => {
                stopTimer(state);
                if (!canAutoplay(state)) return;
                state.timer = window.setTimeout(() => {
                    if (!canAutoplay(state)) return;
                    stepFrame(state.frame, 1, false);
                    schedule(state);
                }, 4000);
            };
            const renderCard = (frame, index, animate = true) => {
                const state = getState(frame);
                const gallery = galleries[Number(frame.dataset.galleryId)];
                if (!gallery?.images?.length) return;
                const nextIndex = (index + gallery.images.length) % gallery.images.length;
                const image = gallery.images[nextIndex];
                const trigger = frame.querySelector('[data-gallery-open]');
                const imageNode = trigger?.querySelector('img');
                const counter = frame.querySelector('[data-gallery-counter]');
                const update = () => {
                    if (imageNode) { imageNode.src = image.src; imageNode.alt = image.alt; }
                    if (trigger) trigger.dataset.galleryIndex = String(nextIndex);
                    if (counter) counter.textContent = `${nextIndex + 1}/${gallery.images.length}`;
                    frame.classList.remove('is-fading');
                };
                if (state.fadeTimer) window.clearTimeout(state.fadeTimer);
                if (animate && !reduceMotion) {
                    frame.classList.add('is-fading');
                    state.fadeTimer = window.setTimeout(update, 180);
                } else {
                    update();
                }
            };
            const stepFrame = (frame, delta, resetTimer = true) => {
                const trigger = frame.querySelector('[data-gallery-open]');
                if (!trigger) return;
                renderCard(frame, Number(trigger.dataset.galleryIndex) + delta);
                if (resetTimer) schedule(getState(frame));
            };
            const renderModal = () => {
                const gallery = currentGallery();
                const image = gallery.images[photoIndex];
                if (!image) return;
                modalImage.src = image.src;
                modalImage.alt = image.alt;
                modalCounter.textContent = `${photoIndex + 1}/${gallery.images.length}`;
                modalCaption.textContent = gallery.name;
                modalDetails.href = gallery.details_url;
                modalPrev.hidden = gallery.images.length < 2;
                modalNext.hidden = gallery.images.length < 2;
            };
            const moveModal = (delta) => {
                const gallery = currentGallery();
                if (gallery.images.length < 2) return;
                photoIndex = (photoIndex + delta + gallery.images.length) % gallery.images.length;
                renderModal();
                const frame = frames.find((candidate) => Number(candidate.dataset.galleryId) === galleryId);
                if (frame) {
                    renderCard(frame, photoIndex);
                    schedule(getState(frame));
                }
            };
            const refreshAll = () => frames.forEach((frame) => {
                const state = getState(frame);
                updateToggle(state);
                schedule(state);
            });
            const open = (id, index, trigger) => {
                galleryId = Number(id);
                photoIndex = Number(index) || 0;
                lastTrigger = trigger;
                modalOpen = true;
                refreshAll();
                renderModal();
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
                modal.querySelector('[data-gallery-close]')?.focus();
            };
            const close = () => {
                modal.hidden = true;
                modalOpen = false;
                document.body.style.overflow = '';
                refreshAll();
                lastTrigger?.focus();
            };

            frames.forEach((frame) => {
                const state = getState(frame);
                const trigger = frame.querySelector('[data-gallery-open]');
                trigger?.addEventListener('click', (event) => {
                    if (touchMoved) {
                        touchMoved = false;
                        event.preventDefault();
                        return;
                    }
                    open(frame.dataset.galleryId, trigger.dataset.galleryIndex, trigger);
                });
                frame.querySelector('[data-gallery-prev]')?.addEventListener('click', () => stepFrame(frame, -1));
                frame.querySelector('[data-gallery-next]')?.addEventListener('click', () => stepFrame(frame, 1));
                frame.querySelector('[data-gallery-toggle]')?.addEventListener('click', (event) => {
                    event.stopPropagation();
                    state.autoPlay = !state.autoPlay;
                    if (state.autoPlay) state.focused = false;
                    updateToggle(state);
                    schedule(state);
                });
                frame.addEventListener('mouseenter', () => { state.hovered = true; schedule(state); });
                frame.addEventListener('mouseleave', () => { state.hovered = false; schedule(state); });
                frame.addEventListener('focusin', () => { state.focused = true; schedule(state); });
                frame.addEventListener('focusout', (event) => {
                    if (!frame.contains(event.relatedTarget)) {
                        state.focused = false;
                        schedule(state);
                    }
                });
                trigger?.addEventListener('touchstart', (event) => {
                    touchStartX = event.changedTouches[0].screenX;
                    touchMoved = false;
                }, { passive: true });
                trigger?.addEventListener('touchend', (event) => {
                    if (touchStartX === null) return;
                    const delta = event.changedTouches[0].screenX - touchStartX;
                    touchStartX = null;
                    if (Math.abs(delta) < 40) return;
                    touchMoved = true;
                    stepFrame(frame, delta < 0 ? 1 : -1);
                    window.setTimeout(() => { touchMoved = false; }, 0);
                }, { passive: true });
                updateToggle(state);
            });

            const observer = 'IntersectionObserver' in window
                ? new IntersectionObserver((entries) => entries.forEach((entry) => {
                    const state = getState(entry.target);
                    state.inView = entry.isIntersecting;
                    schedule(state);
                }), { threshold: 0.25 })
                : null;
            frames.forEach((frame) => {
                if (observer) observer.observe(frame);
                else getState(frame).inView = true;
            });
            if (!observer) refreshAll();

            modal.querySelector('[data-gallery-close]')?.addEventListener('click', close);
            modalPrev.addEventListener('click', () => moveModal(-1));
            modalNext.addEventListener('click', () => moveModal(1));
            modal.addEventListener('click', (event) => { if (event.target === modal) close(); });
            document.addEventListener('visibilitychange', refreshAll);
            document.addEventListener('keydown', (event) => {
                if (modal.hidden) return;
                if (event.key === 'Escape') close();
                if (event.key === 'ArrowLeft') moveModal(-1);
                if (event.key === 'ArrowRight') moveModal(1);
            });
        })();
    </script>
    <script>
        (() => {
            const carousel = document.getElementById('boarding-house-carousel');
            if (!carousel) return;

            const viewport = carousel.querySelector('[data-carousel-viewport]');
            const cards = Array.from(carousel.querySelectorAll('.carousel-card'));
            const previous = carousel.querySelector('[data-carousel-prev]');
            const next = carousel.querySelector('[data-carousel-next]');
            const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
            const AUTOPLAY_DELAY = 5000;
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            let index = 0;
            let timer = null;
            let visible = true;
            let hovered = false;
            let focused = false;
            let paused = reduceMotion;
            let touchStartX = null;
            let touchMoved = false;

            const stop = () => {
                if (timer) window.clearTimeout(timer);
                timer = null;
            };
            const render = () => {
                if (!cards.length) return;
                cards.forEach((card) => {
                    card.classList.remove('is-current', 'is-prev', 'is-next');
                    card.style.zIndex = '0';
                    card.setAttribute('aria-hidden', 'true');
                    card.querySelector('.carousel-card-link')?.setAttribute('tabindex', '-1');
                });
                const current = cards[index];
                current.classList.add('is-current');
                current.style.zIndex = '30';
                current.setAttribute('aria-hidden', 'false');
                current.querySelector('.carousel-card-link')?.setAttribute('tabindex', '0');
                if (cards.length > 1) {
                    const nextIndex = (index + 1) % cards.length;
                    if (cards.length > 2) {
                        const previousIndex = (index - 1 + cards.length) % cards.length;
                        cards[previousIndex].classList.add('is-prev');
                        cards[nextIndex].classList.add('is-next');
                        cards[previousIndex].style.zIndex = '10';
                        cards[nextIndex].style.zIndex = '10';
                        cards[previousIndex].setAttribute('aria-hidden', 'false');
                        cards[nextIndex].setAttribute('aria-hidden', 'false');
                    } else {
                        cards[nextIndex].classList.add('is-next');
                        cards[nextIndex].style.zIndex = '10';
                        cards[nextIndex].setAttribute('aria-hidden', 'false');
                    }
                }
                dots.forEach((dot, dotIndex) => {
                    const active = dotIndex === index;
                    dot.classList.toggle('is-active', active);
                    dot.setAttribute('aria-selected', String(active));
                });
                previous && (previous.disabled = cards.length < 2);
                next && (next.disabled = cards.length < 2);
            };
            const schedule = () => {
                stop();
                if (paused || !visible || hovered || focused || document.hidden || cards.length < 2) return;
                timer = window.setTimeout(() => {
                    index = (index + 1) % cards.length;
                    render();
                    schedule();
                }, AUTOPLAY_DELAY);
            };
            const move = (delta) => {
                if (cards.length < 2) return;
                index = (index + delta + cards.length) % cards.length;
                render();
                schedule();
            };

            previous?.addEventListener('click', () => move(-1));
            next?.addEventListener('click', () => move(1));
            dots.forEach((dot, dotIndex) => dot.addEventListener('click', () => {
                index = dotIndex;
                render();
                schedule();
            }));
            carousel.addEventListener('mouseenter', () => { hovered = true; stop(); });
            carousel.addEventListener('mouseleave', () => { hovered = false; schedule(); });
            carousel.addEventListener('focusin', () => { focused = true; stop(); });
            carousel.addEventListener('focusout', (event) => {
                if (!carousel.contains(event.relatedTarget)) {
                    focused = false;
                    schedule();
                }
            });
            cards.forEach((card, cardIndex) => {
                card.querySelector('.carousel-card-link')?.addEventListener('click', (event) => {
                    if (!card.classList.contains('is-current')) {
                        event.preventDefault();
                        index = cardIndex;
                        render();
                        schedule();
                    }
                });
            });
            viewport?.addEventListener('touchstart', (event) => {
                touchStartX = event.changedTouches[0].screenX;
                touchMoved = false;
            }, { passive: true });
            viewport?.addEventListener('touchend', (event) => {
                if (touchStartX === null) return;
                const delta = event.changedTouches[0].screenX - touchStartX;
                touchStartX = null;
                if (Math.abs(delta) < 40) return;
                touchMoved = true;
                move(delta < 0 ? 1 : -1);
                window.setTimeout(() => { touchMoved = false; }, 0);
            }, { passive: true });
            viewport?.addEventListener('click', (event) => {
                if (touchMoved) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, true);
            document.addEventListener('visibilitychange', schedule);
            window.addEventListener('resize', () => { render(); schedule(); });
            if ('IntersectionObserver' in window) {
                new IntersectionObserver((entries) => {
                    visible = entries[0]?.isIntersecting ?? true;
                    schedule();
                }, { threshold: 0.2 }).observe(carousel);
            }
            render();
            schedule();
        })();
    </script>
</body>
</html>
