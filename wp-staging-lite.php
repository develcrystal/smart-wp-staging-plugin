<?php
/*
Plugin Name: WP Staging Lite
Description: 1-Klick-Staging, Sync & Backup für WordPress. Pro-Features verfügbar!
Version: 1.0.0
Author: Dein Name
Text Domain: wp-staging-lite
*/

if (!defined('ABSPATH')) exit;

define('WPSTAGING_LITE_VERSION', '1.0.0');

// 1. Staging-Schutz: noindex, robots.txt, Header
add_action('init', function() {
    if (wpstaging_is_staging()) {
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
        'Staging',
        'Staging',
        'manage_options',
        'wpstaging-lite',
        'wpstaging_admin_page',
        'dashicons-admin-site',
        3
    );
});

function wpstaging_admin_page() {
    echo '<div class="wrap"><h1>WP Staging Lite</h1>';
    echo '<p>1-Klick-Staging, Sync & Backup für WordPress.</p>';
    echo '<h2>Staging-Umgebung anlegen</h2>';
    echo '<form method="post">';
    echo '<input type="submit" name="wpstaging_create" class="button button-primary" value="Staging jetzt anlegen">';
    echo '</form>';
    if (isset($_POST['wpstaging_create'])) {
        wpstaging_create_staging();
    }
    echo '<h2>Backups</h2>';
    wpstaging_backup_overview();
    echo '<h2>Pro-Features</h2>';
    echo '<p>Automatische Syncs, Git-Integration, mehrere Staging-Umgebungen uvm. <a href="#" target="_blank">Jetzt upgraden!</a></p>';
    echo '</div>';
}

// 3. Staging anlegen (Dummy-Logik)
function wpstaging_create_staging() {
    echo '<div class="notice notice-success"><p>Staging-Umgebung wurde (Demo) angelegt. Schutz gegen Suchmaschinen ist aktiv!</p></div>';
    // Hier: Klonen von Dateien/DB, Setzen von WP_STAGING_MODE, robots.txt, etc.
}

// 4. Backup-Übersicht und Excludes
function wpstaging_backup_overview() {
    echo '<p>Backups werden automatisch vor jedem Sync erstellt.</p>';
    echo '<form method="post">';
    echo '<label>Vom Backup ausschließen (ein Eintrag pro Zeile):<br>';
    $excludes = get_option('wpstaging_backup_excludes', "wp-content/cache\nwp-content/backups\n*.zip\n*.mp4");
    echo '<textarea name="wpstaging_excludes" rows="4" cols="50">' . esc_textarea($excludes) . '</textarea>';
    echo '</label><br>';
    echo '<input type="submit" name="wpstaging_save_excludes" class="button" value="Excludes speichern">';
    echo '</form>';
    if (isset($_POST['wpstaging_save_excludes'])) {
        update_option('wpstaging_backup_excludes', sanitize_textarea_field($_POST['wpstaging_excludes']));
        echo '<div class="updated"><p>Excludes gespeichert!</p></div>';
    }
    // (Demo) Backup-Liste anzeigen
    echo '<ul><li>Backup vom 16.04.2025 (Demo)</li></ul>';
}

// 5. Helper: Staging-Modus erkennen
function wpstaging_is_staging() {
    // In echter Logik: z.B. per Konstante in wp-config.php oder anhand der URL
    return defined('WP_STAGING_MODE') && WP_STAGING_MODE === true;
}
