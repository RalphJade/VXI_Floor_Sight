<template>
  <div class="flex flex-col h-full bg-[#050B14]">
    <!-- Header -->
    <header class="bg-vxi-navy-dark border-b border-vxi-navy/30 px-6 py-4 flex justify-between items-center shrink-0 shadow-lg">
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
        <span class="text-xs text-slate-400 font-mono">WORKSPACE: <span class="text-vxi-cyan font-bold">{{ selectedFloorName }}</span></span>
        <div class="h-6 w-px bg-vxi-navy/30"></div>
        <a href="/" class="text-xs font-bold text-slate-400 hover:text-white transition">Back to Main HUD</a>
      </div>
    </header>

    <div class="flex-1 flex overflow-hidden relative">
      <!-- Left Sidebar -->
      <aside v-show="leftSidebarOpen" class="w-72 border-r border-vxi-navy/30 bg-vxi-navy-dark/60 p-5 flex flex-col justify-between shrink-0 overflow-y-auto z-10">
        <div class="space-y-6">
          <div class="flex items-center justify-between">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider flex items-center">
              <i data-lucide="layers" class="h-4 w-4 mr-1.5 text-vxi-red"></i> Floor Layouts
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
            <template v-for="floor in floors" :key="floor.id">
              <div :class="parseInt(selectedFloorId) === parseInt(floor.id) ? 'bg-vxi-red/10 border-vxi-red/40' : 'bg-slate-950/40 border-vxi-navy/20 hover:border-vxi-navy/40'" class="group p-3 rounded-xl border flex items-center justify-between transition duration-150 cursor-pointer" @click="selectFloor(floor)">
                <div class="flex-1">
                  <h4 class="text-xs font-extrabold text-white">{{ floor.name }}</h4>
                  <p class="text-[9px] font-mono text-slate-500 uppercase mt-0.5">{{ ': ' + floor.campaign }}</p>
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
          <button @click="openCreateAssetModal()" class="w-full py-2.5 bg-slate-900 border border-vxi-navy/30 hover:border-vxi-navy/60 text-slate-200 text-xs font-bold rounded-lg">Add Station</button>
        </div>
      </aside>

      <!-- Main Canvas Map -->
      <main class="flex-1 p-6 flex flex-col justify-between overflow-y-auto dashboard-scanlines relative z-0" ref="stageContainer">
        <v-stage :config="stageConfig" class="w-full h-auto relative z-10" @mousedown="checkDeselect">
          <v-layer>
            <template v-for="ws in filteredWorkstations" :key="ws.id">
              <v-group 
                :config="{ x: ws.x, y: ws.y, draggable: true, id: ws.id.toString() }"
                @mousedown="selectAsset(ws)"
                @dragend="onDragEnd(ws, $event)"
              >
                <v-rect :config="{
                  width: 46, height: 34, cornerRadius: 5,
                  fill: selectedAsset && selectedAsset.id === ws.id ? '#E31B23' : '#001024',
                  opacity: selectedAsset && selectedAsset.id === ws.id ? 0.2 : 0.9,
                  stroke: selectedAsset && selectedAsset.id === ws.id ? '#FF2E37' : (ws.type === 'agent' ? '#10b981' : (ws.type === 'support' ? '#22d3ee' : '#E31B23')),
                  strokeWidth: 2
                }"></v-rect>
                <v-circle :config="{
                  x: 9, y: 10, radius: 4,
                  fill: ws.type === 'agent' ? '#10b981' : (ws.type === 'support' ? '#22d3ee' : '#E31B23')
                }"></v-circle>
                <v-text :config="{
                  x: 0, y: 18, width: 46, text: ws.name,
                  fill: '#f1f5f9', fontSize: 9, fontFamily: 'monospace', fontStyle: '900',
                  align: 'center'
                }"></v-text>
              </v-group>
            </template>
          </v-layer>
        </v-stage>
        <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between text-[9px] font-mono bg-slate-950/80 px-4 py-2 border border-vxi-navy/35 rounded-xl pointer-events-none">
          <span class="text-slate-400">💡 TIP: DRAG ANY STATION ON MAP TO ALTER MAP GEOMETRICS</span>
          <span class="text-vxi-cyan">ACTIVE COORDINATE LAYER</span>
        </div>
      </main>

      <!-- Right Sidebar (Asset Inspector) -->
      <aside v-show="rightSidebarOpen" class="w-80 border-l border-vxi-navy/30 bg-[#001024]/60 p-5 flex flex-col justify-between shrink-0 overflow-y-auto z-10">
        <template v-if="!selectedAsset"><div class="flex flex-col items-center justify-center text-center h-full text-slate-500 relative">
          <button @click="rightSidebarOpen = false" class="absolute top-0 right-0 text-slate-500 hover:text-slate-300 p-1" title="Close Panel"><i data-lucide="x" class="h-4 w-4"></i></button>
          <i data-lucide="info" class="h-10 w-10 text-slate-700 mb-2"></i>
          <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Asset Inspector</h4>
          <p class="text-[10px] max-w-[180px] mt-1">Select any workstation on the map canvas blueprint to inspect properties and customize coordinates.</p>
        </div></template>
        <div v-else class="space-y-6">
          <div class="flex items-center justify-between border-b border-vxi-navy/20 pb-3">
            <div>
              <span class="text-[8px] font-black px-2 py-0.5 rounded border uppercase tracking-widest" :class="selectedAsset.type === 'agent' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' : (selectedAsset.type === 'support' ? 'bg-cyan-500/10 border-cyan-500/20 text-vxi-cyan' : 'bg-vxi-red/10 border-vxi-red/20 text-vxi-red')">{{ selectedAsset.type + ' Station' }}</span>
              <h3 class="text-xl font-extrabold text-white mt-1">{{ 'Workspace ' + selectedAsset.name }}</h3>
            </div>
            <div class="flex items-center gap-1.5">
              <button @click="selectedAsset = null" class="text-slate-500 hover:text-slate-300 p-1" title="Deselect Asset"><i data-lucide="minus-circle" class="h-4 w-4"></i></button>
              <button @click="rightSidebarOpen = false; selectedAsset = null" class="text-slate-500 hover:text-vxi-red p-1" title="Close Inspector"><i data-lucide="x" class="h-4 w-4"></i></button>
            </div>
          </div>
          <div class="space-y-2 text-[11px] font-mono bg-slate-950/60 p-4 rounded-xl border border-vxi-navy/25">
            <div class="flex justify-between border-b border-vxi-navy/15 pb-1.5"><span class="text-slate-500">Hostname:</span><span class="text-white font-bold">{{ selectedAsset.hostname }}</span></div>
            <div class="flex justify-between border-b border-vxi-navy/15 pb-1.5"><span class="text-slate-500">IP Segment:</span><span class="text-vxi-cyan">{{ selectedAsset.ip }}</span></div>
            <div class="flex justify-between border-b border-vxi-navy/15 pb-1.5"><span class="text-slate-500">MAC Addr:</span><span class="text-slate-400 text-[10px]">{{ selectedAsset.mac }}</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Seat Category:</span><span class="text-white font-bold capitalize">{{ selectedAsset.type }}</span></div>
          </div>
          <div class="space-y-4">
            <h4 class="text-xs font-black text-slate-400 uppercase tracking-wider">Coordinates (Drag Simulation)</h4>
            <div class="grid grid-cols-2 gap-3 text-[11px] font-mono">
              <div>
                <span class="text-slate-500">Axis X (horizontal):</span>
                <input type="number" v-model.number="selectedAsset.x" @input="persistCoordinates(selectedAsset)" class="w-full mt-1 bg-slate-950 border border-vxi-navy/35 text-white px-2 py-1.5 rounded focus:outline-none focus:border-vxi-red text-center" />
              </div>
              <div>
                <span class="text-slate-500">Axis Y (vertical):</span>
                <input type="number" v-model.number="selectedAsset.y" @input="persistCoordinates(selectedAsset)" class="w-full mt-1 bg-slate-950 border border-vxi-navy/35 text-white px-2 py-1.5 rounded focus:outline-none focus:border-vxi-red text-center" />
              </div>
            </div>
            <div class="bg-slate-950/40 p-3 rounded-xl border border-vxi-navy/20 space-y-2">
              <span class="text-[9px] font-black text-slate-500 uppercase tracking-wider block text-center">Numpad Fine‑Tuning</span>
              <div class="flex justify-center">
                <button @click="moveSelectedAsset(0, -10)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded"><i data-lucide="chevron-up" class="h-4 w-4"></i></button>
              </div>
              <div class="flex justify-center gap-4">
                <button @click="moveSelectedAsset(-10, 0)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded"><i data-lucide="chevron-left" class="h-4 w-4"></i></button>
                <button @click="moveSelectedAsset(10, 0)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded"><i data-lucide="chevron-right" class="h-4 w-4"></i></button>
              </div>
              <div class="flex justify-center">
                <button @click="moveSelectedAsset(0, 10)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded"><i data-lucide="chevron-down" class="h-4 w-4"></i></button>
              </div>
            </div>
          </div>
          <div class="flex justify-end gap-2"><button @click="openEditAssetModal(selectedAsset)" class="w-full py-2.5 bg-slate-900 border border-vxi-navy/30 hover:border-vxi-navy/60 text-slate-200 text-xs font-bold rounded-lg">Edit Station Properties</button><button @click="confirmDeleteAsset(selectedAsset)" class="w-full py-2.5 bg-vxi-red/10 hover:bg-vxi-red/20 border border-vxi-red/30 text-vxi-red text-xs font-bold rounded-lg">Delete Workstation</button></div>
        </div>
      </aside>
    </div>

    <!-- Add Station Modal -->
    <div v-if="showAddStationModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-vxi-navy-dark border border-vxi-navy/40 p-6 rounded-2xl shadow-2xl w-full max-w-sm">
        <h2 class="text-white font-black text-lg mb-4">Deploy New Workstation</h2>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-1">Station Identifier</label>
            <input v-model="newStation.name" type="text" class="w-full bg-slate-900 border border-vxi-navy/50 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-vxi-cyan" placeholder="e.g. A01">
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-400 mb-1">Station Type</label>
            <select v-model="newStation.type" class="w-full bg-slate-900 border border-vxi-navy/50 text-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-vxi-cyan">
              <option value="agent">Agent Station</option>
              <option value="support">Support Station</option>
              <option value="om">OM Station</option>
            </select>
          </div>
        </div>
        <div class="mt-6 flex justify-end gap-3">
          <button @click="showAddStationModal = false" class="px-4 py-2 text-sm font-bold text-slate-400 hover:text-white">Cancel</button>
          <button @click="submitNewStation" class="px-4 py-2 text-sm font-bold bg-vxi-cyan hover:bg-cyan-400 text-vxi-navy-dark rounded-lg">Deploy Station</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  name: 'Dashboard',
  data() {
    return {
      // Core UI state
      floors: [],
      selectedFloorId: null,
      selectedFloorName: '',
      leftSidebarOpen: true,
      rightSidebarOpen: false,
      workstations: [],
      selectedAsset: null,

      // Konva State
      stageConfig: {
        width: 1600,
        height: 800,
        scaleX: 1,
        scaleY: 1
      },
      resizeObserver: null,

      // Modal State
      showAddStationModal: false,
      newStation: {
        name: '',
        type: 'agent'
      },

      // Search & UI helpers
      searchQuery: '',
    };
  },
  computed: {
    filteredWorkstations() {
      return this.workstations.filter(ws => !this.selectedFloorId || ws.floor_id == this.selectedFloorId);
    }
  },
  async mounted() {
    try {
      const [floorsRes, stationsRes] = await Promise.all([
        axios.get('/api/floors'),
        axios.get('/api/workstations')
      ]);
      this.floors = floorsRes.data;
      this.workstations = stationsRes.data;
      if (this.floors.length) {
        this.selectFloor(this.floors[0]);
      }
    } catch (e) {
      console.error('Failed to load data', e);
    }

    // Set up responsive Konva stage
    this.resizeObserver = new ResizeObserver(entries => {
      for (let entry of entries) {
        const containerWidth = entry.contentRect.width;
        // Base logical dimensions for the floor plan
        const logicalWidth = 2000;
        const logicalHeight = 1000;
        
        // Calculate scale to fit width
        const scale = Math.min(containerWidth, 1600) / logicalWidth;
        
        this.stageConfig.width = logicalWidth * scale;
        this.stageConfig.height = logicalHeight * scale;
        this.stageConfig.scaleX = scale;
        this.stageConfig.scaleY = scale;
      }
    });
    
    if (this.$refs.stageContainer) {
      this.resizeObserver.observe(this.$refs.stageContainer);
    }
  },
  beforeUnmount() {
    if (this.resizeObserver) {
      this.resizeObserver.disconnect();
    }
  },
  methods: {
    async fetchFloors() {
      // Re‑use the same endpoint the Blade view used for $floors
      try {
        const response = await axios.get('/api/floors');
        this.floors = response.data;
        if (this.floors.length) {
          this.selectFloor(this.floors[0]);
        }
      } catch (e) {
        console.error('Failed to load floors', e);
      }
    },
    async fetchWorkstations() {
      try {
        const response = await axios.get('/api/workstations');
        this.workstations = response.data;
      } catch (e) {
        console.error('Failed to load workstations', e);
      }
    },
    selectFloor(floor) {
      this.selectedFloorId = floor.id;
      this.selectedFloorName = floor.name;
      this.refreshFilteredAssets();
    },
    refreshFilteredAssets() {
      // Simple client‑side filter based on selectedFloorId
      const fid = parseInt(this.selectedFloorId, 10);
      // In a real app we might fetch from a filtered endpoint, 
      // here we rely on the component list binding or manual filtering
    },
    selectAsset(ws) {
      this.selectedAsset = ws;
      this.rightSidebarOpen = true;
    },
    checkDeselect(e) {
      // Deselect when clicking on empty area of the stage
      const clickedOnEmpty = e.target === e.target.getStage();
      if (clickedOnEmpty) {
        this.selectedAsset = null;
        this.rightSidebarOpen = false;
      }
    },
    onDragEnd(ws, e) {
      // Update coordinates from Konva event
      ws.x = Math.round(e.target.x());
      ws.y = Math.round(e.target.y());
      this.persistCoordinates(ws);
    },
    async persistCoordinates(ws) {
      try {
        await axios.patch(`/api/workstations/${ws.id}`, { x: ws.x, y: ws.y });
      } catch (e) {
        console.error('Failed to persist coordinates', e);
      }
    },
    moveSelectedAsset(dx, dy) {
      if (!this.selectedAsset) return;
      this.selectedAsset.x += dx;
      this.selectedAsset.y += dy;
      this.persistCoordinates(this.selectedAsset);
    },
    
    // Modal & Action methods
    openCreateAssetModal() {
      if (!this.selectedFloorId) {
        alert("Please select a floor first.");
        return;
      }
      this.newStation = { name: '', type: 'agent' };
      this.showAddStationModal = true;
    },
    async submitNewStation() {
      if (!this.newStation.name) return;
      
      try {
        const payload = {
            name: this.newStation.name,
            type: this.newStation.type,
            floor_id: this.selectedFloorId,
            hostname: `WS-F${this.selectedFloorId}-${this.newStation.name}`,
            ip: `10.0.${this.selectedFloorId}.${Math.floor(Math.random() * 255)}`,
            x: 500, // Default drop location on canvas
            y: 500,
        };
        const response = await axios.post('/api/workstations', payload);
        this.workstations.push(response.data.asset);
        this.showAddStationModal = false;
        this.selectAsset(response.data.asset);
      } catch (e) {
        console.error('Failed to create workstation', e);
        alert(e.response?.data?.message || 'Failed to create workstation');
      }
    },
    confirmDeleteAsset(asset) {
        if (confirm(`Are you sure you want to delete ${asset.name}?`)) {
            axios.delete(`/api/workstations/${asset.id}`).then(() => {
                this.workstations = this.workstations.filter(w => w.id !== asset.id);
                this.selectedAsset = null;
                this.rightSidebarOpen = false;
            }).catch(e => {
                alert('Failed to delete workstation');
            });
        }
    },
    
    // Placeholder methods for future modal implementations
    openCreateFloorModal() {},
    openEditFloorModal(floor) {},
    confirmDeleteFloor(floor) {},
    openEditAssetModal(asset) {},
  },
};
</script>

<style scoped>
/* Add component-specific styles if needed */
</style>
