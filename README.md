# Clyde Housebuyers — Deployment Guide

A complete, production-ready PHP/HTML/CSS website for **clydehousebuyers.co.uk**, designed for standard Linux shared hosting (cPanel, Plesk, DirectAdmin, etc.).

---

## What's included

- **Homepage** with 3-pathway hero, solution grid, case studies, FAQ
- **5 location pages** (Glasgow, Paisley, East Kilbride, Hamilton, Motherwell)
- **5 solution pages** (Cash, Assisted Sale, Tenanted, Brokered, JV)
- **5 situation pages** (Inherited, Repossession, Tenanted exit, Divorce, Repairs)
- **Multi-step lead form** with server-side validation, honeypot, optional reCAPTCHA, CSV logging, email notifications, and optional HighLevel webhook
- **Supporting pages**: About, Contact, How it works, Case studies, FAQ, Privacy, Terms, Cookies, Complaints
- **SEO**: sitemap.xml, robots.txt, schema.org RealEstateAgent + FAQPage, canonical URLs, OG tags
- **404 page**, **.htaccess** with HTTPS redirect, security headers, gzip, caching
- **Mobile-first responsive design**, sticky CTA bar, accessible (skip link, focus states)

---

## Quick deployment (cPanel / FTP)

### 1. Upload the files

**Option A — cPanel File Manager (easiest)**
1. Log into cPanel.
2. Open **File Manager** → `public_html`.
3. Upload the zip and **Extract** it. Make sure files end up *directly in* `public_html`, not inside a subfolder.

**Option B — FTP (FileZilla / Cyberduck)**
1. Connect to your host with your FTP credentials.
2. Upload all files and folders inside `clydehousebuyers/` to `public_html/`.
3. Keep folder structure intact.

### 2. Configure email (Fasthosts SMTP)

The form sends two emails per submission via authenticated SMTP through Fasthosts:

1. A **lead notification** to your real inbox
2. An **auto-reply** to the seller

**Step 2a — Create a sender mailbox in Fasthosts**

In the Fasthosts control panel:
- Email → Add new mailbox
- Address: `no-reply@clydehousebuyers.co.uk`
- Set any password (you'll never log in to it — it's only used for SMTP auth)
- Save it somewhere secure — you'll paste it into the next step

**Step 2b — Edit `/submit-lead.php`**

Open the file and update the CONFIG block at the top:

```php
$LEAD_RECIPIENT   = 'info@clydehousebuyers.co.uk';                  // where YOU receive leads
$FROM_EMAIL       = 'no-reply@clydehousebuyers.co.uk';              // the sender mailbox you just created
$FROM_NAME        = 'Clyde Housebuyers Website';

$SMTP_HOST        = 'smtp.livemail.co.uk';
$SMTP_PORT        = 587;
$SMTP_SECURE      = 'starttls';
$SMTP_USER        = 'no-reply@clydehousebuyers.co.uk';              // full email address
$SMTP_PASS        = 'PASTE-THE-MAILBOX-PASSWORD-HERE';              // from Step 2a
```

**Important:** the `$SMTP_USER` must be the full email address (with `@clydehousebuyers.co.uk`), not just `no-reply`. Fasthosts requires the full address for SMTP authentication.

**Why we don't use PHP's `mail()` function**

PHP's built-in `mail()` uses the server's local sendmail, which often gets flagged as spam (especially by Gmail/Outlook) because it fails SPF/DKIM checks. Using authenticated SMTP through Fasthosts' `smtp.livemail.co.uk` means the emails are sent like a normal email client would send them — fully authenticated and with much better deliverability.

### 3. Test the form

1. Go to `https://clydehousebuyers.co.uk/free-valuation.php`
2. Fill out and submit a test lead using a Gmail or Outlook address as the seller email
3. Confirm:
   - You receive the lead notification at `$LEAD_RECIPIENT` (usually within 30 seconds)
   - The seller email receives the auto-reply (check inbox AND spam folder — though SMTP delivery should land in inbox)
   - The lead is appended to `/private/leads.csv` (visible via File Manager)

**If emails don't arrive**, temporarily enable debug mode in `/submit-lead.php`:

```php
$DEBUG_SMTP = true;
```

