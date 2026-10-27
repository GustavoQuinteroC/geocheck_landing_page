<?php
$NOINDEX = false;
$GC_TARIFA = true;

require_once __DIR__ . '/_geocheck.php';
require_once __DIR__ . '/_seo.php';

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
// SEO y GEO: datos para seo_head() y seo_enriquecer() (ver _seo.php).
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
            <?php include __DIR__ . '/_novedades_recientes.php'; ?>
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
                    <li><a href="/terminos/">Términos y condiciones</a></li>
                    <li><a href="/privacidad/">Aviso de privacidad</a></li>
                    <li><a href="/cookies/">Cookies</a></li>
                    <li><a href="/encargo-de-datos/">Tratamiento de datos</a></li>
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
