<?php
/**
 * funnels/free-listing.php — Facebook ad landing page.
 *
 * Purpose: Convert paid traffic (Facebook ad: "List your property for FREE")
 * into seller leads. Pitches the FREE listing-via-partner-agent route:
 * Clyde Housebuyers passes the lead to its partner estate agent, who lists
 * the property on Rightmove / Zoopla / OnTheMarket at no cost to the seller.
 *
 * Mechanics:
 *  - Single-page focused funnel (deliberately uses minimal header/footer to
 *    avoid escape routes — pure conversion page).
 *  - Submits to /submit-lead.php with postcode=N/A-FUNNEL so the handler
 *    applies the lightest validation (name + phone only — address, email,
 *    situation captured on the follow-up call to minimise cold-traffic friction).
 *  - noindex (this is paid traffic only — keep it out of organic SEO).
 *
 * Compliance:
 *  - Claims match the FB ad (active partner-agent arrangement, real listings).
 *  - All regulator references (PRS, ICO, HMRC AML) match the rest of the site.
 *  - Privacy notice link present below form (UK GDPR).
 */
$page_title       = "List Your Glasgow Property For FREE | Clyde Housebuyers";
$page_description = "Get your home listed on Rightmove, Zoopla & OnTheMarket with no fees from your sale. Our partner estate agent is paid by the buyer side. You receive the full agreed price. Glasgow & Central Belt.";
$canonical        = "https://clydehousebuyers.co.uk/funnels/free-listing.php";
$noindex          = true;  // Paid-traffic landing page — don't compete with organic.
include __DIR__ . '/../includes/head.php';
?>
<body>
<style>
  /* ---- Landing-page-specific styles (scoped) ----
     Reuses brand colours from style.css. Kept inline to keep the page
     single-file and fast-loading (no extra CSS request). */
  .lp-page {
    --pad-y: clamp(2.5rem, 6vw, 4rem);
    color: var(--ch-navy);
    background: #fff;
  }
  .lp-page a { color: var(--ch-gold-dark); }

  /* Minimal header — logo + phone only, no nav (kills escape routes) */
  .lp-header {
    position: sticky; top: 0; z-index: 50;
    background: #fff; border-bottom: 1px solid #e8ecf2;
    padding: 0.6rem 0;
  }
  .lp-header-inner {
    display: flex; align-items: center; justify-content: space-between;
    max-width: 1100px; margin: 0 auto; padding: 0 1rem;
    gap: 1rem;
  }
  .lp-header img { height: 44px; width: auto; }
  .lp-header-phone {
    font-weight: 700; color: var(--ch-navy); text-decoration: none;
    font-size: 1.05rem; white-space: nowrap;
  }
  .lp-header-phone::before { content: "📞 "; }

  /* ---- Hero ---- */
  .lp-hero {
    background: linear-gradient(135deg, #0B1F3B 0%, #091a32 100%);
    color: #fff;
    padding: var(--pad-y) 1rem;
    position: relative; overflow: hidden;
  }
  .lp-hero::before {
    content: ""; position: absolute; right: -120px; top: -80px;
    width: 360px; height: 360px; border-radius: 50%;
    background: radial-gradient(circle, rgba(200,162,74,0.12) 0%, transparent 70%);
    pointer-events: none;
  }
  .lp-hero-inner {
    max-width: 1100px; margin: 0 auto;
    display: grid; grid-template-columns: 1fr; gap: 2rem;
    position: relative;
  }
  @media (min-width: 900px) {
    .lp-hero-inner { grid-template-columns: 1.1fr 0.9fr; gap: 3rem; align-items: center; }
  }
  .lp-eyebrow {
    display: inline-block; background: rgba(200,162,74,0.15);
    color: #d6b56b; padding: 0.4rem 0.9rem; border-radius: 999px;
    font-weight: 600; font-size: 0.85rem; letter-spacing: 0.06em; text-transform: uppercase;
    margin-bottom: 1rem;
  }
  .lp-h1 {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(2rem, 5.5vw, 3.4rem);
    line-height: 1.05; font-weight: 700;
    margin: 0 0 1rem; color: #fff;
  }
  .lp-h1 .gold { color: #C8A24A; }
  .lp-sub {
    font-size: clamp(1.05rem, 2vw, 1.2rem);
    color: #cdd4dc; line-height: 1.55; margin: 0 0 1.5rem;
    max-width: 540px;
  }

  /* Trust chips under hero subhead */
  .lp-trust-chips {
    display: flex; flex-wrap: wrap; gap: 0.5rem 1.25rem; margin: 1.25rem 0 0;
    font-size: 0.92rem; color: #cdd4dc;
  }
  .lp-trust-chips span { display: inline-flex; align-items: center; gap: 0.4rem; }
  .lp-trust-chips .tick { color: #C8A24A; font-weight: 700; }

  /* ---- Lead form card (hero right) ---- */
  .lp-form-card {
    background: #fff; color: var(--ch-navy);
    border-radius: 14px; padding: 1.5rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.35);
    border-top: 4px solid #C8A24A;
  }
  .lp-form-card h2 {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.4rem; margin: 0 0 0.25rem; line-height: 1.2;
  }
  .lp-form-card .form-sub {
    font-size: 0.9rem; color: var(--ch-navy-80); margin: 0 0 1.1rem;
  }
  .lp-form-card label {
    display: block; font-size: 0.85rem; font-weight: 600;
    margin: 0.75rem 0 0.3rem; color: var(--ch-navy);
  }
  .lp-form-card input {
    width: 100%; padding: 0.75rem 0.9rem;
    border: 1.5px solid #d3d9e0; border-radius: 8px;
    font-size: 1rem; font-family: inherit;
    transition: border-color 0.15s;
  }
  .lp-form-card input:focus {
    outline: none; border-color: #C8A24A;
    box-shadow: 0 0 0 3px rgba(200,162,74,0.18);
  }
  .lp-cta {
    display: block; width: 100%;
    background: #C8A24A; color: #0B1F3B;
    font-weight: 700; font-size: 1.1rem;
    padding: 0.95rem; border: none; border-radius: 8px;
    margin-top: 1rem; cursor: pointer;
    transition: background 0.15s, transform 0.05s;
    box-shadow: 0 4px 14px rgba(200,162,74,0.4);
  }
  .lp-cta:hover { background: #d6b56b; }
  .lp-cta:active { transform: translateY(1px); }
  .lp-form-note {
    text-align: center; font-size: 0.8rem; color: var(--ch-navy-60);
    margin: 0.8rem 0 0;
  }
  .lp-form-note a { color: var(--ch-navy-80); text-decoration: underline; }

  /* ---- Section base ---- */
  .lp-section { padding: var(--pad-y) 1rem; }
  .lp-section-alt { background: #F7F9FC; }
  .lp-section-inner { max-width: 1100px; margin: 0 auto; }
  .lp-section h2 {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(1.6rem, 3.6vw, 2.4rem);
    line-height: 1.15; font-weight: 600;
    text-align: center; margin: 0 0 0.5rem;
    color: var(--ch-navy);
  }
  .lp-section .section-lede {
    text-align: center; max-width: 640px; margin: 0 auto 2.5rem;
    color: var(--ch-navy-80); font-size: 1.05rem;
  }

  /* ---- Benefits grid ---- */
  .lp-benefits {
    display: grid; grid-template-columns: 1fr; gap: 1rem;
  }
  @media (min-width: 600px) { .lp-benefits { grid-template-columns: 1fr 1fr; } }
  @media (min-width: 960px) { .lp-benefits { grid-template-columns: repeat(3, 1fr); } }
  .lp-benefit {
    background: #fff; border: 1px solid #e8ecf2; border-radius: 12px;
    padding: 1.5rem; text-align: left;
  }
  .lp-benefit-icon {
    width: 48px; height: 48px; border-radius: 50%;
    background: #0B1F3B; color: #C8A24A;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; margin-bottom: 0.8rem; font-weight: 700;
  }
  .lp-benefit h3 {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.2rem; margin: 0 0 0.4rem;
  }
  .lp-benefit p { margin: 0; color: var(--ch-navy-80); font-size: 0.95rem; line-height: 1.5; }

  /* ---- How it works ---- */
  .lp-steps {
    display: grid; grid-template-columns: 1fr; gap: 1.5rem;
    max-width: 900px; margin: 0 auto;
  }
  @media (min-width: 700px) { .lp-steps { grid-template-columns: repeat(3, 1fr); } }
  .lp-step {
    text-align: center; padding: 0.5rem;
    position: relative;
  }
  .lp-step-num {
    width: 60px; height: 60px; border-radius: 50%;
    background: #C8A24A; color: #0B1F3B;
    display: flex; align-items: center; justify-content: center;
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.8rem; font-weight: 700;
    margin: 0 auto 1rem;
  }
  .lp-step h3 {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.2rem; margin: 0 0 0.4rem;
  }
  .lp-step p { color: var(--ch-navy-80); margin: 0; font-size: 0.95rem; line-height: 1.5; }

  /* ---- Pain-point section (who we help) ---- */
  .lp-pain-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;
    max-width: 800px; margin: 0 auto;
  }
  @media (min-width: 700px) { .lp-pain-grid { grid-template-columns: repeat(3, 1fr); gap: 1rem; } }
  .lp-pain-item {
    background: #fff; border: 1px solid #e8ecf2; border-radius: 10px;
    padding: 1rem; text-align: center; font-size: 0.95rem; font-weight: 600;
    color: var(--ch-navy);
  }
  .lp-pain-item span { color: #C8A24A; display: block; font-size: 1.3rem; margin-bottom: 0.3rem; }

  /* ---- Social proof ---- */
  .lp-stats {
    display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;
    max-width: 700px; margin: 0 auto 2.5rem;
    text-align: center;
  }
  @media (min-width: 700px) { .lp-stats { grid-template-columns: repeat(3, 1fr); } }
  .lp-stat-num {
    font-family: 'Fraunces', Georgia, serif;
    font-size: clamp(1.8rem, 4vw, 2.4rem); color: #C8A24A;
    font-weight: 700; display: block; line-height: 1.1;
  }
  .lp-stat-label {
    color: var(--ch-navy-80); font-size: 0.9rem; margin-top: 0.2rem;
  }
  .lp-testimonials {
    display: grid; grid-template-columns: 1fr; gap: 1.25rem;
  }
  @media (min-width: 700px) { .lp-testimonials { grid-template-columns: 1fr 1fr; } }
  .lp-testimonial {
    background: #fff; border: 1px solid #e8ecf2; border-radius: 12px;
    padding: 1.5rem; position: relative;
  }
  .lp-stars { color: #C8A24A; font-size: 1.1rem; margin-bottom: 0.5rem; letter-spacing: 0.1em; }
  .lp-testimonial p {
    font-style: italic; color: var(--ch-navy); margin: 0 0 0.75rem;
    line-height: 1.6;
  }
  .lp-testimonial-author { font-size: 0.9rem; color: var(--ch-navy-60); font-weight: 600; }

  /* ---- FAQ ---- */
  .lp-faqs { max-width: 760px; margin: 0 auto; }
  .lp-faqs details {
    background: #fff; border: 1px solid #e8ecf2; border-radius: 10px;
    margin-bottom: 0.6rem; padding: 0; overflow: hidden;
  }
  .lp-faqs summary {
    cursor: pointer; padding: 1rem 1.25rem;
    font-weight: 600; color: var(--ch-navy); list-style: none;
    position: relative; padding-right: 2.5rem;
  }
  .lp-faqs summary::-webkit-details-marker { display: none; }
  .lp-faqs summary::after {
    content: "+"; position: absolute; right: 1.25rem; top: 50%;
    transform: translateY(-50%); font-size: 1.4rem; color: #C8A24A;
    font-weight: 300; line-height: 1;
  }
  .lp-faqs details[open] summary::after { content: "−"; }
  .lp-faqs details p {
    padding: 0 1.25rem 1.25rem; margin: 0;
    color: var(--ch-navy-80); line-height: 1.6;
  }

  /* ---- Final CTA ---- */
  .lp-final {
    background: linear-gradient(135deg, #0B1F3B 0%, #091a32 100%);
    color: #fff; padding: var(--pad-y) 1rem;
    text-align: center;
  }
  .lp-final h2 { color: #fff; }
  .lp-final .section-lede { color: #cdd4dc; }
  .lp-final-form-wrap { max-width: 540px; margin: 2rem auto 0; }

  /* ---- Sticky mobile CTA ---- */
  .lp-sticky-cta {
    display: none;
    position: fixed; left: 0; right: 0; bottom: 0; z-index: 100;
    background: #0B1F3B; padding: 0.75rem 1rem;
    box-shadow: 0 -4px 16px rgba(0,0,0,0.2);
    border-top: 2px solid #C8A24A;
    transition: transform 0.25s ease-out, opacity 0.25s ease-out;
  }
  /* When a form card is in view, slide the sticky bar out — otherwise the
     sticky CTA stacks directly under the form's own submit button, which
     causes accidental taps that scroll back to the form already on screen. */
  .lp-sticky-cta.is-hidden { transform: translateY(110%); opacity: 0; pointer-events: none; }
  .lp-sticky-cta a {
    display: block; background: #C8A24A; color: #0B1F3B !important;
    text-align: center; font-weight: 700; font-size: 1.05rem;
    padding: 0.85rem; border-radius: 8px; text-decoration: none !important;
  }
  .lp-sticky-cta a:hover, .lp-sticky-cta a:visited { color: #0B1F3B !important; text-decoration: none !important; }
  @media (max-width: 899px) {
    .lp-sticky-cta { display: block; }
    body { padding-bottom: 70px; }  /* clearance for sticky bar */
  }

  /* ---- Form success state (replaces the form in-place on successful submit) ---- */
  .lp-success { text-align: center; padding: 0.5rem 0.25rem; }
  .lp-success .check {
    width: 64px; height: 64px; margin: 0 auto 1rem;
    border-radius: 50%; background: #C8A24A; color: #0B1F3B;
    display: flex; align-items: center; justify-content: center;
    font-size: 2rem; font-weight: 700; line-height: 1;
  }
  .lp-success h3 {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.5rem; margin: 0 0 0.5rem; color: var(--ch-navy);
  }
  .lp-success p { color: var(--ch-navy-80); margin: 0.5rem 0; line-height: 1.55; }
  .lp-success .phone-line { margin-top: 1rem; font-size: 0.95rem; }
  .lp-success .phone-line a { color: var(--ch-gold-dark); font-weight: 700; text-decoration: none; }

  /* ---- Minimal footer ---- */
  .lp-footer {
    background: #091a32; color: rgba(255,255,255,0.7);
    padding: 1.5rem 1rem; font-size: 0.8rem; text-align: center;
    line-height: 1.6;
  }
  .lp-footer a { color: var(--ch-gold-light); }
</style>

<div class="lp-page">

<!-- ============== STICKY MOBILE CTA ============== -->
<div class="lp-sticky-cta">
  <a href="#lead-form">List My Property For Free →</a>
</div>

<!-- ============== MINIMAL HEADER ============== -->
<header class="lp-header">
  <div class="lp-header-inner">
    <a href="/funnels/free-listing.php" style="line-height:0;"><img src="/assets/img/logo.png" alt="Clyde Housebuyers"></a>
    <a href="tel:01415301430" class="lp-header-phone">0141 530 1430</a>
  </div>
</header>

<!-- ============== HERO ============== -->
<section class="lp-hero">
  <div class="lp-hero-inner">
    <div>
      <span class="lp-eyebrow">Glasgow &amp; Scotland's Central Belt</span>
      <h1 class="lp-h1">List your home on <span class="gold">Rightmove, Zoopla &amp; OnTheMarket</span> — no fees from your sale.</h1>
      <p class="lp-sub">You receive the full agreed sale price. No upfront fees. No fees deducted from your proceeds. Our local estate-agent partner handles the listing, the viewings and the negotiation — and only earns if your home sells.</p>

      <div class="lp-trust-chips">
        <span><span class="tick">✓</span> Listed on the UK's biggest portals</span>
        <span><span class="tick">✓</span> No fees from your sale</span>
        <span><span class="tick">✓</span> Local, regulated, no pressure</span>
      </div>
    </div>

    <!-- Hero form (the primary conversion point, above the fold) -->
    <div class="lp-form-card" id="lead-form">
      <h2>Get your free listing started</h2>
      <p class="form-sub">Tell us about your property — we'll be in touch within one working day.</p>

      <form action="/submit-lead.php" method="POST" novalidate data-funnel-form>
        <!-- Honeypot (matches existing form) -->
        <input type="text" name="website" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
        <!-- Mark as contact-form variant so existing handler uses lighter validation -->
        <input type="hidden" name="postcode" value="N/A-FUNNEL">
        <input type="hidden" name="form_started" value="<?= time() ?>">
        <input type="hidden" name="contact_preference" value="Either">
        <!-- Tag the source for the leads CSV -->
        <input type="hidden" name="situation" value="Lead from /funnels/free-listing.php (Facebook ad — free listing)">

        <label for="lf-name">Your name</label>
        <input id="lf-name" name="first_name" type="text" placeholder="First name" required>

        <label for="lf-phone">Phone number</label>
        <input id="lf-phone" name="phone" type="tel" placeholder="Mobile preferred" required>

        <button type="submit" class="lp-cta">Get My Free Listing →</button>
        <p class="lp-form-note">No fees from your sale. No obligation. Your details go to us and our partner estate agent to arrange your listing — nowhere else. <a href="/privacy.php" target="_blank">Privacy</a></p>
      </form>
    </div>
  </div>
</section>

<!-- ============== TRUST STRIP ============== -->
<section class="lp-section" style="padding-top:2rem; padding-bottom:2rem; background:#FBF4E1;">
  <div class="lp-section-inner" style="text-align:center;">
    <p style="margin:0; font-size:0.95rem; color:var(--ch-navy); font-weight:500;">
      <strong>Clyde Housebuyers</strong> &nbsp;·&nbsp; ✓ ICO Registered &nbsp;·&nbsp; ✓ HMRC AML Supervised &nbsp;·&nbsp; ✓ PRS Member &nbsp;·&nbsp; Local Glasgow team
    </p>
  </div>
</section>

<!-- ============== BENEFITS ============== -->
<section class="lp-section">
  <div class="lp-section-inner">
    <h2>What you get with our listing service</h2>
    <p class="section-lede">You receive the full agreed sale price. No fees come out of your proceeds. Our partner estate agent is paid by the buyer side as a separate buyer's-premium fee, not from your sale.</p>

    <div class="lp-benefits">
      <div class="lp-benefit">
        <div class="lp-benefit-icon">£</div>
        <h3>No fees from your sale</h3>
        <p>You pay nothing to list, nothing to advertise, and nothing if it doesn't sell. The partner agent earns a separate buyer's-premium fee paid by the buyer — it's not deducted from the price you agree.</p>
      </div>
      <div class="lp-benefit">
        <div class="lp-benefit-icon">★</div>
        <h3>All three major portals</h3>
        <p>Your property goes live on Rightmove, Zoopla and OnTheMarket — the same exposure a paid estate agent would give you.</p>
      </div>
      <div class="lp-benefit">
        <div class="lp-benefit-icon">📍</div>
        <h3>Local Glasgow team</h3>
        <p>Real people from the Central Belt — not a call centre. We know the area, the buyers, and the realistic price for your street.</p>
      </div>
      <div class="lp-benefit">
        <div class="lp-benefit-icon">⏱</div>
        <h3>Fast to market</h3>
        <p>From the moment we have your details, we aim to have your listing live across the portals within a few working days.</p>
      </div>
      <div class="lp-benefit">
        <div class="lp-benefit-icon">🔒</div>
        <h3>No pressure to sign</h3>
        <p>You're free to decide at any point before signing that it's not the right fit — no cost, no obligation, no hassle. Once you sign, the standard listing term is 3 months, the same as most high-street agents.</p>
      </div>
      <div class="lp-benefit">
        <div class="lp-benefit-icon">✓</div>
        <h3>Regulated &amp; trusted</h3>
        <p>Clyde Housebuyers is ICO registered, HMRC AML supervised, and a member of the Property Redress Scheme (PRS).</p>
      </div>
    </div>
  </div>
</section>

<!-- ============== HOW IT WORKS ============== -->
<section class="lp-section lp-section-alt">
  <div class="lp-section-inner">
    <h2>How it works</h2>
    <p class="section-lede">Three simple steps. The whole thing takes a few days to go live on the portals.</p>

    <div class="lp-steps">
      <div class="lp-step">
        <div class="lp-step-num">1</div>
        <h3>Tell us about your property</h3>
        <p>Quick form above, or a short phone call. Address, a few basics about the property, and how to reach you.</p>
      </div>
      <div class="lp-step">
        <div class="lp-step-num">2</div>
        <h3>We brief our partner agent</h3>
        <p>Our local estate-agent partner takes your details and arranges a valuation visit at a time that suits you.</p>
      </div>
      <div class="lp-step">
        <div class="lp-step-num">3</div>
        <h3>Your home goes live</h3>
        <p>Listed on Rightmove, Zoopla and OnTheMarket. Viewings handled for you. You choose whether to accept any offer.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============== WHO WE HELP (PAIN POINTS) ============== -->
<section class="lp-section">
  <div class="lp-section-inner">
    <h2>Who we help</h2>
    <p class="section-lede">If any of these describe your situation, our free listing service is built for you:</p>

    <div class="lp-pain-grid">
      <div class="lp-pain-item"><span>🏠</span> Inherited a property</div>
      <div class="lp-pain-item"><span>👥</span> Tired landlord</div>
      <div class="lp-pain-item"><span>📦</span> Relocating for work</div>
      <div class="lp-pain-item"><span>🛠</span> Home needs work</div>
      <div class="lp-pain-item"><span>💔</span> Separation or divorce</div>
      <div class="lp-pain-item"><span>🏚</span> Empty / vacant home</div>
      <div class="lp-pain-item"><span>⏬</span> Downsizing</div>
      <div class="lp-pain-item"><span>⚠️</span> Mortgage arrears</div>
      <div class="lp-pain-item"><span>💷</span> Just want maximum value</div>
    </div>
  </div>
</section>

<!-- ============== FAQ ============== -->
<section class="lp-section lp-section-alt">
  <div class="lp-section-inner">
    <h2>Common questions</h2>
    <p class="section-lede">Most sellers want to know the same things. Here's the honest answer to each.</p>

    <div class="lp-faqs">
      <details>
        <summary>Is there really no cost to me?</summary>
        <p>Yes — no fees come out of your sale. You receive the full agreed sale price at completion. You pay nothing to list, nothing for advertising, and nothing if the property doesn't sell. The partner estate agent earns a separate buyer's-premium fee from the buyer (typically a property investor), agreed and signed in writing by the buyer before they make their offer.</p>
      </details>
      <details>
        <summary>How is this different from a normal estate agent?</summary>
        <p>Your property is listed by a fully regulated estate agent on the same portals (Rightmove, Zoopla, OnTheMarket). The difference is who pays the agent's fee: a high-street agent typically charges the seller a percentage of the sale price (often 1–2%). Our partner agent specialises in investor buyers and charges a buyer's-premium fee to the buyer instead, so no fee comes out of your sale proceeds.</p>
      </details>
      <details>
        <summary>Am I tied in to anything?</summary>
        <p>Before you sign, there's no obligation — decide it's not for you and walk away at no cost. Once you sign, the listing agreement includes a standard 3-month minimum marketing term, comparable to most high-street agents. There's no separate penalty fee for leaving early, but because the Home Report and marketing are provided at no upfront cost to you, the agent would look to recover those costs if you cancelled before the term is up.</p>
      </details>
      <details>
        <summary>How quickly does the property go live?</summary>
        <p>Once we have your details and the valuation visit is done, we aim to have your listing live across all three portals within a few working days.</p>
      </details>
      <details>
        <summary>Who actually buys my home?</summary>
        <p>Open-market buyers see the listing on Rightmove, Zoopla and OnTheMarket like any other property. Our partner agent specialises in investor buyers, so the property is also marketed to their network of investors looking for properties in the area. You choose which offer to accept.</p>
      </details>
      <details>
        <summary>So how does the partner agent get paid?</summary>
        <p>The partner agent represents property investors looking to buy. They charge their investor-buyers a separate buyer's-premium fee — added to the property purchase price the buyer pays, but not deducted from the price you (the seller) agree. The buyer signs a written buyer's-premium agreement before making any offer, so the fee is fully disclosed and agreed before the transaction. From your side as a seller: you receive the full agreed sale price, with no agent fee taken from your proceeds.</p>
      </details>
      <details>
        <summary>What if I also want a cash offer?</summary>
        <p>We can do that too. Clyde Housebuyers is also a cash buyer — if a free listing isn't what you want, we'll talk you through our cash-purchase route as an alternative. <a href="/" target="_blank">See our other selling routes</a>.</p>
      </details>
      <details>
        <summary>Are you regulated?</summary>
        <p>Yes. Clyde Housebuyers is a trading name of PropGain UK Limited (registered in England &amp; Wales, company number 16913648). We're ICO registered (ZC071824), HMRC AML supervised, and a member of the Property Redress Scheme (PRS056317). The partner agent is independently regulated as a UK estate agent.</p>
      </details>
    </div>
  </div>
</section>

<!-- ============== FINAL CTA ============== -->
<section class="lp-final">
  <div class="lp-section-inner">
    <h2>Ready to get your home in front of more buyers — with no fees from your sale?</h2>
    <p class="section-lede">Tell us about your property and we'll be in touch within one working day. No obligation.</p>

    <div class="lp-final-form-wrap">
      <div class="lp-form-card" style="text-align:left;">
        <form action="/submit-lead.php" method="POST" novalidate data-funnel-form>
          <input type="text" name="website" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off">
          <input type="hidden" name="postcode" value="N/A-FUNNEL">
          <input type="hidden" name="form_started" value="<?= time() ?>">
          <input type="hidden" name="contact_preference" value="Either">
          <input type="hidden" name="situation" value="Lead from /funnels/free-listing.php (final CTA)">

          <label for="lf-name-2">Your name</label>
          <input id="lf-name-2" name="first_name" type="text" placeholder="First name" required>

          <label for="lf-phone-2">Phone number</label>
          <input id="lf-phone-2" name="phone" type="tel" placeholder="Mobile preferred" required>

          <button type="submit" class="lp-cta">Get My Free Listing →</button>
          <p class="lp-form-note">No fees from your sale. No obligation. Your details go to us and our partner estate agent — nowhere else. <a href="/privacy.php" target="_blank">Privacy</a></p>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ============== MINIMAL FOOTER ============== -->
<footer class="lp-footer">
  Clyde Housebuyers · 0141 530 1430 · info@clydehousebuyers.co.uk<br>
  Trading name of PropGain UK Limited (England &amp; Wales, company no. 16913648).
  Registered office: 20 Wenlock Road, London N1 7GU. Correspondence: 48 W George Street, Glasgow G2 1BP.<br>
  ICO ZC071824 · HMRC AML XNML00000217270 · PRS PRS056317 ·
  <a href="/privacy.php">Privacy</a> · <a href="/terms.php">Terms</a> · <a href="/cookies.php">Cookies</a>
</footer>

</div><!-- /.lp-page -->

<script>
  // Smooth-scroll the sticky mobile CTA to the hero form
  document.querySelectorAll('a[href="#lead-form"]').forEach(a => {
    a.addEventListener('click', e => {
      const target = document.getElementById('lead-form');
      if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const firstInput = target.querySelector('input:not([type=hidden])');
        if (firstInput) setTimeout(() => firstInput.focus(), 400);
      }
    });
  });

  // ---- Hide the sticky CTA when ANY form card is in view ---------------
  // The sticky's only purpose is to push visitors TO the form. When they're
  // already AT the form, two near-identical gold CTAs (the form's submit and
  // the sticky bar) stack on screen and cause accidental taps that scroll
  // back to the same form. Hide the bar whenever a form card is visible.
  (function () {
    const sticky = document.querySelector('.lp-sticky-cta');
    const formCards = document.querySelectorAll('.lp-form-card');
    if (!sticky || !formCards.length || !('IntersectionObserver' in window)) return;

    const visible = new Set();
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) visible.add(entry.target);
        else visible.delete(entry.target);
      });
      sticky.classList.toggle('is-hidden', visible.size > 0);
    }, { threshold: 0.15 });  // 15% visible counts as "in view"

    formCards.forEach(card => io.observe(card));
  })();

  // ---- Intercept form submits: fetch() + inline success state ----------
  // Without this, the native form POST navigates the browser to
  // /submit-lead.php which returns raw JSON like {"ok":true,...}. Users
  // see that and think the form is broken (it isn't — the lead IS saved
  // — but the UX makes them think it failed). This handler:
  //  1. Intercepts the submit, posts via fetch
  //  2. On success: replaces the form with an inline thank-you message
  //  3. On failure: re-enables the submit button + shows an error with phone
  document.querySelectorAll('form[data-funnel-form]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();

      // Minimal client-side guard; server is the real validator
      const first = (form.querySelector('[name="first_name"]') || {}).value || '';
      const phone = (form.querySelector('[name="phone"]') || {}).value || '';
      if (!first.trim() || !phone.trim()) return;

      const btn = form.querySelector('[type="submit"]');
      const originalLabel = btn ? btn.textContent : '';
      if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }

      const data = new FormData(form);
      fetch('/submit-lead.php', { method: 'POST', body: data })
        .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
        .then(function (res) {
          if (res && res.ok) {
            // Replace the form with an inline success state.
            // Wrapping div is .lp-form-card so we replace its inner contents.
            const card = form.closest('.lp-form-card');
            if (card) {
              const name = (res.firstName || first || '').replace(/[<>]/g, '');
              card.innerHTML =
                '<div class="lp-success">' +
                  '<div class="check">✓</div>' +
                  '<h3>Thanks' + (name ? ', ' + name : '') + '!</h3>' +
                  '<p>We\'ve received your details and one of our team will be in touch within one working day.</p>' +
                  '<p>Please keep an eye on your phone and email — we\'ll come from <strong>0141 530 1430</strong> or <strong>info@clydehousebuyers.co.uk</strong>.</p>' +
                  '<p class="phone-line">Need to speak to us sooner? Call <a href="tel:01415301430">0141 530 1430</a>.</p>' +
                '</div>';
              // Scroll the success message into view so the user sees it
              card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
          } else {
            if (btn) { btn.disabled = false; btn.textContent = originalLabel; }
            // Surface the server's specific error if it sent one (rate-limit,
            // validation errors, etc.) — these messages are user-friendly and
            // tell the seller exactly what to do. Fall back to a generic
            // message only if no specific error was provided.
            const msg = (res && res.error)
              ? res.error
              : 'Sorry — we couldn\'t submit your details right now. Please try again, or call us on 0141 530 1430.';
            alert(msg);
          }
        })
        .catch(function () {
          if (btn) { btn.disabled = false; btn.textContent = originalLabel; }
          alert('Sorry — we couldn\'t submit your details right now. Please try again, or call us on 0141 530 1430.');
        });
    });
  });
</script>

</body>
</html>
