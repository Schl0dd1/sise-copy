<?php
/**
 * Decap CMS – GitHub-Login, Schritt 2: Rückkehr von GitHub.
 *
 * Tauscht den Code von GitHub gegen ein Zugangs-Token und gibt es per
 * postMessage an das CMS-Fenster zurück (Protokoll von Decap CMS).
 */

declare(strict_types=1);

$config = require __DIR__ . '/config-laden.php';

session_start();

function antwort(string $status, array $daten, string $origin): void
{
    $nachricht = 'authorization:github:' . $status . ':' . json_encode($daten, JSON_UNESCAPED_SLASHES);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    ?>
<!doctype html>
<html lang="de">
<head><meta charset="utf-8"><title>Anmeldung …</title></head>
<body>
<p>Anmeldung wird abgeschlossen …</p>
<script>
  (function () {
    var nachricht = <?= json_encode($nachricht, JSON_UNESCAPED_SLASHES) ?>;
    var erlaubt = <?= json_encode($origin, JSON_UNESCAPED_SLASHES) ?>;
    function empfangen(e) {
      if (e.origin !== erlaubt) return;
      window.opener.postMessage(nachricht, e.origin);
      window.removeEventListener("message", empfangen, false);
    }
    window.addEventListener("message", empfangen, false);
    window.opener.postMessage("authorizing:github", erlaubt);
  })();
</script>
</body>
</html>
    <?php
    exit;
}

$origin = $config['site_origin'];

$code  = $_GET['code']  ?? '';
$state = $_GET['state'] ?? '';
$erwartet = $_SESSION['decap_oauth_state'] ?? '';
unset($_SESSION['decap_oauth_state']);

if ($code === '' || $erwartet === '' || !hash_equals($erwartet, $state)) {
    antwort('error', ['message' => 'Ungültige Anmeldeanfrage. Bitte erneut versuchen.'], $origin);
}

$ch = curl_init('https://github.com/login/oauth/access_token');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    CURLOPT_POSTFIELDS     => http_build_query([
        'client_id'     => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'code'          => $code,
        'redirect_uri'  => $config['redirect_uri'],
    ]),
]);
$roh = curl_exec($ch);
curl_close($ch);

$ergebnis = is_string($roh) ? json_decode($roh, true) : null;
$token = is_array($ergebnis) ? ($ergebnis['access_token'] ?? '') : '';

if ($token === '') {
    $fehler = is_array($ergebnis) ? ($ergebnis['error_description'] ?? 'Unbekannter Fehler') : 'GitHub nicht erreichbar';
    antwort('error', ['message' => $fehler], $origin);
}

antwort('success', ['token' => $token, 'provider' => 'github'], $origin);
