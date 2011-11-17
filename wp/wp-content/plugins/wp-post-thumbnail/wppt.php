<?php /*
Plugin Name: WP Post Thumbnail
Plugin URI: http://www.seoadsensethemes.com/wp-post-thumbnail-wordpress-plugin/
Description: WP Post Thumbnail enable bloggers to upload images, crop and save it as post thumbnails without manually copy-n-paste custom field values. For theme developers, this plugin can be configured for multiple thumbnails assigned to each posts.
Version: 0.1.8
Author: Stanley Yeoh
Author URI: http://www.seoadsensethemes.com

Copyright (C) 2008 Stanley Yeoh

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>

*/

define ('WPPT_VERSION', '0.1.8');

$wppt_thumbnail_options = array();
$wppt_general_options = array();





class file_search {
	var $found = array();

	function file_search($files, $dirs = '.', $sub = 1, $case = 0) {
		$dirs = (!is_array($dirs)) ? array($dirs) : $dirs;
		foreach ($dirs as $dir) {
			$dir .= (!ereg('/$', $dir)) ? '/' : '';
			$directory = @opendir($dir);

			while (($file = @readdir($directory)) !== FALSE) {
				if ($file != '.' && $file != '..') {
					if ($sub && is_dir($dir . $file)) {
						$this->file_search($files, $dir . $file, $sub, $case);
					}
					else {
						$files = (!is_array($files)) ? array($files) : $files;
						foreach ($files as $target) {
							$tar_ext = substr(strrchr($target, '.'), 1);
							$tar_name = substr($target, 0, strrpos($target, '.'));
							$fil_ext = substr(strrchr($file, '.'), 1);
							$fil_name = substr($file, 0, strrpos($file, '.'));
						
							$ereg = ($case) ? 'ereg' : 'eregi';
							if ($ereg($tar_name, $fil_name) && eregi($tar_ext, $fil_ext)) {
								$this->found[] = $dir . $file;
							}
						}
					}
				}
			}
		}
	}
}

function simplexml2array($xml) {
	if (get_class($xml) == 'SimpleXMLElement') {
		$attributes = $xml->attributes();
		foreach($attributes as $k=>$v) {
			if ($v) $a[$k] = (string) $v;
		}
		$x = $xml;
		$xml = get_object_vars($xml);
	}
	if (is_array($xml)) {
		if (count($xml) == 0) return (string) $x; // for CDATA
			foreach($xml as $key=>$value) {
				$r[$key] = simplexml2array($value);
			}
		if (isset($a)) $r['@'] = $a;    // Attributes
		return $r;
	}
	return (string) $xml;
} /* Copyright Daniel FAIVRE 2005 - www.geomaticien.com Copyleft GPL license */





register_activation_hook( __FILE__, 'wppt_install' );
function wppt_install() {

	if (file_exists(ABSPATH . '/wp-admin/upgrade-functions.php')) {
		require_once(ABSPATH . '/wp-admin/upgrade-functions.php');
	} else {
		require_once(ABSPATH . '/wp-admin/includes/upgrade.php');
	}

	update_option('wppt_version', WPPT_VERSION);

	$wppt_config = new file_search('wppt.xml', TEMPLATEPATH);

	if (count($wppt_config->found) > 0) {
		$xml = simplexml_load_file($wppt_config->found[0]);
		update_option('wppt_thumbnail_options', simplexml2array($xml) );
	}
}





register_deactivation_hook( __FILE__, 'wppt_uninstall' );
function wppt_uninstall() {
	//delete_option('wppt_version');
	//delete_option('wppt_thumbnail_options');
	//delete_option('wppt_general_options');
	//delete_option('_wppt_autosavedraft_pid');
}




add_action('init', 'wppt_init');
function wppt_init() {
	global $wppt_thumbnail_options, $wppt_general_options, $post;

	if ( function_exists('load_plugin_textdomain') ) {
		load_plugin_textdomain('wp-post-thumbnail', 'wp-content/plugins/wp-post-thumbnail/languages');
	}

	$wppt_config = new file_search('wppt.xml', TEMPLATEPATH);
	if (sizeof($wppt_config->found) > 0) {
		$xml = simplexml_load_file($wppt_config->found[0]);
		$wppt_thumbnail_options = simplexml2array($xml);
	} else {
		$wppt_thumbnail_options = array('wppt_default' => array('name' => 'Widescreen',
					'desc' => 'Default thumbnail left corner of content',
					'width' => '160', 'height' => '90') );
	}
	update_option('wppt_thumbnail_options', $wppt_thumbnail_options, 'WP-Post-Thumbnail thumbnail options');
	
	$wppt_general_options = get_option('wppt_general_options');
	if (empty($wppt_general_options)) {
		$wppt_general_options = array();
		$wppt_general_options['original_file_name'] = 'cache.jpg';
		$wppt_general_options['original_max_filesize'] = '3000000';
		$wppt_general_options['original_max_width'] = '690';

		/* Argh! Lots of problem creating thumbnail directory in plugin's initial release. *Embarassed* 
		Let me try again ... */
		$upload_dir = ABSPATH;
		$upload_dir .= str_replace(ABSPATH, '', trim( get_option( 'upload_path' ) ) );
		
		if ( empty( $upload_dir ) )
			$upload_dir = WP_CONTENT_DIR . '/uploads';

		$upload_dir .= '/wp-post-thumbnail';

		if ( ! wp_mkdir_p( $upload_dir ) ) {
			$message = sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $upload_dir );
			update_option('wppt_general_options', $wppt_general_options, 'WP-Post-Thumbnail general options');
			return array( 'error' => $message );
		} else $wppt_general_options['upload_path'] = $upload_dir;
		/* End thumbnail directory creation */

		update_option('wppt_general_options', $wppt_general_options, 'WP-Post-Thumbnail general options');
	}
}





