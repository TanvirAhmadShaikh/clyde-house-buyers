<?php
$page_title = "Free Property Valuation — Glasgow & Central Belt | Clyde Housebuyers";
$page_description = "Get a free, no-pressure valuation of your property within 24 hours. Tell us your situation and we'll propose the route that nets you the most — cash offer, assisted sale or tailored solution.";
$canonical = "https://clydehousebuyers.co.uk/free-valuation.php";
// Suppress the global sticky mobile CTA on this page — the bar links back to
// /free-valuation.php, so on this page it (a) loops back to question 1 and
// (b) visually overlaps the form's own Continue button on long question steps.
$hide_mobile_cta = true;
include 'includes/head.php';
include 'includes/header.php';
?>

<main id="main">

<section class="section">
  <div class="container-narrow">
    <div class="text-center mb-4">
      <span class="eyebrow">Free valuation</span>
      <h1 style="margin-bottom: 0.5rem;">Tell us about your property.</h1>
      <p class="muted">5 quick questions. Free valuation within 24 hours. No pressure, no fees, no chasing.</p>
    </div>

    <div class="form-card">
      <div class="form-progress" aria-hidden="true">
        <span class="active"></span><span></span><span></span><span></span><span></span>
      </div>

      <form id="lead-form" action="/submit-lead.php" method="POST" novalidate>
        <input type="text" name="website" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off" aria-hidden="true">
        <input type="hidden" name="form_started" value="0">

        <!-- Step 1 — Postcode -->
        <div class="form-step active">
          <h2>What's the property's postcode?</h2>
          <p class="form-help">We cover Glasgow and all of Scotland's Central Belt.</p>
          <label class="form-label" for="postcode">Postcode</label>
          <input type="text" id="postcode" name="postcode" class="form-input" data-required="true" data-validate="postcode" placeholder="e.g. G42 8AA" autocomplete="postal-code">
          <div class="form-error" data-for="postcode"></div>
          <div class="form-actions">
            <button type="button" class="btn btn-primary btn-block" data-action="next">Continue →</button>
          </div>
        </div>

        <!-- Step 2 — Property type -->
        <div class="form-step">
          <h2>What kind of property is it?</h2>
          <p class="form-help">Pick the closest match.</p>
          <div class="form-radio-group">
            <label class="form-option"><input type="radio" name="property_type" value="Flat" data-required="true"><span>Flat</span></label>
            <label class="form-option"><input type="radio" name="property_type" value="Terraced" data-required="true"><span>Terraced</span></label>
            <label class="form-option"><input type="radio" name="property_type" value="Semi-detached" data-required="true"><span>Semi-detached</span></label>
            <label class="form-option"><input type="radio" name="property_type" value="Detached" data-required="true"><span>Detached</span></label>
            <label class="form-option"><input type="radio" name="property_type" value="Bungalow" data-required="true"><span>Bungalow</span></label>
            <label class="form-option"><input type="radio" name="property_type" value="Other" data-required="true"><span>Other / not sure</span></label>
          </div>
          <div class="form-error" data-for="property_type"></div>
          <div class="form-actions">
            <button type="button" class="btn btn-ghost" data-action="prev">← Back</button>
            <button type="button" class="btn btn-primary" data-action="next">Continue →</button>
          </div>
        </div>

        <!-- Step 3 — Condition -->
        <div class="form-step">
          <h2>What sort of condition is it in?</h2>
          <p class="form-help">A rough guide is fine — we'll work out the details on the call.</p>
          <div class="form-radio-group">
            <label class="form-option"><input type="radio" name="condition" value="Move-in ready" data-required="true"><span>Move-in ready</span></label>
            <label class="form-option"><input type="radio" name="condition" value="Needs cosmetic work" data-required="true"><span>Needs cosmetic work</span></label>
            <label class="form-option"><input type="radio" name="condition" value="Needs major work" data-required="true"><span>Needs major work</span></label>
            <label class="form-option"><input type="radio" name="condition" value="Uninhabitable / derelict" data-required="true"><span>Uninhabitable / derelict</span></label>
          </div>
          <div class="form-error" data-for="condition"></div>
          <div class="form-actions">
            <button type="button" class="btn btn-ghost" data-action="prev">← Back</button>
            <button type="button" class="btn btn-primary" data-action="next">Continue →</button>
          </div>
        </div>

        <!-- Step 4 — Situation -->
        <div class="form-step">
          <h2>What's your situation?</h2>
          <p class="form-help">Pick anything that applies, or tell us more in a sentence.</p>
          <div class="form-check-group">
            <label class="form-option"><input type="checkbox" name="situation[]" value="Just want a quick sale"><span>Just want a quick sale</span></label>
            <label class="form-option"><input type="checkbox" name="situation[]" value="Inherited the property"><span>Inherited the property</span></label>
            <label class="form-option"><input type="checkbox" name="situation[]" value="Going through divorce"><span>Going through divorce</span></label>
            <label class="form-option"><input type="checkbox" name="situation[]" value="Financial pressure / behind on mortgage"><span>Financial pressure / behind on mortgage</span></label>
            <label class="form-option"><input type="checkbox" name="situation[]" value="Tenants in situ"><span>Tenants in situ</span></label>
            <label class="form-option"><input type="checkbox" name="situation[]" value="Property needs work I can't afford"><span>Property needs work I can't afford</span></label>
            <label class="form-option"><input type="checkbox" name="situation[]" value="Non-standard construction"><span>Non-standard construction</span></label>
            <label class="form-option"><input type="checkbox" name="situation[]" value="Landlord exiting"><span>Landlord exiting</span></label>
          </div>
          <label class="form-label mt-2" for="notes">Anything else we should know?</label>
          <textarea id="notes" name="notes" class="form-textarea" placeholder="Optional — anything about your property or situation that would help us call you back well-prepared."></textarea>
          <div class="form-actions">
            <button type="button" class="btn btn-ghost" data-action="prev">← Back</button>
            <button type="button" class="btn btn-primary" data-action="next">Continue →</button>
          </div>
        </div>

        <!-- Step 5 — Timeline + contact -->
        <div class="form-step">
          <h2>When would you like to sell?</h2>
          <p class="form-help">Just a rough timeline — we'll work to what suits you.</p>
          <div class="form-radio-group">
            <label class="form-option"><input type="radio" name="timeline" value="Within 4 weeks" data-required="true"><span>Within 4 weeks</span></label>
            <label class="form-option"><input type="radio" name="timeline" value="1-3 months" data-required="true"><span>1–3 months</span></label>
            <label class="form-option"><input type="radio" name="timeline" value="3-6 months" data-required="true"><span>3–6 months</span></label>
            <label class="form-option"><input type="radio" name="timeline" value="Flexible / just exploring" data-required="true"><span>Flexible / just exploring</span></label>
          </div>
          <div class="form-error" data-for="timeline"></div>

          <h2 class="mt-4">How can we reach you?</h2>
          <label class="form-label" for="first_name">First name</label>
          <input type="text" id="first_name" name="first_name" class="form-input" data-required="true" autocomplete="given-name">
          <div class="form-error" data-for="first_name"></div>

          <label class="form-label mt-2" for="phone">Phone</label>
          <input type="tel" id="phone" name="phone" class="form-input" data-required="true" data-validate="phone" autocomplete="tel" placeholder="e.g. 07700 900123">
          <div class="form-error" data-for="phone"></div>

          <label class="form-label mt-2" for="email">Email</label>
          <input type="email" id="email" name="email" class="form-input" data-required="true" autocomplete="email">
          <div class="form-error" data-for="email"></div>

          <label class="form-label mt-2">Preferred contact</label>
          <div class="form-check-group">
            <label class="form-option"><input type="checkbox" name="contact_preference[]" value="Phone"><span>Phone</span></label>
            <label class="form-option"><input type="checkbox" name="contact_preference[]" value="Email"><span>Email</span></label>
            <label class="form-option"><input type="checkbox" name="contact_preference[]" value="WhatsApp"><span>WhatsApp</span></label>
            <label class="form-option"><input type="checkbox" name="contact_preference[]" value="Text"><span>Text</span></label>
          </div>

          <p class="small muted mt-2">By submitting, you agree to our <a href="/privacy.php">privacy policy</a>. We never share your details with third parties.</p>

          <div class="form-actions">
            <button type="button" class="btn btn-ghost" data-action="prev">← Back</button>
            <button type="submit" class="btn btn-primary">Send my details</button>
          </div>
        </div>
      </form>
    </div>

    <p class="text-center mt-4 muted">Prefer to talk? Call us on <a href="tel:01415301430"><strong>0141 530 1430</strong></a> — we usually pick up within a few rings.</p>
  </div>
</section>

</main>

<?php include 'includes/footer.php'; ?>
