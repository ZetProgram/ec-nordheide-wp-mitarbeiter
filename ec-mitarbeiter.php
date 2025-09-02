<?php
/*
Plugin Name: EC Mitarbeiter
Description: Dieses Plugin ermöglicht die Erfassung von Mitarbeitern (oder Personen) in Bezug zu einer Funktion und einer Kategorie. Die Darstellung erfolgt mit Hilfe des Gutenberg Blocks "EC Mitarbeiter Einzeldarstellung 2.0". Benötigt wird das WordPress Plugin Block Lab.
Version: 1.0.5
Author: Fabian Bross
Plugin URI: https://github.com/ZetProgram/ec-nordheide-wp-mitarbeiter
Update URI: https://github.com/ZetProgram/ec-nordheide-wp-mitarbeiter
Author URI: https://github.com/ZetProgram
Text Domain: mitarbeiter
Domain Path: /languages
Requires PHP: 8.1
Requires at least: 5.8
Tested up to: 6.8.2
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// === Konstanten & Tabellen ===
global $wpdb;
define('WP_MITARBEITER_TABLE',          $wpdb->prefix . 'mitarbeiter');
define('WP_MITARBEITER_FUNKTIONEN_TABLE',$wpdb->prefix . 'mitarbeiter_funktionen');
define('WP_MITARBEITER_KATEGORIEN_TABLE',$wpdb->prefix . 'mitarbeiter_kategorien');

define('TPL_VERZ', plugin_dir_path(__FILE__) . 'tpl');
define('SUPERADMIN', ''); // ggf. '&superadmin=1'

// === Internationalisierung ===
add_action('plugins_loaded', function () {
    $plugin_dir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('mitarbeiter', false, $plugin_dir . '/languages');
});

// Medien (Uploader) im Admin verfügbar machen
add_action('admin_enqueue_scripts', function () { wp_enqueue_media(); });

// === Files laden (erst Hilfsfunktionen, dann Menü etc.) ===
require_once plugin_dir_path(__FILE__) . 'sonstige_funktionen.php';
require_once plugin_dir_path(__FILE__) . 'mitarbeiter_config.php';
require_once plugin_dir_path(__FILE__) . 'mitarbeiter_categories.php';
require_once plugin_dir_path(__FILE__) . 'mitarbeiter_menu.php';

// === Tabellen anlegen/aktualisieren bei Aktivierung ===
register_activation_hook(__FILE__, 'mitarbeiter_check');

// Safety-Fallback (falls Plugin ohne Re-Aktivierung aktualisiert wurde)
add_action('plugins_loaded', function () {
    if ( function_exists('mitarbeiter_check') ) {
        mitarbeiter_check();
    }
});

// Admin-Assets (CSS/JS) nur einmal registrieren
add_action('admin_enqueue_scripts', function () {
    if ( function_exists('mitarbeiter_add_javascript') ) {
        mitarbeiter_add_javascript('');
    }
});

// Menü
add_action('admin_menu', 'mitarbeiter_menu');


// =====================
// Datenabfragen (SELECT)
// =====================

function hole_wp_mitarbeiter_funktionen($wp_mitarbeiter_funktionen_token = "", $sichtbar = "") {
    global $wpdb;

    $where = array();
    if ($sichtbar !== "") {
        $where[] = "`wp_mitarbeiter_funktionen_sichtbar`=1";
    }
    if ($wp_mitarbeiter_funktionen_token !== "") {
        $where[] = $wpdb->prepare("`wp_mitarbeiter_funktionen_token`=%s", $wp_mitarbeiter_funktionen_token);
    }

    $sql = "SELECT * FROM " . WP_MITARBEITER_FUNKTIONEN_TABLE;
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= ";";

    return $wpdb->get_results($sql);
}

function hole_wp_mitarbeiter_kategorien($wp_mitarbeiter_kategorien_token = "", $sichtbar = "") {
    global $wpdb;

    $where = array();
    if ($sichtbar !== "") {
        $where[] = "`wp_mitarbeiter_kategorien_sichtbar`=1";
    }
    if ($wp_mitarbeiter_kategorien_token !== "") {
        $where[] = $wpdb->prepare("`wp_mitarbeiter_kategorien_token`=%s", $wp_mitarbeiter_kategorien_token);
    }

    $sql = "SELECT * FROM " . WP_MITARBEITER_KATEGORIEN_TABLE;
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    $sql .= ";";

    return $wpdb->get_results($sql);
}

function hole_wp_mitarbeiter($wp_mitarbeiter_token = "") {
    global $wpdb;
    $wp_mitarbeiter_token = !empty($wp_mitarbeiter_token) ? sanitize_text_field($wp_mitarbeiter_token) : '';

    $join_f = "LEFT JOIN `" . WP_MITARBEITER_FUNKTIONEN_TABLE . "` ON `" . WP_MITARBEITER_FUNKTIONEN_TABLE . "`.`wp_mitarbeiter_funktionen_id`=`" . WP_MITARBEITER_TABLE . "`.`wp_mitarbeiter_funktionen_id`";
    $join_k = "LEFT JOIN `" . WP_MITARBEITER_KATEGORIEN_TABLE . "` ON `" . WP_MITARBEITER_KATEGORIEN_TABLE . "`.`wp_mitarbeiter_kategorien_id`=`" . WP_MITARBEITER_TABLE . "`.`wp_mitarbeiter_kategorien_id`";

    if ($wp_mitarbeiter_token === "") {
        $sql = "SELECT * FROM " . WP_MITARBEITER_TABLE . " $join_f $join_k ORDER BY wp_mitarbeiter_name ASC;";
    } else {
        $sql = $wpdb->prepare(
            "SELECT * FROM " . WP_MITARBEITER_TABLE . " $join_f $join_k WHERE wp_mitarbeiter_token=%s;",
            $wp_mitarbeiter_token
        );
    }

    return $wpdb->get_results($sql);
}


// =============================
// Admin-Liste & -Bearbeitungsform
// =============================

function liste_mitarbeiter() {
    $mitarbeiter_liste = hole_wp_mitarbeiter();

    $s_mitarbeiter_liste = '';
    if ( ! empty($mitarbeiter_liste) ) {
        // Listenkopf
        $a_PlatzhalterTpl = array();
        $s_mitarbeiter_liste .= ausgeben_tpl('mitarbeiter_liste_kopf', $a_PlatzhalterTpl);

        foreach ( $mitarbeiter_liste as $mitarbeiter ) {
            $mitarbeiter->wp_mitarbeiter_funktion  = $mitarbeiter->wp_mitarbeiter_funktionen_bezeichnung;
            $mitarbeiter->wp_mitarbeiter_kategorie = $mitarbeiter->wp_mitarbeiter_kategorien_bezeichnung;

            if ( (int) $mitarbeiter->wp_mitarbeiter_sichtbar === 0 ) {
                $icon_sichtbar = '<a href="' . admin_url('admin.php?page=mitarbeiter&amp;action=edit_sichtbarkeit&amp;wp_mitarbeiter_sichtbar=1&amp;wp_mitarbeiter_token=' . esc_attr($mitarbeiter->wp_mitarbeiter_token)) . '"><i class="fas fa-2x fa-eye-slash"></i></a>';
            } else {
                $icon_sichtbar = '<a href="' . admin_url('admin.php?page=mitarbeiter&amp;action=edit_sichtbarkeit&amp;wp_mitarbeiter_sichtbar=0&amp;wp_mitarbeiter_token=' . esc_attr($mitarbeiter->wp_mitarbeiter_token)) . '"><i class="fas fa-2x fa-eye"></i></a>';
            }

            $row_arr = object_to_array($mitarbeiter); // <- kommt aus sonstige_funktionen.php
            $a_PlatzhalterTpl = $row_arr;
            $a_PlatzhalterTpl['icon_sichtbar'] = $icon_sichtbar;
            $a_PlatzhalterTpl['admin_url'] = admin_url('admin.php?page=mitarbeiter' . SUPERADMIN);
            $a_PlatzhalterTpl['wp_nonce_url'] = wp_nonce_url(
                admin_url('admin.php?page=mitarbeiter&amp;action=delete_mitarbeiter&amp;wp_mitarbeiter_token=' . esc_attr($mitarbeiter->wp_mitarbeiter_token)),
                'mitarbeiter-delete_' . $mitarbeiter->wp_mitarbeiter_token
            );

            $s_mitarbeiter_liste .= ausgeben_tpl('mitarbeiter_liste_zeile', $a_PlatzhalterTpl);
        }

        $s_mitarbeiter_liste .= ausgeben_tpl('mitarbeiter_liste_fuss', $a_PlatzhalterTpl);
        echo $s_mitarbeiter_liste; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    } else {
        echo '<p>Keine Mitarbeiter in der Datenbank gefunden!</p>';
    }
}

function mitarbeiter_edit_form() {
    $a_PlatzhalterTpl = array();
    $a_PlatzhalterTpl = initialize_a_Platzhalter(WP_MITARBEITER_TABLE, $a_PlatzhalterTpl);

    $wp_mitarbeiter_token = isset($_REQUEST['wp_mitarbeiter_token'])
        ? sanitize_text_field(wp_unslash($_REQUEST['wp_mitarbeiter_token']))
        : '';

    $a_mitarbeiter = array();
    if ($wp_mitarbeiter_token !== '') {
        $mitarbeiter = hole_wp_mitarbeiter($wp_mitarbeiter_token);
        if (!empty($mitarbeiter) && isset($mitarbeiter[0])) {
            $a_mitarbeiter = object_to_array($mitarbeiter[0]);
        }
    }

    if (!empty($a_mitarbeiter)) {
        $a_PlatzhalterTpl = $a_mitarbeiter;
    }

    $akt_funk_id = isset($a_mitarbeiter['wp_mitarbeiter_funktionen_id']) ? $a_mitarbeiter['wp_mitarbeiter_funktionen_id'] : 0;
    $akt_kat_id  = isset($a_mitarbeiter['wp_mitarbeiter_kategorien_id']) ? $a_mitarbeiter['wp_mitarbeiter_kategorien_id'] : 0;

    $a_PlatzhalterTpl['optionen__wp_mitarbeiter_funktionen'] = liste_optionen_wp_mitarbeiter_funktionen($akt_funk_id);
    $a_PlatzhalterTpl['optionen__wp_mitarbeiter_kategorien'] = liste_optionen_wp_mitarbeiter_kategorien($akt_kat_id);
    $a_PlatzhalterTpl['admin_url'] = admin_url('admin.php?page=mitarbeiter' . SUPERADMIN);

    echo ausgeben_tpl('mitarbeiter_edit_form', $a_PlatzhalterTpl); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

function liste_optionen_wp_mitarbeiter_funktionen($aktuelle_wp_mitarbeiter_funktionen_id) {
    $o = hole_wp_mitarbeiter_funktionen("", 1);
    $a = object_to_array($o);

    $s_optionen = '<option>Bitte auswählen</option>';
    foreach ((array)$a as $row) {
        $sel = ((string)$row['wp_mitarbeiter_funktionen_id'] === (string)$aktuelle_wp_mitarbeiter_funktionen_id) ? 'selected' : '';
        $s_optionen .= '<option ' . $sel . ' value="' . esc_attr($row['wp_mitarbeiter_funktionen_id']) . '">' . esc_html($row['wp_mitarbeiter_funktionen_bezeichnung']) . '</option>';
    }
    return $s_optionen;
}

function liste_optionen_wp_mitarbeiter_kategorien($aktuelle_wp_mitarbeiter_kategorien_id) {
    $o = hole_wp_mitarbeiter_kategorien("", 1);
    $a = object_to_array($o);

    $s_optionen = '<option>Bitte auswählen</option>';
    foreach ((array)$a as $row) {
        $sel = ((string)$row['wp_mitarbeiter_kategorien_id'] === (string)$aktuelle_wp_mitarbeiter_kategorien_id) ? 'selected' : '';
        $s_optionen .= '<option ' . $sel . ' value="' . esc_attr($row['wp_mitarbeiter_kategorien_id']) . '">' . esc_html($row['wp_mitarbeiter_kategorien_bezeichnung']) . '</option>';
    }
    return $s_optionen;
}

// ==========================
// Admin-Controller/Aktionen
// ==========================
function mitarbeiter_edit() {
    global $wpdb;

    // Eingaben einsammeln/säubern
    $action = !empty($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
    $wp_mitarbeiter_token = !empty($_REQUEST['wp_mitarbeiter_token']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_token']) : '';

    $wp_mitarbeiter_name      = !empty($_REQUEST['wp_mitarbeiter_name']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_name']) : '';
    $wp_mitarbeiter_vorname   = !empty($_REQUEST['wp_mitarbeiter_vorname']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_vorname']) : '';
    $wp_mitarbeiter_email     = !empty($_REQUEST['wp_mitarbeiter_email']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_email']) : '';
    $wp_mitarbeiter_telefon   = !empty($_REQUEST['wp_mitarbeiter_telefon']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_telefon']) : '';
    $wp_mitarbeiter_funktion  = !empty($_REQUEST['wp_mitarbeiter_funktion']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_funktion']) : '';
    $wp_mitarbeiter_funktionen_id = !empty($_REQUEST['wp_mitarbeiter_funktionen_id']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_funktionen_id']) : '';
    $wp_mitarbeiter_kategorie = !empty($_REQUEST['wp_mitarbeiter_kategorie']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_kategorie']) : '';
    $wp_mitarbeiter_kategorien_id = !empty($_REQUEST['wp_mitarbeiter_kategorien_id']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_kategorien_id']) : '';
    $wp_mitarbeiter_geburtsdatum  = !empty($_REQUEST['wp_mitarbeiter_geburtsdatum']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_geburtsdatum']) : '';
    $wp_mitarbeiter_bildurl   = !empty($_REQUEST['wp_mitarbeiter_bildurl']) ? wp_filter_nohtml_kses($_REQUEST['wp_mitarbeiter_bildurl']) : '';
    $wp_mitarbeiter_sichtbar  = (isset($_REQUEST['wp_mitarbeiter_sichtbar']) && $_REQUEST['wp_mitarbeiter_sichtbar'] !== '') ? sanitize_text_field($_REQUEST['wp_mitarbeiter_sichtbar']) : '';

    // UPDATE
    if ($wp_mitarbeiter_token !== '' && $action === 'edit_mitarbeiter') {
        $sql = $wpdb->prepare(
            "UPDATE " . WP_MITARBEITER_TABLE . " SET 
                wp_mitarbeiter_name=%s,
                wp_mitarbeiter_vorname=%s,
                wp_mitarbeiter_email=%s,
                wp_mitarbeiter_telefon=%s,
                wp_mitarbeiter_funktion=%s,
                wp_mitarbeiter_funktionen_id=%s,
                wp_mitarbeiter_kategorie=%s,
                wp_mitarbeiter_kategorien_id=%s,
                wp_mitarbeiter_geburtsdatum=%s,
                wp_mitarbeiter_bildurl=%s
             WHERE wp_mitarbeiter_token=%s",
            $wp_mitarbeiter_name,
            $wp_mitarbeiter_vorname,
            $wp_mitarbeiter_email,
            $wp_mitarbeiter_telefon,
            $wp_mitarbeiter_funktion,
            $wp_mitarbeiter_funktionen_id,
            $wp_mitarbeiter_kategorie,
            $wp_mitarbeiter_kategorien_id,
            $wp_mitarbeiter_geburtsdatum,
            $wp_mitarbeiter_bildurl,
            $wp_mitarbeiter_token
        );
        $wpdb->query($sql);
    }
    // INSERT
    elseif ($wp_mitarbeiter_token === '' && $action === 'edit_mitarbeiter') {
        $wp_mitarbeiter_token = make_token();

        $sql = $wpdb->prepare(
            "INSERT INTO " . WP_MITARBEITER_TABLE . " 
                (wp_mitarbeiter_name, wp_mitarbeiter_vorname, wp_mitarbeiter_email, wp_mitarbeiter_telefon, 
                 wp_mitarbeiter_funktion, wp_mitarbeiter_funktionen_id, wp_mitarbeiter_kategorie, 
                 wp_mitarbeiter_kategorien_id, wp_mitarbeiter_geburtsdatum, wp_mitarbeiter_bildurl, wp_mitarbeiter_token)
             VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)",
            $wp_mitarbeiter_name,
            $wp_mitarbeiter_vorname,
            $wp_mitarbeiter_email,
            $wp_mitarbeiter_telefon,
            $wp_mitarbeiter_funktion,
            $wp_mitarbeiter_funktionen_id,
            $wp_mitarbeiter_kategorie,
            $wp_mitarbeiter_kategorien_id,
            $wp_mitarbeiter_geburtsdatum,
            $wp_mitarbeiter_bildurl,
            $wp_mitarbeiter_token
        );
        $wpdb->query($sql);
    }

    // DELETE
    if ($wp_mitarbeiter_token !== '' && $action === 'delete_mitarbeiter') {
        // Optional: Nonce prüfen (du generierst einen, prüfst ihn aber bisher nicht)
        // if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'mitarbeiter-delete_' . $wp_mitarbeiter_token ) ) { wp_die('Ungültige Aktion.'); }

        $sql = $wpdb->prepare("DELETE FROM " . WP_MITARBEITER_TABLE . " WHERE wp_mitarbeiter_token=%s", $wp_mitarbeiter_token);
        $wpdb->query($sql);
    }

    // Sichtbarkeit
    if ($wp_mitarbeiter_token !== '' && $action === 'edit_sichtbarkeit') {
        $sql = $wpdb->prepare(
            "UPDATE " . WP_MITARBEITER_TABLE . " SET wp_mitarbeiter_sichtbar=%s WHERE wp_mitarbeiter_token=%s",
            $wp_mitarbeiter_sichtbar,
            $wp_mitarbeiter_token
        );
        $wpdb->query($sql);
    }

    echo '<div class="wrap">';
    mitarbeiter_edit_form();
    echo '<br/><br/>';
    liste_mitarbeiter();
    echo '</div>';
}


// =====================
// Frontend (Platzhalter)
// =====================
function mitarbeiter($cat_list = '') { /* Platzhalter für späteren Output */ }


