<?php
/**
 * funnels/quick-cash-sale.php — Quick cash sale Facebook ad funnel.
 *
 * Purpose: Convert paid traffic seeking a fast cash property sale into
 * qualified leads via a Perspective.io-style conversational assessment
 * (7 questions, one per screen, single tap progresses), then lead capture.
 *
 * Distinct from /funnels/free-listing.php — that one pitches an open-market
 * listing via the partner agent (no fees, full market exposure). This one
 * pitches direct cash purchase by Clyde Housebuyers (fast, certain, below
 * market). Different funnels, different audiences, different ads.
 *
 * Mechanics:
 *  - 7-step JS-driven question flow with progress bar (no page reloads).
 *  - All answers carried as hidden form fields; final step captures contact
 *    details and submits to /submit-lead.php using the SAME field names the
 *    handler already validates as a full valuation lead (postcode is real,
 *    so this is NOT marked as N/A-CONTACT — handler stores all 7 answers
 *    cleanly with priority tagging on "ASAP" leads).
 *  - Mobile-first (touch targets ≥ 48px, single-question-per-screen).
 *  - noindex (paid traffic only — under /funnels/ which is already blocked
 *    folder-wide in robots.txt and excluded from sitemap).
 *
 * Compliance posture:
 *  - No invented testimonials, no fabricated review scores, no "X homes
 *    purchased" counters, no "X years in business" — all of these would be
 *    false claims for a pre-trading business (we removed identical claims
 *    from the main site earlier this session and shouldn't reintroduce them
 *    on the most-scrutinised surface).
 *  - Real credentials shown instead (ICO, HMRC AML, PRS).
 *  - Trade-off stated honestly: cash purchase = below market value in
 *    exchange for speed and certainty (not promising both top price AND fast).
 *  - Privacy notice link present at lead-capture.
 */
$page_title       = "Sell Your Property For Cash — Free Assessment | Clyde Housebuyers";
$page_description = "Need to sell your home quickly? Take our 60-second assessment for a no-obligation cash offer. Any condition, no fees, fast completion. Glasgow & Scotland's Central Belt.";
$canonical        = "https://clydehousebuyers.co.uk/funnels/quick-cash-sale.php";
$noindex          = true;  // Paid-traffic funnel only.
include __DIR__ . '/../includes/head.php';
?>
<body>
<style>
/* ============================================================================
   Funnel: /funnels/quick-cash-sale.php — scoped styles.
   Reuses brand colours from style.css (--ch-navy, --ch-gold, etc.).
   Conversion principles applied:
   - Hero CTA above the fold, contrasting, large touch target
   - Conversational flow: one question per screen, progress bar, micro-commitments
   - No nav, no escape routes (sticky logo + phone only)
   - Honest social proof (credentials, not invented stats)
   ============================================================================ */
