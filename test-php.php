<?php
/**
 * Prueba rápida de soporte PHP en el servidor.
 *
 * Sube este archivo y visita  https://futura.es/test-php.php
 *   - Si ves la información de abajo  -> PHP está activo. ✅
 *   - Si ves el CÓDIGO o se descarga  -> nginx NO enruta PHP (hay que activar PHP-FPM).
 *
 * ⚠️  BÓRRALO del servidor en cuanto confirmes que PHP funciona.
 */

header('Content-Type: text/plain; charset=utf-8');

echo "PHP funciona correctamente.\n";
echo "Versión de PHP: " . PHP_VERSION . "\n";
echo "OpenSSL (necesario para SMTP/STARTTLS): " . (extension_loaded('openssl') ? 'OK' : 'FALTA') . "\n";
echo "Función mail() disponible: " . (function_exists('mail') ? 'sí' : 'no') . "\n";