// =====================
// Utilities
// =====================
function make_token() {
    return md5( mt_rand(10000,99999) . '-' . time() );
}

function ausgeben_tpl($s_TplName, $a_PlatzhalterTpl = array()) {
    $tpl_pfad = TPL_VERZ . "/" . $s_TplName . '.html';
    if ( ! is_readable($tpl_pfad) ) {
        return '<!-- Template ' . esc_html($s_TplName) . ' nicht gefunden (' . esc_html($tpl_pfad) . ') -->';
    }

    $s_template = file_get_contents($tpl_pfad);

    // Platzhalter [[key]] ersetzen
    foreach ( (array) ca($a_PlatzhalterTpl) as $platzhalter => $wert ) {
        $s_template = str_replace('[[' . $platzhalter . ']]', (string) $wert, $s_template);
    }

    return $s_template;
}

function initialize_a_Platzhalter($s_table, $a_PlatzhalterTpl) {
    global $wpdb;

    $a_PlatzhalterTpl = is_array($a_PlatzhalterTpl) ? $a_PlatzhalterTpl : array();

    $s_query = "SHOW COLUMNS FROM " . $s_table;
    $a_keys_obj = $wpdb->get_results($s_query);
    $a_keys     = object_to_array($a_keys_obj);

    foreach ((array)$a_keys as $a_key) {
        if (isset($a_key['Field'])) {
            $a_PlatzhalterTpl[$a_key['Field']] = "";
        }
    }
    return $a_PlatzhalterTpl;
}