Re-submit the form. A full SMTP transcript (with credentials redacted) is appended to `/private/smtp-debug.log`. The log will show exactly where the SMTP conversation failed — usually one of: wrong password, wrong mailbox address, or the firewall blocking port 587. Set `$DEBUG_SMTP` back to `false` once it's working.

### 4. Force HTTPS

In cPanel → **SSL/TLS Status** → install/enable the AutoSSL certificate for `clydehousebuyers.co.uk` and `www.clydehousebuyers.co.uk`.

Once HTTPS works, the `.htaccess` will automatically redirect all HTTP to HTTPS.

### 5. Submit to Google

- **Google Search Console**: add `https://clydehousebuyers.co.uk`, verify, submit `sitemap.xml`
- **Google Business Profile**: create a Glasgow business listing using NAP exactly matching the footer

---

## Folder structure

```
/
├── index.php                           Homepage
├── free-valuation.php                  Lead form
├── about.php / contact.php / faq.php
├── how-it-works.php / case-studies.php
├── privacy.php / terms.php / cookies.php
├── 404.php
├── submit-lead.php                     Form handler (configure here)
├── sitemap.xml / robots.txt
├── .htaccess                           HTTPS, security, caching
│
├── /includes/                          Shared partials (head/header/footer)
├── /assets/
│   ├── /css/style.css                  Main stylesheet
│   ├── /js/main.js                     Nav, form, FAQ schema
│   └── /img/logo.png                   Brand logo
│
├── /sellers/                           Location pages
│   ├── index.php
│   ├── sell-house-fast-glasgow.php
│   ├── sell-house-fast-paisley.php
│   ├── sell-house-fast-east-kilbride.php
│   ├── sell-house-fast-hamilton.php
│   └── sell-house-fast-motherwell.php
│
├── /solutions/                         Solution pages
│   ├── index.php
│   ├── cash-purchase.php
│   ├── assisted-sale.php
│   ├── sell-with-tenants-in-situ.php
│   ├── brokered-sale.php
│   └── joint-venture.php
│
├── /situations/                        Situation pages
│   ├── index.php
│   ├── sell-inherited-property-glasgow.php
│   ├── avoid-repossession-glasgow.php
│   ├── sell-tenanted-property-scotland.php
│   ├── sell-house-fast-divorce-scotland.php
│   └── sell-house-needing-repairs-glasgow.php
│
└── /private/                           Lead CSV (web access blocked)
    ├── .htaccess
    └── leads.csv  (created on first form submission)
```

---

## Configuration cheat sheet

### Phone number
Currently hard-coded as `0141 530 1430` in `/includes/header.php`, `/includes/footer.php`, and across all pages. If this changes, run:

```bash
find . -name "*.php" -exec sed -i 's/0141 530 1430/NEW NUMBER/g' {} +
find . -name "*.php" -exec sed -i 's/01415301430/NEWNUMBERWITHOUTSPACES/g' {} +
```

### Email
`info@clydehousebuyers.co.uk` used throughout. Same find/replace approach if it changes.

