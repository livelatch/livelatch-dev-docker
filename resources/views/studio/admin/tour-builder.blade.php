<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tour Builder — driver.js</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.css"/>
<style>
    :root{
        --bg:#0e1020; --panel:#171a2e; --panel-2:#1f2238; --line:rgba(255,255,255,.10);
        --text:#eef1ff; --muted:#9aa3c7; --primary:#0092ec; --primary-2:#0ce5de; --danger:#ef4444; --ok:#22c55e;
        --radius:14px; --font:'Segoe UI',system-ui,-apple-system,sans-serif;
    }
    *{box-sizing:border-box}
    html,body{margin:0;height:100%;background:var(--bg);color:var(--text);font-family:var(--font)}
    body{display:flex;flex-direction:column;overflow:hidden}

    .tb-top{display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid var(--line);background:var(--panel);flex-wrap:wrap}
    .tb-top h1{font-size:1rem;margin:0 12px 0 0;font-weight:700}
    .tb-top .grow{flex:1}
    .tb-input{height:38px;padding:0 12px;border-radius:10px;border:1px solid var(--line);background:var(--panel-2);color:var(--text);font-size:.9rem;min-width:220px}
    .tb-btn{height:38px;padding:0 14px;border-radius:10px;border:1px solid var(--line);background:var(--panel-2);color:var(--text);font-weight:600;font-size:.86rem;cursor:pointer;display:inline-flex;align-items:center;gap:7px;white-space:nowrap}
    .tb-btn:hover{border-color:rgba(255,255,255,.3)}
    .tb-btn.primary{border-color:transparent;background:linear-gradient(135deg,var(--primary),var(--primary-2));color:#04122b}
    .tb-btn.ok{border-color:transparent;background:var(--ok);color:#062b13}
    .tb-btn.danger{border-color:transparent;background:var(--danger);color:#fff}
    .tb-btn.active{outline:2px solid var(--primary-2);background:rgba(12,229,222,.16)}
    .tb-btn[disabled]{opacity:.45;cursor:not-allowed}

    .tb-body{flex:1;display:grid;grid-template-columns:380px 1fr;min-height:0}
    .tb-steps{border-right:1px solid var(--line);background:var(--panel);overflow-y:auto;padding:14px;display:flex;flex-direction:column;gap:12px}
    .tb-stage{position:relative;background:#fff;overflow:hidden}
    .tb-stage iframe{width:100%;height:100%;border:0;display:block}
    .tb-pickhint{position:absolute;inset:auto 0 0 0;padding:8px 12px;background:rgba(0,146,236,.92);color:#fff;font-size:.82rem;text-align:center;font-weight:600}

    .tb-step{border:1px solid var(--line);border-radius:var(--radius);background:var(--panel-2);padding:12px;display:flex;flex-direction:column;gap:8px}
    .tb-step-head{display:flex;align-items:center;gap:8px}
    .tb-badge{width:24px;height:24px;border-radius:7px;background:linear-gradient(135deg,var(--primary),var(--primary-2));color:#04122b;font-weight:800;font-size:.78rem;display:grid;place-items:center;flex:0 0 auto}
    .tb-step-head .sel{flex:1;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.74rem;color:var(--primary-2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .tb-mini{width:28px;height:28px;border-radius:8px;border:1px solid var(--line);background:transparent;color:var(--muted);cursor:pointer;font-size:.85rem;line-height:1}
    .tb-mini:hover{color:var(--text);border-color:rgba(255,255,255,.3)}
    .tb-field label{display:block;font-size:.68rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin:0 0 3px;font-weight:700}
    .tb-field input,.tb-field textarea,.tb-field select{width:100%;padding:7px 9px;border-radius:9px;border:1px solid var(--line);background:var(--panel);color:var(--text);font-size:.84rem;font-family:var(--font)}
    .tb-field textarea{resize:vertical;min-height:48px}
    .tb-row{display:grid;grid-template-columns:1fr 1fr;gap:8px}
    .tb-empty{color:var(--muted);font-size:.86rem;text-align:center;padding:30px 10px;line-height:1.5}

    .tb-modal{position:fixed;inset:0;background:rgba(4,6,18,.7);display:none;align-items:center;justify-content:center;z-index:50;padding:20px}
    .tb-modal.show{display:flex}
    .tb-modal-card{width:min(760px,100%);max-height:88vh;background:var(--panel);border:1px solid var(--line);border-radius:16px;display:flex;flex-direction:column;overflow:hidden}
    .tb-modal-head{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--line)}
    .tb-modal-head h2{margin:0;font-size:.95rem}
    .tb-code{flex:1;overflow:auto;margin:0;padding:16px;background:#0b0d1a;color:#d6e0ff;font-family:ui-monospace,Menlo,Consolas,monospace;font-size:.8rem;line-height:1.55;white-space:pre}
    .tb-tabs{display:flex;gap:6px}
    .tb-tab{padding:6px 12px;border-radius:9px;border:1px solid var(--line);background:var(--panel-2);color:var(--muted);cursor:pointer;font-size:.8rem;font-weight:600}
    .tb-tab.active{color:var(--text);border-color:var(--primary)}
    .tb-toast{position:fixed;bottom:18px;left:50%;transform:translateX(-50%);background:var(--ok);color:#062b13;padding:9px 16px;border-radius:10px;font-weight:700;font-size:.85rem;opacity:0;pointer-events:none;transition:opacity .2s;z-index:60}
    .tb-toast.show{opacity:1}
</style>
</head>
<body>
    <div class="tb-top">
        <h1>🧭 Tour Builder</h1>
        <input id="tb-url" class="tb-input" value="/dashboard" title="Page to load & build the tour against">
        <button id="tb-load" class="tb-btn">↻ Load</button>
        <button id="tb-pick" class="tb-btn">🎯 Pick element</button>
        <button id="tb-add" class="tb-btn">＋ Blank step</button>
        <div class="grow"></div>
        <button id="tb-preview" class="tb-btn primary">▶ Preview tour</button>
        <button id="tb-export" class="tb-btn ok">⤓ Export code</button>
    </div>

    <div class="tb-body">
        <div id="tb-steps" class="tb-steps"></div>
        <div class="tb-stage">
            <iframe id="tb-frame" src="/dashboard"></iframe>
            <div id="tb-pickhint" class="tb-pickhint" style="display:none">Pick mode ON — click any element in the page to capture it as a step. Press Esc to stop.</div>
        </div>
    </div>

    <div id="tb-modal" class="tb-modal">
        <div class="tb-modal-card">
            <div class="tb-modal-head">
                <h2>Generated code</h2>
                <div class="tb-tabs">
                    <button class="tb-tab active" data-tab="claude">💬 Claude prompt</button>
                    <button class="tb-tab" data-tab="script">Script tag (no build)</button>
                    <button class="tb-tab" data-tab="module">ES module</button>
                    <button class="tb-tab" data-tab="json">Steps JSON</button>
                </div>
                <div class="grow" style="flex:1"></div>
                <button id="tb-copy" class="tb-btn primary">Copy</button>
                <button id="tb-close" class="tb-btn">Close</button>
            </div>
            <pre id="tb-code" class="tb-code"></pre>
        </div>
    </div>

    <div id="tb-toast" class="tb-toast"></div>

<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.js.iife.js"></script>
<script>
(function(){
    "use strict";
    const $ = (id) => document.getElementById(id);
    const frame = $("tb-frame");
    let steps = [];          // {selector,title,description,side,align}
    let picking = false;

    /* ---------- selector generation (runs against the iframe document) ---------- */
    function cssEscape(s){ return (window.CSS && CSS.escape) ? CSS.escape(s) : s.replace(/([^\w-])/g,'\\$1'); }
    function isUnique(doc, sel){ try { return doc.querySelectorAll(sel).length === 1; } catch(e){ return false; } }

    function buildSelector(doc, el){
        if(!el || el.nodeType !== 1) return "";
        // 1. Author-provided hook wins.
        if(el.getAttribute && el.getAttribute('data-tour')) return '[data-tour="'+el.getAttribute('data-tour')+'"]';
        // 2. A stable id.
        if(el.id && isUnique(doc, '#'+cssEscape(el.id))) return '#'+cssEscape(el.id);
        // 3. A single class that is unique on the page.
        const cls = (el.className && typeof el.className === 'string')
            ? el.className.trim().split(/\s+/).filter(c => c && !/^(active|is-|htmx-|show|open)/.test(c))
            : [];
        for(const c of cls){
            const sel = el.tagName.toLowerCase()+'.'+cssEscape(c);
            if(isUnique(doc, sel)) return sel;
        }
        // 4. Fall back to an nth-of-type path up to the nearest id/body.
        const parts = [];
        let node = el;
        while(node && node.nodeType === 1 && node !== doc.body){
            let part = node.tagName.toLowerCase();
            if(node.id){ parts.unshift('#'+cssEscape(node.id)); break; }
            const parent = node.parentNode;
            if(parent){
                const sibs = Array.from(parent.children).filter(s => s.tagName === node.tagName);
                if(sibs.length > 1) part += ':nth-of-type('+(sibs.indexOf(node)+1)+')';
            }
            parts.unshift(part);
            node = node.parentNode;
        }
        return parts.join(' > ');
    }

    function frameDoc(){
        try { return frame.contentDocument || frame.contentWindow.document; }
        catch(e){ return null; }
    }

    /* ---------- pick mode (injected highlight + click capture) ---------- */
    let hoverEl = null;
    function setHover(el){
        if(hoverEl) hoverEl.style.outline = hoverEl.__tbPrev || '';
        hoverEl = el;
        if(el){ el.__tbPrev = el.style.outline; el.style.outline = '2px solid #0ce5de'; }
    }
    function onFrameMove(e){ if(picking) setHover(e.target); }
    function onFrameClick(e){
        if(!picking) return;
        e.preventDefault(); e.stopPropagation();
        const doc = frameDoc();
        const sel = buildSelector(doc, e.target);
        const text = (e.target.innerText||'').trim().replace(/\s+/g,' ').slice(0,60);
        addStep({ selector: sel, title: text ? ('"'+text+'"') : 'New step', description: '' });
        toast('Captured: '+sel);
    }
    function onFrameKey(e){ if(e.key === 'Escape') setPicking(false); }

    function bindFrame(){
        const doc = frameDoc();
        if(!doc) return;
        doc.addEventListener('mousemove', onFrameMove, true);
        doc.addEventListener('click', onFrameClick, true);
        doc.addEventListener('keydown', onFrameKey, true);
    }
    function setPicking(on){
        picking = on;
        $("tb-pick").classList.toggle('active', on);
        $("tb-pickhint").style.display = on ? 'block' : 'none';
        const doc = frameDoc();
        if(doc) doc.body.style.cursor = on ? 'crosshair' : '';
        if(!on) setHover(null);
    }

    /* ---------- steps model + render ---------- */
    function addStep(s){
        steps.push(Object.assign({ selector:'', title:'New step', description:'', side:'bottom', align:'start' }, s));
        render(); persist();
    }
    function render(){
        const wrap = $("tb-steps");
        wrap.innerHTML = '';
        if(!steps.length){
            wrap.innerHTML = '<div class="tb-empty">No steps yet.<br>Hit <b>🎯 Pick element</b> then click things in the page on the right, or add a blank step.</div>';
            return;
        }
        steps.forEach((s, i) => {
            const el = document.createElement('div');
            el.className = 'tb-step';
            el.innerHTML = `
                <div class="tb-step-head">
                    <span class="tb-badge">${i+1}</span>
                    <span class="sel" title="${escapeHtml(s.selector)}">${escapeHtml(s.selector || '(no selector)')}</span>
                    <button class="tb-mini" data-act="hl"   title="Flash in page">✦</button>
                    <button class="tb-mini" data-act="up"   title="Move up">▲</button>
                    <button class="tb-mini" data-act="down" title="Move down">▼</button>
                    <button class="tb-mini" data-act="del"  title="Delete">✕</button>
                </div>
                <div class="tb-field"><label>Element selector</label><input data-f="selector" value="${escapeAttr(s.selector)}"></div>
                <div class="tb-field"><label>Popover title</label><input data-f="title" value="${escapeAttr(s.title)}"></div>
                <div class="tb-field"><label>Description</label><textarea data-f="description">${escapeHtml(s.description)}</textarea></div>
                <div class="tb-row">
                    <div class="tb-field"><label>Side</label><select data-f="side">${opts(['top','right','bottom','left','over'], s.side)}</select></div>
                    <div class="tb-field"><label>Align</label><select data-f="align">${opts(['start','center','end'], s.align)}</select></div>
                </div>`;
            el.querySelectorAll('[data-f]').forEach(inp => {
                inp.addEventListener('input', () => { s[inp.dataset.f] = inp.value; persist(); if(inp.dataset.f==='selector'){ el.querySelector('.sel').textContent = inp.value || '(no selector)'; } });
            });
            el.querySelectorAll('[data-act]').forEach(b => b.addEventListener('click', () => stepAction(b.dataset.act, i)));
            wrap.appendChild(el);
        });
    }
    function stepAction(act, i){
        if(act==='del'){ steps.splice(i,1); }
        else if(act==='up' && i>0){ [steps[i-1],steps[i]]=[steps[i],steps[i-1]]; }
        else if(act==='down' && i<steps.length-1){ [steps[i+1],steps[i]]=[steps[i],steps[i+1]]; }
        else if(act==='hl'){ flash(steps[i].selector); return; }
        render(); persist();
    }
    function flash(sel){
        const doc = frameDoc(); if(!doc || !sel) return;
        let el; try { el = doc.querySelector(sel); } catch(e){}
        if(!el){ toast('Not found: '+sel); return; }
        el.scrollIntoView({behavior:'smooth', block:'center'});
        const prev = el.style.outline, prevT = el.style.transition;
        el.style.transition='outline .15s'; el.style.outline='3px solid #ef4444';
        setTimeout(()=>{ el.style.outline=prev; el.style.transition=prevT; }, 900);
    }

    /* ---------- live preview (driver.js injected into the iframe) ---------- */
    function ensureDriverInFrame(cb){
        const win = frame.contentWindow, doc = frameDoc();
        if(!win || !doc) return;
        if(win.driver && win.driver.js){ cb(win.driver.js.driver); return; }
        if(!doc.querySelector('link[data-tb-driver]')){
            const link = doc.createElement('link');
            link.rel='stylesheet'; link.href='https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.css';
            link.setAttribute('data-tb-driver','1'); doc.head.appendChild(link);
        }
        const sc = doc.createElement('script');
        sc.src='https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.js.iife.js';
        sc.onload = () => cb(win.driver.js.driver);
        doc.body.appendChild(sc);
    }
    function preview(){
        if(!steps.length){ toast('Add a step first'); return; }
        setPicking(false);
        ensureDriverInFrame((driver) => {
            const d = driver({ showProgress:true, allowClose:true, steps: toDriverSteps() });
            d.drive();
        });
    }

    /* ---------- export ---------- */
    function toDriverSteps(){
        return steps.filter(s=>s.selector).map(s => ({
            element: s.selector,
            popover: Object.assign(
                { title: s.title||'', description: s.description||'' },
                s.side ? { side: s.side } : {},
                s.align ? { align: s.align } : {}
            )
        }));
    }
    function stepsLiteral(indent){
        const pad = ' '.repeat(indent);
        return toDriverSteps().map(s => {
            const p = s.popover;
            const parts = [`element: ${js(s.element)}`,
                `popover: { title: ${js(p.title)}, description: ${js(p.description)}`
                + (p.side?`, side: ${js(p.side)}`:'') + (p.align?`, align: ${js(p.align)}`:'') + ` }`];
            return `${pad}{ ${parts.join(', ')} }`;
        }).join(',\n');
    }
    function exportScript(){
        return `<!-- 1. Include once (e.g. in layouts/sidebar.blade.php <head>) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.6/dist/driver.js.iife.js"><\/script>

<!-- 2. Start the tour for first-time users -->
<script>
(function(){
  const KEY = 'll_studio_tour_done';
  if (localStorage.getItem(KEY)) return;            // already seen it
  const driver = window.driver.js.driver;
  const tour = driver({
    showProgress: true,
    allowClose: true,
    onDestroyed: () => localStorage.setItem(KEY, '1'),
    steps: [
${stepsLiteral(6)}
    ],
  });
  // Wait for the page to settle before starting.
  window.addEventListener('load', () => setTimeout(() => tour.drive(), 600));
})();
<\/script>`;
    }
    function exportModule(){
        return `import { driver } from "driver.js";
import "driver.js/dist/driver.css";

const TOUR_KEY = "ll_studio_tour_done";

export function startStudioTour(force = false) {
  if (!force && localStorage.getItem(TOUR_KEY)) return;
  const tour = driver({
    showProgress: true,
    allowClose: true,
    onDestroyed: () => localStorage.setItem(TOUR_KEY, "1"),
    steps: [
${stepsLiteral(6)}
    ],
  });
  tour.drive();
}`;
    }
    function exportJson(){ return JSON.stringify(toDriverSteps(), null, 2); }

    function exportClaude(){
        const url = $("tb-url").value || '/dashboard';
        return `Update the Livelatch studio onboarding tour.

In resources/views/partials/studio-tour.blade.php, replace the "var steps = [];"
array with EXACTLY the steps below (keep everything else in that file as-is —
the driver.js includes, the localStorage gate, and the window-load trigger):

var steps = ${JSON.stringify(toDriverSteps(), null, 2)};

These steps were built against the page "${url}". Don't invent or reorder
selectors, and leave the existing data-tour attributes in the markup intact.
After editing, summarise what changed.`;
    }

    const exporters = { claude: exportClaude, script: exportScript, module: exportModule, json: exportJson };
    let curTab = 'claude';
    function showExport(){
        if(!toDriverSteps().length){ toast('Add at least one step with a selector'); return; }
        renderTab(); $("tb-modal").classList.add('show');
    }
    function renderTab(){ $("tb-code").textContent = exporters[curTab](); }

    /* ---------- persistence (localStorage so you don't lose work on reload) ---------- */
    const STORE='ll_tour_builder_steps';
    function persist(){ try{ localStorage.setItem(STORE, JSON.stringify(steps)); }catch(e){} }
    function restore(){ try{ const r=localStorage.getItem(STORE); if(r) steps=JSON.parse(r)||[]; }catch(e){} }

    /* ---------- helpers ---------- */
    function js(v){ return JSON.stringify(v == null ? '' : v); }
    function opts(list, cur){ return list.map(o=>`<option value="${o}"${o===cur?' selected':''}>${o}</option>`).join(''); }
    function escapeHtml(s){ return (s==null?'':String(s)).replace(/[&<>]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;'}[c])); }
    function escapeAttr(s){ return escapeHtml(s).replace(/"/g,'&quot;'); }
    let toastT;
    function toast(msg){ const t=$("tb-toast"); t.textContent=msg; t.classList.add('show'); clearTimeout(toastT); toastT=setTimeout(()=>t.classList.remove('show'),1800); }

    /* ---------- wire up ---------- */
    $("tb-load").addEventListener('click', () => { setPicking(false); frame.src = $("tb-url").value || '/dashboard'; });
    $("tb-url").addEventListener('keydown', e => { if(e.key==='Enter') $("tb-load").click(); });
    $("tb-pick").addEventListener('click', () => setPicking(!picking));
    $("tb-add").addEventListener('click', () => addStep({ selector:'', title:'New step' }));
    $("tb-preview").addEventListener('click', preview);
    $("tb-export").addEventListener('click', showExport);
    $("tb-close").addEventListener('click', () => $("tb-modal").classList.remove('show'));
    $("tb-copy").addEventListener('click', () => {
        navigator.clipboard.writeText($("tb-code").textContent).then(()=>toast('Copied to clipboard'));
    });
    document.querySelectorAll('.tb-tab').forEach(t => t.addEventListener('click', () => {
        document.querySelectorAll('.tb-tab').forEach(x=>x.classList.remove('active'));
        t.classList.add('active'); curTab = t.dataset.tab; renderTab();
    }));
    document.addEventListener('keydown', e => { if(e.key==='Escape') setPicking(false); });
    frame.addEventListener('load', () => { bindFrame(); if(picking) setPicking(true); });

    restore(); render();
})();
</script>
</body>
</html>
