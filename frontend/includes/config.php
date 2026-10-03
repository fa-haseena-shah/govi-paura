<?php
/**
 * Govi Paura — path bootstrap
 *
 * PROBLEM THIS SOLVES
 *   Every asset/link in this project used to be written as a root-absolute
 *   path, e.g. href="/assets/css/base.css" or href="/farmer/dashboard.php".
 *   That only resolves correctly if the site's document root IS the
 *   `public/` folder (e.g. via an Apache virtual host, which is the
 *   long-term recommended setup — see docs/DEPLOYMENT.md).
 *
 *   WAMP/XAMPP's default "localhost" document root is C:\wamp64\www, so if
 *   you simply drop this project in www\govi-paura and open it as
 *   http://localhost/govi-paura/public/, the site is actually one level
 *   BELOW the domain root. Every "/assets/..." link then points at
 *   http://localhost/assets/... (which doesn't exist) instead of
 *   http://localhost/govi-paura/public/assets/... — so no CSS/JS/images load
 *   and the page renders as unstyled HTML.
 *
 * THE FIX
 *   BASE_URL is computed once here from the current request, and every
 *   asset/link in the project is written as "<?= BASE_URL ?>/assets/..."
 *   instead of "/assets/...". That makes the whole site portable: it works
 *   unmodified whether it's the document root or sitting in a subfolder.
 *
 * USAGE
 *   require_once __DIR__ . '/config.php';   // (or the correct relative path)
 *   <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
 */

if (!defined('BASE_URL')) {
    // Directory of the currently-executing script, e.g. /govi-paura/public/farmer
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $segments  = array_filter(explode('/', $scriptDir));

    // If we're inside a portal/auth subfolder, strip that one segment so we
    // land on the `public` folder itself (e.g. /govi-paura/public).
    $knownFolders = ['farmer', 'rider', 'buyer', 'admin', 'auth'];
    if (!empty($segments) && in_array(end($segments), $knownFolders, true)) {
        array_pop($segments);
    }

    define('BASE_URL', $segments ? '/' . implode('/', $segments) : '');
}
