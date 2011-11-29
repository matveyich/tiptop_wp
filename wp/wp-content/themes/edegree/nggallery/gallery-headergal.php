<?php 
/**
Template Page for the gallery overview

Follow variables are useable :

	$gallery     : Contain all about the gallery
	$images      : Contain all images, path, title
	$pagination  : Contain the pagination content

 You can check the content when you insert the tag <?php var_dump($variable) ?>
 If you would like to show the timestamp of the image ,you can use <?php echo $exif['created_timestamp'] ?>
**/

?>
<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><?php if (!empty ($gallery)) : ?>
<div id="home-scrollable">

<div class="navi"></div>
<!-- "previous page" action -->
<a class="prev browse left"></a>

<div class="scrollable featured-scrollable" id="browsable">
<div class='featured-gallery items' id="main-gallery-items">

	<?php
	foreach ( $images as $image ) : ?>
	<?if (isset($image->ngg_custom_fields["Link to"])) $piclink = $image->ngg_custom_fields["Link to"]; else $piclink = "#";?>
	<?php if ( !$image->hidden )
{ ?>
						<div>

                            <div class="caption">
                                <h2>
                                    <a href="<?php echo $image->ngg_custom_fields['link']?>">
                                        <?php echo $image->alttext;?>
                                    </a>
                                </h2>
								<p>
                                    <span class="image-title">
                                        <?php echo $image->description ?>
                                    </span>
                                </p>
                            </div>
                             <div class="image-src">
								<img src="<?php echo $image->imageURL; ?>" alt="<?php echo $image->alttext ?>"border="0" />
                            </div>
						</div>
	<?php
} ?>
	<?php if ( $image->hidden ) continue; ?>

 	<?php endforeach; ?>

</div>
</div>
    <!-- "next page" action -->
<a class="next browse right"></a>

<script language="JavaScript">

jq(document).ready(function() {

    // initialize scrollable together with the navigator plugin
    jq("#browsable").scrollable({circular: true}).navigator({
        navi:'div.navi'
    }).autoscroll({ autoplay: true });
});
</script>

</div>

<?php endif; ?>