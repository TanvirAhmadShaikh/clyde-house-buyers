<?php
$page_title = "Cookie Policy | Clyde Housebuyers";
$page_description = "How Clyde Housebuyers uses cookies on this website.";
$canonical = "https://clydehousebuyers.co.uk/cookies.php";
include 'includes/head.php';
include 'includes/header.php';
?>

<main id="main">
<nav class="breadcrumbs"><div class="container"><a href="/">Home</a> · <span aria-current="page">Cookies</span></div></nav>

<section class="section">
<div class="container-narrow prose">

<h1>Cookie Policy</h1>
<p class="muted">Last updated: <?= date('F Y') ?></p>

<h2>What are cookies?</h2>
<p>Cookies are small text files stored on your device when you visit a website. They help the site remember your preferences and provide a better experience.</p>

<h2>What cookies we use</h2>

<h3>Strictly necessary</h3>
<p>These cookies are required for the website to function. They don't store personal data. We use them for things like maintaining your form progress as you fill out our free valuation form. You cannot opt out of these without disabling the site's functionality.</p>

<h3>Analytics (optional)</h3>
<p>We use Google Analytics to understand how people use the website — which pages are visited and where people drop off — so we can improve it. These cookies only load <strong>if you accept them</strong> via the cookie banner shown when you first visit. If you reject or ignore the banner, no analytics cookies are set. You can change your choice at any time using the button below, or opt out via your browser settings or the <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" rel="noopener">Google Analytics opt-out add-on</a>.</p>

<h3>What we don't use</h3>
<p>We don't use advertising cookies. We don't track you across other websites. We don't sell or share cookie data with third-party advertisers.</p>

<h2>Managing cookies</h2>
<p>You can change or withdraw your analytics consent on this site at any time:</p>
<p>
  <button type="button" id="chb-cc-reopen" class="btn btn-ghost" style="cursor:pointer;">Change my cookie choice</button>
  <span id="chb-cc-current" class="muted" style="margin-left:0.75rem;font-size:0.9rem;"></span>
</p>
<script>
(function(){
  var KEY='chb_cookie_consent';
  var label=document.getElementById('chb-cc-current');
  function refresh(){
    var v=null; try{v=localStorage.getItem(KEY);}catch(e){}
    if(label) label.textContent = v==='accepted' ? 'Current choice: Accepted' : (v==='rejected' ? 'Current choice: Rejected' : 'No choice made yet');
  }
  var btn=document.getElementById('chb-cc-reopen');
  if(btn){
    btn.addEventListener('click',function(){
      try{localStorage.removeItem(KEY);}catch(e){}
      var banner=document.getElementById('chb-cookie-banner');
      if(banner) banner.hidden=false;
      refresh();
    });
  }
  refresh();
})();
</script>
<p>You can also control cookies through your browser settings. Most browsers let you:</p>
<ul>
  <li>See which cookies you have and delete them</li>
  <li>Block cookies from specific sites</li>
  <li>Block third-party cookies</li>
  <li>Clear all cookies when you close the browser</li>
</ul>

<h2>More information</h2>
<p>For more on cookies and online privacy, see <a href="https://ico.org.uk/your-data-matters/online/cookies/" target="_blank" rel="noopener">the ICO's guidance on cookies</a>.</p>

<h2>Contact</h2>
<p>Questions about cookies on this site? Email <a href="mailto:info@clydehousebuyers.co.uk">info@clydehousebuyers.co.uk</a>.</p>

</div>
</section>
</main>

<?php include 'includes/footer.php'; ?>
