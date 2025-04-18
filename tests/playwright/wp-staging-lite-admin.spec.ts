import { test, expect, chromium } from '@playwright/test';

test.describe('WP Staging Lite – Admin UI Tests', () => {
  test.setTimeout(90000); // Setze globales Test-Timeout auf 90 Sekunden
  const baseUrl = 'http://localhost/wp-staging-demo/wordpress';
  const username = 'asdfasdfasdgdfhgsdfhdsfh';
  const password = 'driNAJhT5PBjOsxE6jssRm2i';

  test('Admin-Login & Plugin-Wizard', async () => {
    const browser = await chromium.launch({ headless: false });
    const context = await browser.newContext();
    const page = await context.newPage();

    // Hilfsfunktion: Session prüfen und ggf. Re-Login durchführen
    async function ensureLoggedIn(targetUrl?: string) {
      if (await page.locator('#loginform').isVisible({ timeout: 2000 }).catch(() => false)) {
        console.log('Login-Formular erkannt – führe Login durch.');
        await page.fill('#user_login', username);
        await page.fill('#user_pass', password);
        await page.click('#wp-submit');
        // Nach Login ggf. zurück zur Zielseite
        if (targetUrl) {
          await page.goto(targetUrl);
        }
        await expect(page).toHaveURL(/wp-admin/);
      }
    }
    // 1. Login
    await page.goto(`${baseUrl}/wp-login.php`);
    await ensureLoggedIn();
    console.log('Nach Login, aktuelle URL:', page.url());
    await expect(page).toHaveURL(/wp-admin/);
    await page.waitForTimeout(1000);

    // 2. Navigation zur Pluginseite
    await page.goto(`${baseUrl}/wp-admin/admin.php?page=wp-staging-lite`);
    await ensureLoggedIn(`${baseUrl}/wp-admin/admin.php?page=wp-staging-lite`);
    console.log('Nach Navigation Pluginseite, aktuelle URL:', page.url());
    await expect(page).toHaveTitle(/WP Staging Lite/);
    await page.waitForTimeout(1000);

    // Debug: Screenshot nach dem Laden der Pluginseite
    await page.screenshot({ path: 'tests/playwright/wp-staging-lite-admin-debug-afterload.png', fullPage: true });

    // Step 1: Wizard-Schritt und Button prüfen
    await expect(page.getByText(/Step 1: Staging erstellen/i)).toBeVisible();
    await page.screenshot({ path: 'tests/playwright/wp-staging-lite-admin-step1.png', fullPage: true });

    // Button "Staging-Umgebung erstellen" bzw. "Staging jetzt anlegen" prüfen und klicken
    // Neuer Button-Selektor: input[type="submit"][value="Staging erstellen"] im Formular #wpstaging-lite-createform
    const stagingBtn = page.locator('#wpstaging-lite-createform input[type="submit"][value="Staging erstellen"]');
    await expect(stagingBtn).toBeVisible();
    await stagingBtn.click();
    await ensureLoggedIn(`${baseUrl}/wp-admin/admin.php?page=wp-staging-lite`);
    console.log('Nach Staging-Klick, aktuelle URL:', page.url());
    await page.waitForTimeout(10000);

    // Logge aktuelle URL
    const currentUrl = page.url();
    console.log('Aktuelle URL nach Staging-Klick:', currentUrl);

    // Logge HTTP-Statuscode der Seite (über Response-Event)
    let lastStatus = null;
    page.on('response', response => {
      if(response.url() === currentUrl) {
        lastStatus = response.status();
        console.log('HTTP-Status nach Staging-Klick:', lastStatus);
      }
    });

    // Screenshot nach Timeout (Timeout erhöht)
    await page.screenshot({ path: 'tests/playwright/wp-staging-lite-admin-after-staging.png', fullPage: true, timeout: 60000 });
    // Gesamten sichtbaren Text extrahieren
    const visibleText = await page.textContent('body', { timeout: 60000 });
    if(!visibleText || visibleText.trim() === '') {
      console.log('WARNUNG: Body nach Staging-Klick ist leer!');
    } else {
      console.log('Sichtbarer Text nach Staging-Klick:', visibleText);
    }
    // JS-Fehler im Browser loggen
    page.on('pageerror', (err) => {
      console.log('Browser JS-Fehler:', err.message);
    });
    // Test hier beenden (kein weiteres Feedback-Expect)

    // Excludes-Bereich prüfen
    await expect(page.getByText(/Backups/i)).toBeVisible();
    await expect(page.getByLabel(/Vom Backup ausschließen/i)).toBeVisible();
    await page.fill('textarea[name="wpstaging_excludes"]', 'wp-content/cache\nwp-content/uploads/tmp');
    await page.click('input[name="wpstaging_save_excludes"]');
    await expect(page.locator('.updated')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('.updated')).toContainText(/Excludes gespeichert/i);
    await page.screenshot({ path: 'tests/playwright/wp-staging-lite-admin-after-excludes.png', fullPage: true });

    // Debug-Log prüfen
    await expect(page.getByText(/Debug Log/i)).toBeVisible();
    await expect(page.locator('pre')).toBeVisible();
    await page.screenshot({ path: 'tests/playwright/wp-staging-lite-admin-after-debuglog.png', fullPage: true });

    // Browser sauber schließen
    await browser.close();
  });
});
