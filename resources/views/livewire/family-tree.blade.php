<div id="tree-root">
<div class="ft-layout">

    {{-- ── Tree canvas ───────────────────────────────────────── --}}
    <div id="tree-viewport" class="ft-viewport">

        @if($people->isEmpty())
            <div class="ft-empty-state" dir="rtl">
                <p class="ft-empty-message">لا يوجد أشخاص بعد</p>
                <a href="{{ route('people.create') }}" class="ft-empty-link">
                    + إضافة أول شخص
                </a>
            </div>
        @else
            <svg id="ftree" class="ft-svg-canvas">
                <defs>
                    <filter id="ftGlow" x="-40%" y="-40%" width="180%" height="180%">
                        <feGaussianBlur in="SourceGraphic" stdDeviation="3" result="blur"/>
                        <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
                    </filter>
                </defs>
                <g id="tree-group"></g>
            </svg>
        @endif

        {{-- Zoom controls --}}
        <div class="ft-zoom-controls">
            <button id="btn-zoom-in"   class="tree-ctrl-btn">+</button>
            <button id="btn-zoom-out"  class="tree-ctrl-btn">−</button>
            <button id="btn-zoom-fit"  class="tree-ctrl-btn" title="ضبط الشاشة">⌖</button>
            <button id="btn-patrilineal" class="tree-ctrl-btn" title="عرض عائلة واحدة فقط (رجال النسب)">عائلة واحدة</button>
        </div>

        {{-- Mobile panel toggle --}}
        <button id="panel-toggle" class="ft-panel-toggle">☰</button>
    </div>

    {{-- ── Side panel ─────────────────────────────────────────── --}}
    <aside id="side-panel" class="ft-side-panel">

        {{-- Selection --}}
        <div class="ft-panel-section ft-selection-section" dir="rtl">
            <p class="ft-section-label">القرابة بين شخصَين</p>
            <div class="ft-chips-list">
                <div id="chip-a" class="selection-chip">
                    <span class="chip-badge chip-badge--a">أ</span>
                    <span id="chip-a-name" class="ft-chip-name">انقر شخصاً في الشجرة</span>
                    <button id="chip-a-clear" onclick="clearSelectedA()" class="chip-clear ft-hidden">×</button>
                </div>
                <div id="chip-b" class="selection-chip">
                    <span class="chip-badge chip-badge--b">ب</span>
                    <span id="chip-b-name" class="ft-chip-name">اختر شخصاً ثانياً</span>
                    <button id="chip-b-clear" onclick="clearSelectedB()" class="chip-clear ft-hidden">×</button>
                </div>
            </div>
            <p id="panel-hint" class="ft-panel-hint">انقر على أي شخص</p>
        </div>

        {{-- Kinship result --}}
        <div id="kinship-panel" class="ft-panel-section ft-kinship-panel ft-hidden" dir="rtl">
            <p class="ft-section-label">نتيجة القرابة</p>
            <div id="kinship-loading" class="ft-kinship-loading ft-hidden">جاري الحساب…</div>
            <div id="kinship-result" class="ft-hidden">
                <p class="ft-kinship-direction ft-kinship-direction--a">ب بالنسبة لـ أ</p>
                <div id="kinship-label-primary" class="ft-kinship-label-primary"></div>
                <div id="kinship-desc-primary" class="ft-kinship-desc"></div>
                <div id="kinship-secondary" class="ft-kinship-secondary ft-hidden">
                    <p class="ft-kinship-direction ft-kinship-direction--b">أ بالنسبة لـ ب</p>
                    <div id="kinship-label-secondary" class="ft-kinship-label-secondary"></div>
                    <div id="kinship-desc-secondary" class="ft-kinship-desc"></div>
                </div>
            </div>
            <div id="kinship-no-relation" class="ft-no-relation ft-hidden">
                لا توجد صلة قرابة مسجلة
            </div>
        </div>

        {{-- Legend --}}
        <div class="ft-legend-section" dir="rtl">
            <p class="ft-section-label">مفتاح</p>
            <div class="ft-legend-items">
                <div class="legend-row">
                    <svg width="24" height="8"><line x1="0" y1="4" x2="24" y2="4" stroke="#3A5070" stroke-width="2.5"/></svg>
                    <span class="legend-label">رابط الأب</span>
                </div>
                <div class="legend-row">
                    <svg width="24" height="8"><line x1="0" y1="4" x2="24" y2="4" stroke="#3D7055" stroke-width="2" stroke-dasharray="0.5 7" stroke-linecap="round"/></svg>
                    <span class="legend-label">رابط الأم</span>
                </div>
                <div class="legend-row">
                    <svg width="24" height="8"><line x1="0" y1="4" x2="24" y2="4" stroke="#8A7228" stroke-width="2" stroke-dasharray="4 3"/></svg>
                    <span class="legend-label">زواج</span>
                </div>
                <div class="ft-legend-swatches">
                    <div class="legend-row">
                        <span class="ft-legend-swatch ft-legend-swatch--male"></span>
                        <span class="legend-label">ذكر</span>
                    </div>
                    <div class="legend-row">
                        <span class="ft-legend-swatch ft-legend-swatch--female"></span>
                        <span class="legend-label">أنثى</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Unlinked people --}}
        <div class="ft-unlinked-section" dir="rtl">
            <div class="ft-unlinked-header">
                <p class="ft-section-label">
                    غير مرتبطين <span id="unlinked-count" class="ft-unlinked-count"></span>
                </p>
                <a href="{{ route('people.create') }}" class="ft-unlinked-add-link">+ إضافة</a>
            </div>
            <div id="unlinked-list"></div>
            <p id="unlinked-empty" class="ft-unlinked-empty ft-hidden">
                جميع الأشخاص مرتبطون
            </p>
        </div>
    </aside>

</div>{{-- /flex row --}}

{{-- Link menu (drag-to-link) --}}
<div id="link-menu" class="ft-link-menu ft-hidden" dir="rtl">
    <p id="link-menu-title" class="ft-link-menu-title"></p>
    <div id="link-menu-buttons" class="ft-link-menu-buttons"></div>
    <button onclick="hideLinkMenu()" class="ft-link-menu-cancel">إلغاء</button>
</div>
<div id="link-menu-backdrop" class="ft-link-menu-backdrop ft-hidden" onclick="hideLinkMenu()"></div>

{{-- Mobile overlay --}}
<div id="panel-backdrop" class="ft-mobile-overlay ft-hidden" onclick="closeMobilePanel()"></div>

</div>{{-- /tree-root --}}

<style>
/* ── Utility ─────────────────────────────────────────────────────── */
.ft-hidden { display: none; }

/* ── Root layout ─────────────────────────────────────────────────── */
#tree-root {
    height: 100%;
    display: flex;
    flex-direction: column;
}
.ft-layout {
    flex: 1;
    display: flex;
    direction: ltr;
    overflow: hidden;
    min-height: 0;
}

/* ── Tree viewport ───────────────────────────────────────────────── */
.ft-viewport {
    flex: 1;
    position: relative;
    overflow: hidden;
    cursor: grab;
    background: #0E1620;
}
.ft-svg-canvas {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    display: block;
}

