jQuery(document).on("click","a.sw-mediabild",function(event) {
	event.preventDefault();
	aktbildlink=jQuery(this);
	var feld=aktbildlink.attr("data-feld");
	
	var gallery_window = wp.media({
		title: 'Bitte Bild auswählen',
		library: {type: 'image'},
		multiple: false,
		button: {text: 'auswählen'}
	});

	gallery_window.on('select', function () {
		var user_selection = gallery_window.state().get('selection').first().toJSON();
		jQuery("input[name='"+feld+"']").val(user_selection.url);
		jQuery("#bild_"+feld+"").attr("src",user_selection.url);
	});
	gallery_window.open();
	return false;
});		

/*
function speichern_sichtbarkeit(wp_mitarbeiter_token,$sichtbarkeit){
	$.ajax({
	method: "POST",
	url: "/wp-content/themes/ecjugend20192/func.php", 
	data: "aktion=set_offsetWidth&offsetWidth="+document.body.offsetWidth, 
	success: function(result){
		//hier passiert nichts, unten, falls die Werte abweichen
		
	},
	fail: function(){
		alert('fail');
	}
	});
}
*/