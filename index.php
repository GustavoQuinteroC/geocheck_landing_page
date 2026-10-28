<?php
$NOINDEX = false;
$GC_TARIFA = true;

// Contacto institucional. Se define antes de cargar el kit: el kit solo
// define estas constantes si no existen.
define('ALKES_MAIL',    'soporte@alkes.mx');
define('ALKES_TEL',     '+523339460097');
define('ALKES_TEL_TXT', '333 946 0097');


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

if (!defined('ALKES_SITIO'))    { define('ALKES_SITIO', 'https://alkes.mx'); }
if (!defined('ALKES_CENTRAL'))  { define('ALKES_CENTRAL', 'https://contratar.alkes.mx'); }
if (!defined('ALKES_MAIL'))     { define('ALKES_MAIL', 'gquintero@alkes.mx'); }
if (!defined('ALKES_TEL'))      { define('ALKES_TEL', '+523339460097'); }
if (!defined('ALKES_TEL_TXT'))  { define('ALKES_TEL_TXT', '333 946 0097'); }

/** Escape de todo dato que entre al HTML. */
function a_e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

/**
 * La familia completa. Alimenta la rejilla de ecosistema del pie.
 * Orden fijo en las seis landings: la firma tiene que ser reconocible.
 */
function a_familia()
{
    return array(
        'smartpend' => array('nombre' => 'SmartPend', 'url' => 'https://smartpend.alkes.mx', 'que' => 'Pendientes y proyectos'),
        'geocheck'  => array('nombre' => 'GeoCheck',  'url' => 'https://geocheck.alkes.mx',  'que' => 'Asistencia por ubicación'),
        'mantis'    => array('nombre' => 'Mantis',    'url' => 'https://mantis.alkes.mx',    'que' => 'Mantenimiento de activos'),
        'syspower'  => array('nombre' => 'SysPower',  'url' => 'https://syspower.alkes.mx',  'que' => 'Energía controlada a distancia'),
        'erp'       => array('nombre' => 'Alkes ERP', 'url' => 'https://erp.alkes.mx',       'que' => 'Gestión empresarial y CFDI'),
    );
}

// ---------------------------------------------------------------------------
// Iconos. Trazo 1.75, viewBox 24×24, currentColor. Decorativos siempre.
// ---------------------------------------------------------------------------
function a_icon($nombre, $clase = '')
{
    static $d = null;
    if ($d === null) { $d = array(
    'arrow'      => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    'check'      => '<path d="M20 6 9 17l-5-5"/>',
    'close'      => '<path d="M18 6 6 18M6 6l12 12"/>',
    'chevron'    => '<path d="m6 9 6 6 6-6"/>',
    'menu'       => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    'moon'       => '<path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/>',
    'sun'        => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
    'external'   => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
    'mail'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 7.6 5.3a1.6 1.6 0 0 0 1.8 0L20.5 7"/>',
    'phone'      => '<path d="M7 3h3l2 5-2.5 1.5a12 12 0 0 0 5 5L16 12l5 2v3a2 2 0 0 1-2.2 2A16.5 16.5 0 0 1 4 5.2 2 2 0 0 1 6 3Z"/>',
    'globe'      => '<circle cx="12" cy="12" r="9"/><path d="M3.5 9h17M3.5 15h17M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/>',
    'shield'     => '<path d="M12 3 5 6v5.5c0 4.2 2.9 7.6 7 9.5 4.1-1.9 7-5.3 7-9.5V6Z"/><path d="m9 12 2 2 4-4"/>',
    'play'       => '<circle cx="12" cy="12" r="9"/><path d="M10.5 9 15 12l-4.5 3Z"/>',
    'eye'        => '<path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="3"/>',
    'lock'       => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
    'clock'      => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5.3l3.3 2"/>',
    'history'    => '<path d="M3.5 12a8.5 8.5 0 1 0 2.6-6.1M3.5 5v4h4"/><path d="M12 7.5v5l3.2 1.9"/>',
    'users'      => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-5.5 6-5.5s6 2.2 6 5.5"/><path d="M16 5.3a3.2 3.2 0 0 1 0 6.2M17.5 14.8c2.1.6 3.5 2.3 3.5 4.6"/>',
    'user-check' => '<circle cx="10" cy="8" r="3.2"/><path d="M3.5 20c0-3.3 2.9-5.5 6.5-5.5 1 0 2 .2 2.8.5"/><path d="m15.5 17.5 2 2 4-4"/>',
    'mic'        => '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5.5 11.5a6.5 6.5 0 0 0 13 0M12 18v3"/>',
    'tag'        => '<path d="M3.5 11.2V4.5a1 1 0 0 1 1-1h6.7a1 1 0 0 1 .7.3l8.3 8.3a1 1 0 0 1 0 1.4l-6.7 6.7a1 1 0 0 1-1.4 0L3.8 11.9a1 1 0 0 1-.3-.7Z"/><circle cx="7.8" cy="7.8" r="1.3"/>',
    'form'       => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/>',
    'report'     => '<path d="M6 3h8l5 5v13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1Z"/><path d="M14 3v5h5"/><path d="M9 17v-3M12 17v-5M15 17v-2"/>',
    'calendar'   => '<rect x="3.5" y="5" width="17" height="16" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
    'repeat'     => '<path d="M4 9V8a3 3 0 0 1 3-3h10l-3-3M20 15v1a3 3 0 0 1-3 3H7l3 3"/>',
    'layers'     => '<path d="m12 3 9 5-9 5-9-5Z"/><path d="m3.5 12.5 8.5 4.7 8.5-4.7"/><path d="m3.5 16.8 8.5 4.7 8.5-4.7"/>',
    'filter'     => '<path d="M3.5 5h17l-6.6 7.8V20l-3.8-2.2v-5Z"/>',
    'building'   => '<path d="M4 21V5a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v16"/><path d="M15 10h3a2 2 0 0 1 2 2v9M8 7h3M8 11h3M8 15h3M2.5 21h19"/>',
    'bell'       => '<path d="M6 9a6 6 0 1 1 12 0c0 4 1.5 5.5 2 6H4c.5-.5 2-2 2-6Z"/><path d="M10 19a2 2 0 0 0 4 0"/>',
    'cpu'        => '<rect x="6" y="6" width="12" height="12" rx="2"/><rect x="9.5" y="9.5" width="5" height="5" rx="1"/><path d="M9 3v3M15 3v3M9 18v3M15 18v3M3 9h3M3 15h3M18 9h3M18 15h3"/>',
    'sparkle'    => '<path d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9Z"/><path d="M18.5 16.5 19 18l1.5.5-1.5.5-.5 1.5-.5-1.5L16.5 18l1.5-.5Z"/>',
    'cloud'      => '<path d="M7 18h10.5a3.5 3.5 0 0 0 .4-7A5.5 5.5 0 0 0 7.3 9.6 4.2 4.2 0 0 0 7 18Z"/>',
    'cloud-check'=> '<path d="M7 17.5h10a3.5 3.5 0 0 0 .4-7A5.5 5.5 0 0 0 7.3 9.1 4.2 4.2 0 0 0 7 17.5Z"/><path d="m9.5 13.5 2 2 3.5-3.5"/>',
    'refresh'    => '<path d="M20 11a8 8 0 0 0-13.8-5.2L3.5 8.5"/><path d="M3.5 4v4.5H8"/><path d="M4 13a8 8 0 0 0 13.8 5.2l2.7-2.7"/><path d="M20.5 20v-4.5H16"/>',
    'checklist'  => '<path d="M9 6h11M9 12h11M9 18h11"/><path d="m3.5 6 1.2 1.2L7 5M3.5 12l1.2 1.2L7 11M3.5 18l1.2 1.2L7 17"/>',
    'geo'        => '<path d="M12 21s7-5.7 7-11a7 7 0 1 0-14 0c0 5.3 7 11 7 11Z"/><circle cx="12" cy="10" r="2.7"/>',
    'target'     => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r="1"/>',
    'badge'      => '<rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="9" cy="10.5" r="2.2"/><path d="M5.5 16.5c.5-1.7 1.9-2.6 3.5-2.6s3 .9 3.5 2.6M15 9.5h3.5M15 13h3.5"/>',
    'camera'     => '<path d="M4 7h3l1.5-2h7L17 7h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1Z"/><circle cx="12" cy="13" r="3.6"/>',
    'qr'         => '<rect x="3.5" y="3.5" width="7" height="7" rx="1"/><rect x="13.5" y="3.5" width="7" height="7" rx="1"/><rect x="3.5" y="13.5" width="7" height="7" rx="1"/><path d="M13.5 13.5h3v3h-3zM20.5 13.5v3M17.5 20.5h3M13.5 20.5h1"/>',
    'route'      => '<circle cx="6" cy="18" r="2.5"/><circle cx="18" cy="6" r="2.5"/><path d="M15.5 6H10a4 4 0 0 0 0 8h4a4 4 0 0 1 0 8H8.5"/>',
    'truck'      => '<path d="M3 6h10v10H3zM13 9.5h4l3 3.2V16h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
    'warehouse'  => '<path d="M3 21V8.5L12 4l9 4.5V21"/><path d="M7.5 21v-7h9v7M7.5 17.5h9"/>',
    'factory'    => '<path d="M3 21V10l6 4V10l6 4V6h6v15Z"/><path d="M7 17.5h2M13 17.5h2M18 17.5h1"/>',
    'cart'       => '<path d="M3 4h2.2l2.4 11.2a1.5 1.5 0 0 0 1.5 1.2h8.3a1.5 1.5 0 0 0 1.5-1.2L21 8H6"/><circle cx="9.5" cy="20" r="1.4"/><circle cx="17.5" cy="20" r="1.4"/>',
    'invoice'    => '<path d="M5.5 3h13v18l-2.2-1.5-2.1 1.5-2.2-1.5L9.8 21l-2.1-1.5L5.5 21Z"/><path d="M9 8h6M9 12h6"/>',
    'coins'      => '<ellipse cx="9" cy="7" rx="5.5" ry="2.5"/><path d="M3.5 7v5c0 1.4 2.5 2.5 5.5 2.5s5.5-1.1 5.5-2.5V7"/><path d="M14.5 11.4c3 .2 5.2 1.2 5.2 2.4v4.7c0 1.4-2.5 2.5-5.5 2.5s-5.5-1.1-5.5-2.5v-3.6"/>',
    'server'     => '<rect x="3.5" y="4" width="17" height="6.5" rx="1.5"/><rect x="3.5" y="13.5" width="17" height="6.5" rx="1.5"/><path d="M7 7.2h.01M7 16.8h.01"/>',
    'rack'       => '<rect x="4.5" y="3" width="15" height="18" rx="2"/><path d="M4.5 8.5h15M4.5 14h15M8 5.8h.01M8 11.2h.01M8 17.3h.01"/>',
    'ethernet'   => '<path d="M6 10V6.5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1V10l2 2v5.5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V12Z"/><path d="M8.5 12.5v3M12 12.5v3M15.5 12.5v3"/>',
    'wifi'       => '<path d="M2.5 8.5a15 15 0 0 1 19 0M5.8 12.2a10 10 0 0 1 12.4 0M9.2 15.8a5 5 0 0 1 5.6 0"/><circle cx="12" cy="19.3" r="1.2"/>',
    'power'      => '<path d="M12 3v9"/><path d="M7.3 6.3a8 8 0 1 0 9.4 0"/>',
    'zap'        => '<path d="M13.5 2 5 13.5h6L10.5 22 19 10.5h-6Z"/>',
    'display'    => '<rect x="2.5" y="4" width="19" height="12.5" rx="2"/><path d="M8.5 20.5h7M12 16.5v4"/>',
    'wrench'     => '<path d="M20 5.5a5 5 0 0 1-6.6 6.6L6 19.5a2.1 2.1 0 0 1-3-3l7.4-7.4A5 5 0 0 1 17 2.5l-3 3 1.5 3 3 1.5Z"/>',
    'box'        => '<path d="m12 3 8.5 4.5v9L12 21l-8.5-4.5v-9Z"/><path d="M3.5 7.5 12 12l8.5-4.5M12 12v9"/>',
    'chart'      => '<path d="M4 20V4M4 20h16"/><path d="M8 16.5v-4M12 16.5V8M16 16.5v-6.5"/>',
    'gear'       => '<circle cx="12" cy="12" r="3.2"/><path d="M12 2.5v2.2M12 19.3v2.2M4.2 12H2M22 12h-2.2M6.3 6.3 4.8 4.8M19.2 19.2l-1.5-1.5M6.3 17.7l-1.5 1.5M19.2 4.8l-1.5 1.5"/>',
    'database'   => '<ellipse cx="12" cy="6" rx="7.5" ry="3"/><path d="M4.5 6v12c0 1.7 3.4 3 7.5 3s7.5-1.3 7.5-3V6M4.5 12c0 1.7 3.4 3 7.5 3s7.5-1.3 7.5-3"/>',
    'code'       => '<path d="m8.5 8-5 4 5 4M15.5 8l5 4-5 4M13.5 4.5l-3 15"/>',
    'book'       => '<path d="M4 4.5A1.5 1.5 0 0 1 5.5 3H19v15.5H5.5A1.5 1.5 0 0 0 4 20Z"/><path d="M4 20a1.5 1.5 0 0 0 1.5 1.5H19"/>',
    'search'     => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
    'image'      => '<rect x="3.5" y="4.5" width="17" height="15" rx="2"/><circle cx="8.8" cy="9.8" r="1.6"/><path d="m4 17 4.8-4.5 3.4 3.2 3-2.7 4.8 4.5"/>',
    'alert'      => '<path d="M12 3.5 21 19.5H3Z"/><path d="M12 10v4M12 16.8h.01"/>',
    'restart'    => '<path d="M12 3v5"/><path d="M17.6 6.4a8 8 0 1 1-11.2 0"/>',
    'login'      => '<path d="M14 3.5h4.5a1.5 1.5 0 0 1 1.5 1.5v14a1.5 1.5 0 0 1-1.5 1.5H14"/><path d="M4 12h11M11.5 8.5 15 12l-3.5 3.5"/>',
    'paperclip'  => '<path d="M20 11.5 12.2 19.3a5 5 0 0 1-7-7L12.9 4.4a3.3 3.3 0 0 1 4.7 4.7l-7.7 7.8a1.7 1.7 0 0 1-2.4-2.4l7.2-7.2"/>',
    'github'     => '<path d="M9 19c-4.5 1.4-4.5-2.4-6.5-2.9m13 4.9v-3.5a3 3 0 0 0-.85-2.35c2.85-.3 5.85-1.4 5.85-6.3a4.9 4.9 0 0 0-1.3-3.4 4.6 4.6 0 0 0-.1-3.4s-1.05-.3-3.5 1.3a12 12 0 0 0-6.3 0C6.85.65 5.8.95 5.8.95a4.6 4.6 0 0 0-.1 3.4A4.9 4.9 0 0 0 4.4 7.75c0 4.9 3 6 5.85 6.3a3 3 0 0 0-.85 2.3V20"/>',
    'linkedin'   => '<path d="M15 8.5a5.5 5.5 0 0 1 5.5 5.5v6h-3.5v-6a2 2 0 0 0-4 0v6H9.5V8.8H13v1.9A4.4 4.4 0 0 1 15 8.5Z"/><rect x="2.5" y="8.8" width="3.5" height="11.2"/><circle cx="4.25" cy="4.5" r="1.9"/>',
    ); }
    $p = isset($d[$nombre]) ? $d[$nombre] : $d['check'];
    $c = $clase !== '' ? ' class="' . a_e($clase) . '"' : '';
    return '<svg' . $c . ' viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"'
         . ' stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $p . '</svg>';
}

