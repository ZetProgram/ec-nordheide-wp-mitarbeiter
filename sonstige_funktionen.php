<?php 
// Function to provide time with WordPress offset, localy replaces time()
function mitarbeiter_ctwo()
{
  return (time()+(3600*(get_option('gmt_offset'))));
}
// Function to display a warning on the admin panel if the mitarbeiter plugin is mising setup

function mitarbeiter_setup_incomplete_warning() {
    global $wpdb;
	/*
    $incomplete_check = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='show_attribution_link'");
    if (empty($incomplete_check) && !(isset($_GET['page']) && $_GET['page'] == 'mitarbeiter-config')) {
        $args = array( 'page' => 'mitarbeiter-config');
        $url = add_query_arg( $args, admin_url( 'admin.php' ) );
        ?>
        <div class="update-nag"><p><strong><?php _e('Warning','mitarbeiter'); ?>:</strong> <?php _e("Mitarbeiter setup incomplete. Go to the <a href=\"$url\">mitarbeiter plugin settings</a> to complete setup.",'mitarbeiter'); ?></p></div>
        <?php
    }
	*/
}
add_action( 'admin_notices', 'mitarbeiter_setup_incomplete_warning' );

// Add the function that puts style information in the header
add_action('wp_head', 'mitarbeiter_wp_head');

// Function to add the mitarbeiter style into the header
function mitarbeiter_wp_head()
{
  global $wpdb;
/*
  $style = $wpdb->get_var("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='mitarbeiter_style'");
  if ($style != '')
    {
	  echo '<style type="text/css">
';
          echo stripslashes($style).'
';
	  echo '</style>
';
    }
	*/
}

add_filter( 'query_vars', 'mitarbeiter_feed_query_vars' );
function mitarbeiter_feed_query_vars( $query_vars )
{
    $query_vars[] = 'mitarbeiter_feed';
    return $query_vars;
}

add_action( 'parse_request', 'mitarbeiter_feed_parse_request' );
function mitarbeiter_feed_parse_request( &$wp )
{
    if ( array_key_exists( 'mitarbeiter_feed', $wp->query_vars ) ) {
        global $wpdb;
        include 'mitarbeiter-feed.php';
        exit();
    }
    return;
}

// Function to deal with events posted by a user when that user is deleted
function mitarbeiter_deal_with_deleted_user($id)
{
  global $wpdb;

  // Do the query
  $wpdb->get_results($wpdb->prepare("UPDATE ".WP_MITARBEITER_TABLE." SET event_author=".$wpdb->get_var("SELECT MIN(ID) FROM ".$wpdb->prefix."users",0,0)." WHERE event_author=%d",$id));
}

// Function to add the javascript to the admin header
function mitarbeiter_add_javascript()
{
    wp_enqueue_script( 'mitarbeiter_custom_wp_admin_js', plugins_url('javascript.js', __FILE__) );
    wp_enqueue_style( 'mitarbeiter_custom_wp_admin_css', plugins_url('mitarbeiter-admin.css', __FILE__) );
}


// Function to check what version of Mitarbeiter is installed and install if needed
function mitarbeiter_check()
{
	
  // Checks to make sure Mitarbeiter is installed, if not it adds the default
  // database tables and populates them with test data. If it is, then the 
  // version is checked through various means and if it is not up to date 
  // then it is upgraded.

  // Lets see if this is first run and create us a table if it is!
  global $wpdb;

	
      // Assume this is not a new install until we prove otherwise
      $new_install = false;

      $wp_mitarbeiter_exists = false;

      // Determine the mitarbeiter version
      $tables = $wpdb->get_results("show tables");
      foreach ($tables as $table) {
          foreach ($table as $value) {
//			  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $value;
              if ($value == WP_MITARBEITER_TABLE) {
                  $wp_mitarbeiter_exists = true;
              }
      }
	  }

      if ($wp_mitarbeiter_exists == false) {
          $new_install = true;
      }

//echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . WP_MITARBEITER_TABLE;
      // Now we've determined what the current install is or isn't
      // we perform operations according to the findings
      if ($new_install == true) {

$sql = "CREATE TABLE `" . WP_MITARBEITER_TABLE . "` (
  `wp_mitarbeiter_id` int(11) NOT NULL,
  `wp_mitarbeiter_token` varchar(255) NOT NULL,
  `wp_mitarbeiter_bildurl` varchar(255) NOT NULL,
  `wp_mitarbeiter_vorname` varchar(255) NOT NULL,
  `wp_mitarbeiter_name` varchar(255) NOT NULL,
  `wp_mitarbeiter_email` varchar(255) NOT NULL,
  `wp_mitarbeiter_telefon` varchar(50) NOT NULL,
  `wp_mitarbeiter_funktion` varchar(255) NOT NULL,
  `wp_mitarbeiter_funktionen_id` int(11) NOT NULL,
  `wp_mitarbeiter_kategorie` varchar(255) NOT NULL,
  `wp_mitarbeiter_kategorien_id` int(11) NOT NULL,
  `wp_mitarbeiter_geburtsdatum` date NOT NULL,
  `wp_mitarbeiter_sortierung` int(11) NOT NULL,
  `wp_mitarbeiter_sichtbar` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$wpdb->get_results($sql);

$sql = "CREATE TABLE `" . WP_MITARBEITER_FUNKTIONEN_TABLE . "` (
  `wp_mitarbeiter_funktionen_id` int(11) NOT NULL,
  `wp_mitarbeiter_funktionen_token` varchar(255) NOT NULL,
  `wp_mitarbeiter_funktionen_bezeichnung` varchar(255) NOT NULL,
  `wp_mitarbeiter_funktionen_sichtbar` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
$wpdb->get_results($sql);
          
$sql = "CREATE TABLE `" . WP_MITARBEITER_KATEGORIEN_TABLE . "` (
  `wp_mitarbeiter_kategorien_id` int(11) NOT NULL,
  `wp_mitarbeiter_kategorien_token` varchar(255) NOT NULL,
  `wp_mitarbeiter_kategorien_bezeichnung` varchar(255) NOT NULL,
  `wp_mitarbeiter_kategorien_sichtbar` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";          
$wpdb->get_results($sql);

$sql = "ALTER TABLE `" . WP_MITARBEITER_TABLE . "`
  ADD PRIMARY KEY (`wp_mitarbeiter_id`);";
$wpdb->get_results($sql);

$sql = "ALTER TABLE `" . WP_MITARBEITER_FUNKTIONEN_TABLE . "`
  ADD PRIMARY KEY (`wp_mitarbeiter_funktionen_id`);";
$wpdb->get_results($sql);

$sql = "ALTER TABLE `" . WP_MITARBEITER_KATEGORIEN_TABLE . "`
  ADD PRIMARY KEY (`wp_mitarbeiter_kategorien_id`);";
$wpdb->get_results($sql);

$sql = "ALTER TABLE `" . WP_MITARBEITER_TABLE . "`
  MODIFY `wp_mitarbeiter_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";
$wpdb->get_results($sql);

$sql = "ALTER TABLE `" . WP_MITARBEITER_FUNKTIONEN_TABLE . "`
  MODIFY `wp_mitarbeiter_funktionen_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";
$wpdb->get_results($sql);

$sql = "ALTER TABLE `" . WP_MITARBEITER_KATEGORIEN_TABLE . "`
  MODIFY `wp_mitarbeiter_kategorien_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;";
$wpdb->get_results($sql);
	  }
  }



function ca($a = array()){
                if(!is_array($a)){
                               $a = array();
                }
                return $a;
}
?>