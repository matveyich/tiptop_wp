<?php require_once '../../../wp-config.php';

$pid = wppt_new_autosaved_draft_id($_POST['pid']);

$tid = $_POST['tid'];
$name = $wppt_thumbnail_options[$tid]['name'];
$desc = $wppt_thumbnail_options[$tid]['desc'];
$w = $wppt_thumbnail_options[$tid]['width'];
$h = $wppt_thumbnail_options[$tid]['height'];

function wppt_new_autosaved_draft_id($pid) {
	global $wpdb;

	$autosavedraft_id = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta 
					WHERE meta_key = '_wppt_autosavedraft_pid' 
					AND meta_value = '". $pid ."' ");

	if ($autosavedraft_id != null) {
		return $autosavedraft_id;
	} else {
		return $pid;
	}
}

function wppt_resizeThumbnailImage($thumb_image_name, $image, $width, $height, $start_width, $start_height, $scale) {
	$newImageWidth = ceil($width * $scale);
	$newImageHeight = ceil($height * $scale);
	$newImage = imagecreatetruecolor($newImageWidth,$newImageHeight);
	$source = imagecreatefromjpeg($image);
	imagecopyresampled($newImage,$source,0,0,$start_width,$start_height,$newImageWidth,$newImageHeight,$width,$height);
	imagejpeg($newImage,$thumb_image_name,82);
	chmod($thumb_image_name, 0777);
	imagedestroy($newImage);
	imagedestroy($source);
	return $thumb_image_name;
}

/* Generate a random string of numbers and alphabets */
function wppt_generateRandStr($length = 5) {
	$characters = "PQO0WI1E2URY3TLA8KS7JD6HF2GVBCNXMZqazwsxedrf4cvty8bngh1uij0kmlpo";
	$string = "";

	for ($p = 0; $p < $length; $p++) {
		$string .= $characters[mt_rand(0, strlen($characters))];
	}
	
	return $string;
} /* http://www.lost-in-code.com/php-code/php-random-string-with-numbers-and-letters */

/* +---------------------------------------------------------------------------+
   | Save thumbnail button is clicked as signalled by $_POST["save_thumbnail"] |
   +---------------------------------------------------------------------------+ */
if ( (isset($_POST["save_thumbnail"])) && ($_POST["save_thumbnail"]=="yes") )  {

	/* get x, y, width and height properties of cropped image area */
	$x = $_POST["crop_left"];
	$y = $_POST["crop_top"];
	$crop_w = $_POST["crop_width"];
	$crop_h = $_POST["crop_height"];

	/* thumbnail filename as specified by user. Spaces will be replaced with '-' */
	$title = str_replace(' ', '-', $_POST["crop_title"]);

	/* random string of 5 alphanumerics is appended to thumbnail filename
	   eg. All-New-2008-Fiat-500_u2Rh9.jpg */
	$thumbnail_name = $title.'_'.wppt_generateRandStr().'.jpg';

	/* thumbnail's specified width divided by cropped image area's width */
	$scale = $w / $crop_w;

	/* Filepath for cropped thumbnail to be saved */
	$thumbnail_location = $wppt_general_options['upload_path'].'/'.$thumbnail_name;

	/* Check for existing thumbnail in custom field.
	   Get value from thumbnail's custom field key. */
	$existing_thumbnail = get_post_meta($pid, $tid, true);

	/* if value is not empty, a thumbnail already exists ... */
	if (!empty($existing_thumbnail)) {

		/* Get the existing thumbnail's absolute filepath */
		$existing_thumbnail = $wppt_general_options['upload_path'].'/'.basename($existing_thumbnail);

		/* Check existing thumbnail file and delete it */
		if (file_exists($existing_thumbnail)) {
			unlink($existing_thumbnail);
		} else echo "[Debug] Failed to delete ".$existing_thumbnail;
	}

	/* Save new thumbnail */
	$thumbnail_saved = wppt_resizeThumbnailImage($thumbnail_location, $wppt_general_options['upload_path'].'/'.$wppt_general_options['original_file_name'], $crop_w, $crop_h, $x, $y, $scale);

	/* get new thumbnail's URL and ... */
	$thumbnail_url = get_bloginfo('wpurl').'/wp-content/uploads/wp-post-thumbnail/'.$thumbnail_name;

	/* add or update thumbnail's URL to custom key value */
	add_post_meta($pid, $tid, $thumbnail_url, true)
	or update_post_meta($pid, $tid, $thumbnail_url);
} 

/* +-------------------------------------------------------------------------------+
   | Delete thumbnail button is clicked as signalled by $_POST["delete_thumbnail"] |
   +-------------------------------------------------------------------------------+ */
if ( (isset($_POST["delete_thumbnail"])) && ($_POST["delete_thumbnail"]=="yes") )  {
	/* Check for existing thumbnail in custom field.
	   Get value from thumbnail's custom field key. */
	$existing_thumbnail = get_post_meta($pid, $tid, true);

	/* if value is not empty, a thumbnail already exists ... */
	if (!empty($existing_thumbnail)) {

		/* Get the existing thumbnail's absolute filepath */
		$existing_thumbnail = $wppt_general_options['upload_path'].'/'.basename($existing_thumbnail);

		if (file_exists($existing_thumbnail)) {
			unlink($existing_thumbnail); /* Delete existing thumbnail */
			delete_post_meta($pid, $tid); /* Delete thumbnail's custom key */
		} else echo "Failed to delete ".$existing_thumbnail;
	}
} ?>

