=== Plugin Name ===
Contributors: Stanley Yeoh
Donate link: http://www.seoadsensethemes.com/wp-post-thumbnail-wordpress-plugin
Tags: thumbnail, thumbnails, images, image, post, ajax, custom field, upload, resize, crop, themes
Requires at least: 2.6.2
Tested up to: 2.6.2
Stable tag: 0.1.8

This plugin enable bloggers to upload images, crop and save it as post thumbnails without manually copy-n-paste custom field values. For theme developers, this plugin can be configured for multiple thumbnails per post usage.

== Description ==

WP Post Thumbnail plugin adds an image upload, crop and save panel in 'Write Post' screen. It allows you to easily upload a .jpg image file. Once the image file is uploaded, you can crop it before saving it as your thumbnail. That's it.

Spend less time messing around with Photoshop everytime you need to make a thumbnail (or two) for your post. Also, saves you time and trouble from manually copy-and-pasting uploaded image URLs into custom key value fields.

For theme developers, particularly magazine-style WordPress theme developers, you can configure up to 3 different thumbnails to be used in each post. For instance,

- a big 320px by 180px widescreen thumbnail for leading featured post to be displayed prominently on the front page.

- a square 125px by 125px thumbnail for recent posts.

Dimensions as varied as your theme requires can be easily configured in an XML file. Save the file as 'wppt.xml' and put it in your theme's folder (ideally, the main folder but any folder will do). WP Post Thumbnail will scan your theme folders, look for your theme's 'wppt.xml' file and read your theme's thumbnail(s) configuration.

== Installation ==
1. Copy the `wp-post-thumbnail` folder into your 'wp-content/plugins' folder.
2. Activate the plugin through the 'Plugins' menu in WordPress. That's it.

Note: The default thumbnail is 160px by 90px which floats to the top left corner of the content of your post.

If your WordPress blog uses a theme that comes with pre-configured set of thumbnail(s) to work with this plugin, the theme's folder should contain an XML file called 'wppt.xml' created by the theme developer. In this case, it will override the default thumbnail.

For theme developers, more information on how to configure WP Post Thumbnail for your WordPress theme can be found in FAQ.

== Known Issues / Bugs ==

== Frequently Asked Questions ==

= I am developing a WordPress theme. How do I configure WP Post Thumbnail to work specifically for my theme? =

This is the purpose of which WP Post Thumbnail is written, for theme developers who wants to use multiple dimensioned thumbnails in their WordPress themes. The current version of this plugin assumes that you, as theme developer, have basic knowledge of writing a configuration file in XML format and calling/placing the thumbnails’ custom field value within your theme.

For the visually inclined, click <a href="http://www.seoadsensethemes.com/wp-content/uploads/2008/10/wp-post-thumbnail-wordpress-plugin_developer_flow.jpg">here</a> to open an image that illustrates basically how theme developers can use WP Post Thumbnail to handle thumbnail(s) in their WordPress themes.

I have included an XML configuration file in the plugin's main folder called 'wppt.xml':

	<?xml version="1.0" encoding="UTF-8"?>
	<wp-post-thumbnail>
		<pft_widescreen>
			<name>Widescreen</name>
			<desc>Recent posts</desc>
			<width>270</width>
			<height>110</height>
		</pft_widescreen>
		<pft_square>
			<name>Square</name>
			<desc>Category post and more</desc>
			<width>150</width>
			<height>150</height>
		</pft_square>
		<pft_rectangle>
			<name>Rectangle</name>
			<desc>Latest featured post</desc>
			<width>390</width>
			<height>270</height>
		</pft_rectangle>
	</wp-post-thumbnail>

The sample XML file configures 3 different thumbnails titled <code>&lt;pft_widescreen&gt;</code>, <code>&lt;pft_square&gt;</code> and <code>&lt;pft_rectangle&gt;</code>.

The node titles are also used as custom field keys to call specific thumbnails to appear in your theme. For instance, to get the square thumbnail:

	<code>&lt;img src=&quot;&lt;?php echo get_post_meta($post-&gt;ID, 'pft_square', true); ?&gt;&quot; /&gt;</code>


Each thumbnail has four properties:

<code>&lt;name&gt;</code> specifies a friendly name to appear in the thumbnail tab in 'Write Post' area.
<code>&lt;desc&gt;</code> a short description about the thumbnail
<code>&lt;width&gt;</code> width of thumbnail in pixels
<code>&lt;height&gt;</code> height of thumbnail in pixels

IMPORTANT: All fields are required.

Save your file as 'wppt.xml' and put it in your theme's directory. Blogs that uses your theme, and has WP Post Thumbnail plugin activated, will detect your 'wppt.xml' configuration file.

= Where are all the cropped thumbnails stored? =

They are stored in 'wp-post-thumbnail' folder inside your WordPress default upload path. Eg. <code>'/wp-content/uploads/wp-post-thumbnail'</code> by default.

== Screenshots ==

1. WP Post Thumbnail 'out-of-box' default thumbnail in use
2. WP Post Thumbnail panel in 'Write Post' screen
3. A WordPress theme with WP Post Thumbnail configured to use 3 different thumbnails per post - 390x270px, 150x150px and 290x110px.

== Uninstall ==

1. Deactivate the plugin