### Brand colours
Defined as CSS custom properties at the top of `/assets/css/style.css`:
- `--ch-navy` (deep navy, #0B1F3B)
- `--ch-gold` (warm gold, #C8A24A)
- `--ch-offwhite` (#F7F9FC)
- `--ch-slate`, `--ch-charcoal`, `--ch-success`, `--ch-coral`

Change them in one place; the whole site updates.

### Analytics
To add Google Analytics, paste your GA4 snippet into `/includes/head.php` just before `</head>`. The form already fires a `generate_lead` event on submission.

### Form abuse protection (built-in)

The form handler has several layers of abuse protection out of the box — no setup required:

- **Honeypot field** — invisible to humans, catches naive bots that fill every field
- **Server-side validation** — postcode/email/phone all validated server-side regardless of JS
- **Header-injection sanitisation** — CR/LF and control characters stripped from all inputs to prevent SMTP header injection
- **Timing check** — submissions completed in under 3 seconds get flagged. They still log to CSV (visible to you) but the auto-reply to the seller is skipped — protecting innocent third parties from being spammed via a forged email address.
- **Rate limiting** — max 3 submissions per IP per 5 minutes; 12 per IP per day. State tracked in `/private/ratelimit.json` (hashed IPs only).
- **CSV formula injection defence** — values starting with `=`, `+`, `-`, `@` are prefixed with `'` so Excel/Sheets treats them as text rather than executing them.
- **TLS certificate verification** — the SMTP client verifies Fasthosts' certificate before sending mail (prevents MITM).

### reCAPTCHA v3 (optional — extra layer for high-traffic sites)

The built-in protections above are sufficient for most cases. If you start seeing repeated abuse, you can add reCAPTCHA v3 as an additional layer:

1. Get a v3 key pair from `https://www.google.com/recaptcha/admin`
2. Set `$RECAPTCHA_SECRET` in `/submit-lead.php`
3. Add this just before `</body>` in `/includes/footer.php`:

```html
<script src="https://www.google.com/recaptcha/api.js?render=YOUR_SITE_KEY"></script>
<script>
grecaptcha.ready(function() {
  document.querySelectorAll('form').forEach(function(f) {
    grecaptcha.execute('YOUR_SITE_KEY', {action:'submit'}).then(function(token) {
      var input = document.createElement('input');
      input.type='hidden'; input.name='recaptcha_token'; input.value=token;
      f.appendChild(input);
    });
  });
});
</script>
```

### HighLevel CRM integration (optional)
If you want every lead also pushed to GoHighLevel:
1. In HighLevel, create an Inbound Webhook trigger
2. Copy the webhook URL
3. Paste into `$HL_WEBHOOK` in `/submit-lead.php`

Leads will go to email **and** HighLevel simultaneously.

---

## PHP requirements

- **PHP 7.4 or higher** (PHP 8.x recommended)
- Outbound TCP port 587 allowed (standard on Fasthosts and most shared hosting)
- `openssl` extension (for STARTTLS)
- `curl` extension (for optional HighLevel webhook)
- `mbstring` extension (for `mb_substr`)

Almost every UK shared host (Fasthosts, Hostinger, SiteGround, Krystal, IONOS, Namecheap, etc.) ships with all of these enabled.

---

## Going live checklist

- [ ] Files uploaded to `public_html`
- [ ] SSL certificate installed and HTTPS forced
- [ ] `no-reply@clydehousebuyers.co.uk` mailbox created in Fasthosts
- [ ] `submit-lead.php` configured with real `$LEAD_RECIPIENT`, `$SMTP_USER`, and `$SMTP_PASS`
- [ ] Test lead submitted from the live site — confirmed received at `info@clydehousebuyers.co.uk`
- [ ] Auto-reply confirmed received in seller's Gmail/Outlook inbox (not spam)
- [ ] CSV log at `/private/leads.csv` working
- [ ] `$DEBUG_SMTP` set back to `false`
- [ ] Phone number, email, address verified throughout site
- [ ] Companies House registration number added (replace placeholder in footer)
- [ ] Trustpilot widget added (when you have real reviews — currently the hero shows real ICO/AML/PRS credentials instead)
- [ ] Google Search Console verified, sitemap submitted
- [ ] Google Business Profile created with matching NAP
- [ ] Google Analytics 4 snippet added (optional)
- [ ] reCAPTCHA v3 added (optional, recommended)

---

## Compliance reminders

A few claims used throughout the site should be **verified against your real operations** before going fully live:

- **Trustpilot** — there's no Trustpilot widget yet because there are no real reviews. The hero trust strip uses real regulator credentials (ICO ZC071824 · HMRC AML XNML00000217270 · PRS PRS056317) instead. Add Trustpilot only once you have real reviews from real customers.
- **Case studies** (G42, PA1, ML3) are presented as real anonymised completions. If these aren't yet real, mark them as illustrative examples or remove until you have real case studies.
- **Office address** — `48 W George St, Glasgow G2 1BP` is used in footer, contact page, terms page, and structured data schema. Confirm this address is current before launch.
- **Companies House number** — replace the placeholder text in `/includes/footer.php` with the actual number when ready.
- **Phone number `0141 530 1430`** — must point to a real, answered line.

This isn't legal advice — but advertising standards (ASA) and TPO principles require these claims be accurate.

---

## Support

Site built by Claude (Anthropic) for PropGain UK Limited / Clyde Housebuyers. The code is self-contained vanilla PHP — any web developer can maintain it. No build step, no framework, no node_modules.
