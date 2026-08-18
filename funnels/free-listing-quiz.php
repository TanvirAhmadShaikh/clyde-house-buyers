<?php
/**
 * funnels/free-listing-quiz.php — A/B test variant of /funnels/free-listing.php.
 *
 * Same proposition (free listing on Rightmove/Zoopla/OnTheMarket via partner
 * agent, no upfront fees) but delivered via a Perspective.io-style
 * conversational quiz instead of a static form.
 *
 * Hypothesis being tested:
 *   The static form on /funnels/free-listing.php asks for 2 fields up-front
 *   from cold paid traffic. A 5-step quiz that ends with the same 2-field
 *   capture builds psychological commitment via micro-decisions, should
 *   convert better on cold FB traffic.
 *
 * To compare meaningfully, the two pages match on:
 *   - Offer wording (no upfront fees, fee built into listing price, etc.)
 *   - Trust signals (ICO / HMRC AML / PRS)
 *   - Honest "how does the partner agent get paid" explanation
 *   - 2-field lead capture (name + phone — same as static variant)
 *   - Same /submit-lead.php endpoint with N/A-FUNNEL marker
 *
 * Distinguishing source tag in leads CSV:
 *   "Lead from /funnels/free-listing-quiz.php" → quiz variant
 *   "Lead from /funnels/free-listing.php"      → static variant
 *
 * Run them as separate Facebook ad sets with the same budget and audience.
 * After 2-3 weeks (or 100+ landings each), compare CPL between the two.
 *
 * Question flow (5 steps + lead capture):
 *   Q1 — Why are you thinking of selling?           → situation field
 *   Q2 — How soon do you want to sell?              → timeline field
 *   Q3 — What type of property?                     → notes (informational)
 *   Q4 — What's the property's postcode?            → notes (informational)
 *   Q5 — What matters most in your sale?            → notes (and informs Q6 success message)
 *   Q6 — Name + phone lead capture
 */
$page_title       = "List Your Glasgow Property For Free — 60-Second Assessment | Clyde Housebuyers";
$page_description = "Get your home listed on Rightmove, Zoopla & OnTheMarket with no fees from your sale. Take our 60-second assessment to start. You receive the full agreed price. Glasgow & Scotland's Central Belt.";
$canonical        = "https://clydehousebuyers.co.uk/funnels/free-listing-quiz.php";
$noindex          = true;  // Paid-traffic funnel only.
include __DIR__ . '/../includes/head.php';
?>
<body>
<style>
/* ============================================================================
   Funnel: /funnels/free-listing-quiz.php
   Shares ALL the visual language with /funnels/quick-cash-sale.php
   (same `.fl-*` selectors below mirror that page's `.qcs-*` selectors)
   so visitors who landed on either funnel see a consistent brand.
   ============================================================================ */