// ---------------------------------------------------------------------------
// Piezas compartidas
// ---------------------------------------------------------------------------

/** Rejilla del ecosistema para el pie. */
function a_ecosistema_grid($actual)
{
    $h = '<div class="eco-grid" data-reveal>';
    foreach (a_familia() as $k => $p) {
        if ($k === $actual) {
            $h .= '<div class="eco-card" aria-current="page">';
            $h .= '<div><b>' . a_e($p['nombre']) . '</b><span>Estás aquí</span></div></div>';
        } else {
            $h .= '<a class="eco-card" href="' . a_e($p['url']) . '">';
            $h .= '<div><b>' . a_e($p['nombre']) . '</b><span>' . a_e($p['que']) . '</span></div></a>';
        }
    }
    return $h . '</div>';
}

/** Encabezado de sección: índice en el riel + eyebrow + título + entradilla. */
function a_head($idx, $eyebrow, $titulo, $lede = '', $extra = '')
{
    $h  = '<header class="head' . ($extra ? ' ' . a_e($extra) : '') . '" data-reveal>';
    $h .= '<p class="idx head__idx">' . a_e($idx) . '</p>';
    $h .= '<div class="head__main">';
    if ($eyebrow !== '') { $h .= '<p class="eyebrow">' . a_e($eyebrow) . '</p>'; }
    $h .= '<h2>' . $titulo . '</h2>';
    if ($lede !== '')    { $h .= '<p class="lede">' . a_e($lede) . '</p>'; }
    return $h . '</div></header>';
}

/**
 * Hueco de captura.
 * Si el archivo existe se pinta la imagen; si no, un marco con la ruta y las
 * dimensiones que debe tener, para sustituirlo sin adivinar nada.
 */
function a_shot($archivo, $alt, $w, $h, $pie = '', $fig = '', $clase = '')
{
    $ruta   = __DIR__ . '/' . $archivo;
    $existe = is_file($ruta);
    $osc    = preg_replace('/\.(png|jpe?g|webp)$/i', '-oscuro.$1', $archivo);
    $existeOsc = $osc !== $archivo && is_file(__DIR__ . '/' . $osc);
    $o  = '<figure class="shot' . ($existe ? '' : ' shot--pendiente')
        . ($clase ? ' ' . a_e($clase) : '') . '" data-reveal>';
    $o .= '<div class="shot__box ticks">';
    $o .= '<div class="shot__frame" style="--ar: ' . (int) $w . ' / ' . (int) $h . '">';
    if ($existe) {
        $o .= '<img src="' . a_e($archivo) . '" alt="' . a_e($alt) . '"'
            . ($existeOsc ? ' data-theme-img="light"' : '')
            . ' width="' . (int) $w . '" height="' . (int) $h . '" loading="lazy" decoding="async">';
        if ($existeOsc) {
            $o .= '<img src="' . a_e($osc) . '" alt="' . a_e($alt) . '" data-theme-img="dark"'
                . ' width="' . (int) $w . '" height="' . (int) $h . '" loading="lazy" decoding="async">';
        }
    } else {
        $o .= '<div class="shot__pending">' . a_icon('image')
            . '<b>Captura pendiente</b>'
            . '<code>' . a_e($archivo) . '</code>'
            . '<small>' . (int) $w . ' × ' . (int) $h . ' px</small></div>';
    }
    $o .= '</div></div>';
    if ($pie !== '') {
        $o .= '<figcaption class="shot__cap">';
        if ($fig !== '') { $o .= '<span class="mono">' . a_e($fig) . '</span>'; }
        $o .= '<span>' . a_e($pie) . '</span></figcaption>';
    }
    return $o . '</figure>';
}

/** Conmutador de tema. Muestra el icono de lo que va a activar. */
function a_tema($clase = 'theme-toggle')
{
    return '<button class="' . a_e($clase) . '" type="button" data-theme-toggle aria-label="Cambiar tema">'
         . a_icon('moon', 'i-moon') . a_icon('sun', 'i-sun')
         . '<span class="theme-toggle__label"></span></button>';
}

/** Bloque del autor: la persona real que desarrolla y da soporte. */
function a_autor($cita)
{
    $h  = '<div class="author" data-reveal>';
    $h .= '<img class="author__photo" src="https://gustavoquintero.dev/assets/img/my-profile-image.jpg"'
        . ' alt="Retrato de Gustavo Quintero" width="84" height="84" loading="lazy" decoding="async">';
    $h .= '<div><h3>Gustavo Quintero</h3>';
    $h .= '<p class="author__role">Desarrollo y operación</p>';
    $h .= '<blockquote><p>«' . a_e($cita) . '»</p></blockquote>';
    $h .= '<div class="author__links">';
    $h .= '<a href="mailto:atencion@gustavoquintero.dev">' . a_icon('mail') . 'atencion@gustavoquintero.dev</a>';
    $h .= '<a href="https://gustavoquintero.dev" target="_blank" rel="noopener">' . a_icon('globe')
        . 'gustavoquintero.dev' . a_icon('external') . '</a>';
    $h .= '<a href="https://github.com/GustavoQuinteroC" target="_blank" rel="noopener">' . a_icon('github')
        . 'GitHub' . a_icon('external') . '</a>';
    $h .= '<a href="https://www.linkedin.com/in/gustavo-quintero-81aa18169/" target="_blank" rel="noopener">' . a_icon('linkedin')
        . 'LinkedIn' . a_icon('external') . '</a>';
    $h .= '</div></div></div>';
    return $h;
}

/** Diálogo con las credenciales del demo. */
function a_dialogo_demo($url_app)
{
    $h  = '<dialog id="demoDialog" aria-labelledby="demoTitulo">';
    $h .= '<div class="dlg"><h2 id="demoTitulo">Acceso al demo</h2>';
    $h .= '<p>Entra a la empresa de demostración con estas credenciales.</p>';
    $h .= '<dl class="dlg__creds">';
    $h .= '<div><dt>Usuario</dt><dd>demo@alkes.mx</dd></div>';
    $h .= '<div><dt>Contraseña</dt><dd>Demo@Alkes2026</dd></div>';
    $h .= '</dl>';
    $h .= '<p>Para una demostración guiada, contacta a <strong>Gustavo Quintero</strong> al correo '
        . '<a href="mailto:' . ALKES_MAIL . '">' . ALKES_MAIL . '</a> o al teléfono '
        . '<a href="tel:' . ALKES_TEL . '">' . ALKES_TEL_TXT . '</a>.</p>';
    $h .= '<div class="dlg__foot">';
    $h .= '<a class="btn btn--primary" href="' . a_e($url_app) . '" target="_blank" rel="noopener">Ir a la app' . a_icon('external') . '</a>';
    $h .= '<button class="btn btn--outline" type="button" data-close-demo>Cerrar</button>';
    $h .= '</div></div></dialog>';
    return $h;
}


function a_cache()
{
    static $d = null;
    if ($d !== null) { return $d; }
    $f = __DIR__ . '/planes.cache.json';
    $d = array('producto' => array(), 'planes' => array());
    if (is_file($f)) {
        $j = json_decode((string) @file_get_contents($f), true);
        if (is_array($j)) { $d = $j + $d; }
    }
    return $d;
}

function a_planes()         { $d = a_cache(); return isset($d['planes']) ? $d['planes'] : array(); }
function a_producto_clave() { $d = a_cache(); return isset($d['producto']['clave']) ? $d['producto']['clave'] : ''; }
function a_url_panel()      { $d = a_cache(); return isset($d['producto']['url_panel']) ? $d['producto']['url_panel'] : ALKES_SITIO; }

function a_num($n) { return number_format((float) $n, (floor($n) == $n ? 0 : 2), '.', ','); }

/** Traduce los iconos del catálogo (Bootstrap Icons) al set propio. */
function a_map_icon($bi)
{
    $m = array(
        'bi-people-fill' => 'users',            'bi-cpu-fill' => 'cpu',
        'bi-paperclip' => 'paperclip',          'bi-tools' => 'wrench',
        'bi-arrow-up-circle-fill' => 'refresh', 'bi-clock-history' => 'history',
        'bi-shield-lock' => 'lock',             'bi-box-seam' => 'box',
        'bi-box-seam-fill' => 'box',            'bi-card-checklist' => 'checklist',
        'bi-geo-alt-fill' => 'geo',             'bi-person-badge-fill' => 'badge',
        'bi-file-earmark-bar-graph' => 'report','bi-display-fill' => 'display',
        'bi-ethernet' => 'ethernet',            'bi-hdd-rack-fill' => 'rack',
        'bi-cloud-check-fill' => 'cloud-check',
    );
    return isset($m[$bi]) ? $m[$bi] : 'check';
}

/** Ahorro del plan anual frente a doce mensualidades. Calculado, nunca escrito. */
function a_ahorro_anual()
{
    $m = array(); $a = array();
    foreach (a_planes() as $p) {
        if ($p['periodo'] === 'mensual') { $m[$p['clave']] = (float) $p['precio']; }
        if ($p['periodo'] === 'anual')   { $a[$p['clave']] = (float) $p['precio']; }
    }
    $pcts = array();
    foreach ($a as $k => $precio) {
        if (!empty($m[$k]) && $m[$k] > 0) {
            $full = $m[$k] * 12;
            if ($full > $precio) { $pcts[$k] = (int) round((1 - $precio / $full) * 100); }
        }
    }
    return $pcts ? max($pcts) : 0;
}

