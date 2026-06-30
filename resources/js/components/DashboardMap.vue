<template>
  <!-- ==================== HEADER ==================== -->
  <header class="bg-vxi-navy-dark border-b border-vxi-navy/30 px-6 py-4 flex justify-between items-center shadow-lg">
    <div class="flex items-center gap-3">
      <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-vxi-red text-white font-black text-xl tracking-tighter shadow-lg shadow-vxi-red/20">
        VXI
      </div>
      <div>
        <h1 class="text-sm font-extrabold text-white flex items-center tracking-wider">
          <i data-lucide="map" class="h-5 w-5 mr-2 text-vxi-red"></i>
          VXI FloorSight Terminal Map
          <span class="ml-2 text-[9px] px-2 py-0.5 rounded-full bg-vxi-red/15 text-vxi-red border border-vxi-red/30 uppercase tracking-widest font-black">
            Structure Editor
          </span>
        </h1>
        <p class="text-[10px] text-slate-400 font-mono">
          DAVAO CENTRALE HUBS • LOCAL GEOMETRICS
        </p>
      </div>
    </div>

    <div class="flex items-center gap-4 text-xs text-slate-400 font-mono">
      <!-- Floor selector -->
      <select v-model="selectedFloorId" @change="onFloorChange" class="bg-slate-950 border border-vxi-navy/30 rounded px-2 py-1 text-white focus:outline-none focus:border-vxi-red">
        <option v-for="floor in floors" :key="floor.id" :value="floor.id">
          {{ floor.name }} – {{ floor.campaign }}
        </option>
      </select>

      <div class="h-6 w-px bg-vxi-navy/30"></div>

      <a href="/" class="font-bold text-slate-400 hover:text-white transition">Back to Main HUD</a>
    </div>
  </header>

  <!-- ==================== MAIN AREA ==================== -->
  <div class="flex flex-1 overflow-hidden relative bg-[#050B14]">

            <!-- Agent -->
            <div class="flex items-center gap-2 p-2 bg-slate-950/50 rounded-lg border border-vxi-navy/25">
              <span class="h-3 w-3 rounded bg-emerald-500 border border-emerald-400"></span>
              <div>
                <p class="font-extrabold text-white">Agent Station</p>
                <p class="text-[9px] text-slate-500">Regular BPO workspace</p>
              </div>
            </div>
            <!-- Support -->
            <div class="flex items-center gap-2 p-2 bg-slate-950/50 rounded-lg border border-vxi-navy/25">
              <span class="h-3 w-3 rounded bg-cyan-500 border border-cyan-400"></span>
              <div>
                <p class="font-extrabold text-white">Support Station</p>
                <p class="text-[9px] text-slate-500">SME / QA / Real‑Time Support</p>
              </div>
            </div>
            <!-- OM -->
            <div class="flex items-center gap-2 p-2 bg-slate-950/50 rounded-lg border border-vxi-navy/25">
              <span class="h-3 w-3 rounded bg-vxi-red border border-vxi-red-light animate-pulse"></span>
              <div>
                <p class="font-extrabold text-white">OM Station</p>
                <p class="text-[9px] text-slate-500">Operations Manager Desk</p>
              </div>
            </div>
          </div>
        </div>

        <button @click="openCreateAssetModal" class="w-full py-2.5 bg-slate-900 border border-vxi-navy/30 hover:border-vxi-navy/60 text-slate-200 text-xs font-bold rounded-lg">
          Add Station
        </button>
      </div>
    </aside>

    <!-- ---------- MAIN SVG MAP ---------- -->
    <main class="flex-1 p-6 flex flex-col justify-between overflow-y-auto dashboard-scanlines relative z-0">
      <!-- Search bar -->
      <div class="flex items-center gap-2 mb-4">
        <input type="text" v-model="searchQuery" placeholder="Search workstations…" class="flex-1 w-full px-3 py-1.5 bg-slate-950 border border-vxi-navy/30 rounded text-xs text-slate-300 focus:outline-none focus:border-vxi-red transition font-mono" />
      </div>

      <svg viewBox="0 0 2000 1000" class="w-full h-auto max-w-[1600px] aspect-[2/1] bg-[#001024] relative z-10">
        <template v-for="ws in filteredWorkstations" :key="ws.id">
          <g class="movable-station" :class="{selected: selectedAsset && selectedAsset.id===ws.id}" @mousedown="startDrag(ws, $event)" @click.stop="selectAsset(ws)">
            <rect :x="ws.x" :y="ws.y" width="46" height="34" rx="5"
                  :fill="selectedAsset && selectedAsset.id===ws.id ? '#E31B23' : '#001024'"
                  :fill-opacity="selectedAsset && selectedAsset.id===ws.id ? '0.2' : '0.9'"
                  :stroke="selectedAsset && selectedAsset.id===ws.id ? '#FF2E37' : (ws.type==='agent' ? '#10b981' : (ws.type==='support' ? '#22d3ee' : '#E31B23'))"
                  stroke-width="2" />
            <circle :cx="ws.x + 9" :cy="ws.y + 10" r="4"
                    :fill="ws.type === 'agent' ? '#10b981' : (ws.type === 'support' ? '#22d3ee' : '#E31B23')" />
            <text :x="ws.x + 23" :y="ws.y + 26" fill="#f1f5f9" font-size="9" font-family="monospace" font-weight="black" text-anchor="middle">
              {{ ws.name }}
            </text>
          </g>
        </template>
      </svg>

      <!-- Bottom tip banner -->
      <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between text-[9px] font-mono bg-slate-950/80 px-4 py-2 border border-vxi-navy/35 rounded-xl">
        <span class="text-slate-400">💡 TIP: CLICK ANY STATION ON MAP THEN ADJUST COORDINATES TO ALTER MAP GEOMETRICS</span>
        <span class="text-vxi-cyan">ACTIVE COORDINATE LAYER</span>
      </div>
    </main>

      </div>

      <!-- Filled state -->
      <div v-else class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-vxi-navy/20 pb-3">
          <div>
            <span class="text-[8px] font-black px-2 py-0.5 rounded border uppercase tracking-widest"
                  :class="badgeClass(selectedAsset.type)">
              {{ selectedAsset.type }} Station
            </span>
            <h3 class="text-xl font-extrabold text-white mt-1">Workspace {{ selectedAsset.name }}</h3>
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

        <!-- Details -->
        <div class="space-y-2 text-[11px] font-mono bg-slate-950/60 p-4 rounded-xl border border-vxi-navy/25">
          <div class="flex justify-between border-b border-vxi-navy/15 pb-1.5">
            <span class="text-slate-500">Hostname:</span>
            <span class="text-white font-bold">{{ selectedAsset.hostname }}</span>
          </div>
          <div class="flex justify-between border-b border-vxi-navy/15 pb-1.5">
            <span class="text-slate-500">IP Segment:</span>
            <span class="text-vxi-cyan">{{ selectedAsset.ip }}</span>
          </div>
          <div class="flex justify-between border-b border-vxi-navy/15 pb-1.5">
            <span class="text-slate-500">MAC Addr:</span>
            <span class="text-slate-400 text-[10px]">{{ selectedAsset.mac }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Seat Category:</span>
            <span class="text-white font-bold capitalize">{{ selectedAsset.type }}</span>
          </div>
        </div>

        <!-- Coordinate editor + fine‑tuning -->
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

          <!-- Numpad fine‑tuning -->
          <div class="bg-slate-950/40 p-3 rounded-xl border border-vxi-navy/20 space-y-2">
            <span class="text-[9px] font-black text-slate-500 uppercase tracking-wider block text-center">Numpad Fine‑Tuning</span>
            <div class="flex justify-center">
              <button @click="moveSelectedAsset(0,-10)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded"><i data-lucide="chevron-up" class="h-4 w-4"></i></button>
            </div>
            <div class="flex justify-center gap-4">
              <button @click="moveSelectedAsset(-10,0)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded"><i data-lucide="chevron-left" class="h-4 w-4"></i></button>
              <button @click="moveSelectedAsset(10,0)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded"><i data-lucide="chevron-right" class="h-4 w-4"></i></button>
            </div>
            <div class="flex justify-center">
              <button @click="moveSelectedAsset(0,10)" class="p-1.5 bg-vxi-navy/30 hover:bg-vxi-navy/70 border border-vxi-navy/40 text-white rounded"><i data-lucide="chevron-down" class="h-4 w-4"></i></button>
            </div>
          </div>
        </div>

        <!-- Action buttons -->
        <div class="flex justify-end gap-2">
          <button @click="openEditAssetModal(selectedAsset)" class="w-full py-2.5 bg-slate-900 border border-vxi-navy/30 hover:border-vxi-navy/60 text-slate-200 text-xs font-bold rounded-lg">Edit Station Properties</button>
          <button @click="confirmDeleteAsset(selectedAsset)" class="w-full py-2.5 bg-vxi-red/10 hover:bg-vxi-red/20 border border-vxi-red/30 text-vxi-red text-xs font-bold rounded-lg">Delete Workstation</button>
        </div>
      </div>
    </template>
  </div>
</div>
<!-- End Asset Inspector Modal -->

  <!-- ==================== TOAST ALERT ==================== -->
  <div v-if="toast.visible" class="fixed bottom-6 right-6 z-50 max-w-sm rounded-xl border border-vxi-navy/40 bg-[#001024] p-4 shadow-2xl flex items-start space-x-3">
    <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-400 border border-emerald-500/20">
      <i data-lucide="check-circle" class="h-5 w-5"></i>
    </div>
    <div>
      <h4 class="text-xs font-extrabold text-white uppercase tracking-wider">{{ toast.title }}</h4>
      <p class="text-[10px] text-slate-400 mt-1">{{ toast.message }}</p>
    </div>
  </div>

  <!-- ==================== DELETE CONFIRM MODAL ==================== -->
  <div v-if="confirm.visible" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
    <div class="bg-[#001024] border border-vxi-navy/30 rounded-xl p-6 w-full max-w-sm shadow-2xl text-center space-y-4">
      <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-vxi-red/10 text-vxi-red border border-vxi-red/20">
        <i data-lucide="alert-triangle" class="h-6 w-6"></i>
      </div>
      <h3 class="text-sm font-extrabold text-white uppercase tracking-wider">Confirm Deletion</h3>
      <p class="text-xs text-slate-400">{{ confirm.message }}</p>
      <div class="flex justify-center gap-3 pt-2">
        <button @click="confirm.visible = false" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-slate-400 text-xs font-bold rounded-lg transition">Cancel</button>
        <button @click="confirm.callback" class="px-4 py-2 bg-vxi-red hover:bg-vxi-red/90 text-white text-xs font-bold rounded-lg transition">Yes, Delete</button>
      </div>
    </div>
  </div>
</template>

<script setup>
/* -------------------------------------------------
   Imports
   ------------------------------------------------- */
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';

/* -------------------------------------------------
   Reactive state
   ------------------------------------------------- */
const leftSidebarOpen = ref(true);
const rightSidebarOpen = ref(false);

const floors = ref([]);
const selectedFloorId = ref(null);
const selectedFloorName = ref('');

const workstations = ref([]);
const searchQuery = ref('');

const selectedAsset = ref(null); // currently inspected workstation
const draggingAsset = ref(null); // asset being dragged
let dragOffset = { x: 0, y: 0 };

// Toast & confirmation UI
const toast = reactive({
  visible: false,
  title: '',
  message: '',
  timeoutId: null,
});

const confirm = reactive({
  visible: false,
  message: '',
  callback: null,
});

/* -------------------------------------------------
   Computed helpers
   ------------------------------------------------- */
const filteredWorkstations = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return workstations.value;
  return workstations.value.filter(ws => {
    return (
      ws.name?.toLowerCase().includes(q) ||
      ws.hostname?.toLowerCase().includes(q) ||
      ws.ip?.toLowerCase().includes(q) ||
      ws.agent?.toLowerCase().includes(q)
    );
  });
});