<script language="javascript" type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#bigimg').imgAreaSelect({hide:true});

	/* reset big image's crop box to selected thumbnail's dimension and aspect ratio */
	function resetBigImg() {
		jQuery('#bigimg').imgAreaSelect({
			aspectRatio:'1:<?php echo $h/$w; ?>',
			minWidth:<?php echo $w; ?>, minHeight:<?php echo $h; ?>,
			x1:0, y1:0, x2:<?php echo $w; ?>, y2:<?php echo $h; ?>,
			borderColor1:'#888888', borderColor2:'#C8C8C8',
			outerOpacity:0.38, selectionOpacity:0,
			onSelectEnd:wppt_updateCoords
		});
	}

	<?php /* if thumbnail found */
	if ( $existing_thumbnail = get_post_meta($pid, $tid, true) ) {
		$crop_title = basename($existing_thumbnail);
		$ext =  substr($crop_title, strrpos($crop_title, "_"));
		$crop_title = str_replace($ext, "", $crop_title);
		$crop_title = str_replace('-', ' ', $crop_title); ?>

		var thumbimg = new Image();
		jQuery(thumbimg).attr({ id:"thumb_img", src:"<?php echo $existing_thumbnail; ?>" })
			.hide().load(function() {
			jQuery('#crop_title').attr({value:"<?php echo $crop_title; ?>"});
			jQuery(this).prependTo(jQuery('#wppt_thumb_preview .image'));
			jQuery(this).fadeIn('slow', function() {
				resetBigImg();
				jQuery(loading_img).remove();
			});
		});
	<?php } else { ?>
		/* thumbnail not found */
		jQuery('#wppt_thumb_preview .image').empty().append('<p id="wppt_not_found"><em style="color:#bbb;font-weight:bold;">&ldquo;<?php echo $name; ?>&rdquo;</em> <?php echo _e('thumbnail is not found. Make one', 'wp-post-thumbnail'); ?> :)</p>');
		jQuery('#delete_thumbnail_button').hide();
		jQuery(loading_img).remove();
		resetBigImg();
	<?php } ?>

	/* Bind click action to save thumbnail button */
	jQuery('#save_thumbnail_button').click(function() {
		jQuery('#bigimg').imgAreaSelect({hide:true});
		jQuery(this).attr({value:"<?php echo _e('Saving', 'wp-post-thumbnail'); ?> ..."});
		jQuery('#thumb_img').fadeOut('slow');
		jQuery('#delete_thumbnail_button').fadeOut('slow', function() {
			jQuery('.selected_thumbnail').append(jQuery(loading_img));
		});
		jQuery('#wppt_admin_panel').load("<?php echo WP_PLUGIN_URL; ?>/wp-post-thumbnail/wppt_repository.php", {
			crop_top	: jQuery('#crop_top').val(),
			crop_left	: jQuery('#crop_left').val(),
			crop_width	: jQuery('#crop_width').val(),
			crop_height	: jQuery('#crop_height').val(),
			crop_title	: jQuery('#crop_title').val(),
			tid		: jQuery('#hidden_tid').val(),
			pid		: <?php echo $pid; ?>,
			save_thumbnail	: "yes"
		});
	});

	/* Bind click action to delete thumbnail button */
	jQuery('#delete_thumbnail_button').hover(function() {
		jQuery(this).css({ "cursor":"pointer" });
	}, function () {
		jQuery(this).css({ "cursor":"" });
	}).click( function() {
		jQuery(this).empty().append('<?php echo _e('Deleting', 'wp-post-thumbnail'); ?> &hellip;').fadeOut('slow');
		jQuery('#thumb_img').fadeOut('slow');
		jQuery('#wppt_admin_panel').load("<?php echo WP_PLUGIN_URL; ?>/wp-post-thumbnail/wppt_repository.php", {
			tid			: jQuery('#hidden_tid').val(),
			pid			: <?php echo $pid; ?>,
			delete_thumbnail	: "yes"
		});
	});
});
</script>

<div id="wppt_thumb_preview">
	<div class="meta"><?php echo $w.' x '.$h.'px | '.$desc; ?></div>
	<div class="image"></div>
	<p style="margin-top:20px;"><span id="delete_thumbnail_button" style="padding:4px 8px;border:1px solid #FFB1B1;background:#FFE9E9;font-size:1.1em;-moz-border-radius:3px;
-webkit-border-radius:3px;"><?php echo _e('Delete', 'wp-post-thumbnail'); ?></span></p>
</div>

<div id="wppt_save_form">
	<h4 style="margin:0;font-size:1.1em;"><?php echo '3) ' . __('Name and save your thumbnail', 'wp-post-thumbnail'); ?></h4>
	<p><?php echo __('Thumbnail name', 'wp-post-thumbnail'); ?>:<input type="text" id="crop_title" name="crop_title" value="" /><br />
	<span style="color:#a8a8a8;font-size:90%;"><?php echo _e('Optional but highly recommended to improve search engine ranking', 'wp-post-thumbnail'); ?></span></p>
	<p><input type="button" id="save_thumbnail_button" class="button" style="background:#E9FFC2" value="<?php echo _e('Save Thumbnail', 'wp-post-thumbnail'); ?>" /><br />
	<span style="color:#a8a8a8;font-size:90%"><?php echo _e('Overwrites existing thumbnail', 'wp-post-thumbnail'); ?></span></p>
</div>
<br style="clear:both;" />

<!-- thumbnail hidden inputs -->
<input type="hidden" id="crop_top" name="crop_top" value="0" />
<input type="hidden" id="crop_left" name="crop_left" value="0" />
<input type="hidden" id="crop_width" name="crop_width" value="<?php echo $w; ?>" />
<input type="hidden" id="crop_height" name="crop_height" value="<?php echo $h; ?>" />
<input type="hidden" id="hidden_tid" name="hidden_tid" value="<?php echo $tid; ?>" />