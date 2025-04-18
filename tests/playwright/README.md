# Playwright UI-Tests für WP Staging Lite

## Inhalt
- `wp-staging-lite-admin.spec.ts`: Automatisierte Tests für Admin-Login, Plugin-Wizard, Usability und Feedback.

## Ausführung der Tests

**Vorbereitung:**
1. Playwright installieren (falls nicht vorhanden):
   ```powershell
   npm install -D playwright
   ```
2. Browser-Engines installieren:
   ```powershell
   npx playwright install
   ```

**Test ausführen:**
```powershell
npx playwright test tests/playwright/wp-staging-lite-admin.spec.ts
```

**Hinweis:**
- Zugangsdaten und URLs sind im Testskript hinterlegt.
- Selektoren ggf. an Plugin-Frontend anpassen!
- Testreport wird nach Durchlauf im Terminal angezeigt.

## Erweiterung
- Weitere Testfälle können als zusätzliche `.spec.ts`-Dateien im selben Verzeichnis abgelegt werden.
