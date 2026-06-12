<?php
/**
 * Plantilla de configuración del endpoint de contacto.
 *
 * COPIA este archivo a "config.php" EN EL SERVIDOR y rellena las credenciales.
 * "config.php" NO se sube a git (está en .gitignore) y, a ser posible, debe
 * ubicarse fuera del directorio público (webroot). Si lo mueves, ajusta la
 * ruta del require en enviar.php.
 */

return [
    'smtp_host'  => 'smtp.gmail.com',
    'smtp_port'  => 587,
    'smtp_user'  => 'CUENTA@gmail.com',       // cuenta Gmail que ENVÍA
    'smtp_pass'  => 'APP-PASSWORD-16-CHARS',  // contraseña de aplicación de Gmail (no la normal)
    'from_email' => 'CUENTA@gmail.com',       // normalmente igual que smtp_user
    'from_name'  => 'Futura Teck',
    'aviso_a'    => 'web@futura.es',          // destinatario del aviso interno
];
