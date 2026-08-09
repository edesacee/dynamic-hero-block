<?php
/**
 * @phpcs:disable WordPress.Files.FileName.InvalidClassFileName
 */

global $post;

$image_url = '';
$style = '';

if (isset( $attributes['mediaId'] ) && $attributes['mediaId'] ){
    $media_id = $attributes['mediaId'];
    $image_url = wp_get_attachment_image_url( $media_id, array( 1200, 600 ) );
}

if (is_singular()) {
    $page_title = $post->post_title;

    if ($post->post_type == 'post') {
        $image_data = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );

        if ($image_data) {
            $image_url = $image_data[0];    
        }
    }
    else if ($post->post_type == 'page') {
        $meta_image_url = get_post_meta($post->ID,'_dphb_hero_bg_image', true);

        if ($meta_image_url) {
            $image_url = $meta_image_url;
        }
    }
}
else if ( is_post_type_archive() ) {
    // $post_type = get_queried_object()->name;
    // $post_type_obj = get_post_type_object( $post_type );

    // Nothing to do here for now
}
else if ( is_tax() ) {
    // Nothing to do here for now
}    
else if ( is_category() || is_tag()) {
    // Nothing to do here for now
}

if ( $image_url ) {
    $style .= 'background: url("' . esc_attr($image_url) . '") center/cover no-repeat';
}

$width_num = isset( $attributes['widthNum'] ) ? $attributes['widthNum'] : '100';
$width_unit = isset( $attributes['widthUnit'] ) ? $attributes['widthUnit'] : '%';
$custom_width = $width_num . $width_unit;

$overlay = $attributes['gradient'];

// Prepare dynamic styles to inject into the block wrapper
$wrapper_attributes = get_block_wrapper_attributes( array(
    'style' => $style,
    'class' => 'custom-notice-box'
) );

$inner_attributes = 'style="' . ($custom_width ? 'width: ' . esc_attr( $custom_width ) . ';' : '') . 'margin: 0 auto;"';
$overlay_styles = 'style="' . ($overlay ? 'background: ' . esc_attr($overlay) . ';"' : '');
$dynamic_post_title = get_the_title();

$html = '<' . esc_html($opts['tag']) . '>' . esc_html($archive_title) . '</' . esc_html($opts['tag']) . '>';

?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="overlay" <?php echo $overlay_styles ?>>
        <div <?php echo $inner_attributes; ?> class="inner">
            <?php echo $content; ?>
        </div>
    </div>
</div>