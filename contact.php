<?php
$page_title = "Contact Clyde Housebuyers | Glasgow Property Buyers";
$page_description = "Talk to Clyde Housebuyers. 0141 530 1430. Glasgow-based property buyers covering Scotland's Central Belt. Free valuation in 24 hours.";
$canonical = "https://clydehousebuyers.co.uk/contact.php";
// Sticky mobile CTA points to /free-valuation.php; on the contact page that
// splits user intent — they actively chose contact. Suppress it here.
$hide_mobile_cta = true;
include 'includes/head.php';
include 'includes/header.php';
?>

<main id="main">

<nav class="breadcrumbs"><div class="container"><a href="/">Home</a> · <span aria-current="page">Contact</span></div></nav>

<section class="hero" style="padding:3rem 0;">
  <div class="container hero-inner">
    <span class="eyebrow" style="color:#d6b56b;">Contact</span>
    <h1>Talk to us. We pick up.</h1>
    <p class="hero-subhead">The easiest way to start is a phone call — typically 10 to 15 minutes, no pressure, no script. Or send us your property details via the form and we'll come back within 24 hours.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width: 900px;">
    <div class="grid-2">
      <div>
        <h2>By phone</h2>
        <p style="font-size:1.5rem; font-family: var(--ch-font-display); margin-bottom: 0.25rem;"><a href="tel:01415301430" style="text-decoration:none;">0141 530 1430</a></p>
        <p class="muted">Monday to Friday, 9am – 6pm. Voicemail outside these hours — we return calls within one working day.</p>

        <h2 class="mt-4">By email</h2>
        <p><a href="mailto:info@clydehousebuyers.co.uk"><strong>info@clydehousebuyers.co.uk</strong></a></p>
        <p class="muted">Replies within one working day.</p>

        <h2 class="mt-4">Correspondence address</h2>
        <p>48 W George St<br>Glasgow G2 1BP<br>Scotland</p>
        <p class="small muted">This is a mailing address — we're not staffed here for walk-ins. We come to you, or meet by appointment.</p>

        <h2 class="mt-4">Compliance</h2>
        <p class="small muted">Clyde Housebuyers is a trading name of PropGain UK Limited, a company registered in England &amp; Wales (company number 16913648). Registered office: 20 Wenlock Road, London N1 7GU. ICO registration ZC071824.</p>
      </div>

      <div>
        <div class="form-card">
          <h2 style="margin-top:0;">Send a message</h2>
          <p class="muted">For a property valuation, please use the <a href="/free-valuation.php">free valuation form</a> instead — it gives us everything we need to come back with an accurate offer.</p>
          <form id="lead-form" action="/submit-lead.php" method="POST" novalidate>
            <input type="text" name="website" style="position:absolute;left:-9999px;" tabindex="-1" autocomplete="off" aria-hidden="true">
            <input type="hidden" name="form_started" value="0">
            <input type="hidden" name="postcode" value="N/A-CONTACT">
            <input type="hidden" name="property_type" value="Contact enquiry">
            <input type="hidden" name="condition" value="N/A">
            <input type="hidden" name="situation" value="General enquiry">
            <input type="hidden" name="timeline" value="Flexible / just exploring">

            <div class="form-step active">
              <label class="form-label" for="first_name">Name</label>
              <input type="text" id="first_name" name="first_name" class="form-input" data-required="true" autocomplete="name">
              <div class="form-error" data-for="first_name"></div>

              <label class="form-label mt-2" for="phone">Phone</label>
              <input type="tel" id="phone" name="phone" class="form-input" data-required="true" data-validate="phone" autocomplete="tel">
              <div class="form-error" data-for="phone"></div>

              <label class="form-label mt-2" for="email">Email</label>
              <input type="email" id="email" name="email" class="form-input" data-required="true" autocomplete="email">
              <div class="form-error" data-for="email"></div>

              <label class="form-label mt-2" for="notes">Message</label>
              <textarea id="notes" name="notes" class="form-textarea" data-required="true" placeholder="What would you like to know?"></textarea>
              <div class="form-error" data-for="notes"></div>

              <button type="submit" class="btn btn-primary btn-block mt-2">Send message</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

</main>

<?php include 'includes/footer.php'; ?>
