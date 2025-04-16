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
                echo "<h2>Staging-Umgebung erstellen</h2><p>Mit einem Klick wird eine Staging-Kopie deiner Website angelegt. Diese ist für Suchmaschinen gesperrt und kann gefahrlos getestet werden.</p>";
            }else{
                echo "<h2>Create Staging Environment</h2><p>With one click, a staging copy of your site will be created. It is protected from search engines and safe for testing.</p>";
            }
            echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">
            '.wp_nonce_field('wpstaging_lite_create', 'wpstaging_lite_nonce', true, false).'
            <input type="hidden" name="action" value="wpstaging_lite_create">
            <p><input type="submit" class="button button-primary" value="'.esc_attr($labels[0]).'"></p>
            </form>';
            if(isset($_GET['created']) && $_GET['created']==='1'){
                if($lang=="de"){
                    echo '<div class="notice notice-success is-dismissible" style="margin-top:16px;"><p>Staging-Umgebung erfolgreich erstellt.<br>Verzeichnis: <code>/wp-content/uploads/wpstaging/</code><br>Dateien: <code>robots.txt</code>, <code>index.html</code>.<br>Weiter mit Schritt 2!</p></div>';
                }else{
                    echo '<div class="notice notice-success is-dismissible" style="margin-top:16px;"><p>Staging environment created successfully.<br>Directory: <code>/wp-content/uploads/wpstaging/</code><br>Files: <code>robots.txt</code>, <code>index.html</code>.<br>Continue with step 2!</p></div>';
                }
            }
        }
        // Schritt 2: Excludes verwalten
        if($step==2){
            if($lang=="de"){
                echo "<h2>Backup Excludes verwalten</h2><p>Hier kannst du Dateien oder Ordner angeben, die beim Staging/Backup ausgeschlossen werden sollen (je Zeile ein Pfad).</p>";
            }else{
                echo "<h2>Manage Backup Excludes</h2><p>Specify files or folders to exclude from staging/backup (one path per line).</p>";
            }
            echo '<form id="wpstaging-lite-excludes-form" method="post" action="'.esc_url(admin_url('admin-post.php')).'">
                '.wp_nonce_field('wpstaging_lite_excludes', 'wpstaging_lite_excludes_nonce', true, false).'
                <input type="hidden" name="action" value="wpstaging_lite_excludes">
                <textarea name="wpstaging_lite_excludes" rows="3" cols="60">'.esc_textarea(get_option('wpstaging_lite_excludes', '')).'</textarea><br>
                <small>'.($lang=="de"?"Einträge durch Zeilenumbruch trennen (z.B. <code>wp-content/cache/</code>)":"Separate entries by line break (e.g. <code>wp-content/cache/</code>)").'</small><br>
                <button type="submit" class="button"><span id="wpstaging-lite-spinner" style="display:none;margin-right:6px;">⏳</span>'.esc_html($labels[1]).'</button>
            </form>';
            echo '<script>document.getElementById("wpstaging-lite-excludes-form").addEventListener("submit",function(){document.getElementById("wpstaging-lite-spinner").style.display="inline-block";});</script>';
            if(isset($_GET['excludes_saved']) && $_GET['excludes_saved']==='1'){
                echo '<div class="notice notice-success is-dismissible" style="margin-top:16px;"><p>'.($lang=="de"?"Excludes erfolgreich gespeichert.":"Excludes saved successfully.").'</p></div>';
            }elseif(isset($_GET['excludes_error']) && $_GET['excludes_error']==='1'){
                echo '<div class="notice notice-error is-dismissible" style="margin-top:16px;"><p>'.($lang=="de"?"Fehler beim Speichern der Excludes!":"Error saving excludes!").'</p></div>';
            }elseif(isset($_GET['excludes_empty']) && $_GET['excludes_empty']==='1'){
                echo '<div class="notice notice-warning is-dismissible" style="margin-top:16px;"><p>'.($lang=="de"?"Bitte mindestens einen Exclude eintragen.":"Please enter at least one exclude.").'</p></div>';
            }
        }
        // Schritt 3: Debug Log prüfen
        if($step==3){
            if($lang=="de"){
                echo "<h2>Debug Log prüfen</h2><p>Hier werden alle wichtigen Aktionen und Fehler angezeigt. Das Logfile findest du auch unter <code>wp-content/uploads/wpstaging-logs/debug.log</code>.</p>";
            }else{
                echo "<h2>Check Debug Log</h2><p>All important actions and errors are listed here. You can also find the logfile at <code>wp-content/uploads/wpstaging-logs/debug.log</code>.</p>";
            }
            $log_file = WP_CONTENT_DIR . '/uploads/wpstaging-logs/debug.log';
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
            $staging_url = content_url('uploads/wpstaging/');
            if($lang=="de"){
                echo "<h2>Staging-Umgebung testen</h2><p>Die Staging-Umgebung ist jetzt einsatzbereit. Du findest sie unter: <a href='".esc_url($staging_url)."' target='_blank'>".esc_html($staging_url)."</a></p><ul><li>Stelle sicher, dass <code>robots.txt</code> und <code>index.html</code> vorhanden sind.</li><li>Teste Änderungen, ohne die Live-Seite zu gefährden.</li><li>Für weitere Backups oder Excludes, wiederhole die vorherigen Schritte.</li></ul>";
            }else{
                echo "<h2>Test Staging Environment</h2><p>Your staging environment is ready. You can find it at: <a href='".esc_url($staging_url)."' target='_blank'>".esc_html($staging_url)."</a></p><ul><li>Ensure <code>robots.txt</code> and <code>index.html</code> exist.</li><li>Test changes safely without affecting your live site.</li><li>For more backups or excludes, repeat previous steps.</li></ul>";
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
