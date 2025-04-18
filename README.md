# WordPress Staging mit Docker & Web-UI

Dieses Projekt stellt eine professionelle Staging-Umgebung für WordPress bereit – inklusive moderner Weboberfläche, automatischer Synchronisation und Git-Integration.

## Features
- **Live & Staging:** Vollständig getrennte WordPress-Instanzen (inkl. Datenbanken)
- **Sync-Service:** Automatische Synchronisation (Dateien & DB) von Live → Staging
- **Web-UI:** Moderne Admin-Oberfläche (React) zur Steuerung und Übersicht
- **Sicherheit:** Passwortschutz, IP-Restriktion, HTTPS (Traefik)
- **Git-Integration:** Änderungen im Staging können versioniert werden

## Schnellstart
```bash
git clone <REPO-URL>
cd wp-staging-docker
cp .env.example .env
docker-compose up -d
```

Web-UI: [http://localhost:8080](http://localhost:8080)

## Projektstruktur
- `admin-ui/` – Web-UI (React)
- `live/` – Live-WordPress-Daten
- `staging/` – Staging-WordPress-Daten
- `db_live/`, `db_staging/` – Datenbankdaten
- `sync-script.sh` – Synchronisationsskript
- `docker-compose.yml` – Docker Setup
- `.env` – Zentrale Konfiguration

## Sicherheit & Hinweise
- Passwörter und Secrets **nur** in `.env` pflegen!
- Staging ist durch Traefik und robots.txt geschützt.

---

Für individuelle Anpassungen: Siehe Dokumentation oder kontaktiere den Entwickler.
