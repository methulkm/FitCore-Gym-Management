/*
 * Public-site visual polish: scroll-reveal for [data-reveal] elements and count-up for
 * [data-count]. Both use IntersectionObserver (cheap, native, no library) and fire once per
 * element so they don't keep costing anything after the first reveal. Respects
 * prefers-reduced-motion by doing nothing extra (the CSS already shows elements instantly then).
 */
(function () {
  'use strict';
  if (!('IntersectionObserver' in window)) {
    // No IO support: just show everything, don't leave content invisible.
    document.querySelectorAll('[data-reveal]').forEach(function (el) { el.classList.add('is-visible'); });
    return;
  }

  var revealObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

  var items = document.querySelectorAll('[data-reveal]');
  items.forEach(function (el, i) {
    el.style.transitionDelay = Math.min(i % 6, 5) * 70 + 'ms';
    revealObserver.observe(el);
  });

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function animateCount(el) {
    var raw = el.getAttribute('data-count') || '';
    var match = raw.match(/^(\D*)(\d+)(.*)$/); // prefix (non-digits), the number, suffix
    if (!match) return;
    var prefix = match[1], target = parseInt(match[2], 10), suffix = match[3];
    if (reduceMotion || !target) { el.textContent = raw; return; }

    var start = null, duration = 900;
    function step(ts) {
      if (!start) start = ts;
      var p = Math.min((ts - start) / duration, 1);
      var eased = 1 - Math.pow(1 - p, 3); // ease-out cubic
      el.textContent = prefix + Math.floor(eased * target) + suffix;
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = raw;
    }
    requestAnimationFrame(step);
  }

  var countObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        animateCount(entry.target);
        countObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.4 });

  document.querySelectorAll('[data-count]').forEach(function (el) { countObserver.observe(el); });

  // Progress/capacity bars: animate from 0 to their real width once scrolled into view.
  var barObserver = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        var el = entry.target;
        requestAnimationFrame(function () { el.style.width = el.getAttribute('data-width') + '%'; });
        barObserver.unobserve(el);
      }
    });
  }, { threshold: 0.3 });
  document.querySelectorAll('[data-width]').forEach(function (el) { barObserver.observe(el); });
})();
