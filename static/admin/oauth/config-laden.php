<?php
/**
 * Lädt die Zugangsdaten der GitHub-OAuth-App.
 *
 * Die Datei „decap-oauth-config.php“ liegt bewusst AUSSERHALB des Web-Ordners
 * (eine Ebene über dem Document Root), damit sie nie ausgeliefert wird und beim
 * Deployment (rsync --delete) nicht gelöscht wird. Vorlage: DEPLOYMENT.md
 */

declare(strict_types=1);

$pfad = dirname($_SERVER['DOCUMENT_ROOT']) . '/decap-oauth-config.php';

if (!is_readable($pfad)) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "CMS-Login ist noch nicht eingerichtet: decap-oauth-config.php fehlt auf dem Server.";
    exit;
}

return require $pfad;
