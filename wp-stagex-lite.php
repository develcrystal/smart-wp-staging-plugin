<?php
/*
Plugin Name: WP StageX Lite
Description: 1-Klick-Staging, Sync & Backup für WordPress. Pro-Features verfügbar!
Version: 1.0.1
Author: romain hill ai developer
Text Domain: wp-stagex-lite
*/

if (!defined('ABSPATH')) exit;

define('WPSTAGEX_LITE_VERSION', '1.0.0');

// 1. Staging-Schutz: noindex, robots.txt, Header
add_action('init', function() {
    if (wpstagex_is_staging()) {
        add_action('wp_head', function() {
            echo '<meta name="robots" content="noindex, nofollow">\n';
        });
        add_action('send_headers', function() {
            header('X-Robots-Tag: noindex, nofollow', true);
        });
        // robots.txt überschreiben
        add_filter('robots_txt', function($output, $public) {
            return "User-agent: *\nDisallow: /\n";
        }, 10, 2);
    }
});

// 2. Admin-Menü
add_action('admin_menu', function() {
    add_menu_page(
        'StageX',
        'StageX',
        'manage_options',
        'wpstaging-lite',
        'wpstagex_admin_page',
        'dashicons-admin-site',
        3
    );
});

function wpstagex_admin_page() {
    echo '<div class="wrap" style="height:90vh">';
    echo '<iframe src="' . plugins_url('wp-staging-lite/admin-ui/index.html') . '" style="width:100%;height:85vh;border:none;"></iframe>';
    echo '</div>';
}

// 3. Staging anlegen (Dummy-Logik)
function wpstagex_create_staging() {
    wpstagex_log('Staging-Umgebung angelegt (Demo).');
    echo '<div class="notice notice-success"><p>Staging-Umgebung wurde (Demo) angelegt. Schutz gegen Suchmaschinen ist aktiv!</p></div>';
    // Hier: Klonen von Dateien/DB, Setzen von WP_STAGING_MODE, robots.txt, etc.
}

// 4. Backup-Übersicht und Excludes
function wpstagex_backup_overview() {
    echo '<p>Backups werden automatisch vor jedem Sync erstellt.</p>';
    echo '<form method="post">';
    echo '<label>Vom Backup ausschließen (ein Eintrag pro Zeile):<br>';
    $excludes = get_option('wpstagex_backup_excludes', "wp-content/cache\nwp-content/backups\n*.zip\n*.mp4");
    echo '<textarea name="wpstagex_excludes" rows="4" cols="50">' . esc_textarea($excludes) . '</textarea>';
    echo '</label><br>';
    echo '<input type="submit" name="wpstagex_save_excludes" class="button" value="Excludes speichern">';
    echo '</form>';
    if (isset($_POST['wpstagex_save_excludes'])) {
        update_option('wpstagex_backup_excludes', sanitize_textarea_field($_POST['wpstagex_excludes']));
        wpstagex_log('Backup-Excludes gespeichert: ' . str_replace("\n", ", ", sanitize_textarea_field($_POST['wpstagex_excludes'])));
        echo '<div class="updated"><p>Excludes gespeichert!</p></div>';
    }
    // (Demo) Backup-Liste anzeigen
    echo '<ul><li>Backup vom 16.04.2025 (Demo)</li></ul>';
    echo '<h3>Debug-Log</h3>';
    echo '<pre style="background:#222;color:#0f0;max-height:200px;overflow:auto;">' . esc_html(wpstagex_get_log()) . '</pre>';
}

// 5. Helper: Staging-Modus erkennen
function wpstagex_is_staging() {
    // In echter Logik: z.B. per Konstante in wp-config.php oder anhand der URL
    return defined('WP_STAGING_MODE') && WP_STAGING_MODE === true;
}

// 6. Debugging & Logging
function wpstagex_log($msg) {
    $upload_dir = wp_upload_dir();
    $log_dir = $upload_dir['basedir'] . '/wpstaging-logs';
    if (!file_exists($log_dir)) {
        wp_mkdir_p($log_dir);
    }
    $log_file = $log_dir . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] $msg\n";
    file_put_contents($log_file, $entry, FILE_APPEND);
}

function wpstagex_get_log() {
    $upload_dir = wp_upload_dir();
    $log_file = $upload_dir['basedir'] . '/wpstaging-logs/debug.log';
    if (file_exists($log_file)) {
        $lines = file($log_file);
        $last = array_slice($lines, -20); // Zeige die letzten 20 Einträge
        return implode('', $last);
    }
    return 'Noch keine Log-Einträge.';
}