/* ── Empty-state (no people yet) ─────────────────────────────────── */
.ft-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    gap: 16px;
}
.ft-empty-message { color: #6B829E; font-size: 0.95rem; }
.ft-empty-link {
    background: #2E5A4B;
    color: #E8DFD0;
    border-radius: 8px;
    padding: 8px 20px;
    text-decoration: none;
    font-size: 0.9rem;
}
.ft-empty-link:hover { filter: brightness(1.1); }

/* ── Zoom controls ───────────────────────────────────────────────── */
.ft-zoom-controls {
    position: absolute;
    bottom: 20px; right: 16px;
    z-index: 20;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.tree-ctrl-btn {
    width: 44px; height: 44px;
    border-radius: 7px;
    background: #1D2D42;
    border: 1px solid rgba(255,255,255,.1);
    color: #C8A63E;
    font-size: 22px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}
.tree-ctrl-btn:hover { background: #243650; }

/* ── Mobile panel-open button ────────────────────────────────────── */
.ft-panel-toggle {
    /* hidden on desktop; media query below flips it to flex */
    display: none;
    position: absolute;
    bottom: 20px; left: 16px;
    z-index: 20;
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #2E5A4B;
    border: 1px solid rgba(200,166,62,.3);
    color: #C8A63E;
    font-size: 18px;
    cursor: pointer;
    align-items: center;
    justify-content: center;
}

/* ── Side panel ──────────────────────────────────────────────────── */
.ft-side-panel {
    width: 320px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-left: 1px solid rgba(255,255,255,.07);
    background: #111A26;
    flex-shrink: 0;
}

/* ── Panel sections (generic) ────────────────────────────────────── */
.ft-panel-section {
    padding: 12px 18px 16px;
    border-bottom: 1px solid rgba(255,255,255,.06);
}
.ft-selection-section {
    padding-top: 16px;
    padding-bottom: 14px;
}
.ft-section-label {
    font-size: 0.9rem;
    color: #6B829E;
    letter-spacing: .06em;
    margin-bottom: 10px;
}

/* ── Selection chips ─────────────────────────────────────────────── */
.selection-chip {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px;
    border-radius: 8px;
    background: #141D2B;
    border: 1px solid rgba(255,255,255,.06);
    min-height: 44px;
}
.ft-chips-list { display: flex; flex-direction: column; gap: 8px; }
.chip-badge {
    width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.82rem; flex-shrink: 0;
}
.chip-badge--a { background: #1E4A36; color: #4ABA80; }
.chip-badge--b { background: #3A1A08; color: #E07040; }
.ft-chip-name { flex: 1; color: #6B829E; font-size: 0.95rem; }
.chip-clear {
    background: none; border: none; color: #6B829E;
    font-size: 18px; cursor: pointer; padding: 0; line-height: 1;
}
.chip-clear:hover { color: #C8C0B0; }
.ft-panel-hint { font-size: 0.9rem; color: #6B829E; margin-top: 8px; text-align: center; }

/* ── Kinship result panel ────────────────────────────────────────── */
.ft-kinship-panel { /* inherits ft-panel-section for spacing */ }
.ft-kinship-loading { text-align: center; color: #6B829E; font-size: 0.9rem; padding: 4px 0; }
.ft-kinship-direction { font-size: 0.88rem; margin-bottom: 4px; text-align: center; }
.ft-kinship-direction--a { color: #4A6A58; }
.ft-kinship-direction--b { color: #4A5A30; }
.ft-kinship-label-primary {
    font-size: 2.2rem;
    color: #C8A63E;
    text-align: center;
    line-height: 1.2;
    margin-bottom: 4px;
    font-family: 'Segoe UI', 'Arial Unicode MS', sans-serif;
}
.ft-kinship-label-secondary {
    font-size: 1.6rem;
    color: #C8A63E;
    opacity: .75;
    text-align: center;
    font-family: 'Segoe UI', 'Arial Unicode MS', sans-serif;
}
.ft-kinship-desc { font-size: 0.9rem; color: #6B829E; text-align: center; }
.ft-kinship-secondary {
    border-top: 1px solid rgba(255,255,255,.06);
    margin-top: 12px;
    padding-top: 12px;
}
.ft-no-relation { text-align: center; color: #6B829E; font-size: 0.9rem; padding: 4px 0; }

/* ── Legend ──────────────────────────────────────────────────────── */
.ft-legend-section { padding: 12px 18px; border-bottom: 1px solid rgba(255,255,255,.06); }
.ft-legend-items { display: flex; flex-direction: column; gap: 6px; }
.ft-legend-swatches { display: flex; gap: 16px; margin-top: 4px; }
.legend-row { display: flex; align-items: center; gap: 10px; }
.legend-label { font-size: 0.9rem; color: #6B829E; }
.ft-legend-swatch { display: inline-block; width: 10px; height: 10px; border-radius: 2px; }
.ft-legend-swatch--male   { background: #192B42; border: 1px solid #2E5060; }
.ft-legend-swatch--female { background: #2A1625; border: 1px solid #5A2E40; }

/* ── Unlinked people section ─────────────────────────────────────── */
.ft-unlinked-section { flex: 1; overflow-y: auto; padding: 12px 18px; }
.ft-unlinked-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 10px;
}
.ft-unlinked-count { color: #C8A63E; }
.ft-unlinked-add-link { font-size: 0.9rem; color: #4ABA80; text-decoration: none; }
.ft-unlinked-add-link:hover { text-decoration: underline; }
.ft-unlinked-empty { font-size: 0.9rem; color: #6B829E; text-align: center; padding: 10px 0; }

/* ── Unlinked people rows (built by JS) ──────────────────────────── */
.ft-unlinked-row {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px;
    border-radius: 7px;
    background: #141D2B;
    border: 1px solid rgba(255,255,255,.05);
    cursor: pointer;
    margin-bottom: 6px;
}
.ft-unlinked-row:hover { background: #1A2535; }
.ft-unlinked-dot { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }
.ft-unlinked-dot--male   { background: #192B42; border: 1px solid #2E5060; }
.ft-unlinked-dot--female { background: #2A1625; border: 1px solid #5A2E40; }
.ft-unlinked-name { flex: 1; font-size: 0.95rem; color: #8A9AB8; direction: rtl; }
.ft-unlinked-link { font-size: 0.9rem; color: #4ABA80; text-decoration: none; white-space: nowrap; }
.ft-unlinked-link:hover { text-decoration: underline; }

/* ── Drag-to-link menu ───────────────────────────────────────────── */
.ft-link-menu {
    position: fixed;
    z-index: 200;
    background: #1A2535;
    border: 1px solid rgba(200,166,62,.3);
    border-radius: 10px;
    padding: 14px 16px;
    min-width: 220px;
    box-shadow: 0 8px 32px rgba(0,0,0,.6);
}
.ft-link-menu-title { font-size: 0.95rem; color: #6B829E; margin-bottom: 12px; }
.ft-link-menu-buttons { display: flex; flex-direction: column; gap: 8px; }
.ft-link-menu-cancel {
    margin-top: 10px; width: 100%; padding: 8px;
    background: none;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 6px;
    color: #6B829E; font-size: 0.95rem; cursor: pointer;
}
.ft-link-menu-cancel:hover { border-color: rgba(255,255,255,.2); color: #C8C0B0; }
.ft-link-menu-backdrop { position: fixed; inset: 0; z-index: 199; }
.ft-link-no-options {
    color: #6B829E; font-size: 0.95rem;
    text-align: center; padding: 6px 0; direction: rtl;
}
.link-option-btn {
    padding: 9px 12px;
    border-radius: 7px;
    background: #1D2D42;
    border: 1px solid rgba(255,255,255,.1);
    color: #C8C0B0;
    font-size: 0.95rem;
    cursor: pointer;
    text-align: right;
    width: 100%;
}
.link-option-btn:hover { background: #243650; border-color: rgba(200,166,62,.3); color: #C8A63E; }

/* ── Mobile overlay (behind open side panel) ─────────────────────── */
.ft-mobile-overlay { position: fixed; inset: 0; z-index: 30; background: rgba(0,0,0,.5); }

/* ── Kinship name highlight (injected by JS into description) ─────── */
.ft-kinship-name { color: #C8C0B0; }

/* ── SVG node group ──────────────────────────────────────────────── */
.ft-node-group { cursor: pointer; user-select: none; }

/* ── Path highlight animation ────────────────────────────────────── */
@keyframes ftPathDraw { to { stroke-dashoffset: 0; } }
.ft-highlighted { stroke-dasharray: 2000; stroke-dashoffset: 2000; animation: ftPathDraw .55s ease forwards; }

/* ── Drag drop-target pulse ring ─────────────────────────────────── */
@keyframes ftDropRing {
    0%, 100% { opacity: 0.7; }
    50%      { opacity: 0.15; }
}
.ft-drop-ring { animation: ftDropRing 0.85s ease-in-out infinite; }

/* ── Mobile breakpoint ───────────────────────────────────────────── */
@media (max-width: 767px) {
    .ft-panel-toggle { display: flex !important; }
    #side-panel {
        position: fixed !important;
        top: 0; right: 0; bottom: 0;
        z-index: 40;
        transform: translateX(100%);
        transition: transform .28s ease;
        width: 88vw !important; max-width: 310px !important;
    }
    #side-panel.open { transform: translateX(0); }
    #panel-backdrop.open { display: block !important; }
}
</style>

<script>
(function () {

'use strict';

// ══════════════════════════════════════════════════════════════════
// 1. RAW DATA (from server)
// ══════════════════════════════════════════════════════════════════

const PEOPLE    = @json($people);
const PC        = @json($parentChild);
const MARRIAGES = @json($marriages);

if (!PEOPLE.length) return;

// ══════════════════════════════════════════════════════════════════
// 2. CONSTANTS
// ══════════════════════════════════════════════════════════════════

const NODE = Object.freeze({ width: 190, height: 88, cornerRadius: 12 });

const GAP = Object.freeze({
    couple:  240,   // center-to-center: husband → wife (must be > NODE.width for nodes not to overlap)
    sibling:  90,   // horizontal gap between subtrees
    level:   185,   // vertical gap between generations
    margin:  110,   // canvas left/top offset
    topPad:  120,   // vertical offset for generation 0
});

const FIT_PADDING      = 0.88;            // zoom-to-fit leaves this fraction of the viewport
const ROOT_X_GAP       = GAP.sibling * 3; // extra horizontal gap between structural-root subtrees
const LINK_SNAP_RADIUS = NODE.width / 2;  // dragged center must land inside the target rect to trigger

const COLOR = Object.freeze({
    nodeMale:           '#192B42',
    nodeFemale:         '#2A1625',
    borderMale:         '#2E5060',
    borderFemale:       '#5A2E40',
    nodeSelectedA:      '#1C4432',
    nodeSelectedB:      '#3D1A0E',
    nodeOnPath:         '#2C240E',
    nodeDimmed:         '#0F1820',
    borderSelectedA:    '#4ABA80',
    borderSelectedB:    '#E07040',
    borderOnPath:       '#C8A63E',
    borderDimmed:       '#1A2438',
    edgeFather:         '#3A5070',
    edgeMother:         '#3D7055',
    edgeMarriage:       '#8A7228',
    edgeHighlight:      '#C8A63E',
    textDefault:        '#C8C0B0',
    textOnPath:         '#C8A63E',
    textDimmed:         '#2A3A50',
    genderPipMale:      '#3A8A6A',
    genderPipFemale:    '#A04070',
});

// ══════════════════════════════════════════════════════════════════
// 3. MODE STATE & DATA-DERIVED MAPS
// rebuildLayout() recomputes all of these when mode changes.
// ══════════════════════════════════════════════════════════════════

let patrilinealMode = false; // show only males, father links only
let renderPeople    = null;  // PEOPLE filtered by active mode
let renderMarriages = null;  // MARRIAGES filtered (empty in patrilineal mode)

// Data-derived maps — declared here so all closures below reference
// the same bindings; reassigned by rebuildLayout() on each rebuild.
let personById;
let fatherChildrenOf, motherChildrenOf, hasFather, hasMother;
let wifeIds, wivesOf;
let structuralRoots;
let generationOf;
let subtreeWidthCache;
let basePositions;
let parentsOf;

// These arrow functions close over the let bindings — they automatically
// see the new values after every rebuildLayout() call.
const isMarriageOnlyWife = (id) => wifeIds.has(id) && !hasFather.has(id);
const placedWivesOf      = (id) => wivesOf[id].filter(isMarriageOnlyWife);

// positionOverrides is cleared (not reassigned) by rebuildLayout().
const positionOverrides = {};

function positionOf(personId) {
    return positionOverrides[personId] ?? basePositions[personId] ?? { x: 0, y: 0 };
}

function subtreeWidth(personId) {
    if (subtreeWidthCache[personId] !== undefined) return subtreeWidthCache[personId];

    const wifeCount  = placedWivesOf(personId).length;
    const coupleSpan = NODE.width + wifeCount * GAP.couple;
    const children   = fatherChildrenOf[personId].filter(id => !isMarriageOnlyWife(id));

    if (!children.length) {
        return (subtreeWidthCache[personId] = coupleSpan);
    }

    const childrenTotalWidth = children.reduce((sum, id) => sum + subtreeWidth(id), 0)
        + Math.max(0, children.length - 1) * GAP.sibling;

    return (subtreeWidthCache[personId] = Math.max(coupleSpan, childrenTotalWidth));
}

function placeSubtree(personId, coupleCenter, rowY) {
    const wives = placedWivesOf(personId);
    const hubX  = coupleCenter - wives.length * GAP.couple / 2;

    basePositions[personId] = { x: hubX, y: rowY };
    wives.forEach((wifeId, index) => {
        basePositions[wifeId] = { x: hubX + GAP.couple * (index + 1), y: rowY };
    });

    const children = fatherChildrenOf[personId].filter(id => !isMarriageOnlyWife(id));
    if (!children.length) return;

    const totalChildrenWidth = children.reduce((sum, id) => sum + subtreeWidth(id), 0)
        + Math.max(0, children.length - 1) * GAP.sibling;

    let nextChildX = coupleCenter - totalChildrenWidth / 2;
    children.forEach(childId => {
        const childWidth = subtreeWidth(childId);
        placeSubtree(childId, nextChildX + childWidth / 2, rowY + GAP.level);
        nextChildX += childWidth + GAP.sibling;
    });
}

function rebuildLayout() {
    // ── Compute active data based on mode ────────────────────────
    renderPeople    = patrilinealMode ? PEOPLE.filter(p => p.gender === 'male') : PEOPLE;
    renderMarriages = patrilinealMode ? [] : MARRIAGES;
    const activePC  = patrilinealMode ? PC.filter(pc => pc.type === 'father') : PC;

    // Clear drag overrides from the old layout
    Object.keys(positionOverrides).forEach(k => delete positionOverrides[k]);

    // ── Section 3: BUILD FAMILY LOOKUPS ──────────────────────────
    personById       = Object.fromEntries(renderPeople.map(p => [p.id, p]));
    fatherChildrenOf = Object.fromEntries(renderPeople.map(p => [p.id, []]));
    motherChildrenOf = Object.fromEntries(renderPeople.map(p => [p.id, []]));
    hasFather        = new Set();
    hasMother        = new Set();

    activePC.forEach(({ parent_id, child_id, type }) => {
        if (type === 'father') {
            hasFather.add(child_id);
            if (fatherChildrenOf[parent_id]) fatherChildrenOf[parent_id].push(child_id);
        } else {
            hasMother.add(child_id);
            if (motherChildrenOf[parent_id]) motherChildrenOf[parent_id].push(child_id);
        }
    });

    wifeIds = new Set(renderMarriages.map(m => m.wife_id));
    wivesOf = Object.fromEntries(renderPeople.map(p => [p.id, []]));
    renderMarriages.forEach(({ husband_id, wife_id }) => wivesOf[husband_id].push(wife_id));

    structuralRoots = renderPeople
        .map(p => p.id)
        .filter(id => !hasFather.has(id) && !isMarriageOnlyWife(id));

    // ── Section 4: ASSIGN GENERATIONS ────────────────────────────
    generationOf = {};
    structuralRoots.forEach(id => (generationOf[id] = 0));

    const bfsQueue = [...structuralRoots];
    for (let qi = 0; qi < bfsQueue.length; qi++) {
        const personId = bfsQueue[qi];
        fatherChildrenOf[personId].forEach(childId => {
            if (generationOf[childId] === undefined) {
                generationOf[childId] = generationOf[personId] + 1;
                bfsQueue.push(childId);
            }
        });
    }

    // Married-in husbands align to their wife's generation
    renderMarriages.forEach(({ husband_id, wife_id }) => {
        if (!hasFather.has(husband_id)) {
            generationOf[husband_id] = generationOf[wife_id] ?? 0;
        }
    });

    for (let pass = 0; pass < 5; pass++) {
        activePC.forEach(({ parent_id, child_id, type }) => {
            if (type === 'father' && generationOf[parent_id] !== undefined) {
                generationOf[child_id] = generationOf[parent_id] + 1;
            }
        });
    }

    renderMarriages.forEach(({ husband_id, wife_id }) => {
        if (isMarriageOnlyWife(wife_id)) generationOf[wife_id] = generationOf[husband_id] ?? 0;
    });

    renderPeople.forEach(p => { if (generationOf[p.id] === undefined) generationOf[p.id] = 0; });

    // ── Section 5: COMPUTE LAYOUT POSITIONS ──────────────────────
    subtreeWidthCache = {};
    basePositions     = {};

    let nextRootX = GAP.margin;
    [...structuralRoots]
        .sort((a, b) => (generationOf[a] ?? 0) - (generationOf[b] ?? 0) || a - b)
        .forEach(rootId => {
            const width = subtreeWidth(rootId);
            const rootY = (generationOf[rootId] ?? 0) * GAP.level + GAP.topPad;
            placeSubtree(rootId, nextRootX + width / 2, rootY);
            nextRootX += width + ROOT_X_GAP;
        });

    // Fallback: position any remaining people not placed by subtree traversal
    renderPeople.forEach(p => {
        if (!basePositions[p.id]) {
            basePositions[p.id] = { x: nextRootX + NODE.width / 2, y: (generationOf[p.id] ?? 0) * GAP.level + GAP.topPad };
            nextRootX += NODE.width + GAP.sibling;
        }
    });

    // ── Section 7: KINSHIP HIGHLIGHT PATH ────────────────────────
    parentsOf = Object.fromEntries(renderPeople.map(p => [p.id, []]));
    activePC.forEach(({ parent_id, child_id }) => parentsOf[child_id].push(parent_id));
}

rebuildLayout();

function togglePatrilinealMode() {
    patrilinealMode = !patrilinealMode;
    // Clear any selection that may reference people hidden in the new mode
    selectedPersonA = null;
    selectedPersonB = null;
    const btn = document.getElementById('btn-patrilineal');
    if (btn) btn.textContent = patrilinealMode ? 'الكل' : 'عائلة واحدة';
    rebuildLayout();
    render();
    fitTreeToViewport();
}

function findUpwardAncestors(personId) {
    // Returns Map<ancestorId, pathFromPersonUpToAncestor (ancestor included, personId excluded)>
    const ancestorPaths = new Map();
    const queue         = [[personId, []]];
    const visited       = new Set([personId]);
    while (queue.length) {
        const [currentId, pathSoFar] = queue.shift();
        for (const parentId of (parentsOf[currentId] || [])) {
            if (!visited.has(parentId)) {
                visited.add(parentId);
                const newPath = [...pathSoFar, parentId];
                ancestorPaths.set(parentId, newPath);
                queue.push([parentId, newPath]);
            }
        }
    }
    return ancestorPaths;
}

function findAncestralHighlightPath(aId, bId) {
    const ancestorsOfA = findUpwardAncestors(aId);
    const ancestorsOfB = findUpwardAncestors(bId);

    // B is a direct ancestor of A: path goes straight up from A to B
    if (ancestorsOfA.has(bId)) {
        return [aId, ...ancestorsOfA.get(bId)];
    }
    // A is a direct ancestor of B: reverse the upward path from B to A
    if (ancestorsOfB.has(aId)) {
        const upwardFromBToA = ancestorsOfB.get(aId); // [B's parent, ..., A]
        return [aId, ...upwardFromBToA.slice(0, -1).reverse(), bId];
    }

    // Find the closest common ancestor (LCA)
    let lcaId    = null;
    let lcaDist  = Infinity;
    let lcaPathA = null;
    let lcaPathB = null;

    for (const [ancestorId, pathA] of ancestorsOfA) {
        if (ancestorsOfB.has(ancestorId)) {
            const pathB = ancestorsOfB.get(ancestorId);
            const dist  = pathA.length + pathB.length;
            if (dist < lcaDist) {
                lcaDist  = dist;
                lcaId    = ancestorId;
                lcaPathA = pathA;
                lcaPathB = pathB;
            }
        }
    }

    if (lcaId === null) return null;

    // lcaPathA = [node1_above_A, ..., LCA]  — upward steps from A
    // lcaPathB = [node1_above_B, ..., LCA]  — upward steps from B
    // Display: A → ... up to LCA ... → down to B
    const downFromLcaToB = lcaPathB.slice(0, -1).reverse(); // intermediates between LCA and B, going down

    return [aId, ...lcaPathA, ...downFromLcaToB, bId];
}

// ══════════════════════════════════════════════════════════════════
// 8. SVG ELEMENT BUILDERS
// ══════════════════════════════════════════════════════════════════

const SVG_NAMESPACE = 'http://www.w3.org/2000/svg';

function svgElement(tag, attributes) {
    const el = document.createElementNS(SVG_NAMESPACE, tag);
    for (const [name, value] of Object.entries(attributes)) {
        el.setAttribute(name, value);
    }
    return el;
}

function buildEdge({ d, stroke, strokeWidth, dashArray = null, lineCap = null, opacity = 1, animated = false }) {
    const el = svgElement('path', {
        d,
        stroke,
        fill:           'none',
        'stroke-width': strokeWidth,
        opacity,
    });
    if (dashArray) el.setAttribute('stroke-dasharray', dashArray);
    if (lineCap)   el.setAttribute('stroke-linecap', lineCap);
    if (animated)  el.setAttribute('class', 'ft-highlighted');
    return el;
}

function buildNodeBackground({ centerX, centerY, fill, stroke, strokeWidth, opacity = 1 }) {
    return svgElement('rect', {
        x:              centerX - NODE.width  / 2,
        y:              centerY - NODE.height / 2,
        width:          NODE.width,
        height:         NODE.height,
        rx:             NODE.cornerRadius,
        ry:             NODE.cornerRadius,
        fill,
        stroke,
        'stroke-width': strokeWidth,
        opacity,
    });
}

function buildLabel({ labelX, labelY, text, fill, fontSize = 14, opacity = 1 }) {
    const el = svgElement('text', {
        x: labelX,
        y: labelY,
        'text-anchor':       'middle',
        'dominant-baseline': 'middle',
        fill,
        'font-size':         fontSize,
        'font-family':       "'Segoe UI','Noto Naskh Arabic',Arial,sans-serif",
        'pointer-events':    'none',
        direction:           'rtl',
    });
    if (opacity < 1) el.setAttribute('opacity', opacity);
    el.textContent = text;
    return el;
}

// ══════════════════════════════════════════════════════════════════
// 9. INTERACTION STATE
// ══════════════════════════════════════════════════════════════════

let selectedPersonA   = null;
let selectedPersonB   = null;
let wasDraggingNode   = false;  // suppresses click after a drag gesture
let dragHoverTargetId = null;   // node currently under the dragged node
let linkMenuDraggedId   = null;  // person whose drag triggered the open link menu
let linkMenuSnapBackPos = null;  // position to restore on cancel (null = no prior override → delete)

// ══════════════════════════════════════════════════════════════════
// 10. RENDERING
// ══════════════════════════════════════════════════════════════════

const svgEl     = document.getElementById('ftree');
const treeGroup = document.getElementById('tree-group');
// The SVG fills the viewport div via CSS (width:100%; height:100%).
// Do NOT set width/height/viewBox on it — that would create a second,
// narrower clip region that hides content when panning beyond the canvas size.

function parseHexColor(hex) {
    return {
        red:   parseInt(hex.slice(1, 3), 16),
        green: parseInt(hex.slice(3, 5), 16),
        blue:  parseInt(hex.slice(5, 7), 16),
    };
}

function interpolateColor(fromHex, toHex, position) {
    const from    = parseHexColor(fromHex);
    const to      = parseHexColor(toHex);
    const channel = (fromVal, toVal) => Math.round(fromVal + (toVal - fromVal) * position).toString(16).padStart(2, '0');
    return `#${channel(from.red, to.red)}${channel(from.green, to.green)}${channel(from.blue, to.blue)}`;
}

function edgeKey(personIdA, personIdB) {
    return personIdA < personIdB
        ? `${personIdA}-${personIdB}`
        : `${personIdB}-${personIdA}`;
}

function buildPathHighlights() {
    if (!selectedPersonA || !selectedPersonB) {
        return { pathNodeIds: new Set(), pathEdgeMap: new Map() };
    }

    const path      = findAncestralHighlightPath(selectedPersonA, selectedPersonB) || [];
    const edgeCount = path.length - 1;
    const pathNodeIds = new Set(path);
    const pathEdgeMap = new Map(); // edgeKey → gradientPosition (0.0 = A's end, 1.0 = B's end)

    for (let edgeIndex = 0; edgeIndex < edgeCount; edgeIndex++) {
        const key              = edgeKey(path[edgeIndex], path[edgeIndex + 1]);
        const gradientPosition = edgeCount > 1 ? edgeIndex / (edgeCount - 1) : 0.5;
        pathEdgeMap.set(key, gradientPosition);
    }
    return { pathNodeIds, pathEdgeMap };
}

function render() {
    treeGroup.innerHTML = '';

    // Called once per render and threaded through to all sub-renderers — avoids re-running the full BFS N times
    const { pathNodeIds, pathEdgeMap } = buildPathHighlights();
    const hasBothSelected = selectedPersonA !== null && selectedPersonB !== null;

    const isDimmed    = personId => hasBothSelected && !pathNodeIds.has(personId);
    const isOnPath    = personId => pathNodeIds.has(personId) && personId !== selectedPersonA && personId !== selectedPersonB;
    // Returns the gradient position (0.0 = A's end, 1.0 = B's end) for an edge on the path,
    // or null if the edge is not part of the highlighted path.
    const edgePathPosition = (personIdA, personIdB) => pathEdgeMap.get(edgeKey(personIdA, personIdB)) ?? null;

    renderMarriageArcs(treeGroup,  isDimmed);
    renderFatherEdges(treeGroup,   isDimmed, edgePathPosition);
    renderMotherEdges(treeGroup,   isDimmed, edgePathPosition);
    renderAllNodes(treeGroup, isDimmed, isOnPath, pathNodeIds, hasBothSelected, dragHoverTargetId);
}

function renderMarriageArcs(group, isDimmed) {
    renderMarriages.forEach(({ husband_id, wife_id }) => {
        const husband = positionOf(husband_id);
        const wife    = positionOf(wife_id);

        const startX    = husband.x + NODE.width / 2;
        const endX      = wife.x    - NODE.width / 2;
        const midX      = (startX + endX) / 2;
        const arcHeight = Math.max(20, Math.abs(endX - startX) * 0.16);
        const controlY  = husband.y - arcHeight;
        const opacity   = (isDimmed(husband_id) && isDimmed(wife_id)) ? 0.1 : 0.55;

        group.appendChild(buildEdge({
            d:           `M ${startX} ${husband.y} Q ${midX} ${controlY} ${endX} ${wife.y}`,
            stroke:      COLOR.edgeMarriage,
            strokeWidth: 2,
            dashArray:   '4 3',
            opacity,
        }));
    });
}

function renderFatherEdges(group, isDimmed, edgePathPosition) {
    Object.keys(fatherChildrenOf).forEach(parentIdStr => {
        const parentId = parseInt(parentIdStr);
        const children = fatherChildrenOf[parentId].filter(
            childId => !isMarriageOnlyWife(childId) && basePositions[childId]
        );
        if (!children.length) return;

        const parent       = positionOf(parentId);
        const parentBottom = parent.y + NODE.height / 2;
        const busY         = parentBottom + (GAP.level - NODE.height) * 0.42;

        const childPositions = children.map(id => ({ id, x: positionOf(id).x, y: positionOf(id).y }));
        const busLeft        = Math.min(parent.x, ...childPositions.map(c => c.x));
        const busRight       = Math.max(parent.x, ...childPositions.map(c => c.x));
        const allDimmed      = isDimmed(parentId) && children.every(id => isDimmed(id));
        const baseOpacity    = allDimmed ? 0.1 : 0.65;

        // ── Base bus structure (normal color, drawn first) ──────────
        // Always draw the full bus in the base color so the structural
        // layout is clear. Highlighted path overlays are drawn on top.
        group.appendChild(buildEdge({
            d:           `M ${parent.x} ${parentBottom} L ${parent.x} ${busY}`,
            stroke:      COLOR.edgeFather,
            strokeWidth: 2,
            opacity:     baseOpacity,
        }));

        if (busLeft < busRight) {
            group.appendChild(buildEdge({
                d:           `M ${busLeft} ${busY} L ${busRight} ${busY}`,
                stroke:      COLOR.edgeFather,
                strokeWidth: 2,
                opacity:     baseOpacity,
            }));
        }

        childPositions.forEach(({ id: childId, x: childX, y: childY }) => {
            group.appendChild(buildEdge({
                d:           `M ${childX} ${busY} L ${childX} ${childY - NODE.height / 2}`,
                stroke:      COLOR.edgeFather,
                strokeWidth: 2,
                opacity:     isDimmed(childId) ? 0.1 : baseOpacity,
            }));
        });

        // ── Highlighted overlay (drawn on top, per path-child) ──────
        // Each path edge gets its own 3-segment overlay that traces the
        // exact route: stem → horizontal to this child's column → drop.
        // This prevents the bus from glowing toward non-path siblings.
        childPositions.forEach(({ id: childId, x: childX, y: childY }) => {
            const gradientPosition = edgePathPosition(parentId, childId);
            if (gradientPosition === null) return;
            group.appendChild(buildEdge({
                d:           `M ${parent.x} ${parentBottom} L ${parent.x} ${busY} L ${childX} ${busY} L ${childX} ${childY - NODE.height / 2}`,
                stroke:      interpolateColor(COLOR.borderSelectedA, COLOR.borderSelectedB, gradientPosition),
                strokeWidth: 3,
                opacity:     1,
                animated:    true,
            }));
        });
    });
}

function renderMotherEdges(group, isDimmed, edgePathPosition) {
    Object.keys(motherChildrenOf).forEach(parentIdStr => {
        const parentId = parseInt(parentIdStr);
        const children = motherChildrenOf[parentId].filter(id => basePositions[id]);
        if (!children.length) return;

        const parent = positionOf(parentId);

        children.forEach(childId => {
            const child    = positionOf(childId);
            const gradientPosition = edgePathPosition(parentId, childId);
            const onPath           = gradientPosition !== null;
            const dimmed   = isDimmed(parentId) && isDimmed(childId);
            const startX   = parent.x + NODE.width * 0.15;
            const endX     = child.x  - NODE.width * 0.15;
            const busY     = parent.y + NODE.height / 2 + (GAP.level - NODE.height) * 0.42;

            group.appendChild(buildEdge({
                d:           `M ${startX} ${parent.y + NODE.height / 2} L ${startX} ${busY} L ${endX} ${busY} L ${endX} ${child.y - NODE.height / 2}`,
                stroke:      onPath ? interpolateColor(COLOR.borderSelectedA, COLOR.borderSelectedB, gradientPosition) : COLOR.edgeMother,
                strokeWidth: onPath ? 3 : 2,
                dashArray:   onPath ? null : '0.5 7',
                lineCap:     onPath ? null : 'round',
                opacity:     onPath ? 1 : (dimmed ? 0.08 : 0.45),
                animated:    onPath,
            }));
        });
    });
}

function nodeAppearanceFor(personId, pathNodeIds, hasBothSelected, dropTargetId) {
    const person       = personById[personId];
    const isMale       = person?.gender === 'male';
    const isSelectedA  = personId === selectedPersonA;
    const isSelectedB  = personId === selectedPersonB;
    const isDropTarget = personId === dropTargetId;
    const onPath       = pathNodeIds.has(personId) && !isSelectedA && !isSelectedB;
    const dimmed       = hasBothSelected && !pathNodeIds.has(personId);

    if (isDropTarget) return { fill: '#0A2218', stroke: COLOR.borderSelectedA, strokeWidth: 3, textFill: '#A0E8C0', opacity: 1 };
    if (isSelectedA)  return { fill: COLOR.nodeSelectedA, stroke: COLOR.borderSelectedA, strokeWidth: 2.5, textFill: '#D0F0E0', opacity: 1 };
    if (isSelectedB)  return { fill: COLOR.nodeSelectedB, stroke: COLOR.borderSelectedB, strokeWidth: 2.5, textFill: '#F0C8A8', opacity: 1 };
    if (onPath)       return { fill: COLOR.nodeOnPath,     stroke: COLOR.borderOnPath,     strokeWidth: 2,   textFill: COLOR.textOnPath,   opacity: 1 };
    if (dimmed)       return { fill: COLOR.nodeDimmed,     stroke: COLOR.borderDimmed,     strokeWidth: 1,   textFill: COLOR.textDimmed,   opacity: 0.3 };

    return {
        fill:        isMale ? COLOR.nodeMale : COLOR.nodeFemale,
        stroke:      isMale ? COLOR.borderMale : COLOR.borderFemale,
        strokeWidth: 1.5,
        textFill:    COLOR.textDefault,
        opacity:     1,
    };
}

function splitNameLines(fullName) {
    const words = (fullName || '').trim().split(/\s+/).filter(Boolean);
    return [words.slice(0, 2).join(' '), words.slice(2, 4).join(' ')];
}

function renderAllNodes(group, isDimmed, isOnPath, pathNodeIds, hasBothSelected, dropTargetId) {
    renderPeople.forEach(person => {
        const position  = positionOf(person.id);
        const appear    = nodeAppearanceFor(person.id, pathNodeIds, hasBothSelected, dropTargetId);
        const isMale    = person.gender === 'male';
        const isTarget  = person.id === dropTargetId;
        const nodeGroup = svgElement('g', { class: 'ft-node-group' });

        // Scale drop target up so it "magnetises" toward the dragged node
        if (isTarget) {
            nodeGroup.setAttribute('transform',
                `translate(${position.x},${position.y}) scale(1.15) translate(${-position.x},${-position.y})`
            );
        }

        nodeGroup.addEventListener('click', () => {
            if (wasDraggingNode) return;
            onNodeClick(person.id);
        });

        // Drop target outer ring — rendered first so it sits behind the node
        if (isTarget) {
            const ringEl = svgElement('rect', {
                x:              position.x - NODE.width  / 2 - 10,
                y:              position.y - NODE.height / 2 - 10,
                width:          NODE.width  + 20,
                height:         NODE.height + 20,
                rx:             NODE.cornerRadius + 10,
                ry:             NODE.cornerRadius + 10,
                fill:           'none',
                stroke:         COLOR.borderSelectedA,
                'stroke-width': 2.5,
                filter:         'url(#ftGlow)',
                class:          'ft-drop-ring',
            });
            nodeGroup.appendChild(ringEl);
        }

        // Glow ring for path-highlighted nodes
        if (isOnPath(person.id)) {
            nodeGroup.appendChild(svgElement('rect', {
                x:              position.x - NODE.width  / 2 - 5,
                y:              position.y - NODE.height / 2 - 5,
                width:          NODE.width  + 10,
                height:         NODE.height + 10,
                rx:             NODE.cornerRadius + 5,
                ry:             NODE.cornerRadius + 5,
                fill:           'none',
                stroke:         COLOR.borderOnPath,
                'stroke-width': 1,
                opacity:        0.35,
                filter:         'url(#ftGlow)',
            }));
        }

        nodeGroup.appendChild(buildNodeBackground({
            centerX:     position.x,
            centerY:     position.y,
            fill:        appear.fill,
            stroke:      appear.stroke,
            strokeWidth: appear.strokeWidth,
            opacity:     appear.opacity,
        }));

        const [nameLine1, nameLine2] = splitNameLines(person.name);
        const hasSecondLine  = nameLine2.trim().length > 0;
        const firstLineY     = position.y + (hasSecondLine ? -16 : 0);

        nodeGroup.appendChild(buildLabel({
            labelX:  position.x,
            labelY:  firstLineY + 5,
            text:    nameLine1,
            fill:    appear.textFill,
            fontSize: 18,
            opacity: appear.opacity,
        }));

        if (hasSecondLine) {
            nodeGroup.appendChild(buildLabel({
                labelX:   position.x,
                labelY:   position.y + 16 + 8,
                text:     nameLine2,
                fill:     appear.textFill,
                fontSize: 15,
                opacity:  appear.opacity * 0.8,
            }));
        }

        nodeGroup.appendChild(svgElement('circle', {
            cx:               position.x + NODE.width  / 2 - 7,
            cy:               position.y - NODE.height / 2 + 7,
            r:                5,
            fill:             isMale ? COLOR.genderPipMale : COLOR.genderPipFemale,
            opacity:          appear.opacity * 0.85,
            'pointer-events': 'none',
        }));

        enableNodeDragging(nodeGroup, person.id);

        group.appendChild(nodeGroup);
    });
}

// ══════════════════════════════════════════════════════════════════
// 11. NODE DRAGGING
// ══════════════════════════════════════════════════════════════════

function enableNodeDragging(nodeGroupEl, personId) {
    nodeGroupEl.addEventListener('pointerdown', startNodeDrag);

    function startNodeDrag(event) {
        if (event.button !== 0 && event.pointerType === 'mouse') return;
        // Prevent the viewport's own pointerdown from also firing and starting a canvas pan
        event.stopPropagation();

        const priorOverride    = positionOverrides[personId] ? { ...positionOverrides[personId] } : null;
        const originalPosition = positionOf(personId);
        const dragStartPointer = { x: event.clientX, y: event.clientY };
        wasDraggingNode        = false;

        function onPointerMove(moveEvent) {
            const dx = (moveEvent.clientX - dragStartPointer.x) / viewState.zoom;
            const dy = (moveEvent.clientY - dragStartPointer.y) / viewState.zoom;
            if (Math.abs(dx) > 3 || Math.abs(dy) > 3) wasDraggingNode = true;
            positionOverrides[personId] = {
                x: originalPosition.x + dx,
                y: originalPosition.y + dy,
            };

            if (wasDraggingNode) {
                const currentPos = positionOverrides[personId];
                const nearby = renderPeople.find(p => {
                    if (p.id === personId) return false;
                    const other = positionOf(p.id);
                    return Math.hypot(other.x - currentPos.x, other.y - currentPos.y) < LINK_SNAP_RADIUS;
                });
                dragHoverTargetId = nearby ? nearby.id : null;
            }

            render();
        }

        function cleanupDragListeners() {
            window.removeEventListener('pointermove',   onPointerMove);
            window.removeEventListener('pointerup',     onPointerUp);
            window.removeEventListener('pointercancel', onPointerCancel);
        }

        function onPointerCancel() {
            cleanupDragListeners();
            dragHoverTargetId = null;
            delete positionOverrides[personId];
            render();
        }

        function onPointerUp(upEvent) {
            cleanupDragListeners();
            dragHoverTargetId = null;

            if (!wasDraggingNode) return;

            const droppedPosition = positionOf(personId);
            const nearbyPerson    = renderPeople.find(p => {
                if (p.id === personId) return false;
                const other    = positionOf(p.id);
                const distance = Math.hypot(other.x - droppedPosition.x, other.y - droppedPosition.y);
                return distance < LINK_SNAP_RADIUS;
            });

            if (nearbyPerson) {
                showLinkMenu(personId, nearbyPerson.id, upEvent.clientX, upEvent.clientY, priorOverride);
            }
        }

        window.addEventListener('pointermove',   onPointerMove);
        window.addEventListener('pointerup',     onPointerUp);
        window.addEventListener('pointercancel', onPointerCancel);
    }
}

// ══════════════════════════════════════════════════════════════════
// 12. LINK MENU (drag-to-link) — with gender & relationship validation
// ══════════════════════════════════════════════════════════════════

function isAlreadyMarried(idA, idB) {
    return MARRIAGES.some(m =>
        (m.husband_id === idA && m.wife_id === idB) ||
        (m.husband_id === idB && m.wife_id === idA)
    );
}

function alreadyHasParent(childId, parentType) {
    return PC.some(pc => pc.child_id === childId && pc.type === parentType);
}

function isAncestorOf(ancestorId, personId) {
    const visited = new Set();
    const queue   = [personId];
    while (queue.length) {
        const current = queue.shift();
        if (visited.has(current)) continue;
        visited.add(current);
        for (const parentId of (parentsOf[current] || [])) {
            if (!visited.has(parentId)) {
                if (parentId === ancestorId) return true;
                queue.push(parentId);
            }
        }
    }
    return false;
}

function isInDirectAncestralLine(idA, idB) {
    return isAncestorOf(idA, idB) || isAncestorOf(idB, idA);
}

// True when both people are wives of the same man — prevents offering parent links between co-wives.
function shareHusband(idA, idB) {
    const husbandsOfA = new Set(MARRIAGES.filter(m => m.wife_id === idA).map(m => m.husband_id));
    return husbandsOfA.size > 0 && MARRIAGES.some(m => m.wife_id === idB && husbandsOfA.has(m.husband_id));
}

// True when proposedParentId shares a parent with any existing parent of proposedChildId —
// i.e. the proposed parent is a sibling of the child's father or mother, which is incest.
// Example: Sara and Omar are both children of Ibrahim → Sara cannot be Youssef's (Omar's son) mother.
function isSiblingOfParent(proposedParentId, proposedChildId) {
    const proposedParentParents = new Set(parentsOf[proposedParentId] || []);
    if (proposedParentParents.size === 0) return false;
    for (const existingParentId of (parentsOf[proposedChildId] || [])) {
        for (const grandparentId of (parentsOf[existingParentId] || [])) {
            if (proposedParentParents.has(grandparentId)) return true;
        }
    }
    return false;
}

// True when the proposed CHILD is a spouse of any ancestor of the proposed PARENT.
// This blocks descendants of a husband from being offered as parents of his wives.
// Example: Issaq→Omar (father-son), Issaq↔Aisha (marriage) → Omar CANNOT be Aisha's father.
function isSpouseOfAncestor(proposedChildId, proposedParentId) {
    const ancestorsAndSelf = new Set([proposedParentId]);
    const queue = [proposedParentId];
    while (queue.length) {
        const current = queue.shift();
        for (const parentId of (parentsOf[current] || [])) {
            if (!ancestorsAndSelf.has(parentId)) {
                ancestorsAndSelf.add(parentId);
                queue.push(parentId);
            }
        }
    }
    return MARRIAGES.some(m =>
        (m.wife_id    === proposedChildId && ancestorsAndSelf.has(m.husband_id)) ||
        (m.husband_id === proposedChildId && ancestorsAndSelf.has(m.wife_id))
    );
}

// True when A and B are within prohibited blood kinship per Islamic law (Quran 4:23 + إجماع).
// Rule: block if min(distA_to_LCA, distB_to_LCA) ≤ 1 for any common ancestor.
//   min=0 → one is a direct ancestor/descendant of the other
//   min=1 → one is a sibling of someone on the other's ancestral line
//           (covers: siblings=1+1, aunt/nephew=1+2, grand-aunt/nephew=1+3, at any depth)
//   min=2 → first cousins (2+2) → ALLOWED in Arab/Islamic tradition
// Each person is included at distance 0 so direct-line relatives are caught automatically.
function isWithinProhibitedKinship(idA, idB) {
    const distFromA = new Map([[idA, 0]]);
    const queueA    = [[idA, 0]];
    const visitedA  = new Set();
    while (queueA.length) {
        const [current, depth] = queueA.shift();
        if (visitedA.has(current)) continue;
        visitedA.add(current);
        for (const parentId of (parentsOf[current] || [])) {
            if (!distFromA.has(parentId)) {
                distFromA.set(parentId, depth + 1);
                queueA.push([parentId, depth + 1]);
            }
        }
    }
    const distFromB = new Map([[idB, 0]]);
    const queueB    = [[idB, 0]];
    const visitedB  = new Set();
    while (queueB.length) {
        const [current, depth] = queueB.shift();
        if (visitedB.has(current)) continue;
        visitedB.add(current);
        for (const parentId of (parentsOf[current] || [])) {
            if (!distFromB.has(parentId)) {
                distFromB.set(parentId, depth + 1);
                queueB.push([parentId, depth + 1]);
            }
        }
    }
    for (const [nodeId, dA] of distFromA) {
        const dB = distFromB.get(nodeId);
        if (dB !== undefined && Math.min(dA, dB) <= 1) return true;
    }
    return false;
}

// True when proposedParentId is married to any descendant of proposedChildId.
// Blocks creating a link that would make a person the ancestor of their spouse's parent.
// Example: Said married Sara, Sara is Ibrahim's daughter → Said cannot be Ibrahim's father.
function isMarriedToDescendantOf(proposedParentId, proposedChildId) {
    const descendants = new Set();
    const queue       = [proposedChildId];
    while (queue.length) {
        const current = queue.shift();
        PC.forEach(pc => {
            if (pc.parent_id === current && !descendants.has(pc.child_id)) {
                descendants.add(pc.child_id);
                queue.push(pc.child_id);
            }
        });
    }
    return MARRIAGES.some(m =>
        (m.husband_id === proposedParentId && descendants.has(m.wife_id)) ||
        (m.wife_id    === proposedParentId && descendants.has(m.husband_id))
    );
}

// True when proposedParentId already has a child who is married to proposedChildId.
// Blocks creating a sibling-marriage situation.
// Example: Ibrahim is Sara's father, Sara married Said → Ibrahim cannot be Said's father.
function isParentOfSpouseOf(proposedParentId, proposedChildId) {
    const childrenOfParent = new Set(PC.filter(pc => pc.parent_id === proposedParentId).map(pc => pc.child_id));
    if (childrenOfParent.size === 0) return false;
    return MARRIAGES.some(m =>
        (m.husband_id === proposedChildId && childrenOfParent.has(m.wife_id)) ||
        (m.wife_id    === proposedChildId && childrenOfParent.has(m.husband_id))
    );
}

// True when the proposed parent and the child's existing co-parent are within prohibited
// blood kinship — i.e. adding this parent would imply an incestuous co-parenting pair.
// Example: Yousef's father is Omar. Laila (Omar's niece) cannot be Yousef's mother
// because that would mean Omar fathered a child with his own niece.
function coParentIsProhibited(childId, proposedParentId, proposedType) {
    const coType  = proposedType === 'father' ? 'mother' : 'father';
    const coEntry = PC.find(pc => pc.child_id === childId && pc.type === coType);
    if (!coEntry) return false;
    return isWithinProhibitedKinship(proposedParentId, coEntry.parent_id);
}

function buildLinkOptions(draggedId, targetId) {
    const dragged    = personById[draggedId];
    const target     = personById[targetId];
    const draggedMale = dragged.gender === 'male';
    const targetMale  = target.gender  === 'male';
    const options = [];

    const coWives = shareHusband(draggedId, targetId);

    // Father link: potential father must be male; child must not already have a father;
    // no circular ancestry; no co-wives; proposed child cannot be a spouse of the proposed
    // father's ancestor (blocks descendants of X from fathering X's wives).
    if (draggedMale
        && !alreadyHasParent(targetId, 'father')
        && !isAncestorOf(targetId, draggedId)
        && !coWives
        && !isSpouseOfAncestor(targetId, draggedId)
        && !isSiblingOfParent(draggedId, targetId)
        && !isMarriedToDescendantOf(draggedId, targetId)
        && !isParentOfSpouseOf(draggedId, targetId)
        && !coParentIsProhibited(targetId, draggedId, 'father')
    ) {
        options.push({
            label:  `${dragged.name} أباً لـ ${target.name}`,
            action: () => callLivewireMethod('linkAsParent', [draggedId, targetId, 'father']),
        });
    }
    if (targetMale
        && !alreadyHasParent(draggedId, 'father')
        && !isAncestorOf(draggedId, targetId)
        && !coWives
        && !isSpouseOfAncestor(draggedId, targetId)
        && !isSiblingOfParent(targetId, draggedId)
        && !isMarriedToDescendantOf(targetId, draggedId)
        && !isParentOfSpouseOf(targetId, draggedId)
        && !coParentIsProhibited(draggedId, targetId, 'father')
    ) {
        options.push({
            label:  `${target.name} أباً لـ ${dragged.name}`,
            action: () => callLivewireMethod('linkAsParent', [targetId, draggedId, 'father']),
        });
    }

    // Mother link: same constraints.
    if (!draggedMale
        && !alreadyHasParent(targetId, 'mother')
        && !isAncestorOf(targetId, draggedId)
        && !coWives
        && !isSpouseOfAncestor(targetId, draggedId)
        && !isSiblingOfParent(draggedId, targetId)
        && !isMarriedToDescendantOf(draggedId, targetId)
        && !isParentOfSpouseOf(draggedId, targetId)
        && !coParentIsProhibited(targetId, draggedId, 'mother')
    ) {
        options.push({
            label:  `${dragged.name} أماً لـ ${target.name}`,
            action: () => callLivewireMethod('linkAsParent', [draggedId, targetId, 'mother']),
        });
    }
    if (!targetMale
        && !alreadyHasParent(draggedId, 'mother')
        && !isAncestorOf(draggedId, targetId)
        && !coWives
        && !isSpouseOfAncestor(draggedId, targetId)
        && !isSiblingOfParent(targetId, draggedId)
        && !isMarriedToDescendantOf(targetId, draggedId)
        && !isParentOfSpouseOf(targetId, draggedId)
        && !coParentIsProhibited(draggedId, targetId, 'mother')
    ) {
        options.push({
            label:  `${target.name} أماً لـ ${dragged.name}`,
            action: () => callLivewireMethod('linkAsParent', [targetId, draggedId, 'mother']),
        });
    }

    // Marriage: opposite genders, not already married to each other, not blood relatives within
    // 3 LCA steps (siblings=2, aunt-nephew=3 are blocked; first cousins=4 are allowed),
    // and the woman must not already be a wife (polygamy is one-sided — men only).
    const potentialWifeId = draggedMale ? targetId : draggedId;
    if (draggedMale !== targetMale
        && !isAlreadyMarried(draggedId, targetId)
        && !isWithinProhibitedKinship(draggedId, targetId)
        && !wifeIds.has(potentialWifeId)
    ) {
        const husbandId = draggedMale ? draggedId : targetId;
        const wifeId    = potentialWifeId;
        options.push({
            label:  `تزويج ${dragged.name} و${target.name}`,
            action: () => callLivewireMethod('linkAsSpouse', [husbandId, wifeId]),
        });
    }

    return options;
}

function showLinkMenu(draggedId, targetId, screenX, screenY, priorOverride = null) {
    const dragged = personById[draggedId];
    const target  = personById[targetId];
    const menu    = document.getElementById('link-menu');
    const buttons = document.getElementById('link-menu-buttons');

    linkMenuDraggedId   = draggedId;
    linkMenuSnapBackPos = priorOverride; // null = node had no prior override → delete on cancel

    document.getElementById('link-menu-title').textContent =
        `${dragged.name} ↔ ${target.name}`;

    const options = buildLinkOptions(draggedId, targetId);

    buttons.innerHTML = '';
    if (!options.length) {
        const msg = document.createElement('p');
        msg.className   = 'ft-link-no-options';
        msg.textContent = 'لا توجد روابط ممكنة بين هذين الشخصين';
        buttons.appendChild(msg);
    } else {
        options.forEach(({ label, action }) => {
            const btn = document.createElement('button');
            btn.className   = 'link-option-btn';
            btn.textContent = label;
            // Clear linkMenuDraggedId before hiding so the snap-back doesn't fire —
            // the Livewire re-render that follows the action will redraw the tree anyway.
            btn.onclick     = () => { linkMenuDraggedId = null; hideLinkMenu(); action(); };
            buttons.appendChild(btn);
        });
    }

    // Show at origin first so offsetWidth/Height reflect the actual rendered size,
    // then reposition based on those measurements.
    menu.style.left    = '0px';
    menu.style.top     = '0px';
    menu.style.display = 'block';
    document.getElementById('link-menu-backdrop').style.display = 'block';

    const viewW       = window.innerWidth;
    const viewH       = window.innerHeight;
    const actualMenuW = menu.offsetWidth;
    const actualMenuH = menu.offsetHeight;
    const left        = Math.min(Math.max(10, screenX - actualMenuW / 2), viewW - actualMenuW - 10);
    const top         = Math.min(Math.max(10, screenY - 20),              viewH - actualMenuH - 10);
    menu.style.left   = left + 'px';
    menu.style.top    = top  + 'px';
}

window.hideLinkMenu = function () {
    document.getElementById('link-menu').style.display          = 'none';
    document.getElementById('link-menu-backdrop').style.display = 'none';
    if (linkMenuDraggedId !== null) {
        if (linkMenuSnapBackPos !== null) {
            positionOverrides[linkMenuDraggedId] = linkMenuSnapBackPos; // restore last dragged-to position
        } else {
            delete positionOverrides[linkMenuDraggedId]; // node had never been moved — go back to layout
        }
        linkMenuDraggedId   = null;
        linkMenuSnapBackPos = null;
        render();
    }
};

function callLivewireMethod(methodName, args) {
    const wireId   = document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
    const component = window.Livewire?.find(wireId);
    if (component) {
        component.call(methodName, ...args);
    }
}

// ══════════════════════════════════════════════════════════════════
// 13. NODE SELECTION & KINSHIP LOOKUP
// ══════════════════════════════════════════════════════════════════

function onNodeClick(personId) {
    if (!selectedPersonA) {
        selectedPersonA = personId;
        setChip('a', personId);
        setPanelHint('اختر شخصاً ثانياً');
        render();
        return;
    }

    if (!selectedPersonB && personId !== selectedPersonA) {
        selectedPersonB = personId;
        setChip('b', personId);
        setPanelHint('');
        render();
        loadKinship(selectedPersonA, selectedPersonB);
        return;
    }

    clearSelection();
}

function clearSelection() {
    selectedPersonA = null;
    selectedPersonB = null;
    resetChip('a');
    resetChip('b');
    setPanelHint('انقر على أي شخص');
    document.getElementById('kinship-panel').style.display = 'none';
    render();
}

async function loadKinship(aId, bId) {
    const panel = document.getElementById('kinship-panel');
    panel.style.display = 'block';
    document.getElementById('kinship-loading').style.display = 'block';
    document.getElementById('kinship-result').style.display  = 'none';
    document.getElementById('kinship-no-relation').style.display = 'none';

    try {
        const response = await fetch(
            `/kinship/calculate?a=${aId}&b=${bId}`,
            { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
        );
        const data = await response.json();
        displayKinshipResult(personById[aId].name, personById[bId].name, data);
    } catch {
        displayKinshipResult(personById[aId].name, personById[bId].name, { found: false, labels: [] });
    }
}

function displayKinshipResult(nameA, nameB, data) {
    document.getElementById('kinship-loading').style.display = 'none';

    if (!data.found) {
        document.getElementById('kinship-no-relation').style.display = 'block';
        return;
    }

    const labels = data.labels || [data.label];

    document.getElementById('kinship-label-primary').textContent = labels[0] ?? '—';
    document.getElementById('kinship-desc-primary').innerHTML =
        `<strong class="ft-kinship-name">${nameB}</strong> بالنسبة لـ<strong class="ft-kinship-name">${nameA}</strong>`;

    const secondaryEl = document.getElementById('kinship-secondary');
    if (labels.length > 1) {
        document.getElementById('kinship-label-secondary').textContent = labels[1];
        document.getElementById('kinship-desc-secondary').innerHTML =
            `<strong class="ft-kinship-name">${nameA}</strong> بالنسبة لـ<strong class="ft-kinship-name">${nameB}</strong>`;
        secondaryEl.style.display = 'block';
    } else {
        secondaryEl.style.display = 'none';
    }

    document.getElementById('kinship-result').style.display = 'block';
}

// ══════════════════════════════════════════════════════════════════
// 14. PANEL CHIP HELPERS
// ══════════════════════════════════════════════════════════════════

function setChip(side, personId) {
    const person    = personById[personId];
    const nameEl    = document.getElementById(`chip-${side}-name`);
    const clearBtn  = document.getElementById(`chip-${side}-clear`);
    const chipEl    = document.getElementById(`chip-${side}`);
    nameEl.textContent  = person.name;
    nameEl.style.color  = side === 'a' ? '#4ABA80' : '#E07040';
    clearBtn.style.display  = 'block';
    chipEl.style.borderColor = side === 'a' ? 'rgba(74,186,128,.3)' : 'rgba(224,112,64,.3)';
}

function resetChip(side) {
    const nameEl   = document.getElementById(`chip-${side}-name`);
    const clearBtn = document.getElementById(`chip-${side}-clear`);
    const chipEl   = document.getElementById(`chip-${side}`);
    nameEl.textContent  = side === 'a' ? 'انقر شخصاً في الشجرة' : 'اختر شخصاً ثانياً';
    nameEl.style.color  = '#6B829E';
    clearBtn.style.display  = 'none';
    chipEl.style.borderColor = 'rgba(255,255,255,.06)';
}

function setPanelHint(message) {
    document.getElementById('panel-hint').textContent = message;
}

window.clearSelectedA = function () {
    if (selectedPersonB) {
        selectedPersonA = selectedPersonB;
        selectedPersonB = null;
        setChip('a', selectedPersonA);
        resetChip('b');
        document.getElementById('kinship-panel').style.display = 'none';
        setPanelHint('اختر شخصاً ثانياً');
    } else {
        clearSelection();
    }
    render();
};

window.clearSelectedB = function () {
    selectedPersonB = null;
    resetChip('b');
    document.getElementById('kinship-panel').style.display = 'none';
    setPanelHint('اختر شخصاً ثانياً');
    render();
};

// ══════════════════════════════════════════════════════════════════
// 15. PAN & ZOOM
// ══════════════════════════════════════════════════════════════════

const viewState    = { panX: 0, panY: 0, zoom: 1 };
const viewport     = document.getElementById('tree-viewport');
let   isPanning    = false;
let   panStartDrag = { x: 0, y: 0 };

function applyViewTransform() {
    treeGroup.setAttribute(
        'transform',
        `translate(${viewState.panX}, ${viewState.panY}) scale(${viewState.zoom})`
    );
}

function fitTreeToViewport() {
    const rect            = viewport.getBoundingClientRect();
    const allPos          = Object.values(basePositions);
    const canvasWidth     = allPos.length ? Math.max(...allPos.map(p => p.x)) + NODE.width  / 2 + GAP.margin : rect.width;
    const canvasHeight    = allPos.length ? Math.max(...allPos.map(p => p.y)) + NODE.height / 2 + 80        : rect.height;
    const scaleX          = rect.width  / canvasWidth;
    const scaleY          = rect.height / canvasHeight;
    viewState.zoom = Math.min(scaleX, scaleY, 1) * FIT_PADDING;
    viewState.panX = (rect.width  - canvasWidth  * viewState.zoom) / 2;
    viewState.panY = (rect.height - canvasHeight * viewState.zoom) / 2;
    applyViewTransform();
}

function zoomAroundCenter(factor) {
    const rect       = viewport.getBoundingClientRect();
    const vpCenterX  = rect.width / 2;
    const vpCenterY  = rect.height / 2;
    viewState.panX  = vpCenterX + (viewState.panX - vpCenterX) * factor;
    viewState.panY  = vpCenterY + (viewState.panY - vpCenterY) * factor;
    viewState.zoom *= factor;
    applyViewTransform();
}

// Pan via pointer events — stopPropagation() on pointerdown from nodes correctly
// prevents this handler from firing when the user drags a node rather than the canvas.
// Also skip if the event originated inside a button (zoom controls, panel-toggle, etc.)
// so those buttons don't accidentally start a pan gesture.
viewport.addEventListener('pointerdown', event => {
    if (event.button !== 0 && event.pointerType === 'mouse') return;
    if (event.target.closest('button')) return;
    isPanning = true;
    // Capture keeps pointermove firing on the viewport even if the pointer leaves its bounds mid-drag
    viewport.setPointerCapture(event.pointerId);
    viewport.style.cursor = 'grabbing';
    panStartDrag = { x: event.clientX - viewState.panX, y: event.clientY - viewState.panY };
});
viewport.addEventListener('pointermove', event => {
    if (!isPanning) return;
    viewState.panX = event.clientX - panStartDrag.x;
    viewState.panY = event.clientY - panStartDrag.y;
    applyViewTransform();
});
viewport.addEventListener('pointerup', () => {
    isPanning = false;
    viewport.style.cursor = 'grab';
});
viewport.addEventListener('pointercancel', () => {
    isPanning = false;
    viewport.style.cursor = 'grab';
});

document.getElementById('btn-zoom-in')     .addEventListener('click', () => zoomAroundCenter(1.2));
document.getElementById('btn-zoom-out')    .addEventListener('click', () => zoomAroundCenter(1 / 1.2));
document.getElementById('btn-zoom-fit')    .addEventListener('click', fitTreeToViewport);
document.getElementById('btn-patrilineal') .addEventListener('click', togglePatrilinealMode);

// ══════════════════════════════════════════════════════════════════
// 16. UNLINKED PEOPLE PANEL
// ══════════════════════════════════════════════════════════════════

(function populateUnlinkedPeople() {
    const connectedIds = new Set();
    PC.forEach(({ parent_id, child_id }) => { connectedIds.add(parent_id); connectedIds.add(child_id); });
    MARRIAGES.forEach(({ husband_id, wife_id }) => { connectedIds.add(husband_id); connectedIds.add(wife_id); });

    const unlinked = PEOPLE.filter(p => !connectedIds.has(p.id));
    const listEl   = document.getElementById('unlinked-list');
    const emptyEl  = document.getElementById('unlinked-empty');
    const countEl  = document.getElementById('unlinked-count');

    if (!unlinked.length) {
        emptyEl.style.display = 'block';
        return;
    }

    countEl.textContent = `(${unlinked.length})`;
    unlinked.forEach(person => {
        const isMale = person.gender === 'male';
        const row    = document.createElement('div');
        row.className = 'ft-unlinked-row';

        const colorDot = document.createElement('span');
        colorDot.className = `ft-unlinked-dot ${isMale ? 'ft-unlinked-dot--male' : 'ft-unlinked-dot--female'}`;

        const nameSpan = document.createElement('span');
        nameSpan.className   = 'ft-unlinked-name';
        nameSpan.textContent = person.name;

        const linkEl = document.createElement('a');
        linkEl.href        = `/people/${person.id}/parents`;
        linkEl.className   = 'ft-unlinked-link';
        linkEl.title       = 'ربط الوالدين';
        linkEl.textContent = 'ربط ↗';

        row.appendChild(colorDot);
        row.appendChild(nameSpan);
        row.appendChild(linkEl);
        row.addEventListener('click', event => {
            if (event.target.tagName === 'A') return;
            onNodeClick(person.id);
        });
        listEl.appendChild(row);
    });
}());

// ══════════════════════════════════════════════════════════════════
// 17. MOBILE PANEL TOGGLE
// ══════════════════════════════════════════════════════════════════

document.getElementById('panel-toggle').addEventListener('click', () => {
    document.getElementById('side-panel').classList.add('open');
    document.getElementById('panel-backdrop').classList.add('open');
});

window.closeMobilePanel = function () {
    document.getElementById('side-panel').classList.remove('open');
    document.getElementById('panel-backdrop').classList.remove('open');
};

// ══════════════════════════════════════════════════════════════════
// 18. INITIALISE
// ══════════════════════════════════════════════════════════════════

render();
requestAnimationFrame(fitTreeToViewport);

}());
</script>