.fl { color: var(--ch-navy); background: #fff; }
.fl a { color: var(--ch-gold-dark); }

/* ---- Minimal sticky header ---- */
.fl-header {
  position: sticky; top: 0; z-index: 50;
  background: #fff; border-bottom: 1px solid #e8ecf2;
  padding: 0.6rem 0;
}
.fl-header-inner {
  display: flex; align-items: center; justify-content: space-between;
  max-width: 1100px; margin: 0 auto; padding: 0 1rem; gap: 1rem;
}
.fl-header img { height: 44px; width: auto; }
.fl-header-phone {
  font-weight: 700; color: var(--ch-navy); text-decoration: none;
  font-size: 1.05rem; white-space: nowrap;
}
.fl-header-phone::before { content: "📞 "; }

/* ---- Hero ---- */
.fl-hero {
  background: linear-gradient(135deg, #0B1F3B 0%, #091a32 100%);
  color: #fff; padding: clamp(2.5rem, 6vw, 4rem) 1rem;
  position: relative; overflow: hidden; text-align: center;
}
.fl-hero::before {
  content: ""; position: absolute; right: -120px; top: -80px;
  width: 360px; height: 360px; border-radius: 50%;
  background: radial-gradient(circle, rgba(200,162,74,0.12) 0%, transparent 70%);
  pointer-events: none;
}
.fl-hero-inner { max-width: 760px; margin: 0 auto; position: relative; }
.fl-eyebrow {
  display: inline-block; background: rgba(200,162,74,0.15);
  color: #d6b56b; padding: 0.4rem 0.9rem; border-radius: 999px;
  font-weight: 600; font-size: 0.85rem; letter-spacing: 0.06em; text-transform: uppercase;
  margin-bottom: 1rem;
}
.fl-h1 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(2rem, 5.5vw, 3.2rem); line-height: 1.1; font-weight: 700;
  margin: 0 0 1rem; color: #fff;
}
.fl-h1 .gold { color: #C8A24A; }
.fl-sub {
  font-size: clamp(1.05rem, 2vw, 1.2rem); color: #cdd4dc;
  line-height: 1.55; margin: 0 0 1.75rem; max-width: 600px;
  margin-left: auto; margin-right: auto;
}
.fl-cta {
  display: inline-block; background: #C8A24A; color: #0B1F3B !important;
  font-weight: 700; font-size: 1.1rem; padding: 1rem 2rem;
  border: none; border-radius: 10px; cursor: pointer;
  text-decoration: none !important; text-shadow: none;
  transition: background 0.15s, transform 0.05s;
  box-shadow: 0 6px 18px rgba(200,162,74,0.45);
  min-height: 56px; line-height: 1.3;
}
.fl-cta:hover, .fl-cta:focus, .fl-cta:visited { color: #0B1F3B !important; text-decoration: none !important; }
.fl-cta:hover { background: #d6b56b; }
.fl-cta:active { transform: translateY(1px); }
.fl-cta-big { display: block; width: 100%; max-width: 420px; margin: 0 auto; padding: 1.1rem; font-size: 1.15rem; }
.fl-hero-trust {
  display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem;
  justify-content: center; margin: 1.5rem auto 0;
  font-size: 0.92rem; color: #cdd4dc; max-width: 640px;
}
.fl-hero-trust span { display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
.fl-hero-trust .tick { color: #C8A24A; font-weight: 700; }

/* ---- Section base ---- */
.fl-section { padding: clamp(2.5rem, 6vw, 4rem) 1rem; }
.fl-section-alt { background: #F7F9FC; }
.fl-section-inner { max-width: 1000px; margin: 0 auto; }
.fl-section h2 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(1.6rem, 3.6vw, 2.4rem);
  line-height: 1.15; font-weight: 600;
  text-align: center; margin: 0 0 0.5rem; color: var(--ch-navy);
}
.fl-section .fl-lede {
  text-align: center; max-width: 640px; margin: 0 auto 2.5rem;
  color: var(--ch-navy-80); font-size: 1.05rem;
}

/* ---- Trust strip ---- */
.fl-trust-strip { background: #FBF4E1; padding: 1.5rem 1rem; text-align: center; }
.fl-trust-strip p { margin: 0; color: var(--ch-navy); font-size: 0.95rem; }

/* ============================================================================
   THE QUIZ — same architecture as /funnels/quick-cash-sale.php.
   Single question per screen, progress bar fills with each tap, options
   auto-advance for max conversion velocity.
   ============================================================================ */
.fl-assessment-wrap {
  background: linear-gradient(135deg, #FBF4E1 0%, #f7ecc8 100%);
  padding: clamp(2rem, 5vw, 3.5rem) 1rem;
}
.fl-assessment {
  max-width: 640px; margin: 0 auto;
  background: #fff; border-radius: 16px;
  box-shadow: 0 12px 40px rgba(0,0,0,0.10); overflow: hidden;
}
.fl-progress { height: 6px; background: #e8ecf2; position: relative; }
.fl-progress-bar {
  height: 100%; background: #C8A24A;
  width: 0%; transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.fl-step-label {
  text-align: center; font-size: 0.85rem; color: var(--ch-navy-60);
  padding: 0.75rem; font-weight: 600; letter-spacing: 0.04em;
  border-bottom: 1px solid #e8ecf2;
}
.fl-step {
  display: none; padding: 1.75rem 1.5rem 1.5rem;
  animation: flFade 0.3s ease-out;
}
.fl-step.active { display: block; }
@keyframes flFade {
  from { opacity: 0; transform: translateY(8px); }
  to { opacity: 1; transform: translateY(0); }
}
.fl-q {
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(1.25rem, 3vw, 1.55rem); line-height: 1.3; font-weight: 600;
  color: var(--ch-navy); margin: 0 0 1.25rem; text-align: center;
}
.fl-options { display: flex; flex-direction: column; gap: 0.6rem; }
.fl-option {
  background: #fff; border: 2px solid #e8ecf2; border-radius: 10px;
  padding: 0.95rem 1.1rem; font-size: 1rem; font-weight: 500;
  color: var(--ch-navy); cursor: pointer; text-align: left;
  font-family: inherit; min-height: 52px;
  transition: border-color 0.15s, background 0.15s, transform 0.05s;
  display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;
}
.fl-option::after {
  content: "→"; color: #C8A24A; font-weight: 700; opacity: 0; transition: opacity 0.15s;
}
.fl-option:hover { border-color: #C8A24A; background: #FBF4E1; }
.fl-option:hover::after { opacity: 1; }
.fl-option:active { transform: scale(0.98); }
.fl-option.selected { border-color: #C8A24A; background: #FBF4E1; }

.fl-text-input {
  width: 100%; padding: 0.95rem 1.1rem;
  border: 2px solid #e8ecf2; border-radius: 10px;
  font-size: 1.1rem; font-family: inherit; text-transform: uppercase;
  letter-spacing: 0.05em; text-align: center;
}
.fl-text-input:focus {
  outline: none; border-color: #C8A24A;
  box-shadow: 0 0 0 3px rgba(200,162,74,0.18);
}
.fl-text-error {
  font-size: 0.85rem; color: #b91c1c; margin: 0.5rem 0 0; text-align: center; min-height: 1.2em;
}
.fl-step-actions { display: flex; gap: 0.5rem; margin-top: 1rem; align-items: center; }
.fl-back {
  background: transparent; border: none; color: var(--ch-navy-60);
  font-size: 0.9rem; font-family: inherit; cursor: pointer;
  padding: 0.5rem 0.75rem; flex: 0 0 auto;
}
.fl-back:hover { color: var(--ch-navy); }
.fl-next {
  background: #C8A24A; color: #0B1F3B; border: none; border-radius: 8px;
  padding: 0.85rem 1.5rem; font-size: 1rem; font-weight: 700;
  cursor: pointer; flex: 1; min-height: 52px; font-family: inherit;
}
.fl-next:hover { background: #d6b56b; }
.fl-next:disabled { background: #ddd; color: #888; cursor: not-allowed; }

/* Final lead-capture step */
.fl-final-headline {
  text-align: center; margin: 0 0 0.5rem;
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(1.3rem, 3vw, 1.65rem); color: var(--ch-navy);
}
.fl-final-sub {
  text-align: center; color: var(--ch-navy-80); font-size: 0.95rem;
  margin: 0 0 1.25rem;
}
.fl-final label {
  display: block; font-size: 0.85rem; font-weight: 600;
  margin: 0.75rem 0 0.3rem; color: var(--ch-navy);
}
.fl-final input[type="text"], .fl-final input[type="tel"] {
  width: 100%; padding: 0.85rem 1rem;
  border: 1.5px solid #d3d9e0; border-radius: 8px;
  font-size: 1rem; font-family: inherit;
}
.fl-final input:focus {
  outline: none; border-color: #C8A24A;
  box-shadow: 0 0 0 3px rgba(200,162,74,0.18);
}
.fl-final-note {
  text-align: center; font-size: 0.8rem; color: var(--ch-navy-60); margin: 0.8rem 0 0;
}
.fl-final-note a { color: var(--ch-navy-80); text-decoration: underline; }

.fl-success { display: none; padding: 2.5rem 1.5rem; text-align: center; }
.fl-success.active { display: block; }
.fl-success .check { font-size: 3.5rem; color: #C8A24A; line-height: 1; }
.fl-success h3 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: 1.6rem; margin: 1rem 0 0.5rem;
}
.fl-success p { color: var(--ch-navy-80); margin: 0.5rem 0; }
.fl-success .personal-note {
  background: #FBF4E1; border-left: 4px solid #C8A24A;
  padding: 1rem 1.25rem; margin: 1.25rem auto; text-align: left;
  border-radius: 6px; max-width: 480px; font-size: 0.95rem;
}

/* ---- Why us ---- */
.fl-why { display: grid; grid-template-columns: 1fr; gap: 1rem; }
@media (min-width: 700px) { .fl-why { grid-template-columns: repeat(3, 1fr); } }
.fl-why-card {
  background: #fff; border: 1px solid #e8ecf2; border-radius: 12px; padding: 1.5rem;
}
.fl-why-icon {
  width: 48px; height: 48px; border-radius: 50%;
  background: #0B1F3B; color: #C8A24A;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.4rem; margin-bottom: 0.8rem; font-weight: 700;
}
.fl-why-card h3 { font-family: 'Fraunces', Georgia, serif; font-size: 1.2rem; margin: 0 0 0.4rem; }
.fl-why-card p { margin: 0; color: var(--ch-navy-80); font-size: 0.95rem; line-height: 1.55; }

/* ---- FAQ ---- */
.fl-faqs { max-width: 760px; margin: 0 auto; }
.fl-faqs details {
  background: #fff; border: 1px solid #e8ecf2; border-radius: 10px;
  margin-bottom: 0.6rem; overflow: hidden;
}
.fl-faqs summary {
  cursor: pointer; padding: 1rem 1.25rem;
  font-weight: 600; color: var(--ch-navy); list-style: none;
  position: relative; padding-right: 2.5rem;
}
.fl-faqs summary::-webkit-details-marker { display: none; }
.fl-faqs summary::after {
  content: "+"; position: absolute; right: 1.25rem; top: 50%;
  transform: translateY(-50%); font-size: 1.4rem; color: #C8A24A;
  font-weight: 300; line-height: 1;
}
.fl-faqs details[open] summary::after { content: "−"; }
.fl-faqs details p { padding: 0 1.25rem 1.25rem; margin: 0; color: var(--ch-navy-80); line-height: 1.6; }

/* ---- Final CTA ---- */
.fl-final-cta {
  background: linear-gradient(135deg, #0B1F3B 0%, #091a32 100%);
  color: #fff; padding: clamp(2.5rem, 6vw, 4rem) 1rem; text-align: center;
}
.fl-final-cta h2 { color: #fff; }
.fl-final-cta .fl-lede { color: #cdd4dc; }

/* ---- Sticky mobile CTA ---- */
.fl-sticky {
  display: none;
  position: fixed; left: 0; right: 0; bottom: 0; z-index: 100;
  background: #0B1F3B; padding: 0.75rem 1rem;
  box-shadow: 0 -4px 16px rgba(0,0,0,0.2);
  border-top: 2px solid #C8A24A;
  transition: transform 0.25s ease-out, opacity 0.25s ease-out;
}
.fl-sticky.is-hidden { transform: translateY(110%); opacity: 0; pointer-events: none; }
.fl-sticky a {
  display: block; background: #C8A24A; color: #0B1F3B !important;
  text-align: center; font-weight: 700; font-size: 1.05rem;
  padding: 0.85rem; border-radius: 8px; text-decoration: none !important;
}
.fl-sticky a:hover, .fl-sticky a:visited { color: #0B1F3B !important; text-decoration: none !important; }
@media (max-width: 899px) {
  .fl-sticky { display: block; }
  body { padding-bottom: 70px; }
}

/* ---- Minimal footer ---- */
.fl-footer {
  background: #091a32; color: rgba(255,255,255,0.7);
  padding: 1.5rem 1rem; font-size: 0.8rem; text-align: center; line-height: 1.6;
}
.fl-footer a { color: var(--ch-gold-light); }
</style>

<div class="fl">

<!-- Sticky mobile CTA -->
<div class="fl-sticky"><a href="#assessment">Start My Free Assessment →</a></div>

<!-- Minimal header -->
<header class="fl-header">
  <div class="fl-header-inner">
    <a href="/funnels/free-listing-quiz.php" style="line-height:0;"><img src="/assets/img/logo.png" alt="Clyde Housebuyers"></a>
    <a href="tel:01415301430" class="fl-header-phone">0141 530 1430</a>
  </div>
</header>

<!-- Hero -->
<section class="fl-hero">
  <div class="fl-hero-inner">
    <span class="fl-eyebrow">Glasgow &amp; Scotland's Central Belt</span>
    <h1 class="fl-h1">List your home on <span class="gold">Rightmove, Zoopla &amp; OnTheMarket</span> — no fees from your sale.</h1>
    <p class="fl-sub">Take our 60-second assessment to start. You receive the full agreed sale price. No fees deducted from your proceeds. Our partner estate agent is paid by the buyer side, not by you.</p>
    <a href="#assessment" class="fl-cta fl-cta-big">Start My Free Assessment</a>
    <div class="fl-hero-trust">
      <span><span class="tick">✓</span> Listed on the UK's biggest portals</span>
      <span><span class="tick">✓</span> No fees from your sale</span>
      <span><span class="tick">✓</span> Local, regulated, no pressure</span>
    </div>
  </div>
</section>

<!-- Trust strip -->
<div class="fl-trust-strip">
  <p><strong>Clyde Housebuyers</strong> &nbsp;·&nbsp; ✓ ICO Registered &nbsp;·&nbsp; ✓ HMRC AML Supervised &nbsp;·&nbsp; ✓ PRS Member &nbsp;·&nbsp; Local Glasgow team</p>
</div>

<!-- ===== THE QUIZ ===== -->
<section class="fl-assessment-wrap" id="assessment">
  <div class="fl-assessment">
    <div class="fl-progress"><div class="fl-progress-bar" id="flProgress"></div></div>
    <div class="fl-step-label" id="flStepLabel">Question 1 of 5</div>

    <form id="flForm" action="/submit-lead.php" method="POST" novalidate>
      <!-- Honeypot -->
      <input type="text" name="website" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
      <input type="hidden" name="form_started" value="<?= time() ?>">
      <input type="hidden" name="contact_preference" value="Either">
      <!-- _quiz marker: tells submit-lead.php this is a quiz funnel (real
           postcode + qualification fields, but no email required). -->
      <input type="hidden" name="_quiz" value="1">
      <!-- Quiz answers carried as hidden fields. -->
      <input type="hidden" name="situation" id="fl-situation" value="Lead from /funnels/free-listing-quiz.php">
      <input type="hidden" name="timeline" id="fl-timeline" value="">
      <input type="hidden" name="property_type" id="fl-property_type" value="">
      <input type="hidden" name="condition" id="fl-condition" value="Not assessed (free-listing quiz)">
      <input type="hidden" name="postcode" id="fl-postcode" value="">
      <input type="hidden" name="notes" id="fl-notes" value="">

      <!-- Q1 — Reason for selling -->
      <div class="fl-step active" data-step="1">
        <h2 class="fl-q">Why are you thinking of selling?</h2>
        <div class="fl-options">
          <button type="button" class="fl-option" data-field="reason" data-value="Want maximum value on open market">Want maximum value on open market</button>
          <button type="button" class="fl-option" data-field="reason" data-value="Relocating">Relocating</button>
          <button type="button" class="fl-option" data-field="reason" data-value="Downsizing or upsizing">Downsizing or upsizing</button>
          <button type="button" class="fl-option" data-field="reason" data-value="Tired landlord / exiting BTL">Tired landlord / exiting BTL</button>
          <button type="button" class="fl-option" data-field="reason" data-value="Inherited the property">Inherited the property</button>
          <button type="button" class="fl-option" data-field="reason" data-value="Just exploring options">Just exploring options</button>
        </div>
      </div>

      <!-- Q2 — Timeline -->
      <div class="fl-step" data-step="2">
        <h2 class="fl-q">How soon would you like to sell?</h2>
        <div class="fl-options">
          <button type="button" class="fl-option" data-field="timeline" data-value="ASAP">ASAP</button>
          <button type="button" class="fl-option" data-field="timeline" data-value="Within 3 months">Within 3 months</button>
          <button type="button" class="fl-option" data-field="timeline" data-value="Within 6 months">Within 6 months</button>
          <button type="button" class="fl-option" data-field="timeline" data-value="No rush — when the right offer comes">No rush — when the right offer comes</button>
        </div>
        <div class="fl-step-actions"><button type="button" class="fl-back">← Back</button></div>
      </div>

      <!-- Q3 — Property type -->
      <div class="fl-step" data-step="3">
        <h2 class="fl-q">What type of property is it?</h2>
        <div class="fl-options">
          <button type="button" class="fl-option" data-field="property_type" data-value="Detached">Detached</button>
          <button type="button" class="fl-option" data-field="property_type" data-value="Semi-detached">Semi-detached</button>
          <button type="button" class="fl-option" data-field="property_type" data-value="Terraced">Terraced</button>
          <button type="button" class="fl-option" data-field="property_type" data-value="Flat / Apartment">Flat / Apartment</button>
          <button type="button" class="fl-option" data-field="property_type" data-value="Other">Other</button>
        </div>
        <div class="fl-step-actions"><button type="button" class="fl-back">← Back</button></div>
      </div>

      <!-- Q4 — Postcode -->
      <div class="fl-step" data-step="4">
        <h2 class="fl-q">What's the property's postcode?</h2>
        <input type="text" class="fl-text-input" id="fl-postcode-input" placeholder="e.g. G1 1AA" autocapitalize="characters" maxlength="10">
        <p class="fl-text-error" id="fl-postcode-error"></p>
        <div class="fl-step-actions">
          <button type="button" class="fl-back">← Back</button>
          <button type="button" class="fl-next" data-action="postcode-next">Continue →</button>
        </div>
      </div>

      <!-- Q5 — What matters most -->
      <div class="fl-step" data-step="5">
        <h2 class="fl-q">What matters most to you in this sale?</h2>
        <div class="fl-options">
          <button type="button" class="fl-option" data-field="priority" data-value="Getting the best price">Getting the best price</button>
          <button type="button" class="fl-option" data-field="priority" data-value="A quick, certain sale">A quick, certain sale</button>
          <button type="button" class="fl-option" data-field="priority" data-value="A hassle-free process">A hassle-free process</button>
          <button type="button" class="fl-option" data-field="priority" data-value="Avoiding upfront fees">Avoiding upfront fees</button>
        </div>
        <div class="fl-step-actions"><button type="button" class="fl-back">← Back</button></div>
      </div>

      <!-- Q6 — Lead capture (just name + phone — already invested 5 micro-decisions) -->
      <div class="fl-step" data-step="6">
        <h2 class="fl-final-headline">Your free listing plan is ready</h2>
        <p class="fl-final-sub">Just your name and number — we'll be in touch within one working day to talk it through. No obligation.</p>

        <div class="fl-final">
          <label for="fl-first">First name</label>
          <input id="fl-first" name="first_name" type="text" placeholder="Your first name" autocomplete="given-name" required>

          <label for="fl-phone">Phone number</label>
          <input id="fl-phone" name="phone" type="tel" placeholder="Mobile preferred" autocomplete="tel" required>

          <button type="submit" class="fl-cta fl-cta-big" style="margin-top: 1.25rem;">Get My Free Listing Plan →</button>
          <p class="fl-final-note">No fees from your sale. No obligation. Your details go to us and our partner estate agent — nowhere else. <a href="/privacy.php" target="_blank">Privacy</a></p>
        </div>
        <div class="fl-step-actions"><button type="button" class="fl-back">← Back</button></div>
      </div>

      <!-- Success state -->
      <div class="fl-success" id="flSuccess">
        <div class="check">✓</div>
        <h3 id="flSuccessName">Thanks!</h3>
        <p>We've received your details. One of our team will be in touch within one working day.</p>
        <!-- Personalised note based on Q5 priority. Filled in by JS. -->
        <div class="personal-note" id="flPersonalNote"></div>
        <p style="margin-top: 1rem;"><strong>Need to talk sooner?</strong><br>Call us on <a href="tel:01415301430" style="color:#C8A24A; font-weight:700;">0141 530 1430</a></p>
      </div>
    </form>
  </div>
</section>

<!-- Why us -->
<section class="fl-section fl-section-alt">
  <div class="fl-section-inner">
    <h2>Why list with us</h2>
    <p class="fl-lede">Built on real credentials and a fair process — not on inflated claims.</p>
    <div class="fl-why">
      <div class="fl-why-card">
        <div class="fl-why-icon">£</div>
        <h3>No fees from your sale</h3>
        <p>You pay nothing to list, nothing to advertise, and nothing if it doesn't sell. The partner agent earns a separate buyer's-premium fee paid by the buyer (typically a property investor) — not deducted from your sale proceeds.</p>
      </div>
      <div class="fl-why-card">
        <div class="fl-why-icon">★</div>
        <h3>All three major portals</h3>
        <p>Your property goes live on Rightmove, Zoopla and OnTheMarket — the same exposure a paid estate-agent listing would give you.</p>
      </div>
      <div class="fl-why-card">
        <div class="fl-why-icon">📍</div>
        <h3>Local Glasgow team</h3>
        <p>Real people from the Central Belt — not a national call centre. We know the area, the buyers, and the realistic price for your street.</p>
      </div>
      <div class="fl-why-card">
        <div class="fl-why-icon">⏱</div>
        <h3>Fast to market</h3>
        <p>From the moment we have your details, we aim to have your listing live across the three portals within a few working days.</p>
      </div>
      <div class="fl-why-card">
        <div class="fl-why-icon">🔒</div>
        <h3>No pressure to sign</h3>
        <p>You're free to decide at any point before signing that it's not the right fit — no cost, no obligation, no hassle. Once you sign, the standard listing term is 3 months, the same as most high-street agents.</p>
      </div>
      <div class="fl-why-card">
        <div class="fl-why-icon">✓</div>
        <h3>Regulated &amp; transparent</h3>
        <p>ICO registered (ZC071824). HMRC AML supervised. Member of the Property Redress Scheme (PRS056317). The partner agent is independently regulated as a UK estate agent.</p>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="fl-section">
  <div class="fl-section-inner">
    <h2>Common questions</h2>
    <p class="fl-lede">Honest answers to what sellers ask us most.</p>

    <div class="fl-faqs">
      <details>
        <summary>Is there really no cost to me?</summary>
        <p>Yes — no fees come out of your sale. You receive the full agreed sale price at completion. You pay nothing to list, nothing for advertising, and nothing if the property doesn't sell. The partner estate agent earns a separate buyer's-premium fee from the buyer (typically a property investor), agreed and signed in writing by the buyer before they make their offer.</p>
      </details>
      <details>
        <summary>So how does the partner agent get paid?</summary>
        <p>The partner agent represents property investors looking to buy. They charge their investor-buyers a separate buyer's-premium fee — added to the property purchase price the buyer pays, but not deducted from the price you (the seller) agree. The buyer signs a written buyer's-premium agreement before making any offer, so the fee is fully disclosed and agreed before the transaction. From your side as a seller: you receive the full agreed sale price, with no agent fee taken from your proceeds.</p>
      </details>
      <details>
        <summary>How is this different from a normal estate agent?</summary>
        <p>Your property is listed by a fully regulated estate agent on the same portals (Rightmove, Zoopla, OnTheMarket). The difference is who pays the agent's fee: a high-street agent typically charges the seller a percentage of the sale price (often 1–2%). Our partner agent specialises in investor buyers and charges a buyer's-premium fee to the buyer instead, so no fee comes out of your sale proceeds.</p>
      </details>
      <details>
        <summary>How quickly does the property go live?</summary>
        <p>Once we have your details and the valuation visit is done, we aim to have your listing live across all three portals within a few working days.</p>
      </details>
      <details>
        <summary>Am I tied in to anything?</summary>
        <p>Before you sign, there's no obligation — decide it's not for you and walk away at no cost. Once you sign, the listing agreement includes a standard 3-month minimum marketing term, comparable to most high-street agents. There's no separate penalty fee for leaving early, but because the Home Report and marketing are provided at no upfront cost to you, the agent would look to recover those costs if you cancelled before the term is up.</p>
      </details>
      <details>
        <summary>What if I also want a cash offer?</summary>
        <p>We can do that too. Clyde Housebuyers is also a cash buyer — if a fast cash sale would suit you better, we'll talk you through that route as an alternative. You can see both numbers and choose. <a href="/" target="_blank">See our other selling routes</a>.</p>
      </details>
      <details>
        <summary>Are you regulated?</summary>
        <p>Yes. Clyde Housebuyers is a trading name of PropGain UK Limited (registered in England &amp; Wales, company number 16913648). We're ICO registered (ZC071824), HMRC AML supervised (XNML00000217270), and a member of the Property Redress Scheme (PRS056317).</p>
      </details>
    </div>
  </div>
</section>

<!-- Final CTA -->
<section class="fl-final-cta">
  <div class="fl-section-inner">
    <h2>Ready to list with no fees from your sale?</h2>
    <p class="fl-lede">60-second assessment. No obligation. No pushy follow-ups.</p>
    <a href="#assessment" class="fl-cta fl-cta-big">Start My Free Assessment</a>
  </div>
</section>

<!-- Footer -->
<footer class="fl-footer">
  Clyde Housebuyers · 0141 530 1430 · info@clydehousebuyers.co.uk<br>
  Trading name of PropGain UK Limited (England &amp; Wales, company no. 16913648).
  Registered office: 20 Wenlock Road, London N1 7GU. Correspondence: 48 W George Street, Glasgow G2 1BP.<br>
  ICO ZC071824 · HMRC AML XNML00000217270 · PRS PRS056317 ·
  <a href="/privacy.php">Privacy</a> · <a href="/terms.php">Terms</a> · <a href="/cookies.php">Cookies</a>
</footer>

</div><!-- /.fl -->

<script>
/* ============================================================================
   Quiz flow controller. Same logic as /funnels/quick-cash-sale.php but with
   the priority-personalised success state at the end.
   ============================================================================ */
(function () {
  var TOTAL_QUESTIONS = 5;
  var TOTAL_STEPS = 6;  // 5 questions + lead capture
  var currentStep = 1;
  var answers = {
    reason: "", timeline: "", property_type: "",
    postcode: "", priority: ""
  };

  var form = document.getElementById('flForm');
  var steps = form.querySelectorAll('.fl-step');
  var progress = document.getElementById('flProgress');
  var stepLabel = document.getElementById('flStepLabel');
  var success = document.getElementById('flSuccess');

  function showStep(n) {
    steps.forEach(function (s) { s.classList.remove('active'); });
    var target = form.querySelector('.fl-step[data-step="' + n + '"]');
    if (target) target.classList.add('active');

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

    var wrap = document.getElementById('assessment');
    if (wrap && window.innerWidth < 700) {
      wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  // Option clicks (auto-advance)
  form.querySelectorAll('.fl-option').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var field = btn.getAttribute('data-field');
      var value = btn.getAttribute('data-value');
      answers[field] = value;

      var parent = btn.closest('.fl-step');
      parent.querySelectorAll('.fl-option').forEach(function (o) { o.classList.remove('selected'); });
      btn.classList.add('selected');

      updateHiddenFields();

      setTimeout(function () {
        if (currentStep < TOTAL_STEPS) showStep(currentStep + 1);
      }, 220);
    });
  });

  // Back buttons
  form.querySelectorAll('.fl-back').forEach(function (b) {
    b.addEventListener('click', function () { if (currentStep > 1) showStep(currentStep - 1); });
  });

  // Postcode step
  var postcodeInput = document.getElementById('fl-postcode-input');
  var postcodeError = document.getElementById('fl-postcode-error');
  var postcodeNext = form.querySelector('[data-action="postcode-next"]');
  var POSTCODE_RE = /^([A-Z]{1,2}\d[A-Z\d]?|ASCN|STHL|TDCU|BBND|[BFS]IQQ|PCRN|TKCA) ?\d[A-Z]{2}$/i;

  function validatePostcode() {
    var v = (postcodeInput.value || '').trim().toUpperCase();
    if (!v) { postcodeError.textContent = ''; return false; }
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
    if (validatePostcode()) { updateHiddenFields(); showStep(currentStep + 1); }
  });

  // Map answers to hidden fields
  function updateHiddenFields() {
    document.getElementById('fl-timeline').value = answers.timeline;
    document.getElementById('fl-property_type').value = answers.property_type;
    document.getElementById('fl-postcode').value = answers.postcode;

    var sit = 'Lead from /funnels/free-listing-quiz.php';
    if (answers.reason) sit += ' · Reason: ' + answers.reason;
    document.getElementById('fl-situation').value = sit;

    var notes = '';
    if (answers.priority) notes += 'Biggest priority: ' + answers.priority + '\n';
    notes += '\nSubmitted via /funnels/free-listing-quiz.php assessment.';
    document.getElementById('fl-notes').value = notes;
  }

  // Personalised success message based on Q5 priority.
  // This is what makes the quiz feel "worthwhile" rather than gimmicky —
  // the seller's answers visibly shape what they hear next.
  function personalisedMessage(priority) {
    switch (priority) {
      case 'Getting the best price':
        return 'Based on your answers, the open-market listing route is a good fit. We\'ll arrange the valuation visit and aim to have your home live on the portals within a few working days.';
      case 'A quick, certain sale':
        return 'Based on your answers, we may also want to talk you through our cash sale option alongside the open-market listing. A cash sale typically completes in 14–28 days. We\'ll cover both routes on the call so you can choose.';
      case 'A hassle-free process':
        return 'Our partner agent handles photos, viewings and negotiation — we\'ll explain exactly what to expect on the call so there are no surprises.';
      case 'Avoiding upfront fees':
        return 'Good news — our listing service has no fees from your sale, nothing to pay upfront, and nothing if the property doesn\'t sell. You receive the full agreed sale price. We\'ll explain the full fee structure on the call.';
      default:
        return 'We\'ll cover all your selling options on the call and recommend the route that fits your situation best.';
    }
  }

  // Final submit
  form.addEventListener('submit', function (e) {
    e.preventDefault();
    updateHiddenFields();

    var first = form.querySelector('[name="first_name"]').value.trim();
    var phone = form.querySelector('[name="phone"]').value.trim();
    if (!first || !phone) return;

    var btn = form.querySelector('[type="submit"]');
    var orig = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Sending...';

    var data = new FormData(form);
    fetch('/submit-lead.php', { method: 'POST', body: data })
      .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
      .then(function (res) {
        if (res && res.ok) {
          steps.forEach(function (s) { s.classList.remove('active'); });
          progress.style.width = '100%';
          stepLabel.textContent = 'Done!';
          success.classList.add('active');
          var name = (res.firstName || first).replace(/[<>]/g, '');
          document.getElementById('flSuccessName').textContent = 'Thanks, ' + name + '!';
          document.getElementById('flPersonalNote').textContent = personalisedMessage(answers.priority);
          document.getElementById('assessment').scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
          btn.disabled = false;
          btn.textContent = orig;
          var msg = (res && res.error)
            ? res.error
            : 'Sorry — we couldn\'t submit your details. Please try again, or call us on 0141 530 1430.';
          alert(msg);
        }
      })
      .catch(function () {
        btn.disabled = false;
        btn.textContent = orig;
        alert('Sorry — we couldn\'t submit your details. Please try again, or call us on 0141 530 1430.');
      });
  });

  // Smooth scroll for hero/sticky CTAs
  document.querySelectorAll('a[href="#assessment"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var target = document.getElementById('assessment');
      if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });

  // Hide sticky CTA while the assessment is in view
  var stickyEl = document.querySelector('.fl-sticky');
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
