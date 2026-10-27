<?php

// Contacto institucional. Se define antes de cargar el kit: el kit solo
// define estas constantes si no existen.
define('ALKES_MAIL',    'soporte@alkes.mx');
define('ALKES_TEL',     '+523339460097');
define('ALKES_TEL_TXT', '333 946 0097');

require_once __DIR__ . '/_planes.php';

if (!defined('GC_SITIO'))     { define('GC_SITIO', 'https://geocheck.alkes.mx'); }
if (!defined('GC_WHATSAPP'))  { define('GC_WHATSAPP', 'https://wa.me/523339460097'); }

/** Redes oficiales de Alkes. */
function gc_redes()
{
    return array(
        array('n' => 'Facebook',  'u' => 'https://www.facebook.com/alkes.mx'),
        array('n' => 'Instagram', 'u' => 'https://www.instagram.com/alkes.mx'),
        array('n' => 'LinkedIn',  'u' => 'https://www.linkedin.com/company/alkes-mx'),
        array('n' => 'TikTok',    'u' => 'https://www.tiktok.com/@alkes.mx'),
    );
}

/** Mensaje de partida para pedir una demostración guiada. */
function gc_url_agendar()
{
    return 'mailto:' . ALKES_MAIL . '?subject=' . rawurlencode('Demostración guiada de GeoCheck');
}

/** Logo en dos versiones; el CSS muestra la que corresponde al tema. */
function gc_logo($px = 34)
{
    $px = (int) $px;
    return '<img class="logo logo--claro" src="assets/img/logo.png" alt="" width="' . $px . '" height="' . $px . '">'
         . '<img class="logo logo--oscuro" src="assets/img/logo-blanco.png" alt="" width="' . $px . '" height="' . $px . '">';
}

/**
 * Captura dentro de una ventana de navegador. Si existe la versión oscura se
 * pintan las dos y el CSS muestra la del tema activo.
 */
function gc_ventana($archivo, $alt, $w, $h, $url = 'app.geocheck.alkes.mx', $carga = 'lazy')
{
    $existe = is_file(__DIR__ . '/' . $archivo);
    $osc    = preg_replace('/\.(png|jpe?g|webp)$/i', '-oscuro.$1', $archivo);
    $hayOsc = $osc !== $archivo && is_file(__DIR__ . '/' . $osc);
    $o  = '<figure class="win">';
    $o .= '<div class="win__bar"><span class="win__dots" aria-hidden="true"><i></i><i></i><i></i></span>';
    $o .= '<span class="win__url">' . a_e($url) . '</span></div>';
    $o .= '<div class="win__shot" style="--ar:' . (int) $w . ' / ' . (int) $h . '">';
    if ($existe) {
        $o .= '<img src="' . a_e($archivo) . '" alt="' . a_e($alt) . '"' . ($hayOsc ? ' data-tema-img="claro"' : '')
            . ' width="' . (int) $w . '" height="' . (int) $h . '" loading="' . a_e($carga) . '" decoding="async">';
        if ($hayOsc) {
            $o .= '<img src="' . a_e($osc) . '" alt="' . a_e($alt) . '" data-tema-img="oscuro"'
                . ' width="' . (int) $w . '" height="' . (int) $h . '" loading="' . a_e($carga) . '" decoding="async">';
        }
    } else {
        $o .= '<div class="win__falta"><b>Captura pendiente</b><code>' . a_e($archivo) . '</code></div>';
    }
    return $o . '</div></figure>';
}

/** Tarifa por usuario (tarifa.json): el descuento sube 1% por cada usuario adicional hasta el tope, y aplica al precio de todos. */
function gc_tarifa()
{
    static $t = null;
    if ($t !== null) { return $t; }
    $t = array('anual_meses' => 10, 'base' => 70, 'paso' => 0.01, 'tope_descuento' => 0.5);
    $f = __DIR__ . '/tarifa.json';
    if (is_file($f)) {
        $j = json_decode((string) @file_get_contents($f), true);
        if (is_array($j) && !empty($j['base'])) { $t = $j + $t; }
    }
    return $t;
}

/** Mensualidad para $n usuarios: descuento de (n-1) × paso, con tope; el precio por usuario va a centavos. */
function gc_calcular($n)
{
    $n = max(1, (int) $n);
    $t = gc_tarifa();
    $d = min((float) $t['tope_descuento'], ($n - 1) * (float) $t['paso']);
    $precio = round((float) $t['base'] * (1 - $d), 2);
    return array('total' => round($n * $precio, 2), 'precio' => $precio, 'n' => $n, 'descuento' => $d);
}

