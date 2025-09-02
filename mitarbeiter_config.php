<?php
// Display the admin configuration page
function mitarbeiter_config_edit()
{
  global $wpdb, $initial_style;

  if (isset($_POST['permissions']) && isset($_POST['style']) && wp_verify_nonce(sanitize_text_field($_POST['_wpnonce']),'mitarbeiter-config') == false) {
		?>
		<div class="error"><p><strong><?php _e('Error','mitarbeiter'); ?>:</strong> <?php _e("Security check failure, try editing the config again",'mitarbeiter'); ?></p></div>
		<?php
  }
  elseif (isset($_POST['permissions']) && isset($_POST['style']))
    {
      if ($_POST['permissions'] == 'subscriber') { $new_perms = 'read'; }
      else if ($_POST['permissions'] == 'contributor') { $new_perms = 'edit_posts'; }
      else if ($_POST['permissions'] == 'author') { $new_perms = 'publish_posts'; }
      else if ($_POST['permissions'] == 'editor') { $new_perms = 'moderate_comments'; }
      else if ($_POST['permissions'] == 'admin') { $new_perms = 'manage_options'; }
      else { $new_perms = 'manage_options'; }

      // We want to sanitize this but the inbuilt function clatters an important char, re-instate it!
      $mitarbeiter_style = str_replace("&gt;",">",wp_filter_nohtml_kses($_POST['style']));
      $display_upcoming_days = sanitize_text_field($_POST['display_upcoming_days']);

      if ($_POST['display_author'] == 'on')
	      {
	        $disp_author = 'true';
	      }
      else
	      {
	        $disp_author = 'false';
	      }

      if ($_POST['display_jump'] == 'on')
        {
          $disp_jump = 'true';
        }
      else
        {
          $disp_jump = 'false';
        }

      if ($_POST['display_todays'] == 'on')
        {
          $disp_todays = 'true';
        }
      else
        {
          $disp_todays = 'false';
        }

      if ($_POST['display_upcoming'] == 'on')
        {
          $disp_upcoming = 'true';
        }
      else
        {
          $disp_upcoming = 'false';
        }

      if ($_POST['enable_categories'] == 'on')
        {
          $enable_categories = 'true';
        }
      else
        {
	        $enable_categories = 'false';
        }

      if ($_POST['enable_feed'] == 'on')
        {
            $enable_feed = 'true';
        }
      else
        {
            $enable_feed = 'false';
        }

      if ($_POST['enhance_contrast'] == 'on') {
          $enhance_contrast = 'true';
      } else {
          $enhance_contrast = 'false';
      }

      if ($_POST['show_attribution_link'] == 'on') {
          $show_attribution_link = 'true';
      } else {
          $show_attribution_link = 'false';
      }

      $wpdb->get_results($wpdb->prepare("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '%s' WHERE config_item='can_manage_events'",$new_perms));
      $wpdb->get_results($wpdb->prepare("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '%s' WHERE config_item='mitarbeiter_style'",$mitarbeiter_style));
      $wpdb->get_results($wpdb->prepare("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '%s' WHERE config_item='display_author'",$disp_author));
      $wpdb->get_results($wpdb->prepare("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '%s' WHERE config_item='display_jump'",$disp_jump));
      $wpdb->get_results($wpdb->prepare("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '%s' WHERE config_item='display_todays'",$disp_todays));
      $wpdb->get_results($wpdb->prepare("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '%s' WHERE config_item='display_upcoming'",$disp_upcoming));
      $wpdb->get_results($wpdb->prepare("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '%d' WHERE config_item='display_upcoming_days'",$display_upcoming_days));
      $wpdb->get_results($wpdb->prepare("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '%s' WHERE config_item='enable_categories'",$enable_categories));
      $wpdb->get_results($wpdb->prepare("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '%s' WHERE config_item='enable_feed'",$enable_feed));
      $contrast_present = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='enhance_contrast'");
      if (empty($contrast_present)) {
          $wpdb->get_results("INSERT INTO " . WP_MITARBEITER_CONFIG_TABLE . " SET config_item='enhance_contrast', config_value='false'");
      }
      $wpdb->get_results($wpdb->prepare("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '%s' WHERE config_item='enhance_contrast'",$enhance_contrast));
      $attribution_present = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='show_attribution_link'");
      if (empty($attribution_present)) {
          $wpdb->get_results("INSERT INTO " . WP_MITARBEITER_CONFIG_TABLE . " SET config_item='show_attribution_link', config_value='false'");
      }
      $wpdb->get_results($wpdb->prepare("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '%s' WHERE config_item='show_attribution_link'",$show_attribution_link));

      // Check to see if we are replacing the original style
      if (isset($_POST['reset_styles'])) {
        if ($_POST['reset_styles'] == 'on')
          {
            $wpdb->get_results("UPDATE " . WP_MITARBEITER_CONFIG_TABLE . " SET config_value = '".$initial_style."' WHERE config_item='mitarbeiter_style'");
          }
      }

      echo "<div class=\"updated\"><p><strong>".__('Settings saved','mitarbeiter').".</strong></p></div>";
    }

  // Pull the values out of the database that we need for the form
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='can_manage_events'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          $allowed_group = stripslashes($config->config_value);
        }
    }

  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='mitarbeiter_style'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          $mitarbeiter_style = stripslashes($config->config_value);
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='display_author'");
  $yes_disp_author = '';
  $no_disp_author = '';
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
	  if ($config->config_value == 'true')
	    {
	      $yes_disp_author = 'selected="selected"';
	    }
	  else
	    {
	      $no_disp_author = 'selected="selected"';
	    }
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='display_jump'");
  $yes_disp_jump = '';
  $no_disp_jump = '';
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          if ($config->config_value == 'true')
            {
              $yes_disp_jump = 'selected="selected"';
            }
          else
            {
              $no_disp_jump = 'selected="selected"';
            }
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='display_todays'");
  $yes_disp_todays = '';
  $no_disp_todays = '';
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          if ($config->config_value == 'true')
            {
              $yes_disp_todays = 'selected="selected"';
            }
          else
            {
              $no_disp_todays = 'selected="selected"';
            }
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='display_upcoming'");
  $yes_disp_upcoming = '';
  $no_disp_upcoming = '';
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          if ($config->config_value == 'true')
            {
              $yes_disp_upcoming = 'selected="selected"';
            }
          else
            {
              $no_disp_upcoming = 'selected="selected"';
            }
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='display_upcoming_days'");
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          $upcoming_days = stripslashes($config->config_value);
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='enable_categories'");
  $yes_enable_categories = '';
  $no_enable_categories = '';
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          if ($config->config_value == 'true')
            {
              $yes_enable_categories = 'selected="selected"';
            }
          else
            {
              $no_enable_categories = 'selected="selected"';
            }
        }
    }
  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='enable_feed'");
  $yes_enable_feed = '';
  $no_enable_feed = '';
  if (!empty($configs))
    {
      foreach ($configs as $config)
        {
          if ($config->config_value == 'true')
            {
                $yes_enable_feed = 'selected="selected"';
            }
          else
            {
                $no_enable_feed = 'selected="selected"';
            }
        }
    }

  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='enhance_contrast'");
  $yes_enhance_contrast = '';
  $no_enhance_contrast = '';
  if (!empty($configs)) {
      foreach ($configs as $config) {
          if ($config->config_value == 'true') {
              $yes_enhance_contrast = 'selected="selected"';
          } else {
              $no_enhance_contrast = 'selected="selected"';
          }
      }
  }

  $configs = $wpdb->get_results("SELECT config_value FROM " . WP_MITARBEITER_CONFIG_TABLE . " WHERE config_item='show_attribution_link'");
  $yes_show_attribution_link = '';
  $no_show_attribution_link = '';
  if (!empty($configs))
  {
      foreach ($configs as $config)
      {
          if ($config->config_value == 'true')
          {
              $yes_show_attribution_link = 'selected="selected"';
          }
          else
          {
              $no_show_attribution_link = 'selected="selected"';
          }
      }
  }
  $subscriber_selected = '';
  $contributor_selected = '';
  $author_selected = '';
  $editor_selected = '';
  $admin_selected = '';
  if ($allowed_group == 'read') { $subscriber_selected='selected="selected"';}
  else if ($allowed_group == 'edit_posts') { $contributor_selected='selected="selected"';}
  else if ($allowed_group == 'publish_posts') { $author_selected='selected="selected"';}
  else if ($allowed_group == 'moderate_comments') { $editor_selected='selected="selected"';}
  else if ($allowed_group == 'manage_options') { $admin_selected='selected="selected"';}

  // Now we render the form
  ?>
  <div class="wrap">
  <h2><?php _e('Mitarbeiter Options','mitarbeiter'); ?></h2>
  <form name="quoteform" id="quoteform" class="wrap" method="post" action="<?php echo admin_url('admin.php?page=mitarbeiter-config'); ?>">
		<?php wp_nonce_field('mitarbeiter-config'); ?>
                <div id="linkadvanceddiv" class="postbox">
                        <div style="float: left; width: 98%; clear: both;" class="inside">
                                <table cellpadding="5" cellspacing="5">
				<tr>
                                <td><legend><?php _e('Choose the lowest user group that may manage events','mitarbeiter'); ?></legend></td>
				<td>        <select name="permissions">
				            <option value="subscriber"<?php echo $subscriber_selected ?>><?php _e('Subscriber','mitarbeiter')?></option>
				            <option value="contributor" <?php echo $contributor_selected ?>><?php _e('Contributor','mitarbeiter')?></option>
				            <option value="author" <?php echo $author_selected ?>><?php _e('Author','mitarbeiter')?></option>
				            <option value="editor" <?php echo $editor_selected ?>><?php _e('Editor','mitarbeiter')?></option>
				            <option value="admin" <?php echo $admin_selected ?>><?php _e('Administrator','mitarbeiter')?></option>
				        </select>
                                </td>
                                </tr>
                                <tr>
				<td><legend><?php _e('Do you want to display the author name on events?','mitarbeiter'); ?></legend></td>
                                <td>    <select name="display_author">
                                        <option value="on" <?php echo $yes_disp_author ?>><?php _e('Yes','mitarbeiter') ?></option>
                                        <option value="off" <?php echo $no_disp_author ?>><?php _e('No','mitarbeiter') ?></option>
                                    </select>
                                </td>
                                </tr>
                                <tr>
				<td><legend><?php _e('Display a jumpbox for changing month and year quickly?','mitarbeiter'); ?></legend></td>
                                <td>    <select name="display_jump">
                                         <option value="on" <?php echo $yes_disp_jump ?>><?php _e('Yes','mitarbeiter') ?></option>
                                         <option value="off" <?php echo $no_disp_jump ?>><?php _e('No','mitarbeiter') ?></option>
                                    </select>
                                </td>
                                </tr>
                                <tr>
				<td><legend><?php _e('Display todays events?','mitarbeiter'); ?></legend></td>
                                <td>    <select name="display_todays">
						<option value="on" <?php echo $yes_disp_todays ?>><?php _e('Yes','mitarbeiter') ?></option>
						<option value="off" <?php echo $no_disp_todays ?>><?php _e('No','mitarbeiter') ?></option>
                                    </select>
                                </td>
                                </tr>
                                <tr>
				<td><legend><?php _e('Display upcoming events?','mitarbeiter'); ?></legend></td>
                                <td>    <select name="display_upcoming">
						<option value="on" <?php echo $yes_disp_upcoming ?>><?php _e('Yes','mitarbeiter') ?></option>
						<option value="off" <?php echo $no_disp_upcoming ?>><?php _e('No','mitarbeiter') ?></option>
                                    </select>
				    <?php _e('for','mitarbeiter'); ?> <input type="text" name="display_upcoming_days" value="<?php echo $upcoming_days ?>" size="1" maxlength="2" /> <?php _e('days into the future','mitarbeiter'); ?>
                                </td>
                                </tr>
                                <tr>
				<td><legend><?php _e('Enable event categories?','mitarbeiter'); ?></legend></td>
                                <td>    <select name="enable_categories">
				                <option value="on" <?php echo $yes_enable_categories ?>><?php _e('Yes','mitarbeiter') ?></option>
						<option value="off" <?php echo $no_enable_categories ?>><?php _e('No','mitarbeiter') ?></option>
                                    </select>
                                </td>
                                </tr>
                                <tr>
                <td><legend><?php _e('Enable iMitarbeiter feed?','mitarbeiter'); ?></legend></td>
                                <td>    <select name="enable_feed">
                        <option value="on" <?php echo $yes_enable_feed ?>><?php _e('Yes','mitarbeiter') ?></option>
                        <option value="off" <?php echo $no_enable_feed ?>><?php _e('No','mitarbeiter') ?></option>
                                    </select>
                                </td>
                                </tr>

                                 <tr>
                                     <td><legend><?php _e('Enhance foreground contrast against category colour?','mitarbeiter'); ?></legend></td>
                                        <td>    <select name="enhance_contrast">
                                                <option value="on" <?php echo $yes_enhance_contrast ?>><?php _e('Yes','mitarbeiter') ?></option>
                                                <option value="off" <?php echo $no_enhance_contrast ?>><?php _e('No','mitarbeiter') ?></option>
                                            </select>
                                        </td>
                                 </tr>

                                <tr>
                <td><legend><?php _e('Enable attribution link?','mitarbeiter'); ?></legend></td>
                                <td>    <select name="show_attribution_link">
                                        <?php if ($yes_show_attribution_link == '' && $yes_show_attribution_link == '') { ?>
                                            <option value="on" selected="selected"></option>
                                        <?php } ?>
                                <option value="on" <?php echo $yes_show_attribution_link ?>><?php _e('Yes','mitarbeiter') ?></option>
                        <option value="off" <?php echo $no_show_attribution_link ?>><?php _e('No','mitarbeiter') ?></option>
                                     </select>
                                </td>
                                </tr>
                                <tr>
				<td style="vertical-align:top;"><legend><?php _e('Configure the stylesheet for Mitarbeiter','mitarbeiter'); ?></legend></td>
				<td><textarea name="style" rows="10" cols="60" tabindex="2"><?php echo $mitarbeiter_style; ?></textarea><br />
                                <input type="checkbox" name="reset_styles" /> <?php _e('Tick this box if you wish to reset the Mitarbeiter style to default','mitarbeiter'); ?></td>
                                </tr>
                                </table>
			</div>
                        <div style="clear:both; height:1px;">&nbsp;</div>
	        </div>
                <input type="submit" name="save" class="button bold" value="<?php _e('Save','mitarbeiter'); ?> &raquo;" />
  </form>
  </div>
  <?php


}
?>