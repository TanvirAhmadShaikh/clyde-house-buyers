<?php
/**
 * funnels/free-listing-article.php — Content-first landing page.
 *
 * Built for the "Breaking News" style Facebook ad creative with a "Read More"
 * or "Learn More" CTA. The visitor's expectation when they click that kind
 * of ad is "show me an article that explains how this works" — NOT a form,
 * NOT a quiz.
 *
 * This page answers the implicit promise of the ad: the "How is it free?"
 * speech bubble in the creative is the question the page opens by answering.
 * The conversion ask is deliberately at the BOTTOM — soft, after the reader
 * has decided for themselves that the offer is real and right for them.
 *
 * Conversion path:
 *   Read article → understand offer → sticky CTA or end-of-article CTA
 *     → /funnels/free-listing-quiz.php (the 5-step quiz, which captures
 *       qualified leads at the end)
 *
 * Compliance notes (all matter):
 *  - Page is clearly identified as Clyde Housebuyers content (logo top of
 *    page, footer disclosure). NOT dressed as third-party journalism — the
 *    CAP Code (section 2.4) prohibits commercial content pretending to be
 *    editorial. The "Breaking News" aesthetic stays in the ad; here we just
 *    use a clean, information-dense editorial layout.
 *  - Buyer's-premium model explained honestly (matches the wording we
 *    settled on earlier this session: full sale price kept, partner agent
 *    paid by buyer via separate signed premium agreement).
 *  - Trade-offs section names who this ISN'T for — builds trust and filters
 *    wrong-fit leads.
 *  - noindex (paid-traffic page, kept out of organic search to avoid
 *    duplicate-content competition with the main site's open-market-sale
 *    page).
 */
$page_title       = "How Glasgow homeowners list on Rightmove with no agent fee | Clyde Housebuyers";
$page_description = "We explain how Glasgow homeowners are listing on Rightmove, Zoopla and OnTheMarket without paying any agent fee, and what the trade-offs are. Plain English. No sales pitch.";
$canonical        = "https://clydehousebuyers.co.uk/funnels/free-listing-article.php";
$noindex          = true;
include __DIR__ . '/../includes/head.php';
?>
<body>
<style>
/* ============================================================================
   funnels/free-listing-article.php — scoped styles.
   Editorial layout: long-form readable, narrow column, generous whitespace.
   Same brand palette as the rest of the site, no fake-news dressing.
   ============================================================================ */
.fa { color: var(--ch-navy); background: #fff; line-height: 1.65; }
.fa a { color: var(--ch-gold-dark); }

/* Minimal sticky header */
.fa-header {
  position: sticky; top: 0; z-index: 50;
  background: #fff; border-bottom: 1px solid #e8ecf2;
  padding: 0.6rem 0;
}
.fa-header-inner {
  display: flex; align-items: center; justify-content: space-between;
  max-width: 760px; margin: 0 auto; padding: 0 1.25rem; gap: 1rem;
}
.fa-header img { height: 40px; width: auto; }
.fa-header-phone {
  font-weight: 700; color: var(--ch-navy); text-decoration: none;
  font-size: 0.98rem; white-space: nowrap;
}
.fa-header-phone::before { content: "📞 "; }

/* Article container — narrow column, typographic emphasis */
.fa-article { max-width: 720px; margin: 0 auto; padding: 0 1.25rem; }

/* Hero — editorial style, full-bleed warm backdrop matching the rest of the site.
   Lives OUTSIDE .fa-article so the cream background can span the full viewport
   width without 100vw scrollbar tricks. */
.fa-hero-wrap {
  background: linear-gradient(135deg, var(--ch-gold-tint) 0%, #fdf9ea 100%);
  border-bottom: 1px solid #e8ecf2;
}
.fa-hero {
  max-width: 720px; margin: 0 auto;
  padding: clamp(2rem, 5vw, 3rem) 1.25rem clamp(1.5rem, 4vw, 2.5rem);
}
.fa-eyebrow {
  font-size: 0.78rem; letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--ch-gold-dark); font-weight: 700;
  margin-bottom: 0.8rem;
  display: inline-flex; align-items: center; gap: 0.6rem;
}
.fa-eyebrow::before {
  content: ""; display: inline-block;
  width: 28px; height: 3px; background: var(--ch-gold);
}
.fa-h1 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(1.85rem, 4.8vw, 2.6rem);
  line-height: 1.15; font-weight: 700; color: var(--ch-navy);
  margin: 0 0 0.85rem; letter-spacing: -0.01em;
}
.fa-deck {
  font-size: clamp(1.05rem, 2vw, 1.15rem);
  color: var(--ch-navy-80); line-height: 1.55;
  margin: 0 0 1.25rem;
}
.fa-meta {
  font-size: 0.85rem; color: var(--ch-navy-60);
  display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;
}
.fa-meta strong { color: var(--ch-navy); font-weight: 600; }