// =====================
// Block Lab Integration
// =====================
function blockLab_register_EC_Mitarbeiter_Einzeldarstellung_2_0_block_mitarbeiterplugin() {
    $a_mitarbeiter = hole_wp_mitarbeiter_blocklab_mitarbeiterplugin("", "wp_mitarbeiter_name");

    $options = array(array("label" => "Bitte auswählen", 'value' => 0));
    if (!empty($a_mitarbeiter)) {
        foreach ($a_mitarbeiter as $p) {
            $label = $p['wp_mitarbeiter_name'] . ', ' . $p['wp_mitarbeiter_vorname'] . ' (' . $p['wp_mitarbeiter_kategorie'] . ' - ' . $p['wp_mitarbeiter_funktion'] . ')';
            $options[] = array("label" => $label, 'value' => $p['wp_mitarbeiter_id']);
        }
    }

    if ( function_exists('block_lab_add_block') ) {
        block_lab_add_block(
            'ec-mitarbeiter-einzeldarstellung-20',
            array(
                'title'    => 'EC Mitarbeiter Einzeldarstellung 2.0',
                'category' => 'ec',
                'icon'     => 'waves',
                'excluded' => array(''),
                'keywords' => array('mitarbeiter', 'person', 'team'),
                'fields'   => array(
                    'eme_mitarbeiter' => array(
                        'label'   => 'Mitarbeiter',
                        'help'    => '',
                        'control' => 'select',
                        'width'   => '100%',
                        'options' => $options,
                    ),
                    'eme_beschreibung'  => array(
                        'label'   => 'Beschreibung',
                        'help'    => '',
                        'control' => 'textarea',
                        'width'   => '100%',
                    ),
                ),
            )
        );
    }
}
add_action( 'block_lab_add_blocks', 'blockLab_register_EC_Mitarbeiter_Einzeldarstellung_2_0_block_mitarbeiterplugin' );