/** Rejilla de planes de un periodo. Los de pago único aparecen en ambos. */
function a_grid_planes($periodo)
{
    $items = array();
    foreach (a_planes() as $p) {
        if ($p['periodo'] === $periodo || $p['periodo'] === 'unico') { $items[] = $p; }
    }
    if (!$items) {
        return '<p class="center muted">Por el momento no hay planes publicados. '
             . '<a class="link-arrow" href="mailto:' . ALKES_MAIL . '">Escríbenos' . a_icon('arrow') . '</a></p>';
    }

    $clave  = a_producto_clave();
    $ahorro = a_ahorro_anual();
    $h = '<div class="plans">';

    foreach ($items as $p) {
        $rec = !empty($p['recomendado']);
        $per = $p['periodo'] === 'anual' ? 'MXN / año'
             : ($p['periodo'] === 'unico' ? 'MXN / pago único' : 'MXN / mes');

        $h .= '<div class="plan' . ($rec ? ' plan--rec' : '') . '">';
        $h .= '<div class="plan__head"><h3 class="plan__name">' . a_e($p['nombre']) . '</h3>';
        if ($rec) { $h .= '<span class="plan__tag">Recomendado</span>'; }
        $h .= '</div>';

        $h .= '<p class="plan__price"><span class="plan__cur">$</span>'
            . '<span class="plan__amt">' . a_num($p['precio']) . '</span>'
            . '<span class="plan__per">' . $per . '</span></p>';

        $nota = '';
        if (!empty($p['precio_anterior'])) {
            $nota = 'Antes <s>$' . a_num($p['precio_anterior']) . '</s>';
        } elseif ($p['periodo'] === 'anual') {
            $nota = 'Equivale a $' . a_num(round($p['precio'] / 12)) . ' al mes'
                  . ($ahorro ? ' · ahorras ' . $ahorro . ' %' : '');
        } elseif ($p['periodo'] === 'mensual') {
            $nota = 'Sin permanencia. Cancela cuando quieras.';
        }
        $h .= '<p class="plan__note">' . $nota . '</p>';

        $h .= '<ul class="plan__features">';
        foreach ($p['caracteristicas'] as $c) {
            $h .= '<li>' . a_icon(a_map_icon(isset($c['icono']) ? $c['icono'] : ''))
                . '<div><strong>' . a_e($c['titulo']) . '</strong>';
            if (!empty($c['descripcion'])) { $h .= '<p>' . a_e($c['descripcion']) . '</p>'; }
            $h .= '</div></li>';
        }
        $h .= '</ul>';

        $url = ALKES_CENTRAL . '/?producto=' . urlencode($clave)
             . '&plan=' . urlencode($p['clave'])
             . '&periodo=' . urlencode($p['periodo']);

        $h .= '<a class="btn ' . ($rec ? 'btn--primary' : 'btn--outline') . ' btn--block" href="' . a_e($url) . '">'
            . 'Contratar ' . a_e($p['nombre']) . a_icon('arrow') . '</a>';
        $h .= '</div>';
    }
    return $h . '</div>';
}

if (!defined('SEO_FUNDADOR_ID')) { define('SEO_FUNDADOR_ID', 'https://gustavoquintero.dev/#persona'); }

/** Persona fundadora: la misma entidad que describe gustavoquintero.dev. */
function seo_fundador()
{
    return array(
        '@type' => 'Person', '@id' => SEO_FUNDADOR_ID,
        'name' => 'Gustavo de Jesús Quintero Cerpa', 'alternateName' => 'Gustavo Quintero',
        'jobTitle' => 'Fundador y desarrollador de Alkes',
        'url' => 'https://gustavoquintero.dev/',
        'sameAs' => array('https://www.linkedin.com/in/gustavo-quintero-81aa18169/', 'https://github.com/GustavoQuinteroC'),
        'address' => array('@type' => 'PostalAddress', 'addressLocality' => 'Guadalajara', 'addressRegion' => 'Jalisco', 'addressCountry' => 'MX'),
    );
}

/** Versión publicada más reciente, leída del historial de novedades. */
function seo_version($dir)
{
    $t = (string) file_get_contents($dir . '/index.php');
    return preg_match('/disc__v">v(\d+\.\d+\.\d+)</', $t, $m) ? $m[1] : '';
}

/**
 * Lista de funciones y capturas a partir de los arreglos de la página.
 * Acepta $CAPACIDADES (con 'items' e 'img'), $SOSTIENE y $AVANZADAS.
 */
function seo_de_pagina($sitio, $capacidades, $extras = array())
{
    $funciones = array();
    $capturas = array();
    foreach ((array) $capacidades as $c) {
        if (!empty($c['h3'])) { $funciones[] = $c['h3']; }
        foreach ((array) (isset($c['items']) ? $c['items'] : array()) as $it) {
            if (is_array($it) && isset($it[1])) { $funciones[] = $it[1]; }
        }
        if (!empty($c['img'])) {
            $capturas[] = array_filter(array('@type' => 'ImageObject', 'url' => $sitio . '/' . ltrim($c['img'], '/'),
                                'caption' => isset($c['alt']) ? $c['alt'] : '',
                                'width' => isset($c['w']) ? (int) $c['w'] : null, 'height' => isset($c['h']) ? (int) $c['h'] : null),
                                function ($v) { return $v !== null && $v !== ''; });
        }
    }
    foreach ((array) $extras as $lista) {
        foreach ((array) $lista as $it) {
            if (is_array($it) && isset($it[1])) { $funciones[] = $it[1]; }
        }
    }
    $funciones = array_values(array_unique(array_filter($funciones)));
    return array('funciones' => $funciones, 'capturas' => $capturas);
}

/** Etiquetas extra del <head>. $s: sitio, nombre, titulo, alt, palabras, noindex. */
function seo_head($s)
{
    $e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
    $sitio = $s['sitio'];
    $og = $sitio . '/assets/img/og-image.png';
    $out = array();
    if (empty($s['noindex'])) {
        $out[] = '<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">';
        $out[] = '<meta name="googlebot" content="index, follow, max-image-preview:large, max-snippet:-1">';
        $out[] = '<meta name="bingbot" content="index, follow, max-image-preview:large, max-snippet:-1">';
    }
    $out[] = '<meta name="keywords" content="' . $e(implode(', ', $s['palabras'])) . '">';
    $out[] = '<meta name="application-name" content="' . $e($s['nombre']) . '">';
    $out[] = '<meta name="apple-mobile-web-app-title" content="' . $e($s['nombre']) . '">';
    $out[] = '<meta name="creator" content="Gustavo Quintero">';
    $out[] = '<meta name="publisher" content="Alkes">';
    $out[] = '<meta name="classification" content="Software empresarial en la nube">';
    $out[] = '<meta name="language" content="es-MX">';
    $out[] = '<meta name="geo.region" content="MX-JAL">';
    $out[] = '<meta name="geo.placename" content="Guadalajara, Jalisco, México">';
    $out[] = '<meta name="geo.position" content="20.6597;-103.3496">';
    $out[] = '<meta name="ICBM" content="20.6597, -103.3496">';
    $out[] = '<link rel="alternate" hreflang="es-MX" href="' . $sitio . '/">';
    $out[] = '<link rel="alternate" hreflang="es" href="' . $sitio . '/">';
    $out[] = '<link rel="alternate" hreflang="x-default" href="' . $sitio . '/">';
    $out[] = '<link rel="alternate" type="text/markdown" title="Resumen para modelos de lenguaje" href="' . $sitio . '/llms.txt">';
    $out[] = '<link rel="sitemap" type="application/xml" href="' . $sitio . '/sitemap.xml">';
    $out[] = '<link rel="author" href="https://gustavoquintero.dev/">';
    $out[] = '<meta property="og:image:secure_url" content="' . $og . '">';
    $out[] = '<meta property="og:image:type" content="image/png">';
    $out[] = '<meta name="twitter:image:alt" content="' . $e($s['alt']) . '">';
    return implode("\n", $out) . "\n";
}

/**
 * Enriquece el grafo JSON-LD que arma cada landing.
 * $s: sitio, nombre, subcategoria, palabras, version, funciones, capturas,
 *     dispositivos, requisitos, titulo.
 */
function seo_enriquecer($g, $s)
{
    if (empty($s) || empty($g['@graph'])) { return $g; }
    $sitio = $s['sitio'];
    $org = ALKES_SITIO . '/#org';
    foreach ($g['@graph'] as $i => $n) {
        $tipo = isset($n['@type']) ? $n['@type'] : '';
        if ($tipo === 'Organization') {
            $g['@graph'][$i] = array_merge($n, array(
                'alternateName' => array('Alkes Software y Soluciones TI', 'Alkes TI'),
                'description' => 'Empresa de software y soluciones TI en Guadalajara, Jalisco. Desarrolla y opera sistemas en la nube para empresas mexicanas: ERP con CFDI 4.0, pendientes con IA, control de asistencia, mantenimiento y reinicio remoto de equipos.',
                'logo' => array('@type' => 'ImageObject', 'url' => ALKES_SITIO . '/assets/img/logo_alkes.png'),
                'address' => array('@type' => 'PostalAddress', 'addressLocality' => 'Guadalajara', 'addressRegion' => 'Jalisco', 'addressCountry' => 'MX'),
                'areaServed' => array('@type' => 'Country', 'name' => 'México'),
                'founder' => array('@id' => SEO_FUNDADOR_ID),
                'foundingLocation' => array('@type' => 'Place', 'name' => 'Guadalajara, Jalisco, México'),
                'contactPoint' => array('@type' => 'ContactPoint', 'contactType' => 'Ventas y soporte', 'telephone' => ALKES_TEL,
                                        'email' => ALKES_MAIL, 'areaServed' => 'MX', 'availableLanguage' => array('es')),
                'knowsAbout' => array('Software empresarial', 'ERP', 'Facturación electrónica CFDI 4.0', 'Gestión de pendientes',
                                      'Control de asistencia', 'Mantenimiento preventivo', 'Internet de las cosas', 'Inteligencia artificial'),
            ));
        } elseif ($tipo === 'SoftwareApplication') {
            $extra = array(
                'applicationSubCategory' => $s['subcategoria'],
                'keywords' => implode(', ', $s['palabras']),
                'countriesSupported' => 'MX',
                'availableOnDevice' => $s['dispositivos'],
                'browserRequirements' => $s['requisitos'],
                'provider' => array('@id' => $org),
                'creator' => array('@id' => SEO_FUNDADOR_ID),
                'author' => array('@id' => SEO_FUNDADOR_ID),
                'isAccessibleForFree' => false,
                'mainEntityOfPage' => array('@id' => $sitio . '/#pagina'),
            );
            if (!empty($s['version'])) {
                $extra['softwareVersion'] = $s['version'];
                $extra['releaseNotes'] = $sitio . '/#novedades';
            }
            if (!empty($s['funciones'])) { $extra['featureList'] = $s['funciones']; }
            if (!empty($s['capturas'])) { $extra['screenshot'] = $s['capturas']; }
            $g['@graph'][$i] = array_merge($n, $extra);
        } elseif ($tipo === 'WebSite') {
            $g['@graph'][$i] = array_merge($n, array(
                'description' => $s['titulo'],
                'creator' => array('@id' => SEO_FUNDADOR_ID),
                'copyrightHolder' => array('@id' => $org),
            ));
        }
    }
    $g['@graph'][] = array(
        '@type' => 'WebPage', '@id' => $sitio . '/#pagina', 'url' => $sitio . '/', 'name' => $s['titulo'],
        'isPartOf' => array('@id' => $sitio . '/#web'), 'about' => array('@id' => $sitio . '/#app'),
        'mainEntity' => array('@id' => $sitio . '/#app'), 'inLanguage' => 'es-MX',
        'primaryImageOfPage' => array('@type' => 'ImageObject', 'url' => $sitio . '/assets/img/og-image.png', 'width' => 1200, 'height' => 630),
        'breadcrumb' => array('@id' => $sitio . '/#migas'),
        'dateModified' => date('Y-m-d', (int) @filemtime(__DIR__ . '/index.php')),
        'publisher' => array('@id' => $org),
    );
    $g['@graph'][] = array(
        '@type' => 'BreadcrumbList', '@id' => $sitio . '/#migas',
        'itemListElement' => array(
            array('@type' => 'ListItem', 'position' => 1, 'name' => 'Alkes', 'item' => ALKES_SITIO . '/'),
            array('@type' => 'ListItem', 'position' => 2, 'name' => $s['nombre'], 'item' => $sitio . '/'),
        ),
    );
    $g['@graph'][] = seo_fundador();
    return $g;
}