/** Precio por usuario más alto (calculadora y checkout; la publicidad no lo cita). */
function gc_tarifa_max()
{
    return (float) gc_tarifa()['base'];
}

/** Precio por usuario más bajo (el que se anuncia). */
function gc_tarifa_min()
{
    $t = gc_tarifa();
    return round((float) $t['base'] * (1 - (float) $t['tope_descuento']), 2);
}

/** Usuario desde el que aplica el descuento máximo. */
function gc_tarifa_tope_usuarios()
{
    $t = gc_tarifa();
    return (int) ceil((float) $t['tope_descuento'] / (float) $t['paso']) + 1;
}

/** Sección de precios: explicación y calculadora. Sin JS se ve la explicación. */
function gc_tarifas_html($url_base)
{
    $t = gc_tarifa();
    $meses = (int) $t['anual_meses'];
    $pct = rtrim(rtrim(number_format((float) $t['paso'] * 100, 2, '.', ''), '0'), '.');
    $top = (int) round((float) $t['tope_descuento'] * 100);
    $h  = '<div class="tarifa" data-tarifa="' . a_e(json_encode($t, JSON_UNESCAPED_UNICODE)) . '" data-url="' . a_e($url_base) . '">';

    $h .= '<div class="tarifa__card"><p class="eyebrow">Precio por usuario</p>';
    $h .= '<p class="tarifa__desde"><small>Desde</small><b>$' . a_num(gc_tarifa_min()) . '</b><small>MXN por usuario al mes</small></p>';
    $h .= '<h3>Más usuarios, menos pagas por cada uno</h3>';
    $h .= '<ul class="tarifa__reglas">';
    $h .= '<li>' . a_icon('check') . 'Cada usuario adicional baja ' . $pct . '% el precio de todos tus usuarios.</li>';
    $h .= '<li>' . a_icon('check') . 'Arriba de ' . (gc_tarifa_tope_usuarios() - 1) . ' usuarios, obtienes el descuento máximo de ' . $top . '%.</li>';
    $h .= '<li>' . a_icon('check') . 'Sin planes ni topes de ubicaciones o administradores. Empleados y administradores cuentan y cuestan igual.</li>';
    $h .= '<li>' . a_icon('check') . 'Al sumar un usuario se cobra al momento la parte proporcional del periodo. Al reducir tus usuarios contratados, el cobro menor rige desde tu siguiente renovación.</li>';
    $h .= '<li>' . a_icon('check') . 'Con el plan anual solo pagas ' . $meses . ' meses, y los otros ' . (12 - $meses) . ' son gratis.</li>';
    $h .= '</ul></div>';

    $h .= '<div class="tarifa__card tarifa__calc" hidden data-calc>';
    $h .= '<p class="eyebrow">Calcula tu mensualidad</p>';
    $h .= '<label class="tarifa__lbl" for="tarifa-n">¿Cuántos usuarios tendrás?</label>';
    $h .= '<input class="tarifa__in" id="tarifa-n" type="number" inputmode="numeric" min="1" max="100000" value="1">';
    $h .= '<div class="period period--sm" role="group" aria-label="Periodo de facturación">';
    $h .= '<button type="button" data-per="mensual" aria-pressed="true">Mensual</button>';
    $h .= '<button type="button" data-per="anual" aria-pressed="false">Anual<span class="save">−17%</span></button></div>';
    $h .= '<ul class="tarifa__desg" data-desg></ul>';
    $h .= '<p class="tarifa__tot"><span data-tot>$0</span><small data-per-txt>MXN al mes</small></p>';
    $h .= '<p class="tarifa__prom muted" data-prom></p>';
    $h .= '<a class="btn btn--primary btn--block" data-cta href="' . a_e($url_base) . '">Contratar' . a_icon('arrow') . '</a>';
    $h .= '</div>';
    $h .= '</div>';
    return $h;
}

/**
 * Rejilla de planes. Misma lógica de contratación que la del kit; cambia el
 * marcado y se quitan las frases que hablan del tamaño de la organización.
 */