.qcs { color: var(--ch-navy); background: #fff; }
.qcs a { color: var(--ch-gold-dark); }

/* ---- Minimal sticky header — logo + phone only (no escape routes) ---- */
.qcs-header {
  position: sticky; top: 0; z-index: 50;
  background: #fff; border-bottom: 1px solid #e8ecf2;
  padding: 0.6rem 0;
}
.qcs-header-inner {
  display: flex; align-items: center; justify-content: space-between;
  max-width: 1100px; margin: 0 auto; padding: 0 1rem; gap: 1rem;
}
.qcs-header img { height: 44px; width: auto; }
.qcs-header-phone {
  font-weight: 700; color: var(--ch-navy); text-decoration: none;
  font-size: 1.05rem; white-space: nowrap;
}
.qcs-header-phone::before { content: "📞 "; }

/* ---- Hero ---- */
.qcs-hero {
  background: linear-gradient(135deg, #0B1F3B 0%, #091a32 100%);
  color: #fff; padding: clamp(2.5rem, 6vw, 4rem) 1rem;
  position: relative; overflow: hidden; text-align: center;
}
.qcs-hero::before {
  content: ""; position: absolute; right: -120px; top: -80px;
  width: 360px; height: 360px; border-radius: 50%;
  background: radial-gradient(circle, rgba(200,162,74,0.12) 0%, transparent 70%);
  pointer-events: none;
}
.qcs-hero-inner { max-width: 760px; margin: 0 auto; position: relative; }
.qcs-eyebrow {
  display: inline-block; background: rgba(200,162,74,0.15);
  color: #d6b56b; padding: 0.4rem 0.9rem; border-radius: 999px;
  font-weight: 600; font-size: 0.85rem; letter-spacing: 0.06em; text-transform: uppercase;
  margin-bottom: 1rem;
}
.qcs-h1 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(2rem, 5.5vw, 3.2rem); line-height: 1.1; font-weight: 700;
  margin: 0 0 1rem; color: #fff;
}
.qcs-h1 .gold { color: #C8A24A; }
.qcs-sub {
  font-size: clamp(1.05rem, 2vw, 1.2rem); color: #cdd4dc;
  line-height: 1.55; margin: 0 0 1.75rem; max-width: 600px;
  margin-left: auto; margin-right: auto;
}
.qcs-cta {
  display: inline-block; background: #C8A24A; color: #0B1F3B !important;
  font-weight: 700; font-size: 1.1rem; padding: 1rem 2rem;
  border: none; border-radius: 10px; cursor: pointer;
  text-decoration: none !important;  /* override global a { text-decoration: underline; } */
  text-shadow: none;
  transition: background 0.15s, transform 0.05s;
  box-shadow: 0 6px 18px rgba(200,162,74,0.45);
  min-height: 56px;  /* touch-friendly */
  line-height: 1.3;
}
.qcs-cta:hover, .qcs-cta:focus, .qcs-cta:visited { color: #0B1F3B !important; text-decoration: none !important; }
.qcs-cta:hover { background: #d6b56b; }
.qcs-cta:active { transform: translateY(1px); }
.qcs-cta-big {
  display: block; width: 100%; max-width: 420px; margin: 0 auto;
  padding: 1.1rem; font-size: 1.15rem;
}
.qcs-hero-trust {
  display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem;
  justify-content: center; margin: 1.5rem auto 0;
  font-size: 0.92rem; color: #cdd4dc; max-width: 640px;
}
.qcs-hero-trust span { display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
.qcs-hero-trust .tick { color: #C8A24A; font-weight: 700; }

/* ---- Section base ---- */
.qcs-section { padding: clamp(2.5rem, 6vw, 4rem) 1rem; }
.qcs-section-alt { background: #F7F9FC; }
.qcs-section-inner { max-width: 1000px; margin: 0 auto; }
.qcs-section h2 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(1.6rem, 3.6vw, 2.4rem);
  line-height: 1.15; font-weight: 600;
  text-align: center; margin: 0 0 0.5rem; color: var(--ch-navy);
}
.qcs-section .qcs-lede {
  text-align: center; max-width: 640px; margin: 0 auto 2.5rem;
  color: var(--ch-navy-80); font-size: 1.05rem;
}

/* ---- Problem situation grid ---- */
.qcs-problems { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
@media (min-width: 700px) { .qcs-problems { grid-template-columns: repeat(4, 1fr); gap: 1rem; } }
.qcs-problem {
  background: #fff; border: 1px solid #e8ecf2; border-radius: 10px;
  padding: 1rem; text-align: center; font-size: 0.95rem; font-weight: 600;
  color: var(--ch-navy);
}
.qcs-problem .ico { color: #C8A24A; display: block; font-size: 1.5rem; margin-bottom: 0.3rem; }

/* ============================================================================
   CONVERSATIONAL ASSESSMENT — the conversion mechanism.
   - One question visible at a time; tap an option → progress.
   - Progress bar fills as user advances (psychological commitment).
   - All answers stored as hidden inputs and submitted together at the end.
   ============================================================================ */
.qcs-assessment-wrap {
  background: linear-gradient(135deg, #FBF4E1 0%, #f7ecc8 100%);
  padding: clamp(2rem, 5vw, 3.5rem) 1rem;
}
.qcs-assessment {
  max-width: 640px; margin: 0 auto;
  background: #fff; border-radius: 16px;
  box-shadow: 0 12px 40px rgba(0,0,0,0.10);
  overflow: hidden;
}
.qcs-progress {
  height: 6px; background: #e8ecf2; position: relative;
}
.qcs-progress-bar {
  height: 100%; background: #C8A24A;
  width: 0%; transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.qcs-step-label {
  text-align: center; font-size: 0.85rem; color: var(--ch-navy-60);
  padding: 0.75rem; font-weight: 600; letter-spacing: 0.04em;
  border-bottom: 1px solid #e8ecf2;
}
.qcs-step {
  display: none; padding: 1.75rem 1.5rem 1.5rem;
  animation: qcsFade 0.3s ease-out;
}
.qcs-step.active { display: block; }
@keyframes qcsFade {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
.qcs-q {
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(1.25rem, 3vw, 1.55rem); line-height: 1.3; font-weight: 600;
  color: var(--ch-navy); margin: 0 0 1.25rem; text-align: center;
}
.qcs-options { display: flex; flex-direction: column; gap: 0.6rem; }
.qcs-option {
  background: #fff; border: 2px solid #e8ecf2; border-radius: 10px;
  padding: 0.95rem 1.1rem; font-size: 1rem; font-weight: 500;
  color: var(--ch-navy); cursor: pointer; text-align: left;
  font-family: inherit;
  transition: border-color 0.15s, background 0.15s, transform 0.05s;
  min-height: 52px;  /* touch-friendly */
  display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
}
.qcs-option::after {
  content: "→"; color: #C8A24A; font-weight: 700; opacity: 0; transition: opacity 0.15s;
}
.qcs-option:hover { border-color: #C8A24A; background: #FBF4E1; }
.qcs-option:hover::after { opacity: 1; }
.qcs-option:active { transform: scale(0.98); }
.qcs-option.selected { border-color: #C8A24A; background: #FBF4E1; }

/* Text input variant (postcode question) */
.qcs-text-input {
  width: 100%; padding: 0.95rem 1.1rem;
  border: 2px solid #e8ecf2; border-radius: 10px;
  font-size: 1.1rem; font-family: inherit; text-transform: uppercase;
  letter-spacing: 0.05em; text-align: center;
}
.qcs-text-input:focus {
  outline: none; border-color: #C8A24A;
  box-shadow: 0 0 0 3px rgba(200,162,74,0.18);
}
.qcs-text-error {
  font-size: 0.85rem; color: #b91c1c; margin: 0.5rem 0 0; text-align: center;
  min-height: 1.2em;
}

.qcs-step-actions {
  display: flex; gap: 0.5rem; margin-top: 1rem; align-items: center;
}
.qcs-back {
  background: transparent; border: none; color: var(--ch-navy-60);
  font-size: 0.9rem; font-family: inherit; cursor: pointer;
  padding: 0.5rem 0.75rem; flex: 0 0 auto;
}
.qcs-back:hover { color: var(--ch-navy); }
.qcs-next {
  background: #C8A24A; color: #0B1F3B; border: none; border-radius: 8px;
  padding: 0.85rem 1.5rem; font-size: 1rem; font-weight: 700;
  cursor: pointer; flex: 1; min-height: 52px;
  font-family: inherit;
}
.qcs-next:hover { background: #d6b56b; }
.qcs-next:disabled { background: #ddd; color: #888; cursor: not-allowed; }

/* Final step (lead capture) ---------------------------------------------- */
.qcs-final-headline {
  text-align: center; margin: 0 0 0.5rem;
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(1.3rem, 3vw, 1.65rem); color: var(--ch-navy);
}
.qcs-final-sub {
  text-align: center; color: var(--ch-navy-80); font-size: 0.95rem;
  margin: 0 0 1.25rem;
}
.qcs-final label {
  display: block; font-size: 0.85rem; font-weight: 600;
  margin: 0.75rem 0 0.3rem; color: var(--ch-navy);
}
.qcs-final input[type="text"], .qcs-final input[type="tel"], .qcs-final input[type="email"] {
  width: 100%; padding: 0.85rem 1rem;
  border: 1.5px solid #d3d9e0; border-radius: 8px;
  font-size: 1rem; font-family: inherit;
}
.qcs-final input:focus {
  outline: none; border-color: #C8A24A;
  box-shadow: 0 0 0 3px rgba(200,162,74,0.18);
}
.qcs-final-note {
  text-align: center; font-size: 0.8rem; color: var(--ch-navy-60);
  margin: 0.8rem 0 0;
}
.qcs-final-note a { color: var(--ch-navy-80); text-decoration: underline; }

.qcs-success {
  display: none; padding: 2.5rem 1.5rem; text-align: center;
}
.qcs-success.active { display: block; }
.qcs-success .check {
  font-size: 3.5rem; color: #C8A24A; line-height: 1;
}
.qcs-success h3 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: 1.6rem; margin: 1rem 0 0.5rem;
}
.qcs-success p { color: var(--ch-navy-80); margin: 0.5rem 0; }

/* ---- Trust strip ---- */
.qcs-trust-strip { background: #FBF4E1; padding: 1.5rem 1rem; text-align: center; }
.qcs-trust-strip p { margin: 0; color: var(--ch-navy); font-size: 0.95rem; }

/* ---- Why us / honest benefits ---- */
.qcs-why { display: grid; grid-template-columns: 1fr; gap: 1rem; }
@media (min-width: 700px) { .qcs-why { grid-template-columns: repeat(3, 1fr); } }
.qcs-why-card {
  background: #fff; border: 1px solid #e8ecf2; border-radius: 12px; padding: 1.5rem;
}
.qcs-why-icon {
  width: 48px; height: 48px; border-radius: 50%;
  background: #0B1F3B; color: #C8A24A;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem; margin-bottom: 0.8rem; font-weight: 700;
}
.qcs-why-card h3 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: 1.2rem; margin: 0 0 0.4rem;
}
.qcs-why-card p { margin: 0; color: var(--ch-navy-80); font-size: 0.95rem; line-height: 1.55; }

/* ---- The honest trade-off section (replaces fake testimonials) ---- */
.qcs-tradeoff {
  background: #fff; border: 1px solid #e8ecf2; border-radius: 14px;
  padding: 1.75rem; max-width: 760px; margin: 0 auto;
}
.qcs-tradeoff h3 {
  font-family: 'Fraunces', Georgia, serif;
  margin: 0 0 1rem; font-size: 1.3rem;
}
.qcs-tradeoff table {
  width: 100%; border-collapse: collapse; margin-top: 0.5rem;
}
.qcs-tradeoff th, .qcs-tradeoff td {
  padding: 0.7rem 0.5rem; text-align: left;
  border-bottom: 1px solid #f1f3f6;
  font-size: 0.95rem; vertical-align: top;
}
.qcs-tradeoff th {
  background: #0B1F3B; color: #fff; font-weight: 600;
  font-size: 0.85rem; letter-spacing: 0.03em;
}
.qcs-tradeoff tr:last-child td { border-bottom: none; }

/* ---- FAQ ---- */
.qcs-faqs { max-width: 760px; margin: 0 auto; }
.qcs-faqs details {
  background: #fff; border: 1px solid #e8ecf2; border-radius: 10px;
  margin-bottom: 0.6rem; overflow: hidden;
}
.qcs-faqs summary {
  cursor: pointer; padding: 1rem 1.25rem;
  font-weight: 600; color: var(--ch-navy); list-style: none;
  position: relative; padding-right: 2.5rem;
}
.qcs-faqs summary::-webkit-details-marker { display: none; }
.qcs-faqs summary::after {
  content: "+"; position: absolute; right: 1.25rem; top: 50%;
  transform: translateY(-50%); font-size: 1.4rem; color: #C8A24A;
  font-weight: 300; line-height: 1;
}
.qcs-faqs details[open] summary::after { content: "−"; }
.qcs-faqs details p {
  padding: 0 1.25rem 1.25rem; margin: 0;
  color: var(--ch-navy-80); line-height: 1.6;
}

/* ---- Final CTA ---- */
.qcs-final-cta {
  background: linear-gradient(135deg, #0B1F3B 0%, #091a32 100%);
  color: #fff; padding: clamp(2.5rem, 6vw, 4rem) 1rem; text-align: center;
}
.qcs-final-cta h2 { color: #fff; }
.qcs-final-cta .qcs-lede { color: #cdd4dc; }

/* ---- Sticky mobile CTA bar ---- */
.qcs-sticky {
  display: none;
  position: fixed; left: 0; right: 0; bottom: 0; z-index: 100;
  background: #0B1F3B; padding: 0.75rem 1rem;
  box-shadow: 0 -4px 16px rgba(0,0,0,0.2);
  border-top: 2px solid #C8A24A;
  transition: transform 0.25s ease-out, opacity 0.25s ease-out;
}
/* Hide while the assessment is on screen — otherwise the sticky 'Start My
   Assessment' button stacks under the active step's Continue/Submit button. */
.qcs-sticky.is-hidden { transform: translateY(110%); opacity: 0; pointer-events: none; }
.qcs-sticky a {
  display: block; background: #C8A24A; color: #0B1F3B !important;
  text-align: center; font-weight: 700; font-size: 1.05rem;
  padding: 0.85rem; border-radius: 8px; text-decoration: none !important;
}
.qcs-sticky a:hover, .qcs-sticky a:visited { color: #0B1F3B !important; text-decoration: none !important; }
@media (max-width: 899px) {
  .qcs-sticky { display: block; }
  body { padding-bottom: 70px; }
}

/* ---- Minimal footer ---- */
.qcs-footer {
  background: #091a32; color: rgba(255,255,255,0.7);
  padding: 1.5rem 1rem; font-size: 0.8rem; text-align: center;
  line-height: 1.6;
}
.qcs-footer a { color: var(--ch-gold-light); }
</style>

<div class="qcs">

<!-- ===== Sticky mobile CTA ===== -->
<div class="qcs-sticky"><a href="#assessment">Start My Free Assessment →</a></div>

<!-- ===== Minimal header ===== -->
<header class="qcs-header">
  <div class="qcs-header-inner">
    <a href="/funnels/quick-cash-sale.php" style="line-height:0;"><img src="/assets/img/logo.png" alt="Clyde Housebuyers"></a>
    <a href="tel:01415301430" class="qcs-header-phone">0141 530 1430</a>
  </div>
</header>

<!-- ===== Hero ===== -->
<section class="qcs-hero">
  <div class="qcs-hero-inner">
    <span class="qcs-eyebrow">Glasgow &amp; Scotland's Central Belt</span>
    <h1 class="qcs-h1">Need to sell your property <span class="gold">quickly?</span></h1>
    <p class="qcs-sub">Get a no-obligation cash offer and discover your selling options in less than 60 seconds.</p>
    <a href="#assessment" class="qcs-cta qcs-cta-big">Start My Free Property Assessment</a>
    <div class="qcs-hero-trust">
      <span><span class="tick">✓</span> No estate agent fees</span>
      <span><span class="tick">✓</span> No obligation</span>
      <span><span class="tick">✓</span> Fast completion possible</span>
      <span><span class="tick">✓</span> Any property condition</span>
    </div>
  </div>
</section>

<!-- ===== Trust strip ===== -->
<div class="qcs-trust-strip">
  <p><strong>Clyde Housebuyers</strong> &nbsp;·&nbsp; ✓ ICO Registered &nbsp;·&nbsp; ✓ HMRC AML Supervised &nbsp;·&nbsp; ✓ PRS Member &nbsp;·&nbsp; Local Glasgow team</p>
</div>

<!-- ===== Seller problems ===== -->
<section class="qcs-section">
  <div class="qcs-section-inner">
    <h2>We help homeowners in situations like these</h2>
    <p class="qcs-lede">If any of these describe where you are right now — we can help you find the route that fits.</p>
    <div class="qcs-problems">
      <div class="qcs-problem"><span class="ico">⏱</span>Need a quick sale</div>
      <div class="qcs-problem"><span class="ico">📜</span>Probate / inherited</div>
      <div class="qcs-problem"><span class="ico">📦</span>Relocating for work</div>
      <div class="qcs-problem"><span class="ico">👥</span>Tired landlord</div>
      <div class="qcs-problem"><span class="ico">🛠</span>Property needs repairs</div>
      <div class="qcs-problem"><span class="ico">🔗</span>Avoiding a chain</div>
      <div class="qcs-problem"><span class="ico">💷</span>Financial pressure</div>
      <div class="qcs-problem"><span class="ico">💔</span>Divorce or separation</div>
    </div>
  </div>
</section>

<!-- ===== THE ASSESSMENT (conversion mechanism) ===== -->
<section class="qcs-assessment-wrap" id="assessment">
  <div class="qcs-assessment">
    <div class="qcs-progress"><div class="qcs-progress-bar" id="qcsProgress"></div></div>
    <div class="qcs-step-label" id="qcsStepLabel">Question 1 of 7</div>

    <form id="qcsForm" action="/submit-lead.php" method="POST" novalidate>
      <!-- Honeypot -->
      <input type="text" name="website" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
      <input type="hidden" name="form_started" value="<?= time() ?>">
      <input type="hidden" name="contact_preference" value="Either">
      <!-- _quiz marker: tells submit-lead.php to use the quiz-variant validation
           (real postcode + qualification fields, but no email required). -->
      <input type="hidden" name="_quiz" value="1">
      <!-- All assessment answers carried as hidden fields (mapped to submit-lead.php's known fields). -->
      <input type="hidden" name="situation" id="qcs-situation" value="Lead from /funnels/quick-cash-sale.php">
      <input type="hidden" name="timeline" id="qcs-timeline" value="">
      <input type="hidden" name="property_type" id="qcs-property_type" value="">
      <input type="hidden" name="condition" id="qcs-condition" value="">
      <input type="hidden" name="postcode" id="qcs-postcode" value="">
      <!-- notes accumulates value + concern + funnel context -->
      <input type="hidden" name="notes" id="qcs-notes" value="">

      <!-- ===== STEP 1 — Why selling ===== -->
      <div class="qcs-step active" data-step="1">
        <h2 class="qcs-q">Why are you considering selling?</h2>
        <div class="qcs-options">
          <button type="button" class="qcs-option" data-field="reason" data-value="Need a quick sale">Need a quick sale</button>
          <button type="button" class="qcs-option" data-field="reason" data-value="Probate / inheritance">Probate / inheritance</button>
          <button type="button" class="qcs-option" data-field="reason" data-value="Relocation">Relocation</button>
          <button type="button" class="qcs-option" data-field="reason" data-value="Landlord issues">Landlord issues</button>
          <button type="button" class="qcs-option" data-field="reason" data-value="Property needs repairs">Property needs repairs</button>
          <button type="button" class="qcs-option" data-field="reason" data-value="Financial reasons">Financial reasons</button>
          <button type="button" class="qcs-option" data-field="reason" data-value="Other">Other</button>
        </div>
      </div>

      <!-- ===== STEP 2 — Timeline ===== -->
      <div class="qcs-step" data-step="2">
        <h2 class="qcs-q">How soon would you like to sell?</h2>
        <div class="qcs-options">
          <button type="button" class="qcs-option" data-field="timeline" data-value="ASAP">ASAP</button>
          <button type="button" class="qcs-option" data-field="timeline" data-value="Within 30 days">Within 30 days</button>
          <button type="button" class="qcs-option" data-field="timeline" data-value="Within 3 months">Within 3 months</button>
          <button type="button" class="qcs-option" data-field="timeline" data-value="Just exploring options">Just exploring options</button>
        </div>
        <div class="qcs-step-actions">
          <button type="button" class="qcs-back">← Back</button>
        </div>
      </div>

      <!-- ===== STEP 3 — Property type ===== -->
      <div class="qcs-step" data-step="3">
        <h2 class="qcs-q">What type of property do you own?</h2>
        <div class="qcs-options">
          <button type="button" class="qcs-option" data-field="property_type" data-value="Detached">Detached</button>
          <button type="button" class="qcs-option" data-field="property_type" data-value="Semi-detached">Semi-detached</button>
          <button type="button" class="qcs-option" data-field="property_type" data-value="Terraced">Terraced</button>
          <button type="button" class="qcs-option" data-field="property_type" data-value="Flat / Apartment">Flat / Apartment</button>
          <button type="button" class="qcs-option" data-field="property_type" data-value="Other">Other</button>
        </div>
        <div class="qcs-step-actions">
          <button type="button" class="qcs-back">← Back</button>
        </div>
      </div>

      <!-- ===== STEP 4 — Condition ===== -->
      <div class="qcs-step" data-step="4">
        <h2 class="qcs-q">How would you describe the property's condition?</h2>
        <div class="qcs-options">
          <button type="button" class="qcs-option" data-field="condition" data-value="Excellent">Excellent</button>
          <button type="button" class="qcs-option" data-field="condition" data-value="Good">Good</button>
          <button type="button" class="qcs-option" data-field="condition" data-value="Needs updating">Needs updating</button>
          <button type="button" class="qcs-option" data-field="condition" data-value="Requires major repairs">Requires major repairs</button>
        </div>
        <div class="qcs-step-actions">
          <button type="button" class="qcs-back">← Back</button>
        </div>
      </div>

      <!-- ===== STEP 5 — Postcode ===== -->
      <div class="qcs-step" data-step="5">
        <h2 class="qcs-q">What's the property's postcode?</h2>
        <input type="text" class="qcs-text-input" id="qcs-postcode-input" placeholder="e.g. G1 1AA" autocapitalize="characters" maxlength="10">
        <p class="qcs-text-error" id="qcs-postcode-error"></p>
        <div class="qcs-step-actions">
          <button type="button" class="qcs-back">← Back</button>
          <button type="button" class="qcs-next" data-action="postcode-next">Continue →</button>
        </div>
      </div>

      <!-- ===== STEP 6 — Estimated value ===== -->
      <div class="qcs-step" data-step="6">
        <h2 class="qcs-q">What's the property's estimated value?</h2>
        <div class="qcs-options">
          <button type="button" class="qcs-option" data-field="value" data-value="Under £150,000">Under £150,000</button>
          <button type="button" class="qcs-option" data-field="value" data-value="£150,000 – £300,000">£150,000 – £300,000</button>
          <button type="button" class="qcs-option" data-field="value" data-value="£300,000 – £500,000">£300,000 – £500,000</button>
          <button type="button" class="qcs-option" data-field="value" data-value="£500,000+">£500,000+</button>
          <button type="button" class="qcs-option" data-field="value" data-value="Not sure">Not sure</button>
        </div>
        <div class="qcs-step-actions">
          <button type="button" class="qcs-back">← Back</button>
        </div>
      </div>

      <!-- ===== STEP 7 — Main concern ===== -->
      <div class="qcs-step" data-step="7">
        <h2 class="qcs-q">What's your biggest concern about selling?</h2>
        <div class="qcs-options">
          <button type="button" class="qcs-option" data-field="concern" data-value="Speed">Speed</button>
          <button type="button" class="qcs-option" data-field="concern" data-value="Certainty">Certainty</button>
          <button type="button" class="qcs-option" data-field="concern" data-value="Achieving the best price">Achieving the best price</button>
          <button type="button" class="qcs-option" data-field="concern" data-value="Property condition">Property condition</button>
          <button type="button" class="qcs-option" data-field="concern" data-value="Avoiding fees">Avoiding fees</button>
          <button type="button" class="qcs-option" data-field="concern" data-value="Avoiding delays">Avoiding delays</button>
        </div>
        <div class="qcs-step-actions">
          <button type="button" class="qcs-back">← Back</button>
        </div>
      </div>

      <!-- ===== STEP 8 — Lead capture ===== -->
      <div class="qcs-step" data-step="8">
        <h2 class="qcs-final-headline">Your personalised selling options are ready</h2>
        <p class="qcs-final-sub">Enter your details and one of our property specialists will be in touch to discuss your options. No obligation — your details aren't shared outside Clyde Housebuyers.</p>

        <div class="qcs-final">
          <label for="qcs-first">First name</label>
          <input id="qcs-first" name="first_name" type="text" placeholder="Your first name" autocomplete="given-name" required>

          <label for="qcs-phone">Phone number</label>
          <input id="qcs-phone" name="phone" type="tel" placeholder="Mobile preferred" autocomplete="tel" required>

          <button type="submit" class="qcs-cta qcs-cta-big" style="margin-top: 1.25rem;">Get My Free Property Consultation</button>
          <p class="qcs-final-note">No fees. No obligation. <a href="/privacy.php" target="_blank">Privacy</a></p>
        </div>
        <div class="qcs-step-actions">
          <button type="button" class="qcs-back">← Back</button>
        </div>
      </div>

      <!-- ===== Success state (shown after successful submit) ===== -->
      <div class="qcs-success" id="qcsSuccess">
        <div class="check">✓</div>
        <h3 id="qcsSuccessName">Thanks!</h3>
        <p>We've received your details. One of our property specialists will be in touch within one working day to discuss your options.</p>
        <p style="margin-top: 1rem;"><strong>Need to talk sooner?</strong><br>Call us on <a href="tel:01415301430" style="color:#C8A24A; font-weight:700;">0141 530 1430</a></p>
      </div>
    </form>
  </div>
</section>

<!-- ===== Why us — honest credentials, no fake stats ===== -->
<section class="qcs-section qcs-section-alt">
  <div class="qcs-section-inner">
    <h2>Why homeowners choose us</h2>
    <p class="qcs-lede">Built on real credentials and a fair process — not on inflated claims.</p>
    <div class="qcs-why">
      <div class="qcs-why-card">
        <div class="qcs-why-icon">⏱</div>
        <h3>Fast completion possible</h3>
        <p>For cash purchases, we can typically complete in 14–28 days using regulated Scottish solicitors. The exact timing depends on missives, searches and lender (if any) on either side.</p>
      </div>
      <div class="qcs-why-card">
        <div class="qcs-why-icon">£</div>
        <h3>No hidden fees</h3>
        <p>You pay no agent fees, no marketing fees, no legal fees on a cash sale. The offer we make is the amount you receive.</p>
      </div>
      <div class="qcs-why-card">
        <div class="qcs-why-icon">🛠</div>
        <h3>No repairs required</h3>
        <p>We buy in any condition — from move-in ready to needing significant work. You don't have to fix anything or even clear the property.</p>
      </div>
      <div class="qcs-why-card">
        <div class="qcs-why-icon">📅</div>
        <h3>Flexible completion dates</h3>
        <p>Need to complete fast? We can. Need extra time to find your next home or settle affairs? We work around your timeline.</p>
      </div>
      <div class="qcs-why-card">
        <div class="qcs-why-icon">✓</div>
        <h3>Regulated &amp; transparent</h3>
        <p>ICO registered (ZC071824). HMRC AML supervised. Member of the Property Redress Scheme (PRS056317). All transactions via regulated Scottish solicitors.</p>
      </div>
      <div class="qcs-why-card">
        <div class="qcs-why-icon">📍</div>
        <h3>Local Glasgow team</h3>
        <p>We're based in Glasgow and operate across Scotland's Central Belt. Real people who know your area — not a national call centre.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== The honest trade-off (replaces invented testimonials) ===== -->
<section class="qcs-section">
  <div class="qcs-section-inner">
    <h2>What you should know before requesting a cash offer</h2>
    <p class="qcs-lede">We don't believe in surprises. Here's how a cash sale to us compares to the open market, honestly.</p>

    <div class="qcs-tradeoff">
      <h3>Cash sale vs open market</h3>
      <table>
        <thead>
          <tr>
            <th>Factor</th>
            <th>Cash sale to us</th>
            <th>Open market</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Price</strong></td>
            <td>Below market (typically 80–90% of value)</td>
            <td>Full market value, if buyer found</td>
          </tr>
          <tr>
            <td><strong>Certainty</strong></td>
            <td>Very high — direct buyer, no chain</td>
            <td>Depends on the market</td>
          </tr>
          <tr>
            <td><strong>Timeline</strong></td>
            <td>14–28 days typical</td>
            <td>Typically 2–6 months</td>
          </tr>
          <tr>
            <td><strong>Fees to you</strong></td>
            <td>None</td>
            <td>Agent + legal fees</td>
          </tr>
          <tr>
            <td><strong>Condition</strong></td>
            <td>Any condition accepted</td>
            <td>Usually needs to present well</td>
          </tr>
          <tr>
            <td><strong>Best for</strong></td>
            <td>Speed, certainty, difficult situations</td>
            <td>Top price, sellers who can wait</td>
          </tr>
        </tbody>
      </table>
      <p style="margin: 1rem 0 0; font-size: 0.9rem; color: var(--ch-navy-80);">If a cash sale isn't right for your situation, we'll tell you — and we can point you to alternatives like our partner agent for an open-market listing, or one of our other selling routes.</p>
    </div>
  </div>
</section>

<!-- ===== FAQ ===== -->
<section class="qcs-section qcs-section-alt">
  <div class="qcs-section-inner">
    <h2>Common questions</h2>
    <p class="qcs-lede">Honest answers to what most sellers ask us first.</p>

    <div class="qcs-faqs">
      <details>
        <summary>How quickly can I sell my property?</summary>
        <p>For a cash sale, completion in 14–28 days is typical. The exact timing depends on the missives process, conveyancing searches, and the responsiveness of solicitors on both sides. If you need to complete urgently for a specific reason (e.g. a repossession hearing), we can sometimes move faster — tell us at the assessment stage.</p>
      </details>
      <details>
        <summary>Is there any obligation?</summary>
        <p>No. The assessment is free and there's no obligation to accept any offer we make. You can take our offer, walk away, or use it as leverage with another buyer — your choice.</p>
      </details>
      <details>
        <summary>Do I need to make repairs?</summary>
        <p>No — we buy in any condition. We don't need you to fix anything, paint anything, or even clear the property. Our offer accounts for the work needed.</p>
      </details>
      <details>
        <summary>Are there any fees?</summary>
        <p>No. We don't charge any fees. On a cash sale, you don't pay estate agent commission or marketing costs either. The cash offer figure we agree is what you receive.</p>
      </details>
      <details>
        <summary>How is my property valued?</summary>
        <p>We look at recent comparable sales on your street and immediate area (via Registers of Scotland data), the property's specific condition, and what it would cost to bring it up to mortgageable standard. We share the comparable evidence with you so you can see how we got to the number.</p>
      </details>
      <details>
        <summary>What happens after I submit my details?</summary>
        <p>One of our team calls or emails you within one working day (your choice of contact method). We have an informal initial conversation to understand your situation, and arrange a property visit at a time that suits you. After the visit, we send you a written cash offer with the comparable evidence behind it. You take it or leave it — no pressure.</p>
      </details>
      <details>
        <summary>What if a cash sale isn't right for me?</summary>
        <p>We'll be honest about it. If a cash sale doesn't fit your situation — for example, if your property is in good condition and you can afford to wait for market price — we'll tell you, and we can introduce you to our partner agent for an open-market listing instead. We'd rather lose the deal than push you into the wrong route.</p>
      </details>
      <details>
        <summary>Are you regulated?</summary>
        <p>Yes. Clyde Housebuyers is a trading name of PropGain UK Limited (registered in England &amp; Wales, company number 16913648). We're ICO registered (ZC071824), HMRC AML supervised (XNML00000217270), and a member of the Property Redress Scheme (PRS056317). All transactions go through regulated Scottish solicitors.</p>
      </details>
    </div>
  </div>
</section>

<!-- ===== Final CTA ===== -->
<section class="qcs-final-cta">
  <div class="qcs-section-inner">
    <h2>Ready to explore your selling options?</h2>
    <p class="qcs-lede">Take the 60-second assessment. No obligation. No pushy follow-ups.</p>
    <a href="#assessment" class="qcs-cta qcs-cta-big" style="background:#C8A24A;">Start My Free Assessment</a>
  </div>
</section>

<!-- ===== Minimal footer ===== -->
<footer class="qcs-footer">
  Clyde Housebuyers · 0141 530 1430 · info@clydehousebuyers.co.uk<br>
  Trading name of PropGain UK Limited (England &amp; Wales, company no. 16913648).
  Registered office: 20 Wenlock Road, London N1 7GU. Correspondence: 48 W George Street, Glasgow G2 1BP.<br>
  ICO ZC071824 · HMRC AML XNML00000217270 · PRS PRS056317 ·
  <a href="/privacy.php">Privacy</a> · <a href="/terms.php">Terms</a> · <a href="/cookies.php">Cookies</a>
</footer>

</div><!-- /.qcs -->

<script>
/* ============================================================================
   Assessment flow controller — lightweight, no framework.
   - 7 questions visible one at a time + final lead-capture step + success state
   - "Back" navigation supported
   - Postcode validated client-side before advancing (server validates again)
   - Final submit goes via fetch() to /submit-lead.php, expecting {ok:true}
   - State is in-memory (no localStorage) — fine for a single-session funnel
   ============================================================================ */
(function () {
  var TOTAL_QUESTIONS = 7;       // visible question count for progress label
  var TOTAL_STEPS = 8;            // 7 questions + lead capture
  var currentStep = 1;
  var answers = {
    reason: "", timeline: "", property_type: "",
    condition: "", postcode: "", value: "", concern: ""
  };

  var form = document.getElementById('qcsForm');
  var steps = form.querySelectorAll('.qcs-step');
  var progress = document.getElementById('qcsProgress');
  var stepLabel = document.getElementById('qcsStepLabel');
  var success = document.getElementById('qcsSuccess');

  function showStep(n) {
    steps.forEach(function (s) { s.classList.remove('active'); });
    var target = form.querySelector('.qcs-step[data-step="' + n + '"]');
    if (target) target.classList.add('active');

    // Progress bar: question N of 7. Step 8 (lead capture) shows 100%.
    var pct;
    if (n <= TOTAL_QUESTIONS) {
      pct = ((n - 1) / TOTAL_QUESTIONS) * 100;
      stepLabel.textContent = 'Question ' + n + ' of ' + TOTAL_QUESTIONS;
    } else {
      pct = 100;
      stepLabel.textContent = 'Almost done — just your details';
    }
    progress.style.width = pct + '%';
    currentStep = n;

    // Scroll the assessment back into view on mobile after step change
    var wrap = document.getElementById('assessment');
    if (wrap && window.innerWidth < 700) {
      wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  // ---- Option click handlers (auto-advance) ----------------------------
  // Maps button data-field → answers key AND sets the matching hidden form input.
  // Note: 'reason' and 'concern' both feed into the `situation` and `notes`
  // hidden fields respectively (see updateHiddenFields() below).
  form.querySelectorAll('.qcs-option').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var field = btn.getAttribute('data-field');
      var value = btn.getAttribute('data-value');
      answers[field] = value;

      // Visual: highlight selected, deselect siblings
      var parent = btn.closest('.qcs-step');
      parent.querySelectorAll('.qcs-option').forEach(function (o) { o.classList.remove('selected'); });
      btn.classList.add('selected');

      updateHiddenFields();

      // Auto-advance after a brief beat so the user sees their selection register
      setTimeout(function () {
        if (currentStep < TOTAL_STEPS) showStep(currentStep + 1);
      }, 220);
    });
  });

  // ---- Back buttons ----------------------------------------------------
  form.querySelectorAll('.qcs-back').forEach(function (b) {
    b.addEventListener('click', function () {
      if (currentStep > 1) showStep(currentStep - 1);
    });
  });

  // ---- Postcode step (text input + manual Continue) --------------------
  var postcodeInput = document.getElementById('qcs-postcode-input');
  var postcodeError = document.getElementById('qcs-postcode-error');
  var postcodeNext = form.querySelector('[data-action="postcode-next"]');

  // UK postcode regex — matches RoyalMail format. Server re-validates.
  var POSTCODE_RE = /^([A-Z]{1,2}\d[A-Z\d]?|ASCN|STHL|TDCU|BBND|[BFS]IQQ|PCRN|TKCA) ?\d[A-Z]{2}$/i;

  function validatePostcode() {
    var v = (postcodeInput.value || '').trim().toUpperCase();
    if (!v) { postcodeError.textContent = ''; return false; }
    // Insert space if missing (e.g. G11AA → G1 1AA)
    if (!/\s/.test(v) && v.length >= 5) {
      v = v.slice(0, v.length - 3) + ' ' + v.slice(-3);
      postcodeInput.value = v;
    }
    if (!POSTCODE_RE.test(v)) {
      postcodeError.textContent = 'Please enter a valid UK postcode (e.g. G1 1AA).';
      return false;
    }
    postcodeError.textContent = '';
    answers.postcode = v;
    return true;
  }

  postcodeInput.addEventListener('input', function () { postcodeError.textContent = ''; });
  postcodeInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { e.preventDefault(); postcodeNext.click(); }
  });
  postcodeNext.addEventListener('click', function () {
    if (validatePostcode()) {
      updateHiddenFields();
      showStep(currentStep + 1);
    }
  });

  // ---- Hidden-field updater -------------------------------------------
  // Maps assessment answers onto the handler's known field names.
  function updateHiddenFields() {
    document.getElementById('qcs-timeline').value = answers.timeline;
    document.getElementById('qcs-property_type').value = answers.property_type;
    document.getElementById('qcs-condition').value = answers.condition;
    document.getElementById('qcs-postcode').value = answers.postcode;

    // `situation`: lead source + reason for selling (reaches handler under 1000-char cap)
    var sit = 'Lead from /funnels/quick-cash-sale.php';
    if (answers.reason) sit += ' · Reason: ' + answers.reason;
    document.getElementById('qcs-situation').value = sit;

    // `notes`: estimated value + biggest concern (formatted for human reading in CSV/email)
    var notes = '';
    if (answers.value)   notes += 'Estimated value: ' + answers.value + '\n';
    if (answers.concern) notes += 'Biggest concern: ' + answers.concern + '\n';
    notes += '\nSubmitted via /funnels/quick-cash-sale.php assessment.';
    document.getElementById('qcs-notes').value = notes;
  }

  // ---- Final submit ----------------------------------------------------
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    updateHiddenFields();

    // Basic client-side check; server is the source of truth
    var first = form.querySelector('[name="first_name"]').value.trim();
    var phone = form.querySelector('[name="phone"]').value.trim();
    if (!first || !phone) return;

    var btn = form.querySelector('[type="submit"]');
    var orig = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Sending...';

    var data = new FormData(form);
    fetch('/submit-lead.php', { method: 'POST', body: data })
      .then(function (r) { return r.json(); })
      .then(function (res) {
        if (res && res.ok) {
          // Replace assessment with success state
          steps.forEach(function (s) { s.classList.remove('active'); });
          progress.style.width = '100%';
          stepLabel.textContent = 'Done!';
          success.classList.add('active');
          document.getElementById('qcsSuccessName').textContent =
            'Thanks, ' + (res.firstName || first) + '!';
          // Scroll success into view
          document.getElementById('assessment').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
          btn.disabled = false;
          btn.textContent = orig;
          alert('Sorry — we couldn\'t submit your details. Please try again, or call us on 0141 530 1430.');
        }
      })
      .catch(function () {
        btn.disabled = false;
        btn.textContent = orig;
        alert('Sorry — we couldn\'t submit your details. Please try again, or call us on 0141 530 1430.');
      });
  });

  // ---- Smooth scroll for hero CTA + sticky CTA ------------------------
  document.querySelectorAll('a[href="#assessment"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var target = document.getElementById('assessment');
      if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  // ---- Hide the sticky CTA whenever the assessment is on screen --------
  // The sticky bar's job is to drive visitors INTO the assessment. Once
  // they're in it, the sticky stacks under the question's own Continue /
  // Submit button — same accidental-tap risk as on the free-listing page.
  var stickyEl = document.querySelector('.qcs-sticky');
  var assessmentEl = document.getElementById('assessment');
  if (stickyEl && assessmentEl && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        stickyEl.classList.toggle('is-hidden', entry.isIntersecting);
      });
    }, { threshold: 0.15 });
    io.observe(assessmentEl);
  }
})();
</script>

</body>
</html>
