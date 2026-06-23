<?php
/**
 * Shared head fragment.
 * Set $page_title, $page_description, $canonical, $og_title, $og_description
 * before including. Optional: $extra_head for page-specific schema.
 */
$base_url = 'https://clydehousebuyers.co.uk';
$default_description = "Glasgow's local house buyers. Cash offer, assisted sale or a tailored route across Scotland's Central Belt. Free valuation in 24 hours, no fees, completion in 14–28 days.";
$page_title = $page_title ?? "Clyde Housebuyers — Sell Your House Fast in Glasgow & Central Belt";
$page_description = $page_description ?? $default_description;
$canonical = $canonical ?? $base_url . $_SERVER['REQUEST_URI'];
$og_title = $og_title ?? $page_title;
$og_description = $og_description ?? $page_description;
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
<!-- Google tag (gtag.js) with Consent Mode v2 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-K7GHQ8RD4N"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  // Consent Mode v2: deny analytics/ads storage by DEFAULT (before any cookie is set).
  // PECR/UK GDPR requires consent BEFORE non-essential cookies load.
  gtag('consent', 'default', {
    'analytics_storage': 'denied',
    'ad_storage': 'denied',
    'ad_user_data': 'denied',
    'ad_personalization': 'denied'
  });

  // If the visitor previously accepted, restore that consent immediately.
  try {
    if (localStorage.getItem('chb_cookie_consent') === 'accepted') {
      gtag('consent', 'update', { 'analytics_storage': 'granted' });
    }
  } catch (e) {}

  gtag('config', 'G-K7GHQ8RD4N');
</script>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($page_title) ?></title>
<meta name="description" content="<?= htmlspecialchars($page_description) ?>">
<link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
<?php if (!empty($noindex)): ?>
<meta name="robots" content="noindex, nofollow">
<?php endif; ?>

<meta property="og:title" content="<?= htmlspecialchars($og_title) ?>">
<meta property="og:description" content="<?= htmlspecialchars($og_description) ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
<meta property="og:locale" content="en_GB">
<meta property="og:image" content="<?= $base_url ?>/assets/img/logo.png">
<meta name="twitter:card" content="summary_large_image">

<link rel="icon" href="/assets/img/favicon.png" type="image/png">
<link rel="apple-touch-icon" href="/assets/img/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="/assets/css/style.css">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "RealEstateAgent",
  "name": "Clyde Housebuyers",
  "alternateName": "Clyde House Buyers",
  "url": "<?= $base_url ?>",
  "logo": "<?= $base_url ?>/assets/img/logo.png",
  "image": "<?= $base_url ?>/assets/img/logo.png",
  "telephone": "+441415301430",
  "email": "info@clydehousebuyers.co.uk",
  "priceRange": "Free valuation",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "48 W George St",
    "addressLocality": "Glasgow",
    "postalCode": "G2 1BP",
    "addressRegion": "Scotland",
    "addressCountry": "GB"
  },
  "areaServed": [
    {"@type": "City", "name": "Glasgow"},
    {"@type": "City", "name": "Paisley"},
    {"@type": "City", "name": "East Kilbride"},
    {"@type": "City", "name": "Hamilton"},
    {"@type": "City", "name": "Motherwell"},
    {"@type": "City", "name": "Coatbridge"},
    {"@type": "City", "name": "Airdrie"},
    {"@type": "City", "name": "Falkirk"},
    {"@type": "City", "name": "Cumbernauld"},
    {"@type": "City", "name": "Renfrew"}
  ],
  "openingHoursSpecification": {
    "@type": "OpeningHoursSpecification",
    "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
    "opens": "09:00", "closes": "18:00"
  }
}
</script>
<?php if (!empty($extra_head)) echo $extra_head; ?>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