/* -------------------------------------------------
   API helpers
   ------------------------------------------------- */
async function fetchFloors() {
  try {
    const { data } = await axios.get('/api/floors');
    floors.value = data;
    if (data.length) selectFloor(data[0]);
  } catch (e) {
    console.error('Failed to load floors', e);
    showToast('Error', 'Could not load floor list');
  }
}

async function fetchWorkstations() {
  try {
    const { data } = await axios.get('/api/workstations');
    workstations.value = data;
  } catch (e) {
    console.error('Failed to load workstations', e);
    showToast('Error', 'Could not load workstations');
  }
}

/* -------------------------------------------------
   UI actions
   ------------------------------------------------- */
function selectFloor(floor) {
  selectedFloorId.value = floor.id;
  selectedFloorName.value = floor.name;
  // Optionally filter workstations server‑side
  // await axios.get(`/api/floors/${floor.id}/workstations`).then(r => workstations.value = r.data);
}

function onFloorChange() {
  const floor = floors.value.find(f => f.id === selectedFloorId.value);
  if (floor) selectFloor(floor);
}

function selectAsset(ws) {
  selectedAsset.value = ws;
  rightSidebarOpen.value = true;
}

function startDrag(ws, ev) {
  draggingAsset.value = ws;
  const rect = ev.currentTarget.getBoundingClientRect();
  dragOffset.x = ev.clientX - rect.x;
  dragOffset.y = ev.clientY - rect.y;
}

