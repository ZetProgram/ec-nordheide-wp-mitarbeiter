<?php
// Function to deal with adding the mitarbeiter menus
function mitarbeiter_menu() 
{
  global $wpdb;

  // Set admin as the only one who can use Mitarbeiter for security
  $allowed_group = 'manage_options';

  // Use the database to *potentially* override the above if allowed
  /*
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='can_manage_events'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
	{
	  $allowed_group = $config->config_value;
	}
    }
*/
  // Add the admin panel pages for Mitarbeiter. Use permissions pulled from above
   if (function_exists('add_menu_page')) 
     {
       add_menu_page(__('Mitarbeiter','mitarbeiter'), __('Mitarbeiter','mitarbeiter'), $allowed_group, 'mitarbeiter', 'mitarbeiter_edit');
     }
   if (function_exists('add_submenu_page')) 
     {
       add_submenu_page('mitarbeiter', __('Manage Mitarbeiter','mitarbeiter'), __('Manage Mitarbeiter','mitarbeiter'), $allowed_group, 'mitarbeiter', 'mitarbeiter_edit');
       
	   // Note only admin can change mitarbeiter options
       add_submenu_page('mitarbeiter', 'Mitarbeiter Eigenschaften', 'Mitarbeiter Eigenschaften', 'manage_options', 'mitarbeiter-categories', 'mitarbeiter_manage_categories');
       //add_submenu_page('mitarbeiter', __('Mitarbeiter Config','mitarbeiter'), __('Mitarbeiter Options','mitarbeiter'), 'manage_options', 'mitarbeiter-config', 'mitarbeiter_config_edit');
     }
}
?>