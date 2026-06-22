<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#050B14] text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'VXI FloorSight - Dashboard Map Editor') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

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

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        /* Scanline Overlay matching Landing & Login */
        .dashboard-scanlines {
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

        /* SVG Node Drag Cursor & hover aesthetics */
        .movable-station {
            cursor: move;
            transition: transform 0.1s ease, filter 0.15s ease;
        }
        .movable-station:hover {
            filter: brightness(1.25);
            transform: scale(1.02);
        }
    </style>
</head>
<body class="antialiased h-full flex flex-col justify-between bg-[#050B14] select-none" x-data="dashboardController()">

    <header class="bg-vxi-navy-dark border-b border-vxi-navy/30 px-6 py-4 flex justify-between items-center shrink-0 shadow-lg relative z-20">

        <div class="hidden"></div>

        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-vxi-red text-white font-black text-xl tracking-tighter shadow-lg shadow-vxi-red/20">
                VXI
            </div>
            <div>
                <h1 class="text-sm font-extrabold text-white tracking-wider flex items-center">
                    FloorSight Terminal Map <span class="ml-2 text-[9px] px-2 py-0.5 rounded-full bg-vxi-red/15 text-vxi-red border border-vxi-red/30 uppercase tracking-widest font-black text-center">Structure Editor</span>
                </h1>
                <p class="text-[10px] text-slate-400 font-mono">DAVAO CENTRALE HUBS • LOCAL GEOMETRICS</p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <span class="text-xs text-slate-400 font-mono">WORKSPACE: <span class="text-vxi-cyan font-bold" x-text="selectedFloorName"></span></span>
            <div class="h-6 w-px bg-vxi-navy/30"></div>
            <a href="/" class="text-xs font-bold text-slate-400 hover:text-white transition">Back to Main HUD</a>
        </div>
    </header>

    <div class="flex-1 flex overflow-hidden relative">

        <aside x-cloak x-show="leftSidebarOpen" class="w-72 border-r border-vxi-navy/30 bg-vxi-navy-dark/60 p-5 flex flex-col justify-between shrink-0 overflow-y-auto z-10">

            <div class="space-y-6">

                
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider flex items-center">
                        <i data-lucide="layers" class="h-4 w-4 mr-1.5 text-vxi-red"></i>
                        Floor Layouts
                    </h3>
                    <div class="flex items-center gap-2">
                        <button @click="openCreateFloorModal()" class="text-[9px] font-black text-vxi-cyan hover:text-cyan-300 flex items-center uppercase tracking-wider">
                            <i data-lucide="plus" class="h-3 w-3 mr-0.5"></i> Add Floor
                        </button>
                        <button @click="leftSidebarOpen = false" class="text-slate-500 hover:text-slate-300 p-1" title="Close Floors Panel">
                            <i data-lucide="x" class="h-3.5 w-3.5"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <template x-for="floor in floors" :key="floor.id">
                        <div 
                            :class="parseInt(selectedFloorId, 10) === parseInt(floor.id, 10) ? 'bg-vxi-red/10 border-vxi-red/40' : 'bg-slate-950/40 border-vxi-navy/20 hover:border-vxi-navy/40'"
                            class="group p-3 rounded-xl border flex items-center justify-between transition duration-150 cursor-pointer"
                            @click="selectFloor(floor)"
                        >
                            <div class="flex-1">
                                <h4 class="text-xs font-extrabold text-white" x-text="floor.name"></h4>
                                <p class="text-[9px] font-mono text-slate-500 uppercase mt-0.5" x-text="': ' + floor.campaign"></p>
                            </div>
                            <div class="opacity-0 group-hover:opacity-100 flex items-center gap-1.5 transition duration-150">
                                <button @click.stop="openEditFloorModal(floor)" class="text-slate-400 hover:text-white p-0.5">
                                    <i data-lucide="edit-3" class="h-3 w-3"></i>
                                </button>
                                <button @click.stop="confirmDeleteFloor(floor)" class="text-slate-500 hover:text-vxi-red p-0.5">
                                    <i data-lucide="trash-2" class="h-3 w-3"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="pt-4 border-t border-vxi-navy/20 space-y-3">
                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Operational Station Types</h4>
                    
                    <div class="space-y-2 text-xs">
                        <div class="flex items-center gap-2 p-2 bg-slate-950/50 rounded-lg border border-vxi-navy/25">
                            <span class="h-3 w-3 rounded bg-emerald-500 border border-emerald-400"></span>
                            <div>
                                <p class="font-extrabold text-white">Agent Station</p>
                                <p class="text-[9px] text-slate-500">Regular BPO workspace</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-slate-950/50 rounded-lg border border-vxi-navy/25">
                            <span class="h-3 w-3 rounded bg-vxi-cyan border border-cyan-400"></span>
                            <div>
                                <p class="font-extrabold text-white">Support Station</p>
                                <p class="text-[9px] text-slate-500">SME / QA / Real-Time Support</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-slate-950/50 rounded-lg border border-vxi-navy/25">
                            <span class="h-3 w-3 rounded bg-vxi-red border border-vxi-red-light animate-pulse"></span>
                            <div>
                                <p class="font-extrabold text-white">OM Station</p>
                                <p class="text-[9px] text-slate-500">Operations Manager Desk</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="border-t border-vxi-navy/20 pt-4">
                <div class="bg-slate-950/70 p-3 rounded-xl border border-vxi-navy/35 text-[9px] text-slate-500 space-y-1 font-mono">
                    <div class="flex justify-between">
                        <span>Map Grid Area:</span>
                        <span class="text-white font-bold">2000 x 1000 px</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Topology Logic:</span>
                        <span class="text-vxi-cyan">Interactive Drag</span>
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex-1 p-6 flex flex-col justify-between overflow-y-auto dashboard-scanlines relative z-0">

            <!-- Sidebars toggle buttons (kept outside main text flow, absolute inside relative main panel) -->
            <div class="absolute top-6 left-6 right-6 z-20 pointer-events-none">

                <div class="flex justify-between pointer-events-auto">
                    <!-- Left sidebar toggle (Floor selector) -->
                    <div class="flex gap-2">
                        <button @click="leftSidebarOpen = true" x-show="!leftSidebarOpen" class="text-[10px] px-3 py-1.5 bg-vxi-navy-dark/80 hover:bg-vxi-navy-dark text-slate-200 border border-vxi-navy/30 rounded-lg shadow">
                            <i data-lucide="layers" class="inline-block h-3.5 w-3.5 mr-1"></i> Floors
                        </button>
                        <button @click="leftSidebarOpen = false" x-show="leftSidebarOpen" class="text-[10px] px-3 py-1.5 bg-vxi-navy-dark/80 hover:bg-vxi-navy-dark text-slate-200 border border-vxi-navy/30 rounded-lg shadow">
                            <i data-lucide="chevron-left" class="inline-block h-3.5 w-3.5 mr-1"></i> Close
                        </button>
                    </div>

                    <!-- Right sidebar toggle (Asset inspector) -->
                    <div class="flex gap-2">
                        <button @click="rightSidebarOpen = false" x-show="rightSidebarOpen" class="text-[10px] px-3 py-1.5 bg-vxi-navy-dark/80 hover:bg-vxi-navy-dark text-slate-200 border border-vxi-navy/30 rounded-lg shadow">
                            <i data-lucide="chevron-right" class="inline-block h-3.5 w-3.5 mr-1"></i> Close
                        </button>
                        <button @click="rightSidebarOpen = true" x-show="!rightSidebarOpen" class="text-[10px] px-3 py-1.5 bg-vxi-navy-dark/80 hover:bg-vxi-navy-dark text-slate-200 border border-vxi-navy/30 rounded-lg shadow">
                            <i data-lucide="info" class="inline-block h-3.5 w-3.5 mr-1"></i> Inspect
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="space-y-6">

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-lg font-black text-white flex items-center tracking-wide uppercase">
                            <i data-lucide="map" class="h-5 w-5 mr-2 text-vxi-red"></i>
                            Centrale Site Map Editor
                        </h2>
                        <p class="text-xs text-slate-400">
                            Line of Business Group: <span class="text-vxi-cyan font-bold" x-text="getCampaignName()"></span>
                        </p>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative w-full sm:w-48">
                            <input 
                                type="text" 
                                placeholder="Search Workstations..." 
                                x-model="searchQuery" 
                                class="w-full px-3 py-1.5 bg-slate-950 border border-vxi-navy/30 rounded-lg text-xs text-slate-300 focus:outline-none focus:border-vxi-red transition font-mono"
                            >
                        </div>
                        <button @click="openCreateAssetModal()" class="px-3.5 py-1.5 bg-vxi-red hover:bg-vxi-red-dark text-white text-xs font-black uppercase tracking-wider rounded-lg flex items-center gap-1.5 transition shrink-0 shadow-lg shadow-vxi-red/20">
                            <i data-lucide="plus" class="h-3.5 w-3.5"></i> Add Station
                        </button>
                    </div>
                </div>

                <div class="relative bg-slate-950 border border-vxi-navy/30 rounded-2xl p-4 shadow-2xl overflow-hidden flex flex-col items-center justify-center">
                    
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(0,28,61,0.12)_1px,transparent_1px),linear-gradient(to_bottom,rgba(0,28,61,0.12)_1px,transparent_1px)] bg-[size:20px_20px] pointer-events-none"></div>

                        <svg viewBox="0 0 2000 1000" class="w-full h-auto max-w-[1600px] aspect-[2/1] relative z-10">
                        

                        <template x-for="asset in filteredAssets" :key="'map-' + asset.id + '-' + mapRenderKey">
                            <g 
                                class="movable-station" 
                                @click="selectAsset(asset)"
                            >
                                <rect 
                                    :x="asset.x" 
                                    :y="asset.y" 
                                    width="46" 
                                    height="34" 
                                    rx="5" 
                                    :fill="selectedAsset?.id === asset.id ? '#E31B23' : '#001024'" 
                                    :fill-opacity="selectedAsset?.id === asset.id ? '0.2' : '0.9'"
                                    :stroke="selectedAsset?.id === asset.id ? '#FF2E37' : (asset.type === 'agent' ? '#10b981' : (asset.type === 'support' ? '#22d3ee' : '#E31B23'))" 
                                    stroke-width="2"
                                />

                                <circle 
                                    :cx="asset.x + 9" 
                                    :cy="asset.y + 10" 
                                    r="4" 
                                    :fill="asset.type === 'agent' ? '#10b981' : (asset.type === 'support' ? '#22d3ee' : '#E31B23')"
                                />

                                <text 
                                    :x="asset.x + 23" 
                                    :y="asset.y + 26" 
                                    fill="#f1f5f9" 
                                    font-size="9" 
                                    font-family="monospace" 
                                    font-weight="black"
                                    text-anchor="middle"
                                    x-text="asset.name"
                                ></text>
                            </g>
                        </template>

                    </svg>

                    <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between text-[9px] font-mono bg-slate-950/80 px-4 py-2 border border-vxi-navy/35 rounded-xl">
                        <span class="text-slate-400">💡 TIP: CLICK ANY STATION ON MAP THEN ADJUST COORDINATES TO ALTER MAP GEOMETRICS</span>
                        <span class="text-vxi-cyan">ACTIVE COORDINATE LAYER</span>
                    </div>

                </div>

            </div>

            <div class="mt-6 p-4 rounded-xl border border-vxi-navy/20 bg-vxi-navy/10 flex items-center justify-between text-xs text-slate-400 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Geometric Layout Engine is active. Adjust workstation X & Y coordinates inside details deck to move.</span>
                </div>
                <span class="font-mono text-[9px]">VXI Sentinel v2.7</span>
            </div>

        </main>

        <aside x-cloak x-show="rightSidebarOpen" class="w-80 border-l border-vxi-navy/30 bg-[#001024]/60 p-5 flex flex-col justify-between shrink-0 overflow-y-auto z-10">

            
            <div x-show="!selectedAsset" class="flex flex-col items-center justify-center text-center h-full text-slate-500 relative">
                <!-- Close Button in placeholder state -->
                <button @click="rightSidebarOpen = false" class="absolute top-0 right-0 text-slate-500 hover:text-slate-300 p-1" title="Close Panel">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
                <i data-lucide="info" class="h-10 w-10 text-slate-700 mb-2"></i>
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Asset Inspector</h4>
                <p class="text-[10px] max-w-[180px] mt-1">Select any workstation on the map canvas blueprint to inspect properties and customize coordinates.</p>
            </div>

            <div x-show="selectedAsset" x-cloak class="space-y-6">
                
                <div class="flex items-center justify-between border-b border-vxi-navy/20 pb-3">
                    <div>
                        <span class="text-[8px] font-black px-2 py-0.5 rounded border uppercase tracking-widest"
                              :class="selectedAsset?.type === 'agent' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : (selectedAsset?.type === 'support' ? 'bg-cyan-500/10 border-cyan-500/20 text-vxi-cyan' : 'bg-vxi-red/10 border-vxi-red/20 text-vxi-red')"
                              x-text="selectedAsset?.type + ' Station'"></span>
                        <h3 class="text-xl font-extrabold text-white mt-1" x-text="'Workspace ' + selectedAsset?.name"></h3>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button @click="selectedAsset = null" class="text-slate-500 hover:text-slate-300 p-1" title="Deselect Asset">
                            <i data-lucide="minus-circle" class="h-4 w-4"></i>
                        </button>
                        <button @click="rightSidebarOpen = false; selectedAsset = null" class="text-slate-500 hover:text-vxi-red p-1" title="Close Inspector">
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-2 text-[11px] font-mono bg-slate-950/60 p-4 rounded-xl border border-vxi-navy/25">
                    <div class="flex justify-between border-b border-vxi-navy/15 pb-1.5">
                        <span class="text-slate-500">Hostname:</span>
                        <span class="text-white font-bold" x-text="selectedAsset?.hostname"></span>
                    </div>
                    <div class="flex justify-between border-b border-vxi-navy/15 pb-1.5">
                        <span class="text-slate-500">IP Segment:</span>
                        <span class="text-vxi-cyan" x-text="selectedAsset?.ip"></span>
                    </div>
                    <div class="flex justify-between border-b border-vxi-navy/15 pb-1.5">
                        <span class="text-slate-500">MAC Addr:</span>
                        <span class="text-slate-400 text-[10px]" x-text="selectedAsset?.mac"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Seat Category:</span>
                        <span class="text-white font-bold capitalize" x-text="selectedAsset?.type"></span>
                    </div>
                </div>

                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider">Coordinates (Drag Simulation)</h4>
                    
                    <div class="grid grid-cols-2 gap-3 text-[11px] font-mono">
                        <div>
                            <span class="text-slate-500">Axis X (horizontal):</span>
                            <input 
                                type="number" 
                                x-model.number="selectedAsset.x" 
                                @input="clampCoordinates(selectedAsset); syncAssetInArray(selectedAsset); refreshFilteredAssets(); persistAssetCoordinates(selectedAsset)"
                                class="w-full mt-1 bg-slate-950 border border-vxi-navy/35 text-white px-2 py-1.5 rounded focus:outline-none focus:border-vxi-red text-center"
                            >
                        </div>
                        <div>
                            <span class="text-slate-500">Axis Y (vertical):</span>
                            <input 
                                type="number" 
                                x-model.number="selectedAsset.y" 
                                @input="clampCoordinates(selectedAsset); syncAssetInArray(selectedAsset); refreshFilteredAssets(); persistAssetCoordinates(selectedAsset)"
                                class="w-full mt-1 bg-slate-950 border border-vxi-navy/35 text-white px-2 py-1.5 rounded focus:outline-none focus:border-vxi-red text-center"
                            >
                        </div>
                    </div>

                    <div class="bg-slate-950/40 p-3 rounded-xl border border-vxi-navy/20 space-y-2">
                        <span class="text-[9px] font-black text-slate-500 uppercase tracking-wider block text-center">Numpad Fine-Tuning</span>
                        
                        <div class="flex justify-center">
                            <button @click="moveSelectedAsset(0, -10)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded">
                                <i data-lucide="chevron-up" class="h-4 w-4"></i>
                            </button>
                        </div>
                        <div class="flex justify-center gap-4">
                            <button @click="moveSelectedAsset(-10, 0)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded">
                                <i data-lucide="chevron-left" class="h-4 w-4"></i>
                            </button>
                            <button @click="moveSelectedAsset(10, 0)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded">
                                <i data-lucide="chevron-right" class="h-4 w-4"></i>
                            </button>
                        </div>
                        <div class="flex justify-center">
                            <button @click="moveSelectedAsset(0, 10)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded">
                                <i data-lucide="chevron-down" class="h-4 w-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 pt-4 border-t border-vxi-navy/20">
                    <button @click="openEditAssetModal(selectedAsset)" class="w-full py-2.5 bg-slate-900 border border-vxi-navy/30 hover:border-vxi-navy/60 text-slate-200 text-xs font-bold rounded-lg transition">
                        Edit Station Properties
                    </button>
                    <button @click="confirmDeleteAsset(selectedAsset)" class="w-full py-2.5 bg-vxi-red/10 hover:bg-vxi-red/20 border border-vxi-red/30 text-vxi-red text-xs font-bold rounded-lg transition">
                        Delete Workstation
                    </button>
                </div>

            </div>

        </aside>

    </div>

    <div x-show="createFloorModal" x-cloak class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-[#001024] border border-vxi-navy/30 rounded-xl p-6 w-full max-w-md shadow-2xl relative" style="background-color: #07111e !important;">
            <h3 class="text-sm font-extrabold text-white mb-4 uppercase tracking-wider">Create Floor Layout</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Floor Name</label>
                    <input type="text" x-model="newFloor.name" placeholder="e.g. Floor 6 - Comcast Voice" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Target Campaign</label>
                    <input type="text" x-model="newFloor.campaign" placeholder="e.g. Comcast CX" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button @click="createFloorModal = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-bold rounded-lg">Cancel</button>
                    <button @click="saveNewFloor()" class="px-4 py-2 bg-vxi-red hover:bg-vxi-red/90 text-white text-xs font-bold rounded-lg">Save Floor</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="editFloorModal" x-cloak class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-[#001024] border border-vxi-navy/30 rounded-xl p-6 w-full max-w-md shadow-2xl relative" style="background-color: #07111e !important;">
            <h3 class="text-sm font-extrabold text-white mb-4 uppercase tracking-wider">Update Floor Properties</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Floor Name</label>
                    <input type="text" x-model="editingFloor.name" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Target Campaign</label>
                    <input type="text" x-model="editingFloor.campaign" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button @click="editFloorModal = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-bold rounded-lg">Cancel</button>
                    <button @click="updateFloor()" class="px-4 py-2 bg-vxi-red hover:bg-vxi-red/90 text-white text-xs font-bold rounded-lg">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="createAssetModal" x-cloak class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-[#001024] border border-vxi-navy/30 rounded-xl p-6 w-full max-w-md shadow-2xl relative" style="background-color: #07111e !important;">
            <h3 class="text-sm font-extrabold text-white mb-4 uppercase tracking-wider">Install Workstation Station</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Seat Label</label>
                    <input type="text" x-model="newAsset.name" placeholder="e.g. A5" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Station Type</label>
                    <select x-model="newAsset.type" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                        <option value="agent">Agent Station</option>
                        <option value="support">Support Station</option>
                        <option value="om">OM Station</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Axis X (20-1940px)</label>
                    <input type="number" x-model.number="newAsset.x" placeholder="230" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Axis Y (20-940px)</label>
                    <input type="number" x-model.number="newAsset.y" placeholder="100" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">HostName ID</label>
                    <input type="text" x-model="newAsset.hostname" placeholder="VXI-DVO-0505" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">IP Address</label>
                    <input type="text" x-model="newAsset.ip" placeholder="10.100.5.15" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">MAC Address</label>
                    <input type="text" x-model="newAsset.mac" placeholder="00:E0:4C:68:01:F5" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div class="col-span-2 flex justify-end gap-2 pt-2">
                    <button @click="createAssetModal = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-bold rounded-lg">Cancel</button>
                    <button @click="saveNewAsset()" class="px-4 py-2 bg-vxi-red hover:bg-vxi-red/90 text-white text-xs font-bold rounded-lg">Deploy Desk</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="editAssetModal" x-cloak class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-[#001024] border border-vxi-navy/30 rounded-xl p-6 w-full max-w-md shadow-2xl relative" style="background-color: #07111e !important;">
            <h3 class="text-sm font-extrabold text-white mb-4 uppercase tracking-wider">Modify Station configuration</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Seat Label</label>
                    <input type="text" x-model="editingAsset.name" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Station Type</label>
                    <select x-model="editingAsset.type" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                        <option value="agent">Agent Station</option>
                        <option value="support">Support Station</option>
                        <option value="om">OM Station</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Axis X (pixels)</label>
                    <input type="number" x-model.number="editingAsset.x" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Axis Y (pixels)</label>
                    <input type="number" x-model.number="editingAsset.y" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div class="col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">HostName ID</label>
                    <input type="text" x-model="editingAsset.hostname" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">IP Address</label>
                    <input type="text" x-model="editingAsset.ip" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">MAC Address</label>
                    <input type="text" x-model="editingAsset.mac" class="block w-full rounded-lg border border-vxi-navy/30 bg-slate-950/85 px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-vxi-red" style="background-color: #020813 !important;">
                </div>
                <div class="col-span-2 flex justify-end gap-2 pt-2">
                    <button @click="editAssetModal = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-bold rounded-lg">Cancel</button>
                    <button @click="updateAsset()" class="px-4 py-2 bg-vxi-red hover:bg-vxi-red/90 text-white text-xs font-bold rounded-lg">Save Config</button>
                </div>
            </div>
        </div>
    </div>

    <div x-show="confirmBox.visible" x-cloak class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
        <div class="bg-[#001024] border border-vxi-navy/30 rounded-xl p-6 w-full max-w-sm shadow-2xl text-center space-y-4" style="background-color: #07111e !important;">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-vxi-red/10 text-vxi-red border border-vxi-red/20">
                <i data-lucide="alert-triangle" class="h-6 w-6"></i>
            </div>
            <h3 class="text-sm font-extrabold text-white uppercase tracking-wider">Confirm Operation</h3>
            <p class="text-xs text-slate-400 leading-relaxed" x-text="confirmBox.message"></p>
            <div class="flex justify-center gap-3 pt-2">
                <button @click="confirmBox.visible = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-bold rounded-lg transition">Cancel</button>
                <button @click="confirmBox.onConfirm()" class="px-4 py-2 bg-vxi-red hover:bg-vxi-red/90 text-white text-xs font-bold rounded-lg transition">Yes, Proceed</button>
            </div>
        </div>
    </div>

    <div x-show="toast.visible" x-cloak x-transition class="fixed bottom-6 right-6 z-50 max-w-sm rounded-xl border border-vxi-navy/40 bg-[#001024] p-4 shadow-2xl flex items-start space-x-3">
        <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-400 border border-emerald-500/20">
            <i data-lucide="check-circle" class="h-5 w-5"></i>
        </div>
        <div>
            <h4 class="text-xs font-extrabold text-white uppercase tracking-wider" x-text="toast.title"></h4>
            <p class="text-[10px] text-slate-400 mt-1" x-text="toast.message"></p>
        </div>
    </div>

    <footer class="bg-vxi-navy-dark border-t border-vxi-navy/30 py-4 px-6 text-center text-[10px] text-slate-500 shrink-0">
        <p>© {{ date('Y') }} VXI Global Solutions • Davao Centrale Map Matrix Editor • Davao City, PH</p>
    </footer>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        function dashboardController() {
                return {
                selectedFloorId: null,
                selectedFloorName: 'Floor 1 - Recruitment Hub',
                selectedAsset: null,
                searchQuery: '',
                filteredAssets: [],
                mapRenderKey: 0,
                authRole: 'IT Site Operator',
                siteHealth: 98,

                // Sidebar modals
                leftSidebarOpen: false,
                rightSidebarOpen: false,

                // Modal control gates
                createFloorModal: false,

                editFloorModal: false,
                createAssetModal: false,
                editAssetModal: false,
                confirmBox: { visible: false, message: '', onConfirm: null },
                toast: { visible: false, title: '', message: '' },
                validationErrors: { floor: '', asset: '' },

                floors: @json($floors->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'campaign' => $f->campaign])),

                // Simplified CAD Layout assets categorized into Agent, Support, and OM Station Types
                assets: @json($allAssets),

                // CRUD instantiation models
                newFloor: { name: '', campaign: '' },
                editingFloor: { id: null, name: '', campaign: '' },
                newAsset: { name: '', type: 'agent', floor_id: null, hostname: '', ip: '', mac: '', x: 350, y: 150 },
                editingAsset: { id: null, name: '', type: 'agent', floor_id: null, hostname: '', ip: '', mac: '', x: 350, y: 150 },

                init() {
                    // Default: start with sidebars closed to maximize map area
                    this.leftSidebarOpen = false;
                    this.rightSidebarOpen = false;

                    // Normalize server-loaded assets so floor_id/x/y are always numeric
                    this.assets = (Array.isArray(this.assets) ? this.assets : []).map(a => this.normalizeAsset(a));

                    if (!Array.isArray(this.floors) || this.floors.length === 0) {
                        this.selectedFloorId = null;
                        this.selectedFloorName = 'No Floors Available';
                        this.refreshFilteredAssets();
                        return;
                    }

                    const first = this.floors[0];
                    this.selectedFloorId = parseInt(first.id, 10);
                    this.selectedFloorName = first.name;
                    this.refreshFilteredAssets();

                    this.$watch('assets', () => this.refreshFilteredAssets());
                    this.$watch('selectedFloorId', () => this.refreshFilteredAssets());
                    this.$watch('searchQuery', () => this.refreshFilteredAssets());
                },

                normalizeAsset(raw) {
                    const floorId = parseInt(raw.floor_id ?? raw.floorId, 10);
                    return {
                        id: parseInt(raw.id, 10),
                        floor_id: Number.isFinite(floorId) ? floorId : null,
                        name: String(raw.name ?? ''),
                        type: raw.type ?? 'agent',
                        hostname: String(raw.hostname ?? ''),
                        ip: String(raw.ip ?? ''),
                        mac: raw.mac ?? '',
                        status: raw.status ?? 'empty',
                        agent: raw.agent ?? 'Unassigned Station',
                        x: parseInt(raw.x, 10) || 100,
                        y: parseInt(raw.y, 10) || 100,
                    };
                },

                refreshFilteredAssets() {
                    const floorId = parseInt(this.selectedFloorId, 10);
                    const query = (this.searchQuery ?? '').toLowerCase().trim();

                    this.filteredAssets = (Array.isArray(this.assets) ? this.assets : []).filter(a => {
                        if (!Number.isFinite(floorId) || parseInt(a.floor_id, 10) !== floorId) {
                            return false;
                        }
                        if (query === '') {
                            return true;
                        }
                        const hostname = (a.hostname ?? '').toLowerCase();
                        const name = (a.name ?? '').toLowerCase();
                        return hostname.includes(query) || name.includes(query);
                    });

                    this.mapRenderKey++;
                },

                selectFloor(floor) {
                    this.selectedFloorId = parseInt(floor.id, 10);
                    this.selectedFloorName = floor.name;
                    this.selectedAsset = null;
                    this.refreshFilteredAssets();
                    this.showToast("Telemetry Synced", `Active directory mapped to floor ${floor.id}`);
                },

                getCampaignName() {
                    let current = this.floors.find(f => f.id === this.selectedFloorId);
                    return current ? current.campaign : 'General Services';
                },

                selectAsset(asset) {
                    this.selectedAsset = asset;
                },

                // Real-time custom position modifier methods for the absolute map geometry
                moveSelectedAsset(dx, dy) {
                    if (!this.selectedAsset) return;
                    this.selectedAsset.x = parseInt(this.selectedAsset.x, 10) + dx;
                    this.selectedAsset.y = parseInt(this.selectedAsset.y, 10) + dy;
                    this.clampCoordinates(this.selectedAsset);
                    this.syncAssetInArray(this.selectedAsset);
                    this.refreshFilteredAssets();
                    this.persistAssetCoordinates(this.selectedAsset);
                },

                syncAssetInArray(asset) {
                    const idx = this.assets.findIndex(a => a.id === asset.id);
                    if (idx !== -1) {
                        this.assets[idx] = { ...this.normalizeAsset(asset) };
                        this.assets = [...this.assets];
                    }
                },

                async persistAssetCoordinates(asset) {
                    if (!asset?.id) return;
                    try {
                        await fetch(`/api/workstations/${asset.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                floor_id: parseInt(asset.floor_id, 10),
                                x: parseInt(asset.x, 10),
                                y: parseInt(asset.y, 10),
                            })
                        });
                    } catch (error) {
                        console.warn('Failed to persist coordinates', error);
                    }
                },

                clampCoordinates(asset) {
                    asset.x = parseInt(asset.x, 10) || 100;
                    asset.y = parseInt(asset.y, 10) || 100;
                    if (asset.x < 10) asset.x = 10;
                    if (asset.x > 1940) asset.x = 1940;
                    if (asset.y < 10) asset.y = 10;
                    if (asset.y > 940) asset.y = 940;
                },

                // --- FLOOR RECORD CONFIGURATIONS ---
                openCreateFloorModal() {
                    this.newFloor = { name: '', campaign: '' };
                    this.createFloorModal = true;
                },

                async saveNewFloor() {
                    // Defensive check: ensure this.floors is an array before modifying it
                    if (!Array.isArray(this.floors)) {
                        console.warn("this.floors was not an array before saveNewFloor. Reinitializing.");
                        this.floors = [];
                    }
                    this.validationErrors.floor = '';
                    
                    if (!this.newFloor.name || !this.newFloor.campaign) {
                        this.showToast('Error', 'Please complete required fields.');
                        return;
                    }
                    
                    try {
                        const response = await fetch('/api/floors', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.newFloor.name,
                                campaign: this.newFloor.campaign
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Failed to save floor');
                        }

                        this.floors = [...this.floors, data.floor];
                        this.createFloorModal = false;
                        this.newFloor = { name: '', campaign: '' };
                        this.showToast("Floor Installed", `Floor "${data.floor.name}" has been mapped.`);
                    } catch (error) {
                        this.showToast('Error', error.message);
                    }
                },

                openEditFloorModal(floor) {
                    this.editingFloor = { ...floor };
                    this.editFloorModal = true;
                },

                async updateFloor() {
                    try {
                        const response = await fetch(`/api/floors/${this.editingFloor.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.editingFloor.name,
                                campaign: this.editingFloor.campaign
                            })
                        });

                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Failed to update floor');

                        this.floors = this.floors.map(f => f.id === data.floor.id ? data.floor : f);
                        if (this.selectedFloorId === data.floor.id) {
                            this.selectedFloorName = data.floor.name;
                        }
                        this.editFloorModal = false;
                        this.showToast("Floor Saved", "Properties saved successfully.");
                    } catch (error) {
                        this.showToast('Error', error.message);
                    }
                },

                confirmDeleteFloor(floor) {
                    this.confirmBox.message = `Decommission entire layout segment: "${floor.name}"? This action will disconnect all local workstations physically mounted on this segment.`;
                    // Defensive checks: ensure arrays are valid before filtering
                    if (!Array.isArray(this.floors)) {
                        console.warn("this.floors was not an array before confirmDeleteFloor. Reinitializing.");
                        this.floors = [];
                    }
                    this.confirmBox.onConfirm = () => {
                        fetch(`/api/floors/${floor.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) return response.json().then(d => { throw new Error(d.message) });
                            
                            this.floors = this.floors.filter(f => f.id !== floor.id);
                            this.assets = this.assets.filter(a => parseInt(a.floor_id, 10) !== parseInt(floor.id, 10));
                            this.refreshFilteredAssets();
                            
                            if (this.selectedFloorId === floor.id && this.floors.length > 0) {
                                this.selectFloor(this.floors[0]);
                            } else if (this.floors.length === 0) {
                                this.selectedFloorId = null;
                                this.selectedFloorName = 'No Floors Available';
                            }
                            
                            this.confirmBox.visible = false;
                            this.showToast("Layout Purged", "Floor layout removed safely.");
                        })
                        .catch(error => {
                            this.showToast('Error', error.message);
                        });
                    };
                    this.confirmBox.visible = true;
                },

                // --- WORKSTATION ASSET CONFIGURATIONS ---

                // Finds the first open grid slot on the given floor so a new station
                // never lands exactly on top of an existing one (which previously made
                // newly added stations invisible, stacked under the default 350,150 spot).
                getNextAvailablePosition(floorId) {
                    const fid = parseInt(floorId, 10);
                    const existingOnFloor = (Array.isArray(this.assets) ? this.assets : [])
                        .filter(a => parseInt(a.floor_id, 10) === fid);

                    const startX = 20, startY = 20;
                    const stepX = 60, stepY = 50;
                    const maxX = 1940, maxY = 940;
                    const minDist = 40; // station box is 46x34, so this keeps boxes from overlapping

                    for (let y = startY; y <= maxY; y += stepY) {
                        for (let x = startX; x <= maxX; x += stepX) {
                            const collision = existingOnFloor.some(a =>
                                Math.abs((a.x ?? 0) - x) < minDist && Math.abs((a.y ?? 0) - y) < minDist
                            );
                            if (!collision) {
                                return { x, y };
                            }
                        }
                    }

                    // Grid is fully packed (unlikely) - fall back to a random in-bounds spot
                    return {
                        x: startX + Math.floor(Math.random() * (maxX - startX)),
                        y: startY + Math.floor(Math.random() * (maxY - startY)),
                    };
                },

                openCreateAssetModal() {
                    const floorId = parseInt(this.selectedFloorId, 10);
                    const pos = this.getNextAvailablePosition(floorId);
                    this.newAsset = {
                        name: '',
                        type: 'agent',
                        floor_id: Number.isFinite(floorId) ? floorId : null,
                        hostname: '',
                        ip: '',
                        mac: '',
                        x: pos.x,
                        y: pos.y,
                    };
                    this.createAssetModal = true;
                },

                async saveNewAsset() {
                    if (!Array.isArray(this.assets)) {
                        this.assets = [];
                    }
                    this.validationErrors.asset = '';

                    const floorId = parseInt(this.selectedFloorId, 10);
                    if (!Number.isFinite(floorId)) {
                        this.showToast('Error', 'Select a floor before deploying a station.');
                        return;
                    }

                    if (!this.newAsset.name || !this.newAsset.hostname || !this.newAsset.ip) {
                        this.showToast('Error', 'Please fill out necessary details.');
                        return;
                    }

                    const payload = {
                        name: this.newAsset.name,
                        type: this.newAsset.type,
                        floor_id: floorId,
                        hostname: this.newAsset.hostname,
                        ip: this.newAsset.ip,
                        mac: this.newAsset.mac || null,
                        x: parseInt(this.newAsset.x, 10) || 100,
                        y: parseInt(this.newAsset.y, 10) || 100,
                    };

                    try {
                        const response = await fetch('/api/workstations', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Failed to deploy station'));
                        }

                        const newStation = this.normalizeAsset({
                            ...data.asset,
                            floor_id: floorId,
                            x: payload.x,
                            y: payload.y,
                        });

                        this.assets = [...this.assets, newStation];
                        this.selectedAsset = newStation;
                        this.refreshFilteredAssets();
                        this.createAssetModal = false;
                        const nextPos = this.getNextAvailablePosition(floorId);
                        this.newAsset = { name: '', type: 'agent', floor_id: floorId, hostname: '', ip: '', mac: '', x: nextPos.x, y: nextPos.y };
                        this.showToast("Station Deployed", `Workstation ${newStation.hostname} is now active on the map.`);
                    } catch (error) {
                        this.showToast('Error', error.message);
                    }
                },

                openEditAssetModal(asset) {
                    this.editingAsset = { ...this.normalizeAsset(asset) };
                    this.editAssetModal = true;
                },

                async updateAsset() {
                    const floorId = parseInt(this.editingAsset.floor_id ?? this.selectedFloorId, 10);
                    try {
                        const response = await fetch(`/api/workstations/${this.editingAsset.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                name: this.editingAsset.name,
                                type: this.editingAsset.type,
                                hostname: this.editingAsset.hostname,
                                ip: this.editingAsset.ip,
                                mac: this.editingAsset.mac || null,
                                x: parseInt(this.editingAsset.x, 10) || 100,
                                y: parseInt(this.editingAsset.y, 10) || 100,
                                floor_id: floorId,
                            })
                        });

                        const data = await response.json();
                        if (!response.ok) throw new Error(data.message || 'Failed to update station');

                        const updatedAsset = this.normalizeAsset({
                            ...data.asset,
                            floor_id: floorId,
                        });
                        this.assets = this.assets.map(a => a.id === updatedAsset.id ? updatedAsset : a);
                        if (this.selectedAsset && this.selectedAsset.id === updatedAsset.id) {
                            this.selectedAsset = updatedAsset;
                        }
                        this.refreshFilteredAssets();
                        this.editAssetModal = false;
                        this.showToast("Configuration Saved", "Properties successfully updated.");
                    } catch (error) {
                        this.showToast('Error', error.message);
                    }
                },

                confirmDeleteAsset(asset) {
                    this.confirmBox.message = `De-provision workstation node "${asset.hostname}" from local layout mapping?`;
                    // Defensive check: ensure this.assets is an array before filtering
                    if (!Array.isArray(this.assets)) {
                        this.assets = [];
                    }
                    this.confirmBox.onConfirm = () => {
                        fetch(`/api/workstations/${asset.id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) return response.json().then(d => { throw new Error(d.message) });
                            
                            this.assets = this.assets.filter(a => a.id !== asset.id);
                            this.selectedAsset = null;
                            this.refreshFilteredAssets();
                            this.confirmBox.visible = false;
                            this.showToast("Asset Purged", "Station removed successfully.");
                        })
                        .catch(error => {
                            this.showToast('Error', error.message);
                        });
                    };
                    this.confirmBox.visible = true;
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