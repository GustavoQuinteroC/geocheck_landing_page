/* ==========================================================================
   ALKES V3 — interacciones
   Sin dependencias. Todo degrada: sin JS la página se lee, se navega y se
   contrata. Idéntico en las seis landings.
   ========================================================================== */
(function () {
    'use strict';

    var root = document.documentElement;
    var $  = function (s, c) { return (c || document).querySelector(s); };
    var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

    /* ---------------------------------------------------------------------
       0. Tema claro / oscuro
       Tres estados: sin elección (sigue al sistema en vivo), forzado claro y
       forzado oscuro. La elección se guarda; el sistema no la pisa.
       --------------------------------------------------------------------- */
    var mqDark = window.matchMedia('(prefers-color-scheme: dark)');

    function guardado() {
        try {
            var t = localStorage.getItem('tema');
            return (t === 'dark' || t === 'light') ? t : null;
        } catch (e) { return null; }
    }
    function temaActivo() { return guardado() || (mqDark.matches ? 'dark' : 'light'); }

    function pintarConmutadores() {
        var esOscuro = temaActivo() === 'dark';
        var destino  = esOscuro ? 'claro' : 'oscuro';
        $$('[data-theme-toggle]').forEach(function (b) {
            b.setAttribute('aria-label', 'Cambiar a tema ' + destino);
            var et = $('.theme-toggle__label', b);
            if (et) { et.textContent = 'Cambiar a tema ' + destino; }
        });
    }

    $$('[data-theme-toggle]').forEach(function (b) {
        b.addEventListener('click', function () {
            var nuevo = temaActivo() === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', nuevo);
            try { localStorage.setItem('tema', nuevo); } catch (e) {}
            pintarConmutadores();
        });
    });

    if (mqDark.addEventListener) {
        mqDark.addEventListener('change', function () { if (!guardado()) { pintarConmutadores(); } });
    }
    pintarConmutadores();

    /* ---------------------------------------------------------------------
       1 y 3. Barra fija y marcado de la sección activa
       Un solo manejador de scroll, limitado con requestAnimationFrame.

       El marcado NO usa intersección: hay secciones que no están en el menú
       (bandas oscuras, cierres) y, al pasar por ellas, el subrayado
       desaparecía. Se marca la última sección enlazada cuyo borde superior ya
       quedó por encima del umbral de lectura, así la marca nunca parpadea.
       --------------------------------------------------------------------- */
    var nav = $('#nav');
    var enlaces = $$('.nav__links .nav__link');
    var secciones = [];

    enlaces.forEach(function (a) {
        var h = a.getAttribute('href') || '';
        if (h.charAt(0) !== '#' || h.length < 2) { return; }
        var s = document.getElementById(h.slice(1));
        if (s) { secciones.push({ el: s, enlace: a }); }
    });

    function pintarScroll() {
        if (nav) { nav.classList.toggle('is-stuck', window.scrollY > 8); }

        if (!secciones.length) { return; }
        var umbral = Math.max(140, window.innerHeight * 0.32);
        var activo = null;
        for (var i = 0; i < secciones.length; i++) {
            if (secciones[i].el.getBoundingClientRect().top <= umbral) { activo = secciones[i]; }
        }
        // Al final del documento gana la última sección enlazada.
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 4) {
            activo = secciones[secciones.length - 1];
        }
        enlaces.forEach(function (a) { a.removeAttribute('aria-current'); });
        if (activo) { activo.enlace.setAttribute('aria-current', 'true'); }
    }

    var pendiente = false;
    function alHacerScroll() {
        if (pendiente) { return; }
        pendiente = true;
        window.requestAnimationFrame(function () { pendiente = false; pintarScroll(); });
    }
    pintarScroll();
    window.addEventListener('scroll', alHacerScroll, { passive: true });
    window.addEventListener('resize', alHacerScroll, { passive: true });

    /* ---------------------------------------------------------------------
       2. Menú móvil
       --------------------------------------------------------------------- */
    var burger = $('#navToggle');
    var panel  = $('#navPanel');
    if (burger && panel) {
        var cerrarPanel = function () {
            panel.classList.remove('is-open');
            burger.setAttribute('aria-expanded', 'false');
        };
        burger.addEventListener('click', function () {
            var abierto = panel.classList.toggle('is-open');
            burger.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        });
        $$('a', panel).forEach(function (a) { a.addEventListener('click', cerrarPanel); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && panel.classList.contains('is-open')) { cerrarPanel(); burger.focus(); }
        });
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1180) { cerrarPanel(); }
        });
    }

    /* ---------------------------------------------------------------------
       4. Revelado al entrar en pantalla — una sola vez
       --------------------------------------------------------------------- */
    var revelables = $$('[data-reveal]');
    if (revelables.length) {
        if (!('IntersectionObserver' in window) || reduce.matches) {
            revelables.forEach(function (el) { el.classList.add('is-in'); });
        } else {
            var ob = new IntersectionObserver(function (entradas) {
                entradas.forEach(function (en) {
                    if (en.isIntersecting) { en.target.classList.add('is-in'); ob.unobserve(en.target); }
                });
            }, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 });
            revelables.forEach(function (el) { ob.observe(el); });
        }
    }

    /* ---------------------------------------------------------------------
       5. Alternar periodo de facturación
       --------------------------------------------------------------------- */
    var pestanas = $$('[role="tab"]');
    if (pestanas.length) {
        var activar = function (tab) {
            pestanas.forEach(function (t) {
                var sel = t === tab;
                t.setAttribute('aria-selected', sel ? 'true' : 'false');
                t.setAttribute('tabindex', sel ? '0' : '-1');
                var p = document.getElementById(t.getAttribute('aria-controls'));
                if (p) { p.hidden = !sel; }
            });
        };
        pestanas.forEach(function (t, i) {
            t.addEventListener('click', function () { activar(t); });
            t.addEventListener('keydown', function (e) {
                var d = e.key === 'ArrowRight' ? 1 : (e.key === 'ArrowLeft' ? -1 : 0);
                if (!d) { return; }
                e.preventDefault();
                var sig = pestanas[(i + d + pestanas.length) % pestanas.length];
                sig.focus(); activar(sig);
            });
        });
    }

    /* ---------------------------------------------------------------------
       6. Diálogo de demo
       --------------------------------------------------------------------- */
    var dlg = $('#demoDialog');
    if (dlg && typeof dlg.showModal === 'function') {
        $$('[data-open-demo]').forEach(function (b) {
            b.addEventListener('click', function (e) { e.preventDefault(); dlg.showModal(); });
        });
        $$('[data-close-demo]', dlg).forEach(function (b) {
            b.addEventListener('click', function () { dlg.close(); });
        });
        dlg.addEventListener('click', function (e) { if (e.target === dlg) { dlg.close(); } });
    }
})();