/*add_action('admin_menu', 'wppt_set_default_page');
function wppt_set_default_page() {
    if ( function_exists('add_submenu_page') )
        add_submenu_page('plugins.php', __('Set WP Post Thumbnail Defaults','wp-post-thumbnail'), __('Set WP Post Thumbnail Defaults','wp-post-thumbnail'), 'manage_options', 'wp-post-thumbnail/wppt_options.php' );
}*/





add_action('admin_print_scripts', 'wppt_js');
function wppt_js() {
	wp_enqueue_script('jquery');

	wp_enqueue_script('wppt_imgareaselect',
		get_settings('siteurl').'/wp-content/plugins/wp-post-thumbnail/js/jquery.imgareaselect-0.5.1.min.js',
		array('jquery'), mt_rand() );

	wp_enqueue_script('wppt',
		get_settings('siteurl').'/wp-content/plugins/wp-post-thumbnail/js/wppt.js',
		array('jquery'), mt_rand() );
}





add_action('admin_head', 'wppt_css');
function wppt_css() {
	$url = get_settings('siteurl').'/wp-content/plugins/wp-post-thumbnail/css/wppt_admin.css';
	echo "\n" . '<link rel="stylesheet" type="text/css" href="'.$url.'" />' . "\n";
}





add_action('admin_menu', 'add_wppt_custom_box');
function add_wppt_custom_box() {
	if (function_exists('add_meta_box'))  {
		add_meta_box( 'wp-post-thumbnail', 'WP Post Thumbnail', 'wppt_template', 'post', 'normal' );
	} else {
		add_action('dbx_post_advanced', 'wppt_template' );
	}
}

function wppt_template() {
	global $post, $wppt_general_options;
	$wppt_general_options['pid'] = $post->ID; ?>

	<input type="hidden" id="wppt_pid" name="wppt_pid" value="<?php echo $post->ID; ?>" />
	<div id="wppt_ajax"></div>
<?php }

function wppt_template_old() { ?>
	<div class="dbx-b-ox-wrapper">
		<div class="dbx-h-andle-wrapper">
			<h3 class="dbx-handle">WP Post Thumbnail</h3>
		</div>
		<div class="dbx-c-ontent-wrapper">
			<div class="dbx-content">
				<?php wppt_template(); ?>
			</div>
		</div>
	</div>
<?php }






add_action('wp_head', wppt_style);
function wppt_style() {
	$output = '<!-- Start WP Post Thumbnail CSS -->'."\n";
	$output .= '<style type="text/css">'."\n";
	$output .= '.wppt_float_left {float:left;margin:0 1.5em 0.5em 0; padding:3px;border:1px solid #ddd;}'."\n";
	$output .= '.wppt_float_right {float:right;margin:0 0 0.5em 1.5em; padding:3px;border:1px solid #ddd;}'."\n";
	$output .= '</style>'."\n";
	$output .= '<!-- End WP Post Thumbnail CSS -->'."\n";

	echo $output;
}





add_filter('the_content', 'send_wppt_to_content');
function send_wppt_to_content($content) {
	global $post;

	if ($src = get_post_meta($post->ID, 'wppt_default', true)) {
		if ( !is_single() && !is_category() ) {
			$alt = basename($src);
			$ext =  substr($alt, strrpos($alt, "_"));
			$alt = str_replace($ext, "", $alt);
			$alt = str_replace('-', ' ', $alt);

			$content = '<img alt="'.$alt.'" src="'. $src .'" class="wppt_float_left" />' . $content;
		}
	}
	
	return $content;
}





add_action('save_post', 'reload', 1, 2);
function reload($post_id, $post) {
	global $wpdb;

	if ($_REQUEST['post_ID'] < 0) {
		$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key = '_wppt_autosavedraft_pid'");
		add_post_meta($post_id, '_wppt_autosavedraft_pid', $_REQUEST[ 'post_ID' ], true);
	}
}





/* Let's do some house cleaning when blogger deletes a post.

   HELP NEEDED: Do you know what action I should hook to when blogger
   deletes a post directly from the 'Manage Post' list? Please drop me a hint! */
add_action('delete_post', 'delete_orphaned_thumbnails', 1 );
function delete_orphaned_thumbnails() {
	global $post, $wppt_thumbnail_options, $wppt_general_options;

	waste_it( get_post_meta($post->ID, 'wppt_default', true) );

	foreach ( $wppt_thumbnail_options as $thumbnail => $properties ) {
		waste_it( get_post_meta($post->ID, $thumbnail, true) );
	}
}

function waste_it($thumbnail_file) {
	global $wppt_general_options;

	if ($thumbnail_file != null) {
		$thumbnail_file = $wppt_general_options['upload_path'].'/'.basename($thumbnail_file);

		if (file_exists($thumbnail_file)) {
			unlink($thumbnail_file);
			delete_post_meta($post->ID, $thumbnail);
		}
	}
} ?>