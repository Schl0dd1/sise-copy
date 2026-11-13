# Veröffentlichung auf Hetzner-Webhosting

Die Website wird mit **GitHub Actions** gebaut (Hugo + Tailwind) und per **rsync über SSH**
auf den Hetzner-Webspace geladen. Das passiert automatisch:

- bei jedem Push auf `main` – also auch nach jeder Änderung im CMS,
- jede Nacht (damit vergangene Veranstaltungen verschwinden),
- manuell: GitHub → *Actions* → *Deploy* → *Run workflow*.

Workflow: [`.github/workflows/deploy.yml`](.github/workflows/deploy.yml)
Apache-Einstellungen (Weiterleitungen, 404, Caching): [`static/.htaccess`](static/.htaccess)

Die folgenden Schritte sind **einmalig** nötig.

---

## 1. SSH-Zugang für das Deployment

1. Im Hetzner-Konto (konsoleH) beim Webhosting **SSH-Zugang aktivieren**.
   Notieren: Server (z. B. `www123.your-server.de`), Port (bei Webhosting meist `222`), Benutzername.
2. Lokal ein eigenes Schlüsselpaar nur für das Deployment erzeugen (ohne Passphrase):
   ```bash
   ssh-keygen -t ed25519 -f sise-deploy -C "github-actions sise"
   ```
3. Den **öffentlichen** Schlüssel (`sise-deploy.pub`) auf dem Server in `~/.ssh/authorized_keys` eintragen
   (über konsoleH oder per `ssh-copy-id -i sise-deploy.pub -p 222 USER@SERVER`).
4. Fingerabdruck des Servers holen:
   ```bash
   ssh-keyscan -p 222 www123.your-server.de
   ```

## 2. Secrets in GitHub eintragen

GitHub → Repository → *Settings* → *Secrets and variables* → *Actions* → *New repository secret*:

| Secret | Inhalt |
|---|---|
| `HETZNER_SSH_HOST` | z. B. `www123.your-server.de` |
| `HETZNER_SSH_PORT` | z. B. `222` |
| `HETZNER_SSH_USER` | Benutzername des Webhostings |
| `HETZNER_SSH_KEY` | Inhalt der Datei `sise-deploy` (privater Schlüssel) |
| `HETZNER_SSH_KNOWN_HOSTS` | Ausgabe von `ssh-keyscan` aus Schritt 1.4 |
| `HETZNER_TARGET_DIR` | Web-Ordner der Domain, z. B. `public_html/` |

> ⚠️ Das Deployment löscht im Zielordner alle Dateien, die nicht zur Website gehören
> (`rsync --delete`, ausgenommen `.well-known/`). Der Zielordner muss deshalb **nur** für diese Website da sein.

## 3. CMS-Login über GitHub

Das CMS (`/admin/`) speichert direkt ins GitHub-Repository. Für den Login braucht es eine
**GitHub-OAuth-App** und die kleinen PHP-Skripte in `static/admin/oauth/`.

1. GitHub → *Settings* → *Developer settings* → *OAuth Apps* → *New OAuth App*
   (am besten in der Organisation bzw. beim Konto, dem das Repository gehört):
   - Homepage URL: `https://sichtbarundselbstbestimmt.org`
   - Authorization callback URL: `https://sichtbarundselbstbestimmt.org/admin/oauth/callback.php`
2. *Client ID* notieren, *Generate a new client secret* → Secret notieren.
3. Auf dem Server **eine Ebene über dem Web-Ordner** (also neben `public_html/`, nicht darin!)
   die Datei `decap-oauth-config.php` anlegen:
   ```php
   <?php
   return [
       'client_id'     => 'HIER_CLIENT_ID',
       'client_secret' => 'HIER_CLIENT_SECRET',
       'redirect_uri'  => 'https://sichtbarundselbstbestimmt.org/admin/oauth/callback.php',
       'site_origin'   => 'https://sichtbarundselbstbestimmt.org',
       'scope'         => 'repo', // bei einem öffentlichen Repository reicht 'public_repo'
   ];
   ```
4. In [`static/admin/config.yml`](static/admin/config.yml) prüfen, dass `backend.repo` auf das richtige
   Repository zeigt (`Besitzer/Name`).

Wer im CMS arbeiten soll, braucht ein **GitHub-Konto mit Schreibrechten** auf das Repository.

## 4. Domain und HTTPS

- Domain im Hetzner-Konto auf den Web-Ordner zeigen lassen und das (kostenlose) SSL-Zertifikat aktivieren.
- Danach in `static/.htaccess` die Zeilen unter „HTTPS erzwingen“ einkommentieren.
- Die Seite muss unter genau der Adresse aufgerufen werden, die in `site_origin` steht
  (mit oder ohne `www` einheitlich), sonst schlägt der CMS-Login fehl.

---

## Lokal arbeiten

```bash
npm install
hugo server -D          # Website unter http://localhost:1313
npx decap-server        # zusätzlich, um das CMS lokal ohne Login zu nutzen (/admin/)
```
