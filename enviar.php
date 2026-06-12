<?php
/**
 * Endpoint de contacto de futura.es
 *
 * Recibe el POST del formulario de index.html y envía DOS correos por SMTP (Gmail):
 *   A) Aviso interno a web@futura.es con los datos del formulario.
 *   B) Confirmación al cliente que ha rellenado el formulario.
 *
 * Responde en JSON si la petición es AJAX (cabecera Accept: application/json),
 * o redirige a gracias.html si es un envío de formulario tradicional.
 */

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Carga de PHPMailer (composer o copia manual en /PHPMailer) -------------
if (is_file(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} elseif (is_file(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
    require __DIR__ . '/PHPMailer/src/Exception.php';
    require __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require __DIR__ . '/PHPMailer/src/SMTP.php';
} else {
    respond(500, false, 'PHPMailer no está instalado en el servidor.');
}

$cfg = require __DIR__ . '/config.php';

// --- Etiquetas legibles para el tipo de solicitud (select del formulario) ---
$tiposSolicitud = [
    'tech'        => 'Soporte Técnico',
    'sales'       => 'Presupuesto de Equipos',
    'sublimacion' => 'Personalización y Sublimación',
    'beinsen'     => 'Planchas Beinsen',
    'other'       => 'Otra consulta',
];

// --- Validaciones de entrada ------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, false, 'Método no permitido.');
}

// Honeypot anti-spam: si viene relleno, fingimos éxito y descartamos.
if (!empty($_POST['website'])) {
    respond(200, true, 'OK');
}

$nombre  = trim((string)($_POST['name'] ?? ''));
$email   = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$tipoKey = trim((string)($_POST['requestType'] ?? ''));
$mensaje = trim((string)($_POST['message'] ?? ''));

$errores = [];
if (mb_strlen($nombre) < 3)              { $errores[] = 'nombre'; }
if (!$email)                             { $errores[] = 'email'; }
if (!isset($tiposSolicitud[$tipoKey]))   { $errores[] = 'tipo de solicitud'; }
if (mb_strlen($mensaje) < 20)            { $errores[] = 'mensaje'; }

if ($errores) {
    respond(422, false, 'Revisa los campos: ' . implode(', ', $errores) . '.');
}

$tipoLabel = $tiposSolicitud[$tipoKey];

// --- Helper para crear una instancia de PHPMailer configurada ---------------
function smtp(array $cfg): PHPMailer
{
    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->Host       = $cfg['smtp_host'];
    $m->SMTPAuth   = true;
    $m->Username   = $cfg['smtp_user'];
    $m->Password   = $cfg['smtp_pass'];
    $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $m->Port       = (int)$cfg['smtp_port'];
    $m->CharSet    = 'UTF-8';
    $m->setFrom($cfg['from_email'], $cfg['from_name']);
    return $m;
}

try {
    // A) Aviso interno a web@futura.es ---------------------------------------
    $a = smtp($cfg);
    $a->addAddress($cfg['aviso_a']);
    $a->addReplyTo($email, $nombre);
    $a->Subject = 'Nueva consulta web (' . $tipoLabel . ')';
    $a->isHTML(true);
    $a->Body =
        '<h3>Nueva consulta desde futura.es</h3>' .
        '<p><b>Nombre / Empresa:</b> ' . htmlspecialchars($nombre) . '</p>' .
        '<p><b>Email:</b> ' . htmlspecialchars($email) . '</p>' .
        '<p><b>Tipo de solicitud:</b> ' . htmlspecialchars($tipoLabel) . '</p>' .
        '<p><b>Mensaje:</b><br>' . nl2br(htmlspecialchars($mensaje)) . '</p>';
    $a->AltBody =
        "Nueva consulta desde futura.es\n\n" .
        "Nombre / Empresa: $nombre\n" .
        "Email: $email\n" .
        "Tipo de solicitud: $tipoLabel\n\n" .
        "Mensaje:\n$mensaje\n";
    $a->send();

    // B) Confirmación al cliente ---------------------------------------------
    $c = smtp($cfg);
    $c->addAddress($email, $nombre);
    $c->Subject = 'Hemos recibido tu mensaje · Futura Teck';
    $c->isHTML(true);
    $c->Body =
        '<p>Hola ' . htmlspecialchars($nombre) . ',</p>' .
        '<p>Gracias por contactar con <b>Futura Teck</b>. Hemos recibido tu consulta ' .
        'sobre <b>' . htmlspecialchars($tipoLabel) . '</b> y te responderemos en menos de 24h laborables.</p>' .
        '<p>Si es urgente, puedes llamarnos al <b>+34 968 902 300</b>.</p>' .
        '<p>— El equipo de ' . htmlspecialchars($cfg['from_name']) . '</p>';
    $c->AltBody =
        "Hola $nombre,\n\n" .
        "Gracias por contactar con Futura Teck. Hemos recibido tu consulta sobre " .
        "$tipoLabel y te responderemos en menos de 24h laborables.\n\n" .
        "Si es urgente, llámanos al +34 968 902 300.\n\n" .
        "— El equipo de {$cfg['from_name']}\n";
    $c->send();

    respond(200, true, 'Mensaje enviado correctamente.');
} catch (Exception $e) {
    // No exponemos detalles internos al usuario; quedan en el log de PHP.
    error_log('[enviar.php] Error SMTP: ' . $e->getMessage());
    respond(500, false, 'No se pudo enviar el mensaje. Inténtalo más tarde.');
}

// --- Respuesta unificada (JSON para AJAX, redirección para form normal) -----
function respond(int $code, bool $success, string $message): void
{
    http_response_code($code);

    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $isAjax = stripos($accept, 'application/json') !== false
        || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $success ? 'true' : 'false', 'message' => $message]);
    } elseif ($success) {
        header('Location: gracias.html');
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
    }
    exit;
}