function onMouseMove(ev) {
  if (!draggingAsset.value) return;
  const svg = document.querySelector('svg');
  const svgRect = svg.getBoundingClientRect();
  const newX = Math.round(ev.clientX - svgRect.left - dragOffset.x);
  const newY = Math.round(ev.clientY - svgRect.top - dragOffset.y);
  draggingAsset.value.x = newX;
  draggingAsset.value.y = newY;
}

function endDrag() {
  if (!draggingAsset.value) return;
  persistCoordinates(draggingAsset.value);
  draggingAsset.value = null;
}

async function persistCoordinates(ws) {
  if (!ws?.id) return;
  try {
    await axios.put(`/api/workstations/${ws.id}`, { x: ws.x, y: ws.y });
    showToast('Success', `Coordinates saved for ${ws.name}`);
  } catch (e) {
    console.error('Persist error', e);
    showToast('Error', 'Could not save coordinates');
  }
}

function moveSelectedAsset(dx, dy) {
  if (!selectedAsset.value) return;
  selectedAsset.value.x += dx;
  selectedAsset.value.y += dy;
  persistCoordinates(selectedAsset.value);
}

function showToast(title, message, duration = 3000) {
  toast.title = title;
  toast.message = message;
  toast.visible = true;
  clearTimeout(toast.timeoutId);
  toast.timeoutId = setTimeout(() => (toast.visible = false), duration);
}

