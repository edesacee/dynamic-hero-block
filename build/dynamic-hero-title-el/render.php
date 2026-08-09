<?php
/**
 * @phpcs:disable WordPress.Files.FileName.InvalidClassFileName
 */

global $post;

$page_title = '';

if (is_singular()) {
    $page_title = $post->post_title;

    if ($post->post_type == 'post') {
        $subtext = $post->post_excerpt;
    }
    else if ($post->post_type == 'page') {
        $subtext = get_post_meta($post->ID,'_semp_hero_subtext', true);
    }
}
else if ( is_post_type_archive() ) {
    $post_type = get_queried_object()->name;
    $post_type_obj = get_post_type_object( $post_type );

    if ( $post_type ) {
        $page_title = $post_type_obj->labels->name;
    }
}
else if ( is_tax() ) {
    $current_term = get_queried_object();
    $current_taxonomy = get_taxonomy( $current_term->taxonomy );        
    $page_title = $current_taxonomy->labels->name;
    $subtext = term_description();
}    
else if ( is_category() || is_tag()) {
    $current_obj = get_queried_object();

    $obj_id   = $current_obj->term_id;
    $obj_name = $current_obj->name;
    $obj_slug = $current_obj->slug;
    $page_title = $obj_name;
    $subtext = term_description();
}
else {
    $page_title = get_the_title( get_option( 'page_for_posts' ) );
}

$wrapper_attributes = get_block_wrapper_attributes( array(
    'style' => "text-align:" . $attributes['alignment'],
    'class' => 'custom-notice-box'
) );

?>
<h1 <?php echo $wrapper_attributes; ?>>
    <?php echo esc_html($page_title); ?>
</h1>