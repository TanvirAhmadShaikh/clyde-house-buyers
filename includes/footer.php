<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <img src="/assets/img/logo.png" alt="Clyde Housebuyers">
        <p>Glasgow-based house buyers serving sellers across Scotland's Central Belt. Cash purchase, assisted sale, and tailored property solutions — without fees, pressure, or chains.</p>
      </div>
      <div>
        <h4>Sellers</h4>
        <ul>
          <li><a href="/sellers/sell-house-fast-glasgow.php">Glasgow</a></li>
          <li><a href="/sellers/sell-house-fast-paisley.php">Paisley</a></li>
          <li><a href="/sellers/sell-house-fast-east-kilbride.php">East Kilbride</a></li>
          <li><a href="/sellers/sell-house-fast-hamilton.php">Hamilton</a></li>
          <li><a href="/sellers/sell-house-fast-motherwell.php">Motherwell</a></li>
        </ul>
      </div>
      <div>
        <h4>Solutions</h4>
        <ul>
          <li><a href="/solutions/cash-purchase.php">Cash purchase</a></li>
          <li><a href="/solutions/assisted-sale.php">Assisted sale</a></li>
          <li><a href="/solutions/sell-with-tenants-in-situ.php">Tenanted sale</a></li>
          <li><a href="/solutions/brokered-sale.php">Brokered sale</a></li>
          <li><a href="/solutions/joint-venture.php">Joint venture</a></li>
          <li><a href="/solutions/open-market-sale.php">Open market sale</a></li>
        </ul>
      </div>
      <div>
        <h4>Company</h4>
        <ul>
          <li><a href="/about.php">About us</a></li>
          <li><a href="/case-studies.php">Worked examples</a></li>
          <li><a href="/how-it-works.php">How it works</a></li>
          <li><a href="/faq.php">FAQ</a></li>
          <li><a href="/contact.php">Contact</a></li>
          <li><a href="/free-valuation.php">Free valuation</a></li>
        </ul>
      </div>
    </div>

    <hr>

    <div style="display:grid; grid-template-columns: 1fr; gap:1rem; margin-bottom: 1.5rem;">
      <div style="font-size:0.88rem; color: rgba(255,255,255,0.72);">
        <strong style="color: var(--ch-white);">Clyde Housebuyers</strong> — <a href="tel:01415301430" style="color:var(--ch-gold-light);">0141 530 1430</a> · <a href="mailto:info@clydehousebuyers.co.uk" style="color:var(--ch-gold-light);">info@clydehousebuyers.co.uk</a><br>
        Correspondence: 48 W George St, Glasgow G2 1BP<br>
        <span class="small">Trading name of PropGain UK Limited, registered in England &amp; Wales (company no. 16913648). Registered office: 20 Wenlock Road, London N1 7GU.</span>
      </div>
    </div>

    <div class="footer-regulators">
      <div class="footer-reg-badges">
        <span class="footer-reg"><span class="trust-tick" aria-hidden="true">✓</span> ICO Registered</span>
        <span class="footer-reg"><span class="trust-tick" aria-hidden="true">✓</span> HMRC AML Supervised</span>
        <span class="footer-reg"><span class="trust-tick" aria-hidden="true">✓</span> PRS Member</span>
      </div>
      <div class="footer-reg-refs">
        <span>ICO: ZC071824</span>
        <span>HMRC AML: XNML00000217270</span>
        <span>PRS: PRS056317</span>
      </div>
    </div>
    </div>

    <hr>

    <div class="footer-bottom">
      <span>© <?= date('Y') ?> Clyde Housebuyers. All rights reserved.</span>
      <span>
        <a href="/privacy.php">Privacy</a> ·
        <a href="/terms.php">Terms</a> ·
        <a href="/cookies.php">Cookies</a>
      </span>
    </div>
  </div>
</footer>

<?php
// Suppress the sticky mobile CTA on pages where it would either:
//  (a) link to itself (creating an infinite loop — e.g. on /free-valuation.php),
//  (b) overlap a form's own Continue/Submit button mid-flow, or
//  (c) compete with the page's own dedicated conversion CTA (funnels).
// Pages that need this set $hide_mobile_cta = true before including head.php.
if (empty($hide_mobile_cta)): ?>
<div class="mobile-cta" aria-label="Quick actions">
  <a href="tel:01415301430" class="call">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
    Call us
  </a>
  <a href="/free-valuation.php" class="cta">Free valuation →</a>
