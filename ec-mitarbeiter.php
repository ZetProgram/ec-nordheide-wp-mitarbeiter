<?php
/*
Plugin Name: EC Mitarbeiter
Description: Dieses Plugin ermöglicht die Erfassung von Mitarbeitern (oder Personen) in Bezug zu einer Funktion und einer Kategorie. Die Darstellung erfolgt mit Hilfe des Gutenberg Blocks "EC Mitarbeiter Einzeldarstellung 2.0". Benötigt wird das WordPress Plugin Block Lab.
Version: 1.0.3
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
include('mitarbeiter_menu.php');
include('sonstige_funktionen.php');
include('mitarbeiter_config.php');
include('mitarbeiter_categories.php');

define("TPL_VERZ",plugin_dir_path(__FILE__).'/tpl');
//define("SUPERADMIN","&superadmin=1");
define("SUPERADMIN","");


// Direct access shouldn't be allowed
if ( ! defined( 'ABSPATH' ) ) exit;

// Enable internationalisation
function mitarbeiter_load_text_domain() {
    $plugin_dir = dirname(plugin_basename(__FILE__));
    load_plugin_textdomain('mitarbeiter', false, $plugin_dir . '/languages');
}
add_action('plugins_loaded', 'mitarbeiter_load_text_domain');


function load_media_files() {
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'load_media_files' );

// Define the constants & tables used in Mitarbeiter
global $wpdb;

define('WP_MITARBEITER_TABLE', $wpdb->prefix . 'mitarbeiter');
define('WP_MITARBEITER_FUNKTIONEN_TABLE', $wpdb->prefix . 'mitarbeiter_funktionen');
define('WP_MITARBEITER_KATEGORIEN_TABLE', $wpdb->prefix . 'mitarbeiter_kategorien');

// Check ensure mitarbeiter is installed and install it if not - required for
// the successful operation of most functions called from this point on
mitarbeiter_check();

// Create a master category for Mitarbeiter and its sub-pages

add_action('admin_enqueue_scripts', 'mitarbeiter_add_javascript');
add_action('admin_menu', 'mitarbeiter_menu');
/*

*/
function hole_wp_mitarbeiter_funktionen($wp_mitarbeiter_funktionen_token = "",$sichtbar=""){
	global $wpdb;
	
	$s_where_sichtbar = '';
	
	if($sichtbar != ""){
		$s_where_sichtbar = "WHERE `wp_mitarbeiter_funktionen_sichtbar`=1";
	}
	
	$s_where2 = '';
	
	if($wp_mitarbeiter_funktionen_token != ""){
		$s_and = "";
		if($s_where_sichtbar != ""){
			$s_and = " AND ";
		} else {
			$s_and = " WHERE ";
		}
		$s_where2 = "$s_and `wp_mitarbeiter_funktionen_token`='$wp_mitarbeiter_funktionen_token'";
	}

	$s_query = "SELECT * FROM " . WP_MITARBEITER_FUNKTIONEN_TABLE . " $s_where_sichtbar $s_where2;";

	$a_mitarbeiter_funktionen = $wpdb->get_results($s_query);
	
	return $a_mitarbeiter_funktionen;
}

function hole_wp_mitarbeiter_kategorien($wp_mitarbeiter_kategorien_token = "",$sichtbar=""){
	global $wpdb;
	
	$s_where_sichtbar = '';
	if($sichtbar != ""){
		$s_where_sichtbar = "WHERE `wp_mitarbeiter_kategorien_sichtbar`=1";
	}
	
	$s_where2 = '';
	if($wp_mitarbeiter_kategorien_token != ""){
		$s_and = "";
		if($s_where_sichtbar != ""){
			$s_and = " AND ";
		} else {
			$s_and = " WHERE ";
		}
		$s_where2 = "$s_and `wp_mitarbeiter_kategorien_token`='$wp_mitarbeiter_kategorien_token'";
	}

	$s_query = "SELECT * FROM " . WP_MITARBEITER_KATEGORIEN_TABLE . " $s_where_sichtbar $s_where2;";

	$a_mitarbeiter_kategorien = $wpdb->get_results($s_query);
	
	return $a_mitarbeiter_kategorien;
}

