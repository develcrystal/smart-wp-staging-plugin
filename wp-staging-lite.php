<?php
/**
 * Plugin Name:       WP Staging Lite
 * Plugin URI:        https://example.com/wp-staging-lite
 * Description:       Lightweight plugin to create 1-click staging environments, manual backups, debug logging, and search engine protection.
 * Version:           1.0.2
 * Author:            Romain Hill, AI Developer
 * Author URI:        https://github.com/develcrystal
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
    $locale = get_locale();
    $step = isset($_GET['wpstaging_step']) ? intval($_GET['wpstaging_step']) : 1;
    $step = max(1, min($step, 4)); // nur 1-4 zulassen
    $base_url = admin_url('admin.php?page=wp-staging-lite');
    $step_labels = [
        'de' => ['Staging erstellen', 'Excludes verwalten', 'Debug Log prüfen', 'Staging testen'],
        'en' => ['Create Staging', 'Manage Excludes', 'Check Debug Log', 'Test Staging']
    ];
    $lang = (strpos($locale, 'de_') === 0) ? 'de' : 'en';
    $labels = $step_labels[$lang];
    
    ?>
    <div class="wrap">
        <h1>WP Staging Lite</h1>
        <style>
        .wpstaging-stepper {display:flex;gap:8px;margin:24px 0 28px 0;}
        .wpstaging-step {padding:6px 16px;border-radius:16px;border:1px solid #b6daff;background:#eaf6ff;color:#222;opacity:0.7;}
        .wpstaging-step.active {background:#007cba;color:#fff;opacity:1;}
        .wpstaging-step.done {background:#b6daff;color:#222;opacity:1;}
        .wpstaging-stepper-hr {border:0;border-top:1.5px solid #b6daff;margin:0 0 24px 0;}
        .wpstaging-step-content {max-width:700px;padding:24px 32px 32px 32px;background:#f8f8f8;border-radius:10px;border:1px solid #eaf6ff;box-shadow:0 2px 8px #eaf6ff44;}
        .wpstaging-step-nav {margin-top:24px;display:flex;gap:8px;}
        </style>
        <div class="wpstaging-stepper">
        <?php for($i=1;$i<=4;$i++): ?>
            <div class="wpstaging-step<?php echo ($step==$i)?' active':(($step>$i)?' done':''); ?>">Step <?php echo $i; ?>: <?php echo esc_html($labels[$i-1]); ?></div>
        <?php endfor; ?>
        </div>
        <div class="wpstaging-stepper-hr"></div>
        <div class="wpstaging-step-content">
        <?php
        // Schritt 1: Staging erstellen
        if($step==1){
            if($lang=="de"){
                echo "<h2>Staging-Umgebung erstellen</h2><p><strong>Hinweis:</strong> Hier kannst du eine vollständige Staging-Kopie deiner Website anlegen. Alle Dateien, Themes, Plugins und Medien werden kopiert. Die Staging-Umgebung ist für Suchmaschinen gesperrt.</p>";
            }else{
                echo "<h2>Create Staging Environment</h2><p><strong>Note:</strong> Here you can create a full staging copy of your website. All files, themes, plugins, and media will be copied. The staging environment is blocked from search engines.</p>";
            }
            echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'" id="wpstaging-lite-createform">
            '.wp_nonce_field('wpstaging_lite_create', 'wpstaging_lite_nonce', true, false).'
            <input type="hidden" name="action" value="wpstaging_lite_create">
            <p><input type="submit" class="button button-primary" value="'.esc_attr($labels[0]).'" title="Erstellt eine komplette Kopie deiner WordPress-Installation im Verzeichnis /staging/."></p>
            </form>';
            echo '<div id="wpstaging-lite-progress" style="display:none;margin-top:16px;"><span class="spinner is-active" style="float:none;vertical-align:middle;"></span> <span id="wpstaging-lite-progress-text">'.($lang=="de"?"Staging wird erstellt, bitte warten ...":"Creating staging, please wait ...").'</span></div>';
            echo '<script>document.getElementById("wpstaging-lite-createform").addEventListener("submit",function(){document.getElementById("wpstaging-lite-progress").style.display="block";});</script>';
            if(isset($_GET['created']) && $_GET['created']==='1'){
                echo '<div class="notice notice-success is-dismissible" style="margin-top:16px;background:#e7fbe7;border-color:#46b450;color:#1a531b;"><p><strong>'.($lang=="de"?"Staging-Umgebung erfolgreich erstellt!":"Staging environment created successfully!").'</strong><br>'.($lang=="de"?"Verzeichnis: <code>/staging/</code><br>Alle Dateien wurden kopiert. Weiter mit Schritt 2!":"Directory: <code>/staging/</code><br>All files copied. Continue with step 2!").'</p></div>';
            }elseif(isset($_GET['created']) && $_GET['created']==='0' && isset($_GET['error']) && $_GET['error']==='noplace'){
                echo '<div class="notice notice-error is-dismissible" style="margin-top:16px;background:#ffeaea;border-color:#dc3232;color:#a00;"><p><strong>'.($lang=="de"?"Nicht genügend Speicherplatz!":"Not enough disk space!").'</strong><br>'.($lang=="de"?"Bitte lösche alte Backups oder schaffe freien Speicher, bevor du fortfährst. <a href=\"https://wordpress.org/support/article/optimizing-disk-space/\" target=\"_blank\">Tipps zum Speicherplatz freigeben</a>.":"Please delete old backups or free up space before proceeding. <a href=\"https://wordpress.org/support/article/optimizing-disk-space/\" target=\"_blank\">Tips for freeing disk space</a>.").'</p></div>';
            }
        }
        // Schritt 2: Excludes verwalten
        if($step==2){
            if($lang=="de"){
                echo "<h2>Backup Excludes verwalten</h2><p><strong>Tipp:</strong> Hier kannst du angeben, welche Ordner/Dateien beim Staging ausgeschlossen werden sollen (z.B. große Backups oder Cache-Ordner).</p>";
            }else{
                echo "<h2>Manage Backup Excludes</h2><p><strong>Tip:</strong> Specify files or folders to exclude from staging/backup (e.g. large backups or cache folders).</p>";
            }
            echo '<form id="wpstaging-lite-excludes-form" method="post" action="'.esc_url(admin_url('admin-post.php')).'">
                '.wp_nonce_field('wpstaging_lite_excludes', 'wpstaging_lite_excludes_nonce', true, false).'
                <input type="hidden" name="action" value="wpstaging_lite_excludes">
                <textarea name="wpstaging_lite_excludes" rows="3" cols="60" title="'.($lang=="de"?"Jeder Pfad in einer neuen Zeile. Beispiel: wp-content/cache/":"One path per line. Example: wp-content/cache/").'">'.esc_textarea(get_option('wpstaging_lite_excludes', '')).'</textarea><br>
                <small>'.($lang=="de"?"Einträge durch Zeilenumbruch trennen (z.B. <code>wp-content/cache/</code>)":"Separate entries by line break (e.g. <code>wp-content/cache/</code>)").'</small><br>
                <button type="submit" class="button" title="'.($lang=="de"?"Speichert die Excludes für das Staging.":"Save excludes for staging.").'"><span id="wpstaging-lite-spinner" style="display:none;margin-right:6px;">⏳</span>'.esc_html($labels[1]).'</button>
            </form>';
            echo '<script>document.getElementById("wpstaging-lite-excludes-form").addEventListener("submit",function(){document.getElementById("wpstaging-lite-spinner").style.display="inline-block";});</script>';
            if(isset($_GET['excludes_saved']) && $_GET['excludes_saved']==='1'){
                echo '<div class="notice notice-success is-dismissible" style="margin-top:16px;background:#e7fbe7;border-color:#46b450;color:#1a531b;"><p>'.($lang=="de"?"Excludes erfolgreich gespeichert.":"Excludes saved successfully.").'</p></div>';
            }elseif(isset($_GET['excludes_error']) && $_GET['excludes_error']==='1'){
                echo '<div class="notice notice-error is-dismissible" style="margin-top:16px;background:#ffeaea;border-color:#dc3232;color:#a00;"><p>'.($lang=="de"?"Fehler beim Speichern der Excludes!":"Error saving excludes!").'</p></div>';
            }elseif(isset($_GET['excludes_empty']) && $_GET['excludes_empty']==='1'){
                echo '<div class="notice notice-warning is-dismissible" style="margin-top:16px;background:#fffbe5;border-color:#ffb900;color:#8a6d00;"><p>'.($lang=="de"?"Bitte mindestens einen Exclude eintragen.":"Please enter at least one exclude.").'</p></div>';
            }
        }
        // Schritt 3: Debug Log prüfen
        if($step==3){
            if($lang=="de"){
                echo "<h2>Debug Log prüfen</h2><p><strong>Hinweis:</strong> Hier werden alle wichtigen Aktionen, Fehler und Hinweise zum Staging-Prozess angezeigt. Das Logfile findest du auch unter <code>wp-content/uploads/wpstaging-logs/debug.log</code>.</p>";
            }else{
                echo "<h2>Check Debug Log</h2><p><strong>Note:</strong> All important actions, errors, and notices regarding the staging process are shown here. You can also find the logfile at <code>wp-content/uploads/wpstaging-logs/debug.log</code>.</p>";
            }
            $log_file = WP_CONTENT_DIR . '/uploads/wpstaging-logs/debug.log';
            echo '<button onclick="location.reload();" class="button" style="margin-bottom:8px;" title="'.($lang=="de"?"Aktualisiert das Log. Nützlich, wenn du gerade ein Staging erstellt hast.":"Reloads the log. Useful after creating a staging.").'">'.($lang=="de"?"Log aktualisieren":"Reload log").'</button>';
            echo '<pre style="background:#fff;border:1px solid #ccc;padding:10px;max-height:200px;overflow:auto;">';
            if(file_exists($log_file)){
                echo esc_html(file_get_contents($log_file));
            }else{
                echo $lang=="de"?'Noch keine Log-Einträge.':'No log entries yet.';
            }
            echo '</pre>';
        }
        // Schritt 4: Staging testen
        if($step==4){
            $staging_url = site_url('staging/');
            if($lang=="de"){
                echo "<h2>Staging-Umgebung testen</h2><p><strong>Fertig!</strong> Deine Staging-Umgebung ist jetzt einsatzbereit. Du findest sie unter: <a href='".esc_url($staging_url)."' target='_blank' title='Öffnet die Staging-Seite in neuem Tab'>".esc_html($staging_url)."</a></p>
                <div style='background:#e7fbe7;border:1px solid #46b450;color:#1a531b;padding:8px 14px;margin-bottom:12px;border-radius:6px;'><strong>Hinweise:</strong><ul style='margin:8px 0 0 20px;'><li>Stelle sicher, dass <code>robots.txt</code> und <code>index.html</code> vorhanden sind.</li><li>Für echtes Staging muss hier eine eigene Datenbank angelegt werden. Passe ggf. die <code>wp-config.php</code> im Staging-Verzeichnis an.</li><li>Wenn die Staging-Seite nicht lädt, prüfe Dateirechte und Datenbank.</li><li><a href='https://wordpress.org/support/article/creating-a-staging-site/' target='_blank'>Weitere Tipps zur Staging-Konfiguration</a></li></ul></div>
                <div style='background:#fffbe5;border:1px solid #ffb900;color:#8a6d00;padding:8px 14px;margin-bottom:12px;border-radius:6px;'><strong>Wie lösche ich das Staging?</strong> Einfach das Verzeichnis <code>/staging/</code> und ggf. die Staging-Datenbank entfernen.</div>";
            }else{
                echo "<h2>Test Staging Environment</h2><p><strong>Ready!</strong> Your staging environment is ready. You can find it at: <a href='".esc_url($staging_url)."' target='_blank' title='Opens staging site in new tab'>".esc_html($staging_url)."</a></p>
                <div style='background:#e7fbe7;border:1px solid #46b450;color:#1a531b;padding:8px 14px;margin-bottom:12px;border-radius:6px;'><strong>Notes:</strong><ul style='margin:8px 0 0 20px;'><li>Ensure <code>robots.txt</code> and <code>index.html</code> exist.</li><li>For real staging, create a dedicated database and adjust <code>wp-config.php</code> in the staging directory.</li><li>If the staging site does not load, check file permissions and database.</li><li><a href='https://wordpress.org/support/article/creating-a-staging-site/' target='_blank'>More tips for staging configuration</a></li></ul></div>
                <div style='background:#fffbe5;border:1px solid #ffb900;color:#8a6d00;padding:8px 14px;margin-bottom:12px;border-radius:6px;'><strong>How to remove staging?</strong> Just delete the <code>/staging/</code> directory and the staging database if needed.</div>";
            }
        }
        // Navigation
        echo '<div class="wpstaging-step-nav">';
        if($step>1){
            echo '<a href="'.esc_url($base_url.'&wpstaging_step='.($step-1)).'" class="button">'.($lang=="de"?"Zurück":"Back").'</a>';
        }
        if($step<4){
            echo '<a href="'.esc_url($base_url.'&wpstaging_step='.($step+1)).'" class="button button-primary">'.($lang=="de"?"Weiter":"Next").'</a>';
        }
        echo '</div>';
        ?>
        </div>
    </div>

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
    $root = ABSPATH;
    $staging_dir = $root . 'staging';
    $excludes = get_option('wpstaging_lite_excludes', '');
    $exclude_list = array_filter(array_map('trim', explode("\n", $excludes)));

    // --- Speicherplatz-Check ---
    function wpstaging_lite_dirsize($dir, $excludes = []) {
        $size = 0;
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($rii as $file) {
            $skip = false;
            foreach ($excludes as $ex) {
                if (strpos($file->getPathname(), $ex) !== false) {
                    $skip = true; break;
                }
            }
            if ($skip) continue;
            $size += $file->getSize();
        }
        return $size;
    }
    $needed_bytes = wpstaging_lite_dirsize($root, $exclude_list);
    $free_bytes = disk_free_space($root);
    if ($free_bytes !== false && $needed_bytes > 0 && $free_bytes < ($needed_bytes * 1.1)) { // 10% Puffer
        wpstaging_lite_write_log('Nicht genügend Speicherplatz für Staging! Benötigt: '.round($needed_bytes/1024/1024,1).' MB, frei: '.round($free_bytes/1024/1024,1).' MB');
        wp_redirect(admin_url('admin.php?page=wp-staging-lite&created=0&error=noplace'));
        exit;
    }
    // --- Kopieren aller WP-Dateien (außer Excludes) ---
    function wpstaging_lite_copydir($src, $dst, $excludes = []) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ($file = readdir($dir))) {
            if ($file == '.' || $file == '..') continue;
            $skip = false;
            foreach ($excludes as $ex) {
                if (strpos($src.'/'.$file, $ex) !== false) {$skip = true; break;}
            }
            if ($skip) continue;
            if (is_dir($src.'/'.$file)) {
                wpstaging_lite_copydir($src.'/'.$file, $dst.'/'.$file, $excludes);
            } else {
                copy($src.'/'.$file, $dst.'/'.$file);
            }
        }
        closedir($dir);
    }
    if (!file_exists($staging_dir)) {
        mkdir($staging_dir, 0755, true);
    }
    wpstaging_lite_copydir($root, $staging_dir, $exclude_list);
    // --- wp-config.php für Staging anpassen ---
    $wpconfig = file_get_contents($root.'wp-config.php');
    if ($wpconfig) {
        $wpconfig = preg_replace(
            "/define\s*\(\s*'DB_NAME'\s*,\s*'([^']+)'\s*\)/",
            "define('DB_NAME', '$1_staging')",
            $wpconfig
        );
        file_put_contents($staging_dir.'/wp-config.php', $wpconfig);
    }
    // --- Schutzdateien ---
    file_put_contents($staging_dir . '/robots.txt', "User-agent: *\nDisallow: /");
    file_put_contents($staging_dir . '/index.html', "<html><head><meta name=\"robots\" content=\"noindex,nofollow\"></head><body></body></html>");
    // --- Logging ---
    wpstaging_lite_write_log('Staging erstellt unter '.$staging_dir.' ('.round($needed_bytes/1024/1024,1).' MB kopiert)');
    // --- Redirect ---
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
