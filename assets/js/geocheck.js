/* ==========================================================================
   GeoCheck: interacciones de la landing rediseñada.
   Sin dependencias. Todo degrada: sin JS la página se lee, se navega y se
   contrata (las pestañas muestran todo, los planes mensuales quedan visibles).
   ========================================================================== */
(function () {
    'use strict';

    var root = document.documentElement;
    var $  = function (s, c) { return (c || document).querySelector(s); };
    var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)');

    /* 0. Tema: sin elección sigue al sistema; forzado claro u oscuro se guarda. */
    var mqDark = window.matchMedia('(prefers-color-scheme: dark)');
    function guardado() {
        try { var t = localStorage.getItem('tema'); return (t === 'dark' || t === 'light') ? t : null; }
        catch (e) { return null; }
    }
    function temaActivo() { return guardado() || (mqDark.matches ? 'dark' : 'light'); }
    function pintarConmutadores() {
        var destino = temaActivo() === 'dark' ? 'claro' : 'oscuro';
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

    /* 1. Barra: borde al hacer scroll y sección activa. Un solo manejador, con rAF. */
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

    /* 2. Menú móvil */
    var burger = $('#navToggle');
    var panel  = $('#navPanel');
    if (burger && panel) {
        var cerrarPanel = function () { panel.classList.remove('is-open'); burger.setAttribute('aria-expanded', 'false'); };
        burger.addEventListener('click', function () {
            var abierto = panel.classList.toggle('is-open');
            burger.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        });
        $$('a', panel).forEach(function (a) { a.addEventListener('click', cerrarPanel); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && panel.classList.contains('is-open')) { cerrarPanel(); burger.focus(); }
        });
        window.addEventListener('resize', function () { if (window.innerWidth >= 1120) { cerrarPanel(); } });
    }

    /* 3. Entrada suave, una sola vez por bloque */
    var revelables = $$('[data-reveal]');
    if (revelables.length) {
        if (!('IntersectionObserver' in window) || reduce.matches) {
            revelables.forEach(function (el) { el.classList.add('is-in'); });
        } else {
            var ob = new IntersectionObserver(function (entradas) {
                entradas.forEach(function (en) {
                    if (en.isIntersecting) { en.target.classList.add('is-in'); ob.unobserve(en.target); }
                });
            }, { rootMargin: '0px 0px -6% 0px', threshold: 0.05 });
            revelables.forEach(function (el) { ob.observe(el); });
        }
    }

    /* 4. Pestañas (periodo de facturación y capacidades). Cada grupo se mueve por separado. */
    $$('[role="tablist"]').forEach(function (lista) {
        var tabs = $$('[role="tab"]', lista);
        if (!tabs.length) { return; }
        var activar = function (tab, alUsuario) {
            tabs.forEach(function (t) {
                var sel = t === tab;
                t.setAttribute('aria-selected', sel ? 'true' : 'false');
                t.setAttribute('tabindex', sel ? '0' : '-1');
                var p = document.getElementById(t.getAttribute('aria-controls'));
                if (p) { p.hidden = !sel; }
            });
            // Centra la pestaña dentro de su propia lista sin mover la página.
            if (alUsuario && lista.scrollWidth > lista.clientWidth && lista.scrollTo) {
                lista.scrollTo({ left: tab.offsetLeft - (lista.clientWidth - tab.offsetWidth) / 2, behavior: reduce.matches ? 'auto' : 'smooth' });
            }
        };
        tabs.forEach(function (t, i) {
            t.addEventListener('click', function () { activar(t, true); });
            t.addEventListener('keydown', function (e) {
                var d = e.key === 'ArrowRight' ? 1 : (e.key === 'ArrowLeft' ? -1 : 0);
                if (e.key === 'Home') { e.preventDefault(); tabs[0].focus(); activar(tabs[0], true); return; }
                if (e.key === 'End')  { e.preventDefault(); tabs[tabs.length - 1].focus(); activar(tabs[tabs.length - 1], true); return; }
                if (!d) { return; }
                e.preventDefault();
                var sig = tabs[(i + d + tabs.length) % tabs.length];
                sig.focus(); activar(sig, true);
            });
        });
        // Sin JS todos los paneles se ven; con JS solo el activo.
        activar($('[aria-selected="true"]', lista) || tabs[0]);
    });

    /* 5. Diálogo del demo */
    var dlg = $('#demoDialog');
    if (dlg && typeof dlg.showModal === 'function') {
        $$('[data-open-demo]').forEach(function (b) {
            b.addEventListener('click', function (e) { e.preventDefault(); dlg.showModal(); });
        });
        $$('[data-close-demo]', dlg).forEach(function (b) { b.addEventListener('click', function () { dlg.close(); }); });
        dlg.addEventListener('click', function (e) { if (e.target === dlg) { dlg.close(); } });
    }
})();