function hole_wp_mitarbeiter($wp_mitarbeiter_token = ""){

	global $wpdb;
	$wp_mitarbeiter_token = !empty($wp_mitarbeiter_token) ? sanitize_text_field($wp_mitarbeiter_token) : '';
	
	$s_left_join = "LEFT JOIN `" . WP_MITARBEITER_FUNKTIONEN_TABLE . "` ON `" . WP_MITARBEITER_FUNKTIONEN_TABLE . "`.`wp_mitarbeiter_funktionen_id`=`" . WP_MITARBEITER_TABLE . "`.`wp_mitarbeiter_funktionen_id`";
	$s_left_join2 = "LEFT JOIN `" . WP_MITARBEITER_KATEGORIEN_TABLE . "` ON `" . WP_MITARBEITER_KATEGORIEN_TABLE . "`.`wp_mitarbeiter_kategorien_id`=`" . WP_MITARBEITER_TABLE . "`.`wp_mitarbeiter_kategorien_id`";
		
		
	if($wp_mitarbeiter_token == ""){
		$s_query = "SELECT * FROM " . WP_MITARBEITER_TABLE . " $s_left_join $s_left_join2 ORDER BY wp_mitarbeiter_name ASC";
	} else {
		
		$s_query = "SELECT * FROM " . WP_MITARBEITER_TABLE . " $s_left_join $s_left_join2 WHERE  wp_mitarbeiter_token = '$wp_mitarbeiter_token'";
	}
	//echo $s_query;
	$a_mitarbeiter = $wpdb->get_results($s_query);
	//$a_mitarbeiter = object_to_array($o_mitarbeiter);
	
	return $a_mitarbeiter;
}
// Used on the manage events admin page to display a list of events
function liste_mitarbeiter(){

	$a_mitarbeiter = hole_wp_mitarbeiter();
	
	
//pf($a_mitarbeiter);
	$s_mitarbeiter_liste = '';
	if ( !empty($a_mitarbeiter) )
	{
		//Listenkopf
		$s_mitarbeiter_liste .= ausgeben_tpl('mitarbeiter_liste_kopf',$a_PlatzhalterTpl);

		$class = '';
		
		foreach ( $a_mitarbeiter as $mitarbeiter )
		{
			$mitarbeiter->wp_mitarbeiter_funktion = $mitarbeiter->wp_mitarbeiter_funktionen_bezeichnung;
			$mitarbeiter->wp_mitarbeiter_kategorie = $mitarbeiter->wp_mitarbeiter_kategorien_bezeichnung;
			
			if($mitarbeiter->wp_mitarbeiter_sichtbar == 0){
				$icon_sichtbar = '<a href="'.admin_url('admin.php?page=mitarbeiter&amp;action=edit_sichtbarkeit&amp;wp_mitarbeiter_sichtbar=1&amp;wp_mitarbeiter_token='.stripslashes($mitarbeiter->wp_mitarbeiter_token)).'"><i class="fas fa-2x fa-eye-slash"></i></a>';
				
			} else {
				$icon_sichtbar = '<a href="'.admin_url('admin.php?page=mitarbeiter&amp;action=edit_sichtbarkeit&amp;wp_mitarbeiter_sichtbar=0&amp;wp_mitarbeiter_token='.stripslashes($mitarbeiter->wp_mitarbeiter_token)).'"><i class="fas fa-2x fa-eye"></i></a>';
			}
	
			$class = ($class == 'alternate') ? '' : 'alternate';
			
			//Listenzeilen
			$a_mitarbeiter = object_to_array($mitarbeiter);
			$a_PlatzhalterTpl = $a_mitarbeiter;
			$a_PlatzhalterTpl['icon_sichtbar'] = $icon_sichtbar;
			$a_PlatzhalterTpl['admin_url'] = admin_url('admin.php?page=mitarbeiter&amp;action=edit&amp;wp_mitarbeiter_token='.stripslashes($mitarbeiter->wp_mitarbeiter_token));
			$a_PlatzhalterTpl['wp_nonce_url'] = wp_nonce_url(admin_url('admin.php?page=mitarbeiter&amp;action=delete_mitarbeiter&amp;wp_mitarbeiter_token='.stripslashes($mitarbeiter->wp_mitarbeiter_token)),'mitarbeiter-delete_'.stripslashes($mitarbeiter->wp_mitarbeiter_token));
			$s_mitarbeiter_liste .= ausgeben_tpl('mitarbeiter_liste_zeile',$a_PlatzhalterTpl);

		}
		$s_mitarbeiter_liste .= ausgeben_tpl('mitarbeiter_liste_fuss',$a_PlatzhalterTpl);
		echo $s_mitarbeiter_liste;
	}
	else
	{
		?>
		<p>Keine Mitarbeiter in der Datenbank gefunden!</p>
		<?php	
	}
}