function gc_planes($periodo)
{
    $items = array();
    foreach (a_planes() as $p) {
        if ($p['periodo'] === $periodo || $p['periodo'] === 'unico') { $items[] = $p; }
    }
    if (!$items) {
        return '<p class="muted">Por el momento no hay planes publicados. Escríbenos a '
             . '<a href="mailto:' . ALKES_MAIL . '">' . ALKES_MAIL . '</a>.</p>';
    }

    $clave  = a_producto_clave();
    $ahorro = a_ahorro_anual();
    $h = '<div class="plans">';

    foreach ($items as $p) {
        $rec = !empty($p['recomendado']);
        $per = $p['periodo'] === 'anual' ? 'MXN / año'
             : ($p['periodo'] === 'unico' ? 'MXN / pago único' : 'MXN / mes');

        $h .= '<article class="plan' . ($rec ? ' plan--rec' : '') . '">';
        $h .= '<div class="plan__head"><h3 class="plan__name">' . a_e($p['nombre']) . '</h3>';
        if ($rec) { $h .= '<span class="pill pill--azul">Recomendado</span>'; }
        $h .= '</div>';
        $h .= '<p class="plan__price"><span class="plan__cur">$</span><span class="plan__amt">' . a_num($p['precio'])
            . '</span><span class="plan__per">' . $per . '</span></p>';

        $nota = '';
        if (!empty($p['precio_anterior'])) {
            $nota = 'Antes <s>$' . a_num($p['precio_anterior']) . '</s>';
        } elseif ($p['periodo'] === 'anual') {
            $nota = 'Equivale a $' . a_num(round($p['precio'] / 12)) . ' al mes' . ($ahorro ? '. Ahorras ' . $ahorro . '%.' : '');
        } elseif ($p['periodo'] === 'mensual') {
            $nota = 'Sin plazo forzoso. Cancela cuando quieras.';
        }
        $h .= '<p class="plan__note">' . $nota . '</p>';

        $h .= '<ul class="checks plan__features">';
        foreach ($p['caracteristicas'] as $c) {
            $titulo = $c['titulo'];
            $desc   = !empty($c['descripcion']) ? $c['descripcion'] : '';
            if (stripos($titulo, 'empleados') !== false) { $desc = ''; }          // La descripción, si existe, habla de tamaño.
            $h .= '<li><div><strong>' . a_e($titulo) . '</strong>' . ($desc !== '' ? '<p>' . a_e($desc) . '</p>' : '') . '</div></li>';
        }
        $h .= '</ul>';

        $url = ALKES_CENTRAL . '/?producto=' . urlencode($clave) . '&plan=' . urlencode($p['clave']) . '&periodo=' . urlencode($p['periodo']);
        $h .= '<a class="btn ' . ($rec ? 'btn--primary' : 'btn--line') . ' btn--block" href="' . a_e($url) . '">'
            . 'Contratar ' . a_e($p['nombre']) . a_icon('arrow') . '</a>';
        $h .= '</article>';
    }
    return $h . '</div>';
}

/** Diálogo con las credenciales del demo. */
function gc_dialogo_demo($url_app)
{
    $h  = '<dialog id="demoDialog" aria-labelledby="demoTitulo"><div class="dlg">';
    $h .= '<h2 id="demoTitulo">Acceso al demo</h2>';
    $h .= '<p>Entra a la empresa de demostración con estas credenciales. No captures información real.</p>';
    $h .= '<dl class="dlg__creds"><div><dt>Usuario</dt><dd>demo@alkes.mx</dd></div>';
    $h .= '<div><dt>Contraseña</dt><dd>Demo@Alkes2026</dd></div></dl>';
    $h .= '<p>Para una demostración guiada escríbenos a <a href="' . a_e(gc_url_agendar()) . '">' . ALKES_MAIL . '</a>, '
        . 'llama al <a href="tel:' . ALKES_TEL . '">' . ALKES_TEL_TXT . '</a> o usa '
        . '<a href="' . GC_WHATSAPP . '" target="_blank" rel="noopener">WhatsApp</a>.</p>';
    $h .= '<div class="dlg__foot"><a class="btn btn--primary" href="' . a_e($url_app) . '" target="_blank" rel="noopener">Ir a la app' . a_icon('external') . '</a>';
    $h .= '<button class="btn btn--line" type="button" data-close-demo>Cerrar</button></div>';
    return $h . '</div></dialog>';
}