/* Calculadora de tarifa por usuario: descuento de 1% por usuario adicional, con tope. */
(function () {
    'use strict';
    var raiz = document.querySelector('[data-tarifa]');
    if (!raiz) { return; }
    var tarifa, calc = raiz.querySelector('[data-calc]');
    try { tarifa = JSON.parse(raiz.getAttribute('data-tarifa')); } catch (e) { return; }
    if (!calc || !tarifa || !tarifa.base) { return; }
    var entrada = calc.querySelector('input'), lista = calc.querySelector('[data-desg]'),
        total = calc.querySelector('[data-tot]'), perTxt = calc.querySelector('[data-per-txt]'),
        prom = calc.querySelector('[data-prom]'), cta = calc.querySelector('[data-cta]'),
        botones = Array.prototype.slice.call(calc.querySelectorAll('[data-per]')), periodo = 'mensual';
    var fmt = function (n) { var e = Math.round(n) === n; return '$' + n.toLocaleString('es-MX', { minimumFractionDigits: e ? 0 : 2, maximumFractionDigits: 2 }); };
    function calcular(n) {
        var d = Math.min(tarifa.tope_descuento, (n - 1) * tarifa.paso),
            precio = Math.round(tarifa.base * (1 - d) * 100) / 100;
        return { suma: Math.round(n * precio * 100) / 100, precio: precio, desc: d };
    }
    function pintar() {
        var n = Math.max(1, Math.min(100000, parseInt(entrada.value, 10) || 1)), c = calcular(n),
            anual = periodo === 'anual', mult = anual ? tarifa.anual_meses : 1, importe = c.suma * mult;
        lista.innerHTML = '';
        var li = document.createElement('li');
        li.innerHTML = '<span>' + n.toLocaleString('es-MX') + ' × ' + fmt(c.precio) + '</span><span>' + fmt(c.suma) + '</span>';
        lista.appendChild(li);
        var ld = document.createElement('li');
        ld.innerHTML = '<span>Descuento por volumen</span><span>' + (Math.round(c.desc * 1000) / 10).toLocaleString('es-MX') + '%</span>';
        lista.appendChild(ld);
        total.textContent = fmt(importe);
        perTxt.textContent = anual ? 'MXN al año (pagas ' + tarifa.anual_meses + ' meses, ' + (12 - tarifa.anual_meses) + ' son gratis)' : 'MXN al mes';
        prom.textContent = fmt(c.precio) + ' por usuario al mes.';
        cta.href = raiz.getAttribute('data-url') + '&usuarios=' + n + '&periodo=' + periodo;
        cta.firstChild.nodeValue = 'Contratar con ' + n.toLocaleString('es-MX') + (n === 1 ? ' usuario' : ' usuarios');
    }
    botones.forEach(function (b) {
        b.addEventListener('click', function () {
            periodo = b.getAttribute('data-per');
            botones.forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
            pintar();
        });
    });
    entrada.addEventListener('input', pintar);
    calc.hidden = false;
    pintar();
})();

/* Escena del hero: pausa la animación cuando no está a la vista */
(function () {
    var s = document.querySelector('[data-scene]');
    if (!s || !('IntersectionObserver' in window)) return;
    new IntersectionObserver(function (e) { s.classList.toggle('is-paused', !e[0].isIntersecting); }).observe(s);
})();