// The event edit form for the manage events admin page
function mitarbeiter_edit_form()
{	global $wpdb;
	$data = false;
	
	$a_PlatzhalterTpl = initialize_a_Platzhalter(WP_MITARBEITER_TABLE,$a_PlatzhalterTpl);
	
	$wp_mitarbeiter_token = $_REQUEST['wp_mitarbeiter_token'];
	
	if($wp_mitarbeiter_token != ""){
		$a_mitarbeiter = hole_wp_mitarbeiter($wp_mitarbeiter_token);
	}
  
	//pf($a_PlatzhalterTpl);
	$a_mitarbeiter = object_to_array($a_mitarbeiter[0]);
	if(@count($a_mitarbeiter) > 0){
		$a_PlatzhalterTpl = $a_mitarbeiter;
	}
	//pf($a_mitarbeiter);
	$a_PlatzhalterTpl['optionen__wp_mitarbeiter_funktionen'] = liste_optionen_wp_mitarbeiter_funktionen($a_mitarbeiter['wp_mitarbeiter_funktionen_id']);
	$a_PlatzhalterTpl['optionen__wp_mitarbeiter_kategorien'] = liste_optionen_wp_mitarbeiter_kategorien($a_mitarbeiter['wp_mitarbeiter_kategorien_id']);
	
	$a_PlatzhalterTpl['admin_url'] = admin_url('admin.php?page=mitarbeiter'.SUPERADMIN);
	
	$s_editform = ausgeben_tpl('mitarbeiter_edit_form',$a_PlatzhalterTpl);
	echo $s_editform;
}

function liste_optionen_wp_mitarbeiter_funktionen($aktuelle_wp_mitarbeiter_funktionen_id){
	
	$o_wp_mitarbeiter_funktionen = hole_wp_mitarbeiter_funktionen("",1);
	
	$a_wp_mitarbeiter_funktionen = object_to_array($o_wp_mitarbeiter_funktionen);
	//pf($a_wp_mitarbeiter_funktionen);
	$s_optionen .= '<option>Bitte auswählen</option>';
	foreach($a_wp_mitarbeiter_funktionen as $a_wp_mitarbeiter_funktion){
		$sel = '';
		if($a_wp_mitarbeiter_funktion['wp_mitarbeiter_funktionen_id'] == $aktuelle_wp_mitarbeiter_funktionen_id){
			$sel = 'selected';
		}
		$s_optionen .= '<option '.$sel.' value="'.$a_wp_mitarbeiter_funktion['wp_mitarbeiter_funktionen_id'].'">'.$a_wp_mitarbeiter_funktion['wp_mitarbeiter_funktionen_bezeichnung'].'</option>';
	}
	
	return $s_optionen;
}