/** Preguntas frecuentes: la misma lista alimenta la sección y el JSON-LD. */
function gc_faq()
{
    $ahorro = a_ahorro_anual();
    if (!empty($GLOBALS['GC_TARIFA'])) { return gc_faq_tarifa(); }
    return array(
        array('q' => '¿Qué es GeoCheck?',
              'a' => 'Un sistema web de Alkes para controlar la asistencia por ubicación: cada registro valida identidad por NIP, geolocalización GPS contra una geocerca y una foto tomada en el momento.'),
        array('q' => '¿Cuánto cuesta?',
              'a' => 'Desde $799 MXN al mes con el plan Básico, $1,999 con el Profesional y $4,999 con el Empresarial. Los planes anuales ahorran ' . ($ahorro ? $ahorro . '%' : 'frente a pagar mes a mes') . '.'),
        array('q' => '¿Cuántos empleados y ubicaciones incluye cada plan?',
              'a' => 'Hasta 15 empleados activos y 5 ubicaciones en el Básico, hasta 50 y 20 en el Profesional, y hasta 200 y 100 en el Empresarial, con 1, 3 o 10 administradores según el plan.'),
        array('q' => '¿Qué valida cada registro de asistencia?',
              'a' => 'Tres comprobaciones obligatorias e inseparables: NIP alfanumérico de 4 caracteres, ubicación GPS dentro de la geocerca y foto en tiempo real. Si alguna falla, no hay asistencia.'),
        array('q' => '¿Los empleados tienen que descargar una app?',
              'a' => 'No. GeoCheck funciona en el navegador del teléfono, sin descargas de App Store ni Google Play.'),
        array('q' => '¿Se puede subir una foto de la galería?',
              'a' => 'No. La foto se toma en el momento desde la cámara del dispositivo y no se permite subir archivos.'),
        array('q' => '¿GeoCheck rastrea a las personas durante el día?',
              'a' => 'No. El GPS solo se activa durante los segundos exactos del registro; no hay rastreo constante ni ubicación en vivo.'),
        array('q' => '¿Se puede registrar asistencia desde casa?',
              'a' => 'No. El sistema valida presencia física en el sitio de trabajo asignado: fuera de la geocerca no se registra.'),
        array('q' => '¿Qué pasa con los intentos fallidos?',
              'a' => 'También quedan registrados, con el motivo del rechazo, igual que cualquier cambio hecho desde el panel por administradores o supervisores.'),
        array('q' => '¿Puedo probarlo?',
              'a' => 'Sí, con el demo y sus credenciales de prueba.'),
    );
}

/** Preguntas frecuentes con el esquema de precio por usuario. */
function gc_faq_tarifa()
{
    $t = gc_tarifa();
    return array(
        array('q' => '¿Qué es GeoCheck?',
              'a' => 'Un sistema web de Alkes para controlar la asistencia por ubicación: cada registro valida identidad por NIP, geolocalización GPS contra una geocerca y una foto tomada en el momento.'),
        array('q' => '¿Cuánto cuesta?',
              'a' => gc_faq_precio()),
        array('q' => '¿Cómo se calcula mi mensualidad?',
              'a' => gc_faq_calculo()),
        array('q' => '¿Qué pasa si agrego o quito usuarios a mitad del periodo?',
              'a' => gc_faq_altas()),
        array('q' => '¿Hay planes o límites de ubicaciones y administradores?',
              'a' => 'No. El precio depende solo del número de usuarios; tú decides quién es administrador y qué rol tiene cada persona.'),
        array('q' => '¿Qué valida cada registro de asistencia?',
              'a' => 'Tres comprobaciones obligatorias e inseparables: NIP alfanumérico de 4 caracteres, ubicación GPS dentro de la geocerca y foto en tiempo real. Si alguna falla, no hay asistencia.'),
        array('q' => '¿Los empleados tienen que descargar una app?',
              'a' => 'No. GeoCheck funciona en el navegador del teléfono, sin descargas de App Store ni Google Play.'),
        array('q' => '¿Se puede subir una foto de la galería?',
              'a' => 'No. La foto se toma en el momento desde la cámara del dispositivo y no se permite subir archivos.'),
        array('q' => '¿GeoCheck rastrea a las personas durante el día?',
              'a' => 'No. El GPS solo se activa durante los segundos exactos del registro; no hay rastreo constante ni ubicación en vivo.'),
        array('q' => '¿Se puede registrar asistencia desde casa?',
              'a' => 'No. El sistema valida presencia física en el sitio de trabajo asignado: fuera de la geocerca no se registra.'),
        array('q' => '¿Qué pasa con los intentos fallidos?',
              'a' => 'También quedan registrados, con el motivo del rechazo, igual que cualquier cambio hecho desde el panel por administradores o supervisores.'),
        array('q' => '¿Puedo probarlo?',
              'a' => 'Sí, con el demo y sus credenciales de prueba.'),
    );
}


