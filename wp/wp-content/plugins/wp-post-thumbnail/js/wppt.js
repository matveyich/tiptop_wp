jQuery(document).ready(function() {
	jQuery('#wppt_ajax').load('../wp-content/plugins/wp-post-thumbnail/wppt_admin.php', {
		pid: jQuery('#post_ID').val()
	});
});