function liste_optionen_wp_mitarbeiter_kategorien($aktuelle_wp_mitarbeiter_kategorien_id){
	
	$o_wp_mitarbeiter_kategorien = hole_wp_mitarbeiter_kategorien("",1);
	
	$a_wp_mitarbeiter_kategorien = object_to_array($o_wp_mitarbeiter_kategorien);
	//pf($a_wp_mitarbeiter_funktionen);
	$s_optionen .= '<option>Bitte auswählen</option>';
	foreach($a_wp_mitarbeiter_kategorien as $a_wp_mitarbeiter_kategorie){
		$sel = '';
		if($a_wp_mitarbeiter_kategorie['wp_mitarbeiter_kategorien_id'] == $aktuelle_wp_mitarbeiter_kategorien_id){
			$sel = 'selected';
		}
		$s_optionen .= '<option '.$sel.' value="'.$a_wp_mitarbeiter_kategorie['wp_mitarbeiter_kategorien_id'].'">'.$a_wp_mitarbeiter_kategorie['wp_mitarbeiter_kategorien_bezeichnung'].'</option>';
	}
	return $s_optionen;
}
// The actual function called to render the manage events page and 
// to deal with posts
function mitarbeiter_edit()
{
global $current_user, $wpdb, $users_entries;

// First some quick cleaning up
$edit = $create = $save = $delete = false;

// Make sure we are collecting the variables we need to select years and months
$action = !empty($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
$wp_mitarbeiter_token = !empty($_REQUEST['wp_mitarbeiter_token']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_token']) : '';

$wp_mitarbeiter_name = !empty($_REQUEST['wp_mitarbeiter_name']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_name']) : '';
$wp_mitarbeiter_vorname = !empty($_REQUEST['wp_mitarbeiter_vorname']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_vorname']) : '';//wp_kses_post($_REQUEST['event_desc']) : '';
$wp_mitarbeiter_email = !empty($_REQUEST['wp_mitarbeiter_email']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_email']) : '';
$wp_mitarbeiter_telefon = !empty($_REQUEST['wp_mitarbeiter_telefon']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_telefon']) : '';
$wp_mitarbeiter_funktion = !empty($_REQUEST['wp_mitarbeiter_funktion']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_funktion']) : '';
$wp_mitarbeiter_funktionen_id = !empty($_REQUEST['wp_mitarbeiter_funktionen_id']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_funktionen_id']) : '';
$wp_mitarbeiter_kategorie = !empty($_REQUEST['wp_mitarbeiter_kategorie']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_kategorie']) : '';
$wp_mitarbeiter_kategorien_id = !empty($_REQUEST['wp_mitarbeiter_kategorien_id']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_kategorien_id']) : '';
$wp_mitarbeiter_geburtsdatum = !empty($_REQUEST['wp_mitarbeiter_geburtsdatum']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_geburtsdatum']) : '';
$wp_mitarbeiter_bildurl = !empty($_REQUEST['wp_mitarbeiter_bildurl']) ? wp_filter_nohtml_kses($_REQUEST['wp_mitarbeiter_bildurl']) : '';
$wp_mitarbeiter_sichtbar = !empty($_REQUEST['wp_mitarbeiter_sichtbar']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_sichtbar']) : '';

//UPDATE oder INSERT
if($wp_mitarbeiter_token != "" && $action == "edit_mitarbeiter"){
	
	//edit
	$sql = $wpdb->prepare("UPDATE " . WP_MITARBEITER_TABLE . " SET 
	wp_mitarbeiter_name='%s', 
	wp_mitarbeiter_vorname='%s', 
	wp_mitarbeiter_email='%s', 
	wp_mitarbeiter_telefon='%s', 
	wp_mitarbeiter_funktion='%s', 
	wp_mitarbeiter_funktionen_id='%s', 
	wp_mitarbeiter_kategorie='%s', 
	wp_mitarbeiter_kategorien_id='%s', 
	wp_mitarbeiter_geburtsdatum='%s', 
	wp_mitarbeiter_bildurl=%s
	
	WHERE 
	wp_mitarbeiter_token='%s'",
	
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
	$wp_mitarbeiter_token);		     
	
	//echo $sql;
	$wpdb->get_results($sql);
} elseif($wp_mitarbeiter_token == "" && $action == "edit_mitarbeiter") {
	//INSERT
	
	$wp_mitarbeiter_token = make_token();
	
	$sql = $wpdb->prepare("
	INSERT INTO " . WP_MITARBEITER_TABLE . " SET 
	wp_mitarbeiter_name='%s', 
	wp_mitarbeiter_vorname='%s', 
	wp_mitarbeiter_email='%s', 
	wp_mitarbeiter_telefon='%s', 
	wp_mitarbeiter_funktion='%s',  
	wp_mitarbeiter_funktionen_id='%s', 
	wp_mitarbeiter_kategorie='%s', 
	wp_mitarbeiter_kategorien_id='%s', 
	wp_mitarbeiter_geburtsdatum='%s', 
	wp_mitarbeiter_bildurl=%s,
	wp_mitarbeiter_token=%s
	"
	
	,
	
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
	$wp_mitarbeiter_token);
	
    $wpdb->get_results($sql);
	
	//echo $sql;
}

if($wp_mitarbeiter_token != "" && $action == "delete_mitarbeiter"){
	//DELETE
	$sql = $wpdb->prepare("DELETE FROM " . WP_MITARBEITER_TABLE . " WHERE wp_mitarbeiter_token='%s'",$wp_mitarbeiter_token);
	//echo $sql;
	$wpdb->get_results($sql);
}

if($wp_mitarbeiter_token != "" && $action == "edit_sichtbarkeit"){
	//edit
	$sql = $wpdb->prepare("UPDATE " . WP_MITARBEITER_TABLE . " SET 
	
	wp_mitarbeiter_sichtbar='%s'
	
	WHERE 
	wp_mitarbeiter_token='%s'",
	
	$wp_mitarbeiter_sichtbar,
	
	$wp_mitarbeiter_token);		     
	
	//echo $sql;
	$wpdb->get_results($sql);
}

?>

<div class="wrap">

<?php mitarbeiter_edit_form(); ?>
<br/><br/>
<?php liste_mitarbeiter(); ?>
</div>

<?php
 
}

/*?*/
function mitarbeiter($cat_list = '')
{
  global $wpdb;

    
}


function make_token(){
	$token = md5(rand(10000,99999).'-'.time());
	return $token;
}

function ausgeben_tpl($s_TplName,$a_PlatzhalterTpl=array()){
	
		$tpl_pfad = TPL_VERZ."/".$s_TplName.'.html';

		$s_template = file_get_contents($tpl_pfad);

		//Platzhalter austauschen
		foreach(ca($a_PlatzhalterTpl) as $platzhalter => $wert){
			$s_template = str_replace("[[".$platzhalter."]]",$wert,$s_template);
		}
		
		return $s_template;
}

function initialize_a_Platzhalter($s_table, $a_PlatzhalterTpl){
	
	global $wpdb;
	
	$s_query = "SHOW COLUMNS FROM " . $s_table;
	
	$a_keys_obj = $wpdb->get_results($s_query);
	
	$a_keys = object_to_array($a_keys_obj);
	
	foreach($a_keys as $z => $a_key){
		//echo $a_key['Field'];
		$a_PlatzhalterTpl[$a_key['Field']] = "";
	}
	
	return $a_PlatzhalterTpl;
}

function blockLab_register_EC_Mitarbeiter_Einzeldarstellung_2_0_block_mitarbeiterplugin() {
	$a_mitarbeiter = hole_wp_mitarbeiter_blocklab_mitarbeiterplugin("","wp_mitarbeiter_name");
	
	//pf($a_mitarbeiter);

	if( ! empty( $a_mitarbeiter ) ){
		$options[0] = array("label" => "Bitte auswählen", 'value' => 0); 
		foreach ( $a_mitarbeiter as $p ){	
			$options[] = array("label" => $p['wp_mitarbeiter_name'].', '.$p['wp_mitarbeiter_vorname'].' ('.$p['wp_mitarbeiter_kategorie'].' - '.$p['wp_mitarbeiter_funktion'].')', 'value' => $p['wp_mitarbeiter_id']); 
		}
	}

    block_lab_add_block(
        'ec-mitarbeiter-einzeldarstellung-20',
        array(
            'title'    => 'EC Mitarbeiter Einzeldarstellung 2.0',
            'category' => 'ec',
            'icon'     => 'waves',
            'excluded' => array( '' ),
            'keywords' => array( 'sad', 'glad', 'bad' ),
            'fields'   => array(
                'eme_mitarbeiter' => array(
                    'label'   => 'Mitarbeiter',
                    'help'   => '',
                    'control' => 'select',
                    'width'   => '100%',
                    'options' => 
					$options
                    ,
                ),
                'eme_beschreibung'  => array(
                    'label'   => 'Beschreibung',
                    'help'   => '',
                    'control' => 'textarea',
                    'width'   => '100%',
                ),	
            ),
        )
    );
}

add_action( 'block_lab_add_blocks', 'blockLab_register_EC_Mitarbeiter_Einzeldarstellung_2_0_block_mitarbeiterplugin' );

function hole_wp_mitarbeiter_blocklab_mitarbeiterplugin($wp_mitarbeiter_id = "",$order_by = ""){
		
		// Define the constants & tables used in Mitarbeiter
		global $wpdb;

		$a_mitarbeiter = array();
		
		$s_left_join = "LEFT JOIN `".WP_MITARBEITER_FUNKTIONEN_TABLE."` ON `".WP_MITARBEITER_FUNKTIONEN_TABLE."`.`wp_mitarbeiter_funktionen_id`=`".WP_MITARBEITER_TABLE."`.`wp_mitarbeiter_funktionen_id`";
		$s_left_join2 = "LEFT JOIN `".WP_MITARBEITER_KATEGORIEN_TABLE."` ON `".WP_MITARBEITER_KATEGORIEN_TABLE."`.`wp_mitarbeiter_kategorien_id`=`".WP_MITARBEITER_TABLE."`.`wp_mitarbeiter_kategorien_id`";
		
		if($wp_mitarbeiter_id != ""){
			$s_query = "SELECT * FROM `".WP_MITARBEITER_TABLE."` $s_left_join $s_left_join2 WHERE `wp_mitarbeiter_sichtbar`='1' AND `wp_mitarbeiter_id`='$wp_mitarbeiter_id';";
		} else {
			//alle
			$s_query = "SELECT * FROM `".WP_MITARBEITER_TABLE."` $s_left_join  $s_left_join2 
			WHERE `wp_mitarbeiter_sichtbar`='1' 
			AND `".WP_MITARBEITER_KATEGORIEN_TABLE."`.`wp_mitarbeiter_kategorien_sichtbar`=1
			ORDER BY `wp_mitarbeiter_kategorien_bezeichnung`,`wp_mitarbeiter_sortierung`;";
		}
		
		if($order_by != ""){
			//alle
			$s_query = "SELECT * FROM `".WP_MITARBEITER_TABLE."` $s_left_join $s_left_join2 ORDER BY `$order_by`;";
		}
		
		$a_reslts = $wpdb->get_results($s_query);
	
		$a_alle_mitarbeiter = object_to_array($a_reslts);
		
		foreach($a_alle_mitarbeiter as $z => $row){
						$a_mitarbeiter[$row['wp_mitarbeiter_id']] = $row;
						$a_mitarbeiter[$row['wp_mitarbeiter_id']]['wp_mitarbeiter_funktion'] = $row['wp_mitarbeiter_funktionen_bezeichnung'];
						$a_mitarbeiter[$row['wp_mitarbeiter_id']]['wp_mitarbeiter_kategorie'] = $row['wp_mitarbeiter_kategorien_bezeichnung'];
		}
		
		return $a_mitarbeiter;
}

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
?>
