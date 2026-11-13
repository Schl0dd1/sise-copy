<?php
/**
 * Decap CMS – GitHub-Login, Schritt 1: Weiterleitung zu GitHub.
 *
 * Decap öffnet diese Seite in einem Popup (backend.auth_endpoint in admin/config.yml).
 * Die Zugangsdaten der GitHub-OAuth-App liegen NICHT im Repository, sondern in
 * einer Datei außerhalb des Web-Ordners auf dem Server – siehe DEPLOYMENT.md.
 */

declare(strict_types=1);

$config = require __DIR__ . '/config-laden.php';

session_start();
$state = bin2hex(random_bytes(16));
$_SESSION['decap_oauth_state'] = $state;

$params = http_build_query([
    'client_id'    => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    // "repo" für private Repositories, "public_repo" reicht für öffentliche
    'scope'        => $config['scope'] ?? 'repo',
    'state'        => $state,
]);

header('Location: https://github.com/login/oauth/authorize?' . $params, true, 302);
exit;