/** Datos estructurados. Todo sale de lo que la página dice; sin reseñas ni calificaciones. */
function gc_jsonld($seo = array())
{
    $ofertas = array(
        '@type' => 'AggregateOffer',
        'priceCurrency' => 'MXN',
        'lowPrice' => (string) gc_tarifa_min(),
        'highPrice' => (string) gc_tarifa_max(),
        'url' => GC_SITIO . '/#precios',
    );
    $faq = array();
    foreach (gc_faq() as $f) {
        $faq[] = array('@type' => 'Question', 'name' => $f['q'],
                       'acceptedAnswer' => array('@type' => 'Answer', 'text' => $f['a']));
    }
    $redes = array();
    foreach (gc_redes() as $r) { $redes[] = $r['u']; }

    $g = array(
        '@context' => 'https://schema.org',
        '@graph' => array(
            array('@type' => 'Organization', '@id' => ALKES_SITIO . '/#org', 'name' => 'Alkes', 'url' => ALKES_SITIO,
                  'email' => ALKES_MAIL, 'telephone' => ALKES_TEL, 'sameAs' => $redes),
            array('@type' => 'WebSite', '@id' => GC_SITIO . '/#web', 'name' => 'GeoCheck', 'url' => GC_SITIO . '/',
                  'inLanguage' => 'es-MX', 'publisher' => array('@id' => ALKES_SITIO . '/#org')),
            array('@type' => 'SoftwareApplication', '@id' => GC_SITIO . '/#app', 'name' => 'GeoCheck',
                  'applicationCategory' => 'BusinessApplication', 'operatingSystem' => 'Web',
                  'description' => 'Control de asistencia por ubicación: cada registro valida NIP, geocerca GPS y foto en tiempo real. Sin checador físico y sin descargar ninguna app.',
                  'url' => GC_SITIO . '/', 'inLanguage' => 'es-MX', 'image' => GC_SITIO . '/assets/img/og-image.png',
                  'publisher' => array('@id' => ALKES_SITIO . '/#org'), 'offers' => $ofertas),
            array('@type' => 'FAQPage', '@id' => GC_SITIO . '/#faq', 'mainEntity' => $faq),
        ),
    );
    $g = seo_enriquecer($g, $seo);
    return json_encode($g, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
}

/** Texto de precio para las preguntas frecuentes. */
function gc_faq_precio()
{
    $t = gc_tarifa();
    $pct = rtrim(rtrim(number_format((float) $t['paso'] * 100, 2, '.', ''), '0'), '.');
    return 'Desde $' . a_num(gc_tarifa_min()) . ' MXN por usuario al mes. Se cobra por usuario con cuenta activa, y cada usuario adicional baja ' . $pct . '% el precio de todos hasta llegar a ' . (int) round((float) $t['tope_descuento'] * 100) . '% de descuento arriba de ' . (gc_tarifa_tope_usuarios() - 1) . ' usuarios. Con el plan anual solo pagas ' . (int) $t['anual_meses'] . ' meses, y los otros ' . (12 - (int) $t['anual_meses']) . ' son gratis.';
}

function gc_faq_calculo()
{
    $c15 = gc_calcular(15); $c50 = gc_calcular(50);
    return 'Todos tus usuarios pagan el mismo precio, que baja con cada usuario que sumas. Con 15 usuarios son 15 × $' . a_num($c15['precio']) . ' = $' . a_num($c15['total']) . ' MXN al mes; con 50 usuarios son 50 × $' . a_num($c50['precio']) . ' = $' . a_num($c50['total']) . ' MXN al mes. La calculadora de la sección de precios te da el total exacto.';
}

function gc_faq_altas()
{
    return 'Al sumar un usuario se cobra al momento la parte proporcional hasta tu próxima factura, y desde la siguiente el precio por usuario se ajusta a tu nuevo total. Desactivar un usuario libera su lugar para otra persona, pero no cambia el cobro: para pagar menos, reduce tus usuarios contratados en Configuración y la reducción rige desde la siguiente renovación; no hay reembolso a mitad del periodo.';
}
