<?php

/**
 * Register custom post types.
 *
 * @package jbros-film-review
 */

namespace JbrosFilmReview\includes;

! defined( 'ABSPATH' ) AND exit;

/**
 * Register the "Film Review" custom post type.
 */
class Film_Review_Post { 


    //function __construct() {
    public static function register() {

        $instance = new self;

        add_action( 'init', array( $instance, 'register_post_types' ) );
        add_filter( 'single_template', array( $instance, 'load_film_review_template' ) );
        add_action( 'add_meta_boxes', array( $instance, 'meta_fields_add_meta_box' ) );
        add_action( 'save_post', array( $instance, 'save_meta_box'), 10, 2 );
    }

    
    function register_post_types() {
        //flush_rewrite_rules();
        $labels = array(
            'name'               => __( 'Film Reviews', 'jbros-film-review' ),
            'singular_name'      => __( 'Film Review', 'jbros-film-review' ),
            'menu_name'          => __( 'Film Reviews', 'jbros-film-review' ),
            'name_admin_bar'     => __( 'Film Review', 'jbros-film-review' ),
            'add_new'            => __( 'Add New', 'jbros-film-review' ),
            'add_new_item'       => __( 'Add New Film Review', 'jbros-film-review' ),
            'new_item'           => __( 'New Film Review', 'jbros-film-review' ),
            'edit_item'          => __( 'Edit Film Review', 'jbros-film-review' ),
            'update_item'          => __( 'Update Film Review', 'jbros-film-review' ),
            'view_item'          => __( 'View Film Review', 'jbros-film-review' ),
            'all_items'          => __( 'All Film Reviews', 'jbros-film-review' ),
            'search_items'       => __( 'Search Film Reviews', 'jbros-film-review' ),
            'parent_item_colon'  => __( 'Parent Film Reviews:', 'jbros-film-review' ),
            'not_found'          => __( 'No film reviews found.', 'jbros-film-review' ),
            'not_found_in_trash' => __( 'No film reviews found in Trash.', 'jbros-film-review' ),
        );

        $supports = array(
            'title', // post title
            'editor', // post content
            'author', // post author
            'thumbnail', // featured images
            'excerpt', // post excerpt
            'custom-fields', // custom fields
            'comments', // post comments
            'revisions', // post revisions
            'post-formats', // post formats
          );

        $args = array(
            'label'              => __('Film Review', 'theme'),
            'description'        => __('All Film Reviews', 'theme'),
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            //'show_in_nav_menus'  => true,
            //'show_in_admin_bar'  => true,
            //'can_export'         => true,
            'show_in_rest'       => true,
            'query_var'           => true,
            'rewrite'             => array( 'slug' => 'film-review' ),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'menu_icon'          => 'dashicons-image-filter',
            'hierarchical'        => false,
            'menu_position'       => null,
            'supports'            => $supports,
            /*
            'template' => array(
                array( plugin_dir_path( __FILE__ ) . 'single-film_review.php' ), // Single post template
            ),*/
        );

        register_post_type('film_review', $args );
    }

    function load_film_review_template( $template ) {
        global $post;
    
        if ( 'film_review' === $post->post_type && locate_template( array( 'single-film_review.php' ) ) !== $template ) {
            /*
             * This is a 'film_review' post
             * AND a 'single film_review template' is not found on
             * theme or child theme directories, so load it
             * from our plugin directory.
             */
            return plugin_dir_path( __FILE__ ) . 'single-film_review.php';
        }
    
        return $template;
    }