function hole_wp_mitarbeiter_blocklab_mitarbeiterplugin($wp_mitarbeiter_id = "", $order_by = "") {
    global $wpdb;

    $a_mitarbeiter = array();

    $join_f = "LEFT JOIN `".WP_MITARBEITER_FUNKTIONEN_TABLE."` ON `".WP_MITARBEITER_FUNKTIONEN_TABLE."`.`wp_mitarbeiter_funktionen_id`=`".WP_MITARBEITER_TABLE."`.`wp_mitarbeiter_funktionen_id`";
    $join_k = "LEFT JOIN `".WP_MITARBEITER_KATEGORIEN_TABLE."` ON `".WP_MITARBEITER_KATEGORIEN_TABLE."`.`wp_mitarbeiter_kategorien_id`=`".WP_MITARBEITER_TABLE."`.`wp_mitarbeiter_kategorien_id`";

    if ($wp_mitarbeiter_id !== "") {
        $sql = $wpdb->prepare(
            "SELECT * FROM `".WP_MITARBEITER_TABLE."` $join_f $join_k 
             WHERE `wp_mitarbeiter_sichtbar`='1' AND `wp_mitarbeiter_id`=%s;",
            $wp_mitarbeiter_id
        );
    } else {
        $sql = "SELECT * FROM `".WP_MITARBEITER_TABLE."` $join_f $join_k 
                WHERE `wp_mitarbeiter_sichtbar`='1' 
                  AND `".WP_MITARBEITER_KATEGORIEN_TABLE."`.`wp_mitarbeiter_kategorien_sichtbar`=1";
        if ($order_by !== "") {
            // Whitelist: nur bestimmte Spalten erlauben
            $allowed = array(
                'wp_mitarbeiter_name',
                'wp_mitarbeiter_vorname',
                'wp_mitarbeiter_sortierung',
                'wp_mitarbeiter_kategorien_bezeichnung',
            );
            if (in_array($order_by, $allowed, true)) {
                $sql .= " ORDER BY `" . esc_sql($order_by) . "`";
            } else {
                $sql .= " ORDER BY `wp_mitarbeiter_kategorien_bezeichnung`, `wp_mitarbeiter_sortierung`";
            }
        } else {
            $sql .= " ORDER BY `wp_mitarbeiter_kategorien_bezeichnung`, `wp_mitarbeiter_sortierung`";
        }
        $sql .= ";";
    }

    $rows_obj = $wpdb->get_results($sql);
    $rows     = object_to_array($rows_obj);

    foreach ((array)$rows as $row) {
        $id = $row['wp_mitarbeiter_id'];
        $a_mitarbeiter[$id] = $row;
        $a_mitarbeiter[$id]['wp_mitarbeiter_funktion']  = $row['wp_mitarbeiter_funktionen_bezeichnung'];
        $a_mitarbeiter[$id]['wp_mitarbeiter_kategorie'] = $row['wp_mitarbeiter_kategorien_bezeichnung'];
    }

    return $a_mitarbeiter;
}

// === Self-Update (wie gehabt) ===
add_action('plugins_loaded', function () {
    $puc = __DIR__ . '/lib/plugin-update-checker/plugin-update-checker.php';
    if (!file_exists($puc)) return;
    require_once $puc;

    $factory = class_exists('\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory')
        ? '\\YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory'
        : (class_exists('Puc_v4_Factory') ? 'Puc_v4_Factory' : null);
    if (!$factory) return;

    $uc = $factory::buildUpdateChecker(
        'https://github.com/ZetProgram/ec-nordheide-wp-mitarbeiter/',
        __FILE__,
        'ec-mitarbeiter'
    );

    $uc->setBranch('production');
    if ($api = $uc->getVcsApi()) {
        $api->enableReleaseAssets();
    }
});
