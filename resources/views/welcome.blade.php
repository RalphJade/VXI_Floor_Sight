<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#050B14] text-slate-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'VXI FloorSight - Davao Centrale') }}</title>

        <!-- Google Fonts: Inter & JetBrains Mono -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

        <!-- Tailwind CSS CDN -->
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

        <!-- Alpine.js for interactive dashboard state -->
        <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- Lucide Icons -->
        <script src="https://unpkg.com/lucide@latest"></script>

        <style>
            [x-cloak] { display: none !important; }
            
            /* High-voltage alert animation */
            .pulse-alert {
                box-shadow: 0 0 0 0 rgba(227, 27, 35, 0.7);
                animation: pulse-red 2s infinite;
            }
            @keyframes pulse-red {
                0% {
                    transform: scale(0.98);
                    box-shadow: 0 0 0 0 rgba(227, 27, 35, 0.7);
                }
                70% {
                    transform: scale(1.02);
                    box-shadow: 0 0 0 8px rgba(227, 27, 35, 0);
                }
                100% {
                    transform: scale(0.98);
                    box-shadow: 0 0 0 0 rgba(227, 27, 35, 0);
                }
            }

            /* Retro Scanline effect for command center aesthetic */
            .scanlines {
                background: linear-gradient(
                    rgba(18, 30, 49, 0) 50%, 
                    rgba(0, 0, 0, 0.15) 50%
                ), linear-gradient(
                    90deg, 
                    rgba(227, 27, 35, 0.02), 
                    rgba(34, 211, 238, 0.01), 
                    rgba(0, 0, 255, 0.02)
                );
                background-size: 100% 4px, 6px 100%;
            }
        </style>
    </head>
    <body class="antialiased min-h-screen flex flex-col justify-between bg-[#050B14]" x-data="topologyDashboard()">

        <!-- HEADER NAVIGATION BAR -->
        <header class="border-b border-vxi-navy/30 bg-vxi-midnight/80 backdrop-blur sticky top-0 z-50 px-6 py-4">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <!-- Logo brand section -->
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-vxi-red text-white font-black text-xl tracking-tighter shadow-lg shadow-vxi-red/20">
                        VXI
                    </div>
                    <div>
                        <h1 class="text-sm font-extrabold text-white tracking-wider flex items-center">
                            DAVAO CENTRALE <span class="ml-2 text-[9px] px-2 py-0.5 rounded-full bg-vxi-red/15 text-vxi-red border border-vxi-red/30 uppercase tracking-widest font-black">SITE HUD</span>
                        </h1>
                        <p class="text-[10px] text-slate-400 font-medium">FELCRIS CENTRALE </p>
                    </div>
                </div>

                <!-- Navigation Portal Links -->
                <div class="flex items-center space-x-4">
                    @if (Route::has('login'))
                        <div class="flex items-center space-x-3">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-vxi-red hover:bg-vxi-red-dark text-white rounded-lg text-xs font-extrabold tracking-wide shadow-lg shadow-vxi-red/20 transition-all">
                                    Open IT Management Console →
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="text-xs font-bold text-slate-300 hover:text-white px-3 py-2 transition">
                                    Sign In
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="px-4 py-2 bg-vxi-navy hover:bg-vxi-navy-light border border-vxi-red/30 text-white rounded-lg text-xs font-extrabold tracking-wide transition">
                                        Request Access
                                    </a>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </header>

        <!-- MAIN LAYOUT WRAPPER -->
        <main class="flex-1 max-w-7xl w-full mx-auto p-6 space-y-10">
            
            <!-- CENTRAL HERO BANNER & TEXT (Guaranteed to render fully visible) -->
            <section class="border-b border-vxi-navy/20 pb-8 pt-4">
                <div class="space-y-4 max-w-4xl">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold bg-vxi-red/10 text-vxi-red border border-vxi-red/20 uppercase tracking-widest">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                        Live Edge Operations Monitoring
                    </span>
                    
                    <!-- HIGH-VISIBILITY TITLING -->
                    <h1 class="text-4xl md:text-5xl font-black text-white leading-tight tracking-tight">
                        Real-Time Seat Topology for <br>
                        <span class="text-vxi-red">Davao Centrale Hub</span>
                    </h1>
                    
                    <p class="text-slate-300 text-sm md:text-base leading-relaxed">
                        An interactive system layout visualizing hardware pings, subnet assignments, and logged-in user directories across Felcris Centrale's production bays. Click any building floor below to switch active console feeds instantly.
                    </p>
                </div>
            </section>

            <!-- SITE CONTROL CENTER GRID (ISOMETRIC STACK + LIVE SEAT MATRIX) -->
            <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- LEFT COLUMN: ISOMETRIC BUILDING VISUALIZER (4 FLOORS) -->
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-vxi-navy/10 border border-vxi-navy/30 rounded-2xl p-6 relative overflow-hidden flex flex-col justify-between">
                        
                        <!-- Grid decorative layout background -->
                        <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b12_1px,transparent_1px),linear-gradient(to_bottom,#1e293b12_1px,transparent_1px)] bg-[size:16px_16px] pointer-events-none"></div>

                        <div class="text-center pb-4 z-10 border-b border-vxi-navy/25">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Floor Selector</h3>
                            <p class="text-[10px] text-vxi-cyan font-semibold font-mono">DAVAO CENTRALE SITE BLUEPRINT</p>
                        </div>

                        <!-- 3D Stack Render -->
                        <div class="relative py-12 flex flex-col items-center justify-center space-y-[-16px] z-10 select-none">
                            <template x-for="floorNum in [4, 3, 2, 1]" :key="floorNum">
                                <div 
                                    @click="selectFloor(floorNum)"
                                    class="relative w-48 h-12 cursor-pointer transition-all duration-300 transform hover:-translate-y-2 hover:scale-105"
                                    style="transform: perspective(400px) rotateX(45deg) rotateZ(-30deg);"
                                >
                                    <!-- Isometric Slab Layers -->
                                    <div 
                                        :class="selectedFloor === floorNum ? 'bg-vxi-red border-vxi-red-light shadow-lg shadow-vxi-red/40' : 'bg-vxi-navy border-vxi-navy-light'"
                                        class="absolute inset-0 border-2 rounded-lg flex items-center justify-center transition-all duration-200"
                                    >
                                        <span class="text-xs font-black text-white tracking-widest" x-text="'FLOOR ' + floorNum"></span>
                                    </div>
                                    
                                    <!-- Slab Bevel Styling -->
                                    <div 
                                        :class="selectedFloor === floorNum ? 'bg-vxi-red-dark' : 'bg-vxi-navy-dark'"
                                        class="absolute left-0 right-0 -bottom-1.5 h-1.5 rounded-b-lg transition-colors"
                                    ></div>
                                </div>
                            </template>
                        </div>

                        <!-- Local Context HUD -->
                        <div class="mt-4 p-4 rounded-xl bg-vxi-midnight border border-vxi-red/20 z-10">
                            <div class="flex items-center justify-between border-b border-vxi-navy/30 pb-2 mb-2">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="'FLOOR ' + selectedFloor + ' DETAILS'"></span >
                                <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            </div>
                            <div class="space-y-2 font-mono text-[11px]">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Campaign:</span>
                                    <span class="text-white font-bold" x-text="floorData[selectedFloor].campaign"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">IP Segment:</span>
                                    <span class="text-vxi-cyan font-bold" x-text="floorData[selectedFloor].subnet"></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT COLUMN: THE DYNAMIC TOPOLOGY MATRIX GRID -->
                <div class="lg:col-span-8 border border-vxi-navy/30 bg-vxi-midnight/60 rounded-2xl overflow-hidden shadow-2xl relative">
                    
                    <!-- Sub-section Header -->
                    <div class="bg-vxi-navy/20 px-6 py-4 border-b border-vxi-navy/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-sm font-extrabold text-white uppercase tracking-wider flex items-center">
                                <i data-lucide="layout-grid" class="h-4 w-4 mr-2 text-vxi-red"></i>
                                Live Physical Layout Grid
                            </h3>
                            <p class="text-[11px] text-slate-400" x-text="'Visualizing bay topology configuration for ' + floorData[selectedFloor].campaign"></p>
                        </div>
                        
                        <!-- Mini status toggles -->
                        <div class="flex items-center space-x-3 text-[10px] font-mono bg-slate-950/40 px-3 py-1.5 rounded-lg border border-vxi-navy/25">
                            <div class="flex items-center space-x-1">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="text-slate-400">Online</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <span class="h-2 w-2 rounded-full bg-vxi-red pulse-alert"></span>
                                <span class="text-slate-400">Alert</span>
                            </div>
                        </div>
                    </div>

                    <!-- Scanline interactive map layout -->
                    <div class="p-6 scanlines flex flex-col md:flex-row gap-6">
                        
                        <!-- Bay map columns -->
                        <div class="flex-1 space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                
                                <!-- BAY A WORKSTATION MATRIX -->
                                <div class="border border-vxi-navy/20 bg-vxi-navy/5 rounded-xl p-3">
                                    <div class="flex justify-between items-center mb-2 pb-1 border-b border-vxi-navy/10">
                                        <span class="text-[9px] font-bold text-vxi-red uppercase tracking-wider">Bay A</span>
                                        <span class="text-[8px] font-mono text-slate-500" x-text="'VLAN ' + floorData[selectedFloor].vlanA"></span>
                                    </div>
                                    <div class="grid grid-cols-4 gap-2">
                                        <template x-for="node in getNodesForBay('A')" :key="node.id">
                                            <button 
                                                @click="activeNode = node"
                                                :class="getNodeClasses(node)"
                                                class="aspect-square rounded border font-mono text-[9px] font-bold flex flex-col items-center justify-center transition transform hover:scale-105 active:scale-95 relative"
                                            >
                                                <span x-text="node.name"></span>
                                                <span :class="node.status === 'active' ? 'bg-emerald-500' : (node.status === 'alert' ? 'bg-vxi-red pulse-alert' : 'bg-slate-500')" class="absolute bottom-1 right-1 h-1.5 w-1.5 rounded-full"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- BAY B WORKSTATION MATRIX -->
                                <div class="border border-vxi-navy/20 bg-vxi-navy/5 rounded-xl p-3">
                                    <div class="flex justify-between items-center mb-2 pb-1 border-b border-vxi-navy/10">
                                        <span class="text-[9px] font-bold text-vxi-red uppercase tracking-wider">Bay B</span>
                                        <span class="text-[8px] font-mono text-slate-500" x-text="'VLAN ' + floorData[selectedFloor].vlanB"></span>
                                    </div>
                                    <div class="grid grid-cols-4 gap-2">
                                        <template x-for="node in getNodesForBay('B')" :key="node.id">
                                            <button 
                                                @click="activeNode = node"
                                                :class="getNodeClasses(node)"
                                                class="aspect-square rounded border font-mono text-[9px] font-bold flex flex-col items-center justify-center transition transform hover:scale-105 active:scale-95 relative"
                                            >
                                                <span x-text="node.name"></span>
                                                <span :class="node.status === 'active' ? 'bg-emerald-500' : (node.status === 'alert' ? 'bg-vxi-red pulse-alert' : 'bg-slate-500')" class="absolute bottom-1 right-1 h-1.5 w-1.5 rounded-full"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- BAY C WORKSTATION MATRIX -->
                                <div class="border border-vxi-navy/20 bg-vxi-navy/5 rounded-xl p-3">
                                    <div class="flex justify-between items-center mb-2 pb-1 border-b border-vxi-navy/10">
                                        <span class="text-[9px] font-bold text-vxi-red uppercase tracking-wider">Bay C</span>
                                        <span class="text-[8px] font-mono text-slate-500" x-text="'VLAN ' + floorData[selectedFloor].vlanC"></span>
                                    </div>
                                    <div class="grid grid-cols-4 gap-2">
                                        <template x-for="node in getNodesForBay('C')" :key="node.id">
                                            <button 
                                                @click="activeNode = node"
                                                :class="getNodeClasses(node)"
                                                class="aspect-square rounded border font-mono text-[9px] font-bold flex flex-col items-center justify-center transition transform hover:scale-105 active:scale-95 relative"
                                            >
                                                <span x-text="node.name"></span>
                                                <span :class="node.status === 'active' ? 'bg-emerald-500' : (node.status === 'alert' ? 'bg-vxi-red pulse-alert' : 'bg-slate-500')" class="absolute bottom-1 right-1 h-1.5 w-1.5 rounded-full"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- Right Details Context Pane -->
                        <div class="w-full md:w-64 shrink-0">
                            <div class="border border-vxi-navy/30 bg-slate-950/90 p-4 rounded-xl h-full flex flex-col justify-between space-y-4">
                                
                                <!-- Empty state details info -->
                                <div x-show="!activeNode" class="text-center py-8 flex flex-col items-center justify-center space-y-2">
                                    <div class="h-8 w-8 rounded-full border border-vxi-navy/20 bg-vxi-navy/5 flex items-center justify-center text-slate-500">
                                        <i data-lucide="info" class="h-4 w-4"></i>
                                    </div>
                                    <p class="text-[10px] font-bold text-white uppercase tracking-wider">Node Inspector</p>
                                    <p class="text-[9px] text-slate-500 max-w-[150px]">Select any layout node to audit IP details.</p>
                                </div>

                                <!-- Dynamic telemetry detail readout -->
                                <div x-show="activeNode" x-cloak class="space-y-3 text-[11px] font-mono">
                                    <div class="border-b border-vxi-navy/25 pb-2">
                                        <span class="text-[9px] text-vxi-red font-bold uppercase tracking-widest" x-text="'BAY ' + activeNode.bay + ' SEAT'"></span >
                                        <h4 class="text-base font-black text-white" x-text="activeNode.name"></h4>
                                    </div>

                                    <div class="space-y-1.5 text-slate-300">
                                        <div class="flex justify-between">
                                            <span class="text-slate-500">Host:</span>
                                            <span class="text-white font-bold" x-text="activeNode.hostname"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500">IP Address:</span>
                                            <span class="text-vxi-cyan font-bold" x-text="activeNode.ip"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-slate-500">Operator:</span>
                                            <span class="text-white font-bold truncate max-w-[100px]" x-text="activeNode.agent"></span>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-vxi-navy/20">
                                        <button @click="rebootNode()" class="w-full py-1.5 bg-vxi-red text-white text-[10px] font-bold rounded hover:bg-vxi-red-dark transition">
                                            Trigger Force Reboot
                                        </button>
                                    </div>
                                </div>

                                <div class="border-t border-vxi-navy/25 pt-2">
                                    <div class="flex justify-between text-[8px] font-semibold text-slate-500 uppercase">
                                        <span>System Telemetry</span>
                                        <span class="text-emerald-400">ACTIVE</span>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

            </section>

        </main>

        <!-- FOOTER BRANDING BANNER -->
        <footer class="border-t border-vxi-navy/30 bg-vxi-midnight/80 py-8 px-6 mt-12 shrink-0">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6 text-xs text-slate-400">
                <div class="flex items-center space-x-3">
                    <div class="text-vxi-red font-bold tracking-widest text-sm">VXI SOLUTIONS</div>
                    <span class="text-slate-700">|</span>
                    <p>© {{ date('Y') }} Ralph Jade Omega. All rights reserved.</p>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="hover:text-vxi-red transition-all">Careers</a>
                    <a href="#" class="hover:text-vxi-red transition-all">Privacy Agreement</a>
                    <a href="#" class="hover:text-vxi-red transition-all">Site Security</a>
                </div>
            </div>
        </footer>

        <!-- INTERACTIVE TOAST SYSTEM NOTIFICATION -->
        <div x-show="toast.visible" x-cloak x-transition class="fixed bottom-6 right-6 z-50 max-w-sm rounded-xl border border-vxi-navy/40 bg-[#001024] p-4 shadow-2xl flex items-start space-x-3">
            <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-400 border border-emerald-500/20">
                <i data-lucide="check-circle" class="h-5 w-5"></i>
            </div>
            <div>
                <h4 class="text-xs font-extrabold text-white uppercase tracking-wider" x-text="toast.title"></h4>
                <p class="text-[11px] text-slate-400 mt-1" x-text="toast.message"></p>
            </div>
        </div>

        <!-- SCRIPTS CONTROLLER LOGIC -->
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                lucide.createIcons();
            });

            function topologyDashboard() {
                return {
                    selectedFloor: 4,
                    activeNode: null,
                    toast: { visible: false, title: "", message: "" },

                    floorData: {
                        1: { name: "Floor 1 - HR & Sourcing", campaign: "Recruiter Hub & Training Bay", subnet: "10.100.1.0/24", vlanA: "10", vlanB: "12", vlanC: "15" },
                        2: { name: "Floor 2 - Tech Support Hub", campaign: "Telecom Customer Support", subnet: "10.100.2.0/24", vlanA: "20", vlanB: "22", vlanC: "25" },
                        3: { name: "Floor 3 - AT&T Operations", campaign: "AT&T Billing & Support", subnet: "10.100.3.0/24", vlanA: "30", vlanB: "32", vlanC: "35" },
                        4: { name: "Floor 4 - Comcast Premium", campaign: "Comcast CX (E360 & Billing)", subnet: "10.100.4.0/24", vlanA: "40", vlanB: "42", vlanC: "45" }
                    },

                    nodes: [
                        // Floor 4 Nodes
                        { id: 1, name: "A1", bay: "A", floor: 4, hostname: "VXI-DVO-0401", ip: "10.100.4.10", mac: "00:E0:4C:68:01:A1", status: "active", agent: "John Michael" },
                        { id: 2, name: "A2", bay: "A", floor: 4, hostname: "VXI-DVO-0402", ip: "10.100.4.11", mac: "00:E0:4C:68:01:A2", status: "active", agent: "Sarah Geronimo" },
                        { id: 3, name: "A3", bay: "A", floor: 4, hostname: "VXI-DVO-0403", ip: "10.100.4.12", mac: "00:E0:4C:68:01:A3", status: "alert", agent: "Reynald Mac" },
                        { id: 4, name: "A4", bay: "A", floor: 4, hostname: "VXI-DVO-0404", ip: "10.100.4.13", mac: "00:E0:4C:68:01:A4", status: "active", agent: "Angela Perez" },
                        { id: 5, name: "B1", bay: "B", floor: 4, hostname: "VXI-DVO-0405", ip: "10.100.4.20", mac: "00:E0:4C:68:01:B1", status: "active", agent: "Julius Caesar" },
                        { id: 6, name: "B2", bay: "B", floor: 4, hostname: "VXI-DVO-0406", ip: "10.100.4.21", mac: "00:E0:4C:68:01:B2", status: "empty", agent: "Unassigned" },
                        { id: 7, name: "B3", bay: "B", floor: 4, hostname: "VXI-DVO-0407", ip: "10.100.4.22", mac: "00:E0:4C:68:01:B3", status: "active", agent: "Rica Mae" },
                        { id: 8, name: "B4", bay: "B", floor: 4, hostname: "VXI-DVO-0408", ip: "10.100.4.23", mac: "00:E0:4C:68:01:B4", status: "active", agent: "Ephraim Jose" },
                        { id: 9, name: "C1", bay: "C", floor: 4, hostname: "VXI-DVO-0409", ip: "10.100.4.30", mac: "00:E0:4C:68:01:C1", status: "active", agent: "Jefferson Gab" },
                        { id: 10, name: "C2", bay: "C", floor: 4, hostname: "VXI-DVO-0410", ip: "10.100.4.31", mac: "00:E0:4C:68:01:C2", status: "alert", agent: "Vanessa Claire" },
                        { id: 11, name: "C3", bay: "C", floor: 4, hostname: "VXI-DVO-0411", ip: "10.100.4.32", mac: "00:E0:4C:68:01:C3", status: "active", agent: "Gervin Chris" },
                        { id: 12, name: "C4", bay: "C", floor: 4, hostname: "VXI-DVO-0412", ip: "10.100.4.33", mac: "00:E0:4C:68:01:C4", status: "active", agent: "Aaron Joshua" },

                        // Floor 3 Nodes
                        { id: 13, name: "A1", bay: "A", floor: 3, hostname: "VXI-DVO-0301", ip: "10.100.3.10", mac: "00:E0:4C:68:02:A1", status: "active", agent: "Leandro Al" },
                        { id: 14, name: "A2", bay: "A", floor: 3, hostname: "VXI-DVO-0302", ip: "10.100.3.11", mac: "00:E0:4C:68:02:A2", status: "active", agent: "Ronalyn Evan" },
                        { id: 15, name: "A3", bay: "A", floor: 3, hostname: "VXI-DVO-0303", ip: "10.100.3.12", mac: "00:E0:4C:68:02:A3", status: "empty", agent: "Unassigned" },
                        { id: 16, name: "A4", bay: "A", floor: 3, hostname: "VXI-DVO-0304", ip: "10.100.3.13", mac: "00:E0:4C:68:02:A4", status: "active", agent: "Kimberly Faye" },
                        { id: 17, name: "B1", bay: "B", floor: 3, hostname: "VXI-DVO-0305", ip: "10.100.3.20", mac: "00:E0:4C:68:02:B1", status: "active", agent: "Dave Chappelle" },
                        { id: 18, name: "B2", bay: "B", floor: 3, hostname: "VXI-DVO-0306", ip: "10.100.3.21", mac: "00:E0:4C:68:02:B2", status: "active", agent: "Christian Paul" },
                        { id: 19, name: "B3", bay: "B", floor: 3, hostname: "VXI-DVO-0307", ip: "10.100.3.22", mac: "00:E0:4C:68:02:B3", status: "alert", agent: "Nathaniel Smith" },
                        { id: 20, name: "B4", bay: "B", floor: 3, hostname: "VXI-DVO-0308", ip: "10.100.3.23", mac: "00:E0:4C:68:02:B4", status: "active", agent: "Patricia Maye" },
                        { id: 21, name: "C1", bay: "C", floor: 3, hostname: "VXI-DVO-0309", ip: "10.100.3.30", mac: "00:E0:4C:68:02:C1", status: "active", agent: "Jasmine Tol" },
                        { id: 22, name: "C2", bay: "C", floor: 3, hostname: "VXI-DVO-0310", ip: "10.100.3.31", mac: "00:E0:4C:68:02:C2", status: "active", agent: "John Robert" },
                        { id: 23, name: "C3", bay: "C", floor: 3, hostname: "VXI-DVO-0311", ip: "10.100.3.32", mac: "00:E0:4C:68:02:C3", status: "empty", agent: "Unassigned" },
                        { id: 24, name: "C4", bay: "C", floor: 3, hostname: "VXI-DVO-0312", ip: "10.100.3.33", mac: "00:E0:4C:68:02:C4", status: "active", agent: "Maria Kristina" },

                        // Floor 2 Nodes
                        { id: 25, name: "A1", bay: "A", floor: 2, hostname: "VXI-DVO-0201", ip: "10.100.2.10", mac: "00:E0:4C:68:03:A1", status: "active", agent: "Rica Mae" },
                        { id: 26, name: "A2", bay: "A", floor: 2, hostname: "VXI-DVO-0202", ip: "10.100.2.11", mac: "00:E0:4C:68:03:A2", status: "active", agent: "Reynald Mac" },
                        { id: 27, name: "A3", bay: "A", floor: 2, hostname: "VXI-DVO-0203", ip: "10.100.2.12", mac: "00:E0:4C:68:03:A3", status: "active", agent: "John Michael" },
                        { id: 28, name: "A4", bay: "A", floor: 2, hostname: "VXI-DVO-0204", ip: "10.100.2.13", mac: "00:E0:4C:68:03:A4", status: "empty", agent: "Unassigned" },
                        { id: 29, name: "B1", bay: "B", floor: 2, hostname: "VXI-DVO-0205", ip: "10.100.2.20", mac: "00:E0:4C:68:03:B1", status: "active", agent: "Julius Caesar" },
                        { id: 30, name: "B2", bay: "B", floor: 2, hostname: "VXI-DVO-0206", ip: "10.100.2.21", mac: "00:E0:4C:68:03:B2", status: "active", agent: "Sarah Gero" },
                        { id: 31, name: "B3", bay: "B", floor: 2, hostname: "VXI-DVO-0207", ip: "10.100.2.22", mac: "00:E0:4C:68:03:B3", status: "alert", agent: "Nathaniel Smith" },
                        { id: 32, name: "B4", bay: "B", floor: 2, hostname: "VXI-DVO-0208", ip: "10.100.2.23", mac: "00:E0:4C:68:03:B4", status: "active", agent: "Patricia Maye" },
                        { id: 33, name: "C1", bay: "C", floor: 2, hostname: "VXI-DVO-0209", ip: "10.100.2.30", mac: "00:E0:4C:68:03:C1", status: "empty", agent: "Unassigned" },
                        { id: 34, name: "C2", bay: "C", floor: 2, hostname: "VXI-DVO-0210", ip: "10.100.2.31", mac: "00:E0:4C:68:03:C2", status: "active", agent: "Maria Kristina" },
                        { id: 35, name: "C3", bay: "C", floor: 2, hostname: "VXI-DVO-0211", ip: "10.100.2.32", mac: "00:E0:4C:68:03:C3", status: "active", agent: "Aaron Joshua" },
                        { id: 36, name: "C4", bay: "C", floor: 2, hostname: "VXI-DVO-0212", ip: "10.100.2.33", mac: "00:E0:4C:68:03:C4", status: "active", agent: "Dave Chappelle" },

                        // Floor 1 Nodes
                        { id: 37, name: "A1", bay: "A", floor: 1, hostname: "VXI-DVO-0101", ip: "10.100.1.10", mac: "00:E0:4C:68:04:A1", status: "active", agent: "Christian Paul" },
                        { id: 38, name: "A2", bay: "A", floor: 1, hostname: "VXI-DVO-0102", ip: "10.100.1.11", mac: "00:E0:4C:68:04:A2", status: "active", agent: "Angela Perez" },
                        { id: 39, name: "A3", bay: "A", floor: 1, hostname: "VXI-DVO-0103", ip: "10.100.1.12", mac: "00:E0:4C:68:04:A3", status: "active", agent: "Jefferson Gab" },
                        { id: 40, name: "A4", bay: "A", floor: 1, hostname: "VXI-DVO-0104", ip: "10.100.1.13", mac: "00:E0:4C:68:04:A4", status: "empty", agent: "Unassigned" },
                        { id: 41, name: "B1", bay: "B", floor: 1, hostname: "VXI-DVO-0105", ip: "10.100.1.20", mac: "00:E0:4C:68:04:B1", status: "active", agent: "Vanessa Claire" },
                        { id: 42, name: "B2", bay: "B", floor: 1, hostname: "VXI-DVO-0106", ip: "10.100.1.21", mac: "00:E0:4C:68:04:B2", status: "active", agent: "Gervin Chris" },
                        { id: 43, name: "B3", bay: "B", floor: 1, hostname: "VXI-DVO-0107", ip: "10.100.1.22", mac: "00:E0:4C:68:04:B3", status: "active", agent: "Leandro Al" },
                        { id: 44, name: "B4", bay: "B", floor: 1, hostname: "VXI-DVO-0108", ip: "10.100.1.23", mac: "00:E0:4C:68:04:B4", status: "empty", agent: "Unassigned" },
                        { id: 45, name: "C1", bay: "C", floor: 1, hostname: "VXI-DVO-0109", ip: "10.100.1.30", mac: "00:E0:4C:68:04:C1", status: "alert", agent: "Ronalyn Evan" },
                        { id: 46, name: "C2", bay: "C", floor: 1, hostname: "VXI-DVO-0110", ip: "10.100.1.31", mac: "00:E0:4C:68:04:C2", status: "active", agent: "Kimberly Faye" },
                        { id: 47, name: "C3", bay: "C", floor: 1, hostname: "VXI-DVO-0111", ip: "10.100.1.32", mac: "00:E0:4C:68:04:C3", status: "active", agent: "Jasmine Tol" },
                        { id: 48, name: "C4", bay: "C", floor: 1, hostname: "VXI-DVO-0112", ip: "10.100.1.33", mac: "00:E0:4C:68:04:C4", status: "active", agent: "John Robert" }
                    ],

                    selectFloor(floorNum) {
                        this.selectedFloor = floorNum;
                        this.activeNode = null;
                        this.showToast("Telemetry Swapped", `Console connected to Floor ${floorNum}`);
                    },

                    getNodesForBay(bayLetter) {
                        return this.nodes.filter(n => n.floor === this.selectedFloor && n.bay === bayLetter);
                    },

                    getNodeClasses(node) {
                        let base = "border text-[10px] p-2 relative transition duration-150 ";
                        
                        if (this.activeNode && this.activeNode.id === node.id) {
                            base += "ring-2 ring-vxi-red ring-offset-1 ring-offset-slate-950 scale-105 border-vxi-red ";
                        }

                        if (node.status === 'active') {
                            return base + "bg-emerald-950/20 border-emerald-500/30 text-emerald-400 hover:bg-emerald-900/30";
                        } else if (node.status === 'alert') {
                            return base + "bg-red-950/20 border-red-500/30 text-vxi-red hover:bg-red-900/30 pulse-alert";
                        } else {
                            return base + "bg-slate-900/30 border-slate-700/20 text-slate-500 hover:bg-slate-800/20";
                        }
                    },

                    rebootNode() {
                        if (!this.activeNode) return;
                        this.activeNode.status = 'active';
                        this.showToast("Reboot Initialized", `Power signal cycled on station ${this.activeNode.hostname}`);
                    },

                    showToast(title, message) {
                        this.toast.title = title;
                        this.toast.message = message;
                        this.toast.visible = true;
                        setTimeout(() => {
                            this.toast.visible = false;
                        }, 3000);
                    }
                };
            }
        </script>
    </body>
</html>