</div>
<style>body.no-mobile-cta-padding { padding-bottom: 0 !important; }</style>
<?php else: ?>
<style>
/* Sticky mobile CTA is suppressed on this page — reclaim the bottom padding
   the global rule reserves for it, so the page footer/buttons aren't pushed
   up unnecessarily on mobile. */
@media (max-width: 991px) { body { padding-bottom: 0 !important; } }
</style>
<?php endif; ?>

<script src="/assets/js/main.js" defer></script>

<!-- Cookie consent banner (PECR/UK GDPR). Gates Google Analytics via Consent Mode v2. -->
<div id="chb-cookie-banner" role="dialog" aria-live="polite" aria-label="Cookie consent" hidden>
  <div class="chb-cc-inner">
    <p class="chb-cc-text">
      We use essential cookies to make this site work. With your consent, we'd also like to use
      Google Analytics cookies to understand how visitors use the site so we can improve it.
      See our <a href="/cookies.php">Cookie Policy</a> for details.
    </p>
    <div class="chb-cc-buttons">
      <button type="button" id="chb-cc-reject" class="chb-cc-btn chb-cc-btn-secondary">Reject</button>
      <button type="button" id="chb-cc-accept" class="chb-cc-btn chb-cc-btn-primary">Accept</button>
    </div>
  </div>
</div>

<style>
  #chb-cookie-banner {
    position: fixed; left: 1rem; right: 1rem; bottom: 1rem; z-index: 9999;
    max-width: 760px; margin: 0 auto;
    background: #0B1F3B; color: #F7F9FC;
    border: 1px solid #C8A24A; border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.35);
    padding: 1.1rem 1.25rem;
    font-size: 0.92rem; line-height: 1.5;
  }
  #chb-cookie-banner .chb-cc-inner { display: flex; flex-direction: column; gap: 0.9rem; }
  #chb-cookie-banner .chb-cc-text { margin: 0; color: #cdd4dc; }
  #chb-cookie-banner .chb-cc-text a { color: #d6b56b; text-decoration: underline; }
  #chb-cookie-banner .chb-cc-buttons { display: flex; gap: 0.6rem; justify-content: flex-end; flex-wrap: wrap; }
  #chb-cookie-banner .chb-cc-btn {
    font: inherit; font-weight: 600; cursor: pointer;
    padding: 0.55rem 1.4rem; border-radius: 8px; border: 1.5px solid transparent;
  }
  /* Reject is given equal visual weight to Accept (ICO requirement) */
  #chb-cookie-banner .chb-cc-btn-secondary { background: transparent; color: #F7F9FC; border-color: #A7B0BD; }
  #chb-cookie-banner .chb-cc-btn-secondary:hover { border-color: #F7F9FC; }
  #chb-cookie-banner .chb-cc-btn-primary { background: #C8A24A; color: #0B1F3B; border-color: #C8A24A; }
  #chb-cookie-banner .chb-cc-btn-primary:hover { background: #d6b56b; border-color: #d6b56b; }
  @media (min-width: 560px) {
    #chb-cookie-banner .chb-cc-inner { flex-direction: row; align-items: center; }
    #chb-cookie-banner .chb-cc-text { flex: 1; }
    #chb-cookie-banner .chb-cc-buttons { flex-shrink: 0; }
  }
</style>

<script>
(function () {
  var KEY = 'chb_cookie_consent';
  var banner = document.getElementById('chb-cookie-banner');
  if (!banner) return;

  function consent(state) {
    // state: 'granted' or 'denied'
    if (typeof gtag === 'function') {
      gtag('consent', 'update', { 'analytics_storage': state });
    }
  }

  var stored = null;
  try { stored = localStorage.getItem(KEY); } catch (e) {}

  // Only show the banner if no choice has been made yet.
  if (stored !== 'accepted' && stored !== 'rejected') {
    banner.hidden = false;
  }

  document.getElementById('chb-cc-accept').addEventListener('click', function () {
    try { localStorage.setItem(KEY, 'accepted'); } catch (e) {}
    consent('granted');
    banner.hidden = true;
  });

  document.getElementById('chb-cc-reject').addEventListener('click', function () {
    try { localStorage.setItem(KEY, 'rejected'); } catch (e) {}
    consent('denied');
    banner.hidden = true;
  });
})();
</script>
</body>
</html>