    /**
     * Render the custom fields meta box.
     *
     * @param WP_Post $post The current post object.
     */
    function render_meta_box( $post ) {
        //user-given movie params
        $film_title = get_post_meta( $post->ID, 'film_title', true );
        $film_year  = get_post_meta( $post->ID, 'film_year', true );

        //tmdb api data
        $movie_id = get_post_meta($post->ID, 'jbros_movie_id', true);
        $poster_url = get_post_meta($post->ID, 'jbros_poster_url', true);
        $backdrop_path = get_post_meta($post->ID, 'jbros_backdrop_path', true);
        $overview = get_post_meta($post->ID, 'jbros_overview', true);

        wp_nonce_field( 'jbros_film_review_meta_box', 'jbros_film_review_meta_box_nonce' );
        ?>
        <div>
            <label for="film_title"><?php esc_html_e( 'Film Title', 'jbros-film-review' ); ?></label>
            <input type="text" id="film_title" name="film_title" value="<?php echo esc_attr( $film_title ); ?>" required>
        </div>
        <div>
            <label for="film_year"><?php esc_html_e( 'Film Year', 'jbros-film-review' ); ?></label>
            <input type="number" id="film_year" name="film_year" value="<?php echo esc_attr( $film_year ); ?>" required>
        </div>
        <div>
            <label for="movie_id"><?php esc_html_e('Movie ID', 'jbros-film-review'); ?></label>
            <input type="text" id="movie_id" name="movie_id" value="<?php echo esc_attr($movie_id); ?>" ><!-- removed readonly attribute -->
        </div>
        <div>
            <label for="overview"><?php esc_html_e('Overview', 'jbros-film-review'); ?></label>
            <textarea id="overview" name="overview" ><?php echo esc_attr($overview); ?></textarea>
        </div>
        <div>
            <label for="backdrop_path"><?php esc_html_e('Backdrop URL', 'jbros-film-review'); ?></label>
            <input type="text" id="backdrop_path" name="backdrop_path" value="<?php echo esc_attr($backdrop_path); ?>" >
            <?php if ($backdrop_path) : ?><img src="<?php echo esc_attr($backdrop_path); ?>" alt="backdrop preview" /><? endif; ?>
        </div>
        <div>
            <label for="poster_url"><?php esc_html_e('Poster URL', 'jbros-film-review'); ?></label>
            <input type="text" id="poster_url" name="poster_url" value="<?php echo esc_attr($poster_url); ?>" ><!-- removed readonly attribute -->
            <?php if ($poster_url) : ?><img src="<?php echo esc_attr($poster_url); ?>" alt="poster preview" /><? endif; ?>
        </div>
        <?php
    }

    function meta_fields_add_meta_box() {
        add_meta_box(
            'film_review_meta_box',
            __( 'Film Details', 'jbros-film-review' ),
            array($this,'render_meta_box'),
            'film_review',
            'normal',
            'high'
        );
    }

    /**
     * Save the custom fields meta box data.
     *
     * @param int     $post_id The ID of the current post.
     * @param WP_Post $post    The current post object.
     */
    function save_meta_box( $post_id, $post ) {
        if ( ! isset( $_POST['jbros_film_review_meta_box_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['jbros_film_review_meta_box_nonce'], 'jbros_film_review_meta_box' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        if ( isset( $_POST['film_title'] ) ) {
            update_post_meta( $post_id, 'film_title', sanitize_text_field( $_POST['film_title'] ) );
        }

        if ( isset( $_POST['film_year'] ) ) {
            update_post_meta( $post_id, 'film_year', sanitize_text_field( $_POST['film_year'] ) );
        }

        // The movie_id and poster_url fields can/should be read-only, so no need to update them.
        //for now keeping editable
        if ( isset( $_POST['jbros_movie_id'] ) ) {
            update_post_meta( $post_id, 'jbros_movie_id', sanitize_text_field( $_POST['jbros_movie_id'] ) );
        }

        if ( isset( $_POST['jbros_overview'] ) ) {
            update_post_meta( $post_id, 'jbros_overview', sanitize_text_field( $_POST['jbros_overview'] ) );
        }

        if ( isset( $_POST['jbros_poster_url'] ) ) {
            update_post_meta( $post_id, 'jbros_poster_url', sanitize_text_field( $_POST['jbros_poster_url'] ) );
        }
        if ( isset( $_POST['jbros_backdrop_path'] ) ) {
            update_post_meta( $post_id, 'jbros_backdrop_path', sanitize_text_field( $_POST['jbros_backdrop_path'] ) );
        }
    }
}

Film_Review_Post::register();





