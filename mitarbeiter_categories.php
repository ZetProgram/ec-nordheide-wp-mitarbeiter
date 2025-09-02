<?php

// Function to handle the management of categories
function mitarbeiter_manage_categories()
{
	//Kategorien
	$a_PlatzhalterTpl = initialize_a_Platzhalter(WP_MITARBEITER_KATEGORIEN_TABLE,$a_PlatzhalterTpl);
	
	global $wpdb;
	
	//löschen
	$delete_wp_mitarbeiter_kategorien_token = !empty($_REQUEST['delete_wp_mitarbeiter_kategorien_token']) ? sanitize_text_field($_REQUEST['delete_wp_mitarbeiter_kategorien_token']) : '';
	if($delete_wp_mitarbeiter_kategorien_token != ""){
		//update
		$sql = $wpdb->prepare("DELETE FROM " . WP_MITARBEITER_KATEGORIEN_TABLE . " WHERE wp_mitarbeiter_kategorien_token=%s LIMIT 1;",$delete_wp_mitarbeiter_kategorien_token);
        $wpdb->get_results($sql);
		//echo $sql;
	} 
	
	//editieren speichern
	$edit_wp_mitarbeiter_kategorien_token = !empty($_REQUEST['edit_wp_mitarbeiter_kategorien_token']) ? sanitize_text_field($_REQUEST['edit_wp_mitarbeiter_kategorien_token']) : '';
	$wp_mitarbeiter_kategorien_bezeichnung = !empty($_REQUEST['wp_mitarbeiter_kategorien_bezeichnung']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_kategorien_bezeichnung']) : '';
	$action = !empty($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
	if($edit_wp_mitarbeiter_kategorien_token != "" && $action == 'speichern'){
		//update
		$sql = $wpdb->prepare("UPDATE " . WP_MITARBEITER_KATEGORIEN_TABLE . " SET wp_mitarbeiter_kategorien_bezeichnung=%s WHERE wp_mitarbeiter_kategorien_token=%s",$wp_mitarbeiter_kategorien_bezeichnung,$edit_wp_mitarbeiter_kategorien_token);
        $wpdb->get_results($sql);
		echo $sql;
	} 
	
	$wp_mitarbeiter_kategorien_bezeichnung = !empty($_REQUEST['wp_mitarbeiter_kategorien_bezeichnung']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_kategorien_bezeichnung']) : '';
	$action = !empty($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
	if($edit_wp_mitarbeiter_kategorien_token == "" && $action == 'speichern' && $wp_mitarbeiter_kategorien_bezeichnung != ""){
		//insert
		
		$wp_mitarbeiter_kategorien_token = make_token();
		$sql = $wpdb->prepare("INSERT INTO " . WP_MITARBEITER_KATEGORIEN_TABLE . " 
		SET 
		wp_mitarbeiter_kategorien_token='%s', 
		wp_mitarbeiter_kategorien_bezeichnung='%s', 
		wp_mitarbeiter_kategorien_sichtbar='0'",
		$wp_mitarbeiter_kategorien_token,
		$wp_mitarbeiter_kategorien_bezeichnung
		);
		$wpdb->get_results($sql);
		
	}
	
	$edit_wp_mitarbeiter_kategorien_token = !empty($_REQUEST['edit_wp_mitarbeiter_kategorien_token']) ? sanitize_text_field($_REQUEST['edit_wp_mitarbeiter_kategorien_token']) : '';
	//echo $wp_mitarbeiter_kategorien_token;
	//editieren aufrufen
	if($edit_wp_mitarbeiter_kategorien_token != ""){
		$o_wp_mitarbeiter_kategorien = hole_wp_mitarbeiter_kategorien($edit_wp_mitarbeiter_kategorien_token,"");
		$a_wp_mitarbeiter_kategorien = object_to_array($o_wp_mitarbeiter_kategorien[0]);
		$a_PlatzhalterTpl['wp_mitarbeiter_kategorien_bezeichnung'] = $a_wp_mitarbeiter_kategorien['wp_mitarbeiter_kategorien_bezeichnung'];
		$a_PlatzhalterTpl['wp_mitarbeiter_kategorien_token'] = $a_wp_mitarbeiter_kategorien['wp_mitarbeiter_kategorien_token'];
	}
	
	//unsichtbar machen
	$sichtbar_wp_mitarbeiter_kategorien_token = !empty($_REQUEST['sichtbar_wp_mitarbeiter_kategorien_token']) ? sanitize_text_field($_REQUEST['sichtbar_wp_mitarbeiter_kategorien_token']) : '';
	//echo $sichtbar_wp_mitarbeiter_kategorien_token;
	if($sichtbar_wp_mitarbeiter_kategorien_token != ""){
		$a_token_aktion = explode("_",$sichtbar_wp_mitarbeiter_kategorien_token);
		//pf($a_token_aktion);
		$wp_mitarbeiter_kategorien_token = $a_token_aktion[0];
		$wp_mitarbeiter_kategorien_sichtbar = $a_token_aktion[1];
		
		$sql = $wpdb->prepare("UPDATE " . WP_MITARBEITER_KATEGORIEN_TABLE . " SET wp_mitarbeiter_kategorien_sichtbar=$wp_mitarbeiter_kategorien_sichtbar WHERE wp_mitarbeiter_kategorien_token=%s",$wp_mitarbeiter_kategorien_token);
        $wpdb->get_results($sql);
		//echo $sql;
	}
	
	$a_PlatzhalterTpl['liste_kategorien'] = liste_kategorien();

	

	//Funktionen
	$a_PlatzhalterTpl = initialize_a_Platzhalter(WP_MITARBEITER_FUNKTIONEN_TABLE,$a_PlatzhalterTpl);
	
	
		//löschen
	$delete_wp_mitarbeiter_funktionen_token = !empty($_REQUEST['delete_wp_mitarbeiter_funktionen_token']) ? sanitize_text_field($_REQUEST['delete_wp_mitarbeiter_funktionen_token']) : '';
	if($delete_wp_mitarbeiter_funktionen_token != ""){
		//update
		$sql = $wpdb->prepare("DELETE FROM " . WP_MITARBEITER_FUNKTIONEN_TABLE . " WHERE wp_mitarbeiter_funktionen_token=%s LIMIT 1;",$delete_wp_mitarbeiter_funktionen_token);
        $wpdb->get_results($sql);
		//echo $sql;
	} 
	
	//editieren speichern
	$edit_wp_mitarbeiter_funktionen_token = !empty($_REQUEST['edit_wp_mitarbeiter_funktionen_token']) ? sanitize_text_field($_REQUEST['edit_wp_mitarbeiter_funktionen_token']) : '';
	$wp_mitarbeiter_funktionen_bezeichnung = !empty($_REQUEST['wp_mitarbeiter_funktionen_bezeichnung']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_funktionen_bezeichnung']) : '';
	$action = !empty($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
	if($edit_wp_mitarbeiter_funktionen_token != "" && $action == 'speichern'){
		//update
		$sql = $wpdb->prepare("UPDATE " . WP_MITARBEITER_FUNKTIONEN_TABLE . " SET wp_mitarbeiter_funktionen_bezeichnung=%s WHERE wp_mitarbeiter_funktionen_token=%s",$wp_mitarbeiter_funktionen_bezeichnung,$edit_wp_mitarbeiter_funktionen_token);
        $wpdb->get_results($sql);
		echo $sql;
	} 
	
	$wp_mitarbeiter_funktionen_bezeichnung = !empty($_REQUEST['wp_mitarbeiter_funktionen_bezeichnung']) ? sanitize_text_field($_REQUEST['wp_mitarbeiter_funktionen_bezeichnung']) : '';
	$action = !empty($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
	if($edit_wp_mitarbeiter_funktionen_token == "" && $action == 'speichern' && $wp_mitarbeiter_funktionen_bezeichnung != ""){
		//insert
		
		$wp_mitarbeiter_funktionen_token = make_token();
		$sql = $wpdb->prepare("INSERT INTO " . WP_MITARBEITER_FUNKTIONEN_TABLE . " 
		SET 
		wp_mitarbeiter_funktionen_token='%s', 
		wp_mitarbeiter_funktionen_bezeichnung='%s', 
		wp_mitarbeiter_funktionen_sichtbar='0'",
		$wp_mitarbeiter_funktionen_token,
		$wp_mitarbeiter_funktionen_bezeichnung
		);
		$wpdb->get_results($sql);
		
	}
	
	$edit_wp_mitarbeiter_funktionen_token = !empty($_REQUEST['edit_wp_mitarbeiter_funktionen_token']) ? sanitize_text_field($_REQUEST['edit_wp_mitarbeiter_funktionen_token']) : '';
	//echo $edit_wp_mitarbeiter_funktionen_token;
	//editieren aufrufen
	if($edit_wp_mitarbeiter_funktionen_token != ""){
		$o_wp_mitarbeiter_funktionen = hole_wp_mitarbeiter_funktionen($edit_wp_mitarbeiter_funktionen_token,"");
		$a_wp_mitarbeiter_funktionen = object_to_array($o_wp_mitarbeiter_funktionen[0]);
		$a_PlatzhalterTpl['wp_mitarbeiter_funktionen_bezeichnung'] = $a_wp_mitarbeiter_funktionen['wp_mitarbeiter_funktionen_bezeichnung'];
		$a_PlatzhalterTpl['wp_mitarbeiter_funktionen_token'] = $a_wp_mitarbeiter_funktionen['wp_mitarbeiter_funktionen_token'];
	}
	
	//unsichtbar machen
	$sichtbar_wp_mitarbeiter_funktionen_token = !empty($_REQUEST['sichtbar_wp_mitarbeiter_funktionen_token']) ? sanitize_text_field($_REQUEST['sichtbar_wp_mitarbeiter_funktionen_token']) : '';
	//echo $sichtbar_wp_mitarbeiter_funktionen_token;
	if($sichtbar_wp_mitarbeiter_funktionen_token != ""){
		$a_token_aktion = explode("_",$sichtbar_wp_mitarbeiter_funktionen_token);
		//pf($a_token_aktion);
		$wp_mitarbeiter_funktionen_token = $a_token_aktion[0];
		$wp_mitarbeiter_funktionen_sichtbar = $a_token_aktion[1];
		
		$sql = $wpdb->prepare("UPDATE " . WP_MITARBEITER_FUNKTIONEN_TABLE . " SET wp_mitarbeiter_funktionen_sichtbar=$wp_mitarbeiter_funktionen_sichtbar WHERE wp_mitarbeiter_funktionen_token=%s",$wp_mitarbeiter_funktionen_token);
        $wpdb->get_results($sql);
		//echo $sql;
	}
	
	
	$a_PlatzhalterTpl['liste_funktionen'] = liste_funktionen();
	
	$a_PlatzhalterTpl['admin_url'] = admin_url('admin.php?page=mitarbeiter-categories'.SUPERADMIN);
	//Ausgabe
	$s_editform = ausgeben_tpl('mitarbeiter_kat_funk',$a_PlatzhalterTpl);
	echo $s_editform;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
/*

      // Proceed with the save  
      $sql = $wpdb->prepare("INSERT INTO " . WP_MITARBEITER_CATEGORIES_TABLE . " SET category_name='%s', category_colour='%s'",sanitize_text_field($_POST['category_name']),sanitize_text_field($_POST['category_colour']));
      $wpdb->get_results($sql);
         $sql = $wpdb->prepare("DELETE FROM " . WP_MITARBEITER_CATEGORIES_TABLE . " WHERE category_id=%d",sanitize_text_field($_GET['category_id']));
        $wpdb->get_results($sql);
        $sql = $wpdb->prepare("UPDATE " . WP_MITARBEITER_TABLE . " SET event_category=1 WHERE event_category=%d",sanitize_text_field($_GET['category_id']));
        $wpdb->get_results($sql);
       
      $sql = $wpdb->prepare("SELECT * FROM " . WP_MITARBEITER_CATEGORIES_TABLE . " WHERE category_id=%d",sanitize_text_field($_GET['category_id']));
      $cur_cat = $wpdb->get_row($sql);
    
      // Proceed with the save
        $sql = $wpdb->prepare("UPDATE " . WP_MITARBEITER_CATEGORIES_TABLE . " SET category_name='%s', category_colour='%s' WHERE category_id=%d",sanitize_text_field($_POST['category_name']),sanitize_text_field($_POST['category_colour']),sanitize_text_field($_POST['category_id']));
        $wpdb->get_results($sql);
        
    // We pull the categories from the database	
    $categories = $wpdb->get_results("SELECT * FROM " . WP_MITARBEITER_CATEGORIES_TABLE . " ORDER BY category_id ASC");

	     echo stripslashes($category->category_id);
	     echo htmlspecialchars(stripslashes($category->category_name));
	     echo stripslashes($category->category_colour);
	     
		 echo admin_url('admin.php?page=mitarbeiter-categories&amp;mode=edit&amp;category_id='.stripslashes($category->category_id));*/
}


function liste_kategorien(){
	$o_wp_mitarbeiter_kategorien = hole_wp_mitarbeiter_kategorien();
	$a_wp_mitarbeiter_kategorien = object_to_array($o_wp_mitarbeiter_kategorien);
	
	
	//pf($a_wp_mitarbeiter_kategorien);
	foreach($a_wp_mitarbeiter_kategorien as $a_kategorie){
		
		$a_sichtbar = array(0 => 1,1=>0);
		$a_sichtbar_icon = array(0 => 'fa-eye-slash',1=>'fa-eye');
		$a_sichtbar_title = array(0 => 'zeigen',1=>'verstecken');
		$a_sichtbar_background = array(0 => '#ddd',1=>'#eee');
		
		$anzahl_verbundene_mitarbeiter = hole_anzahl_mit_kategorie_verbunden_mitarbeiter($a_kategorie['wp_mitarbeiter_kategorien_id']);
		$admin_url = admin_url('admin.php?page=mitarbeiter-categories'.SUPERADMIN);
		$s_liste_kategorie .= '<div style="float: left;margin: 5px;background-color:'.$a_sichtbar_background[$a_kategorie['wp_mitarbeiter_kategorien_sichtbar']].'; border: 1px solid grey;padding: 3px;">
		<div style="float: left;margin-right: 3px;" title="Kategorie bearbeiten"><a style="text-decoration: none;" href="'.$admin_url.'&edit_wp_mitarbeiter_kategorien_token='.$a_kategorie['wp_mitarbeiter_kategorien_token'].'">'.$a_kategorie['wp_mitarbeiter_kategorien_bezeichnung'].'&nbsp;<i class="fas fa-edit"></i></a></div>
		<div style="float: left;margin-right: 3px;" title="Kategorie löschen ('.$anzahl_verbundene_mitarbeiter.' Mitarbeiter verknüpft)"><a href="'.$admin_url.'&delete_wp_mitarbeiter_kategorien_token='.$a_kategorie['wp_mitarbeiter_kategorien_token'].'"" onclick="return confirm(\'Wollen Sie diese Kategorie wirklich löschen ('.$anzahl_verbundene_mitarbeiter.' Mitarbeiter verknüpft)?\')"><i class="fas fa-trash-alt"></i></a></div>
		<div style="float: left;" title="Kategorie '.$a_sichtbar_title[$a_kategorie['wp_mitarbeiter_kategorien_sichtbar']].'"><a style="text-decoration: none;" href="'.$admin_url.'&sichtbar_wp_mitarbeiter_kategorien_token='.$a_kategorie['wp_mitarbeiter_kategorien_token'].'_'.$a_sichtbar[$a_kategorie['wp_mitarbeiter_kategorien_sichtbar']].'"><i class="fas '.$a_sichtbar_icon[$a_kategorie['wp_mitarbeiter_kategorien_sichtbar']].'"></i></a></div>
		</div>';
		
	}
	return $s_liste_kategorie .'<div style="clear: both;"></div>';
}

function liste_funktionen(){
	$o_wp_mitarbeiter_funktionen = hole_wp_mitarbeiter_funktionen();
	$a_wp_mitarbeiter_funktionen = object_to_array($o_wp_mitarbeiter_funktionen);
	
	
	//pf($a_wp_mitarbeiter_kategorien);
	foreach($a_wp_mitarbeiter_funktionen as $a_funktion){
		
		$a_sichtbar = array(0 => 1,1=>0);
		$a_sichtbar_icon = array(0 => 'fa-eye-slash',1=>'fa-eye');
		$a_sichtbar_title = array(0 => 'zeigen',1=>'verstecken');
		$a_sichtbar_background = array(0 => '#ddd',1=>'#eee');
		
		$anzahl_verbundene_mitarbeiter = hole_anzahl_mit_funktionen_verbunden_mitarbeiter($a_funktion['wp_mitarbeiter_funktionen_id']);
		$admin_url = admin_url('admin.php?page=mitarbeiter-categories'.SUPERADMIN);
		$s_liste_funktionen .= '<div style="float: left;margin: 5px;background-color:'.$a_sichtbar_background[$a_funktion['wp_mitarbeiter_funktionen_sichtbar']].'; border: 1px solid grey;padding: 3px;">
		<div style="float: left;margin-right: 3px;" title="Funktion bearbeiten"><a style="text-decoration: none;" href="'.$admin_url.'&edit_wp_mitarbeiter_funktionen_token='.$a_funktion['wp_mitarbeiter_funktionen_token'].'">'.$a_funktion['wp_mitarbeiter_funktionen_bezeichnung'].'&nbsp;<i class="fas fa-edit"></i></a></div>
		<div style="float: left;margin-right: 3px;" title="Funktion löschen ('.$anzahl_verbundene_mitarbeiter.' Mitarbeiter verknüpft)"><a href="'.$admin_url.'&delete_wp_mitarbeiter_funktionen_token='.$a_funktion['wp_mitarbeiter_funktionen_token'].'"" onclick="return confirm(\'Wollen Sie diese Funktion wirklich löschen ('.$anzahl_verbundene_mitarbeiter.' Mitarbeiter verknüpft)?\')"><i class="fas fa-trash-alt"></i></a></div>
		<div style="float: left;" title="Funktion '.$a_sichtbar_title[$a_funktion['wp_mitarbeiter_funktionen_sichtbar']].'"><a style="text-decoration: none;" href="'.$admin_url.'&sichtbar_wp_mitarbeiter_funktionen_token='.$a_funktion['wp_mitarbeiter_funktionen_token'].'_'.$a_sichtbar[$a_funktion['wp_mitarbeiter_funktionen_sichtbar']].'"><i class="fas '.$a_sichtbar_icon[$a_funktion['wp_mitarbeiter_funktionen_sichtbar']].'"></i></a></div>
		</div>';
		
	}
	return $s_liste_funktionen .'<div style="clear: both;"></div>';
}

function hole_anzahl_mit_kategorie_verbunden_mitarbeiter($wp_mitarbeiter_kategorien_id){
	global $wpdb;
	$sql = $wpdb->prepare("SELECT COUNT(*) AS anzahl FROM " . WP_MITARBEITER_TABLE . " WHERE wp_mitarbeiter_kategorien_id=%d",$wp_mitarbeiter_kategorien_id);
	$cur_cat = $wpdb->get_row($sql);
	return $cur_cat->anzahl;
}

function hole_anzahl_mit_funktionen_verbunden_mitarbeiter($wp_mitarbeiter_funktionen_id){
	global $wpdb;
	$sql = $wpdb->prepare("SELECT COUNT(*) AS anzahl FROM " . WP_MITARBEITER_TABLE . " WHERE wp_mitarbeiter_funktionen_id=%d",$wp_mitarbeiter_funktionen_id);
	$cur_cat = $wpdb->get_row($sql);
	return $cur_cat->anzahl;
}

?>