/* Body — long-form reading */
.fa-body {
  padding: clamp(1.5rem, 4vw, 2.5rem) 0;
  font-size: 1.05rem;
}
.fa-body p { margin: 0 0 1.15rem; }
.fa-body p.lede {
  font-size: 1.18rem; color: var(--ch-navy);
  margin-bottom: 1.75rem; font-weight: 500;
  border-left: 3px solid var(--ch-gold); padding-left: 1rem;
}

.fa-h2 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(1.35rem, 3vw, 1.65rem);
  color: var(--ch-navy); font-weight: 600;
  margin: 2.5rem 0 0.9rem; line-height: 1.25;
  letter-spacing: -0.01em;
}

/* Numbered steps — for the "how it works" section.
   Step circles use the navy/gold combo from the rest of the site
   (cards section on the homepage uses the same pattern). */
.fa-steps {
  counter-reset: stepnum; margin: 1.25rem 0 1.5rem;
  background: var(--ch-gold-tint);
  border-radius: 12px; padding: 0.5rem 1.25rem;
}
.fa-step {
  display: flex; gap: 1rem; align-items: flex-start;
  padding: 1rem 0;
  border-bottom: 1px solid rgba(168, 134, 47, 0.18);
}
.fa-step:last-child { border-bottom: none; }
.fa-step::before {
  counter-increment: stepnum;
  content: counter(stepnum);
  flex: 0 0 36px; height: 36px; border-radius: 50%;
  background: var(--ch-navy); color: var(--ch-gold);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.95rem;
  border: 2px solid var(--ch-gold);
}
.fa-step-body { flex: 1; }
.fa-step-body h3 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: 1.1rem; margin: 0 0 0.3rem; color: var(--ch-navy); font-weight: 600;
}
.fa-step-body p { margin: 0; font-size: 0.98rem; color: var(--ch-navy-80); }

/* Pull-quote — used sparingly for emphasis */
.fa-pull {
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(1.2rem, 2.4vw, 1.4rem);
  font-style: italic; color: var(--ch-navy);
  background: var(--ch-gold-tint);
  border-left: 4px solid var(--ch-gold);
  padding: 1.25rem 1.5rem;
  margin: 1.75rem 0; line-height: 1.4;
  border-radius: 0 8px 8px 0;
}

/* Honest "who this is and isn't for" — two-column on desktop */
.fa-fit { margin: 1.25rem 0 1.5rem; display: grid; grid-template-columns: 1fr; gap: 1rem; }
@media (min-width: 640px) { .fa-fit { grid-template-columns: 1fr 1fr; } }
.fa-fit-card {
  border-radius: 10px; padding: 1.25rem 1.4rem;
}
.fa-fit-card h3 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: 1.05rem; margin: 0 0 0.6rem; color: var(--ch-navy);
  display: flex; align-items: center; gap: 0.5rem;
}
.fa-fit-card h3::before {
  display: inline-flex; align-items: center; justify-content: center;
  width: 24px; height: 24px; border-radius: 50%;
  font-size: 0.85rem; font-weight: 700;
  flex: 0 0 24px;
}
.fa-fit-card.good {
  background: var(--ch-gold-tint);
  border: 1px solid rgba(168, 134, 47, 0.25);
}
.fa-fit-card.good h3::before {
  content: "✓"; background: var(--ch-gold); color: var(--ch-navy);
}
.fa-fit-card.bad {
  background: #fef4f3;
  border: 1px solid rgba(185, 28, 28, 0.18);
}
.fa-fit-card.bad h3::before {
  content: "×"; background: #b91c1c; color: #fff;
}
.fa-fit-card ul { margin: 0; padding-left: 1.1rem; font-size: 0.95rem; color: var(--ch-navy-80); }
.fa-fit-card li { margin: 0.35rem 0; }