$AHORRO  = a_ahorro_anual();
$URL_APP = a_url_panel();
$PROD    = 'geocheck';

$DESDE_TXT = '$' . a_num(gc_tarifa_min());

$TITULO  = 'GeoCheck: control de asistencia por ubicación | Alkes';
$DESC    = 'Control de asistencia para equipos en campo: cada registro valida NIP, geocerca GPS y foto en tiempo real. Sin checador físico. Desde ' . $DESDE_TXT . ' MXN por usuario al mes. Pruébalo.';
$DESC_OG = 'Control de asistencia para equipos en campo: cada registro valida NIP, geocerca GPS y foto en tiempo real. Desde ' . $DESDE_TXT . ' MXN por usuario al mes.';

/* Capacidades en pestañas: icono, título y descripción de cada punto. */
$CAPACIDADES = array(
    array('id' => 'validacion', 'tab' => 'Validación', 'eyebrow' => 'Tecnología validadora',
        'h3' => 'Tres comprobaciones que no se pueden separar',
        'p' => 'Cada registro se somete a las tres. Si alguna falla, no hay asistencia.',
        'items' => array(
            array('lock', 'Identidad única', 'Acceso mediante NIP alfanumérico de 4 caracteres.'),
            array('geo', 'Ubicación GPS', 'Validación contra ubicaciones (geocercas) predefinidas. Si no estás en el radio permitido, no registras.'),
            array('camera', 'Evidencia visual', 'Foto obligatoria en tiempo real desde la cámara del dispositivo. No se permite subir archivos.'),
        ),
        'img' => 'assets/img/registro.png', 'alt' => 'Pantalla de registro de asistencia de GeoCheck en un teléfono, con NIP, cámara y GPS activos', 'w' => 840, 'h' => 1800, 'tall' => true,
        'cap' => 'Registro de asistencia desde el navegador del teléfono'),
    array('id' => 'operacion', 'tab' => 'Operación del día', 'eyebrow' => 'Operación del día',
        'h3' => 'El supervisor ve lo que pasó, no lo que le contaron',
        'p' => 'Cada registro llega con su hora, su ubicación validada y su fotografía. Los intentos fallidos también quedan, con el motivo del rechazo.',
        'items' => array(
            array('geo', 'Geocercas por ubicación de trabajo', 'Define el sitio y el radio permitido para cada equipo o sucursal.'),
            array('camera', 'Evidencia no editable', 'La foto se toma en el momento; no se pueden subir archivos ya existentes.'),
            array('report', 'Reportes exportables', 'Reportes avanzados de la asistencia, listos para exportar.'),
        ),
        'img' => 'assets/img/checadas.jpg', 'alt' => 'Listado de checadas de GeoCheck con su resultado, su nivel de riesgo y si hubo retardo', 'w' => 1568, 'h' => 768,
        'cap' => 'Checadas con su resultado, su riesgo y su retardo'),
    array('id' => 'seguimiento', 'tab' => 'Seguimiento', 'eyebrow' => 'Seguimiento',
        'h3' => 'Calendario de asistencia, avisos y notas',
        'p' => 'Asistencias, retardos, faltas e incidencias de cada día en un solo lugar, y la campana te avisa de lo que importa.',
        'items' => array(
            array('calendar', 'Calendario de asistencia', 'Asistencias, retardos, faltas e incidencias por autorizar de cada día, en vista de mes, semana o lista.'),
            array('bell', 'Avisos que tú eliges', 'Incidencias nuevas, retardos, checadas sospechosas, cierre del día y notas; cada usuario decide cuáles recibe.'),
            array('form', 'Notas en checadas e incidencias', 'Una aclaración sobre un retardo o lo que falta para autorizar una incidencia queda escrita en el propio registro.'),
        ),
        'img' => 'assets/img/calendario.jpg', 'alt' => 'Calendario de asistencia de GeoCheck con asistencias, retardos e incidencias', 'w' => 1568, 'h' => 768,
        'cap' => 'Calendario de asistencia por día'),
    array('id' => 'privacidad', 'tab' => 'Privacidad', 'eyebrow' => 'Privacidad y claridad',
        'h3' => 'Lo que GeoCheck no es',
        'p' => 'Respetamos la privacidad de la persona mientras garantizamos el cumplimiento laboral. Es un sistema de verdad, no un sistema de espionaje.',
        'items' => array(
            array('camera', 'No es biometría facial invasiva', 'La foto es solo evidencia del momento del registro y se guarda 2 años. No creamos bases de datos biométricas ni reconocemos rostros constantemente.'),
            array('route', 'No es rastreo constante', 'El GPS solo se activa durante los segundos exactos del registro. No rastreamos movimientos todo el día ni mostramos ubicación en vivo.'),
            array('building', 'No permite trabajo desde casa', 'El sistema valida estrictamente la presencia física en el sitio de trabajo asignado.'),
        ),
        'img' => 'assets/img/checada.jpg', 'alt' => 'Detalle de una checada de GeoCheck con su evidencia fotográfica y su validación de geocerca', 'w' => 995, 'h' => 570,
        'cap' => 'Detalle de una checada: evidencia y validación de geocerca'),
);

$SOSTIENE = array(
    array('building', 'Personal en obra', 'Valida que los operarios y albañiles están físicamente en el terreno de construcción.'),
    array('route', 'Vendedores externos', 'Control de visitas a clientes y puntos de venta. Reportes de ruta y presencia.'),
    array('wrench', 'Técnicos de servicio', 'Soporte técnico, mantenimiento y reparaciones. Validación en domicilio del cliente.'),
    array('truck', 'Logística y reparto', 'Control de salida y llegada de mercancía en almacenes y puntos de entrega.'),
    array('factory', 'Sucursales', 'Gestión centralizada de múltiples sucursales: control de personal, horarios y reportes por ubicación.'),
    array('calendar', 'Eventos y servicios temporales', 'Control de personal en eventos, ferias y sedes temporales; gestión de turnos y presencia por ubicación.'),
);

$AVANZADAS = array(
    array('search', 'Búsqueda en todo el sistema', 'Escribe una persona, una ubicación, un horario o una incidencia en la barra de arriba; solo aparece lo que tú puedes abrir.'),
    array('camera', 'Evidencia en grande', 'La foto de cada checada se abre en un visor a pantalla completa, sin salir del registro.'),
    array('report', 'Imprime como documento', 'Reportes y checadas salen con el logo y el nombre de tu empresa, sin menús ni botones en el papel.'),
    array('display', 'Apariencia a tu gusto', 'Tema claro, oscuro o automático, 20 colores de énfasis y un modo compacto para ver más filas.'),
    array('user-check', 'Mi perfil y ajustes', 'Tus datos y tu foto, cambio de contraseña, apariencia, avisos y actividad, sin salir de lo que haces.'),
    array('book', 'Manual dentro del sistema', 'La ayuda se abre en la pantalla donde estás, con buscador.'),
);

function gc_items($lista)
{
    $h = '';
    foreach ($lista as $i) {
        $h .= '<div class="item">' . a_icon($i[0]) . '<div><h4>' . a_e($i[1]) . '</h4><p>' . a_e($i[2]) . '</p></div></div>';
    }
    return $h;
}
?>
<?php
// SEO y GEO: datos para seo_head() y seo_enriquecer() (ver seo_head).
$SEO_PAG = seo_de_pagina(GC_SITIO, isset($CAPACIDADES) ? $CAPACIDADES : array(), array($SOSTIENE, $AVANZADAS));
$SEO = array(
    'sitio' => GC_SITIO, 'nombre' => 'GeoCheck', 'titulo' => $TITULO, 'noindex' => $NOINDEX,
    'subcategoria' => 'Control de asistencia y personal', 'dispositivos' => 'Computadora, teléfono y tableta', 'requisitos' => 'Navegador web moderno con conexión a internet',
    'alt' => 'Vista de GeoCheck, software en la nube de Alkes',
    'palabras' => array('control de asistencia', 'checador en línea', 'reloj checador GPS', 'checador con geocerca', 'asistencia con foto', 'control de personal en campo', 'registro de entradas y salidas', 'retardos e incidencias', 'checador sin hardware', 'asistencia por ubicación', 'software de recursos humanos', 'sistema en la nube', 'SaaS México', 'software Guadalajara', 'Alkes'),
    'version' => seo_version(__DIR__), 'funciones' => $SEO_PAG['funciones'], 'capturas' => $SEO_PAG['capturas'],
);
?>
<!doctype html>
<html lang="es" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= a_e($TITULO) ?></title>

<meta name="description" content="<?= a_e($DESC) ?>">
<meta name="author" content="Alkes">
<meta name="theme-color" content="#061c6b">
<meta name="format-detection" content="telephone=no">
<?php if ($NOINDEX): ?><meta name="robots" content="noindex,nofollow">
<?php endif; ?>
<link rel="canonical" href="<?= GC_SITIO ?>/">

<meta property="og:type" content="website">
<meta property="og:site_name" content="GeoCheck">
<meta property="og:url" content="<?= GC_SITIO ?>/">
<meta property="og:title" content="<?= a_e($TITULO) ?>">
<meta property="og:description" content="<?= a_e($DESC_OG) ?>">
<meta property="og:image" content="<?= GC_SITIO ?>/assets/img/og-image.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="GeoCheck, sistema de control de asistencia por ubicación">
<meta property="og:locale" content="es_MX">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= a_e($TITULO) ?>">
<meta name="twitter:description" content="<?= a_e($DESC_OG) ?>">
<meta name="twitter:image" content="<?= GC_SITIO ?>/assets/img/og-image.png">
<?= seo_head($SEO) ?>
<link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
<link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">

<link rel="preload" href="assets/fonts/lexend-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="assets/fonts/source-sans-3-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="assets/css/geocheck.css">

<script type="application/ld+json"><?= gc_jsonld($SEO) ?></script>

<script>
(function () {
    var r = document.documentElement;
    r.className = r.className.replace('no-js', 'js');
    try {
        var t = localStorage.getItem('tema');
        if (t === 'dark' || t === 'light') { r.setAttribute('data-theme', t); }
    } catch (e) {}
})();
</script>
</head>
<body>

<a class="skip" href="#main">Saltar al contenido</a>

<header class="nav" id="nav">
    <div class="container nav__in">
        <a class="brand" href="#inicio"><?= gc_logo(34) ?><span>GeoCheck</span></a>
        <a class="brand__by" href="<?= ALKES_SITIO ?>">por <strong>Alkes</strong></a>
        <nav class="nav__links" aria-label="Secciones">
            <a class="nav__link" href="#producto">Producto</a>
            <a class="nav__link" href="#capacidades">Capacidades</a>
            <a class="nav__link" href="#precios">Precios</a>
            <a class="nav__link" href="#preguntas">Preguntas</a>
            <a class="nav__link" href="#novedades">Novedades</a>
            <a class="nav__link" href="#contacto">Contacto</a>
        </nav>
        <div class="nav__actions">
            <?= a_tema() ?>
            <a class="btn btn--line btn--sm" href="<?= a_e($URL_APP) ?>">Iniciar sesión</a>
            <a class="btn btn--primary btn--sm" href="#precios">Ver precios</a>
        </div>
        <button class="nav__burger" type="button" id="navToggle" aria-expanded="false" aria-controls="navPanel" aria-label="Abrir menú">
            <?= a_icon('menu', 'i-menu') ?><?= a_icon('close', 'i-close') ?>
        </button>
    </div>
    <div class="nav__panel container" id="navPanel">
        <a class="nav__link" href="#producto">Producto</a>
        <a class="nav__link" href="#capacidades">Capacidades</a>
        <a class="nav__link" href="#precios">Precios</a>
        <a class="nav__link" href="#preguntas">Preguntas</a>
        <a class="nav__link" href="#novedades">Novedades</a>
        <a class="nav__link" href="#contacto">Contacto</a>
        <a class="nav__link" href="#ecosistema">Ecosistema Alkes</a>
        <div class="nav__panel-actions">
            <?= a_tema() ?>
            <a class="btn btn--line" href="<?= a_e($URL_APP) ?>">Iniciar sesión</a>
            <a class="btn btn--primary" href="#precios">Ver precios</a>
        </div>
    </div>
