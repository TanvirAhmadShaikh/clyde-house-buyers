/* Clyde Housebuyers — main JS */
(function () {
  'use strict';

  /* Mobile nav toggle */
  const toggle = document.querySelector('.nav-toggle');
  const mobileNav = document.querySelector('.mobile-nav');
  if (toggle && mobileNav) {
    toggle.addEventListener('click', function () {
      const open = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!open));
      mobileNav.classList.toggle('open', !open);
      document.body.classList.toggle('mobile-nav-open', !open);
    });
    // Close the menu when a link inside it is clicked (so the new page loads with a clean state)
    mobileNav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        toggle.setAttribute('aria-expanded', 'false');
        mobileNav.classList.remove('open');
        document.body.classList.remove('mobile-nav-open');
      });
    });
  }

  /* Multi-step lead form */
  const form = document.getElementById('lead-form');
  if (form) initLeadForm(form);

  function initLeadForm(form) {
    const steps = form.querySelectorAll('.form-step');
    const progressBars = form.querySelectorAll('.form-progress span');
    let current = 0;

    // Timestamp when the form became available — used server-side to detect bot-fast submissions.
    const startedField = form.querySelector('input[name="form_started"]');
    if (startedField) startedField.value = String(Date.now());

    function showStep(i) {
      steps.forEach((s, idx) => {
        s.classList.toggle('active', idx === i);
      });
      progressBars.forEach((b, idx) => {
        b.classList.toggle('active', idx === i);
        b.classList.toggle('done', idx < i);
      });
      window.scrollTo({ top: form.offsetTop - 90, behavior: 'smooth' });
    }

    function validateStep(stepEl) {
      const required = stepEl.querySelectorAll('[data-required="true"]');
      let ok = true;
      let firstInvalid = null;
      required.forEach((el) => {
        const err = stepEl.querySelector(`.form-error[data-for="${el.name || el.id}"]`);
        if (err) err.textContent = '';
        let valid = true;
        if (el.type === 'radio') {
          const group = stepEl.querySelectorAll(`input[name="${el.name}"]`);
          valid = Array.from(group).some((r) => r.checked);
        } else if (el.type === 'checkbox') {
          const group = stepEl.querySelectorAll(`input[name="${el.name}"]`);
          valid = Array.from(group).some((r) => r.checked);
        } else if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA' || el.tagName === 'SELECT') {
          valid = el.value.trim().length > 0;
          if (valid && el.dataset.validate === 'postcode') {
            const pc = el.value.replace(/\s+/g, '').toUpperCase();
            valid = /^[A-Z]{1,2}[0-9R][0-9A-Z]?[0-9][A-Z]{2}$/.test(pc);
            if (!valid && err) err.textContent = 'Please enter a valid UK postcode (e.g. G42 8AA).';
          }
          if (valid && el.type === 'email') {
            valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value);
            if (!valid && err) err.textContent = 'Please enter a valid email address.';
          }
          if (valid && el.dataset.validate === 'phone') {
            const ph = el.value.replace(/[\s\-\(\)]/g, '');
            valid = /^(\+?44|0)[0-9]{9,11}$/.test(ph);
            if (!valid && err) err.textContent = 'Please enter a valid UK phone number.';
          }
        }
        if (!valid) {
          ok = false;
          if (err && !err.textContent) err.textContent = 'This is required.';
          if (!firstInvalid) firstInvalid = el;
        }
      });
      if (!ok && firstInvalid) {
        const focusEl = firstInvalid.type === 'radio' || firstInvalid.type === 'checkbox' ?
          stepEl.querySelector(`input[name="${firstInvalid.name}"]`) : firstInvalid;
        if (focusEl) focusEl.focus();
      }
      return ok;
    }

    form.addEventListener('click', function (e) {
      if (e.target.matches('[data-action="next"]')) {
        e.preventDefault();
        if (validateStep(steps[current])) {
          current = Math.min(current + 1, steps.length - 1);
          showStep(current);
        }
      } else if (e.target.matches('[data-action="prev"]')) {
        e.preventDefault();
        current = Math.max(current - 1, 0);
        showStep(current);
      }
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (!validateStep(steps[current])) return;

      const submitBtn = form.querySelector('[type="submit"]');
      if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Sending…'; }

      const data = new FormData(form);
      fetch(form.action, { method: 'POST', body: data })
        .then((r) => r.json())
        .then((res) => {
          if (res && res.ok) {
            form.innerHTML = `
              <div class="form-success">
                <h2>Thanks${res.firstName ? ', ' + res.firstName : ''} — we've got your details.</h2>
                <p>A member of our team will be in touch within 1 working day. If your situation is urgent, please call us now on <a href="tel:01415301430">0141 530 1430</a>.</p>
              </div>`;
            try { if (typeof gtag === 'function') gtag('event', 'generate_lead', { event_category: 'lead', value: 1 }); } catch (e) {}
          } else {
            alert((res && res.error) || 'Sorry, something went wrong. Please try calling us on 0141 530 1430.');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Send my details'; }
          }
        })
        .catch(() => {
          alert('Sorry, something went wrong. Please try calling us on 0141 530 1430.');
          if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Send my details'; }
        });
    });

    showStep(0);
  }

  /* FAQ — auto-inject FAQPage schema for any page with .faq */
  const faqs = document.querySelectorAll('.faq details');
  if (faqs.length > 0 && document.querySelector('.faq[data-schema="true"]')) {
    const mainEntity = [];
    faqs.forEach((d) => {
      const q = d.querySelector('summary');
      const a = d.querySelector('p, div.faq-answer');
      if (q && a) {
        mainEntity.push({
          '@type': 'Question',
          name: q.textContent.trim(),
          acceptedAnswer: { '@type': 'Answer', text: a.textContent.trim() }
        });
      }
    });
    if (mainEntity.length) {
      const script = document.createElement('script');
      script.type = 'application/ld+json';
      script.textContent = JSON.stringify({ '@context': 'https://schema.org', '@type': 'FAQPage', mainEntity });
      document.head.appendChild(script);
    }
  }
})();