/* Credentials strip — discreet, factual */
.fa-creds {
  background: var(--ch-gold-tint);
  border-left: 3px solid var(--ch-gold);
  border-radius: 0 10px 10px 0;
  padding: 1.15rem 1.4rem; margin: 1.5rem 0;
  font-size: 0.92rem; color: var(--ch-navy);
  line-height: 1.55;
}
.fa-creds strong { display: block; margin-bottom: 0.4rem; font-size: 1rem; color: var(--ch-navy); }

/* FAQ */
.fa-faqs { margin: 1.5rem 0; }
.fa-faqs details {
  border-top: 1px solid #e8ecf2; padding: 0;
}
.fa-faqs details:last-of-type { border-bottom: 1px solid #e8ecf2; }
.fa-faqs summary {
  cursor: pointer; padding: 1rem 0;
  font-weight: 600; color: var(--ch-navy); list-style: none;
  position: relative; padding-right: 2.5rem;
  font-size: 1rem;
}
.fa-faqs summary::-webkit-details-marker { display: none; }
.fa-faqs summary::after {
  content: "+"; position: absolute; right: 0; top: 50%;
  transform: translateY(-50%); font-size: 1.5rem; color: var(--ch-gold-dark);
  font-weight: 300; line-height: 1;
}
.fa-faqs details[open] summary::after { content: "−"; }
.fa-faqs details p {
  padding: 0 0 1.25rem; margin: 0;
  color: var(--ch-navy-80); line-height: 1.65;
}

/* End-of-article CTA — the actual conversion ask */
.fa-end-cta {
  background: linear-gradient(135deg, #0B1F3B 0%, #091a32 100%);
  color: #fff; padding: clamp(2rem, 5vw, 3rem) 1.5rem;
  border-radius: 14px; text-align: center;
  margin: 2rem 0 3rem;
}
.fa-end-cta h2 {
  font-family: 'Fraunces', Georgia, serif;
  font-size: clamp(1.4rem, 3vw, 1.8rem);
  margin: 0 0 0.5rem; color: #fff; line-height: 1.25;
}
.fa-end-cta p { color: #cdd4dc; margin: 0 0 1.25rem; font-size: 1rem; line-height: 1.5; }
.fa-end-cta-btn {
  display: inline-block; background: var(--ch-gold);
  color: var(--ch-navy) !important; font-weight: 700; font-size: 1.05rem;
  padding: 0.95rem 1.75rem; border-radius: 10px;
  text-decoration: none !important;
  box-shadow: 0 6px 18px rgba(200,162,74,0.45);
  transition: background 0.15s, transform 0.05s;
  min-height: 52px; line-height: 1.3;
}
.fa-end-cta-btn:hover, .fa-end-cta-btn:focus, .fa-end-cta-btn:visited {
  color: var(--ch-navy) !important; text-decoration: none !important;
}
.fa-end-cta-btn:hover { background: #d6b56b; }
.fa-end-cta-btn:active { transform: translateY(1px); }
.fa-end-cta-note { font-size: 0.85rem; color: #a3acb8; margin-top: 0.9rem; }

/* Sticky mobile CTA — same pattern as the other funnels */
.fa-sticky {
  display: none;
  position: fixed; left: 0; right: 0; bottom: 0; z-index: 100;
  background: #0B1F3B; padding: 0.75rem 1rem;
  box-shadow: 0 -4px 16px rgba(0,0,0,0.2);
  border-top: 2px solid var(--ch-gold);
  transition: transform 0.25s ease-out, opacity 0.25s ease-out;
}
.fa-sticky.is-hidden { transform: translateY(110%); opacity: 0; pointer-events: none; }
.fa-sticky a {
  display: block; background: var(--ch-gold); color: var(--ch-navy) !important;
  text-align: center; font-weight: 700; font-size: 1.02rem;
  padding: 0.85rem; border-radius: 8px; text-decoration: none !important;
}
.fa-sticky a:hover, .fa-sticky a:visited { color: var(--ch-navy) !important; text-decoration: none !important; }
@media (max-width: 899px) {
  .fa-sticky { display: block; }
  body { padding-bottom: 70px; }
}

/* Minimal footer */
.fa-footer {
  background: #f7f9fc; color: var(--ch-navy-60);
  padding: 1.5rem 1.25rem; font-size: 0.78rem;
  line-height: 1.6; text-align: center;
  border-top: 1px solid #e8ecf2;
}
.fa-footer a { color: var(--ch-navy-80); }
</style>

<div class="fa">

<!-- Sticky mobile CTA -->
<div class="fa-sticky"><a href="#end-cta">See what this looks like for your property →</a></div>

<!-- Minimal header — clear brand identification (compliance: not pretending to be journalism) -->
<header class="fa-header">
  <div class="fa-header-inner">
    <a href="/" style="line-height:0;"><img src="/assets/img/logo.png" alt="Clyde Housebuyers"></a>
    <a href="tel:01415301430" class="fa-header-phone">0141 530 1430</a>
  </div>
</header>

<!-- ===== HERO (full-bleed warm backdrop) ===== -->
<div class="fa-hero-wrap">
  <header class="fa-hero">
    <div class="fa-eyebrow">Glasgow property · How it works</div>
    <h1 class="fa-h1">How Glasgow homeowners are listing on Rightmove without paying an agent fee</h1>
    <p class="fa-deck">A growing number of sellers in Glasgow and Scotland's Central Belt are listing on Rightmove, Zoopla and OnTheMarket without paying any agent commission out of their sale price. Here's exactly how it works — and what the trade-offs are.</p>
    <div class="fa-meta">
      <span><strong>Clyde Housebuyers</strong></span>
      <span>·</span>
      <span>Glasgow &amp; Central Belt</span>
    </div>
  </header>
</div>

<article class="fa-article">

  <!-- ===== BODY ===== -->
  <section class="fa-body">

    <p class="lede">If you've seen claims of "free listings on Rightmove" and wondered where the catch is — there isn't one for you as the seller, but there is a mechanism that makes it possible. We'll walk through it plainly so you can decide whether it's a fit for your situation.</p>

    <h2 class="fa-h2">How is it actually free for the seller?</h2>
    <p>The short answer: the partner estate agent doing the listing is paid by the <em>buyer</em>, not the seller. It's a different business model from a typical high-street agent.</p>

    <p>A normal estate agent charges the seller a percentage of the sale price — often 1–2% — and that fee comes out of your proceeds at completion. So on a £200,000 sale you'd typically receive between £196,000 and £198,000 after agent commission, before solicitor fees.</p>

    <p>Our partner agent specialises in working with property investors looking to buy. Their fee — a buyer's premium — is paid by the investor-buyer, separately from the price paid to you. The buyer signs a written premium agreement before making any offer, so the fee structure is fully disclosed and agreed on the buyer's side before anything happens.</p>

    <blockquote class="fa-pull">From your side as a seller: you receive the full agreed sale price, with no agent fee taken from your proceeds.</blockquote>

    <h2 class="fa-h2">Here's exactly what happens, step by step</h2>
    <div class="fa-steps">
      <div class="fa-step">
        <div class="fa-step-body">
          <h3>You request a valuation</h3>
          <p>We arrange a no-obligation property visit at a time that suits you. The visit is free and there's no commitment to list afterwards.</p>
        </div>
      </div>
      <div class="fa-step">
        <div class="fa-step-body">
          <h3>You sign a written listing agreement</h3>
          <p>Before anything goes live, the partner agent gives you a written agreement that spells out exactly what they'll do, how the buyer's premium works, and that no fee comes out of your sale proceeds. You read it, ask questions, and sign — or walk away.</p>
        </div>
      </div>
      <div class="fa-step">
        <div class="fa-step-body">
          <h3>Your property goes live on the major portals</h3>
          <p>Listed on Rightmove, Zoopla and OnTheMarket — the same exposure a paid high-street agent listing would give you. The partner agent handles photos, viewings and negotiation.</p>
        </div>
      </div>
      <div class="fa-step">
        <div class="fa-step-body">
          <h3>An offer comes in — buyer signs the premium agreement</h3>
          <p>When a buyer is ready to offer, they sign a separate buyer's-premium agreement covering the partner agent's fee. The buyer is told about this upfront before making an offer. The price you agree with the buyer is yours in full.</p>
        </div>
      </div>
      <div class="fa-step">
        <div class="fa-step-body">
          <h3>Completion — you receive the full agreed price</h3>
          <p>Standard Scottish conveyancing through your own solicitor. At completion, you receive the full agreed sale price. The buyer pays their premium to the agent separately. No commission is deducted from your side.</p>
        </div>
      </div>
    </div>

    <h2 class="fa-h2">Why this exists</h2>
    <p>The partner agent operates as what they call "the estate agent for the investor". Their clients are property investors — people buying to rent out, refurbish and resell, or add to a portfolio. Those buyers value off-market and well-presented deals, and they're willing to pay a buyer's premium to access them.</p>
    <p>The model only works because the agent specialises. A high-street agent serving general buyers couldn't pull this off — typical retail buyers wouldn't accept a premium on top of the asking price. Investor-buyers operate by different economics: they're looking at yield and refurbishment margin, and the premium is the cost of finding the deal.</p>

    <h2 class="fa-h2">Who this is and isn't for</h2>
    <p>This works well for some sellers and not for others. Honestly:</p>
    <div class="fa-fit">
      <div class="fa-fit-card good">
        <h3>Probably a good fit if you…</h3>
        <ul>
          <li>Want to keep the full sale price (no fees from your proceeds)</li>
          <li>Want a proper Rightmove / Zoopla listing, not a private cash sale</li>
          <li>Don't want to pay anything upfront</li>
          <li>Are open to investor-buyers as well as owner-occupiers</li>
          <li>Have time to wait for the right offer (open market timing)</li>
        </ul>
      </div>
      <div class="fa-fit-card bad">
        <h3>Probably NOT the right fit if you…</h3>
        <ul>
          <li>Need cash in 14–28 days (a direct cash sale would suit you better)</li>
          <li>Want a guaranteed offer at a specific price upfront</li>
          <li>Your property is heavily distressed and won't pass a mortgage survey</li>
          <li>You're already mid-listing with another agent (existing contract issues)</li>
        </ul>
      </div>
    </div>
    <p>If a cash sale is more your situation — fast completion, certainty, any condition accepted — Clyde Housebuyers can buy direct as well. We'll be honest with you on the call about which route fits.</p>

    <h2 class="fa-h2">Is this legitimate?</h2>
    <p>Reasonable question, especially when something sounds different from the norm. The short answer is yes, and here's how to verify:</p>

    <div class="fa-creds">
      <strong>Clyde Housebuyers — Glasgow</strong>
      Trading name of PropGain UK Limited, registered in England &amp; Wales (Company no. 16913648).<br>
      ICO Registered (ZC071824) · HMRC AML Supervised (XNML00000217270) · Member of the Property Redress Scheme (PRS056317).<br>
      Our partner estate agent is a fully regulated UK estate agency with their own redress-scheme membership and AML supervision. All transactions go through regulated Scottish solicitors.
    </div>

    <p>Every part of the transaction is documented in writing: your listing agreement with the partner agent, the buyer's premium agreement signed by the buyer, the standard Scottish missives between solicitors. Nothing happens on a handshake.</p>

    <h2 class="fa-h2">What sellers ask us most</h2>
    <div class="fa-faqs">
      <details>
        <summary>What if my home doesn't sell?</summary>
        <p>You pay nothing. The partner agent only earns when there's a completed sale, so if it doesn't sell within your listing term, there are no advertising fees to pay. (If you decide to withdraw before the term is up, see the next question — different terms apply.)</p>
      </details>
      <details>
        <summary>Am I locked in for any period of time?</summary>
        <p>You'll see the partner agent's terms in the written listing agreement before signing. The standard minimum-marketing period is 12 weeks (3 months), comparable to high-street agents — you're not signing a multi-year tie-in. There's no separate penalty fee for leaving early, but because the Home Report and marketing are provided at no upfront cost to you, the agent would look to recover those costs if you cancelled before the term is up.</p>
      </details>
      <details>
        <summary>Does the buyer's premium mean buyers offer less?</summary>
        <p>This is the right question to ask. In practice: investor-buyers price the premium into their decision-making the same way they'd price in legal fees or refurbishment costs. The market for investor-grade property in Glasgow is active enough that this hasn't been a meaningful drag on agreed prices. That said, no model is perfect — if your property is more likely to attract an owner-occupier than an investor, an alternative route might net you more. We'll be honest about this on the valuation call.</p>
      </details>
      <details>
        <summary>How long does it take to go live?</summary>
        <p>Typically a few working days from the valuation visit to the listing being live on all three portals — photos taken, particulars written, descriptions approved by you.</p>
      </details>
      <details>
        <summary>Who values the property and how?</summary>
        <p>The partner agent does the valuation visit and recommends a listing price based on comparable sold properties in your immediate area (using Registers of Scotland data). You approve the price before listing. You're never pressured into a number you're not happy with.</p>
      </details>
      <details>
        <summary>What if I change my mind after signing?</summary>
        <p>The listing agreement will set out the agent's cancellation terms — read them before you sign. There's generally a cooling-off period under consumer regulations. Speak to the agent (or us) before signing if anything's unclear.</p>
      </details>
    </div>

    <!-- ===== END-OF-ARTICLE CTA ===== -->
    <div class="fa-end-cta" id="end-cta">
      <h2>See what this would look like for your property</h2>
      <p>Take our 60-second assessment. We'll ask a few quick questions about your property and situation, then call you back to talk through the options — including this route and the alternatives.</p>
      <a href="/funnels/free-listing-quiz.php#assessment" class="fa-end-cta-btn">Start My Free Assessment →</a>
      <p class="fa-end-cta-note">No obligation. No pushy follow-ups. Glasgow &amp; Scotland's Central Belt.</p>
    </div>

  </section>
</article>

<!-- Minimal footer -->
<footer class="fa-footer">
  Clyde Housebuyers · 0141 530 1430 · info@clydehousebuyers.co.uk<br>
  Trading name of PropGain UK Limited (England &amp; Wales, company no. 16913648).
  Registered office: 20 Wenlock Road, London N1 7GU.<br>
  ICO ZC071824 · HMRC AML XNML00000217270 · PRS PRS056317 ·
  <a href="/privacy.php">Privacy</a> · <a href="/terms.php">Terms</a> · <a href="/cookies.php">Cookies</a>
</footer>

</div><!-- /.fa -->

<script>
/* Hide the sticky CTA bar when the end-of-article CTA is in view.
   Same pattern as the other funnels — the sticky's job is to keep the
   "act on this" option visible while the reader scrolls; once they've
   reached the real CTA at the bottom, the sticky bar is redundant. */
(function () {
  var sticky = document.querySelector('.fa-sticky');
  var target = document.getElementById('end-cta');
  if (!sticky || !target || !('IntersectionObserver' in window)) return;
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      sticky.classList.toggle('is-hidden', entry.isIntersecting);
    });
  }, { threshold: 0.15 });
  io.observe(target);
})();

/* Smooth-scroll the sticky bar's anchor link to the end-of-article CTA. */
document.querySelectorAll('a[href="#end-cta"]').forEach(function (a) {
  a.addEventListener('click', function (e) {
    e.preventDefault();
    var target = document.getElementById('end-cta');
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'center' });
  });
});
</script>

</body>
</html>
