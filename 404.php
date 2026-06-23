<?php
http_response_code(404);
$page_title = "Page not found | Clyde Housebuyers";
$page_description = "Page not found.";
include 'includes/head.php';
include 'includes/header.php';
?>

<main id="main">
<section class="section text-center">
  <div class="container-narrow">
    <span class="eyebrow">404</span>
    <h1>That page doesn't exist.</h1>
    <p style="font-size:1.15rem; color: var(--ch-navy-60); margin-bottom: 2rem;">It might have moved, or the link might be wrong. Let's get you back somewhere useful.</p>
    <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
      <a href="/" class="btn btn-primary">Back to homepage</a>
      <a href="/free-valuation.php" class="btn btn-ghost">Get a free valuation</a>
      <a href="tel:01415301430" class="btn btn-ghost">📞 0141 530 1430</a>
    </div>
  </div>
</section>
</main>

<?php include 'includes/footer.php'; ?>