</header>

<main id="main">

<!-- Hero ------------------------------------------------------------------ -->
<section class="hero" id="inicio">
    <div class="container">
        <div class="hero__grid">
            <div class="hero__body">
                <p class="eyebrow" data-reveal>Control de asistencia por ubicación para equipos en campo</p>
                <h1 data-reveal>Si no está en el sitio, no <em>registra</em></h1>
                <p class="lede" data-reveal>Cada asistencia se valida con tres comprobaciones obligatorias e inseparables: NIP, GPS contra una geocerca y foto en tiempo real. Sin checador físico. Desde <?= a_e($DESDE_TXT) ?> MXN por usuario al mes.</p>
                <div class="row" data-reveal>
                    <a class="btn btn--primary btn--lg" href="<?= a_e($URL_APP) ?>" data-open-demo><?= a_icon('play') ?>Probar el demo</a>
                    <a class="btn btn--line btn--lg" href="#precios">Ver precios</a>
                </div>
                <div class="row" data-reveal>
                    <a class="link" href="<?= a_e(gc_url_agendar()) ?>">Agendar demostración guiada<?= a_icon('arrow') ?></a>
                    <a class="link" href="#novedades">Ver novedades<?= a_icon('arrow') ?></a>
                </div>
                <ul class="hero__facts" data-reveal>
                    <li><?= a_icon('check') ?>Sin checador físico</li>
                    <li><?= a_icon('check') ?>Funciona en el navegador</li>
                    <li><?= a_icon('check') ?>Evidencia no editable</li>
                </ul>
            </div>
            <div class="hero__aside" data-reveal>
                <div class="scene" data-scene>
  <svg class="scene__svg" viewBox="0 0 560 420" role="img" aria-label="Animación: una persona llega a su lugar de trabajo y cruza la geocerca; en el celular se valida su ubicación, escribe su NIP, se toma una selfie y aparece la checada registrada.">
    <defs><clipPath id="sc-tr"><rect x="384" y="200" width="48" height="80" rx="14"/></clipPath><clipPath id="p2-tr"><path d="M387 213Q387 200 401 200H417Q431 200 431 213L428 277H390Z"/></clipPath></defs>
    <rect class="sc-wall" x="0" y="0" width="560" height="330"/>
    <circle class="gc-sun" cx="290" cy="64" r="22"/><circle class="gc-sunr" cx="290" cy="64" r="32"/>
    <g class="gc-cloud"><circle class="gc-cl" cx="70" cy="62" r="14"/><circle class="gc-cl" cx="90" cy="54" r="18"/><circle class="gc-cl" cx="114" cy="62" r="14"/><rect class="gc-cl" x="62" y="62" width="64" height="14" rx="7"/></g>
    <g class="gc-cloud2"><circle class="gc-cl" cx="196" cy="34" r="10"/><circle class="gc-cl" cx="210" cy="28" r="13"/><circle class="gc-cl" cx="228" cy="34" r="10"/><rect class="gc-cl" x="188" y="34" width="48" height="10" rx="5"/></g>
    <rect class="sc-floor" x="0" y="330" width="560" height="90"/><path class="sc-line" d="M0 330H560"/>
    <path class="sc-tile" d="M0 360H560M0 392H560M120 330L72 420M290 330L284 420M450 330L500 420"/>
    <g class="gc-all">
      <rect class="sc-b" x="30" y="92" width="232" height="238" rx="6"/>
      <rect class="sc-sh" x="170" y="92" width="92" height="238" rx="6"/>
      <rect class="gc-roof" x="22" y="82" width="248" height="16" rx="5"/>
      <rect class="sc-sky" x="52" y="120" width="40" height="30" rx="4"/><rect class="sc-winf" x="52" y="120" width="40" height="30" rx="4" style="stroke-width:1.6"/><rect class="sc-sky" x="176" y="120" width="40" height="30" rx="4"/><rect class="sc-winf" x="176" y="120" width="40" height="30" rx="4" style="stroke-width:1.6"/><rect class="sc-sky" x="52" y="166" width="40" height="30" rx="4"/><rect class="sc-winf" x="52" y="166" width="40" height="30" rx="4" style="stroke-width:1.6"/><rect class="sc-sky" x="176" y="166" width="40" height="30" rx="4"/><rect class="sc-winf" x="176" y="166" width="40" height="30" rx="4" style="stroke-width:1.6"/>
      <rect class="gc-sign" x="98" y="118" width="96" height="26" rx="6"/><text class="sc-txt" x="146" y="136" text-anchor="middle">OFICINA</text>
      <rect class="gc-door" x="118" y="244" width="56" height="86" rx="4"/><path class="gc-doorl" d="M146 244V330M118 280H174"/>
      <rect class="gc-awn" x="106" y="232" width="80" height="14" rx="4"/>
      <circle class="gc-lamp" cx="198" cy="270" r="4"/>
            <ellipse class="gc-fence" cx="146" cy="340" rx="176" ry="32"/>
      <ellipse class="gc-ping" cx="146" cy="340" rx="176" ry="32"/>
      <text class="gc-fl" x="146" y="388" text-anchor="middle">Geocerca</text>
      <g class="gc-pin"><circle class="gc-pr" cx="146" cy="66" r="10"/><path class="gc-pinb" d="M146 80c-9-11-14-17-14-24a14 14 0 0 1 28 0c0 7-5 13-14 24z"/><circle class="gc-pinc" cx="146" cy="56" r="5"/></g>
      
      <g class="gc-walk"><ellipse class="sc-shd" cx="408" cy="333" rx="36" ry="5"/><g class="gp" transform="translate(0,0)">
      <g class="sc-armb gp-b"><path class="p2-sleeve" style="stroke-width:13" d="M426 220L398 226"/><path class="p2-fore" style="stroke-width:9" d="M398 226L373 218.500"/><rect class="sc-phone" x="354" y="196" width="14" height="26" rx="3"/><rect class="sc-screen" x="356.500" y="199" width="9" height="19" rx="1.500"/><ellipse class="p2-skin" cx="369.500" cy="217.500" rx="6" ry="5.200" transform="rotate(-14 369.500 217.500)"/></g>
      <g class="sc-legl gp-ll">
        <path class="sc-pant" d="M390.500 270H405.500L404 323H391.500Z"/>
        <path class="sc-shoe" d="M406 322L392 322Q386 324 382 329Q380.500 332.500 385 332.500H408Q411.500 332.500 411.500 329Q411.500 324 406 322Z"/>
        <path class="p2-sole" d="M381.500 330.200H411.200L411.400 332.500H384Q381.600 332.500 381.500 330.200Z"/>
      </g>
      <g class="sc-legr gp-lr">
        <path class="sc-pant" d="M411.500 270H426.500L425 323H412.500Z"/>
        <path class="p2-shade" d="M411.500 270H426.500L425 323H412.500Z"/>
        <path class="sc-shoe" d="M427 322L413 322Q407 324 403 329Q401.500 332.500 406 332.500H429Q432.500 332.500 432.500 329Q432.500 324 427 322Z"/>
        <path class="p2-sole" d="M402.500 330.200H432.200L432.400 332.500H405Q402.600 332.500 402.500 330.200Z"/>
      </g>
      <g class="sc-body">
        <path class="sc-shirt" d="M387 213Q387 200 401 200H417Q431 200 431 213L428 277H390Z"/>
        <path class="sc-shirt2" clip-path="url(#p2-tr)" d="M414 196H436V281H408Q415 240 414 196Z"/>
        <path class="p2-placket" d="M408 211V268"/>
        <path class="sc-belt" d="M390 268H428L428.200 275H389.800Z"/><rect class="sc-buckle" x="404" y="268" width="8" height="7" rx="1.500"/>
        
        <path class="p2-lan" d="M405 211L411 233M417 211L412 233"/><rect class="p2-badge" x="406" y="232" width="12" height="15" rx="2.500"/><rect class="p2-badgel" x="408.500" y="235" width="7" height="2.200" rx="1.100"/><rect class="p2-badgel" style="opacity:.45" x="408.500" y="239.500" width="7" height="1.800" rx=".9"/>
        <path class="p2-skin" d="M401.500 187H414.500V201Q408 207 401.500 201Z"/>
        <path class="p2-shade" d="M401.500 187H414.500V193Q408 197 401.500 193Z"/>
        <path class="p2-collar" d="M399.500 200.500L408 213L416.500 200.500L412 199Q408 203 404 199Z"/>
        <ellipse class="p2-skin" cx="408" cy="175" rx="15.500" ry="17.500"/>
        <path class="p2-hair" d="M392.500 176Q392 170 394 167L423.500 167Q425.500 172 424.500 178Q423 172 418 171Z" style="opacity:.9"/><path class="sc-hat" d="M390 172C389.500 158 397.500 150 408 150C418.500 150 426.500 158 426 172Z"/><path class="p2-shade" d="M408 150C418.500 150 426.500 158 426 172H415Q416 158 408 150Z"/><rect class="sc-hat" x="385" y="169.500" width="46" height="5.500" rx="2.700"/><rect class="sc-lamp" x="389" y="160" width="8" height="7" rx="2"/>
        <ellipse class="p2-skin" cx="418.200" cy="180" rx="3.200" ry="4.200"/>
        <path class="p2-shade" d="M416.800 178Q419.500 180 417 183"/>
        <ellipse class="p2-skin" cx="392.600" cy="180.400" rx="2.900" ry="3.300"/>
        <circle class="p2-blush" cx="397" cy="183.500" r="3.400"/>
        <g class="sc-eyeg gp-eye"><ellipse class="p2-eyew" cx="401.200" cy="177" rx="3.100" ry="3.300"/><circle class="p2-iris" cx="400.200" cy="177.300" r="1.900"/><circle class="p2-eyew" cx="399.500" cy="176.500" r=".65"/></g>
        <path class="p2-line" d="M396.500 172.300Q401 169.800 405.500 171.300"/>
        <path class="p2-mouth sc-m1 gp-m1" style="opacity:0;stroke-width:1.7" d="M398.5 187.8H403"/>
        <path class="p2-mouth sc-m2 gp-m2" d="M397 186.200Q401 191 406 186"/>
      </g>
      <g class="sc-arma gp-a">
        <path class="p2-sleeve" style="stroke-width:14" d="M391 214L364 222"/>
        <path class="p2-cuff" d="M365 221.700L362.500 222.700" style="stroke-width:12"/>
        <path class="p2-fore" style="stroke-width:9.500" d="M363.500 222.500L339 233.500"/>
        <ellipse class="p2-skin" cx="334.500" cy="235" rx="6.300" ry="5.400" transform="rotate(-24 334.500 235)"/>
        <ellipse class="p2-skin" cx="332.800" cy="229.300" rx="2.200" ry="4" transform="rotate(-38 332.800 229.300)"/>
        
        <circle class="sc-shirt" cx="392" cy="214.500" r="8.500" style="fill:var(--sc-shirt)"/>
      </g>
    </g></g>
    </g>
    <g class="gc-tag sc-tag"><rect class="sc-tagb" x="44" y="10" width="206" height="34" rx="17"/><circle class="sc-tagc" cx="66" cy="27" r="10"/><path class="sc-ck" d="M61 27l3.500 3.500 6.500-7"/><text class="sc-txt2" x="84" y="32">Entrada registrada</text></g>
    <g class="sc-card">
      <rect class="sc-cardb" x="326" y="8" width="222" height="148" rx="18"/>
      <rect class="sc-logo" x="340" y="20" width="16" height="16" rx="4"/><path class="sc-ck2" d="M344 28l3 3 5-5.500"/>
      <text class="sc-t0" x="362" y="32.500">GeoCheck</text><text class="sc-t3" x="534" y="32.500" text-anchor="end">Registro de entrada</text>
      <rect class="sc-thumb" x="340" y="44" width="196" height="52" rx="10"/>
      <g class="gc-p1"><path class="gc-road" d="M340 70H536M400 44V96M470 44V96"/><circle class="gc-gf" cx="470" cy="70" r="22"/><g class="gc-mpin"><circle class="gc-mp" cx="366" cy="86" r="6"/><circle class="gc-mpc" cx="366" cy="86" r="2.400"/></g><text class="sc-t3" x="438" y="58" text-anchor="middle" style="display:none">GPS</text></g>
      <g class="gc-p2"><text class="sc-t1" x="438" y="58" text-anchor="middle">Ingresa tu NIP</text><circle class="gc-dd" style="--i:0" cx="398" cy="74" r="6.500"/><circle class="gc-dd" style="--i:1" cx="424" cy="74" r="6.500"/><circle class="gc-dd" style="--i:2" cx="450" cy="74" r="6.500"/><circle class="gc-dd" style="--i:3" cx="476" cy="74" r="6.500"/></g>
      <g class="gc-p3"><circle class="gc-face" cx="380" cy="70" r="22"/><circle class="sc-skinf" cx="380" cy="70" r="17"/><circle class="sc-eye" cx="374" cy="68" r="1.800"/><circle class="sc-eye" cx="386" cy="68" r="1.800"/><path class="sc-m2" d="M374 76q6 5 12 0"/><path class="sc-hat" d="M363 66a17 14 0 0 1 34 0z"/><path class="gc-vf" d="M350 56v-6h6M404 50h6v6M350 84v6h6M410 84v6h-6"/><text class="sc-t1" x="470" y="66" text-anchor="middle">Selfie</text><text class="sc-t2" x="470" y="82" text-anchor="middle">en el momento</text></g>
      <rect class="sc-flash gc-cf" x="340" y="44" width="196" height="52" rx="10"/>
      <g class="sc-chip c1"><rect class="sc-chipb" x="340" y="102" width="70" height="20" rx="10"/><circle class="sc-chipc" cx="351" cy="112" r="6"/><path class="sc-ck3" d="M348 112l2.200 2.200 4-4.400"/><text class="sc-chipt" x="361" y="115.500">Ubicación</text></g><g class="sc-chip c2"><rect class="sc-chipb" x="416" y="102" width="44" height="20" rx="10"/><circle class="sc-chipc" cx="427" cy="112" r="6"/><path class="sc-ck3" d="M424 112l2.200 2.200 4-4.400"/><text class="sc-chipt" x="437" y="115.500">NIP</text></g><g class="sc-chip c3"><rect class="sc-chipb" x="466" y="102" width="50" height="20" rx="10"/><circle class="sc-chipc" cx="477" cy="112" r="6"/><path class="sc-ck3" d="M474 112l2.200 2.200 4-4.400"/><text class="sc-chipt" x="487" y="115.500">Foto</text></g>
      <g class="sc-send"><rect class="sc-sendb" x="340" y="128" width="196" height="20" rx="10"/><text class="sc-sendt" x="438" y="142" text-anchor="middle">Registrar entrada</text></g>
      <g class="sc-prog"><rect class="sc-track" x="340" y="128" width="196" height="20" rx="10"/><rect class="sc-fill" x="340" y="128" width="196" height="20" rx="10"/></g>
      <g class="sc-sent"><rect class="sc-sentb" x="340" y="128" width="196" height="20" rx="10"/><circle class="sc-sentc" cx="418" cy="138" r="6.500"/><path class="sc-ck3" d="M414.500 138l2.500 2.500 4.500-5"/><text class="sc-sentt" x="430" y="142">Registrada</text></g>
      <circle class="sc-finger" cx="470" cy="140" r="8"/>
    </g>
  </svg>
