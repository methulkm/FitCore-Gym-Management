/*
 * Partial page navigation for the admin shell.
 * Clicking a sidebar link / button or submitting a form inside /modules/ fetches the target page in the
 * background and swaps ONLY the content area (#app-content) and the sidebar highlight (#app-sidebar),
 * so the header, sidebar and scroll position of the shell don't reload. Anything outside the scope
 * (login, logout, public site, file downloads) is left as a normal full page load.
 */
(function () {
  'use strict';

  var CONTENT_ID = 'app-content';
  var SIDEBAR_ID = 'app-sidebar';
  var scope = document.body.getAttribute('data-ajax-scope');
  if (!scope || !document.getElementById(CONTENT_ID) || !window.fetch) return;

  var seq = 0;
  var loadedScripts = new Set(Array.prototype.map.call(
    Array.prototype.filter.call(document.scripts, function (s) { return s.src; }),
    function (s) { return s.src; }
  ));

  // ---- thin progress bar -------------------------------------------------
  var bar = document.createElement('div');
  bar.style.cssText = 'position:fixed;top:0;left:0;height:3px;width:0;background:#14B8A6;z-index:9999;' +
    'transition:width .3s ease,opacity .3s ease;opacity:0;pointer-events:none';
  document.body.appendChild(bar);
  var barTimer;
  function barStart() {
    clearTimeout(barTimer);
    bar.style.opacity = '1';
    bar.style.width = '35%';
    barTimer = setTimeout(function () { bar.style.width = '75%'; }, 250);
  }
  function barDone() {
    clearTimeout(barTimer);
    bar.style.width = '100%';
    setTimeout(function () {
      bar.style.opacity = '0';
      setTimeout(function () { bar.style.width = '0'; }, 300);
    }, 150);
  }

  function inScope(url) {
    return url.origin === location.origin && url.pathname.indexOf(scope) === 0;
  }

  // ---- run <script>s from swapped-in HTML (innerHTML leaves them inert), in order ----
  function runScripts(container) {
    var scripts = Array.prototype.slice.call(container.querySelectorAll('script'));
    return scripts.reduce(function (chain, old) {
      return chain.then(function () {
        return new Promise(function (resolve) {
          var s = document.createElement('script');
          if (old.src) {
            if (loadedScripts.has(old.src)) return resolve();
            loadedScripts.add(old.src);
            s.src = old.src;
            s.onload = s.onerror = function () { resolve(); };
            document.head.appendChild(s);
          } else {
            // Block-wrapped so top-level const/let don't clash when the same page is visited twice.
            s.text = '{\n' + old.text + '\n}';
            document.body.appendChild(s);
            document.body.removeChild(s);
            resolve();
          }
        });
      });
    }, Promise.resolve());
  }

  function swap(doc) {
    var content = document.getElementById(CONTENT_ID);
    content.innerHTML = doc.getElementById(CONTENT_ID).innerHTML;

    var newSide = doc.getElementById(SIDEBAR_ID);
    var side = document.getElementById(SIDEBAR_ID);
    if (newSide && side) {
      var nav = side.querySelector('nav');
      var keep = nav ? nav.scrollTop : 0;
      side.innerHTML = newSide.innerHTML;
      var nav2 = side.querySelector('nav');
      if (nav2) nav2.scrollTop = keep;
    }

    document.title = doc.title;
    window.scrollTo(0, 0);
    return runScripts(content);
  }

  // ---- the loader --------------------------------------------------------
  function load(url, init, push) {
    var mine = ++seq;
    var content = document.getElementById(CONTENT_ID);
    content.style.opacity = '.55';
    content.style.transition = 'opacity .15s';
    barStart();

    var opts = { credentials: 'same-origin', headers: { 'X-Requested-With': 'app-nav' } };
    if (init) Object.keys(init).forEach(function (k) { opts[k] = init[k]; });

    return fetch(url, opts)
      .then(function (res) {
        var type = res.headers.get('content-type') || '';
        if (type.indexOf('text/html') === -1) { location.href = url; return; }
        return res.text().then(function (html) {
          if (mine !== seq) return; // a newer navigation already took over
          var doc = new DOMParser().parseFromString(html, 'text/html');
          if (!doc.getElementById(CONTENT_ID)) { location.href = res.url; return; } // e.g. redirected to login
          if (push) {
            if (res.url === location.href) history.replaceState({ ajax: 1 }, '', res.url);
            else history.pushState({ ajax: 1 }, '', res.url);
          }
          return swap(doc);
        });
      })
      .catch(function () { location.href = url; })
      .then(function () {
        if (mine === seq) {
          var c = document.getElementById(CONTENT_ID);
          if (c) c.style.opacity = '';
          barDone();
        }
      });
  }

  // ---- links -------------------------------------------------------------
  document.addEventListener('click', function (e) {
    // defaultPrevented: an inline onclick="return confirm(...)" that was cancelled must NOT navigate.
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target.closest ? e.target.closest('a[href]') : null;
    if (!a || (a.target && a.target !== '_self') || a.hasAttribute('download') || a.hasAttribute('data-no-ajax')) return;
    var url = new URL(a.href, location.href);
    if (!inScope(url)) return;
    if (url.hash && url.pathname === location.pathname && url.search === location.search) return;
    e.preventDefault();
    load(url.href, null, true);
  });

  // ---- forms -------------------------------------------------------------
  document.addEventListener('submit', function (e) {
    if (e.defaultPrevented) return;
    var form = e.target;
    if (!(form instanceof HTMLFormElement) || (form.target && form.target !== '_self') || form.hasAttribute('data-no-ajax')) return;
    var action = new URL(form.getAttribute('action') || location.href, location.href);
    if (!inScope(action)) return;
    e.preventDefault();

    var data;
    try { data = new FormData(form, e.submitter); } catch (err) { data = new FormData(form); }

    if ((form.getAttribute('method') || 'get').toLowerCase() === 'post') {
      load(action.href, { method: 'POST', body: data }, true);
    } else {
      var params = new URLSearchParams();
      data.forEach(function (v, k) { if (typeof v === 'string') params.append(k, v); });
      action.search = params.toString();
      load(action.href, null, true);
    }
  });

  // ---- back / forward ----------------------------------------------------
  window.addEventListener('popstate', function () {
    var url = new URL(location.href);
    if (inScope(url)) load(url.href, null, false);
    else location.reload();
  });
})();
