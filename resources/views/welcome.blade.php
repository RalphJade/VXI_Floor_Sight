<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'VXI FloorSight - Davao Centrale') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            vxi: {
                                red: '#E31B23',
                                'red-light': '#FF2E37',
                                'red-dark': '#B61219',
                                navy: '#001C3D',
                                'navy-light': '#002D62',
                                'navy-dark': '#001024',
                                midnight: '#050B14',
                                slate: '#0F1E33',
                                cyan: '#22d3ee',
                            }
                        },
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                            mono: ['JetBrains Mono', 'monospace'],
                        }
                    }
                }
            }
        </script>

        <style>
            body {
                background: linear-gradient(135deg, #001C3D 0%, #050B14 100%);
                min-height: 100vh;
                color: #fff;
            }
            .vxi-container {
                width: 100%;
                max-width: 1280px;
                margin: 0 auto;
                padding: 0 1.5rem;
            }
            .vxi-grid-bg {
                background-image: 
                    linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
                background-size: 40px 40px;
            }
            .vxi-glow-effect {
                box-shadow: 0 0 30px rgba(227, 27, 35, 0.15);
            }
            .vxi-pulse {
                animation: vxi-pulse-animation 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }
            @keyframes vxi-pulse-animation {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.5; transform: scale(1.1); }
            }
            .isometric-floor {
                transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), filter 0.3s ease, opacity 0.3s ease;
                cursor: pointer;
            }
            .isometric-floor:hover {
                transform: translate(-10px, -15px);
                filter: brightness(1.2) drop-shadow(0 15px 20px rgba(34, 211, 238, 0.3));
            }
            .isometric-floor.active-floor {
                transform: translate(-15px, -22px);
                filter: brightness(1.3) drop-shadow(0 20px 25px rgba(227, 27, 35, 0.4));
            }
        </style>
    </head>
    <body class="vxi-grid-bg relative overflow-x-hidden font-sans">
        <!-- Decorative Glowing Elements -->
        <div class="fixed top-0 right-0 w-[600px] h-[600px] bg-red-600/5 rounded-full blur-[150px] pointer-events-none"></div>
        <div class="fixed bottom-0 left-0 w-[600px] h-[600px] bg-cyan-600/5 rounded-full blur-[150px] pointer-events-none"></div>

        <!-- Navigation Bar -->
        <nav class="relative z-50 border-b border-white/10 bg-black/40 backdrop-blur-md">
            <div class="vxi-container flex items-center justify-between h-20">
                <div class="flex items-center gap-3">
                    <!-- VXI Logo Frame -->
                    <div class="flex items-center gap-3">
                        <span class="text-2xl font-black tracking-tighter text-white bg-gradient-to-r from-vxi-red to-vxi-red-dark px-3 py-1 rounded shadow-lg border border-red-500/20">
                            VXI
                        </span>
                        <div class="flex flex-col border-l border-white/20 pl-3">
                            <span class="text-[13px] font-black uppercase tracking-wider text-slate-100 leading-none">FloorSight</span>
                            <span class="text-[10px] font-bold text-vxi-red-light uppercase tracking-widest mt-1">Davao Centrale Campus</span>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Navigation & Admin Quick Actions -->
                <div class="hidden lg:flex items-center gap-6 text-xs font-bold uppercase tracking-wider text-slate-400">
                    <span class="flex items-center gap-2 text-green-400 bg-green-500/10 px-2.5 py-1 rounded-full border border-green-500/20 font-mono">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                        Campus Subnet Active
                    </span>
                    <a href="https://vxi.com/what-we-do/" target="_blank" class="hover:text-white transition-colors">Client Campaigns</a>
                    <a href="https://jobs.vxi.com/" target="_blank" class="hover:text-vxi-red-light text-slate-300 transition-colors">Davao Careers</a>
                </div>

                <!-- Session / Authentication Gateway -->
                <div class="flex items-center gap-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center bg-vxi-red hover:bg-vxi-red-light text-white text-xs font-bold uppercase tracking-widest py-3 px-5 rounded border border-red-500/30 shadow-lg shadow-red-600/20 transition-all transform hover:-translate-y-0.5">
                                Launch Command Console
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center bg-transparent hover:bg-white/5 text-white border border-white/20 hover:border-vxi-red text-xs font-bold uppercase tracking-widest py-3 px-5 rounded transition-all">
                                Agent Log In
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center bg-vxi-red hover:bg-vxi-red-light text-white text-xs font-bold uppercase tracking-widest py-3 px-5 rounded shadow-lg shadow-red-600/20 transition-all transform hover:-translate-y-0.5">
                                    Register Site
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </nav>

        <!-- Hero Section & Real-time Davao Floor Selector Stack -->
        <section class="relative z-10 pt-10 pb-16 lg:pt-20 lg:pb-24">
            <div class="vxi-container">
                <div class="grid lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left Column: Vision & Branch Portal Entrance -->
                    <div class="space-y-8 lg:col-span-6">
                        <div class="inline-flex items-center gap-2.5 px-3.5 py-2 rounded-full bg-slate-900/80 border border-white/10 text-xs font-bold uppercase tracking-wider text-vxi-red-light">
                            <span class="w-2.5 h-2.5 rounded-full bg-vxi-red vxi-pulse"></span>
                            Davao City Site Operational Core
                        </div>
                        
                        <h1 class="text-4xl lg:text-5xl font-white leading-tight tracking-tight">
                            Real-time Seat Topology for <br>
                            <span class="bg-gradient-to-r from-vxi-red via-vxi-red-light to-cyan-400 bg-clip-text text-transparent">Davao Centrale Hub</span>
                        </h1>
                        
                        <p class="text-sm lg:text-base text-slate-300 max-w-xl leading-relaxed">
                            Welcome to the unified infrastructure control room of <strong>VXI Davao Centrale</strong> (Felcris Hub). Monitor live campaign allocations, trace active network terminals, and coordinate seat occupancy across multiple state-of-the-art production floors.
                        </p>

                        <!-- Primary Hub Actions -->
                        <div class="flex flex-wrap gap-4">
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center bg-vxi-red hover:bg-vxi-red-light text-white text-sm font-bold uppercase tracking-wider py-4 px-8 rounded shadow-xl shadow-red-600/20 transition-all transform hover:-translate-y-0.5 group">
                                Enter Seat Map Control
                                <svg class="w-4 h-4 ml-2.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>
                            <button onclick="scrollToFloors()" class="inline-flex items-center justify-center bg-transparent hover:bg-white/5 text-white border-2 border-white/10 hover:border-vxi-red text-sm font-bold uppercase tracking-wider py-4 px-8 rounded transition-all">
                                Scan Floor Blueprint
                            </button>
                        </div>

                        <!-- Davao Centrale Specific Real-time Stats -->
                        <div class="grid grid-cols-3 gap-4 pt-8 border-t border-white/10">
                            <div>
                                <div class="text-2xl lg:text-3xl font-black text-white font-mono">5,200+</div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">Total Terminals</div>
                            </div>
                            <div>
                                <div class="text-2xl lg:text-3xl font-black text-white font-mono" id="live-occupancy-rate">91.8%</div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">DVO Occupancy</div>
                            </div>
                            <div>
                                <div class="text-2xl lg:text-3xl font-black text-white font-mono">16+</div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">Active Brands</div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Interactive Isometric Building Floor Stack SVG -->
                    <div class="lg:col-span-6 relative flex flex-col items-center">
                        <!-- Dynamic Floor Detail Card (Updates on hover/click) -->
                        <div class="absolute top-0 right-0 z-20 bg-vxi-slate/90 backdrop-blur-md p-4 rounded-xl border border-white/10 w-64 shadow-2xl transition-all duration-300 hidden sm:block pointer-events-none" id="floor-hud">
                            <div class="text-[10px] font-bold text-cyan-400 uppercase tracking-widest mb-1 font-mono">Selected Level</div>
                            <h4 class="text-lg font-black text-white" id="hud-floor-name">Floor 1</h4>
                            <div class="text-xs text-slate-400 mb-3" id="hud-campaign">Recruitment & Shared Services</div>
                            <div class="grid grid-cols-2 gap-2 border-t border-white/10 pt-2 font-mono text-[11px]">
                                <div>
                                    <span class="text-slate-500 block">SEATS</span>
                                    <span class="text-white font-bold" id="hud-seats">850 Total</span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block">STATUS</span>
                                    <span class="text-green-400 font-bold" id="hud-status">92% Active</span>
                                </div>
                            </div>
                        </div>

                        <!-- 3D Isometric Projection SVG -->
                        <div class="w-full max-w-[500px] aspect-[4/5] bg-vxi-slate/20 rounded-2xl border border-white/5 flex items-center justify-center p-4">
                            <svg class="w-full h-full" viewBox="0 0 500 650" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <linearGradient id="activeGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#E31B23" stop-opacity="0.9"/>
                                        <stop offset="100%" stop-color="#7A0005" stop-opacity="0.9"/>
                                    </linearGradient>
                                    <linearGradient id="inactiveGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#0F1E33" stop-opacity="0.95"/>
                                        <stop offset="100%" stop-color="#020813" stop-opacity="0.95"/>
                                    </linearGradient>
                                    <filter id="glow">
                                        <feGaussianBlur stdDeviation="6" result="blur" />
                                        <feComposite in="SourceGraphic" in2="blur" operator="over" />
                                    </filter>
                                </defs>

                                <!-- Grid Grid Floor Floor (Isometric projection grid lines) -->
                                <path d="M 50,450 L 250,350 L 450,450 L 250,550 Z" stroke="rgba(255,255,255,0.05)" stroke-width="1.5" />
                                <path d="M 50,330 L 250,230 L 450,330 L 250,430 Z" stroke="rgba(255,255,255,0.05)" stroke-width="1.5" />
                                <path d="M 50,210 L 250,110 L 450,210 L 250,310 Z" stroke="rgba(255,255,255,0.05)" stroke-width="1.5" />
                                <path d="M 50,90 L 250,-10 L 450,90 L 250,190 Z" stroke="rgba(255,255,255,0.05)" stroke-width="1.5" />

                                <!-- Vertical Connector Lines -->
                                <line x1="250" y1="90" x2="250" y2="450" stroke="rgba(34, 211, 238, 0.2)" stroke-width="2" stroke-dasharray="5 5" />
                                <line x1="50" y1="190" x2="50" y2="550" stroke="rgba(227, 27, 35, 0.2)" stroke-width="2" stroke-dasharray="5 5" />
                                <line x1="450" y1="190" x2="450" y2="550" stroke="rgba(227, 27, 35, 0.2)" stroke-width="2" stroke-dasharray="5 5" />

                                <!-- FLOOR 4 (TOP LAYER) -->
                                <g class="isometric-floor active-floor" id="floor-group-4" onclick="selectVisualFloor(4)">
                                    <!-- Extrusion Base/Wall -->
                                    <path d="M 50,150 L 50,170 L 250,270 L 250,250 Z" fill="#600A0F" />
                                    <path d="M 250,250 L 250,270 L 450,170 L 450,150 Z" fill="#901116" />
                                    <!-- Floor Surface -->
                                    <path d="M 50,150 L 250,50 L 450,150 L 250,250 Z" fill="url(#activeGradient)" stroke="#E31B23" stroke-width="2" />
                                    <!-- Dynamic Seat Matrix Grid representation inside SVG floor -->
                                    <circle cx="150" cy="130" r="4" fill="#00FFCC" />
                                    <circle cx="180" cy="140" r="4" fill="#00FFCC" />
                                    <circle cx="210" cy="150" r="4" fill="#00FFCC" class="animate-pulse" />
                                    <circle cx="230" cy="180" r="4" fill="#FF3B30" />
                                    <circle cx="260" cy="170" r="4" fill="#00FFCC" />
                                    <circle cx="290" cy="160" r="4" fill="#00FFCC" />
                                    <circle cx="320" cy="150" r="4" fill="#00FFCC" />
                                    <circle cx="350" cy="140" r="4" fill="#FF3B30" />
                                    <!-- Label -->
                                    <text x="250" y="140" font-family="JetBrains Mono" font-size="16" font-weight="900" fill="#ffffff" text-anchor="middle" filter="drop-shadow(0px 2px 4px rgba(0,0,0,0.8))">FLOOR 4</text>
                                    <text x="250" y="160" font-family="Inter" font-size="9" font-weight="bold" fill="#00ffcc" text-anchor="middle" filter="drop-shadow(0px 1px 2px rgba(0,0,0,0.8))">TELCO BAY A-F</text>
                                </g>

                                <!-- FLOOR 3 (MIDDLE-HIGH LAYER) -->
                                <g class="isometric-floor" id="floor-group-3" onclick="selectVisualFloor(3)">
                                    <path d="M 50,270 L 50,290 L 250,390 L 250,370 Z" fill="#0A1424" />
                                    <path d="M 250,370 L 250,390 L 450,290 L 450,270 Z" fill="#0F2039" />
                                    <path d="M 50,270 L 250,170 L 450,270 L 250,370 Z" fill="url(#inactiveGradient)" stroke="#22d3ee" stroke-width="1.5" />
                                    <!-- Small Terminal representations -->
                                    <circle cx="150" cy="250" r="3" fill="#22d3ee" />
                                    <circle cx="200" cy="280" r="3" fill="#22d3ee" />
                                    <circle cx="250" cy="300" r="3" fill="#FF3B30" />
                                    <circle cx="300" cy="280" r="3" fill="#22d3ee" />
                                    <text x="250" y="260" font-family="JetBrains Mono" font-size="16" font-weight="900" fill="#a1f0ff" text-anchor="middle">FLOOR 3</text>
                                    <text x="250" y="280" font-family="Inter" font-size="9" font-weight="bold" fill="#94a3b8" text-anchor="middle">FINANCIAL HUB</text>
                                </g>

                                <!-- FLOOR 2 (MIDDLE-LOW LAYER) -->
                                <g class="isometric-floor" id="floor-group-2" onclick="selectVisualFloor(2)">
                                    <path d="M 50,390 L 50,410 L 250,510 L 250,490 Z" fill="#0A1424" />
                                    <path d="M 250,490 L 250,510 L 450,410 L 450,390 Z" fill="#0F2039" />
                                    <path d="M 50,390 L 250,290 L 450,390 L 250,490 Z" fill="url(#inactiveGradient)" stroke="#22d3ee" stroke-width="1.5" />
                                    <circle cx="150" cy="370" r="3" fill="#22d3ee" />
                                    <circle cx="180" cy="390" r="3" fill="#22d3ee" />
                                    <circle cx="210" cy="410" r="3" fill="#22d3ee" />
                                    <circle cx="270" cy="410" r="3" fill="#FF3B30" />
                                    <text x="250" y="380" font-family="JetBrains Mono" font-size="16" font-weight="900" fill="#a1f0ff" text-anchor="middle">FLOOR 2</text>
                                    <text x="250" y="400" font-family="Inter" font-size="9" font-weight="bold" fill="#94a3b8" text-anchor="middle">HEALTHCARE BAY G-K</text>
                                </g>

                                <!-- FLOOR 1 (BOTTOM LAYER) -->
                                <g class="isometric-floor" id="floor-group-1" onclick="selectVisualFloor(1)">
                                    <path d="M 50,510 L 50,530 L 250,630 L 250,610 Z" fill="#0A1424" />
                                    <path d="M 250,610 L 250,630 L 450,530 L 450,511 Z" fill="#0F2039" />
                                    <path d="M 50,510 L 250,410 L 450,510 L 250,610 Z" fill="url(#inactiveGradient)" stroke="#22d3ee" stroke-width="1.5" />
                                    <circle cx="170" cy="490" r="3" fill="#22d3ee" />
                                    <circle cx="220" cy="510" r="3" fill="#22d3ee" />
                                    <circle cx="280" cy="510" r="3" fill="#22d3ee" />
                                    <text x="250" y="500" font-family="JetBrains Mono" font-size="16" font-weight="900" fill="#a1f0ff" text-anchor="middle">FLOOR 1</text>
                                    <text x="250" y="520" font-family="Inter" font-size="9" font-weight="bold" fill="#94a3b8" text-anchor="middle">HR, SOURCING & RECRUITMENT</text>
                                </g>
                            </svg>
                        </div>
                        <span class="text-xs text-slate-500 mt-3 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                            Interactive Isometric Projection. Select a floor layer to view terminal telemetry.
                        </span>
                    </div>

                </div>
            </div>
        </section>

        <!-- Davao Centrale Floor Breakdown Grid -->
        <section id="floor-breakdown-section" class="relative z-10 py-16 border-t border-white/10 bg-vxi-navy/30">
            <div class="vxi-container">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-12">
                    <div>
                        <h2 class="text-3xl font-extrabold tracking-tight mb-2">Davao Centrale <span class="text-vxi-red-light">Floor Directory</span></h2>
                        <p class="text-slate-400 text-sm max-w-xl">Comprehensive operational metrics, physical subnet details, and seat maps for each live production level.</p>
                    </div>
                    <div class="mt-4 md:mt-0 bg-slate-900 border border-white/10 p-1.5 rounded-lg flex gap-2 text-xs font-bold font-mono">
                        <span class="px-3 py-1.5 rounded bg-vxi-red text-white uppercase tracking-wider">Site ID: DVO-CENT-FC1</span>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Floor 1 Card -->
                    <div class="vxi-slate border border-white/5 rounded-xl bg-slate-900/50 p-6 hover:border-vxi-red/40 transition-all duration-300 group cursor-pointer" onclick="selectVisualFloor(1)">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-lg bg-cyan-500/10 flex items-center justify-center font-mono font-bold text-cyan-400 border border-cyan-500/20">
                                FL1
                            </div>
                            <span class="text-xs bg-slate-800 px-2 py-0.5 rounded text-slate-400 font-mono">850 Seats</span>
                        </div>
                        <h3 class="font-extrabold text-lg text-white mb-1 group-hover:text-vxi-red-light transition-colors">Shared Services</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Sourcing hub, recruiter booths, medical clinic, and human capital deployment floor.
                        </p>
                        <div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs font-mono">
                            <span class="text-slate-500">Subnet: 10.10.10.0</span>
                            <span class="text-green-400 font-bold">Active</span>
                        </div>
                    </div>

                    <!-- Floor 2 Card -->
                    <div class="vxi-slate border border-white/5 rounded-xl bg-slate-900/50 p-6 hover:border-vxi-red/40 transition-all duration-300 group cursor-pointer" onclick="selectVisualFloor(2)">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-lg bg-cyan-500/10 flex items-center justify-center font-mono font-bold text-cyan-400 border border-cyan-500/20">
                                FL2
                            </div>
                            <span class="text-xs bg-slate-800 px-2 py-0.5 rounded text-slate-400 font-mono">1,450 Seats</span>
                        </div>
                        <h3 class="font-extrabold text-lg text-white mb-1 group-hover:text-vxi-red-light transition-colors">Healthcare Services</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Dedicated HIPAA-certified physical environment hosting medical support accounts.
                        </p>
                        <div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs font-mono">
                            <span class="text-slate-500">Subnet: 10.10.12.0</span>
                            <span class="text-green-400 font-bold">Active</span>
                        </div>
                    </div>

                    <!-- Floor 3 Card -->
                    <div class="vxi-slate border border-white/5 rounded-xl bg-slate-900/50 p-6 hover:border-vxi-red/40 transition-all duration-300 group cursor-pointer" onclick="selectVisualFloor(3)">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-lg bg-cyan-500/10 flex items-center justify-center font-mono font-bold text-cyan-400 border border-cyan-500/20">
                                FL3
                            </div>
                            <span class="text-xs bg-slate-800 px-2 py-0.5 rounded text-slate-400 font-mono">1,300 Seats</span>
                        </div>
                        <h3 class="font-extrabold text-lg text-white mb-1 group-hover:text-vxi-red-light transition-colors">Financial Services</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Premium banking support campaigns, corporate security vaults, and localized servers.
                        </p>
                        <div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs font-mono">
                            <span class="text-slate-500">Subnet: 10.10.14.0</span>
                            <span class="text-green-400 font-bold">Active</span>
                        </div>
                    </div>

                    <!-- Floor 4 Card -->
                    <div class="vxi-slate border border-white/5 rounded-xl bg-slate-900/50 p-6 hover:border-vxi-red/40 transition-all duration-300 group cursor-pointer" onclick="selectVisualFloor(4)">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-10 h-10 rounded-lg bg-vxi-red/10 flex items-center justify-center font-mono font-bold text-vxi-red border border-vxi-red/20">
                                FL4
                            </div>
                            <span class="text-xs bg-vxi-red/10 px-2 py-0.5 rounded text-vxi-red-light font-mono">1,600 Seats</span>
                        </div>
                        <h3 class="font-extrabold text-lg text-white mb-1 group-hover:text-vxi-red-light transition-colors">Telco & Retail Core</h3>
                        <p class="text-xs text-slate-400 leading-relaxed mb-4">
                            Enterprise-tier broadband, sales accelerator campaigns, and live support channels.
                        </p>
                        <div class="pt-4 border-t border-white/5 flex items-center justify-between text-xs font-mono">
                            <span class="text-slate-500">Subnet: 10.10.16.0</span>
                            <span class="text-green-400 font-bold">Active</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Davao Local Campus Map and Client Boundaries -->
        <section class="relative z-10 py-16 border-t border-white/10">
            <div class="vxi-container">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl font-extrabold tracking-tight mb-4">Davao Centrale <span class="bg-gradient-to-r from-vxi-red to-cyan-400 bg-clip-text text-transparent">Network Infrastructure</span></h2>
                        <p class="text-slate-300 mb-8 leading-relaxed text-sm">
                            VXI Davao Centrale utilizes active fiber links with multi-provider redundancy (PLDT / Globe Enterprise) straight into the building core. Our dedicated floor-to-subnet boundaries are managed to guarantee maximum up-time for all campaign operations.
                        </p>
                        
                        <div class="grid grid-cols-2 gap-6 font-mono text-xs">
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 rounded-full bg-vxi-red mt-2 shrink-0"></div>
                                <div>
                                    <div class="font-bold text-white uppercase">DVO Core Switch</div>
                                    <div class="text-slate-500">10G Dual Fiber Ring</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 rounded-full bg-vxi-red mt-2 shrink-0"></div>
                                <div>
                                    <div class="font-bold text-white uppercase">PCI Compliance</div>
                                    <div class="text-slate-500">Audited Level 1 Gateways</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 rounded-full bg-vxi-red mt-2 shrink-0"></div>
                                <div>
                                    <div class="font-bold text-white uppercase">OJT Training Pods</div>
                                    <div class="text-slate-500">Dedicated Simulator Nodes</div>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 rounded-full bg-vxi-red mt-2 shrink-0"></div>
                                <div>
                                    <div class="font-bold text-white uppercase">UPS Redundancy</div>
                                    <div class="text-slate-500">100% Generators Active</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Localized Davao Centrale SVG Server Rack / Operations visualizer -->
                    <div class="relative">
                        <div class="aspect-video rounded-xl bg-gradient-to-br from-red-950/20 to-vxi-navy/40 border border-white/10 flex flex-col p-6 relative overflow-hidden font-mono">
                            <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-4">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                    <span class="text-xs text-white uppercase font-bold tracking-widest">Router Gateway: dvo-cent-rtr01</span>
                                </div>
                                <span class="text-[10px] text-slate-500">VER: 14.2.8</span>
                            </div>
                            
                            <!-- Simulated Router Diagnostics list -->
                            <div class="space-y-2 text-[11px] text-slate-400">
                                <div class="flex justify-between items-center bg-black/30 p-2 rounded">
                                    <span>VLAN-100 (RECRUITMENT)</span>
                                    <span class="text-green-400">ONLINE</span>
                                </div>
                                <div class="flex justify-between items-center bg-black/30 p-2 rounded">
                                    <span>VLAN-200 (HEALTHCARE)</span>
                                    <span class="text-green-400">ONLINE</span>
                                </div>
                                <div class="flex justify-between items-center bg-black/30 p-2 rounded">
                                    <span>VLAN-300 (FINANCIAL CORE)</span>
                                    <span class="text-green-400">ONLINE</span>
                                </div>
                                <div class="flex justify-between items-center bg-black/30 p-2 rounded">
                                    <span>VLAN-400 (TELCO PRODUCTION)</span>
                                    <span class="text-yellow-400">HIGH TRAFFIC</span>
                                </div>
                            </div>
                            
                            <!-- Abstract diagnostic background graphics -->
                            <div class="absolute bottom-2 right-4 opacity-5 pointer-events-none">
                                <svg width="150" height="150" viewBox="0 0 100 100" fill="currentColor">
                                    <path d="M10 10 H 90 V 90 H 10 Z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Dynamic Brand/Recruitment Tag: "Stand out and fit in at VXI Davao" -->
        <section class="relative z-10 py-16 border-t border-white/10 bg-gradient-to-b from-transparent to-red-950/20">
            <div class="vxi-container text-center">
                <div class="max-w-3xl mx-auto space-y-6">
                    <h2 class="text-3xl lg:text-4xl font-black tracking-tight">
                        Stand Out And <span class="bg-gradient-to-r from-vxi-red via-vxi-red-light to-orange-400 bg-clip-text text-transparent">Fit In in Davao.</span>
                    </h2>
                    <p class="text-slate-300 text-sm lg:text-base max-w-xl mx-auto leading-relaxed">
                        Become part of Davao City's elite customer service center. Join a warm, professional, and performance-driven environment focused on growth, teamwork, and employee development.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4 pt-4">
                        <a href="https://jobs.vxi.com/" target="_blank" class="inline-flex items-center justify-center bg-vxi-red hover:bg-vxi-red-light text-white text-xs font-bold uppercase tracking-widest py-3.5 px-7 rounded shadow-lg shadow-red-600/10 transition-all">
                            Apply at Davao Centrale
                        </a>
                        <a href="https://vxi.com/why-vxi/overview/mindset-and-culture/" target="_blank" class="inline-flex items-center justify-center bg-transparent hover:bg-white/5 text-white border border-white/20 hover:border-vxi-red text-xs font-bold uppercase tracking-widest py-3.5 px-7 rounded transition-all">
                            Our Culture Book
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-white/10 py-12 bg-black/40">
            <div class="vxi-container">
                <div class="grid md:grid-cols-4 gap-8 mb-8">
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-lg font-black tracking-tighter text-white bg-vxi-red px-2.5 py-0.5 rounded">
                                VXI
                            </span>
                            <span class="font-bold text-sm uppercase tracking-wider text-white">Davao Hub</span>
                        </div>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Providing world-class customer service, robust security frameworks, and localized campaign systems in Davao City, Philippines since 2013.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-extrabold mb-4 text-xs uppercase tracking-widest text-slate-300">Operational Hubs</h4>
                        <ul class="space-y-2 text-xs text-slate-400">
                            <li>Davao Centrale - Felcris</li>
                            <li>Davao SM Centrale</li>
                            <li>Davao Robinsons Hub</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-extrabold mb-4 text-xs uppercase tracking-widest text-slate-300">Console Links</h4>
                        <ul class="space-y-2 text-xs text-slate-400">
                            <li><a href="{{ url('/dashboard') }}" class="hover:text-vxi-red-light transition-colors">Floor Map Dashboard</a></li>
                            <li><a href="https://jobs.vxi.com/" class="hover:text-vxi-red-light transition-colors">Local Recruitment</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-extrabold mb-4 text-xs uppercase tracking-widest text-slate-300">Site Management</h4>
                        <ul class="space-y-2 text-xs text-slate-400">
                            <li>Felcris Centrale, Quimpo Blvd</li>
                            <li>Davao City, 8000</li>
                            <li>Contact: dvo.ops@vxi.com</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-white/5 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-slate-500">
                        &copy; {{ date('Y') }} VXI Global Solutions. Built on Laravel v{{ app()->version() }}. All rights reserved.
                    </p>
                    <div class="flex gap-6 text-xs text-slate-500">
                        <a href="https://vxi.com/privacy-notice/" target="_blank" class="hover:text-white transition-colors">Privacy Notice</a>
                        <a href="https://vxi.com/cookie-policy/" target="_blank" class="hover:text-white transition-colors">Cookie Policy</a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Bottom Layout Spacer -->
        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif

        <!-- Interactive script to coordinate visual SVG floor highlight to detail card update -->
        <script>
            const floorData = {
                1: {
                    name: "Floor 1",
                    campaign: "Recruitment & Shared Services",
                    seats: "850 Total",
                    status: "91% Active",
                    occupancy: "91.2%"
                },
                2: {
                    name: "Floor 2",
                    campaign: "Healthcare Support Hub",
                    seats: "1,450 Total",
                    status: "93% Active",
                    occupancy: "93.4%"
                },
                3: {
                    name: "Floor 3",
                    campaign: "Financial Services",
                    seats: "1,300 Total",
                    status: "89% Active",
                    occupancy: "89.1%"
                },
                4: {
                    name: "Floor 4",
                    campaign: "Telco & Retail Core",
                    seats: "1,600 Total",
                    status: "94% Active",
                    occupancy: "93.8%"
                }
            };

            function selectVisualFloor(floorNum) {
                // Remove active classes from all isometric floors
                for (let i = 1; i <= 4; i++) {
                    const floorGrp = document.getElementById('floor-group-' + i);
                    if (floorGrp) {
                        floorGrp.classList.remove('active-floor');
                        // Update border colors of surface path manually for contrast if desired
                        const surface = floorGrp.querySelector('path:nth-of-type(3)');
                        if (surface && i !== floorNum) {
                            surface.setAttribute('stroke', '#22d3ee');
                            surface.setAttribute('stroke-width', '1.5');
                        }
                    }
                }

                // Add active-floor class to clicked floor
                const selectedGrp = document.getElementById('floor-group-' + floorNum);
                if (selectedGrp) {
                    selectedGrp.classList.add('active-floor');
                    const activeSurface = selectedGrp.querySelector('path:nth-of-type(3)');
                    if (activeSurface) {
                        activeSurface.setAttribute('stroke', '#E31B23');
                        activeSurface.setAttribute('stroke-width', '2.5');
                    }
                }

                // Update information card (HUD)
                const hud = document.getElementById('floor-hud');
                const hudFloorName = document.getElementById('hud-floor-name');
                const hudCampaign = document.getElementById('hud-campaign');
                const hudSeats = document.getElementById('hud-seats');
                const hudStatus = document.getElementById('hud-status');
                const liveOccRate = document.getElementById('live-occupancy-rate');

                if (hud && floorData[floorNum]) {
                    hud.classList.remove('hidden');
                    hudFloorName.textContent = floorData[floorNum].name;
                    hudCampaign.textContent = floorData[floorNum].campaign;
                    hudSeats.textContent = floorData[floorNum].seats;
                    hudStatus.textContent = floorData[floorNum].status;
                    liveOccRate.textContent = floorData[floorNum].occupancy;
                }
            }

            function scrollToFloors() {
                const element = document.getElementById('floor-breakdown-section');
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }

            // Initialize on Load
            window.onload = function() {
                // Preset to Floor 4
                selectVisualFloor(4);
            }
        </script>
    </body>
</html>