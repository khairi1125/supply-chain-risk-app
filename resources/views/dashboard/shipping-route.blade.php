@extends('layouts.app')

@section('title', 'Shipment Risk Planner - Supply Chain Risk Intelligence')
@section('page-title', 'Shipment Risk Planner')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
:root {
    --ocean-deep:#0f2b4a; --ocean-mid:#1a4a7a; --ocean:#2563eb;
    --cyan:#06b6d4; --gold:#f59e0b; --green:#10b981; --red:#ef4444;
    --surface:#fff; --surface2:#f8fafc; --border:#e2e8f0;
    --txt:#0f172a; --txt2:#64748b;
}
body { background:var(--surface2); }

/* ── Header ── */
.srp-header {
    background:linear-gradient(135deg,#0f2b4a 0%,#1a4a7a 60%,#1e3a5f 100%);
    border-radius:16px; padding:1.4rem 2rem; color:#fff !important;
    margin-bottom:1.2rem; position:relative; overflow:hidden;
    box-shadow:0 16px 50px rgba(15,43,74,.4);
}
.srp-header::before {
    content:''; position:absolute; top:-50px; right:-40px;
    width:240px; height:240px;
    background:radial-gradient(circle,rgba(6,182,212,.18) 0%,transparent 70%);
    border-radius:50%;
}
.srp-header h2 { font-size:1.6rem; font-weight:800; margin:0; position:relative; z-index:1; color:#fff !important; }
.srp-header p  { opacity:.85; margin:.25rem 0 0; font-size:.85rem; position:relative; z-index:1; color:#fff !important; }
.srp-badge {
    background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.25);
    border-radius:50px; padding:.35rem .9rem; font-size:.75rem; font-weight:600;
    position:relative; z-index:1; color:#fff !important; display:inline-block;
}

/* ── Config Bar ── */
.config-bar {
    background:var(--surface); border-radius:14px;
    border:1px solid var(--border);
    box-shadow:0 4px 20px rgba(0,0,0,.06);
    padding:1rem 1.4rem; margin-bottom:1rem;
}
.config-bar .cb-row {
    display:grid;
    grid-template-columns: 1fr auto 1fr 1fr auto auto 1fr auto;
    gap:.7rem; align-items:end;
}
.form-label { font-weight:600; font-size:.72rem; color:var(--txt2); text-transform:uppercase; letter-spacing:.4px; margin-bottom:.3rem; display:block; }
.form-select, .form-control {
    border:2px solid var(--border); border-radius:9px; padding:.55rem .85rem;
    font-size:.84rem; color:var(--txt); width:100%; transition:border-color .2s,box-shadow .2s;
}
.form-select:focus,.form-control:focus { border-color:var(--ocean); box-shadow:0 0 0 3px rgba(37,99,235,.15); outline:none; }
.ts-control { border:2px solid var(--border); border-radius:9px; padding:.55rem .85rem; font-size:.84rem; color:var(--txt); transition:border-color .2s,box-shadow .2s; }
.ts-control.focus { border-color:var(--ocean); box-shadow:0 0 0 3px rgba(37,99,235,.15); }
.ts-dropdown { border-radius:9px; border:1px solid var(--border); box-shadow:0 4px 20px rgba(0,0,0,.08); font-size:.84rem; }
.ts-dropdown .active { background-color: var(--ocean) !important; color: white !important; }

/* Swap btn */
.btn-swap {
    border:2px solid var(--border); border-radius:9px; background:#fff;
    padding:.55rem .85rem; color:var(--txt2); cursor:pointer;
    transition:all .2s; white-space:nowrap; font-size:.82rem;
    align-self:end; height:calc(2*.55rem + 1.2em + 4px);
}
.btn-swap:hover { border-color:var(--ocean); color:var(--ocean); background:#f0f5ff; }

/* Ship type select */
.ship-select { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:.35rem; }
.ship-btn {
    border:2px solid var(--border); border-radius:8px; padding:.4rem .3rem;
    cursor:pointer; transition:all .18s; text-align:center; background:#fff;
}
.ship-btn:hover { border-color:var(--ocean); background:#f0f5ff; }
.ship-btn.active { border-color:var(--ocean); background:linear-gradient(135deg,#eff6ff,#dbeafe); }
.ship-btn .si { font-size:1.1rem; display:block; }
.ship-btn .sn { font-size:.62rem; font-weight:700; color:var(--txt); display:block; }
.ship-btn .ss { font-size:.6rem; color:var(--txt2); display:block; }

/* Analyze btn */
.btn-analyze {
    background:linear-gradient(135deg,var(--ocean),var(--ocean-mid));
    border:none; color:#fff; font-weight:700; font-size:.88rem;
    padding:.55rem 1.4rem; border-radius:9px; white-space:nowrap;
    transition:all .25s; box-shadow:0 4px 15px rgba(37,99,235,.35);
    align-self:end; height:calc(2*.55rem + 1.2em + 4px);
}
.btn-analyze:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(37,99,235,.5); color:#fff; }
.btn-analyze:disabled { opacity:.6; transform:none; }

/* ── Map ── */
.map-panel { background:var(--surface); border-radius:14px; border:1px solid var(--border); overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.06); margin-bottom:1rem; }
.map-hd { padding:.8rem 1.3rem; background:linear-gradient(135deg,var(--ocean-deep),var(--ocean-mid)); color:#fff; font-weight:700; font-size:.85rem; display:flex; align-items:center; gap:.5rem; }
#routeMap { height:460px; width:100%; z-index:1; }

/* ── Results Row ── */
#resultsRow { display:none; }

/* Result card */
.rc {
    background:var(--surface); border-radius:14px; border:1px solid var(--border);
    box-shadow:0 4px 16px rgba(0,0,0,.06); overflow:hidden; height:100%;
}
.rc-hd { padding:.75rem 1.1rem; background:linear-gradient(135deg,var(--ocean-deep),var(--ocean-mid)); color:#fff; font-weight:700; font-size:.82rem; display:flex; align-items:center; gap:.45rem; }
.rc-body { padding:1rem 1.1rem; }

/* Metric */
.mc { display:flex; align-items:center; gap:.75rem; background:var(--surface2); border:1px solid var(--border); border-radius:9px; padding:.7rem .9rem; margin-bottom:.55rem; }
.mc-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
.mc-icon.blue  { background:#dbeafe; color:var(--ocean); }
.mc-icon.green { background:#d1fae5; color:var(--green); }
.mc-icon.gold  { background:#fef3c7; color:var(--gold); }
.mc-icon.cyan  { background:#cffafe; color:var(--cyan); }
.mc-icon.purple{ background:#ede9fe; color:#7c3aed; }
.mc-icon.red   { background:#fee2e2; color:var(--red); }
.mc-val { font-size:1rem; font-weight:800; color:var(--txt); line-height:1.15; }
.mc-lbl { font-size:.68rem; font-weight:600; text-transform:uppercase; letter-spacing:.4px; color:var(--txt2); }
.mc-sub { font-size:.74rem; color:var(--txt2); margin-top:.1rem; }

/* ETA */
.eta-card {
    background:linear-gradient(135deg,#fffbeb,#fef3c7);
    border:2px solid #fbbf24; border-radius:9px; padding:.85rem 1rem;
    margin-bottom:.55rem; text-align:center;
}
.eta-val { font-size:1.1rem; font-weight:800; color:#92400e; }
.eta-lbl { font-size:.68rem; font-weight:700; text-transform:uppercase; color:#b45309; letter-spacing:.4px; }
.eta-full { font-size:.74rem; color:#92400e; margin-top:.2rem; }

/* Recommendation */
.rec { border-radius:11px; padding:1rem 1.1rem; display:flex; align-items:flex-start; gap:.75rem; margin-bottom:.55rem; }
.rec.safe    { background:linear-gradient(135deg,#d1fae5,#a7f3d0); border:2px solid #10b981; }
.rec.caution { background:linear-gradient(135deg,#fef3c7,#fde68a); border:2px solid #f59e0b; }
.rec.danger  { background:linear-gradient(135deg,#fee2e2,#fecaca); border:2px solid #ef4444; }
.rec .rice { font-size:1.6rem; }
.rec .rttl { font-weight:800; font-size:.88rem; }
.rec .rtxt { font-size:.74rem; margin-top:.15rem; opacity:.85; }

/* Risk pill */
.rpill { display:inline-flex; align-items:center; padding:.22rem .65rem; border-radius:50px; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.4px; }
.rpill.low      { background:#d1fae5; color:#065f46; }
.rpill.medium   { background:#fef3c7; color:#92400e; }
.rpill.high     { background:#fee2e2; color:#991b1b; }
.rpill.critical { background:#fce7f3; color:#9d174d; }
.rpill.unknown  { background:#f1f5f9; color:#64748b; }

/* News */
.news-item { padding:.5rem 0; border-bottom:1px solid var(--border); }
.news-item:last-child { border-bottom:none; }
.news-item .nttl { font-size:.76rem; font-weight:600; color:var(--txt); line-height:1.35; }
.news-item .nmeta { font-size:.68rem; color:var(--txt2); margin-top:.15rem; }

/* Port strip */
.pstrip { display:flex; gap:.5rem; align-items:center; padding:.55rem .8rem; border-radius:8px; border:1px solid var(--border); background:var(--surface2); margin-bottom:.4rem; }
.pdot { width:11px; height:11px; border-radius:50%; flex-shrink:0; }
.pdot.o { background:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.2); }
.pdot.d { background:#ef4444; box-shadow:0 0 0 3px rgba(239,68,68,.2); }
.pname    { font-weight:700; font-size:.8rem; color:var(--txt); }
.pcountry { font-size:.7rem; color:var(--txt2); }

/* Sec label */
.sec-lbl { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--txt2); margin:.8rem 0 .4rem; display:flex; align-items:center; gap:.3rem; }
.sec-lbl::after { content:''; flex:1; height:1px; background:var(--border); }

/* spinner */
@keyframes spin{to{transform:rotate(360deg)}}
.spin{animation:spin .7s linear infinite}

/* Route divider */
.rdiv{display:flex;align-items:center;gap:.4rem;margin:.2rem 0;color:var(--txt2);font-size:.72rem;}
.rdiv::before,.rdiv::after{content:'';flex:1;height:1px;background:var(--border);}

/* Responsive */
@media(max-width:1200px){
    .config-bar .cb-row { grid-template-columns:1fr 1fr; }
    .ship-section { grid-column:1/-1; }
}
@media(max-width:768px){
    .config-bar .cb-row { grid-template-columns:1fr; }
    #routeMap { height:300px; }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="srp-header d-flex justify-content-between align-items-center">
        <div>
            <h2>⚓ Shipment Risk Planner</h2>
            <p>Estimasi rute, biaya kargo, dan analisis risiko pengiriman untuk eksportir & importir</p>
        </div>
        <span class="srp-badge">
            <i class="fas fa-anchor me-1"></i> {{ count($ports) }} Active Ports
        </span>
    </div>

    {{-- ═══ Configuration Bar ═══ --}}
    <div class="config-bar">
        <div class="row g-2 align-items-end">
            {{-- Origin --}}
            <div class="col-xl-3 col-md-5">
                <label class="form-label"><i class="fas fa-circle text-success me-1" style="font-size:.5rem"></i> Port of Origin</label>
                <select class="form-select" id="portOrigin">
                    <option value="">— Select Origin —</option>
                    @foreach($ports as $p)
                    <option value="{{ $p->id }}" data-lat="{{ $p->latitude }}" data-lon="{{ $p->longitude }}"
                        data-name="{{ $p->name }}" data-country="{{ $p->country }}" data-code="{{ $p->country_code }}">
                        {{ $p->name }} ({{ $p->country }})
                    </option>
                    @endforeach
                </select>
            </div>
            {{-- Swap --}}
            <div class="col-auto">
                <button class="btn-swap" id="swapBtn" title="Swap ports"><i class="fas fa-arrows-left-right"></i></button>
            </div>
            {{-- Destination --}}
            <div class="col-xl-3 col-md-5">
                <label class="form-label"><i class="fas fa-circle text-danger me-1" style="font-size:.5rem"></i> Port of Destination</label>
                <select class="form-select" id="portDest">
                    <option value="">— Select Destination —</option>
                    @foreach($ports as $p)
                    <option value="{{ $p->id }}" data-lat="{{ $p->latitude }}" data-lon="{{ $p->longitude }}"
                        data-name="{{ $p->name }}" data-country="{{ $p->country }}" data-code="{{ $p->country_code }}">
                        {{ $p->name }} ({{ $p->country }})
                    </option>
                    @endforeach
                </select>
            </div>
            {{-- Departure --}}
            <div class="col-xl-2 col-md-4">
                <label class="form-label"><i class="fas fa-calendar me-1"></i> Departure</label>
                <input type="datetime-local" class="form-control" id="departureTime">
            </div>
            {{-- Cargo --}}
            <div class="col-xl-1 col-md-3">
                <label class="form-label"><i class="fas fa-weight-hanging me-1"></i> Ton</label>
                <input type="number" class="form-control" id="cargoTon" placeholder="0" min="0" step="0.1">
            </div>
            <div class="col-xl-1 col-md-3">
                <label class="form-label"><i class="fas fa-cube me-1"></i> CBM</label>
                <input type="number" class="form-control" id="cargoCbm" placeholder="0" min="0" step="0.1">
            </div>
            {{-- Analyze --}}
            <div class="col-auto">
                <button class="btn-analyze" id="calcBtn" onclick="calculateRoute()">
                    <i class="fas fa-route me-1"></i> Analyze
                </button>
            </div>
        </div>

        {{-- Ship Type Row --}}
        <div class="row mt-2">
            <div class="col-12">
                <label class="form-label mb-1"><i class="fas fa-ship me-1"></i> Ship Type</label>
                <div class="ship-select">
                    <div class="ship-btn active" data-speed="20" data-rate="3200" data-type="Container Ship" onclick="selectShip(this)">
                        <span class="si">🚢</span><span class="sn">Container</span><span class="ss">~20 kts</span>
                    </div>
                    <div class="ship-btn" data-speed="14" data-rate="22" data-type="Bulk Carrier" onclick="selectShip(this)">
                        <span class="si">🛳️</span><span class="sn">Bulk Carrier</span><span class="ss">~14 kts</span>
                    </div>
                    <div class="ship-btn" data-speed="13" data-rate="18" data-type="Oil Tanker" onclick="selectShip(this)">
                        <span class="si">🛢️</span><span class="sn">Oil Tanker</span><span class="ss">~13 kts</span>
                    </div>
                    <div class="ship-btn" data-speed="12" data-rate="28" data-type="General Cargo" onclick="selectShip(this)">
                        <span class="si">📦</span><span class="sn">Gen. Cargo</span><span class="ss">~12 kts</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Map (Full Width) ═══ --}}
    <div class="map-panel">
        <div class="map-hd">
            <i class="fas fa-map"></i> Route Map
            <span class="ms-auto" id="mapStatus" style="font-size:.75rem;font-weight:400;opacity:.75;">Select ports and click Analyze to draw route</span>
        </div>
        <div id="routeMap"></div>
    </div>

    {{-- ═══ Results (Below Map, Full Width) ═══ --}}
    <div id="resultsRow" class="row g-3">

        {{-- Route + ETA --}}
        <div class="col-xl-3 col-lg-6">
            <div class="rc">
                <div class="rc-hd"><i class="fas fa-route"></i> Route Summary</div>
                <div class="rc-body" id="routeSummary"></div>
            </div>
        </div>

        {{-- Recommendation --}}
        <div class="col-xl-3 col-lg-6">
            <div class="rc">
                <div class="rc-hd"><i class="fas fa-shield-alt"></i> Risk & Recommendation</div>
                <div class="rc-body" id="riskPanel"></div>
            </div>
        </div>



    </div>

    {{-- Placeholder shown before analysis --}}
    <div id="placeholderRow" style="text-align:center;padding:2.5rem;color:var(--txt2);background:var(--surface);border-radius:14px;border:1px solid var(--border);">
        <div style="font-size:3rem;opacity:.2;margin-bottom:.75rem">⚓</div>
        <p style="font-size:.88rem;margin:0">Configure your shipment above and click <strong>Analyze</strong> to see the full risk analysis, cost estimate, weather, and recommendation.</p>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
let map, routeLine, originMarker, destMarker;
let selectedSpeed = 20, selectedRate = 3200, selectedShipType = 'Container Ship';
let allRisks = {};
let originSelect, destSelect;

// ── Init ──
document.addEventListener('DOMContentLoaded', () => {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    document.getElementById('departureTime').value = now.toISOString().slice(0,16);

    // Init searchable selects
    const tsOptions = { placeholder: "— Type to search port —", maxOptions: null };
    originSelect = new TomSelect('#portOrigin', tsOptions);
    destSelect   = new TomSelect('#portDest', tsOptions);

    map = L.map('routeMap').setView([20, 20], 2);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap', maxZoom:18 }).addTo(map);

    fetch('/api/risk-all').then(r=>r.json()).then(d=>{ allRisks = d; }).catch(()=>{});
});

// ── Ship select ──
function selectShip(el) {
    document.querySelectorAll('.ship-btn').forEach(c=>c.classList.remove('active'));
    el.classList.add('active');
    selectedSpeed = +el.dataset.speed;
    selectedRate  = +el.dataset.rate;
    selectedShipType = el.dataset.type;
    if (routeLine) calculateRoute();
}

// ── Swap ──
document.getElementById('swapBtn').addEventListener('click', () => {
    const oVal = originSelect.getValue();
    const dVal = destSelect.getValue();
    originSelect.setValue(dVal, true);
    destSelect.setValue(oVal, true);
    if (routeLine) calculateRoute();
});

// ── Main ──
async function calculateRoute() {
    const oVal = originSelect.getValue();
    const dVal = destSelect.getValue();
    if (!oVal || !dVal) { alert('Please select both ports.'); return; }
    if (oVal === dVal)  { alert('Origin and destination cannot be the same.'); return; }

    const oSel = document.getElementById('portOrigin');
    const dSel = document.getElementById('portDest');
    const oOpt = oSel.options[oSel.selectedIndex];
    const dOpt = dSel.options[dSel.selectedIndex];
    const origin = { lat:+oOpt.dataset.lat, lon:+oOpt.dataset.lon, name:oOpt.dataset.name, country:oOpt.dataset.country, code:oOpt.dataset.code };
    const dest   = { lat:+dOpt.dataset.lat, lon:+dOpt.dataset.lon, name:dOpt.dataset.name, country:dOpt.dataset.country, code:dOpt.dataset.code };

    const btn = document.getElementById('calcBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-circle-notch spin me-1"></i> Analyzing...';

    // Show skeleton
    document.getElementById('placeholderRow').style.display = 'none';
    document.getElementById('resultsRow').style.display = 'flex';
    ['routeSummary','riskPanel'].forEach(id => {
        document.getElementById(id).innerHTML = `<div style="text-align:center;padding:2rem;color:var(--txt2);"><i class="fas fa-circle-notch spin" style="font-size:1.5rem;opacity:.4;"></i></div>`;
    });

    // Calculate actual route path
    const routeCoords = getRouteCoords(origin, dest);
    let distKm = 0;
    for (let i = 0; i < routeCoords.length - 1; i++) {
        distKm += haversine(routeCoords[i][0], routeCoords[i][1], routeCoords[i+1][0], routeCoords[i+1][1]);
    }
    const distNm   = distKm / 1.852;
    const durHours = distNm / selectedSpeed;
    const departure = new Date(document.getElementById('departureTime').value);
    const arrival   = new Date(departure.getTime() + durHours * 3600000);
    const ton = +document.getElementById('cargoTon').value || 0;
    const cbm = +document.getElementById('cargoCbm').value || 0;

    drawRoutePath(origin, dest, routeCoords);
    document.getElementById('mapStatus').textContent = `${origin.name} → ${dest.name} · ${Math.round(distKm).toLocaleString()} km`;

    // Fetch destination data
    let destData = null;
    if (dest.code) {
        try { const r = await fetch(`/api/countries/${dest.code}`); destData = await r.json(); } catch(e){}
    }

    const originRisk = allRisks[origin.code] || null;
    const destRisk   = allRisks[dest.code]   || null;

    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-route me-1"></i> Analyze';

    renderRouteSummary({ origin, dest, distKm, distNm, durHours, departure, arrival, ton, cbm });
    renderRiskPanel({ origin, dest, originRisk, destRisk, destData });
    renderConditions({ destData });
    renderNews({ dest, destData });
}

// ── Route Summary ──
function renderRouteSummary({ origin, dest, distKm, distNm, durHours, departure, arrival, ton, cbm }) {
    const days = Math.floor(durHours/24), hrs = Math.round(durHours%24);
    const durStr = days > 0 ? `${days}d ${hrs}h` : `${Math.round(durHours)}h`;
    const fmtS = dt => dt.toLocaleString('id-ID',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
    const fmtF = dt => dt.toLocaleString('id-ID',{weekday:'long',day:'numeric',month:'long',year:'numeric',hour:'2-digit',minute:'2-digit'});

    let costHTML = '';
    if (ton > 0 || cbm > 0) {
        let cost = 0;
        if (selectedShipType === 'Container Ship') {
            const mult = distKm < 3000 ? 0.6 : distKm < 8000 ? 1.0 : 1.5;
            const teu = Math.max(Math.max(ton/25, cbm/28), 1);
            cost = Math.round(teu * selectedRate * mult);
        } else {
            const eff = ton > 0 ? ton : cbm * 0.9;
            cost = Math.round(eff * selectedRate * (distKm/5000));
        }
        costHTML = `<div class="mc" style="border-color:#ede9fe;background:linear-gradient(135deg,#faf5ff,#ede9fe)">
            <div class="mc-icon purple"><i class="fas fa-dollar-sign"></i></div>
            <div><div class="mc-val">~USD ${cost.toLocaleString()}</div><div class="mc-lbl">Estimated Freight Cost</div>
            <div class="mc-sub">${ton?ton+' ton ':''} ${cbm?cbm+' CBM':''}</div></div></div>`;
    }

    document.getElementById('routeSummary').innerHTML = `
        <div class="pstrip"><div class="pdot o"></div><div><div class="pname">${origin.name}</div><div class="pcountry">${origin.country}</div></div></div>
        <div class="rdiv"><i class="fas fa-ship" style="color:var(--ocean)"></i> ${selectedShipType} · ${selectedSpeed} kts</div>
        <div class="pstrip"><div class="pdot d"></div><div><div class="pname">${dest.name}</div><div class="pcountry">${dest.country}</div></div></div>
        <div class="sec-lbl"><i class="fas fa-ruler"></i> Metrics</div>
        <div class="mc"><div class="mc-icon blue"><i class="fas fa-ruler-combined"></i></div>
            <div><div class="mc-val">${Math.round(distKm).toLocaleString()} km</div>
            <div class="mc-lbl">Distance</div><div class="mc-sub">${Math.round(distNm).toLocaleString()} nautical miles</div></div></div>
        <div class="mc"><div class="mc-icon cyan"><i class="fas fa-clock"></i></div>
            <div><div class="mc-val">${durStr}</div><div class="mc-lbl">Duration</div>
            <div class="mc-sub">${durHours.toFixed(1)} hours total</div></div></div>
        <div class="mc"><div class="mc-icon green"><i class="fas fa-anchor"></i></div>
            <div><div class="mc-val" style="font-size:.88rem">${fmtS(departure)}</div><div class="mc-lbl">Departure</div></div></div>
        <div class="eta-card"><div class="eta-lbl">⚡ Estimated Arrival (ETA)</div>
            <div class="eta-val">${fmtS(arrival)}</div>
            <div class="eta-full">${fmtF(arrival)}</div></div>
        ${costHTML}`;
}

// ── Risk Panel ──
function renderRiskPanel({ origin, dest, originRisk, destRisk, destData }) {
    let recScore = 0, recReasons = [];
    if (destRisk) {
        const s = +destRisk.total_score;
        if (s >= 76) { recScore+=3; recReasons.push('Critical risk at destination'); }
        else if (s >= 51) { recScore+=2; recReasons.push('High risk at destination'); }
        else if (s >= 26) { recScore+=1; recReasons.push('Medium risk at destination'); }
    }
    if (destData?.weather?.risk_level === 'high')   { recScore+=2; recReasons.push('Adverse weather at destination'); }
    else if (destData?.weather?.risk_level === 'medium') { recScore+=1; recReasons.push('Moderate weather conditions'); }
    if (destData?.news?.sentiment?.overall_sentiment === 'negative') { recScore+=1; recReasons.push('Negative news sentiment'); }

    let recClass, recIcon, recTitle, recText;
    if (recScore >= 4) {
        recClass='danger'; recIcon='🚨'; recTitle='High Risk — Consider Delaying';
        recText = recReasons.join(' · ');
    } else if (recScore >= 2) {
        recClass='caution'; recIcon='⚠️'; recTitle='Moderate Risk — Proceed with Caution';
        recText = recReasons.join(' · ') || 'Monitor conditions closely.';
    } else {
        recClass='safe'; recIcon='✅'; recTitle='Low Risk — Good to Ship';
        recText = 'Conditions look favorable. Safe to proceed.';
    }

    document.getElementById('riskPanel').innerHTML = `
        <div class="rec ${recClass}">
            <div class="rice">${recIcon}</div>
            <div><div class="rttl">${recTitle}</div><div class="rtxt">${recText}</div></div>
        </div>
        <div class="sec-lbl"><i class="fas fa-globe"></i> Country Risk Scores</div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.45rem">
            <span style="font-size:.78rem;color:var(--txt)">🟢 ${origin.name}</span>
            ${originRisk ? `<span class="rpill ${originRisk.risk_level}">${originRisk.risk_level.toUpperCase()} · ${(+originRisk.total_score).toFixed(1)}</span>` : '<span class="rpill unknown">N/A</span>'}
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:.78rem;color:var(--txt)">🔴 ${dest.name}</span>
            ${destRisk ? `<span class="rpill ${destRisk.risk_level}">${destRisk.risk_level.toUpperCase()} · ${(+destRisk.total_score).toFixed(1)}</span>` : '<span class="rpill unknown">N/A</span>'}
        </div>`;
}

// ── Conditions Panel ──
function renderConditions({ destData }) {
    let html = '';
    if (destData?.weather) {
        const w = destData.weather;
        html += `<div class="sec-lbl"><i class="fas fa-cloud-sun"></i> Weather at Destination</div>
        <div class="mc"><div class="mc-icon cyan"><i class="fas fa-thermometer-half"></i></div>
            <div><div class="mc-val">${w.temperature ?? 'N/A'}°C · ${w.weather_condition ?? 'N/A'}</div>
            <div class="mc-lbl">Current Conditions</div>
            <div class="mc-sub">💨 ${w.wind_speed ?? 'N/A'} km/h · 🌧 ${w.rainfall ?? 0} mm ·
                <span class="rpill ${w.risk_level||'unknown'}" style="padding:.12rem .45rem;font-size:.62rem">${(w.risk_level||'unknown').toUpperCase()}</span>
            </div></div></div>`;
    }
    if (destData?.currency?.rate_to_usd) {
        const c = destData.currency;
        const ch = +c.change_7d || 0;
        const arrow = ch > 0 ? '📈' : ch < 0 ? '📉' : '➖';
        const color = Math.abs(ch) > 5 ? '#ef4444' : Math.abs(ch) > 2 ? '#f59e0b' : '#10b981';
        html += `<div class="sec-lbl"><i class="fas fa-coins"></i> Exchange Rate</div>
        <div class="mc"><div class="mc-icon gold"><i class="fas fa-coins"></i></div>
            <div><div class="mc-val">1 USD = ${(+c.rate_to_usd).toFixed(4)} ${c.code}</div>
            <div class="mc-lbl">Currency</div>
            <div class="mc-sub" style="color:${color}">${arrow} ${Math.abs(ch).toFixed(2)}% 7-day change</div>
            </div></div>`;
    }
    if (!html) html = '<div style="text-align:center;color:var(--txt2);padding:1.5rem;font-size:.82rem;">No condition data available</div>';
    document.getElementById('conditionsPanel').innerHTML = html;
}

// ── News Panel ──
function renderNews({ dest, destData }) {
    const articles = destData?.news?.articles?.slice(0,5) || [];
    const sentColors = { positive:'#10b981', neutral:'#64748b', negative:'#ef4444' };
    if (!articles.length) {
        document.getElementById('newsPanel').innerHTML = '<div style="text-align:center;color:var(--txt2);padding:1.5rem;font-size:.82rem;">No recent news found for this destination.</div>';
        return;
    }
    let html = `<div class="sec-lbl"><i class="fas fa-newspaper"></i> ${dest.country}</div>`;
    articles.forEach(a => {
        const sc = sentColors[a.sentiment] || '#64748b';
        html += `<div class="news-item">
            <div class="nttl"><a href="${a.url}" target="_blank" style="color:var(--txt);text-decoration:none;">${a.title}</a></div>
            <div class="nmeta"><span style="color:${sc};font-weight:700">${(a.sentiment||'neutral').toUpperCase()}</span> · ${a.source} · ${new Date(a.published_at).toLocaleDateString('id-ID')}</div>
        </div>`;
    });
    document.getElementById('newsPanel').innerHTML = html;
}

// ── Routing Engine (Maritime Graph) ──
const mNodes = {
    EU_NORTH: [51.5, 2.5], BALTIC_SEA: [57.0, 19.0], SPAIN_COAST: [43.0, -10.0],
    GIBRALTAR: [35.9, -5.5], WEST_AFRICA_N: [15.0, -18.0], WEST_AFRICA_S: [-10.0, -10.0],
    MED_WEST: [38.0, 5.0], MED_EAST: [34.0, 25.0], BLACK_SEA: [43.0, 35.0],
    SUEZ: [31.0, 32.3], RED_SEA: [20.0, 39.0], BAB_EL_MANDEB: [12.6, 43.3],
    EAST_AFRICA: [-5.0, 45.0], CAPE_GOOD_HOPE: [-36.0, 20.0],
    PERSIAN_GULF: [24.0, 56.0], ARABIAN_SEA: [15.0, 60.0], INDIA_SOUTH: [5.0, 80.0],
    BAY_OF_BENGAL: [13.0, 88.0], MALACCA: [4.0, 99.0], SINGAPORE: [1.2, 104.0],
    SOUTH_CHINA_SEA: [12.0, 112.0], EAST_CHINA_SEA: [28.0, 125.0], JAPAN_SEA: [38.0, 135.0],
    PACIFIC_WEST: [20.0, 140.0], PACIFIC_MID: [20.0, -170.0], PACIFIC_EAST: [20.0, -120.0],
    US_WEST: [35.0, -125.0], PANAMA_PACIFIC: [8.0, -80.0], PANAMA_CANAL: [9.1, -79.7],
    CARIBBEAN: [15.0, -75.0], GULF_MEXICO: [25.0, -90.0], US_EAST: [35.0, -70.0],
    ATLANTIC_NORTH: [45.0, -30.0], ATLANTIC_MID: [20.0, -40.0], ATLANTIC_SOUTH: [-20.0, -20.0],
    BRAZIL_COAST: [-15.0, -38.0], ARGENTINA_COAST: [-40.0, -55.0], CAPE_HORN: [-57.0, -67.0],
    INDIAN_OCEAN: [-20.0, 65.0], AUSTRALIA_WEST: [-25.0, 110.0],
    AUSTRALIA_SOUTH: [-40.0, 130.0], AUSTRALIA_EAST: [-25.0, 155.0]
};
const mEdges = [
    ['BALTIC_SEA', 'EU_NORTH'], ['EU_NORTH', 'SPAIN_COAST'], ['SPAIN_COAST', 'GIBRALTAR'],
    ['SPAIN_COAST', 'ATLANTIC_NORTH'], ['GIBRALTAR', 'WEST_AFRICA_N'], ['WEST_AFRICA_N', 'WEST_AFRICA_S'],
    ['WEST_AFRICA_S', 'CAPE_GOOD_HOPE'], ['WEST_AFRICA_S', 'ATLANTIC_SOUTH'], ['GIBRALTAR', 'ATLANTIC_MID'],
    ['GIBRALTAR', 'MED_WEST'], ['MED_WEST', 'MED_EAST'], ['MED_EAST', 'BLACK_SEA'],
    ['MED_EAST', 'SUEZ'], ['SUEZ', 'RED_SEA'], ['RED_SEA', 'BAB_EL_MANDEB'],
    ['BAB_EL_MANDEB', 'ARABIAN_SEA'], ['BAB_EL_MANDEB', 'EAST_AFRICA'], ['EAST_AFRICA', 'CAPE_GOOD_HOPE'],
    ['EAST_AFRICA', 'INDIAN_OCEAN'], ['PERSIAN_GULF', 'ARABIAN_SEA'], ['ARABIAN_SEA', 'INDIA_SOUTH'],
    ['INDIA_SOUTH', 'BAY_OF_BENGAL'], ['INDIA_SOUTH', 'MALACCA'], ['INDIA_SOUTH', 'INDIAN_OCEAN'],
    ['BAY_OF_BENGAL', 'MALACCA'], ['MALACCA', 'SINGAPORE'], ['SINGAPORE', 'SOUTH_CHINA_SEA'],
    ['SINGAPORE', 'AUSTRALIA_WEST'], ['SOUTH_CHINA_SEA', 'EAST_CHINA_SEA'], ['EAST_CHINA_SEA', 'JAPAN_SEA'],
    ['EAST_CHINA_SEA', 'PACIFIC_WEST'], ['PACIFIC_WEST', 'PACIFIC_MID'], ['PACIFIC_WEST', 'AUSTRALIA_EAST'],
    ['PACIFIC_MID', 'PACIFIC_EAST'], ['PACIFIC_EAST', 'US_WEST'], ['PACIFIC_EAST', 'PANAMA_PACIFIC'],
    ['PANAMA_PACIFIC', 'PANAMA_CANAL'], ['PANAMA_PACIFIC', 'CAPE_HORN'], ['PANAMA_CANAL', 'CARIBBEAN'],
    ['CARIBBEAN', 'GULF_MEXICO'], ['CARIBBEAN', 'US_EAST'], ['CARIBBEAN', 'ATLANTIC_MID'],
    ['US_EAST', 'ATLANTIC_NORTH'], ['ATLANTIC_NORTH', 'ATLANTIC_MID'], ['ATLANTIC_MID', 'ATLANTIC_SOUTH'],
    ['ATLANTIC_MID', 'BRAZIL_COAST'], ['BRAZIL_COAST', 'ARGENTINA_COAST'], ['ARGENTINA_COAST', 'CAPE_HORN'],
    ['ATLANTIC_SOUTH', 'CAPE_GOOD_HOPE'], ['ATLANTIC_SOUTH', 'BRAZIL_COAST'], ['CAPE_GOOD_HOPE', 'INDIAN_OCEAN'],
    ['INDIAN_OCEAN', 'AUSTRALIA_WEST'], ['AUSTRALIA_WEST', 'AUSTRALIA_SOUTH'], ['AUSTRALIA_SOUTH', 'AUSTRALIA_EAST']
];
const graph = {};
for (let n in mNodes) graph[n] = [];
mEdges.forEach(e => {
    const d = haversine(mNodes[e[0]][0], mNodes[e[0]][1], mNodes[e[1]][0], mNodes[e[1]][1]);
    graph[e[0]].push({ node: e[1], dist: d });
    graph[e[1]].push({ node: e[0], dist: d });
});

function findClosestNode(lat, lon) {
    let closest = null, minD = Infinity;
    for (let n in mNodes) {
        let d = haversine(lat, lon, mNodes[n][0], mNodes[n][1]);
        if (d < minD) { minD = d; closest = n; }
    }
    return closest;
}

function getRouteCoords(origin, dest) {
    const startNode = findClosestNode(origin.lat, origin.lon);
    const endNode = findClosestNode(dest.lat, dest.lon);
    let coords = [[origin.lat, origin.lon]];
    
    if (startNode !== endNode) {
        const distances = {}, prev = {};
        const q = new Set();
        for (let n in mNodes) { distances[n] = Infinity; prev[n] = null; q.add(n); }
        distances[startNode] = 0;
        
        while (q.size > 0) {
            let u = null;
            for (let n of q) { if (!u || distances[n] < distances[u]) u = n; }
            if (distances[u] === Infinity || u === endNode) break;
            q.delete(u);
            for (let neighbor of graph[u]) {
                if (q.has(neighbor.node)) {
                    let alt = distances[u] + neighbor.dist;
                    if (alt < distances[neighbor.node]) {
                        distances[neighbor.node] = alt;
                        prev[neighbor.node] = u;
                    }
                }
            }
        }
        
        const path = [];
        let curr = endNode;
        while (curr) { path.unshift(curr); curr = prev[curr]; }
        path.forEach(n => coords.push(mNodes[n]));
    }
    coords.push([dest.lat, dest.lon]);
    return coords;
}

// ── Smooth Curve Interpolation ──
function getGreatCirclePoints(lat1, lon1, lat2, lon2, numPts = 15) {
    const pts = [];
    const r = Math.PI / 180;
    const p1 = lat1 * r, l1 = lon1 * r, p2 = lat2 * r, l2 = lon2 * r;
    const d = Math.acos(Math.sin(p1)*Math.sin(p2) + Math.cos(p1)*Math.cos(p2)*Math.cos(l2-l1));
    if (d === 0 || isNaN(d)) return [[lat1, lon1], [lat2, lon2]];
    
    for (let i = 0; i <= numPts; i++) {
        const f = i / numPts;
        const A = Math.sin((1 - f) * d) / Math.sin(d);
        const B = Math.sin(f * d) / Math.sin(d);
        const x = A * Math.cos(p1) * Math.cos(l1) + B * Math.cos(p2) * Math.cos(l2);
        const y = A * Math.cos(p1) * Math.sin(l1) + B * Math.cos(p2) * Math.sin(l2);
        const z = A * Math.sin(p1) + B * Math.sin(p2);
        pts.push([Math.atan2(z, Math.sqrt(x*x + y*y)) / r, Math.atan2(y, x) / r]);
    }
    return pts;
}

// ── Draw Route ──
function drawRoutePath(origin, dest, pts) {
    if (routeLine)    map.removeLayer(routeLine);
    if (originMarker) map.removeLayer(originMarker);
    if (destMarker)   map.removeLayer(destMarker);

    const mkGreen = L.divIcon({ html:'<div style="background:#10b981;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.4)"></div>', iconSize:[14,14], iconAnchor:[7,7], className:'' });
    const mkRed   = L.divIcon({ html:'<div style="background:#ef4444;width:14px;height:14px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.4)"></div>', iconSize:[14,14], iconAnchor:[7,7], className:'' });

    originMarker = L.marker([origin.lat,origin.lon],{icon:mkGreen}).addTo(map).bindPopup(`<b>🟢 ${origin.name}</b><br>${origin.country}`);
    destMarker   = L.marker([dest.lat,  dest.lon],  {icon:mkRed  }).addTo(map).bindPopup(`<b>🔴 ${dest.name}</b><br>${dest.country}`);

    // Generate smooth curve
    const curvedPts = [];
    for (let i = 0; i < pts.length - 1; i++) {
        const segment = getGreatCirclePoints(pts[i][0], pts[i][1], pts[i+1][0], pts[i+1][1], 20);
        if (i > 0) segment.shift();
        curvedPts.push(...segment);
    }
    
    // Fix antimeridian jump (IDL crossing)
    let offset = 0;
    for (let i = 1; i < curvedPts.length; i++) {
        let dl = curvedPts[i][1] - (curvedPts[i-1][1] - offset);
        if (dl > 180) offset -= 360;
        else if (dl < -180) offset += 360;
        curvedPts[i][1] += offset;
    }

    routeLine = L.polyline(curvedPts, { color:'#2563eb', weight:4, opacity:.8, dashArray:'8,8', lineJoin:'round' }).addTo(map);
    map.fitBounds(routeLine.getBounds(), { padding:[60,60] });
}

// ── Haversine ──
function haversine(la1,lo1,la2,lo2) {
    const R=6371, dL=rad(la2-la1), dO=rad(lo2-lo1);
    const a=Math.sin(dL/2)**2+Math.cos(rad(la1))*Math.cos(rad(la2))*Math.sin(dO/2)**2;
    return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
}
function rad(d){ return d*Math.PI/180; }
</script>
@endpush
