<?php
/**
 * Register Gutenberg blocks.
 *
 * @package jbros-film-review
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the jbros-film-review-block block.
 */
function jbros_film_review_register_blocks() {
    register_block_type(
        plugin_dir_path( __FILE__ ) . 'jbros-film-review-block/build'
    );
    register_block_type(
        plugin_dir_path( __FILE__ ) . 'jbros-movie-info-block/build'
    );
}
add_action( 'init', 'jbros_film_review_register_blocks' );