function confirmDeleteAsset(asset) {
  confirm.message = `Delete workstation "${asset.name}" (ID ${asset.id})? This action cannot be undone.`;
  confirm.visible = true;
  confirm.callback = async () => {
    confirm.visible = false;
    try {
      await axios.delete(`/api/workstations/${asset.id}`);
      const idx = workstations.value.findIndex(w => w.id === asset.id);
      if (idx !== -1) workstations.value.splice(idx, 1);
      selectedAsset.value = null;
      showToast('Deleted', `Workstation ${asset.name} removed`);
    } catch (e) {
      console.error('Delete failed', e);
      showToast('Error', 'Could not delete workstation');
    }
  };
}

function badgeClass(type) {
  if (type === 'agent') return 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400';
  if (type === 'support') return 'bg-cyan-500/10 border-cyan-500/20 text-vxi-cyan';
  return 'bg-vxi-red/10 border-vxi-red/20 text-vxi-red';
}

/* -------------------------------------------------
   Lifecycle hooks
   ------------------------------------------------- */
onMounted(() => {
  fetchFloors();
  fetchWorkstations();
  window.addEventListener('mousemove', onMouseMove);
  window.addEventListener('mouseup', endDrag);
});

onBeforeUnmount(() => {
  window.removeEventListener('mousemove', onMouseMove);
  window.removeEventListener('mouseup', endDrag);
});
</script>

<style scoped>
/* Dark midnight theme enhancements */
.dashboard-scanlines {
  background: linear-gradient(
      rgba(18, 30, 49, 0) 50%,
      rgba(0, 0, 0, 0.15) 50%
    ),
    linear-gradient(
      90deg,
      rgba(227, 27, 35, 0.02),
      rgba(34, 211, 238, 0.01),
      rgba(0, 0, 255, 0.02)
    );
  background-size: 100% 4px, 6px 100%;
}

.movable-station {
  cursor: move;
  transition: transform 0.1s ease, filter 0.15s ease;
}
.movable-station:hover {
  filter: brightness(1.25);
  transform: scale(1.02);
}
.movable-station.selected rect {
  stroke: #FF2E37;
  stroke-width: 3;
}
</style>
