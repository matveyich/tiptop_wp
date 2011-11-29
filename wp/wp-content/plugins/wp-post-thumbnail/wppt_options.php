 <?php require_once '../wp-config.php';

define ('WPPT_CUSTOM_THUMBNAILS_LIMIT', 3);

/*$xml = simplexml_load_file(WP_PLUGIN_DIR.'/wp-post-thumbnail/employees.xml');*/

function array2XML(XMLWriter $xml, $data){
    foreach($data as $key => $value){
        if(is_array($value)){
            $xml->startElement($key);
            array2XML($xml, $value);
            $xml->endElement();
            continue;
        }
        $xml->writeElement($key, $value);
    }
}

function makeXML($arr) {
	$xml = new XmlWriter();
	$xml->openMemory();
	$xml->startDocument('1.0', 'UTF-8');
	$xml->startElement('wppt');
	array2XML($xml, $arr);
	$xml->endElement();
	return $xml->outputMemory(true);
}

if (isset($_POST['update_wppt_thumbnails'])) {
	$i = 1;
	$arr = array();

	$thumb_prefix = 'wppt'.$i;
	while ( isset($_POST[$thumb_prefix.'_id']) ) {
		$arr[$thumb_prefix] = array('id' => $thumb_prefix,
					'name' => $_POST[$thumb_prefix.'_name'],
					'desc' => $_POST[$thumb_prefix.'_desc'],
					'width' => $_POST[$thumb_prefix.'_width'],
					'height' => $_POST[$thumb_prefix.'_height'] );
		$i++;
		$thumb_prefix = 'wppt'.$i;
	}

	$wppt_thumbnail_options_xml = makeXML($arr);
	update_option("wppt_thumbnail_options", $arr);

	$wppt_thumbnail_options = get_option('wppt_thumbnails_options');
	print_r (htmlentities($wppt_thumbnail_options_xml)); ?>

	<script language="javascript" type="text/javascript">
	jQuery(document).ready(function() {
		jQuery('#save_button').hide();
		jQuery('#saved_msg').show().fadeOut(3968, function() {
			jQuery('#save_button').fadeIn('slow');
		});
	});
	</script>

<?php } ?>


<script language="javascript" type="text/javascript">
jQuery(document).ready(function() {
	var i = 0;

	<?php if (isset($_POST['update_wppt_thumbnails'])) { ?>
		jQuery('#save_button').hide();
		jQuery('#saved_msg').show().fadeOut(3968, function() {
			jQuery('#save_button').fadeIn('slow');
		});
	<?php } ?>

	function addThumbnail(id, name, desc, width, height) {
		jQuery('#wppt-conf-form > .thumbnails').append('<fieldset id="fieldset' + id + '" class="wppt_custom_fieldset">' + "\n" 
			+ '<legend style="padding:0 6px;">' + id + '</legend>' + "\n" 
			+ '<input type="hidden" id="' + id + '_id" name="' + id + '_id" value="' + id + '" />' + "\n" 
			+ '<label for="' + id + '_name"><?php _e('Name', 'wp-post-thumbnail'); ?>:</label><br />' + "\n" 
			+ '<input type="text" value="' + name + '" name="' + id + '_name" id="' + id + '_name" /><br /><br />' + "\n" 

			+ '<label for="' + id + '_desc"><?php _e('Description', 'wp-post-thumbnail'); ?>:</label><br />' + "\n" 
			+ '<input type="text" value="' + desc + '" name="' + id + '_desc" id="' + id + '_desc" /><br /><br />' + "\n" 

			+ '<label for="' + id + '_width"><?php _e('Width', 'wp-post-thumbnail'); ?>:</label><br />' + "\n" 
			+ '<input type="text" value="' + width + '" name="' + id + '_width" id="' + id + '_width" /> px<br /><br />' + "\n" 

			+ '<label for="' + id + '_height"><?php _e('Height', 'wp-post-thumbnail'); ?>:</label><br />' + "\n" 
			+ '<input type="text" value="' + height + '" name="' + id + '_height" id="' + id + '_height" /> px<br /><br />' + "\n" 

			+ '</fieldset>' + "\n");
	} /* end function addThumbnail */

	function hideShowAddRemoveButtons() {
		jQuery('#remove').show();
		jQuery('#add').show();

		if (i == <?php echo WPPT_CUSTOM_THUMBNAILS_LIMIT; ?>) {
			jQuery('#add').hide();
		} else if (i == 1) {
			jQuery('#remove').hide();
		}
	}


	jQuery('#add').click(function() {
		addThumbnail('wppt' + ++i, 'name', 'description', '100', '100');
		hideShowAddRemoveButtons();
	});


	jQuery('#remove').click(function() {
		jQuery('#fieldsetwppt' + i--).remove();
		hideShowAddRemoveButtons();
	});


	<?php foreach ( $wppt_thumbnail_options as $thumbnail ) { ?>
		i++;
		addThumbnail('<?php echo $thumbnail['id']; ?>',
			'<?php echo $thumbnail['name']; ?>',
			'<?php echo $thumbnail['desc']; ?>',
			'<?php echo $thumbnail['width']; ?>',
			'<?php echo $thumbnail['height']; ?>' );
	<?php } ?>
	hideShowAddRemoveButtons();
});
</script>

<div class="wrap">
	<h2><?php _e('Set WP Post Thumbnail Defaults', 'wp-post-thumbnail'); ?></h2>
	<div class="narrow">
		<form action="" method="post" id="wppt-conf-form">
			<div class="thumbnails"></div>
	
			<div class="actions">
				<p id="save_button" style="margin:0;" ><input name="update_wppt_thumbnails" type="submit" value="Save settings" class="button" style="background:#E9FFC2;margin:0 10px 5px 0;float:left;" /><?php _e('Remember to save your thumbnail settings if you made any changes', 'wp-post-thumbnail'); ?>.</p>
				<p id="saved_msg" style="display:none;margin:0;padding:10px;background:yellow;border:1px solid #ddd;"><?php _e('Saved changes', 'wp-post-thumbnail'); ?>!</p>
			</div>
		</form><br style="clear:both;" />
		<button style="display:none;" id="add"><?php _e('Add another thumbnail?', 'wp-post-thumbnail'); ?></button> <button style="display:none;margin-left:10px;" id="remove"><?php _e('One less thumbnail please', 'wp-post-thumbnail'); ?></button>
		<p id="debug"></p>
	</div>
</div>