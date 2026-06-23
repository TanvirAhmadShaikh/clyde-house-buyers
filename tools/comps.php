<?php
/**
 * comps.php — Property Comparable Sales Finder (internal tool).
 *
 * This version includes:
 * 1. PERSISTENT RESEARCH DETAILS: Log, timer, and credits move to an expandable section after completion.
 * 2. REAL-TIME CREDIT TRACKING: Shows tokens consumed during research.
 * 3. AUTO-SCROLLING LOG: Shows only the latest messages with scroll support.
 * 4. CLICKABLE TASK ID: Link to debug view.
 *
 * --- VERSION ---
 * Bump TOOL_VERSION on every change so the deployed version is visible in the UI.
 * The build date auto-stamps from this file's last-modified time on the server.
 */
define('TOOL_VERSION', 'v4.1');
require __DIR__ . '/_auth.php';
$build_date = date('d M Y H:i', @filemtime(__FILE__) ?: time());
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Comparable Sales Finder — Internal Tool</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --ch-navy: #0B1F3B; --ch-navy-80: #2c3f59; --ch-navy-60: #4d5f78;
    --ch-gold: #C8A24A; --ch-gold-dark: #a8862f; --ch-gold-light: #d6b56b; --ch-gold-tint: #FBF4E1;
    --ch-offwhite: #F7F9FC; --ch-slate: #A7B0BD; --ch-slate-light: #cdd4dc; --ch-slate-lighter: #e3e7ed;
    --ch-charcoal: #2B2F36; --ch-white: #ffffff; --ch-success: #2FBF71; --ch-coral: #FF6B5A;
    --ch-font-display: 'Fraunces', Georgia, serif; --ch-font-body: 'Inter', sans-serif;
    --ch-radius: 8px; --ch-radius-lg: 14px; --ch-shadow-sm: 0 1px 2px rgba(11, 31, 59, 0.06);
  }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: var(--ch-font-body); background: var(--ch-offwhite); color: var(--ch-charcoal); line-height: 1.6; padding: 2rem 1.25rem; }
  .wrap { max-width: 1100px; margin: 0 auto; }
  header.tool-head { margin-bottom: 2rem; }
  .internal-flag { display: inline-block; background: var(--ch-navy); color: var(--ch-gold-light); font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.08em; padding: 0.2rem 0.6rem; border-radius: 100px; font-weight: 600; margin-bottom: 0.5rem; }
  .version-badge { display: inline-block; background: var(--ch-gold-tint); color: var(--ch-gold-dark); font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; margin-left: 0.5rem; border: 1px solid var(--ch-gold); }
  h1 { font-family: var(--ch-font-display); font-size: 2rem; color: var(--ch-navy); margin: 0.25rem 0; font-weight: 600; }
  .card { background: var(--ch-white); border: 1px solid var(--ch-slate-lighter); border-radius: var(--ch-radius-lg); box-shadow: var(--ch-shadow-sm); padding: 1.5rem; margin-bottom: 2rem; }
  form { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem; align-items: end; }
  @media (max-width: 700px) { form { grid-template-columns: 1fr; } }
  label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--ch-navy); margin-bottom: 0.35rem; }
  input, select { width: 100%; padding: 0.7rem 0.85rem; border: 1px solid var(--ch-slate-light); border-radius: var(--ch-radius); font-size: 0.95rem; }
  .btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 0.85rem 1.5rem; background: var(--ch-gold); color: var(--ch-navy); border: none; border-radius: var(--ch-radius); font-weight: 700; cursor: pointer; }
  .status { text-align: center; padding: 2rem 1rem; }
  .spinner { width: 44px; height: 44px; border: 3px solid var(--ch-slate-lighter); border-top-color: var(--ch-gold); border-radius: 50%; margin: 0 auto 1rem; animation: spin 0.8s linear infinite; }
  @keyframes spin { to { transform: rotate(360deg); } }
  .timer-box { font-size: 0.9rem; color: var(--ch-navy-60); margin-bottom: 1rem; }
  .timer-box b { color: var(--ch-navy); }
  .progress-log { max-width: 600px; margin: 1rem auto; background: #f0f4f8; border: 1px solid var(--ch-slate-lighter); border-radius: var(--ch-radius); padding: 0.5rem; font-size: 0.8rem; text-align: left; height: 5.5rem; overflow-y: auto; display: block; scroll-behavior: smooth; }
  .log-entry { border-bottom: 1px solid #e1e8f0; padding: 0.4rem 0.25rem; line-height: 1.4; word-wrap: break-word; white-space: normal; display: block; clear: both; }
  .log-entry:last-child { border-bottom: none; font-weight: 500; color: var(--ch-navy); }
  .table-card { background: var(--ch-white); border: 1px solid var(--ch-slate-lighter); border-radius: var(--ch-radius-lg); overflow: hidden; box-shadow: var(--ch-shadow-sm); margin-bottom: 2rem; }
  table { width: 100%; border-collapse: collapse; }
  thead { background: var(--ch-navy); color: white; }
  th, td { text-align: left; padding: 0.8rem 1rem; font-size: 0.9rem; border-bottom: 1px solid var(--ch-slate-lighter); }
  .price { font-weight: 700; color: var(--ch-gold-dark); }
  .error { background: #FFF0EE; border-left: 4px solid var(--ch-coral); padding: 1rem; border-radius: var(--ch-radius); color: #8a2c22; margin-bottom: 2rem; }
  
  /* Expandable Details */
  details.research-details { background: #f8fafc; border: 1px solid var(--ch-slate-lighter); border-radius: var(--ch-radius); padding: 0.5rem 1rem; margin-top: 1rem; }
  details.research-details summary { cursor: pointer; font-size: 0.85rem; font-weight: 600; color: var(--ch-navy-60); padding: 0.5rem 0; }
  details.research-details summary:hover { color: var(--ch-navy); }
</style>
</head>
<body>
<div class="wrap">
  <header class="tool-head">
    <span class="internal-flag">Internal tool</span><span class="version-badge"><?= TOOL_VERSION ?> · <?= $build_date ?></span>
    <h1>Comparable Sales Finder</h1>
    <p>Find recent comparable sales via AI agent. · <a href="?logout=1" style="color:var(--ch-navy-60);">Sign out</a></p>
  </header>

  <div class="card">
    <form id="comps-form">
      <div><label>Address / Postcode</label><input type="text" id="address" required placeholder="e.g. 111 Lochend Rd, G69 8AH"></div>
      <div><label>Bedrooms</label><select id="beds"><option value="1">1 Bed</option><option value="2">2 Bed</option><option value="3" selected>3 Bed</option><option value="4">4 Bed</option><option value="5+">5+ Bed</option></select></div>
      <div><label>Property Type</label><select id="type"><option value="detached">Detached</option><option value="semi-detached" selected>Semi-Detached</option><option value="terraced">Terraced</option><option value="flat">Flat</option></select></div>
      <div style="grid-column: 1/-1;"><button type="submit" class="btn" id="submit-btn">Find Comparable Sales</button></div>
    </form>
  </div>

  <div id="error" class="error" style="display:none;"></div>

  <div id="loading" class="status" style="display:none;">
    <div class="spinner"></div>
    <div id="active-research-info">
      <div class="timer-box">Elapsed: <b class="timer-val">0s</b> &nbsp;·&nbsp; Usage: <b class="credits-val">0</b></div>
      <div style="font-size: 0.8rem; margin-bottom: 1rem;">Task ID: <a class="task-id-link" href="#" target="_blank" style="font-family: monospace; font-weight: 600; color: var(--ch-navy);">—</a></div>
      <div class="progress-log"></div>
    </div>
  </div>

  <div id="results-wrap" style="display:none;">
    <div class="table-card">
      <div style="padding:1rem; border-bottom:1px solid #eee;">
        <h2 style="font-size:1.1rem; color:var(--ch-navy);">Results <span id="result-count" style="font-size:0.8rem; color:#666; font-weight:400;"></span></h2>
      </div>
      <div style="overflow-x:auto;">
        <table>
          <thead><tr><th>Address</th><th>Beds</th><th>Sold Date</th><th>Price</th><th>Area</th><th>EPC</th><th>Dist</th><th>Ref</th></tr></thead>
          <tbody id="results-body"></tbody>
        </table>
      </div>
    </div>
    
    <details class="research-details">
      <summary>View Research Details (Log, Usage, Timer)</summary>
      <div id="persistent-research-info" style="padding-top: 1rem;">
        <!-- This will be populated after research finishes -->
      </div>
    </details>
  </div>
</div>

<script>
(function () {
  const form = document.getElementById('comps-form');
  const submitBtn = document.getElementById('submit-btn');
  const resultsWrap = document.getElementById('results-wrap');
  const loading = document.getElementById('loading');
  const activeInfo = document.getElementById('active-research-info');
  const persistentInfo = document.getElementById('persistent-research-info');
  const API = 'comps-api.php';
  
  let seenUpdates = new Set();
  let timerInterval = null;
  let currentTaskId = null;

  function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

  function fmtDist(d) {
    const n = parseFloat(d);
    if (isNaN(n)) return esc(d);            // non-numeric (e.g. "Not available") — show as-is
    return n.toFixed(2) + ' mi';            // always 2 decimals, e.g. 0.00, 0.08, 0.34
  }

  function fmtEpc(rating) {
    if (!rating) return '—';
    const r = String(rating).trim();
    // Treat explicit "not available" / "unknown" / "n/a" as no data
    if (/^(n\/?a|not available|unknown|none|-)$/i.test(r)) return '—';
    // A valid EPC value starts with a single band letter A–G (optionally followed by
    // a space, bracket, digit, etc.) — anchor to the START so "Not available" can't match.
    const m = r.match(/^([A-Ga-g])\b/);
    if (!m) return esc(r);
    const band = m[1].toUpperCase();
    // Standard UK EPC band colours
    const colours = {
      A: '#008054', B: '#19b459', C: '#8dce46',
      D: '#ffd500', E: '#fcaa65', F: '#ef8023', G: '#e9153b'
    };
    const bg = colours[band] || '#A7B0BD';
    // dark text on the light/yellow bands, white on the dark/red ones
    const dark = ['C','D','E'].includes(band);
    const fg = dark ? '#2B2F36' : '#ffffff';
    return `<span style="display:inline-block;min-width:1.5rem;text-align:center;padding:0.1rem 0.4rem;border-radius:4px;font-weight:700;font-size:0.8rem;background:${bg};color:${fg};">${esc(r)}</span>`;
  }

  function formatDate(dateStr) {
    if (!dateStr) return '—';
    try {
      // Handle YYYY-MM-DD or other formats
      const d = new Date(dateStr);
      if (isNaN(d.getTime())) return dateStr;
      return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    } catch(e) { return dateStr; }
  }

  function addLogEntry(container, text) {
    if (seenUpdates.has(text)) return;
    seenUpdates.add(text);
    const entry = document.createElement('div');
    entry.className = 'log-entry';
    entry.textContent = text;
    container.appendChild(entry);
    container.scrollTop = container.scrollHeight;
  }

  function renderComparables(comps) {
    document.getElementById('results-body').innerHTML = (comps || []).map(p => `
            <tr>
              <td><b>${esc(p.address)}</b></td>
              <td>${esc(p.bedrooms)}</td><td>${formatDate(p.date_sold)}</td>
              <td class="price">${esc(p.price)}</td><td>${esc(p.area_sqm)}</td>
              <td>${fmtEpc(p.epc_rating)}</td>
              <td>${fmtDist(p.distance_miles)}</td>
              <td>${p.reference_url && p.reference_url !== 'Not available' ? `<a href="${esc(p.reference_url)}" target="_blank">View</a>` : '—'}</td>
            </tr>
          `).join('');
    document.getElementById('result-count').textContent = '(' + (comps ? comps.length : 0) + ')';
    if (comps && comps.length) document.getElementById('results-wrap').style.display = 'block';
  }

  function handleCreditError(msg) {
    // Stop everything cleanly when the research allowance is exhausted.
    clearInterval(timerInterval);
    const text = msg || 'Research stopped: the AI research allowance has run out. Please top up the account to continue.';
    // Show in the progress log — look it up via activeInfo (the local logBox isn't in scope here)
    const lb = activeInfo ? activeInfo.querySelector('.progress-log') : null;
    if (lb) {
      const entry = document.createElement('div');
      entry.className = 'log-entry';
      entry.textContent = '⚠ ' + text;
      lb.appendChild(entry);
      lb.scrollTop = lb.scrollHeight;
    }
    // Show in the error banner
    const errBox = document.getElementById('error');
    errBox.textContent = text;
    errBox.style.display = 'block';
    // Reset UI
    loading.style.display = 'none';
    submitBtn.disabled = false;
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    document.getElementById('error').style.display = 'none';
    resultsWrap.style.display = 'none';
    submitBtn.disabled = true;
    loading.style.display = 'block';
    
    const logBox = activeInfo.querySelector('.progress-log');
    const timerVal = activeInfo.querySelector('.timer-val');
    const creditsVal = activeInfo.querySelector('.credits-val');
    const taskIdLink = activeInfo.querySelector('.task-id-link');
    
    seenUpdates.clear(); logBox.innerHTML = ''; addLogEntry(logBox, 'Initializing...');
    let elapsed = 0; timerVal.textContent = '0'; creditsVal.textContent = '0';
    timerInterval = setInterval(() => { 
      elapsed++; 
      if (elapsed >= 60) {
        const mins = Math.floor(elapsed / 60);
        const secs = elapsed % 60;
        timerVal.textContent = mins + 'm ' + secs + 's';
      } else {
        timerVal.textContent = elapsed + 's';
      }
    }, 1000);

    try {
      const res = await fetch(API + '?action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ address: document.getElementById('address').value, beds: document.getElementById('beds').value, type: document.getElementById('type').value })
      });
      const data = await res.json();
      if (data.credit_error) { handleCreditError(data.error); return; }
      if (!data.ok) throw new Error(data.error);

      currentTaskId = data.task_id;
      taskIdLink.textContent = currentTaskId;
      taskIdLink.href = API + '?action=debug&task_id=' + encodeURIComponent(currentTaskId);

      const poll = async () => {
        const sRes = await fetch(API + '?action=status&task_id=' + encodeURIComponent(currentTaskId));
        const sData = await sRes.json();
        if (sData.credit_error) { handleCreditError(sData.error); return; }
        if (sData.credit_cap) {
          clearInterval(timerInterval);
          if (sData.comparables && sData.comparables.length) {
            renderComparables(sData.comparables);          // show whatever was produced
            addLogEntry(logBox, '⚠ ' + sData.error + ' Showing partial results (' + sData.comparables.length + ').');
          } else {
            addLogEntry(logBox, '⚠ ' + sData.error);
          }
          const eb = document.getElementById('error');
          eb.textContent = sData.error;
          eb.style.display = 'block';
          creditsVal.textContent = sData.credits || 0;
          loading.style.display = 'none';
          submitBtn.disabled = false;
          persistentInfo.innerHTML = activeInfo.innerHTML;
          return;
        }
        if (!sData.ok) throw new Error(sData.error);

        creditsVal.textContent = sData.credits || 0;
        if (sData.updates) sData.updates.forEach(u => addLogEntry(logBox, u));

        if (sData.status === 'done') {
          const rRes = await fetch(API + '?action=result&task_id=' + encodeURIComponent(currentTaskId));
          const rData = await rRes.json();

          clearInterval(timerInterval);
          loading.style.display = 'none';
          submitBtn.disabled = false;

          renderComparables(rData.comparables);
          persistentInfo.innerHTML = activeInfo.innerHTML;
          resultsWrap.style.display = 'block';
        } else if (sData.status === 'error') {
          throw new Error('Research failed.');
        } else {
          setTimeout(poll, 4000);
        }
      };
      poll();
    } catch (err) {
      clearInterval(timerInterval);
      document.getElementById('error').textContent = err.message;
      document.getElementById('error').style.display = 'block';
      loading.style.display = 'none';
      submitBtn.disabled = false;
    }
  });
})();
</script>
</body>
</html>
