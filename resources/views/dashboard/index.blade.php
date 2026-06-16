<x-app-layout>
<div class="h-screen bg-slate-800 text-white overflow-hidden flex flex-col">
    <!-- Header -->
    <div class="bg-slate-900 border-b border-slate-700 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold text-cyan-400">VXI FloorSight</h1>
            <div class="text-sm text-slate-400">
                Floor <span class="font-mono text-cyan-300">{{ $currentFloor->floor_number }}</span> | 
                {{ $currentFloor->floor_name ?? "Floor {$currentFloor->floor_number}" }}
            </div>
        </div>

        <div class="flex items-center gap-4">
            <div class="text-sm text-slate-400">
                Welcome, <span class="text-cyan-300">{{ auth()->user()->name }}</span>
                <span class="text-xs text-slate-500">({{ auth()->user()->roles->first()?->display_name ?? 'User' }})</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="text-sm text-red-400 hover:text-red-300 transition">Logout</button>
            </form>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex flex-1 overflow-hidden">
        <!-- Left Panel: Metrics -->
        <div class="w-80 bg-slate-800 border-r border-slate-700 overflow-y-auto p-6">
            <div class="space-y-6">
                <!-- Floor Selection -->
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-cyan-500 rounded"></span>Select Floor
                    </h3>
                    <div class="space-y-2">
                        @foreach($floors as $floor)
                            <a href="{{ route('dashboard', ['floor' => $floor->id]) }}"
                               class="block px-4 py-3 rounded-lg transition font-medium text-sm border {{ $floor->id === $currentFloor->id ? 'bg-gradient-to-r from-cyan-600 to-cyan-700 text-white border-cyan-500 shadow-lg shadow-cyan-500/30' : 'bg-slate-700 text-slate-300 hover:bg-slate-600 border-slate-600 hover:border-slate-500' }}">
                                <div class="flex justify-between items-center">
                                    <span>Floor {{ $floor->floor_number }}</span>
                                    <span class="text-xs bg-black/20 px-2 py-1 rounded">{{ $floor->workstations()->count() }} seats</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Floor Metrics -->
                <div class="border-t border-slate-700 pt-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <span class="w-1 h-4 bg-cyan-500 rounded"></span>Floor Metrics
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Total Workstations -->
                        <div class="bg-gradient-to-br from-slate-700 to-slate-800 rounded-lg p-4 border border-slate-600 hover:border-slate-500 transition shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-3xl font-bold text-cyan-400">{{ $metrics['total_workstations'] }}</div>
                                    <div class="text-xs text-slate-400 mt-1">Total Seats</div>
                                </div>
                                <div class="text-cyan-400/20 text-4xl">&#128519;</div>
                            </div>
                        </div>

                        <!-- Active -->
                        <div class="bg-gradient-to-br from-green-900/20 to-slate-800 rounded-lg p-4 border border-green-600/50 hover:border-green-500 transition shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-3xl font-bold text-green-400">{{ $metrics['active'] }}</div>
                                    <div class="text-xs text-slate-400 mt-1">Active</div>
                                </div>
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            </div>
                        </div>

                        <!-- Offline -->
                        <div class="bg-gradient-to-br from-red-900/20 to-slate-800 rounded-lg p-4 border border-red-600/50 hover:border-red-500 transition shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-3xl font-bold text-red-400">{{ $metrics['offline'] }}</div>
                                    <div class="text-xs text-slate-400 mt-1">Offline</div>
                                </div>
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            </div>
                        </div>

                        <!-- Empty -->
                        <div class="bg-gradient-to-br from-gray-700/20 to-slate-800 rounded-lg p-4 border border-gray-600/50 hover:border-gray-500 transition shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-3xl font-bold text-gray-400">{{ $metrics['empty'] }}</div>
                                    <div class="text-xs text-slate-400 mt-1">Empty</div>
                                </div>
                                <div class="text-gray-400/20 text-4xl">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Occupancy Percentage -->
                    <div class="mt-4 bg-gradient-to-r from-slate-700 to-slate-800 rounded-lg p-4 border border-cyan-600/30 hover:border-cyan-500 transition shadow-lg">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs text-slate-400 font-semibold">OCCUPANCY RATE</span>
                            <span class="text-lg font-bold text-cyan-300">{{ $metrics['occupancy_percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-slate-600 rounded-full h-3 overflow-hidden border border-slate-500">
                            <div class="bg-gradient-to-r from-cyan-500 via-blue-500 to-cyan-400 h-3 rounded-full transition-all duration-500 shadow-lg shadow-cyan-500/50"
                                 style="width: {{ $metrics['occupancy_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Search Workstation -->
                <div class="border-t border-slate-700 pt-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="w-1 h-4 bg-cyan-500 rounded"></span>Search Asset
                    </h3>
                    <div class="relative">
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                            <input type="text"
                                   id="search-workstation"
                                   placeholder="Hostname, IP, Agent..."
                                   class="w-full pl-10 pr-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-sm focus:outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/30 transition"
                                   autocomplete="off">
                        </div>
                        <div id="search-results"
                             class="absolute top-full left-0 right-0 mt-2 bg-slate-700 border border-slate-600 rounded-lg shadow-xl max-h-48 overflow-y-auto z-50 hidden">
                        </div>
                    </div>
                </div>

                <!-- Recent Audit Logs -->
                @if(auth()->user()->can('view_audit_logs'))
                    <div class="border-t border-slate-700 pt-4">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                            <span class="w-1 h-4 bg-cyan-500 rounded"></span>Recent Activity
                        </h3>
                        <div class="space-y-2 text-xs max-h-40 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-600 scrollbar-track-slate-800">
                            @forelse($recentAuditLogs as $log)
                                <div class="bg-slate-700 rounded-lg px-3 py-2 border-l-3 border-cyan-500 hover:bg-slate-600 transition">
                                    <div class="text-slate-200 font-medium">{{ $log->action_performed }}</div>
                                    <div class="text-slate-500 text-xs mt-1 flex justify-between">
                                        <span>{{ $log->user->name }}</span>
                                        <span>{{ $log->timestamp->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-slate-500 text-xs text-center py-4">No recent activity</div>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Center: SVG Floor Map -->
        <div class="flex-1 bg-slate-800 relative overflow-auto p-6">
            <div class="h-full w-full bg-gradient-to-br from-slate-700 to-slate-800 rounded-xl border border-slate-700 shadow-2xl p-6 overflow-auto"
                 id="floor-map-container">
                <svg id="floor-map" class="w-full h-full" viewBox="0 0 1200 800" xmlns="http://www.w3.org/2000/svg">
                    <!-- SVG Floor Plan -->
                    <defs>
                        <style>
                            .workstation-seat { cursor: pointer; transition: all 0.3s ease; filter: drop-shadow(0 0 2px rgba(0,0,0,0.5)); }
                            .workstation-seat:hover { filter: brightness(1.4) drop-shadow(0 0 8px rgba(34, 197, 94, 0.6)); transform-origin: center; }
                            .workstation-seat.active { fill: #10b981; }
                            .workstation-seat.offline { fill: #ef4444; animation: pulse 1.5s infinite; }
                            .workstation-seat.empty { fill: #6b7280; }
                            .workstation-seat.selected { filter: drop-shadow(0 0 12px #06b6d4) brightness(1.2); }
                            @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
                        </style>
                    </defs>

                    <!-- Floor title -->
                    <text x="600" y="50" text-anchor="middle" class="text-3xl fill-cyan-400 font-bold"
                          style="font-family: 'Courier New', monospace; font-size: 32px; font-weight: bold; fill: #22d3ee;">
                        Floor {{ $currentFloor->floor_number }} &#x2503; {{ $currentFloor->floor_name ?? "Floor {$currentFloor->floor_number}" }}
                    </text>

                    <!-- Bay sections with workstations -->
                    @php $xOffset = 100; $yOffset = 120; @endphp
                    @foreach($bays as $bayIndex => $bay)
                        @php
                            $bayX = $xOffset + ($bayIndex % 2) * 550;
                            $bayY = $yOffset + intdiv($bayIndex, 2) * 320;
                        @endphp

                        <!-- Bay Background -->
                        <rect x="{{ $bayX - 20 }}" y="{{ $bayY - 40 }}"
                              width="480" height="280"
                              fill="rgba(51, 65, 85, 0.3)"
                              stroke="#475569"
                              stroke-width="1"
                              rx="8"/>

                        <!-- Bay Label -->
                        <text x="{{ $bayX }}" y="{{ $bayY - 20 }}"
                              style="font-family: 'Courier New', monospace; font-size: 16px; font-weight: bold; fill: #a1f0ff;"
                              class="text-lg fill-cyan-300 font-semibold">
                            Bay {{ $bay->bay_letter }}
                        </text>
                        <text x="{{ $bayX }}" y="{{ $bayY + 2 }}"
                              style="font-family: Arial, sans-serif; font-size: 12px; fill: #cbd5e1;"
                              class="text-xs fill-slate-300">
                            {{ $bay->client_campaign_name }}
                        </text>

                        <!-- Workstations Grid -->
                        @php $wsIndex = 0; @endphp
                        @foreach($bay->workstations as $workstation)
                            @php
                                $col = $wsIndex % 5;
                                $row = intdiv($wsIndex, 5);
                                $x = $bayX + 50 + ($col * 85);
                                $y = $bayY + 40 + ($row * 85);
                                $wsIndex++;
                            @endphp

                            <circle
                                id="{{ $workstation->getSvgElementId() }}"
                                cx="{{ $x }}"
                                cy="{{ $y }}"
                                r="32"
                                class="workstation-seat {{ $workstation->status }}"
                                data-workstation-id="{{ $workstation->id }}"
                                data-hostname="{{ $workstation->hostname }}"
                                data-status="{{ $workstation->status }}"
                            />
                            <text x="{{ $x }}" y="{{ $y + 5 }}"
                                  text-anchor="middle"
                                  style="font-family: 'Courier New', monospace; font-size: 11px; font-weight: bold; fill: white; pointer-events: none;"
                                  class="text-xs fill-white font-semibold pointer-events-none">
                                {{ $workstation->station_id }}
                            </text>
                        @endforeach
                    @endforeach
                </svg>
            </div>
        </div>

        <!-- Right Panel: Asset Details (Slide-out) -->
        <div id="asset-sidebar"
             class="w-0 bg-slate-800 border-l border-slate-700 overflow-y-auto transition-all duration-300"
             style="width: 0px;">
            <div class="p-6 space-y-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-cyan-400">Asset Details</h3>
                    <button id="close-sidebar"
                            class="text-slate-400 hover:text-slate-200 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div id="asset-details-content" class="space-y-4">
                    <!-- Will be populated by JavaScript -->
                </div>

                <!-- Action Buttons -->
                <div id="asset-actions" class="border-t border-slate-700 pt-4 space-y-2">
                    <!-- Will be populated based on user permissions -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
    const floorMapSvg = document.getElementById('floor-map');
    const assetSidebar = document.getElementById('asset-sidebar');
    const closeSidebarBtn = document.getElementById('close-sidebar');
    const searchInput = document.getElementById('search-workstation');
    const searchResults = document.getElementById('search-results');

    // Handle workstation click
    document.querySelectorAll('.workstation-seat').forEach(seat => {
        seat.addEventListener('click', function() {
            const workstationId = this.dataset.workstationId;
            loadWorkstationDetails(workstationId);
            openSidebar();
        });
    });

    // Close sidebar
    closeSidebarBtn.addEventListener('click', closeSidebar);

    // Search functionality
    searchInput.addEventListener('input', debounce(async (e) => {
        const term = e.target.value.trim();

        if (term.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        try {
            const response = await fetch(`/api/search?term=${encodeURIComponent(term)}`);
            const data = await response.json();

            if (data.results.length === 0) {
                searchResults.innerHTML = '<div class="p-3 text-slate-400">No results found</div>';
            } else {
                searchResults.innerHTML = data.results
                    .map(result => `
                        <div class="p-3 hover:bg-slate-600 cursor-pointer border-b border-slate-700 last:border-b-0"
                             onclick="selectSearchResult(${result.id}, '${result.hostname}')">
                            <div class="font-semibold text-cyan-300">${result.hostname}</div>
                            <div class="text-xs text-slate-400">
                                ${result.agent_name ? result.agent_name + ' • ' : ''}${result.ip_address}
                            </div>
                            <div class="text-xs text-slate-500">
                                Floor ${result.floor_number} • Bay ${result.bay_letter}
                            </div>
                        </div>
                    `).join('');
            }

            searchResults.classList.remove('hidden');
        } catch (error) {
            console.error('Search error:', error);
        }
    }, 300));

    // Select search result
    async function selectSearchResult(workstationId, hostname) {
        searchInput.value = hostname;
        searchResults.classList.add('hidden');
        await loadWorkstationDetails(workstationId);
        openSidebar();
    }

    // Load workstation details
    async function loadWorkstationDetails(workstationId) {
        try {
            const response = await fetch(`/api/workstations/${workstationId}`);
            const data = await response.json();

            // Highlight the selected workstation on the map
            document.querySelectorAll('.workstation-seat').forEach(seat => {
                seat.classList.remove('selected');
            });
            const selectedSeat = document.getElementById(data.svg_element_id);
            if (selectedSeat) {
                selectedSeat.classList.add('selected');
                selectedSeat.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // Populate sidebar
            const detailsContent = document.getElementById('asset-details-content');
            detailsContent.innerHTML = `
                <div class="space-y-3">
                    <div class="bg-slate-700 rounded p-3 border border-slate-600">
                        <div class="text-xs text-slate-400">Hostname</div>
                        <div class="font-mono text-cyan-300">${data.hostname}</div>
                    </div>

                    <div class="bg-slate-700 rounded p-3 border border-slate-600">
                        <div class="text-xs text-slate-400">Station ID</div>
                        <div class="font-mono text-cyan-300">${data.station_id}</div>
                    </div>

                    <div class="bg-slate-700 rounded p-3 border border-slate-600">
                        <div class="text-xs text-slate-400">IP Address</div>
                        <div class="font-mono text-cyan-300">${data.ip_address}</div>
                    </div>

                    <div class="bg-slate-700 rounded p-3 border border-slate-600">
                        <div class="text-xs text-slate-400">MAC Address</div>
                        <div class="font-mono text-slate-300">${data.mac_address || 'N/A'}</div>
                    </div>

                    <div class="bg-slate-700 rounded p-3 border border-slate-600">
                        <div class="text-xs text-slate-400">Status</div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full ${data.status === 'active' ? 'bg-green-500' : data.status === 'offline' ? 'bg-red-500 animate-pulse' : 'bg-gray-500'}"></span>
                            <span class="capitalize">${data.status}</span>
                        </div>
                    </div>

                    ${data.agent_name ? `
                        <div class="bg-slate-700 rounded p-3 border border-slate-600">
                            <div class="text-xs text-slate-400">Agent Name</div>
                            <div>${data.agent_name}</div>
                        </div>
                    ` : ''}

                    ${data.asset_tag ? `
                        <div class="bg-slate-700 rounded p-3 border border-slate-600">
                            <div class="text-xs text-slate-400">Asset Tag</div>
                            <div class="font-mono">${data.asset_tag}</div>
                        </div>
                    ` : ''}

                    <div class="bg-slate-700 rounded p-3 border border-slate-600">
                        <div class="text-xs text-slate-400">Campaign</div>
                        <div>${data.bay.client_campaign_name}</div>
                    </div>

                    ${data.last_ping_at ? `
                        <div class="bg-slate-700 rounded p-3 border border-slate-600">
                            <div class="text-xs text-slate-400">Last Ping</div>
                            <div class="text-sm">${data.last_ping_at}</div>
                        </div>
                    ` : ''}
                </div>
            `;

            // Populate actions
            const actionsContent = document.getElementById('asset-actions');
            let actionsHtml = '';

            if (data.can_remote_session) {
                actionsHtml += `
                    <button onclick="launchRemoteSession(${data.id}, '${data.hostname}')"
                            class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded transition">
                        Launch RDP
                    </button>
                `;
            }

            if (data.can_update) {
                actionsHtml += `
                    <button onclick="editWorkstation(${data.id})"
                            class="w-full bg-slate-600 hover:bg-slate-500 text-white font-bold py-2 px-4 rounded transition">
                        Edit Details
                    </button>
                `;
            }

            actionsContent.innerHTML = actionsHtml || '<div class="text-slate-400 text-sm">No actions available</div>';
        } catch (error) {
            console.error('Error loading workstation details:', error);
        }
    }

    // Launch remote session
    async function launchRemoteSession(workstationId, hostname) {
        try {
            const response = await fetch(`/api/workstations/${workstationId}/remote-session`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });
            const data = await response.json();
            // In a real scenario, this would launch the RDP client
            // For now, we'll just show a notification
            alert(`Remote session initiated to ${hostname}`);
        } catch (error) {
            console.error('Error launching remote session:', error);
        }
    }

    // Open sidebar
    function openSidebar() {
        assetSidebar.style.width = '400px';
    }

    // Close sidebar
    function closeSidebar() {
        assetSidebar.style.width = '0';
        document.querySelectorAll('.workstation-seat').forEach(seat => {
            seat.classList.remove('selected');
        });
    }

    // Debounce function
    function debounce(func, delay) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // Real-time status updates
    setInterval(async () => {
        try {
            const response = await fetch('/api/workstations-statuses');
            const data = await response.json();

            data.statuses.forEach(status => {
                const element = document.getElementById(status.svg_element_id);
                if (element) {
                    element.className.baseVal = `workstation-seat ${status.status}`;
                }
            });
        } catch (error) {
            console.error('Error updating statuses:', error);
        }
    }, 5000); // Update every 5 seconds
</script>
</x-app-layout>
