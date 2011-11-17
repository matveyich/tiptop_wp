<?php
/**
Template name: blogposts
*/
global $shortname;

$number_posts = get_option('tbf2_number_posts');

if (!isset($number_posts)) {
	$number_posts = get_option('posts_per_page');
}

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

/*if (is_active_widget('widget_myFeature')) {
	$category = "showposts=$posts&cat=-".$options['category'];
} else {
	$category = "showposts=".$posts;
}
query_posts($category."&paged=$paged&showposts=$number_posts");*/

get_header();

$news_cat = get_category_by_slug('news');
$args = array(
	'cat'      => $news_cat->term_id,
	'order'    => 'DESC',
    'paged'    => 1
);
query_posts($args);
?>
	
	<?php if (have_posts()) : ?>
		<?php
        $i = 0;
        while (have_posts()) {
            the_post(); 
            include(dirname(__FILE__).'/post.php');
            if ($html = get_option($shortname.'_custom_html_'.$i)) {
                echo "<div class='customhtml'>$html</div>";
            }
        $i++;
        }
		?>
	<?php endif; ?>
    
    <?php if(isset($paged)):?>
<!--    <div class="navigation">
        <p class="alignleft"><?php /*previous_posts_link('&laquo; Previous Page'); */?></p>
        <p class="alignright"><?php /*next_posts_link('Next Page &raquo;'); */?></p>
        <div class="recover"></div>
    </div>
-->
        <?php
/*
 * show extended pagination
 * */

 global $wp_query, $wp_rewrite;
$wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;

$pagination = array(
	'base' => @add_query_arg('page','%#%'),
	'format' => '',
	'total' => $wp_query->max_num_pages,
	'current' => $current,
	'show_all' => true,
	'type' => 'plain'
	);

if( $wp_rewrite->using_permalinks() )
	$pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 's', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );

if( !empty($wp_query->query_vars['s']) )
	$pagination['add_args'] = array( 's' => get_query_var( 's' ) );

echo paginate_links( $pagination );
        ?>
    <?php endif; ?>

<?php get_footer(); ?>