</div>
                <p class="hero__note"><?= a_icon('geo') ?><span><strong>Validación contra geocercas</strong>Si no estás dentro del radio permitido, no registras.</span></p>
            </div>
        </div>
    </div>
</section>

<!-- Qué es ---------------------------------------------------------------- -->
<section class="section section--tight" id="producto">
    <div class="container">
        <h2 class="sr-only">Qué es GeoCheck, para quién es y qué resuelve</h2>
        <div class="mosaic" data-reveal>
            <div class="card card--big">
                <p class="eyebrow">Qué es</p>
                <h3>Asistencia validada en tres pasos</h3>
                <p>Cada registro pasa por NIP, geocerca y foto en tiempo real. Las tres validaciones son obligatorias e inseparables.</p>
                <p class="muted">GeoCheck es un sistema web: la persona registra desde el navegador de su teléfono y quien supervisa ve cada registro con su hora, su ubicación validada y su fotografía. Con auditoría de cada intento, exitoso o fallido.</p>
            </div>
            <div class="card card--md">
                <p class="eyebrow">Para quién</p>
                <h3>Equipos que trabajan fuera de la oficina</h3>
                <p class="muted">Obra, ventas externas, servicio técnico, reparto, sucursales y sedes temporales.</p>
            </div>
            <div class="card card--md">
                <p class="eyebrow">Qué resuelve</p>
                <h3>El checado por compadrazgo</h3>
                <p class="muted">Olvídate de las hojas de papel, de los relojes checadores manipulables y del costo de comprar equipos.</p>
            </div>
        </div>
    </div>
</section>

<!-- Capacidades ----------------------------------------------------------- -->
<section class="section" id="capacidades">
    <div class="container">
        <header class="head" data-reveal>
            <p class="eyebrow">Capacidades</p>
            <h2>De la hoja de asistencia a la evidencia verificable</h2>
            <p class="lede">Validación en el momento del registro, seguimiento para quien supervisa y reglas claras de privacidad para quien registra.</p>
        </header>

        <div data-reveal>
            <div class="tabs" role="tablist" aria-label="Capacidades de GeoCheck">
                <?php foreach ($CAPACIDADES as $i => $c): ?>
                <button class="tab" type="button" role="tab" id="tab-<?= a_e($c['id']) ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"<?= $i === 0 ? '' : ' tabindex="-1"' ?> aria-controls="panel-<?= a_e($c['id']) ?>"><?= a_e($c['tab']) ?></button>
                <?php endforeach; ?>
            </div>
            <?php foreach ($CAPACIDADES as $i => $c): ?>
            <div class="cap-panel" role="tabpanel" id="panel-<?= a_e($c['id']) ?>" aria-labelledby="tab-<?= a_e($c['id']) ?>"<?= $i === 0 ? '' : ' hidden' ?>>
                <div class="split">
                    <div class="split__body">
                        <p class="eyebrow"><?= a_e($c['eyebrow']) ?></p>
                        <h3 style="font-size:clamp(1.5rem,1.4vw + 1rem,2rem)"><?= a_e($c['h3']) ?></h3>
                        <p class="muted"><?= a_e($c['p']) ?></p>
                        <ul class="feat-list">
                            <?php foreach ($c['items'] as $it): ?>
                            <li><span class="ico"><?= a_icon($it[0]) ?></span><div><strong><?= a_e($it[1]) ?></strong><p><?= a_e($it[2]) ?></p></div></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="split__media<?= !empty($c['tall']) ? ' split__media--tall' : '' ?>">
                        <?= gc_ventana($c['img'], $c['alt'], $c['w'], $c['h']) ?>
                        <p class="cap"><?= a_e($c['cap']) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="group" data-reveal>
            <h3>Diseñado para el control real</h3>
            <div class="items items--3"><?= gc_items($SOSTIENE) ?></div>
            <details class="more">
                <summary>Ver más del sistema<?= a_icon('chevron') ?></summary>
                <h3 style="margin-top:22px;margin-bottom:0">Más del sistema</h3>
                <div class="items items--3"><?= gc_items($AVANZADAS) ?></div>
            </details>
        </div>
    </div>
</section>

<!-- Por qué GeoCheck ------------------------------------------------------ -->
<section class="section section--sunken" id="resultados">
    <div class="container">
        <header class="head" data-reveal>
            <p class="eyebrow">Por qué GeoCheck</p>
            <h2>GeoCheck frente a un reloj checador</h2>
            <p class="lede">El reloj de la entrada valida que alguien puso un dedo. No valida quién, ni dónde, ni cuándo de verdad.</p>
        </header>

        <div class="why" data-reveal>
            <div class="why__col">
                <p class="eyebrow">La diferencia</p>
                <h3>Un registro que no se puede acomodar</h3>
                <p class="muted">Cada registro pasa por NIP, geocerca y foto en el momento: sin checado por compadrazgo, sin fotos manipuladas y sin comprar equipos.</p>
                <ul class="checks">
                    <li><div><strong>Validación GPS</strong><p>Fuera de la geocerca no hay registro.</p></div></li>
                    <li><div><strong>Evidencia no editable</strong><p>La foto se toma en el momento; no se admiten archivos de la galería.</p></div></li>
                    <li><div><strong>Sin checador físico</strong><p>Sin costo de comprar equipos: se registra desde el teléfono.</p></div></li>
                    <li><div><strong>Auditoría total</strong><p>Cada intento y cada cambio administrativo quedan registrados con autor y fecha.</p></div></li>
                    <li><div><strong>Intentos fallidos a la vista</strong><p>También quedan, con el motivo del rechazo.</p></div></li>
                    <li><div><strong>Reportes exportables</strong><p>Reportes avanzados de la asistencia, listos para exportar.</p></div></li>
                </ul>
            </div>
            <div class="why__col">
                <p class="eyebrow">Integración con tu operación</p>
                <p class="muted">GeoCheck se adapta al sitio donde trabaja tu gente, no al revés.</p>
                <div>
                    <details class="disc" open>
                        <summary class="disc__sum"><span class="disc__t">Geocercas por ubicación de trabajo</span><?= a_icon('chevron', 'disc__chev') ?></summary>
                        <div class="disc__body"><p>Defines el sitio y el radio permitido para cada equipo o sucursal, y cada registro se valida contra esa ubicación.</p></div>
                    </details>
                    <details class="disc">
                        <summary class="disc__sum"><span class="disc__t">Funciona en el navegador, sin descargas</span><?= a_icon('chevron', 'disc__chev') ?></summary>
                        <div class="disc__body"><p>No requiere descargas de App Store ni Google Play. Las actualizaciones son automáticas e invisibles para el usuario final.</p></div>
                    </details>
                    <details class="disc">
                        <summary class="disc__sum"><span class="disc__t">Auditoría total</span><?= a_icon('chevron', 'disc__chev') ?></summary>
                        <div class="disc__body"><p>Se registra cada intento de asistencia, exitoso o fallido, y cualquier cambio hecho por administradores o supervisores desde el panel.</p></div>
                    </details>
                    <details class="disc">
                        <summary class="disc__sum"><span class="disc__t">Aviso a quien supervisa</span><?= a_icon('chevron', 'disc__chev') ?></summary>
                        <div class="disc__body"><p>La campana avisa de incidencias nuevas, retardos y checadas sospechosas, y cada usuario decide qué avisos recibe.</p></div>
                    </details>
                </div>
                <div class="dif">
                    <p class="eyebrow">La diferencia GeoCheck</p>
                    <p class="lede">No ofrecemos solo un software, ofrecemos una solución completa a tu control de asistencia. Con soporte directo del equipo que lo desarrolla y actualizaciones incluidas, tu inversión evoluciona contigo.</p>
                </div>
            </div>
        </div>

        <div class="facts" data-reveal>
            <div class="fact"><b>Tres validaciones</b><span>NIP, GPS y foto, siempre juntas.</span></div>
            <div class="fact"><b>Sin checador físico</b><span>Se registra desde el navegador.</span></div>
            <div class="fact"><b>Sin descargas</b><span>Nada de App Store ni Google Play.</span></div>
            <div class="fact"><b>Auditoría total</b><span>Cada intento, exitoso o fallido.</span></div>
        </div>
    </div>
</section>

<!-- Precios --------------------------------------------------------------- -->
<section class="section" id="precios">
    <div class="container">
        <header class="head" data-reveal>
            <p class="eyebrow">Precios</p>
            <h2>Un precio por usuario, sin planes</h2>
            <p class="lede">Pagas por cada usuario con cuenta activa, y el precio baja con cada usuario que sumas. Todos incluyen la validación por NIP, GPS y foto, auditoría total, reportes exportables, soporte y las actualizaciones que se publiquen.</p>
        </header>
        <div data-reveal><?= gc_tarifas_html(ALKES_CENTRAL . '/?producto=geocheck') ?></div>
        <ul class="plan-foot" data-reveal>
            <li><?= a_icon('check') ?>Validación NIP, GPS y foto</li>
            <li><?= a_icon('check') ?>Auditoría total detallada</li>
            <li><?= a_icon('check') ?>Soporte y mantenimiento incluidos</li>
            <li><?= a_icon('check') ?>Actualizaciones sin costo adicional</li>
            <li><?= a_icon('check') ?>Sin descargas de App Store ni Google Play</li>
            <li><?= a_icon('check') ?>Precios en pesos mexicanos</li>
        </ul>
    </div>
