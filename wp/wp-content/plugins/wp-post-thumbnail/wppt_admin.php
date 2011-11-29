<?php

require_once '../../../wp-config.php';

$pid = $_POST["pid"]; /* post ID */

/* Thumbnail's ID. If not specified by $_POST["tid"], defaults to first thumbnail. */
reset($wppt_thumbnail_options);
$first_thumbnail_id = key($wppt_thumbnail_options);
$tid = ((!empty($_POST["tid"])) ? $_POST["tid"] : $first_thumbnail_id );

?>

<script type="text/javascript">

	var loading_img = new Image();
	jQuery(loading_img)
		.attr({ src:"<?php echo WP_PLUGIN_URL; ?>/wp-post-thumbnail/images/loader.gif" })
		.css({ "vertical-align":"text-bottom", "margin-left":"8px" })
		.load(function() {});

	function wppt_updateCoords(img, selection) {
		jQuery("#crop_top").attr({value:selection.y1});
		jQuery("#crop_left").attr({value:selection.x1});
		jQuery("#crop_width").attr({value:selection.width});
		jQuery("#crop_height").attr({value:selection.height});
	}
	
	function startUpload() {
		jQuery('#bigimg').imgAreaSelect({hide:true}).remove();
		jQuery('#wppt_admin_tabs').hide();
		jQuery('#wppt_upload_form').hide();
		jQuery('#wppt_admin_panel').hide();
		jQuery('#wppt_orig_img_area').empty().append('<?php _e('Uploading, please wait ...', 'wp-post-thumbnail') ?>', jQuery(loading_img));

		return true;
	}
	
	function EndUpload(success) {
		jQuery('#wppt_orig_img_area').empty().append('<?php _e('Done! Your image has been uploaded.', 'wp-post-thumbnail') ?>');
		jQuery('#wppt_ajax').load("<?php echo WP_PLUGIN_URL; ?>/wp-post-thumbnail/wppt_admin.php", {
			tid : jQuery('#hidden_tid').val(),
			pid : "<?php echo $pid; ?>",
			force_refresh : "<?php echo '?unique='.time(); ?>" });

		return true;
	}

	jQuery(document).ready(function() {

		<?php $originalFile = $wppt_general_options['upload_path'].'/'.$wppt_general_options['original_file_name'];
		
		if (file_exists($originalFile)) {

			// Instruction to be appended to big image upload form
			$upload_original_instruction = '<strong style="font-size:1.1em;">2) '.
				__('Crop the image below','wp-post-thumbnail') . '</strong> ' .
				__('or choose a new image to crop','wp-post-thumbnail');


			/* --- START lead photo format tabs HTML --- */
			$thumbnail_id_tabs = '<ul id="thumbnail_id_tabs">'."\n";
			foreach ( $wppt_thumbnail_options as $thumbnail => $properties ) {
				$thumbnail_id_tabs .= '<li id="'.$thumbnail.'">'.$properties['name'].'</li>'."\n";
			}
			$thumbnail_id_tabs .= '</ul>'."\n"; ?>
			/* --- END lead photo format tabs HTML --- */

			/* --- START Make lead photo format tabs clickable --- */
			jQuery('ul#thumbnail_id_tabs > li').hover(function() {
				jQuery(this).css({ "cursor":"pointer" });
			}, function () {
				jQuery(this).css({ "cursor":"" });
			}).click( function() {
				var thumbnail_id = jQuery(this).attr('id');
	
				jQuery('ul#thumbnail_id_tabs > li').removeClass('selected_thumbnail');
				jQuery(this).addClass('selected_thumbnail').append(jQuery(loading_img));
				
				jQuery('#wppt_admin_panel').load("<?php echo WP_PLUGIN_URL; ?>/wp-post-thumbnail/wppt_repository.php", {
					tid : thumbnail_id,
					pid : "<?php echo $pid; ?>"
				}, function() {
					return false;
				});
			});
			/* --- END Make lead photo format tabs clickable --- */

			jQuery('#wppt_admin_tabs').hide();
			jQuery('#wppt_upload_form').hide();
			jQuery('#wppt_admin_panel').hide();
			jQuery('#wppt_orig_img_area').append('<?php _e('Loading original image for cropping ...','wp-post-thumbnail'); ?> <img src="<?php echo WP_PLUGIN_URL; ?>/wp-post-thumbnail/images/loader.gif" style="vertical-align:text-bottom;margin-left:8px" />');
		
			/* --- START load big image --- */
			<?php $original_file_URL = WP_CONTENT_URL.'/uploads/wp-post-thumbnail/'.$wppt_general_options['original_file_name'].$_POST["force_refresh"]; ?>
			var img = new Image();
			jQuery(img).attr({ id:"bigimg", src:"<?php echo $original_file_URL; ?>" }).load(function() {
				jQuery(this).hide();
				jQuery('#bigimg').imgAreaSelect({hide:true}).remove();
				jQuery('#wppt_orig_img_area').empty().append(this);
				jQuery('#wppt_admin_tabs').fadeIn('slow');
				jQuery('#wppt_admin_panel').fadeIn('slow');
				jQuery('#wppt_upload_form').fadeIn('slow');
				jQuery(this).fadeIn('slow', function() {
					jQuery('ul#thumbnail_id_tabs > li').removeClass('selected_thumbnail');
					jQuery('#<?php echo $first_thumbnail_id; ?>').addClass('selected_thumbnail').append(jQuery(loading_img));
					jQuery('#wppt_admin_panel').load("<?php echo WP_PLUGIN_URL; ?>/wp-post-thumbnail/wppt_repository.php", {
						tid : "<?php echo $first_thumbnail_id; ?>",
						pid : <?php echo $pid; ?>
					});
				});
			});
			/* --- END load big image --- */
	
		<?php } else {
			/* Instruction to be appended to big image upload form */
			$upload_original_instruction = '<strong style="font-size:1.1em;">' . __('Choose an image file to upload','wp-post-thumbnail') . '</strong>'; ?>
			jQuery('#wppt_upload_form > p').css({ "text-align":"left" });
			jQuery('#wppt_admin_tabs').hide();
			jQuery('#wppt_admin_panel').hide();
		<?php } ?>
	});
</script>

<div id="wppt_admin_tabs">
	<h4 style="font-size:1.1em;">1) <?php _e('Select thumbnail','wp-post-thumbnail'); ?></h4>
	<?php echo $thumbnail_id_tabs; ?>
</div>
<div id="wppt_admin_panel"></div>

<form id="wppt_upload_form" action="<?php echo WP_PLUGIN_URL; ?>/wp-post-thumbnail/wppt_upload.php" method="post" enctype="multipart/form-data" target="target_upload" onsubmit="startUpload();">
	<p style="text-align:right;"><?php echo $upload_original_instruction; ?><br /><input name="image" type="file" size="28" /> <?php echo _e('and click', 'wp-post-thumbnail'); ?> <input style="padding:0 5px;margin:5px 0;" type="submit" name="submitBtn" value="Upload" /><br />
	<span style="color:#bbb;"><?php _e('* Accepts .jpg image files no bigger than 2 MBs', 'wp-post-thumbnail'); ?></span></p>
	<iframe id="target_upload" name="target_upload" src="#" style="width:0px;height:0px;border:0px;"></iframe>
</form>
<div id="wppt_orig_img_area"></div>