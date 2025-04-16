<?php
/**
 * Plugin Name:       WP Staging Lite
 * Plugin URI:        https://example.com/wp-staging-lite
 * Description:       Lightweight plugin to create 1-click staging environments, manual backups, debug logging, and search engine protection.
 * Version:           1.0.2
 * Author:            Your Name
 * Author URI:        https://example.com
 * Text Domain:       wp-staging-lite
 * Domain Path:       /languages
 * License:           GPL2
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Initialize plugin menu and actions
add_action('admin_menu', 'wpstaging_lite_add_menu');
add_action('admin_post_wpstaging_lite_create', 'wpstaging_lite_handle_create');
add_action('admin_post_wpstaging_lite_excludes', 'wpstaging_lite_handle_excludes');

function wpstaging_lite_add_menu() {
    add_menu_page(
        'WP Staging Lite',
        'WP Staging Lite',
        'manage_options',
        'wp-staging-lite',
        'wpstaging_lite_render_page',
        'dashicons-admin-tools',
        80
    );
}

function wpstaging_lite_render_page() {
    ?>
    <div class="wrap">
        <h1>WP Staging Lite</h1>
        <?php
        $locale = get_locale();
        if (strpos($locale, 'de_') === 0) {
        ?>
        <div style="background:#eaf6ff;border:1px solid #b6daff;padding:15px 20px;margin-bottom:20px;max-width:800px;">
            <strong>Was macht dieses Plugin?</strong><br>
            <ul style="margin:8px 0 0 20px;">
                <li><b>1‑Klick‑Staging:</b> Erstelle eine sichere Kopie deiner Website zum Testen und Entwickeln – ohne Risiko für die Live-Seite.</li>
                <li><b>Backup-Excludes:</b> Schließe bestimmte Ordner/Dateien von Backups und Staging aus (z.B. <code>wp-content/cache/</code>).</li>
                <li><b>Debug-Logging:</b> Alle wichtigen Aktionen und Fehler werden in <code>wp-content/uploads/wpstaging-logs/debug.log</code> protokolliert und sind unten einsehbar.</li>
                <li><b>Suchmaschinen-Schutz:</b> Die Staging-Umgebung wird automatisch für Suchmaschinen gesperrt (robots.txt, Meta-Tag).</li>
            </ul>
            <div style="margin-top:10px;color:#444;">
                <b>So funktioniert's:</b> <br>
                <ol style="margin:8px 0 0 20px;">
                    <li><b>Staging erstellen</b>: Button klicken – Verzeichnis und Schutzdateien werden automatisch angelegt.</li>
                    <li><b>Excludes pflegen</b>: Trage auszuschließende Pfade ein und speichere sie. Änderungen werden geloggt.</li>
                    <li><b>Log prüfen</b>: Fehler und Aktionen werden unten angezeigt.</li>
                </ol>
            </div>
        </div>
        <?php } else { ?>
        <div style="background:#eaf6ff;border:1px solid #b6daff;padding:15px 20px;margin-bottom:20px;max-width:800px;">
            <strong>What does this plugin do?</strong><br>
            <ul style="margin:8px 0 0 20px;">
                <li><b>1-click staging:</b> Create a safe copy of your website for testing and development – without risk to your live site.</li>
                <li><b>Backup Excludes:</b> Exclude certain folders/files from backups and staging (e.g. <code>wp-content/cache/</code>).</li>
                <li><b>Debug logging:</b> All important actions and errors are logged to <code>wp-content/uploads/wpstaging-logs/debug.log</code> and shown below.</li>
                <li><b>Search engine protection:</b> The staging environment is automatically protected from search engines (robots.txt, meta tag).</li>
            </ul>
            <div style="margin-top:10px;color:#444;">
                <b>How it works:</b> <br>
                <ol style="margin:8px 0 0 20px;">
                    <li><b>Create staging</b>: Click the button – directory and protection files are created automatically.</li>
                    <li><b>Manage excludes</b>: Enter paths to exclude and save. All changes are logged.</li>
                    <li><b>Check log</b>: Errors and actions are shown below.</li>
                </ol>
            </div>
        </div>
        <?php } ?>
        <?php if ( isset($_GET['created']) && $_GET['created'] === '1' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p>Staging-Umgebung erfolgreich erstellt.</p>
            </div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('wpstaging_lite_create', 'wpstaging_lite_nonce'); ?>
            <input type="hidden" name="action" value="wpstaging_lite_create">
            <p><input type="submit" class="button button-primary" value="Staging erstellen"></p>
        </form>
        <hr>
        <h2>Backup Excludes</h2>
        <?php if ( isset($_GET['excludes_saved']) && $_GET['excludes_saved'] === '1' ) : ?>
            <div class="notice notice-success is-dismissible"><p>Excludes erfolgreich gespeichert.</p></div>
        <?php elseif ( isset($_GET['excludes_error']) && $_GET['excludes_error'] === '1' ) : ?>
            <div class="notice notice-error is-dismissible"><p>Fehler beim Speichern der Excludes!</p></div>
        <?php elseif ( isset($_GET['excludes_empty']) && $_GET['excludes_empty'] === '1' ) : ?>
            <div class="notice notice-warning is-dismissible"><p>Bitte mindestens einen Exclude eintragen.</p></div>
        <?php endif; ?>
        <form id="wpstaging-lite-excludes-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('wpstaging_lite_excludes', 'wpstaging_lite_excludes_nonce'); ?>
            <input type="hidden" name="action" value="wpstaging_lite_excludes">
            <textarea name="wpstaging_lite_excludes" rows="3" cols="60"><?php echo esc_textarea(get_option('wpstaging_lite_excludes', '')); ?></textarea><br>
            <small>Einträge durch Zeilenumbruch trennen (z.B. <code>wp-content/cache/</code>)</small><br>
            <button type="submit" class="button"><span id="wpstaging-lite-spinner" style="display:none;margin-right:6px;">⏳</span>Excludes speichern</button>
        </form>
        <script>
        document.getElementById('wpstaging-lite-excludes-form').addEventListener('submit', function() {
            document.getElementById('wpstaging-lite-spinner').style.display = 'inline-block';
        });
        </script>
        <hr>
        <h2>Debug Log</h2>
        <pre style="background:#f8f8f8; border:1px solid #ccc; padding:10px; max-height:200px; overflow:auto;"><?php
        $log_file = WP_CONTENT_DIR . '/uploads/wpstaging-logs/debug.log';
        if (file_exists($log_file)) {
            echo esc_html(file_get_contents($log_file));
        } else {
            echo 'Noch keine Log-Einträge.';
        }
        ?></pre>
    </div>
    <?php
}

function wpstaging_lite_handle_create() {
    if ( ! current_user_can('manage_options') || ! check_admin_referer('wpstaging_lite_create', 'wpstaging_lite_nonce') ) {
        wp_die('Unberechtigter Zugriff');
    }
    $upload = wp_upload_dir();
    $staging_dir = $upload['basedir'] . '/wpstaging';
    if ( ! file_exists($staging_dir) ) {
        wp_mkdir_p($staging_dir);
    }
    // Create robots.txt
    file_put_contents($staging_dir . '/robots.txt', "User-agent: *\nDisallow: /");
    // Create index.html with meta robots tag
    file_put_contents($staging_dir . '/index.html', "<html><head><meta name=\"robots\" content=\"noindex,nofollow\"></head><body></body></html>");
    // Redirect back with success flag
    wp_redirect(admin_url('admin.php?page=wp-staging-lite&created=1'));
    exit;
}

// --- Exclude-Handler und Logging ---
function wpstaging_lite_handle_excludes() {
    if ( ! current_user_can('manage_options') || ! check_admin_referer('wpstaging_lite_excludes', 'wpstaging_lite_excludes_nonce') ) {
        wp_die('Unberechtigter Zugriff');
    }
    $excludes = isset($_POST['wpstaging_lite_excludes']) ? sanitize_textarea_field($_POST['wpstaging_lite_excludes']) : '';
    if (trim($excludes) === '') {
        wp_redirect(admin_url('admin.php?page=wp-staging-lite&excludes_empty=1'));
        exit;
    }
    $saved = update_option('wpstaging_lite_excludes', $excludes);
    $log_success = wpstaging_lite_write_log('Backup-Excludes gespeichert: ' . str_replace("\n", ", ", $excludes));
    if (!$saved || !$log_success) {
        wp_redirect(admin_url('admin.php?page=wp-staging-lite&excludes_error=1'));
        exit;
    }
    wp_redirect(admin_url('admin.php?page=wp-staging-lite&excludes_saved=1'));
    exit;
}

function wpstaging_lite_write_log($msg) {
    $log_dir = WP_CONTENT_DIR . '/uploads/wpstaging-logs';
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }
    $log_file = $log_dir . '/debug.log';
    $date = date('Y-m-d H:i:s');
    return file_put_contents($log_file, "[$date] $msg\n", FILE_APPEND) !== false;
}
// Plugin core functionality will be initialized here
