<?php
/**
 * Plugin Name:       Dynamic Hero Block
 * Description:       Displays a page hero section with the page title, featured image, and dynamic page metadata.
 * Version:           1.0.0
 * Text Domain:       dynamic-page-hero-block
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Edesa Cabang
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dynamic-page-hero-block
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( file_exists( __DIR__ . '/app/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/app/vendor/autoload.php';
}

$my_base_framework = new \DHB\Flowy\Main(__FILE__, array());

include_once('includes/main.php');
include_once('includes/hero-form.php');
include_once('includes/hero-metabox.php');

new DPHB_Main(__FILE__, array('style_version' => '1.0.3'));

/**
 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
 * based on the registered block metadata. Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function create_block_dynamic_page_hero_block_block_init() {
	wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
}
add_action( 'init', 'create_block_dynamic_page_hero_block_block_init' );