</section>

<!-- Preguntas frecuentes -------------------------------------------------- -->
<section class="section section--sunken" id="preguntas">
    <div class="container">
        <div class="faq">
            <div class="faq__intro" data-reveal>
                <p class="eyebrow">Preguntas frecuentes</p>
                <h2>Lo que más nos preguntan sobre GeoCheck</h2>
                <p class="muted">¿Tienes otra duda? Escríbenos a <a href="mailto:<?= ALKES_MAIL ?>"><?= ALKES_MAIL ?></a>.</p>
            </div>
            <div class="faq__list" data-reveal>
                <?php foreach (gc_faq() as $i => $f): ?>
                <details class="disc"<?= $i === 0 ? ' open' : '' ?>>
                    <summary class="disc__sum"><span class="disc__t"><?= a_e($f['q']) ?></span><?= a_icon('chevron', 'disc__chev') ?></summary>
                    <div class="disc__body"><p><?= a_e($f['a']) ?></p></div>
                </details>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Confianza ------------------------------------------------------------- -->
<section class="section" id="confianza">
    <div class="container">
        <header class="head" data-reveal>
            <p class="eyebrow">Confianza</p>
            <h2>Un equipo con nombre, correo y teléfono</h2>
            <p class="lede">GeoCheck es un producto de Alkes. Nuestro equipo desarrolla, opera y da soporte al mismo sistema.</p>
        </header>
        <div class="tiles" data-reveal>
            <div class="tile"><?= a_icon('display') ?><h3>Sin descargas</h3><p>Funciona en el navegador del teléfono. Nada que instalar desde App Store ni Google Play.</p></div>
            <div class="tile"><?= a_icon('history') ?><h3>Auditoría total</h3><p>Cada intento y cada cambio administrativo quedan registrados con autor y fecha.</p></div>
            <div class="tile"><?= a_icon('lock') ?><h3>Evidencia íntegra</h3><p>La foto se captura en el momento; no se admiten archivos subidos desde la galería.</p></div>
            <div class="tile"><?= a_icon('refresh') ?><h3>Actualizaciones incluidas</h3><p>Soporte, mantenimiento y nuevas funciones sin costo adicional durante tu periodo.</p></div>
        </div>
        <div class="team" data-reveal>
            <div>
                <h3>Soporte del equipo que construye GeoCheck</h3>
                <p>Nuestro equipo desarrolla, opera y da soporte a GeoCheck de principio a fin. Para soporte técnico, una demostración guiada o un módulo a la medida de tu operación, hablas directamente con el equipo que construye el sistema.</p>
            </div>
            <div class="team__links">
                <a class="btn btn--primary" href="<?= a_e(gc_url_agendar()) ?>"><?= a_icon('mail') ?>Escribir al equipo</a>
                <a class="btn btn--line" href="tel:<?= ALKES_TEL ?>"><?= a_icon('phone') ?><?= ALKES_TEL_TXT ?></a>
                <a class="btn btn--line" href="<?= GC_WHATSAPP ?>" target="_blank" rel="noopener">WhatsApp<?= a_icon('external') ?></a>
            </div>
        </div>
    </div>
</section>

<!-- Novedades ------------------------------------------------------------- -->
<section class="section section--sunken" id="novedades">
    <div class="container narrow">
        <header class="head" data-reveal>
            <p class="eyebrow">Novedades</p>
            <h2>El producto se mueve, y queda por escrito</h2>
            <p class="lede">Historial público de versiones: lo que se entregó, cuándo y qué cambia en tu día a día.</p>
        </header>
        <div data-reveal>
                        <details class="disc disc--latest" open>
                <summary class="disc__sum">
                    <span class="disc__v">v2.3.0</span>
                    <span class="disc__t">Incidencias resueltas, definitivas</span>
                    <span class="disc__d">26 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Solo lo pendiente se atiende</strong><p>Completar la checada, autorizar o rechazar solo se ofrece en incidencias pendientes. Una incidencia autorizada o rechazada queda como está.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.2.1</span>
                    <span class="disc__t">Fotos del checador más ligeras y guardadas 24 meses</span>
                    <span class="disc__d">24 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Fotos más ligeras</strong><p>La foto de cada checada pesa menos y se le quitan los datos ocultos del celular, como la ubicación de la cámara.</p></div></li>
                    <li><div><strong>24 meses de evidencia</strong><p>Las fotos del checador se guardan 24 meses; después, la checada se conserva y su ficha indica que la foto venció.</p></div></li>
                    <li><div><strong>Aviso de checada corregido</strong><p>El mensaje «Checada registrada» ya no muestra etiquetas raras.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.2.0</span>
                    <span class="disc__t">Mi nube: tus archivos en un solo lugar</span>
                    <span class="disc__d">23 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Mi nube</strong><p>En el menú de tu nombre, después de «Mi perfil y ajustes»: todos tus archivos, para verlos, descargarlos, borrarlos o subir archivos sueltos. Cada quien ve solo lo suyo.</p></div></li>
                    <li><div><strong>Tu espacio, siempre a la vista</strong><p>Cada usuario tiene su propio espacio y una barra te dice cuánto llevas usado, con aviso cuando se está llenando.</p></div></li>
                    <li><div><strong>Fotos del checador aparte</strong><p>Las fotos de las checadas no ocupan tu espacio ni salen en Mi nube; solo un administrador las puede borrar.</p></div></li>
                    <li><div><strong>Archivos mejor protegidos</strong><p>Cada archivo se revisa por su contenido real y solo se descarga con permiso.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.1.7</span>
                    <span class="disc__t">Captura más a prueba de errores</span>
                    <span class="disc__d">17 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Fecha de ingreso razonable</strong><p>La fecha de ingreso de un usuario debe estar entre 1950 y un año adelante.</p></div></li>
                    <li><div><strong>Incidencias con fechas reales</strong><p>Las fechas de una incidencia deben ser válidas y no estar en el futuro.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.1.6</span>
                    <span class="disc__t">El visor de PDF funciona en el celular</span>
                    <span class="disc__d">16 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>PDF dentro del mismo visor</strong><p>En Chrome para Android y otros navegadores sin visor propio, el PDF ahora se ve en el modal, página por página, con su botón Descargar.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.1.5</span>
                    <span class="disc__t">«Recuérdame» en hasta 5 equipos</span>
                    <span class="disc__d">16 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Cada equipo se recuerda por separado</strong><p>Puedes recordar hasta 5 equipos a la vez; al recordar el sexto se olvida el que lleva más tiempo sin usarse.</p></div></li>
                    <li><div><strong>Lista en Mi perfil</strong><p>En Mi perfil › Seguridad ves tus equipos recordados con navegador, sistema, IP y último uso, y puedes olvidar el que quieras.</p></div></li>
                    <li><div><strong>Más seguro</strong><p>Cerrar sesión olvida solo ese equipo, y cambiar la contraseña o dar de baja a un usuario olvida sus equipos recordados. Cada persona inicia sesión una vez más y vuelve a marcar «Recordarme».</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.1.4</span>
                    <span class="disc__t">Correos de recuperación funcionando</span>
                    <span class="disc__d">16 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Se corrigió el envío de correos</strong><p>Se resolvió un error que impedía mandar el código de recuperación de contraseña y los correos de documentos.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.1.3</span>
                    <span class="disc__t">Recuperar contraseña y acceso desde el celular</span>
                    <span class="disc__d">16 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Recupera tu contraseña desde el acceso</strong><p>Escribes tu correo, recibes un código de 6 dígitos que vence a los 15 minutos y con él pones tu contraseña nueva.</p></div></li>
                    <li><div><strong>Acceso cómodo en el celular</strong><p>La pantalla de acceso se ajusta a cualquier tamaño, en vertical y en horizontal, sin hacer scroll ni tapar la tarjeta con el teclado.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.1.2</span>
                    <span class="disc__t">Tablas con el mismo control de orden</span>
                    <span class="disc__d">15 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Un solo control de orden</strong><p>Las tablas de reportes, festivos y el modal de selección ordenan con el mismo menú que el resto.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.1.1</span>
                    <span class="disc__t">Botones iguales y en el mismo orden en todos los módulos</span>
                    <span class="disc__d">15 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Cada botón, siempre del mismo color</strong><p>Guardar y Borrador en azul, Procesar en verde, Cancelar y Eliminar en rojo, todas las autorizaciones en amarillo, Imprimir y Regresar en gris. Los mismos colores en todos los módulos.</p></div></li>
                    <li><div><strong>Siempre en el mismo orden</strong><p>De izquierda a derecha, por relevancia: primero guardar, luego las acciones, las cancelaciones y las autorizaciones. Al extremo derecho van Enviar, Información y Regresar.</p></div></li>
                    <li><div><strong>Sin menús escondidos</strong><p>Todos los botones están a la vista, uno por uno, rellenos y con su ícono. Ya no hay botones de solo contorno ni menús «Más».</p></div></li>
                    <li><div><strong>Mejor en el celular</strong><p>En pantallas chicas los botones muestran solo su ícono para que quepan, y la acción principal conserva su texto.</p></div></li>
                    <li><div><strong>El botón de correo, más corto</strong><p>Ahora se llama simplemente Enviar.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.1.0</span>
                    <span class="disc__t">Envía fichas de empleado y de checada por correo</span>
                    <span class="disc__d">15 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>De la checada al correo del empleado</strong><p>Con el botón Enviar mandas la ficha de una checada o la ficha de un empleado. El PDF va como archivo adjunto.</p></div></li>
                    <li><div><strong>Revisas antes de enviar</strong><p>Una ventana te muestra el correo del empleado, el asunto y el mensaje para que los ajustes, y eliges qué secciones incluye el PDF.</p></div></li>
                    <li><div><strong>Con el nombre de tu empresa</strong><p>El correo sale a nombre de tu empresa, con tu firma y tu pie. Las respuestas llegan al correo que elijas.</p></div></li>
                    <li><div><strong>Con tu propio servidor de correo</strong><p>Si lo prefieres, configura el servidor de correo de tu empresa para que los envíos salgan desde tu dominio y sin el límite de uso justo.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.0.4</span>
                    <span class="disc__t">Formularios más claros, con ícono en cada campo</span>
                    <span class="disc__d">14 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Un ícono en cada campo</strong><p>Cada dato que capturas lleva su ícono al lado izquierdo, para reconocer de un vistazo qué se pide.</p></div></li>
                    <li><div><strong>Nombre y campo en la misma línea</strong><p>El nombre del campo queda a la izquierda y el campo a su derecha, en todos los formularios y ventanas.</p></div></li>
                    <li><div><strong>Tarjetas con título e ícono</strong><p>Cada bloque de una pantalla se identifica con su ícono y su título.</p></div></li>
                    <li><div><strong>Configuración y datos para facturar con el mismo orden</strong><p>Las pantallas de Configuración, suscripción y datos para facturar siguen el mismo acomodo que el resto del sistema.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.0.3</span>
                    <span class="disc__t">Fichas de empleado y de checada en PDF</span>
                    <span class="disc__d">14 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Ficha del empleado</strong><p>Un resumen de sus últimos 30 días y, si quieres, sus últimas checadas, incidencias, ubicaciones y horarios asignados.</p></div></li>
                    <li><div><strong>Ficha de la checada</strong><p>Hora del servidor y del dispositivo, geocerca, precisión del GPS, riesgo y resultado, con sus incidencias, evidencias y notas.</p></div></li>
                    <li><div><strong>Tú eliges qué incluye</strong><p>El botón Imprimir, al pie del formulario, abre una ventana donde marcas las secciones y ves cuántos elementos tiene cada una. Recuerda tu última elección.</p></div></li>
                    <li><div><strong>Se ve sin salir de la pantalla</strong><p>El PDF se abre en una ventana, con la opción de descargarlo. Arriba lleva el logo y el nombre de tu empresa; GeoCheck aparece solo como un crédito discreto al pie.</p></div></li>
                    <li><div><strong>La checada, con Regresar</strong><p>El formulario de una checada ahora tiene Imprimir y Regresar al pie.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.0.2</span>
                    <span class="disc__t">Cobro por usuario, cupo editable y roles que se suman</span>
                    <span class="disc__d">13 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Tú decides cuántos usuarios contratas</strong><p>Ya no hay planes ni topes por tipo de usuario. Contratas un número de usuarios y cada persona activa ocupa un lugar, sea cual sea su rol.</p></div></li>
                    <li><div><strong>Cambia tu cupo desde Configuración</strong><p>El administrador sube o baja el total de varios en varios y ve la vista previa del cobro antes de confirmar. Al subir, se cobra al momento la parte proporcional; al bajar, el cambio se aplica en la renovación.</p></div></li>
                    <li><div><strong>Aviso cuando el cupo se llena</strong><p>Dar de alta o reactivar a alguien con el cupo lleno no genera ningún cobro: el sistema avisa y tú decides si amplías.</p></div></li>
                    <li><div><strong>Roles que se suman</strong><p>Una persona puede tener varios roles. El checador es para quien tiene el rol Empleado.</p></div></li>
                    <li><div><strong>Sin topes de ubicaciones ni de administradores</strong><p>El cupo se mide solo por usuarios, empleados y administradores por igual.</p></div></li>
                    <li><div><strong>Datos para facturar</strong><p>En Configuración puedes capturar, si los necesitas, los datos fiscales de tu suscripción.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.0.1</span>
                    <span class="disc__t">La misma forma de trabajar en GeoCheck, Mantis y SmartPend</span>
                    <span class="disc__d">8 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <h4 class="disc__group">Resumen de la versión</h4>
                    <ul>
                    <li><div><strong>Tarjetas iguales en los tres sistemas</strong><p>Las tarjetas secundarias llevan la misma sombra que en Mantis y SmartPend.</p></div></li>
                    <li><div><strong>Maximizar junto al buscador</strong><p>En los listados, el botón para ver la tabla a pantalla completa está a un lado de Buscar.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v2.0.0</span>
                    <span class="disc__t">Interfaz renovada, calendario de asistencia, avisos y notas</span>
                    <span class="disc__d">8 oct 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <h4 class="disc__group">Resumen de la versión</h4>
                    <ul>
                    <li><div><strong>Interfaz renovada de punta a punta</strong><p>El panel de GeoCheck estrena diseño completo. El encabezado, el menú y el pie se quedan fijos mientras trabajas, y se usa igual de cómodo en el celular que en la computadora. Tema claro, oscuro o automático, 20 colores de énfasis y un modo compacto para ver más filas.</p></div></li>
                    <li><div><strong>Calendario de asistencia</strong><p>Asistencias, retardos, faltas e incidencias por autorizar de cada día, en vista de mes, semana o lista. Por persona, su entrada y su salida de cada día. Un clic lleva al listado.</p></div></li>
                    <li><div><strong>Avisos en la campana, los que tú elijas</strong><p>Te avisa de incidencias nuevas, retardos, checadas sospechosas, el cierre del día, la resolución de una incidencia y las notas. Un interruptor por tipo de aviso decide cuáles recibes.</p></div></li>
                    <li><div><strong>Notas en checadas e incidencias</strong><p>Una aclaración sobre un retardo o lo que falta para autorizar una incidencia queda escrita en el propio registro, como una conversación.</p></div></li>
                    <li><div><strong>La evidencia de cada checada, en grande</strong><p>La foto se abre en un visor a pantalla completa, sin salir del registro.</p></div></li>
                    <li><div><strong>Reportes y checadas impresos como documento</strong><p>Con el logo y el nombre de tu empresa, sin menús ni botones en el papel.</p></div></li>
                    <li><div><strong>Búsqueda en todo el sistema</strong><p>Escribe una persona, una ubicación, un horario o una incidencia en la barra de arriba, desde cualquier pantalla. Solo aparece lo que tú puedes abrir.</p></div></li>
                    <li><div><strong>Un dashboard que dice cómo va el día</strong><p>Checadas y retardos de hoy, incidencias por autorizar y ubicaciones por revisar, cada uno con su acceso directo. Tres barras miden quién ya checó, cuántas checadas caen dentro de la geocerca y la puntualidad del mes.</p></div></li>
                    <li><div><strong>Mi perfil y ajustes, sin salir de lo que haces</strong><p>Desde tu nombre, arriba a la derecha: tus datos y tu foto, el cambio de contraseña, la apariencia, tus avisos y tu actividad.</p></div></li>
                    <li><div><strong>Manual de usuario dentro del sistema</strong><p>Se abre desde el menú de tu nombre, en la sección de la pantalla donde estás, con buscador.</p></div></li>
                    <li><div><strong>Errores claros en los formularios</strong><p>Si un dato falta o no es válido, el campo se marca en rojo con el mensaje debajo.</p></div></li>
                    <li><div><strong>Más seguridad</strong><p>El NIP de cada persona ya no queda escrito en ninguna bitácora, y se reforzó la protección de las sesiones.</p></div></li>
                    </ul>
                    <h4 class="disc__group">Impacto en tu día a día</h4>
                    <ul>
                    <li><div><strong>Checar se hace igual</strong><p>El checador, la ubicación y la foto funcionan como siempre. No hay que reconfigurar nada.</p></div></li>
                    <li><div><strong>Cerrar sesión cambia de lugar</strong><p>Ahora está en el menú de tu nombre, arriba a la derecha.</p></div></li>
                    </ul>
                </div>
            </details>
            <details class="disc">
                <summary class="disc__sum">
                    <span class="disc__v">v1.0.0</span>
                    <span class="disc__t">Primera versión de GeoCheck</span>
                    <span class="disc__d">jun 2026</span>
                    <?= a_icon('chevron', 'disc__chev') ?>
                </summary>
                <div class="disc__body">
                    <ul>
                    <li><div><strong>Checador con ubicación</strong><p>El empleado marca su entrada y salida desde el celular; el sistema valida su GPS contra la geocerca de la ubicación.</p></div></li>
                    <li><div><strong>Panel de administración</strong><p>Usuarios, ubicaciones, horarios, checadas e incidencias en un solo lugar.</p></div></li>
                    <li><div><strong>Varias empresas</strong><p>Cada empresa ve solo su información.</p></div></li>
                    <li><div><strong>Roles</strong><p>Administrador, supervisor y empleado: el empleado solo usa el checador.</p></div></li>
                    </ul>
                </div>
            </details>

        </div>

        <div class="block">
            <header class="head head--sub" data-reveal>
                <p class="eyebrow">En camino</p>
                <h2 style="font-size:clamp(1.4rem,1vw + 1.1rem,1.8rem)">Lo que sigue</h2>
            </header>
            <div class="roadmap" data-reveal>
                <div class="roadmap__item"><h3>Reconocimiento facial</h3><p>Validación opcional mediante reconocimiento facial para evitar el uso del NIP por terceros.</p></div>
                <div class="roadmap__item"><h3>Ubicaciones por zonas</h3><p>Gestión avanzada para empresas con múltiples sub-zonas dentro de una misma ubicación general.</p></div>
                <div class="roadmap__item"><h3>Modo offline</h3><p>Capacidad para registrar asistencia sin conexión a internet y sincronizar al regresar.</p></div>
            </div>
            <div class="notice" data-reveal><?= a_icon('alert') ?><p>Las fechas, el orden y la disponibilidad de esta funcionalidad están sujetos a cambios sin previo aviso.</p></div>
        </div>
    </div>
</section>

<!-- Cierre ---------------------------------------------------------------- -->
<section class="section section--tight">
    <div class="container">
        <div class="cta" data-reveal>
            <div>
                <p class="eyebrow">Empieza hoy</p>
                <h2>Empieza a validar la asistencia de verdad</h2>
                <p class="lede">Entra al demo con las credenciales de prueba, o contrata por usuario y registra la primera asistencia validada el mismo día.</p>
            </div>
            <div class="cta__act">
                <a class="btn btn--cta btn--lg" href="<?= a_e($URL_APP) ?>" data-open-demo><?= a_icon('play') ?>Probar el demo</a>
                <a class="btn btn--line btn--lg" href="#precios">Ver precios</a>
                <a class="link" href="<?= a_e(gc_url_agendar()) ?>">Agendar demostración guiada<?= a_icon('arrow') ?></a>
            </div>
        </div>
    </div>
</section>

<!-- Contacto -------------------------------------------------------------- -->
<section class="section section--sunken" id="contacto">
    <div class="container">
        <header class="head" data-reveal>
            <p class="eyebrow">Contacto</p>
            <h2>Hablemos</h2>
            <p class="lede">Soporte técnico, dudas comerciales o módulos a la medida. Tu mensaje llega a una bandeja real.</p>
        </header>
        <div class="contact" data-reveal>
            <a class="contact__item" href="mailto:<?= ALKES_MAIL ?>"><?= a_icon('mail') ?><span class="contact__k">Correo</span><span class="contact__v"><?= ALKES_MAIL ?></span><span class="contact__d">Respuesta en horario hábil.</span></a>
            <a class="contact__item" href="tel:<?= ALKES_TEL ?>"><?= a_icon('phone') ?><span class="contact__k">Teléfono</span><span class="contact__v"><?= ALKES_TEL_TXT ?></span><span class="contact__d">Para demostraciones guiadas.</span></a>
            <a class="contact__item" href="<?= GC_WHATSAPP ?>" target="_blank" rel="noopener"><?= a_icon('phone') ?><span class="contact__k">WhatsApp</span><span class="contact__v"><?= ALKES_TEL_TXT ?></span><span class="contact__d">Escríbenos por mensaje.</span></a>
            <a class="contact__item" href="<?= a_e($URL_APP) ?>"><?= a_icon('login') ?><span class="contact__k">Acceso</span><span class="contact__v">app.geocheck.alkes.mx</span><span class="contact__d">Entra a tu cuenta de GeoCheck.</span></a>
        </div>
        <div class="redes" data-reveal>
            <span class="redes__k">Síguenos</span>
            <?php foreach (gc_redes() as $r): ?>
            <a href="<?= a_e($r['u']) ?>" target="_blank" rel="noopener"><?= a_e($r['n']) ?><?= a_icon('external') ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Ecosistema ------------------------------------------------------------ -->
<section class="section section--tight" id="ecosistema">
    <div class="container">
        <header class="head" data-reveal>
            <p class="eyebrow">Ecosistema Alkes</p>
            <h2>GeoCheck no está solo</h2>
            <p class="lede">Cinco productos desarrollados, alojados y operados por Alkes. Mismo soporte, misma casa.</p>
        </header>
        <?= a_ecosistema_grid($PROD) ?>
    </div>
</section>

</main>

<footer class="footer">
    <div class="container footer__top">
        <div class="footer__brand">
            <span class="brand"><?= gc_logo(34) ?><span>GeoCheck</span></span>
            <p>Sistema web de control de asistencia por ubicación. Validamos identidad, GPS y evidencia visual para empresas con personal en campo.</p>
        </div>
        <div class="footer__cols">
            <div class="footer__col">
                <h2 class="footer__h">Producto</h2>
                <ul>
                    <li><a href="#producto">Qué es</a></li>
                    <li><a href="#capacidades">Capacidades</a></li>
                    <li><a href="#precios">Precios</a></li>
                    <li><a href="#preguntas">Preguntas frecuentes</a></li>
                    <li><a href="#novedades">Novedades</a></li>
                                    </ul>
            </div>
            <div class="footer__col">
                <h2 class="footer__h">Accesos</h2>
                <ul>
                    <li><a href="<?= a_e($URL_APP) ?>">Iniciar sesión</a></li>
                    <li><a href="<?= a_e($URL_APP) ?>" data-open-demo>Probar el demo</a></li>
                    <li><a href="<?= ALKES_CENTRAL ?>">Contratar</a></li>
                    <li><a href="manual.html">Manual de usuario</a></li>
                    <li><a href="<?= ALKES_SITIO ?>">Alkes</a></li>
                </ul>
            </div>
            <div class="footer__col">
                <h2 class="footer__h">Legal</h2>
                <ul>
                    <li><a href="/terminos.html">Términos y condiciones</a></li>
                    <li><a href="/privacidad.html">Aviso de privacidad</a></li>
                    <li><a href="/cookies.html">Cookies</a></li>
                    <li><a href="/encargo-de-datos.html">Tratamiento de datos</a></li>
                </ul>
            </div>
            <div class="footer__col">
                <h2 class="footer__h">Síguenos</h2>
                <ul>
                    <?php foreach (gc_redes() as $r): ?>
                    <li><a href="<?= a_e($r['u']) ?>" target="_blank" rel="noopener"><?= a_e($r['n']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="footer__bot">
            <p>© <?= date('Y') ?> Alkes. GeoCheck es un producto de Alkes. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

<?= gc_dialogo_demo($URL_APP) ?>

<script src="assets/js/geocheck.js" defer></script>
</body>
</html>
